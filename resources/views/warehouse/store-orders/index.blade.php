@extends('layouts.app')

@section('title', 'Daftar Pesanan Toko')

@section('content')
<div class="container-fluid">
    <div class="row mb-4 align-items-center">
        <div class="col">
            <h1 class="h3 mb-0 text-dark fw-bold">
                <i class="fas fa-shopping-basket me-2 text-primary"></i> Pesanan Toko
            </h1>
            <p class="text-muted">Mengelola pesanan toko untuk pengiriman.</p>
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

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 fw-bold text-primary">Daftar Pesanan</h6>
            <div>
                <form action="{{ route('warehouse.store-orders.index') }}" method="GET" class="d-flex">
                    <div class="input-group">
                        <input type="text" class="form-control form-control-sm" name="search" placeholder="Cari pesanan..." value="{{ request('search') }}">
                        <button class="btn btn-primary btn-sm" type="submit">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>No. Pesanan</th>
                            <th>Toko</th>
                            <th>Tanggal</th>
                            <th>Status</th>
                            <th>Dibuat Oleh</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($storeOrders as $order)
                        <tr>
                            <td>{{ $order->order_number }}</td>
                            <td>{{ $order->store->name }}</td>
                            <td>{{ $order->date->format('d/m/Y') }}</td>
                            <td>
                                @if($order->status == 'pending')
                                    <span class="badge bg-warning bg-opacity-10 text-warning">Menunggu</span>
                                @elseif($order->status == 'confirmed_by_admin')
                                    <span class="badge bg-info bg-opacity-10 text-info">Dikonfirmasi</span>
                                @elseif($order->status == 'forwarded_to_warehouse')
                                    <span class="badge bg-primary bg-opacity-10 text-primary">Diteruskan</span>
                                @elseif($order->status == 'shipped')
                                    <span class="badge bg-info bg-opacity-10 text-info">Dikirim</span>
                                @elseif($order->status == 'completed')
                                    <span class="badge bg-success bg-opacity-10 text-success">Selesai</span>
                                @else
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary">{{ ucfirst($order->status) }}</span>
                                @endif
                            </td>
                            <td>{{ $order->createdBy->name }}</td>
                            <td>
                                <div class="btn-group">
                                    <a href="{{ route('warehouse.store-orders.show', $order->id) }}" class="btn btn-sm btn-outline-primary" data-bs-toggle="tooltip" title="Lihat Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>

                                    @if($order->status == 'forwarded_to_warehouse')
                                    <a href="{{ route('warehouse.store-orders.shipment.create', $order->id) }}" class="btn btn-sm btn-outline-info" data-bs-toggle="tooltip" title="Buat Pengiriman">
                                        <i class="fas fa-truck"></i>
                                    </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center">Tidak ada pesanan ditemukan.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-center mt-4">
                {{ $storeOrders->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Init tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });
    });
</script>
@endsection
