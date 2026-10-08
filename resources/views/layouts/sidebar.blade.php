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

    // Hitung jumlah pengajuan pending untuk notifikasi HR & Superadmin
    $pendingCuti = 0;
    $pendingPerjalananDinas = 0;
    $pendingPerizinan = 0;
    if ($isHr || $isSuperAdmin) {
        $pendingCuti = \App\Models\Cuti::where('status', 'pending')->count();
        $pendingPerjalananDinas = \App\Models\PerjalananDinas::where('status', 'pending')->count();
        $pendingPerizinan = \App\Models\Perizinan::where('status', 'pending')->count();
    }
@endphp

<aside id="sidebar"
    class="fixed inset-y-0 left-0 z-40 w-64 bg-gradient-to-b from-[#0F1245] to-[#161758] transform transition-transform duration-300 ease-in-out -translate-x-full md:translate-x-0 overflow-y-auto shadow-2xl md:shadow-xl">
    <div class="h-full flex flex-col pt-14 sm:pt-16">
        <div class="p-3 sm:p-4 pb-20">
            <!-- User Profile Card -->
            <div class="bg-white/10 backdrop-blur-sm rounded-xl p-3 sm:p-4 mb-4 sm:mb-6 border border-white/10 hover:bg-white/15 transition-all duration-300">
                <div class="flex items-center space-x-2 sm:space-x-3">
                    <div
                        class="w-10 h-10 sm:w-12 sm:h-12 rounded-full {{ $isSuperAdmin ? 'bg-gradient-to-br from-[#FCC626] to-[#f0a500] text-[#0F1245]' : 'bg-gradient-to-br from-[#00a2e9] to-[#0077b6] text-white' }} flex items-center justify-center text-base sm:text-xl font-bold overflow-hidden flex-shrink-0 ring-2 ring-white/20 shadow-lg">
                        @if ($userPhoto)
                            <img src="{{ $userPhoto }}" alt="{{ $user->nama_lengkap }}"
                                class="w-full h-full object-cover">
                        @else
                            {{ $userInitial }}
                        @endif
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-white font-semibold text-xs sm:text-sm truncate"
                            title="{{ $user->nama_lengkap }}">
                            {{ $shortName }}
                        </p>
                        @if($isSuperAdmin)
                            <span class="inline-flex items-center gap-1 text-[#FCC626] text-[10px] sm:text-xs font-bold uppercase tracking-wider">
                                <i class="fa-solid fa-crown text-[9px]"></i>
                                Super Admin
                            </span>
                        @elseif($isHr)
                            <p class="text-[#00a2e9] text-[10px] sm:text-xs font-medium">HR Administrator</p>
                        @else
                            <p class="text-[#00a2e9] text-[10px] sm:text-xs font-medium">Karyawan</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- ============================================== -->
            <!-- SUPER ADMIN EXCLUSIVE MENUS -->
            <!-- ============================================== -->
            @if ($isSuperAdmin)
                <div class="mb-2 sm:mb-4">
                    <h3 class="text-[#FCC626] text-[10px] sm:text-[11px] font-bold uppercase tracking-widest px-3 sm:px-4 flex items-center gap-1.5">
                        <i class="fa-solid fa-crown text-[10px]"></i>
                        <span>Super Admin Panel</span>
                    </h3>
                </div>

                <nav class="space-y-1 mb-5">
                    <!-- Dashboard Superadmin -->
                    <a href="{{ route('superadmin.dashboard') }}"
                        class="flex items-center space-x-2 sm:space-x-3 px-3 sm:px-4 py-2.5 sm:py-3 rounded-xl transition-all duration-200 text-xs sm:text-sm {{ $currentRoute === 'superadmin.dashboard' ? 'bg-[#FCC626] text-[#0F1245] font-bold shadow-lg shadow-[#FCC626]/30' : 'text-white/80 hover:bg-white/10 hover:text-white' }}">
                        <i class="fa-solid fa-gauge text-sm flex-shrink-0"></i>
                        <span class="truncate">Dashboard Superadmin</span>
                    </a>

                    <!-- Kontrol Fitur & Maintenance -->
                    <a href="{{ route('superadmin.features.index') }}"
                        class="flex items-center space-x-2 sm:space-x-3 px-3 sm:px-4 py-2.5 sm:py-3 rounded-xl transition-all duration-200 text-xs sm:text-sm {{ $currentRoute === 'superadmin.features.index' ? 'bg-[#00a2e9] text-white font-semibold shadow-lg shadow-[#00a2e9]/30' : 'text-white/80 hover:bg-white/10 hover:text-white' }}">
                        <i class="fa-solid fa-sliders text-sm flex-shrink-0"></i>
                        <span class="truncate">Fitur & Maintenance</span>
                    </a>

                    <!-- Kelola & Koreksi Jam Presensi -->
                    <a href="{{ route('superadmin.absensi.index') }}"
                        class="flex items-center space-x-2 sm:space-x-3 px-3 sm:px-4 py-2.5 sm:py-3 rounded-xl transition-all duration-200 text-xs sm:text-sm {{ $currentRoute === 'superadmin.absensi.index' ? 'bg-[#00a2e9] text-white font-semibold shadow-lg shadow-[#00a2e9]/30' : 'text-white/80 hover:bg-white/10 hover:text-white' }}">
                        <i class="fa-solid fa-clock-rotate-left text-sm flex-shrink-0"></i>
                        <span class="truncate">Koreksi Presensi Karyawan</span>
                    </a>

                    <!-- Manajemen Role Pengguna -->
                    <a href="{{ route('superadmin.karyawan.index') }}"
                        class="flex items-center space-x-2 sm:space-x-3 px-3 sm:px-4 py-2.5 sm:py-3 rounded-xl transition-all duration-200 text-xs sm:text-sm {{ $currentRoute === 'superadmin.karyawan.index' ? 'bg-[#00a2e9] text-white font-semibold shadow-lg shadow-[#00a2e9]/30' : 'text-white/80 hover:bg-white/10 hover:text-white' }}">
                        <i class="fa-solid fa-users-gear text-sm flex-shrink-0"></i>
                        <span class="truncate">Manajemen Role User</span>
                    </a>
                </nav>

            @elseif ($isHr)
                <!-- ============================================== -->
                <!-- HR EXCLUSIVE MENUS -->
                <!-- ============================================== -->
                <div class="mb-3 sm:mb-6">
                    <h3 class="text-[#00a2e9]/70 text-[10px] sm:text-[11px] font-bold uppercase tracking-widest px-3 sm:px-4">Menu Utama HR</h3>
                </div>
                <nav class="space-y-1">
                    <!-- HR Dashboard -->
                    <a href="{{ route('hr.dashboard') }}"
                        class="flex items-center space-x-2 sm:space-x-3 px-3 sm:px-4 py-2.5 sm:py-3 rounded-xl transition-all duration-200 text-xs sm:text-sm {{ $currentRoute === 'hr.dashboard' ? 'bg-[#00a2e9] text-white shadow-lg shadow-[#00a2e9]/30' : 'text-white/70 hover:bg-white/10 hover:text-white' }}">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
                        </svg>
                        <span class="truncate">Dashboard</span>
                    </a>

                    <!-- Data Karyawan -->
                    <a href="{{ route('hr.karyawan.index') }}"
                        class="flex items-center space-x-2 sm:space-x-3 px-3 sm:px-4 py-2.5 sm:py-3 rounded-xl transition-all duration-200 text-xs sm:text-sm {{ $currentRoute === 'hr.karyawan.index' || str_starts_with($currentRoute, 'hr.karyawan.') ? 'bg-[#00a2e9] text-white shadow-lg shadow-[#00a2e9]/30' : 'text-white/70 hover:bg-white/10 hover:text-white' }}">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                        <span class="truncate">Data Karyawan</span>
                    </a>

                    <!-- Absensi HR -->
                    <a href="{{ route('hr.absensi.index') }}"
                        class="flex items-center space-x-2 sm:space-x-3 px-3 sm:px-4 py-2.5 sm:py-3 rounded-xl transition-all duration-200 text-xs sm:text-sm {{ $currentRoute === 'hr.absensi.index' || str_starts_with($currentRoute, 'hr.absensi.') ? 'bg-[#00a2e9] text-white shadow-lg shadow-[#00a2e9]/30' : 'text-white/70 hover:bg-white/10 hover:text-white' }}">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <span class="truncate">Presensi Karyawan</span>
                    </a>

                    <!-- Pengumuman HR -->
                    <a href="{{ route('hr.pengumuman.index') }}"
                        class="flex items-center space-x-2 sm:space-x-3 px-3 sm:px-4 py-2.5 sm:py-3 rounded-xl transition-all duration-200 text-xs sm:text-sm {{ $currentRoute === 'hr.pengumuman.index' || str_starts_with($currentRoute, 'hr.pengumuman.') ? 'bg-[#00a2e9] text-white shadow-lg shadow-[#00a2e9]/30' : 'text-white/70 hover:bg-white/10 hover:text-white' }}">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                        <span class="truncate">Pengumuman</span>
                    </a>

                    <!-- CUTI -->
                    <a href="{{ route('hr.cuti.index') }}"
                        class="flex items-center justify-between px-3 sm:px-4 py-2.5 sm:py-3 rounded-xl transition-all duration-200 text-xs sm:text-sm {{ $currentRoute === 'hr.cuti.index' || str_starts_with($currentRoute, 'hr.cuti.') ? 'bg-[#00a2e9] text-white shadow-lg shadow-[#00a2e9]/30' : 'text-white/70 hover:bg-white/10 hover:text-white' }}">
                        <div class="flex items-center space-x-2 sm:space-x-3 min-w-0">
                            <svg class="w-4 h-4 sm:w-5 sm:h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            <span class="truncate">Cuti</span>
                        </div>
                        @if($pendingCuti > 0)
                            <span class="flex-shrink-0 ml-2 bg-[#ec1d1d] text-white text-[9px] sm:text-[10px] font-bold rounded-full min-w-[18px] h-[18px] flex items-center justify-center px-1 shadow-lg animate-pulse">
                                {{ $pendingCuti > 99 ? '99+' : $pendingCuti }}
                            </span>
                        @endif
                    </a>

                    <!-- Perjalanan Dinas HR -->
                    <a href="{{ route('hr.perjalanan-dinas.index') }}"
                        class="flex items-center justify-between px-3 sm:px-4 py-2.5 sm:py-3 rounded-xl transition-all duration-200 text-xs sm:text-sm {{ $currentRoute === 'hr.perjalanan-dinas.index' || str_starts_with($currentRoute, 'hr.perjalanan-dinas.') ? 'bg-[#00a2e9] text-white shadow-lg shadow-[#00a2e9]/30' : 'text-white/70 hover:bg-white/10 hover:text-white' }}">
                        <div class="flex items-center space-x-2 sm:space-x-3 min-w-0">
                            <svg class="w-4 h-4 sm:w-5 sm:h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <span class="truncate">Perjalanan Dinas</span>
                        </div>
                        @if($pendingPerjalananDinas > 0)
                            <span class="flex-shrink-0 ml-2 bg-[#ec1d1d] text-white text-[9px] sm:text-[10px] font-bold rounded-full min-w-[18px] h-[18px] flex items-center justify-center px-1 shadow-lg animate-pulse">
                                {{ $pendingPerjalananDinas > 99 ? '99+' : $pendingPerjalananDinas }}
                            </span>
                        @endif
                    </a>

                    <a href="{{ route('hr.perizinan.index') }}"
                        class="flex items-center justify-between px-3 sm:px-4 py-2.5 sm:py-3 rounded-xl transition-all duration-200 text-xs sm:text-sm {{ $currentRoute === 'hr.perizinan.index' || str_starts_with($currentRoute, 'hr.perizinan.') ? 'bg-[#00a2e9] text-white shadow-lg shadow-[#00a2e9]/30' : 'text-white/70 hover:bg-white/10 hover:text-white' }}">
                        <div class="flex items-center space-x-2 sm:space-x-3 min-w-0">
                            <svg class="w-4 h-4 sm:w-5 sm:h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 9h-4a1 1 0 01-1-1V4" />
                            </svg>
                            <span class="truncate">Perizinan Karyawan</span>
                        </div>
                        @if($pendingPerizinan > 0)
                            <span class="flex-shrink-0 ml-2 bg-[#ec1d1d] text-white text-[9px] sm:text-[10px] font-bold rounded-full min-w-[18px] h-[18px] flex items-center justify-center px-1 shadow-lg animate-pulse">
                                {{ $pendingPerizinan > 99 ? '99+' : $pendingPerizinan }}
                            </span>
                        @endif
                    </a>

                    <!-- Menu Lainnya -->
                    <div class="pt-3 mt-3 border-t border-white/10">
                        <h4 class="text-white/40 text-[10px] sm:text-[11px] font-bold uppercase tracking-widest px-3 sm:px-4 py-2">
                            Lainnya
                        </h4>
                    </div>

                    <!-- Kepala Suku -->
                    <a href="https://pamersuku.read1kpmseikhlasnya.com/login"
                        class="flex items-center space-x-2 sm:space-x-3 px-3 sm:px-4 py-2.5 sm:py-3 rounded-xl transition-all duration-200 text-xs sm:text-sm text-white/70 hover:bg-white/10 hover:text-white">
                        <i class="fa-solid fa-people-group text-sm flex-shrink-0"></i>
                        <span class="truncate">Pamer Suku</span>
                    </a>

                    <!-- 7SPS -->
                    <a href="{{ route('hr.sunnah.index') }}"
                        class="flex items-center space-x-2 sm:space-x-3 px-3 sm:px-4 py-2.5 sm:py-3 rounded-xl transition-all duration-200 text-xs sm:text-sm {{ $currentRoute === 'hr.sunnah.index' || str_starts_with($currentRoute, 'hr.sunnah.') ? 'bg-[#00a2e9] text-white shadow-lg shadow-[#00a2e9]/30' : 'text-white/70 hover:bg-white/10 hover:text-white' }}">
                        <i class="fa-solid fa-kaaba text-sm flex-shrink-0"></i>
                        <span class="truncate">7SPS</span>
                    </a>

                    <!-- English Today -->
                    <a href="https://englishtoday.read1kpmseikhlasnya.com/"
                        class="flex items-center space-x-2 sm:space-x-3 px-3 sm:px-4 py-2.5 sm:py-3 rounded-xl transition-all duration-200 text-xs sm:text-sm text-white/70 hover:bg-white/10 hover:text-white">
                        <i class="fa-solid fa-language text-sm flex-shrink-0"></i>
                        <span class="truncate">English Today</span>
                    </a>

                    <!-- FHL -->
                    <a href="{{ route('hr.fhl.index') }}"
                        class="flex items-center space-x-2 sm:space-x-3 px-3 sm:px-4 py-2.5 sm:py-3 rounded-xl transition-all duration-200 text-xs sm:text-sm {{ $currentRoute === 'hr.fhl.index' || str_starts_with($currentRoute, 'hr.fhl.') ? 'bg-[#00a2e9] text-white shadow-lg shadow-[#00a2e9]/30' : 'text-white/70 hover:bg-white/10 hover:text-white' }}">
                        <i class="fa-solid fa-heart text-sm flex-shrink-0"></i>
                        <span class="truncate">FHL</span>
                    </a>

                    <!-- Khataman -->
                    <a href="{{ route('hr.khataman.index') }}"
                        class="flex items-center space-x-2 sm:space-x-3 px-3 sm:px-4 py-2.5 sm:py-3 rounded-xl transition-all duration-200 text-xs sm:text-sm {{ $currentRoute === 'hr.khataman.index' || str_starts_with($currentRoute, 'hr.khataman.') ? 'bg-[#00a2e9] text-white shadow-lg shadow-[#00a2e9]/30' : 'text-white/70 hover:bg-white/10 hover:text-white' }}">
                        <i class="fa-solid fa-book-quran text-sm flex-shrink-0"></i>
                        <span class="truncate">Khataman</span>
                    </a>
                </nav>
            @else
                <!-- ============================================== -->
                <!-- KARYAWAN MENUS -->
                <!-- ============================================== -->
                <div class="mb-3 sm:mb-6">
                    <h3 class="text-[#00a2e9]/70 text-[10px] sm:text-[11px] font-bold uppercase tracking-widest px-3 sm:px-4">Menu Karyawan</h3>
                </div>
                <nav class="space-y-1">
                    <!-- Karyawan Dashboard -->
                    <a href="{{ route('karyawan.dashboard') }}"
                        class="flex items-center space-x-2 sm:space-x-3 px-3 sm:px-4 py-2.5 sm:py-3 rounded-xl transition-all duration-200 text-xs sm:text-sm {{ $currentRoute === 'karyawan.dashboard' ? 'bg-[#00a2e9] text-white shadow-lg shadow-[#00a2e9]/30' : 'text-white/70 hover:bg-white/10 hover:text-white' }}">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
                        </svg>
                        <span class="truncate">Dashboard</span>
                    </a>

                    <!-- Absensi Karyawan -->
                    <a href="{{ route('karyawan.absensi') }}"
                        class="flex items-center space-x-2 sm:space-x-3 px-3 sm:px-4 py-2.5 sm:py-3 rounded-xl transition-all duration-200 text-xs sm:text-sm {{ $currentRoute === 'karyawan.absensi' ? 'bg-[#00a2e9] text-white shadow-lg shadow-[#00a2e9]/30' : 'text-white/70 hover:bg-white/10 hover:text-white' }}">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <span class="truncate">Presensi Kehadiran</span>
                    </a>

                    <!-- CUTI -->
                    <a href="{{ route('karyawan.cuti.dashboard') }}"
                        class="flex items-center space-x-2 sm:space-x-3 px-3 sm:px-4 py-2.5 sm:py-3 rounded-xl transition-all duration-200 text-xs sm:text-sm {{ $currentRoute === 'karyawan.cuti.dashboard' || $currentRoute === 'karyawan.cuti.create' ? 'bg-[#00a2e9] text-white shadow-lg shadow-[#00a2e9]/30' : 'text-white/70 hover:bg-white/10 hover:text-white' }}">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        <span class="truncate">Cuti</span>
                    </a>

                    <!-- Perjalanan Dinas -->
                    <a href="{{ route('karyawan.perjalanan-dinas.index') }}"
                        class="flex items-center space-x-2 sm:space-x-3 px-3 sm:px-4 py-2.5 sm:py-3 rounded-xl transition-all duration-200 text-xs sm:text-sm {{ $currentRoute === 'karyawan.perjalanan-dinas.index' || str_starts_with($currentRoute, 'karyawan.perjalanan-dinas.') ? 'bg-[#00a2e9] text-white shadow-lg shadow-[#00a2e9]/30' : 'text-white/70 hover:bg-white/10 hover:text-white' }}">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <span class="truncate">Perjalanan Dinas</span>
                    </a>

                    <!-- Menu Lainnya -->
                    <div class="pt-3 mt-3 border-t border-white/10">
                        <h4 class="text-white/40 text-[10px] sm:text-[11px] font-bold uppercase tracking-widest px-3 sm:px-4 py-2">
                            Lainnya
                        </h4>
                    </div>

                    <!-- Kepala Suku -->
                    <a href="https://pamersuku.read1kpmseikhlasnya.com/game"
                        class="flex items-center space-x-2 sm:space-x-3 px-3 sm:px-4 py-2.5 sm:py-3 rounded-xl transition-all duration-200 text-xs sm:text-sm text-white/70 hover:bg-white/10 hover:text-white">
                        <i class="fa-solid fa-people-group text-sm flex-shrink-0"></i>
                        <span class="truncate">Pamer Suku</span>
                    </a>

                    <!-- 7SPS -->
                    <a href="{{ route('karyawan.sunnah.dashboard') }}"
                        class="flex items-center space-x-2 sm:space-x-3 px-3 sm:px-4 py-2.5 sm:py-3 rounded-xl transition-all duration-200 text-xs sm:text-sm {{ $currentRoute === 'karyawan.sunnah.dashboard' ? 'bg-[#00a2e9] text-white shadow-lg shadow-[#00a2e9]/30' : 'text-white/70 hover:bg-white/10 hover:text-white' }}">
                        <i class="fa-solid fa-kaaba text-sm flex-shrink-0"></i>
                        <span class="truncate">7SPS</span>
                    </a>

                    <!-- English Today -->
                    <a href="https://englishtoday.read1kpmseikhlasnya.com/"
                        class="flex items-center space-x-2 sm:space-x-3 px-3 sm:px-4 py-2.5 sm:py-3 rounded-xl transition-all duration-200 text-xs sm:text-sm text-white/70 hover:bg-white/10 hover:text-white">
                        <i class="fa-solid fa-language text-sm flex-shrink-0"></i>
                        <span class="truncate">English Today</span>
                    </a>

                    <!-- FHL -->
                    <a href="{{ route('karyawan.fhl.dashboard') }}"
                        class="flex items-center space-x-2 sm:space-x-3 px-3 sm:px-4 py-2.5 sm:py-3 rounded-xl transition-all duration-200 text-xs sm:text-sm {{ $currentRoute === 'karyawan.fhl.dashboard' ? 'bg-[#00a2e9] text-white shadow-lg shadow-[#00a2e9]/30' : 'text-white/70 hover:bg-white/10 hover:text-white' }}">
                        <i class="fa-solid fa-heart text-sm flex-shrink-0"></i>
                        <span class="truncate">FHL</span>
                    </a>

                    <!-- Khataman -->
                    <a href="{{ route('karyawan.khataman.dashboard') }}"
                        class="flex items-center space-x-2 sm:space-x-3 px-3 sm:px-4 py-2.5 sm:py-3 rounded-xl transition-all duration-200 text-xs sm:text-sm {{ $currentRoute === 'karyawan.khataman.dashboard' ? 'bg-[#00a2e9] text-white shadow-lg shadow-[#00a2e9]/30' : 'text-white/70 hover:bg-white/10 hover:text-white' }}">
                        <i class="fa-solid fa-book-quran text-sm flex-shrink-0"></i>
                        <span class="truncate">Khataman</span>
                    </a>
                </nav>
            @endif

            <!-- Profile & Setting for all roles -->
            <div class="pt-3 mt-3 border-t border-white/10">
                <a href="{{ route('profile.show') }}"
                    class="flex items-center space-x-2 sm:space-x-3 px-3 sm:px-4 py-2.5 sm:py-3 rounded-xl transition-all duration-200 text-xs sm:text-sm {{ $currentRoute === 'profile.show' || $currentRoute === 'profile.edit' ? 'bg-[#00a2e9] text-white shadow-lg shadow-[#00a2e9]/30' : 'text-white/70 hover:bg-white/10 hover:text-white' }}">
                    <i class="fa-solid fa-user-gear text-sm flex-shrink-0"></i>
                    <span class="truncate">Profil Akun</span>
                </a>
            </div>
        </div>
    </div>
</aside>

<!-- Mobile Overlay -->
<div id="sidebar-overlay" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-30 md:hidden hidden transition-opacity duration-300"></div>

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
