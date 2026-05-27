/**
 * ============================================
 * HOTEL PMS — Booking Modal JS
 * Handles booking form: availability check, 
 * price calculation, DP toggle, etc.
 * Depends on: app.js (Toast, Modal, FormHandler)
 * ============================================
 */

(function() {
    'use strict';

    var checkInEl = document.getElementById('checkIn');
    var checkOutEl = document.getElementById('checkOut');
    var roomSelect = document.getElementById('roomSelect');
    var roomInfo = document.getElementById('roomInfo');
    var priceInput = document.getElementById('pricePerNight');
    var statusEl = document.getElementById('availabilityStatus');
    var apiUrl = '';

    // ── Helper: format date ──
    function fmtDate(d) {
        return d.toISOString().split('T')[0];
    }

    // ── Calculate total with weekday/weekend rates ──
    function calcRangeTotal(priceWeekday, priceWeekend, ci, co) {
        if (!ci || !co || !priceWeekday) return 0;
        var d1 = new Date(ci);
        var d2 = new Date(co);
        if (d2 <= d1) return 0;
        var total = 0;
        var cur = new Date(d1);
        while (cur < d2) {
            var day = cur.getDay(); // 0=Sun, 6=Sat
            var isWeekend = (day === 0 || day === 6);
            total += isWeekend ? (priceWeekend || priceWeekday) : priceWeekday;
            cur.setDate(cur.getDate() + 1);
        }
        return total;
    }

    // ── Set min dates ──
    var today = new Date();
    if (checkInEl) checkInEl.min = fmtDate(today);
    if (checkOutEl) checkOutEl.min = fmtDate(today);

    // ── Check room availability via AJAX ──
    function checkAvailability() {
        var ci = checkInEl ? checkInEl.value : '';
        var co = checkOutEl ? checkOutEl.value : '';

        if (!ci || !co) {
            if (roomSelect) {
                roomSelect.disabled = true;
                roomSelect.innerHTML = '<option value="">-- Pilih tanggal check-in & check-out dulu --</option>';
            }
            if (roomInfo) roomInfo.textContent = 'Kamar yang tersedia akan muncul setelah memilih tanggal';
            if (statusEl) statusEl.classList.add('hidden');
            return;
        }

        if (co <= ci) {
            if (roomSelect) {
                roomSelect.disabled = true;
                roomSelect.innerHTML = '<option value="">-- Check-out harus setelah check-in --</option>';
            }
            if (statusEl) statusEl.classList.add('hidden');
            return;
        }

        if (roomSelect) {
            roomSelect.disabled = true;
            roomSelect.innerHTML = '<option value="">Mengecek ketersediaan kamar...</option>';
        }
        if (roomInfo) roomInfo.textContent = 'Sedang mengecek ketersediaan...';

        var xhr = new XMLHttpRequest();
        xhr.open('GET', apiUrl + '?check_in=' + ci + '&check_out=' + co, true);
        xhr.setRequestHeader('Accept', 'application/json');
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

        xhr.onload = function() {
            if (xhr.status >= 200 && xhr.status < 300) {
                try {
                    var data = JSON.parse(xhr.responseText);
                    if (data.rooms && data.rooms.length > 0) {
                        if (roomSelect) {
                            roomSelect.innerHTML = '<option value="">-- Pilih Kamar --</option>';
                        }
                        var selectedRoomFound = false;
                        for (var i = 0; i < data.rooms.length; i++) {
                            var r = data.rooms[i];
                            var opt = document.createElement('option');
                            opt.value = r.id;
                            var wd = Number(r.price_weekday || r.price_per_night);
                            var we = Number(r.price_weekend || r.price_per_night);
                            opt.textContent = r.room_number + ' - ' + (r.room_type_name || 'Standard') +
                                ' (Weekday Rp ' + wd.toLocaleString('id-ID') + ' / Weekend Rp ' + we.toLocaleString('id-ID') + ')';
                            opt.dataset.price = r.price_per_night;
                            opt.dataset.priceWeekday = wd;
                            opt.dataset.priceWeekend = we;
                            if (roomSelect) roomSelect.appendChild(opt);

                            // Auto-select pre-selected room
                            if (window._preSelectedRoomId && r.id == window._preSelectedRoomId) {
                                opt.selected = true;
                                selectedRoomFound = true;
                                if (priceInput) {
                                    priceInput.value = wd;
                                    priceInput.dataset.edited = 'false';
                                }
                            }
                        }
                        if (roomSelect) roomSelect.disabled = false;

                        if (selectedRoomFound) {
                            if (roomInfo) {
                                roomInfo.textContent = 'Kamar ' + window._preSelectedRoomNumber + ' dipilih (' + data.rooms.length + ' kamar tersedia)';
                                roomInfo.className = 'text-xs mt-1 text-blue-600 font-medium';
                            }
                        } else {
                            if (roomInfo) {
                                roomInfo.textContent = data.rooms.length + ' kamar tersedia';
                                if (window._preSelectedRoomId) {
                                    roomInfo.textContent += ' (Kamar ' + window._preSelectedRoomNumber + ' tidak tersedia pada tanggal ini)';
                                    roomInfo.className = 'text-xs mt-1 text-red-600 font-medium';
                                }
                            }
                        }

                        if (statusEl) {
                            statusEl.className = 'mb-4 p-3 rounded-lg text-sm bg-green-100 border border-green-300 text-green-800';
                            statusEl.innerHTML = '<i class="fas fa-check-circle mr-1"></i> <strong>' + data.rooms.length + ' kamar tersedia</strong>';
                            statusEl.classList.remove('hidden');
                        }
                    } else {
                        if (roomSelect) {
                            roomSelect.innerHTML = '<option value="">-- Tidak ada kamar tersedia --</option>';
                        }
                        if (roomInfo) roomInfo.textContent = 'Semua kamar sudah dipesan pada tanggal tersebut';
                        if (statusEl) {
                            statusEl.className = 'mb-4 p-3 rounded-lg text-sm bg-yellow-100 border border-yellow-300 text-yellow-800';
                            statusEl.innerHTML = '<i class="fas fa-exclamation-triangle mr-1"></i> <strong>Tidak ada kamar tersedia</strong>';
                            statusEl.classList.remove('hidden');
                        }
                    }
                } catch(e) {
                    console.error('BookingModal: Parse error', e);
                }
            }
        };

        xhr.onerror = function() {
            if (roomSelect) {
                roomSelect.innerHTML = '<option value="">-- Error koneksi --</option>';
            }
            if (roomInfo) roomInfo.textContent = 'Gagal mengecek ketersediaan';
        };

        xhr.send();
    }

    // ── Calculate total price ──
    function calculateTotal() {
        var ci = checkInEl ? checkInEl.value : '';
        var co = checkOutEl ? checkOutEl.value : '';
        var totalEl = document.getElementById('totalTagihan');

        if (!totalEl) return;

        // Check if user manually edited price
        if (priceInput && priceInput.dataset.edited === 'true') {
            var manualPrice = parseInt(priceInput.value) || 0;
            if (ci && co && manualPrice > 0) {
                var d1 = new Date(ci);
                var d2 = new Date(co);
                var days = Math.max(1, Math.ceil((d2 - d1) / 86400000));
                var total = manualPrice * days;
                totalEl.textContent = 'Rp ' + total.toLocaleString('id-ID');
                totalEl.dataset.total = total;
            } else {
                totalEl.textContent = 'Rp 0';
                totalEl.dataset.total = 0;
            }
        } else {
            // Use weekday/weekend dynamic pricing
            var sel = roomSelect ? roomSelect.options[roomSelect.selectedIndex] : null;
            var wd = sel && sel.dataset.priceWeekday ? Number(sel.dataset.priceWeekday) : 0;
            var we = sel && sel.dataset.priceWeekend ? Number(sel.dataset.priceWeekend) : 0;
            var total = calcRangeTotal(wd, we, ci, co);
            totalEl.textContent = 'Rp ' + total.toLocaleString('id-ID');
            totalEl.dataset.total = total;

            // Update price input to show average per night for reference
            if (priceInput && !priceInput.dataset.edited && ci && co && total > 0) {
                var d1 = new Date(ci);
                var d2 = new Date(co);
                var days = Math.max(1, Math.ceil((d2 - d1) / 86400000));
                priceInput.value = Math.round(total / days);
            }
        }
        updateSisaBayar();
    }

    // ── Update remaining balance ──
    function updateSisaBayar() {
        var totalEl = document.getElementById('totalTagihan');
        var dpInput = document.getElementById('dpAmount');
        var sisaEl = document.getElementById('sisaBayar');

        if (!totalEl || !sisaEl) return;

        var total = parseInt(totalEl.dataset.total) || 0;
        var dpAmount = parseInt(dpInput ? dpInput.value : 0) || 0;
        var sisa = Math.max(0, total - dpAmount);
        sisaEl.textContent = 'Rp ' + sisa.toLocaleString('id-ID');
    }

    // ── Toggle DP fields (exposed globally for inline onclick) ──
    window.toggleDpFields = function() {
        var isDp = document.querySelector('input[name="payment_type"]:checked') &&
                   document.querySelector('input[name="payment_type"]:checked').value === 'dp';
        var dpSection = document.getElementById('dpAmountSection');
        var dpInput = document.getElementById('dpAmount');

        if (!dpSection || !dpInput) return;

        if (isDp) {
            dpSection.classList.remove('hidden');
            dpInput.setAttribute('required', 'required');
        } else {
            dpSection.classList.add('hidden');
            dpInput.removeAttribute('required');
            dpInput.value = '';
        }
        updateSisaBayar();
    };

    // ── Initialize ──
    function init() {
        // Get API URL from meta or data attribute
        var metaEl = document.querySelector('meta[name="booking-check-url"]');
        if (metaEl) {
            apiUrl = metaEl.getAttribute('content');
        }

        // If no meta tag, try to find the route URL from a data attribute on the form
        if (!apiUrl) {
            var form = document.querySelector('form[action*="booking"]');
            if (form && form.dataset.checkUrl) {
                apiUrl = form.dataset.checkUrl;
            }
        }

        // ── Event Listeners ──
        if (checkInEl) {
            checkInEl.addEventListener('change', function() {
                if (checkOutEl) {
                    checkOutEl.min = this.value;
                    if (checkOutEl.value && checkOutEl.value <= this.value) {
                        checkOutEl.value = '';
                    }
                }
                checkAvailability();
                calculateTotal();
            });
        }

        if (checkOutEl) {
            checkOutEl.addEventListener('change', function() {
                if (checkInEl && checkInEl.value && this.value > checkInEl.value) {
                    checkAvailability();
                    calculateTotal();
                }
            });
        }

        if (roomSelect) {
            roomSelect.addEventListener('change', function() {
                var sel = this.options[this.selectedIndex];
                if (sel && sel.dataset.priceWeekday && priceInput) {
                    priceInput.dataset.edited = 'false';
                }
                calculateTotal();
            });
        }

        if (priceInput) {
            priceInput.addEventListener('input', function() {
                this.dataset.edited = 'true';
                calculateTotal();
            });
        }

        var dpInput = document.getElementById('dpAmount');
        if (dpInput) {
            dpInput.addEventListener('input', updateSisaBayar);
        }

        // ── Auto-trigger if dates pre-filled ──
        if (checkInEl && checkInEl.value && checkOutEl && checkOutEl.value) {
            checkAvailability();
        }

        // ── Pre-select room info ──
        if (window._preSelectedRoomId && roomInfo) {
            roomInfo.textContent = 'Kamar ' + window._preSelectedRoomNumber + ' dipilih. Pastikan tanggal sesuai.';
            roomInfo.className = 'text-xs mt-1 text-blue-600 font-medium';
        }
    }

    // Run on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
