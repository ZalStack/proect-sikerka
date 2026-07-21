<?php

namespace App\Http\Controllers;

use App\Models\PerjalananDinas;
use App\Models\Karyawan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
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
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'judul' => 'required|string|max:200',
            'agenda' => 'required|string',
            'tanggal_mulai' => 'required|date|after_or_equal:today',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'surat_tugas' => 'nullable|file|mimes:pdf|max:2048', // max 2MB
        ], [
            'surat_tugas.max' => 'Ukuran file surat tugas maksimal 2 MB.',
            'surat_tugas.mimes' => 'File surat tugas harus berformat PDF.',
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
        $data['status'] = 'pending';

        // Handle file upload
        if ($request->hasFile('surat_tugas')) {
            $file = $request->file('surat_tugas');

            // Validasi tambahan ukuran file
            if ($file->getSize() > 2 * 1024 * 1024) {
                return redirect()->back()
                    ->withErrors(['surat_tugas' => 'Ukuran file surat tugas maksimal 2 MB.'])
                    ->withInput();
            }

            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('surat_tugas', $filename, 'public');
            $data['surat_tugas'] = $path;
        }

        PerjalananDinas::create($data);

        return redirect()->route('karyawan.perjalanan-dinas.index')
            ->with('success', 'Pengajuan perjalanan dinas berhasil dikirim. Menunggu persetujuan HR.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $perjalananDinas = PerjalananDinas::with(['karyawan', 'approver'])->findOrFail($id);

        // Cek akses: hanya HR atau pemilik data
        if (Auth::user()->posisi !== 'hr' && $perjalananDinas->karyawan_id !== Auth::id()) {
            abort(403, 'Anda tidak memiliki akses ke data ini.');
        }

        return view('hr.perjalanan-dinas.show', compact('perjalananDinas'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $perjalananDinas = PerjalananDinas::findOrFail($id);

        // Hanya pemilik data yang bisa edit (jika masih pending)
        if ($perjalananDinas->karyawan_id !== Auth::id() || $perjalananDinas->status !== 'pending') {
            abort(403, 'Anda tidak dapat mengedit pengajuan ini.');
        }

        return view('karyawan.perjalanan-dinas.edit', compact('perjalananDinas'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $perjalananDinas = PerjalananDinas::findOrFail($id);

        // Hanya pemilik data yang bisa update (jika masih pending)
        if ($perjalananDinas->karyawan_id !== Auth::id() || $perjalananDinas->status !== 'pending') {
            abort(403, 'Anda tidak dapat mengedit pengajuan ini.');
        }

        $validator = Validator::make($request->all(), [
            'judul' => 'required|string|max:200',
            'agenda' => 'required|string',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'surat_tugas' => 'nullable|file|mimes:pdf|max:2048',
        ], [
            'surat_tugas.max' => 'Ukuran file surat tugas maksimal 2 MB.',
            'surat_tugas.mimes' => 'File surat tugas harus berformat PDF.',
            'tanggal_selesai.after_or_equal' => 'Tanggal selesai harus setelah atau sama dengan tanggal mulai.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $request->except(['surat_tugas']);

        // Handle file upload
        if ($request->hasFile('surat_tugas')) {
            $file = $request->file('surat_tugas');

            if ($file->getSize() > 2 * 1024 * 1024) {
                return redirect()->back()
                    ->withErrors(['surat_tugas' => 'Ukuran file surat tugas maksimal 2 MB.'])
                    ->withInput();
            }

            // Hapus file lama
            if ($perjalananDinas->surat_tugas) {
                Storage::disk('public')->delete($perjalananDinas->surat_tugas);
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
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $perjalananDinas = PerjalananDinas::findOrFail($id);

        // Hanya pemilik data yang bisa hapus (jika masih pending)
        if ($perjalananDinas->karyawan_id !== Auth::id() || $perjalananDinas->status !== 'pending') {
            abort(403, 'Anda tidak dapat menghapus pengajuan ini.');
        }

        // Hapus file
        if ($perjalananDinas->surat_tugas) {
            Storage::disk('public')->delete($perjalananDinas->surat_tugas);
        }

        $perjalananDinas->delete();

        return redirect()->route('karyawan.perjalanan-dinas.index')
            ->with('success', 'Pengajuan perjalanan dinas berhasil dihapus.');
    }

    /**
     * Dashboard for Karyawan.
     */
    public function dashboard(Request $request)
    {
        $karyawanId = Auth::id();
        $query = PerjalananDinas::where('karyawan_id', $karyawanId);

        // Filter by status
        if ($request->filled('status') && $request->status !== 'semua') {
            $query->where('status', $request->status);
        }

        $perjalananDinas = $query->orderBy('created_at', 'desc')->paginate(10);

        // Stats
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
     * Approve perjalanan dinas by HR.
     */
    public function approve(Request $request, $id)
    {
        $perjalananDinas = PerjalananDinas::findOrFail($id);

        if ($perjalananDinas->status !== 'pending') {
            return redirect()->back()->with('error', 'Pengajuan ini sudah diproses sebelumnya.');
        }

        $validator = Validator::make($request->all(), [
            'catatan_hr' => 'nullable|string|max:500',
            'status' => 'required|in:approved,rejected'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $perjalananDinas->status = $request->status;
        $perjalananDinas->approved_by = Auth::id();
        $perjalananDinas->approved_at = Carbon::now();

        if ($request->filled('catatan_hr')) {
            $perjalananDinas->catatan_hr = $request->catatan_hr;
        }

        $perjalananDinas->save();

        $message = $request->status === 'approved'
            ? 'Pengajuan perjalanan dinas berhasil disetujui.'
            : 'Pengajuan perjalanan dinas ditolak.';

        return redirect()->route('hr.perjalanan-dinas.index')
            ->with('success', $message);
    }

    /**
     * Bulk approve perjalanan dinas.
     */
    public function bulkApprove(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ids' => 'required|array',
            'ids.*' => 'exists:perjalanan_dinas,id',
            'status' => 'required|in:approved,rejected'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak valid.'
            ], 422);
        }

        $ids = $request->ids;
        $status = $request->status;

        $updated = PerjalananDinas::whereIn('id', $ids)
            ->where('status', 'pending')
            ->update([
                'status' => $status,
                'approved_by' => Auth::id(),
                'approved_at' => Carbon::now(),
                'catatan_hr' => $request->catatan_hr ?? null
            ]);

        return response()->json([
            'success' => true,
            'message' => "{$updated} pengajuan berhasil diproses.",
            'updated' => $updated
        ]);
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

        // Cek akses
        if (Auth::user()->posisi !== 'hr' && $perjalananDinas->karyawan_id !== Auth::id()) {
            abort(403, 'Anda tidak memiliki akses ke file ini.');
        }

        if (!$perjalananDinas->surat_tugas || !Storage::disk('public')->exists($perjalananDinas->surat_tugas)) {
            abort(404, 'File surat tugas tidak ditemukan.');
        }

        return Storage::disk('public')->download($perjalananDinas->surat_tugas);
    }
}
