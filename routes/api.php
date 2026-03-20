<?php

use App\Http\Controllers\Api\AppointmentApiController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('/appointments', [AppointmentApiController::class, 'index']);
    Route::post('/appointments', [AppointmentApiController::class, 'store']);
    Route::get('/appointments/{appointment}', [AppointmentApiController::class, 'show']);
    Route::put('/appointments/{appointment}', [AppointmentApiController::class, 'update']);
    Route::delete('/appointments/{appointment}', [AppointmentApiController::class, 'destroy']);

    Route::get('/slots', [AppointmentApiController::class, 'slots']);
});
