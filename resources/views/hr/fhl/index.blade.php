{{-- views/hr/fhl/index.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="flex min-h-screen">
    @include('layouts.sidebar')
    <div class="flex-1 transition-all duration-300 md:ml-64 pt-6">
        <div class="p-3 sm:p-6">
            <div class="mb-6">
                <h1 class="text-xl sm:text-2xl font-bold font-['Montserrat'] text-[#161758]">FHL - Friday Healthy Lifestyle</h1>
                <p class="text-sm sm:text-base text-[#27438D]">Monitoring absensi kegiatan FHL</p>
            </div>

            <!-- Filter -->
            <div class="bg-white rounded-lg shadow-md p-4 sm:p-6 mb-6">
                <h2 class="text-base sm:text-lg font-semibold text-[#161758] mb-4">Filter Laporan</h2>
                <form action="{{ route('hr.fhl.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3 sm:gap-4">
                    <div>
                        <label class="block text-xs sm:text-sm font-medium text-[#1B1B1B] mb-1">Bulan</label>
                        <select name="month" class="w-full px-3 sm:px-4 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                            @for($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}" {{ request('month', date('m')) == $m ? 'selected' : '' }}>
                                    {{ DateTime::createFromFormat('!m', $m)->format('F') }}
                                </option>
                            @endfor
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs sm:text-sm font-medium text-[#1B1B1B] mb-1">Tahun</label>
                        <select name="year" class="w-full px-3 sm:px-4 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                            @for($y = date('Y'); $y >= date('Y')-5; $y--)
                                <option value="{{ $y }}" {{ request('year', date('Y')) == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs sm:text-sm font-medium text-[#1B1B1B] mb-1">Karyawan</label>
                        <select name="karyawan_id" class="w-full px-3 sm:px-4 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                            <option value="">Semua Karyawan</option>
                            @foreach($karyawans as $karyawan)
                                <option value="{{ $karyawan->id }}" {{ request('karyawan_id') == $karyawan->id ? 'selected' : '' }}>
                                    {{ $karyawan->nama_lengkap }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="w-full bg-[#27438D] text-white px-4 sm:px-6 py-2 rounded-lg hover:bg-[#161758] transition-colors duration-200 text-sm sm:text-base">
                            Filter
                        </button>
                    </div>
                </form>
            </div>

            <!-- Statistik -->
            <div class="bg-white rounded-lg shadow-md p-4 sm:p-6 mb-6">
                <h2 class="text-base sm:text-lg font-semibold text-[#161758] mb-4">Statistik FHL</h2>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4">
                    <div class="bg-[#F5F5F5] rounded-lg p-3 sm:p-4 text-center">
                        <p class="text-xl sm:text-2xl font-bold text-[#161758]">{{ $statistik['total_jumat'] }}</p>
                        <p class="text-xs sm:text-sm text-[#1B1B1B]">Total Jumat</p>
                    </div>
                    <div class="bg-[#2E7D3E] text-white rounded-lg p-3 sm:p-4 text-center">
                        <p class="text-xl sm:text-2xl font-bold">{{ $statistik['hadir'] }}</p>
                        <p class="text-xs sm:text-sm">Total Hadir</p>
                    </div>
                    <div class="bg-[#F5F5F5] rounded-lg p-3 sm:p-4 text-center">
                        <p class="text-xl sm:text-2xl font-bold text-[#161758]">{{ $statistik['total_jumat'] - $statistik['hadir'] }}</p>
                        <p class="text-xs sm:text-sm text-[#1B1B1B]">Belum Hadir</p>
                    </div>
                </div>
            </div>

            <!-- Tabel Absensi -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="overflow-x-auto -mx-4 sm:mx-0">
                    <div class="inline-block min-w-full align-middle">
                        <table class="min-w-[700px] sm:min-w-full">
                            <thead class="bg-[#F5F5F5]">
                                <tr>
                                    <th class="px-3 sm:px-4 py-2 sm:py-3 text-left text-xs sm:text-sm font-semibold text-[#1B1B1B]">No</th>
                                    <th class="px-3 sm:px-4 py-2 sm:py-3 text-left text-xs sm:text-sm font-semibold text-[#1B1B1B]">Karyawan</th>
                                    <th class="px-3 sm:px-4 py-2 sm:py-3 text-left text-xs sm:text-sm font-semibold text-[#1B1B1B]">Tanggal</th>
                                    <th class="px-3 sm:px-4 py-2 sm:py-3 text-left text-xs sm:text-sm font-semibold text-[#1B1B1B]">Check-in</th>
                                    <th class="px-3 sm:px-4 py-2 sm:py-3 text-left text-xs sm:text-sm font-semibold text-[#1B1B1B]">Status</th>
                                    <th class="px-3 sm:px-4 py-2 sm:py-3 text-left text-xs sm:text-sm font-semibold text-[#1B1B1B]">Bukti</th>
                                    <th class="px-3 sm:px-4 py-2 sm:py-3 text-left text-xs sm:text-sm font-semibold text-[#1B1B1B]">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($absensis as $absen)
                                <tr class="border-b border-gray-200 hover:bg-[#F5F5F5]">
                                    <td class="px-3 sm:px-4 py-2 sm:py-3 text-xs sm:text-sm">{{ $loop->iteration + ($absensis->currentPage() - 1) * $absensis->perPage() }}</td>
                                    <td class="px-3 sm:px-4 py-2 sm:py-3 text-xs sm:text-sm">{{ $absen->karyawan->nama_lengkap }}</td>
                                    <td class="px-3 sm:px-4 py-2 sm:py-3 text-xs sm:text-sm">{{ $absen->tanggal->format('d-m-Y') }}</td>
                                    <td class="px-3 sm:px-4 py-2 sm:py-3 text-xs sm:text-sm">{{ $absen->check_in ? Carbon\Carbon::parse($absen->check_in)->format('H:i:s') : '-' }}</td>
                                    <td class="px-3 sm:px-4 py-2 sm:py-3">
                                        <span class="px-2 py-1 rounded-full text-[10px] sm:text-xs font-medium bg-[#2E7D3E] text-white">
                                            {{ $absen->status }}
                                        </span>
                                    </td>
                                    <td class="px-3 sm:px-4 py-2 sm:py-3 text-xs sm:text-sm">
                                        @if($absen->foto_bukti)
                                            <a href="{{ Storage::url($absen->foto_bukti) }}" target="_blank"
                                               class="text-[#00a2e9] hover:text-[#27438D]">
                                                📷 Lihat
                                            </a>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="px-3 sm:px-4 py-2 sm:py-3 text-xs sm:text-sm">
                                        <a href="{{ route('hr.fhl.detail', $absen->id) }}"
                                           class="text-[#00a2e9] hover:text-[#27438D]">
                                            Detail
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-8 text-center text-[#1B1B1B]">
                                        <div class="flex flex-col items-center">
                                            <svg class="w-12 sm:w-16 h-12 sm:h-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                            <p class="text-base sm:text-lg font-semibold">Belum ada data absensi FHL</p>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="p-3 sm:p-4">
                    {{ $absensis->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
