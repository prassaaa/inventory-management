@extends('layouts.app')

@section('title', 'Buat Pesanan Baru')

@section('content')
<div class="container-fluid">
    <div class="row mb-4 align-items-center">
        <div class="col">
            <h1 class="h3 mb-0 text-dark fw-bold">
                <i class="fas fa-shopping-basket me-2 text-primary"></i> Buat Pesanan Baru
            </h1>
            <p class="text-muted">Silakan lengkapi form pesanan baru ke pusat.</p>
        </div>
        <div class="col-auto">
            <a href="{{ route('store.orders.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Kembali
            </a>
        </div>
    </div>

    @if(session('error'))
    <div class="alert alert-danger">
        {{ session('error') }}
    </div>
    @endif

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 fw-bold text-primary">Form Pesanan</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('store.orders.store') }}" method="POST">
                @csrf

                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label for="store_id" class="form-label">Toko</label>
                            <input type="text" class="form-control" value="{{ $store->name ?? 'Tidak ada toko terpilih' }}" readonly>
                            <input type="hidden" name="store_id" value="{{ $store->id ?? '' }}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label for="date" class="form-label">Tanggal Pesanan</label>
                            <input type="date" class="form-control" name="date" value="{{ old('date', date('Y-m-d')) }}" required>
                        </div>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center bg-light">
                        <h6 class="m-0 fw-bold text-primary">Item Pesanan</h6>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="add-item-btn">
                            <i class="fas fa-plus me-1"></i> Tambah Item
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table" id="order-items-table">
                                <thead>
                                    <tr>
                                        <th>Produk</th>
                                        <th>Satuan</th>
                                        <th>Jumlah</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="order-item-row">
                                        <td>
                                            <select name="product_id[]" class="form-select product-select" required>
                                                <option value="">Pilih Produk</option>
                                                @foreach($products as $product)
                                                    <option value="{{ $product->id }}">
                                                        {{ $product->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <select name="unit_id[]" class="form-select unit-select" required>
                                                <option value="">Pilih Satuan</option>
                                                @foreach($units as $unit)
                                                    <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <input type="number" name="quantity[]" class="form-control" min="1" value="1" required>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-outline-danger remove-item-btn">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="form-group mb-4">
                    <label for="note" class="form-label">Catatan</label>
                    <textarea name="note" id="note" class="form-control" rows="3">{{ old('note') }}</textarea>
                </div>

                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <button type="reset" class="btn btn-outline-secondary">
                        <i class="fas fa-undo me-1"></i> Reset
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Simpan Pesanan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Inisialisasi select2 jika ada
        if (typeof $.fn.select2 !== 'undefined') {
            $('.product-select').select2({
                placeholder: 'Pilih Produk',
                theme: 'bootstrap-5'
            });

            $('.unit-select').select2({
                placeholder: 'Pilih Satuan',
                theme: 'bootstrap-5'
            });
        }

        // Tambah baris item baru
        $('#add-item-btn').click(function() {
            var newRow = `
                <tr class="order-item-row">
                    <td>
                        <select name="product_id[]" class="form-select product-select" required>
                            <option value="">Pilih Produk</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}">
                                    {{ $product->name }}
                                </option>
                            @endforeach
                        </select>
                    </td>
                    <td>
                        <select name="unit_id[]" class="form-select unit-select" required>
                            <option value="">Pilih Satuan</option>
                            @foreach($units as $unit)
                                <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td>
                        <input type="number" name="quantity[]" class="form-control" min="1" value="1" required>
                    </td>
                    <td>
                        <button type="button" class="btn btn-sm btn-outline-danger remove-item-btn">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
            $('#order-items-table tbody').append(newRow);

            if (typeof $.fn.select2 !== 'undefined') {
                // Re-initialize select2 for new row
                $('.product-select').last().select2({
                    placeholder: 'Pilih Produk',
                    theme: 'bootstrap-5'
                });

                $('.unit-select').last().select2({
                    placeholder: 'Pilih Satuan',
                    theme: 'bootstrap-5'
                });
            }
        });

        // Hapus baris item
        $(document).on('click', '.remove-item-btn', function() {
            if ($('.order-item-row').length > 1) {
                $(this).closest('tr').remove();
            } else {
                alert('Pesanan harus memiliki minimal 1 item.');
            }
        });
    });
</script>
@endsection
