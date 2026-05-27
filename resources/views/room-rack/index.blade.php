@extends('layouts.app')

@section('title', 'Room Rack')
@section('header', 'Room Rack — Availability')

@section('content')
<div class="max-w-full">
    {{-- Stats Bar --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-7 gap-3 mb-6" id="statsBar">
        <div class="bg-emerald-50 border border-emerald-200 rounded-lg p-3 text-center">
            <p class="text-xs text-emerald-700 font-medium">Available</p>
            <p class="text-2xl font-bold text-emerald-600"><span id="availableCount">{{ $stats['available_now'] }}</span></p>
        </div>
        <div class="bg-red-50 border border-red-200 rounded-lg p-3 text-center">
            <p class="text-xs text-red-700 font-medium">Occupied</p>
            <p class="text-2xl font-bold text-red-600"><span id="statOccupied">{{ $stats['occupied_now'] }}</span></p>
        </div>
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 text-center">
            <p class="text-xs text-blue-700 font-medium">Check-in</p>
            <p class="text-2xl font-bold text-blue-600"><span id="checkinsCount">{{ $stats['checkins_today'] }}</span></p>
        </div>
        <div class="bg-amber-50 border border-amber-200 rounded-lg p-3 text-center">
            <p class="text-xs text-amber-700 font-medium">Check-out</p>
            <p class="text-2xl font-bold text-amber-600"><span id="checkoutsCount">{{ $stats['checkouts_today'] }}</span></p>
        </div>
        <div class="bg-gray-50 border border-gray-200 rounded-lg p-3 text-center">
            <p class="text-xs text-gray-700 font-medium">Dirty</p>
            <p class="text-2xl font-bold text-gray-600">{{ $stats['dirty'] }}</p>
        </div>
        <div class="bg-purple-50 border border-purple-200 rounded-lg p-3 text-center">
            <p class="text-xs text-purple-700 font-medium">Maintenance</p>
            <p class="text-2xl font-bold text-purple-600">{{ $stats['maintenance'] }}</p>
        </div>
        <div class="bg-slate-50 border border-slate-200 rounded-lg p-3 text-center">
            <p class="text-xs text-slate-700 font-medium">Okupansi</p>
            <p class="text-2xl font-bold text-slate-600" id="statOccupancy">—</p>
        </div>
    </div>

    {{-- Tab Selector: Grid | Rack | Forecast --}}
    <div class="flex gap-1 mb-4 bg-gray-100 p-1 rounded-lg w-fit">
        <button onclick="switchTab('grid')" data-tab="grid" class="px-4 py-2 text-sm font-medium rounded-md bg-white shadow-sm">Grid Kamar</button>
        <button onclick="switchTab('rack')" data-tab="rack" class="px-4 py-2 text-sm font-medium rounded-md text-gray-600 hover:bg-white/50">Room Rack</button>
        <button onclick="switchTab('forecast')" data-tab="forecast" class="px-4 py-2 text-sm font-medium rounded-md text-gray-600 hover:bg-white/50">Forecast</button>
    </div>

    {{-- === GRID VIEW (Rooms Dashboard) === --}}
    <div id="tab-grid">
        <!-- Dashboard Data Attributes -->
        <div id="roomsDashboard" 
             data-rooms-api="{{ route('rooms.api') }}" 
             data-booking-url="{{ route('booking.create') }}"
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
                    <button onclick="Modal.open('{{ route('booking.group.create') }}')" class="bg-green-600 text-white px-3 py-1 rounded text-sm hover:bg-green-700 transition flex-shrink-0"><i class="fas fa-users"></i> Group</button>
                </div>
            </div>
            <div class="rooms-grid min-h-[200px]" id="roomsGrid">
                @include('room-rack.partials.room-grid-cards')
            </div>
        </div>
    </div>

    {{-- === RACK VIEW (Timeline) === --}}
    <div id="tab-rack" class="hidden">
        <div class="bg-white rounded-xl shadow-sm border p-4 mb-6">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <h2 class="text-lg font-bold">Room Rack Timeline</h2>
                    <span class="text-xs text-gray-400">
                        <span class="inline-block w-3 h-3 rounded bg-emerald-400 align-middle mr-1"></span>Available
                        <span class="inline-block w-3 h-3 rounded bg-red-400 align-middle mx-1"></span>Occupied
                        <span class="inline-block w-3 h-3 rounded bg-amber-400 align-middle mx-1"></span>Due Out
                        <span class="inline-block w-3 h-3 rounded bg-gray-300 align-middle mx-1"></span>Dirty
                        <span class="inline-block w-3 h-3 rounded bg-purple-300 align-middle mx-1"></span>Maint
                    </span>
                </div>
                <div class="flex items-center gap-2 flex-wrap">
                    <input type="date" id="rackStartDate" value="{{ $startDate->format('Y-m-d') }}" class="border rounded px-2 py-1 text-sm w-[140px]">
                    <select id="rackDays" class="border rounded px-2 py-1 text-sm">
                        <option value="7" {{ $days == 7 ? 'selected' : '' }}>7 hari</option>
                        <option value="14" {{ $days == 14 ? 'selected' : '' }}>14 hari</option>
                        <option value="30" {{ $days == 30 ? 'selected' : '' }}>30 hari</option>
                        <option value="60" {{ $days == 60 ? 'selected' : '' }}>60 hari</option>
                    </select>
                    <button onclick="loadRack()" class="bg-blue-600 text-white px-3 py-1 rounded text-sm hover:bg-blue-700 transition"><i class="fas fa-sync-alt mr-1"></i> Load</button>
                    <a href="{{ route('room-rack.occupancy') }}" class="bg-purple-600 text-white px-3 py-1 rounded text-sm hover:bg-purple-700 transition"><i class="fas fa-calendar-alt mr-1"></i> Calendar</a>
                </div>
            </div>
        </div>
        <div id="rackContainer" class="bg-white rounded-xl shadow-sm border overflow-x-auto">
            @include('room-rack.partials.rack-table')
        </div>
    </div>

    {{-- === FORECAST === --}}
    <div id="tab-forecast" class="hidden">
        <div class="bg-white rounded-xl shadow-sm border p-4">
            <h3 class="font-bold text-lg mb-3"><i class="fas fa-chart-line text-blue-500 mr-2"></i>Forecast 30 Hari</h3>
            <div class="overflow-x-auto">
                <div class="flex gap-2 min-w-max pb-2" id="forecastBar">
                    @foreach($forecast as $day)
                        @php
                            $barColor = $day['occupancy_pct'] >= 90 ? 'bg-red-500' : ($day['occupancy_pct'] >= 70 ? 'bg-amber-500' : 'bg-emerald-500');
                        @endphp
                        <div class="flex flex-col items-center w-10 flex-shrink-0">
                            <div class="text-[10px] text-gray-500 mb-1">{{ $day['occupancy_pct'] }}%</div>
                            <div class="h-20 w-6 bg-gray-100 rounded-full relative overflow-hidden">
                                <div class="absolute bottom-0 w-full rounded-full {{ $barColor }}" 
                                     style="height: {{ $day['occupancy_pct'] }}%; transition: height 0.5s;"></div>
                            </div>
                            <div class="text-[9px] text-gray-400 mt-1">{{ $day['label'] }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('rackStartDate')?.addEventListener('change', loadRack);
    document.getElementById('rackDays')?.addEventListener('change', loadRack);
});

function loadRack() {
    var startDate = document.getElementById('rackStartDate').value;
    var days = document.getElementById('rackDays').value;
    var container = document.getElementById('rackContainer');
    if (!container) return;
    container.innerHTML = '<div class="p-8 text-center text-gray-500"><i class="fas fa-spinner fa-spin text-2xl"></i><p class="mt-2">Memuat...</p></div>';

    var xhr = new XMLHttpRequest();
    xhr.open('GET', '/room-rack?start_date=' + startDate + '&days=' + days, true);
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    xhr.setRequestHeader('Accept', 'application/json');
    xhr.onload = function() {
        if (xhr.status >= 200 && xhr.status < 300) {
            try {
                var data = JSON.parse(xhr.responseText);
                if (data.success && data.view) {
                    container.innerHTML = data.view;
                }
            } catch(e) { container.innerHTML = '<div class="p-8 text-center text-red-500">Error loading</div>'; }
        }
    };
    xhr.send();
}

// Tab switching
function switchTab(tab) {
    document.querySelectorAll('[data-tab]').forEach(function(btn) {
        btn.classList.remove('bg-white', 'shadow-sm');
        btn.classList.add('text-gray-600');
    });
    document.querySelector('[data-tab="' + tab + '"]').classList.add('bg-white', 'shadow-sm');
    document.querySelector('[data-tab="' + tab + '"]').classList.remove('text-gray-600');

    document.getElementById('tab-grid').classList.toggle('hidden', tab !== 'grid');
    document.getElementById('tab-rack').classList.toggle('hidden', tab !== 'rack');
    document.getElementById('tab-forecast').classList.toggle('hidden', tab !== 'forecast');

    // Init grid if needed
    if (tab === 'grid' && document.getElementById('roomsGrid') && typeof RoomsDashboard === 'undefined') {
        var s = document.createElement('script');
        s.src = '{{ asset("js/rooms-dashboard.js") }}?v={{ filemtime(public_path('js/rooms-dashboard.js')) }}';
        document.body.appendChild(s);
    }
}
</script>
<script src="{{ asset('js/rooms-dashboard.js') }}?v={{ filemtime(public_path('js/rooms-dashboard.js')) }}"></script>
@endsection
