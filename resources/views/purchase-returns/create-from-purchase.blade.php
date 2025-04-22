@extends('layouts.app')

@section('title', 'Return Purchase')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Return Purchase: {{ $purchase->invoice_number }}</h1>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Purchase Information</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table">
                        <tr>
                            <th style="width: 200px;">Invoice Number</th>
                            <td>{{ $purchase->invoice_number }}</td>
                        </tr>
                        <tr>
                            <th>Date</th>
                            <td>{{ $purchase->date->format('d/m/Y') }}</td>
                        </tr>
                        <tr>
                            <th>Supplier</th>
                            <td>{{ $purchase->supplier->name }}</td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table">
                        <tr>
                            <th style="width: 200px;">Payment Type</th>
                            <td>{{ ucfirst($purchase->payment_type) }}</td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td>{{ ucfirst($purchase->status) }}</td>
                        </tr>
                        <tr>
                            <th>Total Amount</th>
                            <td>{{ number_format($purchase->total_amount, 0, ',', '.') }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Return Details</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('purchase-returns.store') }}" method="POST" id="return-form">
                @csrf
                <input type="hidden" name="purchase_id" value="{{ $purchase->id }}">
                
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="date">Return Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('date') is-invalid @enderror" id="date" name="date" value="{{ old('date', date('Y-m-d')) }}" required>
                            @error('date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="note">Note <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('note') is-invalid @enderror" id="note" name="note" rows="3" required>{{ old('note') }}</textarea>
                            @error('note')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered" id="products-table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Unit</th>
                                <th>Purchase Quantity</th>
                                <th>Returned Quantity</th>
                                <th>Available Quantity</th>
                                <th>Return Quantity</th>
                                <th>Price</th>
                                <th>Subtotal</th>
                                <th>Reason</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($purchase->purchaseDetails as $index => $detail)
                                @php
                                    $returnedQuantity = $detail->returnedQuantity();
                                    $availableQuantity = $detail->quantity - $returnedQuantity;
                                @endphp
                                <tr>
                                    <td>{{ $detail->product->code }} - {{ $detail->product->name }}</td>
                                    <td>{{ $detail->unit->name }}</td>
                                    <td>{{ $detail->quantity }}</td>
                                    <td>{{ $returnedQuantity }}</td>
                                    <td class="available-quantity">{{ $availableQuantity }}</td>
                                    <td>
                                        <input type="number" class="form-control return-quantity" 
                                               name="return_quantities[{{ $detail->id }}]" 
                                               value="0" min="0" max="{{ $availableQuantity }}" 
                                               step="0.01" 
                                               data-price="{{ $detail->price }}" 
                                               data-index="{{ $index }}">
                                    </td>
                                    <td>{{ number_format($detail->price, 0, ',', '.') }}</td>
                                    <td class="subtotal">0</td>
                                    <td>
                                        <input type="text" class="form-control" 
                                               name="reasons[{{ $detail->id }}]" 
                                               placeholder="Reason for return">
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="7" class="text-right">Total Return Amount:</th>
                                <th id="total-amount">0</th>
                                <th></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                
                <input type="hidden" name="total_amount" id="total-amount-input" value="0">
                
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">Submit Return</button>
                    <a href="{{ route('purchases.show', $purchase) }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Update subtotal when return quantity changes
        $('.return-quantity').on('input', function() {
            const index = $(this).data('index');
            const price = $(this).data('price');
            const availableQuantity = parseFloat($(this).closest('tr').find('.available-quantity').text());
            let quantity = parseFloat($(this).val()) || 0;
            
            // Make sure quantity doesn't exceed available quantity
            if (quantity > availableQuantity) {
                quantity = availableQuantity;
                $(this).val(availableQuantity);
            }
            
            const subtotal = quantity * price;
            $(this).closest('tr').find('.subtotal').text(formatNumber(subtotal));
            
            updateTotal();
        });
        
        // Update total
        function updateTotal() {
            let total = 0;
            $('.subtotal').each(function() {
                total += parseFloat($(this).text().replace(/\./g, '').replace(',', '.')) || 0;
            });
            
            $('#total-amount').text(formatNumber(total));
            $('#total-amount-input').val(total);
        }
        
        // Format number with thousand separator
        function formatNumber(number) {
            return number.toFixed(0).replace(/\d(?=(\d{3})+$)/g, '$&.');
        }
        
        // Validate form before submission
        $('#return-form').on('submit', function(e) {
            const totalAmount = parseFloat($('#total-amount-input').val());
            
            if (totalAmount <= 0) {
                e.preventDefault();
                alert('Please return at least one item.');
                return false;
            }
            
            return true;
        });
    });
</script>
@endsection