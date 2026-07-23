<?php

namespace App\Http\Controllers;

use App\Models\PerjalananDinas;
use App\Models\Karyawan;
use App\Models\Absensi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class PerjalananDinasController extends Controller
{
    /**
     * Display a listing of the resource for HR.
     */
    public function index(Request $request)
    {
        $query = PerjalananDinas::with('karyawan');

        // Filter by status
        if ($request->filled('status') && $request->status !== 'semua') {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->filled('tanggal_mulai')) {
            $query->whereDate('tanggal_mulai', '>=', $request->tanggal_mulai);
        }
        if ($request->filled('tanggal_selesai')) {
            $query->whereDate('tanggal_selesai', '<=', $request->tanggal_selesai);
        }

        // Search by karyawan name or judul
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('judul', 'like', "%{$search}%")
                    ->orWhereHas('karyawan', function ($sub) use ($search) {
                        $sub->where('nama_lengkap', 'like', "%{$search}%")
                            ->orWhere('kode_pegawai', 'like', "%{$search}%");
                    });
            });
        }

        $perjalananDinas = $query->orderBy('created_at', 'desc')->paginate(15);

        // Stats
        $stats = [
            'total' => PerjalananDinas::count(),
            'pending' => PerjalananDinas::where('status', 'pending')->count(),
            'approved' => PerjalananDinas::where('status', 'approved')->count(),
            'rejected' => PerjalananDinas::where('status', 'rejected')->count(),
            'selesai' => PerjalananDinas::where('status', 'selesai')->count(),
        ];

        return view('hr.perjalanan-dinas.index', compact('perjalananDinas', 'stats'));
    }

    /**
     * Show the form for creating a new resource for Karyawan.
     */
    public function create()
    {
        $karyawan = Auth::user();
        return view('karyawan.perjalanan-dinas.create', compact('karyawan'));
    }

    /**
     * Store a newly created resource in storage.
     * Otomatis approved, langsung rekap ke absensi.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'judul' => 'required|string|max:200',
            'agenda' => 'required|string',
            'tanggal_mulai' => [
                'required',
                'date',
                'after_or_equal:' . now()->addDays(7)->toDateString() // minimal H-7
            ],
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'surat_tugas' => 'required|file|mimes:pdf|max:2048', // wajib
        ], [
            'surat_tugas.required' => 'Surat tugas wajib diupload.',
            'surat_tugas.max' => 'Ukuran file surat tugas maksimal 2 MB.',
            'surat_tugas.mimes' => 'File surat tugas harus berformat PDF.',
            'tanggal_mulai.after_or_equal' => 'Tanggal mulai harus minimal 7 hari dari hari ini.',
            'tanggal_selesai.after_or_equal' => 'Tanggal selesai harus setelah atau sama dengan tanggal mulai.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $request->all();
        $data['karyawan_id'] = Auth::id();
        $data['tanggal_pengajuan'] = Carbon::today();
        $data['status'] = 'approved'; // langsung approved
        $data['approved_at'] = Carbon::now();
        $data['approved_by'] = null; // tidak ada approval dari HR

        // Handle file upload
        if ($request->hasFile('surat_tugas')) {
            $file = $request->file('surat_tugas');

            if ($file->getSize() > 2 * 1024 * 1024) {
                return redirect()->back()
                    ->withErrors(['surat_tugas' => 'Ukuran file surat tugas maksimal 2 MB.'])
                    ->withInput();
            }

            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('surat_tugas', $filename, 'public');
            $data['surat_tugas'] = $path;
        }

        $perjalanan = PerjalananDinas::create($data);

        // Rekap ke absensi otomatis
        $this->rekapKeAbsensi($perjalanan);

        return redirect()->route('karyawan.perjalanan-dinas.index')
            ->with('success', 'Pengajuan perjalanan dinas berhasil disetujui otomatis dan telah direkap ke absensi.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $perjalananDinas = PerjalananDinas::with(['karyawan', 'approver'])->findOrFail($id);

        if (Auth::user()->posisi !== 'hr' && $perjalananDinas->karyawan_id !== Auth::id()) {
            abort(403, 'Anda tidak memiliki akses ke data ini.');
        }

        return view('hr.perjalanan-dinas.show', compact('perjalananDinas'));
    }

    /**
     * Dashboard for Karyawan.
     */
    public function dashboard(Request $request)
    {
        $karyawanId = Auth::id();
        $query = PerjalananDinas::where('karyawan_id', $karyawanId);

        if ($request->filled('status') && $request->status !== 'semua') {
            $query->where('status', $request->status);
        }

        $perjalananDinas = $query->orderBy('created_at', 'desc')->paginate(10);

        $stats = [
            'total' => PerjalananDinas::where('karyawan_id', $karyawanId)->count(),
            'pending' => PerjalananDinas::where('karyawan_id', $karyawanId)->where('status', 'pending')->count(),
            'approved' => PerjalananDinas::where('karyawan_id', $karyawanId)->where('status', 'approved')->count(),
            'rejected' => PerjalananDinas::where('karyawan_id', $karyawanId)->where('status', 'rejected')->count(),
            'selesai' => PerjalananDinas::where('karyawan_id', $karyawanId)->where('status', 'selesai')->count(),
        ];

        return view('karyawan.perjalanan-dinas.index', compact('perjalananDinas', 'stats'));
    }

    /**
     * Mark as selesai (completed) by HR.
     */
    public function markAsSelesai($id)
    {
        $perjalananDinas = PerjalananDinas::findOrFail($id);

        if ($perjalananDinas->status !== 'approved') {
            return redirect()->back()->with('error', 'Hanya pengajuan yang sudah disetujui yang dapat ditandai selesai.');
        }

        $perjalananDinas->status = 'selesai';
        $perjalananDinas->save();

        return redirect()->route('hr.perjalanan-dinas.index')
            ->with('success', 'Perjalanan dinas ditandai sebagai selesai.');
    }

    /**
     * Download surat tugas file.
     */
    public function downloadSuratTugas($id)
    {
        $perjalananDinas = PerjalananDinas::findOrFail($id);

        if (Auth::user()->posisi !== 'hr' && $perjalananDinas->karyawan_id !== Auth::id()) {
            abort(403, 'Anda tidak memiliki akses ke file ini.');
        }

        if (!$perjalananDinas->surat_tugas || !Storage::disk('public')->exists($perjalananDinas->surat_tugas)) {
            abort(404, 'File surat tugas tidak ditemukan.');
        }

        return Storage::disk('public')->download($perjalananDinas->surat_tugas);
    }

    /**
     * Rekap perjalanan dinas ke tabel absensi untuk setiap hari dalam rentang tanggal.
     */
    private function rekapKeAbsensi(PerjalananDinas $perjalananDinas)
    {
        $start = Carbon::parse($perjalananDinas->tanggal_mulai);
        $end = Carbon::parse($perjalananDinas->tanggal_selesai);
        $karyawanId = $perjalananDinas->karyawan_id;
        $judul = $perjalananDinas->judul;

        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            $tanggal = $date->format('Y-m-d');

            $absensi = Absensi::where('karyawan_id', $karyawanId)
                ->whereDate('tanggal', $tanggal)
                ->first();

            if (!$absensi) {
                Absensi::create([
                    'karyawan_id' => $karyawanId,
                    'tanggal' => $tanggal,
                    'status' => 'Perjalanan Dinas',
                    'kantor_cabang' => 'Perjalanan Dinas',
                    'keterangan' => "Perjalanan Dinas: {$judul}",
                    'check_in' => null,
                    'check_out' => null,
                    'total_jam_kerja' => 0,
                    'is_valid_location' => false,
                    'ip_address' => null,
                    'user_agent' => null,
                    'is_suspicious' => false,
                    'suspicious_reason' => null,
                ]);
            } else {
                $absensi->status = 'Perjalanan Dinas';
                $absensi->kantor_cabang = 'Perjalanan Dinas';
                $absensi->keterangan = "Perjalanan Dinas: {$judul}";
                $absensi->check_in = null;
                $absensi->check_out = null;
                $absensi->total_jam_kerja = 0;
                $absensi->is_valid_location = false;
                $absensi->save();
            }
        }
    }
}
