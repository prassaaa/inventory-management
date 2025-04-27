<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StoreOrder;
use App\Models\StoreOrderDetail;
use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\User;
use App\Models\Store;
use App\Models\Unit;
use App\Models\StockStore;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BackOfficeStoreOrderController extends Controller
{
    public function index()
    {
        // Filter berdasarkan role user
        if (Auth::user()->hasRole('admin_store')) {
            // Admin store hanya bisa melihat pesanan dari toko mereka
            $storeOrders = StoreOrder::where('store_id', Auth::user()->store_id)
                ->with(['store', 'createdBy'])
                ->orderBy('created_at', 'desc')
                ->paginate(10);
        } elseif (Auth::user()->hasRole('admin_gudang')) {
            // Admin gudang hanya melihat pesanan yang sudah dikonfirmasi atau diteruskan
            $storeOrders = StoreOrder::whereIn('status', [
                    StoreOrder::STATUS_FORWARDED_TO_WAREHOUSE,
                    StoreOrder::STATUS_SHIPPED
                ])
                ->with(['store', 'createdBy'])
                ->orderBy('created_at', 'desc')
                ->paginate(10);
        } else {
            // Admin pusat/owner melihat semua pesanan
            $storeOrders = StoreOrder::with(['store', 'createdBy'])
                ->orderBy('created_at', 'desc')
                ->paginate(10);
        }

        return view('store-orders.index', compact('storeOrders'));
    }

    public function create()
    {
        // Hanya admin store yang bisa membuat pesanan
        if (!Auth::user()->hasRole('admin_store')) {
            return redirect()->route('store-orders.index')
                ->with('error', 'Anda tidak memiliki izin untuk membuat pesanan.');
        }

        $store = Auth::user()->store;
        $products = Product::where('store_source', 'pusat')
            ->where('is_active', true)
            ->get();

        // Ambil semua unit untuk dropdown
        $units = Unit::all();

        return view('store-orders.create', compact('store', 'products', 'units'));
    }

    public function store(Request $request)
    {
        // Validasi input
        $request->validate([
            'product_id' => 'required|array',
            'product_id.*' => 'required|exists:products,id',
            'unit_id' => 'required|array',
            'unit_id.*' => 'required|exists:units,id',
            'quantity' => 'required|array',
            'quantity.*' => 'required|numeric|min:1',
        ]);

        DB::beginTransaction();
        try {
            // Buat pesanan
            $storeOrder = StoreOrder::create([
                'store_id' => Auth::user()->store_id,
                'order_number' => 'ORD-' . date('YmdHis'),
                'date' => now(),
                'status' => StoreOrder::STATUS_PENDING,
                'note' => $request->note,
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
            ]);

            // Tambahkan detail pesanan
            foreach ($request->product_id as $index => $productId) {
                if ($request->quantity[$index] > 0) {
                    // Dapatkan harga produk berdasarkan unit
                    $productUnit = ProductUnit::where('product_id', $productId)
                        ->where('unit_id', $request->unit_id[$index])
                        ->first();

                    $price = $productUnit ? $productUnit->purchase_price : Product::find($productId)->purchase_price;

                    StoreOrderDetail::create([
                        'store_order_id' => $storeOrder->id,
                        'product_id' => $productId,
                        'unit_id' => $request->unit_id[$index],
                        'quantity' => $request->quantity[$index],
                        'price' => $price,
                        'subtotal' => $price * $request->quantity[$index],
                    ]);
                }
            }

            // Kirim notifikasi ke admin pusat
            $adminUsers = User::role('admin_back_office')->get();
            foreach ($adminUsers as $admin) {
                // Implementasi notifikasi akan ditambahkan nanti
            }

            DB::commit();

            return redirect()->route('store-orders.index')
                ->with('success', 'Pesanan berhasil dibuat dan menunggu konfirmasi dari pusat.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat membuat pesanan: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function show($id)
    {
        // Cek akses sesuai role
        $storeOrder = StoreOrder::with(['store', 'storeOrderDetails.product', 'storeOrderDetails.unit', 'shipments'])
            ->findOrFail($id);

        // Jika admin store, pastikan hanya bisa lihat pesanan milik tokonya
        if (Auth::user()->hasRole('admin_store') && Auth::user()->store_id != $storeOrder->store_id) {
            return redirect()->route('store-orders.index')
                ->with('error', 'Anda tidak memiliki akses ke pesanan ini.');
        }

        return view('store-orders.show', compact('storeOrder'));
    }

    public function confirm(Request $request, $id)
    {
        // Pastikan hanya admin pusat yang bisa konfirmasi
        if (!Auth::user()->hasRole(['owner', 'admin_back_office'])) {
            return redirect()->back()->with('error', 'Anda tidak memiliki izin untuk mengkonfirmasi pesanan.');
        }

        $storeOrder = StoreOrder::findOrFail($id);

        if ($storeOrder->status != StoreOrder::STATUS_PENDING) {
            return redirect()->back()->with('error', 'Pesanan ini tidak dapat dikonfirmasi (status saat ini: ' . $storeOrder->status . ').');
        }

        // Update status pesanan
        $storeOrder->status = StoreOrder::STATUS_CONFIRMED_BY_ADMIN;
        $storeOrder->confirmed_at = Carbon::now();
        $storeOrder->updated_by = Auth::id();
        $storeOrder->save();

        // Kirim notifikasi ke Admin Gudang
        $warehouseAdmins = User::role('admin_gudang')->get();
        foreach ($warehouseAdmins as $admin) {
            // Implementasi notifikasi akan ditambahkan nanti
        }

        return redirect()->route('store-orders.index')
            ->with('success', 'Pesanan berhasil dikonfirmasi dan diteruskan ke admin gudang.');
    }

    // Tambahkan method baru untuk meneruskan ke gudang
    public function forwardToWarehouse($id)
    {
        // Pastikan hanya admin pusat yang bisa meneruskan
        if (!Auth::user()->hasRole(['owner', 'admin_back_office'])) {
            return redirect()->back()->with('error', 'Anda tidak memiliki izin untuk meneruskan pesanan.');
        }

        $storeOrder = StoreOrder::findOrFail($id);

        if ($storeOrder->status != StoreOrder::STATUS_CONFIRMED_BY_ADMIN) {
            return redirect()->back()->with('error', 'Pesanan ini tidak dapat diteruskan ke gudang (status saat ini: ' . $storeOrder->status . ').');
        }

        // Update status pesanan
        $storeOrder->status = StoreOrder::STATUS_FORWARDED_TO_WAREHOUSE;
        $storeOrder->forwarded_at = Carbon::now();
        $storeOrder->updated_by = Auth::id();
        $storeOrder->save();

        return redirect()->route('store-orders.index')
            ->with('success', 'Pesanan berhasil diteruskan ke admin gudang.');
    }

    // Tambahkan method untuk konfirmasi penerimaan oleh toko
    public function confirmDelivery($id)
    {
        // Pastikan hanya admin store yang bisa konfirmasi penerimaan
        if (!Auth::user()->hasRole('admin_store')) {
            return redirect()->back()->with('error', 'Anda tidak memiliki izin untuk mengkonfirmasi penerimaan.');
        }

        $storeOrder = StoreOrder::where('store_id', Auth::user()->store_id)
            ->findOrFail($id);

        if ($storeOrder->status != StoreOrder::STATUS_SHIPPED) {
            return redirect()->back()->with('error', 'Pesanan ini tidak dapat dikonfirmasi penerimaannya (status saat ini: ' . $storeOrder->status . ').');
        }

        DB::beginTransaction();
        try {
            // Update status pesanan
            $storeOrder->status = StoreOrder::STATUS_COMPLETED;
            $storeOrder->delivered_at = Carbon::now();
            $storeOrder->completed_at = Carbon::now();
            $storeOrder->updated_by = Auth::id();
            $storeOrder->save();

            // Update status pengiriman
            $shipment = $storeOrder->shipments()->latest()->first();
            if ($shipment) {
                $shipment->status = 'delivered';
                $shipment->save();

                // Tambah stok toko berdasarkan pengiriman
                foreach ($shipment->shipmentDetails as $detail) {
                    $stockStore = StockStore::firstOrNew([
                        'store_id' => Auth::user()->store_id,
                        'product_id' => $detail->product_id,
                        'unit_id' => $detail->unit_id,
                    ]);

                    $stockStore->quantity = ($stockStore->quantity ?? 0) + $detail->quantity;
                    $stockStore->save();
                }
            }

            DB::commit();
            return redirect()->route('store-orders.index')
                ->with('success', 'Penerimaan barang berhasil dikonfirmasi dan stok toko telah ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat mengkonfirmasi penerimaan: ' . $e->getMessage());
        }
    }
}
