@extends('layouts.app')

@section('content')
<div class="flex">
    @include('layouts.sidebar')
    <div class="ml-64 flex-1 p-6">
        <div class="mb-6 flex items-center justify-between flex-wrap gap-3">
            <div>
                <h1 class="text-2xl font-bold font-['Montserrat'] text-[#161758]">7SPS - Monitoring</h1>
                <p class="text-[#27438D]">Monitoring kegiatan 7 Sunnah Plus Suprasional karyawan</p>
            </div>
            <a href="{{ route('hr.sunnah.rekap') }}"
               class="bg-[#00a2e9] text-white px-4 py-2 rounded-lg hover:bg-[#27438D] transition-colors duration-200 text-sm font-medium">
                📊 Lihat Rekap Poin Bulanan per Karyawan
            </a>
        </div>

        @if(session('success'))
            <div class="bg-[#2E7D3E] text-white p-4 rounded-lg mb-4">
                {{ session('success') }}
            </div>
        @endif

        <!-- Filter -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-lg font-semibold text-[#161758] mb-4">Filter Laporan</h2>
            <form action="{{ route('hr.sunnah.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-6 gap-4">
                <div>
                    <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Periode Cepat</label>
                    <select name="periode" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                        <option value="">Gunakan Bulan/Tahun</option>
                        @foreach($periodeOptions as $value => $label)
                            <option value="{{ $value }}" {{ $periode === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Bulan</label>
                    <select name="month" {{ $periode ? 'disabled' : '' }} class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9] disabled:bg-gray-100">
                        @for($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" {{ (int) ($month ?? date('m')) === $m ? 'selected' : '' }}>
                                {{ DateTime::createFromFormat('!m', $m)->format('F') }}
                            </option>
                        @endfor
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Tahun</label>
                    <select name="year" {{ $periode ? 'disabled' : '' }} class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9] disabled:bg-gray-100">
                        @for($y = date('Y'); $y >= date('Y')-5; $y--)
                            <option value="{{ $y }}" {{ (int) ($year ?? date('Y')) === $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Karyawan</label>
                    <select name="karyawan_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                        <option value="">Semua Karyawan</option>
                        @foreach($karyawans as $karyawan)
                            <option value="{{ $karyawan->id }}" {{ request('karyawan_id') == $karyawan->id ? 'selected' : '' }}>
                                {{ $karyawan->nama_lengkap }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Divisi</label>
                    <select name="divisi" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                        <option value="">Semua Divisi</option>
                        @foreach($divisiList as $divisi)
                            <option value="{{ $divisi }}" {{ request('divisi') === $divisi ? 'selected' : '' }}>{{ $divisi }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Status</label>
                    <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                        <option value="">Semua Status</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Menunggu</option>
                        <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Disetujui</option>
                        <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Ditolak</option>
                    </select>
                </div>
                <div class="md:col-span-6 flex justify-end">
                    <button type="submit" class="bg-[#27438D] text-white px-6 py-2 rounded-lg hover:bg-[#161758] transition-colors duration-200">
                        Filter
                    </button>
                </div>
            </form>
        </div>

        <!-- Statistik -->
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow-md p-4 text-center">
                <p class="text-2xl font-bold text-[#161758]">{{ $statistik['total'] }}</p>
                <p class="text-sm text-[#1B1B1B]">Total</p>
            </div>
            <div class="bg-[#FCC626] text-[#1B1B1B] rounded-lg shadow-md p-4 text-center">
                <p class="text-2xl font-bold">{{ $statistik['pending'] }}</p>
                <p class="text-sm">Menunggu</p>
            </div>
            <div class="bg-[#2E7D3E] text-white rounded-lg shadow-md p-4 text-center">
                <p class="text-2xl font-bold">{{ $statistik['approved'] }}</p>
                <p class="text-sm">Disetujui</p>
            </div>
            <div class="bg-[#ec1d1d] text-white rounded-lg shadow-md p-4 text-center">
                <p class="text-2xl font-bold">{{ $statistik['rejected'] }}</p>
                <p class="text-sm">Ditolak</p>
            </div>
            <div class="bg-[#00a2e9] text-white rounded-lg shadow-md p-4 text-center">
                <p class="text-2xl font-bold">{{ $statistik['total_poin'] }}</p>
                <p class="text-sm">Total Poin</p>
            </div>
        </div>

        <!-- Form Bulk Approve -->
        <form id="bulk-approve-form" action="{{ route('hr.sunnah.bulk-approve') }}" method="POST">
            @csrf
            <input type="hidden" name="target_status" id="bulk-target-status" value="">
            <input type="hidden" name="month" value="{{ $month }}">
            <input type="hidden" name="year" value="{{ $year }}">
            <input type="hidden" name="periode" value="{{ $periode }}">
            <input type="hidden" name="karyawan_id" value="{{ request('karyawan_id') }}">
            <input type="hidden" name="status" value="{{ request('status') }}">
            <input type="hidden" name="divisi" value="{{ request('divisi') }}">
            <div id="bulk-ids-container"></div>

            <!-- Toolbar Bulk Action -->
            <div class="bg-white rounded-lg shadow-md p-4 mb-4 flex items-center justify-between flex-wrap gap-3">
                <div class="text-sm text-[#1B1B1B]">
                    <span id="bulk-selected-count">0</span> data dipilih
                </div>
                <div class="flex gap-2">
                    <button type="button" onclick="submitBulk('approved')"
                            class="bg-[#2E7D3E] text-white px-4 py-2 rounded-lg text-sm font-medium hover:opacity-90 transition-opacity duration-200 disabled:opacity-40"
                            id="btn-bulk-approve" disabled>
                        ✅ Setujui Terpilih
                    </button>
                    <button type="button" onclick="submitBulk('rejected')"
                            class="bg-[#ec1d1d] text-white px-4 py-2 rounded-lg text-sm font-medium hover:opacity-90 transition-opacity duration-200 disabled:opacity-40"
                            id="btn-bulk-reject" disabled>
                        ❌ Tolak Terpilih
                    </button>
                </div>
            </div>

            <!-- Tabel Data dikelompokkan per Divisi -->
            @forelse($groupedData as $divisi => $items)
                <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
                    <div class="bg-[#161758] text-white px-4 py-3 flex items-center justify-between flex-wrap gap-2">
                        <h3 class="font-semibold">🏢 Divisi: {{ $divisi }}</h3>
                        <span class="text-xs bg-white/20 px-3 py-1 rounded-full">{{ $items->count() }} data</span>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[950px]">
                            <thead class="bg-[#F5F5F5]">
                                <tr>
                                    <th class="px-4 py-3 text-left text-sm font-semibold text-[#1B1B1B]">
                                        <input type="checkbox" class="divisi-check-all w-4 h-4" data-divisi="{{ \Illuminate\Support\Str::slug($divisi) }}">
                                    </th>
                                    <th class="px-4 py-3 text-left text-sm font-semibold text-[#1B1B1B]">Karyawan</th>
                                    <th class="px-4 py-3 text-left text-sm font-semibold text-[#1B1B1B]">Tanggal</th>
                                    <th class="px-4 py-3 text-left text-sm font-semibold text-[#1B1B1B]">Total Poin</th>
                                    <th class="px-4 py-3 text-left text-sm font-semibold text-[#1B1B1B]">Status</th>
                                    <th class="px-4 py-3 text-left text-sm font-semibold text-[#1B1B1B]">Catatan HR</th>
                                    <th class="px-4 py-3 text-left text-sm font-semibold text-[#1B1B1B]">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($items as $item)
                                <tr class="border-b border-gray-200 hover:bg-[#F5F5F5]">
                                    <td class="px-4 py-3">
                                        <input type="checkbox"
                                               class="row-check divisi-{{ \Illuminate\Support\Str::slug($divisi) }} w-4 h-4"
                                               value="{{ $item->id }}"
                                               {{ $item->status_approval === 'approved' ? 'disabled' : '' }}>
                                    </td>
                                    <td class="px-4 py-3 text-sm">{{ $item->karyawan->nama_lengkap }}</td>
                                    <td class="px-4 py-3 text-sm">{{ $item->tanggal->format('d-m-Y') }}</td>
                                    <td class="px-4 py-3 text-sm font-semibold text-[#161758]">{{ $item->total_poin }}</td>
                                    <td class="px-4 py-3">
                                        <span class="px-2 py-1 rounded-full text-xs font-medium {{ $item->status_badge }}">
                                            {{ $item->status_label }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-sm">{{ $item->catatan_hr ?? '-' }}</td>
                                    <td class="px-4 py-3">
                                        <a href="{{ route('hr.sunnah.detail', $item->id) }}"
                                           class="text-[#00a2e9] hover:text-[#27438D] text-sm">
                                            Detail
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-lg shadow-md p-8 text-center text-[#1B1B1B]">
                    <svg class="w-16 h-16 text-gray-400 mb-4 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <p class="text-lg font-semibold">Belum ada data 7SPS untuk filter ini</p>
                </div>
            @endforelse
        </form>
    </div>
</div>

<script>
// Nonaktifkan filter Bulan/Tahun secara langsung ketika Periode Cepat dipilih
const periodeSelect = document.querySelector('select[name="periode"]');
const monthSelect = document.querySelector('select[name="month"]');
const yearSelect = document.querySelector('select[name="year"]');
if (periodeSelect) {
    periodeSelect.addEventListener('change', function () {
        const active = this.value !== '';
        if (monthSelect) monthSelect.disabled = active;
        if (yearSelect) yearSelect.disabled = active;
    });
}

const bulkForm = document.getElementById('bulk-approve-form');
const bulkTargetStatus = document.getElementById('bulk-target-status');
const bulkIdsContainer = document.getElementById('bulk-ids-container');
const bulkSelectedCount = document.getElementById('bulk-selected-count');
const btnBulkApprove = document.getElementById('btn-bulk-approve');
const btnBulkReject = document.getElementById('btn-bulk-reject');

function getCheckedRows() {
    return Array.from(document.querySelectorAll('.row-check:checked'));
}

function refreshBulkToolbar() {
    const checked = getCheckedRows();
    bulkSelectedCount.textContent = checked.length;
    btnBulkApprove.disabled = checked.length === 0;
    btnBulkReject.disabled = checked.length === 0;
}

document.addEventListener('change', function (event) {
    if (event.target.classList.contains('row-check')) {
        refreshBulkToolbar();
    }

    if (event.target.classList.contains('divisi-check-all')) {
        const divisiSlug = event.target.dataset.divisi;
        document.querySelectorAll(`.row-check.divisi-${divisiSlug}`).forEach(cb => {
            if (!cb.disabled) cb.checked = event.target.checked;
        });
        refreshBulkToolbar();
    }
});

function submitBulk(status) {
    const checked = getCheckedRows();
    if (checked.length === 0) return;

    const confirmText = status === 'approved'
        ? `Setujui ${checked.length} data terpilih?`
        : `Tolak ${checked.length} data terpilih?`;
    if (!confirm(confirmText)) return;

    // Bersihkan input ids sebelumnya
    bulkIdsContainer.innerHTML = '';
    checked.forEach(cb => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'ids[]';
        input.value = cb.value;
        bulkIdsContainer.appendChild(input);
    });

    bulkTargetStatus.value = status;
    bulkForm.submit();
}
</script>
@endsection
