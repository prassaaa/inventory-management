@extends('layouts.app')

@section('title', 'Laporan Penjualan Per Toko')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-dark fw-bold">
                <i class="fas fa-store me-2 text-primary"></i> Laporan Penjualan Per Toko
            </h1>
            <p class="text-muted">Analisis performa penjualan toko berdasarkan omzet (urutan terbesar ke terkecil)</p>
        </div>
        <div>
            <a href="{{ route('reports.sales-by-store.export', request()->query()) }}" class="btn btn-success">
                <i class="fas fa-file-excel me-1"></i> Export Excel
            </a>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 fw-bold text-primary">Filter</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('reports.sales-by-store') }}" method="GET">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label for="start_date" class="form-label">Tanggal Mulai</label>
                            <input type="date" class="form-control" id="start_date" name="start_date" value="{{ request('start_date', $startDate) }}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label for="end_date" class="form-label">Tanggal Selesai</label>
                            <input type="date" class="form-control" id="end_date" name="end_date" value="{{ request('end_date', $endDate) }}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group mb-3 mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-filter me-1"></i> Filter
                            </button>
                            <a href="{{ route('reports.sales-by-store') }}" class="btn btn-secondary">
                                <i class="fas fa-sync me-1"></i> Reset
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-6 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Omzet Semua Toko</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">Rp {{ number_format($totalOmzet, 0, ',', '.') }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-cash-register fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-6 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Total Transaksi Semua Toko</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($totalTransactions, 0, ',', '.') }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-receipt fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-12 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 fw-bold text-primary">Perbandingan Omzet Toko</h6>
                </div>
                <div class="card-body">
                    <div class="chart-bar">
                        <canvas id="storeOmzetChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 fw-bold text-primary">Peringkat Toko Berdasarkan Omzet</h6>
            <div class="d-flex align-items-center">
                <div class="input-group">
                    <span class="input-group-text bg-light border-0">
                        <i class="fas fa-search text-primary"></i>
                    </span>
                    <input type="text" id="customSearch" class="form-control border-0 bg-light" placeholder="Cari toko...">
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="storesTable" width="100%" cellspacing="0">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center">Peringkat</th>
                            <th>Nama Toko</th>
                            <th class="text-center">Jumlah Transaksi</th>
                            <th class="text-end">Total Omzet</th>
                            <th class="text-end">Rata-rata per Transaksi</th>
                            <th class="text-center">Persentase Kontribusi</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($storesSales as $index => $store)
                        <tr>
                            <td class="text-center"><span class="badge bg-primary rounded-pill px-2">{{ $index + 1 }}</span></td>
                            <td><span class="fw-medium">{{ $store->store_name }}</span></td>
                            <td class="text-center">{{ number_format($store->total_transactions, 0, ',', '.') }}</td>
                            <td class="text-end">Rp {{ number_format($store->total_omzet, 0, ',', '.') }}</td>
                            <td class="text-end">Rp {{ number_format($store->total_transactions > 0 ? $store->total_omzet / $store->total_transactions : 0, 0, ',', '.') }}</td>
                            <td class="text-center">
                                @php
                                    $percentage = $totalOmzet > 0 ? ($store->total_omzet / $totalOmzet) * 100 : 0;
                                @endphp
                                <div class="progress" style="height: 20px;">
                                    <div class="progress-bar bg-success" role="progressbar" style="width: {{ $percentage }}%;"
                                        aria-valuenow="{{ $percentage }}" aria-valuemin="0" aria-valuemax="100">
                                        {{ number_format($percentage, 1) }}%
                                    </div>
                                </div>
                            </td>
                            <td class="text-center">
                                <a href="{{ route('reports.sales', ['store_id' => $store->store_id]) }}" class="btn btn-sm btn-outline-info" data-bs-toggle="tooltip" title="Detail Penjualan">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('stores.show', $store->store_id) }}" class="btn btn-sm btn-outline-primary" data-bs-toggle="tooltip" title="Profil Toko">
                                    <i class="fas fa-store"></i>
                                </a>
                            </td>
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
        // Inisialisasi DataTable
        var storesTable = $('#storesTable').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json'
            },
            responsive: true,
            searching: true,
            paging: true,
            info: true,
            ordering: false, // Tidak perlu ordering karena sudah diurutkan dari controller
            dom: '<"row"<"col-sm-12">>t<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
        });

        // Custom search untuk tabel stores
        $('#customSearch').keyup(function() {
            storesTable.search($(this).val()).draw();
        });

        // Chart untuk perbandingan omzet toko
        var ctx = document.getElementById("storeOmzetChart");
        var storeOmzetChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: {!! json_encode($chart_data['labels']) !!},
                datasets: [{
                    label: "Total Omzet",
                    backgroundColor: "rgba(78, 115, 223, 0.9)",
                    hoverBackgroundColor: "rgba(78, 115, 223, 1)",
                    borderColor: "rgba(78, 115, 223, 1)",
                    data: {!! json_encode($chart_data['values']) !!},
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
                        }
                    },
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
