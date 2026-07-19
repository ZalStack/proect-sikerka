<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\Cuti;
use App\Models\FhlAbsensi;
use App\Models\SunnahDaily;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class KaryawanDashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Absensi Today
        $absensiHariIni = Absensi::where('karyawan_id', $user->id)
            ->whereDate('tanggal', Carbon::today())
            ->first();

        // Absensi This Month
        $absensiBulanIni = Absensi::where('karyawan_id', $user->id)
            ->whereMonth('tanggal', Carbon::now()->month)
            ->whereYear('tanggal', Carbon::now()->year)
            ->count();

        $totalJamKerja = Absensi::where('karyawan_id', $user->id)
            ->whereMonth('tanggal', Carbon::now()->month)
            ->whereYear('tanggal', Carbon::now()->year)
            ->sum('total_jam_kerja');

        // Cuti Statistics
        $totalCuti = Cuti::where('karyawan_id', $user->id)->count();
        $cutiPending = Cuti::where('karyawan_id', $user->id)
            ->where('status', 'pending')
            ->count();
        $cutiApproved = Cuti::where('karyawan_id', $user->id)
            ->where('status', 'approved')
            ->count();

        $sisaCuti = Cuti::where('karyawan_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->value('sisa_cuti') ?? 12;

        // FHL Absensi
        $fhlHariIni = FhlAbsensi::where('karyawan_id', $user->id)
            ->whereDate('tanggal', Carbon::today())
            ->first();

        $fhlBulanIni = FhlAbsensi::where('karyawan_id', $user->id)
            ->whereMonth('tanggal', Carbon::now()->month)
            ->whereYear('tanggal', Carbon::now()->year)
            ->count();

        // Sunnah Statistics
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

        // Charts Data
        $absensiChart = $this->getAbsensiChartData($user->id);
        $sunnahChart = $this->getSunnahChartData($user->id);

        // Recent Activities
        $absensiTerbaru = Absensi::where('karyawan_id', $user->id)
            ->latest('tanggal')
            ->take(5)
            ->get();

        $cutiTerbaru = Cuti::where('karyawan_id', $user->id)
            ->latest('tanggal_pengajuan')
            ->take(5)
            ->get();

        return view('karyawan.dashboard', compact(
            'user',
            'absensiHariIni',
            'absensiBulanIni',
            'totalJamKerja',
            'totalCuti',
            'cutiPending',
            'cutiApproved',
            'sisaCuti',
            'fhlHariIni',
            'fhlBulanIni',
            'sunnahHariIni',
            'sunnahBulanIni',
            'sunnahTotalDays',
            'absensiChart',
            'sunnahChart',
            'absensiTerbaru',
            'cutiTerbaru'
        ));
    }

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
            $data[] = SunnahDaily::where('karyawan_id', $karyawanId)
                ->whereDate('tanggal', $date)
                ->value('total_poin') ?? 0;
        }

        return [
            'labels' => $labels,
            'data' => $data
        ];
    }
}
