{{-- views/hr/perjalanan-dinas/index.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="flex min-h-screen">
    @include('layouts.sidebar')
    <div class="flex-1 transition-all duration-300 md:ml-64">
        <div class="p-3 sm:p-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
                <div>
                    <h1 class="text-xl sm:text-2xl font-bold font-['Montserrat'] text-[#161758]">Manajemen Perjalanan Dinas</h1>
                    <p class="text-sm text-[#27438D] mt-1">Kelola pengajuan perjalanan dinas seluruh karyawan</p>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-2 md:grid-cols-5 gap-2 sm:gap-3 mb-6">
                <div class="bg-white rounded-xl shadow-sm p-3 sm:p-4 border-l-4 border-[#161758]">
                    <p class="text-[10px] sm:text-xs text-gray-500">Total</p>
                    <p class="text-lg sm:text-2xl font-bold text-[#161758]">{{ $stats['total'] }}</p>
                </div>
                <div class="bg-white rounded-xl shadow-sm p-3 sm:p-4 border-l-4 border-yellow-500">
                    <p class="text-[10px] sm:text-xs text-gray-500">Menunggu</p>
                    <p class="text-lg sm:text-2xl font-bold text-yellow-500">{{ $stats['pending'] }}</p>
                </div>
                <div class="bg-white rounded-xl shadow-sm p-3 sm:p-4 border-l-4 border-green-500">
                    <p class="text-[10px] sm:text-xs text-gray-500">Disetujui</p>
                    <p class="text-lg sm:text-2xl font-bold text-green-500">{{ $stats['approved'] }}</p>
                </div>
                <div class="bg-white rounded-xl shadow-sm p-3 sm:p-4 border-l-4 border-red-500">
                    <p class="text-[10px] sm:text-xs text-gray-500">Ditolak</p>
                    <p class="text-lg sm:text-2xl font-bold text-red-500">{{ $stats['rejected'] }}</p>
                </div>
                <div class="bg-white rounded-xl shadow-sm p-3 sm:p-4 border-l-4 border-blue-500 col-span-2 md:col-span-1">
                    <p class="text-[10px] sm:text-xs text-gray-500">Selesai</p>
                    <p class="text-lg sm:text-2xl font-bold text-blue-500">{{ $stats['selesai'] }}</p>
                </div>
            </div>

            <!-- Filter -->
            <div class="bg-white rounded-xl shadow-sm p-4 mb-6">
                <form action="{{ route('hr.perjalanan-dinas.index') }}" method="GET"
                      class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                    <div class="lg:col-span-2">
                        <label class="block text-xs font-medium text-gray-600 mb-1">Cari</label>
                        <input type="text" name="search" value="{{ request('search') }}"
                               placeholder="Judul, nama, atau kode pegawai..."
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-[#00a2e9] focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Status</label>
                        <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-[#00a2e9] focus:border-transparent bg-white">
                            <option value="semua" {{ !request('status') || request('status') == 'semua' ? 'selected' : '' }}>Semua Status</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Menunggu</option>
                            <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Disetujui</option>
                            <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Ditolak</option>
                            <option value="selesai" {{ request('status') == 'selesai' ? 'selected' : '' }}>Selesai</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Dari Tanggal</label>
                        <input type="date" name="tanggal_mulai" value="{{ request('tanggal_mulai') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-[#00a2e9] focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Sampai Tanggal</label>
                        <input type="date" name="tanggal_selesai" value="{{ request('tanggal_selesai') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-[#00a2e9] focus:border-transparent">
                    </div>
                    <div class="flex items-end gap-2 sm:col-span-2 lg:col-span-5">
                        <button type="submit" class="px-4 py-2 bg-[#00a2e9] text-white rounded-lg hover:bg-[#0088c4] transition text-sm">
                            Filter
                        </button>
                        <a href="{{ route('hr.perjalanan-dinas.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition text-sm">
                            Reset
                        </a>
                    </div>
                </form>
            </div>

            <!-- Table -->
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="overflow-x-auto -mx-3 sm:mx-0">
                    <div class="inline-block min-w-full align-middle">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Karyawan</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Judul & Agenda</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden sm:table-cell">Periode</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden lg:table-cell">Catatan HR</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden md:table-cell">Surat Tugas</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($perjalananDinas as $item)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-4 py-3">
                                    <p class="text-sm font-medium text-gray-900">{{ $item->karyawan->nama_lengkap ?? '-' }}</p>
                                    <p class="text-xs text-gray-500">{{ $item->karyawan->kode_pegawai ?? '-' }}</p>
                                </td>
                                <td class="px-4 py-3 max-w-[220px]">
                                    <p class="text-sm font-medium text-gray-900">{{ Str::limit($item->judul, 40) }}</p>
                                    <p class="text-xs text-gray-500">{{ Str::limit($item->agenda, 50) }}</p>
                                    <p class="text-xs text-gray-400 sm:hidden mt-1">
                                        {{ $item->tanggal_mulai->format('d/m/Y') }} s/d {{ $item->tanggal_selesai->format('d/m/Y') }}
                                    </p>
                                </td>
                                <td class="px-4 py-3 hidden sm:table-cell">
                                    <p class="text-sm text-gray-900">{{ $item->tanggal_mulai->format('d/m/Y') }}</p>
                                    <p class="text-xs text-gray-500">s/d {{ $item->tanggal_selesai->format('d/m/Y') }}</p>
                                </td>
                                <td class="px-4 py-3">
                                    @php
                                        $statusColors = [
                                            'pending' => 'bg-yellow-100 text-yellow-800',
                                            'approved' => 'bg-green-100 text-green-800',
                                            'rejected' => 'bg-red-100 text-red-800',
                                            'selesai' => 'bg-blue-100 text-blue-800',
                                        ];
                                    @endphp
                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusColors[$item->status] ?? 'bg-gray-100 text-gray-800' }}">
                                        {{ ucfirst($item->status) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 hidden lg:table-cell max-w-[180px]">
                                    @if($item->catatan_hr)
                                        <span class="text-xs text-gray-600" title="{{ $item->catatan_hr }}">{{ Str::limit($item->catatan_hr, 40) }}</span>
                                    @else
                                        <span class="text-xs text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 hidden md:table-cell">
                                    @if($item->surat_tugas)
                                        <a href="{{ route('hr.perjalanan-dinas.download', $item->id) }}"
                                           class="text-[#00a2e9] hover:text-[#0088c4] text-sm">
                                            Download
                                        </a>
                                    @else
                                        <span class="text-xs text-gray-400">Tidak ada</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <a href="{{ route('hr.perjalanan-dinas.show', $item->id) }}"
                                           class="text-blue-600 hover:text-blue-800 text-sm whitespace-nowrap">Detail</a>

                                        @if($item->status === 'pending')
                                            <form action="{{ route('hr.perjalanan-dinas.approve', $item->id) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="text-green-600 hover:text-green-800 text-sm whitespace-nowrap" onclick="return confirm('Setujui pengajuan ini?')">Setujui</button>
                                            </form>
                                            <button type="button" onclick="openRejectModal({{ $item->id }})" class="text-red-600 hover:text-red-800 text-sm whitespace-nowrap">Tolak</button>
                                            <form action="{{ route('hr.perjalanan-dinas.destroy', $item->id) }}" method="POST" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-gray-500 hover:text-gray-700 text-sm whitespace-nowrap" onclick="return confirm('Hapus data ini?')">Hapus</button>
                                            </form>
                                        @endif

                                        @if($item->status === 'approved')
                                            <form action="{{ route('hr.perjalanan-dinas.mark-selesai', $item->id) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="text-blue-600 hover:text-blue-800 text-sm whitespace-nowrap" onclick="return confirm('Tandai perjalanan dinas ini sebagai selesai?')">Tandai Selesai</button>
                                            </form>
                                        @endif

                                        @if($item->status === 'rejected')
                                            <form action="{{ route('hr.perjalanan-dinas.destroy', $item->id) }}" method="POST" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-gray-500 hover:text-gray-700 text-sm whitespace-nowrap" onclick="return confirm('Hapus data ini?')">Hapus</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                                    <p>Belum ada data perjalanan dinas untuk filter ini.</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                    </div>
                </div>

                <!-- Pagination -->
                <div class="px-4 py-3 bg-gray-50 border-t border-gray-200">
                    {{ $perjalananDinas->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Reject -->
<div id="rejectModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" onclick="closeRejectModal()"></div>
        <div class="relative bg-white rounded-xl shadow-xl max-w-md w-full p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-[#161758]">Tolak Pengajuan</h3>
                <button onclick="closeRejectModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <form id="rejectForm" method="POST">
                @csrf
                <div class="mb-4">
                    <label for="catatan_hr" class="block text-sm font-medium text-gray-700 mb-1">Catatan Penolakan <span class="text-red-500">*</span></label>
                    <textarea name="catatan_hr" id="catatan_hr" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00a2e9] focus:border-transparent" required placeholder="Berikan alasan penolakan..."></textarea>
                </div>
                <button type="submit" class="w-full bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600 transition">Tolak</button>
            </form>
        </div>
    </div>
</div>

<script>
    function openRejectModal(id) {
        var actionUrl = '{{ route("hr.perjalanan-dinas.reject", ["id" => ":id"]) }}';
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
