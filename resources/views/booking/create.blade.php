@extends('layouts.app')

@section('title', 'Booking Kamar')

@section('content')
<div class="bg-white rounded-lg shadow p-6 max-w-2xl mx-auto">
    <h2 class="text-2xl font-bold mb-6">Booking Kamar</h2>

    <!-- Status Ketersediaan -->
    <div id="availabilityStatus" class="hidden mb-4 p-3 rounded-lg text-sm"></div>

    <form method="POST" action="{{ route('booking.store') }}">
        @csrf

        <!-- Check-in & Check-out (di atas supaya user pilih tanggal dulu) -->
        <div class="grid grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-gray-700 font-bold mb-2">Check-in</label>
                <input type="date" name="check_in" id="checkIn" class="w-full border rounded px-3 py-2" required>
            </div>
            <div>
                <label class="block text-gray-700 font-bold mb-2">Check-out</label>
                <input type="date" name="check_out" id="checkOut" class="w-full border rounded px-3 py-2" required>
            </div>
        </div>

        <!-- Pilih Kamar (filter otomatis by tanggal) -->
        <div class="mb-4">
            <label class="block text-gray-700 font-bold mb-2">Pilih Kamar</label>
            <select name="room_id" id="roomSelect" class="w-full border rounded px-3 py-2" required disabled>
                <option value="">-- Pilih tanggal check-in & check-out dulu --</option>
            </select>
            <p class="text-xs text-gray-500 mt-1" id="roomInfo">Kamar yang tersedia akan muncul setelah memilih tanggal</p>
        </div>

        <!-- Nama Tamu -->
        <div class="mb-4">
            <label class="block text-gray-700 font-bold mb-2">Nama Tamu</label>
            <input type="text" name="guest_name" class="w-full border rounded px-3 py-2" required placeholder="Masukkan nama tamu">
        </div>

        <!-- Identitas & Telepon -->
        <div class="grid grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-gray-700 font-bold mb-2">No. Identitas</label>
                <input type="text" name="id_number" class="w-full border rounded px-3 py-2" placeholder="KTP / SIM / Passport">
            </div>
            <div>
                <label class="block text-gray-700 font-bold mb-2">Telepon</label>
                <input type="text" name="phone" class="w-full border rounded px-3 py-2" placeholder="No. HP">
            </div>
        </div>

        <!-- Email -->
        <div class="mb-4">
            <label class="block text-gray-700 font-bold mb-2">Email</label>
            <input type="email" name="email" class="w-full border rounded px-3 py-2" placeholder="Email tamu (opsional)">
        </div>

        <!-- Harga per Malam (flexible) -->
        <div class="mb-4">
            <label class="block text-gray-700 font-bold mb-2">Harga per Malam (Rp)</label>
            <input type="number" name="price_per_night" id="pricePerNight" class="w-full border rounded px-3 py-2" min="0" step="1000" placeholder="Masukkan harga per malam">
            <p class="text-xs text-gray-500 mt-1">Otomatis terisi dari harga kamar, bisa diubah sesuai kebutuhan</p>
        </div>

        <!-- Metode Pembayaran -->
        <div class="mb-4">
            <label class="block text-gray-700 font-bold mb-2">Metode Pembayaran</label>
            <select name="payment_method" class="w-full border rounded px-3 py-2">
                <option value="">-- Pilih Metode --</option>
                <option value="cash">Tunai</option>
                <option value="bank_transfer">Transfer Bank</option>
                <option value="credit_card">Kartu Kredit</option>
                <option value="debit_card">Kartu Debit</option>
            </select>
        </div>

        <!-- Catatan -->
        <div class="mb-4">
            <label class="block text-gray-700 font-bold mb-2">Catatan</label>
            <textarea name="notes" rows="2" class="w-full border rounded px-3 py-2" placeholder="Catatan tambahan (opsional)"></textarea>
        </div>

        <div class="flex justify-end space-x-2">
            <a href="{{ route('rooms.dashboard') }}" class="bg-gray-400 text-white px-4 py-2 rounded hover:bg-gray-500">Batal</a>
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Simpan Booking</button>
        </div>
    </form>
</div>

<script>
    const checkInEl = document.getElementById('checkIn');
    const checkOutEl = document.getElementById('checkOut');
    const roomSelect = document.getElementById('roomSelect');
    const roomInfo = document.getElementById('roomInfo');
    const priceInput = document.getElementById('pricePerNight');
    const statusEl = document.getElementById('availabilityStatus');

    // Set minimum date ke hari ini
    const today = new Date().toISOString().split('T')[0];
    checkInEl.min = today;
    checkOutEl.min = today;

    checkInEl.addEventListener('change', function() {
        checkOutEl.min = this.value;
        if (checkOutEl.value && checkOutEl.value <= this.value) {
            checkOutEl.value = '';
        }
        checkAvailability();
    });

    checkOutEl.addEventListener('change', function() {
        if (checkInEl.value && this.value > checkInEl.value) {
            checkAvailability();
        }
    });

    function checkAvailability() {
        const checkIn = checkInEl.value;
        const checkOut = checkOutEl.value;

        if (!checkIn || !checkOut) {
            roomSelect.disabled = true;
            roomSelect.innerHTML = '<option value="">-- Pilih tanggal check-in & check-out dulu --</option>';
            roomInfo.textContent = 'Kamar yang tersedia akan muncul setelah memilih tanggal';
            statusEl.classList.add('hidden');
            return;
        }

        if (checkOut <= checkIn) {
            roomSelect.disabled = true;
            roomSelect.innerHTML = '<option value="">-- Check-out harus setelah check-in --</option>';
            statusEl.classList.add('hidden');
            return;
        }

        roomSelect.disabled = true;
        roomSelect.innerHTML = '<option value="">Mengecek ketersediaan kamar...</option>';
        roomInfo.textContent = 'Sedang mengecek ketersediaan...';
        statusEl.classList.add('hidden');

        fetch('{{ route("booking.check-availability") }}?check_in=' + checkIn + '&check_out=' + checkOut)
            .then(res => res.json())
            .then(data => {
                if (data.rooms && data.rooms.length > 0) {
                    roomSelect.innerHTML = '<option value="">-- Pilih Kamar --</option>';
                    data.rooms.forEach(room => {
                        const opt = document.createElement('option');
                        opt.value = room.id;
                        opt.textContent = room.room_number + ' - ' + (room.room_type_name || 'Standard') + ' (Rp ' + Number(room.price_per_night).toLocaleString('id-ID') + '/malam)';
                        opt.dataset.price = room.price_per_night;
                        roomSelect.appendChild(opt);
                    });
                    roomSelect.disabled = false;
                    roomInfo.textContent = data.rooms.length + ' kamar tersedia untuk ' + fmt(checkIn) + ' - ' + fmt(checkOut);
                    statusEl.className = 'mb-4 p-3 rounded-lg text-sm bg-green-100 border border-green-300 text-green-800';
                    statusEl.innerHTML = '<i class="fas fa-check-circle mr-1"></i> <strong>' + data.rooms.length + ' kamar tersedia</strong>';
                    statusEl.classList.remove('hidden');
                } else {
                    roomSelect.innerHTML = '<option value="">-- Tidak ada kamar tersedia --</option>';
                    roomSelect.disabled = true;
                    roomInfo.textContent = 'Semua kamar sudah dipesan pada tanggal tersebut';
                    statusEl.className = 'mb-4 p-3 rounded-lg text-sm bg-yellow-100 border border-yellow-300 text-yellow-800';
                    statusEl.innerHTML = '<i class="fas fa-exclamation-triangle mr-1"></i> <strong>Tidak ada kamar tersedia</strong>. Pilih tanggal lain.';
                    statusEl.classList.remove('hidden');
                }
            })
            .catch(err => {
                roomSelect.innerHTML = '<option value="">-- Error --</option>';
                roomInfo.textContent = 'Gagal mengecek: ' + err.message;
                statusEl.className = 'mb-4 p-3 rounded-lg text-sm bg-red-100 border border-red-300 text-red-800';
                statusEl.innerHTML = '<i class="fas fa-times-circle mr-1"></i> <strong>Error:</strong> ' + err.message;
                statusEl.classList.remove('hidden');
            });
    }

    roomSelect.addEventListener('change', function() {
        const sel = this.options[this.selectedIndex];
        // Hanya auto-fill jika user belum mengisi harga manual
        if (sel && sel.dataset.price && !priceInput.dataset.edited) {
            priceInput.value = sel.dataset.price;
        }
    });

    // Tandai jika user edit harga manual
    priceInput.addEventListener('input', function() {
        this.dataset.edited = 'true';
    });

    function fmt(d) { return new Date(d).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }); }
</script>
@endsection
