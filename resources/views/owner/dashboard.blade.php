@extends('layouts.app')

//@section('title', 'Owner Dashboard')

@section('content')
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

<div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="font-bold text-lg mb-4">Okupansi 7 Hari Terakhir</h3>
        <canvas id="occupancyChart" height="200" data-labels='@json($last7Days["labels"])' data-occupancy='@json($last7Days["occupancy"])'></canvas>
    </div>
    
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="font-bold text-lg mb-4">Pendapatan 7 Hari Terakhir</h3>
        <canvas id="revenueChart" height="200" data-labels='@json($last7Days["labels"])' data-revenue='@json($last7Days["revenue"])'></canvas>
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
                @foreach($recentReservations as $res)
                <tr class="border-b">
                    <td class="p-2">{{ $res->reservation_number }}</td>
                    <td class="p-2">{{ $res->guest->guest_name }}</td>
                    <td class="p-2">{{ $res->room->room_number }}</td>
                    <td class="p-2">{{ $res->check_in->format('d/m/Y H:i') }}</td>
                    <td class="p-2">{{ $res->check_out->format('d/m/Y H:i') }}</td>
                    <td class="p-2">
                        <span class="px-2 py-1 rounded text-xs 
                            @if($res->status == 'checked_in') bg-green-100 text-green-800
                            @elseif($res->status == 'pending') bg-yellow-100 text-yellow-800
                            @else bg-gray-100 text-gray-800 @endif">
                            {{ $res->status_label }}
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<script src="{{ asset('js/dashboard-charts.js') }}"></script>
@endsection