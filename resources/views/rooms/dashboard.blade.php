@extends('layouts.app')

@section('title', 'Dashboard Kamar')

@section('header', 'Dashboard Kamar')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold">Dashboard Operasional Hotel</h1>
    <p class="text-gray-600">Pilih kamar untuk melakukan reservasi / check-in cepat</p>
</div>

<div class="stats-grid mb-8">
    <div class="bg-green-100 border-l-4 border-green-500 p-4 rounded shadow">
        <div class="flex items-center">
            <i class="fas fa-bed text-green-600 text-2xl mr-3 flex-shrink-0"></i>
            <div class="min-w-0">
                <p class="text-sm text-gray-600 truncate">Kamar Tersedia</p>
                <p class="text-2xl font-bold" id="availableCount">{{ $availableRoomsCount }}</p>
            </div>
        </div>
    </div>
    <div class="bg-blue-100 border-l-4 border-blue-500 p-4 rounded shadow">
        <div class="flex items-center">
            <i class="fas fa-sign-in-alt text-blue-600 text-2xl mr-3 flex-shrink-0"></i>
            <div class="min-w-0">
                <p class="text-sm text-gray-600 truncate">Check-in Hari Ini</p>
                <p class="text-2xl font-bold" id="checkinsCount">{{ $checkinsToday }}</p>
            </div>
        </div>
    </div>
    <div class="bg-yellow-100 border-l-4 border-yellow-500 p-4 rounded shadow">
        <div class="flex items-center">
            <i class="fas fa-sign-out-alt text-yellow-600 text-2xl mr-3 flex-shrink-0"></i>
            <div class="min-w-0">
                <p class="text-sm text-gray-600 truncate">Check-out Hari Ini</p>
                <p class="text-2xl font-bold" id="checkoutsCount">{{ $checkoutsToday }}</p>
            </div>
        </div>
    </div>
    <div class="bg-purple-100 border-l-4 border-purple-500 p-4 rounded shadow">
        <div class="flex items-center">
            <i class="fas fa-calendar-alt text-purple-600 text-2xl mr-3 flex-shrink-0"></i>
            <div class="min-w-0">
                <p class="text-sm text-gray-600 truncate">Booking Mendatang</p>
                <p class="text-2xl font-bold">{{ $upcomingBookings->count() }}</p>
            </div>
        </div>
    </div>
</div>

<!-- Dashboard Data Attributes -->
<div id="roomsDashboard" 
     data-rooms-api="{{ route('rooms.api') }}" 
     data-booking-url="{{ route('booking.create') }}"
     data-date-from="{{ $dateFrom }}"
     data-date-to="{{ $dateTo }}"
     data-status-filter="{{ $statusFilter }}"
     data-room-type-filter="{{ $roomTypeFilter }}"
     class="hidden"></div>

<!-- Bulk Action Panel -->
<div id="bulkActionPanel" class="hidden mb-4 p-3 bg-blue-50 border border-blue-200 rounded-lg flex items-center justify-between">
    <div class="flex items-center gap-3">
        <span class="text-sm font-medium text-blue-700"><span id="bulkSelectedCount">0</span> kamar dipilih</span>
        <button data-bulk-action data-bulk-checkin class="px-3 py-1 bg-green-500 text-white rounded text-sm opacity-50" disabled>
            <i class="fas fa-sign-in-alt mr-1"></i> Check-in
        </button>
        <button data-bulk-action data-bulk-checkout class="px-3 py-1 bg-yellow-500 text-white rounded text-sm opacity-50" disabled>
            <i class="fas fa-sign-out-alt mr-1"></i> Check-out
        </button>
        <button data-bulk-action data-bulk-maintenance class="px-3 py-1 bg-gray-500 text-white rounded text-sm opacity-50" disabled>
            <i class="fas fa-tools mr-1"></i> Maintenance
        </button>
    </div>
    <button onclick="RoomsDashboard.toggleBulkMode()" class="text-sm text-blue-600 hover:text-blue-800">
        <i class="fas fa-times"></i> Tutup
    </button>
