<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Sale;
use App\Models\StockWarehouse;
use App\Models\StoreOrder;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        try {
            // Total Products
            $totalProducts = Product::count();

            // Today's Sales
            $todaySales = Sale::whereDate('date', Carbon::today())->sum('total_amount');

            // Low Stock Count
            $lowStockCount = Product::whereHas('stockWarehouses', function ($query) {
                $query->whereColumn('quantity', '<', 'products.min_stock');
            })->count();

            // Pending Orders
            $pendingOrders = StoreOrder::where('status', 'pending')->count();

            // Sales Chart Data (last 7 days)
            $salesChartData = $this->getSalesChartData();

            // Top Products Data
            $topProductsData = $this->getTopProductsData();

            // Recent Transactions
            $recentTransactions = $this->getRecentTransactions();

            return view('dashboard.index', compact(
                'totalProducts',
                'todaySales',
                'lowStockCount',
                'pendingOrders',
                'pendingStoreOrders',
                'salesChartData',
                'topProductsData',
                'recentTransactions'
            ));
        } catch (\Exception $e) {
            // Jika terjadi error, tampilkan dashboard dengan data minimal
            return view('dashboard.index', [
                'totalProducts' => 0,
                'todaySales' => 0,
                'lowStockCount' => 0,
                'pendingOrders' => 0,
                'salesChartData' => ['labels' => [], 'data' => []],
                'topProductsData' => ['labels' => [], 'data' => []],
                'recentTransactions' => []
            ]);
        }
    }

    private function getSalesChartData()
    {
        $labels = [];
        $data = [];

        try {
            $salesData = Sale::select(
                DB::raw('DATE(date) as sale_date'),
                DB::raw('SUM(total_amount) as total_sales')
            )
            ->whereDate('date', '>=', Carbon::now()->subDays(7))
            ->groupBy('sale_date')
            ->orderBy('sale_date')
            ->get();

            // Generate array for last 7 days
            for ($i = 6; $i >= 0; $i--) {
                $date = Carbon::now()->subDays($i)->format('Y-m-d');
                $labels[] = Carbon::now()->subDays($i)->format('d/m');

                $sale = $salesData->firstWhere('sale_date', $date);
                $data[] = $sale ? $sale->total_sales : 0;
            }
        } catch (\Exception $e) {
            // Jika error, kembalikan data kosong
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
            $topProducts = DB::table('sale_details')
                ->join('products', 'sale_details.product_id', '=', 'products.id')
                ->select('products.name', DB::raw('SUM(sale_details.quantity) as total_sold'))
                ->groupBy('products.name')
                ->orderBy('total_sold', 'desc')
                ->limit(5)
                ->get();

            $labels = $topProducts->pluck('name')->toArray();
            $data = $topProducts->pluck('total_sold')->toArray();

            // Jika tidak ada data, tambahkan placeholder
            if (empty($labels)) {
                $labels = ['No Data'];
                $data = [0];
            }
        } catch (\Exception $e) {
            $labels = ['No Data'];
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
            $sales = Sale::select(
                'id',
                'date',
                DB::raw("'Sale' as type"),
                'total_amount as amount',
                DB::raw("status as status")
            )
            ->orderBy('date', 'desc')
            ->limit(5);

            $purchases = DB::table('purchases')
                ->select(
                    'id',
                    'date',
                    DB::raw("'Purchase' as type"),
                    'total_amount as amount',
                    DB::raw("status as status")
                )
                ->orderBy('date', 'desc')
                ->limit(5)
                ->union($sales)
                ->orderBy('date', 'desc')
                ->limit(10)
                ->get()
                ->map(function ($item) {
                    $item->date = Carbon::parse($item->date);
                    return $item;
                });

            return $purchases;
        } catch (\Exception $e) {
            return collect();
        }
    }
}
