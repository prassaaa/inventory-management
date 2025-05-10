<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Unit;
use App\Models\ProductUnit;
use App\Models\StockWarehouse;
use App\Models\StockStore;
use App\Models\PurchaseDetail;
use App\Models\SaleDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ProductsExport;
use App\Imports\ProductsImport;
use App\Models\Store;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = Product::with(['category', 'baseUnit', 'stockWarehouses'])->orderBy('name')->get();
        return view('products.index', compact('products'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = Category::orderBy('name')->get();
        $baseUnits = Unit::where('is_base_unit', true)->orderBy('name')->get();
        $units = Unit::orderBy('name')->get();
        $stores = Store::orderBy('name')->get();
        $centralProducts = Product::where('store_source', 'pusat')->where('is_active', true)->orderBy('name')->get();

        return view('products.create', compact('categories', 'baseUnits', 'units', 'stores', 'centralProducts'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'nullable|string|max:50|unique:products',
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'base_unit_id' => 'required|exists:units,id',
            'purchase_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'min_stock' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:2048', // max 2MB
            'is_active' => 'sometimes|boolean',
            'store_source' => 'required|in:pusat,store',
            // Hilangkan validasi required_if untuk store_id
            'is_processed' => 'sometimes|boolean',
            'ingredients' => 'nullable|array',
            'ingredients.*.ingredient_id' => 'nullable|required_if:is_processed,1|exists:products,id',
            'ingredients.*.quantity' => 'nullable|required_if:is_processed,1|numeric|min:0.01',
            'ingredients.*.unit_id' => 'nullable|required_if:is_processed,1|exists:units,id',
            'additional_units' => 'nullable|array',
            'additional_units.*.unit_id' => 'nullable|exists:units,id',
            'additional_units.*.conversion_value' => 'nullable|numeric|min:0.0001',
            'additional_units.*.purchase_price' => 'nullable|numeric|min:0',
            'additional_units.*.selling_price' => 'nullable|numeric|min:0',
        ]);

        // Handle input with currency format
        if ($request->has('purchase_price_real')) {
            $validated['purchase_price'] = $request->purchase_price_real;
        }

        if ($request->has('selling_price_real')) {
            $validated['selling_price'] = $request->selling_price_real;
        }

        // Convert min_stock to integer if it's actually a whole number
        $minStock = floatval($validated['min_stock']);
        if (floor($minStock) == $minStock) {
            $validated['min_stock'] = intval($minStock);
        }

        // Generate product code if not provided
        if (empty($validated['code'])) {
            $latestProduct = Product::latest()->first();
            $latestCode = $latestProduct ? $latestProduct->code : 'P0000';
            $numericPart = (int)substr($latestCode, 1);
            $validated['code'] = 'P' . str_pad($numericPart + 1, 4, '0', STR_PAD_LEFT);
        }

        // Handle image upload
        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('products', 'public');
        }

        // Set boolean values
        $validated['is_active'] = isset($validated['is_active']) && $validated['is_active'] == 1;
        $validated['is_processed'] = isset($validated['is_processed']) && $validated['is_processed'] == 1;

        // Set store_id = null untuk semua produk
        $validated['store_id'] = null;

        // Create product
        $product = Product::create($validated);

        // Create initial warehouse stock
        if ($validated['store_source'] === 'pusat') {
            StockWarehouse::create([
                'product_id' => $product->id,
                'unit_id' => $product->base_unit_id,
                'quantity' => 0
            ]);
        } else {
            // Jika store, buat stok untuk semua store
            $stores = Store::all();
            foreach ($stores as $store) {
                StockStore::create([
                    'store_id' => $store->id,
                    'product_id' => $product->id,
                    'unit_id' => $product->base_unit_id,
                    'quantity' => 0
                ]);
            }
        }

        // Process ingredients for processed products
        if ($validated['is_processed'] && isset($request->ingredients)) {
            foreach ($request->ingredients as $ingredient) {
                if (!empty($ingredient['ingredient_id']) && !empty($ingredient['quantity'])) {
                    DB::table('product_ingredients')->insert([
                        'product_id' => $product->id,
                        'ingredient_id' => $ingredient['ingredient_id'],
                        'quantity' => $ingredient['quantity'],
                        'unit_id' => $ingredient['unit_id'],
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
            }
        }

        // Process additional units
        if (isset($validated['additional_units'])) {
            foreach ($validated['additional_units'] as $unitData) {
                if (!empty($unitData['unit_id']) && !empty($unitData['conversion_value'])) {
                    // Handle input with currency format for additional units
                    $purchasePrice = isset($unitData['purchase_price_real']) ?
                                    $unitData['purchase_price_real'] :
                                    ($unitData['purchase_price'] ?? 0);

                    $sellingPrice = isset($unitData['selling_price_real']) ?
                                    $unitData['selling_price_real'] :
                                    ($unitData['selling_price'] ?? 0);

                    ProductUnit::create([
                        'product_id' => $product->id,
                        'unit_id' => $unitData['unit_id'],
                        'conversion_value' => $unitData['conversion_value'],
                        'purchase_price' => $purchasePrice,
                        'selling_price' => $sellingPrice,
                    ]);
                }
            }
        }

        return redirect()->route('products.index')
            ->with('success', 'Product created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        $product->load(['category', 'baseUnit', 'productUnits.unit', 'stockWarehouses', 'storeStocks.store']);

        // Get recent purchase details
        $recentPurchases = PurchaseDetail::with(['purchase.supplier', 'unit'])
            ->where('product_id', $product->id)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // Get recent sale details
        $recentSales = SaleDetail::with(['sale.store', 'unit'])
            ->where('product_id', $product->id)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return view('products.show', compact('product', 'recentPurchases', 'recentSales'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        $categories = Category::orderBy('name')->get();
        $baseUnits = Unit::where('is_base_unit', true)->orderBy('name')->get();
        $units = Unit::orderBy('name')->get();
        $stores = Store::orderBy('name')->get();
        $centralProducts = Product::where('store_source', 'pusat')->where('is_active', true)->orderBy('name')->get();

        $product->load(['productUnits', 'ingredients.baseUnit']);

        return view('products.edit', compact('product', 'categories', 'baseUnits', 'units', 'stores', 'centralProducts'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'base_unit_id' => 'required|exists:units,id',
            'purchase_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'min_stock' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:2048', // max 2MB
            'is_active' => 'sometimes|boolean',
            'store_source' => 'required|in:pusat,store',
            // Hilangkan validasi required_if untuk store_id
            'is_processed' => 'sometimes|boolean',
            'ingredients' => 'nullable|array',
            'ingredients.*.ingredient_id' => 'nullable|required_if:is_processed,1|exists:products,id',
            'ingredients.*.quantity' => 'nullable|required_if:is_processed,1|numeric|min:0.01',
            'ingredients.*.unit_id' => 'nullable|required_if:is_processed,1|exists:units,id',
            'additional_units' => 'nullable|array',
            'additional_units.*.unit_id' => 'nullable|exists:units,id',
            'additional_units.*.conversion_value' => 'nullable|numeric|min:0.0001',
            'additional_units.*.purchase_price' => 'nullable|numeric|min:0',
            'additional_units.*.selling_price' => 'nullable|numeric|min:0',
        ]);

        // Handle input with currency format
        if ($request->has('purchase_price_real')) {
            $validated['purchase_price'] = $request->purchase_price_real;
        }

        if ($request->has('selling_price_real')) {
            $validated['selling_price'] = $request->selling_price_real;
        }

        // Convert min_stock to integer if it's actually a whole number
        $minStock = floatval($validated['min_stock']);
        if (floor($minStock) == $minStock) {
            $validated['min_stock'] = intval($minStock);
        }

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $validated['image'] = $request->file('image')->store('products', 'public');
        }

        // Set boolean values
        $validated['is_active'] = isset($validated['is_active']) && $validated['is_active'] == 1;
        $validated['is_processed'] = isset($validated['is_processed']) && $validated['is_processed'] == 1;

        // Set store_id = null untuk semua produk
        $validated['store_id'] = null;

        // Begin transaction
        DB::beginTransaction();

        try {
            // Update product
            $product->update($validated);

            // Update stocks based on store_source change
            $oldStoreSource = $product->getOriginal('store_source');

            if ($validated['store_source'] === 'pusat' && $oldStoreSource === 'store') {
                // Produk diubah dari store ke pusat
                // Hapus semua stok toko
                $product->storeStocks()->delete();

                // Buat stok gudang jika belum ada
                if (!$product->stockWarehouses()->exists()) {
                    StockWarehouse::create([
                        'product_id' => $product->id,
                        'unit_id' => $product->base_unit_id,
                        'quantity' => 0
                    ]);
                }
            }
            else if ($validated['store_source'] === 'store' && $oldStoreSource === 'pusat') {
                // Produk diubah dari pusat ke store
                // Buat stok untuk semua toko jika belum ada
                $stores = Store::all();
                foreach ($stores as $store) {
                    if (!$product->storeStocks()->where('store_id', $store->id)->exists()) {
                        StockStore::create([
                            'store_id' => $store->id,
                            'product_id' => $product->id,
                            'unit_id' => $product->base_unit_id,
                            'quantity' => 0
                        ]);
                    }
                }
            }

            // Process ingredients for processed products
            if ($validated['is_processed']) {
                // Delete existing ingredients
                DB::table('product_ingredients')->where('product_id', $product->id)->delete();

                // Add new ingredients
                if (isset($request->ingredients)) {
                    foreach ($request->ingredients as $ingredient) {
                        if (!empty($ingredient['ingredient_id']) && !empty($ingredient['quantity'])) {
                            DB::table('product_ingredients')->insert([
                                'product_id' => $product->id,
                                'ingredient_id' => $ingredient['ingredient_id'],
                                'quantity' => $ingredient['quantity'],
                                'unit_id' => $ingredient['unit_id'],
                                'created_at' => now(),
                                'updated_at' => now()
                            ]);
                        }
                    }
                }
            } else {
                // If not a processed product, delete all ingredients
                DB::table('product_ingredients')->where('product_id', $product->id)->delete();
            }

            // Process additional units
            if (isset($validated['additional_units'])) {
                // Delete existing product units
                $product->productUnits()->delete();

                // Create new product units
                foreach ($validated['additional_units'] as $unitData) {
                    if (!empty($unitData['unit_id']) && !empty($unitData['conversion_value'])) {
                        // Handle input with currency format for additional units
                        $purchasePrice = isset($unitData['purchase_price_real']) ?
                                        $unitData['purchase_price_real'] :
                                        ($unitData['purchase_price'] ?? 0);

                        $sellingPrice = isset($unitData['selling_price_real']) ?
                                        $unitData['selling_price_real'] :
                                        ($unitData['selling_price'] ?? 0);

                        ProductUnit::create([
                            'product_id' => $product->id,
                            'unit_id' => $unitData['unit_id'],
                            'conversion_value' => $unitData['conversion_value'],
                            'purchase_price' => $purchasePrice,
                            'selling_price' => $sellingPrice,
                        ]);
                    }
                }
            }

            // Commit transaction
            DB::commit();

            return redirect()->route('products.index')
                ->with('success', 'Product updated successfully.');

        } catch (\Exception $e) {
            // Rollback transaction if an error occurs
            DB::rollBack();

            return redirect()->back()
                ->with('error', 'Error updating product: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        try {
            // Dengan soft delete, kita cukup memanggil delete() dan Laravel akan mengatur kolom deleted_at
            $product->delete();

            return redirect()->route('products.index')
                ->with('success', 'Produk berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->route('products.index')
                ->with('error', 'Error menghapus produk: ' . $e->getMessage());
        }
    }

    /**
     * Download import template
     */
    public function importTemplate()
    {
        $filePath = public_path('templates/product_import_template.xlsx');
        return response()->download($filePath, 'product_import_template.xlsx');
    }

    /**
     * Import products from Excel
     */
    public function import(Request $request)
    {
        $request->validate([
            'import_file' => 'required|file|mimes:xlsx,xls,csv'
        ]);

        try {
            Excel::import(new ProductsImport, $request->file('import_file'));
            return redirect()->route('products.index')
                ->with('success', 'Products imported successfully.');
        } catch (\Exception $e) {
            return redirect()->route('products.index')
                ->with('error', 'Error importing products: ' . $e->getMessage());
        }
    }

    /**
     * Export products to Excel
     */
    public function export()
    {
        return Excel::download(new ProductsExport, 'products_' . date('Y-m-d') . '.xlsx');
    }

    /**
     * Get ingredients for processed product
     */
    public function getIngredients(Request $request)
    {
        $productId = $request->input('product_id');

        \Log::info('Request untuk mendapatkan bahan produk', [
            'product_id' => $productId
        ]);

        if (!$productId) {
            \Log::warning('ID produk tidak valid untuk request bahan');
            return response()->json([
                'success' => false,
                'message' => 'ID produk tidak valid'
            ]);
        }

        // Gunakan find() untuk mengambil data produk secara langsung
        $product = Product::find($productId);

        if (!$product) {
            \Log::warning('Produk tidak ditemukan', [
                'product_id' => $productId
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Produk tidak ditemukan'
            ]);
        }

        if (!$product->is_processed) {
            \Log::warning('Produk bukan produk olahan', [
                'product_id' => $productId,
                'product_name' => $product->name
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Produk ini bukan produk olahan'
            ]);
        }

        try {
            // Ambil bahan-bahan secara manual dengan query langsung
            $ingredientsQuery = DB::table('product_ingredients')
                ->where('product_id', $productId)
                ->get();

            \Log::info('Bahan-bahan produk diambil', [
                'product_id' => $productId,
                'ingredients_count' => $ingredientsQuery->count()
            ]);

            $ingredients = [];

            foreach ($ingredientsQuery as $ingredientData) {
                // Ambil data produk bahan dan satuan
                $ingredient = Product::find($ingredientData->ingredient_id);
                $unit = Unit::find($ingredientData->unit_id);

                if (!$ingredient || !$unit) {
                    \Log::warning('Data bahan atau satuan tidak ditemukan', [
                        'ingredient_id' => $ingredientData->ingredient_id,
                        'unit_id' => $ingredientData->unit_id
                    ]);
                    continue;
                }

                $ingredients[] = [
                    'id' => $ingredient->id,
                    'name' => $ingredient->name,
                    'quantity' => $ingredientData->quantity,
                    'unit_id' => $ingredientData->unit_id,
                    'unit_name' => $unit->name
                ];
            }

            \Log::info('Mengembalikan data bahan', [
                'product_id' => $productId,
                'ingredients_count' => count($ingredients)
            ]);

            return response()->json([
                'success' => true,
                'ingredients' => $ingredients
            ]);
        } catch (\Exception $e) {
            \Log::error('Error saat mengambil bahan produk', [
                'product_id' => $productId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data bahan: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Display a listing of trashed products.
     */
    public function trashed()
    {
        $trashedProducts = Product::onlyTrashed()
            ->with(['category', 'baseUnit'])
            ->orderBy('name')
            ->get();

        return view('products.trashed', compact('trashedProducts'));
    }

    /**
     * Restore a trashed product.
     */
    public function restore($id)
    {
        try {
            $product = Product::onlyTrashed()->findOrFail($id);
            $product->restore();

            return redirect()->route('products.trashed')
                ->with('success', 'Produk berhasil dikembalikan.');
        } catch (\Exception $e) {
            return redirect()->route('products.trashed')
                ->with('error', 'Error mengembalikan produk: ' . $e->getMessage());
        }
    }

    /**
     * Permanently delete a product.
     */
    public function forceDelete($id)
    {
        try {
            $product = Product::onlyTrashed()->findOrFail($id);

            // Check for product references in shipment_details or other tables
            if (DB::table('shipment_details')->where('product_id', $id)->exists() ||
                PurchaseDetail::where('product_id', $id)->exists() ||
                SaleDetail::where('product_id', $id)->exists()) {
                return redirect()->route('products.trashed')
                    ->with('error', 'Tidak dapat menghapus produk secara permanen karena masih terkait dengan transaksi atau pengiriman.');
            }

            // Begin transaction
            DB::beginTransaction();

            // Hapus terlebih dahulu data di stock_adjustment_details
            DB::table('stock_adjustment_details')->where('product_id', $id)->delete();

            // Delete product image if exists
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }

            // Delete product units
            $product->productUnits()->delete();

            // Delete product ingredients
            DB::table('product_ingredients')->where('product_id', $id)->delete();

            // Delete product from ingredients
            DB::table('product_ingredients')->where('ingredient_id', $id)->delete();

            // Delete product stocks
            $product->stockWarehouses()->delete();
            $product->storeStocks()->delete();

            // Permanently delete the product
            $product->forceDelete();

            // Commit changes
            DB::commit();

            return redirect()->route('products.trashed')
                ->with('success', 'Produk berhasil dihapus permanen.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('products.trashed')
                ->with('error', 'Error menghapus permanen produk: ' . $e->getMessage());
        }
    }
}
