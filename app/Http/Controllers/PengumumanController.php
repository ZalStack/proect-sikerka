<?php

namespace App\Http\Controllers;

use App\Models\Pengumuman;
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
        return view('hr.pengumuman.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'judul' => 'required|string|max:200',
            'isi' => 'required|string',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'target' => 'required|in:semua,hr,karyawan',
        ]);

        $data = [
            'judul' => $request->judul,
            'isi' => $request->isi,
            'target' => $request->target,
            'created_by' => Auth::id(),
        ];

        if ($request->hasFile('gambar')) {
            $file = $request->file('gambar');
            $filename = time() . '_' . Str::slug($request->judul) . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('pengumuman', $filename, 'public');
            $data['gambar'] = $path;
        }

        try {
            Pengumuman::create($data);

            // Tidak perlu logika kirim WhatsApp lagi.
            // Notifikasi "Pengumuman Baru" otomatis muncul lewat NotificationService,
            // langsung menarik dari tabel pengumuman ini (tanpa tabel tambahan).
            return redirect()->route('hr.pengumuman.index')
                ->with('success', 'Pengumuman berhasil ditambahkan');

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyimpan pengumuman: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Detail pengumuman untuk role HR. Ini juga tujuan klik notifikasi
     * "Pengumuman Baru" / "Pengumuman Diperbarui" untuk user HR.
     */
    public function show($id)
    {
        $pengumuman = Pengumuman::with('creator')->findOrFail($id);

        return view('hr.pengumuman.show', compact('pengumuman'));
    }

    /**
     * Detail pengumuman untuk role karyawan. Ini tujuan klik notifikasi
     * "Pengumuman Baru" / "Pengumuman Diperbarui" untuk user karyawan.
     * Hanya bisa diakses kalau target pengumuman memang untuk karyawan.
     */
    public function showKaryawan($id)
    {
        $pengumuman = Pengumuman::with('creator')
            ->whereIn('target', ['semua', 'karyawan'])
            ->findOrFail($id);

        return view('karyawan.pengumuman.show', compact('pengumuman'));
    }

    public function edit($id)
    {
        $pengumuman = Pengumuman::findOrFail($id);

        return view('hr.pengumuman.edit', compact('pengumuman'));
    }

    public function update(Request $request, $id)
    {
        $pengumuman = Pengumuman::findOrFail($id);

        $request->validate([
            'judul' => 'required|string|max:200',
            'isi' => 'required|string',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'target' => 'required|in:semua,hr,karyawan',
        ]);

        $data = [
            'judul' => $request->judul,
            'isi' => $request->isi,
            'target' => $request->target,
        ];

        if ($request->hasFile('gambar')) {
            if ($pengumuman->gambar) {
                Storage::disk('public')->delete($pengumuman->gambar);
            }
            $file = $request->file('gambar');
            $filename = time() . '_' . Str::slug($request->judul) . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('pengumuman', $filename, 'public');
            $data['gambar'] = $path;
        }

        // update() otomatis mengubah kolom updated_at, dari situ
        // NotificationService mendeteksi event "Pengumuman Diperbarui".
        $pengumuman->update($data);

        return redirect()->route('hr.pengumuman.index')
            ->with('success', 'Pengumuman berhasil diupdate');
    }

    public function destroy($id)
    {
        $pengumuman = Pengumuman::findOrFail($id);

        if ($pengumuman->gambar) {
            Storage::disk('public')->delete($pengumuman->gambar);
        }

        // Soft delete: baris tetap ada di tabel pengumuman (kolom deleted_at terisi).
        // Dengan begitu NotificationService masih bisa menampilkan
        // notifikasi "Pengumuman Dihapus" tanpa perlu tabel log terpisah,
        // dan pengumuman otomatis hilang dari listing/index karena SoftDeletes.
        $pengumuman->delete();

        return redirect()->route('hr.pengumuman.index')
            ->with('success', 'Pengumuman berhasil dihapus');
    }
}
