<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InitialBalance extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'category_id', // Kategori saldo
        'amount', // Jumlah saldo
        'notes',
        'store_id', // null untuk global, store_id untuk saldo toko tertentu
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function category()
    {
        return $this->belongsTo(BalanceCategory::class, 'category_id');
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
}
