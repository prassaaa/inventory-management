<!-- Modal Konfirmasi dengan Input Ongkir -->
<div class="modal fade" id="confirmModal{{ $storeOrder->id }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('store-orders.confirm', $storeOrder->id) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Konfirmasi Pesanan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <strong>Nomor Pesanan:</strong> {{ $storeOrder->order_number }}
                    </div>
                    <div class="mb-3">
                        <strong>Toko:</strong> {{ $storeOrder->store->name }}
                    </div>
                    <div class="mb-3">
                        <strong>Total Pesanan:</strong> Rp {{ number_format($storeOrder->total_amount, 0, ',', '.') }}
                    </div>

                    <div class="mb-3">
                        <label for="shipping_cost_{{ $storeOrder->id }}" class="form-label">Ongkos Kirim <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="number"
                                   class="form-control shipping-cost-input @error('shipping_cost') is-invalid @enderror"
                                   id="shipping_cost_{{ $storeOrder->id }}"
                                   name="shipping_cost"
                                   value="{{ old('shipping_cost', 0) }}"
                                   min="0"
                                   step="1000"
                                   data-base-amount="{{ $storeOrder->total_amount }}"
                                   data-grand-total-id="grandTotal{{ $storeOrder->id }}"
                                   required>
                        </div>
                        @error('shipping_cost')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Masukkan ongkos kirim untuk pesanan ini</small>
                    </div>

                    <div class="alert alert-info">
                        <div class="row">
                            <div class="col-6">
                                <strong>Subtotal:</strong><br>
                                <span>Rp {{ number_format($storeOrder->total_amount, 0, ',', '.') }}</span>
                            </div>
                            <div class="col-6">
                                <strong>Ongkir:</strong><br>
                                <span id="ongkirDisplay{{ $storeOrder->id }}">Rp 0</span>
                            </div>
                        </div>
                        <hr class="my-2">
                        <div class="text-center">
                            <strong>Grand Total:</strong>
                            <span id="grandTotal{{ $storeOrder->id }}" class="fs-5 text-primary fw-bold">
                                Rp {{ number_format($storeOrder->total_amount, 0, ',', '.') }}
                            </span>
                        </div>
                    </div>

                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Perhatian:</strong> Setelah dikonfirmasi, ongkir tidak dapat diubah dan akan menjadi bagian dari total tagihan.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check me-1"></i> Konfirmasi Pesanan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const shippingInput = document.getElementById('shipping_cost_{{ $storeOrder->id }}');
    const grandTotalSpan = document.getElementById('grandTotal{{ $storeOrder->id }}');
    const ongkirDisplay = document.getElementById('ongkirDisplay{{ $storeOrder->id }}');
    const baseAmount = {{ $storeOrder->total_amount }};

    shippingInput.addEventListener('input', function() {
        const shippingCost = parseFloat(this.value) || 0;
        const grandTotal = baseAmount + shippingCost;

        // Update displays
        grandTotalSpan.textContent = 'Rp ' + grandTotal.toLocaleString('id-ID');
        ongkirDisplay.textContent = 'Rp ' + shippingCost.toLocaleString('id-ID');
    });
});
</script>
