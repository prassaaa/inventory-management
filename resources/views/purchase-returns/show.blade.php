@extends('layouts.app')

@section('title', 'Purchase Return Details')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Purchase Return Details</h1>
        <div>
            <a href="{{ route('purchase-returns.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Return Information</h6>
                </div>
                <div class="card-body">
                    <table class="table">
                        <tr>
                            <th style="width: 200px;">Return Date</th>
                            <td>{{ $purchaseReturn->date->format('d/m/Y') }}</td>
                        </tr>
                        <tr>
                            <th>Purchase Invoice</th>
                            <td>
                                <a href="{{ route('purchases.show', $purchaseReturn->purchase) }}">
                                    {{ $purchaseReturn->purchase->invoice_number }}
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <th>Supplier</th>
                            <td>
                                <a href="{{ route('suppliers.show', $purchaseReturn->purchase->supplier) }}">
                                    {{ $purchaseReturn->purchase->supplier->name }}
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <th>Total Amount</th>
                            <td class="font-weight-bold">{{ number_format($purchaseReturn->total_amount, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <th>Note</th>
                            <td>{{ $purchaseReturn->note }}</td>
                        </tr>
                        <tr>
                            <th>Created By</th>
                            <td>{{ $purchaseReturn->creator->name }}</td>
                        </tr>
                        <tr>
                            <th>Created At</th>
                            <td>{{ $purchaseReturn->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Purchase Information</h6>
                </div>
                <div class="card-body">
                    <table class="table">
                        <tr>
                            <th style="width: 200px;">Invoice Number</th>
                            <td>{{ $purchaseReturn->purchase->invoice_number }}</td>
                        </tr>
                        <tr>
                            <th>Date</th>
                            <td>{{ $purchaseReturn->purchase->date->format('d/m/Y') }}</td>
                        </tr>
                        <tr>
                            <th>Payment Type</th>
                            <td>
                                <span class="badge {{ $purchaseReturn->purchase->payment_type === 'tunai' ? 'bg-success' : 'bg-warning' }}">
                                    {{ ucfirst($purchaseReturn->purchase->payment_type) }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td>
                                <span class="badge 
                                    {{ $purchaseReturn->purchase->status === 'pending' ? 'bg-warning' : '' }}
                                    {{ $purchaseReturn->purchase->status === 'complete' ? 'bg-success' : '' }}
                                    {{ $purchaseReturn->purchase->status === 'partial' ? 'bg-info' : '' }}
                                ">
                                    {{ ucfirst($purchaseReturn->purchase->status) }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Total Amount</th>
                            <td>{{ number_format($purchaseReturn->purchase->total_amount, 0, ',', '.') }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Returned Items</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Product</th>
                            <th>Quantity</th>
                            <th>Unit</th>
                            <th>Price</th>
                            <th>Subtotal</th>
                            <th>Reason</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($purchaseReturn->purchaseReturnDetails as $index => $detail)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>
                                <a href="{{ route('products.show', $detail->product) }}">
                                    {{ $detail->product->code }} - {{ $detail->product->name }}
                                </a>
                            </td>
                            <td>{{ $detail->quantity }}</td>
                            <td>{{ $detail->unit->name }}</td>
                            <td>{{ number_format($detail->price, 0, ',', '.') }}</td>
                            <td>{{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                            <td>{{ $detail->reason }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="5" class="text-right">Total:</th>
                            <th>{{ number_format($purchaseReturn->total_amount, 0, ',', '.') }}</th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection