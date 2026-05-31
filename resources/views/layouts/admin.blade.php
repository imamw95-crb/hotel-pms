<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Dashboard') - Hotel PMS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="/hotel-pms/public/assets/fontawesome/css/all.min.css" rel="stylesheet">
    <script src="/hotel-pms/public/assets/chartjs/chart.js"></script>
    <script type="module" src="/hotel-pms/public/assets/turbo/turbo.esm.js"></script>
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
                <div class="sidebar-brand-title">Hotel PMS</div>
                <div class="sidebar-brand-subtitle">{{ ucfirst(auth()->user()->role ?? 'Admin') }}</div>
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
</body>
</html>
    @yield('scripts')
</body>
</html>