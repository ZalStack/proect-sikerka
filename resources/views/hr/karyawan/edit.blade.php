@extends('layouts.app')

@section('content')
    <div class="flex">
        @include('layouts.sidebar')
        <div class="ml-64 flex-1 p-6">
            <div class="mb-6">
                <h1 class="text-2xl font-bold font-['Montserrat'] text-[#161758]">Edit Karyawan</h1>
                <p class="text-[#27438D]">Update data karyawan</p>
            </div>

            <form action="{{ route('hr.karyawan.update', $karyawan->id) }}" method="POST" enctype="multipart/form-data"
                class="bg-white rounded-lg shadow-md p-6">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Informasi Pribadi -->
                    <div class="md:col-span-2">
                        <h3 class="text-lg font-semibold text-[#161758] border-b border-gray-200 pb-2 mb-4">Informasi
                            Pribadi</h3>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-[#1B1B1B] mb-1">NIP *</label>
                        <input type="text" name="nip" value="{{ old('nip', $karyawan->nip) }}" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                        @error('nip')
                            <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Email *</label>
                        <input type="email" name="email" value="{{ old('email', $karyawan->email) }}" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                        @error('email')
                            <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Password Baru</label>
                        <input type="password" name="password"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                        <p class="text-xs text-gray-500 mt-1">Kosongkan jika tidak ingin mengubah password</p>
                        @error('password')
                            <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Konfirmasi Password Baru</label>
                        <input type="password" name="password_confirmation"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Nama Depan *</label>
                        <input type="text" name="nama_depan" value="{{ old('nama_depan', $karyawan->nama_depan) }}"
                            required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                        @error('nama_depan')
                            <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Nama Belakang *</label>
                        <input type="text" name="nama_belakang"
                            value="{{ old('nama_belakang', $karyawan->nama_belakang) }}" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                        @error('nama_belakang')
                            <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Nama Lengkap *</label>
                        <input type="text" name="nama_lengkap"
                            value="{{ old('nama_lengkap', $karyawan->nama_lengkap) }}" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                        @error('nama_lengkap')
                            <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-[#1B1B1B] mb-1">NIK *</label>
                        <input type="text" name="nik" value="{{ old('nik', $karyawan->nik) }}" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                        @error('nik')
                            <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-[#1B1B1B] mb-1">NPWP</label>
                        <input type="text" name="npwp" value="{{ old('npwp', $karyawan->npwp) }}"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Tempat Lahir *</label>
                        <input type="text" name="tempat_lahir"
                            value="{{ old('tempat_lahir', $karyawan->tempat_lahir) }}" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                        @error('tempat_lahir')
                            <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Tanggal Lahir *</label>
                        <input type="date" name="tanggal_lahir"
                            value="{{ old('tanggal_lahir', $karyawan->tanggal_lahir->format('Y-m-d')) }}" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                        @error('tanggal_lahir')
                            <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Jenis Kelamin *</label>
                        <select name="jenis_kelamin" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                            <option value="">Pilih</option>
                            <option value="Laki-laki"
                                {{ old('jenis_kelamin', $karyawan->jenis_kelamin) === 'Laki-laki' ? 'selected' : '' }}>
                                Laki-laki</option>
                            <option value="Perempuan"
                                {{ old('jenis_kelamin', $karyawan->jenis_kelamin) === 'Perempuan' ? 'selected' : '' }}>
                                Perempuan</option>
                        </select>
                        @error('jenis_kelamin')
                            <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Agama *</label>
                        <select name="agama" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                            <option value="">Pilih</option>
                            <option value="Islam" {{ old('agama', $karyawan->agama) === 'Islam' ? 'selected' : '' }}>Islam
                            </option>
                            <option value="Kristen" {{ old('agama', $karyawan->agama) === 'Kristen' ? 'selected' : '' }}>
                                Kristen</option>
                            <option value="Katolik" {{ old('agama', $karyawan->agama) === 'Katolik' ? 'selected' : '' }}>
                                Katolik</option>
                            <option value="Hindu" {{ old('agama', $karyawan->agama) === 'Hindu' ? 'selected' : '' }}>Hindu
                            </option>
                            <option value="Buddha" {{ old('agama', $karyawan->agama) === 'Buddha' ? 'selected' : '' }}>
                                Buddha</option>
                            <option value="Konghucu" {{ old('agama', $karyawan->agama) === 'Konghucu' ? 'selected' : '' }}>
                                Konghucu</option>
                        </select>
                        @error('agama')
                            <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Status Pernikahan *</label>
                        <select name="status_pernikahan" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                            <option value="">Pilih</option>
                            <option value="Belum Menikah"
                                {{ old('status_pernikahan', $karyawan->status_pernikahan) === 'Belum Menikah' ? 'selected' : '' }}>
                                Belum Menikah</option>
                            <option value="Menikah"
                                {{ old('status_pernikahan', $karyawan->status_pernikahan) === 'Menikah' ? 'selected' : '' }}>
                                Menikah</option>
                            <option value="Cerai"
                                {{ old('status_pernikahan', $karyawan->status_pernikahan) === 'Cerai' ? 'selected' : '' }}>
                                Cerai</option>
                        </select>
                        @error('status_pernikahan')
                            <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Tambahkan field-field baru di bagian Informasi Pribadi -->
                    <div>
                        <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Jumlah Anak</label>
                        <input type="number" name="jumlah_anak"
                            value="{{ old('jumlah_anak', $karyawan->jumlah_anak ?? 0) }}" min="0"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                        @error('jumlah_anak')
                            <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Golongan Darah</label>
                        <select name="golongan_darah"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                            <option value="">Pilih</option>
                            <option value="A"
                                {{ old('golongan_darah', $karyawan->golongan_darah) === 'A' ? 'selected' : '' }}>A</option>
                            <option value="B"
                                {{ old('golongan_darah', $karyawan->golongan_darah) === 'B' ? 'selected' : '' }}>B</option>
                            <option value="AB"
                                {{ old('golongan_darah', $karyawan->golongan_darah) === 'AB' ? 'selected' : '' }}>AB
                            </option>
                            <option value="O"
                                {{ old('golongan_darah', $karyawan->golongan_darah) === 'O' ? 'selected' : '' }}>O</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-[#1B1B1B] mb-1">No KK</label>
                        <input type="text" name="no_kk" value="{{ old('no_kk', $karyawan->no_kk) }}"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Gelar Pendidikan</label>
                        <input type="text" name="gelar_pendidikan"
                            value="{{ old('gelar_pendidikan', $karyawan->gelar_pendidikan) }}"
                            placeholder="Contoh: S.Kom, S.E, M.M"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Sedang Melanjutkan Pendidikan</label>
                        <input type="text" name="sedang_melanjutkan_pendidikan"
                            value="{{ old('sedang_melanjutkan_pendidikan', $karyawan->sedang_melanjutkan_pendidikan) }}"
                            placeholder="Contoh: S2 Manajemen"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Divisi</label>
                        <select name="divisi"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                            <option value="">Pilih Divisi</option>
                            <option value="MEDIA" {{ old('divisi', $karyawan->divisi) === 'MEDIA' ? 'selected' : '' }}>
                                MEDIA</option>
                            <option value="KPD" {{ old('divisi', $karyawan->divisi) === 'KPD' ? 'selected' : '' }}>KPD
                            </option>
                            <option value="IT" {{ old('divisi', $karyawan->divisi) === 'IT' ? 'selected' : '' }}>IT
                            </option>
                            <option value="HRD" {{ old('divisi', $karyawan->divisi) === 'HRD' ? 'selected' : '' }}>HRD
                            </option>
                            <option value="LPS" {{ old('divisi', $karyawan->divisi) === 'LPS' ? 'selected' : '' }}>LPS
                            </option>
                            <option value="PKA" {{ old('divisi', $karyawan->divisi) === 'PKA' ? 'selected' : '' }}>PKA
                            </option>
                            <option value="RG" {{ old('divisi', $karyawan->divisi) === 'RG' ? 'selected' : '' }}>RG
                            </option>
                            <option value="SAPRAS" {{ old('divisi', $karyawan->divisi) === 'SAPRAS' ? 'selected' : '' }}>
                                SAPRAS</option>
                            <option value="PENDIDIKAN"
                                {{ old('divisi', $karyawan->divisi) === 'PENDIDIKAN' ? 'selected' : '' }}>PENDIDIKAN
                            </option>
                        </select>
                        @error('divisi')
                            <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Informasi Profesional -->
                    <div class="md:col-span-2">
                        <h3 class="text-lg font-semibold text-[#161758] border-b border-gray-200 pb-2 mb-4 mt-4">Informasi
                            Profesional</h3>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Jabatan *</label>
                        <input type="text" name="jabatan" value="{{ old('jabatan', $karyawan->jabatan) }}" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                        @error('jabatan')
                            <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Role</label>
                        <select name="role"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                            <option value="karyawan" {{ old('role', $karyawan->role) === 'karyawan' ? 'selected' : '' }}>
                                Karyawan</option>
                            <option value="hr" {{ old('role', $karyawan->role) === 'hr' ? 'selected' : '' }}>HR
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Tanggal Bergabung *</label>
                        <input type="date" name="tanggal_bergabung"
                            value="{{ old('tanggal_bergabung', $karyawan->tanggal_bergabung->format('Y-m-d')) }}" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                        @error('tanggal_bergabung')
                            <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Tanggal Berakhir</label>
                        <input type="date" name="end_date"
                            value="{{ old('end_date', $karyawan->end_date ? $karyawan->end_date->format('Y-m-d') : '') }}"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                        @error('end_date')
                            <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Status *</label>
                        <select name="status" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                            <option value="Full-time"
                                {{ old('status', $karyawan->status) === 'Full-time' ? 'selected' : '' }}>Full-time</option>
                            <option value="Contract"
                                {{ old('status', $karyawan->status) === 'Contract' ? 'selected' : '' }}>Contract</option>
                            <option value="Internship"
                                {{ old('status', $karyawan->status) === 'Internship' ? 'selected' : '' }}>Internship
                            </option>
                        </select>
                        @error('status')
                            <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Total Hari Kerja</label>
                        <input type="number" name="total_hari_kerja"
                            value="{{ old('total_hari_kerja', $karyawan->total_hari_kerja ?? 0) }}" min="0"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Alasan Resign</label>
                        <textarea name="reason_resigned" rows="2"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">{{ old('reason_resigned', $karyawan->reason_resigned) }}</textarea>
                    </div>

                    <!-- Pendidikan -->
                    <div class="md:col-span-2">
                        <h3 class="text-lg font-semibold text-[#161758] border-b border-gray-200 pb-2 mb-4 mt-4">Pendidikan
                        </h3>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Pendidikan Terakhir</label>
                        <select name="pendidikan_terakhir_new"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                            <option value="">Pilih</option>
                            <option value="SMP"
                                {{ old('pendidikan_terakhir_new', $karyawan->pendidikan_terakhir_new) === 'SMP' ? 'selected' : '' }}>
                                SMP</option>
                            <option value="SMA/MA"
                                {{ old('pendidikan_terakhir_new', $karyawan->pendidikan_terakhir_new) === 'SMA/MA' ? 'selected' : '' }}>
                                SMA/MA</option>
                            <option value="SMK"
                                {{ old('pendidikan_terakhir_new', $karyawan->pendidikan_terakhir_new) === 'SMK' ? 'selected' : '' }}>
                                SMK</option>
                            <option value="D1"
                                {{ old('pendidikan_terakhir_new', $karyawan->pendidikan_terakhir_new) === 'D1' ? 'selected' : '' }}>
                                D1</option>
                            <option value="D2"
                                {{ old('pendidikan_terakhir_new', $karyawan->pendidikan_terakhir_new) === 'D2' ? 'selected' : '' }}>
                                D2</option>
                            <option value="D3"
                                {{ old('pendidikan_terakhir_new', $karyawan->pendidikan_terakhir_new) === 'D3' ? 'selected' : '' }}>
                                D3</option>
                            <option value="D4"
                                {{ old('pendidikan_terakhir_new', $karyawan->pendidikan_terakhir_new) === 'D4' ? 'selected' : '' }}>
                                D4</option>
                            <option value="S1"
                                {{ old('pendidikan_terakhir_new', $karyawan->pendidikan_terakhir_new) === 'S1' ? 'selected' : '' }}>
                                S1</option>
                            <option value="S2"
                                {{ old('pendidikan_terakhir_new', $karyawan->pendidikan_terakhir_new) === 'S2' ? 'selected' : '' }}>
                                S2</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Universitas</label>
                        <input type="text" name="universitas"
                            value="{{ old('universitas', $karyawan->universitas) }}"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Jurusan</label>
                        <input type="text" name="jurusan" value="{{ old('jurusan', $karyawan->jurusan) }}"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Tahun Lulus</label>
                        <input type="number" name="tahun_lulus"
                            value="{{ old('tahun_lulus', $karyawan->tahun_lulus) }}" min="1900"
                            max="{{ date('Y') }}"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                    </div>

                    <!-- Kontak & Alamat -->
                    <div class="md:col-span-2">
                        <h3 class="text-lg font-semibold text-[#161758] border-b border-gray-200 pb-2 mb-4 mt-4">Kontak &
                            Alamat</h3>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Nomor Telepon</label>
                        <input type="text" name="nomor_telepon"
                            value="{{ old('nomor_telepon', $karyawan->nomor_telepon) }}"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Foto Profil</label>
                        <input type="file" name="foto_profil" accept="image/*"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                        @if ($karyawan->foto_profil)
                            <div class="mt-2">
                                <img src="{{ Storage::url($karyawan->foto_profil) }}" alt="Foto Profil"
                                    class="w-20 h-20 rounded-full object-cover">
                            </div>
                        @endif
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Alamat</label>
                        <textarea name="alamat" rows="3"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">{{ old('alamat', $karyawan->alamat) }}</textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Nama Kontak Darurat</label>
                        <input type="text" name="nama_kontak_darurat"
                            value="{{ old('nama_kontak_darurat', $karyawan->nama_kontak_darurat) }}"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Telepon Kontak Darurat</label>
                        <input type="text" name="telepon_kontak_darurat"
                            value="{{ old('telepon_kontak_darurat', $karyawan->telepon_kontak_darurat) }}"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                    </div>
                </div>

                <div class="mt-6 flex space-x-4">
                    <button type="submit"
                        class="bg-[#27438D] text-white px-6 py-2 rounded-lg hover:bg-[#161758] transition-colors duration-200">
                        Update
                    </button>
                    <a href="{{ route('hr.karyawan.index') }}"
                        class="bg-gray-500 text-white px-6 py-2 rounded-lg hover:bg-gray-600 transition-colors duration-200">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection
