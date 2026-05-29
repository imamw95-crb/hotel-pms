/**
 * ============================================
 * HOTEL PMS — Centralized Application JS
 * Modern SPA-like experience
 * ============================================
 */

// ========== CSRF SETUP ==========
var CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').getAttribute('content') : '';

// ========== TOAST NOTIFICATION SYSTEM ==========
var Toast = {
    container: null,

    init: function() {
        if (this.container) return;
        this.container = document.createElement('div');
        this.container.id = 'toast-container';
        this.container.className = 'fixed top-4 right-4 z-[200] space-y-2 max-w-sm';
        document.body.appendChild(this.container);
    },

    show: function(message, type, duration) {
        this.init();
        var t = type || 'success';
        var d = duration || 4000;
        var colors = { success: 'bg-emerald-500', error: 'bg-red-500', warning: 'bg-amber-500', info: 'bg-blue-500' };
        var icons = { success: 'fa-check-circle', error: 'fa-times-circle', warning: 'fa-exclamation-triangle', info: 'fa-info-circle' };

        var el = document.createElement('div');
        el.className = (colors[t] || colors.success) + ' text-white px-4 py-3 rounded-lg shadow-lg flex items-center gap-3';
        el.innerHTML = '<i class="fas ' + (icons[t] || icons.success) + '"></i><span class="text-sm font-medium">' + message + '</span>';
        this.container.appendChild(el);

        var self = this;
        setTimeout(function() { el.parentNode.removeChild(el); }, d);
    },

    success: function(msg) { this.show(msg, 'success'); },
    error: function(msg) { this.show(msg, 'error'); },
    warning: function(msg) { this.show(msg, 'warning'); },
    info: function(msg) { this.show(msg, 'info'); }
};

// ========== MODAL SYSTEM ==========
var Modal = {
    open: function(url, options) {
        var opts = options || {};
        var overlay = document.getElementById('modalOverlay');
        var container = document.getElementById('modalContainer');
        var body = document.getElementById('modalBody');
        var content = document.getElementById('modalContent');

        if (!overlay || !container || !body) return;

        var size = opts.size || 'max-w-4xl';
        content.className = 'bg-white rounded-xl shadow-2xl w-full ' + size + ' max-h-[90vh] overflow-y-auto relative';

        overlay.classList.remove('hidden');
        container.classList.remove('hidden');
        body.innerHTML = '<div class="p-12 text-center"><div class="inline-block animate-spin rounded-full h-10 w-10 border-4 border-blue-500 border-t-transparent"></div><p class="mt-3 text-gray-500 text-sm">Memuat...</p></div>';

        var xhr = new XMLHttpRequest();
        xhr.open('GET', url, true);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.setRequestHeader('Accept', 'application/json');
        xhr.onload = function() {
            if (xhr.status >= 200 && xhr.status < 300) {
                try {
                    var data = JSON.parse(xhr.responseText);
                    if (data.success && data.view) {
                        body.innerHTML = data.view;
                        // Execute ALL scripts (inline AND external with src)
                        Modal._executeScripts(body);
                        if (typeof initAsyncForms === 'function') initAsyncForms();
                    } else if (data.success) {
                        Modal.close();
                        if (data.message) Toast.success(data.message);
                        if (data.redirect_url) window.location.href = data.redirect_url;
                    } else {
                        body.innerHTML = '<div class="p-8 text-center text-red-500"><i class="fas fa-exclamation-circle text-3xl"></i><p class="mt-2">' + (data.message || 'Gagal memuat') + '</p></div>';
                    }
                } catch(e) {
                    body.innerHTML = '<div class="p-8 text-center text-red-500"><i class="fas fa-exclamation-circle text-3xl"></i><p class="mt-2">Response tidak valid</p></div>';
                }
            } else {
                body.innerHTML = '<div class="p-8 text-center text-red-500"><i class="fas fa-exclamation-circle text-3xl"></i><p class="mt-2">Gagal memuat data</p></div>';
            }
        };
        xhr.onerror = function() {
            body.innerHTML = '<div class="p-8 text-center text-red-500"><i class="fas fa-exclamation-circle text-3xl"></i><p class="mt-2">Koneksi gagal</p></div>';
        };
        xhr.send();
    },

    _executeScripts: function(container) {
        var scripts = container.querySelectorAll('script');
        var loadedCount = 0;
        var totalExternal = 0;

        for (var i = 0; i < scripts.length; i++) {
            var oldScript = scripts[i];
            var newScript = document.createElement('script');

            // Copy all attributes (like src, type, etc.)
            for (var j = 0; j < oldScript.attributes.length; j++) {
                var attr = oldScript.attributes[j];
                newScript.setAttribute(attr.name, attr.value);
            }

            // Copy inline code if any
            if (oldScript.textContent) {
                newScript.textContent = oldScript.textContent;
            }

            // If external script (has src), track loading
            if (newScript.src) {
                totalExternal++;
                (function(ns) {
                    ns.onload = function() {
                        loadedCount++;
                        if (loadedCount >= totalExternal && typeof initAsyncForms === 'function') {
                            initAsyncForms();
                        }
                    };
                    ns.onerror = function() {
                        loadedCount++;
                        console.warn('Failed to load script:', ns.src);
                    };
                })(newScript);
            }

            document.head.appendChild(newScript);
            document.head.removeChild(newScript);
        }
    },

    close: function() {
        var overlay = document.getElementById('modalOverlay');
        var container = document.getElementById('modalContainer');
        var body = document.getElementById('modalBody');
        if (overlay) overlay.classList.add('hidden');
        if (container) container.classList.add('hidden');
        if (body) body.innerHTML = '';
    }
};