</div>

<div class="bg-white rounded-lg shadow p-4 mb-8">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 mb-4">
        <div class="flex items-center gap-3 flex-shrink-0">
            <h2 class="text-xl font-bold whitespace-nowrap">Denah Kamar</h2>
            <span class="text-xs text-gray-400 hidden sm:inline whitespace-nowrap">
                <kbd class="px-1 bg-gray-100 rounded text-[10px]">Ctrl+B</kbd>
                <kbd class="px-1 bg-gray-100 rounded text-[10px] ml-1">Ctrl+R</kbd>
                <kbd class="px-1 bg-gray-100 rounded text-[10px] ml-1">Ctrl+A</kbd>
            </span>
        </div>
        <div class="flex items-center gap-2 overflow-x-auto pb-1">
            <select data-filter-status class="border rounded px-2 py-1 text-sm flex-shrink-0">
                <option value="all" {{ $statusFilter === 'all' ? 'selected' : '' }}>Semua Status</option>
                <option value="available" {{ $statusFilter === 'available' ? 'selected' : '' }}>Available</option>
                <option value="occupied" {{ $statusFilter === 'occupied' ? 'selected' : '' }}>Occupied</option>
                <option value="due_out" {{ $statusFilter === 'due_out' ? 'selected' : '' }}>Due Out</option>
                <option value="cleaning" {{ $statusFilter === 'cleaning' ? 'selected' : '' }}>Cleaning</option>
                <option value="maintenance" {{ $statusFilter === 'maintenance' ? 'selected' : '' }}>Maintenance</option>
            </select>
            <select data-filter-type class="border rounded px-2 py-1 text-sm flex-shrink-0">
                <option value="all" {{ $roomTypeFilter === 'all' ? 'selected' : '' }}>Semua Tipe</option>
                @foreach($roomTypes as $type)
                    <option value="{{ $type }}" {{ $roomTypeFilter === $type ? 'selected' : '' }}>{{ $type }}</option>
                @endforeach
            </select>
            <input type="date" data-filter-date-from value="{{ $dateFrom }}" class="border rounded px-2 py-1 text-sm flex-shrink-0 w-[130px]">
            <span class="text-sm text-gray-500 flex-shrink-0">s/d</span>
            <input type="date" data-filter-date-to value="{{ $dateTo }}" class="border rounded px-2 py-1 text-sm flex-shrink-0 w-[130px]">
            <button onclick="RoomsDashboard.refresh()" class="bg-blue-500 text-white px-3 py-1 rounded text-sm hover:bg-blue-600 transition flex-shrink-0" title="Refresh">
                <i class="fas fa-sync-alt"></i>
            </button>
            <button data-bulk-toggle class="bg-purple-500 text-white px-3 py-1 rounded text-sm hover:bg-purple-600 transition flex-shrink-0" title="Bulk Action (Ctrl+A)">
                <i class="fas fa-check-square"></i>
            </button>
            <button onclick="Modal.open('{{ route('booking.group.create') }}')" class="bg-green-600 text-white px-3 py-1 rounded text-sm hover:bg-green-700 transition flex-shrink-0">
                <i class="fas fa-users"></i> Group
            </button>
        </div>
    </div>
    
    <div class="rooms-grid" id="roomsGrid">
        @forelse($rooms as $room)
            @php
                $isDueOut = in_array($room->id, $dueOutRoomIds);
                $statusColor = [
                    'available' => 'bg-green-100 border-green-500 text-green-800',
                    'occupied' => $isDueOut ? 'bg-orange-100 border-orange-500 text-orange-800' : 'bg-red-100 border-red-500 text-red-800',
                    'maintenance' => 'bg-gray-100 border-gray-500 text-gray-800',
                    'cleaning' => 'bg-yellow-100 border-yellow-500 text-yellow-800',
                ][$room->status] ?? 'bg-gray-100 border-gray-400';
                
                $statusIcon = [
                    'available' => 'fa-check-circle',
                    'occupied' => $isDueOut ? 'fa-clock' : 'fa-ban',
                    'maintenance' => 'fa-tools',
                    'cleaning' => 'fa-broom',
                ][$room->status] ?? 'fa-bed';

                $statusLabel = $isDueOut ? 'Due Out' : ucfirst($room->status);
                $activeReservation = $room->reservations->first();
                $guestName = $activeReservation && $activeReservation->guest ? $activeReservation->guest->guest_name : null;
            @endphp
            <div class="room-card border-2 rounded-xl p-3 text-center cursor-pointer hover:shadow-lg transition-all duration-200 relative group"
                 data-room-id="{{ $room->id }}"
                 data-room-number="{{ $room->room_number }}"
                 data-room-type="{{ $room->room_type_name ?? 'Standard' }}"
                 data-status="{{ $room->status }}">
                <div class="rounded-lg p-2 {{ $statusColor }}">
                    <i class="fas {{ $statusIcon }} text-lg"></i>
                    <p class="font-bold text-lg">{{ $room->room_number }}</p>
                    <p class="text-xs">{{ $room->room_type_name ?? 'Standard' }}</p>
                    <p class="text-xs mt-1 font-semibold">{{ $statusLabel }}</p>
                    <p class="text-[10px] mt-1 text-gray-600">
                        <i class="fas fa-tag mr-0.5"></i>Wd: Rp {{ number_format($room->price_weekday ?? $room->price_per_night, 0, ',', '.') }} · We: Rp {{ number_format($room->price_weekend ?? $room->price_per_night, 0, ',', '.') }}
                    </p>
                    @if($isDueOut && $activeReservation)
                        <p class="text-xs mt-1 text-orange-700"><i class="fas fa-clock mr-1"></i>Due Out: {{ $activeReservation->check_out->format('H:i') }}</p>
                    @endif
                    @if($guestName)
                        <p class="text-xs mt-1 truncate text-blue-700 font-medium" title="{{ $guestName }}"><i class="fas fa-user mr-1"></i>{{ $guestName }}</p>
                    @endif
                    @if($activeReservation && !$isDueOut)
                        <p class="text-xs mt-1 text-gray-600">
                            <i class="fas fa-calendar mr-1"></i>{{ $activeReservation->check_in->format('d/m') }} - {{ $activeReservation->check_out->format('d/m') }}
                        </p>
                    @endif
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-8 text-gray-500">
                <i class="fas fa-search text-3xl mb-2"></i>
                <p>Tidak ada kamar yang sesuai filter.</p>
            </div>
        @endforelse
    </div>
