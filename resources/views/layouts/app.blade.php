{{-- views/layouts/app.blade.php --}}
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.17/index.global.min.css">
</head>
<body class="font-['Poppins'] antialiased bg-gradient-to-br from-[#f0f4ff] via-[#f5f5f5] to-[#e8f0fe] overflow-x-hidden">
    <div class="min-h-screen flex flex-col">
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
        ::selection { background: rgba(0,162,233,0.2); }
    </style>
    @stack('scripts')
</body>
</html>
