@extends('layouts.app')

@section('content')
<div class="flex">
    @include('layouts.sidebar')

    <div class="flex-1 min-w-0 md:ml-64 p-4 sm:p-6 lg:p-8 space-y-6">
        
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">
                    <a href="{{ route('superadmin.dashboard') }}" class="hover:text-[#00a2e9]">Super Admin</a>
                    <span>/</span>
                    <span class="text-gray-700">Manajemen Role Pengguna</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-[#0F1245] font-['Montserrat']">
                    Kelola Role & Hak Akses Pengguna
                </h1>
                <p class="text-xs sm:text-sm text-gray-500 font-light mt-0.5">
                    Atur hak akses pengguna menjadi Super Administrator, HR Administrator, atau Karyawan.
                </p>
            </div>

            <a href="{{ route('superadmin.dashboard') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold transition-all">
                <i class="fa-solid fa-arrow-left"></i>
                <span>Kembali ke Dashboard</span>
            </a>
        </div>

        <!-- Flash Messages -->
        @if(session('success'))
            <div class="bg-emerald-500/10 border border-emerald-500/20 text-emerald-800 rounded-2xl p-4 flex items-center justify-between shadow-sm" role="alert">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-emerald-500 text-white flex items-center justify-center flex-shrink-0">
                        <i class="fa-solid fa-check"></i>
                    </div>
                    <span class="text-sm font-medium">{{ session('success') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-900 p-1">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
        @endif

        @if(session('error'))
            <div class="bg-rose-500/10 border border-rose-500/20 text-rose-800 rounded-2xl p-4 flex items-center justify-between shadow-sm" role="alert">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-rose-500 text-white flex items-center justify-center flex-shrink-0">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                    </div>
                    <span class="text-sm font-medium">{{ session('error') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-rose-600 hover:text-rose-900 p-1">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
        @endif

        <!-- Role KPI Cards -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <div class="bg-white rounded-2xl p-4 border border-gray-100 shadow-sm">
                <span class="text-xs font-medium text-gray-400">Total Akun Pengguna</span>
                <p class="text-2xl font-extrabold text-[#0F1245] mt-1">{{ $stats['total'] }}</p>
            </div>
            <div class="bg-white rounded-2xl p-4 border border-amber-200 shadow-sm bg-gradient-to-br from-white to-amber-50/40">
                <span class="text-xs font-bold text-amber-700">Super Administrator</span>
                <div class="flex items-baseline justify-between mt-1">
                    <span class="text-2xl font-extrabold text-amber-600">{{ $stats['superadmin'] }}</span>
                    <i class="fa-solid fa-crown text-amber-500 text-sm"></i>
                </div>
            </div>
            <div class="bg-white rounded-2xl p-4 border border-blue-100 shadow-sm">
                <span class="text-xs font-medium text-blue-600">HR Administrator</span>
                <p class="text-2xl font-extrabold text-blue-700 mt-1">{{ $stats['hr'] }}</p>
            </div>
            <div class="bg-white rounded-2xl p-4 border border-emerald-100 shadow-sm">
                <span class="text-xs font-medium text-emerald-600">Karyawan</span>
                <p class="text-2xl font-extrabold text-emerald-700 mt-1">{{ $stats['karyawan'] }}</p>
            </div>
        </div>

        <!-- Filter & Search -->
        <div class="bg-white rounded-3xl p-5 shadow-sm border border-gray-100">
            <form method="GET" action="{{ route('superadmin.karyawan.index') }}" class="flex flex-col sm:flex-row items-center justify-between gap-3">
                <div class="flex items-center gap-3 w-full sm:w-auto flex-1">
                    <div class="relative flex-1 max-w-md">
                        <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                        <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama, email, NIP, atau jabatan..." class="w-full pl-9 pr-3 py-2 rounded-xl border border-gray-200 text-xs font-medium focus:ring-2 focus:ring-[#00a2e9]/20 focus:border-[#00a2e9]">
                    </div>

                    <select name="posisi" class="px-3 py-2 rounded-xl border border-gray-200 text-xs font-medium focus:ring-2 focus:ring-[#00a2e9]/20 focus:border-[#00a2e9]">
                        <option value="">-- Semua Role --</option>
                        <option value="superadmin" {{ $posisi === 'superadmin' ? 'selected' : '' }}>Super Administrator</option>
                        <option value="hr" {{ $posisi === 'hr' ? 'selected' : '' }}>HR Administrator</option>
                        <option value="karyawan" {{ $posisi === 'karyawan' ? 'selected' : '' }}>Karyawan</option>
                    </select>
                </div>

                <div class="flex items-center gap-2 w-full sm:w-auto justify-end">
                    <a href="{{ route('superadmin.karyawan.index') }}" class="px-4 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-600 text-xs font-semibold transition-all">
                        Reset
                    </a>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-[#0F1245] hover:bg-[#161758] text-white text-xs font-bold transition-all shadow-sm flex items-center gap-1.5">
                        <i class="fa-solid fa-filter"></i>
                        <span>Filter</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- Users Table -->
        <div class="bg-white rounded-3xl p-5 sm:p-6 shadow-sm border border-gray-100 space-y-4">
            <div class="overflow-x-auto rounded-2xl border border-gray-100">
                <table class="w-full text-left text-xs sm:text-sm text-gray-600">
                    <thead class="bg-gray-50/80 text-gray-700 font-semibold text-xs border-b border-gray-100">
                        <tr>
                            <th class="px-4 py-3.5">Nama & NIP</th>
                            <th class="px-4 py-3.5">Email</th>
                            <th class="px-4 py-3.5">Divisi & Jabatan</th>
                            <th class="px-4 py-3.5">Role Saat Ini</th>
                            <th class="px-4 py-3.5 text-right">Ubah Hak Akses</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($karyawans as $k)
                            <tr class="hover:bg-blue-50/25 transition-colors">
                                <td class="px-4 py-3.5">
                                    <div class="font-bold text-gray-900">{{ $k->nama_lengkap }}</div>
                                    <div class="text-[11px] text-gray-400">NIP: {{ $k->kode_pegawai }}</div>
                                </td>
                                <td class="px-4 py-3.5 font-mono text-xs text-gray-700">
                                    {{ $k->email }}
                                </td>
                                <td class="px-4 py-3.5 text-xs">
                                    <span class="font-semibold text-gray-800">{{ $k->jabatan ?? '-' }}</span>
                                    <span class="block text-gray-400">{{ $k->divisi ?? '-' }}</span>
                                </td>
                                <td class="px-4 py-3.5 whitespace-nowrap">
                                    @if($k->posisi === 'superadmin')
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-extrabold bg-gradient-to-r from-amber-100 to-yellow-100 text-amber-800 border border-amber-300 shadow-sm">
                                            <i class="fa-solid fa-crown text-amber-600"></i>
                                            SUPERADMIN
                                        </span>
                                    @elseif($k->posisi === 'hr')
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-800 border border-blue-200">
                                            <i class="fa-solid fa-user-shield text-blue-600"></i>
                                            HR ADMIN
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-700">
                                            Karyawan
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3.5 text-right whitespace-nowrap">
                                    <form action="{{ route('superadmin.karyawan.update-role', $k->id) }}" method="POST" class="inline-flex items-center gap-2">
                                        @csrf
                                        @method('PUT')
                                        <select name="posisi" class="px-2.5 py-1.5 rounded-lg border border-gray-200 text-xs font-semibold focus:ring-2 focus:ring-[#00a2e9]/20 focus:border-[#00a2e9]">
                                            <option value="karyawan" {{ $k->posisi === 'karyawan' ? 'selected' : '' }}>Karyawan</option>
                                            <option value="hr" {{ $k->posisi === 'hr' ? 'selected' : '' }}>HR Admin</option>
                                            <option value="superadmin" {{ $k->posisi === 'superadmin' ? 'selected' : '' }}>Super Admin</option>
                                        </select>
                                        <button type="submit" class="px-3 py-1.5 rounded-lg bg-[#0F1245] hover:bg-[#161758] text-white text-xs font-semibold shadow-sm transition-all">
                                            Update
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-10 text-gray-400">
                                    Tidak ada data pengguna.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="pt-4">
                {{ $karyawans->links() }}
            </div>
        </div>

    </div>
</div>
@endsection
