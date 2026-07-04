@extends('layouts.app')

@section('content')
<div class="flex">
    @include('layouts.sidebar')
    <div class="ml-64 flex-1 p-6">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold font-['Montserrat'] text-[#161758]">Detail Karyawan</h1>
                <p class="text-[#27438D]">Informasi lengkap karyawan</p>
            </div>
            <div class="flex space-x-2">
                <a href="{{ route('hr.karyawan.edit', $karyawan->id) }}"
                   class="bg-[#00a2e9] text-white px-4 py-2 rounded-lg hover:bg-[#27438D] transition-colors duration-200">
                    Edit
                </a>
                <a href="{{ route('hr.karyawan.index') }}"
                   class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600 transition-colors duration-200">
                    Kembali
                </a>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="p-6">
                <div class="flex items-center space-x-6 mb-6">
                    @if($karyawan->foto_profil)
                        <img src="{{ Storage::url($karyawan->foto_profil) }}" alt="Foto" class="w-32 h-32 rounded-full object-cover border-4 border-[#00a2e9]">
                    @else
                        <div class="w-32 h-32 rounded-full bg-[#00a2e9] flex items-center justify-center text-white text-4xl font-bold">
                            {{ strtoupper(substr($karyawan->nama_depan, 0, 1)) }}
                        </div>
                    @endif
                    <div>
                        <h2 class="text-2xl font-bold text-[#161758]">{{ $karyawan->nama_lengkap }}</h2>
                        <p class="text-[#27438D]">{{ $karyawan->jabatan }}</p>
                        <span class="inline-block px-3 py-1 rounded-full text-sm font-medium mt-2 {{ $karyawan->status === 'Full-time' ? 'bg-[#2E7D3E] text-white' : ($karyawan->status === 'Contract' ? 'bg-[#FCC626] text-[#1B1B1B]' : 'bg-[#00a2e9] text-white') }}">
                            {{ $karyawan->status }}
                        </span>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-4">
                        <h3 class="text-lg font-semibold text-[#161758] border-b border-gray-200 pb-2">Informasi Pribadi</h3>
                        <div>
                            <label class="text-sm text-[#1B1B1B] font-medium">Kode Pegawai</label>
                            <p class="text-[#27438D]">{{ $karyawan->kode_pegawai }}</p>
                        </div>
                        <div>
                            <label class="text-sm text-[#1B1B1B] font-medium">Email</label>
                            <p class="text-[#27438D]">{{ $karyawan->email }}</p>
                        </div>
                        <div>
                            <label class="text-sm text-[#1B1B1B] font-medium">NIK</label>
                            <p class="text-[#27438D]">{{ $karyawan->nik ?? '-' }}</p>
                        </div>
                        <div>
                            <label class="text-sm text-[#1B1B1B] font-medium">NPWP</label>
                            <p class="text-[#27438D]">{{ $karyawan->npwp ?? '-' }}</p>
                        </div>
                        <div>
                            <label class="text-sm text-[#1B1B1B] font-medium">Tempat, Tanggal Lahir</label>
                            <p class="text-[#27438D]">{{ $karyawan->tempat_lahir ?? '-' }}{{ $karyawan->tempat_lahir && $karyawan->tanggal_lahir ? ', ' : '' }}{{ $karyawan->tanggal_lahir ? $karyawan->tanggal_lahir->format('d-m-Y') : '-' }}</p>
                        </div>
                        <div>
                            <label class="text-sm text-[#1B1B1B] font-medium">Jenis Kelamin</label>
                            <p class="text-[#27438D]">{{ $karyawan->jenis_kelamin ?? '-' }}</p>
                        </div>
                        <div>
                            <label class="text-sm text-[#1B1B1B] font-medium">Agama</label>
                            <p class="text-[#27438D]">{{ $karyawan->agama ?? '-' }}</p>
                        </div>
                        <div>
                            <label class="text-sm text-[#1B1B1B] font-medium">Status Pernikahan</label>
                            <p class="text-[#27438D]">{{ $karyawan->status_pernikahan ?? '-' }}</p>
                        </div>
                        <div>
                            <label class="text-sm text-[#1B1B1B] font-medium">Golongan Darah</label>
                            <p class="text-[#27438D]">{{ $karyawan->golongan_darah ?? '-' }}</p>
                        </div>
                        <div>
                            <label class="text-sm text-[#1B1B1B] font-medium">No KK</label>
                            <p class="text-[#27438D]">{{ $karyawan->no_kk ?? '-' }}</p>
                        </div>
                        <div>
                            <label class="text-sm text-[#1B1B1B] font-medium">Nama Ibu Kandung</label>
                            <p class="text-[#27438D]">{{ $karyawan->nama_ibu_kandung ?? '-' }}</p>
                        </div>
                        <div>
                            <label class="text-sm text-[#1B1B1B] font-medium">Sedang Melanjutkan Pendidikan</label>
                            <p class="text-[#27438D]">{{ $karyawan->sedang_melanjutkan_pendidikan ?? '-' }}</p>
                        </div>
                        <div>
                            <label class="text-sm text-[#1B1B1B] font-medium">Jumlah Anak</label>
                            <p class="text-[#27438D]">{{ $karyawan->jumlah_anak ?? 0 }}</p>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <h3 class="text-lg font-semibold text-[#161758] border-b border-gray-200 pb-2">Informasi Profesional</h3>
                        <div>
                            <label class="text-sm text-[#1B1B1B] font-medium">Jabatan</label>
                            <p class="text-[#27438D]">{{ $karyawan->jabatan }}</p>
                        </div>
                        <div>
                            <label class="text-sm text-[#1B1B1B] font-medium">Divisi</label>
                            <p class="text-[#27438D]">{{ $karyawan->divisi ?? '-' }}</p>
                        </div>
                        <div>
                            <label class="text-sm text-[#1B1B1B] font-medium">Posisi</label>
                            <p class="text-[#27438D]">{{ $karyawan->posisi === 'hr' ? 'HR' : 'Karyawan' }}</p>
                        </div>
                        <div>
                            <label class="text-sm text-[#1B1B1B] font-medium">Tanggal Bergabung</label>
                            <p class="text-[#27438D]">{{ $karyawan->tanggal_bergabung ? $karyawan->tanggal_bergabung->format('d-m-Y') : '-' }}</p>
                        </div>
                        @if($karyawan->end_date)
                        <div>
                            <label class="text-sm text-[#1B1B1B] font-medium">Tanggal Berakhir</label>
                            <p class="text-[#27438D]">{{ $karyawan->end_date->format('d-m-Y') }}</p>
                        </div>
                        @endif
                        <div>
                            <label class="text-sm text-[#1B1B1B] font-medium">Total Hari Kerja</label>
                            <p class="text-[#27438D]">{{ $karyawan->total_hari_kerja ?? 0 }}</p>
                        </div>
                        @if($karyawan->reason_resigned)
                        <div>
                            <label class="text-sm text-[#1B1B1B] font-medium">Alasan Resign</label>
                            <p class="text-[#27438D]">{{ $karyawan->reason_resigned }}</p>
                        </div>
                        @endif
                        <div>
                            <label class="text-sm text-[#1B1B1B] font-medium">Status</label>
                            <p class="text-[#27438D]">{{ $karyawan->status }}</p>
                        </div>
                    </div>

                    <div class="md:col-span-2 space-y-4">
                        <h3 class="text-lg font-semibold text-[#161758] border-b border-gray-200 pb-2">Pendidikan</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="text-sm text-[#1B1B1B] font-medium">Pendidikan Terakhir</label>
                                <p class="text-[#27438D]">{{ $karyawan->pendidikan_terakhir_new ?? $karyawan->pendidikan_terakhir ?? '-' }}</p>
                            </div>
                            <div>
                                <label class="text-sm text-[#1B1B1B] font-medium">Perguruan Tinggi</label>
                                <p class="text-[#27438D]">{{ $karyawan->perguruan_tinggi ?? '-' }}</p>
                            </div>
                            <div>
                                <label class="text-sm text-[#1B1B1B] font-medium">Jurusan</label>
                                <p class="text-[#27438D]">{{ $karyawan->jurusan ?? '-' }}</p>
                            </div>
                            <div>
                                <label class="text-sm text-[#1B1B1B] font-medium">Tahun Lulus</label>
                                <p class="text-[#27438D]">{{ $karyawan->tahun_lulus ?? '-' }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="md:col-span-2 space-y-4">
                        <h3 class="text-lg font-semibold text-[#161758] border-b border-gray-200 pb-2">Kontak & Alamat</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="text-sm text-[#1B1B1B] font-medium">Nomor Telepon</label>
                                <p class="text-[#27438D]">{{ $karyawan->nomor_telepon ?? '-' }}</p>
                            </div>
                            <div>
                                <label class="text-sm text-[#1B1B1B] font-medium">No WA</label>
                                <p class="text-[#27438D]">{{ $karyawan->no_wa ?? '-' }}</p>
                            </div>
                            <div>
                                <label class="text-sm text-[#1B1B1B] font-medium">Alamat</label>
                                <p class="text-[#27438D]">{{ $karyawan->alamat ?? '-' }}</p>
                            </div>
                            <div>
                                <label class="text-sm text-[#1B1B1B] font-medium">Nama Kontak Darurat</label>
                                <p class="text-[#27438D]">{{ $karyawan->nama_kontak_darurat ?? '-' }}</p>
                            </div>
                            <div>
                                <label class="text-sm text-[#1B1B1B] font-medium">Telepon Kontak Darurat</label>
                                <p class="text-[#27438D]">{{ $karyawan->telepon_kontak_darurat ?? '-' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
