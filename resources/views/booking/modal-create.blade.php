{{-- Booking Create Modal Content — no layout, pure HTML for AJAX modal --}}
<div class="p-6">
    <h2 class="text-2xl font-bold mb-6"><i class="fas fa-calendar-plus text-blue-500 mr-2"></i>Booking Kamar</h2>

    <div id="availabilityStatus" class="hidden mb-4 p-3 rounded-lg text-sm"></div>

    <form method="POST" action="{{ route('booking.store') }}" data-ajax="true">
        @csrf

        <!-- Row 1: Check-in, Check-out, Kamar -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
            <div>
                <label class="block text-gray-700 font-bold mb-2">Check-in</label>
                <input type="date" name="check_in" id="checkIn" class="w-full border rounded px-3 py-2" value="{{ old('check_in', $checkIn ?? '') }}" required>
            </div>
            <div>
                <label class="block text-gray-700 font-bold mb-2">Check-out</label>
                <input type="date" name="check_out" id="checkOut" class="w-full border rounded px-3 py-2" value="{{ old('check_out', $checkOut ?? '') }}" required>
            </div>
            <div>
                <label class="block text-gray-700 font-bold mb-2">Pilih Kamar</label>
                <select name="room_id" id="roomSelect" class="w-full border rounded px-3 py-2" required disabled>
                    <option value="">-- Pilih tanggal dulu --</option>
                </select>
                <p class="text-xs text-gray-500 mt-1" id="roomInfo">Kamar tersedia muncul setelah pilih tanggal</p>
            </div>
        </div>

        <!-- Row 2: Nama Tamu, Identitas, Telepon -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
            <div>
                <label class="block text-gray-700 font-bold mb-2">Nama Tamu</label>
                <input type="text" name="guest_name" class="w-full border rounded px-3 py-2" required placeholder="Nama tamu">
            </div>
            <div>
                <label class="block text-gray-700 font-bold mb-2">No. Identitas</label>
                <input type="text" name="id_number" class="w-full border rounded px-3 py-2" placeholder="KTP / SIM / Passport">
            </div>
            <div>
                <label class="block text-gray-700 font-bold mb-2">Telepon</label>
                <input type="text" name="phone" class="w-full border rounded px-3 py-2" placeholder="No. HP">
            </div>
            <div>
                <label class="block text-gray-700 font-bold mb-2">Alamat</label>
                <input type="text" name="address" class="w-full border rounded px-3 py-2" placeholder="Alamat (opsional)">
            </div>
        </div>

        <!-- Row 3: Email, No. Reservasi OTA, Harga, Metode Bayar -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
            <div>
                <label class="block text-gray-700 font-bold mb-2">Email</label>
                <input type="email" name="email" class="w-full border rounded px-3 py-2" placeholder="Email (opsional)">
            </div>
            <div>
                <label class="block text-gray-700 font-bold mb-2">No. Reservasi OTA</label>
                <input type="text" name="ota_reservation_number" class="w-full border rounded px-3 py-2" placeholder="Opsional (cth: BKNG-12345)">
            </div>
            <div>
                <label class="block text-gray-700 font-bold mb-2">Harga per Malam (Rp)</label>
                <input type="number" name="price_per_night" id="pricePerNight" class="w-full border rounded px-3 py-2" min="0" step="1000" placeholder="Auto-fill: harga weekday/weekend">
                <p class="text-xs text-gray-500 mt-1">Kosongkan untuk gunakan harga weekday/weekend otomatis. Isi manual untuk harga tetap.</p>
            </div>
            <div>
                <label class="block text-gray-700 font-bold mb-2">Total Tagihan</label>
                <div class="w-full border rounded px-3 py-2 bg-gray-100 font-bold text-blue-700" id="totalTagihan">Rp 0</div>
            </div>
        </div>

        <!-- Sarapan -->
        <div class="mb-4">
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="include_breakfast" value="1" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                <span class="text-gray-700 font-medium"><i class="fas fa-coffee text-amber-600 mr-1"></i> Termasuk Sarapan</span>
            </label>
            <p class="text-xs text-gray-500 mt-1 ml-6">Centang jika tamu mendapatkan sarapan</p>
        </div>

        <!-- Catatan -->
        <div class="mb-4">
            <label class="block text-gray-700 font-bold mb-2">Catatan</label>
            <textarea name="notes" rows="2" class="w-full border rounded px-3 py-2" placeholder="Catatan tambahan (opsional)"></textarea>
        </div>

        <div class="flex justify-end space-x-2 mb-4">
            <button type="button" onclick="Modal.close()" class="bg-gray-400 text-white px-4 py-2 rounded hover:bg-gray-500">Tutup</button>
            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                <i class="fas fa-save mr-1"></i> Simpan Booking
            </button>
        </div>
    </form>
</div>

{{-- Pass data to external JS via meta tag and window variables --}}
<meta name="booking-check-url" content="{{ route('booking.check-availability') }}">
<script>
    window._preSelectedRoomId = '{{ $selectedRoom ? $selectedRoom->id : "" }}';
    window._preSelectedRoomNumber = '{{ $selectedRoom ? $selectedRoom->room_number : "" }}';

</script>
<script src="{{ asset('js/booking-modal.js') }}"></script>
