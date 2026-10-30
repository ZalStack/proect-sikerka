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
use App\Http\Controllers\KhatamanController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PengumumanController;
use App\Http\Controllers\PerjalananDinasController;
use App\Http\Controllers\SunnahController;
use App\Http\Controllers\SuperAdminController;
use App\Http\Controllers\SuperAdmin\ActivityLogController;
use App\Http\Controllers\SuperAdmin\BackupController;
use App\Http\Controllers\SuperAdmin\SystemHealthController;
use App\Http\Controllers\SuperAdmin\SystemStatisticController;
use Illuminate\Support\Facades\Route;

// Redirect root berdasarkan role
Route::get('/', function () {
    if (auth()->check()) {
        $user = auth()->user();
        if ($user->posisi === 'superadmin') {
            return redirect()->route('superadmin.dashboard');
        }
        if ($user->posisi === 'hr') {
            return redirect()->route('hr.dashboard');
        }
        if ($user->posisi === 'karyawan') {
            return redirect()->route('karyawan.dashboard');
        }
    }
    return redirect('/login');
});

// Login & Authentication routes
Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
Route::post('login', [AuthenticatedSessionController::class, 'store']);
Route::get('superadmin/login', [AuthenticatedSessionController::class, 'create'])->name('superadmin.login');
Route::post('superadmin/login', [AuthenticatedSessionController::class, 'store'])->name('superadmin.login.store');
Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

// Lupa password & reset password via link.
// Sengaja TANPA middleware 'guest': halaman harus selalu bisa dibuka,
// termasuk saat Super Admin sedang login (mis. mengetes link hasil generate)
// atau saat sesi lama masih aktif — agar form reset tidak pernah ter-redirect.
Route::get('forgot-password', [ForgotPasswordController::class, 'showForgotForm'])->name('password.request');
Route::get('reset-password/{token}', [ForgotPasswordController::class, 'showResetForm'])->name('password.reset.form');
Route::post('reset-password/{token}', [ForgotPasswordController::class, 'resetPassword'])->name('password.reset');

