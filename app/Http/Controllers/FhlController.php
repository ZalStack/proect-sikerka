<?php
// app/Http/Controllers/FhlController.php

namespace App\Http\Controllers;

use App\Models\FhlAbsensi;
use App\Models\FhlKode;
use App\Models\FhlConfig;
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
        $todayAbsensi = FhlAbsensi::where('karyawan_id', $user->id)->whereDate('tanggal', $today)->first();

        // Data absensi bulan ini
        $absensiBulanIni = FhlAbsensi::where('karyawan_id', $user->id)
            ->whereMonth('tanggal', $month)
            ->whereYear('tanggal', $year)
            ->orderBy('tanggal', 'desc')
            ->get();

        // Get config
        $endTime = FhlAbsensi::getEndTime();
        $activeDayName = FhlAbsensi::getActiveDayName();

        // Statistik
        $statistik = [
            'total' => $absensiBulanIni->count(),
            'hadir' => $absensiBulanIni->where('status', 'Hadir')->count(),
            'total_jumat' => FhlAbsensi::countActiveDaysInMonth($month, $year),
        ];

        // Daftar hari aktif dalam bulan ini
        $activeDays = FhlAbsensi::getActiveDaysInMonth($month, $year);

        // Cek apakah hari ini aktif
        $isActiveDay = FhlAbsensi::isActiveDay();
        $isWithinTime = FhlAbsensi::isWithinAbsensiTime();

        // Kirim data absensi ke view, di-index berdasarkan tanggal
        $absensi = $absensiBulanIni->keyBy(function ($item) {
            return $item->tanggal->format('Y-m-d');
        });

        return view('karyawan.fhl.dashboard', compact(
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

    // Check-in FHL (dengan validasi kode, hari, dan jam)
    public function checkIn(Request $request)
    {
        $request->validate([
            'foto_bukti' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'kode_absensi' => 'required|string|max:20',
        ]);

        $user = Auth::user();
        $today = Carbon::today();
        $now = Carbon::now();

        // 1. Cek hari aktif
        if (!FhlAbsensi::isActiveDay()) {
            return response()->json([
                'success' => false,
                'message' => 'FHL hanya dilaksanakan pada hari ' . FhlAbsensi::getActiveDayName() . '!',
            ], 400);
        }

        // 2. Cek jam absensi
        if (!FhlAbsensi::isWithinAbsensiTime()) {
            $endTime = FhlAbsensi::getEndTime();
            return response()->json([
                'success' => false,
                'message' => 'Waktu absensi FHL telah berakhir. Batas akhir pukul ' . 
                    sprintf('%02d:%02d', $endTime['hour'], $endTime['minute']) . ' WIB.',
            ], 400);
        }

        // 3. Cek apakah sudah absen hari ini
        if (FhlAbsensi::hasCheckedInToday($user->id)) {
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah melakukan absen FHL hari ini!',
            ], 400);
        }

        // 4. Validasi kode kegiatan
        $kodeBenar = FhlKode::getKodeForDate($today);
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

        // 5. Upload foto
        $file = $request->file('foto_bukti');
        $filename = 'fhl_' . time() . '_' . Str::slug($user->nama_lengkap) . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('fhl_bukti', $filename, 'public');

        // 6. Simpan absensi
        $absensi = FhlAbsensi::create([
            'karyawan_id' => $user->id,
            'tanggal' => $today,
            'check_in' => $now,
            'foto_bukti' => $path,
            'kode_input' => $kodeInput,
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
            ],
        ]);
    }

    // Server time for FHL
    public function serverTime()
    {
        $now = Carbon::now();
        $endTime = FhlAbsensi::getEndTime();

        return response()->json([
            'success' => true,
            'server_time' => $now->format('Y-m-d H:i:s'),
            'timestamp_ms' => $now->valueOf(),
            'is_active_day' => FhlAbsensi::isActiveDay(),
            'is_within_time' => FhlAbsensi::isWithinAbsensiTime(),
            'end_time' => sprintf('%02d:%02d', $endTime['hour'], $endTime['minute']),
            'active_day' => FhlAbsensi::getActiveDayName(),
            'has_checked_in' => Auth::check() ? FhlAbsensi::hasCheckedInToday(Auth::id()) : null,
        ]);
    }

    // HR View FHL
    public function index(Request $request)
    {
        $query = FhlAbsensi::with('karyawan');

        // Filter bulan dan tahun
        if ($request->filled('month') && $request->filled('year')) {
            $query->whereMonth('tanggal', $request->month)->whereYear('tanggal', $request->year);
        } else {
            $request->merge([
                'month' => date('m'),
                'year' => date('Y'),
            ]);
            $query->whereMonth('tanggal', date('m'))->whereYear('tanggal', date('Y'));
        }

        // Filter karyawan
        if ($request->filled('karyawan_id')) {
            $query->where('karyawan_id', $request->karyawan_id);
        }

        $absensis = $query->orderBy('tanggal', 'desc')->paginate(10)->withQueryString();
        $karyawans = Karyawan::all();
        $month = $request->month ?? date('m');
        $year = $request->year ?? date('Y');

        // Statistik
        $statistik = [
            'total' => $absensis->total(),
            'hadir' => $query->get()->where('status', 'Hadir')->count(),
            'total_jumat' => FhlAbsensi::countActiveDaysInMonth($month, $year),
        ];

        // Get config from database
        $config = FhlConfig::getAll();
        $days = [
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
            7 => 'Minggu',
        ];
        $config['active_day_name'] = $days[$config['active_day'] ?? 5] ?? 'Jumat';

        return view('hr.fhl.index', compact('absensis', 'karyawans', 'statistik', 'month', 'year', 'config', 'days'));
    }

    // HR Detail FHL
    public function detail($id)
    {
        $absensi = FhlAbsensi::with('karyawan')->findOrFail($id);
        return view('hr.fhl.detail', compact('absensi'));
    }

    // HR: Buat kode kegiatan
    public function generateKode(Request $request)
    {
        $request->validate([
            'kode' => 'required|string|min:3|max:20|alpha_num',
        ], [
            'kode.required' => 'Kode kegiatan wajib diisi!',
            'kode.min' => 'Kode kegiatan minimal 3 karakter!',
            'kode.max' => 'Kode kegiatan maksimal 20 karakter!',
            'kode.alpha_num' => 'Kode kegiatan hanya boleh huruf dan angka (tanpa spasi/simbol)!',
        ]);

        $user = Auth::user();
        $today = Carbon::today();

        // Cek apakah hari ini aktif
        if (!FhlAbsensi::isActiveDay()) {
            return redirect()->back()->with('error', 'Kode hanya bisa dibuat pada hari ' . FhlAbsensi::getActiveDayName() . '!');
        }

        // Cek apakah sudah ada kode untuk hari ini
        if (FhlKode::hasKodeForDate($today)) {
            return redirect()->back()->with('error', 'Kode untuk hari ini sudah dibuat.');
        }

        $kode = strtoupper($request->input('kode'));

        // Simpan
        FhlKode::create([
            'tanggal' => $today,
            'kode' => $kode,
            'created_by' => $user->id,
        ]);

        return redirect()
            ->route('hr.fhl.index')
            ->with('success', "Kode kegiatan FHL berhasil dibuat: <strong>{$kode}</strong>");
    }

    // HR: Konfigurasi FHL
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
            FhlConfig::setValue('active_day', $request->active_day);
            FhlConfig::setValue('end_hour', $request->end_hour);
            FhlConfig::setValue('end_minute', $request->end_minute);

            return redirect()
                ->route('hr.fhl.index')
                ->with('success', 'Konfigurasi jadwal FHL berhasil diperbarui!');
        }

        // GET: Tampilkan form
        $config = FhlConfig::getAll();
        $config['active_day'] = $config['active_day'] ?? 5;
        $config['end_hour'] = $config['end_hour'] ?? 23;
        $config['end_minute'] = $config['end_minute'] ?? 59;

        return view('hr.fhl.config', compact('config', 'days'));
    }
}