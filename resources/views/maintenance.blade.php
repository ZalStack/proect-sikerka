<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $info['title'] ?? 'Sistem Sedang Pemeliharaan' }} - SIKEKAR</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&family=Montserrat:wght@400;600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @keyframes pulse-glow {
            0%, 100% {
                box-shadow: 0 0 25px rgba(0, 162, 233, 0.3), 0 0 50px rgba(252, 198, 38, 0.15);
                transform: scale(1);
            }
            50% {
                box-shadow: 0 0 45px rgba(0, 162, 233, 0.6), 0 0 80px rgba(252, 198, 38, 0.35);
                transform: scale(1.03);
            }
        }
        @keyframes rotate-gear {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        @keyframes rotate-gear-reverse {
            from { transform: rotate(360deg); }
            to { transform: rotate(0deg); }
        }
        @keyframes float-badge {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-8px); }
        }
        .animate-glow {
            animation: pulse-glow 3s infinite ease-in-out;
        }
        .gear-fast {
            animation: rotate-gear 8s linear infinite;
        }
        .gear-slow-rev {
            animation: rotate-gear-reverse 12s linear infinite;
        }
        .float-card {
            animation: float-badge 4s ease-in-out infinite;
        }
    </style>
</head>
<body class="font-['Poppins'] bg-gradient-to-br from-[#090b2c] via-[#0F1245] to-[#161758] min-h-screen text-white flex flex-col justify-between relative overflow-x-hidden selection:bg-[#00a2e9] selection:text-white">

    <!-- Decorative Background Grid & Glowing Orbs -->
    <div class="fixed inset-0 pointer-events-none opacity-20">
        <div class="absolute -top-40 -left-40 w-96 h-96 bg-[#00a2e9] rounded-full blur-[120px]"></div>
        <div class="absolute top-1/2 -right-40 w-96 h-96 bg-[#FCC626] rounded-full blur-[140px]"></div>
        <div class="absolute -bottom-40 left-1/3 w-96 h-96 bg-[#27438D] rounded-full blur-[130px]"></div>
    </div>

    <!-- Top Navbar Bar -->
    <header class="relative z-20 w-full border-b border-white/10 backdrop-blur-md bg-white/[0.02]">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-3.5 sm:py-4 flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-xl bg-gradient-to-br from-[#FCC626] to-[#e5a000] flex items-center justify-center shadow-lg shadow-[#FCC626]/20 font-['Montserrat'] font-extrabold text-[#0F1245] text-base sm:text-lg">
                    S
                </div>
                <span class="font-['Montserrat'] font-bold text-lg sm:text-xl tracking-wider text-[#FCC626]">
                    SIKEKAR
                </span>
            </div>

            <div class="flex items-center gap-3">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-[#ec1d1d]/20 text-[#ff6b6b] border border-[#ec1d1d]/30">
                    <span class="w-2 h-2 rounded-full bg-[#ec1d1d] animate-ping"></span>
                    Maintenance Mode
                </span>
            </div>
        </div>
    </header>

    <!-- Main Content Center -->
    <main class="relative z-10 flex-1 flex items-center justify-center px-4 sm:px-6 lg:px-8 py-10">
        <div class="max-w-2xl w-full text-center space-y-8">

            <!-- Animated Tech Graphic / Server Gears -->
            <div class="relative inline-flex items-center justify-center mb-2">
                <div class="w-28 h-28 sm:w-36 sm:h-36 rounded-3xl bg-gradient-to-tr from-[#161758]/90 via-[#27438D]/80 to-[#00a2e9]/40 border border-white/20 backdrop-blur-xl flex items-center justify-center shadow-2xl animate-glow relative">
                    <i class="fa-solid fa-server text-4xl sm:text-5xl text-[#FCC626] drop-shadow-md"></i>
                    <!-- Spinning Gears Overlay -->
                    <div class="absolute -top-3 -right-3 w-10 h-10 rounded-full bg-[#00a2e9]/30 border border-[#00a2e9]/50 flex items-center justify-center backdrop-blur-md gear-fast">
                        <i class="fa-solid fa-gear text-sm text-[#00a2e9]"></i>
                    </div>
                    <div class="absolute -bottom-2 -left-2 w-9 h-9 rounded-full bg-[#FCC626]/20 border border-[#FCC626]/40 flex items-center justify-center backdrop-blur-md gear-slow-rev">
                        <i class="fa-solid fa-gear text-xs text-[#FCC626]"></i>
                    </div>
                </div>
            </div>

            <!-- Title & Badges -->
            <div class="space-y-3">
                <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-white/10 border border-white/15 text-xs sm:text-sm font-medium text-[#00a2e9] backdrop-blur-sm shadow-sm">
                    <i class="fa-solid fa-wrench text-xs"></i>
                    <span>Sistem Sedang Diperbarui & Dioptimalkan</span>
                </div>
                <h1 class="text-2xl sm:text-4xl md:text-5xl font-extrabold font-['Montserrat'] tracking-tight text-white leading-tight">
                    {{ $info['title'] ?? 'Sistem Sedang Dalam Pemeliharaan' }}
                </h1>
            </div>

            <!-- Message Card -->
            <div class="bg-white/[0.06] border border-white/15 backdrop-blur-xl rounded-2xl sm:rounded-3xl p-6 sm:p-8 shadow-2xl text-left space-y-5">
                <p class="text-white/85 text-sm sm:text-base leading-relaxed font-light">
                    {{ $info['message'] ?? 'Mohon maaf atas ketidaknyamanannya. Saat ini kami sedang melakukan pemeliharaan dan pembaruan sistem untuk meningkatkan performa, stabilitas, dan keamanan layanan SIKEKAR.' }}
                </p>

                <div class="pt-4 border-t border-white/10 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs sm:text-sm">
                    <div class="flex items-center gap-2.5 text-[#FCC626] font-medium">
                        <i class="fa-regular fa-clock text-base"></i>
                        <span>Estimasi Selesai: <strong class="text-white font-semibold">{{ $info['end_time'] ?? 'Segera Kembali' }}</strong></span>
                    </div>

                    <div class="flex items-center gap-2 text-white/60">
                        <i class="fa-solid fa-circle-notch fa-spin text-xs text-[#00a2e9]"></i>
                        <span>Pemeriksaan otomatis aktif</span>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-center pt-2">
                <button onclick="window.location.reload();" class="px-8 py-3 rounded-xl bg-gradient-to-r from-[#00a2e9] to-[#0077b6] hover:from-[#00b4ff] hover:to-[#0088cc] text-white font-semibold text-sm shadow-lg shadow-[#00a2e9]/30 hover:scale-[1.02] active:scale-[0.98] transition-all flex items-center justify-center gap-2">
                    <i class="fa-solid fa-rotate-right"></i>
                    <span>Muat Ulang Halaman</span>
                </button>
            </div>

            <!-- Auto Reload Countdown Note -->
            <p class="text-xs text-white/50">
                Halaman ini akan otomatis menyegarkan status sistem dalam <span id="countdown" class="font-bold text-[#00a2e9]">30</span> detik.
            </p>
        </div>
    </main>

    <!-- Footer -->
    <footer class="relative z-20 w-full border-t border-white/10 py-4 text-center text-xs text-white/50">
        <p>&copy; {{ date('Y') }} SIKEKAR - Sistem Kepegawaian & Presensi Terpadu. Hak cipta dilindungi.</p>
    </footer>

    <script>
        let seconds = 30;
        const countdownEl = document.getElementById('countdown');
        setInterval(() => {
            seconds--;
            if (countdownEl) {
                countdownEl.innerText = seconds;
            }
            if (seconds <= 0) {
                window.location.reload();
            }
        }, 1000);
    </script>
</body>
</html>
