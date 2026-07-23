<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\ApiServiceController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CaseController;
use App\Http\Controllers\GoogleAuthController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SearchResultController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::post('/resend-otp', [AuthController::class, 'resendOtp'])->middleware('throttle:5,1');
Route::post('/verify-email-otp', [AuthController::class, 'verifyOtp'])->middleware('throttle:5,1');

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
    Route::post('/social-intel-search', [ApiServiceController::class, 'socialIntelSearch']);
    Route::post('/generate-report', [ReportController::class, 'generateReport']);
    Route::post('/generate-ai-report', [ReportController::class, 'generateAiReport']);
    Route::post('/generate-rc-report', [ReportController::class, 'generateRcReport']);
    Route::post('/generate-upi-report', [ReportController::class, 'generateUpiReport']);
    Route::post('/generate-challan-report', [ReportController::class, 'generateChallanReport']);
    Route::post('/generate-verification-report', [ReportController::class, 'generateVerificationReport']);
    Route::post('/generate-social-report', [ReportController::class, 'generateSocialReport']);

    // Route::get('/download-report/{filename}', [ReportController::class, 'downloadReport']);
});

// ADMIN ROUTES
Route::middleware(['auth:sanctum', 'token.expire', 'admin', 'throttle:60,1'])->prefix('admin')->group(function () {
    Route::get('/stats', [AdminController::class, 'stats']);
    Route::get('/users', [AdminController::class, 'users']);
    Route::post('/users', [AdminController::class, 'createUser']);
    Route::get('/users/{id}', [AdminController::class, 'user']);
    Route::put('/users/{id}', [AdminController::class, 'updateUser']);
    Route::delete('/users/{id}', [AdminController::class, 'deleteUser']);
    Route::get('/queries', [AdminController::class, 'queries']);
    Route::get('/users/{id}/queries', [AdminController::class, 'userQueries']);
    Route::get('/api-engines', [AdminController::class, 'apiEngines']);
    Route::get('/users/{id}/permissions', [AdminController::class, 'userPermissions']);
    Route::put('/users/{id}/permissions', [AdminController::class, 'updateUserPermissions']);
    Route::post('/users/{id}/sync-permissions', [AdminController::class, 'syncAdminPermissions']);
});

Route::get('/registration-status', function () {
    return response()->json([
        'registration_enabled' => config('auth.registration_enabled'),
    ]);
});

// googlelogin route
Route::post('/google-login', [GoogleAuthController::class, 'googleLogin']);

// CMS CASE ROUTES
Route::middleware(['auth:sanctum', 'token.expire'])->prefix('cases')->group(function () {
    Route::get('/', [CaseController::class, 'index']);
    Route::post('/', [CaseController::class, 'store']);
    Route::get('/{case}', [CaseController::class, 'show']);
    Route::put('/{case}', [CaseController::class, 'update']);
    Route::delete('/{case}', [CaseController::class, 'destroy']);
    Route::put('/{case}/status', [CaseController::class, 'updateStatus']);
    Route::put('/{case}/assign', [CaseController::class, 'assign']);
    Route::put('/{case}/members', [CaseController::class, 'assignUsers']);
    Route::get('/{case}/members', [CaseController::class, 'members']);
    Route::delete('/{case}/members/{user}', [CaseController::class, 'removeMember']);
    Route::get('/{case}/activities', [CaseController::class, 'activities']);
    Route::get('/{case}/searches', [CaseController::class, 'searches']);
});

// USER ACTIVE CASE ROUTES
Route::middleware(['auth:sanctum', 'token.expire'])->prefix('user')->group(function () {
    Route::get('/active-case', [UserController::class, 'getActiveCase']);
    Route::put('/set-active-case', [UserController::class, 'setActiveCase']);
    Route::delete('/active-case', [UserController::class, 'clearActiveCase']);
    Route::put('/cms-role', [UserController::class, 'updateCmsRole']);
});

// SEARCH RESULT ROUTES
Route::middleware(['auth:sanctum', 'token.expire'])->prefix('search-results')->group(function () {
    Route::get('/', [SearchResultController::class, 'index']);
    Route::post('/', [SearchResultController::class, 'store']);
    Route::get('/{searchQuery}', [SearchResultController::class, 'show']);
    Route::get('/{searchQuery}/download', [SearchResultController::class, 'download']);
});
