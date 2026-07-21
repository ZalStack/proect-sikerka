{{-- views/hr/dashboard.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="flex min-h-screen bg-gradient-to-br from-slate-50 to-slate-100">
    @include('layouts.sidebar')
    <div class="flex-1 transition-all duration-300 md:ml-64">
        <!-- Header -->
        <div class="bg-white/90 backdrop-blur-md shadow-sm border-b border-slate-200/50 sticky top-0 z-20">
            <div class="px-4 sm:px-6 lg:px-8 py-4">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h1 class="text-2xl sm:text-3xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">
                            Dashboard HR
                        </h1>
                        <p class="text-sm text-slate-500 mt-0.5 flex items-center gap-2">
                            <span class="inline-block w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                            Panel Manajemen Sumber Daya Manusia
                        </p>
                    </div>
                    <div class="mt-3 sm:mt-0 flex items-center gap-3 flex-wrap">
                        <div class="flex items-center gap-2 bg-white rounded-full px-4 py-2 shadow-sm border border-slate-200">
                            <i class="fas fa-calendar-day text-indigo-500 text-sm"></i>
                            <span class="text-xs font-medium text-slate-700">{{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}</span>
                        </div>
                        <div class="flex items-center gap-2 bg-white rounded-full px-4 py-2 shadow-sm border border-slate-200">
                            <i class="fas fa-clock text-indigo-500 text-sm"></i>
                            <span class="text-xs font-medium text-slate-700" id="clockDisplay">{{ \Carbon\Carbon::now()->format('H:i:s') }} WIB</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="p-4 sm:p-6 lg:p-8 space-y-6">
            <!-- Stats Grid -->
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                @php
                    $stats = [
                        [
                            'label' => 'Total Karyawan',
                            'value' => $totalKaryawan ?? 0,
                            'sub' => "Aktif: {$totalKaryawanAktif} • Resign: {$totalKaryawanResigned}",
                            'icon' => 'fa-users',
                            'color' => 'from-blue-500 to-blue-600',
                            'bg' => 'bg-blue-50',
                            'text' => 'text-blue-600'
                        ],
                        [
                            'label' => 'Tim HR',
                            'value' => $totalHr ?? 0,
                            'sub' => 'Personil HR',
                            'icon' => 'fa-user-tie',
                            'color' => 'from-purple-500 to-purple-600',
                            'bg' => 'bg-purple-50',
                            'text' => 'text-purple-600'
                        ],
                        [
                            'label' => 'Absensi Hari Ini',
                            'value' => $absensiHariIni ?? 0,
                            'sub' => "Terlambat: {$absensiTerlambat}",
                            'icon' => 'fa-fingerprint',
                            'color' => 'from-emerald-500 to-emerald-600',
                            'bg' => 'bg-emerald-50',
                            'text' => 'text-emerald-600'
                        ],
                        [
                            'label' => 'FHL Hari Ini',
                            'value' => $fhlHariIni ?? 0,
                            'sub' => 'Jumat Berkah',
                            'icon' => 'fa-mosque',
                            'color' => 'from-teal-500 to-teal-600',
                            'bg' => 'bg-teal-50',
                            'text' => 'text-teal-600'
                        ],
                        [
                            'label' => 'Cuti Pending',
                            'value' => $cutiPending ?? 0,
                            'sub' => "Approved: {$cutiApproved}",
                            'icon' => 'fa-clock',
                            'color' => 'from-amber-500 to-amber-600',
                            'bg' => 'bg-amber-50',
                            'text' => 'text-amber-600'
                        ],
                        [
                            'label' => '7SPS Pending',
                            'value' => $sunnahPending ?? 0,
                            'sub' => "Bulan ini: {$sunnahBulanIni}",
                            'icon' => 'fa-star',
                            'color' => 'from-rose-500 to-rose-600',
                            'bg' => 'bg-rose-50',
                            'text' => 'text-rose-600'
                        ]
                    ];
                @endphp

                @foreach($stats as $stat)
                <div class="bg-white rounded-2xl shadow-sm p-4 border border-slate-100 hover:shadow-lg transition-all duration-300 group relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-20 h-20 bg-gradient-to-br {{ $stat['color'] }} opacity-5 rounded-full -translate-y-8 translate-x-8 group-hover:scale-150 transition-transform duration-500"></div>
                    <div class="flex items-start justify-between relative z-10">
                        <div>
                            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">{{ $stat['label'] }}</p>
                            <p class="text-2xl font-bold text-slate-800 mt-1">{{ $stat['value'] }}</p>
                            <p class="text-[10px] text-slate-400 mt-1">{{ $stat['sub'] }}</p>
                        </div>
                        <div class="w-10 h-10 rounded-xl {{ $stat['bg'] }} flex items-center justify-center group-hover:scale-110 transition-transform shadow-sm">
                            <i class="fas {{ $stat['icon'] }} {{ $stat['text'] }} text-lg"></i>
                        </div>
                    </div>
                    <div class="absolute bottom-0 left-0 h-1 bg-gradient-to-r {{ $stat['color'] }} rounded-b-2xl transition-all duration-300 group-hover:w-full" style="width: 30%;"></div>
                </div>
                @endforeach
            </div>

            <!-- Charts Row -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Absensi Chart -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden hover:shadow-lg transition-shadow duration-300">
                    <div class="p-5 border-b border-slate-100">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-sm font-semibold text-slate-700 flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                                    Tren Absensi 7 Hari
                                </h3>
                                <p class="text-xs text-slate-400 mt-0.5">Data real-time kehadiran karyawan</p>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-1 bg-indigo-50 text-indigo-600 text-xs font-medium rounded-full">Harian</span>
                            </div>
                        </div>
                    </div>
                    <div class="p-5">
                        <div style="height: 240px;">
                            <canvas id="absensiChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Status Karyawan Chart -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden hover:shadow-lg transition-shadow duration-300">
                    <div class="p-5 border-b border-slate-100">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-sm font-semibold text-slate-700 flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-purple-500"></span>
                                    Komposisi Karyawan
                                </h3>
                                <p class="text-xs text-slate-400 mt-0.5">Distribusi status karyawan</p>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-1 bg-purple-50 text-purple-600 text-xs font-medium rounded-full">Per hari ini</span>
                            </div>
                        </div>
                    </div>
                    <div class="p-5">
                        <div style="height: 240px;">
                            <canvas id="statusKaryawanChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Second Row -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Top Sunnah Performers -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden hover:shadow-lg transition-shadow duration-300">
                    <div class="p-5 border-b border-slate-100">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-sm font-semibold text-slate-700 flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                                    Top 7SPS Performers
                                </h3>
                                <p class="text-xs text-slate-400 mt-0.5">Performa terbaik bulan ini</p>
                            </div>
                            <span class="px-2 py-1 bg-amber-50 text-amber-600 text-xs font-medium rounded-full">🏆</span>
                        </div>
                    </div>
                    <div class="p-4 space-y-2 max-h-[320px] overflow-y-auto custom-scrollbar">
                        @forelse($topSunnah ?? [] as $index => $sunnah)
                        <div class="flex items-center justify-between p-3 rounded-xl transition-all duration-200 {{ $index === 0 ? 'bg-gradient-to-r from-amber-50 to-yellow-50 border border-amber-200/50' : 'hover:bg-slate-50' }}">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center text-white font-bold text-xs shadow-sm flex-shrink-0
                                    {{ $index === 0 ? 'bg-gradient-to-br from-amber-400 to-orange-500' :
                                    ($index === 1 ? 'bg-gradient-to-br from-slate-300 to-slate-400' :
                                    ($index === 2 ? 'bg-gradient-to-br from-amber-600 to-amber-700' : 'bg-slate-200 text-slate-600')) }}">
                                    {{ $index + 1 }}
                                </div>
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-slate-800 truncate">{{ $sunnah->karyawan->nama_lengkap ?? 'Tidak Diketahui' }}</p>
                                    <p class="text-xs text-slate-400 truncate">{{ $sunnah->karyawan->divisi ?? '-' }}</p>
                                </div>
                            </div>
                            <div class="text-right flex-shrink-0 ml-2">
                                <p class="text-sm font-bold text-indigo-600">{{ number_format($sunnah->total_poin ?? 0) }}</p>
                                <p class="text-[10px] text-slate-400">{{ $sunnah->total_days ?? 0 }} hari</p>
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-8">
                            <i class="fas fa-star text-3xl text-slate-200 mb-2 block"></i>
                            <p class="text-sm text-slate-400">Belum ada data 7SPS bulan ini</p>
                        </div>
                        @endforelse
                    </div>
                </div>

                <!-- Karyawan Terbaru -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden hover:shadow-lg transition-shadow duration-300">
                    <div class="p-5 border-b border-slate-100">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-sm font-semibold text-slate-700 flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                                    Karyawan Terbaru
                                </h3>
                                <p class="text-xs text-slate-400 mt-0.5">Bergabung terakhir</p>
                            </div>
                            <a href="{{ route('hr.karyawan.index') }}" class="text-xs text-blue-500 hover:text-blue-600 font-medium flex items-center gap-1">
                                Lihat Semua <i class="fas fa-arrow-right text-[10px]"></i>
                            </a>
                        </div>
                    </div>
                    <div class="p-4 space-y-2 max-h-[320px] overflow-y-auto custom-scrollbar">
                        @forelse($karyawanTerbaru ?? [] as $karyawan)
                        <div class="flex items-center gap-3 p-3 rounded-xl hover:bg-slate-50 transition-colors duration-200">
                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center text-white font-bold text-sm shadow-sm flex-shrink-0">
                                {{ strtoupper(substr($karyawan->nama_lengkap ?? '?', 0, 1)) }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-slate-800 truncate">{{ $karyawan->nama_lengkap ?? '-' }}</p>
                                <p class="text-xs text-slate-400 truncate">
                                    <span class="inline-block w-1.5 h-1.5 rounded-full bg-slate-300 mr-1"></span>
                                    {{ $karyawan->jabatan ?? 'N/A' }} • {{ $karyawan->divisi ?? '-' }}
                                </p>
                            </div>
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-medium flex-shrink-0
                                {{ ($karyawan->status ?? '') === 'Karyawan Tetap' ? 'bg-emerald-100 text-emerald-700' :
                                (($karyawan->status ?? '') === 'Contract' ? 'bg-amber-100 text-amber-700' :
                                (($karyawan->is_resigned ?? false) ? 'bg-red-100 text-red-700' : 'bg-blue-100 text-blue-700')) }}">
                                {{ $karyawan->status_label ?? $karyawan->status ?? 'Magang' }}
                            </span>
                        </div>
                        @empty
                        <div class="text-center py-8">
                            <i class="fas fa-users text-3xl text-slate-200 mb-2 block"></i>
                            <p class="text-sm text-slate-400">Belum ada karyawan baru</p>
                        </div>
                        @endforelse
                    </div>
                </div>

                <!-- Absensi Terbaru -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden hover:shadow-lg transition-shadow duration-300">
                    <div class="p-5 border-b border-slate-100">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-sm font-semibold text-slate-700 flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                    Absensi Terbaru
                                </h3>
                                <p class="text-xs text-slate-400 mt-0.5">Aktivitas check-in terakhir</p>
                            </div>
                            <a href="{{ route('hr.absensi.index') }}" class="text-xs text-blue-500 hover:text-blue-600 font-medium flex items-center gap-1">
                                Lihat Semua <i class="fas fa-arrow-right text-[10px]"></i>
                            </a>
                        </div>
                    </div>
                    <div class="p-4 space-y-2 max-h-[320px] overflow-y-auto custom-scrollbar">
                        @forelse($absensiTerbaru ?? [] as $absensi)
                        <div class="flex items-center gap-3 p-3 rounded-xl hover:bg-slate-50 transition-colors duration-200">
                            <span class="w-2.5 h-2.5 rounded-full flex-shrink-0
                                {{ ($absensi->status ?? '') === 'Hadir' ? 'bg-emerald-500' :
                                (($absensi->status ?? '') === 'Terlambat' ? 'bg-amber-500' : 'bg-red-500') }}">
                            </span>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-slate-800 truncate">{{ $absensi->karyawan->nama_lengkap ?? 'Tidak Diketahui' }}</p>
                                <p class="text-xs text-slate-400 flex items-center gap-2">
                                    <span><i class="fas fa-sign-in-alt mr-1"></i>{{ $absensi->check_in ? $absensi->check_in->format('H:i') : '-' }}</span>
                                    @if($absensi->check_out)
                                    <span><i class="fas fa-sign-out-alt mr-1"></i>{{ $absensi->check_out->format('H:i') }}</span>
                                    @endif
                                </p>
                            </div>
                            <div class="text-right flex-shrink-0">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-medium
                                    {{ ($absensi->status ?? '') === 'Hadir' ? 'bg-emerald-100 text-emerald-700' :
                                    (($absensi->status ?? '') === 'Terlambat' ? 'bg-amber-100 text-amber-700' : 'bg-red-100 text-red-700') }}">
                                    {{ $absensi->status ?? '-' }}
                                </span>
                                <p class="text-[10px] text-slate-400 mt-0.5">{{ $absensi->tanggal ? $absensi->tanggal->format('d/m/Y') : '-' }}</p>
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-8">
                            <i class="fas fa-fingerprint text-3xl text-slate-200 mb-2 block"></i>
                            <p class="text-sm text-slate-400">Belum ada data absensi</p>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Third Row - Cuti Chart & Cuti Terbaru -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Cuti Chart -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden hover:shadow-lg transition-shadow duration-300">
                    <div class="p-5 border-b border-slate-100">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-sm font-semibold text-slate-700 flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-cyan-500"></span>
                                    Trend Cuti 6 Bulan
                                </h3>
                                <p class="text-xs text-slate-400 mt-0.5">Statistik pengajuan cuti per bulan</p>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-1 bg-cyan-50 text-cyan-600 text-xs font-medium rounded-full">Bulanan</span>
                            </div>
                        </div>
                    </div>
                    <div class="p-5">
                        <div style="height: 240px;">
                            <canvas id="cutiChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Cuti Terbaru -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden hover:shadow-lg transition-shadow duration-300">
                    <div class="p-5 border-b border-slate-100">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-sm font-semibold text-slate-700 flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                                    Pengajuan Cuti Terbaru
                                </h3>
                                <p class="text-xs text-slate-400 mt-0.5">Menunggu persetujuan & terbaru</p>
                            </div>
                            <a href="{{ route('hr.cuti.index') }}" class="text-xs text-blue-500 hover:text-blue-600 font-medium flex items-center gap-1">
                                Lihat Semua <i class="fas fa-arrow-right text-[10px]"></i>
                            </a>
                        </div>
                    </div>
                    <div class="p-4 space-y-2 max-h-[320px] overflow-y-auto custom-scrollbar">
                        @forelse($cutiTerbaru ?? [] as $cuti)
                        <div class="flex items-center gap-3 p-3 rounded-xl hover:bg-slate-50 transition-colors duration-200">
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-medium flex-shrink-0
                                {{ ($cuti->status ?? '') === 'pending' ? 'bg-amber-100 text-amber-700' :
                                (($cuti->status ?? '') === 'approved' ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700') }}">
                                {{ $cuti->status_label ?? '-' }}
                            </span>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-slate-800 truncate">{{ $cuti->karyawan->nama_lengkap ?? 'Tidak Diketahui' }}</p>
                                <p class="text-xs text-slate-400 truncate flex items-center gap-1">
                                    <i class="fas fa-tag text-[10px]"></i>{{ $cuti->jenis_cuti ?? '-' }}
                                    <span class="text-slate-300">•</span>
                                    <i class="far fa-calendar-alt text-[10px]"></i>{{ $cuti->durasi ?? 0 }} hari
                                </p>
                            </div>
                            <p class="text-[10px] text-slate-400 flex-shrink-0">{{ $cuti->tanggal_pengajuan ? $cuti->tanggal_pengajuan->format('d/m/Y') : '-' }}</p>
                        </div>
                        @empty
                        <div class="text-center py-8">
                            <i class="fas fa-file-alt text-3xl text-slate-200 mb-2 block"></i>
                            <p class="text-sm text-slate-400">Belum ada pengajuan cuti</p>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Perjalanan Dinas Terbaru -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden hover:shadow-lg transition-shadow duration-300">
                <div class="p-5 border-b border-slate-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-semibold text-slate-700 flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-violet-500"></span>
                                Perjalanan Dinas Terbaru
                            </h3>
                            <p class="text-xs text-slate-400 mt-0.5">Pengajuan perjalanan dinas terkini</p>
                        </div>
                        <a href="{{ route('hr.perjalanan-dinas.index') }}" class="text-xs text-blue-500 hover:text-blue-600 font-medium flex items-center gap-1">
                            Lihat Semua <i class="fas fa-arrow-right text-[10px]"></i>
                        </a>
                    </div>
                </div>
                <div class="p-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 max-h-[300px] overflow-y-auto custom-scrollbar">
                        @forelse(($perjalananDinasTerbaru ?? []) as $pd)
                        <div class="p-3 rounded-xl border border-slate-100 hover:border-violet-200 hover:bg-violet-50/50 transition-all duration-200">
                            <div class="flex items-start justify-between">
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-slate-800 truncate">{{ $pd->judul ?? '-' }}</p>
                                    <p class="text-xs text-slate-400 truncate">{{ $pd->karyawan->nama_lengkap ?? 'Tidak Diketahui' }}</p>
                                </div>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-medium flex-shrink-0 ml-2
                                    {{ ($pd->status ?? '') === 'pending' ? 'bg-amber-100 text-amber-700' :
                                    (($pd->status ?? '') === 'approved' ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700') }}">
                                    {{ $pd->status_label ?? $pd->status ?? '-' }}
                                </span>
                            </div>
                            <div class="mt-1.5 flex items-center gap-3 text-[10px] text-slate-400">
                                <span><i class="far fa-calendar-alt mr-1"></i>{{ $pd->tanggal_mulai ? $pd->tanggal_mulai->format('d/m/Y') : '-' }}</span>
                                <span>→</span>
                                <span>{{ $pd->tanggal_selesai ? $pd->tanggal_selesai->format('d/m/Y') : '-' }}</span>
                            </div>
                        </div>
                        @empty
                        <div class="col-span-full text-center py-8">
                            <i class="fas fa-briefcase text-3xl text-slate-200 mb-2 block"></i>
                            <p class="text-sm text-slate-400">Belum ada perjalanan dinas</p>
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
    // Update clock
    function updateClock() {
        const now = new Date();
        const wib = new Date(now.getTime() + 7 * 60 * 60 * 1000);
        document.getElementById('clockDisplay').textContent =
            wib.toISOString().slice(11, 19) + ' WIB';
    }
    setInterval(updateClock, 1000);

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
                borderWidth: 3,
                tension: 0.4,
                fill: true,
                pointRadius: 5,
                pointBackgroundColor: '#4F46E5',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointHoverRadius: 7,
                pointHoverBackgroundColor: '#4338CA'
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
                    cornerRadius: 10,
                    padding: 12,
                    boxShadow: '0 4px 6px -1px rgba(0,0,0,0.1)'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1,
                        font: { size: 11, family: 'Inter' }
                    },
                    grid: {
                        color: 'rgba(0,0,0,0.04)',
                        drawBorder: false
                    }
                },
                x: {
                    grid: { display: false },
                    ticks: {
                        font: { size: 10, family: 'Inter' },
                        maxRotation: 0
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
                backgroundColor: {!! json_encode($statusKaryawanChart['colors'] ?? ['#10B981', '#F59E0B', '#3B82F6', '#EF4444']) !!},
                borderWidth: 4,
                borderColor: '#ffffff',
                hoverOffset: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '72%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        boxWidth: 12,
                        padding: 14,
                        font: { size: 11, family: 'Inter', weight: '500' },
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
                    cornerRadius: 10,
                    padding: 12
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
                        padding: 12,
                        font: { size: 11, family: 'Inter', weight: '500' },
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
                    cornerRadius: 10,
                    padding: 12
                }
            },
            scales: {
                x: {
                    stacked: true,
                    grid: { display: false },
                    ticks: { font: { size: 10, family: 'Inter' } }
                },
                y: {
                    stacked: true,
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1,
                        font: { size: 11, family: 'Inter' }
                    },
                    grid: {
                        color: 'rgba(0,0,0,0.04)',
                        drawBorder: false
                    }
                }
            }
        }
    });
    @endif
});

// Custom scrollbar
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
        .custom-scrollbar {
            scrollbar-width: thin;
            scrollbar-color: #D1D5DB transparent;
        }
    `;
    document.head.appendChild(style);
});
</script>
@endpush
@endsection
