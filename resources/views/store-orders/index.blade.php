@extends('layouts.app')

@section('title', 'Daftar Pesanan Toko')

@section('content')
<div class="container-fluid">
    <div class="row mb-4 align-items-center">
        <div class="col">
            <h1 class="h3 mb-0 text-dark fw-bold">
                <i class="fas fa-shopping-basket me-2 text-primary"></i> Pesanan Toko
            </h1>
            <p class="text-muted">
                @if(Auth::user()->store_id)
                    Mengelola pesanan untuk {{ Auth::user()->store->name }}.
                @else
                    Mengelola pesanan toko ke pusat.
                @endif
            </p>
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

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 fw-bold text-primary">
                Daftar Pesanan
                @if(Auth::user()->store_id)
                    <small class="text-muted">({{ Auth::user()->store->name }})</small>
                @else
                    <small class="text-muted">(Semua Cabang)</small>
                @endif
            </h6>
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
                            @if(!Auth::user()->store_id) {{-- Hanya tampilkan kolom toko jika user adalah pusat --}}
                            <th>Toko</th>
                            @endif
                            <th>Tanggal</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Pembayaran</th>
                            <th>Dibuat Oleh</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($storeOrders as $order)
                        {{-- Pengecekan akses: cabang hanya bisa lihat pesanan sendiri --}}
                        @if(!Auth::user()->store_id || Auth::user()->store_id == $order->store_id)
                        <tr>
                            <td>
                                <span class="fw-bold">{{ $order->order_number }}</span>
                            </td>
                            @if(!Auth::user()->store_id) {{-- Hanya tampilkan nama toko jika user adalah pusat --}}
                            <td>{{ $order->store->name }}</td>
                            @endif
                            <td>{{ $order->date->format('d/m/Y') }}</td>
                            <td>
                                <div>
                                    @if($order->grand_total > 0)
                                        {{-- Jika sudah ada grand total (sudah dikonfirmasi dengan ongkir) --}}
                                        <strong>Rp {{ number_format($order->grand_total, 0, ',', '.') }}</strong>
                                        <small class="d-block text-muted">
                                            Subtotal: Rp {{ number_format($order->total_amount, 0, ',', '.') }}<br>
                                            Ongkir: Rp {{ number_format($order->shipping_cost, 0, ',', '.') }}
                                        </small>
                                    @else
                                        {{-- Jika belum ada grand total (belum dikonfirmasi) --}}
                                        <strong>Rp {{ number_format($order->total_amount, 0, ',', '.') }}</strong>
                                        @if($order->status == 'pending')
                                            <small class="d-block text-warning">
                                                <i class="fas fa-clock me-1"></i>Belum termasuk ongkir
                                            </small>
                                        @elseif($order->shipping_cost == 0 && $order->status != 'pending')
                                            <small class="d-block text-success">
                                                <i class="fas fa-check me-1"></i>Ongkir: Gratis
                                            </small>
                                        @endif
                                    @endif
                                </div>
                            </td>
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
                                @if($order->payment_type == 'cash')
                                    <span class="badge bg-success bg-opacity-10 text-success">
                                        <i class="fas fa-money-bill me-1"></i>Tunai
                                    </span>
                                @elseif($order->payment_type == 'credit')
                                    <span class="badge bg-warning bg-opacity-10 text-warning">
                                        <i class="fas fa-credit-card me-1"></i>Kredit
                                    </span>
                                    @if($order->due_date)
                                    <small class="d-block text-muted">Jatuh tempo: {{ $order->due_date->format('d/m/Y') }}</small>
                                    @endif
                                @else
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary">{{ ucfirst($order->payment_type ?? 'N/A') }}</span>
                                @endif
                            </td>
                            <td>
                                <i class="fas fa-user me-1 text-muted"></i>{{ $order->createdBy->name }}
                            </td>
                            <td>
                                <div class="btn-group">
                                    <a href="{{ route('store-orders.show', $order->id) }}" class="btn btn-sm btn-outline-primary" data-bs-toggle="tooltip" title="Lihat Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>

                                    @if(Auth::user()->hasRole(['owner', 'admin_back_office']) && $order->status == 'pending')
                                    <button type="button"
                                            class="btn btn-sm btn-outline-success"
                                            data-bs-toggle="modal"
                                            data-bs-target="#confirmModal{{ $order->id }}"
                                            title="Konfirmasi">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    @include('store-orders.confirm-modal', ['storeOrder' => $order])
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

                                    {{-- Tombol hapus hanya untuk pesanan pending dan admin store --}}
                                    @if(Auth::user()->hasRole('admin_store') && $order->status == App\Models\StoreOrder::STATUS_PENDING)
                                    <button type="button"
                                            class="btn btn-sm btn-outline-danger"
                                            data-bs-toggle="modal"
                                            data-bs-target="#deleteModal{{ $order->id }}"
                                            title="Hapus Pesanan">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    @endif
                                </div>

                                {{-- Modal Delete untuk setiap pesanan --}}
                                @if(Auth::user()->hasRole('admin_store') && $order->status == App\Models\StoreOrder::STATUS_PENDING)
                                <div class="modal fade" id="deleteModal{{ $order->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">
                                                    <i class="fas fa-exclamation-triangle text-danger me-2"></i>Konfirmasi Hapus
                                                </h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="alert alert-warning">
                                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                                    <strong>Perhatian!</strong> Tindakan ini tidak dapat dibatalkan.
                                                </div>
                                                <p>Apakah Anda yakin ingin menghapus pesanan <strong>{{ $order->order_number }}</strong>?</p>
                                                <div class="mb-3">
                                                    <strong>Detail Pesanan:</strong><br>
                                                    <small class="text-muted">
                                                        Total: Rp {{ number_format($order->total_amount, 0, ',', '.') }}<br>
                                                        Tanggal: {{ $order->date->format('d/m/Y') }}<br>
                                                        Status: {{ ucfirst($order->status) }}
                                                    </small>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="confirmDeleteCheckbox{{ $order->id }}">
                                                    <label class="form-check-label" for="confirmDeleteCheckbox{{ $order->id }}">
                                                        Saya memahami bahwa pesanan ini akan dihapus permanen
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                <form action="{{ route('store.orders.destroy', $order->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger" id="deleteBtn{{ $order->id }}" disabled>
                                                        <i class="fas fa-trash me-1"></i>Ya, Hapus
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endif
                            </td>
                        </tr>
                        @endif
                        @empty
                        <tr>
                            <td colspan="{{ Auth::user()->store_id ? '7' : '8' }}" class="text-center py-4">
                                <div class="text-muted">
                                    <i class="fas fa-inbox fa-3x mb-3"></i>
                                    <p>Tidak ada pesanan ditemukan.</p>
                                </div>
                            </td>
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

        // Handle delete confirmation checkboxes
        @foreach($storeOrders as $order)
            @if(Auth::user()->hasRole('admin_store') && $order->status == App\Models\StoreOrder::STATUS_PENDING)
            const confirmDeleteCheckbox{{ $order->id }} = document.getElementById('confirmDeleteCheckbox{{ $order->id }}');
            const deleteBtn{{ $order->id }} = document.getElementById('deleteBtn{{ $order->id }}');

            if (confirmDeleteCheckbox{{ $order->id }} && deleteBtn{{ $order->id }}) {
                confirmDeleteCheckbox{{ $order->id }}.addEventListener('change', function() {
                    deleteBtn{{ $order->id }}.disabled = !this.checked;
                });
            }
            @endif
        @endforeach

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
