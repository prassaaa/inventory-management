<?php

namespace App\Http\Controllers;

use App\Models\InitialBalance;
use App\Models\Expense;
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
        // Ambil saldo awal terbaru jika ada
        $latestBalance = InitialBalance::latest('date')->first();

        return view('finance.balance.create', compact('latestBalance'));
    }

    /**
     * Menyimpan saldo awal baru
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'cash_balance' => 'required|numeric|min:0',
            'bank1_balance' => 'required|numeric|min:0',
            'bank2_balance' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        // Cek jika sudah ada saldo awal di tanggal yang sama
        $existingBalance = InitialBalance::where('date', $validated['date'])->first();

        if ($existingBalance) {
            return redirect()->back()->with('error', 'Saldo awal untuk tanggal yang dipilih sudah ada.');
        }

        $validated['created_by'] = Auth::id();

        try {
            // Simpan saldo awal baru
            $balance = InitialBalance::create($validated);

            // Log informasi untuk debugging
            Log::info('Saldo awal berhasil disimpan', [
                'id' => $balance->id,
                'date' => $balance->date,
                'cash_balance' => $balance->cash_balance,
                'bank1_balance' => $balance->bank1_balance,
                'bank2_balance' => $balance->bank2_balance
            ]);

            // Redirect ke laporan keuangan dengan parameter tanggal untuk memastikan data ditampilkan
            return redirect()->route('reports.finance', [
                'start_date' => $validated['date'],
                'end_date' => now()->format('Y-m-d')
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
        $categories = Expense::select('category')->distinct()->get()->pluck('category');

        return view('finance.expense.create', compact('categories'));
    }

    /**
     * Menyimpan pengeluaran baru
     */
    public function storeExpense(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'category' => 'required|string|max:255',
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
                'category' => $expense->category,
                'amount' => $expense->amount
            ]);

            // Redirect ke laporan keuangan dengan parameter tanggal
            return redirect()->route('reports.finance', [
                'start_date' => $validated['date'],
                'end_date' => now()->format('Y-m-d')
            ])->with('success', 'Pengeluaran berhasil disimpan.');

        } catch (\Exception $e) {
            Log::error('Gagal menyimpan pengeluaran', [
                'error' => $e->getMessage(),
                'data' => $validated
            ]);

            return redirect()->back()->with('error', 'Terjadi kesalahan saat menyimpan pengeluaran: ' . $e->getMessage());
        }
    }
}
