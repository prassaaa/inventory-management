@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Detail Pengiriman</h4>
                    <div class="float-end">
                        <a href="{{ route('shipments.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                        <a href="{{ route('shipments.document', $shipment->id) }}" class="btn btn-primary" target="_blank">
                            <i class="fas fa-file-alt"></i> Cetak Surat Jalan
                        </a>
                    </div>
                </div>
                <div class="card-body">
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
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0">Informasi Pengiriman</h5>
                                </div>
                                <div class="card-body">
                                    <table class="table table-borderless">
                                        <tr>
                                            <th width="30%">No. Pengiriman</th>
                                            <td>{{ $shipment->shipment_number }}</td>
                                        </tr>
                                        <tr>
                                            <th>Tanggal</th>
                                            <td>{{ $shipment->date->format('d/m/Y') }}</td>
                                        </tr>
                                        <tr>
                                            <th>No. Pesanan</th>
                                            <td>{{ $shipment->storeOrder->order_number }}</td>
                                        </tr>
                                        <tr>
                                            <th>Status</th>
                                            <td>
                                                @if($shipment->status == 'pending')
                                                    <span class="badge bg-warning">Pending</span>
                                                @elseif($shipment->status == 'shipped')
                                                    <span class="badge bg-info">Dikirim</span>
                                                @elseif($shipment->status == 'delivered')
                                                    <span class="badge bg-success">Diterima</span>
                                                @else
                                                    <span class="badge bg-secondary">{{ ucfirst($shipment->status) }}</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Catatan</th>
                                            <td>{{ $shipment->note ?? '-' }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0">Informasi Toko</h5>
                                </div>
                                <div class="card-body">
                                    <table class="table table-borderless">
                                        <tr>
                                            <th width="30%">Nama Toko</th>
                                            <td>{{ $shipment->storeOrder->store->name }}</td>
                                        </tr>
                                        <tr>
                                            <th>Alamat</th>
                                            <td>{{ $shipment->storeOrder->store->address }}</td>
                                        </tr>
                                        <tr>
                                            <th>Telepon</th>
                                            <td>{{ $shipment->storeOrder->store->phone }}</td>
                                        </tr>
                                        <tr>
                                            <th>Email</th>
                                            <td>{{ $shipment->storeOrder->store->email }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Detail Item Pengiriman -->
                    <div class="card">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">Detail Item Pengiriman</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th width="5%">No</th>
                                            <th width="50%">Produk</th>
                                            <th width="20%">Satuan</th>
                                            <th width="25%">Jumlah</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($shipment->shipmentDetails as $index => $detail)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $detail->product->name }}</td>
                                            <td>{{ $detail->unit->name }}</td>
                                            <td>{{ $detail->quantity }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