// ========== AJAX FORM HANDLER ==========
var FormHandler = {
    submit(form, options) {
        var settings = options || {};
        var confirmMessage = settings.confirmMessage || null;
        var loadingText = settings.loadingText || 'Memproses...';
        var onSuccess = settings.onSuccess || null;
        var onError = settings.onError || null;

        // Confirmation
        if (confirmMessage) {
            if (!window.confirm(confirmMessage)) return;
        }

        // Check DELETE method
        var methodInput = form.querySelector('input[name="_method"]');
        if (methodInput && methodInput.value === 'DELETE' && !confirmMessage) {
            if (!window.confirm('Yakin ingin menghapus data ini?')) return;
        }

        var submitBtn = form.querySelector('button[type="submit"]') || form.querySelector('button');
        var originalText = submitBtn ? submitBtn.textContent : '';
        var originalClass = submitBtn ? submitBtn.className : '';

        // Loading state
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> ' + loadingText;
            submitBtn.classList.add('opacity-75');
        }

        // Clear previous errors
        var prevErrors = form.querySelectorAll('.error-message');
        for (var i = 0; i < prevErrors.length; i++) prevErrors[i].remove();
        var prevBorders = form.querySelectorAll('.border-red-500');
        for (var j = 0; j < prevBorders.length; j++) prevBorders[j].classList.remove('border-red-500');

        // Build form data
        var hasFile = false;
        var elements = form.elements;
        for (var k = 0; k < elements.length; k++) {
            if (elements[k].type === 'file') { hasFile = true; break; }
        }
        var formData;
        if (hasFile) {
            formData = new FormData(form);
        } else {
            formData = new FormData(form);
        }

        // Use XHR for maximum compatibility
        var xhr = new XMLHttpRequest();
        xhr.open(form.method.toUpperCase(), form.action, true);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.setRequestHeader('Accept', 'application/json');

        xhr.onload = function() {
            if (xhr.status >= 200 && xhr.status < 300) {
                try {
                    var data = JSON.parse(xhr.responseText);
                    if (data.success) {
                        if (data.message) Toast.success(data.message);
                        if (onSuccess) {
                            onSuccess(data);
                        } else if (data.redirect_url) {
                            window.location.href = data.redirect_url;
                        }
                    } else {
                        if (data.errors) {
                            FormHandler._showErrors(form, data.errors);
                            Toast.error('Silakan periksa form dan coba lagi');
                        } else if (data.message) {
                            Toast.error(data.message);
                        }
                        if (onError) onError(data);
                    }
                } catch(e) {
                    console.error('Parse error:', e);
                    Toast.error('Response tidak valid');
                }
            } else {
                Toast.error('Terjadi kesalahan server');
            }

            // Reset button
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
                submitBtn.className = originalClass;
            }
        };

        xhr.onerror = function() {
            Toast.error('Koneksi gagal. Silakan coba lagi.');
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
                submitBtn.className = originalClass;
            }
        };

        xhr.send(formData);
    },

    _showErrors(form, errors) {
        for (var field in errors) {
            if (!errors.hasOwnProperty(field)) continue;
            var input = form.querySelector('[name="' + field + '"]');
            if (input) {
                input.classList.add('border-red-500');
                var errDiv = document.createElement('div');
                errDiv.className = 'error-message text-red-500 text-sm mt-1';
                var msg = errors[field];
                errDiv.textContent = Array.isArray(msg) ? msg[0] : msg;
                input.parentNode.insertBefore(errDiv, input.nextSibling);
            }
        }
    }
};

