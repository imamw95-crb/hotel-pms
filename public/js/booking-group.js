/**
 * ============================================
 * HOTEL PMS — Booking Group JS
 * Handles group booking (multiple rooms) for both
 * modal and full page versions.
 * Depends on: app.js (Toast, Modal, FormHandler)
 * ============================================
 */

(function() {
    'use strict';

    var checkInEl = document.getElementById('checkIn');
    var checkOutEl = document.getElementById('checkOut');
    var roomsContainer = document.getElementById('roomsContainer');
    var roomInfo = document.getElementById('roomInfo');
    var statusEl = document.getElementById('availabilityStatus');
    var selectedRoomsSection = document.getElementById('selectedRoomsSection');
    var selectedRoomsTable = document.getElementById('selectedRoomsTable');
    var totalPerNightEl = document.getElementById('totalPerNight');
    var btnSubmit = document.getElementById('btnSubmit');
    var bulkPriceInput = document.getElementById('bulkPrice');
    var apiUrl = '';

    var selectedRooms = {};
    var availableRoomsData = [];

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
            if (roomsContainer) {
                roomsContainer.innerHTML = '<p class="col-span-full text-gray-500 text-center py-4">Pilih tanggal check-in & check-out dulu</p>';
            }
            if (roomInfo) roomInfo.textContent = 'Kamar yang tersedia akan muncul setelah memilih tanggal';
            if (statusEl) statusEl.classList.add('hidden');
            return;
        }

        if (co <= ci) {
            if (roomsContainer) {
                roomsContainer.innerHTML = '<p class="col-span-full text-gray-500 text-center py-4">Check-out harus setelah check-in</p>';
            }
            if (statusEl) statusEl.classList.add('hidden');
            return;
        }

        if (roomsContainer) {
            roomsContainer.innerHTML = '<p class="col-span-full text-gray-500 text-center py-4"><i class="fas fa-spinner fa-spin mr-1"></i> Mengecek ketersediaan kamar...</p>';
        }
        if (roomInfo) roomInfo.textContent = 'Sedang mengecek ketersediaan...';
        if (statusEl) statusEl.classList.add('hidden');

        var xhr = new XMLHttpRequest();
        xhr.open('GET', apiUrl + '?check_in=' + ci + '&check_out=' + co, true);
        xhr.setRequestHeader('Accept', 'application/json');
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

        xhr.onload = function() {
            if (xhr.status >= 200 && xhr.status < 300) {
                try {
                    var data = JSON.parse(xhr.responseText);
                    availableRoomsData = data.rooms || [];

                    if (availableRoomsData.length > 0) {
                        if (roomsContainer) roomsContainer.innerHTML = '';
                        for (var i = 0; i < availableRoomsData.length; i++) {
                            var r = availableRoomsData[i];
                            var isChecked = selectedRooms[r.id] ? 'checked' : '';
                            var label = document.createElement('label');
                            label.className = 'flex items-center space-x-2 p-2 rounded hover:bg-blue-50 cursor-pointer transition border ' +
                                (selectedRooms[r.id] ? 'bg-blue-50 border-blue-300' : 'border-transparent');
                            var wd = Number(r.price_weekday || r.price_per_night);
                            var we = Number(r.price_weekend || r.price_per_night);
                            label.innerHTML =
                                '<input type="checkbox" name="room_ids[]" value="' + r.id + '" class="rounded border-gray-300 room-checkbox" ' + isChecked + ' onchange="BookingGroup.toggleRoom(' + r.id + ', this.checked)">' +
                                '<span class="text-sm"><strong>' + r.room_number + '</strong> - ' + (r.room_type_name || 'Standard') + '</span>' +
                                '<span class="text-xs text-gray-500 ml-auto">Wd Rp ' + wd.toLocaleString('id-ID') + ' / We Rp ' + we.toLocaleString('id-ID') + '</span>';
                            if (roomsContainer) roomsContainer.appendChild(label);
                        }
                        if (roomInfo) roomInfo.textContent = availableRoomsData.length + ' kamar tersedia — centang kamar yang ingin di-booking';
                        if (statusEl) {
                            statusEl.className = 'mb-4 p-3 rounded-lg text-sm bg-green-100 border border-green-300 text-green-800';
                            statusEl.innerHTML = '<i class="fas fa-check-circle mr-1"></i> <strong>' + availableRoomsData.length + ' kamar tersedia</strong>';
                            statusEl.classList.remove('hidden');
                        }
                    } else {
                        if (roomsContainer) {
                            roomsContainer.innerHTML = '<p class="col-span-full text-yellow-600 text-center py-4"><i class="fas fa-exclamation-triangle mr-1"></i> Tidak ada kamar tersedia untuk tanggal ini</p>';
                        }
                        if (roomInfo) roomInfo.textContent = 'Semua kamar sudah dipesan pada tanggal tersebut';
                        if (statusEl) {
                            statusEl.className = 'mb-4 p-3 rounded-lg text-sm bg-yellow-100 border border-yellow-300 text-yellow-800';
                            statusEl.innerHTML = '<i class="fas fa-exclamation-triangle mr-1"></i> <strong>Tidak ada kamar tersedia</strong>';
                            statusEl.classList.remove('hidden');
                        }
                    }
                } catch(e) {
                    console.error('BookingGroup: Parse error', e);
                }
            }
        };

        xhr.onerror = function() {
            if (roomsContainer) {
                roomsContainer.innerHTML = '<p class="col-span-full text-red-500 text-center py-4">Gagal mengecek ketersediaan</p>';
            }
        };

        xhr.send();
    }

    // ── Toggle room selection ──
    function toggleRoom(roomId, isChecked) {
        var room = null;
        for (var i = 0; i < availableRoomsData.length; i++) {
            if (availableRoomsData[i].id === roomId) {
                room = availableRoomsData[i];
                break;
            }
        }
        if (!room) return;

        if (isChecked) {
            var wd = Number(room.price_weekday || room.price_per_night);
            var we = Number(room.price_weekend || room.price_per_night);
            selectedRooms[roomId] = {
                id: room.id,
                room_number: room.room_number,
                room_type_name: room.room_type_name || 'Standard',
                default_price: room.price_per_night,
                price: room.price_per_night,
                price_weekday: wd,
                price_weekend: we
            };
        } else {
            delete selectedRooms[roomId];
        }

        renderSelectedRooms();
    }

    // ── Render selected rooms table ──
    function renderSelectedRooms() {
        var rooms = Object.values(selectedRooms);
        if (rooms.length === 0) {
            if (selectedRoomsSection) selectedRoomsSection.classList.add('hidden');
            if (btnSubmit) btnSubmit.disabled = true;
            return;
        }

        if (selectedRoomsSection) selectedRoomsSection.classList.remove('hidden');
        if (btnSubmit) btnSubmit.disabled = false;
        if (selectedRoomsTable) selectedRoomsTable.innerHTML = '';

        var ci = checkInEl ? checkInEl.value : '';
        var co = checkOutEl ? checkOutEl.value : '';
        var totalAll = 0;

        for (var i = 0; i < rooms.length; i++) {
            var room = rooms[i];
            var roomTotal = 0;

            // Check if custom price is set (user edited the price input)
            var customPrice = parseInt(room.price) || 0;
            var hasCustomPrice = customPrice > 0 && customPrice !== Number(room.default_price);

            if (hasCustomPrice && ci && co) {
                var days = Math.ceil((new Date(co) - new Date(ci)) / 86400000);
                roomTotal = customPrice * days;
            } else if (ci && co) {
                // Use weekday/weekend dynamic pricing
                roomTotal = calcRangeTotal(room.price_weekday, room.price_weekend, ci, co);
            } else {
                roomTotal = Number(room.price_weekday || room.default_price);
            }

            totalAll += roomTotal;

            var wd = Number(room.price_weekend || room.default_price);
            var tr = document.createElement('tr');
            tr.className = 'border-b border-gray-100';
            tr.innerHTML =
                '<td class="p-2 font-bold">' + room.room_number + '</td>' +
                '<td class="p-2 text-gray-600">' + room.room_type_name + '</td>' +
                '<td class="p-2 text-center text-gray-400 text-xs">Wd ' + Number(room.price_weekday || room.default_price).toLocaleString('id-ID') + '<br>We ' + wd.toLocaleString('id-ID') + '</td>' +
                '<td class="p-2 text-center">' +
                    '<input type="number" name="room_prices[' + room.id + ']" value="' + (hasCustomPrice ? customPrice : Number(room.price_weekday || room.default_price)) + '" min="0" step="1000" class="w-28 border rounded px-2 py-1 text-center text-sm room-price-input" data-room-id="' + room.id + '" onchange="BookingGroup.updateRoomPrice(' + room.id + ', this.value)">' +
                '</td>' +
                '<td class="p-2 text-center">' +
                    '<button type="button" onclick="BookingGroup.removeRoom(' + room.id + ')" class="text-red-500 hover:text-red-700 text-sm"><i class="fas fa-trash"></i></button>' +
                '</td>';
            if (selectedRoomsTable) selectedRoomsTable.appendChild(tr);
        }

        // Update total tagihan
        var totalTagihanEl = document.getElementById('totalTagihanGroup');
        if (totalTagihanEl) {
            totalTagihanEl.textContent = 'Rp ' + totalAll.toLocaleString('id-ID');
            totalTagihanEl.dataset.total = totalAll;
        }
        updateSisaBayar();
    }

    // ── Update individual room price ──
    function updateRoomPrice(roomId, price) {
        if (selectedRooms[roomId]) {
            selectedRooms[roomId].price = parseInt(price) || 0;
            renderSelectedRooms();
        }
    }

    // ── Remove room from selection ──
    function removeRoom(roomId) {
        delete selectedRooms[roomId];
        // Uncheck the checkbox
        var checkbox = document.querySelector('.room-checkbox[value="' + roomId + '"]');
        if (checkbox) {
            checkbox.checked = false;
            var label = checkbox.closest('label');
            if (label) {
                label.classList.remove('bg-blue-50', 'border-blue-300');
                label.classList.add('border-transparent');
            }
        }
        renderSelectedRooms();
    }

    // ── Apply bulk price to all selected rooms ──
    function applyBulkPrice() {
        var price = parseInt(bulkPriceInput ? bulkPriceInput.value : 0) || 0;
        var roomIds = Object.keys(selectedRooms);
        for (var i = 0; i < roomIds.length; i++) {
            selectedRooms[roomIds[i]].price = price;
        }
        renderSelectedRooms();
    }

    // ── Toggle DP fields ──
    function toggleDpFields() {
        var dpSection = document.getElementById('dpAmountSection');
        var dpInput = document.getElementById('dpAmount');
        var dpRadio = document.querySelector('input[name="payment_type"][value="dp"]');
        var isDp = dpRadio && dpRadio.checked;

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
    }

    // ── Update remaining balance ──
    function updateSisaBayar() {
        var dpInput = document.getElementById('dpAmount');
        var totalEl = document.getElementById('totalTagihanGroup');
        var sisaEl = document.getElementById('sisaBayarGroup');

        if (!totalEl || !sisaEl) return;

        var dpAmount = parseInt(dpInput ? dpInput.value : 0) || 0;
        var total = parseInt(totalEl.dataset.total) || 0;
        var sisa = Math.max(0, total - dpAmount);
        sisaEl.textContent = 'Rp ' + sisa.toLocaleString('id-ID');
    }

    // ── Initialize ──
    function init() {
        // Get API URL from meta tag
        var metaEl = document.querySelector('meta[name="booking-check-url"]');
        if (metaEl) {
            apiUrl = metaEl.getAttribute('content');
        }

        // Fallback: try to find route from form action
        if (!apiUrl) {
            var form = document.getElementById('bookingGroupForm') ||
                       document.querySelector('form[action*="booking-group"]');
            if (form && form.dataset.checkUrl) {
                apiUrl = form.dataset.checkUrl;
            }
        }

        // Last fallback
        if (!apiUrl) {
            apiUrl = '/booking/check-availability';
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
            });
        }

        if (checkOutEl) {
            checkOutEl.addEventListener('change', function() {
                if (checkInEl && checkInEl.value && this.value > checkInEl.value) {
                    checkAvailability();
                }
            });
        }

        var dpInput = document.getElementById('dpAmount');
        if (dpInput) {
            dpInput.addEventListener('input', updateSisaBayar);
        }

        // ── Payment type radio change listeners ──
        var paymentRadios = document.querySelectorAll('input[name="payment_type"]');
        for (var r = 0; r < paymentRadios.length; r++) {
            paymentRadios[r].addEventListener('change', toggleDpFields);
        }

        // ── Auto-trigger if dates pre-filled ──
        if (checkInEl && checkInEl.value && checkOutEl && checkOutEl.value) {
            checkAvailability();
        }
    }

    // ── Expose public API ──
    window.BookingGroup = {
        toggleRoom: toggleRoom,
        updateRoomPrice: updateRoomPrice,
        removeRoom: removeRoom,
        applyBulkPrice: applyBulkPrice,
        toggleDpFields: toggleDpFields,
        checkAvailability: checkAvailability
    };

    // Also expose standalone functions for backward compatibility
    window.toggleDpFields = toggleDpFields;

    // Run on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
