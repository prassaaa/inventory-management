@extends('layouts.app')

@section('title', 'Detail Penyesuaian Stok')

@section('content')
<div class="container-fluid">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-0 text-dark fw-bold">
                <i class="fas fa-clipboard-check me-2 text-primary"></i> Detail Penyesuaian Stok
            </h1>
            <p class="text-muted">Informasi lengkap untuk penyesuaian: <span class="fw-medium">{{ $stockAdjustment->reference }}</span></p>
        </div>
        <a href="{{ route('stock-adjustments.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Kembali
        </a>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex align-items-center">
                    <i class="fas fa-info-circle text-primary me-2"></i>
                    <h6 class="m-0 fw-bold text-primary">Informasi Penyesuaian</h6>
                </div>
                <div class="card-body">
                    <table class="table">
                        <tr>
                            <th class="border-0 ps-0" style="width: 200px;">Referensi</th>
                            <td class="border-0 fw-medium">{{ $stockAdjustment->reference }}</td>
                        </tr>
                        <tr>
                            <th class="border-0 ps-0">Tanggal</th>
                            <td class="border-0">{{ $stockAdjustment->date->format('d/m/Y') }}</td>
                        </tr>
                        <tr>
                            <th class="border-0 ps-0">Tipe</th>
                            <td class="border-0">
                                @if($stockAdjustment->type === 'warehouse')
                                    <span class="badge bg-primary-light text-primary rounded-pill px-2">Gudang</span>
                                @else
                                    <span class="badge bg-info-light text-info rounded-pill px-2">Toko</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th class="border-0 ps-0">Lokasi</th>
                            <td class="border-0">
                                @if($stockAdjustment->type === 'warehouse')
                                    Gudang
                                @else
                                    {{ $stockAdjustment->store->name }}
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th class="border-0 ps-0">Dibuat Oleh</th>
                            <td class="border-0">{{ $stockAdjustment->creator->name }}</td>
                        </tr>
                        <tr>
                            <th class="border-0 ps-0">Dibuat Pada</th>
                            <td class="border-0">{{ $stockAdjustment->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex align-items-center">
                    <i class="fas fa-chart-pie text-primary me-2"></i>
                    <h6 class="m-0 fw-bold text-primary">Ringkasan</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="card border-0 bg-primary">
                                <div class="card-body py-3">
                                    <div class="d-flex align-items-center">
                                        <div class="icon-circle bg-white me-3">
                                            <i class="fas fa-plus fa-lg text-primary"></i>
                                        </div>
                                        <div class="text-white">
                                            <h6 class="mb-0 small">Penambahan</h6>
                                            <h5 class="mb-0 fw-bold">{{ $stockAdjustment->stockAdjustmentDetails->where('type', 'addition')->count() }}</h5>
                                            <small>item</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="card border-0 bg-danger">
                                <div class="card-body py-3">
                                    <div class="d-flex align-items-center">
                                        <div class="icon-circle bg-white me-3">
                                            <i class="fas fa-minus fa-lg text-danger"></i>
                                        </div>
                                        <div class="text-white">
                                            <h6 class="mb-0 small">Pengurangan</h6>
                                            <h5 class="mb-0 fw-bold">{{ $stockAdjustment->stockAdjustmentDetails->where('type', 'reduction')->count() }}</h5>
                                            <small>item</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex align-items-center">
            <i class="fas fa-list-alt text-primary me-2"></i>
            <h6 class="m-0 fw-bold text-primary">Detail Penyesuaian</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" style="width: 50px;">#</th>
                            <th>Produk</th>
                            <th>Tipe Penyesuaian</th>
                            <th>Jumlah</th>
                            <th>Satuan</th>
                            <th>Alasan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($stockAdjustment->stockAdjustmentDetails as $index => $detail)
                        <tr>
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td>
                                <a href="{{ route('products.show', $detail->product) }}" class="text-primary">
                                    <strong>{{ $detail->product->code }}</strong> - {{ $detail->product->name }}
                                </a>
                            </td>
                            <td>
                                @if($detail->type === 'addition')
                                    <span class="badge bg-success-light text-success rounded-pill px-2">Penambahan</span>
                                @else
                                    <span class="badge bg-danger-light text-danger rounded-pill px-2">Pengurangan</span>
                                @endif
                            </td>
                            <td>{{ $detail->quantity }}</td>
                            <td>{{ $detail->unit->name }}</td>
                            <td>{{ $detail->reason ?: '-' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection