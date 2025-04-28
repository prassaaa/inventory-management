<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use App\Models\Purchase;
use App\Models\StockWarehouse;
use App\Models\User;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WarehousePurchaseController extends Controller
{
    public function index()
    {
        $purchases = Purchase::where('status', 'confirmed')
                    ->with(['supplier', 'creator'])
                    ->orderBy('date', 'desc')
                    ->paginate(10);

        return view('warehouse.purchases.index', compact('purchases'));
    }

    public function show($id)
    {
        $purchase = Purchase::with(['supplier', 'creator', 'purchaseDetails.product', 'purchaseDetails.unit'])
                   ->findOrFail($id);

        return view('warehouse.purchases.show', compact('purchase'));
    }

    public function processReceive(Purchase $purchase)
    {
        // Only allow processing purchases with 'confirmed' status
        if ($purchase->status !== 'confirmed') {
            return redirect()->route('warehouse.purchases.show', $purchase->id)
                ->with('error', 'Purchase is already processed or still pending.');
        }

        try {
            DB::beginTransaction();

            // Update purchase status to 'complete'
            $purchase->update([
                'status' => 'complete',
                'updated_by' => Auth::id(),
            ]);

            // Update stock for each product
            foreach ($purchase->purchaseDetails as $detail) {
                $product = $detail->product;
                $unit = $detail->unit;

                // Convert quantity to base unit if necessary
                $baseQuantity = $detail->quantity;
                if ($unit->id !== $product->base_unit_id) {
                    // Find product unit for conversion
                    $productUnit = $product->productUnits()->where('unit_id', $unit->id)->first();
                    if ($productUnit) {
                        $baseQuantity = $detail->quantity * $productUnit->conversion_value;
                    }
                }

                // Update or create stock warehouse
                $stockWarehouse = StockWarehouse::firstOrCreate(
                    ['product_id' => $product->id, 'unit_id' => $product->base_unit_id],
                    ['quantity' => 0]
                );

                $stockWarehouse->increment('quantity', $baseQuantity);
            }

            // Kirim notifikasi ke admin pusat
            $centralAdmins = User::whereHas('roles', function($query) {
                $query->whereIn('name', ['admin_back_office', 'owner']);
            })->get();

            // Kemudian lanjutkan dengan kode pengiriman notifikasi
            foreach ($centralAdmins as $admin) {
                Notification::create([
                    'user_id' => $admin->id,
                    'title' => 'Pembelian Barang Berhasil Diterima',
                    'message' => 'Pembelian dengan No. Invoice: ' . $purchase->invoice_number . ' telah diterima dan stok diperbarui.',
                    'link' => route('purchases.show', $purchase->id),
                    'is_read' => false
                ]);
            }

            DB::commit();

            return redirect()->route('warehouse.purchases.index')
                ->with('success', 'Purchase received successfully and stock updated.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('warehouse.purchases.show', $purchase->id)
                ->with('error', 'Error processing purchase: ' . $e->getMessage());
        }
    }
}
