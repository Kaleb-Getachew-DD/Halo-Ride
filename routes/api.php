<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use App\Http\Controllers\OTPController;

    // Public routes for Authentication
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/forgot-password', [AuthController::class, 'forgot']);
    Route::post('/customer/register', [AuthController::class, 'register_customer']);
    Route::post('/customer/login', [AuthController::class, 'login_customer']);

    // OTP routes Afromessage Service
    Route::post('/customer/send/otp', [OTPController::class, 'send']);
    Route::post('/customer/verify/otp', [OTPController::class, 'verify']);

Route::middleware(['auth.jwt'])->group(function () {

    // Protected routes for Authentication
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/update-profile', [AuthController::class, 'update_profile']);
    Route::get('/refresh', [AuthController::class, 'refresh']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::middleware(['access:Admin,Driver'])->group(function () {

        // Admin and Driver routes for Authentication
        Route::post('/staff/register', [AuthController::class, 'register']);
        Route::post('/backoffice/register', [AuthController::class, 'registerBackoffice']);
    });
});

// =====================
// Booking Routes
// =====================
Route::middleware(['auth.jwt'])->group(function () {
    
});

// =====================
// Reservation Routes
// =====================
Route::middleware(['auth:jwt'])->group(function () {
    // Admin Reservations
    Route::middleware(['access:Admin,Driver'])->group(function () {

    });
});

// =====================
// Payment Routes
// =====================
Route::middleware(['auth:jwt'])->group(function () {
    
});