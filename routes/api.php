<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Models\Karyawan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

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
        ]);
    } catch (\Throwable $e) {
        Log::error('Verify error', [
            'kode_pegawai' => $request->kode_pegawai,
            'error' => $e->getMessage(),
        ]);
        return response()->json(['valid' => false], 500);
    }
});
