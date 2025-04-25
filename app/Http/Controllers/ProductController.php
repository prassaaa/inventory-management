<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Unit;
use App\Models\ProductUnit;
use App\Models\StockWarehouse;
use App\Models\PurchaseDetail;
use App\Models\SaleDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ProductsExport;
use App\Imports\ProductsImport;

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
        
        return view('products.create', compact('categories', 'baseUnits', 'units'));
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

        // Set is_active to boolean value
        $validated['is_active'] = isset($validated['is_active']) && $validated['is_active'] == 1;

        // Set store_source to 'pusat'
        $validated['store_source'] = 'pusat';

        // Create product
        $product = Product::create($validated);

        // Create initial warehouse stock
        StockWarehouse::create([
            'product_id' => $product->id,
            'unit_id' => $product->base_unit_id,
            'quantity' => 0
        ]);

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
        
        $product->load('productUnits');
        
        return view('products.edit', compact('product', 'categories', 'baseUnits', 'units'));
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

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $validated['image'] = $request->file('image')->store('products', 'public');
        }

        // Set is_active to boolean value
        $validated['is_active'] = isset($validated['is_active']) && $validated['is_active'] == 1;

        // Update product
        $product->update($validated);

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

        return redirect()->route('products.index')
            ->with('success', 'Product updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        // Check if product has any transactions
        if (PurchaseDetail::where('product_id', $product->id)->exists() ||
            SaleDetail::where('product_id', $product->id)->exists()) {
            return redirect()->route('products.index')
                ->with('error', 'Cannot delete product because it has associated transactions.');
        }

        // Delete product image if exists
        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }

        // Delete product units
        $product->productUnits()->delete();
        
        // Delete product stocks
        $product->stockWarehouses()->delete();
        $product->storeStocks()->delete();

        // Delete product
        $product->delete();

        return redirect()->route('products.index')
            ->with('success', 'Product deleted successfully.');
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
}