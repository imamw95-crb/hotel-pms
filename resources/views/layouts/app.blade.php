<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Hotel PMS') - Hotel PMS</title>
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
</body>
</html>
