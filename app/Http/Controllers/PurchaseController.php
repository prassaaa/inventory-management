<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\PurchaseDetail;
use App\Models\Supplier;
use App\Models\Product;
use App\Models\Unit;
use App\Models\StockWarehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use PDF;

class PurchaseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Purchase::with(['supplier', 'creator']);
        
        // Filter by supplier if provided
        if ($request->has('supplier_id') && $request->supplier_id) {
            $query->where('supplier_id', $request->supplier_id);
        }
        
        $purchases = $query->orderBy('date', 'desc')->get();
        
        return view('purchases.index', compact('purchases'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();
        $products = Product::with('baseUnit')->where('is_active', true)->orderBy('name')->get();
        $units = Unit::orderBy('name')->get();
        
        return view('purchases.create', compact('suppliers', 'products', 'units'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'invoice_number' => 'required|string|max:50|unique:purchases',
            'date' => 'required|date',
            'payment_type' => 'required|in:tunai,tempo',
            'due_date' => 'required_if:payment_type,tempo|nullable|date',
            'total_amount' => 'required|numeric|min:0',
            'note' => 'nullable|string',
            'product_ids' => 'required|array|min:1',
            'product_ids.*' => 'required|exists:products,id',
            'quantities' => 'required|array|min:1',
            'quantities.*' => 'required|numeric|min:0.01',
            'prices' => 'required|array|min:1',
            'prices.*' => 'required|numeric|min:0',
            'units' => 'required|array|min:1',
            'units.*' => 'required|exists:units,id',
        ]);

        try {
            DB::beginTransaction();
            
            // Create purchase
            $purchase = Purchase::create([
                'supplier_id' => $validated['supplier_id'],
                'invoice_number' => $validated['invoice_number'],
                'date' => $validated['date'],
                'payment_type' => $validated['payment_type'],
                'due_date' => $validated['payment_type'] === 'tempo' ? $validated['due_date'] : null,
                'total_amount' => $validated['total_amount'],
                'status' => 'pending',
                'note' => $validated['note'],
                'created_by' => Auth::id(),
            ]);
            
            // Create purchase details
            foreach ($validated['product_ids'] as $index => $productId) {
                $quantity = $validated['quantities'][$index];
                $price = $validated['prices'][$index];
                $unitId = $validated['units'][$index];
                
                PurchaseDetail::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $productId,
                    'unit_id' => $unitId,
                    'quantity' => $quantity,
                    'price' => $price,
                    'subtotal' => $quantity * $price,
                ]);
            }
            
            DB::commit();
            
            return redirect()->route('purchases.show', $purchase)
                ->with('success', 'Purchase created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Error creating purchase: ' . $e->getMessage());
        }
    }

    /**
        * Display the specified resource.
        */
    public function show(Purchase $purchase)
    {
        $purchase->load(['supplier', 'creator', 'purchaseDetails.product', 'purchaseDetails.unit', 'purchaseReturns']);
        
        return view('purchases.show', compact('purchase'));
    }

    /**
        * Show the form for editing the specified resource.
        */
    public function edit(Purchase $purchase)
    {
        // Only allow editing purchases with 'pending' status
        if ($purchase->status !== 'pending') {
            return redirect()->route('purchases.show', $purchase)
                ->with('error', 'Cannot edit purchase. Only pending purchases can be edited.');
        }
        
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();
        $products = Product::with('baseUnit')->where('is_active', true)->orderBy('name')->get();
        $units = Unit::orderBy('name')->get();
        
        $purchase->load(['purchaseDetails.product', 'purchaseDetails.unit']);
        
        return view('purchases.edit', compact('purchase', 'suppliers', 'products', 'units'));
    }

    /**
        * Update the specified resource in storage.
        */
    public function update(Request $request, Purchase $purchase)
    {
        // Only allow updating purchases with 'pending' status
        if ($purchase->status !== 'pending') {
            return redirect()->route('purchases.show', $purchase)
                ->with('error', 'Cannot update purchase. Only pending purchases can be updated.');
        }
        
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'invoice_number' => 'required|string|max:50|unique:purchases,invoice_number,' . $purchase->id,
            'date' => 'required|date',
            'payment_type' => 'required|in:tunai,tempo',
            'due_date' => 'required_if:payment_type,tempo|nullable|date',
            'total_amount' => 'required|numeric|min:0',
            'note' => 'nullable|string',
            'product_ids' => 'required|array|min:1',
            'product_ids.*' => 'required|exists:products,id',
            'quantities' => 'required|array|min:1',
            'quantities.*' => 'required|numeric|min:0.01',
            'prices' => 'required|array|min:1',
            'prices.*' => 'required|numeric|min:0',
            'units' => 'required|array|min:1',
            'units.*' => 'required|exists:units,id',
        ]);

        try {
            DB::beginTransaction();
            
            // Update purchase
            $purchase->update([
                'supplier_id' => $validated['supplier_id'],
                'invoice_number' => $validated['invoice_number'],
                'date' => $validated['date'],
                'payment_type' => $validated['payment_type'],
                'due_date' => $validated['payment_type'] === 'tempo' ? $validated['due_date'] : null,
                'total_amount' => $validated['total_amount'],
                'note' => $validated['note'],
                'updated_by' => Auth::id(),
            ]);
            
            // Delete existing purchase details
            $purchase->purchaseDetails()->delete();
            
            // Create new purchase details
            foreach ($validated['product_ids'] as $index => $productId) {
                $quantity = $validated['quantities'][$index];
                $price = $validated['prices'][$index];
                $unitId = $validated['units'][$index];
                
                PurchaseDetail::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $productId,
                    'unit_id' => $unitId,
                    'quantity' => $quantity,
                    'price' => $price,
                    'subtotal' => $quantity * $price,
                ]);
            }
            
            DB::commit();
            
            return redirect()->route('purchases.show', $purchase)
                ->with('success', 'Purchase updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Error updating purchase: ' . $e->getMessage());
        }
    }

    /**
        * Remove the specified resource from storage.
        */
    public function destroy(Purchase $purchase)
    {
        // Only allow deleting purchases with 'pending' status
        if ($purchase->status !== 'pending') {
            return redirect()->route('purchases.index')
                ->with('error', 'Cannot delete purchase. Only pending purchases can be deleted.');
        }
        
        try {
            DB::beginTransaction();
            
            // Delete purchase details
            $purchase->purchaseDetails()->delete();
            
            // Delete purchase
            $purchase->delete();
            
            DB::commit();
            
            return redirect()->route('purchases.index')
                ->with('success', 'Purchase deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('purchases.index')
                ->with('error', 'Error deleting purchase: ' . $e->getMessage());
        }
    }
    
    /**
        * Confirm purchase and update stock
        */
    public function confirm(Purchase $purchase)
    {
        // Only allow confirming purchases with 'pending' status
        if ($purchase->status !== 'pending') {
            return redirect()->route('purchases.show', $purchase)
                ->with('error', 'Purchase is already confirmed.');
        }
        
        try {
            DB::beginTransaction();
            
            // Update purchase status
            $purchase->update([
                'status' => 'complete',
                'updated_by' => Auth::id(),
            ]);
            
            // Update stock for each product
            foreach ($purchase->purchaseDetails as $detail) {
                $product = $detail->product;
                $unit = $detail->unit;
                
                // Convert quantity to base unit if necessary
                $baseQuantity = $detail->quantity;
                if ($unit->id !== $product->base_unit_id) {
                    // Find product unit for conversion
                    $productUnit = $product->productUnits()->where('unit_id', $unit->id)->first();
                    if ($productUnit) {
                        $baseQuantity = $detail->quantity * $productUnit->conversion_value;
                    }
                }
                
                // Update or create stock warehouse
                $stockWarehouse = StockWarehouse::firstOrCreate(
                    ['product_id' => $product->id, 'unit_id' => $product->base_unit_id],
                    ['quantity' => 0]
                );
                
                $stockWarehouse->increment('quantity', $baseQuantity);
            }
            
            DB::commit();
            
            return redirect()->route('purchases.show', $purchase)
                ->with('success', 'Purchase confirmed successfully and stock updated.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('purchases.show', $purchase)
                ->with('error', 'Error confirming purchase: ' . $e->getMessage());
        }
    }
    
    /**
        * Generate purchase receipt in PDF
        */
    public function receipt(Purchase $purchase)
    {
        $purchase->load(['supplier', 'purchaseDetails.product', 'purchaseDetails.unit', 'creator']);
        
        $pdf = PDF::loadView('purchases.receipt', compact('purchase'));
        
        return $pdf->stream('purchase_' . $purchase->invoice_number . '.pdf');
    }
    }