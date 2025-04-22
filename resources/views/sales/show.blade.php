@extends('layouts.app')

@section('title', 'Detail Penjualan')

@section('content')
<div class="container-fluid">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-0 text-dark fw-bold">
                <i class="fas fa-receipt me-2 text-primary"></i> Detail Penjualan
            </h1>
            <p class="text-muted">Informasi lengkap untuk penjualan: <span class="fw-medium">{{ $sale->invoice_number }}</span></p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('sales.receipt', $sale) }}" class="btn btn-outline-secondary" target="_blank">
                <i class="fas fa-print me-1"></i> Cetak Struk
            </a>
            <a href="{{ route('sales.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Kembali
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex align-items-center">
                    <i class="fas fa-info-circle text-primary me-2"></i>
                    <h6 class="m-0 fw-bold text-primary">Informasi Penjualan</h6>
                </div>
                <div class="card-body">
                    <table class="table">
                        <tr>
                            <th class="border-0 ps-0" style="width: 200px;">Nomor Faktur</th>
                            <td class="border-0 fw-medium">{{ $sale->invoice_number }}</td>
                        </tr>
                        <tr>
                            <th class="border-0 ps-0">Tanggal</th>
                            <td class="border-0">{{ $sale->date->format('d/m/Y') }}</td>
                        </tr>
                        <tr>
                            <th class="border-0 ps-0">Toko</th>
                            <td class="border-0">{{ $sale->store->name }}</td>
                        </tr>
                        <tr>
                            <th class="border-0 ps-0">Pelanggan</th>
                            <td class="border-0">{{ $sale->customer_name ?? 'Pelanggan Umum' }}</td>
                        </tr>
                        <tr>
                            <th class="border-0 ps-0">Jenis Pembayaran</th>
                            <td class="border-0">
                                @if($sale->payment_type === 'tunai')
                                    <span class="badge bg-success-light text-success rounded-pill px-2">Tunai</span>
                                @elseif($sale->payment_type === 'tempo')
                                    <span class="badge bg-warning-light text-warning rounded-pill px-2">Tempo</span>
                                @elseif($sale->payment_type === 'kartu')
                                    <span class="badge bg-info-light text-info rounded-pill px-2">Kartu</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th class="border-0 ps-0">Status</th>
                            <td class="border-0">
                                @if($sale->status === 'paid')
                                    <span class="badge bg-success-light text-success rounded-pill px-2">Lunas</span>
                                @else
                                    <span class="badge bg-warning-light text-warning rounded-pill px-2">Tertunda</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th class="border-0 ps-0">Dibuat Oleh</th>
                            <td class="border-0">{{ $sale->creator->name }}</td>
                        </tr>
                        <tr>
                            <th class="border-0 ps-0">Dibuat Pada</th>
                            <td class="border-0">{{ $sale->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex align-items-center">
                    <i class="fas fa-money-bill text-primary me-2"></i>
                    <h6 class="m-0 fw-bold text-primary">Detail Pembayaran</h6>
                </div>
                <div class="card-body">
                    <table class="table">
                        <tr>
                            <th class="border-0 ps-0" style="width: 200px;">Subtotal</th>
                            <td class="border-0 text-end">Rp {{ number_format($sale->total_amount + $sale->discount - $sale->tax, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <th class="border-0 ps-0">Diskon</th>
                            <td class="border-0 text-end">Rp {{ number_format($sale->discount, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <th class="border-0 ps-0">Pajak</th>
                            <td class="border-0 text-end">Rp {{ number_format($sale->tax, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <th class="border-0 ps-0 text-primary fw-bold">Total</th>
                            <td class="border-0 text-end text-primary fw-bold">Rp {{ number_format($sale->total_amount, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <th class="border-0 ps-0">Dibayar</th>
                            <td class="border-0 text-end">Rp {{ number_format($sale->total_payment, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <th class="border-0 ps-0">Kembalian</th>
                            <td class="border-0 text-end">Rp {{ number_format($sale->change, 0, ',', '.') }}</td>
                        </tr>
                    </table>
                    
                    <div class="d-flex justify-content-center mt-4">
                        <div class="card border-0 bg-success" style="max-width: 320px;">
                            <div class="card-body py-3">
                                <div class="d-flex align-items-center justify-content-center">
                                    <div class="icon-circle bg-white me-3">
                                        <i class="fas fa-receipt fa-lg text-success"></i>
                                    </div>
                                    <div class="text-white">
                                        <h6 class="mb-0 small">Total Pembayaran</h6>
                                        <h4 class="mb-0 fw-bold">Rp {{ number_format($sale->total_amount, 0, ',', '.') }}</h4>
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
            <i class="fas fa-shopping-basket text-primary me-2"></i>
            <h6 class="m-0 fw-bold text-primary">Daftar Produk</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" style="width: 50px;">#</th>
                            <th>Produk</th>
                            <th>Jumlah</th>
                            <th>Satuan</th>
                            <th>Harga</th>
                            <th>Diskon</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sale->saleDetails as $index => $detail)
                        <tr>
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td>
                                <a href="{{ route('products.show', $detail->product) }}" class="text-primary">
                                    <strong>{{ $detail->product->code }}</strong> - {{ $detail->product->name }}
                                </a>
                            </td>
                            <td>{{ $detail->quantity }}</td>
                            <td>{{ $detail->unit->name }}</td>
                            <td>Rp {{ number_format($detail->price, 0, ',', '.') }}</td>
                            <td>Rp {{ number_format($detail->discount, 0, ',', '.') }}</td>
                            <td>Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <th colspan="6" class="text-end">Total:</th>
                            <th class="text-primary fw-bold">Rp {{ number_format($sale->saleDetails->sum('subtotal'), 0, ',', '.') }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection