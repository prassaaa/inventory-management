@extends('layouts.app')

@section('title', 'Daftar Toko')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-dark fw-bold">
                <i class="fas fa-store me-2 text-primary"></i> Toko
            </h1>
            <p class="text-muted">Kelola semua toko dalam jaringan bisnis Anda</p>
        </div>
        @can('create stores')
        <a href="{{ route('stores.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Tambah Toko
        </a>
        @endcan
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 fw-bold text-primary">Daftar Toko</h6>
            <div class="d-flex align-items-center">
                <div class="input-group">
                    <span class="input-group-text bg-light border-0">
                        <i class="fas fa-search text-primary"></i>
                    </span>
                    <input type="text" id="customSearch" class="form-control border-0 bg-light" placeholder="Cari toko...">
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
                <table class="table table-hover" id="storesTable" width="100%" cellspacing="0">
                    <thead class="table-light">
                        <tr>
                            <th width="5%">#</th>
                            <th>Nama Toko</th>
                            <th>Alamat</th>
                            <th>Telepon</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th width="15%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($stores as $index => $store)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td><span class="fw-medium">{{ $store->name }}</span></td>
                                <td>{{ $store->address ?? '-' }}</td>
                                <td>{{ $store->phone ?? '-' }}</td>
                                <td>{{ $store->email ?? '-' }}</td>
                                <td>
                                    @if($store->is_active)
                                        <span class="badge bg-success-light text-success rounded-pill px-2">Aktif</span>
                                    @else
                                        <span class="badge bg-danger-light text-danger rounded-pill px-2">Tidak Aktif</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('stores.show', $store) }}" class="btn btn-sm btn-outline-info" data-bs-toggle="tooltip" title="Lihat Detail">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @can('edit stores')
                                        <a href="{{ route('stores.edit', $store) }}" class="btn btn-sm btn-outline-primary" data-bs-toggle="tooltip" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @endcan
                                        @can('delete stores')
                                        <button type="button" class="btn btn-sm btn-outline-danger delete-btn" 
                                                data-bs-toggle="tooltip" title="Hapus"
                                                data-id="{{ $store->id }}"
                                                data-name="{{ $store->name }}">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                        <form id="delete-form-{{ $store->id }}" action="{{ route('stores.destroy', $store) }}" method="POST" class="d-none">
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
                    <h5>Apakah Anda yakin ingin menghapus toko ini?</h5>
                    <p class="text-muted">Toko: <span id="store-name" class="fw-bold"></span></p>
                    <p class="small text-danger">Tindakan ini tidak dapat dibatalkan</p>
                </div>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-danger" id="confirm-delete">
                    <i class="fas fa-trash me-1"></i> Hapus Toko
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
        if ($.fn.dataTable.isDataTable('#storesTable')) {
            $('#storesTable').DataTable().destroy();
        }
        
        // Inisialisasi DataTable baru
        var table = $('#storesTable').DataTable({
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
            
            $('#store-name').text(name);
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