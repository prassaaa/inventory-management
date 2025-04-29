@extends('layouts.app')

@section('title', 'Laporan Hutang ke Pemasok')

@section('content')
<div class="container-fluid">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-0 text-dark fw-bold">
                <i class="fas fa-file-invoice-dollar me-2 text-primary"></i> Laporan Hutang ke Pemasok
            </h1>
            <p class="text-muted">Menampilkan daftar hutang yang belum dibayar ke pemasok</p>
        </div>
        <a href="{{ route('reports.finance') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Kembali
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 fw-bold text-primary">Filter</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('reports.payables') }}" method="GET" class="mb-0">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label for="supplier_id" class="form-label small mb-1">Pemasok</label>
                        <select name="supplier_id" id="supplier_id" class="form-select">
                            <option value="">Semua Pemasok</option>
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}" {{ request('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                    {{ $supplier->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="status" class="form-label small mb-1">Status</label>
                        <select name="status" id="status" class="form-select">
                            <option value="">Semua Status</option>
                            <option value="unpaid" {{ request('status') == 'unpaid' ? 'selected' : '' }}>Belum Bayar</option>
                            <option value="partially_paid" {{ request('status') == 'partially_paid' ? 'selected' : '' }}>Bayar Sebagian</option>
                            <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Lunas</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="start_date" class="form-label small mb-1">Tanggal Awal</label>
                        <input type="date" class="form-control" id="start_date" name="start_date" value="{{ request('start_date') }}">
                    </div>
                    <div class="col-md-2">
                        <label for="end_date" class="form-label small mb-1">Tanggal Akhir</label>
                        <input type="date" class="form-control" id="end_date" name="end_date" value="{{ request('end_date') }}">
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="fas fa-filter me-1"></i> Filter
                        </button>
                        <a href="{{ route('reports.payables') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-sync-alt me-1"></i> Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Hutang</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">Rp {{ number_format($totalPayables, 0, ',', '.') }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-money-bill-wave fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Total Dibayar</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">Rp {{ number_format($totalPaid, 0, ',', '.') }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Sisa Hutang</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">Rp {{ number_format($totalRemaining, 0, ',', '.') }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-coins fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Hutang Jatuh Tempo</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">Rp {{ number_format($overdueTotalAmount, 0, ',', '.') }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 fw-bold text-primary">Daftar Hutang</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>No. Faktur</th>
                            <th>Pemasok</th>
                            <th>Tanggal Faktur</th>
                            <th>Jatuh Tempo</th>
                            <th>Total</th>
                            <th>Dibayar</th>
                            <th>Sisa</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payables as $payable)
                            <tr class="{{ $payable->is_overdue ? 'table-danger' : '' }}">
                                <td>
                                    <a href="{{ route('purchases.show', $payable->purchase_id) }}" class="fw-bold text-primary">
                                        {{ $payable->purchase->invoice_number }}
                                    </a>
                                </td>
                                <td>{{ $payable->supplier->name }}</td>
                                <td>{{ $payable->purchase->date->format('d/m/Y') }}</td>
                                <td>
                                    {{ $payable->due_date->format('d/m/Y') }}
                                    @if($payable->is_overdue)
                                        <span class="badge bg-danger">Jatuh Tempo</span>
                                    @endif
                                </td>
                                <td>Rp {{ number_format($payable->amount, 0, ',', '.') }}</td>
                                <td>Rp {{ number_format($payable->paid_amount, 0, ',', '.') }}</td>
                                <td>Rp {{ number_format($payable->remaining_amount, 0, ',', '.') }}</td>
                                <td>
                                    @if($payable->status == 'unpaid')
                                        <span class="badge bg-danger">Belum Bayar</span>
                                    @elseif($payable->status == 'partially_paid')
                                        <span class="badge bg-warning">Bayar Sebagian</span>
                                    @elseif($payable->status == 'paid')
                                        <span class="badge bg-success">Lunas</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="#" class="btn btn-sm btn-outline-primary record-payment-btn"
                                       data-bs-toggle="modal"
                                       data-bs-target="#recordPaymentModal"
                                       data-id="{{ $payable->id }}"
                                       data-invoice="{{ $payable->purchase->invoice_number }}"
                                       data-remaining="{{ $payable->remaining_amount }}">
                                        <i class="fas fa-money-bill-wave"></i> Bayar
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center">Tidak ada data hutang</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if(isset($payables) && method_exists($payables, 'links'))
                <div class="mt-4">
                    {{ $payables->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Record Payment Modal -->
<div class="modal fade" id="recordPaymentModal" tabindex="-1" aria-labelledby="recordPaymentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="recordPaymentModalLabel">Catat Pembayaran Hutang</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="paymentForm" action="{{ route('finance.record-payment') }}" method="POST">
                @csrf
                <input type="hidden" name="payable_id" id="payable_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="invoice_number" class="form-label">No. Faktur</label>
                        <input type="text" class="form-control" id="invoice_number" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="remaining_amount" class="form-label">Sisa Hutang</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text" class="form-control" id="remaining_amount" readonly>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="payment_amount" class="form-label">Jumlah Pembayaran</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="number" class="form-control" id="payment_amount" name="payment_amount" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="payment_date" class="form-label">Tanggal Pembayaran</label>
                        <input type="date" class="form-control" id="payment_date" name="payment_date" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="payment_notes" class="form-label">Catatan</label>
                        <textarea class="form-control" id="payment_notes" name="payment_notes" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Pembayaran</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Record Payment Button Click
        $('.record-payment-btn').click(function() {
            const id = $(this).data('id');
            const invoice = $(this).data('invoice');
            const remaining = $(this).data('remaining');

            $('#payable_id').val(id);
            $('#invoice_number').val(invoice);
            $('#remaining_amount').val(remaining.toLocaleString('id-ID'));
            $('#payment_amount').val(remaining);
        });

        // Form validation
        $('#paymentForm').submit(function(e) {
            const paymentAmount = parseFloat($('#payment_amount').val());
            const remainingAmount = parseFloat($('#remaining_amount').val().replace(/\./g, '').replace(',', '.'));

            if (paymentAmount <= 0) {
                e.preventDefault();
                alert('Jumlah pembayaran harus lebih dari 0');
                return false;
            }

            if (paymentAmount > remainingAmount) {
                e.preventDefault();
                alert('Jumlah pembayaran tidak boleh melebihi sisa hutang');
                return false;
            }

            return true;
        });
    });
</script>
@endsection
