<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductStorePrice;
use App\Models\Store;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProductStorePriceController extends Controller
{
    /**
     * Tampilkan daftar produk dengan harga store
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Jika user adalah owner_store, hanya tampilkan untuk store mereka
        if ($user->hasRole('owner_store') && $user->store_id) {
            $storeId = $user->store_id;
            $store = Store::findOrFail($storeId);

            // Ambil produk dengan harga store
            $products = Product::with([
                'category',
                'baseUnit',
                'productUnits.unit',
                'storePrices' => function($query) use ($storeId) {
                    $query->where('store_id', $storeId)->where('is_active', true);
                }
            ])
            ->where('is_active', true)
            ->orderBy('name')
            ->paginate(20);

            return view('store-prices.index', compact('products', 'store'));
        }

        // Untuk admin pusat dan owner, bisa pilih store
        $stores = Store::where('is_active', true)->orderBy('name')->get();
        $selectedStoreId = $request->get('store_id');
        $selectedStore = null;
        $products = collect();

        if ($selectedStoreId) {
            $selectedStore = Store::find($selectedStoreId);
            if ($selectedStore) {
                $products = Product::with([
                    'category',
                    'baseUnit',
                    'productUnits.unit',
                    'storePrices' => function($query) use ($selectedStoreId) {
                        $query->where('store_id', $selectedStoreId)->where('is_active', true);
                    }
                ])
                ->where('is_active', true)
                ->orderBy('name')
                ->paginate(20);
            }
        }

        return view('store-prices.index-admin', compact('products', 'stores', 'selectedStore'));
    }

    /**
     * Form edit harga produk untuk store - FIXED VERSION
     */
    public function edit($productId)
    {
        $user = Auth::user();
        $product = Product::with(['baseUnit', 'productUnits.unit'])->findOrFail($productId);

        if ($user->hasRole('owner_store') && $user->store_id) {
            $store = Store::findOrFail($user->store_id);

            // Ambil harga yang sudah ada untuk store ini - FIXED QUERY
            $storePrices = ProductStorePrice::where('product_id', $productId)
                ->where('store_id', $store->id)
                ->where('is_active', true)
                ->get()
                ->keyBy('unit_id');

            Log::info('Loading store prices for edit', [
                'product_id' => $productId,
                'store_id' => $store->id,
                'found_prices' => $storePrices->count(),
                'price_details' => $storePrices->toArray()
            ]);

            return view('store-prices.edit', compact('product', 'store', 'storePrices'));
        }

        // Admin pusat dan owner bisa pilih store
        $stores = Store::where('is_active', true)->get();

        // Jika ada store_id di request, ambil harga untuk store tersebut
        if (request('store_id')) {
            $selectedStore = Store::find(request('store_id'));
            $storePrices = ProductStorePrice::where('product_id', $productId)
                ->where('store_id', request('store_id'))
                ->where('is_active', true)
                ->get()
                ->keyBy('unit_id');

            Log::info('Loading store prices for admin edit', [
                'product_id' => $productId,
                'store_id' => request('store_id'),
                'found_prices' => $storePrices->count(),
                'price_details' => $storePrices->toArray()
            ]);

            return view('store-prices.edit-admin', compact('product', 'stores', 'selectedStore', 'storePrices'));
        }

        return view('store-prices.edit-admin', compact('product', 'stores'));
    }

    /**
     * Update harga produk untuk store
     */
    public function update(Request $request, $productId)
    {
        $user = Auth::user();
        $product = Product::findOrFail($productId);

        $validated = $request->validate([
            'store_id' => 'required|exists:stores,id',
            'base_price' => 'required|numeric|min:0',
            'unit_prices' => 'nullable|array',
            'unit_prices.*.unit_id' => 'required|exists:units,id',
            'unit_prices.*.selling_price' => 'required|numeric|min:0',
        ]);

        $storeId = $validated['store_id'];

        // Cek permission - owner_store hanya bisa edit store mereka sendiri
        if ($user->hasRole('owner_store') && $user->store_id != $storeId) {
            abort(403, 'Unauthorized');
        }

        DB::beginTransaction();
        try {
            Log::info('Updating store prices', [
                'product_id' => $productId,
                'store_id' => $storeId,
                'base_price' => $validated['base_price'],
                'unit_prices' => $validated['unit_prices'] ?? []
            ]);

            // Update harga untuk unit dasar
            $basePrice = ProductStorePrice::updateOrCreate(
                [
                    'product_id' => $productId,
                    'store_id' => $storeId,
                    'unit_id' => $product->base_unit_id
                ],
                [
                    'selling_price' => $validated['base_price'],
                    'is_active' => true
                ]
            );

            Log::info('Base price updated', [
                'id' => $basePrice->id,
                'price' => $basePrice->selling_price,
                'unit_id' => $basePrice->unit_id
            ]);

            // Update harga untuk unit tambahan
            if (isset($validated['unit_prices'])) {
                foreach ($validated['unit_prices'] as $unitPrice) {
                    $unitPriceRecord = ProductStorePrice::updateOrCreate(
                        [
                            'product_id' => $productId,
                            'store_id' => $storeId,
                            'unit_id' => $unitPrice['unit_id']
                        ],
                        [
                            'selling_price' => $unitPrice['selling_price'],
                            'is_active' => true
                        ]
                    );

                    Log::info('Unit price updated', [
                        'id' => $unitPriceRecord->id,
                        'price' => $unitPriceRecord->selling_price,
                        'unit_id' => $unitPriceRecord->unit_id
                    ]);
                }
            }

            DB::commit();

            Log::info('Store price updated successfully', [
                'product_id' => $productId,
                'store_id' => $storeId,
                'user_id' => $user->id
            ]);

            return redirect()->route('store-prices.index', ['store_id' => $storeId])
                ->with('success', 'Harga produk berhasil diperbarui');

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error updating store price', [
                'product_id' => $productId,
                'store_id' => $storeId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * API endpoint untuk mendapatkan harga saat ini
     */
    public function getCurrentPrices(Request $request, $productId)
    {
        $storeId = $request->get('store_id');

        if (!$storeId) {
            return response()->json([
                'success' => false,
                'message' => 'Store ID diperlukan'
            ]);
        }

        try {
            $product = Product::with(['baseUnit', 'productUnits.unit'])->findOrFail($productId);

            // Ambil harga custom untuk store ini
            $storePrices = ProductStorePrice::where('product_id', $productId)
                ->where('store_id', $storeId)
                ->where('is_active', true)
                ->get()
                ->keyBy('unit_id');

            $data = [
                'base_price' => $storePrices->get($product->base_unit_id)->selling_price ?? $product->selling_price,
                'unit_prices' => []
            ];

            foreach ($product->productUnits as $productUnit) {
                $data['unit_prices'][$productUnit->unit_id] = $storePrices->get($productUnit->unit_id)->selling_price ?? $productUnit->selling_price;
            }

            return response()->json([
                'success' => true,
                'data' => $data
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting current prices', [
                'product_id' => $productId,
                'store_id' => $storeId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil harga'
            ], 500);
        }
    }

    /**
     * Reset harga ke default (hapus custom price)
     */
    public function resetToDefault(Request $request, $productId)
    {
        $user = Auth::user();
        $storeId = $request->store_id;

        // Cek permission - owner_store hanya bisa reset untuk store mereka sendiri
        if ($user->hasRole('owner_store') && $user->store_id != $storeId) {
            abort(403, 'Unauthorized');
        }

        try {
            $deletedCount = ProductStorePrice::where('product_id', $productId)
                ->where('store_id', $storeId)
                ->delete();

            Log::info('Store price reset to default', [
                'product_id' => $productId,
                'store_id' => $storeId,
                'deleted_count' => $deletedCount,
                'user_id' => $user->id
            ]);

            return back()->with('success', 'Harga produk dikembalikan ke default');
        } catch (\Exception $e) {
            Log::error('Error resetting store price', [
                'product_id' => $productId,
                'store_id' => $storeId,
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Bulk update harga untuk multiple produk
     */
    public function bulkUpdate(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'store_id' => 'required|exists:stores,id',
            'products' => 'required|array',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.selling_price' => 'required|numeric|min:0',
        ]);

        $storeId = $validated['store_id'];

        // Cek permission - owner_store hanya bisa bulk update untuk store mereka sendiri
        if ($user->hasRole('owner_store') && $user->store_id != $storeId) {
            abort(403, 'Unauthorized');
        }

        DB::beginTransaction();
        try {
            foreach ($validated['products'] as $productData) {
                $product = Product::find($productData['product_id']);

                ProductStorePrice::updateOrCreate(
                    [
                        'product_id' => $productData['product_id'],
                        'store_id' => $storeId,
                        'unit_id' => $product->base_unit_id
                    ],
                    [
                        'selling_price' => $productData['selling_price'],
                        'is_active' => true
                    ]
                );
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Harga berhasil diperbarui']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
