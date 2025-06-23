<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Unit;
use App\Models\ProductStorePrice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProductApiController extends Controller
{
    /**
     * Get ingredients for a processed product
     */
    public function getIngredients(Request $request)
    {
        $productId = $request->input('product_id');

        Log::info('API request untuk mendapatkan bahan produk', [
            'product_id' => $productId
        ]);

        if (!$productId) {
            Log::warning('API: ID produk tidak valid untuk request bahan');
            return response()->json([
                'success' => false,
                'message' => 'ID produk tidak valid'
            ]);
        }

        // Gunakan find() untuk mengambil data produk secara langsung
        $product = Product::find($productId);

        if (!$product) {
            Log::warning('API: Produk tidak ditemukan', [
                'product_id' => $productId
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Produk tidak ditemukan'
            ]);
        }

        if (!$product->is_processed) {
            Log::warning('API: Produk bukan produk olahan', [
                'product_id' => $productId,
                'product_name' => $product->name
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Produk ini bukan produk olahan'
            ]);
        }

        try {
            // Ambil bahan-bahan secara manual dengan query langsung
            $ingredientsQuery = DB::table('product_ingredients')
                ->where('product_id', $productId)
                ->get();

            Log::info('API: Bahan-bahan produk diambil', [
                'product_id' => $productId,
                'ingredients_count' => $ingredientsQuery->count()
            ]);

            $ingredients = [];

            foreach ($ingredientsQuery as $ingredientData) {
                // Ambil data produk bahan dan satuan
                $ingredient = Product::find($ingredientData->ingredient_id);
                $unit = Unit::find($ingredientData->unit_id);

                if (!$ingredient || !$unit) {
                    Log::warning('API: Data bahan atau satuan tidak ditemukan', [
                        'ingredient_id' => $ingredientData->ingredient_id,
                        'unit_id' => $ingredientData->unit_id
                    ]);
                    continue;
                }

                $ingredients[] = [
                    'id' => $ingredient->id,
                    'name' => $ingredient->name,
                    'quantity' => $ingredientData->quantity,
                    'unit_id' => $ingredientData->unit_id,
                    'unit_name' => $unit->name
                ];
            }

            Log::info('API: Mengembalikan data bahan', [
                'product_id' => $productId,
                'ingredients_count' => count($ingredients)
            ]);

            return response()->json([
                'success' => true,
                'ingredients' => $ingredients
            ]);
        } catch (\Exception $e) {
            Log::error('API: Error saat mengambil bahan produk', [
                'product_id' => $productId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data bahan: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get price for specific store
     */
    public function getStorePrice(Request $request)
    {
        $productId = $request->input('product_id');
        $storeId = $request->input('store_id');
        $unitId = $request->input('unit_id');

        Log::info('API: Get store price request', [
            'product_id' => $productId,
            'store_id' => $storeId,
            'unit_id' => $unitId
        ]);

        if (!$productId || !$storeId) {
            return response()->json([
                'success' => false,
                'message' => 'Product ID dan Store ID diperlukan'
            ]);
        }

        try {
            $product = Product::find($productId);
            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Produk tidak ditemukan'
                ]);
            }

            $price = $product->getPriceForStore($storeId, $unitId);
            $hasCustomPrice = $product->hasCustomPriceForStore($storeId);

            Log::info('API: Store price retrieved', [
                'product_id' => $productId,
                'store_id' => $storeId,
                'price' => $price,
                'has_custom_price' => $hasCustomPrice
            ]);

            return response()->json([
                'success' => true,
                'price' => $price,
                'formatted_price' => number_format($price, 0, ',', '.'),
                'has_custom_price' => $hasCustomPrice
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting store price', [
                'product_id' => $productId,
                'store_id' => $storeId,
                'unit_id' => $unitId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil harga: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get available units for a product
     */
    public function getProductUnits(Request $request)
    {
        $productId = $request->input('product_id');

        Log::info('API: Get product units request', [
            'product_id' => $productId
        ]);

        if (!$productId) {
            return response()->json([
                'success' => false,
                'message' => 'Product ID diperlukan'
            ]);
        }

        try {
            $product = Product::with(['baseUnit', 'productUnits.unit'])->find($productId);
            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Produk tidak ditemukan'
                ]);
            }

            $units = [];

            // Unit dasar
            $units[] = [
                'id' => $product->base_unit_id,
                'name' => $product->baseUnit->name,
                'is_base_unit' => true,
                'conversion_value' => 1
            ];

            // Unit tambahan
            foreach ($product->productUnits as $productUnit) {
                $units[] = [
                    'id' => $productUnit->unit_id,
                    'name' => $productUnit->unit->name,
                    'is_base_unit' => false,
                    'conversion_value' => $productUnit->conversion_value
                ];
            }

            Log::info('API: Product units retrieved', [
                'product_id' => $productId,
                'units_count' => count($units),
                'units' => $units
            ]);

            return response()->json([
                'success' => true,
                'units' => $units,
                'base_unit_id' => $product->base_unit_id
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting product units', [
                'product_id' => $productId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all prices for a product in specific store
     */
    public function getProductStorePrices(Request $request)
    {
        $productId = $request->input('product_id');
        $storeId = $request->input('store_id');

        Log::info('API: Get product store prices request', [
            'product_id' => $productId,
            'store_id' => $storeId
        ]);

        if (!$productId || !$storeId) {
            return response()->json([
                'success' => false,
                'message' => 'Product ID dan Store ID diperlukan'
            ]);
        }

        try {
            $product = Product::with(['baseUnit', 'productUnits.unit'])->find($productId);
            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Produk tidak ditemukan'
                ]);
            }

            $prices = [];
            $hasCustomPrice = $product->hasCustomPriceForStore($storeId);

            // Harga untuk unit dasar
            $baseUnitPrice = $product->getPriceForStore($storeId, $product->base_unit_id);
            $baseUnitHasCustom = ProductStorePrice::where('product_id', $productId)
                ->where('store_id', $storeId)
                ->where('unit_id', $product->base_unit_id)
                ->where('is_active', true)
                ->exists();

            // Ensure price is numeric
            $baseUnitPrice = is_numeric($baseUnitPrice) ? (float)$baseUnitPrice : 0;

            $prices[] = [
                'unit_id' => $product->base_unit_id,
                'unit_name' => $product->baseUnit->name,
                'price' => $baseUnitPrice,
                'is_base_unit' => true,
                'has_custom_price' => $baseUnitHasCustom
            ];

            // Harga untuk unit tambahan
            foreach ($product->productUnits as $productUnit) {
                $unitPrice = $product->getPriceForStore($storeId, $productUnit->unit_id);
                $unitHasCustom = ProductStorePrice::where('product_id', $productId)
                    ->where('store_id', $storeId)
                    ->where('unit_id', $productUnit->unit_id)
                    ->where('is_active', true)
                    ->exists();

                // Ensure price is numeric
                $unitPrice = is_numeric($unitPrice) ? (float)$unitPrice : 0;

                $prices[] = [
                    'unit_id' => $productUnit->unit_id,
                    'unit_name' => $productUnit->unit->name,
                    'price' => $unitPrice,
                    'is_base_unit' => false,
                    'has_custom_price' => $unitHasCustom
                ];
            }

            Log::info('API: Product store prices retrieved', [
                'product_id' => $productId,
                'store_id' => $storeId,
                'prices_count' => count($prices),
                'has_custom_price' => $hasCustomPrice,
                'prices' => $prices // Log the actual prices for debugging
            ]);

            return response()->json([
                'success' => true,
                'prices' => $prices,
                'has_custom_price' => $hasCustomPrice
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting product store prices', [
                'product_id' => $productId,
                'store_id' => $storeId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
}
