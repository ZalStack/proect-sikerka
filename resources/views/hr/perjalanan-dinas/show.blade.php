@extends('layouts.app')

@section('content')
<div class="flex min-h-screen">
    @include('layouts.sidebar')
    <div class="flex-1 transition-all duration-300 md:ml-64">
        <div class="p-4 sm:p-6">
            <div class="mb-6">
                <a href="{{ route('hr.perjalanan-dinas.index') }}" class="text-[#00a2e9] hover:text-[#0088c4] flex items-center space-x-1 text-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    <span>Kembali</span>
                </a>
            </div>

            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <!-- Header Card -->
                <div class="p-6 border-b border-gray-200">
                    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                        <div>
                            <div class="flex flex-wrap items-center gap-3">
                                <h1 class="text-xl font-bold text-[#161758]">{{ $perjalananDinas->judul }}</h1>
                                @php
                                    $statusColors = [
                                        'pending' => 'bg-yellow-100 text-yellow-800',
                                        'approved' => 'bg-green-100 text-green-800',
                                        'rejected' => 'bg-red-100 text-red-800',
                                        'selesai' => 'bg-blue-100 text-blue-800',
                                    ];
                                @endphp
                                <span class="px-3 py-1 text-xs font-semibold rounded-full {{ $statusColors[$perjalananDinas->status] ?? 'bg-gray-100 text-gray-800' }}">
                                    {{ ucfirst($perjalananDinas->status) }}
                                </span>
                            </div>
                            <p class="text-sm text-gray-500 mt-1">Pengajuan: {{ $perjalananDinas->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                        @if($perjalananDinas->status === 'pending')
                            <div class="flex flex-wrap gap-2">
                                <button onclick="showApproveModal({{ $perjalananDinas->id }})" class="w-full sm:w-auto px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition text-sm">
                                    Setujui
                                </button>
                                <button onclick="showRejectModal({{ $perjalananDinas->id }})" class="w-full sm:w-auto px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition text-sm">
                                    Tolak
                                </button>
                            </div>
                        @endif
                        @if($perjalananDinas->status === 'approved')
                            <a href="{{ route('hr.perjalanan-dinas.mark-selesai', $perjalananDinas->id) }}"
                               class="w-full sm:w-auto text-center px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition text-sm"
                               onclick="return confirm('Tandai perjalanan dinas ini sebagai selesai?')">
                                Tandai Selesai
                            </a>
                        @endif
                    </div>
                </div>

                <!-- Content -->
                <div class="p-6 space-y-6">
                    <!-- Informasi Karyawan -->
                    <div>
                        <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3">Informasi Karyawan</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 bg-gray-50 rounded-lg p-4">
                            <div>
                                <p class="text-xs text-gray-500">Nama Lengkap</p>
                                <p class="font-medium">{{ $perjalananDinas->karyawan->nama_lengkap ?? '-' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Kode Pegawai</p>
                                <p class="font-medium">{{ $perjalananDinas->karyawan->kode_pegawai ?? '-' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Email</p>
                                <p class="font-medium">{{ $perjalananDinas->karyawan->email ?? '-' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Divisi</p>
                                <p class="font-medium">{{ $perjalananDinas->karyawan->divisi ?? '-' }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Detail Perjalanan Dinas -->
                    <div>
                        <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3">Detail Perjalanan Dinas</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 bg-gray-50 rounded-lg p-4">
                            <div>
                                <p class="text-xs text-gray-500">Tanggal Mulai</p>
                                <p class="font-medium">{{ $perjalananDinas->tanggal_mulai->format('d/m/Y') }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Tanggal Selesai</p>
                                <p class="font-medium">{{ $perjalananDinas->tanggal_selesai->format('d/m/Y') }}</p>
                            </div>
                            <div class="sm:col-span-2">
                                <p class="text-xs text-gray-500">Agenda</p>
                                <p class="font-medium">{{ $perjalananDinas->agenda }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Surat Tugas -->
                    <div>
                        <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3">Surat Tugas</h3>
                        <div class="bg-gray-50 rounded-lg p-4">
                            @if($perjalananDinas->surat_tugas)
                                <a href="{{ route('hr.perjalanan-dinas.download', $perjalananDinas->id) }}"
                                   class="inline-flex items-center space-x-2 text-[#00a2e9] hover:text-[#0088c4]">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    <span>Download Surat Tugas (PDF)</span>
                                </a>
                            @else
                                <p class="text-gray-500 text-sm">Tidak ada surat tugas yang diupload.</p>
                            @endif
                        </div>
                    </div>

                    <!-- Catatan HR -->
                    @if($perjalananDinas->catatan_hr)
                    <div>
                        <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3">Catatan HR</h3>
                        <div class="bg-blue-50 rounded-lg p-4 border-l-4 border-blue-500">
                            <p class="text-gray-700">{{ $perjalananDinas->catatan_hr }}</p>
                        </div>
                    </div>
                    @endif

                    <!-- Approval Info -->
                    @if($perjalananDinas->status !== 'pending')
                    <div>
                        <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3">Informasi Persetujuan</h3>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <p class="text-xs text-gray-500">Dipersiapkan oleh</p>
                                    <p class="font-medium">{{ $perjalananDinas->approver->nama_lengkap ?? '-' }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500">Tanggal Disetujui</p>
                                    <p class="font-medium">{{ $perjalananDinas->approved_at ? $perjalananDinas->approved_at->format('d/m/Y H:i') : '-' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Approve -->
<div id="approveModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" onclick="closeModal()"></div>
        <div class="relative bg-white rounded-xl shadow-xl max-w-md w-full p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-[#161758]">Setujui Pengajuan</h3>
                <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <form id="approveForm" method="POST">
                @csrf
                <input type="hidden" name="status" value="approved">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Catatan (Opsional)</label>
                    <textarea name="catatan_hr" rows="3"
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00a2e9] focus:border-transparent"
                              placeholder="Tulis catatan untuk karyawan..."></textarea>
                </div>
                <button type="submit" class="w-full px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition">
                    Ya, Setujui
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Modal Reject -->
<div id="rejectModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" onclick="closeModal()"></div>
        <div class="relative bg-white rounded-xl shadow-xl max-w-md w-full p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-[#161758]">Tolak Pengajuan</h3>
                <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <form id="rejectForm" method="POST">
                @csrf
                <input type="hidden" name="status" value="rejected">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Alasan Penolakan <span class="text-red-500">*</span></label>
                    <textarea name="catatan_hr" rows="3" required
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00a2e9] focus:border-transparent"
                              placeholder="Berikan alasan penolakan..."></textarea>
                </div>
                <button type="submit" class="w-full px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition">
                    Ya, Tolak
                </button>
            </form>
        </div>
    </div>
</div>

<script>
function showApproveModal(id) {
    // PERBAIKAN: Gunakan route dengan parameter ID yang benar
    const url = "{{ route('hr.perjalanan-dinas.approve', ':id') }}".replace(':id', id);
    document.getElementById('approveForm').action = url;
    document.getElementById('approveModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function showRejectModal(id) {
    const url = "{{ route('hr.perjalanan-dinas.approve', ':id') }}".replace(':id', id);
    document.getElementById('rejectForm').action = url;
    document.getElementById('rejectModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeModal() {
    document.getElementById('approveModal').classList.add('hidden');
    document.getElementById('rejectModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeModal();
});
</script>
@endsection
