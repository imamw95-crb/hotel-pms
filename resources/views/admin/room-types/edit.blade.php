@extends('layouts.app')

@section('title', 'Edit Tipe Kamar')

@section('content')
<div class="bg-white rounded-lg shadow p-6 max-w-2xl mx-auto">
    <h2 class="text-2xl font-bold mb-6">Edit Tipe Kamar</h2>
    <form method="POST" action="{{ route('room-types.update', $roomType) }}">
        @csrf
        @method('PUT')
        <div class="mb-4">
            <label class="block text-gray-700 mb-2">Kode Tipe</label>
            <input type="text" name="code" value="{{ $roomType->code }}" class="w-full border rounded px-3 py-2" required readonly>
            @error('code')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 mb-2">Nama Tipe</label>
            <input type="text" name="name" value="{{ $roomType->name }}" class="w-full border rounded px-3 py-2" required>
            @error('name')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 mb-2">Urutan</label>
            <input type="number" name="sequence" value="{{ $roomType->sequence }}" class="w-full border rounded px-3 py-2">
            @error('sequence')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>

        <div class="flex justify-end space-x-2">
            <a href="{{ route('room-types.index') }}" class="bg-gray-400 text-white px-4 py-2 rounded">Batal</a>
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Update</button>
        </div>
    </form>
</div>
@endsection
