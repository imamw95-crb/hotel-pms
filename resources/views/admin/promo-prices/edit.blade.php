@extends('layouts.app')

@section('title', 'Edit Promo Harga')

@section('content')
<div class="bg-white rounded-lg shadow p-6 max-w-2xl mx-auto">
    <div class="flex items-center gap-3 mb-6">
        <div class="w-10 h-10 rounded-lg bg-amber-100 flex items-center justify-center flex-shrink-0">
            <i class="fas fa-edit text-amber-600"></i>
        </div>
        <div>
            <h2 class="text-xl font-bold text-gray-800">Edit Promo Harga</h2>
            <p class="text-sm text-gray-500">Ubah harga promo untuk {{ \Carbon\Carbon::parse($roomTypeDatePrice->date)->isoFormat('DD MMM YYYY') }}</p>
        </div>
    </div>

    <form method="POST" action="{{ route('promo-prices.update', $roomTypeDatePrice) }}" data-ajax="true" data-refresh="true">
        @csrf
        @method('PUT')

        {{-- Room Type --}}
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Tipe Kamar <span class="text-red-500">*</span></label>
            <select name="room_type_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition" required>
                <option value="">-- Pilih Tipe Kamar --</option>
                @foreach($roomTypes as $type)
                    <option value="{{ $type->id }}" {{ ($roomTypeDatePrice->room_type_id ?? old('room_type_id')) == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                @endforeach
            </select>
            @error('room_type_id')
                <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
            @enderror
        </div>

        {{-- Date --}}
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal <span class="text-red-500">*</span></label>
            <input type="date" name="date" value="{{ old('date', $roomTypeDatePrice->date->format('Y-m-d')) }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition" required>
            @error('date')
                <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
            @enderror
        </div>

        {{-- Price & Label --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Harga Promo (Rp) <span class="text-red-500">*</span></label>
                <input type="number" name="price" value="{{ old('price', $roomTypeDatePrice->price) }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition" required min="0" placeholder="Harga per malam">
                @error('price')
                    <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Label Promo</label>
                <input type="text" name="label" value="{{ old('label', $roomTypeDatePrice->label) }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition" maxlength="255" placeholder="Contoh: Promo Lebaran, High Season">
                <p class="text-xs text-gray-400 mt-1">Label untuk memudahkan identifikasi promo</p>
                @error('label')
                    <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                @enderror
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
            <button type="button" onclick="Modal.close()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition cursor-pointer">Batal</button>
            <button type="submit" class="px-5 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition cursor-pointer">
                <i class="fas fa-save mr-1"></i> Update
            </button>
        </div>
    </form>
</div>
@endsection
