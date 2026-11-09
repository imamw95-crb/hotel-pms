@extends('layouts.app')

@section('title', 'Arus Kas')
@section('header', 'Akunting — Arus Kas')

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

<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <div class="bg-white p-5 rounded shadow">
        <div class="text-gray-500 text-sm">Total Penerimaan</div>
        <div class="text-2xl font-bold text-green-600">Rp {{ number_format($report['totals']['receipts'], 0, ',', '.') }}</div>
    </div>
    <div class="bg-white p-5 rounded shadow">
        <div class="text-gray-500 text-sm">Total Pengeluaran</div>
        <div class="text-2xl font-bold text-red-600">Rp {{ number_format($report['totals']['payments'], 0, ',', '.') }}</div>
    </div>
    <div class="bg-white p-5 rounded shadow">
        <div class="text-gray-500 text-sm">Arus Kas Bersih</div>
        <div class="text-2xl font-bold text-indigo-600">
            Rp {{ number_format($report['totals']['receipts'] - $report['totals']['payments'], 0, ',', '.') }}
        </div>
    </div>
</div>

<div class="bg-white rounded shadow overflow-hidden mb-6">
    <div class="px-4 py-3 bg-gray-100 border-b font-bold">Mutasi per Akun Kas & Bank</div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-600">
                <tr>
                    <th class="px-4 py-2 text-left">Akun</th>
                    <th class="px-4 py-2 text-right">Saldo Awal</th>
                    <th class="px-4 py-2 text-right">Penerimaan</th>
                    <th class="px-4 py-2 text-right">Pengeluaran</th>
                    <th class="px-4 py-2 text-right">Saldo Akhir</th>
                </tr>
            </thead>
            <tbody>
                @forelse($report['rows'] as $row)
                    <tr class="border-b">
                        <td class="px-4 py-2">{{ $row['account']->code }} — {{ $row['account']->name }}</td>
                        <td class="px-4 py-2 text-right">{{ number_format($row['opening'], 0, ',', '.') }}</td>
                        <td class="px-4 py-2 text-right text-green-600">{{ number_format($row['receipts'], 0, ',', '.') }}</td>
                        <td class="px-4 py-2 text-right text-red-600">{{ number_format($row['payments'], 0, ',', '.') }}</td>
                        <td class="px-4 py-2 text-right font-semibold">{{ number_format($row['closing'], 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-6 text-center text-gray-500">Belum ada akun kas/bank.</td></tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr class="bg-gray-50 font-bold">
                    <td class="px-4 py-3">TOTAL</td>
                    <td class="px-4 py-3 text-right">{{ number_format($report['totals']['opening'], 0, ',', '.') }}</td>
                    <td class="px-4 py-3 text-right">{{ number_format($report['totals']['receipts'], 0, ',', '.') }}</td>
                    <td class="px-4 py-3 text-right">{{ number_format($report['totals']['payments'], 0, ',', '.') }}</td>
                    <td class="px-4 py-3 text-right">{{ number_format($report['totals']['closing'], 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="bg-white rounded shadow p-5">
        <h3 class="font-bold mb-3">Penerimaan per Jenis Aktivitas</h3>
        @forelse($report['receipts_by_source'] as $item)
            <div class="flex justify-between text-sm py-1 border-b border-dashed border-gray-100">
                <span class="text-gray-600">{{ $item->label }}</span>
                <span class="text-green-700 font-medium">{{ number_format($item->amount, 0, ',', '.') }}</span>
            </div>
        @empty
            <p class="text-sm text-gray-500">Tidak ada penerimaan.</p>
        @endforelse
    </div>

    <div class="bg-white rounded shadow p-5">
        <h3 class="font-bold mb-3">Pengeluaran per Jenis Aktivitas</h3>
        @forelse($report['payments_by_source'] as $item)
            <div class="flex justify-between text-sm py-1 border-b border-dashed border-gray-100">
                <span class="text-gray-600">{{ $item->label }}</span>
                <span class="text-red-700 font-medium">{{ number_format($item->amount, 0, ',', '.') }}</span>
            </div>
        @empty
            <p class="text-sm text-gray-500">Tidak ada pengeluaran.</p>
        @endforelse
    </div>
</div>
@endsection
