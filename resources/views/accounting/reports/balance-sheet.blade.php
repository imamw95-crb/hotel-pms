@extends('layouts.app')

@section('title', 'Neraca')
@section('header', 'Akunting — Neraca')

@section('content')
@include('accounting.partials.nav')

<div class="mb-6 flex flex-wrap items-end gap-4 print:hidden">
    <form method="GET" class="flex flex-wrap items-end gap-4">
        <div>
            <label class="block text-gray-700 text-sm font-bold mb-2">Per Tanggal</label>
            <input type="date" name="as_of" value="{{ $asOf }}" class="border rounded px-3 py-2">
        </div>
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
            <i class="fas fa-search mr-1"></i> Tampilkan
        </button>
    </form>
    <button type="button" onclick="window.print()" class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-slate-700">
        <i class="fas fa-print mr-1"></i> Cetak
    </button>
</div>

<div class="bg-white rounded shadow p-6">
    <div class="text-center mb-6">
        <h3 class="font-bold text-lg">Neraca (Balance Sheet)</h3>
        <p class="text-sm text-gray-500">Per {{ \Carbon\Carbon::parse($asOf)->format('d/m/Y') }}</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        {{-- ASET --}}
        <div>
            <h4 class="font-bold text-gray-800 border-b-2 border-gray-300 pb-1 mb-3">ASET</h4>
            @forelse($report['assets'] as $group)
                <div class="mb-3">
                    <div class="text-sm font-semibold text-gray-700">{{ $group->group }}</div>
                    @foreach($group->items as $item)
                        <div class="flex justify-between text-sm py-1 border-b border-dashed border-gray-100">
                            <span class="text-gray-600">{{ $item->account->code }} — {{ $item->account->name }}</span>
                            <span>{{ number_format($item->amount, 0, ',', '.') }}</span>
                        </div>
                    @endforeach
                    <div class="flex justify-between text-sm font-semibold py-1">
                        <span>Subtotal</span>
                        <span>{{ number_format($group->total, 0, ',', '.') }}</span>
                    </div>
                </div>
            @empty
                <p class="text-sm text-gray-500 mb-3">Belum ada saldo aset.</p>
            @endforelse
            <div class="flex justify-between font-bold border-t-2 border-gray-300 py-2">
                <span>TOTAL ASET</span>
                <span>{{ number_format($report['total_assets'], 0, ',', '.') }}</span>
            </div>
        </div>

        {{-- LIABILITAS & EKUITAS --}}
        <div>
            <h4 class="font-bold text-gray-800 border-b-2 border-gray-300 pb-1 mb-3">LIABILITAS</h4>
            @forelse($report['liabilities'] as $group)
                <div class="mb-3">
                    <div class="text-sm font-semibold text-gray-700">{{ $group->group }}</div>
                    @foreach($group->items as $item)
                        <div class="flex justify-between text-sm py-1 border-b border-dashed border-gray-100">
                            <span class="text-gray-600">{{ $item->account->code }} — {{ $item->account->name }}</span>
                            <span>{{ number_format($item->amount, 0, ',', '.') }}</span>
                        </div>
                    @endforeach
                    <div class="flex justify-between text-sm font-semibold py-1">
                        <span>Subtotal</span>
                        <span>{{ number_format($group->total, 0, ',', '.') }}</span>
                    </div>
                </div>
            @empty
                <p class="text-sm text-gray-500 mb-3">Tidak ada liabilitas.</p>
            @endforelse
            <div class="flex justify-between font-bold py-2">
                <span>TOTAL LIABILITAS</span>
                <span>{{ number_format($report['total_liabilities'], 0, ',', '.') }}</span>
            </div>

            <h4 class="font-bold text-gray-800 border-b-2 border-gray-300 pb-1 mb-3 mt-6">EKUITAS</h4>
            @foreach($report['equity'] as $group)
                <div class="mb-3">
                    <div class="text-sm font-semibold text-gray-700">{{ $group->group }}</div>
                    @foreach($group->items as $item)
                        <div class="flex justify-between text-sm py-1 border-b border-dashed border-gray-100">
                            <span class="text-gray-600">{{ $item->account->code }} — {{ $item->account->name }}</span>
                            <span>{{ number_format($item->amount, 0, ',', '.') }}</span>
                        </div>
                    @endforeach
                </div>
            @endforeach
            <div class="flex justify-between text-sm py-1 border-b border-dashed border-gray-100">
                <span class="text-gray-600">Laba / Rugi Berjalan</span>
                <span>{{ number_format($report['current_earnings'], 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between font-bold border-t-2 border-gray-300 py-2">
                <span>TOTAL LIABILITAS & EKUITAS</span>
                <span>{{ number_format($report['liabilities_and_equity'], 0, ',', '.') }}</span>
            </div>
        </div>
    </div>

    <div class="mt-6 pt-4 border-t text-sm">
        @if($report['balanced'])
            <span class="text-green-600"><i class="fas fa-check-circle mr-1"></i> Neraca seimbang (Aset = Liabilitas + Ekuitas).</span>
        @else
            <span class="text-red-600">
                <i class="fas fa-exclamation-triangle mr-1"></i>
                Neraca belum seimbang — selisih
                Rp {{ number_format(abs($report['total_assets'] - $report['liabilities_and_equity']), 0, ',', '.') }}.
                Periksa saldo awal dan jurnal manual.
            </span>
        @endif
    </div>
</div>
@endsection
