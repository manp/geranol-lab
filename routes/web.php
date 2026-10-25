<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes — وب‌سایت عمومی
|--------------------------------------------------------------------------
| صفحات placeholder گر-۱۰۳؛ محتوای واقعی در GR-101 و GR-102 ساخته می‌شود.
*/

Route::view('/', 'pages.home')->name('home');
Route::view('/services', 'pages.services')->name('services');
Route::view('/about', 'pages.about')->name('about');
Route::view('/contact', 'pages.contact')->name('contact');
