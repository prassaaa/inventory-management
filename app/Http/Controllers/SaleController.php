<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\Store;
use App\Models\Product;
use App\Models\StockStore;
use App\Models\Category;
use App\Models\Unit;
use App\Models\ProductStorePrice;
use App\Helpers\UnitConverter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use PDF;

class SaleController extends Controller
{
    /**
     * Display a listing of the resource with proper filtering.
     */
    public function index()
    {
        $user = Auth::user();

        // Query dasar
        $query = Sale::with(['store', 'creator']);

        // Filter berdasarkan role dan store_id
        if ($user->hasRole(['admin_back_office', 'admin_gudang', 'owner'])) {
            // Role PUSAT bisa lihat semua penjualan dari semua toko
            // Tidak perlu filter tambahan
        } elseif ($user->hasRole(['admin_store', 'kasir'])) {
            // Role CABANG hanya bisa lihat penjualan toko mereka
            if ($user->store_id) {
                $query->where('store_id', $user->store_id);
            } else {
                // Jika tidak ada store_id, kembalikan collection kosong
                $query->whereRaw('1 = 0');
            }
        } else {
            // Role lain tidak bisa melihat penjualan
            $query->whereRaw('1 = 0');
        }

        $sales = $query->orderBy('date', 'desc')->get();

        return view('sales.index', compact('sales'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return redirect()->route('pos');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Redirect to POS
        return redirect()->route('pos');
    }

    /**
     * Display the specified resource.
     */
    public function show(Sale $sale)
    {
        // Check if user has access to this sale
        if (!$this->canUserViewSale($sale)) {
            abort(403, 'Anda tidak memiliki akses untuk melihat penjualan ini.');
        }

        $sale->load(['store', 'creator', 'saleDetails.product', 'saleDetails.unit']);

        return view('sales.show', compact('sale'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Sale $sale)
    {
        // Check permission
        if (!$this->canUserAccessSale($sale)) {
            abort(403, 'Anda tidak memiliki akses untuk mengedit penjualan ini.');
        }

        // Load necessary relationships
        $sale->load(['store', 'creator', 'saleDetails.product.baseUnit', 'saleDetails.unit']);

        // Get products for dropdown
        $products = Product::with(['baseUnit', 'category'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        // Get units for dropdown
        $units = Unit::orderBy('name')->get();

        return view('sales.edit', compact('sale', 'products', 'units'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Sale $sale)
    {
        // Check permission
        if (!$this->canUserAccessSale($sale)) {
            abort(403, 'Anda tidak memiliki akses untuk mengedit penjualan ini.');
        }

        $validated = $request->validate([
            'customer_name' => 'nullable|string|max:255',
            'payment_type' => 'required|in:tunai,non_tunai',
            'dining_option' => 'required|in:makan_di_tempat,dibawa_pulang',
            'discount' => 'required|numeric|min:0',
            'tax_enabled' => 'required|boolean',
            'tax' => 'required|numeric|min:0',
            'total_payment' => 'required|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.unit_id' => 'required|exists:units,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.discount' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            // Hitung ulang total
            $subtotal = 0;
            foreach ($validated['items'] as $item) {
                $subtotal += ($item['quantity'] * $item['price']) - $item['discount'];
            }

            $totalAmount = $subtotal - $validated['discount'] + $validated['tax'];
            $change = $validated['total_payment'] - $totalAmount;

            // Update sale
            $sale->update([
                'customer_name' => $validated['customer_name'],
                'payment_type' => $validated['payment_type'],
                'dining_option' => $validated['dining_option'],
                'discount' => $validated['discount'],
                'tax_enabled' => $validated['tax_enabled'],
                'tax' => $validated['tax'],
                'total_amount' => $totalAmount,
                'total_payment' => $validated['total_payment'],
                'change' => $change,
                'updated_by' => Auth::id(),
            ]);

            // Kembalikan stok dari detail lama (reverse stock)
            foreach ($sale->saleDetails as $oldDetail) {
                $this->reverseStock($oldDetail);
            }

            // Hapus detail lama
            $sale->saleDetails()->delete();

            // Buat detail baru dan kurangi stok
            foreach ($validated['items'] as $item) {
                $saleDetail = SaleDetail::create([
                    'sale_id' => $sale->id,
                    'product_id' => $item['product_id'],
                    'unit_id' => $item['unit_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'discount' => $item['discount'],
                    'subtotal' => ($item['quantity'] * $item['price']) - $item['discount'],
                ]);

                // Kurangi stok baru
                $this->reduceStock($saleDetail, $sale->store_id);
            }

            DB::commit();

            return redirect()->route('sales.index')
                ->with('success', 'Penjualan berhasil diupdate.');

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error updating sale', [
                'sale_id' => $sale->id,
                'error' => $e->getMessage()
            ]);

            return back()->withErrors(['error' => 'Gagal mengupdate penjualan: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Sale $sale)
    {
        // Check permission
        if (!$this->canUserAccessSale($sale)) {
            abort(403, 'Anda tidak memiliki akses untuk menghapus penjualan ini.');
        }

        try {
            DB::beginTransaction();

            // Kembalikan stok
            foreach ($sale->saleDetails as $detail) {
                $this->reverseStock($detail);
            }

            // Hapus detail penjualan
            $sale->saleDetails()->delete();

            // Hapus penjualan
            $sale->delete();

            DB::commit();

            return redirect()->route('sales.index')
                ->with('success', 'Penjualan berhasil dihapus.');

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error deleting sale', [
                'sale_id' => $sale->id,
                'error' => $e->getMessage()
            ]);

            return back()->withErrors(['error' => 'Gagal menghapus penjualan: ' . $e->getMessage()]);
        }
    }

    /**
     * Display POS interface.
     */
   public function pos()
    {
        $categories = Category::where('show_in_pos', true)
                            ->orderBy('name')
                            ->get();

        $storeId = Auth::user()->store_id;

        Log::info('POS accessed by user', [
            'user_id' => Auth::id(),
            'store_id' => $storeId
        ]);

        // Ambil semua produk yang aktif dengan eager loading
        $products = Product::with(['category', 'baseUnit', 'productUnits.unit'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        // Ambil semua data stok untuk toko ini
        $stocks = StockStore::where('store_id', $storeId)->get();
        $stockMap = [];
        foreach ($stocks as $stock) {
            $stockMap[$stock->product_id] = $stock;
        }

        // Ambil harga khusus untuk store ini
        $storePrices = ProductStorePrice::where('store_id', $storeId)
            ->where('is_active', true)
            ->get()
            ->groupBy('product_id');

        // Hubungkan data dengan produk
        foreach ($products as $product) {
            // Set stok
            $product->storeStock = $stockMap[$product->id] ?? null;

            // Set harga untuk store ini (unit dasar)
            $product->store_selling_price = $product->getPriceForStore($storeId);
            $product->has_custom_price = $product->hasCustomPriceForStore($storeId);

            // Set harga untuk unit tambahan jika ada
            if ($product->productUnits->count() > 0) {
                foreach ($product->productUnits as $productUnit) {
                    $productUnit->store_selling_price = $product->getPriceForStore($storeId, $productUnit->unit_id);
                }
            }
        }

        return view('sales.pos', compact('categories', 'products', 'stockMap', 'storePrices'));
    }

    /**
     * Process POS sale - Complete working version with flexible validation
     */
    public function processPos(Request $request)
    {
        $validated = $request->validate([
            'sale.store_id' => 'required|exists:stores,id',
            'sale.payment_type' => 'required|in:tunai,non_tunai',
            'sale.dining_option' => 'required|in:makan_di_tempat,dibawa_pulang',
            'sale.customer_name' => 'nullable|string|max:255',
            'sale.discount' => 'required|numeric|min:0',
            'sale.tax_enabled' => 'required', // Flexible - accept any value
            'sale.tax' => 'required|numeric|min:0',
            'sale.total_amount' => 'required|numeric|min:0',
            'sale.total_payment' => 'required|numeric|min:0',
            'sale.change' => 'required|numeric|min:0',
            'sale.items' => 'required|array|min:1',
            'sale.items.*.product_id' => 'required|exists:products,id',
            'sale.items.*.unit_id' => 'required|exists:units,id',
            'sale.items.*.quantity' => 'required|numeric|min:0.01',
            'sale.items.*.price' => 'required|numeric|min:0',
            'sale.items.*.discount' => 'required|numeric|min:0',
            'sale.items.*.subtotal' => 'required|numeric|min:0',
            'sale.items.*.is_processed' => 'nullable', // Flexible - accept any value
        ]);

        $saleData = $request->sale;

        try {
            DB::beginTransaction();

            // Log awal proses penjualan
            Log::info('Mulai proses penjualan', [
                'store_id' => $saleData['store_id'],
                'jumlah_item' => count($saleData['items']),
                'total_amount' => $saleData['total_amount']
            ]);

            // Generate invoice number
            $lastSale = Sale::where('store_id', $saleData['store_id'])
                ->latest()
                ->first();

            $storeCode = Store::find($saleData['store_id'])->name[0] ?? 'S';
            $invoiceNumber = 'INV/' . $storeCode . '/' . date('Ymd') . '/' . sprintf('%04d', $lastSale ? (int)substr($lastSale->invoice_number, -4) + 1 : 1);

            // Convert tax_enabled to boolean manually
            $taxEnabled = filter_var($saleData['tax_enabled'], FILTER_VALIDATE_BOOLEAN);

            // Create sale
            $sale = Sale::create([
                'store_id' => $saleData['store_id'],
                'invoice_number' => $invoiceNumber,
                'date' => now(),
                'customer_name' => $saleData['customer_name'],
                'total_amount' => $saleData['total_amount'],
                'payment_type' => $saleData['payment_type'],
                'dining_option' => $saleData['dining_option'],
                'discount' => $saleData['discount'],
                'tax' => $saleData['tax'],
                'tax_enabled' => $taxEnabled, // Use converted boolean
                'total_payment' => $saleData['total_payment'],
                'change' => $saleData['change'],
                'status' => 'paid',
                'created_by' => Auth::id(),
            ]);

            // Log pembuatan data penjualan
            Log::info('Data penjualan dibuat', [
                'sale_id' => $sale->id,
                'invoice' => $sale->invoice_number,
                'total' => $sale->total_amount
            ]);

            // Create sale details and update stock
            foreach ($saleData['items'] as $index => $item) {
                // Log informasi item yang diproses
                Log::info('Memproses item penjualan', [
                    'index' => $index,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_id' => $item['unit_id']
                ]);

                // Buat detail penjualan
                $saleDetail = SaleDetail::create([
                    'sale_id' => $sale->id,
                    'product_id' => $item['product_id'],
                    'unit_id' => $item['unit_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'discount' => $item['discount'],
                    'subtotal' => $item['subtotal'],
                ]);

                Log::info('Detail penjualan dibuat', [
                    'sale_detail_id' => $saleDetail->id
                ]);

                // Get product from database
                $product = Product::find($item['product_id']);

                if (!$product) {
                    throw new \Exception("Produk dengan ID {$item['product_id']} tidak ditemukan.");
                }

                Log::info('Info produk', [
                    'product_id' => $product->id,
                    'name' => $product->name,
                    'base_unit_id' => $product->base_unit_id,
                    'db_is_processed' => $product->is_processed
                ]);

                // ALWAYS use database value for is_processed - not from frontend
                $useProcessed = $product->is_processed;

                Log::info('Menggunakan is_processed dari database', [
                    'product_name' => $product->name,
                    'is_processed' => $useProcessed
                ]);

                // Check if product is processed
                if ($useProcessed) {
                    Log::info('Memproses produk olahan', [
                        'product_id' => $product->id,
                        'product_name' => $product->name
                    ]);

                    // For processed products, reduce stock of ingredients
                    try {
                        // Ambil bahan-bahan secara manual dengan query langsung
                        $ingredientsQuery = DB::table('product_ingredients')
                            ->where('product_id', $product->id)
                            ->get();

                        Log::info('Jumlah bahan yang perlu diproses', [
                            'product_id' => $product->id,
                            'ingredients_count' => $ingredientsQuery->count()
                        ]);

                        foreach ($ingredientsQuery as $ingredientData) {
                            // Ambil data produk bahan dan satuan
                            $ingredient = Product::find($ingredientData->ingredient_id);

                            if (!$ingredient) {
                                Log::warning('Bahan tidak ditemukan', [
                                    'ingredient_id' => $ingredientData->ingredient_id
                                ]);
                                continue;
                            }

                            // Jumlah bahan yang dibutuhkan, sesuai satuan di resep
                            $ingredientQuantity = $ingredientData->quantity * $item['quantity'];

                            Log::info('Memproses bahan', [
                                'ingredient_id' => $ingredient->id,
                                'ingredient_name' => $ingredient->name,
                                'quantity_needed' => $ingredientQuantity,
                                'unit_id' => $ingredientData->unit_id
                            ]);

                            // Find store stock for this ingredient
                            $ingredientStockStore = StockStore::where([
                                'store_id' => $saleData['store_id'],
                                'product_id' => $ingredient->id
                            ])->first();

                            // Jika tidak ditemukan stok, buat baru dengan stok 0
                            if (!$ingredientStockStore) {
                                Log::info('Stok bahan tidak ditemukan, membuat stok baru', [
                                    'ingredient_id' => $ingredient->id,
                                    'ingredient_name' => $ingredient->name
                                ]);

                                $ingredientStockStore = StockStore::create([
                                    'store_id' => $saleData['store_id'],
                                    'product_id' => $ingredient->id,
                                    'unit_id' => $ingredient->base_unit_id,
                                    'quantity' => 0
                                ]);
                            }

                            // Konversi jumlah bahan jika satuan berbeda
                            $quantityToReduce = $ingredientQuantity;

                            if ($ingredientData->unit_id !== $ingredientStockStore->unit_id) {
                                Log::info('Satuan resep dan stok berbeda, perlu konversi', [
                                    'ingredient_name' => $ingredient->name,
                                    'recipe_unit_id' => $ingredientData->unit_id,
                                    'stock_unit_id' => $ingredientStockStore->unit_id
                                ]);

                                // Gunakan helper konversi
                                $convertedQuantity = UnitConverter::convert(
                                    $ingredientQuantity,
                                    $ingredientData->unit_id,
                                    $ingredientStockStore->unit_id
                                );

                                if ($convertedQuantity === null) {
                                    Log::error('Konversi satuan gagal', [
                                        'ingredient_name' => $ingredient->name,
                                        'from_unit_id' => $ingredientData->unit_id,
                                        'to_unit_id' => $ingredientStockStore->unit_id
                                    ]);

                                    throw new \Exception("Tidak dapat mengkonversi satuan untuk bahan {$ingredient->name}");
                                }

                                $quantityToReduce = $convertedQuantity;

                                Log::info('Konversi satuan berhasil', [
                                    'ingredient_name' => $ingredient->name,
                                    'original_quantity' => $ingredientQuantity,
                                    'converted_quantity' => $quantityToReduce,
                                    'from_unit' => $ingredientData->unit_id,
                                    'to_unit' => $ingredientStockStore->unit_id
                                ]);
                            }

                            // Cek stok - HANYA WARNING, TIDAK BLOCK TRANSAKSI
                            if ($ingredientStockStore->quantity < $quantityToReduce) {
                                Log::warning('Stok bahan tidak cukup - melanjutkan transaksi', [
                                    'ingredient_id' => $ingredient->id,
                                    'ingredient_name' => $ingredient->name,
                                    'available' => $ingredientStockStore->quantity,
                                    'needed' => $quantityToReduce
                                ]);

                                // Don't throw exception - just continue with negative stock
                            }

                            // Simpan stok sebelum pengurangan
                            $stockBefore = $ingredientStockStore->quantity;

                            // Kurangi stok (bisa jadi negatif)
                            DB::table('stock_stores')
                                ->where('id', $ingredientStockStore->id)
                                ->decrement('quantity', $quantityToReduce);

                            // Muat ulang data
                            $ingredientStockStore->refresh();

                            Log::info('Stok bahan berhasil dikurangi', [
                                'ingredient_id' => $ingredient->id,
                                'ingredient_name' => $ingredient->name,
                                'before' => $stockBefore,
                                'reduced_by' => $quantityToReduce,
                                'after' => $ingredientStockStore->quantity
                            ]);
                        }
                    } catch (\Exception $e) {
                        Log::error('Error saat memproses bahan produk', [
                            'product_id' => $product->id,
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString()
                        ]);
                        throw $e;
                    }
                } else {
                    Log::info('Memproses produk reguler', [
                        'product_id' => $product->id,
                        'product_name' => $product->name
                    ]);

                    // Untuk produk reguler, konversi ke satuan dasar jika diperlukan
                    $baseQuantity = $item['quantity'];

                    // Jika unit yang digunakan berbeda dengan unit dasar produk
                    if ($item['unit_id'] != $product->base_unit_id) {
                        Log::info('Perlu konversi satuan untuk produk reguler', [
                            'product_name' => $product->name,
                            'from_unit_id' => $item['unit_id'],
                            'to_unit_id' => $product->base_unit_id
                        ]);

                        // Gunakan UnitConverter untuk konversi
                        $baseQuantity = UnitConverter::convert(
                            $item['quantity'],
                            $item['unit_id'],
                            $product->base_unit_id
                        );

                        if ($baseQuantity === null) {
                            Log::error('Konversi satuan gagal untuk produk reguler', [
                                'product_name' => $product->name,
                                'from_unit_id' => $item['unit_id'],
                                'to_unit_id' => $product->base_unit_id
                            ]);

                            throw new \Exception("Tidak dapat mengkonversi satuan untuk produk {$product->name}");
                        }

                        Log::info('Konversi satuan produk reguler berhasil', [
                            'product_name' => $product->name,
                            'original_quantity' => $item['quantity'],
                            'converted_quantity' => $baseQuantity
                        ]);
                    }

                    // Dapatkan atau buat stok dengan satuan dasar
                    $stockStore = StockStore::firstOrCreate(
                        [
                            'store_id' => $saleData['store_id'],
                            'product_id' => $item['product_id'],
                            'unit_id' => $product->base_unit_id
                        ],
                        ['quantity' => 0]
                    );

                    Log::info('Stok produk ditemukan', [
                        'stock_id' => $stockStore->id,
                        'current_quantity' => $stockStore->quantity,
                        'needed_quantity' => $baseQuantity
                    ]);

                    // Cek stok - HANYA WARNING, TIDAK BLOCK TRANSAKSI
                    if ($stockStore->quantity < $baseQuantity) {
                        Log::warning('Stok produk tidak cukup - melanjutkan transaksi', [
                            'product_id' => $product->id,
                            'product_name' => $product->name,
                            'available' => $stockStore->quantity,
                            'needed' => $baseQuantity
                        ]);

                        // Don't throw exception - just continue
                    }

                    // Simpan stok sebelum pengurangan
                    $stockBefore = $stockStore->quantity;

                    // Kurangi stok dengan query database langsung (bisa jadi negatif)
                    DB::table('stock_stores')
                        ->where('id', $stockStore->id)
                        ->decrement('quantity', $baseQuantity);

                    // Muat ulang data dari database
                    $stockStore->refresh();

                    Log::info('Stok produk berhasil dikurangi', [
                        'product_id' => $product->id,
                        'before' => $stockBefore,
                        'reduced_by' => $baseQuantity,
                        'after' => $stockStore->quantity
                    ]);
                }
            }

            // Commit transaction
            DB::commit();

            Log::info('Transaksi penjualan berhasil', [
                'sale_id' => $sale->id,
                'invoice' => $sale->invoice_number
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Penjualan berhasil diproses.',
                'invoice_number' => $sale->invoice_number,
                'receipt_url' => route('sales.receipt', $sale),
            ]);
        } catch (\Exception $e) {
            // Rollback transaction in case of error
            DB::rollBack();

            Log::error('Error saat memproses penjualan', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Generate sale receipt and print directly
     */
    public function receipt(Sale $sale)
    {
        $sale->load(['store', 'creator', 'saleDetails.product', 'saleDetails.unit']);

        // Create a safe filename by replacing slashes with underscores
        $safeFilename = str_replace(['/', '\\'], '_', $sale->invoice_number);

        // Render receipt view to HTML
        return view('sales.receipt-print', compact('sale'));
    }

    /**
     * Generate sale receipt optimized for RAWBT Printer
     */
    public function rawbtReceipt(Sale $sale)
    {
        $sale->load(['store', 'creator', 'saleDetails.product', 'saleDetails.unit']);

        // Render receipt view khusus untuk RAWBT printer
        return view('sales.receipt-rawbt', compact('sale'));
    }

    /**
     * Check if user can view the sale (for show method)
     */
    private function canUserViewSale(Sale $sale)
    {
        $user = Auth::user();

        // Role PUSAT bisa akses semua
        if ($user->hasRole(['admin_back_office', 'admin_gudang', 'owner'])) {
            return true;
        }

        // Role CABANG hanya bisa akses penjualan toko mereka
        if ($user->hasRole(['admin_store', 'kasir'])) {
            return $user->store_id === $sale->store_id;
        }

        return false;
    }

    /**
     * Check if user can access the sale (based on role and store) for edit/delete
     */
    private function canUserAccessSale(Sale $sale)
    {
        $user = Auth::user();

        // Role PUSAT bisa akses semua
        if ($user->hasRole(['admin_back_office', 'admin_gudang', 'owner'])) {
            return true;
        }

        // Role CABANG (admin_store) hanya bisa akses penjualan toko mereka
        if ($user->hasRole(['admin_store'])) {
            return $user->store_id === $sale->store_id;
        }

        // KASIR tidak bisa edit/hapus
        if ($user->hasRole(['kasir'])) {
            return false;
        }

        return false;
    }

    /**
     * Reverse stock when updating/deleting sale
     */
    private function reverseStock(SaleDetail $detail)
    {
        $product = Product::find($detail->product_id);

        if ($product->is_processed) {
            // Untuk produk olahan, kembalikan stok bahan
            $ingredients = DB::table('product_ingredients')
                ->where('product_id', $product->id)
                ->get();

            foreach ($ingredients as $ingredient) {
                $ingredientProduct = Product::find($ingredient->ingredient_id);
                $storeId = $detail->sale->store_id;

                // Hitung jumlah bahan yang dikembalikan
                $ingredientQuantity = $ingredient->quantity * $detail->quantity;

                // Konversi satuan jika perlu
                $stockStore = StockStore::where([
                    'store_id' => $storeId,
                    'product_id' => $ingredientProduct->id
                ])->first();

                if ($stockStore) {
                    $quantityToAdd = $ingredientQuantity;

                    if ($ingredient->unit_id !== $stockStore->unit_id) {
                        $quantityToAdd = UnitConverter::convert(
                            $ingredientQuantity,
                            $ingredient->unit_id,
                            $stockStore->unit_id
                        );
                    }

                    if ($quantityToAdd !== null) {
                        $stockStore->increment('quantity', $quantityToAdd);
                    }
                }
            }
        } else {
            // Untuk produk reguler, kembalikan stok langsung
            $baseQuantity = $detail->quantity;

            if ($detail->unit_id != $product->base_unit_id) {
                $baseQuantity = UnitConverter::convert(
                    $detail->quantity,
                    $detail->unit_id,
                    $product->base_unit_id
                );
            }

            if ($baseQuantity !== null) {
                $stockStore = StockStore::where([
                    'store_id' => $detail->sale->store_id,
                    'product_id' => $product->id
                ])->first();

                if ($stockStore) {
                    $stockStore->increment('quantity', $baseQuantity);
                }
            }
        }
    }

    /**
     * Reduce stock when creating new sale detail
     */
    private function reduceStock(SaleDetail $detail, $storeId)
    {
        $product = Product::find($detail->product_id);

        if ($product->is_processed) {
            // Logic untuk produk olahan (sama seperti di processPos)
            $ingredients = DB::table('product_ingredients')
                ->where('product_id', $product->id)
                ->get();

            foreach ($ingredients as $ingredient) {
                $ingredientProduct = Product::find($ingredient->ingredient_id);
                $ingredientQuantity = $ingredient->quantity * $detail->quantity;

                $stockStore = StockStore::where([
                    'store_id' => $storeId,
                    'product_id' => $ingredientProduct->id
                ])->first();

                if ($stockStore) {
                    $quantityToReduce = $ingredientQuantity;

                    if ($ingredient->unit_id !== $stockStore->unit_id) {
                        $quantityToReduce = UnitConverter::convert(
                            $ingredientQuantity,
                            $ingredient->unit_id,
                            $stockStore->unit_id
                        );
                    }

                    if ($quantityToReduce !== null && $stockStore->quantity >= $quantityToReduce) {
                        $stockStore->decrement('quantity', $quantityToReduce);
                    } else {
                        throw new \Exception("Stok bahan {$ingredientProduct->name} tidak mencukupi.");
                    }
                }
            }
        } else {
            // Logic untuk produk reguler
            $baseQuantity = $detail->quantity;

            if ($detail->unit_id != $product->base_unit_id) {
                $baseQuantity = UnitConverter::convert(
                    $detail->quantity,
                    $detail->unit_id,
                    $product->base_unit_id
                );
            }

            if ($baseQuantity !== null) {
                $stockStore = StockStore::firstOrCreate(
                    [
                        'store_id' => $storeId,
                        'product_id' => $product->id,
                        'unit_id' => $product->base_unit_id
                    ],
                    ['quantity' => 0]
                );

                if ($stockStore->quantity >= $baseQuantity) {
                    $stockStore->decrement('quantity', $baseQuantity);
                } else {
                    throw new \Exception("Stok produk {$product->name} tidak mencukupi.");
                }
            }
        }
    }
}
