@extends('layouts.app')

@section('title', 'Stock Opnames')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Stock Opnames</h1>
        @can('create stock opnames')
        <a href="{{ route('stock-opnames.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> New Stock Opname
        </a>
        @endcan
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">All Stock Opnames</h6>
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
                            <th>Reference</th>
                            <th>Type</th>
                            <th>Location</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($stockOpnames as $opname)
                        <tr>
                            <td>{{ $opname->date->format('d/m/Y') }}</td>
                            <td>{{ $opname->reference }}</td>
                            <td>
                                @if($opname->type === 'warehouse')
                                    <span class="badge bg-primary">Warehouse</span>
                                @else
                                    <span class="badge bg-info">Store</span>
                                @endif
                            </td>
                            <td>
                                @if($opname->type === 'warehouse')
                                    Warehouse
                                @else
                                    {{ $opname->store->name }}
                                @endif
                            </td>
                            <td>
                                @if($opname->status === 'draft')
                                    <span class="badge bg-warning">Draft</span>
                                @else
                                    <span class="badge bg-success">Confirmed</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('stock-opnames.show', $opname) }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    
                                    @if($opname->status === 'draft')
                                        @can('edit stock opnames')
                                        <form action="{{ route('stock-opnames.confirm', $opname) }}" method="POST" onsubmit="return confirm('Are you sure you want to confirm this stock opname?')">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success">
                                                <i class="fas fa-check"></i> Confirm
                                            </button>
                                        </form>
                                        @endcan
                                    @endif
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