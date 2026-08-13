<?php
// app/Http/Controllers/KhatamanController.php

namespace App\Http\Controllers;

use App\Models\KhatamanAbsensi;
use App\Models\KhatamanKode;
use App\Models\KhatamanConfig;
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

        $endTime = KhatamanAbsensi::getEndTime();
        $activeDayName = KhatamanAbsensi::getActiveDayName();

        $statistik = [
            'total' => $absensiBulanIni->count(),
            'hadir' => $absensiBulanIni->where('status', 'Hadir')->count(),
            'total_hari_aktif' => KhatamanAbsensi::countActiveDaysInMonth($month, $year),
        ];

        $activeDays = KhatamanAbsensi::getActiveDaysInMonth($month, $year);
        $isActiveDay = KhatamanAbsensi::isActiveDay();
        $isWithinTime = KhatamanAbsensi::isWithinAbsensiTime();

        $absensi = $absensiBulanIni->keyBy(function ($item) {
            return $item->tanggal->format('Y-m-d');
        });

        return view('karyawan.khataman.dashboard', compact(
            'todayAbsensi',
            'absensi',
            'statistik',
            'activeDays',
            'isActiveDay',
            'isWithinTime',
            'endTime',
            'activeDayName',
            'month',
            'year'
        ));
    }

    // Check-in
    public function checkIn(Request $request)
    {
        $request->validate([
            'kode_absensi' => 'required|string|max:20',
        ]);

        $user = Auth::user();
        $today = Carbon::today();
        $now = Carbon::now();

        // Validasi hari aktif
        if (!KhatamanAbsensi::isActiveDay()) {
            return response()->json([
                'success' => false,
                'message' => 'Khataman hanya dilaksanakan pada hari ' . KhatamanAbsensi::getActiveDayName() . '!',
            ], 400);
        }

        // Validasi jam absensi
        if (!KhatamanAbsensi::isWithinAbsensiTime()) {
            $endTime = KhatamanAbsensi::getEndTime();
            return response()->json([
                'success' => false,
                'message' => 'Waktu absensi Khataman telah berakhir. Batas akhir pukul ' . 
                    sprintf('%02d:%02d', $endTime['hour'], $endTime['minute']) . ' WIB.',
            ], 400);
        }

        if (KhatamanAbsensi::hasCheckedInToday($user->id)) {
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah melakukan absen Khataman hari ini!',
            ], 400);
        }

        $kodeBenar = KhatamanKode::getKodeForDate($today);
        if (!$kodeBenar) {
            return response()->json([
                'success' => false,
                'message' => 'Kode kegiatan belum dibuat oleh HR untuk hari ini.',
            ], 400);
        }

        $kodeInput = $request->input('kode_absensi');
        if (strtoupper($kodeInput) !== strtoupper($kodeBenar)) {
            return response()->json([
                'success' => false,
                'message' => 'Kode kegiatan yang Anda masukkan salah!',
            ], 400);
        }

        $absensi = KhatamanAbsensi::create([
            'karyawan_id' => $user->id,
            'tanggal' => $today,
            'check_in' => $now,
            'kode_input' => $kodeInput,
            'status' => 'Hadir',
            'ip_address' => $request->ip(),
        ]);

        return response()->json([
            'success' => true,
            'message' => '✅ Absen Khataman berhasil!',
            'data' => [
                'waktu' => $now->format('H:i:s'),
                'tanggal' => $now->format('d-m-Y'),
            ],
        ]);
    }

    // Server time
    public function serverTime()
    {
        $now = Carbon::now();
        $endTime = KhatamanAbsensi::getEndTime();

        return response()->json([
            'success' => true,
            'server_time' => $now->format('Y-m-d H:i:s'),
            'timestamp_ms' => $now->valueOf(),
            'is_active_day' => KhatamanAbsensi::isActiveDay(),
            'is_within_time' => KhatamanAbsensi::isWithinAbsensiTime(),
            'end_time' => sprintf('%02d:%02d', $endTime['hour'], $endTime['minute']),
            'active_day' => KhatamanAbsensi::getActiveDayName(),
            'has_checked_in' => Auth::check() ? KhatamanAbsensi::hasCheckedInToday(Auth::id()) : null,
        ]);
    }

    // HR: Index
    public function index(Request $request)
    {
        $query = KhatamanAbsensi::with('karyawan');

        if ($request->filled('month') && $request->filled('year')) {
            $query->whereMonth('tanggal', $request->month)->whereYear('tanggal', $request->year);
        } else {
            $request->merge([
                'month' => date('m'),
                'year' => date('Y'),
            ]);
            $query->whereMonth('tanggal', date('m'))->whereYear('tanggal', date('Y'));
        }

        if ($request->filled('karyawan_id')) {
            $query->where('karyawan_id', $request->karyawan_id);
        }

        $absensis = $query->orderBy('tanggal', 'desc')->paginate(10)->withQueryString();
        $karyawans = Karyawan::all();
        $month = $request->month ?? date('m');
        $year = $request->year ?? date('Y');

        $statistik = [
            'total' => $absensis->total(),
            'hadir' => $query->get()->where('status', 'Hadir')->count(),
            'total_hari_aktif' => KhatamanAbsensi::countActiveDaysInMonth($month, $year),
        ];

        // Get config from database
        $config = KhatamanConfig::getAll();
        $days = [
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
            7 => 'Minggu',
        ];
        $config['active_day_name'] = $days[$config['active_day'] ?? 4] ?? 'Kamis';

        return view('hr.khataman.index', compact('absensis', 'karyawans', 'statistik', 'month', 'year', 'config', 'days'));
    }

    // HR: Detail
    public function detail($id)
    {
        $absensi = KhatamanAbsensi::with('karyawan')->findOrFail($id);
        return view('hr.khataman.detail', compact('absensi'));
    }

    // HR: Generate kode
    public function generateKode(Request $request)
    {
        $user = Auth::user();
        $today = Carbon::today();

        if (!KhatamanAbsensi::isActiveDay()) {
            return redirect()->back()->with('error', 'Kode hanya bisa dibuat pada hari ' . KhatamanAbsensi::getActiveDayName() . '!');
        }

        if (KhatamanKode::hasKodeForDate($today)) {
            return redirect()->back()->with('error', 'Kode untuk hari ini sudah dibuat.');
        }

        $request->validate([
            'kode' => 'required|string|max:20',
        ]);

        $kode = strtoupper(trim($request->kode));

        KhatamanKode::create([
            'tanggal' => $today,
            'kode' => $kode,
            'created_by' => $user->id,
        ]);

        return redirect()
            ->route('hr.khataman.index')
            ->with('success', "Kode Khataman berhasil dibuat: <strong>{$kode}</strong>");
    }

    // HR: Konfigurasi
    public function config(Request $request)
    {
        $days = [
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
            7 => 'Minggu',
        ];

        if ($request->isMethod('post')) {
            $request->validate([
                'active_day' => 'required|integer|min:1|max:7',
                'end_hour' => 'required|integer|min:0|max:23',
                'end_minute' => 'required|integer|min:0|max:59',
            ]);

            // Save to database
            KhatamanConfig::setValue('active_day', $request->active_day);
            KhatamanConfig::setValue('end_hour', $request->end_hour);
            KhatamanConfig::setValue('end_minute', $request->end_minute);

            return redirect()
                ->route('hr.khataman.index')
                ->with('success', 'Konfigurasi jadwal Khataman berhasil diperbarui!');
        }

        // GET: Tampilkan form
        $config = KhatamanConfig::getAll();
        $config['active_day'] = $config['active_day'] ?? 4;
        $config['end_hour'] = $config['end_hour'] ?? 23;
        $config['end_minute'] = $config['end_minute'] ?? 59;

        return view('hr.khataman.config', compact('config', 'days'));
    }
}