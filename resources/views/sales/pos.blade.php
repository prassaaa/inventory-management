@extends('layouts.app')

@section('title', 'Kasir (POS)')

@section('styles')
<style>
    /* POS specific styles */
    .product-card {
        cursor: pointer;
        transition: all 0.2s;
        height: 100%;
        border-radius: 0.75rem;
        border: none;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }

    .product-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }

    .product-image {
        height: 100px;
        object-fit: contain;
        padding: 10px;
    }

    .cart-container {
        min-height: 400px;
        max-height: 400px;
        overflow-y: auto;
    }

    .totals-section {
        background-color: #f8fafc;
        padding: 15px;
        border-radius: 0.75rem;
    }

    .products-container {
        min-height: 600px;
        max-height: 600px;
        overflow-y: auto;
    }

    .category-pills {
        overflow-x: auto;
        white-space: nowrap;
        padding-bottom: 10px;
    }

    .category-pill {
        display: inline-block;
        padding: 8px 15px;
        margin-right: 5px;
        border-radius: 999px;
        background-color: #f3f4f6;
        cursor: pointer;
        transition: all 0.2s;
    }

    .category-pill.active {
        background-color: #2563eb;
        color: white;
    }

    .processed-badge {
        position: absolute;
        top: 5px;
        right: 5px;
        z-index: 10;
        padding: 3px 8px;
        border-radius: 999px;
        font-size: 10px;
        background-color: #fb923c;
        color: white;
    }

    .custom-price-badge {
        position: absolute;
        top: 5px;
        left: 5px;
        z-index: 10;
        padding: 3px 8px;
        border-radius: 999px;
        font-size: 10px;
        background-color: #10b981;
        color: white;
    }

    /* Custom scrollbar */
    .products-container::-webkit-scrollbar,
    .cart-container::-webkit-scrollbar {
        width: 6px;
    }

    .products-container::-webkit-scrollbar-track,
    .cart-container::-webkit-scrollbar-track {
        background: #f8fafc;
    }

    .products-container::-webkit-scrollbar-thumb,
    .cart-container::-webkit-scrollbar-thumb {
        background-color: #d1d5db;
        border-radius: 20px;
    }

    .products-container::-webkit-scrollbar-thumb:hover,
    .cart-container::-webkit-scrollbar-thumb:hover {
        background-color: #9ca3af;
    }

    /* Loading spinner for price update */
    .price-loading {
        opacity: 0.6;
        position: relative;
    }

    .price-loading::after {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: 16px;
        height: 16px;
        margin: -8px 0 0 -8px;
        border: 2px solid #f3f3f3;
        border-top: 2px solid #2563eb;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-dark fw-bold">
                <i class="fas fa-cash-register me-2 text-primary"></i> Kasir (POS)
            </h1>
            <p class="text-muted">Proses transaksi penjualan dengan cepat dan mudah</p>
        </div>
        <div>
            <button type="button" class="btn btn-outline-info me-2" id="refresh-prices">
                <i class="fas fa-sync-alt me-1"></i> Refresh Harga
            </button>
            <a href="{{ route('sales.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-list me-1"></i> Lihat Penjualan
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Products Section (Moved to the left) -->
        <div class="col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h6 class="m-0 fw-bold text-primary">
                                <i class="fas fa-box me-1"></i> Produk
                            </h6>
                        </div>
                        <div class="col-md-6">
                            <div class="input-group">
                                <span class="input-group-text bg-light border-0">
                                    <i class="fas fa-search text-primary"></i>
                                </span>
                                <input type="text" class="form-control border-0 bg-light" id="search-product" placeholder="Cari produk...">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="category-pills mb-3">
                        @foreach($categories as $category)
                            <div class="category-pill" data-category="{{ $category->id }}">{{ $category->name }}</div>
                        @endforeach
                    </div>

                    <div class="products-container">
                        <div class="row" id="products-grid">
                            @foreach($products as $product)
                            <div class="col-md-4 col-lg-3 mb-3 product-item"
                                 data-category="{{ $product->category_id }}"
                                 data-name="{{ strtolower($product->name) }}"
                                 data-code="{{ strtolower($product->code) }}">
                                <div class="card product-card"
                                    data-id="{{ $product->id }}"
                                    data-code="{{ $product->code }}"
                                    data-name="{{ $product->name }}"
                                    data-base-price="{{ $product->selling_price }}"
                                    data-unit-id="{{ $product->base_unit_id }}"
                                    data-unit-name="{{ $product->baseUnit->name }}"
                                    data-is-processed="{{ $product->is_processed ? 'true' : 'false' }}">

                                    @if($product->is_processed)
                                        <span class="processed-badge">
                                            <i class="fas fa-mortar-pestle me-1"></i> Olahan
                                        </span>
                                    @endif

                                    <span class="custom-price-badge d-none" id="custom-badge-{{ $product->id }}">
                                        <i class="fas fa-tag me-1"></i> Custom
                                    </span>

                                    <div class="text-center">
                                        @if($product->image)
                                            <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="product-image">
                                        @else
                                            <div class="product-image d-flex align-items-center justify-content-center bg-light">
                                                <i class="fas fa-box fa-3x text-secondary"></i>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="card-body p-2 text-center">
                                        <h6 class="card-title mb-1 text-truncate">{{ $product->name }}</h6>
                                        <p class="card-text text-primary fw-bold mb-0" id="price-{{ $product->id }}">
                                            Rp {{ number_format($product->selling_price, 0, ',', '.') }}
                                        </p>
                                        <small class="text-muted d-block" id="unit-{{ $product->id }}">
                                            {{ $product->baseUnit->name }}
                                        </small>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cart Section (Moved to the right) -->
        <div class="col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 fw-bold text-primary">
                        <i class="fas fa-shopping-cart me-1"></i> Keranjang Belanja
                    </h6>
                    <button type="button" class="btn btn-sm btn-outline-danger" id="clear-cart">
                        <i class="fas fa-trash me-1"></i> Kosongkan
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="cart-container">
                        <table class="table table-bordered table-sm mb-0" id="cart-table">
                            <thead class="table-light">
                                <tr>
                                    <th>Produk</th>
                                    <th width="80">Qty</th>
                                    <th width="120">Harga</th>
                                    <th width="120">Subtotal</th>
                                    <th width="40">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Cart items will be added here dynamically -->
                                <tr id="empty-cart-row">
                                    <td colspan="5" class="text-center py-3 text-muted">Keranjang kosong</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="totals-section">
                        <div class="row mb-2">
                            <div class="col-6 text-end">Subtotal:</div>
                            <div class="col-6 text-end fw-medium" id="subtotal">0</div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-6 text-end">Diskon:</div>
                            <div class="col-6">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" class="form-control form-control-sm text-end" id="discount" value="0" min="0">
                                </div>
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-6 text-end">Pajak (10%):</div>
                            <div class="col-6">
                                <div class="d-flex justify-content-end align-items-center">
                                    <div class="form-check form-switch me-2">
                                        <input class="form-check-input" type="checkbox" id="tax-enabled">
                                        <label class="form-check-label" for="tax-enabled">Aktif</label>
                                    </div>
                                    <span class="fw-medium" id="tax">0</span>
                                </div>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-6 text-end fw-bold text-primary">Total:</div>
                            <div class="col-6 text-end fw-bold text-primary fs-5" id="total">0</div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-6 text-end">Metode Pembayaran:</div>
                            <div class="col-6">
                                <select class="form-select form-select-sm" id="payment-type">
                                    <option value="tunai">Tunai</option>
                                    <option value="non_tunai">Non Tunai</option>
                                </select>
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-6 text-end">Jumlah Dibayar:</div>
                            <div class="col-6">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" class="form-control form-control-sm text-end" id="paid-amount" value="0" min="0">
                                </div>
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-6 text-end">Kembalian:</div>
                            <div class="col-6 text-end fw-medium" id="change">0</div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-6 text-end">Pilihan Makan:</div>
                            <div class="col-6">
                                <select class="form-select form-select-sm" id="dining-option">
                                    <option value="dibawa_pulang">Dibawa Pulang</option>
                                    <option value="makan_di_tempat">Makan di Tempat</option>
                                </select>
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-6 text-end">Nama Pelanggan:</div>
                            <div class="col-6">
                                <input type="text" class="form-control form-control-sm" id="customer-name" placeholder="Opsional">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <button type="button" class="btn btn-success btn-block w-100" id="process-payment">
                                    <i class="fas fa-cash-register me-1"></i> Proses Pembayaran
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Unit Selection Modal -->
<div class="modal fade" id="unit-selection-modal" tabindex="-1" aria-labelledby="unitSelectionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="unitSelectionModalLabel">
                    <i class="fas fa-box me-2"></i>
                    <span id="modal-product-name"></span>
                    <span id="modal-processed-badge" class="badge bg-warning ms-2 d-none">
                        <i class="fas fa-mortar-pestle me-1"></i>Olahan
                    </span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-bold">Pilih Unit & Harga:</label>
                    <div class="row" id="unit-options-container">
                        <!-- Unit options will be populated here -->
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Jumlah:</label>
                    <div class="input-group">
                        <button class="btn btn-outline-secondary" type="button" id="decrease-qty">
                            <i class="fas fa-minus"></i>
                        </button>
                        <input type="number" class="form-control text-center" id="product-quantity" value="1" min="0.01" step="0.01">
                        <button class="btn btn-outline-secondary" type="button" id="increase-qty">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Total: <span id="modal-total">Rp 0</span></strong>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Batal
                </button>
                <button type="button" class="btn btn-primary" id="add-to-cart-btn">
                    <i class="fas fa-cart-plus me-1"></i>Tambah ke Keranjang
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Payment Success Modal -->
<div class="modal fade" id="payment-success-modal" tabindex="-1" aria-labelledby="paymentSuccessModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="paymentSuccessModalLabel">Pembayaran Berhasil</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <i class="fas fa-check-circle fa-5x text-success mb-3"></i>
                <h5>Transaksi penjualan telah berhasil diproses!</h5>
                <p>Faktur: <span id="success-invoice" class="fw-bold"></span></p>
                <p>Total: <span id="success-total" class="fw-bold"></span></p>
                <p>Kembalian: <span id="success-change" class="fw-bold"></span></p>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-outline-secondary" id="print-receipt-btn">
                    <i class="fas fa-print me-1"></i> Cetak Struk
                </button>
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Transaksi Baru</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
    // Variables
    let cartItems = [];
    let taxRate = 0.1; // 10%
    let taxEnabled = false; // Pajak dinonaktifkan secara default
    const storeId = {{ Auth::user()->store_id ?? 1 }}; // Store ID dari user yang login
    let currentProduct = null; // Product yang sedang dipilih
    let availablePrices = {}; // Cache untuk harga produk
    let productStorePrices = {}; // Cache untuk semua harga store produk

    // Format number with thousand separator
    function formatNumber(number) {
        // Convert to number if it's a string
        const num = typeof number === 'string' ? parseFloat(number) : number;

        // Check if it's a valid number
        if (isNaN(num) || num === null || num === undefined) {
            console.warn('Invalid number for formatting:', number);
            return '0';
        }

        return num.toFixed(0).replace(/\d(?=(\d{3})+$)/g, '$&.');
    }

    // Show loading indicator
    function showLoading(message = 'Loading...') {
        Swal.fire({
            title: message,
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
    }

    // Hide loading indicator
    function hideLoading() {
        Swal.close();
    }

    // Show alert/notification
    function showAlert(title, message, type = 'error') {
        Swal.fire({
            icon: type,
            title: title,
            text: message,
            confirmButtonText: 'OK'
        });
    }

    // Load all product store prices on page load
    async function loadAllProductStorePrices() {
        try {
            console.log('Loading all product store prices...');

            // Get all product IDs from the page
            const productIds = [];
            $('.product-card').each(function() {
                const productId = $(this).data('id');
                if (productId) {
                    productIds.push(productId);
                }
            });

            console.log('Found products:', productIds);

            // Load prices for each product
            const promises = productIds.map(async (productId) => {
                try {
                    const prices = await getProductStorePrices(productId);
                    if (prices && prices.length > 0) {
                        productStorePrices[productId] = prices;
                        updateProductCardPrice(productId, prices);
                    }
                } catch (error) {
                    console.error(`Error loading prices for product ${productId}:`, error);
                }
            });

            await Promise.all(promises);
            console.log('All product prices loaded:', productStorePrices);

        } catch (error) {
            console.error('Error loading all product store prices:', error);
        }
    }

    // Update product card price display
    function updateProductCardPrice(productId, prices) {
        if (!prices || prices.length === 0) return;

        // Find base unit price
        const baseUnitPrice = prices.find(p => p.is_base_unit);
        if (!baseUnitPrice) return;

        const price = parseFloat(baseUnitPrice.price);
        const hasCustomPrice = baseUnitPrice.has_custom_price;

        // Update price display
        $(`#price-${productId}`).text(`Rp ${formatNumber(price)}`);

        // Update product card data
        $(`.product-card[data-id="${productId}"]`).attr('data-current-price', price);
        $(`.product-card[data-id="${productId}"]`).attr('data-has-custom-price', hasCustomPrice);

        // Show/hide custom price badge
        if (hasCustomPrice) {
            $(`#custom-badge-${productId}`).removeClass('d-none');
        } else {
            $(`#custom-badge-${productId}`).addClass('d-none');
        }

        console.log(`Updated price for product ${productId}: Rp ${formatNumber(price)} (Custom: ${hasCustomPrice})`);
    }

    // Get all prices for a product in specific store (API call)
    async function getProductStorePrices(productId) {
        try {
            const url = `/api/products/store-prices?product_id=${productId}&store_id=${storeId}`;

            const response = await fetch(url, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const contentType = response.headers.get("content-type");
            if (!contentType || !contentType.includes("application/json")) {
                const text = await response.text();
                console.error('Non-JSON response received:', text.substring(0, 200));
                throw new Error('Server returned non-JSON response');
            }

            const data = await response.json();

            if (data.success) {
                // Validate price data
                const validPrices = data.prices.filter(price => {
                    if (!price.price || isNaN(parseFloat(price.price))) {
                        console.warn('Invalid price in response:', price);
                        return false;
                    }
                    return true;
                });

                return validPrices;
            }
            throw new Error(data.message || 'Unknown error');
        } catch (error) {
            console.error('Error getting store prices:', error);
            return null;
        }
    }

    // Calculate subtotal
    function calculateSubtotal() {
        let subtotal = 0;
        cartItems.forEach(item => {
            subtotal += item.quantity * item.price;
        });
        return subtotal;
    }

    // Calculate tax
    function calculateTax(subtotal) {
        if (!taxEnabled) {
            return 0;
        }
        const discount = parseFloat($('#discount').val()) || 0;
        return Math.round((subtotal - discount) * taxRate);
    }

    // Calculate total
    function calculateTotal() {
        const subtotal = calculateSubtotal();
        const discount = parseFloat($('#discount').val()) || 0;
        const tax = calculateTax(subtotal);
        return subtotal - discount + tax;
    }

    // Update cart table
    function updateCartTable() {
        if (cartItems.length === 0) {
            $('#empty-cart-row').show();
        } else {
            $('#empty-cart-row').hide();
        }

        $('#cart-table tbody tr:not(#empty-cart-row)').remove();

        cartItems.forEach((item, index) => {
            const isProcessed = item.is_processed ? '<span class="badge bg-warning text-white ms-1"><i class="fas fa-mortar-pestle"></i></span>' : '';
            const customPriceBadge = item.has_custom_price ? '<span class="badge bg-success ms-1" title="Harga Khusus Store"><i class="fas fa-tag"></i></span>' : '';

            const row = `
                <tr>
                    <td class="text-truncate" style="max-width: 120px;">
                        ${item.name} ${isProcessed} ${customPriceBadge}
                        <small class="d-block text-muted">${item.unit_name}</small>
                    </td>
                    <td>
                        <input type="number" class="form-control form-control-sm item-quantity" data-index="${index}" value="${item.quantity}" min="1" step="0.01">
                    </td>
                    <td class="text-end">Rp ${formatNumber(item.price)}</td>
                    <td class="text-end">Rp ${formatNumber(item.quantity * item.price)}</td>
                    <td>
                        <button type="button" class="btn btn-sm btn-outline-danger remove-item" data-index="${index}">
                            <i class="fas fa-times"></i>
                        </button>
                    </td>
                </tr>
            `;

            $('#cart-table tbody').append(row);
        });

        updateTotals();
    }

    // Update totals
    function updateTotals() {
        const subtotal = calculateSubtotal();
        const discount = parseFloat($('#discount').val()) || 0;
        const tax = calculateTax(subtotal);
        const total = calculateTotal();
        const paidAmount = parseFloat($('#paid-amount').val()) || 0;
        const change = paidAmount - total > 0 ? paidAmount - total : 0;

        $('#subtotal').text("Rp " + formatNumber(subtotal));
        $('#tax').text("Rp " + formatNumber(tax));
        $('#total').text("Rp " + formatNumber(total));
        $('#change').text("Rp " + formatNumber(change));
    }

    // Show unit selection modal
    function showUnitSelectionModal(productId) {
        const productPrices = productStorePrices[productId];
        if (!productPrices || productPrices.length === 0) {
            showAlert('Error', 'Harga produk belum dimuat. Silakan refresh halaman.');
            return;
        }

        const productCard = $(`.product-card[data-id="${productId}"]`);
        const productName = productCard.data('name');
        const isProcessed = productCard.data('is-processed') === 'true' || productCard.data('is-processed') === true;

        // Set modal title
        $('#modal-product-name').text(productName);
        if (isProcessed) {
            $('#modal-processed-badge').removeClass('d-none');
        } else {
            $('#modal-processed-badge').addClass('d-none');
        }

        // Populate unit options
        let optionsHtml = '';
        productPrices.forEach((priceData, index) => {
            const price = parseFloat(priceData.price);
            const customBadge = priceData.has_custom_price ?
                '<span class="badge bg-success ms-2" title="Harga Khusus Store"><i class="fas fa-tag me-1"></i>Custom</span>' : '';
            const baseBadge = priceData.is_base_unit ?
                '<span class="badge bg-primary ms-2">Unit Dasar</span>' : '';

            optionsHtml += `
                <div class="col-md-6 mb-2">
                    <div class="unit-option card h-100 ${index === 0 ? 'border-primary' : ''}"
                         data-unit-id="${priceData.unit_id}"
                         data-price="${price}"
                         data-has-custom-price="${priceData.has_custom_price}">
                        <div class="card-body p-3 cursor-pointer unit-selector ${index === 0 ? 'selected' : ''}">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>${priceData.unit_name}</strong>
                                    ${customBadge}
                                    ${baseBadge}
                                </div>
                                <div class="text-end">
                                    <div class="h5 mb-0 text-primary">Rp ${formatNumber(price)}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });

        $('#unit-options-container').html(optionsHtml);

        // Set current product
        currentProduct = {
            id: productId,
            name: productName,
            prices: productPrices,
            is_processed: isProcessed
        };

        // Reset quantity
        $('#product-quantity').val(1);

        // Update modal total
        updateModalTotal();

        // Show modal
        $('#unit-selection-modal').modal('show');
    }

    // Update modal total when quantity or unit changes
    function updateModalTotal() {
        const selectedUnit = $('.unit-selector.selected').closest('.unit-option');
        const price = parseFloat(selectedUnit.data('price')) || 0;
        const quantity = parseFloat($('#product-quantity').val()) || 0;
        const total = price * quantity;

        $('#modal-total').text('Rp ' + formatNumber(total));
    }

    // Add selected product to cart from modal
    function addSelectedProductToCart() {
        const selectedUnit = $('.unit-selector.selected').closest('.unit-option');
        const quantity = parseFloat($('#product-quantity').val());

        if (!selectedUnit.length) {
            showAlert('Error', 'Pilih unit terlebih dahulu');
            return;
        }

        if (!quantity || quantity <= 0) {
            showAlert('Error', 'Masukkan jumlah yang valid');
            return;
        }

        const unitId = selectedUnit.data('unit-id');
        const price = parseFloat(selectedUnit.data('price'));
        const hasCustomPrice = selectedUnit.data('has-custom-price');
        const unitName = selectedUnit.find('.unit-selector strong').text();

        // Check if product with same unit already exists in cart
        const existingItemIndex = cartItems.findIndex(item =>
            item.product_id == currentProduct.id && item.unit_id == unitId
        );

        if (existingItemIndex > -1) {
            // Update quantity if already exists
            cartItems[existingItemIndex].quantity += quantity;
        } else {
            // Add new item
            cartItems.push({
                product_id: currentProduct.id,
                name: currentProduct.name,
                price: price,
                quantity: quantity,
                unit_id: unitId,
                unit_name: unitName,
                is_processed: currentProduct.is_processed, // Keep as boolean for cart display
                has_custom_price: hasCustomPrice
            });
        }

        // Update cart display
        updateCartTable();

        // Close modal
        $('#unit-selection-modal').modal('hide');

        // Show success message
        showAlert('Berhasil', 'Produk berhasil ditambahkan ke keranjang', 'success');
    }

    // Direct add to cart with base unit (when product has only one unit or single click)
    function directAddToCart(productId) {
        const productPrices = productStorePrices[productId];
        if (!productPrices || productPrices.length === 0) {
            showAlert('Error', 'Harga produk belum dimuat. Silakan refresh halaman.');
            return;
        }

        // If product has multiple units, show modal
        if (productPrices.length > 1) {
            showUnitSelectionModal(productId);
            return;
        }

        // Direct add with base unit
        const productCard = $(`.product-card[data-id="${productId}"]`);
        const productName = productCard.data('name');
        const isProcessed = (productCard.data('is-processed') === 'true' || productCard.data('is-processed') === true);
        const basePrice = productPrices[0];

        const price = parseFloat(basePrice.price);
        const unitId = basePrice.unit_id;
        const unitName = basePrice.unit_name;
        const hasCustomPrice = basePrice.has_custom_price;

        // Check if product with same unit already exists in cart
        const existingItemIndex = cartItems.findIndex(item =>
            item.product_id == productId && item.unit_id == unitId
        );

        if (existingItemIndex > -1) {
            // Update quantity if already exists
            cartItems[existingItemIndex].quantity += 1;
        } else {
            // Add new item
            cartItems.push({
                product_id: productId,
                name: productName,
                price: price,
                quantity: 1,
                unit_id: unitId,
                unit_name: unitName,
                is_processed: isProcessed, // Keep as boolean for cart display
                has_custom_price: hasCustomPrice
            });
        }

        // Update cart display
        updateCartTable();

        // Show success message
        showAlert('Berhasil', 'Produk berhasil ditambahkan ke keranjang', 'success');
    }

    // Event Handlers

    // Handle product card click
    $(document).on('click', '.product-card', function() {
        const productId = $(this).data('id');
        directAddToCart(productId);
    });

    // Handle product card double click for unit selection
    $(document).on('dblclick', '.product-card', function(e) {
        e.stopPropagation();
        const productId = $(this).data('id');
        showUnitSelectionModal(productId);
    });

    // Unit selection in modal
    $(document).on('click', '.unit-selector', function() {
        $('.unit-selector').removeClass('selected');
        $('.unit-option').removeClass('border-primary');

        $(this).addClass('selected');
        $(this).closest('.unit-option').addClass('border-primary');

        updateModalTotal();
    });

    // Quantity controls in modal
    $(document).on('click', '#increase-qty', function() {
        const qtyInput = $('#product-quantity');
        const currentQty = parseFloat(qtyInput.val()) || 0;
        qtyInput.val(currentQty + 1);
        updateModalTotal();
    });

    $(document).on('click', '#decrease-qty', function() {
        const qtyInput = $('#product-quantity');
        const currentQty = parseFloat(qtyInput.val()) || 0;
        if (currentQty > 0.01) {
            qtyInput.val(Math.max(0.01, currentQty - 1));
            updateModalTotal();
        }
    });

    $(document).on('input', '#product-quantity', function() {
        const qty = parseFloat($(this).val()) || 0;
        if (qty < 0.01) {
            $(this).val(0.01);
        }
        updateModalTotal();
    });

    // Add to cart button in modal
    $(document).on('click', '#add-to-cart-btn', function() {
        addSelectedProductToCart();
    });

    // Toggle tax
    $('#tax-enabled').change(function() {
        taxEnabled = $(this).is(':checked');
        updateTotals();
    });

    // Filter products by category
    $('.category-pill').click(function() {
        const category = $(this).data('category');
        $('.category-pill').removeClass('active');
        $(this).addClass('active');

        // Clear search when category is selected
        $('#search-product').val('');

        // Hide all products first
        $('.product-item').hide();
        // Show only products with selected category
        $('.product-item[data-category="' + category + '"]').show();
    });

    // Set first category as default
    setTimeout(function() {
        if ($('.category-pill').length > 0) {
            $('.category-pill:eq(0)').addClass('active').trigger('click');
        } else {
            $('.product-item').show();
        }
    }, 100);

    // Search products
    $('#search-product').on('input', function() {
        const searchTerm = $(this).val().toLowerCase();

        if (searchTerm.length > 0) {
            // Clear category selection when searching
            $('.category-pill').removeClass('active');

            $('.product-item').hide();
            $('.product-item').each(function() {
                const productName = $(this).data('name');
                const productCode = $(this).data('code');

                if (productName.includes(searchTerm) || productCode.includes(searchTerm)) {
                    $(this).show();
                }
            });
        } else {
            // If search is cleared, reapply category filter
            const activeCategory = $('.category-pill.active').data('category');
            if (activeCategory) {
                $('.product-item').hide();
                $('.product-item[data-category="' + activeCategory + '"]').show();
            } else {
                $('.product-item').show();
            }
        }
    });

    // Update item quantity in cart
    $(document).on('change', '.item-quantity', function() {
        const index = $(this).data('index');
        const newQuantity = parseFloat($(this).val()) || 0.01;

        if (newQuantity < 0.01) {
            $(this).val(0.01);
            cartItems[index].quantity = 0.01;
        } else {
            cartItems[index].quantity = newQuantity;
        }

        updateCartTable();
    });

    // Remove item from cart
    $(document).on('click', '.remove-item', function() {
        const index = $(this).data('index');
        cartItems.splice(index, 1);
        updateCartTable();
    });

    // Clear cart
    $('#clear-cart').click(function() {
        if (cartItems.length > 0) {
            Swal.fire({
                title: 'Kosongkan Keranjang?',
                text: "Semua produk akan dihapus dari keranjang",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, Kosongkan!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    cartItems = [];
                    updateCartTable();
                }
            });
        }
    });

    // Refresh prices button
    $('#refresh-prices').click(function() {
        showLoading('Memuat ulang harga produk...');

        // Clear cache
        productStorePrices = {};

        // Reload all prices
        loadAllProductStorePrices().then(() => {
            hideLoading();
            showAlert('Berhasil', 'Harga produk berhasil dimuat ulang', 'success');
        }).catch((error) => {
            hideLoading();
            showAlert('Error', 'Gagal memuat ulang harga produk');
        });
    });

    // Update totals when discount changes
    $('#discount').on('input', function() {
        updateTotals();
    });

    // Update change when paid amount changes
    $('#paid-amount').on('input', function() {
        updateTotals();
    });

    // Process payment
    $('#process-payment').click(function() {
        if (cartItems.length === 0) {
            Swal.fire({
                icon: 'error',
                title: 'Keranjang Kosong',
                text: 'Silakan tambahkan produk ke keranjang terlebih dahulu'
            });
            return;
        }

        const subtotal = calculateSubtotal();
        const discount = parseFloat($('#discount').val()) || 0;
        const tax = calculateTax(subtotal);
        const total = calculateTotal();
        const paidAmount = parseFloat($('#paid-amount').val()) || 0;
        const paymentType = $('#payment-type').val();
        const diningOption = $('#dining-option').val();
        const customerName = $('#customer-name').val();

        if (paidAmount < total && paymentType === 'tunai') {
            Swal.fire({
                icon: 'error',
                title: 'Pembayaran Kurang',
                text: 'Jumlah pembayaran tidak boleh kurang dari total untuk pembayaran tunai'
            });
            return;
        }

        // Prepare items for submission with proper boolean conversion
        const itemsForSubmission = cartItems.map(item => ({
            product_id: item.product_id,
            unit_id: item.unit_id,
            quantity: item.quantity,
            price: item.price,
            discount: 0,
            subtotal: item.quantity * item.price,
            is_processed: item.is_processed ? true : false // Explicit boolean conversion
        }));

        const data = {
            store_id: storeId,
            payment_type: paymentType,
            dining_option: diningOption,
            customer_name: customerName,
            discount: discount,
            tax_enabled: taxEnabled ? true : false, // Explicit boolean conversion
            tax: tax,
            total_amount: total,
            total_payment: paidAmount,
            change: paidAmount - total > 0 ? paidAmount - total : 0,
            items: itemsForSubmission
        };

        // Show loading indicator
        Swal.fire({
            title: 'Sedang Memproses',
            text: 'Mohon tunggu, transaksi sedang diproses...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        console.log('Sending payment data:', data); // Debug log

        // Submit to server
        $.ajax({
            url: "{{ route('pos.process') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                sale: data
            },
            success: function(response) {
                Swal.close();

                if (response.success) {
                    // Show success modal
                    $('#success-invoice').text(response.invoice_number);
                    $('#success-total').text("Rp " + formatNumber(total));
                    $('#success-change').text("Rp " + formatNumber(paidAmount - total > 0 ? paidAmount - total : 0));

                    // Store receipt URL
                    window.receiptUrl = response.receipt_url;

                    // Show success modal
                    $('#payment-success-modal').modal('show');

                    // Clear cart for new transaction
                    cartItems = [];
                    updateCartTable();
                    $('#discount').val(0);
                    $('#paid-amount').val(0);
                    $('#dining-option').val('dibawa_pulang');
                    $('#customer-name').val('');
                    $('#tax-enabled').prop('checked', false);
                    taxEnabled = false;

                    console.log('Transaksi berhasil:', response);
                } else {
                    console.error('Error dengan response success=false:', response);
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: response.message || 'Terjadi kesalahan saat memproses penjualan',
                        confirmButtonText: 'Coba Lagi'
                    });
                }
            },
            error: function(xhr) {
                Swal.close();

                console.error('AJAX Error:', xhr);
                console.error('Response Text:', xhr.responseText);

                let errorMessage = 'Terjadi kesalahan saat memproses penjualan.';
                let errorDetails = [];

                if (xhr.responseJSON) {
                    if (xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }

                    // Handle stock issues specifically
                    if (xhr.responseJSON.stock_issues) {
                        errorDetails = xhr.responseJSON.stock_issues;
                        errorMessage = 'Stok tidak mencukupi untuk beberapa produk:';
                    }

                    // Handle validation errors
                    if (xhr.responseJSON.errors) {
                        const validationErrors = xhr.responseJSON.errors;
                        console.error('Validation errors:', validationErrors);

                        for (const field in validationErrors) {
                            errorDetails.push(`${field}: ${validationErrors[field][0]}`);
                        }
                    }
                }

                // Show detailed error message
                if (errorDetails.length > 0) {
                    let detailsHtml = '<div class="text-start"><ul style="margin: 10px 0; padding-left: 20px;">';
                    errorDetails.forEach(detail => {
                        detailsHtml += `<li>${detail}</li>`;
                    });
                    detailsHtml += '</ul></div>';

                    Swal.fire({
                        icon: 'error',
                        title: 'Transaksi Gagal',
                        html: `<div class="text-start">${errorMessage}</div>${detailsHtml}`,
                        width: '600px',
                        confirmButtonText: 'OK',
                        customClass: {
                            htmlContainer: 'text-start'
                        }
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: errorMessage,
                        confirmButtonText: 'Coba Lagi'
                    });
                }
            }
        });
    });

    // Handle print receipt button
    $(document).on('click', '#print-receipt-btn', function() {
        if (window.receiptUrl) {
            // Detect if Android device
            var isAndroid = /Android/i.test(navigator.userAgent);

            if (isAndroid) {
                // For Android, use rawbt-receipt
                let rawbtUrl = window.receiptUrl.replace('/receipt', '/rawbt-receipt');
                window.location.href = rawbtUrl;
            } else {
                // For non-Android, use normal way
                const printWindow = window.open(window.receiptUrl, '_blank', 'width=800,height=600');
                if (printWindow) {
                    printWindow.focus();
                } else {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Popup Diblokir',
                        text: 'Mohon izinkan popup untuk mencetak struk.'
                    });
                }
            }
        }
    });

    // Handle modal cleanup when hidden
    $(document).on('hidden.bs.modal', '#unit-selection-modal', function () {
        currentProduct = null;
        $('#unit-options-container').empty();
        $('#modal-processed-badge').addClass('d-none');
    });

    // Handle keyboard shortcuts
    $(document).keydown(function(e) {
        // ESC to clear search
        if (e.keyCode === 27) {
            $('#search-product').val('').trigger('input');
        }

        // F1 to focus search
        if (e.keyCode === 112) {
            e.preventDefault();
            $('#search-product').focus();
        }

        // F2 to clear cart
        if (e.keyCode === 113) {
            e.preventDefault();
            $('#clear-cart').trigger('click');
        }

        // F3 to process payment
        if (e.keyCode === 114) {
            e.preventDefault();
            $('#process-payment').trigger('click');
        }

        // F5 to refresh prices
        if (e.keyCode === 116) {
            e.preventDefault();
            $('#refresh-prices').trigger('click');
        }
    });

    // Initialize on page load
    updateCartTable();

    // Add CSRF token to all AJAX requests
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // Load all product store prices on page load
    showLoading('Memuat harga produk...');
    loadAllProductStorePrices().then(() => {
        hideLoading();
        console.log('POS System initialized with Store ID:', storeId);
        console.log('Available keyboard shortcuts:');
        console.log('- ESC: Clear search');
        console.log('- F1: Focus search');
        console.log('- F2: Clear cart');
        console.log('- F3: Process payment');
        console.log('- F5: Refresh prices');
        console.log('- Single click: Add to cart with base unit');
        console.log('- Double click: Show unit selection modal');
    }).catch((error) => {
        hideLoading();
        console.error('Error initializing POS:', error);
        showAlert('Error', 'Gagal memuat harga produk. Silakan refresh halaman.');
    });
});
</script>
@endsection
