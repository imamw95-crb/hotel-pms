@extends('layouts.app')

@section('title', 'Edit Kamar')

@section('content')
<div class="bg-white rounded-lg shadow p-6 max-w-2xl mx-auto">
    <div class="flex items-center gap-3 mb-6">
        <div class="w-10 h-10 rounded-lg bg-amber-100 flex items-center justify-center flex-shrink-0">
            <i class="fas fa-edit text-amber-600"></i>
        </div>
        <div>
            <h2 class="text-xl font-bold text-gray-800">Edit Kamar</h2>
            <p class="text-sm text-gray-500">Ubah data kamar <span class="font-semibold">{{ $room->room_number }}</span></p>
        </div>
    </div>

    <form method="POST" action="{{ route('rooms.update', $room) }}" data-ajax="true" data-refresh="true">
        @csrf
        @method('PUT')

        <!-- Section: Info Dasar -->
        <div class="mb-5">
            <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Informasi Dasar</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">No. Kamar</label>
                    <input type="text" name="room_number" value="{{ $room->room_number }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm bg-gray-50 text-gray-500" readonly>
                    <p class="text-xs text-gray-400 mt-1">Nomor kamar tidak dapat diubah</p>
                    @error('room_number')
                        <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tipe Kamar</label>
                    <select name="room_type_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                        <option value="">-- Pilih Tipe --</option>
                        @foreach($roomTypes as $type)
                            <option value="{{ $type->id }}" data-capacity="{{ $type->base_capacity ?? '' }}" {{ $room->room_type_id == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                        @endforeach
                    </select>
                    @error('room_type_id')
                        <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Section: Harga -->
        <div class="mb-5">
            <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Tarif per Malam</h3>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Weekday (Rp)</label>
                    <input type="number" name="price_weekday" value="{{ $room->price_weekday ?? old('price_weekday') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition" min="0" step="1000" placeholder="Senin–Jumat">
                    @error('price_weekday')
                        <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Weekend (Rp)</label>
                    <input type="number" name="price_weekend" value="{{ $room->price_weekend ?? old('price_weekend') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition" min="0" step="1000" placeholder="Sabtu–Minggu">
                    @error('price_weekend')
                        <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Default <span class="text-red-500">*</span></label>
                    <input type="number" name="price_per_night" value="{{ $room->price_per_night }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition" required min="0" step="1000" placeholder="Fallback harga">
                    @error('price_per_night')
                        <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                    @enderror
                </div>
            </div>
            <p class="text-xs text-gray-400 mt-2"><i class="fas fa-info-circle mr-1"></i>Jika weekday & weekend dikosongkan, harga default akan digunakan untuk semua hari.</p>
        </div>

        <!-- Section: Detail -->
        <div class="mb-6">
            <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Detail Kamar</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Maksimal Okupansi <span class="text-red-500">*</span></label>
                    <input type="number" name="max_occupancy" value="{{ $room->max_occupancy }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition" required min="1" placeholder="Jumlah tamu maks.">
                    @error('max_occupancy')
                        <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status <span class="text-red-500">*</span></label>
                    <select name="status" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition" required>
                        <option value="available" {{ $room->status === 'available' ? 'selected' : '' }}>Tersedia</option>
                        <option value="occupied" {{ $room->status === 'occupied' ? 'selected' : '' }}>Ditempati</option>
                        <option value="maintenance" {{ $room->status === 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                        <option value="cleaning" {{ $room->status === 'cleaning' ? 'selected' : '' }}>Pembersihan</option>
                    </select>
                    @error('status')
                        <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
            <button type="button" onclick="Modal.close()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition cursor-pointer">Batal</button>
            <button type="submit" class="px-5 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition cursor-pointer">
                <i class="fas fa-save mr-1"></i> Update
            </button>
        </div>
    </form>
</div>
@endsection
