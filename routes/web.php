<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EventController;
use App\Http\Controllers\RegistrationController;
use App\Models\Event;

Route::get('/', [EventController::class, 'home']);


Route::get('/events/{event:slug}/results',
[EventController::class, 'results']
)->name('event.results');
Route::get('/events/{event:slug}/participants',
[EventController::class, 'participants']
)->name('event.participants');
Route::get('/events/{event:slug}/register', [RegistrationController::class, 'create'])->name('event.register');
Route::post('/events/{event:slug}/register', [RegistrationController::class, 'store'])->name('event.register.store');
Route::get('/events/{event:slug}/register/success', function (Event $event) {
    return view('event.register-success', compact('event'));
})->name('event.register.success');
