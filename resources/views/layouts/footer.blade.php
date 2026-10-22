{{-- views/layouts/footer.blade.php --}}
<footer class="relative mt-auto bg-[#0F1245] text-white overflow-hidden">
    {{-- Brand accent line --}}
    <div class="absolute inset-x-0 top-0 h-[3px] bg-gradient-to-r from-[#FCC626] via-[#00a2e9] to-[#27438D]"></div>
    <div class="absolute -top-16 left-1/4 w-72 h-72 rounded-full bg-[#00a2e9]/10 blur-3xl pointer-events-none"></div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-5 sm:py-7">
        <div class="flex flex-col sm:flex-row items-center justify-between gap-3 text-center sm:text-left">
            <div class="flex items-center gap-3">
                <span class="w-9 h-9 rounded-xl bg-gradient-to-br from-[#FCC626] to-[#f0a500] flex items-center justify-center text-[#0F1245] font-extrabold shadow-lg shadow-[#FCC626]/30">S</span>
                <div>
                    <p class="text-sm font-semibold">&copy; {{ date('Y') }} SIKEKAR. All rights reserved.</p>
                    <p class="text-[#00a2e9] text-xs mt-0.5 font-medium">Sistem Informasi Karyawan</p>
                </div>
            </div>
            <p class="text-xs text-white/60">Powered by <span class="text-[#00a2e9] font-semibold">Klinik Pendidikan MIPA</span></p>
        </div>
    </div>
</footer>
