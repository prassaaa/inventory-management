@extends('layouts.app')

@section('title', 'Penjualan')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-dark fw-bold">
                <i class="fas fa-cash-register me-2 text-primary"></i> Penjualan
            </h1>
            <p class="text-muted">Kelola transaksi penjualan ke pelanggan</p>
        </div>
        <div class="d-flex gap-2">
            @can('create sales')
            <a href="{{ route('pos') }}" class="btn btn-success">
                <i class="fas fa-cash-register me-1"></i> Kasir (POS)
            </a>
            @endcan
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 fw-bold text-primary">Daftar Penjualan</h6>
            <div class="input-group" style="width: 250px;">
                <span class="input-group-text bg-light border-0">
                    <i class="fas fa-search text-primary"></i>
                </span>
                <input type="text" id="customSearch" class="form-control border-0 bg-light" placeholder="Cari penjualan...">
            </div>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show d-flex align-items-center" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    <div>{{ session('success') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <div>{{ session('error') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="table-responsive">
                <table class="table table-hover datatable" width="100%" cellspacing="0">
                    <thead class="table-light">
                        <tr>
                            <th>Tanggal</th>
                            <th>No. Faktur</th>
                            <th>Toko</th>
                            <th>Pelanggan</th>
                            <th>Total</th>
                            <th>Status</th>
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
                            <td>Rp {{ number_format($sale->total_amount, 0, ',', '.') }}</td>
                            <td>
                                @if($sale->status === 'paid')
                                    <span class="badge bg-success-light text-success rounded-pill px-2">Lunas</span>
                                @else
                                    <span class="badge bg-warning-light text-warning rounded-pill px-2">Tertunda</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('sales.show', $sale) }}" class="btn btn-sm btn-outline-info" data-bs-toggle="tooltip" title="Lihat Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    
                                    <a href="{{ route('sales.receipt', $sale) }}" class="btn btn-sm btn-outline-secondary" target="_blank" data-bs-toggle="tooltip" title="Cetak">
                                        <i class="fas fa-print"></i>
                                    </a>
                                </div>
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
        // Cek jika tabel sudah diinisialisasi sebelumnya
        if ($.fn.dataTable.isDataTable('.datatable')) {
            // Hancurkan tabel yang sudah ada sebelum menginisialisasi yang baru
            $('.datatable').DataTable().destroy();
        }
        
        // Inisialisasi DataTable baru
        var table = $('.datatable').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json'
            },
            responsive: true,
            dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>t<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
            pageLength: 10,
            order: [[0, 'desc']],
            destroy: true // Pastikan opsi destroy diaktifkan
        });
        
        // Custom search
        $('#customSearch').keyup(function() {
            table.search($(this).val()).draw();
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