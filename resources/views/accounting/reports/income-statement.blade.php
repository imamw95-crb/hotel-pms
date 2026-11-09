@extends('layouts.app')

@section('title', 'Laba Rugi')
@section('header', 'Akunting — Laporan Laba Rugi')

@section('content')
@include('accounting.partials.nav')

<div class="mb-6 flex flex-wrap items-end gap-4 print:hidden">
    <form method="GET" class="flex flex-wrap items-end gap-4">
        <div>
            <label class="block text-gray-700 text-sm font-bold mb-2">Dari Tanggal</label>
            <input type="date" name="start_date" value="{{ $start }}" class="border rounded px-3 py-2">
        </div>
        <div>
            <label class="block text-gray-700 text-sm font-bold mb-2">Sampai Tanggal</label>
            <input type="date" name="end_date" value="{{ $end }}" class="border rounded px-3 py-2">
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
        <h3 class="font-bold text-lg">Laporan Laba Rugi</h3>
        <p class="text-sm text-gray-500">Periode {{ \Carbon\Carbon::parse($start)->format('d/m/Y') }} — {{ \Carbon\Carbon::parse($end)->format('d/m/Y') }}</p>
    </div>

    <h4 class="font-bold text-green-700 border-b pb-1 mb-2">PENDAPATAN</h4>
    @forelse($report['revenue'] as $group)
        <div class="mb-3">
            <div class="text-sm font-semibold text-gray-700">{{ $group->group }}</div>
            @foreach($group->items as $item)
                <div class="flex justify-between text-sm py-1 border-b border-dashed border-gray-100">
                    <span class="text-gray-600">{{ $item->account->code }} — {{ $item->account->name }}</span>
                    <span>{{ number_format($item->amount, 0, ',', '.') }}</span>
                </div>
            @endforeach
            <div class="flex justify-between text-sm font-semibold py-1">
                <span>Subtotal {{ $group->group }}</span>
                <span>{{ number_format($group->total, 0, ',', '.') }}</span>
            </div>
        </div>
    @empty
        <p class="text-sm text-gray-500 mb-3">Belum ada pendapatan pada periode ini.</p>
    @endforelse

    <div class="flex justify-between font-bold border-t-2 border-gray-300 py-2 mb-6">
        <span>TOTAL PENDAPATAN</span>
        <span class="text-green-700">{{ number_format($report['total_revenue'], 0, ',', '.') }}</span>
    </div>

    <h4 class="font-bold text-red-700 border-b pb-1 mb-2">BEBAN</h4>
    @forelse($report['expense'] as $group)
        <div class="mb-3">
            <div class="text-sm font-semibold text-gray-700">{{ $group->group }}</div>
            @foreach($group->items as $item)
                <div class="flex justify-between text-sm py-1 border-b border-dashed border-gray-100">
                    <span class="text-gray-600">{{ $item->account->code }} — {{ $item->account->name }}</span>
                    <span>{{ number_format($item->amount, 0, ',', '.') }}</span>
                </div>
            @endforeach
            <div class="flex justify-between text-sm font-semibold py-1">
                <span>Subtotal {{ $group->group }}</span>
                <span>{{ number_format($group->total, 0, ',', '.') }}</span>
            </div>
        </div>
    @empty
        <p class="text-sm text-gray-500 mb-3">Belum ada beban pada periode ini.</p>
    @endforelse

    <div class="flex justify-between font-bold border-t-2 border-gray-300 py-2 mb-6">
        <span>TOTAL BEBAN</span>
        <span class="text-red-700">{{ number_format($report['total_expense'], 0, ',', '.') }}</span>
    </div>

    <div class="flex justify-between items-center bg-gray-100 rounded px-4 py-3">
        <div>
            <div class="font-bold text-lg">LABA / RUGI BERSIH</div>
            <div class="text-xs text-gray-500">Margin bersih {{ number_format($report['margin'], 1, ',', '.') }}%</div>
        </div>
        <div class="text-2xl font-bold {{ $report['net_income'] >= 0 ? 'text-green-700' : 'text-red-700' }}">
            Rp {{ number_format($report['net_income'], 0, ',', '.') }}
        </div>
    </div>
</div>
@endsection
