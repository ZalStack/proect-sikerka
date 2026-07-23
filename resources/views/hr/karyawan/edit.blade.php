{{-- views/hr/karyawan/edit.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="flex min-h-screen">
    @include('layouts.sidebar')
    <div class="flex-1 transition-all duration-300 md:ml-64 p-3 sm:p-6">
        <div class="flex flex-wrap justify-between items-start mb-6 gap-3">
            <div>
                <h1 class="text-xl sm:text-2xl font-bold font-['Montserrat'] text-[#161758]">Edit Karyawan</h1>
                <p class="text-sm sm:text-base text-[#27438D]">Perbarui data karyawan</p>
            </div>
            <button onclick="toggleEditForm()"
                class="w-full sm:w-auto bg-[#27438D] text-white px-6 py-2 rounded-lg hover:bg-[#161758] transition-colors duration-200">
                <i class="fas fa-edit mr-2"></i> Edit Karyawan
            </button>
        </div>

        @if (session('error'))
            <div class="bg-[#ec1d1d] text-white p-4 rounded-lg mb-4">{{ session('error') }}</div>
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

        <!-- Detail Karyawan (Readonly) -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-100 mb-6">
            <!-- Header Profil -->
            <div class="p-6 bg-gradient-to-r from-[#f8faff] to-white border-b border-gray-200">
                <div class="flex flex-col md:flex-row items-center md:items-start space-y-4 md:space-y-0 md:space-x-6">
                    <div class="flex-shrink-0">
                        @if($karyawan->foto_profil)
                            <img src="{{ Storage::url($karyawan->foto_profil) }}" alt="Foto"
                                 class="w-24 h-24 sm:w-32 sm:h-32 rounded-full object-cover border-4 {{ $karyawan->is_resigned ? 'border-red-500' : 'border-[#00a2e9]' }}">
                        @else
                            <div class="w-24 h-24 sm:w-32 sm:h-32 rounded-full {{ $karyawan->is_resigned ? 'bg-red-500' : 'bg-[#00a2e9]' }} flex items-center justify-center text-white text-3xl sm:text-4xl font-bold">
                                {{ strtoupper(substr($karyawan->nama_lengkap, 0, 1)) }}
                            </div>
                        @endif
                    </div>
                    <div class="flex-1 text-center md:text-left">
                        <h2 class="text-xl sm:text-2xl font-bold text-[#161758] break-words">{{ $karyawan->nama_lengkap }}</h2>
                        <p class="text-sm sm:text-base text-[#27438D]">{{ $karyawan->jabatan }}</p>
                        <div class="flex flex-wrap items-center gap-2 mt-2 justify-center md:justify-start">
                            <span class="inline-block px-2 sm:px-3 py-1 rounded-full text-[10px] sm:text-sm font-medium {{ $karyawan->status_badge }}">
                                {{ $karyawan->status_label }}
                            </span>
                            <span class="inline-block px-2 sm:px-3 py-1 rounded-full text-[10px] sm:text-sm font-medium {{ $karyawan->posisi === 'hr' ? 'bg-[#27438D] text-white' : 'bg-[#00a2e9] text-white' }}">
                                {{ $karyawan->posisi === 'hr' ? 'HR' : 'Karyawan' }}
                            </span>
                            <span class="inline-block px-2 sm:px-3 py-1 rounded-full text-[10px] sm:text-sm font-medium bg-[#F5F5F5] text-[#1B1B1B]">
                                {{ $karyawan->divisi ?? '-' }}
                            </span>
                            @if($karyawan->is_resigned)
                                <span class="inline-block px-2 sm:px-3 py-1 rounded-full text-[10px] sm:text-sm font-medium bg-red-500 text-white animate-pulse">
                                    ⚠️ Resign
                                </span>
                            @endif
                        </div>
                        @if($karyawan->is_resigned)
                            <p class="text-xs sm:text-sm text-red-500 mt-2">
                                <strong>Tanggal Resign:</strong> {{ $karyawan->tanggal_resign ? $karyawan->tanggal_resign->format('d-m-Y') : '-' }}
                            </p>
                        @endif
                    </div>
                    <div class="flex-shrink-0 text-center md:text-right">
                        <div class="text-xs sm:text-sm text-gray-500">
                            <p>Bergabung: {{ $karyawan->tanggal_bergabung ? $karyawan->tanggal_bergabung->format('d-m-Y') : '-' }}</p>
                            <p>ID Karyawan: {{ $karyawan->kode_pegawai }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detail Data (Readonly) – Sama seperti di show.blade.php -->
            <div class="p-6">
                <!-- Informasi Pribadi -->
                <div class="mb-8">
                    <h3 class="text-base sm:text-lg font-semibold text-[#161758] border-b-2 border-[#00a2e9] pb-2 mb-4 flex items-center">
                        <span class="bg-[#00a2e9] text-white rounded-full w-6 h-6 flex items-center justify-center text-xs mr-2">1</span>
                        Informasi Pribadi
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
                        <div><label class="text-xs sm:text-sm text-[#1B1B1B] font-medium">ID Karyawan</label><p class="text-sm sm:text-base text-[#27438D] font-semibold break-words">{{ $karyawan->kode_pegawai }}</p></div>
                        <div><label class="text-xs sm:text-sm text-[#1B1B1B] font-medium">Nama Lengkap</label><p class="text-sm sm:text-base text-[#27438D] break-words">{{ $karyawan->nama_lengkap }}</p></div>
                        <div><label class="text-xs sm:text-sm text-[#1B1B1B] font-medium">Tempat Lahir</label><p class="text-sm sm:text-base text-[#27438D] break-words">{{ $karyawan->tempat_lahir ?? '-' }}</p></div>
                        <div><label class="text-xs sm:text-sm text-[#1B1B1B] font-medium">Tanggal Lahir</label><p class="text-sm sm:text-base text-[#27438D]">{{ $karyawan->tanggal_lahir ? $karyawan->tanggal_lahir->format('d-m-Y') : '-' }}</p></div>
                        <div><label class="text-xs sm:text-sm text-[#1B1B1B] font-medium">Jenis Kelamin</label><p class="text-sm sm:text-base text-[#27438D]">{{ $karyawan->jenis_kelamin ?? '-' }}</p></div>
                        <div><label class="text-xs sm:text-sm text-[#1B1B1B] font-medium">Nama Ibu Kandung</label><p class="text-sm sm:text-base text-[#27438D] break-words">{{ $karyawan->nama_ibu_kandung ?? '-' }}</p></div>
                        <div><label class="text-xs sm:text-sm text-[#1B1B1B] font-medium">NIK</label><p class="text-sm sm:text-base text-[#27438D] break-words">{{ $karyawan->nik ?? '-' }}</p></div>
                        <div><label class="text-xs sm:text-sm text-[#1B1B1B] font-medium">No KK</label><p class="text-sm sm:text-base text-[#27438D] break-words">{{ $karyawan->no_kk ?? '-' }}</p></div>
                        <div><label class="text-xs sm:text-sm text-[#1B1B1B] font-medium">Agama</label><p class="text-sm sm:text-base text-[#27438D] break-words">{{ $karyawan->agama ?? '-' }}</p></div>
                        <div><label class="text-xs sm:text-sm text-[#1B1B1B] font-medium">Status Pernikahan</label><p class="text-sm sm:text-base text-[#27438D] break-words">{{ $karyawan->status_pernikahan ?? '-' }}</p></div>
                        <div><label class="text-xs sm:text-sm text-[#1B1B1B] font-medium">Jumlah Anak</label><p class="text-sm sm:text-base text-[#27438D]">{{ $karyawan->jumlah_anak ?? 0 }}</p></div>
                        <div><label class="text-xs sm:text-sm text-[#1B1B1B] font-medium">Email</label><p class="text-sm sm:text-base text-[#27438D] break-all">{{ $karyawan->email }}</p></div>
                        <div><label class="text-xs sm:text-sm text-[#1B1B1B] font-medium">Golongan Darah</label><p class="text-sm sm:text-base text-[#27438D]">{{ $karyawan->golongan_darah ?? '-' }}</p></div>
                        <div><label class="text-xs sm:text-sm text-[#1B1B1B] font-medium">NPWP</label><p class="text-sm sm:text-base text-[#27438D] break-words">{{ $karyawan->npwp ?? '-' }}</p></div>
                    </div>
                </div>

                <!-- Informasi Profesional -->
                <div class="mb-8">
                    <h3 class="text-base sm:text-lg font-semibold text-[#161758] border-b-2 border-[#27438D] pb-2 mb-4 flex items-center">
                        <span class="bg-[#27438D] text-white rounded-full w-6 h-6 flex items-center justify-center text-xs mr-2">2</span>
                        Informasi Profesional
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
                        <div><label class="text-xs sm:text-sm text-[#1B1B1B] font-medium">Jabatan</label><p class="text-sm sm:text-base text-[#27438D] break-words">{{ $karyawan->jabatan }}</p></div>
                        <div><label class="text-xs sm:text-sm text-[#1B1B1B] font-medium">Divisi</label><p class="text-sm sm:text-base text-[#27438D] break-words">{{ $karyawan->divisi ?? '-' }}</p></div>
                        <div><label class="text-xs sm:text-sm text-[#1B1B1B] font-medium">Posisi</label><p class="text-sm sm:text-base text-[#27438D]"><span class="px-2 py-1 rounded-full text-xs font-medium {{ $karyawan->posisi === 'hr' ? 'bg-[#27438D] text-white' : 'bg-[#00a2e9] text-white' }}">{{ $karyawan->posisi === 'hr' ? 'HR' : 'Karyawan' }}</span></p></div>
                        <div><label class="text-xs sm:text-sm text-[#1B1B1B] font-medium">Status Karyawan</label><p class="text-sm sm:text-base text-[#27438D]"><span class="px-2 py-1 rounded-full text-xs font-medium {{ $karyawan->status_badge }}">{{ $karyawan->status_label }}</span></p></div>
                        <div><label class="text-xs sm:text-sm text-[#1B1B1B] font-medium">Tanggal Bergabung</label><p class="text-sm sm:text-base text-[#27438D]">{{ $karyawan->tanggal_bergabung ? $karyawan->tanggal_bergabung->format('d-m-Y') : '-' }}</p></div>
                        @if($karyawan->end_date)
                        <div><label class="text-xs sm:text-sm text-[#1B1B1B] font-medium">Tanggal Berakhir</label><p class="text-sm sm:text-base text-[#27438D]">{{ $karyawan->end_date->format('d-m-Y') }}</p></div>
                        @endif
                        <div><label class="text-xs sm:text-sm text-[#1B1B1B] font-medium">Tanggal Pengangkatan Tetap</label><p class="text-sm sm:text-base text-[#27438D]">{{ $karyawan->tanggal_pengangkatan_tetap ? $karyawan->tanggal_pengangkatan_tetap->format('d-m-Y') : '-' }}</p></div>
                    </div>
                </div>

                <!-- Pendidikan -->
                <div class="mb-8">
                    <h3 class="text-base sm:text-lg font-semibold text-[#161758] border-b-2 border-[#FCC626] pb-2 mb-4 flex items-center">
                        <span class="bg-[#FCC626] text-[#1B1B1B] rounded-full w-6 h-6 flex items-center justify-center text-xs mr-2">3</span>
                        Pendidikan
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
                        <div>
                            <div class="mb-2"><label class="text-xs sm:text-sm text-[#1B1B1B] font-medium">Pendidikan Terakhir</label><p class="text-sm sm:text-base text-[#27438D] break-words">{{ $karyawan->pendidikan_terakhir ?? '-' }}</p></div>
                            <div class="mb-2"><label class="text-xs sm:text-sm text-[#1B1B1B] font-medium">Perguruan Tinggi</label><p class="text-sm sm:text-base text-[#27438D] break-words">{{ $karyawan->perguruan_tinggi ?? '-' }}</p></div>
                            <div class="mb-2"><label class="text-xs sm:text-sm text-[#1B1B1B] font-medium">Jurusan / Program Studi</label><p class="text-sm sm:text-base text-[#27438D] break-words">{{ $karyawan->jurusan ?? '-' }}</p></div>
                            <div class="mb-2"><label class="text-xs sm:text-sm text-[#1B1B1B] font-medium">IPK Terakhir</label><p class="text-sm sm:text-base text-[#27438D]">{{ $karyawan->ipk_terakhir ?? '-' }}</p></div>
                            <div><label class="text-xs sm:text-sm text-[#1B1B1B] font-medium">Tahun Lulus</label><p class="text-sm sm:text-base text-[#27438D]">{{ $karyawan->tahun_lulus ?? '-' }}</p></div>
                        </div>
                        <div>
                            <div class="mb-2"><label class="text-xs sm:text-sm text-[#1B1B1B] font-medium">Sedang Melanjutkan Pendidikan?</label><p class="text-sm sm:text-base text-[#27438D] font-semibold">{{ $karyawan->is_continuing_education ? 'Iya' : 'Tidak' }}</p></div>
                            <div class="mb-2"><label class="text-xs sm:text-sm text-[#1B1B1B] font-medium">Program Pendidikan</label><p class="text-sm sm:text-base text-[#27438D]">{{ $karyawan->is_continuing_education ? ($karyawan->continuing_program ?? '-') : '-' }}</p></div>
                            <div class="mb-2"><label class="text-xs sm:text-sm text-[#1B1B1B] font-medium">Perguruan Tinggi</label><p class="text-sm sm:text-base text-[#27438D] break-words">{{ $karyawan->is_continuing_education ? ($karyawan->continuing_perguruan_tinggi ?? '-') : '-' }}</p></div>
                            <div><label class="text-xs sm:text-sm text-[#1B1B1B] font-medium">Jurusan / Program Studi</label><p class="text-sm sm:text-base text-[#27438D] break-words">{{ $karyawan->is_continuing_education ? ($karyawan->continuing_jurusan ?? '-') : '-' }}</p></div>
                        </div>
                    </div>
                </div>

                <!-- Kontak & Alamat -->
                <div class="mb-8">
                    <h3 class="text-base sm:text-lg font-semibold text-[#161758] border-b-2 border-[#2E7D3E] pb-2 mb-4 flex items-center">
                        <span class="bg-[#2E7D3E] text-white rounded-full w-6 h-6 flex items-center justify-center text-xs mr-2">4</span>
                        Kontak & Alamat
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
                        <div><label class="text-xs sm:text-sm text-[#1B1B1B] font-medium">Nomor Telepon</label><p class="text-sm sm:text-base text-[#27438D] break-words">{{ $karyawan->nomor_telepon ?? '-' }}</p></div>
                        <div><label class="text-xs sm:text-sm text-[#1B1B1B] font-medium">No WA</label><p class="text-sm sm:text-base text-[#27438D] break-words">{{ $karyawan->no_wa ?? '-' }}</p></div>
                        <div><label class="text-xs sm:text-sm text-[#1B1B1B] font-medium">Telepon Kontak Darurat</label><p class="text-sm sm:text-base text-[#27438D] break-words">{{ $karyawan->telepon_kontak_darurat ?? '-' }}</p></div>
                        <div><label class="text-xs sm:text-sm text-[#1B1B1B] font-medium">Nama Kontak Darurat</label><p class="text-sm sm:text-base text-[#27438D] break-words">{{ $karyawan->nama_kontak_darurat ?? '-' }}</p></div>
                        <div class="md:col-span-2"><label class="text-xs sm:text-sm text-[#1B1B1B] font-medium">Alamat (KTP)</label><p class="text-sm sm:text-base text-[#27438D] break-words">{{ $karyawan->alamat ?? '-' }}</p></div>
                        <div class="md:col-span-2"><label class="text-xs sm:text-sm text-[#1B1B1B] font-medium">Alamat Domisili</label><p class="text-sm sm:text-base text-[#27438D] break-words">{{ $karyawan->alamat_domisili ?? '-' }}</p></div>
                    </div>
                </div>

                <!-- Informasi Tambahan -->
                <div>
                    <h3 class="text-base sm:text-lg font-semibold text-[#161758] border-b-2 border-[#ec1d1d] pb-2 mb-4 flex items-center">
                        <span class="bg-[#ec1d1d] text-white rounded-full w-6 h-6 flex items-center justify-center text-xs mr-2">5</span>
                        Informasi Tambahan
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
                        <div><label class="text-xs sm:text-sm text-[#1B1B1B] font-medium">Nomor Rekening</label><p class="text-sm sm:text-base text-[#27438D] break-words">{{ $karyawan->nomor_rekening ?? '-' }}</p></div>
                        <div><label class="text-xs sm:text-sm text-[#1B1B1B] font-medium">Nama Bank</label><p class="text-sm sm:text-base text-[#27438D] break-words">{{ $karyawan->nama_bank ?? 'BSI' }}</p></div>
                        <div>
                            <label class="text-xs sm:text-sm text-[#1B1B1B] font-medium">Foto Profil</label>
                            @if($karyawan->foto_profil)
                                <img src="{{ Storage::url($karyawan->foto_profil) }}" alt="Foto Profil" class="w-16 h-16 sm:w-20 sm:h-20 rounded-full object-cover mt-1 border">
                            @else
                                <p class="text-sm sm:text-base text-[#27438D]">-</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Edit Karyawan (Tersembunyi) -->
        <div id="editForm" style="display: none;">
            <div class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden">
                <div class="p-6">
                    <h2 class="text-xl font-bold text-[#161758] mb-4">Edit Data Karyawan</h2>
                    <form action="{{ route('hr.karyawan.update', $karyawan->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Informasi Pribadi -->
                            <div class="md:col-span-2">
                                <h3 class="text-base sm:text-lg font-semibold text-[#161758] border-b-2 border-[#00a2e9] pb-2 mb-4 flex items-center">
                                    <span class="bg-[#00a2e9] text-white rounded-full w-6 h-6 flex items-center justify-center text-xs mr-2">1</span>
                                    Informasi Pribadi
                                </h3>
                            </div>

                            <!-- KOLOM KIRI -->
                            <div>
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-[#1B1B1B] mb-1">ID Karyawan <span class="text-[#ec1d1d]">*</span></label>
                                    <input type="text" name="kode_pegawai" value="{{ old('kode_pegawai', $karyawan->kode_pegawai) }}" required
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                                    @error('kode_pegawai') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                                </div>

                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Nama Lengkap <span class="text-[#ec1d1d]">*</span></label>
                                    <input type="text" name="nama_lengkap" value="{{ old('nama_lengkap', $karyawan->nama_lengkap) }}" required
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                                    @error('nama_lengkap') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                                </div>

                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Tempat Lahir</label>
                                    <input type="text" name="tempat_lahir" value="{{ old('tempat_lahir', $karyawan->tempat_lahir) }}"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                                    @error('tempat_lahir') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                                </div>

                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Tanggal Lahir</label>
                                    <input type="date" name="tanggal_lahir" value="{{ old('tanggal_lahir', $karyawan->tanggal_lahir ? $karyawan->tanggal_lahir->format('Y-m-d') : '') }}"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                                    @error('tanggal_lahir') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                                </div>

                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Jenis Kelamin</label>
                                    <select name="jenis_kelamin" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                                        <option value="">Pilih</option>
                                        <option value="Laki-laki" {{ old('jenis_kelamin', $karyawan->jenis_kelamin) === 'Laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                                        <option value="Perempuan" {{ old('jenis_kelamin', $karyawan->jenis_kelamin) === 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
                                    </select>
                                    @error('jenis_kelamin') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                                </div>

                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Nama Ibu Kandung</label>
                                    <input type="text" name="nama_ibu_kandung" value="{{ old('nama_ibu_kandung', $karyawan->nama_ibu_kandung) }}"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                                    @error('nama_ibu_kandung') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                                </div>

                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-[#1B1B1B] mb-1">NIK</label>
                                    <input type="text" name="nik" value="{{ old('nik', $karyawan->nik) }}"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                                    @error('nik') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                                </div>

                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-[#1B1B1B] mb-1">No KK</label>
                                    <input type="text" name="no_kk" value="{{ old('no_kk', $karyawan->no_kk) }}"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                                    @error('no_kk') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                                </div>

                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Agama</label>
                                    <input type="text" name="agama" value="{{ old('agama', $karyawan->agama) }}"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                                    @error('agama') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            <!-- KOLOM KANAN -->
                            <div>
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Status Pernikahan</label>
                                    <select name="status_pernikahan" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                                        <option value="">Pilih</option>
                                        <option value="Belum Menikah" {{ old('status_pernikahan', $karyawan->status_pernikahan) === 'Belum Menikah' ? 'selected' : '' }}>Belum Menikah</option>
                                        <option value="Menikah" {{ old('status_pernikahan', $karyawan->status_pernikahan) === 'Menikah' ? 'selected' : '' }}>Menikah</option>
                                        <option value="Cerai" {{ old('status_pernikahan', $karyawan->status_pernikahan) === 'Cerai' ? 'selected' : '' }}>Cerai</option>
                                    </select>
                                    @error('status_pernikahan') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                                </div>

                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Jumlah Anak</label>
                                    <input type="number" name="jumlah_anak" value="{{ old('jumlah_anak', $karyawan->jumlah_anak ?? 0) }}" min="0"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                                    @error('jumlah_anak') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                                </div>

                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Email <span class="text-[#ec1d1d]">*</span></label>
                                    <input type="email" name="email" value="{{ old('email', $karyawan->email) }}" required
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                                    @error('email') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                                </div>

                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Password Baru</label>
                                    <input type="password" name="password" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                                    <p class="text-xs text-gray-500 mt-1">Kosongkan jika tidak ingin mengubah password</p>
                                    @error('password') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                                </div>

                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Konfirmasi Password Baru</label>
                                    <input type="password" name="password_confirmation" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                                    @error('password_confirmation') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                                </div>

                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Golongan Darah</label>
                                    <select name="golongan_darah" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                                        <option value="">Pilih</option>
                                        <option value="A" {{ old('golongan_darah', $karyawan->golongan_darah) === 'A' ? 'selected' : '' }}>A</option>
                                        <option value="B" {{ old('golongan_darah', $karyawan->golongan_darah) === 'B' ? 'selected' : '' }}>B</option>
                                        <option value="AB" {{ old('golongan_darah', $karyawan->golongan_darah) === 'AB' ? 'selected' : '' }}>AB</option>
                                        <option value="O" {{ old('golongan_darah', $karyawan->golongan_darah) === 'O' ? 'selected' : '' }}>O</option>
                                    </select>
                                    @error('golongan_darah') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                                </div>

                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-[#1B1B1B] mb-1">NPWP</label>
                                    <input type="text" name="npwp" value="{{ old('npwp', $karyawan->npwp) }}"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                                    @error('npwp') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            <!-- Informasi Profesional -->
                            <div class="md:col-span-2">
                                <h3 class="text-base sm:text-lg font-semibold text-[#161758] border-b-2 border-[#27438D] pb-2 mb-4 mt-4 flex items-center">
                                    <span class="bg-[#27438D] text-white rounded-full w-6 h-6 flex items-center justify-center text-xs mr-2">2</span>
                                    Informasi Profesional
                                </h3>
                            </div>

                            <div>
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Jabatan <span class="text-[#ec1d1d]">*</span></label>
                                    <input type="text" name="jabatan" value="{{ old('jabatan', $karyawan->jabatan) }}" required
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                                    @error('jabatan') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                                </div>

                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Divisi <span class="text-[#ec1d1d]">*</span></label>
                                    <input type="text" name="divisi" id="divisi_input" value="{{ old('divisi', $karyawan->divisi) }}" required
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                                    <p class="text-xs text-[#27438D] mt-1">* Jika diisi "HRD", posisi akan otomatis menjadi HR</p>
                                    @error('divisi') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                                </div>

                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Posisi</label>
                                    <input type="text" id="posisi_display" value="{{ $karyawan->posisi === 'hr' ? 'HR' : 'Karyawan' }}" disabled
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-100">
                                    <input type="hidden" name="posisi" id="posisi_hidden" value="{{ $karyawan->posisi }}">
                                    <p class="text-xs text-[#27438D] mt-1">* Posisi ditentukan otomatis berdasarkan divisi</p>
                                </div>

                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Tanggal Pengangkatan Tetap</label>
                                    <input type="date" name="tanggal_pengangkatan_tetap" value="{{ old('tanggal_pengangkatan_tetap', $karyawan->tanggal_pengangkatan_tetap ? $karyawan->tanggal_pengangkatan_tetap->format('Y-m-d') : '') }}"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                                    @error('tanggal_pengangkatan_tetap') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            <div>
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Status <span class="text-[#ec1d1d]">*</span></label>
                                    <select name="status" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                                        <option value="Karyawan Tetap" {{ old('status', $karyawan->status) === 'Karyawan Tetap' ? 'selected' : '' }}>Karyawan Tetap</option>
                                        <option value="Contract" {{ old('status', $karyawan->status) === 'Contract' ? 'selected' : '' }}>Kontrak</option>
                                        <option value="Internship" {{ old('status', $karyawan->status) === 'Internship' ? 'selected' : '' }}>Magang</option>
                                    </select>
                                    @error('status') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                                </div>

                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Tanggal Bergabung <span class="text-[#ec1d1d]">*</span></label>
                                    <input type="date" name="tanggal_bergabung" value="{{ old('tanggal_bergabung', $karyawan->tanggal_bergabung ? $karyawan->tanggal_bergabung->format('Y-m-d') : '') }}" required
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                                    @error('tanggal_bergabung') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                                </div>

                                <!-- Status Resign -->
                                <div class="mb-4">
                                    <div class="flex items-center space-x-3">
                                        <input type="checkbox" name="is_resigned" id="is_resigned" value="1"
                                            {{ old('is_resigned', $karyawan->is_resigned) ? 'checked' : '' }}
                                            class="w-4 h-4 sm:w-5 sm:h-5 rounded text-[#ec1d1d] focus:ring-2 focus:ring-[#ec1d1d] transition-all duration-200">
                                        <label for="is_resigned" class="text-xs sm:text-sm font-medium text-[#1B1B1B]">
                                            ☑️ Karyawan ini sudah resign
                                        </label>
                                    </div>
                                    <p class="text-xs text-[#27438D] mt-1">* Jika dicentang, akun karyawan akan dinonaktifkan dan tidak bisa login</p>
                                </div>

                                <div id="tanggal_resign_container" class="mb-4 {{ old('is_resigned', $karyawan->is_resigned) ? '' : 'hidden' }}">
                                    <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Tanggal Resign <span class="text-[#ec1d1d]">*</span></label>
                                    <input type="date" name="tanggal_resign" id="tanggal_resign"
                                        value="{{ old('tanggal_resign', $karyawan->tanggal_resign ? $karyawan->tanggal_resign->format('Y-m-d') : '') }}"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                                    <p class="text-xs text-[#27438D] mt-1">* Tanggal resign harus setelah tanggal bergabung</p>
                                    @error('tanggal_resign') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            <!-- Pendidikan -->
                            <div class="md:col-span-2">
                                <h3 class="text-base sm:text-lg font-semibold text-[#161758] border-b-2 border-[#FCC626] pb-2 mb-4 mt-4 flex items-center">
                                    <span class="bg-[#FCC626] text-[#1B1B1B] rounded-full w-6 h-6 flex items-center justify-center text-xs mr-2">3</span>
                                    Pendidikan
                                </h3>
                            </div>

                            <!-- KOLOM KIRI: Pendidikan Terakhir -->
                            <div>
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Pendidikan Terakhir</label>
                                    <input type="text" name="pendidikan_terakhir" value="{{ old('pendidikan_terakhir', $karyawan->pendidikan_terakhir) }}"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                                    @error('pendidikan_terakhir') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                                </div>

                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Perguruan Tinggi</label>
                                    <input type="text" name="perguruan_tinggi" value="{{ old('perguruan_tinggi', $karyawan->perguruan_tinggi) }}"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                                    @error('perguruan_tinggi') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                                </div>

                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Jurusan / Program Studi</label>
                                    <input type="text" name="jurusan" value="{{ old('jurusan', $karyawan->jurusan) }}"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                                    @error('jurusan') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                                </div>

                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-[#1B1B1B] mb-1">IPK Terakhir</label>
                                    <input type="number" name="ipk_terakhir" value="{{ old('ipk_terakhir', $karyawan->ipk_terakhir) }}" step="0.01" min="0" max="4"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                                    @error('ipk_terakhir') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                                </div>

                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Tahun Lulus</label>
                                    <input type="number" name="tahun_lulus" value="{{ old('tahun_lulus', $karyawan->tahun_lulus) }}" min="1900" max="{{ date('Y') }}"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                                    @error('tahun_lulus') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            <!-- KOLOM KANAN: Sedang Melanjutkan Pendidikan -->
                            <div>
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Sedang Melanjutkan Pendidikan?</label>
                                    <div class="flex flex-wrap items-center gap-4">
                                        <label class="inline-flex items-center">
                                            <input type="radio" name="is_continuing_education" value="1" {{ old('is_continuing_education', $karyawan->is_continuing_education) ? 'checked' : '' }}
                                                class="w-4 h-4 text-[#27438D] border-gray-300 focus:ring-[#27438D]">
                                            <span class="ml-2 text-sm">Iya</span>
                                        </label>
                                        <label class="inline-flex items-center">
                                            <input type="radio" name="is_continuing_education" value="0" {{ old('is_continuing_education', $karyawan->is_continuing_education) === false ? 'checked' : '' }}
                                                class="w-4 h-4 text-[#27438D] border-gray-300 focus:ring-[#27438D]">
                                            <span class="ml-2 text-sm">Tidak</span>
                                        </label>
                                    </div>
                                    @error('is_continuing_education') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                                </div>

                                <div id="continuing_fields" style="{{ old('is_continuing_education', $karyawan->is_continuing_education) ? '' : 'display: none;' }}">
                                    <div class="mb-4">
                                        <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Program Pendidikan</label>
                                        <select name="continuing_program" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                                            <option value="">Pilih</option>
                                            <option value="D3" {{ old('continuing_program', $karyawan->continuing_program) === 'D3' ? 'selected' : '' }}>D3</option>
                                            <option value="D4/S1" {{ old('continuing_program', $karyawan->continuing_program) === 'D4/S1' ? 'selected' : '' }}>D4/S1</option>
                                            <option value="S2" {{ old('continuing_program', $karyawan->continuing_program) === 'S2' ? 'selected' : '' }}>S2</option>
                                            <option value="S3" {{ old('continuing_program', $karyawan->continuing_program) === 'S3' ? 'selected' : '' }}>S3</option>
                                        </select>
                                        @error('continuing_program') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                                    </div>

                                    <div class="mb-4">
                                        <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Perguruan Tinggi</label>
                                        <input type="text" name="continuing_perguruan_tinggi" value="{{ old('continuing_perguruan_tinggi', $karyawan->continuing_perguruan_tinggi) }}"
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                                        @error('continuing_perguruan_tinggi') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                                    </div>

                                    <div class="mb-4">
                                        <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Jurusan / Program Studi</label>
                                        <input type="text" name="continuing_jurusan" value="{{ old('continuing_jurusan', $karyawan->continuing_jurusan) }}"
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                                        @error('continuing_jurusan') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Kontak & Alamat -->
                            <div class="md:col-span-2">
                                <h3 class="text-base sm:text-lg font-semibold text-[#161758] border-b-2 border-[#2E7D3E] pb-2 mb-4 mt-4 flex items-center">
                                    <span class="bg-[#2E7D3E] text-white rounded-full w-6 h-6 flex items-center justify-center text-xs mr-2">4</span>
                                    Kontak & Alamat
                                </h3>
                            </div>

                            <div>
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Nomor Telepon</label>
                                    <input type="text" name="nomor_telepon" value="{{ old('nomor_telepon', $karyawan->nomor_telepon) }}"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                                    @error('nomor_telepon') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                                </div>

                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-[#1B1B1B] mb-1">No WA</label>
                                    <input type="text" name="no_wa" value="{{ old('no_wa', $karyawan->no_wa) }}"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                                    @error('no_wa') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                                </div>

                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Telepon Kontak Darurat</label>
                                    <input type="text" name="telepon_kontak_darurat" value="{{ old('telepon_kontak_darurat', $karyawan->telepon_kontak_darurat) }}"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                                    @error('telepon_kontak_darurat') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                                </div>

                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Nama Kontak Darurat</label>
                                    <input type="text" name="nama_kontak_darurat" value="{{ old('nama_kontak_darurat', $karyawan->nama_kontak_darurat) }}"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                                    @error('nama_kontak_darurat') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            <div>
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Alamat (KTP)</label>
                                    <textarea name="alamat" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">{{ old('alamat', $karyawan->alamat) }}</textarea>
                                    @error('alamat') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                                </div>

                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Alamat Domisili</label>
                                    <textarea name="alamat_domisili" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">{{ old('alamat_domisili', $karyawan->alamat_domisili) }}</textarea>
                                    @error('alamat_domisili') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            <!-- Informasi Tambahan -->
                            <div class="md:col-span-2">
                                <h3 class="text-base sm:text-lg font-semibold text-[#161758] border-b-2 border-[#ec1d1d] pb-2 mb-4 mt-4 flex items-center">
                                    <span class="bg-[#ec1d1d] text-white rounded-full w-6 h-6 flex items-center justify-center text-xs mr-2">5</span>
                                    Informasi Tambahan
                                </h3>
                            </div>

                            <div>
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Nomor Rekening</label>
                                    <input type="text" name="nomor_rekening" value="{{ old('nomor_rekening', $karyawan->nomor_rekening) }}"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                                    @error('nomor_rekening') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            <div>
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Nama Bank</label>
                                    <input type="text" value="BSI" disabled class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-100">
                                    <input type="hidden" name="nama_bank" value="BSI">
                                    <p class="text-xs text-[#27438D] mt-1">* Nama bank default BSI</p>
                                </div>

                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Foto Profil</label>
                                    <input type="file" name="foto_profil" accept="image/*" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                                    @if ($karyawan->foto_profil)
                                        <div class="mt-2"><img src="{{ Storage::url($karyawan->foto_profil) }}" alt="Foto Profil" class="w-20 h-20 rounded-full object-cover border"></div>
                                    @endif
                                    @error('foto_profil') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mt-6 flex flex-wrap gap-4">
                            <button type="submit" class="w-full sm:w-auto bg-[#27438D] text-white px-6 py-2 rounded-lg hover:bg-[#161758] transition-colors duration-200">Update Karyawan</button>
                            <button type="button" onclick="toggleEditForm()" class="w-full sm:w-auto bg-gray-500 text-white px-6 py-2 rounded-lg hover:bg-gray-600 transition-colors duration-200">Batal</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function toggleEditForm() {
        const form = document.getElementById('editForm');
        if (form.style.display === 'none') {
            form.style.display = 'block';
            form.scrollIntoView({ behavior: 'smooth', block: 'start' });
        } else {
            form.style.display = 'none';
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Posisi otomatis berdasarkan divisi
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

        // Toggle tanggal resign
        const isResignedCheckbox = document.getElementById('is_resigned');
        const tanggalResignContainer = document.getElementById('tanggal_resign_container');
        const tanggalResignInput = document.getElementById('tanggal_resign');

        function toggleTanggalResign() {
            if (isResignedCheckbox.checked) {
                tanggalResignContainer.classList.remove('hidden');
                tanggalResignInput.required = true;
            } else {
                tanggalResignContainer.classList.add('hidden');
                tanggalResignInput.required = false;
                tanggalResignInput.value = '';
            }
        }

        isResignedCheckbox.addEventListener('change', toggleTanggalResign);
        toggleTanggalResign();

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
