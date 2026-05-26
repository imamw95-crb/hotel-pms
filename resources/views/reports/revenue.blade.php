@extends('layouts.app')

@section('title', 'Laporan Pendapatan')
@section('header', 'Laporan Pendapatan')

@section('content')
<div class="mb-6">
    <form method="GET" class="flex gap-4">
        <div>
            <label class="block text-gray-700 text-sm font-bold mb-2">Dari Tanggal</label>
            <input type="date" name="start_date" value="{{ $startDate }}" class="border rounded px-3 py-2">
        </div>
        <div>
            <label class="block text-gray-700 text-sm font-bold mb-2">Sampai Tanggal</label>
            <input type="date" name="end_date" value="{{ $endDate }}" class="border rounded px-3 py-2">
        </div>
        <div class="pt-6 flex gap-2">
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Filter</button>
            <a href="{{ route('reports.revenue.export', request()->query()) }}" class="bg-orange-600 text-white px-4 py-2 rounded">Export CSV</a>
            <button type="button" onclick="window.print()" class="bg-green-600 text-white px-4 py-2 rounded">Print</button>
        </div>
    </form>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <div class="bg-white p-5 rounded shadow">
        <div class="text-gray-500 text-sm">Total Pendapatan</div>
        <div class="text-3xl font-bold text-green-600">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</div>
    </div>
    <div class="bg-white p-5 rounded shadow">
        <div class="text-gray-500 text-sm">Jumlah Transaksi</div>
        <div class="text-3xl font-bold">{{ $transactions->count() }}</div>
    </div>
    <div class="bg-white p-5 rounded shadow">
        <div class="text-gray-500 text-sm">Rata-rata per Transaksi</div>
        <div class="text-3xl font-bold">Rp {{ number_format($transactions->count() > 0 ? $totalRevenue / $transactions->count() : 0, 0, ',', '.') }}</div>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
    <div class="bg-white p-5 rounded shadow">
        <h3 class="text-lg font-bold mb-4">Pendapatan per Metode Pembayaran</h3>
        <div class="space-y-2">
            @foreach($byMethod as $method => $amount)
                <div class="flex justify-between">
                    <span class="text-gray-600 capitalize">{{ str_replace('_', ' ', $method) }}</span>
                    <span class="font-bold">Rp {{ number_format($amount, 0, ',', '.') }}</span>
                </div>
            @endforeach
        </div>
    </div>

    <div class="bg-white p-5 rounded shadow">
        <h3 class="text-lg font-bold mb-4">Statistik</h3>
        <canvas id="revenueChart"></canvas>
    </div>
</div>

<div class="bg-white rounded shadow overflow-hidden">
    <table class="w-full">
        <thead class="bg-gray-200">
            <tr>
                <th class="px-4 py-2 text-left">No. Transaksi</th>
                <th class="px-4 py-2 text-left">Kamar</th>
                <th class="px-4 py-2 text-left">Tipe</th>
                <th class="px-4 py-2 text-left">Jumlah</th>
                <th class="px-4 py-2 text-left">Metode</th>
                <th class="px-4 py-2 text-left">Tanggal</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transactions as $txn)
                <tr class="border-b hover:bg-gray-50">
                    <td class="px-4 py-2">{{ $txn->transaction_number }}</td>
                    <td class="px-4 py-2">{{ $txn->reservation?->room?->room_number ?? '-' }}</td>
                    <td class="px-4 py-2">{{ $txn->type }}</td>
                    <td class="px-4 py-2 font-bold text-green-600">Rp {{ number_format($txn->amount, 0, ',', '.') }}</td>
                    <td class="px-4 py-2 capitalize">{{ str_replace('_', ' ', $txn->payment_method) }}</td>
                    <td class="px-4 py-2">{{ $txn->created_at->format('d/m/Y H:i') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-4 py-2 text-center text-gray-500">Tidak ada data transaksi</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection

@section('scripts')
<script src="{{ asset('js/chart.min.js') }}"></script>
<script>
    @if(!empty($byMethod))
    document.addEventListener('DOMContentLoaded', function() {
        /* @php-ignore */ new Chart(document.getElementById('revenueChart'), {
            type: 'pie',
            data: {
                labels: [
                    @foreach($byMethod as $method => $amount)
                        '{{ str_replace('_', ' ', $method) }}',
                    @endforeach
                ],
                datasets: [{
                    data: [
                        @foreach($byMethod as $method => $amount)
                            {{ $amount }},
                        @endforeach
                    ],
                    backgroundColor: [
                        '#3b82f6',
                        '#10b981',
                        '#6366f1',
                        '#ef4444',
                        '#8b5cf6'
                    ]
                }]
            }
        });
    });
    @endif
</script>
@endsection
