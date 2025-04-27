@extends('layouts.app')

@section('title', 'Detail Pesanan')

@section('content')
<div class="container-fluid">
    <div class="row mb-4 align-items-center">
        <div class="col">
            <h1 class="h3 mb-0 text-dark fw-bold">
                <i class="fas fa-shopping-basket me-2 text-primary"></i> Detail Pesanan
            </h1>
            <p class="text-muted">Pesanan {{ $storeOrder->order_number }} dari {{ $storeOrder->store->name }}</p>
        </div>
        <div class="col-auto">
            <a href="{{ route('warehouse.store-orders.index') }}" class="btn btn-secondary">
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
                            <th width="30%">No. Pesanan</th>
                            <td>{{ $storeOrder->order_number }}</td>
                        </tr>
                        <tr>
                            <th>Tanggal</th>
                            <td>{{ $storeOrder->date->format('d/m/Y') }}</td>
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
                                    <span class="badge bg-secondary">{{ ucfirst($storeOrder->status) }}</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Dibuat Oleh</th>
                            <td>{{ $storeOrder->createdBy->name }}</td>
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
                            <td>{{ $storeOrder->store->name }}</td>
                        </tr>
                        <tr>
                            <th>Alamat</th>
                            <td>{{ $storeOrder->store->address }}</td>
                        </tr>
                        <tr>
                            <th>Telepon</th>
                            <td>{{ $storeOrder->store->phone }}</td>
                        </tr>
                        <tr>
                            <th>Email</th>
                            <td>{{ $storeOrder->store->email }}</td>
                        </tr>
                    </table>
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
                        @php $total = 0; @endphp
                        @foreach($storeOrder->storeOrderDetails as $index => $detail)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $detail->product->name }}</td>
                                <td>{{ $detail->unit->name }}</td>
                                <td>{{ intval($detail->quantity) }}</td>
                                <td class="text-end">{{ number_format($detail->price, 0, ',', '.') }}</td>
                                <td class="text-end">{{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                            </tr>
                            @php $total += $detail->subtotal; @endphp
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="5" class="text-end">Total</th>
                            <th class="text-end">{{ number_format($total, 0, ',', '.') }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <!-- Shipments -->
    @if($storeOrder->shipments->isNotEmpty())
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
                                <td>{{ $shipment->date->format('d/m/Y') }}</td>
                                <td>
                                    @if($shipment->status == 'pending')
                                        <span class="badge bg-warning">Menunggu</span>
                                    @elseif($shipment->status == 'shipped')
                                        <span class="badge bg-info">Dikirim</span>
                                    @elseif($shipment->status == 'delivered')
                                        <span class="badge bg-success">Diterima</span>
                                    @else
                                        <span class="badge bg-secondary">{{ ucfirst($shipment->status) }}</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('shipments.show', $shipment->id) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i> Detail
                                    </a>
                                    <a href="{{ route('shipments.document', $shipment->id) }}" class="btn btn-sm btn-outline-secondary" target="_blank">
                                        <i class="fas fa-file-alt"></i> Surat Jalan
                                    </a>
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
            <div class="d-flex justify-content-between">
                <div>
                    <a href="{{ route('warehouse.store-orders.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Kembali
                    </a>
                </div>

                <div>
                    @if($storeOrder->status == 'forwarded_to_warehouse')
                    <a href="{{ route('warehouse.store-orders.shipment.create', $storeOrder->id) }}" class="btn btn-primary">
                        <i class="fas fa-truck me-1"></i> Buat Pengiriman
                    </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
