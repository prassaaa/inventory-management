@extends('layouts.app')

@section('title', 'Edit Kategori')

@section('content')
<div class="container-fluid">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-0 text-dark fw-bold">
                <i class="fas fa-edit me-2 text-primary"></i> Edit Kategori
            </h1>
            <p class="text-muted">Edit informasi untuk kategori: <span class="fw-medium">{{ $category->name }}</span></p>
        </div>
        <a href="{{ route('categories.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Kembali
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex align-items-center">
            <i class="fas fa-info-circle text-primary me-2"></i>
            <h6 class="m-0 fw-bold text-primary">Informasi Kategori</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('categories.update', $category) }}" method="POST">
                @csrf
                @method('PUT')
                @include('categories._form', ['submitButtonText' => 'Perbarui Kategori'])
            </form>
        </div>
    </div>
</div>
@endsection