{{-- views/layouts/navigation.blade.php --}}
@php
    $user = Auth::user();
    $isHr = $user && $user->posisi === 'hr';
    $dashboardRoute = $isHr ? 'hr.dashboard' : 'karyawan.dashboard';
    $employeeRoute = $isHr ? 'hr.karyawan.index' : '#';

    // Get user photo
    $userPhoto = $user->foto_profil ? Storage::url($user->foto_profil) : null;
    $userInitial = strtoupper(substr($user->nama_lengkap ?? 'U', 0, 1));
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

                {{-- ==========================================================
                     NOTIFIKASI (bell icon + dropdown)
                     Berlaku untuk KEDUA role (HR & karyawan). Data notifikasi
                     dirakit di backend (NotificationService) dari data yang
                     sudah ada, tanpa tabel/migration baru.
                ========================================================== --}}
                <div class="relative"
                     x-data="{
                        open: false,
                        loading: true,
                        items: [],
                        unreadCount: 0,
                        icons: { pengumuman: '📢', cuti: '🗓️', dinas: '🧳', sunnah: '🌙', absensi: '⚠️', profile: '👤', fhl: '🕌', khataman: '📖' },
                        colors: {
                            blue: 'bg-blue-100 text-blue-700',
                            amber: 'bg-amber-100 text-amber-700',
                            violet: 'bg-violet-100 text-violet-700',
                            emerald: 'bg-emerald-100 text-emerald-700',
                            rose: 'bg-rose-100 text-rose-700',
                            sky: 'bg-sky-100 text-sky-700',
                            teal: 'bg-teal-100 text-teal-700',
                            indigo: 'bg-indigo-100 text-indigo-700',
                            cyan: 'bg-cyan-100 text-cyan-700',
                        },
                        notifIcon(type) { return this.icons[type] || '🔔'; },
                        notifColorClass(color) { return this.colors[color] || 'bg-gray-100 text-gray-700'; },
                        async fetchLatest() {
                            try {
                                const res = await fetch('{{ route('notifications.latest') }}', {
                                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                                });
                                if (!res.ok) return;
                                const data = await res.json();
                                this.items = data.items || [];
                                this.unreadCount = data.unread_count || 0;
                            } catch (e) {
                                // Diam saja kalau gagal fetch, badge tetap seperti sebelumnya
                            } finally {
                                this.loading = false;
                            }
                        },
                        async markRead() {
                            if (this.unreadCount === 0) return;
                            this.unreadCount = 0;
                            this.items = this.items.map(i => ({ ...i, is_new: false }));
                            try {
                                await fetch('{{ route('notifications.mark-read') }}', {
                                    method: 'POST',
                                    headers: {
                                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                                        'X-Requested-With': 'XMLHttpRequest',
                                        'Accept': 'application/json',
                                    },
                                });
                            } catch (e) {
                                // Tidak fatal -- badge akan sinkron lagi di polling berikutnya
                            }
                        },
                        toggle() {
                            this.open = !this.open;
                            if (this.open) { this.markRead(); }
                        },
                     }"
                     x-init="fetchLatest(); setInterval(() => fetchLatest(), 60000)"
                     @keydown.escape.window="open = false">

                    <button @click="toggle()" type="button"
                        class="relative text-white hover:text-[#00a2e9] focus:outline-none p-1.5 sm:p-2 rounded-full hover:bg-white/10 transition"
                        aria-label="Notifikasi">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="w-5 h-5 sm:w-6 sm:h-6">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                        </svg>
                        <span x-show="unreadCount > 0" x-text="unreadCount > 9 ? '9+' : unreadCount"
                            class="absolute -top-0.5 -right-0.5 bg-[#ec1d1d] text-white text-[9px] sm:text-[10px] font-bold rounded-full h-4 w-4 sm:h-5 sm:w-5 flex items-center justify-center leading-none"
                            style="display: none;"></span>
                    </button>

                    <div x-show="open" @click.away="open = false" x-transition
                        class="fixed sm:absolute left-2 right-2 sm:left-auto sm:right-0 top-14 sm:top-auto sm:mt-2 w-auto sm:w-96 max-w-full bg-white rounded-lg shadow-xl z-50 max-h-[80vh] sm:max-h-[28rem] flex flex-col overflow-hidden"
                        style="display: none;">
                        <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between bg-[#F5F5F5] flex-shrink-0">
                            <h3 class="text-sm font-semibold text-[#1B1B1B]">Notifikasi</h3>
                            <span x-show="unreadCount > 0" x-text="unreadCount + ' baru'"
                                class="text-[10px] sm:text-xs text-[#00a2e9] font-medium" style="display: none;"></span>
                        </div>

                        <div class="overflow-y-auto flex-1">
                            <template x-if="loading">
                                <div class="p-6 text-center text-xs text-gray-400">Memuat notifikasi...</div>
                            </template>
                            <template x-if="!loading && items.length === 0">
                                <div class="p-6 text-center text-xs text-gray-400">Belum ada notifikasi</div>
                            </template>
                            <template x-for="item in items" :key="item.id">
                                <a :href="item.url"
                                    class="flex items-start gap-3 px-4 py-3 border-b border-gray-50 hover:bg-[#F5F5F5] transition"
                                    :class="item.is_new ? 'bg-[#E9F5FC]' : ''">
                                    <div class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center text-sm"
                                        :class="notifColorClass(item.color)" x-text="notifIcon(item.type)"></div>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-xs sm:text-sm font-semibold text-[#1B1B1B] truncate" x-text="item.title"></p>
                                        <p class="text-[11px] sm:text-xs text-gray-500 line-clamp-2" x-text="item.message"></p>
                                        <p class="text-[10px] text-gray-400 mt-0.5" x-text="item.time_ago"></p>
                                    </div>
                                    <span x-show="item.is_new" class="flex-shrink-0 w-2 h-2 rounded-full bg-[#00a2e9] mt-1.5" style="display: none;"></span>
                                </a>
                            </template>
                        </div>

                        <a href="{{ route('notifications.index') }}"
                            class="block text-center text-xs sm:text-sm font-medium text-[#00a2e9] py-2.5 border-t border-gray-100 hover:bg-[#F5F5F5] flex-shrink-0">
                            Lihat Semua Notifikasi
                        </a>
                    </div>
                </div>

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
                        <a href="{{ route('profile.show') }}" class="block px-3 sm:px-4 py-2 text-xs sm:text-sm text-[#1B1B1B] hover:bg-[#F5F5F5]">
                            <div class="flex items-center space-x-2">
                                <div class="w-5 h-5 sm:w-6 sm:h-6 rounded-full bg-[#00a2e9] flex items-center justify-center text-white text-[10px] sm:text-xs overflow-hidden">
                                    @if($userPhoto)
                                        <img src="{{ $userPhoto }}" alt="{{ $user->nama_lengkap }}" class="w-full h-full object-cover">
                                    @else
                                        {{ $userInitial }}
                                    @endif
                                </div>
                                <span>Profile</span>
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
