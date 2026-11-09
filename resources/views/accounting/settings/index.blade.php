@extends('layouts.app')

@section('title', 'Pemetaan Akun')
@section('header', 'Akunting — Pemetaan Akun Otomatis')

@section('content')
@include('accounting.partials.nav')

@php
    $sections = [
        'payment_method' => [
            'title' => 'Akun Kas/Bank per Metode Pembayaran',
            'hint' => 'Menentukan akun kas/bank yang di-debit saat uang diterima lewat metode tersebut.',
            'accounts' => $cashAccounts,
        ],
        'revenue' => [
            'title' => 'Akun Pendapatan per Tipe Transaksi',
            'hint' => 'Menentukan akun pendapatan yang di-kredit saat pembayaran reservasi diterima.',
            'accounts' => $revenueAccounts,
        ],
        'expense' => [
            'title' => 'Akun Beban per Kategori Pengeluaran',
            'hint' => 'Menentukan akun beban yang di-debit saat pengeluaran dicatat.',
            'accounts' => $expenseAccounts,
        ],
    ];
@endphp

<form method="POST" action="{{ route('accounting.settings.update') }}">
    @csrf

    @foreach($sections as $groupKey => $section)
        <div class="bg-white rounded shadow overflow-hidden mb-6">
            <div class="px-4 py-3 bg-gray-100 border-b">
                <h3 class="font-bold text-gray-700">{{ $section['title'] }}</h3>
                <p class="text-xs text-gray-500 mt-1">{{ $section['hint'] }}</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-gray-600">
                        <tr>
                            <th class="px-4 py-2 text-left w-1/3">Sumber Transaksi</th>
                            <th class="px-4 py-2 text-left">Akun</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($mappings->get($groupKey, collect()) as $mapping)
                            <tr class="border-b">
                                <td class="px-4 py-2">
                                    <div class="font-medium">{{ $mapping->label ?? $mapping->key }}</div>
                                    <div class="text-xs text-gray-400 font-mono">{{ $mapping->key }}</div>
                                </td>
                                <td class="px-4 py-2">
                                    <select name="mappings[{{ $mapping->id }}]" class="w-full border rounded px-2 py-1">
                                        <option value="">— Tidak dipetakan (pakai default) —</option>
                                        @foreach($section['accounts'] as $account)
                                            <option value="{{ $account->id }}" {{ $mapping->account_id === $account->id ? 'selected' : '' }}>
                                                {{ $account->code }} — {{ $account->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="2" class="px-4 py-4 text-center text-gray-500">Belum ada data pemetaan.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endforeach

    <div class="mb-8">
        <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 font-semibold">
            <i class="fas fa-save mr-2"></i> Simpan Pemetaan
        </button>
    </div>
</form>

<div class="bg-white rounded shadow p-5 text-sm text-gray-600">
    <h3 class="text-base font-bold text-gray-800 mb-2">Cara kerja pemetaan</h3>
    <ul class="list-disc pl-5 space-y-1">
        <li>Pemetaan dipakai saat jurnal dibuat otomatis. Bila kosong, sistem memakai akun default (Kas, Bank, Pendapatan Kamar, Beban Lain-lain).</li>
        <li>Metode pembayaran baru otomatis muncul di sini setelah ditambahkan di Master Data → Metode Pembayaran.</li>
        <li>Kategori beban berasal dari form Pengeluaran (field <em>Kategori Beban</em>).</li>
    </ul>
</div>
@endsection
