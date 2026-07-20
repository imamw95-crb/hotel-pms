@extends('layouts.app')

@section('title', 'Available Rooms')
@section('header', 'Available Rooms')

@section('content')
<div class="max-w-full space-y-4">
    {{-- Filter Form --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-3">
        <form method="GET" action="{{ route('available-rooms.index') }}" class="flex flex-wrap items-end gap-2">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Check-in</label>
                <input type="date" name="check_in" value="{{ $checkIn }}"
                    class="border border-gray-300 rounded-lg px-2.5 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 w-[140px]">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Check-out</label>
                <input type="date" name="check_out" value="{{ $checkOut }}"
                    class="border border-gray-300 rounded-lg px-2.5 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 w-[140px]">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Tipe Kamar</label>
                <select name="room_type"
                    class="border border-gray-300 rounded-lg px-2.5 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Semua</option>
                    @foreach($roomTypes as $type)
                        <option value="{{ $type->id }}" {{ $selectedRoomType == $type->id ? 'selected' : '' }}>
                            {{ $type->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Status</label>
                <select name="status"
                    class="border border-gray-300 rounded-lg px-2.5 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Semua</option>
                    <option value="available" {{ $selectedStatus === 'available' ? 'selected' : '' }}>Available</option>
                    <option value="limited" {{ $selectedStatus === 'limited' ? 'selected' : '' }}>Limited</option>
                    <option value="sold_out" {{ $selectedStatus === 'sold_out' ? 'selected' : '' }}>Sold Out</option>
                </select>
            </div>
            <div class="flex items-center gap-1.5">
                <button type="submit"
                    class="bg-blue-600 text-white px-3 py-1.5 rounded-lg text-sm font-medium hover:bg-blue-700 transition">
                    <i class="fas fa-search mr-1"></i> Cari
                </button>
                <a href="{{ route('available-rooms.index') }}"
                    class="bg-gray-200 text-gray-700 px-3 py-1.5 rounded-lg text-sm font-medium hover:bg-gray-300 transition">
                    <i class="fas fa-sync-alt mr-1"></i> Reset
                </a>
            </div>
        </form>
    </div>

    {{-- Summary Bar + Date Info --}}
    @php
        $grandTotal = array_sum(array_column($availability, 'total'));
        $grandOccupied = array_sum(array_column($availability, 'occupied'));
        $grandAvailable = array_sum(array_column($availability, 'available'));
        $grandMtn = array_sum(array_column($availability, 'maintenance_or_ooo'));
    @endphp
    <div class="grid grid-cols-6 gap-3">
        <div class="bg-blue-50 border border-blue-200 rounded-xl p-3 text-sm text-blue-800 col-span-2 flex items-center gap-2">
            <i class="fas fa-calendar-alt"></i>
            <span><strong>{{ \Carbon\Carbon::parse($checkIn)->isoFormat('D MMM YYYY') }}</strong> &mdash; <strong>{{ \Carbon\Carbon::parse($checkOut)->isoFormat('D MMM YYYY') }}</strong> ({{ \Carbon\Carbon::parse($checkIn)->diffInDays(\Carbon\Carbon::parse($checkOut)) }} malam)</span>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-3 text-center">
            <p class="text-lg font-bold text-gray-900">{{ $grandTotal }}</p>
            <p class="text-[10px] text-gray-500">Total Kamar</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-3 text-center">
            <p class="text-lg font-bold text-red-600">{{ $grandOccupied }}</p>
            <p class="text-[10px] text-gray-500">Terisi</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-3 text-center">
            <p class="text-lg font-bold text-gray-500">{{ $grandMtn }}</p>
            <p class="text-[10px] text-gray-500">Mnt/OOO</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-3 text-center">
            <p class="text-lg font-bold text-emerald-600">{{ $grandAvailable }}</p>
            <p class="text-[10px] text-gray-500">Tersedia</p>
        </div>
    </div>

    {{-- Main Table --}}
    @if(count($availability) > 0)
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-100">
                            <th class="text-left px-4 py-3 font-semibold text-gray-700 text-xs uppercase tracking-wider">Tipe Kamar</th>
                            <th class="text-center px-3 py-3 font-semibold text-gray-700 text-xs uppercase tracking-wider">Total</th>
                            <th class="text-center px-3 py-3 font-semibold text-gray-700 text-xs uppercase tracking-wider">Terisi</th>
                            <th class="text-center px-3 py-3 font-semibold text-gray-700 text-xs uppercase tracking-wider">Mnt/OOO</th>
                            <th class="text-center px-3 py-3 font-semibold text-gray-700 text-xs uppercase tracking-wider">Tersedia</th>
                            <th class="text-center px-3 py-3 font-semibold text-gray-700 text-xs uppercase tracking-wider">Status</th>
                            <th class="text-left px-4 py-3 font-semibold text-gray-700 text-xs uppercase tracking-wider min-w-[200px]">Okupansi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($availability as $item)
                            @php
                                $occupiedPercent = $item['total'] > 0 ? round(($item['occupied'] / $item['total']) * 100) : 0;
                                $unavailablePercent = $item['total'] > 0 ? round(($item['maintenance_or_ooo'] / $item['total']) * 100) : 0;
                                $availablePercent = 100 - $occupiedPercent - $unavailablePercent;
                                if ($availablePercent < 0) $availablePercent = 0;
                            @endphp
                            <tr class="hover:bg-gray-50/50 transition">
                                <td class="px-4 py-3">
                                    <div class="font-semibold text-gray-900">{{ $item['room_type']->name }}</div>
                                    @if($item['room_type']->code)
                                        <div class="text-[11px] text-gray-400">{{ $item['room_type']->code }}</div>
                                    @endif
                                </td>
                                <td class="px-3 py-3 text-center font-semibold text-gray-900">{{ $item['total'] }}</td>
                                <td class="px-3 py-3 text-center font-semibold {{ $item['occupied'] > 0 ? 'text-red-600' : 'text-gray-400' }}">{{ $item['occupied'] }}</td>
                                <td class="px-3 py-3 text-center font-semibold {{ $item['maintenance_or_ooo'] > 0 ? 'text-gray-500' : 'text-gray-400' }}">{{ $item['maintenance_or_ooo'] }}</td>
                                <td class="px-3 py-3 text-center">
                                    <span class="font-bold text-lg {{ $item['available'] > 0 ? 'text-emerald-600' : 'text-red-600' }}">{{ $item['available'] }}</span>
                                </td>
                                <td class="px-3 py-3 text-center">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold border {{ $item['visual_class'] }}">
                                        {{ $item['visual_label'] }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <div class="flex-1 bg-gray-100 rounded-full h-2 overflow-hidden">
                                            <div class="flex h-full">
                                                <div class="bg-emerald-500 h-full transition-all" style="width: {{ $availablePercent }}%"></div>
                                                <div class="bg-red-500 h-full transition-all" style="width: {{ $occupiedPercent }}%"></div>
                                                <div class="bg-gray-400 h-full transition-all" style="width: {{ $unavailablePercent }}%"></div>
                                            </div>
                                        </div>
                                        <span class="text-[11px] text-gray-500 whitespace-nowrap min-w-[40px] text-right">{{ $availablePercent }}%</span>
                                    </div>
                                    @if($item['maintenance_or_ooo'] > 0)
                                        <div class="text-[10px] text-gray-400 mt-0.5">
                                            <i class="fas fa-tools mr-0.5"></i>{{ $item['maintenance_or_ooo'] }} kamar maintenance
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8 text-center">
            <div class="text-gray-400 mb-3">
                <i class="fas fa-bed text-5xl"></i>
            </div>
            <p class="text-gray-600 font-medium">Tidak ada data kamar tersedia</p>
            <p class="text-gray-400 text-sm mt-1">Silakan ubah filter atau periode tanggal</p>
        </div>
    @endif
</div>
@endsection
