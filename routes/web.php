<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Dentist\DashboardController as DentistDashboardController;
use App\Http\Controllers\Patient\AppointmentController as PatientAppointmentController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => view('welcome'))->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login.form');
    Route::post('/login', [AuthController::class, 'login'])->name('login');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register.form');
    Route::post('/register', [AuthController::class, 'register'])->name('register');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::get('/dashboard', function () {
    $user = Auth::user();

    return match ($user->role) {
        'admin' => redirect()->route('admin.dashboard'),
        'dentist' => redirect()->route('dentist.dashboard'),
        default => redirect()->route('patient.dashboard'),
    };
})->middleware('auth')->name('dashboard.redirect');

Route::middleware(['auth', 'role:patient'])->prefix('patient')->name('patient.')->group(function () {
    Route::get('/dashboard', [PatientAppointmentController::class, 'dashboard'])->name('dashboard');
    Route::get('/profile', [PatientAppointmentController::class, 'profile'])->name('profile');
    Route::post('/profile', [PatientAppointmentController::class, 'updateProfile'])->name('profile.update');

    Route::get('/slots', [PatientAppointmentController::class, 'availableSlots'])->name('slots');
    Route::post('/appointments', [PatientAppointmentController::class, 'store'])->name('appointments.store');
    Route::put('/appointments/{appointment}/reschedule', [PatientAppointmentController::class, 'reschedule'])->name('appointments.reschedule');
    Route::put('/appointments/{appointment}/cancel', [PatientAppointmentController::class, 'cancel'])->name('appointments.cancel');
});

Route::middleware(['auth', 'role:dentist'])->prefix('dentist')->name('dentist.')->group(function () {
    Route::get('/dashboard', [DentistDashboardController::class, 'index'])->name('dashboard');
    Route::post('/availability', [DentistDashboardController::class, 'saveAvailability'])->name('availability.store');
    Route::put('/availability/{availability}', [DentistDashboardController::class, 'updateAvailability'])->name('availability.update');
    Route::delete('/availability/{availability}', [DentistDashboardController::class, 'deleteAvailability'])->name('availability.delete');
    Route::put('/appointments/{appointment}/status', [DentistDashboardController::class, 'updateStatus'])->name('appointments.status');
    Route::post('/appointments/{appointment}/notes', [DentistDashboardController::class, 'addNote'])->name('appointments.notes');
});

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    Route::post('/dentists', [AdminDashboardController::class, 'upsertDentist'])->name('dentists.store');
    Route::put('/dentists/{user}', [AdminDashboardController::class, 'upsertDentist'])->name('dentists.update');

    Route::put('/appointments/{appointment}', [AdminDashboardController::class, 'updateAppointment'])->name('appointments.update');

    Route::post('/queue', [AdminDashboardController::class, 'queueWalkIn'])->name('queue.store');
    Route::put('/queue/{walkInQueue}', [AdminDashboardController::class, 'updateQueue'])->name('queue.update');
});
