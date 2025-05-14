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
     * Display sales report.
     */
    public function sales(Request $request)
    {
        // Cek user yang login apakah terkait dengan toko tertentu
        $user = Auth::user();
        $userStoreId = $user->store_id ?? null; // Asumsi ada field store_id di tabel users

        $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));
        $storeId = $userStoreId ? $userStoreId : $request->input('store_id');
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
        $stores = $userStoreId ? collect([Store::find($userStoreId)]) : Store::where('is_active', true)->orderBy('name')->get();

        // Variabel untuk menentukan apakah tampilkan filter toko atau tidak
        $canSelectStore = !$userStoreId;

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
        $query = SaleDetail::with(['product.category', 'product.baseUnit'])
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
        * Export sales report to Excel.
        */
    public function exportSales(Request $request)
    {
        $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));
        $storeId = $request->input('store_id');
        $paymentType = $request->input('payment_type');

        return Excel::download(new SalesReportExport($startDate, $endDate, $storeId, $paymentType), 'sales_report.xlsx');
    }

    /**
        * Display purchases report.
        */
    public function purchases(Request $request)
    {
        $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));
        $supplierId = $request->input('supplier_id');
        $status = $request->input('status');

        $query = Purchase::with(['supplier', 'creator'])
            ->whereBetween('date', [$startDate, $endDate]);

        if ($supplierId) {
            $query->where('supplier_id', $supplierId);
        }

        if ($status) {
            $query->where('status', $status);
        }

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

        return view('reports.purchases', compact(
            'purchases',
            'total_purchases',
            'total_transactions',
            'status_data',
            'chart_data',
            'top_products',
            'suppliers'
        ));
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
        $query = PurchaseDetail::with(['product.category', 'product.baseUnit'])
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
        * Export purchases report to Excel.
        */
    public function exportPurchases(Request $request)
    {
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
        $storeId = $request->input('store_id');
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
            // Get products with warehouse stock
            $query->select('products.*', DB::raw('COALESCE(stock_warehouses.quantity, 0) as stock_quantity'))
                    ->leftJoin('stock_warehouses', function($join) {
                        $join->on('products.id', '=', 'stock_warehouses.product_id')
                            ->where('stock_warehouses.unit_id', '=', DB::raw('products.base_unit_id'));
                    });
        }

        if ($stockStatus) {
            switch ($stockStatus) {
                case 'in_stock':
                    $query->where(DB::raw('COALESCE(stock_stores.quantity, COALESCE(stock_warehouses.quantity, 0))'), '>', 0);
                    break;
                case 'low_stock':
                    $query->whereColumn(DB::raw('COALESCE(stock_stores.quantity, COALESCE(stock_warehouses.quantity, 0))'), '<', 'products.min_stock')
                            ->whereColumn(DB::raw('COALESCE(stock_stores.quantity, COALESCE(stock_warehouses.quantity, 0))'), '>', 0);
                    break;
                case 'out_of_stock':
                    $query->where(DB::raw('COALESCE(stock_stores.quantity, COALESCE(stock_warehouses.quantity, 0))'), '<=', 0);
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
                return [
                    'name' => $items->first()->category->name,
                    'value' => $items->sum(function($product) {
                        return $product->stock_quantity * $product->purchase_price;
                    })
                ];
            })
            ->values()
            ->toArray();

        // Get all stores and categories for filter
        $stores = Store::where('is_active', true)->orderBy('name')->get();
        $categories = Category::orderBy('name')->get();

        return view('reports.inventory', compact(
            'products',
            'total_products',
            'total_stock_value',
            'low_stock_count',
            'out_of_stock_count',
            'category_data',
            'stores',
            'categories'
        ));
    }

    /**
        * Export inventory report to Excel.
        */
    public function exportInventory(Request $request)
    {
        $storeId = $request->input('store_id');
        $categoryId = $request->input('category_id');
        $stockStatus = $request->input('stock_status');

        return Excel::download(new InventoryReportExport($storeId, $categoryId, $stockStatus), 'inventory_report.xlsx');
    }

    /**
     * Display finance report.
     */
    public function finance(Request $request)
    {
        $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));
        $storeId = $request->input('store_id');

        // Debug untuk memeriksa parameter tanggal
        Log::info('Finance report params', [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'store_id' => $storeId
        ]);

        // Mengambil semua saldo awal yang tanggalnya <= tanggal mulai laporan
        $query = InitialBalance::with('category')
            ->where('date', '<=', $endDate);

        if ($storeId) {
            $query->where(function($q) use ($storeId) {
                $q->where('store_id', $storeId)
                ->orWhereNull('store_id'); // Juga ambil saldo global
            });
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

        // Get purchases
        $purchasesQuery = Purchase::whereBetween('date', [$startDate, $endDate])
            ->where('status', '!=', 'pending');
        $purchases = $purchasesQuery->sum('total_amount');

        // Get expenses - ubah untuk menggunakan relasi category
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

        // Get expense breakdown - ubah untuk menggunakan kategori dari tabel ExpenseCategory
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

        // Get total payables & receivables for summary cards
        $totalPayables = AccountPayable::where('status', '!=', 'paid')->sum('amount');
        $overduePayables = AccountPayable::where('status', '!=', 'paid')
                        ->where('due_date', '<', now())
                        ->sum(DB::raw('amount - paid_amount'));

        $totalReceivables = AccountReceivable::where('status', '!=', 'paid')->sum('amount');
        $overdueReceivables = AccountReceivable::where('status', '!=', 'paid')
                        ->where('due_date', '<', now())
                        ->sum(DB::raw('amount - paid_amount'));

        // Get all stores for filter
        $stores = Store::where('is_active', true)->orderBy('name')->get();

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
            'startDate',  // Tambahkan parameter ini agar tersedia di view
            'endDate'     // Tambahkan parameter ini agar tersedia di view
        ));
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

            // Get purchases by week
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

                // Get purchases for the day
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

    /**
        * Export finance report to Excel.
        */
    public function exportFinance(Request $request)
    {
        $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));
        $storeId = $request->input('store_id');

        return Excel::download(new FinanceReportExport($startDate, $endDate, $storeId), 'finance_report.xlsx');
    }

    /**
        * Display profit and loss report.
        */
    public function profitLoss(Request $request)
    {
        $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));
        $storeId = $request->input('store_id');

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
        $expensesQuery = Expense::whereBetween('date', [$startDate, $endDate]);
        if ($storeId) {
            $expensesQuery->where('store_id', $storeId);
        }

        $expenses = $expensesQuery->sum('amount');

        // Calculate gross profit and net profit
        $grossProfit = $totalSales - $cogs;
        $netProfit = $grossProfit - $expenses;

        // Get expense breakdown
        $expenseBreakdown = Expense::select('category', DB::raw('SUM(amount) as total'))
            ->whereBetween('date', [$startDate, $endDate]);

        if ($storeId) {
            $expenseBreakdown->where('store_id', $storeId);
        }

        $expenseBreakdown = $expenseBreakdown->groupBy('category')
            ->orderByDesc('total')
            ->get();

        // Get all stores for filter
        $stores = Store::where('is_active', true)->orderBy('name')->get();

        return view('reports.profit-loss', compact(
            'totalSales',
            'cogs',
            'grossProfit',
            'expenses',
            'netProfit',
            'expenseBreakdown',
            'stores'
        ));
    }

    /**
        * Export profit and loss report to Excel.
        */
    public function exportProfitLoss(Request $request)
    {
        $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));
        $storeId = $request->input('store_id');

        return Excel::download(new ProfitLossReportExport($startDate, $endDate, $storeId), 'profit_loss_report.xlsx');
    }

    /**
 * Display payables (debt to suppliers) report
 */
public function payables(Request $request)
{
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
     */
    public function receivables(Request $request)
    {
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
     */
    public function balanceSheet(Request $request)
    {
        $date = $request->input('date', now()->format('Y-m-d'));

        // --- AKTIVA (ASSETS) ---

        // Ambil saldo awal kas dan bank dari tabel initial_balances
        $initialBalance = InitialBalance::where('date', '<=', $date)
                        ->orderBy('date', 'desc')
                        ->first();

        // 1. Kas & Setara Kas
        $cash = $initialBalance ? $initialBalance->cash_balance : 0;

        // 1a. Bank 1
        $bank1 = $initialBalance ? $initialBalance->bank1_balance : 0;

        // 1b. Bank 2
        $bank2 = $initialBalance ? $initialBalance->bank2_balance : 0;

        // Total kas dan bank
        $totalCashAndBank = $cash + $bank1 + $bank2;

        // 2. Piutang Dagang
        $accountsReceivable = AccountReceivable::where('status', '!=', 'paid')
                            ->where('due_date', '>=', $date)
                            ->sum(DB::raw('amount - paid_amount'));

        // 3. Persediaan Barang
        $inventory = DB::table('stock_warehouses')
                    ->join('products', 'stock_warehouses.product_id', '=', 'products.id')
                    ->sum(DB::raw('stock_warehouses.quantity * products.purchase_price'));

        // Total Aktiva Lancar
        $totalCurrentAssets = $totalCashAndBank + $accountsReceivable + $inventory;

        // Aktiva Tetap (Fixed Assets)
        // Implementasi: Jika ada data aktiva tetap di sistem
        $fixedAssets = 0;
        $accumulatedDepreciation = 0;
        $netFixedAssets = $fixedAssets - $accumulatedDepreciation;

        // Total Aktiva
        $totalAssets = $totalCurrentAssets + $netFixedAssets;

        // --- PASIVA (LIABILITIES & EQUITY) ---

        // Kewajiban Lancar (Current Liabilities)

        // 1. Hutang Dagang
        $accountsPayable = AccountPayable::where('status', '!=', 'paid')
                        ->where('due_date', '>=', $date)
                        ->sum(DB::raw('amount - paid_amount'));

        // 2. Hutang Pajak (jika ada)
        $taxPayable = 0;

        // Total Kewajiban Lancar
        $totalCurrentLiabilities = $accountsPayable + $taxPayable;

        // Kewajiban Jangka Panjang (jika ada)
        $longTermLiabilities = 0;

        // Total Kewajiban
        $totalLiabilities = $totalCurrentLiabilities + $longTermLiabilities;

        // Ekuitas (Equity)
        // Implementasi: Ambil modal awal dan laba ditahan
        $initialCapital = 0; // Modal awal

        // Laba tahun berjalan
        $startOfYear = Carbon::parse($date)->startOfYear()->format('Y-m-d');

        // Pendapatan
        $revenue = Sale::whereBetween('date', [$startOfYear, $date])
                ->sum('total_amount');

        // Harga Pokok Penjualan
        $costOfGoodsSold = SaleDetail::join('products', 'sale_details.product_id', '=', 'products.id')
                        ->whereHas('sale', function($q) use ($startOfYear, $date) {
                            $q->whereBetween('date', [$startOfYear, $date]);
                        })
                        ->sum(DB::raw('sale_details.quantity * products.purchase_price'));

        // Beban Operasional
        $operatingExpenses = Expense::whereBetween('date', [$startOfYear, $date])
                            ->sum('amount');

        // Laba Bersih
        $netIncome = $revenue - $costOfGoodsSold - $operatingExpenses;

        // Total Ekuitas
        $totalEquity = $initialCapital + $netIncome;

        // Total Pasiva
        $totalLiabilitiesAndEquity = $totalLiabilities + $totalEquity;

        // Perbedaan (seharusnya 0 jika balance)
        $difference = $totalAssets - $totalLiabilitiesAndEquity;

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
            'initialBalance'
        ));
    }

    /**
     * Display sales report by store/outlet (sorted by omzet - highest to lowest).
     */
    public function salesByStore(Request $request)
    {
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
     */
    public function exportSalesByStore(Request $request)
    {
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
        $date = $request->input('date', now()->format('Y-m-d'));

        // Cek user yang login apakah terkait dengan toko tertentu
        $user = Auth::user();
        $userStoreId = $user->store_id ?? null;

        // Gunakan store_id user jika ada, atau ambil dari request
        $storeId = $userStoreId ? $userStoreId : $request->input('store_id');

        if (!$storeId) {
            return redirect()->back()->with('error', 'Store ID is required');
        }

        $store = Store::findOrFail($storeId);

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
}
