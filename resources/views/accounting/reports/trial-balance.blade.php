@extends('layouts.app')

@section('title', 'Neraca Saldo')
@section('header', 'Akunting — Neraca Saldo')

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

<div class="bg-white rounded shadow p-4 mb-4">
    <h3 class="font-bold text-lg">Neraca Saldo</h3>
    <p class="text-sm text-gray-500">Periode {{ \Carbon\Carbon::parse($start)->format('d/m/Y') }} — {{ \Carbon\Carbon::parse($end)->format('d/m/Y') }}</p>
</div>

<div class="bg-white rounded shadow overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-100">
                <tr class="text-gray-600">
                    <th rowspan="2" class="px-3 py-2 text-left">Kode</th>
                    <th rowspan="2" class="px-3 py-2 text-left">Nama Akun</th>
                    <th colspan="2" class="px-3 py-1 text-center border-b">Saldo Awal</th>
                    <th colspan="2" class="px-3 py-1 text-center border-b">Mutasi Periode</th>
                    <th colspan="2" class="px-3 py-1 text-center border-b">Saldo Akhir</th>
                </tr>
                <tr class="text-gray-600 text-xs">
                    <th class="px-3 py-1 text-right">Debit</th>
                    <th class="px-3 py-1 text-right">Kredit</th>
                    <th class="px-3 py-1 text-right">Debit</th>
                    <th class="px-3 py-1 text-right">Kredit</th>
                    <th class="px-3 py-1 text-right">Debit</th>
                    <th class="px-3 py-1 text-right">Kredit</th>
                </tr>
            </thead>
            <tbody>
                @forelse($report['rows'] as $row)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="px-3 py-2 font-mono">{{ $row['account']->code }}</td>
                        <td class="px-3 py-2">{{ $row['account']->name }}</td>
                        <td class="px-3 py-2 text-right">{{ $row['opening_debit'] ? number_format($row['opening_debit'], 0, ',', '.') : '' }}</td>
                        <td class="px-3 py-2 text-right">{{ $row['opening_credit'] ? number_format($row['opening_credit'], 0, ',', '.') : '' }}</td>
                        <td class="px-3 py-2 text-right">{{ $row['debit'] ? number_format($row['debit'], 0, ',', '.') : '' }}</td>
                        <td class="px-3 py-2 text-right">{{ $row['credit'] ? number_format($row['credit'], 0, ',', '.') : '' }}</td>
                        <td class="px-3 py-2 text-right font-semibold">{{ $row['ending_debit'] ? number_format($row['ending_debit'], 0, ',', '.') : '' }}</td>
                        <td class="px-3 py-2 text-right font-semibold">{{ $row['ending_credit'] ? number_format($row['ending_credit'], 0, ',', '.') : '' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="px-3 py-6 text-center text-gray-500">Belum ada mutasi pada periode ini.</td></tr>
                @endforelse
            </tbody>
            @if(count($report['rows']))
                <tfoot>
                    <tr class="bg-gray-50 font-bold">
                        <td colspan="2" class="px-3 py-3 text-right">TOTAL</td>
                        <td class="px-3 py-3 text-right">{{ number_format($report['totals']['opening_debit'], 0, ',', '.') }}</td>
                        <td class="px-3 py-3 text-right">{{ number_format($report['totals']['opening_credit'], 0, ',', '.') }}</td>
                        <td class="px-3 py-3 text-right">{{ number_format($report['totals']['debit'], 0, ',', '.') }}</td>
                        <td class="px-3 py-3 text-right">{{ number_format($report['totals']['credit'], 0, ',', '.') }}</td>
                        <td class="px-3 py-3 text-right">{{ number_format($report['totals']['ending_debit'], 0, ',', '.') }}</td>
                        <td class="px-3 py-3 text-right">{{ number_format($report['totals']['ending_credit'], 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>
</div>

<div class="mt-4 text-sm">
    @if(abs($report['totals']['ending_debit'] - $report['totals']['ending_credit']) < 0.01)
        <span class="text-green-600"><i class="fas fa-check-circle mr-1"></i> Neraca saldo seimbang.</span>
    @else
        <span class="text-red-600"><i class="fas fa-exclamation-triangle mr-1"></i> Neraca saldo tidak seimbang — periksa jurnal.</span>
    @endif
</div>
@endsection
