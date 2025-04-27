<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shipment extends Model
{
    use HasFactory;

    protected $fillable = ['store_order_id', 'shipment_number', 'date', 'status', 'note', 'created_by', 'updated_by'];

    protected $casts = [
        'date' => 'date',
    ];

    public function storeOrder()
    {
        return $this->belongsTo(StoreOrder::class);
    }

    public function shipmentDetails()
    {
        return $this->hasMany(ShipmentDetail::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
