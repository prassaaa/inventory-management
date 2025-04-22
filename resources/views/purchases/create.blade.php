@extends('layouts.app')

@section('title', 'Pembelian Baru')

@section('content')
<div class="container-fluid">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-0 text-dark fw-bold">
                <i class="fas fa-plus-circle me-2 text-primary"></i> Pembelian Baru
            </h1>
            <p class="text-muted">Buat transaksi pembelian baru dari pemasok</p>
        </div>
        <a href="{{ route('purchases.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Kembali
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex align-items-center">
            <i class="fas fa-info-circle text-primary me-2"></i>
            <h6 class="m-0 fw-bold text-primary">Informasi Pembelian</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('purchases.store') }}" method="POST" id="purchase-form">
                @csrf
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label for="invoice_number" class="form-label">Nomor Faktur <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('invoice_number') is-invalid @enderror" id="invoice_number" name="invoice_number" value="{{ old('invoice_number', 'INV-' . date('YmdHis')) }}" required>
                            <small class="form-text text-muted">Nomor faktur otomatis, dapat diubah jika diperlukan</small>
                            @error('invoice_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="date" class="form-label">Tanggal <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('date') is-invalid @enderror" id="date" name="date" value="{{ old('date', date('Y-m-d')) }}" required>
                            @error('date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="supplier_id" class="form-label">Pemasok <span class="text-danger">*</span></label>
                            <select class="form-select select2 @error('supplier_id') is-invalid @enderror" id="supplier_id" name="supplier_id" required>
                                <option value="">Pilih Pemasok</option>
                                @foreach($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}" data-payment-term="{{ $supplier->payment_term }}" {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                        {{ $supplier->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('supplier_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label for="payment_type" class="form-label">Jenis Pembayaran <span class="text-danger">*</span></label>
                            <select class="form-select @error('payment_type') is-invalid @enderror" id="payment_type" name="payment_type" required>
                                <option value="tunai" {{ old('payment_type') === 'tunai' ? 'selected' : '' }}>Tunai</option>
                                <option value="tempo" {{ old('payment_type') === 'tempo' ? 'selected' : '' }}>Tempo (Kredit)</option>
                            </select>
                            @error('payment_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-3 due-date-group {{ old('payment_type') === 'tempo' ? '' : 'd-none' }}">
                            <label for="due_date" class="form-label">Tanggal Jatuh Tempo <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('due_date') is-invalid @enderror" id="due_date" name="due_date" value="{{ old('due_date', date('Y-m-d', strtotime('+30 days'))) }}">
                            @error('due_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="note" class="form-label">Catatan</label>
                            <textarea class="form-control @error('note') is-invalid @enderror" id="note" name="note" rows="3" placeholder="Catatan tambahan untuk pembelian ini">{{ old('note') }}</textarea>
                            @error('note')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <hr class="my-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold text-primary mb-0">
                        <i class="fas fa-boxes me-1"></i> Produk
                    </h5>
                    <div class="form-text text-muted">Tambahkan produk yang dibeli</div>
                </div>
                
                <div class="mb-3">
                    <div class="input-group">
                        <select class="form-select select2" id="product-search" style="width: 85%;">
                            <option value="">Cari Produk</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}" 
                                        data-code="{{ $product->code }}" 
                                        data-name="{{ $product->name }}"
                                        data-price="{{ $product->purchase_price }}"
                                        data-unit-id="{{ $product->base_unit_id }}"
                                        data-unit-name="{{ $product->baseUnit->name }}">
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
                                <th>Kode</th>
                                <th>Produk</th>
                                <th>Satuan</th>
                                <th style="width: 120px;">Jumlah</th>
                                <th style="width: 180px;">Harga</th>
                                <th>Subtotal</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Products will be added here dynamically -->
                            @if(old('product_ids'))
                                @foreach(old('product_ids') as $index => $productId)
                                    <tr>
                                        <td>{{ old('product_codes')[$index] }}</td>
                                        <td>{{ old('product_names')[$index] }}</td>
                                        <td>
                                            <select class="form-select unit-select" name="units[{{ $index }}]" data-index="{{ $index }}">
                                                @foreach($units->where('id', old('units')[$index]) as $unit)
                                                    <option value="{{ $unit->id }}" selected>{{ $unit->name }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <input type="number" class="form-control quantity-input" name="quantities[{{ $index }}]" value="{{ old('quantities')[$index] }}" min="0.01" step="0.01" required data-index="{{ $index }}">
                                        </td>
                                        <td>
                                            <div class="input-group">
                                                <span class="input-group-text">Rp</span>
                                                <input type="number" class="form-control price-input" name="prices[{{ $index }}]" value="{{ old('prices')[$index] }}" min="0" step="0.01" required data-index="{{ $index }}">
                                            </div>
                                        </td>
                                        <td class="subtotal">{{ number_format(old('quantities')[$index] * old('prices')[$index], 0, ',', '.') }}</td>
                                        <td>
                                            <button type="button" class="btn btn-outline-danger btn-sm remove-product">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                            <input type="hidden" name="product_ids[]" value="{{ $productId }}">
                                            <input type="hidden" name="product_codes[]" value="{{ old('product_codes')[$index] }}">
                                            <input type="hidden" name="product_names[]" value="{{ old('product_names')[$index] }}">
                                        </td>
                                    </tr>
                                @endforeach
                            @endif
                        </tbody>
                        <tfoot>
                            <tr class="table-light">
                                <th colspan="5" class="text-end">Total:</th>
                                <th id="total-amount" class="text-primary fw-bold">0</th>
                                <th></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                
                <input type="hidden" name="total_amount" id="total-amount-input" value="{{ old('total_amount', 0) }}">
                
                <div class="card mb-0 mt-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Simpan Pembelian
                            </button>
                            <a href="{{ route('purchases.index') }}" class="btn btn-outline-secondary">
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
            theme: "bootstrap-5"
        });
        
        // Show/hide due date based on payment type
        $('#payment_type').change(function() {
            if ($(this).val() === 'tempo') {
                $('.due-date-group').removeClass('d-none');
                $('#due_date').prop('required', true);
            } else {
                $('.due-date-group').addClass('d-none');
                $('#due_date').prop('required', false);
            }
        });
        
        // Set payment type based on supplier's payment term
        $('#supplier_id').change(function() {
            var paymentTerm = $(this).find(':selected').data('payment-term');
            $('#payment_type').val(paymentTerm).trigger('change');
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
            const productCode = productOption.data('code');
            const productName = productOption.data('name');
            const productPrice = productOption.data('price');
            const unitId = productOption.data('unit-id');
            const unitName = productOption.data('unit-name');
            
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
                    text: 'Produk ini sudah ditambahkan dalam daftar pembelian'
                });
                return;
            }
            
            // Add product to table
            addProductRow(productId, productCode, productName, productPrice, unitId, unitName);
            
            // Reset product select
            productSelect.val('').trigger('change');
        });
        
        // Remove product from table
        $(document).on('click', '.remove-product', function() {
            const row = $(this).closest('tr');
            
            // Animasi penghapusan
            row.addClass('bg-danger-light');
            row.fadeOut(300, function() {
                $(this).remove();
                updateTotal();
                
                // Check if table is empty
                if ($('#products-table tbody tr').length === 0) {
                    $('#products-table tbody').append('<tr id="empty-row"><td colspan="7" class="text-center text-muted py-3">Belum ada produk ditambahkan</td></tr>');
                }
            });
        });
        
        // Update subtotal when quantity or price changes
        $(document).on('input', '.quantity-input, .price-input', function() {
            const index = $(this).data('index');
            updateSubtotal(index);
            updateTotal();
        });
        
        // Function to add product row
        function addProductRow(productId, productCode, productName, productPrice, unitId, unitName) {
            // Remove empty row if exists
            $('#empty-row').remove();
            
            const newRow = `
                <tr class="fade-in">
                    <td>${productCode}</td>
                    <td>${productName}</td>
                    <td>
                        <select class="form-select unit-select" name="units[${productCounter}]" data-index="${productCounter}">
                            <option value="${unitId}">${unitName}</option>
                        </select>
                    </td>
                    <td>
                        <input type="number" class="form-control quantity-input" name="quantities[${productCounter}]" value="1" min="0.01" step="0.01" required data-index="${productCounter}">
                    </td>
                    <td>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="number" class="form-control price-input" name="prices[${productCounter}]" value="${productPrice}" min="0" step="0.01" required data-index="${productCounter}">
                        </div>
                    </td>
                    <td class="subtotal">0</td>
                    <td>
                        <button type="button" class="btn btn-outline-danger btn-sm remove-product">
                            <i class="fas fa-trash"></i>
                        </button>
                        <input type="hidden" name="product_ids[]" value="${productId}">
                        <input type="hidden" name="product_codes[]" value="${productCode}">
                        <input type="hidden" name="product_names[]" value="${productName}">
                    </td>
                </tr>
            `;
            
            $('#products-table tbody').append(newRow);
            
            // Update subtotal and total
            updateSubtotal(productCounter);
            updateTotal();
            
            productCounter++;
        }
        
        // Function to update subtotal
        function updateSubtotal(index) {
            const quantity = parseFloat($(`input[name="quantities[${index}]"]`).val()) || 0;
            const price = parseFloat($(`input[name="prices[${index}]"]`).val()) || 0;
            const subtotal = quantity * price;
            
            $(`input[name="quantities[${index}]"]`).closest('tr').find('.subtotal').text(formatNumber(subtotal));
        }
        
        // Function to update total
        function updateTotal() {
            let total = 0;
            $('.subtotal').each(function() {
                total += parseFloat($(this).text().replace(/\./g, '').replace(',', '.')) || 0;
            });
            
            $('#total-amount').text(formatNumber(total));
            $('#total-amount-input').val(total);
        }
        
        // Function to format number with thousand separator
        function formatNumber(number) {
            return number.toFixed(0).replace(/\d(?=(\d{3})+$)/g, '$&.');
        }
        
        // Check if table is empty
        if ($('#products-table tbody tr').length === 0) {
            $('#products-table tbody').append('<tr id="empty-row"><td colspan="7" class="text-center text-muted py-3">Belum ada produk ditambahkan</td></tr>');
        }
        
        // Initialize subtotals and total
        updateTotal();
        
        // Form validation before submit
        $('#purchase-form').on('submit', function(e) {
            if ($('#products-table tbody tr').length === 0 || $('#products-table tbody tr#empty-row').length > 0) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Tidak Ada Produk',
                    text: 'Tambahkan minimal satu produk untuk pembelian ini'
                });
                return false;
            }
            
            if ($('#payment_type').val() === 'tempo' && !$('#due_date').val()) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Tanggal Jatuh Tempo Diperlukan',
                    text: 'Silakan masukkan tanggal jatuh tempo untuk pembayaran tempo'
                });
                return false;
            }
            
            return true;
        });
    });
</script>
@endsection