@extends('layouts.app')

@section('content')
<div class="flex">
    @include('layouts.sidebar')
    <div class="flex-1 p-6">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
            <div>
                <h1 class="text-2xl font-bold font-['Montserrat'] text-[#161758]">Cuti Saya</h1>
                <p class="text-[#27438D]">Kelola pengajuan cuti Anda</p>
            </div>
            <a href="{{ route('karyawan.cuti.create') }}"
               class="bg-[#27438D] text-white px-4 py-2 rounded-lg hover:bg-[#161758] transition-colors whitespace-nowrap">
                + Ajukan Cuti
            </a>
        </div>

        @if(session('success'))
            <div class="bg-[#2E7D3E] text-white p-4 rounded-lg mb-4">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-[#ec1d1d] text-white p-4 rounded-lg mb-4">
                {{ session('error') }}
            </div>
        @endif

        <!-- Sisa Cuti -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow-md p-4 text-center">
                <p class="text-sm text-[#1B1B1B]">Jatah Cuti Tahunan</p>
                <p class="text-2xl font-bold text-[#161758]">{{ $cutiTahunan->jatah_cuti ?? 12 }} hari</p>
            </div>
            <div class="bg-[#2E7D3E] text-white rounded-lg shadow-md p-4 text-center">
                <p class="text-sm">Cuti Digunakan</p>
                <p class="text-2xl font-bold">{{ $cutiTahunan->cuti_digunakan ?? 0 }} hari</p>
            </div>
            <div class="bg-[#FCC626] text-[#1B1B1B] rounded-lg shadow-md p-4 text-center">
                <p class="text-sm">Sisa Cuti</p>
                <p class="text-2xl font-bold">{{ $cutiTahunan->sisa_cuti ?? 0 }} hari</p>
            </div>
        </div>

        <!-- Riwayat Cuti -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="p-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-[#161758]">Riwayat Pengajuan Cuti</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[600px]">
                    <thead class="bg-[#F5F5F5]">
                        <tr>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-[#1B1B1B]">Tanggal</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-[#1B1B1B]">Durasi</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-[#1B1B1B]">Keterangan</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-[#1B1B1B]">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($cuti as $item)
                        <tr class="border-b border-gray-200 hover:bg-[#F5F5F5]">
                            <td class="px-4 py-3 text-sm">
                                {{ $item->tanggal_mulai ? $item->tanggal_mulai->format('d/m/Y') : '-' }}
                                <span class="text-xs text-gray-500">→</span>
                                {{ $item->tanggal_selesai ? $item->tanggal_selesai->format('d/m/Y') : '-' }}
                            </td>
                            <td class="px-4 py-3 text-sm font-semibold">{{ $item->durasi }} hari</td>
                            <td class="px-4 py-3 text-sm">{{ $item->keterangan ?? '-' }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-1 rounded-full text-xs font-medium {{ $item->status_badge }}">
                                    {{ $item->status_label }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-4 py-8 text-center text-[#1B1B1B]">
                                <div class="flex flex-col items-center">
                                    <svg class="w-16 h-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                    <p class="text-lg font-semibold">Belum ada pengajuan cuti</p>
                                    <p class="text-sm text-gray-500 mt-1">Klik tombol "Ajukan Cuti" untuk mengajukan</p>
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
@endsection
