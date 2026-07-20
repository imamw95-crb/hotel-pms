@extends('layouts.app')

@section('title', 'Available Rooms')
@section('header', 'Available Rooms')

@section('content')
<div class="max-w-full space-y-6">
    {{-- Filter Form --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
        <form method="GET" action="{{ route('available-rooms.index') }}" class="flex flex-wrap items-end gap-3">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Check-in Date</label>
                <input type="date" name="check_in" value="{{ $checkIn }}"
                    class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 w-[150px]">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Check-out Date</label>
                <input type="date" name="check_out" value="{{ $checkOut }}"
                    class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 w-[150px]">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Room Type</label>
                <select name="room_type"
                    class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
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
                    class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Semua</option>
                    <option value="available" {{ $selectedStatus === 'available' ? 'selected' : '' }}>Available</option>
                    <option value="limited" {{ $selectedStatus === 'limited' ? 'selected' : '' }}>Limited</option>
                    <option value="sold_out" {{ $selectedStatus === 'sold_out' ? 'selected' : '' }}>Sold Out</option>
                </select>
            </div>
            <div class="flex items-center gap-2">
                <button type="submit"
                    class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-700 transition">
                    <i class="fas fa-search mr-1"></i> Cari
                </button>
                <a href="{{ route('available-rooms.index') }}"
                    class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium hover:bg-gray-300 transition">
                    <i class="fas fa-sync-alt mr-1"></i> Reset
                </a>
            </div>
        </form>
    </div>

    {{-- Date Info --}}
    <div class="bg-blue-50 border border-blue-200 rounded-xl p-3 text-sm text-blue-800">
        <i class="fas fa-calendar-alt mr-1"></i>
        Periode: <strong>{{ \Carbon\Carbon::parse($checkIn)->isoFormat('D MMMM YYYY') }}</strong>
        s/d <strong>{{ \Carbon\Carbon::parse($checkOut)->isoFormat('D MMMM YYYY') }}</strong>
        ({{ \Carbon\Carbon::parse($checkIn)->diffInDays(\Carbon\Carbon::parse($checkOut)) }} malam)
    </div>

    {{-- Availability Cards --}}
    @forelse($availability as $item)
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            {{-- Header --}}
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                <div>
                    <h3 class="text-lg font-bold text-gray-900">{{ $item['room_type']->name }}</h3>
                    @if($item['room_type']->code)
                        <span class="text-xs text-gray-500">Kode: {{ $item['room_type']->code }}</span>
                    @endif
                </div>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold border {{ $item['visual_class'] }}">
                    {{ $item['visual_label'] }}
                </span>
            </div>

            {{-- Stats --}}
            <div class="grid grid-cols-3 divide-x divide-gray-100">
                <div class="p-5 text-center">
                    <p class="text-3xl font-bold text-gray-900">{{ $item['total'] }}</p>
                    <p class="text-xs text-gray-500 mt-1">Total Kamar</p>
                </div>
                <div class="p-5 text-center">
                    <p class="text-3xl font-bold text-red-600">{{ $item['occupied'] }}</p>
                    <p class="text-xs text-gray-500 mt-1">Terisi</p>
                </div>
                <div class="p-5 text-center">
                    <p class="text-3xl font-bold text-emerald-600">{{ $item['available'] }}</p>
                    <p class="text-xs text-gray-500 mt-1">Tersedia</p>
                </div>
            </div>

            {{-- Progress Bar --}}
            @php
                $occupiedPercent = $item['total'] > 0 ? round(($item['occupied'] / $item['total']) * 100) : 0;
                $unavailablePercent = $item['total'] > 0 ? round(($item['maintenance_or_ooo'] / $item['total']) * 100) : 0;
                $availablePercent = 100 - $occupiedPercent - $unavailablePercent;
                if ($availablePercent < 0) $availablePercent = 0;
            @endphp
            <div class="px-5 pb-4">
                <div class="w-full bg-gray-100 rounded-full h-2.5 overflow-hidden">
                    <div class="flex h-full">
                        <div class="bg-emerald-500 h-full transition-all" style="width: {{ $availablePercent }}%"></div>
                        <div class="bg-red-500 h-full transition-all" style="width: {{ $occupiedPercent }}%"></div>
                        <div class="bg-gray-400 h-full transition-all" style="width: {{ $unavailablePercent }}%"></div>
                    </div>
                </div>
                <div class="flex justify-between mt-1.5 text-xs text-gray-500">
                    <span><span class="inline-block w-2 h-2 bg-emerald-500 rounded-full mr-1"></span>Tersedia {{ $availablePercent }}%</span>
                    <span><span class="inline-block w-2 h-2 bg-red-500 rounded-full mr-1"></span>Terisi {{ $occupiedPercent }}%</span>
                    @if($item['maintenance_or_ooo'] > 0)
                        <span><span class="inline-block w-2 h-2 bg-gray-400 rounded-full mr-1"></span>Mnt/OOO {{ $unavailablePercent }}%</span>
                    @endif
                </div>
            </div>

            {{-- Detail Info --}}
            @if($item['maintenance_or_ooo'] > 0)
                <div class="px-5 pb-4 flex items-center gap-2 text-xs text-gray-500">
                    <i class="fas fa-tools text-gray-400"></i>
                    <span>{{ $item['maintenance_or_ooo'] }} kamar dalam maintenance / out of order</span>
                </div>
            @endif
        </div>
    @empty
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8 text-center">
            <div class="text-gray-400 mb-3">
                <i class="fas fa-bed text-5xl"></i>
            </div>
            <p class="text-gray-600 font-medium">Tidak ada data kamar tersedia</p>
            <p class="text-gray-400 text-sm mt-1">Silakan ubah filter atau periode tanggal</p>
        </div>
    @endforelse
</div>
@endsection
