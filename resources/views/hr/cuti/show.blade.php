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
                <a href="{{ route('hr.cuti.index') }}"
                   class="w-full sm:w-auto text-center bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600 transition-colors text-sm sm:text-base">
                    Kembali
                </a>
            </div>

            <div class="bg-white rounded-lg shadow-md p-4 sm:p-6">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                    <div>
                        <label class="text-xs sm:text-sm text-[#1B1B1B] font-medium">Karyawan</label>
                        <p class="text-sm sm:text-base text-[#27438D] font-semibold break-words">{{ $cuti->karyawan->nama_lengkap }}</p>
                    </div>
                    <div>
                        <label class="text-xs sm:text-sm text-[#1B1B1B] font-medium">Jenis Cuti</label>
                        <p class="text-sm sm:text-base text-[#27438D] break-words">{{ $cuti->jenis_cuti }}</p>
                    </div>
                    <div>
                        <label class="text-xs sm:text-sm text-[#1B1B1B] font-medium">Tanggal Mulai</label>
                        <p class="text-sm sm:text-base text-[#27438D]">{{ $cuti->tanggal_mulai ? $cuti->tanggal_mulai->format('d-m-Y') : '-' }}</p>
                    </div>
                    <div>
                        <label class="text-xs sm:text-sm text-[#1B1B1B] font-medium">Tanggal Selesai</label>
                        <p class="text-sm sm:text-base text-[#27438D]">{{ $cuti->tanggal_selesai ? $cuti->tanggal_selesai->format('d-m-Y') : '-' }}</p>
                    </div>
                    <div>
                        <label class="text-xs sm:text-sm text-[#1B1B1B] font-medium">Durasi</label>
                        <p class="text-sm sm:text-base text-[#27438D] font-semibold">{{ $cuti->durasi }} hari</p>
                    </div>
                    <div>
                        <label class="text-xs sm:text-sm text-[#1B1B1B] font-medium">Status</label>
                        <p>
                            <span class="px-2 py-1 rounded-full text-xs font-medium {{ $cuti->status_badge }}">
                                {{ $cuti->status_label }}
                            </span>
                        </p>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="text-xs sm:text-sm text-[#1B1B1B] font-medium">Keterangan</label>
                        <p class="text-sm sm:text-base text-[#27438D] break-words">{{ $cuti->keterangan ?? '-' }}</p>
                    </div>
                    @if($cuti->catatan_hr)
                    <div class="sm:col-span-2">
                        <label class="text-xs sm:text-sm text-[#1B1B1B] font-medium">Catatan HR</label>
                        <p class="text-sm sm:text-base text-[#27438D] break-words">{{ $cuti->catatan_hr }}</p>
                    </div>
                    @endif
                    <div>
                        <label class="text-xs sm:text-sm text-[#1B1B1B] font-medium">Tanggal Pengajuan</label>
                        <p class="text-sm sm:text-base text-[#27438D]">{{ $cuti->tanggal_pengajuan ? $cuti->tanggal_pengajuan->format('d-m-Y H:i') : '-' }}</p>
                    </div>
                    <div>
                        <label class="text-xs sm:text-sm text-[#1B1B1B] font-medium">Sisa Cuti</label>
                        <p class="text-sm sm:text-base text-[#27438D] font-semibold">{{ $cuti->sisa_cuti }} hari</p>
                    </div>
                </div>

                @if($cuti->status === 'pending')
                <div class="mt-6 pt-6 border-t border-gray-200">
                    <h3 class="text-base sm:text-lg font-semibold text-[#161758] mb-4">Aksi</h3>
                    <div class="flex flex-wrap gap-3 sm:gap-4">
                        <form action="{{ route('hr.cuti.approve', $cuti->id) }}" method="POST" class="inline">
                            @csrf
                            <input type="hidden" name="status" value="approved">
                            <button type="submit" class="w-full sm:w-auto bg-[#2E7D3E] text-white px-4 sm:px-6 py-2 rounded-lg hover:bg-green-700 transition-colors text-sm sm:text-base">
                                Setujui
                            </button>
                        </form>
                        <form action="{{ route('hr.cuti.approve', $cuti->id) }}" method="POST" class="inline">
                            @csrf
                            <input type="hidden" name="status" value="rejected">
                            <input type="hidden" name="catatan_hr" value="Pengajuan cuti ditolak">
                            <button type="submit" class="w-full sm:w-auto bg-[#ec1d1d] text-white px-4 sm:px-6 py-2 rounded-lg hover:bg-red-700 transition-colors text-sm sm:text-base">
                                Tolak
                            </button>
                        </form>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
