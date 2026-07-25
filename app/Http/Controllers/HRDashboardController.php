<?php

namespace App\Http\Controllers;

use App\Models\Karyawan;
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

class HRDashboardController extends Controller
{
    public function index()
    {
        // ==================== STATISTIK DASAR ====================
        $totalKaryawan = Karyawan::count();
        $totalHr = Karyawan::where('posisi', 'hr')->count();
        $totalKaryawanAktif = Karyawan::active()->count();
        $totalKaryawanResigned = Karyawan::resigned()->count();

        // ==================== ABSENSI ====================
        $absensiHariIni = Absensi::whereDate('tanggal', Carbon::today())->count();
        $absensiTerlambat = Absensi::whereDate('tanggal', Carbon::today())->where('status', 'Terlambat')->count();
        $absensiBulanIni = Absensi::whereMonth('tanggal', Carbon::now()->month)
            ->whereYear('tanggal', Carbon::now()->year)
            ->count();

        // ==================== CUTI ====================
        $cutiPending = Cuti::where('status', 'pending')->count();
        $cutiApproved = Cuti::where('status', 'approved')
            ->whereMonth('tanggal_mulai', Carbon::now()->month)
            ->count();
        $cutiRejected = Cuti::where('status', 'rejected')
            ->whereMonth('tanggal_pengajuan', Carbon::now()->month)
            ->count();
        $totalCutiBulanIni = Cuti::whereMonth('tanggal_pengajuan', Carbon::now()->month)
            ->whereYear('tanggal_pengajuan', Carbon::now()->year)
            ->count();

        // ==================== FHL (Jumat Berkah) ====================
        $fhlHariIni = FhlAbsensi::whereDate('tanggal', Carbon::today())->count();
        $fhlBulanIni = FhlAbsensi::whereMonth('tanggal', Carbon::now()->month)
            ->whereYear('tanggal', Carbon::now()->year)
            ->count();

        // ==================== SUNNAH (7SPS) ====================
        $sunnahPending = SunnahDaily::where('status_approval', 'pending')->count();
        $sunnahBulanIni = SunnahDaily::whereMonth('tanggal', Carbon::now()->month)
            ->whereYear('tanggal', Carbon::now()->year)
            ->count();
        $sunnahApprovedBulanIni = SunnahDaily::whereMonth('tanggal', Carbon::now()->month)
            ->whereYear('tanggal', Carbon::now()->year)
            ->where('status_approval', 'approved')
            ->sum('total_poin');

        // ==================== KHATAMAN ====================
        $khatamanHariIni = KhatamanAbsensi::whereDate('tanggal', Carbon::today())->count();
        $khatamanBulanIni = KhatamanAbsensi::whereMonth('tanggal', Carbon::now()->month)
            ->whereYear('tanggal', Carbon::now()->year)
            ->count();

        // ==================== PERJALANAN DINAS ====================
        $perjalananDinasPending = PerjalananDinas::where('status', 'pending')->count();
        $perjalananDinasApproved = PerjalananDinas::where('status', 'approved')->count();
        $perjalananDinasRejected = PerjalananDinas::where('status', 'rejected')->count();
        $perjalananDinasBulanIni = PerjalananDinas::whereMonth('tanggal_pengajuan', Carbon::now()->month)
            ->whereYear('tanggal_pengajuan', Carbon::now()->year)
            ->count();

        // ==================== PENGUMUMAN ====================
        $pengumumanTerbaru = Pengumuman::with('creator')
            ->latest('created_at')
            ->take(5)
            ->get();

        // ==================== CHART DATA ====================
        $absensiChart = $this->getAbsensiChartData();
        $statusKaryawanChart = $this->getStatusKaryawanChartData();
        $cutiChart = $this->getCutiChartData();
        $perjalananDinasChart = $this->getPerjalananDinasChartData();

        // ==================== TOP PERFORMERS ====================
        $topSunnah = $this->getTopSunnahPerformers();

        // ==================== AKTIVITAS TERBARU ====================
        $karyawanTerbaru = Karyawan::latest()->take(5)->get();
        $absensiTerbaru = Absensi::with('karyawan')->latest('check_in')->take(5)->get();
        $cutiTerbaru = Cuti::with('karyawan')->latest('tanggal_pengajuan')->take(5)->get();
        $perjalananDinasTerbaru = PerjalananDinas::with('karyawan')->latest('created_at')->take(6)->get();

        return view('hr.dashboard', compact(
            'totalKaryawan',
            'totalHr',
            'totalKaryawanAktif',
            'totalKaryawanResigned',
            'absensiHariIni',
            'absensiTerlambat',
            'absensiBulanIni',
            'cutiPending',
            'cutiApproved',
            'cutiRejected',
            'totalCutiBulanIni',
            'fhlHariIni',
            'fhlBulanIni',
            'sunnahPending',
            'sunnahBulanIni',
            'sunnahApprovedBulanIni',
            'khatamanHariIni',
            'khatamanBulanIni',
            'perjalananDinasPending',
            'perjalananDinasApproved',
            'perjalananDinasRejected',
            'perjalananDinasBulanIni',
            'pengumumanTerbaru',
            'absensiChart',
            'statusKaryawanChart',
            'cutiChart',
            'perjalananDinasChart',
            'topSunnah',
            'karyawanTerbaru',
            'absensiTerbaru',
            'cutiTerbaru',
            'perjalananDinasTerbaru'
        ));
    }

