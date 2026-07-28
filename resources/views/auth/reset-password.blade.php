<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'SIKEKAR') }} - Reset Password</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,400;14..32,500;14..32,600;14..32,700;14..32,800;14..32,900&family=Lexend:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Tailwind CSS via CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        'sans': ['Inter', 'system-ui', 'sans-serif'],
                        'display': ['Lexend', 'Inter', 'sans-serif'],
                    },
                    animation: {
                        'float': 'float 8s ease-in-out infinite',
                        'float-delayed': 'float-delayed 10s ease-in-out infinite',
                        'float-slow': 'float-slow 14s ease-in-out infinite',
                        'pulse-ring': 'pulse-ring 2.5s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                        'gradient-shift': 'gradient-shift 6s ease infinite',
                        'slide-up': 'slide-up 0.7s cubic-bezier(0.16, 1, 0.3, 1) forwards',
                        'slide-right': 'slide-right 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards',
                        'fade-in': 'fade-in 0.9s ease-out forwards',
                        'scale-in': 'scale-in 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards',
                        'blob': 'blob 20s linear infinite',
                    },
                    keyframes: {
                        'float': {
                            '0%, 100%': { transform: 'translateY(0px) rotate(0deg)' },
                            '25%': { transform: 'translateY(-25px) rotate(2deg)' },
                            '50%': { transform: 'translateY(-15px) rotate(-1deg)' },
                            '75%': { transform: 'translateY(-30px) rotate(1deg)' },
                        },
                        'float-delayed': {
                            '0%, 100%': { transform: 'translateY(0px) rotate(0deg)' },
                            '25%': { transform: 'translateY(-20px) rotate(-2deg)' },
                            '50%': { transform: 'translateY(-30px) rotate(1deg)' },
                            '75%': { transform: 'translateY(-10px) rotate(-1deg)' },
                        },
                        'float-slow': {
                            '0%, 100%': { transform: 'translateY(0px) scale(1)' },
                            '50%': { transform: 'translateY(-20px) scale(1.03)' },
                        },
                        'pulse-ring': {
                            '0%': { transform: 'scale(0.8)', opacity: '0.7' },
                            '100%': { transform: 'scale(1.5)', opacity: '0' },
                        },
                        'gradient-shift': {
                            '0%': { backgroundPosition: '0% 50%' },
                            '50%': { backgroundPosition: '100% 50%' },
                            '100%': { backgroundPosition: '0% 50%' },
                        },
                        'slide-up': {
                            'from': { opacity: '0', transform: 'translateY(40px)' },
                            'to': { opacity: '1', transform: 'translateY(0)' },
                        },
                        'slide-right': {
                            'from': { opacity: '0', transform: 'translateX(-40px)' },
                            'to': { opacity: '1', transform: 'translateX(0)' },
                        },
                        'fade-in': {
                            'from': { opacity: '0' },
                            'to': { opacity: '1' },
                        },
                        'scale-in': {
                            'from': { opacity: '0', transform: 'scale(0.85)' },
                            'to': { opacity: '1', transform: 'scale(1)' },
                        },
                        'blob': {
                            '0%, 100%': { borderRadius: '60% 40% 70% 30% / 60% 40% 70% 30%' },
                            '25%': { borderRadius: '40% 60% 30% 70% / 40% 60% 30% 70%' },
                            '50%': { borderRadius: '50% 50% 40% 60% / 50% 50% 40% 60%' },
                            '75%': { borderRadius: '45% 55% 60% 40% / 45% 55% 60% 40%' },
                        },
                    },
                    backgroundSize: {
                        '200%': '200% 200%',
                    }
                }
            }
        }
    </script>

    <style>
        ::-webkit-scrollbar { width: 0px; }
        * { transition-property: background-color, border-color, color, fill, stroke, opacity, box-shadow, transform; transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1); transition-duration: 250ms; }
        .glass-card {
            background: rgba(255, 255, 255, 0.75);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid rgba(255, 255, 255, 0.5);
        }
        .glass-input {
            background: rgba(255, 255, 255, 0.5);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        /* Prevent iOS Safari from auto-zooming when focusing inputs on small screens */
        @media (max-width: 639px) {
            input, select, textarea { font-size: 16px !important; }
        }
        /* Respect users who prefer reduced motion */
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
                scroll-behavior: auto !important;
            }
        }
        /* Gentle shake to draw attention to validation errors */
        @keyframes shake-x {
            10%, 90% { transform: translateX(-1px); }
            20%, 80% { transform: translateX(2px); }
            30%, 50%, 70% { transform: translateX(-4px); }
            40%, 60% { transform: translateX(4px); }
        }
        .animate-shake { animation: shake-x 0.5s cubic-bezier(0.36, 0.07, 0.19, 0.97) both; }
        /* Button loading spinner */
        .btn-spinner {
            width: 1.15rem; height: 1.15rem; border-radius: 9999px;
            border: 2.5px solid rgba(255,255,255,0.35); border-top-color: #fff;
            animation: spin 0.7s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
    </style>
</head>
<body class="font-sans antialiased min-h-screen min-h-[100dvh] flex bg-slate-950 overflow-x-hidden">

    <div class="w-full min-h-screen min-h-[100dvh] flex flex-col lg:flex-row">

        <!-- ==================== LEFT PANEL (BRANDING) ==================== -->
        <div class="flex w-full lg:w-1/2 xl:w-[48%] relative overflow-hidden bg-gradient-to-br from-slate-900 via-blue-950 to-slate-900 items-center justify-center p-8 sm:p-10 lg:p-12 xl:p-20">
            <div class="absolute inset-0 overflow-hidden pointer-events-none">
                <div class="absolute -top-20 -left-20 w-[500px] h-[500px] bg-gradient-to-r from-cyan-400/20 via-blue-500/10 to-indigo-600/10 rounded-full mix-blend-overlay filter blur-3xl animate-blob"></div>
                <div class="absolute -bottom-32 -right-20 w-[450px] h-[450px] bg-gradient-to-r from-purple-500/15 via-fuchsia-500/10 to-rose-500/15 rounded-full mix-blend-overlay filter blur-3xl animate-blob" style="animation-delay: -7s;"></div>
                <div class="absolute top-1/2 left-1/3 w-[350px] h-[350px] bg-gradient-to-r from-cyan-600/15 via-blue-600/10 to-cyan-400/15 rounded-full mix-blend-overlay filter blur-3xl animate-blob" style="animation-delay: -12s;"></div>
                <div class="absolute inset-0 opacity-[0.04]" style="background-image: radial-gradient(circle, #ffffff 1px, transparent 1px); background-size: 45px 45px;"></div>
            </div>
            <div class="absolute top-10 right-10 w-24 h-24 border border-white/10 rounded-full animate-float-slow"></div>
            <div class="absolute bottom-20 left-10 w-16 h-16 border border-cyan-400/20 rounded-2xl rotate-45 animate-float-delayed"></div>
            <div class="absolute top-1/3 right-20 w-8 h-8 bg-gradient-to-r from-cyan-400 to-blue-500 rounded-lg rotate-12 animate-float opacity-60"></div>

            <div class="relative z-10 max-w-lg mx-auto text-center lg:text-left">
                <div class="inline-flex mb-5 lg:mb-10 animate-scale-in">
                    <div class="relative">
                        <div class="absolute inset-0 rounded-3xl animate-pulse-ring bg-cyan-400/30"></div>
                        <div class="absolute inset-0 rounded-3xl animate-pulse-ring bg-cyan-400/20" style="animation-delay: 0.7s;"></div>
                        <div class="relative w-16 h-16 lg:w-20 lg:h-20 xl:w-24 xl:h-24 rounded-2xl bg-gradient-to-br from-slate-800 via-blue-900 to-cyan-600 flex items-center justify-center shadow-2xl shadow-cyan-500/25 border border-white/10 z-10">
                            <svg class="w-8 h-8 lg:w-10 lg:h-10 xl:w-12 xl:h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                    </div>
                </div>
                <h1 class="text-3xl lg:text-4xl xl:text-5xl font-display font-extrabold text-white tracking-tight mb-3 lg:mb-4 animate-slide-right">
                    <span class="bg-gradient-to-r from-cyan-300 via-blue-300 to-purple-300 bg-clip-text text-transparent animate-gradient-shift bg-200%">SIKEKAR</span>
                    <span class="block text-white/90 text-xl lg:text-2xl xl:text-3xl mt-1 lg:mt-2 font-semibold">KPM</span>
                </h1>
                <p class="text-blue-200/70 text-xs lg:text-sm xl:text-base font-medium tracking-widest uppercase mb-5 lg:mb-8 animate-fade-in delay-200 [animation-delay:200ms]">Sistem Informasi Kinerja Karyawan</p>
                <div class="flex items-center gap-3 mb-6 lg:mb-10 animate-fade-in delay-300 [animation-delay:300ms]">
                    <div class="h-px flex-1 bg-gradient-to-r from-transparent via-cyan-400/50 to-transparent"></div>
                    <div class="w-3 h-3 rounded-full bg-cyan-400 shadow-lg shadow-cyan-400/50"></div>
                    <div class="h-px flex-1 bg-gradient-to-r from-transparent via-cyan-400/50 to-transparent"></div>
                </div>
                <!-- Feature list (desktop only, keeps mobile hero compact) -->
                <div class="hidden lg:block space-y-6 text-left animate-slide-right delay-400 [animation-delay:400ms]">
                    <div class="flex items-center gap-5 group">
                        <div class="w-12 h-12 rounded-xl bg-white/5 flex items-center justify-center flex-shrink-0 group-hover:bg-cyan-500/20 transition-all duration-300 border border-white/5 group-hover:border-cyan-400/30">
                            <svg class="w-6 h-6 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                        </div>
                        <div><h4 class="text-white font-semibold text-base">Keamanan Data</h4><p class="text-blue-200/50 text-sm mt-0.5">Enkripsi end-to-end level perusahaan</p></div>
                    </div>
                    <div class="flex items-center gap-5 group">
                        <div class="w-12 h-12 rounded-xl bg-white/5 flex items-center justify-center flex-shrink-0 group-hover:bg-purple-500/20 transition-all duration-300 border border-white/5 group-hover:border-purple-400/30">
                            <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                        </div>
                        <div><h4 class="text-white font-semibold text-base">Performa Cepat</h4><p class="text-blue-200/50 text-sm mt-0.5">Akses real-time tanpa hambatan</p></div>
                    </div>
                    <div class="flex items-center gap-5 group">
                        <div class="w-12 h-12 rounded-xl bg-white/5 flex items-center justify-center flex-shrink-0 group-hover:bg-emerald-500/20 transition-all duration-300 border border-white/5 group-hover:border-emerald-400/30">
                            <svg class="w-6 h-6 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                        </div>
                        <div><h4 class="text-white font-semibold text-base">Dashboard Analitik</h4><p class="text-blue-200/50 text-sm mt-0.5">Pantau kinerja dengan visualisasi data</p></div>
                    </div>
                </div>
                <!-- Bottom quote (desktop only) -->
                <div class="hidden lg:block mt-12 pt-8 border-t border-white/10 animate-fade-in delay-500 [animation-delay:500ms]">
                    <p class="text-blue-200/60 text-sm italic flex items-start gap-2">
                        <svg class="w-5 h-5 text-cyan-400 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 24 24"><path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10H14.017zM0 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151C7.546 6.068 5.983 8.789 5.983 11H9.983v10H0z"/></svg>
                        <span>"Mengubah data menjadi kinerja unggul untuk Indonesia maju."</span>
                    </p>
                </div>
            </div>
        </div>

        <!-- ==================== RIGHT PANEL (FORM) ==================== -->
        <div class="w-full lg:w-1/2 xl:w-[52%] flex items-center justify-center p-4 sm:p-6 md:p-8 lg:p-10 xl:p-16 relative z-10 -mt-6 lg:mt-0 rounded-t-[2rem] lg:rounded-none bg-gradient-to-br from-gray-50 via-slate-100 to-gray-100 shadow-[0_-10px_30px_-15px_rgba(0,0,0,0.3)] lg:shadow-none">
            <div class="absolute inset-0 overflow-hidden pointer-events-none rounded-t-[2rem] lg:rounded-none">
                <div class="absolute top-20 right-10 w-80 h-80 rounded-full bg-cyan-100/30 blur-3xl"></div>
                <div class="absolute bottom-10 left-10 w-96 h-96 rounded-full bg-blue-100/30 blur-3xl"></div>
                <div class="absolute inset-0 opacity-[0.02]" style="background-image: radial-gradient(circle, #0f172a 1px, transparent 1px); background-size: 35px 35px;"></div>
            </div>

            <div class="w-full max-w-md xl:max-w-lg relative z-10 animate-slide-up">
                <div class="glass-card rounded-3xl shadow-2xl shadow-slate-900/10 p-6 sm:p-8 md:p-10 border border-white/40 {{ ($errors->any() || session('error')) ? 'animate-shake' : '' }}">

                    <!-- Header -->
                    <div class="mb-8">
                        <span class="inline-flex items-center gap-2 px-3 py-1.5 text-xs font-semibold text-emerald-700 bg-emerald-50 rounded-full mb-5 tracking-wide uppercase border border-emerald-200">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                            Reset Password
                        </span>
                        <h2 class="text-2xl sm:text-3xl font-display font-bold text-slate-800 leading-tight">Buat Password Baru</h2>
                        <p class="text-gray-500 mt-2 text-sm">
                            Untuk akun <span class="font-semibold text-slate-700">{{ $email }}</span>
                        </p>
                    </div>

                    @if(session('success'))
                    <div class="bg-emerald-50 border border-emerald-200 p-4 mb-6 rounded-2xl animate-scale-in flex items-start gap-3">
                        <div class="w-8 h-8 rounded-full bg-emerald-100 flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                        </div>
                        <p class="text-sm text-emerald-700 font-medium">{{ session('success') }}</p>
                    </div>
                    @endif

                    @if(session('error'))
                    <div class="bg-red-50 border border-red-200 p-4 mb-6 rounded-2xl animate-scale-in flex items-start gap-3">
                        <div class="w-8 h-8 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-red-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path></svg>
                        </div>
                        <p class="text-sm text-red-700 font-medium">{{ session('error') }}</p>
                    </div>
                    @endif

                    <form id="reset-form" method="POST" action="{{ route('password.reset') }}" class="space-y-5">
                        @csrf
                        <input type="hidden" name="email" value="{{ $email }}">

                        <!-- Password Baru -->
                        <div class="animate-slide-up delay-100 [animation-delay:100ms]">
                            <label for="password" class="block text-sm font-semibold text-slate-700 mb-2 ml-1">Password Baru</label>
                            <div class="relative group">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                    <svg class="w-5 h-5 text-gray-400 group-focus-within:text-cyan-600 transition-colors duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                </div>
                                <input id="password" type="password" name="password" required placeholder="Minimal 8 karakter"
                                    class="w-full pl-12 pr-12 py-3.5 glass-input rounded-2xl focus:outline-none focus:border-cyan-400 focus:ring-4 focus:ring-cyan-400/20 transition-all duration-300 text-slate-800 placeholder-gray-400 text-sm font-medium @error('password') border-red-300 @enderror">
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                    <button type="button" onclick="togglePassword()" class="p-1.5 rounded-lg text-gray-400 hover:text-cyan-600 hover:bg-cyan-50 transition-all duration-200 focus:outline-none">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" id="password-toggle">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            @error('password')<p class="mt-1.5 ml-1 text-xs text-red-500 font-medium">{{ $message }}</p>@enderror
                        </div>

                        <!-- Konfirmasi Password -->
                        <div class="animate-slide-up delay-200 [animation-delay:200ms]">
                            <label for="password_confirmation" class="block text-sm font-semibold text-slate-700 mb-2 ml-1">Konfirmasi Password</label>
                            <input id="password_confirmation" type="password" name="password_confirmation" required placeholder="Ulangi password baru"
                                class="w-full px-4 py-3.5 glass-input rounded-2xl focus:outline-none focus:border-cyan-400 focus:ring-4 focus:ring-cyan-400/20 transition-all duration-300 text-slate-800 placeholder-gray-400 text-sm font-medium">
                        </div>

                        <!-- Strength Indicator -->
                        <div class="animate-slide-up delay-300 [animation-delay:300ms]">
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-xs font-medium text-gray-500">Kekuatan Password</span>
                                <span id="strength-text" class="text-xs font-semibold text-red-500">Lemah</span>
                            </div>
                            <div class="w-full h-2 bg-gray-200 rounded-full overflow-hidden">
                                <div id="strength-bar" class="h-full w-0 bg-red-500 transition-all duration-500 rounded-full"></div>
                            </div>
                        </div>

                        <!-- Submit -->
                        <div class="pt-2 animate-slide-up delay-400 [animation-delay:400ms]">
                            <button type="submit" id="reset-submit-btn"
                                class="w-full relative bg-gradient-to-r from-emerald-700 to-emerald-600 text-white py-3.5 px-6 rounded-2xl font-semibold text-base flex items-center justify-center group overflow-hidden shadow-xl shadow-emerald-800/20 hover:shadow-emerald-500/25 transform hover:-translate-y-0.5 active:translate-y-0 active:scale-[0.98] transition-all duration-300 disabled:opacity-70 disabled:cursor-not-allowed disabled:hover:translate-y-0 disabled:hover:shadow-xl">
                                <span class="relative z-10 flex items-center gap-2" id="reset-submit-label">
                                    Reset Password
                                    <svg class="w-5 h-5 transform group-hover:translate-x-1.5 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path></svg>
                                </span>
                                <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent -translate-x-full group-hover:translate-x-full transition-transform duration-700 ease-in-out"></div>
                            </button>
                        </div>

                        <!-- Kembali ke Login -->
                        <div class="text-center pt-2 animate-fade-in delay-500 [animation-delay:500ms]">
                            <a href="{{ route('login') }}" class="text-sm font-semibold text-cyan-700 hover:text-slate-800 transition-colors duration-200 inline-flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                                Kembali ke Login
                            </a>
                        </div>
                    </form>
                </div>
                <p class="text-center mt-6 text-xs text-gray-400 font-medium animate-fade-in delay-500 [animation-delay:500ms]">&copy; {{ date('Y') }} <span class="text-slate-700 font-bold">SIKEKAR</span> <span class="text-cyan-600 font-bold">KPM</span>. Hak cipta dilindungi.</p>
            </div>
        </div>
    </div>

    <script>
        function togglePassword() {
            const pw = document.getElementById('password');
            const icon = document.getElementById('password-toggle');
            if (pw.type === 'password') {
                pw.type = 'text';
                icon.innerHTML = `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>`;
            } else {
                pw.type = 'password';
                icon.innerHTML = `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>`;
            }
        }

        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            const bar = document.getElementById('strength-bar');
            const text = document.getElementById('strength-text');
            let strength = 0;
            if (password.length >= 8) strength++;
            if (password.length >= 12) strength++;
            if (/[a-z]/.test(password)) strength++;
            if (/[A-Z]/.test(password)) strength++;
            if (/[0-9]/.test(password)) strength++;
            if (/[^a-zA-Z0-9]/.test(password)) strength++;
            const percentage = Math.min((strength / 6) * 100, 100);
            bar.style.width = percentage + '%';
            if (strength <= 2) {
                bar.style.backgroundColor = '#ef4444';
                text.textContent = 'Lemah';
                text.style.color = '#ef4444';
            } else if (strength <= 4) {
                bar.style.backgroundColor = '#eab308';
                text.textContent = 'Sedang';
                text.style.color = '#eab308';
            } else {
                bar.style.backgroundColor = '#22c55e';
                text.textContent = 'Kuat';
                text.style.color = '#22c55e';
            }
        });
    </script>

    <!-- Submit Loading State -->
    <script>
        document.getElementById('reset-form').addEventListener('submit', function () {
            const btn = document.getElementById('reset-submit-btn');
            const label = document.getElementById('reset-submit-label');
            if (btn.disabled) return; // avoid double submit
            btn.disabled = true;
            label.innerHTML = '<span class="btn-spinner"></span><span>Menyimpan...</span>';
        });
    </script>
</body>
</html>
