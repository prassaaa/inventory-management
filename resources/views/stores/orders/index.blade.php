@extends('layouts.app')

@section('title', 'Daftar Pesanan Toko')

@section('content')
<div class="container-fluid">
    <div class="row mb-4 align-items-center">
        <div class="col">
            <h1 class="h3 mb-0 text-dark fw-bold">
                <i class="fas fa-shopping-basket me-2 text-primary"></i> Pesanan Toko
            </h1>
            <p class="text-muted">Mengelola pesanan ke pusat.</p>
        </div>
        <div class="col-auto">
            <a href="{{ route('store.orders.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> Buat Pesanan Baru
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

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 fw-bold text-primary">Daftar Pesanan</h6>
            <div>
                <form action="{{ route('store.orders.index') }}" method="GET" class="d-flex">
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
                            <th>Tanggal</th>
                            <th>Status</th>
                            <th>Total</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($storeOrders as $order)
                        <tr>
                            <td>{{ $order->order_number }}</td>
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
                            <td>
                                @php
                                    $total = $order->storeOrderDetails->sum('subtotal');
                                @endphp
                                {{ number_format($total, 0, ',', '.') }}
                            </td>
                            <td>
                                <div class="btn-group">
                                    <a href="{{ route('store.orders.show', $order->id) }}" class="btn btn-sm btn-outline-primary" data-bs-toggle="tooltip" title="Lihat Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>

                                    @if($order->status == 'shipped')
                                    <form action="{{ route('store.orders.confirm-delivery', $order->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-success" data-bs-toggle="tooltip" title="Konfirmasi Penerimaan">
                                            <i class="fas fa-check-double"></i>
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center">Tidak ada pesanan ditemukan.</td>
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
