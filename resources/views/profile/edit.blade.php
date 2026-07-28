{{-- resources/views/profile/edit.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="flex min-h-screen">
    @include('layouts.sidebar')
    <div class="flex-1 transition-all duration-300 md:ml-64 p-3 sm:p-6">
        <div class="flex flex-wrap justify-between items-start mb-6 gap-3">
            <div>
                <h1 class="text-2xl font-bold font-['Montserrat'] text-[#161758]">Edit Profile</h1>
                <p class="text-[#27438D]">Perbarui informasi profil Anda</p>
            </div>
            <a href="{{ route('profile.show') }}"
                class="w-full sm:w-auto bg-gray-500 text-white px-6 py-2 rounded-lg hover:bg-gray-600 transition-colors duration-200 text-center">
                <i class="fas fa-arrow-left mr-2"></i> Kembali ke Detail
            </a>
        </div>

        @if (session('success'))
            <div class="bg-[#2E7D3E] text-white p-4 rounded-lg mb-4">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
            <div class="bg-[#ec1d1d] text-white p-4 rounded-lg mb-4">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- ========== FORM EDIT PROFIL ========== -->
        <div class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden">
            <div class="p-6">
                <h2 class="text-xl font-bold text-[#161758] mb-4">Form Edit Profile</h2>
                <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- 1. Informasi Pribadi -->
                        <div class="md:col-span-2">
                            <h3 class="text-base sm:text-lg font-semibold text-[#161758] border-b-2 border-[#00a2e9] pb-2 mb-4 flex items-center">
                                <span class="bg-[#00a2e9] text-white rounded-full w-6 h-6 flex items-center justify-center text-xs mr-2">1</span>
                                Informasi Pribadi
                            </h3>
                        </div>

                        <div>
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-[#1B1B1B] mb-1">ID Karyawan <span class="text-[#ec1d1d]">*</span></label>
                                <input type="text" name="kode_pegawai" value="{{ old('kode_pegawai', $user->kode_pegawai) }}" readonly
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-100 focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                                @error('kode_pegawai') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                            </div>

                            <div class="mb-4">
                                <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Tempat Lahir</label>
                                <input type="text" name="tempat_lahir" value="{{ old('tempat_lahir', $user->tempat_lahir) }}"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                                @error('tempat_lahir') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                            </div>

                            <div class="mb-4">
                                <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Jenis Kelamin</label>
                                <select name="jenis_kelamin" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                                    <option value="">Pilih</option>
                                    <option value="Laki-laki" {{ old('jenis_kelamin', $user->jenis_kelamin) === 'Laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                                    <option value="Perempuan" {{ old('jenis_kelamin', $user->jenis_kelamin) === 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
                                </select>
                                @error('jenis_kelamin') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                            </div>

                            <div class="mb-4">
                                <label class="block text-sm font-medium text-[#1B1B1B] mb-1">NIK</label>
                                <input type="text" name="nik" value="{{ old('nik', $user->nik) }}"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                                @error('nik') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                            </div>

                            <div class="mb-4">
                                <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Agama</label>
                                <input type="text" name="agama" value="{{ old('agama', $user->agama) }}"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                                @error('agama') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                            </div>

                            <div class="mb-4">
                                <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Jumlah Anak</label>
                                <input type="number" name="jumlah_anak" value="{{ old('jumlah_anak', $user->jumlah_anak ?? 0) }}" min="0"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                                @error('jumlah_anak') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                            </div>

                            <div class="mb-4">
                                <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Golongan Darah</label>
                                <select name="golongan_darah" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                                    <option value="">Pilih</option>
                                    <option value="A" {{ old('golongan_darah', $user->golongan_darah) === 'A' ? 'selected' : '' }}>A</option>
                                    <option value="B" {{ old('golongan_darah', $user->golongan_darah) === 'B' ? 'selected' : '' }}>B</option>
                                    <option value="AB" {{ old('golongan_darah', $user->golongan_darah) === 'AB' ? 'selected' : '' }}>AB</option>
                                    <option value="O" {{ old('golongan_darah', $user->golongan_darah) === 'O' ? 'selected' : '' }}>O</option>
                                </select>
                                @error('golongan_darah') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div>
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Nama Lengkap <span class="text-[#ec1d1d]">*</span></label>
                                <input type="text" name="nama_lengkap" value="{{ old('nama_lengkap', $user->nama_lengkap) }}" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                                @error('nama_lengkap') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                            </div>

                            <div class="mb-4">
                                <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Tanggal Lahir</label>
                                <input type="date" name="tanggal_lahir" value="{{ old('tanggal_lahir', $user->tanggal_lahir ? $user->tanggal_lahir->format('Y-m-d') : '') }}"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                                @error('tanggal_lahir') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                            </div>

                            <div class="mb-4">
                                <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Nama Ibu Kandung</label>
                                <input type="text" name="nama_ibu_kandung" value="{{ old('nama_ibu_kandung', $user->nama_ibu_kandung) }}"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                                @error('nama_ibu_kandung') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                            </div>

                            <div class="mb-4">
                                <label class="block text-sm font-medium text-[#1B1B1B] mb-1">No KK</label>
                                <input type="text" name="no_kk" value="{{ old('no_kk', $user->no_kk) }}"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                                @error('no_kk') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                            </div>

                            <div class="mb-4">
                                <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Status Pernikahan</label>
                                <select name="status_pernikahan" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                                    <option value="">Pilih</option>
                                    <option value="Belum Menikah" {{ old('status_pernikahan', $user->status_pernikahan) === 'Belum Menikah' ? 'selected' : '' }}>Belum Menikah</option>
                                    <option value="Menikah" {{ old('status_pernikahan', $user->status_pernikahan) === 'Menikah' ? 'selected' : '' }}>Menikah</option>
                                    <option value="Cerai" {{ old('status_pernikahan', $user->status_pernikahan) === 'Cerai' ? 'selected' : '' }}>Cerai</option>
                                </select>
                                @error('status_pernikahan') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                            </div>

                            <div class="mb-4">
                                <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Email <span class="text-[#ec1d1d]">*</span></label>
                                <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                                @error('email') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                            </div>

                            <div class="mb-4">
                                <label class="block text-sm font-medium text-[#1B1B1B] mb-1">NPWP</label>
                                <input type="text" name="npwp" value="{{ old('npwp', $user->npwp) }}"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                                @error('npwp') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <!-- 2. Informasi Profesional -->
                        <div class="md:col-span-2">
                            <h3 class="text-base sm:text-lg font-semibold text-[#161758] border-b-2 border-[#27438D] pb-2 mb-4 mt-4 flex items-center">
                                <span class="bg-[#27438D] text-white rounded-full w-6 h-6 flex items-center justify-center text-xs mr-2">2</span>
                                Informasi Profesional
                            </h3>
                        </div>

                        <div>
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Jabatan <span class="text-[#ec1d1d]">*</span></label>
                                <input type="text" name="jabatan" value="{{ old('jabatan', $user->jabatan) }}" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                                @error('jabatan') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                            </div>

                            <div class="mb-4">
                                <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Tanggal Bergabung <span class="text-[#ec1d1d]">*</span></label>
                                <input type="date" name="tanggal_bergabung" value="{{ old('tanggal_bergabung', $user->tanggal_bergabung ? $user->tanggal_bergabung->format('Y-m-d') : '') }}" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                                @error('tanggal_bergabung') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                            </div>

                            <div class="mb-4">
                                <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Tanggal Pengangkatan Karyawan Tetap</label>
                                <input type="date" name="tanggal_pengangkatan_tetap" value="{{ old('tanggal_pengangkatan_tetap', $user->tanggal_pengangkatan_tetap ? $user->tanggal_pengangkatan_tetap->format('Y-m-d') : '') }}"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                                @error('tanggal_pengangkatan_tetap') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div>
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Divisi <span class="text-[#ec1d1d]">*</span></label>
                                <input type="text" name="divisi" id="divisi_input" value="{{ old('divisi', $user->divisi) }}" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                                <p class="text-xs text-[#27438D] mt-1">* Jika diisi "HRD", posisi akan otomatis menjadi HR</p>
                                @error('divisi') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                            </div>

                            <div class="mb-4">
                                <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Status <span class="text-[#ec1d1d]">*</span></label>
                                <select name="status" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                                    <option value="Karyawan Tetap" {{ old('status', $user->status) === 'Karyawan Tetap' ? 'selected' : '' }}>Karyawan Tetap</option>
                                    <option value="Contract" {{ old('status', $user->status) === 'Contract' ? 'selected' : '' }}>Kontrak</option>
                                    <option value="Internship" {{ old('status', $user->status) === 'Internship' ? 'selected' : '' }}>Magang</option>
                                </select>
                                @error('status') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <!-- 3. Pendidikan -->
                        <div class="md:col-span-2">
                            <h3 class="text-base sm:text-lg font-semibold text-[#161758] border-b-2 border-[#FCC626] pb-2 mb-4 mt-4 flex items-center">
                                <span class="bg-[#FCC626] text-[#1B1B1B] rounded-full w-6 h-6 flex items-center justify-center text-xs mr-2">3</span>
                                Pendidikan
                            </h3>
                        </div>

                        <div>
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Pendidikan Terakhir</label>
                                <input type="text" name="pendidikan_terakhir" value="{{ old('pendidikan_terakhir', $user->pendidikan_terakhir) }}"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                                @error('pendidikan_terakhir') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                            </div>

                            <div class="mb-4">
                                <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Perguruan Tinggi</label>
                                <input type="text" name="perguruan_tinggi" value="{{ old('perguruan_tinggi', $user->perguruan_tinggi) }}"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                                @error('perguruan_tinggi') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                            </div>

                            <div class="mb-4">
                                <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Jurusan / Program Studi</label>
                                <input type="text" name="jurusan" value="{{ old('jurusan', $user->jurusan) }}"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                                @error('jurusan') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                            </div>

                            <div class="mb-4">
                                <label class="block text-sm font-medium text-[#1B1B1B] mb-1">IPK Terakhir</label>
                                <input type="number" name="ipk_terakhir" value="{{ old('ipk_terakhir', $user->ipk_terakhir) }}" step="0.01" min="0" max="4"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                                @error('ipk_terakhir') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                            </div>

                            <div class="mb-4">
                                <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Tahun Lulus</label>
                                <input type="number" name="tahun_lulus" value="{{ old('tahun_lulus', $user->tahun_lulus) }}" min="1900" max="{{ date('Y') }}"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                                @error('tahun_lulus') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div>
                            <!-- Radio Button Sedang Melanjutkan Pendidikan -->
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Sedang Melanjutkan Pendidikan?</label>
                                <div class="flex flex-wrap items-center gap-4">
                                    <label class="inline-flex items-center">
                                        <input type="radio" name="is_continuing_education" value="1"
                                            {{ old('is_continuing_education', $user->is_continuing_education) == 1 ? 'checked' : '' }}
                                            class="w-4 h-4 text-[#27438D] border-gray-300 focus:ring-[#27438D]">
                                        <span class="ml-2 text-sm">Iya</span>
                                    </label>
                                    <label class="inline-flex items-center">
                                        <input type="radio" name="is_continuing_education" value="0"
                                            {{ old('is_continuing_education', $user->is_continuing_education) == 0 ? 'checked' : '' }}
                                            class="w-4 h-4 text-[#27438D] border-gray-300 focus:ring-[#27438D]">
                                        <span class="ml-2 text-sm">Tidak</span>
                                    </label>
                                </div>
                                @error('is_continuing_education') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                            </div>

                            <!-- Field pendidikan lanjutan (toggle) -->
                            <div id="continuing_fields" style="{{ old('is_continuing_education', $user->is_continuing_education) == 1 ? '' : 'display: none;' }}">
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Program Pendidikan</label>
                                    <select name="continuing_program" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                                        <option value="">Pilih</option>
                                        <option value="D3" {{ old('continuing_program', $user->continuing_program) === 'D3' ? 'selected' : '' }}>D3</option>
                                        <option value="D4/S1" {{ old('continuing_program', $user->continuing_program) === 'D4/S1' ? 'selected' : '' }}>D4/S1</option>
                                        <option value="S2" {{ old('continuing_program', $user->continuing_program) === 'S2' ? 'selected' : '' }}>S2</option>
                                        <option value="S3" {{ old('continuing_program', $user->continuing_program) === 'S3' ? 'selected' : '' }}>S3</option>
                                    </select>
                                    @error('continuing_program') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                                </div>

                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Perguruan Tinggi</label>
                                    <input type="text" name="continuing_perguruan_tinggi" value="{{ old('continuing_perguruan_tinggi', $user->continuing_perguruan_tinggi) }}"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                                    @error('continuing_perguruan_tinggi') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                                </div>

                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Jurusan / Program Studi</label>
                                    <input type="text" name="continuing_jurusan" value="{{ old('continuing_jurusan', $user->continuing_jurusan) }}"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                                    @error('continuing_jurusan') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                                </div>
                            </div>
                        </div>

                        <!-- 4. Kontak & Alamat -->
                        <div class="md:col-span-2">
                            <h3 class="text-base sm:text-lg font-semibold text-[#161758] border-b-2 border-[#2E7D3E] pb-2 mb-4 mt-4 flex items-center">
                                <span class="bg-[#2E7D3E] text-white rounded-full w-6 h-6 flex items-center justify-center text-xs mr-2">4</span>
                                Kontak & Alamat
                            </h3>
                        </div>

                        <div>
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Nomor Telepon</label>
                                <input type="text" name="nomor_telepon" value="{{ old('nomor_telepon', $user->nomor_telepon) }}"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                                @error('nomor_telepon') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                            </div>

                            <div class="mb-4">
                                <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Telepon Kontak Darurat</label>
                                <input type="text" name="telepon_kontak_darurat" value="{{ old('telepon_kontak_darurat', $user->telepon_kontak_darurat) }}"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                                @error('telepon_kontak_darurat') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div>
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-[#1B1B1B] mb-1">No WA</label>
                                <input type="text" name="no_wa" value="{{ old('no_wa', $user->no_wa) }}"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                                @error('no_wa') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                            </div>

                            <div class="mb-4">
                                <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Nama Kontak Darurat</label>
                                <input type="text" name="nama_kontak_darurat" value="{{ old('nama_kontak_darurat', $user->nama_kontak_darurat) }}"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                                @error('nama_kontak_darurat') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="md:col-span-2">
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Alamat (KTP)</label>
                                <textarea name="alamat" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">{{ old('alamat', $user->alamat) }}</textarea>
                                @error('alamat') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="md:col-span-2">
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Alamat Domisili</label>
                                <textarea name="alamat_domisili" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">{{ old('alamat_domisili', $user->alamat_domisili) }}</textarea>
                                @error('alamat_domisili') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <!-- 5. Informasi Tambahan -->
                        <div class="md:col-span-2">
                            <h3 class="text-base sm:text-lg font-semibold text-[#161758] border-b-2 border-[#ec1d1d] pb-2 mb-4 mt-4 flex items-center">
                                <span class="bg-[#ec1d1d] text-white rounded-full w-6 h-6 flex items-center justify-center text-xs mr-2">5</span>
                                Informasi Tambahan
                            </h3>
                        </div>

                        <div>
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Nomor Rekening</label>
                                <input type="text" name="nomor_rekening" value="{{ old('nomor_rekening', $user->nomor_rekening) }}"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                                @error('nomor_rekening') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                            </div>

                            <div class="mb-4">
                                <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Foto Profil</label>
                                <input type="file" name="foto_profil" accept="image/*" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                                @if ($user->foto_profil)
                                    <div class="mt-2"><img src="{{ Storage::url($user->foto_profil) }}" alt="Foto Profil" class="w-20 h-20 rounded-full object-cover border"></div>
                                @endif
                                @error('foto_profil') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div>
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Nama Bank</label>
                                <input type="text" value="BSI" disabled class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-100">
                                <input type="hidden" name="nama_bank" value="BSI">
                                <p class="text-xs text-[#27438D] mt-1">* Nama bank default BSI</p>
                            </div>
                        </div>
                    </div>

                    <!-- TOMBOL AKSI: Update, Batal, dan Ubah Password -->
                    <div class="mt-6 flex flex-wrap gap-4">
                        <button type="submit" class="w-full sm:w-auto bg-[#27438D] text-white px-6 py-2 rounded-lg hover:bg-[#161758] transition-colors duration-200">Update Profile</button>
                        <a href="{{ route('profile.show') }}" class="w-full sm:w-auto bg-gray-500 text-white px-6 py-2 rounded-lg hover:bg-gray-600 transition-colors duration-200 text-center">Batal</a>
                        <a href="{{ route('profile.change-password') }}" class="w-full sm:w-auto bg-[#FCC626] text-[#1B1B1B] px-6 py-2 rounded-lg hover:bg-[#e6b222] transition-colors duration-200 text-center">
                            <i class="fas fa-key mr-2"></i> Ubah Password
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Toggle field pendidikan lanjutan berdasarkan pilihan radio
        const radios = document.querySelectorAll('input[name="is_continuing_education"]');
        const continuingFields = document.getElementById('continuing_fields');

        function toggleContinuingFields() {
            let checked = false;
            radios.forEach(radio => {
                if (radio.checked && radio.value === '1') {
                    checked = true;
                }
            });
            continuingFields.style.display = checked ? 'block' : 'none';
        }

        radios.forEach(radio => {
            radio.addEventListener('change', toggleContinuingFields);
        });
        toggleContinuingFields(); // inisialisasi awal

        // (Opsional) Divisi -> posisi otomatis jika diperlukan
        const divisiInput = document.getElementById('divisi_input');
        if (divisiInput) {
            divisiInput.addEventListener('input', function() {
                // hanya untuk tampilan, tidak perlu hidden karena posisi sudah ditentukan di controller
            });
        }
    });
</script>
@endsection
