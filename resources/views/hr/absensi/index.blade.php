{{-- views/hr/absensi/index.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="flex min-h-screen bg-gray-50">
    @include('layouts.sidebar')
    <div class="flex-1 transition-all duration-300 md:ml-64 pt-6 w-full">
        <div class="p-3 sm:p-6">
            <div class="mb-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 sm:gap-4">
                <div>
                    <h1 class="text-xl sm:text-2xl font-bold font-['Montserrat'] text-[#161758]">Data Absensi Karyawan</h1>
                    <p class="text-sm sm:text-base text-[#27438D]">Rekap dan pantau kehadiran seluruh karyawan</p>
                </div>
                <a href="{{ route('hr.absensi.export', request()->query()) }}"
                   class="w-full sm:w-auto text-center bg-[#2E7D3E] text-white px-4 sm:px-5 py-2 rounded-lg hover:bg-[#009a4b] transition-colors duration-200 text-sm font-semibold">
                    ⬇️ Export Excel/CSV
                </a>
            </div>

            @if(session('success'))
            <div class="bg-[#2E7D3E]/10 border border-[#2E7D3E] text-[#2E7D3E] px-4 py-3 rounded-lg mb-4 text-sm">
                {{ session('success') }}
            </div>
            @endif

            <!-- Ringkasan / Chart -->
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-4 mb-6">
                <div class="lg:col-span-1 bg-white rounded-lg shadow-md p-3 sm:p-6">
                    <h3 class="text-base sm:text-lg font-semibold text-[#161758] mb-4">Ringkasan Status</h3>
                    <canvas id="chartAbsensi" height="220"></canvas>
                </div>
                <div class="lg:col-span-3 grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
                    <div class="bg-white rounded-lg shadow-md p-3 sm:p-4 text-center">
                        <p class="text-xs sm:text-sm text-[#1B1B1B]">Total</p>
                        <p class="text-xl sm:text-2xl font-bold text-[#161758]">{{ $chartData['total'] ?? 0 }}</p>
                    </div>
                    <div class="bg-white rounded-lg shadow-md p-3 sm:p-4 text-center">
                        <p class="text-xs sm:text-sm text-[#1B1B1B]">Hadir</p>
                        <p class="text-xl sm:text-2xl font-bold text-[#2E7D3E]">{{ $chartData['hadir'] ?? 0 }}</p>
                    </div>
                    <div class="bg-white rounded-lg shadow-md p-3 sm:p-4 text-center">
                        <p class="text-xs sm:text-sm text-[#1B1B1B]">Izin</p>
                        <p class="text-xl sm:text-2xl font-bold text-[#FCC626]">{{ $chartData['izin'] ?? 0 }}</p>
                    </div>
                    <div class="bg-white rounded-lg shadow-md p-3 sm:p-4 text-center">
                        <p class="text-xs sm:text-sm text-[#1B1B1B]">Sakit</p>
                        <p class="text-xl sm:text-2xl font-bold text-[#00a2e9]">{{ $chartData['sakit'] ?? 0 }}</p>
                    </div>
                    <div class="bg-white rounded-lg shadow-md p-3 sm:p-4 text-center">
                        <p class="text-xs sm:text-sm text-[#1B1B1B]">Alpha</p>
                        <p class="text-xl sm:text-2xl font-bold text-[#ec1d1d]">{{ $chartData['alpha'] ?? 0 }}</p>
                    </div>
                    <div class="bg-white rounded-lg shadow-md p-3 sm:p-4 text-center">
                        <p class="text-xs sm:text-sm text-[#1B1B1B]">Valid Lokasi</p>
                        <p class="text-xl sm:text-2xl font-bold text-[#27438D]">{{ $chartData['valid_location'] ?? 0 }}</p>
                    </div>
                </div>
            </div>

            <!-- Filter -->
            <div class="bg-white rounded-lg shadow-md p-3 sm:p-6 mb-6">
                <h3 class="text-base sm:text-lg font-semibold text-[#161758] mb-4">Filter Data</h3>
                <form action="{{ route('hr.absensi.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 items-end">
                    <div>
                        <label class="block text-xs sm:text-sm font-medium text-[#1B1B1B] mb-1">Dari Tanggal</label>
                        <input type="date" name="start_date" value="{{ request('start_date') }}"
                               class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                    </div>
                    <div>
                        <label class="block text-xs sm:text-sm font-medium text-[#1B1B1B] mb-1">Sampai Tanggal</label>
                        <input type="date" name="end_date" value="{{ request('end_date') }}"
                               class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                    </div>
                    <div>
                        <label class="block text-xs sm:text-sm font-medium text-[#1B1B1B] mb-1">Bulan</label>
                        <select name="month" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                            <option value="">Semua</option>
                            @foreach(range(1, 12) as $m)
                                <option value="{{ $m }}" {{ request('month') == $m ? 'selected' : '' }}>
                                    {{ \Carbon\Carbon::create()->month($m)->locale('id')->isoFormat('MMMM') }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs sm:text-sm font-medium text-[#1B1B1B] mb-1">Tahun</label>
                        <select name="year" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                            <option value="">Semua</option>
                            @foreach(range(now()->year, now()->year - 5) as $y)
                                <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs sm:text-sm font-medium text-[#1B1B1B] mb-1">Karyawan</label>
                        <select name="karyawan_id" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                            <option value="">Semua Karyawan</option>
                            @foreach($karyawans as $k)
                                <option value="{{ $k->id }}" {{ request('karyawan_id') == $k->id ? 'selected' : '' }}>
                                    {{ $k->nama_lengkap }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="sm:col-span-2 lg:col-span-5 flex flex-wrap gap-2">
                        <button type="submit"
                                class="flex-1 sm:flex-none bg-[#27438D] text-white px-4 sm:px-6 py-2 rounded-lg hover:bg-[#161758] transition-colors duration-200 text-sm">
                            🔍 Terapkan Filter
                        </button>
                        <a href="{{ route('hr.absensi.index') }}"
                           class="flex-1 sm:flex-none bg-gray-400 text-white px-4 sm:px-6 py-2 rounded-lg hover:bg-gray-500 transition-colors duration-200 text-sm text-center">
                            Reset
                        </a>
                    </div>
                </form>
            </div>

            <!-- Tabel Data Absensi -->
            <div class="bg-white rounded-lg shadow-md p-3 sm:p-6">
                <h3 class="text-base sm:text-lg font-semibold text-[#161758] mb-4">Daftar Absensi</h3>
                <div class="overflow-x-auto -mx-3 sm:mx-0">
                    <div class="inline-block min-w-full align-middle">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-[#F5F5F5]">
                                <tr>
                                    <th class="px-2 sm:px-4 py-2 text-left text-xs font-semibold text-[#1B1B1B]">No</th>
                                    <th class="px-2 sm:px-4 py-2 text-left text-xs font-semibold text-[#1B1B1B]">Nama Karyawan</th>
                                    <th class="px-2 sm:px-4 py-2 text-left text-xs font-semibold text-[#1B1B1B] hidden sm:table-cell">Tanggal</th>
                                    <th class="px-2 sm:px-4 py-2 text-left text-xs font-semibold text-[#1B1B1B] hidden md:table-cell">Check-in</th>
                                    <th class="px-2 sm:px-4 py-2 text-left text-xs font-semibold text-[#1B1B1B] hidden md:table-cell">Check-out</th>
                                    <th class="px-2 sm:px-4 py-2 text-left text-xs font-semibold text-[#1B1B1B] hidden lg:table-cell">Kantor</th>
                                    <th class="px-2 sm:px-4 py-2 text-left text-xs font-semibold text-[#1B1B1B]">Status</th>
                                    <th class="px-2 sm:px-4 py-2 text-left text-xs font-semibold text-[#1B1B1B] hidden md:table-cell">Lokasi</th>
                                    <th class="px-2 sm:px-4 py-2 text-left text-xs font-semibold text-[#1B1B1B] hidden lg:table-cell">Jarak</th>
                                    <th class="px-2 sm:px-4 py-2 text-left text-xs font-semibold text-[#1B1B1B]">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @forelse($absensis as $i => $absen)
                                <tr class="hover:bg-gray-50 transition-colors duration-150">
                                    <td class="px-2 sm:px-4 py-2 text-xs">{{ $absensis->firstItem() + $i }}</td>
                                    <td class="px-2 sm:px-4 py-2 text-xs">
                                        <div class="font-medium text-gray-900">{{ $absen->karyawan->nama_lengkap ?? '-' }}</div>
                                        <div class="text-[10px] text-gray-500 sm:hidden">{{ $absen->tanggal->format('d/m/Y') }} • {{ $absen->kantor_cabang ?? '-' }}</div>
                                    </td>
                                    <td class="px-2 sm:px-4 py-2 text-xs hidden sm:table-cell">{{ $absen->tanggal->format('d-m-Y') }}</td>
                                    <td class="px-2 sm:px-4 py-2 text-xs hidden md:table-cell">{{ $absen->check_in ? \Carbon\Carbon::parse($absen->check_in)->format('H:i:s') : '-' }}</td>
                                    <td class="px-2 sm:px-4 py-2 text-xs hidden md:table-cell">{{ $absen->check_out ? \Carbon\Carbon::parse($absen->check_out)->format('H:i:s') : '-' }}</td>
                                    <td class="px-2 sm:px-4 py-2 text-xs hidden lg:table-cell">{{ $absen->kantor_cabang ?? '-' }}</td>
                                    <td class="px-2 sm:px-4 py-2 text-xs">
                                        <span class="px-2 py-1 rounded-full text-[10px] font-medium
                                            {{ $absen->status == 'Hadir' ? 'bg-[#2E7D3E] text-white' : ($absen->status == 'Izin' ? 'bg-[#FCC626] text-[#1B1B1B]' : ($absen->status == 'Sakit' ? 'bg-[#00a2e9] text-white' : 'bg-[#ec1d1d] text-white')) }}">
                                            {{ $absen->status }}
                                        </span>
                                    </td>
                                    <td class="px-2 sm:px-4 py-2 text-xs hidden md:table-cell">
                                        @if($absen->is_valid_location)
                                            <span class="text-[#2E7D3E] text-[10px] font-semibold">✅ Valid</span>
                                        @else
                                            <span class="text-[#ec1d1d] text-[10px] font-semibold">❌ Invalid</span>
                                        @endif
                                    </td>
                                    <td class="px-2 sm:px-4 py-2 text-xs hidden lg:table-cell">
                                        @php
                                            $distance = '-';
                                            if ($absen->latitude && $absen->longitude) {
                                                $locations = \App\Models\Absensi::getOfficeLocations();
                                                $minDist = PHP_FLOAT_MAX;
                                                foreach ($locations as $coords) {
                                                    $d = \App\Models\Absensi::haversineDistance(
                                                        $absen->latitude, $absen->longitude,
                                                        $coords['latitude'], $coords['longitude']
                                                    );
                                                    if ($d < $minDist) $minDist = $d;
                                                }
                                                $distance = $minDist < PHP_FLOAT_MAX ? round($minDist, 1) . 'm' : '-';
                                            }
                                        @endphp
                                        <span class="{{ $distance !== '-' && (float) $distance <= 50 ? 'text-[#2E7D3E]' : 'text-[#ec1d1d]' }}">
                                            {{ $distance }}
                                        </span>
                                    </td>
                                    <td class="px-2 sm:px-4 py-2 text-xs">
                                        <a href="{{ route('hr.absensi.detail', $absen->id) }}"
                                           class="text-[#27438D] hover:text-[#161758] font-semibold transition-colors duration-200">
                                            Detail
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="10" class="px-4 py-8 text-center text-sm text-gray-500">
                                        <div class="flex flex-col items-center justify-center py-4">
                                            <svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                            </svg>
                                            <span>Tidak ada data absensi untuk filter yang dipilih.</span>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="mt-4">
                    {{ $absensis->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('chartAbsensi');
        if (ctx) {
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Hadir', 'Izin', 'Sakit', 'Alpha'],
                    datasets: [{
                        data: [
                            {{ $chartData['hadir'] ?? 0 }},
                            {{ $chartData['izin'] ?? 0 }},
                            {{ $chartData['sakit'] ?? 0 }},
                            {{ $chartData['alpha'] ?? 0 }}
                        ],
                        backgroundColor: ['#2E7D3E', '#FCC626', '#00a2e9', '#ec1d1d'],
                        borderWidth: 0,
                        hoverOffset: 8
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 12,
                                usePointStyle: true,
                                pointStyle: 'circle',
                                font: { size: 11 }
                            }
                        }
                    },
                    cutout: '65%'
                }
            });
        }
    });
</script>
@endsection
