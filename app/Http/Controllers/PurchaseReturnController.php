<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnDetail;
use App\Models\PurchaseDetail;
use App\Models\StockWarehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Helpers\UnitConversion;

class PurchaseReturnController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $purchaseReturns = PurchaseReturn::with(['purchase.supplier', 'creator'])
            ->orderBy('date', 'desc')
            ->get();
        
        return view('purchase-returns.index', compact('purchaseReturns'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $purchases = Purchase::where('status', '!=', 'pending')
            ->orderBy('date', 'desc')
            ->get();
            
        return view('purchase-returns.create', compact('purchases'));
    }
    
    /**
     * Show form for creating a return from a specific purchase
     */
    public function createFromPurchase(Purchase $purchase)
    {
        // Check if purchase is not pending
        if ($purchase->status === 'pending') {
            return redirect()->route('purchases.show', $purchase)
                ->with('error', 'Cannot create return for pending purchase.');
        }
        
        $purchase->load(['supplier', 'purchaseDetails.product', 'purchaseDetails.unit']);
        
        // Add method to calculate returned quantity for each purchase detail
        foreach ($purchase->purchaseDetails as $detail) {
            $detail->returnedQuantity = function() use ($detail) {
                return $detail->purchaseReturnDetails->sum('quantity');
            };
        }
        
        return view('purchase-returns.create-from-purchase', compact('purchase'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'purchase_id' => 'required|exists:purchases,id',
            'date' => 'required|date',
            'total_amount' => 'required|numeric|min:0.01',
            'note' => 'required|string',
            'return_quantities' => 'required|array',
            'return_quantities.*' => 'numeric|min:0',
            'reasons' => 'required|array',
            'reasons.*' => 'nullable|string',
        ]);
        
        // Get purchase
        $purchase = Purchase::findOrFail($validated['purchase_id']);
        
        // Check if purchase is not pending
        if ($purchase->status === 'pending') {
            return redirect()->route('purchases.show', $purchase)
                ->with('error', 'Cannot create return for pending purchase.');
        }
        
        // Check if return quantities are valid
        $returnItems = [];
        $totalReturnAmount = 0;
        
        foreach ($validated['return_quantities'] as $purchaseDetailId => $quantity) {
            if ($quantity <= 0) {
                continue;
            }
            
            $purchaseDetail = PurchaseDetail::findOrFail($purchaseDetailId);
            
            // Check if purchase detail belongs to the selected purchase
            if ($purchaseDetail->purchase_id != $purchase->id) {
                return redirect()->back()->withInput()
                    ->with('error', 'Invalid purchase detail selected.');
            }
            
            // Calculate already returned quantity
            $returnedQuantity = $purchaseDetail->purchaseReturnDetails()->sum('quantity');
            $availableQuantity = $purchaseDetail->quantity - $returnedQuantity;
            
            // Validate return quantity
            if ($quantity > $availableQuantity) {
                return redirect()->back()->withInput()
                    ->with('error', "Cannot return more than available quantity for {$purchaseDetail->product->name}.");
            }
            
            $returnItems[] = [
                'purchase_detail_id' => $purchaseDetailId,
                'product_id' => $purchaseDetail->product_id,
                'unit_id' => $purchaseDetail->unit_id,
                'quantity' => $quantity,
                'price' => $purchaseDetail->price,
                'subtotal' => $quantity * $purchaseDetail->price,
                'reason' => $validated['reasons'][$purchaseDetailId] ?? null,
            ];
            
            $totalReturnAmount += $quantity * $purchaseDetail->price;
        }
        
        // Check if at least one item is returned
        if (empty($returnItems)) {
            return redirect()->back()->withInput()
                ->with('error', 'Please return at least one item.');
        }
        
        // Check if calculated total matches the input total
        if (abs($totalReturnAmount - $validated['total_amount']) > 0.01) {
            return redirect()->back()->withInput()
                ->with('error', 'Total return amount mismatch.');
        }
        
        try {
            DB::beginTransaction();
            
            // Create purchase return
            $purchaseReturn = PurchaseReturn::create([
                'purchase_id' => $purchase->id,
                'date' => $validated['date'],
                'total_amount' => $totalReturnAmount,
                'note' => $validated['note'],
                'created_by' => Auth::id(),
            ]);
            
            // Create purchase return details and update stock
            foreach ($returnItems as $item) {
                $purchaseDetail = PurchaseDetail::find($item['purchase_detail_id']);
                
                // Create purchase return detail
                PurchaseReturnDetail::create([
                    'purchase_return_id' => $purchaseReturn->id,
                    'product_id' => $item['product_id'],
                    'unit_id' => $item['unit_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'subtotal' => $item['subtotal'],
                    'reason' => $item['reason'],
                ]);
                
                // Update warehouse stock
                $product = $purchaseDetail->product;
                $unit = $purchaseDetail->unit;
                
                // Convert return quantity to base unit if necessary
                $baseQuantity = $item['quantity'];
                if ($unit->id !== $product->base_unit_id) {
                    // Use helper for unit conversion
                    $baseQuantity = UnitConversion::convert(
                        $item['quantity'], 
                        $unit->id, 
                        $product->base_unit_id, 
                        $product->id
                    );
                    
                    if ($baseQuantity === null) {
                        throw new \Exception("Cannot convert unit for product {$product->name}.");
                    }
                }
                
                // Update stock warehouse
                $stockWarehouse = StockWarehouse::where('product_id', $product->id)
                    ->where('unit_id', $product->base_unit_id)
                    ->first();
                
                if (!$stockWarehouse) {
                    throw new \Exception("Stock not found for product {$product->name}.");
                }
                
                if ($stockWarehouse->quantity < $baseQuantity) {
                    throw new \Exception("Not enough stock for product {$product->name}.");
                }
                
                $stockWarehouse->decrement('quantity', $baseQuantity);
            }
            
            DB::commit();
            
            return redirect()->route('purchase-returns.show', $purchaseReturn)
                ->with('success', 'Purchase return created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()
                ->with('error', 'Error creating purchase return: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(PurchaseReturn $purchaseReturn)
    {
        $purchaseReturn->load([
            'purchase.supplier', 
            'purchaseReturnDetails.product', 
            'purchaseReturnDetails.unit',
            'creator'
        ]);
        
        return view('purchase-returns.show', compact('purchaseReturn'));
    }
}