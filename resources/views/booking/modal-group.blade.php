{{-- Booking Group Modal Content — no layout, pure HTML for AJAX modal --}}
<div class="p-6">
    <h2 class="text-2xl font-bold mb-6"><i class="fas fa-users text-green-500 mr-2"></i>Booking Group</h2>

    <div id="availabilityStatus" class="hidden mb-4 p-3 rounded-lg text-sm"></div>

    <form method="POST" action="{{ route('booking.group.store') }}" id="bookingGroupForm" data-ajax="true">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-gray-700 font-bold mb-2">Check-in</label>
                <input type="date" name="check_in" id="checkIn" class="w-full border rounded px-3 py-2" value="{{ old('check_in', date('Y-m-d')) }}" required>
            </div>
            <div>
                <label class="block text-gray-700 font-bold mb-2">Check-out</label>
                <input type="date" name="check_out" id="checkOut" class="w-full border rounded px-3 py-2" value="{{ old('check_out', date('Y-m-d', strtotime('+1 day'))) }}" required>
            </div>
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 font-bold mb-2">Pilih Kamar</label>
            <div id="roomsContainer" class="grid grid-cols-3 md:grid-cols-4 gap-2 max-h-48 overflow-y-auto border rounded p-2">
                <p class="text-gray-500 text-sm col-span-full text-center py-4">Pilih tanggal terlebih dahulu</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
            <div>
                <label class="block text-gray-700 font-bold mb-2">Nama Tamu</label>
                <input type="text" name="guest_name" class="w-full border rounded px-3 py-2" required placeholder="Nama tamu">
            </div>
            <div>
                <label class="block text-gray-700 font-bold mb-2">No. Identitas</label>
                <input type="text" name="id_number" class="w-full border rounded px-3 py-2" placeholder="KTP / SIM">
            </div>
            <div>
                <label class="block text-gray-700 font-bold mb-2">Telepon</label>
                <input type="text" name="phone" class="w-full border rounded px-3 py-2" placeholder="No. HP">
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
            <div>
                <label class="block text-gray-700 font-bold mb-2">Harga per Malam (Rp)</label>
                <input type="number" name="price_per_night" id="groupPrice" class="w-full border rounded px-3 py-2" min="0" step="1000" placeholder="Kosongkan = gunakan harga weekday/weekend">
                <p class="text-xs text-gray-500 mt-1">Kosongkan untuk gunakan harga weekday/weekend otomatis per kamar</p>
            </div>
            <div>
                <label class="block text-gray-700 font-bold mb-2">Metode Pembayaran</label>
                <select name="payment_method" class="w-full border rounded px-3 py-2">
                    <option value="">-- Pilih --</option>
                    @php $pms = \App\Models\PaymentMethod::where('is_active', true)->orderBy('name')->get(); @endphp
                    @foreach($pms as $pm)
                        <option value="{{ $pm->slug }}">{{ $pm->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-gray-700 font-bold mb-2">Tipe Pembayaran</label>
                <div class="flex space-x-4 mt-2">
                    <label class="flex items-center space-x-2 cursor-pointer">
                        <input type="radio" name="payment_type" value="full" checked>
                        <span>Lunas</span>
                    </label>
                    <label class="flex items-center space-x-2 cursor-pointer">
                        <input type="radio" name="payment_type" value="dp">
                        <span>DP</span>
                    </label>
                </div>
            </div>
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 font-bold mb-2">Catatan</label>
            <textarea name="notes" rows="2" class="w-full border rounded px-3 py-2" placeholder="Catatan (opsional)"></textarea>
        </div>

        <div class="flex justify-end space-x-2">
            <button type="button" onclick="Modal.close()" class="bg-gray-400 text-white px-4 py-2 rounded hover:bg-gray-500">Batal</button>
            <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700" id="btnSubmit" disabled>
                <i class="fas fa-save mr-1"></i> Booking Group
            </button>
        </div>
    </form>
</div>

<meta name="booking-check-url" content="{{ route('booking.check-availability') }}">
<script src="{{ asset('js/booking-group.js') }}"></script>
