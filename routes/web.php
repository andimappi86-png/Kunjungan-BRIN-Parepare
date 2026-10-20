<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::middleware('guest')->group(function () {
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store'])->name('login.store');
});

Route::middleware('auth')->group(function () {
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::patch('visits/{id}', [DashboardController::class, 'updateStatus'])->name('visits.update');
    Route::patch('visits/{id}/whatsapp', [DashboardController::class, 'toggleWhatsApp'])->name('visits.whatsapp');
    Route::post('sync-sheets', [DashboardController::class, 'syncSheets'])->name('sheets.sync');
});
