{{-- views/karyawan/dashboard.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="flex min-h-screen bg-gradient-to-br from-slate-50 to-slate-100">
    @include('layouts.sidebar')
    <div class="flex-1 transition-all duration-300 md:ml-64">

        {{-- ==================== HEADER ==================== --}}
        <div class="bg-white/90 backdrop-blur-md shadow-sm border-b border-slate-200/50 sticky top-0 z-20">
            <div class="px-4 sm:px-6 lg:px-8 py-4">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div>
                        <h1 class="text-2xl sm:text-3xl font-bold font-['Montserrat'] text-[#161758]">
                            Dashboard Karyawan
                        </h1>
                        <p class="text-sm text-slate-500 mt-0.5 flex items-center gap-2">
                            Selamat datang, <span class="font-medium text-slate-700">{{ $user->nama_lengkap }}</span>
                        </p>
                    </div>
                    <div class="flex items-center gap-3 flex-wrap">
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
                        <button id="manualRefreshBtn" type="button"
                            class="flex items-center gap-2 bg-[#161758] hover:bg-[#22246e] transition-colors text-white rounded-full px-4 py-2 shadow-sm text-xs font-medium">
                            <i id="refreshIcon" class="fas fa-sync-alt text-[11px]"></i>
                            <span>Refresh</span>
                        </button>
                    </div>
                </div>
                <div class="mt-2 flex items-center gap-1.5 text-[11px] text-slate-400">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                    </span>
                    Data live &middot; diperbarui otomatis setiap 60 detik &middot; terakhir disegarkan <span id="lastSyncLabel" class="font-medium text-slate-500">baru saja</span>
                </div>
            </div>
        </div>

        <div id="dashboard-content" class="p-4 sm:p-6 lg:p-8 space-y-6">

            <script type="application/json" id="chart-data">
                {!! json_encode([
                    'absensi' => $absensiChart ?? ['labels' => [], 'data' => []],
                    'sunnah' => $sunnahChart ?? ['labels' => [], 'data' => []],
                ]) !!}
            </script>

            {{-- ---------- Stats Grid ---------- --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                @php
                    $absensiValue = '';
                    if ($absensiHariIni) {
                        $absensiValue = '<i class="fas fa-check-circle text-emerald-500 text-xl"></i> ';
                        $absensiValue .= $absensiHariIni->check_in ? $absensiHariIni->check_in->format('H:i') : '-';
                        if ($absensiHariIni->check_out) {
                            $absensiValue .= ' &rarr; ' . $absensiHariIni->check_out->format('H:i');
                        }
                    } else {
                        $absensiValue = '<i class="fas fa-times-circle text-red-400 text-xl"></i>';
                    }

                    $stats = [
                        [
                            'label' => 'Absensi Hari Ini', 'value' => $absensiValue,
                            'sub' => $absensiHariIni ? 'Sudah absen' : 'Belum absen hari ini',
                            'icon' => 'fa-fingerprint',
                            'color' => $absensiHariIni ? 'from-emerald-500 to-emerald-600' : 'from-red-500 to-red-600',
                            'bg' => $absensiHariIni ? 'bg-emerald-50' : 'bg-red-50',
                            'text' => $absensiHariIni ? 'text-emerald-600' : 'text-red-600',
                            'is_html' => true
                        ],
                        [
                            'label' => 'Jam Kerja', 'value' => number_format($totalJamKerja ?? 0, 1) . 'h',
                            'sub' => ($absensiBulanIni ?? 0) . ' hari hadir bulan ini',
                            'icon' => 'fa-clock', 'color' => 'from-blue-500 to-blue-600', 'bg' => 'bg-blue-50', 'text' => 'text-blue-600'
                        ],
                        [
                            'label' => 'Sisa Cuti', 'value' => $sisaCuti ?? 12,
                            'sub' => 'Disetujui: ' . ($cutiApproved ?? 0) . ' &bull; Menunggu: ' . ($cutiPending ?? 0),
                            'icon' => 'fa-umbrella-beach', 'color' => 'from-amber-500 to-amber-600', 'bg' => 'bg-amber-50', 'text' => 'text-amber-600'
                        ],
                        [
                            'label' => 'Poin 7SPS', 'value' => number_format($sunnahBulanIni ?? 0),
                            'sub' => ($sunnahTotalDays ?? 0) . ' hari tracking' . ($sunnahHariIni ? ' &bull; Hari ini: ' . ($sunnahHariIni->total_poin ?? 0) . ' pts' : ''),
                            'icon' => 'fa-star', 'color' => 'from-indigo-500 to-indigo-600', 'bg' => 'bg-indigo-50', 'text' => 'text-indigo-600'
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
                            <p class="text-[10px] text-slate-400 mt-1">{!! $stat['sub'] !!}</p>
                        </div>
                        <div class="w-10 h-10 rounded-xl {{ $stat['bg'] }} flex items-center justify-center group-hover:scale-110 transition-transform shadow-sm shrink-0">
                            <i class="fas {{ $stat['icon'] }} {{ $stat['text'] }} text-lg"></i>
                        </div>
                    </div>
                    <div class="absolute bottom-0 left-0 h-1 bg-gradient-to-r {{ $stat['color'] }} rounded-b-2xl transition-all duration-300 group-hover:w-full" style="width: 30%;"></div>
                </div>
                @endforeach
            </div>

            {{-- ---------- FHL / Khataman / Dinas mini stats ---------- --}}
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5">
                <h2 class="text-sm font-semibold text-slate-600 mb-4 flex items-center gap-2">
                    <i class="fas fa-mosque text-teal-500"></i> Program Lainnya
                </h2>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                    <a href="{{ route('karyawan.fhl.dashboard') }}" class="rounded-xl bg-teal-50 border border-teal-100 p-3 text-center hover:bg-teal-100 transition-colors">
                        <p class="text-[10px] text-teal-600 font-semibold uppercase tracking-wide">Jumat Berkah</p>
                        <p class="text-lg font-bold text-teal-700 mt-1">{{ $fhlHariIni ? 'Sudah Absen' : 'Belum' }}</p>
                        <p class="text-[10px] text-teal-500 mt-0.5">{{ $fhlBulanIni ?? 0 }}x bulan ini</p>
                    </a>
                    <a href="{{ route('karyawan.khataman.dashboard') }}" class="rounded-xl bg-purple-50 border border-purple-100 p-3 text-center hover:bg-purple-100 transition-colors">
                        <p class="text-[10px] text-purple-600 font-semibold uppercase tracking-wide">Khataman</p>
                        <p class="text-lg font-bold text-purple-700 mt-1">{{ $khatamanHariIni ? 'Sudah Absen' : 'Belum' }}</p>
                        <p class="text-[10px] text-purple-500 mt-0.5">{{ $khatamanBulanIni ?? 0 }}x bulan ini</p>
                    </a>
                    <a href="{{ route('karyawan.perjalanan-dinas.index') }}" class="rounded-xl bg-blue-50 border border-blue-100 p-3 text-center hover:bg-blue-100 transition-colors">
                        <p class="text-[10px] text-blue-600 font-semibold uppercase tracking-wide">Perjalanan Dinas</p>
                        <p class="text-lg font-bold text-blue-700 mt-1">{{ $perjalananDinasTotal ?? 0 }}</p>
                        <p class="text-[10px] text-blue-500 mt-0.5">{{ $perjalananDinasPending ?? 0 }} menunggu</p>
                    </a>
                    <a href="{{ route('karyawan.cuti.dashboard') }}" class="rounded-xl bg-amber-50 border border-amber-100 p-3 text-center hover:bg-amber-100 transition-colors">
                        <p class="text-[10px] text-amber-600 font-semibold uppercase tracking-wide">Total Cuti</p>
                        <p class="text-lg font-bold text-amber-700 mt-1">{{ $totalCuti ?? 0 }}</p>
                        <p class="text-[10px] text-amber-500 mt-0.5">{{ $cutiRejected ?? 0 }} ditolak</p>
                    </a>
                </div>
            </div>

            {{-- ---------- Pengumuman ---------- --}}
            @if(($pengumumanTerbaru ?? collect())->count() > 0)
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                <div class="p-5 border-b border-slate-100">
                    <h3 class="text-sm font-semibold text-slate-700 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-rose-500"></span> Pengumuman Terbaru
                    </h3>
                </div>
                <div class="p-4 grid grid-cols-1 md:grid-cols-3 gap-3">
                    @foreach($pengumumanTerbaru as $p)
                    <div class="p-3 rounded-xl border border-slate-100 hover:bg-slate-50 transition-colors">
                        <p class="text-sm font-medium text-slate-800 truncate">{{ $p->judul }}</p>
                        <p class="text-xs text-slate-400 mt-0.5 line-clamp-2">{{ \Illuminate\Support\Str::limit(strip_tags($p->isi), 70) }}</p>
                        <p class="text-[10px] text-slate-400 mt-1"><i class="far fa-clock mr-1"></i>{{ $p->created_at->diffForHumans() }}</p>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- ---------- Charts ---------- --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5">
                    <h3 class="text-sm font-semibold text-slate-700 mb-4 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-blue-500"></span> Kehadiran 7 Hari Terakhir
                    </h3>
                    <div class="h-56"><canvas id="absensiChart"></canvas></div>
                </div>
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5">
                    <h3 class="text-sm font-semibold text-slate-700 mb-4 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-indigo-500"></span> Poin 7SPS 7 Hari Terakhir
                    </h3>
                    <div class="h-56"><canvas id="sunnahChart"></canvas></div>
                </div>
            </div>

            {{-- ---------- Detail Cards ---------- --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- Personal Info --}}
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
                            <span class="text-sm text-slate-500 flex items-center gap-2"><i class="fas fa-id-card text-slate-300 text-xs w-4"></i> ID Karyawan</span>
                            <span class="text-sm font-medium text-slate-700">{{ $user->kode_pegawai ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between items-center py-1.5 border-b border-slate-50">
                            <span class="text-sm text-slate-500 flex items-center gap-2"><i class="fas fa-building text-slate-300 text-xs w-4"></i> Divisi</span>
                            <span class="text-sm font-medium text-slate-700">{{ $user->divisi ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between items-center py-1.5 border-b border-slate-50">
                            <span class="text-sm text-slate-500 flex items-center gap-2"><i class="fas fa-calendar-alt text-slate-300 text-xs w-4"></i> Bergabung</span>
                            <span class="text-sm font-medium text-slate-700">{{ $user->tanggal_bergabung ? $user->tanggal_bergabung->format('d-m-Y') : '-' }}</span>
                        </div>
                        <div class="flex justify-between items-center py-1.5">
                            <span class="text-sm text-slate-500 flex items-center gap-2"><i class="fas fa-envelope text-slate-300 text-xs w-4"></i> Email</span>
                            <span class="text-sm font-medium text-slate-700 truncate max-w-[180px]">{{ $user->email ?? '-' }}</span>
                        </div>
                    </div>
                </div>

                {{-- Recent Absensi --}}
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden hover:shadow-lg transition-shadow duration-300">
                    <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-semibold text-slate-700 flex items-center gap-2"><span class="w-2 h-2 rounded-full bg-blue-500"></span> Absensi Terbaru</h3>
                            <p class="text-xs text-slate-400 mt-0.5">Riwayat check-in terakhir</p>
                        </div>
                        <a href="{{ route('karyawan.absensi') }}" class="text-xs text-blue-500 hover:text-blue-600 font-medium flex items-center gap-1">Lihat Semua <i class="fas fa-arrow-right text-[10px]"></i></a>
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

                {{-- Recent Cuti --}}
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden hover:shadow-lg transition-shadow duration-300">
                    <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-semibold text-slate-700 flex items-center gap-2"><span class="w-2 h-2 rounded-full bg-rose-500"></span> Pengajuan Cuti</h3>
                            <p class="text-xs text-slate-400 mt-0.5">Status pengajuan terbaru</p>
                        </div>
                        <a href="{{ route('karyawan.cuti.dashboard') }}" class="text-xs text-blue-500 hover:text-blue-600 font-medium flex items-center gap-1">Lihat Semua <i class="fas fa-arrow-right text-[10px]"></i></a>
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
                                    <span class="text-slate-300">&bull;</span> {{ $cuti->durasi ?? 0 }} hari
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

            {{-- ---------- Sunnah Detail Card ---------- --}}
            @if($sunnahHariIni)
            <div class="bg-gradient-to-r from-indigo-600 via-purple-600 to-rose-600 rounded-2xl shadow-xl p-6 text-white relative overflow-hidden">
                <div class="absolute top-0 right-0 w-64 h-64 bg-white/5 rounded-full -translate-y-32 translate-x-32"></div>
                <div class="absolute bottom-0 left-0 w-48 h-48 bg-white/5 rounded-full translate-y-24 -translate-x-24"></div>

                <div class="relative z-10">
                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-4">
                        <div>
                            <h3 class="text-lg font-bold flex items-center gap-2"><span class="text-2xl">🌟</span> 7 Sunnah Rasul - Hari Ini</h3>
                            <p class="text-sm opacity-80 mt-0.5">{{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}</p>
                        </div>
                        <div class="flex items-center gap-3 mt-3 sm:mt-0 flex-wrap">
                            <span class="bg-white/20 backdrop-blur-sm px-4 py-2 rounded-xl text-sm font-bold">
                                {{ $sunnahHariIni->total_poin ?? 0 }} Poin
                            </span>
                            <span class="px-3 py-2 rounded-xl text-xs font-medium
                                {{ ($sunnahHariIni->status_approval ?? '') === 'approved' ? 'bg-emerald-400/30' :
                                (($sunnahHariIni->status_approval ?? '') === 'pending' ? 'bg-amber-400/30' : 'bg-red-400/30') }}">
                                {{ $sunnahHariIni->status_label ?? '-' }}
                            </span>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3">
                        @php
                            $poinConfig = \App\Models\SunnahDaily::getPoinConfig();
                            $wajibKeys = \App\Models\SunnahDaily::getSholatWajibKeys();
                            $wajibLabels = ['sholat_subuh' => '🌅 Subuh', 'sholat_zuhur' => '☀️ Zuhur', 'sholat_asar' => '🌤️ Asar', 'sholat_maghrib' => '🌆 Maghrib', 'sholat_isya' => '🌙 Isya'];
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
                                    {{ $sunnahHariIni->$key && $sunnahHariIni->{$key.'_berjamaah'} ? '✅' : ($sunnahHariIni->$key ? '⚠️' : '❌') }}
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
                        <h3 class="text-lg font-bold flex items-center gap-2"><span class="text-2xl">🌟</span> 7 Sunnah Rasul</h3>
                        <p class="text-sm opacity-80 mt-0.5">Belum mengisi 7SPS hari ini</p>
                    </div>
                    <a href="{{ route('karyawan.sunnah.dashboard') }}" class="bg-white/20 backdrop-blur-sm hover:bg-white/30 transition-colors px-5 py-2.5 rounded-xl text-sm font-medium flex items-center gap-2">
                        Isi Sekarang <i class="fas fa-arrow-right text-xs"></i>
                    </a>
                </div>
            </div>
            @endif

            {{-- ---------- Perjalanan Dinas Terbaru (format periode) ---------- --}}
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden hover:shadow-lg transition-shadow duration-300">
                <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-semibold text-slate-700 flex items-center gap-2"><span class="w-2 h-2 rounded-full bg-violet-500"></span> Perjalanan Dinas</h3>
                        <p class="text-xs text-slate-400 mt-0.5">Pengajuan perjalanan dinas Anda</p>
                    </div>
                    <a href="{{ route('karyawan.perjalanan-dinas.index') }}" class="text-xs text-blue-500 hover:text-blue-600 font-medium flex items-center gap-1">Lihat Semua <i class="fas fa-arrow-right text-[10px]"></i></a>
                </div>
                <div class="p-4">
                    @php
                        $perjalananDinas = $perjalananDinasTerbaru ?? collect();
                        $formatPeriode = function ($start, $end) {
                            if (!$start || !$end) return '-';
                            if ($start->isSameDay($end)) return $start->translatedFormat('d M Y');
                            if ($start->year === $end->year && $start->month === $end->month) {
                                return $start->format('d') . ' - ' . $end->translatedFormat('d M Y');
                            }
                            if ($start->year === $end->year) {
                                return $start->translatedFormat('d M') . ' - ' . $end->translatedFormat('d M Y');
                            }
                            return $start->translatedFormat('d M Y') . ' - ' . $end->translatedFormat('d M Y');
                        };
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
                            <p class="mt-1.5 flex items-center gap-1 text-[10px] text-slate-400">
                                <i class="far fa-calendar-alt"></i>
                                {{ $formatPeriode($pd->tanggal_mulai, $pd->tanggal_selesai) }}
                                <span class="text-slate-300">&bull;</span>
                                {{ $pd->tanggal_mulai && $pd->tanggal_selesai ? $pd->tanggal_mulai->diffInDays($pd->tanggal_selesai) + 1 : 0 }} hari
                            </p>
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

        </div>{{-- /#dashboard-content --}}
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
(function () {
    let absensiChartInstance, sunnahChartInstance;

    function readChartData() {
        const el = document.getElementById('chart-data');
        const fallback = { absensi: { labels: [], data: [] }, sunnah: { labels: [], data: [] } };
        if (!el) return fallback;
        try { return JSON.parse(el.textContent); } catch (e) { return fallback; }
    }

    function initCharts() {
        const chartData = readChartData();
        const absensiData = chartData.absensi.data || [];
        const commonTooltip = { backgroundColor: 'rgba(255,255,255,0.95)', titleColor: '#1F2937', bodyColor: '#6B7280', borderColor: '#E5E7EB', borderWidth: 1, cornerRadius: 10, padding: 12 };

        if (absensiChartInstance) absensiChartInstance.destroy();
        const absensiCtx = document.getElementById('absensiChart');
        if (absensiCtx) {
            absensiChartInstance = new Chart(absensiCtx, {
                type: 'bar',
                data: { labels: chartData.absensi.labels, datasets: [{ label: 'Kehadiran', data: absensiData, backgroundColor: absensiData.map(v => v > 0 ? '#10B981' : '#EF4444'), borderRadius: 6, borderSkipped: false, barPercentage: 0.7 }] },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { display: false }, tooltip: { ...commonTooltip, callbacks: { label: c => c.parsed.y > 0 ? '✅ Hadir' : '❌ Tidak Hadir' } } },
                    scales: {
                        y: { beginAtZero: true, max: 1, ticks: { stepSize: 1, font: { size: 10 }, callback: v => v === 1 ? 'Hadir' : '' }, grid: { color: 'rgba(0,0,0,0.04)' } },
                        x: { grid: { display: false }, ticks: { font: { size: 9 }, maxRotation: 0 } }
                    }
                }
            });
        }

        if (sunnahChartInstance) sunnahChartInstance.destroy();
        const sunnahCtx = document.getElementById('sunnahChart');
        if (sunnahCtx) {
            sunnahChartInstance = new Chart(sunnahCtx, {
                type: 'line',
                data: { labels: chartData.sunnah.labels, datasets: [{ label: 'Poin 7SPS', data: chartData.sunnah.data, borderColor: '#6366F1', backgroundColor: 'rgba(99, 102, 241, 0.08)', borderWidth: 3, tension: 0.4, fill: true, pointRadius: 5, pointBackgroundColor: '#6366F1', pointBorderColor: '#fff', pointBorderWidth: 2, pointHoverRadius: 7, pointHoverBackgroundColor: '#4F46E5' }] },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { display: false }, tooltip: { ...commonTooltip, callbacks: { label: c => c.parsed.y + ' poin' } } },
                    scales: {
                        y: { beginAtZero: true, ticks: { font: { size: 10 }, stepSize: 1 }, grid: { color: 'rgba(0,0,0,0.04)' } },
                        x: { grid: { display: false }, ticks: { font: { size: 9 }, maxRotation: 0 } }
                    }
                }
            });
        }
    }

    document.addEventListener('DOMContentLoaded', initCharts);

    // ==================== LIVE REFRESH ====================
    let lastSync = Date.now();
    const REFRESH_INTERVAL = 60000; // 60s

    function updateLastSyncLabel() {
        const el = document.getElementById('lastSyncLabel');
        if (!el) return;
        const seconds = Math.floor((Date.now() - lastSync) / 1000);
        el.textContent = seconds < 5 ? 'baru saja' : seconds + ' detik lalu';
    }
    setInterval(updateLastSyncLabel, 1000);

    async function refreshDashboard() {
        const icon = document.getElementById('refreshIcon');
        icon && icon.classList.add('fa-spin');
        try {
            const res = await fetch(window.location.href, { headers: { 'X-Requested-With': 'XMLHttpRequest' }, cache: 'no-store' });
            const html = await res.text();
            const doc = new DOMParser().parseFromString(html, 'text/html');
            const newContent = doc.getElementById('dashboard-content');
            const currentContent = document.getElementById('dashboard-content');
            if (newContent && currentContent) {
                currentContent.innerHTML = newContent.innerHTML;
                initCharts();
            }
        } catch (e) {
            console.warn('Gagal menyegarkan dashboard:', e);
        } finally {
            lastSync = Date.now();
            updateLastSyncLabel();
            icon && icon.classList.remove('fa-spin');
        }
    }

    document.getElementById('manualRefreshBtn')?.addEventListener('click', refreshDashboard);

    setInterval(function () {
        if (document.visibilityState === 'visible') {
            refreshDashboard();
        }
    }, REFRESH_INTERVAL);
})();

// Custom scrollbar
document.addEventListener('DOMContentLoaded', function() {
    const style = document.createElement('style');
    style.textContent = `
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #D1D5DB; border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #9CA3AF; }
        .custom-scrollbar { scrollbar-width: thin; scrollbar-color: #D1D5DB transparent; }
    `;
    document.head.appendChild(style);
});
</script>
@endpush
@endsection