    // ==================== CHART HELPER ====================

    private function getAbsensiChartData()
    {
        $labels = [];
        $data = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $labels[] = $date->format('D, d M');
            $data[] = Absensi::whereDate('tanggal', $date)->count();
        }

        return ['labels' => $labels, 'data' => $data];
    }

    private function getStatusKaryawanChartData()
    {
        $statuses = ['Karyawan Tetap', 'Contract', 'Internship'];
        $data = [];
        $colors = ['#2E7D3E', '#FCC626', '#00a2e9'];

        foreach ($statuses as $status) {
            $data[] = Karyawan::where('status', $status)->count();
        }

        $data[] = Karyawan::resigned()->count();
        $colors[] = '#ec1d1d';
        $statuses[] = 'Resign';

        return [
            'labels' => $statuses,
            'data' => $data,
            'colors' => $colors,
        ];
    }

    private function getCutiChartData()
    {
        $labels = [];
        $approved = [];
        $pending = [];
        $rejected = [];

        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $labels[] = $month->format('M Y');

            $approved[] = Cuti::where('status', 'approved')
                ->whereMonth('tanggal_mulai', $month->month)
                ->whereYear('tanggal_mulai', $month->year)
                ->count();

            $pending[] = Cuti::where('status', 'pending')
                ->whereMonth('tanggal_pengajuan', $month->month)
                ->whereYear('tanggal_pengajuan', $month->year)
                ->count();

            $rejected[] = Cuti::where('status', 'rejected')
                ->whereMonth('tanggal_pengajuan', $month->month)
                ->whereYear('tanggal_pengajuan', $month->year)
                ->count();
        }

        return [
            'labels' => $labels,
            'approved' => $approved,
            'pending' => $pending,
            'rejected' => $rejected,
        ];
    }

    private function getPerjalananDinasChartData()
    {
        $labels = [];
        $pending = [];
        $approved = [];

        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $labels[] = $month->format('M Y');

            $pending[] = PerjalananDinas::where('status', 'pending')
                ->whereMonth('tanggal_pengajuan', $month->month)
                ->whereYear('tanggal_pengajuan', $month->year)
                ->count();

            $approved[] = PerjalananDinas::where('status', 'approved')
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

    private function getTopSunnahPerformers()
    {
        return SunnahDaily::selectRaw('karyawan_id, SUM(total_poin) as total_poin, COUNT(*) as total_days')
            ->with('karyawan')
            ->whereMonth('tanggal', Carbon::now()->month)
            ->whereYear('tanggal', Carbon::now()->year)
            ->where('status_approval', 'approved')
            ->groupBy('karyawan_id')
            ->orderByDesc('total_poin')
            ->take(5)
            ->get();
    }
}
