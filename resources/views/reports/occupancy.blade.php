@extends('layouts.app')

@section('title', 'Laporan Okupansi')

@section('content')
<div class="bg-white rounded-lg shadow p-6">
    <h2 class="text-xl font-bold mb-4">Laporan Okupansi Kamar</h2>
    
    <form method="GET" class="mb-6 flex gap-4">
        <div>
            <label class="block text-sm">Start Date</label>
            <input type="date" name="start_date" value="{{ $startDate }}" class="border rounded px-2 py-1">
        </div>
        <div>
            <label class="block text-sm">End Date</label>
            <input type="date" name="end_date" value="{{ $endDate }}" class="border rounded px-2 py-1">
        </div>
        <div class="flex items-end">
            <button type="submit" class="bg-blue-600 text-white px-3 py-1 rounded">Filter</button>
        </div>
    </form>
    
    <canvas id="occupancyChart" height="100"></canvas>
    
    <div class="mt-4">
        <p>Total Kamar: {{ $totalRooms }}</p>
        <p>Periode: {{ $startDate }} s/d {{ $endDate }}</p>
    </div>
</div>

<script>
    new Chart(document.getElementById('occupancyChart'), {
        type: 'line',
        data: {
            labels: @json($dates),
            datasets: [{
                label: 'Okupansi (%)',
                data: @json($occupancyData),
                borderColor: '#3b82f6',
                fill: true
            }]
        }
    });
</script>
@endsection