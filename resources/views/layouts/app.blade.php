<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Hotel PMS') - Hotel PMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        [x-cloak] { display: none !important; }
        .sidebar-item.active {
            background-color: #1e3a8a;
            color: white;
        }
        .sidebar-item:hover:not(.active) {
            background-color: #1e40af;
            color: #e0e7ff;
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <aside class="w-64 bg-blue-800 text-white flex-shrink-0 overflow-y-auto">
            <div class="p-4 text-center border-b border-blue-700">
                <i class="fas fa-hotel text-3xl"></i>
                <h1 class="text-xl font-bold mt-2">Hotel PMS</h1>
                <p class="text-xs text-blue-200">{{ ucfirst(auth()->user()->role ?? 'guest') }}</p>
            </div>
            <nav class="mt-6">
                @if(auth()->user()->isOwner())
                    <a href="{{ route('owner.dashboard') }}" class="sidebar-item block py-2.5 px-4 rounded transition duration-200 {{ request()->routeIs('owner.dashboard') ? 'active' : 'hover:bg-blue-700' }}">
                        <i class="fas fa-tachometer-alt w-5 mr-2"></i> Dashboard Owner
                    </a>
                @elseif(auth()->user()->isAdmin())
                    <a href="{{ route('admin.dashboard') }}" class="sidebar-item block py-2.5 px-4 rounded transition duration-200 {{ request()->routeIs('admin.dashboard') ? 'active' : 'hover:bg-blue-700' }}">
                        <i class="fas fa-tachometer-alt w-5 mr-2"></i> Dashboard Admin
                    </a>
                @else
                    <a href="{{ route('frontoffice.dashboard') }}" class="sidebar-item block py-2.5 px-4 rounded transition duration-200 {{ request()->routeIs('frontoffice.dashboard') ? 'active' : 'hover:bg-blue-700' }}">
                        <i class="fas fa-tachometer-alt w-5 mr-2"></i> Dashboard Front Office
                    </a>
                @endif

                <a href="{{ route('rooms.dashboard') }}" class="sidebar-item block py-2.5 px-4 rounded transition duration-200 {{ request()->routeIs('rooms.dashboard') ? 'active' : 'hover:bg-blue-700' }}">
                    <i class="fas fa-bed w-5 mr-2"></i> Dashboard Kamar
                </a>

                <div class="text-xs text-blue-300 uppercase tracking-wider mt-6 mb-2 px-4">Transaksi</div>
                <a href="{{ route('issue-card.index') }}" class="sidebar-item block py-2.5 px-4 rounded transition duration-200 {{ request()->routeIs('issue-card.*') ? 'active' : 'hover:bg-blue-700' }}">
                    <i class="fas fa-key w-5 mr-2"></i> Issue Card
                </a>
                <a href="{{ route('reservations.index') }}" class="sidebar-item block py-2.5 px-4 rounded transition duration-200 {{ request()->routeIs('reservations.*') ? 'active' : 'hover:bg-blue-700' }}">
                    <i class="fas fa-clipboard-list w-5 mr-2"></i> Reservasi
                </a>
                <a href="{{ route('checkin.index') }}" class="sidebar-item block py-2.5 px-4 rounded transition duration-200 {{ request()->routeIs('checkin.*') ? 'active' : 'hover:bg-blue-700' }}">
                    <i class="fas fa-sign-in-alt w-5 mr-2"></i> Check-in
                </a>

                <div class="text-xs text-blue-300 uppercase tracking-wider mt-6 mb-2 px-4">Booking</div>
                <a href="{{ route('booking.create') }}" class="sidebar-item block py-2.5 px-4 rounded transition duration-200 {{ request()->routeIs('booking.create') ? 'active' : 'hover:bg-blue-700' }}">
                    <i class="fas fa-calendar-plus w-5 mr-2"></i> Booking Single
                </a>
                <a href="{{ route('booking.group.create') }}" class="sidebar-item block py-2.5 px-4 rounded transition duration-200 {{ request()->routeIs('booking.group.create') ? 'active' : 'hover:bg-blue-700' }}">
                    <i class="fas fa-users w-5 mr-2"></i> Booking Group
                </a>

                @if(auth()->user()->isAdmin())
                    <div class="text-xs text-blue-300 uppercase tracking-wider mt-6 mb-2 px-4">Manajemen</div>
                    <a href="{{ route('rooms.index') }}" class="sidebar-item block py-2.5 px-4 rounded transition duration-200 {{ request()->routeIs('rooms.*') ? 'active' : 'hover:bg-blue-700' }}">
                        <i class="fas fa-door-open w-5 mr-2"></i> Kelola Kamar
                    </a>
                    <a href="{{ route('room-types.index') }}" class="sidebar-item block py-2.5 px-4 rounded transition duration-200 {{ request()->routeIs('room-types.*') ? 'active' : 'hover:bg-blue-700' }}">
                        <i class="fas fa-tag w-5 mr-2"></i> Tipe Kamar
                    </a>
                @endif

                @if(auth()->user()->isOwner())
                    <div class="text-xs text-blue-300 uppercase tracking-wider mt-6 mb-2 px-4">Laporan</div>
                    <a href="{{ route('reports.occupancy') }}" class="sidebar-item block py-2.5 px-4 rounded transition duration-200 {{ request()->routeIs('reports.occupancy') ? 'active' : 'hover:bg-blue-700' }}">
                        <i class="fas fa-chart-line w-5 mr-2"></i> Okupansi
                    </a>
                    <a href="{{ route('reports.revenue') }}" class="sidebar-item block py-2.5 px-4 rounded transition duration-200 {{ request()->routeIs('reports.revenue') ? 'active' : 'hover:bg-blue-700' }}">
                        <i class="fas fa-money-bill-wave w-5 mr-2"></i> Pendapatan
                    </a>
                    <a href="{{ route('reports.reservations') }}" class="sidebar-item block py-2.5 px-4 rounded transition duration-200 {{ request()->routeIs('reports.reservations') ? 'active' : 'hover:bg-blue-700' }}">
                        <i class="fas fa-book w-5 mr-2"></i> Reservasi
                    </a>
                @endif
            </nav>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-y-auto">
            <!-- Top Navbar -->
            <header class="bg-white shadow-sm py-3 px-6 flex justify-between items-center">
                <div class="flex items-center">
                    <i class="fas fa-bars text-gray-600 text-xl mr-4 cursor-pointer" id="sidebarToggle"></i>
                    <span class="text-gray-800 font-semibold">@yield('header', 'Hotel PMS')</span>
                </div>
                <div class="flex items-center space-x-3">
                    <span class="text-sm text-gray-600">{{ auth()->user()->name }}</span>
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="text-red-500 hover:text-red-700 text-sm">
                            <i class="fas fa-sign-out-alt mr-1"></i> Logout
                        </button>
                    </form>
                </div>
            </header>

            <!-- Page Content -->
            <main class="p-6">
                @if(session('success'))
                    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4 rounded shadow-sm">
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4 rounded shadow-sm">
                        {{ session('error') }}
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <script>
        // Sidebar toggle untuk mobile
        document.getElementById('sidebarToggle')?.addEventListener('click', function() {
            document.querySelector('aside').classList.toggle('hidden');
        });
    </script>
</body>
</html>