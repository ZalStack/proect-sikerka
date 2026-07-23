<?php
// app/Http/Controllers/FhlController.php

namespace App\Http\Controllers;

use App\Models\FhlAbsensi;
use App\Models\FhlKode;
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

        // Kirim data absensi ke view, di-index berdasarkan tanggal
        $absensi = $absensiBulanIni->keyBy(function ($item) {
            return $item->tanggal->format('Y-m-d');
        });

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

    // Check-in FHL (dengan validasi kode dan jam)
    public function checkIn(Request $request)
    {
        $request->validate([
            'foto_bukti'   => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'kode_absensi' => 'required|string|max:20',
        ]);

        $user = Auth::user();
        $today = Carbon::today();
        $now = Carbon::now();

        // 1. Cek hari Jumat
        if (!FhlAbsensi::isFriday()) {
            return response()->json([
                'success' => false,
                'message' => 'FHL hanya dilaksanakan pada hari Jumat!'
            ], 400);
        }

        // 2. Cek batas waktu (07:00 - 08:00)
        $start = $today->copy()->setTime(7, 0, 0);
        $end   = $today->copy()->setTime(8, 0, 0);
        if (!$now->between($start, $end)) {
            return response()->json([
                'success' => false,
                'message' => 'Check-in FHL hanya dapat dilakukan antara pukul 07:00 - 08:00!'
            ], 400);
        }

        // 3. Cek apakah sudah absen hari ini
        if (FhlAbsensi::hasCheckedInToday($user->id)) {
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah melakukan absen FHL hari ini!'
            ], 400);
        }

        // 4. Validasi kode kegiatan
        $kodeBenar = FhlKode::getKodeForDate($today);
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

        // 5. Upload foto
        $file = $request->file('foto_bukti');
        $filename = 'fhl_' . time() . '_' . Str::slug($user->nama_lengkap) . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('fhl_bukti', $filename, 'public');

        // 6. Simpan absensi
        $absensi = FhlAbsensi::create([
            'karyawan_id' => $user->id,
            'tanggal'     => $today,
            'check_in'    => $now,
            'foto_bukti'  => $path,
            'kode_input'  => $kodeInput,
            'status'      => 'Hadir',
            'ip_address'  => $request->ip(),
        ]);

        return response()->json([
            'success' => true,
            'message' => '✅ Absen FHL berhasil!',
            'data' => [
                'waktu'   => $now->format('H:i:s'),
                'tanggal' => $now->format('d-m-Y'),
                'foto'    => Storage::url($path),
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

    // HR: Generate kode kegiatan untuk hari ini
    public function generateKode(Request $request)
    {
        $user = Auth::user();
        $today = Carbon::today();

        // Cek apakah hari ini Jumat
        if (!FhlAbsensi::isFriday()) {
            return redirect()->back()->with('error', 'Kode hanya bisa dibuat pada hari Jumat!');
        }

        // Cek apakah sudah ada kode untuk hari ini
        if (FhlKode::hasKodeForDate($today)) {
            return redirect()->back()->with('error', 'Kode untuk hari ini sudah dibuat.');
        }

        // Generate kode acak
        $kode = FhlKode::generateRandomKode();

        // Simpan
        FhlKode::create([
            'tanggal'    => $today,
            'kode'       => $kode,
            'created_by' => $user->id,
        ]);

        return redirect()->route('hr.fhl.index')->with('success', "Kode kegiatan FHL berhasil dibuat: <strong>{$kode}</strong>");
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
