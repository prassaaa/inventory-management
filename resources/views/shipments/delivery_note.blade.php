<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Jalan {{ $shipment->shipment_number ?? 'N/A' }}</title>
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
            margin-bottom: 30px;
        }
        table, th, td {
            border: 1px solid #000;
        }
        th, td {
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .text-center {
            text-align: center;
        }
        .signature-section {
            margin-top: 50px;
            width: 100%;
            display: table;
        }
        .signature-box {
            display: table-cell;
            width: 33.33%;
            text-align: center;
            vertical-align: top;
            padding: 0 10px;
        }
        .signature-line {
            border-bottom: 1px solid #000;
            margin-top: 60px;
            margin-bottom: 10px;
            height: 1px;
            width: 80%;
            margin-left: auto;
            margin-right: auto;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">SURAT JALAN</div>
        <div class="document-number">No. {{ $shipment->shipment_number ?? 'N/A' }}</div>
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
                <th width="10%">No</th>
                <th width="50%">Nama Produk</th>
                <th width="20%">Satuan</th>
                <th width="20%">Jumlah</th>
            </tr>
        </thead>
        <tbody>
            @forelse($shipment->shipmentDetails as $index => $detail)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $detail->product->name ?? 'N/A' }}</td>
                <td class="text-center">{{ $detail->unit->name ?? 'N/A' }}</td>
                <td class="text-center">{{ $detail->quantity ?? 0 }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="text-center">Tidak ada item</td>
            </tr>
            @endforelse
        </tbody>
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

    <div class="signature-section">
        <div class="signature-box">
            <div class="signature-line"></div>
            <div><strong>Dibuat oleh</strong></div>
            <div>Gudang Pusat</div>
        </div>
        <div class="signature-box">
            <div class="signature-line"></div>
            <div><strong>Pengirim</strong></div>
            <div>(...........................)</div>
        </div>
        <div class="signature-box">
            <div class="signature-line"></div>
            <div><strong>Penerima</strong></div>
            <div>(...........................)</div>
        </div>
    </div>

</body>
</html>
