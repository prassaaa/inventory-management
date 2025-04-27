<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use App\Models\StoreOrder;
use App\Models\Shipment;
use App\Models\ShipmentDetail;
use App\Models\StockWarehouse;
use App\Models\User;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use PDF;

class WarehouseOrderController extends Controller
{
    public function index()
    {
        $storeOrders = StoreOrder::whereIn('status', [
                        StoreOrder::STATUS_FORWARDED_TO_WAREHOUSE,
                        StoreOrder::STATUS_SHIPPED
                      ])
                      ->with(['store', 'createdBy'])
                      ->orderBy('created_at', 'desc')
                      ->paginate(10);

        return view('warehouse.store-orders.index', compact('storeOrders'));
    }

    public function show($id)
    {
        $storeOrder = StoreOrder::with(['store', 'storeOrderDetails.product', 'storeOrderDetails.unit'])
                      ->findOrFail($id);

        return view('warehouse.store-orders.show', compact('storeOrder'));
    }

    public function createShipment($id)
    {
        $storeOrder = StoreOrder::with(['storeOrderDetails.product', 'storeOrderDetails.unit'])
                      ->findOrFail($id);

        if ($storeOrder->status != StoreOrder::STATUS_FORWARDED_TO_WAREHOUSE) {
            return redirect()->back()->with('error', 'Pesanan ini tidak dapat diproses pengirimannya.');
        }

        return view('warehouse.store-orders.create-shipment', compact('storeOrder'));
    }

    public function storeShipment(Request $request, $id)
    {
        $storeOrder = StoreOrder::findOrFail($id);

        if ($storeOrder->status != StoreOrder::STATUS_FORWARDED_TO_WAREHOUSE) {
            return redirect()->back()->with('error', 'Pesanan ini tidak dapat diproses pengirimannya.');
        }

        // Validasi ketersediaan stok
        foreach ($request->product_id as $index => $productId) {
            $stockWarehouse = StockWarehouse::where('product_id', $productId)
                             ->where('unit_id', $request->unit_id[$index])
                             ->first();

            if (!$stockWarehouse || $stockWarehouse->quantity < $request->quantity[$index]) {
                return redirect()->back()->with('error', 'Stok tidak mencukupi untuk produk dengan ID ' . $productId);
            }
        }

        // Simpan data pengiriman
        $shipment = Shipment::create([
            'store_order_id' => $storeOrder->id,
            'shipment_number' => 'SHP-' . date('YmdHis'),
            'date' => now(),
            'status' => 'shipped',
            'note' => $request->note,
            'created_by' => Auth::id(),
            'updated_by' => Auth::id()
        ]);

        // Simpan detail pengiriman
        foreach ($request->product_id as $index => $productId) {
            ShipmentDetail::create([
                'shipment_id' => $shipment->id,
                'product_id' => $productId,
                'unit_id' => $request->unit_id[$index],
                'quantity' => $request->quantity[$index]
            ]);

            // Kurangi stok gudang
            $stockWarehouse = StockWarehouse::where('product_id', $productId)
                             ->where('unit_id', $request->unit_id[$index])
                             ->first();

            $stockWarehouse->quantity -= $request->quantity[$index];
            $stockWarehouse->save();
        }

        // Update status pesanan
        $storeOrder->status = StoreOrder::STATUS_SHIPPED;
        $storeOrder->shipped_at = Carbon::now();
        $storeOrder->save();

        // Kirim notifikasi ke store
        $storeAdmins = User::where('store_id', $storeOrder->store_id)
                      ->role('admin_store')
                      ->get();

        foreach ($storeAdmins as $admin) {
            Notification::create([
                'user_id' => $admin->id,
                'title' => 'Pengiriman Barang Telah Dikirim',
                'message' => 'Pesanan Anda telah dikirim dari gudang pusat. Silakan konfirmasi saat barang telah diterima.',
                'link' => route('store.orders.show', $storeOrder->id),
                'is_read' => false
            ]);
        }

        return redirect()->route('warehouse.store-orders.index')
                         ->with('success', 'Pengiriman berhasil dibuat dan stok gudang telah dikurangi.');
    }

    public function printShippingDocument($id)
    {
        $shipment = Shipment::with([
                    'storeOrder.store',
                    'shipmentDetails.product',
                    'shipmentDetails.unit',
                    'createdBy'
                  ])
                  ->findOrFail($id);

        $pdf = PDF::loadView('warehouse.store-orders.shipping-document', compact('shipment'));
        return $pdf->stream('surat-jalan-' . $shipment->shipment_number . '.pdf');
    }
}
