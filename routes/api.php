<?php

use App\Http\Controllers\RfidTimingController;
use App\Http\Controllers\TripayCallbackController;
use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

/*
|--------------------------------------------------------------------------
| Tripay Callback
|--------------------------------------------------------------------------
*/
Route::post('/callback/tripay', [TripayCallbackController::class, 'handle'])->name('tripay.callback');

/*
|--------------------------------------------------------------------------
| RFID Timing API Routes
|--------------------------------------------------------------------------
*/

// Public routes (for scanner devices and live results)
/*
|--------------------------------------------------------------------------
| RFID Timing Routes
|--------------------------------------------------------------------------
|
| Tiga grup:
|
| 1. Scanner routes → device key auth (header X-DEVICE-KEY)
|    Dipanggil Go scanner secara otomatis. Tidak pakai session/sanctum.
|
| 2. Public routes → no auth
|    Live results, participant lookup — bisa diakses leaderboard publik.
|
| 3. Admin routes → sanctum auth + role:admin
|    Manual entry, time correction, raw logs — khusus panitia.
|
*/

// ── 1. SCANNER ROUTES ─────────────────────────────────────────────────────────
// Auth dilakukan di dalam controller via X-DEVICE-KEY header check.
// Tidak pakai middleware auth karena scanner adalah headless process,
// bukan user yang punya session.
Route::prefix('rfid')->group(function () {

    // Main endpoint: dipanggil setiap ada tag terbaca
    Route::post('/scan', [RfidTimingController::class, 'processScan'])
        ->name('rfid.scan');

    // Config endpoint: dipanggil scanner saat startup
    Route::get('/device', [RfidTimingController::class, 'getDeviceConfig'])
        ->name('rfid.device.config');

});

// ── 2. PUBLIC ROUTES ──────────────────────────────────────────────────────────
Route::prefix('rfid')->group(function () {

    // Live leaderboard per kategori
    Route::get('/results/{categoryId}', [RfidTimingController::class, 'getLiveResults'])
        ->name('rfid.results');

    // Info peserta (untuk scan bib di finish area, dll)
    Route::get('/participant/{participantId}/times', [RfidTimingController::class, 'getParticipantTimes'])
        ->name('rfid.participant.times');

    Route::get('/participant/by-rfid/{rfidTag}', [RfidTimingController::class, 'getParticipantByRfid'])
        ->name('rfid.participant.by-rfid');

    Route::get('/event/{eventId}/checkpoints', [RfidTimingController::class, 'getEventCheckpoints'])
        ->name('rfid.event.checkpoints');

    // Checkpoint monitoring (bisa publik, bisa di-protect sesuai kebutuhan)
    Route::get('/checkpoint/{checkpointId}/status', [RfidTimingController::class, 'getCheckpointStatus'])
        ->name('rfid.checkpoint.status');

});

// Protected routes (for admin panel)
Route::prefix('rfid')->middleware(['auth:sanctum'])->group(function () {
    // Checkpoint monitoring
    Route::get('/checkpoint/{checkpointId}/status', [RfidTimingController::class, 'getCheckpointStatus']);

    // Raw logs for debugging
    Route::get('/raw-logs', [RfidTimingController::class, 'getRawLogs']);

    // Manual time management
    Route::post('/manual-entry', [RfidTimingController::class, 'manualEntry']);
    Route::put('/correct-time/{validatedTimeId}', [RfidTimingController::class, 'correctTime']);
});