</div>

<!-- Legend for room status -->
<div class="bg-gray-50 rounded p-4 mb-6 text-sm">
    <h3 class="font-bold mb-2">Legenda Status Kamar:</h3>
    <div class="flex flex-wrap gap-4">
        <div class="flex items-center">
            <span class="w-3 h-3 bg-green-100 border border-green-500 rounded-full mr-2"></span>
            <span>Available - Kamar kosong dan siap digunakan</span>
        </div>
        <div class="flex items-center">
            <span class="w-3 h-3 bg-red-100 border border-red-500 rounded-full mr-2"></span>
            <span>Occupied - Kamar sedang diisi tamu</span>
        </div>
        <div class="flex items-center">
            <span class="w-3 h-3 bg-orange-100 border border-orange-500 rounded-full mr-2"></span>
            <span>Due Out - Tamu akan check-out hari ini</span>
        </div>
        <div class="flex items-center">
            <span class="w-3 h-3 bg-yellow-100 border border-yellow-500 rounded-full mr-2"></span>
            <span>Cleaning - Kamar sedang dibersihkan</span>
        </div>
        <div class="flex items-center">
            <span class="w-3 h-3 bg-gray-100 border border-gray-500 rounded-full mr-2"></span>
            <span>Maintenance - Kamar sedang dalam perbaikan</span>
        </div>
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
                        <td class="p-2"><span class="px-2 py-1 rounded text-xs bg-yellow-100 text-yellow-800">Pending</span></td>
                        <td class="p-2"><a href="{{ route('checkin.process') }}?reservation_id={{ $booking->id }}" class="text-blue-600 hover:underline">Check-in</a></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <p class="text-gray-500">Tidak ada booking mendatang.</p>
    @endif
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
    <div class="bg-white rounded-lg shadow p-4">
        <h3 class="font-bold text-lg mb-3"><i class="fas fa-user-check text-green-500 mr-2"></i>Check-in Hari Ini</h3>
        @php
            $todayCheckins = \App\Models\Reservation::with(['guest', 'room'])
                ->whereDate('check_in', '>=', $dateFrom)
                ->whereDate('check_in', '<=', $dateTo)
                ->where('status', 'pending')
                ->orderBy('check_in')
                ->limit(10)
                ->get();
        @endphp
        @if($todayCheckins->count() > 0)
            <table class="min-w-full">
                <thead><tr class="border-b">
                    <th class="text-left p-2 text-sm">Nama Tamu</th>
                    <th class="text-left p-2 text-sm">Kamar</th>
                    <th class="text-left p-2 text-sm">Check-in</th>
                    <th class="text-left p-2 text-sm">Check-out</th>
                    <th class="text-left p-2 text-sm">Status</th>
                </tr></thead>
                <tbody>
                    @foreach($todayCheckins as $res)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="p-2 font-medium">{{ $res->guest->guest_name ?? '-' }}</td>
                        <td class="p-2">{{ $res->room->room_number ?? '-' }}</td>
                        <td class="p-2 text-sm">{{ $res->check_in->format('d/m/Y H:i') }}</td>
                        <td class="p-2 text-sm">{{ $res->check_out->format('d/m/Y H:i') }}</td>
                        <td class="p-2"><span class="px-2 py-1 rounded text-xs bg-yellow-100 text-yellow-800">Pending</span></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p class="text-gray-500 text-center py-4">Tidak ada check-in pada tanggal ini</p>
        @endif
    </div>

    <div class="bg-white rounded-lg shadow p-4">
        <h3 class="font-bold text-lg mb-3"><i class="fas fa-user-times text-yellow-500 mr-2"></i>Check-out</h3>
        @php
            $todayCheckouts = \App\Models\Reservation::with(['guest', 'room'])
                ->whereDate('check_out', '>=', $dateFrom)
                ->whereDate('check_out', '<=', $dateTo)
                ->where('status', 'checked_in')
                ->orderBy('check_out')
                ->limit(10)
                ->get();
        @endphp
        @if($todayCheckouts->count() > 0)
            <table class="min-w-full">
                <thead><tr class="border-b">
                    <th class="text-left p-2 text-sm">Nama Tamu</th>
                    <th class="text-left p-2 text-sm">Kamar</th>
                    <th class="text-left p-2 text-sm">Check-in</th>
                    <th class="text-left p-2 text-sm">Check-out</th>
                    <th class="text-left p-2 text-sm">Status</th>
                </tr></thead>
                <tbody>
                    @foreach($todayCheckouts as $res)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="p-2 font-medium">{{ $res->guest->guest_name ?? '-' }}</td>
                        <td class="p-2">{{ $res->room->room_number ?? '-' }}</td>
                        <td class="p-2 text-sm">{{ $res->check_in->format('d/m/Y H:i') }}</td>
                        <td class="p-2 text-sm">{{ $res->check_out->format('d/m/Y H:i') }}</td>
                        <td class="p-2"><span class="px-2 py-1 rounded text-xs bg-green-100 text-green-800">Checked In</span></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p class="text-gray-500 text-center py-4">Tidak ada check-out pada tanggal ini</p>
        @endif
    </div>
</div>


@section('scripts')
<script src="{{ asset('js/rooms-dashboard.js') }}"></script>
@endsection