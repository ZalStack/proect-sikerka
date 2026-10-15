@props([
    'title',
    'value',
    'icon' => 'fa-chart-line',
    'color' => 'blue',
    'subtext' => null,
])

@php
    $colorClasses = [
        'blue' => [
            'icon' => 'bg-[#00a2e9]/10 text-[#00a2e9]',
            'border' => 'border-l-4 border-l-[#00a2e9]',
            'accent' => 'text-[#00a2e9]',
        ],
        'navy' => [
            'icon' => 'bg-[#161758]/10 text-[#161758]',
            'border' => 'border-l-4 border-l-[#161758]',
            'accent' => 'text-[#161758]',
        ],
        'gold' => [
            'icon' => 'bg-[#FCC626]/20 text-[#d49b06]',
            'border' => 'border-l-4 border-l-[#FCC626]',
            'accent' => 'text-[#d49b06]',
        ],
        'green' => [
            'icon' => 'bg-[#2E7D3E]/10 text-[#2E7D3E]',
            'border' => 'border-l-4 border-l-[#2E7D3E]',
            'accent' => 'text-[#2E7D3E]',
        ],
        'red' => [
            'icon' => 'bg-[#ec1d1d]/10 text-[#ec1d1d]',
            'border' => 'border-l-4 border-l-[#ec1d1d]',
            'accent' => 'text-[#ec1d1d]',
        ],
    ];

    $cfg = $colorClasses[$color] ?? $colorClasses['blue'];
@endphp

<div {{ $attributes->merge(['class' => "bg-white rounded-2xl p-4 sm:p-5 shadow-sm border border-slate-100 hover:shadow-md transition-all duration-300 flex items-center justify-between {$cfg['border']}"]) }}>
    <div class="space-y-1">
        <p class="text-xs sm:text-sm font-medium text-slate-500 uppercase tracking-wider">{{ $title }}</p>
        <p class="text-xl sm:text-2xl lg:text-3xl font-bold font-['Poppins'] text-slate-800">{{ $value }}</p>
        @if($subtext)
            <p class="text-xs text-slate-400">{{ $subtext }}</p>
        @endif
    </div>
    <div class="w-11 h-11 sm:w-13 sm:h-13 rounded-2xl {{ $cfg['icon'] }} flex items-center justify-center text-lg sm:text-xl flex-shrink-0 shadow-sm">
        <i class="fa-solid {{ $icon }}"></i>
    </div>
</div>
