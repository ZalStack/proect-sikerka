<?php

namespace App\Http\Controllers;

use App\Models\Pengumuman;
use App\Models\Karyawan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PengumumanController extends Controller
{
    /**
     * Daftar pengumuman (halaman HR).
     */
    public function index()
    {
        $pengumuman = Pengumuman::with('creator')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('hr.pengumuman.index', compact('pengumuman'));
    }

    public function create()
    {
        $karyawan = Karyawan::where('is_resigned', false)
            ->orderBy('nama_lengkap')
            ->get(['id', 'nama_lengkap', 'kode_pegawai', 'posisi', 'divisi']);

        return view('hr.pengumuman.create', compact('karyawan'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'judul' => 'required|string|max:200',
            'isi' => 'required|string',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'target' => 'required|in:semua,spesifik',
            'target_karyawan' => 'required_if:target,spesifik|array',
            'target_karyawan.*' => 'exists:karyawans,id',
        ]);

        $data = [
            'judul' => $request->judul,
            'isi' => $request->isi,
            'target' => $request->target,
            'created_by' => Auth::id(),
        ];

        // Jika target spesifik, simpan sebagai JSON
        if ($request->target === 'spesifik' && $request->has('target_karyawan')) {
            $data['target_karyawan_ids'] = json_encode($request->target_karyawan);
        } else {
            $data['target_karyawan_ids'] = null;
        }

        if ($request->hasFile('gambar')) {
            $file = $request->file('gambar');
            $filename = time() . '_' . Str::slug($request->judul) . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('pengumuman', $filename, 'public');
            $data['gambar'] = $path;
        }

        try {
            Pengumuman::create($data);

            return redirect()->route('hr.pengumuman.index')
                ->with('success', 'Pengumuman berhasil ditambahkan');

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyimpan pengumuman: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Detail pengumuman untuk role HR.
     */
    public function show($id)
    {
        $pengumuman = Pengumuman::with('creator')->findOrFail($id);

        // Tambahkan target_karyawan_list ke object
        $pengumuman->target_karyawan_list = $pengumuman->target_karyawan_list;

        return view('hr.pengumuman.show', compact('pengumuman'));
    }

    /**
     * Detail pengumuman untuk role karyawan.
     */
    public function showKaryawan($id)
    {
        $karyawanId = Auth::id();

        // Cari pengumuman yang dapat dilihat oleh karyawan
        $pengumuman = Pengumuman::with('creator')
            ->where(function($query) use ($karyawanId) {
                $query->where('target', 'semua')
                    ->orWhere(function($q) use ($karyawanId) {
                        $q->where('target', 'spesifik')
                            ->whereRaw('JSON_CONTAINS(target_karyawan_ids, ?)', [json_encode((string)$karyawanId)]);
                    });
            })
            ->findOrFail($id);

        // Tambahkan target_karyawan_list ke object
        $pengumuman->target_karyawan_list = $pengumuman->target_karyawan_list;

        return view('karyawan.pengumuman.show', compact('pengumuman'));
    }

    public function edit($id)
    {
        $pengumuman = Pengumuman::findOrFail($id);

        // Decode target karyawan jika spesifik
        if ($pengumuman->target === 'spesifik' && $pengumuman->target_karyawan_ids) {
            $pengumuman->target_karyawan_ids = is_array($pengumuman->target_karyawan_ids)
                ? $pengumuman->target_karyawan_ids
                : json_decode($pengumuman->target_karyawan_ids, true);
        } else {
            $pengumuman->target_karyawan_ids = [];
        }

        $karyawan = Karyawan::where('is_resigned', false)
            ->orderBy('nama_lengkap')
            ->get(['id', 'nama_lengkap', 'kode_pegawai', 'posisi', 'divisi']);

        return view('hr.pengumuman.edit', compact('pengumuman', 'karyawan'));
    }

    public function update(Request $request, $id)
    {
        $pengumuman = Pengumuman::findOrFail($id);

        $request->validate([
            'judul' => 'required|string|max:200',
            'isi' => 'required|string',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'target' => 'required|in:semua,spesifik',
            'target_karyawan' => 'required_if:target,spesifik|array',
            'target_karyawan.*' => 'exists:karyawans,id',
        ]);

        $data = [
            'judul' => $request->judul,
            'isi' => $request->isi,
            'target' => $request->target,
        ];

        // Jika target spesifik, simpan sebagai JSON
        if ($request->target === 'spesifik' && $request->has('target_karyawan')) {
            $data['target_karyawan_ids'] = json_encode($request->target_karyawan);
        } else {
            $data['target_karyawan_ids'] = null;
        }

        if ($request->hasFile('gambar')) {
            if ($pengumuman->gambar) {
                Storage::disk('public')->delete($pengumuman->gambar);
            }
            $file = $request->file('gambar');
            $filename = time() . '_' . Str::slug($request->judul) . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('pengumuman', $filename, 'public');
            $data['gambar'] = $path;
        }

        try {
            $pengumuman->update($data);

            return redirect()->route('hr.pengumuman.index')
                ->with('success', 'Pengumuman berhasil diupdate');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mengupdate pengumuman: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function destroy($id)
    {
        $pengumuman = Pengumuman::findOrFail($id);

        if ($pengumuman->gambar) {
            Storage::disk('public')->delete($pengumuman->gambar);
        }

        $pengumuman->delete();

        return redirect()->route('hr.pengumuman.index')
            ->with('success', 'Pengumuman berhasil dihapus');
    }
}
