# Sistem Permission dan Custom Menu

Sistem permission dan menu dinamis untuk Hotel PMS yang memungkinkan kontrol akses terperinci berbasis role dan permission.

## Fitur

- **Role-Based Access Control (RBAC)** - Kontrol akses berbasis role (owner, admin, frontoffice)
- **Permission Management** - Manajemen permission yang terpisah dari role
- **Direct User Permissions** - Permission tambahan langsung ke user tertentu
- **Dynamic Menu** - Menu dinamis berdasarkan role dan permission user
- **Middleware Support** - Middleware untuk check permission di route

## Database Schema

### `permissions` table
Menyimpan daftar permission yang tersedia:
- `id` - Primary key
- `name` - Nama permission (display name)
- `slug` - Unique identifier permission (e.g., 'view_reports', 'create_booking')
- `description` - Deskripsi permission
- `group` - Group permission (e.g., 'reports', 'rooms', 'booking')
- `timestamps`

### `role_permission` table
Relasi banyak-ke-banyak antara role dan permission:
- `id` - Primary key
- `role` - Nama role (owner, admin, frontoffice)
- `permission_id` - Foreign key ke permissions
- `timestamps`

### `user_permission` table
Direct permission untuk user individual (override role permissions):
- `id` - Primary key
- `user_id` - Foreign key ke users
- `permission_id` - Foreign key ke permissions
- `timestamps`

## Usage

### 1. Check Permission di View/Blade

```blade
@if(hasPermission('view_reports'))
    <a href="{{ route('reports.night-audit') }}">Laporan</a>
@endif
```

### 2. Check Multiple Permissions

```blade
@if(hasAllPermissions(['view_reports', 'export_reports']))
    <!-- Tampilkan export button -->
@endif

@if(hasAnyPermission(['view_reports', 'manage_users']))
    <!-- Tampilkan jika punya salah satu permission -->
@endif
```

### 3. Check Permission di Controller

```php
// Di controller
if (auth()->user()->hasPermission('view_reports')) {
    // Execute code
}

if (auth()->user()->hasAllPermissions(['view_reports', 'export_reports'])) {
    // Execute code
}
```

### 4. Use Permission Middleware di Route

```php
// Di routes/web.php
Route::get('/reports', [ReportController::class, 'index'])
    ->middleware('permission:view_reports');

// Atau multiple permissions (user perlu punya salah satu)
Route::get('/admin', [AdminController::class, 'index'])
    ->middleware('permission:manage_users,manage_rooms');
```

### 5. Dynamic Menu - Menggunakan Menu Component

```blade
<x-menu />
```

Menu component akan otomatis:
- Filter berdasarkan role user
- Filter berdasarkan permission user
- Highlight current active route
- Tampilkan submenu jika ada

### 6. Konfigurasi Menu

Edit `config/menus.php`:

```php
'items' => [
    [
        'label' => 'Dashboard',
        'icon' => 'chart-line',
        'route' => 'home',
        'roles' => ['owner', 'admin', 'frontoffice'], // Siapa yang bisa akses
    ],
    
    [
        'label' => 'Reports',
        'icon' => 'file-chart-line',
        'roles' => ['owner', 'admin'],
        'children' => [
            [
                'label' => 'Night Audit',
                'route' => 'reports.night-audit',
                'permission' => 'view_reports', // Permission yang diperlukan
            ],
        ],
    ],
],
```

## Default Permissions

Seeder sudah membuat permissions berikut:

### Booking Permissions
- `create_booking` - Buat booking
- `view_bookings` - Lihat daftar booking
- `edit_booking` - Edit booking
- `delete_booking` - Hapus booking
- `create_booking_group` - Buat booking group

### Reservation Permissions
- `view_reservations` - Lihat daftar reservasi
- `edit_reservation` - Edit reservasi
- `cancel_reservation` - Batalkan reservasi
- `add_payment` - Tambah pembayaran

### Check-in/Check-out Permissions
- `checkin` - Check-in
- `checkout` - Check-out
- `issue_card` - Issue kartu kamar
- `reissue_card` - Re-issue kartu

### Room Permissions
- `view_rooms` - Lihat kamar
- `create_room` - Buat kamar
- `edit_room` - Edit kamar
- `delete_room` - Hapus kamar
- `view_room_dashboard` - Lihat room dashboard
- `manage_rooms` - Kelola semua kamar
- `view_room_types` - Lihat tipe kamar
- `create_room_type` - Buat tipe kamar
- `edit_room_type` - Edit tipe kamar
- `delete_room_type` - Hapus tipe kamar

### Report Permissions
- `view_reports` - Lihat laporan
- `export_reports` - Export laporan

### User Permissions
- `manage_users` - Kelola pengguna
- `create_user` - Buat pengguna
- `edit_user` - Edit pengguna
- `delete_user` - Hapus pengguna

## Default Role-Permission Assignment

### Owner
- view_reports
- export_reports
- manage_users

### Admin
- Semua permission booking dan reservation
- Semua permission check-in/out
- Semua permission room
- view_reports

### Front Office
- create_booking, view_bookings
- view_reservations, add_payment
- checkin, checkout, issue_card, reissue_card
- view_room_dashboard

## Implementasi di Layout

Update `resources/views/layouts/app.blade.php`:

```blade
<div class="sidebar">
    <x-menu />
</div>
```

## Migration & Seeding

Jalankan migration dan seeder:

```bash
php artisan migrate
php artisan db:seed --class=PermissionSeeder
```

Atau jalankan semua seeder:

```bash
php artisan db:seed
```

## Tips

1. **Owner selalu punya semua permission** - Role owner di-skip dari permission check
2. **Direct user permission override** - Permission langsung ke user akan override role permission
3. **Tambah permission baru** - Tambahkan di PermissionSeeder lalu jalankan `php artisan db:seed`
4. **Disable menu item** - Set `'roles' => []` atau tambahkan permission requirement yang tidak dimiliki user

## Troubleshooting

**Permission tidak bekerja:**
- Jalankan `php artisan cache:clear`
- Pastikan permission sudah di-seed ke database
- Cek apakah user/role sudah punya permission di role_permission table

**Menu tidak muncul:**
- Pastikan route sudah terdaftar
- Cek apakah role user sesuai dengan config menus
- Verifikasi permission user dengan `hasPermission('slug')`
