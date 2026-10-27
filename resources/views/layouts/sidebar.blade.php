@php
    $user = Auth::user();
    $isSuperAdmin = $user && $user->posisi === 'superadmin';
    $isHr = $user && $user->posisi === 'hr';
    $isKaryawan = $user && $user->posisi === 'karyawan';
    $currentRoute = Route::currentRouteName();

    $userPhoto = $user->foto_profil ? Storage::url($user->foto_profil) : null;
    $nameParts = explode(' ', $user->nama_lengkap ?? 'U');
    $userInitial = strtoupper(substr($nameParts[0] ?? 'U', 0, 1));
    $shortName =
        strlen($user->nama_lengkap ?? '') > 15
            ? substr($user->nama_lengkap ?? '', 0, 15) . '...'
            : $user->nama_lengkap ?? 'User';

    // Hitung jumlah pengajuan pending untuk notifikasi HR & Superadmin (di-cache 30 detik untuk efisiensi)
    $pendingCuti = 0;
    $pendingPerjalananDinas = 0;
    $pendingPerizinan = 0;
    if ($isHr || $isSuperAdmin) {
        $pendingCounts = \Illuminate\Support\Facades\Cache::remember('pending_approval_counts', 30, function () {
            return [
                'cuti' => \App\Models\Cuti::where('status', 'pending')->count(),
                'perjalanan_dinas' => \App\Models\PerjalananDinas::where('status', 'pending')->count(),
                'perizinan' => \App\Models\Perizinan::where('status', 'pending')->count(),
            ];
        });
        $pendingCuti = $pendingCounts['cuti'] ?? 0;
        $pendingPerjalananDinas = $pendingCounts['perjalanan_dinas'] ?? 0;
        $pendingPerizinan = $pendingCounts['perizinan'] ?? 0;
    }

    // Antrean review pelanggaran presensi (anti remote access) khusus Superadmin
    $pendingViolations = 0;
    if ($isSuperAdmin) {
        $pendingViolations = \Illuminate\Support\Facades\Cache::remember('superadmin_pending_violations', 30, function () {
            return \App\Models\RemoteAccessViolation::where('status', 'pending_review')->count();
        });
    }

    // Jumlah akun sedang disuspend (suspend rentang tanggal) khusus Superadmin
    $suspendedAccounts = 0;
    if ($isSuperAdmin) {
        $todayStr = now()->toDateString();
        $suspendedAccounts = \App\Models\Karyawan::whereNotNull('suspend_start')
            ->whereNotNull('suspend_end')
            ->whereDate('suspend_start', '<=', $todayStr)
            ->whereDate('suspend_end', '>=', $todayStr)
            ->count();
    }

    // Helper kelas untuk item menu aktif / tidak aktif (konsisten di semua role)
    $menuBase = 'group relative flex items-center gap-3 px-3 py-2.5 rounded-xl text-[13px] font-medium transition-all duration-200';
    $menuActive = 'bg-gradient-to-r from-[#00a2e9] to-[#0077b6] text-white shadow-lg shadow-[#00a2e9]/30 font-semibold';
    $menuIdle = 'text-slate-300/90 hover:bg-white/10 hover:text-white';
    $menuGoldActive = 'bg-gradient-to-r from-[#FCC626] to-[#f0a500] text-[#0F1245] shadow-lg shadow-[#FCC626]/30 font-bold';
    $iconChip = 'shrink-0 w-8 h-8 rounded-lg flex items-center justify-center transition-colors duration-200';
@endphp

