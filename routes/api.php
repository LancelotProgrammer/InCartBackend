<?php

use App\Http\Controllers\AdvertisementController;
use App\Http\Controllers\AuthenticationController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CityController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\PaymentMethodController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\UserAddressController;
use App\Http\Controllers\UserNotificationController;
use App\Http\Middleware\EnsureHeaderValidation;
use App\Http\Middleware\SetCurrentBranch;
use App\Http\Middleware\SetLocal;
use App\Http\Middleware\UserStateValidation;
use Illuminate\Support\Facades\Route;

Route::middleware([
    SetLocal::class,
    // EnsureHeaderValidation::class,
    SetCurrentBranch::class,
])->group(function () {

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

        Route::put('/user/update', [AuthenticationController::class, 'updateUser']);

        Route::post('/logout', [AuthenticationController::class, 'logout']);
    });

    Route::middleware(['auth:sanctum', UserStateValidation::class])->group(function () {
        Route::get('/branches', [BranchController::class, 'getBranches']);

        Route::post('/order', [OrderController::class, 'order']);
        Route::post('/checkout', [OrderController::class, 'checkout']);

        Route::get('/users/orders', [OrderController::class, 'getUserPreviousOrders']);
        Route::get('/users/notifications', [UserNotificationController::class, 'getUserNotifications']);

        Route::get('/packages', [PackageController::class, 'getPackages']);
        Route::post('/packages', [PackageController::class, 'createPackage']);
        Route::put('/packages/{id}', [PackageController::class, 'updatePackage']);
        Route::delete('/packages/{id}', [PackageController::class, 'deletePackage']);
        Route::get('/packages/{id}/products', [PackageController::class, 'getPackageProducts']);
        Route::post('/packages/{package_id}/products/{product_id}', [PackageController::class, 'addProductToPackage']);
        Route::delete('/packages/{package_id}/products/{product_id}', [PackageController::class, 'deleteProductFromPackage']);

        Route::get('/favorites/products', [FavoriteController::class, 'getFavoriteProducts']);
        Route::post('/favorites/products/{id}', [FavoriteController::class, 'addProductToFavorites']);
        Route::delete('/favorites/products/{id}', [FavoriteController::class, 'deleteProductFromFavorites']);

        Route::get('/users/addresses', [UserAddressController::class, 'getUserAddresses']);
        Route::post('/users/addresses/{id}', [UserAddressController::class, 'addUserAddress']);
        Route::delete('/users/addresses/{id}', [UserAddressController::class, 'deleteUserAddress']);
    });

    Route::get('/cities', [CityController::class, 'getCities']);
    Route::get('/home', [HomeController::class, 'getHome']);
    Route::get('/products', [ProductController::class, 'getProducts']);
    Route::get('/products/{id}', [ProductController::class, 'getProductDetails']);
    Route::get('/categories', [CategoryController::class, 'getCategories']);
    Route::get('/advertisements', [AdvertisementController::class, 'getAdvertisements']);
    Route::get('/payment-methods', [PaymentMethodController::class, 'getPaymentMethods']);
});
