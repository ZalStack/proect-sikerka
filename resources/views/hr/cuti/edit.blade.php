{{-- views/hr/cuti/edit.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="flex min-h-screen">
    @include('layouts.sidebar')
    <div class="flex-1 transition-all duration-300 md:ml-64 pt-6">
        <div class="p-3 sm:p-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-3 sm:gap-4">
                <div>
                    <h1 class="text-xl sm:text-2xl font-bold font-['Montserrat'] text-[#161758]">Edit Data Cuti</h1>
                    <p class="text-sm sm:text-base text-[#27438D]">Perbarui data cuti karyawan</p>
                </div>
                <div class="flex flex-wrap gap-2 w-full sm:w-auto">
                    <a href="{{ route('hr.cuti.show', $cuti->id) }}"
                       class="w-full sm:w-auto text-center bg-[#00a2e9] text-white px-4 py-2 rounded-lg hover:bg-blue-600 transition-colors text-sm">
                        <i class="fas fa-eye mr-1"></i> Detail
                    </a>
                    <a href="{{ route('hr.cuti.index') }}"
                       class="w-full sm:w-auto text-center bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600 transition-colors text-sm">
                        <i class="fas fa-arrow-left mr-1"></i> Kembali
                    </a>
                </div>
            </div>

            @if(session('success'))
                <div class="bg-[#2E7D3E] text-white p-3 sm:p-4 rounded-lg mb-4 text-sm">
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="bg-[#ec1d1d] text-white p-3 sm:p-4 rounded-lg mb-4 text-sm">
                    <ul class="list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white rounded-lg shadow-md p-4 sm:p-6">
                <form action="{{ route('hr.cuti.update-hr', $cuti->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Karyawan -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Karyawan <span class="text-[#ec1d1d]">*</span></label>
                            <select name="karyawan_id" required
                                    class="w-full px-3 sm:px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9] text-sm">
                                <option value="">Pilih Karyawan</option>
                                @foreach($karyawans as $karyawan)
                                    <option value="{{ $karyawan->id }}"
                                        {{ old('karyawan_id', $cuti->karyawan_id) == $karyawan->id ? 'selected' : '' }}>
                                        {{ $karyawan->nama_lengkap }}
                                    </option>
                                @endforeach
                            </select>
                            @error('karyawan_id') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                        </div>

                        <!-- Status -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Status <span class="text-[#ec1d1d]">*</span></label>
                            <select name="status" required
                                    class="w-full px-3 sm:px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9] text-sm">
                                <option value="pending" {{ old('status', $cuti->status) == 'pending' ? 'selected' : '' }}>Menunggu</option>
                                <option value="approved" {{ old('status', $cuti->status) == 'approved' ? 'selected' : '' }}>Disetujui</option>
                                <option value="rejected" {{ old('status', $cuti->status) == 'rejected' ? 'selected' : '' }}>Ditolak</option>
                            </select>
                            @error('status') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                        </div>

                        <!-- Jenis Cuti -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Jenis Cuti <span class="text-[#ec1d1d]">*</span></label>
                            <select name="jenis_cuti" required
                                    class="w-full px-3 sm:px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9] text-sm">
                                <option value="Cuti Tahunan" {{ old('jenis_cuti', $cuti->jenis_cuti) == 'Cuti Tahunan' ? 'selected' : '' }}>Cuti Tahunan</option>
                                <option value="Cuti Sakit" {{ old('jenis_cuti', $cuti->jenis_cuti) == 'Cuti Sakit' ? 'selected' : '' }}>Cuti Sakit</option>
                                <option value="Cuti Izin" {{ old('jenis_cuti', $cuti->jenis_cuti) == 'Cuti Izin' ? 'selected' : '' }}>Cuti Izin</option>
                                <option value="Cuti Khusus" {{ old('jenis_cuti', $cuti->jenis_cuti) == 'Cuti Khusus' ? 'selected' : '' }}>Cuti Khusus</option>
                            </select>
                            @error('jenis_cuti') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                        </div>

                        <!-- Jatah Cuti -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Jatah Cuti (hari) <span class="text-[#ec1d1d]">*</span></label>
                            <input type="number" name="jatah_cuti" value="{{ old('jatah_cuti', $cuti->jatah_cuti) }}" required min="0"
                                   class="w-full px-3 sm:px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9] text-sm">
                            @error('jatah_cuti') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                        </div>

                        <!-- Sisa Cuti -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Sisa Cuti (hari) <span class="text-[#ec1d1d]">*</span></label>
                            <input type="number" name="sisa_cuti" value="{{ old('sisa_cuti', $cuti->sisa_cuti) }}" required min="0"
                                   class="w-full px-3 sm:px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9] text-sm">
                            @error('sisa_cuti') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                        </div>

                        <!-- Cuti Digunakan -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Cuti Digunakan (hari) <span class="text-[#ec1d1d]">*</span></label>
                            <input type="number" name="cuti_digunakan" value="{{ old('cuti_digunakan', $cuti->cuti_digunakan) }}" required min="0"
                                   class="w-full px-3 sm:px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9] text-sm">
                            @error('cuti_digunakan') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                        </div>

                        <!-- Tanggal Mulai -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Tanggal Mulai</label>
                            <input type="date" name="tanggal_mulai" value="{{ old('tanggal_mulai', $cuti->tanggal_mulai ? $cuti->tanggal_mulai->format('Y-m-d') : '') }}"
                                   class="w-full px-3 sm:px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9] text-sm">
                            @error('tanggal_mulai') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                        </div>

                        <!-- Tanggal Selesai -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Tanggal Selesai</label>
                            <input type="date" name="tanggal_selesai" value="{{ old('tanggal_selesai', $cuti->tanggal_selesai ? $cuti->tanggal_selesai->format('Y-m-d') : '') }}"
                                   class="w-full px-3 sm:px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9] text-sm">
                            @error('tanggal_selesai') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                        </div>

                        <!-- Keterangan -->
                        <div class="mb-4 sm:col-span-2">
                            <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Keterangan</label>
                            <textarea name="keterangan" rows="3"
                                      class="w-full px-3 sm:px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9] text-sm">{{ old('keterangan', $cuti->keterangan) }}</textarea>
                            @error('keterangan') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                        </div>

                        <!-- Catatan HR -->
                        <div class="mb-4 sm:col-span-2">
                            <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Catatan HR</label>
                            <textarea name="catatan_hr" rows="3"
                                      class="w-full px-3 sm:px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9] text-sm">{{ old('catatan_hr', $cuti->catatan_hr) }}</textarea>
                            @error('catatan_hr') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <!-- Informasi -->
                    <div class="mb-6">
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 sm:p-4">
                            <p class="text-xs sm:text-sm text-blue-800">
                                <span class="font-semibold">ℹ️ Informasi:</span>
                                Mengubah data cuti akan mempengaruhi perhitungan sisa cuti karyawan.
                                Pastikan data yang dimasukkan sudah benar.
                            </p>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-3 sm:gap-4">
                        <button type="submit"
                                class="w-full sm:w-auto bg-[#27438D] text-white px-6 py-2 rounded-lg hover:bg-[#161758] transition-colors text-sm sm:text-base">
                            <i class="fas fa-save mr-1"></i> Simpan Perubahan
                        </button>
                        <a href="{{ route('hr.cuti.index') }}"
                           class="w-full sm:w-auto text-center bg-gray-500 text-white px-6 py-2 rounded-lg hover:bg-gray-600 transition-colors text-sm sm:text-base">
                            <i class="fas fa-times mr-1"></i> Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
