@props([
    'type' => 'success',
    'message' => null,
    'dismissible' => true,
])

@php
    $typeConfig = [
        'success' => [
            'bg' => 'bg-emerald-500/10 border-emerald-500/20 text-emerald-800',
            'iconBg' => 'bg-[#2E7D3E] text-white',
            'icon' => 'fa-check',
        ],
        'error' => [
            'bg' => 'bg-rose-500/10 border-rose-500/20 text-rose-800',
            'iconBg' => 'bg-[#ec1d1d] text-white',
            'icon' => 'fa-circle-xmark',
        ],
        'warning' => [
            'bg' => 'bg-amber-500/10 border-amber-500/20 text-amber-800',
            'iconBg' => 'bg-[#FCC626] text-[#161758]',
            'icon' => 'fa-triangle-exclamation',
        ],
        'info' => [
            'bg' => 'bg-sky-500/10 border-sky-500/20 text-[#27438D]',
            'iconBg' => 'bg-[#00a2e9] text-white',
            'icon' => 'fa-circle-info',
        ],
    ];

    $cfg = $typeConfig[$type] ?? $typeConfig['info'];
@endphp

<div {{ $attributes->merge(['class' => "border rounded-2xl p-4 flex items-center justify-between shadow-sm transition-all duration-300 {$cfg['bg']}"]) }} role="alert">
    <div class="flex items-center gap-3">
        <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-xl {{ $cfg['iconBg'] }} flex items-center justify-center flex-shrink-0 shadow-sm text-sm">
            <i class="fa-solid {{ $cfg['icon'] }}"></i>
        </div>
        <div class="text-xs sm:text-sm font-medium">
            {{ $message ?? $slot }}
        </div>
    </div>
    @if($dismissible)
        <button type="button" onclick="this.closest('[role=alert]').remove()" class="opacity-70 hover:opacity-100 p-1.5 rounded-lg transition-opacity ml-3" aria-label="Close">
            <i class="fa-solid fa-xmark text-sm"></i>
        </button>
    @endif
</div>
