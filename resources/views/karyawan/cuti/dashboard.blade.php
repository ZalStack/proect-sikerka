{{-- views/karyawan/cuti/dashboard.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="flex min-h-screen">
    @include('layouts.sidebar')
    <div class="flex-1 transition-all duration-300 md:ml-64 pt-6">
        <div class="p-3 sm:p-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-3 sm:gap-4">
                <div>
                    <h1 class="text-xl sm:text-2xl font-bold font-['Montserrat'] text-[#161758]">Cuti Saya</h1>
                    <p class="text-sm sm:text-base text-[#27438D]">Kelola pengajuan cuti Anda</p>
                </div>
                <a href="{{ route('karyawan.cuti.create') }}"
                   class="w-full sm:w-auto text-center bg-[#27438D] text-white px-4 py-2 rounded-lg hover:bg-[#161758] transition-colors whitespace-nowrap text-sm sm:text-base">
                    + Ajukan Cuti
                </a>
            </div>

            @if(session('success'))
                <div class="bg-[#2E7D3E] text-white p-3 sm:p-4 rounded-lg mb-4 text-sm">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="bg-[#ec1d1d] text-white p-3 sm:p-4 rounded-lg mb-4 text-sm">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Sisa Cuti -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-6 mb-6">
                <div class="bg-white rounded-lg shadow-md p-3 sm:p-6 text-center border-l-4 border-[#00a2e9]">
                    <p class="text-xs sm:text-sm text-[#1B1B1B]">Jatah Cuti Tahunan</p>
                    <p class="text-xl sm:text-2xl font-bold text-[#161758]">{{ $cutiTahunan->jatah_cuti ?? 12 }} hari</p>
                </div>
                <div class="bg-white rounded-lg shadow-md p-3 sm:p-6 text-center border-l-4 border-[#2E7D3E]">
                    <p class="text-xs sm:text-sm text-[#1B1B1B]">Cuti Digunakan</p>
                    <p class="text-xl sm:text-2xl font-bold text-[#161758]">{{ $cutiTahunan->cuti_digunakan ?? 0 }} hari</p>
                </div>
                <div class="bg-white rounded-lg shadow-md p-3 sm:p-6 text-center border-l-4 border-[#FCC626]">
                    <p class="text-xs sm:text-sm text-[#1B1B1B]">Sisa Cuti</p>
                    <p class="text-xl sm:text-2xl font-bold text-[#161758]">{{ $cutiTahunan->sisa_cuti ?? 0 }} hari</p>
                </div>
            </div>

            <!-- Riwayat Cuti -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="p-3 sm:p-4 border-b border-gray-200">
                    <h2 class="text-base sm:text-lg font-semibold text-[#161758]">Riwayat Pengajuan Cuti</h2>
                </div>
                <div class="overflow-x-auto -mx-4 sm:mx-0">
                    <div class="inline-block min-w-full align-middle">
                        <table class="min-w-[500px] sm:min-w-full">
                            <thead class="bg-[#F5F5F5]">
                                <tr>
                                    <th class="px-3 sm:px-4 py-2 sm:py-3 text-left text-xs sm:text-sm font-semibold text-[#1B1B1B]">Tanggal</th>
                                    <th class="px-3 sm:px-4 py-2 sm:py-3 text-left text-xs sm:text-sm font-semibold text-[#1B1B1B]">Durasi</th>
                                    <th class="px-3 sm:px-4 py-2 sm:py-3 text-left text-xs sm:text-sm font-semibold text-[#1B1B1B] hidden sm:table-cell">Keterangan</th>
                                    <th class="px-3 sm:px-4 py-2 sm:py-3 text-left text-xs sm:text-sm font-semibold text-[#1B1B1B]">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($cuti as $item)
                                <tr class="border-b border-gray-200 hover:bg-[#F5F5F5]">
                                    <td class="px-3 sm:px-4 py-2 sm:py-3 text-xs sm:text-sm">
                                        {{ $item->tanggal_mulai ? $item->tanggal_mulai->format('d/m/Y') : '-' }}
                                        <span class="text-[10px] sm:text-xs text-gray-500">→</span>
                                        {{ $item->tanggal_selesai ? $item->tanggal_selesai->format('d/m/Y') : '-' }}
                                    </td>
                                    <td class="px-3 sm:px-4 py-2 sm:py-3 text-xs sm:text-sm font-semibold">{{ $item->durasi }} hari</td>
                                    <td class="px-3 sm:px-4 py-2 sm:py-3 text-xs sm:text-sm hidden sm:table-cell">{{ $item->keterangan ?? '-' }}</td>
                                    <td class="px-3 sm:px-4 py-2 sm:py-3">
                                        <span class="px-2 py-1 rounded-full text-[10px] sm:text-xs font-medium {{ $item->status_badge }}">
                                            {{ $item->status_label }}
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-8 text-center text-[#1B1B1B]">
                                        <div class="flex flex-col items-center">
                                            <svg class="w-12 sm:w-16 h-12 sm:h-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                            </svg>
                                            <p class="text-base sm:text-lg font-semibold">Belum ada pengajuan cuti</p>
                                            <p class="text-xs sm:text-sm text-gray-500 mt-1">Klik tombol "Ajukan Cuti" untuk mengajukan</p>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
