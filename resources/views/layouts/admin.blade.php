<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Dashboard') - Dynamic PMS V.2</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="{{ asset('assets/fontawesome/css/all.min.css') }}" rel="stylesheet">
    <script src="{{ asset('assets/chartjs/chart.js') }}"></script>
    <script type="module" src="{{ asset('assets/turbo/turbo.esm.js') }}"></script>
    <script nomodule>
        // Fallback: Turbo tidak support browser ini (halaman tetap jalan normal)
    </script>
</head>
<body class="bg-slate-50">

    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()" data-turbo-permanent></div>

    <div id="app-layout">
        <aside class="app-sidebar" id="appSidebar">
            <div class="sidebar-brand">
                <div class="sidebar-brand-icon">
                    <i class="fas fa-hotel text-white text-xl"></i>
                </div>
                <div class="sidebar-brand-title">Dynamic PMS V.2</div>
                <div class="sidebar-brand-subtitle">{{ auth()->user()->role === 'user_manager' ? 'Manager' : ucfirst(auth()->user()->role ?? 'Admin') }}</div>
            </div>
            <div class="sidebar-scroll">
                <x-menu />
            </div>
        </aside>

        <div class="sidebar-spacer"></div>

        <div class="main-wrapper">
            <header class="app-header" data-turbo-permanent>
                <div class="flex items-center gap-3">
                    <button class="text-slate-500 hover:text-slate-700 text-lg p-1 rounded-lg hover:bg-slate-100 transition" id="sidebarToggle" onclick="toggleSidebar()">
                        <i class="fas fa-bars"></i>
                    </button>
                    <nav class="text-sm text-slate-500">
                        <span class="font-semibold text-slate-800">@yield('header', 'Dashboard')</span>
                    </nav>
                </div>
                <div class="flex items-center gap-4">
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

                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white text-xs font-bold">
                            {{ substr(auth()->user()->name ?? 'U', 0, 1) }}
                        </div>
                        <span class="text-sm font-medium text-slate-700">{{ auth()->user()->name ?? 'User' }}</span>
                    </div>
                    <div class="w-px h-6 bg-slate-200"></div>
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="text-slate-400 hover:text-red-500 text-sm transition flex items-center gap-1.5">
                            <i class="fas fa-sign-out-alt"></i>
                            <span class="hidden sm:inline">Logout</span>
                        </button>
                    </form>
                </div>
            </header>

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

    <script>
        function toggleSidebar() {
            document.getElementById('appSidebar').classList.toggle('open');
            document.getElementById('sidebarOverlay').classList.toggle('show');
        }
    </script>
    <script src="{{ asset('js/app.js') }}"></script>
    <script src="{{ asset('js/async-form.js') }}"></script>

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

    {{-- Booking Notification Polling --}}
    <script data-turbo-permanent>
        window.BookingNotifications = {
            pollingInterval: null,
            isOpen: false,
            unreadCount: 0,

            init: function() {
                this.poll();
                this.pollingInterval = setInterval(() => this.poll(), 15000);

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

                        // If new notification detected, auto-show in AI Chat
                        if (hasNew && data.notifications && data.notifications.length > 0) {
                            var latest = data.notifications[0];
                            if (typeof AiChat !== 'undefined' && AiChat.showNotification) {
                                AiChat.showNotification(latest);
                            }
                        }
                    })
                    .catch(function() {});
            },

            toggle: function() {
                if (this.isOpen) { this.close(); } else { this.open(); }
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
                    if (n.action === 'cancelled') { icon = 'fa-times-circle'; color = 'text-red-500 bg-red-50'; }
                    else if (n.type === 'ota_booking') { icon = 'fa-globe'; color = 'text-teal-500 bg-teal-50'; }
                    return '<div class="flex items-start gap-3 px-4 py-3 ' + (isUnread ? 'bg-blue-50/50' : '') + ' hover:bg-gray-50 border-b border-gray-50 transition cursor-pointer" onclick="BookingNotifications.markRead(' + n.id + '); window.location=\'' + detailUrl + '\'">' +
                        '<div class="w-8 h-8 rounded-full ' + color + ' flex items-center justify-center flex-shrink-0"><i class="fas ' + icon + ' text-xs"></i></div>' +
                        '<div class="flex-1 min-w-0"><p class="text-sm ' + (isUnread ? 'font-semibold text-gray-900' : 'text-gray-600') + ' line-clamp-2">' + this.escapeHtml(n.message) + '</p>' +
                        '<p class="text-xs text-gray-400 mt-1">' + timeAgo + '</p></div>' +
                        (isUnread ? '<span class="w-2 h-2 rounded-full bg-blue-500 flex-shrink-0 mt-2"></span>' : '') + '</div>';
                }).join('');
            },

            updateBadge: function() {
                const badge = document.getElementById('notificationBadge');
                if (!badge) return;
                if (this.unreadCount > 0) {
                    badge.classList.remove('hidden');
                    badge.textContent = this.unreadCount > 99 ? '99+' : this.unreadCount;
                } else { badge.classList.add('hidden'); }
            },

            markRead: function(id) {
                fetch('{{ route('notifications.mark-read', '__ID__') }}'.replace('__ID__', id), { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=\'csrf-token\']').content } }).catch(() => {});
                this.unreadCount = Math.max(0, this.unreadCount - 1);
                this.updateBadge();
            },

            markAllRead: function() {
                fetch('{{ route('notifications.mark-all-read') }}', { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=\'csrf-token\']').content } })
                    .then(() => { this.unreadCount = 0; this.updateBadge(); this.loadNotifications(); }).catch(() => {});
            },

            timeAgo: function(dateStr) {
                const now = new Date();
                const date = new Date(dateStr.replace(' ', 'T') + 'Z');
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
        document.addEventListener('turbo:load', () => { if (typeof BookingNotifications !== 'undefined') BookingNotifications.init(); });
    </script>

    {{-- AI Chat Widget --}}
    <div data-turbo-permanent>
        @include('components.ai-chat-widget')
    </div>
</body>
</html>
    @yield('scripts')
</body>
</html>