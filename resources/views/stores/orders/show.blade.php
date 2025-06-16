@extends('layouts.app')

@section('title', 'Detail Pesanan')

@section('content')
<div class="container-fluid">
    <div class="row mb-4 align-items-center">
        <div class="col">
            <h1 class="h3 mb-0 text-dark fw-bold">
                <i class="fas fa-shopping-basket me-2 text-primary"></i> Detail Pesanan Toko
            </h1>
            <p class="text-muted">Pesanan {{ $storeOrder->order_number }} - {{ Auth::user()->store->name }}</p>
        </div>
        <div class="col-auto">
            <a href="{{ route('store.orders.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Kembali
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <!-- Status Alert Card -->
    <div class="card shadow mb-4 border-0">
        <div class="card-body bg-light">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h5 class="mb-2">
                        <i class="fas fa-info-circle me-2 text-primary"></i>Status Pesanan:
                        @if($storeOrder->status == 'pending')
                            <span class="badge bg-warning fs-6 ms-2">Menunggu Konfirmasi</span>
                        @elseif($storeOrder->status == 'confirmed_by_admin')
                            <span class="badge bg-info fs-6 ms-2">Dikonfirmasi Admin</span>
                        @elseif($storeOrder->status == 'forwarded_to_warehouse')
                            <span class="badge bg-primary fs-6 ms-2">Diproses Gudang</span>
                        @elseif($storeOrder->status == 'shipped')
                            <span class="badge bg-info fs-6 ms-2">Dalam Pengiriman</span>
                        @elseif($storeOrder->status == 'completed')
                            <span class="badge bg-success fs-6 ms-2">Selesai</span>
                        @else
                            <span class="badge bg-secondary fs-6 ms-2">{{ ucfirst($storeOrder->status) }}</span>
                        @endif
                    </h5>
                    <p class="mb-0 text-muted">
                        @if($storeOrder->status == 'pending')
                            <i class="fas fa-clock me-1"></i>Pesanan Anda sedang menunggu konfirmasi dari admin pusat.
                        @elseif($storeOrder->status == 'confirmed_by_admin')
                            <i class="fas fa-check me-1"></i>Pesanan telah dikonfirmasi dan akan segera diproses.
                        @elseif($storeOrder->status == 'forwarded_to_warehouse')
                            <i class="fas fa-boxes me-1"></i>Pesanan sedang diproses oleh tim gudang.
                        @elseif($storeOrder->status == 'shipped')
                            <i class="fas fa-shipping-fast me-1"></i>Pesanan telah dikirim, silakan konfirmasi setelah barang diterima.
                        @elseif($storeOrder->status == 'completed')
                            <i class="fas fa-check-double me-1"></i>Pesanan telah selesai dan barang sudah diterima.
                        @endif
                    </p>
                </div>
                <div class="col-md-4 text-end">
                    @if($storeOrder->status == 'shipped')
                    <button type="button" class="btn btn-success btn-lg" data-bs-toggle="modal" data-bs-target="#confirmDeliveryModal">
                        <i class="fas fa-check-double me-2"></i>Konfirmasi Penerimaan
                    </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <!-- Informasi Pesanan -->
        <div class="col-md-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 fw-bold text-primary">
                        <i class="fas fa-file-alt me-2"></i>Informasi Pesanan
                    </h6>
                </div>
                <div class="card-body">
                    <table class="table table-borderless mb-0">
                        <tr>
                            <th width="45%">No. Pesanan</th>
                            <td><span class="badge bg-primary bg-opacity-10 text-primary">{{ $storeOrder->order_number }}</span></td>
                        </tr>
                        <tr>
                            <th>Tanggal</th>
                            <td>{{ $storeOrder->date->format('d/m/Y H:i') }}</td>
                        </tr>
                        <tr>
                            <th>Metode Pembayaran</th>
                            <td>
                                @if($storeOrder->payment_type == 'cash')
                                    <span class="badge bg-success bg-opacity-10 text-success">
                                        <i class="fas fa-money-bill me-1"></i>Tunai
                                    </span>
                                @elseif($storeOrder->payment_type == 'credit')
                                    <span class="badge bg-warning bg-opacity-10 text-warning">
                                        <i class="fas fa-credit-card me-1"></i>Kredit
                                    </span>
                                @else
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary">{{ ucfirst($storeOrder->payment_type ?? 'N/A') }}</span>
                                @endif
                            </td>
                        </tr>
                        @if($storeOrder->payment_type == 'credit' && $storeOrder->due_date)
                        <tr>
                            <th>Jatuh Tempo</th>
                            <td>
                                <span class="text-warning">
                                    <i class="fas fa-calendar-alt me-1"></i>{{ $storeOrder->due_date->format('d/m/Y') }}
                                </span>
                            </td>
                        </tr>
                        @endif
                        <tr>
                            <th>Dibuat Oleh</th>
                            <td>
                                <i class="fas fa-user me-1 text-muted"></i>{{ $storeOrder->createdBy->name }}
                            </td>
                        </tr>
                        @if($storeOrder->note)
                        <tr>
                            <th>Catatan</th>
                            <td>
                                <small class="text-muted fst-italic">{{ $storeOrder->note }}</small>
                            </td>
                        </tr>
                        @endif
                    </table>
                </div>
            </div>
        </div>

        <!-- Informasi Toko -->
        <div class="col-md-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 fw-bold text-primary">
                        <i class="fas fa-store me-2"></i>Informasi Toko
                    </h6>
                </div>
                <div class="card-body">
                    <table class="table table-borderless mb-0">
                        <tr>
                            <th width="35%">Nama</th>
                            <td><strong>{{ $storeOrder->store->name }}</strong></td>
                        </tr>
                        <tr>
                            <th>Alamat</th>
                            <td>
                                <i class="fas fa-map-marker-alt me-1 text-muted"></i>
                                {{ $storeOrder->store->address ?: '-' }}
                            </td>
                        </tr>
                        <tr>
                            <th>Telepon</th>
                            <td>
                                @if($storeOrder->store->phone)
                                    <i class="fas fa-phone me-1 text-muted"></i>{{ $storeOrder->store->phone }}
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Email</th>
                            <td>
                                @if($storeOrder->store->email)
                                    <i class="fas fa-envelope me-1 text-muted"></i>{{ $storeOrder->store->email }}
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- Ringkasan Biaya -->
        <div class="col-md-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 fw-bold text-primary">
                        <i class="fas fa-calculator me-2"></i>Ringkasan Biaya
                    </h6>
                </div>
                <div class="card-body">
                    <table class="table table-borderless mb-0">
                        <tr>
                            <th width="65%">Subtotal Pesanan</th>
                            <td class="text-end">
                                <span class="fw-bold">Rp {{ number_format($storeOrder->total_amount, 0, ',', '.') }}</span>
                            </td>
                        </tr>
                        <tr>
                            <th>Ongkos Kirim</th>
                            <td class="text-end">
                                @if($storeOrder->shipping_cost > 0)
                                    <span class="fw-bold text-info">Rp {{ number_format($storeOrder->shipping_cost, 0, ',', '.') }}</span>
                                @else
                                    <span class="text-muted">
                                        @if($storeOrder->status == 'pending')
                                            <i class="fas fa-clock me-1"></i>Menunggu konfirmasi
                                        @else
                                            Rp 0
                                        @endif
                                    </span>
                                @endif
                            </td>
                        </tr>
                        <tr class="border-top">
                            <th class="fs-5 pt-3">
                                <i class="fas fa-receipt me-2 text-success"></i>Grand Total
                            </th>
                            <th class="text-end fs-4 pt-3">
                                <span class="text-success fw-bold">
                                    Rp {{ number_format($storeOrder->grand_total ?: $storeOrder->total_amount, 0, ',', '.') }}
                                </span>
                            </th>
                        </tr>
                    </table>

                    @if($storeOrder->status == 'pending')
                    <div class="alert alert-info mt-3 mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        <small><strong>Catatan:</strong> Ongkos kirim akan ditentukan saat admin pusat mengkonfirmasi pesanan.</small>
                    </div>
                    @elseif($storeOrder->shipping_cost > 0)
                    <div class="alert alert-success mt-3 mb-0">
                        <i class="fas fa-check-circle me-2"></i>
                        <small><strong>Info:</strong> Ongkos kirim telah ditambahkan dalam total pembayaran.</small>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Timeline Progress -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 fw-bold text-primary">
                <i class="fas fa-route me-2"></i>Progress Pesanan
            </h6>
        </div>
        <div class="card-body">
            <div class="timeline-horizontal">
                <div class="timeline-item {{ $storeOrder->created_at ? 'completed' : '' }}">
                    <div class="timeline-marker">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <div class="timeline-content">
                        <h6>Pesanan Dibuat</h6>
                        <small>{{ $storeOrder->created_at ? $storeOrder->created_at->format('d/m/Y H:i') : '-' }}</small>
                    </div>
                </div>

                <div class="timeline-item {{ $storeOrder->confirmed_at ? 'completed' : '' }}">
                    <div class="timeline-marker">
                        <i class="fas fa-check"></i>
                    </div>
                    <div class="timeline-content">
                        <h6>Dikonfirmasi</h6>
                        <small>{{ $storeOrder->confirmed_at ? $storeOrder->confirmed_at->format('d/m/Y H:i') : '-' }}</small>
                        @if($storeOrder->confirmed_at && $storeOrder->shipping_cost > 0)
                            <small class="d-block text-info">
                                <i class="fas fa-truck me-1"></i>Ongkir: Rp {{ number_format($storeOrder->shipping_cost, 0, ',', '.') }}
                            </small>
                        @endif
                    </div>
                </div>

                <div class="timeline-item {{ $storeOrder->shipped_at ? 'completed' : '' }}">
                    <div class="timeline-marker">
                        <i class="fas fa-truck"></i>
                    </div>
                    <div class="timeline-content">
                        <h6>Dikirim</h6>
                        <small>{{ $storeOrder->shipped_at ? $storeOrder->shipped_at->format('d/m/Y H:i') : '-' }}</small>
                    </div>
                </div>

                <div class="timeline-item {{ $storeOrder->delivered_at ? 'completed' : '' }}">
                    <div class="timeline-marker">
                        <i class="fas fa-check-double"></i>
                    </div>
                    <div class="timeline-content">
                        <h6>Diterima</h6>
                        <small>{{ $storeOrder->delivered_at ? $storeOrder->delivered_at->format('d/m/Y H:i') : '-' }}</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Detail Items -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 fw-bold text-primary">
                <i class="fas fa-boxes me-2"></i>Detail Item Pesanan
            </h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead class="table-light">
                        <tr>
                            <th width="5%">No</th>
                            <th width="45%">Produk</th>
                            <th width="12%">Satuan</th>
                            <th width="10%">Jumlah</th>
                            <th width="14%">Harga</th>
                            <th width="14%">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $subtotal = 0; @endphp
                        @foreach($storeOrder->storeOrderDetails as $index => $detail)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <strong>{{ $detail->product->name }}</strong>
                                    @if($detail->product->code)
                                        <br><small class="text-muted">Kode: {{ $detail->product->code }}</small>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary">
                                        {{ $detail->unit->name }}
                                    </span>
                                </td>
                                <td><span class="fw-bold">{{ number_format($detail->quantity, 0, ',', '.') }}</span></td>
                                <td class="text-end">Rp {{ number_format($detail->price, 0, ',', '.') }}</td>
                                <td class="text-end fw-bold">Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                            </tr>
                            @php $subtotal += $detail->subtotal; @endphp
                        @endforeach
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <th colspan="5" class="text-end">Subtotal Pesanan</th>
                            <th class="text-end">Rp {{ number_format($subtotal, 0, ',', '.') }}</th>
                        </tr>
                        @if($storeOrder->shipping_cost > 0)
                        <tr>
                            <th colspan="5" class="text-end">
                                <i class="fas fa-truck me-1 text-info"></i>Ongkos Kirim
                            </th>
                            <th class="text-end text-info">Rp {{ number_format($storeOrder->shipping_cost, 0, ',', '.') }}</th>
                        </tr>
                        @endif
                        <tr class="table-success">
                            <th colspan="5" class="text-end fs-5">
                                <i class="fas fa-receipt me-2"></i>Grand Total
                            </th>
                            <th class="text-end fs-5">
                                <strong class="text-success">
                                    Rp {{ number_format($storeOrder->grand_total ?: $storeOrder->total_amount, 0, ',', '.') }}
                                </strong>
                            </th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <!-- Informasi Pengiriman -->
    @if($storeOrder->shipments->isNotEmpty())
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 fw-bold text-primary">
                <i class="fas fa-shipping-fast me-2"></i>Informasi Pengiriman
            </h6>
        </div>
        <div class="card-body">
            @foreach($storeOrder->shipments as $shipment)
            <div class="row mb-3 pb-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                <div class="col-md-6">
                    <h6><strong>{{ $shipment->shipment_number }}</strong></h6>
                    <p class="mb-1">
                        <i class="fas fa-calendar me-1 text-muted"></i>
                        <strong>Tanggal Kirim:</strong> {{ $shipment->date->format('d/m/Y') }}
                    </p>
                    <p class="mb-1">
                        <i class="fas fa-info-circle me-1 text-muted"></i>
                        <strong>Status:</strong>
                        @if($shipment->status == 'shipped')
                            <span class="badge bg-info">Dalam Perjalanan</span>
                        @elseif($shipment->status == 'delivered')
                            <span class="badge bg-success">Terkirim</span>
                        @else
                            <span class="badge bg-secondary">{{ ucfirst($shipment->status) }}</span>
                        @endif
                    </p>
                    @if($shipment->note)
                    <p class="mb-0">
                        <i class="fas fa-sticky-note me-1 text-muted"></i>
                        <strong>Catatan:</strong> <em>{{ $shipment->note }}</em>
                    </p>
                    @endif
                </div>
                <div class="col-md-6">
                    <div class="text-end">
                        <a href="{{ route('shipments.show', $shipment->id) }}" class="btn btn-sm btn-outline-primary mb-1">
                            <i class="fas fa-eye me-1"></i>Detail
                        </a>
                        <a href="{{ route('shipments.document', $shipment->id) }}" class="btn btn-sm btn-outline-secondary mb-1" target="_blank">
                            <i class="fas fa-file-alt me-1"></i>Surat Jalan
                        </a>
                    </div>

                    @if($shipment->shipmentDetails->count() > 0)
                    <div class="mt-3">
                        <small class="text-muted"><strong>Barang yang dikirim:</strong></small>
                        <div class="table-responsive">
                            <table class="table table-sm table-borderless mt-1">
                                @foreach($shipment->shipmentDetails as $detail)
                                <tr>
                                    <td class="py-1">
                                        <small>{{ $detail->product->name }}
                                        ({{ number_format($detail->quantity, 0, ',', '.') }} {{ $detail->unit->name }})</small>
                                    </td>
                                </tr>
                                @endforeach
                            </table>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Action Buttons -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <a href="{{ route('store.orders.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Kembali ke Daftar
                    </a>
                </div>

                <div>
                    @if($storeOrder->status == 'shipped')
                    <button type="button" class="btn btn-success btn-lg" data-bs-toggle="modal" data-bs-target="#confirmDeliveryModal">
                        <i class="fas fa-check-double me-2"></i>Konfirmasi Penerimaan
                    </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Konfirmasi Penerimaan -->
