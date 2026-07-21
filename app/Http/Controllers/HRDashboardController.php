<?php

namespace App\Http\Controllers;

use App\Models\Karyawan;
use App\Models\Absensi;
use App\Models\Cuti;
use App\Models\FhlAbsensi;
use App\Models\PerjalananDinas;
use App\Models\SunnahDaily;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class HRDashboardController extends Controller
{
    public function index()
    {
        // Basic Statistics
        $totalKaryawan = Karyawan::count();
        $totalHr = Karyawan::where('posisi', 'hr')->count();
        $totalKaryawanAktif = Karyawan::active()->count();
        $totalKaryawanResigned = Karyawan::resigned()->count();

        // Absensi Statistics
        $absensiHariIni = Absensi::whereDate('tanggal', Carbon::today())->count();
        $absensiTerlambat = Absensi::whereDate('tanggal', Carbon::today())->where('status', 'Terlambat')->count();

        // Cuti Statistics
        $cutiPending = Cuti::where('status', 'pending')->count();
        $cutiApproved = Cuti::where('status', 'approved')
            ->whereMonth('tanggal_mulai', Carbon::now()->month)
            ->count();

        // FHL Absensi
        $fhlHariIni = FhlAbsensi::whereDate('tanggal', Carbon::today())->count();

        // Sunnah Statistics
        $sunnahPending = SunnahDaily::where('status_approval', 'pending')->count();
        $sunnahBulanIni = SunnahDaily::whereMonth('tanggal', Carbon::now()->month)
            ->whereYear('tanggal', Carbon::now()->year)
            ->count();

        // Chart Data - Absensi 7 Hari Terakhir
        $absensiChart = $this->getAbsensiChartData();

        // Chart Data - Status Karyawan
        $statusKaryawanChart = $this->getStatusKaryawanChartData();

        // Chart Data - Cuti Bulanan
        $cutiChart = $this->getCutiChartData();

        // Top Performers Sunnah
        $topSunnah = $this->getTopSunnahPerformers();

        // Recent Activities
        $karyawanTerbaru = Karyawan::latest()->take(5)->get();
        $absensiTerbaru = Absensi::with('karyawan')->latest('check_in')->take(5)->get();
        $cutiTerbaru = Cuti::with('karyawan')->latest('tanggal_pengajuan')->take(5)->get();

        // Perjalanan Dinas Terbaru
        $perjalananDinasTerbaru = PerjalananDinas::with('karyawan')->latest('created_at')->take(6)->get();

        return view('hr.dashboard', compact('totalKaryawan', 'totalHr', 'totalKaryawanAktif', 'totalKaryawanResigned', 'absensiHariIni', 'absensiTerlambat', 'cutiPending', 'cutiApproved', 'fhlHariIni', 'sunnahPending', 'sunnahBulanIni', 'absensiChart', 'statusKaryawanChart', 'cutiChart', 'topSunnah', 'karyawanTerbaru', 'absensiTerbaru', 'cutiTerbaru', 'perjalananDinasTerbaru'));
    }

    private function getAbsensiChartData()
    {
        $labels = [];
        $data = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $labels[] = $date->format('D, d M');
            $data[] = Absensi::whereDate('tanggal', $date)->count();
        }

        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }

    private function getStatusKaryawanChartData()
    {
        $statuses = ['Karyawan Tetap', 'Contract', 'Internship'];
        $data = [];
        $colors = ['#2E7D3E', '#FCC626', '#00a2e9'];

        foreach ($statuses as $status) {
            $data[] = Karyawan::where('status', $status)->count();
        }

        // Add resigned
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

            $approved[] = Cuti::where('status', 'approved')->whereMonth('tanggal_mulai', $month->month)->whereYear('tanggal_mulai', $month->year)->count();

            $pending[] = Cuti::where('status', 'pending')->whereMonth('tanggal_pengajuan', $month->month)->whereYear('tanggal_pengajuan', $month->year)->count();

            $rejected[] = Cuti::where('status', 'rejected')->whereMonth('tanggal_pengajuan', $month->month)->whereYear('tanggal_pengajuan', $month->year)->count();
        }

        return [
            'labels' => $labels,
            'approved' => $approved,
            'pending' => $pending,
            'rejected' => $rejected,
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
