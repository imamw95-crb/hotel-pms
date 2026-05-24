<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Kwitansi - {{ $reservation->reservation_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Courier New', monospace; font-size: 12px; padding: 20px; max-width: 80mm; margin: 0 auto; }
        .header { text-align: center; border-bottom: 2px dashed #000; padding-bottom: 10px; margin-bottom: 10px; }
        .header h1 { font-size: 16px; font-weight: bold; }
        .header p { font-size: 10px; }
        .info { margin-bottom: 10px; }
        .info table { width: 100%; }
        .info td { padding: 2px 0; vertical-align: top; }
        .info td:first-child { width: 35%; }
        .divider { border-top: 1px dashed #000; margin: 8px 0; }
        .payment-table { width: 100%; border-collapse: collapse; margin: 8px 0; }
        .payment-table th, .payment-table td { padding: 3px 4px; text-align: left; border-bottom: 1px dotted #ccc; font-size: 11px; }
        .payment-table th { border-bottom: 1px solid #000; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .total-row { font-weight: bold; border-top: 1px solid #000; }
        .footer { text-align: center; margin-top: 15px; font-size: 10px; }
        .sign { margin-top: 20px; display: flex; justify-content: space-between; }
        .sign-box { text-align: center; width: 45%; }
        .sign-line { border-top: 1px solid #000; margin-top: 30px; padding-top: 5px; font-size: 10px; }
        @media print {
            body { padding: 5px; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="text-align:center; margin-bottom:15px;">
        <button onclick="window.print()" style="padding:8px 20px; background:#4CAF50; color:#fff; border:none; border-radius:4px; cursor:pointer; font-size:14px;">
            🖨️ Print Kwitansi
        </button>
        <a href="{{ route('reservations.show', $reservation) }}" style="padding:8px 20px; background:#666; color:#fff; text-decoration:none; border-radius:4px; font-size:14px; margin-left:5px;">Kembali</a>
    </div>

    <div class="header">
        <h1>HOTEL PMS</h1>
        <p>Jl. Contoh Alamat No. 123, Kota</p>
        <p>Telp: (021) 123-4567</p>
    </div>

    <div class="info">
        <table>
            <tr><td>No. Kwitansi</td><td>: {{ $reservation->reservation_number }}</td></tr>
            <tr><td>Tanggal</td><td>: {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}</td></tr>
            <tr><td>Nama Tamu</td><td>: {{ $reservation->guest->guest_name ?? '-' }}</td></tr>
            <tr><td>No. Kamar</td><td>: {{ $reservation->room->room_number ?? '-' }}</td></tr>
            <tr><td>Check-in</td><td>: {{ $reservation->check_in->format('d/m/Y') }}</td></tr>
            <tr><td>Check-out</td><td>: {{ $reservation->check_out->format('d/m/Y') }}</td></tr>
        </table>
    </div>

    <div class="divider"></div>

    <table class="payment-table">
        <thead>
            <tr>
                <th>Keterangan</th>
                <th class="text-right">Nominal</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Total Tagihan</td>
                <td class="text-right">Rp {{ number_format($reservation->total_amount, 0, ',', '.') }}</td>
            </tr>
            @if($reservation->paid_amount > 0)
                @foreach($transactions as $txn)
                <tr>
                    <td>{{ ucwords(str_replace('_', ' ', $txn->type)) }} ({{ strtoupper($txn->payment_method) }})</td>
                    <td class="text-right">Rp {{ number_format($txn->amount, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            @endif
            <tr class="total-row">
                <td>Total Dibayar</td>
                <td class="text-right">Rp {{ number_format($reservation->paid_amount, 0, ',', '.') }}</td>
            </tr>
            <tr class="total-row">
                <td>Sisa Bayar</td>
                <td class="text-right">Rp {{ number_format(max(0, $reservation->total_amount - $reservation->paid_amount), 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <div class="divider"></div>

    <div class="sign">
        <div class="sign-box">
            <div class="sign-line">Penerima</div>
        </div>
        <div class="sign-box">
            <div class="sign-line">Tamu</div>
        </div>
    </div>

    <div class="footer">
        <p>--- Terima Kasih ---</p>
        <p style="font-size:9px; margin-top:5px;">Kwitansi ini sah sebagai bukti pembayaran</p>
    </div>
</body>
</html>
