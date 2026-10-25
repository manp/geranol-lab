<?php

use App\Http\Controllers\ContactController;
use App\Http\Controllers\ServicesController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes — وب‌سایت عمومی
|--------------------------------------------------------------------------
| GR-103: صفحات پایه؛ GR-101: خانه/درباره/تماس با فرم تماس.
| GR-102: فهرست خدمات و جزئیات هر آزمایش از مدل TestType.
*/

Route::view('/', 'pages.home')->name('home');
Route::view('/about', 'pages.about')->name('about');
Route::get('/services', [ServicesController::class, 'index'])->name('services');
Route::get('/services/{service:slug}', [ServicesController::class, 'show'])->name('services.show');
Route::get('/contact', [ContactController::class, 'show'])->name('contact');
Route::post('/contact', [ContactController::class, 'submit'])->name('contact.submit');
