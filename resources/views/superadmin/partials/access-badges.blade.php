{{-- Badge status akun (suspend/aktif/resign) + daftar menu nonaktif untuk satu karyawan --}}
@php
    $k = $k ?? null;
    $isResign = (bool) ($k->is_resigned ?? false);
    $today = \Carbon\Carbon::today();
    $hasRange = $k->suspend_start && $k->suspend_end;
    $isScheduled = $hasRange && $k->suspend_start->gt($today);
    $isSuspended = !$isResign && $hasRange && !$isScheduled;
    $daysLeft = $isSuspended ? $k->suspensionDaysRemaining() : 0;
    $disabledKeys = $k->disabledMenuKeys();
    $self = (int) $k->id === (int) auth()->id();
@endphp

<div class="flex flex-col gap-1.5">
    {{-- Status akun --}}
    @if($isResign)
        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-gray-100 text-gray-600 border border-gray-200 w-fit">
            <i class="fa-solid fa-person-circle-minus"></i>
            RESIGN
        </span>
    @elseif($isSuspended)
        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-rose-100 text-rose-700 border border-rose-200 w-fit"
              title="Alasan: {{ $k->suspensionReasonLabel() }}">
            <i class="fa-solid fa-user-lock"></i>
            DISUSPEND s/d {{ $k->suspend_end->isoFormat('D MMM Y') }}
            <span class="px-1.5 py-0.5 rounded-full bg-rose-600 text-white text-[10px]">{{ $daysLeft }} hr</span>
        </span>
        <span class="text-[11px] text-gray-400">
            Mulai {{ $k->suspend_start->isoFormat('D MMM Y') }}
        </span>
    @elseif($isScheduled)
        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-100 text-amber-700 border border-amber-200 w-fit"
              title="Alasan: {{ $k->suspensionReasonLabel() }}">
            <i class="fa-solid fa-clock"></i>
            TERJADWAL mulai {{ $k->suspend_start->isoFormat('D MMM Y') }}
        </span>
    @else
        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-700 border border-emerald-200 w-fit">
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
            AKTIF
        </span>
    @endif

    {{-- Menu yang dinonaktifkan --}}
    @if(count($disabledKeys) > 0)
        <div class="flex flex-wrap gap-1">
            @foreach(array_slice($disabledKeys, 0, 3) as $key)
                <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-[#0F1245]/10 text-[#0F1245] border border-[#0F1245]/15"
                      title="{{ $menuMeta[$key]['name'] ?? $key }} nonaktif">
                    {{ \Illuminate\Support\Str::of($menuMeta[$key]['name'] ?? $key)->limit(16, '…') }}
                </span>
            @endforeach
            @if(count($disabledKeys) > 3)
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#0F1245] text-white">
                    +{{ count($disabledKeys) - 3 }}
                </span>
            @endif
        </div>
    @elseif(!$self)
        <span class="text-[11px] text-gray-400">Semua menu aktif</span>
    @endif

    @if($self)
        <span class="text-[11px] font-semibold text-[#00a2e9] w-fit">
            <i class="fa-solid fa-circle-info"></i> Akun Anda sendiri
        </span>
    @endif
</div>
