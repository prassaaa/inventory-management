<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Surat Jalan - {{ $shipment->shipment_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12pt;
            line-height: 1.4;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0 0 5px 0;
            font-size: 20pt;
        }
        .header p {
            margin: 0;
            font-size: 10pt;
        }
        .info-section {
            margin-bottom: 20px;
        }
        .info-section:after {
            content: "";
            display: table;
            clear: both;
        }
        .left-info {
            float: left;
            width: 48%;
        }
        .right-info {
            float: right;
            width: 48%;
            text-align: right;
        }
        .info-box {
            border: 1px solid #000;
            padding: 10px;
            margin-bottom: 15px;
        }
        .info-title {
            font-weight: bold;
            margin-bottom: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        table th, table td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }
        table th {
            background-color: #f2f2f2;
        }
        .signature-section {
            margin-top: 50px;
        }
        .signature-box {
            float: left;
            width: 30%;
            text-align: center;
        }
        .signature-line {
            border-top: 1px solid #000;
            margin-top: 60px;
            padding-top: 5px;
        }
        .clearfix:after {
            content: "";
            display: table;
            clear: both;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>SURAT JALAN</h1>
            <p>{{ config('app.name', 'Laravel') }}</p>
        </div>

        <div class="info-section">
            <div class="left-info">
                <div class="info-box">
                    <div class="info-title">Dikirim Kepada:</div>
                    <div>{{ $shipment->storeOrder->store->name }}</div>
                    <div>{{ $shipment->storeOrder->store->address }}</div>
                    <div>{{ $shipment->storeOrder->store->phone }}</div>
                </div>
            </div>
            <div class="right-info">
                <div class="info-box">
                    <div class="info-title">Informasi Pengiriman:</div>
                    <div>No. Surat Jalan: {{ $shipment->shipment_number }}</div>
                    <div>Tanggal: {{ \Carbon\Carbon::parse($shipment->date)->format('d/m/Y') }}</div>
                    <div>No. Pesanan: {{ $shipment->storeOrder->order_number }}</div>
                    <div>Tanggal Pesanan: {{ \Carbon\Carbon::parse($shipment->storeOrder->date)->format('d/m/Y') }}</div>
                </div>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th width="5%">No</th>
                    <th width="40%">Nama Barang</th>
                    <th width="15%">Satuan</th>
                    <th width="15%">Jumlah</th>
                    <th width="25%">Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @foreach($shipment->shipmentDetails as $index => $detail)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $detail->product->name }}</td>
                    <td>{{ $detail->unit->name }}</td>
                    <td>{{ $detail->quantity }}</td>
                    <td></td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div>
            <p><strong>Catatan:</strong> {{ $shipment->note ?? '-' }}</p>
        </div>

        <div class="signature-section clearfix">
            <div class="signature-box">
                <div>Disiapkan Oleh,</div>
                <div class="signature-line">{{ $shipment->createdBy->name }}</div>
                <div>Admin Gudang</div>
            </div>
            <div class="signature-box">
                <div>Diperiksa Oleh,</div>
                <div class="signature-line">................................</div>
                <div>Pengirim/Kurir</div>
            </div>
            <div class="signature-box">
                <div>Diterima Oleh,</div>
                <div class="signature-line">................................</div>
                <div>Penerima</div>
            </div>
        </div>
    </div>
</body>
</html>
