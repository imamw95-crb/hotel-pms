@extends('layouts.app')

@section('title', 'Booking Group')

@section('content')
<div class="bg-white rounded-lg shadow p-6 max-w-4xl mx-auto">
    <h2 class="text-2xl font-bold mb-6">Booking Group (Multiple Kamar)</h2>

    <!-- Status Ketersediaan -->
    <div id="availabilityStatus" class="hidden mb-4 p-3 rounded-lg text-sm"></div>

    <form method="POST" action="{{ route('booking.group.store') }}" id="bookingGroupForm">
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
            <div id="roomsContainer" class="grid grid-cols-2 md:grid-cols-3 gap-2 border rounded p-3 max-h-60 overflow-y-auto bg-gray-50">
                <p class="col-span-full text-gray-500 text-center py-4">Pilih tanggal check-in & check-out dulu untuk melihat kamar tersedia</p>
            </div>
            <p class="text-xs text-gray-500 mt-1" id="roomInfo">Kamar yang tersedia akan muncul setelah memilih tanggal</p>
        </div>

        <!-- Selected Rooms with Individual Prices -->
        <div id="selectedRoomsSection" class="hidden mb-6">
            <div class="flex justify-between items-center mb-3">
                <label class="block text-gray-700 font-bold">Kamar Terpilih & Harga per Malam</label>
                <div class="flex items-center space-x-2">
                    <label class="text-sm text-gray-600">Harga Semua Kamar:</label>
                    <input type="number" id="bulkPrice" placeholder="Rp" class="w-32 border rounded px-2 py-1 text-sm" min="0" step="1000">
                    <button type="button" onclick="applyBulkPrice()" class="bg-blue-500 text-white px-3 py-1 rounded text-sm hover:bg-blue-600">Apply</button>
                </div>
            </div>
            <div class="border rounded overflow-hidden">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-100 border-b">
                            <th class="text-left p-2 font-bold">Kamar</th>
                            <th class="text-left p-2 font-bold">Tipe</th>
                            <th class="text-center p-2 font-bold">Harga Default</th>
                            <th class="text-center p-2 font-bold">Harga per Malam (Rp)</th>
                            <th class="text-center p-2 font-bold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="selectedRoomsTable">
                    </tbody>
                    <tfoot>
                        <tr class="bg-green-50 border-t-2 border-green-300">
                            <td colspan="4" class="p-2 font-bold text-green-800 text-right">Total per Malam:</td>
                            <td class="p-2 text-center font-bold text-green-700" id="totalPerNight">Rp 0</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Row: Nama Tamu, Identitas, Telepon, Alamat -->
        <div class="grid grid-cols-4 gap-4 mb-4">
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

        <!-- Row: Email -->
        <div class="grid grid-cols-4 gap-4 mb-4">
            <div class="col-span-2">
                <label class="block text-gray-700 font-bold mb-2">Email</label>
                <input type="email" name="email" class="w-full border rounded px-3 py-2" placeholder="Email tamu (opsional)">
            </div>
        </div>

        <!-- Metode Pembayaran -->
        <div class="mb-4">
            <label class="block text-gray-700 font-bold mb-2">Metode Pembayaran</label>
            <select name="payment_method" id="paymentMethod" class="w-full border rounded px-3 py-2" onchange="toggleDpFields()">
                <option value="">-- Pilih Metode --</option>
                <option value="cash">Tunai</option>
                <option value="bank_transfer">Transfer Bank</option>
                <option value="credit_card">Kartu Kredit</option>
                <option value="debit_card">Kartu Debit</option>
            </select>
        </div>

        <!-- Tipe Pembayaran (Full / DP) -->
        <div class="mb-4">
            <label class="block text-gray-700 font-bold mb-2">Tipe Pembayaran</label>
            <div class="flex space-x-4">
                <label class="flex items-center space-x-2 cursor-pointer">
                    <input type="radio" name="payment_type" value="full" checked onchange="toggleDpFields()">
                    <span>Lunas (Bayar Penuh)</span>
                </label>
                <label class="flex items-center space-x-2 cursor-pointer">
                    <input type="radio" name="payment_type" value="dp" onchange="toggleDpFields()">
                    <span>DP (Down Payment)</span>
                </label>
            </div>
        </div>

        <!-- DP Amount (hidden by default) -->
        <div class="mb-4 hidden" id="dpAmountSection">
            <label class="block text-gray-700 font-bold mb-2">Nominal DP (Rp)</label>
            <input type="number" name="dp_amount" id="dpAmount" class="w-full border rounded px-3 py-2" min="0" step="1000" placeholder="Masukkan nominal DP">
            <p class="text-xs text-gray-500 mt-1">Total semua kamar: <span id="totalSemuaKamar">Rp 0</span> | Sisa bayar: <span id="sisaBayarGroup">Rp 0</span></p>
        </div>

        <!-- Catatan -->
        <div class="mb-4">
            <label class="block text-gray-700 font-bold mb-2">Catatan</label>
            <textarea name="notes" rows="2" class="w-full border rounded px-3 py-2" placeholder="Catatan tambahan (opsional)"></textarea>
        </div>

        <div class="flex justify-end space-x-2">
            <a href="{{ route('rooms.dashboard') }}" class="bg-gray-400 text-white px-4 py-2 rounded hover:bg-gray-500">Batal</a>
            <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700" id="btnSubmit" disabled>Booking Group</button>
        </div>
    </form>
