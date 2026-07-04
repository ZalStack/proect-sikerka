<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'SIPegawai') }} - Login</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Inter:wght@300;400;500;600;700&family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-poppins antialiased bg-off-white min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <!-- Card Container -->
        <div class="bg-white rounded-2xl shadow-xl p-8 md:p-10">
            <!-- Brand Section -->
            <div class="text-center mb-10">
                <div class="inline-block mb-3">
                    <div class="w-16 h-16 rounded-full bg-gradient-to-br from-french-blue to-deep-twilight flex items-center justify-center mx-auto shadow-lg">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                </div>
                <h1 class="text-4xl font-bold font-montserrat text-deep-twilight tracking-tight">SIKEKAR</h1>
                <p class="text-french-blue mt-2 text-sm font-medium tracking-wide">Sistem Informasi Kerja Karyawan</p>
                <div class="w-20 h-1 bg-blue-bell mx-auto mt-3 rounded-full"></div>
            </div>

            <!-- Login Form -->
            <form method="POST" action="{{ route('login') }}" class="space-y-5">
                @csrf

                <!-- Email Field -->
                <div>
                    <label for="email" class="block text-sm font-semibold text-deep-twilight mb-1.5">
                        <span class="flex items-center">
                            <svg class="w-4 h-4 mr-1.5 text-blue-bell" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                        class="w-full px-4 py-3 bg-off-white border-2 border-gray-200 rounded-xl focus:outline-none focus:border-blue-bell focus:ring-4 focus:ring-blue-bell/20 transition-all duration-200 @error('email') border-racing-red focus:border-racing-red focus:ring-racing-red/20 @enderror">
                    @error('email')
                        <p class="mt-1.5 text-sm text-racing-red flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                            </svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Password Field -->
                <div>
                    <label for="password" class="block text-sm font-semibold text-deep-twilight mb-1.5">
                        <span class="flex items-center">
                            <svg class="w-4 h-4 mr-1.5 text-blue-bell" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>
                            Password
                        </span>
                    </label>
                    <input
                        id="password"
                        type="password"
                        name="password"
                        required
                        placeholder="Enter your password"
                        class="w-full px-4 py-3 bg-off-white border-2 border-gray-200 rounded-xl focus:outline-none focus:border-blue-bell focus:ring-4 focus:ring-blue-bell/20 transition-all duration-200 @error('password') border-racing-red focus:border-racing-red focus:ring-racing-red/20 @enderror">
                    @error('password')
                        <p class="mt-1.5 text-sm text-racing-red flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                            </svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Remember Me & Forgot Password -->
                <div class="flex items-center justify-between pt-1">
                    <label class="flex items-center cursor-pointer group">
                        <input
                            type="checkbox"
                            name="remember"
                            class="w-4 h-4 rounded border-2 border-gray-300 text-blue-bell focus:ring-2 focus:ring-blue-bell/50 focus:ring-offset-0 transition-all duration-200 cursor-pointer">
                        <span class="ml-2.5 text-sm text-deep-twilight font-medium group-hover:text-french-blue transition-colors duration-200">Ingat saya</span>
                    </label>
                    <a href="#" class="text-sm font-medium text-blue-bell hover:text-french-blue transition-colors duration-200 hover:underline">
                        Lupa password?
                    </a>
                </div>

                <!-- Submit Button -->
                <button
                    type="submit"
                    class="w-full bg-gradient-to-r from-french-blue to-deep-twilight text-white py-3.5 px-4 rounded-xl hover:shadow-lg hover:shadow-french-blue/30 transform hover:-translate-y-0.5 transition-all duration-200 font-semibold text-base flex items-center justify-center group">
                    <span>Masuk</span>
                    <svg class="w-5 h-5 ml-2 transform group-hover:translate-x-1 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                    </svg>
                </button>

                <!-- Additional Info -->
                <div class="text-center pt-4">
                    <p class="text-xs text-gray-400">
                        <span class="inline-block w-1 h-1 rounded-full bg-blue-bell mx-1"></span>
                        Sistem aman & terenkripsi
                        <span class="inline-block w-1 h-1 rounded-full bg-blue-bell mx-1"></span>
                    </p>
                </div>
            </form>
        </div>

        <!-- Footer -->
        <div class="text-center mt-6">
            <p class="text-xs text-gray-400">
                &copy; {{ date('Y') }} SIKEKAR. All rights reserved.
            </p>
        </div>
    </div>
</body>
</html>
