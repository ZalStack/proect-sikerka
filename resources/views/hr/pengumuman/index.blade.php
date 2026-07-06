@extends('layouts.app')

@section('content')
<div class="flex">
    @include('layouts.sidebar')
    <div class="ml-64 flex-1 p-6">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
            <div>
                <h1 class="text-2xl font-bold font-['Montserrat'] text-[#161758]">Pengumuman</h1>
                <p class="text-[#27438D]">Kelola pengumuman dan kirim ke WhatsApp</p>
            </div>
            <a href="{{ route('hr.pengumuman.create') }}"
               class="bg-[#27438D] text-white px-4 py-2 rounded-lg hover:bg-[#161758] transition-colors duration-200 whitespace-nowrap">
                + Buat Pengumuman
            </a>
        </div>

        @if(session('success'))
            <div class="bg-[#2E7D3E] text-white p-4 rounded-lg mb-4">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-[#ec1d1d] text-white p-4 rounded-lg mb-4">
                {{ session('error') }}
            </div>
        @endif

        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[800px]">
                    <thead class="bg-[#F5F5F5]">
                        <tr>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-[#1B1B1B]">No</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-[#1B1B1B]">Judul</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-[#1B1B1B]">Target</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-[#1B1B1B]">WhatsApp</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-[#1B1B1B]">Dibuat</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-[#1B1B1B]">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pengumuman as $item)
                        <tr class="border-b border-gray-200 hover:bg-[#F5F5F5]">
                            <td class="px-4 py-3 text-sm">{{ $loop->iteration + ($pengumuman->currentPage() - 1) * $pengumuman->perPage() }}</td>
                            <td class="px-4 py-3 text-sm">
                                <div class="font-semibold text-[#161758]">{{ $item->judul }}</div>
                                <div class="text-xs text-gray-500 truncate max-w-xs">{{ Str::limit($item->isi, 50) }}</div>
                                @if($item->gambar)
                                    <span class="text-xs text-[#00a2e9]">📷 Ada gambar</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <span class="px-2 py-1 rounded-full text-xs font-medium
                                    {{ $item->target == 'semua' ? 'bg-[#00a2e9] text-white' :
                                       ($item->target == 'hr' ? 'bg-[#27438D] text-white' : 'bg-[#FCC626] text-[#1B1B1B]') }}">
                                    {{ $item->target_label }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                @if($item->is_sent_to_whatsapp)
                                    <span class="px-2 py-1 rounded-full text-xs font-medium bg-[#2E7D3E] text-white">
                                        ✅ Terkirim
                                    </span>
                                    <div class="text-xs text-gray-500 mt-1">
                                        {{ $item->sent_at ? $item->sent_at->format('d-m-Y H:i') : '-' }}
                                    </div>
                                @else
                                    <span class="px-2 py-1 rounded-full text-xs font-medium bg-gray-300 text-gray-600">
                                        ❌ Belum
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <div>{{ $item->created_at->format('d-m-Y') }}</div>
                                <div class="text-xs text-gray-500">{{ $item->creator ? $item->creator->nama_lengkap : '-' }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap gap-1">
                                    <a href="{{ route('hr.pengumuman.show', $item->id) }}"
                                       class="text-[#00a2e9] hover:text-[#27438D] text-sm">
                                        Detail
                                    </a>
                                    <a href="{{ route('hr.pengumuman.edit', $item->id) }}"
                                       class="text-[#FCC626] hover:text-[#e6b222] text-sm">
                                        Edit
                                    </a>
                                    @if(!$item->is_sent_to_whatsapp)
                                        <a href="{{ route('hr.pengumuman.select-contact', $item->id) }}"
                                           class="text-[#25D366] hover:text-green-700 text-sm font-semibold">
                                            📤 Kirim WA
                                        </a>
                                    @else
                                        <a href="{{ route('hr.pengumuman.select-contact', $item->id) }}"
                                           class="text-[#00a2e9] hover:text-[#27438D] text-sm">
                                            📤 Kirim Ulang
                                        </a>
                                    @endif
                                    <form action="{{ route('hr.pengumuman.destroy', $item->id) }}" method="POST" class="inline" onsubmit="return confirm('Apakah anda yakin ingin menghapus pengumuman ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-[#ec1d1d] hover:text-red-700 text-sm">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-[#1B1B1B]">
                                <div class="flex flex-col items-center">
                                    <svg class="w-16 h-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                    </svg>
                                    <p class="text-lg font-semibold">Belum ada pengumuman</p>
                                    <p class="text-sm text-gray-500 mt-1">Klik tombol "Buat Pengumuman" untuk menambahkan</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="px-4 py-4 bg-white border-t border-gray-200">
                <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="text-sm text-[#1B1B1B] order-2 sm:order-1">
                        Menampilkan
                        <span class="font-semibold">{{ $pengumuman->firstItem() ?? 0 }}</span>
                        -
                        <span class="font-semibold">{{ $pengumuman->lastItem() ?? 0 }}</span>
                        dari
                        <span class="font-semibold">{{ $pengumuman->total() }}</span>
                        data
                    </div>

                    <div class="order-1 sm:order-2 w-full sm:w-auto">
                        @if ($pengumuman->hasPages())
                            <nav class="flex justify-center" aria-label="Pagination">
                                <ul class="flex flex-wrap gap-1">
                                    {{-- Previous Page Link --}}
                                    @if ($pengumuman->onFirstPage())
                                        <li class="disabled">
                                            <span class="px-3 py-2 rounded-lg bg-gray-100 text-gray-400 cursor-not-allowed text-sm">
                                                &laquo;
                                            </span>
                                        </li>
                                    @else
                                        <li>
                                            <a href="{{ $pengumuman->previousPageUrl() }}"
                                               class="px-3 py-2 rounded-lg bg-gray-100 text-[#27438D] hover:bg-[#27438D] hover:text-white transition-colors duration-200 text-sm">
                                                &laquo;
                                            </a>
                                        </li>
                                    @endif

                                    {{-- Pagination Elements --}}
                                    @php
                                        $start = max(1, $pengumuman->currentPage() - 2);
                                        $end = min($start + 4, $pengumuman->lastPage());
                                        if ($end - $start < 4) {
                                            $start = max(1, $end - 4);
                                        }
                                    @endphp

                                    @if ($start > 1)
                                        <li>
                                            <a href="{{ $pengumuman->url(1) }}"
                                               class="px-3 py-2 rounded-lg bg-gray-100 text-[#27438D] hover:bg-[#27438D] hover:text-white transition-colors duration-200 text-sm">
                                                1
                                            </a>
                                        </li>
                                        @if ($start > 2)
                                            <li class="disabled">
                                                <span class="px-3 py-2 rounded-lg bg-gray-100 text-gray-400 cursor-not-allowed text-sm">
                                                    ...
                                                </span>
                                            </li>
                                        @endif
                                    @endif

                                    @for ($page = $start; $page <= $end; $page++)
                                        @if ($page == $pengumuman->currentPage())
                                            <li class="active">
                                                <span class="px-3 py-2 rounded-lg bg-[#27438D] text-white text-sm font-semibold">
                                                    {{ $page }}
                                                </span>
                                            </li>
                                        @else
                                            <li>
                                                <a href="{{ $pengumuman->url($page) }}"
                                                   class="px-3 py-2 rounded-lg bg-gray-100 text-[#27438D] hover:bg-[#27438D] hover:text-white transition-colors duration-200 text-sm">
                                                    {{ $page }}
                                                </a>
                                            </li>
                                        @endif
                                    @endfor

                                    @if ($end < $pengumuman->lastPage())
                                        @if ($end < $pengumuman->lastPage() - 1)
                                            <li class="disabled">
                                                <span class="px-3 py-2 rounded-lg bg-gray-100 text-gray-400 cursor-not-allowed text-sm">
                                                    ...
                                                </span>
                                            </li>
                                        @endif
                                        <li>
                                            <a href="{{ $pengumuman->url($pengumuman->lastPage()) }}"
                                               class="px-3 py-2 rounded-lg bg-gray-100 text-[#27438D] hover:bg-[#27438D] hover:text-white transition-colors duration-200 text-sm">
                                                {{ $pengumuman->lastPage() }}
                                            </a>
                                        </li>
                                    @endif

                                    {{-- Next Page Link --}}
                                    @if ($pengumuman->hasMorePages())
                                        <li>
                                            <a href="{{ $pengumuman->nextPageUrl() }}"
                                               class="px-3 py-2 rounded-lg bg-gray-100 text-[#27438D] hover:bg-[#27438D] hover:text-white transition-colors duration-200 text-sm">
                                                &raquo;
                                            </a>
                                        </li>
                                    @else
                                        <li class="disabled">
                                            <span class="px-3 py-2 rounded-lg bg-gray-100 text-gray-400 cursor-not-allowed text-sm">
                                                &raquo;
                                            </span>
                                        </li>
                                    @endif
                                </ul>
                            </nav>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
