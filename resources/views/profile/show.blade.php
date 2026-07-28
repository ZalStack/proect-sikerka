{{-- resources/views/profile/show.blade.php --}}
@extends('layouts.app')

@section('content')
    <div class="flex min-h-screen">
        @include('layouts.sidebar')
        <div class="flex-1 transition-all duration-300 md:ml-64 p-3 sm:p-6">
            <div class="flex flex-wrap justify-between items-start mb-6 gap-3">
                <div>
                    <h1 class="text-2xl font-bold font-['Montserrat'] text-[#161758]">Profile Saya</h1>
                    <p class="text-[#27438D]">Detail informasi profil Anda</p>
                </div>

                {{-- ===== TOMBOL AKSI (tanpa dropdown) ===== --}}
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('profile.edit') }}"
                        class="bg-[#00a2e9] text-white px-4 py-2 rounded-lg hover:bg-[#27438D] transition-colors duration-200">
                        <i class="fas fa-edit mr-2"></i> Edit Profile
                    </a>
                    <a href="{{ route('profile.achievement') }}"
                        class="bg-[#FCC626] text-[#1B1B1B] px-4 py-2 rounded-lg hover:bg-[#e6b222] transition-colors duration-200">
                        <i class="fas fa-trophy mr-2"></i> Achievement
                    </a>
                </div>
            </div>

            {{-- Notifikasi --}}
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

            <!-- ========== DETAIL PROFIL (READONLY) ========== -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-100 mb-6">
                <!-- Header Profil -->
                <div class="p-6 bg-gradient-to-r from-[#f8faff] to-white border-b border-gray-200">
                    <div class="flex flex-col md:flex-row items-center md:items-start space-y-4 md:space-y-0 md:space-x-6">
                        <div class="flex-shrink-0">
                            @if ($user->foto_profil)
                                <img src="{{ Storage::url($user->foto_profil) }}" alt="Foto"
                                    class="w-24 h-24 sm:w-32 sm:h-32 rounded-full object-cover border-4 border-[#00a2e9]">
                            @else
                                <div
                                    class="w-24 h-24 sm:w-32 sm:h-32 rounded-full bg-[#00a2e9] flex items-center justify-center text-white text-3xl sm:text-4xl font-bold">
                                    {{ strtoupper(substr($user->nama_lengkap, 0, 1)) }}
                                </div>
                            @endif
                        </div>
                        <div class="flex-1 text-center md:text-left">
                            <h2 class="text-xl sm:text-2xl font-bold text-[#161758] break-words">{{ $user->nama_lengkap }}
                            </h2>
                            <p class="text-sm sm:text-base text-[#27438D]">{{ $user->jabatan }}</p>
                            <div class="flex flex-wrap items-center gap-2 mt-2 justify-center md:justify-start">
                                <span
                                    class="inline-block px-2 sm:px-3 py-1 rounded-full text-[10px] sm:text-sm font-medium {{ $user->status_badge }}">
                                    {{ $user->status_label }}
                                </span>
                                <span
                                    class="inline-block px-2 sm:px-3 py-1 rounded-full text-[10px] sm:text-sm font-medium {{ $user->posisi === 'hr' ? 'bg-[#27438D] text-white' : 'bg-[#00a2e9] text-white' }}">
                                    {{ $user->posisi === 'hr' ? 'HR' : 'Karyawan' }}
                                </span>
                                <span
                                    class="inline-block px-2 sm:px-3 py-1 rounded-full text-[10px] sm:text-sm font-medium bg-[#F5F5F5] text-[#1B1B1B]">
                                    {{ $user->divisi ?? '-' }}
                                </span>
                            </div>
                        </div>
                        <div class="flex-shrink-0 text-center md:text-right">
                            <div class="text-xs sm:text-sm text-gray-500">
                                <p>Bergabung:
                                    {{ $user->tanggal_bergabung ? $user->tanggal_bergabung->format('d-m-Y') : '-' }}</p>
                                <p>ID Karyawan: {{ $user->kode_pegawai }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Detail Data (Readonly) -->
                <div class="p-6">
                    <!-- 1. Informasi Pribadi -->
                    <div class="mb-8">
                        <h3
                            class="text-base sm:text-lg font-semibold text-[#161758] border-b-2 border-[#00a2e9] pb-2 mb-4 flex items-center">
                            <span
                                class="bg-[#00a2e9] text-white rounded-full w-6 h-6 flex items-center justify-center text-xs mr-2">1</span>
                            Informasi Pribadi
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
                            <div><label class="text-xs sm:text-sm text-[#1B1B1B] font-medium">ID Karyawan</label>
                                <p class="text-sm sm:text-base text-[#27438D] font-semibold break-words">
                                    {{ $user->kode_pegawai }}</p>
                            </div>
                            <div><label class="text-xs sm:text-sm text-[#1B1B1B] font-medium">Nama Lengkap</label>
                                <p class="text-sm sm:text-base text-[#27438D] break-words">{{ $user->nama_lengkap }}</p>
                            </div>
                            <div><label class="text-xs sm:text-sm text-[#1B1B1B] font-medium">Tempat Lahir</label>
                                <p class="text-sm sm:text-base text-[#27438D] break-words">{{ $user->tempat_lahir ?? '-' }}
                                </p>
                            </div>
                            <div><label class="text-xs sm:text-sm text-[#1B1B1B] font-medium">Tanggal Lahir</label>
                                <p class="text-sm sm:text-base text-[#27438D]">
                                    {{ $user->tanggal_lahir ? $user->tanggal_lahir->format('d-m-Y') : '-' }}</p>
                            </div>
                            <div><label class="text-xs sm:text-sm text-[#1B1B1B] font-medium">Jenis Kelamin</label>
                                <p class="text-sm sm:text-base text-[#27438D]">{{ $user->jenis_kelamin ?? '-' }}</p>
                            </div>
                            <div><label class="text-xs sm:text-sm text-[#1B1B1B] font-medium">Nama Ibu Kandung</label>
                                <p class="text-sm sm:text-base text-[#27438D] break-words">
                                    {{ $user->nama_ibu_kandung ?? '-' }}</p>
                            </div>
                            <div><label class="text-xs sm:text-sm text-[#1B1B1B] font-medium">NIK</label>
                                <p class="text-sm sm:text-base text-[#27438D] break-words">{{ $user->nik ?? '-' }}</p>
                            </div>
                            <div><label class="text-xs sm:text-sm text-[#1B1B1B] font-medium">No KK</label>
                                <p class="text-sm sm:text-base text-[#27438D] break-words">{{ $user->no_kk ?? '-' }}</p>
                            </div>
                            <div><label class="text-xs sm:text-sm text-[#1B1B1B] font-medium">Agama</label>
                                <p class="text-sm sm:text-base text-[#27438D] break-words">{{ $user->agama ?? '-' }}</p>
                            </div>
                            <div><label class="text-xs sm:text-sm text-[#1B1B1B] font-medium">Status Pernikahan</label>
                                <p class="text-sm sm:text-base text-[#27438D] break-words">
                                    {{ $user->status_pernikahan ?? '-' }}</p>
                            </div>
                            <div><label class="text-xs sm:text-sm text-[#1B1B1B] font-medium">Jumlah Anak</label>
                                <p class="text-sm sm:text-base text-[#27438D]">{{ $user->jumlah_anak ?? 0 }}</p>
                            </div>
                            <div><label class="text-xs sm:text-sm text-[#1B1B1B] font-medium">Email</label>
                                <p class="text-sm sm:text-base text-[#27438D] break-all">{{ $user->email }}</p>
                            </div>
                            <div><label class="text-xs sm:text-sm text-[#1B1B1B] font-medium">Golongan Darah</label>
                                <p class="text-sm sm:text-base text-[#27438D]">{{ $user->golongan_darah ?? '-' }}</p>
                            </div>
                            <div><label class="text-xs sm:text-sm text-[#1B1B1B] font-medium">NPWP</label>
                                <p class="text-sm sm:text-base text-[#27438D] break-words">{{ $user->npwp ?? '-' }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- 2. Informasi Profesional -->
                    <div class="mb-8">
                        <h3
                            class="text-base sm:text-lg font-semibold text-[#161758] border-b-2 border-[#27438D] pb-2 mb-4 flex items-center">
                            <span
                                class="bg-[#27438D] text-white rounded-full w-6 h-6 flex items-center justify-center text-xs mr-2">2</span>
                            Informasi Profesional
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
                            <div><label class="text-xs sm:text-sm text-[#1B1B1B] font-medium">Jabatan</label>
                                <p class="text-sm sm:text-base text-[#27438D] break-words">{{ $user->jabatan }}</p>
                            </div>
                            <div><label class="text-xs sm:text-sm text-[#1B1B1B] font-medium">Divisi</label>
                                <p class="text-sm sm:text-base text-[#27438D] break-words">{{ $user->divisi ?? '-' }}</p>
                            </div>
                            <div><label class="text-xs sm:text-sm text-[#1B1B1B] font-medium">Posisi</label>
                                <p class="text-sm sm:text-base text-[#27438D]"><span
                                        class="px-2 py-1 rounded-full text-xs font-medium {{ $user->posisi === 'hr' ? 'bg-[#27438D] text-white' : 'bg-[#00a2e9] text-white' }}">{{ $user->posisi === 'hr' ? 'HR' : 'Karyawan' }}</span>
                                </p>
                            </div>
                            <div><label class="text-xs sm:text-sm text-[#1B1B1B] font-medium">Status Karyawan</label>
                                <p class="text-sm sm:text-base text-[#27438D]"><span
                                        class="px-2 py-1 rounded-full text-xs font-medium {{ $user->status_badge }}">{{ $user->status_label }}</span>
                                </p>
                            </div>
                            <div><label class="text-xs sm:text-sm text-[#1B1B1B] font-medium">Tanggal Bergabung</label>
                                <p class="text-sm sm:text-base text-[#27438D]">
                                    {{ $user->tanggal_bergabung ? $user->tanggal_bergabung->format('d-m-Y') : '-' }}</p>
                            </div>
                            @if ($user->end_date)
                                <div><label class="text-xs sm:text-sm text-[#1B1B1B] font-medium">Tanggal Berakhir</label>
                                    <p class="text-sm sm:text-base text-[#27438D]">{{ $user->end_date->format('d-m-Y') }}
                                    </p>
                                </div>
                            @endif
                            <div><label class="text-xs sm:text-sm text-[#1B1B1B] font-medium">Tanggal Pengangkatan Karyawan
                                    Tetap</label>
                                <p class="text-sm sm:text-base text-[#27438D]">
                                    {{ $user->tanggal_pengangkatan_tetap ? $user->tanggal_pengangkatan_tetap->format('d-m-Y') : '-' }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- 3. Pendidikan -->
                    <div class="mb-8">
                        <h3
                            class="text-base sm:text-lg font-semibold text-[#161758] border-b-2 border-[#FCC626] pb-2 mb-4 flex items-center">
                            <span
                                class="bg-[#FCC626] text-[#1B1B1B] rounded-full w-6 h-6 flex items-center justify-center text-xs mr-2">3</span>
                            Pendidikan
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
                            <div>
                                <div class="mb-2"><label class="text-xs sm:text-sm text-[#1B1B1B] font-medium">Pendidikan
                                        Terakhir</label>
                                    <p class="text-sm sm:text-base text-[#27438D] break-words">
                                        {{ $user->pendidikan_terakhir ?? '-' }}</p>
                                </div>
                                <div class="mb-2"><label class="text-xs sm:text-sm text-[#1B1B1B] font-medium">Perguruan
                                        Tinggi</label>
                                    <p class="text-sm sm:text-base text-[#27438D] break-words">
                                        {{ $user->perguruan_tinggi ?? '-' }}</p>
                                </div>
                                <div class="mb-2"><label class="text-xs sm:text-sm text-[#1B1B1B] font-medium">Jurusan /
                                        Program Studi</label>
                                    <p class="text-sm sm:text-base text-[#27438D] break-words">{{ $user->jurusan ?? '-' }}
                                    </p>
                                </div>
                                <div class="mb-2"><label class="text-xs sm:text-sm text-[#1B1B1B] font-medium">IPK
                                        Terakhir</label>
                                    <p class="text-sm sm:text-base text-[#27438D]">{{ $user->ipk_terakhir ?? '-' }}</p>
                                </div>
                                <div><label class="text-xs sm:text-sm text-[#1B1B1B] font-medium">Tahun Lulus</label>
                                    <p class="text-sm sm:text-base text-[#27438D]">{{ $user->tahun_lulus ?? '-' }}</p>
                                </div>
                            </div>
                            <div>
                                <div class="mb-2"><label class="text-xs sm:text-sm text-[#1B1B1B] font-medium">Sedang
                                        Melanjutkan Pendidikan?</label>
                                    <p class="text-sm sm:text-base text-[#27438D] font-semibold">
                                        {{ $user->is_continuing_education ? 'Iya' : 'Tidak' }}</p>
                                </div>
                                <div class="mb-2"><label class="text-xs sm:text-sm text-[#1B1B1B] font-medium">Program
                                        Pendidikan</label>
                                    <p class="text-sm sm:text-base text-[#27438D]">
                                        {{ $user->is_continuing_education ? $user->continuing_program ?? '-' : '-' }}</p>
                                </div>
                                <div class="mb-2"><label class="text-xs sm:text-sm text-[#1B1B1B] font-medium">Perguruan
                                        Tinggi</label>
                                    <p class="text-sm sm:text-base text-[#27438D] break-words">
                                        {{ $user->is_continuing_education ? $user->continuing_perguruan_tinggi ?? '-' : '-' }}
                                    </p>
                                </div>
                                <div><label class="text-xs sm:text-sm text-[#1B1B1B] font-medium">Jurusan / Program
                                        Studi</label>
                                    <p class="text-sm sm:text-base text-[#27438D] break-words">
                                        {{ $user->is_continuing_education ? $user->continuing_jurusan ?? '-' : '-' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 4. Kontak & Alamat -->
                    <div class="mb-8">
                        <h3
                            class="text-base sm:text-lg font-semibold text-[#161758] border-b-2 border-[#2E7D3E] pb-2 mb-4 flex items-center">
                            <span
                                class="bg-[#2E7D3E] text-white rounded-full w-6 h-6 flex items-center justify-center text-xs mr-2">4</span>
                            Kontak & Alamat
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
                            <div><label class="text-xs sm:text-sm text-[#1B1B1B] font-medium">Nomor Telepon</label>
                                <p class="text-sm sm:text-base text-[#27438D] break-words">
                                    {{ $user->nomor_telepon ?? '-' }}</p>
                            </div>
                            <div><label class="text-xs sm:text-sm text-[#1B1B1B] font-medium">No WA</label>
                                <p class="text-sm sm:text-base text-[#27438D] break-words">{{ $user->no_wa ?? '-' }}</p>
                            </div>
                            <div><label class="text-xs sm:text-sm text-[#1B1B1B] font-medium">Telepon Kontak
                                    Darurat</label>
                                <p class="text-sm sm:text-base text-[#27438D] break-words">
                                    {{ $user->telepon_kontak_darurat ?? '-' }}</p>
                            </div>
                            <div><label class="text-xs sm:text-sm text-[#1B1B1B] font-medium">Nama Kontak Darurat</label>
                                <p class="text-sm sm:text-base text-[#27438D] break-words">
                                    {{ $user->nama_kontak_darurat ?? '-' }}</p>
                            </div>
                            <div class="md:col-span-2"><label class="text-xs sm:text-sm text-[#1B1B1B] font-medium">Alamat
                                    (KTP)</label>
                                <p class="text-sm sm:text-base text-[#27438D] break-words">{{ $user->alamat ?? '-' }}</p>
                            </div>
                            <div class="md:col-span-2"><label class="text-xs sm:text-sm text-[#1B1B1B] font-medium">Alamat
                                    Domisili</label>
                                <p class="text-sm sm:text-base text-[#27438D] break-words">
                                    {{ $user->alamat_domisili ?? '-' }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- 5. Informasi Tambahan -->
                    <div>
                        <h3
                            class="text-base sm:text-lg font-semibold text-[#161758] border-b-2 border-[#ec1d1d] pb-2 mb-4 flex items-center">
                            <span
                                class="bg-[#ec1d1d] text-white rounded-full w-6 h-6 flex items-center justify-center text-xs mr-2">5</span>
                            Informasi Tambahan
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
                            <div><label class="text-xs sm:text-sm text-[#1B1B1B] font-medium">Nomor Rekening</label>
                                <p class="text-sm sm:text-base text-[#27438D] break-words">
                                    {{ $user->nomor_rekening ?? '-' }}</p>
                            </div>
                            <div><label class="text-xs sm:text-sm text-[#1B1B1B] font-medium">Nama Bank</label>
                                <p class="text-sm sm:text-base text-[#27438D] break-words">{{ $user->nama_bank ?? 'BSI' }}
                                </p>
                            </div>
                            <div>
                                <label class="text-xs sm:text-sm text-[#1B1B1B] font-medium">Foto Profil</label>
                                @if ($user->foto_profil)
                                    <img src="{{ Storage::url($user->foto_profil) }}" alt="Foto Profil"
                                        class="w-16 h-16 sm:w-20 sm:h-20 rounded-full object-cover mt-1 border">
                                @else
                                    <p class="text-sm sm:text-base text-[#27438D]">-</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
