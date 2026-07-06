<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'SIKEKAR') }} - Reset Password</title>

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
                <h1 class="text-3xl font-['Montserrat'] font-bold text-[#161758]">Buat Password Baru</h1>
                <p class="text-[#27438D] text-sm mt-2">Masukkan password baru untuk akun Anda</p>
                <p class="text-sm text-[#00A2E9] mt-1 font-semibold">📧 {{ $email }}</p>
            </div>

            @if(session('success'))
                <div class="bg-green-50 border-l-4 border-[#2E7D3E] p-4 rounded-lg mb-4">
                    <p class="text-sm text-green-700">{{ session('success') }}</p>
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-50 border-l-4 border-[#ec1d1d] p-4 rounded-lg mb-4">
                    <p class="text-sm text-red-700">{{ session('error') }}</p>
                </div>
            @endif

            <!-- Form Reset Password -->
            <form method="POST" action="{{ route('password.reset') }}" class="space-y-6">
                @csrf

                <!-- Email (hidden) -->
                <input type="hidden" name="email" value="{{ $email }}">

                <!-- Password Baru -->
                <div>
                    <label for="password" class="block text-sm font-semibold text-[#1B1B1B] mb-2">
                        <span class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-[#00A2E9]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>
                            Password Baru
                        </span>
                    </label>
                    <div class="relative">
                        <input
                            id="password"
                            type="password"
                            name="password"
                            required
                            placeholder="Minimal 8 karakter"
                            class="w-full px-4 py-3.5 bg-gray-50 border-2 border-gray-200 rounded-xl focus:outline-none focus:border-[#00A2E9] focus:ring-4 focus:ring-[#00A2E9]/20 transition-all duration-200 text-[#1B1B1B] placeholder-gray-400"
                        >
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                            <button type="button" onclick="togglePassword()" class="text-gray-400 hover:text-[#00A2E9] transition-colors">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" id="password-toggle">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                    @error('password')
                        <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Konfirmasi Password -->
                <div>
                    <label for="password_confirmation" class="block text-sm font-semibold text-[#1B1B1B] mb-2">
                        <span class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-[#00A2E9]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                            </svg>
                            Konfirmasi Password
                        </span>
                    </label>
                    <input
                        id="password_confirmation"
                        type="password"
                        name="password_confirmation"
                        required
                        placeholder="Ulangi password baru"
                        class="w-full px-4 py-3.5 bg-gray-50 border-2 border-gray-200 rounded-xl focus:outline-none focus:border-[#00A2E9] focus:ring-4 focus:ring-[#00A2E9]/20 transition-all duration-200 text-[#1B1B1B] placeholder-gray-400"
                    >
                    @error('password_confirmation')
                        <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password Strength Indicator -->
                <div class="mt-2">
                    <div class="flex items-center gap-2">
                        <div class="flex-1 h-1.5 bg-gray-200 rounded-full overflow-hidden">
                            <div id="strength-bar" class="h-full w-0 bg-[#ec1d1d] transition-all duration-300 rounded-full"></div>
                        </div>
                        <span id="strength-text" class="text-xs text-gray-500 min-w-[60px] text-right">Lemah</span>
                    </div>
                </div>

                <button
                    type="submit"
                    class="w-full bg-gradient-to-r from-[#2E7D3E] to-[#009a4b] text-white py-3.5 px-4 rounded-xl hover:shadow-xl hover:shadow-[#2E7D3E]/30 transform hover:-translate-y-0.5 transition-all duration-200 font-semibold text-base"
                >
                    Reset Password
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
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('password-toggle');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>
                `;
            } else {
                passwordInput.type = 'password';
                toggleIcon.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                `;
            }
        }

        // Password Strength
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            const strengthBar = document.getElementById('strength-bar');
            const strengthText = document.getElementById('strength-text');

            let strength = 0;
            let color = '#ec1d1d';
            let label = 'Lemah';

            if (password.length >= 8) strength += 1;
            if (password.length >= 12) strength += 1;
            if (/[a-z]/.test(password)) strength += 1;
            if (/[A-Z]/.test(password)) strength += 1;
            if (/[0-9]/.test(password)) strength += 1;
            if (/[^a-zA-Z0-9]/.test(password)) strength += 1;

            const percentage = Math.min((strength / 6) * 100, 100);

            if (strength <= 2) {
                color = '#ec1d1d';
                label = 'Lemah';
            } else if (strength <= 4) {
                color = '#FCC626';
                label = 'Sedang';
            } else {
                color = '#2E7D3E';
                label = 'Kuat';
            }

            strengthBar.style.width = percentage + '%';
            strengthBar.style.backgroundColor = color;
            strengthText.textContent = label;
            strengthText.style.color = color;
        });
    </script>
</body>
</html>
