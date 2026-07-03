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
<body class="font-['Poppins'] antialiased bg-[#F5F5F5]">
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="max-w-md w-full bg-white rounded-lg shadow-lg p-8">
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold font-['Montserrat'] text-[#161758]">SIKEKAR</h1>
                <p class="text-[#27438D] mt-2">Sistem Informasi Kerja Karyawan</p>
            </div>

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="mb-4">
                    <label for="email" class="block text-sm font-medium text-[#1B1B1B] mb-1">Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9] focus:border-transparent @error('email') border-[#ec1d1d] @enderror">
                    @error('email')
                        <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="password" class="block text-sm font-medium text-[#1B1B1B] mb-1">Password</label>
                    <input id="password" type="password" name="password" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9] focus:border-transparent @error('password') border-[#ec1d1d] @enderror">
                    @error('password')
                        <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-between mb-6">
                    <label class="flex items-center">
                        <input type="checkbox" name="remember" class="rounded border-gray-300 text-[#00a2e9] focus:ring-[#00a2e9]">
                        <span class="ml-2 text-sm text-[#1B1B1B]">Ingat saya</span>
                    </label>
                </div>

                <button type="submit"
                        class="w-full bg-[#27438D] text-white py-2 px-4 rounded-lg hover:bg-[#161758] transition-colors duration-200 font-medium">
                    Login
                </button>
            </form>
        </div>
    </div>
</body>
</html>
