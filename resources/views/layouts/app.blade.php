{{-- views/layouts/app.blade.php --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#0F1245">
    <title>{{ config('app.name', 'SIKEKAR') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.17/index.global.min.css">
</head>
<body class="font-['Poppins'] antialiased bg-[#eef2f9] overflow-x-hidden text-slate-800">

    {{-- ==================== DECORATIVE BACKGROUND (behind everything) ==================== --}}
    <div class="pointer-events-none fixed inset-0 -z-10 overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-br from-[#eaf1ff] via-[#f3f5fb] to-[#e7edf9]"></div>
        <div class="absolute -top-40 -left-32 w-[36rem] h-[36rem] rounded-full bg-[#00a2e9]/10 blur-3xl"></div>
        <div class="absolute top-1/3 -right-40 w-[32rem] h-[32rem] rounded-full bg-[#FCC626]/10 blur-3xl"></div>
        <div class="absolute -bottom-40 left-1/3 w-[30rem] h-[30rem] rounded-full bg-[#27438D]/10 blur-3xl"></div>
        <div class="absolute inset-0 opacity-[0.035]"
             style="background-image: radial-gradient(circle at 1px 1px, #161758 1px, transparent 0); background-size: 26px 26px;"></div>
    </div>

    <div class="min-h-screen flex flex-col relative">
        @include('layouts.navigation')
        <!-- Page Content -->
        <main class="flex-1 pt-14 sm:pt-16">
            @yield('content')
        </main>
        @include('layouts.footer')
    </div>

    <style>
        html { scroll-behavior: smooth; }
        * { -webkit-tap-highlight-color: transparent; }
        ::selection { background: rgba(0,162,233,0.22); }

        /* Refined focus ring for accessibility across the whole app */
        :focus-visible {
            outline: none;
            box-shadow: 0 0 0 3px rgba(0,162,233,0.35);
            border-radius: 0.75rem;
        }

        /* Slim, modern scrollbars */
        ::-webkit-scrollbar { width: 10px; height: 10px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb {
            background: linear-gradient(180deg, #c3ccdd, #aab6cc);
            border-radius: 9999px;
            border: 2px solid transparent;
            background-clip: padding-box;
        }
        ::-webkit-scrollbar-thumb:hover { background: linear-gradient(180deg, #00a2e9, #27438D); background-clip: padding-box; }

        /* Birthday / decorative animations (used by several pages) */
        @keyframes gradient-x {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }
        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-12px) rotate(5deg); }
        }
        @keyframes float-slow {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-8px) rotate(-3deg); }
        }
        @keyframes pulse-slow {
            0%, 100% { transform: translate(-50%, -50%) scale(1); opacity: 0.2; }
            50% { transform: translate(-50%, -50%) scale(1.2); opacity: 0.3; }
        }
        @keyframes fadeSlideUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-gradient-x {
            background-size: 200% 200%;
            animation: gradient-x 4s ease infinite;
        }
        .animate-float { animation: float 3s ease-in-out infinite; }
        .animate-float-slow { animation: float-slow 4s ease-in-out infinite; }
        .animate-pulse-slow { animation: pulse-slow 3s ease-in-out infinite; }
        @keyframes bounce-slow {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-8px); }
        }
        .animate-bounce-slow { animation: bounce-slow 2s ease-in-out infinite; }

        /* Respect users who prefer reduced motion */
        @media (prefers-reduced-motion: reduce) {
            html { scroll-behavior: auto; }
            *, *::before, *::after {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }
        }
    </style>
    @stack('scripts')
</body>
</html>
