@php
    $user = Auth::user();
    $isHr = $user && $user->posisi === 'hr';
    $dashboardRoute = $isHr ? 'hr.dashboard' : 'karyawan.dashboard';
    $employeeRoute = $isHr ? 'hr.karyawan.index' : '#';

    $userPhoto = $user->foto_profil ? Storage::url($user->foto_profil) : null;
    $userInitial = strtoupper(substr($user->nama_lengkap ?? 'U', 0, 1));
@endphp

<nav class="bg-gradient-to-r from-[#0F1245] via-[#161758] to-[#0F1245] border-b border-white/10 fixed top-0 left-0 right-0 z-50 shadow-xl shadow-[#161758]/20 backdrop-blur-sm">
    <div class="max-w-7xl mx-auto px-2 sm:px-4 lg:px-8">
        <div class="flex justify-between items-center h-12 sm:h-14 md:h-16">
            <!-- Logo & Menu -->
            <div class="flex items-center gap-2 sm:gap-3">
                <!-- Mobile menu button -->
                <button id="mobile-menu-toggle" class="md:hidden text-white/80 hover:text-[#00a2e9] focus:outline-none transition-all duration-200 p-1.5 rounded-lg hover:bg-white/10">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>

                <a href="{{ route($dashboardRoute) }}" class="flex items-center gap-1.5 sm:gap-2 group">
                    <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg bg-gradient-to-br from-[#FCC626] to-[#f0a500] flex items-center justify-center shadow-lg shadow-[#FCC626]/30 group-hover:scale-110 transition-transform duration-200">
                        <span class="text-[#0F1245] text-xs sm:text-sm font-bold font-['Montserrat']">S</span>
                    </div>
                    <span class="text-[#FCC626] text-base sm:text-lg md:text-xl font-bold font-['Montserrat'] tracking-wide group-hover:text-white transition-colors">
                        SIKEKAR
                    </span>
                </a>
            </div>

            <!-- Right Section -->
            <div class="flex items-center gap-1 sm:gap-2 md:gap-3">
                <!-- User Name -->
                <span class="text-white/80 text-xs sm:text-sm hidden sm:block truncate max-w-[100px] md:max-w-[200px] font-medium">
                    {{ $user->nama_lengkap ?? $user->email }}
                </span>

                {{-- ============ NOTIFIKASI ============ --}}
                <div class="relative"
                     x-data="notificationHandler()"
                     x-init="init()"
                     @keydown.escape.window="open = false">

                    <!-- Bell Button -->
                    <button @click="toggle()" type="button"
                        class="relative text-white/80 hover:text-white focus:outline-none p-1.5 sm:p-2 rounded-xl hover:bg-white/10 transition-all duration-200"
                        aria-label="Notifikasi">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="w-4 h-4 sm:w-5 sm:h-5 md:w-6 md:h-6">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                        </svg>

                        <!-- Badge -->
                        <span x-show="unreadCount > 0"
                            x-text="unreadCount > 99 ? '99+' : unreadCount"
                            class="absolute -top-1 -right-1 bg-gradient-to-br from-[#ec1d1d] to-[#b31414] text-white text-[8px] sm:text-[10px] font-bold rounded-full min-w-[16px] sm:min-w-[18px] h-[16px] sm:h-[18px] flex items-center justify-center leading-none px-1 shadow-lg ring-2 ring-[#161758]"
                            style="display: none;"></span>
                    </button>

                    <!-- Dropdown Notifikasi -->
                    <div x-show="open"
                        @click.away="open = false"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 translate-y-1 scale-95"
                        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                        x-transition:leave-end="opacity-0 translate-y-1 scale-95"
                        class="fixed inset-x-2 sm:inset-x-auto sm:right-0 top-12 sm:top-full sm:mt-2.5 w-[calc(100%-16px)] sm:w-[380px] md:w-[420px] bg-white rounded-2xl shadow-2xl z-50 border border-gray-100 overflow-hidden"
                        style="display: none; max-height: calc(100vh - 80px);">

                        <!-- Header -->
                        <div class="px-4 sm:px-5 py-3 sm:py-4 bg-gradient-to-r from-[#161758] to-[#27438D] flex items-center justify-between gap-2">
                            <div class="flex items-center gap-2">
                                <span class="text-base sm:text-lg">🔔</span>
                                <h3 class="text-xs sm:text-sm font-bold text-white">Notifikasi</h3>
                                <span x-show="unreadCount > 0"
                                    x-text="(unreadCount > 99 ? '99+' : unreadCount) + ' baru'"
                                    class="text-[9px] sm:text-[10px] font-semibold bg-white/20 text-white px-2 py-0.5 rounded-full"
                                    style="display: none;"></span>
                            </div>
                            <button type="button" @click="markRead()" x-show="unreadCount > 0"
                                class="text-[9px] sm:text-[10px] font-medium text-white/80 hover:text-white underline underline-offset-2 transition-colors"
                                style="display: none;">
                                Tandai dibaca
                            </button>
                        </div>

                        <!-- Content -->
                        <div class="overflow-y-auto" style="max-height: calc(80vh - 150px);">
                            <!-- Loading -->
                            <template x-if="loading">
                                <div class="p-3 sm:p-4 space-y-2 sm:space-y-3">
                                    <template x-for="n in 3" :key="n">
                                        <div class="flex items-start gap-3 px-2 py-2 animate-pulse">
                                            <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-full bg-gray-200 shrink-0"></div>
                                            <div class="flex-1 space-y-1.5 pt-1">
                                                <div class="h-2.5 bg-gray-200 rounded w-3/4"></div>
                                                <div class="h-2 bg-gray-100 rounded w-full"></div>
                                                <div class="h-2 bg-gray-100 rounded w-1/3"></div>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </template>

                            <!-- Empty -->
                            <template x-if="!loading && items.length === 0">
                                <div class="p-8 sm:p-10 text-center">
                                    <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-full bg-gray-50 flex items-center justify-center text-2xl sm:text-3xl mx-auto mb-3">🔔</div>
                                    <p class="text-xs sm:text-sm text-gray-600 font-semibold">Belum ada notifikasi</p>
                                    <p class="text-[10px] sm:text-xs text-gray-400 mt-1">Notifikasi terbaru akan muncul di sini</p>
                                </div>
                            </template>

                            <!-- Group: Baru -->
                            <template x-if="!loading && itemsNew.length > 0">
                                <div>
                                    <p class="px-4 sm:px-5 pt-3 pb-1 text-[9px] sm:text-[10px] font-bold text-[#00a2e9] uppercase tracking-wider">Baru</p>
                                    <template x-for="item in itemsNew" :key="item.id">
                                        <a :href="item.url"
                                            class="flex items-start gap-2.5 sm:gap-3 px-4 sm:px-5 py-2.5 sm:py-3 border-l-[3px] border-l-[#00a2e9] bg-[#F0F9FF] hover:bg-[#E4F4FE] transition-colors duration-150 cursor-pointer">
                                            <div class="flex-shrink-0 w-8 h-8 sm:w-10 sm:h-10 rounded-full flex items-center justify-center text-sm sm:text-lg shadow-sm"
                                                :class="notifColorClass(item.color)">
                                                <span x-text="notifIcon(item.type)"></span>
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <div class="flex items-start justify-between gap-2">
                                                    <p class="text-[11px] sm:text-sm font-semibold text-gray-800 leading-snug" x-text="item.title"></p>
                                                    <span class="flex-shrink-0 w-1.5 h-1.5 sm:w-2 sm:h-2 rounded-full bg-[#00a2e9] mt-1.5"></span>
                                                </div>
                                                <p class="text-[10px] sm:text-xs text-gray-600 mt-0.5 sm:mt-1 line-clamp-2 leading-relaxed" x-text="item.message"></p>
                                                <p class="text-[9px] sm:text-[11px] text-gray-400 mt-1 sm:mt-1.5 flex items-center gap-1">
                                                    <svg class="w-2.5 h-2.5 sm:w-3 sm:h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                    <span x-text="item.time_ago"></span>
                                                </p>
                                            </div>
                                        </a>
                                    </template>
                                </div>
                            </template>

                            <!-- Group: Sebelumnya -->
                            <template x-if="!loading && itemsOlder.length > 0">
                                <div>
                                    <p class="px-4 sm:px-5 pt-3 pb-1 text-[9px] sm:text-[10px] font-bold text-gray-400 uppercase tracking-wider" x-show="itemsNew.length > 0">Sebelumnya</p>
                                    <template x-for="item in itemsOlder" :key="item.id">
                                        <a :href="item.url"
                                            class="flex items-start gap-2.5 sm:gap-3 px-4 sm:px-5 py-2.5 sm:py-3 border-b border-gray-50 last:border-b-0 hover:bg-gray-50/80 transition-colors duration-150 cursor-pointer">
                                            <div class="flex-shrink-0 w-8 h-8 sm:w-10 sm:h-10 rounded-full flex items-center justify-center text-sm sm:text-lg shadow-sm opacity-90"
                                                :class="notifColorClass(item.color)">
                                                <span x-text="notifIcon(item.type)"></span>
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <p class="text-[11px] sm:text-sm font-semibold text-gray-700 leading-snug" x-text="item.title"></p>
                                                <p class="text-[10px] sm:text-xs text-gray-500 mt-0.5 sm:mt-1 line-clamp-2 leading-relaxed" x-text="item.message"></p>
                                                <p class="text-[9px] sm:text-[11px] text-gray-400 mt-1 sm:mt-1.5 flex items-center gap-1">
                                                    <svg class="w-2.5 h-2.5 sm:w-3 sm:h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                    <span x-text="item.time_ago"></span>
                                                </p>
                                            </div>
                                        </a>
                                    </template>
                                </div>
                            </template>
                        </div>

                        <!-- Footer -->
                        <a href="{{ route('notifications.index') }}"
                            class="flex items-center justify-center gap-1.5 text-center text-[11px] sm:text-sm font-semibold text-[#27438D] py-2.5 sm:py-3.5 border-t border-gray-100 bg-gray-50/50 hover:bg-gray-100 transition-colors duration-150">
                            Lihat Semua Notifikasi
                            <svg class="w-3 h-3 sm:w-3.5 sm:h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </a>
                    </div>
                </div>

                <!-- Profile Dropdown -->
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open"
                        class="flex items-center gap-1 sm:gap-2 focus:outline-none group">
                        <div class="w-7 h-7 sm:w-8 sm:h-8 md:w-9 md:h-9 rounded-full bg-gradient-to-br from-[#00a2e9] to-[#0077b6] flex items-center justify-center text-white font-bold overflow-hidden text-[10px] sm:text-xs md:text-sm shadow-lg shadow-[#00a2e9]/30 group-hover:shadow-xl transition-all duration-200 ring-2 ring-transparent group-hover:ring-[#FCC626]/50">
                            @if($userPhoto)
                                <img src="{{ $userPhoto }}" alt="{{ $user->nama_lengkap }}" class="w-full h-full object-cover">
                            @else
                                {{ $userInitial }}
                            @endif
                        </div>
                        <svg class="w-3 h-3 sm:w-3.5 sm:h-3.5 md:w-4 md:h-4 text-white/60 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>

                    <!-- Dropdown Menu -->
                    <div x-show="open"
                        @click.away="open = false"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 scale-95"
                        x-transition:enter-end="opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 scale-100"
                        x-transition:leave-end="opacity-0 scale-95"
                        class="absolute right-0 mt-1 sm:mt-2 w-44 sm:w-48 md:w-56 bg-white rounded-xl shadow-xl py-2 z-50 border border-gray-100"
                        style="display: none;">

                        <!-- User Info -->
                        <div class="px-3 sm:px-4 py-2 sm:py-3 border-b border-gray-100">
                            <p class="text-xs sm:text-sm font-semibold text-gray-800 truncate">{{ $user->nama_lengkap }}</p>
                            <p class="text-[10px] sm:text-xs text-gray-500 truncate">{{ $user->email }}</p>
                        </div>

                        <!-- Menu Items -->
                        <a href="{{ route('profile.show') }}"
                            class="flex items-center gap-2 sm:gap-3 px-3 sm:px-4 py-2 sm:py-2.5 md:py-3 text-xs sm:text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                            <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            <span>Profile Saya</span>
                        </a>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"
                                class="flex items-center gap-2 sm:gap-3 w-full px-3 sm:px-4 py-2 sm:py-2.5 md:py-3 text-xs sm:text-sm text-red-600 hover:bg-red-50 transition-colors">
                                <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                </svg>
                                <span>Logout</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</nav>

<script>
function notificationHandler() {
    return {
        open: false,
        loading: true,
        items: [],
        unreadCount: 0,
        icons: {
            pengumuman: '📢',
            cuti: '🗓️',
            dinas: '🧳',
            sunnah: '🌙',
            absensi: '⚠️',
            profile: '👤',
            fhl: '🕌',
            khataman: '📖'
        },
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

        get itemsNew() {
            return this.items.filter(i => i.is_new);
        },

        get itemsOlder() {
            return this.items.filter(i => !i.is_new);
        },

        notifIcon(type) {
            return this.icons[type] || '🔔';
        },

        notifColorClass(color) {
            return this.colors[color] || 'bg-gray-100 text-gray-700';
        },

        async fetchLatest() {
            try {
                const res = await fetch('{{ route('notifications.latest') }}', {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                });
                if (!res.ok) return;
                const data = await res.json();
                this.items = data.items || [];
                this.unreadCount = data.unread_count || 0;
            } catch (e) {
                // Silent fail
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
                // Silent fail
            }
        },

        toggle() {
            this.open = !this.open;
            if (this.open) {
                this.markRead();
            }
        },

        init() {
            this.fetchLatest();
            setInterval(() => this.fetchLatest(), 60000);
        }
    }
}
</script>
