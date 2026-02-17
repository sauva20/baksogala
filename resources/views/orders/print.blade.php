<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk #{{ $order->id }} - Bakso Gala</title>
    <style>
        body {
            font-family: 'Courier New', Courier, monospace; /* Font struk */
            font-size: 12px;
            margin: 0;
            padding: 10px;
            width: 58mm; /* Standar lebar kertas thermal kecil */
            color: #000;
        }
        .header { text-align: center; margin-bottom: 10px; border-bottom: 1px dashed #000; padding-bottom: 5px; }
        .header h2 { margin: 0; font-size: 16px; font-weight: bold; }
        .header p { margin: 2px 0; font-size: 10px; }
        
        .info { margin-bottom: 10px; font-size: 11px; }
        .info div { display: flex; justify-content: space-between; }

        table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        td { vertical-align: top; }
        .qty { width: 25px; font-weight: bold; }
        .item { padding-right: 5px; }
        .price { text-align: right; white-space: nowrap; }
        
        .totals { border-top: 1px dashed #000; padding-top: 5px; }
        .totals div { display: flex; justify-content: space-between; margin-bottom: 2px; }
        .grand-total { font-weight: bold; font-size: 14px; margin-top: 5px; border-top: 1px solid #000; padding-top: 5px; }

        .footer { text-align: center; margin-top: 15px; font-size: 10px; }
        
        /* Hapus elemen browser saat print */
        @media print {
            @page { margin: 0; size: auto; }
            body { padding: 5px; }
        }
    </style>
</head>
<body onload="window.print()">

    <div class="header">
        <h2>BAKSO GALA</h2>
        <p>Jl. Raya Pagaden No. 123, Subang</p>
        <p>WA: 0812-3456-7890</p>
    </div>

    <div class="info">
        <div><span>No. Order:</span> <strong>#{{ $order->id }}</strong></div>
        <div><span>Tanggal:</span> <span>{{ $order->created_at->format('d/m/y H:i') }}</span></div>
        <div><span>Pelanggan:</span> <span>{{ Str::limit($order->customer_name, 15) }}</span></div>
        <div><span>Tipe:</span> <span>{{ $order->order_type }}</span></div>
        @if($order->order_type == 'Dine In')
            <div><span>Meja:</span> <strong>{{ Str::after($order->shipping_address, '-') }}</strong></div>
        @endif
    </div>

    <table>
        @php $subtotalHitung = 0; @endphp
        @foreach($order->orderDetails as $detail)
            @php $subtotalHitung += $detail->subtotal; @endphp
            <tr>
                <td class="qty">{{ $detail->quantity }}x</td>
                <td class="item">
                    {{ $detail->menuItem->name ?? 'Item Dihapus' }}
                    @if($detail->item_notes)
                        <br><i style="font-size:9px;">({{ $detail->item_notes }})</i>
                    @endif
                </td>
                <td class="price">{{ number_format($detail->subtotal, 0, ',', '.') }}</td>
            </tr>
        @endforeach
    </table>

    <div class="totals">
        <div>
            <span>Subtotal</span>
            <span>{{ number_format($subtotalHitung, 0, ',', '.') }}</span>
        </div>
        
        {{-- Hitung Biaya Layanan & Bungkus secara mundur --}}
        @php
            $grandTotal = $order->total_price;
            $appFee = ceil($grandTotal - ($grandTotal / 1.007)); // Estimasi balik app fee
            // Sisa selisih dimasukkan ke biaya bungkus (jika ada)
            $diff = $grandTotal - $subtotalHitung - $appFee;
        @endphp

        @if($diff > 100) {{-- Toleransi pembulatan --}}
            <div>
                <span>Biaya Kemasan</span>
                <span>{{ number_format($diff, 0, ',', '.') }}</span>
            </div>
        @endif

        <div>
            <span>Biaya Layanan (0.7%)</span>
            <span>{{ number_format($appFee, 0, ',', '.') }}</span>
        </div>

        <div class="grand-total">
            <span>TOTAL</span>
            <span>Rp {{ number_format($order->total_price, 0, ',', '.') }}</span>
        </div>
        
        <div style="margin-top: 5px; text-align:center;">
            Status: <strong>{{ strtoupper($order->payment_status) }}</strong>
        </div>
    </div>

    <div class="footer">
        <p>Terima Kasih atas Kunjungan Anda!</p>
        <p>Simpan struk ini sebagai bukti pembayaran.</p>
        <p>Password Wifi: <strong>gala123</strong></p>
    </div>

</body>
</html>