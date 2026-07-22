<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice Online - {{ $reservation->reservation_number }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .no-print { display: none !important; }
            body { font-size: 11px; }
        }
    </style>
</head>
<body class="bg-gray-100">
    <!-- Navbar -->
    <nav class="bg-white shadow-sm border-b no-print">
        <div class="max-w-5xl mx-auto px-4 py-3 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <span class="font-semibold text-gray-700">Invoice Online</span>
            </div>
            <button onclick="window.print()" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition text-sm font-medium">
                🖨️ Print / PDF
            </button>
        </div>
    </nav>

    {{-- Signature Validation Badge --}}
    @if($signatureStatus === 'valid')
        <div class="max-w-4xl mx-auto mb-2 p-3 bg-green-100 border border-green-300 text-green-800 rounded-lg flex items-center gap-2 text-sm no-print">
            <svg class="w-5 h-5 text-green-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <div><strong>Dokumen Valid</strong> — Invoice ini telah ditandatangani secara kriptografi dan tidak diubah sejak diterbitkan.</div>
        </div>
    @elseif($signatureStatus === 'invalid')
        <div class="max-w-4xl mx-auto mb-2 p-3 bg-red-100 border border-red-300 text-red-800 rounded-lg flex items-center gap-2 text-sm no-print">
            <svg class="w-5 h-5 text-red-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
            </svg>
            <div><strong>Peringatan!</strong> Tanda tangan digital invoice ini <strong>tidak valid</strong> — data telah diubah sejak diterbitkan.</div>
        </div>
    @endif

    <!-- Invoice Content -->
    <div class="max-w-4xl mx-auto bg-white shadow-lg rounded-lg my-6 p-8 print:shadow-none print:my-0 print:p-4">
        <!-- Header -->
        <div class="flex justify-between items-start border-b-2 border-gray-800 pb-4 mb-5">
            <div>
                @php $hotel = \App\Models\HotelSetting::first(); @endphp
                @if($hotel->logo_path)
                    <img src="{{ asset('storage/' . $hotel->logo_path) }}" alt="Logo" class="h-12 mb-2">
                @endif
                <h1 class="text-2xl font-bold text-gray-900">{{ strtoupper($hotel->hotel_name ?? 'DYNAMIC PMS V.2') }}</h1>
                @if($hotel->address)<p class="text-sm text-gray-500">{{ $hotel->address }}</p>@endif
                @if($hotel->phone || $hotel->email)
                    <p class="text-sm text-gray-500">
                        @if($hotel->phone)Telp: {{ $hotel->phone }}@endif
                        @if($hotel->phone && $hotel->email) | @endif
                        @if($hotel->email){{ $hotel->email }}@endif
                    </p>
                @endif
                @if($hotel->website)<p class="text-sm text-gray-500">{{ $hotel->website }}</p>@endif
            </div>
            <div class="text-right">
                <h2 class="text-xl font-bold text-red-600">INVOICE</h2>
                <p class="text-sm"><strong>No:</strong> {{ $reservation->reservation_number }}</p>
                <p class="text-sm"><strong>Tanggal:</strong> {{ \Carbon\Carbon::now()->format('d F Y') }}</p>
            </div>
        </div>

        <!-- Details -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-5">
            <div class="border border-gray-200 rounded-lg p-3">
                <h3 class="text-xs font-bold text-gray-800 border-b border-gray-100 pb-2 mb-2 uppercase tracking-wide">Info Tamu</h3>
                @php
                    // ── Helper masking sesuai UU PDP ──
                    function maskIdNumber($val) {
                        if (!$val) return '-';
                        $len = strlen($val);
                        if ($len <= 4) return str_repeat('*', $len);
                        return substr($val, 0, 2) . str_repeat('*', $len - 4) . substr($val, -2);
                    }
                    function maskPhone($val) {
                        if (!$val) return '-';
                        $len = strlen($val);
                        if ($len <= 4) return str_repeat('*', $len);
                        return str_repeat('*', $len - 4) . substr($val, -4);
                    }
                    function maskEmail($val) {
                        if (!$val) return '-';
                        $parts = explode('@', $val);
                        $name = $parts[0] ?? '';
                        $domain = $parts[1] ?? '';
                        if (strlen($name) <= 2) {
                            $masked = substr($name, 0, 1) . str_repeat('*', max(1, strlen($name) - 1));
                        } else {
                            $masked = substr($name, 0, 2) . str_repeat('*', strlen($name) - 2);
                        }
                        return $masked . '@' . $domain;
                    }
                @endphp
                <table class="w-full text-sm">
                    <tr><td class="text-gray-500 w-1/3 py-0.5">Nama</td><td>: {{ $reservation->guest->guest_name ?? '-' }}</td></tr>
                    <tr><td class="text-gray-500 w-1/3 py-0.5">No. Identitas</td><td>: {{ maskIdNumber($reservation->guest->id_number ?? '') }}</td></tr>
                    <tr><td class="text-gray-500 w-1/3 py-0.5">Telepon</td><td>: {{ maskPhone($reservation->guest->phone ?? '') }}</td></tr>
                    <tr><td class="text-gray-500 w-1/3 py-0.5">Email</td><td>: {{ maskEmail($reservation->guest->email ?? '') }}</td></tr>
                    <tr><td class="text-gray-500 w-1/3 py-0.5">Alamat</td><td>: {{ $reservation->guest->address ?? '-' }}</td></tr>
                </table>
            </div>
            <div class="border border-gray-200 rounded-lg p-3">
                <h3 class="text-xs font-bold text-gray-800 border-b border-gray-100 pb-2 mb-2 uppercase tracking-wide">Info Kamar</h3>
                <table class="w-full text-sm">
                    <tr><td class="text-gray-500 w-1/3 py-0.5">Tipe Kamar</td><td>: {{ $reservation->room->roomType->name ?? $reservation->room->room_type_name ?? '-' }}</td></tr>
                    <tr><td class="text-gray-500 w-1/3 py-0.5">No. Kamar</td><td>: {{ $reservation->room->room_number ?? '-' }}</td></tr>
                    <tr><td class="text-gray-500 w-1/3 py-0.5">Check-in</td><td>: {{ $reservation->check_in->format('d/m/Y H:i') }}</td></tr>
                    <tr><td class="text-gray-500 w-1/3 py-0.5">Check-out</td><td>: {{ $reservation->check_out->format('d/m/Y H:i') }}</td></tr>
                    <tr><td class="text-gray-500 w-1/3 py-0.5">Durasi</td><td>: {{ $reservation->nights }} malam</td></tr>
                </table>
            </div>
        </div>

        <!-- Items Table -->
        <table class="w-full border-collapse mb-4 text-sm">
            <thead>
                <tr class="bg-gray-800 text-white">
                    <th class="p-2 text-left">Deskripsi</th>
                    <th class="p-2">Kamar</th>
                    <th class="p-2">Durasi</th>
                    <th class="p-2 text-right">Harga/Malam</th>
                    <th class="p-2 text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                <tr class="border-b border-gray-200">
                    <td class="p-2">Kamar {{ $reservation->room->room_number ?? '-' }}</td>
                    <td class="p-2 text-center">{{ $reservation->room->room_number ?? '-' }}</td>
                    <td class="p-2 text-center">{{ $reservation->nights }} malam</td>
                    <td class="p-2 text-right">Rp {{ number_format($reservation->total_amount / max(1, $reservation->nights), 0, ',', '.') }}</td>
                    <td class="p-2 text-right font-semibold">Rp {{ number_format($reservation->total_amount, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>

        @if($reservation->serviceCharges->count() > 0)
        <table class="w-full border-collapse mb-4 text-sm">
            <thead>
                <tr class="bg-blue-800 text-white">
                    <th class="p-2 text-left">Other Revenue</th>
                    <th class="p-2">Tanggal</th>
                    <th class="p-2">Layanan</th>
                    <th class="p-2">Qty</th>
                    <th class="p-2 text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reservation->serviceCharges as $sc)
                <tr class="border-b border-gray-200">
                    <td class="p-2">{{ $sc->charge_number }}</td>
                    <td class="p-2">{{ $sc->charge_date->format('d/m/Y') }}</td>
                    <td class="p-2">{{ $sc->service_name }}</td>
                    <td class="p-2">{{ $sc->quantity }} × Rp {{ number_format($sc->amount, 0, ',', '.') }}</td>
                    <td class="p-2 text-right">Rp {{ number_format($sc->total_amount, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="bg-blue-50 font-semibold">
                    <td colspan="4" class="p-2 text-right">Subtotal Other Revenue</td>
                    <td class="p-2 text-right text-blue-800">Rp {{ number_format($totalServiceCharge, 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>
        @endif

        @if($reservation->restoTransactions->count() > 0)
        <table class="w-full border-collapse mb-4 text-sm">
            <thead>
                <tr class="bg-orange-800 text-white">
                    <th class="p-2 text-left">Resto</th>
                    <th class="p-2">No. Transaksi</th>
                    <th class="p-2">Tanggal</th>
                    <th class="p-2">Items</th>
                    <th class="p-2 text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reservation->restoTransactions as $rt)
                <tr class="border-b border-gray-200">
                    <td class="p-2"></td>
                    <td class="p-2">{{ $rt->transaction_number }}</td>
                    <td class="p-2">{{ $rt->created_at->format('d/m/Y H:i') }}</td>
                    <td class="p-2">
                        @if(is_array($rt->items))
                            @foreach($rt->items as $item)
                                {{ $item['name'] ?? $item['menu_name'] ?? 'Item' }} × {{ $item['quantity'] ?? 1 }}@if(!$loop->last), @endif
                            @endforeach
                        @else
                            -
                        @endif
                    </td>
                    <td class="p-2 text-right">Rp {{ number_format($rt->total_amount, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="bg-orange-50 font-semibold">
                    <td colspan="4" class="p-2 text-right">Subtotal Resto</td>
                    <td class="p-2 text-right text-orange-700">Rp {{ number_format($totalResto, 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>
        @endif

        <!-- Summary -->
        <div class="flex justify-end mb-5">
            <table class="w-56 text-sm">
                <tr>
                    <td class="p-1.5 text-right text-gray-600">Subtotal Kamar:</td>
                    <td class="p-1.5 text-right font-semibold">Rp {{ number_format($reservation->total_amount, 0, ',', '.') }}</td>
                </tr>
                @if($totalServiceCharge > 0)
                <tr>
                    <td class="p-1.5 text-right text-gray-600">Other Revenue:</td>
                    <td class="p-1.5 text-right font-semibold">Rp {{ number_format($totalServiceCharge, 0, ',', '.') }}</td>
                </tr>
                @endif
                @if($totalResto > 0)
                <tr>
                    <td class="p-1.5 text-right text-gray-600">Resto:</td>
                    <td class="p-1.5 text-right font-semibold">Rp {{ number_format($totalResto, 0, ',', '.') }}</td>
                </tr>
                @endif
                <tr class="border-t border-gray-300">
                    <td class="p-1.5 text-right font-bold">Grand Total:</td>
                    <td class="p-1.5 text-right font-bold">Rp {{ number_format($grandTotal, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td class="p-1.5 text-right text-gray-600">Total Dibayar:</td>
                    <td class="p-1.5 text-right font-semibold">Rp {{ number_format($reservation->paid_amount, 0, ',', '.') }}</td>
                </tr>
                <tr class="bg-gray-800 text-white">
                    <td class="p-2 text-right font-bold">SISA BAYAR:</td>
                    <td class="p-2 text-right font-bold">Rp {{ number_format(max(0, $grandTotal - $reservation->paid_amount), 0, ',', '.') }}</td>
                </tr>
            </table>
        </div>

        <!-- Payment History -->
        @if($transactions->count() > 0)
        <div class="mb-5">
            <h3 class="text-xs font-bold text-gray-800 border-b border-gray-200 pb-1 mb-2 uppercase tracking-wide">Riwayat Pembayaran</h3>
            <table class="w-full border-collapse text-sm">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="p-2 text-left border border-gray-200">No. Transaksi</th>
                        <th class="p-2 text-left border border-gray-200">Tanggal</th>
                        <th class="p-2 text-left border border-gray-200">Tipe</th>
                        <th class="p-2 text-left border border-gray-200">Metode</th>
                        <th class="p-2 text-right border border-gray-200">Nominal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($transactions as $txn)
                    <tr>
                        <td class="p-2 border border-gray-200">{{ $txn->transaction_number }}</td>
                        <td class="p-2 border border-gray-200">{{ $txn->created_at->format('d/m/Y H:i') }}</td>
                        <td class="p-2 border border-gray-200">{{ ucwords(str_replace('_', ' ', $txn->type)) }}</td>
                        <td class="p-2 border border-gray-200">{{ ucwords(str_replace('_', ' ', $txn->payment_method)) }}</td>
                        <td class="p-2 border border-gray-200 text-right">Rp {{ number_format($txn->amount, 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        <!-- Footer -->
        <div class="text-center text-xs text-gray-400 border-t border-gray-200 pt-4 mt-5">
            <p class="text-gray-600 text-sm font-medium mb-1">Terima kasih atas kunjungan Anda</p>
            <p>Invoice ini sah sebagai bukti tagihan pembayaran</p>
            @if($signatureStatus === 'valid')
                <p class="text-green-600 font-medium mt-1">✅ Ditandatangani secara digital (HMAC-SHA256)</p>
            @elseif($signatureStatus === 'invalid')
                <p class="text-red-600 font-medium mt-1">❌ Tanda tangan digital TIDAK VALID</p>
            @endif
            <p>{{ $hotel->hotel_name ?? 'Dynamic PMS v2' }} &copy; {{ date('Y') }}</p>
        </div>
    </div>
</body>
</html>
