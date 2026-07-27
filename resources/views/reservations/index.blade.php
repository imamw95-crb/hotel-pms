@extends('layouts.app')

@section('title', 'Reservasi')
@section('header')
    <div class="flex items-center gap-2 w-full flex-wrap">
        <span class="whitespace-nowrap">Data Reservasi</span>
        <div class="flex items-center gap-1.5 ml-auto">
            <button type="button" onclick="Modal.open('{{ route('booking.create') }}')"
                class="bg-blue-600 text-white px-2.5 py-1.5 rounded-lg text-xs font-medium hover:bg-blue-700 transition flex items-center gap-1 whitespace-nowrap">
                <i class="fas fa-plus"></i> <span class="hidden sm:inline">Booking</span> Single
            </button>
            <button type="button" onclick="Modal.open('{{ route('booking.group.create') }}')"
                class="bg-indigo-600 text-white px-2.5 py-1.5 rounded-lg text-xs font-medium hover:bg-indigo-700 transition flex items-center gap-1 whitespace-nowrap">
                <i class="fas fa-users"></i> <span class="hidden sm:inline">Booking</span> Group
            </button>
            <button type="button" onclick="AiChat.toggle()"
                class="bg-gradient-to-r from-blue-500 to-indigo-600 text-white px-2.5 py-1.5 rounded-lg text-xs font-medium hover:from-blue-600 hover:to-indigo-700 transition flex items-center gap-1 shadow-sm whitespace-nowrap"
                title="AI Assistant">
                <i class="fas fa-robot"></i> <span class="hidden sm:inline">AI</span>
            </button>
        </div>
    </div>
@endsection

@section('content')

<!-- Statistik Ringkasan -->
<div id="stats-container" class="grid grid-cols-2 lg:grid-cols-5 gap-3 mb-6">
    @include('reservations.partials._stats')
</div>

