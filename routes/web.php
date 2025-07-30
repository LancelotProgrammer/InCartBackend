<?php

use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('/docs', function () {
        return view('scribe.index');
    });
});

Route::get('/', function () {
    return view('welcome');
});
