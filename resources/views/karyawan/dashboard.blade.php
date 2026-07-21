{{-- views/karyawan/dashboard.blade.php --}}
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
                        <h1 class="text-xl sm:text-2xl font-bold bg-gradient-to-r from-emerald-600 to-teal-600 bg-clip-text text-transparent">
                            Dashboard Karyawan
                        </h1>
                        <p class="text-sm text-gray-500 mt-0.5">
                            Selamat datang, <span class="font-medium text-gray-700">{{ $user->nama_lengkap }}</span>
                        </p>
                    </div>
                    <div class="mt-2 sm:mt-0 flex items-center gap-3 flex-wrap">
                        <span class="text-xs bg-blue-50 text-blue-700 px-3 py-1.5 rounded-full font-medium border border-blue-100">
                            <i class="fas fa-calendar-check mr-1.5"></i>
                            {{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}
                        </span>
                        <span class="text-xs px-3 py-1.5 rounded-full font-medium border {{ $user->status === 'Karyawan Tetap' ? 'bg-green-50 text-green-700 border-green-100' : ($user->status === 'Contract' ? 'bg-amber-50 text-amber-700 border-amber-100' : 'bg-blue-50 text-blue-700 border-blue-100') }}">
                            <i class="fas fa-briefcase mr-1.5"></i>
                            {{ $user->status_label ?? $user->status ?? 'Karyawan' }}
                        </span>
                        @if($user->divisi)
                        <span class="text-xs bg-gray-100 text-gray-600 px-3 py-1.5 rounded-full font-medium border border-gray-200">
                            <i class="fas fa-building mr-1.5"></i>
                            {{ $user->divisi }}
                        </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="p-4 sm:p-6 lg:p-8">
            <!-- Stats Grid -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                <!-- Absensi Hari Ini -->
                <div class="bg-white rounded-2xl shadow-sm p-4 border border-gray-100 hover:shadow-md transition-shadow duration-200 group">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium text-gray-400 uppercase tracking-wider">Absensi Hari Ini</p>
                            <div class="flex items-center gap-2 mt-1">
                                <p class="text-2xl font-bold text-gray-800">
                                    @if($absensiHariIni)
                                        <i class="fas fa-check-circle text-green-500"></i>
                                    @else
                                        <i class="fas fa-times-circle text-red-400"></i>
                                    @endif
                                </p>
                                @if($absensiHariIni)
                                <span class="text-xs text-gray-500 font-medium">
                                    {{ $absensiHariIni->check_in ? $absensiHariIni->check_in->format('H:i') : '-' }}
                                    @if($absensiHariIni->check_out)
                                        → {{ $absensiHariIni->check_out->format('H:i') }}
                                    @endif
                                </span>
                                @endif
                            </div>
                        </div>
                        <div class="w-10 h-10 rounded-xl {{ $absensiHariIni ? 'bg-green-50' : 'bg-red-50' }} flex items-center justify-center group-hover:scale-110 transition-transform">
                            <i class="fas fa-fingerprint {{ $absensiHariIni ? 'text-green-500' : 'text-red-400' }} text-lg"></i>
                        </div>
                    </div>
                    <div class="mt-2">
                        <span class="text-xs text-gray-400">
                            {{ $absensiHariIni ? 'Sudah absen' : 'Belum absen hari ini' }}
                        </span>
                    </div>
                </div>

                <!-- Jam Kerja -->
                <div class="bg-white rounded-2xl shadow-sm p-4 border border-gray-100 hover:shadow-md transition-shadow duration-200 group">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium text-gray-400 uppercase tracking-wider">Jam Kerja</p>
                            <p class="text-2xl font-bold text-gray-800 mt-1">{{ number_format($totalJamKerja ?? 0, 1) }}h</p>
                        </div>
                        <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center group-hover:scale-110 transition-transform">
                            <i class="fas fa-clock text-blue-500 text-lg"></i>
                        </div>
                    </div>
                    <div class="mt-2 flex items-center gap-1">
                        <span class="text-xs text-gray-400">Bulan ini</span>
                        <span class="w-1 h-1 rounded-full bg-gray-300"></span>
                        <span class="text-xs text-gray-400">{{ $absensiBulanIni ?? 0 }} hari hadir</span>
                    </div>
                </div>

                <!-- Sisa Cuti -->
                <div class="bg-white rounded-2xl shadow-sm p-4 border border-gray-100 hover:shadow-md transition-shadow duration-200 group">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium text-gray-400 uppercase tracking-wider">Sisa Cuti</p>
                            <p class="text-2xl font-bold text-gray-800 mt-1">{{ $sisaCuti ?? 12 }}</p>
                        </div>
                        <div class="w-10 h-10 rounded-xl bg-amber-50 flex items-center justify-center group-hover:scale-110 transition-transform">
                            <i class="fas fa-umbrella-beach text-amber-500 text-lg"></i>
                        </div>
                    </div>
                    <div class="mt-2 flex items-center gap-1">
                        <span class="text-xs text-gray-400">Approved: {{ $cutiApproved ?? 0 }}</span>
                        <span class="w-1 h-1 rounded-full bg-gray-300"></span>
                        <span class="text-xs text-gray-400">Pending: {{ $cutiPending ?? 0 }}</span>
                    </div>
                </div>

                <!-- Poin Sunnah -->
                <div class="bg-white rounded-2xl shadow-sm p-4 border border-gray-100 hover:shadow-md transition-shadow duration-200 group">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium text-gray-400 uppercase tracking-wider">Total Poin 7SPS</p>
                            <p class="text-2xl font-bold text-gray-800 mt-1">{{ number_format($sunnahBulanIni ?? 0) }}</p>
                        </div>
                        <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center group-hover:scale-110 transition-transform">
                            <i class="fas fa-star text-indigo-500 text-lg"></i>
                        </div>
                    </div>
                    <div class="mt-2 flex items-center gap-1">
                        <span class="text-xs text-gray-400">{{ $sunnahTotalDays ?? 0 }} hari tracking</span>
                        @if($sunnahHariIni)
                        <span class="w-1 h-1 rounded-full bg-gray-300"></span>
                        <span class="text-xs text-green-500">Hari ini: {{ $sunnahHariIni->total_poin ?? 0 }} pts</span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Charts Row -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <!-- Absensi Chart -->
                <div class="bg-white rounded-2xl shadow-sm p-5 border border-gray-100">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-semibold text-gray-700">📊 Kehadiran 7 Hari Terakhir</h3>
                        <span class="text-xs text-gray-400 bg-gray-50 px-2 py-1 rounded-full">Harian</span>
                    </div>
                    <div style="height: 200px;">
                        <canvas id="absensiChart"></canvas>
                    </div>
                </div>

                <!-- Sunnah Chart -->
                <div class="bg-white rounded-2xl shadow-sm p-5 border border-gray-100">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-semibold text-gray-700">⭐ Poin 7SPS Harian</h3>
                        <span class="text-xs text-gray-400 bg-gray-50 px-2 py-1 rounded-full">7 hari</span>
                    </div>
                    <div style="height: 200px;">
                        <canvas id="sunnahChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Detail Cards -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                <!-- Personal Info -->
                <div class="bg-white rounded-2xl shadow-sm p-5 border border-gray-100">
                    <div class="flex items-center gap-3 mb-4 pb-3 border-b border-gray-100">
                        <div class="w-12 h-12 rounded-full bg-gradient-to-br from-emerald-400 to-teal-500 flex items-center justify-center text-white font-bold text-lg shadow-sm flex-shrink-0">
                            {{ strtoupper(substr($user->nama_lengkap ?? '?', 0, 1)) }}
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-gray-800">{{ $user->nama_lengkap }}</h3>
                            <p class="text-xs text-gray-400">{{ $user->jabatan ?? 'Karyawan' }}</p>
                        </div>
                    </div>
                    <div class="space-y-2.5">
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-gray-500">ID Karyawan</span>
                            <span class="font-medium text-gray-700">{{ $user->kode_pegawai ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-gray-500">NIK</span>
                            <span class="font-medium text-gray-700">{{ $user->nik ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-gray-500">Divisi</span>
                            <span class="font-medium text-gray-700">{{ $user->divisi ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-gray-500">Tgl Bergabung</span>
                            <span class="font-medium text-gray-700">{{ $user->tanggal_bergabung ? $user->tanggal_bergabung->format('d-m-Y') : '-' }}</span>
                        </div>
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-gray-500">Email</span>
                            <span class="font-medium text-gray-700 truncate max-w-[140px]">{{ $user->email ?? '-' }}</span>
                        </div>
                    </div>
                </div>

                <!-- Recent Absensi -->
                <div class="bg-white rounded-2xl shadow-sm p-5 border border-gray-100">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-semibold text-gray-700">📋 Absensi Terbaru</h3>
                        <a href="{{ route('karyawan.absensi') }}" class="text-xs text-blue-500 hover:text-blue-600 font-medium">Lihat Semua →</a>
                    </div>
                    <div class="space-y-2.5 max-h-[240px] overflow-y-auto pr-1 custom-scrollbar">
                        @forelse($absensiTerbaru ?? [] as $absensi)
                        <div class="flex items-center justify-between p-2.5 rounded-xl hover:bg-gray-50 transition-colors duration-200">
                            <div>
                                <p class="text-sm font-medium text-gray-800">{{ $absensi->tanggal->format('d M Y') }}</p>
                                <p class="text-xs text-gray-400">
                                    <i class="fas fa-sign-in-alt mr-1"></i>{{ $absensi->check_in ? $absensi->check_in->format('H:i') : '-' }}
                                    @if($absensi->check_out)
                                        <i class="fas fa-sign-out-alt ml-2 mr-1"></i>{{ $absensi->check_out->format('H:i') }}
                                    @endif
                                </p>
                            </div>
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-medium {{ ($absensi->status ?? '') === 'Hadir' ? 'bg-green-100 text-green-700' : (($absensi->status ?? '') === 'Terlambat' ? 'bg-amber-100 text-amber-700' : 'bg-red-100 text-red-700') }}">
                                {{ $absensi->status ?? '-' }}
                            </span>
                        </div>
                        @empty
                        <div class="text-center py-6">
                            <i class="fas fa-fingerprint text-3xl text-gray-200 mb-2 block"></i>
                            <p class="text-sm text-gray-400">Belum ada data absensi</p>
                        </div>
                        @endforelse
                    </div>
                </div>

                <!-- Recent Cuti -->
                <div class="bg-white rounded-2xl shadow-sm p-5 border border-gray-100">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-semibold text-gray-700">📝 Pengajuan Cuti</h3>
                        <a href="{{ route('karyawan.cuti.dashboard') }}" class="text-xs text-blue-500 hover:text-blue-600 font-medium">Lihat Semua →</a>
                    </div>
                    <div class="space-y-2.5 max-h-[240px] overflow-y-auto pr-1 custom-scrollbar">
                        @forelse($cutiTerbaru ?? [] as $cuti)
                        <div class="p-2.5 rounded-xl hover:bg-gray-50 transition-colors duration-200">
                            <div class="flex items-center justify-between mb-0.5">
                                <span class="text-sm font-medium text-gray-800">{{ $cuti->jenis_cuti ?? '-' }}</span>
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-medium {{ ($cuti->status ?? '') === 'pending' ? 'bg-amber-100 text-amber-700' : (($cuti->status ?? '') === 'approved' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700') }}">
                                    {{ $cuti->status_label ?? '-' }}
                                </span>
                            </div>
                            <p class="text-xs text-gray-400">
                                <i class="far fa-calendar-alt mr-1"></i>
                                @if($cuti->tanggal_mulai && $cuti->tanggal_selesai)
                                    {{ $cuti->tanggal_mulai->format('d/m/Y') }} - {{ $cuti->tanggal_selesai->format('d/m/Y') }}
                                    <span class="mx-1">•</span>
                                    {{ $cuti->durasi ?? 0 }} hari
                                @else
                                    Tanggal belum ditentukan
                                @endif
                            </p>
                        </div>
                        @empty
                        <div class="text-center py-6">
                            <i class="fas fa-file-alt text-3xl text-gray-200 mb-2 block"></i>
                            <p class="text-sm text-gray-400">Belum ada pengajuan cuti</p>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Sunnah Detail Card -->
            @if($sunnahHariIni)
            <div class="bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500 rounded-2xl shadow-lg p-6 text-white">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-sm font-semibold opacity-90">🌟 7 Sunnah Rasul - Hari Ini</h3>
                        <p class="text-xs opacity-75 mt-0.5">{{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="bg-white/20 backdrop-blur-sm px-4 py-2 rounded-xl text-sm font-bold">
                            Total Poin: {{ $sunnahHariIni->total_poin ?? 0 }}
                        </span>
                        <span class="bg-white/20 backdrop-blur-sm px-3 py-2 rounded-xl text-xs">
                            <i class="fas fa-users mr-1"></i>Sholat Berjamaah: {{ $sunnahHariIni->jumlah_sholat_berjamaah ?? 0 }}/5
                        </span>
                    </div>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3">
                    @php
                        $poinConfig = \App\Models\SunnahDaily::getPoinConfig();
                        $wajibKeys = \App\Models\SunnahDaily::getSholatWajibKeys();
                        $wajibLabels = [
                            'sholat_subuh' => 'Subuh',
                            'sholat_zuhur' => 'Zuhur',
                            'sholat_asar' => 'Asar',
                            'sholat_maghrib' => 'Maghrib',
                            'sholat_isya' => 'Isya'
                        ];
                    @endphp
                    @foreach($poinConfig as $key => $config)
                        @if(!in_array($key, $wajibKeys))
                        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-3 text-center hover:bg-white/20 transition-colors">
                            <div class="text-2xl mb-1">{{ $config['icon'] }}</div>
                            <p class="text-[10px] font-medium truncate">{{ $config['label'] }}</p>
                            <p class="text-xs font-bold mt-1 {{ $sunnahHariIni->$key ? 'text-green-300' : 'text-red-300' }}">
                                {{ $sunnahHariIni->$key ? '✓ ' . $config['poin'] . ' pts' : '✗ 0' }}
                            </p>
                        </div>
                        @endif
                    @endforeach
                </div>
                <div class="mt-4 pt-4 border-t border-white/20 grid grid-cols-5 gap-2">
                    @foreach($wajibKeys as $key)
                    <div class="text-center bg-white/5 rounded-lg py-2 px-1">
                        <p class="text-xs opacity-75">{{ $wajibLabels[$key] ?? $key }}</p>
                        <p class="text-sm font-bold mt-0.5 {{ $sunnahHariIni->$key && $sunnahHariIni->{$key.'_berjamaah'} ? 'text-green-300' : ($sunnahHariIni->$key ? 'text-amber-300' : 'text-red-300') }}">
                            {{ $sunnahHariIni->$key && $sunnahHariIni->{$key.'_berjamaah'} ? '✅' : ($sunnahHariIni->$key ? '⚠️' : '❌') }}
                        </p>
                    </div>
                    @endforeach
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
    const absensiData = {!! json_encode($absensiChart['data'] ?? []) !!};
    new Chart(absensiCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($absensiChart['labels'] ?? []) !!},
            datasets: [{
                label: 'Kehadiran',
                data: absensiData,
                backgroundColor: absensiData.map(v => v > 0 ? '#10B981' : '#EF4444'),
                borderRadius: 6,
                borderSkipped: false
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: 'rgba(255,255,255,0.95)',
                    titleColor: '#1F2937',
                    bodyColor: '#6B7280',
                    borderColor: '#E5E7EB',
                    borderWidth: 1,
                    cornerRadius: 8,
                    padding: 10,
                    callbacks: {
                        label: function(context) {
                            return context.parsed.y > 0 ? '✅ Hadir' : '❌ Tidak Hadir';
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    max: 1,
                    ticks: {
                        stepSize: 1,
                        font: { size: 10 },
                        callback: function(value) {
                            return value === 1 ? 'Hadir' : 'Tidak';
                        }
                    },
                    grid: {
                        color: 'rgba(0,0,0,0.05)'
                    }
                },
                x: {
                    grid: { display: false },
                    ticks: { font: { size: 9 } }
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
                backgroundColor: 'rgba(99, 102, 241, 0.08)',
                borderWidth: 2.5,
                tension: 0.4,
                fill: true,
                pointRadius: 4,
                pointBackgroundColor: '#6366F1',
                pointHoverRadius: 6,
                pointHoverBackgroundColor: '#4F46E5'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
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
                y: {
                    beginAtZero: true,
                    ticks: { font: { size: 10 } },
                    grid: {
                        color: 'rgba(0,0,0,0.05)'
                    }
                },
                x: {
                    grid: { display: false },
                    ticks: { font: { size: 9 } }
                }
            }
        }
    });
    @endif
});

// Custom scrollbar style
document.addEventListener('DOMContentLoaded', function() {
    const style = document.createElement('style');
    style.textContent = `
        .custom-scrollbar::-webkit-scrollbar {
            width: 4px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #D1D5DB;
            border-radius: 10px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #9CA3AF;
        }
    `;
    document.head.appendChild(style);
});
</script>
@endpush
@endsection
