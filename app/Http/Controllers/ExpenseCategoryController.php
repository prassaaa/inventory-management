<?php

namespace App\Http\Controllers;

use App\Models\ExpenseCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExpenseCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = ExpenseCategory::orderBy('name')->get();
        return view('expenses.categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('expenses.categories.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:expense_categories,name',
            'description' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
        ]);

        // Set default values
        $validated['is_active'] = isset($validated['is_active']) ? 1 : 0;
        $validated['store_id'] = null; // Set store_id null untuk semua kategori

        ExpenseCategory::create($validated);

        return redirect()->route('expense-categories.index')
            ->with('success', 'Kategori pengeluaran berhasil ditambahkan.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ExpenseCategory $expenseCategory)
    {
        return view('expenses.categories.edit', compact('expenseCategory'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ExpenseCategory $expenseCategory)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:expense_categories,name,' . $expenseCategory->id,
            'description' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
        ]);

        // Set default values
        $validated['is_active'] = isset($validated['is_active']) ? 1 : 0;
        $validated['store_id'] = null; // Set store_id null untuk semua kategori

        $expenseCategory->update($validated);

        return redirect()->route('expense-categories.index')
            ->with('success', 'Kategori pengeluaran berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ExpenseCategory $expenseCategory)
    {
        // Check if category is being used
        $usageCount = DB::table('expenses')
            ->where('category_id', $expenseCategory->id)
            ->count();

        if ($usageCount > 0) {
            return redirect()->route('expense-categories.index')
                ->with('error', 'Kategori tidak dapat dihapus karena sedang digunakan dalam ' . $usageCount . ' transaksi pengeluaran.');
        }

        $expenseCategory->delete();

        return redirect()->route('expense-categories.index')
            ->with('success', 'Kategori pengeluaran berhasil dihapus.');
    }

    /**
     * Batch store of multiple categories
     */
    public function batchStore(Request $request)
    {
        $validated = $request->validate([
            'categories' => 'required|string',
        ]);

        $categoriesText = trim($validated['categories']);
        $categoryNames = explode("\n", $categoriesText);

        $count = 0;
        $now = now();

        foreach ($categoryNames as $name) {
            $name = trim($name);
            if (empty($name)) continue;

            // Skip existing categories
            if (ExpenseCategory::where('name', $name)->exists()) continue;

            ExpenseCategory::create([
                'name' => $name,
                'description' => null,
                'store_id' => null,
                'is_active' => 1,
                'created_at' => $now,
                'updated_at' => $now
            ]);

            $count++;
        }

        return redirect()->route('expense-categories.index')
            ->with('success', $count . ' kategori pengeluaran berhasil ditambahkan.');
    }
}
