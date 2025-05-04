@extends('layouts.app')

@section('title', 'Stok Toko')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-dark fw-bold">
                <i class="fas fa-store me-2 text-primary"></i> Stok Toko
            </h1>
            <p class="text-muted">Kelola stok produk di toko Anda</p>
        </div>
        @can('adjust stock stores')
        <a href="{{ route('stock-adjustments.create') }}?type=store" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Penyesuaian Stok
        </a>
        @endcan
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 fw-bold text-primary">Stok di Toko: {{ $selectedStore->name }}</h6>
            <div class="d-flex align-items-center">
                <div class="input-group">
                    <span class="input-group-text bg-light border-0">
                        <i class="fas fa-search text-primary"></i>
                    </span>
                    <input type="text" id="customSearch" class="form-control border-0 bg-light" placeholder="Cari produk...">
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="stockTable" width="100%" cellspacing="0">
                    <thead class="table-light">
                        <tr>
                            <th>Kode Produk</th>
                            <th>Nama Produk</th>
                            <th>Kategori</th>
                            <th>Satuan</th>
                            <th>Stok</th>
                            <th>Min. Stok</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($products as $stock)
                            <tr>
                                <td><span class="fw-medium">{{ $stock->product->code }}</span></td>
                                <td>
                                    <a href="{{ route('products.show', $stock->product) }}">
                                        {{ $stock->product->name }}
                                    </a>
                                </td>
                                <td>
                                    <span class="badge bg-primary-light text-primary rounded-pill px-2">
                                        {{ $stock->product->category->name }}
                                    </span>
                                </td>
                                <td>{{ $stock->unit->name }}</td>
                                <td>{{ intval($stock->quantity) }} {{ $stock->unit->name }}</td>
                                <td>{{ intval($stock->product->min_stock) }}</td>
                                <td>
                                    @if($stock->quantity <= 0)
                                        <span class="badge bg-danger-light text-danger rounded-pill px-2">Habis</span>
                                    @elseif($stock->quantity < $stock->product->min_stock)
                                        <span class="badge bg-warning-light text-warning rounded-pill px-2">Hampir Habis</span>
                                    @else
                                        <span class="badge bg-success-light text-success rounded-pill px-2">Tersedia</span>
                                    @endif
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
    var table = $('#stockTable').DataTable({
        "paging": true,
        "lengthChange": true,
        "searching": true,
        "ordering": true,
        "info": true,
        "autoWidth": false,
        "responsive": true,
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json"
        }
    });

    // Custom search
    $('#customSearch').keyup(function() {
        table.search($(this).val()).draw();
    });
});
</script>
@endsection
