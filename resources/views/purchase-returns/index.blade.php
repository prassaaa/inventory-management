@extends('layouts.app')

@section('title', 'Purchase Returns')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Purchase Returns</h1>
        @can('create purchase returns')
        <a href="{{ route('purchase-returns.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> New Return
        </a>
        @endcan
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">All Purchase Returns</h6>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif

            <div class="table-responsive">
                <table class="table table-bordered datatable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Purchase Invoice</th>
                            <th>Supplier</th>
                            <th>Total</th>
                            <th>Note</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($purchaseReturns as $return)
                        <tr>
                            <td>{{ $return->date->format('d/m/Y') }}</td>
                            <td>
                                <a href="{{ route('purchases.show', $return->purchase) }}">
                                    {{ $return->purchase->invoice_number }}
                                </a>
                            </td>
                            <td>{{ $return->purchase->supplier->name }}</td>
                            <td>{{ number_format($return->total_amount, 0, ',', '.') }}</td>
                            <td>{{ Str::limit($return->note, 30) }}</td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('purchase-returns.show', $return) }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        $('.datatable').DataTable({
            order: [[0, 'desc']]
        });
    });
</script>
@endsection