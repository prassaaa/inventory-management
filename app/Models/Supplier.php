<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'address', 'phone', 'email', 'payment_term', 'credit_limit', 'is_active'];

    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }
}