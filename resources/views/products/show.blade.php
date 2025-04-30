@extends('layouts.app')

@section('title', 'Detail Produk')

@section('content')
<div class="container-fluid">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-0 text-dark fw-bold">
                <i class="fas fa-box me-2 text-primary"></i> Detail Produk
            </h1>
            <p class="text-muted">Informasi lengkap untuk produk: <span class="fw-medium">{{ $product->name }}</span></p>
        </div>
        <div class="d-flex gap-2">
            @can('edit products')
            <a href="{{ route('products.edit', $product) }}" class="btn btn-primary">
                <i class="fas fa-edit me-1"></i> Edit
            </a>
            @endcan
            <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Kembali
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex align-items-center">
                    <i class="fas fa-info-circle text-primary me-2"></i>
                    <h6 class="m-0 fw-bold text-primary">Informasi Produk</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 text-center mb-3">
                            @if($product->image)
                                <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="img-fluid rounded" style="max-height: 150px;">
                            @else
                                <div class="bg-light rounded d-flex align-items-center justify-content-center" style="height: 150px;">
                                    <i class="fas fa-image fa-3x text-secondary"></i>
                                </div>
                            @endif
                        </div>
                        <div class="col-md-8">
                            <table class="table">
                                <tr>
                                    <th class="border-0 ps-0" style="width: 130px;">Kode</th>
                                    <td class="border-0"><span class="badge bg-primary-light text-primary">{{ $product->code }}</span></td>
                                </tr>
                                <tr>
                                    <th class="border-0 ps-0">Nama</th>
                                    <td class="border-0 fw-medium">{{ $product->name }}</td>
                                </tr>
                                <tr>
                                    <th class="border-0 ps-0">Kategori</th>
                                    <td class="border-0">{{ $product->category->name }}</td>
                                </tr>
                                <tr>
                                    <th class="border-0 ps-0">Status</th>
                                    <td class="border-0">
                                        <span class="badge {{ $product->is_active ? 'bg-success-light text-success' : 'bg-danger-light text-danger' }} rounded-pill px-2">
                                            {{ $product->is_active ? 'Aktif' : 'Nonaktif' }}
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <div class="mt-3">
                        <h6 class="fw-bold text-dark">Deskripsi</h6>
                        <p class="text-muted">{{ $product->description ?: 'Tidak ada deskripsi tersedia.' }}</p>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <h6 class="fw-bold text-dark">Sumber Produk</h6>
                                <p class="mb-0">
                                    <span class="badge bg-{{ $product->store_source == 'pusat' ? 'primary' : 'info' }}-light text-{{ $product->store_source == 'pusat' ? 'primary' : 'info' }}">
                                        {{ $product->store_source == 'pusat' ? 'Pusat' : 'Semua Store' }}
                                    </span>
                                </p>
                            </div>
                        </div>

                        @if($product->store_source == 'store' && $product->is_processed)
                        <div class="col-md-6">
                            <div class="mb-3">
                                <h6 class="fw-bold text-dark">Jenis Produk</h6>
                                <p class="mb-0">
                                    <span class="badge bg-warning-light text-warning">
                                        Produk Olahan
                                    </span>
                                </p>
                            </div>
                        </div>
                        @endif
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-6">
                            <div class="card bg-primary text-white mb-3">
                                <div class="card-body p-3">
                                    <div class="d-flex align-items-center">
                                        <div class="icon-circle bg-white me-3">
                                            <i class="fas fa-tags fa-lg text-primary"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0 small">Harga Beli</h6>
                                            <h5 class="mb-0 fw-bold">Rp {{ number_format($product->purchase_price, 0, ',', '.') }}</h5>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-success text-white mb-3">
                                <div class="card-body p-3">
                                    <div class="d-flex align-items-center">
                                        <div class="icon-circle bg-white me-3">
                                            <i class="fas fa-money-bill fa-lg text-success"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0 small">Harga Jual</h6>
                                            <h5 class="mb-0 fw-bold">Rp {{ number_format($product->selling_price, 0, ',', '.') }}</h5>
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
                    <i class="fas fa-ruler text-primary me-2"></i>
                    <h6 class="m-0 fw-bold text-primary">Satuan Produk</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Satuan</th>
                                    <th>Konversi</th>
                                    <th>Harga Beli</th>
                                    <th>Harga Jual</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <span class="d-flex align-items-center">
                                            {{ $product->baseUnit->name }}
                                            <span class="badge bg-info-light text-info ms-2">Dasar</span>
                                        </span>
                                    </td>
                                    <td>1</td>
                                    <td>Rp {{ number_format($product->purchase_price, 0, ',', '.') }}</td>
                                    <td>Rp {{ number_format($product->selling_price, 0, ',', '.') }}</td>
                                </tr>
                                @foreach($product->productUnits as $productUnit)
                                <tr>
                                    <td>{{ $productUnit->unit->name }}</td>
                                    <td>{{ $productUnit->conversion_value }}</td>
                                    <td>Rp {{ number_format($productUnit->purchase_price, 0, ',', '.') }}</td>
                                    <td>Rp {{ number_format($productUnit->selling_price, 0, ',', '.') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            @if($product->is_processed && $product->ingredients->count() > 0)
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex align-items-center">
                    <i class="fas fa-mortar-pestle text-primary me-2"></i>
                    <h6 class="m-0 fw-bold text-primary">Bahan-bahan Produk</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Bahan</th>
                                    <th>Jumlah</th>
                                    <th>Satuan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($product->ingredients as $ingredient)
                                <tr>
                                    <td>
                                        <a href="{{ route('products.show', $ingredient->id) }}" class="text-primary fw-medium">
                                            {{ $ingredient->name }}
                                        </a>
                                    </td>
                                    <td>{{ $ingredient->pivot->quantity }}</td>
                                    <td>
                                        @php
                                            $unitName = App\Models\Unit::find($ingredient->pivot->unit_id)->name ?? '';
                                        @endphp
                                        {{ $unitName }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <div class="col-md-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex align-items-center">
                    <i class="fas fa-boxes text-primary me-2"></i>
                    <h6 class="m-0 fw-bold text-primary">Informasi Stok</h6>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <p class="text-muted mb-1">Stok Gudang</p>
                                            <h4 class="mb-0 fw-bold {{ $product->stockWarehouses->first() && $product->stockWarehouses->first()->quantity < $product->min_stock ? 'text-danger' : 'text-dark' }}">
                                                @php
                                                    $wareStock = $product->stockWarehouses->first();
                                                    $stockQty = $wareStock ? floatval($wareStock->quantity) : 0;
                                                    $formattedStock = (floor($stockQty) == $stockQty) ? number_format($stockQty, 0, ',', '.') : number_format($stockQty, 2, ',', '.');
                                                @endphp
                                                {{ $formattedStock }}
                                                <small>{{ $product->baseUnit->name }}</small>
                                            </h4>
                                            @if($product->stockWarehouses->first() && $product->stockWarehouses->first()->quantity < $product->min_stock)
                                                <span class="badge bg-danger-light text-danger mt-1">Stok Rendah</span>
                                            @endif
                                        </div>
                                        <div class="icon-circle bg-primary-light">
                                            <i class="fas fa-warehouse fa-lg text-primary"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <p class="text-muted mb-1">Minimum Stok</p>
                                            <h4 class="mb-0 fw-bold text-dark">
                                                @php
                                                    $minStock = floatval($product->min_stock);
                                                    $formattedMinStock = (floor($minStock) == $minStock) ? number_format($minStock, 0, ',', '.') : number_format($minStock, 2, ',', '.');
                                                @endphp
                                                {{ $formattedMinStock }}
                                                <small>{{ $product->baseUnit->name }}</small>
                                            </h4>
                                        </div>
                                        <div class="icon-circle bg-warning-light">
                                            <i class="fas fa-battery-quarter fa-lg text-warning"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <h6 class="fw-bold mb-3">Stok Toko</h6>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Toko</th>
                                    <th>Stok</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($product->storeStocks as $storeStock)
                                <tr>
                                    <td>{{ $storeStock->store->name }}</td>
                                    <td>
                                        @php
                                            $storeQty = floatval($storeStock->quantity);
                                            $formattedStoreQty = (floor($storeQty) == $storeQty) ? number_format($storeQty, 0, ',', '.') : number_format($storeQty, 2, ',', '.');
                                        @endphp
                                        {{ $formattedStoreQty }} {{ $product->baseUnit->name }}
                                    </td>
                                    <td>
                                        @if($storeStock->quantity < $product->min_stock)
                                            <span class="badge bg-danger-light text-danger">Stok Rendah</span>
                                        @else
                                            <span class="badge bg-success-light text-success">Tersedia</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center py-3 text-muted">Belum ada informasi stok toko.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex align-items-center">
                    <i class="fas fa-history text-primary me-2"></i>
                    <h6 class="m-0 fw-bold text-primary">Transaksi Terbaru</h6>
                </div>
                <div class="card-body">
                    <ul class="nav nav-tabs" id="myTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="purchases-tab" data-bs-toggle="tab" data-bs-target="#purchases" type="button" role="tab" aria-controls="purchases" aria-selected="true">
                                <i class="fas fa-shopping-cart me-1"></i> Pembelian
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="sales-tab" data-bs-toggle="tab" data-bs-target="#sales" type="button" role="tab" aria-controls="sales" aria-selected="false">
                                <i class="fas fa-cash-register me-1"></i> Penjualan
                            </button>
                        </li>
                    </ul>
                    <div class="tab-content mt-3" id="myTabContent">
                        <div class="tab-pane fade show active" id="purchases" role="tabpanel" aria-labelledby="purchases-tab">
                            @if($recentPurchases->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Tanggal</th>
                                                <th>No. Faktur</th>
                                                <th>Pemasok</th>
                                                <th>Jumlah</th>
                                                <th>Harga</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($recentPurchases as $purchase)
                                            <tr>
                                                <td>{{ $purchase->purchase->date->format('d/m/Y') }}</td>
                                                <td>
                                                    <a href="{{ route('purchases.show', $purchase->purchase) }}" class="text-primary">
                                                        {{ $purchase->purchase->invoice_number }}
                                                    </a>
                                                </td>
                                                <td>{{ $purchase->purchase->supplier->name }}</td>
                                                <td>{{ $purchase->quantity }} {{ $purchase->unit->name }}</td>
                                                <td>Rp {{ number_format($purchase->price, 0, ',', '.') }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-4">
                                    <i class="fas fa-shopping-basket fa-4x text-muted mb-3"></i>
                                    <p class="text-muted">Belum ada riwayat pembelian.</p>
                                </div>
                            @endif
                        </div>
                        <div class="tab-pane fade" id="sales" role="tabpanel" aria-labelledby="sales-tab">
                            @if($recentSales->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Tanggal</th>
                                                <th>No. Faktur</th>
                                                <th>Toko</th>
                                                <th>Jumlah</th>
                                                <th>Harga</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($recentSales as $sale)
                                            <tr>
                                                <td>{{ $sale->sale->date->format('d/m/Y') }}</td>
                                                <td>
                                                    <a href="{{ route('sales.show', $sale->sale) }}" class="text-primary">
                                                        {{ $sale->sale->invoice_number }}
                                                    </a>
                                                </td>
                                                <td>{{ $sale->sale->store->name }}</td>
                                                <td>{{ $sale->quantity }} {{ $sale->unit->name }}</td>
                                                <td>Rp {{ number_format($sale->price, 0, ',', '.') }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-4">
                                    <i class="fas fa-cash-register fa-4x text-muted mb-3"></i>
                                    <p class="text-muted">Belum ada riwayat penjualan.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
