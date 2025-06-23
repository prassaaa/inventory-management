<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\StockWarehouse;
use App\Models\StockStore;
use App\Models\StoreOrder;
use App\Models\Purchase;
use App\Models\Store;
use App\Models\Category;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        Log::info('Dashboard accessed by: ' . Auth::user()->name);

        try {
            // Debug: Tinjau koneksi database
            try {
                DB::connection()->getPdo();
                Log::info('Database connection successful');
            } catch (\Exception $e) {
                Log::error('Database connection failed: ' . $e->getMessage());
                return view('dashboard.index', [
                    'error' => 'Database connection failed: ' . $e->getMessage()
                ]);
            }

            // Total Products
            $totalProducts = $this->getTotalProducts();
            Log::info('Total products: ' . $totalProducts);

            // Today's Sales
            $todaySales = $this->getTodaySales();
            Log::info('Today sales: ' . $todaySales);

            // Low Stock Count
            $lowStockCount = $this->getLowStockCount();
            Log::info('Low stock count: ' . $lowStockCount);

            // Pending Orders
            $pendingOrders = $this->getPendingOrders();
            Log::info('Pending orders: ' . $pendingOrders);

            // Sales Chart Data (last 7 days)
            $salesChartData = $this->getSalesChartData();
            Log::info('Sales chart data generated: ' . count($salesChartData['labels']) . ' data points');

            // Top Products Data
            $topProductsData = $this->getTopProductsData();
            Log::info('Top products data generated: ' . count($topProductsData['labels']) . ' products');

            // Recent Transactions
            $recentTransactions = $this->getRecentTransactions();
            Log::info('Recent transactions: ' . count($recentTransactions) . ' found');

            // Store Orders
            $recentStoreOrders = $this->getRecentStoreOrders();
            Log::info('Recent store orders: ' . count($recentStoreOrders) . ' found');

            return view('dashboard.index', compact(
                'totalProducts',
                'todaySales',
                'lowStockCount',
                'pendingOrders',
                'salesChartData',
                'topProductsData',
                'recentTransactions',
                'recentStoreOrders'
            ));

        } catch (\Exception $e) {
            Log::error('Dashboard error: ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            // Return dashboard with error message
            return view('dashboard.index', [
                'totalProducts' => 0,
                'todaySales' => 0,
                'lowStockCount' => 0,
                'pendingOrders' => 0,
                'salesChartData' => ['labels' => [], 'data' => []],
                'topProductsData' => ['labels' => [], 'data' => []],
                'recentTransactions' => [],
                'error' => 'Terjadi kesalahan saat memuat dashboard: ' . $e->getMessage()
            ]);
        }
    }

    private function getTotalProducts()
    {
        return Product::where('is_active', true)->count();
    }

    private function getTodaySales()
    {
        $query = Sale::whereDate('date', Carbon::today());

        if (Auth::user()->hasRole('admin_store') && Auth::user()->store_id) {
            $query->where('store_id', Auth::user()->store_id);
        }

        return $query->sum('total_amount');
    }

    private function getLowStockCount()
    {
        if (Auth::user()->hasRole('admin_store') && Auth::user()->store_id) {
            // Get store stock under minimum
            $count = DB::table('stock_stores')
                ->join('products', 'stock_stores.product_id', '=', 'products.id')
                ->where('stock_stores.store_id', Auth::user()->store_id)
                ->where('products.is_active', true)
                ->whereColumn('stock_stores.quantity', '<', 'products.min_stock')
                ->count();

            Log::info("Low stock count for store {$Auth::user()->store_id}: {$count}");
            return $count;
        } else {
            // Get warehouse stock under minimum
            $count = DB::table('stock_warehouses')
                ->join('products', 'stock_warehouses.product_id', '=', 'products.id')
                ->where('products.is_active', true)
                ->whereColumn('stock_warehouses.quantity', '<', 'products.min_stock')
                ->count();

            Log::info("Low stock count for warehouse: {$count}");
            return $count;
        }
    }

    private function getPendingOrders()
    {
        if (Auth::user()->hasRole('admin_store') && Auth::user()->store_id) {
            // For store admin, count pending store orders
            return StoreOrder::where('store_id', Auth::user()->store_id)
                ->where('status', StoreOrder::STATUS_PENDING)
                ->count();
        } elseif (Auth::user()->hasRole(['owner', 'admin_back_office'])) {
            // For central admin, count pending purchase orders
            return StoreOrder::where('status', StoreOrder::STATUS_PENDING)->count();
        } elseif (Auth::user()->hasRole('admin_gudang')) {
            // For warehouse admin, count forwarded store orders
            return StoreOrder::where('status', StoreOrder::STATUS_FORWARDED_TO_WAREHOUSE)->count();
        } else {
            return 0;
        }
    }

    private function getSalesChartData()
    {
        $labels = [];
        $data = [];

        try {
            $salesData = DB::table('sales')
                ->select(
                    DB::raw('DATE(date) as sale_date'),
                    DB::raw('SUM(total_amount) as total_sales')
                );

            // Filter for store admin
            if (Auth::user()->hasRole('admin_store') && Auth::user()->store_id) {
                $salesData->where('store_id', Auth::user()->store_id);
            }

            $salesData = $salesData->whereDate('date', '>=', Carbon::now()->subDays(7))
                ->groupBy('sale_date')
                ->orderBy('sale_date')
                ->get();

            // Generate array for last 7 days
            for ($i = 6; $i >= 0; $i--) {
                $date = Carbon::now()->subDays($i)->format('Y-m-d');
                $labels[] = Carbon::now()->subDays($i)->format('d/m');

                $sale = $salesData->firstWhere('sale_date', $date);
                $data[] = $sale ? (int)$sale->total_sales : 0;
            }

            // Log the data for debugging
            Log::info('Sales chart data: ', [
                'labels' => $labels,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            Log::error('Error generating sales chart data: ' . $e->getMessage());

            // Fallback to empty data
            for ($i = 6; $i >= 0; $i--) {
                $labels[] = Carbon::now()->subDays($i)->format('d/m');
                $data[] = 0;
            }
        }

        return [
            'labels' => $labels,
            'data' => $data
        ];
    }

    private function getTopProductsData()
    {
        $labels = [];
        $data = [];

        try {
            $query = DB::table('sale_details')
                ->join('products', 'sale_details.product_id', '=', 'products.id')
                ->join('sales', 'sale_details.sale_id', '=', 'sales.id')
                ->select(
                    'products.name',
                    'products.id as product_id',
                    DB::raw('SUM(sale_details.quantity) as total_sold')
                )
                ->where('products.is_active', true);

            // Filter for store admin - hanya tampilkan data dari toko sendiri
            if (Auth::user()->store_id) {
                $query->where('sales.store_id', Auth::user()->store_id);
                Log::info('Dashboard: Filtering top products for store_id: ' . Auth::user()->store_id);
            }

            // Filter untuk 30 hari terakhir untuk data yang lebih relevan
            $query->where('sales.date', '>=', Carbon::now()->subDays(30));

            $topProducts = $query->groupBy('products.id', 'products.name')
                ->having('total_sold', '>', 0)
                ->orderBy('total_sold', 'desc')
                ->limit(5)
                ->get();

            Log::info('Dashboard: Top products query result count: ' . $topProducts->count());

            if ($topProducts->count() > 0) {
                foreach ($topProducts as $product) {
                    $labels[] = $product->name;
                    $data[] = (int)$product->total_sold;
                }
                
                Log::info('Dashboard: Top products data found', [
                    'labels' => $labels,
                    'data' => $data
                ]);
            } else {
                // Jika tidak ada data, coba tanpa filter tanggal
                Log::info('Dashboard: No top products found in last 30 days, trying all time data');
                
                $allTimeQuery = DB::table('sale_details')
                    ->join('products', 'sale_details.product_id', '=', 'products.id')
                    ->join('sales', 'sale_details.sale_id', '=', 'sales.id')
                    ->select(
                        'products.name',
                        'products.id as product_id',
                        DB::raw('SUM(sale_details.quantity) as total_sold')
                    )
                    ->where('products.is_active', true);

                if (Auth::user()->store_id) {
                    $allTimeQuery->where('sales.store_id', Auth::user()->store_id);
                }

                $allTimeProducts = $allTimeQuery->groupBy('products.id', 'products.name')
                    ->having('total_sold', '>', 0)
                    ->orderBy('total_sold', 'desc')
                    ->limit(5)
                    ->get();

                if ($allTimeProducts->count() > 0) {
                    foreach ($allTimeProducts as $product) {
                        $labels[] = $product->name;
                        $data[] = (int)$product->total_sold;
                    }
                    
                    Log::info('Dashboard: All time top products data found', [
                        'labels' => $labels,
                        'data' => $data
                    ]);
                } else {
                    $labels = ['Tidak ada data'];
                    $data = [0];
                    Log::info('Dashboard: No top products data found at all');
                }
            }

        } catch (\Exception $e) {
            Log::error('Error generating top products data: ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            // Default fallback data
            $labels = ['Tidak ada data'];
            $data = [0];
        }

        return [
            'labels' => $labels,
            'data' => $data
        ];
    }

    private function getRecentTransactions()
    {
        try {
            // Start with sales query
            $salesQuery = Sale::select(
                'id',
                'date',
                DB::raw("'Sale' as type"),
                'total_amount as amount',
                DB::raw('status as status')
            );

            // Filter for store admin
            if (Auth::user()->hasRole('admin_store') && Auth::user()->store_id) {
                $salesQuery->where('store_id', Auth::user()->store_id);
            }

            // Only include purchases if user has permission
            if (Auth::user()->hasRole(['owner', 'admin_back_office', 'admin_gudang'])) {
                $purchasesQuery = Purchase::select(
                    'id',
                    'date',
                    DB::raw("'Purchase' as type"),
                    'total_amount as amount',
                    DB::raw('status as status')
                );

                // Combine sales and purchases
                $transactions = $salesQuery->unionAll($purchasesQuery)
                    ->orderBy('date', 'desc')
                    ->limit(10)
                    ->get();
            } else {
                // Only sales for store admin
                $transactions = $salesQuery
                    ->orderBy('date', 'desc')
                    ->limit(10)
                    ->get();
            }

            return $transactions;
        } catch (\Exception $e) {
            Log::error('Error getting recent transactions: ' . $e->getMessage());
            return collect();
        }
    }

    private function getRecentStoreOrders()
    {
        try {
            $query = StoreOrder::with('store');

            // Filter for store admin
            if (Auth::user()->hasRole('admin_store') && Auth::user()->store_id) {
                $query->where('store_id', Auth::user()->store_id);
            }

            $recentStoreOrders = $query->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();

            return $recentStoreOrders;
        } catch (\Exception $e) {
            Log::error('Error getting recent store orders: ' . $e->getMessage());
            return collect();
        }
    }
}
