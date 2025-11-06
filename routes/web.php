<?php

use App\Http\Controllers\Api\AuthenticationController;
use App\Http\Controllers\Web\ApiDocsController;
use App\Http\Controllers\Web\LegalController;
use App\Http\Controllers\Web\OrderController;
use Illuminate\Support\Facades\Route;

Route::get('/email/verify/{id}/{hash}', [AuthenticationController::class, 'verifyEmail'])->name('verify.email');

Route::middleware('auth')->group(function () {
    Route::get('/orders/{id}/invoice', OrderController::class)->name('web.order.invoice');
    Route::get('/docs', ApiDocsController::class);
});

Route::get('/privacy-policy', [LegalController::class, 'getPolicyPage']);
Route::get('/terms-of-service', [LegalController::class, 'getTermsOfServicePage']);
Route::get('/support', [LegalController::class, 'getSupportPage'])->name('support.page');
Route::post('/support', [LegalController::class, 'createSupport'])->name('support.submit');

Route::get('/', function () {
    return view('welcome');
});
