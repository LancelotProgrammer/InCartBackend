<?php

use App\Http\Controllers\AuthenticationController;
use App\Http\Controllers\InvoiceController;
use Illuminate\Support\Facades\Route;

Route::get('/email/verify/{id}/{hash}', [AuthenticationController::class, 'verifyEmail'])->name('verify.email');

Route::middleware('auth')->group(function () {
    Route::middleware('auth')->get('orders/{id}/invoice', InvoiceController::class)->name('order.invoice');
    Route::get('/docs', function () {
        return view('scribe.index');
    });
});

Route::get('/', function () {
    return view('welcome');
});
