@extends('layouts.app')

@section('title', 'Pembelian')

@section('content')
<div class="container-fluid">
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-dark fw-bold">
                <i class="fas fa-shopping-cart me-2 text-primary"></i> Pembelian
            </h1>
            <p class="text-muted">Kelola transaksi pembelian dari pemasok</p>

            <!-- Store Info for Store Admin -->
            @if(Auth::user()->store_id && !Auth::user()->hasRole(['admin_back_office', 'admin_gudang', 'owner']))
                <div class="alert alert-info alert-sm d-inline-flex align-items-center mt-2" style="padding: 6px 12px; font-size: 0.9em;">
                    <i class="fas fa-store me-2"></i>
                    <span>Anda adalah <strong>{{ Auth::user()->getRoleNames()->first() }}</strong> di cabang <strong>{{ Auth::user()->store->name }}</strong></span>
                </div>
            @endif
        </div>

        @can('create purchases')
        <a href="{{ route('purchases.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Pembelian Baru
        </a>
        @endcan
    </div>

    <!-- Main Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 fw-bold text-primary">Daftar Pembelian</h6>
        </div>

        <div class="card-body">
            <!-- Alert Messages -->
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

            <!-- Data Table -->
            <div class="table-responsive">
                <table class="table table-hover" id="purchases-table" width="100%" cellspacing="0">
                    <thead class="table-light">
                        <tr>
                            <th>Tanggal</th>
                            <th>No. Faktur</th>
                            <th>Pemasok</th>
                            <th>Total</th>
                            <th>Pembayaran</th>
                            <th>Status</th>
                            <th width="200">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($purchases as $purchase)
                            @php
                                // Calculate user permissions once per row
                                $user = Auth::user();
                                $isPending = $purchase->status === 'pending';

                                // Role-based permissions
                                if ($user->hasRole(['admin_back_office', 'admin_gudang', 'owner'])) {
                                    // PUSAT roles can access all purchases
                                    $canEdit = $user->can('edit purchases') && $isPending;
                                    $canDelete = $user->can('delete purchases') && $isPending;
                                    $canConfirm = $user->can('edit purchases') && $isPending;
                                } elseif ($user->hasRole(['admin_store'])) {
                                    // CABANG roles can only access their store purchases
                                    $storeMatch = $user->store_id === ($purchase->store_id ?? null);
                                    $canEdit = $user->can('edit purchases') && $isPending && $storeMatch;
                                    $canDelete = $user->can('delete purchases') && $isPending && $storeMatch;
                                    $canConfirm = $user->can('edit purchases') && $isPending && $storeMatch;
                                } else {
                                    $canEdit = false;
                                    $canDelete = false;
                                    $canConfirm = false;
                                }
                            @endphp

                            <tr>
                                <td>{{ $purchase->date->format('d/m/Y') }}</td>
                                <td>
                                    <span class="fw-medium">{{ $purchase->invoice_number }}</span>
                                </td>
                                <td>{{ $purchase->supplier->name }}</td>
                                <td>Rp {{ number_format($purchase->total_amount, 0, ',', '.') }}</td>
                                <td>
                                    @if($purchase->payment_type === 'tunai')
                                        <span class="badge bg-success text-white rounded-pill px-2">Tunai</span>
                                    @else
                                        <span class="badge bg-warning text-dark rounded-pill px-2">Tempo</span>
                                        @if($purchase->due_date)
                                            <br><small class="text-muted">{{ $purchase->due_date->format('d/m/Y') }}</small>
                                        @endif
                                    @endif
                                </td>
                                <td>
                                    @switch($purchase->status)
                                        @case('pending')
                                            <span class="badge bg-warning text-dark rounded-pill px-2">Tertunda</span>
                                            @break
                                        @case('confirmed')
                                            <span class="badge bg-info text-white rounded-pill px-2">Dikonfirmasi</span>
                                            @break
                                        @case('complete')
                                            <span class="badge bg-success text-white rounded-pill px-2">Selesai</span>
                                            @break
                                        @case('partial')
                                            <span class="badge bg-secondary text-white rounded-pill px-2">Sebagian</span>
                                            @break
                                        @default
                                            <span class="badge bg-light text-dark rounded-pill px-2">{{ ucfirst($purchase->status) }}</span>
                                    @endswitch
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <!-- View Button (Always Available) -->
                                        <a href="{{ route('purchases.show', $purchase) }}"
                                           class="btn btn-sm btn-outline-info"
                                           data-bs-toggle="tooltip"
                                           title="Lihat Detail">
                                            <i class="fas fa-eye"></i>
                                        </a>

                                        <!-- Pending Status Actions -->
                                        @if($isPending)
                                            @if($canEdit)
                                                <a href="{{ route('purchases.edit', $purchase) }}"
                                                   class="btn btn-sm btn-outline-warning"
                                                   data-bs-toggle="tooltip"
                                                   title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            @endif

                                            @if($canDelete)
                                                <button type="button"
                                                        class="btn btn-sm btn-outline-danger btn-delete"
                                                        data-purchase-id="{{ $purchase->id }}"
                                                        data-invoice="{{ $purchase->invoice_number }}"
                                                        data-bs-toggle="tooltip"
                                                        title="Hapus">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            @endif

                                            @if($canConfirm)
                                                <button type="button"
                                                        class="btn btn-sm btn-outline-success btn-confirm"
                                                        data-purchase-id="{{ $purchase->id }}"
                                                        data-invoice="{{ $purchase->invoice_number }}"
                                                        data-bs-toggle="tooltip"
                                                        title="Konfirmasi">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            @endif
                                        @endif

                                        <!-- Non-Pending Status Actions -->
                                        @if(!$isPending)
                                            <a href="{{ route('purchases.receipt', $purchase) }}"
                                               class="btn btn-sm btn-outline-secondary"
                                               target="_blank"
                                               data-bs-toggle="tooltip"
                                               title="Cetak">
                                                <i class="fas fa-print"></i>
                                            </a>

                                            @can('create purchase returns')
                                                <a href="{{ route('purchase-returns.create-from-purchase', $purchase) }}"
                                                   class="btn btn-sm btn-outline-info"
                                                   data-bs-toggle="tooltip"
                                                   title="Retur">
                                                    <i class="fas fa-undo"></i>
                                                </a>
                                            @endcan
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
                    Konfirmasi Hapus Pembelian
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-trash fa-3x text-danger"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="mb-2">Apakah Anda yakin ingin menghapus pembelian ini?</h6>
                        <p class="mb-1"><strong>Nomor Faktur:</strong> <span id="purchase-invoice-delete" class="text-danger"></span></p>
                        <div class="alert alert-warning mt-3 mb-0">
                            <small><i class="fas fa-info-circle me-1"></i> Tindakan ini tidak dapat dibatalkan dan akan menghapus semua data terkait!</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Batal
                </button>
                <button type="button" class="btn btn-danger" id="confirm-delete-btn">
                    <i class="fas fa-trash me-1"></i> Ya, Hapus Pembelian
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Confirm Purchase Modal -->
<div class="modal fade" id="confirmPurchaseModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="confirmModalLabel">
                    <i class="fas fa-check-circle me-2"></i>
                    Konfirmasi Pembelian
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-check-circle fa-3x text-success"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="mb-2">Apakah Anda yakin ingin mengkonfirmasi pembelian ini?</h6>
                        <p class="mb-1"><strong>Nomor Faktur:</strong> <span id="purchase-invoice-confirm" class="text-success"></span></p>
                        <div class="alert alert-info mt-3 mb-0">
                            <small><i class="fas fa-info-circle me-1"></i> Tindakan ini akan memproses pembelian dan mengirim notifikasi ke admin gudang!</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Batal
                </button>
                <button type="button" class="btn btn-success" id="confirm-purchase-btn">
                    <i class="fas fa-check me-1"></i> Ya, Konfirmasi Pembelian
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Hidden Forms -->
<form id="delete-form" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

