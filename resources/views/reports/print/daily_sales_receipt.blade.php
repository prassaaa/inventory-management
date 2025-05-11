<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Harian Kasir - {{ $store->name }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            padding: 20px;
            margin: 0;
        }
        .container {
            max-width: 960px;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .row {
            display: flex;
            flex-wrap: wrap;
            margin: 0 -15px;
        }
        .col-md-6 {
            flex: 0 0 50%;
            max-width: 50%;
            padding: 0 15px;
            box-sizing: border-box;
        }
        @media (max-width: 768px) {
            .col-md-6 {
                flex: 0 0 100%;
                max-width: 100%;
            }
        }
        .btn {
            display: inline-block;
            padding: 8px 16px;
            background-color: #4e73df;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            margin-top: 20px;
            margin-right: 10px;
            text-decoration: none;
        }
        .btn:hover {
            background-color: #375bcb;
        }
        .btn-secondary {
            background-color: #6c757d;
        }
        .btn-secondary:hover {
            background-color: #5a6268;
        }
        .status {
            padding: 10px;
            margin: 10px 0;
            border-radius: 4px;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
        }
        .hidden {
            display: none;
        }
        .receipt-content {
            display: none;
        }
        .preview-container {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            font-family: 'Courier New', monospace;
            white-space: pre;
            overflow-x: auto;
            font-size: 12px;
            line-height: 1.4;
            border: 1px solid #ddd;
            margin-top: 20px;
            max-width: 280px; /* Approximates 58mm */
            margin-left: auto;
            margin-right: auto;
        }
        .preview-header {
            font-weight: bold;
            margin-bottom: 10px;
            font-size: 16px;
            color: #333;
        }
        .section-title {
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 10px;
            font-size: 18px;
        }
        .info-box {
            background-color: #e9ecef;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .info-item {
            margin-bottom: 8px;
        }
        .info-label {
            font-weight: bold;
            display: inline-block;
            width: 150px;
        }
        .text-center {
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2 class="text-center">Cetak Laporan Harian Kasir</h2>
        <div id="print-status" class="status hidden"></div>

        <div class="row">
            <div class="col-md-6">
                <div class="section-title text-center">Informasi Laporan</div>
                <div class="info-box">
                    <div class="info-item">
                        <span class="info-label">Toko:</span> {{ $store->name }}
                    </div>
                    <div class="info-item">
                        <span class="info-label">Alamat:</span> {{ $store->address }}
                    </div>
                    <div class="info-item">
                        <span class="info-label">Telepon:</span> {{ $store->phone }}
                    </div>
                    <div class="info-item">
                        <span class="info-label">Tanggal:</span> {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}
                    </div>
                    <div class="info-item">
                        <span class="info-label">Total Transaksi:</span> {{ $total_transactions }}
                    </div>
                    <div class="info-item">
                        <span class="info-label">Total Penjualan:</span> Rp {{ number_format($total_sales, 0, ',', '.') }}
                    </div>
                </div>

                <div class="text-center">
                    <button id="print-button" class="btn">Cetak Struk</button>
                    <button id="close-button" class="btn btn-secondary">Tutup Jendela</button>
                </div>
            </div>

            <div class="col-md-6">
                <div class="section-title text-center">Preview Struk (58mm)</div>
                <div class="preview-container" id="receipt-preview"></div>
            </div>
        </div>
    </div>

    <!-- Konten struk yang akan dicetak via RawBT - dioptimalkan untuk 58mm dengan semua element ditengahkan -->
    <div class="receipt-content">
{{ $store->name }}
{{ $store->address }}
Telp: {{ $store->phone }}

================================

LAPORAN HARIAN KASIR
{{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}
{{ now()->format('H:i') }}
{{ Auth::user()->name }}

================================

RINGKASAN PENJUALAN
################################
Tot. Transaksi: {{ $total_transactions }}
Tot. Penjualan: {{ number_format($total_sales, 0, ',', '.') }}

================================

DETAIL PEMBAYARAN
################################
@foreach($payment_methods as $method => $data)
{{ ucfirst(substr($method, 0, 8)) }}:
{{ $data['count'] }}x | Rp {{ number_format($data['amount'], 0, ',', '.') }}
@endforeach

================================

DAFTAR PRODUK TERJUAL
################################
@php
    // Hitung semua produk yang terjual pada hari ini
    $soldProducts = [];
    foreach($sales as $sale) {
        foreach($sale->saleDetails as $detail) {
            $productId = $detail->product_id;
            $productName = $detail->product->name;

            if (!isset($soldProducts[$productId])) {
                $soldProducts[$productId] = [
                    'name' => $productName,
                    'quantity' => 0,
                    'amount' => 0
                ];
            }

            $soldProducts[$productId]['quantity'] += $detail->quantity;
            $soldProducts[$productId]['amount'] += $detail->subtotal;
        }
    }

    // Sort by total amount
    usort($soldProducts, function($a, $b) {
        return $b['amount'] <=> $a['amount'];
    });
@endphp

@foreach($soldProducts as $product)
{{ substr($product['name'], 0, 20) }}
{{ $product['quantity'] }}x | Rp {{ number_format($product['amount'], 0, ',', '.') }}
@endforeach

================================

{{ now()->format('d/m/Y H:i:s') }}

*** TERIMA KASIH ***
</div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Deteksi apakah perangkat Android
            var isAndroid = /Android/i.test(navigator.userAgent);
            var printButton = document.getElementById('print-button');
            var closeButton = document.getElementById('close-button');
            var printStatus = document.getElementById('print-status');
            var receiptPreview = document.getElementById('receipt-preview');

            // Tampilkan preview struk
            var receiptText = document.querySelector('.receipt-content').innerText;
            receiptPreview.textContent = receiptText;

            // Handle tombol print
            printButton.addEventListener('click', function() {
                try {
                    // Konstanta untuk printer 58mm (32-35 karakter per baris)
                    const MAX_CHARS_PER_LINE = 32;

                    // Inisialisasi printer dan atur ukuran kertas
                    var escposInit = '\x1B@'; // Initialize printer
                    var setFontSmall = '\x1B\x21\x01'; // Set smaller font
                    var setPaper58mm = '\x1D\x57\x00\x32'; // Set paper width to 58mm (32 chars per line)
                    var centerAlign = '\x1B\x61\x01'; // Center alignment
                    var leftAlign = '\x1B\x61\x00'; // Left alignment
                    var feedAndCut = '\x1D\x56\x42\x00'; // Feed paper and cut

                    // Format data agar sesuai dengan lebar printer 58mm
                    function formatForPrinter(text) {
                        const lines = text.split('\n');
                        const formattedLines = lines.map(line => {
                            // Jika line terlalu panjang, potong menjadi 32 karakter
                            if (line.length > MAX_CHARS_PER_LINE) {
                                return line.substring(0, MAX_CHARS_PER_LINE);
                            }
                            return line;
                        });
                        return formattedLines.join('\n');
                    }

                    // Mendapatkan baris-baris dari konten
                    var lines = receiptText.split('\n');
                    var formattedLines = lines.map(line => {
                        if (line.length > MAX_CHARS_PER_LINE) {
                            return line.substring(0, MAX_CHARS_PER_LINE);
                        }
                        return line;
                    });

                    // Gabungkan dengan kontrol yang sesuai - semua content center aligned
                    var finalPrintData = escposInit +
                                       setPaper58mm +
                                       setFontSmall +
                                       centerAlign +
                                       formattedLines.join('\n') +
                                       '\n\n' + // Extra line feeds before cutting
                                       feedAndCut;

                    // Kirim ke printer menggunakan protokol rawbt
                    window.location.href = 'rawbt:' + encodeURIComponent(finalPrintData);

                    // Tampilkan pesan sukses
                    printStatus.textContent = "Perintah mencetak telah dikirim ke printer!";
                    printStatus.className = "status success";
                    printStatus.classList.remove("hidden");
                } catch (e) {
                    console.error("Error sending to RAWBT:", e);

                    // Tampilkan pesan error
                    printStatus.textContent = "Terjadi kesalahan saat mencetak: " + e.message;
                    printStatus.className = "status error";
                    printStatus.classList.remove("hidden");
                }
            });

            // Handle tombol close
            closeButton.addEventListener('click', function() {
                window.close();
            });
        });
    </script>
</body>
</html>
