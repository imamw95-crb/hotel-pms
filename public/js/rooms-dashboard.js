/**
 * ============================================
 * HOTEL PMS — Rooms Dashboard JS
 * Realtime room status, modal booking, filters
 * Depends on: app.js (Toast, Modal, FormHandler)
 * ============================================
 */

var RoomsDashboard = {
    config: {
        apiUrl: '',
        bookingUrl: '',
        otaBookingUrl: '',
        refreshInterval: 5000
    },

    state: {
        statusFilter: 'all',
        roomTypeFilter: 'all',
        dateFrom: '',
        dateTo: '',
        selectedRooms: {},
        bulkMode: false
    },

    init: function() {
        var apiEl = document.querySelector('[data-rooms-api]');
        var bookingEl = document.querySelector('[data-booking-url]');
        var otaBookingEl = document.querySelector('[data-ota-booking-url]');
        var dateFromEl = document.querySelector('[data-date-from]');
        var dateToEl = document.querySelector('[data-date-to]');
        this.config.apiUrl = apiEl ? apiEl.getAttribute('data-rooms-api') : '';
        this.config.bookingUrl = bookingEl ? bookingEl.getAttribute('data-booking-url') : '';
        this.config.otaBookingUrl = otaBookingEl ? otaBookingEl.getAttribute('data-ota-booking-url') : '';
        this.state.dateFrom = dateFromEl ? dateFromEl.getAttribute('data-date-from') : '';
        this.state.dateTo = dateToEl ? dateToEl.getAttribute('data-date-to') : '';

        this.bindFilterEvents();
        this.bindKeyboardShortcuts();
        this.startRealtimeRefresh();
        this.refresh();
    },

    // ========== FILTER EVENTS ==========
    bindFilterEvents: function() {
        var self = this;

        var statusEls = document.querySelectorAll('[data-filter-status]');
        for (var i = 0; i < statusEls.length; i++) {
            statusEls[i].addEventListener('change', function(e) {
                self.state.statusFilter = e.target.value;
                self.refresh();
            });
        }

        var typeEls = document.querySelectorAll('[data-filter-type]');
        for (var j = 0; j < typeEls.length; j++) {
            typeEls[j].addEventListener('change', function(e) {
                self.state.roomTypeFilter = e.target.value;
                self.refresh();
            });
        }

        var dateFromEls = document.querySelectorAll('[data-filter-date-from]');
        for (var k = 0; k < dateFromEls.length; k++) {
            dateFromEls[k].addEventListener('change', function(e) {
                self.state.dateFrom = e.target.value;
                self.refresh();
            });
        }

        var dateToEls = document.querySelectorAll('[data-filter-date-to]');
        for (var l = 0; l < dateToEls.length; l++) {
            dateToEls[l].addEventListener('change', function(e) {
                self.state.dateTo = e.target.value;
                self.refresh();
            });
        }

        var tabEls = document.querySelectorAll('[data-tab-filter]');
        for (var m = 0; m < tabEls.length; m++) {
            (function(btn) {
                btn.addEventListener('click', function(e) {
                    var allTabs = document.querySelectorAll('[data-tab-filter]');
                    for (var n = 0; n < allTabs.length; n++) {
                        allTabs[n].classList.remove('border-blue-500', 'text-blue-600');
                        allTabs[n].classList.add('border-transparent', 'hover:border-gray-300');
                    }
                    e.target.classList.add('border-blue-500', 'text-blue-600');
                    self.filterByTab(e.target.getAttribute('data-tab-filter'));
                });
            })(tabEls[m]);
        }

        var bulkToggle = document.querySelector('[data-bulk-toggle]');
        if (bulkToggle) {
            bulkToggle.addEventListener('click', function() { self.toggleBulkMode(); });
        }

        var bulkCheckin = document.querySelector('[data-bulk-checkin]');
        if (bulkCheckin) bulkCheckin.addEventListener('click', function() { self.bulkCheckin(); });

        var bulkCheckout = document.querySelector('[data-bulk-checkout]');
        if (bulkCheckout) bulkCheckout.addEventListener('click', function() { self.bulkCheckout(); });

        var bulkMaint = document.querySelector('[data-bulk-maintenance]');
        if (bulkMaint) bulkMaint.addEventListener('click', function() { self.bulkMaintenance(); });

        var bulkAvail = document.querySelector('[data-bulk-available]');
        if (bulkAvail) bulkAvail.addEventListener('click', function() { self.bulkAvailable(); });

        var bulkOoo = document.querySelector('[data-bulk-out-of-order]');
        if (bulkOoo) bulkOoo.addEventListener('click', function() { self.bulkOutOfOrder(); });
    },

    // ========== REALTIME REFRESH ==========
    startRealtimeRefresh: function() {
        if (this._refreshTimerId) return; // prevent duplicate intervals
        var self = this;
        this._refreshTimerId = setInterval(function() { self.refresh(true); }, this.config.refreshInterval);
        // Also refresh immediately when page regains focus (e.g. after checkout in another tab/page)
        document.addEventListener('visibilitychange', function() {
            if (!document.hidden) self.refresh(true);
        });
        window.addEventListener('focus', function() {
            self.refresh(true);
        });
    },

    refresh: function(silent) {
        var grid = document.getElementById('roomsGrid');
        if (!grid) return;
        var self = this;

        if (!silent && !grid.dataset.loaded) {
            grid.innerHTML = this._skeletonHTML();
        } else if (!silent) {
            grid.classList.add('opacity-50');
        }

        var params = 'status_filter=' + encodeURIComponent(this.state.statusFilter) +
            '&room_type=' + encodeURIComponent(this.state.roomTypeFilter) +
            '&date_from=' + encodeURIComponent(this.state.dateFrom) +
            '&date_to=' + encodeURIComponent(this.state.dateTo);

        var xhr = new XMLHttpRequest();
        xhr.open('GET', this.config.apiUrl + '?' + params, true);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.setRequestHeader('Accept', 'application/json');
        xhr.onload = function() {
            if (xhr.status >= 200 && xhr.status < 300) {
                try {
                    var data = JSON.parse(xhr.responseText);
                    self._updateCounts(data);
                    self._renderRooms(data, grid);
                    grid.dataset.loaded = 'true';
                } catch(e) {
                    console.error('Parse error:', e);
                }
            }
            grid.classList.remove('opacity-50');
        };
        xhr.onerror = function() {
            grid.classList.remove('opacity-50');
            if (!silent) Toast.error('Gagal memuat data kamar');
        };
        xhr.send();
    },

    // ========== ROOM CARD RENDERING ==========
    _renderRooms: function(data, grid) {
        grid.innerHTML = '';
        if (!data.rooms || data.rooms.length === 0) {
            grid.innerHTML = '<div class="col-span-full text-center py-12"><i class="fas fa-search text-4xl text-gray-300 mb-3"></i><p class="text-gray-500">Tidak ada kamar yang sesuai filter.</p></div>';
            return;
        }
        for (var i = 0; i < data.rooms.length; i++) {
            var card = this._createRoomCard(data.rooms[i], data.due_out_room_ids || []);
            grid.appendChild(card);
        }
    },

    _createRoomCard: function(room, dueOutIds) {
        var isDueOut = dueOutIds.indexOf(room.id) !== -1;
        var status = this._getStatusConfig(room.status, isDueOut);
        var guestName = '';
        if (room.reservations && room.reservations.length > 0 && room.reservations[0].guest) {
            guestName = room.reservations[0].guest.guest_name;
        }
        var reservation = room.reservations ? room.reservations[0] : null;
        var isSelected = this.state.selectedRooms[room.id] ? true : false;

        var card = document.createElement('div');
        card.className = 'room-card border-2 rounded-xl p-3 text-center cursor-pointer hover:shadow-lg transition-all duration-200 relative group ' + status.borderClass + (isSelected ? ' ring-2 ring-blue-500' : '');
        card.setAttribute('data-room-id', room.id);
        card.setAttribute('data-room-number', room.room_number);
        card.setAttribute('data-room-type', room.room_type_name || 'Standard');
        card.setAttribute('data-status', room.status);

        var checkbox = this.state.bulkMode ? '<div class="absolute top-2 left-2 z-10"><input type="checkbox" class="w-4 h-4 text-blue-600 rounded bulk-room-checkbox" data-room-id="' + room.id + '"' + (isSelected ? ' checked' : '') + ' onclick="event.stopPropagation(); if(window.RoomsDashboard) window.RoomsDashboard.toggleRoomSelection(' + room.id + ')"></div>' : '';

        var quickAction = '<div class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity z-10"><button onclick="event.stopPropagation(); if(window.RoomsDashboard) window.RoomsDashboard.quickAction(' + room.id + ',\'' + room.room_number.replace(/'/g, "\\'") + '\',\'' + room.status + '\')" class="w-7 h-7 rounded-full bg-white shadow-md hover:bg-blue-50 flex items-center justify-center text-gray-500 hover:text-blue-600 transition"><i class="fas fa-ellipsis-v text-xs"></i></button></div>';

        var html = checkbox + quickAction + '<div class="rounded-lg p-3 ' + status.bgClass + '">';
        html += '<i class="fas ' + status.icon + ' text-2xl mb-1"></i>';
        html += '<p class="font-bold text-xl">' + room.room_number + '</p>';
        html += '<p class="text-xs opacity-75">' + (room.room_type_name || 'Standard') + '</p>';
        html += '<span class="inline-block mt-1 px-2 py-0.5 rounded-full text-xs font-semibold ' + status.badgeClass + '">' + status.label + '</span>';
        if (guestName) html += '<p class="text-xs mt-2 truncate font-medium" title="' + guestName + '"><i class="fas fa-user mr-1"></i>' + guestName + '</p>';
        if (reservation) html += '<p class="text-xs mt-1 opacity-60"><i class="fas fa-calendar mr-1"></i>' + (reservation.check_in ? reservation.check_in.substring(5, 10) : '') + ' - ' + (reservation.check_out ? reservation.check_out.substring(5, 10) : '') + '</p>';
        html += '</div>';
        card.innerHTML = html;

        var self = this;
        card.addEventListener('click', function() {
            if (self.state.bulkMode) {
                self.toggleRoomSelection(room.id);
                var cb = card.querySelector('.bulk-room-checkbox');
                if (cb) cb.checked = self.state.selectedRooms[room.id] ? true : false;
            } else {
                self.openBooking(room.id, room.room_number);
            }
        });

        return card;
    },

    _getStatusConfig: function(status, isDueOut) {
        if (status === 'available') return { bgClass: 'bg-emerald-50', borderClass: 'border-emerald-400', icon: 'fa-check-circle text-emerald-500', badgeClass: 'bg-emerald-100 text-emerald-700', label: 'Available' };
        if (status === 'occupied' && isDueOut) return { bgClass: 'bg-amber-50', borderClass: 'border-amber-400', icon: 'fa-clock text-amber-500', badgeClass: 'bg-amber-100 text-amber-700', label: 'Due Out' };
        if (status === 'occupied') return { bgClass: 'bg-red-50', borderClass: 'border-red-400', icon: 'fa-ban text-red-500', badgeClass: 'bg-red-100 text-red-700', label: 'Occupied' };
        if (status === 'maintenance') return { bgClass: 'bg-gray-50', borderClass: 'border-gray-400', icon: 'fa-tools text-gray-500', badgeClass: 'bg-gray-100 text-gray-700', label: 'Maintenance' };
        if (status === 'out_of_order') return { bgClass: 'bg-pink-50', borderClass: 'border-pink-400', icon: 'fa-plug text-pink-500', badgeClass: 'bg-pink-100 text-pink-700', label: 'Out of Order' };
        return { bgClass: 'bg-yellow-50', borderClass: 'border-yellow-400', icon: 'fa-broom text-yellow-500', badgeClass: 'bg-yellow-100 text-yellow-700', label: 'Cleaning' };
    },

    _updateCounts: function(data) {
        var availableEl = document.getElementById('availableCount');
        var checkinsEl = document.getElementById('checkinsCount');
        var checkoutsEl = document.getElementById('checkoutsCount');
        if (availableEl) availableEl.textContent = data.available_count || 0;
        if (checkinsEl) checkinsEl.textContent = data.checkins_today || 0;
        if (checkoutsEl) checkoutsEl.textContent = data.checkouts_today || 0;
    },

    _skeletonHTML: function() {
        var html = '';
        for (var i = 0; i < 12; i++) {
            html += '<div class="border-2 border-gray-200 rounded-xl p-3 animate-pulse"><div class="rounded-lg p-3 bg-gray-100"><div class="w-8 h-8 bg-gray-200 rounded-full mx-auto mb-2"></div><div class="h-5 bg-gray-200 rounded w-16 mx-auto mb-1"></div><div class="h-3 bg-gray-200 rounded w-12 mx-auto mb-1"></div><div class="h-4 bg-gray-200 rounded w-14 mx-auto mt-2"></div></div></div>';
        }
        return html;
    },

    // ========== BOOKING ==========
    openBooking: function(roomId, roomNumber) {
        var today = new Date();
        var tomorrow = new Date(today);
        tomorrow.setDate(tomorrow.getDate() + 1);
        var fmt = function(d) { return d.toISOString().split('T')[0]; };
        var url = this.config.bookingUrl + '?room_id=' + roomId + '&check_in=' + fmt(today) + '&check_out=' + fmt(tomorrow);
        Modal.open(url);
    },

    openOtaBooking: function(roomId) {
        var url = this.config.otaBookingUrl;
        if (!url) url = '/hotel-pms/public/booking/ota-create';
        if (roomId) {
            var today = new Date();
            var tomorrow = new Date(today);
            tomorrow.setDate(tomorrow.getDate() + 1);
            var fmt = function(d) { return d.toISOString().split('T')[0]; };
            url += '?room_id=' + roomId + '&check_in=' + fmt(today) + '&check_out=' + fmt(tomorrow);
        }
        Modal.open(url);
    },

    quickAction: function(roomId, roomNumber, status) {
        var actions = [];
        if (status === 'available') {
            actions.push({ label: 'Booking', icon: 'fa-calendar-plus', fn: 'openBooking' });
            actions.push({ label: 'Booking OTA', icon: 'fa-globe', fn: 'openOtaBooking' });
            actions.push({ label: 'Set Available', icon: 'fa-check', fn: 'setAvailable' });
            actions.push({ label: 'Set Maintenance', icon: 'fa-tools', fn: 'setMaintenance' });
            actions.push({ label: 'Set Out of Order', icon: 'fa-plug', fn: 'setOutOfOrder' });
        }
        if (status === 'occupied' || status === 'due_out') {
            actions.push({ label: 'Check-out', icon: 'fa-sign-out-alt', fn: 'checkoutRoom' });
            actions.push({ label: 'Set Available', icon: 'fa-check', fn: 'setAvailable' });
        }
        if (status === 'maintenance' || status === 'cleaning' || status === 'out_of_order') {
            actions.push({ label: 'Set Available', icon: 'fa-check', fn: 'setAvailable' });
        }

        var menu = document.createElement('div');
        menu.className = 'fixed inset-0 z-[300] flex items-center justify-center';
        var html = '<div class="absolute inset-0 bg-black/30" onclick="this.parentElement.remove()"></div>' +
            '<div class="bg-white rounded-xl shadow-2xl p-4 w-64 relative z-10">' +
            '<h3 class="font-bold text-lg mb-3">Kamar ' + roomNumber + '</h3>' +
            '<div class="space-y-1">';
        for (var i = 0; i < actions.length; i++) {
            html += '<button onclick="this.closest(\'.fixed\').remove(); if(window.RoomsDashboard) window.RoomsDashboard.' + actions[i].fn + '(' + roomId + ')" class="w-full text-left px-3 py-2 rounded-lg hover:bg-blue-50 hover:text-blue-600 transition flex items-center gap-2"><i class="fas ' + actions[i].icon + ' w-5"></i> ' + actions[i].label + '</button>';
        }
        html += '</div></div>';
        menu.innerHTML = html;
        document.body.appendChild(menu);
    },

    // ========== ROOM ACTIONS ==========
    setMaintenance: function(roomId) { this._updateRoomStatus(roomId, 'maintenance'); },
    setOutOfOrder: function(roomId) { this._updateRoomStatus(roomId, 'out_of_order'); },
    setAvailable: function(roomId) { this._updateRoomStatus(roomId, 'available'); },

    _updateRoomStatus: function(roomId, status) {
        var self = this;
        var xhr = new XMLHttpRequest();
        xhr.open('PATCH', '/rooms/' + roomId + '/status', true);
        xhr.setRequestHeader('Content-Type', 'application/json');
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.setRequestHeader('Accept', 'application/json');
        var token = document.querySelector('meta[name="csrf-token"]');
        if (token) xhr.setRequestHeader('X-CSRF-TOKEN', token.getAttribute('content'));
        xhr.onload = function() {
            if (xhr.status >= 200 && xhr.status < 300) {
                try {
                    var data = JSON.parse(xhr.responseText);
                    if (data.success) { Toast.success(data.message); self.refresh(); }
                    else Toast.error(data.message || 'Gagal');
                } catch(e) { Toast.error('Response tidak valid'); }
            }
        };
        xhr.send(JSON.stringify({ status: status }));
    },

    checkoutRoom: function(roomId) {
        if (!window.confirm('Check-out kamar ini? Status kamar akan berubah menjadi Available.')) return;
        var self = this;
        var xhr = new XMLHttpRequest();
        xhr.open('POST', '/rooms/' + roomId + '/checkout', true);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.setRequestHeader('Accept', 'application/json');
        var token = document.querySelector('meta[name="csrf-token"]');
        if (token) xhr.setRequestHeader('X-CSRF-TOKEN', token.getAttribute('content'));
        xhr.onload = function() {
            if (xhr.status >= 200 && xhr.status < 300) {
                try {
                    var data = JSON.parse(xhr.responseText);
                    if (data.success) { Toast.success(data.message); self.refresh(); }
                    else Toast.error(data.message || 'Gagal checkout');
                } catch(e) { Toast.error('Response tidak valid'); }
            } else {
                try {
                    var err = JSON.parse(xhr.responseText);
                    Toast.error(err.message || 'Gagal checkout (HTTP ' + xhr.status + ')');
                } catch(e) {
                    if (xhr.status === 419) Toast.error('Session expired. Silakan refresh halaman.');
                    else if (xhr.status === 403) Toast.error('Anda tidak memiliki izin untuk checkout.');
                    else if (xhr.status === 404) Toast.error('Reservasi tidak ditemukan.');
                    else Toast.error('Gagal checkout (HTTP ' + xhr.status + ')');
                }
            }
        };
        xhr.onerror = function() { Toast.error('Koneksi gagal. Periksa jaringan Anda.'); };
        xhr.send();
    },

    // ========== TAB FILTER ==========
    filterByTab: function(tabName) {
        var cards = document.querySelectorAll('.room-card');
        for (var i = 0; i < cards.length; i++) {
            var cardType = cards[i].getAttribute('data-room-type');
            cards[i].style.display = (tabName === 'all' || cardType === tabName) ? 'block' : 'none';
        }
    },

    // ========== BULK ACTIONS ==========
    toggleBulkMode: function() {
        this.state.bulkMode = !this.state.bulkMode;
        this.state.selectedRooms = {};

        var panel = document.getElementById('bulkActionPanel');
        if (panel) panel.classList.toggle('hidden', !this.state.bulkMode);

        // Re-render cards with/without checkboxes
        this.refresh(true);

        if (this.state.bulkMode) {
            this._updateBulkCount();
            Toast.info('Mode bulk aktif — pilih kamar');
        }
    },

    toggleRoomSelection: function(roomId) {
        if (this.state.selectedRooms[roomId]) {
            delete this.state.selectedRooms[roomId];
        } else {
            this.state.selectedRooms[roomId] = true;
        }
        this._updateBulkCount();
    },

    _updateBulkCount: function() {
        var count = 0;
        for (var k in this.state.selectedRooms) { if (this.state.selectedRooms.hasOwnProperty(k)) count++; }
        var el = document.getElementById('bulkSelectedCount');
        if (el) el.textContent = count;

        var btns = document.querySelectorAll('[data-bulk-action]');
        for (var i = 0; i < btns.length; i++) {
            btns[i].disabled = count === 0;
            btns[i].classList.toggle('opacity-50', count === 0);
        }
    },

    bulkCheckin: function() {
        if (this._selectedCount() === 0) return;
        Toast.info('Fitur bulk check-in akan segera tersedia');
    },

    bulkCheckout: function() {
        var count = this._selectedCount();
        if (count === 0) return;
        if (!window.confirm('Check-out ' + count + ' kamar terpilih? Status kamar akan berubah menjadi Available.')) return;

        var self = this;
        var roomIds = [];
        for (var k in this.state.selectedRooms) {
            if (this.state.selectedRooms.hasOwnProperty(k)) roomIds.push(k);
        }

        var successCount = 0;
        var failCount = 0;
        var total = roomIds.length;

        for (var i = 0; i < roomIds.length; i++) {
            (function(rid) {
                var xhr = new XMLHttpRequest();
                xhr.open('POST', '/rooms/' + rid + '/checkout', true);
                xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                xhr.setRequestHeader('Accept', 'application/json');
                var token = document.querySelector('meta[name="csrf-token"]');
                if (token) xhr.setRequestHeader('X-CSRF-TOKEN', token.getAttribute('content'));
                xhr.onload = function() {
                    if (xhr.status >= 200 && xhr.status < 300) {
                        try {
                            var data = JSON.parse(xhr.responseText);
                            if (data.success) successCount++;
                            else failCount++;
                        } catch(e) { failCount++; }
                    } else {
                        failCount++;
                    }
                    if (successCount + failCount === total) {
                        if (successCount > 0) Toast.success(successCount + ' kamar berhasil di-check-out.');
                        if (failCount > 0) Toast.error(failCount + ' kamar gagal di-check-out.');
                        self.state.selectedRooms = {};
                        self.toggleBulkMode();
                        self.refresh();
                    }
                };
                xhr.onerror = function() {
                    failCount++;
                    if (successCount + failCount === total) {
                        if (successCount > 0) Toast.success(successCount + ' kamar berhasil di-check-out.');
                        if (failCount > 0) Toast.error(failCount + ' kamar gagal di-check-out.');
                        self.state.selectedRooms = {};
                        self.toggleBulkMode();
                        self.refresh();
                    }
                };
                xhr.send();
            })(roomIds[i]);
        }
    },

    bulkAvailable: function() {
        var count = this._selectedCount();
        if (count === 0) return;
        if (!window.confirm('Set ' + count + ' kamar ke Available?')) return;

        var self = this;
        var roomIds = [];
        for (var k in this.state.selectedRooms) {
            if (this.state.selectedRooms.hasOwnProperty(k)) roomIds.push(k);
        }

        var xhr = new XMLHttpRequest();
        xhr.open('PATCH', '/rooms/bulk-status', true);
        xhr.setRequestHeader('Content-Type', 'application/json');
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.setRequestHeader('Accept', 'application/json');
        var token = document.querySelector('meta[name="csrf-token"]');
        if (token) xhr.setRequestHeader('X-CSRF-TOKEN', token.getAttribute('content'));
        xhr.onload = function() {
            if (xhr.status >= 200 && xhr.status < 300) {
                try {
                    var data = JSON.parse(xhr.responseText);
                    if (data.success) {
                        Toast.success(data.message);
                        self.state.selectedRooms = {};
                        self.toggleBulkMode();
                        self.refresh();
                    } else {
                        Toast.error(data.message || 'Gagal');
                    }
                } catch(e) { Toast.error('Response tidak valid'); }
            }
        };
        xhr.send(JSON.stringify({ room_ids: roomIds, status: 'available' }));
    },

    bulkMaintenance: function() {
        var count = this._selectedCount();
        if (count === 0) return;
        if (!window.confirm('Set ' + count + ' kamar ke Maintenance?')) return;

        var self = this;
        var roomIds = [];
        for (var k in this.state.selectedRooms) {
            if (this.state.selectedRooms.hasOwnProperty(k)) roomIds.push(k);
        }

        var xhr = new XMLHttpRequest();
        xhr.open('PATCH', '/rooms/bulk-status', true);
        xhr.setRequestHeader('Content-Type', 'application/json');
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.setRequestHeader('Accept', 'application/json');
        var token = document.querySelector('meta[name="csrf-token"]');
        if (token) xhr.setRequestHeader('X-CSRF-TOKEN', token.getAttribute('content'));
        xhr.onload = function() {
            if (xhr.status >= 200 && xhr.status < 300) {
                try {
                    var data = JSON.parse(xhr.responseText);
                    if (data.success) {
                        Toast.success(data.message);
                        self.state.selectedRooms = {};
                        self.toggleBulkMode();
                        self.refresh();
                    } else {
                        Toast.error(data.message || 'Gagal');
                    }
                } catch(e) { Toast.error('Response tidak valid'); }
            }
        };
        xhr.send(JSON.stringify({ room_ids: roomIds, status: 'maintenance' }));
    },

    bulkOutOfOrder: function() {
        var count = this._selectedCount();
        if (count === 0) return;
        if (!window.confirm('Set ' + count + ' kamar ke Out of Order?')) return;

        var self = this;
        var roomIds = [];
        for (var k in this.state.selectedRooms) {
            if (this.state.selectedRooms.hasOwnProperty(k)) roomIds.push(k);
        }

        var xhr = new XMLHttpRequest();
        xhr.open('PATCH', '/rooms/bulk-status', true);
        xhr.setRequestHeader('Content-Type', 'application/json');
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.setRequestHeader('Accept', 'application/json');
        var token = document.querySelector('meta[name="csrf-token"]');
        if (token) xhr.setRequestHeader('X-CSRF-TOKEN', token.getAttribute('content'));
        xhr.onload = function() {
            if (xhr.status >= 200 && xhr.status < 300) {
                try {
                    var data = JSON.parse(xhr.responseText);
                    if (data.success) {
                        Toast.success(data.message);
                        self.state.selectedRooms = {};
                        self.toggleBulkMode();
                        self.refresh();
                    } else {
                        Toast.error(data.message || 'Gagal');
                    }
                } catch(e) { Toast.error('Response tidak valid'); }
            }
        };
        xhr.send(JSON.stringify({ room_ids: roomIds, status: 'out_of_order' }));
    },

    _selectedCount: function() {
        var count = 0;
        for (var k in this.state.selectedRooms) { if (this.state.selectedRooms.hasOwnProperty(k)) count++; }
        return count;
    },

    // ========== KEYBOARD SHORTCUTS ==========
    bindKeyboardShortcuts: function() {
        var self = this;
        document.addEventListener('keydown', function(e) {
            if (e.target.matches('input, textarea, select')) return;
            if ((e.ctrlKey || e.metaKey) && e.key === 'r') {
                e.preventDefault();
                self.refresh();
                Toast.info('Refreshing...');
            }
            if ((e.ctrlKey || e.metaKey) && e.key === 'a') {
                e.preventDefault();
                self.toggleBulkMode();
            }
        });
    }
};

// Handle both initial page load and Turbo Drive navigation
function initRoomsDashboard() {
    var grid = document.getElementById('roomsGrid');
    if (grid && !grid.dataset.dashboardInitialized) {
        grid.dataset.dashboardInitialized = '1';
        RoomsDashboard.init();
    }
}
document.addEventListener('DOMContentLoaded', initRoomsDashboard);
document.addEventListener('turbo:load', initRoomsDashboard);

window.RoomsDashboard = RoomsDashboard;
