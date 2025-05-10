<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Struk - {{ $sale->invoice_number }}</title>
</head>
<body>
    <div class="receipt-content" style="display:none">
{{ $sale->store->name }}
Restoran & Kafe
{{ $sale->store->address }}
Telp: {{ $sale->store->phone }}

==============================

No: {{ $sale->invoice_number }}
Tgl: {{ $sale->date->format('d/m/Y') }}
Jam: {{ $sale->date->format('H:i') }}
Kasir: {{ $sale->creator->name }}
@if($sale->customer_name)
Cust: {{ $sale->customer_name }}
@endif
Bayar: {{ ucfirst($sale->payment_type) }}

==============================

Qty Item           Harga
------------------------------
@foreach($sale->saleDetails as $detail)
{{ str_pad(intval($detail->quantity), 2, ' ', STR_PAD_LEFT) }} {{ str_pad(substr($detail->product->name, 0, 14), 14, ' ', STR_PAD_RIGHT) }} {{ str_pad(number_format($detail->subtotal, 0, ',', '.'), 8, ' ', STR_PAD_LEFT) }}
@endforeach

------------------------------

Sub {{ str_pad(number_format($sale->total_amount + $sale->discount - $sale->tax, 0, ',', '.'), 19, ' ', STR_PAD_LEFT) }}
@if($sale->discount > 0)
Dis {{ str_pad(number_format($sale->discount, 0, ',', '.'), 19, ' ', STR_PAD_LEFT) }}
@endif
@if($sale->tax > 0)
Tax {{ str_pad(number_format($sale->tax, 0, ',', '.'), 19, ' ', STR_PAD_LEFT) }}
@endif
------------------------------

TOTAL {{ str_pad(number_format($sale->total_amount, 0, ',', '.'), 18, ' ', STR_PAD_LEFT) }}

Tunai {{ str_pad(number_format($sale->total_payment, 0, ',', '.'), 18, ' ', STR_PAD_LEFT) }}
Kmbl  {{ str_pad(number_format($sale->change, 0, ',', '.'), 18, ' ', STR_PAD_LEFT) }}

==============================

Terima Kasih
Simpan struk sebagai bukti
pembayaran yang sah

www.namatoko.com
@namatoko
    </div>

    <script>
        window.onload = function() {
            setTimeout(function() {
                try {
                    var receiptText = document.querySelector('.receipt-content').innerText;
                    var escposInit = '\x1B@'; // Initialize printer
                    var setFontSmall = '\x1B\x21\x01'; // Set smaller font
                    var centerAlign = '\x1B\x61\x01'; // Center alignment
                    var leftAlign = '\x1B\x61\x00'; // Left alignment

                    // Menambahkan kontrol printer
                    var header = centerAlign;
                    var body = leftAlign;
                    var footer = centerAlign;

                    // Mendapatkan baris-baris dari konten
                    var lines = receiptText.split('\n');

                    // 5 baris pertama adalah header (centered)
                    // Sisanya adalah body (left-aligned)
                    // 5 baris terakhir adalah footer (centered)

                    var headerLines = lines.slice(0, 5).join('\n');
                    var bodyLines = lines.slice(5, lines.length - 5).join('\n');
                    var footerLines = lines.slice(lines.length - 5).join('\n');

                    // Gabungkan dengan kontrol yang sesuai
                    var finalPrintData = escposInit + setFontSmall +
                                        header + headerLines + '\n' +
                                        body + bodyLines + '\n' +
                                        footer + footerLines;

                    // Kirim ke printer
                    window.location.href = 'rawbt:' + encodeURIComponent(finalPrintData);
                } catch (e) {
                    console.error("Error sending to RAWBT:", e);
                    alert("Terjadi kesalahan saat mencetak: " + e.message);
                }
            }, 500);
        }
    </script>
</body>
</html>
