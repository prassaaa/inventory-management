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
}
