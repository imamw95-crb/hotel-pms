@extends('layouts.app')

@section('title', 'Dashboard - ' . ucfirst(auth()->user()->role))
@section('header', 'Dashboard ' . ucfirst(auth()->user()->role))

@section('content')
    {{-- OWNER DASHBOARD --}}
    @if(auth()->user()->isOwner() || auth()->user()->isUserManager())
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="bg-blue-500 rounded-full p-3 mr-4">
                        <i class="fas fa-bed text-white text-xl"></i>
                    </div>
                    <div>
                        <p class="text-gray-500 text-sm">Kamar Terisi</p>
                        <p class="text-2xl font-bold">{{ $occupiedRooms }} / {{ $totalRooms }}</p>
                        <p class="text-sm text-green-500">Okupansi {{ $occupancyRate }}%</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="bg-green-500 rounded-full p-3 mr-4">
                        <i class="fas fa-money-bill text-white text-xl"></i>
                    </div>
                    <div>
                        <p class="text-gray-500 text-sm">Pendapatan Hari Ini</p>
                        <p class="text-2xl font-bold">Rp {{ number_format($todayRevenue, 0, ',', '.') }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="bg-yellow-500 rounded-full p-3 mr-4">
                        <i class="fas fa-chart-line text-white text-xl"></i>
                    </div>
                    <div>
                        <p class="text-gray-500 text-sm">Pendapatan Bulan Ini</p>
                        <p class="text-2xl font-bold">Rp {{ number_format($monthRevenue, 0, ',', '.') }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="bg-purple-500 rounded-full p-3 mr-4">
                        <i class="fas fa-exchange-alt text-white text-xl"></i>
                    </div>
                    <div>
                        <p class="text-gray-500 text-sm">Check-in / Check-out</p>
                        <p class="text-2xl font-bold">{{ $checkinsToday }} / {{ $checkoutsToday }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Auto-Cancel Button --}}
        <div class="flex justify-end mb-2">
            <button onclick="autoCancelPending(this)" data-url="{{ route('dashboard.auto-cancel-pending') }}" class="bg-red-500 hover:bg-red-600 text-white text-sm px-4 py-2 rounded-lg shadow transition">
                <i class="fas fa-times-circle mr-1"></i> Batalkan Booking Pending >3 Jam (From WEB)
            </button>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="font-bold text-lg mb-4">Okupansi 7 Hari Terakhir</h3>
                <canvas id="occupancyChart" height="200"></canvas>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="font-bold text-lg mb-4">Pendapatan 7 Hari Terakhir</h3>
                <canvas id="revenueChart" height="200"></canvas>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="font-bold text-lg mb-4">Reservasi Terbaru</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr class="border-b">
                            <th class="text-left p-2">No. Reservasi</th>
                            <th class="text-left p-2">Guest</th>
                            <th class="text-left p-2">Kamar</th>
                            <th class="text-left p-2">Check-in</th>
                            <th class="text-left p-2">Check-out</th>
                            <th class="text-left p-2">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentReservations as $res)
                        <tr class="border-b">
                            <td class="p-2">{{ $res->reservation_number }}</td>
                            <td class="p-2">{{ $res->guest->guest_name ?? '-' }}</td>
                            <td class="p-2">{{ $res->room->room_number ?? '-' }}</td>
                            <td class="p-2">{{ $res->check_in->format('d/m/Y H:i') }}</td>
                            <td class="p-2">{{ $res->check_out->format('d/m/Y H:i') }}</td>
                            <td class="p-2">
                                <span class="px-2 py-1 rounded text-xs font-bold
                                    @if($res->status == 'checked_in') bg-green-100 text-green-800
                                    @elseif($res->status == 'pending') bg-yellow-100 text-yellow-800
                                    @elseif($res->status == 'checked_out') bg-blue-100 text-blue-800
                                    @else bg-gray-100 text-gray-800 @endif">
                                    {{ strtoupper($res->status) }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="p-4 text-center text-gray-500">Belum ada data reservasi</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>



    {{-- ADMIN DASHBOARD --}}
    @elseif(auth()->user()->isAdmin())
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="bg-white p-5 rounded shadow">
                <div class="text-gray-500">Total Kamar</div>
                <div class="text-3xl font-bold">{{ $totalRooms }}</div>
            </div>
            <div class="bg-white p-5 rounded shadow">
                <div class="text-gray-500">Total Reservasi</div>
                <div class="text-3xl font-bold">{{ $totalReservations }}</div>
            </div>
            <div class="bg-white p-5 rounded shadow">
                <div class="text-gray-500">Total Pendapatan</div>
                <div class="text-3xl font-bold">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="font-bold text-lg mb-4"><i class="fas fa-user-check text-green-500 mr-2"></i>Check-in Hari Ini</h3>
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
                            <thead><tr class="border-b">
                                <th class="text-left p-2 text-sm">Nama Tamu</th>
                                <th class="text-left p-2 text-sm">Kamar</th>
                                <th class="text-left p-2 text-sm">Check-out</th>
                            </tr></thead>
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
                    </div>
                @else
                    <p class="text-gray-500 text-center py-4">Tidak ada check-in hari ini</p>
                @endif
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="font-bold text-lg mb-4"><i class="fas fa-user-times text-yellow-500 mr-2"></i>Check-out Hari Ini</h3>
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
                            <thead><tr class="border-b">
                                <th class="text-left p-2 text-sm">Nama Tamu</th>
                                <th class="text-left p-2 text-sm">Kamar</th>
                                <th class="text-left p-2 text-sm">Check-in</th>
                            </tr></thead>
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
        </div>

        {{-- Housekeeping Widget --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="font-bold text-lg mb-4"><i class="fas fa-broom text-yellow-500 mr-2"></i>Housekeeping Status</h3>
                @php
                    $hkPending = \App\Models\HousekeepingTask::where('status', 'pending')->count();
                    $hkInProgress = \App\Models\HousekeepingTask::where('status', 'in_progress')->count();
                    $hkUrgent = \App\Models\HousekeepingTask::urgent()->count();
                    $hkTodayCompleted = \App\Models\HousekeepingTask::where('status', 'completed')
                        ->whereDate('completed_at', \Carbon\Carbon::today())->count();
                @endphp
                <div class="grid grid-cols-2 gap-3 mb-4">
                    <div class="bg-yellow-50 p-3 rounded text-center">
                        <p class="text-2xl font-bold text-yellow-600">{{ $hkPending }}</p>
                        <p class="text-xs text-gray-500">Menunggu</p>
                    </div>
                    <div class="bg-blue-50 p-3 rounded text-center">
                        <p class="text-2xl font-bold text-blue-600">{{ $hkInProgress }}</p>
                        <p class="text-xs text-gray-500">Dikerjakan</p>
                    </div>
                    <div class="bg-red-50 p-3 rounded text-center">
                        <p class="text-2xl font-bold text-red-600">{{ $hkUrgent }}</p>
                        <p class="text-xs text-gray-500">Urgent</p>
                    </div>
                    <div class="bg-green-50 p-3 rounded text-center">
                        <p class="text-2xl font-bold text-green-600">{{ $hkTodayCompleted }}</p>
                        <p class="text-xs text-gray-500">Selesai Hari Ini</p>
                    </div>
                </div>
                <a href="{{ route('housekeeping.index') }}" class="text-blue-500 hover:underline text-sm">
                    <i class="fas fa-arrow-right mr-1"></i> Kelola Housekeeping
                </a>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="font-bold text-lg mb-4"><i class="fas fa-door-open text-green-500 mr-2"></i>Status Kamar</h3>
                @php
                    $roomStatsAvail = \App\Models\Room::where('status', 'available')->count();
                    $roomStatsCleaning = \App\Models\Room::where('status', 'cleaning')->count();
                    $roomStatsOccupied = \App\Models\Room::where('status', 'occupied')->count();
                    $roomStatsMaint = \App\Models\Room::where('status', 'maintenance')->count();
                    $roomStatsOoo = \App\Models\Room::where('status', 'out_of_order')->count();
                @endphp
                <div class="grid grid-cols-2 gap-3">
                    <div class="bg-green-50 p-3 rounded text-center">
                        <p class="text-2xl font-bold text-green-600">{{ $roomStatsAvail }}</p>
                        <p class="text-xs text-gray-500">Available</p>
                    </div>
                    <div class="bg-yellow-50 p-3 rounded text-center">
                        <p class="text-2xl font-bold text-yellow-600">{{ $roomStatsCleaning }}</p>
                        <p class="text-xs text-gray-500">Cleaning</p>
                    </div>
                    <div class="bg-red-50 p-3 rounded text-center">
                        <p class="text-2xl font-bold text-red-600">{{ $roomStatsOccupied }}</p>
                        <p class="text-xs text-gray-500">Terisi</p>
                    </div>
                    <div class="bg-gray-50 p-3 rounded text-center">
                        <p class="text-2xl font-bold text-gray-600">{{ $roomStatsMaint }}</p>
                        <p class="text-xs text-gray-500">Maintenance</p>
                    </div>
                    <div class="bg-purple-50 p-3 rounded text-center">
                        <p class="text-2xl font-bold text-purple-600">{{ $roomStatsOoo }}</p>
                        <p class="text-xs text-gray-500">Out of Order</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex justify-end mt-6 mb-2">
            <button onclick="autoCancelPending(this)" data-url="{{ route('auto-cancel-pending') }}" class="bg-red-500 hover:bg-red-600 text-white text-sm px-4 py-2 rounded-lg shadow transition">
                <i class="fas fa-times-circle mr-1"></i> Batalkan Booking Pending >3 Jam (From WEB)
            </button>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="font-bold text-lg mb-4"><i class="fas fa-history text-blue-500 mr-2"></i>Reservasi Terbaru</h3>
            @php
                $recentReservations = \App\Models\Reservation::with(['guest', 'room'])
                    ->orderBy('created_at', 'desc')
                    ->limit(10)
                    ->get();
            @endphp
            @if($recentReservations->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead><tr class="border-b">
                            <th class="text-left p-2 text-sm">No. Reservasi</th>
                            <th class="text-left p-2 text-sm">Nama Tamu</th>
                            <th class="text-left p-2 text-sm">Kamar</th>
                            <th class="text-left p-2 text-sm">Check-in</th>
                            <th class="text-left p-2 text-sm">Check-out</th>
                            <th class="text-left p-2 text-sm">Status</th>
                        </tr></thead>
                        <tbody>
                            @foreach($recentReservations as $res)
                            <tr class="border-b hover:bg-gray-50">
                                <td class="p-2 font-medium">{{ $res->reservation_number }}</td>
                                <td class="p-2">{{ $res->guest->guest_name ?? '-' }}</td>
                                <td class="p-2">{{ $res->room->room_number ?? '-' }}</td>
                                <td class="p-2 text-sm">{{ $res->check_in->format('d/m/Y H:i') }}</td>
                                <td class="p-2 text-sm">{{ $res->check_out->format('d/m/Y H:i') }}</td>
                                <td class="p-2">
                                    <span class="px-2 py-1 rounded text-xs font-bold
                                        @if($res->status == 'checked_in') bg-green-100 text-green-800
                                        @elseif($res->status == 'pending') bg-yellow-100 text-yellow-800
                                        @elseif($res->status == 'checked_out') bg-blue-100 text-blue-800
                                        @else bg-gray-100 text-gray-800 @endif">
                                        {{ strtoupper($res->status) }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-gray-500 text-center py-4">Belum ada data reservasi</p>
            @endif
        </div>

    {{-- FRONT OFFICE DASHBOARD --}}
    @else
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

        <div class="bg-white rounded-lg shadow p-6 mt-6">
            <h3 class="font-bold text-lg mb-4"><i class="fas fa-user-check text-green-500 mr-2"></i>Tamu Check-in Hari Ini</h3>
            @php
                $foCheckins = \App\Models\Reservation::with(['guest', 'room'])
                    ->whereDate('check_in', \Carbon\Carbon::today())
                    ->where('status', 'pending')
                    ->orderBy('check_in')
                    ->limit(10)
                    ->get();
            @endphp
            @if($foCheckins->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead><tr class="border-b">
                            <th class="text-left p-2 text-sm">Nama Tamu</th>
                            <th class="text-left p-2 text-sm">Kamar</th>
                            <th class="text-left p-2 text-sm">Check-out</th>
                            <th class="text-left p-2 text-sm">Aksi</th>
                        </tr></thead>
                        <tbody>
                            @foreach($foCheckins as $res)
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

        <div class="bg-white rounded-lg shadow p-6 mt-6">
            <h3 class="font-bold text-lg mb-4"><i class="fas fa-user-times text-yellow-500 mr-2"></i>Tamu Check-out Hari Ini</h3>
            @php
                $foCheckouts = \App\Models\Reservation::with(['guest', 'room'])
                    ->whereDate('check_out', \Carbon\Carbon::today())
                    ->where('status', 'checked_in')
                    ->orderBy('check_out')
                    ->limit(10)
                    ->get();
            @endphp
            @if($foCheckouts->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead><tr class="border-b">
                            <th class="text-left p-2 text-sm">Nama Tamu</th>
                            <th class="text-left p-2 text-sm">Kamar</th>
                            <th class="text-left p-2 text-sm">Check-in</th>
                        </tr></thead>
                        <tbody>
                            @foreach($foCheckouts as $res)
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

        <div class="flex items-center gap-2 mt-6">
            <a href="{{ route('checkin.index') }}" class="bg-blue-600 text-white px-4 py-2 rounded inline-block">
                <i class="fas fa-plus"></i> Check-in Baru
            </a>
            <a href="{{ route('rooms.dashboard') }}" class="bg-green-600 text-white px-4 py-2 rounded inline-block">
                <i class="fas fa-th-large"></i> Dashboard Kamar
            </a>
        </div>

        <div class="mt-4">
            <button onclick="autoCancelPending(this)" data-url="{{ route('auto-cancel-pending') }}" class="bg-red-500 hover:bg-red-600 text-white text-sm px-4 py-2 rounded-lg shadow transition">
                <i class="fas fa-times-circle mr-1"></i> Batalkan Booking Pending >3 Jam (From WEB)
            </button>
        </div>
    @endif
@endsection

@section('scripts')
@if(auth()->user()->isOwner() || auth()->user()->isUserManager())
<script>
    (function() {
        var occEl = document.getElementById('occupancyChart');
        if (occEl && typeof window.Chart !== 'undefined') {
            new window.Chart(occEl, {
                type: 'line',
                data: {
                    labels: @json($last7Days['labels']),
                    datasets: [{
                        label: 'Okupansi (%)',
                        data: @json($last7Days['occupancy']),
                        borderColor: '#3b82f6',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        fill: true
                    }]
                }
            });
        }
        var revEl = document.getElementById('revenueChart');
        if (revEl && typeof window.Chart !== 'undefined') {
            new window.Chart(revEl, {
                type: 'bar',
                data: {
                    labels: @json($last7Days['labels']),
                    datasets: [{
                        label: 'Pendapatan (Rp)',
                        data: @json($last7Days['revenue']),
                        backgroundColor: '#10b981'
                    }]
                }
            });
        }
    })();
</script>
@endif
<script>
    function autoCancelPending(btn) {
        if (!btn) btn = event && event.currentTarget;
        if (!btn) return;

        var url = btn.getAttribute('data-url');
        if (!url) return;

        if (!confirm('⚠️ Batalkan semua booking pending dari website yang sudah >3 jam?\n\nTindakan ini tidak bisa dibatalkan.')) {
            return;
        }

        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Memproses...';

        fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
            },
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success) {
                alert(data.message);
                location.reload();
            } else {
                alert('Gagal: ' + data.message);
            }
        })
        .catch(function(err) {
            alert('Error: ' + err.message);
        })
        .finally(function() {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-times-circle mr-1"></i> Batalkan Booking Pending >3 Jam';
        });
    }
</script>
@endsection
