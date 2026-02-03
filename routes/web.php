<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EventController;
use App\Http\Controllers\RegistrationController;

Route::get('/', [EventController::class, 'home'])->name('home');


Route::get('/events/{event:slug}/results', [EventController::class, 'results'])->name('event.results');
Route::get('/events/{event:slug}/participants', [EventController::class, 'participants'])->name('event.participants');

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


