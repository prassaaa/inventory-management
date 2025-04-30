@extends('layouts.app')

@section('title', 'Penyesuaian Stok Baru')

@section('content')
<div class="container-fluid">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-0 text-dark fw-bold">
                <i class="fas fa-plus-circle me-2 text-primary"></i> Penyesuaian Stok Baru
            </h1>
            <p class="text-muted">Buat penyesuaian stok baru untuk gudang atau toko</p>
        </div>
        <a href="{{ route('stock-adjustments.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Kembali
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex align-items-center">
            <i class="fas fa-info-circle text-primary me-2"></i>
            <h6 class="m-0 fw-bold text-primary">Informasi Penyesuaian Stok</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('stock-adjustments.store') }}" method="POST" id="adjustment-form">
                @csrf
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label for="date" class="form-label">Tanggal <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('date') is-invalid @enderror" id="date" name="date" value="{{ old('date', date('Y-m-d')) }}" required>
                            @error('date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label for="reference" class="form-label">Referensi <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('reference') is-invalid @enderror" id="reference" name="reference" value="{{ old('reference', 'ADJ-'.date('YmdHis')) }}" required>
                            <small class="form-text text-muted">Nomor referensi otomatis, dapat diubah jika diperlukan</small>
                            @error('reference')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label for="type" class="form-label">Tipe <span class="text-danger">*</span></label>
                            @if($userHasStore)
                                <!-- Jika user adalah cabang tertentu, tampilkan sebagai input tersembunyi dan text statis -->
                                <input type="hidden" name="type" value="store">
                                <input type="hidden" name="store_id" value="{{ $userStoreId }}">
                                <div class="form-control bg-light">Toko - {{ optional($stores->where('id', $userStoreId)->first())->name }}</div>
                                <small class="form-text text-muted">Anda hanya dapat melakukan penyesuaian di toko Anda</small>
                            @else
                                <!-- Jika user adalah admin pusat, tampilkan semua pilihan -->
                                <select class="form-select @error('type') is-invalid @enderror" id="type" name="type" required>
                                    <option value="warehouse" {{ old('type') === 'warehouse' ? 'selected' : '' }}>Gudang</option>
                                    <option value="store" {{ old('type') === 'store' ? 'selected' : '' }}>Toko Tunggal</option>
                                    <option value="all_stores" {{ old('type') === 'all_stores' ? 'selected' : '' }}>Semua Toko</option>
                                </select>
                                @error('type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            @endif
                        </div>
                    </div>
                </div>

                @if(!$userHasStore)
                <div id="store-selection" class="form-group mb-3 {{ old('type') === 'store' ? '' : 'd-none' }}">
                    <label for="store_id" class="form-label">Toko <span class="text-danger">*</span></label>
                    <select class="form-select @error('store_id') is-invalid @enderror" id="store_id" name="store_id" {{ old('type') === 'store' ? 'required' : '' }}>
                        <option value="">Pilih Toko</option>
                        @foreach($stores as $store)
                            <option value="{{ $store->id }}" {{ old('store_id') == $store->id ? 'selected' : '' }}>
                                {{ $store->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('store_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                @endif

                <hr class="my-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold text-primary mb-0">
                        <i class="fas fa-list-ul me-1"></i> Detail Penyesuaian
                    </h5>
                    <div class="form-text text-muted">Tambahkan produk yang akan disesuaikan stoknya</div>
                </div>

                <div class="mb-3">
                    <div class="input-group">
                        <select class="form-select select2" id="product-search" style="width: 85%;">
                            <option value="">Cari Produk</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}"
                                        data-code="{{ $product->code }}"
                                        data-name="{{ $product->name }}"
                                        data-base-unit-id="{{ $product->base_unit_id }}"
                                        data-base-unit-name="{{ $product->baseUnit->name }}">
                                    {{ $product->code }} - {{ $product->name }}
                                </option>
                            @endforeach
                        </select>
                        <button type="button" class="btn btn-primary" id="add-product">
                            <i class="fas fa-plus me-1"></i> Tambah
                        </button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover" id="products-table">
                        <thead class="table-light">
                            <tr>
                                <th>Produk</th>
                                <th>Tipe Penyesuaian</th>
                                <th>Jumlah</th>
                                <th>Satuan</th>
                                <th>Alasan</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(old('product_ids'))
                                @foreach(old('product_ids') as $index => $productId)
                                    <tr>
                                        <td>{{ old('product_names')[$index] }}</td>
                                        <td>
                                            <select class="form-select" name="adjustment_types[{{ $index }}]">
                                                <option value="addition" {{ old('adjustment_types')[$index] === 'addition' ? 'selected' : '' }}>Penambahan</option>
                                                <option value="reduction" {{ old('adjustment_types')[$index] === 'reduction' ? 'selected' : '' }}>Pengurangan</option>
                                            </select>
                                        </td>
                                        <td>
                                            <input type="number" class="form-control" name="quantities[{{ $index }}]" value="{{ old('quantities')[$index] }}" min="0.01" step="0.01" required>
                                        </td>
                                        <td>{{ old('unit_names')[$index] }}</td>
                                        <td>
                                            <input type="text" class="form-control" name="reasons[{{ $index }}]" value="{{ old('reasons')[$index] }}" placeholder="Alasan penyesuaian">
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-outline-danger btn-sm remove-product">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                            <input type="hidden" name="product_ids[]" value="{{ $productId }}">
                                            <input type="hidden" name="product_names[]" value="{{ old('product_names')[$index] }}">
                                            <input type="hidden" name="unit_ids[]" value="{{ old('unit_ids')[$index] }}">
                                            <input type="hidden" name="unit_names[]" value="{{ old('unit_names')[$index] }}">
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr id="empty-products-row">
                                    <td colspan="6" class="text-center py-3 text-muted">Belum ada produk ditambahkan</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>

                <div class="card mb-0 mt-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Simpan Penyesuaian
                            </button>
                            <a href="{{ route('stock-adjustments.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i> Batal
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Initialize Select2
        $('.select2').select2({
            theme: "bootstrap-5",
            width: '100%'
        });

        // Show/hide store selection based on type (hanya untuk admin pusat)
        $('#type').change(function() {
            if ($(this).val() === 'store') {
                $('#store-selection').removeClass('d-none');
                $('#store_id').prop('required', true);
            } else {
                $('#store-selection').addClass('d-none');
                $('#store_id').prop('required', false);
            }
        });

        // Product counter
        let productCounter = {{ old('product_ids') ? count(old('product_ids')) : 0 }};

        // Add product to table
        $('#add-product').click(function() {
            const productSelect = $('#product-search');
            const productId = productSelect.val();

            if (!productId) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Perhatian',
                    text: 'Silakan pilih produk terlebih dahulu'
                });
                return;
            }

            const productOption = productSelect.find(':selected');
            const productName = productOption.data('name');
            const unitId = productOption.data('base-unit-id');
            const unitName = productOption.data('base-unit-name');

            // Check if product already exists in table
            let exists = false;
            $('input[name="product_ids[]"]').each(function() {
                if ($(this).val() == productId) {
                    exists = true;
                    return false;
                }
            });

            if (exists) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Produk Sudah Ada',
                    text: 'Produk ini sudah ditambahkan dalam daftar penyesuaian'
                });
                return;
            }

            // Remove empty row if exists
            $('#empty-products-row').remove();

            // Add product to table
            const newRow = `
                <tr class="fade-in">
                    <td>${productName}</td>
                    <td>
                        <select class="form-select" name="adjustment_types[${productCounter}]">
                            <option value="addition">Penambahan</option>
                            <option value="reduction">Pengurangan</option>
                        </select>
                    </td>
                    <td>
                        <input type="number" class="form-control" name="quantities[${productCounter}]" value="1" min="0.01" step="0.01" required>
                    </td>
                    <td>${unitName}</td>
                    <td>
                        <input type="text" class="form-control" name="reasons[${productCounter}]" placeholder="Alasan penyesuaian">
                    </td>
                    <td>
                        <button type="button" class="btn btn-outline-danger btn-sm remove-product">
                            <i class="fas fa-trash"></i>
                        </button>
                        <input type="hidden" name="product_ids[]" value="${productId}">
                        <input type="hidden" name="product_names[]" value="${productName}">
                        <input type="hidden" name="unit_ids[]" value="${unitId}">
                        <input type="hidden" name="unit_names[]" value="${unitName}">
                    </td>
                </tr>
            `;

            $('#products-table tbody').append(newRow);

            // Reset product select
            productSelect.val('').trigger('change');

            productCounter++;
        });

        // Remove product from table
        $(document).on('click', '.remove-product', function() {
            const row = $(this).closest('tr');

            // Animasi penghapusan
            row.addClass('bg-danger-light');
            row.fadeOut(300, function() {
                $(this).remove();

                // Check if table is empty
                if ($('#products-table tbody tr').length === 0) {
                    $('#products-table tbody').append('<tr id="empty-products-row"><td colspan="6" class="text-center py-3 text-muted">Belum ada produk ditambahkan</td></tr>');
                }
            });
        });

        // Validate form before submission
        $('#adjustment-form').on('submit', function(e) {
            if ($('#products-table tbody tr').length === 0 || $('#products-table tbody tr#empty-products-row').length > 0) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Tidak Ada Produk',
                    text: 'Tambahkan minimal satu produk untuk penyesuaian stok.'
                });
                return false;
            }

            // Hanya periksa store_id jika admin pusat dan tipe = store
            @if(!$userHasStore)
            if ($('#type').val() === 'store' && !$('#store_id').val()) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Toko Belum Dipilih',
                    text: 'Silakan pilih toko untuk penyesuaian stok toko.'
                });
                return false;
            }

            // Konfirmasi jika memilih semua toko
            if ($('#type').val() === 'all_stores') {
                e.preventDefault();
                Swal.fire({
                    title: 'Konfirmasi Penyesuaian Semua Toko',
                    text: 'Anda akan melakukan penyesuaian stok untuk SEMUA toko sekaligus. Lanjutkan?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ya, Lanjutkan',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $(this).off('submit').submit();
                    }
                });
                return false;
            }
            @endif

            return true;
        });
    });
</script>
@endsection
