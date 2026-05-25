<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Dashboard\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware('guest.session')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
});

Route::get('/forgot-account', [AuthController::class, 'showForgotAccount'])->name('forgot-account');
Route::post('/forgot-account', [AuthController::class, 'forgotAccount'])->name('forgot-account.store');

Route::get('/dashboard', DashboardController::class)->name('dashboard');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
