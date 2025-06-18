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

    /**
     * Helper method untuk mencari kategori Biaya Operasional
     */
    public static function findOperationalCategory()
    {
        return self::where(function($query) {
                $query->where('name', 'Biaya Operasional')
                    ->orWhere('name', 'LIKE', '%operasional%');
            })
            ->whereNull('store_id')
            ->where('is_active', true)
            ->first();
    }

    /**
     * Helper method untuk mencari kategori Beban Biaya Transportasi
     */
    public static function findTransportationCategory()
    {
        return self::where(function($query) {
                $query->where('name', 'Beban Biaya Transportasi')
                    ->orWhere('name', 'LIKE', '%transportasi%')
                    ->orWhere('name', 'LIKE', '%transport%');
            })
            ->whereNull('store_id')
            ->where('is_active', true)
            ->first();
    }
}
