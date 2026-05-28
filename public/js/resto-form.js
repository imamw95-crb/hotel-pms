/**
 * Resto Form Handler
 * Menangani form transaksi resto via modal dengan tampilan bersih
 * Mengekstrak hanya form dari layout lengkap (tanpa sidebar/header)
 */

var RestoForm = {
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

        xhr.onload = function() {
            if (xhr.status >= 200 && xhr.status < 300) {
                var html = RestoForm._extractForm(xhr.responseText);
                body.innerHTML = html;
                RestoForm._init();
            } else {
                body.innerHTML = '<div class="p-8 text-center text-red-500"><i class="fas fa-exclamation-circle text-3xl"></i><p class="mt-2">Gagal memuat data</p></div>';
            }
        };

        xhr.onerror = function() {
            body.innerHTML = '<div class="p-8 text-center text-red-500"><i class="fas fa-exclamation-circle text-3xl"></i><p class="mt-2">Koneksi gagal</p></div>';
        };

        xhr.send();
    },

    /**
     * Ekstrak hanya konten form dari halaman HTML lengkap
     * (buang layout: sidebar, header, login bar, dll)
     */
    _extractForm: function(html) {
        // Cari form di dalam page-content
        var parser = document.createElement('div');
        parser.innerHTML = html;

        // Cari elemen form dengan id restoForm
        var form = parser.querySelector('#restoForm');
        if (form) {
            return form.outerHTML;
        }

        // Fallback: cari div dengan class max-w-3xl (wrapper form)
        var wrapper = parser.querySelector('.max-w-3xl');
        if (wrapper) {
            return wrapper.outerHTML;
        }

        // Fallback: gunakan seluruh konten
        return html;
    },

    _init: function() {
        // Init first item
        var container = document.getElementById('itemsContainer');
        if (container && container.children.length === 0) {
            RestoForm._addItem();
        }

        // Auto-select guest
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

        // Init async forms
        if (typeof initAsyncForms === 'function') initAsyncForms();
    },

    _addItem: function() {
        var container = document.getElementById('itemsContainer');
        if (!container) return;

        var index = container.children.length;
        var row = document.createElement('div');
        row.className = 'item-row flex gap-3 items-start';
        row.innerHTML =
            '<div class="flex-1">' +
                '<input type="text" name="items[' + index + '][name]" placeholder="Nama item (contoh: Nasi Goreng)"' +
                       ' class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none" required>' +
            '</div>' +
            '<div class="w-20">' +
                '<input type="number" name="items[' + index + '][qty]" placeholder="Qty" min="1" value="1"' +
                       ' class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm text-center focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none" required oninput="RestoForm._calculate()">' +
            '</div>' +
            '<div class="w-32">' +
                '<input type="number" name="items[' + index + '][price]" placeholder="Harga" min="0" step="500"' +
                       ' class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm text-right focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none" required oninput="RestoForm._calculate()">' +
            '</div>' +
            '<div class="w-28 text-right py-2 text-sm font-semibold item-subtotal">Rp 0</div>' +
            '<button type="button" onclick="RestoForm._remove(this)" class="text-red-500 hover:text-red-700 py-2 px-1">' +
                '<i class="fas fa-trash"></i>' +
            '</button>';
        container.appendChild(row);
    },

    _remove: function(btn) {
        btn.closest('.item-row').remove();
        RestoForm._calculate();
    },

    addItem: function() {
        RestoForm._addItem();
    },

    _calculate: function() {
        var subtotal = 0;
        document.querySelectorAll('.item-row').forEach(function(row) {
            var qty = parseInt(row.querySelector('input[name*="[qty]"]').value) || 0;
            var price = parseInt(row.querySelector('input[name*="[price]"]').value) || 0;
            var itemSubtotal = qty * price;
            subtotal += itemSubtotal;
            row.querySelector('.item-subtotal').textContent = RestoForm._formatRupiah(itemSubtotal);
        });

        var tax = parseInt(document.getElementById('tax').value) || 0;
        var discount = parseInt(document.getElementById('discount').value) || 0;
        var total = subtotal + tax - discount;

        document.getElementById('subtotalDisplay').textContent = RestoForm._formatRupiah(subtotal);
        document.getElementById('taxDisplay').textContent = RestoForm._formatRupiah(tax);
        document.getElementById('discountDisplay').textContent = RestoForm._formatRupiah(discount);
        document.getElementById('totalDisplay').textContent = RestoForm._formatRupiah(total);
    },

    _formatRupiah: function(num) {
        return 'Rp ' + parseInt(num).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }
};

window.RestoForm = RestoForm;