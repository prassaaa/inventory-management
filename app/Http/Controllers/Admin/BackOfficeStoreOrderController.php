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
use App\Models\AccountReceivable;
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
            'payment_type' => 'required|in:cash,credit', // Validasi metode pembayaran
            'due_date' => 'required_if:payment_type,credit|nullable|date', // Validasi jatuh tempo
        ]);

        DB::beginTransaction();
        try {
            // Buat pesanan
            $storeOrder = StoreOrder::create([
                'store_id' => Auth::user()->store_id,
                'order_number' => 'ORD-' . date('YmdHis'),
                'date' => now(),
                'status' => StoreOrder::STATUS_PENDING,
                'payment_type' => $request->payment_type, // Simpan metode pembayaran
                'due_date' => $request->payment_type === 'credit' ? $request->due_date : null, // Simpan jatuh tempo
                'note' => $request->note,
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
            ]);

            // Tambahkan detail pesanan dan hitung total amount
            $totalAmount = 0;
            foreach ($request->product_id as $index => $productId) {
                if ($request->quantity[$index] > 0) {
                    // Dapatkan harga produk berdasarkan unit
                    $productUnit = ProductUnit::where('product_id', $productId)
                        ->where('unit_id', $request->unit_id[$index])
                        ->first();

                    $price = $productUnit ? $productUnit->purchase_price : Product::find($productId)->purchase_price;
                    $subtotal = $price * $request->quantity[$index];
                    $totalAmount += $subtotal;

                    StoreOrderDetail::create([
                        'store_order_id' => $storeOrder->id,
                        'product_id' => $productId,
                        'unit_id' => $request->unit_id[$index],
                        'quantity' => $request->quantity[$index],
                        'price' => $price,
                        'subtotal' => $subtotal,
                    ]);
                }
            }

            // Update total amount
            $storeOrder->update([
                'total_amount' => $totalAmount
            ]);

            // Cek apakah pemesanan menggunakan metode kredit
            if ($request->payment_type === 'credit') {
                // Buat catatan piutang dari toko
                AccountReceivable::create([
                    'store_order_id' => $storeOrder->id,
                    'store_id' => Auth::user()->store_id,
                    'amount' => $totalAmount,
                    'due_date' => $request->due_date,
                    'status' => 'unpaid',
                    'paid_amount' => 0,
                    'notes' => 'Piutang dari pesanan nomor ' . $storeOrder->order_number,
                    'created_by' => Auth::id(),
                ]);
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
        // Pastikan hanya owner atau admin_back_office yang bisa konfirmasi
        if (!Auth::user()->hasRole('owner') && !Auth::user()->hasRole('admin_back_office')) {
            return redirect()->back()->with('error', 'Anda tidak memiliki izin untuk mengkonfirmasi pesanan.');
        }

        // VALIDASI INPUT ONGKIR
        $validated = $request->validate([
            'shipping_cost' => 'required|numeric|min:0'
        ]);

        $storeOrder = StoreOrder::findOrFail($id);

        if ($storeOrder->status != StoreOrder::STATUS_PENDING) {
            return redirect()->back()->with('error', 'Pesanan ini tidak dapat dikonfirmasi (status saat ini: ' . $storeOrder->status . ').');
        }

        DB::beginTransaction();
        try {
            // HITUNG GRAND TOTAL
            $grandTotal = $storeOrder->total_amount + $validated['shipping_cost'];

            // Update status pesanan dan ongkir
            $storeOrder->update([
                'status' => StoreOrder::STATUS_CONFIRMED_BY_ADMIN,
                'shipping_cost' => $validated['shipping_cost'],
                'grand_total' => $grandTotal,
                'confirmed_at' => Carbon::now(),
                'updated_by' => Auth::id()
            ]);

            // UPDATE PIUTANG JIKA ADA
            $receivable = AccountReceivable::where('store_order_id', $storeOrder->id)->first();
            if ($receivable) {
                $receivable->update([
                    'amount' => $grandTotal, // Update dengan grand total termasuk ongkir
                    'notes' => $receivable->notes . "\n" . date('d/m/Y') . ": Ongkir Rp " . number_format($validated['shipping_cost'], 0, ',', '.') . " ditambahkan",
                    'updated_by' => Auth::id(),
                ]);
            }

            // Log aktivitas untuk debugging
            \Log::info('Pesanan dikonfirmasi dengan ongkir', [
                'order_id' => $storeOrder->id,
                'order_number' => $storeOrder->order_number,
                'shipping_cost' => $validated['shipping_cost'],
                'grand_total' => $grandTotal,
                'confirmed_by' => Auth::user()->name,
                'user_role' => Auth::user()->getRoleNames()
            ]);

            // Kirim notifikasi ke Admin Gudang
            $warehouseAdmins = User::role('admin_gudang')->get();
            foreach ($warehouseAdmins as $admin) {
                \Log::info('Notifikasi dikirim ke admin gudang', ['admin_id' => $admin->id]);
            }

            DB::commit();

            return redirect()->route('store-orders.index')
                ->with('success', 'Pesanan berhasil dikonfirmasi dengan ongkir Rp ' . number_format($validated['shipping_cost'], 0, ',', '.') . ' dan diteruskan ke admin gudang.');

        } catch (\Exception $e) {
            DB::rollBack();

            \Log::error('Error saat konfirmasi pesanan', [
                'order_id' => $id,
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat mengkonfirmasi pesanan: ' . $e->getMessage());
        }
    }

    // Tambahkan method baru untuk meneruskan ke gudang
    public function forwardToWarehouse($id)
    {
        // Pastikan hanya owner atau admin_back_office yang bisa meneruskan
        if (!Auth::user()->hasRole('owner') && !Auth::user()->hasRole('admin_back_office')) {
            return redirect()->back()->with('error', 'Anda tidak memiliki izin untuk meneruskan pesanan.');
        }

        $storeOrder = StoreOrder::findOrFail($id);

        if ($storeOrder->status != StoreOrder::STATUS_CONFIRMED_BY_ADMIN) {
            return redirect()->back()->with('error', 'Pesanan ini tidak dapat diteruskan ke gudang (status saat ini: ' . $storeOrder->status . ').');
        }

        DB::beginTransaction();
        try {
            // Update status pesanan
            $storeOrder->update([
                'status' => StoreOrder::STATUS_FORWARDED_TO_WAREHOUSE,
                'forwarded_at' => Carbon::now(),
                'updated_by' => Auth::id()
            ]);

            // Log aktivitas untuk debugging
            \Log::info('Pesanan diteruskan ke gudang', [
                'order_id' => $storeOrder->id,
                'order_number' => $storeOrder->order_number,
                'forwarded_by' => Auth::user()->name,
                'user_role' => Auth::user()->getRoleNames()
            ]);

            DB::commit();

            return redirect()->route('store-orders.index')
                ->with('success', 'Pesanan berhasil diteruskan ke admin gudang.');

        } catch (\Exception $e) {
            DB::rollBack();

            \Log::error('Error saat meneruskan pesanan ke gudang', [
                'order_id' => $id,
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat meneruskan pesanan: ' . $e->getMessage());
        }
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

            // Update piutang jika pesanan menggunakan metode kredit
            $receivable = AccountReceivable::where('store_order_id', $storeOrder->id)->first();
            if ($receivable && $storeOrder->payment_type === 'cash') {
                // Jika metode pembayaran tunai, tandai piutang sebagai lunas
                $receivable->update([
                    'status' => 'paid',
                    'paid_amount' => $receivable->amount,
                    'payment_date' => Carbon::now(),
                    'notes' => $receivable->notes . "\n" . date('d/m/Y') . ": Dibayar tunai pada saat pengiriman",
                    'updated_by' => Auth::id(),
                ]);
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
