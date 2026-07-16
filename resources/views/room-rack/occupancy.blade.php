@extends('layouts.app')

@section('title', 'Occupancy Calendar')
@section('header', 'Occupancy Calendar')

@section('content')
<div class="max-w-full">
    <div class="bg-white rounded-xl shadow-sm border p-4 mb-6">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
            <h2 class="text-lg font-bold">
                <i class="fas fa-calendar-alt text-purple-500 mr-2"></i>
                {{ $start->format('M j') }} — {{ $end->format('M j, Y') }}
            </h2>
            <div class="flex flex-wrap items-center gap-2">
                <form method="GET" action="{{ route('room-rack.occupancy') }}" class="flex items-center gap-1">
                    <input type="date" name="start_date" value="{{ $start->format('Y-m-d') }}"
                           class="text-xs border rounded px-2 py-1.5 focus:outline-none focus:ring-1 focus:ring-blue-500"
                           onchange="this.form.submit()">
                </form>
                <div class="flex items-center gap-1">
                    <a href="{{ route('room-rack.occupancy', ['start_date' => $start->copy()->subDays(7)->format('Y-m-d')]) }}"
                       class="px-3 py-1.5 border rounded text-xs hover:bg-gray-50">&larr; Prev 7</a>
                    <a href="{{ route('room-rack.occupancy', ['start_date' => now()->format('Y-m-d')]) }}"
                       class="px-3 py-1.5 bg-blue-600 text-white rounded text-xs hover:bg-blue-700">Today</a>
                    <a href="{{ route('room-rack.occupancy', ['start_date' => $start->copy()->addDays(7)->format('Y-m-d')]) }}"
                       class="px-3 py-1.5 border rounded text-xs hover:bg-gray-50">Next 7 &rarr;</a>
                </div>
            </div>
        </div>
    </div>

    @include('room-rack.partials.occupancy-calendar')
</div>
@endsection
