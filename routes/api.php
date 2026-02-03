<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TripayCallbackController;
use App\Http\Controllers\RfidTimingController;

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
Route::prefix('rfid')->group(function () {
    // Main scan endpoint - called by Python scanner
    Route::post('/scan', [RfidTimingController::class, 'processScan']);

    // Device configuration - called by Python scanner on startup
    Route::get('/device', [RfidTimingController::class, 'getDeviceConfig']);

    // Public results
    Route::get('/results/{categoryId}', [RfidTimingController::class, 'getLiveResults']);
    Route::get('/participant/{participantId}/times', [RfidTimingController::class, 'getParticipantTimes']);
    Route::get('/participant/by-rfid/{rfidTag}', [RfidTimingController::class, 'getParticipantByRfid']);
    Route::get('/event/{eventId}/checkpoints', [RfidTimingController::class, 'getEventCheckpoints']);
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
