<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="format-detection" content="telephone=no">
    <title>Struk - {{ $sale->invoice_number }}</title>
    <style>
        @page {
            margin: 0;
            size: 80mm auto;
        }

        body {
            font-family: 'Arial', sans-serif;
            font-size: 12px;
            line-height: 1.4;
            margin: 0;
            padding: 0;
            width: 80mm; /* Standar lebar thermal printer */
            background-color: #fff;
        }

        .receipt {
            width: 76mm; /* 80mm - 4mm margin */
            padding: 8px 2mm;
            margin: 0 auto;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .text-left {
            text-align: left;
        }

        .font-bold {
            font-weight: bold;
        }

        .header {
            padding: 5px 0 10px;
            border-bottom: 1px solid #eee;
            margin-bottom: 10px;
        }

        .store-name {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 2px;
        }

        .store-tagline {
            font-size: 10px;
            color: #555;
            font-style: italic;
            margin-top: 2px;
        }

        .store-info {
            font-size: 11px;
            margin-top: 5px;
        }

        .logo {
            max-width: 60px;
            max-height: 60px;
            margin: 0 auto 8px;
            display: block;
        }

        .divider {
            border-top: 1px dashed #ccc;
            margin: 10px 0;
        }

        .divider-bold {
            border-top: 1.5px solid #000;
            margin: 10px 0;
        }

        .transaction-info {
            margin-bottom: 10px;
            font-size: 11px;
        }

        .transaction-info .row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3px;
        }

        .transaction-info .label {
            font-weight: bold;
            color: #555;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        .items-table th {
            border-bottom: 1px solid #ddd;
            padding: 5px 0;
            font-weight: bold;
            font-size: 11px;
            text-align: left;
        }

        .items-table td {
            padding: 5px 0;
            font-size: 11px;
            border-bottom: 1px dotted #eee;
        }

        .items-table .qty {
            width: 40px;
            text-align: center;
        }

        .items-table .price {
            text-align: right;
            width: 80px;
        }

        .items-table .unit {
            font-style: italic;
            font-size: 9px;
            color: #777;
        }

        .summary {
            margin-top: 15px;
        }

        .summary .row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3px;
        }

        .summary .total-row {
            font-weight: bold;
            font-size: 14px;
            margin: 8px 0;
        }

        .payment-info {
            margin-top: 5px;
            padding-top: 8px;
            border-top: 1px dotted #ddd;
        }

        .footer {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px dotted #ccc;
            text-align: center;
            font-size: 10px;
            color: #777;
        }

        .footer p {
            margin: 5px 0;
        }

        .footer .thank-you {
            font-size: 12px;
            font-weight: bold;
            margin: 10px 0;
            color: #333;
        }

        .barcode {
            text-align: center;
            margin: 15px 0 5px;
        }

        .barcode img {
            max-width: 60%;
            height: auto;
        }

        .footer-logo {
            max-width: 40px;
            margin: 8px auto;
            display: block;
            opacity: 0.7;
        }

        @media print {
            html, body {
                width: 80mm;
                height: auto;
            }

            .receipt {
                page-break-after: always;
            }

            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="receipt">
        <!-- Header Section -->
        <div class="header text-center">
            <!-- Jika ada logo
            <img src="{{ asset('img/logo.png') }}" alt="Logo" class="logo">
            -->
            <div class="store-name">{{ $sale->store->name }}</div>
            <div class="store-tagline">Restoran & Kafe</div>
            <div class="store-info">
                {{ $sale->store->address }}<br>
                Telp: {{ $sale->store->phone }}
            </div>
        </div>

        <!-- Transaction Info -->
        <div class="transaction-info">
            <div class="row">
                <span class="label">No. Invoice:</span>
                <span>{{ $sale->invoice_number }}</span>
            </div>
            <div class="row">
                <span class="label">Tanggal:</span>
                <span>{{ $sale->date->format('d/m/Y') }}</span>
            </div>
            <div class="row">
                <span class="label">Waktu:</span>
                <span>{{ $sale->date->format('H:i') }}</span>
            </div>
            <div class="row">
                <span class="label">Kasir:</span>
                <span>{{ $sale->creator->name }}</span>
            </div>
            @if($sale->customer_name)
            <div class="row">
                <span class="label">Pelanggan:</span>
                <span>{{ $sale->customer_name }}</span>
            </div>
            @endif
            <div class="row">
                <span class="label">Pembayaran:</span>
                <span>{{ ucfirst($sale->payment_type) }}</span>
            </div>
        </div>

        <div class="divider-bold"></div>

        <!-- Items Section -->
        <table class="items-table">
            <thead>
                <tr>
                    <th class="qty">Qty</th>
                    <th>Item</th>
                    <th class="price">Harga</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sale->saleDetails as $detail)
                <tr>
                    <td class="qty">
                        {{ intval($detail->quantity) }}<br>
                        <span class="unit">{{ $detail->unit->name }}</span>
                    </td>
                    <td>{{ $detail->product->name }}</td>
                    <td class="price">Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Summary Section -->
        <div class="summary">
            <div class="divider"></div>

            <div class="row">
                <span>Subtotal</span>
                <span>Rp {{ number_format($sale->total_amount + $sale->discount - $sale->tax, 0, ',', '.') }}</span>
            </div>

            @if($sale->discount > 0)
            <div class="row">
                <span>Diskon</span>
                <span>Rp {{ number_format($sale->discount, 0, ',', '.') }}</span>
            </div>
            @endif

            @if($sale->tax > 0)
            <div class="row">
                <span>Pajak (10%)</span>
                <span>Rp {{ number_format($sale->tax, 0, ',', '.') }}</span>
            </div>
            @endif

            <div class="divider"></div>

            <div class="row total-row">
                <span>TOTAL</span>
                <span>Rp {{ number_format($sale->total_amount, 0, ',', '.') }}</span>
            </div>

            <!-- Payment Details -->
            <div class="payment-info">
                <div class="row">
                    <span>Tunai</span>
                    <span>Rp {{ number_format($sale->total_payment, 0, ',', '.') }}</span>
                </div>

                <div class="row">
                    <span>Kembali</span>
                    <span>Rp {{ number_format($sale->change, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        <!-- Footer Section -->
        <div class="footer">
            <div class="barcode">
                <!-- Placeholder for barcode, bisa diganti dengan QR code jika perlu -->
                <svg width="200" height="40">
                    <rect x="0" y="0" width="200" height="40" style="fill:none; stroke:none" />
                    <text x="100" y="20" text-anchor="middle" style="font-size: 12px">{{ $sale->invoice_number }}</text>
                </svg>
            </div>

            <p class="thank-you">Terima Kasih atas Kunjungan Anda</p>

            <p>Simpan struk ini sebagai bukti pembayaran yang sah</p>
            <p>Barang yang sudah dibeli tidak dapat ditukar kembali</p>

            <!-- Optional social media / website info -->
            <p>www.namatoko.com | @namatoko</p>

            <!-- Optional small logo in footer
            <img src="{{ asset('img/logo-small.png') }}" alt="Logo" class="footer-logo">
            -->
        </div>
    </div>

    <!-- Script khusus untuk RAWBT Printer pada Android -->
    <script>
        window.onload = function() {
            // Tunggu sebentar untuk memastikan halaman dirender dengan baik
            setTimeout(function() {
                try {
                    // Mendapatkan HTML struk
                    var receiptHTML = document.querySelector('.receipt').innerHTML;

                    // Membuat ESC/POS commands untuk header dan font size
                    var escposCommands = '\x1B@'; // Initialize printer
                    escposCommands += '\x1B!\x00'; // Set font to normal

                    // Encode ke base64 untuk mencegah masalah karakter khusus
                    var base64Data = btoa(unescape(encodeURIComponent(receiptHTML)));

                    // Buat URL dengan protocol rawbt
                    var rawbtURL = 'rawbt:base64,' + base64Data;

                    // Panggil RAWBT Printer
                    console.log("Sending to RAWBT Printer...");
                    window.location.href = rawbtURL;
                } catch (e) {
                    console.error("Error sending to RAWBT:", e);
                    alert("Terjadi kesalahan saat mencetak: " + e.message);
                }
            }, 500);
        }
    </script>
</body>
</html>
