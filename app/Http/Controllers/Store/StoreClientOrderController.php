<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\StoreOrder;
use App\Models\StoreOrderDetail;
use App\Models\Product;
use App\Models\StockStore;
use App\Models\Shipment;
use App\Models\AccountReceivable;
use App\Models\User;
use App\Models\Notification;
use App\Models\Unit;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StoreClientOrderController extends Controller
{
    public function index()
    {
        $storeId = Auth::user()->store_id;
        $storeOrders = StoreOrder::where('store_id', $storeId)
                       ->orderBy('created_at', 'desc')
                       ->paginate(10);

        return view('stores.orders.index', compact('storeOrders'));
    }

    public function create()
    {
        $products = Product::where('store_source', 'pusat')
                   ->where('is_active', true)
                   ->get();

        $units = Unit::all();
        $store = Auth::user()->store;

        return view('stores.orders.create', compact('products', 'units', 'store'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|array',
            'product_id.*' => 'required|exists:products,id',
            'unit_id' => 'required|array',
            'unit_id.*' => 'required|exists:units,id',
            'quantity' => 'required|array',
            'quantity.*' => 'required|numeric|min:1',
            'payment_type' => 'required|in:cash,credit', // Validasi payment_type
            'due_date' => 'required_if:payment_type,credit|nullable|date', // Validasi due_date
        ]);

        DB::beginTransaction();
        try {
            $storeOrder = StoreOrder::create([
                'store_id' => Auth::user()->store_id,
                'order_number' => 'ORD-' . date('YmdHis'),
                'date' => now(),
                'status' => StoreOrder::STATUS_PENDING,
                'payment_type' => $request->payment_type, // Tambahkan payment_type
                'due_date' => $request->payment_type === 'credit' ? $request->due_date : null, // Tambahkan due_date
                'note' => $request->note,
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
            ]);

            // Hitung total amount
            $totalAmount = 0;

            foreach ($request->product_id as $index => $productId) {
                if ($request->quantity[$index] > 0) {
                    $product = Product::find($productId);
                    $price = $product->purchase_price;
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

            // Tambahkan pembuatan AccountReceivable jika metode kredit
            if ($request->payment_type === 'credit') {
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
                Notification::create([
                    'user_id' => $admin->id,
                    'title' => 'Pesanan Baru dari Toko',
                    'message' => 'Toko ' . Auth::user()->store->name . ' telah membuat pesanan baru.',
                    'link' => route('admin.store-orders.show', $storeOrder->id),
                    'is_read' => false
                ]);
            }

            DB::commit();
            return redirect()->route('store.orders.index')
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
        $storeOrder = StoreOrder::where('store_id', Auth::user()->store_id)
                      ->with(['storeOrderDetails.product', 'storeOrderDetails.unit'])
                      ->findOrFail($id);

        $shipment = Shipment::where('store_order_id', $storeOrder->id)
                   ->with(['shipmentDetails.product', 'shipmentDetails.unit'])
                   ->first();

        return view('stores.orders.show', compact('storeOrder', 'shipment'));
    }

    public function confirmDelivery($id)
    {
        $storeOrder = StoreOrder::where('store_id', Auth::user()->store_id)
                      ->findOrFail($id);

        if ($storeOrder->status != StoreOrder::STATUS_SHIPPED) {
            return redirect()->back()->with('error', 'Pesanan ini tidak dapat dikonfirmasi penerimaannya.');
        }

        $shipment = Shipment::where('store_order_id', $storeOrder->id)->first();

        if (!$shipment) {
            return redirect()->back()->with('error', 'Data pengiriman tidak ditemukan.');
        }

        DB::beginTransaction();
        try {
            // Update status pesanan
            $storeOrder->status = StoreOrder::STATUS_COMPLETED;
            $storeOrder->delivered_at = Carbon::now();
            $storeOrder->completed_at = Carbon::now();
            $storeOrder->save();

            // Update status pengiriman
            $shipment->status = 'delivered';
            $shipment->save();

            // Tambah stok toko
            foreach ($shipment->shipmentDetails as $detail) {
                $stockStore = StockStore::firstOrNew([
                    'store_id' => Auth::user()->store_id,
                    'product_id' => $detail->product_id,
                    'unit_id' => $detail->unit_id,
                ]);

                $stockStore->quantity = ($stockStore->quantity ?? 0) + $detail->quantity;
                $stockStore->save();
            }

            // Update piutang jika pesanan menggunakan metode kredit dan pembayaran cash
            if ($storeOrder->payment_type === 'cash') {
                $receivable = AccountReceivable::where('store_order_id', $storeOrder->id)->first();
                if ($receivable) {
                    $receivable->update([
                        'status' => 'paid',
                        'paid_amount' => $receivable->amount,
                        'payment_date' => Carbon::now(),
                        'notes' => $receivable->notes . "\n" . date('d/m/Y') . ": Dibayar tunai pada saat pengiriman",
                        'updated_by' => Auth::id(),
                    ]);
                }
            }

            // CATAT ONGKIR SEBAGAI BEBAN BIAYA TRANSPORTASI UNTUK OUTLET
            // Gunakan helper method untuk mencari kategori
            $transportCategory = ExpenseCategory::findTransportationCategory();

            if ($transportCategory && $storeOrder->shipping_cost > 0) {
                Expense::create([
                    'date' => Carbon::now(),
                    'amount' => $storeOrder->shipping_cost,
                    'description' => 'Beban transportasi untuk pesanan ' . $storeOrder->order_number,
                    'category_id' => $transportCategory->id,
                    'store_id' => Auth::user()->store_id,
                    'created_by' => Auth::id(),
                    'updated_by' => Auth::id()
                ]);

                // Log aktivitas pencatatan beban transportasi
                \Log::info('Ongkir dicatat sebagai Beban Biaya Transportasi', [
                    'order_id' => $storeOrder->id,
                    'order_number' => $storeOrder->order_number,
                    'shipping_cost' => $storeOrder->shipping_cost,
                    'expense_category' => $transportCategory->name,
                    'store_id' => Auth::user()->store_id,
                    'recorded_by' => Auth::user()->name
                ]);
            } else {
                // Log warning jika kategori tidak ditemukan atau shipping_cost = 0
                \Log::warning('Kategori Beban Transportasi tidak ditemukan atau ongkir = 0, beban tidak dicatat', [
                    'order_id' => $storeOrder->id,
                    'shipping_cost' => $storeOrder->shipping_cost ?? 0,
                    'category_found' => $transportCategory ? true : false
                ]);
            }

            DB::commit();
            return redirect()->route('store.orders.index')
                ->with('success', 'Penerimaan barang berhasil dikonfirmasi dan stok toko telah ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat mengkonfirmasi penerimaan: ' . $e->getMessage());
        }
    }

    /**
     * Hapus pesanan (hanya untuk status pending dan admin store)
     */
    public function destroy($id)
    {
        $storeOrder = StoreOrder::where('store_id', Auth::user()->store_id)
                    ->findOrFail($id);

        // Hanya bisa hapus jika status pending
        if ($storeOrder->status !== StoreOrder::STATUS_PENDING) {
            return redirect()->back()->with('error', 'Pesanan ini tidak dapat dihapus karena sudah diproses.');
        }

        DB::beginTransaction();
        try {
            // Hapus piutang jika ada
            AccountReceivable::where('store_order_id', $storeOrder->id)->delete();

            // Hapus detail pesanan
            $storeOrder->storeOrderDetails()->delete();

            // Hapus pesanan
            $storeOrder->delete();

            DB::commit();

            return redirect()->route('store.orders.index')
                ->with('success', 'Pesanan berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat menghapus pesanan: ' . $e->getMessage());
        }
    }
}
