<?php

namespace App\Http\Controllers;

use App\Models\Karyawan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProfileController extends Controller
{
    public function edit()
    {
        $user = Auth::user();
        $isHr = $user->posisi === 'hr'; // Ganti 'role' menjadi 'posisi'
        return view('profile.edit', compact('user', 'isHr'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        $isHr = $user->posisi === 'hr'; // Ganti 'role' menjadi 'posisi'

        $rules = [
            'kode_pegawai' => 'required|unique:karyawans,kode_pegawai,' . $user->id,
            'nama_depan' => 'required|string|max:100',
            'nama_belakang' => 'required|string|max:100',
            'nama_lengkap' => 'required|string|max:100',
            'email' => 'required|email|unique:karyawans,email,' . $user->id,
            'nomor_telepon' => 'nullable|string|max:20',
            'no_wa' => 'nullable|string|max:20',
            'alamat' => 'nullable|string',
            'nik' => 'nullable|string|max:16|unique:karyawans,nik,' . $user->id,
            'npwp' => 'nullable|string|max:20',
            'tempat_lahir' => 'nullable|string|max:50',
            'tanggal_lahir' => 'nullable|date',
            'jenis_kelamin' => 'nullable|in:Laki-laki,Perempuan',
            'agama' => 'nullable|string|max:20',
            'status_pernikahan' => 'nullable|string|max:20',
            'golongan_darah' => 'nullable|in:A,B,AB,O',
            'no_kk' => 'nullable|string|max:20',
            'nama_ibu_kandung' => 'nullable|string|max:100',
            'sedang_melanjutkan_pendidikan' => 'nullable|string|max:100',
            'jumlah_anak' => 'nullable|integer|min:0',
            'pendidikan_terakhir' => 'nullable|string|max:50',
            'pendidikan_terakhir_new' => 'nullable|in:SMP,SMA/MA,SMK,D1,D2,D3,D4,S1,S2',
            'perguruan_tinggi' => 'nullable|string|max:100',
            'jurusan' => 'nullable|string|max:100',
            'tahun_lulus' => 'nullable|integer|min:1900|max:' . date('Y'),
            'nama_kontak_darurat' => 'nullable|string|max:100',
            'telepon_kontak_darurat' => 'nullable|string|max:20',
            'foto_profil' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ];

        if ($isHr) {
            $rules['jabatan'] = 'required|string|max:100';
            $rules['status'] = 'required|in:Full-time,Contract,Internship';
            $rules['tanggal_bergabung'] = 'required|date';
            $rules['divisi'] = 'required|in:HRD,IT,KPD,LPS,MEDIA,PENDIDIKAN,PKA,RG,SAPRAS';
        }

        $request->validate($rules);

        $data = $request->except(['foto_profil', '_token', '_method']);

        if (!$isHr) {
            unset($data['jabatan']);
            unset($data['posisi']);
            unset($data['status']);
            unset($data['tanggal_bergabung']);
            unset($data['divisi']);
        }

        $data['jumlah_anak'] = $data['jumlah_anak'] ?? 0;

        if ($request->hasFile('foto_profil')) {
            if ($user->foto_profil) {
                Storage::disk('public')->delete($user->foto_profil);
            }
            $file = $request->file('foto_profil');
            $filename = time() . '_' . Str::slug($request->nama_lengkap) . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('foto_profil', $filename, 'public');
            $data['foto_profil'] = $path;
        }

        $user->update($data);
        $user->refresh();

        return redirect()->back()->with('success', 'Profile berhasil diupdate');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|min:8|confirmed',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->kata_sandi)) {
            return back()->withErrors(['current_password' => 'Password saat ini salah']);
        }

        $user->update([
            'kata_sandi' => Hash::make($request->password)
        ]);

        return redirect()->back()->with('success', 'Password berhasil diupdate');
    }
}
