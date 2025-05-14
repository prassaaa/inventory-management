<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BalanceCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type', // 'asset', 'liability', 'equity'
        'description',
        'store_id', // null untuk kategori global, store_id untuk kategori khusus toko
        'is_active'
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }
}
