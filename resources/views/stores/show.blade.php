@extends('layouts.app')

@section('title', 'Detail Toko')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-dark fw-bold">
            <i class="fas fa-store me-2 text-primary"></i> Detail Toko
        </h1>
        <div>
            @can('edit stores')
            <a href="{{ route('stores.edit', $store) }}" class="btn btn-primary">
                <i class="fas fa-edit me-1"></i> Edit
            </a>
            @endcan
            <a href="{{ route('stores.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Kembali
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Informasi Toko -->
        <div class="col-xl-6 col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 fw-bold text-primary">Informasi Toko</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <tr>
                                <th width="30%" class="bg-light">Nama Toko</th>
                                <td class="fw-medium">{{ $store->name }}</td>
                            </tr>
                            <tr>
                                <th class="bg-light">Alamat</th>
                                <td>{{ $store->address ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th class="bg-light">Telepon</th>
                                <td>{{ $store->phone ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th class="bg-light">Email</th>
                                <td>{{ $store->email ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th class="bg-light">Status</th>
                                <td>
                                    @if($store->is_active)
                                        <span class="badge bg-success-light text-success rounded-pill px-2">Aktif</span>
                                    @else
                                        <span class="badge bg-danger-light text-danger rounded-pill px-2">Tidak Aktif</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th class="bg-light">Terdaftar Sejak</th>
                                <td>{{ $store->created_at->format('d/m/Y') }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistik Toko -->
        <div class="col-xl-6 col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 fw-bold text-primary">Statistik Toko</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <div class="card border-left-primary shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                Total Produk</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $productCount }}</div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-boxes fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 mb-4">
                            <div class="card border-left-success shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                                Nilai Stok</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">Rp {{ number_format($stockValue, 0, ',', '.') }}</div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-money-bill-wave fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Link Terkait -->
                    <div class="mt-3">
                        <h6 class="font-weight-bold">Link Terkait:</h6>
                        <div class="list-group">
                            <a href="{{ route('stock.store') }}?store_id={{ $store->id }}" class="list-group-item list-group-item-action d-flex align-items-center">
                                <i class="fas fa-boxes me-2 text-primary"></i> Lihat Stok Toko
                            </a>
                            <a href="{{ route('reports.sales') }}?store_id={{ $store->id }}" class="list-group-item list-group-item-action d-flex align-items-center">
                                <i class="fas fa-chart-line me-2 text-primary"></i> Laporan Penjualan Toko
                            </a>
                            <a href="{{ route('reports.inventory') }}?store_id={{ $store->id }}" class="list-group-item list-group-item-action d-flex align-items-center">
                                <i class="fas fa-dolly me-2 text-primary"></i> Laporan Inventaris Toko
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Transaksi Terbaru -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 fw-bold text-primary">Transaksi Penjualan Terbaru</h6>
            <a href="{{ route('reports.sales') }}?store_id={{ $store->id }}" class="btn btn-sm btn-outline-primary">
                Lihat Semua <i class="fas fa-arrow-right ms-1"></i>
            </a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="recentSalesTable" width="100%" cellspacing="0">
                    <thead class="table-light">
                        <tr>
                            <th>Tanggal</th>
                            <th>Invoice</th>
                            <th>Pelanggan</th>
                            <th>Total</th>
                            <th>Metode Pembayaran</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentSales as $sale)
                            <tr>
                                <td>{{ $sale->date->format('d/m/Y') }}</td>
                                <td><span class="fw-medium">{{ $sale->invoice_number }}</span></td>
                                <td>{{ $sale->customer_name ?? 'Pelanggan Umum' }}</td>
                                <td>Rp {{ number_format($sale->total_amount, 0, ',', '.') }}</td>
                                <td>
                                    <span class="badge {{ $sale->payment_type === 'tunai' ? 'bg-success-light text-success' : '' }}
                                                    {{ $sale->payment_type === 'kartu' ? 'bg-info-light text-info' : '' }}
                                                    {{ $sale->payment_type === 'tempo' ? 'bg-warning-light text-warning' : '' }} rounded-pill px-2">
                                        {{ ucfirst($sale->payment_type) }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('sales.show', $sale) }}" class="btn btn-sm btn-outline-info" data-bs-toggle="tooltip" title="Lihat Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">Belum ada transaksi penjualan</td>
                            </tr>
                        @endforelse
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
        if ($.fn.dataTable.isDataTable('#recentSalesTable')) {
            $('#recentSalesTable').DataTable().destroy();
        }
        
        // Cek jika ada data di tabel
        if ($('#recentSalesTable tbody tr').length > 1 || 
            ($('#recentSalesTable tbody tr').length === 1 && $('#recentSalesTable tbody tr td').length > 1)) {
            
            // Inisialisasi DataTable untuk tabel transaksi terbaru
            $('#recentSalesTable').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json'
                },
                responsive: true,
                paging: false,
                searching: false,
                info: false,
                ordering: false,
                destroy: true
            });
        }
        
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