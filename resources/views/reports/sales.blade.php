@extends('layouts.app')

@section('title', 'Laporan Penjualan')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-dark fw-bold">
                <i class="fas fa-chart-line me-2 text-primary"></i> Laporan Penjualan
            </h1>
            <p class="text-muted">Analisis data penjualan dalam periode tertentu</p>
        </div>
        <div>
            <a href="{{ route('reports.sales.export', request()->query()) }}" class="btn btn-success">
                <i class="fas fa-file-excel me-1"></i> Export Excel
            </a>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 fw-bold text-primary">Filter</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('reports.sales') }}" method="GET">
                <div class="row">
                    <div class="col-md-{{ $canSelectStore ? '3' : '4' }}">
                        <div class="form-group mb-3">
                            <label for="start_date" class="form-label">Tanggal Mulai</label>
                            <input type="date" class="form-control" id="start_date" name="start_date" value="{{ request('start_date', now()->startOfMonth()->format('Y-m-d')) }}">
                        </div>
                    </div>
                    <div class="col-md-{{ $canSelectStore ? '3' : '4' }}">
                        <div class="form-group mb-3">
                            <label for="end_date" class="form-label">Tanggal Selesai</label>
                            <input type="date" class="form-control" id="end_date" name="end_date" value="{{ request('end_date', now()->format('Y-m-d')) }}">
                        </div>
                    </div>

                    @if($canSelectStore)
                    <div class="col-md-3">
                        <div class="form-group mb-3">
                            <label for="store_id" class="form-label">Toko</label>
                            <select class="form-select" id="store_id" name="store_id">
                                <option value="">Semua Toko</option>
                                @foreach($stores as $store)
                                    <option value="{{ $store->id }}" {{ request('store_id') == $store->id ? 'selected' : '' }}>
                                        {{ $store->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    @else
                        <!-- Jika user terkait toko tertentu, tambahkan hidden input -->
                        <input type="hidden" name="store_id" id="store_id" value="{{ $userStoreId }}">
                    @endif

                    <div class="col-md-{{ $canSelectStore ? '3' : '4' }}">
                        <div class="form-group mb-3">
                            <label for="payment_type" class="form-label">Metode Pembayaran</label>
                            <select class="form-select" id="payment_type" name="payment_type">
                                <option value="">Semua Metode</option>
                                <option value="tunai" {{ request('payment_type') === 'tunai' ? 'selected' : '' }}>Tunai</option>
                                <option value="kartu" {{ request('payment_type') === 'kartu' ? 'selected' : '' }}>Kartu</option>
                                <option value="tempo" {{ request('payment_type') === 'tempo' ? 'selected' : '' }}>Kredit</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter me-1"></i> Filter
                    </button>
                    <a href="{{ route('reports.sales') }}" class="btn btn-secondary">
                        <i class="fas fa-sync me-1"></i> Reset
                    </a>

                    @if($userStoreId || request('store_id'))
                        <button type="button" id="printReceiptBtn" class="btn btn-info ms-2 text-white">
                            <i class="fas fa-print me-1 text-white"></i> Print Struk Kasir
                        </button>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Penjualan</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">Rp {{ number_format($total_sales, 0, ',', '.') }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-cash-register fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Total Transaksi</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $total_transactions }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-receipt fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Rata-rata Penjualan</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">Rp {{ $total_transactions > 0 ? number_format($total_sales / $total_transactions, 0, ',', '.') : 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calculator fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 fw-bold text-primary">Tren Penjualan</h6>
                </div>
                <div class="card-body">
                    <div class="chart-area">
                        <canvas id="salesChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 fw-bold text-primary">Metode Pembayaran</h6>
                </div>
                <div class="card-body">
                    <div class="chart-pie pt-4 pb-2">
                        <canvas id="paymentMethodsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 fw-bold text-primary">Detail Penjualan</h6>
            <div class="d-flex align-items-center">
                <div class="input-group">
                    <span class="input-group-text bg-light border-0">
                        <i class="fas fa-search text-primary"></i>
                    </span>
                    <input type="text" id="customSearch" class="form-control border-0 bg-light" placeholder="Cari transaksi...">
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="salesTable" width="100%" cellspacing="0">
                    <thead class="table-light">
                        <tr>
                            <th>Tanggal</th>
                            <th>Invoice #</th>
                            <th>Toko</th>
                            <th>Pelanggan</th>
                            <th>Pembayaran</th>
                            <th>Jumlah</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sales as $sale)
                        <tr>
                            <td>{{ $sale->date->format('d/m/Y') }}</td>
                            <td><span class="fw-medium">{{ $sale->invoice_number }}</span></td>
                            <td>{{ $sale->store->name }}</td>
                            <td>{{ $sale->customer_name ?? 'Pelanggan Umum' }}</td>
                            <td>
                                <span class="badge {{ $sale->payment_type === 'tunai' ? 'bg-success-light text-success' : '' }}
                                                {{ $sale->payment_type === 'kartu' ? 'bg-info-light text-info' : '' }}
                                                {{ $sale->payment_type === 'tempo' ? 'bg-warning-light text-warning' : '' }} rounded-pill px-2">
                                    {{ ucfirst($sale->payment_type) }}
                                </span>
                            </td>
                            <td>Rp {{ number_format($sale->total_amount, 0, ',', '.') }}</td>
                            <td>
                                <a href="{{ route('sales.show', $sale) }}" class="btn btn-sm btn-outline-info" data-bs-toggle="tooltip" title="Lihat Detail">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 fw-bold text-primary">Produk Terlaris</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="topProductsTable" width="100%" cellspacing="0">
                    <thead class="table-light">
                        <tr>
                            <th>Produk</th>
                            <th>Kategori</th>
                            <th>Jumlah Terjual</th>
                            <th>Total Penjualan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($top_products as $product)
                        <tr>
                            <td>
                                @if($product->product)
                                    @if($product->product->deleted_at)
                                        <span class="fw-medium">{{ $product->product->code }}</span> -
                                        {{ $product->product->name }}
                                        <span class="badge bg-danger text-white">terhapus</span>
                                    @else
                                        <a href="{{ route('products.show', ['product' => $product->product->id]) }}">
                                            <span class="fw-medium">{{ $product->product->code }}</span> - {{ $product->product->name }}
                                        </a>
                                    @endif
                                @else
                                    <span class="text-muted">Produk tidak tersedia</span>
                                @endif
                            </td>
                            <td>
                                @if($product->product && $product->product->category)
                                    <span class="badge bg-primary-light text-primary rounded-pill px-2">
                                        {{ $product->product->category->name }}
                                    </span>
                                @else
                                    <span class="badge bg-secondary rounded-pill px-2">
                                        Tidak Terkategori
                                    </span>
                                @endif
                            </td>
                            <td>
                                {{ intval($product->total_quantity) }}
                                @if($product->product && isset($product->product->baseUnit) && $product->product->baseUnit)
                                    {{ $product->product->baseUnit->name }}
                                @else
                                    unit
                                @endif
                            </td>
                            <td>Rp {{ number_format($product->total_amount, 0, ',', '.') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Cek jika tabel sudah diinisialisasi sebelumnya
        if ($.fn.dataTable.isDataTable('#salesTable')) {
            $('#salesTable').DataTable().destroy();
        }

        // Inisialisasi DataTable baru
        var salesTable = $('#salesTable').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json'
            },
            responsive: true,
            dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>t<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
            pageLength: 10,
            order: [[0, 'desc']],
            destroy: true // Pastikan opsi destroy diaktifkan
        });

        // Custom search untuk tabel penjualan
        $('#customSearch').keyup(function() {
            salesTable.search($(this).val()).draw();
        });

        // Inisialisasi tabel produk terlaris
        $('#topProductsTable').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json'
            },
            paging: false,
            searching: false,
            info: false,
            responsive: true,
            destroy: true
        });

        // Sales Chart
        var ctx = document.getElementById("salesChart");
        var salesChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: {!! json_encode($chart_data['labels']) !!},
                datasets: [{
                    label: "Penjualan",
                    lineTension: 0.3,
                    backgroundColor: "rgba(78, 115, 223, 0.05)",
                    borderColor: "rgba(78, 115, 223, 1)",
                    pointRadius: 3,
                    pointBackgroundColor: "rgba(78, 115, 223, 1)",
                    pointBorderColor: "rgba(78, 115, 223, 1)",
                    pointHoverRadius: 3,
                    pointHoverBackgroundColor: "rgba(78, 115, 223, 1)",
                    pointHoverBorderColor: "rgba(78, 115, 223, 1)",
                    pointHitRadius: 10,
                    pointBorderWidth: 2,
                    data: {!! json_encode($chart_data['values']) !!},
                }],
            },
            options: {
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'Rp ' + value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                            }
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                label += 'Rp ' + context.parsed.y.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                                return label;
                            }
                        }
                    }
                }
            }
        });

        // Payment Methods Chart
        var ctx2 = document.getElementById("paymentMethodsChart");
        var paymentMethodsChart = new Chart(ctx2, {
            type: 'doughnut',
            data: {
                labels: ["Tunai", "Kartu", "Kredit"],
                datasets: [{
                    data: [
                        {{ $payment_methods['tunai'] ?? 0 }},
                        {{ $payment_methods['kartu'] ?? 0 }},
                        {{ $payment_methods['tempo'] ?? 0 }}
                    ],
                    backgroundColor: ['#1cc88a', '#36b9cc', '#f6c23e'],
                    hoverBackgroundColor: ['#17a673', '#2c9faf', '#dda20a'],
                    hoverBorderColor: "rgba(234, 236, 244, 1)",
                }],
            },
            options: {
                maintainAspectRatio: false,
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Initialize tooltips
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl, {
                trigger: 'hover'
            });
        });

        // Handle print receipt button
        $('#printReceiptBtn').on('click', function() {
            var storeId = $('#store_id').val();
            var date = $('#end_date').val(); // Gunakan tanggal akhir sebagai tanggal laporan

            // Gunakan URL dari route baru
            var printUrl = "/print-daily-sales?store_id=" + storeId + "&date=" + date;

            // Buka jendela baru dalam ukuran penuh (tanpa parameter size)
            window.open(printUrl, '_blank');
        });
    });
</script>
@endsection
