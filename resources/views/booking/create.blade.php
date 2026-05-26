@extends('layouts.app')

@section('title', 'Booking Kamar')

@section('content')
<div class="bg-white rounded-lg shadow p-6 max-w-5xl mx-auto">
    <h2 class="text-2xl font-bold mb-6"><i class="fas fa-calendar-plus text-blue-500 mr-2"></i>Booking Kamar</h2>

    <!-- Status Ketersediaan -->
    <div id="availabilityStatus" class="hidden mb-4 p-3 rounded-lg text-sm"></div>

    <form method="POST" action="{{ route('booking.store') }}">
        @csrf

        <!-- Row 1: Check-in, Check-out, Kamar -->
        <div class="grid grid-cols-3 gap-4 mb-4">
            <div>
                <label class="block text-gray-700 font-bold mb-2">Check-in</label>
                <input type="date" name="check_in" id="checkIn" class="w-full border rounded px-3 py-2" required>
            </div>
            <div>
                <label class="block text-gray-700 font-bold mb-2">Check-out</label>
                <input type="date" name="check_out" id="checkOut" class="w-full border rounded px-3 py-2" required>
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

        <!-- Row 3: Email, No. Reservasi OTA, Harga, Metode Bayar -->
        <div class="grid grid-cols-4 gap-4 mb-4">
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
                <input type="number" name="price_per_night" id="pricePerNight" class="w-full border rounded px-3 py-2" min="0" step="1000" placeholder="Auto-fill dari kamar">
                <p class="text-xs text-gray-500 mt-1">Bisa diubah manual</p>
            </div>
            <div>
                <label class="block text-gray-700 font-bold mb-2">Metode Pembayaran</label>
                <select name="payment_method" id="paymentMethod" class="w-full border rounded px-3 py-2" onchange="toggleDpFields()">
                    <option value="">-- Pilih Metode --</option>
                    @php $paymentMethods = \App\Models\PaymentMethod::where('is_active', true)->orderBy('name')->get(); @endphp
                    @foreach($paymentMethods as $pm)
                        <option value="{{ $pm->slug }}">{{ $pm->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- Row 4: Tipe Pembayaran & Nominal DP -->
        <div class="grid grid-cols-3 gap-4 mb-4">
            <div>
                <label class="block text-gray-700 font-bold mb-2">Tipe Pembayaran</label>
                <div class="flex space-x-4 mt-2">
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
            <div>
                <label class="block text-gray-700 font-bold mb-2">Total Tagihan</label>
                <div class="w-full border rounded px-3 py-2 bg-gray-100 font-bold text-blue-700" id="totalTagihan">Rp 0</div>
            </div>
            <div id="dpAmountSection" class="hidden">
                <label class="block text-gray-700 font-bold mb-2">Nominal DP (Rp) <span class="text-red-500">*</span></label>
                <input type="number" name="dp_amount" id="dpAmount" class="w-full border rounded px-3 py-2" min="0" step="1000" placeholder="Masukkan nominal DP">
                <p class="text-xs text-gray-500 mt-1">Sisa bayar: <span id="sisaBayar" class="font-semibold text-orange-600">Rp 0</span></p>
            </div>
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
    const today = new window.Date().toISOString().split('T')[0];
    checkInEl.min = today;
    checkOutEl.min = today;

    checkInEl.addEventListener('change', function() {
        checkOutEl.min = this.value;
        if (checkOutEl.value && checkOutEl.value <= this.value) {
            checkOutEl.value = '';
        }
        checkAvailability();
        calculateTotal();
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
                    calculateTotal();
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
        if (sel && sel.dataset.price && !priceInput.dataset.edited) {
            priceInput.value = sel.dataset.price;
        }
        calculateTotal();
    });

    priceInput.addEventListener('input', function() {
        this.dataset.edited = 'true';
        calculateTotal();
    });

    function calculateTotal() {
        const checkIn = checkInEl.value;
        const checkOut = checkOutEl.value;
        const price = parseInt(priceInput.value) || 0;
        const totalEl = document.getElementById('totalTagihan');

        if (checkIn && checkOut && price > 0) {
            const d1 = new window.Date(checkIn);
            const d2 = new window.Date(checkOut);
            const days = Math.max(1, Math.ceil((d2 - d1) / (1000 * 60 * 60 * 24)));
            const total = price * days;
            totalEl.textContent = 'Rp ' + total.toLocaleString('id-ID');
            totalEl.dataset.total = total;
        } else {
            totalEl.textContent = 'Rp 0';
            totalEl.dataset.total = 0;
        }
        updateSisaBayar();
    }

    function toggleDpFields() {
        const isDp = document.querySelector('input[name="payment_type"]:checked').value === 'dp';
        const dpSection = document.getElementById('dpAmountSection');
        const dpInput = document.getElementById('dpAmount');
        if (isDp) {
            dpSection.classList.remove('hidden');
            dpInput.setAttribute('required', 'required');
        } else {
            dpSection.classList.add('hidden');
            dpInput.removeAttribute('required');
            dpInput.value = '';
        }
        updateSisaBayar();
    }

    function updateSisaBayar() {
        const total = parseInt(document.getElementById('totalTagihan').dataset.total) || 0;
        const dpAmount = parseInt(document.getElementById('dpAmount').value) || 0;
        const sisa = Math.max(0, total - dpAmount);
        document.getElementById('sisaBayar').textContent = 'Rp ' + sisa.toLocaleString('id-ID');
    }

    document.getElementById('dpAmount').addEventListener('input', updateSisaBayar);

    function fmt(d) { return new window.Date(d).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }); }
</script>
@endsection
