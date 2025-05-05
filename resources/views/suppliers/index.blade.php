@extends('layouts.app')

@section('title', 'Pemasok')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-dark fw-bold">
                <i class="fas fa-truck me-2 text-primary"></i> Pemasok
            </h1>
            <p class="text-muted">Kelola daftar pemasok produk inventaris Anda</p>
        </div>
        @can('create suppliers')
        <a href="{{ route('suppliers.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Tambah Pemasok
        </a>
        @endcan
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 fw-bold text-primary">Daftar Pemasok</h6>
            <div class="input-group" style="width: 250px;">
                <span class="input-group-text bg-light border-0">
                    <i class="fas fa-search text-primary"></i>
                </span>
                <input type="text" id="customSearch" class="form-control border-0 bg-light" placeholder="Cari pemasok...">
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
                            <th>ID</th>
                            <th>Nama</th>
                            <th>Telepon</th>
                            <th>Email</th>
                            <th>Pembayaran</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($suppliers as $supplier)
                        <tr>
                            <td><span class="fw-medium">{{ $supplier->id }}</span></td>
                            <td>{{ $supplier->name }}</td>
                            <td>{{ $supplier->phone ?: '-' }}</td>
                            <td>{{ $supplier->email ?: '-' }}</td>
                            <td>
                                @if($supplier->payment_term === 'tunai')
                                    <span class="badge bg-success-light text-success rounded-pill px-2">Tunai</span>
                                @else
                                    <span class="badge bg-warning-light text-warning rounded-pill px-2">Tempo</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $supplier->is_active ? 'bg-success-light text-success' : 'bg-danger-light text-danger' }} rounded-pill px-2">
                                    {{ $supplier->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('suppliers.show', $supplier) }}" class="btn btn-sm btn-outline-info" data-bs-toggle="tooltip" title="Lihat Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @can('edit suppliers')
                                    <a href="{{ route('suppliers.edit', $supplier) }}" class="btn btn-sm btn-outline-primary" data-bs-toggle="tooltip" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @endcan

                                    @can('delete suppliers')
                                    <a href="javascript:void(0)" class="btn btn-sm btn-outline-danger delete-btn"
                                       data-bs-toggle="tooltip" title="Hapus"
                                       onclick="showDeleteModal('{{ $supplier->id }}', '{{ $supplier->name }}')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                    <form id="delete-form-{{ $supplier->id }}" action="{{ route('suppliers.destroy', $supplier) }}" method="POST" class="d-none">
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
                    <h5>Apakah Anda yakin ingin menghapus pemasok ini?</h5>
                    <p class="text-muted">Pemasok: <span id="supplier-name" class="fw-bold"></span></p>
                    <p class="small text-danger">Tindakan ini tidak dapat dibatalkan dan mungkin mempengaruhi data transaksi terkait</p>
                </div>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-danger" id="confirm-delete" onclick="confirmDelete()">
                    <i class="fas fa-trash me-1"></i> Hapus Pemasok
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Variabel global untuk menyimpan ID pemasok yang akan dihapus
    var supplierIdToDelete = null;

    // Fungsi untuk menampilkan modal hapus
    function showDeleteModal(id, name) {
        supplierIdToDelete = id;
        document.getElementById('supplier-name').textContent = name;
        var deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
        deleteModal.show();
    }

    // Fungsi untuk mengonfirmasi penghapusan
    function confirmDelete() {
        if (supplierIdToDelete) {
                document.getElementById('delete-form-' + supplierIdToDelete).submit();
        }
    }

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
