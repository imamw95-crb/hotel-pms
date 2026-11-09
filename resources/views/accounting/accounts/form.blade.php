@extends('layouts.app')

@section('title', $account->exists ? 'Edit Akun' : 'Tambah Akun')
@section('header', 'Akunting — Chart of Accounts')

@section('content')
@include('accounting.partials.nav')

<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-lg shadow p-6">
        <form method="POST" action="{{ $account->exists ? route('accounting.accounts.update', $account) : route('accounting.accounts.store') }}">
            @csrf
            @if($account->exists)
                @method('PUT')
            @endif

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Kode Akun <span class="text-red-500">*</span></label>
                <input type="text" name="code" value="{{ old('code', $account->code) }}" required maxlength="20"
                       class="w-full border rounded px-3 py-2 @error('code') border-red-500 @enderror" placeholder="cth: 1-1100">
                @error('code') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Nama Akun <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name', $account->name) }}" required maxlength="120"
                       class="w-full border rounded px-3 py-2 @error('name') border-red-500 @enderror">
                @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">Tipe Akun <span class="text-red-500">*</span></label>
                    <select name="type" required class="w-full border rounded px-3 py-2 @error('type') border-red-500 @enderror">
                        @foreach(\App\Models\Account::TYPES as $key => $label)
                            <option value="{{ $key }}" {{ old('type', $account->type) === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('type') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">Saldo Normal <span class="text-red-500">*</span></label>
                    <select name="normal_balance" required class="w-full border rounded px-3 py-2">
                        <option value="debit" {{ old('normal_balance', $account->normal_balance) === 'debit' ? 'selected' : '' }}>Debit</option>
                        <option value="credit" {{ old('normal_balance', $account->normal_balance) === 'credit' ? 'selected' : '' }}>Kredit</option>
                    </select>
                </div>
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Kelompok Laporan</label>
                <input type="text" name="group_label" value="{{ old('group_label', $account->group_label) }}" maxlength="80"
                       class="w-full border rounded px-3 py-2" placeholder="cth: Pendapatan Kamar / Beban Kamar / Aset Lancar">
                <p class="text-xs text-gray-500 mt-1">Dipakai untuk mengelompokkan baris pada Laba Rugi & Neraca.</p>
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Urutan Tampil</label>
                <input type="number" name="sort_order" value="{{ old('sort_order', $account->sort_order ?? 0) }}" min="0"
                       class="w-full border rounded px-3 py-2">
            </div>

            <div class="mb-4 flex items-center gap-6">
                <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                    <input type="checkbox" name="is_cash" value="1" {{ old('is_cash', $account->is_cash) ? 'checked' : '' }}>
                    Akun Kas/Bank (masuk laporan arus kas)
                </label>
                <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $account->is_active ?? true) ? 'checked' : '' }}>
                    Akun Aktif
                </label>
            </div>

            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-2">Keterangan</label>
                <textarea name="description" rows="2" maxlength="255" class="w-full border rounded px-3 py-2">{{ old('description', $account->description) }}</textarea>
            </div>

            <div class="flex gap-3">
                <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 font-semibold">
                    <i class="fas fa-save mr-2"></i> Simpan
                </button>
                <a href="{{ route('accounting.accounts.index') }}" class="bg-gray-500 text-white px-6 py-2 rounded-lg hover:bg-gray-600 font-semibold">
                    <i class="fas fa-arrow-left mr-2"></i> Kembali
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
