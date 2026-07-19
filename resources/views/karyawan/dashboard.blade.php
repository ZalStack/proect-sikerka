{{-- views/karyawan/dashboard.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="flex min-h-screen bg-gray-50">
    @include('layouts.sidebar')
    <div class="flex-1 transition-all duration-300 md:ml-64">
        <!-- Header -->
        <div class="bg-white shadow-sm border-b">
            <div class="px-4 sm:px-6 py-3">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h1 class="text-lg sm:text-xl font-bold text-gray-800">Dashboard Karyawan</h1>
                        <p class="text-xs sm:text-sm text-gray-600">Selamat datang, {{ $user->nama_lengkap }}</p>
                    </div>
                    <div class="mt-2 sm:mt-0 flex items-center gap-2">
                        <span class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded-full">
                            <i class="fas fa-calendar-check mr-1"></i>
                            {{ \Carbon\Carbon::now()->format('d M Y') }}
                        </span>
                        <span class="text-xs px-2 py-1 rounded-full {{ $user->status === 'Karyawan Tetap' ? 'bg-green-100 text-green-800' : ($user->status === 'Contract' ? 'bg-yellow-100 text-yellow-800' : 'bg-blue-100 text-blue-800') }}">
                            {{ $user->status }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="p-3 sm:p-4 lg:p-6">
            <!-- Stats Grid - Ukuran Standar -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-4">
                <!-- Absensi Hari Ini -->
                <div class="bg-white rounded-lg shadow-sm p-3 border-l-4 border-blue-500">
                    <p class="text-xs text-gray-600 font-medium">Absensi Hari Ini</p>
                    <p class="text-lg font-bold text-gray-800 mt-1">
                        @if($absensiHariIni)
                            <i class="fas fa-check-circle text-green-500 text-base"></i>
                        @else
                            <i class="fas fa-times-circle text-red-500 text-base"></i>
                        @endif
                    </p>
                    @if($absensiHariIni)
                    <div class="mt-1 text-xs text-gray-500">
                        <span>In: {{ $absensiHariIni->check_in ? $absensiHariIni->check_in->format('H:i') : '-' }}</span>
                        <span class="ml-1">Out: {{ $absensiHariIni->check_out ? $absensiHariIni->check_out->format('H:i') : '-' }}</span>
                    </div>
                    @endif
                </div>

                <!-- Jam Kerja -->
                <div class="bg-white rounded-lg shadow-sm p-3 border-l-4 border-green-500">
                    <p class="text-xs text-gray-600 font-medium">Jam Kerja Bulan Ini</p>
                    <p class="text-lg font-bold text-gray-800 mt-1">{{ number_format($totalJamKerja ?? 0, 1) }}h</p>
                    <p class="text-xs text-gray-500 mt-1">{{ $absensiBulanIni ?? 0 }} hari hadir</p>
                </div>

                <!-- Sisa Cuti -->
                <div class="bg-white rounded-lg shadow-sm p-3 border-l-4 border-yellow-500">
                    <p class="text-xs text-gray-600 font-medium">Sisa Cuti</p>
                    <p class="text-lg font-bold text-gray-800 mt-1">{{ $sisaCuti ?? 12 }}</p>
                    <p class="text-xs text-gray-500 mt-1">{{ $cutiApproved ?? 0 }} approved, {{ $cutiPending ?? 0 }} pending</p>
                </div>

                <!-- Poin Sunnah -->
                <div class="bg-white rounded-lg shadow-sm p-3 border-l-4 border-indigo-500">
                    <p class="text-xs text-gray-600 font-medium">Total Poin 7SPS</p>
                    <p class="text-lg font-bold text-gray-800 mt-1">{{ number_format($sunnahBulanIni ?? 0) }}</p>
                    <p class="text-xs text-gray-500 mt-1">{{ $sunnahTotalDays ?? 0 }} hari tracking</p>
                </div>
            </div>

            <!-- Charts Row - Ukuran Standar -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-3 mb-4">
                <!-- Absensi Chart -->
                <div class="bg-white rounded-lg shadow-sm p-3">
                    <h3 class="text-sm font-semibold text-gray-800 mb-2">Kehadiran 7 Hari Terakhir</h3>
                    <div style="height: 200px;">
                        <canvas id="absensiChart"></canvas>
                    </div>
                </div>

                <!-- Sunnah Chart -->
                <div class="bg-white rounded-lg shadow-sm p-3">
                    <h3 class="text-sm font-semibold text-gray-800 mb-2">Poin 7SPS Harian</h3>
                    <div style="height: 200px;">
                        <canvas id="sunnahChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Detail Cards - Ukuran Standar -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-3 mb-4">
                <!-- Personal Info -->
                <div class="bg-white rounded-lg shadow-sm p-3">
                    <h3 class="text-sm font-semibold text-gray-800 mb-2 pb-2 border-b">Informasi Pribadi</h3>
                    <div class="space-y-1.5 text-xs">
                        <div class="flex justify-between">
                            <span class="text-gray-500">ID Pegawai</span>
                            <span class="font-medium">{{ $user->kode_pegawai ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">NIK</span>
                            <span class="font-medium">{{ $user->nik ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Divisi</span>
                            <span class="font-medium">{{ $user->divisi ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Jabatan</span>
                            <span class="font-medium">{{ $user->jabatan ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Tgl Bergabung</span>
                            <span class="font-medium">{{ $user->tanggal_bergabung ? $user->tanggal_bergabung->format('d-m-Y') : '-' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Email</span>
                            <span class="font-medium truncate ml-2 max-w-[150px]">{{ $user->email ?? '-' }}</span>
                        </div>
                    </div>
                </div>

                <!-- Recent Absensi -->
                <div class="bg-white rounded-lg shadow-sm p-3">
                    <h3 class="text-sm font-semibold text-gray-800 mb-2 pb-2 border-b">Absensi Terbaru</h3>
                    <div class="space-y-1.5">
                        @forelse($absensiTerbaru ?? [] as $absensi)
                        <div class="flex items-center justify-between text-xs p-1.5 hover:bg-gray-50 rounded">
                            <div>
                                <p class="font-medium">{{ $absensi->tanggal->format('d M Y') }}</p>
                                <p class="text-gray-500">
                                    {{ $absensi->check_in ? $absensi->check_in->format('H:i') : '-' }}
                                    @if($absensi->check_out)
                                        - {{ $absensi->check_out->format('H:i') }}
                                    @endif
                                </p>
                            </div>
                            <span class="px-1.5 py-0.5 rounded text-xs {{ ($absensi->status ?? '') === 'Hadir' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                {{ $absensi->status ?? '-' }}
                            </span>
                        </div>
                        @empty
                        <p class="text-center text-gray-500 text-xs py-2">Belum ada data absensi</p>
                        @endforelse
                    </div>
                </div>

                <!-- Recent Cuti - FIXED ERROR -->
                <div class="bg-white rounded-lg shadow-sm p-3">
                    <h3 class="text-sm font-semibold text-gray-800 mb-2 pb-2 border-b">Pengajuan Cuti</h3>
                    <div class="space-y-1.5">
                        @forelse($cutiTerbaru ?? [] as $cuti)
                        <div class="text-xs p-1.5 hover:bg-gray-50 rounded">
                            <div class="flex items-center justify-between mb-0.5">
                                <span class="font-medium">{{ $cuti->jenis_cuti ?? '-' }}</span>
                                <span class="px-1.5 py-0.5 rounded text-xs {{ ($cuti->status ?? '') === 'pending' ? 'bg-yellow-100 text-yellow-800' : (($cuti->status ?? '') === 'approved' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800') }}">
                                    {{ $cuti->status_label ?? '-' }}
                                </span>
                            </div>
                            <p class="text-gray-500">
                                @if($cuti->tanggal_mulai && $cuti->tanggal_selesai)
                                    {{ $cuti->tanggal_mulai->format('d/m/Y') }} - {{ $cuti->tanggal_selesai->format('d/m/Y') }}
                                    ({{ $cuti->durasi ?? 0 }} hari)
                                @else
                                    Tanggal belum ditentukan
                                @endif
                            </p>
                        </div>
                        @empty
                        <p class="text-center text-gray-500 text-xs py-2">Belum ada pengajuan cuti</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Sunnah Detail Card - Only if exists -->
            @if($sunnahHariIni)
            <div class="bg-gradient-to-r from-indigo-500 to-purple-600 rounded-lg shadow-sm p-3 text-white">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-sm font-semibold">7 Sunnah Rasul - Hari Ini</h3>
                    <span class="text-xs bg-white/20 px-2 py-0.5 rounded-full">
                        Poin: {{ $sunnahHariIni->total_poin ?? 0 }}
                    </span>
                </div>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-2 text-xs">
                    @php
                        $poinConfig = \App\Models\SunnahDaily::getPoinConfig();
                        $wajibKeys = \App\Models\SunnahDaily::getSholatWajibKeys();
                    @endphp
                    @foreach($poinConfig as $key => $config)
                        @if(!in_array($key, $wajibKeys))
                        <div class="flex items-center gap-1.5">
                            <span>{{ $config['icon'] }}</span>
                            <div>
                                <p class="opacity-90">{{ $config['label'] }}</p>
                                <p class="font-semibold">
                                    {{ $sunnahHariIni->$key ? '✓ ' . $config['poin'] . ' pts' : '✗ 0 pts' }}
                                </p>
                            </div>
                        </div>
                        @endif
                    @endforeach
                </div>
                <div class="mt-2 pt-2 border-t border-white/20 text-xs">
                    <p>Sholat Wajib Berjamaah: <span class="font-bold">{{ $sunnahHariIni->jumlah_sholat_berjamaah ?? 0 }}/5</span></p>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Absensi Chart
    const absensiCtx = document.getElementById('absensiChart').getContext('2d');
    new Chart(absensiCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($absensiChart['labels'] ?? []) !!},
            datasets: [{
                label: 'Kehadiran',
                data: {!! json_encode($absensiChart['data'] ?? []) !!},
                backgroundColor: function(context) {
                    const value = context.dataset.data[context.dataIndex];
                    return value > 0 ? '#10B981' : '#EF4444';
                },
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    max: 1,
                    ticks: {
                        stepSize: 1,
                        callback: function(value) {
                            return value === 1 ? 'Hadir' : 'Tidak';
                        }
                    }
                }
            }
        }
    });

    // Sunnah Chart
    @if(isset($sunnahChart))
    const sunnahCtx = document.getElementById('sunnahChart').getContext('2d');
    new Chart(sunnahCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($sunnahChart['labels'] ?? []) !!},
            datasets: [{
                label: 'Poin 7SPS',
                data: {!! json_encode($sunnahChart['data'] ?? []) !!},
                borderColor: '#6366F1',
                backgroundColor: 'rgba(99, 102, 241, 0.1)',
                tension: 0.4,
                fill: true,
                pointRadius: 3,
                pointHoverRadius: 5
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: { beginAtZero: true }
            }
        }
    });
    @endif
});
</script>
@endpush
@endsection
