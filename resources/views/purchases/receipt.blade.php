<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Faktur Pembelian - {{ $purchase->invoice_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 14px;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #2563eb;
            padding-bottom: 15px;
        }
        
        .company-name {
            font-size: 22px;
            font-weight: bold;
            margin-bottom: 5px;
            color: #2563eb;
        }
        
        .company-info {
            font-size: 12px;
            margin-bottom: 5px;
        }
        
        .document-title {
            font-size: 18px;
            font-weight: bold;
            margin: 20px 0;
            text-align: center;
            color: #2563eb;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .info-container {
            width: 100%;
            margin-bottom: 20px;
        }
        
        .info-container .left {
            float: left;
            width: 50%;
        }
        
        .info-container .right {
            float: right;
            width: 50%;
            text-align: right;
        }
        
        .clear {
            clear: both;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        table th, table td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        
        table th {
            background-color: #f8fafc;
            font-weight: bold;
            color: #2563eb;
        }
        
        table tr:nth-child(even) {
            background-color: #f9fafb;
        }
        
        table .text-right {
            text-align: right;
        }
        
        .total-row {
            font-weight: bold;
            background-color: #f0f9ff !important;
        }
        
        .total-row td {
            color: #2563eb;
        }
        
        .signature-container {
            margin-top: 50px;
            width: 100%;
        }
        
        .signature-box {
            float: left;
            width: 30%;
            text-align: center;
        }
        
        .signature-line {
            margin-top: 70px;
            border-top: 1px solid #333;
            width: 80%;
            display: inline-block;
        }
        
        .footer {
            margin-top: 50px;
            font-size: 12px;
            text-align: center;
            color: #6b7280;
            border-top: 1px solid #e5e7eb;
            padding-top: 10px;
        }
    </style>
    </head>
    <body>
    <div class="header">
        <div class="company-name">{{ config('app.name', 'Sistem Manajemen Inventaris') }}</div>
        <div class="company-info">Jl. Contoh Alamat No.123, Jakarta, Indonesia</div>
        <div class="company-info">Telp: 021-1234567, Email: info@example.com</div>
    </div>
    
    <div class="document-title">Faktur Pembelian</div>
    
    <div class="info-container">
        <div class="left">
            <div><strong>No. Faktur:</strong> {{ $purchase->invoice_number }}</div>
            <div><strong>Tanggal:</strong> {{ $purchase->date->format('d/m/Y') }}</div>
            <div><strong>Pembayaran:</strong> {{ $purchase->payment_type === 'tunai' ? 'Tunai' : 'Tempo' }}</div>
            @if($purchase->payment_type === 'tempo')
                <div><strong>Jatuh Tempo:</strong> {{ $purchase->due_date->format('d/m/Y') }}</div>
            @endif
        </div>
        <div class="right">
            <div><strong>Pemasok:</strong> {{ $purchase->supplier->name }}</div>
            <div>{{ $purchase->supplier->address ?: 'Alamat tidak tersedia' }}</div>
            <div>{{ $purchase->supplier->phone ?: 'Telepon tidak tersedia' }}</div>
        </div>
    </div>
    
    <div class="clear"></div>
    
    <table>
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 15%;">Kode</th>
                <th style="width: 35%;">Produk</th>
                <th style="width: 10%;">Jumlah</th>
                <th style="width: 10%;">Satuan</th>
                <th style="width: 12%;">Harga</th>
                <th style="width: 13%;">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($purchase->purchaseDetails as $index => $detail)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $detail->product->code }}</td>
                    <td>{{ $detail->product->name }}</td>
                    <td>{{ $detail->quantity }}</td>
                    <td>{{ $detail->unit->name }}</td>
                    <td class="text-right">{{ number_format($detail->price, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="6" class="text-right">Total:</td>
                <td class="text-right">{{ number_format($purchase->total_amount, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>
    
    @if($purchase->note)
        <div><strong>Catatan:</strong> {{ $purchase->note }}</div>
    @endif
    
    <div class="signature-container">
        <div class="signature-box">
            <div>Dibuat Oleh</div>
            <div class="signature-line"></div>
            <div>{{ $purchase->creator->name }}</div>
        </div>
        <div class="signature-box">
            <div>Disetujui Oleh</div>
            <div class="signature-line"></div>
            <div></div>
        </div>
        <div class="signature-box">
            <div>Diterima Oleh</div>
            <div class="signature-line"></div>
            <div></div>
        </div>
    </div>
    
    <div class="clear"></div>
    
    <div class="footer">
        Dokumen ini dihasilkan oleh sistem dan sah tanpa tanda tangan.
    </div>
    </body>
</html>