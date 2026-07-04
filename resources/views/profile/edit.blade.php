@extends('layouts.app')

@section('content')
<div class="flex">
    @include('layouts.sidebar')
    <div class="ml-64 flex-1 p-6">
        <div class="mb-6">
            <h1 class="text-2xl font-bold font-['Montserrat'] text-[#161758]">Edit Profile</h1>
            <p class="text-[#27438D]">Update informasi profil anda</p>
        </div>

        @if(session('success'))
            <div class="bg-[#2E7D3E] text-white p-4 rounded-lg mb-4">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="bg-[#ec1d1d] text-white p-4 rounded-lg mb-4">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" class="bg-white rounded-lg shadow-md p-6 mb-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Informasi Pribadi -->
                <div class="md:col-span-2">
                    <h3 class="text-lg font-semibold text-[#161758] border-b border-gray-200 pb-2 mb-4">Informasi Pribadi</h3>
                </div>

                <div>
                    <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Kode Pegawai <span class="text-[#ec1d1d]">*</span></label>
                    <input type="text" name="kode_pegawai" value="{{ old('kode_pegawai', $user->kode_pegawai) }}" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                    @error('kode_pegawai') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Email <span class="text-[#ec1d1d]">*</span></label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                    @error('email') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Nama Depan <span class="text-[#ec1d1d]">*</span></label>
                    <input type="text" name="nama_depan" value="{{ old('nama_depan', $user->nama_depan) }}" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                    @error('nama_depan') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Nama Belakang <span class="text-[#ec1d1d]">*</span></label>
                    <input type="text" name="nama_belakang" value="{{ old('nama_belakang', $user->nama_belakang) }}" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                    @error('nama_belakang') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Nama Lengkap <span class="text-[#ec1d1d]">*</span></label>
                    <input type="text" name="nama_lengkap" value="{{ old('nama_lengkap', $user->nama_lengkap) }}" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                    @error('nama_lengkap') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-[#1B1B1B] mb-1">NIK</label>
                    <input type="text" name="nik" value="{{ old('nik', $user->nik) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                    @error('nik') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-[#1B1B1B] mb-1">NPWP</label>
                    <input type="text" name="npwp" value="{{ old('npwp', $user->npwp) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                </div>

                <div>
                    <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Tempat Lahir</label>
                    <input type="text" name="tempat_lahir" value="{{ old('tempat_lahir', $user->tempat_lahir) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                    @error('tempat_lahir') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Tanggal Lahir</label>
                    <input type="date" name="tanggal_lahir" value="{{ old('tanggal_lahir', $user->tanggal_lahir ? $user->tanggal_lahir->format('Y-m-d') : '') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                    @error('tanggal_lahir') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Jenis Kelamin</label>
                    <select name="jenis_kelamin"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                        <option value="">Pilih</option>
                        <option value="Laki-laki" {{ old('jenis_kelamin', $user->jenis_kelamin) === 'Laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                        <option value="Perempuan" {{ old('jenis_kelamin', $user->jenis_kelamin) === 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
                    </select>
                    @error('jenis_kelamin') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Agama</label>
                    <select name="agama"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                        <option value="">Pilih</option>
                        <option value="Islam" {{ old('agama', $user->agama) === 'Islam' ? 'selected' : '' }}>Islam</option>
                        <option value="Kristen" {{ old('agama', $user->agama) === 'Kristen' ? 'selected' : '' }}>Kristen</option>
                        <option value="Katolik" {{ old('agama', $user->agama) === 'Katolik' ? 'selected' : '' }}>Katolik</option>
                        <option value="Hindu" {{ old('agama', $user->agama) === 'Hindu' ? 'selected' : '' }}>Hindu</option>
                        <option value="Buddha" {{ old('agama', $user->agama) === 'Buddha' ? 'selected' : '' }}>Buddha</option>
                        <option value="Konghucu" {{ old('agama', $user->agama) === 'Konghucu' ? 'selected' : '' }}>Konghucu</option>
                    </select>
                    @error('agama') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Status Pernikahan</label>
                    <select name="status_pernikahan"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                        <option value="">Pilih</option>
                        <option value="Belum Menikah" {{ old('status_pernikahan', $user->status_pernikahan) === 'Belum Menikah' ? 'selected' : '' }}>Belum Menikah</option>
                        <option value="Menikah" {{ old('status_pernikahan', $user->status_pernikahan) === 'Menikah' ? 'selected' : '' }}>Menikah</option>
                    </select>
                    @error('status_pernikahan') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Golongan Darah</label>
                    <select name="golongan_darah"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                        <option value="">Pilih</option>
                        <option value="A" {{ old('golongan_darah', $user->golongan_darah) === 'A' ? 'selected' : '' }}>A</option>
                        <option value="B" {{ old('golongan_darah', $user->golongan_darah) === 'B' ? 'selected' : '' }}>B</option>
                        <option value="AB" {{ old('golongan_darah', $user->golongan_darah) === 'AB' ? 'selected' : '' }}>AB</option>
                        <option value="O" {{ old('golongan_darah', $user->golongan_darah) === 'O' ? 'selected' : '' }}>O</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-[#1B1B1B] mb-1">No KK</label>
                    <input type="text" name="no_kk" value="{{ old('no_kk', $user->no_kk) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                </div>

                <div>
                    <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Nama Ibu Kandung</label>
                    <input type="text" name="nama_ibu_kandung" value="{{ old('nama_ibu_kandung', $user->nama_ibu_kandung) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                </div>

                <div>
                    <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Sedang Melanjutkan Pendidikan</label>
                    <input type="text" name="sedang_melanjutkan_pendidikan" value="{{ old('sedang_melanjutkan_pendidikan', $user->sedang_melanjutkan_pendidikan) }}" placeholder="Contoh: S2 Manajemen"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                </div>

                <div>
                    <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Jumlah Anak</label>
                    <input type="number" name="jumlah_anak" value="{{ old('jumlah_anak', $user->jumlah_anak ?? 0) }}" min="0"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                </div>

                <!-- Informasi Profesional -->
                <div class="md:col-span-2">
                    <h3 class="text-lg font-semibold text-[#161758] border-b border-gray-200 pb-2 mb-4 mt-4">Informasi Profesional</h3>
                </div>

                @if($isHr)
                    <!-- HR bisa edit semua -->
                    <div>
                        <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Jabatan <span class="text-[#ec1d1d]">*</span></label>
                        <input type="text" name="jabatan" value="{{ old('jabatan', $user->jabatan) }}" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                        @error('jabatan') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Divisi</label>
                        <select name="divisi"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                            <option value="">Pilih Divisi</option>
                            <option value="HRD" {{ old('divisi', $user->divisi) === 'HRD' ? 'selected' : '' }}>HRD</option>
                            <option value="IT" {{ old('divisi', $user->divisi) === 'IT' ? 'selected' : '' }}>IT</option>
                            <option value="KPD" {{ old('divisi', $user->divisi) === 'KPD' ? 'selected' : '' }}>KPD</option>
                            <option value="LPS" {{ old('divisi', $user->divisi) === 'LPS' ? 'selected' : '' }}>LPS</option>
                            <option value="MEDIA" {{ old('divisi', $user->divisi) === 'MEDIA' ? 'selected' : '' }}>MEDIA</option>
                            <option value="PENDIDIKAN" {{ old('divisi', $user->divisi) === 'PENDIDIKAN' ? 'selected' : '' }}>PENDIDIKAN</option>
                            <option value="PKA" {{ old('divisi', $user->divisi) === 'PKA' ? 'selected' : '' }}>PKA</option>
                            <option value="RG" {{ old('divisi', $user->divisi) === 'RG' ? 'selected' : '' }}>RG</option>
                            <option value="SAPRAS" {{ old('divisi', $user->divisi) === 'SAPRAS' ? 'selected' : '' }}>SAPRAS</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Status <span class="text-[#ec1d1d]">*</span></label>
                        <select name="status" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                            <option value="Full-time" {{ old('status', $user->status) === 'Full-time' ? 'selected' : '' }}>Full-time</option>
                            <option value="Contract" {{ old('status', $user->status) === 'Contract' ? 'selected' : '' }}>Contract</option>
                            <option value="Internship" {{ old('status', $user->status) === 'Internship' ? 'selected' : '' }}>Internship</option>
                        </select>
                        @error('status') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Tanggal Bergabung <span class="text-[#ec1d1d]">*</span></label>
                        <input type="date" name="tanggal_bergabung" value="{{ old('tanggal_bergabung', $user->tanggal_bergabung ? $user->tanggal_bergabung->format('Y-m-d') : '') }}" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                        @error('tanggal_bergabung') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Posisi</label>
                        <input type="text" value="{{ $user->posisi === 'hr' ? 'HR' : 'Karyawan' }}" disabled
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-100">
                        <input type="hidden" name="posisi" value="{{ $user->posisi }}">
                        <p class="text-xs text-[#27438D] mt-1">* Posisi ditentukan otomatis berdasarkan divisi</p>
                    </div>
                @else
                    <!-- Karyawan tidak bisa edit jabatan, status, tanggal bergabung, divisi -->
                    <div>
                        <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Jabatan</label>
                        <input type="text" value="{{ $user->jabatan }}" disabled
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-100">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Divisi</label>
                        <input type="text" value="{{ $user->divisi ?? '-' }}" disabled
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-100">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Status</label>
                        <input type="text" value="{{ $user->status }}" disabled
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-100">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Tanggal Bergabung</label>
                        <input type="text" value="{{ $user->tanggal_bergabung ? $user->tanggal_bergabung->format('d-m-Y') : '-' }}" disabled
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-100">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Posisi</label>
                        <input type="text" value="{{ $user->posisi === 'hr' ? 'HR' : 'Karyawan' }}" disabled
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-100">
                    </div>
                @endif

                <!-- Pendidikan -->
                <div class="md:col-span-2">
                    <h3 class="text-lg font-semibold text-[#161758] border-b border-gray-200 pb-2 mb-4 mt-4">Pendidikan</h3>
                </div>

                <div>
                    <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Pendidikan Terakhir</label>
                    <select name="pendidikan_terakhir_new"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                        <option value="">Pilih</option>
                        <option value="SMP" {{ old('pendidikan_terakhir_new', $user->pendidikan_terakhir_new) === 'SMP' ? 'selected' : '' }}>SMP</option>
                        <option value="SMA/MA" {{ old('pendidikan_terakhir_new', $user->pendidikan_terakhir_new) === 'SMA/MA' ? 'selected' : '' }}>SMA/MA</option>
                        <option value="SMK" {{ old('pendidikan_terakhir_new', $user->pendidikan_terakhir_new) === 'SMK' ? 'selected' : '' }}>SMK</option>
                        <option value="D1" {{ old('pendidikan_terakhir_new', $user->pendidikan_terakhir_new) === 'D1' ? 'selected' : '' }}>D1</option>
                        <option value="D2" {{ old('pendidikan_terakhir_new', $user->pendidikan_terakhir_new) === 'D2' ? 'selected' : '' }}>D2</option>
                        <option value="D3" {{ old('pendidikan_terakhir_new', $user->pendidikan_terakhir_new) === 'D3' ? 'selected' : '' }}>D3</option>
                        <option value="D4" {{ old('pendidikan_terakhir_new', $user->pendidikan_terakhir_new) === 'D4' ? 'selected' : '' }}>D4</option>
                        <option value="S1" {{ old('pendidikan_terakhir_new', $user->pendidikan_terakhir_new) === 'S1' ? 'selected' : '' }}>S1</option>
                        <option value="S2" {{ old('pendidikan_terakhir_new', $user->pendidikan_terakhir_new) === 'S2' ? 'selected' : '' }}>S2</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Perguruan Tinggi</label>
                    <input type="text" name="perguruan_tinggi" value="{{ old('perguruan_tinggi', $user->perguruan_tinggi) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                </div>

                <div>
                    <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Jurusan</label>
                    <input type="text" name="jurusan" value="{{ old('jurusan', $user->jurusan) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                </div>

                <div>
                    <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Tahun Lulus</label>
                    <input type="number" name="tahun_lulus" value="{{ old('tahun_lulus', $user->tahun_lulus) }}" min="1900" max="{{ date('Y') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                </div>

                <!-- Kontak & Alamat -->
                <div class="md:col-span-2">
                    <h3 class="text-lg font-semibold text-[#161758] border-b border-gray-200 pb-2 mb-4 mt-4">Kontak & Alamat</h3>
                </div>

                <div>
                    <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Nomor Telepon</label>
                    <input type="text" name="nomor_telepon" value="{{ old('nomor_telepon', $user->nomor_telepon) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                </div>

                <div>
                    <label class="block text-sm font-medium text-[#1B1B1B] mb-1">No WA</label>
                    <input type="text" name="no_wa" value="{{ old('no_wa', $user->no_wa) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                </div>

                <div>
                    <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Foto Profil</label>
                    <input type="file" name="foto_profil" accept="image/*"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                    @if($user->foto_profil)
                        <div class="mt-2">
                            <img src="{{ Storage::url($user->foto_profil) }}" alt="Foto Profil" class="w-20 h-20 rounded-full object-cover">
                        </div>
                    @endif
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Alamat</label>
                    <textarea name="alamat" rows="3"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">{{ old('alamat', $user->alamat) }}</textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Nama Kontak Darurat</label>
                    <input type="text" name="nama_kontak_darurat" value="{{ old('nama_kontak_darurat', $user->nama_kontak_darurat) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                </div>

                <div>
                    <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Telepon Kontak Darurat</label>
                    <input type="text" name="telepon_kontak_darurat" value="{{ old('telepon_kontak_darurat', $user->telepon_kontak_darurat) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                </div>
            </div>

            <div class="mt-6">
                <button type="submit"
                        class="bg-[#27438D] text-white px-6 py-2 rounded-lg hover:bg-[#161758] transition-colors duration-200">
                    Update Profile
                </button>
            </div>
        </form>

        <!-- Ubah Password -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-lg font-semibold text-[#161758] mb-4">Ubah Password</h2>
            <form action="{{ route('profile.update-password') }}" method="POST">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Password Saat Ini <span class="text-[#ec1d1d]">*</span></label>
                        <input type="password" name="current_password" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                        @error('current_password') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Password Baru <span class="text-[#ec1d1d]">*</span></label>
                        <input type="password" name="password" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                        @error('password') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Konfirmasi Password Baru <span class="text-[#ec1d1d]">*</span></label>
                        <input type="password" name="password_confirmation" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                    </div>
                </div>

                <div class="mt-6">
                    <button type="submit"
                            class="bg-[#FCC626] text-[#1B1B1B] px-6 py-2 rounded-lg hover:bg-[#e6b222] transition-colors duration-200">
                        Ubah Password
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
