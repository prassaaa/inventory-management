<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StoreReturn extends Model
{
    use HasFactory;

    protected $fillable = ['store_id', 'date', 'total_amount', 'note', 'created_by', 'updated_by'];
    
    protected $casts = [
        'date' => 'date',
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function storeReturnDetails()
    {
        return $this->hasMany(StoreReturnDetail::class);
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