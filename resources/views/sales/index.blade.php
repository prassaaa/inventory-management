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

            <!-- Store Info for Store Admin/Kasir -->
            @if(Auth::user()->store_id && !Auth::user()->hasRole(['admin_back_office', 'admin_gudang', 'owner']))
                <div class="alert alert-info alert-sm d-inline-flex align-items-center mt-2" style="padding: 6px 12px; font-size: 0.9em;">
                    <i class="fas fa-store me-2"></i>
                    <span>Anda adalah <strong>{{ Auth::user()->getRoleNames()->first() }}</strong> di cabang <strong>{{ Auth::user()->store->name }}</strong></span>
                </div>
            @endif
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
        <div class="card-header py-3">
            <h6 class="m-0 fw-bold text-primary">Daftar Penjualan</h6>
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

            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <div>
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="table-responsive">
                <table class="table table-hover" id="sales-table" width="100%" cellspacing="0">
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
                                    <span class="badge bg-success text-white rounded-pill px-2">Lunas</span>
                                @else
                                    <span class="badge bg-warning text-dark rounded-pill px-2">Tertunda</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <!-- View Detail -->
                                    <a href="{{ route('sales.show', $sale) }}" class="btn btn-sm btn-outline-info" data-bs-toggle="tooltip" title="Lihat Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>

                                    <!-- Print Receipt -->
                                    <a href="{{ route('sales.receipt', $sale) }}" class="btn btn-sm btn-outline-secondary" target="_blank" data-bs-toggle="tooltip" title="Cetak">
                                        <i class="fas fa-print"></i>
                                    </a>

                                    <!-- Edit Button -->
                                    @php
                                        $canEdit = false;
                                        // Role PUSAT bisa edit semua
                                        if (Auth::user()->hasRole(['admin_back_office', 'admin_gudang', 'owner'])) {
                                            $canEdit = Auth::user()->can('edit sales');
                                        }
                                        // Role CABANG (admin_store) hanya bisa edit penjualan toko mereka
                                        elseif (Auth::user()->hasRole(['admin_store'])) {
                                            $canEdit = Auth::user()->can('edit sales') &&
                                                      Auth::user()->store_id === $sale->store_id;
                                        }
                                    @endphp

                                    @if($canEdit)
                                        <a href="{{ route('sales.edit', $sale) }}" class="btn btn-sm btn-outline-warning" data-bs-toggle="tooltip" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    @endif

                                    <!-- Delete Button -->
                                    @php
                                        $canDelete = false;
                                        // Role PUSAT bisa hapus semua
                                        if (Auth::user()->hasRole(['admin_back_office', 'admin_gudang', 'owner'])) {
                                            $canDelete = Auth::user()->can('delete sales');
                                        }
                                        // Role CABANG (admin_store) hanya bisa hapus penjualan toko mereka
                                        elseif (Auth::user()->hasRole(['admin_store'])) {
                                            $canDelete = Auth::user()->can('delete sales') &&
                                                        Auth::user()->store_id === $sale->store_id;
                                        }
                                    @endphp

                                    @if($canDelete)
                                        <button type="button" class="btn btn-sm btn-outline-danger btn-delete"
                                                data-sale-id="{{ $sale->id }}"
                                                data-invoice="{{ $sale->invoice_number }}"
                                                data-bs-toggle="tooltip" title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    @endif
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
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteModalLabel">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Konfirmasi Hapus Penjualan
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-trash fa-3x text-danger"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="mb-2">Apakah Anda yakin ingin menghapus penjualan ini?</h6>
                        <p class="mb-1"><strong>Nomor Faktur:</strong> <span id="invoice-to-delete" class="text-danger"></span></p>
                        <div class="alert alert-warning mt-3 mb-0">
                            <small><i class="fas fa-info-circle me-1"></i> Tindakan ini tidak dapat dibatalkan dan stok akan dikembalikan!</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Batal
                </button>
                <button type="button" class="btn btn-danger" id="confirm-delete-btn">
                    <i class="fas fa-trash me-1"></i> Ya, Hapus Penjualan
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Hidden Delete Form -->
<form id="delete-form" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    console.log('Initializing sales page...');

    // Destroy existing DataTable if it exists
    if ($.fn.DataTable.isDataTable('#sales-table')) {
        console.log('Destroying existing DataTable...');
        $('#sales-table').DataTable().clear().destroy();
        $('#sales-table').empty();
    }

    // Wait a bit before reinitializing
    setTimeout(function() {
        console.log('Creating new DataTable...');

        try {
            var table = $('#sales-table').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json'
                },
                responsive: true,
                processing: true,
                dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>t<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
                pageLength: 10,
                order: [[0, 'desc']],
                columnDefs: [
                    { orderable: false, targets: -1 } // Disable sorting on action column
                ]
            });

            console.log('DataTable initialized successfully');

            // Custom search sudah tidak diperlukan karena DataTable punya search sendiri

        } catch (error) {
            console.error('Error initializing DataTable:', error);
            // If DataTable fails, at least make the table functional
            // No custom search needed since we removed the input
        }
    }, 100);

    // Initialize tooltips
    setTimeout(function() {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl, {
                trigger: 'hover'
            });
        });
    }, 200);

    // Handle delete button click
    $(document).on('click', '.btn-delete', function(e) {
        e.preventDefault();

        var saleId = $(this).data('sale-id');
        var invoiceNumber = $(this).data('invoice');

        console.log('Delete clicked - Sale ID:', saleId, 'Invoice:', invoiceNumber);

        if (!saleId || !invoiceNumber) {
            console.error('Missing sale ID or invoice number');
            alert('Error: Data tidak lengkap');
            return;
        }

        // Set modal content
        $('#invoice-to-delete').text(invoiceNumber);

        // Set form action
        var deleteUrl = "{{ route('sales.destroy', ':id') }}".replace(':id', saleId);
        $('#delete-form').attr('action', deleteUrl);

        console.log('Delete URL:', deleteUrl);

        // Show modal
        try {
            var deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
            deleteModal.show();
        } catch (error) {
            console.error('Error showing modal:', error);
            // Fallback for older Bootstrap versions
            $('#deleteModal').modal('show');
        }
    });

    // Handle confirm delete
    $('#confirm-delete-btn').off('click').on('click', function() {
        var $btn = $(this);

        console.log('Confirm delete clicked');

        // Show loading state
        $btn.html('<i class="fas fa-spinner fa-spin me-1"></i> Menghapus...');
        $btn.prop('disabled', true);

        // Submit delete form
        setTimeout(function() {
            $('#delete-form').submit();
        }, 500);
    });

    // Reset modal when closed
    $('#deleteModal').on('hidden.bs.modal', function() {
        $('#confirm-delete-btn').html('<i class="fas fa-trash me-1"></i> Ya, Hapus Penjualan');
        $('#confirm-delete-btn').prop('disabled', false);
    });

    console.log('Sales page initialization complete');
});

// Global function for debugging
window.testDelete = function(saleId, invoice) {
    console.log('Testing delete with ID:', saleId, 'Invoice:', invoice);
    $('.btn-delete').first().trigger('click');
};
</script>
@endsection
