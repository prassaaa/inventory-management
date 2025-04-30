<?php

namespace App\Http\Controllers;

use App\Models\StockAdjustment;
use App\Models\StockAdjustmentDetail;
use App\Models\Store;
use App\Models\Product;
use App\Models\StockWarehouse;
use App\Models\StockStore;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Helpers\UnitConversion;

class StockAdjustmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $query = StockAdjustment::with(['creator', 'store']);

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

        $stockAdjustments = $query->orderBy('date', 'desc')->get();

        return view('stock-adjustments.index', compact('stockAdjustments'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $products = Product::with('baseUnit')->where('is_active', true)->orderBy('name')->get();
        $stores = Store::where('is_active', true)->orderBy('name')->get();

        // Cek apakah user memiliki store_id (cabang tertentu)
        $userHasStore = Auth::user()->store_id ? true : false;
        $userStoreId = Auth::user()->store_id;

        return view('stock-adjustments.create', compact('products', 'stores', 'userHasStore', 'userStoreId'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Cek apakah user memiliki store_id (cabang tertentu)
        $userHasStore = Auth::user()->store_id ? true : false;

        // Validasi umum
        $validationRules = [
            'date' => 'required|date',
            'reference' => 'required|string|max:50|unique:stock_adjustments',
            'product_ids' => 'required|array|min:1',
            'product_ids.*' => 'required|exists:products,id',
            'adjustment_types' => 'required|array|min:1',
            'adjustment_types.*' => 'required|in:addition,reduction',
            'quantities' => 'required|array|min:1',
            'quantities.*' => 'required|numeric|min:0.01',
            'unit_ids' => 'required|array|min:1',
            'unit_ids.*' => 'required|exists:units,id',
            'reasons' => 'nullable|array',
            'reasons.*' => 'nullable|string|max:255',
        ];

        // Jika user memiliki store_id, set type menjadi 'store' dan store_id sesuai user
        if ($userHasStore) {
            // Untuk pengguna cabang, hanya bisa melakukan penyesuaian stok di cabangnya
            $request->merge(['type' => 'store', 'store_id' => Auth::user()->store_id]);
            $validationRules['type'] = 'in:store';
            $validationRules['store_id'] = 'required|exists:stores,id';
        } else {
            // Untuk pengguna pusat (admin), bisa memilih semua tipe
            $validationRules['type'] = 'required|in:warehouse,store,all_stores';
            $validationRules['store_id'] = 'required_if:type,store|nullable|exists:stores,id';
        }

        $validated = $request->validate($validationRules);

        try {
            DB::beginTransaction();

            if ($validated['type'] === 'all_stores') {
                // Jika tipe adalah 'all_stores', buat penyesuaian untuk semua toko
                $stores = Store::where('is_active', true)->get();
                $mainReference = $validated['reference'];
                $firstAdjustment = null;

                foreach ($stores as $store) {
                    // Buat stock adjustment untuk setiap toko
                    $reference = $mainReference . '-' . $store->id;

                    $stockAdjustment = StockAdjustment::create([
                        'date' => $validated['date'],
                        'reference' => $reference,
                        'type' => 'store',
                        'store_id' => $store->id,
                        'created_by' => Auth::id(),
                    ]);

                    if (!$firstAdjustment) {
                        $firstAdjustment = $stockAdjustment;
                    }

                    // Buat detail dan update stok untuk setiap toko
                    $this->createAdjustmentDetails($stockAdjustment, $validated, $store->id);
                }

                DB::commit();

                if ($firstAdjustment) {
                    return redirect()->route('stock-adjustments.show', $firstAdjustment)
                        ->with('success', 'Penyesuaian stok untuk semua toko berhasil dibuat.');
                } else {
                    return redirect()->route('stock-adjustments.index')
                        ->with('success', 'Penyesuaian stok untuk semua toko berhasil dibuat.');
                }
            } else {
                // Jika tipe bukan 'all_stores', gunakan logika yang sudah ada
                $stockAdjustment = StockAdjustment::create([
                    'date' => $validated['date'],
                    'reference' => $validated['reference'],
                    'type' => $validated['type'],
                    'store_id' => $validated['type'] === 'store' ? $validated['store_id'] : null,
                    'created_by' => Auth::id(),
                ]);

                // Buat detail dan update stok
                $this->createAdjustmentDetails($stockAdjustment, $validated);

                DB::commit();

                return redirect()->route('stock-adjustments.show', $stockAdjustment)
                    ->with('success', 'Penyesuaian stok berhasil dibuat.');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()
                ->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Create adjustment details and update stock for a given adjustment
     */
    private function createAdjustmentDetails($stockAdjustment, $validated, $specificStoreId = null)
    {
        foreach ($validated['product_ids'] as $index => $productId) {
            $adjustmentType = $validated['adjustment_types'][$index];
            $quantity = $validated['quantities'][$index];
            $unitId = $validated['unit_ids'][$index];
            $reason = $validated['reasons'][$index] ?? null;

            // Create detail
            StockAdjustmentDetail::create([
                'stock_adjustment_id' => $stockAdjustment->id,
                'product_id' => $productId,
                'unit_id' => $unitId,
                'quantity' => $quantity,
                'type' => $adjustmentType,
                'reason' => $reason,
            ]);

            // Update stock
            $product = Product::findOrFail($productId);

            // Convert quantity to base unit if necessary
            $baseQuantity = $quantity;
            if ($unitId != $product->base_unit_id) {
                $baseQuantity = UnitConversion::convert(
                    $quantity,
                    $unitId,
                    $product->base_unit_id,
                    $productId
                );

                if ($baseQuantity === null) {
                    throw new \Exception("Cannot convert quantity for product {$product->name}");
                }
            }

            if ($stockAdjustment->type === 'warehouse') {
                // Update warehouse stock
                $stockWarehouse = StockWarehouse::firstOrCreate(
                    ['product_id' => $productId, 'unit_id' => $product->base_unit_id],
                    ['quantity' => 0]
                );

                if ($adjustmentType === 'addition') {
                    $stockWarehouse->increment('quantity', $baseQuantity);
                } else {
                    // Check if there's enough stock
                    if ($stockWarehouse->quantity < $baseQuantity) {
                        throw new \Exception("Not enough stock for product {$product->name} in warehouse");
                    }

                    $stockWarehouse->decrement('quantity', $baseQuantity);
                }
            } else {
                // Update store stock
                $storeId = $specificStoreId ?? $stockAdjustment->store_id;

                $stockStore = StockStore::firstOrCreate(
                    [
                        'store_id' => $storeId,
                        'product_id' => $productId,
                        'unit_id' => $product->base_unit_id
                    ],
                    ['quantity' => 0]
                );

                if ($adjustmentType === 'addition') {
                    $stockStore->increment('quantity', $baseQuantity);
                } else {
                    // Check if there's enough stock
                    if ($stockStore->quantity < $baseQuantity) {
                        throw new \Exception("Not enough stock for product {$product->name} in store");
                    }

                    $stockStore->decrement('quantity', $baseQuantity);
                }
            }
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(StockAdjustment $stockAdjustment)
    {
        // Check access permission
        if ($stockAdjustment->type === 'store' &&
            $stockAdjustment->store_id !== Auth::user()->store_id &&
            !Auth::user()->hasRole(['owner', 'admin_back_office'])) {
            abort(403, 'Unauthorized action.');
        }

        $stockAdjustment->load(['creator', 'store', 'stockAdjustmentDetails.product', 'stockAdjustmentDetails.unit']);

        return view('stock-adjustments.show', compact('stockAdjustment'));
    }
}
