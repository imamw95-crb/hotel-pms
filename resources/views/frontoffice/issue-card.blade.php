@extends('layouts.app')

@section('title', 'Issue Card')
@section('header', 'Issue Card - MHS')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Form Issue Card -->
    <div class="lg:col-span-2 space-y-6">
        <!-- Pilih Reservasi -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-bold mb-4"><i class="fas fa-clipboard-list text-blue-500 mr-2"></i>Pilih Reservasi</h2>

            <!-- Pencarian Reservasi -->
            <div class="mb-4">
                <div class="relative">
                    <input type="text" id="searchReservation" placeholder="Cari no. reservasi, nama tamu, no. kamar..."
                        class="w-full border rounded px-3 py-2 pl-10" onkeyup="filterReservations()">
                    <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                </div>
            </div>

            <!-- Filter Status -->
            <div class="flex space-x-2 mb-4">
                <button onclick="filterByStatus('all')" class="filter-btn px-3 py-1 rounded text-sm bg-blue-600 text-white" data-status="all">Semua</button>
                <button onclick="filterByStatus('pending')" class="filter-btn px-3 py-1 rounded text-sm bg-gray-200 text-gray-700 hover:bg-gray-300" data-status="pending">Pending</button>
                <button onclick="filterByStatus('checked_in')" class="filter-btn px-3 py-1 rounded text-sm bg-gray-200 text-gray-700 hover:bg-gray-300" data-status="checked_in">Checked In</button>
            </div>

            <!-- Daftar Reservasi -->
            <div class="border rounded max-h-64 overflow-y-auto" id="reservationList">
                @forelse($reservations as $res)
                    <div class="reservation-item p-3 border-b cursor-pointer hover:bg-blue-50 transition {{ $res->status === 'pending' ? '' : 'opacity-60' }}"
                        data-id="{{ $res->id }}"
                        data-status="{{ $res->status }}"
                        data-room-id="{{ $res->room_id }}"
                        data-room-number="{{ $res->room->room_number ?? '' }}"
                        data-guest-name="{{ $res->guest->guest_name ?? '' }}"
                        data-id-number="{{ $res->guest->id_number ?? '' }}"
                        data-phone="{{ $res->guest->phone ?? '' }}"
                        data-email="{{ $res->guest->email ?? '' }}"
                        data-check-in="{{ $res->check_in->format('Y-m-d\TH:i') }}"
                        data-check-out="{{ $res->check_out->format('Y-m-d\TH:i') }}"
                        data-cards="{{ $res->number_of_cards ?? 1 }}"
                        onclick="selectReservation(this)">
                        <div class="flex justify-between items-start">
                            <div>
                                <span class="font-bold text-blue-600">{{ $res->reservation_number }}</span>
                                <span class="ml-2 px-2 py-0.5 rounded text-xs font-bold
                                    @if($res->status === 'pending') bg-yellow-100 text-yellow-800
                                    @elseif($res->status === 'checked_in') bg-green-100 text-green-800
                                    @elseif($res->status === 'checked_out') bg-blue-100 text-blue-800
                                    @else bg-gray-100 text-gray-800 @endif">
                                    {{ strtoupper($res->status) }}
                                </span>
                            </div>
                            <span class="text-xs text-gray-500">{{ $res->created_at->format('d/m/Y H:i') }}</span>
                        </div>
                        <div class="mt-1 text-sm">
                            <i class="fas fa-user text-gray-400 mr-1"></i> {{ $res->guest->guest_name ?? '-' }}
                            <span class="mx-2">|</span>
                            <i class="fas fa-bed text-gray-400 mr-1"></i> {{ $res->room->room_number ?? '-' }}
                        </div>
                        <div class="mt-1 text-xs text-gray-500">
                            <i class="fas fa-calendar mr-1"></i> {{ $res->check_in->format('d/m/Y H:i') }} - {{ $res->check_out->format('d/m/Y H:i') }}
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-gray-500">
                        <i class="fas fa-inbox text-4xl mb-2"></i>
                        <p>Tidak ada data reservasi</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Form Issue Card -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-bold"><i class="fas fa-key text-blue-500 mr-2"></i>Issue Kartu Kamar</h2>
                <div class="flex items-center space-x-2">
                    <span class="text-sm text-gray-500">Status MHS:</span>
                    <span id="mhsStatus" class="px-2 py-1 rounded text-xs font-bold bg-gray-200 text-gray-600">
                        <i class="fas fa-circle-notch fa-spin"></i> Checking...
                    </span>
                    <button onclick="testMHSConnection()" class="bg-gray-500 text-white px-3 py-1 rounded text-sm hover:bg-gray-600">
                        <i class="fas fa-sync-alt"></i> Test
                    </button>
                </div>
            </div>

            <form method="POST" action="{{ route('issue-card.issue') }}" id="issueCardForm">
                @csrf
                <input type="hidden" name="reservation_id" id="reservationId" value="">

                <!-- Info Reservasi Terpilih -->
                <div id="selectedInfo" class="hidden bg-green-50 border border-green-200 rounded p-4 mb-4">
                    <div class="flex justify-between items-center">
                        <div>
                            <span class="text-sm text-green-600 font-bold"><i class="fas fa-check-circle mr-1"></i>Reservasi Terpilih:</span>
                            <span id="selectedResNumber" class="font-bold text-green-800 ml-2"></span>
                            <span id="selectedStatus" class="ml-2 px-2 py-0.5 rounded text-xs font-bold"></span>
                        </div>
                        <button type="button" onclick="clearSelection()" class="text-red-500 hover:text-red-700 text-sm">
                            <i class="fas fa-times"></i> Clear
                        </button>
                    </div>
                </div>

                <!-- Pilih Kamar (auto-fill dari reservasi) -->
                <div class="mb-4">
                    <label class="block text-gray-700 font-bold mb-2">Kamar</label>
                    <input type="text" id="roomDisplay" class="w-full border rounded px-3 py-2 bg-gray-100" readonly placeholder="Otomatis terisi dari reservasi">
                    <input type="hidden" name="room_id" id="roomSelect" value="">
                </div>

                <!-- Nama Tamu -->
                <div class="mb-4">
                    <label class="block text-gray-700 font-bold mb-2">Nama Tamu</label>
                    <input type="text" name="guest_name" id="guestName" class="w-full border rounded px-3 py-2" required placeholder="Otomatis terisi dari reservasi">
                </div>

                <!-- Hidden fields: Identitas, Telepon, Email (auto-fill dari reservasi) -->
                <input type="hidden" name="id_number" id="idNumber" value="">
                <input type="hidden" name="phone" id="phone" value="">
                <input type="hidden" name="email" id="email" value="">

                <!-- Check-in & Check-out -->
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-gray-700 font-bold mb-2">Check-in</label>
                        <input type="datetime-local" name="check_in" id="checkIn" class="w-full border rounded px-3 py-2" required>
                    </div>
                    <div>
                        <label class="block text-gray-700 font-bold mb-2">Check-out</label>
                        <input type="datetime-local" name="check_out" id="checkOut" class="w-full border rounded px-3 py-2" required>
                    </div>
                </div>

                <!-- Jumlah Kartu -->
                <div class="mb-4">
                    <label class="block text-gray-700 font-bold mb-2">Jumlah Kartu</label>
                    <div class="flex space-x-3">
                        @for($i = 1; $i <= 5; $i++)
                            <label class="flex items-center space-x-1 cursor-pointer">
                                <input type="radio" name="number_of_cards" value="{{ $i }}" class="rounded" {{ $i == 1 ? 'checked' : '' }}>
                                <span>{{ $i }}</span>
                            </label>
                        @endfor
                    </div>
                </div>

                <!-- Preview MHS -->
                <div class="bg-blue-50 border border-blue-200 rounded p-4 mb-4">
                    <h4 class="font-bold text-blue-700 mb-2"><i class="fas fa-info-circle mr-1"></i>Preview Perintah MHS</h4>
                    <div class="grid grid-cols-2 gap-2 text-sm">
                        <div><span class="text-gray-500">Room:</span> <span id="previewRoom" class="font-bold">-</span></div>
                        <div><span class="text-gray-500">Guest:</span> <span id="previewGuest" class="font-bold">-</span></div>
                        <div><span class="text-gray-500">Check-in:</span> <span id="previewCheckin" class="font-bold">-</span></div>
                        <div><span class="text-gray-500">Check-out:</span> <span id="previewCheckout" class="font-bold">-</span></div>
                    </div>
                </div>

                <!-- Tombol Aksi -->
                <div class="flex justify-between items-center">
                    <div>
                        <button type="button" onclick="readCard()" class="bg-purple-600 text-white px-4 py-2 rounded hover:bg-purple-700">
                            <i class="fas fa-id-card mr-1"></i> Baca Kartu
                        </button>
                    </div>
                    <div class="flex space-x-2">
                        <!-- Tombol Checkout (hanya tampil untuk checked_in) -->
                        <button type="button" id="btnCheckout" onclick="doCheckout()" class="hidden bg-yellow-600 text-white px-4 py-2 rounded hover:bg-yellow-700">
                            <i class="fas fa-sign-out-alt mr-1"></i> Checkout & Available
                        </button>
                        <!-- Tombol Issue Card -->
                        <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                            <i class="fas fa-key mr-1"></i> Issue Card
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Panel Status & Log -->
    <div class="space-y-6">
        <div class="bg-white rounded-lg shadow p-4">
            <h3 class="font-bold text-lg mb-3"><i class="fas fa-server text-green-500 mr-2"></i>Status MHS</h3>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-500">Server</span>
                    <span class="font-medium text-xs">{{ env('MHS_BRIDGE_URL', 'http://127.0.0.1:8080/bridge_api.php') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Koneksi</span>
                    <span id="connectionStatus" class="font-medium text-gray-500">Checking...</span>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-4">
            <h3 class="font-bold text-lg mb-3"><i class="fas fa-history text-blue-500 mr-2"></i>Log MHS Terbaru</h3>
            <div class="space-y-2 max-h-96 overflow-y-auto">
                @forelse($recentLogs as $log)
                    <div class="border rounded p-2 text-sm {{ ($log->success ?? false) ? 'border-green-200 bg-green-50' : 'border-red-200 bg-red-50' }}">
                        <div class="flex justify-between items-center">
                            <span class="font-bold">{{ strtoupper($log->command) }}</span>
                            <span class="text-xs text-gray-500">{{ $log->created_at->format('H:i:s') }}</span>
                        </div>
                        <div class="text-gray-600 text-xs mt-1">Room: {{ $log->reservation?->room?->room_number ?? '-' }}</div>
                        <div class="text-xs {{ ($log->success ?? false) ? 'text-green-600' : 'text-red-600' }}">
                            {{ ($log->success ?? false) ? '✓ Success' : '✗ Failed' }}
                        </div>
                    </div>
                @empty
                    <p class="text-gray-500 text-center py-4 text-sm">Belum ada log</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

<script>
    // Panggil testMHSConnection langsung (Turbo tidak trigger DOMContentLoaded lagi)
    if (document.readyState === 'complete' || document.readyState === 'interactive') {
        testMHSConnection();
    } else {
        document.addEventListener('DOMContentLoaded', function() { testMHSConnection(); });
    }

    var selectedReservationStatus = '';

    function selectReservation(el) {
        const d = el.dataset;
        selectedReservationStatus = d.status;
        document.querySelectorAll('.reservation-item').forEach(i => i.classList.remove('bg-blue-100'));
        el.classList.add('bg-blue-100');
        document.getElementById('reservationId').value = d.id;
        document.getElementById('roomSelect').value = d.roomId;
        document.getElementById('roomDisplay').value = (d.roomNumber || '') + ' - ' + (d.roomType || 'Standard');
        document.getElementById('guestName').value = d.guestName;
        document.getElementById('idNumber').value = d.idNumber;
        document.getElementById('phone').value = d.phone;
        document.getElementById('email').value = d.email;
        document.getElementById('checkIn').value = d.checkIn;
        document.getElementById('checkOut').value = d.checkOut;
        const cards = parseInt(d.cards) || 1;
        document.querySelectorAll('input[name="number_of_cards"]').forEach(r => { r.checked = (parseInt(r.value) === cards); });
        document.getElementById('selectedInfo').classList.remove('hidden');
        document.getElementById('selectedResNumber').textContent = el.querySelector('.font-bold.text-blue-600').textContent;

        // Tampilkan status badge
        const statusEl = document.getElementById('selectedStatus');
        const statusLabels = { pending: 'PENDING', checked_in: 'CHECKED IN', checked_out: 'CHECKED OUT', cancelled: 'CANCELLED' };
        const statusColors = { pending: 'bg-yellow-100 text-yellow-800', checked_in: 'bg-green-100 text-green-800', checked_out: 'bg-blue-100 text-blue-800', cancelled: 'bg-red-100 text-red-800' };
        statusEl.textContent = statusLabels[d.status] || d.status.toUpperCase();
        statusEl.className = 'ml-2 px-2 py-0.5 rounded text-xs font-bold ' + (statusColors[d.status] || 'bg-gray-100 text-gray-800');

        // Tampilkan tombol Checkout hanya untuk checked_in
        if (d.status === 'checked_in') {
            document.getElementById('btnCheckout').classList.remove('hidden');
        } else {
            document.getElementById('btnCheckout').classList.add('hidden');
        }

        updatePreview();
    }

    function clearSelection() {
        document.getElementById('reservationId').value = '';
        document.getElementById('selectedInfo').classList.add('hidden');
        document.querySelectorAll('.reservation-item').forEach(i => i.classList.remove('bg-blue-100'));
    }

    function filterByStatus(status) {
        document.querySelectorAll('.filter-btn').forEach(b => {
            b.classList.remove('bg-blue-600', 'text-white');
            b.classList.add('bg-gray-200', 'text-gray-700');
        });
        document.querySelector(`[data-status="${status}"]`).classList.remove('bg-gray-200', 'text-gray-700');
        document.querySelector(`[data-status="${status}"]`).classList.add('bg-blue-600', 'text-white');
        document.querySelectorAll('.reservation-item').forEach(i => {
            i.style.display = (status === 'all' || i.dataset.status === status) ? 'block' : 'none';
        });
    }

    function filterReservations() {
        const q = document.getElementById('searchReservation').value.toLowerCase();
        document.querySelectorAll('.reservation-item').forEach(i => {
            i.style.display = i.textContent.toLowerCase().includes(q) ? 'block' : 'none';
        });
    }

    document.getElementById('guestName').addEventListener('input', updatePreview);
    document.getElementById('checkIn').addEventListener('change', updatePreview);
    document.getElementById('checkOut').addEventListener('change', updatePreview);

    function updatePreview() {
        document.getElementById('previewRoom').textContent = document.getElementById('roomDisplay').value || '-';
        document.getElementById('previewGuest').textContent = document.getElementById('guestName').value || '-';
        const ci = document.getElementById('checkIn').value;
        const co = document.getElementById('checkOut').value;
        document.getElementById('previewCheckin').textContent = ci ? fmt(dt(ci)) : '-';
        document.getElementById('previewCheckout').textContent = co ? fmt(dt(co)) : '-';
    }

    function dt(s) { return new Date(s); }
    function fmt(d) { return d.toLocaleString('id-ID', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' }); }

    function testMHSConnection() {
        const s = document.getElementById('mhsStatus'), c = document.getElementById('connectionStatus');
        s.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> Checking...';
        s.className = 'px-2 py-1 rounded text-xs font-bold bg-gray-200 text-gray-600';
        c.textContent = 'Checking...'; c.className = 'font-medium text-gray-500';
        fetch('{{ route("issue-card.test") }}')
            .then(r => r.json())
            .then(d => {
                if (d.connected) {
                    s.innerHTML = '<i class="fas fa-check-circle"></i> Online'; s.className = 'px-2 py-1 rounded text-xs font-bold bg-green-100 text-green-700';
                    c.textContent = 'Connected'; c.className = 'font-medium text-green-600';
                } else {
                    s.innerHTML = '<i class="fas fa-times-circle"></i> Offline'; s.className = 'px-2 py-1 rounded text-xs font-bold bg-red-100 text-red-700';
                    c.textContent = 'Disconnected'; c.className = 'font-medium text-red-600';
                }
            })
            .catch(e => {
                s.innerHTML = '<i class="fas fa-times-circle"></i> Error'; s.className = 'px-2 py-1 rounded text-xs font-bold bg-red-100 text-red-700';
                c.textContent = 'Error: ' + e.message; c.className = 'font-medium text-red-600';
            });
    }

    // ========== CUSTOM MODAL ==========
    function showModal({ title, message, type = 'info', onConfirm = null, confirmText = 'Ya', cancelText = 'Batal' }) {
        const overlay = document.getElementById('customModal');
        const iconMap = {
            info: '<i class="fas fa-info-circle text-blue-500 text-4xl"></i>',
            success: '<i class="fas fa-check-circle text-green-500 text-4xl"></i>',
            warning: '<i class="fas fa-exclamation-triangle text-yellow-500 text-4xl"></i>',
            error: '<i class="fas fa-times-circle text-red-500 text-4xl"></i>',
            confirm: '<i class="fas fa-question-circle text-yellow-500 text-4xl"></i>'
        };
        const btnColorMap = {
            info: 'bg-blue-600 hover:bg-blue-700',
            success: 'bg-green-600 hover:bg-green-700',
            warning: 'bg-yellow-600 hover:bg-yellow-700',
            error: 'bg-red-600 hover:bg-red-700',
            confirm: 'bg-blue-600 hover:bg-blue-700'
        };

        document.getElementById('modalIcon').innerHTML = iconMap[type] || iconMap.info;
        document.getElementById('modalTitle').textContent = title;
        document.getElementById('modalMessage').innerHTML = message.replace(/\n/g, '<br>');

        const confirmBtn = document.getElementById('modalConfirm');
        const cancelBtn = document.getElementById('modalCancel');

        if (onConfirm) {
            cancelBtn.classList.remove('hidden');
            cancelBtn.textContent = cancelText;
            confirmBtn.textContent = confirmText;
            confirmBtn.className = 'px-6 py-2 rounded text-white font-bold ' + (btnColorMap[type] || btnColorMap.info);
            confirmBtn.onclick = () => { closeModal(); onConfirm(); };
            cancelBtn.onclick = closeModal;
        } else {
            cancelBtn.classList.add('hidden');
            confirmBtn.textContent = 'OK';
            confirmBtn.className = 'px-6 py-2 rounded text-white font-bold ' + (btnColorMap[type] || btnColorMap.info);
            confirmBtn.onclick = closeModal;
        }

        overlay.classList.remove('hidden');
        overlay.classList.add('flex');
    }

    function closeModal() {
        const overlay = document.getElementById('customModal');
        overlay.classList.add('hidden');
        overlay.classList.remove('flex');
    }

    // Close modal on overlay click
    document.getElementById('customModal').addEventListener('click', function(e) {
        if (e.target === this) closeModal();
    });

    // Close modal on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeModal();
    });

    // ========== CHECKOUT ==========
    function doCheckout() {
        const resId = document.getElementById('reservationId').value;
        const roomDisplay = document.getElementById('roomDisplay').value;
        const guestName = document.getElementById('guestName').value;

        if (!resId) {
            showModal({ title: 'Peringatan', message: 'Pilih reservasi terlebih dahulu!', type: 'warning' });
            return;
        }

        showModal({
            title: 'Konfirmasi Checkout',
            message: '<strong>Kamar:</strong> ' + roomDisplay + '<br><strong>Tamu:</strong> ' + guestName + '<br><br>Kamar akan otomatis menjadi <strong>Available</strong> setelah checkout.<br><br>Lanjutkan?',
            type: 'confirm',
            confirmText: 'Ya, Checkout',
            cancelText: 'Batal',
            onConfirm: function() {
                const btn = document.getElementById('btnCheckout');
                btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Processing...';
                btn.disabled = true;

                fetch('/issue-card/' + resId + '/checkout', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || document.querySelector('input[name="_token"]')?.value
                    }
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        showModal({
                            title: 'Checkout Berhasil!',
                            message: '<i class="fas fa-check-circle text-green-500 text-2xl mb-2"></i><br>Kamar ' + roomDisplay + ' sekarang statusnya <strong>Available</strong>.',
                            type: 'success',
                            onConfirm: function() { location.reload(); }
                        });
                    } else {
                        showModal({ title: 'Checkout Gagal', message: data.message || 'Unknown error', type: 'error' });
                        btn.innerHTML = '<i class="fas fa-sign-out-alt mr-1"></i> Checkout & Available';
                        btn.disabled = false;
                    }
                })
                .catch(err => {
                    showModal({ title: 'Error', message: err.message, type: 'error' });
                    btn.innerHTML = '<i class="fas fa-sign-out-alt mr-1"></i> Checkout & Available';
                    btn.disabled = false;
                });
            }
        });
    }

    // ========== READ CARD ==========
    function readCard() {
        showModal({ title: 'Membaca Kartu...', message: '<i class="fas fa-spinner fa-spin text-blue-500 text-2xl"></i><br><br>Silakan tap kartu di reader.', type: 'info', confirmText: 'Tutup' });

        fetch('{{ route("issue-card.read") }}')
            .then(r => r.json())
            .then(d => {
                closeModal();
                if (d.success && d.card_data) {
                    const c = d.card_data;
                    showModal({
                        title: 'Data Kartu',
                        message: '<div class="text-left bg-gray-100 rounded p-3"><p><strong>Room:</strong> ' + (c.room || '-') + '</p><p><strong>Nama:</strong> ' + (c.name || '-') + '</p><p><strong>Check-in:</strong> ' + (c.checkin || '-') + '</p><p><strong>Check-out:</strong> ' + (c.checkout || '-') + '</p></div>',
                        type: 'info'
                    });
                } else {
                    showModal({ title: 'Tidak Ada Data', message: 'Tidak ada data kartu atau gagal membaca kartu.', type: 'warning' });
                }
            })
            .catch(e => {
                closeModal();
                showModal({ title: 'Error', message: 'Gagal membaca kartu: ' + e.message, type: 'error' });
            });
    }

    // ========== ISSUE CARD CONFIRM ==========
    document.getElementById('issueCardForm').addEventListener('submit', function(e) {
        const rn = document.getElementById('roomDisplay').value || '-';
        const guest = document.getElementById('guestName').value;
        e.preventDefault();
        showModal({
            title: 'Konfirmasi Issue Card',
            message: '<strong>Kamar:</strong> ' + rn + '<br><strong>Tamu:</strong> ' + guest + '<br><br>Card akan di-issue ke MHS.<br>Lanjutkan?',
            type: 'confirm',
            confirmText: 'Ya, Issue Card',
            cancelText: 'Batal',
            onConfirm: function() { document.getElementById('issueCardForm').submit(); }
        });
    });
</script>

<!-- Custom Modal -->
<div id="customModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full mx-4 overflow-hidden transform transition-all">
        <!-- Header -->
        <div class="px-6 py-4 border-b bg-gray-50">
            <h3 id="modalTitle" class="text-lg font-bold text-gray-800"></h3>
        </div>
        <!-- Body -->
        <div class="px-6 py-6 text-center">
            <div id="modalIcon" class="mb-4"></div>
            <div id="modalMessage" class="text-gray-600 text-sm leading-relaxed"></div>
        </div>
        <!-- Footer -->
        <div class="px-6 py-4 border-t bg-gray-50 flex justify-end space-x-2">
            <button id="modalCancel" class="px-4 py-2 rounded bg-gray-300 text-gray-700 font-bold hover:bg-gray-400 transition"></button>
            <button id="modalConfirm" class="px-6 py-2 rounded text-white font-bold transition"></button>
        </div>
    </div>
</div>
@endsection
