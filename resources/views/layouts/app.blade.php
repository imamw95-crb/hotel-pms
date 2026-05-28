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
    <style>
        *, *::before, *::after { box-sizing: border-box; }

        [x-cloak] { display: none !important; }

        /* ── Dark mode smooth transition ── */
        body { transition: background-color 0.3s ease, color 0.3s ease; }

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
    </style>
</head>
<body class="bg-slate-50">

    <!-- Mobile Overlay -->
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

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
            <header class="app-header">
                <div class="flex items-center gap-3">
                    <button class="text-slate-500 hover:text-slate-700 text-lg p-1 rounded-lg hover:bg-slate-100 transition" id="sidebarToggle" onclick="toggleSidebar()">
                        <i class="fas fa-bars"></i>
                    </button>
                    <nav class="text-sm text-slate-500">
                        <span class="font-semibold text-slate-800">@yield('header', 'Dashboard')</span>
                    </nav>
                </div>
                <div class="flex items-center gap-3">
                    <button id="darkModeToggle" onclick="DarkMode.toggle()" class="w-8 h-8 rounded-lg hover:bg-slate-100 flex items-center justify-center text-slate-500 hover:text-slate-700 transition" title="Toggle Dark Mode">
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
    <script src="{{ asset('js/rooms-form.js') }}"></script>
    <script>
        window._depositIndexUrl = '{{ route('deposits.index') }}';
        window._depositCreateUrl = '{{ route('deposits.create') }}';
        window._depositReturnUrlTemplate = '{{ route('deposits.return', '__ID__') }}';
    </script>

    <!-- Modal Container -->
    <div id="modalOverlay" class="fixed inset-0 bg-black/50 z-[100] hidden"></div>
    <div id="modalContainer" class="fixed inset-0 z-[101] hidden flex items-center justify-center p-4">
        <div id="modalContent" class="bg-white rounded-xl shadow-2xl w-full max-w-4xl max-h-[90vh] overflow-y-auto relative">
            <button onclick="Modal.close()" class="absolute top-3 right-3 text-gray-400 hover:text-gray-700 text-xl z-10 w-8 h-8 flex items-center justify-center rounded-full hover:bg-gray-100 transition">
                <i class="fas fa-times"></i>
            </button>
            <div id="modalBody"></div>
        </div>
    </div>

    @yield('scripts')
</body>
</html>
