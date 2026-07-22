<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Group Invoice - {{ $reservations->first()->reservation_number }} &lebih</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 12px; padding: 30px; max-width: 210mm; margin: 0 auto; }
        .header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 15px; }
        .hotel-info h1 { font-size: 24px; color: #1a365d; }
        .hotel-info p { font-size: 11px; color: #666; margin: 2px 0; }
        .invoice-info { text-align: right; }
        .invoice-info h2 { font-size: 18px; color: #c53030; }
        .invoice-info p { font-size: 11px; margin: 2px 0; }
        .details { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px; }
        .details-box { border: 1px solid #ddd; padding: 10px; border-radius: 4px; }
        .details-box h3 { font-size: 12px; color: #1a365d; border-bottom: 1px solid #eee; padding-bottom: 5px; margin-bottom: 8px; }
        .details-box table { width: 100%; font-size: 11px; }
        .details-box td { padding: 2px 0; }
        .details-box td:first-child { color: #666; width: 40%; }
        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        .items-table th { background: #1a365d; color: #fff; padding: 8px; text-align: left; font-size: 11px; }
        .items-table td { padding: 6px 8px; border-bottom: 1px solid #eee; font-size: 11px; }
        .items-table .text-right { text-align: right; }
        .items-table .room-row { background: #f8f9ff; }
        .summary { width: 200px; margin-left: auto; margin-bottom: 20px; }
        .summary table { width: 100%; font-size: 12px; }
        .summary td { padding: 4px 8px; }
        .summary td:first-child { text-align: right; color: #666; }
        .summary td:last-child { text-align: right; font-weight: bold; }
        .summary .grand-total { background: #1a365d; color: #fff; font-size: 14px; }
        .summary .grand-total td { padding: 8px; }
        .payment-history { margin-bottom: 20px; }
        .payment-history h3 { font-size: 12px; color: #1a365d; margin-bottom: 8px; }
        .payment-history table { width: 100%; border-collapse: collapse; font-size: 11px; }
        .payment-history th { background: #f0f0f0; padding: 6px 8px; text-align: left; border: 1px solid #ddd; }
        .payment-history td { padding: 5px 8px; border: 1px solid #ddd; }
        .payment-history .text-right { text-align: right; }
        .sign-section { display: flex; justify-content: space-between; margin-top: 30px; padding-top: 15px; border-top: 1px solid #ddd; }
        .sign-box { text-align: center; width: 30%; }
        .sign-line { border-top: 1px solid #333; margin-top: 40px; padding-top: 5px; font-size: 11px; }
        .footer { text-align: center; margin-top: 20px; font-size: 10px; color: #999; }
        .page-break { page-break-before: always; }
        @media print {
            body { padding: 10px; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="text-align:center; margin-bottom:20px;">
        <button onclick="window.print()" style="padding:10px 25px; background:#1a365d; color:#fff; border:none; border-radius:4px; cursor:pointer; font-size:14px;">
            🖨️ Print Group Invoice
        </button>
        <a href="{{ route('reservations.show', $reservations->first()) }}" style="padding:10px 25px; background:#666; color:#fff; text-decoration:none; border-radius:4px; font-size:14px; margin-left:5px;">Kembali</a>
    </div>

    @php $hotel = \App\Models\HotelSetting::first(); @endphp

    <!-- Header -->
    <div class="header">
        <div class="hotel-info">
            @if($hotel->logo_path)
                <img src="{{ asset('storage/' . $hotel->logo_path) }}" alt="Logo" style="height:50px; margin-bottom:8px;">
            @endif
            <h1>{{ strtoupper($hotel->hotel_name ?? 'DYNAMIC PMS V.2') }}</h1>
            @if($hotel->address)<p>{{ $hotel->address }}</p>@endif
            @if($hotel->phone || $hotel->email)
                <p>
                    @if($hotel->phone)Telp: {{ $hotel->phone }}@endif
                    @if($hotel->phone && $hotel->email) | @endif
                    @if($hotel->email){{ $hotel->email }}@endif
                </p>
            @endif
            @if($hotel->website)<p>{{ $hotel->website }}</p>@endif
        </div>
        <div class="invoice-info">
            <h2>GROUP INVOICE</h2>
            <p><strong>No. Group:</strong> {{ $reservations->first()->booking_group_id }}</p>
            <p><strong>Tanggal:</strong> {{ \Carbon\Carbon::now()->format('d F Y') }}</p>
            <p><strong>Jumlah Kamar:</strong> {{ $reservations->count() }}</p>
        </div>
    </div>

    <!-- Details Tamu -->
    <div class="details">
        <div class="details-box">
            <h3>Info Tamu Utama</h3>
            <table>
                <tr><td>Nama</td><td>: {{ $reservations->first()->guest->guest_name ?? '-' }}</td></tr>
                <tr><td>No. Identitas</td><td>: {{ $reservations->first()->guest->id_number ?? '-' }}</td></tr>
                <tr><td>Telepon</td><td>: {{ $reservations->first()->guest->phone ?? '-' }}</td></tr>
                <tr><td>Email</td><td>: {{ $reservations->first()->guest->email ?? '-' }}</td></tr>
            </table>
        </div>
        <div class="details-box">
            <h3>Info Menginap</h3>
            <table>
                <tr><td>Check-in</td><td>: {{ $reservations->first()->check_in->format('d/m/Y H:i') }}</td></tr>
                <tr><td>Check-out</td><td>: {{ $reservations->first()->check_out->format('d/m/Y H:i') }}</td></tr>
                <tr><td>Durasi</td><td>: {{ $reservations->first()->nights }} malam</td></tr>
                <tr><td>Total Kamar</td><td>: {{ $reservations->count() }} kamar</td></tr>
            </table>
        </div>
    </div>

    <!-- Items per Room -->
    <table class="items-table">
        <thead>
            <tr>
                <th>No.</th>
                <th>Reservasi</th>
                <th>Kamar</th>
                <th>Tipe Kamar</th>
                <th>Tamu</th>
                <th>Durasi</th>
                <th class="text-right">Total</th>
                <th class="text-right">Dibayar</th>
                <th class="text-right">Sisa</th>
            </tr>
        </thead>
        <tbody>
            @foreach($reservations as $idx => $res)
            @php
                $sisa = $res->total_amount - $res->paid_amount;
                $totalSc = $res->serviceCharges->sum('total_amount');
                $totalResto = $res->restoTransactions->sum('total_amount');
                $subTotal = $res->total_amount + $totalSc + $totalResto;
            @endphp
            <tr class="{{ $idx % 2 === 0 ? 'room-row' : '' }}">
                <td>{{ $idx + 1 }}</td>
                <td>{{ $res->reservation_number }}</td>
                <td>{{ $res->room->room_number ?? '-' }}</td>
                <td>{{ $res->room->room_type_name ?? '-' }}</td>
                <td>{{ $res->guest->guest_name ?? '-' }}</td>
                <td>{{ $res->nights }} malam</td>
                <td class="text-right">Rp {{ number_format($subTotal, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($res->paid_amount, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format(max(0, $sisa), 0, ',', '.') }}</td>
            </tr>
            @if($totalSc > 0 || $totalResto > 0)
            <tr style="font-size:10px; color:#666;">
                <td></td>
                <td colspan="4">
                    @if($totalSc > 0) Other Revenue: Rp {{ number_format($totalSc, 0, ',', '.') }} @endif
                    @if($totalSc > 0 && $totalResto > 0) | @endif
                    @if($totalResto > 0) Resto: Rp {{ number_format($totalResto, 0, ',', '.') }} @endif
                </td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            @endif
            @endforeach
        </tbody>
    </table>

    <!-- Summary -->
    <div class="summary">
        <table>
            <tr>
                <td>Total Kamar</td>
                <td>Rp {{ number_format($groupTotal, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Other Revenue</td>
                <td>Rp {{ number_format($totalServiceCharge, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Resto</td>
                <td>Rp {{ number_format($totalResto, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Sudah Dibayar</td>
                <td>Rp {{ number_format($groupPaid, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td style="border-top:1px solid #ddd;">Sisa Bayar</td>
                <td style="border-top:1px solid #ddd; color:#c53030;">Rp {{ number_format(max(0, $grandTotal - $groupPaid), 0, ',', '.') }}</td>
            </tr>
            <tr class="grand-total">
                <td>GRAND TOTAL</td>
                <td>Rp {{ number_format($grandTotal, 0, ',', '.') }}</td>
            </tr>
        </table>
    </div>

    <!-- Payment History -->
    <div class="payment-history">
        <h3>Riwayat Pembayaran</h3>
        <table>
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Reservasi</th>
                    <th>Metode</th>
                    <th>Tipe</th>
                    <th class="text-right">Nominal</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transactions as $txn)
                <tr>
                    <td>{{ $txn->created_at->format('d/m/Y H:i') }}</td>
                    <td>{{ $txn->reservation->reservation_number ?? '-' }}</td>
                    <td>{{ str_replace('_', ' ', $txn->payment_method) }}</td>
                    <td>{{ strtoupper(str_replace('_', ' ', $txn->type)) }}</td>
                    <td class="text-right">Rp {{ number_format($txn->amount, 0, ',', '.') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="text-align:center; color:#999; padding:10px;">Belum ada pembayaran</td>
                </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr style="font-weight:bold; background:#f0f0f0;">
                    <td colspan="4" style="text-align:right;">TOTAL DIBAYAR</td>
                    <td class="text-right">Rp {{ number_format($transactions->sum('amount'), 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>
    </div>

    <!-- Signature -->
    <div class="sign-section">
        <div class="sign-box">
            <p>Diterima Oleh</p>
            <div class="sign-line"></div>
        </div>
        <div class="sign-box">
            <p>Hormat Kami</p>
            <div class="sign-line"></div>
        </div>
        <div class="sign-box">
            <p>Mengetahui</p>
            <div class="sign-line"></div>
        </div>
    </div>

    <!-- QR Code -->
    @php
        $firstReservation = $reservations->first();
        $invoiceUrl = $firstReservation ? config('app.url') . '/invoice/' . $firstReservation->reservation_number : '#';
    @endphp
    <div style="text-align: center; margin: 15px 0; padding: 10px; border-top: 1px solid #ddd;">
        <img src="https://api.qrserver.com/v1/create-qr-code/?size=100x100&data={{ urlencode($invoiceUrl) }}"
             alt="QR Code"
             style="width:100px; height:100px;">
        <p style="font-size: 9px; color: #999; margin-top: 3px;">Scan untuk lihat invoice online</p>
    </div>

    <div class="footer">
        <p>Terima kasih atas kunjungan Anda</p>
        <p>{{ $hotel->hotel_name ?? 'Dynamic PMS v2' }} &copy; {{ date('Y') }} — {{ $hotel->address ?? '' }}</p>
    </div>

    <script>
        window.onload = function() { window.print(); }
    </script>
</body>
</html>
