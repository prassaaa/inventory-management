@extends('layouts.app')

@section('title', 'Detail Pesanan')

@section('content')
<div class="container-fluid">
    <div class="row mb-4 align-items-center">
        <div class="col">
            <h1 class="h3 mb-0 text-dark fw-bold">
                <i class="fas fa-shopping-basket me-2 text-primary"></i> Detail Pesanan
            </h1>
            <p class="text-muted">Pesanan {{ $storeOrder->order_number ?? 'N/A' }}</p>
        </div>
        <div class="col-auto">
            <a href="{{ route('store-orders.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Kembali
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger">
        {{ session('error') }}
    </div>
    @endif

    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 fw-bold text-primary">Informasi Pesanan</h6>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th width="35%">No. Pesanan</th>
                            <td>{{ $storeOrder->order_number ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Tanggal</th>
                            <td>{{ $storeOrder->date ? $storeOrder->date->format('d/m/Y') : 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td>
                                @if($storeOrder->status == 'pending')
                                    <span class="badge bg-warning">Menunggu</span>
                                @elseif($storeOrder->status == 'confirmed_by_admin')
                                    <span class="badge bg-info">Dikonfirmasi</span>
                                @elseif($storeOrder->status == 'forwarded_to_warehouse')
                                    <span class="badge bg-primary">Diteruskan</span>
                                @elseif($storeOrder->status == 'shipped')
                                    <span class="badge bg-info">Dikirim</span>
                                @elseif($storeOrder->status == 'completed')
                                    <span class="badge bg-success">Selesai</span>
                                @else
                                    <span class="badge bg-secondary">{{ ucfirst($storeOrder->status ?? 'Unknown') }}</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Metode Pembayaran</th>
                            <td>
                                @if($storeOrder->payment_type == 'cash')
                                    <span class="badge bg-success">Tunai</span>
                                @elseif($storeOrder->payment_type == 'credit')
                                    <span class="badge bg-warning">Kredit</span>
                                @else
                                    <span class="badge bg-secondary">{{ ucfirst($storeOrder->payment_type ?? 'N/A') }}</span>
                                @endif
                            </td>
                        </tr>
                        @if($storeOrder->payment_type == 'credit')
                        <tr>
                            <th>Tanggal Jatuh Tempo</th>
                            <td>{{ $storeOrder->due_date ? $storeOrder->due_date->format('d/m/Y') : 'N/A' }}</td>
                        </tr>
                        @endif
                        <tr>
                            <th>Dibuat Oleh</th>
                            <td>{{ $storeOrder->createdBy->name ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Catatan</th>
                            <td>{{ $storeOrder->note ?? '-' }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 fw-bold text-primary">Informasi Toko</h6>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th width="30%">Nama</th>
                            <td>{{ $storeOrder->store->name ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Alamat</th>
                            <td>{{ $storeOrder->store->address ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Telepon</th>
                            <td>{{ $storeOrder->store->phone ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Email</th>
                            <td>{{ $storeOrder->store->email ?? 'N/A' }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Status Timeline -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 fw-bold text-primary">Timeline Status</h6>
        </div>
        <div class="card-body">
            <div class="row timeline">
                <div class="col text-center">
                    <div class="timeline-step {{ $storeOrder->created_at ? 'complete' : '' }}">
                        <div class="timeline-icon">
                            <i class="fas fa-file-alt"></i>
                        </div>
                        <div class="timeline-text">Dibuat</div>
                        <div class="timeline-date">{{ $storeOrder->created_at ? $storeOrder->created_at->format('d/m/Y H:i') : '-' }}</div>
                    </div>
                </div>
                <div class="col text-center">
                    <div class="timeline-step {{ $storeOrder->confirmed_at ? 'complete' : '' }}">
                        <div class="timeline-icon">
                            <i class="fas fa-check"></i>
                        </div>
                        <div class="timeline-text">Dikonfirmasi</div>
                        <div class="timeline-date">{{ $storeOrder->confirmed_at ? $storeOrder->confirmed_at->format('d/m/Y H:i') : '-' }}</div>
                    </div>
                </div>
                <div class="col text-center">
                    <div class="timeline-step {{ $storeOrder->forwarded_at ? 'complete' : '' }}">
                        <div class="timeline-icon">
                            <i class="fas fa-arrow-right"></i>
                        </div>
                        <div class="timeline-text">Diteruskan</div>
                        <div class="timeline-date">{{ $storeOrder->forwarded_at ? $storeOrder->forwarded_at->format('d/m/Y H:i') : '-' }}</div>
                    </div>
                </div>
                <div class="col text-center">
                    <div class="timeline-step {{ $storeOrder->shipped_at ? 'complete' : '' }}">
                        <div class="timeline-icon">
                            <i class="fas fa-truck"></i>
                        </div>
                        <div class="timeline-text">Dikirim</div>
                        <div class="timeline-date">{{ $storeOrder->shipped_at ? $storeOrder->shipped_at->format('d/m/Y H:i') : '-' }}</div>
                    </div>
                </div>
                <div class="col text-center">
                    <div class="timeline-step {{ $storeOrder->delivered_at ? 'complete' : '' }}">
                        <div class="timeline-icon">
                            <i class="fas fa-check-double"></i>
                        </div>
                        <div class="timeline-text">Diterima</div>
                        <div class="timeline-date">{{ $storeOrder->delivered_at ? $storeOrder->delivered_at->format('d/m/Y H:i') : '-' }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Items -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 fw-bold text-primary">Item Pesanan</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th width="5%">No</th>
                            <th width="40%">Produk</th>
                            <th width="15%">Satuan</th>
                            <th width="10%">Jumlah</th>
                            <th width="15%">Harga</th>
                            <th width="15%">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $subtotal = 0; @endphp
                        @foreach($storeOrder->storeOrderDetails as $index => $detail)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $detail->product->name ?? 'N/A' }}</td>
                                <td>{{ $detail->unit->name ?? 'N/A' }}</td>
                                <td>{{ intval($detail->quantity) }}</td>
                                <td class="text-end">Rp {{ number_format($detail->price, 0, ',', '.') }}</td>
                                <td class="text-end">Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                            </tr>
                            @php $subtotal += $detail->subtotal; @endphp
                        @endforeach
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <th colspan="5" class="text-end">Subtotal Pesanan</th>
                            <th class="text-end">Rp {{ number_format($subtotal, 0, ',', '.') }}</th>
                        </tr>
                        <tr>
                            <th colspan="5" class="text-end">Ongkos Kirim</th>
                            <th class="text-end">
                                @if($storeOrder->shipping_cost > 0)
                                    Rp {{ number_format($storeOrder->shipping_cost, 0, ',', '.') }}
                                @elseif($storeOrder->status == 'pending')
                                    <span class="text-warning">Menunggu konfirmasi</span>
                                @else
                                    <span class="text-success">Gratis</span>
                                @endif
                            </th>
                        </tr>
                        <tr class="table-primary">
                            <th colspan="5" class="text-end fs-5">Grand Total</th>
                            <th class="text-end fs-5">
                                <strong>
                                    @if($storeOrder->grand_total > 0)
                                        Rp {{ number_format($storeOrder->grand_total, 0, ',', '.') }}
                                    @else
                                        Rp {{ number_format($storeOrder->total_amount, 0, ',', '.') }}
                                        @if($storeOrder->status == 'pending')
                                            <small class="d-block text-warning fw-normal">*Belum final</small>
                                        @endif
                                    @endif
                                </strong>
                            </th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <!-- Shipments -->
    @if($storeOrder->shipments && $storeOrder->shipments->isNotEmpty())
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 fw-bold text-primary">Pengiriman</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>No. Pengiriman</th>
                            <th>Tanggal</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($storeOrder->shipments as $shipment)
                            <tr>
                                <td>{{ $shipment->shipment_number }}</td>
                                <td>{{ $shipment->date ? $shipment->date->format('d/m/Y') : 'N/A' }}</td>
                                <td>
                                    @if($shipment->status == 'pending')
                                        <span class="badge bg-warning">Menunggu</span>
                                    @elseif($shipment->status == 'shipped')
                                        <span class="badge bg-info">Dikirim</span>
                                    @elseif($shipment->status == 'delivered')
                                        <span class="badge bg-success">Diterima</span>
                                    @else
                                        <span class="badge bg-secondary">{{ ucfirst($shipment->status ?? 'Unknown') }}</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('shipments.show', $shipment->id) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i> Detail
                                    </a>

                                    {{-- Sembunyikan tombol untuk admin_store --}}
                                    @unless(Auth::user()->hasRole('admin_store'))
                                        <a href="{{ route('shipments.invoice', $shipment->id) }}" class="btn btn-sm btn-outline-success" target="_blank">
                                            <i class="fas fa-file-invoice"></i> Invoice
                                        </a>
                                        <a href="{{ route('shipments.delivery-note', $shipment->id) }}" class="btn btn-sm btn-outline-secondary" target="_blank">
                                            <i class="fas fa-truck"></i> Surat Jalan
                                        </a>
                                    @endunless
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
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

                {{-- Tombol hapus untuk pesanan pending --}}
                @if($storeOrder->status == App\Models\StoreOrder::STATUS_PENDING)
                <button type="button"
                        class="btn btn-outline-danger ms-2"
                        data-bs-toggle="modal"
                        data-bs-target="#deleteOrderModal">
                    <i class="fas fa-trash me-2"></i>Hapus Pesanan
                </button>
                @endif
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

{{-- Modal Delete Order --}}
@if($storeOrder->status == App\Models\StoreOrder::STATUS_PENDING)
<div class="modal fade" id="deleteOrderModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-exclamation-triangle text-danger me-2"></i>Hapus Pesanan
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Peringatan!</strong> Tindakan ini akan menghapus pesanan secara permanen dan tidak dapat dibatalkan.
                </div>

                <div class="mb-3">
                    <strong>Pesanan yang akan dihapus:</strong><br>
                    <div class="card mt-2">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-6">
                                    <small class="text-muted">No. Pesanan:</small><br>
                                    <strong>{{ $storeOrder->order_number }}</strong>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted">Total:</small><br>
                                    <strong>Rp {{ number_format($storeOrder->total_amount, 0, ',', '.') }}</strong>
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-6">
                                    <small class="text-muted">Tanggal:</small><br>
                                    {{ $storeOrder->date->format('d/m/Y') }}
                                </div>
                                <div class="col-6">
                                    <small class="text-muted">Jumlah Item:</small><br>
                                    {{ $storeOrder->storeOrderDetails->count() }} item
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="confirmDeleteCheckbox">
                    <label class="form-check-label" for="confirmDeleteCheckbox">
                        Saya memahami bahwa pesanan ini akan dihapus permanen
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <form action="{{ route('store.orders.destroy', $storeOrder->id) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger" id="deleteOrderBtn" disabled>
                        <i class="fas fa-trash me-1"></i>Ya, Hapus Pesanan
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endif
</div>
@endsection

@section('styles')
<style>
    .timeline {
        position: relative;
        padding: 20px 0;
    }

    .timeline-step {
        text-align: center;
        position: relative;
    }

    .timeline-icon {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background-color: #e9ecef;
        color: #6c757d;
        font-size: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 10px;
    }

    .timeline-step.complete .timeline-icon {
        background-color: #2563eb;
        color: white;
    }

    .timeline-text {
        font-weight: bold;
        margin-bottom: 5px;
    }

    .timeline-date {
        font-size: 12px;
        color: #6c757d;
    }

    /* Horizontal line connecting icons */
    .timeline:before {
        content: '';
        position: absolute;
        height: 2px;
        background-color: #e9ecef;
        top: 45px;
        left: 10%;
        right: 10%;
        z-index: -1;
    }
</style>
@endsection
