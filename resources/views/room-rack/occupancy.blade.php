@extends('layouts.app')

@section('title', 'Occupancy Calendar')
@section('header', 'Occupancy Calendar')

@section('content')
<div class="max-w-full">
    <div class="bg-white rounded-xl shadow-sm border p-4 mb-6">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-bold">
                <i class="fas fa-calendar-alt text-purple-500 mr-2"></i>
                {{ $start->format('F Y') }}
            </h2>
            <div class="flex items-center gap-2">
                <a href="{{ route('room-rack.occupancy', ['month' => $start->copy()->subMonth()->format('Y-m')]) }}"
                   class="px-3 py-1 border rounded text-sm hover:bg-gray-50">&larr; Previous</a>
                <a href="{{ route('room-rack.occupancy', ['month' => now()->format('Y-m')]) }}"
                   class="px-3 py-1 bg-blue-600 text-white rounded text-sm hover:bg-blue-700">Today</a>
                <a href="{{ route('room-rack.occupancy', ['month' => $start->copy()->addMonth()->format('Y-m')]) }}"
                   class="px-3 py-1 border rounded text-sm hover:bg-gray-50">Next &rarr;</a>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border overflow-x-auto">
        <div class="min-w-max">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 sticky top-0 z-10">
                    <tr>
                        <th class="text-left px-3 py-2 border-r border-b w-[120px] sticky left-0 bg-gray-50 z-20">Room</th>
                        @foreach($days as $day)
                            @php
                                $isToday = $day->isToday();
                                $isWeekend = $day->isWeekend();
                                $dayClass = $isToday ? 'bg-blue-50 text-blue-700 font-bold' : ($isWeekend ? 'text-gray-400' : '');
                            @endphp
                            <th class="text-center px-1 py-1 border-r border-b text-[11px] {{ $dayClass }}" style="min-width:32px;">
                                {{ $day->format('j') }}
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($calendar as $row)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="px-3 py-1.5 border-r sticky left-0 bg-white font-bold text-sm">{{ $row['room']->room_number }}</td>
                            @foreach($row['days'] as $cell)
                                @php
                                    $bg = match($cell['status']) {
                                        'occupied' => $cell['is_checkin'] ? 'bg-emerald-300' : ($cell['is_checkout'] ? 'bg-amber-300' : 'bg-red-400'),
                                        'maintenance' => 'bg-purple-200',
                                        'dirty' => 'bg-amber-100',
                                        default => 'bg-emerald-100',
                                    };
                                @endphp
                                <td class="border-r p-0 text-center" style="min-width:32px; height:28px;">
                                    <div class="w-full h-full {{ $bg }}" title="{{ $cell['booking']?->guest?->guest_name ?? $cell['status'] }}">
                                    </div>
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4 flex flex-wrap gap-4 text-xs text-gray-600">
        <span><span class="inline-block w-3 h-3 rounded bg-emerald-100 align-middle mr-1"></span>Available</span>
        <span><span class="inline-block w-3 h-3 rounded bg-red-400 align-middle mr-1"></span>Occupied</span>
        <span><span class="inline-block w-3 h-3 rounded bg-emerald-300 align-middle mr-1"></span>Check-in</span>
        <span><span class="inline-block w-3 h-3 rounded bg-amber-300 align-middle mr-1"></span>Check-out</span>
        <span><span class="inline-block w-3 h-3 rounded bg-amber-100 align-middle mr-1"></span>Dirty</span>
        <span><span class="inline-block w-3 h-3 rounded bg-purple-200 align-middle mr-1"></span>Maintenance</span>
    </div>
</div>
@endsection
