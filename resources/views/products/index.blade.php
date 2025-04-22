@extends('layouts.app')

@section('title', 'Produk')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-dark fw-bold">
                <i class="fas fa-boxes me-2 text-primary"></i> Produk
            </h1>
            <p class="text-muted">Kelola semua produk inventaris Anda</p>
        </div>
        <div class="d-flex gap-2">
            @can('create products')
            <a href="{{ route('products.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> Tambah Produk
            </a>
            @endcan
            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#importModal">
                <i class="fas fa-file-import me-1"></i> Import
            </button>
            <a href="{{ route('products.export') }}" class="btn btn-info text-white">
                <i class="fas fa-file-export me-1"></i> Export
            </a>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 fw-bold text-primary">Daftar Produk</h6>
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
                            <th>Kode</th>
                            <th>Nama</th>
                            <th>Kategori</th>
                            <th>Harga Beli</th>
                            <th>Harga Jual</th>
                            <th>Stok</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($products as $product)
                        <tr>
                            <td><span class="fw-medium">{{ $product->code }}</span></td>
                            <td>{{ $product->name }}</td>
                            <td>
                                <span class="badge bg-primary-light text-primary rounded-pill px-2">
                                    {{ $product->category->name }}
                                </span>
                            </td>
                            <td>Rp {{ number_format($product->purchase_price, 0, ',', '.') }}</td>
                            <td>Rp {{ number_format($product->selling_price, 0, ',', '.') }}</td>
                            <td>
                                @if ($product->warehouseStock)
                                    <div class="d-flex align-items-center">
                                        {{ $product->warehouseStock->quantity }} {{ $product->baseUnit->name }}
                                        @if ($product->warehouseStock->quantity < $product->min_stock)
                                            <span class="badge bg-danger-light text-danger ms-2">Stok Rendah</span>
                                        @else
                                            <span class="badge bg-success-light text-success ms-2">Tersedia</span>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-danger">0 {{ $product->baseUnit->name }}</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $product->is_active ? 'bg-success-light text-success' : 'bg-danger-light text-danger' }} rounded-pill px-2">
                                    {{ $product->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('products.show', $product) }}" class="btn btn-sm btn-outline-info" data-bs-toggle="tooltip" title="Lihat Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @can('edit products')
                                    <a href="{{ route('products.edit', $product) }}" class="btn btn-sm btn-outline-primary" data-bs-toggle="tooltip" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @endcan
                                    
                                    @can('delete products')
                                    <button type="button" class="btn btn-sm btn-outline-danger delete-btn" 
                                            data-bs-toggle="tooltip" title="Hapus"
                                            data-id="{{ $product->id }}"
                                            data-name="{{ $product->name }}">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    <form id="delete-form-{{ $product->id }}" action="{{ route('products.destroy', $product) }}" method="POST" class="d-none">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                    @endcan
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

<!-- Import Modal -->
<div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="importModalLabel">Import Produk</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('products.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="import_file" class="form-label">File Excel</label>
                        <input type="file" class="form-control" id="import_file" name="import_file" required accept=".xlsx, .xls, .csv">
                        <div class="form-text">Unggah file Excel (.xlsx, .xls) atau CSV.</div>
                    </div>
                    <div class="mb-3">
                        <a href="{{ route('products.import.template') }}" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-download me-1"></i> Unduh Template
                        </a>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-upload me-1"></i> Import
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Konfirmasi Hapus</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-3">
                    <i class="fas fa-exclamation-circle text-danger fa-5x mb-3"></i>
                    <h5>Apakah Anda yakin ingin menghapus produk ini?</h5>
                    <p class="text-muted">Produk: <span id="product-name" class="fw-bold"></span></p>
                    <p class="small text-danger">Tindakan ini tidak dapat dibatalkan</p>
                </div>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-danger" id="confirm-delete">
                    <i class="fas fa-trash me-1"></i> Hapus Produk
                </button>
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
            destroy: true // Pastikan opsi destroy diaktifkan
        });
        
        // Custom search
        $('#customSearch').keyup(function() {
            table.search($(this).val()).draw();
        });
        
        // Delete confirmation
        $('.delete-btn').click(function() {
            var id = $(this).data('id');
            var name = $(this).data('name');
            
            $('#product-name').text(name);
            $('#confirm-delete').data('id', id);
            $('#deleteModal').modal('show');
        });
        
        $('#confirm-delete').click(function() {
            var id = $(this).data('id');
            $('#delete-form-' + id).submit();
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