<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('events', function () {
    return \App\Models\Event::withCount('registrations')->get();
});


require __DIR__ . '/events.php';
require __DIR__ . '/registrations.php';