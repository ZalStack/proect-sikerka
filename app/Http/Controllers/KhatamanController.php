<?php
// app/Http/Controllers/KhatamanController.php

namespace App\Http\Controllers;

use App\Models\KhatamanAbsensi;
use App\Models\KhatamanKode;
use App\Models\Karyawan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class KhatamanController extends Controller
{
    // Dashboard Karyawan
    public function dashboard()
    {
        $user = Auth::user();
        $today = Carbon::today();
        $month = Carbon::now()->month;
        $year = Carbon::now()->year;

        $todayAbsensi = KhatamanAbsensi::where('karyawan_id', $user->id)
            ->whereDate('tanggal', $today)
            ->first();

        $absensiBulanIni = KhatamanAbsensi::where('karyawan_id', $user->id)
            ->whereMonth('tanggal', $month)
            ->whereYear('tanggal', $year)
            ->orderBy('tanggal', 'desc')
            ->get();

        $statistik = [
            'total' => $absensiBulanIni->count(),
            'hadir' => $absensiBulanIni->where('status', 'Hadir')->count(),
            'total_hari_aktif' => $this->countActiveDaysInMonth($month, $year),
        ];

        // Daftar hari aktif (Kamis) dalam bulan ini
        $activeDays = $this->getActiveDaysInMonth($month, $year);
        $isActiveDay = KhatamanAbsensi::isActiveDay();

        $absensi = $absensiBulanIni->keyBy(function ($item) {
            return $item->tanggal->format('Y-m-d');
        });

        return view('karyawan.khataman.dashboard', compact(
            'todayAbsensi',
            'absensi',
            'statistik',
            'activeDays',
            'isActiveDay',
            'month',
            'year'
        ));
    }

    // Check-in (tanpa batas jam, hanya validasi kode)
    public function checkIn(Request $request)
    {
        $request->validate([
            'kode_absensi' => 'required|string|max:20',
        ]);

        $user = Auth::user();
        $today = Carbon::today();
        $now = Carbon::now();

        // Hanya validasi hari aktif (Kamis)
        if (!KhatamanAbsensi::isActiveDay()) {
            return response()->json([
                'success' => false,
                'message' => 'Khataman hanya dilaksanakan pada hari Kamis!'
            ], 400);
        }

        if (KhatamanAbsensi::hasCheckedInToday($user->id)) {
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah melakukan absen Khataman hari ini!'
            ], 400);
        }

        $kodeBenar = KhatamanKode::getKodeForDate($today);
        if (!$kodeBenar) {
            return response()->json([
                'success' => false,
                'message' => 'Kode kegiatan belum dibuat oleh HR untuk hari ini.'
            ], 400);
        }

        $kodeInput = $request->input('kode_absensi');
        if (strtoupper($kodeInput) !== strtoupper($kodeBenar)) {
            return response()->json([
                'success' => false,
                'message' => 'Kode kegiatan yang Anda masukkan salah!'
            ], 400);
        }

        $absensi = KhatamanAbsensi::create([
            'karyawan_id' => $user->id,
            'tanggal'     => $today,
            'check_in'    => $now,
            'kode_input'  => $kodeInput,
            'status'      => 'Hadir',
            'ip_address'  => $request->ip(),
        ]);

        return response()->json([
            'success' => true,
            'message' => '✅ Absen Khataman berhasil!',
            'data' => [
                'waktu'   => $now->format('H:i:s'),
                'tanggal' => $now->format('d-m-Y'),
            ]
        ]);
    }

    // Kirim waktu server saat ini ke client, dipakai untuk sinkronisasi jam
    // real-time di dashboard supaya tidak bergantung pada jam device karyawan.
    public function serverTime()
    {
        $now = Carbon::now();

        return response()->json([
            'success'       => true,
            'server_time'   => $now->format('Y-m-d H:i:s'),
            'timestamp_ms'  => $now->valueOf(), // epoch ms, dipakai JS
            'is_active_day' => KhatamanAbsensi::isActiveDay(),
            'has_checked_in' => Auth::check() ? KhatamanAbsensi::hasCheckedInToday(Auth::id()) : null,
        ]);
    }

    // HR: Index
    public function index(Request $request)
    {
        $query = KhatamanAbsensi::with('karyawan');

        if ($request->filled('month') && $request->filled('year')) {
            $query->whereMonth('tanggal', $request->month)
                  ->whereYear('tanggal', $request->year);
        } else {
            $request->merge([
                'month' => date('m'),
                'year' => date('Y')
            ]);
            $query->whereMonth('tanggal', date('m'))
                  ->whereYear('tanggal', date('Y'));
        }

        if ($request->filled('karyawan_id')) {
            $query->where('karyawan_id', $request->karyawan_id);
        }

        $absensis = $query->orderBy('tanggal', 'desc')->paginate(15);
        $karyawans = Karyawan::all();
        $month = $request->month ?? date('m');
        $year = $request->year ?? date('Y');

        $statistik = [
            'total' => $absensis->total(),
            'hadir' => $query->get()->where('status', 'Hadir')->count(),
            'total_hari_aktif' => $this->countActiveDaysInMonth($month, $year),
        ];

        return view('hr.khataman.index', compact('absensis', 'karyawans', 'statistik', 'month', 'year'));
    }

    // HR: Detail
    public function detail($id)
    {
        $absensi = KhatamanAbsensi::with('karyawan')->findOrFail($id);
        return view('hr.khataman.detail', compact('absensi'));
    }

    // HR: Generate kode (hanya pada hari Kamis)
    public function generateKode(Request $request)
    {
        $user = Auth::user();
        $today = Carbon::today();

        // Hanya boleh generate pada hari Kamis
        if (!KhatamanAbsensi::isActiveDay()) {
            return redirect()->back()->with('error', 'Kode hanya bisa dibuat pada hari Kamis!');
        }

        if (KhatamanKode::hasKodeForDate($today)) {
            return redirect()->back()->with('error', 'Kode untuk hari ini sudah dibuat.');
        }

        $kode = KhatamanKode::generateRandomKode();
        KhatamanKode::create([
            'tanggal'    => $today,
            'kode'       => $kode,
            'created_by' => $user->id,
        ]);

        return redirect()->route('hr.khataman.index')->with('success', "Kode Khataman berhasil dibuat: <strong>{$kode}</strong>");
    }

    // Helpers: Hitung jumlah hari aktif (default: Kamis) dalam bulan
    private function countActiveDaysInMonth($month, $year)
    {
        $count = 0;
        $date = Carbon::create($year, $month, 1);
        while ($date->month == $month) {
            if ($date->dayOfWeekIso == KhatamanAbsensi::ACTIVE_DAY) {
                $count++;
            }
            $date->addDay();
        }
        return $count;
    }

    // Helper: Dapatkan daftar tanggal hari aktif (default: Kamis) dalam bulan
    private function getActiveDaysInMonth($month, $year)
    {
        $days = [];
        $date = Carbon::create($year, $month, 1);
        while ($date->month == $month) {
            if ($date->dayOfWeekIso == KhatamanAbsensi::ACTIVE_DAY) {
                $days[] = $date->copy();
            }
            $date->addDay();
        }
        return $days;
    }
}
