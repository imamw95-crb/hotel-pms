@extends('layouts.app')

@section('title', 'Front Office Dashboard')

@section('content')
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