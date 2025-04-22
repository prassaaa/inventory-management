<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\Store;
use App\Models\Product;
use App\Models\StockStore;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use PDF;

class SaleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $sales = Sale::with(['store', 'creator'])
            ->where('store_id', Auth::user()->store_id)
            ->orderBy('date', 'desc')
            ->get();
        
        return view('sales.index', compact('sales'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return redirect()->route('pos');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Redirect to POS
        return redirect()->route('pos');
    }

    /**
     * Display the specified resource.
     */
    public function show(Sale $sale)
    {
        // Check if user has access to this sale (same store)
        if (Auth::user()->store_id !== $sale->store_id && !Auth::user()->hasRole('owner')) {
            abort(403, 'Unauthorized action.');
        }
        
        $sale->load(['store', 'creator', 'saleDetails.product', 'saleDetails.unit']);
        
        return view('sales.show', compact('sale'));
    }

    /**
     * Display POS interface.
     */
    public function pos()
    {
        $categories = \App\Models\Category::orderBy('name')->get();
        
        $products = Product::with(['category', 'baseUnit'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
        
        // Add store stock for each product
        foreach ($products as $product) {
            $product->storeStock = StockStore::where('store_id', Auth::user()->store_id)
                ->where('product_id', $product->id)
                ->where('unit_id', $product->base_unit_id)
                ->first();
        }
        
        return view('sales.pos', compact('categories', 'products'));
    }

    /**
     * Process POS sale.
     */
    public function processPos(Request $request)
    {
        $validated = $request->validate([
            'sale.store_id' => 'required|exists:stores,id',
            'sale.payment_type' => 'required|in:tunai,kartu,tempo',
            'sale.customer_name' => 'nullable|string|max:255',
            'sale.discount' => 'required|numeric|min:0',
            'sale.tax' => 'required|numeric|min:0',
            'sale.total_amount' => 'required|numeric|min:0',
            'sale.total_payment' => 'required|numeric|min:0',
            'sale.change' => 'required|numeric|min:0',
            'sale.items' => 'required|array|min:1',
            'sale.items.*.product_id' => 'required|exists:products,id',
            'sale.items.*.unit_id' => 'required|exists:units,id',
            'sale.items.*.quantity' => 'required|numeric|min:0.01',
            'sale.items.*.price' => 'required|numeric|min:0',
            'sale.items.*.discount' => 'required|numeric|min:0',
            'sale.items.*.subtotal' => 'required|numeric|min:0',
        ]);
        
        $saleData = $request->sale;
        
        try {
            DB::beginTransaction();
            
            // Generate invoice number
            $lastSale = Sale::where('store_id', $saleData['store_id'])
                ->latest()
                ->first();
                
            $storeCode = Store::find($saleData['store_id'])->name[0] ?? 'S';
            $invoiceNumber = 'INV/' . $storeCode . '/' . date('Ymd') . '/' . sprintf('%04d', $lastSale ? (int)substr($lastSale->invoice_number, -4) + 1 : 1);
            
            // Create sale
            $sale = Sale::create([
                'store_id' => $saleData['store_id'],
                'invoice_number' => $invoiceNumber,
                'date' => now(),
                'customer_name' => $saleData['customer_name'],
                'total_amount' => $saleData['total_amount'],
                'payment_type' => $saleData['payment_type'],
                'discount' => $saleData['discount'],
                'tax' => $saleData['tax'],
                'total_payment' => $saleData['total_payment'],
                'change' => $saleData['change'],
                'status' => 'paid',
                'created_by' => Auth::id(),
            ]);
            
            // Create sale details and update stock
            foreach ($saleData['items'] as $item) {
                SaleDetail::create([
                    'sale_id' => $sale->id,
                    'product_id' => $item['product_id'],
                    'unit_id' => $item['unit_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'discount' => $item['discount'],
                    'subtotal' => $item['subtotal'],
                ]);
                
                // Update store stock
                $product = Product::find($item['product_id']);
                
                // Convert quantity to base unit if necessary
                $baseQuantity = $item['quantity'];
                if ($item['unit_id'] !== $product->base_unit_id) {
                    // Use helper for unit conversion
                    $baseQuantity = \App\Helpers\UnitConversion::convert(
                        $item['quantity'], 
                        $item['unit_id'], 
                        $product->base_unit_id, 
                        $product->id
                    );
                    
                    if ($baseQuantity === null) {
                        throw new \Exception("Cannot convert unit for product {$product->name}.");
                    }
                }
                
                // Update or create stock store
                $stockStore = StockStore::firstOrCreate(
                    [
                        'store_id' => $saleData['store_id'],
                        'product_id' => $item['product_id'], 
                        'unit_id' => $product->base_unit_id
                    ],
                    ['quantity' => 0]
                );
                
                if ($stockStore->quantity < $baseQuantity) {
                    throw new \Exception("Not enough stock for product {$product->name}.");
                }
                
                $stockStore->decrement('quantity', $baseQuantity);
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Sale processed successfully.',
                'invoice_number' => $sale->invoice_number,
                'receipt_url' => route('sales.receipt', $sale),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Generate sale receipt in PDF
     */
    public function receipt(Sale $sale)
    {
        $sale->load(['store', 'creator', 'saleDetails.product', 'saleDetails.unit']);
        
        $pdf = PDF::loadView('sales.receipt', compact('sale'))->setPaper([0, 0, 226.77, 650], 'portrait');
        
        return $pdf->stream('sale_' . $sale->invoice_number . '.pdf');
    }
}