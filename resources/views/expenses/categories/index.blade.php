@extends('layouts.app')

@section('title', 'Kategori Pengeluaran')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-dark fw-bold">
                <i class="fas fa-tags me-2 text-primary"></i> Kategori Pengeluaran
            </h1>
            <p class="text-muted">Kelola kategori untuk pencatatan pengeluaran keuangan</p>
        </div>
        <div>
            <button type="button" class="btn btn-outline-primary me-2" data-bs-toggle="modal" data-bs-target="#batchModal">
                <i class="fas fa-list me-1"></i> Input Massal
            </button>
            <a href="{{ route('expense-categories.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> Tambah Kategori
            </a>
        </div>
    </div>

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

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 fw-bold text-primary">Daftar Kategori Pengeluaran</h6>
            <div class="input-group" style="width: 250px;">
                <span class="input-group-text bg-light border-0">
                    <i class="fas fa-search text-primary"></i>
                </span>
                <input type="text" id="categorySearch" class="form-control border-0 bg-light" placeholder="Cari kategori...">
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="categoryTable" width="100%" cellspacing="0">
                    <thead class="table-light">
                        <tr>
                            <th width="60">No</th>
                            <th>Nama Kategori</th>
                            <th>Keterangan</th>
                            <th width="120">Status</th>
                            <th width="150">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($categories as $index => $category)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $category->name }}</td>
                                <td>{{ $category->description ?? '-' }}</td>
                                <td>
                                    @if($category->is_active)
                                        <span class="badge bg-success-light text-success rounded-pill px-3">Aktif</span>
                                    @else
                                        <span class="badge bg-danger-light text-danger rounded-pill px-3">Nonaktif</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <a href="{{ route('expense-categories.edit', $category) }}" class="btn btn-sm btn-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-danger"
                                                onclick="confirmDelete('{{ $category->id }}', '{{ $category->name }}')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                        <form id="delete-form-{{ $category->id }}"
                                              action="{{ route('expense-categories.destroy', $category) }}"
                                              method="POST" class="d-none">
                                            @csrf
                                            @method('DELETE')
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

<!-- Modal Input Batch -->
<div class="modal fade" id="batchModal" tabindex="-1" aria-labelledby="batchModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('expense-categories.batch-store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="batchModalLabel">Input Kategori Massal</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted">
                        Masukkan nama-nama kategori pengeluaran, satu kategori per baris. Kategori yang sudah ada akan dilewati.
                    </p>
                    <div class="form-group">
                        <label for="categories" class="form-label">Daftar Kategori</label>
                        <textarea class="form-control" id="categories" name="categories" rows="10" placeholder="Contoh:
                        Biaya Air Kantor
                        Biaya Listrik Kantor
                        Biaya Telepon
                        Biaya Penyusutan Bangunan
                        ..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Semua</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Inisialisasi DataTable
    var table = $('#categoryTable').DataTable({
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
    $('#categorySearch').keyup(function() {
        table.search($(this).val()).draw();
    });
});

// Konfirmasi Hapus
function confirmDelete(id, name) {
    if (confirm('Anda yakin ingin menghapus kategori "' + name + '"?')) {
        document.getElementById('delete-form-' + id).submit();
    }
}
</script>
@endsection
