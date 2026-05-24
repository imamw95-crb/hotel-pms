@extends('layouts.app')

@section('title', 'Dashboard Kamar')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold">Dashboard Operasional Hotel</h1>
    <p class="text-gray-600">Pilih kamar untuk melakukan reservasi / check-in cepat</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
    <div class="bg-green-100 border-l-4 border-green-500 p-4 rounded shadow">
        <div class="flex items-center">
            <i class="fas fa-bed text-green-600 text-2xl mr-3"></i>
            <div>
                <p class="text-sm text-gray-600">Kamar Tersedia</p>
                <p class="text-2xl font-bold" id="availableCount">{{ $availableRoomsCount }}</p>
            </div>
        </div>
    </div>
    <div class="bg-blue-100 border-l-4 border-blue-500 p-4 rounded shadow">
        <div class="flex items-center">
            <i class="fas fa-sign-in-alt text-blue-600 text-2xl mr-3"></i>
            <div>
                <p class="text-sm text-gray-600">Check-in Hari Ini</p>
                <p class="text-2xl font-bold" id="checkinsCount">{{ $checkinsToday }}</p>
            </div>
        </div>
    </div>
    <div class="bg-yellow-100 border-l-4 border-yellow-500 p-4 rounded shadow">
        <div class="flex items-center">
            <i class="fas fa-sign-out-alt text-yellow-600 text-2xl mr-3"></i>
            <div>
                <p class="text-sm text-gray-600">Check-out Hari Ini</p>
                <p class="text-2xl font-bold" id="checkoutsCount">{{ $checkoutsToday }}</p>
            </div>
        </div>
    </div>
    <div class="bg-purple-100 border-l-4 border-purple-500 p-4 rounded shadow">
        <div class="flex items-center">
            <i class="fas fa-calendar-alt text-purple-600 text-2xl mr-3"></i>
            <div>
                <p class="text-sm text-gray-600">Booking Mendatang</p>
                <p class="text-2xl font-bold">{{ $upcomingBookings->count() }}</p>
            </div>
        </div>
    </div>
</div>

<div class="bg-white rounded-lg shadow p-4 mb-8">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-bold">Denah Kamar</h2>
        <div class="flex space-x-3">
            <button onclick="refreshRooms()" class="bg-blue-500 text-white px-3 py-1 rounded text-sm">
                <i class="fas fa-sync-alt"></i> Refresh
            </button>
            <a href="{{ route('booking.group.create') }}" class="bg-green-600 text-white px-3 py-1 rounded text-sm">
                <i class="fas fa-users"></i> Booking Group
            </a>
        </div>
    </div>
    
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4" id="roomsGrid">
        @foreach($rooms as $room)
            @php
                $statusColor = [
                    'available' => 'bg-green-100 border-green-500 text-green-800',
                    'occupied' => 'bg-red-100 border-red-500 text-red-800',
                    'maintenance' => 'bg-gray-100 border-gray-500 text-gray-800',
                    'cleaning' => 'bg-yellow-100 border-yellow-500 text-yellow-800',
                ][$room->status] ?? 'bg-gray-100 border-gray-400';
                
                $statusIcon = [
                    'available' => 'fa-check-circle',
                    'occupied' => 'fa-ban',
                    'maintenance' => 'fa-tools',
                    'cleaning' => 'fa-broom',
                ][$room->status] ?? 'fa-bed';
            @endphp
            <div class="border rounded-lg p-3 text-center cursor-pointer hover:shadow-md transition room-card" data-room-id="{{ $room->id }}" data-room-number="{{ $room->room_number }}" data-status="{{ $room->status }}" onclick="window.location.href='{{ route('booking.create', ['room_id' => $room->id]) }}'">
                <div class="rounded-lg p-2 {{ $statusColor }}">
                    <i class="fas {{ $statusIcon }} text-lg"></i>
                    <p class="font-bold text-lg">{{ $room->room_number }}</p>
                    <p class="text-xs">{{ $room->room_type_name ?? 'Standard' }}</p>
                    <p class="text-xs mt-1">{{ ucfirst($room->status) }}</p>
                </div>
            </div>
        @endforeach
    </div>
</div>

