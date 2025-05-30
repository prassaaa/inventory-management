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
{{ $sale->store->name }}
Restoran & Kafe
{{ $sale->store->address }}
Telp: {{ $sale->store->phone }}

==============================

{{ $sale->dining_option_text }}
Kasir: {{ $sale->creator->name }}
Waktu: {{ $sale->date->format('Y-m-d H:i') }}

Barang              Jmlh    Juml
==============================
@foreach($sale->saleDetails as $detail)
{{ str_pad(substr($detail->product->name, 0, 14), 15, ' ', STR_PAD_RIGHT) }} {{ str_pad(intval($detail->quantity), 3, ' ', STR_PAD_LEFT) }} {{ str_pad('Rp ' . number_format($detail->subtotal, 0, ',', '.'), 12, ' ', STR_PAD_LEFT) }}
@endforeach
==============================

Item: {{ $sale->saleDetails->count() }}    Subtotal: {{ str_pad('Rp ' . number_format($sale->total_amount + $sale->discount - $sale->tax, 0, ',', '.'), 15, ' ', STR_PAD_LEFT) }}
Jmlh: {{ $sale->saleDetails->sum('quantity') }}
==============================
Total:      {{ str_pad('Rp ' . number_format($sale->total_amount, 0, ',', '.'), 15, ' ', STR_PAD_LEFT) }}
==============================

Kata sandi wifi : Kiagenggiring2
Terima Kasih
Silahkan datang lagi!

Didukung oleh WnO POS
www.wnopos.com
    </div>

    <script>
        window.onload = function() {
            setTimeout(function() {
                try {
                    // ESC/POS commands
                    var ESC = '\x1B';
                    var escposInit = ESC + '@';               // Initialize printer
                    var setFontSmall = ESC + '\x21\x01';      // Set smaller font
                    var centerAlign = ESC + 'a' + '\x01';     // Center alignment
                    var leftAlign = ESC + 'a' + '\x00';       // Left alignment
                    var rightAlign = ESC + 'a' + '\x02';      // Right alignment
                    var feedAndCut = ESC + 'd' + '\x04' + ESC + 'i'; // Feed and cut
                    var lineFeed = '\x0A';                    // Line feed

                    // Get receipt content and split into lines
                    var receiptText = document.querySelector('.receipt-content').innerText;
                    var lines = receiptText.trim().split('\n');

                    // For 58mm receipt, maximum characters per line is ~32
                    // Paper width is usually 48mm, with printable area ~47mm
                    // Standard font is ~1.5mm per character (varies by printer)
                    var maxChars = 32;

                    // Start with printer initialization and font size
                    var printData = escposInit + setFontSmall;

                    // Process each line with proper alignment
                    for (var i = 0; i < lines.length; i++) {
                        var line = lines[i].trim();

                        // Case 1: Always center - Store information, separators, and footer
                        if (i <= 3 || // Store name, type, address, phone
                            line.includes('===============') ||
                            line.includes('--------------') ||
                            line.includes('Kata sandi wifi') ||
                            line.includes('Terima Kasih') ||
                            line.includes('Silahkan datang lagi') ||
                            line.includes('Didukung oleh') ||
                            line.includes('www.wnopos.com') ||
                            line === '') {
                            // For centered content, add padding if needed to ensure text is properly centered
                            if (line.length < maxChars) {
                                var padding = Math.floor((maxChars - line.length) / 2);
                                line = ' '.repeat(padding) + line;
                            }
                            printData += centerAlign + line + lineFeed;
                        }
                        // Case 2: Column headers - also centered
                        else if ((line.includes('Barang') && line.includes('Jmlh') && line.includes('Juml')) ||
                                 (line.includes('Item:') && line.includes('Subtotal:')) ||
                                 (line.includes('Jmlh:')) ||
                                 (line.includes('Total:'))) {
                            printData += leftAlign + line + lineFeed;
                        }
                        // Case 3: Dining option and staff info - left aligned but prominent
                        else if (line.includes('Makan di Tempat') ||
                                 line.includes('Dibawa Pulang') ||
                                 line.includes('Kasir:') ||
                                 line.includes('Waktu:')) {
                            printData += leftAlign + line + lineFeed;
                        }
                        // Case 4: Everything else - left aligned
                        else {
                            printData += leftAlign + line + lineFeed;
                        }
                    }

                    // Add line feeds and cut command
                    printData += lineFeed + lineFeed + feedAndCut;

                    // Send to printer
                    window.location.href = 'rawbt:' + encodeURIComponent(printData);

                    console.log("Print data sent successfully");
                } catch (e) {
                    console.error("Error sending to RAWBT:", e);
                    alert("Terjadi kesalahan saat mencetak: " + e.message);
                }
            }, 500);
        }
    </script>
</body>
</html>
