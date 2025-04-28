@extends('layouts.app')

@section('title', 'Pembelian')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-dark fw-bold">
                <i class="fas fa-shopping-cart me-2 text-primary"></i> Pembelian
            </h1>
            <p class="text-muted">Kelola transaksi pembelian dari pemasok</p>
        </div>
        @can('create purchases')
        <a href="{{ route('purchases.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Pembelian Baru
        </a>
        @endcan
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 fw-bold text-primary">Daftar Pembelian</h6>
            <div class="input-group" style="width: 250px;">
                <span class="input-group-text bg-light border-0">
                    <i class="fas fa-search text-primary"></i>
                </span>
                <input type="text" id="customSearch" class="form-control border-0 bg-light" placeholder="Cari pembelian...">
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
                            <th>Tanggal</th>
                            <th>No. Faktur</th>
                            <th>Pemasok</th>
                            <th>Total</th>
                            <th>Pembayaran</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($purchases as $purchase)
                        <tr>
                            <td>{{ $purchase->date->format('d/m/Y') }}</td>
                            <td>
                                <span class="fw-medium">{{ $purchase->invoice_number }}</span>
                            </td>
                            <td>{{ $purchase->supplier->name }}</td>
                            <td>Rp {{ number_format($purchase->total_amount, 0, ',', '.') }}</td>
                            <td>
                                @if($purchase->payment_type === 'tunai')
                                    <span class="badge bg-success-light text-success rounded-pill px-2">Tunai</span>
                                @else
                                    <span class="badge bg-warning-light text-warning rounded-pill px-2">Tempo</span>
                                    @if($purchase->due_date)
                                        <br><small class="text-muted">Jatuh tempo: {{ $purchase->due_date->format('d/m/Y') }}</small>
                                    @endif
                                @endif
                            </td>
                            <td>
                                @if($purchase->status === 'pending')
                                    <span class="badge bg-warning-light text-warning rounded-pill px-2">Tertunda</span>
                                @elseif($purchase->status === 'confirmed')
                                    <span class="badge bg-info-light text-info rounded-pill px-2">Menunggu Konfirmasi Gudang</span>
                                @elseif($purchase->status === 'complete')
                                    <span class="badge bg-success-light text-success rounded-pill px-2">Selesai</span>
                                @elseif($purchase->status === 'partial')
                                    <span class="badge bg-info-light text-info rounded-pill px-2">Sebagian</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('purchases.show', $purchase) }}" class="btn btn-sm btn-outline-info" data-bs-toggle="tooltip" title="Lihat Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>

                                    @if($purchase->status === 'pending')
                                        @can('edit purchases')
                                        <a href="{{ route('purchases.edit', $purchase) }}" class="btn btn-sm btn-outline-primary" data-bs-toggle="tooltip" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @endcan

                                        @can('delete purchases')
                                        <button type="button" class="btn btn-sm btn-outline-danger delete-btn"
                                                data-bs-toggle="tooltip" title="Hapus"
                                                data-id="{{ $purchase->id }}"
                                                data-invoice="{{ $purchase->invoice_number }}">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                        <form id="delete-form-{{ $purchase->id }}" action="{{ route('purchases.destroy', $purchase) }}" method="POST" class="d-none">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                        @endcan

                                        @can('edit purchases')
                                        <button type="button" class="btn btn-sm btn-outline-success confirm-btn"
                                                data-bs-toggle="tooltip" title="Konfirmasi"
                                                data-id="{{ $purchase->id }}"
                                                data-invoice="{{ $purchase->invoice_number }}">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        <form id="confirm-form-{{ $purchase->id }}" action="{{ route('purchases.confirm', $purchase) }}" method="POST" class="d-none">
                                            @csrf
                                        </form>
                                        @endcan
                                    @endif

                                    @if($purchase->status !== 'pending')
                                        <a href="{{ route('purchases.receipt', $purchase) }}" class="btn btn-sm btn-outline-secondary" target="_blank" data-bs-toggle="tooltip" title="Cetak">
                                            <i class="fas fa-print"></i>
                                        </a>

                                        @can('create purchase returns')
                                        <a href="{{ route('purchase-returns.create-from-purchase', $purchase) }}" class="btn btn-sm btn-outline-warning" data-bs-toggle="tooltip" title="Retur">
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
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Konfirmasi Hapus</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-3">
                    <i class="fas fa-exclamation-circle text-danger fa-5x mb-3"></i>
                    <h5>Apakah Anda yakin ingin menghapus pembelian ini?</h5>
                    <p class="text-muted">Faktur: <span id="purchase-invoice" class="fw-bold"></span></p>
                    <p class="small text-danger">Tindakan ini tidak dapat dibatalkan dan akan menghapus semua data terkait</p>
                </div>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-danger" id="confirm-delete">
                    <i class="fas fa-trash me-1"></i> Hapus Pembelian
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Confirm Purchase Modal -->
<div class="modal fade" id="confirmPurchaseModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmModalLabel">Konfirmasi Pembelian</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-3">
                    <i class="fas fa-question-circle text-primary fa-5x mb-3"></i>
                    <h5>Apakah Anda yakin ingin mengkonfirmasi pembelian ini?</h5>
                    <p class="text-muted">Faktur: <span id="confirm-purchase-invoice" class="fw-bold"></span></p>
                    <p class="small text-warning">Tindakan ini akan memproses pembelian dan memperbarui stok</p>
                </div>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-success" id="confirm-purchase">
                    <i class="fas fa-check me-1"></i> Konfirmasi Pembelian
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
            order: [[0, 'desc']],
            destroy: true // Pastikan opsi destroy diaktifkan
        });

        // Custom search
        $('#customSearch').keyup(function() {
            table.search($(this).val()).draw();
        });

        // Delete confirmation
        $('.delete-btn').click(function() {
            var id = $(this).data('id');
            var invoice = $(this).data('invoice');

            $('#purchase-invoice').text(invoice);
            $('#confirm-delete').data('id', id);
            $('#deleteModal').modal('show');
        });

        $('#confirm-delete').click(function() {
            var id = $(this).data('id');
            $('#delete-form-' + id).submit();
        });

        // Confirm purchase
        $('.confirm-btn').click(function() {
            var id = $(this).data('id');
            var invoice = $(this).data('invoice');

            $('#confirm-purchase-invoice').text(invoice);
            $('#confirm-purchase').data('id', id);
            $('#confirmPurchaseModal').modal('show');
        });

        $('#confirm-purchase').click(function() {
            var id = $(this).data('id');
            $('#confirm-form-' + id).submit();
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
