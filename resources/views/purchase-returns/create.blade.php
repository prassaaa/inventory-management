@extends('layouts.app')

@section('title', 'Create Purchase Return')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Create Purchase Return</h1>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Select Purchase</h6>
        </div>
        <div class="card-body">
            @if(session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif

            <div class="form-group mb-4">
                <label for="purchase_id">Select Purchase to Return</label>
                <select class="form-control select2" id="purchase_id">
                    <option value="">Select Purchase</option>
                    @foreach($purchases as $purchase)
                        <option value="{{ $purchase->id }}">
                            {{ $purchase->invoice_number }} - {{ $purchase->date->format('d/m/Y') }} - {{ $purchase->supplier->name }} ({{ number_format($purchase->total_amount, 0, ',', '.') }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="text-center">
                <button type="button" id="continue-btn" class="btn btn-primary">Continue</button>
                <a href="{{ route('purchase-returns.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Initialize Select2
        $('.select2').select2();
        
        // Handle continue button click
        $('#continue-btn').click(function() {
            const purchaseId = $('#purchase_id').val();
            
            if (!purchaseId) {
                alert('Please select a purchase');
                return;
            }
            
            // Redirect to create-from-purchase route
            window.location.href = "{{ url('purchase-returns/create') }}/" + purchaseId;
        });
    });
</script>
@endsection