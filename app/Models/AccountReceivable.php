<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountReceivable extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_order_id', 'store_id', 'amount', 'due_date',
        'status', 'paid_amount', 'payment_date', 'notes',
        'created_by', 'updated_by'
    ];

    protected $casts = [
        'due_date' => 'date',
        'payment_date' => 'date',
    ];

    public function storeOrder()
    {
        return $this->belongsTo(StoreOrder::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // Calculate remaining amount to be paid
    public function getRemainingAmountAttribute()
    {
        return $this->amount - $this->paid_amount;
    }

    // Check if payment is overdue
    public function getIsOverdueAttribute()
    {
        return $this->status !== 'paid' && now()->greaterThan($this->due_date);
    }
}
