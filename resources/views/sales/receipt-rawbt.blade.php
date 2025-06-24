<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Struk - {{ $sale->invoice_number }}</title>
    <style>
        body, html {
            margin: 0;
            padding: 0;
            font-family: monospace;
            width: 100%;
        }
        .hidden {
            display: none;
        }
    </style>
</head>
<body>
    <div class="receipt-content hidden">
{{ str_pad($sale->store->name, 32, ' ', STR_PAD_BOTH) }}
{{ str_pad('Restoran & Kafe', 32, ' ', STR_PAD_BOTH) }}
{{ str_pad($sale->store->address, 32, ' ', STR_PAD_BOTH) }}
{{ str_pad('Telp: ' . $sale->store->phone, 32, ' ', STR_PAD_BOTH) }}

================================

{{ str_pad('No: ' . $sale->invoice_number, 32, ' ', STR_PAD_BOTH) }}
{{ $sale->dining_option_text }}
Kasir: {{ $sale->creator->name }}
Waktu: {{ $sale->date->format('d/m/Y H:i') }}

================================
Barang           Qty      Harga
================================
@foreach($sale->saleDetails as $detail)
@php
    $productName = substr($detail->product->name, 0, 12);
    $qty = intval($detail->quantity);
    $price = 'Rp ' . number_format($detail->subtotal, 0, ',', '.');

    // Format: Product (12) + Qty (3) + Price (15) = 32 chars
    $line = str_pad($productName, 12, ' ', STR_PAD_RIGHT) . ' ' .
            str_pad($qty, 3, ' ', STR_PAD_RIGHT) . ' ' .
            str_pad($price, 15, ' ', STR_PAD_LEFT);
@endphp
{{ $line }}
@endforeach
================================

@php
    $itemCount = $sale->saleDetails->count();
    $qtyTotal = $sale->saleDetails->sum('quantity');
    $subtotal = $sale->total_amount + $sale->discount - $sale->tax;
@endphp

{{ str_pad('Item: ' . $itemCount, 16, ' ', STR_PAD_RIGHT) . str_pad('Qty: ' . $qtyTotal, 16, ' ', STR_PAD_LEFT) }}

@if($sale->discount > 0)
{{ str_pad('Subtotal:', 16, ' ', STR_PAD_RIGHT) . str_pad('Rp ' . number_format($subtotal, 0, ',', '.'), 16, ' ', STR_PAD_LEFT) }}
{{ str_pad('Diskon:', 16, ' ', STR_PAD_RIGHT) . str_pad('Rp ' . number_format($sale->discount, 0, ',', '.'), 16, ' ', STR_PAD_LEFT) }}
@endif

@if($sale->tax > 0)
{{ str_pad('Pajak:', 16, ' ', STR_PAD_RIGHT) . str_pad('Rp ' . number_format($sale->tax, 0, ',', '.'), 16, ' ', STR_PAD_LEFT) }}
@endif

================================
{{ str_pad('TOTAL:', 16, ' ', STR_PAD_RIGHT) . str_pad('Rp ' . number_format($sale->total_amount, 0, ',', '.'), 16, ' ', STR_PAD_LEFT) }}
================================

@if($sale->payment_type === 'tunai')
{{ str_pad('Bayar:', 16, ' ', STR_PAD_RIGHT) . str_pad('Rp ' . number_format($sale->payment_amount ?? $sale->total_amount, 0, ',', '.'), 16, ' ', STR_PAD_LEFT) }}
{{ str_pad('Kembali:', 16, ' ', STR_PAD_RIGHT) . str_pad('Rp ' . number_format(($sale->payment_amount ?? $sale->total_amount) - $sale->total_amount, 0, ',', '.'), 16, ' ', STR_PAD_LEFT) }}

================================
@endif
{{ str_pad('Terima Kasih', 32, ' ', STR_PAD_BOTH) }}
{{ str_pad('Silahkan datang lagi!', 32, ' ', STR_PAD_BOTH) }}
    </div>

    <script>
        window.onload = function() {
            setTimeout(function() {
                try {
                    // ESC/POS commands untuk printer thermal 58mm
                    var ESC = '\x1B';
                    var escposInit = ESC + '@';               // Initialize printer
                    var setFontSmall = ESC + '\x21\x00';      // Normal font size
                    var setFontBold = ESC + '\x21\x08';       // Bold font
                    var centerAlign = ESC + 'a' + '\x01';     // Center alignment
                    var leftAlign = ESC + 'a' + '\x00';       // Left alignment
                    var feedAndCut = ESC + 'd' + '\x03' + ESC + 'i'; // Feed and cut
                    var lineFeed = '\x0A';                    // Line feed
                    var doubleFeed = '\x0A\x0A';             // Double line feed

                    // Get receipt content
                    var receiptText = document.querySelector('.receipt-content').innerText;
                    var lines = receiptText.trim().split('\n');

                    // Start with printer initialization
                    var printData = escposInit + setFontSmall + centerAlign;

                    // Process each line
                    for (var i = 0; i < lines.length; i++) {
                        var line = lines[i];

                        // Skip empty lines at the beginning/end
                        if (line.trim() === '' && (i === 0 || i === lines.length - 1)) {
                            continue;
                        }

                        // Add bold formatting for important lines
                        if (line.includes('TOTAL:') ||
                            line.includes(document.querySelector('.receipt-content').innerText.split('\n')[0].trim()) ||
                            line.includes('================================')) {
                            printData += setFontBold + line + lineFeed + setFontSmall;
                        }
                        // Double line feed after separators for better spacing
                        else if (line.includes('================================')) {
                            printData += line + doubleFeed;
                        }
                        // Regular content
                        else {
                            printData += line + lineFeed;
                        }
                    }

                    // Add final spacing and cut
                    printData += doubleFeed + feedAndCut;

                    // Send to printer via RAWBT
                    if (typeof Android !== 'undefined' && Android.print) {
                        // For Android app
                        Android.print(printData);
                    } else if (window.location.protocol === 'file:' || window.location.hostname === 'localhost') {
                        // For desktop testing - open print dialog
                        window.print();
                    } else {
                        // For RAWBT protocol
                        window.location.href = 'rawbt:' + encodeURIComponent(printData);
                    }

                    console.log("Print data sent successfully");

                    // Close window after printing (optional)
                    setTimeout(function() {
                        window.close();
                    }, 1000);

                } catch (e) {
                    console.error("Error sending to printer:", e);

                    // Fallback to browser print
                    document.querySelector('.receipt-content').classList.remove('hidden');
                    document.body.style.fontFamily = 'monospace';
                    document.body.style.fontSize = '12px';
                    document.body.style.lineHeight = '1.2';
                    document.body.style.whiteSpace = 'pre';
                    document.body.style.textAlign = 'center';
                    document.body.style.margin = '10px';

                    setTimeout(function() {
                        window.print();
                    }, 500);
                }
            }, 500);
        }

        // Handle print button if exists
        function printReceipt() {
            window.onload();
        }

        // Add print styles for browser printing
        var style = document.createElement('style');
        style.innerHTML = `
            @media print {
                body {
                    font-family: 'Courier New', monospace;
                    font-size: 10px;
                    line-height: 1.1;
                    margin: 0;
                    padding: 10px;
                    width: 58mm;
                    max-width: 58mm;
                }
                .receipt-content {
                    display: block !important;
                    white-space: pre;
                    text-align: left;
                    font-size: 10px;
                }
                @page {
                    size: 58mm auto;
                    margin: 0;
                }
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>
