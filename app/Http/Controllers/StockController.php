<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Store;
use App\Models\StockWarehouse;
use App\Models\StockStore;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StockController extends Controller
{
    /**
     * Display warehouse stock.
     */
    public function warehouse()
    {
        // Get products with warehouse stock
        $products = Product::with(['category', 'baseUnit'])
            ->select('products.*', DB::raw('COALESCE(stock_warehouses.quantity, 0) as stock_quantity'))
            ->leftJoin('stock_warehouses', function($join) {
                $join->on('products.id', '=', 'stock_warehouses.product_id')
                     ->where('stock_warehouses.unit_id', '=', DB::raw('products.base_unit_id'));
            })
            ->orderBy('products.name')
            ->get();

        return view('stock.warehouse', compact('products'));
    }

    /**
     * Display store stock.
     */
    public function store()
    {
        $stores = Store::where('is_active', true)->get();

        // Jika user memiliki store_id (pengguna cabang), gunakan store tersebut
        if (Auth::user()->store_id) {
            $selectedStore = Store::findOrFail(Auth::user()->store_id);
            $canSelectStore = false; // User cabang tidak bisa pilih store lain
        } else {
            // Jika user adalah admin pusat (owner), bisa pilih store
            $canSelectStore = true;
            $selectedStoreId = request('store_id');
            
            if ($selectedStoreId) {
                $selectedStore = Store::findOrFail($selectedStoreId);
            } else {
                // Untuk owner, tidak ada default store - harus pilih sendiri
                $selectedStore = null;
            }
        }

        $products = collect(); // Default empty collection

        // Ambil produk hanya jika ada store yang dipilih
        if ($selectedStore) {
            // Gunakan withTrashed() untuk mengambil produk yang sudah dihapus juga
            $query = StockStore::with(['product' => function($query) {
                            $query->withTrashed();
                        }, 'product.category', 'unit'])
                       ->where('store_id', $selectedStore->id);

            $products = $query->get();
        }

        return view('stock.store', compact('stores', 'selectedStore', 'products', 'canSelectStore'));
    }
}
