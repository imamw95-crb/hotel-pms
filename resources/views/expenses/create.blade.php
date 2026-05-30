@extends('layouts.app')

@section('title', 'Tambah Pengeluaran')
@section('header', 'Tambah Pengeluaran')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-lg shadow p-6">
        <p class="text-sm text-gray-500 mb-6">Catat pengeluaran hotel (operasional, supplies, dll).</p>

        <form method="POST" action="{{ route('expenses.store') }}">
            @csrf

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Deskripsi <span class="text-red-500">*</span></label>
                <input type="text" name="description" value="{{ old('description') }}" required
                    class="w-full border rounded px-3 py-2 @error('description') border-red-500 @enderror"
                    placeholder="Misal: Beli ATK, Listrik, Air mineral...">
                @error('description') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Jumlah (Rp) <span class="text-red-500">*</span></label>
                <input type="number" name="amount" value="{{ old('amount') }}" required min="0" step="0.01"
                    class="w-full border rounded px-3 py-2 @error('amount') border-red-500 @enderror"
                    placeholder="0">
                @error('amount') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Metode Pembayaran <span class="text-red-500">*</span></label>
                <select name="payment_method" required
                    class="w-full border rounded px-3 py-2 @error('payment_method') border-red-500 @enderror">
                    <option value="">— Pilih Metode —</option>
                    <option value="cash" {{ old('payment_method') === 'cash' ? 'selected' : '' }}>Cash (Tunai)</option>
                    <option value="bank_transfer" {{ old('payment_method') === 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                    <option value="credit_card" {{ old('payment_method') === 'credit_card' ? 'selected' : '' }}>Credit Card</option>
                    <option value="debit_card" {{ old('payment_method') === 'debit_card' ? 'selected' : '' }}>Debit Card</option>
                    <option value="virtual_account" {{ old('payment_method') === 'virtual_account' ? 'selected' : '' }}>Virtual Account</option>
                    <option value="ewallet" {{ old('payment_method') === 'ewallet' ? 'selected' : '' }}>E-Wallet</option>
                    <option value="qris" {{ old('payment_method') === 'qris' ? 'selected' : '' }}>QRIS</option>
                    <option value="other" {{ old('payment_method') === 'other' ? 'selected' : '' }}>Lainnya</option>
                </select>
                @error('payment_method') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Tanggal Pengeluaran <span class="text-red-500">*</span></label>
                <input type="date" name="expense_date" value="{{ old('expense_date', date('Y-m-d')) }}" required
                    class="w-full border rounded px-3 py-2 @error('expense_date') border-red-500 @enderror">
                @error('expense_date') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-2">Catatan (opsional)</label>
                <textarea name="notes" rows="3"
                    class="w-full border rounded px-3 py-2 @error('notes') border-red-500 @enderror"
                    placeholder="Keterangan tambahan...">{{ old('notes') }}</textarea>
                @error('notes') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="flex gap-3">
                <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 font-semibold">
                    <i class="fas fa-save mr-2"></i> Simpan Pengeluaran
                </button>
                <a href="{{ route('expenses.index') }}" class="bg-gray-500 text-white px-6 py-2 rounded-lg hover:bg-gray-600 font-semibold">
                    <i class="fas fa-arrow-left mr-2"></i> Kembali
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
