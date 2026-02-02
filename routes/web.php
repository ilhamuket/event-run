<?php

use App\Models\Event;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EventController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\TripayCallbackController;

Route::get('/', [EventController::class, 'home'])->name('home');


Route::get('/events/{event:slug}/results', [EventController::class, 'results'])->name('event.results');
Route::get('/events/{event:slug}/participants', [EventController::class, 'participants'])->name('event.participants');
// Route::get('/events/{event:slug}/register', [RegistrationController::class, 'create'])->name('event.register');
// Route::post('/events/{event:slug}/register', [RegistrationController::class, 'store'])->name('event.register.store');
// Route::get('/events/{event:slug}/register/success', fn (Event $event) => view('event.register-success', compact('event')))->name('event.register.success');


// Event Registration Routes
Route::prefix('event/{event:slug}')->name('event.')->group(function () {
    // Registration form
    Route::get('/register', [RegistrationController::class, 'create'])->name('register');
    Route::post('/register', [RegistrationController::class, 'store'])->name('register.store');

    // Payment routes
    Route::get('/payment/{ref}', [RegistrationController::class, 'showPayment'])->name('payment.show');
    Route::get('/payment/{ref}/status', [RegistrationController::class, 'checkPaymentStatus'])->name('payment.status');
    Route::get('/payment/{ref}/success', [RegistrationController::class, 'paymentSuccess'])->name('payment.success');

    // Old success route (backward compatibility)
    Route::get('/register/success', [RegistrationController::class, 'success'])->name('register.success');
});


/*
|--------------------------------------------------------------------------
| API Routes - Tripay Callback
|--------------------------------------------------------------------------
|
| Add this route to your routes/api.php or routes/web.php
| Make sure to exclude this route from CSRF verification
|
*/

// Tripay Callback (add to routes/api.php or exclude from CSRF)
Route::post('/callback/tripay', [TripayCallbackController::class, 'handle'])->name('tripay.callback');
