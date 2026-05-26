<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Dashboard') - Hotel PMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        [x-cloak] { display: none !important; }

        /* ── Sidebar ── */
        .app-sidebar {
            width: 260px;
            background: linear-gradient(180deg, #0f172a 0%, #1e293b 100%);
            display: flex;
            flex-direction: column;
            flex-shrink: 0;
            overflow-y: auto;
            transition: transform 0.3s ease;
            z-index: 50;
        }

        .app-sidebar::-webkit-scrollbar {
            width: 4px;
        }
        .app-sidebar::-webkit-scrollbar-track {
            background: transparent;
        }
        .app-sidebar::-webkit-scrollbar-thumb {
            background: rgba(255,255,255,0.15);
            border-radius: 4px;
        }

        /* ── Sidebar Brand ── */
        .sidebar-brand {
            padding: 1.25rem 1.25rem 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.06);
            text-align: center;
        }
        .sidebar-brand-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 42px;
            height: 42px;
            background: linear-gradient(135deg, #3b82f6, #6366f1);
            border-radius: 10px;
            margin-bottom: 0.5rem;
        }
        .sidebar-brand-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: #f1f5f9;
            letter-spacing: 0.5px;
        }
        .sidebar-brand-subtitle {
            font-size: 0.7rem;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            margin-top: 0.2rem;
        }

        /* ── Top Navbar ── */
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
            z-index: 45;
        }
        .sidebar-overlay.show {
            display: block;
        }

        @media (max-width: 768px) {
            .app-sidebar {
                position: fixed;
                top: 0;
                left: 0;
                bottom: 0;
                transform: translateX(-100%);
            }
            .app-sidebar.open {
                transform: translateX(0);
            }
        }
    </style>
</head>
<body class="bg-slate-50">
    <!-- Mobile Overlay -->
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

    <div class="flex h-screen">
        <!-- Sidebar -->
        <aside class="app-sidebar" id="appSidebar">
            <div class="sidebar-brand">
                <div class="sidebar-brand-icon">
                    <i class="fas fa-hotel text-white text-xl"></i>
                </div>
                <div class="sidebar-brand-title">Hotel PMS</div>
                <div class="sidebar-brand-subtitle">{{ ucfirst(auth()->user()->role ?? 'Admin') }}</div>
            </div>
            <div class="flex-1 py-2">
                <x-menu />
            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Navbar -->
            <header class="app-header">
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

            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto p-6">
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
            </main>
        </div>
    </div>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('appSidebar');
            const overlay = document.getElementById('sidebarOverlay');
            sidebar.classList.toggle('open');
            overlay.classList.toggle('show');
        }
    </script>
    @yield('scripts')
</body>
</html>