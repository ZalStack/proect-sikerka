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

        // Daftar hari aktif (Senin-Jumat) dalam bulan ini
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

        // Hanya validasi hari aktif (Senin-Jumat)
        if (!KhatamanAbsensi::isActiveDay()) {
            return response()->json([
                'success' => false,
                'message' => 'Khataman hanya dilaksanakan pada hari Senin-Jumat!'
            ], 400);
        }

        // **Tidak ada batasan jam** – bebas kapan pun

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

    // HR: Generate kode (bisa kapan saja, asal hari aktif)
    public function generateKode(Request $request)
    {
        $user = Auth::user();
        $today = Carbon::today();

        // Boleh generate kode hanya pada hari aktif (Senin-Jumat)
        if (!KhatamanAbsensi::isActiveDay()) {
            return redirect()->back()->with('error', 'Kode hanya bisa dibuat pada hari Senin-Jumat!');
        }

        if (KhatamanKode::hasKodeForDate($today)) {
            return redirect()->back()->with('error', 'Kode untuk hari ini sudah dibuat.');
        }

        $kode = KhatamanKode::generateRandomKode();
        KhatamanKode::create([
            'tanggal'    => $today,
            'kode'       => $kode,
            'created_by' => $user->id, // nullable, tidak masalah
        ]);

        return redirect()->route('hr.khataman.index')->with('success', "Kode Khataman berhasil dibuat: <strong>{$kode}</strong>");
    }

    // Helpers: Hitung jumlah hari aktif (Senin-Jumat) dalam bulan
    private function countActiveDaysInMonth($month, $year)
    {
        $count = 0;
        $date = Carbon::create($year, $month, 1);
        while ($date->month == $month) {
            $dayOfWeek = $date->dayOfWeek; // 1=Senin ... 7=Minggu
            if ($dayOfWeek >= 1 && $dayOfWeek <= 5) {
                $count++;
            }
            $date->addDay();
        }
        return $count;
    }

    // Helper: Dapatkan daftar hari aktif (Senin-Jumat) dalam bulan
    private function getActiveDaysInMonth($month, $year)
    {
        $days = [];
        $date = Carbon::create($year, $month, 1);
        while ($date->month == $month) {
            $dayOfWeek = $date->dayOfWeek;
            if ($dayOfWeek >= 1 && $dayOfWeek <= 5) {
                $days[] = $date->copy();
            }
            $date->addDay();
        }
        return $days;
    }
}
