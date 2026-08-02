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
                       class="w-full sm:w-auto text-center bg-[#27438D] text-white px-4 py-2 rounded-lg hover:bg-[#161758] transition-colors text-sm">
                        <i class="fas fa-sync mr-1"></i> Refresh
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
                    <button type="submit" class="bg-[#27438D] text-white px-4 sm:px-6 py-2 rounded-lg hover:bg-[#161758] transition-colors text-sm">
                        <i class="fas fa-filter mr-1"></i> Filter
                    </button>
                    @if(request('status') || request('karyawan_id'))
                        <a href="{{ route('hr.cuti.index') }}" class="bg-gray-500 text-white px-4 sm:px-6 py-2 rounded-lg hover:bg-gray-600 transition-colors text-sm">
                            <i class="fas fa-undo mr-1"></i> Reset
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
                                class="bg-[#2E7D3E] text-white px-3 py-1.5 rounded-lg hover:bg-green-700 transition-colors text-sm disabled:opacity-50 disabled:cursor-not-allowed"
                                id="btnApproveAll" disabled>
                            <i class="fas fa-check mr-1"></i> Setujui Semua
                        </button>
                        <button type="button" onclick="bulkAction('rejected')"
                                class="bg-[#ec1d1d] text-white px-3 py-1.5 rounded-lg hover:bg-red-700 transition-colors text-sm disabled:opacity-50 disabled:cursor-not-allowed"
                                id="btnRejectAll" disabled>
                            <i class="fas fa-times mr-1"></i> Tolak Semua
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
                                           class="text-[#00a2e9] hover:text-[#27438D] text-xs sm:text-sm px-2 py-1 rounded hover:bg-blue-50 transition-colors whitespace-nowrap" title="Detail">
                                            <i class="fas fa-eye mr-1"></i>Detail
                                        </a>

                                        <!-- Edit -->
                                        <a href="{{ route('hr.cuti.edit-hr', $item->id) }}"
                                           class="text-[#FCC626] hover:text-[#e6b800] text-xs sm:text-sm px-2 py-1 rounded hover:bg-yellow-50 transition-colors whitespace-nowrap" title="Edit">
                                            <i class="fas fa-edit mr-1"></i>Edit
                                        </a>

                                        <!-- Setujui -->
                                        @if($item->status === 'pending')
                                            <form action="{{ route('hr.cuti.approve', $item->id) }}" method="POST" class="inline">
                                                @csrf
                                                <input type="hidden" name="status" value="approved">
                                                <button type="submit" class="text-[#2E7D3E] hover:text-green-700 text-xs sm:text-sm px-2 py-1 rounded hover:bg-green-50 transition-colors whitespace-nowrap" title="Setujui">
                                                    <i class="fas fa-check-circle mr-1"></i>Setujui
                                                </button>
                                            </form>
                                            <form action="{{ route('hr.cuti.approve', $item->id) }}" method="POST" class="inline">
                                                @csrf
                                                <input type="hidden" name="status" value="rejected">
                                                <button type="submit" class="text-[#ec1d1d] hover:text-red-700 text-xs sm:text-sm px-2 py-1 rounded hover:bg-red-50 transition-colors whitespace-nowrap" title="Tolak">
                                                    <i class="fas fa-times-circle mr-1"></i>Tolak
                                                </button>
                                            </form>
                                        @endif

                                        <!-- Hapus -->
                                        <form action="{{ route('hr.cuti.destroy', $item->id) }}" method="POST" class="inline"
                                              onsubmit="return confirm('Yakin ingin menghapus data cuti ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-gray-400 hover:text-[#ec1d1d] text-xs sm:text-sm px-2 py-1 rounded hover:bg-red-50 transition-colors whitespace-nowrap" title="Hapus">
                                                <i class="fas fa-trash mr-1"></i>Hapus
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
                            <a href="{{ route('hr.cuti.show', $item->id) }}" class="text-[#00a2e9] text-xs font-medium px-2 py-1 rounded hover:bg-blue-50">
                                <i class="fas fa-eye mr-1"></i> Detail
                            </a>
                            <a href="{{ route('hr.cuti.edit-hr', $item->id) }}" class="text-[#FCC626] text-xs font-medium px-2 py-1 rounded hover:bg-yellow-50">
                                <i class="fas fa-edit mr-1"></i> Edit
                            </a>
                            @if($item->status === 'pending')
                                <form action="{{ route('hr.cuti.approve', $item->id) }}" method="POST" class="inline">
                                    @csrf
                                    <input type="hidden" name="status" value="approved">
                                    <button type="submit" class="text-[#2E7D3E] text-xs font-medium px-2 py-1 rounded hover:bg-green-50">
                                        <i class="fas fa-check-circle mr-1"></i> Setujui
                                    </button>
                                </form>
                                <form action="{{ route('hr.cuti.approve', $item->id) }}" method="POST" class="inline">
                                    @csrf
                                    <input type="hidden" name="status" value="rejected">
                                    <button type="submit" class="text-[#ec1d1d] text-xs font-medium px-2 py-1 rounded hover:bg-red-50">
                                        <i class="fas fa-times-circle mr-1"></i> Tolak
                                    </button>
                                </form>
                            @endif
                            <form action="{{ route('hr.cuti.destroy', $item->id) }}" method="POST" class="inline"
                                  onsubmit="return confirm('Yakin ingin menghapus data cuti ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-gray-400 hover:text-[#ec1d1d] text-xs font-medium px-2 py-1 rounded hover:bg-red-50">
                                    <i class="fas fa-trash mr-1"></i> Hapus
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
