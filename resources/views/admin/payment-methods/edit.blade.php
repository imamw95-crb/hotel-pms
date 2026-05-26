@extends('layouts.app')

@section('title', 'Edit Metode Pembayaran')
@section('header', 'Edit Metode Pembayaran')

@section('content')
<div class="max-w-xl mx-auto">

    <form method="POST" action="{{ route('admin.payment-methods.update', $paymentMethod) }}" class="bg-white rounded-lg shadow">
        @csrf
        @method('PUT')

        <div class="p-6 border-b">
            <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">
                <i class="fas fa-credit-card text-blue-500 mr-1"></i> Nama Metode Pembayaran
            </label>
            <input type="text" name="name" id="name"
                   value="{{ old('name', $paymentMethod->name) }}"
                   class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
                   placeholder="Contoh: E-Wallet, QRIS, dll." required>
            @error('name')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="p-6 border-b">
            <label class="flex items-center gap-3 cursor-pointer">
                <input type="checkbox" name="is_active" value="1"
                       {{ $paymentMethod->is_active ? 'checked' : '' }}
                       class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                <span class="text-sm font-medium text-gray-700">Aktif</span>
            </label>
        </div>

        <div class="p-6 bg-gray-50 rounded-b-lg flex justify-between">
            <a href="{{ route('admin.payment-methods.index') }}" class="text-gray-500 hover:text-gray-700 font-medium px-4 py-2.5 transition">
                <i class="fas fa-arrow-left mr-1"></i> Kembali
            </a>
            <button type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-2.5 rounded-lg transition flex items-center gap-2">
                <i class="fas fa-save"></i> Simpan Perubahan
            </button>
        </div>
    </form>

</div>
@endsection
