<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak QR Code Meja</title>
    <style>
        body { font-family: sans-serif; -webkit-print-color-adjust: exact; }
        .grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; padding: 20px; }
        .card { 
            border: 2px solid #333; 
            padding: 20px; 
            text-align: center; 
            border-radius: 15px; 
            page-break-inside: avoid; /* Agar tidak terpotong saat print */
        }
        .area-name { font-weight: bold; font-size: 1.2rem; color: #B1935B; margin-bottom: 5px; }
        .table-num { font-weight: 800; font-size: 2rem; color: #2F3D65; margin: 0; }
        .scan-text { font-size: 0.8rem; margin-top: 10px; color: #555; }
        
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body>

<div class="no-print" style="text-align: center; margin-bottom: 20px;">
    <h1>Generator QR Code Meja</h1>
    <button onclick="window.print()" style="padding: 10px 20px; font-size: 1.2rem; cursor: pointer;">Cetak Sekarang / Save PDF</button>
</div>

<div class="grid">
    @foreach($areas as $area)
        @for($i = 1; $i <= $totalMejaPerArea; $i++)
            <div class="card">
                {{-- Logo Toko (Opsional) --}}
                <div style="margin-bottom: 10px; font-weight:bold;">BAKSO GALA</div>

                {{-- Generate QR Code --}}
                {{-- URL: https://domainanda.com/scan/Nama-Area/1 --}}
                <div style="display: flex; justify-content: center;">
                    {!! QrCode::size(150)->generate(route('scan.qr', ['area' => $area, 'table' => $i])) !!}
                </div>

                <div style="margin-top: 15px;">
                    <div class="area-name">{{ $area }}</div>
                    <div class="table-num">MEJA {{ $i }}</div>
                </div>
                
                <div class="scan-text">Scan untuk pesan menu</div>
            </div>
        @endfor
    @endforeach
</div>

</body>
</html>