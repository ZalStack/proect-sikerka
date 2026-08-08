{{-- views/karyawan/pengumuman/show.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto px-3 sm:px-6 lg:px-8 py-4 sm:py-8">

    {{-- Breadcrumb / Back --}}
    <div class="mb-4 sm:mb-6">
        <a href="{{ route('karyawan.dashboard') }}"
            class="inline-flex items-center gap-1.5 text-xs sm:text-sm font-medium text-[#00a2e9] hover:text-[#0077b6] transition-colors">
            <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Kembali ke Dashboard
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm overflow-hidden">

        @if ($pengumuman->gambar)
            <img src="{{ Storage::url($pengumuman->gambar) }}"
                alt="{{ $pengumuman->judul }}"
                class="w-full max-h-64 sm:max-h-96 object-cover">
        @endif

        <div class="p-4 sm:p-6 lg:p-8">
            <div class="flex flex-wrap items-center gap-2 mb-3">
                <span class="inline-flex items-center gap-1 text-[10px] sm:text-xs font-semibold px-2 sm:px-2.5 py-1 rounded-full bg-blue-100 text-blue-700">
                    📢 Pengumuman
                </span>
            </div>

            <h1 class="text-lg sm:text-2xl lg:text-3xl font-bold text-[#1B1B1B] leading-snug break-words">
                {{ $pengumuman->judul }}
            </h1>

            <div class="flex flex-wrap items-center gap-x-4 gap-y-1 mt-3 mb-5 sm:mb-6 text-[11px] sm:text-xs text-gray-500">
                <span class="flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    {{ $pengumuman->creator->nama_lengkap ?? 'HR' }}
                </span>
                <span class="flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Dibuat {{ $pengumuman->created_at->translatedFormat('d F Y, H:i') }}
                </span>
                @if ($pengumuman->updated_at->gt($pengumuman->created_at->copy()->addMinute()))
                    <span class="flex items-center gap-1.5 text-amber-600">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        Diperbarui {{ $pengumuman->updated_at->diffForHumans() }}
                    </span>
                @endif
            </div>

            <div class="prose prose-sm sm:prose max-w-none text-sm sm:text-base text-gray-700 whitespace-pre-line break-words">
                {{ $pengumuman->isi }}
            </div>
        </div>
    </div>
</div>
@endsection
