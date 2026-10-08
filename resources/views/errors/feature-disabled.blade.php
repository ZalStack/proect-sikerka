@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-16">
    <div class="bg-white rounded-3xl shadow-xl border border-gray-100 overflow-hidden text-center p-8 sm:p-12 relative">
        <!-- Glow Effect -->
        <div class="absolute -top-24 -right-24 w-60 h-60 bg-blue-100 rounded-full blur-3xl pointer-events-none opacity-60"></div>
        <div class="absolute -bottom-24 -left-24 w-60 h-60 bg-amber-100 rounded-full blur-3xl pointer-events-none opacity-60"></div>

        <div class="relative z-10 space-y-6">
            <!-- Icon -->
            <div class="w-20 h-20 sm:w-24 sm:h-24 mx-auto rounded-3xl bg-amber-50 border border-amber-200 flex items-center justify-center text-amber-500 shadow-inner">
                <i class="fa-solid fa-lock text-3xl sm:text-4xl"></i>
            </div>

            <!-- Header -->
            <div class="space-y-2">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 border border-amber-200">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    Fitur Dinonaktifkan
                </span>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-[#0F1245] font-['Montserrat']">
                    {{ $meta['name'] ?? 'Fitur Ini Sedang Ditutup' }}
                </h1>
                <p class="text-sm sm:text-base text-gray-600 max-w-lg mx-auto font-light leading-relaxed">
                    Mohon maaf, fitur ini sedang dinonaktifkan sementara oleh Super Administrator untuk pemeliharaan berkala atau penyesuaian operasional.
                </p>
            </div>

            <!-- Feature Info Box -->
            <div class="bg-gray-50 rounded-2xl p-4 sm:p-5 max-w-md mx-auto text-left border border-gray-200/80 space-y-2">
                <div class="flex items-center justify-between text-xs text-gray-500 font-medium">
                    <span>Kategori Modul:</span>
                    <span class="font-semibold text-gray-800">{{ $meta['category'] ?? 'Sistem' }}</span>
                </div>
                <div class="flex items-center justify-between text-xs text-gray-500 font-medium">
                    <span>Status Fitur:</span>
                    <span class="inline-flex items-center gap-1 font-semibold text-rose-600">
                        <span class="w-1.5 h-1.5 rounded-full bg-rose-600"></span>
                        Non-Aktif (Maintenance)
                    </span>
                </div>
                <p class="text-xs text-gray-500 pt-2 border-t border-gray-200">
                    {{ $meta['description'] ?? 'Silakan hubungi administrator atau coba kembali beberapa saat lagi.' }}
                </p>
            </div>

            <!-- Action buttons -->
            <div class="pt-4 flex flex-col sm:flex-row items-center justify-center gap-3">
                <a href="{{ auth()->user() && auth()->user()->posisi === 'hr' ? route('hr.dashboard') : (auth()->user() && auth()->user()->posisi === 'superadmin' ? route('superadmin.dashboard') : route('karyawan.dashboard')) }}"
                   class="w-full sm:w-auto px-6 py-2.5 rounded-xl bg-gradient-to-r from-[#161758] to-[#27438D] hover:from-[#0F1245] hover:to-[#161758] text-white font-medium text-sm shadow-md transition-all flex items-center justify-center gap-2">
                    <i class="fa-solid fa-arrow-left text-xs"></i>
                    <span>Kembali ke Dashboard</span>
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
