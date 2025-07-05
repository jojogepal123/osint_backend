<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiServiceController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\GoogleAuthController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ReportController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);

Route::middleware(['auth'])->group(function () {
    Route::post('/email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');
});

Route::get('/verify-email/{id}/{hash}', VerifyEmailController::class)
    ->middleware(['auth', 'signed', 'throttle:6,1'])
    ->name('verification.verify');

// AUTHENTICATED USER DATA
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// API SERVICE ROUTES (Secured with `auth:sanctum` & Rate Limiting)
Route::middleware(['auth:sanctum', 'throttle:30,1'])->group(function () {
    // Route::get('/tel/{number}', [ApiServiceController::class, 'getTelData']);

    Route::get('/tel', [ApiServiceController::class, 'getTelData']);
    // Route::get('/email/{email}', [ApiServiceController::class, 'getEmailData']);
    Route::get('/email', [ApiServiceController::class, 'getEmailData']);

    Route::post('/generate-report', [ReportController::class, 'generateReport']);

    Route::post('/generate-credit-report', [ReportController::class, 'generate']);
    Route::post('/generate-ai-report', [ReportController::class, 'generateAiReport']);

    // Route::get('/download-report/{filename}', [ReportController::class, 'downloadReport']);
});


// googlelogin route
Route::post('/google-login', [GoogleAuthController::class, 'googleLogin']);
