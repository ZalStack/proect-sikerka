<?php

namespace App\Http\Controllers;

use App\Models\Karyawan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class KaryawanController extends Controller
{
    public function index()
    {
        $karyawans = Karyawan::all();
        return view('hr.karyawan.index', compact('karyawans'));
    }

    public function create()
    {
        return view('hr.karyawan.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nip' => 'required|unique:karyawans,nip',
            'email' => 'required|email|unique:karyawans,email',
            'password' => 'required|min:8|confirmed',
            'nama_depan' => 'required|string|max:100',
            'nama_belakang' => 'required|string|max:100',
            'nama_lengkap' => 'required|string|max:100',
            'jabatan' => 'required|string|max:100',
            'role' => 'nullable|in:hr,karyawan',
            'divisi' => 'nullable|in:MEDIA,KPD,IT,HRD,LPS,PKA,RG,SAPRAS,PENDIDIKAN',
            'status' => 'required|in:Full-time,Contract,Internship',
            'tanggal_bergabung' => 'required|date',
            'golongan_darah' => 'nullable|in:A,B,AB,O',
            'no_kk' => 'nullable|string|max:20',
            'gelar_pendidikan' => 'nullable|string|max:50',
            'sedang_melanjutkan_pendidikan' => 'nullable|string|max:100',
            'jumlah_anak' => 'nullable|integer|min:0',
            'nik' => 'nullable|string|max:16|unique:karyawans,nik',
            'npwp' => 'nullable|string|max:20',
            'tempat_lahir' => 'nullable|string|max:50',
            'tanggal_lahir' => 'nullable|date',
            'jenis_kelamin' => 'nullable|in:Laki-laki,Perempuan',
            'agama' => 'nullable|string|max:20',
            'status_pernikahan' => 'nullable|string|max:20',
            'pendidikan_terakhir' => 'nullable|string|max:50',
            'pendidikan_terakhir_new' => 'nullable|in:SMP,SMA/MA,SMK,D1,D2,D3,D4,S1,S2',
            'universitas' => 'nullable|string|max:100',
            'jurusan' => 'nullable|string|max:100',
            'tahun_lulus' => 'nullable|integer|min:1900|max:' . date('Y'),
            'nomor_telepon' => 'nullable|string|max:20',
            'alamat' => 'nullable|string',
            'foto_profil' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'nama_kontak_darurat' => 'nullable|string|max:100',
            'telepon_kontak_darurat' => 'nullable|string|max:20',
        ]);

        $data = $request->except(['password', 'password_confirmation', 'foto_profil']);

        $data['kata_sandi'] = Hash::make($request->password);
        $data['total_hari_kerja'] = 0;
        $data['role'] = $request->role ?? 'karyawan';
        $data['jumlah_anak'] = $request->jumlah_anak ?? 0;

        if ($request->hasFile('foto_profil')) {
            $file = $request->file('foto_profil');
            $filename = time() . '_' . Str::slug($request->nama_lengkap) . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('foto_profil', $filename, 'public');
            $data['foto_profil'] = $path;
        }

        Karyawan::create($data);

        return redirect()->route('hr.karyawan.index')
            ->with('success', 'Karyawan berhasil ditambahkan');
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
            'nip' => 'required|unique:karyawans,nip,' . $id,
            'email' => 'required|email|unique:karyawans,email,' . $id,
            'nama_depan' => 'required|string|max:100',
            'nama_belakang' => 'required|string|max:100',
            'nama_lengkap' => 'required|string|max:100',
            'jabatan' => 'required|string|max:100',
            'role' => 'nullable|in:hr,karyawan',
            'divisi' => 'nullable|in:MEDIA,KPD,IT,HRD,LPS,PKA,RG,SAPRAS,PENDIDIKAN',
            'status' => 'required|in:Full-time,Contract,Internship',
            'tanggal_bergabung' => 'required|date',
            'end_date' => 'nullable|date|after:tanggal_bergabung',
            'golongan_darah' => 'nullable|in:A,B,AB,O',
            'no_kk' => 'nullable|string|max:20',
            'gelar_pendidikan' => 'nullable|string|max:50',
            'sedang_melanjutkan_pendidikan' => 'nullable|string|max:100',
            'jumlah_anak' => 'nullable|integer|min:0',
            'reason_resigned' => 'nullable|string',
            'nik' => 'nullable|string|max:16|unique:karyawans,nik,' . $id,
            'npwp' => 'nullable|string|max:20',
            'tempat_lahir' => 'nullable|string|max:50',
            'tanggal_lahir' => 'nullable|date',
            'jenis_kelamin' => 'nullable|in:Laki-laki,Perempuan',
            'agama' => 'nullable|string|max:20',
            'status_pernikahan' => 'nullable|string|max:20',
            'pendidikan_terakhir' => 'nullable|string|max:50',
            'pendidikan_terakhir_new' => 'nullable|in:SMP,SMA/MA,SMK,D1,D2,D3,D4,S1,S2',
            'universitas' => 'nullable|string|max:100',
            'jurusan' => 'nullable|string|max:100',
            'tahun_lulus' => 'nullable|integer|min:1900|max:' . date('Y'),
            'nomor_telepon' => 'nullable|string|max:20',
            'alamat' => 'nullable|string',
            'foto_profil' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'nama_kontak_darurat' => 'nullable|string|max:100',
            'telepon_kontak_darurat' => 'nullable|string|max:20',
            'password' => 'nullable|min:8|confirmed',
            'total_hari_kerja' => 'nullable|integer|min:0',
        ]);

        $data = $request->except(['password', 'password_confirmation', 'foto_profil', '_token', '_method']);

        $data['total_hari_kerja'] = $data['total_hari_kerja'] ?? 0;
        $data['jumlah_anak'] = $data['jumlah_anak'] ?? 0;

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

        return redirect()->route('hr.karyawan.index')
            ->with('success', 'Karyawan berhasil diupdate');
    }

    public function destroy($id)
    {
        $karyawan = Karyawan::findOrFail($id);
        if ($karyawan->foto_profil) {
            Storage::disk('public')->delete($karyawan->foto_profil);
        }
        $karyawan->delete();

        return redirect()->route('hr.karyawan.index')
            ->with('success', 'Karyawan berhasil dihapus');
    }
}
