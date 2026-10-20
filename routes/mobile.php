<?php

use App\NativeComponents\Category;
use App\NativeComponents\Home;
use App\NativeComponents\Letter;
use App\NativeComponents\Quiz;
use App\NativeComponents\Spelling;
use Illuminate\Support\Facades\Route;

// Replaces the web "home" route wherever the native routes load, so it keeps the name.
Route::native('/', Home::class)->name('home');
Route::native('/learn/{category}', Category::class);
Route::native('/learn/{category}/{index}', Letter::class);
Route::native('/quiz', Quiz::class);
Route::native('/spell', Spelling::class);
