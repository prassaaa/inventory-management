@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Buat Pengiriman</h4>
                    <div class="float-end">
                        <a href="{{ route('store-orders.show', $storeOrder->id) }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif

                    <form action="{{ route('shipments.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="store_order_id" value="{{ $storeOrder->id }}">

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header bg-light">
                                        <h5 class="mb-0">Informasi Pesanan</h5>
                                    </div>
                                    <div class="card-body">
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
                                                <td>{!! $storeOrder->status_badge !!}</td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header bg-light">
                                        <h5 class="mb-0">Informasi Toko</h5>
                                    </div>
                                    <div class="card-body">
                                        <table class="table table-borderless">
                                            <tr>
                                                <th width="30%">Nama Toko</th>
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

                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <h5 class="mb-0">Detail Pengiriman</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
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
                                                $stockWarehouse = App\Models\StockWarehouse::where('product_id', $detail->product_id)
                                                    ->where('unit_id', $detail->unit_id)
                                                    ->first();
                                                $availableStock = $stockWarehouse ? $stockWarehouse->quantity : 0;
                                            @endphp
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td>{{ $detail->product->name }}</td>
                                                <td>{{ $detail->unit->name }}</td>
                                                <td>{{ $detail->quantity }}</td>
                                                <td>{{ $availableStock }}</td>
                                                <td>
                                                    <input type="hidden" name="product_id[]" value="{{ $detail->product_id }}">
                                                    <input type="hidden" name="unit_id[]" value="{{ $detail->unit_id }}">
                                                    <input type="number" class="form-control" name="quantity[]" value="{{ min($detail->quantity, $availableStock) }}" min="0" max="{{ $availableStock }}" required>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                <div class="form-group mt-3">
                                    <label for="note">Catatan Pengiriman</label>
                                    <textarea name="note" id="note" class="form-control" rows="3"></textarea>
                                </div>
                            </div>
                        </div>

                        <div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Simpan Pengiriman
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
