@extends('layouts.app')

@section('title', 'Stok Gudang')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-dark fw-bold">
                <i class="fas fa-warehouse me-2 text-primary"></i> Stok Gudang
            </h1>
            <p class="text-muted">Kelola dan pantau stok produk di gudang</p>
        </div>
        <div>
            @can('adjust stock warehouses')
            <a href="{{ route('stock-adjustments.create') }}" class="btn btn-primary">
                <i class="fas fa-edit me-1"></i> Sesuaikan Stok
            </a>
            @endcan
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 fw-bold text-primary">Semua Produk di Gudang</h6>
            <div class="input-group" style="width: 250px;">
                <span class="input-group-text bg-light border-0">
                    <i class="fas fa-search text-primary"></i>
                </span>
                <input type="text" id="customSearch" class="form-control border-0 bg-light" placeholder="Cari produk...">
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover datatable" width="100%" cellspacing="0">
                    <thead class="table-light">
                        <tr>
                            <th>Kode</th>
                            <th>Produk</th>
                            <th>Kategori</th>
                            <th>Stok Saat Ini</th>
                            <th>Stok Minimum</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($products as $product)
                        <tr>
                            <td><span class="fw-medium">{{ $product->code }}</span></td>
                            <td>
                                <a href="{{ route('products.show', $product) }}" class="text-primary">
                                    {{ $product->name }}
                                </a>
                            </td>
                            <td>
                                <span class="badge bg-primary-light text-primary rounded-pill px-2">
                                    {{ $product->category->name }}
                                </span>
                            </td>
                            <td>
                                <span class="fw-medium">{{ $product->stock_quantity }}</span> {{ $product->baseUnit->name }}
                            </td>
                            <td>{{ $product->min_stock }} {{ $product->baseUnit->name }}</td>
                            <td>
                                @if($product->stock_quantity <= 0)
                                    <span class="badge bg-danger-light text-danger rounded-pill px-2">Stok Habis</span>
                                @elseif($product->stock_quantity < $product->min_stock)
                                    <span class="badge bg-warning-light text-warning rounded-pill px-2">Stok Rendah</span>
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

    <div class="row">
        <div class="col-md-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex align-items-center">
                    <i class="fas fa-chart-pie text-primary me-2"></i>
                    <h6 class="m-0 fw-bold text-primary">Ringkasan Stok</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <div class="card border-0 bg-success">
                                <div class="card-body py-3 text-center">
                                    <div class="text-white">
                                        <h6 class="mb-0 small">Tersedia</h6>
                                        <h4 class="mb-0 fw-bold">{{ $products->where('stock_quantity', '>=', DB::raw('min_stock'))->count() }}</h4>
                                        <small>produk</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="card border-0 bg-warning">
                                <div class="card-body py-3 text-center">
                                    <div class="text-white">
                                        <h6 class="mb-0 small">Stok Rendah</h6>
                                        <h4 class="mb-0 fw-bold">{{ $products->where('stock_quantity', '>', 0)->where('stock_quantity', '<', DB::raw('min_stock'))->count() }}</h4>
                                        <small>produk</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="card border-0 bg-danger">
                                <div class="card-body py-3 text-center">
                                    <div class="text-white">
                                        <h6 class="mb-0 small">Stok Habis</h6>
                                        <h4 class="mb-0 fw-bold">{{ $products->where('stock_quantity', '<=', 0)->count() }}</h4>
                                        <small>produk</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex align-items-center">
                    <i class="fas fa-exclamation-triangle text-primary me-2"></i>
                    <h6 class="m-0 fw-bold text-primary">Produk Perlu Perhatian</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Produk</th>
                                    <th>Stok Saat Ini</th>
                                    <th>Min. Stok</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                $lowStockProducts = $products->where('stock_quantity', '<', DB::raw('min_stock'))->take(5);
                                @endphp
                                
                                @forelse($lowStockProducts as $product)
                                <tr>
                                    <td>
                                        <a href="{{ route('products.show', $product) }}" class="text-primary">
                                            {{ $product->name }}
                                        </a>
                                    </td>
                                    <td>
                                        <span class="fw-medium">{{ $product->stock_quantity }}</span> {{ $product->baseUnit->name }}
                                    </td>
                                    <td>{{ $product->min_stock }} {{ $product->baseUnit->name }}</td>
                                    <td>
                                        @if($product->stock_quantity <= 0)
                                            <span class="badge bg-danger-light text-danger rounded-pill px-2">Stok Habis</span>
                                        @else
                                            <span class="badge bg-warning-light text-warning rounded-pill px-2">Stok Rendah</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center py-3 text-muted">Semua produk memiliki stok yang cukup.</td>
                                </tr>
                                @endforelse
                            </tbody>
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
            order: [[3, 'asc']],
            destroy: true // Pastikan opsi destroy diaktifkan
        });
        
        // Custom search
        $('#customSearch').keyup(function() {
            table.search($(this).val()).draw();
        });
    });
</script>
@endsection