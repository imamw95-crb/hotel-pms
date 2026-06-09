@extends('layouts.app')

@section('title', 'Issue Card')
@section('header', 'Issue Card - MHS')

@section('content')
<div class="max-w-5xl mx-auto">
    <!-- Form Issue Card -->
    <div class="bg-white rounded-lg shadow p-5 mb-4">
        <!-- Header dengan Status MHS -->
        <div class="flex justify-between items-center mb-4 pb-3 border-b">
            <div>
                <h2 class="text-xl font-bold"><i class="fas fa-key text-blue-500 mr-2"></i>Issue Kartu Kamar</h2>
            </div>
            <div class="flex items-center space-x-3">
                <span id="mhsStatus" class="px-3 py-1 rounded text-sm font-bold bg-gray-200 text-gray-600">
                    <i class="fas fa-circle-notch fa-spin"></i> Checking...
                </span>
                <button onclick="testMHSConnection()" class="bg-gray-500 text-white px-3 py-1.5 rounded text-sm hover:bg-gray-600">
                    <i class="fas fa-sync-alt"></i> Test
                </button>
            </div>
        </div>

        <form method="POST" action="{{ route('issue-card.issue') }}" id="issueCardForm">
            @csrf
            <input type="hidden" name="reservation_id" id="reservationId" value="">
            <input type="hidden" name="room_id" id="roomSelect" value="">

            <!-- Baris 1: Pilih Reservasi + Kamar + Nama Tamu -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                <div>
                    <label class="block text-gray-700 font-bold mb-1.5">Cari & Pilih Reservasi</label>
                    <div class="relative">
                        <input type="text" id="searchReservation" placeholder="Ketik nama tamu, no. reservasi, atau no. kamar..."
                            class="w-full border rounded px-3 py-2.5 pl-9 text-sm focus:border-blue-500 outline-none"
                            autocomplete="off"
                            oninput="searchReservationAjax(this.value)"
                            onfocus="if(this.value.trim())showResults()"
                            onblur="setTimeout(hideResults, 250)">
                        <i class="fas fa-search absolute left-3 top-3 text-gray-400 text-sm"></i>
                        <button type="button" onclick="clearSearchAjax()" class="absolute right-2.5 top-2.5 text-gray-400 hover:text-gray-600 hidden text-sm" id="clearSearchBtn">
                            <i class="fas fa-times"></i>
                        </button>
                        <div id="searchSpinner" class="absolute right-2.5 top-2.5 hidden text-sm text-gray-400">
                            <i class="fas fa-spinner fa-spin"></i>
                        </div>
                    </div>
                    <div id="searchResults" class="hidden absolute z-50 bg-white border rounded shadow-lg max-h-48 overflow-y-auto" style="width:100%"></div>
                    <div id="selectedReservationInfo" class="hidden mt-1.5">
                        <div class="flex items-center justify-between bg-green-50 border border-green-200 rounded px-3 py-2">
                            <div class="flex items-center space-x-2 text-sm">
                                <i class="fas fa-check-circle text-green-500"></i>
                                <span id="selectedResNumber" class="font-bold text-green-700"></span>
                                <span id="selectedStatus" class="px-1.5 py-0.5 rounded text-xs font-bold"></span>
                            </div>
                            <button type="button" onclick="clearSearchAjax()" class="text-red-500 hover:text-red-700 text-xs font-bold">Ubah</button>
                        </div>
                    </div>
                </div>
                <div>
                    <label class="block text-gray-700 font-bold mb-1.5">Kamar</label>
                    <input type="text" id="roomDisplay" class="w-full border rounded px-3 py-2.5 text-sm bg-gray-100" readonly placeholder="Otomatis terisi">
                </div>
                <div>
                    <label class="block text-gray-700 font-bold mb-1.5">Nama Tamu</label>
                    <input type="text" name="guest_name" id="guestName" class="w-full border rounded px-3 py-2.5 text-sm" required placeholder="Otomatis terisi">
                </div>
            </div>

            <!-- Hidden fields -->
            <input type="hidden" name="id_number" id="idNumber" value="">
            <input type="hidden" name="phone" id="phone" value="">
            <input type="hidden" name="email" id="email" value="">

            <!-- Baris 2: Check-in, Check-out, Jumlah Kartu -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                <div>
                    <label class="block text-gray-700 font-bold mb-1.5">Check-in</label>
                    <input type="datetime-local" name="check_in" id="checkIn" class="w-full border rounded px-3 py-2.5 text-sm" required>
                </div>
                <div>
                    <label class="block text-gray-700 font-bold mb-1.5">Check-out</label>
                    <input type="datetime-local" name="check_out" id="checkOut" class="w-full border rounded px-3 py-2.5 text-sm" required>
                </div>
                <div>
                    <label class="block text-gray-700 font-bold mb-1.5">Jumlah Kartu</label>
                    <div class="flex space-x-4 pt-1">
                        @for($i = 1; $i <= 2; $i++)
                            <label class="flex items-center space-x-1.5 cursor-pointer text-sm">
                                <input type="radio" name="number_of_cards" value="{{ $i }}" class="rounded" {{ $i == 1 ? 'checked' : '' }}>
                                <span>{{ $i }}</span>
                            </label>
                        @endfor
                    </div>
                </div>
                <div>
                    <label class="block text-gray-700 font-bold mb-1.5">Koneksi MHS</label>
                    <div class="pt-1">
                        <span id="connectionStatus" class="text-sm text-gray-500">Checking...</span>
                    </div>
                </div>
            </div>

            <!-- Preview MHS -->
            <div class="bg-blue-50 border border-blue-200 rounded p-3 mb-4">
                <div class="grid grid-cols-4 gap-3 text-sm">
                    <div><span class="text-gray-500">Room:</span> <span id="previewRoom" class="font-bold ml-1">-</span></div>
                    <div><span class="text-gray-500">Guest:</span> <span id="previewGuest" class="font-bold ml-1">-</span></div>
                    <div><span class="text-gray-500">Check-in:</span> <span id="previewCheckin" class="font-bold ml-1">-</span></div>
                    <div><span class="text-gray-500">Check-out:</span> <span id="previewCheckout" class="font-bold ml-1">-</span></div>
                </div>
            </div>

            <!-- Tombol Aksi -->
            <div class="flex justify-between items-center bg-gray-50 border rounded-lg p-4">
                <button type="button" onclick="readCard()" class="bg-purple-600 text-white px-4 py-2.5 rounded-lg hover:bg-purple-700 font-semibold">
                    <i class="fas fa-id-card mr-1.5"></i> Baca Kartu
                </button>
                <div class="flex space-x-3">
                    <button type="button" id="btnCheckout" onclick="doCheckout()" class="bg-yellow-600 text-white px-4 py-2.5 rounded-lg hover:bg-yellow-700 font-semibold">
                        <i class="fas fa-sign-out-alt mr-1.5"></i> Checkout
                    </button>
                    <button type="button" id="btnEraseCard" onclick="doEraseCard()" class="bg-red-600 text-white px-4 py-2.5 rounded-lg hover:bg-red-700 font-semibold">
                        <i class="fas fa-times-circle mr-1.5"></i> Erase
                    </button>
                    <button type="submit" class="bg-blue-600 text-white px-5 py-2.5 rounded-lg hover:bg-blue-700 font-semibold">
                        <i class="fas fa-key mr-1.5"></i> Issue
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Baris Bawah: Status & Log -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white rounded-lg shadow p-4">
            <h3 class="font-bold mb-3"><i class="fas fa-server text-green-500 mr-2"></i>Status MHS</h3>
            <div class="space-y-3">
                <div class="flex justify-between">
                    <span class="text-gray-500">Total Kamar</span>
                    <button type="button" onclick="showMhsRooms()" class="text-blue-600 hover:text-blue-800 font-medium">
                        <i class="fas fa-list mr-1"></i> Lihat
                    </button>
                </div>
            </div>
        </div>

        <div class="md:col-span-2 bg-white rounded-lg shadow p-4">
            <h3 class="font-bold mb-3"><i class="fas fa-history text-blue-500 mr-2"></i>Log MHS</h3>
            <div class="space-y-2 max-h-48 overflow-y-auto">
                @forelse($recentLogs as $log)
                    <div class="border rounded p-2 text-sm {{ ($log->success ?? false) ? 'border-green-200 bg-green-50' : 'border-red-200 bg-red-50' }}">
                        <div class="flex justify-between items-center">
                            <span class="font-bold">{{ strtoupper($log->command) }}</span>
                            <span class="text-xs text-gray-500">{{ $log->created_at->format('H:i:s') }}</span>
                        </div>
                        <div class="text-gray-600 text-xs mt-1">Room: {{ $log->reservation?->room?->room_number ?? '-' }}</div>
                        <div class="text-gray-600 text-xs">Oleh: {{ $log->creator?->name ?? $log->creator?->username ?? 'System' }}</div>
                        <div class="text-xs {{ ($log->success ?? false) ? 'text-green-600' : 'text-red-600' }}">
                            {{ ($log->success ?? false) ? '✓ Success' : '✗ Failed' }}
                        </div>
                    </div>
                @empty
                    <p class="text-gray-500 text-center py-4">Belum ada log</p>
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

    let searchTimeout;

    function searchReservationAjax(query) {
        clearTimeout(searchTimeout);
        const q = query.trim();

        if (q.length === 0) {
            document.getElementById('searchResults').classList.add('hidden');
            document.getElementById('searchResults').innerHTML = '';
            document.getElementById('clearSearchBtn').classList.add('hidden');
            return;
        }

        document.getElementById('searchSpinner').classList.remove('hidden');
        document.getElementById('clearSearchBtn').classList.remove('hidden');

        searchTimeout = setTimeout(() => {
            fetch('{{ route("issue-card.search-reservations") }}?q=' + encodeURIComponent(q))
                .then(r => r.json())
                .then(data => {
                    document.getElementById('searchSpinner').classList.add('hidden');
                    if (data.success && data.results.length > 0) {
                        let html = '';
                        data.results.forEach(r => {
                            const statusBadge = r.status === 'checked_in' ? 'CHECKED IN' : r.status.toUpperCase();
                            const guest = r.guest_name.replace(/'/g,"\\'");
                            const type = (r.room_type || 'Standard').replace(/'/g,"\\'");
                            html += '<div class="px-3 py-2 border-b border-gray-100 cursor-pointer hover:bg-blue-50 text-sm"';
                            html += ` onmousedown="selectReservationAjax(${r.id},'${r.reservation_number}','${guest}','${r.room_number}','${type}',${r.room_id || 0},'${r.id_number || ''}','${r.phone || ''}','${r.email || ''}','${r.check_in}','${r.check_out}',${r.number_of_cards || 1},'${r.status}')">`;
                            html += '<div class="font-semibold text-blue-600">' + r.reservation_number + ' <span class="text-gray-500 font-normal">- ' + r.guest_name + '</span></div>';
                            html += '<div class="text-xs text-gray-500">Kamar ' + r.room_number + ' (' + r.room_type + ') | ' + statusBadge + '</div>';
                            html += '</div>';
                        });
                        document.getElementById('searchResults').innerHTML = html;
                        document.getElementById('searchResults').classList.remove('hidden');
                    } else {
                        document.getElementById('searchResults').innerHTML = '<div class="px-3 py-3 text-sm text-gray-400 text-center">Reservasi tidak ditemukan</div>';
                        document.getElementById('searchResults').classList.remove('hidden');
                    }
                })
                .catch(() => {
                    document.getElementById('searchSpinner').classList.add('hidden');
                });
        }, 300);
    }

    function showResults() {
        const el = document.getElementById('searchResults');
        if (el.innerHTML.trim()) el.classList.remove('hidden');
    }

    function hideResults() {
        document.getElementById('searchResults').classList.add('hidden');
    }

    function clearSearchAjax() {
        document.getElementById('searchReservation').value = '';
        document.getElementById('searchResults').classList.add('hidden');
        document.getElementById('searchResults').innerHTML = '';
        document.getElementById('clearSearchBtn').classList.add('hidden');
        document.getElementById('selectedReservationInfo').classList.add('hidden');
        document.getElementById('reservationId').value = '';
        document.getElementById('roomSelect').value = '';
        document.getElementById('roomDisplay').value = '';
        document.getElementById('guestName').value = '';
        document.getElementById('idNumber').value = '';
        document.getElementById('phone').value = '';
        document.getElementById('email').value = '';
        document.getElementById('checkIn').value = '';
        document.getElementById('checkOut').value = '';
        updatePreview();
    }

    function selectReservationAjax(id, resNumber, guestName, roomNumber, roomType, roomId, idNumber, phone, email, checkIn, checkOut, cards, status) {
        document.getElementById('reservationId').value = id;
        document.getElementById('roomSelect').value = roomId;
        document.getElementById('roomDisplay').value = roomNumber + ' - ' + (roomType || 'Standard');
        document.getElementById('guestName').value = guestName;
        document.getElementById('idNumber').value = idNumber || '';
        document.getElementById('phone').value = phone || '';
        document.getElementById('email').value = email || '';
        document.getElementById('checkIn').value = checkIn;
        document.getElementById('checkOut').value = checkOut;
        document.querySelectorAll('input[name="number_of_cards"]').forEach(r => { r.checked = (parseInt(r.value) === (parseInt(cards) || 1)); });

        const info = document.getElementById('selectedReservationInfo');
        document.getElementById('selectedResNumber').textContent = resNumber || '#' + id;
        const statusEl = document.getElementById('selectedStatus');
        const labels = { pending: 'PENDING', checked_in: 'CHECKED IN', checked_out: 'CHECKED OUT', cancelled: 'CANCELLED' };
        const colors = { pending: 'bg-yellow-100 text-yellow-800', checked_in: 'bg-green-100 text-green-800', checked_out: 'bg-blue-100 text-blue-800', cancelled: 'bg-red-100 text-red-800' };
        statusEl.textContent = labels[status] || status.toUpperCase();
        statusEl.className = 'px-1.5 py-0.5 rounded text-xs font-bold ' + (colors[status] || 'bg-gray-100 text-gray-800');
        info.classList.remove('hidden');

        document.getElementById('searchResults').classList.add('hidden');
        document.getElementById('searchReservation').value = guestName;

        updatePreview();
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
            confirmBtn.onclick = () => { closeCustomModal(); onConfirm(); };
            cancelBtn.onclick = closeCustomModal;
        } else {
            cancelBtn.classList.add('hidden');
            confirmBtn.textContent = 'OK';
            confirmBtn.className = 'px-6 py-2 rounded text-white font-bold ' + (btnColorMap[type] || btnColorMap.info);
            confirmBtn.onclick = closeCustomModal;
        }

        overlay.classList.remove('hidden');
        overlay.classList.add('flex');
    }

    function closeCustomModal() {
        const overlay = document.getElementById('customModal');
        overlay.classList.add('hidden');
        overlay.classList.remove('flex');
    }

    // Close modal on overlay click
    document.getElementById('customModal').addEventListener('click', function(e) {
        if (e.target === this) closeCustomModal();
    });

    // Close modal on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeCustomModal();
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

    // ========== ERASE CARD ==========
    function doEraseCard() {
        const resId = document.getElementById('reservationId').value;
        const roomDisplay = document.getElementById('roomDisplay').value;
        const guestName = document.getElementById('guestName').value;

        if (!resId) {
            showModal({ title: 'Peringatan', message: 'Pilih reservasi terlebih dahulu!', type: 'warning' });
            return;
        }

        showModal({
            title: 'Konfirmasi Erase Card',
            message: '<strong>Kamar:</strong> ' + roomDisplay + '<br><strong>Tamu:</strong> ' + guestName + '<br><br>Kartu akan di-<strong>erase</strong> dari encoder.<br><br>Silakan tap kartu di reader!<br><br>Lanjutkan?',
            type: 'confirm',
            confirmText: 'Ya, Erase Card',
            cancelText: 'Batal',
            onConfirm: function() {
                const btn = document.getElementById('btnEraseCard');
                btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Processing...';
                btn.disabled = true;

                fetch('/issue-card/' + resId + '/erase-card', {
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
                            title: 'Erase Card Berhasil!',
                            message: '<i class="fas fa-check-circle text-green-500 text-2xl mb-2"></i><br>Kartu kamar <strong>' + roomDisplay + '</strong> berhasil di-erase.',
                            type: 'success',
                            onConfirm: function() { location.reload(); }
                        });
                    } else {
                        showModal({ title: 'Erase Card Gagal', message: data.message || 'Unknown error', type: 'error' });
                        btn.innerHTML = '<i class="fas fa-times-circle mr-1"></i> Erase Card';
                        btn.disabled = false;
                    }
                })
                .catch(err => {
                    showModal({ title: 'Error', message: err.message, type: 'error' });
                    btn.innerHTML = '<i class="fas fa-times-circle mr-1"></i> Erase Card';
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
                closeCustomModal();
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
                closeCustomModal();
                showModal({ title: 'Error', message: 'Gagal membaca kartu: ' + e.message, type: 'error' });
            });
    }

    // ========== MHS ROOMS ==========
    function showMhsRooms() {
        fetch('{{ route("issue-card.mhs-rooms") }}')
            .then(r => r.json())
            .then(data => {
                if (data.success && data.rooms) {
                    let html = '<div class="text-left" style="min-width:500px">';
                    html += '<div class="flex justify-between items-center mb-3 px-1"><span class="text-gray-500 text-sm">Total <strong>' + data.total_rooms + '</strong> kamar</span></div>';
                    if (data.by_floor) {
                        Object.keys(data.by_floor).sort().forEach(floor => {
                            html += '<div class="mb-4">';
                            html += '<div class="flex items-center mb-1"><span class="bg-blue-100 text-blue-700 px-2 py-0.5 rounded text-xs font-bold">Lantai ' + floor + '</span></div>';
                            html += '<table class="w-full text-sm border-collapse rounded overflow-hidden" style="border:1px solid #e5e7eb">';
                            html += '<thead><tr style="background:#f3f4f6;border-bottom:2px solid #d1d5db">';
                            html += '<th style="padding:6px 10px;text-align:left;font-weight:600;color:#374151;font-size:12px">Kamar</th>';
                            html += '<th style="padding:6px 10px;text-align:left;font-weight:600;color:#374151;font-size:12px">Tipe</th>';
                            html += '<th style="padding:6px 10px;text-align:left;font-weight:600;color:#374151;font-size:12px">Status</th>';
                            html += '<th style="padding:6px 10px;text-align:left;font-weight:600;color:#374151;font-size:12px">Tamu</th>';
                            html += '<th style="padding:6px 10px;text-align:left;font-weight:600;color:#374151;font-size:12px">Check In/Out</th>';
                            html += '</tr></thead><tbody>';
                            data.by_floor[floor].forEach(r => {
                                const isOccupied = r.status === 'occupied';
                                const isMaintenance = r.status === 'maintenance';
                                const bgRow = isOccupied ? 'style="background:#fef2f2"' : isMaintenance ? 'style="background:#fffbeb"' : '';
                                let badge, badgeStyle;
                                if (isOccupied) { badge = 'Terisi'; badgeStyle = 'background:#fee2e2;color:#991b1b;padding:2px 8px;border-radius:999px;font-size:11px;font-weight:600'; }
                                else if (isMaintenance) { badge = 'Maintenance'; badgeStyle = 'background:#fef3c7;color:#92400e;padding:2px 8px;border-radius:999px;font-size:11px;font-weight:600'; }
                                else if (r.status === 'cleaning') { badge = 'Cleaning'; badgeStyle = 'background:#dbeafe;color:#1e40af;padding:2px 8px;border-radius:999px;font-size:11px;font-weight:600'; }
                                else { badge = 'Available'; badgeStyle = 'background:#dcfce7;color:#166534;padding:2px 8px;border-radius:999px;font-size:11px;font-weight:600'; }
                                const guestName = r.guest_name || '-';
                                const dates = r.check_in ? r.check_in + ' - ' + r.check_out : '-';
                                html += '<tr ' + bgRow + ' style="border-bottom:1px solid #e5e7eb">';
                                html += '<td style="padding:6px 10px;font-weight:600;font-size:13px">' + r.room_number + '</td>';
                                html += '<td style="padding:6px 10px;color:#6b7280;font-size:12px">' + (r.room_type || '-') + '</td>';
                                html += '<td style="padding:6px 10px"><span style="' + badgeStyle + '">' + badge + '</span></td>';
                                html += '<td style="padding:6px 10px;color:#374151;font-size:12px">' + guestName + '</td>';
                                html += '<td style="padding:6px 10px;color:#6b7280;font-size:11px">' + dates + '</td>';
                                html += '</tr>';
                            });
                            html += '</tbody></table></div>';
                        });
                    }
                    html += '</div>';

                    // Tampilkan dalam overlay khusus yang lebih lebar
                    showRoomsModal(html);
                } else {
                    showModal({ title: 'Gagal', message: data.message || 'Tidak dapat mengambil daftar kamar.', type: 'error' });
                }
            })
            .catch(err => {
                showModal({ title: 'Error', message: err.message, type: 'error' });
            });
    }

    function showRoomsModal(contentHtml) {
        const overlay = document.getElementById('roomsModal');
        if (!overlay) {
            // Buat elemen modal rooms jika belum ada
            const div = document.createElement('div');
            div.id = 'roomsModal';
            div.className = 'fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50';
            div.innerHTML = '<div class="bg-white rounded-2xl shadow-2xl mx-4 overflow-hidden transform transition-all" style="max-width:750px;width:100%;max-height:90vh">' +
                '<div class="px-6 py-4 border-b bg-gray-50 flex justify-between items-center">' +
                '<h3 class="text-lg font-bold text-gray-800">Daftar Kamar</h3>' +
                '<button onclick="document.getElementById(\'roomsModal\').classList.add(\'hidden\');document.getElementById(\'roomsModal\').classList.remove(\'flex\')" class="text-gray-400 hover:text-gray-600 text-xl leading-none">&times;</button>' +
                '</div>' +
                '<div id="roomsModalBody" class="px-6 py-4 overflow-y-auto" style="max-height:calc(90vh - 80px)"></div>' +
                '</div>';
            document.body.appendChild(div);
            div.addEventListener('click', function(e) { if (e.target === this) { this.classList.add('hidden'); this.classList.remove('flex'); } });
        }
        document.getElementById('roomsModalBody').innerHTML = contentHtml;
        const modal = document.getElementById('roomsModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    // ========== REGISTER ENCODER ==========
    function doRegisterEncoder() {
        showModal({
            title: 'Konfirmasi Register Encoder',
            message: 'Daftarkan encoder ke sistem MHS?<br><br>IP Encoder: <strong>192.168.88.2</strong><br>ID Encoder: <strong>01</strong><br><br>Cukup dilakukan <strong>sekali saja</strong> atau saat error "Client end not connected".',
            type: 'confirm',
            confirmText: 'Ya, Register',
            cancelText: 'Batal',
            onConfirm: function() {
                fetch('{{ route("issue-card.register-encoder") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || document.querySelector('input[name="_token"]')?.value
                    },
                    body: JSON.stringify({ ip: '192.168.88.2', encoder_id: '01' })
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        showModal({ title: 'Berhasil!', message: 'Encoder berhasil didaftarkan ke MHS.', type: 'success' });
                    } else {
                        showModal({ title: 'Gagal', message: data.message || 'Gagal mendaftarkan encoder.', type: 'error' });
                    }
                })
                .catch(err => {
                    showModal({ title: 'Error', message: err.message, type: 'error' });
                });
            }
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
