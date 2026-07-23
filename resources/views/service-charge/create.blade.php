@extends('layouts.app')

@section('title', 'Other Revenue Baru')
@section('header', 'Other Revenue Baru')

@section('content')
<div class="max-w-3xl mx-auto">

    <form method="POST" action="{{ route('service-charge.store') }}" class="bg-white rounded-lg shadow" id="serviceChargeForm" data-ajax="true" data-refresh="true">
        @csrf

        {{-- Info Box --}}
        <div class="bg-blue-50 border border-blue-200 rounded-t-lg p-4 flex items-start gap-3">
            <i class="fas fa-receipt text-blue-500 mt-0.5"></i>
            <div class="text-sm text-blue-700">
                <p><strong>Other Revenue</strong> — Catat pendapatan lain selain kamar dan resto (laundry, extra bed, mini bar, room service, dll).</p>
            </div>
        </div>

        {{-- Reservasi --}}
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
                            {{ old('reservation_id', $selectedReservationId ?? '') == $res->id ? 'selected' : '' }}>
                        {{ $res->reservation_number }} — {{ $res->guest->guest_name ?? '-' }} ({{ $res->room->room_number ?? '-' }})
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Tamu --}}
        <div class="p-6 border-b">
            <label for="guest_id" class="block text-sm font-semibold text-gray-700 mb-2">
                <i class="fas fa-user text-blue-500 mr-1"></i> Tamu <span class="text-gray-400 font-normal">(opsional)</span>
            </label>
            <select name="guest_id" id="guest_id"
                    class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
                <option value="">-- Pilih Tamu --</option>
                @foreach($guests as $guest)
                    <option value="{{ $guest->id }}" {{ old('guest_id') == $guest->id ? 'selected' : '' }}>
                        {{ $guest->guest_name }} @if($guest->id_number)({{ $guest->id_number }})@endif
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Nama Layanan --}}
        <div class="p-6 border-b">
            <label for="service_name" class="block text-sm font-semibold text-gray-700 mb-2">
                <i class="fas fa-concierge-bell text-blue-500 mr-1"></i> Nama Layanan
            </label>
            <input type="text" name="service_name" id="service_name"
                   value="{{ old('service_name') }}"
                   class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
                   placeholder="Contoh: Laundry, Extra Bed, Mini Bar, Room Service"
                   required>
        </div>

        {{-- Deskripsi --}}
        <div class="p-6 border-b">
            <label for="description" class="block text-sm font-semibold text-gray-700 mb-2">
                <i class="fas fa-align-left text-blue-500 mr-1"></i> Deskripsi <span class="text-gray-400 font-normal">(opsional)</span>
            </label>
            <textarea name="description" id="description" rows="2"
                      class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition resize-none"
                      placeholder="Deskripsi layanan...">{{ old('description') }}</textarea>
        </div>

        {{-- Amount & Quantity --}}
        <div class="p-6 border-b">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="amount" class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-money-bill text-blue-500 mr-1"></i> Harga Satuan
                    </label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 font-semibold">Rp</span>
                        <input type="number" name="amount" id="amount" value="{{ old('amount', 0) }}" min="0"
                               class="w-full border border-gray-300 rounded-lg pl-12 pr-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition text-right"
                               oninput="SCForm._calculate()" required>
                    </div>
                </div>
                <div>
                    <label for="quantity" class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-times text-blue-500 mr-1"></i> Jumlah
                    </label>
                    <input type="number" name="quantity" id="quantity" value="{{ old('quantity', 1) }}" min="1"
                           class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition text-center"
                           oninput="SCForm._calculate()" required>
                </div>
            </div>
        </div>

        {{-- Tanggal Charge & Total --}}
        <div class="p-6 border-b">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="charge_date" class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-calendar-day text-blue-500 mr-1"></i> Tanggal Charge
                    </label>
                    <input type="date" name="charge_date" id="charge_date"
                           value="{{ old('charge_date', date('Y-m-d')) }}"
                           class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
                           required>
                </div>
                <div class="flex flex-col justify-end">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Total</label>
                    <div class="text-2xl font-bold text-blue-700 bg-gray-50 rounded-lg px-4 py-2.5 text-right" id="totalDisplay">
                        Rp {{ number_format(0, 0, ',', '.') }}
                    </div>
                </div>
            </div>
        </div>

        {{-- Metode Pembayaran (opsional) --}}
        <div class="p-6 border-b">
            <label for="payment_method" class="block text-sm font-semibold text-gray-700 mb-2">
                <i class="fas fa-wallet text-blue-500 mr-1"></i> Metode Pembayaran <span class="text-gray-400 font-normal">(opsional)</span>
            </label>
            <select name="payment_method" id="payment_method"
                    class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
                <option value="">-- Belum Dibayar --</option>
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
                <i class="fas fa-save"></i> Simpan Other Revenue
            </button>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
// SCForm already defined in service-charge-form.js, just extend
if (typeof SCForm === 'undefined') {
    window.SCForm = {};
}

document.addEventListener('DOMContentLoaded', function() {
    // Auto-select guest from reservation
    var reservation = document.getElementById('reservation_id');
    if (reservation) {
        reservation.addEventListener('change', function() {
            var selected = this.options[this.selectedIndex];
            var guestId = selected.getAttribute('data-guest');
            if (guestId) {
                document.getElementById('guest_id').value = guestId;
            }
        });
    }

    // Auto-fill guest on load (when pre-selected via reservation_id)
    if (typeof SCForm._autoFillGuest === 'function') {
        SCForm._autoFillGuest();
    }

    // Trigger initial calculation
    if (typeof SCForm._calculate === 'function') {
        SCForm._calculate();
    }
});
</script>
@endsection
