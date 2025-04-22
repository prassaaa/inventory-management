<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Store extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'address', 'phone', 'email', 'is_active'];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function stockStores()
    {
        return $this->hasMany(StockStore::class);
    }

    public function storeOrders()
    {
        return $this->hasMany(StoreOrder::class);
    }

    public function storeReturns()
    {
        return $this->hasMany(StoreReturn::class);
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    public function stocks()
    {
        return $this->hasMany(StockStore::class);
    }
}