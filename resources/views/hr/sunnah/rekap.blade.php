@extends('layouts.app')

@section('content')
<div class="flex">
    @include('layouts.sidebar')
    <div class="ml-64 flex-1 p-6">
        <div class="mb-6 flex items-center justify-between flex-wrap gap-3">
            <div>
                <h1 class="text-2xl font-bold font-['Montserrat'] text-[#161758]">Rekap Poin Bulanan 7SPS</h1>
                <p class="text-[#27438D]">Total poin 7 Sunnah Plus Suprasional setiap karyawan per bulan</p>
            </div>
            <a href="{{ route('hr.sunnah.index') }}"
               class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600 transition-colors duration-200 text-sm">
                ← Kembali ke Monitoring Harian
            </a>
        </div>

        <!-- Filter Bulan/Tahun -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <form action="{{ route('hr.sunnah.rekap') }}" method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Bulan</label>
                    <select name="month" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                        @for($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" {{ (int) $month === $m ? 'selected' : '' }}>
                                {{ DateTime::createFromFormat('!m', $m)->format('F') }}
                            </option>
                        @endfor
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Tahun</label>
                    <select name="year" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                        @for($y = date('Y'); $y >= date('Y') - 5; $y--)
                            <option value="{{ $y }}" {{ (int) $year === $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="submit" class="bg-[#27438D] text-white px-6 py-2 rounded-lg hover:bg-[#161758] transition-colors duration-200 w-full">
                        Tampilkan
                    </button>
                </div>
            </form>
        </div>

        <!-- Ringkasan -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow-md p-4 text-center">
                <p class="text-2xl font-bold text-[#161758]">{{ $rekap->count() }}</p>
                <p class="text-sm text-[#1B1B1B]">Total Karyawan</p>
            </div>
            <div class="bg-[#00a2e9] text-white rounded-lg shadow-md p-4 text-center">
                <p class="text-2xl font-bold">{{ $totalKaryawanAktif }}</p>
                <p class="text-sm">Karyawan Sudah Mengisi</p>
            </div>
            <div class="bg-[#2E7D3E] text-white rounded-lg shadow-md p-4 text-center">
                <p class="text-2xl font-bold">{{ $totalPoinKeseluruhan }}</p>
                <p class="text-sm">Total Poin Seluruh Karyawan</p>
            </div>
        </div>

        <!-- Tabel Rekap -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[950px]">
                    <thead class="bg-[#F5F5F5]">
                        <tr>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-[#1B1B1B]">Rank</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-[#1B1B1B]">Nama Karyawan</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-[#1B1B1B]">Kode Pegawai</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-[#1B1B1B]">Divisi</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-[#1B1B1B]">Hari Mengisi</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-[#1B1B1B]">Total Poin</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-[#1B1B1B]">Rata-rata/Hari</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-[#1B1B1B]">Disetujui</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-[#1B1B1B]">Menunggu</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-[#1B1B1B]">Ditolak</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rekap as $index => $row)
                        <tr class="border-b border-gray-200 hover:bg-[#F5F5F5]">
                            <td class="px-4 py-3 text-sm font-semibold">
                                @if($index === 0 && $row['total_poin'] > 0)
                                    🥇
                                @elseif($index === 1 && $row['total_poin'] > 0)
                                    🥈
                                @elseif($index === 2 && $row['total_poin'] > 0)
                                    🥉
                                @else
                                    {{ $index + 1 }}
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <a href="{{ route('hr.sunnah.index', ['karyawan_id' => $row['karyawan_id'], 'month' => $month, 'year' => $year]) }}"
                                   class="text-[#161758] hover:text-[#00a2e9] font-medium">
                                    {{ $row['nama_lengkap'] }}
                                </a>
                            </td>
                            <td class="px-4 py-3 text-sm">{{ $row['kode_pegawai'] }}</td>
                            <td class="px-4 py-3 text-sm">{{ $row['divisi'] }}</td>
                            <td class="px-4 py-3 text-sm">{{ $row['total_hari'] }}</td>
                            <td class="px-4 py-3 text-sm font-bold text-[#161758]">{{ $row['total_poin'] }}</td>
                            <td class="px-4 py-3 text-sm">{{ $row['rata_rata'] }}</td>
                            <td class="px-4 py-3 text-sm text-[#2E7D3E] font-semibold">{{ $row['approved'] }}</td>
                            <td class="px-4 py-3 text-sm text-[#FCC626] font-semibold">{{ $row['pending'] }}</td>
                            <td class="px-4 py-3 text-sm text-[#ec1d1d] font-semibold">{{ $row['rejected'] }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="px-4 py-8 text-center text-[#1B1B1B]">
                                <div class="flex flex-col items-center">
                                    <svg class="w-16 h-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    <p class="text-lg font-semibold">Belum ada data karyawan</p>
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
@endsection
