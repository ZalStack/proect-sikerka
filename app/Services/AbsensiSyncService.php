<?php

namespace App\Services;

use App\Models\Absensi;
use App\Models\Cuti;
use App\Models\Perizinan;
use App\Models\PerjalananDinas;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AbsensiSyncService
{
    /**
     * Sinkronkan pengajuan cuti ke tabel Absensi.
     * Invarian: Jangan pernah menimpa atau menghapus check-in asli karyawan!
     */
    public function syncCuti(Cuti $cuti): void
    {
        DB::transaction(function () use ($cuti) {
            $labelStatus = $cuti->status === 'approved' ? 'Cuti (disetujui HR)' : 'Cuti (menunggu persetujuan)';
            $keteranganAbsensi = $labelStatus . ($cuti->keterangan ? ' - ' . $cuti->keterangan : '');

            $period = CarbonPeriod::create($cuti->tanggal_mulai, $cuti->tanggal_selesai);

            foreach ($period as $date) {
                $tanggalStr = $date->format('Y-m-d');

                $absensi = Absensi::where('karyawan_id', $cuti->karyawan_id)
                    ->whereDate('tanggal', $tanggalStr)
                    ->first();

                // Lindungi absensi asli jika karyawan sudah check-in
                if ($absensi && $absensi->check_in) {
                    continue;
                }

                if ($absensi) {
                    $absensi->status = 'Cuti';
                    $absensi->keterangan = $keteranganAbsensi;
                    $absensi->save();
                } else {
                    Absensi::create([
                        'karyawan_id' => $cuti->karyawan_id,
                        'tanggal' => $tanggalStr,
                        'status' => 'Cuti',
                        'keterangan' => $keteranganAbsensi,
                        'total_jam_kerja' => 0,
                        'is_valid_location' => false,
                    ]);
                }
            }
        });
    }

    /**
     * Hapus sinkronisasi cuti (saat dibatalkan / ditolak).
     * Invarian: Hanya sentuh baris yang berstatus Cuti dan belum memiliki check_in asli.
     */
    public function removeCuti(Cuti $cuti): void
    {
        DB::transaction(function () use ($cuti) {
            $period = CarbonPeriod::create($cuti->tanggal_mulai, $cuti->tanggal_selesai);

            foreach ($period as $date) {
                $tanggalStr = $date->format('Y-m-d');

                $absensi = Absensi::where('karyawan_id', $cuti->karyawan_id)
                    ->whereDate('tanggal', $tanggalStr)
                    ->where('status', 'Cuti')
                    ->whereNull('check_in')
                    ->first();

                if ($absensi) {
                    $absensi->delete();
                }
            }
        });
    }

    /**
     * Sinkronkan pengajuan izin / sakit ke tabel Absensi.
     * Invarian: Lindungi check-in asli jika sudah ada.
     */
    public function syncPerizinan(Perizinan $perizinan): void
    {
        DB::transaction(function () use ($perizinan) {
            $period = CarbonPeriod::create($perizinan->tanggal_mulai, $perizinan->tanggal_selesai);

            foreach ($period as $date) {
                $tanggalStr = $date->format('Y-m-d');

                $absensi = Absensi::where('karyawan_id', $perizinan->karyawan_id)
                    ->whereDate('tanggal', $tanggalStr)
                    ->first();

                if ($absensi && $absensi->check_in) {
                    // Jika karyawan sudah hadir/check-in, catat perizinan di keterangan tanpa menghapus check-in
                    $catatan = 'Pengajuan ' . $perizinan->jenis . ' (disetujui HRD): ' . $perizinan->keterangan;
                    $absensi->keterangan = $absensi->keterangan ? $absensi->keterangan . ' | ' . $catatan : $catatan;
                    $absensi->save();
                    continue;
                }

                if ($absensi) {
                    $absensi->status = $perizinan->jenis;
                    $absensi->keterangan = 'Pengajuan ' . $perizinan->jenis . ' (disetujui HRD): ' . $perizinan->keterangan;
                    $absensi->save();
                } else {
                    Absensi::create([
                        'karyawan_id' => $perizinan->karyawan_id,
                        'tanggal' => $tanggalStr,
                        'status' => $perizinan->jenis,
                        'keterangan' => 'Pengajuan ' . $perizinan->jenis . ' (disetujui HRD): ' . $perizinan->keterangan,
                        'total_jam_kerja' => 0,
                        'is_valid_location' => false,
                    ]);
                }
            }
        });
    }

    /**
     * Reset perizinan (saat ditolak / dibatalkan).
     */
    public function removePerizinan(Perizinan $perizinan): void
    {
        DB::transaction(function () use ($perizinan) {
            $period = CarbonPeriod::create($perizinan->tanggal_mulai, $perizinan->tanggal_selesai);

            foreach ($period as $date) {
                $tanggalStr = $date->format('Y-m-d');

                $absensi = Absensi::where('karyawan_id', $perizinan->karyawan_id)
                    ->whereDate('tanggal', $tanggalStr)
                    ->where('status', $perizinan->jenis)
                    ->whereNull('check_in')
                    ->first();

                if ($absensi) {
                    $absensi->status = 'Alpha';
                    $absensi->keterangan = null;
                    $absensi->save();
                }
            }
        });
    }

    /**
     * Sinkronkan perjalanan dinas ke tabel Absensi.
     * Invarian: JANGAN PERNAH MENGHAPUS check_in / check_out jika karyawan sudah presensi!
     */
    public function syncPerjalananDinas(PerjalananDinas $perjalananDinas): void
    {
        DB::transaction(function () use ($perjalananDinas) {
            $start = Carbon::parse($perjalananDinas->tanggal_mulai);
            $end = Carbon::parse($perjalananDinas->tanggal_selesai);
            $karyawanId = $perjalananDinas->karyawan_id;
            $judul = $perjalananDinas->judul;

            for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
                $tanggalStr = $date->format('Y-m-d');

                $absensi = Absensi::where('karyawan_id', $karyawanId)
                    ->whereDate('tanggal', $tanggalStr)
                    ->first();

                if (!$absensi) {
                    Absensi::create([
                        'karyawan_id' => $karyawanId,
                        'tanggal' => $tanggalStr,
                        'status' => 'Perjalanan Dinas',
                        'kantor_cabang' => 'Perjalanan Dinas',
                        'keterangan' => "Perjalanan Dinas: {$judul}",
                        'check_in' => null,
                        'check_out' => null,
                        'total_jam_kerja' => 0,
                        'is_valid_location' => false,
                    ]);
                } else {
                    $absensi->status = 'Perjalanan Dinas';
                    $absensi->kantor_cabang = $absensi->kantor_cabang ?: 'Perjalanan Dinas';
                    $absensi->keterangan = "Perjalanan Dinas: {$judul}" . ($absensi->keterangan ? ' | ' . $absensi->keterangan : '');
                    // Pertahankan check_in dan check_out asli jika ada!
                    if (!$absensi->check_in) {
                        $absensi->is_valid_location = false;
                    }
                    $absensi->save();
                }
            }
        });
    }

    /**
     * Hapus rekap perjalanan dinas (saat ditolak / dibatalkan).
     */
    public function removePerjalananDinas(PerjalananDinas $perjalananDinas): void
    {
        DB::transaction(function () use ($perjalananDinas) {
            $start = Carbon::parse($perjalananDinas->tanggal_mulai);
            $end = Carbon::parse($perjalananDinas->tanggal_selesai);
            $karyawanId = $perjalananDinas->karyawan_id;

            for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
                $tanggalStr = $date->format('Y-m-d');

                $absensi = Absensi::where('karyawan_id', $karyawanId)
                    ->whereDate('tanggal', $tanggalStr)
                    ->where('status', 'Perjalanan Dinas')
                    ->whereNull('check_in')
                    ->first();

                if ($absensi) {
                    $absensi->delete();
                }
            }
        });
    }
}
