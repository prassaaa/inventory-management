<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\StoreOrder;
use App\Models\StoreOrderDetail;
use App\Models\Product;
use App\Models\StockStore;
use App\Models\Shipment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

        return view('stores.orders.create', compact('products'));
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
        ]);

        $storeOrder = StoreOrder::create([
            'store_id' => Auth::user()->store_id,
            'order_number' => 'ORD-' . date('YmdHis'),
            'date' => now(),
            'status' => StoreOrder::STATUS_PENDING,
            'note' => $request->note,
            'created_by' => Auth::id(),
            'updated_by' => Auth::id(),
        ]);

        foreach ($request->product_id as $index => $productId) {
            if ($request->quantity[$index] > 0) {
                StoreOrderDetail::create([
                    'store_order_id' => $storeOrder->id,
                    'product_id' => $productId,
                    'unit_id' => $request->unit_id[$index],
                    'quantity' => $request->quantity[$index],
                    'price' => Product::find($productId)->purchase_price,
                    'subtotal' => Product::find($productId)->purchase_price * $request->quantity[$index],
                ]);
            }
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

        return redirect()->route('store.orders.index')
                         ->with('success', 'Pesanan berhasil dibuat dan menunggu konfirmasi dari pusat.');
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

        return redirect()->route('store.orders.index')
                         ->with('success', 'Penerimaan barang berhasil dikonfirmasi dan stok toko telah ditambahkan.');
    }
}
