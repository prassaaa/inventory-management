@extends('layouts.app')

@section('title', 'Tambah Satuan')

@section('content')
<div class="container-fluid">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-0 text-dark fw-bold">
                <i class="fas fa-plus-circle me-2 text-primary"></i> Tambah Satuan
            </h1>
            <p class="text-muted">Tambahkan satuan baru untuk pengukuran produk</p>
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
            <form action="{{ route('units.store') }}" method="POST">
                @csrf
                @include('units._form', ['submitButtonText' => 'Simpan Satuan'])
            </form>
        </div>
    </div>
</div>
@endsection