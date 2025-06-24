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
        <div class="card-header py-3">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h6 class="m-0 fw-bold text-primary">
                        @if($canSelectStore)
                            Stok Toko
                        @else
                            Stok di Toko: {{ $selectedStore->name ?? 'Tidak Ada Toko' }}
                        @endif
                    </h6>
                </div>
                <div class="col-md-6">
                    <div class="row g-2">
                        @if($canSelectStore)
                        <div class="col-md-7">
                            <form method="GET" action="{{ route('stock.store') }}" id="storeSelectForm">
                                <select name="store_id" class="form-select" onchange="document.getElementById('storeSelectForm').submit()">
                                    <option value="">-- Pilih Toko --</option>
                                    @foreach($stores as $store)
                                        <option value="{{ $store->id }}" 
                                                {{ $selectedStore && $selectedStore->id == $store->id ? 'selected' : '' }}>
                                            {{ $store->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </form>
                        </div>
                        <div class="col-md-5">
                        @else
                        <div class="col-md-12">
                        @endif
                            <div class="input-group">
                                <span class="input-group-text bg-light border-0">
                                    <i class="fas fa-search text-primary"></i>
                                </span>
                                <input type="text" id="customSearch" class="form-control border-0 bg-light" placeholder="Cari produk...">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body">
            @if(!$selectedStore)
                <div class="alert alert-info d-flex align-items-center" role="alert">
                    <i class="fas fa-info-circle me-2"></i>
                    <div>Silakan pilih toko terlebih dahulu untuk melihat stok produk.</div>
                </div>
            @elseif($products->isEmpty())
                <div class="alert alert-warning d-flex align-items-center" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <div>Tidak ada stok produk ditemukan untuk toko {{ $selectedStore->name }}.</div>
                </div>
            @else
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
                            @php
                                $productDeleted = $stock->product && $stock->product->deleted_at;
                            @endphp
                            <tr class="{{ $productDeleted ? 'table-secondary' : '' }}">
                                <td>
                                    <span class="fw-medium">
                                        {{ $stock->product ? $stock->product->code : 'N/A' }}
                                    </span>
                                    @if($productDeleted)
                                        <span class="badge bg-danger ms-1">Dihapus</span>
                                    @endif
                                </td>
                                <td>
                                    @if(!$stock->product)
                                        <span class="text-muted">Produk Tidak Tersedia</span>
                                    @elseif($productDeleted)
                                        <span>{{ $stock->product->name }}</span>
                                        <small class="text-danger d-block">
                                            Dihapus pada: {{ $stock->product->deleted_at->format('d/m/Y H:i') }}
                                        </small>
                                    @else
                                        <a href="{{ route('products.show', $stock->product) }}">
                                            {{ $stock->product->name }}
                                        </a>
                                    @endif
                                </td>
                                <td>
                                    @if($stock->product && $stock->product->category)
                                        <span class="badge bg-primary-light text-primary rounded-pill px-2">
                                            {{ $stock->product->category->name }}
                                        </span>
                                    @else
                                        <span class="badge bg-secondary rounded-pill px-2">Tidak Terkategori</span>
                                    @endif
                                </td>
                                <td>{{ $stock->unit ? $stock->unit->name : 'N/A' }}</td>
                                <td>{{ intval($stock->quantity) }} {{ $stock->unit ? $stock->unit->name : '' }}</td>
                                <td>{{ $stock->product ? intval($stock->product->min_stock) : 'N/A' }}</td>
                                <td>
                                    @if(!$stock->product)
                                        <span class="badge bg-secondary rounded-pill px-2">Produk Tidak Tersedia</span>
                                    @elseif($productDeleted)
                                        <span class="badge bg-danger rounded-pill px-2">Produk Dihapus</span>
                                    @elseif($stock->quantity <= 0)
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
            @endif
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Inisialisasi DataTable hanya jika tabel exists dan memiliki data
    if ($('#stockTable').length && $('#stockTable tbody tr').length > 0) {
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
    } else {
        // Jika tidak ada tabel, custom search tetap bisa bekerja untuk elemen biasa
        $('#customSearch').keyup(function() {
            // Tidak ada action karena tidak ada tabel
        });
    }
});
</script>
@endsection
