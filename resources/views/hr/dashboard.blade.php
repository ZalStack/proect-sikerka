{{-- views/hr/dashboard.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="flex min-h">
    @include('layouts.sidebar')
    <div class="flex-1 transition-all duration-300 md:ml-64">

        {{-- ==================== HEADER ==================== --}}
        <div class="bg-white/90 backdrop-blur-md shadow-sm border-b border-slate-200/50 sticky top-0 z-20">
            <div class="px-4 sm:px-6 lg:px-8 py-4">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div>
                        <h1 class="text-2xl sm:text-3xl font-bold font-['Montserrat'] text-[#161758]">
                            Dashboard HR
                        </h1>
                        <p class="text-sm text-slate-500 mt-0.5">
                            Selamat datang, <span class="font-medium text-slate-700">{{ auth()->user()->nama_lengkap ?? 'HR' }}</span>
                        </p>
                    </div>
                    <div class="flex items-center gap-3 flex-wrap">
                        <div class="flex items-center gap-2 bg-white rounded-full px-4 py-2 shadow-sm border border-slate-200">
                            <i class="fas fa-calendar-day text-emerald-500 text-sm"></i>
                            <span class="text-xs font-medium text-slate-700">{{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}</span>
                        </div>
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

        {{-- ==================== CONTENT (auto-refreshed) ==================== --}}
        <div id="dashboard-content" class="p-4 sm:p-6 lg:p-8 space-y-6">

            <script type="application/json" id="chart-data">
                {!! json_encode([
                    'absensi' => $absensiChart ?? ['labels' => [], 'data' => []],
                    'status' => $statusKaryawanChart ?? ['labels' => [], 'data' => [], 'colors' => []],
                    'cuti' => $cutiChart ?? ['labels' => [], 'approved' => [], 'pending' => [], 'rejected' => []],
                    'pd' => $perjalananDinasChart ?? ['labels' => [], 'pending' => [], 'approved' => []],
                ]) !!}
            </script>

            <script type="application/json" id="calendar-data">
                {!! json_encode($calendarEvents ?? []) !!}
            </script>

            {{-- ---------- KPI UTAMA ---------- --}}
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                @php
                    $kpis = [
                        ['label' => 'Total Karyawan', 'value' => $totalKaryawan ?? 0, 'sub' => ($totalHr ?? 0).' HR', 'icon' => 'fa-users', 'color' => 'from-[#161758] to-[#2b2d8f]', 'bg' => 'bg-indigo-50', 'text' => 'text-[#161758]'],
                        ['label' => 'Karyawan Aktif', 'value' => $totalKaryawanAktif ?? 0, 'sub' => 'Sedang bekerja', 'icon' => 'fa-user-check', 'color' => 'from-emerald-500 to-emerald-600', 'bg' => 'bg-emerald-50', 'text' => 'text-emerald-600'],
                        ['label' => 'Resign', 'value' => $totalKaryawanResigned ?? 0, 'sub' => 'Sudah keluar', 'icon' => 'fa-user-minus', 'color' => 'from-red-500 to-red-600', 'bg' => 'bg-red-50', 'text' => 'text-red-600'],
                        ['label' => 'Absensi Hari Ini', 'value' => $absensiHariIni ?? 0, 'sub' => ($absensiTerlambat ?? 0).' terlambat', 'icon' => 'fa-fingerprint', 'color' => 'from-blue-500 to-blue-600', 'bg' => 'bg-blue-50', 'text' => 'text-blue-600'],
                        ['label' => 'Absensi Bulan Ini', 'value' => $absensiBulanIni ?? 0, 'sub' => 'Total check-in', 'icon' => 'fa-calendar-check', 'color' => 'from-sky-500 to-sky-600', 'bg' => 'bg-sky-50', 'text' => 'text-sky-600'],
                        ['label' => 'Cuti Bulan Ini', 'value' => $totalCutiBulanIni ?? 0, 'sub' => ($cutiPending ?? 0).' menunggu', 'icon' => 'fa-umbrella-beach', 'color' => 'from-amber-500 to-amber-600', 'bg' => 'bg-amber-50', 'text' => 'text-amber-600'],
                    ];
                @endphp
                @foreach($kpis as $kpi)
                <div class="bg-white rounded-2xl shadow-sm p-4 border border-slate-100 hover:shadow-lg transition-all duration-300 group relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-20 h-20 bg-gradient-to-br {{ $kpi['color'] }} opacity-5 rounded-full -translate-y-8 translate-x-8 group-hover:scale-150 transition-transform duration-500"></div>
                    <div class="flex items-start justify-between relative z-10">
                        <div>
                            <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">{{ $kpi['label'] }}</p>
                            <p class="text-xl font-bold text-slate-800 mt-1">{{ $kpi['value'] }}</p>
                            <p class="text-[10px] text-slate-400 mt-1">{{ $kpi['sub'] }}</p>
                        </div>
                        <div class="w-10 h-10 rounded-xl {{ $kpi['bg'] }} flex items-center justify-center group-hover:scale-110 transition-transform shadow-sm shrink-0">
                            <i class="fas {{ $kpi['icon'] }} {{ $kpi['text'] }} text-lg"></i>
                        </div>
                    </div>
                    <div class="absolute bottom-0 left-0 h-1 bg-gradient-to-r {{ $kpi['color'] }} rounded-b-2xl transition-all duration-300 group-hover:w-full" style="width: 30%;"></div>
                </div>
                @endforeach
            </div>

            {{-- ---------- ULANG TAHUN HARI INI ---------- --}}
            @if($isBirthdayToday)
            <div class="birthday-section" x-data="{ show: true }" x-show="show" x-transition:enter="transition ease-out duration-500" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
                <div class="relative overflow-hidden rounded-2xl shadow-lg bg-white border border-amber-200">
                    <div class="h-2 bg-gradient-to-r from-amber-400 via-rose-400 to-pink-500 animate-gradient-x"></div>
                    <div class="absolute top-4 left-6 text-3xl animate-float-slow opacity-25" style="animation-delay: 0s;">🎂</div>
                    <div class="absolute top-4 right-6 text-2xl animate-float opacity-25" style="animation-delay: 0.5s;">🎁</div>
                    <div class="absolute bottom-3 left-10 text-xl animate-float opacity-25" style="animation-delay: 1s;">🎈</div>
                    <div class="absolute bottom-3 right-10 text-2xl animate-float-slow opacity-25" style="animation-delay: 1.5s;">🎉</div>
                    <div class="absolute top-1/2 left-1/4 text-lg animate-pulse-slow opacity-10">✨</div>
                    <div class="absolute top-1/2 right-1/4 text-lg animate-pulse-slow opacity-10" style="animation-delay: 1s;">✨</div>

                    <div class="relative z-10 px-5 py-6 sm:px-8 sm:py-8 flex flex-col items-center text-center">
                        <button @click="show = false" class="absolute top-3 right-3 w-7 h-7 rounded-full bg-slate-100 hover:bg-slate-200 flex items-center justify-center transition-all duration-200 hover:scale-110">
                            <i class="fas fa-times text-slate-400 text-xs"></i>
                        </button>

                        <div class="mb-4">
                            <span class="text-5xl sm:text-6xl block mb-2 animate-bounce-slow">🎂</span>
                            <h3 class="text-lg sm:text-xl font-extrabold text-slate-800">Selamat Ulang Tahun!</h3>
                            <p class="text-xs text-slate-400 mt-1">{{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}</p>
                        </div>

                        <div class="w-full max-w-2xl">
                            <div class="inline-block bg-gradient-to-r from-amber-50 via-rose-50 to-pink-50 rounded-xl px-5 py-3.5 border border-amber-100 shadow-sm">
                                <p class="text-sm sm:text-base text-slate-700 leading-relaxed font-medium">
                                    <span class="text-amber-600 font-bold">Barakallahu fii umrik, {{ auth()->user()->nama_lengkap }}</span>,<br class="hidden sm:block">
                                    <span class="text-slate-600">Semoga Allah SWT limpahkan umur yang berkah, rezeki yang lapang, dan langkah yang selalu dalam ridha-Nya.</span><br class="hidden sm:block">
                                    <span class="text-slate-500 text-sm">Terima kasih sudah menjadi bagian dari keluarga besar KPM.</span>
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center justify-center gap-1.5 mt-3">
                            <span class="text-sm">🎊</span>
                            <span class="text-sm font-bold text-rose-500">Happy Birthday!</span>
                            <span class="text-sm">🎊</span>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            {{-- ---------- PERLU DITINDAKLANJUTI (approval queue) ---------- --}}
            <div>
                <h2 class="text-sm font-semibold text-slate-600 mb-3 flex items-center gap-2">
                    <i class="fas fa-bell text-amber-500"></i> Perlu Ditindaklanjuti
                </h2>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <a href="{{ route('hr.cuti.index') }}" class="bg-white rounded-2xl shadow-sm p-5 border border-slate-100 hover:shadow-lg hover:border-amber-200 transition-all duration-300 flex items-center justify-between">
                        <div>
                            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Cuti Menunggu</p>
                            <p class="text-2xl font-bold text-amber-600 mt-1">{{ $cutiPending ?? 0 }}</p>
                            <p class="text-[11px] text-slate-400 mt-1">Perlu persetujuan</p>
                        </div>
                        <div class="w-12 h-12 rounded-xl bg-amber-50 flex items-center justify-center">
                            <i class="fas fa-file-signature text-amber-500 text-xl"></i>
                        </div>
                    </a>
                    <a href="{{ route('hr.perjalanan-dinas.index') }}" class="bg-white rounded-2xl shadow-sm p-5 border border-slate-100 hover:shadow-lg hover:border-violet-200 transition-all duration-300 flex items-center justify-between">
                        <div>
                            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Perjalanan Dinas</p>
                            <p class="text-2xl font-bold text-violet-600 mt-1">{{ $perjalananDinasPending ?? 0 }}</p>
                            <p class="text-[11px] text-slate-400 mt-1">Menunggu persetujuan</p>
                        </div>
                        <div class="w-12 h-12 rounded-xl bg-violet-50 flex items-center justify-center">
                            <i class="fas fa-suitcase-rolling text-violet-500 text-xl"></i>
                        </div>
                    </a>
                    <a href="{{ route('hr.sunnah.index') }}" class="bg-white rounded-2xl shadow-sm p-5 border border-slate-100 hover:shadow-lg hover:border-indigo-200 transition-all duration-300 flex items-center justify-between">
                        <div>
                            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">7SPS Menunggu</p>
                            <p class="text-2xl font-bold text-indigo-600 mt-1">{{ $sunnahPending ?? 0 }}</p>
                            <p class="text-[11px] text-slate-400 mt-1">Perlu review</p>
                        </div>
                        <div class="w-12 h-12 rounded-xl bg-indigo-50 flex items-center justify-center">
                            <i class="fas fa-star text-indigo-500 text-xl"></i>
                        </div>
                    </a>
                </div>
            </div>

            {{-- ---------- PROGRAM SPIRITUAL RINGKAS ---------- --}}
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5">
                <h2 class="text-sm font-semibold text-slate-600 mb-4 flex items-center gap-2">
                    <i class="fas fa-mosque text-teal-500"></i> Ringkasan Program Mingguan / Bulanan
                </h2>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                    <div class="rounded-xl bg-teal-50 border border-teal-100 p-3 text-center">
                        <p class="text-[10px] text-teal-600 font-semibold uppercase tracking-wide">FHL Hari Ini</p>
                        <p class="text-xl font-bold text-teal-700 mt-1">{{ $fhlHariIni ?? 0 }}</p>
                        <p class="text-[10px] text-teal-500 mt-0.5">{{ $fhlBulanIni ?? 0 }} bulan ini</p>
                    </div>
                    <div class="rounded-xl bg-purple-50 border border-purple-100 p-3 text-center">
                        <p class="text-[10px] text-purple-600 font-semibold uppercase tracking-wide">Khataman Hari Ini</p>
                        <p class="text-xl font-bold text-purple-700 mt-1">{{ $khatamanHariIni ?? 0 }}</p>
                        <p class="text-[10px] text-purple-500 mt-0.5">{{ $khatamanBulanIni ?? 0 }} bulan ini</p>
                    </div>
                    <div class="rounded-xl bg-rose-50 border border-rose-100 p-3 text-center">
                        <p class="text-[10px] text-rose-600 font-semibold uppercase tracking-wide">Poin 7SPS Bulan Ini</p>
                        <p class="text-xl font-bold text-rose-700 mt-1">{{ number_format($sunnahApprovedBulanIni ?? 0) }}</p>
                        <p class="text-[10px] text-rose-500 mt-0.5">{{ $sunnahBulanIni ?? 0 }} entri disetujui</p>
                    </div>
                    <div class="rounded-xl bg-blue-50 border border-blue-100 p-3 text-center">
                        <p class="text-[10px] text-blue-600 font-semibold uppercase tracking-wide">Dinas Bulan Ini</p>
                        <p class="text-xl font-bold text-blue-700 mt-1">{{ $perjalananDinasBulanIni ?? 0 }}</p>
                        <p class="text-[10px] text-blue-500 mt-0.5">{{ $perjalananDinasApproved ?? 0 }} disetujui</p>
                    </div>
                </div>
            </div>

            {{-- ---------- KALENDER NOTIFIKASI (FullCalendar) ---------- --}}
            <div x-data="calendarModal()" x-init="init()" class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden hover:shadow-lg transition-shadow duration-300">
                {{-- Header --}}
                <div class="p-5 border-b border-slate-100 bg-gradient-to-r from-[#161758] to-[#27438D]">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-white/20 backdrop-blur-sm flex items-center justify-center">
                                <i class="fas fa-calendar-alt text-white text-lg"></i>
                            </div>
                            <div>
                                <h3 class="text-sm font-semibold text-white">Kalender Aktivitas</h3>
                                <p class="text-xs text-white/70">Klik tanggal untuk melihat detail</p>
                            </div>
                        </div>
                        <a href="{{ route('notifications.index') }}" class="text-xs text-white/80 hover:text-white font-medium flex items-center gap-1 transition-colors">
                            Semua Notifikasi <i class="fas fa-arrow-right text-[10px]"></i>
                        </a>
                    </div>
                </div>

                <div class="p-4 sm:p-5">
                    <div id="hr-fullcalendar" class="fc fc-media-block fc-direction-ltr fc-theme-standard"></div>

                    {{-- Legend --}}
                    <div class="mt-4 pt-3 border-t border-slate-100 flex flex-wrap gap-3 justify-center">
                        <div class="flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-[#3B82F6]"></span>
                            <span class="text-[10px] text-slate-400">Pengumuman</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-[#F59E0B]"></span>
                            <span class="text-[10px] text-slate-400">Cuti</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-[#8B5CF6]"></span>
                            <span class="text-[10px] text-slate-400">Dinas</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-[#14B8A6]"></span>
                            <span class="text-[10px] text-slate-400">7SPS</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-[#F43F5E]"></span>
                            <span class="text-[10px] text-slate-400">Alert</span>
                        </div>
                    </div>
                </div>

                {{-- ========== MODAL DETAIL TANGGAL ========== --}}
                <template x-if="showModal">
                    <div class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4" @keydown.escape.window="showModal = false">
                        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="showModal = false"></div>
                        <div class="relative bg-white w-full sm:max-w-md sm:rounded-2xl rounded-t-2xl shadow-2xl max-h-[85vh] flex flex-col overflow-hidden transform transition-all"
                             x-show="showModal"
                             x-transition:enter="ease-out duration-300"
                             x-transition:enter-start="opacity-0 translate-y-8 sm:translate-y-4 sm:scale-95"
                             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                             x-transition:leave="ease-in duration-200"
                             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                             x-transition:leave-end="opacity-0 translate-y-8 sm:translate-y-4 sm:scale-95">
                            <div class="p-4 sm:p-5 bg-gradient-to-r from-[#161758] to-[#27438D] text-white shrink-0">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-xs font-medium opacity-70">Detail Aktivitas</p>
                                        <h3 class="text-base sm:text-lg font-bold mt-0.5" x-text="modalDateLabel"></h3>
                                    </div>
                                    <button @click="showModal = false" class="w-8 h-8 rounded-full bg-white/20 hover:bg-white/30 flex items-center justify-center transition-colors">
                                        <i class="fas fa-times text-sm"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="flex-1 overflow-y-auto p-4 sm:p-5 space-y-2.5 custom-scrollbar">
                                <template x-if="modalEvents.length === 0">
                                    <div class="text-center py-10">
                                        <div class="w-16 h-16 rounded-full bg-slate-100 flex items-center justify-center mx-auto mb-3">
                                            <i class="fas fa-calendar-day text-2xl text-slate-300"></i>
                                        </div>
                                        <p class="text-sm font-medium text-slate-500">Tidak ada aktivitas</p>
                                        <p class="text-xs text-slate-400 mt-1">Tanggal ini belum memiliki notifikasi</p>
                                    </div>
                                </template>
                                <template x-for="event in modalEvents" :key="event.id">
                                    <a :href="event.url"
                                       class="flex items-start gap-3 p-3 rounded-xl border border-slate-100 hover:border-slate-200 hover:bg-slate-50 transition-all duration-200 group">
                                        <div class="w-9 h-9 rounded-lg flex items-center justify-center text-sm shrink-0"
                                             :class="getEventColorClass(event.color)">
                                            <i class="fas" :class="event.icon"></i>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <p class="text-sm font-semibold text-slate-800 leading-snug" x-text="event.title"></p>
                                            <p class="text-xs text-slate-500 mt-0.5 line-clamp-2" x-text="event.message"></p>
                                            <p class="text-[10px] text-slate-400 mt-1.5 flex items-center gap-1.5">
                                                <i class="far fa-clock"></i>
                                                <span x-text="event.time"></span>
                                                <span class="text-slate-300">&middot;</span>
                                                <span x-text="event.time_ago"></span>
                                            </p>
                                        </div>
                                        <i class="fas fa-chevron-right text-[10px] text-slate-300 group-hover:text-[#27438D] transition-colors shrink-0 mt-2"></i>
                                    </a>
                                </template>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            {{-- ---------- PENGUMUMAN / TOP SUNNAH / KARYAWAN BARU ---------- --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden hover:shadow-lg transition-shadow duration-300">
                    <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-semibold text-slate-700 flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-rose-500"></span> Pengumuman Terbaru
                            </h3>
                        </div>
                        <a href="{{ route('hr.pengumuman.index') }}" class="text-xs text-blue-500 hover:text-blue-600 font-medium flex items-center gap-1">
                            Lihat Semua <i class="fas fa-arrow-right text-[10px]"></i>
                        </a>
                    </div>
                    <div class="p-4 space-y-2 max-h-[280px] overflow-y-auto custom-scrollbar">
                        @forelse($pengumumanTerbaru ?? [] as $p)
                        <div class="p-3 rounded-xl hover:bg-slate-50 transition-colors">
                            <p class="text-sm font-medium text-slate-800 truncate">{{ $p->judul }}</p>
                            <p class="text-xs text-slate-400 truncate mt-0.5">{{ \Illuminate\Support\Str::limit(strip_tags($p->isi), 60) }}</p>
                            <p class="text-[10px] text-slate-400 mt-1">
                                <i class="far fa-clock mr-1"></i>{{ $p->created_at->diffForHumans() }}
                                <span class="text-slate-300 mx-1">&bull;</span>{{ $p->target_label ?? $p->target }}
                            </p>
                        </div>
                        @empty
                        <div class="text-center py-8">
                            <i class="fas fa-bullhorn text-3xl text-slate-200 mb-2 block"></i>
                            <p class="text-sm text-slate-400">Belum ada pengumuman</p>
                        </div>
                        @endforelse
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden hover:shadow-lg transition-shadow duration-300">
                    <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-slate-700 flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-indigo-500"></span> Top 5 Sunnah (Bulan Ini)
                        </h3>
                        <a href="{{ route('hr.sunnah.rekap') }}" class="text-xs text-blue-500 hover:text-blue-600 font-medium flex items-center gap-1">
                            Rekap <i class="fas fa-arrow-right text-[10px]"></i>
                        </a>
                    </div>
                    <div class="p-4 space-y-2 max-h-[280px] overflow-y-auto custom-scrollbar">
                        @php $medals = ['🥇','🥈','🥉']; @endphp
                        @forelse($topSunnah ?? [] as $i => $t)
                        <div class="flex items-center justify-between p-3 rounded-xl hover:bg-slate-50 transition-colors">
                            <div class="flex items-center gap-3 min-w-0">
                                <span class="text-lg w-6 text-center shrink-0">{{ $medals[$i] ?? ($i + 1) }}</span>
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-slate-800 truncate">{{ $t->karyawan->nama_lengkap ?? '-' }}</p>
                                    <p class="text-[10px] text-slate-400">{{ $t->total_days }} hari tracking</p>
                                </div>
                            </div>
                            <span class="text-sm font-bold text-indigo-600 shrink-0">{{ number_format($t->total_poin) }} pts</span>
                        </div>
                        @empty
                        <div class="text-center py-8">
                            <i class="fas fa-star text-3xl text-slate-200 mb-2 block"></i>
                            <p class="text-sm text-slate-400">Belum ada data</p>
                        </div>
                        @endforelse
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden hover:shadow-lg transition-shadow duration-300">
                    <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-slate-700 flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Karyawan Baru
                        </h3>
                        <a href="{{ route('hr.karyawan.index') }}" class="text-xs text-blue-500 hover:text-blue-600 font-medium flex items-center gap-1">
                            Lihat Semua <i class="fas fa-arrow-right text-[10px]"></i>
                        </a>
                    </div>
                    <div class="p-4 space-y-2 max-h-[280px] overflow-y-auto custom-scrollbar">
                        @forelse($karyawanTerbaru ?? [] as $k)
                        <div class="flex items-center justify-between p-3 rounded-xl hover:bg-slate-50 transition-colors">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-9 h-9 rounded-full bg-gradient-to-br from-emerald-400 to-teal-500 flex items-center justify-center text-white text-xs font-bold shrink-0">
                                    {{ strtoupper(substr($k->nama_lengkap ?? '?', 0, 1)) }}
                                </div>
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-slate-800 truncate">{{ $k->nama_lengkap }}</p>
                                    <p class="text-[10px] text-slate-400 truncate">{{ $k->jabatan ?? '-' }}</p>
                                </div>
                            </div>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-medium shrink-0 {{ $k->status_badge ?? 'bg-slate-100 text-slate-600' }}">
                                {{ $k->status_label ?? $k->status }}
                            </span>
                        </div>
                        @empty
                        <div class="text-center py-8">
                            <i class="fas fa-user-plus text-3xl text-slate-200 mb-2 block"></i>
                            <p class="text-sm text-slate-400">Belum ada karyawan baru</p>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- ---------- AKTIVITAS TERBARU ---------- --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden hover:shadow-lg transition-shadow duration-300">
                    <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-slate-700 flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-blue-500"></span> Absensi Terbaru
                        </h3>
                        <a href="{{ route('hr.absensi.index') }}" class="text-xs text-blue-500 hover:text-blue-600 font-medium flex items-center gap-1">
                            Lihat Semua <i class="fas fa-arrow-right text-[10px]"></i>
                        </a>
                    </div>
                    <div class="p-4 space-y-2 max-h-[280px] overflow-y-auto custom-scrollbar">
                        @forelse($absensiTerbaru ?? [] as $a)
                        <div class="flex items-center justify-between p-3 rounded-xl hover:bg-slate-50 transition-colors">
                            <div class="min-w-0">
                                <p class="text-sm font-medium text-slate-800 truncate">{{ $a->karyawan->nama_lengkap ?? '-' }}</p>
                                <p class="text-[10px] text-slate-400">{{ $a->tanggal ? $a->tanggal->format('d M Y') : '-' }} &bull; {{ $a->check_in ? $a->check_in->format('H:i') : '-' }}</p>
                            </div>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-medium shrink-0 ml-2
                                {{ $a->status === 'Hadir' ? 'bg-emerald-100 text-emerald-700' :
                                ($a->status === 'Terlambat' ? 'bg-amber-100 text-amber-700' : 'bg-red-100 text-red-700') }}">
                                {{ $a->status }}
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

                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden hover:shadow-lg transition-shadow duration-300">
                    <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-slate-700 flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-amber-500"></span> Cuti Terbaru
                        </h3>
                        <a href="{{ route('hr.cuti.index') }}" class="text-xs text-blue-500 hover:text-blue-600 font-medium flex items-center gap-1">
                            Lihat Semua <i class="fas fa-arrow-right text-[10px]"></i>
                        </a>
                    </div>
                    <div class="p-4 space-y-2 max-h-[280px] overflow-y-auto custom-scrollbar">
                        @forelse($cutiTerbaru ?? [] as $c)
                        <div class="p-3 rounded-xl hover:bg-slate-50 transition-colors">
                            <div class="flex items-center justify-between mb-0.5">
                                <span class="text-sm font-medium text-slate-800 truncate">{{ $c->karyawan->nama_lengkap ?? '-' }}</span>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-medium shrink-0 ml-2 {{ $c->status_badge }}">{{ $c->status_label }}</span>
                            </div>
                            <p class="text-[10px] text-slate-400">{{ $c->jenis_cuti ?? '-' }} &bull; {{ $c->durasi }} hari</p>
                        </div>
                        @empty
                        <div class="text-center py-8">
                            <i class="fas fa-file-alt text-3xl text-slate-200 mb-2 block"></i>
                            <p class="text-sm text-slate-400">Belum ada pengajuan cuti</p>
                        </div>
                        @endforelse
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden hover:shadow-lg transition-shadow duration-300">
                    <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-slate-700 flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-violet-500"></span> Perjalanan Dinas
                        </h3>
                        <a href="{{ route('hr.perjalanan-dinas.index') }}" class="text-xs text-blue-500 hover:text-blue-600 font-medium flex items-center gap-1">
                            Lihat Semua <i class="fas fa-arrow-right text-[10px]"></i>
                        </a>
                    </div>
                    <div class="p-4 space-y-2 max-h-[280px] overflow-y-auto custom-scrollbar">
                        @php
                            $pdStatusBadge = fn($s) => $s === 'pending' ? 'bg-amber-100 text-amber-700' : ($s === 'approved' ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700');
                            $pdStatusLabel = fn($s) => ['pending' => 'Menunggu', 'approved' => 'Disetujui', 'rejected' => 'Ditolak'][$s] ?? $s;
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
                        @forelse($perjalananDinasTerbaru ?? [] as $pd)
                        <div class="p-3 rounded-xl border border-slate-100 hover:border-violet-200 hover:bg-violet-50/50 transition-all">
                            <div class="flex items-start justify-between gap-2">
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-slate-800 truncate">{{ $pd->karyawan->nama_lengkap ?? '-' }}</p>
                                    <p class="text-xs text-slate-400 truncate">{{ $pd->judul ?? '-' }}</p>
                                </div>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-medium shrink-0 {{ $pdStatusBadge($pd->status) }}">
                                    {{ $pdStatusLabel($pd->status) }}
                                </span>
                            </div>
                            <p class="mt-1.5 text-[10px] text-slate-400 flex items-center gap-1">
                                <i class="far fa-calendar-alt"></i>
                                {{ $formatPeriode($pd->tanggal_mulai, $pd->tanggal_selesai) }}
                                <span class="text-slate-300">&bull;</span>
                                {{ $pd->tanggal_mulai->diffInDays($pd->tanggal_selesai) + 1 }} hari
                            </p>
                        </div>
                        @empty
                        <div class="text-center py-8">
                            <i class="fas fa-suitcase-rolling text-3xl text-slate-200 mb-2 block"></i>
                            <p class="text-sm text-slate-400">Belum ada perjalanan dinas</p>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>

        </div>{{-- /#dashboard-content --}}
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.17/index.global.min.js"></script>
<script>
(function () {
    let absensiChartInstance, statusChartInstance, cutiChartInstance, pdChartInstance;

    function readChartData() {
        const el = document.getElementById('chart-data');
        const fallback = { absensi: { labels: [], data: [] }, status: { labels: [], data: [], colors: [] }, cuti: { labels: [], approved: [], pending: [], rejected: [] }, pd: { labels: [], pending: [], approved: [] } };
        if (!el) return fallback;
        try { return JSON.parse(el.textContent); } catch (e) { return fallback; }
    }

    function initCharts() {
        const chartData = readChartData();
        const absensiData = chartData.absensi;
        const statusData = chartData.status;
        const cutiData = chartData.cuti;
        const pdData = chartData.pd;

        const commonTooltip = {
            backgroundColor: 'rgba(255,255,255,0.95)', titleColor: '#1F2937', bodyColor: '#6B7280',
            borderColor: '#E5E7EB', borderWidth: 1, cornerRadius: 10, padding: 12
        };

        if (absensiChartInstance) absensiChartInstance.destroy();
        const absensiCtx = document.getElementById('absensiChart');
        if (absensiCtx) {
            absensiChartInstance = new Chart(absensiCtx, {
                type: 'bar',
                data: { labels: absensiData.labels, datasets: [{ label: 'Absensi', data: absensiData.data, backgroundColor: '#3B82F6', borderRadius: 6, borderSkipped: false, barPercentage: 0.6 }] },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false }, tooltip: commonTooltip },
                    scales: { y: { beginAtZero: true, ticks: { precision: 0, font: { size: 10 } }, grid: { color: 'rgba(0,0,0,0.04)' } }, x: { grid: { display: false }, ticks: { font: { size: 9 } } } } }
            });
        }

        if (statusChartInstance) statusChartInstance.destroy();
        const statusCtx = document.getElementById('statusKaryawanChart');
        if (statusCtx) {
            statusChartInstance = new Chart(statusCtx, {
                type: 'doughnut',
                data: { labels: statusData.labels, datasets: [{ data: statusData.data, backgroundColor: statusData.colors, borderWidth: 2, borderColor: '#fff' }] },
                options: { responsive: true, maintainAspectRatio: false, cutout: '65%', plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 10 } } }, tooltip: commonTooltip } }
            });
        }

        if (cutiChartInstance) cutiChartInstance.destroy();
        const cutiCtx = document.getElementById('cutiChart');
        if (cutiCtx) {
            cutiChartInstance = new Chart(cutiCtx, {
                type: 'bar',
                data: { labels: cutiData.labels, datasets: [
                    { label: 'Disetujui', data: cutiData.approved, backgroundColor: '#10B981', borderRadius: 4 },
                    { label: 'Menunggu', data: cutiData.pending, backgroundColor: '#F59E0B', borderRadius: 4 },
                    { label: 'Ditolak', data: cutiData.rejected, backgroundColor: '#EF4444', borderRadius: 4 }
                ] },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 10 } } }, tooltip: commonTooltip },
                    scales: { y: { beginAtZero: true, ticks: { precision: 0, font: { size: 10 } }, grid: { color: 'rgba(0,0,0,0.04)' } }, x: { grid: { display: false }, ticks: { font: { size: 9 } }, stacked: false } } }
            });
        }

        if (pdChartInstance) pdChartInstance.destroy();
        const pdCtx = document.getElementById('perjalananDinasChart');
        if (pdCtx) {
            pdChartInstance = new Chart(pdCtx, {
                type: 'bar',
                data: { labels: pdData.labels, datasets: [
                    { label: 'Menunggu', data: pdData.pending, backgroundColor: '#F59E0B', borderRadius: 4 },
                    { label: 'Disetujui', data: pdData.approved, backgroundColor: '#8B5CF6', borderRadius: 4 }
                ] },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 10 } } }, tooltip: commonTooltip },
                    scales: { y: { beginAtZero: true, ticks: { precision: 0, font: { size: 10 } }, grid: { color: 'rgba(0,0,0,0.04)' } }, x: { grid: { display: false }, ticks: { font: { size: 9 } } } } }
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
                if (window.Alpine) Alpine.initTree(currentContent);
                initCharts();
                initFullCalendar();
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
        .line-clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
    `;
    document.head.appendChild(style);
});

// ========== FullCalendar Modal State ==========
function calendarModal() {
    return {
        showModal: false,
        modalEvents: [],
        modalDateLabel: '',
        init() {
            document.addEventListener('calendar-date-click', (e) => {
                this.modalEvents = e.detail.events;
                this.modalDateLabel = e.detail.dateLabel;
                this.showModal = true;
            });
        },
        getEventColorClass(color) {
            const map = {
                'blue': 'bg-blue-100 text-blue-600',
                'amber': 'bg-amber-100 text-amber-600',
                'violet': 'bg-violet-100 text-violet-600',
                'emerald': 'bg-emerald-100 text-emerald-600',
                'rose': 'bg-rose-100 text-rose-600',
                'sky': 'bg-sky-100 text-sky-600',
                'teal': 'bg-teal-100 text-teal-600',
                'indigo': 'bg-indigo-100 text-indigo-600',
                'cyan': 'bg-cyan-100 text-cyan-600',
            };
            return map[color] || 'bg-slate-100 text-slate-600';
        }
    };
}

// ========== FullCalendar Init ==========
function initFullCalendar() {
    const el = document.getElementById('hr-fullcalendar');
    if (!el || typeof FullCalendar === 'undefined') return;

    const dataEl = document.getElementById('calendar-data');
    let allEvents = {};
    if (dataEl) {
        try { allEvents = JSON.parse(dataEl.textContent); } catch (e) { allEvents = {}; }
    }

    const fcEvents = [];
    const colorMap = {
        'blue': '#3B82F6', 'amber': '#F59E0B', 'violet': '#8B5CF6',
        'emerald': '#10B981', 'rose': '#F43F5E', 'sky': '#0EA5E9',
        'teal': '#14B8A6', 'indigo': '#6366F1', 'cyan': '#06B6D4',
    };

    for (const [date, dayEvents] of Object.entries(allEvents)) {
        for (const ev of dayEvents) {
            fcEvents.push({
                title: ev.title,
                start: date,
                color: colorMap[ev.color] || '#94A3B8',
                extendedProps: { ...ev }
            });
        }
    }

    el.innerHTML = '';
    const isMobile = window.innerWidth < 640;

    const calendar = new FullCalendar.Calendar(el, {
        initialView: isMobile ? 'listWeek' : 'dayGridMonth',
        locale: 'id',
        firstDay: 1,
        height: isMobile ? 'auto' : 580,
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: isMobile ? 'listWeek' : 'dayGridMonth,listWeek'
        },
        buttonText: { today: 'Hari Ini', month: 'Bulan', list: 'Daftar' },
        events: fcEvents,
        dateClick: function(info) {
            const date = info.dateStr;
            const dayEvts = allEvents[date] || [];
            const opts = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
            const label = new Date(date + 'T00:00:00').toLocaleDateString('id-ID', opts);
            document.dispatchEvent(new CustomEvent('calendar-date-click', { detail: { events: dayEvts, dateLabel: label } }));
        },
        eventClick: function(info) {
            info.jsEvent.preventDefault();
            const date = info.event.startStr;
            const dayEvts = allEvents[date] || [];
            const opts = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
            const label = new Date(date + 'T00:00:00').toLocaleDateString('id-ID', opts);
            document.dispatchEvent(new CustomEvent('calendar-date-click', { detail: { events: dayEvts, dateLabel: label } }));
        },
        windowResize: function() {
            const mob = window.innerWidth < 640;
            calendar.changeView(mob ? 'listWeek' : 'dayGridMonth');
            calendar.setOption('height', mob ? 'auto' : 580);
            calendar.setOption('headerToolbar', {
                left: 'prev,next today',
                center: 'title',
                right: mob ? 'listWeek' : 'dayGridMonth,listWeek'
            });
        }
    });

    calendar.render();
}

document.addEventListener('DOMContentLoaded', function() {
    initFullCalendar();

    const fcStyle = document.createElement('style');
    fcStyle.textContent = `
        .fc .fc-toolbar-title { font-size: 1rem !important; font-weight: 700; color: #1e293b; }
        .fc .fc-button { border-radius: 10px !important; font-size: 12px !important; padding: 6px 14px !important; font-weight: 600 !important; background: #f1f5f9 !important; border-color: #e2e8f0 !important; color: #475569 !important; text-transform: capitalize !important; }
        .fc .fc-button:hover { background: #e2e8f0 !important; }
        .fc .fc-button-active { background: #161758 !important; border-color: #161758 !important; color: #fff !important; }
        .fc .fc-daygrid-day { border-radius: 10px !important; padding: 2px !important; }
        .fc .fc-daygrid-day.fc-day-today { background: #f0fdf4 !important; }
        .fc .fc-daygrid-day-number { font-size: 12px !important; padding: 4px 6px !important; border-radius: 8px !important; width: 100% !important; text-align: center !important; }
        .fc .fc-daygrid-day:hover .fc-daygrid-day-number { background: #f1f5f9 !important; }
        .fc .fc-event { border-radius: 6px !important; padding: 1px 6px !important; font-size: 11px !important; border: none !important; cursor: pointer !important; }
        .fc .fc-col-header-cell { font-size: 11px !important; font-weight: 600 !important; color: #94a3b8 !important; padding: 8px 0 !important; }
        .fc .fc-scrollgrid { border: none !important; border-radius: 12px !important; overflow: hidden; }
        .fc .fc-scrollgrid td, .fc .fc-scrollgrid th { border-color: #f1f5f9 !important; }
        .fc .fc-list-event:hover td { background: #f8fafc !important; }
        .fc .fc-list-event-title a { font-size: 13px !important; font-weight: 600 !important; color: #1e293b !important; }
        .fc .fc-list-event-time { font-size: 12px !important; color: #94a3b8 !important; }
        .fc .fc-list-day-cushion { background: #f8fafc !important; }
        .fc .fc-list-day-text { font-size: 13px !important; font-weight: 600 !important; }
        @media (max-width: 639px) {
            .fc .fc-toolbar { flex-direction: column !important; gap: 8px !important; }
            .fc .fc-toolbar fc-toolbar-ltr { flex-wrap: wrap !important; }
            .fc .fc-button { padding: 5px 10px !important; font-size: 11px !important; }
        }
    `;
    document.head.appendChild(fcStyle);
});
</script>
@endpush
@endsection
