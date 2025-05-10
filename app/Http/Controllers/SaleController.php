<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\Store;
use App\Models\Product;
use App\Models\StockStore;
use App\Models\Category;
use App\Models\Unit;
use App\Helpers\UnitConverter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use PDF;

class SaleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $sales = Sale::with(['store', 'creator'])
            ->where('store_id', Auth::user()->store_id)
            ->orderBy('date', 'desc')
            ->get();

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
        // Check if user has access to this sale (same store)
        if (Auth::user()->store_id !== $sale->store_id && !Auth::user()->hasRole('owner')) {
            abort(403, 'Unauthorized action.');
        }

        $sale->load(['store', 'creator', 'saleDetails.product', 'saleDetails.unit']);

        return view('sales.show', compact('sale'));
    }

    /**
     * Display POS interface.
     */
    public function pos()
    {
        // Ubah query untuk hanya menampilkan kategori yang diizinkan di POS
        $categories = Category::where('show_in_pos', true)
                            ->orderBy('name')
                            ->get();

        $storeId = Auth::user()->store_id;

        // Log untuk debugging
        Log::info('POS accessed by user', [
            'user_id' => Auth::id(),
            'store_id' => $storeId
        ]);

        // Ambil semua produk yang aktif dengan eager loading category dan baseUnit
        $products = Product::with(['category', 'baseUnit'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        // Ambil semua data stok untuk toko ini dalam satu query
        $stocks = StockStore::where('store_id', $storeId)->get();

        // Log jumlah stok yang ditemukan
        Log::info('Stock data loaded', [
            'store_id' => $storeId,
            'stock_count' => $stocks->count()
        ]);

        // Buat mapping product_id ke stok untuk lookup yang cepat
        $stockMap = [];
        foreach ($stocks as $stock) {
            $key = $stock->product_id;
            $stockMap[$key] = $stock;
        }

        // Hubungkan stok dengan produk
        foreach ($products as $product) {
            if (isset($stockMap[$product->id])) {
                $product->storeStock = $stockMap[$product->id];

                // Log untuk debugging produk dengan stok
                if ($stockMap[$product->id]->quantity > 0) {
                    Log::info('Product with stock', [
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'stock' => $stockMap[$product->id]->quantity
                    ]);
                }
            } else {
                // Jika tidak ada stok, buat objek kosong
                $product->storeStock = null;
            }
        }

        // Penyederhanaan cara mengakses stok dalam view
        return view('sales.pos', compact('categories', 'products', 'stockMap'));
    }

    /**
     * Process POS sale dengan konversi satuan yang ditingkatkan.
     */
    public function processPos(Request $request)
    {
        $validated = $request->validate([
            'sale.store_id' => 'required|exists:stores,id',
            'sale.payment_type' => 'required|in:tunai,non_tunai',
            'sale.customer_name' => 'nullable|string|max:255',
            'sale.discount' => 'required|numeric|min:0',
            'sale.tax_enabled' => 'required|boolean',
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
            'sale.items.*.is_processed' => 'nullable|boolean',
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

            // Create sale with tax_enabled field
            $sale = Sale::create([
                'store_id' => $saleData['store_id'],
                'invoice_number' => $invoiceNumber,
                'date' => now(),
                'customer_name' => $saleData['customer_name'],
                'total_amount' => $saleData['total_amount'],
                'payment_type' => $saleData['payment_type'],
                'discount' => $saleData['discount'],
                'tax' => $saleData['tax'],
                'tax_enabled' => $saleData['tax_enabled'],
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
                // Normalisasi nilai is_processed
                $isProcessed = false;
                if (isset($item['is_processed'])) {
                    if (is_bool($item['is_processed'])) {
                        $isProcessed = $item['is_processed'];
                    } elseif (is_string($item['is_processed'])) {
                        $isProcessed = in_array(strtolower($item['is_processed']), ['true', '1', 'yes', 'on']);
                    } elseif (is_numeric($item['is_processed'])) {
                        $isProcessed = (int)$item['is_processed'] === 1;
                    }
                }

                // Log informasi item yang diproses
                Log::info('Memproses item penjualan', [
                    'index' => $index,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_id' => $item['unit_id'],
                    'is_processed' => $isProcessed
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

                // Get product
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

                // Gunakan nilai is_processed dari database (bukan dari request) jika produk benar-benar olahan
                $useProcessed = $product->is_processed;

                Log::info('Keputusan penggunaan is_processed', [
                    'from_request' => $isProcessed,
                    'from_database' => $product->is_processed,
                    'final_decision' => $useProcessed
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

                            // Jika tidak ditemukan stok, buat baru
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

                            // Cek stok
                            if ($ingredientStockStore->quantity < $quantityToReduce) {
                                Log::warning('Stok bahan tidak cukup', [
                                    'ingredient_id' => $ingredient->id,
                                    'ingredient_name' => $ingredient->name,
                                    'available' => $ingredientStockStore->quantity,
                                    'needed' => $quantityToReduce
                                ]);

                                throw new \Exception("Stok bahan {$ingredient->name} tidak mencukupi untuk produk {$product->name}.");
                            }

                            // Simpan stok sebelum pengurangan
                            $stockBefore = $ingredientStockStore->quantity;

                            // Kurangi stok
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

                    // Cek stok
                    if ($stockStore->quantity < $baseQuantity) {
                        Log::warning('Stok produk tidak cukup', [
                            'product_id' => $product->id,
                            'product_name' => $product->name,
                            'available' => $stockStore->quantity,
                            'needed' => $baseQuantity
                        ]);

                        throw new \Exception("Stok tidak cukup untuk produk {$product->name}.");
                    }

                    // Simpan stok sebelum pengurangan
                    $stockBefore = $stockStore->quantity;

                    // Kurangi stok dengan query database langsung
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
     *
     * @param Sale $sale
     * @return \Illuminate\Http\Response
     */
    public function rawbtReceipt(Sale $sale)
    {
        $sale->load(['store', 'creator', 'saleDetails.product', 'saleDetails.unit']);

        // Render receipt view khusus untuk RAWBT printer
        return view('sales.receipt-rawbt', compact('sale'));
    }
}
