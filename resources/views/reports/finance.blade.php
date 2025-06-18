@extends('layouts.app')

@section('title', 'Laporan Keuangan')

@section('content')
<div class="container-fluid">
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-dark fw-bold">
                <i class="fas fa-chart-line me-2 text-primary"></i> Laporan Keuangan
            </h1>
            <p class="text-muted">
                Analisis pendapatan, pengeluaran, dan laba perusahaan
                @if(isset($userStoreId) && $userStoreId)
                    - {{ \App\Models\Store::find($userStoreId)->name ?? 'Toko Anda' }}
                @endif
            </p>
        </div>
        <div class="d-flex flex-wrap">
            <a href="{{ route('reports.balance-sheet') }}" class="btn btn-info text-white me-2 mb-2">
                <i class="fas fa-balance-scale me-1"></i> Laporan Neraca
            </a>
            <a href="{{ route('reports.finance.export', request()->query()) }}" class="btn btn-success mb-2">
                <i class="fas fa-file-excel me-1"></i> Export Excel
            </a>
        </div>
    </div>

    <!-- Alert Section -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Info Alert untuk User Cabang --}}
    @if(isset($canSelectStore) && !$canSelectStore && isset($userStoreId) && $userStoreId)
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <i class="fas fa-info-circle me-2"></i>
            <strong>Info:</strong> Anda sedang melihat laporan keuangan untuk toko: <strong>{{ \App\Models\Store::find($userStoreId)->name ?? 'Toko Anda' }}</strong>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Filter Section -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 fw-bold text-primary">
                <i class="fas fa-filter me-1"></i> Filter Laporan
            </h6>
            <span class="small text-muted">Periode: {{ \Carbon\Carbon::parse($startDate ?? now()->startOfMonth())->format('d M Y') }} - {{ \Carbon\Carbon::parse($endDate ?? now())->format('d M Y') }}</span>
        </div>
        <div class="card-body">
            <form action="{{ route('reports.finance') }}" method="GET">
                <div class="row">
                    <div class="col-md-{{ isset($canSelectStore) && $canSelectStore ? '4' : '6' }}">
                        <div class="form-group mb-3">
                            <label for="start_date" class="form-label">Tanggal Mulai</label>
                            <input type="date" class="form-control" id="start_date" name="start_date" value="{{ request('start_date', now()->startOfMonth()->format('Y-m-d')) }}">
                        </div>
                    </div>
                    <div class="col-md-{{ isset($canSelectStore) && $canSelectStore ? '4' : '6' }}">
                        <div class="form-group mb-3">
                            <label for="end_date" class="form-label">Tanggal Akhir</label>
                            <input type="date" class="form-control" id="end_date" name="end_date" value="{{ request('end_date', now()->format('Y-m-d')) }}">
                        </div>
                    </div>

                    @if(isset($canSelectStore) && $canSelectStore)
                        {{-- User pusat - bisa pilih semua toko --}}
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
                    @else
                        {{-- User cabang - toko sudah fixed --}}
                        <input type="hidden" name="store_id" value="{{ $userStoreId }}">
                        <div class="col-md-12">
                            <div class="form-group mb-3">
                                <label class="form-label">Lokasi</label>
                                <div class="form-control-plaintext bg-light rounded px-3 py-2">
                                    <i class="fas fa-store me-2 text-primary"></i>
                                    <strong>{{ \App\Models\Store::find($userStoreId)->name ?? 'Toko Anda' }}</strong>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <div class="form-group d-flex">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-filter me-1"></i> Filter
                    </button>
                    <a href="{{ route('reports.finance') }}" class="btn btn-secondary">
                        <i class="fas fa-sync me-1"></i> Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Saldo Awal Section -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center bg-light">
            <h6 class="m-0 fw-bold text-primary">
                <i class="fas fa-wallet me-1"></i> Saldo Kas dan Bank
                @if(isset($userStoreId) && $userStoreId)
                    <small class="text-muted">- {{ \App\Models\Store::find($userStoreId)->name ?? 'Toko Anda' }}</small>
                @endif
            </h6>
            <div>
                <a href="{{ route('finance.balance.create') }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus me-1"></i> Input Saldo Baru
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                @forelse($balances as $balance)
                    <div class="col-md-4 mb-3">
                        <div class="card h-100 bg-gradient bg-light border-left-primary">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5 class="card-title text-primary">
                                        <i class="fas fa-{{ $balance->category->type == 'asset' ? 'money-bill-alt' : ($balance->category->type == 'liability' ? 'hand-holding-usd' : 'chart-pie') }} me-1"></i>
                                        {{ $balance->category->name }}
                                    </h5>
                                    <i class="fas fa-{{ $balance->category->type == 'asset' ? 'coins' : ($balance->category->type == 'liability' ? 'file-invoice-dollar' : 'balance-scale') }} fa-2x text-primary opacity-25"></i>
                                </div>
                                <h3 class="text-primary fw-bold">Rp {{ number_format($balance->amount, 0, ',', '.') }}</h3>
                                <div class="d-flex justify-content-between">
                                    <small class="text-muted">Update: {{ $balance->date->format('d/m/Y') }}</small>
                                    @if($balance->store)
                                        <span class="badge bg-info">{{ $balance->store->name }}</span>
                                    @else
                                        <span class="badge bg-secondary">Global</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Belum ada data saldo
                            @if(isset($userStoreId) && $userStoreId)
                                untuk toko {{ \App\Models\Store::find($userStoreId)->name ?? 'ini' }}
                            @endif
                            . Silakan tambahkan saldo awal.
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Summary Cards Section -->
    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Penjualan
                                @if(isset($userStoreId) && $userStoreId)
                                    <br><small class="text-muted">{{ \App\Models\Store::find($userStoreId)->name ?? 'Toko' }}</small>
                                @endif
                            </div>
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
                                Total Pembelian
                                @if(isset($userStoreId) && $userStoreId)
                                    <br><small class="text-muted">Global</small>
                                @endif
                            </div>
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
                                Total Pengeluaran
                                @if(isset($userStoreId) && $userStoreId)
                                    <br><small class="text-muted">{{ \App\Models\Store::find($userStoreId)->name ?? 'Toko' }}</small>
                                @endif
                            </div>
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
                                Laba Bersih
                                @if(isset($userStoreId) && $userStoreId)
                                    <br><small class="text-muted">{{ \App\Models\Store::find($userStoreId)->name ?? 'Toko' }}</small>
                                @endif
                            </div>
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

    <!-- Payables & Receivables Section (Hanya tampil untuk user pusat) -->
    @if(isset($canSelectStore) && $canSelectStore)
        <div class="row mt-2">
            <div class="col-lg-6 mb-4">
                <div class="card shadow h-100">
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between bg-light">
                        <h6 class="m-0 fw-bold text-primary">
                            <i class="fas fa-file-invoice-dollar me-1"></i> Laporan Hutang ke Pemasok
                        </h6>
                        <a href="{{ route('reports.payables') }}" class="btn btn-sm btn-primary">
                            <i class="fas fa-arrow-right"></i> Lihat Detail
                        </a>
                    </div>
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="icon-circle bg-primary text-white me-3">
                                <i class="fas fa-file-invoice-dollar"></i>
                            </div>
                            <div>
                                <h5 class="mb-0">Hutang ke Pemasok</h5>
                                <p class="text-muted small mb-0">Menampilkan daftar hutang yang belum dibayar ke pemasok</p>
                            </div>
                        </div>
                        <hr>
                        <div class="row text-center">
                            <div class="col">
                                <h6 class="small text-muted">Total Hutang</h6>
                                <h5 class="mb-0 text-primary">Rp {{ number_format($totalPayables ?? 0, 0, ',', '.') }}</h5>
                            </div>
                            <div class="col">
                                <h6 class="small text-muted">Hutang Jatuh Tempo</h6>
                                <h5 class="mb-0 text-danger">Rp {{ number_format($overduePayables ?? 0, 0, ',', '.') }}</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6 mb-4">
                <div class="card shadow h-100">
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between bg-light">
                        <h6 class="m-0 fw-bold text-primary">
                            <i class="fas fa-hand-holding-usd me-1"></i> Laporan Piutang dari Toko
                        </h6>
                        <a href="{{ route('reports.receivables') }}" class="btn btn-sm btn-primary">
                            <i class="fas fa-arrow-right"></i> Lihat Detail
                        </a>
                    </div>
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="icon-circle bg-success text-white me-3">
                                <i class="fas fa-hand-holding-usd"></i>
                            </div>
                            <div>
                                <h5 class="mb-0">Piutang dari Toko</h5>
                                <p class="text-muted small mb-0">Menampilkan daftar piutang yang belum dibayar dari toko</p>
                            </div>
                        </div>
                        <hr>
                        <div class="row text-center">
                            <div class="col">
                                <h6 class="small text-muted">Total Piutang</h6>
                                <h5 class="mb-0 text-success">Rp {{ number_format($totalReceivables ?? 0, 0, ',', '.') }}</h5>
                            </div>
                            <div class="col">
                                <h6 class="small text-muted">Piutang Jatuh Tempo</h6>
                                <h5 class="mb-0 text-danger">Rp {{ number_format($overdueReceivables ?? 0, 0, ',', '.') }}</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Charts Section -->
    <div class="row">
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-light">
                    <h6 class="m-0 fw-bold text-primary">
                        <i class="fas fa-chart-line me-1"></i> Tren Pendapatan vs Pengeluaran
                        @if(isset($userStoreId) && $userStoreId)
                            <small class="text-muted">- {{ \App\Models\Store::find($userStoreId)->name ?? 'Toko' }}</small>
                        @endif
                    </h6>
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
                <div class="card-header py-3 bg-light">
                    <h6 class="m-0 fw-bold text-primary">
                        <i class="fas fa-chart-pie me-1"></i> Kategori Pengeluaran
                        @if(isset($userStoreId) && $userStoreId)
                            <small class="text-muted">- {{ \App\Models\Store::find($userStoreId)->name ?? 'Toko' }}</small>
                        @endif
                    </h6>
                </div>
                <div class="card-body">
                    <div class="chart-pie pt-4 pb-2">
                        <canvas id="expenseCategoriesChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Financial Summary & Expense Details Section -->
    <div class="row">
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-light">
                    <h6 class="m-0 fw-bold text-primary">
                        <i class="fas fa-file-alt me-1"></i> Ringkasan Keuangan
                        @if(isset($userStoreId) && $userStoreId)
                            <small class="text-muted">- {{ \App\Models\Store::find($userStoreId)->name ?? 'Toko' }}</small>
                        @endif
                    </h6>
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
                                <td>
                                    @if($canSelectStore && !request('store_id'))
                                        Biaya Operasional
                                        <small class="text-muted d-block">Termasuk ongkir yang dibayarkan oleh kantor pusat</small>
                                    @else
                                        Beban Biaya Transportasi
                                        <small class="text-muted d-block">Termasuk ongkir yang ditanggung oleh outlet</small>
                                    @endif
                                </td>
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
                <div class="card-header py-3 d-flex justify-content-between align-items-center bg-light">
                    <h6 class="m-0 fw-bold text-primary">
                        <i class="fas fa-list me-1"></i> Rincian Pengeluaran
                        @if(isset($userStoreId) && $userStoreId)
                            <small class="text-muted">- {{ \App\Models\Store::find($userStoreId)->name ?? 'Toko' }}</small>
                        @endif
                    </h6>
                    <a href="{{ route('finance.expense.create') }}" class="btn btn-sm btn-warning">
                        <i class="fas fa-plus me-1"></i> Tambah Pengeluaran
                    </a>
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
                                @forelse($expense_categories as $category)
                                <tr class="{{ $category['has_shipping'] ? 'table-info' : '' }}">
                                    <td>
                                        {{ $category['category'] }}
                                        @if(str_contains(strtolower($category['category']), 'operasional'))
                                            <small class="text-muted d-block">Termasuk ongkir untuk pesanan dari pusat</small>
                                        @elseif(str_contains(strtolower($category['category']), 'transportasi'))
                                            <small class="text-muted d-block">Termasuk ongkir untuk pesanan ke outlet</small>
                                        @endif
                                    </td>
                                    <td>Rp {{ number_format($category['total'], 0, ',', '.') }}</td>
                                    <td>{{ $expenses > 0 ? number_format(($category['total'] / $expenses) * 100, 1, ',', '.') : 0 }}%</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted">
                                        Tidak ada data pengeluaran
                                        @if(isset($userStoreId) && $userStoreId)
                                            untuk toko ini
                                        @endif
                                        pada periode yang dipilih
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                            @if($expense_categories->count() > 0)
                            <tfoot>
                                <tr class="fw-bold">
                                    <td>Total</td>
                                    <td>Rp {{ number_format($expenses, 0, ',', '.') }}</td>
                                    <td>100%</td>
                                </tr>
                            </tfoot>
                            @endif
                        </table>
                    </div>

                    @if($expense_categories->filter(function($category) { return $category['has_shipping']; })->count() > 0)
                    <div class="alert alert-info mt-3">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Informasi Ongkir:</strong>
                        <ul class="mb-0 mt-1">
                            <li>Baris yang disorot <span class="badge bg-info">biru</span> menunjukkan kategori yang mencakup biaya ongkir.</li>
                            <li><strong>Biaya Operasional</strong> mencakup ongkir yang dibayarkan oleh kantor pusat saat konfirmasi pesanan.</li>
                            <li><strong>Beban Biaya Transportasi</strong> mencakup ongkir yang ditanggung oleh outlet saat penerimaan barang.</li>
                        </ul>
                    </div>
                    @endif

                    @if($canSelectStore && !request('store_id'))
                        <div class="alert alert-info mt-3">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Informasi:</strong> Sebagai kantor pusat, Anda hanya melihat kategori pengeluaran pusat termasuk Biaya Operasional (ongkir). Beban Biaya Transportasi untuk outlet tidak ditampilkan.
                        </div>
                    @elseif(!$canSelectStore || request('store_id'))
                        <div class="alert alert-info mt-3">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Informasi:</strong> Untuk outlet, hanya kategori pengeluaran outlet yang ditampilkan termasuk Beban Biaya Transportasi (ongkir). Biaya Operasional pusat tidak ditampilkan.
                        </div>
                    @endif
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
                layout: {
                    padding: {
                        left: 10,
                        right: 25,
                        top: 25,
                        bottom: 0
                    }
                },
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
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        backgroundColor: "rgb(30, 30, 30)",
                        titleColor: "#ffffff",
                        bodyColor: "#ffffff",
                        titleMarginBottom: 10,
                        titleFont: {
                            size: 14,
                            weight: 'bold'
                        },
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
                        "{{ ucfirst($category['category']) }}",
                    @endforeach
                ],
                datasets: [{
                    data: [
                        @foreach($expense_categories as $category)
                            {{ $category['total'] }},
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
                        position: 'bottom',
                        display: true,
                        labels: {
                            boxWidth: 12
                        }
                    },
                    tooltip: {
                        backgroundColor: "rgb(30, 30, 30)",
                        titleColor: "#ffffff",
                        bodyColor: "#ffffff",
                        titleFont: {
                            size: 14,
                            weight: 'bold'
                        },
                        bodyFont: {
                            size: 14
                        },
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
                    }
                },
                cutout: '70%',
            }
        });
    });

    function number_format(number, decimals, dec_point, thousands_sep) {
        // Format numbers with proper separator for Indonesian format (. for thousands, , for decimal)
        number = (number + '').replace(',', '').replace(' ', '');
        var n = !isFinite(+number) ? 0 : +number,
            prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
            sep = (typeof thousands_sep === 'undefined') ? '.' : thousands_sep,
            dec = (typeof dec_point === 'undefined') ? ',' : dec_point,
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
