{{-- views/hr/sunnah/index.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="flex min-h">
    @include('layouts.sidebar')
    <div class="flex-1 transition-all duration-300 md:ml-64 pt-4 sm:pt-6">
        <div class="p-3 sm:p-4 md:p-6">

            {{-- ============ HEADER ============ --}}
            <div class="mb-4 sm:mb-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 sm:gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-2xl bg-gradient-to-br from-[#161758] to-[#27438D] flex items-center justify-center text-xl sm:text-2xl shadow-md shrink-0">
                        📋
                    </div>
                    <div>
                        <h1 class="text-lg sm:text-xl md:text-2xl font-bold font-['Montserrat'] text-[#161758] leading-tight">Monitoring 7SPS</h1>
                        <p class="text-xs sm:text-sm md:text-base text-[#27438D]">Monitoring kegiatan 7 Sunnah Plus Suprasional karyawan</p>
                    </div>
                </div>
                <div class="flex flex-wrap gap-2 w-full sm:w-auto">
                    <a href="{{ route('hr.sunnah.rekap') }}"
                       class="flex-1 sm:flex-none inline-flex items-center justify-center gap-1.5 text-center bg-[#00a2e9] text-white px-3 sm:px-4 py-2 rounded-xl hover:bg-[#27438D] active:scale-95 transition-all duration-200 text-xs sm:text-sm font-medium shadow-sm">
                        📊 <span>Rekap Karyawan</span>
                    </a>
                    <a href="{{ route('hr.sunnah.rekap-divisi') }}"
                       class="flex-1 sm:flex-none inline-flex items-center justify-center gap-1.5 text-center bg-[#2E7D3E] text-white px-3 sm:px-4 py-2 rounded-xl hover:bg-[#1a5a2a] active:scale-95 transition-all duration-200 text-xs sm:text-sm font-medium shadow-sm">
                        🏆 <span>Rekap Divisi</span>
                    </a>
                </div>
            </div>

            {{-- ============ INFO PERIODE APPROVAL ============ --}}
            <div class="bg-gradient-to-r from-[#161758] to-[#27438D] text-white p-3 sm:p-4 rounded-2xl mb-4 sm:mb-6 text-xs sm:text-sm shadow-md">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2">
                    <div class="flex items-start gap-2">
                        <span class="text-base sm:text-lg leading-none">📋</span>
                        <span><strong>Periode Approval:</strong> HR hanya dapat melakukan approve/reject untuk data <strong>1 bulan terakhir</strong> (30 hari termasuk hari ini).</span>
                    </div>
                    <span class="text-[10px] sm:text-xs bg-white/20 px-2.5 sm:px-3 py-1 rounded-full whitespace-nowrap font-medium">
                        {{ \Carbon\Carbon::today()->subDays(29)->format('d/m/Y') }} &ndash; {{ \Carbon\Carbon::today()->format('d/m/Y') }}
                    </span>
                </div>
            </div>

            @if(session('success'))
                <div class="bg-[#2E7D3E] text-white p-3 sm:p-4 rounded-2xl mb-4 text-xs sm:text-sm shadow-md flex items-start gap-2">
                    <span>✅</span><span>{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="bg-[#ec1d1d] text-white p-3 sm:p-4 rounded-2xl mb-4 text-xs sm:text-sm shadow-md flex items-start gap-2">
                    <span>⚠️</span><span>{{ session('error') }}</span>
                </div>
            @endif

            {{-- ============ FILTER ============ --}}
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 hover:shadow-lg transition-all duration-300 p-3 sm:p-4 md:p-6 mb-4 sm:mb-6">
                <h2 class="text-sm sm:text-base md:text-lg font-semibold text-[#161758] mb-3 sm:mb-4 flex items-center gap-2">
                    <span>🔎</span> Filter Laporan
                </h2>
                <form id="filter-form" action="{{ route('hr.sunnah.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 sm:gap-4">
                <div id="filter-fields" class="contents">
                    <div>
                        <label class="block text-xs sm:text-sm font-medium text-[#1B1B1B] mb-1">Tanggal Mulai</label>
                        <input type="date" name="start_date" value="{{ $startDate ? $startDate->format('Y-m-d') : $defaultStartDate }}"
                               class="w-full px-3 sm:px-4 py-2 text-sm sm:text-base border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#00a2e9] focus:border-transparent transition">
                    </div>
                    <div>
                        <label class="block text-xs sm:text-sm font-medium text-[#1B1B1B] mb-1">Tanggal Selesai</label>
                        <input type="date" name="end_date" value="{{ $endDate ? $endDate->format('Y-m-d') : $defaultEndDate }}"
                               class="w-full px-3 sm:px-4 py-2 text-sm sm:text-base border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#00a2e9] focus:border-transparent transition">
                    </div>
                    <div>
                        <label class="block text-xs sm:text-sm font-medium text-[#1B1B1B] mb-1">Karyawan</label>
                        <select name="karyawan_id" class="w-full px-3 sm:px-4 py-2 text-sm sm:text-base border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#00a2e9] focus:border-transparent transition">
                            <option value="">Semua Karyawan</option>
                            @foreach($karyawans as $karyawan)
                                <option value="{{ $karyawan->id }}" {{ request('karyawan_id') == $karyawan->id ? 'selected' : '' }}>
                                    {{ $karyawan->nama_lengkap }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs sm:text-sm font-medium text-[#1B1B1B] mb-1">Divisi</label>
                        <select name="divisi" class="w-full px-3 sm:px-4 py-2 text-sm sm:text-base border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#00a2e9] focus:border-transparent transition">
                            <option value="">Semua Divisi</option>
                            @foreach($divisiList as $divisi)
                                <option value="{{ $divisi }}" {{ request('divisi') === $divisi ? 'selected' : '' }}>{{ $divisi }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs sm:text-sm font-medium text-[#1B1B1B] mb-1">Status</label>
                        <select name="status" class="w-full px-3 sm:px-4 py-2 text-sm sm:text-base border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#00a2e9] focus:border-transparent transition">
                            <option value="">Semua Status</option>
                            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Menunggu</option>
                            <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Disetujui</option>
                            <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Ditolak</option>
                        </select>
                    </div>
                    <div class="sm:col-span-2 lg:col-span-5 flex flex-col sm:flex-row justify-end gap-2">
                        @if(request()->anyFilled(['start_date','end_date','karyawan_id','divisi','status']))
                            <a href="{{ route('hr.sunnah.index') }}"
                               class="w-full sm:w-auto text-center bg-gray-100 text-[#1B1B1B] px-4 sm:px-6 py-2 rounded-xl hover:bg-gray-200 transition-colors duration-200 text-sm sm:text-base font-medium">
                                Reset
                            </a>
                        @endif
                        <button type="submit" id="filter-submit-btn" class="w-full sm:w-auto bg-[#27438D] text-white px-4 sm:px-6 py-2 rounded-xl hover:bg-[#161758] active:scale-95 transition-all duration-200 text-sm sm:text-base font-medium shadow-sm inline-flex items-center justify-center gap-2">
                            <svg id="filter-submit-spinner" class="hidden animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            <span>Terapkan Filter</span>
                        </button>
                    </div>
                </div>
                </form>
            </div>

            {{-- ============ AJAX CONTENT (statistik, toolbar, data, pagination) ============ --}}
            <div id="ajax-content" class="relative">
            <div id="ajax-loading-overlay" class="hidden absolute inset-0 z-40 bg-white/60 backdrop-blur-[1px] items-center justify-center rounded-2xl">
                <div class="flex items-center gap-2 bg-white shadow-lg rounded-full px-4 py-2 border border-gray-100">
                    <svg class="animate-spin h-4 w-4 text-[#27438D]" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    <span class="text-xs sm:text-sm font-medium text-[#161758]">Memuat data...</span>
                </div>
            </div>

            {{-- ============ STATISTIK ============ --}}
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-2 sm:gap-3 md:gap-4 mb-4 sm:mb-6">
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 hover:shadow-lg transition-all duration-300 p-3 sm:p-4 text-center">
                    <p class="text-base sm:text-lg md:text-2xl font-bold text-[#161758]">{{ $statistik['total'] }}</p>
                    <p class="text-[10px] sm:text-xs md:text-sm text-[#1B1B1B] mt-0.5">📁 Total</p>
                </div>
                <div class="bg-[#FCC626] text-[#1B1B1B] rounded-2xl shadow-md p-3 sm:p-4 text-center hover:shadow-lg transition-shadow duration-200">
                    <p class="text-base sm:text-lg md:text-2xl font-bold">{{ $statistik['pending'] }}</p>
                    <p class="text-[10px] sm:text-xs md:text-sm mt-0.5">⏳ Menunggu</p>
                </div>
                <div class="bg-[#2E7D3E] text-white rounded-2xl shadow-md p-3 sm:p-4 text-center hover:shadow-lg transition-shadow duration-200">
                    <p class="text-base sm:text-lg md:text-2xl font-bold">{{ $statistik['approved'] }}</p>
                    <p class="text-[10px] sm:text-xs md:text-sm mt-0.5">✅ Disetujui</p>
                </div>
                <div class="bg-[#ec1d1d] text-white rounded-2xl shadow-md p-3 sm:p-4 text-center hover:shadow-lg transition-shadow duration-200">
                    <p class="text-base sm:text-lg md:text-2xl font-bold">{{ $statistik['rejected'] }}</p>
                    <p class="text-[10px] sm:text-xs md:text-sm mt-0.5">❌ Ditolak</p>
                </div>
                <div class="bg-[#00a2e9] text-white rounded-2xl shadow-md p-3 sm:p-4 text-center col-span-2 sm:col-span-1 hover:shadow-lg transition-shadow duration-200">
                    <p class="text-base sm:text-lg md:text-2xl font-bold">{{ $statistik['total_poin'] }}</p>
                    <p class="text-[10px] sm:text-xs md:text-sm mt-0.5">⭐ Total Poin</p>
                </div>
            </div>

            {{-- ============ FORM BULK APPROVE ============ --}}
            <form id="bulk-approve-form" action="{{ route('hr.sunnah.bulk-approve') }}" method="POST">
                @csrf
                <input type="hidden" name="target_status" id="bulk-target-status" value="">
                <input type="hidden" name="catatan_hr" id="bulk-catatan-hr" value="">
                <input type="hidden" name="start_date" value="{{ request('start_date') }}">
                <input type="hidden" name="end_date" value="{{ request('end_date') }}">
                <input type="hidden" name="karyawan_id" value="{{ request('karyawan_id') }}">
                <input type="hidden" name="status" value="{{ request('status') }}">
                <input type="hidden" name="divisi" value="{{ request('divisi') }}">
                <div id="bulk-ids-container"></div>

                {{-- Sticky Toolbar Bulk Action --}}
                <div class="sticky top-2 z-30 bg-white/95 backdrop-blur rounded-2xl shadow-lg p-3 sm:p-4 mb-3 sm:mb-4 border border-gray-100 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                    <div class="flex items-center gap-2 text-xs sm:text-sm text-[#1B1B1B]">
                        <label class="inline-flex items-center gap-1.5 cursor-pointer select-none">
                            <input type="checkbox" id="select-all-global" class="w-3.5 h-3.5 sm:w-4 sm:h-4 rounded border-gray-300 text-[#27438D] focus:ring-[#00a2e9]">
                            <span class="hidden sm:inline">Pilih semua</span>
                        </label>
                        <span class="text-gray-300">|</span>
                        <span>
                            <span id="bulk-selected-count" class="font-bold text-[#161758]">0</span> data dipilih
                        </span>
                        <span class="text-[10px] sm:text-xs text-[#27438D] ml-1 hidden md:inline">
                            (hanya data dalam periode 1 bulan terakhir yang dapat di-approve)
                        </span>
                    </div>
                    <div class="flex flex-wrap gap-2 w-full sm:w-auto">
                        <button type="button" onclick="openCommentModal('approved')"
                                class="flex-1 sm:flex-none inline-flex items-center justify-center gap-1.5 bg-[#2E7D3E] text-white px-3 sm:px-4 py-2 rounded-xl text-xs sm:text-sm font-medium hover:opacity-90 active:scale-95 transition-all duration-200 disabled:opacity-40 disabled:cursor-not-allowed shadow-sm"
                                id="btn-bulk-approve" disabled>
                            ✅ Setujui
                        </button>
                        <button type="button" onclick="openCommentModal('rejected')"
                                class="flex-1 sm:flex-none inline-flex items-center justify-center gap-1.5 bg-[#ec1d1d] text-white px-3 sm:px-4 py-2 rounded-xl text-xs sm:text-sm font-medium hover:opacity-90 active:scale-95 transition-all duration-200 disabled:opacity-40 disabled:cursor-not-allowed shadow-sm"
                                id="btn-bulk-reject" disabled>
                            ❌ Tolak
                        </button>
                        <button type="button" onclick="openCommentModal('pending')"
                                class="flex-1 sm:flex-none inline-flex items-center justify-center gap-1.5 bg-[#FCC626] text-[#1B1B1B] px-3 sm:px-4 py-2 rounded-xl text-xs sm:text-sm font-medium hover:opacity-90 active:scale-95 transition-all duration-200 disabled:opacity-40 disabled:cursor-not-allowed shadow-sm"
                                id="btn-bulk-pending" disabled>
                            ⏳ Kembalikan
                        </button>
                    </div>
                </div>

                {{-- ============ DATA (grouped per divisi) ============ --}}
                @forelse($groupedData as $divisi => $items)
                    @php $divisiSlug = \Illuminate\Support\Str::slug($divisi); @endphp
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 hover:shadow-lg transition-all duration-300 overflow-hidden mb-4 sm:mb-6">
                        <div class="bg-gradient-to-r from-[#161758] to-[#27438D] text-white px-3 sm:px-4 py-2.5 sm:py-3 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2">
                            <label class="font-semibold text-sm sm:text-base flex items-center gap-2 cursor-pointer select-none">
                                <input type="checkbox" class="divisi-check-all w-3.5 h-3.5 sm:w-4 sm:h-4 rounded border-white/50 text-[#27438D] focus:ring-[#00a2e9]" data-divisi="{{ $divisiSlug }}">
                                🏢 Divisi: {{ $divisi }}
                            </label>
                            <span class="text-[10px] sm:text-xs bg-white/20 px-2.5 sm:px-3 py-1 rounded-full font-medium">{{ $items->count() }} data</span>
                        </div>

                        {{-- ---- Desktop / tablet table (>= sm) ---- --}}
                        <div class="hidden sm:block overflow-x-auto">
                            <table class="w-full text-xs sm:text-sm">
                                <thead class="bg-[#F5F5F5]">
                                    <tr>
                                        <th class="px-2 sm:px-3 md:px-4 py-2 sm:py-3 text-left font-semibold text-[#1B1B1B] w-8 sm:w-10"></th>
                                        <th class="px-2 sm:px-3 md:px-4 py-2 sm:py-3 text-left font-semibold text-[#1B1B1B]">Karyawan</th>
                                        <th class="px-2 sm:px-3 md:px-4 py-2 sm:py-3 text-left font-semibold text-[#1B1B1B]">Tanggal</th>
                                        <th class="px-2 sm:px-3 md:px-4 py-2 sm:py-3 text-left font-semibold text-[#1B1B1B]">Poin</th>
                                        <th class="px-2 sm:px-3 md:px-4 py-2 sm:py-3 text-left font-semibold text-[#1B1B1B]">Status</th>
                                        <th class="px-2 sm:px-3 md:px-4 py-2 sm:py-3 text-left font-semibold text-[#1B1B1B] hidden lg:table-cell">Periode</th>
                                        <th class="px-2 sm:px-3 md:px-4 py-2 sm:py-3 text-left font-semibold text-[#1B1B1B]">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @foreach($items as $item)
                                    <tr class="hover:bg-[#F5F5F5] transition-colors duration-150">
                                        <td class="px-2 sm:px-3 md:px-4 py-2 sm:py-3">
                                            <input type="checkbox"
                                                   class="row-check divisi-{{ $divisiSlug }} w-3.5 h-3.5 sm:w-4 sm:h-4 rounded border-gray-300 text-[#27438D] focus:ring-[#00a2e9]"
                                                   value="{{ $item->id }}"
                                                   {{ !$item->isWithinApprovalPeriod() ? 'disabled' : '' }}>
                                            @if(!$item->isWithinApprovalPeriod())
                                                <span class="text-[8px] sm:text-[10px] text-[#ec1d1d] block mt-0.5">expired</span>
                                            @endif
                                        </td>
                                        <td class="px-2 sm:px-3 md:px-4 py-2 sm:py-3">
                                            <div class="flex items-center gap-2">
                                                <div class="w-6 h-6 sm:w-8 sm:h-8 rounded-full bg-[#27438D]/10 text-[#27438D] flex items-center justify-center text-[10px] sm:text-xs font-bold shrink-0">
                                                    {{ strtoupper(substr($item->karyawan->nama_lengkap, 0, 1)) }}
                                                </div>
                                                <span class="break-words max-w-[100px] sm:max-w-none font-medium text-[#1B1B1B]">{{ $item->karyawan->nama_lengkap }}</span>
                                            </div>
                                        </td>
                                        <td class="px-2 sm:px-3 md:px-4 py-2 sm:py-3 text-[#1B1B1B]">
                                            {{ $item->tanggal->format('d-m-Y') }}
                                        </td>
                                        <td class="px-2 sm:px-3 md:px-4 py-2 sm:py-3 font-bold text-[#161758]">
                                            {{ $item->total_poin }}
                                        </td>
                                        <td class="px-2 sm:px-3 md:px-4 py-2 sm:py-3">
                                            <span class="px-2 py-1 rounded-full text-[9px] sm:text-[10px] md:text-xs font-medium {{ $item->status_badge }}">
                                                {{ $item->status_label }}
                                            </span>
                                        </td>
                                        <td class="px-2 sm:px-3 md:px-4 py-2 sm:py-3 hidden lg:table-cell">
                                            @if($item->isWithinApprovalPeriod())
                                                <span class="text-[#2E7D3E] text-[10px] sm:text-xs font-medium">✅ Aktif</span>
                                            @else
                                                <span class="text-[#ec1d1d] text-[10px] sm:text-xs font-medium">❌ Expired</span>
                                            @endif
                                        </td>
                                        <td class="px-2 sm:px-3 md:px-4 py-2 sm:py-3">
                                            <a href="{{ route('hr.sunnah.detail', $item->id) }}"
                                               class="inline-flex items-center gap-1 text-[#00a2e9] hover:text-[#27438D] font-medium text-xs sm:text-sm transition-colors duration-150">
                                                Detail →
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{-- ---- Mobile card list (< sm) ---- --}}
                        <div class="sm:hidden divide-y divide-gray-100">
                            @foreach($items as $item)
                            <div class="p-3 flex flex-col gap-2">
                                <div class="flex items-start justify-between gap-2">
                                    <div class="flex items-center gap-2 min-w-0">
                                        <input type="checkbox"
                                               class="row-check divisi-{{ $divisiSlug }} w-4 h-4 rounded border-gray-300 text-[#27438D] focus:ring-[#00a2e9] shrink-0"
                                               value="{{ $item->id }}"
                                               {{ !$item->isWithinApprovalPeriod() ? 'disabled' : '' }}>
                                        <div class="w-7 h-7 rounded-full bg-[#27438D]/10 text-[#27438D] flex items-center justify-center text-[11px] font-bold shrink-0">
                                            {{ strtoupper(substr($item->karyawan->nama_lengkap, 0, 1)) }}
                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-xs font-semibold text-[#1B1B1B] truncate">{{ $item->karyawan->nama_lengkap }}</p>
                                            <p class="text-[10px] text-[#27438D]">{{ $item->tanggal->format('d-m-Y') }}</p>
                                        </div>
                                    </div>
                                    <span class="px-2 py-1 rounded-full text-[9px] font-medium shrink-0 {{ $item->status_badge }}">
                                        {{ $item->status_label }}
                                    </span>
                                </div>
                                <div class="flex items-center justify-between text-xs pl-6">
                                    <span class="font-bold text-[#161758]">⭐ {{ $item->total_poin }} poin</span>
                                    @if($item->isWithinApprovalPeriod())
                                        <span class="text-[#2E7D3E] text-[10px] font-medium">✅ Aktif</span>
                                    @else
                                        <span class="text-[#ec1d1d] text-[10px] font-medium">❌ Expired</span>
                                    @endif
                                    <a href="{{ route('hr.sunnah.detail', $item->id) }}"
                                       class="text-[#00a2e9] font-medium">Detail →</a>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                @empty
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 hover:shadow-lg transition-all duration-300 p-6 sm:p-8 text-center text-[#1B1B1B]">
                        <svg class="w-12 sm:w-16 h-12 sm:h-16 text-gray-400 mb-4 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <p class="text-sm sm:text-base md:text-lg font-semibold">Belum ada data 7SPS untuk filter ini</p>
                    </div>
                @endforelse

                @if($sunnahData->total() > 0)
                    <div id="pagination-wrapper" class="bg-white rounded-2xl shadow-sm border border-slate-100 hover:shadow-lg transition-all duration-300 p-3 sm:p-4 mt-2">
                        {{ $sunnahData->onEachSide(1)->links() }}
                    </div>
                @endif
            </form>
            </div>
            {{-- ============ /AJAX CONTENT ============ --}}
        </div>
    </div>
</div>

{{-- ============ MODAL: KOMENTAR / KETERANGAN BULK ACTION ============ --}}
<div id="comment-modal" class="fixed inset-0 z-50 hidden items-center justify-center p-3 sm:p-4">
    <div id="comment-modal-backdrop" class="absolute inset-0 bg-black/50 backdrop-blur-sm"></div>

    <div class="relative bg-white w-full max-w-md sm:max-w-lg rounded-2xl shadow-2xl overflow-hidden animate-[fadeIn_0.15s_ease-out]">
        <div id="comment-modal-header" class="px-4 sm:px-6 py-3 sm:py-4 text-white flex items-center justify-between">
            <h3 id="comment-modal-title" class="text-sm sm:text-base md:text-lg font-bold flex items-center gap-2">
                <span id="comment-modal-icon">✅</span>
                <span id="comment-modal-title-text">Setujui Data Terpilih</span>
            </h3>
            <button type="button" onclick="closeCommentModal()" class="text-white/80 hover:text-white text-xl leading-none">&times;</button>
        </div>

        <div class="p-4 sm:p-6 space-y-3 sm:space-y-4">
            <div class="bg-[#F5F5F5] rounded-xl px-3 sm:px-4 py-2.5 sm:py-3 text-xs sm:text-sm text-[#1B1B1B]">
                Anda akan mengubah status <strong id="comment-modal-count">0</strong> data terpilih.
                Komentar/keterangan berikut akan diterapkan ke <strong>semua data yang dicentang</strong>.
            </div>

            <div>
                <label for="comment-modal-textarea" class="block text-xs sm:text-sm font-medium text-[#1B1B1B] mb-1">
                    Komentar / Keterangan <span id="comment-modal-required" class="text-[#ec1d1d]"></span>
                </label>
                <textarea id="comment-modal-textarea" rows="4" maxlength="500"
                          placeholder="Tuliskan catatan atau alasan untuk data yang dipilih..."
                          class="w-full px-3 sm:px-4 py-2 text-sm sm:text-base border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#00a2e9] focus:border-transparent transition resize-none"></textarea>
                <div class="flex justify-between mt-1">
                    <p id="comment-modal-hint" class="text-[10px] sm:text-xs text-gray-500">Opsional, maksimal 500 karakter.</p>
                    <p class="text-[10px] sm:text-xs text-gray-400"><span id="comment-modal-charcount">0</span>/500</p>
                </div>
            </div>
        </div>

        <div class="px-4 sm:px-6 py-3 sm:py-4 bg-gray-50 flex flex-col-reverse sm:flex-row justify-end gap-2 border-t border-gray-100">
            <button type="button" onclick="closeCommentModal()"
                    class="w-full sm:w-auto px-4 sm:px-5 py-2 rounded-xl text-sm font-medium text-[#1B1B1B] bg-white border border-gray-300 hover:bg-gray-100 transition-colors duration-200">
                Batal
            </button>
            <button type="button" id="comment-modal-confirm" onclick="confirmBulkSubmit()"
                    class="w-full sm:w-auto px-4 sm:px-5 py-2 rounded-xl text-sm font-medium text-white bg-[#2E7D3E] hover:opacity-90 active:scale-95 transition-all duration-200 shadow-sm">
                Konfirmasi
            </button>
        </div>
    </div>
</div>

<style>
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(8px) scale(0.98); }
    to { opacity: 1; transform: translateY(0) scale(1); }
}
</style>

<script>
// Referensi elemen di dalam #ajax-content. Elemen ini DIGANTI setiap kali
// filter/pagination di-refresh via AJAX, jadi harus di-rebind (bukan const)
// supaya tidak menunjuk ke node lama yang sudah lepas dari DOM.
let bulkForm, bulkTargetStatus, bulkCatatanHr, bulkIdsContainer, bulkSelectedCount;
let btnBulkApprove, btnBulkReject, btnBulkPending, selectAllGlobal;

function bindBulkRefs() {
    bulkForm = document.getElementById('bulk-approve-form');
    bulkTargetStatus = document.getElementById('bulk-target-status');
    bulkCatatanHr = document.getElementById('bulk-catatan-hr');
    bulkIdsContainer = document.getElementById('bulk-ids-container');
    bulkSelectedCount = document.getElementById('bulk-selected-count');
    btnBulkApprove = document.getElementById('btn-bulk-approve');
    btnBulkReject = document.getElementById('btn-bulk-reject');
    btnBulkPending = document.getElementById('btn-bulk-pending');
    selectAllGlobal = document.getElementById('select-all-global');
}
bindBulkRefs();

const commentModal = document.getElementById('comment-modal');
const commentModalBackdrop = document.getElementById('comment-modal-backdrop');
const commentModalHeader = document.getElementById('comment-modal-header');
const commentModalIcon = document.getElementById('comment-modal-icon');
const commentModalTitleText = document.getElementById('comment-modal-title-text');
const commentModalCount = document.getElementById('comment-modal-count');
const commentModalTextarea = document.getElementById('comment-modal-textarea');
const commentModalCharcount = document.getElementById('comment-modal-charcount');
const commentModalRequired = document.getElementById('comment-modal-required');
const commentModalHint = document.getElementById('comment-modal-hint');
const commentModalConfirm = document.getElementById('comment-modal-confirm');

let pendingStatus = null;

const statusConfig = {
    approved: { title: 'Setujui Data Terpilih', icon: '✅', headerClass: 'bg-gradient-to-r from-[#2E7D3E] to-[#1a5a2a]', btnClass: 'bg-[#2E7D3E]', required: false },
    rejected: { title: 'Tolak Data Terpilih', icon: '❌', headerClass: 'bg-gradient-to-r from-[#ec1d1d] to-[#b31414]', btnClass: 'bg-[#ec1d1d]', required: true },
    pending:  { title: 'Kembalikan ke Menunggu', icon: '⏳', headerClass: 'bg-gradient-to-r from-[#FCC626] to-[#e0ab00]', btnClass: 'bg-[#FCC626] text-[#1B1B1B]', required: false },
};

// PENTING: setiap baris data punya DUA checkbox dengan value (id) yang sama —
// satu di tabel desktop (hidden di mobile), satu lagi di kartu mobile (hidden
// di desktop) — supaya tampilan tetap responsive. Karena itu, jumlah "data
// dipilih" HARUS dihitung berdasarkan id yang UNIK (bukan jumlah elemen
// checkbox), dan setiap kali satu checkbox dicentang, checkbox kembarannya
// (dengan id yang sama) ikut disamakan. Ini memastikan jumlah data yang
// tercentang selalu sama persis dengan jumlah data yang benar-benar terpilih,
// di layar ukuran apa pun.
function setRowChecked(value, checked) {
    document.querySelectorAll(`.row-check[value="${CSS.escape(String(value))}"]`).forEach(cb => {
        if (!cb.disabled) cb.checked = checked;
    });
}

function getUniqueCheckedIds() {
    const ids = new Set();
    document.querySelectorAll('.row-check:checked').forEach(cb => ids.add(cb.value));
    return Array.from(ids);
}

function getUniqueSelectableIds() {
    const ids = new Set();
    document.querySelectorAll('.row-check:not(:disabled)').forEach(cb => ids.add(cb.value));
    return Array.from(ids);
}

function refreshBulkToolbar() {
    const checkedIds = getUniqueCheckedIds();
    bulkSelectedCount.textContent = checkedIds.length;
    btnBulkApprove.disabled = checkedIds.length === 0;
    btnBulkReject.disabled = checkedIds.length === 0;
    btnBulkPending.disabled = checkedIds.length === 0;

    const allIds = getUniqueSelectableIds();
    selectAllGlobal.checked = allIds.length > 0 && checkedIds.length === allIds.length;

    // Sinkronkan juga status "pilih semua di divisi ini" per grup.
    document.querySelectorAll('.divisi-check-all').forEach(groupCb => {
        const slug = groupCb.dataset.divisi;
        const groupIds = new Set();
        document.querySelectorAll(`.row-check.divisi-${slug}:not(:disabled)`).forEach(cb => groupIds.add(cb.value));
        const groupCheckedIds = new Set();
        document.querySelectorAll(`.row-check.divisi-${slug}:checked`).forEach(cb => groupCheckedIds.add(cb.value));
        groupCb.checked = groupIds.size > 0 && groupCheckedIds.size === groupIds.size;
    });
}

document.addEventListener('change', function (event) {
    if (event.target.classList.contains('row-check')) {
        // Samakan checkbox kembarannya (desktop <-> mobile) untuk id yang sama.
        setRowChecked(event.target.value, event.target.checked);
        refreshBulkToolbar();
    }

    if (event.target.classList.contains('divisi-check-all')) {
        const divisiSlug = event.target.dataset.divisi;
        const ids = new Set();
        document.querySelectorAll(`.row-check.divisi-${divisiSlug}`).forEach(cb => ids.add(cb.value));
        ids.forEach(id => setRowChecked(id, event.target.checked));
        refreshBulkToolbar();
    }

    if (event.target === selectAllGlobal) {
        getUniqueSelectableIds().forEach(id => setRowChecked(id, selectAllGlobal.checked));
        refreshBulkToolbar();
    }
});

function openCommentModal(status) {
    const checkedIds = getUniqueCheckedIds();
    if (checkedIds.length === 0) return;

    pendingStatus = status;
    const cfg = statusConfig[status];

    commentModalTitleText.textContent = cfg.title;
    commentModalIcon.textContent = cfg.icon;
    commentModalCount.textContent = checkedIds.length;
    commentModalTextarea.value = '';
    commentModalCharcount.textContent = '0';

    commentModalHeader.className = 'px-4 sm:px-6 py-3 sm:py-4 text-white flex items-center justify-between rounded-t-2xl ' + cfg.headerClass;
    commentModalConfirm.className = 'w-full sm:w-auto px-4 sm:px-5 py-2 rounded-xl text-sm font-medium hover:opacity-90 active:scale-95 transition-all duration-200 shadow-sm ' +
        (status === 'pending' ? 'text-[#1B1B1B] ' : 'text-white ') + cfg.btnClass;

    if (cfg.required) {
        commentModalRequired.textContent = '(wajib diisi)';
        commentModalHint.textContent = 'Wajib diisi untuk data yang ditolak, maksimal 500 karakter.';
    } else {
        commentModalRequired.textContent = '';
        commentModalHint.textContent = 'Opsional, maksimal 500 karakter.';
    }

    commentModal.classList.remove('hidden');
    commentModal.classList.add('flex');
    document.body.style.overflow = 'hidden';
    setTimeout(() => commentModalTextarea.focus(), 50);
}

function closeCommentModal() {
    commentModal.classList.add('hidden');
    commentModal.classList.remove('flex');
    document.body.style.overflow = '';
    pendingStatus = null;
}

commentModalTextarea.addEventListener('input', function () {
    commentModalCharcount.textContent = this.value.length;
});

commentModalBackdrop.addEventListener('click', closeCommentModal);

document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape' && !commentModal.classList.contains('hidden')) {
        closeCommentModal();
    }
});

function confirmBulkSubmit() {
    if (!pendingStatus) return;

    const cfg = statusConfig[pendingStatus];
    const catatan = commentModalTextarea.value.trim();

    if (cfg.required && catatan === '') {
        commentModalTextarea.classList.add('ring-2', 'ring-[#ec1d1d]', 'border-[#ec1d1d]');
        commentModalTextarea.focus();
        return;
    }

    const checkedIds = getUniqueCheckedIds();
    if (checkedIds.length === 0) {
        closeCommentModal();
        return;
    }

    bulkIdsContainer.innerHTML = '';
    checkedIds.forEach(id => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'ids[]';
        input.value = id;
        bulkIdsContainer.appendChild(input);
    });

    bulkTargetStatus.value = pendingStatus;
    bulkCatatanHr.value = catatan;

    closeCommentModal();
    bulkForm.submit();
}

// ============ AJAX FILTER & PAGINATION (stay on the same page) ============
// Supaya filter/pindah halaman data TIDAK reload seluruh halaman (state
// scroll & komponen lain tetap terjaga), form filter + link pagination
// di-intercept dan datanya diambil via fetch(), lalu hanya bagian
// #ajax-content (statistik, toolbar, daftar data, pagination) yang diganti.
// Modal keterangan/komentar tetap wajib muncul dulu sebelum bulk approve
// dikirim, karena tombol aksi bulk selalu memanggil openCommentModal()
// terlebih dahulu (lihat di atas) — perilaku ini tidak berubah walau data
// sedang difilter, karena hidden input filter di dalam form bulk selalu
// ikut ter-refresh bersama #ajax-content.
const filterForm = document.getElementById('filter-form');
// #ajax-content sendiri (wrapper-nya) tidak pernah diganti -- hanya
// innerHTML-nya -- jadi aman di-cache. Tapi elemen DI DALAMNYA (overlay,
// tombol, dsb) ikut diganti setiap refresh, jadi harus selalu di-query ulang
// (jangan di-cache) supaya tidak menunjuk ke node lama yang sudah lepas.
const ajaxContent = document.getElementById('ajax-content');
const filterSubmitBtn = document.getElementById('filter-submit-btn');
const filterSubmitSpinner = document.getElementById('filter-submit-spinner');

function setAjaxLoading(isLoading) {
    const overlay = document.getElementById('ajax-loading-overlay');
    if (overlay) {
        overlay.classList.toggle('hidden', !isLoading);
        overlay.classList.toggle('flex', isLoading);
    }
    if (ajaxContent) {
        ajaxContent.classList.toggle('pointer-events-none', isLoading);
    }
    if (filterSubmitBtn) filterSubmitBtn.disabled = isLoading;
    if (filterSubmitSpinner) filterSubmitSpinner.classList.toggle('hidden', !isLoading);
}

async function loadFilteredData(url, { pushState = true } = {}) {
    setAjaxLoading(true);
    try {
        const res = await fetch(url, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
        });
        if (!res.ok) throw new Error('Gagal memuat data (' + res.status + ')');

        const html = await res.text();
        const parsed = new DOMParser().parseFromString(html, 'text/html');

        const newAjaxContent = parsed.getElementById('ajax-content');
        const newFilterFields = parsed.getElementById('filter-fields');

        if (newAjaxContent) {
            ajaxContent.innerHTML = newAjaxContent.innerHTML;
        }
        if (newFilterFields) {
            document.getElementById('filter-fields').innerHTML = newFilterFields.innerHTML;
        }

        // Elemen di dalam #ajax-content baru saja diganti total -> rebind referensi JS.
        bindBulkRefs();
        refreshBulkToolbar();

        if (pushState) {
            window.history.pushState({ ajaxUrl: url }, '', url);
        }
    } catch (err) {
        // Fallback aman: kalau AJAX gagal (mis. jaringan bermasalah), tetap
        // navigasi normal supaya user tidak "stuck" tanpa hasil.
        window.location.href = url;
    } finally {
        setAjaxLoading(false);
    }
}

// Submit form filter via AJAX (tetap di halaman yang sama).
if (filterForm) {
    filterForm.addEventListener('submit', function (event) {
        event.preventDefault();
        const params = new URLSearchParams(new FormData(filterForm)).toString();
        const url = filterForm.action + (params ? '?' + params : '');
        loadFilteredData(url);
    });
}

// Intercept klik pada link pagination & tombol "Reset" filter supaya juga
// tetap di halaman yang sama (tidak full reload). Link "Detail" pada setiap
// baris data SENGAJA tidak di-intercept karena memang harus membuka halaman
// detail seperti biasa.
document.addEventListener('click', function (event) {
    const link = event.target.closest('#pagination-wrapper a[href], #filter-fields a[href]');
    if (!link) return;

    event.preventDefault();
    loadFilteredData(link.getAttribute('href'));
});

// Dukungan tombol back/forward browser.
window.addEventListener('popstate', function () {
    loadFilteredData(window.location.href, { pushState: false });
});
</script>
@endsection