</div>

<script>
    const checkInEl = document.getElementById('checkIn');
    const checkOutEl = document.getElementById('checkOut');
    const roomsContainer = document.getElementById('roomsContainer');
    const roomInfo = document.getElementById('roomInfo');
    const statusEl = document.getElementById('availabilityStatus');
    const selectedRoomsSection = document.getElementById('selectedRoomsSection');
    const selectedRoomsTable = document.getElementById('selectedRoomsTable');
    const totalPerNightEl = document.getElementById('totalPerNight');
    const btnSubmit = document.getElementById('btnSubmit');
    const bulkPriceInput = document.getElementById('bulkPrice');

    let selectedRooms = {};
    let availableRoomsData = [];

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
                availableRoomsData = data.rooms || [];
                if (availableRoomsData.length > 0) {
                    roomsContainer.innerHTML = '';
                    availableRoomsData.forEach(room => {
                        const isChecked = selectedRooms[room.id] ? 'checked' : '';
                        const label = document.createElement('label');
                        label.className = 'flex items-center space-x-2 p-2 rounded hover:bg-blue-50 cursor-pointer transition border ' + (selectedRooms[room.id] ? 'bg-blue-50 border-blue-300' : 'border-transparent');
                        label.innerHTML =
                            '<input type="checkbox" name="room_ids[]" value="' + room.id + '" class="rounded border-gray-300 room-checkbox" ' + isChecked + ' onchange="toggleRoom(' + room.id + ', this.checked)">' +
                            '<span class="text-sm"><strong>' + room.room_number + '</strong> - ' + (room.room_type_name || 'Standard') + '</span>' +
                            '<span class="text-xs text-gray-500 ml-auto">Rp ' + Number(room.price_per_night).toLocaleString('id-ID') + '</span>';
                        roomsContainer.appendChild(label);
                    });
                    roomInfo.textContent = availableRoomsData.length + ' kamar tersedia — centang kamar yang ingin di-booking';
                    statusEl.className = 'mb-4 p-3 rounded-lg text-sm bg-green-100 border border-green-300 text-green-800';
                    statusEl.innerHTML = '<i class="fas fa-check-circle mr-1"></i> <strong>' + availableRoomsData.length + ' kamar tersedia</strong>';
                    statusEl.classList.remove('hidden');
                } else {
                    roomsContainer.innerHTML = '<p class="col-span-full text-yellow-600 text-center py-4"><i class="fas fa-exclamation-triangle mr-1"></i> Tidak ada kamar tersedia untuk tanggal ini</p>';
                    roomInfo.textContent = 'Semua kamar sudah dipesan pada tanggal tersebut';
                    statusEl.className = 'mb-4 p-3 rounded-lg text-sm bg-yellow-100 border border-yellow-300 text-yellow-800';
                    statusEl.innerHTML = '<i class="fas fa-exclamation-triangle mr-1"></i> <strong>Tidak ada kamar tersedia</strong>';
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

    function toggleRoom(roomId, isChecked) {
        const room = availableRoomsData.find(r => r.id === roomId);
        if (!room) return;

        if (isChecked) {
            selectedRooms[roomId] = {
                id: room.id,
                room_number: room.room_number,
                room_type_name: room.room_type_name || 'Standard',
                default_price: room.price_per_night,
                price: room.price_per_night
            };
        } else {
            delete selectedRooms[roomId];
        }

        renderSelectedRooms();
    }

    function renderSelectedRooms() {
        const rooms = Object.values(selectedRooms);
        if (rooms.length === 0) {
            selectedRoomsSection.classList.add('hidden');
            btnSubmit.disabled = true;
            return;
        }

        selectedRoomsSection.classList.remove('hidden');
        btnSubmit.disabled = false;
        selectedRoomsTable.innerHTML = '';

        let total = 0;
        rooms.forEach(room => {
            total += parseInt(room.price) || 0;
            const tr = document.createElement('tr');
            tr.className = 'border-b border-gray-100';
            tr.innerHTML =
                '<td class="p-2 font-bold">' + room.room_number + '</td>' +
                '<td class="p-2 text-gray-600">' + room.room_type_name + '</td>' +
                '<td class="p-2 text-center text-gray-400">Rp ' + Number(room.default_price).toLocaleString('id-ID') + '</td>' +
                '<td class="p-2 text-center">' +
                    '<input type="number" name="room_prices[' + room.id + ']" value="' + room.price + '" min="0" step="1000" class="w-28 border rounded px-2 py-1 text-center text-sm room-price-input" data-room-id="' + room.id + '" onchange="updateRoomPrice(' + room.id + ', this.value)">' +
                '</td>' +
                '<td class="p-2 text-center">' +
                    '<button type="button" onclick="removeRoom(' + room.id + ')" class="text-red-500 hover:text-red-700 text-sm"><i class="fas fa-trash"></i></button>' +
                '</td>';
            selectedRoomsTable.appendChild(tr);
        });

        totalPerNightEl.textContent = 'Rp ' + total.toLocaleString('id-ID');

        // Update total semua kamar (total amount = price * days)
        const checkIn = checkInEl.value;
        const checkOut = checkOutEl.value;
        if (checkIn && checkOut) {
            const days = Math.ceil((new Date(checkOut) - new Date(checkIn)) / (1000 * 60 * 60 * 24));
            const totalAll = total * days;
            document.getElementById('totalSemuaKamar').textContent = 'Rp ' + totalAll.toLocaleString('id-ID');
            updateSisaBayar();
        }
    }

    function updateRoomPrice(roomId, price) {
        if (selectedRooms[roomId]) {
            selectedRooms[roomId].price = parseInt(price) || 0;
            renderSelectedRooms();
        }
    }

    function removeRoom(roomId) {
        delete selectedRooms[roomId];
        // Uncheck the checkbox
        const checkbox = document.querySelector('.room-checkbox[value="' + roomId + '"]');
        if (checkbox) {
            checkbox.checked = false;
            checkbox.closest('label').classList.remove('bg-blue-50', 'border-blue-300');
            checkbox.closest('label').classList.add('border-transparent');
        }
        renderSelectedRooms();
    }

    function applyBulkPrice() {
        const price = parseInt(bulkPriceInput.value) || 0;
        Object.keys(selectedRooms).forEach(roomId => {
            selectedRooms[roomId].price = price;
        });
        renderSelectedRooms();
    }

    function toggleDpFields() {
        const dpSection = document.getElementById('dpAmountSection');
        const dpRadio = document.querySelector('input[name="payment_type"][value="dp"]');
        if (dpRadio && dpRadio.checked) {
            dpSection.classList.remove('hidden');
        } else {
            dpSection.classList.add('hidden');
        }
    }

    function updateSisaBayar() {
        const dpAmount = parseInt(document.getElementById('dpAmount').value) || 0;
        const totalText = document.getElementById('totalSemuaKamar').textContent;
        const total = parseInt(totalText.replace(/[^\d]/g, '')) || 0;
        const sisa = total - dpAmount;
        document.getElementById('sisaBayarGroup').textContent = 'Rp ' + (sisa > 0 ? sisa.toLocaleString('id-ID') : '0');
    }

    // Listen for DP amount changes
    document.getElementById('dpAmount')?.addEventListener('input', updateSisaBayar);
</script>
@endsection
