<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk #{{ $order->id }} - Bakso Gala</title>
    <style>
        @page { margin: 0; size: 58mm auto; } /* Setting ukuran kertas */
        
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 10px; /* Ukuran standar struk */
            margin: 0;
            padding: 5px 2px;
            width: 58mm; /* Lebar kertas 58mm */
            color: #000;
            line-height: 1.2;
        }

        .center { text-align: center; }
        .right { text-align: right; }
        .bold { font-weight: bold; }
        .uppercase { text-transform: uppercase; }

        /* Logo Styling */
        .logo {
            width: 50px;
            height: auto;
            margin: 0 auto 5px auto;
            display: block;
            filter: grayscale(100%) contrast(150%); /* Agar tajam saat diprint B/W */
        }

        /* Garis Pemisah */
        .separator {
            border-bottom: 1px dashed #000;
            margin: 5px 0;
            display: block;
            width: 100%;
        }
        .separator-double {
            border-bottom: 2px double #000;
            margin: 5px 0;
        }

        /* Header Info */
        .header h2 { margin: 0; font-size: 14px; }
        .header p { margin: 2px 0; font-size: 9px; }

        /* Detail Order */
        .info { font-size: 9px; margin-bottom: 5px; }
        .info-row { display: flex; justify-content: space-between; }

        /* Tabel Item */
        table { width: 100%; border-collapse: collapse; margin-top: 5px; }
        td { vertical-align: top; padding: 2px 0; }
        
        .col-qty { width: 15%; text-align: left; }
        .col-item { width: 60%; text-align: left; padding-right: 5px; }
        .col-price { width: 25%; text-align: right; }

        /* Total Section */
        .totals { margin-top: 5px; }
        .totals-row { display: flex; justify-content: space-between; margin-bottom: 2px; }
        
        .grand-total { 
            font-size: 14px; 
            font-weight: 800; 
            margin-top: 5px; 
            padding-top: 5px; 
            border-top: 1px dashed #000;
        }

        /* Footer */
        .footer { margin-top: 10px; font-size: 9px; }
        .wifi-box { 
            border: 1px solid #000; 
            padding: 3px; 
            margin: 5px auto; 
            display: inline-block; 
            font-weight: bold; 
        }

        /* Hide elements on print */
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body onload="window.print()">

    <div class="header center">
        <img src="{{ asset('assets/images/GALA.png') }}" class="logo" alt="Logo">
        <h2 class="uppercase">BAKSO GALA</h2>
        <p>Jl. Otto Iskandardinata No.115, Karanganyar, Kec. Subang, Kabupaten Subang, Jawa Barat 41211</p>
        <p>+62 881-0816-31531</p>
    </div>

    <div class="separator-double"></div>

    <div class="info">
        <div class="info-row">
            <span>No: #{{ $order->id }}</span>
            <span>{{ $order->created_at->format('d/m/y H:i') }}</span>
        </div>
        <div class="info-row">
            <span>Cust: {{ Str::limit($order->customer_name, 12) }}</span>
            <span class="bold">{{ strtoupper($order->order_type) }}</span>
        </div>
        @if($order->order_type == 'Dine In')
        <div class="info-row">
            <span>Meja:</span>
            <span class="bold">{{ Str::after($order->shipping_address, '-') }}</span>
        </div>
        @endif
    </div>

    <div class="separator"></div>

    <table>
        @php $subtotalHitung = 0; @endphp
        @foreach($order->orderDetails as $detail)
            @php $subtotalHitung += $detail->subtotal; @endphp
            <tr>
                <td class="col-qty bold">{{ $detail->quantity }}x</td>
                <td class="col-item">
                    {{ $detail->menuItem->name ?? 'Item Dihapus' }}
                    @if($detail->item_notes)
                        <div style="font-size: 8px; font-style: italic; color: #333;">
                            ({{ $detail->item_notes }})
                        </div>
                    @endif
                </td>
                <td class="col-price">{{ number_format($detail->subtotal, 0, ',', '.') }}</td>
            </tr>
        @endforeach
    </table>

    <div class="separator"></div>

    <div class="totals">
        <div class="totals-row">
            <span>Subtotal</span>
            <span>{{ number_format($subtotalHitung, 0, ',', '.') }}</span>
        </div>

        {{-- Logika Hitung Mundur Biaya --}}
        @php
            $grandTotal = $order->total_price;
            // Estimasi App Fee (0.7%)
            $appFee = ceil($grandTotal - ($grandTotal / 1.007)); 
            // Sisanya adalah Packaging (jika ada)
            $diff = $grandTotal - $subtotalHitung - $appFee;
        @endphp

        @if($diff > 100)
            <div class="totals-row">
                <span>Biaya Kemasan</span>
                <span>{{ number_format($diff, 0, ',', '.') }}</span>
            </div>
        @endif

        <div class="totals-row">
            <span>Layanan (0.7%)</span>
            <span>{{ number_format($appFee, 0, ',', '.') }}</span>
        </div>

        <div class="totals-row grand-total">
            <span>TOTAL</span>
            <span>Rp {{ number_format($grandTotal, 0, ',', '.') }}</span>
        </div>

        <div class="totals-row" style="margin-top: 5px;">
            <span>Pembayaran:</span>
            <span class="bold uppercase">{{ $order->payment_method }}</span>
        </div>
        <div class="totals-row">
            <span>Status:</span>
            <span class="bold uppercase">{{ $order->payment_status }}</span>
        </div>
    </div>

    <div class="separator"></div>

    <div class="footer center">
        <p>Terima Kasih atas kunjungan Anda!</p>

        
        <p style="margin-top: 5px;">*Simpan struk ini sebagai bukti*</p>
        <p>-- Layanan Konsumen --<br>+62 881-0816-31531</p>
    </div>

</body>
</html>