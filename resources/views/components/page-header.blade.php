@props([
    'title',
    'subtitle' => null,
    'icon' => null,
])

<div {{ $attributes->merge(['class' => 'flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 sm:gap-4 mb-6']) }}>
    <div class="flex items-center gap-3">
        @if($icon)
            <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-2xl bg-gradient-to-br from-[#161758] to-[#27438D] text-white flex items-center justify-center text-lg sm:text-xl shadow-md shrink-0">
                <i class="fa-solid {{ $icon }}"></i>
            </div>
        @endif
        <div>
            <h1 class="text-xl sm:text-2xl font-bold font-['Montserrat'] text-[#161758] tracking-tight">
                {{ $title }}
            </h1>
            @if($subtitle)
                <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                    {{ $subtitle }}
                </p>
            @endif
        </div>
    </div>
    @if(isset($actions) && $actions->isNotEmpty())
        <div class="w-full sm:w-auto flex flex-wrap items-center gap-2 sm:gap-3">
            {{ $actions }}
        </div>
    @elseif($slot->isNotEmpty())
        <div class="w-full sm:w-auto flex flex-wrap items-center gap-2 sm:gap-3">
            {{ $slot }}
        </div>
    @endif
</div>
