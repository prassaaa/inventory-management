@extends('layouts.app')

@section('title', 'Laporan Keuangan')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-dark fw-bold">
                <i class="fas fa-chart-line me-2 text-primary"></i> Laporan Keuangan
            </h1>
            <p class="text-muted">Analisis pendapatan, pengeluaran, dan laba perusahaan</p>
        </div>
        <div>
            <a href="{{ route('reports.finance.export', request()->query()) }}" class="btn btn-success">
                <i class="fas fa-file-excel me-1"></i> Export Excel
            </a>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 fw-bold text-primary">Filter</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('reports.finance') }}" method="GET">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label for="start_date" class="form-label">Tanggal Mulai</label>
                            <input type="date" class="form-select" id="start_date" name="start_date" value="{{ request('start_date', now()->startOfMonth()->format('Y-m-d')) }}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label for="end_date" class="form-label">Tanggal Akhir</label>
                            <input type="date" class="form-select" id="end_date" name="end_date" value="{{ request('end_date', now()->format('Y-m-d')) }}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label for="store_id" class="form-label">Lokasi</label>
                            <select class="form-select" id="store_id" name="store_id">
                                <option value="">Semua Lokasi</option>
                                @foreach($stores as $store)
                                    <option value="{{ $store->id }}" {{ request('store_id') == $store->id ? 'selected' : '' }}>
                                        {{ $store->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter me-1"></i> Filter
                    </button>
                    <a href="{{ route('reports.finance') }}" class="btn btn-secondary">
                        <i class="fas fa-sync me-1"></i> Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Penjualan</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">Rp {{ number_format($sales, 0, ',', '.') }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-cash-register fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                Total Pembelian</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">Rp {{ number_format($purchases, 0, ',', '.') }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-shopping-cart fa-2x text-gray-300"></i>
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
                            <i class="fas fa-file-invoice-dollar fa-2x text-gray-300"></i>
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
                                Laba Bersih</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">Rp {{ number_format($netProfit, 0, ',', '.') }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 fw-bold text-primary">Tren Pendapatan vs Pengeluaran</h6>
                </div>
                <div class="card-body">
                    <div class="chart-area">
                        <canvas id="financeChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
                
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 fw-bold text-primary">Kategori Pengeluaran</h6>
                </div>
                <div class="card-body">
                    <div class="chart-pie pt-4 pb-2">
                        <canvas id="expenseCategoriesChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 fw-bold text-primary">Ringkasan Keuangan</h6>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <tbody>
                            <tr class="table-primary">
                                <th colspan="2">Pendapatan</th>
                            </tr>
                            <tr>
                                <td>Pendapatan Penjualan</td>
                                <td class="text-end">Rp {{ number_format($sales, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td><strong>Total Pendapatan</strong></td>
                                <td class="text-end"><strong>Rp {{ number_format($sales, 0, ',', '.') }}</strong></td>
                            </tr>
                            
                            <tr class="table-danger">
                                <th colspan="2">Pengeluaran</th>
                            </tr>
                            <tr>
                                <td>Biaya Barang (Pembelian)</td>
                                <td class="text-end">Rp {{ number_format($purchases, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td>Biaya Operasional</td>
                                <td class="text-end">Rp {{ number_format($expenses, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td><strong>Total Pengeluaran</strong></td>
                                <td class="text-end"><strong>Rp {{ number_format($purchases + $expenses, 0, ',', '.') }}</strong></td>
                            </tr>
                            
                            <tr class="table-success">
                                <th>Laba Kotor</th>
                                <td class="text-end"><strong>Rp {{ number_format($grossProfit, 0, ',', '.') }}</strong></td>
                            </tr>
                            <tr class="table-success">
                                <th>Laba Bersih</th>
                                <td class="text-end"><strong>Rp {{ number_format($netProfit, 0, ',', '.') }}</strong></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 fw-bold text-primary">Rincian Pengeluaran</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Kategori</th>
                                    <th>Jumlah</th>
                                    <th>%</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($expense_categories as $category)
                                <tr>
                                    <td>{{ ucfirst($category->category) }}</td>
                                    <td>Rp {{ number_format($category->total, 0, ',', '.') }}</td>
                                    <td>{{ $expenses > 0 ? number_format(($category->total / $expenses) * 100, 1) : 0 }}%</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="fw-bold">
                                    <td>Total</td>
                                    <td>Rp {{ number_format($expenses, 0, ',', '.') }}</td>
                                    <td>100%</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Finance Chart
        var ctx = document.getElementById("financeChart");
        var financeChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: {!! json_encode($chart_data['labels']) !!},
                datasets: [
                    {
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
                        data: {!! json_encode($chart_data['sales']) !!},
                    },
                    {
                        label: "Pembelian",
                        lineTension: 0.3,
                        backgroundColor: "rgba(231, 74, 59, 0.05)",
                        borderColor: "rgba(231, 74, 59, 1)",
                        pointRadius: 3,
                        pointBackgroundColor: "rgba(231, 74, 59, 1)",
                        pointBorderColor: "rgba(231, 74, 59, 1)",
                        pointHoverRadius: 3,
                        pointHoverBackgroundColor: "rgba(231, 74, 59, 1)",
                        pointHoverBorderColor: "rgba(231, 74, 59, 1)",
                        pointHitRadius: 10,
                        pointBorderWidth: 2,
                        data: {!! json_encode($chart_data['purchases']) !!},
                    },
                    {
                        label: "Pengeluaran",
                        lineTension: 0.3,
                        backgroundColor: "rgba(246, 194, 62, 0.05)",
                        borderColor: "rgba(246, 194, 62, 1)",
                        pointRadius: 3,
                        pointBackgroundColor: "rgba(246, 194, 62, 1)",
                        pointBorderColor: "rgba(246, 194, 62, 1)",
                        pointHoverRadius: 3,
                        pointHoverBackgroundColor: "rgba(246, 194, 62, 1)",
                        pointHoverBorderColor: "rgba(246, 194, 62, 1)",
                        pointHitRadius: 10,
                        pointBorderWidth: 2,
                        data: {!! json_encode($chart_data['expenses']) !!},
                    }
                ],
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
                        backgroundColor: "rgb(255,255,255)",
                        bodyFontColor: "#858796",
                        titleMarginBottom: 10,
                        titleFontColor: '#6e707e',
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
                                return context.dataset.label + ': Rp ' + context.raw.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                            }
                        }
                    }
                }
            }
        });
        
        // Expense Categories Chart
        var ctx2 = document.getElementById("expenseCategoriesChart");
        var expenseCategoriesChart = new Chart(ctx2, {
            type: 'doughnut',
            data: {
                labels: [
                    @foreach($expense_categories as $category)
                        "{{ ucfirst($category->category) }}",
                    @endforeach
                ],
                datasets: [{
                    data: [
                        @foreach($expense_categories as $category)
                            {{ $category->total }},
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
                                return context.label + ': Rp ' + context.raw.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                            }
                        }
                    },
                    legend: {
                        display: true,
                        position: 'bottom'
                    }
                },
                cutout: '70%',
            }
        });
    });

    function number_format(number, decimals, dec_point, thousands_sep) {
        // Format numbers with commas
        number = (number + '').replace(',', '').replace(' ', '');
        var n = !isFinite(+number) ? 0 : +number,
            prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
            sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
            dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
            s = '',
            toFixedFix = function(n, prec) {
                var k = Math.pow(10, prec);
                return '' + Math.round(n * k) / k;
            };
        s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
        if (s[0].length > 3) {
            s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
        }
        if ((s[1] || '').length < prec) {
            s[1] = s[1] || '';
            s[1] += new Array(prec - s[1].length + 1).join('0');
        }
        return s.join(dec);
    }
</script>
@endsection