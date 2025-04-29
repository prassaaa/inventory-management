<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountPayable extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_id', 'supplier_id', 'amount', 'due_date',
        'status', 'paid_amount', 'payment_date', 'notes',
        'created_by', 'updated_by'
    ];

    protected $casts = [
        'due_date' => 'date',
        'payment_date' => 'date',
    ];

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
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
