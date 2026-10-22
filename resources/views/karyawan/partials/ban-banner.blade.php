{{--
    Banner blokir presensi (anti remote access).
    Tampil otomatis di dashboard & halaman absensi karyawan yang sedang kena ban
    (sementara maupun permanen), lengkap dengan pesan alert dari Superadmin.
--}}
@php
    $banUser = Auth::user();
    $banActive = $banUser && method_exists($banUser, 'isBanned') && $banUser->isBanned();
@endphp

@if($banActive)
    <div class="mb-4 sm:mb-6 bg-gradient-to-r from-red-600 to-red-700 rounded-2xl shadow-lg p-4 sm:p-5 text-white border-2 border-red-800/40"
         x-data x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
        <div class="flex items-start gap-3">
            <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center text-xl shrink-0">
                <i class="fa-solid fa-ban"></i>
            </div>
            <div class="min-w-0 flex-1">
                <p class="font-bold text-sm sm:text-base font-['Poppins']">
                    ⛔ AKUN ANDA DIBLOKIR DARI FITUR PRESENSI
                </p>

                <p class="text-xs sm:text-sm text-white/90 mt-1.5">
                    @if($banUser->is_banned_permanent)
                        Status: <span class="font-bold">BAN PERMANEN</span> — check-in &amp; check-out dinonaktifkan selamanya.
                    @else
                        Status: <span class="font-bold">BAN SEMENTARA</span> — sisa
                        <span class="font-bold">{{ $banUser->banDaysRemaining() ?? 1 }} hari</span>
                        @if($banUser->banned_until)
                            (berakhir {{ $banUser->banned_until->translatedFormat('d F Y, H:i') }})
                        @endif
                    @endif
                </p>

                <p class="text-[11px] text-white/80 mt-1">
                    Total pelanggaran tercatat: <b>{{ (int) $banUser->violation_count }}&times;</b> (kebijakan: 1&times; = 1 hari, 2&times; = 2 hari, 3&times; = permanen).
                </p>

                @if($banUser->ban_message)
                    <div class="mt-3 bg-white/15 border border-white/30 rounded-xl p-3">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-white/70 mb-1">
                            <i class="fa-solid fa-message"></i> Pesan dari Superadmin
                        </p>
                        <p class="text-xs sm:text-sm text-white leading-relaxed">{{ $banUser->ban_message }}</p>
                    </div>
                @endif

                <p class="text-[11px] text-white/70 mt-2.5">
                    <i class="fa-solid fa-circle-info"></i>
                    Hubungi Superadmin / HR jika Anda merasa ini adalah kekeliruan.
                </p>
            </div>
        </div>
    </div>
@endif
