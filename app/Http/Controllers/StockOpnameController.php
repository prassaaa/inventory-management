<?php

namespace App\Http\Controllers;

use App\Models\StockOpname;
use App\Models\StockOpnameDetail;
use App\Models\Store;
use App\Models\Product;
use App\Models\StockWarehouse;
use App\Models\StockStore;
use App\Models\StockAdjustment;
use App\Models\StockAdjustmentDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class StockOpnameController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $query = StockOpname::with(['creator', 'store']);
        
        // Filter by store if user is not owner/admin
        if (!Auth::user()->hasRole(['owner', 'admin_back_office'])) {
            $query->where(function($q) {
                $q->where('type', 'warehouse')
                  ->orWhere(function($q2) {
                      $q2->where('type', 'store')
                         ->where('store_id', Auth::user()->store_id);
                  });
            });
        }
        
        $stockOpnames = $query->orderBy('date', 'desc')->get();
        
        return view('stock-opnames.index', compact('stockOpnames'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $stores = Store::where('is_active', true)->orderBy('name')->get();
        
        return view('stock-opnames.create', compact('stores'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'reference' => 'required|string|max:50|unique:stock_opnames',
            'type' => 'required|in:warehouse,store',
            'store_id' => 'required_if:type,store|nullable|exists:stores,id',
            'product_ids' => 'required|array|min:1',
            'product_ids.*' => 'required|exists:products,id',
            'unit_ids' => 'required|array|min:1',
            'unit_ids.*' => 'required|exists:units,id',
            'system_stocks' => 'required|array|min:1',
            'system_stocks.*' => 'required|numeric|min:0',
            'physical_counts' => 'required|array|min:1',
            'physical_counts.*' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();
            
            // Create stock opname
            $stockOpname = StockOpname::create([
                'date' => $validated['date'],
                'reference' => $validated['reference'],
                'type' => $validated['type'],
                'store_id' => $validated['type'] === 'store' ? $validated['store_id'] : null,
                'status' => 'draft',
                'created_by' => Auth::id(),
            ]);
            
            // Create stock opname details
            foreach ($validated['product_ids'] as $index => $productId) {
                $unitId = $validated['unit_ids'][$index];
                $systemStock = $validated['system_stocks'][$index];
                $physicalStock = $validated['physical_counts'][$productId] ?? 0;
                $difference = $physicalStock - $systemStock;
                
                StockOpnameDetail::create([
                    'stock_opname_id' => $stockOpname->id,
                    'product_id' => $productId,
                    'unit_id' => $unitId,
                    'system_stock' => $systemStock,
                    'physical_stock' => $physicalStock,
                    'difference' => $difference,
                ]);
            }
            
            DB::commit();
            
            return redirect()->route('stock-opnames.show', $stockOpname)
                ->with('success', 'Stock opname created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()
                ->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(StockOpname $stockOpname)
    {
        // Check access permission
        if ($stockOpname->type === 'store' && 
            $stockOpname->store_id !== Auth::user()->store_id && 
            !Auth::user()->hasRole(['owner', 'admin_back_office'])) {
            abort(403, 'Unauthorized action.');
        }
        
        $stockOpname->load(['creator', 'updater', 'store', 'stockOpnameDetails.product', 'stockOpnameDetails.unit']);
        
        return view('stock-opnames.show', compact('stockOpname'));
    }

    /**
     * Confirm stock opname and create adjustment.
     */
    public function confirm(Request $request, StockOpname $stockOpname)
    {
        // Check if already confirmed
        if ($stockOpname->status === 'confirmed') {
            return redirect()->route('stock-opnames.show', $stockOpname)
                ->with('error', 'Stock opname is already confirmed.');
        }
        
        try {
            DB::beginTransaction();
            
            // Update stock opname status
            $stockOpname->update([
                'status' => 'confirmed',
                'updated_by' => Auth::id(),
            ]);
            
            // Create stock adjustment if there are differences
            $hasAdjustments = false;
            foreach ($stockOpname->stockOpnameDetails as $detail) {
                if ($detail->difference != 0) {
                    $hasAdjustments = true;
                    break;
                }
            }
            
            if ($hasAdjustments) {
                // Create stock adjustment
                $stockAdjustment = StockAdjustment::create([
                    'date' => now(),
                    'reference' => 'ADJ-OPN-' . $stockOpname->id,
                    'type' => $stockOpname->type,
                    'store_id' => $stockOpname->store_id,
                    'created_by' => Auth::id(),
                ]);
                
                // Create adjustment details and update stock
                foreach ($stockOpname->stockOpnameDetails as $detail) {
                    if ($detail->difference == 0) {
                        continue;
                    }
                    
                    $adjustmentType = $detail->difference > 0 ? 'addition' : 'reduction';
                    $adjustmentQuantity = abs($detail->difference);
                    
                    // Create adjustment detail
                    StockAdjustmentDetail::create([
                        'stock_adjustment_id' => $stockAdjustment->id,
                        'product_id' => $detail->product_id,
                        'unit_id' => $detail->unit_id,
                        'quantity' => $adjustmentQuantity,
                        'type' => $adjustmentType,
                        'reason' => 'Stock opname adjustment: ' . $stockOpname->reference,
                    ]);
                    
                    // Update stock
                    $product = Product::findOrFail($detail->product_id);
                    
                    if ($stockOpname->type === 'warehouse') {
                        // Update warehouse stock
                        $stockWarehouse = StockWarehouse::firstOrCreate(
                            ['product_id' => $detail->product_id, 'unit_id' => $detail->unit_id],
                            ['quantity' => 0]
                        );
                        
                        if ($adjustmentType === 'addition') {
                            $stockWarehouse->increment('quantity', $adjustmentQuantity);
                        } else {
                            $stockWarehouse->decrement('quantity', $adjustmentQuantity);
                        }
                    } else {
                        // Update store stock
                        $stockStore = StockStore::firstOrCreate(
                            [
                                'store_id' => $stockOpname->store_id,
                                'product_id' => $detail->product_id,
                                'unit_id' => $detail->unit_id
                            ],
                            ['quantity' => 0]
                        );
                        
                        if ($adjustmentType === 'addition') {
                            $stockStore->increment('quantity', $adjustmentQuantity);
                        } else {
                            $stockStore->decrement('quantity', $adjustmentQuantity);
                        }
                    }
                }
            }
            
            DB::commit();
            
            return redirect()->route('stock-opnames.show', $stockOpname)
                ->with('success', 'Stock opname confirmed successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('stock-opnames.show', $stockOpname)
                ->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
        * Get products for stock opname via AJAX.
        */
    public function getProducts(Request $request)
    {
        $type = $request->input('type', 'warehouse');
        $storeId = $request->input('store_id');
        
        if ($type === 'store' && !$storeId) {
            return response()->json([
                'success' => false,
                'message' => 'Store ID is required for store type.'
            ], 400);
        }
        
        try {
            $products = [];
            
            if ($type === 'warehouse') {
                // Get products with warehouse stock
                $productsQuery = Product::with('baseUnit')
                    ->select('products.*', DB::raw('COALESCE(stock_warehouses.quantity, 0) as stock_quantity'))
                    ->leftJoin('stock_warehouses', function($join) {
                        $join->on('products.id', '=', 'stock_warehouses.product_id')
                                ->where('stock_warehouses.unit_id', '=', DB::raw('products.base_unit_id'));
                    })
                    ->where('products.is_active', true)
                    ->orderBy('products.name')
                    ->get();
                
                foreach ($productsQuery as $product) {
                    $products[] = [
                        'id' => $product->id,
                        'code' => $product->code,
                        'name' => $product->name,
                        'stock_quantity' => $product->stock_quantity,
                        'unit_id' => $product->base_unit_id,
                        'unit_name' => $product->baseUnit->name
                    ];
                }
            } else {
                // Get products with store stock
                $productsQuery = Product::with('baseUnit')
                    ->select('products.*', DB::raw('COALESCE(stock_stores.quantity, 0) as stock_quantity'))
                    ->leftJoin('stock_stores', function($join) use ($storeId) {
                        $join->on('products.id', '=', 'stock_stores.product_id')
                                ->where('stock_stores.unit_id', '=', DB::raw('products.base_unit_id'))
                                ->where('stock_stores.store_id', '=', $storeId);
                    })
                    ->where('products.is_active', true)
                    ->orderBy('products.name')
                    ->get();
                
                foreach ($productsQuery as $product) {
                    $products[] = [
                        'id' => $product->id,
                        'code' => $product->code,
                        'name' => $product->name,
                        'stock_quantity' => $product->stock_quantity,
                        'unit_id' => $product->base_unit_id,
                        'unit_name' => $product->baseUnit->name
                    ];
                }
            }
            
            return response()->json([
                'success' => true,
                'products' => $products
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}