<?php

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
        $karyawans = Karyawan::orderBy('kode_pegawai', 'asc')->paginate(10);

        if ($request->filled('search')) {
            $search = $request->search;
            $karyawans = Karyawan::where('nama_lengkap', 'LIKE', "%{$search}%")
                ->orWhere('kode_pegawai', 'LIKE', "%{$search}%")
                ->orWhere('email', 'LIKE', "%{$search}%")
                ->orWhere('jabatan', 'LIKE', "%{$search}%")
                ->orWhere('divisi', 'LIKE', "%{$search}%")
                ->orderBy('kode_pegawai', 'asc')
                ->paginate(10)
                ->withQueryString();
        }

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
            'divisi' => 'required|in:HRD,IT,KPD,LPS,MEDIA,PENDIDIKAN,PKA,RG,SAPRAS',
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
            'pendidikan_terakhir_new' => 'nullable|in:SMP,SMA/MA,SMK,D1,D2,D3,D4,S1,S2',
            'sedang_melanjutkan_pendidikan' => 'nullable|string|max:100',
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
        ]);

        $data = $request->except(['password', 'password_confirmation', 'foto_profil']);

        $data['kata_sandi'] = Hash::make($request->password);
        $data['jumlah_anak'] = $request->jumlah_anak ?? 0;
        $data['posisi'] = $this->determinePosisi($request->divisi);
        $data['nama_bank'] = 'BSI';

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
            'divisi' => 'required|in:HRD,IT,KPD,LPS,MEDIA,PENDIDIKAN,PKA,RG,SAPRAS',
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
            'pendidikan_terakhir_new' => 'nullable|in:SMP,SMA/MA,SMK,D1,D2,D3,D4,S1,S2',
            'sedang_melanjutkan_pendidikan' => 'nullable|string|max:100',
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
        ]);

        $data = $request->except(['password', 'password_confirmation', 'foto_profil', '_token', '_method']);

        $data['jumlah_anak'] = $data['jumlah_anak'] ?? 0;
        $data['posisi'] = $this->determinePosisi($request->divisi);
        $data['nama_bank'] = 'BSI';

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
        return $divisi === 'HRD' ? 'hr' : 'karyawan';
    }
}
