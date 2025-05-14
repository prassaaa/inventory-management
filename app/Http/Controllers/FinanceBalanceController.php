<?php

namespace App\Http\Controllers;

use App\Models\InitialBalance;
use App\Models\BalanceCategory;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FinanceBalanceController extends Controller
{
    /**
     * Menampilkan form input saldo awal
     */
    public function create()
    {
        // Ambil semua kategori saldo yang aktif
        $categories = BalanceCategory::where('is_active', true)
            ->orderBy('name')
            ->get();

        // Ambil semua toko aktif
        $stores = Store::where('is_active', true)
            ->orderBy('name')
            ->get();

        // Cek user yang login apakah terkait dengan toko tertentu
        $user = Auth::user();
        $userStoreId = $user->store_id ?? null;

        // Ambil saldo awal terbaru per kategori jika ada
        $latestBalances = InitialBalance::with(['category', 'store'])
            ->select('category_id', DB::raw('MAX(date) as max_date'))
            ->groupBy('category_id')
            ->get()
            ->map(function ($item) {
                return InitialBalance::with(['category', 'store'])
                    ->where('category_id', $item->category_id)
                    ->where('date', $item->max_date)
                    ->first();
            });

        return view('finance.balance.create', compact('categories', 'stores', 'userStoreId', 'latestBalances'));
    }

    /**
     * Menyimpan saldo awal baru
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'category_id' => 'required|exists:balance_categories,id',
            'amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
            'store_id' => 'nullable|exists:stores,id',
        ]);

        // Cek jika sudah ada saldo awal dengan kategori yg sama di tanggal yang sama
        $query = InitialBalance::where('date', $validated['date'])
            ->where('category_id', $validated['category_id']);

        if (isset($validated['store_id'])) {
            $query->where('store_id', $validated['store_id']);
        } else {
            $query->whereNull('store_id');
        }

        $existingBalance = $query->first();

        if ($existingBalance) {
            return redirect()->back()->with('error', 'Saldo awal untuk kategori dan tanggal yang dipilih sudah ada.');
        }

        $validated['created_by'] = Auth::id();

        try {
            // Simpan saldo awal baru
            $balance = InitialBalance::create($validated);

            // Log informasi untuk debugging
            Log::info('Saldo awal berhasil disimpan', [
                'id' => $balance->id,
                'date' => $balance->date,
                'category_id' => $balance->category_id,
                'amount' => $balance->amount,
                'store_id' => $balance->store_id
            ]);

            // Redirect ke laporan keuangan dengan parameter tanggal untuk memastikan data ditampilkan
            return redirect()->route('reports.finance', [
                'start_date' => $validated['date'],
                'end_date' => now()->format('Y-m-d'),
                'store_id' => $validated['store_id'] ?? null
            ])->with('success', 'Saldo awal berhasil disimpan.');

        } catch (\Exception $e) {
            Log::error('Gagal menyimpan saldo awal', [
                'error' => $e->getMessage(),
                'data' => $validated
            ]);

            return redirect()->back()->with('error', 'Terjadi kesalahan saat menyimpan saldo awal: ' . $e->getMessage());
        }
    }

    /**
     * Menampilkan form input pengeluaran
     */
    public function createExpense()
    {
        // Ambil kategori pengeluaran
        $categories = ExpenseCategory::where('is_active', true)
            ->orderBy('name')
            ->get();

        // Ambil semua toko aktif
        $stores = Store::where('is_active', true)
            ->orderBy('name')
            ->get();

        // Cek user yang login apakah terkait dengan toko tertentu
        $user = Auth::user();
        $userStoreId = $user->store_id ?? null;

        return view('finance.expense.create', compact('categories', 'stores', 'userStoreId'));
    }

    /**
     * Menyimpan pengeluaran baru
     */
    public function storeExpense(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'category_id' => 'required|exists:expense_categories,id',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string',
            'store_id' => 'nullable|exists:stores,id',
        ]);

        $validated['created_by'] = Auth::id();

        try {
            // Simpan pengeluaran baru
            $expense = Expense::create($validated);

            // Log informasi untuk debugging
            Log::info('Pengeluaran berhasil disimpan', [
                'id' => $expense->id,
                'date' => $expense->date,
                'category_id' => $expense->category_id,
                'amount' => $expense->amount,
                'store_id' => $expense->store_id
            ]);

            // Redirect ke laporan keuangan dengan parameter tanggal
            return redirect()->route('reports.finance', [
                'start_date' => $validated['date'],
                'end_date' => now()->format('Y-m-d'),
                'store_id' => $validated['store_id'] ?? null
            ])->with('success', 'Pengeluaran berhasil disimpan.');

        } catch (\Exception $e) {
            Log::error('Gagal menyimpan pengeluaran', [
                'error' => $e->getMessage(),
                'data' => $validated
            ]);

            return redirect()->back()->with('error', 'Terjadi kesalahan saat menyimpan pengeluaran: ' . $e->getMessage());
        }
    }

    /**
     * Mengelola kategori saldo
     */
    public function manageBalanceCategories()
    {
        $categories = BalanceCategory::orderBy('name')->get();
        $stores = Store::where('is_active', true)->orderBy('name')->get();

        return view('finance.categories.balance', compact('categories', 'stores'));
    }

    /**
     * Mengelola kategori pengeluaran
     */
    public function manageExpenseCategories()
    {
        $categories = ExpenseCategory::orderBy('name')->get();
        $stores = Store::where('is_active', true)->orderBy('name')->get();

        return view('finance.categories.expense', compact('categories', 'stores'));
    }

    /**
     * Menyimpan kategori saldo baru
     */
    public function storeBalanceCategory(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:asset,liability,equity',
            'description' => 'nullable|string',
            'store_id' => 'nullable|exists:stores,id',
        ]);

        $validated['is_active'] = true;

        BalanceCategory::create($validated);

        return redirect()->route('finance.categories.balance')
            ->with('success', 'Kategori saldo berhasil ditambahkan');
    }

    /**
     * Menyimpan kategori pengeluaran baru
     */
    public function storeExpenseCategory(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'store_id' => 'nullable|exists:stores,id',
        ]);

        $validated['is_active'] = true;

        ExpenseCategory::create($validated);

        return redirect()->route('finance.categories.expense')
            ->with('success', 'Kategori pengeluaran berhasil ditambahkan');
    }
}
