@extends('layouts.admin')

@section('title', 'Admin Dashboard')
@section('header', 'Dashboard')

@section('content')
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white p-5 rounded shadow">
            <div class="text-gray-500">Total Kamar</div>
            <div class="text-3xl font-bold">{{ \App\Models\Room::count() }}</div>
        </div>
        <div class="bg-white p-5 rounded shadow">
            <div class="text-gray-500">Total Reservasi</div>
            <div class="text-3xl font-bold">{{ \App\Models\Reservation::count() }}</div>
        </div>
        <div class="bg-white p-5 rounded shadow">
            <div class="text-gray-500">Total Pendapatan</div>
            <div class="text-3xl font-bold">Rp {{ number_format(\App\Models\Transaction::sum('amount'),0,',','.') }}</div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Daftar Tamu Check-in Hari Ini -->
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
                </div>
            @else
                <p class="text-gray-500 text-center py-4">Tidak ada check-in hari ini</p>
            @endif
        </div>

        <!-- Daftar Tamu Check-out Hari Ini -->
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
    </div>

    <!-- Reservasi Terbaru -->
    <div class="bg-white rounded-lg shadow p-6 mt-6">
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
                    <thead>
                        <tr class="border-b">
                            <th class="text-left p-2 text-sm">No. Reservasi</th>
                            <th class="text-left p-2 text-sm">Nama Tamu</th>
                            <th class="text-left p-2 text-sm">Kamar</th>
                            <th class="text-left p-2 text-sm">Check-in</th>
                            <th class="text-left p-2 text-sm">Check-out</th>
                            <th class="text-left p-2 text-sm">Status</th>
                        </tr>
                    </thead>
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
@endsection