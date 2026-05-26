@extends('layouts.app')

@section('title', 'Deposit Kartu')
@section('header', 'Deposit Kartu')

@section('content')
<div class="max-w-2xl mx-auto">

    <form method="POST" action="{{ route('deposits.store') }}" class="bg-white rounded-lg shadow" id="depositForm">
        @csrf

        {{-- Info Box --}}
        <div class="bg-blue-50 border border-blue-200 rounded-t-lg p-4 flex items-start gap-3">
            <i class="fas fa-info-circle text-blue-500 mt-0.5"></i>
            <div class="text-sm text-blue-700">
                <p><strong>Deposit Kartu</strong> — Nominal default Rp 100.000 per kartu.</p>
                <p class="text-xs mt-1">Tanda terima akan otomatis dibuat setelah disimpan.</p>
            </div>
        </div>

        {{-- Tamu --}}
        <div class="p-6 border-b">
            <label for="guest_id" class="block text-sm font-semibold text-gray-700 mb-2">
                <i class="fas fa-user text-blue-500 mr-1"></i> Tamu
            </label>
            <select name="guest_id" id="guest_id" required
                    class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
                <option value="">-- Pilih Tamu --</option>
                @foreach($guests as $guest)
                    <option value="{{ $guest->id }}" {{ old('guest_id') == $guest->id ? 'selected' : '' }}>
                        {{ $guest->guest_name }} @if($guest->id_number)({{ $guest->id_number }})@endif
                    </option>
                @endforeach
            </select>
            @error('guest_id')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
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
                            {{ old('reservation_id', $selectedReservation?->id) == $res->id ? 'selected' : '' }}>
                        {{ $res->reservation_number }} — {{ $res->guest->guest_name ?? '-' }} ({{ $res->room->room_number ?? '-' }})
                    </option>
                @endforeach
            </select>
            @error('reservation_id')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- Jumlah Kartu --}}
        <div class="p-6 border-b">
            <label for="number_of_cards" class="block text-sm font-semibold text-gray-700 mb-2">
                <i class="fas fa-credit-card text-blue-500 mr-1"></i> Jumlah Kartu
            </label>
            <input type="number" name="number_of_cards" id="number_of_cards"
                   value="{{ old('number_of_cards', 1) }}" min="1" max="10"
                   class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
                   required>
            @error('number_of_cards')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- Nominal per Kartu --}}
        <div class="p-6 border-b">
            <label for="nominal_per_card" class="block text-sm font-semibold text-gray-700 mb-2">
                <i class="fas fa-money-bill-wave text-blue-500 mr-1"></i> Nominal per Kartu
            </label>
            <div class="relative">
                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 font-semibold">Rp</span>
                <input type="text" id="nominal_display"
                       value="{{ number_format(100000, 0, ',', '.') }}"
                       class="w-full border border-gray-300 rounded-lg pl-12 pr-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
                       readonly>
                <input type="hidden" name="nominal_per_card" id="nominal_per_card" value="100000">
            </div>
            @error('nominal_per_card')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- Total --}}
        <div class="p-6 border-b bg-gray-50">
            <div class="flex justify-between items-center">
                <span class="text-sm font-semibold text-gray-600">Total Deposit:</span>
                <span class="text-2xl font-bold text-blue-700" id="totalDisplay">Rp 100.000</span>
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
            @error('payment_method')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- Catatan --}}
        <div class="p-6 border-b">
            <label for="notes" class="block text-sm font-semibold text-gray-700 mb-2">
                <i class="fas fa-sticky-note text-blue-500 mr-1"></i> Catatan <span class="text-gray-400 font-normal">(opsional)</span>
            </label>
            <textarea name="notes" id="notes" rows="2"
                      class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition resize-none"
                      placeholder="Catatan tambahan...">{{ old('notes') }}</textarea>
            @error('notes')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- Submit --}}
        <div class="p-6 bg-gray-50 rounded-b-lg flex justify-between">
            <a href="{{ route('deposits.index') }}" class="text-gray-500 hover:text-gray-700 font-medium px-4 py-2.5 transition">
                <i class="fas fa-arrow-left mr-1"></i> Kembali
            </a>
            <button type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-2.5 rounded-lg transition flex items-center gap-2">
                <i class="fas fa-save"></i> Simpan Deposit
            </button>
        </div>
    </form>
</div>

<script>
    const nominalPerCard = 100000;
    const cardsInput = document.getElementById('number_of_cards');
    const totalDisplay = document.getElementById('totalDisplay');

    function formatRupiah(num) {
        return 'Rp ' + num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

    function updateTotal() {
        const cards = parseInt(cardsInput.value) || 1;
        const total = cards * nominalPerCard;
        totalDisplay.textContent = formatRupiah(total);
    }

    cardsInput.addEventListener('input', updateTotal);

    // Auto-select guest when reservation is selected
    document.getElementById('reservation_id').addEventListener('change', function() {
        const selected = this.options[this.selectedIndex];
        const guestId = selected.getAttribute('data-guest');
        if (guestId) {
            document.getElementById('guest_id').value = guestId;
        }
    });
</script>
@endsection
