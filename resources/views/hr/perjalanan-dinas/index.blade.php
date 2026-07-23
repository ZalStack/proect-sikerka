@extends('layouts.app')

@section('content')
<div class="flex min-h-screen">
    @include('layouts.sidebar')
    <div class="flex-1 transition-all duration-300 md:ml-64">
        <div class="p-4 sm:p-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
                <div>
                    <h1 class="text-xl sm:text-2xl font-bold text-[#161758]">Manajemen Perjalanan Dinas</h1>
                    <p class="text-sm text-gray-500 mt-1">Kelola pengajuan perjalanan dinas karyawan</p>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-2 md:grid-cols-5 gap-3 sm:gap-4 mb-6">
                <div class="bg-white rounded-xl shadow-sm p-3 sm:p-4 border-l-4 border-[#161758]">
                    <p class="text-xs text-gray-500">Total</p>
                    <p class="text-xl sm:text-2xl font-bold text-[#161758]">{{ $stats['total'] }}</p>
                </div>
                <div class="bg-white rounded-xl shadow-sm p-3 sm:p-4 border-l-4 border-yellow-500">
                    <p class="text-xs text-gray-500">Pending</p>
                    <p class="text-xl sm:text-2xl font-bold text-yellow-500">{{ $stats['pending'] }}</p>
                </div>
                <div class="bg-white rounded-xl shadow-sm p-3 sm:p-4 border-l-4 border-green-500">
                    <p class="text-xs text-gray-500">Disetujui</p>
                    <p class="text-xl sm:text-2xl font-bold text-green-500">{{ $stats['approved'] }}</p>
                </div>
                <div class="bg-white rounded-xl shadow-sm p-3 sm:p-4 border-l-4 border-red-500">
                    <p class="text-xs text-gray-500">Ditolak</p>
                    <p class="text-xl sm:text-2xl font-bold text-red-500">{{ $stats['rejected'] }}</p>
                </div>
                <div class="bg-white rounded-xl shadow-sm p-3 sm:p-4 border-l-4 border-blue-500">
                    <p class="text-xs text-gray-500">Selesai</p>
                    <p class="text-xl sm:text-2xl font-bold text-blue-500">{{ $stats['selesai'] }}</p>
                </div>
            </div>

            <!-- Filter & Search -->
            <div class="bg-white rounded-xl shadow-sm p-4 mb-6">
                <form action="{{ route('hr.perjalanan-dinas.index') }}" method="GET" class="flex flex-col sm:flex-row gap-3">
                    <div class="flex-1">
                        <input type="text" name="search" value="{{ request('search') }}"
                               placeholder="Cari judul atau nama karyawan..."
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00a2e9] focus:border-transparent text-sm">
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <select name="status" class="w-full sm:w-auto px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00a2e9] focus:border-transparent text-sm bg-white">
                            <option value="semua">Semua Status</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Disetujui</option>
                            <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Ditolak</option>
                            <option value="selesai" {{ request('status') == 'selesai' ? 'selected' : '' }}>Selesai</option>
                        </select>
                        <div class="flex items-center gap-1 flex-wrap">
                            <input type="date" name="tanggal_mulai" value="{{ request('tanggal_mulai') }}"
                                   class="w-full sm:w-auto px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00a2e9] focus:border-transparent text-sm">
                            <span class="flex-shrink-0 text-gray-500 text-sm">s/d</span>
                            <input type="date" name="tanggal_selesai" value="{{ request('tanggal_selesai') }}"
                                   class="w-full sm:w-auto px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00a2e9] focus:border-transparent text-sm">
                        </div>
                        <button type="submit" class="flex-1 sm:flex-none px-4 py-2 bg-[#00a2e9] text-white rounded-lg hover:bg-[#0088c4] transition text-sm">
                            Filter
                        </button>
                        <a href="{{ route('hr.perjalanan-dinas.index') }}" class="flex-1 sm:flex-none text-center px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition text-sm">
                            Reset
                        </a>
                    </div>
                </form>
            </div>

            <!-- Table -->
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="overflow-x-auto -mx-4 sm:mx-0">
                    <div class="inline-block min-w-full align-middle">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Karyawan</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Judul</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden sm:table-cell">Tanggal</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden md:table-cell">Surat Tugas</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($perjalananDinas as $item)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-4 py-3">
                                    <div class="flex items-center space-x-2">
                                        <div class="w-8 h-8 rounded-full bg-[#00a2e9] flex items-center justify-center text-white text-xs font-bold">
                                            {{ strtoupper(substr($item->karyawan->nama_lengkap ?? 'U', 0, 1)) }}
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">{{ $item->karyawan->nama_lengkap ?? '-' }}</p>
                                            <p class="text-xs text-gray-500">{{ $item->karyawan->kode_pegawai ?? '-' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <p class="text-sm text-gray-900">{{ Str::limit($item->judul, 30) }}</p>
                                    <p class="text-xs text-gray-500">{{ Str::limit($item->agenda, 40) }}</p>
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
                                <td class="px-4 py-3 hidden md:table-cell">
                                    @if($item->surat_tugas)
                                        <a href="{{ route('hr.perjalanan-dinas.download', $item->id) }}"
                                           class="text-[#00a2e9] hover:text-[#0088c4] text-sm flex items-center space-x-1">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                            </svg>
                                            <span>Download</span>
                                        </a>
                                    @else
                                        <span class="text-xs text-gray-400">Tidak ada</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center space-x-2">
                                        <a href="{{ route('hr.perjalanan-dinas.show', $item->id) }}"
                                           class="text-blue-600 hover:text-blue-800 text-sm">
                                            Detail
                                        </a>
                                        @if($item->status === 'approved')
                                            <a href="{{ route('hr.perjalanan-dinas.mark-selesai', $item->id) }}"
                                               class="text-blue-600 hover:text-blue-800 text-sm"
                                               onclick="return confirm('Tandai perjalanan dinas ini sebagai selesai?')">
                                                Selesai
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                                    <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    <p>Belum ada data perjalanan dinas.</p>
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
@endsection
