@extends('layouts.app')

@section('title', 'MHS Audit Log')
@section('header', 'MHS Audit Log')

@section('content')
<!-- Filter (hidden on print) -->
<div class="bg-white rounded-lg shadow p-4 mb-6 no-print">
    <form method="GET">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">Dari Tanggal</label>
                <input type="date" name="start_date" value="{{ $startDate }}" class="w-full border rounded px-3 py-2">
            </div>
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">Sampai Tanggal</label>
                <input type="date" name="end_date" value="{{ $endDate }}" class="w-full border rounded px-3 py-2">
            </div>
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">Command</label>
                <select name="command" class="w-full border rounded px-3 py-2">
                    <option value="all" {{ $command === 'all' ? 'selected' : '' }}>Semua</option>
                    <option value="checkin" {{ $command === 'checkin' ? 'selected' : '' }}>Check-in</option>
                    <option value="checkout" {{ $command === 'checkout' ? 'selected' : '' }}>Checkout</option>
                    <option value="erase_card" {{ $command === 'erase_card' ? 'selected' : '' }}>Erase Card</option>
                </select>
            </div>
            <div class="flex items-end space-x-2">
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                    <i class="fas fa-search mr-1"></i> Cari
                </button>
                <button type="button" onclick="window.print()" data-turbo="false" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                    <i class="fas fa-print mr-1"></i> Print
                </button>
            </div>
        </div>
    </form>
</div>

<!-- Print Header -->
<div class="hidden print:block mb-6">
    <h1 class="text-2xl font-bold text-center">MHS AUDIT LOG</h1>
    <p class="text-center text-gray-600">Periode: {{ \Carbon\Carbon::parse($startDate)->format('d F Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d F Y') }}</p>
    <p class="text-center text-gray-600">Command: {{ $command === 'all' ? 'Semua' : strtoupper(str_replace('_', ' ', $command)) }}</p>
    <p class="text-center text-gray-600">Dicetak: {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}</p>
    <p class="text-center text-gray-600">Total: {{ $logs->total() }} entri</p>
    <hr class="my-4">
</div>

<!-- Summary Cards -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
    @foreach($summary as $s)
        <div class="bg-white rounded-lg shadow p-4 border-l-4 {{ $s->fail_count > 0 ? 'border-red-500' : 'border-green-500' }}">
            <div class="text-sm text-gray-500 uppercase tracking-wider font-bold">{{ strtoupper(str_replace('_', ' ', $s->command)) }}</div>
            <div class="text-2xl font-bold mt-1">{{ $s->total }}</div>
            <div class="flex justify-between text-xs mt-1">
                <span class="text-green-600">✓ {{ $s->success_count }}</span>
                <span class="text-red-600">✗ {{ $s->fail_count }}</span>
            </div>
        </div>
    @endforeach
</div>

<!-- Tabel Log -->
<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="px-4 py-3 border-b flex justify-between items-center no-print">
        <h3 class="font-bold text-lg">Detail Log MHS</h3>
        <span class="text-sm text-gray-500">Total: {{ $logs->total() }} entri</span>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">Waktu</th>
                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">Command</th>
                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">Kamar</th>
                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">Reservasi</th>
                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">Oleh</th>
                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">Status</th>
                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">Request</th>
                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">Response</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($logs as $log)
                    <tr class="hover:bg-gray-50 {{ !$log->success ? 'bg-red-50' : '' }}">
                        <td class="px-4 py-3 whitespace-nowrap text-gray-600">{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <span class="px-2 py-1 rounded text-xs font-bold
                                @if($log->command === 'checkin') bg-blue-100 text-blue-800
                                @elseif($log->command === 'checkout') bg-yellow-100 text-yellow-800
                                @elseif($log->command === 'erase_card') bg-red-100 text-red-800
                                @else bg-gray-100 text-gray-800 @endif">
                                {{ strtoupper(str_replace('_', ' ', $log->command)) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap font-bold">{{ $log->reservation?->room?->room_number ?? '-' }}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-gray-600">{{ $log->reservation?->reservation_number ?? '-' }}</td>
                        <td class="px-4 py-3 whitespace-nowrap">{{ $log->creator?->name ?? $log->creator?->username ?? 'System' }}</td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            @if($log->success)
                                <span class="text-green-600 font-bold"><i class="fas fa-check-circle"></i> Sukses</span>
                            @else
                                <span class="text-red-600 font-bold"><i class="fas fa-times-circle"></i> Gagal</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-xs text-gray-500 max-w-xs truncate" title="{{ json_encode($log->request_data) }}">
                            {{ json_encode($log->request_data) }}
                        </td>
                        <td class="px-4 py-3 text-xs text-gray-500 max-w-xs truncate" title="{{ json_encode($log->response_data) }}">
                            {{ is_array($log->response_data) ? ($log->response_data['response']['message'] ?? json_encode($log->response_data)) : $log->response_data }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-8 text-center text-gray-500">
                            <i class="fas fa-inbox text-3xl mb-2"></i>
                            <p>Tidak ada data log MHS</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($logs->hasPages())
        <div class="px-4 py-3 border-t no-print">
            {{ $logs->links() }}
        </div>
    @endif
</div>

<style>
@media print {
    .no-print { display: none !important; }
    body { background: white !important; }
    .app-sidebar, .sidebar-spacer, .app-header { display: none !important; }
    .main-wrapper { margin-left: 0 !important; padding: 0 !important; }
    table { font-size: 10px; width: 100%; border-collapse: collapse; }
    th { background: #f3f4f6 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    th, td { border: 1px solid #d1d5db; padding: 4px 6px; }
    .bg-red-50 { background: #fef2f2 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
}
</style>
@endsection
