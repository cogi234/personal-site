<?php

use App\Http\Controllers\LoginController;
use Illuminate\Support\Facades\Route;


//Routes that only guests can access
Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'view'])
        ->name('login');
    Route::post('login', [LoginController::class, 'login']);
});

//Routes that only authenticated users can access
Route::middleware('auth')->group(function () {
    Route::post('logout', [LoginController::class, 'logout'])
        ->name('logout');
});