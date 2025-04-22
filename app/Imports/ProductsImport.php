<?php

namespace App\Imports;

use App\Models\Product;
use App\Models\Category;
use App\Models\Unit;
use App\Models\StockWarehouse;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Str;

class ProductsImport implements ToModel, WithHeadingRow, WithValidation
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        // Find or create category
        $category = Category::firstOrCreate(
            ['name' => $row['category']],
            ['description' => '']
        );
        
        // Find base unit
        $baseUnit = Unit::where('name', $row['base_unit'])->first();
        if (!$baseUnit) {
            // Create base unit if it doesn't exist
            $baseUnit = Unit::create([
                'name' => $row['base_unit'],
                'is_base_unit' => true,
                'conversion_factor' => 1
            ]);
        }
        
        // Generate product code if not provided
        $code = $row['code'] ?? null;
        if (empty($code)) {
            $latestProduct = Product::latest()->first();
            $latestCode = $latestProduct ? $latestProduct->code : 'P0000';
            $numericPart = (int)substr($latestCode, 1);
            $code = 'P' . str_pad($numericPart + 1, 4, '0', STR_PAD_LEFT);
        }
        
        // Create product
        $product = Product::create([
            'code' => $code,
            'name' => $row['name'],
            'description' => $row['description'] ?? null,
            'category_id' => $category->id,
            'base_unit_id' => $baseUnit->id,
            'purchase_price' => $row['purchase_price'],
            'selling_price' => $row['selling_price'],
            'min_stock' => $row['min_stock'] ?? 0,
            'is_active' => ($row['status'] ?? 'active') == 'active',
            'store_source' => 'pusat'
        ]);
        
        // Create initial warehouse stock
        StockWarehouse::create([
            'product_id' => $product->id,
            'unit_id' => $product->base_unit_id,
            'quantity' => 0
        ]);
        
        return $product;
    }
    
    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'base_unit' => 'required|string|max:50',
            'purchase_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0'
        ];
    }
    
    /**
     * @return array
     */
    public function customValidationMessages()
    {
        return [
            'name.required' => 'Product name is required.',
            'category.required' => 'Category is required.',
            'base_unit.required' => 'Base unit is required.',
            'purchase_price.required' => 'Purchase price is required.',
            'selling_price.required' => 'Selling price is required.'
        ];
    }
}