{{-- Booking OTA Modal Content — pure HTML for AJAX modal --}}
<div class="p-6">
    <h2 class="text-2xl font-bold mb-6"><i class="fas fa-globe text-teal-500 mr-2"></i>Booking OTA</h2>

    <div id="availabilityStatus" class="hidden mb-4 p-3 rounded-lg text-sm"></div>

    <form method="POST" action="{{ route('booking.store') }}" data-ajax="true" data-check-url="{{ route('booking.check-availability') }}">
        @csrf

        <!-- Row 1: Check-in, Check-out, Kamar -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
            <div>
                <label class="block text-gray-700 font-bold mb-2">Check-in</label>
                <input type="date" name="check_in" id="otaCheckIn" class="w-full border rounded px-3 py-2" value="{{ old('check_in', $checkIn ?? '') }}" required>
            </div>
            <div>
                <label class="block text-gray-700 font-bold mb-2">Check-out</label>
                <input type="date" name="check_out" id="otaCheckOut" class="w-full border rounded px-3 py-2" value="{{ old('check_out', $checkOut ?? '') }}" required>
            </div>
            <div>
                <label class="block text-gray-700 font-bold mb-2">Pilih Kamar</label>
                <select name="room_id" id="otaRoomSelect" class="w-full border rounded px-3 py-2" required disabled>
                    <option value="">-- Pilih tanggal dulu --</option>
                </select>
                <p class="text-xs text-gray-500 mt-1" id="otaRoomInfo">Kamar tersedia muncul setelah pilih tanggal</p>
            </div>
        </div>

        <!-- Row 2: Nama Tamu, Identitas, Telepon, Alamat -->
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

        <!-- Row 2b: Tempat Lahir, Tanggal Lahir -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-gray-700 font-bold mb-2">Tempat Lahir</label>
                <input type="text" name="place_of_birth" class="w-full border rounded px-3 py-2" placeholder="Tempat lahir (opsional)">
            </div>
            <div>
                <label class="block text-gray-700 font-bold mb-2">Tanggal Lahir</label>
                <input type="date" name="date_of_birth" class="w-full border rounded px-3 py-2">
            </div>
        </div>

        <!-- Row 3: OTA Reservation Number, OTA Source, Harga -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
            <div>
                <label class="block text-gray-700 font-bold mb-2">
                    <i class="fas fa-tag text-teal-600 mr-1"></i>No. Reservasi OTA
                </label>
                <input type="text" name="ota_reservation_number" id="otaReservationNumber"
                    class="w-full border rounded px-3 py-2" placeholder="Cth: TVL-12345 / TIK-98765">
                <p class="text-xs text-gray-500 mt-1" id="otaDetectedSource">Ketik nomor reservasi untuk auto-detect</p>
            </div>
            <div>
                <label class="block text-gray-700 font-bold mb-2">Sumber OTA</label>
                <select name="ota_source" id="otaSource" class="w-full border rounded px-3 py-2">
                    <option value="">-- Pilih / Auto-detect --</option>
                    @foreach($otaSources as $slug => $name)
                        <option value="{{ $slug }}">{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-gray-700 font-bold mb-2">Harga per Malam (Rp)</label>
                <input type="number" name="price_per_night" id="otaPricePerNight" class="w-full border rounded px-3 py-2" min="0" step="any" placeholder="Auto-fill: harga weekday/weekend">
                <p class="text-xs text-gray-500 mt-1">Kosongkan untuk gunakan harga otomatis</p>
            </div>
            <div>
                <label class="block text-gray-700 font-bold mb-2">Total Tagihan</label>
                <div class="w-full border rounded px-3 py-2 bg-gray-100 font-bold text-blue-700" id="otaTotalTagihan">Rp 0</div>
            </div>
        </div>

        <!-- Row 4: OTA Payment Status, OTA Paid Amount -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-gray-700 font-bold mb-2">Status Pembayaran OTA</label>
                <select name="ota_payment_status" id="otaPaymentStatus" class="w-full border rounded px-3 py-2" onchange="BookingOta.toggleOtaPaidAmount()">
                    <option value="">-- Pilih --</option>
                    <option value="unpaid_ota">Belum Dibayar OTA</option>
                    <option value="paid_ota">Sudah Dibayar OTA (Lunas)</option>
                    <option value="partial_ota">Dibayar Sebagian OTA</option>
                </select>
                <p class="text-xs text-gray-500 mt-1">Status pembayaran dari pihak OTA ke hotel</p>
            </div>
            <div id="otaPaidAmountWrap" class="hidden">
                <label class="block text-gray-700 font-bold mb-2">Jumlah Dibayar OTA (Rp)</label>
                <input type="number" name="ota_paid_amount" id="otaPaidAmount" class="w-full border rounded px-3 py-2" min="0" step="any" placeholder="Nominal yang sudah dibayar OTA">
            </div>
        </div>

        <!-- Catatan -->
        <div class="mb-4">
            <label class="block text-gray-700 font-bold mb-2">Catatan</label>
            <textarea name="notes" rows="2" class="w-full border rounded px-3 py-2" placeholder="Catatan tambahan (opsional)"></textarea>
        </div>

        <div class="flex justify-end space-x-2 mb-4">
            <button type="button" onclick="Modal.close()" class="bg-gray-400 text-white px-4 py-2 rounded hover:bg-gray-500">Tutup</button>
            <button type="submit" class="bg-teal-600 text-white px-6 py-2 rounded hover:bg-teal-700">
                <i class="fas fa-save mr-1"></i> Simpan Booking OTA
            </button>
        </div>
    </form>
</div>

<meta name="booking-check-url" content="{{ route('booking.check-availability') }}">
<script>
    window._preSelectedRoomId = '{{ $selectedRoom ? $selectedRoom->id : "" }}';
    window._preSelectedRoomNumber = '{{ $selectedRoom ? $selectedRoom->room_number : "" }}';

    // Hapus min pada check_in agar tanggal sebelumnya bisa dipilih (Firefox fix)
    (function() {
        var el = document.getElementById('otaCheckIn');
        if (el) el.removeAttribute('min');
        var el2 = document.getElementById('checkIn');
        if (el2) el2.removeAttribute('min');
    })();
</script>
<script src="{{ asset('js/booking-ota.js') }}"></script>
