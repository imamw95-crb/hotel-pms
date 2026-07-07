@extends('layouts.app')

@section('title', 'Pindah Kamar')
@section('header', 'Pindah Kamar')

@section('content')
<!-- Stats -->
<div class="stats-grid mb-6">
    <div class="bg-red-50 border border-red-200 rounded-lg p-4 text-center">
        <p class="text-xs text-red-600 font-medium">Reservasi Aktif</p>
        <p class="text-2xl font-bold text-red-600">{{ $reservations->count() }}</p>
    </div>
    <div class="bg-green-50 border border-green-200 rounded-lg p-4 text-center">
        <p class="text-xs text-green-600 font-medium">Kamar Available</p>
        <p class="text-2xl font-bold text-green-600">{{ $availableRooms->count() }}</p>
    </div>
</div>

<!-- Search & Filter -->
<div class="bg-white rounded-lg shadow mb-4">
    <div class="p-4 border-b border-gray-100">
        <div class="flex flex-col sm:flex-row sm:items-center gap-3">
            <!-- Search -->
            <div class="relative flex-1 max-w-sm">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                <input type="text" id="searchInput" placeholder="Cari no. reservasi, nama tamu, no. kamar..." value="{{ $search ?? '' }}"
                       class="w-full pl-9 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-blue-400">
            </div>
            <!-- Date Filter -->
            <div class="flex items-center gap-2 flex-wrap">
                <label class="text-sm text-gray-600 font-medium whitespace-nowrap"><i class="fas fa-calendar-alt mr-1"></i>Check-in:</label>
                <input type="date" id="dateFrom" value="{{ $dateFrom ?? '' }}"
                       class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-blue-400 w-40">
                <span class="text-gray-400 text-sm">–</span>
                <input type="date" id="dateTo" value="{{ $dateTo ?? '' }}"
                       class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-blue-400 w-40">
                <button id="clearDateFilter" class="text-sm text-gray-500 hover:text-red-500 transition px-2 py-2 rounded hover:bg-red-50" title="Hapus filter tanggal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="text-sm text-gray-500 ml-auto whitespace-nowrap">
                <span id="resultCount">{{ $reservations->count() }}</span> reservasi
            </div>
        </div>
    </div>

    <!-- Tabel Pindah Kamar -->
    <div class="overflow-x-auto">
        <table class="min-w-full" id="roomChangeTable">
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
                <tr class="border-b hover:bg-gray-50 search-row"
                    data-checkin="{{ $res->check_in->format('Y-m-d') }}"
                    data-checkout="{{ $res->check_out->format('Y-m-d') }}">
                    <td class="p-3 font-medium text-blue-600 search-data">{{ $res->reservation_number }}</td>
                    <td class="p-3 search-data">
                        <div class="font-medium">{{ $res->guest->guest_name ?? '-' }}</div>
                        <div class="text-xs text-gray-500">{{ $res->guest->phone ?? '' }}</div>
                    </td>
                    <td class="p-3 search-data">
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
                <tr id="emptyRow">
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

<!-- Empty state for search no results (hidden by default) -->
<div id="noSearchResults" class="bg-white rounded-lg shadow p-8 text-center text-gray-500 hidden">
    <i class="fas fa-search text-4xl mb-2 text-gray-300"></i>
    <p class="text-lg font-medium text-gray-400">Tidak ada hasil ditemukan</p>
    <p class="text-sm text-gray-400 mt-1">Coba ubah kata kunci atau filter tanggal untuk mencari reservasi</p>
</div>

<script>
    (function() {
        var searchInput = document.getElementById('searchInput');
        var dateFrom = document.getElementById('dateFrom');
        var dateTo = document.getElementById('dateTo');
        var clearBtn = document.getElementById('clearDateFilter');
        var rows = document.querySelectorAll('.search-row');
        var resultCount = document.getElementById('resultCount');
        var noResults = document.getElementById('noSearchResults');
        var table = document.getElementById('roomChangeTable');

        function parseDate(str) {
            if (!str) return null;
            var parts = str.split('-');
            return new Date(parseInt(parts[0]), parseInt(parts[1]) - 1, parseInt(parts[2]));
        }

        function filterTable() {
            var keyword = searchInput.value.toLowerCase().trim();
            var df = dateFrom.value;
            var dt = dateTo.value;
            var dateFromVal = parseDate(df);
            var dateToVal = parseDate(dt);
            var visibleCount = 0;

            rows.forEach(function(row) {
                var searchCells = row.querySelectorAll('.search-data');
                var textMatch = false;

                searchCells.forEach(function(cell) {
                    if (cell.textContent.toLowerCase().includes(keyword)) {
                        textMatch = true;
                    }
                });

                if (!keyword) textMatch = true;

                // Date filter
                var dateMatch = true;
                var checkinStr = row.getAttribute('data-checkin');
                if (dateFromVal || dateToVal) {
                    var checkinDate = parseDate(checkinStr);
                    if (checkinDate) {
                        if (dateFromVal && checkinDate < dateFromVal) dateMatch = false;
                        if (dateToVal && checkinDate > dateToVal) dateMatch = false;
                    }
                }

                if (textMatch && dateMatch) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });

            resultCount.textContent = visibleCount;

            if (rows.length > 0) {
                if (visibleCount === 0) {
                    table.classList.add('hidden');
                    noResults.classList.remove('hidden');
                } else {
                    table.classList.remove('hidden');
                    noResults.classList.add('hidden');
                }
            }
        }

        searchInput.addEventListener('input', filterTable);
        dateFrom.addEventListener('change', filterTable);
        dateTo.addEventListener('change', filterTable);

        clearBtn.addEventListener('click', function() {
            dateFrom.value = '';
            dateTo.value = '';
            filterTable();
        });
    })();
</script>
@endsection
