{{-- views/hr/sunnah/rekap-divisi.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="flex min-h">
    @include('layouts.sidebar')
    <div class="flex-1 transition-all duration-300 md:ml-64 pt-4 sm:pt-6">
        <div class="p-3 sm:p-4 md:p-6">
            <!-- Header -->
            <div class="mb-4 sm:mb-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 sm:gap-4">
                <div>
                    <h1 class="text-lg sm:text-xl md:text-2xl font-bold font-['Montserrat'] text-[#161758]">🏆 Rekap Divisi 7SPS</h1>
                    <p class="text-xs sm:text-sm md:text-base text-[#27438D]">Rata-rata poin per anggota = total poin seluruh anggota ÷ jumlah anggota divisi</p>
                </div>
                <div class="flex flex-wrap gap-2 w-full sm:w-auto">
                    <a href="{{ route('hr.sunnah.index') }}"
                       class="flex-1 sm:flex-none text-center bg-gray-500 text-white px-3 sm:px-4 py-1.5 sm:py-2 rounded-lg hover:bg-gray-600 transition-colors duration-200 text-xs sm:text-sm">
                        📋 Monitoring
                    </a>
                    <a href="{{ route('hr.sunnah.rekap') }}"
                       class="flex-1 sm:flex-none text-center bg-[#00a2e9] text-white px-3 sm:px-4 py-1.5 sm:py-2 rounded-lg hover:bg-[#27438D] transition-colors duration-200 text-xs sm:text-sm">
                        📊 Rekap Karyawan
                    </a>
                    <a href="{{ route('hr.sunnah.rekapitulasi-karyawan') }}"
                       class="flex-1 sm:flex-none text-center bg-[#161758] text-white px-3 sm:px-4 py-1.5 sm:py-2 rounded-lg hover:bg-[#0F1245] transition-colors duration-200 text-xs sm:text-sm">
                        👥 Rekapitulasi
                    </a>
                </div>
            </div>

            <!-- Filter -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 hover:shadow-lg transition-all duration-300 p-3 sm:p-4 md:p-6 mb-4 sm:mb-6">
                <form action="{{ route('hr.sunnah.rekap-divisi') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4">
                    <div>
                        <label class="block text-xs sm:text-sm font-medium text-[#1B1B1B] mb-1">Tanggal Mulai</label>
                        <input type="date" name="start_date" value="{{ $startDate->format('Y-m-d') }}"
                               class="w-full px-3 sm:px-4 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                    </div>
                    <div>
                        <label class="block text-xs sm:text-sm font-medium text-[#1B1B1B] mb-1">Tanggal Selesai</label>
                        <input type="date" name="end_date" value="{{ $endDate->format('Y-m-d') }}"
                               class="w-full px-3 sm:px-4 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="w-full bg-[#27438D] text-white px-4 sm:px-6 py-2 rounded-lg hover:bg-[#161758] transition-colors duration-200 text-sm sm:text-base">
                            Tampilkan
                        </button>
                    </div>
                </form>
            </div>

            <!-- Informasi Periode -->
            <div class="bg-[#F5F5F5] text-[#1B1B1B] p-3 sm:p-4 rounded-lg mb-4 sm:mb-6 text-xs sm:text-sm">
                <strong>📅 Periode:</strong> {{ $startDate->format('d F Y') }} - {{ $endDate->format('d F Y') }}
            </div>

            <!-- Tabel Rekap Divisi -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 hover:shadow-lg transition-all duration-300 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-xs sm:text-sm">
                        <thead class="bg-[#161758] text-white">
                            <tr>
                                <th class="px-2 sm:px-3 md:px-4 py-1.5 sm:py-2 md:py-3 text-left font-semibold">Rank</th>
                                <th class="px-2 sm:px-3 md:px-4 py-1.5 sm:py-2 md:py-3 text-left font-semibold">Divisi</th>
                                <th class="px-2 sm:px-3 md:px-4 py-1.5 sm:py-2 md:py-3 text-left font-semibold">Anggota</th>
                                <th class="px-2 sm:px-3 md:px-4 py-1.5 sm:py-2 md:py-3 text-left font-semibold hidden sm:table-cell">Total Poin</th>
                                <th class="px-2 sm:px-3 md:px-4 py-1.5 sm:py-2 md:py-3 text-left font-semibold">Rata-rata</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($divisiRanking as $index => $row)
                            <tr class="border-b border-gray-200 hover:bg-[#F5F5F5]">
                                <td class="px-2 sm:px-3 md:px-4 py-1.5 sm:py-2 md:py-3 text-xs sm:text-sm font-semibold">
                                    @if($index === 0 && $row['rata_rata_poin'] > 0)
                                        🥇
                                    @elseif($index === 1 && $row['rata_rata_poin'] > 0)
                                        🥈
                                    @elseif($index === 2 && $row['rata_rata_poin'] > 0)
                                        🥉
                                    @else
                                        {{ $index + 1 }}
                                    @endif
                                </td>
                                <td class="px-2 sm:px-3 md:px-4 py-1.5 sm:py-2 md:py-3 text-xs sm:text-sm font-medium text-[#161758] break-words max-w-[100px] sm:max-w-none">
                                    {{ $row['divisi'] }}
                                </td>
                                <td class="px-2 sm:px-3 md:px-4 py-1.5 sm:py-2 md:py-3 text-xs sm:text-sm">{{ $row['jumlah_anggota'] }}</td>
                                <td class="px-2 sm:px-3 md:px-4 py-1.5 sm:py-2 md:py-3 text-xs sm:text-sm hidden sm:table-cell">{{ $row['total_poin'] }}</td>
                                <td class="px-2 sm:px-3 md:px-4 py-1.5 sm:py-2 md:py-3 text-xs sm:text-sm font-bold
                                    @if($index === 0 && $row['rata_rata_poin'] > 0) text-[#2E7D3E]
                                    @elseif($index === 1 && $row['rata_rata_poin'] > 0) text-[#00a2e9]
                                    @elseif($index === 2 && $row['rata_rata_poin'] > 0) text-[#FCC626]
                                    @else text-[#161758] @endif">
                                    {{ $row['rata_rata_poin'] }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-[#1B1B1B]">
                                    <div class="flex flex-col items-center">
                                        <svg class="w-12 sm:w-16 h-12 sm:h-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                        <p class="text-sm sm:text-base md:text-lg font-semibold">Belum ada data divisi untuk periode ini</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if($divisiRanking->count() > 0)
            <div class="mt-3 sm:mt-4 grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4">
                <div class="bg-[#2E7D3E] text-white rounded-lg shadow-md p-3 sm:p-4 text-center">
                    <p class="text-sm sm:text-base md:text-lg font-bold truncate">{{ $divisiRanking->first()['divisi'] ?? '-' }}</p>
                    <p class="text-[10px] sm:text-xs md:text-sm">🏆 Divisi Terbaik</p>
                    <p class="text-xs sm:text-sm mt-1">Rata-rata: {{ $divisiRanking->first()['rata_rata_poin'] ?? 0 }}</p>
                </div>
                <div class="bg-[#00a2e9] text-white rounded-lg shadow-md p-3 sm:p-4 text-center">
                    <p class="text-base sm:text-lg md:text-2xl font-bold">{{ $divisiRanking->sum('total_poin') }}</p>
                    <p class="text-[10px] sm:text-xs md:text-sm">Total Poin Semua Divisi</p>
                </div>
                <div class="bg-[#161758] text-white rounded-lg shadow-md p-3 sm:p-4 text-center">
                    <p class="text-base sm:text-lg md:text-2xl font-bold">{{ $divisiRanking->count() }}</p>
                    <p class="text-[10px] sm:text-xs md:text-sm">Total Divisi Aktif</p>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection