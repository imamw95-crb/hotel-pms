@extends('layouts.app')

@section('title', 'Dashboard Akuntansi')
@section('header', 'Akunting — Dashboard')

@section('content')
@include('accounting.partials.nav')

<div class="mb-6">
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
</div>

<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
    <div class="bg-white p-5 rounded shadow">
        <div class="text-gray-500 text-sm">Pendapatan (periode)</div>
        <div class="text-2xl font-bold text-green-600">Rp {{ number_format($income['total_revenue'], 0, ',', '.') }}</div>
    </div>
    <div class="bg-white p-5 rounded shadow">
        <div class="text-gray-500 text-sm">Beban (periode)</div>
        <div class="text-2xl font-bold text-red-600">Rp {{ number_format($income['total_expense'], 0, ',', '.') }}</div>
    </div>
    <div class="bg-white p-5 rounded shadow">
        <div class="text-gray-500 text-sm">Laba / Rugi Bersih</div>
        <div class="text-2xl font-bold {{ $income['net_income'] >= 0 ? 'text-indigo-600' : 'text-red-600' }}">
            Rp {{ number_format($income['net_income'], 0, ',', '.') }}
        </div>
        <div class="text-xs text-gray-500 mt-1">Margin {{ number_format($income['margin'], 1, ',', '.') }}%</div>
    </div>
    <div class="bg-white p-5 rounded shadow">
        <div class="text-gray-500 text-sm">Total Aset</div>
        <div class="text-2xl font-bold text-gray-700">Rp {{ number_format($balance['total_assets'], 0, ',', '.') }}</div>
        <div class="text-xs mt-1 {{ $balance['balanced'] ? 'text-green-600' : 'text-red-600' }}">
            <i class="fas fa-{{ $balance['balanced'] ? 'check-circle' : 'exclamation-triangle' }} mr-1"></i>
            {{ $balance['balanced'] ? 'Neraca seimbang' : 'Neraca tidak seimbang' }}
        </div>
    </div>
</div>

<div class="grid grid-cols-1 xl:grid-cols-2 gap-4 mb-6">
    <div class="bg-white rounded shadow p-5">
        <h3 class="text-lg font-bold mb-4">Saldo Kas & Bank (sampai {{ \Carbon\Carbon::parse($end)->format('d/m/Y') }})</h3>
        @if(count($cash['rows']))
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-500 border-b">
                        <th class="py-2">Akun</th>
                        <th class="py-2 text-right">Saldo Awal</th>
                        <th class="py-2 text-right">Masuk</th>
                        <th class="py-2 text-right">Keluar</th>
                        <th class="py-2 text-right">Saldo Akhir</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($cash['rows'] as $row)
                        <tr class="border-b last:border-0">
                            <td class="py-2">{{ $row['account']->code }} — {{ $row['account']->name }}</td>
                            <td class="py-2 text-right">{{ number_format($row['opening'], 0, ',', '.') }}</td>
                            <td class="py-2 text-right text-green-600">{{ number_format($row['receipts'], 0, ',', '.') }}</td>
                            <td class="py-2 text-right text-red-600">{{ number_format($row['payments'], 0, ',', '.') }}</td>
                            <td class="py-2 text-right font-bold">{{ number_format($row['closing'], 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="font-bold bg-gray-50">
                        <td class="py-2 px-1">TOTAL</td>
                        <td class="py-2 text-right">{{ number_format($cash['totals']['opening'], 0, ',', '.') }}</td>
                        <td class="py-2 text-right text-green-700">{{ number_format($cash['totals']['receipts'], 0, ',', '.') }}</td>
                        <td class="py-2 text-right text-red-700">{{ number_format($cash['totals']['payments'], 0, ',', '.') }}</td>
                        <td class="py-2 text-right">{{ number_format($cash['totals']['closing'], 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        @else
            <p class="text-sm text-gray-500">Belum ada akun kas/bank. Jalankan seeder akuntansi terlebih dahulu.</p>
        @endif
    </div>

    <div class="bg-white rounded shadow p-5">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold">Jurnal Terbaru</h3>
            <span class="text-xs text-gray-500">{{ $activeAccounts }} akun aktif — periode terakhir: {{ $lastPeriod?->period ?? '-' }}</span>
        </div>
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-gray-500 border-b">
                    <th class="py-2">No</th>
                    <th class="py-2">Tanggal</th>
                    <th class="py-2">Keterangan</th>
                    <th class="py-2 text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentEntries as $entry)
                    <tr class="border-b last:border-0">
                        <td class="py-2">
                            <a href="{{ route('accounting.journals.show', $entry) }}" class="text-indigo-600 hover:underline">{{ $entry->entry_number }}</a>
                        </td>
                        <td class="py-2">{{ $entry->date->format('d/m/Y') }}</td>
                        <td class="py-2">
                            {{ \Illuminate\Support\Str::limit($entry->memo, 40) }}
                            @if($entry->is_void)
                                <span class="text-xs text-red-600">(void)</span>
                            @endif
                        </td>
                        <td class="py-2 text-right">{{ number_format($entry->total_debit, 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="py-4 text-center text-gray-500">Belum ada jurnal</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="bg-white rounded shadow p-5 text-sm text-gray-600">
    <h3 class="text-base font-bold text-gray-800 mb-2">Catatan Kaidah Akuntansi</h3>
    <ul class="list-disc pl-5 space-y-1">
        <li>Pencatatan memakai <strong>basis kas</strong>: pendapatan diakui saat uang diterima, beban saat uang dibayar.</li>
        <li>Jurnal dibuat otomatis dari: pembayaran reservasi/OTA (termasuk refund), pendapatan resto, other revenue, deposit tamu, dan pengeluaran.</li>
        <li>Deposit kartu dicatat sebagai <strong>liabilitas</strong> (Uang Jaminan Tamu), bukan pendapatan; jurnal berbalik ketika deposit dikembalikan.</li>
        <li>Periode yang sudah ditutup tidak dapat diubah — buka kembali periode bila perlu koreksi.</li>
    </ul>
</div>
@endsection
