<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'homepage')
    ->name('homepage');

Route::view('/blog', 'homepage')
    ->name('blog');

Route::view('/notes', 'homepage')
    ->name('notes');

Route::view('/tags', 'homepage')
    ->name('tags');

Route::view('/about', 'homepage')
    ->name('about');