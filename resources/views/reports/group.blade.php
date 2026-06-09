@extends('layouts.app')

@section('title', 'Laporan Booking Grup')
@section('header', 'Laporan Booking Grup')

@section('content')
<!-- Filter & Print - hidden on print -->
<div class="mb-6 no-print">
    <form method="GET" class="flex flex-wrap gap-4 items-end">
        <div>
            <label class="block text-gray-700 text-sm font-bold mb-2">Dari Tanggal</label>
            <input type="date" name="start_date" value="{{ $startDate }}" class="border rounded px-3 py-2">
        </div>
        <div>
            <label class="block text-gray-700 text-sm font-bold mb-2">Sampai Tanggal</label>
            <input type="date" name="end_date" value="{{ $endDate }}" class="border rounded px-3 py-2">
        </div>
        <div class="flex gap-2">
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Filter</button>
            <a href="{{ route('reports.group.export', request()->query()) }}" class="bg-orange-600 text-white px-4 py-2 rounded hover:bg-orange-700">Export CSV</a>
            <button type="button" onclick="window.print()" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Print</button>
        </div>
    </form>
</div>

<!-- Print Header -->
<div class="hidden print:block mb-6 text-center">
    @php $hotel = \App\Models\HotelSetting::first(); @endphp
    @if($hotel->logo_path)
        <img src="{{ asset('storage/' . $hotel->logo_path) }}" alt="Logo" class="h-12 mx-auto mb-2">
    @endif
    <h2 class="text-lg font-bold uppercase tracking-wider">{{ $hotel->hotel_name ?? 'Dynamic PMS V.2' }}</h2>
    @if($hotel->address)<p class="text-xs text-gray-500">{{ $hotel->address }}</p>@endif
    @if($hotel->phone)<p class="text-xs text-gray-500">Telp: {{ $hotel->phone }}</p>@endif
    <h1 class="text-2xl font-bold uppercase tracking-wider mt-2">LAPORAN BOOKING GRUP</h1>
    <p class="text-gray-600">Periode: {{ \Carbon\Carbon::parse($startDate)->format('d F Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d F Y') }}</p>
    <p class="text-xs text-gray-400">Dicetak: {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}</p>
    <hr class="my-4 border-t-2 border-gray-800">
</div>

{{-- Summary Cards --}}
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-white p-5 rounded shadow">
        <div class="text-gray-500 text-sm">Total Grup</div>
        <div class="text-3xl font-bold">{{ $totalGroups }}</div>
    </div>
    <div class="bg-white p-5 rounded shadow">
        <div class="text-gray-500 text-sm">Total Pendapatan</div>
        <div class="text-3xl font-bold text-green-600">Rp {{ number_format($grandTotalAmount, 0, ',', '.') }}</div>
    </div>
    <div class="bg-white p-5 rounded shadow">
        <div class="text-gray-500 text-sm">Total Terbayar</div>
        <div class="text-3xl font-bold text-blue-600">Rp {{ number_format($grandTotalPaid, 0, ',', '.') }}</div>
    </div>
    <div class="bg-white p-5 rounded shadow">
        <div class="text-gray-500 text-sm">Sisa Pembayaran</div>
        <div class="text-3xl font-bold text-red-600">Rp {{ number_format($grandTotalRemaining, 0, ',', '.') }}</div>
    </div>
</div>

