@extends('layouts.app')

@section('title', 'Buku Besar')
@section('header', 'Akunting — Buku Besar')

@section('content')
@include('accounting.partials.nav')

<div class="mb-6 flex flex-wrap items-end gap-4 print:hidden">
    <form method="GET" class="flex flex-wrap items-end gap-4">
        <div>
            <label class="block text-gray-700 text-sm font-bold mb-2">Akun</label>
            <select name="account_id" class="border rounded px-3 py-2 min-w-[280px]">
                <option value="">— Pilih Akun —</option>
                @foreach($accounts as $account)
                    <option value="{{ $account->id }}" {{ (int) $accountId === $account->id ? 'selected' : '' }}>
                        {{ $account->code }} — {{ $account->name }}
                    </option>
                @endforeach
            </select>
        </div>
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
    @if($report)
        <button type="button" onclick="window.print()" class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-slate-700">
            <i class="fas fa-print mr-1"></i> Cetak
        </button>
    @endif
</div>

@if(! $report)
    <div class="bg-white rounded shadow p-8 text-center text-gray-500">
        Pilih akun terlebih dahulu untuk menampilkan buku besar.
    </div>
@else
    <div class="bg-white rounded shadow p-4 mb-4">
        <h3 class="font-bold text-lg">{{ $report['account']->code }} — {{ $report['account']->name }}</h3>
        <p class="text-sm text-gray-500">
            {{ \Carbon\Carbon::parse($start)->format('d/m/Y') }} — {{ \Carbon\Carbon::parse($end)->format('d/m/Y') }}
            · Saldo normal: {{ strtoupper($report['account']->normal_balance) }}
        </p>
    </div>

    <div class="bg-white rounded shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-100 text-gray-600">
                    <tr>
                        <th class="px-3 py-2 text-left">Tanggal</th>
                        <th class="px-3 py-2 text-left">No. Jurnal</th>
                        <th class="px-3 py-2 text-left">Keterangan</th>
                        <th class="px-3 py-2 text-left">Sumber</th>
                        <th class="px-3 py-2 text-right">Debit</th>
                        <th class="px-3 py-2 text-right">Kredit</th>
                        <th class="px-3 py-2 text-right">Saldo</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="bg-gray-50 font-semibold">
                        <td colspan="6" class="px-3 py-2 text-right">Saldo Awal</td>
                        <td class="px-3 py-2 text-right">{{ number_format($report['opening'], 0, ',', '.') }}</td>
                    </tr>
                    @forelse($report['rows'] as $row)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="px-3 py-2">{{ \Carbon\Carbon::parse($row['date'])->format('d/m/Y') }}</td>
                            <td class="px-3 py-2 font-mono">
                                <a href="{{ route('accounting.journals.show', $row['entry_id']) }}" class="text-indigo-600 hover:underline">{{ $row['entry_number'] }}</a>
                            </td>
                            <td class="px-3 py-2">{{ $row['memo'] }}</td>
                            <td class="px-3 py-2 text-gray-500">{{ \App\Models\JournalEntry::SOURCE_LABELS[$row['source_type']] ?? $row['source_type'] }}</td>
                            <td class="px-3 py-2 text-right">{{ $row['debit'] ? number_format($row['debit'], 0, ',', '.') : '' }}</td>
                            <td class="px-3 py-2 text-right">{{ $row['credit'] ? number_format($row['credit'], 0, ',', '.') : '' }}</td>
                            <td class="px-3 py-2 text-right font-semibold">{{ number_format($row['balance'], 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-3 py-6 text-center text-gray-500">Tidak ada mutasi pada periode ini.</td></tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="bg-gray-100 font-bold">
                        <td colspan="4" class="px-3 py-3 text-right">MUTASI & SALDO AKHIR</td>
                        <td class="px-3 py-3 text-right">{{ number_format($report['total_debit'], 0, ',', '.') }}</td>
                        <td class="px-3 py-3 text-right">{{ number_format($report['total_credit'], 0, ',', '.') }}</td>
                        <td class="px-3 py-3 text-right">{{ number_format($report['ending'], 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
@endif
@endsection
