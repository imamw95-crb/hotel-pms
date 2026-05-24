<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RoomDashboardController;
use App\Http\Controllers\CheckinController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\BookingGroupController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\RoomTypeController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\IssueCardController;
use Illuminate\Support\Facades\Route;

// Auth routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Root route - redirect based on user role
Route::get('/', function () {
    $user = auth()->user();
    if ($user->isOwner()) return redirect()->route('owner.dashboard');
    if ($user->isAdmin()) return redirect()->route('admin.dashboard');
    return redirect()->route('frontoffice.dashboard');
})->middleware('auth')->name('home');

Route::middleware(['auth'])->group(function () {
    // Dashboard routes
    Route::get('/owner/dashboard', [DashboardController::class, 'ownerDashboard'])->name('owner.dashboard');
    Route::get('/admin/dashboard', [DashboardController::class, 'adminDashboard'])->name('admin.dashboard');
    Route::get('/frontoffice/dashboard', [DashboardController::class, 'frontOfficeDashboard'])->name('frontoffice.dashboard');
    
    // Room Dashboard
    Route::get('/rooms-dashboard', [RoomDashboardController::class, 'index'])->name('rooms.dashboard');
    Route::get('/api/rooms-status', [RoomDashboardController::class, 'apiRoomsStatus'])->name('rooms.api');
    
    // Booking
    Route::get('/booking/create', [BookingController::class, 'create'])->name('booking.create');
    Route::get('/booking/check-availability', [BookingController::class, 'checkAvailability'])->name('booking.check-availability');
    Route::post('/booking', [BookingController::class, 'store'])->name('booking.store');
    Route::get('/booking-group', [BookingGroupController::class, 'create'])->name('booking.group.create');
    Route::post('/booking-group', [BookingGroupController::class, 'store'])->name('booking.group.store');
    
    // Checkin
    Route::get('/checkin', [CheckinController::class, 'index'])->name('checkin.index');
    Route::post('/checkin', [CheckinController::class, 'process'])->name('checkin.process');
    Route::get('/checkin/success/{id}', [CheckinController::class, 'success'])->name('checkin.success');
    

    // Issue Card
    Route::get('/issue-card', [IssueCardController::class, 'index'])->name('issue-card.index');
    Route::post('/issue-card/issue', [IssueCardController::class, 'issue'])->name('issue-card.issue');
    Route::post('/issue-card/{reservation}/reissue', [IssueCardController::class, 'reissue'])->name('issue-card.reissue');
    Route::post('/issue-card/{reservation}/checkout', [IssueCardController::class, 'checkout'])->name('issue-card.checkout');
    Route::get('/issue-card/test', [IssueCardController::class, 'testConnection'])->name('issue-card.test');
    Route::get('/issue-card/read', [IssueCardController::class, 'readCard'])->name('issue-card.read');

    // Reservasi
    Route::get('/reservations', [ReservationController::class, 'index'])->name('reservations.index');
    Route::get('/reservations/{reservation}', [ReservationController::class, 'show'])->name('reservations.show');
    Route::post('/reservations/{reservation}/cancel', [ReservationController::class, 'cancel'])->name('reservations.cancel');
    Route::post('/reservations/{reservation}/checkin', [ReservationController::class, 'checkin'])->name('reservations.checkin');
    Route::post('/reservations/{reservation}/checkout', [ReservationController::class, 'checkout'])->name('reservations.checkout');
    Route::post('/reservations/{reservation}/add-payment', [ReservationController::class, 'addPayment'])->name('reservations.add-payment');
    
    // Admin only
    Route::middleware(['role:admin'])->group(function () {
        Route::resource('rooms', RoomController::class);
        Route::resource('room-types', RoomTypeController::class);
    });
    
    // Reports (Owner & Admin)
    Route::middleware(['role:owner,admin'])->group(function () {
        Route::get('/reports/night-audit', [ReportController::class, 'nightAudit'])->name('reports.night-audit');
        Route::get('/reports/guest-list', [ReportController::class, 'guestList'])->name('reports.guest-list');
        Route::get('/reports/occupancy', [ReportController::class, 'occupancy'])->name('reports.occupancy');
        Route::get('/reports/revenue', [ReportController::class, 'revenue'])->name('reports.revenue');
        Route::get('/reports/reservations', [ReportController::class, 'reservations'])->name('reports.reservations');
    });
});