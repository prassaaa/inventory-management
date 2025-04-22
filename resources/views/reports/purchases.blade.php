@extends('layouts.app')

@section('title', 'Laporan Pembelian')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-dark fw-bold">
                <i class="fas fa-shopping-cart me-2 text-primary"></i> Laporan Pembelian
            </h1>
            <p class="text-muted">Analisis data pembelian dalam periode tertentu</p>
        </div>
        <div>
            <a href="{{ route('reports.purchases.export', request()->query()) }}" class="btn btn-success">
                <i class="fas fa-file-excel me-1"></i> Export Excel
            </a>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 fw-bold text-primary">Filter</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('reports.purchases') }}" method="GET">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group mb-3">
                            <label for="start_date" class="form-label">Tanggal Mulai</label>
                            <input type="date" class="form-control" id="start_date" name="start_date" value="{{ request('start_date', now()->startOfMonth()->format('Y-m-d')) }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group mb-3">
                            <label for="end_date" class="form-label">Tanggal Selesai</label>
                            <input type="date" class="form-control" id="end_date" name="end_date" value="{{ request('end_date', now()->format('Y-m-d')) }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group mb-3">
                            <label for="supplier_id" class="form-label">Supplier</label>
                            <select class="form-select" id="supplier_id" name="supplier_id">
                                <option value="">Semua Supplier</option>
                                @foreach($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}" {{ request('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                        {{ $supplier->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="">Semua Status</option>
                                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="complete" {{ request('status') === 'complete' ? 'selected' : '' }}>Selesai</option>
                                <option value="partial" {{ request('status') === 'partial' ? 'selected' : '' }}>Sebagian</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter me-1"></i> Filter
                    </button>
                    <a href="{{ route('reports.purchases') }}" class="btn btn-secondary">
                        <i class="fas fa-sync me-1"></i> Reset
                    </a>
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
                                Total Pembelian</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">Rp {{ number_format($total_purchases, 0, ',', '.') }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-shopping-cart fa-2x text-gray-300"></i>
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
                            <i class="fas fa-file-invoice fa-2x text-gray-300"></i>
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
                                Rata-rata Pembelian</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">Rp {{ $total_transactions > 0 ? number_format($total_purchases / $total_transactions, 0, ',', '.') : 0 }}</div>
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
                    <h6 class="m-0 fw-bold text-primary">Tren Pembelian</h6>
                </div>
                <div class="card-body">
                    <div class="chart-area">
                        <canvas id="purchasesChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 fw-bold text-primary">Status Pembelian</h6>
                </div>
                <div class="card-body">
                    <div class="chart-pie pt-4 pb-2">
                        <canvas id="statusChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 fw-bold text-primary">Detail Pembelian</h6>
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
                <table class="table table-hover" id="purchasesTable" width="100%" cellspacing="0">
                    <thead class="table-light">
                        <tr>
                            <th>Tanggal</th>
                            <th>Invoice #</th>
                            <th>Supplier</th>
                            <th>Status</th>
                            <th>Pembayaran</th>
                            <th>Jumlah</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($purchases as $purchase)
                        <tr>
                            <td>{{ $purchase->date->format('d/m/Y') }}</td>
                            <td><span class="fw-medium">{{ $purchase->invoice_number }}</span></td>
                            <td>{{ $purchase->supplier->name }}</td>
                            <td>
                                <span class="badge {{ $purchase->status === 'pending' ? 'bg-warning-light text-warning' : '' }}
                                                {{ $purchase->status === 'complete' ? 'bg-success-light text-success' : '' }}
                                                {{ $purchase->status === 'partial' ? 'bg-info-light text-info' : '' }} rounded-pill px-2">
                                    {{ $purchase->status === 'pending' ? 'Pending' : 
                                      ($purchase->status === 'complete' ? 'Selesai' : 'Sebagian') }}
                                </span>
                            </td>
                            <td>
                                <span class="badge {{ $purchase->payment_type === 'tunai' ? 'bg-success-light text-success' : 'bg-warning-light text-warning' }} rounded-pill px-2">
                                    {{ $purchase->payment_type === 'tunai' ? 'Tunai' : 'Tempo' }}
                                </span>
                            </td>
                            <td>Rp {{ number_format($purchase->total_amount, 0, ',', '.') }}</td>
                            <td>
                                <a href="{{ route('purchases.show', $purchase) }}" class="btn btn-sm btn-outline-info" data-bs-toggle="tooltip" title="Lihat Detail">
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
            <h6 class="m-0 fw-bold text-primary">Produk Terbanyak Dibeli</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="topProductsTable" width="100%" cellspacing="0">
                    <thead class="table-light">
                        <tr>
                            <th>Produk</th>
                            <th>Kategori</th>
                            <th>Jumlah Dibeli</th>
                            <th>Total Pembelian</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($top_products as $product)
                        <tr>
                            <td>
                                <a href="{{ route('products.show', $product->product) }}">
                                    <span class="fw-medium">{{ $product->product->code }}</span> - {{ $product->product->name }}
                                </a>
                            </td>
                            <td>
                                <span class="badge bg-primary-light text-primary rounded-pill px-2">
                                    {{ $product->product->category->name }}
                                </span>
                            </td>
                            <td>{{ $product->total_quantity }} {{ $product->product->baseUnit->name }}</td>
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
        if ($.fn.dataTable.isDataTable('#purchasesTable')) {
            $('#purchasesTable').DataTable().destroy();
        }
        
        // Inisialisasi DataTable baru
        var purchasesTable = $('#purchasesTable').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json'
            },
            responsive: true,
            dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>t<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
            pageLength: 10,
            order: [[0, 'desc']],
            destroy: true // Pastikan opsi destroy diaktifkan
        });
        
        // Custom search untuk tabel pembelian
        $('#customSearch').keyup(function() {
            purchasesTable.search($(this).val()).draw();
        });
        
        // Inisialisasi tabel produk terbanyak dibeli
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
        
        // Purchases Chart
        var ctx = document.getElementById("purchasesChart");
        var purchasesChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: {!! json_encode($chart_data['labels']) !!},
                datasets: [{
                    label: "Pembelian",
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
        
        // Status Chart
        var ctx2 = document.getElementById("statusChart");
        var statusChart = new Chart(ctx2, {
            type: 'doughnut',
            data: {
                labels: ["Selesai", "Pending", "Sebagian"],
                datasets: [{
                    data: [
                        {{ $status_data['complete'] ?? 0 }},
                        {{ $status_data['pending'] ?? 0 }},
                        {{ $status_data['partial'] ?? 0 }}
                    ],
                    backgroundColor: ['#1cc88a', '#f6c23e', '#36b9cc'],
                    hoverBackgroundColor: ['#17a673', '#dda20a', '#2c9faf'],
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
    });
</script>
@endsection