<?php

use App\Http\Controllers\ContactController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes — وب‌سایت عمومی
|--------------------------------------------------------------------------
| GR-103: صفحات پایه؛ GR-101: خانه/درباره/تماس با فرم تماس.
| GR-102 صفحهٔ خدمات را از مدل TestType می‌سازد.
*/

Route::view('/', 'pages.home')->name('home');
Route::view('/about', 'pages.about')->name('about');
Route::view('/services', 'pages.services')->name('services');
Route::get('/contact', [ContactController::class, 'show'])->name('contact');
Route::post('/contact', [ContactController::class, 'submit'])->name('contact.submit');
