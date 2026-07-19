{{-- views/hr/pengumuman/show.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="flex min-h-screen">
    @include('layouts.sidebar')
    <div class="flex-1 transition-all duration-300 md:ml-64 pt-6">
        <div class="p-4 sm:p-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-3 sm:gap-4">
                <div>
                    <h1 class="text-xl sm:text-2xl font-bold font-['Montserrat'] text-[#161758]">Detail Pengumuman</h1>
                    <p class="text-sm sm:text-base text-[#27438D]">Informasi lengkap pengumuman</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('hr.pengumuman.edit', $pengumuman->id) }}"
                       class="bg-[#00a2e9] text-white px-3 sm:px-4 py-2 rounded-lg hover:bg-[#27438D] transition-colors duration-200 text-sm sm:text-base">
                        Edit
                    </a>
                    <a href="{{ route('hr.pengumuman.index') }}"
                       class="bg-gray-500 text-white px-3 sm:px-4 py-2 rounded-lg hover:bg-gray-600 transition-colors duration-200 text-sm sm:text-base">
                        Kembali
                    </a>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="p-4 sm:p-6">
                    @if($pengumuman->gambar)
                        <div class="mb-6">
                            <img src="{{ Storage::url($pengumuman->gambar) }}" alt="{{ $pengumuman->judul }}"
                                 class="w-full max-h-96 object-cover rounded-lg">
                        </div>
                    @endif

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
                        <div class="space-y-3 sm:space-y-4">
                            <div>
                                <label class="text-xs sm:text-sm text-[#1B1B1B] font-medium">Judul</label>
                                <h2 class="text-lg sm:text-xl font-bold text-[#161758] break-words">{{ $pengumuman->judul }}</h2>
                            </div>
                            <div>
                                <label class="text-xs sm:text-sm text-[#1B1B1B] font-medium">Isi Pengumuman</label>
                                <div class="text-sm sm:text-base text-[#27438D] whitespace-pre-wrap break-words mt-1">{{ $pengumuman->isi }}</div>
                            </div>
                        </div>

                        <div class="space-y-3 sm:space-y-4">
                            <div>
                                <label class="text-xs sm:text-sm text-[#1B1B1B] font-medium">Target</label>
                                <p class="text-sm sm:text-base text-[#27438D] break-words">{{ $pengumuman->target_label }}</p>
                            </div>
                            <div>
                                <label class="text-xs sm:text-sm text-[#1B1B1B] font-medium">Status WhatsApp</label>
                                <p class="text-sm sm:text-base text-[#27438D]">
                                    <span class="px-2 py-1 rounded-full text-xs font-medium {{ $pengumuman->whatsapp_status_color }}">
                                        @if($pengumuman->whatsapp_status == 'sent')
                                            ✅ Terkirim
                                        @elseif($pengumuman->whatsapp_status == 'pending')
                                            ⏳ Menunggu
                                        @else
                                            ❌ Gagal
                                        @endif
                                    </span>
                                    @if($pengumuman->sent_at)
                                        <div class="text-xs sm:text-sm mt-1">Dikirim: {{ $pengumuman->sent_at->format('d-m-Y H:i') }}</div>
                                    @endif
                                </p>
                            </div>
                            <div>
                                <label class="text-xs sm:text-sm text-[#1B1B1B] font-medium">Dibuat Oleh</label>
                                <p class="text-sm sm:text-base text-[#27438D] break-words">{{ $pengumuman->creator->nama_lengkap ?? 'HR' }}</p>
                            </div>
                            <div>
                                <label class="text-xs sm:text-sm text-[#1B1B1B] font-medium">Tanggal Dibuat</label>
                                <p class="text-sm sm:text-base text-[#27438D]">{{ $pengumuman->created_at->format('d-m-Y H:i:s') }}</p>
                            </div>
                        </div>
                    </div>

                    @if($pengumuman->whatsapp_status != 'sent')
                    <div class="mt-6 pt-6 border-t border-gray-200">
                        <form action="{{ route('hr.pengumuman.resend-whatsapp', $pengumuman->id) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="w-full sm:w-auto bg-[#25D366] text-white px-4 sm:px-6 py-2 rounded-lg hover:bg-green-700 transition-colors duration-200 text-sm sm:text-base">
                                📤 Kirim ke WhatsApp
                            </button>
                        </form>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
