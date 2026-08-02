{{-- views/notifications/index.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-3 sm:px-6 lg:px-8 py-4 sm:py-8">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4 sm:mb-6">
        <div>
            <h1 class="text-lg sm:text-2xl font-bold text-[#1B1B1B]">Notifikasi</h1>
            <p class="text-xs sm:text-sm text-gray-500">Semua pemberitahuan terbaru untuk akun Anda</p>
        </div>

        <form method="GET" action="{{ route('notifications.index') }}" class="flex items-center gap-2">
            <label for="type" class="text-xs sm:text-sm text-gray-600 whitespace-nowrap">Filter:</label>
            <select id="type" name="type" onchange="this.form.submit()"
                class="text-xs sm:text-sm border border-gray-300 rounded-lg px-2.5 sm:px-3 py-1.5 sm:py-2 focus:outline-none focus:ring-2 focus:ring-[#00a2e9] bg-white">
                <option value="semua" {{ $selectedType === 'semua' ? 'selected' : '' }}>Semua Tipe</option>
                @foreach ($types as $key => $label)
                    <option value="{{ $key }}" {{ $selectedType === $key ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </form>
    </div>

    @php
        $icons = [
            'pengumuman' => '📢', 'cuti' => '🗓️', 'dinas' => '🧳', 'sunnah' => '🌙',
            'absensi' => '⚠️', 'profile' => '👤', 'fhl' => '🕌', 'khataman' => '📖',
        ];
        $colors = [
            'blue' => 'bg-blue-100 text-blue-700',
            'amber' => 'bg-amber-100 text-amber-700',
            'violet' => 'bg-violet-100 text-violet-700',
            'emerald' => 'bg-emerald-100 text-emerald-700',
            'rose' => 'bg-rose-100 text-rose-700',
            'sky' => 'bg-sky-100 text-sky-700',
            'teal' => 'bg-teal-100 text-teal-700',
            'indigo' => 'bg-indigo-100 text-indigo-700',
            'cyan' => 'bg-cyan-100 text-cyan-700',
        ];
    @endphp

    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        @forelse ($paginator as $item)
            <a href="{{ $item['url'] }}"
                class="flex items-start gap-3 sm:gap-4 px-3 sm:px-5 py-3 sm:py-4 border-b border-gray-100 last:border-b-0 hover:bg-[#F5F5F5] transition">
                <div class="flex-shrink-0 w-9 h-9 sm:w-10 sm:h-10 rounded-full flex items-center justify-center text-base sm:text-lg {{ $colors[$item['color']] ?? 'bg-gray-100 text-gray-700' }}">
                    {{ $icons[$item['type']] ?? '🔔' }}
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-sm sm:text-base font-semibold text-[#1B1B1B]">{{ $item['title'] }}</p>
                    <p class="text-xs sm:text-sm text-gray-600 mt-0.5 break-words">{{ $item['message'] }}</p>
                    <p class="text-[10px] sm:text-xs text-gray-400 mt-1">
                        {{ $item['created_at']->translatedFormat('d F Y, H:i') }} &middot; {{ $item['created_at']->diffForHumans() }}
                    </p>
                </div>
            </a>
        @empty
            <div class="text-center py-12 sm:py-16 px-4">
                <div class="text-3xl sm:text-4xl mb-2">🔔</div>
                <p class="text-sm sm:text-base text-gray-500">
                    Belum ada notifikasi{{ $selectedType !== 'semua' ? ' untuk filter ini' : '' }}.
                </p>
            </div>
        @endforelse
    </div>

    @if ($paginator->total() > 0)
        <div class="mt-4 sm:mt-6">
            {{ $paginator->links() }}
        </div>
    @endif

</div>
@endsection
