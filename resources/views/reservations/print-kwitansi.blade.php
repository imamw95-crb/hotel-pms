<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Kwitansi - {{ $reservation->reservation_number }}</title>
    <style>
        @page { size: A5 landscape; margin: 10mm 12mm; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 11px;
            color: #000;
            background: #fff;
            padding: 10px 15px;
            line-height: 1.4;
        }

        /* Header */
        .header {
            text-align: center;
            border-bottom: 1px solid #000;
            padding-bottom: 8px;
            margin-bottom: 10px;
        }
        .header h1 {
            font-size: 16px;
            font-weight: bold;
            letter-spacing: 2px;
            text-transform: uppercase;
        }
        .header p {
            font-size: 10px;
            margin: 1px 0;
        }
        .kwitansi-no {
            text-align: right;
            font-size: 10px;
            margin-bottom: 8px;
        }

        /* Info Row */
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        .info-left, .info-right {
            width: 48%;
        }
        .info-row table { width: 100%; }
        .info-row td {
            padding: 1px 0;
            vertical-align: top;
        }
        .info-row td:first-child {
            width: 90px;
            white-space: nowrap;
        }

        /* Divider */
        .divider {
            border-top: 1px dashed #000;
            margin: 8px 0;
        }

        /* Payment Table */
        .pay-table {
            width: 100%;
            border-collapse: collapse;
            margin: 8px 0;
            font-size: 11px;
        }
        .pay-table th {
            border-top: 1px solid #000;
            border-bottom: 1px solid #000;
            padding: 4px 6px;
            text-align: left;
            font-weight: bold;
        }
        .pay-table td {
            padding: 3px 6px;
            border-bottom: 1px dotted #ccc;
        }
        .pay-table .right { text-align: right; }
        .pay-table .center { text-align: center; }
        .pay-table tfoot td {
            border-top: 1px solid #000;
            border-bottom: 1px solid #000;
            font-weight: bold;
            padding: 5px 6px;
        }

        /* Summary Line */
        .summary-line {
            display: flex;
            justify-content: space-between;
            margin: 10px 0;
            padding: 6px 0;
            border-top: 1px solid #000;
            border-bottom: 1px solid #000;
            font-weight: bold;
            font-size: 12px;
        }

        /* Sign */
        .sign-row {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }
        .sign-box {
            text-align: center;
            width: 30%;
        }
        .sign-box .line {
            border-top: 1px solid #000;
            margin-top: 35px;
            padding-top: 4px;
            font-size: 10px;
        }
        .sign-box .role {
            font-size: 9px;
            color: #666;
        }

        /* Footer */
        .footer {
            text-align: center;
            margin-top: 12px;
            font-size: 9px;
            color: #666;
        }

        /* Print Button */
        .no-print { text-align: center; margin-bottom: 12px; }
        .no-print button, .no-print a {
            padding: 6px 18px;
            border: 1px solid #333;
            border-radius: 3px;
            cursor: pointer;
            font-size: 12px;
            margin: 0 3px;
            text-decoration: none;
            display: inline-block;
            background: #f0f0f0;
            color: #333;
        }
        .no-print button:hover, .no-print a:hover { background: #e0e0e0; }

        @media print {
            body { padding: 5px 8px; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>

    <div class="no-print">
        <button onclick="window.print()">🖨️ Print</button>
        <a href="{{ route('reservations.show', $reservation) }}">← Kembali</a>
    </div>

    <!-- Header -->
    <div class="header">
        @php $hotel = \App\Models\HotelSetting::get(); @endphp
        @if($hotel->logo_path)
            <img src="{{ asset('storage/' . $hotel->logo_path) }}" alt="Logo" style="height:40px; margin-bottom:4px;">
        @endif
        <h1>{{ strtoupper($hotel->hotel_name ?? 'DYNAMIC PMS V.2') }}</h1>
        @if($hotel->address)<p>{{ $hotel->address }}</p>@endif
        @if($hotel->phone)<p>Telp: {{ $hotel->phone }}</p>@endif
        @if($hotel->website)<p>{{ $hotel->website }}</p>@endif
    </div>

    <div class="kwitansi-no">
        <strong>KWITANSI</strong> &nbsp; No: {{ $reservation->reservation_number }} &nbsp; Tgl: {{ \Carbon\Carbon::now()->format('d/m/Y') }}
    </div>

    <!-- Info -->
    <div class="info-row">
        <div class="info-left">
            <table>
                <tr><td>Telah terima dari</td><td>: {{ $reservation->guest->guest_name ?? '-' }}</td></tr>
                <tr><td>No. Identitas</td><td>: {{ $reservation->guest->id_number ?? '-' }}</td></tr>
                <tr><td>Telepon</td><td>: {{ $reservation->guest->phone ?? '-' }}</td></tr>
            </table>
        </div>
        <div class="info-right">
            <table>
                <tr><td>Tipe Kamar</td><td>: {{ $reservation->room->roomType->name ?? $reservation->room->room_type_name ?? '-' }}</td></tr>
                <tr><td>Check-in</td><td>: {{ $reservation->check_in->format('d/m/Y') }}</td></tr>
                <tr><td>Check-out</td><td>: {{ $reservation->check_out->format('d/m/Y') }}</td></tr>
            </table>
        </div>
    </div>

    <div class="divider"></div>

    <!-- Payment Table -->
    <table class="pay-table">
        <thead>
            <tr>
                <th style="width:5%">No</th>
                <th style="width:15%">Tipe</th>
                <th style="width:15%">Metode</th>
                <th>Keterangan</th>
                <th style="width:20%" class="right">Nominal</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transactions as $i => $txn)
            <tr>
                <td class="center">{{ $i + 1 }}</td>
                <td class="center">{{ strtoupper(str_replace('_', ' ', $txn->type)) }}</td>
                <td class="center">{{ ucwords(str_replace('_', ' ', $txn->payment_method)) }}</td>
                <td>{{ $txn->transaction_number }}</td>
                <td class="right">Rp {{ number_format($txn->amount, 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="center" style="padding:10px; color:#999;">Belum ada pembayaran</td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" class="right">TOTAL DIBAYAR</td>
                <td class="right">Rp {{ number_format($reservation->paid_amount, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td colspan="4" class="right">SISA BAYAR</td>
                <td class="right">Rp {{ number_format(max(0, $reservation->total_amount - $reservation->paid_amount), 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="divider"></div>

    <!-- Terbilang -->
    <div style="margin: 8px 0; padding: 6px 8px; border: 1px solid #000; font-size: 11px; font-style: italic;">
        <strong>Terbilang:</strong> {{ terbilang($reservation->paid_amount) }} Rupiah
    </div>

    <!-- Summary -->
    <div class="summary-line">
        <span>TOTAL TAGIHAN: Rp {{ number_format($reservation->total_amount, 0, ',', '.') }}</span>
        <span>STATUS: @if($reservation->paid_amount >= $reservation->total_amount) LUNAS @elseif($reservation->paid_amount > 0) DP @else BELUM BAYAR @endif</span>
    </div>

    <!-- Sign -->
    <div class="sign-row">
        <div class="sign-box">
            <div class="line">Penerima</div>
            <div class="role">Petugas</div>
        </div>
        <div class="sign-box">
            <div class="line">{{ $reservation->createdBy->name ?? 'Admin' }}</div>
            <div class="role">Dibuat Oleh</div>
        </div>
        <div class="sign-box">
            <div class="line">{{ $reservation->guest->guest_name ?? 'Tamu' }}</div>
            <div class="role">Tamu</div>
        </div>
    </div>

    <div class="footer">
        --- Kwitansi ini sah sebagai bukti pembayaran ---
    </div>

</body>
</html>
