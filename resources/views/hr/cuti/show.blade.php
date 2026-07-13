@extends('layouts.app')

@section('content')
<div class="flex">
    @include('layouts.sidebar')
    <div class="flex-1 p-6">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold font-['Montserrat'] text-[#161758]">Detail Pengajuan Cuti</h1>
                <p class="text-[#27438D]">Informasi lengkap pengajuan cuti</p>
            </div>
            <div class="flex space-x-2">
                <a href="{{ route('hr.cuti.index') }}"
                   class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600 transition-colors">
                    Kembali
                </a>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="text-sm text-[#1B1B1B] font-medium">Karyawan</label>
                    <p class="text-[#27438D] font-semibold">{{ $cuti->karyawan->nama_lengkap }}</p>
                </div>
                <div>
                    <label class="text-sm text-[#1B1B1B] font-medium">Jenis Cuti</label>
                    <p class="text-[#27438D]">{{ $cuti->jenis_cuti }}</p>
                </div>
                <div>
                    <label class="text-sm text-[#1B1B1B] font-medium">Tanggal Mulai</label>
                    <p class="text-[#27438D]">{{ $cuti->tanggal_mulai ? $cuti->tanggal_mulai->format('d-m-Y') : '-' }}</p>
                </div>
                <div>
                    <label class="text-sm text-[#1B1B1B] font-medium">Tanggal Selesai</label>
                    <p class="text-[#27438D]">{{ $cuti->tanggal_selesai ? $cuti->tanggal_selesai->format('d-m-Y') : '-' }}</p>
                </div>
                <div>
                    <label class="text-sm text-[#1B1B1B] font-medium">Durasi</label>
                    <p class="text-[#27438D] font-semibold">{{ $cuti->durasi }} hari</p>
                </div>
                <div>
                    <label class="text-sm text-[#1B1B1B] font-medium">Status</label>
                    <p>
                        <span class="px-2 py-1 rounded-full text-xs font-medium {{ $cuti->status_badge }}">
                            {{ $cuti->status_label }}
                        </span>
                    </p>
                </div>
                <div class="md:col-span-2">
                    <label class="text-sm text-[#1B1B1B] font-medium">Keterangan</label>
                    <p class="text-[#27438D]">{{ $cuti->keterangan ?? '-' }}</p>
                </div>
                @if($cuti->catatan_hr)
                <div class="md:col-span-2">
                    <label class="text-sm text-[#1B1B1B] font-medium">Catatan HR</label>
                    <p class="text-[#27438D]">{{ $cuti->catatan_hr }}</p>
                </div>
                @endif
                <div>
                    <label class="text-sm text-[#1B1B1B] font-medium">Tanggal Pengajuan</label>
                    <p class="text-[#27438D]">{{ $cuti->tanggal_pengajuan ? $cuti->tanggal_pengajuan->format('d-m-Y H:i') : '-' }}</p>
                </div>
                <div>
                    <label class="text-sm text-[#1B1B1B] font-medium">Sisa Cuti</label>
                    <p class="text-[#27438D] font-semibold">{{ $cuti->sisa_cuti }} hari</p>
                </div>
            </div>

            @if($cuti->status === 'pending')
            <div class="mt-6 pt-6 border-t border-gray-200">
                <h3 class="text-lg font-semibold text-[#161758] mb-4">Aksi</h3>
                <div class="flex flex-wrap gap-4">
                    <form action="{{ route('hr.cuti.approve', $cuti->id) }}" method="POST" class="inline">
                        @csrf
                        <input type="hidden" name="status" value="approved">
                        <button type="submit" class="bg-[#2E7D3E] text-white px-6 py-2 rounded-lg hover:bg-green-700 transition-colors">
                            Setujui
                        </button>
                    </form>
                    <form action="{{ route('hr.cuti.approve', $cuti->id) }}" method="POST" class="inline">
                        @csrf
                        <input type="hidden" name="status" value="rejected">
                        <input type="hidden" name="catatan_hr" value="Pengajuan cuti ditolak">
                        <button type="submit" class="bg-[#ec1d1d] text-white px-6 py-2 rounded-lg hover:bg-red-700 transition-colors">
                            Tolak
                        </button>
                    </form>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
