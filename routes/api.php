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

Route::post('/resend-otp', [AuthController::class, 'resendOtp']);
Route::post('/verify-email-otp', [AuthController::class, 'verifyOtp']);


Route::middleware(['auth'])->group(function () {
    Route::post('/email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');
});

Route::get('/verify-email/{id}/{hash}', VerifyEmailController::class)
    ->middleware(['auth', 'signed', 'throttle:6,1'])
    ->name('verification.verify');

// AUTHENTICATED USER DATA
Route::middleware(['auth:sanctum', 'token.expire'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware(['auth:sanctum', 'token.expire'])->post('/logout', [AuthController::class, 'logout']);



// API SERVICE ROUTES (Secured with `auth:sanctum` & Rate Limiting)
Route::middleware(['auth:sanctum', 'token.expire', 'throttle:30,1'])->group(function () {
    // Route::get('/tel/{number}', [ApiServiceController::class, 'getTelData']);

    Route::get('/tel', [ApiServiceController::class, 'getTelData']);
    // Route::get('/email/{email}', [ApiServiceController::class, 'getEmailData']);
    Route::get('/email', [ApiServiceController::class, 'getEmailData']);
    Route::post('/rcfull-details', [ApiServiceController::class, 'getRcFullDetails']);
    Route::post('/upifull-details', [ApiServiceController::class, 'getUpiFullDetails']);
    Route::post('/rc-challan-details', [ApiServiceController::class, 'getRcChallanDetails']);
    Route::post('/leak-data-finder', [ApiServiceController::class, 'leakDataFinder']);
    Route::post('/corporate-intelligence', [ApiServiceController::class, 'corporateData']);
    Route::post('/verification-id', [ApiServiceController::class, 'verificationIdData']);
    Route::post('/generate-report', [ReportController::class, 'generateReport']);
    Route::post('/generate-ai-report', [ReportController::class, 'generateAiReport']);
    Route::post('/generate-rc-report', [ReportController::class, 'generateRcReport']);
    Route::post('/generate-upi-report', [ReportController::class, 'generateUpiReport']);
    Route::post('/generate-challan-report', [ReportController::class, 'generateChallanReport']);

    // Route::get('/download-report/{filename}', [ReportController::class, 'downloadReport']);
});


Route::get('/registration-status', function () {
    return response()->json([
        'registration_enabled' => config('auth.registration_enabled'),
    ]);
});



// googlelogin route
Route::post('/google-login', [GoogleAuthController::class, 'googleLogin']);
