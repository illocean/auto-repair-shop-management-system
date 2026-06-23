<?php

use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\AuditController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RepairOrderController;
use App\Http\Controllers\ServiceTypeController;
use App\Http\Controllers\SupplyController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\VehicleController;
use Illuminate\Support\Facades\Route;

// Guest routes — no session required
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'loginPage'])->name('login');
    Route::get('/', [AuthController::class, 'loginPage']);
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    Route::get('/register', [AuthController::class, 'registerPage'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.post');
});

// Authenticated routes — require session
Route::middleware('auth.session')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('customers', CustomerController::class);
    Route::resource('vehicles', VehicleController::class);
    Route::resource('repair-orders', RepairOrderController::class);
    Route::get('repair-orders/{customerId}/vehicles', [RepairOrderController::class, 'getByCustomer'])->name('repair-orders.vehicles');
    Route::get('repair-orders/{id}/remove-service/{serviceId}', [RepairOrderController::class, 'removeService'])->name('repair-orders.remove-service');
    Route::resource('service-types', ServiceTypeController::class);

    Route::get('appointments/calendar', [AppointmentController::class, 'calendar'])->name('appointments.calendar');
    Route::get('appointments/{customerId}/vehicles', [AppointmentController::class, 'getByCustomer'])->name('appointments.vehicles');
    Route::resource('appointments', AppointmentController::class);

    Route::resource('supplies', SupplyController::class);

    Route::resource('users', UsersController::class);
    Route::get('/audit', [AuditController::class, 'index'])->name('audit.index');
});
