<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Struk Penjualan - {{ $sale->invoice_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 10px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 10px;
        }
        
        .company-name {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 5px;
            color: #2563eb;
        }
        
        .company-info {
            font-size: 10px;
            margin-bottom: 5px;
        }
        
        .receipt-title {
            font-size: 14px;
            font-weight: bold;
            margin: 10px 0;
            text-align: center;
            color: #2563eb;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .info-container {
            margin-bottom: 10px;
        }
        
        .info-item {
            margin-bottom: 3px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        
        table th, table td {
            border-top: 1px solid #ddd;
            border-bottom: 1px solid #ddd;
            padding: 5px;
            text-align: left;
        }
        
        table th {
            font-weight: bold;
        }
        
        table .text-right {
            text-align: right;
        }
        
        .totals-table {
            width: 100%;
            margin-top: 10px;
        }
        
        .totals-table th, .totals-table td {
            border: none;
            padding: 2px;
        }
        
        .totals-table th {
            text-align: right;
            font-weight: normal;
        }
        
        .totals-table .total-row {
            font-weight: bold;
        }
        
        .footer {
            margin-top: 20px;
            font-size: 10px;
            text-align: center;
        }
        
        .dashed-line {
            border-bottom: 1px dashed #333;
            margin: 15px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">{{ $sale->store->name }}</div>
        <div class="company-info">{{ $sale->store->address ?: 'Alamat toko tidak tersedia' }}</div>
        <div class="company-info">Telp: {{ $sale->store->phone ?? 'N/A' }} | Email: {{ $sale->store->email ?? 'N/A' }}</div>
    </div>
    
    <div class="receipt-title">STRUK PENJUALAN</div>
    
    <div class="info-container">
        <div class="info-item"><strong>Faktur:</strong> {{ $sale->invoice_number }}</div>
        <div class="info-item"><strong>Tanggal:</strong> {{ $sale->date->format('d/m/Y H:i') }}</div>
        <div class="info-item"><strong>Pelanggan:</strong> {{ $sale->customer_name ?? 'Pelanggan Umum' }}</div>
        <div class="info-item"><strong>Kasir:</strong> {{ $sale->creator->name }}</div>
    </div>
    
    <div class="dashed-line"></div>
    
    <table>
        <thead>
            <tr>
                <th>Produk</th>
                <th>Qty</th>
                <th>Harga</th>
                <th>Disc</th>
                <th class="text-right">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sale->saleDetails as $detail)
                <tr>
                    <td>{{ $detail->product->name }}</td>
                    <td>{{ $detail->quantity }} {{ $detail->unit->name }}</td>
                    <td>{{ number_format($detail->price, 0, ',', '.') }}</td>
                    <td>{{ $detail->discount > 0 ? number_format($detail->discount, 0, ',', '.') : '-' }}</td>
                    <td class="text-right">{{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    
    <div class="dashed-line"></div>
    
    <table class="totals-table">
        <tr>
            <th style="width: 70%;">Subtotal:</th>
            <td class="text-right">{{ number_format($sale->total_amount + $sale->discount - $sale->tax, 0, ',', '.') }}</td>
        </tr>
        @if($sale->discount > 0)
        <tr>
            <th>Diskon:</th>
            <td class="text-right">{{ number_format($sale->discount, 0, ',', '.') }}</td>
        </tr>
        @endif
        @if($sale->tax > 0)
        <tr>
            <th>Pajak:</th>
            <td class="text-right">{{ number_format($sale->tax, 0, ',', '.') }}</td>
        </tr>
        @endif
        <tr class="total-row">
            <th>Total:</th>
            <td class="text-right">{{ number_format($sale->total_amount, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <th>Dibayar ({{ $sale->payment_type === 'tunai' ? 'Tunai' : ($sale->payment_type === 'kartu' ? 'Kartu' : 'Tempo') }}):</th>
            <td class="text-right">{{ number_format($sale->total_payment, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <th>Kembalian:</th>
            <td class="text-right">{{ number_format($sale->change, 0, ',', '.') }}</td>
        </tr>
    </table>
    
    <div class="dashed-line"></div>
    
    <div class="footer">
        Terima kasih atas kunjungan Anda!
        <br>
        Barang yang sudah dibeli tidak dapat dikembalikan.
    </div>
</body>
</html>