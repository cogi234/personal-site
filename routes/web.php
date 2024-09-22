<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'homepage')
    ->name('homepage');

Route::view('/test', 'homepage')
    ->name('test');
