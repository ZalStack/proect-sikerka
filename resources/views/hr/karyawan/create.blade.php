@extends('layouts.app')

@section('content')
<div class="flex">
    @include('layouts.sidebar')
    <div class="ml-64 flex-1 p-6">
        <div class="mb-6">
            <h1 class="text-2xl font-bold font-['Montserrat'] text-[#161758]">Tambah Karyawan</h1>
            <p class="text-[#27438D]">Isi form untuk menambahkan karyawan baru</p>
        </div>

        <form action="{{ route('hr.karyawan.store') }}" method="POST" enctype="multipart/form-data" class="bg-white rounded-lg shadow-md p-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Informasi Pribadi -->
                <div class="md:col-span-2">
                    <h3 class="text-lg font-semibold text-[#161758] border-b border-gray-200 pb-2 mb-4">Informasi Pribadi</h3>
                </div>

                <div>
                    <label class="block text-sm font-medium text-[#1B1B1B] mb-1">NIP *</label>
                    <input type="text" name="nip" value="{{ old('nip') }}" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                    @error('nip') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Email *</label>
                    <input type="email" name="email" value="{{ old('email') }}" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                    @error('email') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Password *</label>
                    <input type="password" name="password" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                    @error('password') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Konfirmasi Password *</label>
                    <input type="password" name="password_confirmation" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                </div>

                <div>
                    <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Nama Depan *</label>
                    <input type="text" name="nama_depan" value="{{ old('nama_depan') }}" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                    @error('nama_depan') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Nama Belakang *</label>
                    <input type="text" name="nama_belakang" value="{{ old('nama_belakang') }}" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                    @error('nama_belakang') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Nama Lengkap *</label>
                    <input type="text" name="nama_lengkap" value="{{ old('nama_lengkap') }}" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                    @error('nama_lengkap') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-[#1B1B1B] mb-1">NIK *</label>
                    <input type="text" name="nik" value="{{ old('nik') }}" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                    @error('nik') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-[#1B1B1B] mb-1">NPWP</label>
                    <input type="text" name="npwp" value="{{ old('npwp') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                </div>

                <div>
                    <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Tempat Lahir *</label>
                    <input type="text" name="tempat_lahir" value="{{ old('tempat_lahir') }}" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                    @error('tempat_lahir') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Tanggal Lahir *</label>
                    <input type="date" name="tanggal_lahir" value="{{ old('tanggal_lahir') }}" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                    @error('tanggal_lahir') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Jenis Kelamin *</label>
                    <select name="jenis_kelamin" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                        <option value="">Pilih</option>
                        <option value="Laki-laki" {{ old('jenis_kelamin') === 'Laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                        <option value="Perempuan" {{ old('jenis_kelamin') === 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
                    </select>
                    @error('jenis_kelamin') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Agama *</label>
                    <select name="agama" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                        <option value="">Pilih</option>
                        <option value="Islam" {{ old('agama') === 'Islam' ? 'selected' : '' }}>Islam</option>
                        <option value="Kristen" {{ old('agama') === 'Kristen' ? 'selected' : '' }}>Kristen</option>
                        <option value="Katolik" {{ old('agama') === 'Katolik' ? 'selected' : '' }}>Katolik</option>
                        <option value="Hindu" {{ old('agama') === 'Hindu' ? 'selected' : '' }}>Hindu</option>
                        <option value="Buddha" {{ old('agama') === 'Buddha' ? 'selected' : '' }}>Buddha</option>
                        <option value="Konghucu" {{ old('agama') === 'Konghucu' ? 'selected' : '' }}>Konghucu</option>
                    </select>
                    @error('agama') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Status Pernikahan *</label>
                    <select name="status_pernikahan" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                        <option value="">Pilih</option>
                        <option value="Belum Menikah" {{ old('status_pernikahan') === 'Belum Menikah' ? 'selected' : '' }}>Belum Menikah</option>
                        <option value="Menikah" {{ old('status_pernikahan') === 'Menikah' ? 'selected' : '' }}>Menikah</option>
                        <option value="Cerai" {{ old('status_pernikahan') === 'Cerai' ? 'selected' : '' }}>Cerai</option>
                    </select>
                    @error('status_pernikahan') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                </div>

                <!-- Informasi Profesional -->
                <div class="md:col-span-2">
                    <h3 class="text-lg font-semibold text-[#161758] border-b border-gray-200 pb-2 mb-4 mt-4">Informasi Profesional</h3>
                </div>

                <div>
                    <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Jabatan *</label>
                    <input type="text" name="jabatan" value="{{ old('jabatan') }}" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                    @error('jabatan') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Role</label>
                    <select name="role"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                        <option value="karyawan" {{ old('role') === 'karyawan' ? 'selected' : '' }}>Karyawan</option>
                        <option value="hr" {{ old('role') === 'hr' ? 'selected' : '' }}>HR</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Tanggal Bergabung *</label>
                    <input type="date" name="tanggal_bergabung" value="{{ old('tanggal_bergabung') }}" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                    @error('tanggal_bergabung') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Status *</label>
                    <select name="status" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                        <option value="Full-time" {{ old('status') === 'Full-time' ? 'selected' : '' }}>Full-time</option>
                        <option value="Contract" {{ old('status') === 'Contract' ? 'selected' : '' }}>Contract</option>
                        <option value="Internship" {{ old('status') === 'Internship' ? 'selected' : '' }}>Internship</option>
                    </select>
                    @error('status') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                </div>

                <!-- Pendidikan -->
                <div class="md:col-span-2">
                    <h3 class="text-lg font-semibold text-[#161758] border-b border-gray-200 pb-2 mb-4 mt-4">Pendidikan</h3>
                </div>

                <div>
                    <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Pendidikan Terakhir</label>
                    <select name="pendidikan_terakhir_new"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                        <option value="">Pilih</option>
                        <option value="SMP" {{ old('pendidikan_terakhir_new') === 'SMP' ? 'selected' : '' }}>SMP</option>
                        <option value="SMA/MA" {{ old('pendidikan_terakhir_new') === 'SMA/MA' ? 'selected' : '' }}>SMA/MA</option>
                        <option value="SMK" {{ old('pendidikan_terakhir_new') === 'SMK' ? 'selected' : '' }}>SMK</option>
                        <option value="D1" {{ old('pendidikan_terakhir_new') === 'D1' ? 'selected' : '' }}>D1</option>
                        <option value="D2" {{ old('pendidikan_terakhir_new') === 'D2' ? 'selected' : '' }}>D2</option>
                        <option value="D3" {{ old('pendidikan_terakhir_new') === 'D3' ? 'selected' : '' }}>D3</option>
                        <option value="D4" {{ old('pendidikan_terakhir_new') === 'D4' ? 'selected' : '' }}>D4</option>
                        <option value="S1" {{ old('pendidikan_terakhir_new') === 'S1' ? 'selected' : '' }}>S1</option>
                        <option value="S2" {{ old('pendidikan_terakhir_new') === 'S2' ? 'selected' : '' }}>S2</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Universitas</label>
                    <input type="text" name="universitas" value="{{ old('universitas') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                </div>

                <div>
                    <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Jurusan</label>
                    <input type="text" name="jurusan" value="{{ old('jurusan') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                </div>

                <div>
                    <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Tahun Lulus</label>
                    <input type="number" name="tahun_lulus" value="{{ old('tahun_lulus') }}" min="1900" max="{{ date('Y') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                </div>

                <!-- Kontak & Alamat -->
                <div class="md:col-span-2">
                    <h3 class="text-lg font-semibold text-[#161758] border-b border-gray-200 pb-2 mb-4 mt-4">Kontak & Alamat</h3>
                </div>

                <div>
                    <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Nomor Telepon</label>
                    <input type="text" name="nomor_telepon" value="{{ old('nomor_telepon') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                </div>

                <div>
                    <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Foto Profil</label>
                    <input type="file" name="foto_profil" accept="image/*"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Alamat</label>
                    <textarea name="alamat" rows="3"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">{{ old('alamat') }}</textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Nama Kontak Darurat</label>
                    <input type="text" name="nama_kontak_darurat" value="{{ old('nama_kontak_darurat') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                </div>

                <div>
                    <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Telepon Kontak Darurat</label>
                    <input type="text" name="telepon_kontak_darurat" value="{{ old('telepon_kontak_darurat') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                </div>
            </div>

            <div class="mt-6 flex space-x-4">
                <button type="submit"
                        class="bg-[#27438D] text-white px-6 py-2 rounded-lg hover:bg-[#161758] transition-colors duration-200">
                    Simpan
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
