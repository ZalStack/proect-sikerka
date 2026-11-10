<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Karyawan;
use App\Models\MessageTemplate;
use App\Models\Pengumuman;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Broadcast pengumuman dari Super Admin (Fitur C9) — Fitur 'pengumuman'.
 * Mengirim informasi ke seluruh karyawan atau karyawan tertentu,
 * langsung masuk ke daftar notifikasi mereka.
 */
class BroadcastController extends Controller
{
    public function index()
    {
        $broadcasts = Pengumuman::with('creator')
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();

        $recipients = Karyawan::active()
            ->orderBy('nama_lengkap')
            ->get(['id', 'nama_lengkap', 'divisi', 'posisi']);

        $templates = MessageTemplate::where('type', 'broadcast')
            ->where('is_active', true)
            ->orderByDesc('is_default')
            ->get();

        $totalAudience = $recipients->count();

        return view('superadmin.broadcast', compact('broadcasts', 'recipients', 'templates', 'totalAudience'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'judul' => ['required', 'string', 'max:200'],
            'isi' => ['required', 'string', 'max:10000'],
            'target' => ['required', 'in:semua,spesifik'],
            'target_karyawan' => ['required_if:target,spesifik', 'array'],
            'target_karyawan.*' => ['integer', 'exists:karyawans,id'],
            'gambar' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
        ]);

        // target_karyawan boleh kosong saat target = semua.
        $targetIds = $data['target'] === 'spesifik'
            ? array_values((array) ($data['target_karyawan'] ?? []))
            : null;

        if ($data['target'] === 'spesifik' && empty($targetIds)) {
            return back()
                ->with('error', 'Pilih minimal satu penerima untuk broadcast spesifik.')
                ->withInput();
        }

        $payload = [
            'judul' => $data['judul'],
            'isi' => $data['isi'],
            'target' => $data['target'],
            // Jangan json_encode manual: kolom sudah di-cast 'array' pada model.
            'target_karyawan_ids' => $targetIds,
            'created_by' => Auth::id(),
        ];

        if ($request->hasFile('gambar')) {
            $file = $request->file('gambar');
            $filename = time() . '_' . Str::slug($data['judul']) . '.' . $file->getClientOriginalExtension();
            $payload['gambar'] = $file->storeAs('pengumuman', $filename, 'public');
        }

        try {
            $pengumuman = DB::transaction(fn () => Pengumuman::create($payload));
        } catch (\Throwable $e) {
            report($e);

            return back()->with('error', 'Gagal mengirim broadcast: ' . $e->getMessage())->withInput();
        }

        $audience = $data['target'] === 'semua'
            ? Karyawan::active()->count()
            : count($targetIds);

        ActivityLog::record(
            'broadcast.send',
            'Broadcast "' . $data['judul'] . '" dikirim ke ' . $audience . ' penerima.',
            [
                'module' => 'features',
                'subject_type' => 'pengumuman',
                'subject_id' => $pengumuman->id,
                'metadata' => [
                    'target' => $data['target'],
                    'audience' => $audience,
                ],
            ]
        );

        return redirect()->route('superadmin.broadcast.index')
            ->with('success', 'Broadcast berhasil dikirim ke ' . $audience . ' penerima.');
    }

    public function destroy(Request $request, int $id)
    {
        $pengumuman = Pengumuman::findOrFail($id);

        try {
            if (!empty($pengumuman->gambar) && Storage::disk('public')->exists($pengumuman->gambar)) {
                Storage::disk('public')->delete($pengumuman->gambar);
            }

            $pengumuman->delete();
        } catch (\Throwable $e) {
            report($e);

            return redirect()->route('superadmin.broadcast.index')
                ->with('error', 'Broadcast gagal dihapus: ' . $e->getMessage());
        }

        ActivityLog::record(
            'broadcast.delete',
            'Broadcast "' . $pengumuman->judul . '" dihapus.',
            ['module' => 'features', 'subject_type' => 'pengumuman', 'subject_id' => $pengumuman->id]
        );

        return redirect()->route('superadmin.broadcast.index')
            ->with('success', 'Broadcast berhasil dihapus.');
    }
}
