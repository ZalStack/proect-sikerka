@extends('layouts.app')

@section('content')
<div class="flex">
    @include('layouts.sidebar')

    <div class="flex-1 min-w-0 md:ml-64 p-4 sm:p-6 lg:p-8 space-y-6">

        <!-- Header -->
        <div class="flex flex-col xl:flex-row xl:items-end justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">
                    <a href="{{ route('superadmin.dashboard') }}" class="hover:text-[#00a2e9] transition-colors">Super Admin</a>
                    <span>/</span>
                    <span class="text-gray-700">Analitik HR</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-[#0F1245] font-['Poppins'] tracking-tight">
                    Analitik Kehadiran &amp; Kepegawaian
                </h1>
                <p class="text-xs sm:text-sm text-gray-500 font-light mt-0.5 max-w-2xl">
                    Tren kehadiran, distribusi status absensi, pengajuan, dan kualitas ketepatan waktu seluruh karyawan.
                </p>
            </div>

            <div class="inline-flex items-center gap-1 bg-white border border-gray-200 rounded-xl p-1 shadow-sm self-start xl:self-auto">
                @foreach([7 => '7 Hari', 30 => '30 Hari', 90 => '90 Hari'] as $value => $label)
                    <a href="{{ route('superadmin.analytics.index', ['period' => $value]) }}"
                        class="px-3.5 py-2 rounded-lg text-xs font-bold transition-all {{ $period === $value ? 'bg-[#0F1245] text-white shadow-md' : 'text-gray-500 hover:bg-gray-100 hover:text-gray-800' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>
        </div>

        @include('superadmin.partials.flash')

        <!-- KPI -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
            <div class="bg-white rounded-2xl p-4 border border-slate-100 shadow-sm">
                <span class="text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-slate-400">Tingkat Kehadiran</span>
                <p class="text-xl sm:text-2xl font-extrabold text-[#2E7D3E] mt-2">{{ $kpi['attendance_rate'] }}%</p>
                <p class="text-[10px] text-slate-400 mt-0.5">{{ number_format($kpi['hadir']) }} dari {{ number_format($kpi['total_records']) }} catatan</p>
            </div>

            <div class="bg-white rounded-2xl p-4 border border-slate-100 shadow-sm">
                <span class="text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-slate-400">Karyawan Hadir</span>
                <p class="text-xl sm:text-2xl font-extrabold text-[#00a2e9] mt-2">{{ number_format($kpi['unique_attendees']) }}</p>
                <p class="text-[10px] text-slate-400 mt-0.5">dari {{ number_format($kpi['active_employees']) }} karyawan aktif</p>
            </div>

            <div class="bg-white rounded-2xl p-4 border border-slate-100 shadow-sm">
                <span class="text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-slate-400">Pengajuan Menunggu</span>
                <p class="text-xl sm:text-2xl font-extrabold text-[#d49b06] mt-2">{{ number_format($kpi['pending_requests']) }}</p>
                <p class="text-[10px] text-slate-400 mt-0.5">Cuti {{ $kpi['pending_cuti'] }} &middot; Izin {{ $kpi['pending_izin'] }} &middot; Dinas {{ $kpi['pending_dinas'] }}</p>
            </div>

            <div class="bg-white rounded-2xl p-4 border border-slate-100 shadow-sm">
                <span class="text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-slate-400">Catatan Alpha</span>
                <p class="text-xl sm:text-2xl font-extrabold text-[#dc2626] mt-2">{{ number_format($kpi['alpha']) }}</p>
                <p class="text-[10px] text-slate-400 mt-0.5">{{ number_format($pendingViolations) }} pelanggaran menunggu review</p>
            </div>
        </div>

        <!-- GRAFIK UTAMA -->
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-4 sm:gap-5">
            <!-- Tren kehadiran -->
            <div class="xl:col-span-2 bg-white rounded-3xl p-5 sm:p-6 shadow-sm border border-gray-100">
                <div class="flex items-center justify-between gap-3 mb-4">
                    <div>
                        <h2 class="text-base font-bold text-[#0F1245] flex items-center gap-2">
                            <i class="fa-solid fa-chart-line text-[#00a2e9]"></i>
                            Tren Kehadiran Harian
                        </h2>
                        <p class="text-xs text-gray-500 mt-0.5">{{ $period }} hari terakhir</p>
                    </div>
                </div>
                <div class="h-72"><canvas id="trendChart"></canvas></div>
            </div>

            <!-- Distribusi status -->
            <div class="bg-white rounded-3xl p-5 sm:p-6 shadow-sm border border-gray-100">
                <h2 class="text-base font-bold text-[#0F1245] flex items-center gap-2 mb-1">
                    <i class="fa-solid fa-chart-pie text-[#d49b06]"></i>
                    Distribusi Status
                </h2>
                <p class="text-xs text-gray-500 mb-3">Seluruh catatan absensi pada periode</p>

                @if(count($statusDistribution['labels']) === 0)
                    <p class="text-xs text-gray-400 text-center py-10">Belum ada data absensi pada periode ini.</p>
                @else
                    <div class="h-56"><canvas id="statusChart"></canvas></div>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-2 gap-4 sm:gap-5">
            <!-- Kehadiran per divisi -->
            <div class="bg-white rounded-3xl p-5 sm:p-6 shadow-sm border border-gray-100">
                <h2 class="text-base font-bold text-[#0F1245] flex items-center gap-2 mb-1">
                    <i class="fa-solid fa-building-user text-[#27438D]"></i>
                    Persentase Kehadiran per Divisi
                </h2>
                <p class="text-xs text-gray-500 mb-3">Rasio status Hadir terhadap seluruh catatan divisi</p>

                @if(count($perDivisi['labels']) === 0)
                    <p class="text-xs text-gray-400 text-center py-10">Belum ada data pada periode ini.</p>
                @else
                    <div class="h-72"><canvas id="divisiChart"></canvas></div>
                @endif
            </div>

            <!-- Pengajuan per bulan -->
            <div class="bg-white rounded-3xl p-5 sm:p-6 shadow-sm border border-gray-100">
                <h2 class="text-base font-bold text-[#0F1245] flex items-center gap-2 mb-1">
                    <i class="fa-solid fa-file-signature text-[#9333ea]"></i>
                    Pengajuan 6 Bulan Terakhir
                </h2>
                <p class="text-xs text-gray-500 mb-3">Cuti, izin/sakit, dan perjalanan dinas</p>
                <div class="h-72"><canvas id="requestChart"></canvas></div>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-4 sm:gap-5">
            <!-- Kualitas kehadiran -->
            <div class="bg-white rounded-3xl p-5 sm:p-6 shadow-sm border border-gray-100">
                <h2 class="text-base font-bold text-[#0F1245] flex items-center gap-2 mb-4">
                    <i class="fa-solid fa-stopwatch text-[#ec1d1d]"></i>
                    Kualitas Ketepatan Waktu
                </h2>

                <div class="space-y-3">
                    <div class="p-3 rounded-xl bg-gray-50 border border-gray-100">
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-gray-500">Tingkat keterlambatan</span>
                            <span class="text-sm font-extrabold text-[#d49b06]">{{ $quality['late_rate'] }}%</span>
                        </div>
                        <div class="w-full h-2 bg-gray-200 rounded-full mt-2 overflow-hidden">
                            <div class="h-full bg-[#d49b06] rounded-full" style="width: {{ min(100, $quality['late_rate']) }}%"></div>
                        </div>
                        <p class="text-[10px] text-gray-400 mt-1">{{ number_format($quality['late']) }} karyawan hadir setelah 07:45</p>
                    </div>

                    <div class="p-3 rounded-xl bg-gray-50 border border-gray-100">
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-gray-500">Tingkat kehadiran</span>
                            <span class="text-sm font-extrabold text-[#2E7D3E]">{{ $quality['attendance_rate'] }}%</span>
                        </div>
                        <div class="w-full h-2 bg-gray-200 rounded-full mt-2 overflow-hidden">
                            <div class="h-full bg-[#2E7D3E] rounded-full" style="width: {{ min(100, $quality['attendance_rate']) }}%"></div>
                        </div>
                        <p class="text-[10px] text-gray-400 mt-1">{{ number_format($quality['hadir']) }} catatan hadir dari {{ number_format($quality['total_records']) }}</p>
                    </div>

                    <div class="p-3 rounded-xl bg-rose-50 border border-rose-100">
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-rose-700">Total alpha</span>
                            <span class="text-sm font-extrabold text-rose-600">{{ number_format($quality['alpha']) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sunnah per divisi -->
            <div class="bg-white rounded-3xl p-5 sm:p-6 shadow-sm border border-gray-100">
                <h2 class="text-base font-bold text-[#0F1245] flex items-center gap-2 mb-1">
                    <i class="fa-solid fa-kaaba text-[#059669]"></i>
                    Catatan 7SPS per Divisi
                </h2>
                <p class="text-xs text-gray-500 mb-3">Jumlah pencatatan amalan pada periode</p>

                @if(count($sunnahPerDivisi['labels']) === 0)
                    <p class="text-xs text-gray-400 text-center py-10">Belum ada catatan 7SPS pada periode ini.</p>
                @else
                    <div class="h-64"><canvas id="sunnahChart"></canvas></div>
                @endif
            </div>

            <!-- Login terakhir -->
            <div class="bg-white rounded-3xl p-5 sm:p-6 shadow-sm border border-gray-100">
                <h2 class="text-base font-bold text-[#0F1245] flex items-center gap-2 mb-1">
                    <i class="fa-solid fa-right-to-bracket text-[#0F1245]"></i>
                    Aktivitas Login Terakhir
                </h2>
                <p class="text-xs text-gray-500 mb-3">Termasuk percobaan yang gagal</p>

                @if(count($recentLogins) === 0)
                    <p class="text-xs text-gray-400 text-center py-10">Belum ada riwayat login tercatat.</p>
                @else
                    <ul class="divide-y divide-gray-50">
                        @foreach($recentLogins as $login)
                            <li class="py-2.5 flex items-start gap-2.5">
                                <span class="w-7 h-7 rounded-lg flex items-center justify-center text-[10px] shrink-0 {{ $login['success'] ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600' }}">
                                    <i class="fa-solid {{ $login['success'] ? 'fa-check' : 'fa-xmark' }}"></i>
                                </span>
                                <div class="min-w-0 flex-1">
                                    <p class="text-xs font-bold text-gray-800 truncate">{{ $login['name'] }}</p>
                                    <p class="text-[10px] text-gray-400 truncate" title="{{ $login['email'] }}">{{ $login['email'] }} &middot; {{ $login['ip'] }}</p>
                                    @if(!$login['success'] && $login['reason'])
                                        <p class="text-[10px] text-rose-500 truncate">{{ $login['reason'] }}</p>
                                    @endif
                                </div>
                                <span class="text-[10px] text-gray-400 shrink-0">{{ $login['at']?->diffForHumans() }}</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script type="application/json" id="analytics-data">{!! json_encode([
    'trend' => $trend,
    'status' => $statusDistribution,
    'divisi' => $perDivisi,
    'requests' => $requestsPerMonth,
    'sunnah' => $sunnahPerDivisi,
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const el = document.getElementById('analytics-data');
        if (!el || typeof Chart === 'undefined') return;

        const data = JSON.parse(el.textContent);
        const fontFamily = "'Poppins', sans-serif";
        Chart.defaults.font.family = fontFamily;
        Chart.defaults.color = '#64748b';

        const gridColor = 'rgba(148, 163, 184, 0.14)';
        const tooltipStyle = {
            backgroundColor: '#0F1245',
            borderColor: 'rgba(252, 198, 38, 0.35)',
            borderWidth: 1,
            titleColor: '#FCC626',
            bodyColor: '#FFFFFF',
        };
        const axisTicks = { font: { size: 9 }, maxRotation: 0, autoSkip: true, maxTicksLimit: 14 };

        // 1. Tren kehadiran
        const trendCanvas = document.getElementById('trendChart');
        if (trendCanvas) {
            new Chart(trendCanvas, {
                type: 'line',
                data: {
                    labels: data.trend.labels,
                    datasets: [
                        { label: 'Hadir', data: data.trend.hadir, borderColor: '#2E7D3E', backgroundColor: 'rgba(46,125,62,0.12)', fill: true, tension: 0.35, pointRadius: 0, borderWidth: 2 },
                        { label: 'Izin/Sakit', data: data.trend.izinSakit, borderColor: '#9333ea', backgroundColor: 'transparent', tension: 0.35, pointRadius: 0, borderWidth: 2 },
                        { label: 'Cuti/Dinas', data: data.trend.cutiDinas, borderColor: '#00a2e9', backgroundColor: 'transparent', tension: 0.35, pointRadius: 0, borderWidth: 2 },
                        { label: 'Alpha', data: data.trend.alpha, borderColor: '#dc2626', backgroundColor: 'rgba(220,38,38,0.10)', fill: true, tension: 0.35, pointRadius: 0, borderWidth: 2 },
                    ],
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    interaction: { mode: 'index', intersect: false },
                    plugins: { legend: { labels: { boxWidth: 10, boxHeight: 10, usePointStyle: true, font: { size: 10 } } }, tooltip: { ...tooltipStyle, padding: 10, cornerRadius: 10 } },
                    scales: {
                        x: { grid: { display: false }, ticks: axisTicks },
                        y: { beginAtZero: true, ticks: { precision: 0, font: { size: 10 } }, grid: { color: gridColor, drawBorder: false } },
                    },
                },
            });
        }

        // 2. Distribusi status (doughnut)
        const statusCanvas = document.getElementById('statusChart');
        if (statusCanvas) {
            new Chart(statusCanvas, {
                type: 'doughnut',
                data: {
                    labels: data.status.labels,
                    datasets: [{ data: data.status.data, backgroundColor: data.status.colors, borderWidth: 2, borderColor: '#ffffff' }],
                },
                options: {
                    responsive: true, maintainAspectRatio: false, cutout: '62%',
                    plugins: {
                        legend: { position: 'bottom', labels: { boxWidth: 10, boxHeight: 10, usePointStyle: true, font: { size: 10 }, padding: 12 } },
                        tooltip: { ...tooltipStyle, padding: 10, cornerRadius: 10 },
                    },
                },
            });
        }

        // 3. Kehadiran per divisi (bar horizontal)
        const divisiCanvas = document.getElementById('divisiChart');
        if (divisiCanvas) {
            new Chart(divisiCanvas, {
                type: 'bar',
                data: {
                    labels: data.divisi.labels,
                    datasets: [{
                        label: '% Hadir',
                        data: data.divisi.percent,
                        backgroundColor: data.divisi.percent.map(v => v >= 80 ? 'rgba(46,125,62,0.75)' : (v >= 60 ? 'rgba(212,155,6,0.75)' : 'rgba(220,38,38,0.75)')),
                        borderRadius: 5, borderSkipped: false,
                    }],
                },
                options: {
                    indexAxis: 'y',
                    responsive: true, maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: { ...tooltipStyle, padding: 10, cornerRadius: 10, callbacks: { label: (c) => c.parsed.x + '% hadir' } },
                    },
                    scales: {
                        x: { beginAtZero: true, max: 100, ticks: { callback: v => v + '%', font: { size: 10 } }, grid: { color: gridColor, drawBorder: false } },
                        y: { grid: { display: false }, ticks: { font: { size: 10 } } },
                    },
                },
            });
        }

        // 4. Pengajuan per bulan (grouped bar)
        const requestCanvas = document.getElementById('requestChart');
        if (requestCanvas) {
            new Chart(requestCanvas, {
                type: 'bar',
                data: {
                    labels: data.requests.labels,
                    datasets: [
                        { label: 'Cuti', data: data.requests.cuti, backgroundColor: 'rgba(0,162,233,0.75)', borderRadius: 4 },
                        { label: 'Izin/Sakit', data: data.requests.izin, backgroundColor: 'rgba(147,51,234,0.75)', borderRadius: 4 },
                        { label: 'Dinas', data: data.requests.dinas, backgroundColor: 'rgba(212,155,6,0.75)', borderRadius: 4 },
                    ],
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { labels: { boxWidth: 10, boxHeight: 10, usePointStyle: true, font: { size: 10 } } }, tooltip: { ...tooltipStyle, padding: 10, cornerRadius: 10 } },
                    scales: {
                        x: { stacked: false, grid: { display: false }, ticks: { font: { size: 9 } } },
                        y: { beginAtZero: true, ticks: { precision: 0, font: { size: 10 } }, grid: { color: gridColor, drawBorder: false } },
                    },
                },
            });
        }

        // 5. Sunnah per divisi (bar)
        const sunnahCanvas = document.getElementById('sunnahChart');
        if (sunnahCanvas) {
            new Chart(sunnahCanvas, {
                type: 'bar',
                data: {
                    labels: data.sunnah.labels,
                    datasets: [{ label: 'Catatan', data: data.sunnah.data, backgroundColor: 'rgba(5,150,105,0.75)', borderRadius: 5, borderSkipped: false }],
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { display: false }, tooltip: { ...tooltipStyle, padding: 10, cornerRadius: 10 } },
                    scales: {
                        x: { grid: { display: false }, ticks: { font: { size: 9 }, maxRotation: 0, autoSkip: true } },
                        y: { beginAtZero: true, ticks: { precision: 0, font: { size: 10 } }, grid: { color: gridColor, drawBorder: false } },
                    },
                },
            });
        }
    });
</script>
@endpush
