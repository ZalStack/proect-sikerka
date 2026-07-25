<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\Cuti;
use App\Models\FhlAbsensi;
use App\Models\PerjalananDinas;
use App\Models\SunnahDaily;
use App\Models\KhatamanAbsensi;
use App\Models\Pengumuman;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class KaryawanDashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // ==================== ABSENSI ====================
        $absensiHariIni = Absensi::where('karyawan_id', $user->id)
            ->whereDate('tanggal', Carbon::today())
            ->first();

        $absensiBulanIni = Absensi::where('karyawan_id', $user->id)
            ->whereMonth('tanggal', Carbon::now()->month)
            ->whereYear('tanggal', Carbon::now()->year)
            ->count();

        $totalJamKerja = Absensi::where('karyawan_id', $user->id)
            ->whereMonth('tanggal', Carbon::now()->month)
            ->whereYear('tanggal', Carbon::now()->year)
            ->sum('total_jam_kerja');

        // ==================== CUTI ====================
        $totalCuti = Cuti::where('karyawan_id', $user->id)->count();
        $cutiPending = Cuti::where('karyawan_id', $user->id)
            ->where('status', 'pending')
            ->count();
        $cutiApproved = Cuti::where('karyawan_id', $user->id)
            ->where('status', 'approved')
            ->count();
        $cutiRejected = Cuti::where('karyawan_id', $user->id)
            ->where('status', 'rejected')
            ->count();

        $sisaCuti = Cuti::where('karyawan_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->value('sisa_cuti') ?? 12;

        // ==================== FHL (Jumat Berkah) ====================
        $fhlHariIni = FhlAbsensi::where('karyawan_id', $user->id)
            ->whereDate('tanggal', Carbon::today())
            ->first();

        $fhlBulanIni = FhlAbsensi::where('karyawan_id', $user->id)
            ->whereMonth('tanggal', Carbon::now()->month)
            ->whereYear('tanggal', Carbon::now()->year)
            ->count();

        // ==================== KHATAMAN ====================
        $khatamanHariIni = KhatamanAbsensi::where('karyawan_id', $user->id)
            ->whereDate('tanggal', Carbon::today())
            ->first();

        $khatamanBulanIni = KhatamanAbsensi::where('karyawan_id', $user->id)
            ->whereMonth('tanggal', Carbon::now()->month)
            ->whereYear('tanggal', Carbon::now()->year)
            ->count();

        // ==================== SUNNAH (7SPS) ====================
        $sunnahHariIni = SunnahDaily::where('karyawan_id', $user->id)
            ->whereDate('tanggal', Carbon::today())
            ->first();

        $sunnahBulanIni = SunnahDaily::where('karyawan_id', $user->id)
            ->whereMonth('tanggal', Carbon::now()->month)
            ->whereYear('tanggal', Carbon::now()->year)
            ->where('status_approval', 'approved')
            ->sum('total_poin');

        $sunnahTotalDays = SunnahDaily::where('karyawan_id', $user->id)
            ->whereMonth('tanggal', Carbon::now()->month)
            ->whereYear('tanggal', Carbon::now()->year)
            ->count();

        // ==================== PERJALANAN DINAS ====================
        $perjalananDinasTotal = PerjalananDinas::where('karyawan_id', $user->id)->count();
        $perjalananDinasPending = PerjalananDinas::where('karyawan_id', $user->id)
            ->where('status', 'pending')
            ->count();
        $perjalananDinasApproved = PerjalananDinas::where('karyawan_id', $user->id)
            ->where('status', 'approved')
            ->count();
        $perjalananDinasRejected = PerjalananDinas::where('karyawan_id', $user->id)
            ->where('status', 'rejected')
            ->count();

        // ==================== PENGUMUMAN ====================
        $pengumumanTerbaru = Pengumuman::whereIn('target', ['semua', 'karyawan'])
            ->with('creator')
            ->latest('created_at')
            ->take(3)
            ->get();

        // ==================== CHART DATA ====================
        $absensiChart = $this->getAbsensiChartData($user->id);
        $sunnahChart = $this->getSunnahChartData($user->id);
        $cutiChart = $this->getCutiChartData($user->id);

        // ==================== AKTIVITAS TERBARU ====================
        $absensiTerbaru = Absensi::where('karyawan_id', $user->id)
            ->latest('tanggal')
            ->take(5)
            ->get();

        $cutiTerbaru = Cuti::where('karyawan_id', $user->id)
            ->latest('tanggal_pengajuan')
            ->take(5)
            ->get();

        $perjalananDinasTerbaru = PerjalananDinas::where('karyawan_id', $user->id)
            ->latest('created_at')
            ->take(6)
            ->get();

        return view('karyawan.dashboard', compact(
            'user',
            'absensiHariIni',
            'absensiBulanIni',
            'totalJamKerja',
            'totalCuti',
            'cutiPending',
            'cutiApproved',
            'cutiRejected',
            'sisaCuti',
            'fhlHariIni',
            'fhlBulanIni',
            'khatamanHariIni',
            'khatamanBulanIni',
            'sunnahHariIni',
            'sunnahBulanIni',
            'sunnahTotalDays',
            'perjalananDinasTotal',
            'perjalananDinasPending',
            'perjalananDinasApproved',
            'perjalananDinasRejected',
            'pengumumanTerbaru',
            'absensiChart',
            'sunnahChart',
            'cutiChart',
            'absensiTerbaru',
            'cutiTerbaru',
            'perjalananDinasTerbaru'
        ));
    }

    // ==================== CHART HELPER ====================

    private function getAbsensiChartData($karyawanId)
    {
        $labels = [];
        $data = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $labels[] = $date->format('D, d M');
            $data[] = Absensi::where('karyawan_id', $karyawanId)
                ->whereDate('tanggal', $date)
                ->count();
        }

        return [
            'labels' => $labels,
            'data' => $data
        ];
    }

    private function getSunnahChartData($karyawanId)
    {
        $labels = [];
        $data = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $labels[] = $date->format('D');
            $poin = SunnahDaily::where('karyawan_id', $karyawanId)
                ->whereDate('tanggal', $date)
                ->value('total_poin');
            $data[] = $poin ?? 0;
        }

        return [
            'labels' => $labels,
            'data' => $data
        ];
    }

    private function getCutiChartData($karyawanId)
    {
        $labels = [];
        $pending = [];
        $approved = [];

        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $labels[] = $month->format('M Y');

            $pending[] = Cuti::where('karyawan_id', $karyawanId)
                ->where('status', 'pending')
                ->whereMonth('tanggal_pengajuan', $month->month)
                ->whereYear('tanggal_pengajuan', $month->year)
                ->count();

            $approved[] = Cuti::where('karyawan_id', $karyawanId)
                ->where('status', 'approved')
                ->whereMonth('tanggal_pengajuan', $month->month)
                ->whereYear('tanggal_pengajuan', $month->year)
                ->count();
        }

        return [
            'labels' => $labels,
            'pending' => $pending,
            'approved' => $approved,
        ];
    }
}
