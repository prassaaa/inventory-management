@extends('layouts.app')

@section('title', 'Detail Pemasok')

@section('content')
<div class="container-fluid">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-0 text-dark fw-bold">
                <i class="fas fa-truck me-2 text-primary"></i> Detail Pemasok
            </h1>
            <p class="text-muted">Informasi lengkap untuk pemasok: <span class="fw-medium">{{ $supplier->name }}</span></p>
        </div>
        <div class="d-flex gap-2">
            @can('edit suppliers')
            <a href="{{ route('suppliers.edit', $supplier) }}" class="btn btn-primary">
                <i class="fas fa-edit me-1"></i> Edit
            </a>
            @endcan
            <a href="{{ route('suppliers.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Kembali
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex align-items-center">
                    <i class="fas fa-info-circle text-primary me-2"></i>
                    <h6 class="m-0 fw-bold text-primary">Informasi Pemasok</h6>
                </div>
                <div class="card-body">
                    <table class="table">
                        <tr>
                            <th class="border-0 ps-0" style="width: 30%">ID</th>
                            <td class="border-0">{{ $supplier->id }}</td>
                        </tr>
                        <tr>
                            <th class="border-0 ps-0">Nama</th>
                            <td class="border-0">{{ $supplier->name }}</td>
                        </tr>
                        <tr>
                            <th class="border-0 ps-0">Email</th>
                            <td class="border-0">{{ $supplier->email ?: 'Tidak ada' }}</td>
                        </tr>
                        <tr>
                            <th class="border-0 ps-0">Telepon</th>
                            <td class="border-0">{{ $supplier->phone ?: 'Tidak ada' }}</td>
                        </tr>
                        <tr>
                            <th class="border-0 ps-0">Alamat</th>
                            <td class="border-0">{{ $supplier->address ?: 'Tidak ada' }}</td>
                        </tr>
                        <tr>
                            <th class="border-0 ps-0">Jenis Pembayaran</th>
                            <td class="border-0">
                                @if($supplier->payment_term === 'tunai')
                                    <span class="badge bg-success-light text-success rounded-pill px-2">Tunai</span>
                                @else
                                    <span class="badge bg-warning-light text-warning rounded-pill px-2">Tempo</span>
                                @endif
                            </td>
                        </tr>
                        @if($supplier->payment_term === 'tempo')
                        <tr>
                            <th class="border-0 ps-0">Limit Kredit</th>
                            <td class="border-0">Rp {{ number_format($supplier->credit_limit, 0, ',', '.') }}</td>
                        </tr>
                        @endif
                        <tr>
                            <th class="border-0 ps-0">Status</th>
                            <td class="border-0">
                                <span class="badge {{ $supplier->is_active ? 'bg-success-light text-success' : 'bg-danger-light text-danger' }} rounded-pill px-2">
                                    {{ $supplier->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th class="border-0 ps-0">Dibuat Pada</th>
                            <td class="border-0">{{ $supplier->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                        <tr>
                            <th class="border-0 ps-0">Diperbarui Pada</th>
                            <td class="border-0">{{ $supplier->updated_at->format('d/m/Y H:i') }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex align-items-center">
                    <i class="fas fa-history text-primary me-2"></i>
                    <h6 class="m-0 fw-bold text-primary">Riwayat Pembelian</h6>
                </div>
                <div class="card-body">
                    @if($supplier->purchases->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>No. Faktur</th>
                                        <th>Tanggal</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($supplier->purchases->sortByDesc('date')->take(5) as $purchase)
                                    <tr>
                                        <td>
                                            <a href="{{ route('purchases.show', $purchase) }}" class="text-primary">{{ $purchase->invoice_number }}</a>
                                        </td>
                                        <td>{{ $purchase->date->format('d/m/Y') }}</td>
                                        <td>Rp {{ number_format($purchase->total_amount, 0, ',', '.') }}</td>
                                        <td>
                                            @if($purchase->status === 'complete')
                                                <span class="badge bg-success-light text-success">Selesai</span>
                                            @elseif($purchase->status === 'pending')
                                                <span class="badge bg-warning-light text-warning">Tertunda</span>
                                            @elseif($purchase->status === 'partial')
                                                <span class="badge bg-info-light text-info">Sebagian</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @if($supplier->purchases->count() > 5)
                            <div class="text-center mt-3">
                                <a href="{{ route('purchases.index', ['supplier_id' => $supplier->id]) }}" class="btn btn-sm btn-primary">
                                    <i class="fas fa-list me-1"></i> Lihat Semua Pembelian
                                </a>
                            </div>
                        @endif
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-shopping-basket fa-4x text-muted mb-3"></i>
                            <p class="text-muted">Belum ada riwayat pembelian dari pemasok ini.</p>
                        </div>
                    @endif
                </div>
            </div>

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
                                            <i class="fas fa-shopping-cart fa-lg text-primary"></i>
                                        </div>
                                        <div class="text-white">
                                            <h6 class="mb-0 small">Total Pembelian</h6>
                                            <h5 class="mb-0 fw-bold">{{ $supplier->purchases->count() }}</h5>
                                            <small>transaksi</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="card border-0 bg-success">
                                <div class="card-body py-3">
                                    <div class="d-flex align-items-center">
                                        <div class="icon-circle bg-white me-3">
                                            <i class="fas fa-money-bill fa-lg text-success"></i>
                                        </div>
                                        <div class="text-white">
                                            <h6 class="mb-0 small">Total Nilai</h6>
                                            <h5 class="mb-0 fw-bold">Rp {{ number_format($supplier->purchases->sum('total_amount'), 0, ',', '.') }}</h5>
                                            <small>pembelian</small>
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
</div>
@endsection
