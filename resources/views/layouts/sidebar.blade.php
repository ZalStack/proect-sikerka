@php
    $user = Auth::user();
    $isHr = $user && $user->posisi === 'hr';
    $currentRoute = Route::currentRouteName();

    $userPhoto = $user->foto_profil ? Storage::url($user->foto_profil) : null;
    $userInitial = strtoupper(substr($user->nama_lengkap ?? 'U', 0, 1));
@endphp

<aside class="w-64 bg-[#161758] min-h-screen fixed left-0 top-0 pt-16">
    <div class="p-4">
        <!-- User Profile Card -->
        <div class="bg-[#27438D] rounded-lg p-4 mb-6">
            <div class="flex items-center space-x-3">
                <div class="w-12 h-12 rounded-full bg-[#00a2e9] flex items-center justify-center text-white text-xl font-bold overflow-hidden">
                    @if($userPhoto)
                        <img src="{{ $userPhoto }}" alt="{{ $user->nama_lengkap }}" class="w-full h-full object-cover">
                    @else
                        {{ $userInitial }}
                    @endif
                </div>
                <div class="flex-1">
                    <p class="text-white font-semibold text-sm truncate">{{ $user->nama_lengkap }}</p>
                    <p class="text-[#00a2e9] text-xs">{{ ucfirst($user->posisi) }}</p>
                </div>
            </div>
        </div>

        <div class="mb-8">
            <h3 class="text-[#00a2e9] text-xs font-bold uppercase tracking-wider">Menu</h3>
        </div>
        <nav class="space-y-2">
            @if($isHr)
                <!-- HR Dashboard -->
                <a href="{{ route('hr.dashboard') }}"
                   class="flex items-center space-x-3 px-4 py-3 rounded-lg transition-colors duration-200 {{ $currentRoute === 'hr.dashboard' ? 'bg-[#27438D] text-white' : 'text-[#F5F5F5] hover:bg-[#27438D] hover:text-white' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
                    </svg>
                    <span>Dashboard</span>
                </a>

                <!-- Data Karyawan -->
                <a href="{{ route('hr.karyawan.index') }}"
                   class="flex items-center space-x-3 px-4 py-3 rounded-lg transition-colors duration-200 {{ $currentRoute === 'hr.karyawan.index' || str_starts_with($currentRoute, 'hr.karyawan.') ? 'bg-[#27438D] text-white' : 'text-[#F5F5F5] hover:bg-[#27438D] hover:text-white' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                    <span>Data Karyawan</span>
                </a>

                <!-- Absensi HR -->
                <a href="{{ route('hr.absensi.index') }}"
                   class="flex items-center space-x-3 px-4 py-3 rounded-lg transition-colors duration-200 {{ $currentRoute === 'hr.absensi.index' || str_starts_with($currentRoute, 'hr.absensi.') ? 'bg-[#27438D] text-white' : 'text-[#F5F5F5] hover:bg-[#27438D] hover:text-white' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <span>Absensi</span>
                </a>

                <!-- Pengumuman HR -->
                <a href="{{ route('hr.pengumuman.index') }}"
                   class="flex items-center space-x-3 px-4 py-3 rounded-lg transition-colors duration-200 {{ $currentRoute === 'hr.pengumuman.index' || str_starts_with($currentRoute, 'hr.pengumuman.') ? 'bg-[#27438D] text-white' : 'text-[#F5F5F5] hover:bg-[#27438D] hover:text-white' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                    </svg>
                    <span>Pengumuman</span>
                </a>
            @else
                <!-- Karyawan Dashboard -->
                <a href="{{ route('karyawan.dashboard') }}"
                   class="flex items-center space-x-3 px-4 py-3 rounded-lg transition-colors duration-200 {{ $currentRoute === 'karyawan.dashboard' ? 'bg-[#27438D] text-white' : 'text-[#F5F5F5] hover:bg-[#27438D] hover:text-white' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6z"></path>
                    </svg>
                    <span>Dashboard</span>
                </a>

                <!-- Absensi Karyawan -->
                <a href="{{ route('karyawan.absensi') }}"
                   class="flex items-center space-x-3 px-4 py-3 rounded-lg transition-colors duration-200 {{ $currentRoute === 'karyawan.absensi' ? 'bg-[#27438D] text-white' : 'text-[#F5F5F5] hover:bg-[#27438D] hover:text-white' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <span>Absensi</span>
                </a>
            @endif

            <!-- Profile (sama untuk semua) -->
            <a href="{{ route('profile.edit') }}"
               class="flex items-center space-x-3 px-4 py-3 rounded-lg transition-colors duration-200 {{ $currentRoute === 'profile.edit' ? 'bg-[#27438D] text-white' : 'text-[#F5F5F5] hover:bg-[#27438D] hover:text-white' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
                <span>Profile</span>
            </a>
        </nav>
    </div>
</aside>
