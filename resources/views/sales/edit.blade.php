@extends('layouts.app')

@section('title', 'Edit Penjualan')

@section('styles')
<style>
    .product-row {
        background-color: #f8f9fa;
        border-radius: 0.375rem;
        padding: 15px;
        margin-bottom: 10px;
    }

    .delete-row-btn {
        position: absolute;
        top: 10px;
        right: 10px;
    }

    .summary-section {
        background-color: #e3f2fd;
        border-radius: 0.375rem;
        padding: 20px;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-dark fw-bold">
                <i class="fas fa-edit me-2 text-primary"></i> Edit Penjualan
            </h1>
            <p class="text-muted">Edit transaksi penjualan {{ $sale->invoice_number }}</p>
        </div>
        <div>
            <a href="{{ route('sales.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Kembali
            </a>
        </div>
    </div>

    <form action="{{ route('sales.update', $sale) }}" method="POST" id="editSaleForm">
        @csrf
        @method('PUT')

        <div class="row">
            <!-- Form Section -->
            <div class="col-lg-8">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 fw-bold text-primary">
                            <i class="fas fa-info-circle me-1"></i> Informasi Penjualan
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">No. Faktur</label>
                                    <input type="text" class="form-control" value="{{ $sale->invoice_number }}" readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Tanggal</label>
                                    <input type="text" class="form-control" value="{{ $sale->date->format('d/m/Y H:i') }}" readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Toko</label>
                                    <input type="text" class="form-control" value="{{ $sale->store->name }}" readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Kasir</label>
                                    <input type="text" class="form-control" value="{{ $sale->creator->name }}" readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="customer_name" class="form-label">Nama Pelanggan</label>
                                    <input type="text" class="form-control @error('customer_name') is-invalid @enderror"
                                           id="customer_name" name="customer_name"
                                           value="{{ old('customer_name', $sale->customer_name) }}"
                                           placeholder="Opsional">
                                    @error('customer_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="payment_type" class="form-label">Metode Pembayaran <span class="text-danger">*</span></label>
                                    <select class="form-select @error('payment_type') is-invalid @enderror"
                                            id="payment_type" name="payment_type" required>
                                        <option value="tunai" {{ old('payment_type', $sale->payment_type) == 'tunai' ? 'selected' : '' }}>Tunai</option>
                                        <option value="non_tunai" {{ old('payment_type', $sale->payment_type) == 'non_tunai' ? 'selected' : '' }}>Non Tunai</option>
                                    </select>
                                    @error('payment_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="dining_option" class="form-label">Pilihan Makan <span class="text-danger">*</span></label>
                                    <select class="form-select @error('dining_option') is-invalid @enderror"
                                            id="dining_option" name="dining_option" required>
                                        <option value="dibawa_pulang" {{ old('dining_option', $sale->dining_option) == 'dibawa_pulang' ? 'selected' : '' }}>Dibawa Pulang</option>
                                        <option value="makan_di_tempat" {{ old('dining_option', $sale->dining_option) == 'makan_di_tempat' ? 'selected' : '' }}>Makan di Tempat</option>
                                    </select>
                                    @error('dining_option')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Items Section -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 fw-bold text-primary">
                            <i class="fas fa-shopping-cart me-1"></i> Item Penjualan
                        </h6>
                        <button type="button" class="btn btn-sm btn-success" onclick="addNewItem()">
                            <i class="fas fa-plus me-1"></i> Tambah Item
                        </button>
                    </div>
                    <div class="card-body">
                        <div id="items-container">
                            @foreach($sale->saleDetails as $index => $detail)
                            <div class="product-row position-relative" data-index="{{ $index }}">
                                <button type="button" class="btn btn-sm btn-outline-danger delete-row-btn" onclick="removeItem(this)">
                                    <i class="fas fa-times"></i>
                                </button>

                                <div class="row">
                                    <div class="col-md-4">
                                        <label class="form-label">Produk <span class="text-danger">*</span></label>
                                        <select class="form-select product-select" name="items[{{ $index }}][product_id]" required onchange="updatePrice(this)">
                                            <option value="">Pilih Produk</option>
                                            @foreach($products as $product)
                                                <option value="{{ $product->id }}"
                                                        data-price="{{ $product->selling_price }}"
                                                        {{ $detail->product_id == $product->id ? 'selected' : '' }}>
                                                    {{ $product->name }} - Rp {{ number_format($product->selling_price, 0, ',', '.') }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Satuan <span class="text-danger">*</span></label>
                                        <select class="form-select" name="items[{{ $index }}][unit_id]" required>
                                            @foreach($units as $unit)
                                                <option value="{{ $unit->id }}" {{ $detail->unit_id == $unit->id ? 'selected' : '' }}>
                                                    {{ $unit->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Jumlah <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control quantity-input"
                                               name="items[{{ $index }}][quantity]"
                                               value="{{ $detail->quantity }}"
                                               min="0.01" step="0.01" required
                                               onchange="calculateSubtotal(this)">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Harga <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control price-input"
                                               name="items[{{ $index }}][price]"
                                               value="{{ $detail->price }}"
                                               min="0" required
                                               onchange="calculateSubtotal(this)">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Diskon</label>
                                        <input type="number" class="form-control discount-input"
                                               name="items[{{ $index }}][discount]"
                                               value="{{ $detail->discount }}"
                                               min="0"
                                               onchange="calculateSubtotal(this)">
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- Summary Section -->
            <div class="col-lg-4">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 fw-bold text-primary">
                            <i class="fas fa-calculator me-1"></i> Ringkasan
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="summary-section">
                            <div class="row mb-2">
                                <div class="col-6">Subtotal:</div>
                                <div class="col-6 text-end" id="subtotal-display">Rp 0</div>
                            </div>

                            <div class="row mb-2">
                                <div class="col-6">
                                    <label for="discount" class="form-label">Diskon:</label>
                                </div>
                                <div class="col-6">
                                    <input type="number" class="form-control form-control-sm text-end"
                                           id="discount" name="discount"
                                           value="{{ old('discount', $sale->discount) }}"
                                           min="0" onchange="calculateTotal()">
                                </div>
                            </div>

                            <div class="row mb-2">
                                <div class="col-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox"
                                               id="tax_enabled" name="tax_enabled" value="1"
                                               {{ old('tax_enabled', $sale->tax_enabled) ? 'checked' : '' }}
                                               onchange="calculateTotal()">
                                        <label class="form-check-label" for="tax_enabled">
                                            Pajak (10%):
                                        </label>
                                    </div>
                                </div>
                                <div class="col-6 text-end" id="tax-display">Rp {{ number_format($sale->tax, 0, ',', '.') }}</div>
                                <input type="hidden" id="tax" name="tax" value="{{ $sale->tax }}">
                            </div>

                            <hr>

                            <div class="row mb-3">
                                <div class="col-6 fw-bold">Total:</div>
                                <div class="col-6 text-end fw-bold text-primary fs-5" id="total-display">
                                    Rp {{ number_format($sale->total_amount, 0, ',', '.') }}
                                </div>
                            </div>

                            <div class="row mb-2">
                                <div class="col-6">
                                    <label for="total_payment" class="form-label">Dibayar:</label>
                                </div>
                                <div class="col-6">
                                    <input type="number" class="form-control form-control-sm text-end"
                                           id="total_payment" name="total_payment"
                                           value="{{ old('total_payment', $sale->total_payment) }}"
                                           min="0" required onchange="calculateChange()">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-6">Kembalian:</div>
                                <div class="col-6 text-end" id="change-display">Rp {{ number_format($sale->change, 0, ',', '.') }}</div>
                            </div>

                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-save me-1"></i> Update Penjualan
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Item Template for Adding New Items -->
<template id="item-template">
    <div class="product-row position-relative" data-index="__INDEX__">
        <button type="button" class="btn btn-sm btn-outline-danger delete-row-btn" onclick="removeItem(this)">
            <i class="fas fa-times"></i>
        </button>

        <div class="row">
            <div class="col-md-4">
                <label class="form-label">Produk <span class="text-danger">*</span></label>
                <select class="form-select product-select" name="items[__INDEX__][product_id]" required onchange="updatePrice(this)">
                    <option value="">Pilih Produk</option>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}" data-price="{{ $product->selling_price }}">
                            {{ $product->name }} - Rp {{ number_format($product->selling_price, 0, ',', '.') }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Satuan <span class="text-danger">*</span></label>
                <select class="form-select" name="items[__INDEX__][unit_id]" required>
                    @foreach($units as $unit)
                        <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Jumlah <span class="text-danger">*</span></label>
                <input type="number" class="form-control quantity-input"
                       name="items[__INDEX__][quantity]"
                       value="1" min="0.01" step="0.01" required
                       onchange="calculateSubtotal(this)">
            </div>
            <div class="col-md-2">
                <label class="form-label">Harga <span class="text-danger">*</span></label>
                <input type="number" class="form-control price-input"
                       name="items[__INDEX__][price]"
                       value="0" min="0" required
                       onchange="calculateSubtotal(this)">
            </div>
            <div class="col-md-2">
                <label class="form-label">Diskon</label>
                <input type="number" class="form-control discount-input"
                       name="items[__INDEX__][discount]"
                       value="0" min="0"
                       onchange="calculateSubtotal(this)">
            </div>
        </div>
    </div>
</template>
@endsection

@section('scripts')
<script>
    let itemIndex = {{ $sale->saleDetails->count() }};

    function addNewItem() {
        const template = document.getElementById('item-template');
        const container = document.getElementById('items-container');

        let newItem = template.innerHTML.replace(/__INDEX__/g, itemIndex);

        const div = document.createElement('div');
        div.innerHTML = newItem;

        container.appendChild(div.firstElementChild);
        itemIndex++;

        calculateTotal();
    }

    function removeItem(button) {
        const itemRow = button.closest('.product-row');
        if (document.querySelectorAll('.product-row').length > 1) {
            itemRow.remove();
            calculateTotal();
        } else {
            alert('Minimal harus ada 1 item dalam penjualan');
        }
    }

    function updatePrice(select) {
        const selectedOption = select.options[select.selectedIndex];
        const price = selectedOption.getAttribute('data-price') || 0;
        const row = select.closest('.product-row');
        const priceInput = row.querySelector('.price-input');

        priceInput.value = price;
        calculateSubtotal(priceInput);
    }

    function calculateSubtotal(input) {
        const row = input.closest('.product-row');
        const quantity = parseFloat(row.querySelector('.quantity-input').value) || 0;
        const price = parseFloat(row.querySelector('.price-input').value) || 0;
        const discount = parseFloat(row.querySelector('.discount-input').value) || 0;

        calculateTotal();
    }

    function calculateTotal() {
        let subtotal = 0;

        // Calculate subtotal from all items
        document.querySelectorAll('.product-row').forEach(row => {
            const quantity = parseFloat(row.querySelector('.quantity-input').value) || 0;
            const price = parseFloat(row.querySelector('.price-input').value) || 0;
            const discount = parseFloat(row.querySelector('.discount-input').value) || 0;

            subtotal += (quantity * price) - discount;
        });

        // Get discount and tax
        const globalDiscount = parseFloat(document.getElementById('discount').value) || 0;
        const taxEnabled = document.getElementById('tax_enabled').checked;

        // Calculate tax
        let tax = 0;
        if (taxEnabled) {
            tax = Math.round((subtotal - globalDiscount) * 0.1);
        }

        // Calculate total
        const total = subtotal - globalDiscount + tax;

        // Update displays
        document.getElementById('subtotal-display').textContent = 'Rp ' + formatNumber(subtotal);
        document.getElementById('tax-display').textContent = 'Rp ' + formatNumber(tax);
        document.getElementById('total-display').textContent = 'Rp ' + formatNumber(total);
        document.getElementById('tax').value = tax;

        calculateChange();
    }

    function calculateChange() {
        const totalElement = document.getElementById('total-display');
        const totalText = totalElement.textContent.replace(/[^\d]/g, '');
        const total = parseInt(totalText) || 0;

        const paid = parseFloat(document.getElementById('total_payment').value) || 0;
        const change = paid - total;

        document.getElementById('change-display').textContent = 'Rp ' + formatNumber(Math.max(0, change));
    }

    function formatNumber(number) {
        return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }

    // Initialize calculations on page load
    document.addEventListener('DOMContentLoaded', function() {
        calculateTotal();
    });

    // Form validation before submit
    document.getElementById('editSaleForm').addEventListener('submit', function(e) {
        const items = document.querySelectorAll('.product-row');
        let valid = true;
        let errorMessage = '';

        if (items.length === 0) {
            valid = false;
            errorMessage = 'Minimal harus ada 1 item dalam penjualan';
        }

        items.forEach((row, index) => {
            const productId = row.querySelector('select[name*="[product_id]"]').value;
            const quantity = row.querySelector('input[name*="[quantity]"]').value;
            const price = row.querySelector('input[name*="[price]"]').value;

            if (!productId) {
                valid = false;
                errorMessage = `Item ${index + 1}: Pilih produk`;
            }

            if (!quantity || parseFloat(quantity) <= 0) {
                valid = false;
                errorMessage = `Item ${index + 1}: Jumlah harus lebih dari 0`;
            }

            if (!price || parseFloat(price) < 0) {
                valid = false;
                errorMessage = `Item ${index + 1}: Harga tidak valid`;
            }
        });

        const totalPayment = parseFloat(document.getElementById('total_payment').value) || 0;
        const paymentType = document.getElementById('payment_type').value;

        if (paymentType === 'tunai') {
            const totalText = document.getElementById('total-display').textContent.replace(/[^\d]/g, '');
            const total = parseInt(totalText) || 0;

            if (totalPayment < total) {
                valid = false;
                errorMessage = 'Jumlah pembayaran tidak boleh kurang dari total untuk pembayaran tunai';
            }
        }

        if (!valid) {
            e.preventDefault();
            alert(errorMessage);
            return false;
        }
    });
</script>
@endsection
