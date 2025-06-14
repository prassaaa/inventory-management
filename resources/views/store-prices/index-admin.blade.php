@extends('layouts.app')

@section('title', 'Manajemen Harga Produk')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-dollar-sign me-2"></i>
                        Manajemen Harga Produk Per Store
                    </h5>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <!-- Filter Store -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <form method="GET">
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-store"></i>
                                    </span>
                                    <select name="store_id" class="form-select" onchange="this.form.submit()">
                                        <option value="">Pilih Store</option>
                                        @foreach($stores as $store)
                                            <option value="{{ $store->id }}"
                                                    {{ request('store_id') == $store->id ? 'selected' : '' }}>
                                                {{ $store->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </form>
                        </div>
                    </div>

                    @if($selectedStore)
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            Menampilkan pengaturan harga untuk: <strong>{{ $selectedStore->name }}</strong>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Kode</th>
                                        <th>Nama Produk</th>
                                        <th>Kategori</th>
                                        <th>Harga Default</th>
                                        <th>Harga Store</th>
                                        <th>Status</th>
                                        <th width="120">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($products as $product)
                                    <tr>
                                        <td>
                                            <code>{{ $product->code }}</code>
                                        </td>
                                        <td>
                                            <strong>{{ $product->name }}</strong>
                                            @if($product->is_processed)
                                                <span class="badge bg-warning ms-1">
                                                    <i class="fas fa-mortar-pestle me-1"></i>Olahan
                                                </span>
                                            @endif
                                        </td>
                                        <td>{{ $product->category->name ?? '-' }}</td>
                                        <td class="text-end">
                                            <span class="text-muted">Rp {{ number_format($product->selling_price, 0, ',', '.') }}</span>
                                        </td>
                                        <td class="text-end">
                                            @if($product->storePrices->count() > 0)
                                                <span class="text-success fw-bold">
                                                    Rp {{ number_format($product->getPriceForStore($selectedStore->id), 0, ',', '.') }}
                                                </span>
                                            @else
                                                <span class="text-muted">
                                                    Rp {{ number_format($product->selling_price, 0, ',', '.') }}
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($product->storePrices->count() > 0)
                                                <span class="badge bg-success">
                                                    <i class="fas fa-tag me-1"></i>Custom
                                                </span>
                                            @else
                                                <span class="badge bg-secondary">
                                                    <i class="fas fa-home me-1"></i>Default
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('store-prices.edit', $product->id) }}?store_id={{ $selectedStore->id }}"
                                                   class="btn btn-sm btn-primary" title="Edit Harga">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                @if($product->storePrices->count() > 0)
                                                    <button class="btn btn-sm btn-warning"
                                                            onclick="resetPrice({{ $product->id }}, {{ $selectedStore->id }})"
                                                            title="Reset ke Default">
                                                        <i class="fas fa-undo"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4">
                                            <i class="fas fa-box-open fa-2x text-muted mb-2"></i>
                                            <p class="text-muted">Tidak ada produk yang ditemukan</p>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        {{ $products->appends(request()->query())->links() }}
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-store fa-4x text-muted mb-3"></i>
                            <h5 class="text-muted">Pilih Store</h5>
                            <p class="text-muted">Pilih store untuk melihat dan mengatur harga produk</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Reset Price Modal -->
<div class="modal fade" id="resetPriceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-undo me-2"></i>Konfirmasi Reset Harga
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="text-center">
                    <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                    <p>Apakah Anda yakin ingin mengembalikan harga produk ini ke harga default?</p>
                    <p class="text-muted">Harga khusus untuk toko ini akan dihapus.</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Batal
                </button>
                <form id="resetPriceForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="store_id" id="resetStoreId">
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-undo me-1"></i>Reset ke Default
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function resetPrice(productId, storeId) {
    const form = document.getElementById('resetPriceForm');
    form.action = `/store-prices/${productId}/reset`;
    document.getElementById('resetStoreId').value = storeId;
    new bootstrap.Modal(document.getElementById('resetPriceModal')).show();
}
</script>
@endpush
