@php
    $user = Auth::user();
    $isHr = $user && $user->posisi === 'hr';
    $dashboardRoute = $isHr ? 'hr.dashboard' : 'karyawan.dashboard';

    $userPhoto = $user->foto_profil ? Storage::url($user->foto_profil) : null;
    $nameParts = explode(' ', $user->nama_lengkap ?? 'U');
    $userInitial = strtoupper(substr($nameParts[0] ?? 'U', 0, 1));
@endphp

<nav class="fixed top-0 left-0 right-0 z-50 bg-[#161758] border-b border-[#27438D]">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex items-center">
                <!-- Toggle Sidebar Button (Mobile) -->
                <button id="sidebarToggle" class="md:hidden text-white hover:text-[#00a2e9] transition-colors duration-200 mr-3">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>

                <a href="{{ route($dashboardRoute) }}" class="flex items-center space-x-2">
                    <span class="text-[#FCC626] text-xl font-bold font-['Montserrat']">SIKEKAR</span>
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

<!-- Script untuk Toggle Sidebar -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('sidebar');
    const toggleBtn = document.getElementById('sidebarToggle');
    const overlay = document.createElement('div');
    overlay.id = 'sidebarOverlay';
    overlay.className = 'fixed inset-0 bg-black/50 z-30 hidden md:hidden';
    document.body.appendChild(overlay);

    function toggleSidebar() {
        const isOpen = sidebar.classList.contains('translate-x-0');
        if (isOpen) {
            sidebar.classList.remove('translate-x-0');
            sidebar.classList.add('-translate-x-full');
            overlay.classList.add('hidden');
            document.body.style.overflow = '';
        } else {
            sidebar.classList.remove('-translate-x-full');
            sidebar.classList.add('translate-x-0');
            overlay.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }
    }

    if (toggleBtn) {
        toggleBtn.addEventListener('click', toggleSidebar);
    }

    overlay.addEventListener('click', toggleSidebar);

    // Close sidebar on escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && sidebar.classList.contains('translate-x-0')) {
            toggleSidebar();
        }
    });

    // Auto close on window resize to desktop
    window.addEventListener('resize', function() {
        if (window.innerWidth >= 768 && sidebar.classList.contains('translate-x-0')) {
            sidebar.classList.remove('translate-x-0');
            sidebar.classList.add('-translate-x-full');
            overlay.classList.add('hidden');
            document.body.style.overflow = '';
        }
    });
});
</script>
