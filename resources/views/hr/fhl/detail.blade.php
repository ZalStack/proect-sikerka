{{-- views/hr/fhl/detail.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="flex min-h-screen">
    @include('layouts.sidebar')
    <div class="flex-1 transition-all duration-300 md:ml-64 pt-6">
        <div class="p-4 sm:p-6">
            <div class="mb-6">
                <h1 class="text-xl sm:text-2xl font-bold font-['Montserrat'] text-[#161758]">Detail Absensi FHL</h1>
                <p class="text-sm sm:text-base text-[#27438D]">Informasi lengkap absensi FHL</p>
            </div>

            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="p-4 sm:p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
                        <div>
                            <h3 class="text-base sm:text-lg font-semibold text-[#161758] border-b border-gray-200 pb-2 mb-4">Informasi Karyawan</h3>
                            <div class="space-y-2">
                                <div>
                                    <label class="text-xs sm:text-sm text-[#1B1B1B] font-medium">Nama Lengkap</label>
                                    <p class="text-sm sm:text-base text-[#27438D] break-words">{{ $absensi->karyawan->nama_lengkap }}</p>
                                </div>
                                <div>
                                    <label class="text-xs sm:text-sm text-[#1B1B1B] font-medium">Kode Pegawai</label>
                                    <p class="text-sm sm:text-base text-[#27438D] break-words">{{ $absensi->karyawan->kode_pegawai }}</p>
                                </div>
                                <div>
                                    <label class="text-xs sm:text-sm text-[#1B1B1B] font-medium">Jabatan</label>
                                    <p class="text-sm sm:text-base text-[#27438D] break-words">{{ $absensi->karyawan->jabatan }}</p>
                                </div>
                                <div>
                                    <label class="text-xs sm:text-sm text-[#1B1B1B] font-medium">Divisi</label>
                                    <p class="text-sm sm:text-base text-[#27438D] break-words">{{ $absensi->karyawan->divisi ?? '-' }}</p>
                                </div>
                            </div>
                        </div>

                        <div>
                            <h3 class="text-base sm:text-lg font-semibold text-[#161758] border-b border-gray-200 pb-2 mb-4">Detail Absensi</h3>
                            <div class="space-y-2">
                                <div>
                                    <label class="text-xs sm:text-sm text-[#1B1B1B] font-medium">Tanggal</label>
                                    <p class="text-sm sm:text-base text-[#27438D]">{{ $absensi->tanggal->format('d-m-Y') }}</p>
                                </div>
                                <div>
                                    <label class="text-xs sm:text-sm text-[#1B1B1B] font-medium">Hari</label>
                                    <p class="text-sm sm:text-base text-[#27438D]">Jumat</p>
                                </div>
                                <div>
                                    <label class="text-xs sm:text-sm text-[#1B1B1B] font-medium">Check-in</label>
                                    <p class="text-sm sm:text-base text-[#27438D]">{{ $absensi->check_in ? Carbon\Carbon::parse($absensi->check_in)->format('H:i:s') : '-' }}</p>
                                </div>
                                <div>
                                    <label class="text-xs sm:text-sm text-[#1B1B1B] font-medium">Status</label>
                                    <p class="text-sm sm:text-base text-[#27438D]">
                                        <span class="px-2 py-1 rounded-full text-xs font-medium bg-[#2E7D3E] text-white">
                                            {{ $absensi->status }}
                                        </span>
                                    </p>
                                </div>
                                <div>
                                    <label class="text-xs sm:text-sm text-[#1B1B1B] font-medium">IP Address</label>
                                    <p class="text-sm sm:text-base text-[#27438D] break-words">{{ $absensi->ip_address ?? '-' }}</p>
                                </div>
                                @if($absensi->keterangan)
                                <div>
                                    <label class="text-xs sm:text-sm text-[#1B1B1B] font-medium">Keterangan</label>
                                    <p class="text-sm sm:text-base text-[#27438D] break-words">{{ $absensi->keterangan }}</p>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Bukti Foto -->
                    @if($absensi->foto_bukti)
                    <div class="mt-6 pt-6 border-t border-gray-200">
                        <h3 class="text-base sm:text-lg font-semibold text-[#161758] mb-4">📷 Bukti Foto</h3>
                        <div class="bg-[#F5F5F5] rounded-lg p-4 inline-block w-full sm:w-auto">
                            <img src="{{ Storage::url($absensi->foto_bukti) }}" alt="Bukti FHL"
                                 class="w-full sm:max-w-full max-h-96 object-contain rounded-lg">
                        </div>
                    </div>
                    @endif

                    <div class="mt-6">
                        <a href="{{ route('hr.fhl.index') }}"
                           class="inline-block w-full sm:w-auto text-center bg-gray-500 text-white px-4 sm:px-6 py-2 rounded-lg hover:bg-gray-600 transition-colors duration-200 text-sm sm:text-base">
                            Kembali
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
