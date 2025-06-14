<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code', 'name', 'description', 'category_id', 'base_unit_id',
        'purchase_price', 'selling_price', 'min_stock', 'image', 'is_active',
        'store_source', 'store_id', 'is_processed'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_processed' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function units()
    {
        return $this->belongsToMany(Unit::class, 'product_units')
            ->withPivot('conversion_value', 'purchase_price', 'selling_price');
    }

    public function baseUnit()
    {
        return $this->belongsTo(Unit::class, 'base_unit_id');
    }

    public function productUnits()
    {
        return $this->hasMany(ProductUnit::class);
    }

    public function stockWarehouses()
    {
        return $this->hasMany(StockWarehouse::class);
    }

    public function warehouseStock()
    {
        return $this->hasOne(StockWarehouse::class);
    }

    public function stockStores()
    {
        return $this->hasMany(StockStore::class);
    }

    public function storeStocks()
    {
        return $this->hasMany(StockStore::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    // Relasi untuk produk olahan dan bahan-bahannya
    public function ingredients()
    {
        return $this->belongsToMany(Product::class, 'product_ingredients', 'product_id', 'ingredient_id')
            ->withPivot('quantity', 'unit_id')
            ->withTimestamps();
    }

    public function processedProducts()
    {
        return $this->belongsToMany(Product::class, 'product_ingredients', 'ingredient_id', 'product_id')
            ->withPivot('quantity', 'unit_id')
            ->withTimestamps();
    }
    /**
     * Relasi untuk harga khusus di setiap store
     */
    public function storePrices()
    {
        return $this->hasMany(ProductStorePrice::class);
    }

    /**
     * Dapatkan harga untuk store tertentu
     */
    public function getPriceForStore($storeId, $unitId = null)
    {
        $unitId = $unitId ?? $this->base_unit_id;

        // Cari harga khusus untuk store ini
        $storePrice = $this->storePrices()
            ->where('store_id', $storeId)
            ->where('unit_id', $unitId)
            ->where('is_active', true)
            ->first();

        if ($storePrice) {
            // Pastikan harga berupa angka
            return is_numeric($storePrice->selling_price) ? (float)$storePrice->selling_price : 0;
        }

        // Fallback ke harga default
        if ($unitId != $this->base_unit_id) {
            $productUnit = $this->productUnits()
                ->where('unit_id', $unitId)
                ->first();

            if ($productUnit) {
                return is_numeric($productUnit->selling_price) ? (float)$productUnit->selling_price : 0;
            }
        }

        // Return base selling price, ensure it's numeric
        return is_numeric($this->selling_price) ? (float)$this->selling_price : 0;
    }

    /**
     * Cek apakah produk ini punya harga khusus untuk store
     */
    public function hasCustomPriceForStore($storeId)
    {
        return $this->storePrices()
            ->where('store_id', $storeId)
            ->where('is_active', true)
            ->exists();
    }

    /**
     * Dapatkan semua harga untuk store tertentu
     */
    public function getStorePrices($storeId)
    {
        return $this->storePrices()
            ->where('store_id', $storeId)
            ->where('is_active', true)
            ->with('unit')
            ->get();
    }
}
