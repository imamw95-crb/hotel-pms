# Panduan Implementasi di Layout Existing

## 1. Update Layout untuk Menampilkan Dynamic Menu

### Edit `resources/views/layouts/app.blade.php`

Tambahkan menu component di sidebar:

```blade
<aside class="sidebar">
    <x-menu />
</aside>
```

Atau jika menggunakan navbar:

```blade
<nav class="navbar">
    <div class="menu-container">
        <x-menu />
    </div>
</nav>
```

## 2. Contoh Implementasi di Controller

### Melindungi Route dengan Permission di Controller

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function __construct()
    {
        // Authorize semua method dengan permission view_reports
        // $this->middleware('permission:view_reports');
    }

    public function nightAudit(Request $request)
    {
        // Saat ini sudah dilindungi middleware di route, tapi bisa juga check di controller:
        if (!auth()->user()->hasPermission('view_reports')) {
            abort(403, 'You do not have permission to view reports');
        }

        // ... rest of code
    }
}
```

## 3. Contoh di Blade Template

### Conditional Menu Item

```blade
@if(hasPermission('view_reports'))
    <a href="{{ route('reports.night-audit') }}" class="nav-link">
        <i class="fas fa-chart-line"></i> Laporan
    </a>
@endif
```

### Multiple Permissions Check

```blade
@if(hasAnyPermission(['manage_users', 'manage_rooms']))
    <div class="admin-panel">
        @if(hasPermission('manage_users'))
            <a href="{{ route('users.index') }}">Kelola Pengguna</a>
        @endif
        
        @if(hasPermission('manage_rooms'))
            <a href="{{ route('rooms.index') }}">Kelola Kamar</a>
        @endif
    </div>
@endif
```

### Show/Hide Features Based on Permission

```blade
<!-- Tombol Download Laporan -->
@if(hasPermission('export_reports'))
    <button id="export-report" class="btn btn-primary">
        <i class="fas fa-download"></i> Download Laporan
    </button>
@endif

<!-- Tombol Edit -->
@if(hasPermission('edit_booking'))
    <button id="edit-booking" class="btn btn-secondary">
        <i class="fas fa-edit"></i> Edit
    </button>
@else
    <button disabled class="btn btn-secondary-disabled">
        <i class="fas fa-lock"></i> Tidak Bisa Edit
    </button>
@endif
```

## 4. Custom Menu Configuration

Edit `config/menus.php` untuk customize menu items:

```php
'items' => [
    [
        'label' => 'Laporan Keuangan',
        'icon' => 'money-bill-wave',
        'route' => 'reports.revenue',
        'roles' => ['owner', 'admin'],      // Hanya owner dan admin
        'permission' => 'view_reports',     // Dan harus punya permission ini
    ],
    [
        'label' => 'Pembayaran',
        'icon' => 'credit-card',
        'children' => [
            [
                'label' => 'Tambah Pembayaran',
                'route' => 'reservations.add-payment',
                'permission' => 'add_payment',
            ],
            [
                'label' => 'History Pembayaran',
                'route' => 'transactions.index',
                'permission' => 'view_transactions',
            ],
        ],
    ],
],
```

## 5. Styling Menu Component

Update `resources/views/components/menu.blade.php` dengan Tailwind CSS:

```blade
@php
    $menuItems = getMenuItemsWithPermissions();
@endphp

<nav class="space-y-1">
    @foreach($menuItems as $item)
        <div class="group">
            @if(isset($item['children']) && count($item['children']) > 0)
                <details class="group">
                    <summary class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100 rounded cursor-pointer">
                        <i class="fas fa-{{ $item['icon'] ?? 'folder' }} mr-3"></i>
                        <span class="font-medium">{{ $item['label'] }}</span>
                        <i class="fas fa-chevron-down ml-auto group-open:rotate-180 transition"></i>
                    </summary>
                    
                    <div class="ml-8 space-y-1 mt-2 border-l-2 border-gray-300 pl-2">
                        @foreach($item['children'] as $child)
                            @if(!isset($child['permission']) || hasPermission($child['permission']))
                                <a href="{{ route($child['route']) }}" 
                                   class="flex items-center px-4 py-2 text-sm text-gray-600 hover:text-blue-600 hover:bg-blue-50 rounded
                                          @if(request()->routeIs($child['route'])) 
                                              text-blue-700 bg-blue-50 border-l-4 border-blue-500
                                          @endif">
                                    {{ $child['label'] }}
                                </a>
                            @endif
                        @endforeach
                    </div>
                </details>
            @else
                <a href="{{ isset($item['route']) ? route($item['route']) : '#' }}" 
                   class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100 rounded
                          @if(isset($item['route']) && request()->routeIs($item['route'])) 
                              text-blue-700 bg-blue-100 border-l-4 border-blue-500
                          @endif">
                    <i class="fas fa-{{ $item['icon'] ?? 'circle' }} mr-3"></i>
                    <span class="font-medium">{{ $item['label'] }}</span>
                </a>
            @endif
        </div>
    @endforeach
</nav>
```

## 6. Testing Permission

### Di Tinker:

```bash
php artisan tinker

// Check if admin user has permission
>>> $user = User::where('role', 'admin')->first()
>>> $user->hasPermission('view_reports')
=> true

>>> $user->hasPermission('manage_users')
=> false

// List all permissions for admin role
>>> DB::table('role_permission')
    ->where('role', 'admin')
    ->join('permissions', 'role_permission.permission_id', '=', 'permissions.id')
    ->pluck('permissions.slug')
```

## 7. Management UI (Optional)

Untuk admin panel yang lebih lengkap, buat controller untuk manage permissions:

```php
php artisan make:controller Admin/PermissionController
```

Fitur yang bisa ditambahkan:
- CRUD Permission
- Assign Permission ke Role
- Assign Permission langsung ke User
- View daftar user dan permission mereka
- Bulk assign permission

Dokumentasi lengkap ada di [PERMISSION_SYSTEM.md](PERMISSION_SYSTEM.md)