// Auth routes
Route::middleware('auth')->group(function () {

    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile/update', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/profile/change-password', [ProfileController::class, 'showChangePassword'])->name('profile.change-password');
    Route::put('/profile/update-password', [ProfileController::class, 'updatePassword'])->name('profile.update-password');
    Route::get('/profile/achievement', [ProfileController::class, 'achievement'])->name('profile.achievement');

    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/latest', [NotificationController::class, 'latest'])->name('notifications.latest');
    Route::post('/notifications/mark-read', [NotificationController::class, 'markRead'])->name('notifications.mark-read');

    // ==========================================
    // SUPER ADMIN ROUTES
    // ==========================================
    Route::middleware('superadmin')
        ->prefix('superadmin')
        ->name('superadmin.')
        ->group(function () {
            Route::get('/dashboard', [SuperAdminController::class, 'dashboard'])->name('dashboard');

            // Kontrol Fitur & Maintenance
            Route::get('/features', [SuperAdminController::class, 'featuresIndex'])->name('features.index');
            Route::post('/features/toggle-all', [SuperAdminController::class, 'featureToggleAll'])->name('features.toggle-all');
            Route::post('/features/{feature}/toggle', [SuperAdminController::class, 'featureToggle'])->name('features.toggle');
            Route::post('/maintenance/toggle', [SuperAdminController::class, 'maintenanceToggle'])->name('maintenance.toggle');
            Route::match(['POST', 'PUT'], '/maintenance/update', [SuperAdminController::class, 'maintenanceUpdate'])->name('maintenance.update');

            // Kelola & Koreksi Jam Presensi Karyawan
            Route::get('/absensi', [SuperAdminController::class, 'absensiIndex'])->name('absensi.index');
            Route::post('/absensi/store', [SuperAdminController::class, 'absensiStore'])->name('absensi.store');
            Route::match(['PUT', 'POST'], '/absensi/{id}/update', [SuperAdminController::class, 'absensiUpdate'])->name('absensi.update');
            Route::delete('/absensi/{id}', [SuperAdminController::class, 'absensiDestroy'])->name('absensi.destroy');

            // Kelola Role Pengguna
            Route::get('/karyawan', [SuperAdminController::class, 'karyawanIndex'])->name('karyawan.index');
            Route::match(['PUT', 'POST'], '/karyawan/{id}/role', [SuperAdminController::class, 'karyawanUpdateRole'])->name('karyawan.update-role');

            // Generate link reset password (lupa password) untuk karyawan / HR
            Route::post('/karyawan/{id}/generate-reset-link', [SuperAdminController::class, 'generateResetLink'])->name('karyawan.generate-reset-link');

            // Pelanggaran Presensi (Anti Remote Access: TeamViewer / AnyDesk / RDP dll.)
            Route::get('/pelanggaran', [SuperAdminController::class, 'violationsIndex'])->name('violations.index');
            Route::post('/pelanggaran/{id}/confirm', [SuperAdminController::class, 'violationsConfirm'])->name('violations.confirm');
            Route::post('/pelanggaran/{id}/dismiss', [SuperAdminController::class, 'violationsDismiss'])->name('violations.dismiss');

            // Blokir Presensi Karyawan: aktifkan kembali (unban) & kirim pesan alert
            Route::post('/karyawan/{id}/unban', [SuperAdminController::class, 'karyawanUnban'])->name('karyawan.unban');
            Route::post('/karyawan/{id}/ban-message', [SuperAdminController::class, 'karyawanBanMessage'])->name('karyawan.ban-message');

            // Manajemen Akses Karyawan: suspend akun (rentang tanggal) & hak akses menu
            Route::get('/akses-karyawan', [SuperAdminController::class, 'accessIndex'])->name('access.index');
            Route::post('/akses-karyawan/{id}/suspend', [SuperAdminController::class, 'accessSuspend'])->name('access.suspend');
            Route::post('/akses-karyawan/{id}/unsuspend', [SuperAdminController::class, 'accessUnsuspend'])->name('access.unsuspend');
            Route::post('/akses-karyawan/{id}/menus', [SuperAdminController::class, 'accessUpdateMenus'])->name('access.menus');

            // Kelola Hari Libur & Tanggal Merah
            Route::get('/hari-libur', [SuperAdminController::class, 'hariLiburIndex'])->name('hari-libur.index');
            Route::post('/hari-libur', [SuperAdminController::class, 'hariLiburStore'])->name('hari-libur.store');
            Route::delete('/hari-libur/{id}', [SuperAdminController::class, 'hariLiburDestroy'])->name('hari-libur.destroy');
            Route::post('/hari-libur/sync', [SuperAdminController::class, 'hariLiburSync'])->name('hari-libur.sync');

            // ==========================================
            // PEMELIHARAAN IT
            // ==========================================

            // Backup Database
            Route::get('/backup', [BackupController::class, 'index'])->name('backup.index');
            Route::post('/backup', [BackupController::class, 'store'])->name('backup.store');
            Route::get('/backup/{filename}/download', [BackupController::class, 'download'])
                ->where('filename', '[A-Za-z0-9._-]+')
                ->name('backup.download');
            Route::delete('/backup/{filename}', [BackupController::class, 'destroy'])
                ->where('filename', '[A-Za-z0-9._-]+')
                ->name('backup.destroy');

            // Statistik Penggunaan Sistem
            Route::get('/statistik-penggunaan', [SystemStatisticController::class, 'index'])->name('stats.index');

            // Log Aktivitas / Audit Trail
            Route::get('/log-aktivitas', [ActivityLogController::class, 'index'])->name('logs.index');
            Route::get('/log-aktivitas/export', [ActivityLogController::class, 'export'])->name('logs.export');

            // Kesehatan Sistem & Tools Maintenance
            Route::get('/kesehatan-sistem', [SystemHealthController::class, 'index'])->name('health.index');
            Route::post('/kesehatan-sistem/cache', [SystemHealthController::class, 'clearCache'])->name('health.cache');
            Route::post('/kesehatan-sistem/tool', [SystemHealthController::class, 'runTool'])->name('health.tool');
        });

    // ==========================================
    // HR ROUTES
    // ==========================================
    Route::middleware('hr')
        ->prefix('hr')
        ->name('hr.')
        ->group(function () {
            Route::get('/dashboard', [HRDashboardController::class, 'index'])->name('dashboard');
            Route::resource('karyawan', KaryawanController::class);

            // Presensi
            Route::middleware('feature:absensi')->group(function () {
                Route::get('/absensi', [AbsensiController::class, 'index'])->name('absensi.index');
                Route::get('/absensi/resume', [AbsensiController::class, 'resume'])->name('absensi.resume');
                Route::get('/absensi/export', [AbsensiController::class, 'exportExcel'])->name('absensi.export');
                Route::get('/absensi/verifikasi', [AbsensiController::class, 'verifikasiIndex'])->name('absensi.verifikasi');
                Route::post('/absensi/verifikasi/store', [AbsensiController::class, 'verifikasiStore'])->name('absensi.verifikasi.store');
                Route::get('/absensi/{id}', [AbsensiController::class, 'detail'])->name('absensi.detail');
                Route::put('/absensi/{id}/status', [AbsensiController::class, 'updateStatus'])->name('absensi.update-status');
                Route::post('/absensi/{id}/verifikasi-checkin', [AbsensiController::class, 'manualCheckIn'])->name('absensi.verifikasi-checkin');
                Route::post('/absensi/{id}/verifikasi-checkout', [AbsensiController::class, 'manualCheckOut'])->name('absensi.verifikasi-checkout');
            });

            // Perizinan (review & approval pengajuan izin/sakit karyawan)
            Route::middleware('feature:perizinan')->group(function () {
                Route::get('/perizinan', [AbsensiController::class, 'perizinanIndex'])->name('perizinan.index');
                Route::post('/perizinan/{id}/approve', [AbsensiController::class, 'perizinanApprove'])->name('perizinan.approve');
                Route::post('/perizinan/{id}/reject', [AbsensiController::class, 'perizinanReject'])->name('perizinan.reject');
                Route::post('/perizinan/{id}/reset', [AbsensiController::class, 'perizinanReset'])->name('perizinan.reset');
            });

            // Pengumuman
            Route::middleware('feature:pengumuman')->group(function () {
                Route::resource('pengumuman', PengumumanController::class);
            });

            // FHL
            Route::middleware('feature:fhl')->group(function () {
                Route::get('/fhl', [FhlController::class, 'index'])->name('fhl.index');
                Route::post('/fhl/generate-kode', [FhlController::class, 'generateKode'])->name('fhl.generate-kode');
                Route::get('/fhl/detail/{id}', [FhlController::class, 'detail'])->name('fhl.detail');
                Route::get('/fhl/config', [FhlController::class, 'config'])->name('fhl.config');
                Route::post('/fhl/config', [FhlController::class, 'config'])->name('fhl.config.update');
            });

            // Khataman
            Route::middleware('feature:khataman')->group(function () {
                Route::get('/khataman', [KhatamanController::class, 'index'])->name('khataman.index');
                Route::post('/khataman/generate-kode', [KhatamanController::class, 'generateKode'])->name('khataman.generate-kode');
                Route::get('/khataman/detail/{id}', [KhatamanController::class, 'detail'])->name('khataman.detail');
                Route::get('/khataman/config', [KhatamanController::class, 'config'])->name('khataman.config');
                Route::post('/khataman/config', [KhatamanController::class, 'config'])->name('khataman.config.update');
            });

            // Sunnah / 7SPS
            Route::middleware('feature:sunnah')->group(function () {
                Route::get('/sunnah', [SunnahController::class, 'index'])->name('sunnah.index');
                Route::get('/sunnah/rekap', [SunnahController::class, 'rekapBulanan'])->name('sunnah.rekap');
                Route::get('/sunnah/rekapitulasi-karyawan', [SunnahController::class, 'rekapitulasiKaryawan'])->name('sunnah.rekapitulasi-karyawan');
                Route::get('/sunnah/rekap-divisi', [SunnahController::class, 'rekapDivisi'])->name('sunnah.rekap-divisi');
                Route::get('/sunnah/detail/{id}', [SunnahController::class, 'detail'])->name('sunnah.detail');
                Route::post('/sunnah/approve/{id}', [SunnahController::class, 'approve'])->name('sunnah.approve');
                Route::post('/sunnah/bulk-approve', [SunnahController::class, 'bulkApprove'])->name('sunnah.bulk-approve');
            });

            // Cuti
            Route::middleware('feature:cuti')->group(function () {
                Route::get('/cuti', [CutiController::class, 'index'])->name('cuti.index');
                Route::get('/cuti/{id}', [CutiController::class, 'show'])->name('cuti.show');
                Route::get('/cuti/{id}/edit-hr', [CutiController::class, 'editHr'])->name('cuti.edit-hr');
                Route::put('/cuti/{id}/update-hr', [CutiController::class, 'updateHr'])->name('cuti.update-hr');
                Route::post('/cuti/approve/{id}', [CutiController::class, 'approve'])->name('cuti.approve');
                Route::post('/cuti/bulk-approve', [CutiController::class, 'bulkApprove'])->name('cuti.bulk-approve');
                Route::delete('/cuti/{id}', [CutiController::class, 'destroy'])->name('cuti.destroy');
            });

            // Perjalanan Dinas
            Route::middleware('feature:perjalanan_dinas')->group(function () {
                Route::get('/perjalanan-dinas', [PerjalananDinasController::class, 'index'])->name('perjalanan-dinas.index');
                Route::get('/perjalanan-dinas/{id}', [PerjalananDinasController::class, 'show'])->name('perjalanan-dinas.show');
                Route::post('/perjalanan-dinas/{id}/mark-selesai', [PerjalananDinasController::class, 'markAsSelesai'])->name('perjalanan-dinas.mark-selesai');
                Route::get('/perjalanan-dinas/{id}/download', [PerjalananDinasController::class, 'downloadSuratTugas'])->name('perjalanan-dinas.download');
                Route::post('/perjalanan-dinas/{id}/catatan', [PerjalananDinasController::class, 'updateCatatan'])->name('perjalanan-dinas.catatan');
                Route::post('/perjalanan-dinas/{id}/approve', [PerjalananDinasController::class, 'approve'])->name('perjalanan-dinas.approve');
                Route::post('/perjalanan-dinas/{id}/reject', [PerjalananDinasController::class, 'reject'])->name('perjalanan-dinas.reject');
                Route::delete('/perjalanan-dinas/{id}', [PerjalananDinasController::class, 'destroy'])->name('perjalanan-dinas.destroy');
            });
        });

    // ==========================================
    // KARYAWAN ROUTES
    // ==========================================
    Route::middleware('karyawan')
        ->prefix('karyawan')
        ->name('karyawan.')
        ->group(function () {
            Route::get('/dashboard', [KaryawanDashboardController::class, 'index'])->name('dashboard');

            // Pengumuman
            Route::middleware('feature:pengumuman')->group(function () {
                Route::get('/pengumuman/{id}', [PengumumanController::class, 'showKaryawan'])->name('pengumuman.show');
            });

            // Presensi
            Route::middleware('feature:absensi')->group(function () {
                Route::get('/absensi', [AbsensiController::class, 'dashboard'])->name('absensi');
                Route::post('/absensi/checkin', [AbsensiController::class, 'checkIn'])
                    ->name('absensi.checkin')
                    ->middleware(['throttle:10,1', 'presensi.banned']);
                Route::post('/absensi/checkout', [AbsensiController::class, 'checkOut'])
                    ->name('absensi.checkout')
                    ->middleware(['throttle:10,1', 'presensi.banned']);
                Route::get('/absensi/status', [AbsensiController::class, 'status'])->name('absensi.status');
                Route::get('/absensi/server-time', [AbsensiController::class, 'serverTime'])->name('absensi.server-time');
                Route::get('/absensi/riwayat', [AbsensiController::class, 'getRiwayat'])->name('absensi.riwayat');
            });

            // Perizinan
            Route::middleware('feature:perizinan')->group(function () {
                Route::get('/absensi/perizinan', [AbsensiController::class, 'perizinan'])->name('absensi.perizinan');
                Route::post('/absensi/perizinan', [AbsensiController::class, 'perizinanStore'])->name('absensi.perizinan.store');
                Route::delete('/absensi/perizinan/{id}', [AbsensiController::class, 'perizinanCancel'])->name('absensi.perizinan.cancel');
            });

            // FHL
            Route::middleware('feature:fhl')->group(function () {
                Route::get('/fhl', [FhlController::class, 'dashboard'])->name('fhl.dashboard');
                Route::post('/fhl/checkin', [FhlController::class, 'checkIn'])->name('fhl.checkin')->middleware('throttle:10,1');
                Route::get('/fhl/server-time', [FhlController::class, 'serverTime'])->name('fhl.server-time');
            });

            // Khataman
            Route::middleware('feature:khataman')->group(function () {
                Route::get('/khataman', [KhatamanController::class, 'dashboard'])->name('khataman.dashboard');
                Route::post('/khataman/checkin', [KhatamanController::class, 'checkIn'])->name('khataman.checkin')->middleware('throttle:10,1');
                Route::get('/khataman/server-time', [KhatamanController::class, 'serverTime'])->name('khataman.server-time');
            });

            // 7SPS Sunnah
            Route::middleware('feature:sunnah')->group(function () {
                Route::get('/sunnah', [SunnahController::class, 'dashboard'])->name('sunnah.dashboard');
                Route::post('/sunnah/save', [SunnahController::class, 'saveDaily'])->name('sunnah.save')->middleware('throttle:60,1');
            });

            // Cuti
            Route::middleware('feature:cuti')->group(function () {
                Route::get('/cuti', [CutiController::class, 'dashboard'])->name('cuti.dashboard');
                Route::get('/cuti/create', [CutiController::class, 'create'])->name('cuti.create');
                Route::post('/cuti/store', [CutiController::class, 'store'])->name('cuti.store');
                Route::get('/cuti/{id}/edit', [CutiController::class, 'edit'])->name('cuti.edit');
                Route::put('/cuti/{id}', [CutiController::class, 'update'])->name('cuti.update');
                Route::delete('/cuti/{id}/cancel', [CutiController::class, 'cancel'])->name('cuti.cancel');
            });

            // Perjalanan Dinas
            Route::middleware('feature:perjalanan_dinas')->group(function () {
                Route::get('/perjalanan-dinas', [PerjalananDinasController::class, 'dashboard'])->name('perjalanan-dinas.index');
                Route::get('/perjalanan-dinas/create', [PerjalananDinasController::class, 'create'])->name('perjalanan-dinas.create');
                Route::post('/perjalanan-dinas/store', [PerjalananDinasController::class, 'store'])->name('perjalanan-dinas.store');
                Route::get('/perjalanan-dinas/{id}/download', [PerjalananDinasController::class, 'downloadSuratTugas'])->name('perjalanan-dinas.download');
                Route::get('/perjalanan-dinas/{id}/edit', [PerjalananDinasController::class, 'edit'])->name('perjalanan-dinas.edit');
                Route::put('/perjalanan-dinas/{id}', [PerjalananDinasController::class, 'update'])->name('perjalanan-dinas.update');
                Route::get('/perjalanan-dinas/{id}', [PerjalananDinasController::class, 'show'])->name('perjalanan-dinas.show');
            });
        });
});