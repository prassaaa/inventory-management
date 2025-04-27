@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="container-fluid">
    <div class="row mb-4 align-items-center">
        <div class="col">
            <h1 class="h3 mb-0 text-dark fw-bold">
                <i class="fas fa-tachometer-alt me-2 text-primary"></i> Dashboard
            </h1>
            <p class="text-muted">Selamat datang, {{ Auth::user()->name }}! Berikut adalah ringkasan bisnis Anda.</p>
        </div>
        <div class="col-auto">
            <div class="btn-group" role="group">
                <button class="btn btn-sm btn-outline-primary" id="refreshData">
                    <i class="fas fa-sync-alt me-1"></i> Perbarui
                </button>
                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#filterModal">
                    <i class="fas fa-filter me-1"></i> Filter
                </button>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-xs fw-bold text-uppercase mb-1 text-primary">
                                Produk
                            </div>
                            <div class="h4 mb-0 fw-bold text-gray-800">{{ $totalProducts ?? 0 }}</div>
                            <div class="text-xs text-muted mt-2">
                                <i class="fas fa-arrow-up text-success me-1"></i> +5% dari bulan lalu
                            </div>
                        </div>
                        <div class="col-auto">
                            <div class="icon-circle bg-primary bg-opacity-10">
                                <i class="fas fa-boxes fa-2x text-primary"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-xs fw-bold text-uppercase mb-1 text-success">
                                Penjualan Hari Ini
                            </div>
                            <div class="h4 mb-0 fw-bold text-gray-800">{{ number_format($todaySales ?? 0, 0, ',', '.') }}</div>
                            <div class="text-xs text-muted mt-2">
                                <i class="fas fa-arrow-up text-success me-1"></i> +12% dari kemarin
                            </div>
                        </div>
                        <div class="col-auto">
                            <div class="icon-circle bg-success bg-opacity-10">
                                <i class="fas fa-cash-register fa-2x text-success"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-xs fw-bold text-uppercase mb-1 text-info">
                                Stok Menipis
                            </div>
                            <div class="h4 mb-0 fw-bold text-gray-800">{{ $lowStockCount ?? 0 }}</div>
                            <div class="text-xs text-muted mt-2">
                                <i class="fas fa-exclamation-triangle text-warning me-1"></i> Perlu perhatian
                            </div>
                        </div>
                        <div class="col-auto">
                            <div class="icon-circle bg-info bg-opacity-10">
                                <i class="fas fa-exclamation-triangle fa-2x text-info"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kartu Pesanan Toko -->
        @canany(['view store orders', 'view shipments'])
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-xs fw-bold text-uppercase mb-1 text-warning">
                                Pesanan Toko
                            </div>
                            <div class="h4 mb-0 fw-bold text-gray-800">
                                {{ App\Models\StoreOrder::where('status', 'pending')->count() ?? 0 }}
                            </div>
                            <div class="text-xs text-muted mt-2">
                                <i class="fas fa-clock text-warning me-1"></i> Menunggu proses
                            </div>
                        </div>
                        <div class="col-auto">
                            <div class="icon-circle bg-warning bg-opacity-10">
                                <i class="fas fa-shopping-basket fa-2x text-warning"></i>
                            </div>
                        </div>
                    </div>
                </div>
                @if(Auth::user()->hasRole('admin_store'))
                <a href="{{ route('store.orders.index') }}" class="card-footer bg-white text-center py-2 text-warning fw-bold small">
                @else
                <a href="{{ route('store-orders.index') }}" class="card-footer bg-white text-center py-2 text-warning fw-bold small">
                @endif
                    <span>Lihat Semua</span>
                    <i class="fas fa-arrow-right ms-1"></i>
                </a>
            </div>
        </div>
        @else
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-xs fw-bold text-uppercase mb-1 text-warning">
                                Pesanan Tertunda
                            </div>
                            <div class="h4 mb-0 fw-bold text-gray-800">{{ $pendingOrders ?? 0 }}</div>
                            <div class="text-xs text-muted mt-2">
                                <i class="fas fa-clock text-warning me-1"></i> Menunggu proses
                            </div>
                        </div>
                        <div class="col-auto">
                            <div class="icon-circle bg-warning bg-opacity-10">
                                <i class="fas fa-clipboard-list fa-2x text-warning"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endcanany
    </div>

    <div class="row mb-4">
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 fw-bold text-primary">Ringkasan Penjualan</h6>
                    <div class="dropdown no-arrow">
                        <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-ellipsis-v text-gray-400"></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="dropdownMenuLink">
                            <li><a class="dropdown-item" href="#"><i class="fas fa-download me-2 text-primary"></i> Unduh Laporan</a></li>
                            <li><a class="dropdown-item" href="#"><i class="fas fa-print me-2 text-primary"></i> Cetak Grafik</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="#"><i class="fas fa-sync-alt me-2 text-primary"></i> Perbarui Data</a></li>
                        </ul>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-area">
                        <canvas id="salesChart" style="height: 300px;"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 fw-bold text-primary">Produk Terlaris</h6>
                    <div class="dropdown no-arrow">
                        <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink2" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-ellipsis-v text-gray-400"></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="dropdownMenuLink2">
                            <li><a class="dropdown-item" href="#"><i class="fas fa-eye me-2 text-primary"></i> Lihat Detail</a></li>
                            <li><a class="dropdown-item" href="#"><i class="fas fa-download me-2 text-primary"></i> Unduh Laporan</a></li>
                        </ul>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-pie py-3">
                        <canvas id="topProductsChart"></canvas>
                    </div>
                    <div class="mt-4 text-center small">
                        <span class="me-2">
                            <i class="fas fa-circle text-primary"></i> Produk 1
                        </span>
                        <span class="me-2">
                            <i class="fas fa-circle text-success"></i> Produk 2
                        </span>
                        <span class="me-2">
                            <i class="fas fa-circle text-info"></i> Produk 3
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 fw-bold text-primary">Transaksi Terbaru</h6>
                    <a href="#" class="btn btn-sm btn-primary">
                        <i class="fas fa-list me-1"></i> Lihat Semua
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" width="100%" cellspacing="0">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Tanggal</th>
                                    <th>Tipe</th>
                                    <th>Jumlah</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(isset($recentTransactions) && count($recentTransactions) > 0)
                                    @foreach($recentTransactions as $transaction)
                                    <tr>
                                        <td>{{ $transaction->id }}</td>
                                        <td>{{ $transaction->date->format('d/m/Y') }}</td>
                                        <td>
                                            @if($transaction->type == 'sale')
                                                <span class="badge bg-primary bg-opacity-10 text-primary">Penjualan</span>
                                            @elseif($transaction->type == 'purchase')
                                                <span class="badge bg-success bg-opacity-10 text-success">Pembelian</span>
                                            @else
                                                <span class="badge bg-secondary bg-opacity-10 text-secondary">Lainnya</span>
                                            @endif
                                        </td>
                                        <td>{{ number_format($transaction->amount, 0, ',', '.') }}</td>
                                        <td>
                                            @if($transaction->status == 'completed' || $transaction->status == 'paid')
                                                <span class="badge bg-success bg-opacity-10 text-success">Selesai</span>
                                            @elseif($transaction->status == 'pending')
                                                <span class="badge bg-warning bg-opacity-10 text-warning">Tertunda</span>
                                            @else
                                                <span class="badge bg-danger bg-opacity-10 text-danger">Gagal</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="tooltip" title="Lihat Detail">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-success" data-bs-toggle="tooltip" title="Cetak">
                                                    <i class="fas fa-print"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="6" class="text-center">Tidak ada transaksi ditemukan</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-white">
                    <a href="#" class="text-primary fw-bold">Lihat semua transaksi <i class="fas fa-arrow-right ms-1"></i></a>
                </div>
            </div>
        </div>
    </div>

    @canany(['view store orders', 'view shipments'])
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 fw-bold text-primary">Pesanan Toko Terbaru</h6>
                    @if(Auth::user()->hasRole('admin_store'))
                    <a href="{{ route('store.orders.index') }}" class="btn btn-sm btn-primary">
                    @else
                    <a href="{{ route('store-orders.index') }}" class="btn btn-sm btn-primary">
                    @endif
                        <i class="fas fa-list me-1"></i> Lihat Semua
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" width="100%" cellspacing="0">
                            <thead class="table-light">
                                <tr>
                                    <th>No. Pesanan</th>
                                    <th>Toko</th>
                                    <th>Tanggal</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $recentStoreOrders = App\Models\StoreOrder::with('store')
                                        ->orderBy('created_at', 'desc')
                                        ->take(5)
                                        ->get();
                                @endphp

                                @if(count($recentStoreOrders) > 0)
                                    @foreach($recentStoreOrders as $order)
                                    <tr>
                                        <td>{{ $order->order_number }}</td>
                                        <td>{{ $order->store->name }}</td>
                                        <td>{{ $order->date->format('d/m/Y') }}</td>
                                        <td>
                                            @if($order->status == 'pending')
                                                <span class="badge bg-warning bg-opacity-10 text-warning">Menunggu</span>
                                            @elseif($order->status == 'confirmed_by_admin')
                                                <span class="badge bg-info bg-opacity-10 text-info">Dikonfirmasi</span>
                                            @elseif($order->status == 'forwarded_to_warehouse')
                                                <span class="badge bg-primary bg-opacity-10 text-primary">Diteruskan</span>
                                            @elseif($order->status == 'shipped')
                                                <span class="badge bg-info bg-opacity-10 text-info">Dikirim</span>
                                            @elseif($order->status == 'completed')
                                                <span class="badge bg-success bg-opacity-10 text-success">Selesai</span>
                                            @else
                                                <span class="badge bg-secondary bg-opacity-10 text-secondary">{{ ucfirst($order->status) }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                @if(Auth::user()->hasRole('admin_store'))
                                                <a href="{{ route('store.orders.show', $order->id) }}" class="btn btn-sm btn-outline-primary" data-bs-toggle="tooltip" title="Lihat Detail">
                                                @else
                                                <a href="{{ route('store-orders.show', $order->id) }}" class="btn btn-sm btn-outline-primary" data-bs-toggle="tooltip" title="Lihat Detail">
                                                @endif
                                                    <i class="fas fa-eye"></i>
                                                </a>

                                                @if(Auth::user()->hasRole(['owner', 'admin_back_office']) && $order->status == 'pending')
                                                <form action="{{ route('store-orders.confirm', $order->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-outline-success" data-bs-toggle="tooltip" title="Konfirmasi">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                </form>
                                                @endif

                                                @if(Auth::user()->hasRole('admin_gudang') && $order->status == 'forwarded_to_warehouse')
                                                <a href="{{ route('warehouse.store-orders.shipment.create', $order->id) }}" class="btn btn-sm btn-outline-info" data-bs-toggle="tooltip" title="Buat Pengiriman">
                                                    <i class="fas fa-truck"></i>
                                                </a>
                                                @endif

                                                @if(Auth::user()->hasRole('admin_store') && $order->status == 'shipped')
                                                <form action="{{ route('store.orders.confirm-delivery', $order->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-outline-success" data-bs-toggle="tooltip" title="Konfirmasi Penerimaan">
                                                        <i class="fas fa-check-double"></i>
                                                    </button>
                                                </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="5" class="text-center">Tidak ada pesanan toko ditemukan</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-white">
                    @if(Auth::user()->hasRole('admin_store'))
                    <a href="{{ route('store.orders.index') }}" class="text-primary fw-bold">Lihat semua pesanan <i class="fas fa-arrow-right ms-1"></i></a>
                    @else
                    <a href="{{ route('store-orders.index') }}" class="text-primary fw-bold">Lihat semua pesanan <i class="fas fa-arrow-right ms-1"></i></a>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endcanany
</div>

<!-- Filter Modal -->
<div class="modal fade" id="filterModal" tabindex="-1" aria-labelledby="filterModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="filterModalLabel">Filter Data</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form>
                    <div class="mb-3">
                        <label for="dateRange" class="form-label">Rentang Tanggal</label>
                        <select class="form-select" id="dateRange">
                            <option value="today">Hari Ini</option>
                            <option value="yesterday">Kemarin</option>
                            <option value="last7days">7 Hari Terakhir</option>
                            <option value="last30days" selected>30 Hari Terakhir</option>
                            <option value="thisMonth">Bulan Ini</option>
                            <option value="lastMonth">Bulan Lalu</option>
                            <option value="custom">Kustom</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="category" class="form-label">Kategori</label>
                        <select class="form-select" id="category">
                            <option value="all" selected>Semua Kategori</option>
                            <option value="electronics">Elektronik</option>
                            <option value="clothing">Pakaian</option>
                            <option value="food">Makanan</option>
                            <option value="beverages">Minuman</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status">
                            <option value="all" selected>Semua Status</option>
                            <option value="completed">Selesai</option>
                            <option value="pending">Tertunda</option>
                            <option value="failed">Gagal</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary">Terapkan Filter</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Sales Chart
    var ctx = document.getElementById("salesChart");
    var salesChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: {!! isset($salesChartData) ? json_encode($salesChartData['labels']) : json_encode(['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des']) !!},
            datasets: [{
                label: "Penjualan",
                lineTension: 0.3,
                backgroundColor: "rgba(37, 99, 235, 0.05)",
                borderColor: "rgba(37, 99, 235, 1)",
                pointRadius: 3,
                pointBackgroundColor: "rgba(37, 99, 235, 1)",
                pointBorderColor: "rgba(37, 99, 235, 1)",
                pointHoverRadius: 3,
                pointHoverBackgroundColor: "rgba(37, 99, 235, 1)",
                pointHoverBorderColor: "rgba(37, 99, 235, 1)",
                pointHitRadius: 10,
                pointBorderWidth: 2,
                data: {!! isset($salesChartData) ? json_encode($salesChartData['data']) : json_encode([5000000, 8000000, 12000000, 10000000, 15000000, 14000000, 16000000, 18000000, 21000000, 22000000, 25000000, 30000000]) !!},
            }],
        },
        options: {
            maintainAspectRatio: false,
            layout: {
                padding: {
                    left: 10,
                    right: 25,
                    top: 25,
                    bottom: 0
                }
            },
            scales: {
                x: {
                    grid: {
                        display: false,
                        drawBorder: false
                    },
                    ticks: {
                        maxTicksLimit: 12
                    }
                },
                y: {
                    ticks: {
                        maxTicksLimit: 5,
                        padding: 10,
                        callback: function(value, index, values) {
                            return 'Rp ' + value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                        }
                    },
                    grid: {
                        color: "rgb(234, 236, 244)",
                        zeroLineColor: "rgb(234, 236, 244)",
                        drawBorder: false,
                        borderDash: [2],
                        zeroLineBorderDash: [2]
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: "rgb(255,255,255)",
                    bodyColor: "#858796",
                    titleMarginBottom: 10,
                    titleColor: '#6e707e',
                    titleFontSize: 14,
                    borderColor: '#dddfeb',
                    borderWidth: 1,
                    xPadding: 15,
                    yPadding: 15,
                    displayColors: false,
                    intersect: false,
                    mode: 'index',
                    caretPadding: 10,
                    callbacks: {
                        label: function(context) {
                            var label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.parsed.y !== null) {
                                label += 'Rp ' + context.parsed.y.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                            }
                            return label;
                        }
                    }
                }
            }
        }
    });

    // Top Products Chart
    var ctx2 = document.getElementById("topProductsChart");
    var topProductsChart = new Chart(ctx2, {
        type: 'doughnut',
        data: {
            labels: {!! isset($topProductsData) ? json_encode($topProductsData['labels']) : json_encode(['Produk A', 'Produk B', 'Produk C', 'Produk D', 'Produk E']) !!},
            datasets: [{
                data: {!! isset($topProductsData) ? json_encode($topProductsData['data']) : json_encode([35, 25, 20, 15, 5]) !!},
                backgroundColor: ['#2563eb', '#10b981', '#3b82f6', '#f59e0b', '#ef4444'],
                hoverBackgroundColor: ['#1d4ed8', '#059669', '#2563eb', '#d97706', '#dc2626'],
                hoverBorderColor: "rgba(234, 236, 244, 1)",
            }],
        },
        options: {
            maintainAspectRatio: false,
            responsive: true,
            cutout: '70%',
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: "rgb(255,255,255)",
                    bodyColor: "#858796",
                    borderColor: '#dddfeb',
                    borderWidth: 1,
                    xPadding: 15,
                    yPadding: 15,
                    displayColors: false,
                    caretPadding: 10,
                }
            }
        }
    });

    // Refresh data button functionality
    document.getElementById('refreshData').addEventListener('click', function() {
        // Tampilkan loading spinner
        Swal.fire({
            title: 'Memperbarui data...',
            html: 'Mohon tunggu sebentar',
            timer: 1000,
            timerProgressBar: true,
            didOpen: () => {
                Swal.showLoading();
            }
        }).then((result) => {
            // Reload halaman setelah loading selesai
            if (result.dismiss === Swal.DismissReason.timer) {
                window.location.reload();
            }
        });
    });
</script>
@endsection
