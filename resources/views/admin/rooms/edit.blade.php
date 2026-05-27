@extends('layouts.app')

@section('title', 'Edit Kamar')

@section('content')
<div class="bg-white rounded-lg shadow p-6 max-w-2xl mx-auto">
    <h2 class="text-2xl font-bold mb-6">Edit Kamar</h2>
    <form method="POST" action="{{ route('rooms.update', $room) }}" data-ajax="true">
        @csrf
        @method('PUT')
        <div class="mb-4">
            <label class="block text-gray-700 mb-2">No. Kamar</label>
            <input type="text" name="room_number" value="{{ $room->room_number }}" class="w-full border rounded px-3 py-2" required readonly>
            @error('room_number')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 mb-2">Tipe Kamar</label>
            <select name="room_type_id" class="w-full border rounded px-3 py-2">
                <option value="">-- Pilih Tipe --</option>
                @foreach($roomTypes as $type)
                    <option value="{{ $type->id }}" {{ $room->room_type_id === $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                @endforeach
            </select>
            @error('room_type_id')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 mb-2">Harga per Malam — Weekday (Rp)</label>
            <input type="number" name="price_weekday" value="{{ $room->price_weekday ?? $room->price_per_night }}" class="w-full border rounded px-3 py-2" min="0" step="1000" placeholder="Harga hari biasa (Senin–Jumat)">
            @error('price_weekday')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 mb-2">Harga per Malam — Weekend (Rp)</label>
            <input type="number" name="price_weekend" value="{{ $room->price_weekend ?? $room->price_per_night }}" class="w-full border rounded px-3 py-2" min="0" step="1000" placeholder="Harga akhir pekan (Sabtu–Minggu)">
            @error('price_weekend')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 mb-2">Harga Default / Fallback (Rp)</label>
            <input type="number" name="price_per_night" value="{{ $room->price_per_night }}" class="w-full border rounded px-3 py-2" required min="0" step="1000" placeholder="Digunakan jika weekday/weekend tidak diisi">
            <p class="text-xs text-gray-500 mt-1">Jika weekday & weekend dikosongkan, harga ini akan digunakan untuk semua hari.</p>
            @error('price_per_night')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 mb-2">Maksimal Okupansi</label>
            <input type="number" name="max_occupancy" value="{{ $room->max_occupancy }}" class="w-full border rounded px-3 py-2" required>
            @error('max_occupancy')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 mb-2">Status</label>
            <select name="status" class="w-full border rounded px-3 py-2" required>
                <option value="available" {{ $room->status === 'available' ? 'selected' : '' }}>Tersedia</option>
                <option value="occupied" {{ $room->status === 'occupied' ? 'selected' : '' }}>Ditempati</option>
                <option value="maintenance" {{ $room->status === 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                <option value="cleaning" {{ $room->status === 'cleaning' ? 'selected' : '' }}>Pembersihan</option>
            </select>
            @error('status')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>

        <div class="flex justify-end space-x-2">
            <a href="{{ route('rooms.index') }}" class="bg-gray-400 text-white px-4 py-2 rounded">Batal</a>
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Update</button>
        </div>
    </form>
</div>
@endsection
