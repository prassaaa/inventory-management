<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\Purchase;
use App\Models\PurchaseDetail;
use App\Models\Product;
use App\Models\Category;
use App\Models\Store;
use App\Models\Supplier;
use App\Models\StockWarehouse;
use App\Models\StockStore;
use App\Models\Expense;
use App\Models\BalanceCategory;
use App\Models\ExpenseCategory;
use App\Exports\SalesReportExport;
use App\Exports\PurchasesReportExport;
use App\Exports\InventoryReportExport;
use App\Exports\FinanceReportExport;
use App\Exports\ProfitLossReportExport;
use App\Models\AccountPayable;
use App\Models\AccountReceivable;
use App\Models\InitialBalance;
use App\Exports\SalesByStoreExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Log;

class ReportController extends Controller
{
    /**
     * Get user store context
     */
    private function getUserStoreContext()
    {
        $user = Auth::user();
        $userStoreId = $user->store_id ?? null;
        $canSelectStore = is_null($userStoreId); // Jika null berarti pusat, bisa pilih semua store

        return compact('userStoreId', 'canSelectStore');
    }

    /**
     * Apply store filter to request
     */
    private function applyStoreFilter(Request $request, $userStoreId)
    {
        if ($userStoreId) {
            $request->merge(['store_id' => $userStoreId]);
        }
        return $request->input('store_id');
    }

