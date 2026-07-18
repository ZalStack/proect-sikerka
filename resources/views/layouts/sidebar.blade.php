@php
    $user = Auth::user();
    $isHr = $user && $user->posisi === 'hr';
    $currentRoute = Route::currentRouteName();

    $userPhoto = $user->foto_profil ? Storage::url($user->foto_profil) : null;
    // Ambil inisial dari nama lengkap
    $nameParts = explode(' ', $user->nama_lengkap ?? 'U');
    $userInitial = strtoupper(substr($nameParts[0] ?? 'U', 0, 1));
    // Nama singkat untuk display di sidebar (max 15 karakter)
    $shortName =
        strlen($user->nama_lengkap ?? '') > 15
            ? substr($user->nama_lengkap ?? '', 0, 15) . '...'
            : $user->nama_lengkap ?? 'User';
@endphp

<aside id="sidebar"
    class="fixed inset-y-0 left-0 z-40 w-64 bg-[#161758] transform transition-transform duration-300 ease-in-out -translate-x-full md:translate-x-0">
    <div class="h-full flex flex-col pt-16">
        <div class="p-4">
            <!-- User Profile Card -->
            <div class="bg-[#27438D] rounded-lg p-4 mb-6">
                <div class="flex items-center space-x-3">
                    <div
                        class="w-12 h-12 rounded-full bg-[#00a2e9] flex items-center justify-center text-white text-xl font-bold overflow-hidden flex-shrink-0">
                        @if ($userPhoto)
                            <img src="{{ $userPhoto }}" alt="{{ $user->nama_lengkap }}"
                                class="w-full h-full object-cover">
                        @else
                            {{ $userInitial }}
                        @endif
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-white font-semibold text-sm truncate" title="{{ $user->nama_lengkap }}">
                            {{ $shortName }}
                        </p>
                        <p class="text-[#00a2e9] text-xs">{{ ucfirst($user->posisi) }}</p>
                    </div>
                </div>
            </div>

            <div class="mb-8">
                <h3 class="text-[#00a2e9] text-xs font-bold uppercase tracking-wider">Menu</h3>
            </div>
            <nav class="space-y-1">
                @if ($isHr)
                    <!-- HR Dashboard -->
                    <a href="{{ route('hr.dashboard') }}"
                        class="flex items-center space-x-3 px-4 py-3 rounded-lg transition-colors duration-200 {{ $currentRoute === 'hr.dashboard' ? 'bg-[#27438D] text-white' : 'text-[#F5F5F5] hover:bg-[#27438D] hover:text-white' }}">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
                        </svg>
                        <span class="truncate">Dashboard</span>
                    </a>

                    <!-- Data Karyawan -->
                    <a href="{{ route('hr.karyawan.index') }}"
                        class="flex items-center space-x-3 px-4 py-3 rounded-lg transition-colors duration-200 {{ $currentRoute === 'hr.karyawan.index' || str_starts_with($currentRoute, 'hr.karyawan.') ? 'bg-[#27438D] text-white' : 'text-[#F5F5F5] hover:bg-[#27438D] hover:text-white' }}">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z">
                            </path>
                        </svg>
                        <span class="truncate">Data Karyawan</span>
                    </a>

                    <!-- Absensi HR -->
                    <a href="{{ route('hr.absensi.index') }}"
                        class="flex items-center space-x-3 px-4 py-3 rounded-lg transition-colors duration-200 {{ $currentRoute === 'hr.absensi.index' || str_starts_with($currentRoute, 'hr.absensi.') ? 'bg-[#27438D] text-white' : 'text-[#F5F5F5] hover:bg-[#27438D] hover:text-white' }}">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                            </path>
                        </svg>
                        <span class="truncate">Absensi</span>
                    </a>

                    <!-- Pengumuman HR -->
                    <a href="{{ route('hr.pengumuman.index') }}"
                        class="flex items-center space-x-3 px-4 py-3 rounded-lg transition-colors duration-200 {{ $currentRoute === 'hr.pengumuman.index' || str_starts_with($currentRoute, 'hr.pengumuman.') ? 'bg-[#27438D] text-white' : 'text-[#F5F5F5] hover:bg-[#27438D] hover:text-white' }}">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10">
                            </path>
                        </svg>
                        <span class="truncate">Pengumuman</span>
                    </a>

                    <!-- CUTI  -->
                    <a href="{{ route('hr.cuti.index') }}"
                        class="flex items-center space-x-3 px-4 py-3 rounded-lg transition-colors duration-200 {{ $currentRoute === 'hr.cuti.index' || str_starts_with($currentRoute, 'hr.cuti.') ? 'bg-[#27438D] text-white' : 'text-[#F5F5F5] hover:bg-[#27438D] hover:text-white' }}">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                            </path>
                        </svg>
                        <span class="truncate">Cuti</span>
                    </a>

                    <!-- Menu Lainnya -->
                    <div class="pt-2 mt-2 border-t border-[#27438D]">
                        <h4 class="text-[#00a2e9] text-xs font-bold uppercase tracking-wider px-4 py-2">Lainnya</h4>
                    </div>

                    <!-- Kepala Suku -->
                    <a href="https://pamersuku.read1kpmseikhlasnya.com/login"
                        class="flex items-center space-x-3 px-4 py-3 rounded-lg transition-colors duration-200 text-[#F5F5F5] hover:bg-[#27438D] hover:text-white">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z">
                            </path>
                        </svg>
                        <span class="truncate">Pamer Suku</span>
                    </a>

                    <!-- 7SPS -->
                    <a href="{{ route('hr.sunnah.index') }}"
                        class="flex items-center space-x-3 px-4 py-3 rounded-lg transition-colors duration-200 text-[#F5F5F5] hover:bg-[#27438D] hover:text-white">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span class="truncate">7SPS</span>
                    </a>

                    <!-- English Today -->
                    <a href="https://englishtoday.read1kpmseikhlasnya.com/" {{-- onclick="event.preventDefault(); alert('Menu English Today saat ini masih dalam tahap pengembangan dan akan segera tersedia.');" --}}
                        class="flex items-center space-x-3 px-4 py-3 rounded-lg transition-colors duration-200 text-[#F5F5F5] hover:bg-[#27438D] hover:text-white">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129">
                            </path>
                        </svg>
                        <span class="truncate">English Today</span>
                    </a>

                    <!-- FHL -->
                    <a href="{{ route('hr.fhl.index') }}"
                        class="flex items-center space-x-3 px-4 py-3 rounded-lg transition-colors duration-200 text-[#F5F5F5] hover:bg-[#27438D] hover:text-white">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z">
                            </path>
                        </svg>
                        <span class="truncate">FHL</span>
                    </a>
                @else
                    <!-- Karyawan Dashboard -->
                    <a href="{{ route('karyawan.dashboard') }}"
                        class="flex items-center space-x-3 px-4 py-3 rounded-lg transition-colors duration-200 {{ $currentRoute === 'karyawan.dashboard' ? 'bg-[#27438D] text-white' : 'text-[#F5F5F5] hover:bg-[#27438D] hover:text-white' }}">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6z"></path>
                        </svg>
                        <span class="truncate">Dashboard</span>
                    </a>

                    <!-- Absensi Karyawan -->
                    <a href="#"
                        onclick="event.preventDefault(); alert('Menu Pamer Suku saat ini masih dalam tahap pengembangan dan akan segera tersedia.');"
                        class="flex items-center space-x-3 px-4 py-3 rounded-lg transition-colors duration-200 {{ $currentRoute === 'karyawan.absensi' ? 'bg-[#27438D] text-white' : 'text-[#F5F5F5] hover:bg-[#27438D] hover:text-white' }}">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                            </path>
                        </svg>
                        <span class="truncate">Absensi</span>
                    </a>

                    <!-- CUTI  -->
                    <a href="{{ route('karyawan.cuti.dashboard') }}"
                        class="flex items-center space-x-3 px-4 py-3 rounded-lg transition-colors duration-200 {{ $currentRoute === 'karyawan.cuti.dashboard' || $currentRoute === 'karyawan.cuti.create' ? 'bg-[#27438D] text-white' : 'text-[#F5F5F5] hover:bg-[#27438D] hover:text-white' }}">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                            </path>
                        </svg>
                        <span class="truncate">Cuti</span>
                    </a>

                    <!-- Menu Lainnya -->
                    <div class="pt-2 mt-2 border-t border-[#27438D]">
                        <h4 class="text-[#00a2e9] text-xs font-bold uppercase tracking-wider px-4 py-2">Lainnya</h4>
                    </div>

                    <!-- Kepala Suku -->
                    <a href="https://pamersuku.read1kpmseikhlasnya.com/" {{-- onclick="event.preventDefault(); alert('Menu Pamer Suku saat ini masih dalam tahap pengembangan dan akan segera tersedia.');" --}}
                        class="flex items-center space-x-3 px-4 py-3 rounded-lg transition-colors duration-200 text-[#F5F5F5] hover:bg-[#27438D] hover:text-white">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z">
                            </path>
                        </svg>
                        <span class="truncate">Pamer Suku</span>
                    </a>

                    <!-- 7SPS -->
                    <a href="{{ route('karyawan.sunnah.dashboard') }}"
                        class="flex items-center space-x-3 px-4 py-3 rounded-lg transition-colors duration-200 text-[#F5F5F5] hover:bg-[#27438D] hover:text-white">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span class="truncate">7SPS</span>
                    </a>

                    <!-- English Today -->
                    <a href="https://englishtoday.read1kpmseikhlasnya.com/" {{-- onclick="event.preventDefault(); alert('Menu English Today saat ini masih dalam tahap pengembangan dan akan segera tersedia.');" --}}
                        class="flex items-center space-x-3 px-4 py-3 rounded-lg transition-colors duration-200 text-[#F5F5F5] hover:bg-[#27438D] hover:text-white">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129">
                            </path>
                        </svg>
                        <span class="truncate">English Today</span>
                    </a>

                    <!-- FHL -->
                    <a href="{{ route('karyawan.fhl.dashboard') }}"
                        class="flex items-center space-x-3 px-4 py-3 rounded-lg transition-colors duration-200 text-[#F5F5F5] hover:bg-[#27438D] hover:text-white">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z">
                            </path>
                        </svg>
                        <span class="truncate">FHL</span>
                    </a>
                @endif

                <!-- Profile (sama untuk semua) -->
                <a href="{{ route('profile.edit') }}"
                    class="flex items-center space-x-3 px-4 py-3 rounded-lg transition-colors duration-200 {{ $currentRoute === 'profile.edit' ? 'bg-[#27438D] text-white' : 'text-[#F5F5F5] hover:bg-[#27438D] hover:text-white' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                    <span class="truncate">Profile</span>
                </a>
            </nav>
        </div>
    </div>
</aside>
