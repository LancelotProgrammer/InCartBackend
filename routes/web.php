<?php

use App\Http\Controllers\AuthenticationController;
use Illuminate\Support\Facades\Route;

Route::get('/email/verify/{id}/{hash}', [AuthenticationController::class, 'verifyEmail'])->name('verify.email');

Route::middleware('auth')->group(function () {
    Route::get('/docs', function () {
        return view('scribe.index');
    });
});

Route::get('/', function () {
    return view('welcome');
});