{{-- Detail per Grup --}}
<div class="space-y-4">
    @forelse($groups as $group)
    <div class="bg-white rounded shadow overflow-hidden">
        {{-- Group Header --}}
        <div class="bg-blue-50 px-5 py-3 flex flex-wrap justify-between items-center border-b">
            <div>
                <span class="font-bold text-lg">{{ $group->guest_name }}</span>
                <span class="text-gray-500 text-sm ml-3">{{ $group->room_numbers }}</span>
            </div>
            <div class="flex gap-4 text-sm">
                <span class="text-gray-600">
                    <i class="fas fa-calendar-alt"></i>
                    {{ $group->check_in->format('d/m/Y') }} - {{ $group->check_out->format('d/m/Y') }}
                </span>
                <span class="text-gray-600">
                    <i class="fas fa-layer-group"></i>
                    {{ $group->total_rooms }} Kamar
                </span>
            </div>
        </div>

        {{-- Group Body --}}
        <div class="p-5">
            {{-- Room Details Table --}}
            <table class="w-full mb-3">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-3 py-2 text-left text-xs font-semibold text-gray-600">Kamar</th>
                        <th class="px-3 py-2 text-left text-xs font-semibold text-gray-600">No. Reservasi</th>
                        <th class="px-3 py-2 text-left text-xs font-semibold text-gray-600">Status</th>
                        <th class="px-3 py-2 text-right text-xs font-semibold text-gray-600">Total</th>
                        <th class="px-3 py-2 text-right text-xs font-semibold text-gray-600">Terbayar</th>
                        <th class="px-3 py-2 text-right text-xs font-semibold text-gray-600">Sisa</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($group->rooms as $reservation)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="px-3 py-2">{{ $reservation->room->room_number ?? '-' }}</td>
                        <td class="px-3 py-2">{{ $reservation->reservation_number }}</td>
                        <td class="px-3 py-2">
                            <span class="px-2 py-0.5 rounded text-xs font-semibold
                                @if($reservation->status === 'checked_in') bg-green-100 text-green-800
                                @elseif($reservation->status === 'checked_out') bg-gray-100 text-gray-800
                                @elseif($reservation->status === 'cancelled') bg-red-100 text-red-800
                                @else bg-yellow-100 text-yellow-800
                                @endif">
                                {{ $reservation->status_label }}
                            </span>
                        </td>
                        <td class="px-3 py-2 text-right font-medium">Rp {{ number_format($reservation->total_amount, 0, ',', '.') }}</td>
                        <td class="px-3 py-2 text-right text-green-600">Rp {{ number_format($reservation->paid_amount, 0, ',', '.') }}</td>
                        <td class="px-3 py-2 text-right {{ $reservation->remaining_payment > 0 ? 'text-red-600' : 'text-green-600' }}">
                            Rp {{ number_format($reservation->remaining_payment, 0, ',', '.') }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-gray-50 font-bold">
                    <tr>
                        <td colspan="3" class="px-3 py-2 text-right">Total Grup</td>
                        <td class="px-3 py-2 text-right">Rp {{ number_format($group->total_amount, 0, ',', '.') }}</td>
                        <td class="px-3 py-2 text-right text-green-600">Rp {{ number_format($group->paid_amount, 0, ',', '.') }}</td>
                        <td class="px-3 py-2 text-right {{ $group->remaining_payment > 0 ? 'text-red-600' : 'text-green-600' }}">
                            Rp {{ number_format($group->remaining_payment, 0, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    @empty
    <div class="bg-white rounded shadow p-10 text-center text-gray-500">
        <i class="fas fa-users text-4xl mb-3 text-gray-300"></i>
        <p>Tidak ada data booking grup pada periode ini</p>
    </div>
    @endforelse
</div>

<style>
    @media print {
        /* Sembunyikan semua elemen non-data */
        .no-print,
        aside,
        nav,
        header,
        .app-header,
        .app-sidebar,
        .sidebar-spacer,
        .sidebar-overlay,
        form,
        button,
        .bg-blue-800,
        #ai-chat-widget,
        [data-turbo-permanent] { display: none !important; }

        /* Reset layout */
        body {
            background: white !important;
            margin: 0 !important;
            padding: 10px !important;
            font-size: 11px !important;
        }

        #app-layout,
        .main-wrapper,
        .page-content {
            display: block !important;
            width: 100% !important;
            max-width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
            overflow: visible !important;
        }

        /* Tampilkan print header */
        .print\:block { display: block !important; }

        /* Reset shadow dan border */
        .shadow { box-shadow: none !important; }
        .rounded { border: 1px solid #ccc !important; border-radius: 0 !important; }
        .rounded-lg { border: 1px solid #ccc !important; border-radius: 0 !important; }

        /* Summary cards */
        .grid-cols-1.md\:grid-cols-4 {
            display: grid !important;
            grid-template-columns: repeat(4, 1fr) !important;
            gap: 6px !important;
        }
        .grid-cols-1.md\:grid-cols-4 > div {
            border: 1px solid #ccc !important;
            padding: 8px !important;
        }

        /* Tabel */
        table { width: 100% !important; border-collapse: collapse !important; font-size: 10px !important; }
        th, td { padding: 3px 5px !important; border: 1px solid #999 !important; }
        th { background: #f0f0f0 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }

        /* Font size */
        h1 { font-size: 16px !important; }
        h2 { font-size: 12px !important; }
        .text-3xl { font-size: 16px !important; }
        .text-lg { font-size: 11px !important; }
        .text-sm { font-size: 9px !important; }
        .text-xs { font-size: 8px !important; }

        /* Warna background print */
        .bg-blue-50 { background: #eff6ff !important; -webkit-print-color-adjust: exact; }
        .bg-green-100 { background: #dcfce7 !important; -webkit-print-color-adjust: exact; }
        .bg-red-100 { background: #fee2e2 !important; -webkit-print-color-adjust: exact; }
        .bg-yellow-100 { background: #fef9c3 !important; -webkit-print-color-adjust: exact; }
        .bg-gray-100 { background: #f3f4f6 !important; -webkit-print-color-adjust: exact; }
        .bg-gray-50 { background: #f9fafb !important; -webkit-print-color-adjust: exact; }

        /* Page break per grup */
        .space-y-4 > .bg-white.rounded.shadow {
            page-break-inside: avoid;
        }
    }
</style>
@endsection
