{{-- views/hr/absensi/index.blade.php --}}
@extends('layouts.app')

@section('content')
    <div class="flex min-h-screen bg-gray-50">
        @include('layouts.sidebar')
        <div class="flex-1 transition-all duration-300 md:ml-64">
            <div class="p-4 sm:p-6 lg:p-8">
                <!-- Header -->
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
                    <div>
                        <h1 class="text-2xl sm:text-3xl font-bold text-[#161758] tracking-tight">Manajemen Absensi</h1>
                        <p class="text-sm text-gray-500 mt-1">Rekap kehadiran seluruh karyawan</p>
                    </div>
                    <a href="{{ route('hr.absensi.export', request()->query()) }}"
                        class="inline-flex items-center justify-center px-5 py-2.5 bg-[#2E7D3E] text-white rounded-xl hover:bg-[#256b34] transition-all duration-200 text-sm font-medium shadow-sm hover:shadow-md gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <span>Export CSV</span>
                    </a>
                </div>

                <!-- Stats Cards -->
                @if (isset($chartData))
                    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-8 gap-3 sm:gap-4 mb-8">
                        @php
                            $cards = [
                                [
                                    'label' => 'Total',
                                    'value' => $chartData['total'],
                                    'color' => 'border-[#161758]',
                                    'text' => 'text-[#161758]',
                                ],
                                [
                                    'label' => 'Hadir',
                                    'value' => $chartData['hadir'],
                                    'color' => 'border-[#2E7D3E]',
                                    'text' => 'text-[#2E7D3E]',
                                ],
                                [
                                    'label' => 'Izin',
                                    'value' => $chartData['izin'],
                                    'color' => 'border-[#FCC626]',
                                    'text' => 'text-[#b58a00]',
                                ],
                                [
                                    'label' => 'Sakit',
                                    'value' => $chartData['sakit'],
                                    'color' => 'border-[#00a2e9]',
                                    'text' => 'text-[#00a2e9]',
                                ],
                                [
                                    'label' => 'Alpha',
                                    'value' => $chartData['alpha'],
                                    'color' => 'border-[#ec1d1d]',
                                    'text' => 'text-[#ec1d1d]',
                                ],
                                [
                                    'label' => 'Dinas',
                                    'value' => $chartData['perjalanan_dinas'],
                                    'color' => 'border-purple-600',
                                    'text' => 'text-purple-600',
                                ],
                                [
                                    'label' => 'Cuti',
                                    'value' => $chartData['cuti'],
                                    'color' => 'border-[#27438D]',
                                    'text' => 'text-[#27438D]',
                                ],
                                [
                                    'label' => 'Lokasi Invalid',
                                    'value' => $chartData['invalid_location'],
                                    'color' => 'border-gray-400',
                                    'text' => 'text-gray-600',
                                ],
                            ];
                        @endphp
                        @foreach ($cards as $card)
                            <div
                                class="bg-white rounded-2xl shadow-sm hover:shadow-md transition-shadow p-4 border-l-4 {{ $card['color'] }}">
                                <p class="text-xs font-medium text-gray-400 uppercase tracking-wider">{{ $card['label'] }}
                                </p>
                                <p class="text-xl font-bold {{ $card['text'] }} mt-1">{{ $card['value'] }}</p>
                            </div>
                        @endforeach
                    </div>
                @endif

                <!-- Filter -->
                <div class="bg-white rounded-2xl shadow-sm p-5 sm:p-6 mb-8">
                    <form action="{{ route('hr.absensi.index') }}" method="GET"
                        class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-4">
                        <div>
                            <label
                                class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Karyawan</label>
                            <select name="karyawan_id"
                                class="w-full px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-[#00a2e9] focus:border-transparent transition">
                                <option value="">Semua Karyawan</option>
                                @foreach ($karyawans as $k)
                                    <option value="{{ $k->id }}"
                                        {{ request('karyawan_id') == $k->id ? 'selected' : '' }}>
                                        {{ $k->nama_lengkap }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label
                                class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Status</label>
                            <select name="status"
                                class="w-full px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-[#00a2e9] focus:border-transparent transition">
                                <option value="semua"
                                    {{ !request('status') || request('status') == 'semua' ? 'selected' : '' }}>Semua Status
                                </option>
                                <option value="Hadir" {{ request('status') == 'Hadir' ? 'selected' : '' }}>Hadir</option>
                                <option value="Izin" {{ request('status') == 'Izin' ? 'selected' : '' }}>Izin</option>
                                <option value="Sakit" {{ request('status') == 'Sakit' ? 'selected' : '' }}>Sakit</option>
                                <option value="Alpha" {{ request('status') == 'Alpha' ? 'selected' : '' }}>Alpha</option>
                                <option value="Perjalanan Dinas"
                                    {{ request('status') == 'Perjalanan Dinas' ? 'selected' : '' }}>Perjalanan Dinas
                                </option>
                                <option value="Cuti" {{ request('status') == 'Cuti' ? 'selected' : '' }}>Cuti
                                </option>
                            </select>
                        </div>
                        <div>
                            <label
                                class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Bulan</label>
                            <select name="month"
                                class="w-full px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-[#00a2e9] focus:border-transparent transition">
                                <option value="">Semua Bulan</option>
                                @php
                                    $bulanList = [
                                        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                                        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                                        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
                                    ];
                                    $activeMonth = request('month', $selectedMonth ?? null);
                                @endphp
                                @foreach ($bulanList as $num => $label)
                                    <option value="{{ $num }}" {{ (int) $activeMonth === $num ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label
                                class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Tahun</label>
                            @php
                                $activeYear = request('year', $selectedYear ?? null);
                                $currentYear = (int) \Carbon\Carbon::now('Asia/Jakarta')->year;
                            @endphp
                            <select name="year"
                                class="w-full px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-[#00a2e9] focus:border-transparent transition">
                                <option value="">Semua Tahun</option>
                                @for ($y = $currentYear; $y >= $currentYear - 4; $y--)
                                    <option value="{{ $y }}" {{ (int) $activeYear === $y ? 'selected' : '' }}>
                                        {{ $y }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Dari
                                Tanggal</label>
                            <input type="date" name="start_date" value="{{ request('start_date') }}"
                                class="w-full px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-[#00a2e9] focus:border-transparent transition">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Sampai
                                Tanggal</label>
                            <input type="date" name="end_date" value="{{ request('end_date') }}"
                                class="w-full px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-[#00a2e9] focus:border-transparent transition">
                        </div>
                        <p class="text-xs text-gray-400 sm:col-span-2 lg:col-span-6 -mt-1">
                            * Kalau "Dari Tanggal" &amp; "Sampai Tanggal" diisi, filter Bulan/Tahun akan diabaikan.
                        </p>
                        <div class="flex items-end gap-2 sm:col-span-2 lg:col-span-2">
                            <button type="submit"
                                class="flex-1 px-4 py-2.5 bg-[#00a2e9] text-white rounded-xl hover:bg-[#0088c4] transition-all duration-200 text-sm font-medium shadow-sm hover:shadow-md">
                                Filter
                            </button>
                            <a href="{{ route('hr.absensi.index') }}"
                                class="flex-1 text-center px-4 py-2.5 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 transition-all duration-200 text-sm font-medium">
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
                                    <th scope="col"
                                        class="px-4 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                        Karyawan</th>
                                    <th scope="col"
                                        class="px-4 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                        Tanggal / Periode</th>
                                    <th scope="col"
                                        class="px-4 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider hidden sm:table-cell">
                                        Check-in / out</th>
                                    <th scope="col"
                                        class="px-4 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider hidden md:table-cell">
                                        Terlambat</th>
                                    <th scope="col"
                                        class="px-4 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                        Status</th>
                                    <th scope="col"
                                        class="px-4 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider hidden md:table-cell">
                                        Lokasi</th>
                                    <th scope="col"
                                        class="px-4 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider hidden lg:table-cell">
                                        Jam Kerja</th>
                                    <th scope="col"
                                        class="px-4 py-3.5 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                        Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100">
                                @forelse($absensis as $item)
                                    <tr
                                        class="hover:bg-gray-50/70 transition-colors {{ $item->is_suspicious ? 'bg-red-50/30' : '' }}">
                                        <td class="px-4 py-3.5">
                                            <div>
                                                <p class="text-sm font-medium text-gray-900">
                                                    {{ $item->karyawan->nama_lengkap ?? '-' }}</p>
                                                <p class="text-xs text-gray-500">{{ $item->karyawan->kode_pegawai ?? '-' }}
                                                </p>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3.5">
                                            @if ($item->is_periode)
                                                @php
                                                    $periodeBadgeClass = $item->status === 'Cuti'
                                                        ? 'bg-blue-100 text-[#27438D]'
                                                        : 'bg-purple-100 text-purple-700';
                                                    $periodeLabel = $item->status === 'Cuti' ? 'Periode Cuti' : 'Periode';
                                                @endphp
                                                <div>
                                                    <p class="text-sm font-medium text-gray-900">
                                                        {{ $item->tanggal_mulai_display->format('d/m/Y') }}
                                                        <span class="text-gray-400">—</span>
                                                        {{ $item->tanggal_selesai_display->format('d/m/Y') }}
                                                    </p>
                                                    <span
                                                        class="inline-flex items-center mt-1 px-2.5 py-0.5 rounded-full text-[10px] font-semibold {{ $periodeBadgeClass }}">
                                                        {{ $periodeLabel }} • {{ $item->jumlah_hari }} hari
                                                    </span>
                                                </div>
                                            @else
                                                <p class="text-sm font-medium text-gray-900">
                                                    {{ $item->tanggal_mulai_display->format('d/m/Y') }}</p>
                                            @endif
                                            <!-- Mobile: tampilkan jam check-in/out -->
                                            <div class="text-xs text-gray-400 sm:hidden mt-1">
                                                {{ $item->check_in ? \Carbon\Carbon::parse($item->check_in)->format('H:i') : '-' }}
                                                /
                                                {{ $item->check_out ? \Carbon\Carbon::parse($item->check_out)->format('H:i') : '-' }}
                                            </div>
                                        </td>
                                        <td class="px-4 py-3.5 hidden sm:table-cell">
                                            <p class="text-sm text-gray-700">
                                                {{ $item->check_in ? \Carbon\Carbon::parse($item->check_in)->format('H:i') : '-' }}
                                                <span class="text-gray-300">/</span>
                                                {{ $item->check_out ? \Carbon\Carbon::parse($item->check_out)->format('H:i') : '-' }}
                                            </p>
                                            @if (!$item->is_periode)
                                                <p class="text-xs text-gray-400">{{ $item->hari }}</p>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3.5 hidden md:table-cell">
                                            @if (!$item->is_periode && $item->is_terlambat)
                                                <span
                                                    class="inline-block px-2 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-700">
                                                    +{{ $item->terlambat_menit }} menit
                                                </span>
                                            @elseif(!$item->is_periode && $item->check_in)
                                                <span class="text-xs font-medium text-[#2E7D3E]">Tepat waktu</span>
                                            @else
                                                <span class="text-xs text-gray-400">—</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3.5">
                                            <div>
                                                @php
                                                    $statusClass = match ($item->status) {
                                                        'Hadir' => 'bg-[#2E7D3E] text-white',
                                                        'Izin' => 'bg-[#FCC626] text-[#1B1B1B]',
                                                        'Sakit' => 'bg-[#00a2e9] text-white',
                                                        'Alpha' => 'bg-[#ec1d1d] text-white',
                                                        'Perjalanan Dinas' => 'bg-purple-600 text-white',
                                                        'Cuti' => 'bg-[#27438D] text-white',
                                                        default => 'bg-gray-200 text-gray-800',
                                                    };
                                                @endphp
                                                <span
                                                    class="inline-block px-2.5 py-1 rounded-full text-xs font-semibold {{ $statusClass }}">
                                                    {{ $item->status }}
                                                </span>
                                                @if ($item->is_suspicious)
                                                    <span class="block mt-1 text-[10px] font-medium text-[#ec1d1d]"
                                                        title="{{ $item->suspicious_reason }}">
                                                        ⚠️ Mencurigakan
                                                    </span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-4 py-3.5 hidden md:table-cell">
                                            @if ($item->status === 'Perjalanan Dinas' || $item->status === 'Cuti')
                                                <span class="text-xs text-gray-400">—</span>
                                            @elseif($item->is_valid_location)
                                                <span
                                                    class="inline-flex items-center gap-1 text-xs font-medium text-[#2E7D3E]">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2" d="M5 13l4 4L19 7" />
                                                    </svg>
                                                    Valid
                                                </span>
                                            @else
                                                <span
                                                    class="inline-flex items-center gap-1 text-xs font-medium text-[#ec1d1d]">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                    </svg>
                                                    Invalid
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3.5 hidden lg:table-cell">
                                            <span class="text-sm font-medium text-gray-700">{{ $item->total_jam_kerja }}
                                                jam</span>
                                        </td>
                                        <td class="px-4 py-3.5 text-right">
                                            <a href="{{ route('hr.absensi.detail', $item->id) }}"
                                                class="inline-flex items-center gap-1.5 text-sm font-medium text-blue-600 hover:text-blue-800 transition-colors">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                                Detail
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="px-4 py-12 text-center">
                                            <div class="flex flex-col items-center justify-center text-gray-400">
                                                <svg class="w-12 h-12 mb-2" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="1.5"
                                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                </svg>
                                                <p class="text-sm font-medium">Belum ada data absensi untuk filter ini.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination (Previous / Next, 10 data per halaman) -->
                    @if ($absensis->total() > 0)
                        <div
                            class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-t border-gray-100 px-4 py-4">
                            <p class="text-xs sm:text-sm text-gray-500 text-center sm:text-left">
                                Menampilkan
                                <span class="font-semibold text-gray-700">{{ $absensis->firstItem() }}</span>
                                –
                                <span class="font-semibold text-gray-700">{{ $absensis->lastItem() }}</span>
                                dari
                                <span class="font-semibold text-gray-700">{{ $absensis->total() }}</span> data
                                &middot; Halaman {{ $absensis->currentPage() }} dari {{ $absensis->lastPage() }}
                            </p>
                            <div class="flex items-center justify-center gap-2">
                                @if ($absensis->onFirstPage())
                                    <span
                                        class="inline-flex items-center gap-1 px-4 py-2 rounded-xl text-sm font-medium text-gray-300 bg-gray-50 cursor-not-allowed select-none">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 19l-7-7 7-7" />
                                        </svg>
                                        Previous
                                    </span>
                                @else
                                    <a href="{{ $absensis->appends(request()->query())->previousPageUrl() }}"
                                        class="inline-flex items-center gap-1 px-4 py-2 rounded-xl text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 19l-7-7 7-7" />
                                        </svg>
                                        Previous
                                    </a>
                                @endif

                                @if ($absensis->hasMorePages())
                                    <a href="{{ $absensis->appends(request()->query())->nextPageUrl() }}"
                                        class="inline-flex items-center gap-1 px-4 py-2 rounded-xl text-sm font-medium text-white bg-[#00a2e9] hover:bg-[#0088c4] transition-colors">
                                        Next
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 5l7 7-7 7" />
                                        </svg>
                                    </a>
                                @else
                                    <span
                                        class="inline-flex items-center gap-1 px-4 py-2 rounded-xl text-sm font-medium text-gray-300 bg-gray-50 cursor-not-allowed select-none">
                                        Next
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 5l7 7-7 7" />
                                        </svg>
                                    </span>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Keterangan -->
                <p class="text-xs text-gray-400 mt-6 leading-relaxed">
                    * Baris berlabel <span class="font-semibold text-purple-600">Periode</span> merupakan gabungan tampilan
                    dari beberapa hari Perjalanan Dinas yang berturut-turut untuk karyawan yang sama. Data harian aslinya
                    tetap tersimpan lengkap di database untuk keperluan rekap/audit.
                </p>
            </div>
        </div>
    </div>
@endsection