    /**
     * Display sales report.
     */
    public function sales(Request $request)
    {
        $context = $this->getUserStoreContext();
        extract($context); // $userStoreId, $canSelectStore

        $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));
        $storeId = $this->applyStoreFilter($request, $userStoreId);
        $paymentType = $request->input('payment_type');

        $query = Sale::with(['store', 'creator'])
            ->whereBetween('date', [$startDate, $endDate]);

        if ($storeId) {
            $query->where('store_id', $storeId);
        }

        if ($paymentType) {
            $query->where('payment_type', $paymentType);
        }

        $sales = $query->orderBy('date', 'desc')->get();

        // Calculate summary
        $total_sales = $sales->sum('total_amount');
        $total_transactions = $sales->count();

        // Get payment methods breakdown
        $payment_methods = $sales->groupBy('payment_type')
            ->map(function ($items) {
                return $items->count();
            })
            ->toArray();

        // Get chart data
        $chart_data = $this->getSalesChartData($startDate, $endDate, $storeId, $paymentType);

        // Get top products
        $top_products = $this->getTopSellingProducts($startDate, $endDate, $storeId);

        // Get all stores for filter (hanya jika user tidak terkait toko tertentu)
        $stores = $canSelectStore ? Store::where('is_active', true)->orderBy('name')->get() : collect();

        return view('reports.sales', compact(
            'sales',
            'total_sales',
            'total_transactions',
            'payment_methods',
            'chart_data',
            'top_products',
            'stores',
            'canSelectStore',
            'userStoreId'
        ));
    }

    /**
     * Export sales report to Excel.
     */
    public function exportSales(Request $request)
    {
        $context = $this->getUserStoreContext();
        $storeId = $this->applyStoreFilter($request, $context['userStoreId']);

        $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));
        $paymentType = $request->input('payment_type');

        return Excel::download(new SalesReportExport($startDate, $endDate, $storeId, $paymentType), 'sales_report.xlsx');
    }

    /**
     * Display purchases report.
     */
    public function purchases(Request $request)
    {
        // Purchases biasanya terpusat, tapi tetap terapkan logika jika diperlukan
        $context = $this->getUserStoreContext();
        extract($context);

        $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));
        $supplierId = $request->input('supplier_id');
        $status = $request->input('status');

        // Optional: Apply store filter to purchases if needed
        $storeId = $this->applyStoreFilter($request, $userStoreId);

        $query = Purchase::with(['supplier', 'creator'])
            ->whereBetween('date', [$startDate, $endDate]);

        if ($supplierId) {
            $query->where('supplier_id', $supplierId);
        }

        if ($status) {
            $query->where('status', $status);
        }

        // Apply store filter if applicable (uncomment if purchases are store-specific)
        // if ($storeId) {
        //     $query->where('store_id', $storeId);
        // }

        $purchases = $query->orderBy('date', 'desc')->get();

        // Calculate summary
        $total_purchases = $purchases->sum('total_amount');
        $total_transactions = $purchases->count();

        // Get status breakdown
        $status_data = $purchases->groupBy('status')
            ->map(function ($items) {
                return $items->count();
            })
            ->toArray();

        // Get chart data
        $chart_data = $this->getPurchasesChartData($startDate, $endDate, $supplierId, $status);

        // Get top products
        $top_products = $this->getTopPurchasedProducts($startDate, $endDate, $supplierId);

        // Get all suppliers for filter
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();

        // Get stores (empty for branch users if purchases are centralized)
        $stores = $canSelectStore ? Store::where('is_active', true)->orderBy('name')->get() : collect();
        $showStoreColumn = false; // Set to true if purchases have store_id column

        return view('reports.purchases', compact(
            'purchases',
            'total_purchases',
            'total_transactions',
            'status_data',
            'chart_data',
            'top_products',
            'suppliers',
            'stores',
            'canSelectStore',
            'userStoreId',
            'showStoreColumn'
        ));
    }

    /**
     * Export purchases report to Excel.
     */
    public function exportPurchases(Request $request)
    {
        $context = $this->getUserStoreContext();
        $storeId = $this->applyStoreFilter($request, $context['userStoreId']);

        $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));
        $supplierId = $request->input('supplier_id');
        $status = $request->input('status');

        return Excel::download(new PurchasesReportExport($startDate, $endDate, $supplierId, $status), 'purchases_report.xlsx');
    }

    /**
     * Display inventory report.
     */
    public function inventory(Request $request)
    {
        $context = $this->getUserStoreContext();
        extract($context);

        $storeId = $this->applyStoreFilter($request, $userStoreId);
        $categoryId = $request->input('category_id');
        $stockStatus = $request->input('stock_status');

        $query = Product::with(['category', 'baseUnit']);

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        if ($storeId) {
            // Get products with store stock
            $query->select('products.*', DB::raw('COALESCE(stock_stores.quantity, 0) as stock_quantity'))
                    ->leftJoin('stock_stores', function($join) use ($storeId) {
                        $join->on('products.id', '=', 'stock_stores.product_id')
                            ->where('stock_stores.unit_id', '=', DB::raw('products.base_unit_id'))
                            ->where('stock_stores.store_id', '=', $storeId);
                    });
        } else {
            // Get products with warehouse stock (for central/pusat)
            $query->select('products.*', DB::raw('COALESCE(stock_warehouses.quantity, 0) as stock_quantity'))
                    ->leftJoin('stock_warehouses', function($join) {
                        $join->on('products.id', '=', 'stock_warehouses.product_id')
                            ->where('stock_warehouses.unit_id', '=', DB::raw('products.base_unit_id'));
                    });
        }

        if ($stockStatus) {
            switch ($stockStatus) {
                case 'in_stock':
                    $query->having(DB::raw('COALESCE(stock_stores.quantity, COALESCE(stock_warehouses.quantity, 0))'), '>', 0);
                    break;
                case 'low_stock':
                    $query->havingRaw('COALESCE(stock_stores.quantity, COALESCE(stock_warehouses.quantity, 0)) < products.min_stock')
                          ->havingRaw('COALESCE(stock_stores.quantity, COALESCE(stock_warehouses.quantity, 0)) > 0');
                    break;
                case 'out_of_stock':
                    $query->having(DB::raw('COALESCE(stock_stores.quantity, COALESCE(stock_warehouses.quantity, 0))'), '<=', 0);
                    break;
            }
        }

        $products = $query->orderBy('name')->get();

        // Calculate summary
        $total_products = $products->count();
        $total_stock_value = $products->sum(function($product) {
            return $product->stock_quantity * $product->purchase_price;
        });
        $low_stock_count = $products->filter(function($product) {
            return $product->stock_quantity > 0 && $product->stock_quantity < $product->min_stock;
        })->count();
        $out_of_stock_count = $products->filter(function($product) {
            return $product->stock_quantity <= 0;
        })->count();

        // Get stock value by category
        $category_data = $products->groupBy('category_id')
            ->map(function($items) {
                $category = $items->first()->category;
                return [
                    'name' => $category ? $category->name : 'Tidak Terkategori',
                    'value' => $items->sum(function($product) {
                        return $product->stock_quantity * $product->purchase_price;
                    })
                ];
            })
            ->values()
            ->toArray();

        // Get all stores and categories for filter
        $stores = $canSelectStore ? Store::where('is_active', true)->orderBy('name')->get() : collect();
        $categories = Category::orderBy('name')->get();

        return view('reports.inventory', compact(
            'products',
            'total_products',
            'total_stock_value',
            'low_stock_count',
            'out_of_stock_count',
            'category_data',
            'stores',
            'categories',
            'canSelectStore',
            'userStoreId'
        ));
    }

    /**
     * Export inventory report to Excel.
     */
    public function exportInventory(Request $request)
    {
        $context = $this->getUserStoreContext();
        $storeId = $this->applyStoreFilter($request, $context['userStoreId']);

        $categoryId = $request->input('category_id');
        $stockStatus = $request->input('stock_status');

        return Excel::download(new InventoryReportExport($storeId, $categoryId, $stockStatus), 'inventory_report.xlsx');
    }

    /**
     * Display finance report.
     */
    public function finance(Request $request)
    {
        $context = $this->getUserStoreContext();
        extract($context);

        $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));
        $storeId = $this->applyStoreFilter($request, $userStoreId);

        // Debug untuk memeriksa parameter tanggal
        Log::info('Finance report params', [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'store_id' => $storeId,
            'user_store_id' => $userStoreId,
            'can_select_store' => $canSelectStore
        ]);

        // Mengambil semua saldo awal yang tanggalnya <= tanggal akhir laporan
        $query = InitialBalance::with('category')
            ->where('date', '<=', $endDate);

        if ($storeId) {
            $query->where(function($q) use ($storeId) {
                $q->where('store_id', $storeId)
                ->orWhereNull('store_id'); // Juga ambil saldo global
            });
        } elseif (!$canSelectStore) {
            // User cabang tapi tidak ada store_id di filter, ambil global saja
            $query->whereNull('store_id');
        }

        $initialBalances = $query->orderBy('date', 'desc')->get();

        // Kelompokkan saldo berdasarkan kategori, ambil tanggal terbaru untuk setiap kategori
        $balances = collect();
        $initialBalances->groupBy('category_id')->each(function($items, $categoryId) use ($balances) {
            // Ambil item dengan tanggal terbaru untuk kategori ini
            $latestItem = $items->sortByDesc('date')->first();
            $balances->push($latestItem);
        });

        // Get sales
        $salesQuery = Sale::whereBetween('date', [$startDate, $endDate]);
        if ($storeId) {
            $salesQuery->where('store_id', $storeId);
        }
        $sales = $salesQuery->sum('total_amount');

        // Get purchases (biasanya tidak difilter per store)
        $purchasesQuery = Purchase::whereBetween('date', [$startDate, $endDate])
            ->where('status', '!=', 'pending');
        $purchases = $purchasesQuery->sum('total_amount');

        // Get expenses
        $expensesQuery = Expense::with('category')
            ->whereBetween('date', [$startDate, $endDate]);
        if ($storeId) {
            $expensesQuery->where('store_id', $storeId);
        }
        $expenseItems = $expensesQuery->get();
        $expenses = $expenseItems->sum('amount');

        // Calculate gross profit
        $grossProfit = $sales - $purchases;

        // Calculate net profit
        $netProfit = $grossProfit - $expenses;

        // Get income and expense chart data
        $chart_data = $this->getFinanceChartData($startDate, $endDate, $storeId);

        // Get expense breakdown
        $expense_categories = $expenseItems
            ->groupBy(function($expense) {
                return $expense->category ? $expense->category->name : 'Lainnya';
            })
            ->map(function($items, $categoryName) {
                return [
                    'category' => $categoryName,
                    'total' => $items->sum('amount')
                ];
            })
            ->sortByDesc('total')
            ->values();

        // Get total payables & receivables for summary cards (hanya untuk pusat)
        $totalPayables = 0;
        $overduePayables = 0;
        $totalReceivables = 0;
        $overdueReceivables = 0;

        if ($canSelectStore) {
            $totalPayables = AccountPayable::where('status', '!=', 'paid')->sum('amount');
            $overduePayables = AccountPayable::where('status', '!=', 'paid')
                            ->where('due_date', '<', now())
                            ->sum(DB::raw('amount - paid_amount'));

            $totalReceivables = AccountReceivable::where('status', '!=', 'paid')->sum('amount');
            $overdueReceivables = AccountReceivable::where('status', '!=', 'paid')
                            ->where('due_date', '<', now())
                            ->sum(DB::raw('amount - paid_amount'));
        }

        // Get all stores for filter
        $stores = $canSelectStore ? Store::where('is_active', true)->orderBy('name')->get() : collect();

        // Ambil semua kategori saldo
        $balanceCategories = BalanceCategory::where('is_active', true)->get();

        return view('reports.finance', compact(
            'sales',
            'purchases',
            'expenses',
            'grossProfit',
            'netProfit',
            'chart_data',
            'expense_categories',
            'stores',
            'totalPayables',
            'overduePayables',
            'totalReceivables',
            'overdueReceivables',
            'balances',
            'balanceCategories',
            'startDate',
            'endDate',
            'canSelectStore',
            'userStoreId'
        ));
    }

    /**
     * Export finance report to Excel.
     */
    public function exportFinance(Request $request)
    {
        $context = $this->getUserStoreContext();
        $storeId = $this->applyStoreFilter($request, $context['userStoreId']);

        $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));

        return Excel::download(new FinanceReportExport($startDate, $endDate, $storeId), 'finance_report.xlsx');
    }

    /**
     * Display profit and loss report.
     */
    public function profitLoss(Request $request)
    {
        $context = $this->getUserStoreContext();
        extract($context);

        $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));
        $storeId = $this->applyStoreFilter($request, $userStoreId);

        // Income section
        // Get total sales
        $salesQuery = Sale::whereBetween('date', [$startDate, $endDate]);
        if ($storeId) {
            $salesQuery->where('store_id', $storeId);
        }
        $totalSales = $salesQuery->sum('total_amount');

        // Expense section
        // Get cost of goods sold (from sale details)
        $cogs = SaleDetail::join('products', 'sale_details.product_id', '=', 'products.id')
        ->whereHas('sale', function($q) use ($startDate, $endDate, $storeId) {
            $q->whereBetween('date', [$startDate, $endDate]);
            if ($storeId) {
                $q->where('store_id', $storeId);
            }
        })
        ->sum(DB::raw('sale_details.quantity * products.purchase_price'));

        // Get expenses
        $expenseBreakdown = Expense::select('expense_categories.name as category', DB::raw('SUM(expenses.amount) as total'))
            ->join('expense_categories', 'expenses.category_id', '=', 'expense_categories.id')
            ->whereBetween('expenses.date', [$startDate, $endDate]);

        if ($storeId) {
            $expenseBreakdown->where('expenses.store_id', $storeId);
        }

        $expenseBreakdown = $expenseBreakdown->groupBy('expense_categories.name')
            ->orderByDesc('total')
            ->get();

        // Total expenses
        $expenses = $expenseBreakdown->sum('total');

        // Calculate gross profit and net profit
        $grossProfit = $totalSales - $cogs;
        $netProfit = $grossProfit - $expenses;

        // Get all stores for filter (hanya jika user tidak terkait toko tertentu)
        $stores = $canSelectStore ? Store::where('is_active', true)->orderBy('name')->get() : collect();

        // Tambahkan store name jika filter by store
        $selectedStore = null;
        if ($storeId) {
            $selectedStore = Store::find($storeId);
        }

        return view('reports.profit-loss', compact(
            'totalSales',
            'cogs',
            'grossProfit',
            'expenses',
            'netProfit',
            'expenseBreakdown',
            'stores',
            'canSelectStore',
            'userStoreId',
            'selectedStore'
        ));
    }

    /**
     * Export profit and loss report to Excel.
     */
    public function exportProfitLoss(Request $request)
    {
        $context = $this->getUserStoreContext();
        $storeId = $this->applyStoreFilter($request, $context['userStoreId']);

        $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));

        return Excel::download(new ProfitLossReportExport($startDate, $endDate, $storeId), 'profit_loss_report.xlsx');
    }

    /**
     * Display payables (debt to suppliers) report
     * Hanya untuk user pusat
     */
    public function payables(Request $request)
    {
        $context = $this->getUserStoreContext();

        // Redirect branch users karena payables adalah data global
        if (!$context['canSelectStore']) {
            return redirect()->route('reports.finance')->with('error', 'Laporan hutang hanya tersedia untuk user pusat.');
        }

        $query = AccountPayable::with(['purchase', 'supplier']);

        // Filter by supplier
        if ($request->has('supplier_id') && $request->supplier_id) {
            $query->where('supplier_id', $request->supplier_id);
        }

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->has('start_date') && $request->start_date) {
            $query->where('due_date', '>=', $request->start_date);
        }

        if ($request->has('end_date') && $request->end_date) {
            $query->where('due_date', '<=', $request->end_date);
        }

        // Default sorting by due date
        $query->orderBy('due_date', 'asc');

        $payables = $query->get();

        // Summary calculations
        $totalPayables = $payables->sum('amount');
        $totalPaid = $payables->sum('paid_amount');
        $totalRemaining = $totalPayables - $totalPaid;
        $overdueTotalAmount = $payables->filter(function($payable) {
            return $payable->is_overdue;
        })->sum('remaining_amount');

        $suppliers = Supplier::orderBy('name')->get();

        return view('reports.payables', compact(
            'payables',
            'suppliers',
            'totalPayables',
            'totalPaid',
            'totalRemaining',
            'overdueTotalAmount'
        ));
    }

    /**
     * Display receivables (credit from stores) report
     * Hanya untuk user pusat
     */
    public function receivables(Request $request)
    {
        $context = $this->getUserStoreContext();

        // Redirect branch users karena receivables adalah data global
        if (!$context['canSelectStore']) {
            return redirect()->route('reports.finance')->with('error', 'Laporan piutang hanya tersedia untuk user pusat.');
        }

        $query = AccountReceivable::with(['storeOrder', 'store']);

        // Filter by store
        if ($request->has('store_id') && $request->store_id) {
            $query->where('store_id', $request->store_id);
        }

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->has('start_date') && $request->start_date) {
            $query->where('due_date', '>=', $request->start_date);
        }

        if ($request->has('end_date') && $request->end_date) {
            $query->where('due_date', '<=', $request->end_date);
        }

        // Default sorting by due date
        $query->orderBy('due_date', 'asc');

        $receivables = $query->get();

        // Summary calculations
        $totalReceivables = $receivables->sum('amount');
        $totalPaid = $receivables->sum('paid_amount');
        $totalRemaining = $totalReceivables - $totalPaid;
        $overdueTotalAmount = $receivables->filter(function($receivable) {
            return $receivable->is_overdue;
        })->sum('remaining_amount');

        $stores = Store::orderBy('name')->get();

        return view('reports.receivables', compact(
            'receivables',
            'stores',
            'totalReceivables',
            'totalPaid',
            'totalRemaining',
            'overdueTotalAmount'
        ));
    }

    /**
     * Display balance sheet report (neraca)
     * Biasanya untuk user pusat, tapi bisa disesuaikan
     */
    public function balanceSheet(Request $request)
    {
        $context = $this->getUserStoreContext();
        $date = $request->input('date', now()->format('Y-m-d'));

        // Log untuk debugging
        Log::info('Generating Balance Sheet', [
            'date' => $date,
            'user_store_id' => $context['userStoreId'],
            'can_select_store' => $context['canSelectStore']
        ]);

        // --- AKTIVA (ASSETS) ---

        // 1. Kas & Setara Kas
        // a. Ambil semua kategori saldo jenis asset
        $assetCategories = BalanceCategory::where('type', 'asset')->get();
        Log::info('Asset Categories', ['categories' => $assetCategories->pluck('name')]);

        // b. Inisialisasi nilai default
        $cash = 0;
        $bank1 = 0; // Bank umum atau Bank BCA
        $bank2 = 0; // Bank Mandiri

        // c. Ambil saldo awal untuk setiap kategori
        foreach ($assetCategories as $category) {
            $balanceQuery = InitialBalance::where('category_id', $category->id)
                ->where('date', '<=', $date)
                ->orderBy('date', 'desc');

            // Filter berdasarkan store jika user cabang
            if ($context['userStoreId']) {
                $balanceQuery->where(function($q) use ($context) {
                    $q->where('store_id', $context['userStoreId'])
                      ->orWhereNull('store_id');
                });
            }

            $balance = $balanceQuery->first();

            if ($balance) {
                Log::info('Found balance for category', [
                    'category' => $category->name,
                    'amount' => $balance->amount,
                    'date' => $balance->date
                ]);

                // Tentukan kategori berdasarkan nama
                switch (strtolower($category->name)) {
                    case 'kas':
                        $cash = $balance->amount;
                        break;
                    case 'bank':
                        $bank1 = $balance->amount;
                        break;
                    case 'bank bca':
                        $bank1 = $balance->amount; // BCA masuk ke bank1
                        break;
                    case 'bank mandiri':
                        $bank2 = $balance->amount; // Mandiri masuk ke bank2
                        break;
                }
            }
        }

        // d. Estimasi kas jika semua saldo nol
        $estimatedCash = false;
        if ($cash == 0 && $bank1 == 0 && $bank2 == 0) {
            $estimatedCash = true;

            // Ambil total penjualan tunai
            $cashSalesQuery = Sale::where('payment_type', 'tunai')
                ->where('date', '<=', $date);

            if ($context['userStoreId']) {
                $cashSalesQuery->where('store_id', $context['userStoreId']);
            }

            $cashSales = $cashSalesQuery->sum('total_amount');

            // Ambil total pembelian tunai (biasanya tidak per store)
            $cashPurchases = Purchase::where('payment_type', 'tunai')
                        ->where('date', '<=', $date)
                        ->sum('total_amount');

            // Ambil total pengeluaran tunai
            $expenseQuery = Expense::where('date', '<=', $date);

            if ($context['userStoreId']) {
                $expenseQuery->where('store_id', $context['userStoreId']);
            }

            $cashExpenses = $expenseQuery->sum('amount');

            // Estimasi saldo kas
            $cash = $cashSales - $cashPurchases - $cashExpenses;
            if ($cash < 0) $cash = 0; // Pastikan kas tidak negatif

            Log::info('Estimated Cash Balance', [
                'cashSales' => $cashSales,
                'cashPurchases' => $cashPurchases,
                'cashExpenses' => $cashExpenses,
                'estimatedCash' => $cash
            ]);
        }

        // Total kas dan bank
        $totalCashAndBank = $cash + $bank1 + $bank2;

        Log::info('Total Cash & Bank', [
            'cash' => $cash,
            'bank1' => $bank1,
            'bank2' => $bank2,
            'total' => $totalCashAndBank,
            'estimated' => $estimatedCash
        ]);

        // 2. Piutang Dagang
        $receivablesQuery = AccountReceivable::where('status', '!=', 'paid')
                        ->where('due_date', '<=', $date);

        // Filter berdasarkan store jika user cabang (uncomment jika receivables per store)
        // if ($context['userStoreId']) {
        //     $receivablesQuery->where('store_id', $context['userStoreId']);
        // }

        $accountsReceivable = $receivablesQuery->sum(DB::raw('amount - paid_amount'));

        Log::info('Accounts Receivable', [
            'total' => $accountsReceivable
        ]);

        // 3. Persediaan Barang
        if ($context['userStoreId']) {
            // Persediaan di toko specific
            $inventory = DB::table('stock_stores')
                        ->join('products', 'stock_stores.product_id', '=', 'products.id')
                        ->where('stock_stores.store_id', $context['userStoreId'])
                        ->whereNull('products.deleted_at')
                        ->sum(DB::raw('COALESCE(stock_stores.quantity, 0) * COALESCE(products.purchase_price, 0)'));
        } else {
            // a. Persediaan di gudang
            $warehouseInventory = DB::table('stock_warehouses')
                                ->join('products', 'stock_warehouses.product_id', '=', 'products.id')
                                ->whereNull('products.deleted_at')
                                ->sum(DB::raw('COALESCE(stock_warehouses.quantity, 0) * COALESCE(products.purchase_price, 0)'));

            // b. Persediaan di toko
            $storeInventory = DB::table('stock_stores')
                            ->join('products', 'stock_stores.product_id', '=', 'products.id')
                            ->whereNull('products.deleted_at')
                            ->sum(DB::raw('COALESCE(stock_stores.quantity, 0) * COALESCE(products.purchase_price, 0)'));

            $inventory = $warehouseInventory + $storeInventory;
        }

        Log::info('Inventory', [
            'total' => $inventory
        ]);

        // Total Aktiva Lancar
        $totalCurrentAssets = $totalCashAndBank + $accountsReceivable + $inventory;

        // 4. Aktiva Tetap (Fixed Assets)
        $fixedAssets = 0;
        $accumulatedDepreciation = 0;

        // Cari saldo aktiva tetap jika ada
        $fixedAssetCategory = BalanceCategory::where('type', 'asset')
            ->where(function($query) {
                $query->where('name', 'like', '%aset tetap%')
                    ->orWhere('name', 'like', '%fixed asset%')
                    ->orWhere('name', 'like', '%aktiva tetap%');
            })
            ->first();

        if ($fixedAssetCategory) {
            $fixedAssetQuery = InitialBalance::where('category_id', $fixedAssetCategory->id)
                ->where('date', '<=', $date)
                ->orderBy('date', 'desc');

            if ($context['userStoreId']) {
                $fixedAssetQuery->where(function($q) use ($context) {
                    $q->where('store_id', $context['userStoreId'])
                      ->orWhereNull('store_id');
                });
            }

            $fixedAssetBalance = $fixedAssetQuery->first();

            if ($fixedAssetBalance) {
                $fixedAssets = $fixedAssetBalance->amount;
            }
        }

        // Cari akumulasi penyusutan jika ada
        $depreciationCategory = BalanceCategory::where('type', 'asset')
            ->where(function($query) {
                $query->where('name', 'like', '%penyusutan%')
                    ->orWhere('name', 'like', '%depreciation%')
                    ->orWhere('name', 'like', '%akumulasi%');
            })
            ->first();

        if ($depreciationCategory) {
            $depreciationQuery = InitialBalance::where('category_id', $depreciationCategory->id)
                ->where('date', '<=', $date)
                ->orderBy('date', 'desc');

            if ($context['userStoreId']) {
                $depreciationQuery->where(function($q) use ($context) {
                    $q->where('store_id', $context['userStoreId'])
                      ->orWhereNull('store_id');
                });
            }

            $depreciationBalance = $depreciationQuery->first();

            if ($depreciationBalance) {
                $accumulatedDepreciation = $depreciationBalance->amount;
            }
        }

        $netFixedAssets = $fixedAssets - $accumulatedDepreciation;

        Log::info('Fixed Assets', [
            'assets' => $fixedAssets,
            'depreciation' => $accumulatedDepreciation,
            'net' => $netFixedAssets
        ]);

        // Total Aktiva
        $totalAssets = $totalCurrentAssets + $netFixedAssets;

        // --- PASIVA (LIABILITIES & EQUITY) ---

        // 1. Hutang Dagang (biasanya tidak per store)
        $accountsPayable = AccountPayable::where('status', '!=', 'paid')
                        ->where('due_date', '<=', $date)
                        ->sum(DB::raw('amount - paid_amount'));

        Log::info('Accounts Payable', [
            'total' => $accountsPayable
        ]);

        // 2. Hutang Pajak dan Kewajiban Lainnya
        $taxPayable = 0;
        $longTermLiabilities = 0;

        // Ambil kategori kewajiban (liability)
        $liabilityCategories = BalanceCategory::where('type', 'liability')->get();
        foreach ($liabilityCategories as $category) {
            $balanceQuery = InitialBalance::where('category_id', $category->id)
                ->where('date', '<=', $date)
                ->orderBy('date', 'desc');

            if ($context['userStoreId']) {
                $balanceQuery->where(function($q) use ($context) {
                    $q->where('store_id', $context['userStoreId'])
                      ->orWhereNull('store_id');
                });
            }

            $balance = $balanceQuery->first();

            if ($balance) {
                $categoryName = strtolower($category->name);
                if (str_contains($categoryName, 'pajak') || str_contains($categoryName, 'tax')) {
                    $taxPayable += $balance->amount;
                } elseif (str_contains($categoryName, 'jangka panjang') ||
                        str_contains($categoryName, 'long term') ||
                        str_contains($categoryName, 'kredit')) {
                    $longTermLiabilities += $balance->amount;
                }
            }
        }

        // Total Kewajiban Lancar
        $totalCurrentLiabilities = $accountsPayable + $taxPayable;

        // Total Kewajiban
        $totalLiabilities = $totalCurrentLiabilities + $longTermLiabilities;

        // 3. Ekuitas
        $initialCapital = 0;

        // Ambil kategori ekuitas (equity)
        $equityCategories = BalanceCategory::where('type', 'equity')->get();
        foreach ($equityCategories as $category) {
            $balanceQuery = InitialBalance::where('category_id', $category->id)
                ->where('date', '<=', $date)
                ->orderBy('date', 'desc');

            if ($context['userStoreId']) {
                $balanceQuery->where(function($q) use ($context) {
                    $q->where('store_id', $context['userStoreId'])
                      ->orWhereNull('store_id');
                });
            }

            $balance = $balanceQuery->first();

            if ($balance) {
                $initialCapital += $balance->amount;
            }
        }

        // Jika tidak ada modal, estimasi dari saldo awal
        if ($initialCapital == 0) {
            // Asumsi modal awal = total aset - total kewajiban berdasarkan saldo awal
            $initialCapital = max(0, $totalAssets - $totalLiabilities);
        }

        Log::info('Equity', [
            'initialCapital' => $initialCapital
        ]);

        // 4. Laba tahun berjalan
        $startOfYear = Carbon::parse($date)->startOfYear()->format('Y-m-d');

        // a. Pendapatan: penjualan
        $revenueQuery = Sale::whereBetween('date', [$startOfYear, $date]);
        if ($context['userStoreId']) {
            $revenueQuery->where('store_id', $context['userStoreId']);
        }
        $revenue = $revenueQuery->sum('total_amount');

        // b. Harga Pokok Penjualan
        $cogsQuery = DB::table('sale_details')
                    ->join('sales', 'sale_details.sale_id', '=', 'sales.id')
                    ->join('products', 'sale_details.product_id', '=', 'products.id')
                    ->whereBetween('sales.date', [$startOfYear, $date]);

        if ($context['userStoreId']) {
            $cogsQuery->where('sales.store_id', $context['userStoreId']);
        }

        $costOfGoodsSold = $cogsQuery->sum(DB::raw('COALESCE(sale_details.quantity, 0) * COALESCE(products.purchase_price, 0)'));

        // c. Beban Operasional: pengeluaran
        $expenseQuery = Expense::whereBetween('date', [$startOfYear, $date]);
        if ($context['userStoreId']) {
            $expenseQuery->where('store_id', $context['userStoreId']);
        }
        $operatingExpenses = $expenseQuery->sum('amount');

        // d. Laba Bersih
        $netIncome = $revenue - $costOfGoodsSold - $operatingExpenses;

        Log::info('Net Income Calculation', [
            'startOfYear' => $startOfYear,
            'revenue' => $revenue,
            'cogs' => $costOfGoodsSold,
            'expenses' => $operatingExpenses,
            'netIncome' => $netIncome
        ]);

        // Total Ekuitas
        $totalEquity = $initialCapital + $netIncome;

        // Total Pasiva
        $totalLiabilitiesAndEquity = $totalLiabilities + $totalEquity;

        // Perbedaan (seharusnya 0 jika balance)
        $difference = $totalAssets - $totalLiabilitiesAndEquity;

        Log::info('Balance Sheet Summary', [
            'totalAssets' => $totalAssets,
            'totalLiabilitiesAndEquity' => $totalLiabilitiesAndEquity,
            'difference' => $difference
        ]);

        // Tambahkan variabel untuk view
        $estimatedBalance = $estimatedCash || ($initialCapital == 0);

        return view('reports.balance-sheet', compact(
            'date',
            'cash',
            'bank1',
            'bank2',
            'totalCashAndBank',
            'accountsReceivable',
            'inventory',
            'totalCurrentAssets',
            'fixedAssets',
            'accumulatedDepreciation',
            'netFixedAssets',
            'totalAssets',
            'accountsPayable',
            'taxPayable',
            'totalCurrentLiabilities',
            'longTermLiabilities',
            'totalLiabilities',
            'initialCapital',
            'netIncome',
            'totalEquity',
            'totalLiabilitiesAndEquity',
            'difference',
            'estimatedBalance'
        ));
    }

    /**
     * Display sales report by store/outlet (sorted by omzet - highest to lowest).
     * Hanya untuk user pusat
     */
    public function salesByStore(Request $request)
    {
        $context = $this->getUserStoreContext();

        // Redirect branch users karena ini adalah laporan per store
        if (!$context['canSelectStore']) {
            return redirect()->route('reports.sales')->with('error', 'Laporan penjualan per toko hanya tersedia untuk user pusat.');
        }

        $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));

        // Query untuk mendapatkan data penjualan per store dengan total omzet
        $storesSales = DB::table('sales')
            ->select(
                'stores.name as store_name',
                'stores.id as store_id',
                DB::raw('COUNT(sales.id) as total_transactions'),
                DB::raw('SUM(sales.total_amount) as total_omzet')
            )
            ->join('stores', 'sales.store_id', '=', 'stores.id')
            ->whereBetween('sales.date', [$startDate, $endDate])
            ->groupBy('stores.id', 'stores.name')
            ->orderByDesc('total_omzet') // Sortir dari omzet terbesar ke terkecil
            ->get();

        // Hitung total omzet keseluruhan
        $totalOmzet = $storesSales->sum('total_omzet');
        $totalTransactions = $storesSales->sum('total_transactions');

        // Mendapatkan chart data untuk visualisasi
        $chartLabels = $storesSales->pluck('store_name')->toArray();
        $chartValues = $storesSales->pluck('total_omzet')->toArray();

        $chart_data = [
            'labels' => $chartLabels,
            'values' => $chartValues
        ];

        return view('reports.sales-by-store', compact(
            'storesSales',
            'totalOmzet',
            'totalTransactions',
            'chart_data',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Export sales by store report to Excel.
     * Hanya untuk user pusat
     */
    public function exportSalesByStore(Request $request)
    {
        $context = $this->getUserStoreContext();

        if (!$context['canSelectStore']) {
            return redirect()->route('reports.sales')->with('error', 'Export laporan per toko hanya tersedia untuk user pusat.');
        }

        $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));

        // Query untuk mendapatkan data penjualan per store
        $storesSales = DB::table('sales')
            ->select(
                'stores.name as store_name',
                'stores.id as store_id',
                DB::raw('COUNT(sales.id) as total_transactions'),
                DB::raw('SUM(sales.total_amount) as total_omzet')
            )
            ->join('stores', 'sales.store_id', '=', 'stores.id')
            ->whereBetween('sales.date', [$startDate, $endDate])
            ->groupBy('stores.id', 'stores.name')
            ->orderByDesc('total_omzet') // Sortir dari omzet terbesar ke terkecil
            ->get();

        // Hitung total omzet keseluruhan untuk persentase
        $totalOmzet = $storesSales->sum('total_omzet');

        // Export to Excel
        return Excel::download(new SalesByStoreExport($storesSales, $totalOmzet, $startDate, $endDate), 'penjualan_per_toko.xlsx');
    }

    /**
     * Print daily sales report on receipt paper for a store.
     */
    public function printDailySalesReceipt(Request $request)
    {
        $context = $this->getUserStoreContext();
        $date = $request->input('date', now()->format('Y-m-d'));

        // Gunakan store_id user jika ada, atau ambil dari request
        $storeId = $context['userStoreId'] ?? $request->input('store_id');

        if (!$storeId) {
            return redirect()->back()->with('error', 'Store ID is required');
        }

        $store = Store::findOrFail($storeId);

        // Pastikan user cabang hanya bisa print untuk toko mereka
        if ($context['userStoreId'] && $context['userStoreId'] != $storeId) {
            return redirect()->back()->with('error', 'Anda hanya dapat mencetak laporan untuk toko Anda sendiri');
        }

        // Get sales data for the day WITH DETAILS and PRODUCTS
        $salesQuery = Sale::with(['saleDetails.product', 'saleDetails.unit'])
            ->where('store_id', $storeId)
            ->whereDate('date', $date);

        $sales = $salesQuery->get();

        // Calculate summary
        $total_sales = $sales->sum('total_amount');
        $total_transactions = $sales->count();

        // Get payment methods breakdown
        $payment_methods = $sales->groupBy('payment_type')
            ->map(function ($items) {
                return [
                    'count' => $items->count(),
                    'amount' => $items->sum('total_amount')
                ];
            })
            ->toArray();

        return view('reports.print.daily_sales_receipt', compact(
            'store',
            'date',
            'sales',
            'total_sales',
            'total_transactions',
            'payment_methods'
        ));
    }

    // ================ PRIVATE METHODS ================

    /**
     * Get sales chart data.
     */
    private function getSalesChartData($startDate, $endDate, $storeId = null, $paymentType = null)
    {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        $days = $end->diffInDays($start) + 1;

        $labels = [];
        $values = [];

        // If more than 31 days, group by week
        if ($days > 31) {
            $data = Sale::select(
                DB::raw('YEARWEEK(date, 1) as year_week'),
                DB::raw('MIN(date) as week_start'),
                DB::raw('SUM(total_amount) as total')
            )
            ->whereBetween('date', [$startDate, $endDate]);

            if ($storeId) {
                $data->where('store_id', $storeId);
            }

            if ($paymentType) {
                $data->where('payment_type', $paymentType);
            }

            $data = $data->groupBy('year_week')
                ->orderBy('year_week')
                ->get();

            foreach ($data as $item) {
                $labels[] = Carbon::parse($item->week_start)->format('d M Y');
                $values[] = $item->total;
            }
        }
        // If more than 2 days but less than 31, group by day
        else {
            $current = $start->copy();
            while ($current <= $end) {
                $date = $current->format('Y-m-d');
                $labels[] = $current->format('d M');

                $query = Sale::whereDate('date', $date);

                if ($storeId) {
                    $query->where('store_id', $storeId);
                }

                if ($paymentType) {
                    $query->where('payment_type', $paymentType);
                }

                $values[] = $query->sum('total_amount');

                $current->addDay();
            }
        }

        return [
            'labels' => $labels,
            'values' => $values
        ];
    }

    /**
     * Get top selling products.
     */
    private function getTopSellingProducts($startDate, $endDate, $storeId = null)
    {
        $query = SaleDetail::with(['product' => function($q) {
                $q->withTrashed(); // Tambahkan withTrashed disini untuk mengambil produk yang sudah dihapus
            }, 'product.category', 'product.baseUnit'])
            ->select(
                'product_id',
                DB::raw('SUM(quantity) as total_quantity'),
                DB::raw('SUM(subtotal) as total_amount')
            )
            ->whereHas('sale', function($q) use ($startDate, $endDate, $storeId) {
                $q->whereBetween('date', [$startDate, $endDate]);

                if ($storeId) {
                    $q->where('store_id', $storeId);
                }
            })
            ->groupBy('product_id')
            ->orderByDesc('total_amount')
            ->limit(10)
            ->get();

        return $query;
    }

    /**
     * Get purchases chart data.
     */
    private function getPurchasesChartData($startDate, $endDate, $supplierId = null, $status = null)
    {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        $days = $end->diffInDays($start) + 1;

        $labels = [];
        $values = [];

        // If more than 31 days, group by week
        if ($days > 31) {
            $data = Purchase::select(
                DB::raw('YEARWEEK(date, 1) as year_week'),
                DB::raw('MIN(date) as week_start'),
                DB::raw('SUM(total_amount) as total')
            )
            ->whereBetween('date', [$startDate, $endDate]);

            if ($supplierId) {
                $data->where('supplier_id', $supplierId);
            }

            if ($status) {
                $data->where('status', $status);
            }

            $data = $data->groupBy('year_week')
                ->orderBy('year_week')
                ->get();

            foreach ($data as $item) {
                $labels[] = Carbon::parse($item->week_start)->format('d M Y');
                $values[] = $item->total;
            }
        }
        // If more than 2 days but less than 31, group by day
        else {
            $current = $start->copy();
            while ($current <= $end) {
                $date = $current->format('Y-m-d');
                $labels[] = $current->format('d M');

                $query = Purchase::whereDate('date', $date);

                if ($supplierId) {
                    $query->where('supplier_id', $supplierId);
                }

                if ($status) {
                    $query->where('status', $status);
                }

                $values[] = $query->sum('total_amount');

                $current->addDay();
            }
        }

        return [
            'labels' => $labels,
            'values' => $values
        ];
    }

    /**
     * Get top purchased products.
     */
    private function getTopPurchasedProducts($startDate, $endDate, $supplierId = null)
    {
        $query = PurchaseDetail::with(['product' => function($q) {
                $q->withTrashed(); // Tambahkan withTrashed disini untuk mengambil produk yang sudah dihapus
            }, 'product.category', 'product.baseUnit'])
            ->select(
                'product_id',
                DB::raw('SUM(quantity) as total_quantity'),
                DB::raw('SUM(subtotal) as total_amount')
            )
            ->whereHas('purchase', function($q) use ($startDate, $endDate, $supplierId) {
                $q->whereBetween('date', [$startDate, $endDate])
                    ->where('status', '!=', 'pending'); // Only include confirmed purchases

                if ($supplierId) {
                    $q->where('supplier_id', $supplierId);
                }
            })
            ->groupBy('product_id')
            ->orderByDesc('total_amount')
            ->limit(10)
            ->get();

        return $query;
    }

    /**
     * Get finance chart data.
     */
    private function getFinanceChartData($startDate, $endDate, $storeId = null)
    {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        $days = $end->diffInDays($start) + 1;

        $labels = [];
        $salesValues = [];
        $purchasesValues = [];
        $expensesValues = [];

        // If more than 31 days, group by week
        if ($days > 31) {
            // Get sales by week
            $salesData = Sale::select(
                DB::raw('YEARWEEK(date, 1) as year_week'),
                DB::raw('MIN(date) as week_start'),
                DB::raw('SUM(total_amount) as total')
            )
            ->whereBetween('date', [$startDate, $endDate]);

            if ($storeId) {
                $salesData->where('store_id', $storeId);
            }

            $salesData = $salesData->groupBy('year_week')
                ->orderBy('year_week')
                ->get()
                ->keyBy('year_week');

            // Get purchases by week (biasanya tidak difilter per store)
            $purchasesData = Purchase::select(
                DB::raw('YEARWEEK(date, 1) as year_week'),
                DB::raw('SUM(total_amount) as total')
            )
            ->whereBetween('date', [$startDate, $endDate])
            ->where('status', '!=', 'pending');

            $purchasesData = $purchasesData->groupBy('year_week')
                ->orderBy('year_week')
                ->get()
                ->keyBy('year_week');

            // Get expenses by week
            $expensesData = Expense::select(
                DB::raw('YEARWEEK(date, 1) as year_week'),
                DB::raw('SUM(amount) as total')
            )
            ->whereBetween('date', [$startDate, $endDate]);

            if ($storeId) {
                $expensesData->where('store_id', $storeId);
            }

            $expensesData = $expensesData->groupBy('year_week')
                ->orderBy('year_week')
                ->get()
                ->keyBy('year_week');

            // Get all week keys
            $allWeeks = collect(array_merge(
                $salesData->keys()->toArray(),
                $purchasesData->keys()->toArray(),
                $expensesData->keys()->toArray()
            ))->unique()->sort()->values();

            foreach ($allWeeks as $week) {
                $weekStart = $salesData->has($week) ? $salesData[$week]->week_start :
                            ($purchasesData->has($week) ? null : null);

                if (!$weekStart) {
                    continue;
                }

                $labels[] = Carbon::parse($weekStart)->format('d M Y');
                $salesValues[] = $salesData->has($week) ? $salesData[$week]->total : 0;
                $purchasesValues[] = $purchasesData->has($week) ? $purchasesData[$week]->total : 0;
                $expensesValues[] = $expensesData->has($week) ? $expensesData[$week]->total : 0;
            }
        }
        // If less than 31 days, group by day
        else {
            $current = $start->copy();
            while ($current <= $end) {
                $date = $current->format('Y-m-d');
                $labels[] = $current->format('d M');

                // Get sales for the day
                $salesQuery = Sale::whereDate('date', $date);
                if ($storeId) {
                    $salesQuery->where('store_id', $storeId);
                }
                $salesValues[] = $salesQuery->sum('total_amount');

                // Get purchases for the day (biasanya tidak per store)
                $purchasesQuery = Purchase::whereDate('date', $date)
                    ->where('status', '!=', 'pending');
                $purchasesValues[] = $purchasesQuery->sum('total_amount');

                // Get expenses for the day
                $expensesQuery = Expense::whereDate('date', $date);
                if ($storeId) {
                    $expensesQuery->where('store_id', $storeId);
                }
                $expensesValues[] = $expensesQuery->sum('amount');

                $current->addDay();
            }
        }

        return [
            'labels' => $labels,
            'sales' => $salesValues,
            'purchases' => $purchasesValues,
            'expenses' => $expensesValues
        ];
    }
}
