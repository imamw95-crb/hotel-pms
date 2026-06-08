@extends('layouts.app')

@section('title', 'Dashboard Kamar')
@section('header', 'Dashboard Kamar')

@section('content')
<div class="max-w-full">
    {{-- Stats Bar (sama seperti room-rack) --}}
    @php
        $totalRooms = $rooms->count();
        $occupiedNow = $rooms->where('status', 'occupied')->count();
        $dirtyCount = $rooms->where('status', 'cleaning')->count();
        $maintCount = $rooms->where('status', 'maintenance')->count();
        $oooCount = $rooms->where('status', 'out_of_order')->count();
        $effectiveTotal = $totalRooms - $oooCount;
        $occupancyPct = $effectiveTotal > 0 ? round(($occupiedNow / $effectiveTotal) * 100) : 0;
    @endphp
    <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-7 gap-3 mb-6">
        <div class="bg-emerald-50 border border-emerald-200 rounded-lg p-3 text-center">
            <p class="text-xs text-emerald-700 font-medium">Available</p>
            <p class="text-2xl font-bold text-emerald-600"><span id="availableCount">{{ $availableRoomsCount }}</span></p>
        </div>
        <div class="bg-red-50 border border-red-200 rounded-lg p-3 text-center">
            <p class="text-xs text-red-700 font-medium">Occupied</p>
            <p class="text-2xl font-bold text-red-600"><span id="statOccupied">{{ $occupiedNow }}</span></p>
        </div>
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 text-center">
            <p class="text-xs text-blue-700 font-medium">Check-in</p>
            <p class="text-2xl font-bold text-blue-600"><span id="checkinsCount">{{ $checkinsToday }}</span></p>
        </div>
        <div class="bg-amber-50 border border-amber-200 rounded-lg p-3 text-center">
            <p class="text-xs text-amber-700 font-medium">Check-out</p>
            <p class="text-2xl font-bold text-amber-600"><span id="checkoutsCount">{{ $checkoutsToday }}</span></p>
        </div>
        <div class="bg-gray-50 border border-gray-200 rounded-lg p-3 text-center">
            <p class="text-xs text-gray-700 font-medium">Dirty</p>
            <p class="text-2xl font-bold text-gray-600">{{ $dirtyCount }}</p>
        </div>
        <div class="bg-purple-50 border border-purple-200 rounded-lg p-3 text-center">
            <p class="text-xs text-purple-700 font-medium">Maintenance</p>
            <p class="text-2xl font-bold text-purple-600">{{ $maintCount }}</p>
        </div>
        <div class="bg-red-50 border border-red-200 rounded-lg p-3 text-center">
            <p class="text-xs text-red-700 font-medium">Out of Order</p>
            <p class="text-2xl font-bold text-red-600"><span id="oooCount">{{ $rooms->where('status', 'out_of_order')->count() }}</span></p>
        </div>
        <div class="bg-slate-50 border border-slate-200 rounded-lg p-3 text-center">
            <p class="text-xs text-slate-700 font-medium">Okupansi</p>
            <p class="text-2xl font-bold text-slate-600">{{ $occupancyPct }}%</p>
        </div>
    </div>

    {{-- Tab Selector --}}
    <div class="flex gap-1 mb-4 bg-gray-100 p-1 rounded-lg w-fit">
        <button onclick="switchTab('grid')" data-tab="grid" class="px-4 py-2 text-sm font-medium rounded-md bg-white shadow-sm">Grid Kamar</button>
    </div>

    {{-- === GRID VIEW === --}}
    <div id="tab-grid">
        <!-- Dashboard Data Attributes -->
        <div id="roomsDashboard" 
             data-rooms-api="{{ route('rooms.api') }}" 
             data-booking-url="{{ route('booking.create') }}"
             data-ota-booking-url="{{ route('booking.ota-create') }}"
             data-date-from="{{ $dateFrom }}"
             data-date-to="{{ $dateTo }}"
             data-status-filter="{{ $statusFilter }}"
             data-room-type-filter="{{ $roomTypeFilter }}"
             class="hidden"></div>

        <div id="bulkActionPanel" class="hidden mb-4 p-3 bg-blue-50 border border-blue-200 rounded-lg flex items-center justify-between">
            <div class="flex items-center gap-3">
                <span class="text-sm font-medium text-blue-700"><span id="bulkSelectedCount">0</span> kamar dipilih</span>
                <button data-bulk-action data-bulk-checkin class="px-3 py-1 bg-green-500 text-white rounded text-sm opacity-50" disabled><i class="fas fa-sign-in-alt mr-1"></i> Check-in</button>
                <button data-bulk-action data-bulk-checkout class="px-3 py-1 bg-yellow-500 text-white rounded text-sm opacity-50" disabled><i class="fas fa-sign-out-alt mr-1"></i> Check-out</button>
                <button data-bulk-action data-bulk-available class="px-3 py-1 bg-emerald-500 text-white rounded text-sm opacity-50" disabled><i class="fas fa-check mr-1"></i> Available</button>
                <button data-bulk-action data-bulk-maintenance class="px-3 py-1 bg-gray-500 text-white rounded text-sm opacity-50" disabled><i class="fas fa-tools mr-1"></i> Maintenance</button>
            </div>
            <button onclick="RoomsDashboard.toggleBulkMode()" class="text-sm text-blue-600 hover:text-blue-800"><i class="fas fa-times"></i> Tutup</button>
        </div>

        <div class="bg-white rounded-xl shadow-sm border p-4 mb-6">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 mb-4">
                <h2 class="text-lg font-bold">Denah Kamar</h2>
                <div class="flex items-center gap-2 overflow-x-auto pb-1">
                    <select data-filter-status class="border rounded px-2 py-1 text-sm flex-shrink-0">
                        <option value="all" {{ $statusFilter === 'all' ? 'selected' : '' }}>Semua Status</option>
                        <option value="available" {{ $statusFilter === 'available' ? 'selected' : '' }}>Available</option>
                        <option value="occupied" {{ $statusFilter === 'occupied' ? 'selected' : '' }}>Occupied</option>
                        <option value="due_out" {{ $statusFilter === 'due_out' ? 'selected' : '' }}>Due Out</option>
                        <option value="cleaning" {{ $statusFilter === 'cleaning' ? 'selected' : '' }}>Cleaning</option>
                        <option value="maintenance" {{ $statusFilter === 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                        <option value="out_of_order" {{ $statusFilter === 'out_of_order' ? 'selected' : '' }}>Out of Order</option>
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
                    <button onclick="RoomsDashboard.refresh()" class="bg-blue-600 text-white px-3 py-1 rounded text-sm hover:bg-blue-700 transition flex-shrink-0" title="Refresh"><i class="fas fa-sync-alt"></i></button>
                    <button data-bulk-toggle class="bg-purple-500 text-white px-3 py-1 rounded text-sm hover:bg-purple-600 transition flex-shrink-0" title="Bulk"><i class="fas fa-check-square"></i></button>
                    <button onclick="RoomsDashboard.openOtaBooking()" class="bg-teal-600 text-white px-3 py-1 rounded text-sm hover:bg-teal-700 transition flex-shrink-0"><i class="fas fa-globe"></i> OTA</button>
                    <button onclick="Modal.open('{{ route('booking.group.create') }}')" class="bg-green-600 text-white px-3 py-1 rounded text-sm hover:bg-green-700 transition flex-shrink-0"><i class="fas fa-users"></i> Group</button>
                </div>
            </div>
            <div class="rooms-grid min-h-[200px]" id="roomsGrid">
                @include('room-rack.partials.room-grid-cards')
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function switchTab(tab) {
    document.querySelectorAll('[data-tab]').forEach(function(btn) {
        btn.classList.remove('bg-white', 'shadow-sm');
        btn.classList.add('text-gray-600');
    });
    document.querySelector('[data-tab="' + tab + '"]').classList.add('bg-white', 'shadow-sm');
    document.querySelector('[data-tab="' + tab + '"]').classList.remove('text-gray-600');

    document.getElementById('tab-grid').classList.toggle('hidden', tab !== 'grid');
}
</script>
<script src="{{ asset('js/rooms-dashboard.js') }}?v={{ filemtime(public_path('js/rooms-dashboard.js')) }}"></script>
@endsection