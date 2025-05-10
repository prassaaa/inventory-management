@extends('layouts.app')

@section('title', 'Produk yang Terhapus')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-dark fw-bold">
                <i class="fas fa-trash-alt me-2 text-warning"></i> Produk Terhapus
            </h1>
            <p class="text-muted">Kelola produk-produk yang telah dihapus</p>
        </div>
        <div>
            <a href="{{ route('products.index') }}" class="btn btn-primary">
                <i class="fas fa-arrow-left me-1"></i> Kembali ke Daftar Produk
            </a>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 fw-bold text-warning">Daftar Produk Terhapus</h6>
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
                <table id="trashed-products-table" class="table table-hover" width="100%" cellspacing="0">
                    <thead class="table-light">
                        <tr>
                            <th>Kode</th>
                            <th>Nama</th>
                            <th>Kategori</th>
                            <th>Satuan Dasar</th>
                            <th>Harga Beli</th>
                            <th>Harga Jual</th>
                            <th>Dihapus Pada</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($trashedProducts as $product)
                        <tr>
                            <td><span class="fw-medium">{{ $product->code }}</span></td>
                            <td>{{ $product->name }}</td>
                            <td>
                                <span class="badge bg-primary-light text-primary rounded-pill px-2">
                                    {{ $product->category->name ?? 'N/A' }}
                                </span>
                            </td>
                            <td>{{ $product->baseUnit->name ?? 'N/A' }}</td>
                            <td>Rp {{ number_format($product->purchase_price, 0, ',', '.') }}</td>
                            <td>Rp {{ number_format($product->selling_price, 0, ',', '.') }}</td>
                            <td>{{ $product->deleted_at->format('d M Y, H:i') }}</td>
                            <td>
                                <div class="btn-group" role="group">
                                    <form action="{{ route('products.restore', $product->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-sm btn-outline-success" data-bs-toggle="tooltip" title="Pulihkan" onclick="return confirm('Apakah Anda yakin ingin memulihkan produk ini?')">
                                            <i class="fas fa-trash-restore"></i>
                                        </button>
                                    </form>
                                    
                                    <form action="{{ route('products.force-delete', $product->id) }}" method="POST" class="d-inline ms-1">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" data-bs-toggle="tooltip" title="Hapus Permanen" onclick="return confirm('PERHATIAN! Produk akan dihapus secara permanen dan tidak dapat dikembalikan. Lanjutkan?')">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
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
        try {
            $('#trashed-products-table').DataTable({
                language: {
                    "emptyTable": "Tidak ada data yang tersedia pada tabel ini",
                    "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ entri",
                    "infoEmpty": "Menampilkan 0 sampai 0 dari 0 entri",
                    "infoFiltered": "(disaring dari _MAX_ entri keseluruhan)",
                    "lengthMenu": "Tampilkan _MENU_ entri",
                    "loadingRecords": "Sedang memuat...",
                    "processing": "Sedang memproses...",
                    "search": "Cari:",
                    "zeroRecords": "Tidak ditemukan data yang sesuai",
                    "paginate": {
                        "first": "Pertama",
                        "last": "Terakhir",
                        "next": "Selanjutnya",
                        "previous": "Sebelumnya"
                    }
                },
                responsive: true,
                dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>t<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
                pageLength: 10
            });
        } catch (error) {
            console.error("Error initializing DataTable:", error);
        }

        // Initialize tooltips
        try {
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl, {
                    trigger: 'hover'
                });
            });
        } catch(error) {
            console.error("Error initializing tooltips:", error);
        }
    });
</script>
@endsection