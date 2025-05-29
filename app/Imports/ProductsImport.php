<?php

namespace App\Imports;

use App\Models\Product;
use App\Models\Category;
use App\Models\Unit;
use App\Models\StockWarehouse;
use App\Models\StockStore;
use App\Models\Store;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Log;

class ProductsImport implements ToModel, WithHeadingRow
{
    public function __construct()
    {
        Log::info('=== ProductsImport CONSTRUCTOR CALLED ===');
    }

    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        Log::info('=== ProductsImport model() CALLED ===');
        Log::info('Raw row data received:', $row);

        // Skip empty rows
        if (empty($row['name'])) {
            Log::info('Skipping row - no name field');
            return null;
        }

        // Skip comment rows
        if (str_starts_with($row['name'], '#')) {
            Log::info('Skipping comment row');
            return null;
        }

        try {
            Log::info('Processing row for product:', ['name' => $row['name']]);

            // 1. Handle Category by ID
            $categoryId = (int)($row['category_id'] ?? 1);
            $category = Category::find($categoryId);

            if (!$category) {
                // Create default category if not found
                $category = Category::firstOrCreate(
                    ['name' => 'Default Category'],
                    [
                        'description' => 'Auto-created default category',
                        'show_in_pos' => true
                    ]
                );
                Log::info('Created default category:', ['id' => $category->id]);
            }

            Log::info('Using category:', ['id' => $category->id, 'name' => $category->name]);

            // 2. Handle Unit by ID
            $unitId = (int)($row['base_unit_id'] ?? 1);
            $unit = Unit::find($unitId);

            if (!$unit) {
                // Create default unit if not found
                $unit = Unit::firstOrCreate(
                    ['name' => 'PCS'],
                    [
                        'is_base_unit' => true,
                        'conversion_factor' => 1
                    ]
                );
                Log::info('Created default unit:', ['id' => $unit->id]);
            }

            Log::info('Using unit:', ['id' => $unit->id, 'name' => $unit->name]);

            // 3. Handle Product Code
            $code = trim($row['code'] ?? '');
            if (empty($code)) {
                // Generate new code
                $latestProduct = Product::orderBy('id', 'desc')->first();
                $latestCode = $latestProduct ? $latestProduct->code : 'P0000';
                $numericPart = (int)substr($latestCode, 1);
                $code = 'P' . str_pad($numericPart + 1, 4, '0', STR_PAD_LEFT);
            }

            // Check for duplicate code
            if (Product::where('code', $code)->exists()) {
                Log::warning('Duplicate code found, generating new one:', ['original_code' => $code]);

                $latestProduct = Product::orderBy('id', 'desc')->first();
                $latestCode = $latestProduct ? $latestProduct->code : 'P0000';
                $numericPart = (int)substr($latestCode, 1);
                $code = 'P' . str_pad($numericPart + 1, 4, '0', STR_PAD_LEFT);
            }

            Log::info('Using product code:', ['code' => $code]);

            // 4. Process other fields
            $purchasePrice = (float)($row['purchase_price'] ?? 0);
            $sellingPrice = (float)($row['selling_price'] ?? 0);
            $minStock = (float)($row['min_stock'] ?? 0);
            $isActive = in_array(strtolower($row['is_active'] ?? '1'), ['1', 'true', 'active', 'aktif']);
            $storeSource = in_array(strtolower($row['store_source'] ?? 'pusat'), ['toko', 'store']) ? 'toko' : 'pusat';

            Log::info('Processed fields:', [
                'purchase_price' => $purchasePrice,
                'selling_price' => $sellingPrice,
                'min_stock' => $minStock,
                'is_active' => $isActive,
                'store_source' => $storeSource
            ]);

            // 5. Create Product
            $productData = [
                'code' => $code,
                'name' => trim($row['name']),
                'description' => isset($row['description']) ? trim($row['description']) : null,
                'category_id' => $category->id,
                'base_unit_id' => $unit->id,
                'purchase_price' => $purchasePrice,
                'selling_price' => $sellingPrice,
                'min_stock' => $minStock,
                'is_active' => $isActive,
                'store_source' => $storeSource,
                'store_id' => null,
                'is_processed' => false
            ];

            Log::info('Creating product with data:', $productData);

            $product = Product::create($productData);

            Log::info('Product created successfully:', [
                'id' => $product->id,
                'code' => $product->code,
                'name' => $product->name
            ]);

            // 6. Create Initial Stock
            $initialStock = (float)($row['initial_stock'] ?? 0);

            if ($storeSource === 'pusat') {
                // Create warehouse stock
                StockWarehouse::create([
                    'product_id' => $product->id,
                    'unit_id' => $unit->id,
                    'quantity' => $initialStock
                ]);

                Log::info('Created warehouse stock:', [
                    'product_id' => $product->id,
                    'quantity' => $initialStock
                ]);
            } else {
                // Create store stocks for all stores
                $stores = Store::all();
                foreach ($stores as $store) {
                    StockStore::create([
                        'store_id' => $store->id,
                        'product_id' => $product->id,
                        'unit_id' => $unit->id,
                        'quantity' => $initialStock
                    ]);
                }

                Log::info('Created store stocks:', [
                    'product_id' => $product->id,
                    'stores_count' => $stores->count(),
                    'quantity_each' => $initialStock
                ]);
            }

            Log::info('=== PRODUCT IMPORT SUCCESS ===', [
                'product_id' => $product->id,
                'product_code' => $product->code,
                'product_name' => $product->name
            ]);

            return $product;

        } catch (\Exception $e) {
            Log::error('=== PRODUCT IMPORT ERROR ===', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'row_data' => $row,
                'trace' => $e->getTraceAsString()
            ]);

            // Return null to continue with other rows
            return null;
        }
    }
}
