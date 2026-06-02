/**
 * ============================================
 * HOTEL PMS — Booking OTA JS
 * Handles OTA booking modal: auto-detect OTA source,
 * availability check, price calculation.
 * Depends on: app.js (Toast, Modal, FormHandler)
 * ============================================
 */
(function() {
    'use strict';

    var checkInEl = document.getElementById('otaCheckIn');
    var checkOutEl = document.getElementById('otaCheckOut');
    var roomSelect = document.getElementById('otaRoomSelect');
    var roomInfo = document.getElementById('otaRoomInfo');
    var priceInput = document.getElementById('otaPricePerNight');
    var statusEl = document.getElementById('availabilityStatus');
    var otaResNumber = document.getElementById('otaReservationNumber');
    var otaSourceSelect = document.getElementById('otaSource');
    var otaDetectedSource = document.getElementById('otaDetectedSource');
    var apiUrl = '';

    // ── Resolve API URL ──
    var metaTag = document.querySelector('meta[name="booking-check-url"]');
    if (metaTag) {
        apiUrl = metaTag.getAttribute('content');
    }

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
            var day = cur.getDay();
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

    // ── Auto-detect OTA Source from reservation number ──
    function detectOtaSource(value) {
        if (!value) {
            otaSourceSelect.value = '';
            otaDetectedSource.textContent = 'Ketik nomor reservasi untuk auto-detect';
            otaDetectedSource.className = 'text-xs text-gray-500 mt-1';
            return;
        }

        var upper = value.toUpperCase().trim();
        var source = '';
        var label = '';

        if (upper.startsWith('TVL-') || upper.startsWith('TRV-')) {
            source = 'traveloka.com';
            label = 'Traveloka';
        } else if (upper.startsWith('TIK-') || upper.startsWith('TKT-')) {
            source = 'tiket.com';
            label = 'Tiket.com';
        } else if (upper.indexOf('BKNG') !== -1 || upper.indexOf('BOOKING') !== -1) {
            source = 'traveloka.com';
            label = 'Traveloka (estimated)';
        }

        if (source) {
            otaSourceSelect.value = source;
            otaDetectedSource.innerHTML = '<i class="fas fa-check-circle text-teal-600 mr-1"></i> Terdeteksi: <strong>' + label + '</strong>';
            otaDetectedSource.className = 'text-xs text-teal-700 mt-1';
        } else {
            otaSourceSelect.value = '';
            otaDetectedSource.textContent = 'Sumber OTA tidak terdeteksi. Pilih manual.';
            otaDetectedSource.className = 'text-xs text-amber-600 mt-1';
        }
    }

    // ── Event: OTA Reservation Number input ──
    if (otaResNumber) {
        otaResNumber.addEventListener('input', function() {
            detectOtaSource(this.value);
        });
    }

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
                        }
                        if (roomSelect) roomSelect.disabled = false;
                        if (roomInfo) roomInfo.textContent = data.rooms.length + ' kamar tersedia untuk ' + fmt(ci) + ' - ' + fmt(co);
                        if (statusEl) {
                            statusEl.className = 'mb-4 p-3 rounded-lg text-sm bg-green-100 border border-green-300 text-green-800';
                            statusEl.innerHTML = '<i class="fas fa-check-circle mr-1"></i> <strong>' + data.rooms.length + ' kamar tersedia</strong>';
                            statusEl.classList.remove('hidden');
                        }
                        calculateTotal();
                    } else {
                        if (roomSelect) {
                            roomSelect.innerHTML = '<option value="">-- Tidak ada kamar tersedia --</option>';
                            roomSelect.disabled = true;
                        }
                        if (roomInfo) roomInfo.textContent = 'Semua kamar sudah dipesan pada tanggal tersebut';
                        if (statusEl) {
                            statusEl.className = 'mb-4 p-3 rounded-lg text-sm bg-yellow-100 border border-yellow-300 text-yellow-800';
                            statusEl.innerHTML = '<i class="fas fa-exclamation-triangle mr-1"></i> <strong>Tidak ada kamar tersedia</strong>. Pilih tanggal lain.';
                            statusEl.classList.remove('hidden');
                        }
                    }
                } catch(e) {
                    console.error('BookingOta: Parse error', e);
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
        var totalEl = document.getElementById('otaTotalTagihan');

        if (!totalEl) return;

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
            var sel = roomSelect ? roomSelect.options[roomSelect.selectedIndex] : null;
            var wd = sel && sel.dataset.priceWeekday ? Number(sel.dataset.priceWeekday) : 0;
            var we = sel && sel.dataset.priceWeekend ? Number(sel.dataset.priceWeekend) : 0;
            var total = calcRangeTotal(wd, we, ci, co);
            totalEl.textContent = 'Rp ' + total.toLocaleString('id-ID');
            totalEl.dataset.total = total;

            if (priceInput && !priceInput.dataset.edited && ci && co && total > 0) {
                var d1 = new Date(ci);
                var d2 = new Date(co);
                var days = Math.max(1, Math.ceil((d2 - d1) / 86400000));
                priceInput.value = Math.round(total / days);
            }
        }
    }

    // ── Helper: format date for display ──
    function fmt(d) {
        return new Date(d).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
    }

    // ── Event listeners ──
    if (checkInEl) checkInEl.addEventListener('change', checkAvailability);
    if (checkOutEl) checkOutEl.addEventListener('change', checkAvailability);
    if (roomSelect) roomSelect.addEventListener('change', calculateTotal);
    if (priceInput) {
        priceInput.addEventListener('input', function() {
            this.dataset.edited = 'true';
            calculateTotal();
        });
    }

    // Auto-trigger check if dates are pre-filled
    if (checkInEl && checkInEl.value && checkOutEl && checkOutEl.value) {
        checkAvailability();
    }

    // Auto-detect based on pre-filled room ID
    if (window._preSelectedRoomId && window._preSelectedRoomNumber && roomSelect) {
        // Will be handled after availability check loads the rooms
        var checkExist = setInterval(function() {
            if (!roomSelect.disabled) {
                for (var i = 0; i < roomSelect.options.length; i++) {
                    if (roomSelect.options[i].value === window._preSelectedRoomId) {
                        roomSelect.value = window._preSelectedRoomId;
                        calculateTotal();
                        break;
                    }
                }
                clearInterval(checkExist);
            }
        }, 200);
    }

    // ── Expose public API ──
    window.BookingOta = {
        toggleOtaPaidAmount: function() {
            var statusSelect = document.getElementById('otaPaymentStatus');
            var paidWrap = document.getElementById('otaPaidAmountWrap');
            if (!statusSelect || !paidWrap) return;
            var val = statusSelect.value;
            if (val === 'paid_ota' || val === 'partial_ota') {
                paidWrap.classList.remove('hidden');
            } else {
                paidWrap.classList.add('hidden');
                document.getElementById('otaPaidAmount').value = '';
            }
        },
        detectOtaSource: detectOtaSource
    };
})();
