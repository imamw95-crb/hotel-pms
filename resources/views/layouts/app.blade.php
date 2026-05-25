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
            <nav class="mt-6 px-2 space-y-1">
                <x-menu />
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
    @yield('scripts')
</body>
</html>