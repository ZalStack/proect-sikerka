@props([
    'status' => 'pending',
    'size' => 'md',
])

@php
    $normalized = strtolower(trim((string)$status));
    
    $colorMap = [
        'disetujui' => 'bg-[#2E7D3E]/10 text-[#2E7D3E] border-[#2E7D3E]/20',
        'approved' => 'bg-[#2E7D3E]/10 text-[#2E7D3E] border-[#2E7D3E]/20',
        'hadir' => 'bg-[#2E7D3E]/10 text-[#2E7D3E] border-[#2E7D3E]/20',
        'aktif' => 'bg-[#2E7D3E]/10 text-[#2E7D3E] border-[#2E7D3E]/20',
        'karyawan tetap' => 'bg-[#2E7D3E]/10 text-[#2E7D3E] border-[#2E7D3E]/20',
        
        'menunggu' => 'bg-[#FCC626]/20 text-[#a37500] border-[#FCC626]/30',
        'pending' => 'bg-[#FCC626]/20 text-[#a37500] border-[#FCC626]/30',
        'terlambat' => 'bg-[#FCC626]/20 text-[#a37500] border-[#FCC626]/30',
        'contract' => 'bg-[#FCC626]/20 text-[#a37500] border-[#FCC626]/30',
        
        'ditolak' => 'bg-[#ec1d1d]/10 text-[#ec1d1d] border-[#ec1d1d]/20',
        'rejected' => 'bg-[#ec1d1d]/10 text-[#ec1d1d] border-[#ec1d1d]/20',
        'alpha' => 'bg-[#ec1d1d]/10 text-[#ec1d1d] border-[#ec1d1d]/20',
        'tidak hadir' => 'bg-[#ec1d1d]/10 text-[#ec1d1d] border-[#ec1d1d]/20',
        
        'izin' => 'bg-[#00a2e9]/10 text-[#0081bb] border-[#00a2e9]/20',
        'sakit' => 'bg-amber-500/10 text-amber-700 border-amber-500/20',
        'cuti' => 'bg-purple-500/10 text-purple-700 border-purple-500/20',
        'perjalanan dinas' => 'bg-[#27438D]/10 text-[#27438D] border-[#27438D]/20',
        'internship' => 'bg-[#00a2e9]/10 text-[#0081bb] border-[#00a2e9]/20',
    ];

    $classes = $colorMap[$normalized] ?? 'bg-slate-100 text-slate-700 border-slate-200';
    $sizeClass = $size === 'sm' ? 'px-2 py-0.5 text-[11px]' : 'px-2.5 py-1 text-xs';
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center gap-1 font-semibold rounded-full border {$sizeClass} {$classes}"]) }}>
    {{ $slot->isEmpty() ? ucfirst($status) : $slot }}
</span>
