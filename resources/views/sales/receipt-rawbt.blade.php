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

        ================================

        No. Invoice: {{ $sale->invoice_number }}
        Tanggal: {{ $sale->date->format('d/m/Y') }}
        Waktu: {{ $sale->date->format('H:i') }}
        Kasir: {{ $sale->creator->name }}
        @if($sale->customer_name)
        Pelanggan: {{ $sale->customer_name }}
        @endif
        Pembayaran: {{ ucfirst($sale->payment_type) }}

        ================================

        Qty  Item                 Harga
        --------------------------------
        @foreach($sale->saleDetails as $detail)
        {{ str_pad(intval($detail->quantity), 4, ' ', STR_PAD_LEFT) }} {{ str_pad($detail->product->name, 20, ' ', STR_PAD_RIGHT) }} {{ str_pad('Rp ' . number_format($detail->subtotal, 0, ',', '.'), 10, ' ', STR_PAD_LEFT) }}
        @endforeach

        --------------------------------

        Subtotal {{ str_pad('Rp ' . number_format($sale->total_amount + $sale->discount - $sale->tax, 0, ',', '.'), 20, ' ', STR_PAD_LEFT) }}
        @if($sale->discount > 0)
        Diskon   {{ str_pad('Rp ' . number_format($sale->discount, 0, ',', '.'), 20, ' ', STR_PAD_LEFT) }}
        @endif
        @if($sale->tax > 0)
        Pajak    {{ str_pad('Rp ' . number_format($sale->tax, 0, ',', '.'), 20, ' ', STR_PAD_LEFT) }}
        @endif
        --------------------------------

        TOTAL    {{ str_pad('Rp ' . number_format($sale->total_amount, 0, ',', '.'), 20, ' ', STR_PAD_LEFT) }}

        Tunai    {{ str_pad('Rp ' . number_format($sale->total_payment, 0, ',', '.'), 20, ' ', STR_PAD_LEFT) }}
        Kembali  {{ str_pad('Rp ' . number_format($sale->change, 0, ',', '.'), 20, ' ', STR_PAD_LEFT) }}

        ================================

        Terima Kasih atas Kunjungan Anda

        Simpan struk ini sebagai bukti pembayaran
        yang sah

        www.namatoko.com | @namatoko
    </div>

    <script>
        window.onload = function() {
            // Tunggu sebentar untuk memastikan halaman dirender dengan baik
            setTimeout(function() {
                try {
                    // Mendapatkan teks struk
                    var receiptText = document.querySelector('.receipt-content').innerText;

                    // Tambahkan perintah ESC/POS untuk inisialisasi printer
                    var escposInit = '\x1B@'; // Initialize printer

                    // Gabungkan perintah dengan teks struk
                    var printData = escposInit + receiptText;

                    // Kirim langsung sebagai teks (tanpa encoding base64)
                    window.location.href = 'rawbt:' + encodeURIComponent(printData);
                } catch (e) {
                    console.error("Error sending to RAWBT:", e);
                    alert("Terjadi kesalahan saat mencetak: " + e.message);
                }
            }, 500);
        }
    </script>
</body>
</html>
