@extends('layouts.app')

@section('title', 'Edit Harga Produk')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-edit me-2"></i>
                        Edit Harga Produk: {{ $product->name }}
                    </h5>
                    <small class="text-muted">Store: {{ $store->name }}</small>
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

                    <form action="{{ route('store-prices.update', $product->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="store_id" value="{{ $store->id }}">

                        <!-- Product Info -->
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label class="form-label">Kode Produk</label>
                                    <input type="text" class="form-control bg-light" value="{{ $product->code }}" readonly>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Nama Produk</label>
                                    <input type="text" class="form-control bg-light" value="{{ $product->name }}" readonly>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label class="form-label">Kategori</label>
                                    <input type="text" class="form-control bg-light" value="{{ $product->category->name ?? '-' }}" readonly>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="mb-3">
                                    <label class="form-label">Status</label>
                                    <div>
                                        @if($product->is_processed)
                                            <span class="badge bg-warning">
                                                <i class="fas fa-mortar-pestle me-1"></i>Olahan
                                            </span>
                                        @else
                                            <span class="badge bg-info">
                                                <i class="fas fa-box me-1"></i>Reguler
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Base Unit Price -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h6 class="mb-0">
                                    <i class="fas fa-tag me-2"></i>Harga Unit Dasar ({{ $product->baseUnit->name }})
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Harga Default Sistem</label>
                                            <div class="input-group">
                                                <span class="input-group-text">Rp</span>
                                                <input type="text" class="form-control bg-light"
                                                       value="{{ number_format($product->selling_price, 0, ',', '.') }}" readonly>
                                            </div>
                                            <div class="form-text">Harga dasar sistem untuk semua store</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Harga Store *</label>
                                            <div class="input-group">
                                                <span class="input-group-text">Rp</span>
                                                <input type="text" class="form-control @error('base_price') is-invalid @enderror"
                                                       name="base_price" id="base_price"
                                                       value="{{ old('base_price',
                                                           isset($storePrices[$product->base_unit_id])
                                                               ? number_format($storePrices[$product->base_unit_id]->selling_price, 0, ',', '.')
                                                               : number_format($product->selling_price, 0, ',', '.')
                                                       ) }}"
                                                       required>
                                            </div>
                                            @error('base_price')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <div class="form-text">
                                                @if(isset($storePrices[$product->base_unit_id]))
                                                    <span class="text-success">
                                                        <i class="fas fa-tag me-1"></i>Menggunakan harga custom store
                                                    </span>
                                                @else
                                                    <span class="text-muted">
                                                        <i class="fas fa-home me-1"></i>Menggunakan harga default sistem
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Additional Units -->
                        @if($product->productUnits->count() > 0)
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h6 class="mb-0">
                                        <i class="fas fa-boxes me-2"></i>Harga Unit Tambahan
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Unit</th>
                                                    <th>Konversi</th>
                                                    <th>Harga Default</th>
                                                    <th>Harga Store</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($product->productUnits as $index => $productUnit)
                                                    <tr>
                                                        <td>
                                                            <strong>{{ $productUnit->unit->name }}</strong>
                                                        </td>
                                                        <td>
                                                            <span class="badge bg-secondary">
                                                                1 {{ $productUnit->unit->name }} = {{ $productUnit->conversion_factor }} {{ $product->baseUnit->name }}
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <div class="input-group input-group-sm">
                                                                <span class="input-group-text">Rp</span>
                                                                <input type="text" class="form-control bg-light"
                                                                       value="{{ number_format($productUnit->selling_price, 0, ',', '.') }}" readonly>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div class="input-group input-group-sm">
                                                                <span class="input-group-text">Rp</span>
                                                                <input type="text" class="form-control unit-price-input"
                                                                       name="unit_prices[{{ $index }}][selling_price]"
                                                                       value="{{ old('unit_prices.'.$index.'.selling_price',
                                                                           isset($storePrices[$productUnit->unit_id])
                                                                               ? number_format($storePrices[$productUnit->unit_id]->selling_price, 0, ',', '.')
                                                                               : number_format($productUnit->selling_price, 0, ',', '.')
                                                                       ) }}">
                                                                <input type="hidden" name="unit_prices[{{ $index }}][unit_id]" value="{{ $productUnit->unit_id }}">
                                                            </div>
                                                        </td>
                                                        <td>
                                                            @if(isset($storePrices[$productUnit->unit_id]))
                                                                <span class="badge bg-success">
                                                                    <i class="fas fa-tag me-1"></i>Custom
                                                                </span>
                                                            @else
                                                                <span class="badge bg-secondary">
                                                                    <i class="fas fa-home me-1"></i>Default
                                                                </span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="form-text">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Kosongkan field untuk menggunakan harga default sistem
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Action Buttons -->
                        <div class="d-flex justify-content-between">
                            <div>
                                <a href="{{ route('store-prices.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left me-1"></i> Kembali
                                </a>
                            </div>
                            <div>
                                @if($storePrices->count() > 0)
                                    <button type="button" class="btn btn-warning me-2" onclick="resetToDefault()">
                                        <i class="fas fa-undo me-1"></i> Reset ke Default
                                    </button>
                                @endif
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-save me-1"></i> Simpan Harga
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Reset to Default Modal -->
<div class="modal fade" id="resetToDefaultModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-undo me-2"></i>Reset ke Harga Default
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="text-center">
                    <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                    <p><strong>Apakah Anda yakin ingin mereset harga produk ini ke harga default?</strong></p>
                    <p class="text-muted">Semua harga custom untuk store ini akan dihapus dan kembali menggunakan harga default sistem.</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Batal
                </button>
                <form action="{{ route('store-prices.reset', $product->id) }}" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="store_id" value="{{ $store->id }}">
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
$(document).ready(function() {
    // Format input as Indonesian Rupiah
    function formatRupiah(input) {
        let value = input.val().replace(/[^\d]/g, '');
        let formatted = new Intl.NumberFormat('id-ID').format(value);
        input.val(formatted);
    }

    // Convert formatted rupiah to number for form submission
    function convertToNumber(input) {
        let value = input.val().replace(/[^\d]/g, '');
        input.val(value);
    }

    // Apply formatting to price inputs
    $('.form-control[name="base_price"], .unit-price-input').on('input', function() {
        formatRupiah($(this));
    });

    // Convert back to numbers before form submission
    $('form').on('submit', function() {
        $('.form-control[name="base_price"], .unit-price-input').each(function() {
            convertToNumber($(this));
        });
    });

    // Initialize formatting for existing values
    $('.form-control[name="base_price"], .unit-price-input').each(function() {
        if ($(this).val()) {
            formatRupiah($(this));
        }
    });
});

function resetToDefault() {
    new bootstrap.Modal(document.getElementById('resetToDefaultModal')).show();
}
</script>
@endpush
