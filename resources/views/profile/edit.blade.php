@extends('layouts.app')

@section('content')
    <div class="flex">
        @include('layouts.sidebar')
        <div class="ml-64 flex-1 p-6">
            <div class="mb-6">
                <h1 class="text-2xl font-bold font-['Montserrat'] text-[#161758]">Profile Saya</h1>
                <p class="text-[#27438D]">Detail informasi profil Anda</p>
            </div>

            @if (session('success'))
                <div class="bg-[#2E7D3E] text-white p-4 rounded-lg mb-4">
                    {{ session('success') }}
                </div>
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

            <!-- Detail Profile -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
                <div class="p-6">
                    <!-- Profile Header -->
                    <div class="flex flex-col md:flex-row items-center md:items-start space-y-4 md:space-y-0 md:space-x-6 mb-6">
                        <div class="flex-shrink-0">
                            @if($user->foto_profil)
                                <img src="{{ Storage::url($user->foto_profil) }}" alt="Foto" class="w-32 h-32 rounded-full object-cover border-4 border-[#00a2e9]">
                            @else
                                <div class="w-32 h-32 rounded-full bg-[#00a2e9] flex items-center justify-center text-white text-4xl font-bold">
                                    {{ strtoupper(substr($user->nama_lengkap, 0, 1)) }}
                                </div>
                            @endif
                        </div>
                        <div class="flex-1 text-center md:text-left">
                            <h2 class="text-2xl font-bold text-[#161758]">{{ $user->nama_lengkap }}</h2>
                            <p class="text-[#27438D]">{{ $user->jabatan }}</p>
                            <div class="flex flex-wrap items-center gap-2 mt-2 justify-center md:justify-start">
                                <span class="inline-block px-3 py-1 rounded-full text-sm font-medium {{ $user->status_badge }}">
                                    {{ $user->status_label }}
                                </span>
                                <span class="inline-block px-3 py-1 rounded-full text-sm font-medium {{ $user->posisi === 'hr' ? 'bg-[#27438D] text-white' : 'bg-[#00a2e9] text-white' }}">
                                    {{ $user->posisi === 'hr' ? 'HR' : 'Karyawan' }}
                                </span>
                                <span class="inline-block px-3 py-1 rounded-full text-sm font-medium bg-[#F5F5F5] text-[#1B1B1B]">
                                    {{ $user->divisi ?? '-' }}
                                </span>
                            </div>
                        </div>
                        <div class="flex-shrink-0">
                            <div class="text-sm text-gray-500">
                                <p>Bergabung: {{ $user->tanggal_bergabung ? $user->tanggal_bergabung->format('d-m-Y') : '-' }}</p>
                                <p>ID Karyawan: {{ $user->kode_pegawai }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                        <!-- Informasi Pribadi -->
                        <div class="md:col-span-2">
                            <h3 class="text-lg font-semibold text-[#161758] border-b border-gray-200 pb-2">Informasi Pribadi</h3>
                        </div>

                        <!-- KOLOM KIRI -->
                        <div class="space-y-4">
                            <div>
                                <label class="text-sm text-[#1B1B1B] font-medium">ID Karyawan</label>
                                <p class="text-[#27438D] font-semibold">{{ $user->kode_pegawai }}</p>
                            </div>
                            <div>
                                <label class="text-sm text-[#1B1B1B] font-medium">Nama Lengkap</label>
                                <p class="text-[#27438D]">{{ $user->nama_lengkap }}</p>
                            </div>
                            <div>
                                <label class="text-sm text-[#1B1B1B] font-medium">Tempat Lahir</label>
                                <p class="text-[#27438D]">{{ $user->tempat_lahir ?? '-' }}</p>
                            </div>
                            <div>
                                <label class="text-sm text-[#1B1B1B] font-medium">Tanggal Lahir</label>
                                <p class="text-[#27438D]">{{ $user->tanggal_lahir ? $user->tanggal_lahir->format('d-m-Y') : '-' }}</p>
                            </div>
                            <div>
                                <label class="text-sm text-[#1B1B1B] font-medium">Jenis Kelamin</label>
                                <p class="text-[#27438D]">{{ $user->jenis_kelamin ?? '-' }}</p>
                            </div>
                            <div>
                                <label class="text-sm text-[#1B1B1B] font-medium">Nama Ibu Kandung</label>
                                <p class="text-[#27438D]">{{ $user->nama_ibu_kandung ?? '-' }}</p>
                            </div>
                            <div>
                                <label class="text-sm text-[#1B1B1B] font-medium">NIK</label>
                                <p class="text-[#27438D]">{{ $user->nik ?? '-' }}</p>
                            </div>
                            <div>
                                <label class="text-sm text-[#1B1B1B] font-medium">No KK</label>
                                <p class="text-[#27438D]">{{ $user->no_kk ?? '-' }}</p>
                            </div>
                            <div>
                                <label class="text-sm text-[#1B1B1B] font-medium">Agama</label>
                                <p class="text-[#27438D]">{{ $user->agama ?? '-' }}</p>
                            </div>
                        </div>

                        <!-- KOLOM KANAN -->
                        <div class="space-y-4">
                            <div>
                                <label class="text-sm text-[#1B1B1B] font-medium">Status Pernikahan</label>
                                <p class="text-[#27438D]">{{ $user->status_pernikahan ?? '-' }}</p>
                            </div>
                            <div>
                                <label class="text-sm text-[#1B1B1B] font-medium">Jumlah Anak</label>
                                <p class="text-[#27438D]">{{ $user->jumlah_anak ?? 0 }}</p>
                            </div>
                            <div>
                                <label class="text-sm text-[#1B1B1B] font-medium">Email</label>
                                <p class="text-[#27438D]">{{ $user->email }}</p>
                            </div>
                            <div>
                                <label class="text-sm text-[#1B1B1B] font-medium">Golongan Darah</label>
                                <p class="text-[#27438D]">{{ $user->golongan_darah ?? '-' }}</p>
                            </div>
                            <div>
                                <label class="text-sm text-[#1B1B1B] font-medium">NPWP</label>
                                <p class="text-[#27438D]">{{ $user->npwp ?? '-' }}</p>
                            </div>
                            <div>
                                <label class="text-sm text-[#1B1B1B] font-medium">Status Karyawan</label>
                                <p class="text-[#27438D]">
                                    <span class="px-2 py-1 rounded-full text-xs font-medium {{ $user->status_badge }}">
                                        {{ $user->status_label }}
                                    </span>
                                </p>
                            </div>
                        </div>

                        <!-- Informasi Profesional -->
                        <div class="md:col-span-2">
                            <h3 class="text-lg font-semibold text-[#161758] border-b border-gray-200 pb-2 mt-4">Informasi Profesional</h3>
                        </div>

                        <!-- KOLOM KIRI -->
                        <div class="space-y-4">
                            <div>
                                <label class="text-sm text-[#1B1B1B] font-medium">Jabatan</label>
                                <p class="text-[#27438D]">{{ $user->jabatan }}</p>
                            </div>
                            <div>
                                <label class="text-sm text-[#1B1B1B] font-medium">Divisi</label>
                                <p class="text-[#27438D]">{{ $user->divisi ?? '-' }}</p>
                            </div>
                            <div>
                                <label class="text-sm text-[#1B1B1B] font-medium">Posisi</label>
                                <p class="text-[#27438D]">
                                    <span class="px-2 py-1 rounded-full text-xs font-medium {{ $user->posisi === 'hr' ? 'bg-[#27438D] text-white' : 'bg-[#00a2e9] text-white' }}">
                                        {{ $user->posisi === 'hr' ? 'HR' : 'Karyawan' }}
                                    </span>
                                </p>
                            </div>
                        </div>

                        <!-- KOLOM KANAN -->
                        <div class="space-y-4">
                            <div>
                                <label class="text-sm text-[#1B1B1B] font-medium">Tanggal Bergabung</label>
                                <p class="text-[#27438D]">{{ $user->tanggal_bergabung ? $user->tanggal_bergabung->format('d-m-Y') : '-' }}</p>
                            </div>
                            @if($user->end_date)
                            <div>
                                <label class="text-sm text-[#1B1B1B] font-medium">Tanggal Berakhir</label>
                                <p class="text-[#27438D]">{{ $user->end_date->format('d-m-Y') }}</p>
                            </div>
                            @endif
                        </div>

                        <!-- Pendidikan -->
                        <div class="md:col-span-2">
                            <h3 class="text-lg font-semibold text-[#161758] border-b border-gray-200 pb-2 mt-4">Pendidikan</h3>
                        </div>

                        <!-- KOLOM KIRI -->
                        <div class="space-y-4">
                            <div>
                                <label class="text-sm text-[#1B1B1B] font-medium">Pendidikan Terakhir</label>
                                <p class="text-[#27438D]">{{ $user->pendidikan_terakhir_new ?? $user->pendidikan_terakhir ?? '-' }}</p>
                            </div>
                            <div>
                                <label class="text-sm text-[#1B1B1B] font-medium">Sedang Melanjutkan Pendidikan</label>
                                <p class="text-[#27438D]">{{ $user->sedang_melanjutkan_pendidikan ?? '-' }}</p>
                            </div>
                            <div>
                                <label class="text-sm text-[#1B1B1B] font-medium">IPK Terakhir</label>
                                <p class="text-[#27438D]">{{ $user->ipk_terakhir ?? '-' }}</p>
                            </div>
                        </div>

                        <!-- KOLOM KANAN -->
                        <div class="space-y-4">
                            <div>
                                <label class="text-sm text-[#1B1B1B] font-medium">Perguruan Tinggi</label>
                                <p class="text-[#27438D]">{{ $user->perguruan_tinggi ?? '-' }}</p>
                            </div>
                            <div>
                                <label class="text-sm text-[#1B1B1B] font-medium">Jurusan</label>
                                <p class="text-[#27438D]">{{ $user->jurusan ?? '-' }}</p>
                            </div>
                            <div>
                                <label class="text-sm text-[#1B1B1B] font-medium">Tahun Lulus</label>
                                <p class="text-[#27438D]">{{ $user->tahun_lulus ?? '-' }}</p>
                            </div>
                        </div>

                        <!-- Kontak & Alamat -->
                        <div class="md:col-span-2">
                            <h3 class="text-lg font-semibold text-[#161758] border-b border-gray-200 pb-2 mt-4">Kontak & Alamat</h3>
                        </div>

                        <!-- KOLOM KIRI -->
                        <div class="space-y-4">
                            <div>
                                <label class="text-sm text-[#1B1B1B] font-medium">Nomor Telepon</label>
                                <p class="text-[#27438D]">{{ $user->nomor_telepon ?? '-' }}</p>
                            </div>
                            <div>
                                <label class="text-sm text-[#1B1B1B] font-medium">No WA</label>
                                <p class="text-[#27438D]">{{ $user->no_wa ?? '-' }}</p>
                            </div>
                            <div>
                                <label class="text-sm text-[#1B1B1B] font-medium">Nama Kontak Darurat</label>
                                <p class="text-[#27438D]">{{ $user->nama_kontak_darurat ?? '-' }}</p>
                            </div>
                            <div>
                                <label class="text-sm text-[#1B1B1B] font-medium">Telepon Kontak Darurat</label>
                                <p class="text-[#27438D]">{{ $user->telepon_kontak_darurat ?? '-' }}</p>
                            </div>
                        </div>

                        <!-- KOLOM KANAN -->
                        <div class="space-y-4">
                            <div>
                                <label class="text-sm text-[#1B1B1B] font-medium">Alamat</label>
                                <p class="text-[#27438D]">{{ $user->alamat ?? '-' }}</p>
                            </div>
                            <div>
                                <label class="text-sm text-[#1B1B1B] font-medium">Alamat Domisili</label>
                                <p class="text-[#27438D]">{{ $user->alamat_domisili ?? '-' }}</p>
                            </div>
                        </div>

                        <!-- Informasi Tambahan -->
                        <div class="md:col-span-2">
                            <h3 class="text-lg font-semibold text-[#161758] border-b border-gray-200 pb-2 mt-4">Informasi Tambahan</h3>
                        </div>

                        <!-- KOLOM KIRI -->
                        <div class="space-y-4">
                            <div>
                                <label class="text-sm text-[#1B1B1B] font-medium">Tanggal Pengangkatan Tetap</label>
                                <p class="text-[#27438D]">{{ $user->tanggal_pengangkatan_tetap ? $user->tanggal_pengangkatan_tetap->format('d-m-Y') : '-' }}</p>
                            </div>
                            <div>
                                <label class="text-sm text-[#1B1B1B] font-medium">Nomor Rekening</label>
                                <p class="text-[#27438D]">{{ $user->nomor_rekening ?? '-' }}</p>
                            </div>
                        </div>

                        <!-- KOLOM KANAN -->
                        <div class="space-y-4">
                            <div>
                                <label class="text-sm text-[#1B1B1B] font-medium">Nama Bank</label>
                                <p class="text-[#27438D]">{{ $user->nama_bank ?? 'BSI' }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Tombol Edit Profile -->
                    <div class="mt-6 flex justify-end">
                        <button onclick="toggleEditForm()"
                                class="bg-[#27438D] text-white px-6 py-2 rounded-lg hover:bg-[#161758] transition-colors duration-200">
                            <i class="fas fa-edit mr-2"></i> Edit Profile
                        </button>
                    </div>
                </div>
            </div>

            <!-- Form Edit Profile (disembunyikan default) -->
            <div id="editProfileForm" style="display: none;">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-bold text-[#161758] mb-4">Edit Profile</h2>

                    <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Informasi Pribadi -->
                            <div class="md:col-span-2">
                                <h3 class="text-lg font-semibold text-[#161758] border-b border-gray-200 pb-2 mb-4">Informasi Pribadi</h3>
                            </div>

                            <!-- KOLOM KIRI -->
                            <div>
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-[#1B1B1B] mb-1">ID Karyawan <span class="text-[#ec1d1d]">*</span></label>
                                    <input type="text" name="kode_pegawai" value="{{ old('kode_pegawai', $user->kode_pegawai) }}" required
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                                    @error('kode_pegawai') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                                </div>

                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Nama Lengkap <span class="text-[#ec1d1d]">*</span></label>
                                    <input type="text" name="nama_lengkap" value="{{ old('nama_lengkap', $user->nama_lengkap) }}" required
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                                    @error('nama_lengkap') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                                </div>

                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Tempat Lahir</label>
                                    <input type="text" name="tempat_lahir" value="{{ old('tempat_lahir', $user->tempat_lahir) }}"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                                    @error('tempat_lahir') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                                </div>

                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Tanggal Lahir</label>
                                    <input type="date" name="tanggal_lahir" value="{{ old('tanggal_lahir', $user->tanggal_lahir ? $user->tanggal_lahir->format('Y-m-d') : '') }}"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                                    @error('tanggal_lahir') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
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
                                    <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Nama Ibu Kandung</label>
                                    <input type="text" name="nama_ibu_kandung" value="{{ old('nama_ibu_kandung', $user->nama_ibu_kandung) }}"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                                    @error('nama_ibu_kandung') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                                </div>

                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-[#1B1B1B] mb-1">NIK</label>
                                    <input type="text" name="nik" value="{{ old('nik', $user->nik) }}"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                                    @error('nik') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                                </div>

                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-[#1B1B1B] mb-1">No KK</label>
                                    <input type="text" name="no_kk" value="{{ old('no_kk', $user->no_kk) }}"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                                    @error('no_kk') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                                </div>

                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Agama</label>
                                    <input type="text" name="agama" value="{{ old('agama', $user->agama) }}"
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
                                        <option value="Belum Menikah" {{ old('status_pernikahan', $user->status_pernikahan) === 'Belum Menikah' ? 'selected' : '' }}>Belum Menikah</option>
                                        <option value="Menikah" {{ old('status_pernikahan', $user->status_pernikahan) === 'Menikah' ? 'selected' : '' }}>Menikah</option>
                                        <option value="Cerai" {{ old('status_pernikahan', $user->status_pernikahan) === 'Cerai' ? 'selected' : '' }}>Cerai</option>
                                    </select>
                                    @error('status_pernikahan') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                                </div>

                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Jumlah Anak</label>
                                    <input type="number" name="jumlah_anak" value="{{ old('jumlah_anak', $user->jumlah_anak ?? 0) }}" min="0"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                                    @error('jumlah_anak') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                                </div>

                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Email <span class="text-[#ec1d1d]">*</span></label>
                                    <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                                    @error('email') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
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

                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-[#1B1B1B] mb-1">NPWP</label>
                                    <input type="text" name="npwp" value="{{ old('npwp', $user->npwp) }}"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                                    @error('npwp') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            <!-- Informasi Profesional -->
                            <div class="md:col-span-2">
                                <h3 class="text-lg font-semibold text-[#161758] border-b border-gray-200 pb-2 mb-4 mt-4">Informasi Profesional</h3>
                            </div>

                            @if ($isHr)
                                <div>
                                    <div class="mb-4">
                                        <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Jabatan <span class="text-[#ec1d1d]">*</span></label>
                                        <input type="text" name="jabatan" value="{{ old('jabatan', $user->jabatan) }}" required
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                                        @error('jabatan') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                                    </div>

                                    <div class="mb-4">
                                        <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Divisi</label>
                                        <select name="divisi" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
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

                                <div>
                                    <div class="mb-4">
                                        <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Tanggal Bergabung <span class="text-[#ec1d1d]">*</span></label>
                                        <input type="date" name="tanggal_bergabung" value="{{ old('tanggal_bergabung', $user->tanggal_bergabung ? $user->tanggal_bergabung->format('Y-m-d') : '') }}" required
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                                        @error('tanggal_bergabung') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                                    </div>
                                </div>
                            @else
                                <div>
                                    <div class="mb-4">
                                        <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Jabatan</label>
                                        <input type="text" value="{{ $user->jabatan }}" disabled class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-100">
                                    </div>
                                    <div class="mb-4">
                                        <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Divisi</label>
                                        <input type="text" value="{{ $user->divisi ?? '-' }}" disabled class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-100">
                                    </div>
                                    <div class="mb-4">
                                        <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Status</label>
                                        <input type="text" value="{{ $user->status }}" disabled class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-100">
                                    </div>
                                </div>
                                <div>
                                    <div class="mb-4">
                                        <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Tanggal Bergabung</label>
                                        <input type="text" value="{{ $user->tanggal_bergabung ? $user->tanggal_bergabung->format('d-m-Y') : '-' }}" disabled class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-100">
                                    </div>
                                </div>
                            @endif

                            <!-- Pendidikan -->
                            <div class="md:col-span-2">
                                <h3 class="text-lg font-semibold text-[#161758] border-b border-gray-200 pb-2 mb-4 mt-4">Pendidikan</h3>
                            </div>

                            <div>
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Pendidikan Terakhir</label>
                                    <select name="pendidikan_terakhir_new" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
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
                                    @error('pendidikan_terakhir_new') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                                </div>

                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Sedang Melanjutkan Pendidikan</label>
                                    <input type="text" name="sedang_melanjutkan_pendidikan" value="{{ old('sedang_melanjutkan_pendidikan', $user->sedang_melanjutkan_pendidikan) }}"
                                        placeholder="Contoh: S2 Manajemen"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                                    @error('sedang_melanjutkan_pendidikan') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                                </div>

                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-[#1B1B1B] mb-1">IPK Terakhir</label>
                                    <input type="number" name="ipk_terakhir" value="{{ old('ipk_terakhir', $user->ipk_terakhir) }}" step="0.01" min="0" max="4"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                                    @error('ipk_terakhir') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            <div>
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Perguruan Tinggi</label>
                                    <input type="text" name="perguruan_tinggi" value="{{ old('perguruan_tinggi', $user->perguruan_tinggi) }}"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                                    @error('perguruan_tinggi') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                                </div>

                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Jurusan</label>
                                    <input type="text" name="jurusan" value="{{ old('jurusan', $user->jurusan) }}"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                                    @error('jurusan') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                                </div>

                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Tahun Lulus</label>
                                    <input type="number" name="tahun_lulus" value="{{ old('tahun_lulus', $user->tahun_lulus) }}" min="1900" max="{{ date('Y') }}"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                                    @error('tahun_lulus') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            <!-- Kontak & Alamat -->
                            <div class="md:col-span-2">
                                <h3 class="text-lg font-semibold text-[#161758] border-b border-gray-200 pb-2 mb-4 mt-4">Kontak & Alamat</h3>
                            </div>

                            <div>
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Nomor Telepon</label>
                                    <input type="text" name="nomor_telepon" value="{{ old('nomor_telepon', $user->nomor_telepon) }}"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                                    @error('nomor_telepon') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                                </div>

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

                            <div>
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Alamat</label>
                                    <textarea name="alamat" rows="3"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">{{ old('alamat', $user->alamat) }}</textarea>
                                    @error('alamat') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                                </div>

                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Alamat Domisili</label>
                                    <textarea name="alamat_domisili" rows="3"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">{{ old('alamat_domisili', $user->alamat_domisili) }}</textarea>
                                    @error('alamat_domisili') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                                </div>

                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Telepon Kontak Darurat</label>
                                    <input type="text" name="telepon_kontak_darurat" value="{{ old('telepon_kontak_darurat', $user->telepon_kontak_darurat) }}"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                                    @error('telepon_kontak_darurat') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            <!-- Informasi Tambahan -->
                            <div class="md:col-span-2">
                                <h3 class="text-lg font-semibold text-[#161758] border-b border-gray-200 pb-2 mb-4 mt-4">Informasi Tambahan</h3>
                            </div>

                            <div>
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Tanggal Pengangkatan Tetap</label>
                                    @if ($isHr)
                                        <input type="date" name="tanggal_pengangkatan_tetap"
                                            value="{{ old('tanggal_pengangkatan_tetap', $user->tanggal_pengangkatan_tetap ? $user->tanggal_pengangkatan_tetap->format('Y-m-d') : '') }}"
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                                        @error('tanggal_pengangkatan_tetap') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                                    @else
                                        <input type="text" readonly disabled
                                            value="{{ $user->tanggal_pengangkatan_tetap ? $user->tanggal_pengangkatan_tetap->format('d-m-Y') : '-' }}"
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-100 text-gray-500 cursor-not-allowed">
                                        <p class="mt-1 text-xs text-gray-400">Hanya HR yang dapat mengubah data ini</p>
                                    @endif
                                </div>

                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Nomor Rekening</label>
                                    <input type="text" name="nomor_rekening" value="{{ old('nomor_rekening', $user->nomor_rekening) }}"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                                    @error('nomor_rekening') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            <div>
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Nama Bank</label>
                                    <input type="text" value="BSI" disabled
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-100">
                                    <input type="hidden" name="nama_bank" value="BSI">
                                    <p class="text-xs text-[#27438D] mt-1">* Nama bank default BSI</p>
                                </div>

                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Foto Profil</label>
                                    <input type="file" name="foto_profil" accept="image/*"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                                    @if ($user->foto_profil)
                                        <div class="mt-2">
                                            <img src="{{ Storage::url($user->foto_profil) }}" alt="Foto Profil" class="w-20 h-20 rounded-full object-cover">
                                        </div>
                                    @endif
                                    @error('foto_profil') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mt-6 flex space-x-4">
                            <button type="submit"
                                class="bg-[#27438D] text-white px-6 py-2 rounded-lg hover:bg-[#161758] transition-colors duration-200">
                                Update Profile
                            </button>
                            <button type="button" onclick="toggleEditForm()"
                                class="bg-gray-500 text-white px-6 py-2 rounded-lg hover:bg-gray-600 transition-colors duration-200">
                                Batal
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Ubah Password -->
            <div class="bg-white rounded-lg shadow-md p-6 mt-6">
                <h2 class="text-lg font-semibold text-[#161758] mb-4">Ubah Password</h2>
                <form action="{{ route('profile.update-password') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Password Saat Ini <span class="text-[#ec1d1d]">*</span></label>
                            <input type="password" name="current_password" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                            @error('current_password') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Password Baru <span class="text-[#ec1d1d]">*</span></label>
                            <input type="password" name="password" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                            @error('password') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Konfirmasi Password Baru <span class="text-[#ec1d1d]">*</span></label>
                            <input type="password" name="password_confirmation" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                            @error('password_confirmation') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
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
</div>

<script>
function toggleEditForm() {
    const form = document.getElementById('editProfileForm');
    if (form.style.display === 'none') {
        form.style.display = 'block';
        form.scrollIntoView({ behavior: 'smooth', block: 'start' });
    } else {
        form.style.display = 'none';
    }
}
</script>
@endsection
