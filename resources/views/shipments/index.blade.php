@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Daftar Pengiriman</h4>
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

                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>No. Pengiriman</th>
                                    <th>No. Pesanan</th>
                                    <th>Toko</th>
                                    <th>Tanggal</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($shipments as $shipment)
                                <tr>
                                    <td>{{ $shipment->shipment_number }}</td>
                                    <td>{{ $shipment->storeOrder->order_number }}</td>
                                    <td>{{ $shipment->storeOrder->store->name }}</td>
                                    <td>{{ $shipment->date->format('d/m/Y') }}</td>
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
                                    <td>
                                        <a href="{{ route('shipments.show', $shipment->id) }}" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i> Detail
                                        </a>
                                        <a href="{{ route('shipments.document', $shipment->id) }}" class="btn btn-sm btn-primary" target="_blank">
                                            <i class="fas fa-file-alt"></i> Surat Jalan
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center">Tidak ada pengiriman yang ditemukan.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{ $shipments->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
