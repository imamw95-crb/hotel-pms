@extends('layouts.app')

@section('title', 'Edit Master Group & Harga')

@section('content')
@php
    $priceMap = $master->prices->keyBy('room_type_id');
@endphp
<div class="bg-white rounded-lg shadow p-6 max-w-4xl mx-auto">
    <h2 class="text-2xl font-bold mb-2">Edit Master Group & Harga</h2>
    <p class="text-sm text-gray-500 mb-6">Perbarui paket harga per tipe kamar untuk booking group.</p>

    <form method="POST" action="{{ route('booking-group-masters.update', $master) }}" data-ajax="true">
        @csrf @method('PUT')

        <div class="grid grid-cols-3 gap-4 mb-4">
            <div>
                <label class="block text-gray-700 font-bold mb-2">Kode <span class="text-xs font-normal text-gray-500">(otomatis)</span></label>
                <input type="text" name="code" value="{{ old('code', $master->code) }}" class="w-full border rounded px-3 py-2 bg-gray-100" readonly>
            </div>
            <div class="col-span-2">
                <label class="block text-gray-700 font-bold mb-2">Nama Grup <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name', $master->name) }}" class="w-full border rounded px-3 py-2" placeholder="cth: Group Tour 2026" required>
            </div>
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 font-bold mb-2">Deskripsi</label>
            <textarea name="description" rows="2" class="w-full border rounded px-3 py-2" placeholder="Deskripsi grup (opsional)">{{ old('description', $master->description) }}</textarea>
        </div>

        <div class="mb-6">
            <label class="flex items-center space-x-2 cursor-pointer">
                <input type="checkbox" name="is_active" value="1" class="rounded border-gray-300" {{ $master->is_active ? 'checked' : '' }}>
                <span class="text-gray-700 font-medium">Aktif (bisa dipilih saat booking group)</span>
            </label>
        </div>

        {{-- Master Harga per Tipe Kamar --}}
        <div class="mb-4">
            <div class="flex items-center justify-between mb-2">
                <label class="block text-gray-700 font-bold">Master Harga per Tipe Kamar</label>
                <span class="text-sm text-gray-500">Isi harga per malam untuk tipe kamar yang masuk paket grup.</span>
            </div>

            @if($roomTypes->isEmpty())
                <div class="text-center py-8 text-gray-400 border rounded">
                    <i class="fas fa-door-open text-3xl mb-2 block"></i>
                    <p>Belum ada tipe kamar. Tambahkan tipe kamar terlebih dahulu.</p>
                </div>
            @else
                <div class="border rounded overflow-hidden">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-gray-100 border-b">
                                <th class="text-left p-2 font-bold">Tipe Kamar</th>
                                <th class="text-center p-2 font-bold">Harga per Malam (Rp)</th>
                                <th class="text-center p-2 font-bold">Termasuk Breakfast</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($roomTypes as $type)
                            @php
                                $price = $priceMap->get($type->id);
                                $oldPrice = old('prices.'.$type->id.'.price_per_night');
                                $oldBreakfast = old('prices.'.$type->id.'.include_breakfast');
                                $priceValue = $oldPrice ?? ($price?->price_per_night ?? '');
                                $breakfastChecked = $oldBreakfast !== null ? (bool) $oldBreakfast : ($price?->include_breakfast ?? true);
                            @endphp
                            <tr class="border-b">
                                <td class="p-2">
                                    <span class="font-medium">{{ $type->name }}</span>
                                    <span class="text-xs text-gray-400 ml-1">({{ $type->code }})</span>
                                </td>
                                <td class="p-2 text-center">
                                    <input type="number" name="prices[{{ $type->id }}][price_per_night]"
                                           value="{{ $priceValue }}"
                                           min="0" step="any" placeholder="0"
                                           class="w-40 border rounded px-2 py-1 text-center text-sm">
                                </td>
                                <td class="p-2 text-center">
                                    <input type="checkbox" name="prices[{{ $type->id }}][include_breakfast]" value="1"
                                           class="rounded border-gray-300" {{ $breakfastChecked ? 'checked' : '' }}>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <p class="text-xs text-gray-500 mt-1">Kosongkan harga untuk tipe kamar yang tidak termasuk paket grup.</p>
            @endif
        </div>

        <div class="flex justify-end space-x-2">
            <a href="{{ route('booking-group-masters.index') }}" class="bg-gray-400 text-white px-4 py-2 rounded hover:bg-gray-500">Batal</a>
            <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Simpan Perubahan</button>
        </div>
    </form>
</div>
@endsection