{{-- Width MUST stay w-64 (256px) to match content pages' md:ml-64 offset. --}}
<aside id="sidebar"
    class="fixed inset-y-0 left-0 z-40 w-64 bg-[#0F1245] transform transition-transform duration-300 ease-in-out -translate-x-full md:translate-x-0 overflow-y-auto shadow-2xl md:shadow-xl sidebar-scroll border-r border-white/10">
    {{-- Subtle inner glow --}}
    <div class="pointer-events-none absolute inset-0 bg-gradient-to-b from-[#161758]/40 via-transparent to-[#161758]/40"></div>

    <div class="relative h-full flex flex-col pt-14 sm:pt-16">
        <div class="p-3 pb-24">
            <!-- User Profile Card -->
            <div class="relative overflow-hidden rounded-2xl p-3 mb-5 border border-white/10 bg-white/[0.07] backdrop-blur-sm">
                <div class="absolute -top-8 -right-8 w-24 h-24 rounded-full bg-[#00a2e9]/20 blur-xl"></div>
                <div class="relative flex items-center space-x-3">
                    <div class="relative shrink-0">
                        <div class="w-11 h-11 rounded-full {{ $isSuperAdmin ? 'bg-gradient-to-br from-[#FCC626] to-[#f0a500] text-[#0F1245]' : 'bg-gradient-to-br from-[#00a2e9] to-[#0077b6] text-white' }} flex items-center justify-center text-lg font-bold overflow-hidden ring-2 ring-white/20 shadow-lg">
                            @if ($userPhoto)
                                <img src="{{ $userPhoto }}" alt="{{ $user->nama_lengkap }}" class="w-full h-full object-cover">
                            @else
                                {{ $userInitial }}
                            @endif
                        </div>
                        <span class="absolute bottom-0 right-0 w-3 h-3 rounded-full bg-emerald-400 ring-2 ring-[#0F1245]"></span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-white font-semibold text-[13px] truncate" title="{{ $user->nama_lengkap }}">
                            {{ $shortName }}
                        </p>
                        @if($isSuperAdmin)
                            <span class="inline-flex items-center gap-1 text-[#FCC626] text-[10px] font-bold uppercase tracking-wider">
                                <i class="fa-solid fa-crown text-[9px]"></i>
                                Super Admin
                            </span>
                        @elseif($isHr)
                            <p class="text-[#00a2e9] text-[10px] font-medium">HR Administrator</p>
                        @else
                            <p class="text-[#00a2e9] text-[10px] font-medium">Karyawan</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- ============================================== -->
            <!-- SUPER ADMIN EXCLUSIVE MENUS -->
            <!-- ============================================== -->
            @if ($isSuperAdmin)
                <h3 class="text-[#FCC626] text-[10px] font-bold uppercase tracking-widest px-3 mb-2 flex items-center gap-1.5">
                    <i class="fa-solid fa-crown text-[10px]"></i>
                    <span>Super Admin Panel</span>
                </h3>

                <nav class="space-y-1 mb-5">
                    <!-- Dashboard Superadmin -->
                    <a href="{{ route('superadmin.dashboard') }}"
                        class="{{ $menuBase }} {{ $currentRoute === 'superadmin.dashboard' ? $menuGoldActive : $menuIdle }}">
                        <span class="{{ $iconChip }} {{ $currentRoute === 'superadmin.dashboard' ? 'bg-white/20' : 'bg-white/10 group-hover:bg-white/20' }}">
                            <i class="fa-solid fa-gauge text-sm"></i>
                        </span>
                        <span class="truncate">Dashboard Superadmin</span>
                    </a>

                    <!-- Kontrol Fitur & Maintenance -->
                    <a href="{{ route('superadmin.features.index') }}"
                        class="{{ $menuBase }} {{ $currentRoute === 'superadmin.features.index' ? $menuActive : $menuIdle }}">
                        <span class="{{ $iconChip }} {{ $currentRoute === 'superadmin.features.index' ? 'bg-white/20' : 'bg-white/10 group-hover:bg-white/20' }}">
                            <i class="fa-solid fa-sliders text-sm"></i>
                        </span>
                        <span class="truncate">Fitur & Maintenance</span>
                    </a>

                    <!-- Kelola & Koreksi Jam Presensi -->
                    <a href="{{ route('superadmin.absensi.index') }}"
                        class="{{ $menuBase }} {{ $currentRoute === 'superadmin.absensi.index' ? $menuActive : $menuIdle }}">
                        <span class="{{ $iconChip }} {{ $currentRoute === 'superadmin.absensi.index' ? 'bg-white/20' : 'bg-white/10 group-hover:bg-white/20' }}">
                            <i class="fa-solid fa-clock-rotate-left text-sm"></i>
                        </span>
                        <span class="truncate">Koreksi Presensi Karyawan</span>
                    </a>

                    <!-- Manajemen Role Pengguna -->
                    <a href="{{ route('superadmin.karyawan.index') }}"
                        class="{{ $menuBase }} {{ $currentRoute === 'superadmin.karyawan.index' ? $menuActive : $menuIdle }}">
                        <span class="{{ $iconChip }} {{ $currentRoute === 'superadmin.karyawan.index' ? 'bg-white/20' : 'bg-white/10 group-hover:bg-white/20' }}">
                            <i class="fa-solid fa-users-gear text-sm"></i>
                        </span>
                        <span class="truncate">Manajemen Role User</span>
                    </a>

                    <!-- Manajemen Akses: Suspend Akun & Hak Akses Menu -->
                    <a href="{{ route('superadmin.access.index') }}"
                        class="{{ $menuBase }} {{ str_starts_with((string) $currentRoute, 'superadmin.access.') ? $menuActive : $menuIdle }}">
                        <span class="{{ $iconChip }} {{ str_starts_with((string) $currentRoute, 'superadmin.access.') ? 'bg-white/20' : 'bg-white/10 group-hover:bg-white/20' }}">
                            <i class="fa-solid fa-user-lock text-sm"></i>
                        </span>
                        <span class="truncate flex-1">Manajemen Akses</span>
                        @if($suspendedAccounts > 0)
                            <span class="flex-shrink-0 bg-rose-500 text-white text-[9px] font-bold rounded-full min-w-[18px] h-[18px] flex items-center justify-center px-1 shadow-lg">
                                {{ $suspendedAccounts > 99 ? '99+' : $suspendedAccounts }}
                            </span>
                        @endif
                    </a>

                    <!-- Pelanggaran Presensi (Anti Remote Access) -->
                    @php
                        $isViolationsEnabled = \App\Models\SystemSetting::isFeatureEnabled('pelanggaran_presensi');
                    @endphp
                    <a href="{{ route('superadmin.violations.index') }}"
                        class="{{ $menuBase }} {{ $currentRoute === 'superadmin.violations.index' ? $menuActive : $menuIdle }}">
                        <span class="{{ $iconChip }} {{ $currentRoute === 'superadmin.violations.index' ? 'bg-white/20' : 'bg-white/10 group-hover:bg-white/20' }}">
                            <i class="fa-solid fa-user-shield text-sm"></i>
                        </span>
                        <span class="truncate flex-1">Pelanggaran Presensi</span>
                        @if(!$isViolationsEnabled)
                            <span class="flex-shrink-0 bg-gray-500/80 text-white text-[8px] font-bold rounded-md px-1.5 py-0.5 uppercase tracking-wider">
                                OFF
                            </span>
                        @elseif($pendingViolations > 0)
                            <span class="flex-shrink-0 bg-[#ec1d1d] text-white text-[9px] font-bold rounded-full min-w-[18px] h-[18px] flex items-center justify-center px-1 shadow-lg animate-pulse">
                                {{ $pendingViolations > 99 ? '99+' : $pendingViolations }}
                            </span>
                        @endif
                    </a>

                    <!-- Kelola Hari Libur & Tanggal Merah -->
                    <a href="{{ route('superadmin.hari-libur.index') }}"
                        class="{{ $menuBase }} {{ str_starts_with((string) $currentRoute, 'superadmin.hari-libur.') ? $menuActive : $menuIdle }}">
                        <span class="{{ $iconChip }} {{ str_starts_with((string) $currentRoute, 'superadmin.hari-libur.') ? 'bg-white/20' : 'bg-white/10 group-hover:bg-white/20' }}">
                            <i class="fa-solid fa-calendar-day text-sm"></i>
                        </span>
                        <span class="truncate">Hari Libur & Tgl Merah</span>
                    </a>
                </nav>

            @elseif ($isHr)
                <!-- ============================================== -->
                <!-- HR EXCLUSIVE MENUS -->
                <!-- ============================================== -->
                <h3 class="text-[#00a2e9]/70 text-[10px] font-bold uppercase tracking-widest px-3 mb-2">Menu Utama HR</h3>
                <nav class="space-y-1">
                    <!-- HR Dashboard -->
                    <a href="{{ route('hr.dashboard') }}"
                        class="{{ $menuBase }} {{ $currentRoute === 'hr.dashboard' ? $menuActive : $menuIdle }}">
                        <span class="{{ $iconChip }} {{ $currentRoute === 'hr.dashboard' ? 'bg-white/20' : 'bg-white/10 group-hover:bg-white/20' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
                            </svg>
                        </span>
                        <span class="truncate">Dashboard</span>
                    </a>

                    <!-- Data Karyawan -->
                    <a href="{{ route('hr.karyawan.index') }}"
                        class="{{ $menuBase }} {{ $currentRoute === 'hr.karyawan.index' || str_starts_with($currentRoute, 'hr.karyawan.') ? $menuActive : $menuIdle }}">
                        <span class="{{ $iconChip }} {{ $currentRoute === 'hr.karyawan.index' || str_starts_with($currentRoute, 'hr.karyawan.') ? 'bg-white/20' : 'bg-white/10 group-hover:bg-white/20' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                            </svg>
                        </span>
                        <span class="truncate">Data Karyawan</span>
                    </a>

                    <!-- Absensi HR -->
                    @php $menuOff = Auth::user()->isMenuDisabled('absensi'); @endphp
                    <a href="{{ route('hr.absensi.index') }}"
                        class="{{ $menuBase }} {{ $currentRoute === 'hr.absensi.index' || str_starts_with($currentRoute, 'hr.absensi.') ? $menuActive : $menuIdle }} {{ $menuOff ? 'menu-restricted opacity-50 saturate-50 cursor-not-allowed' : '' }}" @if($menuOff) title="Menu dinonaktifkan oleh Super Administrator — silakan hubungi Superadmin" @endif>
                        <span class="{{ $iconChip }} {{ $currentRoute === 'hr.absensi.index' || str_starts_with($currentRoute, 'hr.absensi.') ? 'bg-white/20' : 'bg-white/10 group-hover:bg-white/20' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </span>
                        <span class="truncate">Presensi Karyawan</span>
                        @if($menuOff)<i class="fa-solid fa-lock text-[10px] opacity-70 ml-1" aria-hidden="true"></i>@endif
                    </a>

                    <!-- Pengumuman HR -->
                    @php $menuOff = Auth::user()->isMenuDisabled('pengumuman'); @endphp
                    <a href="{{ route('hr.pengumuman.index') }}"
                        class="{{ $menuBase }} {{ $currentRoute === 'hr.pengumuman.index' || str_starts_with($currentRoute, 'hr.pengumuman.') ? $menuActive : $menuIdle }} {{ $menuOff ? 'menu-restricted opacity-50 saturate-50 cursor-not-allowed' : '' }}" @if($menuOff) title="Menu dinonaktifkan oleh Super Administrator — silakan hubungi Superadmin" @endif>
                        <span class="{{ $iconChip }} {{ $currentRoute === 'hr.pengumuman.index' || str_starts_with($currentRoute, 'hr.pengumuman.') ? 'bg-white/20' : 'bg-white/10 group-hover:bg-white/20' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                            </svg>
                        </span>
                        <span class="truncate">Pengumuman</span>
                        @if($menuOff)<i class="fa-solid fa-lock text-[10px] opacity-70 ml-1" aria-hidden="true"></i>@endif
                    </a>

                    <!-- CUTI -->
                    @php $menuOff = Auth::user()->isMenuDisabled('cuti'); @endphp
                    <a href="{{ route('hr.cuti.index') }}"
                        class="{{ $menuBase }} {{ $currentRoute === 'hr.cuti.index' || str_starts_with($currentRoute, 'hr.cuti.') ? $menuActive : $menuIdle }} {{ $menuOff ? 'menu-restricted opacity-50 saturate-50 cursor-not-allowed' : '' }}" @if($menuOff) title="Menu dinonaktifkan oleh Super Administrator — silakan hubungi Superadmin" @endif>
                        <span class="{{ $iconChip }} {{ $currentRoute === 'hr.cuti.index' || str_starts_with($currentRoute, 'hr.cuti.') ? 'bg-white/20' : 'bg-white/10 group-hover:bg-white/20' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </span>
                        <span class="truncate flex-1">Cuti</span>
                        @if($menuOff)<i class="fa-solid fa-lock text-[10px] opacity-70 ml-1" aria-hidden="true"></i>@endif
                        @if($pendingCuti > 0)
                            <span class="flex-shrink-0 bg-[#ec1d1d] text-white text-[9px] font-bold rounded-full min-w-[18px] h-[18px] flex items-center justify-center px-1 shadow-lg animate-pulse">
                                {{ $pendingCuti > 99 ? '99+' : $pendingCuti }}
                            </span>
                        @endif
                    </a>

                    <!-- Perjalanan Dinas HR -->
                    @php $menuOff = Auth::user()->isMenuDisabled('perjalanan_dinas'); @endphp
                    <a href="{{ route('hr.perjalanan-dinas.index') }}"
                        class="{{ $menuBase }} {{ $currentRoute === 'hr.perjalanan-dinas.index' || str_starts_with($currentRoute, 'hr.perjalanan-dinas.') ? $menuActive : $menuIdle }} {{ $menuOff ? 'menu-restricted opacity-50 saturate-50 cursor-not-allowed' : '' }}" @if($menuOff) title="Menu dinonaktifkan oleh Super Administrator — silakan hubungi Superadmin" @endif>
                        <span class="{{ $iconChip }} {{ $currentRoute === 'hr.perjalanan-dinas.index' || str_starts_with($currentRoute, 'hr.perjalanan-dinas.') ? 'bg-white/20' : 'bg-white/10 group-hover:bg-white/20' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </span>
                        <span class="truncate flex-1">Perjalanan Dinas</span>
                        @if($menuOff)<i class="fa-solid fa-lock text-[10px] opacity-70 ml-1" aria-hidden="true"></i>@endif
                        @if($pendingPerjalananDinas > 0)
                            <span class="flex-shrink-0 bg-[#ec1d1d] text-white text-[9px] font-bold rounded-full min-w-[18px] h-[18px] flex items-center justify-center px-1 shadow-lg animate-pulse">
                                {{ $pendingPerjalananDinas > 99 ? '99+' : $pendingPerjalananDinas }}
                            </span>
                        @endif
                    </a>

                    @php $menuOff = Auth::user()->isMenuDisabled('perizinan'); @endphp
                    <a href="{{ route('hr.perizinan.index') }}"
                        class="{{ $menuBase }} {{ $currentRoute === 'hr.perizinan.index' || str_starts_with($currentRoute, 'hr.perizinan.') ? $menuActive : $menuIdle }} {{ $menuOff ? 'menu-restricted opacity-50 saturate-50 cursor-not-allowed' : '' }}" @if($menuOff) title="Menu dinonaktifkan oleh Super Administrator — silakan hubungi Superadmin" @endif>
                        <span class="{{ $iconChip }} {{ $currentRoute === 'hr.perizinan.index' || str_starts_with($currentRoute, 'hr.perizinan.') ? 'bg-white/20' : 'bg-white/10 group-hover:bg-white/20' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 9h-4a1 1 0 01-1-1V4" />
                            </svg>
                        </span>
                        <span class="truncate flex-1">Perizinan Karyawan</span>
                        @if($menuOff)<i class="fa-solid fa-lock text-[10px] opacity-70 ml-1" aria-hidden="true"></i>@endif
                        @if($pendingPerizinan > 0)
                            <span class="flex-shrink-0 bg-[#ec1d1d] text-white text-[9px] font-bold rounded-full min-w-[18px] h-[18px] flex items-center justify-center px-1 shadow-lg animate-pulse">
                                {{ $pendingPerizinan > 99 ? '99+' : $pendingPerizinan }}
                            </span>
                        @endif
                    </a>

                    <!-- Menu Lainnya -->
                    <div class="pt-3 mt-3 border-t border-white/10">
                        <h4 class="text-white/40 text-[10px] font-bold uppercase tracking-widest px-3 py-2">
                            Lainnya
                        </h4>
                    </div>

                    <!-- Kepala Suku -->
                    <a href="https://pamersuku.read1kpmseikhlasnya.com/login"
                        class="{{ $menuBase }} {{ $menuIdle }}">
                        <span class="{{ $iconChip }} bg-white/10 group-hover:bg-white/20">
                            <i class="fa-solid fa-people-group text-sm"></i>
                        </span>
                        <span class="truncate">Pamer Suku</span>
                    </a>

                    <!-- 7SPS -->
                    @php $menuOff = Auth::user()->isMenuDisabled('sunnah'); @endphp
                    <a href="{{ route('hr.sunnah.index') }}"
                        class="{{ $menuBase }} {{ $currentRoute === 'hr.sunnah.index' || str_starts_with($currentRoute, 'hr.sunnah.') ? $menuActive : $menuIdle }} {{ $menuOff ? 'menu-restricted opacity-50 saturate-50 cursor-not-allowed' : '' }}" @if($menuOff) title="Menu dinonaktifkan oleh Super Administrator — silakan hubungi Superadmin" @endif>
                        <span class="{{ $iconChip }} {{ $currentRoute === 'hr.sunnah.index' || str_starts_with($currentRoute, 'hr.sunnah.') ? 'bg-white/20' : 'bg-white/10 group-hover:bg-white/20' }}">
                            <i class="fa-solid fa-kaaba text-sm"></i>
                        </span>
                        <span class="truncate">7SPS</span>
                        @if($menuOff)<i class="fa-solid fa-lock text-[10px] opacity-70 ml-1" aria-hidden="true"></i>@endif
                    </a>

                    <!-- English Today -->
                    <a href="https://englishtoday.read1kpmseikhlasnya.com/"
                        class="{{ $menuBase }} {{ $menuIdle }}">
                        <span class="{{ $iconChip }} bg-white/10 group-hover:bg-white/20">
                            <i class="fa-solid fa-language text-sm"></i>
                        </span>
                        <span class="truncate">English Today</span>
                    </a>

                    <!-- FHL -->
                    @php $menuOff = Auth::user()->isMenuDisabled('fhl'); @endphp
                    <a href="{{ route('hr.fhl.index') }}"
                        class="{{ $menuBase }} {{ $currentRoute === 'hr.fhl.index' || str_starts_with($currentRoute, 'hr.fhl.') ? $menuActive : $menuIdle }} {{ $menuOff ? 'menu-restricted opacity-50 saturate-50 cursor-not-allowed' : '' }}" @if($menuOff) title="Menu dinonaktifkan oleh Super Administrator — silakan hubungi Superadmin" @endif>
                        <span class="{{ $iconChip }} {{ $currentRoute === 'hr.fhl.index' || str_starts_with($currentRoute, 'hr.fhl.') ? 'bg-white/20' : 'bg-white/10 group-hover:bg-white/20' }}">
                            <i class="fa-solid fa-heart text-sm"></i>
                        </span>
                        <span class="truncate">FHL</span>
                        @if($menuOff)<i class="fa-solid fa-lock text-[10px] opacity-70 ml-1" aria-hidden="true"></i>@endif
                    </a>

                    <!-- Khataman -->
                    @php $menuOff = Auth::user()->isMenuDisabled('khataman'); @endphp
                    <a href="{{ route('hr.khataman.index') }}"
                        class="{{ $menuBase }} {{ $currentRoute === 'hr.khataman.index' || str_starts_with($currentRoute, 'hr.khataman.') ? $menuActive : $menuIdle }} {{ $menuOff ? 'menu-restricted opacity-50 saturate-50 cursor-not-allowed' : '' }}" @if($menuOff) title="Menu dinonaktifkan oleh Super Administrator — silakan hubungi Superadmin" @endif>
                        <span class="{{ $iconChip }} {{ $currentRoute === 'hr.khataman.index' || str_starts_with($currentRoute, 'hr.khataman.') ? 'bg-white/20' : 'bg-white/10 group-hover:bg-white/20' }}">
                            <i class="fa-solid fa-book-quran text-sm"></i>
                        </span>
                        <span class="truncate">Khataman</span>
                        @if($menuOff)<i class="fa-solid fa-lock text-[10px] opacity-70 ml-1" aria-hidden="true"></i>@endif
                    </a>
                </nav>
            @else
                <!-- ============================================== -->
                <!-- KARYAWAN MENUS -->
                <!-- ============================================== -->
                <h3 class="text-[#00a2e9]/70 text-[10px] font-bold uppercase tracking-widest px-3 mb-2">Menu Karyawan</h3>
                <nav class="space-y-1">
                    <!-- Karyawan Dashboard -->
                    <a href="{{ route('karyawan.dashboard') }}"
                        class="{{ $menuBase }} {{ $currentRoute === 'karyawan.dashboard' ? $menuActive : $menuIdle }}">
                        <span class="{{ $iconChip }} {{ $currentRoute === 'karyawan.dashboard' ? 'bg-white/20' : 'bg-white/10 group-hover:bg-white/20' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
                            </svg>
                        </span>
                        <span class="truncate">Dashboard</span>
                    </a>

                    <!-- Absensi Karyawan -->
                    @php $menuOff = Auth::user()->isMenuDisabled('absensi'); @endphp
                    <a href="{{ route('karyawan.absensi') }}"
                        class="{{ $menuBase }} {{ $currentRoute === 'karyawan.absensi' ? $menuActive : $menuIdle }} {{ $menuOff ? 'menu-restricted opacity-50 saturate-50 cursor-not-allowed' : '' }}" @if($menuOff) title="Menu dinonaktifkan oleh Super Administrator — silakan hubungi Superadmin" @endif>
                        <span class="{{ $iconChip }} {{ $currentRoute === 'karyawan.absensi' ? 'bg-white/20' : 'bg-white/10 group-hover:bg-white/20' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </span>
                        <span class="truncate">Presensi Kehadiran</span>
                        @if($menuOff)<i class="fa-solid fa-lock text-[10px] opacity-70 ml-1" aria-hidden="true"></i>@endif
                    </a>

                    <!-- CUTI -->
                    @php $menuOff = Auth::user()->isMenuDisabled('cuti'); @endphp
                    <a href="{{ route('karyawan.cuti.dashboard') }}"
                        class="{{ $menuBase }} {{ $currentRoute === 'karyawan.cuti.dashboard' || $currentRoute === 'karyawan.cuti.create' ? $menuActive : $menuIdle }} {{ $menuOff ? 'menu-restricted opacity-50 saturate-50 cursor-not-allowed' : '' }}" @if($menuOff) title="Menu dinonaktifkan oleh Super Administrator — silakan hubungi Superadmin" @endif>
                        <span class="{{ $iconChip }} {{ $currentRoute === 'karyawan.cuti.dashboard' || $currentRoute === 'karyawan.cuti.create' ? 'bg-white/20' : 'bg-white/10 group-hover:bg-white/20' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </span>
                        <span class="truncate">Cuti</span>
                        @if($menuOff)<i class="fa-solid fa-lock text-[10px] opacity-70 ml-1" aria-hidden="true"></i>@endif
                    </a>

                    <!-- Perjalanan Dinas -->
                    @php $menuOff = Auth::user()->isMenuDisabled('perjalanan_dinas'); @endphp
                    <a href="{{ route('karyawan.perjalanan-dinas.index') }}"
                        class="{{ $menuBase }} {{ $currentRoute === 'karyawan.perjalanan-dinas.index' || str_starts_with($currentRoute, 'karyawan.perjalanan-dinas.') ? $menuActive : $menuIdle }} {{ $menuOff ? 'menu-restricted opacity-50 saturate-50 cursor-not-allowed' : '' }}" @if($menuOff) title="Menu dinonaktifkan oleh Super Administrator — silakan hubungi Superadmin" @endif>
                        <span class="{{ $iconChip }} {{ $currentRoute === 'karyawan.perjalanan-dinas.index' || str_starts_with($currentRoute, 'karyawan.perjalanan-dinas.') ? 'bg-white/20' : 'bg-white/10 group-hover:bg-white/20' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </span>
                        <span class="truncate">Perjalanan Dinas</span>
                        @if($menuOff)<i class="fa-solid fa-lock text-[10px] opacity-70 ml-1" aria-hidden="true"></i>@endif
                    </a>

                    <!-- Menu Lainnya -->
                    <div class="pt-3 mt-3 border-t border-white/10">
                        <h4 class="text-white/40 text-[10px] font-bold uppercase tracking-widest px-3 py-2">
                            Lainnya
                        </h4>
                    </div>

                    <!-- Kepala Suku -->
                    <a href="https://pamersuku.read1kpmseikhlasnya.com/game"
                        class="{{ $menuBase }} {{ $menuIdle }}">
                        <span class="{{ $iconChip }} bg-white/10 group-hover:bg-white/20">
                            <i class="fa-solid fa-people-group text-sm"></i>
                        </span>
                        <span class="truncate">Pamer Suku</span>
                    </a>

                    <!-- 7SPS -->
                    @php $menuOff = Auth::user()->isMenuDisabled('sunnah'); @endphp
                    <a href="{{ route('karyawan.sunnah.dashboard') }}"
                        class="{{ $menuBase }} {{ $currentRoute === 'karyawan.sunnah.dashboard' ? $menuActive : $menuIdle }} {{ $menuOff ? 'menu-restricted opacity-50 saturate-50 cursor-not-allowed' : '' }}" @if($menuOff) title="Menu dinonaktifkan oleh Super Administrator — silakan hubungi Superadmin" @endif>
                        <span class="{{ $iconChip }} {{ $currentRoute === 'karyawan.sunnah.dashboard' ? 'bg-white/20' : 'bg-white/10 group-hover:bg-white/20' }}">
                            <i class="fa-solid fa-kaaba text-sm"></i>
                        </span>
                        <span class="truncate">7SPS</span>
                        @if($menuOff)<i class="fa-solid fa-lock text-[10px] opacity-70 ml-1" aria-hidden="true"></i>@endif
                    </a>

                    <!-- English Today -->
                    <a href="https://englishtoday.read1kpmseikhlasnya.com/"
                        class="{{ $menuBase }} {{ $menuIdle }}">
                        <span class="{{ $iconChip }} bg-white/10 group-hover:bg-white/20">
                            <i class="fa-solid fa-language text-sm"></i>
                        </span>
                        <span class="truncate">English Today</span>
                    </a>

                    <!-- FHL -->
                    @php $menuOff = Auth::user()->isMenuDisabled('fhl'); @endphp
                    <a href="{{ route('karyawan.fhl.dashboard') }}"
                        class="{{ $menuBase }} {{ $currentRoute === 'karyawan.fhl.dashboard' ? $menuActive : $menuIdle }} {{ $menuOff ? 'menu-restricted opacity-50 saturate-50 cursor-not-allowed' : '' }}" @if($menuOff) title="Menu dinonaktifkan oleh Super Administrator — silakan hubungi Superadmin" @endif>
                        <span class="{{ $iconChip }} {{ $currentRoute === 'karyawan.fhl.dashboard' ? 'bg-white/20' : 'bg-white/10 group-hover:bg-white/20' }}">
                            <i class="fa-solid fa-heart text-sm"></i>
                        </span>
                        <span class="truncate">FHL</span>
                        @if($menuOff)<i class="fa-solid fa-lock text-[10px] opacity-70 ml-1" aria-hidden="true"></i>@endif
                    </a>

                    <!-- Khataman -->
                    @php $menuOff = Auth::user()->isMenuDisabled('khataman'); @endphp
                    <a href="{{ route('karyawan.khataman.dashboard') }}"
                        class="{{ $menuBase }} {{ $currentRoute === 'karyawan.khataman.dashboard' ? $menuActive : $menuIdle }} {{ $menuOff ? 'menu-restricted opacity-50 saturate-50 cursor-not-allowed' : '' }}" @if($menuOff) title="Menu dinonaktifkan oleh Super Administrator — silakan hubungi Superadmin" @endif>
                        <span class="{{ $iconChip }} {{ $currentRoute === 'karyawan.khataman.dashboard' ? 'bg-white/20' : 'bg-white/10 group-hover:bg-white/20' }}">
                            <i class="fa-solid fa-book-quran text-sm"></i>
                        </span>
                        <span class="truncate">Khataman</span>
                        @if($menuOff)<i class="fa-solid fa-lock text-[10px] opacity-70 ml-1" aria-hidden="true"></i>@endif
                    </a>
                </nav>
            @endif

            <!-- Profile & Setting for all roles -->
            <div class="pt-3 mt-3 border-t border-white/10">
                <a href="{{ route('profile.show') }}"
                    class="{{ $menuBase }} {{ $currentRoute === 'profile.show' || $currentRoute === 'profile.edit' ? $menuActive : $menuIdle }}">
                    <span class="{{ $iconChip }} {{ $currentRoute === 'profile.show' || $currentRoute === 'profile.edit' ? 'bg-white/20' : 'bg-white/10 group-hover:bg-white/20' }}">
                        <i class="fa-solid fa-user-gear text-sm"></i>
                    </span>
                    <span class="truncate">Profil Akun</span>
                </a>
            </div>
        </div>
    </div>
</aside>

<!-- Mobile Overlay -->
<div id="sidebar-overlay" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-30 md:hidden hidden transition-opacity duration-300"></div>

<style>
    .sidebar-scroll::-webkit-scrollbar { width: 6px; }
    .sidebar-scroll::-webkit-scrollbar-track { background: transparent; }
    .sidebar-scroll::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.12); border-radius: 9999px; }
    .sidebar-scroll::-webkit-scrollbar-thumb:hover { background: rgba(255,255,255,0.25); }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebar-overlay');
        const toggleBtn = document.getElementById('mobile-menu-toggle');

        function toggleSidebar() {
            sidebar.classList.toggle('-translate-x-full');
            overlay.classList.toggle('hidden');
            document.body.classList.toggle('overflow-hidden');
        }

        if (toggleBtn) {
            toggleBtn.addEventListener('click', toggleSidebar);
        }

        if (overlay) {
            overlay.addEventListener('click', toggleSidebar);
        }

        window.addEventListener('resize', function() {
            if (window.innerWidth >= 768) {
                sidebar.classList.add('md:translate-x-0');
                sidebar.classList.remove('-translate-x-full');
                if (overlay) overlay.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
            } else {
                sidebar.classList.remove('md:translate-x-0');
            }
        });

        const links = sidebar.querySelectorAll('a');
        links.forEach(link => {
            link.addEventListener('click', function() {
                if (window.innerWidth < 768) {
                    sidebar.classList.add('-translate-x-full');
                    if (overlay) overlay.classList.add('hidden');
                    document.body.classList.remove('overflow-hidden');
                }
            });
        });
    });
</script>
