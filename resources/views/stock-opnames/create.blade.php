@extends('layouts.app')

@section('title', 'New Stock Opname')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">New Stock Opname</h1>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Stock Opname Information</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('stock-opnames.store') }}" method="POST" id="opname-form">
                @csrf
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label for="date">Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('date') is-invalid @enderror" id="date" name="date" value="{{ old('date', date('Y-m-d')) }}" required>
                            @error('date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label for="reference">Reference <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('reference') is-invalid @enderror" id="reference" name="reference" value="{{ old('reference', 'OPN-'.date('YmdHis')) }}" required>
                            @error('reference')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label for="type">Type <span class="text-danger">*</span></label>
                            <select class="form-control @error('type') is-invalid @enderror" id="type" name="type" required>
                                <option value="warehouse" {{ old('type') === 'warehouse' ? 'selected' : '' }}>Warehouse</option>
                                <option value="store" {{ old('type') === 'store' ? 'selected' : '' }}>Store</option>
                            </select>
                            @error('type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                
                <div id="store-selection" class="form-group mb-3 {{ old('type') === 'store' ? '' : 'd-none' }}">
                    <label for="store_id">Store <span class="text-danger">*</span></label>
                    <select class="form-control @error('store_id') is-invalid @enderror" id="store_id" name="store_id" {{ old('type') === 'store' ? 'required' : '' }}>
                        <option value="">Select Store</option>
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

                <hr>
                <h5 class="mb-3">Select Products for Stock Count</h5>
                
                <div class="mb-3">
                    <button type="button" class="btn btn-primary" id="get-products">
                        <i class="fas fa-sync"></i> Get Products
                    </button>
                </div>
                
                <div id="products-container" class="d-none">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="products-table">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>System Stock</th>
                                    <th>Unit</th>
                                    <th>Physical Count</th>
                                    <th>Difference</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Products will be added here -->
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">Save Stock Opname</button>
                        <a href="{{ route('stock-opnames.index') }}" class="btn btn-secondary">Cancel</a>
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
        // Show/hide store selection based on type
        $('#type').change(function() {
            if ($(this).val() === 'store') {
                $('#store-selection').removeClass('d-none');
                $('#store_id').prop('required', true);
            } else {
                $('#store-selection').addClass('d-none');
                $('#store_id').prop('required', false);
            }
        });
        
        // Get products for stock opname
        $('#get-products').click(function() {
            const type = $('#type').val();
            let storeId = null;
            
            if (type === 'store') {
                storeId = $('#store_id').val();
                if (!storeId) {
                    alert('Please select a store.');
                    return;
                }
            }
            
            // Show loading indicator
            $(this).html('<i class="fas fa-spinner fa-spin"></i> Loading...');
            $(this).prop('disabled', true);
            
            // Fetch products via AJAX
            $.ajax({
                url: "{{ route('stock-opnames.get-products') }}",
                type: "GET",
                data: {
                    type: type,
                    store_id: storeId
                },
                success: function(response) {
                    if (response.success) {
                        // Clear existing products
                        $('#products-table tbody').empty();
                        
                        // Add products to table
                        if (response.products.length > 0) {
                            response.products.forEach(function(product, index) {
                                const row = `
                                    <tr>
                                        <td>${product.name} (${product.code})</td>
                                        <td>${product.stock_quantity}</td>
                                        <td>${product.unit_name}</td>
                                        <td>
                                            <input type="number" class="form-control physical-count" name="physical_counts[${product.id}]" value="${product.stock_quantity}" min="0" step="0.01" required data-system="${product.stock_quantity}" data-row="${index}">
                                            <input type="hidden" name="product_ids[]" value="${product.id}">
                                            <input type="hidden" name="unit_ids[]" value="${product.unit_id}">
                                            <input type="hidden" name="system_stocks[]" value="${product.stock_quantity}">
                                        </td>
                                        <td class="difference">0</td>
                                    </tr>
                                `;
                                
                                $('#products-table tbody').append(row);
                            });
                            
                            // Show products container
                            $('#products-container').removeClass('d-none');
                        } else {
                            alert('No products found.');
                        }
                    } else {
                        alert('Error: ' + response.message);
                    }
                    
                    // Reset button
                    $('#get-products').html('<i class="fas fa-sync"></i> Get Products');
                    $('#get-products').prop('disabled', false);
                },
                error: function(xhr) {
                    let errorMessage = 'An error occurred while fetching products.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    alert('Error: ' + errorMessage);
                    
                    // Reset button
                    $('#get-products').html('<i class="fas fa-sync"></i> Get Products');
                    $('#get-products').prop('disabled', false);
                }
            });
        });
        
        // Update difference when physical count changes
        $(document).on('input', '.physical-count', function() {
            const systemStock = parseFloat($(this).data('system'));
            const physicalCount = parseFloat($(this).val()) || 0;
            const difference = physicalCount - systemStock;
            
            $(this).closest('tr').find('.difference').text(difference.toFixed(2));
        });
        
        // Validate form before submission
        $('#opname-form').on('submit', function(e) {
            const rowCount = $('#products-table tbody tr').length;
            
            if (rowCount === 0) {
                e.preventDefault();
                alert('Please get products for stock opname.');
                return false;
            }
            
            return true;
        });
    });
</script>
@endsection