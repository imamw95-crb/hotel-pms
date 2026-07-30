@php $hotelSetting = \App\Models\HotelSetting::first(); @endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', $hotelSetting->hotel_name) - {{ $hotelSetting->hotel_name }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="{{ asset('assets/fontawesome/css/all.min.css') }}" rel="stylesheet">
    <script src="{{ asset('assets/chartjs/chart.js') }}"></script>
    <script type="module" src="{{ asset('assets/turbo/turbo.esm.js') }}"></script>
    <script nomodule>
        // Fallback: Turbo tidak support browser ini (halaman tetap jalan normal)
    </script>
</head>
<body class="bg-slate-50">

    <!-- Mobile Overlay -->
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()" data-turbo-permanent></div>

    <!-- ===== APP LAYOUT ===== -->
    <div id="app-layout">

        <!-- SIDEBAR (fixed left) -->
        <aside class="app-sidebar" id="appSidebar">
            <div class="sidebar-brand">
                <div class="sidebar-brand-icon">
                    @if($hotelSetting->logo_path)
                        <img src="{{ asset('storage/' . $hotelSetting->logo_path) }}" alt="Logo" class="h-8 w-auto object-contain">
                    @else
                        <i class="fas fa-hotel text-white text-xl"></i>
                    @endif
                </div>
                <div class="sidebar-brand-title">{{ $hotelSetting->hotel_name }}</div>
                <div class="sidebar-brand-subtitle">{{ auth()->user()->role === 'user_manager' ? 'Manager' : ucfirst(auth()->user()->role ?? 'Guest') }}</div>
            </div>
            <div class="sidebar-scroll">
                <x-menu />
            </div>
        </aside>

        <!-- SPACER (menggantikan lebar sidebar, mendorong konten) -->
        <div class="sidebar-spacer"></div>

        <!-- MAIN CONTENT -->
        <div class="main-wrapper">

            <!-- TOP HEADER -->
            <header class="app-header" data-turbo-permanent>
                <div class="flex items-center gap-3">
                    <button class="text-slate-500 hover:text-slate-700 text-lg p-1 rounded-lg hover:bg-slate-100 transition" id="sidebarToggle" onclick="toggleSidebar()">
                        <i class="fas fa-bars"></i>
                    </button>
                    <nav class="text-sm text-slate-500 flex-1 min-w-0">
                        <span class="font-semibold text-slate-800">@yield('header', 'Dashboard')</span>
                    </nav>
                </div>
                <div class="flex items-center gap-3">
                    {{-- Theme Dropdown --}}
                    <div class="relative" id="themeDropdownWrapper">
                        <button onclick="document.getElementById('themeDropdown').classList.toggle('hidden')" class="w-8 h-8 rounded-lg hover:bg-slate-100 flex items-center justify-center text-slate-500 hover:text-slate-700 transition" title="Pilih Tema">
                            <i class="fas fa-palette"></i>
                        </button>
                        <div id="themeDropdown" class="hidden absolute right-0 top-full mt-2 w-52 bg-white rounded-xl shadow-xl border border-gray-100 py-2 z-[150]">
                            <div class="px-3 py-1.5 text-xs font-semibold text-gray-400 uppercase tracking-wider">Tema</div>
                            <button onclick="DarkMode.setTheme('light'); document.getElementById('themeDropdown').classList.add('hidden')" data-theme="light" class="theme-option w-full flex items-center gap-3 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 transition">
                                <span class="w-8 h-8 rounded-lg bg-yellow-50 text-yellow-500 flex items-center justify-center"><i class="fas fa-sun"></i></span>
                                <span class="flex-1 text-left font-medium">Terang</span>
                                <i class="fas fa-check text-blue-600 theme-check hidden"></i>
                            </button>
                            <button onclick="DarkMode.setTheme('dark'); document.getElementById('themeDropdown').classList.add('hidden')" data-theme="dark" class="theme-option w-full flex items-center gap-3 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 transition">
                                <span class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-500 flex items-center justify-center"><i class="fas fa-moon"></i></span>
                                <span class="flex-1 text-left font-medium">Gelap</span>
                                <i class="fas fa-check text-blue-600 theme-check hidden"></i>
                            </button>
                            <button onclick="DarkMode.setTheme('system'); document.getElementById('themeDropdown').classList.add('hidden')" data-theme="system" class="theme-option w-full flex items-center gap-3 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 transition">
                                <span class="w-8 h-8 rounded-lg bg-gray-100 text-gray-500 flex items-center justify-center"><i class="fas fa-desktop"></i></span>
                                <span class="flex-1 text-left font-medium">Sistem</span>
                                <i class="fas fa-check text-blue-600 theme-check hidden"></i>
                            </button>
                        </div>
                    </div>
                    <button id="darkModeToggle" onclick="DarkMode.toggle()" class="w-8 h-8 rounded-lg hover:bg-slate-100 flex items-center justify-center text-slate-500 hover:text-slate-700 transition" title="Toggle Tema">
                        <i class="fas fa-moon"></i>
                    </button>
                    <button onclick="KeyboardShortcuts._showHelp()" class="w-8 h-8 rounded-lg hover:bg-slate-100 flex items-center justify-center text-slate-500 hover:text-slate-700 transition" title="Keyboard Shortcuts (?)">
                        <i class="fas fa-keyboard"></i>
                    </button>

                    {{-- Booking Notification Bell --}}
                    <div class="relative" id="notificationBellWrapper" data-turbo-permanent>
                        <button id="notificationBellBtn" onclick="BookingNotifications.toggle()"
                            class="relative w-8 h-8 rounded-lg hover:bg-slate-100 flex items-center justify-center text-slate-500 hover:text-slate-700 transition"
                            title="Notifikasi Booking Baru">
                            <i class="fas fa-bell"></i>
                            <span id="notificationBadge" class="hidden absolute -top-0.5 -right-0.5 w-4 h-4 bg-red-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center animate-pulse">0</span>
                        </button>

                        {{-- Notification Dropdown --}}
                        <div id="notificationDropdown" class="hidden absolute right-0 top-full mt-2 w-96 bg-white rounded-xl shadow-xl border border-gray-100 z-[200] overflow-hidden">
                            <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100 bg-gray-50">
                                <h3 class="text-sm font-semibold text-gray-800">Notifikasi Booking</h3>
                                <div class="flex items-center gap-2">
                                    <button onclick="BookingNotifications.markAllRead()" class="text-xs text-blue-600 hover:text-blue-800 font-medium">
                                        <i class="fas fa-check-double mr-1"></i>Baca Semua
                                    </button>
                                </div>
                            </div>
                            <div id="notificationList" class="max-h-96 overflow-y-auto">
                                <div class="px-4 py-8 text-center text-gray-400 text-sm">
                                    <i class="fas fa-bell-slash text-2xl mb-2 block"></i>
                                    Tidak ada notifikasi
                                </div>
                            </div>
                            <div class="border-t border-gray-100 px-4 py-2 bg-gray-50 text-center">
                                <a href="{{ route('reservations.index') }}" class="text-xs text-blue-600 hover:text-blue-800 font-medium">
                                    <i class="fas fa-external-link-alt mr-1"></i>Lihat Semua Reservasi
                                </a>
                            </div>
                        </div>
                    </div>

                    {{-- Night Audit v2 Notification --}}
                    @if($nightAuditPending)
                        <a href="{{ route('reports.night-audit-v2.index') }}"
                           class="flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold
                                  bg-amber-100 text-amber-700 hover:bg-amber-200 transition
                                  dark:bg-amber-900/40 dark:text-amber-300 dark:hover:bg-amber-900/60"
                           title="Night Audit v2 hari ini belum di-lock">
                            <i class="fas fa-moon"></i>
                            <span>Night Audit</span>
                            <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                        </a>
                    @endif

                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white text-xs font-bold">
                            {{ substr(auth()->user()->name ?? 'U', 0, 1) }}
                        </div>
                        <span class="text-sm font-medium text-slate-700">{{ auth()->user()->name ?? 'User' }}</span>
                    </div>
                    <div class="w-px h-6 bg-slate-200"></div>
                    <form method="POST" action="{{ route('logout') }}" class="inline" data-turbo="false">
                        @csrf
                        <button type="submit" class="text-slate-400 hover:text-red-500 text-sm transition flex items-center gap-1.5">
                            <i class="fas fa-sign-out-alt"></i>
                            <span class="hidden sm:inline">Logout</span>
                        </button>
                    </form>
                </div>
            </header>

            <!-- PAGE CONTENT -->
            <div class="page-content">
                @if(session('success'))
                    <div class="alert-success">
                        <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
                    </div>
                @endif
                @if(session('error'))
                    <div class="alert-error">
                        <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
                    </div>
                @endif
                @yield('content')
            </div>

        </div>
    </div>

    <!-- Sidebar Toggle -->
    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('appSidebar');
            const spacer = document.querySelector('.sidebar-spacer');
            const overlay = document.getElementById('sidebarOverlay');
            const isMobile = window.innerWidth <= 768;

            if (isMobile) {
                sidebar.classList.toggle('open');
                overlay.classList.toggle('show');
            } else {
                sidebar.classList.toggle('collapsed');
                if (spacer) spacer.classList.toggle('collapsed');
            }
        }
    </script>

    <!-- Core App JS -->
    <script src="{{ asset('js/app.js') }}"></script>
    <script src="{{ asset('js/async-form.js') }}"></script>
    <script src="{{ asset('js/deposit.js') }}"></script>
    <script src="{{ asset('js/resto-form.js') }}"></script>
    <script src="{{ asset('js/service-charge-form.js') }}"></script>
    <script src="{{ asset('js/rooms-form.js') }}"></script>
    <script>
        window._depositIndexUrl = '{{ route('deposits.index') }}';
        window._depositCreateUrl = '{{ route('deposits.create') }}';
        window._depositReturnUrlTemplate = '{{ route('deposits.return', '__ID__') }}';
    </script>

    <!-- Modal Container -->
    <div id="modalOverlay" class="fixed inset-0 bg-black/50 z-[100] hidden" data-turbo-permanent></div>
    <div id="modalContainer" class="fixed inset-0 z-[101] hidden flex items-center justify-center p-4" data-turbo-permanent>
        <div id="modalContent" class="bg-white rounded-xl shadow-2xl w-full max-w-4xl max-h-[90vh] overflow-y-auto relative">
            <button onclick="Modal.close()" class="absolute top-3 right-3 text-gray-400 hover:text-gray-700 text-xl z-10 w-8 h-8 flex items-center justify-center rounded-full hover:bg-gray-100 transition">
                <i class="fas fa-times"></i>
            </button>
            <div id="modalBody"></div>
        </div>
    </div>

    {{-- AI Chat Widget --}}
    <div data-turbo-permanent>
        @include('components.ai-chat-widget')
    </div>

    <!-- Turbo Drive Events -->
    <script>
        document.addEventListener('turbo:before-render', () => {
            window.scrollTo({ top: 0, behavior: 'instant' });
        });

        document.addEventListener('turbo:render', () => {
            document.body.classList.add('turbo-fade-in');
            setTimeout(function() {
                document.body.classList.remove('turbo-fade-in');
            }, 300);
        });

        document.addEventListener('turbo:load', () => {
            document.querySelectorAll('.menu-item.has-submenu.active').forEach(function(el) {
                el.classList.add('open');
            });
            if (typeof initAsyncForms === 'function') {
                initAsyncForms();
            }
            document.querySelectorAll('[onclick*="Modal.open"]').forEach(function(el) {
                if (el.dataset.turbo !== 'false') {
                    el.setAttribute('data-turbo', 'false');
                }
            });
        });
    </script>

    @yield('scripts')

    {{-- Play notification sound using Web Audio API --}}
    <script>
        function playNotificationSound() {
            try {
                var ctx = new (window.AudioContext || window.webkitAudioContext)();
                var now = ctx.currentTime;

                // First tone (C5 - higher pitch)
                var osc1 = ctx.createOscillator();
                var gain1 = ctx.createGain();
                osc1.type = 'sine';
                osc1.frequency.value = 523.25;
                gain1.gain.setValueAtTime(0.3, now);
                gain1.gain.exponentialRampToValueAtTime(0.01, now + 0.25);
                osc1.connect(gain1);
                gain1.connect(ctx.destination);
                osc1.start(now);
                osc1.stop(now + 0.25);

                // Second tone (E5 - higher, pleasant chime)
                var osc2 = ctx.createOscillator();
                var gain2 = ctx.createGain();
                osc2.type = 'sine';
                osc2.frequency.value = 659.25;
                gain2.gain.setValueAtTime(0.01, now);
                gain2.gain.setValueAtTime(0.3, now + 0.1);
                gain2.gain.exponentialRampToValueAtTime(0.01, now + 0.4);
                osc2.connect(gain2);
                gain2.connect(ctx.destination);
                osc2.start(now + 0.1);
                osc2.stop(now + 0.4);

                // Auto-resume if suspended (needed for modern browsers)
                if (ctx.state === 'suspended') ctx.resume();
            } catch(e) {
                // Audio not supported - silently ignore
            }
        }
    </script>

    {{-- Booking Notification Polling --}}
    <script data-turbo-permanent>
        window.BookingNotifications = {
            pollingInterval: null,
            isOpen: false,
            unreadCount: 0,

            init: function() {
                this.poll();
                this.pollingInterval = setInterval(() => this.poll(), 15000); // every 15s

                // Close dropdown when clicking outside
                document.addEventListener('click', (e) => {
                    const wrapper = document.getElementById('notificationBellWrapper');
                    if (wrapper && !wrapper.contains(e.target) && this.isOpen) {
                        this.close();
                    }
                });
            },

            prevCount: 0,
            initialized: false,

            poll: function() {
                var self = this;
                fetch('{{ route('notifications.index') }}')
                    .then(function(r) { return r.json(); })
                    .then(function(data) {
                        var newCount = data.unread_count || 0;

                        // First poll: just record the count, don't trigger
                        if (!self.initialized) {
                            self.initialized = true;
                            self.prevCount = newCount;
                            self.unreadCount = newCount;
                            self.updateBadge();
                            return;
                        }

                        var hasNew = newCount > self.prevCount;
                        self.prevCount = newCount;
                        self.unreadCount = newCount;
                        self.updateBadge();

                        // Play sound only on actual new notification
                        if (hasNew) {
                            playNotificationSound();
                        }

                        // Show banner ONCE on new notif, auto-hide on next poll
                        if (typeof AiChat !== 'undefined') {
                            if (hasNew && data.notifications && data.notifications.length > 0) {
                                AiChat.showNotification(data.notifications[0]);
                            } else {
                                AiChat.hideNotification();
                            }
                        }
                    })
                    .catch(function() {});
            },

            toggle: function() {
                if (this.isOpen) {
                    this.close();
                } else {
                    this.open();
                }
            },

            open: function() {
                this.isOpen = true;
                document.getElementById('notificationDropdown').classList.remove('hidden');
                this.loadNotifications();
            },

            close: function() {
                this.isOpen = false;
                document.getElementById('notificationDropdown').classList.add('hidden');
            },

            loadNotifications: function() {
                const list = document.getElementById('notificationList');
                list.innerHTML = '<div class="px-4 py-8 text-center text-gray-400 text-sm"><i class="fas fa-spinner fa-spin text-2xl mb-2 block"></i>Memuat...</div>';

                fetch('{{ route('notifications.index') }}')
                    .then(r => r.json())
                    .then(data => {
                        this.unreadCount = data.unread_count || 0;
                        this.updateBadge();
                        this.renderNotifications(data.notifications || []);
                    })
                    .catch(() => {
                        list.innerHTML = '<div class="px-4 py-8 text-center text-red-400 text-sm"><i class="fas fa-exclamation-triangle text-2xl mb-2 block"></i>Gagal memuat</div>';
                    });
            },

            renderNotifications: function(notifications) {
                const list = document.getElementById('notificationList');
                if (notifications.length === 0) {
                    list.innerHTML = '<div class="px-4 py-8 text-center text-gray-400 text-sm"><i class="fas fa-bell-slash text-2xl mb-2 block"></i>Tidak ada notifikasi</div>';
                    return;
                }

                list.innerHTML = notifications.map(n => {
                    const isUnread = !n.is_read;
                    const timeAgo = this.timeAgo(n.created_at);
                    const detailUrl = '{{ route('reservations.show', '__ID__') }}'.replace('__ID__', n.reservation_id || 0);

                    let icon = 'fa-bell';
                    let color = 'text-blue-500 bg-blue-50';
                    if (n.action === 'cancelled') {
                        icon = 'fa-times-circle';
                        color = 'text-red-500 bg-red-50';
                    } else if (n.type === 'ota_booking') {
                        icon = 'fa-globe';
                        color = 'text-teal-500 bg-teal-50';
                    }

                    return `
                        <div class="flex items-start gap-3 px-4 py-3 ${isUnread ? 'bg-blue-50/50' : ''} hover:bg-gray-50 border-b border-gray-50 transition cursor-pointer"
                             onclick="BookingNotifications.markRead(${n.id}); window.location='${detailUrl}'">
                            <div class="w-8 h-8 rounded-full ${color} flex items-center justify-center flex-shrink-0">
                                <i class="fas ${icon} text-xs"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm ${isUnread ? 'font-semibold text-gray-900' : 'text-gray-600'} line-clamp-2">${this.escapeHtml(n.message)}</p>
                                <p class="text-xs text-gray-400 mt-1">${timeAgo}</p>
                            </div>
                            ${isUnread ? '<span class="w-2 h-2 rounded-full bg-blue-500 flex-shrink-0 mt-2"></span>' : ''}
                        </div>
                    `;
                }).join('');
            },

            updateBadge: function() {
                const badge = document.getElementById('notificationBadge');
                if (!badge) return;
                if (this.unreadCount > 0) {
                    badge.classList.remove('hidden');
                    badge.textContent = this.unreadCount > 99 ? '99+' : this.unreadCount;
                } else {
                    badge.classList.add('hidden');
                }
            },

            markRead: function(id) {
                fetch('{{ route('notifications.mark-read', '__ID__') }}'.replace('__ID__', id), {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
                }).catch(() => {});
                this.unreadCount = Math.max(0, this.unreadCount - 1);
                this.updateBadge();
                if (this.unreadCount === 0 && typeof AiChat !== 'undefined' && AiChat.hideNotification) {
                    AiChat.hideNotification();
                }
            },

            markAllRead: function() {
                fetch('{{ route('notifications.mark-all-read') }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
                }).then(() => {
                    this.unreadCount = 0;
                    this.updateBadge();
                    this.loadNotifications();
                    if (typeof AiChat !== 'undefined' && AiChat.hideNotification) {
                        AiChat.hideNotification();
                    }
                }).catch(() => {});
            },

            timeAgo: function(dateStr) {
                const now = new window.Date();
                const date = new window.Date(dateStr.replace(' ', 'T') + 'Z');
                const diff = Math.floor((now - date) / 1000);
                if (diff < 60) return 'baru saja';
                if (diff < 3600) return Math.floor(diff / 60) + ' menit lalu';
                if (diff < 86400) return Math.floor(diff / 3600) + ' jam lalu';
                return date.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' });
            },

            escapeHtml: function(text) {
                const div = document.createElement('div');
                div.textContent = text;
                return div.innerHTML;
            }
        };

        document.addEventListener('DOMContentLoaded', () => BookingNotifications.init());
        // Also init on Turbo load
        document.addEventListener('turbo:load', () => {
            if (typeof BookingNotifications !== 'undefined') BookingNotifications.init();
        });
    </script>

    {{-- Custom Confirmation Modal --}}
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

    <script>
        // ========== GLOBAL CONFIRMATION MODAL ==========
        function showModal({ title, message, type = 'info', onConfirm = null, confirmText = 'Ya', cancelText = 'Batal' }) {
            const overlay = document.getElementById('customModal');
            if (!overlay) return;
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
            if (!overlay) return;
            overlay.classList.add('hidden');
            overlay.classList.remove('flex');
        }

        // Close modal on overlay click
        document.addEventListener('click', function(e) {
            if (e.target && e.target.id === 'customModal') closeCustomModal();
        });

        // Close modal on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeCustomModal();
        });

        // ===== CONFIRM CHECK-IN =====
        function confirmCheckin(id, resNumber, guestName, roomNumber, checkIn) {
            showModal({
                title: 'Konfirmasi Check-in',
                message: '<div class="text-left bg-gray-50 rounded-lg p-3 space-y-2 text-sm">' +
                    '<div class="flex justify-between"><span class="text-gray-500">Reservasi:</span><span class="font-bold text-blue-600">' + resNumber + '</span></div>' +
                    '<div class="flex justify-between"><span class="text-gray-500">Tamu:</span><span class="font-bold">' + guestName + '</span></div>' +
                    '<div class="flex justify-between"><span class="text-gray-500">Kamar:</span><span class="font-bold">' + roomNumber + '</span></div>' +
                    '<div class="flex justify-between"><span class="text-gray-500">Check-in:</span><span class="font-bold">' + checkIn + '</span></div>' +
                    '</div>' +
                    '<p class="mt-3 text-sm">Pastikan data tamu sudah benar. Lanjutkan check-in?</p>',
                type: 'confirm',
                confirmText: 'Ya, Check-in',
                cancelText: 'Batal',
                onConfirm: function() {
                    var form = document.createElement('form');
                    form.method = 'POST';
                    form.action = '{{ route("reservations.checkin", "__ID__") }}'.replace('__ID__', id);
                    var csrf = document.createElement('input');
                    csrf.type = 'hidden';
                    csrf.name = '_token';
                    csrf.value = '{{ csrf_token() }}';
                    form.appendChild(csrf);
                    document.body.appendChild(form);

                    var xhr = new XMLHttpRequest();
                    xhr.open('POST', form.action, true);
                    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                    xhr.setRequestHeader('Accept', 'application/json');
                    xhr.setRequestHeader('X-CSRF-TOKEN', csrf.value);

                    xhr.onload = function() {
                        if (xhr.status >= 200 && xhr.status < 300) {
                            try {
                                var data = JSON.parse(xhr.responseText);
                                if (data.success) {
                                    if (data.message) Toast.success(data.message);
                                    if (data.redirect_url) {
                                        window.location.href = data.redirect_url;
                                    } else {
                                        window.location.reload();
                                    }
                                } else {
                                    Toast.error(data.message || 'Gagal check-in');
                                }
                            } catch(e) {
                                Toast.error('Response tidak valid');
                            }
                        } else {
                            Toast.error('Terjadi kesalahan server');
                        }
                        document.body.removeChild(form);
                    };

                    xhr.onerror = function() {
                        Toast.error('Koneksi gagal. Silakan coba lagi.');
                        document.body.removeChild(form);
                    };

                    xhr.send(new FormData(form));
                }
            });
        }

        // ===== BATCH SELECT ALL / CHECKBOX HANDLERS (event delegation) =====
        document.addEventListener('change', function(e) {
            // Select All master checkbox
            if (e.target && e.target.id === 'checkAll') {
                var isChecked = e.target.checked;
                var tbody = e.target.closest('table')?.querySelector('tbody');
                if (tbody) {
                    var checkboxes = tbody.querySelectorAll('input[type="checkbox"]');
                    checkboxes.forEach(function(cb) { cb.checked = isChecked; });
                }
                refreshBatchButtons();
                return;
            }

            // Individual checkin checkbox
            if (e.target && e.target.classList.contains('checkin-checkbox')) {
                refreshBatchButtons();
                return;
            }

            // Individual checkout checkbox
            if (e.target && e.target.classList.contains('checkout-checkbox')) {
                refreshBatchButtons();
                return;
            }
        });

        function refreshBatchButtons() {
            // Check-in
            var ciChecked = document.querySelectorAll('.checkin-checkbox:checked');
            var ciBtn = document.getElementById('btnBatchCheckin');
            var ciCount = document.getElementById('batchCheckinCount');
            if (ciBtn) {
                ciBtn.disabled = ciChecked.length === 0;
                if (ciCount) ciCount.textContent = ciChecked.length;
            }

            // Checkout
            var coChecked = document.querySelectorAll('.checkout-checkbox:checked');
            var coBtn = document.getElementById('btnBatchCheckout');
            var coCount = document.getElementById('batchCheckoutCount');
            if (coBtn) {
                coBtn.disabled = coChecked.length === 0;
                if (coCount) coCount.textContent = coChecked.length;
            }
        }

        function batchCheckin() {
            var checked = document.querySelectorAll('.checkin-checkbox:checked');
            if (checked.length === 0) {
                Toast.warning('Pilih reservasi yang akan di-check-in.');
                return;
            }

            var ids = Array.from(checked).map(function(cb) { return cb.value; });
            var count = ids.length;

            // Build detail list from table rows
            var detailHtml = '';
            checked.forEach(function(cb) {
                var row = cb.closest('tr');
                if (row) {
                    var cells = row.querySelectorAll('td');
                    if (cells.length >= 5) {
                        var resNum = cells[1]?.textContent?.trim() || '-';
                        var guest = cells[2]?.textContent?.trim() || '-';
                        var roomNum = cells[3]?.textContent?.trim() || '-';
                        detailHtml += '<div class="flex justify-between text-sm py-1 border-b border-gray-100 last:border-0">' +
                            '<span class="text-blue-600 font-medium">' + resNum + '</span>' +
                            '<span>' + guest + ' <span class="text-gray-400">(' + roomNum + ')</span></span>' +
                            '</div>';
                    }
                }
            });

            showModal({
                title: 'Konfirmasi Batch Check-in (' + count + ' reservasi)',
                message: '<div class="text-left bg-gray-50 rounded-lg p-3 space-y-1 text-sm max-h-60 overflow-y-auto mb-3">' +
                    detailHtml +
                    '</div>' +
                    '<p class="text-sm text-gray-600">' + count + ' reservasi akan di-check-in. Status kamar menjadi <strong>Occupied</strong>.</p>',
                type: 'confirm',
                confirmText: 'Ya, Check-in ALL',
                cancelText: 'Batal',
                onConfirm: function() {
                    var xhr = new XMLHttpRequest();
                    xhr.open('POST', '{{ route("checkin.batch") }}', true);
                    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                    xhr.setRequestHeader('Accept', 'application/json');
                    xhr.setRequestHeader('X-CSRF-TOKEN', '{{ csrf_token() }}');
                    xhr.setRequestHeader('Content-Type', 'application/json');

                    xhr.onload = function() {
                        if (xhr.status >= 200 && xhr.status < 300) {
                            try {
                                var data = JSON.parse(xhr.responseText);
                                if (data.success) {
                                    Toast.success(data.message);
                                    if (data.redirect_url) {
                                        window.location.href = data.redirect_url;
                                    } else {
                                        window.location.reload();
                                    }
                                } else {
                                    Toast.error(data.message || 'Gagal check-in');
                                }
                            } catch(e) {
                                Toast.error('Response tidak valid');
                            }
                        } else {
                            Toast.error('Terjadi kesalahan server');
                        }
                    };

                    xhr.onerror = function() {
                        Toast.error('Koneksi gagal. Silakan coba lagi.');
                    };

                    xhr.send(JSON.stringify({ ids: ids }));
                }
            });
        }

        // ===== BATCH CHECK-OUT =====
        function batchCheckout() {
            var checked = document.querySelectorAll('.checkout-checkbox:checked');
            if (checked.length === 0) {
                Toast.warning('Pilih reservasi yang akan di-check-out.');
                return;
            }

            var ids = Array.from(checked).map(function(cb) { return cb.value; });
            var count = ids.length;

            // Build detail list from table rows
            var detailHtml = '';
            checked.forEach(function(cb) {
                var row = cb.closest('tr');
                if (row) {
                    var cells = row.querySelectorAll('td');
                    if (cells.length >= 5) {
                        var resNum = cells[1]?.textContent?.trim() || '-';
                        var guest = cells[2]?.textContent?.trim() || '-';
                        var roomNum = cells[3]?.textContent?.trim() || '-';
                        detailHtml += '<div class="flex justify-between text-sm py-1 border-b border-gray-100 last:border-0">' +
                            '<span class="text-blue-600 font-medium">' + resNum + '</span>' +
                            '<span>' + guest + ' <span class="text-gray-400">(' + roomNum + ')</span></span>' +
                            '</div>';
                    }
                }
            });

            showModal({
                title: 'Konfirmasi Batch Check-out (' + count + ' reservasi)',
                message: '<div class="text-left bg-gray-50 rounded-lg p-3 space-y-1 text-sm max-h-60 overflow-y-auto mb-3">' +
                    detailHtml +
                    '</div>' +
                    '<p class="text-sm text-red-600 font-semibold">⚠ ' + count + ' reservasi akan di-check-out. Status kamar menjadi <strong>Available</strong> dan tugas pembersihan dibuat.</p>',
                type: 'confirm',
                confirmText: 'Ya, Check-out ALL',
                cancelText: 'Batal',
                onConfirm: function() {
                    var xhr = new XMLHttpRequest();
                    xhr.open('POST', '{{ route("reservations.batch-checkout") }}', true);
                    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                    xhr.setRequestHeader('Accept', 'application/json');
                    xhr.setRequestHeader('X-CSRF-TOKEN', '{{ csrf_token() }}');
                    xhr.setRequestHeader('Content-Type', 'application/json');

                    xhr.onload = function() {
                        if (xhr.status >= 200 && xhr.status < 300) {
                            try {
                                var data = JSON.parse(xhr.responseText);
                                if (data.success) {
                                    Toast.success(data.message);
                                    if (data.redirect_url) {
                                        window.location.href = data.redirect_url;
                                    } else {
                                        window.location.reload();
                                    }
                                } else {
                                    Toast.error(data.message || 'Gagal checkout');
                                }
                            } catch(e) {
                                Toast.error('Response tidak valid');
                            }
                        } else {
                            Toast.error('Terjadi kesalahan server');
                        }
                    };

                    xhr.onerror = function() {
                        Toast.error('Koneksi gagal. Silakan coba lagi.');
                    };

                    xhr.send(JSON.stringify({ ids: ids }));
                }
            });
        }

        // ===== CONFIRM CHECK-OUT =====
        function confirmCheckout(id, resNumber, guestName, roomNumber, roomType) {
            showModal({
                title: 'Konfirmasi Check-out',
                message: '<div class="text-left bg-gray-50 rounded-lg p-3 space-y-2 text-sm">' +
                    '<div class="flex justify-between"><span class="text-gray-500">Reservasi:</span><span class="font-bold text-blue-600">' + resNumber + '</span></div>' +
                    '<div class="flex justify-between"><span class="text-gray-500">Tamu:</span><span class="font-bold">' + guestName + '</span></div>' +
                    '<div class="flex justify-between"><span class="text-gray-500">Kamar:</span><span class="font-bold">' + roomNumber + '</span></div>' +
                    (roomType ? '<div class="flex justify-between"><span class="text-gray-500">Tipe Kamar:</span><span class="font-bold">' + roomType + '</span></div>' : '') +
                    '</div>' +
                    '<p class="mt-3 text-sm text-red-600 font-semibold">⚠ Status kamar akan berubah menjadi <strong>Available</strong> setelah checkout.</p>' +
                    '<p class="text-sm">Lanjutkan checkout?</p>',
                type: 'confirm',
                confirmText: 'Ya, Check-out',
                cancelText: 'Batal',
                onConfirm: function() {
                    var form = document.createElement('form');
                    form.method = 'POST';
                    form.action = '{{ route("reservations.checkout", "__ID__") }}'.replace('__ID__', id);
                    var csrf = document.createElement('input');
                    csrf.type = 'hidden';
                    csrf.name = '_token';
                    csrf.value = '{{ csrf_token() }}';
                    form.appendChild(csrf);
                    document.body.appendChild(form);

                    var xhr = new XMLHttpRequest();
                    xhr.open('POST', form.action, true);
                    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                    xhr.setRequestHeader('Accept', 'application/json');
                    xhr.setRequestHeader('X-CSRF-TOKEN', csrf.value);

                    xhr.onload = function() {
                        if (xhr.status >= 200 && xhr.status < 300) {
                            try {
                                var data = JSON.parse(xhr.responseText);
                                if (data.success) {
                                    if (data.message) Toast.success(data.message);
                                    if (data.redirect_url) {
                                        window.location.href = data.redirect_url;
                                    } else {
                                        window.location.reload();
                                    }
                                } else {
                                    Toast.error(data.message || 'Gagal checkout');
                                }
                            } catch(e) {
                                Toast.error('Response tidak valid');
                            }
                        } else {
                            Toast.error('Terjadi kesalahan server');
                        }
                        document.body.removeChild(form);
                    };

                    xhr.onerror = function() {
                        Toast.error('Koneksi gagal. Silakan coba lagi.');
                        document.body.removeChild(form);
                    };

                    xhr.send(new FormData(form));
                }
            });
        }
    </script>

</body>
</html>
