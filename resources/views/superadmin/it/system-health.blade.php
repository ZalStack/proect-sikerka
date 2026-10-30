@extends('layouts.app')

@section('content')
@php
    $statusStyles = [
        'healthy' => [
            'soft' => 'bg-emerald-50',
            'text' => 'text-emerald-600',
            'strong' => 'text-emerald-700',
            'border' => 'border-emerald-200',
            'badge' => 'bg-emerald-500',
            'icon' => 'fa-circle-check',
        ],
        'warning' => [
            'soft' => 'bg-amber-50',
            'text' => 'text-amber-600',
            'strong' => 'text-amber-700',
            'border' => 'border-amber-200',
            'badge' => 'bg-amber-500',
            'icon' => 'fa-triangle-exclamation',
        ],
        'critical' => [
            'soft' => 'bg-rose-50',
            'text' => 'text-rose-600',
            'strong' => 'text-rose-700',
            'border' => 'border-rose-200',
            'badge' => 'bg-rose-500',
            'icon' => 'fa-circle-exclamation',
        ],
    ];
    $style = $statusStyles[$overallStatus] ?? $statusStyles['warning'];
    $formatter = app(\App\Services\DatabaseBackupService::class);

    $diskBar = $diskPercent >= 90 ? 'bg-rose-500' : ($diskPercent >= 75 ? 'bg-amber-500' : 'bg-emerald-500');
@endphp

