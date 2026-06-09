@extends('layouts.app')

@section('title', 'Laporan Booking OTA')
@section('header', 'Laporan Booking OTA')

@section('content')
<!-- Filter -->
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
        <div>
            <label class="block text-gray-700 text-sm font-bold mb-2">Platform OTA</label>
            <select name="source" class="border rounded px-3 py-2">
                <option value="all" {{ $source === 'all' ? 'selected' : '' }}>Semua Platform</option>
                @foreach($otaSources as $opt)
                    <option value="{{ $opt }}" {{ $source === $opt ? 'selected' : '' }}>{{ $opt }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex gap-2">
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Filter</button>
            <a href="{{ route('reports.ota.export', request()->query()) }}" class="bg-orange-600 text-white px-4 py-2 rounded hover:bg-orange-700">Export CSV</a>
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
    <h1 class="text-2xl font-bold uppercase tracking-wider mt-2">LAPORAN BOOKING OTA</h1>
    <p class="text-gray-600">Periode: {{ \Carbon\Carbon::parse($startDate)->format('d F Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d F Y') }}</p>
    <p class="text-xs text-gray-400">Dicetak: {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}</p>
    <hr class="my-4 border-t-2 border-gray-800">
</div>

<!-- Summary Cards -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-white p-5 rounded shadow">
        <div class="text-gray-500 text-sm">Total Booking OTA</div>
        <div class="text-3xl font-bold">{{ $totalBookings }}</div>
    </div>
    <div class="bg-white p-5 rounded shadow">
        <div class="text-gray-500 text-sm">Total Platform</div>
        <div class="text-3xl font-bold">{{ $bySource->count() }}</div>
    </div>
    <div class="bg-white p-5 rounded shadow">
        <div class="text-gray-500 text-sm">Total Pendapatan</div>
        <div class="text-3xl font-bold text-green-600">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</div>
    </div>
    <div class="bg-white p-5 rounded shadow">
        <div class="text-gray-500 text-sm">Total Terbayar</div>
        <div class="text-3xl font-bold text-blue-600">Rp {{ number_format($totalPaid, 0, ',', '.') }}</div>
    </div>
</div>

<!-- Breakdown per Platform OTA -->
<div class="bg-white rounded shadow overflow-hidden mb-6">
    <div class="px-5 py-3 bg-blue-50 border-b font-bold text-lg">Rekap per Platform OTA</div>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-200">
                <tr>
                    <th class="px-4 py-2 text-left">Platform</th>
                    <th class="px-4 py-2 text-center">Total Booking</th>
                    <th class="px-4 py-2 text-center">Checked In</th>
                    <th class="px-4 py-2 text-center">Checked Out</th>
                    <th class="px-4 py-2 text-center">Pending</th>
                    <th class="px-4 py-2 text-center">Cancelled</th>
                    <th class="px-4 py-2 text-right">Total Pendapatan</th>
                    <th class="px-4 py-2 text-right">Terbayar</th>
                </tr>
            </thead>
            <tbody>
                @forelse($bySource as $item)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="px-4 py-2 font-bold">{{ $item->source }}</td>
                        <td class="px-4 py-2 text-center font-semibold">{{ $item->total }}</td>
                        <td class="px-4 py-2 text-center">
                            <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs font-bold">{{ $item->checked_in }}</span>
                        </td>
                        <td class="px-4 py-2 text-center">
                            <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs font-bold">{{ $item->checked_out }}</span>
                        </td>
                        <td class="px-4 py-2 text-center">
                            <span class="bg-indigo-100 text-indigo-800 px-2 py-1 rounded text-xs font-bold">{{ $item->pending }}</span>
                        </td>
                        <td class="px-4 py-2 text-center">
                            <span class="bg-red-100 text-red-800 px-2 py-1 rounded text-xs font-bold">{{ $item->cancelled }}</span>
                        </td>
                        <td class="px-4 py-2 text-right">Rp {{ number_format($item->total_amount, 0, ',', '.') }}</td>
                        <td class="px-4 py-2 text-right">Rp {{ number_format($item->paid_amount, 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-2 text-center text-gray-500">Tidak ada data booking OTA</td>
                    </tr>
                @endforelse
            </tbody>
            @if($bySource->isNotEmpty())
            <tfoot class="bg-gray-100 font-bold">
                <tr>
                    <td class="px-4 py-2">TOTAL</td>
                    <td class="px-4 py-2 text-center">{{ $totalBookings }}</td>
                    <td class="px-4 py-2 text-center">{{ $bySource->sum('checked_in') }}</td>
                    <td class="px-4 py-2 text-center">{{ $bySource->sum('checked_out') }}</td>
                    <td class="px-4 py-2 text-center">{{ $bySource->sum('pending') }}</td>
                    <td class="px-4 py-2 text-center">{{ $bySource->sum('cancelled') }}</td>
                    <td class="px-4 py-2 text-right">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</td>
                    <td class="px-4 py-2 text-right">Rp {{ number_format($totalPaid, 0, ',', '.') }}</td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>

<!-- Detail Booking OTA -->
<div class="bg-white rounded shadow overflow-hidden">
    <div class="px-5 py-3 bg-blue-50 border-b font-bold text-lg">Detail Booking OTA</div>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-200">
                <tr>
                    <th class="px-4 py-2 text-left">Platform</th>
                    <th class="px-4 py-2 text-left">No. Reservasi</th>
                    <th class="px-4 py-2 text-left">No. OTA</th>
                    <th class="px-4 py-2 text-left">Nama Tamu</th>
                    <th class="px-4 py-2 text-left">Kamar</th>
                    <th class="px-4 py-2 text-left">Check-in</th>
                    <th class="px-4 py-2 text-left">Check-out</th>
                    <th class="px-4 py-2 text-center">Status</th>
                    <th class="px-4 py-2 text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reservations as $r)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="px-4 py-2">
                            <span class="bg-purple-100 text-purple-800 px-2 py-0.5 rounded text-xs font-bold">{{ $r->ota_source }}</span>
                        </td>
                        <td class="px-4 py-2 font-bold">{{ $r->reservation_number }}</td>
                        <td class="px-4 py-2 text-gray-600 text-sm">{{ $r->ota_reservation_number ?? '-' }}</td>
                        <td class="px-4 py-2">{{ $r->guest?->guest_name ?? '-' }}</td>
                        <td class="px-4 py-2">{{ $r->room?->room_number ?? '-' }}</td>
                        <td class="px-4 py-2">{{ $r->check_in->format('d/m/Y H:i') }}</td>
                        <td class="px-4 py-2">{{ $r->check_out->format('d/m/Y H:i') }}</td>
                        <td class="px-4 py-2 text-center">
                            @if($r->status === 'pending')
                                <span class="bg-indigo-100 text-indigo-800 px-2 py-1 rounded text-xs font-bold">PENDING</span>
                            @elseif($r->status === 'checked_in')
                                <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs font-bold">CHECKED IN</span>
                            @elseif($r->status === 'checked_out')
                                <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs font-bold">CHECKED OUT</span>
                            @elseif($r->status === 'cancelled')
                                <span class="bg-red-100 text-red-800 px-2 py-1 rounded text-xs font-bold">CANCELLED</span>
                            @else
                                <span class="bg-gray-100 text-gray-800 px-2 py-1 rounded text-xs font-bold">{{ strtoupper($r->status) }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-2 text-right">Rp {{ number_format($r->total_amount, 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="px-4 py-2 text-center text-gray-500">Tidak ada data booking OTA</td>
                    </tr>
                @endforelse
            </tbody>
            @if($reservations->isNotEmpty())
            <tfoot class="bg-gray-100 font-bold">
                <tr>
                    <td colspan="8" class="px-4 py-2 text-right">TOTAL</td>
                    <td class="px-4 py-2 text-right">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>
@endsection
