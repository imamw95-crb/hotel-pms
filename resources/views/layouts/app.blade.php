<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Hotel PMS') - Hotel PMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        dark: { 50: '#f8fafc', 100: '#f1f5f9', 200: '#e2e8f0', 300: '#cbd5e1', 400: '#94a3b8', 500: '#64748b', 600: '#475569', 700: '#334155', 800: '#1e293b', 900: '#0f172a', 950: '#020617' }
                    }
                }
            }
        }
    </script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@hotwired/turbo@8.0.23/dist/turbo.min.js" defer></script>
    <style>
        *, *::before, *::after { box-sizing: border-box; }

        [x-cloak] { display: none !important; }

        /* ── Dark mode smooth transition ── */
        body { transition: background-color 0.3s ease, color 0.3s ease; }

        /* ── DARK MODE STYLES ── */
        .dark body { background-color: #0f172a; color: #e2e8f0; }
        .dark .bg-white { background-color: #1e293b !important; }
        .dark .bg-gray-50 { background-color: #1e293b !important; }
        .dark .bg-gray-50\/50 { background-color: rgba(30,41,59,0.5) !important; }
        .dark .bg-slate-50 { background-color: #0f172a !important; }
        .dark .text-gray-800 { color: #f1f5f9 !important; }
        .dark .text-gray-700 { color: #cbd5e1 !important; }
        .dark .text-gray-600 { color: #94a3b8 !important; }
        .dark .text-gray-500 { color: #94a3b8 !important; }
        .dark .text-gray-400 { color: #64748b !important; }
        .dark .text-gray-300 { color: #475569 !important; }
        .dark .text-slate-800 { color: #f1f5f9 !important; }
        .dark .text-slate-700 { color: #cbd5e1 !important; }
        .dark .text-slate-500 { color: #94a3b8 !important; }
        .dark .text-slate-400 { color: #64748b !important; }
        .dark .border-gray-100 { border-color: #334155 !important; }
        .dark .border-gray-200 { border-color: #334155 !important; }
        .dark .border-gray-300 { border-color: #475569 !important; }
        .dark .border-slate-200 { border-color: #334155 !important; }
        .dark .divide-gray-50 > :not([hidden]) ~ :not([hidden]) { border-color: #1e293b !important; }
        .dark .divide-gray-100 > :not([hidden]) ~ :not([hidden]) { border-color: #334155 !important; }
        .dark .app-header { background-color: #1e293b !important; border-color: #334155 !important; }
        .dark .hover\:bg-slate-100:hover { background-color: #334155 !important; }
        .dark .hover\:text-slate-700:hover { color: #f1f5f9 !important; }
        .dark .hover\:bg-gray-50:hover { background-color: #334155 !important; }
        .dark .hover\:bg-gray-200:hover { background-color: #475569 !important; }
        .dark .hover\:bg-gray-100:hover { background-color: #334155 !important; }
        .dark .hover\:bg-blue-100:hover { background-color: rgba(59,130,246,0.2) !important; }
        .dark .hover\:bg-green-100:hover { background-color: rgba(34,197,94,0.2) !important; }
        .dark .hover\:bg-red-100:hover { background-color: rgba(239,68,68,0.2) !important; }
        .dark .hover\:bg-amber-100:hover { background-color: rgba(245,158,11,0.2) !important; }
        .dark .hover\:text-red-500:hover { color: #f87171 !important; }
        .dark .shadow-sm { box-shadow: 0 1px 2px 0 rgba(0,0,0,0.3) !important; }
        .dark .shadow { box-shadow: 0 1px 3px 0 rgba(0,0,0,0.4) !important; }
        .dark .shadow-2xl { box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5) !important; }
        .dark .ring-gray-200 { --tw-ring-color: #475569 !important; }
        .dark .focus\:ring-blue-500:focus { --tw-ring-color: #3b82f6 !important; }
        .dark input, .dark select, .dark textarea { background-color: #0f172a !important; color: #e2e8f0 !important; border-color: #475569 !important; }
        .dark input::placeholder, .dark textarea::placeholder { color: #64748b !important; }
        .dark .bg-blue-50 { background-color: rgba(59,130,246,0.1) !important; }
        .dark .bg-green-50 { background-color: rgba(34,197,94,0.1) !important; }
        .dark .bg-yellow-50 { background-color: rgba(234,179,8,0.1) !important; }
        .dark .bg-red-50 { background-color: rgba(239,68,68,0.1) !important; }
        .dark .bg-indigo-50 { background-color: rgba(99,102,241,0.1) !important; }
        .dark .bg-amber-50 { background-color: rgba(245,158,11,0.1) !important; }
        .dark .bg-purple-50 { background-color: rgba(168,85,247,0.1) !important; }
        .dark .text-blue-600 { color: #60a5fa !important; }
        .dark .text-blue-700 { color: #93c5fd !important; }
        .dark .text-green-600 { color: #4ade80 !important; }
        .dark .text-green-700 { color: #86efac !important; }
        .dark .text-yellow-600 { color: #facc15 !important; }
        .dark .text-yellow-700 { color: #fde047 !important; }
        .dark .text-red-600 { color: #f87171 !important; }
        .dark .text-red-700 { color: #fca5a5 !important; }
        .dark .text-indigo-600 { color: #818cf8 !important; }
        .dark .text-amber-600 { color: #fbbf24 !important; }
        .dark .border-yellow-200 { border-color: rgba(234,179,8,0.3) !important; }
        .dark .border-green-200 { border-color: rgba(34,197,94,0.3) !important; }
        .dark .border-blue-200 { border-color: rgba(59,130,246,0.3) !important; }
        .dark .border-red-200 { border-color: rgba(239,68,68,0.3) !important; }
        .dark .alert-success { background: rgba(34,197,94,0.1) !important; border-color: #22c55e !important; color: #86efac !important; }
        .dark .alert-error { background: rgba(239,68,68,0.1) !important; border-color: #ef4444 !important; color: #fca5a5 !important; }
        .dark .bg-gradient-to-br { background-image: linear-gradient(to bottom right, #1e293b, #334155) !important; }
        .dark .hover\:bg-blue-50\/40:hover { background-color: rgba(59,130,246,0.08) !important; }
        .dark .bg-blue-600 { background-color: #2563eb !important; }
        .dark .bg-blue-700 { background-color: #1d4ed8 !important; }
        .dark .hover\:bg-blue-700:hover { background-color: #1d4ed8 !important; }
        .dark #themeDropdown { background-color: #1e293b !important; border-color: #334155 !important; }
        .dark #themeDropdown .text-gray-700 { color: #cbd5e1 !important; }
        .dark #themeDropdown .hover\:bg-gray-50:hover { background-color: #334155 !important; }
        .dark #themeDropdown .bg-yellow-50 { background-color: rgba(234,179,8,0.15) !important; }
        .dark #themeDropdown .bg-indigo-50 { background-color: rgba(99,102,241,0.15) !important; }
        .dark #themeDropdown .bg-gray-100 { background-color: #334155 !important; }
        .dark .bg-gray-100 { background-color: #334155 !important; }
        .dark .text-blue-500 { color: #60a5fa !important; }
        .dark .text-yellow-500 { color: #facc15 !important; }
        .dark .text-indigo-500 { color: #818cf8 !important; }
        .dark .hover\:border-gray-300:hover { border-color: #64748b !important; }
        .dark .peer-checked\:border-blue-500:checked + div { border-color: #3b82f6 !important; }

        /* ── APP CONTAINER ── */
        #app-layout {
            display: flex;
            width: 100%;
            min-height: 100vh;
        }

        /* ── SIDEBAR SPACER (mendorong konten) ── */
        .sidebar-spacer {
            width: 260px;
            flex-shrink: 0;
            transition: width 0.3s ease;
        }

        /* ── SIDEBAR (FIXED LEFT, di atas spacer) ── */
        .app-sidebar {
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            width: 260px;
            background: linear-gradient(180deg, #0f172a 0%, #1e293b 100%);
            display: flex;
            flex-direction: column;
            z-index: 50;
            overflow: hidden;
            transform: translateX(0);
            transition: transform 0.3s ease;
        }
        .app-sidebar .sidebar-scroll {
            flex: 1;
            overflow-y: auto;
            overflow-x: hidden;
        }
        .app-sidebar .sidebar-scroll::-webkit-scrollbar { width: 4px; }
        .app-sidebar .sidebar-scroll::-webkit-scrollbar-track { background: transparent; }
        .app-sidebar .sidebar-scroll::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.15); border-radius: 4px; }

        .sidebar-brand {
            padding: 1.25rem 1.25rem 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.06);
            text-align: center;
            flex-shrink: 0;
        }
        .sidebar-brand-icon {
            display: inline-flex; align-items: center; justify-content: center;
            width: 42px; height: 42px;
            background: linear-gradient(135deg, #3b82f6, #6366f1);
            border-radius: 10px; margin-bottom: 0.5rem;
        }
        .sidebar-brand-title { font-size: 1.1rem; font-weight: 700; color: #f1f5f9; letter-spacing: 0.5px; }
        .sidebar-brand-subtitle { font-size: 0.7rem; color: #64748b; text-transform: uppercase; letter-spacing: 1.5px; margin-top: 0.2rem; }

        /* ── MAIN CONTENT WRAPPER ── */
        .main-wrapper {
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            width: 100%;
        }

        /* ── TOP HEADER (sticky) ── */
        .app-header {
            background: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            padding: 0.75rem 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 40;
            flex-shrink: 0;
        }

        /* ── PAGE CONTENT ── */
        .page-content {
            flex: 1;
            overflow-y: auto;
            overflow-x: hidden;
            padding: 1.5rem;
            width: 100%;
            max-width: 100%;
        }

        /* ── Alert Messages ── */
        .alert-success {
            background: #f0fdf4;
            border-left: 4px solid #22c55e;
            color: #166534;
            padding: 0.85rem 1.25rem;
            margin-bottom: 1rem;
            border-radius: 0 0.5rem 0.5rem 0;
            font-size: 0.875rem;
        }
        .alert-error {
            background: #fef2f2;
            border-left: 4px solid #ef4444;
            color: #991b1b;
            padding: 0.85rem 1.25rem;
            margin-bottom: 1rem;
            border-radius: 0 0.5rem 0.5rem 0;
            font-size: 0.875rem;
        }

        /* ── Mobile Overlay ── */
        .sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.5);
            z-index: 49;
        }
        .sidebar-overlay.show { display: block; }

        /* ── RESPONSIVE BREAKPOINTS ── */
        @media (max-width: 768px) {
            .app-sidebar { transform: translateX(-100%); }
            .app-sidebar.open { transform: translateX(0); }
            .sidebar-spacer { width: 0; }
            .page-content { padding: 1rem; }
        }

        @media (min-width: 769px) {
            .app-sidebar.collapsed { transform: translateX(-260px); }
            .sidebar-spacer.collapsed { width: 0; }
        }

        @media (max-width: 480px) {
            .page-content { padding: 0.75rem; }
        }

        /* ── ROOM GRID (responsive) ── */
        .rooms-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 0.75rem;
            width: 100%;
            max-width: 100%;
        }
        @media (min-width: 640px) {
            .rooms-grid { grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 0.75rem; }
        }
        @media (min-width: 1024px) {
            .rooms-grid { grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 1rem; }
        }
        @media (min-width: 1440px) {
            .rooms-grid { grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 1rem; }
        }

        /* ── ROOM CARD ── */
        .room-card {
            width: 100%;
            max-width: 100%;
            overflow: hidden;
        }
        .room-card > div {
            width: 100%;
            overflow: hidden;
        }

        /* ── STATS CARDS ── */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 1rem;
            width: 100%;
            max-width: 100%;
        }
        @media (max-width: 480px) {
            .stats-grid { grid-template-columns: repeat(2, 1fr); gap: 0.5rem; }
        }

        /* ── TURBO PAGE TRANSITION ── */
        .page-content {
            transition: opacity 0.15s ease, transform 0.15s ease;
        }
        .turbo-before-render .page-content {
            opacity: 0;
            transform: translateY(6px);
        }
    </style>
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
                    <i class="fas fa-hotel text-white text-xl"></i>
                </div>
                <div class="sidebar-brand-title">Hotel PMS</div>
                <div class="sidebar-brand-subtitle">{{ ucfirst(auth()->user()->role ?? 'Guest') }}</div>
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
            document.body.classList.add('turbo-before-render');
        });

        document.addEventListener('turbo:render', () => {
            document.body.classList.remove('turbo-before-render');
            window.scrollTo({ top: 0, behavior: 'instant' });
        });

        document.addEventListener('turbo:load', () => {
            // Sidebar: re-init submenu active state
            document.querySelectorAll('.menu-item.has-submenu.active').forEach(function(el) {
                el.classList.add('open');
            });
            // Re-init async forms
            if (typeof initAsyncForms === 'function') {
                initAsyncForms();
            }
            // Re-init modal click handlers (deposit, booking)
            document.querySelectorAll('[onclick*="Modal.open"]').forEach(function(el) {
                if (el.dataset.turbo !== 'false') {
                    el.setAttribute('data-turbo', 'false');
                }
            });
        });
    </script>

    @yield('scripts')
</body>
</html>
