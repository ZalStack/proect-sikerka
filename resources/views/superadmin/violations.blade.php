@extends('layouts.app')

@section('content')
<div class="flex">
    @include('layouts.sidebar')

    <div class="flex-1 min-w-0 md:ml-64 p-4 sm:p-6 lg:p-8 space-y-6">

        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">
                    <a href="{{ route('superadmin.dashboard') }}" class="hover:text-[#00a2e9]">Super Admin</a>
                    <span>/</span>
                    <span class="text-gray-700">Pelanggaran Presensi</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-[#0F1245] font-['Poppins']">
                    Monitoring Pelanggaran Presensi
                </h1>
                <p class="text-xs sm:text-sm text-gray-500 font-light mt-0.5">
                    Deteksi akses remote (TeamViewer / AnyDesk / RustDesk / RDP / VNC). Pelanggaran selalu ditinjau Superadmin terlebih dahulu &mdash; blokir HANYA diterapkan setelah konfirmasi. Tangga ban: 1&times; = 1 hari, 2&times; = 2 hari, 3&times; = permanen.
                </p>
            </div>

            <a href="{{ route('superadmin.dashboard') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold transition-all">
                <i class="fa-solid fa-arrow-left"></i>
                <span>Kembali ke Dashboard</span>
            </a>
        </div>

        <!-- Flash Messages -->
        @if(session('success'))
            <div class="bg-emerald-500/10 border border-emerald-500/20 text-emerald-800 rounded-2xl p-4 flex items-center justify-between shadow-sm" role="alert">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-emerald-500 text-white flex items-center justify-center flex-shrink-0">
                        <i class="fa-solid fa-check"></i>
                    </div>
                    <span class="text-sm font-medium">{{ session('success') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-900 p-1">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
        @endif

        @if(session('error'))
            <div class="bg-rose-500/10 border border-rose-500/20 text-rose-800 rounded-2xl p-4 flex items-center justify-between shadow-sm" role="alert">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-rose-500 text-white flex items-center justify-center flex-shrink-0">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                    </div>
                    <span class="text-sm font-medium">{{ session('error') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-rose-600 hover:text-rose-900 p-1">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
        @endif

        <!-- Feature Status Banner & Toggle On/Off -->
        <div class="bg-white rounded-3xl p-5 shadow-sm border {{ ($isFeatureEnabled ?? true) ? 'border-gray-100' : 'border-rose-200 bg-rose-50/20' }} flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3.5">
                <div class="w-12 h-12 rounded-2xl flex items-center justify-center text-xl flex-shrink-0 {{ ($isFeatureEnabled ?? true) ? 'bg-emerald-500/10 text-emerald-600 border border-emerald-500/20' : 'bg-rose-500/10 text-rose-600 border border-rose-500/20' }}">
                    <i class="fa-solid {{ ($isFeatureEnabled ?? true) ? 'fa-shield-halved' : 'fa-shield-slash' }}"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h2 class="text-sm sm:text-base font-bold text-[#0F1245]">Status Sistem Deteksi Pelanggaran Presensi</h2>
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wide {{ ($isFeatureEnabled ?? true) ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">
                            {{ ($isFeatureEnabled ?? true) ? 'AKTIF' : 'NON-AKTIF' }}
                        </span>
                    </div>
                    <p class="text-xs text-gray-500 mt-0.5">
                        {{ ($isFeatureEnabled ?? true) 
                            ? 'Sistem sedang aktif mendeteksi dan mencatat indikasi akses remote desktop saat karyawan presensi.' 
                            : 'Sistem sedang NON-AKTIF. Karyawan bebas presensi tanpa diperiksa atau ditolak oleh deteksi akses remote.' }}
                    </p>
                </div>
            </div>

            <form action="{{ route('superadmin.features.toggle', 'pelanggaran_presensi') }}" method="POST" class="flex-shrink-0 w-full sm:w-auto">
                @csrf
                <button type="submit" class="w-full sm:w-auto px-4 py-2.5 rounded-xl font-bold text-xs transition-all flex items-center justify-center gap-2 shadow-sm {{ ($isFeatureEnabled ?? true) ? 'bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200' : 'bg-emerald-600 hover:bg-emerald-700 text-white shadow-emerald-600/20' }}">
                    <i class="fa-solid {{ ($isFeatureEnabled ?? true) ? 'fa-toggle-off' : 'fa-toggle-on' }}"></i>
                    <span>{{ ($isFeatureEnabled ?? true) ? 'Nonaktifkan Deteksi Pelanggaran' : 'Aktifkan Deteksi Pelanggaran' }}</span>
                </button>
            </form>
        </div>

        <!-- KPI Cards -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <div class="bg-white rounded-2xl p-4 border border-amber-200 shadow-sm bg-gradient-to-br from-white to-amber-50/40">
                <span class="text-xs font-bold text-amber-700">Menunggu Review</span>
                <div class="flex items-baseline justify-between mt-1">
                    <span class="text-2xl font-extrabold text-amber-600">{{ $stats['pending'] }}</span>
                    <i class="fa-solid fa-hourglass-half text-amber-500 text-sm"></i>
                </div>
            </div>
            <div class="bg-white rounded-2xl p-4 border border-rose-100 shadow-sm">
                <span class="text-xs font-medium text-rose-600">Dikonfirmasi</span>
                <p class="text-2xl font-extrabold text-rose-700 mt-1">{{ $stats['confirmed'] }}</p>
            </div>
            <div class="bg-white rounded-2xl p-4 border border-emerald-100 shadow-sm">
                <span class="text-xs font-medium text-emerald-600">Ditolak (False Positive)</span>
                <p class="text-2xl font-extrabold text-emerald-700 mt-1">{{ $stats['dismissed'] }}</p>
            </div>
            <div class="bg-white rounded-2xl p-4 border border-gray-200 shadow-sm bg-gradient-to-br from-white to-gray-50">
                <span class="text-xs font-medium text-gray-600">Sedang Diblokir</span>
                <div class="flex items-baseline justify-between mt-1">
                    <span class="text-2xl font-extrabold text-[#0F1245]">{{ $stats['banned_now'] }}</span>
                    <i class="fa-solid fa-user-slash text-gray-400 text-sm"></i>
                </div>
            </div>
        </div>

        <!-- Filter & Search -->
        <div class="bg-white rounded-3xl p-5 shadow-sm border border-gray-100">
            <form method="GET" action="{{ route('superadmin.violations.index') }}" class="flex flex-col lg:flex-row items-center justify-between gap-3">
                <div class="flex flex-col sm:flex-row items-center gap-3 w-full lg:w-auto flex-1">
                    <div class="relative flex-1 min-w-[200px] max-w-md">
                        <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                        <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama, NIP, atau divisi..." class="w-full pl-9 pr-3 py-2 rounded-xl border border-gray-200 text-xs font-medium focus:ring-2 focus:ring-[#00a2e9]/20 focus:border-[#00a2e9]">
                    </div>

                    <select name="status" class="px-3 py-2 rounded-xl border border-gray-200 text-xs font-medium focus:ring-2 focus:ring-[#00a2e9]/20 focus:border-[#00a2e9]">
                        <option value="">-- Semua Status --</option>
                        <option value="pending_review" {{ $status === 'pending_review' ? 'selected' : '' }}>Menunggu Review</option>
                        <option value="confirmed" {{ $status === 'confirmed' ? 'selected' : '' }}>Dikonfirmasi</option>
                        <option value="dismissed" {{ $status === 'dismissed' ? 'selected' : '' }}>Ditolak (False Positive)</option>
                    </select>

                    <div class="flex items-center gap-2">
                        <input type="date" name="start_date" value="{{ $startDate }}" class="px-3 py-2 rounded-xl border border-gray-200 text-xs font-medium focus:ring-2 focus:ring-[#00a2e9]/20 focus:border-[#00a2e9]">
                        <span class="text-xs text-gray-400">s/d</span>
                        <input type="date" name="end_date" value="{{ $endDate }}" class="px-3 py-2 rounded-xl border border-gray-200 text-xs font-medium focus:ring-2 focus:ring-[#00a2e9]/20 focus:border-[#00a2e9]">
                    </div>
                </div>

                <div class="flex items-center gap-2 w-full lg:w-auto justify-end">
                    <a href="{{ route('superadmin.violations.index') }}" class="px-4 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-600 text-xs font-semibold transition-all">
                        Reset
                    </a>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-[#0F1245] hover:bg-[#161758] text-white text-xs font-bold transition-all shadow-sm flex items-center gap-1.5">
                        <i class="fa-solid fa-filter"></i>
                        <span>Filter</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- Violations Table -->
        <div class="bg-white rounded-3xl p-5 sm:p-6 shadow-sm border border-gray-100 space-y-4">
            <h2 class="text-lg font-bold text-[#0F1245] flex items-center gap-2">
                <i class="fa-solid fa-shield-halved text-[#00a2e9]"></i>
                Riwayat & Antrean Pelanggaran
            </h2>

            <div class="overflow-x-auto rounded-2xl border border-gray-100">
                <table class="w-full text-left text-xs sm:text-sm text-gray-600">
                    <thead class="bg-gray-50/80 text-gray-700 font-semibold text-xs border-b border-gray-100">
                        <tr>
                            <th class="px-4 py-3.5">Tanggal</th>
                            <th class="px-4 py-3.5">Karyawan</th>
                            <th class="px-4 py-3.5">Konteks</th>
                            <th class="px-4 py-3.5">Risiko</th>
                            <th class="px-4 py-3.5">Status</th>
                            <th class="px-4 py-3.5">Strike</th>
                            <th class="px-4 py-3.5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($violations as $v)
                            <tr class="hover:bg-blue-50/25 transition-colors align-top">
                                <td class="px-4 py-3.5 whitespace-nowrap">
                                    <div class="font-bold text-gray-900">{{ $v->tanggal ? $v->tanggal->translatedFormat('d M Y') : '-' }}</div>
                                    <div class="text-[11px] text-gray-400">{{ $v->created_at ? $v->created_at->format('H:i') : '-' }} WIB</div>
                                </td>
                                <td class="px-4 py-3.5">
                                    <div class="font-bold text-gray-900">{{ $v->karyawan->nama_lengkap ?? '(akun terhapus)' }}</div>
                                    <div class="text-[11px] text-gray-400">NIP: {{ $v->karyawan->kode_pegawai ?? '-' }} &bull; {{ $v->karyawan->divisi ?? '-' }}</div>
                                </td>
                                <td class="px-4 py-3.5 text-xs">
                                    <span class="px-2 py-1 rounded-lg bg-gray-100 text-gray-700 font-semibold">{{ strtoupper($v->konteks) }}</span>
                                </td>
                                <td class="px-4 py-3.5 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold {{ $v->risk_badge }}">
                                        {{ $v->risk_label }} ({{ $v->risk_score }})
                                    </span>
                                    <div class="text-[11px] text-gray-400 mt-1">{{ count($v->signals ?? []) }} sinyal</div>
                                </td>
                                <td class="px-4 py-3.5 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold {{ $v->status_badge }}">
                                        {{ $v->status_label }}
                                    </span>
                                    @if($v->reviewed_by && $v->reviewer)
                                        <div class="text-[11px] text-gray-400 mt-1">oleh {{ $v->reviewer->nama_lengkap }}</div>
                                    @endif
                                </td>
                                <td class="px-4 py-3.5 text-center">
                                    @if($v->strike_number)
                                        <span class="inline-flex items-center justify-center w-7 h-7 rounded-full {{ $v->strike_number >= 3 ? 'bg-rose-600 text-white' : 'bg-amber-100 text-amber-800' }} text-xs font-extrabold">
                                            {{ $v->strike_number }}
                                        </span>
                                    @else
                                        <span class="text-gray-300">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3.5 text-right whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-2">
                                        <!-- Detail (modal) -->
                                        <div x-data="{ open: false }">
                                            <button type="button" @click="open = true" class="px-3 py-1.5 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold shadow-sm transition-all">
                                                <i class="fa-solid fa-magnifying-glass mr-1"></i>Detail
                                            </button>

                                            <!-- Modal Detail Bukti -->
                                            <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                                                 class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display: none;">
                                                <div class="absolute inset-0 bg-black/60" @click="open = false"></div>
                                                <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[85vh] overflow-y-auto p-5 sm:p-6 space-y-4">
                                                    <div class="flex items-start justify-between gap-3">
                                                        <div>
                                                            <h3 class="text-base sm:text-lg font-extrabold text-[#0F1245]">Detail Bukti Pelanggaran</h3>
                                                            <p class="text-xs text-gray-500">{{ $v->karyawan->nama_lengkap ?? '-' }} &bull; {{ $v->tanggal ? $v->tanggal->translatedFormat('d F Y') : '-' }} &bull; {{ strtoupper($v->konteks) }}</p>
                                                        </div>
                                                        <button type="button" @click="open = false" class="text-gray-400 hover:text-gray-700 p-1"><i class="fa-solid fa-xmark text-lg"></i></button>
                                                    </div>

                                                    <div>
                                                        <p class="text-[11px] font-bold uppercase tracking-wider text-gray-400 mb-2">Sinyal Terdeteksi</p>
                                                        <div class="space-y-2">
                                                            @forelse($v->signals ?? [] as $signal)
                                                                <div class="flex items-start gap-3 bg-gray-50 border border-gray-100 rounded-xl p-3">
                                                                    <span class="shrink-0 mt-0.5 px-2 py-0.5 rounded-lg text-[10px] font-extrabold {{ ($signal['weight'] ?? 0) >= 100 ? 'bg-rose-100 text-rose-700' : 'bg-amber-100 text-amber-700' }}">
                                                                        {{ ($signal['weight'] ?? 0) >= 100 ? 'TINGGI' : 'RENDAH' }}
                                                                    </span>
                                                                    <div class="min-w-0">
                                                                        <p class="text-xs font-bold text-gray-800 break-all">{{ $signal['code'] ?? '-' }}</p>
                                                                        <p class="text-[11px] text-gray-500 mt-0.5">{{ $signal['detail'] ?? '-' }}</p>
                                                                        <p class="text-[10px] text-gray-400 mt-0.5">Sumber: {{ $signal['source'] ?? '-' }}</p>
                                                                    </div>
                                                                </div>
                                                            @empty
                                                                <p class="text-xs text-gray-400">Tidak ada detail sinyal.</p>
                                                            @endforelse
                                                        </div>
                                                    </div>

                                                    @if($v->notes)
                                                        <div>
                                                            <p class="text-[11px] font-bold uppercase tracking-wider text-gray-400 mb-1">Informasi Teknis</p>
                                                            <pre class="text-[11px] text-gray-600 bg-gray-50 border border-gray-100 rounded-xl p-3 whitespace-pre-wrap break-all">{{ $v->notes }}</pre>
                                                        </div>
                                                    @endif

                                                    @if($v->absensi_id)
                                                        <p class="text-[11px] text-gray-400">Terkait data absensi #{{ $v->absensi_id }}</p>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>

                                        @if($v->status === \App\Models\RemoteAccessViolation::STATUS_PENDING)
                                            <!-- Konfirmasi (terapkan ban) -->
                                            <form action="{{ route('superadmin.violations.confirm', $v->id) }}" method="POST" class="inline-flex items-center gap-1.5">
                                                @csrf
                                                <input type="text" name="pesan" maxlength="1000" placeholder="Pesan alert (opsional)" class="w-36 px-2.5 py-1.5 rounded-lg border border-gray-200 text-[11px] focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500">
                                                <button type="submit" onclick="return confirm('Konfirmasi pelanggaran ini? Ban akan diterapkan sesuai tangga (1 hari / 2 hari / permanen)/BLOKIR.')"
                                                    class="px-3 py-1.5 rounded-lg bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold shadow-sm transition-all">
                                                    <i class="fa-solid fa-gavel mr-1"></i>Konfirmasi
                                                </button>
                                            </form>

                                            <!-- Tolak (false positive) -->
                                            <form action="{{ route('superadmin.violations.dismiss', $v->id) }}" method="POST" class="inline-flex items-center gap-1.5">
                                                @csrf
                                                <button type="submit" onclick="return confirm('Tandai pelanggaran ini sebagai false positive?')"
                                                    class="px-3 py-1.5 rounded-lg bg-emerald-500 hover:bg-emerald-600 text-white text-xs font-bold shadow-sm transition-all">
                                                    <i class="fa-solid fa-check mr-1"></i>Tolak
                                                </button>
                                            </form>
                                        @elseif($v->status === \App\Models\RemoteAccessViolation::STATUS_CONFIRMED)
                                            <span class="text-[11px] text-gray-400 font-medium">Sudah ditindaklanjuti</span>
                                        @else
                                            <span class="text-[11px] text-gray-400 font-medium">Selesai</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-10 text-gray-400">
                                    Tidak ada data pelanggaran{{ $status ? ' untuk filter ini' : '' }}.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="pt-4">
                {{ $violations->links() }}
            </div>
        </div>

        <!-- Karyawan Diblokir -->
        <div class="bg-white rounded-3xl p-5 sm:p-6 shadow-sm border border-gray-100 space-y-4">
            <h2 class="text-lg font-bold text-[#0F1245] flex items-center gap-2">
                <i class="fa-solid fa-user-slash text-rose-600"></i>
                Karyawan yang Sedang Diblokir Presensi
            </h2>
            <p class="text-xs text-gray-500 -mt-2">Blokir hanya menonaktifkan check-in/check-out. Akun tetap bisa login dan melihat pesan alert di dashboardnya.</p>

            @forelse($bannedKaryawans as $b)
                <div class="border border-gray-100 rounded-2xl p-4 bg-gradient-to-br from-white to-rose-50/30 space-y-3">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div>
                            <div class="font-bold text-gray-900 flex items-center gap-2">
                                {{ $b->nama_lengkap }}
                                @if($b->is_banned_permanent)
                                    <span class="px-2 py-0.5 rounded-full bg-rose-600 text-white text-[10px] font-extrabold">BAN PERMANEN</span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full bg-amber-100 text-amber-800 text-[10px] font-extrabold">SISA {{ $b->banDaysRemaining() ?? 1 }} HARI</span>
                                @endif
                            </div>
                            <div class="text-[11px] text-gray-400 mt-0.5">
                                NIP: {{ $b->kode_pegawai }} &bull; {{ $b->divisi ?? '-' }} &bull; Total pelanggaran: <b>{{ $b->violation_count }}&times;</b>
                                @if(!$b->is_banned_permanent && $b->banned_until)
                                    &bull; berakhir {{ $b->banned_until->translatedFormat('d F Y, H:i') }}
                                @endif
                            </div>
                        </div>

                        <!-- Unban -->
                        <form action="{{ route('superadmin.karyawan.unban', $b->id) }}" method="POST">
                            @csrf
                            <button type="submit" onclick="return confirm('Aktifkan kembali akun {{ $b->nama_lengkap }}? Blokir dilepas & hitungan pelanggaran direset ke 0.')"
                                class="px-4 py-2 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-white text-xs font-bold shadow-sm transition-all whitespace-nowrap">
                                <i class="fa-solid fa-unlock-keyhole mr-1.5"></i>Aktifkan Kembali (Unban)
                            </button>
                        </form>
                    </div>

                    <!-- Pesan Alert -->
                    <form action="{{ route('superadmin.karyawan.ban-message', $b->id) }}" method="POST" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
                        @csrf
                        <div class="flex-1">
                            <label class="text-[11px] font-bold uppercase tracking-wider text-gray-400"><i class="fa-solid fa-message mr-1"></i>Pesan Alert (tampil otomatis di dashboard karyawan)</label>
                            <input type="text" name="ban_message" maxlength="1000" value="{{ $b->ban_message }}"
                                placeholder="Contoh: Pelanggaran kedua. Pemblokian berlaku 2 hari. Jangan ulangi akses remote saat absensi!"
                                class="w-full mt-1 px-3 py-2 rounded-xl border border-gray-200 text-xs focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500">
                        </div>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-[#0F1245] hover:bg-[#161758] text-white text-xs font-bold shadow-sm transition-all self-end">
                            Kirim
                        </button>
                    </form>

                    @if($b->ban_message)
                        <div class="bg-rose-50 border border-rose-200 rounded-xl p-3">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-rose-500 mb-0.5">Pesan saat ini</p>
                            <p class="text-xs text-rose-800">{{ $b->ban_message }}</p>
                        </div>
                    @endif
                </div>
            @empty
                <div class="text-center py-8 text-gray-400 text-sm">
                    <i class="fa-solid fa-shield-check text-2xl mb-2 block text-emerald-400"></i>
                    Tidak ada karyawan yang sedang diblokir presensi.
                </div>
            @endforelse
        </div>

    </div>
</div>
@endsection
