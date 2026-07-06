<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'SIKEKAR') }} - Lupa Password</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&family=Inter:wght@300;400;500;600;700&family=Montserrat:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-['Poppins'] antialiased min-h-screen flex items-center justify-center p-4 bg-gradient-to-br from-[#161758] via-[#1E3A8A] to-[#00A2E9]">

    <div class="fixed inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -right-40 w-80 h-80 bg-[#00A2E9] rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-pulse"></div>
        <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-[#FCC626] rounded-full mix-blend-multiply filter blur-3xl opacity-10 animate-pulse" style="animation-delay: 2s;"></div>
    </div>

    <div class="w-full max-w-md relative z-10">
        <div class="bg-white/95 backdrop-blur-sm rounded-3xl shadow-2xl p-8 md:p-10 border border-white/20">

            <!-- Header -->
            <div class="text-center mb-8">
                <a href="{{ route('login') }}" class="inline-block mb-4">
                    <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-[#161758] via-[#27438D] to-[#00A2E9] flex items-center justify-center mx-auto shadow-xl shadow-[#00A2E9]/20">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                        </svg>
                    </div>
                </a>
                <h1 class="text-3xl font-['Montserrat'] font-bold text-[#161758]">Lupa Password</h1>
                <p class="text-[#27438D] text-sm mt-2">Verifikasi identitas Anda untuk mereset password</p>
            </div>

            @if(session('error'))
                <div class="bg-red-50 border-l-4 border-[#ec1d1d] p-4 rounded-lg mb-4">
                    <p class="text-sm text-red-700">{{ session('error') }}</p>
                </div>
            @endif

            <!-- Form Verifikasi Email -->
            <form method="POST" action="{{ route('password.verify') }}" class="space-y-6">
                @csrf

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-semibold text-[#1B1B1B] mb-2">
                        <span class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-[#00A2E9]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"></path>
                            </svg>
                            Email Address
                        </span>
                    </label>
                    <input
                        id="email"
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        required
                        autofocus
                        placeholder="you@example.com"
                        class="w-full px-4 py-3.5 bg-gray-50 border-2 border-gray-200 rounded-xl focus:outline-none focus:border-[#00A2E9] focus:ring-4 focus:ring-[#00A2E9]/20 transition-all duration-200 text-[#1B1B1B] placeholder-gray-400"
                    >
                    @error('email')
                        <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- CAPTCHA -->
                <div>
                    <label class="block text-sm font-semibold text-[#1B1B1B] mb-2">
                        <span class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-[#00A2E9]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>
                            Verifikasi Manusia
                        </span>
                    </label>
                    <div class="flex items-center gap-4 bg-gray-50 border-2 border-gray-200 rounded-xl p-3">
                        <div class="flex-1 text-center">
                            <span class="text-2xl font-bold text-[#161758] font-['Montserrat']">{{ $captcha['text'] }}</span>
                        </div>
                        <button type="button" onclick="refreshCaptcha()" class="text-[#00A2E9] hover:text-[#161758] transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                        </button>
                    </div>
                    <input
                        type="text"
                        name="captcha_input"
                        required
                        placeholder="Masukkan hasil perhitungan"
                        class="w-full px-4 py-3.5 bg-gray-50 border-2 border-gray-200 rounded-xl focus:outline-none focus:border-[#00A2E9] focus:ring-4 focus:ring-[#00A2E9]/20 transition-all duration-200 text-[#1B1B1B] placeholder-gray-400 mt-2"
                    >
                    @error('captcha_input')
                        <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <button
                    type="submit"
                    class="w-full bg-gradient-to-r from-[#161758] via-[#27438D] to-[#00A2E9] text-white py-3.5 px-4 rounded-xl hover:shadow-xl hover:shadow-[#00A2E9]/30 transform hover:-translate-y-0.5 transition-all duration-200 font-semibold text-base"
                >
                    Verifikasi & Lanjutkan
                </button>

                <div class="text-center">
                    <a href="{{ route('login') }}" class="text-sm text-[#00A2E9] hover:text-[#161758] transition-colors">
                        ← Kembali ke Login
                    </a>
                </div>
            </form>
        </div>

        <div class="text-center mt-6">
            <p class="text-xs text-white/60 font-medium">
                &copy; {{ date('Y') }}
                <span class="text-[#FCC626] font-semibold">SIKEKAR</span>.
                All rights reserved.
            </p>
        </div>
    </div>

    <script>
        function refreshCaptcha() {
            fetch('{{ route("refresh.captcha") }}')
                .then(response => response.json())
                .then(data => {
                    // Update CAPTCHA text
                    document.querySelector('.text-2xl').textContent = data.text;
                })
                .catch(error => {
                    console.error('Error refreshing captcha:', error);
                    // Reload page if fetch fails
                    location.reload();
                });
        }
    </script>
</body>
</html>
