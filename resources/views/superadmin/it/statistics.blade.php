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
                    <span class="text-gray-700">Pemeliharaan IT</span>
                    <span>/</span>
                    <span class="text-gray-700">Statistik Penggunaan</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-[#0F1245] font-['Poppins'] tracking-tight">
                    Statistik Penggunaan Sistem
                </h1>
                <p class="text-xs sm:text-sm text-gray-500 font-light mt-0.5 max-w-2xl">
                    Pantau aktivitas pengguna, tren login, penggunaan modul, dan kondisi penyimpanan aplikasi dalam satu tampilan.
                </p>
            </div>

            <div class="flex flex-col sm:flex-row sm:items-center gap-2">
                <div class="inline-flex items-center gap-1 bg-white border border-gray-200 rounded-xl p-1 shadow-sm">
                    @foreach([7 => '7 Hari', 30 => '30 Hari', 90 => '90 Hari'] as $value => $label)
                        <a href="{{ route('superadmin.stats.index', ['period' => $value]) }}"
                            class="px-3.5 py-2 rounded-lg text-xs font-bold transition-all {{ $period === $value ? 'bg-[#0F1245] text-white shadow-md' : 'text-gray-500 hover:bg-gray-100 hover:text-gray-800' }}">
                            {{ $label }}
                        </a>
                    @endforeach
                </div>
                <a href="{{ route('superadmin.logs.index') }}" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-[#00a2e9] hover:bg-[#0088cc] text-white text-xs font-bold transition-all shadow-md shadow-[#00a2e9]/25">
                    <i class="fa-solid fa-clipboard-list"></i>
                    <span>Lihat Log</span>
                </a>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2 text-[11px] text-gray-400">
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-white border border-gray-100 shadow-sm">
                <i class="fa-solid fa-calendar-days text-[#00a2e9]"></i>
                {{ $startDate->translatedFormat('d M Y') }} &ndash; {{ $endDate->translatedFormat('d M Y') }}
            </span>
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-white border border-gray-100 shadow-sm">
                <i class="fa-solid fa-rotate text-[#00a2e9]"></i>
                Diperbarui {{ $generatedAt->diffForHumans() }}
            </span>
        </div>

        <!-- KPI Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-3 sm:gap-4">
            <div class="bg-white rounded-2xl p-4 border border-slate-100 shadow-sm hover:shadow-md transition-all">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-slate-400">Total User</span>
                    <div class="w-8 h-8 rounded-xl bg-[#27438D]/10 text-[#27438D] flex items-center justify-center text-xs shrink-0">
                        <i class="fa-solid fa-users"></i>
                    </div>
                </div>
                <p class="text-xl sm:text-2xl font-extrabold text-[#0F1245] mt-2">{{ number_format($totalUsers) }}</p>
                <p class="text-[10px] text-emerald-600 font-semibold mt-0.5">{{ number_format($activeUsers) }} akun aktif</p>
            </div>

            <div class="bg-white rounded-2xl p-4 border border-slate-100 shadow-sm hover:shadow-md transition-all">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-slate-400">Online Sekarang</span>
                    <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xs shrink-0">
                        <i class="fa-solid fa-signal"></i>
                    </div>
                </div>
                <p class="text-xl sm:text-2xl font-extrabold text-emerald-600 mt-2">{{ number_format($onlineUsers) }}</p>
                <p class="text-[10px] text-slate-400 font-semibold mt-0.5">{{ number_format($activeSessions) }} sesi aktif</p>
            </div>

            <div class="bg-white rounded-2xl p-4 border border-slate-100 shadow-sm hover:shadow-md transition-all">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-slate-400">Login Hari Ini</span>
                    <div class="w-8 h-8 rounded-xl bg-[#00a2e9]/10 text-[#00a2e9] flex items-center justify-center text-xs shrink-0">
                        <i class="fa-solid fa-right-to-bracket"></i>
                    </div>
                </div>
                <p class="text-xl sm:text-2xl font-extrabold text-[#0F1245] mt-2">{{ number_format($loginsToday) }}</p>
                <p class="text-[10px] text-slate-400 font-semibold mt-0.5">Sejak 00:00 WIB</p>
            </div>

            <div class="bg-white rounded-2xl p-4 border border-slate-100 shadow-sm hover:shadow-md transition-all">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-slate-400">Login Periode</span>
                    <div class="w-8 h-8 rounded-xl bg-[#FCC626]/15 text-[#d49b06] flex items-center justify-center text-xs shrink-0">
                        <i class="fa-solid fa-chart-line"></i>
                    </div>
                </div>
                <p class="text-xl sm:text-2xl font-extrabold text-[#0F1245] mt-2">{{ number_format($periodLogins) }}</p>
                <p class="text-[10px] text-slate-400 font-semibold mt-0.5">{{ $period }} hari terakhir</p>
            </div>

            <div class="bg-white rounded-2xl p-4 border border-slate-100 shadow-sm hover:shadow-md transition-all">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-slate-400">User Unik</span>
                    <div class="w-8 h-8 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-xs shrink-0">
                        <i class="fa-solid fa-user-check"></i>
                    </div>
                </div>
                <p class="text-xl sm:text-2xl font-extrabold text-[#0F1245] mt-2">{{ number_format($uniqueUsers) }}</p>
                <p class="text-[10px] text-slate-400 font-semibold mt-0.5">Pernah beraktivitas</p>
            </div>

            <div class="bg-white rounded-2xl p-4 border border-slate-100 shadow-sm hover:shadow-md transition-all">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-slate-400">User Baru</span>
                    <div class="w-8 h-8 rounded-xl bg-[#2E7D3E]/10 text-[#2E7D3E] flex items-center justify-center text-xs shrink-0">
                        <i class="fa-solid fa-user-plus"></i>
                    </div>
                </div>
                <p class="text-xl sm:text-2xl font-extrabold text-[#0F1245] mt-2">{{ number_format($newUsers) }}</p>
                <p class="text-[10px] text-slate-400 font-semibold mt-0.5">Terdaftar di periode ini</p>
            </div>
        </div>

        <!-- Login Trend + Role Distribution -->
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
            <div class="xl:col-span-2 bg-white rounded-3xl p-5 sm:p-6 shadow-sm border border-gray-100">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5">
                    <div>
                        <h2 class="text-lg font-bold text-[#0F1245] flex items-center gap-2">
                            <i class="fa-solid fa-chart-line text-[#00a2e9]"></i>
                            Tren Login 14 Hari Terakhir
                        </h2>
                        <p class="text-xs text-gray-500 mt-0.5">Jumlah login berhasil per hari.</p>
                    </div>
                    <span class="inline-flex items-center gap-1.5 self-start px-3 py-1 rounded-full bg-[#00a2e9]/10 text-[#0077b6] text-[11px] font-bold">
                        <span class="w-2 h-2 rounded-full bg-[#00a2e9]"></span>
                        Login Berhasil
                    </span>
                </div>
                <div class="h-64 sm:h-72">
                    <canvas id="loginTrendChart"></canvas>
                </div>
            </div>

            <div class="bg-white rounded-3xl p-5 sm:p-6 shadow-sm border border-gray-100">
                <div class="mb-4">
                    <h2 class="text-lg font-bold text-[#0F1245] flex items-center gap-2">
                        <i class="fa-solid fa-users-viewfinder text-[#00a2e9]"></i>
                        Komposisi Role
                    </h2>
                    <p class="text-xs text-gray-500 mt-0.5">Distribusi akun berdasarkan role.</p>
                </div>
                <div class="h-56 sm:h-60 relative">
                    <canvas id="roleChart"></canvas>
                    <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                        <span class="text-2xl font-extrabold text-[#0F1245]">{{ number_format($totalUsers) }}</span>
                        <span class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Total User</span>
                    </div>
                </div>
                <div class="mt-4 grid grid-cols-3 gap-2">
                    @foreach($roleDistribution['labels'] as $index => $label)
                        <div class="text-center p-2 rounded-xl bg-gray-50 border border-gray-100">
                            <span class="block w-2 h-2 rounded-full mx-auto mb-1" style="background-color: {{ $roleDistribution['colors'][$index] }}"></span>
                            <span class="block text-[10px] text-gray-400 font-semibold">{{ $label }}</span>
                            <span class="block text-xs font-extrabold text-gray-800">{{ number_format($roleDistribution['data'][$index]) }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Module Usage + Peak Hours -->
        <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
            <div class="bg-white rounded-3xl p-5 sm:p-6 shadow-sm border border-gray-100">
                <div class="flex items-start justify-between gap-3 mb-5">
                    <div>
                        <h2 class="text-lg font-bold text-[#0F1245] flex items-center gap-2">
                            <i class="fa-solid fa-layer-group text-[#00a2e9]"></i>
                            Penggunaan Modul
                        </h2>
                        <p class="text-xs text-gray-500 mt-0.5">Jumlah aktivitas per modul dalam periode terpilih.</p>
                    </div>
                    <span class="px-3 py-1 rounded-full bg-gray-100 text-gray-600 text-[11px] font-bold whitespace-nowrap">
                        {{ number_format($totalModuleUsage) }} total
                    </span>
                </div>
                <div class="h-64 sm:h-72">
                    <canvas id="moduleChart"></canvas>
                </div>
                <div class="mt-4 grid grid-cols-2 sm:grid-cols-4 gap-2">
                    @foreach($moduleUsage as $module)
                        <div class="flex items-center gap-2 p-2 rounded-xl bg-gray-50 border border-gray-100">
                            <span class="w-7 h-7 rounded-lg flex items-center justify-center text-[11px] shrink-0" style="background-color: {{ $module['color'] }}15; color: {{ $module['color'] }};">
                                <i class="fa-solid {{ $module['icon'] }}"></i>
                            </span>
                            <div class="min-w-0">
                                <p class="text-[10px] text-gray-400 font-semibold truncate">{{ $module['name'] }}</p>
                                <p class="text-xs font-extrabold text-gray-800">{{ number_format($module['count']) }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="bg-white rounded-3xl p-5 sm:p-6 shadow-sm border border-gray-100">
                <div class="mb-5">
                    <h2 class="text-lg font-bold text-[#0F1245] flex items-center gap-2">
                        <i class="fa-solid fa-clock text-[#00a2e9]"></i>
                        Jam Paling Aktif
                    </h2>
                    <p class="text-xs text-gray-500 mt-0.5">Distribusi login berdasarkan jam (WIB).</p>
                </div>
                <div class="h-64 sm:h-72">
                    <canvas id="peakHourChart"></canvas>
                </div>
                <div class="mt-4 flex items-center gap-2 p-3 rounded-xl bg-[#FCC626]/10 border border-[#FCC626]/20">
                    <i class="fa-solid fa-lightbulb text-[#d49b06]"></i>
                    @php
                        $maxLogins = collect($peakHours['data'])->max();
                        $peakIndex = collect($peakHours['data'])->search($maxLogins);
                    @endphp
                    <p class="text-[11px] text-amber-800">
                        Jam tersibuk: <span class="font-bold">{{ $maxLogins > 0 ? $peakHours['labels'][$peakIndex] : '-' }}</span>
                        dengan {{ $maxLogins }} login.
                    </p>
                </div>
            </div>
        </div>

        <!-- Top Users + Action Breakdown -->
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
            <div class="xl:col-span-2 bg-white rounded-3xl p-5 sm:p-6 shadow-sm border border-gray-100">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
                    <div>
                        <h2 class="text-lg font-bold text-[#0F1245] flex items-center gap-2">
                            <i class="fa-solid fa-ranking-star text-[#00a2e9]"></i>
                            User Paling Aktif
                        </h2>
                        <p class="text-xs text-gray-500 mt-0.5">Berdasarkan jumlah aktivitas tercatat pada periode ini.</p>
                    </div>
                    <a href="{{ route('superadmin.logs.index') }}" class="text-xs font-semibold text-[#00a2e9] hover:underline self-start sm:self-auto">
                        Lihat semua log &rarr;
                    </a>
                </div>

                <div class="overflow-x-auto rounded-2xl border border-gray-100">
                    <table class="w-full text-left text-xs sm:text-sm text-gray-600 min-w-[520px]">
                        <thead class="bg-gray-50/80 text-gray-700 font-semibold text-xs border-b border-gray-100">
                            <tr>
                                <th class="px-4 py-3.5 w-12">#</th>
                                <th class="px-4 py-3.5">Pengguna</th>
                                <th class="px-4 py-3.5">Role</th>
                                <th class="px-4 py-3.5 text-right">Aktivitas</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($topUsers as $index => $user)
                                <tr class="hover:bg-blue-50/25 transition-colors">
                                    <td class="px-4 py-3">
                                        <span class="inline-flex items-center justify-center w-7 h-7 rounded-full text-[11px] font-extrabold {{ $loop->iteration <= 3 ? 'bg-[#FCC626]/20 text-[#b8860b]' : 'bg-gray-100 text-gray-500' }}">
                                            {{ $loop->iteration }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-2.5">
                                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-[#161758] to-[#27438D] text-white flex items-center justify-center text-[11px] font-bold shrink-0">
                                                {{ strtoupper(substr($user->user_name ?? 'U', 0, 1)) }}
                                            </div>
                                            <span class="font-bold text-gray-900 truncate max-w-[200px]">{{ $user->user_name ?? 'User #' . $user->user_id }}</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        @php
                                            $roleColor = match($user->user_role) {
                                                'superadmin' => 'bg-[#FCC626]/15 text-[#b8860b]',
                                                'hr' => 'bg-[#00a2e9]/10 text-[#0077b6]',
                                                default => 'bg-gray-100 text-gray-600',
                                            };
                                        @endphp
                                        <span class="inline-flex px-2.5 py-1 rounded-full text-[10px] font-bold uppercase {{ $roleColor }}">
                                            {{ $user->user_role ?? '-' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <span class="text-sm font-extrabold text-[#0F1245]">{{ number_format($user->total) }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-10 text-center text-xs text-gray-400">
                                        Belum ada aktivitas pengguna pada periode ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="space-y-6">
                <div class="bg-white rounded-3xl p-5 sm:p-6 shadow-sm border border-gray-100">
                    <h2 class="text-base font-bold text-[#0F1245] flex items-center gap-2 mb-4">
                        <i class="fa-solid fa-chart-simple text-[#00a2e9]"></i>
                        Aktivitas Terbanyak
                    </h2>
                    <div class="space-y-3">
                        @forelse($actionBreakdown as $action)
                            <div>
                                <div class="flex items-center justify-between gap-2 mb-1">
                                    <span class="text-[11px] font-semibold text-gray-600 truncate">{{ $action['label'] }}</span>
                                    <span class="text-[11px] font-extrabold text-gray-800 shrink-0">{{ number_format($action['total']) }}</span>
                                </div>
                                <div class="h-1.5 rounded-full bg-gray-100 overflow-hidden">
                                    <div class="h-full rounded-full bg-gradient-to-r from-[#00a2e9] to-[#27438D]" style="width: {{ $actionBreakdown[0]['total'] > 0 ? max(4, round(($action['total'] / $actionBreakdown[0]['total']) * 100)) : 0 }}%"></div>
                                </div>
                            </div>
                        @empty
                            <p class="text-xs text-gray-400">Belum ada data aktivitas.</p>
                        @endforelse
                    </div>
                </div>

                <div class="bg-white rounded-3xl p-5 sm:p-6 shadow-sm border border-gray-100">
                    <h2 class="text-base font-bold text-[#0F1245] flex items-center gap-2 mb-4">
                        <i class="fa-solid fa-database text-[#00a2e9]"></i>
                        Ringkasan Penyimpanan
                    </h2>
                    <div class="space-y-2.5">
                        <div class="flex items-center justify-between gap-3 p-3 rounded-xl bg-gray-50 border border-gray-100">
                            <span class="text-[11px] text-gray-500">Storage aplikasi</span>
                            <span class="text-[11px] font-bold text-gray-800">{{ app(\App\Services\DatabaseBackupService::class)->formatBytes($storageBytes) }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-3 p-3 rounded-xl bg-gray-50 border border-gray-100">
                            <span class="text-[11px] text-gray-500">File log</span>
                            <span class="text-[11px] font-bold text-gray-800">{{ app(\App\Services\DatabaseBackupService::class)->formatBytes($logBytes) }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-3 p-3 rounded-xl bg-gray-50 border border-gray-100">
                            <span class="text-[11px] text-gray-500">Total backup</span>
                            <span class="text-[11px] font-bold text-gray-800">{{ $backupStats['total_size_human'] }} ({{ $backupStats['count'] }} file)</span>
                        </div>
                        <div class="flex items-center justify-between gap-3 p-3 rounded-xl {{ $failedJobs > 0 ? 'bg-rose-50 border border-rose-100' : 'bg-emerald-50 border border-emerald-100' }}">
                            <span class="text-[11px] {{ $failedJobs > 0 ? 'text-rose-600' : 'text-emerald-600' }}">Failed jobs</span>
                            <span class="text-[11px] font-bold {{ $failedJobs > 0 ? 'text-rose-700' : 'text-emerald-700' }}">{{ number_format($failedJobs) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script type="application/json" id="stats-data">@json($chartData)</script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const dataElement = document.getElementById('stats-data');
        if (!dataElement || typeof Chart === 'undefined') {
            return;
        }

        const data = JSON.parse(dataElement.textContent);
        const fontFamily = "'Poppins', sans-serif";
        Chart.defaults.font.family = fontFamily;
        Chart.defaults.color = '#64748b';

        const gridColor = 'rgba(148, 163, 184, 0.12)';

        // 1. Tren login
        const loginCanvas = document.getElementById('loginTrendChart');
        if (loginCanvas) {
            const ctx = loginCanvas.getContext('2d');
            const gradient = ctx.createLinearGradient(0, 0, 0, 280);
            gradient.addColorStop(0, 'rgba(0, 162, 233, 0.30)');
            gradient.addColorStop(1, 'rgba(0, 162, 233, 0.01)');

            new Chart(loginCanvas, {
                type: 'line',
                data: {
                    labels: data.loginTrend.labels,
                    datasets: [{
                        label: 'Login',
                        data: data.loginTrend.data,
                        borderColor: '#00a2e9',
                        backgroundColor: gradient,
                        borderWidth: 2.5,
                        pointBackgroundColor: '#ffffff',
                        pointBorderColor: '#00a2e9',
                        pointBorderWidth: 2,
                        pointRadius: 3,
                        pointHoverRadius: 6,
                        fill: true,
                        tension: 0.38,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#0F1245',
                            titleFont: { size: 11, weight: '700' },
                            bodyFont: { size: 11 },
                            padding: 10,
                            cornerRadius: 10,
                            displayColors: false,
                        }
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: { font: { size: 10 }, maxRotation: 0, autoSkip: true }
                        },
                        y: {
                            beginAtZero: true,
                            ticks: { precision: 0, font: { size: 10 } },
                            grid: { color: gridColor, drawBorder: false }
                        }
                    }
                }
            });
        }

        // 2. Komposisi role
        const roleCanvas = document.getElementById('roleChart');
        if (roleCanvas) {
            new Chart(roleCanvas, {
                type: 'doughnut',
                data: {
                    labels: data.roleDistribution.labels,
                    datasets: [{
                        data: data.roleDistribution.data,
                        backgroundColor: data.roleDistribution.colors,
                        borderWidth: 3,
                        borderColor: '#ffffff',
                        hoverOffset: 6,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '68%',
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#0F1245',
                            padding: 10,
                            cornerRadius: 10,
                        }
                    }
                }
            });
        }

        // 3. Penggunaan modul
        const moduleCanvas = document.getElementById('moduleChart');
        if (moduleCanvas) {
            new Chart(moduleCanvas, {
                type: 'bar',
                data: {
                    labels: data.moduleUsage.labels,
                    datasets: [{
                        label: 'Aktivitas',
                        data: data.moduleUsage.data,
                        backgroundColor: data.moduleUsage.colors.map(color => color + 'CC'),
                        borderColor: data.moduleUsage.colors,
                        borderWidth: 1,
                        borderRadius: 7,
                        barThickness: 16,
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#0F1245',
                            padding: 10,
                            cornerRadius: 10,
                            displayColors: false,
                        }
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            ticks: { precision: 0, font: { size: 10 } },
                            grid: { color: gridColor, drawBorder: false }
                        },
                        y: {
                            grid: { display: false },
                            ticks: { font: { size: 10 } }
                        }
                    }
                }
            });
        }

        // 4. Jam aktif
        const peakCanvas = document.getElementById('peakHourChart');
        if (peakCanvas) {
            new Chart(peakCanvas, {
                type: 'bar',
                data: {
                    labels: data.peakHours.labels,
                    datasets: [{
                        label: 'Login',
                        data: data.peakHours.data,
                        backgroundColor: data.peakHours.data.map((value, index) => value === Math.max(...data.peakHours.data) ? '#FCC626' : 'rgba(39, 67, 141, 0.55)'),
                        borderRadius: 5,
                        borderSkipped: false,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#0F1245',
                            padding: 10,
                            cornerRadius: 10,
                            displayColors: false,
                        }
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: { font: { size: 9 }, maxRotation: 0, autoSkip: true, maxTicksLimit: 12 }
                        },
                        y: {
                            beginAtZero: true,
                            ticks: { precision: 0, font: { size: 10 } },
                            grid: { color: gridColor, drawBorder: false }
                        }
                    }
                }
            });
        }
    });
</script>
@endpush