<!-- Filter & Pencarian -->
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
    <form method="GET" action="{{ route('reservations.index') }}" data-turbo="false">
        <!-- Baris 1: Pencarian -->
        <div class="mb-3">
            <label class="block text-[10px] font-semibold text-gray-500 uppercase tracking-wide mb-1">
                Pencarian
            </label>
            <div class="relative">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                <input type="text" name="search" value="{{ $search ?? '' }}"
                    placeholder="Cari no. reservasi, nama tamu, no. kamar..."
                    class="w-full border border-gray-200 rounded-lg pl-8 pr-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
        </div>

        <!-- Baris 2: Filter + Tombol -->
        <div class="flex flex-col sm:flex-row sm:items-end gap-3">
            <!-- Filter Status -->
            <div class="w-full sm:w-36">
                <label class="block text-[10px] font-semibold text-gray-500 uppercase tracking-wide mb-1">Status</label>
                <select name="status" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">Semua Status</option>
                    <option value="pending" {{ ($status ?? '') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="checked_in" {{ ($status ?? '') === 'checked_in' ? 'selected' : '' }}>Checked In</option>
                    <option value="checked_out" {{ ($status ?? '') === 'checked_out' ? 'selected' : '' }}>Checked Out</option>
                    <option value="cancelled" {{ ($status ?? '') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>

            <!-- Filter Sumber -->
            <div class="w-full sm:w-24">
                <label class="block text-[10px] font-semibold text-gray-500 uppercase tracking-wide mb-1">Sumber</label>
                <select name="sumber" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">Semua</option>
                    <option value="website" {{ ($sumber ?? '') === 'website' ? 'selected' : '' }}>🌐 Website</option>
                    <option value="ota" {{ ($sumber ?? '') === 'ota' ? 'selected' : '' }}>🔗 OTA</option>
                    <option value="local" {{ ($sumber ?? '') === 'local' ? 'selected' : '' }}>🏨 Local</option>
                </select>
            </div>

            <!-- Tanggal Dari -->
            <div class="w-full sm:w-36">
                <label class="block text-[10px] font-semibold text-gray-500 uppercase tracking-wide mb-1">Dari</label>
                <input type="date" name="date_from" id="date_from" value="{{ $dateFrom ?? '' }}"
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>

            <!-- Tanggal Sampai -->
            <div class="w-full sm:w-36">
                <label class="block text-[10px] font-semibold text-gray-500 uppercase tracking-wide mb-1">Sampai</label>
                <input type="date" name="date_to" id="date_to" value="{{ $dateTo ?? '' }}"
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>

            <!-- Quick Date: Hari Ini -->
            <div class="flex-shrink-0 pt-5">
                <button type="button" onclick="setTodayRange()"
                    class="bg-teal-500 text-white px-3 py-2 rounded-lg text-xs font-medium hover:bg-teal-600 transition flex items-center gap-1.5 whitespace-nowrap">
                    <i class="fas fa-calendar-day"></i> Hari Ini
                </button>
            </div>

            <script>
            function setTodayRange() {
                var today = new Date();
                var tomorrow = new Date(today);
                tomorrow.setDate(tomorrow.getDate() + 1);

                var fmt = function(d) {
                    var y = d.getFullYear();
                    var m = String(d.getMonth() + 1).padStart(2, '0');
                    var day = String(d.getDate()).padStart(2, '0');
                    return y + '-' + m + '-' + day;
                };

                document.getElementById('date_from').value = fmt(today);
                document.getElementById('date_to').value = fmt(tomorrow);
            }
            </script>

            <!-- Tombol -->
            <div class="flex gap-2 flex-shrink-0">
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-700 transition flex items-center gap-1.5 whitespace-nowrap">
                    <i class="fas fa-search"></i> Cari
                </button>
                <a href="{{ route('reservations.index') }}" class="bg-gray-100 text-gray-600 px-4 py-2 rounded-lg text-sm font-medium hover:bg-gray-200 transition flex items-center gap-1.5 whitespace-nowrap">
                    <i class="fas fa-redo"></i> Reset
                </a>
            </div>
        </div>
    </form>
</div>

<!-- Tabel Reservasi -->
<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-200">
                    <th class="text-left px-4 py-3 text-[10px] font-semibold text-gray-500 uppercase tracking-wider">Reservasi</th>
                    <th class="text-left px-4 py-3 text-[10px] font-semibold text-gray-500 uppercase tracking-wider">Tamu</th>
                    <th class="text-left px-4 py-3 text-[10px] font-semibold text-gray-500 uppercase tracking-wider">Kamar</th>
                    <th class="text-left px-4 py-3 text-[10px] font-semibold text-gray-500 uppercase tracking-wider">Check-in</th>
                    <th class="text-left px-4 py-3 text-[10px] font-semibold text-gray-500 uppercase tracking-wider">Check-out</th>
                    <th class="text-center px-4 py-3 text-[10px] font-semibold text-gray-500 uppercase tracking-wider">Sarapan</th>
                    <th class="text-right px-4 py-3 text-[10px] font-semibold text-gray-500 uppercase tracking-wider">Total</th>
                    <th class="text-center px-4 py-3 text-[10px] font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="text-center px-4 py-3 text-[10px] font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody id="table-body" class="divide-y divide-gray-100">
                @include('reservations.partials._table')
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div id="pagination-container">
        @include('reservations.partials._pagination')
    </div>
</div>

<!-- Tombol Floating: Aksi Cepat -->
<div class="fixed bottom-6 right-6 flex flex-col gap-2 z-50">
    <a href="{{ route('checkout.index') }}"
        class="w-11 h-11 bg-amber-500 text-white rounded-full shadow-lg hover:bg-amber-600 transition flex items-center justify-center group relative"
        title="Checkout">
        <i class="fas fa-sign-out-alt"></i>
        <span class="absolute right-full mr-2 bg-gray-800 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition whitespace-nowrap pointer-events-none">Checkout</span>
    </a>
    <a href="{{ route('room-change.index') }}"
        class="w-11 h-11 bg-purple-600 text-white rounded-full shadow-lg hover:bg-purple-700 transition flex items-center justify-center group relative"
        title="Pindah Kamar">
        <i class="fas fa-exchange-alt"></i>
        <span class="absolute right-full mr-2 bg-gray-800 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition whitespace-nowrap pointer-events-none">Pindah Kamar</span>
    </a>
    <button type="button" onclick="Modal.open('{{ route('booking.group.create') }}')"
        class="w-11 h-11 bg-indigo-600 text-white rounded-full shadow-lg hover:bg-indigo-700 transition flex items-center justify-center group relative"
        title="Booking Group">
        <i class="fas fa-users"></i>
        <span class="absolute right-full mr-2 bg-gray-800 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition whitespace-nowrap pointer-events-none">Grup</span>
    </button>
    <button type="button" onclick="Modal.open('{{ route('booking.create') }}')"
        class="w-14 h-14 bg-blue-600 text-white rounded-full shadow-lg hover:bg-blue-700 transition flex items-center justify-center group relative"
        title="Booking Baru">
        <i class="fas fa-plus text-lg"></i>
        <span class="absolute right-full mr-2 bg-gray-800 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition whitespace-nowrap pointer-events-none">Booking Baru</span>
    </button>
</div>

<!-- Edit Room Rate Modal -->
<div id="editRateModal" class="fixed inset-0 z-[100] hidden items-center justify-center" style="background: rgba(0,0,0,0.5);">
    <div class="rounded-xl shadow-2xl w-full max-w-md mx-4 overflow-hidden">
        <div class="flex items-center justify-between px-5 py-3.5 border-b border-gray-100 bg-white">
            <h3 class="text-base font-bold text-gray-800">
                <i class="fas fa-bed text-teal-500 mr-2"></i>Edit Harga Kamar
            </h3>
            <button onclick="closeEditRateModal()" class="text-gray-400 hover:text-gray-600 transition w-7 h-7 flex items-center justify-center rounded-lg hover:bg-gray-100">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="px-5 py-4 space-y-3 bg-white">
            <div>
                <label class="block text-[10px] font-semibold text-gray-500 uppercase tracking-wide mb-1">No. Reservasi</label>
                <input type="text" id="rateModalReservationNumber" readonly
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm bg-gray-50 text-gray-600">
            </div>
            <div>
                <label class="block text-[10px] font-semibold text-gray-500 uppercase tracking-wide mb-1">Harga Default Kamar (Rp/malam)</label>
                <input type="text" id="rateModalDefaultPrice" readonly
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm bg-gray-50 text-gray-400">
            </div>
            <div>
                <label class="block text-[10px] font-semibold text-gray-500 uppercase tracking-wide mb-1">Custom Harga Kamar (Rp/malam)</label>
                <input type="text" id="rateModalCustomRate" placeholder="Kosongkan untuk pakai harga default"
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent">
                <p class="text-[10px] text-gray-400 mt-1">Biarkan kosong untuk mengembalikan ke harga default kamar.</p>
            </div>
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                <div class="flex items-center justify-between text-sm">
                    <span class="text-blue-700">Jumlah Malam:</span>
                    <span class="font-bold text-blue-800" id="rateModalNights">-</span>
                </div>
                <div class="flex items-center justify-between text-sm mt-1">
                    <span class="text-blue-700">Total Baru:</span>
                    <span class="font-bold text-blue-800" id="rateModalNewTotal">-</span>
                </div>
            </div>
        </div>
        <div class="flex items-center justify-end gap-2 px-5 py-3 border-t border-gray-100 bg-gray-50">
            <button onclick="closeEditRateModal()"
                class="px-4 py-2 text-sm font-medium text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-gray-500 transition">
                Batal
            </button>
            <button id="btnSaveRate"
                class="px-4 py-2 text-sm font-medium text-white bg-teal-600 rounded-lg hover:bg-teal-700 transition flex items-center gap-1.5">
                <i class="fas fa-save"></i> Simpan
            </button>
        </div>
    </div>
</div>

<!-- Edit Total Modal -->
<div id="editTotalModal" class="fixed inset-0 z-[100] hidden items-center justify-center" style="background: rgba(0,0,0,0.5);">
    <div class="rounded-xl shadow-2xl w-full max-w-md mx-4 overflow-hidden">
        <div class="flex items-center justify-between px-5 py-3.5 border-b border-gray-100 bg-white">
            <h3 class="text-base font-bold text-gray-800">
                <i class="fas fa-edit text-blue-500 mr-2"></i>Edit Total Reservasi
            </h3>
            <button onclick="closeEditTotalModal()" class="text-gray-400 hover:text-gray-600 transition w-7 h-7 flex items-center justify-center rounded-lg hover:bg-gray-100">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="px-5 py-4 space-y-3 bg-white">
            <div>
                <label class="block text-[10px] font-semibold text-gray-500 uppercase tracking-wide mb-1">No. Reservasi</label>
                <input type="text" id="modalReservationNumber" readonly
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm bg-gray-50 text-gray-600">
            </div>
            <div>
                <label class="block text-[10px] font-semibold text-gray-500 uppercase tracking-wide mb-1">Total Amount (Rp)</label>
                <input type="text" id="modalTotalAmount"
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
        </div>
        <div class="flex items-center justify-end gap-2 px-5 py-3 border-t border-gray-100 bg-gray-50">
            <button onclick="closeEditTotalModal()"
                class="px-4 py-2 text-sm font-medium text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                Batal
            </button>
            <button id="btnSaveTotal"
                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition flex items-center gap-1.5">
                <i class="fas fa-save"></i> Simpan
            </button>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    var editTotalReservationId = null;

    function openEditTotalModal(id, reservationNumber, totalAmount) {
        editTotalReservationId = id;
        document.getElementById('modalReservationNumber').value = reservationNumber;
        document.getElementById('modalTotalAmount').value = new window.Intl.NumberFormat('id-ID').format(totalAmount);
        const modal = document.getElementById('editTotalModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        setTimeout(() => {
            const input = document.getElementById('modalTotalAmount');
            input.focus();
            input.select();
        }, 100);
    }

    function closeEditTotalModal() {
        const modal = document.getElementById('editTotalModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        editTotalReservationId = null;
    }

    // Format rupiah input on typing
    document.getElementById('modalTotalAmount').addEventListener('input', function(e) {
        let value = this.value.replace(/[^0-9]/g, '');
        if (value) {
            this.value = new window.Intl.NumberFormat('id-ID').format(value);
        }
    });

    document.getElementById('btnSaveTotal').addEventListener('click', function() {
        if (!editTotalReservationId) return;

        const amountInput = document.getElementById('modalTotalAmount').value.replace(/[^0-9]/g, '');
        const amount = parseInt(amountInput);

        if (isNaN(amount) || amount < 0) {
            alert('Nominal tidak valid');
            return;
        }

        const btn = this;
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';

        fetch('/reservations/' + editTotalReservationId + '/update-total', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
            },
            body: JSON.stringify({ total_amount: amount }),
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                closeEditTotalModal();
                if (typeof Toast !== 'undefined') {
                    Toast.success(data.message);
                }
                setTimeout(() => location.reload(), 800);
            } else {
                alert(data.message || 'Gagal menyimpan');
            }
        })
        .catch(err => {
            alert('Terjadi kesalahan: ' + err.message);
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = originalText;
        });
    });


    // ===== Edit Room Rate Modal =====
    var editRateReservationId = null;
    var editRateNights = 1;
    var editRateDefaultPrice = 0;

    function openEditRateModal(id, reservationNumber, defaultPrice, customRate) {
        editRateReservationId = id;
        editRateDefaultPrice = defaultPrice;
        editRateNights = 1;

        document.getElementById('rateModalReservationNumber').value = reservationNumber;
        document.getElementById('rateModalDefaultPrice').value = 'Rp ' + new window.Intl.NumberFormat('id-ID').format(defaultPrice);

        // Get nights from the table row
        const row = document.querySelector('button[onclick*="openEditRateModal(' + id + '"]')?.closest('tr');
        if (row) {
            const checkInCell = row.querySelector('td:nth-child(4)');
            const checkOutCell = row.querySelector('td:nth-child(5)');
            if (checkInCell && checkOutCell) {
                const ciText = checkInCell.innerText.trim().split('\n')[0];
                const coText = checkOutCell.innerText.trim().split('\n')[0];
                const ciParts = ciText.split('/');
                const coParts = coText.split('/');
                if (ciParts.length === 3 && coParts.length === 3) {
                    const ci = new window.Date(ciParts[2], ciParts[1]-1, ciParts[0]);
                    const co = new window.Date(coParts[2], coParts[1]-1, coParts[0]);
                    editRateNights = Math.max(1, Math.round((co - ci) / (1000 * 60 * 60 * 24)));
                }
            }
        }

        document.getElementById('rateModalNights').textContent = editRateNights + ' malam';

        const customRateInput = document.getElementById('rateModalCustomRate');
        if (customRate && customRate !== null) {
            customRateInput.value = new window.Intl.NumberFormat('id-ID').format(customRate);
        } else {
            customRateInput.value = '';
        }

        updateRatePreview();

        const modal = document.getElementById('editRateModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        setTimeout(() => customRateInput.focus(), 100);
    }

    function closeEditRateModal() {
        const modal = document.getElementById('editRateModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        editRateReservationId = null;
    }

    function updateRatePreview() {
        const val = document.getElementById('rateModalCustomRate').value.replace(/[^0-9]/g, '');
        const rate = parseInt(val) || editRateDefaultPrice;
        const total = rate * editRateNights;
        document.getElementById('rateModalNewTotal').textContent = 'Rp ' + new window.Intl.NumberFormat('id-ID').format(total);
    }

    document.getElementById('rateModalCustomRate').addEventListener('input', function(e) {
        let value = this.value.replace(/[^0-9]/g, '');
        if (value) {
            this.value = new window.Intl.NumberFormat('id-ID').format(value);
        }
        updateRatePreview();
    });

    document.getElementById('btnSaveRate').addEventListener('click', function() {
        if (!editRateReservationId) return;

        const rawVal = document.getElementById('rateModalCustomRate').value.replace(/[^0-9]/g, '');
        const customRate = rawVal === '' ? null : parseInt(rawVal);

        if (customRate !== null && customRate < 0) {
            alert('Nominal tidak valid');
            return;
        }

        const btn = this;
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';

        fetch('{{ url('reservations') }}/' + editRateReservationId + '/update-room-rate', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
            },
            body: JSON.stringify({ custom_room_rate: customRate }),
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                closeEditRateModal();
                if (typeof Toast !== 'undefined') {
                    Toast.success(data.message);
                }
                setTimeout(() => location.reload(), 800);
            } else {
                alert(data.message || 'Gagal menyimpan');
            }
        })
        .catch(err => {
            alert('Terjadi kesalahan: ' + err.message);
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = originalText;
        });
    });

    document.getElementById('editRateModal').addEventListener('click', function(e) {
        if (e.target === this) closeEditRateModal();
    });

    // Close modal on Escape key (guard untuk Turbo agar tidak menumpuk listener)
    if (!window._reservationsKeydownInit) {
        window._reservationsKeydownInit = true;
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeEditTotalModal();
        });
    }
    document.getElementById('editTotalModal').addEventListener('click', function(e) {
        if (e.target === this) closeEditTotalModal();
    });

    // ─── Toggle Sarapan ──────────────────────────────────────────
    function toggleBreakfast(reservationId, btn) {
        fetch('{{ url("reservations") }}/' + reservationId + '/toggle-breakfast', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({}),
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                if (data.include_breakfast) {
                    btn.className = 'inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold border transition-all duration-150 cursor-pointer hover:shadow-sm bg-amber-100 text-amber-700 border-amber-300';
                    btn.innerHTML = '<i class="fas fa-coffee"></i>';
                } else {
                    btn.className = 'inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold border transition-all duration-150 cursor-pointer hover:shadow-sm bg-gray-50 text-gray-400 border-gray-200 hover:text-amber-600 hover:border-amber-300';
                    btn.innerHTML = '<i class="fas fa-coffee text-[8px] opacity-40"></i>';
                }
                if (typeof Toast !== 'undefined') {
                    Toast.success(data.message);
                }
            }
        })
        .catch(function() {
            if (typeof Toast !== 'undefined') {
                Toast.error('Gagal mengubah status sarapan');
            }
        });
    }

    // Auto-Refresh: Muat Booking Baru Tanpa Reload Halaman
    (function() {
        var pageLoadedAt = new window.Date().toISOString();
        var refreshInterval = 20000; // 20 detik
        var refreshTimer = null;
        var isRefreshing = false;
        var notificationEl = null;

        function showNotification(count) {
            // Hapus notifikasi lama jika ada
            hideNotification();

            notificationEl = document.createElement('div');
            notificationEl.id = 'auto-refresh-notif';
            notificationEl.className = 'fixed top-4 left-1/2 -translate-x-1/2 z-[999]';
            notificationEl.style.animation = 'slideDown 0.3s ease-out';
            notificationEl.innerHTML = '<div class="bg-blue-600 text-white px-5 py-3 rounded-xl shadow-2xl flex items-center gap-3 text-sm font-medium">' +
                '<i class="fas fa-sync-alt fa-spin text-blue-200"></i>' +
                '<span>' + count + ' booking baru ditemukan — memperbarui data...</span>' +
                '</div>';
            document.body.appendChild(notificationEl);
        }

        function hideNotification() {
            if (notificationEl) {
                notificationEl.remove();
                notificationEl = null;
            }
        }

        function checkNewBookings() {
            if (isRefreshing) return;

            fetch('{{ route("reservations.check-new") }}?since=' + encodeURIComponent(pageLoadedAt), {
                headers: { 'Accept': 'application/json' }
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.has_new) {
                    isRefreshing = true;
                    showNotification(data.count);

                    // Ambil parameter filter dari URL saat ini
                    var params = new window.URLSearchParams(window.location.search);
                    var refreshUrl = '{{ route("reservations.refresh") }}?' + params.toString();

                    // Fetch data terbaru via AJAX
                    fetch(refreshUrl, {
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                    })
                    .then(function(r) { return r.json(); })
                    .then(function(data) {
                        if (data.success) {
                            // Update tabel
                            document.getElementById('table-body').innerHTML = data.table_html;
                            // Update statistik
                            document.getElementById('stats-container').innerHTML = data.stats_html;
                            // Update pagination
                            document.getElementById('pagination-container').innerHTML = data.pagination_html;
                            // Reset timer pendeteksi (gunakan waktu baru)
                            pageLoadedAt = new window.Date().toISOString();
                        }
                        hideNotification();
                        isRefreshing = false;
                    })
                    .catch(function() {
                        hideNotification();
                        isRefreshing = false;
                    });
                }
            })
            .catch(function() {});
        }

        if (document.readyState === 'complete') {
            refreshTimer = setInterval(checkNewBookings, refreshInterval);
        } else {
            document.addEventListener('DOMContentLoaded', function() {
                refreshTimer = setInterval(checkNewBookings, refreshInterval);
            });
        }

        document.addEventListener('turbo:before-visit', function() {
            if (refreshTimer) clearInterval(refreshTimer);
        });
    })();
</script>

<style>
    @keyframes slideDown {
        from { opacity: 0; transform: translateY(-20px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>
@endsection