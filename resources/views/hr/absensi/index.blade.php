@extends('layouts.app')

@section('content')
<div class="flex">
    @include('layouts.sidebar')
    <div class="ml-64 flex-1 p-6">
        <div class="mb-6">
            <h1 class="text-2xl font-bold font-['Montserrat'] text-[#161758]">Manajemen Absensi</h1>
            <p class="text-[#27438D]">Monitoring dan laporan absensi karyawan</p>
        </div>

        @if(session('success'))
            <div class="bg-[#2E7D3E] text-white p-4 rounded-lg mb-4">
                {{ session('success') }}
            </div>
        @endif

        <!-- Filter -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-lg font-semibold text-[#161758] mb-4">Filter Laporan</h2>
            <form action="{{ route('hr.absensi.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Tanggal Mulai</label>
                    <input type="date" name="start_date" value="{{ request('start_date') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                </div>
                <div>
                    <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Tanggal Akhir</label>
                    <input type="date" name="end_date" value="{{ request('end_date') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                </div>
                <div>
                    <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Bulan</label>
                    <select name="month" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                        <option value="">Pilih Bulan</option>
                        @for($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" {{ request('month') == $m ? 'selected' : '' }}>
                                {{ DateTime::createFromFormat('!m', $m)->format('F') }}
                            </option>
                        @endfor
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Tahun</label>
                    <select name="year" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                        <option value="">Pilih Tahun</option>
                        @for($y = date('Y'); $y >= date('Y')-5; $y--)
                            <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Karyawan</label>
                    <select name="karyawan_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                        <option value="">Semua Karyawan</option>
                        @foreach($karyawans as $karyawan)
                            <option value="{{ $karyawan->id }}" {{ request('karyawan_id') == $karyawan->id ? 'selected' : '' }}>
                                {{ $karyawan->nama_lengkap }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-end space-x-2">
                    <button type="submit" class="bg-[#27438D] text-white px-4 py-2 rounded-lg hover:bg-[#161758] transition-colors duration-200">
                        Filter
                    </button>
                    <a href="{{ route('hr.absensi.export', request()->all()) }}"
                       class="bg-[#2E7D3E] text-white px-4 py-2 rounded-lg hover:bg-[#009a4b] transition-colors duration-200">
                        Export Excel
                    </a>
                </div>
            </form>
        </div>

        <!-- Grafik Statistik -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-lg font-semibold text-[#161758] mb-4">Statistik Absensi</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-4">
                <div class="bg-[#F5F5F5] rounded-lg p-4 text-center">
                    <p class="text-2xl font-bold text-[#161758]">{{ $chartData['total'] }}</p>
                    <p class="text-sm text-[#1B1B1B]">Total</p>
                </div>
                <div class="bg-[#2E7D3E] text-white rounded-lg p-4 text-center">
                    <p class="text-2xl font-bold">{{ $chartData['hadir'] }}</p>
                    <p class="text-sm">Hadir</p>
                </div>
                <div class="bg-[#FCC626] text-[#1B1B1B] rounded-lg p-4 text-center">
                    <p class="text-2xl font-bold">{{ $chartData['izin'] }}</p>
                    <p class="text-sm">Izin</p>
                </div>
                <div class="bg-[#00a2e9] text-white rounded-lg p-4 text-center">
                    <p class="text-2xl font-bold">{{ $chartData['sakit'] }}</p>
                    <p class="text-sm">Sakit</p>
                </div>
                <div class="bg-[#ec1d1d] text-white rounded-lg p-4 text-center">
                    <p class="text-2xl font-bold">{{ $chartData['alpha'] }}</p>
                    <p class="text-sm">Alpha</p>
                </div>
                <div class="bg-[#27438D] text-white rounded-lg p-4 text-center">
                    <p class="text-2xl font-bold">{{ $chartData['telat'] }}</p>
                    <p class="text-sm">Telat</p>
                </div>
                <div class="bg-[#009a4b] text-white rounded-lg p-4 text-center">
                    <p class="text-2xl font-bold">{{ $chartData['lembur'] }}</p>
                    <p class="text-sm">Lembur</p>
                </div>
            </div>
        </div>

        <!-- Grafik Gelombang -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-lg font-semibold text-[#161758] mb-4">Grafik Absensi Harian</h2>
            <div class="overflow-x-auto">
                <div style="height: 300px; min-width: 600px; width: 100%; position: relative;">
                    <canvas id="waveChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Tabel Absensi -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-[#F5F5F5]">
                        <tr>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-[#1B1B1B]">No</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-[#1B1B1B]">Nama</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-[#1B1B1B]">NIP</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-[#1B1B1B]">Tanggal</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-[#1B1B1B]">Check-in</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-[#1B1B1B]">Check-out</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-[#1B1B1B]">Status</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-[#1B1B1B]">Telat</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-[#1B1B1B]">Lembur</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-[#1B1B1B]">Manual</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-[#1B1B1B]">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($absensis as $absen)
                        <tr class="border-b border-gray-200 hover:bg-[#F5F5F5]">
                            <td class="px-4 py-3 text-sm">{{ $loop->iteration }}</td>
                            <td class="px-4 py-3 text-sm">{{ $absen->karyawan->nama_lengkap }}</td>
                            <td class="px-4 py-3 text-sm">{{ $absen->karyawan->nip }}</td>
                            <td class="px-4 py-3 text-sm">{{ $absen->tanggal->format('d-m-Y') }}</td>
                            <td class="px-4 py-3 text-sm">
                                {{ $absen->check_in ? Carbon\Carbon::parse($absen->check_in)->format('H:i') : '-' }}
                                @if($absen->is_manual_checkin)
                                    <span class="text-xs text-[#00a2e9]">(M)</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm">
                                {{ $absen->check_out ? Carbon\Carbon::parse($absen->check_out)->format('H:i') : '-' }}
                                @if($absen->is_manual_checkout)
                                    <span class="text-xs text-[#00a2e9]">(M)</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-1 rounded-full text-xs font-medium
                                    {{ $absen->status == 'Hadir' ? 'bg-[#2E7D3E] text-white' :
                                       ($absen->status == 'Izin' ? 'bg-[#FCC626] text-[#1B1B1B]' :
                                       ($absen->status == 'Sakit' ? 'bg-[#00a2e9] text-white' : 'bg-[#ec1d1d] text-white')) }}">
                                    {{ $absen->status }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                @if($absen->is_telat)
                                    <span class="text-[#ec1d1d] font-semibold">{{ $absen->menit_telat }} menit</span>
                                @else
                                    <span class="text-[#2E7D3E]">✓</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm">
                                @if($absen->is_lembur)
                                    <span class="text-[#00a2e9] font-semibold">{{ $absen->jam_lembur }} jam</span>
                                @else
                                    <span>-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm">
                                @if($absen->is_manual_checkin || $absen->is_manual_checkout)
                                    <span class="text-[#FCC626]">✓</span>
                                @else
                                    <span>-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <a href="{{ route('hr.absensi.detail', $absen->id) }}"
                                   class="text-[#00a2e9] hover:text-[#27438D]">
                                    Detail
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="11" class="px-4 py-8 text-center text-[#1B1B1B]">
                                Belum ada data absensi
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-4">
                {{ $absensis->links() }}
            </div>
        </div>
    </div>
</div>

<!-- Chart.js untuk Grafik Gelombang -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('waveChart').getContext('2d');

    const waveData = @json($waveData);

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: waveData.labels,
            datasets: [
                {
                    label: 'Hadir',
                    data: waveData.hadir,
                    borderColor: '#2E7D3E',
                    backgroundColor: 'rgba(46, 125, 62, 0.1)',
                    tension: 0.3,
                    fill: true,
                    borderWidth: 2,
                    pointRadius: 4,
                    pointBackgroundColor: '#2E7D3E'
                },
                {
                    label: 'Telat',
                    data: waveData.telat,
                    borderColor: '#FCC626',
                    backgroundColor: 'rgba(252, 198, 38, 0.1)',
                    tension: 0.3,
                    fill: true,
                    borderWidth: 2,
                    pointRadius: 4,
                    pointBackgroundColor: '#FCC626'
                },
                {
                    label: 'Lembur',
                    data: waveData.lembur,
                    borderColor: '#00a2e9',
                    backgroundColor: 'rgba(0, 162, 233, 0.1)',
                    tension: 0.3,
                    fill: true,
                    borderWidth: 2,
                    pointRadius: 4,
                    pointBackgroundColor: '#00a2e9'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        usePointStyle: true,
                        padding: 20
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (context.parsed.y !== null) {
                                label += ': ' + context.parsed.y + ' orang';
                            }
                            return label;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    },
                    grid: {
                        color: 'rgba(0,0,0,0.05)'
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            },
            interaction: {
                intersect: false,
                mode: 'index'
            }
        }
    });
});
</script>
@endsection
