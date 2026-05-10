<?php

use App\Http\Controllers\RegistrationController;
use Illuminate\Support\Facades\Route;

// Routes publiques
Route::post('/events/{event}/register', [RegistrationController::class, 'store']);
Route::get('/events/{event}/registrations', [RegistrationController::class, 'index']);
Route::delete('/registrations/{registration}', [RegistrationController::class, 'destroy']);