<div class="flex">
    @include('layouts.sidebar')

    <div class="flex-1 min-w-0 md:ml-64 p-4 sm:p-6 lg:p-8 space-y-6">

        <!-- Header -->
        <div class="flex flex-col xl:flex-row xl:items-end justify-between gap-4">
            <div>
                <div class="flex flex-wrap items-center gap-2 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">
                    <a href="{{ route('superadmin.dashboard') }}" class="hover:text-[#00a2e9] transition-colors">Super Admin</a>
                    <span>/</span>
                    <span class="text-gray-700">Pemeliharaan IT</span>
                    <span>/</span>
                    <span class="text-gray-700">Kesehatan Sistem</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-[#0F1245] font-['Poppins'] tracking-tight">
                    Kesehatan Sistem & Tools
                </h1>
                <p class="text-xs sm:text-sm text-gray-500 font-light mt-0.5 max-w-2xl">
                    Pantau kondisi database, cache, penyimpanan, queue, dan jalankan tindakan pemeliharaan langsung dari satu halaman.
                </p>
            </div>

            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
                <a href="{{ route('superadmin.backup.index') }}" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 text-xs font-semibold transition-all shadow-sm">
                    <i class="fa-solid fa-database text-[#00a2e9]"></i>
                    <span>Backup</span>
                </a>
                <a href="{{ route('superadmin.health.index') }}" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-gradient-to-r from-[#00a2e9] to-[#0077b6] hover:from-[#0095d6] hover:to-[#006aa1] text-white text-xs font-bold transition-all shadow-lg shadow-[#00a2e9]/25">
                    <i class="fa-solid fa-rotate"></i>
                    <span>Muat Ulang</span>
                </a>
            </div>
        </div>

        @include('superadmin.partials.flash')

        <!-- Overall status hero -->
        <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-[#0F1245] via-[#161758] to-[#27438D] p-5 sm:p-7 text-white shadow-xl shadow-[#161758]/20">
            <div class="absolute -right-16 -top-16 w-56 h-56 rounded-full bg-[#00a2e9]/20 blur-3xl"></div>
            <div class="absolute -left-16 -bottom-20 w-56 h-56 rounded-full bg-[#FCC626]/15 blur-3xl"></div>

            <div class="relative flex flex-col lg:flex-row lg:items-center justify-between gap-5">
                <div class="flex items-start gap-4">
                    <div class="relative shrink-0">
                        <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-2xl bg-white/10 border border-white/15 flex items-center justify-center text-2xl sm:text-3xl">
                            <i class="fa-solid {{ $style['icon'] }} text-[#FCC626]"></i>
                        </div>
                        <span class="absolute -bottom-1 -right-1 w-5 h-5 rounded-full {{ $style['badge'] }} border-[3px] border-[#161758] flex items-center justify-center">
                            <i class="fa-solid {{ $overallStatus === 'healthy' ? 'fa-check' : 'fa-exclamation' }} text-[8px] text-white"></i>
                        </span>
                    </div>
                    <div class="min-w-0">
                        <div class="inline-flex items-center gap-2 px-2.5 py-1 rounded-full bg-white/10 border border-white/15 text-[10px] font-bold uppercase tracking-wider text-white/80 mb-2">
                            <span class="w-1.5 h-1.5 rounded-full {{ $style['badge'] }} animate-pulse"></span>
                            Status Keseluruhan
                        </div>
                        <h2 class="text-xl sm:text-2xl font-extrabold tracking-tight">{{ $overallLabel }}</h2>
                        <p class="text-xs sm:text-sm text-white/70 mt-1 max-w-xl leading-relaxed">{{ $overallMessage }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 lg:min-w-[360px]">
                    <div class="p-3 rounded-2xl bg-white/10 border border-white/10 backdrop-blur">
                        <p class="text-[10px] font-semibold uppercase tracking-wider text-white/50">Backup</p>
                        <p class="text-sm font-extrabold mt-1">{{ $backupStats['count'] }} file</p>
                    </div>
                    <div class="p-3 rounded-2xl bg-white/10 border border-white/10 backdrop-blur">
                        <p class="text-[10px] font-semibold uppercase tracking-wider text-white/50">Failed Job</p>
                        <p class="text-sm font-extrabold mt-1 {{ $failedJobsCount > 0 ? 'text-rose-300' : 'text-emerald-300' }}">{{ number_format($failedJobsCount) }}</p>
                    </div>
                    <div class="p-3 rounded-2xl bg-white/10 border border-white/10 backdrop-blur">
                        <p class="text-[10px] font-semibold uppercase tracking-wider text-white/50">Queue</p>
                        <p class="text-sm font-extrabold mt-1">{{ number_format($pendingJobsCount) }} pending</p>
                    </div>
                    <div class="p-3 rounded-2xl bg-white/10 border border-white/10 backdrop-blur">
                        <p class="text-[10px] font-semibold uppercase tracking-wider text-white/50">Disk Dipakai</p>
                        <p class="text-sm font-extrabold mt-1">{{ $diskPercent }}%</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Checks -->
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-5 gap-4">
            @foreach($checks as $check)
                <div class="bg-white rounded-2xl p-4 sm:p-5 border {{ $check['ok'] ? 'border-slate-100' : 'border-rose-200' }} shadow-sm hover:shadow-md transition-all duration-300">
                    <div class="flex items-start justify-between gap-2">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center text-base shrink-0 {{ $check['ok'] ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600' }}">
                            <i class="fa-solid {{ $check['icon'] }}"></i>
                        </div>
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[9px] font-bold uppercase tracking-wider {{ $check['ok'] ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600' }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ $check['ok'] ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>
                            {{ $check['ok'] ? 'Normal' : 'Perhatian' }}
                        </span>
                    </div>
                    <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mt-3">{{ $check['label'] }}</p>
                    <p class="text-sm font-extrabold text-[#0F1245] mt-0.5">{{ $check['value'] }}</p>
                    <p class="text-[10px] text-slate-400 mt-1 leading-relaxed">{{ $check['detail'] }}</p>
                </div>
            @endforeach
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
            <!-- Left: storage + app info -->
            <div class="xl:col-span-2 space-y-6">
                <!-- Storage -->
                <div class="bg-white rounded-3xl p-5 sm:p-6 shadow-sm border border-gray-100">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5">
                        <div>
                            <h2 class="text-lg font-bold text-[#0F1245] flex items-center gap-2">
                                <i class="fa-solid fa-hard-drive text-[#00a2e9]"></i>
                                Penyimpanan Server
                            </h2>
                            <p class="text-xs text-gray-500 mt-0.5">Kapasitas disk dan penggunaan folder aplikasi.</p>
                        </div>
                        <span class="inline-flex items-center gap-1.5 self-start px-3 py-1 rounded-full {{ $diskPercent >= 90 ? 'bg-rose-50 text-rose-600' : ($diskPercent >= 75 ? 'bg-amber-50 text-amber-600' : 'bg-emerald-50 text-emerald-600') }} text-[11px] font-bold">
                            <span class="w-1.5 h-1.5 rounded-full {{ $diskBar }}"></span>
                            {{ $diskPercent }}% terpakai
                        </span>
                    </div>

                    <div class="space-y-5">
                        <div>
                            <div class="flex items-center justify-between gap-3 mb-2">
                                <span class="text-xs font-semibold text-gray-600">Disk Utama</span>
                                <span class="text-[11px] text-gray-400">
                                    {{ $formatter->formatBytes((int) $diskUsed) }} / {{ $formatter->formatBytes((int) $diskTotal) }}
                                </span>
                            </div>
                            <div class="h-2.5 rounded-full bg-gray-100 overflow-hidden">
                                <div class="h-full rounded-full {{ $diskBar }} transition-all duration-700" style="width: {{ min(100, max(1, $diskPercent)) }}%"></div>
                            </div>
                            <div class="flex items-center justify-between gap-3 mt-1.5">
                                <span class="text-[10px] text-gray-400">Sisa {{ $formatter->formatBytes((int) $diskFree) }}</span>
                                <span class="text-[10px] text-gray-400">{{ $formatter->formatBytes((int) $diskTotal) }} total</span>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div class="p-3.5 rounded-2xl bg-gray-50 border border-gray-100">
                                <div class="flex items-center gap-2 mb-1">
                                    <i class="fa-solid fa-folder-open text-[#00a2e9] text-xs"></i>
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400">storage/app</span>
                                </div>
                                <p class="text-sm font-extrabold text-[#0F1245]">{{ $formatter->formatBytes($storageSize) }}</p>
                            </div>
                            <div class="p-3.5 rounded-2xl bg-gray-50 border border-gray-100">
                                <div class="flex items-center gap-2 mb-1">
                                    <i class="fa-solid fa-file-lines text-[#d49b06] text-xs"></i>
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Log</span>
                                </div>
                                <p class="text-sm font-extrabold text-[#0F1245]">{{ $formatter->formatBytes($logsSize) }}</p>
                            </div>
                            <div class="p-3.5 rounded-2xl bg-gray-50 border border-gray-100">
                                <div class="flex items-center gap-2 mb-1">
                                    <i class="fa-solid fa-box-archive text-[#2E7D3E] text-xs"></i>
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Backup</span>
                                </div>
                                <p class="text-sm font-extrabold text-[#0F1245]">{{ $backupStats['total_size_human'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- App info -->
                <div class="bg-white rounded-3xl p-5 sm:p-6 shadow-sm border border-gray-100">
                    <div class="flex items-center justify-between gap-3 mb-4">
                        <div>
                            <h2 class="text-lg font-bold text-[#0F1245] flex items-center gap-2">
                                <i class="fa-solid fa-server text-[#00a2e9]"></i>
                                Informasi Aplikasi
                            </h2>
                            <p class="text-xs text-gray-500 mt-0.5">Konfigurasi runtime dan lingkungan server.</p>
                        </div>
                        <span class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-[#0F1245]/5 text-[#0F1245] text-[11px] font-bold">
                            <i class="fa-solid fa-microchip text-[#00a2e9]"></i>
                            Runtime
                        </span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                        @foreach($appInfo as $info)
                            <div class="flex items-center gap-3 p-3 rounded-xl bg-gray-50/80 border border-gray-100">
                                <span class="w-8 h-8 rounded-lg bg-white border border-gray-100 text-[#00a2e9] flex items-center justify-center text-[11px] shrink-0">
                                    <i class="fa-solid {{ $info['icon'] }}"></i>
                                </span>
                                <div class="min-w-0 flex-1">
                                    <p class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">{{ $info['label'] }}</p>
                                    <p class="text-xs font-bold text-gray-800 truncate" title="{{ $info['value'] }}">{{ $info['value'] }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Failed jobs -->
                <div class="bg-white rounded-3xl p-5 sm:p-6 shadow-sm border border-gray-100">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
                        <div>
                            <h2 class="text-lg font-bold text-[#0F1245] flex items-center gap-2">
                                <i class="fa-solid fa-list-check text-[#00a2e9]"></i>
                                Failed Jobs
                            </h2>
                            <p class="text-xs text-gray-500 mt-0.5">Job queue yang gagal diproses dan perlu ditinjau.</p>
                        </div>
                        <span class="inline-flex items-center gap-1.5 self-start px-3 py-1 rounded-full {{ $failedJobsCount > 0 ? 'bg-rose-50 text-rose-600' : 'bg-emerald-50 text-emerald-600' }} text-[11px] font-bold">
                            <span class="w-1.5 h-1.5 rounded-full {{ $failedJobsCount > 0 ? 'bg-rose-500' : 'bg-emerald-500' }}"></span>
                            {{ number_format($failedJobsCount) }} job
                        </span>
                    </div>

                    @if($failedJobs->isEmpty())
                        <div class="p-6 rounded-2xl bg-emerald-50/60 border border-emerald-100 text-center">
                            <div class="w-12 h-12 rounded-2xl bg-white text-emerald-500 flex items-center justify-center mx-auto mb-2.5 text-xl shadow-sm">
                                <i class="fa-solid fa-check-double"></i>
                            </div>
                            <p class="text-sm font-bold text-emerald-700">Tidak ada failed job</p>
                            <p class="text-xs text-emerald-600/70 mt-0.5">Semua job queue berjalan normal.</p>
                        </div>
                    @else
                        <div class="space-y-2">
                            @foreach($failedJobs as $job)
                                @php
                                    $payload = json_decode($job->payload ?? '', true);
                                    $jobName = $payload['displayName'] ?? ($payload['job'] ?? 'Unknown Job');
                                    $jobName = class_basename($jobName);
                                @endphp
                                <details class="group rounded-2xl border border-rose-100 bg-rose-50/40 overflow-hidden">
                                    <summary class="flex items-center gap-3 p-3 cursor-pointer list-none hover:bg-rose-50 transition-colors">
                                        <span class="w-8 h-8 rounded-lg bg-white text-rose-500 border border-rose-100 flex items-center justify-center text-[11px] shrink-0">
                                            <i class="fa-solid fa-bug"></i>
                                        </span>
                                        <div class="min-w-0 flex-1">
                                            <p class="text-xs font-bold text-gray-800 truncate">{{ $jobName }}</p>
                                            <p class="text-[10px] text-gray-400">
                                                {{ $job->queue ?? 'default' }} &middot;
                                                {{ $job->failed_at ? \Carbon\Carbon::parse($job->failed_at)->diffForHumans() : '-' }}
                                            </p>
                                        </div>
                                        <i class="fa-solid fa-chevron-down text-gray-400 text-xs transition-transform group-open:rotate-180 shrink-0"></i>
                                    </summary>
                                    <div class="px-3 pb-3">
                                        <pre class="bg-[#0F1245] text-rose-300 text-[10px] rounded-xl p-3 overflow-x-auto max-h-40 leading-relaxed whitespace-pre-wrap break-words">{{ $job->exception }}</pre>
                                    </div>
                                </details>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <!-- Right column: tools -->
            <div class="space-y-6">
                <!-- Pending migrations -->
                <div class="bg-white rounded-3xl p-5 sm:p-6 shadow-sm border border-gray-100">
                    <div class="flex items-center justify-between gap-3 mb-3">
                        <h2 class="text-base font-bold text-[#0F1245] flex items-center gap-2">
                            <i class="fa-solid fa-code-branch text-[#00a2e9]"></i>
                            Migrasi
                        </h2>
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full {{ empty($pendingMigrations) ? 'bg-emerald-50 text-emerald-600' : 'bg-amber-50 text-amber-600' }} text-[10px] font-bold">
                            {{ empty($pendingMigrations) ? 'Up to date' : count($pendingMigrations) . ' pending' }}
                        </span>
                    </div>

                    @if(empty($pendingMigrations))
                        <p class="text-xs text-gray-500 leading-relaxed">
                            Semua file migrasi sudah dijalankan. Struktur database sesuai dengan versi aplikasi.
                        </p>
                    @else
                        <p class="text-xs text-gray-500 mb-2 leading-relaxed">Migrasi berikut belum dijalankan:</p>
                        <div class="space-y-1.5 max-h-40 overflow-y-auto pr-1">
                            @foreach($pendingMigrations as $migration)
                                <div class="flex items-center gap-2 p-2 rounded-lg bg-amber-50 border border-amber-100">
                                    <i class="fa-solid fa-circle-exclamation text-amber-500 text-[10px] shrink-0"></i>
                                    <span class="text-[10px] font-mono text-amber-800 break-all">{{ $migration }}</span>
                                </div>
                            @endforeach
                        </div>
                        <div class="mt-3 p-2.5 rounded-xl bg-gray-50 border border-gray-100">
                            <p class="text-[10px] text-gray-500">Jalankan via terminal:</p>
                            <code class="block mt-1 text-[10px] font-mono text-[#0F1245]">php artisan migrate</code>
                        </div>
                    @endif
                </div>

                <!-- Cache tools -->
                <div class="bg-white rounded-3xl p-5 sm:p-6 shadow-sm border border-gray-100">
                    <h2 class="text-base font-bold text-[#0F1245] flex items-center gap-2 mb-1">
                        <i class="fa-solid fa-broom text-[#00a2e9]"></i>
                        Bersihkan Cache
                    </h2>
                    <p class="text-xs text-gray-500 mb-4">Pilih jenis cache yang ingin dibersihkan.</p>

                    <form action="{{ route('superadmin.health.cache') }}" method="POST" class="space-y-3"
                        onsubmit="return confirm('Bersihkan cache yang dipilih? Aplikasi akan memuat ulang konfigurasi pada request berikutnya.')">
                        @csrf
                        <select name="action" class="w-full px-3 py-2.5 rounded-xl border border-gray-200 bg-gray-50/60 text-xs font-semibold text-gray-700 focus:bg-white focus:border-[#00a2e9] focus:ring-2 focus:ring-[#00a2e9]/20 outline-none transition-all">
                            <option value="all">Semua Cache (Optimize Clear)</option>
                            <option value="config">Cache Konfigurasi</option>
                            <option value="route">Cache Route</option>
                            <option value="view">Cache View / Blade</option>
                            <option value="optimize">Optimize Clear Saja</option>
                        </select>
                        <button type="submit" class="w-full px-4 py-2.5 rounded-xl bg-[#0F1245] hover:bg-[#1a1d5c] text-white text-xs font-bold transition-all shadow-md inline-flex items-center justify-center gap-2">
                            <i class="fa-solid fa-eraser"></i>
                            <span>Bersihkan Cache</span>
                        </button>
                    </form>

                    <div class="mt-3 p-2.5 rounded-xl bg-amber-50 border border-amber-100">
                        <p class="text-[10px] text-amber-800 leading-relaxed">
                            <i class="fa-solid fa-triangle-exclamation mr-1"></i>
                            Pembersihan cache sesaat dapat memperlambat request pertama. Lakukan saat jam sepi.
                        </p>
                    </div>
                </div>

                <!-- Maintenance tools -->
                <div class="bg-white rounded-3xl p-5 sm:p-6 shadow-sm border border-gray-100">
                    <h2 class="text-base font-bold text-[#0F1245] flex items-center gap-2 mb-1">
                        <i class="fa-solid fa-screwdriver-wrench text-[#00a2e9]"></i>
                        Tools Maintenance
                    </h2>
                    <p class="text-xs text-gray-500 mb-4">Tindakan cepat untuk pemeliharaan sistem.</p>

                    <div class="space-y-2.5">
                        <form action="{{ route('superadmin.health.tool') }}" method="POST"
                            onsubmit="return confirm('Buat / perbarui storage symlink?')">
                            @csrf
                            <input type="hidden" name="tool" value="storage_link">
                            <button type="submit" class="w-full flex items-center gap-3 p-3 rounded-xl bg-gray-50 hover:bg-blue-50 border border-gray-100 hover:border-[#00a2e9]/30 transition-all text-left group">
                                <span class="w-8 h-8 rounded-lg bg-white border border-gray-100 text-[#00a2e9] flex items-center justify-center text-xs shrink-0 group-hover:bg-[#00a2e9] group-hover:text-white transition-all">
                                    <i class="fa-solid fa-link"></i>
                                </span>
                                <div class="min-w-0 flex-1">
                                    <p class="text-xs font-bold text-gray-800">Storage Symlink</p>
                                    <p class="text-[10px] text-gray-400 truncate">Buat tautan public/storage</p>
                                </div>
                                <i class="fa-solid fa-chevron-right text-gray-300 text-[10px] shrink-0"></i>
                            </button>
                        </form>

                        <form action="{{ route('superadmin.health.tool') }}" method="POST"
                            onsubmit="return confirm('Coba ulang semua failed job?')">
                            @csrf
                            <input type="hidden" name="tool" value="retry_failed">
                            <button type="submit" class="w-full flex items-center gap-3 p-3 rounded-xl bg-gray-50 hover:bg-amber-50 border border-gray-100 hover:border-amber-200 transition-all text-left group">
                                <span class="w-8 h-8 rounded-lg bg-white border border-gray-100 text-amber-500 flex items-center justify-center text-xs shrink-0 group-hover:bg-amber-500 group-hover:text-white transition-all">
                                    <i class="fa-solid fa-rotate-right"></i>
                                </span>
                                <div class="min-w-0 flex-1">
                                    <p class="text-xs font-bold text-gray-800">Retry Failed Jobs</p>
                                    <p class="text-[10px] text-gray-400 truncate">Masukkan kembali ke antrean</p>
                                </div>
                                <i class="fa-solid fa-chevron-right text-gray-300 text-[10px] shrink-0"></i>
                            </button>
                        </form>

                        <form action="{{ route('superadmin.health.tool') }}" method="POST"
                            onsubmit="return confirm('Hapus semua failed job secara permanen? Tindakan ini tidak dapat dibatalkan.')">
                            @csrf
                            <input type="hidden" name="tool" value="flush_failed">
                            <button type="submit" class="w-full flex items-center gap-3 p-3 rounded-xl bg-gray-50 hover:bg-rose-50 border border-gray-100 hover:border-rose-200 transition-all text-left group">
                                <span class="w-8 h-8 rounded-lg bg-white border border-gray-100 text-rose-500 flex items-center justify-center text-xs shrink-0 group-hover:bg-rose-500 group-hover:text-white transition-all">
                                    <i class="fa-solid fa-trash-can"></i>
                                </span>
                                <div class="min-w-0 flex-1">
                                    <p class="text-xs font-bold text-gray-800">Flush Failed Jobs</p>
                                    <p class="text-[10px] text-gray-400 truncate">Hapus permanen semua job gagal</p>
                                </div>
                                <i class="fa-solid fa-chevron-right text-gray-300 text-[10px] shrink-0"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Log preview -->
        <div class="bg-white rounded-3xl p-5 sm:p-6 shadow-sm border border-gray-100">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
                <div>
                    <h2 class="text-lg font-bold text-[#0F1245] flex items-center gap-2">
                        <i class="fa-solid fa-file-lines text-[#00a2e9]"></i>
                        Preview Log Aplikasi
                    </h2>
                    <p class="text-xs text-gray-500 mt-0.5">
                        {{ $logPath }}
                        @if($logModified) &middot; terakhir diubah {{ $logModified->diffForHumans() }} @endif
                    </p>
                </div>
                <span class="inline-flex items-center gap-1.5 self-start px-3 py-1 rounded-full bg-gray-100 text-gray-600 text-[11px] font-bold">
                    <i class="fa-solid fa-weight-hanging text-[#00a2e9]"></i>
                    {{ $formatter->formatBytes($logSize) }}
                </span>
            </div>

            @if(empty($logPreview))
                <div class="p-6 rounded-2xl bg-gray-50 border border-gray-100 text-center">
                    <p class="text-xs text-gray-400">File log belum tersedia atau masih kosong.</p>
                </div>
            @else
                <div class="rounded-2xl bg-[#0F1245] overflow-hidden border border-[#161758]">
                    <div class="flex items-center justify-between gap-3 px-4 py-2.5 bg-[#161758] border-b border-white/10">
                        <div class="flex items-center gap-1.5">
                            <span class="w-2.5 h-2.5 rounded-full bg-rose-400"></span>
                            <span class="w-2.5 h-2.5 rounded-full bg-amber-400"></span>
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-400"></span>
                        </div>
                        <span class="text-[10px] font-mono text-white/40 truncate">laravel.log &mdash; 120 baris terakhir</span>
                        <i class="fa-solid fa-terminal text-white/30 text-[10px]"></i>
                    </div>
                    <div class="max-h-80 overflow-auto p-3 sm:p-4">
                        <pre class="text-[10px] sm:text-[11px] font-mono leading-relaxed whitespace-pre-wrap break-words">@foreach($logPreview as $line)@php
                            $lineClass = 'text-slate-300';
                            if (stripos($line, 'ERROR') !== false || stripos($line, 'CRITICAL') !== false || stripos($line, 'EMERGENCY') !== false) {
                                $lineClass = 'text-rose-300';
                            } elseif (stripos($line, 'WARNING') !== false || stripos($line, 'WARN') !== false) {
                                $lineClass = 'text-amber-300';
                            } elseif (stripos($line, 'INFO') !== false) {
                                $lineClass = 'text-emerald-300';
                            }
                        @endphp<span class="{{ $lineClass }}">{{ $line }}</span>
@endforeach</pre>
                    </div>
                </div>
            @endif
        </div>

    </div>
</div>
@endsection
