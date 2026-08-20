{{-- views/hr/sunnah/rekap.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="flex min-h">
    @include('layouts.sidebar')
    <div class="flex-1 transition-all duration-300 md:ml-64 pt-4 sm:pt-6">
        <div class="p-3 sm:p-4 md:p-6">
            <!-- Header -->
            <div class="mb-4 sm:mb-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 sm:gap-4">
                <div>
                    <h1 class="text-lg sm:text-xl md:text-2xl font-bold font-['Montserrat'] text-[#161758]">📊 Rekap Karyawan 7SPS</h1>
                    <p class="text-xs sm:text-sm md:text-base text-[#27438D]">Total poin 7 Sunnah Plus Suprasional setiap karyawan per bulan</p>
                </div>
                <div class="flex flex-wrap gap-2 w-full sm:w-auto">
                    <a href="{{ route('hr.sunnah.index') }}"
                       class="flex-1 sm:flex-none text-center bg-gray-500 text-white px-3 sm:px-4 py-1.5 sm:py-2 rounded-lg hover:bg-gray-600 transition-colors duration-200 text-xs sm:text-sm">
                        📋 Monitoring
                    </a>
                    <a href="{{ route('hr.sunnah.rekap-divisi') }}"
                       class="flex-1 sm:flex-none text-center bg-[#2E7D3E] text-white px-3 sm:px-4 py-1.5 sm:py-2 rounded-lg hover:bg-[#1a5a2a] transition-colors duration-200 text-xs sm:text-sm">
                        🏆 Rekap Divisi
                    </a>
                </div>
            </div>

            <!-- Filter -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 hover:shadow-lg transition-all duration-300 p-3 sm:p-4 md:p-6 mb-4 sm:mb-6">
                <form action="{{ route('hr.sunnah.rekap') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
                    <div>
                        <label class="block text-xs sm:text-sm font-medium text-[#1B1B1B] mb-1">Bulan</label>
                        <select name="month" class="w-full px-3 sm:px-4 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                            @for($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}" {{ (int) $month === $m ? 'selected' : '' }}>
                                    {{ DateTime::createFromFormat('!m', $m)->format('F') }}
                                </option>
                            @endfor
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs sm:text-sm font-medium text-[#1B1B1B] mb-1">Tahun</label>
                        <select name="year" class="w-full px-3 sm:px-4 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                            @for($y = date('Y'); $y >= date('Y') - 5; $y--)
                                <option value="{{ $y }}" {{ (int) $year === $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs sm:text-sm font-medium text-[#1B1B1B] mb-1">Filter</label>
                        <select name="sudah_mengisi" class="w-full px-3 sm:px-4 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                            <option value="0" {{ !$filterSudahMengisi ? 'selected' : '' }}>Semua Karyawan</option>
                            <option value="1" {{ $filterSudahMengisi ? 'selected' : '' }}>Sudah Mengisi</option>
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="w-full bg-[#27438D] text-white px-4 sm:px-6 py-2 rounded-lg hover:bg-[#161758] transition-colors duration-200 text-sm sm:text-base">
                            Tampilkan
                        </button>
                    </div>
                </form>
            </div>

            <!-- Ringkasan -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4 mb-4 sm:mb-6">
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 hover:shadow-lg transition-all duration-300 p-3 sm:p-4 text-center">
                    <p class="text-base sm:text-lg md:text-2xl font-bold text-[#161758]">{{ $totalKaryawan }}</p>
                    <p class="text-[10px] sm:text-xs md:text-sm text-[#1B1B1B]">Total Karyawan</p>
                </div>
                <div class="bg-[#00a2e9] text-white rounded-lg shadow-md p-3 sm:p-4 text-center">
                    <p class="text-base sm:text-lg md:text-2xl font-bold">{{ $totalKaryawanAktif }}</p>
                    <p class="text-[10px] sm:text-xs md:text-sm">Karyawan Sudah Mengisi</p>
                </div>
                <div class="bg-[#2E7D3E] text-white rounded-lg shadow-md p-3 sm:p-4 text-center">
                    <p class="text-base sm:text-lg md:text-2xl font-bold">{{ $totalPoinBulanan }}</p>
                    <p class="text-[10px] sm:text-xs md:text-sm">Total Poin Bulan {{ DateTime::createFromFormat('!m', $month)->format('F') }} {{ $year }}</p>
                </div>
            </div>

            <!-- Tabel Rekap -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 hover:shadow-lg transition-all duration-300 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-xs sm:text-sm">
                        <thead class="bg-[#F5F5F5]">
                            <tr>
                                <th class="px-2 sm:px-3 md:px-4 py-1.5 sm:py-2 md:py-3 text-left font-semibold text-[#1B1B1B]">Rank</th>
                                <th class="px-2 sm:px-3 md:px-4 py-1.5 sm:py-2 md:py-3 text-left font-semibold text-[#1B1B1B]">Nama Karyawan</th>
                                <th class="px-2 sm:px-3 md:px-4 py-1.5 sm:py-2 md:py-3 text-left font-semibold text-[#1B1B1B] hidden sm:table-cell">Kode</th>
                                <th class="px-2 sm:px-3 md:px-4 py-1.5 sm:py-2 md:py-3 text-left font-semibold text-[#1B1B1B] hidden md:table-cell">Divisi</th>
                                <th class="px-2 sm:px-3 md:px-4 py-1.5 sm:py-2 md:py-3 text-left font-semibold text-[#1B1B1B]">Hari</th>
                                <th class="px-2 sm:px-3 md:px-4 py-1.5 sm:py-2 md:py-3 text-left font-semibold text-[#1B1B1B]">Poin</th>
                                <th class="px-2 sm:px-3 md:px-4 py-1.5 sm:py-2 md:py-3 text-left font-semibold text-[#1B1B1B] hidden lg:table-cell">Rata-rata</th>
                                <th class="px-2 sm:px-3 md:px-4 py-1.5 sm:py-2 md:py-3 text-left font-semibold text-[#1B1B1B] hidden lg:table-cell">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($rekap as $index => $row)
                            @php $rank = $rekap->firstItem() + $index; @endphp
                            <tr class="border-b border-gray-200 hover:bg-[#F5F5F5]">
                                <td class="px-2 sm:px-3 md:px-4 py-1.5 sm:py-2 md:py-3 text-xs sm:text-sm font-semibold">
                                    @if($rank === 1 && $row['total_poin'] > 0)
                                        🥇
                                    @elseif($rank === 2 && $row['total_poin'] > 0)
                                        🥈
                                    @elseif($rank === 3 && $row['total_poin'] > 0)
                                        🥉
                                    @else
                                        {{ $rank }}
                                    @endif
                                </td>
                                <td class="px-2 sm:px-3 md:px-4 py-1.5 sm:py-2 md:py-3 text-xs sm:text-sm break-words max-w-[100px] sm:max-w-none">
                                    <a href="{{ route('hr.sunnah.index', ['karyawan_id' => $row['karyawan_id'], 'start_date' => $year . '-' . $month . '-01', 'end_date' => $year . '-' . $month . '-' . date('t', strtotime($year . '-' . $month . '-01'))]) }}"
                                       class="text-[#161758] hover:text-[#00a2e9] font-medium">
                                        {{ $row['nama_lengkap'] }}
                                    </a>
                                </td>
                                <td class="px-2 sm:px-3 md:px-4 py-1.5 sm:py-2 md:py-3 text-xs sm:text-sm hidden sm:table-cell">{{ $row['kode_pegawai'] }}</td>
                                <td class="px-2 sm:px-3 md:px-4 py-1.5 sm:py-2 md:py-3 text-xs sm:text-sm hidden md:table-cell">{{ $row['divisi'] }}</td>
                                <td class="px-2 sm:px-3 md:px-4 py-1.5 sm:py-2 md:py-3 text-xs sm:text-sm">{{ $row['total_hari'] }}</td>
                                <td class="px-2 sm:px-3 md:px-4 py-1.5 sm:py-2 md:py-3 text-xs sm:text-sm font-bold text-[#161758]">{{ $row['total_poin'] }}</td>
                                <td class="px-2 sm:px-3 md:px-4 py-1.5 sm:py-2 md:py-3 text-xs sm:text-sm hidden lg:table-cell">{{ $row['rata_rata'] }}</td>
                                <td class="px-2 sm:px-3 md:px-4 py-1.5 sm:py-2 md:py-3 hidden lg:table-cell">
                                    @if($row['total_hari'] > 0)
                                        <span class="px-1.5 sm:px-2 py-0.5 sm:py-1 rounded-full text-[9px] sm:text-[10px] font-medium bg-[#2E7D3E] text-white">Aktif</span>
                                    @else
                                        <span class="px-1.5 sm:px-2 py-0.5 sm:py-1 rounded-full text-[9px] sm:text-[10px] font-medium bg-gray-300 text-gray-600">Belum</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="px-4 py-8 text-center text-[#1B1B1B]">
                                    <div class="flex flex-col items-center">
                                        <svg class="w-12 sm:w-16 h-12 sm:h-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                        <p class="text-sm sm:text-base md:text-lg font-semibold">Belum ada data karyawan</p>
                                        <p class="text-xs sm:text-sm text-gray-500 mt-1">Belum ada data 7SPS untuk periode ini</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if($rekap->total() > 0)
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 hover:shadow-lg transition-all duration-300 p-3 sm:p-4 mt-3 sm:mt-4">
                    {{ $rekap->onEachSide(1)->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection