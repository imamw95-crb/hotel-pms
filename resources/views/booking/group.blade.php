@extends('layouts.app')

@section('title', 'Booking Group')

@section('content')
<div class="bg-white rounded-lg shadow p-6 max-w-3xl mx-auto">
    <h2 class="text-2xl font-bold mb-6">Booking Group (Multiple Kamar)</h2>

    <!-- Status Ketersediaan -->
    <div id="availabilityStatus" class="hidden mb-4 p-3 rounded-lg text-sm"></div>

    <form method="POST" action="{{ route('booking.group.store') }}">
        @csrf

        <!-- Check-in & Check-out -->
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
            <label class="block text-gray-700 font-bold mb-2">Pilih Kamar (bisa lebih dari satu)</label>
            <div id="roomsContainer" class="grid grid-cols-2 md:grid-cols-4 gap-2 border rounded p-3 max-h-60 overflow-y-auto bg-gray-50">
                <p class="col-span-full text-gray-500 text-center py-4">Pilih tanggal check-in & check-out dulu untuk melihat kamar tersedia</p>
            </div>
            <p class="text-xs text-gray-500 mt-1" id="roomInfo">Kamar yang tersedia akan muncul setelah memilih tanggal</p>
        </div>

        <!-- Nama Tamu -->
        <div class="mb-4">
            <label class="block text-gray-700 font-bold mb-2">Nama Tamu (Pemesan)</label>
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

        <!-- Harga per Malam -->
        <div class="mb-4">
            <label class="block text-gray-700 font-bold mb-2">Harga per Malam (Rp) - Semua Kamar</label>
            <input type="number" name="price_per_night" id="pricePerNight" class="w-full border rounded px-3 py-2" min="0" step="1000">
            <p class="text-xs text-gray-500 mt-1">Bisa diisi manual atau kosongkan untuk harga default masing-masing kamar</p>
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
            <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Booking Group</button>
        </div>
    </form>
</div>

<script>
    const checkInEl = document.getElementById('checkIn');
    const checkOutEl = document.getElementById('checkOut');
    const roomsContainer = document.getElementById('roomsContainer');
    const roomInfo = document.getElementById('roomInfo');
    const statusEl = document.getElementById('availabilityStatus');

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
            roomsContainer.innerHTML = '<p class="col-span-full text-gray-500 text-center py-4">Pilih tanggal check-in & check-out dulu</p>';
            roomInfo.textContent = 'Kamar yang tersedia akan muncul setelah memilih tanggal';
            statusEl.classList.add('hidden');
            return;
        }

        if (checkOut <= checkIn) {
            roomsContainer.innerHTML = '<p class="col-span-full text-gray-500 text-center py-4">Check-out harus setelah check-in</p>';
            statusEl.classList.add('hidden');
            return;
        }

        roomsContainer.innerHTML = '<p class="col-span-full text-gray-500 text-center py-4"><i class="fas fa-spinner fa-spin mr-1"></i> Mengecek ketersediaan kamar...</p>';
        roomInfo.textContent = 'Sedang mengecek ketersediaan...';
        statusEl.classList.add('hidden');

        fetch('{{ route("booking.check-availability") }}?check_in=' + checkIn + '&check_out=' + checkOut)
            .then(res => res.json())
            .then(data => {
                if (data.rooms && data.rooms.length > 0) {
                    roomsContainer.innerHTML = '';
                    data.rooms.forEach(room => {
                        const label = document.createElement('label');
                        label.className = 'flex items-center space-x-2 p-2 rounded hover:bg-blue-50 cursor-pointer transition';
                        label.innerHTML = '<input type="checkbox" name="room_ids[]" value="' + room.id + '" class="rounded border-gray-300">' +
                            '<span class="text-sm"><strong>' + room.room_number + '</strong> - ' + (room.room_type_name || 'Standard') + '<br><span class="text-xs text-gray-500">Rp ' + Number(room.price_per_night).toLocaleString('id-ID') + '/malam</span></span>';
                        roomsContainer.appendChild(label);
                    });
                    roomInfo.textContent = data.rooms.length + ' kamar tersedia untuk ' + fmt(checkIn) + ' - ' + fmt(checkOut);
                    statusEl.className = 'mb-4 p-3 rounded-lg text-sm bg-green-100 border border-green-300 text-green-800';
                    statusEl.innerHTML = '<i class="fas fa-check-circle mr-1"></i> <strong>' + data.rooms.length + ' kamar tersedia</strong> — centang kamar yang ingin di-booking';
                    statusEl.classList.remove('hidden');
                } else {
                    roomsContainer.innerHTML = '<p class="col-span-full text-yellow-600 text-center py-4"><i class="fas fa-exclamation-triangle mr-1"></i> Tidak ada kamar tersedia untuk tanggal ini</p>';
                    roomInfo.textContent = 'Semua kamar sudah dipesan pada tanggal tersebut';
                    statusEl.className = 'mb-4 p-3 rounded-lg text-sm bg-yellow-100 border border-yellow-300 text-yellow-800';
                    statusEl.innerHTML = '<i class="fas fa-exclamation-triangle mr-1"></i> <strong>Tidak ada kamar tersedia</strong>. Pilih tanggal lain.';
                    statusEl.classList.remove('hidden');
                }
            })
            .catch(err => {
                roomsContainer.innerHTML = '<p class="col-span-full text-red-500 text-center py-4">Gagal mengecek: ' + err.message + '</p>';
                statusEl.className = 'mb-4 p-3 rounded-lg text-sm bg-red-100 border border-red-300 text-red-800';
                statusEl.innerHTML = '<i class="fas fa-times-circle mr-1"></i> <strong>Error:</strong> ' + err.message;
                statusEl.classList.remove('hidden');
            });
    }

    function fmt(d) { return new Date(d).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }); }
</script>
@endsection
