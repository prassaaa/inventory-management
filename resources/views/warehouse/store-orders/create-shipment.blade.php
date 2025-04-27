@extends('layouts.app')

@section('title', 'Buat Pengiriman')

@section('content')
<div class="container-fluid">
    <div class="row mb-4 align-items-center">
        <div class="col">
            <h1 class="h3 mb-0 text-dark fw-bold">
                <i class="fas fa-truck me-2 text-primary"></i> Buat Pengiriman
            </h1>
            <p class="text-muted">Untuk pesanan {{ $storeOrder->order_number }} dari {{ $storeOrder->store->name }}</p>
        </div>
        <div class="col-auto">
            <a href="{{ route('warehouse.store-orders.show', $storeOrder->id) }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Kembali
            </a>
        </div>
    </div>

    @if(session('error'))
    <div class="alert alert-danger">
        {{ session('error') }}
    </div>
    @endif

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 fw-bold text-primary">Informasi Pesanan</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr>
                            <th width="30%">No. Pesanan</th>
                            <td>{{ $storeOrder->order_number }}</td>
                        </tr>
                        <tr>
                            <th>Tanggal Pesanan</th>
                            <td>{{ $storeOrder->date->format('d/m/Y') }}</td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td>
                                @if($storeOrder->status == 'pending')
                                    <span class="badge bg-warning">Menunggu</span>
                                @elseif($storeOrder->status == 'confirmed_by_admin')
                                    <span class="badge bg-info">Dikonfirmasi</span>
                                @elseif($storeOrder->status == 'forwarded_to_warehouse')
                                    <span class="badge bg-primary">Diteruskan</span>
                                @elseif($storeOrder->status == 'shipped')
                                    <span class="badge bg-info">Dikirim</span>
                                @elseif($storeOrder->status == 'completed')
                                    <span class="badge bg-success">Selesai</span>
                                @else
                                    <span class="badge bg-secondary">{{ ucfirst($storeOrder->status) }}</span>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr>
                            <th width="30%">Toko</th>
                            <td>{{ $storeOrder->store->name }}</td>
                        </tr>
                        <tr>
                            <th>Alamat</th>
                            <td>{{ $storeOrder->store->address }}</td>
                        </tr>
                        <tr>
                            <th>Telepon</th>
                            <td>{{ $storeOrder->store->phone }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 fw-bold text-primary">Form Pengiriman</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('warehouse.store-orders.shipment.store', $storeOrder->id) }}" method="POST">
                @csrf

                <div class="table-responsive mb-3">
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th width="5%">No</th>
                                <th width="35%">Produk</th>
                                <th width="15%">Satuan</th>
                                <th width="15%">Jumlah Dipesan</th>
                                <th width="15%">Stok Gudang</th>
                                <th width="15%">Jumlah Dikirim</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($storeOrder->storeOrderDetails as $index => $detail)
                            @php
                                // Cek stok gudang
                                $stockWarehouse = App\Models\StockWarehouse::where('product_id', $detail->product_id)
                                    ->where('unit_id', $detail->unit_id)
                                    ->first();

                                $availableStock = $stockWarehouse ? $stockWarehouse->quantity : 0;
                            @endphp
                            <tr class="{{ $availableStock < $detail->quantity ? 'table-warning' : '' }}">
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $detail->product->name }}</td>
                                <td>{{ $detail->unit->name }}</td>
                                <td>{{ intval($detail->quantity) }}</td>
                                <td>
                                    {{ intval($availableStock) }}
                                    @if($availableStock < $detail->quantity)
                                        <i class="fas fa-exclamation-triangle text-warning" data-bs-toggle="tooltip"
                                           title="Stok tidak mencukupi!"></i>
                                    @endif
                                </td>
                                <td>
                                    <input type="hidden" name="product_id[]" value="{{ $detail->product_id }}">
                                    <input type="hidden" name="unit_id[]" value="{{ $detail->unit_id }}">
                                    <input type="number" name="quantity[]" class="form-control"
                                           value="{{ intval(min($detail->quantity, $availableStock)) }}"
                                           min="0" max="{{ intval($availableStock) }}" required>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="form-group mb-4">
                    <label for="note" class="form-label">Catatan Pengiriman</label>
                    <textarea class="form-control" id="note" name="note" rows="3">{{ old('note') }}</textarea>
                </div>

                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <a href="{{ route('warehouse.store-orders.show', $storeOrder->id) }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times me-1"></i> Batal
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Buat Pengiriman
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Init tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });
    });
</script>
@endsection
