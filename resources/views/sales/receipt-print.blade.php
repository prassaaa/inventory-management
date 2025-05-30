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

        /* Special styling for dining option */
        .dining-option {
            font-weight: bold;
            font-size: 12px;
            margin-bottom: 5px;
            text-align: left;
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
            <div class="store-tagline">Cafe And Eatry</div>
            <div class="store-info">
                {{ $sale->store->address }}<br>
                Kec. Kroya Cilacap
            </div>
        </div>

        <div class="divider-bold"></div>

        <!-- Dining Option - Following original format -->
        <div class="dining-option">{{ $sale->dining_option_text }}</div>

        <!-- Transaction Info -->
        <div class="transaction-info">
            <div class="row">
                <span class="label">Staf:</span>
                <span>{{ $sale->creator->name }}</span>
            </div>
            <div class="row">
                <span class="label">Waktu:</span>
                <span>{{ $sale->date->format('Y-m-d H:i') }}</span>
            </div>
        </div>

        <!-- Items Header -->
        <table class="items-table">
            <thead>
                <tr>
                    <th>Barang</th>
                    <th class="qty">Jmlh</th>
                    <th class="price">Juml</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sale->saleDetails as $detail)
                <tr>
                    <td>{{ $detail->product->name }}</td>
                    <td class="qty">{{ intval($detail->quantity) }}</td>
                    <td class="price">Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="divider-bold"></div>

        <!-- Summary Section - Following original format -->
        <div class="summary">
            <div class="row">
                <span>Item: {{ $sale->saleDetails->count() }}</span>
                <span>Subtotal: Rp {{ number_format($sale->total_amount + $sale->discount - $sale->tax, 0, ',', '.') }}</span>
            </div>
            <div class="row">
                <span>Jmlh: {{ $sale->saleDetails->sum('quantity') }}</span>
                <span></span>
            </div>

            <div class="divider-bold"></div>

            <div class="row total-row">
                <span>Total:</span>
                <span>Rp {{ number_format($sale->total_amount, 0, ',', '.') }}</span>
            </div>

            <div class="divider-bold"></div>
        </div>

        <!-- Footer Section -->
        <div class="footer">
            <p>Kata sandi wifi : Kiagenggiring2</p>
            <p class="thank-you">Terima Kasih</p>
            <p>Silahkan datang lagi!</p>

            <div style="margin: 15px 0;">
                <p>Didukung oleh WnO POS</p>
                <p>www.wnopos.com</p>
            </div>
        </div>
    </div>

    <!-- Script untuk RAWBT Printer dan fallback ke print browser -->
    <script>
        window.onload = function() {
            // Delay sedikit untuk memastikan rendering selesai
            setTimeout(function() {
                // Deteksi apakah perangkat Android
                var isAndroid = /Android/i.test(navigator.userAgent);

                if (isAndroid) {
                    try {
                        // Mendapatkan HTML struk
                        var receiptHTML = document.querySelector('.receipt').outerHTML;

                        // Membuat ESC/POS commands untuk header dan font size
                        var escposCommands = '\x1B@'; // Initialize printer
                        escposCommands += '\x1B!\x38'; // Set emphasized + double-height + double-width mode

                        // Menggabungkan commands dengan HTML
                        var printData = escposCommands + receiptHTML;

                        // Encode ke base64 untuk mencegah masalah karakter khusus
                        var base64Data = btoa(unescape(encodeURIComponent(printData)));

                        // Buat URL dengan protocol rawbt
                        var rawbtURL = 'rawbt:base64,' + base64Data;

                        // Panggil RAWBT Printer
                        console.log("Sending to RAWBT Printer...");
                        window.location.href = rawbtURL;
                    } catch (e) {
                        console.error("Error sending to RAWBT:", e);
                        // Fallback ke cetak browser standar jika terjadi error
                        window.print();
                    }
                } else {
                    // Jika bukan Android, gunakan cetak browser standar
                    window.print();
                }
            }, 500);
        }

        // Lakukan tindakan setelah cetak selesai
        window.onafterprint = function() {
            // Delay untuk mencegah penutupan terlalu cepat
            setTimeout(function() {
                // Cek apakah jendela ini dibuka oleh jendela lain
                if (window.opener) {
                    window.close(); // Tutup jendela cetak
                }
            }, 100);
        }
    </script>
</body>
</html>
