@extends('layouts.app')

@section('title', 'Laporan Laba Rugi')

@section('content')
<div class="container-fluid">
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-dark fw-bold">
                <i class="fas fa-chart-pie me-2 text-primary"></i> Laporan Laba Rugi
                @if(isset($selectedStore))
                    <span class="text-primary"> - {{ $selectedStore->name }}</span>
                @elseif(!$canSelectStore && isset($stores) && $stores->count() > 0)
                    <span class="text-primary"> - {{ $stores->first()->name }}</span>
                @endif
            </h1>
            <p class="text-muted">Analisis pendapatan, biaya, dan profitabilitas bisnis</p>
        </div>
        <div>
            <a href="{{ route('reports.profit-loss.export', request()->query()) }}" class="btn btn-success">
                <i class="fas fa-file-excel me-1"></i> Export Excel
            </a>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 fw-bold text-primary">
                <i class="fas fa-filter me-1"></i> Filter Laporan
            </h6>
        </div>
        <div class="card-body">
            <form action="{{ route('reports.profit-loss') }}" method="GET">
                <div class="row">
                    <div class="col-md-{{ $canSelectStore ? '4' : '6' }}">
                        <div class="form-group mb-3">
                            <label for="start_date" class="form-label">Tanggal Mulai</label>
                            <input type="date" class="form-control" id="start_date" name="start_date" value="{{ request('start_date', now()->startOfMonth()->format('Y-m-d')) }}">
                        </div>
                    </div>
                    <div class="col-md-{{ $canSelectStore ? '4' : '6' }}">
                        <div class="form-group mb-3">
                            <label for="end_date" class="form-label">Tanggal Akhir</label>
                            <input type="date" class="form-control" id="end_date" name="end_date" value="{{ request('end_date', now()->format('Y-m-d')) }}">
                        </div>
                    </div>
                    @if($canSelectStore)
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label for="store_id" class="form-label">Lokasi</label>
                            <select class="form-select" id="store_id" name="store_id">
                                <option value="">Kantor Pusat (Semua Lokasi)</option>
                                @foreach($stores as $store)
                                    <option value="{{ $store->id }}" {{ request('store_id') == $store->id ? 'selected' : '' }}>
                                        {{ $store->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    @endif
                </div>

                <div class="form-group mt-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter me-1"></i> Terapkan Filter
                    </button>
                    <a href="{{ route('reports.profit-loss') }}" class="btn btn-secondary ms-2">
                        <i class="fas fa-sync me-1"></i> Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Pendapatan</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">Rp {{ number_format($totalSales, 0, ',', '.') }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-cash-register fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Laba Kotor</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">Rp {{ number_format($grossProfit, 0, ',', '.') }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chart-line fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Total Pengeluaran</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">Rp {{ number_format($expenses, 0, ',', '.') }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card {{ $netProfit >= 0 ? 'border-left-info' : 'border-left-danger' }} shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold {{ $netProfit >= 0 ? 'text-info' : 'text-danger' }} text-uppercase mb-1">
                                {{ $netProfit >= 0 ? 'Laba' : 'Rugi' }} Bersih</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">Rp {{ number_format(abs($netProfit), 0, ',', '.') }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas {{ $netProfit >= 0 ? 'fa-balance-scale' : 'fa-exclamation-triangle' }} fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Profit Loss Statement -->
    <div class="row">
        <div class="col-xl-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 fw-bold text-primary">
                        <i class="fas fa-file-invoice-dollar me-1"></i> Laporan Laba Rugi
                        @if(isset($selectedStore))
                            - {{ $selectedStore->name }}
                        @elseif(!$canSelectStore && isset($stores) && $stores->count() > 0)
                            - {{ $stores->first()->name }}
                        @else
                            - Kantor Pusat (Semua Lokasi)
                        @endif
                    </h6>
                    <div class="dropdown no-arrow">
                        <span class="text-xs font-weight-bold text-primary">
                            Periode: {{ \Carbon\Carbon::parse(request('start_date', now()->startOfMonth()->format('Y-m-d')))->format('d M Y') }} -
                            {{ \Carbon\Carbon::parse(request('end_date', now()->format('Y-m-d')))->format('d M Y') }}
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <tbody>
                                <!-- Revenue Section -->
                                <tr class="bg-light">
                                    <th colspan="2"><strong>PENDAPATAN</strong></th>
                                </tr>
                                <tr>
                                    <td style="padding-left: 20px;">Pendapatan Penjualan</td>
                                    <td class="text-end">Rp {{ number_format($totalSales, 0, ',', '.') }}</td>
                                </tr>
                                <tr class="table-active">
                                    <td><strong>Total Pendapatan</strong></td>
                                    <td class="text-end"><strong>Rp {{ number_format($totalSales, 0, ',', '.') }}</strong></td>
                                </tr>

                                <!-- COGS Section -->
                                <tr class="bg-light">
                                    <th colspan="2"><strong>HARGA POKOK PENJUALAN</strong></th>
                                </tr>
                                <tr>
                                    <td style="padding-left: 20px;">Harga Pokok Penjualan</td>
                                    <td class="text-end">Rp {{ number_format($cogs, 0, ',', '.') }}</td>
                                </tr>
                                <tr class="table-active">
                                    <td><strong>Total Harga Pokok Penjualan</strong></td>
                                    <td class="text-end"><strong>Rp {{ number_format($cogs, 0, ',', '.') }}</strong></td>
                                </tr>

                                <!-- Gross Profit -->
                                <tr class="bg-success text-white">
                                    <th><strong>LABA KOTOR</strong></th>
                                    <th class="text-end"><strong>Rp {{ number_format($grossProfit, 0, ',', '.') }}</strong></th>
                                </tr>

                                <!-- Expenses Section -->
                                <tr class="bg-light">
                                    <th colspan="2"><strong>PENGELUARAN</strong></th>
                                </tr>
                                @forelse($expenseBreakdown as $expense)
                                <tr>
                                    <td style="padding-left: 20px;">{{ ucfirst($expense->category) }}</td>
                                    <td class="text-end">Rp {{ number_format($expense->total, 0, ',', '.') }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td style="padding-left: 20px;" colspan="2" class="text-center">Tidak ada data pengeluaran</td>
                                </tr>
                                @endforelse
                                <tr class="table-active">
                                    <td><strong>Total Pengeluaran</strong></td>
                                    <td class="text-end"><strong>Rp {{ number_format($expenses, 0, ',', '.') }}</strong></td>
                                </tr>

                                <!-- Net Profit/Loss -->
                                <tr class="{{ $netProfit >= 0 ? 'bg-success' : 'bg-danger' }} text-white">
                                    <th><strong>{{ $netProfit >= 0 ? 'LABA' : 'RUGI' }} BERSIH</strong></th>
                                    <th class="text-end"><strong>Rp {{ number_format(abs($netProfit), 0, ',', '.') }}</strong></th>
                                </tr>

                                <!-- Profit Margin -->
                                <tr class="bg-light">
                                    <td><strong>Margin Keuntungan</strong></td>
                                    <td class="text-end"><strong>{{ $totalSales > 0 ? number_format(($netProfit / $totalSales) * 100, 2) : 0 }}%</strong></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="row">
        <div class="col-xl-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 fw-bold text-primary">
                        <i class="fas fa-chart-bar me-1"></i> Pendapatan vs Pengeluaran
                    </h6>
                </div>
                <div class="card-body">
                    <div class="chart-bar">
                        <canvas id="revenueExpensesChart" height="300"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 fw-bold text-primary">
                        <i class="fas fa-chart-pie me-1"></i> Rincian Pengeluaran
                    </h6>
                </div>
                <div class="card-body">
                    @if(count($expenseBreakdown) > 0)
                    <div class="chart-pie">
                        <canvas id="expenseBreakdownChart" height="300"></canvas>
                    </div>
                    <div class="mt-4 text-center small">
                        @foreach($expenseBreakdown as $index => $expense)
                            <span class="me-2">
                                <i class="fas fa-circle" style="color: {{ ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b', '#5a5c69', '#6610f2', '#fd7e14', '#20c9a6', '#858796'][$index % 10] }};"></i> {{ ucfirst($expense->category) }}
                            </span>
                        @endforeach
                    </div>
                    @else
                    <div class="text-center py-5">
                        <i class="fas fa-chart-pie fa-3x text-gray-300 mb-3"></i>
                        <p>Tidak ada data pengeluaran untuk ditampilkan</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Expense Details Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 fw-bold text-primary">
                <i class="fas fa-table me-1"></i> Detail Pengeluaran
            </h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <!-- Tabel standar tanpa DataTables -->
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Kategori</th>
                            <th>Jumlah</th>
                            <th>% dari Total Pengeluaran</th>
                            <th>% dari Pendapatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($expenseBreakdown as $expense)
                        <tr>
                            <td>{{ ucfirst($expense->category) }}</td>
                            <td>Rp {{ number_format($expense->total, 0, ',', '.') }}</td>
                            <td>{{ $expenses > 0 ? number_format(($expense->total / $expenses) * 100, 2) : 0 }}%</td>
                            <td>{{ $totalSales > 0 ? number_format(($expense->total / $totalSales) * 100, 2) : 0 }}%</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center">Tidak ada data pengeluaran</td>
                        </tr>
                        @endforelse
                    </tbody>
                    @if(count($expenseBreakdown) > 0)
                    <tfoot>
                        <tr class="fw-bold table-secondary">
                            <td>Total</td>
                            <td>Rp {{ number_format($expenses, 0, ',', '.') }}</td>
                            <td>100%</td>
                            <td>{{ $totalSales > 0 ? number_format(($expenses / $totalSales) * 100, 2) : 0 }}%</td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Revenue vs Expenses Chart
        var ctx = document.getElementById("revenueExpensesChart");
        if (ctx) {
            var revenueExpensesChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ["Pendapatan", "HPP", "Laba Kotor", "Pengeluaran", "Laba Bersih"],
                    datasets: [{
                        label: "Jumlah (Rp)",
                        backgroundColor: ["#4e73df", "#e74a3b", "#1cc88a", "#f6c23e", "#36b9cc"],
                        data: [
                            {{ $totalSales ?: 0 }},
                            {{ $cogs ?: 0 }},
                            {{ $grossProfit ?: 0 }},
                            {{ $expenses ?: 0 }},
                            {{ $netProfit ?: 0 }}
                        ],
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
                                    return 'Rp ' + number_format(value);
                                }
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return 'Rp ' + number_format(context.raw);
                                }
                            }
                        }
                    }
                }
            });
        }

        // Expense Breakdown Chart (only if we have expense data)
        var ctx2 = document.getElementById("expenseBreakdownChart");
        if (ctx2 && {{ count($expenseBreakdown) > 0 ? 'true' : 'false' }}) {
            var expenseBreakdownChart = new Chart(ctx2, {
                type: 'doughnut',
                data: {
                    labels: [
                        @foreach($expenseBreakdown as $expense)
                            "{{ ucfirst($expense->category) }}",
                        @endforeach
                    ],
                    datasets: [{
                        data: [
                            @foreach($expenseBreakdown as $expense)
                                {{ $expense->total ?: 0 }},
                            @endforeach
                        ],
                        backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b', '#5a5c69', '#6610f2', '#fd7e14', '#20c9a6', '#858796'],
                        hoverBackgroundColor: ['#2e59d9', '#17a673', '#2c9faf', '#dda20a', '#be2617', '#484a52', '#4d0ca3', '#c26012', '#169d81', '#60616f'],
                        hoverBorderColor: "rgba(234, 236, 244, 1)",
                    }],
                },
                options: {
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: "rgb(255,255,255)",
                            bodyFontColor: "#858796",
                            borderColor: '#dddfeb',
                            borderWidth: 1,
                            xPadding: 15,
                            yPadding: 15,
                            displayColors: false,
                            caretPadding: 10,
                            callbacks: {
                                label: function(context) {
                                    var value = context.raw;
                                    var total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    var percentage = Math.round((value / total) * 100);
                                    return context.label + ': Rp ' + number_format(value) + ' (' + percentage + '%)';
                                }
                            }
                        }
                    },
                    cutout: '70%',
                }
            });
        }
    });

    function number_format(number, decimals, dec_point, thousands_sep) {
        // Format numbers with commas
        decimals = decimals || 0;
        number = parseFloat(number);

        if (isNaN(number) || number === null) {
            return "0";
        }

        number = number.toFixed(decimals);

        var parts = number.split('.');
        parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, thousands_sep || '.');

        return parts.join(dec_point || ',');
    }
</script>
@endsection
