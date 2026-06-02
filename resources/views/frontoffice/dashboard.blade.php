@extends('layouts.app')

@section('title', 'Front Office Dashboard')

@section('content')

{{-- Link ke Dashboard Operasional Hotel --}}
<div class="mb-6">
    <a href="{{ route('rooms.dashboard') }}"
       class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold px-5 py-2.5 rounded-lg transition">
        <i class="fas fa-th-large"></i> Dashboard Operasional Hotel
    </a>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-4">
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="bg-green-500 rounded-full p-3 mr-4">
                <i class="fas fa-bed text-white text-xl"></i>
            </div>
            <div>
                <p class="text-gray-500 text-sm">Kamar Tersedia</p>
                <p class="text-2xl font-bold">{{ $availableRooms }}</p>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="bg-blue-500 rounded-full p-3 mr-4">
                <i class="fas fa-sign-in-alt text-white text-xl"></i>
            </div>
            <div>
                <p class="text-gray-500 text-sm">Check-in Hari Ini</p>
                <p class="text-2xl font-bold">{{ $todayCheckins }}</p>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="bg-yellow-500 rounded-full p-3 mr-4">
                <i class="fas fa-sign-out-alt text-white text-xl"></i>
            </div>
            <div>
                <p class="text-gray-500 text-sm">Check-out Hari Ini</p>
                <p class="text-2xl font-bold">{{ $todayCheckouts }}</p>
            </div>
        </div>
    </div>
</div>

{{-- Denah Kamar --}}
<div class="bg-white rounded-lg shadow p-4 mt-6">
    <div class="flex justify-between items-center mb-4">
        <h3 class="font-bold text-lg"><i class="fas fa-th-large text-blue-500 mr-2"></i>Status Kamar</h3>
        <a href="{{ route('rooms.dashboard') }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
            Lihat Detail <i class="fas fa-arrow-right ml-1"></i>
        </a>
    </div>
    @php
        $allRooms = \App\Models\Room::with(['roomType', 'reservations' => function ($q) {
                $q->where('status', 'checked_in')
                    ->orWhere(function ($sub) {
                        $sub->where('status', 'pending')
                            ->whereDate('check_in', \Carbon\Carbon::today());
                    });
            }, 'reservations.guest'])
            ->orderBy('room_number')
            ->get();
    @endphp
    <div class="grid grid-cols-4 sm:grid-cols-6 md:grid-cols-8 lg:grid-cols-10 gap-2">
        @foreach($allRooms as $room)
            @php
                $statusColor = [
                    'available' => 'bg-green-100 border-green-400 text-green-800',
                    'occupied' => 'bg-red-100 border-red-400 text-red-800',
                    'maintenance' => 'bg-gray-100 border-gray-400 text-gray-800',
                    'cleaning' => 'bg-yellow-100 border-yellow-400 text-yellow-800',
                ][$room->status] ?? 'bg-gray-100 border-gray-400';
                $activeRes = $room->reservations->first();
                $guestName = $activeRes && $activeRes->guest ? $activeRes->guest->guest_name : null;
            @endphp
            <div class="border rounded p-1.5 text-center text-xs {{ $statusColor }}" title="{{ $room->room_number }} - {{ $room->room_type_name ?? 'Standard' }}{{ $guestName ? ' - ' . $guestName : '' }}">
                <p class="font-bold">{{ $room->room_number }}</p>
                @if($guestName)
                    <p class="truncate text-[10px] mt-0.5">{{ $guestName }}</p>
                @endif
            </div>
        @endforeach
    </div>
    <div class="flex gap-4 mt-3 text-xs">
        <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-green-100 border border-green-400"></span> Available</span>
        <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-red-100 border border-red-400"></span> Occupied</span>
        <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-yellow-100 border border-yellow-400"></span> Cleaning</span>
        <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-gray-100 border border-gray-400"></span> Maintenance</span>
    </div>
</div>

<!-- Daftar Tamu Check-in Hari Ini -->
<div class="bg-white rounded-lg shadow p-6 mt-6">
    <h3 class="font-bold text-lg mb-4"><i class="fas fa-user-check text-green-500 mr-2"></i>Tamu Check-in Hari Ini</h3>
    @php
        $todayCheckins = \App\Models\Reservation::with(['guest', 'room'])
            ->whereDate('check_in', \Carbon\Carbon::today())
            ->where('status', 'pending')
            ->orderBy('check_in')
            ->limit(10)
            ->get();
    @endphp
    @if($todayCheckins->count() > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead>
                    <tr class="border-b">
                        <th class="text-left p-2 text-sm">Nama Tamu</th>
                        <th class="text-left p-2 text-sm">Kamar</th>
                        <th class="text-left p-2 text-sm">Check-out</th>
                        <th class="text-left p-2 text-sm">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($todayCheckins as $res)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="p-2 font-medium">{{ $res->guest->guest_name ?? '-' }}</td>
                        <td class="p-2">{{ $res->room->room_number ?? '-' }}</td>
                        <td class="p-2 text-sm">{{ $res->check_out->format('d/m/Y') }}</td>
                        <td class="p-2">
                            <a href="{{ route('checkin.index') }}" class="bg-green-500 text-white px-3 py-1 rounded text-xs">Check-in</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <p class="text-gray-500 text-center py-4">Tidak ada check-in hari ini</p>
    @endif
</div>

<!-- Daftar Tamu Check-out Hari Ini -->
<div class="bg-white rounded-lg shadow p-6 mt-6">
    <h3 class="font-bold text-lg mb-4"><i class="fas fa-user-times text-yellow-500 mr-2"></i>Tamu Check-out Hari Ini</h3>
    @php
        $todayCheckouts = \App\Models\Reservation::with(['guest', 'room'])
            ->whereDate('check_out', \Carbon\Carbon::today())
            ->where('status', 'checked_in')
            ->orderBy('check_out')
            ->limit(10)
            ->get();
    @endphp
    @if($todayCheckouts->count() > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead>
                    <tr class="border-b">
                        <th class="text-left p-2 text-sm">Nama Tamu</th>
                        <th class="text-left p-2 text-sm">Kamar</th>
                        <th class="text-left p-2 text-sm">Check-in</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($todayCheckouts as $res)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="p-2 font-medium">{{ $res->guest->guest_name ?? '-' }}</td>
                        <td class="p-2">{{ $res->room->room_number ?? '-' }}</td>
                        <td class="p-2 text-sm">{{ $res->check_in->format('d/m/Y') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <p class="text-gray-500 text-center py-4">Tidak ada check-out hari ini</p>
    @endif
</div>

<div class="mt-6">
    <a href="{{ route('checkin.index') }}" class="bg-blue-600 text-white px-4 py-2 rounded inline-block">
        <i class="fas fa-plus"></i> Check-in Baru
    </a>
    <a href="{{ route('rooms.dashboard') }}" class="bg-green-600 text-white px-4 py-2 rounded inline-block ml-2">
        <i class="fas fa-th-large"></i> Dashboard Kamar
    </a>
</div>
@endsection