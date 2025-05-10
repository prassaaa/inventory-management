<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Unit;
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
}
