<?php

use App\Http\Controllers\IndieAuthController;
use App\Http\Controllers\LoginController;
use Illuminate\Support\Facades\Route;


//  Routes that only guests can access
Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'view'])
        ->name('login');
    Route::post('login', [LoginController::class, 'login']);
});

//  Routes that only authenticated users can access
Route::middleware('auth')->group(function () {
    Route::post('logout', [LoginController::class, 'logout'])
        ->name('logout');

    //IndieAuth consent form
    Route::get('indieauth/consent', [IndieAuthController::class, 'authenticate'])
        ->name('indieauth.consent');
});

//  Routes that anyone can access
//IndieAuth metadata endpoint
Route::get('.well-known/oauth-authorization-server', [IndieAuthController::class, 'metadata'])
    ->name('indieauth.metadata');
//IndieAuth authorisation endpoint
Route::get('indieauth', [IndieAuthController::class, 'authenticate'])
    ->name('indieauth');
Route::post('indieauth', [IndieAuthController::class, 'getToken'])
    ->name('indieauth');