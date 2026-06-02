<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Tanda Terima Deposit - {{ $deposit->receipt_number }}</title>
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

    {{-- Action Buttons — hanya tampil di screen --}}
    <div class="no-print" style="padding: 15px; background: #f8fafc; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
        <a href="{{ route('deposits.index') }}" class="text-gray-500 hover:text-gray-700 font-medium">
            <i class="fas fa-arrow-left mr-1"></i> Kembali
        </a>
        <button onclick="window.print()" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-5 py-2 rounded-lg flex items-center gap-2">
            <i class="fas fa-print"></i> Print Tanda Terima
        </button>
    </div>

    {{-- Tanda Terima Content --}}
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
                Tanda Terima Deposit Kartu
            </div>
            <div style="font-size: 10px; color: #666; margin-top: 3px;">No: {{ $deposit->receipt_number }}</div>
        </div>

        {{-- Info Grid --}}
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 12px; font-size: 11px;">
            <div>
                <table style="width: 100%;">
                    <tr><td style="color: #666; width: 90px; padding: 2px 0;">Tanggal</td><td style="font-weight: 600;">: {{ $deposit->created_at->format('d F Y H:i') }}</td></tr>
                    <tr><td style="color: #666; padding: 2px 0;">Nama Tamu</td><td style="font-weight: 600;">: {{ $deposit->guest->guest_name ?? '-' }}</td></tr>
                    <tr><td style="color: #666; padding: 2px 0;">No. Identitas</td><td style="font-weight: 600;">: {{ $deposit->guest->id_number ?? '-' }}</td></tr>
                    <tr><td style="color: #666; padding: 2px 0;">Telepon</td><td style="font-weight: 600;">: {{ $deposit->guest->phone ?? '-' }}</td></tr>
                </table>
            </div>
            <div>
                <table style="width: 100%;">
                    <tr><td style="color: #666; width: 90px; padding: 2px 0;">Reservasi</td><td style="font-weight: 600;">: {{ $deposit->reservation->reservation_number ?? '-' }}</td></tr>
                    @if($deposit->reservation)
                    <tr><td style="color: #666; padding: 2px 0;">Kamar</td><td style="font-weight: 600;">: {{ $deposit->reservation->room->room_number ?? '-' }}</td></tr>
                    @endif
                    <tr><td style="color: #666; padding: 2px 0;">Metode Bayar</td><td style="font-weight: 600;">: {{ ucwords(str_replace('_', ' ', $deposit->payment_method)) }}</td></tr>
                    <tr><td style="color: #666; padding: 2px 0;">Dibuat Oleh</td><td style="font-weight: 600;">: {{ $deposit->createdBy->name ?? '-' }}</td></tr>
                </table>
            </div>
        </div>

        {{-- Divider --}}
        <hr style="border-top: 1px solid #000; margin: 10px 0;">

        {{-- Deposit Detail Table --}}
        <table style="width: 100%; border-collapse: collapse; font-size: 11px; margin-bottom: 10px;">
            <thead>
                <tr style="border-bottom: 2px solid #000;">
                    <th style="text-align: left; padding: 5px 8px; font-weight: bold;">Keterangan</th>
                    <th style="text-align: center; padding: 5px 8px; font-weight: bold; width: 60px;">Jumlah</th>
                    <th style="text-align: right; padding: 5px 8px; font-weight: bold; width: 120px;">Nominal</th>
                </tr>
            </thead>
            <tbody>
                <tr style="border-bottom: 1px solid #ddd;">
                    <td style="padding: 8px;">
                        <span style="font-weight: 600;">Deposit Kartu</span>
                        <span style="color: #666;">(Rp {{ number_format($deposit->nominal_per_card, 0, ',', '.') }} × {{ $deposit->number_of_cards }} kartu)</span>
                    </td>
                    <td style="text-align: center; padding: 8px;">{{ $deposit->number_of_cards }}</td>
                    <td style="text-align: right; padding: 8px; font-weight: bold;">Rp {{ number_format($deposit->total_amount, 0, ',', '.') }}</td>
                </tr>
            </tbody>
            <tfoot>
                <tr style="border-top: 2px solid #000; border-bottom: 2px solid #000;">
                    <td colspan="2" style="padding: 6px 8px; text-align: right; font-weight: bold; font-size: 12px;">TOTAL</td>
                    <td style="padding: 6px 8px; text-align: right; font-weight: bold; font-size: 12px;">Rp {{ number_format($deposit->total_amount, 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>

        {{-- Terbilang --}}
        <div style="border: 1px solid #000; border-radius: 3px; padding: 6px 10px; margin-bottom: 10px; font-size: 11px; font-style: italic; background: #f9f9f9;">
            <strong>Terbilang:</strong> {{ terbilang($deposit->total_amount) }} Rupiah
        </div>

        {{-- Notes --}}
        @if($deposit->notes)
        <div style="margin-bottom: 10px;">
            <span style="font-weight: 600; font-size: 11px; color: #555;">Catatan:</span>
            <div style="font-size: 11px; margin-top: 2px;">{{ $deposit->notes }}</div>
        </div>
        @endif

        {{-- Signatures --}}
        <div style="display: flex; justify-content: space-between; margin-top: 24px; padding-top: 10px;">
            <div style="text-align: center; width: 30%;">
                <div style="font-size: 11px; color: #666; margin-bottom: 35px;">Penerima</div>
                <div style="border-top: 1px solid #000; padding-top: 4px; font-size: 11px; font-weight: 600;">{{ $deposit->guest->guest_name ?? '(Tamu)' }}</div>
            </div>
            <div style="text-align: center; width: 30%;">
                <div style="font-size: 11px; color: #666; margin-bottom: 35px;">Petugas</div>
                <div style="border-top: 1px solid #000; padding-top: 4px; font-size: 11px; font-weight: 600;">{{ $deposit->createdBy->name ?? '(Petugas)' }}</div>
            </div>
        </div>

        {{-- Footer --}}
        <div style="text-align: center; margin-top: 16px; padding-top: 8px; border-top: 1px solid #ddd;">
            <div style="font-size: 9px; color: #999;">Tanda terima ini sah tanpa tanda tangan basah.</div>
            <div style="font-size: 9px; color: #999;">Dicetak pada: {{ \Carbon\Carbon::now()->format('d/m/Y H:i:s') }}</div>
        </div>

    </div>

</body>
</html>
