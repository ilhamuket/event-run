<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EventController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\ArtisanRunnerController;


Route::get('/', [EventController::class, 'home'])->name('home');


Route::get('/events/{event:slug}/results', [EventController::class, 'results'])->name('event.results');
Route::get('/events/{event:slug}/participants', [EventController::class, 'participants'])->name('event.participants');
Route::get('/event/{event:slug}/live', [EventController::class, 'live'])->name('event.live');
// Artisan Runner (PIN protected)
Route::get('/dev/artisan', [ArtisanRunnerController::class, 'index'])->name('artisan.runner');
Route::post('/dev/artisan/run', [ArtisanRunnerController::class, 'run'])->name('artisan.runner.run');

// Event Registration Routes
Route::prefix('event/{event:slug}')->name('event.')->group(function () {
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


