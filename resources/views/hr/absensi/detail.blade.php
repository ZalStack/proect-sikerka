@extends('layouts.app')

@section('content')
<div class="flex">
    @include('layouts.sidebar')
    <div class="ml-64 flex-1 p-6">
        <div class="mb-6">
            <h1 class="text-2xl font-bold font-['Montserrat'] text-[#161758]">Detail Absensi</h1>
            <p class="text-[#27438D]">Informasi lengkap absensi karyawan</p>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h3 class="text-lg font-semibold text-[#161758] border-b border-gray-200 pb-2 mb-4">Informasi Karyawan</h3>
                    <div class="space-y-2">
                        <div>
                            <label class="text-sm text-[#1B1B1B] font-medium">Nama Lengkap</label>
                            <p class="text-[#27438D]">{{ $absensi->karyawan->nama_lengkap }}</p>
                        </div>
                        <div>
                            <label class="text-sm text-[#1B1B1B] font-medium">NIP</label>
                            <p class="text-[#27438D]">{{ $absensi->karyawan->nip }}</p>
                        </div>
                        <div>
                            <label class="text-sm text-[#1B1B1B] font-medium">Jabatan</label>
                            <p class="text-[#27438D]">{{ $absensi->karyawan->jabatan }}</p>
                        </div>
                        <div>
                            <label class="text-sm text-[#1B1B1B] font-medium">Divisi</label>
                            <p class="text-[#27438D]">{{ $absensi->karyawan->divisi ?? '-' }}</p>
                        </div>
                    </div>
                </div>

                <div>
                    <h3 class="text-lg font-semibold text-[#161758] border-b border-gray-200 pb-2 mb-4">Detail Absensi</h3>
                    <div class="space-y-2">
                        <div>
                            <label class="text-sm text-[#1B1B1B] font-medium">Tanggal</label>
                            <p class="text-[#27438D]">{{ $absensi->tanggal->format('d-m-Y') }}</p>
                        </div>
                        <div>
                            <label class="text-sm text-[#1B1B1B] font-medium">Kantor Cabang</label>
                            <p class="text-[#27438D]">{{ $absensi->kantor_cabang }}</p>
                        </div>
                        <div>
                            <label class="text-sm text-[#1B1B1B] font-medium">Check-in</label>
                            <p class="text-[#27438D]">{{ $absensi->check_in ? Carbon\Carbon::parse($absensi->check_in)->format('H:i') : '-' }}</p>
                        </div>
                        <div>
                            <label class="text-sm text-[#1B1B1B] font-medium">Check-out</label>
                            <p class="text-[#27438D]">{{ $absensi->check_out ? Carbon\Carbon::parse($absensi->check_out)->format('H:i') : '-' }}</p>
                        </div>
                        <div>
                            <label class="text-sm text-[#1B1B1B] font-medium">Total Jam Kerja</label>
                            <p class="text-[#27438D]">{{ $absensi->total_jam_kerja }} jam</p>
                        </div>
                        <div>
                            <label class="text-sm text-[#1B1B1B] font-medium">Status</label>
                            <p class="text-[#27438D]">
                                <span class="px-2 py-1 rounded-full text-xs font-medium
                                    {{ $absensi->status == 'Hadir' ? 'bg-[#2E7D3E] text-white' :
                                       ($absensi->status == 'Izin' ? 'bg-[#FCC626] text-[#1B1B1B]' :
                                       ($absensi->status == 'Sakit' ? 'bg-[#00a2e9] text-white' : 'bg-[#ec1d1d] text-white')) }}">
                                    {{ $absensi->status }}
                                </span>
                            </p>
                        </div>
                        @if($absensi->is_telat)
                        <div>
                            <label class="text-sm text-[#1B1B1B] font-medium">Telat</label>
                            <p class="text-[#ec1d1d] font-semibold">{{ $absensi->menit_telat }} menit</p>
                        </div>
                        @endif
                        @if($absensi->is_lembur)
                        <div>
                            <label class="text-sm text-[#1B1B1B] font-medium">Lembur</label>
                            <p class="text-[#00a2e9] font-semibold">{{ $absensi->jam_lembur }} jam</p>
                        </div>
                        @endif
                        @if($absensi->keterangan)
                        <div>
                            <label class="text-sm text-[#1B1B1B] font-medium">Keterangan</label>
                            <p class="text-[#27438D]">{{ $absensi->keterangan }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Form Update Status -->
            <div class="mt-6 pt-6 border-t border-gray-200">
                <h3 class="text-lg font-semibold text-[#161758] mb-4">Update Status</h3>
                <form action="{{ route('hr.absensi.update-status', $absensi->id) }}" method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Status</label>
                        <select name="status" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                            <option value="Hadir" {{ $absensi->status == 'Hadir' ? 'selected' : '' }}>Hadir</option>
                            <option value="Izin" {{ $absensi->status == 'Izin' ? 'selected' : '' }}>Izin</option>
                            <option value="Sakit" {{ $absensi->status == 'Sakit' ? 'selected' : '' }}>Sakit</option>
                            <option value="Alpha" {{ $absensi->status == 'Alpha' ? 'selected' : '' }}>Alpha</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Keterangan</label>
                        <input type="text" name="keterangan" value="{{ $absensi->keterangan }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                    </div>
                    <div class="flex items-end">
                        <button type="submit"
                                class="bg-[#27438D] text-white px-6 py-2 rounded-lg hover:bg-[#161758] transition-colors duration-200">
                            Update
                        </button>
                    </div>
                </form>
            </div>

            <div class="mt-6">
                <a href="{{ route('hr.absensi.index') }}"
                   class="bg-gray-500 text-white px-6 py-2 rounded-lg hover:bg-gray-600 transition-colors duration-200">
                    Kembali
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
