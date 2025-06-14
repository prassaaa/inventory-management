<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_id', 'invoice_number', 'date', 'customer_name', 'total_amount',
        'payment_type', 'dining_option', 'discount', 'tax', 'tax_enabled',
        'total_payment', 'change', 'status', 'created_by', 'updated_by'
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function getDiningOptionTextAttribute()
    {
        return $this->dining_option === 'makan_di_tempat' ? 'Makan di Tempat' : 'Dibawa Pulang';
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function saleDetails()
    {
        return $this->hasMany(SaleDetail::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
