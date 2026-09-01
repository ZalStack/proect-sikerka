@extends('layouts.app')

@section('content')
<div class="flex min-h">
    @include('layouts.sidebar')
    <div class="flex-1 transition-all duration-300 md:ml-64 pt-4 sm:pt-6">
        <div class="p-3 sm:p-4 md:p-6">

            {{-- ============ HEADER ============ --}}
            <div class="mb-4 sm:mb-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 sm:gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-2xl bg-gradient-to-br from-[#161758] to-[#27438D] flex items-center justify-center text-xl sm:text-2xl shadow-md shrink-0">
                        👥
                    </div>
                    <div>
                        <h1 class="text-lg sm:text-xl md:text-2xl font-bold font-['Montserrat'] text-[#161758] leading-tight">Rekapitulasi Karyawan 7SPS</h1>
                        <p class="text-xs sm:text-sm md:text-base text-[#27438D]">Data karyawan berdasarkan jenis kelamin &mdash; Laki-Laki & Perempuan</p>
                    </div>
                </div>
                <div class="flex flex-wrap gap-2 w-full sm:w-auto">
                    <a href="{{ route('hr.sunnah.index') }}"
                       class="flex-1 sm:flex-none inline-flex items-center justify-center gap-1.5 text-center bg-gray-500 text-white px-3 sm:px-4 py-2 rounded-xl hover:bg-gray-600 active:scale-95 transition-all duration-200 text-xs sm:text-sm font-medium shadow-sm">
                        📋 <span>Monitoring</span>
                    </a>
                    <a href="{{ route('hr.sunnah.rekap') }}"
                       class="flex-1 sm:flex-none inline-flex items-center justify-center gap-1.5 text-center bg-[#00a2e9] text-white px-3 sm:px-4 py-2 rounded-xl hover:bg-[#27438D] active:scale-95 transition-all duration-200 text-xs sm:text-sm font-medium shadow-sm">
                        📊 <span>Rekap Karyawan</span>
                    </a>
                    <a href="{{ route('hr.sunnah.rekap-divisi') }}"
                       class="flex-1 sm:flex-none inline-flex items-center justify-center gap-1.5 text-center bg-[#2E7D3E] text-white px-3 sm:px-4 py-2 rounded-xl hover:bg-[#1a5a2a] active:scale-95 transition-all duration-200 text-xs sm:text-sm font-medium shadow-sm">
                        🏆 <span>Rekap Divisi</span>
                    </a>
                </div>
            </div>

            {{-- ============ FILTER ============ --}}
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 hover:shadow-lg transition-all duration-300 p-3 sm:p-4 md:p-6 mb-4 sm:mb-6">
                <form action="{{ route('hr.sunnah.rekapitulasi-karyawan') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4">
                    <div>
                        <label class="block text-xs sm:text-sm font-medium text-[#1B1B1B] mb-1">Bulan</label>
                        <select name="month" class="w-full px-3 sm:px-4 py-2 text-sm sm:text-base border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#00a2e9] focus:border-transparent transition">
                            @for($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}" {{ (int) $month === $m ? 'selected' : '' }}>
                                    {{ DateTime::createFromFormat('!m', $m)->format('F') }}
                                </option>
                            @endfor
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs sm:text-sm font-medium text-[#1B1B1B] mb-1">Tahun</label>
                        <select name="year" class="w-full px-3 sm:px-4 py-2 text-sm sm:text-base border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#00a2e9] focus:border-transparent transition">
                            @for($y = date('Y'); $y >= date('Y') - 5; $y--)
                                <option value="{{ $y }}" {{ (int) $year === $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="w-full bg-[#27438D] text-white px-4 sm:px-6 py-2 rounded-xl hover:bg-[#161758] active:scale-95 transition-all duration-200 text-sm sm:text-base font-medium shadow-sm">
                            Tampilkan
                        </button>
                    </div>
                </form>
            </div>

            {{-- ============ RINGKASAN UMUM ============ --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 sm:gap-3 md:gap-4 mb-4 sm:mb-6">
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 hover:shadow-lg transition-all duration-300 p-3 sm:p-4 text-center">
                    <p class="text-base sm:text-lg md:text-2xl font-bold text-[#161758]">{{ $totalKaryawan }}</p>
                    <p class="text-[10px] sm:text-xs md:text-sm text-[#1B1B1B] mt-0.5">👥 Total Karyawan</p>
                </div>
                <div class="bg-[#27438D] text-white rounded-2xl shadow-md p-3 sm:p-4 text-center hover:shadow-lg transition-shadow duration-200">
                    <p class="text-base sm:text-lg md:text-2xl font-bold">{{ $totalLakiLaki }}</p>
                    <p class="text-[10px] sm:text-xs md:text-sm mt-0.5">👨 Laki-Laki</p>
                </div>
                <div class="bg-[#ec1d1d] text-white rounded-2xl shadow-md p-3 sm:p-4 text-center hover:shadow-lg transition-shadow duration-200">
                    <p class="text-base sm:text-lg md:text-2xl font-bold">{{ $totalPerempuan }}</p>
                    <p class="text-[10px] sm:text-xs md:text-sm mt-0.5">👩 Perempuan</p>
                </div>
                <div class="bg-[#00a2e9] text-white rounded-2xl shadow-md p-3 sm:p-4 text-center hover:shadow-lg transition-shadow duration-200">
                    <p class="text-base sm:text-lg md:text-2xl font-bold">{{ $totalPoinSemua }}</p>
                    <p class="text-[10px] sm:text-xs md:text-sm mt-0.5">⭐ Total Poin</p>
                </div>
            </div>

            {{-- ============ TOP PERFORMERS ============ --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4 md:gap-6 mb-6 sm:mb-8">
                {{-- TOP LAKI-LAKI --}}
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 hover:shadow-lg transition-all duration-300 overflow-hidden">
                    <div class="bg-gradient-to-r from-[#27438D] to-[#161758] text-white px-4 sm:px-6 py-3 sm:py-4 flex items-center gap-3">
                        <span class="text-2xl sm:text-3xl">👨</span>
                        <div>
                            <h2 class="text-sm sm:text-base md:text-lg font-bold">Laki-Laki Terbaik</h2>
                            <p class="text-[10px] sm:text-xs text-white/80">Poin Tertinggi Bulan {{ DateTime::createFromFormat('!m', $month)->format('F') }} {{ $year }}</p>
                        </div>
                    </div>
                    <div class="p-4 sm:p-6">
                        @if($topLakiLaki && $topLakiLaki['total_poin'] > 0)
                            <div class="flex items-center gap-3 sm:gap-4">
                                <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-full bg-gradient-to-br from-[#27438D] to-[#00a2e9] flex items-center justify-center text-white text-xl sm:text-2xl font-bold shadow-lg shrink-0">
                                    {{ strtoupper(substr($topLakiLaki['nama_lengkap'], 0, 1)) }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <a href="{{ route('hr.sunnah.index', ['karyawan_id' => $topLakiLaki['karyawan_id']]) }}"
                                       class="text-base sm:text-lg md:text-xl font-bold text-[#161758] hover:text-[#00a2e9] transition-colors duration-200 truncate block">
                                        {{ $topLakiLaki['nama_lengkap'] }}
                                    </a>
                                    <p class="text-xs sm:text-sm text-[#27438D]">{{ $topLakiLaki['kode_pegawai'] }} &bull; {{ $topLakiLaki['divisi'] }}</p>
                                </div>
                                <div class="text-right shrink-0">
                                    <div class="bg-[#2E7D3E] text-white px-3 sm:px-4 py-1.5 sm:py-2 rounded-xl shadow-md">
                                        <p class="text-lg sm:text-xl md:text-2xl font-bold">{{ $topLakiLaki['total_poin'] }}</p>
                                        <p class="text-[9px] sm:text-[10px]">poin</p>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-3 sm:mt-4 grid grid-cols-3 gap-2 sm:gap-3">
                                <div class="bg-[#F5F5F5] rounded-xl p-2 sm:p-3 text-center">
                                    <p class="text-sm sm:text-base font-bold text-[#161758]">{{ $topLakiLaki['total_hari'] }}</p>
                                    <p class="text-[9px] sm:text-[10px] text-[#27438D]">Hari Aktif</p>
                                </div>
                                <div class="bg-[#F5F5F5] rounded-xl p-2 sm:p-3 text-center">
                                    <p class="text-sm sm:text-base font-bold text-[#161758]">{{ $topLakiLaki['rata_rata'] }}</p>
                                    <p class="text-[9px] sm:text-[10px] text-[#27438D]">Rata-rata</p>
                                </div>
                                <div class="bg-[#F5F5F5] rounded-xl p-2 sm:p-3 text-center">
                                    <p class="text-sm sm:text-base font-bold text-[#2E7D3E]">{{ $topLakiLaki['approved'] }}</p>
                                    <p class="text-[9px] sm:text-[10px] text-[#27438D]">Disetujui</p>
                                </div>
                            </div>
                        @else
                            <div class="flex flex-col items-center py-4 sm:py-6 text-gray-400">
                                <svg class="w-10 h-10 sm:w-12 sm:h-12 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                <p class="text-sm font-medium">Belum ada data</p>
                                <p class="text-xs text-gray-400">Laki-laki belum mengisi 7SPS</p>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- TOP PEREMPUAN --}}
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 hover:shadow-lg transition-all duration-300 overflow-hidden">
                    <div class="bg-gradient-to-r from-[#ec1d1d] to-[#c41230] text-white px-4 sm:px-6 py-3 sm:py-4 flex items-center gap-3">
                        <span class="text-2xl sm:text-3xl">👩</span>
                        <div>
                            <h2 class="text-sm sm:text-base md:text-lg font-bold">Perempuan Terbaik</h2>
                            <p class="text-[10px] sm:text-xs text-white/80">Poin Tertinggi Bulan {{ DateTime::createFromFormat('!m', $month)->format('F') }} {{ $year }}</p>
                        </div>
                    </div>
                    <div class="p-4 sm:p-6">
                        @if($topPerempuan && $topPerempuan['total_poin'] > 0)
                            <div class="flex items-center gap-3 sm:gap-4">
                                <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-full bg-gradient-to-br from-[#ec1d1d] to-[#FCC626] flex items-center justify-center text-white text-xl sm:text-2xl font-bold shadow-lg shrink-0">
                                    {{ strtoupper(substr($topPerempuan['nama_lengkap'], 0, 1)) }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <a href="{{ route('hr.sunnah.index', ['karyawan_id' => $topPerempuan['karyawan_id']]) }}"
                                       class="text-base sm:text-lg md:text-xl font-bold text-[#161758] hover:text-[#00a2e9] transition-colors duration-200 truncate block">
                                        {{ $topPerempuan['nama_lengkap'] }}
                                    </a>
                                    <p class="text-xs sm:text-sm text-[#27438D]">{{ $topPerempuan['kode_pegawai'] }} &bull; {{ $topPerempuan['divisi'] }}</p>
                                </div>
                                <div class="text-right shrink-0">
                                    <div class="bg-[#ec1d1d] text-white px-3 sm:px-4 py-1.5 sm:py-2 rounded-xl shadow-md">
                                        <p class="text-lg sm:text-xl md:text-2xl font-bold">{{ $topPerempuan['total_poin'] }}</p>
                                        <p class="text-[9px] sm:text-[10px]">poin</p>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-3 sm:mt-4 grid grid-cols-3 gap-2 sm:gap-3">
                                <div class="bg-[#F5F5F5] rounded-xl p-2 sm:p-3 text-center">
                                    <p class="text-sm sm:text-base font-bold text-[#161758]">{{ $topPerempuan['total_hari'] }}</p>
                                    <p class="text-[9px] sm:text-[10px] text-[#27438D]">Hari Aktif</p>
                                </div>
                                <div class="bg-[#F5F5F5] rounded-xl p-2 sm:p-3 text-center">
                                    <p class="text-sm sm:text-base font-bold text-[#161758]">{{ $topPerempuan['rata_rata'] }}</p>
                                    <p class="text-[9px] sm:text-[10px] text-[#27438D]">Rata-rata</p>
                                </div>
                                <div class="bg-[#F5F5F5] rounded-xl p-2 sm:p-3 text-center">
                                    <p class="text-sm sm:text-base font-bold text-[#2E7D3E]">{{ $topPerempuan['approved'] }}</p>
                                    <p class="text-[9px] sm:text-[10px] text-[#27438D]">Disetujui</p>
                                </div>
                            </div>
                        @else
                            <div class="flex flex-col items-center py-4 sm:py-6 text-gray-400">
                                <svg class="w-10 h-10 sm:w-12 sm:h-12 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                <p class="text-sm font-medium">Belum ada data</p>
                                <p class="text-xs text-gray-400">Perempuan belum mengisi 7SPS</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- ============ STATISTIK RINGKASAN PER GENDER ============ --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4 md:gap-6 mb-6 sm:mb-8">
                {{-- Laki-Laki --}}
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 hover:shadow-lg transition-all duration-300 overflow-hidden">
                    <div class="bg-[#27438D] text-white px-4 sm:px-5 py-2.5 sm:py-3 flex items-center justify-between">
                        <h3 class="text-sm sm:text-base font-bold flex items-center gap-2">
                            <span>👨</span> Laki-Laki
                        </h3>
                        <span class="bg-white/20 px-2.5 py-0.5 rounded-full text-xs font-medium">{{ $totalLakiLaki }} orang</span>
                    </div>
                    <div class="p-3 sm:p-4">
                        <div class="grid grid-cols-2 gap-2 sm:gap-3">
                            <div class="bg-[#F5F5F5] rounded-xl p-2.5 sm:p-3 text-center">
                                <p class="text-base sm:text-lg font-bold text-[#27438D]">{{ $totalPoinLakiLaki }}</p>
                                <p class="text-[9px] sm:text-[10px] text-[#27438D]">Total Poin</p>
                            </div>
                            <div class="bg-[#F5F5F5] rounded-xl p-2.5 sm:p-3 text-center">
                                <p class="text-base sm:text-lg font-bold text-[#27438D]">{{ $rataRataLakiLaki }}</p>
                                <p class="text-[9px] sm:text-[10px] text-[#27438D]">Rata-rata Poin</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Perempuan --}}
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 hover:shadow-lg transition-all duration-300 overflow-hidden">
                    <div class="bg-[#ec1d1d] text-white px-4 sm:px-5 py-2.5 sm:py-3 flex items-center justify-between">
                        <h3 class="text-sm sm:text-base font-bold flex items-center gap-2">
                            <span>👩</span> Perempuan
                        </h3>
                        <span class="bg-white/20 px-2.5 py-0.5 rounded-full text-xs font-medium">{{ $totalPerempuan }} orang</span>
                    </div>
                    <div class="p-3 sm:p-4">
                        <div class="grid grid-cols-2 gap-2 sm:gap-3">
                            <div class="bg-[#F5F5F5] rounded-xl p-2.5 sm:p-3 text-center">
                                <p class="text-base sm:text-lg font-bold text-[#ec1d1d]">{{ $totalPoinPerempuan }}</p>
                                <p class="text-[9px] sm:text-[10px] text-[#ec1d1d]">Total Poin</p>
                            </div>
                            <div class="bg-[#F5F5F5] rounded-xl p-2.5 sm:p-3 text-center">
                                <p class="text-base sm:text-lg font-bold text-[#ec1d1d]">{{ $rataRataPerempuan }}</p>
                                <p class="text-[9px] sm:text-[10px] text-[#ec1d1d]">Rata-rata Poin</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ============ TABEL LAKI-LAKI ============ --}}
            <div class="mb-6 sm:mb-8">
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 hover:shadow-lg transition-all duration-300 overflow-hidden">
                    <div class="bg-gradient-to-r from-[#27438D] to-[#161758] text-white px-4 sm:px-6 py-3 sm:py-4 flex items-center justify-between">
                        <h3 class="text-sm sm:text-base md:text-lg font-bold flex items-center gap-2">
                            <span>👨</span> Daftar Laki-Laki
                        </h3>
                        <span class="bg-white/20 px-2.5 sm:px-3 py-1 rounded-full text-[10px] sm:text-xs font-medium">{{ $totalLakiLaki }} data</span>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-xs sm:text-sm">
                            <thead class="bg-[#F5F5F5]">
                                <tr>
                                    <th class="px-2 sm:px-3 md:px-4 py-2 sm:py-3 text-left font-semibold text-[#1B1B1B]">Rank</th>
                                    <th class="px-2 sm:px-3 md:px-4 py-2 sm:py-3 text-left font-semibold text-[#1B1B1B]">Nama Karyawan</th>
                                    <th class="px-2 sm:px-3 md:px-4 py-2 sm:py-3 text-left font-semibold text-[#1B1B1B] hidden sm:table-cell">Kode</th>
                                    <th class="px-2 sm:px-3 md:px-4 py-2 sm:py-3 text-left font-semibold text-[#1B1B1B] hidden md:table-cell">Divisi</th>
                                    <th class="px-2 sm:px-3 md:px-4 py-2 sm:py-3 text-left font-semibold text-[#1B1B1B]">Hari</th>
                                    <th class="px-2 sm:px-3 md:px-4 py-2 sm:py-3 text-left font-semibold text-[#1B1B1B]">Poin</th>
                                    <th class="px-2 sm:px-3 md:px-4 py-2 sm:py-3 text-left font-semibold text-[#1B1B1B] hidden lg:table-cell">Rata-rata</th>
                                    <th class="px-2 sm:px-3 md:px-4 py-2 sm:py-3 text-left font-semibold text-[#1B1B1B] hidden lg:table-cell">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($lakiLaki->sortByDesc('total_poin') as $index => $row)
                                <tr class="border-b border-gray-200 hover:bg-[#F5F5F5] transition-colors duration-150">
                                    <td class="px-2 sm:px-3 md:px-4 py-2 sm:py-3 font-semibold">
                                        @if($index === 0 && $row['total_poin'] > 0)
                                            <span class="inline-flex items-center justify-center w-6 h-6 sm:w-7 sm:h-7 rounded-full bg-[#FCC626] text-[#1B1B1B] text-xs sm:text-sm font-bold shadow-sm">🥇</span>
                                        @elseif($index === 1 && $row['total_poin'] > 0)
                                            <span class="inline-flex items-center justify-center w-6 h-6 sm:w-7 sm:h-7 rounded-full bg-gray-300 text-[#1B1B1B] text-xs sm:text-sm font-bold shadow-sm">🥈</span>
                                        @elseif($index === 2 && $row['total_poin'] > 0)
                                            <span class="inline-flex items-center justify-center w-6 h-6 sm:w-7 sm:h-7 rounded-full bg-amber-600 text-white text-xs sm:text-sm font-bold shadow-sm">🥉</span>
                                        @else
                                            {{ $index + 1 }}
                                        @endif
                                    </td>
                                    <td class="px-2 sm:px-3 md:px-4 py-2 sm:py-3">
                                        <a href="{{ route('hr.sunnah.index', ['karyawan_id' => $row['karyawan_id']]) }}"
                                           class="text-[#161758] hover:text-[#00a2e9] font-medium transition-colors duration-200 break-words max-w-[100px] sm:max-w-none">
                                            {{ $row['nama_lengkap'] }}
                                        </a>
                                    </td>
                                    <td class="px-2 sm:px-3 md:px-4 py-2 sm:py-3 text-[#1B1B1B] hidden sm:table-cell">{{ $row['kode_pegawai'] }}</td>
                                    <td class="px-2 sm:px-3 md:px-4 py-2 sm:py-3 text-[#1B1B1B] hidden md:table-cell">{{ $row['divisi'] }}</td>
                                    <td class="px-2 sm:px-3 md:px-4 py-2 sm:py-3 text-[#1B1B1B]">{{ $row['total_hari'] }}</td>
                                    <td class="px-2 sm:px-3 md:px-4 py-2 sm:py-3 font-bold text-[#161758]">{{ $row['total_poin'] }}</td>
                                    <td class="px-2 sm:px-3 md:px-4 py-2 sm:py-3 text-[#1B1B1B] hidden lg:table-cell">{{ $row['rata_rata'] }}</td>
                                    <td class="px-2 sm:px-3 md:px-4 py-2 sm:py-3 hidden lg:table-cell">
                                        @if($row['total_hari'] > 0)
                                            <span class="px-2 py-0.5 sm:py-1 rounded-full text-[9px] sm:text-[10px] font-medium bg-[#2E7D3E] text-white">Aktif</span>
                                        @else
                                            <span class="px-2 py-0.5 sm:py-1 rounded-full text-[9px] sm:text-[10px] font-medium bg-gray-300 text-gray-600">Belum</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="px-4 py-8 text-center text-gray-400">
                                        <div class="flex flex-col items-center">
                                            <svg class="w-10 h-10 sm:w-12 sm:h-12 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                            </svg>
                                            <p class="text-sm font-medium">Belum ada data laki-laki</p>
                                            <p class="text-xs text-gray-400 mt-1">Laki-laki belum mengisi 7SPS untuk periode ini</p>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- ============ TABEL PEREMPUAN ============ --}}
            <div>
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 hover:shadow-lg transition-all duration-300 overflow-hidden">
                    <div class="bg-gradient-to-r from-[#ec1d1d] to-[#c41230] text-white px-4 sm:px-6 py-3 sm:py-4 flex items-center justify-between">
                        <h3 class="text-sm sm:text-base md:text-lg font-bold flex items-center gap-2">
                            <span>👩</span> Daftar Perempuan
                        </h3>
                        <span class="bg-white/20 px-2.5 sm:px-3 py-1 rounded-full text-[10px] sm:text-xs font-medium">{{ $totalPerempuan }} data</span>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-xs sm:text-sm">
                            <thead class="bg-[#F5F5F5]">
                                <tr>
                                    <th class="px-2 sm:px-3 md:px-4 py-2 sm:py-3 text-left font-semibold text-[#1B1B1B]">Rank</th>
                                    <th class="px-2 sm:px-3 md:px-4 py-2 sm:py-3 text-left font-semibold text-[#1B1B1B]">Nama Karyawan</th>
                                    <th class="px-2 sm:px-3 md:px-4 py-2 sm:py-3 text-left font-semibold text-[#1B1B1B] hidden sm:table-cell">Kode</th>
                                    <th class="px-2 sm:px-3 md:px-4 py-2 sm:py-3 text-left font-semibold text-[#1B1B1B] hidden md:table-cell">Divisi</th>
                                    <th class="px-2 sm:px-3 md:px-4 py-2 sm:py-3 text-left font-semibold text-[#1B1B1B]">Hari</th>
                                    <th class="px-2 sm:px-3 md:px-4 py-2 sm:py-3 text-left font-semibold text-[#1B1B1B]">Poin</th>
                                    <th class="px-2 sm:px-3 md:px-4 py-2 sm:py-3 text-left font-semibold text-[#1B1B1B] hidden lg:table-cell">Rata-rata</th>
                                    <th class="px-2 sm:px-3 md:px-4 py-2 sm:py-3 text-left font-semibold text-[#1B1B1B] hidden lg:table-cell">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($perempuan->sortByDesc('total_poin') as $index => $row)
                                <tr class="border-b border-gray-200 hover:bg-[#F5F5F5] transition-colors duration-150">
                                    <td class="px-2 sm:px-3 md:px-4 py-2 sm:py-3 font-semibold">
                                        @if($index === 0 && $row['total_poin'] > 0)
                                            <span class="inline-flex items-center justify-center w-6 h-6 sm:w-7 sm:h-7 rounded-full bg-[#FCC626] text-[#1B1B1B] text-xs sm:text-sm font-bold shadow-sm">🥇</span>
                                        @elseif($index === 1 && $row['total_poin'] > 0)
                                            <span class="inline-flex items-center justify-center w-6 h-6 sm:w-7 sm:h-7 rounded-full bg-gray-300 text-[#1B1B1B] text-xs sm:text-sm font-bold shadow-sm">🥈</span>
                                        @elseif($index === 2 && $row['total_poin'] > 0)
                                            <span class="inline-flex items-center justify-center w-6 h-6 sm:w-7 sm:h-7 rounded-full bg-amber-600 text-white text-xs sm:text-sm font-bold shadow-sm">🥉</span>
                                        @else
                                            {{ $index + 1 }}
                                        @endif
                                    </td>
                                    <td class="px-2 sm:px-3 md:px-4 py-2 sm:py-3">
                                        <a href="{{ route('hr.sunnah.index', ['karyawan_id' => $row['karyawan_id']]) }}"
                                           class="text-[#161758] hover:text-[#00a2e9] font-medium transition-colors duration-200 break-words max-w-[100px] sm:max-w-none">
                                            {{ $row['nama_lengkap'] }}
                                        </a>
                                    </td>
                                    <td class="px-2 sm:px-3 md:px-4 py-2 sm:py-3 text-[#1B1B1B] hidden sm:table-cell">{{ $row['kode_pegawai'] }}</td>
                                    <td class="px-2 sm:px-3 md:px-4 py-2 sm:py-3 text-[#1B1B1B] hidden md:table-cell">{{ $row['divisi'] }}</td>
                                    <td class="px-2 sm:px-3 md:px-4 py-2 sm:py-3 text-[#1B1B1B]">{{ $row['total_hari'] }}</td>
                                    <td class="px-2 sm:px-3 md:px-4 py-2 sm:py-3 font-bold text-[#161758]">{{ $row['total_poin'] }}</td>
                                    <td class="px-2 sm:px-3 md:px-4 py-2 sm:py-3 text-[#1B1B1B] hidden lg:table-cell">{{ $row['rata_rata'] }}</td>
                                    <td class="px-2 sm:px-3 md:px-4 py-2 sm:py-3 hidden lg:table-cell">
                                        @if($row['total_hari'] > 0)
                                            <span class="px-2 py-0.5 sm:py-1 rounded-full text-[9px] sm:text-[10px] font-medium bg-[#2E7D3E] text-white">Aktif</span>
                                        @else
                                            <span class="px-2 py-0.5 sm:py-1 rounded-full text-[9px] sm:text-[10px] font-medium bg-gray-300 text-gray-600">Belum</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="px-4 py-8 text-center text-gray-400">
                                        <div class="flex flex-col items-center">
                                            <svg class="w-10 h-10 sm:w-12 sm:h-12 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                            </svg>
                                            <p class="text-sm font-medium">Belum ada data perempuan</p>
                                            <p class="text-xs text-gray-400 mt-1">Perempuan belum mengisi 7SPS untuk periode ini</p>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
