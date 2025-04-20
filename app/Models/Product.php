<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'code', 'name', 'description', 'category_id', 'base_unit_id', 
        'purchase_price', 'selling_price', 'min_stock', 'image', 'is_active', 'store_source'
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
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

    public function stockStores()
    {
        return $this->hasMany(StockStore::class);
    }
}