// ========== DATA TABLE ==========
var DataTable = {
    init: function(tableId, options) {
        var table = document.getElementById(tableId);
        if (!table) return;
        var opts = options || {};
        var searchInput = opts.searchInput || null;
        var searchUrl = opts.searchUrl || null;
        var debounceMs = opts.debounceMs || 300;
        var self = this;
        var debounceTimer;

        if (searchInput && searchUrl) {
            var input = document.querySelector(searchInput);
            if (input) {
                input.addEventListener('input', function() {
                    clearTimeout(debounceTimer);
                    debounceTimer = setTimeout(function() {
                        self._search(searchUrl, input.value, table);
                    }, debounceMs);
                });
            }
        }

        table.addEventListener('click', function(e) {
            var link = e.target.closest('a[data-ajax-page]');
            if (link) {
                e.preventDefault();
                self._loadPage(link.href, table);
            }
        });
    },

    _search: function(url, query, table) {
        var tbody = table.querySelector('tbody');
        if (tbody) {
            tbody.innerHTML = '<tr><td colspan="100%" class="text-center py-8"><i class="fas fa-spinner fa-spin text-blue-500 text-2xl"></i></td></tr>';
        }
        var self = this;
        var xhr = new XMLHttpRequest();
        xhr.open('GET', url + '?search=' + encodeURIComponent(query), true);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.setRequestHeader('Accept', 'application/json');
        xhr.onload = function() {
            if (xhr.status >= 200 && xhr.status < 300) {
                try {
                    var data = JSON.parse(xhr.responseText);
                    if (data.html && tbody) tbody.innerHTML = data.html;
                } catch(e) { Toast.error('Gagal memuat data'); }
            }
        };
        xhr.send();
    },

    _loadPage: function(url, table) {
        var tbody = table.querySelector('tbody');
        if (tbody) {
            tbody.innerHTML = '<tr><td colspan="100%" class="text-center py-8"><i class="fas fa-spinner fa-spin text-blue-500 text-2xl"></i></td></tr>';
        }
        var xhr = new XMLHttpRequest();
        xhr.open('GET', url, true);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.setRequestHeader('Accept', 'application/json');
        xhr.onload = function() {
            if (xhr.status >= 200 && xhr.status < 300) {
                try {
                    var data = JSON.parse(xhr.responseText);
                    if (data.html) table.outerHTML = data.html;
                } catch(e) { Toast.error('Gagal memuat halaman'); }
            }
        };
        xhr.send();
    }
};

// ========== DELETE HANDLER ==========
function initDeleteForms() {
    var forms = document.querySelectorAll('form[data-ajax="true"]');
    for (var i = 0; i < forms.length; i++) {
        var form = forms[i];
        var methodInput = form.querySelector('input[name="_method"]');
        if (methodInput && methodInput.value === 'DELETE' && !form.dataset.deleteInit) {
            form.dataset.deleteInit = 'true';
            (function(f) {
                f.addEventListener('submit', function(e) {
                    e.preventDefault();
                    FormHandler.submit(f);
                });
            })(form);
        }
    }
}

// ========== THEME SYSTEM (Light / Dark / System) ==========
var DarkMode = {
    STORAGE_KEY: 'hotel_pms_theme',

    init: function() {
        var saved = localStorage.getItem(this.STORAGE_KEY) || 'system';
        this._apply(saved);
        this._updateUI(saved);
    },

    setTheme: function(theme) {
        localStorage.setItem(this.STORAGE_KEY, theme);
        this._apply(theme);
        this._updateUI(theme);
    },

    toggle: function() {
        var current = localStorage.getItem(this.STORAGE_KEY) || 'system';
        var next = current === 'dark' ? 'light' : (current === 'light' ? 'system' : 'dark');
        this.setTheme(next);
    },

    _apply: function(theme) {
        var html = document.documentElement;
        if (theme === 'dark') {
            html.classList.add('dark');
        } else if (theme === 'light') {
            html.classList.remove('dark');
        } else {
            // System
            var prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            if (prefersDark) html.classList.add('dark');
            else html.classList.remove('dark');
        }
    },

    _updateUI: function(theme) {
        // Update header toggle button
        var btn = document.getElementById('darkModeToggle');
        if (btn) {
            var icons = { light: 'fa-sun', dark: 'fa-moon', system: 'fa-desktop' };
            var titles = { light: 'Tema: Terang', dark: 'Tema: Gelap', system: 'Tema: Sistem' };
            btn.innerHTML = '<i class="fas ' + (icons[theme] || 'fa-desktop') + '"></i>';
            btn.title = titles[theme] || 'Tema';
        }

        // Update dropdown items
        document.querySelectorAll('.theme-option').forEach(function(el) {
            var isActive = el.getAttribute('data-theme') === theme;
            if (isActive) {
                el.classList.add('bg-blue-50', 'text-blue-700');
                el.classList.remove('text-gray-700');
                el.querySelector('.theme-check')?.classList.remove('hidden');
            } else {
                el.classList.remove('bg-blue-50', 'text-blue-700');
                el.classList.add('text-gray-700');
                el.querySelector('.theme-check')?.classList.add('hidden');
            }
        });
    }
};

