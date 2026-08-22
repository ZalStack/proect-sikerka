<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Models\Karyawan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/*
|--------------------------------------------------------------------------
| API Routes for SIKEKAR
|--------------------------------------------------------------------------
*/

// 1. Verifikasi Kredensial Login (Password) dari SUDOKU
Route::post('/verify', function (Request $request) {
    $request->validate([
        'kode_pegawai' => 'required|string',
        'password' => 'required|string',
    ]);

    try {
        $karyawan = Karyawan::where('kode_pegawai', $request->kode_pegawai)->first();

        if (!$karyawan) {
            Log::warning('Verify: Karyawan not found', ['kode_pegawai' => $request->kode_pegawai]);
            return response()->json(['valid' => false]);
        }

        if (!Hash::check($request->password, $karyawan->kata_sandi)) {
            Log::warning('Verify: Wrong password', ['kode_pegawai' => $request->kode_pegawai]);
            return response()->json(['valid' => false]);
        }

        return response()->json([
            'valid' => true,
            'name' => $karyawan->nama_lengkap,
            'email' => $karyawan->email,
            'foto_profil' => $karyawan->foto_profil ? asset('storage/' . $karyawan->foto_profil) : null,
        ]);
    } catch (\Throwable $e) {
        Log::error('Verify error', [
            'kode_pegawai' => $request->kode_pegawai,
            'error' => $e->getMessage(),
        ]);
        return response()->json(['valid' => false], 500);
    }
});

// 2. Tarik Foto Profil Karyawan untuk SUDOKU
Route::get('/foto/{kode_pegawai}', function ($kode_pegawai) {
    try {
        $karyawan = Karyawan::where('kode_pegawai', $kode_pegawai)->first();

        if (!$karyawan || empty($karyawan->foto_profil)) {
            return response()->json(['message' => 'Foto not found for this employee'], 404);
        }

        // Cek file di storage disk public
        if (Storage::disk('public')->exists($karyawan->foto_profil)) {
            $file = Storage::disk('public')->get($karyawan->foto_profil);
            $mime = Storage::disk('public')->mimeType($karyawan->foto_profil);
            return response($file, 200)->header('Content-Type', $mime);
        }

        // Fallback jika file ada di public path
        $publicPath = public_path('storage/' . $karyawan->foto_profil);
        if (file_exists($publicPath)) {
            $mime = mime_content_type($publicPath) ?: 'image/jpeg';
            return response(file_get_contents($publicPath), 200)->header('Content-Type', $mime);
        }

        return response()->json(['message' => 'Foto file does not exist on disk'], 404);
    } catch (\Throwable $e) {
        Log::error('API foto error', ['kode' => $kode_pegawai, 'error' => $e->getMessage()]);
        return response()->json(['message' => 'Internal server error'], 500);
    }
});

// 3. Daftar Seluruh Karyawan Aktif
Route::get('/karyawan', function () {
    try {
        $karyawans = Karyawan::where('is_resigned', false)->get(['kode_pegawai', 'nama_lengkap as name', 'email', 'foto_profil', 'divisi']);
        return response()->json([
            'success' => true,
            'data' => $karyawans->map(fn($k) => [
                'kode_pegawai' => $k->kode_pegawai,
                'name' => $k->name,
                'email' => $k->email,
                'divisi' => $k->divisi,
                'foto_profil' => $k->foto_profil ? asset('storage/' . $k->foto_profil) : null,
            ]),
        ]);
    } catch (\Throwable $e) {
        return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
    }
});