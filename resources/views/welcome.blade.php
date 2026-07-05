<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>E-Learning Karyawan</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body class="font-sans antialiased">
    <div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-indigo-50">
        <!-- Navigation -->
        <nav class="bg-white/80 backdrop-blur-sm shadow-sm sticky top-0 z-50">
            <div class="container mx-auto px-4 py-4 flex justify-between items-center">
                <div class="flex items-center space-x-2">
                    <div class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center">
                        <i class="fas fa-graduation-cap text-white text-xl"></i>
                    </div>
                    <span class="text-xl font-bold text-gray-800">E-Learning</span>
                </div>
                <div class="flex items-center space-x-4">
                    @auth
                        <a href="{{ auth()->user()->role === 'admin' ? route('admin.dashboard') : route('employee.dashboard') }}"
                           class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition">
                            <i class="fas fa-home mr-1"></i> Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition">
                            <i class="fas fa-sign-in-alt mr-1"></i> Login
                        </a>
                    @endauth
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <section class="container mx-auto px-4 py-16 md:py-24">
            <div class="flex flex-col md:flex-row items-center gap-12">
                <div class="flex-1 text-center md:text-left">
                    <h1 class="text-4xl md:text-5xl lg:text-6xl font-extrabold text-gray-800 leading-tight">
                        Sistem <span class="text-blue-600">E-Learning</span><br>
                        Karyawan
                    </h1>
                    <p class="mt-4 text-lg md:text-xl text-gray-600 max-w-2xl">
                        Platform pembelajaran digital untuk meningkatkan kompetensi
                        dan pengetahuan karyawan di perusahaan Anda.
                    </p>
                    <div class="mt-8 flex flex-wrap gap-4 justify-center md:justify-start">
                        @auth
                            <a href="{{ auth()->user()->role === 'admin' ? route('admin.dashboard') : route('employee.dashboard') }}"
                               class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-3 rounded-lg transition transform hover:scale-105 font-medium">
                                <i class="fas fa-home mr-2"></i> Dashboard
                            </a>
                        @else
                            <a href="{{ route('login') }}"
                               class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-3 rounded-lg transition transform hover:scale-105 font-medium">
                                <i class="fas fa-sign-in-alt mr-2"></i> Login
                            </a>
                        @endauth
                        <a href="#features"
                           class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-8 py-3 rounded-lg transition transform hover:scale-105 font-medium">
                            <i class="fas fa-info-circle mr-2"></i> Pelajari
                        </a>
                    </div>
                </div>
                <div class="flex-1 flex justify-center">
                    <div class="relative">
                        <div class="w-64 h-64 md:w-80 md:h-80 bg-gradient-to-br from-blue-400 to-indigo-500 rounded-3xl shadow-2xl flex items-center justify-center transform rotate-6">
                            <i class="fas fa-graduation-cap text-white text-8xl opacity-75"></i>
                        </div>
                        <div class="absolute -bottom-4 -right-4 bg-white rounded-xl shadow-lg p-4">
                            <div class="flex items-center space-x-3">
                                <i class="fas fa-users text-blue-600 text-xl"></i>
                                <div>
                                    <p class="text-sm font-semibold text-gray-800">Karyawan</p>
                                    <p class="text-xs text-gray-500">100+ Terdaftar</p>
                                </div>
                            </div>
                        </div>
                        <div class="absolute -top-4 -left-4 bg-white rounded-xl shadow-lg p-4">
                            <div class="flex items-center space-x-3">
                                <i class="fas fa-book text-green-600 text-xl"></i>
                                <div>
                                    <p class="text-sm font-semibold text-gray-800">Materi</p>
                                    <p class="text-xs text-gray-500">50+ Tersedia</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features -->
        <section id="features" class="container mx-auto px-4 py-16">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-800">Fitur Unggulan</h2>
                <p class="mt-2 text-gray-600">Berbagai fitur untuk mendukung proses pembelajaran</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <div class="bg-white rounded-xl shadow-sm p-6 hover:shadow-lg transition border border-gray-100">
                    <div class="w-14 h-14 bg-blue-100 rounded-lg flex items-center justify-center mb-4">
                        <i class="fas fa-book-open text-blue-600 text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-800">Materi Lengkap</h3>
                    <p class="mt-2 text-gray-600">Akses berbagai materi pembelajaran dalam format PDF, video, dan lainnya.</p>
                </div>
                <div class="bg-white rounded-xl shadow-sm p-6 hover:shadow-lg transition border border-gray-100">
                    <div class="w-14 h-14 bg-green-100 rounded-lg flex items-center justify-center mb-4">
                        <i class="fas fa-pencil-alt text-green-600 text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-800">Quiz & Evaluasi</h3>
                    <p class="mt-2 text-gray-600">Uji pemahaman dengan quiz yang dapat diatur waktu dan acak soal.</p>
                </div>
                <div class="bg-white rounded-xl shadow-sm p-6 hover:shadow-lg transition border border-gray-100">
                    <div class="w-14 h-14 bg-purple-100 rounded-lg flex items-center justify-center mb-4">
                        <i class="fas fa-chart-line text-purple-600 text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-800">Monitoring Progress</h3>
                    <p class="mt-2 text-gray-600">Pantau progress belajar dan hasil evaluasi karyawan secara real-time.</p>
                </div>
                <div class="bg-white rounded-xl shadow-sm p-6 hover:shadow-lg transition border border-gray-100">
                    <div class="w-14 h-14 bg-yellow-100 rounded-lg flex items-center justify-center mb-4">
                        <i class="fas fa-tasks text-yellow-600 text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-800">Pengumpulan Tugas</h3>
                    <p class="mt-2 text-gray-600">Karyawan dapat mengunggah tugas berupa video sebagai bukti pembelajaran.</p>
                </div>
                <div class="bg-white rounded-xl shadow-sm p-6 hover:shadow-lg transition border border-gray-100">
                    <div class="w-14 h-14 bg-red-100 rounded-lg flex items-center justify-center mb-4">
                        <i class="fas fa-random text-red-600 text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-800">Random Soal</h3>
                    <p class="mt-2 text-gray-600">Soal diacak untuk setiap peserta untuk mengurangi kecurangan.</p>
                </div>
                <div class="bg-white rounded-xl shadow-sm p-6 hover:shadow-lg transition border border-gray-100">
                    <div class="w-14 h-14 bg-indigo-100 rounded-lg flex items-center justify-center mb-4">
                        <i class="fas fa-mobile-alt text-indigo-600 text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-800">Responsive Design</h3>
                    <p class="mt-2 text-gray-600">Akses dari berbagai perangkat dengan tampilan yang optimal.</p>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="bg-white border-t border-gray-200 mt-16">
            <div class="container mx-auto px-4 py-8">
                <div class="flex flex-col md:flex-row justify-between items-center">
                    <p class="text-sm text-gray-500">
                        &copy; {{ date('Y') }} Sistem E-Learning Karyawan. All rights reserved.
                    </p>
                    <div class="flex items-center space-x-4 mt-4 md:mt-0">
                        <span class="text-sm text-gray-500">Versi 1.0.0</span>
                        <span class="text-gray-300">|</span>
                        <span class="text-sm text-gray-500">
                            <i class="fas fa-code"></i> Laravel 13
                        </span>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</body>
</html>
