@extends('layouts.app')

@section('content')
<div class="flex">
    @include('layouts.sidebar')
    <div class="ml-64 flex-1 p-6">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold font-['Montserrat'] text-[#161758]">Data Karyawan</h1>
                <p class="text-[#27438D]">Kelola data karyawan</p>
            </div>
            <a href="{{ route('hr.karyawan.create') }}"
               class="bg-[#27438D] text-white px-4 py-2 rounded-lg hover:bg-[#161758] transition-colors duration-200">
                + Tambah Karyawan
            </a>
        </div>

        @if(session('success'))
            <div class="bg-[#2E7D3E] text-white p-4 rounded-lg mb-4">
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-[#F5F5F5]">
                        <tr>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-[#1B1B1B]">Foto</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-[#1B1B1B]">Nama</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-[#1B1B1B]">NIP</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-[#1B1B1B]">Email</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-[#1B1B1B]">Jabatan</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-[#1B1B1B]">Status</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-[#1B1B1B]">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($karyawans as $karyawan)
                        <tr class="border-b border-gray-200 hover:bg-[#F5F5F5]">
                            <td class="px-4 py-3">
                                @if($karyawan->foto_profil)
                                    <img src="{{ Storage::url($karyawan->foto_profil) }}" alt="Foto" class="w-10 h-10 rounded-full object-cover">
                                @else
                                    <div class="w-10 h-10 rounded-full bg-[#00a2e9] flex items-center justify-center text-white font-bold">
                                        {{ strtoupper(substr($karyawan->nama_depan, 0, 1)) }}
                                    </div>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm">{{ $karyawan->nama_lengkap }}</td>
                            <td class="px-4 py-3 text-sm">{{ $karyawan->nip }}</td>
                            <td class="px-4 py-3 text-sm">{{ $karyawan->email }}</td>
                            <td class="px-4 py-3 text-sm">{{ $karyawan->jabatan }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-1 rounded-full text-xs font-medium {{ $karyawan->status === 'Full-time' ? 'bg-[#2E7D3E] text-white' : ($karyawan->status === 'Contract' ? 'bg-[#FCC626] text-[#1B1B1B]' : 'bg-[#00a2e9] text-white') }}">
                                    {{ $karyawan->status }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex space-x-2">
                                    <a href="{{ route('hr.karyawan.show', $karyawan->id) }}"
                                       class="text-[#00a2e9] hover:text-[#27438D]">
                                        Detail
                                    </a>
                                    <a href="{{ route('hr.karyawan.edit', $karyawan->id) }}"
                                       class="text-[#FCC626] hover:text-[#e6b222]">
                                        Edit
                                    </a>
                                    <form action="{{ route('hr.karyawan.destroy', $karyawan->id) }}" method="POST" class="inline" onsubmit="return confirm('Apakah anda yakin ingin menghapus karyawan ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-[#ec1d1d] hover:text-red-700">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-[#1B1B1B]">
                                Belum ada data karyawan
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
