{{-- views/hr/dashboard.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="flex min-h-screen bg-gradient-to-br from-gray-50 to-gray-100">
    @include('layouts.sidebar')
    <div class="flex-1 transition-all duration-300 md:ml-64">
        <!-- Header -->
        <div class="bg-white/80 backdrop-blur-md shadow-sm border-b border-gray-200/50 sticky top-0 z-10">
            <div class="px-4 sm:px-6 lg:px-8 py-4">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h1 class="text-xl sm:text-2xl font-bold bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent">
                            Dashboard HR
                        </h1>
                        <p class="text-sm text-gray-500 mt-0.5">Panel Manajemen Sumber Daya Manusia</p>
                    </div>
                    <div class="mt-2 sm:mt-0 flex items-center gap-3">
                        <span class="text-xs bg-blue-50 text-blue-700 px-3 py-1.5 rounded-full font-medium border border-blue-100">
                            <i class="fas fa-calendar-check mr-1.5"></i>
                            {{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}
                        </span>
                        <span class="text-xs bg-green-50 text-green-700 px-3 py-1.5 rounded-full font-medium border border-green-100">
                            <i class="fas fa-clock mr-1.5"></i>
                            {{ \Carbon\Carbon::now()->format('H:i') }} WIB
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="p-4 sm:p-6 lg:p-8">
            <!-- Stats Grid -->
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-6">
                <!-- Total Karyawan -->
                <div class="bg-white rounded-2xl shadow-sm p-4 border border-gray-100 hover:shadow-md transition-shadow duration-200 group">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium text-gray-400 uppercase tracking-wider">Total Karyawan</p>
                            <p class="text-2xl font-bold text-gray-800 mt-1">{{ $totalKaryawan ?? 0 }}</p>
                        </div>
                        <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center group-hover:scale-110 transition-transform">
                            <i class="fas fa-users text-blue-500 text-lg"></i>
                        </div>
                    </div>
                    <div class="mt-2 flex items-center gap-1">
                        <span class="text-xs text-gray-400">Aktif: {{ $totalKaryawanAktif ?? 0 }}</span>
                        <span class="w-1 h-1 rounded-full bg-gray-300"></span>
                        <span class="text-xs text-gray-400">Resign: {{ $totalKaryawanResigned ?? 0 }}</span>
                    </div>
                </div>

                <!-- HR -->
                <div class="bg-white rounded-2xl shadow-sm p-4 border border-gray-100 hover:shadow-md transition-shadow duration-200 group">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium text-gray-400 uppercase tracking-wider">Tim HR</p>
                            <p class="text-2xl font-bold text-gray-800 mt-1">{{ $totalHr ?? 0 }}</p>
                        </div>
                        <div class="w-10 h-10 rounded-xl bg-purple-50 flex items-center justify-center group-hover:scale-110 transition-transform">
                            <i class="fas fa-user-tie text-purple-500 text-lg"></i>
                        </div>
                    </div>
                    <div class="mt-2">
                        <span class="text-xs text-gray-400">Personil HR</span>
                    </div>
                </div>

                <!-- Absensi Hari Ini -->
                <div class="bg-white rounded-2xl shadow-sm p-4 border border-gray-100 hover:shadow-md transition-shadow duration-200 group">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium text-gray-400 uppercase tracking-wider">Absensi Hari Ini</p>
                            <p class="text-2xl font-bold text-gray-800 mt-1">{{ $absensiHariIni ?? 0 }}</p>
                        </div>
                        <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center group-hover:scale-110 transition-transform">
                            <i class="fas fa-fingerprint text-indigo-500 text-lg"></i>
                        </div>
                    </div>
                    <div class="mt-2 flex items-center gap-1">
                        <span class="text-xs text-gray-400">Terlambat: {{ $absensiTerlambat ?? 0 }}</span>
                    </div>
                </div>

                <!-- FHL Hari Ini -->
                <div class="bg-white rounded-2xl shadow-sm p-4 border border-gray-100 hover:shadow-md transition-shadow duration-200 group">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium text-gray-400 uppercase tracking-wider">FHL Hari Ini</p>
                            <p class="text-2xl font-bold text-gray-800 mt-1">{{ $fhlHariIni ?? 0 }}</p>
                        </div>
                        <div class="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center group-hover:scale-110 transition-transform">
                            <i class="fas fa-mosque text-emerald-500 text-lg"></i>
                        </div>
                    </div>
                    <div class="mt-2">
                        <span class="text-xs text-gray-400">Jumat Berkah</span>
                    </div>
                </div>

                <!-- Cuti Pending -->
                <div class="bg-white rounded-2xl shadow-sm p-4 border border-gray-100 hover:shadow-md transition-shadow duration-200 group">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium text-gray-400 uppercase tracking-wider">Cuti Pending</p>
                            <p class="text-2xl font-bold text-gray-800 mt-1">{{ $cutiPending ?? 0 }}</p>
                        </div>
                        <div class="w-10 h-10 rounded-xl bg-amber-50 flex items-center justify-center group-hover:scale-110 transition-transform">
                            <i class="fas fa-clock text-amber-500 text-lg"></i>
                        </div>
                    </div>
                    <div class="mt-2 flex items-center gap-1">
                        <span class="text-xs text-gray-400">Approved: {{ $cutiApproved ?? 0 }}</span>
                    </div>
                </div>

                <!-- Sunnah Pending -->
                <div class="bg-white rounded-2xl shadow-sm p-4 border border-gray-100 hover:shadow-md transition-shadow duration-200 group">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium text-gray-400 uppercase tracking-wider">7SPS Pending</p>
                            <p class="text-2xl font-bold text-gray-800 mt-1">{{ $sunnahPending ?? 0 }}</p>
                        </div>
                        <div class="w-10 h-10 rounded-xl bg-orange-50 flex items-center justify-center group-hover:scale-110 transition-transform">
                            <i class="fas fa-star text-orange-500 text-lg"></i>
                        </div>
                    </div>
                    <div class="mt-2 flex items-center gap-1">
                        <span class="text-xs text-gray-400">Bulan ini: {{ $sunnahBulanIni ?? 0 }}</span>
                    </div>
                </div>
            </div>

            <!-- Charts Row -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <!-- Absensi Chart -->
                <div class="bg-white rounded-2xl shadow-sm p-5 border border-gray-100">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-semibold text-gray-700">📊 Tren Absensi 7 Hari</h3>
                        <span class="text-xs text-gray-400 bg-gray-50 px-2 py-1 rounded-full">Update real-time</span>
                    </div>
                    <div style="height: 220px;">
                        <canvas id="absensiChart"></canvas>
                    </div>
                </div>

                <!-- Status Karyawan Chart -->
                <div class="bg-white rounded-2xl shadow-sm p-5 border border-gray-100">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-semibold text-gray-700">👥 Komposisi Karyawan</h3>
                        <span class="text-xs text-gray-400 bg-gray-50 px-2 py-1 rounded-full">Per hari ini</span>
                    </div>
                    <div style="height: 220px;">
                        <canvas id="statusKaryawanChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Second Row -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                <!-- Top Sunnah Performers -->
                <div class="bg-white rounded-2xl shadow-sm p-5 border border-gray-100">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-semibold text-gray-700">🏆 Top 7SPS Performers</h3>
                        <span class="text-xs text-gray-400 bg-gray-50 px-2 py-1 rounded-full">Bulan ini</span>
                    </div>
                    <div class="space-y-2.5">
                        @forelse($topSunnah ?? [] as $index => $sunnah)
                        <div class="flex items-center justify-between p-2.5 rounded-xl transition-all duration-200 {{ $index === 0 ? 'bg-gradient-to-r from-yellow-50 to-orange-50 border border-yellow-200/50' : 'hover:bg-gray-50' }}">
                            <div class="flex items-center gap-3">
                                <div class="w-7 h-7 rounded-full {{ $index === 0 ? 'bg-gradient-to-br from-yellow-400 to-orange-500' : ($index === 1 ? 'bg-gradient-to-br from-gray-300 to-gray-400' : ($index === 2 ? 'bg-gradient-to-br from-amber-600 to-amber-700' : 'bg-gray-200')) }} flex items-center justify-center text-white font-bold text-xs shadow-sm">
                                    {{ $index + 1 }}
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-800 truncate max-w-[120px]">{{ $sunnah->karyawan->nama_lengkap ?? '-' }}</p>
                                    <p class="text-xs text-gray-400">{{ $sunnah->karyawan->divisi ?? '-' }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-bold text-indigo-600">{{ number_format($sunnah->total_poin ?? 0) }}</p>
                                <p class="text-[10px] text-gray-400">{{ $sunnah->total_days ?? 0 }} hari</p>
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-8">
                            <i class="fas fa-star text-3xl text-gray-200 mb-2 block"></i>
                            <p class="text-sm text-gray-400">Belum ada data 7SPS</p>
                        </div>
                        @endforelse
                    </div>
                </div>

                <!-- Karyawan Terbaru -->
                <div class="bg-white rounded-2xl shadow-sm p-5 border border-gray-100">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-semibold text-gray-700">👤 Karyawan Terbaru</h3>
                        <a href="{{ route('hr.karyawan.index') }}" class="text-xs text-blue-500 hover:text-blue-600 font-medium">Lihat Semua →</a>
                    </div>
                    <div class="space-y-2.5">
                        @forelse($karyawanTerbaru ?? [] as $karyawan)
                        <div class="flex items-center gap-3 p-2.5 rounded-xl hover:bg-gray-50 transition-colors duration-200">
                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center text-white font-bold text-sm shadow-sm flex-shrink-0">
                                {{ strtoupper(substr($karyawan->nama_lengkap ?? '?', 0, 1)) }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-800 truncate">{{ $karyawan->nama_lengkap ?? '-' }}</p>
                                <p class="text-xs text-gray-400 truncate">{{ $karyawan->jabatan ?? 'N/A' }} • {{ $karyawan->divisi ?? '-' }}</p>
                            </div>
                            <span class="px-2 py-1 rounded-full text-[10px] font-medium flex-shrink-0 {{ ($karyawan->status ?? '') === 'Karyawan Tetap' ? 'bg-green-100 text-green-700' : (($karyawan->status ?? '') === 'Contract' ? 'bg-amber-100 text-amber-700' : 'bg-blue-100 text-blue-700') }}">
                                {{ $karyawan->status_label ?? $karyawan->status ?? '-' }}
                            </span>
                        </div>
                        @empty
                        <div class="text-center py-8">
                            <i class="fas fa-users text-3xl text-gray-200 mb-2 block"></i>
                            <p class="text-sm text-gray-400">Belum ada karyawan baru</p>
                        </div>
                        @endforelse
                    </div>
                </div>

                <!-- Absensi Terbaru -->
                <div class="bg-white rounded-2xl shadow-sm p-5 border border-gray-100">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-semibold text-gray-700">📌 Absensi Terbaru</h3>
                        <a href="{{ route('hr.absensi.index') }}" class="text-xs text-blue-500 hover:text-blue-600 font-medium">Lihat Semua →</a>
                    </div>
                    <div class="space-y-2.5">
                        @forelse($absensiTerbaru ?? [] as $absensi)
                        <div class="flex items-center gap-3 p-2.5 rounded-xl hover:bg-gray-50 transition-colors duration-200">
                            <span class="w-2 h-2 rounded-full {{ $absensi->status === 'Hadir' ? 'bg-green-500' : ($absensi->status === 'Terlambat' ? 'bg-amber-500' : 'bg-red-500') }} flex-shrink-0"></span>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-800 truncate">{{ $absensi->karyawan->nama_lengkap ?? '-' }}</p>
                                <p class="text-xs text-gray-400">
                                    <i class="fas fa-sign-in-alt mr-1"></i>{{ $absensi->check_in ? $absensi->check_in->format('H:i') : '-' }}
                                    @if($absensi->check_out)
                                        <i class="fas fa-sign-out-alt ml-2 mr-1"></i>{{ $absensi->check_out->format('H:i') }}
                                    @endif
                                </p>
                            </div>
                            <div class="text-right">
                                <span class="px-2 py-1 rounded-full text-[10px] font-medium {{ $absensi->status === 'Hadir' ? 'bg-green-100 text-green-700' : ($absensi->status === 'Terlambat' ? 'bg-amber-100 text-amber-700' : 'bg-red-100 text-red-700') }}">
                                    {{ $absensi->status ?? '-' }}
                                </span>
                                <p class="text-[10px] text-gray-400 mt-0.5">{{ $absensi->tanggal ? $absensi->tanggal->format('d/m/Y') : '-' }}</p>
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-8">
                            <i class="fas fa-fingerprint text-3xl text-gray-200 mb-2 block"></i>
                            <p class="text-sm text-gray-400">Belum ada data absensi</p>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Third Row - Cuti Chart & Cuti Terbaru -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Cuti Chart -->
                <div class="bg-white rounded-2xl shadow-sm p-5 border border-gray-100">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-semibold text-gray-700">📋 Trend Cuti 6 Bulan</h3>
                        <span class="text-xs text-gray-400 bg-gray-50 px-2 py-1 rounded-full">Per bulan</span>
                    </div>
                    <div style="height: 220px;">
                        <canvas id="cutiChart"></canvas>
                    </div>
                </div>

                <!-- Cuti Terbaru -->
                <div class="bg-white rounded-2xl shadow-sm p-5 border border-gray-100">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-semibold text-gray-700">📝 Pengajuan Cuti Terbaru</h3>
                        <a href="{{ route('hr.cuti.index') }}" class="text-xs text-blue-500 hover:text-blue-600 font-medium">Lihat Semua →</a>
                    </div>
                    <div class="space-y-2.5">
                        @forelse($cutiTerbaru ?? [] as $cuti)
                        <div class="flex items-center gap-3 p-2.5 rounded-xl hover:bg-gray-50 transition-colors duration-200">
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-medium flex-shrink-0 {{ ($cuti->status ?? '') === 'pending' ? 'bg-amber-100 text-amber-700' : (($cuti->status ?? '') === 'approved' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700') }}">
                                {{ $cuti->status_label ?? '-' }}
                            </span>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-800 truncate">{{ $cuti->karyawan->nama_lengkap ?? '-' }}</p>
                                <p class="text-xs text-gray-400 truncate">
                                    <i class="fas fa-tag mr-1"></i>{{ $cuti->jenis_cuti ?? '-' }}
                                    <span class="mx-1">•</span>
                                    <i class="far fa-calendar-alt mr-1"></i>{{ $cuti->durasi ?? 0 }} hari
                                </p>
                            </div>
                            <p class="text-[10px] text-gray-400 flex-shrink-0">{{ $cuti->tanggal_pengajuan ? $cuti->tanggal_pengajuan->format('d/m') : '-' }}</p>
                        </div>
                        @empty
                        <div class="text-center py-8">
                            <i class="fas fa-file-alt text-3xl text-gray-200 mb-2 block"></i>
                            <p class="text-sm text-gray-400">Belum ada pengajuan cuti</p>
                        </div>
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
                borderColor: '#4F46E5',
                backgroundColor: 'rgba(79, 70, 229, 0.08)',
                borderWidth: 2.5,
                tension: 0.4,
                fill: true,
                pointRadius: 4,
                pointBackgroundColor: '#4F46E5',
                pointHoverRadius: 6,
                pointHoverBackgroundColor: '#4338CA'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(255,255,255,0.95)',
                    titleColor: '#1F2937',
                    bodyColor: '#6B7280',
                    borderColor: '#E5E7EB',
                    borderWidth: 1,
                    cornerRadius: 8,
                    padding: 10,
                    boxShadow: '0 4px 6px -1px rgba(0,0,0,0.1)'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1,
                        font: { size: 11 }
                    },
                    grid: {
                        color: 'rgba(0,0,0,0.05)'
                    }
                },
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        font: { size: 10 }
                    }
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
                backgroundColor: {!! json_encode($statusKaryawanChart['colors'] ?? ['#2E7D3E', '#FCC626', '#00a2e9', '#ec1d1d']) !!},
                borderWidth: 3,
                borderColor: '#ffffff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '70%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        boxWidth: 12,
                        padding: 12,
                        font: { size: 11, weight: '500' },
                        usePointStyle: true,
                        pointStyle: 'circle'
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(255,255,255,0.95)',
                    titleColor: '#1F2937',
                    bodyColor: '#6B7280',
                    borderColor: '#E5E7EB',
                    borderWidth: 1,
                    cornerRadius: 8,
                    padding: 10
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
                    backgroundColor: '#10B981',
                    borderRadius: 4,
                    borderSkipped: false
                },
                {
                    label: 'Pending',
                    data: {!! json_encode($cutiChart['pending'] ?? []) !!},
                    backgroundColor: '#F59E0B',
                    borderRadius: 4,
                    borderSkipped: false
                },
                {
                    label: 'Rejected',
                    data: {!! json_encode($cutiChart['rejected'] ?? []) !!},
                    backgroundColor: '#EF4444',
                    borderRadius: 4,
                    borderSkipped: false
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
                        boxWidth: 12,
                        padding: 10,
                        font: { size: 11, weight: '500' },
                        usePointStyle: true,
                        pointStyle: 'rectRounded'
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(255,255,255,0.95)',
                    titleColor: '#1F2937',
                    bodyColor: '#6B7280',
                    borderColor: '#E5E7EB',
                    borderWidth: 1,
                    cornerRadius: 8,
                    padding: 10
                }
            },
            scales: {
                x: {
                    stacked: true,
                    grid: { display: false },
                    ticks: { font: { size: 10 } }
                },
                y: {
                    stacked: true,
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1,
                        font: { size: 11 }
                    },
                    grid: {
                        color: 'rgba(0,0,0,0.05)'
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
