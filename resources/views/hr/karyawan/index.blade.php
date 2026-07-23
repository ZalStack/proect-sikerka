{{-- views/hr/karyawan/index.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="flex min-h-screen">
    @include('layouts.sidebar')
    <div class="flex-1 transition-all duration-300 md:ml-64 pt-6">
        <div class="p-3 sm:p-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-3 sm:gap-4">
                <div>
                    <h1 class="text-xl sm:text-2xl font-bold font-['Montserrat'] text-[#161758]">Data Karyawan</h1>
                    <p class="text-sm sm:text-base text-[#27438D]">Kelola data karyawan</p>
                </div>
                <a href="{{ route('hr.karyawan.create') }}"
                   class="w-full sm:w-auto text-center bg-[#27438D] text-white px-4 py-2 rounded-lg hover:bg-[#161758] transition-colors duration-200 whitespace-nowrap text-sm sm:text-base">
                    + Tambah Karyawan
                </a>
            </div>

            @if(session('success'))
                <div class="bg-[#2E7D3E] text-white p-3 sm:p-4 rounded-lg mb-4 text-sm">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Search & Filter -->
            <div class="bg-white rounded-lg shadow-md p-4 mb-6">
                <form action="{{ route('hr.karyawan.index') }}" method="GET" class="flex flex-col sm:flex-row gap-3 sm:gap-4">
                    <div class="flex-1">
                        <input type="text"
                               name="search"
                               value="{{ request('search') }}"
                               placeholder="Cari karyawan..."
                               class="w-full px-3 sm:px-4 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                    </div>
                    <div>
                        <select name="status_filter"
                                class="w-full sm:w-auto px-3 sm:px-4 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                            <option value="">Semua Karyawan</option>
                            <option value="active" {{ request('status_filter') === 'active' ? 'selected' : '' }}>Aktif</option>
                            <option value="resigned" {{ request('status_filter') === 'resigned' ? 'selected' : '' }}>Resign</option>
                        </select>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <button type="submit"
                                class="flex-1 sm:flex-none bg-[#27438D] text-white px-4 sm:px-6 py-2 rounded-lg hover:bg-[#161758] transition-colors duration-200 text-sm sm:text-base">
                            Filter
                        </button>
                        @if(request('search') || request('status_filter'))
                            <a href="{{ route('hr.karyawan.index') }}"
                               class="flex-1 sm:flex-none text-center bg-gray-500 text-white px-4 sm:px-6 py-2 rounded-lg hover:bg-gray-600 transition-colors duration-200 text-sm sm:text-base">
                                Reset
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            <div class="bg-white rounded-lg shadow-md">
                {{-- Desktop Table --}}
                <div class="hidden sm:block overflow-x-auto">
                    <div class="inline-block min-w-full align-middle">
                        <table class="min-w-full">
                            <thead class="bg-[#F5F5F5]">
                                <tr>
                                    <th class="px-3 sm:px-4 py-2 sm:py-3 text-left text-xs sm:text-sm font-semibold text-[#1B1B1B]">No</th>
                                    <th class="px-3 sm:px-4 py-2 sm:py-3 text-left text-xs sm:text-sm font-semibold text-[#1B1B1B]">Foto</th>
                                    <th class="px-3 sm:px-4 py-2 sm:py-3 text-left text-xs sm:text-sm font-semibold text-[#1B1B1B]">ID Karyawan</th>
                                    <th class="px-3 sm:px-4 py-2 sm:py-3 text-left text-xs sm:text-sm font-semibold text-[#1B1B1B]">Nama Lengkap</th>
                                    <th class="px-3 sm:px-4 py-2 sm:py-3 text-left text-xs sm:text-sm font-semibold text-[#1B1B1B]">Email</th>
                                    <th class="px-3 sm:px-4 py-2 sm:py-3 text-left text-xs sm:text-sm font-semibold text-[#1B1B1B]">Jabatan</th>
                                    <th class="px-3 sm:px-4 py-2 sm:py-3 text-left text-xs sm:text-sm font-semibold text-[#1B1B1B]">Divisi</th>
                                    <th class="px-3 sm:px-4 py-2 sm:py-3 text-left text-xs sm:text-sm font-semibold text-[#1B1B1B]">Posisi</th>
                                    <th class="px-3 sm:px-4 py-2 sm:py-3 text-left text-xs sm:text-sm font-semibold text-[#1B1B1B]">Status</th>
                                    <th class="px-3 sm:px-4 py-2 sm:py-3 text-left text-xs sm:text-sm font-semibold text-[#1B1B1B]">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($karyawans as $karyawan)
                                <tr class="border-b border-gray-200 hover:bg-[#F5F5F5] {{ $karyawan->is_resigned ? 'bg-red-50' : '' }}">
                                    <td class="px-3 sm:px-4 py-2 sm:py-3 text-xs sm:text-sm">{{ $loop->iteration + ($karyawans->currentPage() - 1) * $karyawans->perPage() }}</td>
                                    <td class="px-3 sm:px-4 py-2 sm:py-3">
                                        @if($karyawan->foto_profil)
                                            <img src="{{ Storage::url($karyawan->foto_profil) }}" alt="Foto" class="w-8 h-8 sm:w-10 sm:h-10 rounded-full object-cover">
                                        @else
                                            <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-full bg-[#00a2e9] flex items-center justify-center text-white font-bold text-xs sm:text-sm">
                                                {{ strtoupper(substr($karyawan->nama_lengkap, 0, 1)) }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-3 sm:px-4 py-2 sm:py-3 text-xs sm:text-sm">{{ $karyawan->kode_pegawai }}</td>
                                    <td class="px-3 sm:px-4 py-2 sm:py-3 text-xs sm:text-sm">{{ $karyawan->nama_lengkap }}</td>
                                    <td class="px-3 sm:px-4 py-2 sm:py-3 text-xs sm:text-sm break-all">{{ $karyawan->email }}</td>
                                    <td class="px-3 sm:px-4 py-2 sm:py-3 text-xs sm:text-sm">{{ $karyawan->jabatan }}</td>
                                    <td class="px-3 sm:px-4 py-2 sm:py-3 text-xs sm:text-sm">{{ $karyawan->divisi ?? '-' }}</td>
                                    <td class="px-3 sm:px-4 py-2 sm:py-3 text-xs sm:text-sm">
                                        <span class="px-2 py-1 rounded-full text-[10px] sm:text-xs font-medium {{ $karyawan->posisi === 'hr' ? 'bg-[#27438D] text-white' : 'bg-[#00a2e9] text-white' }}">
                                            {{ $karyawan->posisi === 'hr' ? 'HR' : 'Karyawan' }}
                                        </span>
                                    </td>
                                    <td class="px-3 sm:px-4 py-2 sm:py-3 text-xs sm:text-sm">
                                        <span class="px-2 py-1 rounded-full text-[10px] sm:text-xs font-medium {{ $karyawan->status_badge }}">
                                            {{ $karyawan->status_label }}
                                        </span>
                                        @if($karyawan->is_resigned)
                                            <div class="text-[10px] sm:text-xs text-[#ec1d1d] mt-1">
                                                Resign: {{ $karyawan->tanggal_resign ? $karyawan->tanggal_resign->format('d-m-Y') : '-' }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-3 sm:px-4 py-2 sm:py-3 text-xs sm:text-sm">
                                        <div class="flex flex-wrap gap-1">
                                            <a href="{{ route('hr.karyawan.show', $karyawan->id) }}"
                                               class="text-[#00a2e9] hover:text-[#27438D] text-xs sm:text-sm">
                                                Detail
                                            </a>
                                            <a href="{{ route('hr.karyawan.edit', $karyawan->id) }}"
                                               class="text-[#FCC626] hover:text-[#e6b222] text-xs sm:text-sm">
                                                Edit
                                            </a>
                                            <form action="{{ route('hr.karyawan.destroy', $karyawan->id) }}" method="POST" class="inline" onsubmit="return confirm('Apakah anda yakin ingin menghapus karyawan ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-[#ec1d1d] hover:text-red-700 text-xs sm:text-sm">
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
                                            <svg class="w-12 sm:w-16 h-12 sm:h-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            <p class="text-base sm:text-lg font-semibold">Belum ada data karyawan</p>
                                            <p class="text-xs sm:text-sm text-gray-500 mt-1">Klik tombol "Tambah Karyawan" untuk menambahkan</p>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Mobile Cards --}}
                <div class="sm:hidden divide-y divide-gray-200">
                    @forelse($karyawans as $karyawan)
                    <div class="p-3 space-y-2 {{ $karyawan->is_resigned ? 'bg-red-50' : '' }}">
                        <div class="flex items-center gap-3">
                            @if($karyawan->foto_profil)
                                <img src="{{ Storage::url($karyawan->foto_profil) }}" alt="" class="w-10 h-10 rounded-full object-cover">
                            @else
                                <div class="w-10 h-10 rounded-full bg-[#00a2e9] flex items-center justify-center text-white font-bold text-sm">
                                    {{ strtoupper(substr($karyawan->nama_lengkap, 0, 1)) }}
                                </div>
                            @endif
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-semibold text-[#1B1B1B] truncate">{{ $karyawan->nama_lengkap }}</p>
                                <p class="text-[11px] text-gray-500">{{ $karyawan->kode_pegawai }} • {{ $karyawan->jabatan }}</p>
                            </div>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-medium {{ $karyawan->status_badge }}">
                                {{ $karyawan->status_label }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between text-[11px] text-gray-500">
                            <span>{{ $karyawan->divisi ?? '-' }}</span>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-medium {{ $karyawan->posisi === 'hr' ? 'bg-[#27438D] text-white' : 'bg-[#00a2e9] text-white' }}">
                                {{ $karyawan->posisi === 'hr' ? 'HR' : 'Karyawan' }}
                            </span>
                        </div>
                        @if($karyawan->is_resigned)
                            <div class="text-[11px] text-[#ec1d1d]">
                                Resign: {{ $karyawan->tanggal_resign ? $karyawan->tanggal_resign->format('d-m-Y') : '-' }}
                            </div>
                        @endif
                        <div class="flex flex-wrap items-center gap-x-3 gap-y-1 pt-1">
                            <a href="{{ route('hr.karyawan.show', $karyawan->id) }}" class="text-[#00a2e9] text-xs">Detail</a>
                            <a href="{{ route('hr.karyawan.edit', $karyawan->id) }}" class="text-[#FCC626] text-xs">Edit</a>
                            <form action="{{ route('hr.karyawan.destroy', $karyawan->id) }}" method="POST" class="inline-flex items-center" onsubmit="return confirm('Apakah anda yakin ingin menghapus karyawan ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-[#ec1d1d] text-xs">Hapus</button>
                            </form>
                        </div>
                    </div>
                    @empty
                    <div class="p-4 py-10 text-center text-[#1B1B1B]">
                        <div class="flex flex-col items-center">
                            <svg class="w-10 h-10 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <p class="text-sm font-semibold text-gray-500">Belum ada data karyawan</p>
                            <p class="text-xs text-gray-400 mt-1">Klik tombol "Tambah Karyawan" untuk menambahkan</p>
                        </div>
                    </div>
                    @endforelse
                </div>

                <!-- Pagination -->
                <div class="px-4 py-4 bg-white border-t border-gray-200">
                    <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                        <div class="text-xs sm:text-sm text-[#1B1B1B] order-2 sm:order-1">
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
                                                <span class="px-2 sm:px-3 py-2 rounded-lg bg-gray-100 text-gray-400 cursor-not-allowed text-xs sm:text-sm">&laquo;</span>
                                            </li>
                                        @else
                                            <li>
                                                <a href="{{ $karyawans->previousPageUrl() }}"
                                                   class="px-2 sm:px-3 py-2 rounded-lg bg-gray-100 text-[#27438D] hover:bg-[#27438D] hover:text-white transition-colors duration-200 text-xs sm:text-sm">&laquo;</a>
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
                                                   class="px-2 sm:px-3 py-2 rounded-lg bg-gray-100 text-[#27438D] hover:bg-[#27438D] hover:text-white transition-colors duration-200 text-xs sm:text-sm">1</a>
                                            </li>
                                            @if ($start > 2)
                                                <li class="disabled">
                                                    <span class="px-2 sm:px-3 py-2 rounded-lg bg-gray-100 text-gray-400 cursor-not-allowed text-xs sm:text-sm">...</span>
                                                </li>
                                            @endif
                                        @endif

                                        @for ($page = $start; $page <= $end; $page++)
                                            @if ($page == $karyawans->currentPage())
                                                <li class="active">
                                                    <span class="px-2 sm:px-3 py-2 rounded-lg bg-[#27438D] text-white text-xs sm:text-sm font-semibold">{{ $page }}</span>
                                                </li>
                                            @else
                                                <li>
                                                    <a href="{{ $karyawans->url($page) }}"
                                                       class="px-2 sm:px-3 py-2 rounded-lg bg-gray-100 text-[#27438D] hover:bg-[#27438D] hover:text-white transition-colors duration-200 text-xs sm:text-sm">{{ $page }}</a>
                                                </li>
                                            @endif
                                        @endfor

                                        @if ($end < $karyawans->lastPage())
                                            @if ($end < $karyawans->lastPage() - 1)
                                                <li class="disabled">
                                                    <span class="px-2 sm:px-3 py-2 rounded-lg bg-gray-100 text-gray-400 cursor-not-allowed text-xs sm:text-sm">...</span>
                                                </li>
                                            @endif
                                            <li>
                                                <a href="{{ $karyawans->url($karyawans->lastPage()) }}"
                                                   class="px-2 sm:px-3 py-2 rounded-lg bg-gray-100 text-[#27438D] hover:bg-[#27438D] hover:text-white transition-colors duration-200 text-xs sm:text-sm">{{ $karyawans->lastPage() }}</a>
                                            </li>
                                        @endif

                                        @if ($karyawans->hasMorePages())
                                            <li>
                                                <a href="{{ $karyawans->nextPageUrl() }}"
                                                   class="px-2 sm:px-3 py-2 rounded-lg bg-gray-100 text-[#27438D] hover:bg-[#27438D] hover:text-white transition-colors duration-200 text-xs sm:text-sm">&raquo;</a>
                                            </li>
                                        @else
                                            <li class="disabled">
                                                <span class="px-2 sm:px-3 py-2 rounded-lg bg-gray-100 text-gray-400 cursor-not-allowed text-xs sm:text-sm">&raquo;</span>
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
</div>
@endsection
