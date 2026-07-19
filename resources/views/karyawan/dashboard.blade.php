{{-- views/karyawan/dashboard.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="flex min-h-screen">
    @include('layouts.sidebar')
    <div class="flex-1 transition-all duration-300 md:ml-64 pt-6">
        <div class="p-4 sm:p-6">
            <div class="mb-6">
                <h1 class="text-xl sm:text-2xl font-bold font-['Montserrat'] text-[#161758]">Dashboard Karyawan</h1>
                <p class="text-sm sm:text-base text-[#27438D]">Selamat datang, {{ $user->nama_lengkap }}</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
                <div class="bg-white rounded-lg shadow-md p-4 sm:p-6 border-l-4 border-[#00a2e9]">
                    <p class="text-xs sm:text-sm text-[#1B1B1B]">ID Pegawai</p>
                    <p class="text-lg sm:text-2xl font-bold text-[#161758] break-words">{{ $user->kode_pegawai ?? '-' }}</p>
                </div>
                <div class="bg-white rounded-lg shadow-md p-4 sm:p-6 border-l-4 border-[#2E7D3E]">
                    <p class="text-xs sm:text-sm text-[#1B1B1B]">Status</p>
                    <p class="text-lg sm:text-2xl font-bold text-[#161758]">
                        <span class="px-2 py-1 rounded-full text-xs font-medium {{ $user->status === 'Karyawan Tetap' ? 'bg-[#2E7D3E] text-white' : ($user->status === 'Contract' ? 'bg-[#FCC626] text-[#1B1B1B]' : 'bg-[#00a2e9] text-white') }}">
                            {{ $user->status }}
                        </span>
                    </p>
                </div>
                <div class="bg-white rounded-lg shadow-md p-4 sm:p-6 border-l-4 border-[#FCC626]">
                    <p class="text-xs sm:text-sm text-[#1B1B1B]">Jabatan</p>
                    <p class="text-lg sm:text-2xl font-bold text-[#161758] break-words">{{ $user->jabatan ?? '-' }}</p>
                </div>
            </div>

            <div class="mt-6 bg-white rounded-lg shadow-md p-4 sm:p-6">
                <h2 class="text-base sm:text-lg font-semibold text-[#161758] mb-4">Informasi Pribadi</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                    <div class="space-y-2">
                        <p class="text-xs sm:text-sm text-[#1B1B1B]"><strong>ID Pegawai:</strong> <span class="break-words">{{ $user->kode_pegawai ?? '-' }}</span></p>
                        <p class="text-xs sm:text-sm text-[#1B1B1B]"><strong>Email:</strong> <span class="break-all">{{ $user->email ?? '-' }}</span></p>
                        <p class="text-xs sm:text-sm text-[#1B1B1B]"><strong>NIK:</strong> <span class="break-words">{{ $user->nik ?? '-' }}</span></p>
                        <p class="text-xs sm:text-sm text-[#1B1B1B]"><strong>Divisi:</strong> <span class="break-words">{{ $user->divisi ?? '-' }}</span></p>
                        <p class="text-xs sm:text-sm text-[#1B1B1B]"><strong>Golongan Darah:</strong> {{ $user->golongan_darah ?? '-' }}</p>
                        <p class="text-xs sm:text-sm text-[#1B1B1B]"><strong>Jumlah Anak:</strong> {{ $user->jumlah_anak ?? 0 }}</p>
                        <p class="text-xs sm:text-sm text-[#1B1B1B]"><strong>Status:</strong>
                            <span class="px-2 py-1 rounded-full text-xs font-medium {{ $user->status === 'Karyawan Tetap' ? 'bg-[#2E7D3E] text-white' : ($user->status === 'Contract' ? 'bg-[#FCC626] text-[#1B1B1B]' : 'bg-[#00a2e9] text-white') }}">
                                {{ $user->status }}
                            </span>
                        </p>
                    </div>
                    <div class="space-y-2">
                        <p class="text-xs sm:text-sm text-[#1B1B1B]"><strong>Tanggal Bergabung:</strong> {{ $user->tanggal_bergabung ? $user->tanggal_bergabung->format('d-m-Y') : '-' }}</p>
                        <p class="text-xs sm:text-sm text-[#1B1B1B]"><strong>Tempat Lahir:</strong> <span class="break-words">{{ $user->tempat_lahir ?? '-' }}</span></p>
                        <p class="text-xs sm:text-sm text-[#1B1B1B]"><strong>Tanggal Lahir:</strong> {{ $user->tanggal_lahir ? $user->tanggal_lahir->format('d-m-Y') : '-' }}</p>
                        <p class="text-xs sm:text-sm text-[#1B1B1B]"><strong>Posisi:</strong> {{ $user->posisi === 'hr' ? 'HR' : 'Karyawan' }}</p>
                        <p class="text-xs sm:text-sm text-[#1B1B1B]"><strong>No WA:</strong> <span class="break-words">{{ $user->no_wa ?? '-' }}</span></p>
                        <p class="text-xs sm:text-sm text-[#1B1B1B]"><strong>Status Pernikahan:</strong> {{ $user->status_pernikahan ?? '-' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
