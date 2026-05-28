@extends('layouts.app')

@section('title', 'Transaksi Resto')
@section('header', 'Transaksi Resto')

@section('content')
<div class="max-w-3xl mx-auto">

    <form method="POST" action="{{ route('resto.store') }}" class="bg-white rounded-lg shadow" id="restoForm" data-ajax="true" data-refresh="true">
        @csrf

        {{-- Info Box --}}
        <div class="bg-orange-50 border border-orange-200 rounded-t-lg p-4 flex items-start gap-3">
            <i class="fas fa-utensils text-orange-500 mt-0.5"></i>
            <div class="text-sm text-orange-700">
                <p><strong>Transaksi Resto / F&B</strong> — Catat pendapatan makanan & minuman.</p>
            </div>
        </div>

        {{-- Tamu (opsional) --}}
        <div class="p-6 border-b">
            <label for="guest_id" class="block text-sm font-semibold text-gray-700 mb-2">
                <i class="fas fa-user text-blue-500 mr-1"></i> Tamu <span class="text-gray-400 font-normal">(opsional)</span>
            </label>
            <select name="guest_id" id="guest_id"
                    class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
                <option value="">-- Tamu Umum / Walk-in --</option>
                @foreach($guests as $guest)
                    <option value="{{ $guest->id }}" {{ old('guest_id') == $guest->id ? 'selected' : '' }}>
                        {{ $guest->guest_name }} @if($guest->id_number)({{ $guest->id_number }})@endif
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Reservasi (opsional) --}}
        <div class="p-6 border-b">
            <label for="reservation_id" class="block text-sm font-semibold text-gray-700 mb-2">
                <i class="fas fa-calendar text-blue-500 mr-1"></i> Reservasi <span class="text-gray-400 font-normal">(opsional)</span>
            </label>
            <select name="reservation_id" id="reservation_id"
                    class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
                <option value="">-- Tanpa Reservasi --</option>
                @foreach($reservations as $res)
                    <option value="{{ $res->id }}"
                            data-guest="{{ $res->guest_id }}"
                            {{ old('reservation_id') == $res->id ? 'selected' : '' }}>
                        {{ $res->reservation_number }} — {{ $res->guest->guest_name ?? '-' }} ({{ $res->room->room_number ?? '-' }})
                    </option>
                @endforeach
            </select>
        </div>

        {{-- No. Meja --}}
        <div class="p-6 border-b">
            <label for="table_number" class="block text-sm font-semibold text-gray-700 mb-2">
                <i class="fas fa-chair text-blue-500 mr-1"></i> No. Meja <span class="text-gray-400 font-normal">(opsional)</span>
            </label>
            <input type="text" name="table_number" id="table_number"
                   value="{{ old('table_number') }}"
                   class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
                   placeholder="Contoh: A1, B3, VIP-1">
        </div>

        {{-- Items --}}
        <div class="p-6 border-b">
            <div class="flex justify-between items-center mb-3">
                <label class="block text-sm font-semibold text-gray-700">
                    <i class="fas fa-list text-blue-500 mr-1"></i> Item Pesanan
                </label>
                <button type="button" onclick="RestoForm.addItem()"
                        class="text-sm bg-green-500 hover:bg-green-600 text-white px-3 py-1.5 rounded-lg transition">
                    <i class="fas fa-plus mr-1"></i> Tambah Item
                </button>
            </div>

            <div id="itemsContainer" class="space-y-3">
                {{-- Item rows will be added here by JS --}}
            </div>
            @error('items')
                <p class="text-red-500 text-xs mt-2">{{ $message }}</p>
            @enderror
        </div>

        {{-- Tax & Discount --}}
        <div class="p-6 border-b">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="tax" class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-percent text-blue-500 mr-1"></i> Pajak (Rp)
                    </label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 font-semibold">Rp</span>
                        <input type="number" name="tax" id="tax" value="{{ old('tax', 0) }}" min="0" step="1000"
                               class="w-full border border-gray-300 rounded-lg pl-12 pr-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition text-right"
                               oninput="RestoForm._calculate()">
                    </div>
                </div>
                <div>
                    <label for="discount" class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-tag text-blue-500 mr-1"></i> Diskon (Rp)
                    </label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 font-semibold">Rp</span>
                        <input type="number" name="discount" id="discount" value="{{ old('discount', 0) }}" min="0" step="1000"
                               class="w-full border border-gray-300 rounded-lg pl-12 pr-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition text-right"
                               oninput="RestoForm._calculate()">
                    </div>
                </div>
            </div>
        </div>

        {{-- Total --}}
        <div class="p-6 border-b bg-gray-50">
            <div class="flex justify-between items-center mb-2">
                <span class="text-sm text-gray-600">Subtotal:</span>
                <span class="text-sm font-semibold" id="subtotalDisplay">Rp 0</span>
            </div>
            <div class="flex justify-between items-center mb-2">
                <span class="text-sm text-gray-600">Pajak:</span>
                <span class="text-sm font-semibold" id="taxDisplay">Rp 0</span>
            </div>
            <div class="flex justify-between items-center mb-2">
                <span class="text-sm text-gray-600">Diskon:</span>
                <span class="text-sm font-semibold text-red-600" id="discountDisplay">Rp 0</span>
            </div>
            <hr class="my-2">
            <div class="flex justify-between items-center">
                <span class="text-base font-bold text-gray-700">TOTAL:</span>
                <span class="text-2xl font-bold text-blue-700" id="totalDisplay">Rp 0</span>
            </div>
        </div>

        {{-- Metode Pembayaran --}}
        <div class="p-6 border-b">
            <label for="payment_method" class="block text-sm font-semibold text-gray-700 mb-2">
                <i class="fas fa-wallet text-blue-500 mr-1"></i> Metode Pembayaran
            </label>
            <select name="payment_method" id="payment_method" required
                    class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
                @php $pms = \App\Models\PaymentMethod::where('is_active', true)->orderBy('name')->get(); @endphp
                @foreach($pms as $pm)
                    <option value="{{ $pm->slug }}" {{ old('payment_method') == $pm->slug ? 'selected' : '' }}>{{ $pm->name }}</option>
                @endforeach
            </select>
        </div>

        {{-- Catatan --}}
        <div class="p-6 border-b">
            <label for="notes" class="block text-sm font-semibold text-gray-700 mb-2">
                <i class="fas fa-sticky-note text-blue-500 mr-1"></i> Catatan <span class="text-gray-400 font-normal">(opsional)</span>
            </label>
            <textarea name="notes" id="notes" rows="2"
                      class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition resize-none"
                      placeholder="Catatan tambahan...">{{ old('notes') }}</textarea>
        </div>

        {{-- Submit --}}
        <div class="p-6 bg-gray-50 rounded-b-lg flex justify-end">
            <button type="button" onclick="Modal.close()" class="text-gray-500 hover:text-gray-700 font-medium px-4 py-2.5 transition mr-4">
                <i class="fas fa-times mr-1"></i> Batal
            </button>
            <button type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-2.5 rounded-lg transition flex items-center gap-2">
                <i class="fas fa-save"></i> Simpan Transaksi
            </button>
        </div>
    </form>
</div>
@endsection