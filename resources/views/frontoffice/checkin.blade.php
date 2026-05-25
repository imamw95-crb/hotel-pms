@extends('layouts.app')

@section('title', 'Check-in')

@section('content')
<div class="bg-white rounded-lg shadow p-6 max-w-2xl mx-auto">
    <h2 class="text-2xl font-bold mb-6">Check-in Tamu</h2>
    <form method="POST" action="{{ route('checkin.process') }}">
        @csrf
        <div class="mb-4">
            <label class="block text-gray-700 mb-2">Pilih Kamar</label>
            <select name="room_id" class="w-full border rounded px-3 py-2" required>
                <option value="">-- Pilih Kamar --</option>
                @foreach($rooms as $room)
                    <option value="{{ $room->id }}">
                        {{ $room->room_number }}
                    </option>
                @endforeach
            </select>
            @error('room_id')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>

        <div class="grid grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-gray-700 mb-2">Nama Tamu</label>
                <input type="text" name="guest_name" class="w-full border rounded px-3 py-2" required>
                @error('guest_name')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>
            <div>
                <label class="block text-gray-700 mb-2">No. Identitas</label>
                <input type="text" name="id_number" class="w-full border rounded px-3 py-2">
                @error('id_number')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-gray-700 mb-2">No. Telepon</label>
                <input type="tel" name="phone" class="w-full border rounded px-3 py-2">
                @error('phone')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>
            <div>
                <label class="block text-gray-700 mb-2">Email</label>
                <input type="email" name="email" class="w-full border rounded px-3 py-2">
                @error('email')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-gray-700 mb-2">Check-in</label>
                <input type="datetime-local" name="check_in" class="w-full border rounded px-3 py-2" required>
                @error('check_in')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>
            <div>
                <label class="block text-gray-700 mb-2">Check-out</label>
                <input type="datetime-local" name="check_out" class="w-full border rounded px-3 py-2" required>
                @error('check_out')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 mb-2">Metode Pembayaran</label>
            <select name="payment_method" class="w-full border rounded px-3 py-2">
                <option value="cash">Tunai</option>
                <option value="bank_transfer">Transfer Bank</option>
                <option value="credit_card">Kartu Kredit</option>
            </select>
            @error('payment_method')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 mb-2">Jumlah Pembayaran (Rp)</label>
            <input type="number" name="payment_amount" class="w-full border rounded px-3 py-2" value="0" min="0">
            @error('payment_amount')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>

        <div class="flex justify-end space-x-2">
            <a href="{{ route('frontoffice.dashboard') }}" class="bg-gray-400 text-white px-4 py-2 rounded">Batal</a>
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Check-in</button>
        </div>
    </form>
</div>
@endsection
