@extends('layouts.app')

@section('title', 'Edit Satuan')

@section('content')
<div class="container-fluid">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-0 text-dark fw-bold">
                <i class="fas fa-edit me-2 text-primary"></i> Edit Satuan
            </h1>
            <p class="text-muted">Edit informasi untuk satuan: <span class="fw-medium">{{ $unit->name }}</span></p>
        </div>
        <a href="{{ route('units.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Kembali
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex align-items-center">
            <i class="fas fa-info-circle text-primary me-2"></i>
            <h6 class="m-0 fw-bold text-primary">Informasi Satuan</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('units.update', $unit) }}" method="POST">
                @csrf
                @method('PUT')
                @include('units._form', ['submitButtonText' => 'Perbarui Satuan'])
            </form>
        </div>
    </div>
</div>
@endsection