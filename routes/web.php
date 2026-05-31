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
use App\Http\Controllers\RestoController;
use App\Http\Controllers\ServiceChargeController;
use App\Http\Controllers\PaymentMethodController;
use App\Http\Controllers\HousekeepingController;
use App\Http\Controllers\NightAuditController;
use App\Http\Controllers\ExpenseController;
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
    Route::patch('/rooms/{room}/status', [RoomDashboardController::class, 'updateStatus'])->middleware('permission:manage_rooms')->name('rooms.update-status');
    Route::patch('/rooms/bulk-status', [RoomDashboardController::class, 'bulkUpdateStatus'])->middleware('permission:manage_rooms')->name('rooms.bulk-status');
    
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
    Route::get('/reservations', [ReservationController::class, 'index'])->name('reservations.index');
    Route::get('/reservations/{reservation}', [ReservationController::class, 'show'])->name('reservations.show');
    Route::post('/reservations/{reservation}/toggle-breakfast', [ReservationController::class, 'toggleBreakfast'])->name('reservations.toggle-breakfast');
    Route::post('/reservations/{reservation}/cancel', [ReservationController::class, 'cancel'])->middleware('permission:cancel_reservation')->name('reservations.cancel');
    Route::post('/reservations/{reservation}/checkin', [ReservationController::class, 'checkin'])->middleware('permission:checkin')->name('reservations.checkin');
    Route::post('/reservations/{reservation}/checkout', [ReservationController::class, 'checkout'])->middleware('permission:checkout')->name('reservations.checkout');
    Route::get('/checkout', [ReservationController::class, 'checkoutList'])->middleware('permission:checkout')->name('checkout.index');
    Route::post('/rooms/{room}/checkout', [ReservationController::class, 'checkoutByRoom'])->middleware('permission:checkout')->name('rooms.checkout');
    Route::post('/reservations/{reservation}/add-payment', [ReservationController::class, 'addPayment'])->middleware('permission:add_payment')->name('reservations.add-payment');
    Route::get('/room-change', [ReservationController::class, 'roomChangeList'])->middleware('permission:change_room')->name('room-change.index');
    Route::get('/reservations/{reservation}/room-change', [ReservationController::class, 'showRoomChange'])->middleware('permission:change_room')->name('reservations.room-change');
    Route::post('/reservations/{reservation}/room-change', [ReservationController::class, 'changeRoom'])->middleware('permission:change_room')->name('reservations.room-change.store');
    Route::get('/reservations/{reservation}/print-kwitansi', [ReservationController::class, 'printKwitansi'])->name('reservations.print-kwitansi');
    Route::get('/reservations/{reservation}/print-invoice', [ReservationController::class, 'printInvoice'])->name('reservations.print-invoice');
    Route::post('/reservations/{reservation}/update-total', [ReservationController::class, 'updateTotal'])->name('reservations.update-total');
    Route::post('/reservations/{reservation}/update-room-rate', [ReservationController::class, 'updateRoomRate'])->name('reservations.update-room-rate');

    // AI Auto-Reservation
    Route::post('/reservations/ai-create', [ReservationController::class, 'aiCreate'])->middleware('permission:create_booking')->name('reservations.ai-create');

    // Room Rack & Availability
    Route::get('/room-rack', [\App\Http\Controllers\RoomRackController::class, 'index'])->name('room-rack.index');
    Route::get('/room-rack/check-availability', [\App\Http\Controllers\RoomRackController::class, 'checkAvailability'])->name('room-rack.check-availability');
    Route::get('/room-rack/occupancy', [\App\Http\Controllers\RoomRackController::class, 'occupancyCalendar'])->name('room-rack.occupancy');
    Route::get('/room-rack/forecast', [\App\Http\Controllers\RoomRackController::class, 'forecast'])->name('room-rack.forecast');

    // Room List — all roles, no permission restriction
    Route::get('/room-list', [\App\Http\Controllers\RoomListController::class, 'index'])->name('room-list.index');
    Route::get('/room-list/print', [\App\Http\Controllers\RoomListController::class, 'print'])->name('room-list.print');

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
        Route::get('/reports/group', [ReportController::class, 'groupReport'])->name('reports.group');

        // Night Audit v2 — Preview, Draft, Lock, History
        Route::get('/reports/night-audit-v2', [NightAuditController::class, 'index'])->name('reports.night-audit-v2.index');
        Route::get('/reports/night-audit-v2/preview', [NightAuditController::class, 'preview'])->name('reports.night-audit-v2.preview');
        Route::post('/reports/night-audit-v2/save-draft', [NightAuditController::class, 'saveDraft'])->name('reports.night-audit-v2.save-draft');
        Route::post('/reports/night-audit-v2/lock', [NightAuditController::class, 'lock'])->name('reports.night-audit-v2.lock');
        Route::post('/reports/night-audit-v2/delete-draft', [NightAuditController::class, 'deleteDraft'])->name('reports.night-audit-v2.delete-draft');
        Route::get('/reports/night-audit-v2/{id}', [NightAuditController::class, 'show'])->name('reports.night-audit-v2.show');
        Route::get('/reports/night-audit-v2/{id}/export', [NightAuditController::class, 'export'])->name('reports.night-audit-v2.export');

        // Pengeluaran (Expenses)
        Route::get('/expenses', [ExpenseController::class, 'index'])->name('expenses.index');
        Route::get('/expenses/create', [ExpenseController::class, 'create'])->name('expenses.create');
        Route::post('/expenses', [ExpenseController::class, 'store'])->name('expenses.store');

        // Export routes
        Route::get('/reports/night-audit/export', [ReportController::class, 'exportNightAudit'])->name('reports.night-audit.export');
        Route::get('/reports/guest-list/export', [ReportController::class, 'exportGuestList'])->name('reports.guest-list.export');
        Route::get('/reports/occupancy/export', [ReportController::class, 'exportOccupancy'])->name('reports.occupancy.export');
        Route::get('/reports/revenue/export', [ReportController::class, 'exportRevenue'])->name('reports.revenue.export');
        Route::get('/reports/reservations/export', [ReportController::class, 'exportReservations'])->name('reports.reservations.export');
        Route::get('/reports/group/export', [ReportController::class, 'exportGroupReport'])->name('reports.group.export');
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

    // API Key Management (Owner only)
    Route::middleware(['role:owner'])->group(function () {
        Route::get('/admin/api-keys', function () {
            $users = \App\Models\User::with(['tokens' => function ($q) {
                $q->select('id', 'tokenable_id', 'name', 'created_at', 'last_used_at');
            }])->whereHas('tokens')->get();

            $keys = $users->map(function ($user) {
                return $user->tokens->map(function ($token) use ($user) {
                    return [
                        'id'           => $token->id,
                        'user_name'    => $user->name,
                        'user_email'   => $user->email,
                        'name'         => $token->name,
                        'last_used_at' => $token->last_used_at,
                        'created_at'   => $token->created_at,
                    ];
                });
            })->flatten(1);

            $apiKey = session('api_key');

            $ownerAdminUsers = \App\Models\User::whereIn('role', ['owner', 'admin'])->get();

            return view('admin.api-keys.index', compact('keys', 'apiKey', 'ownerAdminUsers'));
        })->name('admin.api-keys');

        Route::post('/admin/api-keys/generate', function (\Illuminate\Http\Request $request) {
            $request->validate([
                'user_id' => 'required|exists:users,id',
                'name'    => 'required|string|max:100',
            ]);

            $user = \App\Models\User::findOrFail($request->user_id);
            $apiKey = \Illuminate\Support\Str::random(48);

            $user->tokens()->where('name', $request->name)->delete();
            $user->tokens()->create([
                'name'      => $request->name,
                'token'     => hash('sha256', $apiKey),
                'abilities' => ['*'],
            ]);

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'API Key berhasil dibuat.',
                    'data'    => ['api_key' => $apiKey, 'name' => $request->name],
                ]);
            }

            return redirect()->route('admin.api-keys')
                ->with('success', "API Key berhasil dibuat:<br><code class='bg-green-200 px-2 py-0.5 rounded text-sm select-all'>{$apiKey}</code><br><small class='text-red-600'><i class='fas fa-exclamation-triangle mr-1'></i>SIMPAN KEY INI! Key tidak bisa ditampilkan lagi.</small>");
        })->name('admin.api-keys.generate');

        Route::delete('/admin/api-keys/{id}/revoke', function ($id) {
            $token = \Laravel\Sanctum\PersonalAccessToken::find($id);
            if (!$token) {
                return redirect()->route('admin.api-keys')
                    ->with('error', 'API Key tidak ditemukan.');
            }
            $token->delete();

            return redirect()->route('admin.api-keys')
                ->with('success', 'API Key berhasil dihapus.');
        })->name('admin.api-keys.revoke');
    });

    // Master Metode Pembayaran (Owner only)
    Route::middleware(['role:owner'])->group(function () {
        Route::resource('admin/payment-methods', PaymentMethodController::class, ['names' => 'admin.payment-methods']);
    });

    // Deposit Kartu
    Route::middleware(['auth'])->group(function () {
        Route::get('/deposits', [DepositController::class, 'index'])->name('deposits.index');
        Route::get('/deposits/create', [DepositController::class, 'create'])->name('deposits.create');
        Route::post('/deposits', [DepositController::class, 'store'])->name('deposits.store');
        Route::get('/deposits/{deposit}', [DepositController::class, 'show'])->name('deposits.show');
        Route::post('/deposits/{deposit}/return', [DepositController::class, 'returnDeposit'])->name('deposits.return');
    });

    // Pendapatan Resto
    Route::middleware(['auth', 'permission:view_reports'])->group(function () {
        Route::get('/resto', [RestoController::class, 'index'])->name('resto.index');
        Route::get('/resto/create', [RestoController::class, 'create'])->name('resto.create');
        Route::post('/resto', [RestoController::class, 'store'])->name('resto.store');
        Route::get('/resto/{restoTransaction}', [RestoController::class, 'show'])->name('resto.show');
    });

    // Service Charge
    Route::middleware(['auth', 'permission:checkin'])->group(function () {
        Route::get('/service-charge', [ServiceChargeController::class, 'index'])->name('service-charge.index');
        Route::get('/service-charge/create', [ServiceChargeController::class, 'create'])->name('service-charge.create');
        Route::post('/service-charge', [ServiceChargeController::class, 'store'])->name('service-charge.store');
        Route::get('/service-charge/{serviceCharge}', [ServiceChargeController::class, 'show'])->name('service-charge.show');
    });

    // Housekeeping
    Route::middleware(['auth'])->group(function () {
        Route::get('/housekeeping', [HousekeepingController::class, 'index'])->name('housekeeping.index');
        Route::post('/housekeeping', [HousekeepingController::class, 'store'])->name('housekeeping.store');
        Route::get('/housekeeping/stats', [HousekeepingController::class, 'stats'])->name('housekeeping.stats');
        Route::get('/housekeeping/print', [HousekeepingController::class, 'printReport'])->name('housekeeping.print');
        Route::get('/housekeeping/room/{room}/tasks', [HousekeepingController::class, 'roomTasks'])->name('housekeeping.room-tasks');
        Route::get('/housekeeping/{housekeepingTask}', [HousekeepingController::class, 'show'])->name('housekeeping.show');
        Route::patch('/housekeeping/{housekeepingTask}/status', [HousekeepingController::class, 'updateStatus'])->name('housekeeping.update-status');
        Route::patch('/housekeeping/{housekeepingTask}/assign', [HousekeepingController::class, 'assign'])->name('housekeeping.assign');
        Route::post('/housekeeping/bulk-create', [HousekeepingController::class, 'bulkCreate'])->name('housekeeping.bulk-create');
        Route::delete('/housekeeping/{housekeepingTask}', [HousekeepingController::class, 'destroy'])->name('housekeeping.destroy');
        Route::get('/housekeeping/print', [HousekeepingController::class, 'printReport'])->name('housekeeping.print');
    });

    // ─── AI Chat Assistant ───
    Route::post('/api/ai/chat', [App\Http\Controllers\AiChatController::class, 'chat'])
        ->name('api.ai.chat');

    // ─── OTA Email Monitoring Log ──────────────────────────────
    Route::middleware(['permission:view_reports'])->group(function () {
        Route::get('/ota-email-logs', [App\Http\Controllers\OtaEmailLogController::class, 'index'])
            ->name('ota-email-logs.index');
        Route::get('/ota-email-logs/{id}', [App\Http\Controllers\OtaEmailLogController::class, 'show'])
            ->name('ota-email-logs.show');
        Route::post('/ota-email-logs/{id}/retry', [App\Http\Controllers\OtaEmailLogController::class, 'retry'])
            ->name('ota-email-logs.retry');
        Route::post('/ota-email-logs/refresh-stats', [App\Http\Controllers\OtaEmailLogController::class, 'refreshStats'])
            ->name('ota-email-logs.refresh-stats');
    });

    // ─── API: OTA Email Stats (for dashboard widgets) ──────────
    Route::get('/api/ota-email-logs/stats', [App\Http\Controllers\OtaEmailLogController::class, 'apiStats'])
        ->name('api.ota-email-logs.stats');
    Route::get('/api/ota-email-logs/recent', [App\Http\Controllers\OtaEmailLogController::class, 'apiRecent'])
        ->name('api.ota-email-logs.recent');

    // ─── Panduan Penggunaan ────────────────────────────────────
    Route::get('/help', function () {
        return view('help.index');
    })->name('help.index');

    // ─── OTA Autopilot Test Routes (dev only) ───
    if (app()->environment('local')) {
        Route::prefix('dev/ota-test')->middleware(['auth', 'role:owner'])->group(function () {
            Route::get('/', function () {
                return view('dev.ota-test');
            });
            Route::post('/parse', function (\Illuminate\Http\Request $request) {
                $service = app(\App\Services\OpenRouterService::class);
                $result = $service->parseBookingEmail(
                    $request->input('email_body', ''),
                    $request->input('email_subject', ''),
                    $request->input('ota_source', 'tiket.com')
                );
                return response()->json(['success' => !is_null($result), 'data' => $result]);
            });
            Route::get('/notifications', function () {
                $notifications = cache('ota_notifications', []);
                return response()->json(['notifications' => $notifications]);
            });
        });
    }
});
