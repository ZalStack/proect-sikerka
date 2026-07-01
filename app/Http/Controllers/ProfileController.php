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
        $isHr = $user->role === 'hr';
        return view('profile.edit', compact('user', 'isHr'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        $isHr = $user->role === 'hr';

        $rules = [
            'nama_depan' => 'required|string|max:100',
            'nama_belakang' => 'required|string|max:100',
            'nama_lengkap' => 'required|string|max:100',
            'email' => 'required|email|unique:karyawans,email,' . $user->id,
            'nomor_telepon' => 'nullable|string|max:20',
            'alamat' => 'nullable|string',
            'nik' => 'required|string|max:16|unique:karyawans,nik,' . $user->id,
            'npwp' => 'nullable|string|max:20',
            'tempat_lahir' => 'required|string|max:50',
            'tanggal_lahir' => 'required|date',
            'jenis_kelamin' => 'required|in:Laki-laki,Perempuan',
            'agama' => 'required|string|max:20',
            'status_pernikahan' => 'required|string|max:20',
            'pendidikan_terakhir' => 'nullable|string|max:50',
            'pendidikan_terakhir_new' => 'nullable|in:SMP,SMA/MA,SMK,D1,D2,D3,D4,S1,S2',
            'universitas' => 'nullable|string|max:100',
            'jurusan' => 'nullable|string|max:100',
            'tahun_lulus' => 'nullable|integer|min:1900|max:' . date('Y'),
            'nama_kontak_darurat' => 'nullable|string|max:100',
            'telepon_kontak_darurat' => 'nullable|string|max:20',
            'foto_profil' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ];

        // HR bisa edit semua field, Karyawan tidak bisa edit field tertentu
        if (!$isHr) {
            // Karyawan tidak bisa edit: jabatan, role, status, tanggal_bergabung
            $rules['jabatan'] = 'sometimes|string|max:100';
            $rules['status'] = 'sometimes|in:Full-time,Contract,Internship';
            $rules['tanggal_bergabung'] = 'sometimes|date';
        } else {
            $rules['jabatan'] = 'required|string|max:100';
            $rules['status'] = 'required|in:Full-time,Contract,Internship';
            $rules['tanggal_bergabung'] = 'required|date';
        }

        $request->validate($rules);

        $data = $request->except(['foto_profil', '_token', '_method']);

        // Karyawan tidak boleh mengubah field tertentu
        if (!$isHr) {
            unset($data['jabatan']);
            unset($data['role']);
            unset($data['status']);
            unset($data['tanggal_bergabung']);
        }

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

        // Refresh user data
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
