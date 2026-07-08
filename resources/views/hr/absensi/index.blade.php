@extends('layouts.app')

@section('content')
<div class="flex">
    @include('layouts.sidebar')
    <div class="ml-64 flex-1 p-6">
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold font-['Montserrat'] text-[#161758]">Data Absensi Karyawan</h1>
                <p class="text-[#27438D]">Rekap dan pantau kehadiran seluruh karyawan</p>
            </div>
            <a href="{{ route('hr.absensi.export', request()->query()) }}"
               class="bg-[#2E7D3E] text-white px-5 py-2 rounded-lg hover:bg-[#009a4b] transition-colors duration-200 text-sm font-semibold">
                ⬇️ Export Excel/CSV
            </a>
        </div>

        @if(session('success'))
        <div class="bg-[#2E7D3E]/10 border border-[#2E7D3E] text-[#2E7D3E] px-4 py-3 rounded-lg mb-4">
            {{ session('success') }}
        </div>
        @endif

        <!-- Ringkasan / Chart -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="md:col-span-1 bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold text-[#161758] mb-4">Ringkasan Status</h3>
                <canvas id="chartAbsensi" height="220"></canvas>
            </div>
            <div class="md:col-span-2 grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-white rounded-lg shadow-md p-4 text-center">
                    <p class="text-sm text-[#1B1B1B]">Total</p>
                    <p class="text-2xl font-bold text-[#161758]">{{ $chartData['total'] }}</p>
                </div>
                <div class="bg-white rounded-lg shadow-md p-4 text-center">
                    <p class="text-sm text-[#1B1B1B]">Hadir</p>
                    <p class="text-2xl font-bold text-[#2E7D3E]">{{ $chartData['hadir'] }}</p>
                </div>
                <div class="bg-white rounded-lg shadow-md p-4 text-center">
                    <p class="text-sm text-[#1B1B1B]">Izin</p>
                    <p class="text-2xl font-bold text-[#FCC626]">{{ $chartData['izin'] }}</p>
                </div>
                <div class="bg-white rounded-lg shadow-md p-4 text-center">
                    <p class="text-sm text-[#1B1B1B]">Sakit</p>
                    <p class="text-2xl font-bold text-[#00a2e9]">{{ $chartData['sakit'] }}</p>
                </div>
                <div class="bg-white rounded-lg shadow-md p-4 text-center col-span-2 md:col-span-4">
                    <p class="text-sm text-[#1B1B1B]">Alpha</p>
                    <p class="text-2xl font-bold text-[#ec1d1d]">{{ $chartData['alpha'] }}</p>
                </div>
            </div>
        </div>

        <!-- Filter -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h3 class="text-lg font-semibold text-[#161758] mb-4">Filter Data</h3>
            <form action="{{ route('hr.absensi.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end">
                <div>
                    <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Dari Tanggal</label>
                    <input type="date" name="start_date" value="{{ request('start_date') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                </div>
                <div>
                    <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Sampai Tanggal</label>
                    <input type="date" name="end_date" value="{{ request('end_date') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                </div>
                <div>
                    <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Bulan</label>
                    <select name="month" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                        <option value="">Semua</option>
                        @foreach(range(1, 12) as $m)
                            <option value="{{ $m }}" {{ request('month') == $m ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::create()->month($m)->locale('id')->isoFormat('MMMM') }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Tahun</label>
                    <select name="year" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                        <option value="">Semua</option>
                        @foreach(range(now()->year, now()->year - 5) as $y)
                            <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Karyawan</label>
                    <select name="karyawan_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                        <option value="">Semua Karyawan</option>
                        @foreach($karyawans as $k)
                            <option value="{{ $k->id }}" {{ request('karyawan_id') == $k->id ? 'selected' : '' }}>
                                {{ $k->nama_lengkap }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-5 flex gap-3">
                    <button type="submit"
                            class="bg-[#27438D] text-white px-6 py-2 rounded-lg hover:bg-[#161758] transition-colors duration-200">
                        🔍 Terapkan Filter
                    </button>
                    <a href="{{ route('hr.absensi.index') }}"
                       class="bg-gray-400 text-white px-6 py-2 rounded-lg hover:bg-gray-500 transition-colors duration-200">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Tabel Data Absensi -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold text-[#161758] mb-4">Daftar Absensi</h3>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-[#F5F5F5]">
                            <th class="px-4 py-2 text-left text-sm font-semibold text-[#1B1B1B]">No</th>
                            <th class="px-4 py-2 text-left text-sm font-semibold text-[#1B1B1B]">Nama Karyawan</th>
                            <th class="px-4 py-2 text-left text-sm font-semibold text-[#1B1B1B]">Tanggal</th>
                            <th class="px-4 py-2 text-left text-sm font-semibold text-[#1B1B1B]">Check-in</th>
                            <th class="px-4 py-2 text-left text-sm font-semibold text-[#1B1B1B]">Check-out</th>
                            <th class="px-4 py-2 text-left text-sm font-semibold text-[#1B1B1B]">Kantor</th>
                            <th class="px-4 py-2 text-left text-sm font-semibold text-[#1B1B1B]">Status</th>
                            <th class="px-4 py-2 text-left text-sm font-semibold text-[#1B1B1B]">WiFi</th>
                            <th class="px-4 py-2 text-left text-sm font-semibold text-[#1B1B1B]">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($absensis as $i => $absen)
                        <tr class="border-b border-gray-200">
                            <td class="px-4 py-2 text-sm">{{ $absensis->firstItem() + $i }}</td>
                            <td class="px-4 py-2 text-sm">
                                {{ $absen->karyawan->nama_lengkap ?? '-' }}
                                <div class="text-xs text-gray-500">{{ $absen->karyawan->kode_pegawai ?? '' }}</div>
                            </td>
                            <td class="px-4 py-2 text-sm">{{ $absen->tanggal->format('d-m-Y') }}</td>
                            <td class="px-4 py-2 text-sm">{{ $absen->check_in ? \Carbon\Carbon::parse($absen->check_in)->format('H:i:s') : '-' }}</td>
                            <td class="px-4 py-2 text-sm">{{ $absen->check_out ? \Carbon\Carbon::parse($absen->check_out)->format('H:i:s') : '-' }}</td>
                            <td class="px-4 py-2 text-sm">{{ $absen->kantor_cabang ?? '-' }}</td>
                            <td class="px-4 py-2 text-sm">
                                <span class="px-2 py-1 rounded-full text-xs font-medium
                                    {{ $absen->status == 'Hadir' ? 'bg-[#2E7D3E] text-white' :
                                       ($absen->status == 'Izin' ? 'bg-[#FCC626] text-[#1B1B1B]' :
                                       ($absen->status == 'Sakit' ? 'bg-[#00a2e9] text-white' : 'bg-[#ec1d1d] text-white')) }}">
                                    {{ $absen->status }}
                                </span>
                            </td>
                            <td class="px-4 py-2 text-sm">
                                @if($absen->is_valid_wifi)
                                    <span class="text-[#2E7D3E] text-xs font-semibold">✅ Valid</span>
                                @else
                                    <span class="text-[#ec1d1d] text-xs font-semibold">❌ Tidak Valid</span>
                                @endif
                            </td>
                            <td class="px-4 py-2 text-sm">
                                <a href="{{ route('hr.absensi.detail', $absen->id) }}"
                                   class="text-[#27438D] hover:text-[#161758] font-semibold">Detail</a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="px-4 py-6 text-center text-sm text-gray-500">
                                Tidak ada data absensi untuk filter yang dipilih.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $absensis->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
<script>
    const ctx = document.getElementById('chartAbsensi');
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Hadir', 'Izin', 'Sakit', 'Alpha'],
            datasets: [{
                data: [
                    {{ $chartData['hadir'] }},
                    {{ $chartData['izin'] }},
                    {{ $chartData['sakit'] }},
                    {{ $chartData['alpha'] }}
                ],
                backgroundColor: ['#2E7D3E', '#FCC626', '#00a2e9', '#ec1d1d'],
                borderWidth: 0
            }]
        },
        options: {
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });
</script>
@endsection
