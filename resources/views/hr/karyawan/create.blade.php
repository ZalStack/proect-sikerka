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

                <!-- KOLOM KIRI -->
                <div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-[#1B1B1B] mb-1">ID Pegawai <span class="text-[#ec1d1d]">*</span></label>
                        <input type="text" name="kode_pegawai" value="{{ old('kode_pegawai') }}" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                        @error('kode_pegawai') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Nama Lengkap <span class="text-[#ec1d1d]">*</span></label>
                        <input type="text" name="nama_lengkap" value="{{ old('nama_lengkap') }}" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                        @error('nama_lengkap') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Tempat Lahir</label>
                        <input type="text" name="tempat_lahir" value="{{ old('tempat_lahir') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                        @error('tempat_lahir') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Tanggal Lahir</label>
                        <input type="date" name="tanggal_lahir" value="{{ old('tanggal_lahir') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                        @error('tanggal_lahir') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Jenis Kelamin</label>
                        <select name="jenis_kelamin"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                            <option value="">Pilih</option>
                            <option value="Laki-laki" {{ old('jenis_kelamin') === 'Laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                            <option value="Perempuan" {{ old('jenis_kelamin') === 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
                        </select>
                        @error('jenis_kelamin') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Nama Ibu Kandung</label>
                        <input type="text" name="nama_ibu_kandung" value="{{ old('nama_ibu_kandung') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                        @error('nama_ibu_kandung') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-[#1B1B1B] mb-1">NIK</label>
                        <input type="text" name="nik" value="{{ old('nik') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                        @error('nik') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-[#1B1B1B] mb-1">No KK</label>
                        <input type="text" name="no_kk" value="{{ old('no_kk') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                        @error('no_kk') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                    </div>
                </div>

                <!-- KOLOM KANAN -->
                <div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Status Pernikahan</label>
                        <select name="status_pernikahan"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                            <option value="">Pilih</option>
                            <option value="Belum Menikah" {{ old('status_pernikahan') === 'Belum Menikah' ? 'selected' : '' }}>Belum Menikah</option>
                            <option value="Menikah" {{ old('status_pernikahan') === 'Menikah' ? 'selected' : '' }}>Menikah</option>
                        </select>
                        @error('status_pernikahan') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Jumlah Anak</label>
                        <input type="number" name="jumlah_anak" value="{{ old('jumlah_anak', 0) }}" min="0"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                        @error('jumlah_anak') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Email <span class="text-[#ec1d1d]">*</span></label>
                        <input type="email" name="email" value="{{ old('email') }}" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                        @error('email') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Password <span class="text-[#ec1d1d]">*</span></label>
                        <input type="password" name="password" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                        @error('password') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Konfirmasi Password <span class="text-[#ec1d1d]">*</span></label>
                        <input type="password" name="password_confirmation" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                        @error('password_confirmation') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Golongan Darah</label>
                        <select name="golongan_darah"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                            <option value="">Pilih</option>
                            <option value="A" {{ old('golongan_darah') === 'A' ? 'selected' : '' }}>A</option>
                            <option value="B" {{ old('golongan_darah') === 'B' ? 'selected' : '' }}>B</option>
                            <option value="AB" {{ old('golongan_darah') === 'AB' ? 'selected' : '' }}>AB</option>
                            <option value="O" {{ old('golongan_darah') === 'O' ? 'selected' : '' }}>O</option>
                        </select>
                        @error('golongan_darah') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                    </div>
                </div>

                <!-- Informasi Profesional -->
                <div class="md:col-span-2">
                    <h3 class="text-lg font-semibold text-[#161758] border-b border-gray-200 pb-2 mb-4 mt-4">Informasi Profesional</h3>
                </div>

                <!-- KOLOM KIRI -->
                <div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Jabatan <span class="text-[#ec1d1d]">*</span></label>
                        <input type="text" name="jabatan" value="{{ old('jabatan') }}" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                        @error('jabatan') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Divisi <span class="text-[#ec1d1d]">*</span></label>
                        <select name="divisi" id="divisi_select" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                            <option value="">Pilih Divisi</option>
                            <option value="HRD" {{ old('divisi') === 'HRD' ? 'selected' : '' }}>HRD</option>
                            <option value="IT" {{ old('divisi') === 'IT' ? 'selected' : '' }}>IT</option>
                            <option value="KPD" {{ old('divisi') === 'KPD' ? 'selected' : '' }}>KPD</option>
                            <option value="LPS" {{ old('divisi') === 'LPS' ? 'selected' : '' }}>LPS</option>
                            <option value="MEDIA" {{ old('divisi') === 'MEDIA' ? 'selected' : '' }}>MEDIA</option>
                            <option value="PENDIDIKAN" {{ old('divisi') === 'PENDIDIKAN' ? 'selected' : '' }}>PENDIDIKAN</option>
                            <option value="PKA" {{ old('divisi') === 'PKA' ? 'selected' : '' }}>PKA</option>
                            <option value="RG" {{ old('divisi') === 'RG' ? 'selected' : '' }}>RG</option>
                            <option value="SAPRAS" {{ old('divisi') === 'SAPRAS' ? 'selected' : '' }}>SAPRAS</option>
                        </select>
                        <p class="text-xs text-[#27438D] mt-1">* Posisi akan otomatis ditentukan berdasarkan divisi (HRD = HR, lainnya = Karyawan)</p>
                        @error('divisi') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Posisi</label>
                        <input type="text" id="posisi_display" value="Karyawan" disabled
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-100">
                        <input type="hidden" name="posisi" id="posisi_hidden" value="karyawan">
                        <p class="text-xs text-[#27438D] mt-1">* Posisi ditentukan otomatis berdasarkan divisi</p>
                    </div>
                </div>

                <!-- KOLOM KANAN -->
                <div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Status <span class="text-[#ec1d1d]">*</span></label>
                        <select name="status" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                            <option value="Full-time" {{ old('status') === 'Full-time' ? 'selected' : '' }}>Full-time</option>
                            <option value="Contract" {{ old('status') === 'Contract' ? 'selected' : '' }}>Contract</option>
                            <option value="Internship" {{ old('status') === 'Internship' ? 'selected' : '' }}>Internship</option>
                        </select>
                        @error('status') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Tanggal Bergabung <span class="text-[#ec1d1d]">*</span></label>
                        <input type="date" name="tanggal_bergabung" value="{{ old('tanggal_bergabung') }}" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                        @error('tanggal_bergabung') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-[#1B1B1B] mb-1">NPWP</label>
                        <input type="text" name="npwp" value="{{ old('npwp') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                        @error('npwp') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                    </div>
                </div>

                <!-- Pendidikan -->
                <div class="md:col-span-2">
                    <h3 class="text-lg font-semibold text-[#161758] border-b border-gray-200 pb-2 mb-4 mt-4">Pendidikan</h3>
                </div>

                <!-- KOLOM KIRI -->
                <div>
                    <div class="mb-4">
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
                        @error('pendidikan_terakhir_new') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Sedang Melanjutkan Pendidikan</label>
                        <input type="text" name="sedang_melanjutkan_pendidikan" value="{{ old('sedang_melanjutkan_pendidikan') }}" placeholder="Contoh: S2 Manajemen"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                        @error('sedang_melanjutkan_pendidikan') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                    </div>
                </div>

                <!-- KOLOM KANAN -->
                <div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Perguruan Tinggi</label>
                        <input type="text" name="perguruan_tinggi" value="{{ old('perguruan_tinggi') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                        @error('perguruan_tinggi') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Jurusan</label>
                        <input type="text" name="jurusan" value="{{ old('jurusan') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                        @error('jurusan') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Tahun Lulus</label>
                        <input type="number" name="tahun_lulus" value="{{ old('tahun_lulus') }}" min="1900" max="{{ date('Y') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                        @error('tahun_lulus') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                    </div>
                </div>

                <!-- Kontak & Alamat -->
                <div class="md:col-span-2">
                    <h3 class="text-lg font-semibold text-[#161758] border-b border-gray-200 pb-2 mb-4 mt-4">Kontak & Alamat</h3>
                </div>

                <!-- KOLOM KIRI -->
                <div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Nomor Telepon</label>
                        <input type="text" name="nomor_telepon" value="{{ old('nomor_telepon') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                        @error('nomor_telepon') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-[#1B1B1B] mb-1">No WA</label>
                        <input type="text" name="no_wa" value="{{ old('no_wa') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                        @error('no_wa') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Nama Kontak Darurat</label>
                        <input type="text" name="nama_kontak_darurat" value="{{ old('nama_kontak_darurat') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                        @error('nama_kontak_darurat') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                    </div>
                </div>

                <!-- KOLOM KANAN -->
                <div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Alamat</label>
                        <textarea name="alamat" rows="3"
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">{{ old('alamat') }}</textarea>
                        @error('alamat') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Telepon Kontak Darurat</label>
                        <input type="text" name="telepon_kontak_darurat" value="{{ old('telepon_kontak_darurat') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                        @error('telepon_kontak_darurat') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Foto Profil</label>
                        <input type="file" name="foto_profil" accept="image/*"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                        @error('foto_profil') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                    </div>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const divisiSelect = document.getElementById('divisi_select');
    const posisiDisplay = document.getElementById('posisi_display');
    const posisiHidden = document.getElementById('posisi_hidden');

    function updatePosisi() {
        const divisi = divisiSelect.value;
        if (divisi === 'HRD') {
            posisiDisplay.value = 'HR';
            posisiHidden.value = 'hr';
        } else {
            posisiDisplay.value = 'Karyawan';
            posisiHidden.value = 'karyawan';
        }
    }

    divisiSelect.addEventListener('change', updatePosisi);
    updatePosisi();
});
</script>
@endsection
