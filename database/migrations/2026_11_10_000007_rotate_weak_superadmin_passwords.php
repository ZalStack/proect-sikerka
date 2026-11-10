<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Akun superadmin yang masih memakai kredensial hardcode lama
     * ('superadmin' / 'superadmin123') dipaksa ganti kata sandi.
     *
     * Kata sandi baru diambil dari environment SUPERADMIN_PASSWORD;
     * bila kosong, dibuat acak dan dicatat ke log server (hanya admin
     * server yang dapat membacanya) — tidak pernah kredensial publik.
     */
    public function up(): void
    {
        if (!\Illuminate\Support\Facades\Schema::hasTable('karyawans')) {
            return;
        }

        $knownWeak = ['superadmin', 'superadmin123', 'password123'];
        $newPassword = (string) env('SUPERADMIN_PASSWORD', '');

        $targets = DB::table('karyawans')
            ->where('posisi', 'superadmin')
            ->get();

        foreach ($targets as $row) {
            $needsRotation = false;

            foreach ($knownWeak as $weak) {
                if (Hash::check($weak, $row->kata_sandi)) {
                    $needsRotation = true;
                    break;
                }
            }

            if (!$needsRotation) {
                continue;
            }

            if ($newPassword === '') {
                $newPassword = bin2hex(random_bytes(16));
                Log::warning(
                    'Kata sandi superadmin lemah diganti otomatis. '
                    . 'Simpan kata sandi baru berikut dan bagikan hanya ke Super Administrator: '
                    . $newPassword . ' (akun: ' . $row->email . ')'
                );
            }

            DB::table('karyawans')->where('id', $row->id)->update([
                'kata_sandi' => Hash::make($newPassword),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        // Tidak dapat dibalikkan secara aman (kata sandi lama tidak disimpan).
    }
};
