@extends('layouts.app')

@section('content')
<div class="flex">
    @include('layouts.sidebar')
    <div class="ml-64 flex-1 p-6">
        <div class="mb-6">
            <h1 class="text-2xl font-bold font-['Montserrat'] text-[#161758]">Dashboard Karyawan</h1>
            <p class="text-[#27438D]">Selamat datang, {{ $user->nama_lengkap }}</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-[#00a2e9]">
                <p class="text-sm text-[#1B1B1B]">Kode Pegawai</p>
                <p class="text-2xl font-bold text-[#161758]">{{ $user->kode_pegawai ?? '-' }}</p>
            </div>
            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-[#2E7D3E]">
                <p class="text-sm text-[#1B1B1B]">Status</p>
                <p class="text-2xl font-bold text-[#161758]">
                    <span class="px-2 py-1 rounded-full text-xs font-medium {{ $user->status === 'Karyawan Tetap' ? 'bg-[#2E7D3E] text-white' : ($user->status === 'Contract' ? 'bg-[#FCC626] text-[#1B1B1B]' : 'bg-[#00a2e9] text-white') }}">
                        {{ $user->status }}
                    </span>
                </p>
            </div>
            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-[#FCC626]">
                <p class="text-sm text-[#1B1B1B]">Jabatan</p>
                <p class="text-2xl font-bold text-[#161758]">{{ $user->jabatan ?? '-' }}</p>
            </div>
        </div>

        <div class="mt-6 bg-white rounded-lg shadow-md p-6">
            <h2 class="text-lg font-semibold text-[#161758] mb-4">Informasi Pribadi</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <p class="text-sm text-[#1B1B1B]"><strong>Kode Pegawai:</strong> {{ $user->kode_pegawai ?? '-' }}</p>
                    <p class="text-sm text-[#1B1B1B]"><strong>Email:</strong> {{ $user->email ?? '-' }}</p>
                    <p class="text-sm text-[#1B1B1B]"><strong>NIK:</strong> {{ $user->nik ?? '-' }}</p>
                    <p class="text-sm text-[#1B1B1B]"><strong>Divisi:</strong> {{ $user->divisi ?? '-' }}</p>
                    <p class="text-sm text-[#1B1B1B]"><strong>Golongan Darah:</strong> {{ $user->golongan_darah ?? '-' }}</p>
                    <p class="text-sm text-[#1B1B1B]"><strong>Jumlah Anak:</strong> {{ $user->jumlah_anak ?? 0 }}</p>
                    <p class="text-sm text-[#1B1B1B]"><strong>Status:</strong>
                        <span class="px-2 py-1 rounded-full text-xs font-medium {{ $user->status === 'Karyawan Tetap' ? 'bg-[#2E7D3E] text-white' : ($user->status === 'Contract' ? 'bg-[#FCC626] text-[#1B1B1B]' : 'bg-[#00a2e9] text-white') }}">
                            {{ $user->status }}
                        </span>
                    </p>
                </div>
                <div>
                    <p class="text-sm text-[#1B1B1B]"><strong>Tanggal Bergabung:</strong> {{ $user->tanggal_bergabung ? $user->tanggal_bergabung->format('d-m-Y') : '-' }}</p>
                    <p class="text-sm text-[#1B1B1B]"><strong>Tempat Lahir:</strong> {{ $user->tempat_lahir ?? '-' }}</p>
                    <p class="text-sm text-[#1B1B1B]"><strong>Tanggal Lahir:</strong> {{ $user->tanggal_lahir ? $user->tanggal_lahir->format('d-m-Y') : '-' }}</p>
                    <p class="text-sm text-[#1B1B1B]"><strong>Posisi:</strong> {{ $user->posisi === 'hr' ? 'HR' : 'Karyawan' }}</p>
                    <p class="text-sm text-[#1B1B1B]"><strong>No WA:</strong> {{ $user->no_wa ?? '-' }}</p>
                    <p class="text-sm text-[#1B1B1B]"><strong>Status Pernikahan:</strong> {{ $user->status_pernikahan ?? '-' }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
