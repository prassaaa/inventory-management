<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpenseCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'store_id', // null untuk kategori global, store_id untuk kategori khusus toko
        'is_active'
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class, 'category_id');
    }
}
