@extends('layouts.app')

@section('title', 'Edit Pemasok')

@section('content')
<div class="container-fluid">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-0 text-dark fw-bold">
                <i class="fas fa-edit me-2 text-primary"></i> Edit Pemasok
            </h1>
            <p class="text-muted">Edit informasi untuk pemasok: <span class="fw-medium">{{ $supplier->name }}</span></p>
        </div>
        <a href="{{ route('suppliers.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Kembali
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex align-items-center">
            <i class="fas fa-info-circle text-primary me-2"></i>
            <h6 class="m-0 fw-bold text-primary">Informasi Pemasok</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('suppliers.update', $supplier) }}" method="POST">
                @csrf
                @method('PUT')
                @include('suppliers._form', ['submitButtonText' => 'Perbarui Pemasok'])
            </form>
        </div>
    </div>
</div>
@endsection