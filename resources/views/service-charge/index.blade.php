@extends('layouts.app')

@section('title', 'Other Revenue')
@section('header', 'Other Revenue')

@section('content')
<div class="max-w-6xl mx-auto">

    {{-- Header --}}
    <div class="flex justify-between items-center mb-6">
        <div>
            <p class="text-sm text-gray-500">Kelola pendapatan lain selain kamar dan resto (laundry, extra bed, mini bar, dll).</p>
        </div>
        <button type="button"
                onclick="ServiceChargeForm.open('{{ route('service-charge.create') }}')"
                class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-5 py-2.5 rounded-lg transition flex items-center gap-2">
            <i class="fas fa-plus"></i> Other Revenue Baru
        </button>
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-green-500">
            <div class="text-xs uppercase text-gray-500 font-bold">Total Hari Ini</div>
            <div class="text-2xl font-bold text-green-700 mt-1">Rp {{ number_format($totalToday, 0, ',', '.') }}</div>
        </div>
        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-blue-500">
            <div class="text-xs uppercase text-gray-500 font-bold">Total Periode Filter</div>
            <div class="text-2xl font-bold text-blue-700 mt-1">Rp {{ number_format($totalPeriod, 0, ',', '.') }}</div>
        </div>
        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-purple-500">
            <div class="text-xs uppercase text-gray-500 font-bold">Jumlah Transaksi</div>
            <div class="text-2xl font-bold text-purple-700 mt-1">{{ $charges->total() }}</div>
        </div>
    </div>

    {{-- Filter --}}
    <div class="bg-white rounded-lg shadow p-4 mb-6">
        <form method="GET" class="flex flex-wrap items-end gap-4">
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-1">Dari Tanggal</label>
                <input type="date" name="date_from" value="{{ $dateFrom }}"
                       class="border rounded px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-1">Sampai Tanggal</label>
                <input type="date" name="date_to" value="{{ $dateTo }}"
                       class="border rounded px-3 py-2 text-sm">
            </div>
            <div class="flex-1 min-w-[200px]">
                <label class="block text-gray-700 text-sm font-bold mb-1">Cari</label>
                <input type="text" name="search" value="{{ $search }}"
                       placeholder="No. charge, layanan, atau nama tamu..."
                       class="border rounded px-3 py-2 text-sm w-full">
            </div>
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 text-sm">
                <i class="fas fa-search mr-1"></i> Filter
            </button>
            @if($dateFrom || $dateTo || $search)
                <a href="{{ route('service-charge.index') }}" class="text-gray-500 hover:text-gray-700 text-sm px-3 py-2">
                    <i class="fas fa-times mr-1"></i> Reset
                </a>
            @endif
        </form>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-800 text-white">
                    <th class="text-left p-3 font-semibold">No. Charge</th>
                    <th class="text-left p-3 font-semibold">Tanggal</th>
                    <th class="text-left p-3 font-semibold">Tamu</th>
                    <th class="text-left p-3 font-semibold">Layanan</th>
                    <th class="text-center p-3 font-semibold">Qty</th>
                    <th class="text-right p-3 font-semibold">Total</th>
                    <th class="text-center p-3 font-semibold w-24">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($charges as $charge)
                <tr class="border-b border-gray-100 hover:bg-gray-50 transition">
                    <td class="p-3 font-mono font-bold text-blue-600">{{ $charge->charge_number }}</td>
                    <td class="p-3 text-gray-600">{{ $charge->charge_date->format('d/m/Y') }}</td>
                    <td class="p-3">
                        <div class="font-medium">{{ $charge->guest->guest_name ?? ($charge->reservation->guest->guest_name ?? '-') }}</div>
                        @if($charge->reservation)
                            <div class="text-xs text-gray-400">Kamar {{ $charge->reservation->room->room_number ?? '-' }}</div>
                        @endif
                    </td>
                    <td class="p-3">{{ $charge->service_name }}</td>
                    <td class="p-3 text-center">{{ $charge->quantity }} × Rp {{ number_format($charge->amount, 0, ',', '.') }}</td>
                    <td class="p-3 text-right font-bold">Rp {{ number_format($charge->total_amount, 0, ',', '.') }}</td>
                    <td class="p-3 text-center">
                        <a href="{{ route('service-charge.show', $charge) }}"
                           class="text-blue-600 hover:text-blue-800 text-sm font-medium"
                           title="Lihat / Print">
                            <i class="fas fa-eye mr-1"></i> Lihat
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="p-8 text-center text-gray-400">
                        <i class="fas fa-receipt text-3xl mb-2 block"></i>
                        Belum ada other revenue.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($charges->hasPages())
    <div class="mt-4">
        {{ $charges->withQueryString()->links() }}
    </div>
    @endif

</div>
@endsection

@section('scripts')
<script>
window.ServiceChargeForm = {
    open(url) {
        Modal.open(url);
    }
};
</script>
@endsection
