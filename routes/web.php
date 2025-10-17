<?php

use App\Http\Controllers\Api\AuthenticationController;
use App\Http\Controllers\Web\ApiDocsController;
use App\Http\Controllers\Web\OrderController;
use Illuminate\Support\Facades\Route;

Route::get('/email/verify/{id}/{hash}', [AuthenticationController::class, 'verifyEmail'])->name('verify.email');

Route::middleware('auth')->group(function () {
    Route::get('/orders/{id}/invoice', OrderController::class)->name('web.order.invoice');
    Route::get('/docs', ApiDocsController::class);
});

Route::get('/', function () {
    return view('welcome');
});
