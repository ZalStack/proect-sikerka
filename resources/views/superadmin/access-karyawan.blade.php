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
                    <span class="text-gray-700">Manajemen Akses</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-[#0F1245] font-['Poppins']">
                    Manajemen Akses Karyawan
                </h1>
                <p class="text-xs sm:text-sm text-gray-500 font-light mt-0.5">
                    Tangguhkan (suspend) akun pada rentang tanggal tertentu dan atur hak akses menu tiap karyawan.
                </p>
            </div>

            <a href="{{ route('superadmin.dashboard') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold transition-all self-start">
                <i class="fa-solid fa-arrow-left"></i>
                <span>Kembali ke Dashboard</span>
            </a>
        </div>

        <!-- Validation Errors (gagal validasi server) -->
        @if($errors->any())
            <div class="bg-rose-500/10 border border-rose-500/20 text-rose-800 rounded-2xl p-4 shadow-sm" role="alert">
                <div class="flex items-start gap-3">
                    <div class="w-9 h-9 rounded-xl bg-rose-500 text-white flex items-center justify-center flex-shrink-0">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                    </div>
                    <div class="text-sm font-medium space-y-1">
                        <p class="font-bold">Data belum bisa disimpan:</p>
                        <ul class="list-disc list-inside text-xs">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

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

        <!-- KPI Cards -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <div class="bg-white rounded-2xl p-4 border border-gray-100 shadow-sm">
                <span class="text-xs font-medium text-gray-400">Total Akun Karyawan</span>
                <p class="text-2xl font-extrabold text-[#0F1245] mt-1">{{ $stats['total'] }}</p>
            </div>
            <div class="bg-white rounded-2xl p-4 border border-rose-200 shadow-sm bg-gradient-to-br from-white to-rose-50/40">
                <span class="text-xs font-bold text-rose-700">Sedang Disuspend</span>
                <div class="flex items-baseline justify-between mt-1">
                    <span class="text-2xl font-extrabold text-rose-600">{{ $stats['suspended'] }}</span>
                    <i class="fa-solid fa-user-lock text-rose-500 text-sm"></i>
                </div>
            </div>
            <div class="bg-white rounded-2xl p-4 border border-amber-200 shadow-sm bg-gradient-to-br from-white to-amber-50/40">
                <span class="text-xs font-bold text-amber-700">Menu Dibatasi</span>
                <div class="flex items-baseline justify-between mt-1">
                    <span class="text-2xl font-extrabold text-amber-600">{{ $stats['menu_limited'] }}</span>
                    <i class="fa-solid fa-eye-slash text-amber-500 text-sm"></i>
                </div>
            </div>
            <div class="bg-white rounded-2xl p-4 border border-emerald-100 shadow-sm bg-gradient-to-br from-white to-emerald-50/40">
                <span class="text-xs font-bold text-emerald-700">Akun Aktif</span>
                <div class="flex items-baseline justify-between mt-1">
                    <span class="text-2xl font-extrabold text-emerald-600">{{ $stats['aktif'] }}</span>
                    <i class="fa-solid fa-circle-check text-emerald-500 text-sm"></i>
                </div>
            </div>
        </div>

        <!-- Filter & Search -->
        <div class="bg-white rounded-3xl p-5 shadow-sm border border-gray-100">
            <form method="GET" action="{{ route('superadmin.access.index') }}" class="flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-3">
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 w-full lg:w-auto flex-1">
                    <div class="relative flex-1 min-w-0 sm:max-w-md">
                        <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                        <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama, email, NIP, atau jabatan..." class="w-full pl-9 pr-3 py-2 rounded-xl border border-gray-200 text-xs font-medium focus:ring-2 focus:ring-[#00a2e9]/20 focus:border-[#00a2e9]">
                    </div>

                    <select name="posisi" class="px-3 py-2 rounded-xl border border-gray-200 text-xs font-medium focus:ring-2 focus:ring-[#00a2e9]/20 focus:border-[#00a2e9]">
                        <option value="">-- Semua Role --</option>
                        <option value="superadmin" {{ $posisi === 'superadmin' ? 'selected' : '' }}>Super Administrator</option>
                        <option value="hr" {{ $posisi === 'hr' ? 'selected' : '' }}>HR Administrator</option>
                        <option value="karyawan" {{ $posisi === 'karyawan' ? 'selected' : '' }}>Karyawan</option>
                    </select>

                    <select name="status" class="px-3 py-2 rounded-xl border border-gray-200 text-xs font-medium focus:ring-2 focus:ring-[#00a2e9]/20 focus:border-[#00a2e9]">
                        <option value="">-- Semua Status --</option>
                        <option value="active" {{ $status === 'active' ? 'selected' : '' }}>Aktif</option>
                        <option value="suspended" {{ $status === 'suspended' ? 'selected' : '' }}>Sedang Disuspend</option>
                    </select>
                </div>

                <div class="flex items-center gap-2 justify-end">
                    <a href="{{ route('superadmin.access.index') }}" class="px-4 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-600 text-xs font-semibold transition-all">
                        Reset
                    </a>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-[#0F1245] hover:bg-[#161758] text-white text-xs font-bold transition-all shadow-sm flex items-center gap-1.5">
                        <i class="fa-solid fa-filter"></i>
                        <span>Filter</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- ==================== TABEL (md ke atas) ==================== -->
        <div class="hidden md:block bg-white rounded-3xl p-5 lg:p-6 shadow-sm border border-gray-100 space-y-4">
            <div class="overflow-x-auto rounded-2xl border border-gray-100">
                <table class="w-full text-left text-xs sm:text-sm text-gray-600">
                    <thead class="bg-gray-50/80 text-gray-700 font-semibold text-xs border-b border-gray-100">
                        <tr>
                            <th class="px-4 py-3.5">Nama & NIP</th>
                            <th class="px-4 py-3.5">Email</th>
                            <th class="px-4 py-3.5">Divisi & Jabatan</th>
                            <th class="px-4 py-3.5">Role</th>
                            <th class="px-4 py-3.5">Status Akun & Menu</th>
                            <th class="px-4 py-3.5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($karyawans as $k)
                            @php
                                $isSelf = (int) $k->id === (int) auth()->id();
                                $hasActiveRange = $k->suspend_start && $k->suspend_end
                                    && $k->suspend_start->lte(\Carbon\Carbon::today())
                                    && $k->suspend_end->gte(\Carbon\Carbon::today());
                                $hasAnyRange = (bool) ($k->suspend_start && $k->suspend_end);
                                $menuInfo = $k->menuRestrictionMeta();
                            @endphp
                            <tr class="hover:bg-blue-50/25 transition-colors align-top">
                                <td class="px-4 py-3.5">
                                    <div class="font-bold text-gray-900">{{ $k->nama_lengkap }}</div>
                                    <div class="text-[11px] text-gray-400">NIP: {{ $k->kode_pegawai }}</div>
                                </td>
                                <td class="px-4 py-3.5 text-xs text-gray-700">
                                    {{ $k->email }}
                                </td>
                                <td class="px-4 py-3.5 text-xs">
                                    <span class="font-semibold text-gray-800">{{ $k->jabatan ?? '-' }}</span>
                                    <span class="block text-gray-400">{{ $k->divisi ?? '-' }}</span>
                                </td>
                                <td class="px-4 py-3.5 whitespace-nowrap">
                                    @if($k->posisi === 'superadmin')
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-extrabold bg-gradient-to-r from-amber-100 to-yellow-100 text-amber-800 border border-amber-300 shadow-sm">
                                            <i class="fa-solid fa-crown text-amber-600"></i>
                                            SUPERADMIN
                                        </span>
                                    @elseif($k->posisi === 'hr')
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold bg-blue-100 text-blue-800 border border-blue-200">
                                            <i class="fa-solid fa-user-shield text-blue-600"></i>
                                            HR ADMIN
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-[11px] font-medium bg-gray-100 text-gray-700">
                                            Karyawan
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3.5">
                                    @include('superadmin.partials.access-badges', ['k' => $k, 'menuMeta' => $menuMeta])
                                </td>
                                <td class="px-4 py-3.5 text-right whitespace-nowrap">
                                    @if($isSelf)
                                        <span class="text-[11px] text-gray-400 italic">Akun ini milik Anda</span>
                                    @else
                                        <div class="inline-flex items-center gap-2">
                                            @if($hasAnyRange)
                                                <form action="{{ route('superadmin.access.unsuspend', $k->id) }}" method="POST" class="inline"
                                                      onsubmit="return confirm('Angkat masa suspend akun {{ \Illuminate\Support\Str::of($k->nama_lengkap)->limit(20) }}? Akun bisa login kembali.');">
                                                    @csrf
                                                    <button type="submit" class="px-3 py-1.5 rounded-lg bg-emerald-500 hover:bg-emerald-600 text-white text-xs font-semibold shadow-sm transition-all inline-flex items-center gap-1.5">
                                                        <i class="fa-solid fa-unlock"></i>
                                                        <span>Angkat Suspend</span>
                                                    </button>
                                                </form>
                                            @else
                                                <button type="button"
                                                        onclick="openSuspendModal(this)"
                                                        data-id="{{ $k->id }}"
                                                        data-name="{{ $k->nama_lengkap }}"
                                                        data-start=""
                                                        data-end=""
                                                        data-reason=""
                                                        class="px-3 py-1.5 rounded-lg bg-rose-500 hover:bg-rose-600 text-white text-xs font-semibold shadow-sm transition-all inline-flex items-center gap-1.5">
                                                    <i class="fa-solid fa-user-lock"></i>
                                                    <span>Suspend</span>
                                                </button>
                                            @endif

                                            <button type="button"
                                                    onclick="openMenuModal(this)"
                                                    data-id="{{ $k->id }}"
                                                    data-name="{{ $k->nama_lengkap }}"
                                                    data-disabled="{{ implode(',', $k->disabledMenuKeys()) }}"
                                                    data-superadmin="{{ $k->posisi === 'superadmin' ? '1' : '0' }}"
                                                    data-start="{{ $menuInfo['start'] }}"
                                                    data-end="{{ $menuInfo['end'] }}"
                                                    data-reason="{{ $menuInfo['reason'] }}"
                                                    class="px-3 py-1.5 rounded-lg bg-[#0F1245] hover:bg-[#161758] text-white text-xs font-semibold shadow-sm transition-all inline-flex items-center gap-1.5">
                                                <i class="fa-solid fa-toggle-on"></i>
                                                <span>Hak Akses Menu</span>
                                            </button>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-10 text-gray-400">
                                    Tidak ada data karyawan yang cocok dengan filter.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="pt-4">
                {{ $karyawans->links() }}
            </div>
        </div>

        <!-- ==================== KARTU (layar kecil / mobile) ==================== -->
        <div class="md:hidden space-y-4">
            @forelse($karyawans as $k)
                @php
                    $isSelf = (int) $k->id === (int) auth()->id();
                    $hasAnyRange = (bool) ($k->suspend_start && $k->suspend_end);
                    $menuInfo = $k->menuRestrictionMeta();
                @endphp
                <div class="bg-white rounded-3xl p-4 shadow-sm border border-gray-100 space-y-3">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="shrink-0 w-11 h-11 rounded-full bg-gradient-to-br from-[#00a2e9] to-[#0077b6] text-white flex items-center justify-center font-bold text-sm">
                                {{ strtoupper(substr($k->nama_lengkap, 0, 1)) }}
                            </div>
                            <div class="min-w-0">
                                <p class="font-bold text-gray-900 text-sm truncate">{{ $k->nama_lengkap }}</p>
                                <p class="text-[11px] text-gray-400 truncate">NIP: {{ $k->kode_pegawai }}</p>
                            </div>
                        </div>
                        @if($k->posisi === 'superadmin')
                            <span class="shrink-0 inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-amber-100 text-amber-800 border border-amber-300">
                                <i class="fa-solid fa-crown text-amber-600"></i> SUPERADMIN
                            </span>
                        @elseif($k->posisi === 'hr')
                            <span class="shrink-0 inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800 border border-blue-200">
                                HR ADMIN
                            </span>
                        @else
                            <span class="shrink-0 inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-gray-100 text-gray-700">
                                Karyawan
                            </span>
                        @endif
                    </div>

                    <div class="text-xs text-gray-600 space-y-0.5">
                        <p class="truncate"><i class="fa-solid fa-envelope text-gray-400 w-4"></i> {{ $k->email }}</p>
                        <p><i class="fa-solid fa-briefcase text-gray-400 w-4"></i> {{ $k->jabatan ?? '-' }} &middot; {{ $k->divisi ?? '-' }}</p>
                    </div>

                    @include('superadmin.partials.access-badges', ['k' => $k, 'menuMeta' => $menuMeta])

                    <div class="pt-1">
                        @if($isSelf)
                            <p class="text-[11px] text-gray-400 italic text-center py-1">Akun ini milik Anda</p>
                        @elseif($hasAnyRange)
                            <form action="{{ route('superadmin.access.unsuspend', $k->id) }}" method="POST"
                                  onsubmit="return confirm('Angkat masa suspend akun ini? Akun bisa login kembali.');">
                                @csrf
                                <button type="submit" class="w-full px-3 py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-white text-xs font-bold shadow-sm transition-all inline-flex items-center justify-center gap-2">
                                    <i class="fa-solid fa-unlock"></i>
                                    <span>Angkat Suspend</span>
                                </button>
                            </form>
                        @else
                            <button type="button"
                                    onclick="openSuspendModal(this)"
                                    data-id="{{ $k->id }}"
                                    data-name="{{ $k->nama_lengkap }}"
                                    data-start=""
                                    data-end=""
                                    data-reason=""
                                    class="w-full px-3 py-2.5 rounded-xl bg-rose-500 hover:bg-rose-600 text-white text-xs font-bold shadow-sm transition-all inline-flex items-center justify-center gap-2">
                                <i class="fa-solid fa-user-lock"></i>
                                <span>Suspend Akun (Rentang Tanggal)</span>
                            </button>
                        @endif

                        @if(!$isSelf)
                            <button type="button"
                                    onclick="openMenuModal(this)"
                                    data-id="{{ $k->id }}"
                                    data-name="{{ $k->nama_lengkap }}"
                                    data-disabled="{{ implode(',', $k->disabledMenuKeys()) }}"
                                    data-superadmin="{{ $k->posisi === 'superadmin' ? '1' : '0' }}"
                                    data-start="{{ $menuInfo['start'] }}"
                                    data-end="{{ $menuInfo['end'] }}"
                                    data-reason="{{ $menuInfo['reason'] }}"
                                    class="mt-2 w-full px-3 py-2.5 rounded-xl bg-[#0F1245] hover:bg-[#161758] text-white text-xs font-bold shadow-sm transition-all inline-flex items-center justify-center gap-2">
                                <i class="fa-solid fa-toggle-on"></i>
                                <span>Hak Akses Menu</span>
                            </button>
                        @endif
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-3xl p-8 shadow-sm border border-gray-100 text-center text-sm text-gray-400">
                    Tidak ada data karyawan yang cocok dengan filter.
                </div>
            @endforelse

            <div class="pt-1">
                {{ $karyawans->links() }}
            </div>
        </div>

    </div>
</div>

<!-- ==================== MODAL: SUSPEND AKUN ==================== -->
<div id="suspendModal" class="hidden fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true" aria-labelledby="suspendModalTitle">
    <div class="fixed inset-0 bg-[#0F1245]/60 backdrop-blur-sm" onclick="closeModal('suspendModal')"></div>
    <div class="relative min-h-full flex items-center justify-center p-4">
        <div class="relative w-full max-w-lg bg-white rounded-3xl shadow-2xl border border-gray-100 overflow-hidden">
            <!-- Header -->
            <div class="bg-gradient-to-r from-[#0F1245] to-[#27438D] px-5 sm:px-6 py-4 flex items-center justify-between gap-3">
                <div class="flex items-center gap-3 min-w-0">
                    <span class="shrink-0 w-10 h-10 rounded-xl bg-white/15 text-white flex items-center justify-center">
                        <i class="fa-solid fa-user-lock"></i>
                    </span>
                    <div class="min-w-0">
                        <h3 id="suspendModalTitle" class="text-white font-bold text-sm sm:text-base truncate">Suspend Akun</h3>
                        <p class="text-white/60 text-[11px] truncate">Akun tidak dapat login selama periode terpilih</p>
                    </div>
                </div>
                <button type="button" onclick="closeModal('suspendModal')" class="shrink-0 text-white/70 hover:text-white p-1.5 rounded-lg hover:bg-white/10 transition-all" aria-label="Tutup">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form id="suspendForm" method="POST" action="" class="p-5 sm:p-6 space-y-5">
                @csrf

                <!-- Target -->
                <div class="flex items-center gap-3 p-3 rounded-2xl bg-gray-50 border border-gray-100">
                    <span class="w-9 h-9 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-user-slash"></i>
                    </span>
                    <div class="min-w-0">
                        <p class="text-[11px] text-gray-400 font-medium">Akun yang akan ditangguhkan</p>
                        <p id="suspendTargetName" class="text-sm font-bold text-gray-900 truncate">-</p>
                    </div>
                </div>

                <!-- Preset durasi -->
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-2">Durasi Cepat <span class="font-normal text-gray-400">(klik untuk hitung otomatis)</span></label>
                    <div class="flex flex-wrap gap-2">
                        <button type="button" onclick="applyPreset(7)" class="preset-btn px-3.5 py-2 rounded-xl border border-gray-200 hover:border-[#00a2e9] hover:bg-[#00a2e9]/5 text-xs font-semibold text-gray-700 transition-all">
                            <i class="fa-regular fa-calendar mr-1"></i> 1 Minggu
                        </button>
                        <button type="button" onclick="applyPreset(14)" class="preset-btn px-3.5 py-2 rounded-xl border border-gray-200 hover:border-[#00a2e9] hover:bg-[#00a2e9]/5 text-xs font-semibold text-gray-700 transition-all">
                            <i class="fa-regular fa-calendar mr-1"></i> 2 Minggu
                        </button>
                        <button type="button" onclick="applyPreset(30)" class="preset-btn px-3.5 py-2 rounded-xl border border-gray-200 hover:border-[#00a2e9] hover:bg-[#00a2e9]/5 text-xs font-semibold text-gray-700 transition-all">
                            <i class="fa-regular fa-calendar mr-1"></i> 1 Bulan
                        </button>
                        <button type="button" onclick="applyPreset(90)" class="preset-btn px-3.5 py-2 rounded-xl border border-gray-200 hover:border-[#00a2e9] hover:bg-[#00a2e9]/5 text-xs font-semibold text-gray-700 transition-all">
                            <i class="fa-regular fa-calendar mr-1"></i> 3 Bulan
                        </button>
                    </div>
                </div>

                <!-- Rentang tanggal -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="suspend_start" class="block text-xs font-semibold text-gray-700 mb-1.5">Tanggal Mulai <span class="text-rose-500">*</span></label>
                        <input type="date" id="suspend_start" name="suspend_start" required
                               class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-xs font-medium focus:ring-2 focus:ring-[#00a2e9]/20 focus:border-[#00a2e9]">
                    </div>
                    <div>
                        <label for="suspend_end" class="block text-xs font-semibold text-gray-700 mb-1.5">Tanggal Selesai <span class="text-rose-500">*</span></label>
                        <input type="date" id="suspend_end" name="suspend_end" required
                               class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-xs font-medium focus:ring-2 focus:ring-[#00a2e9]/20 focus:border-[#00a2e9]">
                    </div>
                </div>

                <!-- Ringkasan periode -->
                <div id="suspendSummary" class="text-xs text-gray-500 bg-[#00a2e9]/5 border border-[#00a2e9]/15 rounded-xl px-4 py-3 flex items-center gap-2">
                    <i class="fa-solid fa-circle-info text-[#00a2e9]"></i>
                    <span>Pilih tanggal mulai &amp; selesai terlebih dahulu.</span>
                </div>

                <!-- Alasan -->
                <div>
                    <label for="suspend_reason" class="block text-xs font-semibold text-gray-700 mb-1.5">Alasan Suspend <span class="text-rose-500">*</span></label>
                    <textarea id="suspend_reason" name="suspend_reason" rows="3" required maxlength="500"
                              placeholder="Contoh: Pelanggaran kebijakan kerja — tinjauan tim HRD"
                              class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-xs font-medium focus:ring-2 focus:ring-[#00a2e9]/20 focus:border-[#00a2e9] resize-none"></textarea>
                    <p class="text-[11px] text-gray-400 mt-1">Alasan ini ditampilkan ke karyawan saat login ditolak.</p>
                </div>

                <!-- Footer -->
                <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-2 pt-1">
                    <button type="button" onclick="closeModal('suspendModal')" class="px-5 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold transition-all">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-rose-500 hover:bg-rose-600 text-white text-xs font-bold shadow-md transition-all inline-flex items-center justify-center gap-2">
                        <i class="fa-solid fa-user-lock"></i>
                        <span>Terapkan Suspend</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ==================== MODAL: HAK AKSES MENU ==================== -->
<div id="menuModal" class="hidden fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true" aria-labelledby="menuModalTitle">
    <div class="fixed inset-0 bg-[#0F1245]/60 backdrop-blur-sm" onclick="closeModal('menuModal')"></div>
    <div class="relative min-h-full flex items-center justify-center p-4">
        <div class="relative w-full max-w-xl bg-white rounded-3xl shadow-2xl border border-gray-100 overflow-hidden">
            <!-- Header -->
            <div class="bg-gradient-to-r from-[#0F1245] to-[#27438D] px-5 sm:px-6 py-4 flex items-center justify-between gap-3">
                <div class="flex items-center gap-3 min-w-0">
                    <span class="shrink-0 w-10 h-10 rounded-xl bg-white/15 text-white flex items-center justify-center">
                        <i class="fa-solid fa-toggle-on"></i>
                    </span>
                    <div class="min-w-0">
                        <h3 id="menuModalTitle" class="text-white font-bold text-sm sm:text-base truncate">Hak Akses Menu</h3>
                        <p class="text-white/60 text-[11px] truncate">Nonaktifkan menu pada rentang tanggal tertentu — menu tetap tampil namun dinonaktifkan</p>
                    </div>
                </div>
                <button type="button" onclick="closeModal('menuModal')" class="shrink-0 text-white/70 hover:text-white p-1.5 rounded-lg hover:bg-white/10 transition-all" aria-label="Tutup">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form id="menuForm" method="POST" action="" class="p-5 sm:p-6 space-y-4">
                @csrf

                <div class="flex items-center justify-between gap-3 flex-wrap">
                    <div class="flex items-center gap-3 min-w-0">
                        <span class="w-9 h-9 rounded-xl bg-[#00a2e9]/10 text-[#00a2e9] flex items-center justify-center shrink-0">
                            <i class="fa-solid fa-user"></i>
                        </span>
                        <div class="min-w-0">
                            <p class="text-[11px] text-gray-400 font-medium">Untuk karyawan</p>
                            <p id="menuTargetName" class="text-sm font-bold text-gray-900 truncate">-</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <button type="button" onclick="setAllMenus(true)" class="px-3 py-1.5 rounded-lg bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200 text-[11px] font-bold transition-all">
                            Aktifkan Semua
                        </button>
                        <button type="button" onclick="setAllMenus(false)" class="px-3 py-1.5 rounded-lg bg-rose-50 text-rose-700 hover:bg-rose-100 border border-rose-200 text-[11px] font-bold transition-all">
                            Nonaktifkan Semua
                        </button>
                    </div>
                </div>

                <div id="menuSuperadminNote" class="hidden text-xs text-amber-800 bg-amber-50 border border-amber-200 rounded-xl px-4 py-3 flex items-start gap-2">
                    <i class="fa-solid fa-triangle-exclamation mt-0.5"></i>
                    <span>Akun Super Admin selalu memiliki akses penuh ke semua menu — pembatasan di bawah tidak akan diterapkan untuk akun ini.</span>
                </div>

                <!-- Daftar menu dengan toggle -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                    @foreach($menuMeta as $key => $meta)
                        <label class="menu-item flex items-center justify-between gap-3 p-3 rounded-2xl border border-gray-200 hover:border-[#00a2e9]/50 hover:bg-[#00a2e9]/[0.03] cursor-pointer transition-all">
                            <span class="flex items-center gap-2.5 min-w-0">
                                <span class="shrink-0 w-8 h-8 rounded-lg flex items-center justify-center text-sm"
                                      style="background: {{ $meta['color'] }}1A; color: {{ $meta['color'] }};">
                                    <i class="fa-solid {{ $meta['icon'] }}"></i>
                                </span>
                                <span class="min-w-0">
                                    <span class="block text-xs font-bold text-gray-900 truncate">{{ $meta['name'] }}</span>
                                    <span class="block text-[10px] text-gray-400 truncate">{{ $meta['category'] }}</span>
                                </span>
                            </span>
                            <span class="relative inline-flex shrink-0 items-center">
                                <input type="checkbox" name="menus[]" value="{{ $key }}" class="peer sr-only" onchange="updateMenuCount()">
                                <span class="relative w-10 h-[22px] bg-gray-200 peer-checked:bg-emerald-500 rounded-full transition-colors duration-200
                                             before:content-[''] before:absolute before:top-1/2 before:-translate-y-1/2 before:left-[3px] before:w-[16px] before:h-[16px] before:bg-white before:rounded-full before:transition-transform before:duration-200
                                             peer-checked:before:translate-x-[18px] shadow-inner"></span>
                            </span>
                        </label>
                    @endforeach
                </div>

                <p id="menuCountInfo" class="text-[11px] text-gray-500 bg-gray-50 border border-gray-100 rounded-xl px-4 py-2.5">
                    0 dari 8 menu dinonaktifkan.
                </p>

                <!-- Periode & Alasan Pembatasan (sama seperti form Suspend Akun) -->
                <div id="menuDurationSection" class="space-y-4 pt-1 border-t border-gray-100">
                    <!-- Durasi Cepat -->
                    <div class="pt-4">
                        <label class="block text-xs font-semibold text-gray-700 mb-2">Durasi Cepat <span class="font-normal text-gray-400">(klik untuk hitung otomatis)</span></label>
                        <div class="flex flex-wrap gap-2">
                            <button type="button" onclick="applyMenuPreset(7)" class="px-3.5 py-2 rounded-xl border border-gray-200 hover:border-[#00a2e9] hover:bg-[#00a2e9]/5 text-xs font-semibold text-gray-700 transition-all">
                                <i class="fa-regular fa-calendar mr-1"></i> 1 Minggu
                            </button>
                            <button type="button" onclick="applyMenuPreset(14)" class="px-3.5 py-2 rounded-xl border border-gray-200 hover:border-[#00a2e9] hover:bg-[#00a2e9]/5 text-xs font-semibold text-gray-700 transition-all">
                                <i class="fa-regular fa-calendar mr-1"></i> 2 Minggu
                            </button>
                            <button type="button" onclick="applyMenuPreset(30)" class="px-3.5 py-2 rounded-xl border border-gray-200 hover:border-[#00a2e9] hover:bg-[#00a2e9]/5 text-xs font-semibold text-gray-700 transition-all">
                                <i class="fa-regular fa-calendar mr-1"></i> 1 Bulan
                            </button>
                            <button type="button" onclick="applyMenuPreset(90)" class="px-3.5 py-2 rounded-xl border border-gray-200 hover:border-[#00a2e9] hover:bg-[#00a2e9]/5 text-xs font-semibold text-gray-700 transition-all">
                                <i class="fa-regular fa-calendar mr-1"></i> 3 Bulan
                            </button>
                        </div>
                    </div>

                    <!-- Rentang Tanggal -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="menu_valid_from" class="block text-xs font-semibold text-gray-700 mb-1.5">Tanggal Mulai <span class="text-rose-500">*</span></label>
                            <input type="date" id="menu_valid_from" name="valid_from" required
                                   class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-xs font-medium focus:ring-2 focus:ring-[#00a2e9]/20 focus:border-[#00a2e9]">
                        </div>
                        <div>
                            <label for="menu_valid_until" class="block text-xs font-semibold text-gray-700 mb-1.5">Tanggal Selesai <span class="text-rose-500">*</span></label>
                            <input type="date" id="menu_valid_until" name="valid_until" required
                                   class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-xs font-medium focus:ring-2 focus:ring-[#00a2e9]/20 focus:border-[#00a2e9]">
                        </div>
                    </div>

                    <!-- Ringkasan Periode -->
                    <div id="menuRestrictionSummary" class="text-xs text-gray-500 bg-[#00a2e9]/5 border border-[#00a2e9]/15 rounded-xl px-4 py-3 flex items-center gap-2">
                        <i class="fa-solid fa-circle-info text-[#00a2e9]"></i>
                        <span>Pilih tanggal mulai &amp; selesai terlebih dahulu.</span>
                    </div>

                    <!-- Alasan -->
                    <div>
                        <label for="menu_reason" class="block text-xs font-semibold text-gray-700 mb-1.5">Alasan Pembatasan <span class="text-rose-500">*</span></label>
                        <textarea id="menu_reason" name="reason" rows="3" required maxlength="500"
                                  placeholder="Contoh: Menu hanya untuk tim tertentu — kebijakan divisi"
                                  class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-xs font-medium focus:ring-2 focus:ring-[#00a2e9]/20 focus:border-[#00a2e9] resize-none"></textarea>
                        <p class="text-[11px] text-gray-400 mt-1">Alasan ini ditampilkan ke karyawan saat membuka menu yang dimatikan.</p>
                    </div>
                </div>

                <!-- Footer -->
                <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-2 pt-1">
                    <button type="button" onclick="closeModal('menuModal')" class="px-5 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold transition-all">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-[#0F1245] hover:bg-[#161758] text-white text-xs font-bold shadow-md transition-all inline-flex items-center justify-center gap-2">
                        <i class="fa-solid fa-floppy-disk"></i>
                        <span>Simpan Hak Akses Menu</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    const ACCESS_BASE_URL = "{{ url('superadmin/akses-karyawan') }}";
    const MENU_KEYS = @json(array_keys($menuMeta));

    function fmtDate(d) {
        const m = String(d.getMonth() + 1).padStart(2, '0');
        const day = String(d.getDate()).padStart(2, '0');
        return d.getFullYear() + '-' + m + '-' + day;
    }

    function todayStr() {
        return fmtDate(new Date());
    }

    function showModal(id) {
        document.getElementById(id).classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeModal(id) {
        document.getElementById(id).classList.add('hidden');
        document.body.style.overflow = '';
    }

    // ==================== MODAL SUSPEND ====================
    function openSuspendModal(btn) {
        const id = btn.dataset.id;
        const name = btn.dataset.name || '-';

        document.getElementById('suspendForm').action = ACCESS_BASE_URL + '/' + id + '/suspend';
        document.getElementById('suspendTargetName').textContent = name;

        const startInput = document.getElementById('suspend_start');
        const endInput = document.getElementById('suspend_end');
        const reasonInput = document.getElementById('suspend_reason');

        startInput.value = btn.dataset.start || todayStr();
        endInput.value = btn.dataset.end || '';
        reasonInput.value = btn.dataset.reason || '';

        updateSuspendSummary();
        showModal('suspendModal');
    }

    function applyPreset(days) {
        const startInput = document.getElementById('suspend_start');
        const endInput = document.getElementById('suspend_end');

        if (!startInput.value) {
            startInput.value = todayStr();
        }

        const d = new Date(startInput.value + 'T00:00:00');
        d.setDate(d.getDate() + days);
        endInput.value = fmtDate(d);

        updateSuspendSummary();
    }

    function updateSuspendSummary() {
        const start = document.getElementById('suspend_start').value;
        const end = document.getElementById('suspend_end').value;
        const box = document.getElementById('suspendSummary').querySelector('span');

        if (!start || !end) {
            box.textContent = 'Pilih tanggal mulai & selesai terlebih dahulu.';
            return;
        }

        const s = new Date(start + 'T00:00:00');
        const e = new Date(end + 'T00:00:00');

        if (e < s) {
            box.textContent = 'Tanggal selesai tidak boleh sebelum tanggal mulai.';
            return;
        }

        const days = Math.round((e - s) / 86400000) + 1;
        const opts = { day: 'numeric', month: 'short', year: 'numeric' };
        box.textContent = 'Durasi ' + days + ' hari (' +
            s.toLocaleDateString('id-ID', opts) + ' — ' +
            e.toLocaleDateString('id-ID', opts) + '). Login diblokir pada periode tersebut.';
    }

    document.getElementById('suspend_start').addEventListener('change', updateSuspendSummary);
    document.getElementById('suspend_end').addEventListener('change', updateSuspendSummary);

    document.getElementById('suspendForm').addEventListener('submit', function (e) {
        const start = document.getElementById('suspend_start').value;
        const end = document.getElementById('suspend_end').value;
        const reason = document.getElementById('suspend_reason').value.trim();

        if (!start || !end) {
            e.preventDefault();
            alert('Tanggal mulai dan selesai wajib diisi.');
            return;
        }

        if (new Date(end + 'T00:00:00') < new Date(start + 'T00:00:00')) {
            e.preventDefault();
            alert('Tanggal selesai tidak boleh sebelum tanggal mulai.');
            return;
        }

        if (!reason) {
            e.preventDefault();
            alert('Alasan suspend wajib diisi.');
        }
    });

    // ==================== MODAL HAK AKSES MENU ====================
    function openMenuModal(btn) {
        const id = btn.dataset.id;
        const name = btn.dataset.name || '-';
        const isSuperadmin = btn.dataset.superadmin === '1';
        const disabled = (btn.dataset.disabled || '').split(',').filter(Boolean);

        document.getElementById('menuForm').action = ACCESS_BASE_URL + '/' + id + '/menus';
        document.getElementById('menuTargetName').textContent = name;
        document.getElementById('menuSuperadminNote').classList.toggle('hidden', !isSuperadmin);

        document.querySelectorAll('#menuForm input[name="menus[]"]').forEach(function (cb) {
            cb.checked = !disabled.includes(cb.value);
        });

        // Prefill periode & alasan dari pembatasan yang sedang berlaku
        document.getElementById('menu_valid_from').value = btn.dataset.start || todayStr();
        document.getElementById('menu_valid_until').value = btn.dataset.end || '';
        document.getElementById('menu_reason').value = btn.dataset.reason || '';

        updateMenuCount();
        updateMenuSummary();
        showModal('menuModal');
    }

    function applyMenuPreset(days) {
        const startInput = document.getElementById('menu_valid_from');
        const endInput = document.getElementById('menu_valid_until');

        if (!startInput.value) {
            startInput.value = todayStr();
        }

        const d = new Date(startInput.value + 'T00:00:00');
        d.setDate(d.getDate() + days);
        endInput.value = fmtDate(d);

        updateMenuSummary();
    }

    function updateMenuSummary() {
        const start = document.getElementById('menu_valid_from').value;
        const end = document.getElementById('menu_valid_until').value;
        const box = document.getElementById('menuRestrictionSummary').querySelector('span');

        if (!start || !end) {
            box.textContent = 'Pilih tanggal mulai & selesai terlebih dahulu.';
            return;
        }

        const s = new Date(start + 'T00:00:00');
        const e = new Date(end + 'T00:00:00');

        if (e < s) {
            box.textContent = 'Tanggal selesai tidak boleh sebelum tanggal mulai.';
            return;
        }

        const days = Math.round((e - s) / 86400000) + 1;
        const opts = { day: 'numeric', month: 'short', year: 'numeric' };
        box.textContent = 'Menu nonaktif selama ' + days + ' hari (' +
            s.toLocaleDateString('id-ID', opts) + ' — ' +
            e.toLocaleDateString('id-ID', opts) + '). Menu tetap tampil di sidebar dalam keadaan nonaktif dan kembali aktif otomatis setelah tanggal selesai.';
    }

    function setAllMenus(enabled) {
        document.querySelectorAll('#menuForm input[name="menus[]"]').forEach(function (cb) {
            cb.checked = enabled;
        });
        updateMenuCount();
    }

    function updateMenuCount() {
        const all = document.querySelectorAll('#menuForm input[name="menus[]"]');
        const checked = document.querySelectorAll('#menuForm input[name="menus[]"]:checked').length;
        const disabledCount = all.length - checked;
        const allActive = disabledCount === 0;

        document.getElementById('menuCountInfo').textContent = allActive
            ? 'Semua menu aktif — pembatasan akan dilepas (periode & alasan tidak diperlukan).'
            : disabledCount + ' dari ' + all.length + ' menu dinonaktifkan untuk karyawan ini.';

        // Periode & alasan hanya wajib bila ada menu yang dimatikan.
        // Saat semua menu aktif → form "pelepasan pembatasan", field dinonaktifkan.
        ['menu_valid_from', 'menu_valid_until', 'menu_reason'].forEach(function (elId) {
            document.getElementById(elId).required = !allActive;
        });

        const section = document.getElementById('menuDurationSection');
        section.classList.toggle('opacity-40', allActive);
        section.classList.toggle('pointer-events-none', allActive);
    }

    document.getElementById('menu_valid_from').addEventListener('change', updateMenuSummary);
    document.getElementById('menu_valid_until').addEventListener('change', updateMenuSummary);

    document.getElementById('menuForm').addEventListener('submit', function (e) {
        const all = document.querySelectorAll('#menuForm input[name="menus[]"]');
        const checked = document.querySelectorAll('#menuForm input[name="menus[]"]:checked').length;

        // Semua menu aktif = murni pelepasan pembatasan → tanpa periode/alasan
        if (checked === all.length) {
            return;
        }

        const start = document.getElementById('menu_valid_from').value;
        const end = document.getElementById('menu_valid_until').value;
        const reason = document.getElementById('menu_reason').value.trim();

        if (!start || !end) {
            e.preventDefault();
            alert('Tanggal mulai & selesai pembatasan menu wajib diisi.');
            return;
        }

        if (new Date(end + 'T00:00:00') < new Date(start + 'T00:00:00')) {
            e.preventDefault();
            alert('Tanggal selesai tidak boleh sebelum tanggal mulai.');
            return;
        }

        if (!reason) {
            e.preventDefault();
            alert('Alasan pembatasan menu wajib diisi.');
        }
    });

    // Tutup modal dengan tombol ESC
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeModal('suspendModal');
            closeModal('menuModal');
        }
    });
</script>
@endsection
