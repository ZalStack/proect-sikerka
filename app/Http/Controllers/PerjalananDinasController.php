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
     * HR: index dengan filter
     */
    public function index(Request $request)
    {
        $query = PerjalananDinas::with('karyawan');

        // Filter status
        if ($request->filled('status') && $request->status !== 'semua') {
            $query->where('status', $request->status);
        }

        // Filter tanggal
        if ($request->filled('tanggal_mulai')) {
            $query->whereDate('tanggal_mulai', '>=', $request->tanggal_mulai);
        }
        if ($request->filled('tanggal_selesai')) {
            $query->whereDate('tanggal_selesai', '<=', $request->tanggal_selesai);
        }

        // Search
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
     * Karyawan: form create
     */
    public function create()
    {
        $karyawan = Auth::user();
        return view('karyawan.perjalanan-dinas.create', compact('karyawan'));
    }

    /**
     * Karyawan: store pengajuan (status pending)
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'judul' => 'required|string|max:200',
            'agenda' => 'required|string',
            'tanggal_mulai' => [
                'required',
                'date',
                'after_or_equal:' . now()->addDay()->toDateString(),
            ],
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'surat_tugas' => 'required|file|mimes:pdf|max:2048',
        ], [
            'surat_tugas.required' => 'Surat tugas wajib diupload.',
            'surat_tugas.max' => 'Ukuran file surat tugas maksimal 2 MB.',
            'surat_tugas.mimes' => 'File surat tugas harus berformat PDF.',
            'tanggal_mulai.after_or_equal' => 'Tanggal mulai minimal 1 hari dari hari ini.',
            'tanggal_selesai.after_or_equal' => 'Tanggal selesai harus setelah atau sama dengan tanggal mulai.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $data = $request->all();
        $data['karyawan_id'] = Auth::id();
        $data['tanggal_pengajuan'] = Carbon::today();
        $data['status'] = 'pending'; // status menunggu
        $data['approved_at'] = null;
        $data['approved_by'] = null;

        if ($request->hasFile('surat_tugas')) {
            $file = $request->file('surat_tugas');
            if ($file->getSize() > 2 * 1024 * 1024) {
                return redirect()->back()->withErrors(['surat_tugas' => 'Ukuran file maksimal 2 MB.'])->withInput();
            }
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('surat_tugas', $filename, 'public');
            $data['surat_tugas'] = $path;
        }

        PerjalananDinas::create($data);

        return redirect()->route('karyawan.perjalanan-dinas.index')
            ->with('success', 'Pengajuan perjalanan dinas berhasil dikirim dan menunggu persetujuan HRD.');
    }

    /**
     * Karyawan: edit form (hanya jika status pending)
     */
    public function edit($id)
    {
        $perjalananDinas = PerjalananDinas::where('karyawan_id', Auth::id())
            ->where('status', 'pending')
            ->findOrFail($id);

        return view('karyawan.perjalanan-dinas.edit', compact('perjalananDinas'));
    }

    /**
     * Karyawan: update pengajuan (hanya jika pending)
     */
    public function update(Request $request, $id)
    {
        $perjalananDinas = PerjalananDinas::where('karyawan_id', Auth::id())
            ->where('status', 'pending')
            ->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'judul' => 'required|string|max:200',
            'agenda' => 'required|string',
            'tanggal_mulai' => 'required|date|after_or_equal:' . now()->addDay()->toDateString(),
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'surat_tugas' => 'nullable|file|mimes:pdf|max:2048',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $data = $request->only(['judul', 'agenda', 'tanggal_mulai', 'tanggal_selesai']);

        if ($request->hasFile('surat_tugas')) {
            // Hapus file lama
            if ($perjalananDinas->surat_tugas && Storage::disk('public')->exists($perjalananDinas->surat_tugas)) {
                Storage::disk('public')->delete($perjalananDinas->surat_tugas);
            }
            $file = $request->file('surat_tugas');
            if ($file->getSize() > 2 * 1024 * 1024) {
                return redirect()->back()->withErrors(['surat_tugas' => 'Ukuran file maksimal 2 MB.'])->withInput();
            }
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('surat_tugas', $filename, 'public');
            $data['surat_tugas'] = $path;
        }

        $perjalananDinas->update($data);

        return redirect()->route('karyawan.perjalanan-dinas.index')
            ->with('success', 'Pengajuan perjalanan dinas berhasil diperbarui.');
    }

    /**
     * HR: approve pengajuan
     */
    public function approve($id)
    {
        $perjalananDinas = PerjalananDinas::findOrFail($id);

        if ($perjalananDinas->status !== 'pending') {
            return redirect()->back()->with('error', 'Hanya pengajuan dengan status menunggu yang dapat disetujui.');
        }

        $perjalananDinas->status = 'approved';
        $perjalananDinas->approved_at = Carbon::now();
        $perjalananDinas->approved_by = Auth::id();
        // Hapus catatan HR karena sudah disetujui (sesuai permintaan: "NOTES NYA LANGSUNG TERHAPUS SETELAH BERHASIL")
        $perjalananDinas->catatan_hr = null;
        $perjalananDinas->save();

        // Rekap ke absensi
        $this->rekapKeAbsensi($perjalananDinas);

        return redirect()->route('hr.perjalanan-dinas.index')
            ->with('success', 'Pengajuan perjalanan dinas berhasil disetujui dan telah direkap ke absensi.');
    }

    /**
     * HR: reject pengajuan (dengan catatan)
     */
    public function reject(Request $request, $id)
    {
        $request->validate([
            'catatan_hr' => 'required|string|max:1000',
        ]);

        $perjalananDinas = PerjalananDinas::findOrFail($id);

        if ($perjalananDinas->status !== 'pending') {
            return redirect()->back()->with('error', 'Hanya pengajuan dengan status menunggu yang dapat ditolak.');
        }

        $perjalananDinas->status = 'rejected';
        $perjalananDinas->catatan_hr = $request->catatan_hr;
        $perjalananDinas->approved_at = null;
        $perjalananDinas->approved_by = null;
        $perjalananDinas->save();

        return redirect()->route('hr.perjalanan-dinas.index')
            ->with('success', 'Pengajuan perjalanan dinas ditolak.');
    }

    /**
     * HR: hapus data perjalanan dinas (hanya jika status pending atau rejected)
     */
    public function destroy($id)
    {
        $perjalananDinas = PerjalananDinas::findOrFail($id);

        // Hanya boleh hapus jika status pending atau rejected
        if (!in_array($perjalananDinas->status, ['pending', 'rejected'])) {
            return redirect()->back()->with('error', 'Data yang sudah disetujui atau selesai tidak dapat dihapus.');
        }

        // Hapus file surat tugas
        if ($perjalananDinas->surat_tugas && Storage::disk('public')->exists($perjalananDinas->surat_tugas)) {
            Storage::disk('public')->delete($perjalananDinas->surat_tugas);
        }

        $perjalananDinas->delete();

        return redirect()->route('hr.perjalanan-dinas.index')
            ->with('success', 'Data perjalanan dinas berhasil dihapus.');
    }

    /**
     * HR: tandai selesai (hanya jika status approved)
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
     * HR & Karyawan: show detail
     */
    public function show($id)
    {
        $perjalananDinas = PerjalananDinas::with(['karyawan', 'approver'])->findOrFail($id);

        if (Auth::user()->posisi !== 'hr' && $perjalananDinas->karyawan_id !== Auth::id()) {
            abort(403, 'Anda tidak memiliki akses ke data ini.');
        }

        // Ambil previous dan next ID untuk navigasi (hanya untuk HR)
        $prevNext = null;
        if (Auth::user()->posisi === 'hr') {
            $prevNext = $this->getPrevNextIds($id);
        }

        return view('hr.perjalanan-dinas.show', compact('perjalananDinas', 'prevNext'));
    }

    /**
     * Karyawan: dashboard / index
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
     * Download surat tugas
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
     * HR: update catatan (catatan akan tetap ada sampai approve)
     */
    public function updateCatatan(Request $request, $id)
    {
        $perjalananDinas = PerjalananDinas::findOrFail($id);

        $request->validate([
            'catatan_hr' => 'nullable|string|max:1000',
        ]);

        $perjalananDinas->catatan_hr = $request->catatan_hr;
        $perjalananDinas->save();

        return redirect()->route('hr.perjalanan-dinas.show', $perjalananDinas->id)
            ->with('success', 'Catatan HR berhasil diperbarui.');
    }

    /**
     * Rekap ke absensi untuk setiap hari dalam rentang tanggal
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

    /**
     * Ambil previous dan next ID untuk navigasi (HR only)
     */
    private function getPrevNextIds($currentId)
    {
        $ids = PerjalananDinas::orderBy('created_at', 'desc')->pluck('id')->toArray();
        $index = array_search($currentId, $ids);
        return [
            'prev' => $index > 0 ? $ids[$index - 1] : null,
            'next' => $index < count($ids) - 1 ? $ids[$index + 1] : null,
        ];
    }
}
