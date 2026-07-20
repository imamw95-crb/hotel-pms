<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Kwitansi Group - {{ $reservations->first()->reservation_number }} &lebih</title>
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

        /* Rooms Table */
        .rooms-table {
            width: 100%;
            border-collapse: collapse;
            margin: 8px 0;
            font-size: 10px;
        }
        .rooms-table th {
            border-top: 1px solid #000;
            border-bottom: 1px solid #000;
            padding: 3px 4px;
            text-align: left;
            font-weight: bold;
        }
        .rooms-table td {
            padding: 2px 4px;
            border-bottom: 1px dotted #ccc;
        }
        .rooms-table .right { text-align: right; }
        .rooms-table .center { text-align: center; }

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
            font-size: 10px;
        }
        .pay-table th {
            border-top: 1px solid #000;
            border-bottom: 1px solid #000;
            padding: 3px 4px;
            text-align: left;
            font-weight: bold;
        }
        .pay-table td {
            padding: 2px 4px;
            border-bottom: 1px dotted #ccc;
        }
        .pay-table .right { text-align: right; }
        .pay-table .center { text-align: center; }
        .pay-table tfoot td {
            border-top: 1px solid #000;
            border-bottom: 1px solid #000;
            font-weight: bold;
            padding: 4px 4px;
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
        <a href="{{ route('reservations.show', $reservations->first()) }}">← Kembali</a>
    </div>

    @php $hotel = \App\Models\HotelSetting::first(); @endphp

    <!-- Header -->
    <div class="header">
        @if($hotel->logo_path)
            <img src="{{ asset('storage/' . $hotel->logo_path) }}" alt="Logo" style="height:40px; margin-bottom:4px;">
        @endif
        <h1>{{ strtoupper($hotel->hotel_name ?? 'DYNAMIC PMS V.2') }}</h1>
        @if($hotel->address)<p>{{ $hotel->address }}</p>@endif
        @if($hotel->phone)<p>Telp: {{ $hotel->phone }}</p>@endif
        @if($hotel->website)<p>{{ $hotel->website }}</p>@endif
    </div>

    <div class="kwitansi-no">
        <strong>KWITANSI GROUP</strong> &nbsp; No. Group: {{ $reservations->first()->booking_group_id }} &nbsp; Tgl: {{ \Carbon\Carbon::now()->format('d/m/Y') }}
    </div>

    <!-- Info -->
    <div class="info-row">
        <div class="info-left">
            <table>
                <tr><td>Telah terima dari</td><td>: {{ $reservations->first()->guest->guest_name ?? '-' }}</td></tr>
                <tr><td>No. Identitas</td><td>: {{ $reservations->first()->guest->id_number ?? '-' }}</td></tr>
                <tr><td>Telepon</td><td>: {{ $reservations->first()->guest->phone ?? '-' }}</td></tr>
                <tr><td>Jumlah Kamar</td><td>: {{ $reservations->count() }} kamar</td></tr>
            </table>
        </div>
        <div class="info-right">
            <table>
                <tr><td>Check-in</td><td>: {{ $reservations->first()->check_in->format('d/m/Y') }}</td></tr>
                <tr><td>Check-out</td><td>: {{ $reservations->first()->check_out->format('d/m/Y') }}</td></tr>
                <tr><td>Durasi</td><td>: {{ $reservations->first()->nights }} malam</td></tr>
            </table>
        </div>
    </div>

    <div class="divider"></div>

    <!-- Rooms Table -->
    <table class="rooms-table">
        <thead>
            <tr>
                <th style="width:5%">No</th>
                <th style="width:25%">Reservasi</th>
                <th style="width:10%">Kamar</th>
                <th style="width:20%">Tamu</th>
                <th style="width:15%" class="right">Tagihan</th>
                <th style="width:15%" class="right">Dibayar</th>
                <th style="width:10%" class="right">Sisa</th>
            </tr>
        </thead>
        <tbody>
            @foreach($reservations as $idx => $res)
            @php $sisa = $res->total_amount - $res->paid_amount; @endphp
            <tr>
                <td class="center">{{ $idx + 1 }}</td>
                <td>{{ $res->reservation_number }}</td>
                <td>{{ $res->room->room_number ?? '-' }}</td>
                <td>{{ $res->guest->guest_name ?? '-' }}</td>
                <td class="right">Rp {{ number_format($res->total_amount, 0, ',', '.') }}</td>
                <td class="right">Rp {{ number_format($res->paid_amount, 0, ',', '.') }}</td>
                <td class="right">{{ $sisa > 0 ? 'Rp '.number_format($sisa, 0, ',', '.') : 'LUNAS' }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" class="right"><strong>TOTAL GROUP</strong></td>
                <td class="right"><strong>Rp {{ number_format($groupTotal, 0, ',', '.') }}</strong></td>
                <td class="right"><strong>Rp {{ number_format($groupPaid, 0, ',', '.') }}</strong></td>
                <td class="right"><strong>Rp {{ number_format(max(0, $groupTotal - $groupPaid), 0, ',', '.') }}</strong></td>
            </tr>
        </tfoot>
    </table>

    <div class="divider"></div>

    <!-- Payment Table -->
    <table class="pay-table">
        <thead>
            <tr>
                <th style="width:5%">No</th>
                <th style="width:18%">Reservasi</th>
                <th style="width:12%">Tipe</th>
                <th style="width:12%">Metode</th>
                <th style="width:28%">Keterangan</th>
                <th style="width:15%" class="right">Nominal</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transactions as $i => $txn)
            <tr>
                <td class="center">{{ $i + 1 }}</td>
                <td>{{ $txn->reservation->reservation_number ?? '-' }}</td>
                <td class="center">{{ strtoupper(str_replace('_', ' ', $txn->type)) }}</td>
                <td class="center">{{ ucwords(str_replace('_', ' ', $txn->payment_method)) }}</td>
                <td>{{ $txn->transaction_number }}</td>
                <td class="right">Rp {{ number_format($txn->amount, 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="center" style="padding:10px; color:#999;">Belum ada pembayaran</td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5" class="right">TOTAL DIBAYAR</td>
                <td class="right">Rp {{ number_format($transactions->sum('amount'), 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="divider"></div>

    <!-- Terbilang -->
    <div style="margin: 8px 0; padding: 6px 8px; border: 1px solid #000; font-size: 11px; font-style: italic;">
        <strong>Terbilang:</strong> {{ terbilang($groupPaid) }} Rupiah
    </div>

    <!-- Summary -->
    <div class="summary-line">
        <span>TOTAL TAGIHAN GROUP: Rp {{ number_format($groupTotal, 0, ',', '.') }}</span>
        <span>STATUS: @if($groupPaid >= $groupTotal) LUNAS @elseif($groupPaid > 0) DP @else BELUM BAYAR @endif</span>
    </div>

    <!-- Sign -->
    <div class="sign-row">
        <div class="sign-box">
            <div class="line">Penerima</div>
            <div class="role">Petugas</div>
        </div>
        <div class="sign-box">
            <div class="line">{{ $reservations->first()->createdBy->name ?? 'Admin' }}</div>
            <div class="role">Dibuat Oleh</div>
        </div>
        <div class="sign-box">
            <div class="line">{{ $reservations->first()->guest->guest_name ?? '-' }}</div>
            <div class="role">Pembayar</div>
        </div>
    </div>

    <div class="footer">
        <p>{{ $hotel->hotel_name ?? 'Dynamic PMS v2' }} — Terima kasih atas kunjungan Anda</p>
    </div>

    <script>
        window.onload = function() { window.print(); }
    </script>
</body>
</html>
