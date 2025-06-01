<?php

namespace App\Http\Controllers;

use App\Models\Shipment;
use App\Models\ShipmentDetail;
use App\Models\StoreOrder;
use App\Models\StockWarehouse;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use PDF;

class ShipmentController extends Controller
{
    public function index()
    {
        // Filter berdasarkan role
        if (Auth::user()->hasRole('admin_gudang')) {
            $shipments = Shipment::with(['storeOrder.store'])
                ->orderBy('created_at', 'desc')
                ->paginate(10);
        } elseif (Auth::user()->hasRole('admin_store')) {
            $shipments = Shipment::whereHas('storeOrder', function ($query) {
                    $query->where('store_id', Auth::user()->store_id);
                })
                ->with(['storeOrder.store'])
                ->orderBy('created_at', 'desc')
                ->paginate(10);
        } else {
            $shipments = Shipment::with(['storeOrder.store'])
                ->orderBy('created_at', 'desc')
                ->paginate(10);
        }

        return view('shipments.index', compact('shipments'));
    }

    public function createFromOrder($storeOrderId)
    {
        // Pastikan hanya admin gudang yang bisa membuat pengiriman
        if (!Auth::user()->hasRole('admin_gudang')) {
            return redirect()->back()->with('error', 'Anda tidak memiliki izin untuk membuat pengiriman.');
        }

        $storeOrder = StoreOrder::with(['store', 'storeOrderDetails.product', 'storeOrderDetails.unit'])
            ->findOrFail($storeOrderId);

        if ($storeOrder->status != StoreOrder::STATUS_FORWARDED_TO_WAREHOUSE) {
            return redirect()->back()->with('error', 'Pesanan ini tidak dalam status yang tepat untuk membuat pengiriman.');
        }

        return view('shipments.create', compact('storeOrder'));
    }

    public function store(Request $request)
    {
        // Pastikan hanya admin gudang yang bisa membuat pengiriman
        if (!Auth::user()->hasRole('admin_gudang')) {
            return redirect()->back()->with('error', 'Anda tidak memiliki izin untuk membuat pengiriman.');
        }

        $request->validate([
            'store_order_id' => 'required|exists:store_orders,id',
            'product_id' => 'required|array',
            'product_id.*' => 'required|exists:products,id',
            'unit_id' => 'required|array',
            'unit_id.*' => 'required|exists:units,id',
            'quantity' => 'required|array',
            'quantity.*' => 'required|numeric|min:1',
        ]);

        DB::beginTransaction();
        try {
            $storeOrder = StoreOrder::findOrFail($request->store_order_id);

            if ($storeOrder->status != StoreOrder::STATUS_FORWARDED_TO_WAREHOUSE) {
                return redirect()->back()->with('error', 'Pesanan ini tidak dalam status yang tepat untuk membuat pengiriman.');
            }

            // Validasi ketersediaan stok
            foreach ($request->product_id as $index => $productId) {
                $stockWarehouse = StockWarehouse::where('product_id', $productId)
                    ->where('unit_id', $request->unit_id[$index])
                    ->first();

                if (!$stockWarehouse || $stockWarehouse->quantity < $request->quantity[$index]) {
                    $productName = Product::find($productId)->name;
                    return redirect()->back()->with('error', "Stok tidak mencukupi untuk produk {$productName}");
                }
            }

            // Buat pengiriman
            $shipment = Shipment::create([
                'store_order_id' => $storeOrder->id,
                'shipment_number' => 'SHP-' . date('YmdHis'),
                'date' => now(),
                'status' => 'shipped',
                'note' => $request->note,
                'created_by' => Auth::id(),
                'updated_by' => Auth::id()
            ]);

            // Simpan detail pengiriman dan kurangi stok gudang
            foreach ($request->product_id as $index => $productId) {
                if ($request->quantity[$index] > 0) {
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
            }

            // Update status pesanan
            $storeOrder->status = StoreOrder::STATUS_SHIPPED;
            $storeOrder->shipped_at = Carbon::now();
            $storeOrder->updated_by = Auth::id();
            $storeOrder->save();

            // Kirim notifikasi ke store
            $storeAdmins = User::where('store_id', $storeOrder->store_id)
                ->role('admin_store')
                ->get();

            foreach ($storeAdmins as $admin) {
                // Implementasi notifikasi akan ditambahkan nanti
            }

            DB::commit();

            return redirect()->route('shipments.index')
                ->with('success', 'Pengiriman berhasil dibuat dan stok gudang telah dikurangi.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat membuat pengiriman: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function show($id)
    {
        $shipment = Shipment::with([
                'storeOrder.store',
                'shipmentDetails.product',
                'shipmentDetails.unit',
                'createdBy',
                'updatedBy'
            ])
            ->findOrFail($id);

        // Jika admin store, pastikan hanya bisa lihat pengiriman untuk tokonya
        if (Auth::user()->hasRole('admin_store') && Auth::user()->store_id != $shipment->storeOrder->store_id) {
            return redirect()->route('shipments.index')
                ->with('error', 'Anda tidak memiliki akses ke pengiriman ini.');
        }

        return view('shipments.show', compact('shipment'));
    }

    public function document($id)
    {
        $shipment = Shipment::with([
                'storeOrder.store',
                'storeOrder.storeOrderDetails',
                'shipmentDetails.product',
                'shipmentDetails.unit',
                'createdBy'
            ])
            ->findOrFail($id);

        $pdf = PDF::loadView('shipments.document_raw', compact('shipment'));
        return $pdf->stream('invoice-' . $shipment->shipment_number . '.pdf');
    }

    /**
     * Method untuk Invoice (alias dari document)
     */
    public function invoice($id)
    {
        return $this->document($id);
    }

    /**
     * Method untuk Surat Jalan Baru (tanpa harga)
     */
    public function deliveryNote($id)
    {
        $shipment = Shipment::with([
                'storeOrder.store',
                'shipmentDetails.product',
                'shipmentDetails.unit',
                'createdBy'
            ])
            ->findOrFail($id);

        // Jika admin store, pastikan hanya bisa lihat pengiriman untuk tokonya
        if (Auth::user()->hasRole('admin_store') && Auth::user()->store_id != $shipment->storeOrder->store_id) {
            return redirect()->route('shipments.index')
                ->with('error', 'Anda tidak memiliki akses ke pengiriman ini.');
        }

        $pdf = PDF::loadView('shipments.delivery_note', compact('shipment'));
        return $pdf->stream('surat-jalan-' . $shipment->shipment_number . '.pdf');
    }
}
