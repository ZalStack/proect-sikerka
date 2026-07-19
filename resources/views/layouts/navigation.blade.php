{{-- views/layouts/navigation.blade.php --}}
@php
    $user = Auth::user();
    $isHr = $user && $user->role === 'hr';
    $dashboardRoute = $isHr ? 'hr.dashboard' : 'karyawan.dashboard';
    $employeeRoute = $isHr ? 'hr.karyawan.index' : '#';

    // Get user photo
    $userPhoto = $user->foto_profil ? Storage::url($user->foto_profil) : null;
    $userInitial = strtoupper(substr($user->nama_depan ?? 'U', 0, 1));
@endphp

<nav class="bg-[#161758] border-b border-[#27438D] fixed top-0 left-0 right-0 z-50">
    <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-14 sm:h-16">
            <div class="flex items-center">
                <!-- Mobile menu button -->
                <button id="mobile-menu-toggle" class="md:hidden text-white hover:text-[#00a2e9] focus:outline-none mr-2 sm:mr-3">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
                <a href="{{ route($dashboardRoute) }}" class="flex items-center space-x-2">
                    <span class="text-[#FCC626] text-base sm:text-xl font-bold font-['Montserrat']">SIKEKAR</span>
                </a>
            </div>

            <div class="flex items-center space-x-2 sm:space-x-4">
                <span class="text-white text-xs sm:text-sm hidden sm:block truncate max-w-[100px] md:max-w-[150px]">
                    {{ $user->nama_lengkap ?? $user->email }}
                </span>
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" class="flex items-center space-x-1 sm:space-x-2 focus:outline-none">
                        <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-[#00a2e9] flex items-center justify-center text-white font-bold overflow-hidden text-xs sm:text-sm">
                            @if($userPhoto)
                                <img src="{{ $userPhoto }}" alt="{{ $user->nama_lengkap }}" class="w-full h-full object-cover">
                            @else
                                {{ $userInitial }}
                            @endif
                        </div>
                        <svg class="w-3 h-3 sm:w-4 sm:h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>

                    <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-44 sm:w-48 bg-white rounded-md shadow-lg py-1 z-50">
                        <a href="{{ route('profile.edit') }}" class="block px-3 sm:px-4 py-2 text-xs sm:text-sm text-[#1B1B1B] hover:bg-[#F5F5F5]">
                            <div class="flex items-center space-x-2">
                                <div class="w-5 h-5 sm:w-6 sm:h-6 rounded-full bg-[#00a2e9] flex items-center justify-center text-white text-[10px] sm:text-xs overflow-hidden">
                                    @if($userPhoto)
                                        <img src="{{ $userPhoto }}" alt="{{ $user->nama_lengkap }}" class="w-full h-full object-cover">
                                    @else
                                        {{ $userInitial }}
                                    @endif
                                </div>
                                <span>Profil</span>
                            </div>
                        </a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="block w-full text-left px-3 sm:px-4 py-2 text-xs sm:text-sm text-[#ec1d1d] hover:bg-[#F5F5F5]">
                                Logout
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</nav>
