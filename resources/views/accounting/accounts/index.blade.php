@extends('layouts.app')

@section('title', 'Chart of Accounts')
@section('header', 'Akunting — Chart of Accounts')

@section('content')
@include('accounting.partials.nav')

<div class="mb-6 flex flex-wrap items-end gap-4">
    <form method="GET" class="flex flex-wrap items-end gap-4">
        <div>
            <label class="block text-gray-700 text-sm font-bold mb-2">Tipe Akun</label>
            <select name="type" class="border rounded px-3 py-2">
                <option value="">Semua Tipe</option>
                @foreach(\App\Models\Account::TYPES as $key => $label)
                    <option value="{{ $key }}" {{ $type === $key ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-gray-700 text-sm font-bold mb-2">Status</label>
            <select name="status" class="border rounded px-3 py-2">
                <option value="active" {{ $status === 'active' ? 'selected' : '' }}>Aktif</option>
                <option value="inactive" {{ $status === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                <option value="all" {{ $status === 'all' ? 'selected' : '' }}>Semua</option>
            </select>
        </div>
        <div>
            <label class="block text-gray-700 text-sm font-bold mb-2">Cari</label>
            <input type="text" name="search" value="{{ $search }}" class="border rounded px-3 py-2" placeholder="Kode / nama akun">
        </div>
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
            <i class="fas fa-search mr-1"></i> Filter
        </button>
    </form>

    @if(hasPermission('manage_accounting'))
        <a href="{{ route('accounting.accounts.create') }}" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
            <i class="fas fa-plus mr-1"></i> Tambah Akun
        </a>
    @endif
</div>

@forelse($accounts as $accountType => $group)
    <div class="bg-white rounded shadow overflow-hidden mb-6">
        <div class="px-4 py-3 bg-gray-100 border-b flex items-center justify-between">
            <h3 class="font-bold text-gray-700">{{ \App\Models\Account::TYPES[$accountType] ?? ucfirst($accountType) }}</h3>
            <span class="text-xs text-gray-500">{{ $group->count() }} akun</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-600">
                    <tr>
                        <th class="px-4 py-2 text-left">Kode</th>
                        <th class="px-4 py-2 text-left">Nama Akun</th>
                        <th class="px-4 py-2 text-left">Kelompok Laporan</th>
                        <th class="px-4 py-2 text-center">Saldo Normal</th>
                        <th class="px-4 py-2 text-center">Kas/Bank</th>
                        <th class="px-4 py-2 text-right">Saldo s/d Hari Ini</th>
                        <th class="px-4 py-2 text-center print:hidden">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($group as $account)
                        @php
                            $net = $balances[$account->id] ?? 0;
                        @endphp
                        <tr class="border-b hover:bg-gray-50 {{ $account->is_active ? '' : 'opacity-60' }}">
                            <td class="px-4 py-2 font-mono whitespace-nowrap">{{ $account->code }}</td>
                            <td class="px-4 py-2">
                                {{ $account->name }}
                                @unless($account->is_active)
                                    <span class="ml-1 text-xs text-gray-500">(nonaktif)</span>
                                @endunless
                            </td>
                            <td class="px-4 py-2 text-gray-600">{{ $account->group_label ?? '-' }}</td>
                            <td class="px-4 py-2 text-center uppercase text-xs">{{ $account->normal_balance }}</td>
                            <td class="px-4 py-2 text-center">
                                @if($account->is_cash)
                                    <i class="fas fa-check text-green-600"></i>
                                @endif
                            </td>
                            <td class="px-4 py-2 text-right">
                                {{ number_format(abs($net), 0, ',', '.') }}
                                <span class="text-xs text-gray-500">({{ $net >= 0 ? 'D' : 'K' }})</span>
                            </td>
                            <td class="px-4 py-2 text-center print:hidden whitespace-nowrap">
                                @if(hasPermission('manage_accounting'))
                                    <a href="{{ route('accounting.accounts.edit', $account) }}" class="text-blue-600 hover:underline mr-2">Edit</a>
                                    <form method="POST" action="{{ route('accounting.accounts.destroy', $account) }}" class="inline"
                                          onsubmit="return confirm('Hapus akun {{ $account->code }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:underline">Hapus</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@empty
    <div class="bg-white rounded shadow p-6 text-center text-gray-500">
        Belum ada akun. Jalankan <code class="bg-gray-100 px-1 rounded">php artisan db:seed --class=AccountingSeeder</code>
        untuk memuat Chart of Accounts standar hotel.
    </div>
@endforelse
@endsection
