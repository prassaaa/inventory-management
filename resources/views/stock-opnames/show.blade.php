@extends('layouts.app')

@section('title', 'Stock Opname Details')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Stock Opname: {{ $stockOpname->reference }}</h1>
        <div>
            @if($stockOpname->status === 'draft')
                @can('edit stock opnames')
                <form action="{{ route('stock-opnames.confirm', $stockOpname) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to confirm this stock opname?')">
                    @csrf
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check"></i> Confirm
                    </button>
                </form>
                @endcan
            @endif
            
            <a href="{{ route('stock-opnames.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Opname Information</h6>
                </div>
                <div class="card-body">
                    <table class="table">
                        <tr>
                            <th style="width: 200px;">Reference</th>
                            <td>{{ $stockOpname->reference }}</td>
                        </tr>
                        <tr>
                            <th>Date</th>
                            <td>{{ $stockOpname->date->format('d/m/Y') }}</td>
                        </tr>
                        <tr>
                            <th>Type</th>
                            <td>
                                @if($stockOpname->type === 'warehouse')
                                    <span class="badge bg-primary">Warehouse</span>
                                @else
                                    <span class="badge bg-info">Store</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Location</th>
                            <td>
                                @if($stockOpname->type === 'warehouse')
                                    Warehouse
                                @else
                                    {{ $stockOpname->store->name }}
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td>
                                @if($stockOpname->status === 'draft')
                                    <span class="badge bg-warning">Draft</span>
                                @else
                                    <span class="badge bg-success">Confirmed</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Created By</th>
                            <td>{{ $stockOpname->creator->name }}</td>
                        </tr>
                        <tr>
                            <th>Created At</th>
                            <td>{{ $stockOpname->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                        @if($stockOpname->status === 'confirmed')
                        <tr>
                            <th>Confirmed By</th>
                            <td>{{ $stockOpname->updater->name }}</td>
                        </tr>
                        <tr>
                            <th>Confirmed At</th>
                            <td>{{ $stockOpname->updated_at->format('d/m/Y H:i') }}</td>
                        </tr>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Stock Count Details</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Product</th>
                            <th>System Stock</th>
                            <th>Physical Count</th>
                            <th>Difference</th>
                            <th>Unit</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($stockOpname->stockOpnameDetails as $index => $detail)
                        <tr class="{{ $detail->difference != 0 ? ($detail->difference > 0 ? 'table-success' : 'table-danger') : '' }}">
                            <td>{{ $index + 1 }}</td>
                            <td>
                                <a href="{{ route('products.show', $detail->product) }}">
                                    {{ $detail->product->code }} - {{ $detail->product->name }}
                                </a>
                            </td>
                            <td>{{ $detail->system_stock }}</td>
                            <td>{{ $detail->physical_stock }}</td>
                            <td>
                                {{ $detail->difference }}
                                @if($detail->difference > 0)
                                    <span class="text-success"><i class="fas fa-arrow-up"></i></span>
                                @elseif($detail->difference < 0)
                                    <span class="text-danger"><i class="fas fa-arrow-down"></i></span>
                                @endif
                            </td>
                            <td>{{ $detail->unit->name }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection