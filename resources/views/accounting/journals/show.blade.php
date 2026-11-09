@extends('layouts.app')

@section('title', 'Detail Jurnal')
@section('header', 'Akunting — Jurnal Umum')

@section('content')
@include('accounting.partials.nav')

<div class="bg-white rounded-lg shadow p-6 mb-6">
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <div class="text-xs text-gray-500 uppercase tracking-wide">Nomor Jurnal</div>
            <div class="text-xl font-bold font-mono">{{ $entry->entry_number }}</div>
            <div class="text-sm text-gray-600 mt-1">{{ $entry->date->format('d/m/Y') }} — {{ $entry->source_label }}</div>
            <div class="text-sm text-gray-700 mt-2">{{ $entry->memo }}</div>
        </div>
        <div class="text-right">
            @if($entry->is_void)
                <span class="px-3 py-1 rounded bg-red-100 text-red-700 text-sm font-semibold">VOID</span>
                <div class="text-xs text-gray-500 mt-2">
                    {{ $entry->voided_at?->format('d/m/Y H:i') }}<br>
                    {{ $entry->void_reason }}
                </div>
            @else
                <span class="px-3 py-1 rounded bg-green-100 text-green-700 text-sm font-semibold">POSTED</span>
            @endif
            <div class="text-xs text-gray-500 mt-2">Dibuat oleh: {{ $entry->createdBy?->name ?? '-' }}</div>
        </div>
    </div>
</div>

<div class="bg-white rounded-lg shadow overflow-hidden mb-6">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-100">
                <tr class="text-left text-gray-600">
                    <th class="px-4 py-2">Kode</th>
                    <th class="px-4 py-2">Akun</th>
                    <th class="px-4 py-2">Memo</th>
                    <th class="px-4 py-2 text-right">Debit</th>
                    <th class="px-4 py-2 text-right">Kredit</th>
                </tr>
            </thead>
            <tbody>
                @foreach($entry->lines as $line)
                    <tr class="border-b">
                        <td class="px-4 py-2 font-mono">{{ $line->account?->code }}</td>
                        <td class="px-4 py-2">{{ $line->account?->name }}</td>
                        <td class="px-4 py-2 text-gray-600">{{ $line->memo }}</td>
                        <td class="px-4 py-2 text-right">{{ $line->debit > 0 ? number_format($line->debit, 0, ',', '.') : '' }}</td>
                        <td class="px-4 py-2 text-right">{{ $line->credit > 0 ? number_format($line->credit, 0, ',', '.') : '' }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="bg-gray-50 font-bold">
                    <td colspan="3" class="px-4 py-3 text-right">TOTAL</td>
                    <td class="px-4 py-3 text-right">{{ number_format($entry->lines->sum('debit'), 0, ',', '.') }}</td>
                    <td class="px-4 py-3 text-right">{{ number_format($entry->lines->sum('credit'), 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<div class="flex flex-wrap gap-3">
    <a href="{{ route('accounting.journals.index') }}" class="bg-gray-500 text-white px-6 py-2 rounded-lg hover:bg-gray-600 font-semibold">
        <i class="fas fa-arrow-left mr-2"></i> Kembali
    </a>

    @if(! $entry->is_void && hasPermission('post_journal'))
        <form method="POST" action="{{ route('accounting.journals.void', $entry) }}" class="flex flex-wrap items-end gap-2"
              onsubmit="return confirm('Batalkan jurnal {{ $entry->entry_number }}?')">
            @csrf
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-1">Alasan pembatalan</label>
                <input type="text" name="void_reason" maxlength="255" class="border rounded px-3 py-2" placeholder="cth: salah input nominal">
            </div>
            <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">
                <i class="fas fa-ban mr-1"></i> Void Jurnal
            </button>
        </form>
    @endif
</div>
@endsection
