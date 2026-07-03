@extends('layouts.app')

@section('content')
<div class="flex">
    @include('layouts.sidebar')
    <div class="ml-64 flex-1 p-6">
        <div class="mb-6">
            <h1 class="text-2xl font-bold font-['Montserrat'] text-[#161758]">Dashboard HR</h1>
            <p class="text-[#27438D]">Selamat datang di dashboard HR</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-[#00a2e9]">
                <p class="text-sm text-[#1B1B1B]">Total Karyawan</p>
                <p class="text-2xl font-bold text-[#161758]">{{ $totalKaryawan ?? 0 }}</p>
            </div>
            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-[#FCC626]">
                <p class="text-sm text-[#1B1B1B]">Total HR</p>
                <p class="text-2xl font-bold text-[#161758]">{{ $totalHr ?? 0 }}</p>
            </div>
            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-[#2E7D3E]">
                <p class="text-sm text-[#1B1B1B]">Karyawan Aktif</p>
                <p class="text-2xl font-bold text-[#161758]">{{ $totalKaryawanAktif ?? 0 }}</p>
            </div>
            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-[#ec1d1d]">
                <p class="text-sm text-[#1B1B1B]">Total Hari Kerja</p>
                <p class="text-2xl font-bold text-[#161758]">{{ isset($karyawanTerbaru) ? $karyawanTerbaru->sum('total_hari_kerja') : 0 }}</p>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-lg font-semibold text-[#161758] mb-4">Karyawan Terbaru</h2>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-[#F5F5F5]">
                            <th class="px-4 py-2 text-left text-sm font-semibold text-[#1B1B1B]">Nama</th>
                            <th class="px-4 py-2 text-left text-sm font-semibold text-[#1B1B1B]">NIP</th>
                            <th class="px-4 py-2 text-left text-sm font-semibold text-[#1B1B1B]">Jabatan</th>
                            <th class="px-4 py-2 text-left text-sm font-semibold text-[#1B1B1B]">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse(($karyawanTerbaru ?? []) as $karyawan)
                        <tr class="border-b border-gray-200">
                            <td class="px-4 py-2 text-sm">{{ $karyawan->nama_lengkap ?? '-' }}</td>
                            <td class="px-4 py-2 text-sm">{{ $karyawan->nip ?? '-' }}</td>
                            <td class="px-4 py-2 text-sm">{{ $karyawan->jabatan ?? '-' }}</td>
                            <td class="px-4 py-2 text-sm">
                                <span class="px-2 py-1 rounded-full text-xs font-medium {{ ($karyawan->status ?? '') === 'Full-time' ? 'bg-[#2E7D3E] text-white' : 'bg-[#FCC626] text-[#1B1B1B]' }}">
                                    {{ $karyawan->status ?? '-' }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-4 py-4 text-center text-sm text-[#1B1B1B]">Belum ada data karyawan</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
