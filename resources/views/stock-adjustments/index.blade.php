@extends('layouts.app')

@section('title', 'Penyesuaian Stok')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-dark fw-bold">
                <i class="fas fa-clipboard-list me-2 text-primary"></i> Penyesuaian Stok
            </h1>
            <p class="text-muted">Kelola dan pantau penyesuaian stok produk</p>
        </div>
        @can('adjust stock warehouses')
        <a href="{{ route('stock-adjustments.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Penyesuaian Baru
        </a>
        @endcan
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 fw-bold text-primary">Semua Penyesuaian Stok</h6>
            <div class="input-group" style="width: 250px;">
                <span class="input-group-text bg-light border-0">
                    <i class="fas fa-search text-primary"></i>
                </span>
                <input type="text" id="customSearch" class="form-control border-0 bg-light" placeholder="Cari penyesuaian...">
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
                            <th>Referensi</th>
                            <th>Tipe</th>
                            <th>Lokasi</th>
                            <th>Dibuat Oleh</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($stockAdjustments as $adjustment)
                        <tr>
                            <td>{{ $adjustment->date->format('d/m/Y') }}</td>
                            <td><span class="fw-medium">{{ $adjustment->reference }}</span></td>
                            <td>
                                @if($adjustment->type === 'warehouse')
                                    <span class="badge bg-primary-light text-primary rounded-pill px-2">Gudang</span>
                                @else
                                    <span class="badge bg-info-light text-info rounded-pill px-2">Toko</span>
                                @endif
                            </td>
                            <td>
                                @if($adjustment->type === 'warehouse')
                                    Gudang
                                @else
                                    {{ $adjustment->store->name }}
                                @endif
                            </td>
                            <td>{{ $adjustment->creator->name }}</td>
                            <td>
                                <a href="{{ route('stock-adjustments.show', $adjustment) }}" class="btn btn-sm btn-outline-info" data-bs-toggle="tooltip" title="Lihat Detail">
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