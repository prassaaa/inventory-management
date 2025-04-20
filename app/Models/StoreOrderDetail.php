<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StoreOrderDetail extends Model
{
    use HasFactory;

    protected $fillable = ['store_order_id', 'product_id', 'unit_id', 'quantity', 'price', 'subtotal'];

    public function storeOrder()
    {
        return $this->belongsTo(StoreOrder::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }
}