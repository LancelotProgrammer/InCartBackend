<?php

use App\Http\Controllers\AuthenticationController;
use Illuminate\Support\Facades\Route;

Route::prefix('/auth')->group(function () {
    Route::get('/verify-email', [AuthenticationController::class, 'verifyEmail']);
});

Route::middleware('auth')->group(function () {
    Route::get('/docs', function () {
        return view('scribe.index');
    });
});

Route::get('/', function () {
    return view('welcome');
});
