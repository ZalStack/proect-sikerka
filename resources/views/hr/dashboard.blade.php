{{-- views/hr/dashboard.blade.php --}}
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
                        <h1 class="text-lg sm:text-xl font-bold text-gray-800">Dashboard HR</h1>
                        <p class="text-xs sm:text-sm text-gray-600">Panel HR Management</p>
                    </div>
                    <div class="mt-2 sm:mt-0">
                        <span class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded-full">
                            <i class="fas fa-calendar-check mr-1"></i>
                            {{ \Carbon\Carbon::now()->format('l, d F Y') }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="p-3 sm:p-4 lg:p-6">
            <!-- Stats Grid - Ukuran Standar -->
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3 mb-4">
                <!-- Total Karyawan -->
                <div class="bg-white rounded-lg shadow-sm p-3 border-l-4 border-blue-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs text-gray-600 font-medium">Total Karyawan</p>
                            <p class="text-lg font-bold text-gray-800">{{ $totalKaryawan ?? 0 }}</p>
                        </div>
                        <i class="fas fa-users text-blue-500 text-lg"></i>
                    </div>
                </div>

                <!-- Karyawan Aktif -->
                <div class="bg-white rounded-lg shadow-sm p-3 border-l-4 border-green-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs text-gray-600 font-medium">Aktif</p>
                            <p class="text-lg font-bold text-gray-800">{{ $totalKaryawanAktif ?? 0 }}</p>
                        </div>
                        <i class="fas fa-user-check text-green-500 text-lg"></i>
                    </div>
                </div>

                <!-- HR -->
                <div class="bg-white rounded-lg shadow-sm p-3 border-l-4 border-purple-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs text-gray-600 font-medium">Total HR</p>
                            <p class="text-lg font-bold text-gray-800">{{ $totalHr ?? 0 }}</p>
                        </div>
                        <i class="fas fa-user-tie text-purple-500 text-lg"></i>
                    </div>
                </div>

                <!-- Absensi Hari Ini -->
                <div class="bg-white rounded-lg shadow-sm p-3 border-l-4 border-indigo-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs text-gray-600 font-medium">Absensi Hari Ini</p>
                            <p class="text-lg font-bold text-gray-800">{{ $absensiHariIni ?? 0 }}</p>
                        </div>
                        <i class="fas fa-fingerprint text-indigo-500 text-lg"></i>
                    </div>
                </div>

                <!-- Cuti Pending -->
                <div class="bg-white rounded-lg shadow-sm p-3 border-l-4 border-yellow-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs text-gray-600 font-medium">Cuti Pending</p>
                            <p class="text-lg font-bold text-gray-800">{{ $cutiPending ?? 0 }}</p>
                        </div>
                        <i class="fas fa-clock text-yellow-500 text-lg"></i>
                    </div>
                </div>

                <!-- Sunnah Pending -->
                <div class="bg-white rounded-lg shadow-sm p-3 border-l-4 border-orange-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs text-gray-600 font-medium">Sunnah Pending</p>
                            <p class="text-lg font-bold text-gray-800">{{ $sunnahPending ?? 0 }}</p>
                        </div>
                        <i class="fas fa-star text-orange-500 text-lg"></i>
                    </div>
                </div>
            </div>

            <!-- Charts Row - Ukuran Standar -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-3 mb-4">
                <!-- Absensi Chart -->
                <div class="bg-white rounded-lg shadow-sm p-3">
                    <h3 class="text-sm font-semibold text-gray-800 mb-2">Absensi 7 Hari Terakhir</h3>
                    <div style="height: 200px;">
                        <canvas id="absensiChart"></canvas>
                    </div>
                </div>

                <!-- Status Karyawan Chart -->
                <div class="bg-white rounded-lg shadow-sm p-3">
                    <h3 class="text-sm font-semibold text-gray-800 mb-2">Status Karyawan</h3>
                    <div style="height: 200px;">
                        <canvas id="statusKaryawanChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Second Row - Ukuran Standar -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-3 mb-4">
                <!-- Top Sunnah -->
                <div class="bg-white rounded-lg shadow-sm p-3">
                    <h3 class="text-sm font-semibold text-gray-800 mb-2">Top 7SPS Performers</h3>
                    <div class="space-y-1.5">
                        @forelse($topSunnah ?? [] as $index => $sunnah)
                        <div class="flex items-center justify-between text-xs p-1.5 bg-gray-50 rounded">
                            <div class="flex items-center gap-2">
                                <span class="w-5 h-5 rounded-full bg-gradient-to-br from-yellow-400 to-orange-500 flex items-center justify-center text-white font-bold text-xs">
                                    {{ $index + 1 }}
                                </span>
                                <span class="font-medium truncate max-w-[120px]">{{ $sunnah->karyawan->nama_lengkap ?? '-' }}</span>
                            </div>
                            <span class="font-bold text-indigo-600">{{ number_format($sunnah->total_poin ?? 0) }} pts</span>
                        </div>
                        @empty
                        <p class="text-center text-gray-500 text-xs py-2">Belum ada data</p>
                        @endforelse
                    </div>
                </div>

                <!-- Karyawan Terbaru -->
                <div class="bg-white rounded-lg shadow-sm p-3">
                    <h3 class="text-sm font-semibold text-gray-800 mb-2">Karyawan Terbaru</h3>
                    <div class="space-y-1.5">
                        @forelse($karyawanTerbaru ?? [] as $karyawan)
                        <div class="flex items-center gap-2 text-xs p-1.5 hover:bg-gray-50 rounded">
                            <div class="w-7 h-7 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center text-white font-bold text-xs flex-shrink-0">
                                {{ strtoupper(substr($karyawan->nama_lengkap ?? '?', 0, 1)) }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="font-medium truncate">{{ $karyawan->nama_lengkap ?? '-' }}</p>
                                <p class="text-gray-500 truncate">{{ $karyawan->jabatan ?? 'N/A' }}</p>
                            </div>
                            <span class="px-1.5 py-0.5 rounded text-xs flex-shrink-0 {{ ($karyawan->status ?? '') === 'Karyawan Tetap' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                {{ $karyawan->status_label ?? $karyawan->status ?? '-' }}
                            </span>
                        </div>
                        @empty
                        <p class="text-center text-gray-500 text-xs py-2">Belum ada karyawan baru</p>
                        @endforelse
                    </div>
                </div>

                <!-- Absensi Terbaru -->
                <div class="bg-white rounded-lg shadow-sm p-3">
                    <h3 class="text-sm font-semibold text-gray-800 mb-2">Absensi Terbaru</h3>
                    <div class="space-y-1.5">
                        @forelse($absensiTerbaru ?? [] as $absensi)
                        <div class="flex items-center gap-2 text-xs p-1.5 hover:bg-gray-50 rounded">
                            <span class="w-1.5 h-1.5 rounded-full bg-green-500 flex-shrink-0"></span>
                            <div class="flex-1 min-w-0">
                                <p class="font-medium truncate">{{ $absensi->karyawan->nama_lengkap ?? '-' }}</p>
                                <p class="text-gray-500">
                                    {{ $absensi->check_in ? $absensi->check_in->format('H:i') : '-' }}
                                    @if($absensi->check_out)
                                        - {{ $absensi->check_out->format('H:i') }}
                                    @endif
                                </p>
                            </div>
                            <span class="text-gray-500 flex-shrink-0">{{ $absensi->tanggal ? $absensi->tanggal->format('d/m') : '-' }}</span>
                        </div>
                        @empty
                        <p class="text-center text-gray-500 text-xs py-2">Belum ada data</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Cuti Chart -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-3">
                <div class="bg-white rounded-lg shadow-sm p-3">
                    <h3 class="text-sm font-semibold text-gray-800 mb-2">Trend Cuti 6 Bulan</h3>
                    <div style="height: 200px;">
                        <canvas id="cutiChart"></canvas>
                    </div>
                </div>

                <!-- Cuti Terbaru -->
                <div class="bg-white rounded-lg shadow-sm p-3">
                    <h3 class="text-sm font-semibold text-gray-800 mb-2">Pengajuan Cuti Terbaru</h3>
                    <div class="space-y-1.5">
                        @forelse($cutiTerbaru ?? [] as $cuti)
                        <div class="flex items-center gap-2 text-xs p-1.5 hover:bg-gray-50 rounded">
                            <span class="px-1.5 py-0.5 rounded text-xs flex-shrink-0 {{ ($cuti->status ?? '') === 'pending' ? 'bg-yellow-100 text-yellow-800' : (($cuti->status ?? '') === 'approved' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800') }}">
                                {{ $cuti->status_label ?? '-' }}
                            </span>
                            <div class="flex-1 min-w-0">
                                <p class="font-medium truncate">{{ $cuti->karyawan->nama_lengkap ?? '-' }}</p>
                                <p class="text-gray-500 truncate">{{ $cuti->jenis_cuti ?? '-' }} • {{ $cuti->durasi ?? 0 }} hari</p>
                            </div>
                        </div>
                        @empty
                        <p class="text-center text-gray-500 text-xs py-2">Belum ada pengajuan</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Absensi Chart
    @if(isset($absensiChart))
    const absensiCtx = document.getElementById('absensiChart').getContext('2d');
    new Chart(absensiCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($absensiChart['labels'] ?? []) !!},
            datasets: [{
                label: 'Jumlah Absensi',
                data: {!! json_encode($absensiChart['data'] ?? []) !!},
                borderColor: '#3B82F6',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                tension: 0.4,
                fill: true,
                pointRadius: 3,
                pointHoverRadius: 5
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { stepSize: 1 }
                }
            }
        }
    });
    @endif

    // Status Karyawan Chart
    @if(isset($statusKaryawanChart))
    const statusCtx = document.getElementById('statusKaryawanChart').getContext('2d');
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: {!! json_encode($statusKaryawanChart['labels'] ?? []) !!},
            datasets: [{
                data: {!! json_encode($statusKaryawanChart['data'] ?? []) !!},
                backgroundColor: {!! json_encode($statusKaryawanChart['colors'] ?? []) !!}
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        boxWidth: 10,
                        padding: 10,
                        font: { size: 10 }
                    }
                }
            }
        }
    });
    @endif

    // Cuti Chart
    @if(isset($cutiChart))
    const cutiCtx = document.getElementById('cutiChart').getContext('2d');
    new Chart(cutiCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($cutiChart['labels'] ?? []) !!},
            datasets: [
                {
                    label: 'Approved',
                    data: {!! json_encode($cutiChart['approved'] ?? []) !!},
                    backgroundColor: '#10B981'
                },
                {
                    label: 'Pending',
                    data: {!! json_encode($cutiChart['pending'] ?? []) !!},
                    backgroundColor: '#F59E0B'
                },
                {
                    label: 'Rejected',
                    data: {!! json_encode($cutiChart['rejected'] ?? []) !!},
                    backgroundColor: '#EF4444'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: { stacked: true },
                y: {
                    stacked: true,
                    beginAtZero: true,
                    ticks: { stepSize: 1 }
                }
            },
            plugins: {
                legend: {
                    labels: {
                        boxWidth: 10,
                        padding: 8,
                        font: { size: 10 }
                    }
                }
            }
        }
    });
    @endif
});
</script>
@endpush
@endsection
