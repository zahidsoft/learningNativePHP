<?php

use App\NativeComponents\Category;
use App\NativeComponents\Home;
use App\NativeComponents\Letter;
use App\NativeComponents\Quiz;
use App\NativeComponents\Quran\Bookmarks;
use App\NativeComponents\Quran\KhatamProgress;
use App\NativeComponents\Quran\MushafPage;
use App\NativeComponents\Quran\QuranSearch;
use App\NativeComponents\Quran\QuranSettings;
use App\NativeComponents\Quran\SurahList;
use App\NativeComponents\Quran\SurahReader;
use App\NativeComponents\Spelling;
use Illuminate\Support\Facades\Route;

// Replaces the web "home" route wherever the native routes load, so it keeps the name.
Route::native('/', Home::class)->name('home');
Route::native('/learn/{category}', Category::class);
Route::native('/learn/{category}/{index}', Letter::class);
Route::native('/quiz', Quiz::class);
Route::native('/spell', Spelling::class);

Route::native('/quran', SurahList::class);
Route::native('/quran/surah/{number}', SurahReader::class);
Route::native('/quran/surah/{number}/{ayah}', SurahReader::class);
Route::native('/quran/page/{page}', MushafPage::class);
Route::native('/quran/search', QuranSearch::class);
Route::native('/quran/bookmarks', Bookmarks::class);
Route::native('/quran/khatam', KhatamProgress::class);
Route::native('/quran/settings', QuranSettings::class);
