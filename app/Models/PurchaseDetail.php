<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_id',
        'product_id',
        'unit_id',
        'quantity',
        'price',
        'subtotal'
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'price' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function purchaseReturnDetails()
    {
        return $this->hasMany(PurchaseReturnDetail::class, 'purchase_detail_id');
    }

    /**
     * Calculate total returned quantity for this purchase detail
     */
    public function returnedQuantity()
    {
        return $this->purchaseReturnDetails()->sum('quantity');
    }

    /**
     * Get available quantity that can still be returned
     */
    public function getAvailableQuantityAttribute()
    {
        return $this->quantity - $this->returnedQuantity();
    }
}
