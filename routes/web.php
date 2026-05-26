<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RoomDashboardController;
use App\Http\Controllers\CheckinController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\BookingGroupController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\RoomTypeController;
use App\Http\Controllers\GuestController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\IssueCardController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\DatabaseBackupController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\DepositController;
use Illuminate\Support\Facades\Route;

// Auth routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Root route - redirect to rooms dashboard for all roles
Route::get('/', function () {
    return redirect()->route('rooms.dashboard');
})->middleware('auth')->name('home');

// Dashboard shortcut — restrict to owner only
Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'role:owner'])->name('dashboard');

Route::middleware(['auth'])->group(function () {
    // Dashboard routes — all use the same controller method which adapts to role
    Route::get('/owner/dashboard', [DashboardController::class, 'index'])->middleware('role:owner')->name('owner.dashboard');
    Route::get('/admin/dashboard', [DashboardController::class, 'index'])->middleware('role:admin')->name('admin.dashboard');
    Route::get('/frontoffice/dashboard', [DashboardController::class, 'index'])->middleware('role:frontoffice')->name('frontoffice.dashboard');
    
    // Room Dashboard
    Route::get('/rooms-dashboard', [RoomDashboardController::class, 'index'])->middleware('permission:view_room_dashboard')->name('rooms.dashboard');
    Route::get('/api/rooms-status', [RoomDashboardController::class, 'apiRoomsStatus'])->middleware('permission:view_room_dashboard')->name('rooms.api');
    
    // Booking
    Route::get('/booking/create', [BookingController::class, 'create'])->middleware('permission:create_booking')->name('booking.create');
    Route::get('/booking/check-availability', [BookingController::class, 'checkAvailability'])->middleware('permission:create_booking')->name('booking.check-availability');
    Route::post('/booking', [BookingController::class, 'store'])->middleware('permission:create_booking')->name('booking.store');
    Route::get('/booking-group', [BookingGroupController::class, 'create'])->middleware('permission:create_booking_group')->name('booking.group.create');
    Route::post('/booking-group', [BookingGroupController::class, 'store'])->middleware('permission:create_booking_group')->name('booking.group.store');
    
    // Checkin
    Route::get('/checkin', [CheckinController::class, 'index'])->middleware('permission:checkin')->name('checkin.index');
    Route::post('/checkin', [CheckinController::class, 'process'])->middleware('permission:checkin')->name('checkin.process');
    Route::get('/checkin/success/{id}', [CheckinController::class, 'success'])->middleware('permission:checkin')->name('checkin.success');
    
    
    // Issue Card
    Route::get('/issue-card', [IssueCardController::class, 'index'])->middleware('permission:issue_card')->name('issue-card.index');
    Route::post('/issue-card/issue', [IssueCardController::class, 'issue'])->middleware('permission:issue_card')->name('issue-card.issue');
    Route::post('/issue-card/{reservation}/reissue', [IssueCardController::class, 'reissue'])->middleware('permission:reissue_card')->name('issue-card.reissue');
    Route::post('/issue-card/{reservation}/checkout', [IssueCardController::class, 'checkout'])->middleware('permission:checkout')->name('issue-card.checkout');
    Route::get('/issue-card/test', [IssueCardController::class, 'testConnection'])->middleware('permission:issue_card')->name('issue-card.test');
    Route::get('/issue-card/read', [IssueCardController::class, 'readCard'])->middleware('permission:issue_card')->name('issue-card.read');

    // Reservasi
    Route::get('/reservations', [ReservationController::class, 'index'])->middleware('permission:view_reservations')->name('reservations.index');
    Route::get('/reservations/{reservation}', [ReservationController::class, 'show'])->middleware('permission:view_reservations')->name('reservations.show');
    Route::post('/reservations/{reservation}/cancel', [ReservationController::class, 'cancel'])->middleware('permission:cancel_reservation')->name('reservations.cancel');
    Route::post('/reservations/{reservation}/checkin', [ReservationController::class, 'checkin'])->middleware('permission:checkin')->name('reservations.checkin');
    Route::post('/reservations/{reservation}/checkout', [ReservationController::class, 'checkout'])->middleware('permission:checkout')->name('reservations.checkout');
    Route::post('/reservations/{reservation}/add-payment', [ReservationController::class, 'addPayment'])->middleware('permission:add_payment')->name('reservations.add-payment');
    Route::get('/reservations/{reservation}/print-kwitansi', [ReservationController::class, 'printKwitansi'])->middleware('permission:view_reservations')->name('reservations.print-kwitansi');
    Route::get('/reservations/{reservation}/print-invoice', [ReservationController::class, 'printInvoice'])->middleware('permission:view_reservations')->name('reservations.print-invoice');
    // Rooms & Room Types (all roles with permission)
    Route::resource('rooms', RoomController::class);
    Route::resource('room-types', RoomTypeController::class);
    
    // Guests
    Route::resource('guests', GuestController::class)->middleware('permission:manage_guests');
    Route::get('/guests/export/csv', [GuestController::class, 'export'])->middleware('permission:manage_guests')->name('guests.export');
    
    // Reports (all roles)
    Route::middleware(['permission:view_reports'])->group(function () {
        Route::get('/reports/night-audit', [ReportController::class, 'nightAudit'])->name('reports.night-audit');
        Route::get('/reports/guest-list', [ReportController::class, 'guestList'])->name('reports.guest-list');
        Route::get('/reports/occupancy', [ReportController::class, 'occupancy'])->name('reports.occupancy');
        Route::get('/reports/revenue', [ReportController::class, 'revenue'])->name('reports.revenue');
        Route::get('/reports/reservations', [ReportController::class, 'reservations'])->name('reports.reservations');
    });

    // Admin: Permission Management (Owner only)
    Route::middleware(['role:owner'])->group(function () {
        Route::get('/admin/permissions/dashboard', [PermissionController::class, 'dashboard'])->name('admin.permissions.dashboard');
        Route::get('/admin/permissions', [PermissionController::class, 'index'])->name('admin.permissions.index');
        Route::get('/admin/permissions/user-permissions', [PermissionController::class, 'userPermissions'])->name('admin.permissions.user-permissions');
        Route::get('/admin/permissions/role/{role}', [PermissionController::class, 'manageRolePermissions'])->name('admin.permissions.manage-role');
        Route::post('/admin/permissions/role/{role}', [PermissionController::class, 'updateRolePermissions'])->name('admin.permissions.update-role');
        Route::get('/admin/permissions/user/{user}', [PermissionController::class, 'manageUserPermissions'])->name('admin.permissions.manage-user');
        Route::post('/admin/permissions/user/{user}', [PermissionController::class, 'updateUserPermissions'])->name('admin.permissions.update-user');
    });

    // Admin: User Management (Owner only)
    Route::middleware(['role:owner'])->group(function () {
        Route::resource('admin/users', UserController::class, ['names' => 'admin.users']);
        Route::get('/admin/users/{user}/permissions', [UserController::class, 'permissions'])->name('admin.users.permissions');
    });

    // Database Backup (Owner only)
    Route::middleware(['role:owner'])->group(function () {
        Route::get('/admin/backups', [DatabaseBackupController::class, 'index'])->name('admin.backups.index');
        Route::post('/admin/backups/create', [DatabaseBackupController::class, 'create'])->name('admin.backups.create');
        Route::get('/admin/backups/download/{filename}', [DatabaseBackupController::class, 'download'])->name('admin.backups.download');
        Route::post('/admin/backups/restore/{filename}', [DatabaseBackupController::class, 'restore'])->name('admin.backups.restore');
        Route::delete('/admin/backups/{filename}', [DatabaseBackupController::class, 'destroy'])->name('admin.backups.destroy');
    });

    // Hotel Settings (Owner only)
    Route::middleware(['role:owner'])->group(function () {
        Route::get('/admin/settings', [SettingController::class, 'index'])->name('admin.settings');
        Route::post('/admin/settings', [SettingController::class, 'update'])->name('admin.settings.update');
    });

    // Deposit Kartu
    Route::middleware(['auth'])->group(function () {
        Route::get('/deposits', [DepositController::class, 'index'])->name('deposits.index');
        Route::get('/deposits/create', [DepositController::class, 'create'])->name('deposits.create');
        Route::post('/deposits', [DepositController::class, 'store'])->name('deposits.store');
        Route::get('/deposits/{deposit}', [DepositController::class, 'show'])->name('deposits.show');
    });
});