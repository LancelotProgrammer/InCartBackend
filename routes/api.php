<?php

use App\Http\Controllers\AdvertisementController;
use App\Http\Controllers\AuthenticationController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Middleware\EnsureHeaderValidation;
use App\Http\Middleware\UserStateValidation;
use Illuminate\Support\Facades\Route;
use Laravel\Telescope\Http\Controllers\HomeController;

Route::middleware(EnsureHeaderValidation::class)->group(function () {
    Route::prefix('/auth')->group(function () {
        Route::post('/email/register', [AuthenticationController::class, 'emailRegister']);
        Route::post('/email/login', [AuthenticationController::class, 'emailLogin']);

        Route::post('/phone/register', [AuthenticationController::class, 'phoneRegister']);
        Route::post('/phone/login', [AuthenticationController::class, 'phoneLogin']);

        Route::post('/verify-otp', [AuthenticationController::class, 'verifyOtp']);

        Route::post('/request-forget-password', [AuthenticationController::class, 'forgotPasswordRequest']);
        Route::post('/verify-forget-password', [AuthenticationController::class, 'verifyForgetPasswordRequest']);
        Route::post('/reset-forget-password', [AuthenticationController::class, 'resetPasswordRequest']);

        Route::post('/refresh/token', [AuthenticationController::class, 'refreshTokenRequest']);
    });

    Route::middleware(['auth:sanctum', UserStateValidation::class])->prefix('/auth')->group(function () {
        Route::get('/email/request-verify', [AuthenticationController::class, 'getVerifyEmail']);

        Route::get('/user', [AuthenticationController::class, 'getUser']);

        Route::post('/firebase-token', [AuthenticationController::class, 'createFirebaseToken']);

        Route::put('/user/phone', [AuthenticationController::class, 'updateUserPhone']);
        Route::put('/user/email', [AuthenticationController::class, 'updateUserEmail']);
        Route::put('/user/name', [AuthenticationController::class, 'updateUserName']);
        Route::put('/user/password', [AuthenticationController::class, 'updateUserPassword']);

        Route::post('/logout', [AuthenticationController::class, 'logout']);
    });

    Route::get('/home', [HomeController::class, 'home']);
    Route::get('/products', [ProductController::class, 'getProducts']);
    Route::get('/Categories', [CategoryController::class, 'getCategories']);
    Route::get('/Advertisements', [AdvertisementController::class, 'getAdvertisements']);

    // Route::middleware(['auth:sanctum', UserStateValidation::class])->group(function () {
    //     Route::get('/order', [AuthenticationController::class, 'order']);
    // });
});
