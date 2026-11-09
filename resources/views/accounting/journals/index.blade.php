@extends('layouts.app')

@section('title', 'Jurnal Umum')
@section('header', 'Akunting — Jurnal Umum')

@section('content')
@include('accounting.partials.nav')

<div class="mb-6 flex flex-wrap items-end gap-4">
    <form method="GET" class="flex flex-wrap items-end gap-4">
        <div>
            <label class="block text-gray-700 text-sm font-bold mb-2">Dari Tanggal</label>
            <input type="date" name="start_date" value="{{ $start }}" class="border rounded px-3 py-2">
        </div>
        <div>
            <label class="block text-gray-700 text-sm font-bold mb-2">Sampai Tanggal</label>
            <input type="date" name="end_date" value="{{ $end }}" class="border rounded px-3 py-2">
        </div>
        <div>
            <label class="block text-gray-700 text-sm font-bold mb-2">Sumber</label>
            <select name="source_type" class="border rounded px-3 py-2">
                <option value="">Semua Sumber</option>
                @foreach(\App\Models\JournalEntry::SOURCE_LABELS as $key => $label)
                    <option value="{{ $key }}" {{ $source === $key ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-gray-700 text-sm font-bold mb-2">Status</label>
            <select name="status" class="border rounded px-3 py-2">
                <option value="">Semua</option>
                <option value="posted" {{ $status === 'posted' ? 'selected' : '' }}>Posted</option>
                <option value="void" {{ $status === 'void' ? 'selected' : '' }}>Void</option>
            </select>
        </div>
        <div>
            <label class="block text-gray-700 text-sm font-bold mb-2">Cari</label>
            <input type="text" name="search" value="{{ $search }}" class="border rounded px-3 py-2" placeholder="No / keterangan">
        </div>
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
            <i class="fas fa-search mr-1"></i> Filter
        </button>
    </form>

    @if(hasPermission('post_journal'))
        <a href="{{ route('accounting.journals.create') }}" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
            <i class="fas fa-plus mr-1"></i> Jurnal Manual
        </a>
    @endif
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
    <div class="bg-white p-5 rounded shadow">
        <div class="text-gray-500 text-sm">Jumlah Jurnal (posted)</div>
        <div class="text-2xl font-bold">{{ number_format($totals->entries ?? 0, 0, ',', '.') }}</div>
    </div>
    <div class="bg-white p-5 rounded shadow">
        <div class="text-gray-500 text-sm">Total Nilai Jurnal</div>
        <div class="text-2xl font-bold text-indigo-600">Rp {{ number_format((float) ($totals->total ?? 0), 0, ',', '.') }}</div>
    </div>
</div>

<div class="bg-white rounded shadow overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-100">
                <tr class="text-left text-gray-600">
                    <th class="px-4 py-2">No. Jurnal</th>
                    <th class="px-4 py-2">Tanggal</th>
                    <th class="px-4 py-2">Keterangan</th>
                    <th class="px-4 py-2">Sumber</th>
                    <th class="px-4 py-2 text-right">Total Debit</th>
                    <th class="px-4 py-2 text-center">Status</th>
                    <th class="px-4 py-2 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($entries as $entry)
                    <tr class="border-b hover:bg-gray-50 {{ $entry->is_void ? 'text-gray-400' : '' }}">
                        <td class="px-4 py-2 font-mono">{{ $entry->entry_number }}</td>
                        <td class="px-4 py-2">{{ $entry->date->format('d/m/Y') }}</td>
                        <td class="px-4 py-2">{{ \Illuminate\Support\Str::limit($entry->memo, 60) }}</td>
                        <td class="px-4 py-2">{{ $entry->source_label }}</td>
                        <td class="px-4 py-2 text-right">{{ number_format($entry->total_debit, 0, ',', '.') }}</td>
                        <td class="px-4 py-2 text-center">
                            @if($entry->is_void)
                                <span class="px-2 py-0.5 rounded bg-red-100 text-red-700 text-xs">Void</span>
                            @else
                                <span class="px-2 py-0.5 rounded bg-green-100 text-green-700 text-xs">Posted</span>
                            @endif
                        </td>
                        <td class="px-4 py-2 text-center">
                            <a href="{{ route('accounting.journals.show', $entry) }}" class="text-blue-600 hover:underline">Lihat</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-6 text-center text-gray-500">Tidak ada jurnal pada periode ini.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-4">
    {{ $entries->links() }}
</div>
@endsection
