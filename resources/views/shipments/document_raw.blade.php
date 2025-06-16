<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice {{ $shipment->shipment_number ?? 'N/A' }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            font-size: 12pt;
            line-height: 1.4;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }
        .title {
            font-size: 18pt;
            font-weight: bold;
            margin: 10px 0;
            color: #333;
        }
        .document-number {
            font-size: 14pt;
            margin: 10px 0;
            color: #666;
        }
        .info-section {
            margin-bottom: 20px;
        }
        .info-row {
            display: flex;
            margin-bottom: 5px;
        }
        .info-label {
            width: 150px;
            font-weight: bold;
        }
        .info-value {
            flex: 1;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table, th, td {
            border: 1px solid #000;
        }
        th, td {
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .text-end {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .total-section {
            width: 100%;
            margin-bottom: 20px;
        }
        .total-table {
            width: 50%;
            margin-left: auto;
            border-collapse: collapse;
        }
        .total-table td {
            padding: 8px;
            border: 1px solid #000;
        }
        .total-table .total-label {
            background-color: #f9f9f9;
            font-weight: bold;
            width: 60%;
        }
        .total-table .total-value {
            text-align: right;
            width: 40%;
        }
        .grand-total-row {
            background-color: #e8f4f8 !important;
            font-weight: bold;
            font-size: 14pt;
        }
        .signature-section {
            margin-top: 40px;
            width: 100%;
            display: table;
        }
        .signature-box {
            display: table-cell;
            width: 50%;
            text-align: center;
            vertical-align: top;
            padding: 0 20px;
        }
        .signature-line {
            border-bottom: 1px solid #000;
            margin-top: 60px;
            margin-bottom: 10px;
            height: 1px;
            width: 80%;
            margin-left: auto;
            margin-right: auto;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">INVOICE</div>
        @php
            $invoiceNumber = str_replace('SHP-', 'INV-', $shipment->shipment_number ?? 'N/A');
        @endphp
        <div class="document-number">No. {{ $invoiceNumber }}</div>
    </div>

    <!-- Info Header -->
    <table style="width: 100%; margin-bottom: 20px; border: none;">
        <tr>
            <td style="width: 50%; border: none; vertical-align: top; padding: 0;">
                <div style="margin-bottom: 10px;">
                    <strong>Tanggal Invoice:</strong><br>
                    <span>{{ $shipment->date ? $shipment->date->format('d/m/Y') : '-' }}</span>
                </div>
                <div style="margin-bottom: 10px;">
                    <strong>No. Pesanan:</strong><br>
                    <span>{{ $shipment->storeOrder->order_number ?? 'N/A' }}</span>
                </div>
                <div style="margin-bottom: 10px;">
                    <strong>No. Pengiriman:</strong><br>
                    <span>{{ $shipment->shipment_number ?? 'N/A' }}</span>
                </div>
            </td>
            <td style="width: 50%; border: none; vertical-align: top; padding: 0;">
                <table style="width: 100%; border: none;">
                    <tr>
                        <td style="width: 30%; border: none; padding: 0; vertical-align: top;">
                            <div style="margin-bottom: 10px;">
                                <strong>Dari:</strong><br>
                                <span>
                                    <strong>Gudang Pusat</strong><br>
                                    PT. Nama Perusahaan<br>
                                    Jl. Alamat Pusat No. 123<br>
                                    Telp: (021) 123-4567
                                </span>
                            </div>
                        </td>
                        <td style="width: 70%; border: none; padding: 0; vertical-align: top;">
                            <div style="margin-bottom: 10px;">
                                <strong>Kepada:</strong><br>
                                <span>
                                    <strong>{{ $shipment->storeOrder->store->name ?? 'N/A' }}</strong><br>
                                    {{ $shipment->storeOrder->store->address ?? 'N/A' }}<br>
                                    Telp: {{ $shipment->storeOrder->store->phone ?? 'N/A' }}<br>
                                    @if($shipment->storeOrder->store->email)
                                        Email: {{ $shipment->storeOrder->store->email }}
                                    @endif
                                </span>
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- Tabel Detail Items -->
    <table>
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="40%">Nama Produk</th>
                <th width="10%">Satuan</th>
                <th width="10%">Jumlah</th>
                <th width="15%">Harga Satuan</th>
                <th width="20%">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @php $subtotalItems = 0; @endphp
            @forelse($shipment->shipmentDetails as $index => $detail)
            @php
                // Cari detail pesanan yang sesuai dengan produk dan unit ini
                $orderDetail = $shipment->storeOrder->storeOrderDetails->where('product_id', $detail->product_id)
                                ->where('unit_id', $detail->unit_id)->first();
                $price = $orderDetail ? $orderDetail->price : 0;
                $subtotal = $price * $detail->quantity;
                $subtotalItems += $subtotal;
            @endphp
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>
                    {{ $detail->product->name ?? 'N/A' }}
                    @if($detail->product->code)
                        <br><small style="color: #666;">Kode: {{ $detail->product->code }}</small>
                    @endif
                </td>
                <td class="text-center">{{ $detail->unit->name ?? 'N/A' }}</td>
                <td class="text-center">{{ number_format($detail->quantity, 0, ',', '.') }}</td>
                <td class="text-end">Rp {{ number_format($price, 0, ',', '.') }}</td>
                <td class="text-end">Rp {{ number_format($subtotal, 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center">Tidak ada item</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Total Section -->
    <div class="total-section">
        <table class="total-table">
            <tr>
                <td class="total-label">Subtotal Pesanan:</td>
                <td class="total-value">Rp {{ number_format($shipment->storeOrder->total_amount ?? $subtotalItems, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="total-label">Ongkos Kirim:</td>
                <td class="total-value">Rp {{ number_format($shipment->storeOrder->shipping_cost ?? 0, 0, ',', '.') }}</td>
            </tr>
            <tr class="grand-total-row">
                <td class="total-label">GRAND TOTAL:</td>
                <td class="total-value">Rp {{ number_format($shipment->storeOrder->grand_total ?? ($shipment->storeOrder->total_amount + $shipment->storeOrder->shipping_cost) ?? $subtotalItems, 0, ',', '.') }}</td>
            </tr>
        </table>
    </div>

    <!-- Info Section -->
    <div class="info-section">
        <div class="info-row">
            <div class="info-label">Metode Pembayaran:</div>
            <div class="info-value">
                @if(isset($shipment->storeOrder->payment_type))
                    @if($shipment->storeOrder->payment_type == 'cash')
                        <strong>Tunai</strong>
                    @elseif($shipment->storeOrder->payment_type == 'credit')
                        <strong>Kredit</strong> (Jatuh Tempo: {{ $shipment->storeOrder->due_date ? $shipment->storeOrder->due_date->format('d/m/Y') : '-' }})
                    @else
                        {{ ucfirst($shipment->storeOrder->payment_type) }}
                    @endif
                @else
                    -
                @endif
            </div>
        </div>
        @if($shipment->note)
        <div class="info-row">
            <div class="info-label">Catatan Pengiriman:</div>
            <div class="info-value">{{ $shipment->note }}</div>
        </div>
        @endif
        @if($shipment->storeOrder->shipping_cost > 0)
        <div class="info-row">
            <div class="info-label">Keterangan:</div>
            <div class="info-value">
                <em>Ongkos kirim sebesar Rp {{ number_format($shipment->storeOrder->shipping_cost, 0, ',', '.') }} telah termasuk dalam total pembayaran.</em>
            </div>
        </div>
        @endif
    </div>

    <!-- Signature Section -->
    <div class="signature-section">
        <div class="signature-box">
            <div><strong>Hormat kami,</strong></div>
            <div class="signature-line"></div>
            <div><strong>Gudang Pusat</strong></div>
            <div>{{ $shipment->createdBy->name ?? 'Admin' }}</div>
        </div>
        <div class="signature-box">
            <div><strong>Diterima oleh,</strong></div>
            <div class="signature-line"></div>
            <div><strong>{{ $shipment->storeOrder->store->name ?? 'Toko' }}</strong></div>
            <div>(...........................)</div>
        </div>
    </div>

    <!-- Footer Info -->
    <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #ccc; font-size: 10pt; color: #666; text-align: center;">
        <p>Invoice ini dicetak secara otomatis pada {{ now()->format('d/m/Y H:i:s') }}</p>
        <p><em>Terima kasih atas kepercayaan Anda</em></p>
    </div>

</body>
</html>
