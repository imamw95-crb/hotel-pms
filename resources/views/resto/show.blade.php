<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Struk Resto - {{ $transaction->transaction_number }}</title>
    @vite('resources/css/app.css')
    <link href="{{ asset('assets/fontawesome/css/all.min.css') }}" rel="stylesheet">
    <style>
        @page { size: A5 landscape; margin: 8mm 10mm; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 11px; color: #000; background: #fff; }
        .no-print { display: block; }
        @media print {
            .no-print { display: none !important; }
            body { background: #fff; }
        }
    </style>
</head>
<body>

    {{-- Action Buttons --}}
    <div class="no-print" style="padding: 15px; background: #f8fafc; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
        <a href="{{ route('resto.index') }}" class="text-gray-500 hover:text-gray-700 font-medium">
            <i class="fas fa-arrow-left mr-1"></i> Kembali
        </a>
        <button onclick="window.print()" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-5 py-2 rounded-lg flex items-center gap-2">
            <i class="fas fa-print"></i> Print Struk
        </button>
    </div>

    {{-- Struk Content --}}
    <div style="padding: 15px 20px;">

        {{-- Header Hotel --}}
        <div style="text-align: center; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 12px;">
            @php $hotel = \App\Models\HotelSetting::get(); @endphp
            @if($hotel->logo_path)
                <img src="{{ asset('storage/' . $hotel->logo_path) }}" alt="Logo" style="height: 40px; margin-bottom: 4px;">
            @endif
            <div style="font-size: 16px; font-weight: bold; letter-spacing: 2px; text-transform: uppercase;">{{ $hotel->hotel_name ?? 'Dynamic PMS V.2' }}</div>
            @if($hotel->address)<div style="font-size: 10px; color: #555;">{{ $hotel->address }}</div>@endif
            <div style="font-size: 10px; color: #555;">
                @if($hotel->phone)Telp: {{ $hotel->phone }}@endif
                @if($hotel->phone && $hotel->email) | @endif
                @if($hotel->email){{ $hotel->email }}@endif
            </div>
        </div>

        {{-- Title --}}
        <div style="text-align: center; margin-bottom: 12px;">
            <div style="font-size: 13px; font-weight: bold; letter-spacing: 3px; text-transform: uppercase; border-bottom: 1px solid #999; display: inline-block; padding-bottom: 3px;">
                Struk Resto / F&B
            </div>
            <div style="font-size: 10px; color: #666; margin-top: 3px;">No: {{ $transaction->transaction_number }}</div>
        </div>

        {{-- Info --}}
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 12px; font-size: 11px;">
            <div>
                <table style="width: 100%;">
                    <tr><td style="color: #666; width: 80px; padding: 2px 0;">Tanggal</td><td style="font-weight: 600;">: {{ $transaction->created_at->format('d F Y H:i') }}</td></tr>
                    <tr><td style="color: #666; padding: 2px 0;">Tamu</td><td style="font-weight: 600;">: {{ $transaction->guest->guest_name ?? 'Walk-in' }}</td></tr>
                    <tr><td style="color: #666; padding: 2px 0;">Meja</td><td style="font-weight: 600;">: {{ $transaction->table_number ?? '-' }}</td></tr>
                </table>
            </div>
            <div>
                <table style="width: 100%;">
                    @if($transaction->reservation)
                    <tr><td style="color: #666; width: 80px; padding: 2px 0;">Reservasi</td><td style="font-weight: 600;">: {{ $transaction->reservation->reservation_number }}</td></tr>
                    <tr><td style="color: #666; padding: 2px 0;">Kamar</td><td style="font-weight: 600;">: {{ $transaction->reservation->room->room_number ?? '-' }}</td></tr>
                    @endif
                    <tr><td style="color: #666; padding: 2px 0;">Metode</td><td style="font-weight: 600;">: {{ ucwords(str_replace('_', ' ', $transaction->payment_method)) }}</td></tr>
                    <tr><td style="color: #666; padding: 2px 0;">Kasir</td><td style="font-weight: 600;">: {{ $transaction->createdBy->name ?? '-' }}</td></tr>
                </table>
            </div>
        </div>

        <hr style="border-top: 1px solid #000; margin: 10px 0;">

        {{-- Items Table --}}
        <table style="width: 100%; border-collapse: collapse; font-size: 11px; margin-bottom: 10px;">
            <thead>
                <tr style="border-bottom: 2px solid #000;">
                    <th style="text-align: left; padding: 5px 8px; font-weight: bold;">Item</th>
                    <th style="text-align: center; padding: 5px 8px; font-weight: bold; width: 50px;">Qty</th>
                    <th style="text-align: right; padding: 5px 8px; font-weight: bold; width: 100px;">Harga</th>
                    <th style="text-align: right; padding: 5px 8px; font-weight: bold; width: 110px;">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($transaction->items as $item)
                <tr style="border-bottom: 1px solid #ddd;">
                    <td style="padding: 6px 8px;">{{ $item['name'] }}</td>
                    <td style="text-align: center; padding: 6px 8px;">{{ $item['qty'] }}</td>
                    <td style="text-align: right; padding: 6px 8px;">{{ number_format($item['price'], 0, ',', '.') }}</td>
                    <td style="text-align: right; padding: 6px 8px; font-weight: 600;">{{ number_format($item['subtotal'], 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr style="border-top: 1px solid #000;">
                    <td colspan="3" style="padding: 5px 8px; text-align: right; font-weight: bold;">Subtotal</td>
                    <td style="padding: 5px 8px; text-align: right; font-weight: bold;">Rp {{ number_format($transaction->subtotal, 0, ',', '.') }}</td>
                </tr>
                @if($transaction->tax > 0)
                <tr>
                    <td colspan="3" style="padding: 3px 8px; text-align: right;">Pajak</td>
                    <td style="padding: 3px 8px; text-align: right;">Rp {{ number_format($transaction->tax, 0, ',', '.') }}</td>
                </tr>
                @endif
                @if($transaction->discount > 0)
                <tr>
                    <td colspan="3" style="padding: 3px 8px; text-align: right; color: #c00;">Diskon</td>
                    <td style="padding: 3px 8px; text-align: right; color: #c00;">- Rp {{ number_format($transaction->discount, 0, ',', '.') }}</td>
                </tr>
                @endif
                <tr style="border-top: 2px solid #000; border-bottom: 2px solid #000;">
                    <td colspan="3" style="padding: 6px 8px; text-align: right; font-weight: bold; font-size: 13px;">TOTAL</td>
                    <td style="padding: 6px 8px; text-align: right; font-weight: bold; font-size: 13px;">Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>

        {{-- Terbilang --}}
        <div style="border: 1px solid #000; border-radius: 3px; padding: 6px 10px; margin-bottom: 10px; font-size: 11px; font-style: italic; background: #f9f9f9;">
            <strong>Terbilang:</strong> {{ terbilang($transaction->total_amount) }} Rupiah
        </div>

        {{-- Notes --}}
        @if($transaction->notes)
        <div style="margin-bottom: 10px;">
            <span style="font-weight: 600; font-size: 11px; color: #555;">Catatan:</span>
            <div style="font-size: 11px; margin-top: 2px;">{{ $transaction->notes }}</div>
        </div>
        @endif

        {{-- Footer --}}
        <div style="text-align: center; margin-top: 16px; padding-top: 8px; border-top: 1px solid #ddd;">
            <div style="font-size: 11px; font-weight: bold;">Terima kasih atas kunjungan Anda!</div>
            <div style="font-size: 9px; color: #999; margin-top: 4px;">Struk ini sah tanpa tanda tangan basah.</div>
            <div style="font-size: 9px; color: #999;">Dicetak: {{ \Carbon\Carbon::now()->format('d/m/Y H:i:s') }}</div>
        </div>

    </div>

</body>
</html>
