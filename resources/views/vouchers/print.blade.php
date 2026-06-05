<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Voucher - HsRadius</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 20px;
            background: #f1f5f9;
        }

        .print-header {
            text-align: center;
            margin-bottom: 20px;
            padding: 15px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .print-header h3 {
            color: #1e293b;
            margin-bottom: 5px;
        }

        .print-header p {
            color: #64748b;
            font-size: 0.85rem;
        }

        .print-actions {
            text-align: center;
            margin-bottom: 20px;
        }

        .print-actions button {
            padding: 10px 30px;
            font-size: 14px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            margin: 0 5px;
        }

        .btn-print {
            background: #3b82f6;
            color: #fff;
        }

        .btn-print:hover {
            background: #2563eb;
        }

        .btn-close-page {
            background: #e2e8f0;
            color: #475569;
        }

        .btn-close-page:hover {
            background: #cbd5e1;
        }

        .voucher-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
        }

        .voucher-card {
            border: 2px dashed #94a3b8;
            border-radius: 10px;
            padding: 16px 12px;
            text-align: center;
            background: #fff;
            page-break-inside: avoid;
            position: relative;
        }

        .voucher-card .brand {
            font-size: 10px;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 2px;
            font-weight: 600;
            margin-bottom: 6px;
        }

        .voucher-card .package-name {
            font-size: 12px;
            color: #1e293b;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .voucher-card .code {
            font-size: 20px;
            font-weight: 700;
            letter-spacing: 3px;
            font-family: 'Courier New', Courier, monospace;
            color: #1e293b;
            margin: 10px 0;
            padding: 8px;
            background: #f8fafc;
            border-radius: 6px;
        }

        .voucher-card .validity {
            font-size: 11px;
            color: #64748b;
            margin-top: 6px;
        }

        .voucher-card .speed {
            font-size: 10px;
            color: #94a3b8;
            margin-top: 4px;
        }

        .voucher-card .qr-placeholder {
            width: 50px;
            height: 50px;
            margin: 8px auto 0;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 8px;
            color: #cbd5e1;
        }

        .voucher-card .price {
            font-size: 12px;
            color: #3b82f6;
            font-weight: 700;
            margin-top: 6px;
        }

        @media print {
            body {
                padding: 0;
                background: #fff;
            }

            .print-header,
            .print-actions {
                display: none !important;
            }

            .voucher-grid {
                gap: 6px;
            }

            .voucher-card {
                border-color: #000;
                box-shadow: none;
            }

            .voucher-card .code {
                background: #f0f0f0;
            }
        }

        @media print and (max-width: 210mm) {
            .voucher-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }
    </style>
</head>
<body>
    <div class="print-header">
        <h3>HsRadius - Cetak Voucher</h3>
        <p>Total: {{ count($vouchers ?? []) }} voucher</p>
    </div>

    <div class="print-actions">
        <button class="btn-print" onclick="window.print()">
            Cetak Voucher
        </button>
        <button class="btn-close-page" onclick="window.close()">
            Tutup
        </button>
    </div>

    <div class="voucher-grid">
        @foreach($vouchers ?? [] as $voucher)
            <div class="voucher-card">
                <div class="brand">HsRadius</div>
                <div class="package-name">{{ $voucher->package->name ?? 'Voucher Internet' }}</div>
                <div class="code">{{ $voucher->code }}</div>
                @if($voucher->package && $voucher->package->price)
                    <div class="price">Rp {{ number_format($voucher->package->price, 0, ',', '.') }}</div>
                @endif
                @if($voucher->validity_value)
                    <div class="validity">
                        Masa aktif: {{ $voucher->validity_value }}
                        @switch($voucher->validity_unit ?? '')
                            @case('minutes') Menit @break
                            @case('hours') Jam @break
                            @case('days') Hari @break
                            @case('months') Bulan @break
                            @default {{ $voucher->validity_unit ?? '' }}
                        @endswitch
                    </div>
                @endif
                @if($voucher->package)
                    <div class="speed">{{ $voucher->package->formatted_rate ?? '' }}</div>
                @endif
                <div class="qr-placeholder">QR</div>
            </div>
        @endforeach
    </div>
</body>
</html>
