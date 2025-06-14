<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductStorePrice extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'store_id',
        'unit_id',
        'selling_price',
        'is_active'
    ];

    protected $casts = [
        'selling_price' => 'decimal:2',
        'is_active' => 'boolean'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * Scope untuk harga aktif
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope untuk store tertentu
     */
    public function scopeForStore($query, $storeId)
    {
        return $query->where('store_id', $storeId);
    }
}
