<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoreOrder extends Model
{
    // Konstanta untuk status
    const STATUS_PENDING = 'pending';
    const STATUS_CONFIRMED_BY_ADMIN = 'confirmed_by_admin';
    const STATUS_FORWARDED_TO_WAREHOUSE = 'forwarded_to_warehouse';
    const STATUS_SHIPPED = 'shipped';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_COMPLETED = 'completed';

    protected $fillable = [
        'store_id',
        'order_number',
        'date',
        'status',
        'payment_type',
        'due_date',
        'total_amount',
        'confirmed_at',
        'forwarded_at',
        'shipped_at',
        'delivered_at',
        'completed_at',
        'note',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'date' => 'datetime',
        'due_date' => 'datetime',
        'confirmed_at' => 'datetime',
        'forwarded_at' => 'datetime',
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
        'completed_at' => 'datetime',
        'total_amount' => 'decimal:2'
    ];

    // Relasi yang sudah ada
    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function storeOrderDetails()
    {
        return $this->hasMany(StoreOrderDetail::class);
    }

    public function shipments()
    {
        return $this->hasMany(Shipment::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // Method untuk mendapatkan total nilai pesanan
    public function getTotalAttribute()
    {
        return $this->storeOrderDetails->sum('subtotal');
    }

    // Status badge untuk tampilan
    public function getStatusBadgeAttribute()
    {
        switch ($this->status) {
            case self::STATUS_PENDING:
                return '<span class="badge bg-warning">Pending</span>';
            case self::STATUS_CONFIRMED_BY_ADMIN:
                return '<span class="badge bg-info">Dikonfirmasi Admin</span>';
            case self::STATUS_FORWARDED_TO_WAREHOUSE:
                return '<span class="badge bg-primary">Diteruskan ke Gudang</span>';
            case self::STATUS_SHIPPED:
                return '<span class="badge bg-secondary">Dikirim</span>';
            case self::STATUS_DELIVERED:
                return '<span class="badge bg-success">Diterima</span>';
            case self::STATUS_COMPLETED:
                return '<span class="badge bg-success">Selesai</span>';
            default:
                return '<span class="badge bg-secondary">' . ucfirst($this->status) . '</span>';
        }
    }

    // Tambahkan getter untuk payment type badge
    public function getPaymentTypeBadgeAttribute()
    {
        switch ($this->payment_type) {
            case 'cash':
                return '<span class="badge bg-success bg-opacity-10 text-success">Tunai</span>';
            case 'credit':
                return '<span class="badge bg-warning bg-opacity-10 text-warning">Kredit</span>';
            default:
                return '<span class="badge bg-secondary bg-opacity-10 text-secondary">' . ucfirst($this->payment_type ?? 'N/A') . '</span>';
        }
    }
}
