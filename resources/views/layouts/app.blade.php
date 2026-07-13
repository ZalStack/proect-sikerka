<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'SIKEKAR') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Inter:wght@300;400;500;600;700&family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
        }
        ::-webkit-scrollbar-track {
            background: #161758;
        }
        ::-webkit-scrollbar-thumb {
            background: #27438D;
            border-radius: 3px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #00a2e9;
        }
    </style>
</head>
<body class="font-['Poppins'] antialiased bg-[#F5F5F5]">
    <div class="min-h-screen bg-[#F5F5F5]">
        @include('layouts.navigation')

        <div class="flex pt-16">
            @include('layouts.sidebar')

            <!-- Main Content -->
            <div id="mainContent" class="flex-1 md:ml-64 min-h-screen">
                <main class="p-4 md:p-6">
                    @yield('content')
                </main>
            </div>
        </div>

        @include('layouts.footer')
    </div>

    <!-- Overlay for mobile sidebar -->
    <div id="sidebarOverlay" class="fixed inset-0 bg-black/50 z-30 hidden md:hidden"></div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');

            // Fungsi untuk toggle sidebar
            window.toggleSidebar = function() {
                const isOpen = sidebar.classList.contains('translate-x-0');
                if (isOpen) {
                    sidebar.classList.remove('translate-x-0');
                    sidebar.classList.add('-translate-x-full');
                    overlay.classList.add('hidden');
                    document.body.style.overflow = '';
                } else {
                    sidebar.classList.remove('-translate-x-full');
                    sidebar.classList.add('translate-x-0');
                    overlay.classList.remove('hidden');
                    document.body.style.overflow = 'hidden';
                }
            };

            // Event listener untuk overlay
            if (overlay) {
                overlay.addEventListener('click', window.toggleSidebar);
            }

            // Close sidebar on escape key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && sidebar && sidebar.classList.contains('translate-x-0')) {
                    window.toggleSidebar();
                }
            });

            // Auto close on window resize to desktop
            window.addEventListener('resize', function() {
                if (window.innerWidth >= 768 && sidebar && sidebar.classList.contains('translate-x-0')) {
                    sidebar.classList.remove('translate-x-0');
                    sidebar.classList.add('-translate-x-full');
                    if (overlay) overlay.classList.add('hidden');
                    document.body.style.overflow = '';
                }
            });
        });
    </script>
</body>
</html>
