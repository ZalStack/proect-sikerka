<?php
// app/Http/Controllers/KaryawanController.php

namespace App\Http\Controllers;

use App\Models\Karyawan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class KaryawanController extends Controller
{
    public function index(Request $request)
    {
        $query = Karyawan::orderBy('kode_pegawai', 'asc');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nama_lengkap', 'LIKE', "%{$search}%")
                    ->orWhere('kode_pegawai', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%")
                    ->orWhere('jabatan', 'LIKE', "%{$search}%")
                    ->orWhere('divisi', 'LIKE', "%{$search}%");
            });
        }

        if ($request->filled('status_filter')) {
            if ($request->status_filter === 'active') {
                $query->active();
            } elseif ($request->status_filter === 'resigned') {
                $query->resigned();
            }
        }

        $karyawans = $query->paginate(10)->withQueryString();

        return view('hr.karyawan.index', compact('karyawans'));
    }

    public function create()
    {
        return view('hr.karyawan.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode_pegawai' => 'required|unique:karyawans,kode_pegawai',
            'email' => 'required|email|unique:karyawans,email',
            'password' => 'required|min:8|confirmed',
            'nama_lengkap' => 'required|string|max:100',
            'jabatan' => 'required|string|max:100',
            'divisi' => 'required|string|max:50', // Ubah menjadi string, tidak ada in:...
            'status' => 'required|in:Karyawan Tetap,Contract,Internship',
            'tanggal_bergabung' => 'required|date',
            'tempat_lahir' => 'nullable|string|max:50',
            'tanggal_lahir' => 'nullable|date',
            'jenis_kelamin' => 'nullable|in:Laki-laki,Perempuan',
            'nama_ibu_kandung' => 'nullable|string|max:100',
            'nik' => 'nullable|string|max:16|unique:karyawans,nik',
            'no_kk' => 'nullable|string|max:20',
            'status_pernikahan' => 'nullable|in:Belum Menikah,Menikah,Cerai',
            'jumlah_anak' => 'nullable|integer|min:0',
            'golongan_darah' => 'nullable|in:A,B,AB,O',
            'npwp' => 'nullable|string|max:20',
            'pendidikan_terakhir' => 'nullable|string|max:50',
            'perguruan_tinggi' => 'nullable|string|max:100',
            'jurusan' => 'nullable|string|max:100',
            'tahun_lulus' => 'nullable|integer|min:1900|max:' . date('Y'),
            'nomor_telepon' => 'nullable|string|max:20',
            'no_wa' => 'nullable|string|max:20',
            'alamat' => 'nullable|string',
            'nama_kontak_darurat' => 'nullable|string|max:100',
            'telepon_kontak_darurat' => 'nullable|string|max:20',
            'foto_profil' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'tanggal_pengangkatan_tetap' => 'nullable|date',
            'nomor_rekening' => 'nullable|string|max:50',
            'ipk_terakhir' => 'nullable|numeric|min:0|max:4',
            'alamat_domisili' => 'nullable|string',
            'is_continuing_education' => 'nullable|boolean',
            'continuing_program' => 'required_if:is_continuing_education,1|nullable|in:D3,D4/S1,S2,S3',
            'continuing_perguruan_tinggi' => 'required_if:is_continuing_education,1|nullable|string|max:100',
            'continuing_jurusan' => 'required_if:is_continuing_education,1|nullable|string|max:100',
        ]);

        $data = $request->except(['password', 'password_confirmation', 'foto_profil']);

        $data['kata_sandi'] = Hash::make($request->password);
        $data['jumlah_anak'] = $request->jumlah_anak ?? 0;
        // Tentukan posisi berdasarkan divisi (HRD -> hr, lainnya -> karyawan)
        $data['posisi'] = $this->determinePosisi($request->divisi);
        $data['nama_bank'] = 'BSI';
        $data['is_resigned'] = false;
        $data['tanggal_resign'] = null;
        $data['is_continuing_education'] = $request->has('is_continuing_education') ? true : false;

        if ($request->hasFile('foto_profil')) {
            $file = $request->file('foto_profil');
            $filename = time() . '_' . Str::slug($request->nama_lengkap) . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('foto_profil', $filename, 'public');
            $data['foto_profil'] = $path;
        }

        Karyawan::create($data);

        return redirect()->route('hr.karyawan.index')->with('success', 'Karyawan berhasil ditambahkan');
    }

    public function show($id)
    {
        $karyawan = Karyawan::findOrFail($id);
        return view('hr.karyawan.show', compact('karyawan'));
    }

    public function edit($id)
    {
        $karyawan = Karyawan::findOrFail($id);
        return view('hr.karyawan.edit', compact('karyawan'));
    }

    public function update(Request $request, $id)
    {
        $karyawan = Karyawan::findOrFail($id);

        $request->validate([
            'kode_pegawai' => 'required|unique:karyawans,kode_pegawai,' . $id,
            'email' => 'required|email|unique:karyawans,email,' . $id,
            'nama_lengkap' => 'required|string|max:100',
            'jabatan' => 'required|string|max:100',
            'divisi' => 'required|string|max:50', // Ubah menjadi string
            'status' => 'required|in:Karyawan Tetap,Contract,Internship',
            'tanggal_bergabung' => 'required|date',
            'end_date' => 'nullable|date|after:tanggal_bergabung',
            'tempat_lahir' => 'nullable|string|max:50',
            'tanggal_lahir' => 'nullable|date',
            'jenis_kelamin' => 'nullable|in:Laki-laki,Perempuan',
            'nama_ibu_kandung' => 'nullable|string|max:100',
            'nik' => 'nullable|string|max:16|unique:karyawans,nik,' . $id,
            'no_kk' => 'nullable|string|max:20',
            'status_pernikahan' => 'nullable|in:Belum Menikah,Menikah,Cerai',
            'jumlah_anak' => 'nullable|integer|min:0',
            'golongan_darah' => 'nullable|in:A,B,AB,O',
            'npwp' => 'nullable|string|max:20',
            'pendidikan_terakhir' => 'nullable|string|max:50',
            'perguruan_tinggi' => 'nullable|string|max:100',
            'jurusan' => 'nullable|string|max:100',
            'tahun_lulus' => 'nullable|integer|min:1900|max:' . date('Y'),
            'nomor_telepon' => 'nullable|string|max:20',
            'no_wa' => 'nullable|string|max:20',
            'alamat' => 'nullable|string',
            'nama_kontak_darurat' => 'nullable|string|max:100',
            'telepon_kontak_darurat' => 'nullable|string|max:20',
            'foto_profil' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'password' => 'nullable|min:8|confirmed',
            'tanggal_pengangkatan_tetap' => 'nullable|date',
            'nomor_rekening' => 'nullable|string|max:50',
            'ipk_terakhir' => 'nullable|numeric|min:0|max:4',
            'alamat_domisili' => 'nullable|string',
            'is_resigned' => 'nullable|boolean',
            'tanggal_resign' => 'nullable|required_if:is_resigned,1|date|after_or_equal:tanggal_bergabung',
            'is_continuing_education' => 'nullable|boolean',
            'continuing_program' => 'required_if:is_continuing_education,1|nullable|in:D3,D4/S1,S2,S3',
            'continuing_perguruan_tinggi' => 'required_if:is_continuing_education,1|nullable|string|max:100',
            'continuing_jurusan' => 'required_if:is_continuing_education,1|nullable|string|max:100',
        ]);

        $data = $request->except(['password', 'password_confirmation', 'foto_profil', '_token', '_method']);

        $data['jumlah_anak'] = $data['jumlah_anak'] ?? 0;
        // Tentukan posisi berdasarkan divisi
        $data['posisi'] = $this->determinePosisi($request->divisi);
        $data['nama_bank'] = 'BSI';
        $data['is_continuing_education'] = $request->has('is_continuing_education') ? true : false;

        // Handle resign
        $data['is_resigned'] = $request->has('is_resigned') ? true : false;
        if (!$data['is_resigned']) {
            $data['tanggal_resign'] = null;
        }

        if ($request->hasFile('foto_profil')) {
            if ($karyawan->foto_profil) {
                Storage::disk('public')->delete($karyawan->foto_profil);
            }
            $file = $request->file('foto_profil');
            $filename = time() . '_' . Str::slug($request->nama_lengkap) . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('foto_profil', $filename, 'public');
            $data['foto_profil'] = $path;
        }

        if ($request->filled('password')) {
            $data['kata_sandi'] = Hash::make($request->password);
        }

        $karyawan->update($data);

        return redirect()->route('hr.karyawan.index')->with('success', 'Karyawan berhasil diupdate');
    }

    public function destroy($id)
    {
        $karyawan = Karyawan::findOrFail($id);
        if ($karyawan->foto_profil) {
            Storage::disk('public')->delete($karyawan->foto_profil);
        }
        $karyawan->delete();

        return redirect()->route('hr.karyawan.index')->with('success', 'Karyawan berhasil dihapus');
    }

    private function determinePosisi($divisi)
    {
        // Jika divisi tepat sama dengan "HRD" (case sensitive), maka posisi hr, selain itu karyawan
        return trim($divisi) === 'HRD' ? 'hr' : 'karyawan';
    }
}
