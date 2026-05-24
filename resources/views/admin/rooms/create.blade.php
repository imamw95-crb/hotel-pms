@extends('layouts.app')

@section('title', 'Tambah Kamar')

@section('content')
<div class="bg-white rounded-lg shadow p-6 max-w-2xl mx-auto">
    <h2 class="text-2xl font-bold mb-6">Tambah Kamar</h2>
    <form method="POST" action="{{ route('rooms.store') }}">
        @csrf
        <div class="mb-4">
            <label class="block text-gray-700 mb-2">No. Kamar</label>
            <input type="text" name="room_number" class="w-full border rounded px-3 py-2" required>
            @error('room_number')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 mb-2">Tipe Kamar</label>
            <select name="room_type_id" class="w-full border rounded px-3 py-2">
                <option value="">-- Pilih Tipe --</option>
                @foreach($roomTypes as $type)
                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                @endforeach
            </select>
            @error('room_type_id')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 mb-2">Harga per Malam (Rp)</label>
            <input type="number" name="price_per_night" class="w-full border rounded px-3 py-2" required>
            @error('price_per_night')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 mb-2">Maksimal Okupansi</label>
            <input type="number" name="max_occupancy" class="w-full border rounded px-3 py-2" required>
            @error('max_occupancy')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 mb-2">Status</label>
            <select name="status" class="w-full border rounded px-3 py-2" required>
                <option value="available">Tersedia</option>
                <option value="occupied">Ditempati</option>
                <option value="maintenance">Maintenance</option>
                <option value="cleaning">Pembersihan</option>
            </select>
            @error('status')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>

        <div class="flex justify-end space-x-2">
            <a href="{{ route('rooms.index') }}" class="bg-gray-400 text-white px-4 py-2 rounded">Batal</a>
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Simpan</button>
        </div>
    </form>
</div>
@endsection
