<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice - {{ $reservation->reservation_number }}</title>
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
        @media print {
            body { padding: 10px; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="text-align:center; margin-bottom:20px;">
        <button onclick="window.print()" style="padding:10px 25px; background:#1a365d; color:#fff; border:none; border-radius:4px; cursor:pointer; font-size:14px;">
            🖨️ Print Invoice
        </button>
        <a href="{{ route('reservations.show', $reservation) }}" style="padding:10px 25px; background:#666; color:#fff; text-decoration:none; border-radius:4px; font-size:14px; margin-left:5px;">Kembali</a>
    </div>

    <!-- Header -->
    <div class="header">
        <div class="hotel-info">
            @php $hotel = \App\Models\HotelSetting::first(); @endphp
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
            <h2>INVOICE</h2>
            <p><strong>No:</strong> {{ $reservation->reservation_number }}</p>
            <p><strong>Tanggal:</strong> {{ \Carbon\Carbon::now()->format('d F Y') }}</p>
            <p><strong>Status:</strong> {{ strtoupper($reservation->status) }}</p>
        </div>
    </div>

    <!-- Details -->
    <div class="details">
        <div class="details-box">
            <h3>Info Tamu</h3>
            <table>
                <tr><td>Nama</td><td>: {{ $reservation->guest->guest_name ?? '-' }}</td></tr>
                <tr><td>No. Identitas</td><td>: {{ $reservation->guest->id_number ?? '-' }}</td></tr>
                <tr><td>Telepon</td><td>: {{ $reservation->guest->phone ?? '-' }}</td></tr>
                <tr><td>Email</td><td>: {{ $reservation->guest->email ?? '-' }}</td></tr>
                <tr><td>Alamat</td><td>: {{ $reservation->guest->address ?? '-' }}</td></tr>
            </table>
        </div>
        <div class="details-box">
            <h3>Info Kamar</h3>
            <table>
                <tr><td>Tipe Kamar</td><td>: {{ $reservation->room->roomType->name ?? $reservation->room->room_type_name ?? '-' }}</td></tr>
                <tr><td>Check-in</td><td>: {{ $reservation->check_in->format('d/m/Y H:i') }}</td></tr>
                <tr><td>Check-out</td><td>: {{ $reservation->check_out->format('d/m/Y H:i') }}</td></tr>
                <tr><td>Durasi</td><td>: {{ $reservation->nights }} malam</td></tr>
            </table>
        </div>
    </div>

    <!-- Items -->
    <table class="items-table">
        <thead>
            <tr>
                <th>Deskripsi</th>
                <th>Kamar</th>
                <th>Durasi</th>
                <th>Harga/Malam</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Kamar {{ $reservation->room->room_number ?? '-' }} - {{ $reservation->room->room_type_name ?? 'Standard' }}</td>
                <td>{{ $reservation->room->room_number ?? '-' }}</td>
                <td>{{ $reservation->nights }} malam</td>
                <td>Rp {{ number_format($reservation->total_amount / max(1, $reservation->nights), 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($reservation->total_amount, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    @if($reservation->serviceCharges->count() > 0)
    <!-- Other Revenue Table -->
    <table class="items-table" style="margin-top:15px;">
        <thead>
            <tr>
                <th>Other Revenue</th>
                <th>Tanggal</th>
                <th>Layanan</th>
                <th>Qty</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($reservation->serviceCharges as $sc)
            <tr>
                <td>{{ $sc->charge_number }}</td>
                <td>{{ $sc->charge_date->format('d/m/Y') }}</td>
                <td>{{ $sc->service_name }}</td>
                <td>{{ $sc->quantity }} × Rp {{ number_format($sc->amount, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($sc->total_amount, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="background:#f0f7ff; font-weight:bold;">
                <td colspan="4" style="text-align:right; padding:6px 8px;">Subtotal Other Revenue</td>
                <td class="text-right" style="padding:6px 8px; color:#1a365d;">Rp {{ number_format($totalServiceCharge, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>
    @endif

    @if($reservation->restoTransactions->count() > 0)
    <!-- Resto Table -->
    <table class="items-table" style="margin-top:15px;">
        <thead>
            <tr>
                <th>Resto</th>
                <th>No. Transaksi</th>
                <th>Tanggal</th>
                <th>Items</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($reservation->restoTransactions as $rt)
            <tr>
                <td></td>
                <td>{{ $rt->transaction_number }}</td>
                <td>{{ $rt->created_at->format('d/m/Y H:i') }}</td>
                <td>
                    @if(is_array($rt->items))
                        @foreach($rt->items as $item)
                            {{ $item['name'] ?? $item['menu_name'] ?? 'Item' }} × {{ $item['quantity'] ?? 1 }}@if(!$loop->last), @endif
                        @endforeach
                    @else
                        -
                    @endif
                </td>
                <td class="text-right">Rp {{ number_format($rt->total_amount, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="background:#fff5f0; font-weight:bold;">
                <td colspan="4" style="text-align:right; padding:6px 8px;">Subtotal Resto</td>
                <td class="text-right" style="padding:6px 8px; color:#c05621;">Rp {{ number_format($totalResto, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>
    @endif

    <!-- Summary -->
    <div class="summary">
        <table>
            <tr>
                <td>Subtotal Kamar:</td>
                <td>Rp {{ number_format($reservation->total_amount, 0, ',', '.') }}</td>
            </tr>
            @if($totalServiceCharge > 0)
            <tr>
                <td>Other Revenue:</td>
                <td>Rp {{ number_format($totalServiceCharge, 0, ',', '.') }}</td>
            </tr>
            @endif
            @if($totalResto > 0)
            <tr>
                <td>Resto:</td>
                <td>Rp {{ number_format($totalResto, 0, ',', '.') }}</td>
            </tr>
            @endif
            <tr style="border-top:1px solid #333;">
                <td style="font-weight:bold;">Grand Total:</td>
                <td style="font-weight:bold;">Rp {{ number_format($grandTotal, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Total Dibayar:</td>
                <td>Rp {{ number_format($reservation->paid_amount, 0, ',', '.') }}</td>
            </tr>
            <tr class="grand-total">
                <td>SISA BAYAR:</td>
                <td>Rp {{ number_format(max(0, $grandTotal - $reservation->paid_amount), 0, ',', '.') }}</td>
            </tr>
        </table>
    </div>
    <!-- Terbilang -->
    <div style="margin: 10px 0; padding: 8px 12px; border: 1px solid #333; font-size: 12px; font-style: italic; width: 100%; box-sizing: border-box;">
        <strong>Terbilang:</strong> {{ terbilang($reservation->paid_amount) }} Rupiah
    </div>

    <!-- Payment History -->
    @if($transactions->count() > 0)
    <div class="payment-history">
        <h3>Riwayat Pembayaran</h3>
        <table>
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
                    <td>{{ ucwords(str_replace('_', ' ', $txn->type)) }}</td>
                    <td>{{ ucwords(str_replace('_', ' ', $txn->payment_method)) }}</td>
                    <td class="text-right">Rp {{ number_format($txn->amount, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- Sign -->
    <div class="sign-section">
        <div class="sign-box">
            <div class="sign-line">Dibuat Oleh</div>
            <p style="font-size:10px; color:#999;">{{ $reservation->createdBy->name ?? '-' }}</p>
        </div>
        <div class="sign-box">
            <div class="sign-line">Diterima Oleh</div>
            <p style="font-size:10px; color:#999;">{{ $reservation->guest->guest_name ?? '-' }}</p>
        </div>
    </div>

    <div class="footer">
        <p>Invoice ini sah sebagai bukti tagihan pembayaran</p>
        <p>Dynamic PMS V.2 &copy; {{ date('Y') }}</p>
    </div>
</body>
</html>
