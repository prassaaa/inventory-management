<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice {{ $shipment->shipment_number ?? 'N/A' }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            font-size: 12pt;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }
        .title {
            font-size: 16pt;
            font-weight: bold;
            margin: 10px 0;
        }
        .document-number {
            font-size: 14pt;
            margin: 10px 0;
        }
        .info-section {
            margin-bottom: 20px;
        }
        .info-row {
            display: flex;
            margin-bottom: 5px;
        }
        .info-label {
            width: 150px;
            font-weight: bold;
        }
        .info-value {
            flex: 1;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table, th, td {
            border: 1px solid #000;
        }
        th, td {
            padding: 5px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .text-end {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">INVOICE</div>
        @php
            $invoiceNumber = str_replace('SHP-', 'INV-', $shipment->shipment_number ?? 'N/A');
        @endphp
        <div class="document-number">No. {{ $invoiceNumber }}</div>
    </div>

    <div class="info-section">
        <div class="info-row">
            <div class="info-label">Tanggal Pengiriman:</div>
            <div class="info-value">{{ $shipment->date ? $shipment->date->format('d/m/Y') : '-' }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">No. Pesanan:</div>
            <div class="info-value">{{ $shipment->storeOrder->order_number ?? 'N/A' }}</div>
        </div>
    </div>

    <div class="info-section">
        <div class="info-row">
            <div class="info-label">Dari:</div>
            <div class="info-value">Gudang Pusat</div>
        </div>
        <div class="info-row">
            <div class="info-label">Tujuan:</div>
            <div class="info-value">
                {{ $shipment->storeOrder->store->name ?? 'N/A' }}<br>
                {{ $shipment->storeOrder->store->address ?? 'N/A' }}<br>
                Telp: {{ $shipment->storeOrder->store->phone ?? 'N/A' }}
            </div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="35%">Nama Produk</th>
                <th width="10%">Satuan</th>
                <th width="10%">Jumlah</th>
                <th width="15%">Harga</th>
                <th width="15%">Subtotal</th>
                <th width="10%">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @php $total = 0; @endphp
            @forelse($shipment->shipmentDetails as $index => $detail)
            @php
                // Cari detail pesanan yang sesuai dengan produk dan unit ini
                $orderDetail = $shipment->storeOrder->storeOrderDetails->where('product_id', $detail->product_id)
                                ->where('unit_id', $detail->unit_id)->first();
                $price = $orderDetail ? $orderDetail->price : 0;
                $subtotal = $price * $detail->quantity;
                $total += $subtotal;
            @endphp
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $detail->product->name ?? 'N/A' }}</td>
                <td class="text-center">{{ $detail->unit->name ?? 'N/A' }}</td>
                <td class="text-center">{{ intval($detail->quantity) }}</td>
                <td class="text-end">{{ number_format($price, 0, ',', '.') }}</td>
                <td class="text-end">{{ number_format($subtotal, 0, ',', '.') }}</td>
                <td></td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center">Tidak ada item</td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <th colspan="5" class="text-end">Total</th>
                <th class="text-end">{{ number_format($total, 0, ',', '.') }}</th>
                <th></th>
            </tr>
        </tfoot>
    </table>

    <div class="info-section">
        <div class="info-row">
            <div class="info-label">Catatan:</div>
            <div class="info-value">{{ $shipment->note ?? '-' }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Metode Pembayaran:</div>
            <div class="info-value">
                @if(isset($shipment->storeOrder->payment_type))
                    @if($shipment->storeOrder->payment_type == 'cash')
                        Tunai
                    @elseif($shipment->storeOrder->payment_type == 'credit')
                        Kredit (Jatuh Tempo: {{ $shipment->storeOrder->due_date ? $shipment->storeOrder->due_date->format('d/m/Y') : '-' }})
                    @else
                        {{ ucfirst($shipment->storeOrder->payment_type) }}
                    @endif
                @else
                    -
                @endif
            </div>
        </div>
    </div>

</body>
</html>
