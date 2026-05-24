<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Kwitansi - {{ $reservation->reservation_number }}</title>
    <style>
        @page { size: A5 portrait; margin: 12mm; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            font-size: 13px;
            color: #333;
            background: #fff;
            padding: 15px;
        }

        /* Header */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 3px double #1a365d;
            padding-bottom: 12px;
            margin-bottom: 15px;
        }
        .hotel-logo h1 {
            font-size: 22px;
            color: #1a365d;
            letter-spacing: 2px;
        }
        .hotel-logo p {
            font-size: 11px;
            color: #666;
            margin: 1px 0;
        }
        .kwitansi-title {
            text-align: right;
        }
        .kwitansi-title h2 {
            font-size: 18px;
            color: #c53030;
            letter-spacing: 3px;
        }
        .kwitansi-title p {
            font-size: 11px;
            color: #666;
        }

        /* Info Section */
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 15px;
        }
        .info-box {
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 10px;
        }
        .info-box h3 {
            font-size: 11px;
            color: #1a365d;
            text-transform: uppercase;
            border-bottom: 1px solid #eee;
            padding-bottom: 4px;
            margin-bottom: 6px;
        }
        .info-box table { width: 100%; font-size: 12px; }
        .info-box td { padding: 2px 0; }
        .info-box td:first-child { color: #888; width: 40%; }

        /* Payment Table */
        .payment-section { margin-bottom: 15px; }
        .payment-section h3 {
            font-size: 12px;
            color: #1a365d;
            text-transform: uppercase;
            border-bottom: 2px solid #1a365d;
            padding-bottom: 4px;
            margin-bottom: 8px;
        }
        .payment-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }
        .payment-table th {
            background: #1a365d;
            color: #fff;
            padding: 6px 10px;
            text-align: left;
            font-weight: 600;
        }
        .payment-table td {
            padding: 5px 10px;
            border-bottom: 1px solid #eee;
        }
        .payment-table .text-right { text-align: right; }
        .payment-table .text-center { text-align: center; }
        .payment-table tfoot td {
            border-top: 2px solid #1a365d;
            border-bottom: none;
            font-weight: bold;
            padding: 8px 10px;
        }
        .payment-table .grand-total td {
            background: #f0f7ff;
            font-size: 14px;
            color: #1a365d;
        }

        /* Summary Box */
        .summary-box {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            margin-bottom: 15px;
        }
        .summary-item {
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 10px;
            text-align: center;
        }
        .summary-item .label {
            font-size: 10px;
            color: #888;
            text-transform: uppercase;
        }
        .summary-item .value {
            font-size: 16px;
            font-weight: bold;
            margin-top: 4px;
        }
        .summary-item.total .value { color: #1a365d; }
        .summary-item.paid .value { color: #16a34a; }
        .summary-item.sisa .value { color: #dc2626; }

        /* Sign Section */
        .sign-section {
            display: flex;
            justify-content: space-between;
            margin-top: 25px;
            padding-top: 15px;
            border-top: 1px dashed #ccc;
        }
        .sign-box {
            text-align: center;
            width: 30%;
        }
        .sign-box .sign-line {
            border-top: 1px solid #333;
            margin-top: 45px;
            padding-top: 5px;
            font-size: 11px;
        }
        .sign-box .role {
            font-size: 10px;
            color: #888;
            margin-top: 2px;
        }

        /* Footer */
        .footer {
            text-align: center;
            margin-top: 15px;
            padding-top: 10px;
            border-top: 1px dashed #ccc;
            font-size: 10px;
            color: #999;
        }

        /* Print Button */
        .no-print { text-align: center; margin-bottom: 15px; }
        .no-print button, .no-print a {
            padding: 8px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 13px;
            margin: 0 3px;
            text-decoration: none;
            display: inline-block;
        }
        .btn-print { background: #1a365d; color: #fff; }
        .btn-back { background: #666; color: #fff; }

        @media print {
            body { padding: 0; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>

    <!-- Print Buttons -->
    <div class="no-print">
        <button onclick="window.print()" class="btn-print">🖨️ Print Kwitansi</button>
        <a href="{{ route('reservations.show', $reservation) }}" class="btn-back">← Kembali</a>
    </div>

    <!-- Header -->
    <div class="header">
        <div class="hotel-logo">
            <h1>HOTEL PMS</h1>
            <p>Jl. Contoh Alamat No. 123, Kota</p>
            <p>Telp: (021) 123-4567 | Email: info@hotelpms.com</p>
        </div>
        <div class="kwitansi-title">
            <h2>KWITANSI</h2>
            <p>No: {{ $reservation->reservation_number }}</p>
            <p>Tanggal: {{ \Carbon\Carbon::now()->format('d F Y H:i') }}</p>
        </div>
    </div>

    <!-- Info Grid -->
    <div class="info-grid">
        <div class="info-box">
            <h3>Info Tamu</h3>
            <table>
                <tr><td>Nama</td><td>: {{ $reservation->guest->guest_name ?? '-' }}</td></tr>
                <tr><td>No. Identitas</td><td>: {{ $reservation->guest->id_number ?? '-' }}</td></tr>
                <tr><td>Telepon</td><td>: {{ $reservation->guest->phone ?? '-' }}</td></tr>
                <tr><td>Alamat</td><td>: {{ $reservation->guest->address ?? '-' }}</td></tr>
            </table>
        </div>
        <div class="info-box">
            <h3>Info Kamar</h3>
            <table>
                <tr><td>No. Kamar</td><td>: {{ $reservation->room->room_number ?? '-' }}</td></tr>
                <tr><td>Tipe Kamar</td><td>: {{ $reservation->room->room_type_name ?? '-' }}</td></tr>
                <tr><td>Check-in</td><td>: {{ $reservation->check_in->format('d/m/Y') }}</td></tr>
                <tr><td>Check-out</td><td>: {{ $reservation->check_out->format('d/m/Y') }}</td></tr>
            </table>
        </div>
    </div>

    <!-- Summary -->
    <div class="summary-box">
        <div class="summary-item total">
            <div class="label">Total Tagihan</div>
            <div class="value">Rp {{ number_format($reservation->total_amount, 0, ',', '.') }}</div>
        </div>
        <div class="summary-item paid">
            <div class="label">Total Dibayar</div>
            <div class="value">Rp {{ number_format($reservation->paid_amount, 0, ',', '.') }}</div>
        </div>
        <div class="summary-item sisa">
            <div class="label">Sisa Bayar</div>
            <div class="value">Rp {{ number_format(max(0, $reservation->total_amount - $reservation->paid_amount), 0, ',', '.') }}</div>
        </div>
    </div>

    <!-- Payment History -->
    @if($transactions->count() > 0)
    <div class="payment-section">
        <h3>Detail Pembayaran</h3>
        <table class="payment-table">
            <thead>
                <tr>
                    <th>No. Transaksi</th>
                    <th>Tanggal</th>
                    <th>Tipe</th>
                    <th>Metode</th>
                    <th class="text-right">Nominal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($transactions as $txn)
                <tr>
                    <td>{{ $txn->transaction_number }}</td>
                    <td>{{ $txn->created_at->format('d/m/Y H:i') }}</td>
                    <td class="text-center">
                        <span style="padding:2px 8px; border-radius:3px; font-size:10px; font-weight:bold;
                            @if($txn->type === 'dp') background:#dbeafe; color:#1e40af;
                            @elseif($txn->type === 'pelunasan') background:#dcfce7; color:#166534;
                            @elseif($txn->type === 'checkin_payment') background:#f3e8ff; color:#6b21a8;
                            @else background:#f3f4f6; color:#374151; @endif">
                            {{ strtoupper(str_replace('_', ' ', $txn->type)) }}
                        </span>
                    </td>
                    <td class="text-center">{{ ucwords(str_replace('_', ' ', $txn->payment_method)) }}</td>
                    <td class="text-right">Rp {{ number_format($txn->amount, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="4" class="text-right">Total Dibayar</td>
                    <td class="text-right">Rp {{ number_format($reservation->paid_amount, 0, ',', '.') }}</td>
                </tr>
                <tr class="grand-total">
                    <td colspan="4" class="text-right">SISA BAYAR</td>
                    <td class="text-right">Rp {{ number_format(max(0, $reservation->total_amount - $reservation->paid_amount), 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
    @endif

    <!-- Sign -->
    <div class="sign-section">
        <div class="sign-box">
            <div class="sign-line">Penerima</div>
            <div class="role">Petugas</div>
        </div>
        <div class="sign-box">
            <div class="sign-line">Dibuat Oleh</div>
            <div class="role">{{ $reservation->createdBy->name ?? '-' }}</div>
        </div>
        <div class="sign-box">
            <div class="sign-line">Tamu</div>
            <div class="role">{{ $reservation->guest->guest_name ?? '-' }}</div>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p><strong>Kwitansi ini sah sebagai bukti pembayaran</strong></p>
        <p>Hotel PMS &copy; {{ date('Y') }} | Terima kasih atas kunjungan Anda</p>
    </div>

</body>
</html>
