{{-- views/notifications/index.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto px-3 sm:px-6 lg:px-8 py-4 sm:py-8">

    {{-- ============ HEADER ============ --}}
    <div class="flex items-center gap-3 mb-4 sm:mb-6">
        <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-2xl bg-gradient-to-br from-[#161758] to-[#27438D] flex items-center justify-center text-xl sm:text-2xl shadow-md shrink-0">
            🔔
        </div>
        <div class="min-w-0">
            <h1 class="text-lg sm:text-2xl font-bold text-[#1B1B1B] leading-tight">Notifikasi</h1>
            <p class="text-xs sm:text-sm text-gray-500">Semua pemberitahuan terbaru untuk akun Anda</p>
        </div>
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

        // Kelompokkan item yang tampil di halaman ini berdasarkan tanggal, untuk tampilan timeline.
        $now = \Carbon\Carbon::now();
        $groupLabel = function ($createdAt) use ($now) {
            if ($createdAt->isToday()) return 'Hari Ini';
            if ($createdAt->isYesterday()) return 'Kemarin';
            if ($createdAt->greaterThanOrEqualTo($now->copy()->startOfWeek())) return 'Minggu Ini';
            if ($createdAt->month === $now->month && $createdAt->year === $now->year) return 'Bulan Ini';
            return 'Lebih Lama';
        };

        $groupedItems = collect($paginator->items())->groupBy(function ($item) use ($groupLabel) {
            return $groupLabel($item['created_at']);
        });
    @endphp

    {{-- ============ FILTER CHIPS ============ --}}
    <div class="mb-4 sm:mb-6 -mx-3 sm:mx-0 px-3 sm:px-0">
        <div class="flex gap-2 overflow-x-auto pb-1 sm:flex-wrap sm:overflow-visible scrollbar-hide">
            <a href="{{ route('notifications.index') }}"
               class="shrink-0 inline-flex items-center px-3 sm:px-4 py-1.5 sm:py-2 rounded-full text-xs sm:text-sm font-medium transition-colors duration-150 whitespace-nowrap
                      {{ $selectedType === 'semua' ? 'bg-[#161758] text-white shadow-sm' : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50' }}">
                Semua Tipe
            </a>
            @foreach ($types as $key => $label)
                <a href="{{ route('notifications.index', ['type' => $key]) }}"
                   class="shrink-0 inline-flex items-center gap-1 px-3 sm:px-4 py-1.5 sm:py-2 rounded-full text-xs sm:text-sm font-medium transition-colors duration-150 whitespace-nowrap
                          {{ $selectedType === $key ? 'bg-[#161758] text-white shadow-sm' : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50' }}">
                    <span>{{ $icons[$key] ?? '🔔' }}</span>
                    <span>{{ $label }}</span>
                </a>
            @endforeach
        </div>
    </div>

    {{-- ============ LIST (grouped by tanggal) ============ --}}
    @if ($paginator->total() > 0)
        <div class="space-y-5 sm:space-y-6">
            @foreach ($groupedItems as $label => $groupItems)
                <div>
                    <p class="text-[10px] sm:text-xs font-bold text-[#27438D] uppercase tracking-wider mb-2 px-1">{{ $label }}</p>
                    <div class="bg-white rounded-2xl shadow-sm overflow-hidden border border-gray-100">
                        @foreach ($groupItems as $item)
                            <a href="{{ $item['url'] }}"
                                class="flex items-start gap-3 sm:gap-4 px-3 sm:px-5 py-3 sm:py-4 border-b border-gray-100 last:border-b-0 hover:bg-[#F5F5F5] active:bg-gray-100 transition-colors duration-150 group">
                                <div class="flex-shrink-0 w-9 h-9 sm:w-11 sm:h-11 rounded-full flex items-center justify-center text-base sm:text-lg shadow-sm {{ $colors[$item['color']] ?? 'bg-gray-100 text-gray-700' }}">
                                    {{ $icons[$item['type']] ?? '🔔' }}
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm sm:text-base font-semibold text-[#1B1B1B] leading-snug">{{ $item['title'] }}</p>
                                    <p class="text-xs sm:text-sm text-gray-600 mt-0.5 sm:mt-1 break-words leading-relaxed">{{ $item['message'] }}</p>
                                    <p class="text-[10px] sm:text-xs text-gray-400 mt-1.5 sm:mt-2 flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        {{ $item['created_at']->translatedFormat('d F Y, H:i') }}
                                        <span class="text-gray-300">&middot;</span>
                                        {{ $item['created_at']->diffForHumans() }}
                                    </p>
                                </div>
                                <svg class="w-4 h-4 sm:w-5 sm:h-5 text-gray-300 group-hover:text-[#27438D] transition-colors shrink-0 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-5 sm:mt-6">
            {{ $paginator->links() }}
        </div>
    @else
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 text-center py-14 sm:py-20 px-4">
            <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-full bg-gray-50 flex items-center justify-center text-3xl sm:text-4xl mx-auto mb-3 sm:mb-4">🔔</div>
            <p class="text-sm sm:text-base font-semibold text-gray-700">
                Belum ada notifikasi{{ $selectedType !== 'semua' ? ' untuk filter ini' : '' }}.
            </p>
            <p class="text-xs sm:text-sm text-gray-400 mt-1">Notifikasi baru akan muncul di sini secara otomatis.</p>
            @if ($selectedType !== 'semua')
                <a href="{{ route('notifications.index') }}"
                   class="inline-flex items-center gap-1.5 mt-4 text-xs sm:text-sm font-medium text-[#00a2e9] hover:text-[#27438D] transition-colors">
                    Lihat semua tipe notifikasi
                </a>
            @endif
        </div>
    @endif

</div>

<style>
.scrollbar-hide::-webkit-scrollbar { display: none; }
.scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
</style>
@endsection
