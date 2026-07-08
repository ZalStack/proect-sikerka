@extends('layouts.app')

@section('content')
<div class="flex">
    @include('layouts.sidebar')
    <div class="ml-64 flex-1 p-6">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
            <div>
                <h1 class="text-2xl font-bold font-['Montserrat'] text-[#161758]">Data Karyawan</h1>
                <p class="text-[#27438D]">Kelola data karyawan</p>
            </div>
            <a href="{{ route('hr.karyawan.create') }}"
               class="bg-[#27438D] text-white px-4 py-2 rounded-lg hover:bg-[#161758] transition-colors duration-200 whitespace-nowrap">
                + Tambah Karyawan
            </a>
        </div>

        @if(session('success'))
            <div class="bg-[#2E7D3E] text-white p-4 rounded-lg mb-4">
                {{ session('success') }}
            </div>
        @endif

        <!-- Search -->
        <div class="bg-white rounded-lg shadow-md p-4 mb-6">
            <form action="{{ route('hr.karyawan.index') }}" method="GET" class="flex flex-col sm:flex-row gap-4">
                <div class="flex-1">
                    <input type="text"
                           name="search"
                           value="{{ request('search') }}"
                           placeholder="Cari karyawan..."
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                </div>
                <div class="flex gap-2">
                    <button type="submit"
                            class="bg-[#27438D] text-white px-6 py-2 rounded-lg hover:bg-[#161758] transition-colors duration-200">
                        Cari
                    </button>
                    @if(request('search'))
                        <a href="{{ route('hr.karyawan.index') }}"
                           class="bg-gray-500 text-white px-6 py-2 rounded-lg hover:bg-gray-600 transition-colors duration-200">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[800px]">
                    <thead class="bg-[#F5F5F5]">
                        <tr>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-[#1B1B1B]">No</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-[#1B1B1B]">Foto</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-[#1B1B1B]">Kode Pegawai</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-[#1B1B1B]">Nama Lengkap</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-[#1B1B1B]">Email</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-[#1B1B1B]">Jabatan</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-[#1B1B1B]">Divisi</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-[#1B1B1B]">Posisi</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-[#1B1B1B]">Status</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-[#1B1B1B]">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($karyawans as $karyawan)
                        <tr class="border-b border-gray-200 hover:bg-[#F5F5F5]">
                            <td class="px-4 py-3 text-sm">{{ $loop->iteration + ($karyawans->currentPage() - 1) * $karyawans->perPage() }}</td>
                            <td class="px-4 py-3">
                                @if($karyawan->foto_profil)
                                    <img src="{{ Storage::url($karyawan->foto_profil) }}" alt="Foto" class="w-10 h-10 rounded-full object-cover">
                                @else
                                    <div class="w-10 h-10 rounded-full bg-[#00a2e9] flex items-center justify-center text-white font-bold">
                                        {{ strtoupper(substr($karyawan->nama_lengkap, 0, 1)) }}
                                    </div>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm">{{ $karyawan->kode_pegawai }}</td>
                            <td class="px-4 py-3 text-sm">{{ $karyawan->nama_lengkap }}</td>
                            <td class="px-4 py-3 text-sm">{{ $karyawan->email }}</td>
                            <td class="px-4 py-3 text-sm">{{ $karyawan->jabatan }}</td>
                            <td class="px-4 py-3 text-sm">{{ $karyawan->divisi ?? '-' }}</td>
                            <td class="px-4 py-3 text-sm">
                                <span class="px-2 py-1 rounded-full text-xs font-medium {{ $karyawan->posisi === 'hr' ? 'bg-[#27438D] text-white' : 'bg-[#00a2e9] text-white' }}">
                                    {{ $karyawan->posisi === 'hr' ? 'HR' : 'Karyawan' }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-1 rounded-full text-xs font-medium {{ $karyawan->status === 'Karyawan Tetap' ? 'bg-[#2E7D3E] text-white' : ($karyawan->status === 'Contract' ? 'bg-[#FCC626] text-[#1B1B1B]' : 'bg-[#00a2e9] text-white') }}">
                                    {{ $karyawan->status }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap gap-1">
                                    <a href="{{ route('hr.karyawan.show', $karyawan->id) }}"
                                       class="text-[#00a2e9] hover:text-[#27438D] text-sm">
                                        Detail
                                    </a>
                                    <a href="{{ route('hr.karyawan.edit', $karyawan->id) }}"
                                       class="text-[#FCC626] hover:text-[#e6b222] text-sm">
                                        Edit
                                    </a>
                                    <form action="{{ route('hr.karyawan.destroy', $karyawan->id) }}" method="POST" class="inline" onsubmit="return confirm('Apakah anda yakin ingin menghapus karyawan ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-[#ec1d1d] hover:text-red-700 text-sm">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="px-4 py-8 text-center text-[#1B1B1B]">
                                <div class="flex flex-col items-center">
                                    <svg class="w-16 h-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <p class="text-lg font-semibold">Belum ada data karyawan</p>
                                    <p class="text-sm text-gray-500 mt-1">Klik tombol "Tambah Karyawan" untuk menambahkan</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="px-4 py-4 bg-white border-t border-gray-200">
                <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="text-sm text-[#1B1B1B] order-2 sm:order-1">
                        Menampilkan
                        <span class="font-semibold">{{ $karyawans->firstItem() ?? 0 }}</span>
                        -
                        <span class="font-semibold">{{ $karyawans->lastItem() ?? 0 }}</span>
                        dari
                        <span class="font-semibold">{{ $karyawans->total() }}</span>
                        data
                    </div>

                    <div class="order-1 sm:order-2 w-full sm:w-auto">
                        @if ($karyawans->hasPages())
                            <nav class="flex justify-center" aria-label="Pagination">
                                <ul class="flex flex-wrap gap-1">
                                    @if ($karyawans->onFirstPage())
                                        <li class="disabled">
                                            <span class="px-3 py-2 rounded-lg bg-gray-100 text-gray-400 cursor-not-allowed text-sm">&laquo;</span>
                                        </li>
                                    @else
                                        <li>
                                            <a href="{{ $karyawans->previousPageUrl() }}"
                                               class="px-3 py-2 rounded-lg bg-gray-100 text-[#27438D] hover:bg-[#27438D] hover:text-white transition-colors duration-200 text-sm">&laquo;</a>
                                        </li>
                                    @endif

                                    @php
                                        $start = max(1, $karyawans->currentPage() - 2);
                                        $end = min($start + 4, $karyawans->lastPage());
                                        if ($end - $start < 4) {
                                            $start = max(1, $end - 4);
                                        }
                                    @endphp

                                    @if ($start > 1)
                                        <li>
                                            <a href="{{ $karyawans->url(1) }}"
                                               class="px-3 py-2 rounded-lg bg-gray-100 text-[#27438D] hover:bg-[#27438D] hover:text-white transition-colors duration-200 text-sm">1</a>
                                        </li>
                                        @if ($start > 2)
                                            <li class="disabled">
                                                <span class="px-3 py-2 rounded-lg bg-gray-100 text-gray-400 cursor-not-allowed text-sm">...</span>
                                            </li>
                                        @endif
                                    @endif

                                    @for ($page = $start; $page <= $end; $page++)
                                        @if ($page == $karyawans->currentPage())
                                            <li class="active">
                                                <span class="px-3 py-2 rounded-lg bg-[#27438D] text-white text-sm font-semibold">{{ $page }}</span>
                                            </li>
                                        @else
                                            <li>
                                                <a href="{{ $karyawans->url($page) }}"
                                                   class="px-3 py-2 rounded-lg bg-gray-100 text-[#27438D] hover:bg-[#27438D] hover:text-white transition-colors duration-200 text-sm">{{ $page }}</a>
                                            </li>
                                        @endif
                                    @endfor

                                    @if ($end < $karyawans->lastPage())
                                        @if ($end < $karyawans->lastPage() - 1)
                                            <li class="disabled">
                                                <span class="px-3 py-2 rounded-lg bg-gray-100 text-gray-400 cursor-not-allowed text-sm">...</span>
                                            </li>
                                        @endif
                                        <li>
                                            <a href="{{ $karyawans->url($karyawans->lastPage()) }}"
                                               class="px-3 py-2 rounded-lg bg-gray-100 text-[#27438D] hover:bg-[#27438D] hover:text-white transition-colors duration-200 text-sm">{{ $karyawans->lastPage() }}</a>
                                        </li>
                                    @endif

                                    @if ($karyawans->hasMorePages())
                                        <li>
                                            <a href="{{ $karyawans->nextPageUrl() }}"
                                               class="px-3 py-2 rounded-lg bg-gray-100 text-[#27438D] hover:bg-[#27438D] hover:text-white transition-colors duration-200 text-sm">&raquo;</a>
                                        </li>
                                    @else
                                        <li class="disabled">
                                            <span class="px-3 py-2 rounded-lg bg-gray-100 text-gray-400 cursor-not-allowed text-sm">&raquo;</span>
                                        </li>
                                    @endif
                                </ul>
                            </nav>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
