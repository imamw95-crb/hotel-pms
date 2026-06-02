/**
 * Rooms Form Handler
 * Menangani form tambah/edit kamar via modal dengan tampilan bersih
 * Mengekstrak hanya form dari layout lengkap (tanpa sidebar/header)
 */

var RoomsForm = {
    open: function(url) {
        var overlay = document.getElementById('modalOverlay');
        var container = document.getElementById('modalContainer');
        var body = document.getElementById('modalBody');
        var content = document.getElementById('modalContent');

        if (!overlay || !container || !body) return;

        content.className = 'bg-white rounded-xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto relative p-2';

        overlay.classList.remove('hidden');
        container.classList.remove('hidden');
        body.innerHTML = '<div class="p-12 text-center"><div class="inline-block animate-spin rounded-full h-10 w-10 border-4 border-blue-500 border-t-transparent"></div><p class="mt-3 text-gray-500 text-sm">Memuat...</p></div>';

        var xhr = new XMLHttpRequest();
        xhr.open('GET', url, true);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

        xhr.onload = function() {
            if (xhr.status >= 200 && xhr.status < 300) {
                var html = RoomsForm._extractForm(xhr.responseText);
                body.innerHTML = html;
                RoomsForm._init();
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
        var parser = document.createElement('div');
        parser.innerHTML = html;

        // Cari form dengan data-ajax="true" (form create/edit rooms)
        var form = parser.querySelector('form[data-ajax="true"]');
        if (form) {
            return form.outerHTML;
        }

        // Fallback: cari div wrapper bg-white rounded-lg shadow
        var wrapper = parser.querySelector('.bg-white.rounded-lg.shadow');
        if (wrapper) {
            return wrapper.outerHTML;
        }

        // Fallback: gunakan seluruh konten
        return html;
    },

    _init: function() {
        // Init form submission handler
        var form = document.querySelector('form[data-ajax="true"]');
        if (form) {
            RoomsForm._bindForm(form);
        }

        // Init number formatting for price fields
        document.querySelectorAll('input[type="number"]').forEach(function(input) {
            input.addEventListener('blur', function() {
                if (this.value && parseInt(this.value) > 0) {
                    this.value = parseInt(this.value);
                }
            });
        });

        // Sync max_occupancy with room type if available
        var roomTypeSelect = document.getElementById('room_type_id');
        if (roomTypeSelect && roomTypeSelect.options.length > 0) {
            // Check if there's a data-capacity attribute on options
            roomTypeSelect.addEventListener('change', function() {
                var selected = this.options[this.selectedIndex];
                var capacity = selected.getAttribute('data-capacity');
                var maxOccInput = document.getElementById('max_occupancy');
                if (capacity && maxOccInput && !maxOccInput.value) {
                    maxOccInput.value = capacity;
                }
            });
        }

        // Init async forms
        if (typeof initAsyncForms === 'function') initAsyncForms();
    },

    _bindForm: function(form) {
        // Remove existing handler to prevent duplicates
        form.removeEventListener('submit', RoomsForm._submitHandler);
        form.addEventListener('submit', RoomsForm._submitHandler);
    },

    _submitHandler: function(e) {
        e.preventDefault();

        var form = this;
        var submitBtn = form.querySelector('button[type="submit"]');
        var originalText = submitBtn.innerHTML;

        // Disable button
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...';

        var formData = new FormData(form);

        var xhr = new XMLHttpRequest();
        xhr.open(form.method, form.action, true);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.setRequestHeader('Accept', 'application/json');

        // If not PUT/PATCH/DELETE, include CSRF
        if (form.method.toUpperCase() !== 'GET') {
            var csrfToken = form.querySelector('input[name="_token"]');
            if (csrfToken) {
                formData.set('_token', csrfToken.value);
            }
        }

        xhr.onload = function() {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;

            var data;
            try {
                data = JSON.parse(xhr.responseText);
            } catch (e) {
                data = null;
            }

            if (xhr.status >= 200 && xhr.status < 300) {
                // Success
                if (data && data.message) {
                    if (typeof showToast === 'function') {
                        showToast(data.message, 'success');
                    } else {
                        alert(data.message);
                    }
                }

                // Refresh if data-refresh attribute exists
                if (form.getAttribute('data-refresh') === 'true') {
                    if (typeof window.location.reload === 'function') {
                        window.location.reload();
                    }
                }

                // Close modal
                if (typeof Modal !== 'undefined' && Modal.close) {
                    Modal.close();
                } else if (typeof closeModal === 'function') {
                    closeModal();
                }

                // Trigger custom event for other components
                document.dispatchEvent(new CustomEvent('room:saved', { detail: data }));
            } else {
                // Validation error
                if (data && data.errors) {
                    RoomsForm._showErrors(data.errors);
                } else if (data && data.message) {
                    if (typeof showToast === 'function') {
                        showToast(data.message, 'error');
                    } else {
                        alert('Error: ' + data.message);
                    }
                } else {
                    if (typeof showToast === 'function') {
                        showToast('Terjadi kesalahan saat menyimpan', 'error');
                    } else {
                        alert('Terjadi kesalahan saat menyimpan');
                    }
                }
            }
        };

        xhr.onerror = function() {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
            if (typeof showToast === 'function') {
                showToast('Koneksi gagal', 'error');
            } else {
                alert('Koneksi gagal');
            }
        };

        xhr.send(formData);
    },

    _showErrors: function(errors) {
        // Clear existing error messages
        document.querySelectorAll('.text-red-500.text-sm').forEach(function(el) {
            // Only clear dynamically added errors, keep original server-side ones
            if (el.getAttribute('data-dynamic') === 'true') {
                el.remove();
            }
        });
        document.querySelectorAll('.border-red-500').forEach(function(el) {
            el.classList.remove('border-red-500');
        });

        // Show new errors
        for (var field in errors) {
            if (errors.hasOwnProperty(field)) {
                var input = document.querySelector('[name="' + field + '"]');
                if (input) {
                    input.classList.add('border-red-500');

                    var errorMsg = document.createElement('span');
                    errorMsg.className = 'text-red-500 text-sm mt-1 block';
                    errorMsg.setAttribute('data-dynamic', 'true');
                    errorMsg.textContent = errors[field][0];

                    // Insert after the input's parent container
                    var parent = input.closest('.mb-4') || input.parentElement;
                    parent.appendChild(errorMsg);
                }
            }
        }

        // Scroll to first error
        var firstError = document.querySelector('.border-red-500');
        if (firstError) {
            firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            firstError.focus({ preventScroll: true });
        }
    },

    /**
     * Format angka ke format Rupiah (contoh: Rp 150.000)
     */
    formatRupiah: function(num) {
        return 'Rp ' + parseInt(num).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    },

    /**
     * Hitung nilai dari string Rupiah kembali ke angka
     */
    unformatRupiah: function(str) {
        if (!str) return 0;
        return parseInt(str.replace(/[^0-9]/g, '')) || 0;
    }
};

window.RoomsForm = RoomsForm;