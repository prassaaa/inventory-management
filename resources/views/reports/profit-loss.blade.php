@extends('layouts.app')

@section('title', 'Laporan Laba Rugi')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-dark fw-bold">
                <i class="fas fa-chart-pie me-2 text-primary"></i> Laporan Laba Rugi
            </h1>
            <p class="text-muted">Analisis pendapatan, biaya, dan profitabilitas bisnis</p>
        </div>
        <div>
            <a href="{{ route('reports.profit-loss.export', request()->query()) }}" class="btn btn-success">
                <i class="fas fa-file-excel me-1"></i> Export Excel
            </a>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 fw-bold text-primary">Filter</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('reports.profit-loss') }}" method="GET">
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
                    <a href="{{ route('reports.profit-loss') }}" class="btn btn-secondary">
                        <i class="fas fa-sync me-1"></i> Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 fw-bold text-primary">Laporan Laba Rugi</h6>
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
                                @foreach($expenseBreakdown as $expense)
                                <tr>
                                    <td style="padding-left: 20px;">{{ ucfirst($expense->category) }}</td>
                                    <td class="text-end">Rp {{ number_format($expense->total, 0, ',', '.') }}</td>
                                </tr>
                                @endforeach
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

    <div class="row">
        <div class="col-xl-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 fw-bold text-primary">Pendapatan vs Pengeluaran</h6>
                </div>
                <div class="card-body">
                    <div class="chart-bar">
                        <canvas id="revenueExpensesChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 fw-bold text-primary">Rincian Pengeluaran</h6>
                </div>
                <div class="card-body">
                    <div class="chart-pie">
                        <canvas id="expenseBreakdownChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 fw-bold text-primary">Detail Pengeluaran</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered datatable" width="100%" cellspacing="0">
                    <thead class="table-light">
                        <tr>
                            <th>Kategori</th>
                            <th>Jumlah</th>
                            <th>% dari Total Pengeluaran</th>
                            <th>% dari Pendapatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($expenseBreakdown as $expense)
                        <tr>
                            <td>{{ ucfirst($expense->category) }}</td>
                            <td>Rp {{ number_format($expense->total, 0, ',', '.') }}</td>
                            <td>{{ $expenses > 0 ? number_format(($expense->total / $expenses) * 100, 2) : 0 }}%</td>
                            <td>{{ $totalSales > 0 ? number_format(($expense->total / $totalSales) * 100, 2) : 0 }}%</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="fw-bold">
                            <td>Total</td>
                            <td>Rp {{ number_format($expenses, 0, ',', '.') }}</td>
                            <td>100%</td>
                            <td>{{ $totalSales > 0 ? number_format(($expenses / $totalSales) * 100, 2) : 0 }}%</td>
                        </tr>
                    </tfoot>
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
        if ($.fn.dataTable.isDataTable('.datatable')) {
            $('.datatable').DataTable().destroy();
        }
        
        // Inisialisasi DataTable baru
        $('.datatable').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json'
            },
            responsive: true,
            dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>t<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
            pageLength: 10,
            order: [[1, 'desc']],
            destroy: true // Pastikan opsi destroy diaktifkan
        });
        
        // Revenue vs Expenses Chart
        var ctx = document.getElementById("revenueExpensesChart");
        var revenueExpensesChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ["Pendapatan", "HPP", "Laba Kotor", "Pengeluaran", "Laba Bersih"],
                datasets: [{
                    label: "Jumlah",
                    backgroundColor: ["#4e73df", "#e74a3b", "#1cc88a", "#f6c23e", "#36b9cc"],
                    data: [
                        {{ $totalSales }}, 
                        {{ $cogs }}, 
                        {{ $grossProfit }}, 
                        {{ $expenses }}, 
                        {{ $netProfit }}
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
                                return 'Rp ' + value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                            }
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.label + ': Rp ' + context.raw.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                            }
                        }
                    }
                }
            }
        });
        
        // Expense Breakdown Chart
        var ctx2 = document.getElementById("expenseBreakdownChart");
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
                            {{ $expense->total }},
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