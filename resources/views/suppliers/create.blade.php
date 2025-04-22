@extends('layouts.app')

@section('title', 'Tambah Pemasok')

@section('content')
<div class="container-fluid">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-0 text-dark fw-bold">
                <i class="fas fa-plus-circle me-2 text-primary"></i> Tambah Pemasok
            </h1>
            <p class="text-muted">Tambahkan pemasok baru untuk sumber persediaan produk</p>
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
            <form action="{{ route('suppliers.store') }}" method="POST">
                @csrf
                @include('suppliers._form', ['submitButtonText' => 'Simpan Pemasok'])
            </form>
        </div>
    </div>
</div>
@endsection