@if($storeOrder->status == 'shipped')
<div class="modal fade" id="confirmDeliveryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-check-double me-2 text-success"></i>Konfirmasi Penerimaan Barang
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Perhatian!</strong> Pastikan Anda telah menerima semua barang sesuai dengan pesanan sebelum mengkonfirmasi.
                </div>

                <div class="mb-3">
                    <strong>Pesanan:</strong> {{ $storeOrder->order_number }}<br>
                    <strong>Total:</strong> Rp {{ number_format($storeOrder->grand_total ?: $storeOrder->total_amount, 0, ',', '.') }}
                </div>

                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="confirmCheckbox">
                    <label class="form-check-label" for="confirmCheckbox">
                        Saya telah menerima semua barang dengan kondisi baik dan sesuai pesanan
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <form action="{{ route('store.orders.confirm-delivery', $storeOrder->id) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-success" id="confirmBtn" disabled>
                        <i class="fas fa-check-double me-1"></i>Konfirmasi Penerimaan
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endif
@endsection

@section('styles')
<style>
/* Timeline Styles */
.timeline-horizontal {
    display: flex;
    justify-content: space-between;
    position: relative;
    padding: 20px 0;
}

.timeline-horizontal::before {
    content: '';
    position: absolute;
    top: 35px;
    left: 12.5%;
    right: 12.5%;
    height: 2px;
    background-color: #e9ecef;
    z-index: 1;
}

