/**
 * SCForm — Other Revenue form helpers (didefinisikan global agar bisa dipanggil dari event inline)
 */
window.SCForm = {
    _calculate: function() {
        var amount = parseInt(document.getElementById('amount')?.value) || 0;
        var qty = parseInt(document.getElementById('quantity')?.value) || 0;
        var total = amount * qty;
        var el = document.getElementById('totalDisplay');
        if (el) el.textContent = 'Rp ' + total.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    },

    _autoFillGuest: function() {
        var el = document.getElementById('reservation_id');
        if (!el) return;
        var selected = el.options[el.selectedIndex];
        var guestId = selected ? selected.getAttribute('data-guest') : null;
        var guestEl = document.getElementById('guest_id');
        if (guestId && guestEl) {
            guestEl.value = guestId;
        }
    }
};

/**
 * Other Revenue Form Handler
 * Menangani form other revenue via modal
 */
var ServiceChargeForm = {
    open: function(url) {
        var overlay = document.getElementById('modalOverlay');
        var container = document.getElementById('modalContainer');
        var body = document.getElementById('modalBody');
        var content = document.getElementById('modalContent');

        if (!overlay || !container || !body) return;

        content.className = 'bg-white rounded-xl shadow-2xl w-full max-w-3xl max-h-[90vh] overflow-y-auto relative';

        overlay.classList.remove('hidden');
        container.classList.remove('hidden');
        body.innerHTML = '<div class="p-12 text-center"><div class="inline-block animate-spin rounded-full h-10 w-10 border-4 border-blue-500 border-t-transparent"></div><p class="mt-3 text-gray-500 text-sm">Memuat...</p></div>';

        var xhr = new XMLHttpRequest();
        xhr.open('GET', url, true);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.setRequestHeader('Accept', 'application/json');

        xhr.onload = function() {
            if (xhr.status >= 200 && xhr.status < 300) {
                var responseText = xhr.responseText;
                // Coba parse JSON (server returns JSON when expectsJson)
                try {
                    var json = JSON.parse(responseText);
                    if (json.success && json.view) {
                        responseText = json.view;
                    }
                } catch(e) {
                    // Not JSON, treat as raw HTML
                }
                var html = ServiceChargeForm._extractForm(responseText);
                body.innerHTML = html;
                ServiceChargeForm._init();
            } else {
                body.innerHTML = '<div class="p-8 text-center text-red-500"><i class="fas fa-exclamation-circle text-3xl"></i><p class="mt-2">Gagal memuat data</p></div>';
            }
        };

        xhr.onerror = function() {
            body.innerHTML = '<div class="p-8 text-center text-red-500"><i class="fas fa-exclamation-circle text-3xl"></i><p class="mt-2">Koneksi gagal</p></div>';
        };

        xhr.send();
    },

    _extractForm: function(html) {
        var parser = document.createElement('div');
        parser.innerHTML = html;

        // Cari form dengan id serviceChargeForm
        var form = parser.querySelector('#serviceChargeForm');
        if (form) {
            return form.outerHTML;
        }

        // Fallback: cari wrapper form
        var wrapper = parser.querySelector('.max-w-3xl');
        if (wrapper) {
            return wrapper.outerHTML;
        }

        return html;
    },

    _init: function() {
        // Init auto-select guest from reservation
        var reservation = document.getElementById('reservation_id');
        if (reservation) {
            reservation.addEventListener('change', function() {
                var selected = this.options[this.selectedIndex];
                var guestId = selected.getAttribute('data-guest');
                if (guestId) {
                    document.getElementById('guest_id').value = guestId;
                }
            });
        }

        // Auto-fill guest on load (pre-selected via reservation_id)
        SCForm._autoFillGuest();

        // Trigger initial calculation
        SCForm._calculate();

        // Init async forms
        if (typeof initAsyncForms === 'function') initAsyncForms();
    }
};
