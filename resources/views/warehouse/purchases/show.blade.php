@extends('layouts.app')

@section('title', 'Warehouse - Purchase Details')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Detail Pembelian</h4>
                </div>
                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    @if (session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h5>Informasi Pembelian</h5>
                            <table class="table table-sm">
                                <tr>
                                    <th width="30%">No. Invoice</th>
                                    <td width="70%">{{ $purchase->invoice_number }}</td>
                                </tr>
                                <tr>
                                    <th>Tanggal</th>
                                    <td>{{ $purchase->date->format('d/m/Y') }}</td>
                                </tr>
                                <tr>
                                    <th>Supplier</th>
                                    <td>{{ $purchase->supplier->name }}</td>
                                </tr>
                                <tr>
                                    <th>Status</th>
                                    <td>
                                        <span class="badge bg-warning">Menunggu Konfirmasi Gudang</span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Metode Pembayaran</th>
                                    <td>{{ $purchase->payment_type == 'tunai' ? 'Tunai' : 'Tempo' }}</td>
                                </tr>
                                @if ($purchase->payment_type == 'tempo')
                                <tr>
                                    <th>Jatuh Tempo</th>
                                    <td>{{ $purchase->due_date->format('d/m/Y') }}</td>
                                </tr>
                                @endif
                                <tr>
                                    <th>Dibuat Oleh</th>
                                    <td>{{ $purchase->creator->name }}</td>
                                </tr>
                                @if ($purchase->note)
                                <tr>
                                    <th>Catatan</th>
                                    <td>{{ $purchase->note }}</td>
                                </tr>
                                @endif
                            </table>
                        </div>
                    </div>

                    <h5>Detail Produk</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Produk</th>
                                    <th>Jumlah</th>
                                    <th>Satuan</th>
                                    <th>Harga</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($purchase->purchaseDetails as $index => $detail)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $detail->product->name }}</td>
                                    <td>{{ $detail->quantity }}</td>
                                    <td>{{ $detail->unit->name }}</td>
                                    <td>{{ number_format($detail->price, 0, ',', '.') }}</td>
                                    <td>{{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="5" class="text-right">Total</th>
                                    <th>{{ number_format($purchase->total_amount, 0, ',', '.') }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div class="mt-4">
                        <button type="button" class="btn btn-primary confirm-receive-btn"
                                data-id="{{ $purchase->id }}"
                                data-invoice="{{ $purchase->invoice_number }}">
                            <i class="fas fa-check"></i> Terima Barang & Perbarui Stok
                        </button>
                        <a href="{{ route('warehouse.purchases.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                        <form id="receive-form-{{ $purchase->id }}" action="{{ route('warehouse.purchases.receive', $purchase->id) }}" method="POST" class="d-none">
                            @csrf
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Confirm Receive Modal -->
<div class="modal fade" id="confirmReceiveModal" tabindex="-1" aria-labelledby="confirmReceiveModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmReceiveModalLabel">Konfirmasi Penerimaan Barang</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-3">
                    <i class="fas fa-question-circle text-primary fa-5x mb-3"></i>
                    <h5>Apakah Anda yakin ingin menerima pembelian ini?</h5>
                    <p class="text-muted">Faktur: <span id="confirm-purchase-invoice" class="fw-bold"></span></p>
                    <p class="small text-warning">Tindakan ini akan memproses pembelian dan memperbarui stok gudang</p>
                </div>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-success" id="confirm-receive">
                    <i class="fas fa-check me-1"></i> Terima Barang
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Confirm receive button
        $('.confirm-receive-btn').click(function() {
            var id = $(this).data('id');
            var invoice = $(this).data('invoice');

            $('#confirm-purchase-invoice').text(invoice);
            $('#confirm-receive').data('id', id);
            $('#confirmReceiveModal').modal('show');
        });

        $('#confirm-receive').click(function() {
            var id = $(this).data('id');
            $('#receive-form-' + id).submit();
        });
    });
</script>
@endsection
