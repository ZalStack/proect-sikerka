@php
    $user = Auth::user();
    $isHr = $user && $user->role === 'hr';
    $dashboardRoute = $isHr ? 'hr.dashboard' : 'karyawan.dashboard';
    $employeeRoute = $isHr ? 'hr.karyawan.index' : '#';

    // Get user photo
    $userPhoto = $user->foto_profil ? Storage::url($user->foto_profil) : null;
    $userInitial = strtoupper(substr($user->nama_depan ?? 'U', 0, 1));
@endphp

<nav class="bg-[#161758] border-b border-[#27438D]">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex justify-center items-center">
                <a href="{{ route($dashboardRoute) }}" class="flex items-center space-x-2">
                    <span class="text-[#FCC626] text-xl font-bold font-['Montserrat']">SIPegawai</span>
                </a>
            </div>

            <div class="flex items-center space-x-4">
                <span class="text-white text-sm hidden sm:block">
                    {{ $user->nama_lengkap ?? $user->email }}
                </span>
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" class="flex items-center space-x-2 focus:outline-none">
                        <div class="w-8 h-8 rounded-full bg-[#00a2e9] flex items-center justify-center text-white font-bold overflow-hidden">
                            @if($userPhoto)
                                <img src="{{ $userPhoto }}" alt="{{ $user->nama_lengkap }}" class="w-full h-full object-cover">
                            @else
                                {{ $userInitial }}
                            @endif
                        </div>
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>

                    <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50">
                        <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-[#1B1B1B] hover:bg-[#F5F5F5]">
                            <div class="flex items-center space-x-2">
                                <div class="w-6 h-6 rounded-full bg-[#00a2e9] flex items-center justify-center text-white text-xs overflow-hidden">
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
                            <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-[#ec1d1d] hover:bg-[#F5F5F5]">
                                Logout
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</nav>
