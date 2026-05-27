@extends('layouts.app')

@section('title', 'Pindah Kamar')
@section('header', 'Pindah Kamar')

@section('content')
<!-- Stats -->
<div class="stats-grid mb-6">
    <div class="bg-red-50 border border-red-200 rounded-lg p-4 text-center">
        <p class="text-xs text-red-600 font-medium">Kamar Occupied</p>
        <p class="text-2xl font-bold text-red-600">{{ $reservations->count() }}</p>
    </div>
    <div class="bg-green-50 border border-green-200 rounded-lg p-4 text-center">
        <p class="text-xs text-green-600 font-medium">Kamar Available</p>
        <p class="text-2xl font-bold text-green-600">{{ $availableRooms->count() }}</p>
    </div>
</div>

<!-- Tabel Pindah Kamar -->
<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full">
            <thead class="bg-gray-100">
                <tr>
                    <th class="text-left p-3 text-sm font-semibold">No. Reservasi</th>
                    <th class="text-left p-3 text-sm font-semibold">Nama Tamu</th>
                    <th class="text-left p-3 text-sm font-semibold">Kamar Lama</th>
                    <th class="text-left p-3 text-sm font-semibold">Check-in</th>
                    <th class="text-left p-3 text-sm font-semibold">Check-out</th>
                    <th class="text-left p-3 text-sm font-semibold">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reservations as $res)
                <tr class="border-b hover:bg-gray-50">
                    <td class="p-3 font-medium text-blue-600">{{ $res->reservation_number }}</td>
                    <td class="p-3">
                        <div class="font-medium">{{ $res->guest->guest_name ?? '-' }}</div>
                        <div class="text-xs text-gray-500">{{ $res->guest->phone ?? '' }}</div>
                    </td>
                    <td class="p-3">
                        <span class="font-bold text-red-600">{{ $res->room->room_number ?? '-' }}</span>
                        <div class="text-xs text-gray-500">{{ $res->room->room_type_name ?? '' }}</div>
                    </td>
                    <td class="p-3 text-sm">{{ $res->check_in->format('d/m/Y H:i') }}</td>
                    <td class="p-3 text-sm">{{ $res->check_out->format('d/m/Y H:i') }}</td>
                    <td class="p-3">
                        @if($availableRooms->count() > 0)
                        <a href="{{ route('reservations.room-change', $res) }}" class="bg-blue-500 text-white px-3 py-1 rounded text-xs hover:bg-blue-600" title="Pindah Kamar">
                            <i class="fas fa-exchange-alt mr-1"></i> Pindah Kamar
                        </a>
                        @else
                        <span class="text-xs text-gray-400 italic">Tidak ada kamar available</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="p-8 text-center text-gray-500">
                        <i class="fas fa-exchange-alt text-4xl mb-2 text-gray-300"></i>
                        <p>Tidak ada kamar yang bisa dipindah</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