<div class="bg-white rounded-lg shadow p-4">
    <h2 class="text-xl font-bold mb-3">Booking Mendatang</h2>
    @if($upcomingBookings->count())
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead>
                    <tr class="border-b">
                        <th class="text-left p-2">Tanggal</th>
                        <th class="text-left p-2">Kamar</th>
                        <th class="text-left p-2">Tamu</th>
                        <th class="text-left p-2">Status</th>
                        <th class="text-left p-2">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($upcomingBookings as $booking)
                    <tr class="border-b">
                        <td class="p-2">{{ $booking->check_in->format('d/m/Y') }} - {{ $booking->check_out->format('d/m/Y') }}</td>
                        <td class="p-2">{{ $booking->room->room_number }}</td>
                        <td class="p-2">{{ $booking->guest->guest_name }}</td>
                        <td class="p-2">
                            <span class="px-2 py-1 rounded text-xs bg-yellow-100 text-yellow-800">Pending</span>
                        </td>
                        <td class="p-2">
                            <a href="{{ route('checkin.process') }}?reservation_id={{ $booking->id }}" class="text-blue-600 hover:underline">Check-in</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <p class="text-gray-500">Tidak ada booking mendatang.</p>
    @endif
</div>

<!-- Daftar Tamu Check-in & Check-out Hari Ini -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
    <div class="bg-white rounded-lg shadow p-4">
        <h3 class="font-bold text-lg mb-3"><i class="fas fa-user-check text-green-500 mr-2"></i>Check-in Hari Ini</h3>
        @php
            $todayCheckins = \App\Models\Reservation::with(['guest', 'room'])
                ->whereDate('check_in', \Carbon\Carbon::today())
                ->where('status', 'pending')
                ->orderBy('check_in')
                ->limit(10)
                ->get();
        @endphp
        @if($todayCheckins->count() > 0)
            <table class="min-w-full">
                <thead>
                    <tr class="border-b">
                        <th class="text-left p-2 text-sm">Nama Tamu</th>
                        <th class="text-left p-2 text-sm">Kamar</th>
                        <th class="text-left p-2 text-sm">Check-out</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($todayCheckins as $res)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="p-2 font-medium">{{ $res->guest->guest_name ?? '-' }}</td>
                        <td class="p-2">{{ $res->room->room_number ?? '-' }}</td>
                        <td class="p-2 text-sm">{{ $res->check_out->format('d/m/Y') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p class="text-gray-500 text-center py-4">Tidak ada check-in hari ini</p>
        @endif
    </div>

    <div class="bg-white rounded-lg shadow p-4">
        <h3 class="font-bold text-lg mb-3"><i class="fas fa-user-times text-yellow-500 mr-2"></i>Check-out Hari Ini</h3>
        @php
            $todayCheckouts = \App\Models\Reservation::with(['guest', 'room'])
                ->whereDate('check_out', \Carbon\Carbon::today())
                ->where('status', 'checked_in')
                ->orderBy('check_out')
                ->limit(10)
                ->get();
        @endphp
        @if($todayCheckouts->count() > 0)
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
        @else
            <p class="text-gray-500 text-center py-4">Tidak ada check-out hari ini</p>
        @endif
    </div>
</div>

<script>
    function refreshRooms() {
        fetch('{{ route("rooms.api") }}')
            .then(res => res.json())
            .then(data => {
                document.getElementById('availableCount').innerText = data.available_count;
                document.getElementById('checkinsCount').innerText = data.checkins_today;
                document.getElementById('checkoutsCount').innerText = data.checkouts_today;
                
                const grid = document.getElementById('roomsGrid');
                grid.innerHTML = '';
                data.rooms.forEach(room => {
                    let statusClass = '', icon = '';
                    if (room.status === 'available') {
                        statusClass = 'bg-green-100 border-green-500 text-green-800';
                        icon = 'fa-check-circle';
                    } else if (room.status === 'occupied') {
                        statusClass = 'bg-red-100 border-red-500 text-red-800';
                        icon = 'fa-ban';
                    } else if (room.status === 'maintenance') {
                        statusClass = 'bg-gray-100 border-gray-500 text-gray-800';
                        icon = 'fa-tools';
                    } else {
                        statusClass = 'bg-yellow-100 border-yellow-500 text-yellow-800';
                        icon = 'fa-broom';
                    }
                    
                    const card = document.createElement('div');
                    card.className = 'border rounded-lg p-3 text-center cursor-pointer hover:shadow-md transition';
                    card.onclick = () => window.location.href = `/booking/create?room_id=${room.id}`;
                    card.innerHTML = `
                        <div class="rounded-lg p-2 ${statusClass}">
                            <i class="fas ${icon} text-lg"></i>
                            <p class="font-bold text-lg">${room.room_number}</p>
                            <p class="text-xs">${room.room_type_name || 'Standard'}</p>
                            <p class="text-xs mt-1">${room.status.charAt(0).toUpperCase() + room.status.slice(1)}</p>
                        </div>
                    `;
                    grid.appendChild(card);
                });
            });
    }
    
    setInterval(refreshRooms, 30000);
</script>
@endsection