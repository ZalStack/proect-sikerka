{{-- views/karyawan/cuti/edit.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="flex min-h-screen">
    @include('layouts.sidebar')
    <div class="flex-1 transition-all duration-300 md:ml-64 pt-6">
        <div class="p-3 sm:p-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-3 sm:gap-4">
                <div>
                    <h1 class="text-xl sm:text-2xl font-bold font-['Montserrat'] text-[#161758]">Edit Pengajuan Cuti</h1>
                    <p class="text-sm sm:text-base text-[#27438D]">Perbarui pengajuan cuti Anda</p>
                </div>
                <a href="{{ route('karyawan.cuti.dashboard') }}"
                   class="w-full sm:w-auto text-center bg-gray-500 text-white px-4 sm:px-6 py-2 rounded-lg hover:bg-gray-600 transition-colors text-sm sm:text-base">
                    Kembali
                </a>
            </div>

            @if(session('error'))
                <div class="bg-[#ec1d1d] text-white p-3 sm:p-4 rounded-lg mb-4 text-sm">
                    {{ session('error') }}
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
                <form action="{{ route('karyawan.cuti.update', $cuti->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Status Cuti -->
                        <div class="mb-4 sm:col-span-2">
                            <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Status Pengajuan</label>
                            <div class="px-3 sm:px-4 py-2 border border-gray-300 rounded-lg bg-gray-100">
                                <span class="px-2 py-1 rounded-full text-xs font-medium {{ $cuti->status_badge }}">
                                    {{ $cuti->status_label }}
                                </span>
                                <span class="text-xs text-gray-500 ml-2">(Pengajuan yang sudah diajukan tidak dapat diubah statusnya)</span>
                            </div>
                        </div>

                        <!-- Jenis Cuti -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Jenis Cuti</label>
                            <input type="text" value="Cuti Tahunan" disabled
                                   class="w-full px-3 sm:px-4 py-2 border border-gray-300 rounded-lg bg-gray-100 text-sm">
                            <input type="hidden" name="jenis_cuti" value="Cuti Tahunan">
                        </div>

                        <!-- Durasi Sebelumnya -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Durasi Sebelumnya</label>
                            <div class="px-3 sm:px-4 py-2 border border-gray-300 rounded-lg bg-gray-100 text-sm">
                                <span class="font-semibold text-[#161758]">{{ $cuti->durasi }} hari</span>
                                <span class="text-xs text-gray-500 ml-2">({{ $cuti->tanggal_mulai->format('d/m/Y') }} - {{ $cuti->tanggal_selesai->format('d/m/Y') }})</span>
                            </div>
                        </div>

                        <!-- Sisa Cuti -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Sisa Cuti Saat Ini</label>
                            <div class="px-3 sm:px-4 py-2 border border-gray-300 rounded-lg bg-gray-100 text-sm">
                                <span class="font-semibold text-[#2E7D3E]">{{ $cuti->sisa_cuti }} hari</span>
                            </div>
                        </div>

                        <!-- Tanggal Mulai -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Tanggal Mulai <span class="text-[#ec1d1d]">*</span></label>
                            <input type="date" name="tanggal_mulai" value="{{ old('tanggal_mulai', $cuti->tanggal_mulai->format('Y-m-d')) }}" required
                                   min="{{ date('Y-m-d') }}"
                                   class="w-full px-3 sm:px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9] text-sm">
                            @error('tanggal_mulai') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                        </div>

                        <!-- Tanggal Selesai -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Tanggal Selesai <span class="text-[#ec1d1d]">*</span></label>
                            <input type="date" name="tanggal_selesai" value="{{ old('tanggal_selesai', $cuti->tanggal_selesai->format('Y-m-d')) }}" required
                                   min="{{ date('Y-m-d') }}"
                                   class="w-full px-3 sm:px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9] text-sm">
                            @error('tanggal_selesai') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                        </div>

                        <!-- Keterangan -->
                        <div class="mb-4 sm:col-span-2">
                            <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Keterangan <span class="text-[#ec1d1d]">*</span></label>
                            <textarea name="keterangan" rows="4" required
                                      class="w-full px-3 sm:px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9] text-sm">{{ old('keterangan', $cuti->keterangan) }}</textarea>
                            @error('keterangan') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                        </div>

                        <!-- Informasi -->
                        <div class="mb-4 sm:col-span-2">
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 sm:p-4">
                                <p class="text-xs sm:text-sm text-blue-800">
                                    <span class="font-semibold">ℹ️ Informasi:</span>
                                    Anda hanya dapat mengedit pengajuan yang masih berstatus
                                    <span class="font-semibold">"Menunggu"</span>.
                                    Jika mengubah durasi cuti, sisa cuti akan disesuaikan secara otomatis.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 flex flex-wrap gap-3 sm:gap-4">
                        <button type="submit"
                                class="w-full sm:w-auto bg-[#27438D] text-white px-6 py-2 rounded-lg hover:bg-[#161758] transition-colors text-sm sm:text-base">
                            Simpan Perubahan
                        </button>
                        <a href="{{ route('karyawan.cuti.dashboard') }}"
                           class="w-full sm:w-auto text-center bg-gray-500 text-white px-6 py-2 rounded-lg hover:bg-gray-600 transition-colors text-sm sm:text-base">
                            Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
