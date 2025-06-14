<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\PurchaseDetail;
use App\Models\Supplier;
use App\Models\Product;
use App\Models\Unit;
use App\Models\StockWarehouse;
use App\Models\User;
use App\Models\Notification;
use App\Models\AccountPayable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use PDF;

class PurchaseController extends Controller
{
    /**
     * Display a listing of the resource with proper filtering.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Query dasar
        $query = Purchase::with(['supplier', 'creator']);

        // Filter berdasarkan role dan store_id (jika ada)
        if ($user->hasRole(['admin_back_office', 'admin_gudang', 'owner'])) {
            // Role PUSAT bisa lihat semua pembelian
            // Tidak perlu filter tambahan
        } elseif ($user->hasRole(['admin_store'])) {
            // Admin store hanya bisa lihat pembelian yang terkait dengan store mereka
            if ($user->store_id) {
                $query->where('store_id', $user->store_id);
            } else {
                // Jika tidak ada store_id, kembalikan collection kosong
                $query->whereRaw('1 = 0');
            }
        } else {
            // Role lain tidak bisa melihat pembelian
            $query->whereRaw('1 = 0');
        }

        // Filter by supplier if provided
        if ($request->has('supplier_id') && $request->supplier_id) {
            $query->where('supplier_id', $request->supplier_id);
        }

        $purchases = $query->orderBy('date', 'desc')->get();

        return view('purchases.index', compact('purchases'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();
        $products = Product::with('baseUnit')->where('is_active', true)->orderBy('name')->get();
        $units = Unit::orderBy('name')->get();

        return view('purchases.create', compact('suppliers', 'products', 'units'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'invoice_number' => 'required|string|max:50|unique:purchases',
            'date' => 'required|date',
            'payment_type' => 'required|in:tunai,tempo',
            'due_date' => 'required_if:payment_type,tempo|nullable|date',
            'total_amount' => 'required|numeric|min:0',
            'note' => 'nullable|string',
            'product_ids' => 'required|array|min:1',
            'product_ids.*' => 'required|exists:products,id',
            'quantities' => 'required|array|min:1',
            'quantities.*' => 'required|numeric|min:0.01',
            'prices' => 'required|array|min:1',
            'prices.*' => 'required|numeric|min:0',
            'units' => 'required|array|min:1',
            'units.*' => 'required|exists:units,id',
        ]);

        try {
            DB::beginTransaction();

            // Create purchase
            $purchase = Purchase::create([
                'supplier_id' => $validated['supplier_id'],
                'invoice_number' => $validated['invoice_number'],
                'date' => $validated['date'],
                'payment_type' => $validated['payment_type'],
                'due_date' => $validated['payment_type'] === 'tempo' ? $validated['due_date'] : null,
                'total_amount' => $validated['total_amount'],
                'status' => 'pending',
                'note' => $validated['note'],
                'created_by' => Auth::id(),
            ]);

            // Create purchase details
            foreach ($validated['product_ids'] as $index => $productId) {
                $quantity = $validated['quantities'][$index];
                $price = $validated['prices'][$index];
                $unitId = $validated['units'][$index];

                PurchaseDetail::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $productId,
                    'unit_id' => $unitId,
                    'quantity' => $quantity,
                    'price' => $price,
                    'subtotal' => $quantity * $price,
                ]);
            }

            // Cek apakah pembelian menggunakan metode tempo (kredit)
            if ($validated['payment_type'] === 'tempo') {
                // Buat catatan hutang ke pemasok
                AccountPayable::create([
                    'purchase_id' => $purchase->id,
                    'supplier_id' => $validated['supplier_id'],
                    'amount' => $validated['total_amount'],
                    'due_date' => $validated['due_date'],
                    'status' => 'unpaid',
                    'paid_amount' => 0,
                    'notes' => 'Hutang dari pembelian invoice ' . $validated['invoice_number'],
                    'created_by' => Auth::id(),
                ]);
            }

            DB::commit();

            return redirect()->route('purchases.show', $purchase)
                ->with('success', 'Purchase created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Error creating purchase: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Purchase $purchase)
    {
        // Check if user has access to this purchase
        if (!$this->canUserViewPurchase($purchase)) {
            abort(403, 'Anda tidak memiliki akses untuk melihat pembelian ini.');
        }

        $purchase->load(['supplier', 'creator', 'purchaseDetails.product', 'purchaseDetails.unit', 'purchaseReturns']);

        return view('purchases.show', compact('purchase'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Purchase $purchase)
    {
        // Check permission
        if (!$this->canUserAccessPurchase($purchase)) {
            abort(403, 'Anda tidak memiliki akses untuk mengedit pembelian ini.');
        }

        // Only allow editing purchases with 'pending' status
        if ($purchase->status !== 'pending') {
            return redirect()->route('purchases.show', $purchase)
                ->with('error', 'Cannot edit purchase. Only pending purchases can be edited.');
        }

        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();
        $products = Product::with('baseUnit')->where('is_active', true)->orderBy('name')->get();
        $units = Unit::orderBy('name')->get();

        $purchase->load(['purchaseDetails.product', 'purchaseDetails.unit']);

        return view('purchases.edit', compact('purchase', 'suppliers', 'products', 'units'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Purchase $purchase)
    {
        // Check permission
        if (!$this->canUserAccessPurchase($purchase)) {
            abort(403, 'Anda tidak memiliki akses untuk mengedit pembelian ini.');
        }

        // Only allow updating purchases with 'pending' status
        if ($purchase->status !== 'pending') {
            return redirect()->route('purchases.show', $purchase)
                ->with('error', 'Cannot update purchase. Only pending purchases can be updated.');
        }

        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'invoice_number' => 'required|string|max:50|unique:purchases,invoice_number,' . $purchase->id,
            'date' => 'required|date',
            'payment_type' => 'required|in:tunai,tempo',
            'due_date' => 'required_if:payment_type,tempo|nullable|date',
            'total_amount' => 'required|numeric|min:0',
            'note' => 'nullable|string',
            'product_ids' => 'required|array|min:1',
            'product_ids.*' => 'required|exists:products,id',
            'quantities' => 'required|array|min:1',
            'quantities.*' => 'required|numeric|min:0.01',
            'prices' => 'required|array|min:1',
            'prices.*' => 'required|numeric|min:0',
            'units' => 'required|array|min:1',
            'units.*' => 'required|exists:units,id',
        ]);

        try {
            DB::beginTransaction();

            // Update purchase
            $purchase->update([
                'supplier_id' => $validated['supplier_id'],
                'invoice_number' => $validated['invoice_number'],
                'date' => $validated['date'],
                'payment_type' => $validated['payment_type'],
                'due_date' => $validated['payment_type'] === 'tempo' ? $validated['due_date'] : null,
                'total_amount' => $validated['total_amount'],
                'note' => $validated['note'],
                'updated_by' => Auth::id(),
            ]);

            // Delete existing purchase details
            $purchase->purchaseDetails()->delete();

            // Create new purchase details
            foreach ($validated['product_ids'] as $index => $productId) {
                $quantity = $validated['quantities'][$index];
                $price = $validated['prices'][$index];
                $unitId = $validated['units'][$index];

                PurchaseDetail::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $productId,
                    'unit_id' => $unitId,
                    'quantity' => $quantity,
                    'price' => $price,
                    'subtotal' => $quantity * $price,
                ]);
            }

            // Update atau hapus catatan hutang sesuai dengan metode pembayaran
            if ($validated['payment_type'] === 'tempo') {
                // Update atau buat catatan hutang
                AccountPayable::updateOrCreate(
                    ['purchase_id' => $purchase->id],
                    [
                        'supplier_id' => $validated['supplier_id'],
                        'amount' => $validated['total_amount'],
                        'due_date' => $validated['due_date'],
                        'status' => 'unpaid',
                        'notes' => 'Hutang dari pembelian invoice ' . $validated['invoice_number'] . ' (updated)',
                        'updated_by' => Auth::id(),
                    ]
                );
            } else {
                // Jika diubah dari tempo ke tunai, hapus catatan hutang
                AccountPayable::where('purchase_id', $purchase->id)->delete();
            }

            DB::commit();

            return redirect()->route('purchases.show', $purchase)
                ->with('success', 'Purchase updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Error updating purchase: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Purchase $purchase)
    {
        // Check permission
        if (!$this->canUserAccessPurchase($purchase)) {
            abort(403, 'Anda tidak memiliki akses untuk menghapus pembelian ini.');
        }

        // Only allow deleting purchases with 'pending' status
        if ($purchase->status !== 'pending') {
            return redirect()->route('purchases.index')
                ->with('error', 'Cannot delete purchase. Only pending purchases can be deleted.');
        }

        try {
            DB::beginTransaction();

            // Delete purchase details
            $purchase->purchaseDetails()->delete();

            // Delete any associated account payable
            AccountPayable::where('purchase_id', $purchase->id)->delete();

            // Delete purchase
            $purchase->delete();

            DB::commit();

            return redirect()->route('purchases.index')
                ->with('success', 'Purchase deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('purchases.index')
                ->with('error', 'Error deleting purchase: ' . $e->getMessage());
        }
    }

    /**
     * Confirm purchase and send notification to warehouse admin
     */
    public function confirm(Purchase $purchase)
    {
        // Check permission
        if (!$this->canUserAccessPurchase($purchase)) {
            abort(403, 'Anda tidak memiliki akses untuk mengkonfirmasi pembelian ini.');
        }

        // Log debug info
        \Log::info('Confirm method called for purchase #' . $purchase->id . ' with status: ' . $purchase->status);

        // Only allow confirming purchases with 'pending' status
        if ($purchase->status !== 'pending') {
            \Log::warning('Attempted to confirm purchase with non-pending status: ' . $purchase->status);
            return redirect()->route('purchases.show', $purchase)
                ->with('error', 'Purchase is already confirmed.');
        }

        try {
            \Log::info('Starting DB transaction for purchase confirmation');
            DB::beginTransaction();

            // Update purchase status to 'confirmed'
            $purchase->update([
                'status' => 'confirmed', // Ubah dari 'complete' menjadi 'confirmed'
                'updated_by' => Auth::id(),
            ]);
            \Log::info('Purchase status updated to confirmed');

            // Cari admin gudang dan kirim notifikasi
            $warehouseAdmins = User::role('admin_gudang')->get();
            \Log::info('Found ' . $warehouseAdmins->count() . ' warehouse admins');

            foreach ($warehouseAdmins as $admin) {
                Notification::create([
                    'user_id' => $admin->id,
                    'title' => 'Konfirmasi Pembelian Barang',
                    'message' => 'Ada pembelian barang baru yang perlu dikonfirmasi (No. Invoice: ' . $purchase->invoice_number . ')',
                    'link' => route('warehouse.purchases.show', $purchase->id),
                    'is_read' => false
                ]);
                \Log::info('Created notification for admin #' . $admin->id);
            }

            DB::commit();
            \Log::info('DB transaction committed successfully');

            return redirect()->route('purchases.show', $purchase)
                ->with('success', 'Purchase confirmed successfully and notification sent to warehouse admin.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error during purchase confirmation: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            return redirect()->route('purchases.show', $purchase)
                ->with('error', 'Error confirming purchase: ' . $e->getMessage());
        }
    }

    /**
     * Generate purchase receipt in PDF
     */
    public function receipt(Purchase $purchase)
    {
        $purchase->load(['supplier', 'purchaseDetails.product', 'purchaseDetails.unit', 'creator']);

        $pdf = PDF::loadView('purchases.receipt', compact('purchase'));

        return $pdf->stream('purchase_' . $purchase->invoice_number . '.pdf');
    }

    /**
     * Check if user can view the purchase (for show method)
     */
    private function canUserViewPurchase(Purchase $purchase)
    {
        $user = Auth::user();

        // Role PUSAT bisa akses semua
        if ($user->hasRole(['admin_back_office', 'admin_gudang', 'owner'])) {
            return true;
        }

        // Role CABANG (admin_store) hanya bisa akses pembelian toko mereka
        if ($user->hasRole(['admin_store'])) {
            return $user->store_id === $purchase->store_id;
        }

        return false;
    }

    /**
     * Check if user can access the purchase (based on role) for edit/delete/confirm
     */
    private function canUserAccessPurchase(Purchase $purchase)
    {
        $user = Auth::user();

        // Role PUSAT bisa akses semua
        if ($user->hasRole(['admin_back_office', 'admin_gudang', 'owner'])) {
            return true;
        }

        // Role CABANG (admin_store) hanya bisa akses pembelian toko mereka
        if ($user->hasRole(['admin_store'])) {
            return $user->store_id === $purchase->store_id;
        }

        return false;
    }
}
