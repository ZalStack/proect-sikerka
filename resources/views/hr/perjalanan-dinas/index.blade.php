{{-- views/hr/perjalanan-dinas/index.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="flex min-h-screen bg-gray-50">
    @include('layouts.sidebar')
    <div class="flex-1 transition-all duration-300 md:ml-64">
        <div class="p-4 sm:p-6 lg:p-8">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-bold text-[#161758] tracking-tight">Manajemen Perjalanan Dinas</h1>
                    <p class="text-sm text-gray-500 mt-1">Kelola pengajuan perjalanan dinas seluruh karyawan</p>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-2 md:grid-cols-5 gap-3 sm:gap-4 mb-8">
                @php
                    $cards = [
                        ['label' => 'Total', 'value' => $stats['total'], 'color' => 'border-[#161758]', 'text' => 'text-[#161758]'],
                        ['label' => 'Menunggu', 'value' => $stats['pending'], 'color' => 'border-yellow-500', 'text' => 'text-yellow-500'],
                        ['label' => 'Disetujui', 'value' => $stats['approved'], 'color' => 'border-green-500', 'text' => 'text-green-500'],
                        ['label' => 'Ditolak', 'value' => $stats['rejected'], 'color' => 'border-red-500', 'text' => 'text-red-500'],
                        ['label' => 'Selesai', 'value' => $stats['selesai'], 'color' => 'border-blue-500', 'text' => 'text-blue-500'],
                    ];
                @endphp
                @foreach($cards as $card)
                    <div class="bg-white rounded-2xl shadow-sm hover:shadow-md transition-shadow p-4 border-l-4 {{ $card['color'] }}">
                        <p class="text-xs font-medium text-gray-400 uppercase tracking-wider">{{ $card['label'] }}</p>
                        <p class="text-xl font-bold {{ $card['text'] }} mt-1">{{ $card['value'] }}</p>
                    </div>
                @endforeach
            </div>

            <!-- Filter -->
            <div class="bg-white rounded-2xl shadow-sm p-5 sm:p-6 mb-8">
                <form action="{{ route('hr.perjalanan-dinas.index') }}" method="GET"
                      class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-4">
                    <div class="lg:col-span-2">
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Cari</label>
                        <input type="text" name="search" value="{{ request('search') }}"
                               placeholder="Judul, nama, atau kode pegawai..."
                               class="w-full px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-[#00a2e9] focus:border-transparent transition">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Status</label>
                        <select name="status"
                                class="w-full px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-[#00a2e9] focus:border-transparent transition">
                            <option value="semua" {{ !request('status') || request('status') == 'semua' ? 'selected' : '' }}>Semua Status</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Menunggu</option>
                            <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Disetujui</option>
                            <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Ditolak</option>
                            <option value="selesai" {{ request('status') == 'selesai' ? 'selected' : '' }}>Selesai</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Dari Tanggal</label>
                        <input type="date" name="tanggal_mulai" value="{{ request('tanggal_mulai') }}"
                               class="w-full px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-[#00a2e9] focus:border-transparent transition">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Sampai Tanggal</label>
                        <input type="date" name="tanggal_selesai" value="{{ request('tanggal_selesai') }}"
                               class="w-full px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-[#00a2e9] focus:border-transparent transition">
                    </div>
                    <div class="flex items-end gap-2 sm:col-span-2 lg:col-span-6">
                        <button type="submit"
                                class="px-5 py-2.5 bg-[#00a2e9] text-white rounded-xl hover:bg-[#0088c4] transition-all duration-200 text-sm font-medium shadow-sm hover:shadow-md">
                            Filter
                        </button>
                        <a href="{{ route('hr.perjalanan-dinas.index') }}"
                           class="px-5 py-2.5 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 transition-all duration-200 text-sm font-medium">
                            Reset
                        </a>
                    </div>
                </form>
            </div>

            <!-- Table -->
            <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-gray-50/80">
                            <tr>
                                <th scope="col" class="px-4 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Karyawan</th>
                                <th scope="col" class="px-4 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Judul & Agenda</th>
                                <th scope="col" class="px-4 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider hidden sm:table-cell">Periode</th>
                                <th scope="col" class="px-4 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                                <th scope="col" class="px-4 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider hidden lg:table-cell">Catatan HR</th>
                                <th scope="col" class="px-4 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider hidden md:table-cell">Surat Tugas</th>
                                <th scope="col" class="px-4 py-3.5 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @forelse($perjalananDinas as $item)
                                <tr class="hover:bg-gray-50/70 transition-colors">
                                    <td class="px-4 py-3.5">
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">{{ $item->karyawan->nama_lengkap ?? '-' }}</p>
                                            <p class="text-xs text-gray-500">{{ $item->karyawan->kode_pegawai ?? '-' }}</p>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3.5 max-w-[220px]">
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">{{ Str::limit($item->judul, 40) }}</p>
                                            <p class="text-xs text-gray-500">{{ Str::limit($item->agenda, 50) }}</p>
                                            <div class="text-xs text-gray-400 sm:hidden mt-1">
                                                {{ $item->tanggal_mulai->format('d/m/Y') }} s/d {{ $item->tanggal_selesai->format('d/m/Y') }}
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3.5 hidden sm:table-cell">
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">{{ $item->tanggal_mulai->format('d/m/Y') }}</p>
                                            <p class="text-xs text-gray-500">s/d {{ $item->tanggal_selesai->format('d/m/Y') }}</p>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3.5">
                                        @php
                                            $statusConfig = [
                                                'pending' => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-800', 'label' => 'Menunggu'],
                                                'approved' => ['bg' => 'bg-green-100', 'text' => 'text-green-800', 'label' => 'Disetujui'],
                                                'rejected' => ['bg' => 'bg-red-100', 'text' => 'text-red-800', 'label' => 'Ditolak'],
                                                'selesai' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-800', 'label' => 'Selesai'],
                                            ];
                                            $config = $statusConfig[$item->status] ?? ['bg' => 'bg-gray-100', 'text' => 'text-gray-800', 'label' => ucfirst($item->status)];
                                        @endphp
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $config['bg'] }} {{ $config['text'] }}">
                                            {{ $config['label'] }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3.5 hidden lg:table-cell max-w-[180px]">
                                        @if($item->catatan_hr)
                                            <span class="text-xs text-gray-600" title="{{ $item->catatan_hr }}">{{ Str::limit($item->catatan_hr, 40) }}</span>
                                        @else
                                            <span class="text-xs text-gray-400">—</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3.5 hidden md:table-cell">
                                        @if($item->surat_tugas)
                                            <a href="{{ route('hr.perjalanan-dinas.download', $item->id) }}" class="text-[#00a2e9] hover:text-[#0088c4] text-sm font-medium inline-flex items-center gap-1">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                                Download
                                            </a>
                                        @else
                                            <span class="text-xs text-gray-400">Tidak ada</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3.5 text-right">
                                        <div class="flex items-center justify-end gap-1.5 flex-wrap">
                                            <!-- Detail -->
                                            <a href="{{ route('hr.perjalanan-dinas.show', $item->id) }}" title="Detail" class="text-blue-600 hover:text-blue-800 transition-colors p-1">
                                                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            </a>

                                            @if($item->status === 'pending')
                                                <!-- Approve -->
                                                <form action="{{ route('hr.perjalanan-dinas.approve', $item->id) }}" method="POST" class="inline">
                                                    @csrf
                                                    <button type="submit" title="Setujui" class="text-green-600 hover:text-green-800 transition-colors p-1" onclick="return confirm('Setujui pengajuan ini?')">
                                                        <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                    </button>
                                                </form>
                                                <!-- Reject (modal) -->
                                                <button type="button" title="Tolak" onclick="openRejectModal({{ $item->id }})" class="text-red-600 hover:text-red-800 transition-colors p-1">
                                                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                </button>
                                                <!-- Delete -->
                                                <form action="{{ route('hr.perjalanan-dinas.destroy', $item->id) }}" method="POST" class="inline">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" title="Hapus" class="text-gray-400 hover:text-gray-600 transition-colors p-1" onclick="return confirm('Hapus data ini?')">
                                                        <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                    </button>
                                                </form>
                                            @endif

                                            @if($item->status === 'approved')
                                                <!-- Mark Selesai -->
                                                <form action="{{ route('hr.perjalanan-dinas.mark-selesai', $item->id) }}" method="POST" class="inline">
                                                    @csrf
                                                    <button type="submit" title="Tandai Selesai" class="text-blue-600 hover:text-blue-800 transition-colors p-1" onclick="return confirm('Tandai perjalanan dinas ini sebagai selesai?')">
                                                        <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                    </button>
                                                </form>
                                            @endif

                                            @if($item->status === 'rejected')
                                                <!-- Delete -->
                                                <form action="{{ route('hr.perjalanan-dinas.destroy', $item->id) }}" method="POST" class="inline">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" title="Hapus" class="text-gray-400 hover:text-gray-600 transition-colors p-1" onclick="return confirm('Hapus data ini?')">
                                                        <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-12 text-center">
                                        <div class="flex flex-col items-center justify-center text-gray-400">
                                            <svg class="w-12 h-12 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/>
                                            </svg>
                                            <p class="text-sm font-medium">Belum ada data perjalanan dinas untuk filter ini.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($perjalananDinas->hasPages())
                    <div class="px-4 py-4 bg-gray-50/50 border-t border-gray-100">
                        <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                            <div class="text-sm text-gray-600">
                                Menampilkan
                                <span class="font-semibold text-gray-800">{{ $perjalananDinas->firstItem() }}</span>
                                –
                                <span class="font-semibold text-gray-800">{{ $perjalananDinas->lastItem() }}</span>
                                dari
                                <span class="font-semibold text-gray-800">{{ $perjalananDinas->total() }}</span>
                                data
                            </div>
                            <div class="flex items-center gap-1.5 flex-wrap justify-center">
                                {{-- Previous --}}
                                @if($perjalananDinas->onFirstPage())
                                    <span class="px-3 py-1.5 rounded-lg bg-gray-100 text-gray-400 cursor-not-allowed text-sm font-medium">← Sebelumnya</span>
                                @else
                                    <a href="{{ $perjalananDinas->previousPageUrl() }}"
                                       class="px-3 py-1.5 rounded-lg bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 text-sm font-medium transition-colors shadow-sm hover:shadow">← Sebelumnya</a>
                                @endif

                                {{-- Halaman --}}
                                @foreach($perjalananDinas->getUrlRange(1, $perjalananDinas->lastPage()) as $page => $url)
                                    @if($page == $perjalananDinas->currentPage())
                                        <span class="px-3.5 py-1.5 rounded-lg bg-[#161758] text-white text-sm font-semibold shadow-sm">{{ $page }}</span>
                                    @else
                                        <a href="{{ $url }}"
                                           class="px-3.5 py-1.5 rounded-lg bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 text-sm font-medium transition-colors shadow-sm hover:shadow">{{ $page }}</a>
                                    @endif
                                @endforeach

                                {{-- Next --}}
                                @if($perjalananDinas->hasMorePages())
                                    <a href="{{ $perjalananDinas->nextPageUrl() }}"
                                       class="px-3 py-1.5 rounded-lg bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 text-sm font-medium transition-colors shadow-sm hover:shadow">Selanjutnya →</a>
                                @else
                                    <span class="px-3 py-1.5 rounded-lg bg-gray-100 text-gray-400 cursor-not-allowed text-sm font-medium">Selanjutnya →</span>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Modal Reject -->
    <div id="rejectModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" onclick="closeRejectModal()"></div>
            <div class="relative bg-white rounded-2xl shadow-xl max-w-md w-full p-6 transform transition-all">
                <div class="flex items-center justify-between mb-4">
                    <h3 id="modal-title" class="text-lg font-bold text-[#161758]">Tolak Pengajuan</h3>
                    <button onclick="closeRejectModal()" class="text-gray-400 hover:text-gray-600 transition-colors p-1">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <form id="rejectForm" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label for="catatan_hr" class="block text-sm font-medium text-gray-700 mb-1">Catatan Penolakan <span class="text-red-500">*</span></label>
                        <textarea name="catatan_hr" id="catatan_hr" rows="3"
                                  class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-[#00a2e9] focus:border-transparent transition"
                                  required placeholder="Berikan alasan penolakan..."></textarea>
                    </div>
                    <button type="submit" class="w-full bg-red-500 text-white px-4 py-2.5 rounded-xl hover:bg-red-600 transition-all duration-200 font-medium shadow-sm hover:shadow-md">
                        Tolak Pengajuan
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openRejectModal(id) {
            let actionUrl = '{{ route('hr.perjalanan-dinas.reject', ['id' => ':id']) }}';
            actionUrl = actionUrl.replace(':id', id);
            document.getElementById('rejectForm').action = actionUrl;
            document.getElementById('catatan_hr').value = '';
            document.getElementById('rejectModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeRejectModal() {
            document.getElementById('rejectModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeRejectModal();
        });
    </script>
@endsection
