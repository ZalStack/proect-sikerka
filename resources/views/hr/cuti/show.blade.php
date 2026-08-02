{{-- views/hr/cuti/show.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="flex min-h-screen">
    @include('layouts.sidebar')
    <div class="flex-1 transition-all duration-300 md:ml-64 pt-6">
        <div class="p-4 sm:p-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-3 sm:gap-4">
                <div>
                    <h1 class="text-xl sm:text-2xl font-bold font-['Montserrat'] text-[#161758]">Detail Pengajuan Cuti</h1>
                    <p class="text-sm sm:text-base text-[#27438D]">Informasi lengkap pengajuan cuti</p>
                </div>
                <div class="flex flex-wrap gap-2 w-full sm:w-auto">
                    <a href="{{ route('hr.cuti.edit-hr', $cuti->id) }}"
                       class="w-full sm:w-auto text-center bg-[#FCC626] text-[#1B1B1B] px-4 py-2 rounded-lg hover:bg-yellow-400 transition-colors text-sm">
                        <i class="fas fa-edit mr-1"></i> Edit
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

            <div class="bg-white rounded-lg shadow-md p-4 sm:p-6">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                    <!-- Karyawan -->
                    <div class="sm:col-span-2">
                        <div class="flex items-center gap-3 p-3 bg-[#F5F5F5] rounded-lg">
                            <div class="w-12 h-12 rounded-full bg-[#27438D] text-white flex items-center justify-center text-lg font-bold">
                                {{ strtoupper(substr($cuti->karyawan->nama_lengkap, 0, 2)) }}
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Karyawan</p>
                                <p class="text-base sm:text-lg font-semibold text-[#161758]">{{ $cuti->karyawan->nama_lengkap }}</p>
                            </div>
                            <div class="ml-auto">
                                <span class="px-3 py-1 rounded-full text-xs font-medium {{ $cuti->status_badge }}">
                                    {{ $cuti->status_label }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Jenis Cuti -->
                    <div>
                        <label class="text-xs sm:text-sm text-[#1B1B1B] font-medium">Jenis Cuti</label>
                        <p class="text-sm sm:text-base text-[#27438D] font-semibold break-words">{{ $cuti->jenis_cuti }}</p>
                    </div>

                    <!-- Tanggal Pengajuan -->
                    <div>
                        <label class="text-xs sm:text-sm text-[#1B1B1B] font-medium">Tanggal Pengajuan</label>
                        <p class="text-sm sm:text-base text-[#27438D]">{{ $cuti->tanggal_pengajuan ? $cuti->tanggal_pengajuan->format('d-m-Y H:i') : '-' }}</p>
                    </div>

                    <!-- Tanggal Mulai -->
                    <div>
                        <label class="text-xs sm:text-sm text-[#1B1B1B] font-medium">Tanggal Mulai</label>
                        <p class="text-sm sm:text-base text-[#27438D]">{{ $cuti->tanggal_mulai ? $cuti->tanggal_mulai->format('d-m-Y') : '-' }}</p>
                    </div>

                    <!-- Tanggal Selesai -->
                    <div>
                        <label class="text-xs sm:text-sm text-[#1B1B1B] font-medium">Tanggal Selesai</label>
                        <p class="text-sm sm:text-base text-[#27438D]">{{ $cuti->tanggal_selesai ? $cuti->tanggal_selesai->format('d-m-Y') : '-' }}</p>
                    </div>

                    <!-- Durasi -->
                    <div>
                        <label class="text-xs sm:text-sm text-[#1B1B1B] font-medium">Durasi</label>
                        <p class="text-sm sm:text-base text-[#27438D] font-semibold">{{ $cuti->durasi }} hari</p>
                    </div>

                    <!-- Sisa Cuti -->
                    <div>
                        <label class="text-xs sm:text-sm text-[#1B1B1B] font-medium">Sisa Cuti</label>
                        <p class="text-sm sm:text-base text-[#27438D] font-semibold {{ $cuti->sisa_cuti <= 3 ? 'text-[#ec1d1d]' : '' }}">
                            {{ $cuti->sisa_cuti }} hari
                        </p>
                    </div>

                    <!-- Jatah Cuti -->
                    <div>
                        <label class="text-xs sm:text-sm text-[#1B1B1B] font-medium">Jatah Cuti</label>
                        <p class="text-sm sm:text-base text-[#27438D]">{{ $cuti->jatah_cuti }} hari</p>
                    </div>

                    <!-- Cuti Digunakan -->
                    <div>
                        <label class="text-xs sm:text-sm text-[#1B1B1B] font-medium">Cuti Digunakan</label>
                        <p class="text-sm sm:text-base text-[#27438D]">{{ $cuti->cuti_digunakan }} hari</p>
                    </div>

                    <!-- Keterangan -->
                    <div class="sm:col-span-2">
                        <label class="text-xs sm:text-sm text-[#1B1B1B] font-medium">Keterangan</label>
                        <p class="text-sm sm:text-base text-[#27438D] break-words p-3 bg-[#F5F5F5] rounded-lg">{{ $cuti->keterangan ?? '-' }}</p>
                    </div>

                    <!-- Catatan HR -->
                    @if($cuti->catatan_hr)
                    <div class="sm:col-span-2">
                        <label class="text-xs sm:text-sm text-[#1B1B1B] font-medium">Catatan HR</label>
                        <p class="text-sm sm:text-base text-[#27438D] break-words p-3 bg-[#F5F5F5] rounded-lg">{{ $cuti->catatan_hr }}</p>
                    </div>
                    @endif
                </div>

                @if($cuti->status === 'pending')
                <div class="mt-6 pt-6 border-t border-gray-200">
                    <h3 class="text-base sm:text-lg font-semibold text-[#161758] mb-4">Aksi</h3>
                    <div class="flex flex-wrap gap-3 sm:gap-4">
                        <form action="{{ route('hr.cuti.approve', $cuti->id) }}" method="POST" class="inline">
                            @csrf
                            <input type="hidden" name="status" value="approved">
                            <button type="submit" class="w-full sm:w-auto bg-[#2E7D3E] text-white px-4 sm:px-6 py-2 rounded-lg hover:bg-green-700 transition-colors text-sm sm:text-base">
                                <i class="fas fa-check mr-1"></i> Setujui
                            </button>
                        </form>
                        <form action="{{ route('hr.cuti.approve', $cuti->id) }}" method="POST" class="inline">
                            @csrf
                            <input type="hidden" name="status" value="rejected">
                            <input type="hidden" name="catatan_hr" value="Pengajuan cuti ditolak">
                            <button type="submit" class="w-full sm:w-auto bg-[#ec1d1d] text-white px-4 sm:px-6 py-2 rounded-lg hover:bg-red-700 transition-colors text-sm sm:text-base">
                                <i class="fas fa-times mr-1"></i> Tolak
                            </button>
                        </form>
                    </div>
                </div>
                @endif

                <!-- Informasi Tambahan -->
                <div class="mt-6 pt-6 border-t border-gray-200">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs text-gray-500">
                        <div>
                            <span class="font-medium">Dibuat:</span>
                            {{ $cuti->created_at ? $cuti->created_at->format('d-m-Y H:i:s') : '-' }}
                        </div>
                        <div>
                            <span class="font-medium">Terakhir Diupdate:</span>
                            {{ $cuti->updated_at ? $cuti->updated_at->format('d-m-Y H:i:s') : '-' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
