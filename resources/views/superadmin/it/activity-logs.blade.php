@extends('layouts.app')

@section('content')
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
                    <span class="text-gray-700">Log Aktivitas</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-[#0F1245] font-['Poppins'] tracking-tight">
                    Log Aktivitas & Audit Trail
                </h1>
                <p class="text-xs sm:text-sm text-gray-500 font-light mt-0.5 max-w-2xl">
                    Riwayat lengkap aktivitas pengguna, perubahan data, dan tindakan administratif yang tercatat otomatis oleh sistem.
                </p>
            </div>

            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
                <a href="{{ route('superadmin.stats.index') }}" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 text-xs font-semibold transition-all shadow-sm">
                    <i class="fa-solid fa-chart-pie text-[#00a2e9]"></i>
                    <span>Statistik</span>
                </a>

                <a href="{{ route('superadmin.logs.export', request()->query()) }}"
                    class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-gradient-to-r from-[#00a2e9] to-[#0077b6] hover:from-[#0095d6] hover:to-[#006aa1] text-white text-xs font-bold transition-all shadow-lg shadow-[#00a2e9]/25">
                    <i class="fa-solid fa-file-csv"></i>
                    <span>Ekspor CSV</span>
                </a>
            </div>
        </div>

        @include('superadmin.partials.flash')

        <!-- KPI Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
            <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm hover:shadow-md transition-all duration-300 border-l-4 border-l-[#27438D]">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Log</p>
                        <p class="text-2xl font-extrabold text-[#0F1245] mt-1">{{ number_format($stats['total']) }}</p>
                        <p class="text-[11px] text-slate-400 mt-1">Seluruh aktivitas tersimpan</p>
                    </div>
                    <div class="w-11 h-11 rounded-2xl bg-[#27438D]/10 text-[#27438D] flex items-center justify-center text-lg shrink-0">
                        <i class="fa-solid fa-clipboard-list"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm hover:shadow-md transition-all duration-300 border-l-4 border-l-[#00a2e9]">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Aktivitas Hari Ini</p>
                        <p class="text-2xl font-extrabold text-[#0F1245] mt-1">{{ number_format($stats['today']) }}</p>
                        <p class="text-[11px] text-slate-400 mt-1">Sejak 00:00 WIB</p>
                    </div>
                    <div class="w-11 h-11 rounded-2xl bg-[#00a2e9]/10 text-[#00a2e9] flex items-center justify-center text-lg shrink-0">
                        <i class="fa-solid fa-bolt"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm hover:shadow-md transition-all duration-300 border-l-4 border-l-[#2E7D3E]">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">User Aktif Hari Ini</p>
                        <p class="text-2xl font-extrabold text-[#0F1245] mt-1">{{ number_format($stats['unique_users']) }}</p>
                        <p class="text-[11px] text-slate-400 mt-1">Pengguna dengan aktivitas</p>
                    </div>
                    <div class="w-11 h-11 rounded-2xl bg-[#2E7D3E]/10 text-[#2E7D3E] flex items-center justify-center text-lg shrink-0">
                        <i class="fa-solid fa-user-check"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm hover:shadow-md transition-all duration-300 border-l-4 border-l-[#ec1d1d]">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Aksi Kritis Hari Ini</p>
                        <p class="text-2xl font-extrabold text-[#0F1245] mt-1">{{ number_format($stats['critical']) }}</p>
                        <p class="text-[11px] text-slate-400 mt-1">Perlu perhatian audit</p>
                    </div>
                    <div class="w-11 h-11 rounded-2xl bg-[#ec1d1d]/10 text-[#ec1d1d] flex items-center justify-center text-lg shrink-0">
                        <i class="fa-solid fa-shield-halved"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter -->
        <div class="bg-white rounded-3xl p-5 sm:p-6 shadow-sm border border-gray-100">
            <div class="flex items-center justify-between gap-3 mb-4">
                <h2 class="text-base font-bold text-[#0F1245] flex items-center gap-2">
                    <i class="fa-solid fa-filter text-[#00a2e9]"></i>
                    Filter Log
                </h2>
                @if(collect($filters)->filter()->isNotEmpty())
                    <a href="{{ route('superadmin.logs.index') }}" class="text-[11px] font-bold text-rose-500 hover:text-rose-700 inline-flex items-center gap-1.5">
                        <i class="fa-solid fa-xmark"></i>
                        Reset filter
                    </a>
                @endif
            </div>

            <form method="GET" action="{{ route('superadmin.logs.index') }}" class="space-y-3">
                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-3">
                    <div class="sm:col-span-2">
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-gray-400 mb-1.5">Pencarian</label>
                        <div class="relative">
                            <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-300 text-xs"></i>
                            <input type="text" name="q" value="{{ $filters['q'] }}" placeholder="Cari deskripsi, user, IP, atau aksi..."
                                class="w-full pl-9 pr-3 py-2.5 rounded-xl border border-gray-200 bg-gray-50/60 text-xs sm:text-sm text-gray-700 focus:bg-white focus:border-[#00a2e9] focus:ring-2 focus:ring-[#00a2e9]/20 outline-none transition-all">
                        </div>
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-gray-400 mb-1.5">Pengguna</label>
                        <select name="user_id" class="w-full px-3 py-2.5 rounded-xl border border-gray-200 bg-gray-50/60 text-xs sm:text-sm text-gray-700 focus:bg-white focus:border-[#00a2e9] focus:ring-2 focus:ring-[#00a2e9]/20 outline-none transition-all">
                            <option value="">Semua pengguna</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" @selected((string) $filters['user_id'] === (string) $user->id)>
                                    {{ $user->nama_lengkap }} ({{ $user->posisi }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-gray-400 mb-1.5">Role</label>
                        <select name="role" class="w-full px-3 py-2.5 rounded-xl border border-gray-200 bg-gray-50/60 text-xs sm:text-sm text-gray-700 focus:bg-white focus:border-[#00a2e9] focus:ring-2 focus:ring-[#00a2e9]/20 outline-none transition-all">
                            <option value="">Semua role</option>
                            @foreach(['superadmin' => 'Superadmin', 'hr' => 'HR', 'karyawan' => 'Karyawan'] as $value => $label)
                                <option value="{{ $value }}" @selected($filters['role'] === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-gray-400 mb-1.5">Modul</label>
                        <select name="module" class="w-full px-3 py-2.5 rounded-xl border border-gray-200 bg-gray-50/60 text-xs sm:text-sm text-gray-700 focus:bg-white focus:border-[#00a2e9] focus:ring-2 focus:ring-[#00a2e9]/20 outline-none transition-all">
                            <option value="">Semua modul</option>
                            @foreach($modules as $module)
                                <option value="{{ $module }}" @selected($filters['module'] === $module)>
                                    {{ \App\Models\ActivityLog::MODULE_LABELS[$module] ?? $module }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-gray-400 mb-1.5">Aksi</label>
                        <select name="action" class="w-full px-3 py-2.5 rounded-xl border border-gray-200 bg-gray-50/60 text-xs sm:text-sm text-gray-700 focus:bg-white focus:border-[#00a2e9] focus:ring-2 focus:ring-[#00a2e9]/20 outline-none transition-all">
                            <option value="">Semua aksi</option>
                            @foreach($actions as $action)
                                <option value="{{ $action }}" @selected($filters['action'] === $action)>
                                    {{ \App\Models\ActivityLog::ACTION_LABELS[$action] ?? $action }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-gray-400 mb-1.5">Dari Tanggal</label>
                        <input type="date" name="from" value="{{ $filters['from'] }}"
                            class="w-full px-3 py-2.5 rounded-xl border border-gray-200 bg-gray-50/60 text-xs sm:text-sm text-gray-700 focus:bg-white focus:border-[#00a2e9] focus:ring-2 focus:ring-[#00a2e9]/20 outline-none transition-all">
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-gray-400 mb-1.5">Sampai Tanggal</label>
                        <input type="date" name="to" value="{{ $filters['to'] }}"
                            class="w-full px-3 py-2.5 rounded-xl border border-gray-200 bg-gray-50/60 text-xs sm:text-sm text-gray-700 focus:bg-white focus:border-[#00a2e9] focus:ring-2 focus:ring-[#00a2e9]/20 outline-none transition-all">
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pt-1">
                    <p class="text-[11px] text-gray-400">
                        Menampilkan <span class="font-bold text-gray-600">{{ $logs->count() }}</span> dari
                        <span class="font-bold text-gray-600">{{ number_format($logs->total()) }}</span> log
                    </p>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('superadmin.logs.index') }}" class="px-4 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-600 text-xs font-semibold transition-all text-center">
                            Reset
                        </a>
                        <button type="submit" class="px-5 py-2 rounded-xl bg-[#0F1245] hover:bg-[#1a1d5c] text-white text-xs font-bold transition-all shadow-md inline-flex items-center justify-center gap-2">
                            <i class="fa-solid fa-filter"></i>
                            Terapkan Filter
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Log table -->
        <div class="bg-white rounded-3xl p-5 sm:p-6 shadow-sm border border-gray-100 space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <h2 class="text-lg font-bold text-[#0F1245] flex items-center gap-2">
                        <i class="fa-solid fa-timeline text-[#00a2e9]"></i>
                        Riwayat Aktivitas
                    </h2>
                    <p class="text-xs text-gray-500 mt-0.5">Diurutkan dari aktivitas terbaru.</p>
                </div>
                <span class="inline-flex items-center gap-1.5 self-start sm:self-auto px-3 py-1 rounded-full bg-gray-100 text-gray-600 text-[11px] font-bold">
                    <i class="fa-solid fa-list-ol text-[#00a2e9]"></i>
                    Halaman {{ $logs->currentPage() }} / {{ max(1, $logs->lastPage()) }}
                </span>
            </div>

            <div class="overflow-x-auto rounded-2xl border border-gray-100">
                <table class="w-full text-left text-xs sm:text-sm text-gray-600 min-w-[860px]">
                    <thead class="bg-gray-50/80 text-gray-700 font-semibold text-xs border-b border-gray-100">
                        <tr>
                            <th class="px-4 py-3.5">Waktu</th>
                            <th class="px-4 py-3.5">Pengguna</th>
                            <th class="px-4 py-3.5">Aksi</th>
                            <th class="px-4 py-3.5">Modul</th>
                            <th class="px-4 py-3.5">Deskripsi</th>
                            <th class="px-4 py-3.5">IP</th>
                            <th class="px-4 py-3.5 text-right">Detail</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($logs as $log)
                            @php
                                $roleColor = match($log->user_role) {
                                    'superadmin' => 'bg-[#FCC626]/15 text-[#b8860b]',
                                    'hr' => 'bg-[#00a2e9]/10 text-[#0077b6]',
                                    default => 'bg-gray-100 text-gray-600',
                                };
                                $isCritical = in_array($log->action, [
                                    'backup.delete', 'access.suspend', 'access.menus', 'violation.confirm',
                                    'maintenance.toggle', 'karyawan.role_update', 'auth.failed',
                                ], true);
                                $metadataJson = $log->metadata ? json_encode($log->metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null;
                            @endphp
                            <tr class="hover:bg-blue-50/25 transition-colors">
                                <td class="px-4 py-3.5 whitespace-nowrap">
                                    <div class="font-semibold text-gray-800">{{ $log->created_at ? $log->created_at->translatedFormat('d M Y') : '-' }}</div>
                                    <div class="text-[11px] text-gray-400">{{ $log->created_at ? $log->created_at->format('H:i:s') : '-' }} WIB</div>
                                </td>
                                <td class="px-4 py-3.5">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-[#161758] to-[#27438D] text-white flex items-center justify-center text-[11px] font-bold shrink-0">
                                            {{ strtoupper(substr($log->user_name ?? 'S', 0, 1)) }}
                                        </div>
                                        <div class="min-w-0">
                                            <p class="font-bold text-gray-900 truncate max-w-[160px]">{{ $log->user_name ?? 'Sistem' }}</p>
                                            <span class="inline-flex px-1.5 py-0.5 rounded-md text-[9px] font-bold uppercase {{ $roleColor }}">
                                                {{ $log->user_role ?? 'system' }}
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3.5">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-bold {{ $isCritical ? 'bg-rose-50 text-rose-600' : 'bg-[#00a2e9]/10 text-[#0077b6]' }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $isCritical ? 'bg-rose-500' : 'bg-[#00a2e9]' }}"></span>
                                        {{ $log->action_label }}
                                    </span>
                                </td>
                                <td class="px-4 py-3.5 whitespace-nowrap">
                                    <span class="text-[11px] font-semibold text-gray-600">{{ $log->module ? $log->module_label : '-' }}</span>
                                </td>
                                <td class="px-4 py-3.5">
                                    <p class="text-gray-600 max-w-[280px] truncate" title="{{ $log->description }}">{{ $log->description ?? '-' }}</p>
                                </td>
                                <td class="px-4 py-3.5 whitespace-nowrap">
                                    <span class="text-[11px] font-mono text-gray-500">{{ $log->ip_address ?? '-' }}</span>
                                </td>
                                <td class="px-4 py-3.5 text-right">
                                    <button type="button"
                                        class="log-detail-btn px-3 py-1.5 rounded-lg bg-gray-100 hover:bg-[#0F1245] text-gray-600 hover:text-white text-[11px] font-bold transition-all inline-flex items-center gap-1.5"
                                        data-time="{{ $log->created_at ? $log->created_at->format('d M Y H:i:s') : '-' }}"
                                        data-user="{{ $log->user_name ?? 'Sistem' }}"
                                        data-role="{{ $log->user_role ?? 'system' }}"
                                        data-action="{{ $log->action_label }}"
                                        data-module="{{ $log->module ? $log->module_label : '-' }}"
                                        data-description="{{ $log->description ?? '-' }}"
                                        data-ip="{{ $log->ip_address ?? '-' }}"
                                        data-agent="{{ $log->user_agent ?? '-' }}"
                                        data-metadata="{{ $metadataJson ?? '' }}">
                                        <i class="fa-solid fa-eye"></i>
                                        <span>Detail</span>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-14 text-center">
                                    <div class="w-14 h-14 rounded-2xl bg-gray-50 text-gray-300 flex items-center justify-center mx-auto mb-3 text-2xl">
                                        <i class="fa-solid fa-clipboard-list"></i>
                                    </div>
                                    <p class="text-sm font-bold text-gray-700">Tidak ada log ditemukan</p>
                                    <p class="text-xs text-gray-400 mt-1 max-w-sm mx-auto">Coba ubah kombinasi filter atau perluas rentang tanggal pencarian.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($logs->hasPages())
                <div class="pt-2">
                    {{ $logs->links() }}
                </div>
            @endif
        </div>

    </div>
</div>

<!-- Detail Modal -->
<div id="logDetailModal" class="fixed inset-0 z-[70] hidden">
    <div class="absolute inset-0 bg-[#0F1245]/60 backdrop-blur-sm" data-close-modal></div>
    <div class="absolute inset-0 flex items-end sm:items-center justify-center p-0 sm:p-4">
        <div class="bg-white w-full sm:max-w-lg max-h-[90vh] sm:max-h-[85vh] rounded-t-3xl sm:rounded-3xl shadow-2xl overflow-hidden flex flex-col">
            <div class="flex items-center justify-between gap-3 px-5 sm:px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-[#0F1245] to-[#27438D] text-white">
                <div class="flex items-center gap-2.5 min-w-0">
                    <div class="w-9 h-9 rounded-xl bg-white/10 border border-white/15 flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-magnifying-glass-chart text-[#FCC626]"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-bold truncate">Detail Log Aktivitas</p>
                        <p id="modalTime" class="text-[11px] text-white/60 truncate">-</p>
                    </div>
                </div>
                <button type="button" data-close-modal class="w-8 h-8 rounded-lg bg-white/10 hover:bg-white/20 flex items-center justify-center transition-colors shrink-0">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>

            <div class="p-5 sm:p-6 overflow-y-auto space-y-4">
                <div class="grid grid-cols-2 gap-3">
                    <div class="p-3 rounded-xl bg-gray-50 border border-gray-100 min-w-0">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Pengguna</p>
                        <p id="modalUser" class="text-xs font-bold text-gray-800 mt-0.5 truncate">-</p>
                    </div>
                    <div class="p-3 rounded-xl bg-gray-50 border border-gray-100 min-w-0">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Role</p>
                        <p id="modalRole" class="text-xs font-bold text-gray-800 mt-0.5 uppercase truncate">-</p>
                    </div>
                    <div class="p-3 rounded-xl bg-gray-50 border border-gray-100 min-w-0">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Aksi</p>
                        <p id="modalAction" class="text-xs font-bold text-gray-800 mt-0.5 truncate">-</p>
                    </div>
                    <div class="p-3 rounded-xl bg-gray-50 border border-gray-100 min-w-0">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Modul</p>
                        <p id="modalModule" class="text-xs font-bold text-gray-800 mt-0.5 truncate">-</p>
                    </div>
                </div>

                <div>
                    <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-1">Deskripsi</p>
                    <div class="p-3 rounded-xl bg-gray-50 border border-gray-100">
                        <p id="modalDescription" class="text-xs text-gray-700 leading-relaxed break-words">-</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="min-w-0">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-1">Alamat IP</p>
                        <p id="modalIp" class="text-xs font-mono font-semibold text-gray-700 p-3 rounded-xl bg-gray-50 border border-gray-100 break-all">-</p>
                    </div>
                    <div class="min-w-0">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-1">User Agent</p>
                        <p id="modalAgent" class="text-[10px] text-gray-600 p-3 rounded-xl bg-gray-50 border border-gray-100 break-words max-h-24 overflow-y-auto">-</p>
                    </div>
                </div>

                <div id="modalMetadataWrap" class="hidden">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-1">Metadata</p>
                    <pre id="modalMetadata" class="bg-[#0F1245] text-emerald-300 text-[10px] rounded-xl p-3 overflow-x-auto max-h-48 leading-relaxed whitespace-pre-wrap break-words"></pre>
                </div>
            </div>

            <div class="px-5 sm:px-6 py-4 border-t border-gray-100 flex justify-end">
                <button type="button" data-close-modal class="px-4 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold transition-all">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const modal = document.getElementById('logDetailModal');
        if (!modal) return;

        const fields = {
            time: document.getElementById('modalTime'),
            user: document.getElementById('modalUser'),
            role: document.getElementById('modalRole'),
            action: document.getElementById('modalAction'),
            module: document.getElementById('modalModule'),
            description: document.getElementById('modalDescription'),
            ip: document.getElementById('modalIp'),
            agent: document.getElementById('modalAgent'),
            metadata: document.getElementById('modalMetadata'),
            metadataWrap: document.getElementById('modalMetadataWrap'),
        };

        function setText(el, value) {
            if (el) el.textContent = value || '-';
        }

        function openModal(button) {
            const data = button.dataset;

            setText(fields.time, data.time);
            setText(fields.user, data.user);
            setText(fields.role, data.role);
            setText(fields.action, data.action);
            setText(fields.module, data.module);
            setText(fields.description, data.description);
            setText(fields.ip, data.ip);
            setText(fields.agent, data.agent);

            const metadata = (data.metadata || '').trim();
            if (metadata) {
                fields.metadata.textContent = metadata;
                fields.metadataWrap.classList.remove('hidden');
            } else {
                fields.metadata.textContent = '';
                fields.metadataWrap.classList.add('hidden');
            }

            modal.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        }

        function closeModal() {
            modal.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }

        document.querySelectorAll('.log-detail-btn').forEach(function (button) {
            button.addEventListener('click', function () {
                openModal(button);
            });
        });

        modal.querySelectorAll('[data-close-modal]').forEach(function (el) {
            el.addEventListener('click', closeModal);
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && !modal.classList.contains('hidden')) {
                closeModal();
            }
        });
    });
</script>
@endpush
