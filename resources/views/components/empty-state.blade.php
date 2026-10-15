@props([
    'icon' => 'fa-inbox',
    'title' => 'Belum ada data',
    'message' => 'Data yang Anda cari tidak ditemukan atau belum ditambahkan.',
])

<div {{ $attributes->merge(['class' => 'bg-white rounded-2xl shadow-sm border border-slate-100 text-center py-12 sm:py-16 px-4']) }}>
    <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-2xl bg-slate-50 text-slate-400 flex items-center justify-center text-2xl sm:text-3xl mx-auto mb-3 shadow-inner">
        <i class="fa-solid {{ $icon }}"></i>
    </div>
    <h3 class="text-sm sm:text-base font-semibold text-slate-700">
        {{ $title }}
    </h3>
    @if($message)
        <p class="text-xs sm:text-sm text-slate-400 mt-1 max-w-sm mx-auto">
            {{ $message }}
        </p>
    @endif
    @if($slot->isNotEmpty())
        <div class="mt-4">
            {{ $slot }}
        </div>
    @endif
</div>
