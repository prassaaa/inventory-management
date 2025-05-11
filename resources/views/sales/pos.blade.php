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

    /* Style untuk produk dengan stok habis */
    .product-card.out-of-stock {
        opacity: 0.7;
        cursor: not-allowed;
        position: relative;
        overflow: hidden;
    }

    .product-card.out-of-stock::before {
        content: "STOK HABIS";
        position: absolute;
        width: 150%;
        top: 45%;
        left: -25%;
        text-align: center;
        background-color: rgba(220, 53, 69, 0.8);
        color: white;
        font-weight: bold;
        padding: 5px 0;
        transform: rotate(-35deg);
        z-index: 10;
        font-size: 12px;
        letter-spacing: 1px;
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
                        <!-- Menghilangkan "Semua Kategori" -->
                        @foreach($categories as $category)
                            <div class="category-pill" data-category="{{ $category->id }}">{{ $category->name }}</div>
                        @endforeach
                    </div>

                    <div class="products-container">
                        <div class="row" id="products-grid">
                            @foreach($products as $product)
                            @php
                                $productStock = $product->storeStock ? $product->storeStock->quantity : 0;
                                $outOfStock = $productStock <= 0;
                            @endphp
                            <div class="col-md-4 col-lg-3 mb-3 product-item" data-category="{{ $product->category_id }}" data-name="{{ strtolower($product->name) }}" data-code="{{ strtolower($product->code) }}">
                                <div class="card product-card {{ $outOfStock ? 'out-of-stock' : '' }}"
                                    data-id="{{ $product->id }}"
                                    data-code="{{ $product->code }}"
                                    data-name="{{ $product->name }}"
                                    data-price="{{ $product->selling_price }}"
                                    data-unit-id="{{ $product->base_unit_id }}"
                                    data-unit-name="{{ $product->baseUnit->name }}"
                                    data-stock="{{ $productStock }}"
                                    data-is-processed="{{ $product->is_processed ? 'true' : 'false' }}">
                                    @if($product->is_processed)
                                        <span class="processed-badge">
                                            <i class="fas fa-mortar-pestle me-1"></i> Olahan
                                        </span>
                                    @endif
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
                                        <p class="card-text text-primary fw-bold mb-0">Rp {{ number_format($product->selling_price, 0, ',', '.') }}</p>
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

        // Format number with thousand separator
        function formatNumber(number) {
            return number.toFixed(0).replace(/\d(?=(\d{3})+$)/g, '$&.');
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
                return 0; // Jika pajak dinonaktifkan, return 0
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

                const row = `
                    <tr>
                        <td class="text-truncate" style="max-width: 120px;">
                            ${item.name} ${isProcessed}
                        </td>
                        <td>
                            <input type="number" class="form-control form-control-sm item-quantity" data-index="${index}" value="${item.quantity}" min="1" step="1">
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

            // Menyembunyikan semua produk terlebih dahulu
            $('.product-item').hide();
            // Hanya menampilkan produk dengan kategori yang dipilih
            $('.product-item[data-category="' + category + '"]').show();
        });

        // Set kategori pertama sebagai default
        setTimeout(function() {
            // Jika ada kategori, pilih yang pertama
            if ($('.category-pill').length > 0) {
                $('.category-pill:eq(0)').addClass('active').trigger('click');
            } else {
                // Jika tidak ada kategori sama sekali, tampilkan semua produk
                $('.product-item').show();
            }
        }, 100);

        // Search products
        $('#search-product').on('input', function() {
            const searchTerm = $(this).val().toLowerCase();

            if (searchTerm.length > 0) {
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
                $('.product-item').hide();
                $('.product-item[data-category="' + activeCategory + '"]').show();
            }
        });

        // Handle adding product to cart - Direvisi untuk menghapus popup ingredients
        function addProductToCart(product) {
            const productId = product.data('id');
            const productName = product.data('name');
            const productPrice = parseFloat(product.data('price'));
            const unitId = product.data('unit-id');
            const unitName = product.data('unit-name');
            const isProcessed = (product.data('is-processed') === true || product.data('is-processed') === 'true' ||
                               product.data('is-processed') === '1' || product.data('is-processed') === 1) ? 1 : 0;
            const currentStock = parseFloat(product.data('stock'));

            // Jika stok 0 atau kartu memiliki class out-of-stock, tampilkan pesan error dan hentikan proses
            if (currentStock <= 0 || product.hasClass('out-of-stock')) {
                Swal.fire({
                    icon: 'error',
                    title: 'Stok Habis',
                    text: `Produk "${productName}" tidak tersedia (stok: 0)`
                });
                return;
            }

            // Check if product already in cart
            const existingItemIndex = cartItems.findIndex(item => item.product_id === productId);

            // Jika produk sudah ada di keranjang, cek apakah jumlah yang akan ditambahkan melebihi stok
            if (existingItemIndex > -1) {
                const newQuantity = cartItems[existingItemIndex].quantity + 1;

                // Jika jumlah baru melebihi stok, tampilkan pesan error
                if (newQuantity > currentStock) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Stok Tidak Cukup',
                        text: `Stok produk "${productName}" tidak mencukupi (tersedia: ${currentStock})`
                    });
                    return;
                }

                // Increment quantity
                cartItems[existingItemIndex].quantity = newQuantity;
            } else {
                // Add new item
                cartItems.push({
                    product_id: productId,
                    name: productName,
                    price: productPrice,
                    quantity: 1,
                    unit_id: unitId,
                    unit_name: unitName,
                    is_processed: isProcessed,
                    stock: currentStock
                });
            }

            updateCartTable();
        }

        // Add product to cart when clicked
        $(document).on('click', '.product-card', function() {
            // Hanya proses jika produk bukan out-of-stock
            if (!$(this).hasClass('out-of-stock')) {
                addProductToCart($(this));
            } else {
                const productName = $(this).data('name');
                Swal.fire({
                    icon: 'error',
                    title: 'Stok Habis',
                    text: `Produk "${productName}" tidak tersedia (stok: 0)`
                });
            }
        });

        // Update item quantity
        $(document).on('change', '.item-quantity', function() {
            const index = $(this).data('index');
            const newQuantity = parseInt($(this).val()) || 1;
            const currentStock = cartItems[index].stock || 0;

            if (newQuantity < 1) {
                $(this).val(1);
                cartItems[index].quantity = 1;
            } else if (newQuantity > currentStock) {
                // Jika jumlah yang diinput melebihi stok, tampilkan pesan error
                Swal.fire({
                    icon: 'warning',
                    title: 'Stok Tidak Cukup',
                    text: `Stok produk "${cartItems[index].name}" tidak mencukupi (tersedia: ${currentStock})`
                });

                // Reset jumlah ke nilai maksimum yang diperbolehkan (stok yang tersedia)
                $(this).val(currentStock);
                cartItems[index].quantity = currentStock;
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
            const customerName = $('#customer-name').val();

            if (paidAmount < total && paymentType === 'tunai') {
                Swal.fire({
                    icon: 'error',
                    title: 'Pembayaran Kurang',
                    text: 'Jumlah pembayaran tidak boleh kurang dari total untuk pembayaran tunai'
                });
                return;
            }

            // Pastikan nilai is_processed adalah 1 atau 0
            const itemsForSubmission = cartItems.map(item => ({
                product_id: item.product_id,
                unit_id: item.unit_id,
                quantity: item.quantity,
                price: item.price,
                discount: 0, // Diskon item individual tidak diimplementasikan di UI ini
                subtotal: item.quantity * item.price,
                is_processed: (item.is_processed === true || item.is_processed === 'true' ||
                              item.is_processed === '1' || item.is_processed === 1) ? 1 : 0
            }));

            const data = {
                store_id: {{ Auth::user()->store_id ?? 1 }}, // Default ke 1 jika store_id tidak diatur
                payment_type: paymentType,
                customer_name: customerName,
                discount: discount,
                tax_enabled: taxEnabled ? 1 : 0,
                tax: tax,
                total_amount: total,
                total_payment: paidAmount,
                change: paidAmount - total > 0 ? paidAmount - total : 0,
                items: itemsForSubmission
            };

            // Tampilkan loading indicator
            Swal.fire({
                title: 'Sedang Memproses',
                text: 'Mohon tunggu, transaksi sedang diproses...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Submit data ke server
            $.ajax({
                url: "{{ route('pos.process') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    sale: data
                },
                success: function(response) {
                    Swal.close(); // Tutup loading indicator

                    if (response.success) {
                        // Tampilkan modal sukses
                        $('#success-invoice').text(response.invoice_number);
                        $('#success-total').text("Rp " + formatNumber(total));
                        $('#success-change').text("Rp " + formatNumber(paidAmount - total > 0 ? paidAmount - total : 0));

                        // Simpan receipt URL untuk digunakan nanti
                        window.receiptUrl = response.receipt_url;

                        // Tampilkan modal sukses
                        $('#payment-success-modal').modal('show');

                        // Clear keranjang untuk penjualan baru
                        cartItems = [];
                        updateCartTable();
                        $('#discount').val(0);
                        $('#paid-amount').val(0);
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
                    Swal.close(); // Tutup loading indicator

                    let errorMessage = 'Terjadi kesalahan saat memproses penjualan.';

                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }

                    // Tampilkan error spesifik validasi jika tersedia
                    if (xhr.responseJSON && xhr.responseJSON.errors) {
                        const validationErrors = xhr.responseJSON.errors;
                        const errorMessages = [];
                        for (const field in validationErrors) {
                            errorMessages.push(validationErrors[field][0]);
                        }

                        if (errorMessages.length > 0) {
                            errorMessage = errorMessages.join("\n");
                        }
                    }

                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: errorMessage,
                        customClass: {
                            content: 'text-start'
                        },
                        confirmButtonText: 'Coba Lagi'
                    });
                }
            });
        });

        // Handle print receipt button
        $(document).on('click', '#print-receipt-btn', function() {
            if (window.receiptUrl) {
                // Deteksi apakah perangkat Android
                var isAndroid = /Android/i.test(navigator.userAgent);

                if (isAndroid) {
                    // Untuk Android, gunakan rawbt-receipt
                    let rawbtUrl = window.receiptUrl.replace('/receipt', '/rawbt-receipt');
                    window.location.href = rawbtUrl;
                } else {
                    // Untuk non-Android, gunakan cara normal
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
    });
</script>
@endsection
