<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('/personalized-pooja', 'pages.personalized-pooja')->name('personalized-pooja');
Route::view('/hawan', 'pages.hawan')->name('hawan');
Route::view('/lakshmi-pooja', 'pages.lakshmi-pooja')->name('lakshmi-pooja');
Route::view('/live', 'pages.live')->name('live');
