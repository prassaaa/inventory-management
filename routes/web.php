<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\PurchaseReturnController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\ShipmentController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\StockAdjustmentController;
use App\Http\Controllers\StockOpnameController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Admin\BackOfficeStoreOrderController;
use App\Http\Controllers\Store\StoreClientOrderController;
use App\Http\Controllers\Warehouse\WarehouseOrderController;
use App\Http\Controllers\Warehouse\WarehousePurchaseController;
use App\Http\Controllers\FinanceController;
use App\Http\Controllers\FinanceBalanceController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Master Data Routes
    Route::group(['middleware' => ['permission:view categories']], function () {
        Route::resource('categories', CategoryController::class);
    });

    Route::group(['middleware' => ['permission:view units']], function () {
        Route::resource('units', UnitController::class);
    });

    Route::group(['middleware' => ['permission:view products']], function () {
        // 1. Definisikan route khusus trashed sebelum resource route
        Route::get('products/trashed', [ProductController::class, 'trashed'])->name('products.trashed');
        Route::patch('products/restore/{id}', [ProductController::class, 'restore'])->name('products.restore');
        Route::delete('products/force-delete/{id}', [ProductController::class, 'forceDelete'])->name('products.force-delete');

        // 2. Kemudian definisikan resource route
        Route::resource('products', ProductController::class);

        // 3. Route lainnya
        Route::get('products/import/template', [ProductController::class, 'importTemplate'])->name('products.import.template');
        Route::post('products/import', [ProductController::class, 'import'])->name('products.import');
        Route::get('products/export', [ProductController::class, 'export'])->name('products.export');
        Route::get('/products/ingredients', [ProductController::class, 'getIngredients'])->name('products.ingredients');
    });

    Route::group(['middleware' => ['permission:view suppliers']], function () {
        Route::resource('suppliers', SupplierController::class);
    });

    Route::group(['middleware' => ['permission:view stores']], function () {
        Route::resource('stores', StoreController::class);
    });

    // Transaction Routes
    Route::group(['middleware' => ['permission:view purchases']], function () {
        Route::resource('purchases', PurchaseController::class);
        Route::post('purchases/{purchase}/confirm', [PurchaseController::class, 'confirm'])->name('purchases.confirm');
        Route::get('purchases/{purchase}/receipt', [PurchaseController::class, 'receipt'])->name('purchases.receipt');
    });

    Route::group(['middleware' => ['permission:view purchase returns']], function () {
        Route::resource('purchase-returns', PurchaseReturnController::class);
        Route::get('purchase-returns/create/{purchase}', [PurchaseReturnController::class, 'createFromPurchase'])->name('purchase-returns.create-from-purchase');
    });

    // Admin Store Orders
    Route::group(['middleware' => ['permission:view store orders']], function () {
        Route::resource('store-orders', BackOfficeStoreOrderController::class);
        Route::post('store-orders/{storeOrder}/confirm', [BackOfficeStoreOrderController::class, 'confirm'])->name('store-orders.confirm');
        Route::post('store-orders/{storeOrder}/forward', [BackOfficeStoreOrderController::class, 'forwardToWarehouse'])->name('store-orders.forward');
    });

    // Store Side Orders
    Route::group(['middleware' => ['permission:view store orders']], function () {
        Route::get('store/orders', [StoreClientOrderController::class, 'index'])->name('store.orders.index');
        Route::get('store/orders/create', [StoreClientOrderController::class, 'create'])->name('store.orders.create');
        Route::post('store/orders', [StoreClientOrderController::class, 'store'])->name('store.orders.store');
        Route::get('store/orders/{id}', [StoreClientOrderController::class, 'show'])->name('store.orders.show');
        Route::post('store/orders/{id}/confirm-delivery', [StoreClientOrderController::class, 'confirmDelivery'])->name('store.orders.confirm-delivery');
    });

    // Warehouse Store Orders
    Route::group(['middleware' => ['permission:view store orders']], function () {
        Route::get('warehouse/store-orders', [WarehouseOrderController::class, 'index'])->name('warehouse.store-orders.index');
        Route::get('warehouse/store-orders/{id}', [WarehouseOrderController::class, 'show'])->name('warehouse.store-orders.show');
        Route::get('warehouse/store-orders/{id}/shipment/create', [WarehouseOrderController::class, 'createShipment'])->name('warehouse.store-orders.shipment.create');
        Route::post('warehouse/store-orders/{id}/shipment', [WarehouseOrderController::class, 'storeShipment'])->name('warehouse.store-orders.shipment.store');
    });

    // Warehouse Purchase Confirmation - BAGIAN BARU
    Route::group(['middleware' => ['permission:view purchases']], function () {
        Route::get('warehouse/purchases', [WarehousePurchaseController::class, 'index'])->name('warehouse.purchases.index');
        Route::get('warehouse/purchases/{purchase}', [WarehousePurchaseController::class, 'show'])->name('warehouse.purchases.show');
        Route::post('warehouse/purchases/{purchase}/receive', [WarehousePurchaseController::class, 'processReceive'])->name('warehouse.purchases.receive');
    });

    // Shipments
    Route::group(['middleware' => ['permission:view shipments']], function () {
        Route::resource('shipments', ShipmentController::class);
        Route::get('shipments/create/{storeOrder}', [ShipmentController::class, 'createFromOrder'])->name('shipments.create-from-order');
        Route::post('shipments/{shipment}/deliver', [ShipmentController::class, 'deliver'])->name('shipments.deliver');
        Route::get('shipments/{shipment}/document', [ShipmentController::class, 'document'])->name('shipments.document');
    });


    // Finance Management - BAGIAN BARU
    Route::group(['middleware' => ['permission:view financial reports']], function () {
        Route::post('/finance/record-payment', [FinanceController::class, 'recordPayment'])->name('finance.record-payment');
        Route::post('/finance/record-receivable-payment', [FinanceController::class, 'recordReceivablePayment'])->name('finance.record-receivable-payment');

        // Finance Balance & Expense Management - pindahkan routes ini ke sini
        Route::get('/finance/balance/create', [FinanceBalanceController::class, 'create'])->name('finance.balance.create');
        Route::post('/finance/balance/store', [FinanceBalanceController::class, 'store'])->name('finance.balance.store');
        Route::get('/finance/expense/create', [FinanceBalanceController::class, 'createExpense'])->name('finance.expense.create');
        Route::post('/finance/expense/store', [FinanceBalanceController::class, 'storeExpense'])->name('finance.expense.store');
    });

    // Sales (POS)
    Route::group(['middleware' => ['permission:view sales']], function () {
        Route::resource('sales', SaleController::class)->except(['edit', 'update', 'destroy']);
        Route::get('sales/{sale}/receipt', [SaleController::class, 'receipt'])->name('sales.receipt');
        Route::get('sales/{sale}/rawbt-receipt', [SaleController::class, 'rawbtReceipt'])
        ->name('sales.rawbt-receipt')
        ->middleware('allow-rawbt');
    });

    Route::group(['middleware' => ['permission:create sales']], function () {
        Route::get('pos', [SaleController::class, 'pos'])->name('pos');
        Route::post('pos/process', [SaleController::class, 'processPos'])->name('pos.process');
    });

    // Stock Management Routes
    Route::group(['middleware' => ['permission:view stock warehouses']], function () {
        Route::get('/stock/warehouse', [StockController::class, 'warehouse'])->name('stock.warehouse');
    });

    Route::group(['middleware' => ['permission:view stock stores']], function () {
        Route::get('/stock/store', [StockController::class, 'store'])->name('stock.store');
    });

    // Stock Adjustments - Tanpa middleware sementara ini untuk memperbaiki error
    Route::resource('stock-adjustments', StockAdjustmentController::class)->except(['edit', 'update', 'destroy']);

    Route::group(['middleware' => ['permission:view stock opnames']], function () {
        Route::resource('stock-opnames', StockOpnameController::class)->except(['edit', 'update', 'destroy']);
        Route::post('stock-opnames/{stockOpname}/confirm', [StockOpnameController::class, 'confirm'])->name('stock-opnames.confirm');
        Route::get('stock-opnames/get-products', [StockOpnameController::class, 'getProducts'])->name('stock-opnames.get-products');
    });

    // Route cetak laporan harian kasir di luar group untuk memudahkan debugging
    Route::get('/print-daily-sales', [ReportController::class, 'printDailySalesReceipt'])->name('print.daily.sales');

    // Report Routes
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::group(['middleware' => ['permission:view sales']], function () {
            Route::get('/sales', [ReportController::class, 'sales'])->name('sales');
            Route::get('/sales/export', [ReportController::class, 'exportSales'])->name('sales.export');

            // Perbaiki rute yang bermasalah
            Route::get('/sales/print-receipt', [ReportController::class, 'printDailySalesReceipt'])->name('sales.print-receipt');

            // Laporan Penjualan Per Toko (Peringkat berdasarkan omzet)
            Route::get('/sales-by-store', [ReportController::class, 'salesByStore'])->name('sales-by-store');
            Route::get('/sales-by-store/export', [ReportController::class, 'exportSalesByStore'])->name('sales-by-store.export');
        });

        Route::group(['middleware' => ['permission:view purchases']], function () {
            Route::get('/purchases', [ReportController::class, 'purchases'])->name('purchases');
            Route::get('/purchases/export', [ReportController::class, 'exportPurchases'])->name('purchases.export');
        });

        Route::group(['middleware' => ['permission:view stock warehouses,view stock stores']], function () {
            Route::get('/inventory', [ReportController::class, 'inventory'])->name('inventory');
            Route::get('/inventory/export', [ReportController::class, 'exportInventory'])->name('inventory.export');
        });

        Route::group(['middleware' => ['permission:view financial reports']], function () {
            Route::get('/finance', [ReportController::class, 'finance'])->name('finance');
            Route::get('/profit-loss', [ReportController::class, 'profitLoss'])->name('profit-loss');
            Route::get('/finance/export', [ReportController::class, 'exportFinance'])->name('finance.export');
            Route::get('/profit-loss/export', [ReportController::class, 'exportProfitLoss'])->name('profit-loss.export');

            // Rute Laporan Hutang dan Piutang - BAGIAN BARU
            Route::get('/payables', [ReportController::class, 'payables'])->name('payables');
            Route::get('/receivables', [ReportController::class, 'receivables'])->name('receivables');

            // Laporan Neraca - BAGIAN BARU
            Route::get('/balance-sheet', [ReportController::class, 'balanceSheet'])->name('balance-sheet');
        });
    });

    // System Management Routes
    Route::group(['middleware' => ['permission:manage users']], function () {
        Route::resource('users', UserController::class);
    });

    Route::group(['middleware' => ['permission:manage roles']], function () {
        Route::resource('roles', RoleController::class);
        Route::resource('permissions', PermissionController::class);
    });

    // User Profile (available for all authenticated users)
    Route::get('/profile', [UserController::class, 'profile'])->name('profile');
    Route::put('/profile', [UserController::class, 'updateProfile'])->name('profile.update');
    Route::put('/profile/password', [UserController::class, 'updatePassword'])->name('profile.password');

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/{id}/read', [NotificationController::class, 'read'])->name('notifications.read');
    Route::get('/notifications/unread-count', [NotificationController::class, 'getUnreadCount'])->name('notifications.unread-count');
});

// Debug Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/debug-permissions', function () {
        $user = auth()->user();
        return [
            'user_id' => $user->id,
            'user_name' => $user->name,
            'user_email' => $user->email,
            'roles' => $user->roles->pluck('name'),
            'permissions_count' => $user->getAllPermissions()->count(),
            'all_permissions' => $user->getAllPermissions()->pluck('name')
        ];
    });
});

// Fix Permissions Route (should be secured in production)
Route::get('/fix-permissions', function() {
    // Reset cached permissions
    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

    // Get the admin user
    $admin = \App\Models\User::where('email', 'admin@gmail.com')->first();

    if (!$admin) {
        return "Admin user not found!";
    }

    // Assign owner role
    $admin->assignRole('owner');

    return "Admin user now has owner role with " . $admin->getAllPermissions()->count() . " permissions";
});

Route::get('/debug/purchase/{purchase}', function (App\Models\Purchase $purchase) {
    return [
        'id' => $purchase->id,
        'invoice' => $purchase->invoice_number,
        'status' => $purchase->status,
        'can_be_confirmed' => $purchase->status === 'pending',
        'route' => route('purchases.confirm', $purchase)
    ];
});

// Auth Routes (from Laravel Breeze)
require __DIR__.'/auth.php';
