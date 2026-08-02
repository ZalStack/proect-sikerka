{{-- views/hr/cuti/index.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="flex min-h-screen">
    @include('layouts.sidebar')
    <div class="flex-1 transition-all duration-300 md:ml-64 pt-6">
        <div class="p-3 sm:p-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-3 sm:gap-4">
                <div>
                    <h1 class="text-xl sm:text-2xl font-bold font-['Montserrat'] text-[#161758]">Manajemen Cuti</h1>
                    <p class="text-sm sm:text-base text-[#27438D]">Kelola pengajuan cuti karyawan</p>
                </div>
                <div class="flex flex-wrap gap-2 w-full sm:w-auto">
                    <a href="{{ route('hr.cuti.index') }}"
                       class="w-full sm:w-auto inline-flex items-center justify-center text-center bg-[#27438D] text-white px-4 py-2 rounded-lg hover:bg-[#161758] transition-colors text-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-1" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M15.312 11.424a5.5 5.5 0 01-9.201 2.466l-.312-.311h2.433a.75.75 0 000-1.5H3.989a.75.75 0 00-.75.75v4.242a.75.75 0 001.5 0v-2.43l.31.31a7 7 0 0011.712-3.138.75.75 0 00-1.449-.39zm1.23-3.723a.75.75 0 00.219-.53V2.929a.75.75 0 00-1.5 0V5.36l-.31-.31A7 7 0 003.239 8.188a.75.75 0 101.448.389A5.5 5.5 0 0113.89 6.11l.311.31h-2.432a.75.75 0 000 1.5h4.243a.75.75 0 00.53-.219z" clip-rule="evenodd" />
                        </svg>
                        Refresh
                    </a>
                </div>
            </div>

            @if(session('success'))
                <div class="bg-[#2E7D3E] text-white p-3 sm:p-4 rounded-lg mb-4 text-sm flex justify-between items-center">
                    <span>{{ session('success') }}</span>
                    <button onclick="this.parentElement.style.display='none'" class="text-white hover:text-gray-200">×</button>
                </div>
            @endif

            <!-- Statistik -->
            <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mb-6">
                <div class="bg-white rounded-lg shadow-md p-3 sm:p-4 text-center border-l-4 border-[#00a2e9] hover:shadow-lg transition-shadow">
                    <p class="text-xl sm:text-2xl font-bold text-[#161758]">{{ $statistik['total'] }}</p>
                    <p class="text-xs sm:text-sm text-[#1B1B1B]">Total Pengajuan</p>
                </div>
                <div class="bg-white rounded-lg shadow-md p-3 sm:p-4 text-center border-l-4 border-[#FCC626] hover:shadow-lg transition-shadow">
                    <p class="text-xl sm:text-2xl font-bold text-[#161758]">{{ $statistik['pending'] }}</p>
                    <p class="text-xs sm:text-sm text-[#1B1B1B]">Menunggu</p>
                </div>
                <div class="bg-white rounded-lg shadow-md p-3 sm:p-4 text-center border-l-4 border-[#2E7D3E] hover:shadow-lg transition-shadow">
                    <p class="text-xl sm:text-2xl font-bold text-[#161758]">{{ $statistik['approved'] }}</p>
                    <p class="text-xs sm:text-sm text-[#1B1B1B]">Disetujui</p>
                </div>
                <div class="bg-white rounded-lg shadow-md p-3 sm:p-4 text-center border-l-4 border-[#ec1d1d] hover:shadow-lg transition-shadow">
                    <p class="text-xl sm:text-2xl font-bold text-[#161758]">{{ $statistik['rejected'] }}</p>
                    <p class="text-xs sm:text-sm text-[#1B1B1B]">Ditolak</p>
                </div>
            </div>

            <!-- Filter -->
            <div class="bg-white rounded-lg shadow-md p-4 sm:p-6 mb-6">
                <form action="{{ route('hr.cuti.index') }}" method="GET" class="flex flex-wrap gap-3 sm:gap-4">
                    <div class="flex-1 min-w-[140px] sm:min-w-[150px]">
                        <select name="status" class="w-full px-3 sm:px-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9] bg-white">
                            <option value="">Semua Status</option>
                            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Menunggu</option>
                            <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Disetujui</option>
                            <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Ditolak</option>
                        </select>
                    </div>
                    <div class="flex-1 min-w-[140px] sm:min-w-[150px]">
                        <select name="karyawan_id" class="w-full px-3 sm:px-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9] bg-white">
                            <option value="">Semua Karyawan</option>
                            @foreach($karyawans as $karyawan)
                                <option value="{{ $karyawan->id }}" {{ request('karyawan_id') == $karyawan->id ? 'selected' : '' }}>
                                    {{ $karyawan->nama_lengkap }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="inline-flex items-center bg-[#27438D] text-white px-4 sm:px-6 py-2 rounded-lg hover:bg-[#161758] transition-colors text-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-1" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M2.628 1.601C5.028 1.206 7.49 1 10 1s4.973.206 7.372.601a.75.75 0 01.628.74v2.288a2.25 2.25 0 01-.659 1.59l-4.682 4.683a2.25 2.25 0 00-.659 1.59v3.037c0 .684-.31 1.33-.844 1.757l-1.937 1.55A.75.75 0 018 18.25v-5.757a2.25 2.25 0 00-.659-1.591L2.659 6.22A2.25 2.25 0 012 4.629V2.34a.75.75 0 01.628-.74z" clip-rule="evenodd" />
                        </svg>
                        Filter
                    </button>
                    @if(request('status') || request('karyawan_id'))
                        <a href="{{ route('hr.cuti.index') }}" class="inline-flex items-center bg-gray-500 text-white px-4 sm:px-6 py-2 rounded-lg hover:bg-gray-600 transition-colors text-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-1" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M9.53 2.47a.75.75 0 010 1.06L5.81 7.25H15a6.75 6.75 0 010 13.5h-3a.75.75 0 010-1.5h3a5.25 5.25 0 100-10.5H5.81l3.72 3.72a.75.75 0 11-1.06 1.06l-5-5a.75.75 0 010-1.06l5-5a.75.75 0 011.06 0z" clip-rule="evenodd" />
                            </svg>
                            Reset
                        </a>
                    @endif
                </form>
            </div>

            <!-- Table -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="p-3 sm:p-4 border-b border-gray-200 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2">
                    <h2 class="text-base sm:text-lg font-semibold text-[#161758]">Daftar Pengajuan Cuti</h2>
                    <span class="text-xs text-gray-500">Total: {{ $cuti->total() }} data</span>
                </div>

                <!-- Bulk Action -->
                <div class="p-3 sm:p-4 border-b border-gray-200 bg-[#F5F5F5] flex flex-wrap items-center gap-3">
                    <span class="text-sm font-medium text-[#1B1B1B]">Bulk Action:</span>
                    <form id="bulkApproveForm" action="{{ route('hr.cuti.bulk-approve') }}" method="POST" class="flex flex-wrap items-center gap-2">
                        @csrf
                        <input type="hidden" name="ids" id="bulkIds" value="">
                        <input type="hidden" name="target_status" id="bulkStatus" value="">
                        <button type="button" onclick="bulkAction('approved')"
                                class="inline-flex items-center bg-[#2E7D3E] text-white px-3 py-1.5 rounded-lg hover:bg-green-700 transition-colors text-sm disabled:opacity-50 disabled:cursor-not-allowed"
                                id="btnApproveAll" disabled>
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-1" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd" />
                            </svg>
                            Setujui Semua
                        </button>
                        <button type="button" onclick="bulkAction('rejected')"
                                class="inline-flex items-center bg-[#ec1d1d] text-white px-3 py-1.5 rounded-lg hover:bg-red-700 transition-colors text-sm disabled:opacity-50 disabled:cursor-not-allowed"
                                id="btnRejectAll" disabled>
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-1" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" />
                            </svg>
                            Tolak Semua
                        </button>
                        <span class="text-xs text-gray-500 ml-2" id="selectedCount">Belum ada yang dipilih</span>
                    </form>
                </div>

                {{-- Desktop Table --}}
                <div class="hidden sm:block overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-[#F5F5F5]">
                            <tr>
                                <th class="px-3 sm:px-4 py-2 sm:py-3 text-left text-xs sm:text-sm font-semibold text-[#1B1B1B]">
                                    <input type="checkbox" id="selectAll" onchange="toggleAllCheckbox(this)" class="rounded border-gray-300">
                                </th>
                                <th class="px-3 sm:px-4 py-2 sm:py-3 text-left text-xs sm:text-sm font-semibold text-[#1B1B1B]">No</th>
                                <th class="px-3 sm:px-4 py-2 sm:py-3 text-left text-xs sm:text-sm font-semibold text-[#1B1B1B]">Karyawan</th>
                                <th class="px-3 sm:px-4 py-2 sm:py-3 text-left text-xs sm:text-sm font-semibold text-[#1B1B1B]">Tanggal</th>
                                <th class="px-3 sm:px-4 py-2 sm:py-3 text-left text-xs sm:text-sm font-semibold text-[#1B1B1B]">Durasi</th>
                                <th class="px-3 sm:px-4 py-2 sm:py-3 text-left text-xs sm:text-sm font-semibold text-[#1B1B1B]">Sisa Cuti</th>
                                <th class="px-3 sm:px-4 py-2 sm:py-3 text-left text-xs sm:text-sm font-semibold text-[#1B1B1B]">Status</th>
                                <th class="px-3 sm:px-4 py-2 sm:py-3 text-left text-xs sm:text-sm font-semibold text-[#1B1B1B]">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($cuti as $item)
                            <tr class="border-b border-gray-200 hover:bg-[#F5F5F5] transition-colors">
                                <td class="px-3 sm:px-4 py-2 sm:py-3">
                                    @if($item->status === 'pending')
                                        <input type="checkbox" class="cuti-checkbox rounded border-gray-300" value="{{ $item->id }}" onchange="updateSelectedCount()">
                                    @endif
                                </td>
                                <td class="px-3 sm:px-4 py-2 sm:py-3 text-xs sm:text-sm">{{ $loop->iteration + ($cuti->currentPage() - 1) * $cuti->perPage() }}</td>
                                <td class="px-3 sm:px-4 py-2 sm:py-3">
                                    <div class="flex items-center gap-2">
                                        <div class="w-8 h-8 rounded-full bg-[#27438D] text-white flex items-center justify-center text-xs font-semibold">
                                            {{ strtoupper(substr($item->karyawan->nama_lengkap, 0, 2)) }}
                                        </div>
                                        <span class="text-xs sm:text-sm font-medium">{{ $item->karyawan->nama_lengkap }}</span>
                                    </div>
                                </td>
                                <td class="px-3 sm:px-4 py-2 sm:py-3 text-xs sm:text-sm">
                                    {{ $item->tanggal_mulai ? $item->tanggal_mulai->format('d/m/Y') : '-' }}
                                    <span class="text-[10px] sm:text-xs text-gray-500">→</span>
                                    {{ $item->tanggal_selesai ? $item->tanggal_selesai->format('d/m/Y') : '-' }}
                                </td>
                                <td class="px-3 sm:px-4 py-2 sm:py-3 text-xs sm:text-sm font-semibold text-[#27438D]">{{ $item->durasi }} hari</td>
                                <td class="px-3 sm:px-4 py-2 sm:py-3 text-xs sm:text-sm">
                                    <span class="font-semibold {{ $item->sisa_cuti <= 3 ? 'text-[#ec1d1d]' : 'text-[#2E7D3E]' }}">
                                        {{ $item->sisa_cuti }} hari
                                    </span>
                                </td>
                                <td class="px-3 sm:px-4 py-2 sm:py-3">
                                    <span class="px-2 py-1 rounded-full text-[10px] sm:text-xs font-medium {{ $item->status_badge }}">
                                        {{ $item->status_label }}
                                    </span>
                                </td>
                                <td class="px-3 sm:px-4 py-2 sm:py-3">
                                    <div class="flex flex-wrap gap-1">
                                        <!-- Detail -->
                                        <a href="{{ route('hr.cuti.show', $item->id) }}"
                                           class="inline-flex items-center text-[#00a2e9] hover:text-[#27438D] text-xs sm:text-sm px-2 py-1 rounded hover:bg-blue-50 transition-colors whitespace-nowrap" title="Detail">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-1" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                                                <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
                                            </svg>
                                        </a>

                                        <!-- Edit -->
                                        <a href="{{ route('hr.cuti.edit-hr', $item->id) }}"
                                           class="inline-flex items-center text-[#FCC626] hover:text-[#e6b800] text-xs sm:text-sm px-2 py-1 rounded hover:bg-yellow-50 transition-colors whitespace-nowrap" title="Edit">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-1" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                                            </svg>
                                        </a>

                                        <!-- Hapus -->
                                        <form action="{{ route('hr.cuti.destroy', $item->id) }}" method="POST" class="inline"
                                              onsubmit="return confirm('Yakin ingin menghapus data cuti ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex items-center text-gray-400 hover:text-[#ec1d1d] text-xs sm:text-sm px-2 py-1 rounded hover:bg-red-50 transition-colors whitespace-nowrap" title="Hapus">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-1" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                    <path fill-rule="evenodd" d="M8.75 1A2.75 2.75 0 006 3.75v.443c-.795.077-1.584.176-2.365.298a.75.75 0 10.23 1.482l.149-.022.841 10.518A2.75 2.75 0 007.596 19h4.807a2.75 2.75 0 002.742-2.53l.841-10.52.149.023a.75.75 0 00.23-1.482 41.03 41.03 0 00-2.365-.298V3.75A2.75 2.75 0 0011.25 1h-2.5zM10 4c.84 0 1.673.025 2.5.075V3.75c0-.69-.56-1.25-1.25-1.25h-2.5c-.69 0-1.25.56-1.25 1.25v.325C8.327 4.025 9.16 4 10 4zM8.58 7.72a.75.75 0 00-1.5.06l.3 7.5a.75.75 0 101.5-.06l-.3-7.5zm4.34.06a.75.75 0 10-1.5-.06l-.3 7.5a.75.75 0 101.5.06l.3-7.5z" clip-rule="evenodd" />
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="px-4 py-8 text-center text-[#1B1B1B]">
                                    <div class="flex flex-col items-center">
                                        <svg class="w-12 sm:w-16 h-12 sm:h-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                        <p class="text-base sm:text-lg font-semibold text-gray-500">Belum ada pengajuan cuti</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Mobile Cards --}}
                <div class="sm:hidden divide-y divide-gray-200">
                    @forelse($cuti as $item)
                    <div class="p-3 space-y-2 hover:bg-[#F5F5F5] transition-colors">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                @if($item->status === 'pending')
                                    <input type="checkbox" class="cuti-checkbox-mobile rounded border-gray-300" value="{{ $item->id }}" onchange="updateSelectedCount()">
                                @endif
                                <div class="w-8 h-8 rounded-full bg-[#27438D] text-white flex items-center justify-center text-xs font-semibold">
                                    {{ strtoupper(substr($item->karyawan->nama_lengkap, 0, 2)) }}
                                </div>
                                <span class="text-sm font-semibold text-[#1B1B1B]">{{ $item->karyawan->nama_lengkap }}</span>
                            </div>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-medium {{ $item->status_badge }}">
                                {{ $item->status_label }}
                            </span>
                        </div>
                        <div class="grid grid-cols-2 gap-1 text-[11px]">
                            <span class="text-gray-500">Tanggal:</span>
                            <span class="text-[#1B1B1B] font-medium text-right">
                                {{ $item->tanggal_mulai ? $item->tanggal_mulai->format('d/m/Y') : '-' }}
                                <span class="text-gray-400">→</span>
                                {{ $item->tanggal_selesai ? $item->tanggal_selesai->format('d/m/Y') : '-' }}
                            </span>
                            <span class="text-gray-500">Durasi:</span>
                            <span class="text-[#27438D] font-semibold text-right">{{ $item->durasi }} hari</span>
                            <span class="text-gray-500">Sisa Cuti:</span>
                            <span class="text-right font-semibold {{ $item->sisa_cuti <= 3 ? 'text-[#ec1d1d]' : 'text-[#2E7D3E]' }}">
                                {{ $item->sisa_cuti }} hari
                            </span>
                        </div>
                        <div class="flex flex-wrap items-center gap-1 pt-1 border-t border-gray-100">
                            <a href="{{ route('hr.cuti.show', $item->id) }}" class="inline-flex items-center text-[#00a2e9] text-xs font-medium px-2 py-1 rounded hover:bg-blue-50">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-1" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                                    <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
                                </svg>
                                Detail
                            </a>
                            <a href="{{ route('hr.cuti.edit-hr', $item->id) }}" class="inline-flex items-center text-[#FCC626] text-xs font-medium px-2 py-1 rounded hover:bg-yellow-50">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-1" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                                </svg>
                                Edit
                            </a>
                            @if($item->status === 'pending')
                                <form action="{{ route('hr.cuti.approve', $item->id) }}" method="POST" class="inline">
                                    @csrf
                                    <input type="hidden" name="status" value="approved">
                                    <button type="submit" class="inline-flex items-center text-[#2E7D3E] text-xs font-medium px-2 py-1 rounded hover:bg-green-50">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-1" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                        </svg>
                                        Setujui
                                    </button>
                                </form>
                                <form action="{{ route('hr.cuti.approve', $item->id) }}" method="POST" class="inline">
                                    @csrf
                                    <input type="hidden" name="status" value="rejected">
                                    <button type="submit" class="inline-flex items-center text-[#ec1d1d] text-xs font-medium px-2 py-1 rounded hover:bg-red-50">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-1" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                        </svg>
                                        Tolak
                                    </button>
                                </form>
                            @endif
                            <form action="{{ route('hr.cuti.destroy', $item->id) }}" method="POST" class="inline"
                                  onsubmit="return confirm('Yakin ingin menghapus data cuti ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex items-center text-gray-400 hover:text-[#ec1d1d] text-xs font-medium px-2 py-1 rounded hover:bg-red-50">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-1" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M8.75 1A2.75 2.75 0 006 3.75v.443c-.795.077-1.584.176-2.365.298a.75.75 0 10.23 1.482l.149-.022.841 10.518A2.75 2.75 0 007.596 19h4.807a2.75 2.75 0 002.742-2.53l.841-10.52.149.023a.75.75 0 00.23-1.482 41.03 41.03 0 00-2.365-.298V3.75A2.75 2.75 0 0011.25 1h-2.5zM10 4c.84 0 1.673.025 2.5.075V3.75c0-.69-.56-1.25-1.25-1.25h-2.5c-.69 0-1.25.56-1.25 1.25v.325C8.327 4.025 9.16 4 10 4zM8.58 7.72a.75.75 0 00-1.5.06l.3 7.5a.75.75 0 101.5-.06l-.3-7.5zm4.34.06a.75.75 0 10-1.5-.06l-.3 7.5a.75.75 0 101.5.06l.3-7.5z" clip-rule="evenodd" />
                                    </svg>
                                    Hapus
                                </button>
                            </form>
                        </div>
                    </div>
                    @empty
                    <div class="p-4 py-10 text-center text-[#1B1B1B]">
                        <div class="flex flex-col items-center">
                            <svg class="w-10 h-10 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            <p class="text-sm font-semibold text-gray-500">Belum ada pengajuan cuti</p>
                        </div>
                    </div>
                    @endforelse
                </div>

                <!-- Pagination -->
                <div class="px-4 py-4 bg-white border-t border-gray-200">
                    {{ $cuti->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function toggleAllCheckbox(selectAll) {
    const checkboxes = document.querySelectorAll('.cuti-checkbox, .cuti-checkbox-mobile');
    checkboxes.forEach(cb => {
        cb.checked = selectAll.checked;
    });
    updateSelectedCount();
}

function updateSelectedCount() {
    const checkboxes = document.querySelectorAll('.cuti-checkbox:checked, .cuti-checkbox-mobile:checked');
    const count = checkboxes.length;
    const selectedCount = document.getElementById('selectedCount');
    const btnApproveAll = document.getElementById('btnApproveAll');
    const btnRejectAll = document.getElementById('btnRejectAll');

    if (count > 0) {
        selectedCount.textContent = `${count} data dipilih`;
        btnApproveAll.disabled = false;
        btnRejectAll.disabled = false;
    } else {
        selectedCount.textContent = 'Belum ada yang dipilih';
        btnApproveAll.disabled = true;
        btnRejectAll.disabled = true;
    }
}

function bulkAction(status) {
    const checkboxes = document.querySelectorAll('.cuti-checkbox:checked, .cuti-checkbox-mobile:checked');
    const ids = Array.from(checkboxes).map(cb => cb.value);

    if (ids.length === 0) {
        alert('Pilih minimal satu data cuti!');
        return;
    }

    const statusText = status === 'approved' ? 'menyetujui' : 'menolak';
    if (!confirm(`Yakin ingin ${statusText} ${ids.length} pengajuan cuti yang dipilih?`)) {
        return;
    }

    document.getElementById('bulkIds').value = JSON.stringify(ids);
    document.getElementById('bulkStatus').value = status;
    document.getElementById('bulkApproveForm').submit();
}

document.addEventListener('DOMContentLoaded', function() {
    updateSelectedCount();
});
</script>
@endsection
