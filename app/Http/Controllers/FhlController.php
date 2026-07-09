<?php

namespace App\Http\Controllers;

use App\Models\FhlAbsensi;
use App\Models\Karyawan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;

class FhlController extends Controller
{
    // Dashboard Karyawan FHL
    public function dashboard()
    {
        $user = Auth::user();
        $today = Carbon::today();
        $month = Carbon::now()->month;
        $year = Carbon::now()->year;

        // Cek absensi hari ini
        $todayAbsensi = FhlAbsensi::where('karyawan_id', $user->id)
            ->whereDate('tanggal', $today)
            ->first();

        // Data absensi bulan ini
        $absensiBulanIni = FhlAbsensi::where('karyawan_id', $user->id)
            ->whereMonth('tanggal', $month)
            ->whereYear('tanggal', $year)
            ->orderBy('tanggal', 'desc')
            ->get();

        // Statistik
        $statistik = [
            'total' => $absensiBulanIni->count(),
            'hadir' => $absensiBulanIni->where('status', 'Hadir')->count(),
            'total_jumat' => $this->countFridaysInMonth($month, $year),
        ];

        // Daftar hari Jumat dalam bulan ini
        $fridays = $this->getFridaysInMonth($month, $year);

        // Cek apakah hari ini Jumat
        $isFriday = FhlAbsensi::isFriday();

        // Data untuk grafik/detail
        $absensi = $absensiBulanIni;

        return view('karyawan.fhl.dashboard', compact(
            'todayAbsensi',
            'absensi',
            'statistik',
            'fridays',
            'isFriday',
            'month',
            'year'
        ));
    }

    // Check-in FHL
    public function checkIn(Request $request)
    {
        $request->validate([
            'foto_bukti' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $user = Auth::user();

        // Cek apakah hari ini Jumat
        if (!FhlAbsensi::isFriday()) {
            return response()->json([
                'success' => false,
                'message' => 'FHL hanya dilaksanakan pada hari Jumat!'
            ], 400);
        }

        $today = Carbon::today();

        // Cek apakah sudah absen hari ini
        if (FhlAbsensi::hasCheckedInToday($user->id)) {
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah melakukan absen FHL hari ini!'
            ], 400);
        }

        $now = Carbon::now();

        // Upload foto
        $file = $request->file('foto_bukti');
        $filename = 'fhl_' . time() . '_' . Str::slug($user->nama_lengkap) . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('fhl_bukti', $filename, 'public');

        $absensi = FhlAbsensi::create([
            'karyawan_id' => $user->id,
            'tanggal' => $today,
            'check_in' => $now,
            'foto_bukti' => $path,
            'status' => 'Hadir',
            'ip_address' => $request->ip(),
        ]);

        return response()->json([
            'success' => true,
            'message' => '✅ Absen FHL berhasil!',
            'data' => [
                'waktu' => $now->format('H:i:s'),
                'tanggal' => $now->format('d-m-Y'),
                'foto' => Storage::url($path),
            ]
        ]);
    }

    // HR View FHL
    public function index(Request $request)
    {
        $query = FhlAbsensi::with('karyawan');

        // Filter bulan dan tahun
        if ($request->filled('month') && $request->filled('year')) {
            $query->whereMonth('tanggal', $request->month)
                  ->whereYear('tanggal', $request->year);
        } else {
            // Default: bulan dan tahun sekarang
            $request->merge([
                'month' => date('m'),
                'year' => date('Y')
            ]);
            $query->whereMonth('tanggal', date('m'))
                  ->whereYear('tanggal', date('Y'));
        }

        // Filter karyawan
        if ($request->filled('karyawan_id')) {
            $query->where('karyawan_id', $request->karyawan_id);
        }

        $absensis = $query->orderBy('tanggal', 'desc')->paginate(15);
        $karyawans = Karyawan::all();
        $month = $request->month ?? date('m');
        $year = $request->year ?? date('Y');

        // Statistik
        $statistik = [
            'total' => $absensis->total(),
            'hadir' => $query->get()->where('status', 'Hadir')->count(),
            'total_jumat' => $this->countFridaysInMonth($month, $year),
        ];

        return view('hr.fhl.index', compact('absensis', 'karyawans', 'statistik', 'month', 'year'));
    }

    // HR Detail FHL
    public function detail($id)
    {
        $absensi = FhlAbsensi::with('karyawan')->findOrFail($id);
        return view('hr.fhl.detail', compact('absensi'));
    }

    // Helper: Hitung jumlah Jumat dalam bulan
    private function countFridaysInMonth($month, $year)
    {
        $count = 0;
        $date = Carbon::create($year, $month, 1);
        while ($date->month == $month) {
            if ($date->dayOfWeek == Carbon::FRIDAY) {
                $count++;
            }
            $date->addDay();
        }
        return $count;
    }

    // Helper: Dapatkan daftar hari Jumat dalam bulan
    private function getFridaysInMonth($month, $year)
    {
        $fridays = [];
        $date = Carbon::create($year, $month, 1);
        while ($date->month == $month) {
            if ($date->dayOfWeek == Carbon::FRIDAY) {
                $fridays[] = $date->copy();
            }
            $date->addDay();
        }
        return $fridays;
    }
}
