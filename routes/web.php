<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\ServicesController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes — وب‌سایت عمومی
|--------------------------------------------------------------------------
| GR-103: صفحات پایه؛ GR-101: خانه/درباره/تماس با فرم تماس.
| GR-102: فهرست خدمات و جزئیات هر آزمایش از مدل TestType.
| GR-201: حساب مشتری (placeholder تا پاسخ پرسشنامه).
*/

Route::view('/', 'pages.home')->name('home');
Route::view('/about', 'pages.about')->name('about');
Route::get('/services', [ServicesController::class, 'index'])->name('services');
Route::get('/services/{service:slug}', [ServicesController::class, 'show'])->name('services.show');
Route::get('/contact', [ContactController::class, 'show'])->name('contact');
Route::post('/contact', [ContactController::class, 'submit'])->name('contact.submit');

/*
|--------------------------------------------------------------------------
| حساب کاربری — GR-201
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.submit');
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [AuthController::class, 'dashboardPlaceholder'])->name('dashboard.placeholder');
});
