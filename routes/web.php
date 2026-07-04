<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\HRDashboardController;
use App\Http\Controllers\KaryawanController;
use App\Http\Controllers\KaryawanDashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AbsensiController;
use Illuminate\Support\Facades\Route;

// Redirect root berdasarkan role
Route::get('/', function () {
    if (auth()->check()) {
        $user = auth()->user();
        if ($user->posisi === 'hr') {
            return redirect()->route('hr.dashboard');
        }
        if ($user->posisi === 'karyawan') {
            return redirect()->route('karyawan.dashboard');
        }
    }
    return redirect('/login');
});

// Guest routes
Route::middleware('guest')->group(function () {
    Route::get('login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);
});

// Auth routes
Route::middleware('auth')->group(function () {
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');

    // Profile Routes
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile/update', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/update-password', [ProfileController::class, 'updatePassword'])->name('profile.update-password');

    // HR Routes
    Route::middleware('hr')->prefix('hr')->name('hr.')->group(function () {
        Route::get('/dashboard', [HRDashboardController::class, 'index'])->name('dashboard');
        Route::resource('karyawan', KaryawanController::class);

        // Absensi Routes untuk HR
        Route::get('/absensi', [AbsensiController::class, 'index'])->name('absensi.index');
        Route::get('/absensi/export', [AbsensiController::class, 'exportExcel'])->name('absensi.export');
        Route::get('/absensi/{id}', [AbsensiController::class, 'detail'])->name('absensi.detail');
        Route::put('/absensi/{id}/status', [AbsensiController::class, 'updateStatus'])->name('absensi.update-status');
    });

    // Karyawan Routes
    Route::middleware('karyawan')->prefix('karyawan')->name('karyawan.')->group(function () {
        Route::get('/dashboard', [KaryawanDashboardController::class, 'index'])->name('dashboard');
        Route::get('/absensi', [AbsensiController::class, 'dashboard'])->name('absensi');
        Route::post('/absensi/checkin', [AbsensiController::class, 'checkIn'])->name('absensi.checkin');
        Route::post('/absensi/checkout', [AbsensiController::class, 'checkOut'])->name('absensi.checkout');
    });
});
