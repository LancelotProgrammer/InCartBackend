<?php

use App\Http\Controllers\AdvertisementController;
use App\Http\Controllers\AuthenticationController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CityController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Middleware\EnsureHeaderValidation;
use App\Http\Middleware\UserStateValidation;
use Illuminate\Support\Facades\Route;

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

    Route::middleware(['auth:sanctum', UserStateValidation::class])->prefix('/auth')->group(function () {
        Route::get('/home', [HomeController::class, 'getHome']);
        Route::get('/cities', [CityController::class, 'getCities']);
        Route::get('/branches', [BranchController::class, 'getBranches']);
        Route::get('/products', [ProductController::class, 'getProducts']);
        Route::get('/Categories', [CategoryController::class, 'getCategories']);
        Route::get('/Advertisements', [AdvertisementController::class, 'getAdvertisements']);

        Route::post('/order', [OrderController::class, 'order']);
        Route::post('/checkout', [OrderController::class, 'checkout']);
    });
});
