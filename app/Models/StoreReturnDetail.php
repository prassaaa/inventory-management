<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StoreReturnDetail extends Model
{
    use HasFactory;

    protected $fillable = ['store_return_id', 'product_id', 'unit_id', 'quantity', 'reason'];

    public function storeReturn()
    {
        return $this->belongsTo(StoreReturn::class);
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