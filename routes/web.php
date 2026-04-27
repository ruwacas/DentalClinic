<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\AccountController as AdminAccountController;
use App\Http\Controllers\Admin\DentistAccountController as AdminDentistAccountController;
use App\Http\Controllers\Admin\ServiceController as AdminServiceController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Dentist\DashboardController as DentistDashboardController;
use App\Http\Controllers\Patient\AppointmentController as PatientAppointmentController;
use App\Models\Service;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => view('welcome'))->name('home');
Route::get('/services', fn () => view('services', [
    'serviceMenu' => Service::query()
        ->orderBy('category')
        ->orderBy('name')
        ->get()
        ->groupBy('category')
        ->map(fn ($services, $category) => [
            'category' => $category,
            'services' => $services->map(fn ($service) => [
                'name' => $service->name,
                'sub_services' => [],
            ])->values()->all(),
        ])
        ->values()
        ->all(),
]))->name('services');

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

Route::get('/dentist/appointments/{appointment}/status', fn () => redirect()->route('dashboard.redirect'))
    ->middleware('auth')
    ->name('dentist.appointments.status.guard');

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

    Route::get('/accounts/add-admin', [AdminAccountController::class, 'createAdmin'])->name('accounts.create');
    Route::post('/accounts/add-admin', [AdminAccountController::class, 'storeAdmin'])->name('accounts.store');
    Route::get('/accounts/edit', [AdminAccountController::class, 'editMyAccount'])->name('accounts.edit');
    Route::put('/accounts/edit', [AdminAccountController::class, 'updateMyAccount'])->name('accounts.update');

    Route::get('/accounts/dentists', [AdminDentistAccountController::class, 'index'])->name('dentists.index');
    Route::get('/accounts/dentists/add', [AdminDentistAccountController::class, 'create'])->name('dentists.create');
    Route::post('/accounts/dentists', [AdminDentistAccountController::class, 'store'])->name('dentists.store');
    Route::put('/accounts/dentists/{user}', [AdminDentistAccountController::class, 'update'])->name('dentists.update');
    Route::delete('/accounts/dentists/{user}', [AdminDentistAccountController::class, 'destroy'])->name('dentists.destroy');

    Route::get('/services/add', [AdminServiceController::class, 'create'])->name('services.create');
    Route::post('/services/add', [AdminServiceController::class, 'store'])->name('services.store');
    Route::get('/services/update', [AdminServiceController::class, 'edit'])->name('services.edit');
    Route::put('/services/update', [AdminServiceController::class, 'update'])->name('services.update');
    Route::get('/services/delete', [AdminServiceController::class, 'delete'])->name('services.delete');
    Route::delete('/services/delete', [AdminServiceController::class, 'destroy'])->name('services.destroy');

    Route::put('/appointments/{appointment}', [AdminDashboardController::class, 'updateAppointment'])->name('appointments.update');

    Route::post('/queue', [AdminDashboardController::class, 'queueWalkIn'])->name('queue.store');
    Route::put('/queue/{walkInQueue}', [AdminDashboardController::class, 'updateQueue'])->name('queue.update');
});
