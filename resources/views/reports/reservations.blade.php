@extends('layouts.app')

@section('title', 'Laporan Reservasi')
@section('header', 'Laporan Reservasi')

@section('content')
<div class="mb-6">
    <form method="GET" class="flex gap-4">
        <div>
            <label class="block text-gray-700 text-sm font-bold mb-2">Dari Tanggal</label>
            <input type="date" name="start_date" value="{{ $startDate }}" class="border rounded px-3 py-2">
        </div>
        <div>
            <label class="block text-gray-700 text-sm font-bold mb-2">Sampai Tanggal</label>
            <input type="date" name="end_date" value="{{ $endDate }}" class="border rounded px-3 py-2">
        </div>
        <div class="pt-6">
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Filter</button>
        </div>
    </form>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <div class="bg-white p-5 rounded shadow">
        <div class="text-gray-500 text-sm">Total Reservasi</div>
        <div class="text-3xl font-bold">{{ $reservations->count() }}</div>
    </div>
    <div class="bg-white p-5 rounded shadow">
        <div class="text-gray-500 text-sm">Sudah Check-in</div>
        <div class="text-3xl font-bold text-blue-600">{{ $reservations->where('status', 'checked_in')->count() }}</div>
    </div>
    <div class="bg-white p-5 rounded shadow">
        <div class="text-gray-500 text-sm">Pending</div>
        <div class="text-3xl font-bold text-indigo-600">{{ $reservations->where('status', 'pending')->count() }}</div>
    </div>
</div>

<div class="bg-white rounded shadow overflow-hidden">
    <table class="w-full">
        <thead class="bg-gray-200">
            <tr>
                <th class="px-4 py-2 text-left">No. Reservasi</th>
                <th class="px-4 py-2 text-left">Nama Tamu</th>
                <th class="px-4 py-2 text-left">Kamar</th>
                <th class="px-4 py-2 text-left">Check-in</th>
                <th class="px-4 py-2 text-left">Check-out</th>
                <th class="px-4 py-2 text-left">Status</th>
                <th class="px-4 py-2 text-left">Dibuat Oleh</th>
            </tr>
        </thead>
        <tbody>
            @forelse($reservations as $reservation)
                <tr class="border-b hover:bg-gray-50">
                    <td class="px-4 py-2 font-bold">{{ $reservation->reservation_number }}</td>
                    <td class="px-4 py-2">{{ $reservation->guest?->guest_name ?? '-' }}</td>
                    <td class="px-4 py-2">{{ $reservation->room?->room_number ?? '-' }}</td>
                    <td class="px-4 py-2">{{ $reservation->check_in->format('d/m/Y H:i') }}</td>
                    <td class="px-4 py-2">{{ $reservation->check_out->format('d/m/Y H:i') }}</td>
                    <td class="px-4 py-2">
                        @if($reservation->status === 'pending')
                            <span class="bg-indigo-100 text-indigo-800 px-2 py-1 rounded text-xs font-bold">PENDING</span>
                        @elseif($reservation->status === 'checked_in')
                            <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs font-bold">CHECKED IN</span>
                        @elseif($reservation->status === 'checked_out')
                            <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs font-bold">CHECKED OUT</span>
                        @else
                            <span class="bg-red-100 text-red-800 px-2 py-1 rounded text-xs font-bold">{{ strtoupper($reservation->status) }}</span>
                        @endif
                    </td>
                    <td class="px-4 py-2">{{ $reservation->createdBy?->name ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-4 py-2 text-center text-gray-500">Tidak ada data reservasi</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
