<header class="bg-white shadow-md sticky top-0 z-50">
    <nav class="container mx-auto px-4 py-3 flex items-center justify-between">
        <!-- Logo -->
        <div class="flex items-center space-x-4">
            <a href="{{ route('home') }}" class="flex items-center space-x-2">
                <div class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center">
                    <i class="fas fa-graduation-cap text-white text-xl"></i>
                </div>
                <span class="text-xl font-bold text-gray-800 hidden sm:block">
                    E-Learning
                </span>
            </a>
        </div>

        <!-- Navigation Links -->
        <div class="hidden md:flex items-center space-x-6">
            @auth
                @if(auth()->user()->role === 'admin')
                    <a href="{{ route('admin.dashboard') }}" class="text-gray-600 hover:text-blue-600 transition">
                        <i class="fas fa-home mr-1"></i> Dashboard
                    </a>
                    <a href="{{ route('admin.materials.index') }}" class="text-gray-600 hover:text-blue-600 transition">
                        <i class="fas fa-book mr-1"></i> Materi
                    </a>
                    <a href="{{ route('admin.quizzes.index') }}" class="text-gray-600 hover:text-blue-600 transition">
                        <i class="fas fa-question-circle mr-1"></i> Quiz
                    </a>
                    <a href="{{ route('admin.employees.index') }}" class="text-gray-600 hover:text-blue-600 transition">
                        <i class="fas fa-users mr-1"></i> Karyawan
                    </a>
                @else
                    <a href="{{ route('employee.dashboard') }}" class="text-gray-600 hover:text-blue-600 transition">
                        <i class="fas fa-home mr-1"></i> Dashboard
                    </a>
                    <a href="{{ route('employee.learning') }}" class="text-gray-600 hover:text-blue-600 transition">
                        <i class="fas fa-book mr-1"></i> Belajar
                    </a>
                    <a href="{{ route('employee.quiz.index') }}" class="text-gray-600 hover:text-blue-600 transition">
                        <i class="fas fa-pencil-alt mr-1"></i> Quiz
                    </a>
                @endif
            @endauth
        </div>

        <!-- Right Section -->
        <div class="flex items-center space-x-4">
            @auth
                <!-- Notification -->
                <button class="relative text-gray-600 hover:text-blue-600 transition">
                    <i class="fas fa-bell text-xl"></i>
                    <span class="absolute -top-1 -right-1 w-4 h-4 bg-red-500 text-white text-xs rounded-full flex items-center justify-center">
                        3
                    </span>
                </button>

                <!-- User Dropdown -->
                <div class="relative" x-data="{ open: false }" @click.away="open = false">
                    <button @click="open = !open" class="flex items-center space-x-2 hover:bg-gray-100 rounded-lg px-3 py-2 transition">
                        <div class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center text-white font-semibold">
                            {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                        </div>
                        <span class="hidden md:block text-sm font-medium text-gray-700">
                            {{ auth()->user()->name }}
                        </span>
                        <i class="fas fa-chevron-down text-gray-400 text-xs"></i>
                    </button>

                    <div x-show="open" x-transition class="absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-lg py-1 border border-gray-100 z-50">
                        <div class="px-4 py-3 border-b border-gray-100">
                            <p class="text-sm font-medium text-gray-900">{{ auth()->user()->name }}</p>
                            <p class="text-xs text-gray-500">{{ auth()->user()->email ?? 'Tidak ada email' }}</p>
                        </div>
                        <a href="{{ route('profile') }}" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition">
                            <i class="fas fa-user mr-3 text-gray-400"></i> Profil
                        </a>
                        <a href="{{ route('profile.settings') }}" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition">
                            <i class="fas fa-cog mr-3 text-gray-400"></i> Pengaturan
                        </a>
                        <form method="POST" action="{{ route('logout') }}" class="border-t border-gray-100">
                            @csrf
                            <button type="submit" class="flex items-center w-full px-4 py-2 text-sm text-red-600 hover:bg-gray-50 transition">
                                <i class="fas fa-sign-out-alt mr-3 text-red-400"></i> Logout
                            </button>
                        </form>
                    </div>
                </div>
            @else
                <a href="{{ route('login') }}" class="text-gray-600 hover:text-blue-600 transition">
                    <i class="fas fa-sign-in-alt mr-1"></i> Login
                </a>
            @endauth
        </div>
    </nav>
</header>
