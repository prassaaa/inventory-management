<?php

namespace App\Http\Controllers;

use App\Models\Store;
use Illuminate\Http\Request;

class StoreController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view stores')->only(['index', 'show']);
        $this->middleware('permission:create stores')->only(['create', 'store']);
        $this->middleware('permission:edit stores')->only(['edit', 'update']);
        $this->middleware('permission:delete stores')->only(['destroy']);
    }

    public function index()
    {
        $stores = Store::all();
        return view('stores.index', compact('stores'));
    }

    public function create()
    {
        return view('stores.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'is_active' => 'boolean'
        ]);

        $store = Store::create([
            'name' => $request->name,
            'address' => $request->address,
            'phone' => $request->phone,
            'email' => $request->email,
            'is_active' => $request->is_active ?? true
        ]);

        return redirect()->route('stores.index')
            ->with('success', 'Toko berhasil ditambahkan');
    }

    public function show(Store $store)
    {
        // Hitung jumlah produk di toko
        $productCount = $store->stocks()->distinct('product_id')->count();
        
        // Hitung total nilai stok
        $stockValue = $store->stocks()
            ->join('products', 'stock_stores.product_id', '=', 'products.id')
            ->selectRaw('SUM(stock_stores.quantity * products.purchase_price) as total_value')
            ->first()->total_value ?? 0;
            
        // Ambil 5 transaksi penjualan terakhir
        $recentSales = $store->sales()->latest()->take(5)->get();
        
        return view('stores.show', compact('store', 'productCount', 'stockValue', 'recentSales'));
    }

    public function edit(Store $store)
    {
        return view('stores.edit', compact('store'));
    }

    public function update(Request $request, Store $store)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'is_active' => 'boolean'
        ]);

        $store->update([
            'name' => $request->name,
            'address' => $request->address,
            'phone' => $request->phone,
            'email' => $request->email,
            'is_active' => $request->has('is_active')
        ]);

        return redirect()->route('stores.index')
            ->with('success', 'Informasi toko berhasil diperbarui');
    }

    public function destroy(Store $store)
    {
        // Cek apakah toko memiliki relasi dengan data lain
        if ($store->users()->count() > 0 || 
            $store->stocks()->count() > 0 || 
            $store->sales()->count() > 0 || 
            $store->orders()->count() > 0 || 
            $store->expenses()->count() > 0) {
            return redirect()->route('stores.index')
                ->with('error', 'Toko tidak dapat dihapus karena masih memiliki data terkait');
        }

        $store->delete();

        return redirect()->route('stores.index')
            ->with('success', 'Toko berhasil dihapus');
    }
}