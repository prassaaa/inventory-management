@extends('layouts.app')

@section('title', 'Daftar Pesanan Toko')

@section('content')
<div class="container-fluid">
    <div class="row mb-4 align-items-center">
        <div class="col">
            <h1 class="h3 mb-0 text-dark fw-bold">
                <i class="fas fa-shopping-basket me-2 text-primary"></i> Pesanan Toko
            </h1>
            <p class="text-muted">Mengelola pesanan toko ke pusat.</p>
        </div>
        <div class="col-auto">
            @if(Auth::user()->hasRole('admin_store'))
            <a href="{{ route('store-orders.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> Buat Pesanan Baru
            </a>
            @endif
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
                <form action="{{ route('store-orders.index') }}" method="GET" class="d-flex">
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
                            <th>Pembayaran</th> <!-- Kolom pembayaran yang ditambahkan -->
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
                            <!-- Tambahkan kolom pembayaran -->
                            <td>
                                @if($order->payment_type == 'cash')
                                    <span class="badge bg-success bg-opacity-10 text-success">Tunai</span>
                                @elseif($order->payment_type == 'credit')
                                    <span class="badge bg-warning bg-opacity-10 text-warning">Kredit</span>
                                    @if($order->due_date)
                                    <small class="d-block text-muted">Jatuh tempo: {{ $order->due_date->format('d/m/Y') }}</small>
                                    @endif
                                @else
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary">{{ ucfirst($order->payment_type ?? 'N/A') }}</span>
                                @endif
                            </td>
                            <td>{{ $order->createdBy->name }}</td>
                            <td>
                                <div class="btn-group">
                                    <a href="{{ route('store-orders.show', $order->id) }}" class="btn btn-sm btn-outline-primary" data-bs-toggle="tooltip" title="Lihat Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>

                                    @if(Auth::user()->hasRole(['owner', 'admin_back_office']) && $order->status == 'pending')
                                    <form action="{{ route('store-orders.confirm', $order->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-success" data-bs-toggle="tooltip" title="Konfirmasi">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    </form>
                                    @endif

                                    @if(Auth::user()->hasRole(['owner', 'admin_back_office']) && $order->status == 'confirmed_by_admin')
                                    <form action="{{ route('store-orders.forward', $order->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-primary" data-bs-toggle="tooltip" title="Teruskan ke Gudang">
                                            <i class="fas fa-arrow-right"></i>
                                        </button>
                                    </form>
                                    @endif

                                    @if(Auth::user()->hasRole('admin_gudang') && $order->status == 'forwarded_to_warehouse')
                                    <a href="{{ route('warehouse.store-orders.shipment.create', $order->id) }}" class="btn btn-sm btn-outline-info" data-bs-toggle="tooltip" title="Buat Pengiriman">
                                        <i class="fas fa-truck"></i>
                                    </a>
                                    @endif

                                    @if(Auth::user()->hasRole('admin_store') && $order->status == 'shipped')
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
                            <td colspan="7" class="text-center">Tidak ada pesanan ditemukan.</td>
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
