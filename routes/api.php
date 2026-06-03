<?php

use App\Http\Controllers\Api\ApiKeyController;
use App\Http\Controllers\Api\PromoPriceApiController;
use App\Http\Controllers\Api\ReservationApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| External API Routes
|--------------------------------------------------------------------------
|
| API untuk aplikasi eksternal (OTA, channel manager, dll).
| Autentikasi menggunakan API Key via header `X-API-Key`
| atau query parameter `?api_key=`.
|
| Semua response dalam format JSON.
|
*/

Route::middleware(['api', 'api.key'])->group(function () {

    // ========== RESERVATIONS ==========

    // List reservasi dengan filter & pagination
    Route::get('/reservations', [ReservationApiController::class, 'index']);

    // Detail reservasi
    Route::get('/reservations/{reservation}', [ReservationApiController::class, 'show']);

    // Buat reservasi baru
    Route::post('/reservations', [ReservationApiController::class, 'store']);

    // Update reservasi
    Route::put('/reservations/{reservation}', [ReservationApiController::class, 'update']);

    // Cancel reservasi
    Route::post('/reservations/{reservation}/cancel', [ReservationApiController::class, 'cancel']);

    // Check-in
    Route::post('/reservations/{reservation}/checkin', [ReservationApiController::class, 'checkin']);

    // Check-out
    Route::post('/reservations/{reservation}/checkout', [ReservationApiController::class, 'checkout']);

    // Pindah kamar
    Route::post('/reservations/{reservation}/change-room', [ReservationApiController::class, 'changeRoom']);

    // Tambah pembayaran
    Route::post('/reservations/{reservation}/payments', [ReservationApiController::class, 'addPayment']);

    // Update total amount
    Route::patch('/reservations/{reservation}/total', [ReservationApiController::class, 'updateTotal']);

    // Update room rate
    Route::patch('/reservations/{reservation}/room-rate', [ReservationApiController::class, 'updateRoomRate']);

    // ========== ROOMS ==========

    // List kamar dengan status
    Route::get('/rooms', [ReservationApiController::class, 'roomsIndex']);

    // Cek kamar available
    Route::get('/rooms/available', [ReservationApiController::class, 'availableRooms']);

    // ========== PROMO PRICES ==========

    // List promo prices with filters
    Route::get('/promo-prices', [PromoPriceApiController::class, 'index']);

    // Room types with promo prices
    Route::get('/promo-prices/room-types', [PromoPriceApiController::class, 'roomTypes']);

    // Check effective price for a room on specific date/range
    Route::get('/promo-prices/check', [PromoPriceApiController::class, 'checkPrice']);

    // ========== GUESTS ==========

    // List guests
    Route::get('/guests', [ReservationApiController::class, 'guestsIndex']);

    // ========== DASHBOARD STATS ==========

    Route::get('/stats', [ReservationApiController::class, 'stats']);
});

/*
|--------------------------------------------------------------------------
| API Key Management (dengan Sanctum auth)
|--------------------------------------------------------------------------
|
| Endpoint untuk generate & revoke API key.
| Hanya user yang sudah login via Sanctum session yang bisa akses.
|
*/

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    // Generate API Key baru
    Route::post('/api-keys', [ApiKeyController::class, 'store'])->name('api.v1.api-keys.store');

    // List semua API keys
    Route::get('/api-keys', [ApiKeyController::class, 'index'])->name('api.v1.api-keys.index');

    // Revoke API key
    Route::delete('/api-keys/{id}', [ApiKeyController::class, 'destroy'])->name('api.v1.api-keys.destroy');
});