<form id="confirm-form" method="POST" style="display: none;">
    @csrf
</form>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    console.log('Initializing purchases page...');

    // Initialize DataTable
    try {
        var table = $('#purchases-table').DataTable({
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
    } catch (error) {
        console.error('Error initializing DataTable:', error);
    }

    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl, {
            trigger: 'hover'
        });
    });

    // Delete Purchase Handler
    $(document).on('click', '.btn-delete', function(e) {
        e.preventDefault();

        var purchaseId = $(this).data('purchase-id');
        var invoiceNumber = $(this).data('invoice');

        console.log('Delete clicked - Purchase ID:', purchaseId, 'Invoice:', invoiceNumber);

        if (!purchaseId || !invoiceNumber) {
            alert('Error: Data tidak lengkap');
            return;
        }

        // Set modal content
        $('#purchase-invoice-delete').text(invoiceNumber);

        // Set form action
        var deleteUrl = '{{ url("/purchases") }}/' + purchaseId;
        $('#delete-form').attr('action', deleteUrl);

        // Show modal
        var deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
        deleteModal.show();
    });

    // Confirm Delete Handler
    $('#confirm-delete-btn').on('click', function() {
        var $btn = $(this);

        // Show loading state
        $btn.html('<i class="fas fa-spinner fa-spin me-1"></i> Menghapus...');
        $btn.prop('disabled', true);

        // Submit form
        setTimeout(function() {
            $('#delete-form').submit();
        }, 500);
    });

    // Confirm Purchase Handler
    $(document).on('click', '.btn-confirm', function(e) {
        e.preventDefault();

        var purchaseId = $(this).data('purchase-id');
        var invoiceNumber = $(this).data('invoice');

        console.log('Confirm clicked - Purchase ID:', purchaseId, 'Invoice:', invoiceNumber);

        if (!purchaseId || !invoiceNumber) {
            alert('Error: Data tidak lengkap');
            return;
        }

        // Set modal content
        $('#purchase-invoice-confirm').text(invoiceNumber);

        // Set form action
        var confirmUrl = '{{ url("/purchases") }}/' + purchaseId + '/confirm';
        $('#confirm-form').attr('action', confirmUrl);

        // Show modal
        var confirmModal = new bootstrap.Modal(document.getElementById('confirmPurchaseModal'));
        confirmModal.show();
    });

    // Confirm Purchase Submit Handler
    $('#confirm-purchase-btn').on('click', function() {
        var $btn = $(this);

        // Show loading state
        $btn.html('<i class="fas fa-spinner fa-spin me-1"></i> Mengkonfirmasi...');
        $btn.prop('disabled', true);

        // Submit form
        setTimeout(function() {
            $('#confirm-form').submit();
        }, 500);
    });

    // Reset modals when closed
    $('.modal').on('hidden.bs.modal', function() {
        $('#confirm-delete-btn').html('<i class="fas fa-trash me-1"></i> Ya, Hapus Pembelian');
        $('#confirm-delete-btn').prop('disabled', false);

        $('#confirm-purchase-btn').html('<i class="fas fa-check me-1"></i> Ya, Konfirmasi Pembelian');
        $('#confirm-purchase-btn').prop('disabled', false);
    });

    console.log('Purchases page initialization complete');
});
</script>
@endsection
