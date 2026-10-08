@extends('layouts.app')

@section('content')
<div class="flex min-h">
    @include('layouts.sidebar')
    <div class="flex-1 transition-all duration-300 md:ml-64">
        <div class="p-4 sm:p-6 lg:p-8">

            {{-- Header --}}
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-bold text-[#161758] tracking-tight">Verifikasi Absen</h1>
                    <p class="text-sm text-gray-500 mt-1">Input manual jam masuk &amp; jam keluar karyawan oleh HR</p>
                </div>
                <a href="{{ route('hr.absensi.index') }}"
                   class="inline-flex items-center justify-center px-5 py-2.5 bg-white text-gray-700 rounded-xl hover:bg-gray-50 transition-all duration-200 text-sm font-medium shadow-sm gap-2 border border-gray-200">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    <span>Kembali</span>
                </a>
            </div>

            {{-- GPS Status Card --}}
            <div id="gpsCard" class="bg-white rounded-2xl shadow-sm border border-slate-100 hover:shadow-lg transition-all duration-300 p-5 sm:p-6 mb-6">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div id="gpsIcon" class="w-10 h-10 sm:w-12 sm:h-12 rounded-full bg-yellow-100 flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 sm:w-6 sm:h-6 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </div>
                        <div>
                            <p id="gpsStatusText" class="text-sm font-semibold text-yellow-600">Mendeteksi lokasi device HR...</p>
                            <p id="gpsDetailText" class="text-xs text-gray-500 mt-0.5">GPS diperlukan untuk verifikasi absen</p>
                        </div>
                    </div>
                    <button onclick="getHrLocation(true)" id="btnRefreshGps"
                            class="inline-flex items-center gap-1.5 px-4 py-2 bg-[#27438D] text-white text-sm rounded-xl hover:bg-[#161758] transition-all duration-200 font-medium flex-shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        Refresh GPS
                    </button>
                </div>
                <div class="mt-4 grid grid-cols-2 sm:grid-cols-4 gap-2 text-xs">
                    <div class="bg-gray-50 rounded-lg p-2.5">
                        <span class="text-gray-400 block">Latitude</span>
                        <p id="gpsLat" class="font-semibold text-gray-700">-</p>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-2.5">
                        <span class="text-gray-400 block">Longitude</span>
                        <p id="gpsLng" class="font-semibold text-gray-700">-</p>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-2.5">
                        <span class="text-gray-400 block">Akurasi</span>
                        <p id="gpsAccuracy" class="font-semibold text-gray-700">-</p>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-2.5">
                        <span class="text-gray-400 block">Status GPS</span>
                        <p id="gpsValidStatus" class="font-semibold text-yellow-600">Menunggu...</p>
                    </div>
                </div>
            </div>

            {{-- Stats Cards --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4 mb-6">
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 hover:shadow-lg transition-all duration-300 p-4 border-l-4 border-[#161758]">
                    <p class="text-[10px] sm:text-xs font-medium text-gray-400 uppercase tracking-wider">Total Karyawan</p>
                    <p class="text-xl sm:text-2xl font-bold text-[#161758] mt-1">{{ $stats['total'] }}</p>
                </div>
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 hover:shadow-lg transition-all duration-300 p-4 border-l-4 border-[#2E7D3E]">
                    <p class="text-[10px] sm:text-xs font-medium text-gray-400 uppercase tracking-wider">Sudah Check-in</p>
                    <p class="text-xl sm:text-2xl font-bold text-[#2E7D3E] mt-1">{{ $stats['sudah_checkin'] }}</p>
                </div>
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 hover:shadow-lg transition-all duration-300 p-4 border-l-4 border-[#00a2e9]">
                    <p class="text-[10px] sm:text-xs font-medium text-gray-400 uppercase tracking-wider">Sudah Check-out</p>
                    <p class="text-xl sm:text-2xl font-bold text-[#00a2e9] mt-1">{{ $stats['sudah_checkout'] }}</p>
                </div>
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 hover:shadow-lg transition-all duration-300 p-4 border-l-4 border-[#ec1d1d]">
                    <p class="text-[10px] sm:text-xs font-medium text-gray-400 uppercase tracking-wider">Belum Absen</p>
                    <p class="text-xl sm:text-2xl font-bold text-[#ec1d1d] mt-1">{{ $stats['belum_absen'] }}</p>
                </div>
            </div>

            {{-- Filter & Tanggal --}}
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 hover:shadow-lg transition-all duration-300 p-5 sm:p-6 mb-6">
                <form action="{{ route('hr.absensi.verifikasi') }}" method="GET" class="flex flex-col sm:flex-row gap-3 sm:items-end">
                    <div class="flex-1 sm:max-w-xs">
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Tanggal Absensi</label>
                        <input type="date" name="tanggal" value="{{ $selectedDate->format('Y-m-d') }}"
                               class="w-full px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-[#00a2e9] focus:border-transparent transition">
                    </div>
                    <div class="flex-1 sm:max-w-xs">
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Cari Karyawan</label>
                        <input type="text" id="searchEmployee" placeholder="Nama / Kode / Jabatan..."
                               class="w-full px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-[#00a2e9] focus:border-transparent transition">
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="px-5 py-2.5 bg-[#00a2e9] text-white rounded-xl hover:bg-[#0088c4] transition-all duration-200 text-sm font-medium shadow-sm">Filter</button>
                        <a href="{{ route('hr.absensi.verifikasi') }}" class="px-5 py-2.5 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 transition-all duration-200 text-sm font-medium">Reset</a>
                    </div>
                </form>
            </div>

            {{-- Table --}}
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 hover:shadow-lg transition-all duration-300 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100" id="employeeTable">
                        <thead class="bg-gray-50/80">
                            <tr>
                                <th class="px-4 py-3.5 text-left text-[10px] sm:text-xs font-semibold text-gray-500 uppercase tracking-wider">Karyawan</th>
                                <th class="px-4 py-3.5 text-left text-[10px] sm:text-xs font-semibold text-gray-500 uppercase tracking-wider hidden sm:table-cell">Jabatan / Divisi</th>
                                <th class="px-4 py-3.5 text-left text-[10px] sm:text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-4 py-3.5 text-left text-[10px] sm:text-xs font-semibold text-gray-500 uppercase tracking-wider hidden md:table-cell">Check-in</th>
                                <th class="px-4 py-3.5 text-left text-[10px] sm:text-xs font-semibold text-gray-500 uppercase tracking-wider hidden md:table-cell">Check-out</th>
                                <th class="px-4 py-3.5 text-left text-[10px] sm:text-xs font-semibold text-gray-500 uppercase tracking-wider hidden lg:table-cell">Total Jam</th>
                                <th class="px-4 py-3.5 text-left text-[10px] sm:text-xs font-semibold text-gray-500 uppercase tracking-wider hidden xl:table-cell">Lokasi</th>
                                <th class="px-4 py-3.5 text-center text-[10px] sm:text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100" id="employeeBody">
                            @forelse($paginator as $emp)
                                <tr class="hover:bg-gray-50/70 transition-colors employee-row"
                                    data-search="{{ strtolower($emp['nama'] . ' ' . $emp['kode_pegawai'] . ' ' . $emp['jabatan'] . ' ' . ($emp['divisi'] ?? '')) }}"
                                    data-id="{{ $emp['id'] }}"
                                    data-nama="{{ addslashes($emp['nama']) }}"
                                    data-kode="{{ $emp['kode_pegawai'] }}"
                                    data-jabatan="{{ $emp['jabatan'] }}"
                                    data-divisi="{{ $emp['divisi'] ?? '' }}"
                                    data-checkin="{{ $emp['check_in'] ?? '' }}"
                                    data-checkout="{{ $emp['check_out'] ?? '' }}"
                                    data-status="{{ $emp['status'] }}"
                                    data-totaljam="{{ $emp['total_jam_kerja'] ?? 0 }}"
                                    data-durasiteks="{{ $emp['durasi_teks'] ?? '-' }}">
                                    <td class="px-4 py-3.5">
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">{{ $emp['nama'] }}</p>
                                            <p class="text-xs text-gray-500">{{ $emp['kode_pegawai'] }}</p>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3.5 hidden sm:table-cell">
                                        <p class="text-sm text-gray-700">{{ $emp['jabatan'] }}</p>
                                        <p class="text-xs text-gray-500">{{ $emp['divisi'] ?? '-' }}</p>
                                    </td>
                                    <td class="px-4 py-3.5">
                                        @php
                                            $statusClass = match ($emp['status']) {
                                                'Hadir'           => 'bg-[#2E7D3E] text-white',
                                                'Izin'            => 'bg-[#FCC626] text-[#1B1B1B]',
                                                'Sakit'           => 'bg-[#00a2e9] text-white',
                                                'Alpha'           => 'bg-[#ec1d1d] text-white',
                                                'Perjalanan Dinas' => 'bg-purple-600 text-white',
                                                'Cuti'            => 'bg-[#27438D] text-white',
                                                default           => 'bg-gray-200 text-gray-800',
                                            };
                                        @endphp
                                        <span class="inline-block px-2.5 py-1 rounded-full text-xs font-semibold {{ $statusClass }}">{{ $emp['status'] }}</span>
                                    </td>
                                    <td class="px-4 py-3.5 hidden md:table-cell">
                                        @if ($emp['check_in'])
                                            <span class="text-sm font-semibold text-[#2E7D3E]">{{ $emp['check_in'] }}</span>
                                        @else
                                            <span class="text-sm text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3.5 hidden md:table-cell">
                                        @if ($emp['check_out'])
                                            <span class="text-sm font-semibold text-[#00a2e9]">{{ $emp['check_out'] }}</span>
                                        @else
                                            <span class="text-sm text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3.5 hidden lg:table-cell">
                                        @if (($emp['total_jam_kerja'] ?? 0) > 0 || ($emp['check_in'] && $emp['check_out']))
                                            <div class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-indigo-50 text-[#161758] border border-indigo-100/70 font-semibold text-xs" title="{{ $emp['durasi_teks'] ?? '' }}">
                                                <svg class="w-3.5 h-3.5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                <span>{{ $emp['total_jam_kerja'] }} Jam</span>
                                            </div>
                                        @else
                                            <span class="text-sm text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3.5 hidden xl:table-cell">
                                        <span class="text-xs text-gray-600">{{ $emp['kantor_cabang'] }}</span>
                                    </td>
                                    <td class="px-4 py-3.5 text-center">
                                        <button type="button"
                                                onclick="openVerifikasiModal({{ $emp['id'] }}, '{{ addslashes($emp['nama']) }}', '{{ $emp['kode_pegawai'] }}', '{{ $emp['jabatan'] }}', '{{ $emp['check_in'] ?? '' }}', '{{ $emp['check_out'] ?? '' }}', '{{ $emp['status'] }}', '{{ $emp['total_jam_kerja'] ?? 0 }}', '{{ $emp['durasi_teks'] ?? '-' }}')"
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-[#161758] text-white text-xs font-semibold rounded-lg hover:bg-[#0a0b33] transition-all duration-200 shadow-sm hover:shadow">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                            <span>Verifikasi / Ubah</span>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-4 py-12 text-center">
                                        <div class="flex flex-col items-center justify-center text-gray-400">
                                            <svg class="w-12 h-12 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            </svg>
                                            <p class="text-sm font-medium">Tidak ada data karyawan.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                @if ($paginator->total() > 0)
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-t border-gray-100 px-4 py-4">
                        <p class="text-xs sm:text-sm text-gray-500 text-center sm:text-left">
                            Menampilkan <span class="font-semibold text-gray-700">{{ $paginator->firstItem() }}</span> – <span class="font-semibold text-gray-700">{{ $paginator->lastItem() }}</span>
                            dari <span class="font-semibold text-gray-700">{{ $paginator->total() }}</span> karyawan
                            @if ($selectedDate->isToday())
                                &middot; Hari ini {{ $selectedDate->format('d/m/Y') }}
                            @else
                                &middot; {{ $selectedDate->format('d/m/Y') }}
                            @endif
                            &middot; Hal. {{ $paginator->currentPage() }}/{{ $paginator->lastPage() }}
                        </p>
                        <div class="flex items-center justify-center gap-2">
                            @if ($paginator->onFirstPage())
                                <span class="inline-flex items-center gap-1 px-4 py-2 rounded-xl text-sm font-medium text-gray-300 bg-gray-50 cursor-not-allowed select-none">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                                    Previous
                                </span>
                            @else
                                <a href="{{ $paginator->previousPageUrl() }}" class="inline-flex items-center gap-1 px-4 py-2 rounded-xl text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                                    Previous
                                </a>
                            @endif
                            @if ($paginator->hasMorePages())
                                <a href="{{ $paginator->nextPageUrl() }}" class="inline-flex items-center gap-1 px-4 py-2 rounded-xl text-sm font-medium text-white bg-[#00a2e9] hover:bg-[#0088c4] transition-colors">
                                    Next
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </a>
                            @else
                                <span class="inline-flex items-center gap-1 px-4 py-2 rounded-xl text-sm font-medium text-gray-300 bg-gray-50 cursor-not-allowed select-none">
                                    Next
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </span>
                            @endif
                        </div>
                    </div>
                @endif
            </div>

            {{-- Keterangan --}}
            <div class="mt-6 bg-white rounded-2xl shadow-sm border border-slate-100 hover:shadow-lg transition-all duration-300 p-5 sm:p-6">
                <h3 class="text-sm font-semibold text-[#161758] mb-2">Petunjuk &amp; Ketentuan Verifikasi Absen</h3>
                <ul class="text-xs text-gray-500 space-y-1.5">
                    <li>• <strong class="text-gray-700">Fleksibel &amp; Korektif:</strong> HR dapat mengatur dan mengedit jam masuk maupun jam keluar kapan saja secara manual.</li>
                    <li>• <strong class="text-gray-700">Akumulasi Otomatis:</strong> Total jam kerja dan rincian durasi (jam &amp; menit) otomatis dihitung dan tersinkronisasi saat jam masuk dan pulang diisi.</li>
                    <li>• <strong class="text-gray-700">GPS Terintegrasi:</strong> Lokasi kantor dicocokkan otomatis melalui GPS perangkat HR yang memverifikasi.</li>
                    <li>• <strong class="text-gray-700">Audit Trail:</strong> Riwayat perubahan waktu presensi akan tercatat rapi pada sistem demi transparansi.</li>
                </ul>
            </div>
        </div>
    </div>
</div>

{{-- ============================================================
     MODAL VERIFIKASI & UBAH ABSEN
     ============================================================ --}}
<div id="verifikasiModal" class="fixed inset-0 z-[9999] hidden">
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" onclick="closeVerifikasiModal()"></div>
    <div class="absolute inset-0 flex items-end sm:items-center justify-center p-0 sm:p-4 pointer-events-none">
        <div id="verifikasiModalPanel" class="bg-white rounded-t-3xl sm:rounded-2xl shadow-2xl w-full sm:max-w-lg pointer-events-auto max-h-[92vh] overflow-y-auto transform translate-y-full sm:translate-y-0 transition-transform duration-300 ease-out">

            {{-- Modal Header --}}
            <div class="sticky top-0 bg-white z-10 px-6 pt-5 pb-4 border-b border-gray-100 rounded-t-3xl sm:rounded-t-2xl">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center flex-shrink-0 text-[#161758] border border-indigo-100">
                            <svg class="w-5 h-5 text-[#161758]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-[#161758]">Verifikasi &amp; Koreksi Presensi</h3>
                            <p id="modalKaryawanInfo" class="text-xs text-gray-500 font-medium">-</p>
                        </div>
                    </div>
                    <button type="button" onclick="closeVerifikasiModal()" class="w-8 h-8 rounded-full hover:bg-gray-100 flex items-center justify-center transition text-gray-400 hover:text-gray-700">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>

            {{-- Modal Body --}}
            <div class="px-6 py-5">
                {{-- GPS indicator --}}
                <div id="modalGpsBadge" class="flex items-center justify-between px-3.5 py-2.5 rounded-xl bg-yellow-50 border border-yellow-200 mb-5">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-yellow-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        </svg>
                        <span class="text-xs font-semibold text-yellow-700">GPS Device HR: Siap</span>
                    </div>
                    <span id="vGpsSummary" class="text-[11px] text-gray-500 font-mono">-</span>
                </div>

                <form id="verifikasiForm" class="space-y-4">
                    <input type="hidden" id="vKaryawanId">
                    <input type="hidden" id="vNama">
                    <input type="hidden" id="vSelectedDate" value="{{ $selectedDate->format('Y-m-d') }}">

                    {{-- Status Presensi Saat Ini --}}
                    <div class="bg-slate-50 border border-slate-200/70 rounded-xl p-3.5">
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-2">Data Presensi Saat Ini</p>
                        <div class="grid grid-cols-3 gap-2 text-center">
                            <div class="bg-white rounded-lg p-2 border border-slate-100 shadow-2xs">
                                <p class="text-[10px] text-gray-400 font-medium">Check-in</p>
                                <p id="vCurrentCheckin" class="text-xs sm:text-sm font-bold text-[#2E7D3E] mt-0.5">-</p>
                            </div>
                            <div class="bg-white rounded-lg p-2 border border-slate-100 shadow-2xs">
                                <p class="text-[10px] text-gray-400 font-medium">Check-out</p>
                                <p id="vCurrentCheckout" class="text-xs sm:text-sm font-bold text-[#00a2e9] mt-0.5">-</p>
                            </div>
                            <div class="bg-white rounded-lg p-2 border border-slate-100 shadow-2xs">
                                <p class="text-[10px] text-gray-400 font-medium">Total Jam</p>
                                <p id="vCurrentTotalJam" class="text-xs sm:text-sm font-bold text-[#161758] mt-0.5">-</p>
                            </div>
                        </div>
                    </div>

                    {{-- Pilih Status --}}
                    <div>
                        <label for="vStatus" class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">Status Presensi</label>
                        <select id="vStatus" class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm font-semibold text-gray-800 focus:ring-2 focus:ring-[#161758] focus:border-[#161758] transition">
                            <option value="Hadir">Hadir</option>
                            <option value="Izin">Izin</option>
                            <option value="Sakit">Sakit</option>
                            <option value="Perjalanan Dinas">Perjalanan Dinas</option>
                            <option value="Cuti">Cuti</option>
                            <option value="Alpha">Alpha</option>
                        </select>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        {{-- Jam Masuk --}}
                        <div id="jamMasukGroup">
                            <div class="flex items-center justify-between mb-1.5">
                                <label for="vJamMasuk" class="flex items-center gap-1.5">
                                    <span class="w-5 h-5 rounded-full bg-[#2E7D3E] text-white text-[10px] font-bold flex items-center justify-center">M</span>
                                    <span class="text-xs font-bold text-gray-700 uppercase tracking-wider">Jam Masuk</span>
                                </label>
                                <button type="button" onclick="setJamMasuk('')" class="text-[11px] text-red-500 hover:text-red-700 font-medium">Kosongkan</button>
                            </div>
                            <div class="relative">
                                <input type="time" id="vJamMasuk"
                                       class="w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm font-bold text-gray-800 focus:ring-2 focus:ring-[#2E7D3E] focus:border-[#2E7D3E] transition bg-white">
                            </div>
                            <div class="flex gap-1.5 mt-2">
                                <button type="button" onclick="setJamMasuk('07:30')" class="px-2 py-1 bg-gray-100 hover:bg-gray-200 text-gray-700 text-[11px] font-medium rounded-md transition">07:30</button>
                                <button type="button" onclick="setJamMasuk('08:00')" class="px-2 py-1 bg-gray-100 hover:bg-gray-200 text-gray-700 text-[11px] font-medium rounded-md transition">08:00</button>
                                <button type="button" onclick="setJamMasuk('08:30')" class="px-2 py-1 bg-gray-100 hover:bg-gray-200 text-gray-700 text-[11px] font-medium rounded-md transition">08:30</button>
                            </div>
                        </div>

                        {{-- Jam Keluar --}}
                        <div id="jamKeluarGroup">
                            <div class="flex items-center justify-between mb-1.5">
                                <label for="vJamKeluar" class="flex items-center gap-1.5">
                                    <span class="w-5 h-5 rounded-full bg-[#00a2e9] text-white text-[10px] font-bold flex items-center justify-center">K</span>
                                    <span class="text-xs font-bold text-gray-700 uppercase tracking-wider">Jam Pulang</span>
                                </label>
                                <button type="button" onclick="setJamKeluar('')" class="text-[11px] text-red-500 hover:text-red-700 font-medium">Kosongkan</button>
                            </div>
                            <div class="relative">
                                <input type="time" id="vJamKeluar"
                                       class="w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm font-bold text-gray-800 focus:ring-2 focus:ring-[#00a2e9] focus:border-[#00a2e9] transition bg-white">
                            </div>
                            <div class="flex gap-1.5 mt-2">
                                <button type="button" onclick="setJamKeluar('16:00')" class="px-2 py-1 bg-gray-100 hover:bg-gray-200 text-gray-700 text-[11px] font-medium rounded-md transition">16:00</button>
                                <button type="button" onclick="setJamKeluar('16:30')" class="px-2 py-1 bg-gray-100 hover:bg-gray-200 text-gray-700 text-[11px] font-medium rounded-md transition">16:30</button>
                                <button type="button" onclick="setJamKeluar('17:00')" class="px-2 py-1 bg-gray-100 hover:bg-gray-200 text-gray-700 text-[11px] font-medium rounded-md transition">17:00</button>
                            </div>
                        </div>
                    </div>

                    {{-- Estimasi & Akumulasi Jam Kerja Live --}}
                    <div id="estimasiJamKerja" class="bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200/80 rounded-xl p-4 text-center transition-all">
                        <div class="flex items-center justify-center gap-1.5 text-xs font-semibold text-blue-800 mb-1">
                            <svg class="w-4 h-4 text-[#161758]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span>Akumulasi Otomatis Jam Kerja</span>
                        </div>
                        <p id="vEstimasiJam" class="text-xl sm:text-2xl font-black text-[#161758] tracking-tight">0 Jam</p>
                        <p id="vEstimasiDetail" class="text-xs text-gray-500 mt-0.5">Isi Jam Masuk dan Jam Pulang untuk menghitung otomatis</p>
                    </div>

                    {{-- Submit --}}
                    <div class="flex gap-3 pt-2">
                        <button type="button" onclick="closeVerifikasiModal()"
                                class="flex-1 px-4 py-3 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 transition-all text-sm font-semibold">
                            Batal
                        </button>
                        <button type="button" onclick="submitVerifikasi()"
                                id="btnSubmitVerifikasi"
                                class="flex-1 px-4 py-3 bg-[#161758] text-white rounded-xl hover:bg-[#0a0b33] transition-all text-sm font-semibold shadow-md disabled:opacity-50 disabled:cursor-not-allowed">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- SweetAlert2 --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
let hrLocation = null;

// ==========================================================
// GPS LOCATION - HR Device
// ==========================================================
function getHrLocation(force = false) {
    const statusText = document.getElementById('gpsStatusText');
    const detailText = document.getElementById('gpsDetailText');
    const latEl      = document.getElementById('gpsLat');
    const lngEl      = document.getElementById('gpsLng');
    const accEl      = document.getElementById('gpsAccuracy');
    const validEl    = document.getElementById('gpsValidStatus');
    const card       = document.getElementById('gpsCard');
    const icon       = document.getElementById('gpsIcon');

    statusText.textContent = 'Mendeteksi lokasi device HR...';
    statusText.className   = 'text-sm font-semibold text-yellow-600';
    icon.className         = 'w-10 h-10 sm:w-12 sm:h-12 rounded-full bg-yellow-100 flex items-center justify-center flex-shrink-0';

    if (!navigator.geolocation) {
        statusText.textContent  = 'GPS tidak didukung browser ini';
        statusText.className    = 'text-sm font-semibold text-red-600';
        detailText.textContent  = 'Aktifkan akses lokasi di browser';
        icon.className          = 'w-10 h-10 sm:w-12 sm:h-12 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0';
        validEl.textContent     = 'Tidak tersedia';
        validEl.className       = 'font-semibold text-red-600';
        card.className          = 'bg-white rounded-2xl shadow-sm border border-slate-100 hover:shadow-lg transition-all duration-300 p-5 sm:p-6 mb-6 border-t-red-300 border-t-4';
        return;
    }

    navigator.geolocation.getCurrentPosition(
        function (position) {
            hrLocation = {
                latitude:  position.coords.latitude,
                longitude: position.coords.longitude,
                accuracy:  position.coords.accuracy
            };

            latEl.textContent = hrLocation.latitude.toFixed(6);
            lngEl.textContent = hrLocation.longitude.toFixed(6);
            accEl.textContent = '± ' + (hrLocation.accuracy || 0).toFixed(1) + ' m';
            validEl.textContent     = 'Siap digunakan';
            validEl.className       = 'font-semibold text-[#2E7D3E]';
            statusText.textContent  = 'GPS Aktif — Siap untuk verifikasi';
            statusText.className    = 'text-sm font-semibold text-[#2E7D3E]';
            detailText.textContent  = 'Lokasi device HR akan dicocokkan ke kantor presensi karyawan';
            icon.className          = 'w-10 h-10 sm:w-12 sm:h-12 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0';
            card.className          = 'bg-white rounded-2xl shadow-sm border border-slate-100 hover:shadow-lg transition-all duration-300 p-5 sm:p-6 mb-6 border-t-[#2E7D3E] border-t-4';

            updateModalGpsBadge(true);
        },
        function (error) {
            let msg = 'Gagal mendapatkan lokasi';
            if (error.code === error.PERMISSION_DENIED)       msg = 'Izin lokasi ditolak. Aktifkan izin lokasi di browser.';
            else if (error.code === error.POSITION_UNAVAILABLE) msg = 'Informasi lokasi tidak tersedia.';
            else if (error.code === error.TIMEOUT)              msg = 'Waktu pengambilan lokasi habis.';

            statusText.textContent  = 'GPS Error';
            statusText.className    = 'text-sm font-semibold text-red-600';
            detailText.textContent  = msg;
            icon.className          = 'w-10 h-10 sm:w-12 sm:h-12 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0';
            validEl.textContent     = 'Error';
            validEl.className       = 'font-semibold text-red-600';
            card.className          = 'bg-white rounded-2xl shadow-sm border border-slate-100 hover:shadow-lg transition-all duration-300 p-5 sm:p-6 mb-6 border-t-red-300 border-t-4';

            updateModalGpsBadge(false);
        },
        { enableHighAccuracy: true, timeout: 15000, maximumAge: 5000 }
    );
}

function updateModalGpsBadge(active) {
    const badge = document.getElementById('modalGpsBadge');
    const summary = document.getElementById('vGpsSummary');
    if (active && hrLocation) {
        badge.className = 'flex items-center justify-between px-3.5 py-2.5 rounded-xl bg-green-50 border border-green-200 mb-5';
        badge.querySelector('svg').classList.remove('text-yellow-500', 'text-red-500');
        badge.querySelector('svg').classList.add('text-[#2E7D3E]');
        badge.querySelector('span').textContent = 'GPS Device HR: Siap';
        badge.querySelector('span').className   = 'text-xs font-semibold text-[#2E7D3E]';
        summary.textContent = hrLocation.latitude.toFixed(4) + ', ' + hrLocation.longitude.toFixed(4);
    } else {
        badge.className = 'flex items-center justify-between px-3.5 py-2.5 rounded-xl bg-yellow-50 border border-yellow-200 mb-5';
        badge.querySelector('svg').classList.remove('text-[#2E7D3E]', 'text-red-500');
        badge.querySelector('svg').classList.add('text-yellow-500');
        badge.querySelector('span').textContent = 'GPS HR Belum Terdeteksi';
        badge.querySelector('span').className   = 'text-xs font-semibold text-yellow-700';
        summary.textContent = '-';
    }
}

// ==========================================================
// MODAL VERIFIKASI & UBAH
// ==========================================================
function openVerifikasiModal(id, nama, kode, jabatan, checkin, checkout, status, totalJam, durasiTeks) {
    const hasCheckin  = checkin && checkin !== '';
    const hasCheckout = checkout && checkout !== '';

    document.getElementById('vKaryawanId').value = id;
    document.getElementById('vNama').value        = nama;
    document.getElementById('modalKaryawanInfo').textContent = nama + ' (' + (kode || '-') + ') · ' + (jabatan || '-');
    document.getElementById('vCurrentCheckin').textContent  = hasCheckin  ? checkin  : '-';
    document.getElementById('vCurrentCheckout').textContent = hasCheckout ? checkout : '-';
    document.getElementById('vCurrentTotalJam').textContent  = (totalJam > 0 ? totalJam + ' Jam' : '-') + (durasiTeks && durasiTeks !== '-' ? ' (' + durasiTeks + ')' : '');

    const jamMasukInput  = document.getElementById('vJamMasuk');
    const jamKeluarInput = document.getElementById('vJamKeluar');
    const statusSelect   = document.getElementById('vStatus');

    jamMasukInput.value  = hasCheckin ? checkin : '';
    jamKeluarInput.value = hasCheckout ? checkout : '';

    if (status && status !== '') {
        statusSelect.value = status;
    } else {
        statusSelect.value = (hasCheckin || hasCheckout) ? 'Hadir' : 'Alpha';
    }

    updateEstimasiJam();

    // Show modal
    const modal      = document.getElementById('verifikasiModal');
    const modalPanel = document.getElementById('verifikasiModalPanel');
    modal.classList.remove('hidden');
    requestAnimationFrame(() => {
        modalPanel.classList.remove('translate-y-full');
    });
    document.body.style.overflow = 'hidden';
}

function closeVerifikasiModal() {
    const modal      = document.getElementById('verifikasiModal');
    const modalPanel = document.getElementById('verifikasiModalPanel');
    modalPanel.classList.add('translate-y-full');
    setTimeout(() => {
        modal.classList.add('hidden');
        document.body.style.overflow = '';
    }, 300);
}

function setJamMasuk(val) {
    document.getElementById('vJamMasuk').value = val;
    updateEstimasiJam();
}

function setJamKeluar(val) {
    document.getElementById('vJamKeluar').value = val;
    updateEstimasiJam();
}

function updateEstimasiJam() {
    const masukVal  = document.getElementById('vJamMasuk').value;
    const keluarVal = document.getElementById('vJamKeluar').value;
    const elText    = document.getElementById('vEstimasiJam');
    const elDetail  = document.getElementById('vEstimasiDetail');

    if (masukVal && keluarVal) {
        const [mh, mm] = masukVal.split(':').map(Number);
        const [kh, km] = keluarVal.split(':').map(Number);

        let inMinutes = mh * 60 + mm;
        let outMinutes = kh * 60 + km;

        if (outMinutes < inMinutes) {
            // Lewat tengah malam
            outMinutes += 24 * 60;
        }

        const diffMin = Math.max(0, outMinutes - inMinutes);
        const totalJamRounded = Math.round(diffMin / 60);
        const jam = Math.floor(diffMin / 60);
        const menit = diffMin % 60;

        let durasiTeks = '';
        if (jam > 0 && menit > 0) {
            durasiTeks = `${jam} Jam ${menit} Menit`;
        } else if (jam > 0) {
            durasiTeks = `${jam} Jam`;
        } else {
            durasiTeks = `${menit} Menit`;
        }

        elText.innerHTML = `<span class="text-2xl font-black text-[#161758]">${totalJamRounded} Jam</span> <span class="text-sm font-semibold text-gray-500">(${durasiTeks})</span>`;
        elDetail.textContent = `Dihitung otomatis: ${masukVal} s/d ${keluarVal}`;
    } else if (masukVal && !keluarVal) {
        elText.innerHTML = `<span class="text-base font-bold text-[#2E7D3E]">Check-in ${masukVal}</span>`;
        elDetail.textContent = 'Belum ada jam pulang (Total Jam Kerja dihitung setelah jam pulang diisi)';
    } else if (!masukVal && keluarVal) {
        elText.innerHTML = `<span class="text-base font-bold text-[#00a2e9]">Check-out ${keluarVal}</span>`;
        elDetail.textContent = 'Jam masuk belum diisi';
    } else {
        elText.innerHTML = `<span class="text-base font-bold text-gray-400">0 Jam</span>`;
        elDetail.textContent = 'Isi Jam Masuk dan Jam Pulang untuk menghitung otomatis';
    }
}

// Listen for time changes
document.getElementById('vJamMasuk').addEventListener('input', updateEstimasiJam);
document.getElementById('vJamKeluar').addEventListener('input', updateEstimasiJam);

// ==========================================================
// SUBMIT VERIFIKASI
// ==========================================================
function submitVerifikasi() {
    const karyawanId = document.getElementById('vKaryawanId').value;
    const nama       = document.getElementById('vNama').value;
    const tanggal    = document.getElementById('vSelectedDate').value;
    const jamMasuk   = document.getElementById('vJamMasuk').value;
    const jamKeluar  = document.getElementById('vJamKeluar').value;
    const status     = document.getElementById('vStatus').value;

    let detailWaktu = [];
    if (jamMasuk) detailWaktu.push('Masuk: ' + jamMasuk);
    if (jamKeluar) detailWaktu.push('Pulang: ' + jamKeluar);
    let summaryWaktu = detailWaktu.length > 0 ? detailWaktu.join(' | ') : 'Tanpa Jam Masuk & Pulang';

    Swal.fire({
        title: 'Simpan Presensi?',
        html: '<div class="text-left bg-gray-50 p-4 rounded-xl space-y-1.5 text-xs text-gray-600">' +
              '<p>Karyawan: <strong class="text-gray-900 text-sm">' + nama + '</strong></p>' +
              '<p>Status: <strong class="text-gray-900">' + status + '</strong></p>' +
              '<p>Waktu: <strong class="text-[#161758]">' + summaryWaktu + '</strong></p>' +
              (hrLocation ? '<p class="text-[11px] text-gray-400 mt-2">GPS HR: ' + hrLocation.latitude.toFixed(6) + ', ' + hrLocation.longitude.toFixed(6) + '</p>' : '') +
              '</div>',
        icon:            'question',
        showCancelButton: true,
        confirmButtonColor: '#161758',
        cancelButtonColor:  '#6B7280',
        confirmButtonText:  'Ya, Simpan',
        cancelButtonText:   'Batal',
    }).then((result) => {
        if (result.isConfirmed) {
            doSubmitVerifikasi(karyawanId, nama, tanggal, jamMasuk, jamKeluar, status);
        }
    });
}

function doSubmitVerifikasi(karyawanId, nama, tanggal, jamMasuk, jamKeluar, status) {
    Swal.fire({
        title:           'Menyimpan...',
        html:            'Sedang memperbarui presensi untuk <strong>' + nama + '</strong>',
        allowOutsideClick: false,
        allowEscapeKey:  false,
        showConfirmButton: false,
        didOpen: () => { Swal.showLoading(); }
    });

    fetch('{{ route("hr.absensi.verifikasi.store") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            karyawan_id: karyawanId,
            latitude:    hrLocation ? hrLocation.latitude : null,
            longitude:   hrLocation ? hrLocation.longitude : null,
            tanggal:     tanggal,
            jam_masuk:   jamMasuk || null,
            jam_keluar:  jamKeluar || null,
            status:      status || null,
        })
    })
    .then(r => r.json().then(d => ({ status: r.status, data: d })))
    .then(({ status: httpStatus, data }) => {
        if (data.success) {
            Swal.fire({
                icon:            'success',
                title:           'Berhasil Disimpan!',
                text:            data.message,
                timer:           2200,
                confirmButtonColor: '#161758',
            }).then(() => { window.location.reload(); });
        } else {
            Swal.fire({
                icon:  'error',
                title: 'Gagal!',
                text:  data.message || 'Terjadi kesalahan saat menyimpan.',
                confirmButtonColor: '#ec1d1d'
            });
        }
    })
    .catch((err) => {
        Swal.fire({
            icon:  'error',
            title: 'Error!',
            text:  'Terjadi kesalahan pada server. Pastikan koneksi stabil.',
            confirmButtonColor: '#ec1d1d'
        });
    });
}

// ==========================================================
// SEARCH EMPLOYEE (client-side filter for current page)
// ==========================================================
document.getElementById('searchEmployee').addEventListener('input', function () {
    const query = this.value.toLowerCase();
    document.querySelectorAll('.employee-row').forEach(row => {
        const search = row.getAttribute('data-search');
        row.style.display = search.includes(query) ? '' : 'none';
    });
});

// ==========================================================
// INIT
// ==========================================================
document.addEventListener('DOMContentLoaded', function () {
    getHrLocation();
});
</script>
@endsection
