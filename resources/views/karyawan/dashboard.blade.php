{{-- views/karyawan/dashboard.blade.php --}}
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
                        <h1 class="text-2xl sm:text-3xl font-bold font-['Montserrat'] text-[#161758] bg-clip-text">
                            Dashboard Karyawan
                        </h1>
                        <p class="text-sm text-slate-500 mt-0.5 flex items-center gap-2">
                            Selamat datang, <span class="font-medium text-slate-700">{{ $user->nama_lengkap }}</span>
                        </p>
                    </div>
                    <div class="mt-3 sm:mt-0 flex items-center gap-3 flex-wrap">
                        <div class="flex items-center gap-2 bg-white rounded-full px-4 py-2 shadow-sm border border-slate-200">
                            <i class="fas fa-calendar-day text-emerald-500 text-sm"></i>
                            <span class="text-xs font-medium text-slate-700">{{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}</span>
                        </div>
                        <span class="px-4 py-2 rounded-full text-xs font-medium shadow-sm border
                            {{ ($user->status ?? '') === 'Karyawan Tetap' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' :
                            (($user->status ?? '') === 'Contract' ? 'bg-amber-50 text-amber-700 border-amber-200' : 'bg-blue-50 text-blue-700 border-blue-200') }}">
                            <i class="fas fa-briefcase mr-1.5"></i>
                            {{ $user->status_label ?? $user->status ?? 'Karyawan' }}
                        </span>
                        @if($user->divisi)
                        <span class="px-3 py-2 rounded-full text-xs font-medium bg-slate-100 text-slate-600 border border-slate-200">
                            <i class="fas fa-building mr-1.5"></i>
                            {{ $user->divisi }}
                        </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="p-4 sm:p-6 lg:p-8 space-y-6">
            <!-- Stats Grid -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                @php
                    // Prepare absensi value
                    $absensiValue = '';
                    if ($absensiHariIni) {
                        $absensiValue = '<i class="fas fa-check-circle text-emerald-500 text-xl"></i> ';
                        if ($absensiHariIni->check_in) {
                            $absensiValue .= $absensiHariIni->check_in->format('H:i');
                        } else {
                            $absensiValue .= '-';
                        }
                        if ($absensiHariIni->check_out) {
                            $absensiValue .= ' → ' . $absensiHariIni->check_out->format('H:i');
                        }
                    } else {
                        $absensiValue = '<i class="fas fa-times-circle text-red-400 text-xl"></i>';
                    }

                    $stats = [
                        [
                            'label' => 'Absensi Hari Ini',
                            'value' => $absensiValue,
                            'sub' => $absensiHariIni ? 'Sudah absen' : 'Belum absen hari ini',
                            'icon' => 'fa-fingerprint',
                            'color' => $absensiHariIni ? 'from-emerald-500 to-emerald-600' : 'from-red-500 to-red-600',
                            'bg' => $absensiHariIni ? 'bg-emerald-50' : 'bg-red-50',
                            'text' => $absensiHariIni ? 'text-emerald-600' : 'text-red-600',
                            'is_html' => true
                        ],
                        [
                            'label' => 'Jam Kerja',
                            'value' => number_format($totalJamKerja ?? 0, 1) . 'h',
                            'sub' => ($absensiBulanIni ?? 0) . ' hari hadir bulan ini',
                            'icon' => 'fa-clock',
                            'color' => 'from-blue-500 to-blue-600',
                            'bg' => 'bg-blue-50',
                            'text' => 'text-blue-600'
                        ],
                        [
                            'label' => 'Sisa Cuti',
                            'value' => $sisaCuti ?? 12,
                            'sub' => "Approved: " . ($cutiApproved ?? 0) . " • Pending: " . ($cutiPending ?? 0),
                            'icon' => 'fa-umbrella-beach',
                            'color' => 'from-amber-500 to-amber-600',
                            'bg' => 'bg-amber-50',
                            'text' => 'text-amber-600'
                        ],
                        [
                            'label' => 'Poin 7SPS',
                            'value' => number_format($sunnahBulanIni ?? 0),
                            'sub' => ($sunnahTotalDays ?? 0) . ' hari tracking' .
                                ($sunnahHariIni ? ' • Hari ini: ' . ($sunnahHariIni->total_poin ?? 0) . ' pts' : ''),
                            'icon' => 'fa-star',
                            'color' => 'from-indigo-500 to-indigo-600',
                            'bg' => 'bg-indigo-50',
                            'text' => 'text-indigo-600'
                        ]
                    ];
                @endphp

                @foreach($stats as $stat)
                <div class="bg-white rounded-2xl shadow-sm p-4 border border-slate-100 hover:shadow-lg transition-all duration-300 group relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-20 h-20 bg-gradient-to-br {{ $stat['color'] }} opacity-5 rounded-full -translate-y-8 translate-x-8 group-hover:scale-150 transition-transform duration-500"></div>
                    <div class="flex items-start justify-between relative z-10">
                        <div>
                            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">{{ $stat['label'] }}</p>
                            <p class="text-xl font-bold text-slate-800 mt-1">
                                @if(isset($stat['is_html']) && $stat['is_html'])
                                    {!! $stat['value'] !!}
                                @else
                                    {{ $stat['value'] }}
                                @endif
                            </p>
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
                                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                    Kehadiran 7 Hari Terakhir
                                </h3>
                                <p class="text-xs text-slate-400 mt-0.5">Status kehadiran harian</p>
                            </div>
                            <span class="px-2 py-1 bg-emerald-50 text-emerald-600 text-xs font-medium rounded-full">Harian</span>
                        </div>
                    </div>
                    <div class="p-5">
                        <div style="height: 200px;">
                            <canvas id="absensiChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Sunnah Chart -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden hover:shadow-lg transition-shadow duration-300">
                    <div class="p-5 border-b border-slate-100">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-sm font-semibold text-slate-700 flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                                    Poin 7SPS Harian
                                </h3>
                                <p class="text-xs text-slate-400 mt-0.5">Perkembangan poin 7 hari</p>
                            </div>
                            <span class="px-2 py-1 bg-indigo-50 text-indigo-600 text-xs font-medium rounded-full">⭐</span>
                        </div>
                    </div>
                    <div class="p-5">
                        <div style="height: 200px;">
                            <canvas id="sunnahChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detail Cards -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Personal Info -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden hover:shadow-lg transition-shadow duration-300">
                    <div class="p-5 border-b border-slate-100 bg-gradient-to-r from-emerald-50 to-teal-50">
                        <div class="flex items-center gap-3">
                            <div class="w-14 h-14 rounded-full bg-gradient-to-br from-emerald-400 to-teal-500 flex items-center justify-center text-white font-bold text-xl shadow-md flex-shrink-0">
                                {{ strtoupper(substr($user->nama_lengkap ?? '?', 0, 1)) }}
                            </div>
                            <div>
                                <h3 class="text-base font-semibold text-slate-800">{{ $user->nama_lengkap }}</h3>
                                <p class="text-sm text-slate-500">{{ $user->jabatan ?? 'Karyawan' }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="p-5 space-y-3">
                        <div class="flex justify-between items-center py-1.5 border-b border-slate-50">
                            <span class="text-sm text-slate-500 flex items-center gap-2">
                                <i class="fas fa-id-card text-slate-300 text-xs w-4"></i> ID Karyawan
                            </span>
                            <span class="text-sm font-medium text-slate-700">{{ $user->kode_pegawai ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between items-center py-1.5 border-b border-slate-50">
                            <span class="text-sm text-slate-500 flex items-center gap-2">
                                <i class="fas fa-credit-card text-slate-300 text-xs w-4"></i> NIK
                            </span>
                            <span class="text-sm font-medium text-slate-700">{{ $user->nik ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between items-center py-1.5 border-b border-slate-50">
                            <span class="text-sm text-slate-500 flex items-center gap-2">
                                <i class="fas fa-building text-slate-300 text-xs w-4"></i> Divisi
                            </span>
                            <span class="text-sm font-medium text-slate-700">{{ $user->divisi ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between items-center py-1.5 border-b border-slate-50">
                            <span class="text-sm text-slate-500 flex items-center gap-2">
                                <i class="fas fa-calendar-alt text-slate-300 text-xs w-4"></i> Bergabung
                            </span>
                            <span class="text-sm font-medium text-slate-700">{{ $user->tanggal_bergabung ? $user->tanggal_bergabung->format('d-m-Y') : '-' }}</span>
                        </div>
                        <div class="flex justify-between items-center py-1.5">
                            <span class="text-sm text-slate-500 flex items-center gap-2">
                                <i class="fas fa-envelope text-slate-300 text-xs w-4"></i> Email
                            </span>
                            <span class="text-sm font-medium text-slate-700 truncate max-w-[180px]">{{ $user->email ?? '-' }}</span>
                        </div>
                    </div>
                </div>

                <!-- Recent Absensi -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden hover:shadow-lg transition-shadow duration-300">
                    <div class="p-5 border-b border-slate-100">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-sm font-semibold text-slate-700 flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                                    Absensi Terbaru
                                </h3>
                                <p class="text-xs text-slate-400 mt-0.5">Riwayat check-in terakhir</p>
                            </div>
                            <a href="{{ route('karyawan.absensi') }}" class="text-xs text-blue-500 hover:text-blue-600 font-medium flex items-center gap-1">
                                Lihat Semua <i class="fas fa-arrow-right text-[10px]"></i>
                            </a>
                        </div>
                    </div>
                    <div class="p-4 space-y-2 max-h-[280px] overflow-y-auto custom-scrollbar">
                        @forelse($absensiTerbaru ?? [] as $absensi)
                        <div class="flex items-center justify-between p-3 rounded-xl hover:bg-slate-50 transition-colors duration-200">
                            <div class="min-w-0">
                                <p class="text-sm font-medium text-slate-800">{{ $absensi->tanggal ? $absensi->tanggal->format('d M Y') : '-' }}</p>
                                <p class="text-xs text-slate-400 flex items-center gap-2">
                                    <span><i class="fas fa-sign-in-alt mr-1"></i>{{ $absensi->check_in ? $absensi->check_in->format('H:i') : '-' }}</span>
                                    @if($absensi->check_out)
                                    <span><i class="fas fa-sign-out-alt mr-1"></i>{{ $absensi->check_out->format('H:i') }}</span>
                                    @endif
                                </p>
                            </div>
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-medium flex-shrink-0 ml-2
                                {{ ($absensi->status ?? '') === 'Hadir' ? 'bg-emerald-100 text-emerald-700' :
                                (($absensi->status ?? '') === 'Terlambat' ? 'bg-amber-100 text-amber-700' : 'bg-red-100 text-red-700') }}">
                                {{ $absensi->status ?? '-' }}
                            </span>
                        </div>
                        @empty
                        <div class="text-center py-8">
                            <i class="fas fa-fingerprint text-3xl text-slate-200 mb-2 block"></i>
                            <p class="text-sm text-slate-400">Belum ada data absensi</p>
                        </div>
                        @endforelse
                    </div>
                </div>

                <!-- Recent Cuti -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden hover:shadow-lg transition-shadow duration-300">
                    <div class="p-5 border-b border-slate-100">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-sm font-semibold text-slate-700 flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                                    Pengajuan Cuti
                                </h3>
                                <p class="text-xs text-slate-400 mt-0.5">Status pengajuan terbaru</p>
                            </div>
                            <a href="{{ route('karyawan.cuti.dashboard') }}" class="text-xs text-blue-500 hover:text-blue-600 font-medium flex items-center gap-1">
                                Lihat Semua <i class="fas fa-arrow-right text-[10px]"></i>
                            </a>
                        </div>
                    </div>
                    <div class="p-4 space-y-2 max-h-[280px] overflow-y-auto custom-scrollbar">
                        @forelse($cutiTerbaru ?? [] as $cuti)
                        <div class="p-3 rounded-xl hover:bg-slate-50 transition-colors duration-200">
                            <div class="flex items-center justify-between mb-0.5">
                                <span class="text-sm font-medium text-slate-800">{{ $cuti->jenis_cuti ?? '-' }}</span>
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-medium flex-shrink-0 ml-2
                                    {{ ($cuti->status ?? '') === 'pending' ? 'bg-amber-100 text-amber-700' :
                                    (($cuti->status ?? '') === 'approved' ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700') }}">
                                    {{ $cuti->status_label ?? '-' }}
                                </span>
                            </div>
                            <p class="text-xs text-slate-400 flex items-center gap-1">
                                <i class="far fa-calendar-alt mr-1"></i>
                                @if($cuti->tanggal_mulai && $cuti->tanggal_selesai)
                                    {{ $cuti->tanggal_mulai->format('d/m/Y') }} - {{ $cuti->tanggal_selesai->format('d/m/Y') }}
                                    <span class="text-slate-300">•</span>
                                    {{ $cuti->durasi ?? 0 }} hari
                                @else
                                    Tanggal belum ditentukan
                                @endif
                            </p>
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

            <!-- Sunnah Detail Card -->
            @if($sunnahHariIni)
            <div class="bg-gradient-to-r from-indigo-600 via-purple-600 to-rose-600 rounded-2xl shadow-xl p-6 text-white relative overflow-hidden">
                <div class="absolute top-0 right-0 w-64 h-64 bg-white/5 rounded-full -translate-y-32 translate-x-32"></div>
                <div class="absolute bottom-0 left-0 w-48 h-48 bg-white/5 rounded-full translate-y-24 -translate-x-24"></div>

                <div class="relative z-10">
                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-4">
                        <div>
                            <h3 class="text-lg font-bold flex items-center gap-2">
                                <span class="text-2xl">🌟</span> 7 Sunnah Rasul - Hari Ini
                            </h3>
                            <p class="text-sm opacity-80 mt-0.5">{{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}</p>
                        </div>
                        <div class="flex items-center gap-3 mt-3 sm:mt-0 flex-wrap">
                            <span class="bg-white/20 backdrop-blur-sm px-4 py-2 rounded-xl text-sm font-bold">
                                Total: {{ $sunnahHariIni->total_poin ?? 0 }} poin
                            </span>
                            <span class="bg-white/20 backdrop-blur-sm px-3 py-2 rounded-xl text-xs">
                                <i class="fas fa-users mr-1"></i>Berjamaah: {{ $sunnahHariIni->jumlah_sholat_berjamaah ?? 0 }}/5
                            </span>
                            <span class="bg-white/20 backdrop-blur-sm px-3 py-2 rounded-xl text-xs">
                                <i class="fas fa-clock mr-1"></i>Wajib: {{ $sunnahHariIni->poin_sholat_wajib ?? 0 }} pts
                            </span>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3">
                        @php
                            $poinConfig = \App\Models\SunnahDaily::getPoinConfig();
                            $wajibKeys = \App\Models\SunnahDaily::getSholatWajibKeys();
                            $wajibLabels = [
                                'sholat_subuh' => '🌅 Subuh',
                                'sholat_zuhur' => '☀️ Zuhur',
                                'sholat_asar' => '🌤️ Asar',
                                'sholat_maghrib' => '🌆 Maghrib',
                                'sholat_isya' => '🌙 Isya'
                            ];
                        @endphp
                        @foreach($poinConfig as $key => $config)
                            @if(!in_array($key, $wajibKeys))
                            <div class="bg-white/10 backdrop-blur-sm rounded-xl p-3 text-center hover:bg-white/20 transition-colors border border-white/5">
                                <div class="text-2xl mb-0.5">{{ $config['icon'] }}</div>
                                <p class="text-[10px] font-medium truncate">{{ $config['label'] }}</p>
                                <p class="text-xs font-bold mt-1 {{ $sunnahHariIni->$key ? 'text-emerald-300' : 'text-rose-300' }}">
                                    {{ $sunnahHariIni->$key ? '✅ ' . $config['poin'] . ' pts' : '✗ 0' }}
                                </p>
                            </div>
                            @endif
                        @endforeach
                    </div>

                    <div class="mt-4 pt-4 border-t border-white/20">
                        <p class="text-xs font-medium opacity-75 mb-2">📿 Sholat Wajib Berjamaah</p>
                        <div class="grid grid-cols-5 gap-2">
                            @foreach($wajibKeys as $key)
                            <div class="text-center bg-white/5 rounded-lg py-2 px-1 border border-white/5">
                                <p class="text-[10px] opacity-75">{{ $wajibLabels[$key] ?? $key }}</p>
                                <p class="text-sm font-bold mt-0.5
                                    {{ $sunnahHariIni->$key && $sunnahHariIni->{$key.'_berjamaah'} ? 'text-emerald-300' :
                                    ($sunnahHariIni->$key ? 'text-amber-300' : 'text-rose-300') }}">
                                    {{ $sunnahHariIni->$key && $sunnahHariIni->{$key.'_berjamaah'} ? '✅' :
                                    ($sunnahHariIni->$key ? '⚠️' : '❌') }}
                                </p>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            @else
            <div class="bg-gradient-to-r from-indigo-600 via-purple-600 to-rose-600 rounded-2xl shadow-xl p-6 text-white relative overflow-hidden">
                <div class="absolute top-0 right-0 w-64 h-64 bg-white/5 rounded-full -translate-y-32 translate-x-32"></div>
                <div class="relative z-10 flex items-center justify-between flex-wrap gap-4">
                    <div>
                        <h3 class="text-lg font-bold flex items-center gap-2">
                            <span class="text-2xl">🌟</span> 7 Sunnah Rasul
                        </h3>
                        <p class="text-sm opacity-80 mt-0.5">Belum mengisi 7SPS hari ini</p>
                    </div>
                    <a href="{{ route('karyawan.sunnah.dashboard') }}" class="bg-white/20 backdrop-blur-sm hover:bg-white/30 transition-colors px-5 py-2.5 rounded-xl text-sm font-medium flex items-center gap-2">
                        Isi Sekarang <i class="fas fa-arrow-right text-xs"></i>
                    </a>
                </div>
            </div>
            @endif

            <!-- Perjalanan Dinas Terbaru -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden hover:shadow-lg transition-shadow duration-300">
                <div class="p-5 border-b border-slate-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-semibold text-slate-700 flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-violet-500"></span>
                                Perjalanan Dinas
                            </h3>
                            <p class="text-xs text-slate-400 mt-0.5">Pengajuan perjalanan dinas Anda</p>
                        </div>
                        <a href="{{ route('karyawan.perjalanan-dinas.index') }}" class="text-xs text-blue-500 hover:text-blue-600 font-medium flex items-center gap-1">
                            Lihat Semua <i class="fas fa-arrow-right text-[10px]"></i>
                        </a>
                    </div>
                </div>
                <div class="p-4">
                    @php
                        $perjalananDinas = $perjalananDinasTerbaru ?? collect();
                    @endphp
                    @if($perjalananDinas->count() > 0)
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 max-h-[260px] overflow-y-auto custom-scrollbar">
                        @foreach($perjalananDinas as $pd)
                        <div class="p-3 rounded-xl border border-slate-100 hover:border-violet-200 hover:bg-violet-50/50 transition-all duration-200">
                            <div class="flex items-start justify-between">
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-slate-800 truncate">{{ $pd->judul ?? '-' }}</p>
                                    <p class="text-xs text-slate-400 truncate">{{ $pd->agenda ?? '-' }}</p>
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
                        @endforeach
                    </div>
                    @else
                    <div class="text-center py-8">
                        <i class="fas fa-briefcase text-3xl text-slate-200 mb-2 block"></i>
                        <p class="text-sm text-slate-400">Belum ada perjalanan dinas</p>
                        <a href="{{ route('karyawan.perjalanan-dinas.create') }}" class="mt-2 inline-block text-sm text-blue-500 hover:text-blue-600 font-medium">
                            Ajukan Perjalanan Dinas <i class="fas fa-arrow-right ml-1"></i>
                        </a>
                    </div>
                    @endif
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
                borderSkipped: false,
                barPercentage: 0.7
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
                        font: { size: 10, family: 'Inter' },
                        callback: function(value) {
                            return value === 1 ? 'Hadir' : '';
                        }
                    },
                    grid: {
                        color: 'rgba(0,0,0,0.04)',
                        drawBorder: false
                    }
                },
                x: {
                    grid: { display: false },
                    ticks: {
                        font: { size: 9, family: 'Inter' },
                        maxRotation: 0
                    }
                }
            }
        }
    });
    @endif

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
                borderWidth: 3,
                tension: 0.4,
                fill: true,
                pointRadius: 5,
                pointBackgroundColor: '#6366F1',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointHoverRadius: 7,
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
                    cornerRadius: 10,
                    padding: 12,
                    callbacks: {
                        label: function(context) {
                            return context.parsed.y + ' poin';
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        font: { size: 10, family: 'Inter' },
                        stepSize: 1
                    },
                    grid: {
                        color: 'rgba(0,0,0,0.04)',
                        drawBorder: false
                    }
                },
                x: {
                    grid: { display: false },
                    ticks: {
                        font: { size: 9, family: 'Inter' },
                        maxRotation: 0
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
