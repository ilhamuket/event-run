<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EventController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\ArtisanRunnerController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\ExportFinishController;
use App\Http\Controllers\CertificateController;
use App\Http\Controllers\FinishTimeController;





Route::get('/', [EventController::class, 'home'])->name('home');


Route::get('/events/{event:slug}/results', [EventController::class, 'results'])->name('event.results');
Route::get('/events/{event:slug}/participants', [EventController::class, 'participants'])->name('event.participants');
Route::get('/event/{event:slug}/live', [EventController::class, 'live'])
    ->name('event.live');

    // routes/web.php
Route::get('/events/{event:slug}/export-finish', ExportFinishController::class)
    ->name('event.export-finish');

Route::get('/event/{event:slug}/live/partial', [EventController::class, 'livePartial'])
    ->name('event.live.partial');

Route::get('/certificate', [CertificateController::class, 'index'])
    ->name('certificate.index');

// Endpoint AJAX lookup by BIB
Route::get('/certificate/lookup', [CertificateController::class, 'lookup'])
    ->name('certificate.lookup');

Route::post('/admin/finish-time', [FinishTimeController::class, 'update'])
    ->name('admin.finish-time.update');

// Artisan Runner (PIN protected)
Route::get('/dev/artisan', [ArtisanRunnerController::class, 'index'])->name('artisan.runner');
Route::post('/dev/artisan/run', [ArtisanRunnerController::class, 'run'])->name('artisan.runner.run');
Route::post('/dev/artisan/status', [ArtisanRunnerController::class, 'status'])->name('artisan.runner.status');
Route::post('/dev/artisan/start-worker', [ArtisanRunnerController::class, 'startWorker'])->name('artisan.runner.start-worker');
Route::post('/dev/artisan/stop-worker', [ArtisanRunnerController::class, 'stopWorker'])->name('artisan.runner.stop-worker');
Route::post('/dev/artisan/backfill-start', [ArtisanRunnerController::class, 'backfillStart'])
    ->name('artisan.runner.backfill-start');
 Route::post('/dev/artisan/normalize-finish', [ArtisanRunnerController::class, 'normalizeFinish'])
    ->name('artisan.runner.normalize-finish');


Route::get('/transactions/check-payment-status', [TransactionController::class, 'checkPaymentStatus'])
    ->name('transactions.check-payment-status');

Route::get('/transactions/check-single-status/{tripayReference}', [TransactionController::class, 'checkSingleStatus'])
    ->name('transactions.check-single-status');

// Event Registration Routes
Route::prefix('event/{event:slug}')->name('event.')->group(function () {


    Route::get('/tv',        [EventController::class, 'tvDisplay'])->name('tv');
    Route::get('/tv/lookup', [EventController::class, 'tvLookup'])->name('tv.lookup');
    Route::get('/tv/stats',  [EventController::class, 'tvStats'])->name('tv.stats');
    // Registration form
    Route::middleware('disable.registration')->group(function () {
        Route::get('/register', [RegistrationController::class, 'create'])->name('register');
        Route::post('/register', [RegistrationController::class, 'store'])->name('register.store');
    });

        Route::post('/coupon/validate', [RegistrationController::class, 'validateCoupon'])->name('coupon.validate');

    Route::get('/quota-status', [RegistrationController::class, 'checkQuotaStatus'])->name('quota.status');

    Route::get('/privacy-policy', function () {
        return view('event.privacy-policy');
    })->name('privacy-policy');

    // Payment routes
    Route::get('/payment/{ref}', [RegistrationController::class, 'showPayment'])->name('payment.show');
    Route::get('/payment/{ref}/status', [RegistrationController::class, 'checkPaymentStatus'])->name('payment.status');
    Route::get('/payment/{ref}/success', [RegistrationController::class, 'paymentSuccess'])->name('payment.success');

    // Old success route (backward compatibility)
    Route::get('/register/success', [RegistrationController::class, 'success'])->name('register.success');
});


