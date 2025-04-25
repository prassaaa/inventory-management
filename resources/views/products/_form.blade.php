<div class="row">
    <div class="col-md-6">
        <div class="form-group mb-3">
            <label for="code" class="form-label">Kode Produk <span class="text-danger">*</span></label>
            <input type="text" class="form-control @error('code') is-invalid @enderror" id="code" name="code" value="{{ old('code', $product->code ?? '') }}" required {{ isset($product) ? 'readonly' : '' }}>
            @if(!isset($product))
                <small class="form-text text-muted">Biarkan kosong untuk generate otomatis</small>
            @endif
            @error('code')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group mb-3">
            <label for="name" class="form-label">Nama Produk <span class="text-danger">*</span></label>
            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $product->name ?? '') }}" required>
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group mb-3">
            <label for="category_id" class="form-label">Kategori <span class="text-danger">*</span></label>
            <select class="form-select select2 @error('category_id') is-invalid @enderror" id="category_id" name="category_id" required>
                <option value="">Pilih Kategori</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" {{ old('category_id', $product->category_id ?? '') == $category->id ? 'selected' : '' }}>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
            @error('category_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group mb-3">
            <label for="base_unit_id" class="form-label">Satuan Dasar <span class="text-danger">*</span></label>
            <select class="form-select select2 @error('base_unit_id') is-invalid @enderror" id="base_unit_id" name="base_unit_id" required>
                <option value="">Pilih Satuan Dasar</option>
                @foreach($baseUnits as $unit)
                    <option value="{{ $unit->id }}" {{ old('base_unit_id', $product->base_unit_id ?? '') == $unit->id ? 'selected' : '' }}>
                        {{ $unit->name }}
                    </option>
                @endforeach
            </select>
            @error('base_unit_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="form-group mb-3">
            <label for="purchase_price" class="form-label">Harga Beli <span class="text-danger">*</span></label>
            <div class="input-group">
                <span class="input-group-text">Rp</span>
                <input type="text" class="form-control money-format @error('purchase_price') is-invalid @enderror" id="purchase_price" name="purchase_price" value="{{ old('purchase_price', isset($product) ? number_format($product->purchase_price, 0, ',', '.') : '0') }}" required>
                <input type="hidden" name="purchase_price_real" id="purchase_price_real" value="{{ old('purchase_price_real', $product->purchase_price ?? '0') }}">
            </div>
            @error('purchase_price')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group mb-3">
            <label for="selling_price" class="form-label">Harga Jual <span class="text-danger">*</span></label>
            <div class="input-group">
                <span class="input-group-text">Rp</span>
                <input type="text" class="form-control money-format @error('selling_price') is-invalid @enderror" id="selling_price" name="selling_price" value="{{ old('selling_price', isset($product) ? number_format($product->selling_price, 0, ',', '.') : '0') }}" required>
                <input type="hidden" name="selling_price_real" id="selling_price_real" value="{{ old('selling_price_real', $product->selling_price ?? '0') }}">
            </div>
            @error('selling_price')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group mb-3">
            <label for="min_stock" class="form-label">Stok Minimum <span class="text-danger">*</span></label>
            <input type="number" step="0.01" class="form-control @error('min_stock') is-invalid @enderror" id="min_stock" name="min_stock" value="{{ old('min_stock', $product->min_stock ?? '0') }}" required>
            @error('min_stock')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group mb-3">
            <label for="image" class="form-label">Gambar Produk</label>
            <input type="file" class="form-control @error('image') is-invalid @enderror" id="image" name="image" accept="image/*">
            <small class="form-text text-muted">Format yang didukung: JPG, PNG, GIF. Maks: 2MB</small>
            @error('image')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            @if(isset($product) && $product->image)
                <div class="mt-2">
                    <div class="d-flex align-items-center">
                        <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="img-thumbnail me-2" style="max-height: 80px;">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="remove_image" name="remove_image" value="1">
                            <label class="form-check-label text-danger" for="remove_image">
                                Hapus gambar
                            </label>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<div class="form-group mb-3">
    <label for="description" class="form-label">Deskripsi</label>
    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description', $product->description ?? '') }}</textarea>
    @error('description')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="form-group mb-3">
    <div class="form-check form-switch">
        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" 
               {{ old('is_active', $product->is_active ?? true) ? 'checked' : '' }}>
        <label class="form-check-label" for="is_active">
            Aktif
        </label>
    </div>
    @error('is_active')
        <div class="text-danger small">{{ $message }}</div>
    @enderror
</div>

<div class="card mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center bg-light">
        <h6 class="m-0 fw-bold text-primary">Satuan Tambahan</h6>
        <button type="button" class="btn btn-sm btn-outline-primary" id="add-unit">
            <i class="fas fa-plus me-1"></i> Tambah Satuan
        </button>
    </div>
    <div class="card-body" id="additional-units">
        @if(isset($product) && $product->productUnits->isNotEmpty())
            @foreach($product->productUnits as $index => $productUnit)
                <div class="row mb-3 unit-row align-items-center">
                    <div class="col-md-3">
                        <label class="form-label d-block d-md-none">Satuan</label>
                        <select class="form-select select2" name="additional_units[{{ $index }}][unit_id]">
                            <option value="">Pilih Satuan</option>
                            @foreach($units as $unit)
                                <option value="{{ $unit->id }}" {{ $productUnit->unit_id == $unit->id ? 'selected' : '' }}>
                                    {{ $unit->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label d-block d-md-none">Nilai Konversi</label>
                        <div class="input-group">
                            <input type="number" step="0.0001" class="form-control" name="additional_units[{{ $index }}][conversion_value]" 
                                placeholder="Konversi" value="{{ $productUnit->conversion_value }}">
                            <span class="input-group-text">per satuan dasar</span>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label d-block d-md-none">Harga Beli</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text" class="form-control money-format" name="additional_units[{{ $index }}][purchase_price]" 
                                placeholder="Harga Beli" value="{{ number_format($productUnit->purchase_price, 0, ',', '.') }}">
                            <input type="hidden" name="additional_units[{{ $index }}][purchase_price_real]" value="{{ $productUnit->purchase_price }}">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label d-block d-md-none">Harga Jual</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text" class="form-control money-format" name="additional_units[{{ $index }}][selling_price]" 
                                placeholder="Harga Jual" value="{{ number_format($productUnit->selling_price, 0, ',', '.') }}">
                            <input type="hidden" name="additional_units[{{ $index }}][selling_price_real]" value="{{ $productUnit->selling_price }}">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label d-block d-md-none">&nbsp;</label>
                        <button type="button" class="btn btn-outline-danger remove-unit">
                            <i class="fas fa-trash me-1"></i> Hapus
                        </button>
                    </div>
                </div>
            @endforeach
        @else
            <div class="row mb-3 unit-row align-items-center">
                <div class="col-md-3">
                    <label class="form-label d-block d-md-none">Satuan</label>
                    <select class="form-select select2" name="additional_units[0][unit_id]">
                        <option value="">Pilih Satuan</option>
                        @foreach($units as $unit)
                            <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label d-block d-md-none">Nilai Konversi</label>
                    <div class="input-group">
                        <input type="number" step="0.0001" class="form-control" name="additional_units[0][conversion_value]" 
                            placeholder="Konversi">
                        <span class="input-group-text">per satuan dasar</span>
                    </div>
                </div>
                <div class="col-md-2">
                    <label class="form-label d-block d-md-none">Harga Beli</label>
                    <div class="input-group">
                        <span class="input-group-text">Rp</span>
                        <input type="text" class="form-control money-format" name="additional_units[0][purchase_price]" 
                            placeholder="Harga Beli" value="0">
                        <input type="hidden" name="additional_units[0][purchase_price_real]" value="0">
                    </div>
                </div>
                <div class="col-md-2">
                    <label class="form-label d-block d-md-none">Harga Jual</label>
                    <div class="input-group">
                        <span class="input-group-text">Rp</span>
                        <input type="text" class="form-control money-format" name="additional_units[0][selling_price]" 
                            placeholder="Harga Jual" value="0">
                        <input type="hidden" name="additional_units[0][selling_price_real]" value="0">
                    </div>
                </div>
                <div class="col-md-2">
                    <label class="form-label d-block d-md-none">&nbsp;</label>
                    <button type="button" class="btn btn-outline-danger remove-unit">
                        <i class="fas fa-trash me-1"></i> Hapus
                    </button>
                </div>
            </div>
        @endif
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <div class="d-flex justify-content-between">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save me-1"></i> {{ $submitButtonText }}
            </button>
            <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-times me-1"></i> Batal
            </a>
        </div>
    </div>
</div>

@section('scripts')
@parent
<script>
    $(document).ready(function() {
        // Initialize Select2
        $('.select2').select2({
            theme: "bootstrap-5",
            width: '100%'
        });
        
        // Format currency input (Rupiah)
        function formatRupiah(value) {
            // Hapus semua karakter non-numerik
            value = value.replace(/[^\d]/g, '');
            
            // Format dengan pemisah ribuan
            if (value !== '') {
                return parseInt(value).toLocaleString('id-ID');
            }
            return value;
        }
        
        // Initialize money format for existing inputs
        $('.money-format').each(function() {
            var value = $(this).val();
            var formattedValue = formatRupiah(value);
            $(this).val(formattedValue);
        });
        
        // Handle input for money-format fields
        $(document).on('input', '.money-format', function() {
            var value = $(this).val();
            var formattedValue = formatRupiah(value);
            $(this).val(formattedValue);
            
            // Update the hidden field with the actual numeric value
            var numericValue = value.replace(/\./g, '');
            var targetName = $(this).attr('name');
            
            if (targetName.includes('additional_units')) {
                var realFieldName = targetName.replace('purchase_price', 'purchase_price_real')
                                            .replace('selling_price', 'selling_price_real');
                $('input[name="' + realFieldName + '"]').val(numericValue || 0);
            } else {
                var hiddenFieldId = $(this).attr('id') + '_real';
                $('#' + hiddenFieldId).val(numericValue || 0);
            }
        });
        
        // Add unit row
        $('#add-unit').click(function() {
            var index = $('.unit-row').length;
            var newRow = `
                <div class="row mb-3 unit-row align-items-center">
                    <div class="col-md-3">
                        <label class="form-label d-block d-md-none">Satuan</label>
                        <select class="form-select select2-new" name="additional_units[${index}][unit_id]">
                            <option value="">Pilih Satuan</option>
                            @foreach($units as $unit)
                                <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label d-block d-md-none">Nilai Konversi</label>
                        <div class="input-group">
                            <input type="number" step="0.0001" class="form-control" name="additional_units[${index}][conversion_value]" 
                                placeholder="Konversi">
                            <span class="input-group-text">per satuan dasar</span>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label d-block d-md-none">Harga Beli</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text" class="form-control money-format" name="additional_units[${index}][purchase_price]" 
                                placeholder="Harga Beli" value="0">
                            <input type="hidden" name="additional_units[${index}][purchase_price_real]" value="0">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label d-block d-md-none">Harga Jual</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text" class="form-control money-format" name="additional_units[${index}][selling_price]" 
                                placeholder="Harga Jual" value="0">
                            <input type="hidden" name="additional_units[${index}][selling_price_real]" value="0">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label d-block d-md-none">&nbsp;</label>
                        <button type="button" class="btn btn-outline-danger remove-unit">
                            <i class="fas fa-trash me-1"></i> Hapus
                        </button>
                    </div>
                </div>
            `;
            $('#additional-units').append(newRow);
            $('.select2-new').select2({
                theme: "bootstrap-5",
                width: '100%'
            });
            $('.select2-new').removeClass('select2-new');
        });
        
        // Remove unit row
        $(document).on('click', '.remove-unit', function() {
            $(this).closest('.unit-row').remove();
        });

        // Auto-calculate selling price with markup (for example: 20% markup)
        $('#purchase_price').on('input', function() {
            var purchaseValue = $('#purchase_price_real').val() || 0;
            if (!$('#selling_price').val() || $('#selling_price_real').val() == 0) {
                var purchasePrice = parseFloat(purchaseValue) || 0;
                var markup = 0.2; // 20% markup
                var sellingPrice = purchasePrice * (1 + markup);
                
                // Update selling price and its hidden field
                $('#selling_price').val(formatRupiah(sellingPrice.toString()));
                $('#selling_price_real').val(sellingPrice);
            }
        });
        
        // Form submission - ensure hidden fields are updated
        $('form').on('submit', function() {
            $('.money-format').each(function() {
                var value = $(this).val().replace(/\./g, '');
                var targetName = $(this).attr('name');
                
                if (targetName.includes('additional_units')) {
                    var realFieldName = targetName.replace('purchase_price', 'purchase_price_real')
                                                .replace('selling_price', 'selling_price_real');
                    $('input[name="' + realFieldName + '"]').val(value || 0);
                } else {
                    var hiddenFieldId = $(this).attr('id') + '_real';
                    $('#' + hiddenFieldId).val(value || 0);
                }
            });
        });
    });
</script>
@endsection