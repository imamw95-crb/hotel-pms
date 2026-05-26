@extends('layouts.app')

@section('title', 'Tambah Tamu')

@section('content')
<div class="bg-white rounded-lg shadow p-6 max-w-2xl mx-auto">
    <h2 class="text-2xl font-bold mb-6">Tambah Tamu Baru</h2>

    <form method="POST" action="{{ route('guests.store') }}">
        @csrf

        <div class="mb-4">
            <label class="block text-gray-700 mb-2">Nama Tamu <span class="text-red-500">*</span></label>
            <input type="text" name="guest_name" value="{{ old('guest_name') }}" class="w-full border rounded px-3 py-2" required>
            @error('guest_name')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>

        <div class="grid grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-gray-700 mb-2">No. Identitas</label>
                <input type="text" name="id_number" value="{{ old('id_number') }}" class="w-full border rounded px-3 py-2">
                @error('id_number')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>
            <div>
                <label class="block text-gray-700 mb-2">No. Telepon</label>
                <input type="tel" name="phone" value="{{ old('phone') }}" class="w-full border rounded px-3 py-2">
                @error('phone')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 mb-2">Email</label>
            <input type="email" name="email" value="{{ old('email') }}" class="w-full border rounded px-3 py-2">
            @error('email')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 mb-2">Alamat</label>
            <textarea name="address" class="w-full border rounded px-3 py-2" rows="3">{{ old('address') }}</textarea>
            @error('address')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 mb-2">Catatan</label>
            <textarea name="notes" class="w-full border rounded px-3 py-2" rows="3">{{ old('notes') }}</textarea>
            @error('notes')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>

        <div class="flex justify-end space-x-2">
            <a href="{{ route('guests.index') }}" class="bg-gray-400 text-white px-4 py-2 rounded hover:bg-gray-500">Batal</a>
            <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Simpan</button>
        </div>
    </form>
</div>
@endsection
