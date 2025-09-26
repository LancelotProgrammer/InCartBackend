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
use App\Http\Controllers\SettingController;
use App\Http\Controllers\TicketAndFeedbackController;
use App\Http\Controllers\UserAddressController;
use App\Http\Controllers\UserNotificationController;
use App\Http\Middleware\EnsureHeaderValidation;
use App\Http\Middleware\IsServiceOnline;
use App\Http\Middleware\SetCurrentBranch;
use App\Http\Middleware\SetLocal;
use Illuminate\Support\Facades\Route;

Route::middleware([
    // EnsureHeaderValidation::class,
    IsServiceOnline::class,
    SetLocal::class,
    SetCurrentBranch::class,
])->group(function () {

    Route::prefix('/auth')->group(function () {
        Route::post('/email/register', [AuthenticationController::class, 'emailRegister']);
        Route::post('/email/login', [AuthenticationController::class, 'emailLogin']);

        Route::post('/phone/register', [AuthenticationController::class, 'phoneRegister']);
        Route::post('/phone/login', [AuthenticationController::class, 'phoneLogin']);

        Route::post('/send-otp', [AuthenticationController::class, 'sendOtp']);

        Route::post('/request-forget-password', [AuthenticationController::class, 'forgotPasswordRequest']);
        Route::post('/verify-forget-password', [AuthenticationController::class, 'verifyForgetPasswordRequest']);
        Route::post('/reset-forget-password', [AuthenticationController::class, 'resetPasswordRequest']);
    });

    Route::middleware(['auth:sanctum'])->prefix('/auth')->group(function () {
        Route::get('/email/request-verify', [AuthenticationController::class, 'getVerifyEmail']);
        Route::get('/user', [AuthenticationController::class, 'getUser']);
        // Route::post('/user/credentials', [AuthenticationController::class, 'addCredentials']);
        Route::post('/firebase-token', [AuthenticationController::class, 'createFirebaseToken']);
        Route::put('/user/update', [AuthenticationController::class, 'updateUser']);
        Route::post('/logout', [AuthenticationController::class, 'logout']);
    });

    Route::middleware(['auth:sanctum'])->group(function () {
        Route::get('/branches', [BranchController::class, 'getBranches']);

        Route::post('/advertisements/{id}/click', [AdvertisementController::class, 'createAdvertisementClick']);

        Route::post('/order/bill', [OrderController::class, 'createOrderBill']);
        Route::post('/order', [OrderController::class, 'createOrder']);
        Route::post('/order/checkout', [OrderController::class, 'createOrderCheckout']);
        Route::post('/order/{id}/cancel', [OrderController::class, 'cancelOrder']);
        Route::get('/order/{id}', [OrderController::class, 'getOrderDetails']);
        Route::get('/users/orders', [OrderController::class, 'getUserPreviousOrders']);

        Route::get('/users/notifications', [UserNotificationController::class, 'getUserNotifications']);
        Route::post('/users/notifications/{id}/read', [UserNotificationController::class, 'markUserNotificationAsRead']);

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
        Route::post('/users/addresses', [UserAddressController::class, 'createUserAddress']);
        Route::put('/users/addresses/{id}', [UserAddressController::class, 'updateUserAddress']);
        Route::delete('/users/addresses/{id}', [UserAddressController::class, 'deleteUserAddress']);

        Route::get('/users/tickets', [TicketAndFeedbackController::class, 'getTickets']);
        Route::post('/users/tickets', [TicketAndFeedbackController::class, 'createTicket']);
        Route::post('/users/feedback', [TicketAndFeedbackController::class, 'createFeedback']);
    });

    Route::get('/cities', [CityController::class, 'getCities']);
    Route::get('/home', [HomeController::class, 'getHome']);
    Route::get('/products', [ProductController::class, 'getProducts']);
    Route::get('/products/{id}', [ProductController::class, 'getProductDetails']);
    Route::get('/categories', [CategoryController::class, 'getCategories']);
    Route::get('/advertisements', [AdvertisementController::class, 'getAdvertisements']);
    Route::get('/payment-methods', [PaymentMethodController::class, 'getPaymentMethods']);
    Route::get('/settings', [SettingController::class, 'getSettings']);

    Route::post('/moyasar/callback', [OrderController::class, 'paymentGatewayCallback'])->name('moyasar.callback');
});
