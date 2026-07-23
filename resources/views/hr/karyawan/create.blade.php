{{-- views/hr/karyawan/create.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="flex min-h-screen">
    @include('layouts.sidebar')
    <div class="flex-1 transition-all duration-300 md:ml-64 pt-6">
        <div class="p-4 sm:p-6">
            <div class="mb-6">
                <h1 class="text-xl sm:text-2xl font-bold font-['Montserrat'] text-[#161758]">Tambah Karyawan</h1>
                <p class="text-sm sm:text-base text-[#27438D]">Isi form untuk menambahkan karyawan baru</p>
            </div>

            @if (session('error'))
                <div class="bg-[#ec1d1d] text-white p-3 sm:p-4 rounded-lg mb-4 text-sm">
                    {{ session('error') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="bg-[#ec1d1d] text-white p-3 sm:p-4 rounded-lg mb-4 text-sm">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('hr.karyawan.store') }}" method="POST" enctype="multipart/form-data"
                class="bg-white rounded-lg shadow-md p-4 sm:p-6">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Informasi Pribadi -->
                    <div class="md:col-span-2">
                        <h3 class="text-base sm:text-lg font-semibold text-[#161758] border-b border-gray-200 pb-2 mb-4">Informasi Pribadi</h3>
                    </div>

                    <!-- KOLOM KIRI -->
                    <div>
                        <!-- ID Pegawai -->
                        <div class="mb-4">
                            <label class="block text-xs sm:text-sm font-medium text-[#1B1B1B] mb-1">ID Karyawan <span class="text-[#ec1d1d]">*</span></label>
                            <input type="text" name="kode_pegawai" value="{{ old('kode_pegawai') }}" required
                                class="w-full px-3 sm:px-4 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                            @error('kode_pegawai')
                                <p class="mt-1 text-xs sm:text-sm text-[#ec1d1d]">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Nama Lengkap -->
                        <div class="mb-4">
                            <label class="block text-xs sm:text-sm font-medium text-[#1B1B1B] mb-1">Nama Lengkap <span class="text-[#ec1d1d]">*</span></label>
                            <input type="text" name="nama_lengkap" value="{{ old('nama_lengkap') }}" required
                                class="w-full px-3 sm:px-4 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                            @error('nama_lengkap')
                                <p class="mt-1 text-xs sm:text-sm text-[#ec1d1d]">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Tempat Lahir -->
                        <div class="mb-4">
                            <label class="block text-xs sm:text-sm font-medium text-[#1B1B1B] mb-1">Tempat Lahir</label>
                            <input type="text" name="tempat_lahir" value="{{ old('tempat_lahir') }}"
                                class="w-full px-3 sm:px-4 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                            @error('tempat_lahir')
                                <p class="mt-1 text-xs sm:text-sm text-[#ec1d1d]">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Tanggal Lahir -->
                        <div class="mb-4">
                            <label class="block text-xs sm:text-sm font-medium text-[#1B1B1B] mb-1">Tanggal Lahir</label>
                            <input type="date" name="tanggal_lahir" value="{{ old('tanggal_lahir') }}"
                                class="w-full px-3 sm:px-4 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                            @error('tanggal_lahir')
                                <p class="mt-1 text-xs sm:text-sm text-[#ec1d1d]">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Jenis Kelamin -->
                        <div class="mb-4">
                            <label class="block text-xs sm:text-sm font-medium text-[#1B1B1B] mb-1">Jenis Kelamin</label>
                            <select name="jenis_kelamin"
                                class="w-full px-3 sm:px-4 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                                <option value="">Pilih</option>
                                <option value="Laki-laki" {{ old('jenis_kelamin') === 'Laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                                <option value="Perempuan" {{ old('jenis_kelamin') === 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
                            </select>
                            @error('jenis_kelamin')
                                <p class="mt-1 text-xs sm:text-sm text-[#ec1d1d]">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Nama Ibu Kandung -->
                        <div class="mb-4">
                            <label class="block text-xs sm:text-sm font-medium text-[#1B1B1B] mb-1">Nama Ibu Kandung</label>
                            <input type="text" name="nama_ibu_kandung" value="{{ old('nama_ibu_kandung') }}"
                                class="w-full px-3 sm:px-4 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                            @error('nama_ibu_kandung')
                                <p class="mt-1 text-xs sm:text-sm text-[#ec1d1d]">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- NIK -->
                        <div class="mb-4">
                            <label class="block text-xs sm:text-sm font-medium text-[#1B1B1B] mb-1">NIK</label>
                            <input type="text" name="nik" value="{{ old('nik') }}"
                                class="w-full px-3 sm:px-4 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                            @error('nik')
                                <p class="mt-1 text-xs sm:text-sm text-[#ec1d1d]">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- No KK -->
                        <div class="mb-4">
                            <label class="block text-xs sm:text-sm font-medium text-[#1B1B1B] mb-1">No KK</label>
                            <input type="text" name="no_kk" value="{{ old('no_kk') }}"
                                class="w-full px-3 sm:px-4 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                            @error('no_kk')
                                <p class="mt-1 text-xs sm:text-sm text-[#ec1d1d]">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Agama -->
                        <div class="mb-4">
                            <label class="block text-xs sm:text-sm font-medium text-[#1B1B1B] mb-1">Agama</label>
                            <input type="text" name="agama" value="{{ old('agama') }}"
                                class="w-full px-3 sm:px-4 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                            @error('agama')
                                <p class="mt-1 text-xs sm:text-sm text-[#ec1d1d]">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- KOLOM KANAN -->
                    <div>
                        <!-- Status Pernikahan -->
                        <div class="mb-4">
                            <label class="block text-xs sm:text-sm font-medium text-[#1B1B1B] mb-1">Status Pernikahan</label>
                            <select name="status_pernikahan"
                                class="w-full px-3 sm:px-4 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                                <option value="">Pilih</option>
                                <option value="Belum Menikah" {{ old('status_pernikahan') === 'Belum Menikah' ? 'selected' : '' }}>Belum Menikah</option>
                                <option value="Menikah" {{ old('status_pernikahan') === 'Menikah' ? 'selected' : '' }}>Menikah</option>
                                <option value="Cerai" {{ old('status_pernikahan') === 'Cerai' ? 'selected' : '' }}>Cerai</option>
                            </select>
                            @error('status_pernikahan')
                                <p class="mt-1 text-xs sm:text-sm text-[#ec1d1d]">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Jumlah Anak -->
                        <div class="mb-4">
                            <label class="block text-xs sm:text-sm font-medium text-[#1B1B1B] mb-1">Jumlah Anak</label>
                            <input type="number" name="jumlah_anak" value="{{ old('jumlah_anak', 0) }}" min="0"
                                class="w-full px-3 sm:px-4 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                            @error('jumlah_anak')
                                <p class="mt-1 text-xs sm:text-sm text-[#ec1d1d]">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Email -->
                        <div class="mb-4">
                            <label class="block text-xs sm:text-sm font-medium text-[#1B1B1B] mb-1">Email <span class="text-[#ec1d1d]">*</span></label>
                            <input type="email" name="email" value="{{ old('email') }}" required
                                class="w-full px-3 sm:px-4 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                            @error('email')
                                <p class="mt-1 text-xs sm:text-sm text-[#ec1d1d]">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Password -->
                        <div class="mb-4">
                            <label class="block text-xs sm:text-sm font-medium text-[#1B1B1B] mb-1">Password <span class="text-[#ec1d1d]">*</span></label>
                            <input type="password" name="password" required
                                class="w-full px-3 sm:px-4 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                            @error('password')
                                <p class="mt-1 text-xs sm:text-sm text-[#ec1d1d]">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Konfirmasi Password -->
                        <div class="mb-4">
                            <label class="block text-xs sm:text-sm font-medium text-[#1B1B1B] mb-1">Konfirmasi Password <span class="text-[#ec1d1d]">*</span></label>
                            <input type="password" name="password_confirmation" required
                                class="w-full px-3 sm:px-4 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                            @error('password_confirmation')
                                <p class="mt-1 text-xs sm:text-sm text-[#ec1d1d]">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Golongan Darah -->
                        <div class="mb-4">
                            <label class="block text-xs sm:text-sm font-medium text-[#1B1B1B] mb-1">Golongan Darah</label>
                            <select name="golongan_darah"
                                class="w-full px-3 sm:px-4 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                                <option value="">Pilih</option>
                                <option value="A" {{ old('golongan_darah') === 'A' ? 'selected' : '' }}>A</option>
                                <option value="B" {{ old('golongan_darah') === 'B' ? 'selected' : '' }}>B</option>
                                <option value="AB" {{ old('golongan_darah') === 'AB' ? 'selected' : '' }}>AB</option>
                                <option value="O" {{ old('golongan_darah') === 'O' ? 'selected' : '' }}>O</option>
                            </select>
                            @error('golongan_darah')
                                <p class="mt-1 text-xs sm:text-sm text-[#ec1d1d]">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Informasi Profesional -->
                    <div class="md:col-span-2">
                        <h3 class="text-base sm:text-lg font-semibold text-[#161758] border-b border-gray-200 pb-2 mb-4 mt-4">Informasi Profesional</h3>
                    </div>

                    <!-- KOLOM KIRI -->
                    <div>
                        <!-- Jabatan -->
                        <div class="mb-4">
                            <label class="block text-xs sm:text-sm font-medium text-[#1B1B1B] mb-1">Jabatan <span class="text-[#ec1d1d]">*</span></label>
                            <input type="text" name="jabatan" value="{{ old('jabatan') }}" required
                                class="w-full px-3 sm:px-4 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                            @error('jabatan')
                                <p class="mt-1 text-xs sm:text-sm text-[#ec1d1d]">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Divisi (input text) -->
                        <div class="mb-4">
                            <label class="block text-xs sm:text-sm font-medium text-[#1B1B1B] mb-1">Divisi <span class="text-[#ec1d1d]">*</span></label>
                            <input type="text" name="divisi" id="divisi_input" value="{{ old('divisi') }}" required
                                class="w-full px-3 sm:px-4 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                            <p class="text-[10px] sm:text-xs text-[#27438D] mt-1">* Jika diisi "HRD", posisi akan otomatis menjadi HR</p>
                            @error('divisi')
                                <p class="mt-1 text-xs sm:text-sm text-[#ec1d1d]">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Posisi (Otomatis) -->
                        <div class="mb-4">
                            <label class="block text-xs sm:text-sm font-medium text-[#1B1B1B] mb-1">Posisi</label>
                            <input type="text" id="posisi_display" value="Karyawan" disabled
                                class="w-full px-3 sm:px-4 py-2 text-sm sm:text-base border border-gray-300 rounded-lg bg-gray-100">
                            <input type="hidden" name="posisi" id="posisi_hidden" value="karyawan">
                            <p class="text-[10px] sm:text-xs text-[#27438D] mt-1">* Posisi ditentukan otomatis berdasarkan divisi</p>
                        </div>
                    </div>

                    <!-- KOLOM KANAN -->
                    <div>
                        <!-- Status Karyawan -->
                        <div class="mb-4">
                            <label class="block text-xs sm:text-sm font-medium text-[#1B1B1B] mb-1">Status <span class="text-[#ec1d1d]">*</span></label>
                            <select name="status" required
                                class="w-full px-3 sm:px-4 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                                <option value="Karyawan Tetap" {{ old('status') === 'Karyawan Tetap' ? 'selected' : '' }}>Karyawan Tetap</option>
                                <option value="Contract" {{ old('status') === 'Contract' ? 'selected' : '' }}>Kontrak</option>
                                <option value="Internship" {{ old('status') === 'Internship' ? 'selected' : '' }}>Magang</option>
                            </select>
                            @error('status')
                                <p class="mt-1 text-xs sm:text-sm text-[#ec1d1d]">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Tanggal Bergabung -->
                        <div class="mb-4">
                            <label class="block text-xs sm:text-sm font-medium text-[#1B1B1B] mb-1">Tanggal Bergabung <span class="text-[#ec1d1d]">*</span></label>
                            <input type="date" name="tanggal_bergabung" value="{{ old('tanggal_bergabung') }}" required
                                class="w-full px-3 sm:px-4 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                            @error('tanggal_bergabung')
                                <p class="mt-1 text-xs sm:text-sm text-[#ec1d1d]">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- NPWP -->
                        <div class="mb-4">
                            <label class="block text-xs sm:text-sm font-medium text-[#1B1B1B] mb-1">NPWP</label>
                            <input type="text" name="npwp" value="{{ old('npwp') }}"
                                class="w-full px-3 sm:px-4 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                            @error('npwp')
                                <p class="mt-1 text-xs sm:text-sm text-[#ec1d1d]">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Pendidikan -->
                    <div class="md:col-span-2">
                        <h3 class="text-base sm:text-lg font-semibold text-[#161758] border-b border-gray-200 pb-2 mb-4 mt-4">Pendidikan</h3>
                    </div>

                    <!-- KOLOM KIRI: Pendidikan Lanjutan -->
                    <div>
                        <div class="mb-4">
                            <label class="block text-xs sm:text-sm font-medium text-[#1B1B1B] mb-1">Sedang Melanjutkan Pendidikan?</label>
                            <div class="flex flex-wrap items-center gap-4">
                                <label class="inline-flex items-center">
                                    <input type="radio" name="is_continuing_education" value="1" {{ old('is_continuing_education') ? 'checked' : '' }}
                                        class="w-4 h-4 text-[#27438D] border-gray-300 focus:ring-[#27438D]">
                                    <span class="ml-2 text-sm">Iya</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="radio" name="is_continuing_education" value="0" {{ old('is_continuing_education') === null || old('is_continuing_education') == '0' ? 'checked' : '' }}
                                        class="w-4 h-4 text-[#27438D] border-gray-300 focus:ring-[#27438D]">
                                    <span class="ml-2 text-sm">Tidak</span>
                                </label>
                            </div>
                            @error('is_continuing_education')
                                <p class="mt-1 text-xs sm:text-sm text-[#ec1d1d]">{{ $message }}</p>
                            @enderror
                        </div>

                        <div id="continuing_fields" style="{{ old('is_continuing_education') ? '' : 'display: none;' }}">
                            <div class="mb-4">
                                <label class="block text-xs sm:text-sm font-medium text-[#1B1B1B] mb-1">Program Pendidikan Yang Diambil</label>
                                <select name="continuing_program" class="w-full px-3 sm:px-4 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                                    <option value="">Pilih</option>
                                    <option value="D3" {{ old('continuing_program') === 'D3' ? 'selected' : '' }}>D3</option>
                                    <option value="D4/S1" {{ old('continuing_program') === 'D4/S1' ? 'selected' : '' }}>D4/S1</option>
                                    <option value="S2" {{ old('continuing_program') === 'S2' ? 'selected' : '' }}>S2</option>
                                    <option value="S3" {{ old('continuing_program') === 'S3' ? 'selected' : '' }}>S3</option>
                                </select>
                                @error('continuing_program')
                                    <p class="mt-1 text-xs sm:text-sm text-[#ec1d1d]">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <label class="block text-xs sm:text-sm font-medium text-[#1B1B1B] mb-1">Perguruan Tinggi</label>
                                <input type="text" name="continuing_perguruan_tinggi" value="{{ old('continuing_perguruan_tinggi') }}"
                                    class="w-full px-3 sm:px-4 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                                @error('continuing_perguruan_tinggi')
                                    <p class="mt-1 text-xs sm:text-sm text-[#ec1d1d]">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <label class="block text-xs sm:text-sm font-medium text-[#1B1B1B] mb-1">Jurusan / Program Studi</label>
                                <input type="text" name="continuing_jurusan" value="{{ old('continuing_jurusan') }}"
                                    class="w-full px-3 sm:px-4 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                                @error('continuing_jurusan')
                                    <p class="mt-1 text-xs sm:text-sm text-[#ec1d1d]">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- KOLOM KANAN: Pendidikan Terakhir -->
                    <div>
                        <div class="mb-4">
                            <label class="block text-xs sm:text-sm font-medium text-[#1B1B1B] mb-1">Pendidikan Terakhir</label>
                            <input type="text" name="pendidikan_terakhir" value="{{ old('pendidikan_terakhir') }}"
                                class="w-full px-3 sm:px-4 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                            @error('pendidikan_terakhir')
                                <p class="mt-1 text-xs sm:text-sm text-[#ec1d1d]">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label class="block text-xs sm:text-sm font-medium text-[#1B1B1B] mb-1">Perguruan Tinggi</label>
                            <input type="text" name="perguruan_tinggi" value="{{ old('perguruan_tinggi') }}"
                                class="w-full px-3 sm:px-4 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                            @error('perguruan_tinggi')
                                <p class="mt-1 text-xs sm:text-sm text-[#ec1d1d]">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label class="block text-xs sm:text-sm font-medium text-[#1B1B1B] mb-1">Jurusan / Program Studi</label>
                            <input type="text" name="jurusan" value="{{ old('jurusan') }}"
                                class="w-full px-3 sm:px-4 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                            @error('jurusan')
                                <p class="mt-1 text-xs sm:text-sm text-[#ec1d1d]">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label class="block text-xs sm:text-sm font-medium text-[#1B1B1B] mb-1">IPK Terakhir</label>
                            <input type="number" name="ipk_terakhir" value="{{ old('ipk_terakhir') }}" step="0.01" min="0" max="4"
                                class="w-full px-3 sm:px-4 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                            @error('ipk_terakhir')
                                <p class="mt-1 text-xs sm:text-sm text-[#ec1d1d]">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Kontak & Alamat -->
                    <div class="md:col-span-2">
                        <h3 class="text-base sm:text-lg font-semibold text-[#161758] border-b border-gray-200 pb-2 mb-4 mt-4">Kontak & Alamat</h3>
                    </div>

                    <!-- KOLOM KIRI -->
                    <div>
                        <div class="mb-4">
                            <label class="block text-xs sm:text-sm font-medium text-[#1B1B1B] mb-1">Nomor Telepon</label>
                            <input type="text" name="nomor_telepon" value="{{ old('nomor_telepon') }}"
                                class="w-full px-3 sm:px-4 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                            @error('nomor_telepon')
                                <p class="mt-1 text-xs sm:text-sm text-[#ec1d1d]">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label class="block text-xs sm:text-sm font-medium text-[#1B1B1B] mb-1">No WA</label>
                            <input type="text" name="no_wa" value="{{ old('no_wa') }}"
                                class="w-full px-3 sm:px-4 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                            @error('no_wa')
                                <p class="mt-1 text-xs sm:text-sm text-[#ec1d1d]">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label class="block text-xs sm:text-sm font-medium text-[#1B1B1B] mb-1">Nama Kontak Darurat</label>
                            <input type="text" name="nama_kontak_darurat" value="{{ old('nama_kontak_darurat') }}"
                                class="w-full px-3 sm:px-4 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                            @error('nama_kontak_darurat')
                                <p class="mt-1 text-xs sm:text-sm text-[#ec1d1d]">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- KOLOM KANAN -->
                    <div>
                        <div class="mb-4">
                            <label class="block text-xs sm:text-sm font-medium text-[#1B1B1B] mb-1">Alamat</label>
                            <textarea name="alamat" rows="3"
                                class="w-full px-3 sm:px-4 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">{{ old('alamat') }}</textarea>
                            @error('alamat')
                                <p class="mt-1 text-xs sm:text-sm text-[#ec1d1d]">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label class="block text-xs sm:text-sm font-medium text-[#1B1B1B] mb-1">Alamat Domisili Saat Ini</label>
                            <textarea name="alamat_domisili" rows="3"
                                class="w-full px-3 sm:px-4 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">{{ old('alamat_domisili') }}</textarea>
                            @error('alamat_domisili')
                                <p class="mt-1 text-xs sm:text-sm text-[#ec1d1d]">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label class="block text-xs sm:text-sm font-medium text-[#1B1B1B] mb-1">Telepon Kontak Darurat</label>
                            <input type="text" name="telepon_kontak_darurat" value="{{ old('telepon_kontak_darurat') }}"
                                class="w-full px-3 sm:px-4 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                            @error('telepon_kontak_darurat')
                                <p class="mt-1 text-xs sm:text-sm text-[#ec1d1d]">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Informasi Tambahan -->
                    <div class="md:col-span-2">
                        <h3 class="text-base sm:text-lg font-semibold text-[#161758] border-b border-gray-200 pb-2 mb-4 mt-4">Informasi Tambahan</h3>
                    </div>

                    <!-- KOLOM KIRI -->
                    <div>
                        <div class="mb-4">
                            <label class="block text-xs sm:text-sm font-medium text-[#1B1B1B] mb-1">Tanggal Pengangkatan Karyawan Tetap</label>
                            <input type="date" name="tanggal_pengangkatan_tetap" value="{{ old('tanggal_pengangkatan_tetap') }}"
                                class="w-full px-3 sm:px-4 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                            @error('tanggal_pengangkatan_tetap')
                                <p class="mt-1 text-xs sm:text-sm text-[#ec1d1d]">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label class="block text-xs sm:text-sm font-medium text-[#1B1B1B] mb-1">Nomor Rekening</label>
                            <input type="text" name="nomor_rekening" value="{{ old('nomor_rekening') }}"
                                class="w-full px-3 sm:px-4 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                            @error('nomor_rekening')
                                <p class="mt-1 text-xs sm:text-sm text-[#ec1d1d]">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- KOLOM KANAN -->
                    <div>
                        <div class="mb-4">
                            <label class="block text-xs sm:text-sm font-medium text-[#1B1B1B] mb-1">Nama Bank</label>
                            <input type="text" value="BSI" disabled
                                class="w-full px-3 sm:px-4 py-2 text-sm sm:text-base border border-gray-300 rounded-lg bg-gray-100">
                            <input type="hidden" name="nama_bank" value="BSI">
                            <p class="text-[10px] sm:text-xs text-[#27438D] mt-1">* Nama bank default BSI</p>
                        </div>

                        <div class="mb-4">
                            <label class="block text-xs sm:text-sm font-medium text-[#1B1B1B] mb-1">Foto Profil</label>
                            <input type="file" name="foto_profil" accept="image/*"
                                class="w-full px-3 sm:px-4 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                            @error('foto_profil')
                                <p class="mt-1 text-xs sm:text-sm text-[#ec1d1d]">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex flex-wrap gap-3 sm:gap-4">
                    <button type="submit"
                        class="w-full sm:w-auto bg-[#27438D] text-white px-4 sm:px-6 py-2 rounded-lg hover:bg-[#161758] transition-colors duration-200 text-sm sm:text-base">
                        Simpan
                    </button>
                    <a href="{{ route('hr.karyawan.index') }}"
                        class="w-full sm:w-auto text-center bg-gray-500 text-white px-4 sm:px-6 py-2 rounded-lg hover:bg-gray-600 transition-colors duration-200 text-sm sm:text-base">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const divisiInput = document.getElementById('divisi_input');
        const posisiDisplay = document.getElementById('posisi_display');
        const posisiHidden = document.getElementById('posisi_hidden');

        function updatePosisi() {
            const divisi = divisiInput.value.trim();
            if (divisi === 'HRD') {
                posisiDisplay.value = 'HR';
                posisiHidden.value = 'hr';
            } else {
                posisiDisplay.value = 'Karyawan';
                posisiHidden.value = 'karyawan';
            }
        }

        divisiInput.addEventListener('input', updatePosisi);
        updatePosisi();

        // Toggle pendidikan lanjutan
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
        toggleContinuingFields();
    });
</script>
@endsection
