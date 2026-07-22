<?php
// app/Http/Controllers/ProfileController.php

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
        return view('profile.edit', compact('user'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $rules = [
            'kode_pegawai' => 'required|unique:karyawans,kode_pegawai,' . $user->id,
            'nama_lengkap' => 'required|string|max:100',
            'email' => 'required|email|unique:karyawans,email,' . $user->id,
            'tempat_lahir' => 'nullable|string|max:50',
            'tanggal_lahir' => 'nullable|date',
            'jenis_kelamin' => 'nullable|in:Laki-laki,Perempuan',
            'nama_ibu_kandung' => 'nullable|string|max:100',
            'nik' => 'nullable|string|max:16|unique:karyawans,nik,' . $user->id,
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
            // Semua field profesional boleh diupdate semua role
            'jabatan' => 'required|string|max:100',
            'status' => 'required|in:Karyawan Tetap,Contract,Internship',
            'tanggal_bergabung' => 'required|date',
            'divisi' => 'required|string|max:50', // input text
            // Pendidikan lanjutan
            'is_continuing_education' => 'nullable|boolean',
            'continuing_program' => 'required_if:is_continuing_education,1|nullable|in:D3,D4/S1,S2,S3',
            'continuing_perguruan_tinggi' => 'required_if:is_continuing_education,1|nullable|string|max:100',
            'continuing_jurusan' => 'required_if:is_continuing_education,1|nullable|string|max:100',
        ];

        $request->validate($rules);

        $data = $request->except(['foto_profil', '_token', '_method']);

        $data['jumlah_anak'] = $data['jumlah_anak'] ?? 0;
        $data['nama_bank'] = 'BSI';
        $data['is_continuing_education'] = $request->has('is_continuing_education') ? true : false;

        // Update posisi berdasarkan divisi
        $data['posisi'] = $this->determinePosisi($request->divisi);

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

    private function determinePosisi($divisi)
    {
        return trim($divisi) === 'HRD' ? 'hr' : 'karyawan';
    }
}
