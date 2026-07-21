<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\HRDashboardController;
use App\Http\Controllers\KaryawanController;
use App\Http\Controllers\KaryawanDashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AbsensiController;
use App\Http\Controllers\CutiController;
use App\Http\Controllers\FhlController;
use App\Http\Controllers\PengumumanController;
use App\Http\Controllers\SunnahController;
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
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    // Forgot Password Routes
    Route::get('forgot-password', [ForgotPasswordController::class, 'showForgotForm'])->name('password.request');
    Route::post('forgot-password/verify', [ForgotPasswordController::class, 'verifyEmail'])->name('password.verify');
    Route::get('reset-password', [ForgotPasswordController::class, 'showResetForm'])->name('password.reset.form');
    Route::post('reset-password', [ForgotPasswordController::class, 'resetPassword'])->name('password.reset');
    Route::get('refresh-captcha', [ForgotPasswordController::class, 'refreshCaptcha'])->name('refresh.captcha');
});

// Auth routes
Route::middleware('auth')->group(function () {
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    // Profile Routes
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile/update', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/update-password', [ProfileController::class, 'updatePassword'])->name('profile.update-password');

    // HR Routes
    Route::middleware('hr')
        ->prefix('hr')
        ->name('hr.')
        ->group(function () {
            Route::get('/dashboard', [HRDashboardController::class, 'index'])->name('dashboard');
            Route::resource('karyawan', KaryawanController::class);

            Route::get('/absensi', [AbsensiController::class, 'index'])->name('absensi.index');
            Route::get('/absensi/export', [AbsensiController::class, 'exportExcel'])->name('absensi.export');
            Route::get('/absensi/{id}', [AbsensiController::class, 'detail'])->name('absensi.detail');
            Route::put('/absensi/{id}/status', [AbsensiController::class, 'updateStatus'])->name('absensi.update-status');

            Route::resource('pengumuman', PengumumanController::class);
            Route::get('/pengumuman/{id}/send-whatsapp', [PengumumanController::class, 'sendWhatsApp'])->name('pengumuman.send-whatsapp');
            Route::get('/pengumuman/{id}/send-whatsapp/{phone}', [PengumumanController::class, 'sendWhatsAppToNumber'])->name('pengumuman.send-whatsapp-number');
            Route::get('/pengumuman/{id}/select-contact', [PengumumanController::class, 'selectContact'])->name('pengumuman.select-contact');
            Route::post('/pengumuman/{id}/resend-whatsapp', [PengumumanController::class, 'resendWhatsApp'])->name('pengumuman.resend-whatsapp');

            Route::get('/fhl', [FhlController::class, 'index'])->name('fhl.index');
            Route::get('/fhl/detail/{id}', [FhlController::class, 'detail'])->name('fhl.detail');

            Route::get('/sunnah', [SunnahController::class, 'index'])->name('sunnah.index');
            Route::get('/sunnah/rekap', [SunnahController::class, 'rekapBulanan'])->name('sunnah.rekap');
            Route::get('/sunnah/detail/{id}', [SunnahController::class, 'detail'])->name('sunnah.detail');
            Route::post('/sunnah/approve/{id}', [SunnahController::class, 'approve'])->name('sunnah.approve');
            Route::post('/sunnah/bulk-approve', [SunnahController::class, 'bulkApprove'])->name('sunnah.bulk-approve');

            Route::get('/cuti', [CutiController::class, 'index'])->name('cuti.index');
            Route::get('/cuti/{id}', [CutiController::class, 'show'])->name('cuti.show');
            Route::post('/cuti/approve/{id}', [CutiController::class, 'approve'])->name('cuti.approve');
            Route::post('/cuti/bulk-approve', [CutiController::class, 'bulkApprove'])->name('cuti.bulk-approve');
        });

    // Karyawan Routes
    Route::middleware('karyawan')
        ->prefix('karyawan')
        ->name('karyawan.')
        ->group(function () {
            Route::get('/dashboard', [KaryawanDashboardController::class, 'index'])->name('dashboard');
            Route::get('/absensi', [AbsensiController::class, 'dashboard'])->name('absensi');

            Route::post('/absensi/checkin', [AbsensiController::class, 'checkIn'])->name('absensi.checkin')->middleware('throttle:10,1');
            Route::post('/absensi/checkout', [AbsensiController::class, 'checkOut'])->name('absensi.checkout')->middleware('throttle:10,1');
            Route::get('/absensi/status', [AbsensiController::class, 'status'])->name('absensi.status');
            Route::get('/absensi/server-time', [AbsensiController::class, 'serverTime'])->name('absensi.server-time');

            Route::get('/fhl', [FhlController::class, 'dashboard'])->name('fhl.dashboard');
            Route::post('/fhl/checkin', [FhlController::class, 'checkIn'])->name('fhl.checkin');

            Route::get('/sunnah', [SunnahController::class, 'dashboard'])->name('sunnah.dashboard');
            Route::post('/sunnah/save', [SunnahController::class, 'saveDaily'])->name('sunnah.save');

            Route::get('/cuti', [CutiController::class, 'dashboard'])->name('cuti.dashboard');
            Route::get('/cuti/create', [CutiController::class, 'create'])->name('cuti.create');
            Route::post('/cuti/store', [CutiController::class, 'store'])->name('cuti.store');
        });
});