// ========== KEYBOARD SHORTCUTS ==========
var KeyboardShortcuts = {
    init: function() {
        var self = this;
        document.addEventListener('keydown', function(e) {
            if (e.target.matches('input, textarea, select')) return;
            if ((e.ctrlKey || e.metaKey) && e.key === 'b') {
                e.preventDefault();
                var url = document.querySelector('[data-booking-url]');
                if (url) Modal.open(url.dataset.bookingUrl);
            }
            if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                e.preventDefault();
                var s = document.querySelector('input[type="search"], input[name="search"]');
                if (s) s.focus();
            }
            if ((e.ctrlKey || e.metaKey) && e.key === '\\') {
                e.preventDefault();
                if (typeof toggleSidebar === 'function') toggleSidebar();
            }
            if (e.key === '?' && !e.ctrlKey && !e.metaKey) {
                e.preventDefault();
                self._showHelp();
            }
        });
    },
    _showHelp: function() {
        var existing = document.getElementById('shortcutsModal');
        if (existing) { existing.remove(); return; }
        var modal = document.createElement('div');
        modal.id = 'shortcutsModal';
        modal.className = 'fixed inset-0 z-[200] flex items-center justify-center';
        modal.innerHTML = '<div class="absolute inset-0 bg-black/40" onclick="this.parentElement.remove()"></div>' +
            '<div class="bg-white rounded-xl shadow-2xl p-6 w-96 relative z-10">' +
            '<div class="flex items-center justify-between mb-4"><h3 class="text-lg font-bold"><i class="fas fa-keyboard mr-2"></i>Shortcuts</h3><button onclick="this.closest(\'#shortcutsModal\').remove()" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button></div>' +
            '<div class="space-y-3 text-sm">' +
            '<div class="flex justify-between"><span>New Booking</span><kbd class="px-2 py-0.5 bg-gray-100 rounded text-xs font-mono">Ctrl+B</kbd></div>' +
            '<div class="flex justify-between"><span>Toggle Sidebar</span><kbd class="px-2 py-0.5 bg-gray-100 rounded text-xs font-mono">Ctrl+\</kbd></div>' +
            '<div class="flex justify-between"><span>Search</span><kbd class="px-2 py-0.5 bg-gray-100 rounded text-xs font-mono">Ctrl+K</kbd></div>' +
            '<div class="flex justify-between"><span>Close Modal</span><kbd class="px-2 py-0.5 bg-gray-100 rounded text-xs font-mono">Esc</kbd></div>' +
            '<div class="flex justify-between"><span>This Help</span><kbd class="px-2 py-0.5 bg-gray-100 rounded text-xs font-mono">?</kbd></div>' +
            '</div></div>';
        document.body.appendChild(modal);
    }
};

// ========== MENU MODAL TRIGGERS ==========
function initMenuModalTriggers() {
    document.addEventListener('click', function(e) {
        var trigger = e.target.closest('.menu-modal-trigger');
        if (trigger) {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
            var url = trigger.getAttribute('data-modal-url');
            if (url && typeof Modal !== 'undefined' && Modal.open) {
                Modal.open(url);
            }
            return false;
        }
    });
}

// ========== TOGGLE SUBMENU ==========
function toggleSubmenu(itemId) {
    var li = document.getElementById(itemId);
    if (!li) return;
    li.classList.toggle('open');
}

// ========== GLOBAL INIT ==========
document.addEventListener('DOMContentLoaded', function() {
    initDeleteForms();
    DarkMode.init();
    KeyboardShortcuts.init();
    initMenuModalTriggers();
    var overlay = document.getElementById('modalOverlay');
    if (overlay) overlay.addEventListener('click', function() { Modal.close(); });

    // Close theme dropdown on outside click
    document.addEventListener('click', function(e) {
        var wrapper = document.getElementById('themeDropdownWrapper');
        var dropdown = document.getElementById('themeDropdown');
        if (wrapper && dropdown && !wrapper.contains(e.target)) {
            dropdown.classList.add('hidden');
        }
    });
});

// ========== EXPORTS ==========
window.Toast = Toast;
window.Modal = Modal;
window.FormHandler = FormHandler;
window.DataTable = DataTable;
window.DarkMode = DarkMode;
window.openModal = function(url, opts) { Modal.open(url, opts || {}); };
window.closeModal = function() { Modal.close(); };