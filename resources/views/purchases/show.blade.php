@extends('layouts.app')

@section('title', 'Detail Pembelian')

@section('content')
<div class="container-fluid">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-0 text-dark fw-bold">
                <i class="fas fa-shopping-cart me-2 text-primary"></i> Detail Pembelian
            </h1>
            <p class="text-muted">Informasi lengkap untuk pembelian: <span class="fw-medium">{{ $purchase->invoice_number }}</span></p>
        </div>
        <div class="d-flex gap-2">
            @if($purchase->status === 'pending')
                @can('edit purchases')
                <a href="{{ route('purchases.edit', $purchase) }}" class="btn btn-primary">
                    <i class="fas fa-edit me-1"></i> Edit
                </a>

                <button type="button" class="btn btn-success confirm-btn"
                        data-id="{{ $purchase->id }}"
                        data-invoice="{{ $purchase->invoice_number }}">
                    <i class="fas fa-check me-1"></i> Konfirmasi
                </button>
                <form id="confirm-form-{{ $purchase->id }}" action="{{ route('purchases.confirm', $purchase) }}" method="POST" class="d-none">
                    @csrf
                </form>
                @endcan
            @else
                <a href="{{ route('purchases.receipt', $purchase) }}" class="btn btn-outline-secondary" target="_blank">
                    <i class="fas fa-print me-1"></i> Cetak
                </a>

                @can('create purchase returns')
                <a href="{{ route('purchase-returns.create-from-purchase', $purchase) }}" class="btn btn-warning">
                    <i class="fas fa-undo me-1"></i> Retur
                </a>
                @endcan
            @endif

            <a href="{{ route('purchases.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Kembali
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex align-items-center">
                    <i class="fas fa-info-circle text-primary me-2"></i>
                    <h6 class="m-0 fw-bold text-primary">Informasi Pembelian</h6>
                </div>
                <div class="card-body">
                    <table class="table">
                        <tr>
                            <th class="border-0 ps-0" style="width: 200px;">Nomor Faktur</th>
                            <td class="border-0 fw-medium">{{ $purchase->invoice_number }}</td>
                        </tr>
                        <tr>
                            <th class="border-0 ps-0">Tanggal</th>
                            <td class="border-0">{{ $purchase->date->format('d/m/Y') }}</td>
                        </tr>
                        <tr>
                            <th class="border-0 ps-0">Pemasok</th>
                            <td class="border-0">
                                <a href="{{ route('suppliers.show', $purchase->supplier) }}" class="text-primary">
                                    {{ $purchase->supplier->name }}
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <th class="border-0 ps-0">Jenis Pembayaran</th>
                            <td class="border-0">
                                @if($purchase->payment_type === 'tunai')
                                    <span class="badge bg-success-light text-success rounded-pill px-2">Tunai</span>
                                @else
                                    <span class="badge bg-warning-light text-warning rounded-pill px-2">Tempo</span>
                                @endif
                            </td>
                        </tr>
                        @if($purchase->payment_type === 'tempo')
                        <tr>
                            <th class="border-0 ps-0">Tanggal Jatuh Tempo</th>
                            <td class="border-0">{{ $purchase->due_date->format('d/m/Y') }}</td>
                        </tr>
                        @endif
                        <tr>
                            <th class="border-0 ps-0">Status</th>
                            <td class="border-0">
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
                        </tr>
                        <tr>
                            <th class="border-0 ps-0">Total</th>
                            <td class="border-0 fw-bold text-primary">Rp {{ number_format($purchase->total_amount, 0, ',', '.') }}</td>
                        </tr>
                        @if($purchase->note)
                        <tr>
                            <th class="border-0 ps-0">Catatan</th>
                            <td class="border-0">{{ $purchase->note }}</td>
                        </tr>
                        @endif
                        <tr>
                            <th class="border-0 ps-0">Dibuat Oleh</th>
                            <td class="border-0">{{ $purchase->creator->name }}</td>
                        </tr>
                        <tr>
                            <th class="border-0 ps-0">Dibuat Pada</th>
                            <td class="border-0">{{ $purchase->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex align-items-center">
                    <i class="fas fa-undo text-primary me-2"></i>
                    <h6 class="m-0 fw-bold text-primary">Retur Pembelian</h6>
                </div>
                <div class="card-body">
                    @if($purchase->purchaseReturns->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Jumlah</th>
                                        <th>Catatan</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($purchase->purchaseReturns as $return)
                                    <tr>
                                        <td>{{ $return->date->format('d/m/Y') }}</td>
                                        <td>Rp {{ number_format($return->total_amount, 0, ',', '.') }}</td>
                                        <td>{{ Str::limit($return->note, 30) ?: '-' }}</td>
                                        <td>
                                            <a href="{{ route('purchase-returns.show', $return) }}" class="btn btn-sm btn-outline-info" data-bs-toggle="tooltip" title="Lihat Detail">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-shopping-basket fa-4x text-muted mb-3"></i>
                            <p class="text-muted">Belum ada retur pembelian untuk transaksi ini.</p>
                        </div>
                    @endif
                </div>
            </div>

            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex align-items-center">
                    <i class="fas fa-chart-pie text-primary me-2"></i>
                    <h6 class="m-0 fw-bold text-primary">Ringkasan</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="card border-0 bg-primary">
                                <div class="card-body py-3">
                                    <div class="d-flex align-items-center">
                                        <div class="icon-circle bg-white me-3">
                                            <i class="fas fa-box fa-lg text-primary"></i>
                                        </div>
                                        <div class="text-white">
                                            <h6 class="mb-0 small">Total Item</h6>
                                            <h5 class="mb-0 fw-bold">{{ $purchase->purchaseDetails->count() }}</h5>
                                            <small>produk</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="card border-0 bg-success">
                                <div class="card-body py-3">
                                    <div class="d-flex align-items-center">
                                        <div class="icon-circle bg-white me-3">
                                            <i class="fas fa-money-bill fa-lg text-success"></i>
                                        </div>
                                        <div class="text-white">
                                            <h6 class="mb-0 small">Total Nilai</h6>
                                            <h5 class="mb-0 fw-bold">Rp {{ number_format($purchase->total_amount, 0, ',', '.') }}</h5>
                                            <small>pembelian</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex align-items-center">
            <i class="fas fa-list text-primary me-2"></i>
            <h6 class="m-0 fw-bold text-primary">Daftar Produk</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" style="width: 50px;">#</th>
                            <th>Produk</th>
                            <th>Jumlah</th>
                            <th>Satuan</th>
                            <th>Harga</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($purchase->purchaseDetails as $index => $detail)
                        <tr>
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td>
                                <a href="{{ route('products.show', $detail->product) }}" class="text-primary">
                                    <strong>{{ $detail->product->code }}</strong> - {{ $detail->product->name }}
                                </a>
                            </td>
                            <td>{{ intval($detail->quantity) }}</td>
                            <td>{{ $detail->unit->name }}</td>
                            <td>Rp {{ number_format($detail->price, 0, ',', '.') }}</td>
                            <td>Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <th colspan="5" class="text-end">Total:</th>
                            <th class="text-primary fw-bold">Rp {{ number_format($purchase->total_amount, 0, ',', '.') }}</th>
                        </tr>
                    </tfoot>
                </table>
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
        // Initialize tooltips
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl, {
                trigger: 'hover'
            });
        });

        // Confirm purchase
        $('.confirm-btn').click(function() {
            console.log('Confirm button clicked');
            var id = $(this).data('id');
            var invoice = $(this).data('invoice');

            $('#confirm-purchase-invoice').text(invoice);
            $('#confirm-purchase').data('id', id);
            $('#confirmPurchaseModal').modal('show');
        });

        $('#confirm-purchase').click(function() {
            console.log('Confirm modal button clicked');
            var id = $(this).data('id');
            console.log('Submitting form with ID: confirm-form-' + id);
            $('#confirm-form-' + id).submit();
        });
    });
</script>
@endsection