.timeline-item {
    text-align: center;
    position: relative;
    flex: 1;
    z-index: 2;
}

.timeline-marker {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background-color: #e9ecef;
    color: #6c757d;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 10px;
    position: relative;
    z-index: 3;
    transition: all 0.3s ease;
}

.timeline-item.completed .timeline-marker {
    background-color: #2563eb;
    color: white;
    box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.2);
}

.timeline-content h6 {
    font-size: 14px;
    margin-bottom: 5px;
    font-weight: 600;
}

.timeline-content small {
    font-size: 12px;
    color: #6c757d;
    display: block;
}

/* Card Improvements */
.card {
    border: none;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}

/* Badge Improvements */
.badge {
    font-size: 0.875em;
    font-weight: 500;
}

/* Alert Improvements */
.alert {
    border: none;
    border-left: 4px solid;
}

.alert-info {
    border-left-color: #0dcaf0;
}

.alert-success {
    border-left-color: #198754;
}

.alert-warning {
    border-left-color: #ffc107;
}

/* Button Improvements */
.btn {
    border-radius: 8px;
    font-weight: 500;
    transition: all 0.2s ease;
}

.btn:hover {
    transform: translateY(-1px);
}
</style>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle confirmation checkbox
    const confirmCheckbox = document.getElementById('confirmCheckbox');
    const confirmBtn = document.getElementById('confirmBtn');

    if (confirmCheckbox && confirmBtn) {
        confirmCheckbox.addEventListener('change', function() {
            confirmBtn.disabled = !this.checked;
        });
    }

    // Auto-dismiss alerts after 5 seconds
    setTimeout(function() {
        const alerts = document.querySelectorAll('.alert-dismissible');
        alerts.forEach(function(alert) {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 5000);
});
</script>
@endsection
