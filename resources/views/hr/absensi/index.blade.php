{{-- views/hr/absensi/index.blade.php --}}
@extends('layouts.app')

@section('content')
    <div class="flex min-h-screen bg-gray-50">
        @include('layouts.sidebar')
        <div class="flex-1 transition-all duration-300 md:ml-64">
            <div class="p-4 sm:p-6 lg:p-8">
                <!-- Header -->
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6 sm:mb-8">
                    <div>
                        <h1 class="text-2xl sm:text-3xl font-bold text-[#161758] tracking-tight">Manajemen Absensi</h1>
                        <p class="text-sm text-gray-500 mt-1">Rekap kehadiran seluruh karyawan</p>
                    </div>
                    <a href="{{ route('hr.absensi.export', request()->query()) }}"
                        class="inline-flex items-center justify-center px-5 py-3 sm:py-2.5 bg-[#2E7D3E] text-white rounded-xl hover:bg-[#256b34] transition-all duration-200 text-sm font-medium shadow-sm hover:shadow-md gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <span>Export CSV</span>
                    </a>
                </div>

                <!-- Stats Cards -->
                @if (isset($chartData))
                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-7 gap-3 sm:gap-4 mb-6 sm:mb-8">
                        @php
                            $cards = [
                                ['label' => 'Total', 'value' => $chartData['total'], 'color' => 'border-[#161758]', 'text' => 'text-[#161758]', 'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
                                ['label' => 'Hadir', 'value' => $chartData['hadir'], 'color' => 'border-[#2E7D3E]', 'text' => 'text-[#2E7D3E]', 'icon' => 'M5 13l4 4L19 7'],
                                ['label' => 'Izin', 'value' => $chartData['izin'], 'color' => 'border-[#FCC626]', 'text' => 'text-[#b58a00]', 'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
                                ['label' => 'Sakit', 'value' => $chartData['sakit'], 'color' => 'border-[#00a2e9]', 'text' => 'text-[#00a2e9]', 'icon' => 'M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z'],
                                ['label' => 'Alpha', 'value' => $chartData['alpha'], 'color' => 'border-[#ec1d1d]', 'text' => 'text-[#ec1d1d]', 'icon' => 'M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636'],
                                ['label' => 'Dinas', 'value' => $chartData['perjalanan_dinas'], 'color' => 'border-purple-600', 'text' => 'text-purple-600', 'icon' => 'M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064'],
                                ['label' => 'Invalid', 'value' => $chartData['invalid_location'], 'color' => 'border-gray-400', 'text' => 'text-gray-600', 'icon' => 'M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z'],
                            ];
                        @endphp
                        @foreach ($cards as $card)
                            <div class="bg-white rounded-2xl shadow-sm hover:shadow-md transition-all duration-300 p-4 border-l-4 {{ $card['color'] }} hover:scale-[1.02] cursor-default">
                                <div class="flex items-center justify-between">
                                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">{{ $card['label'] }}</p>
                                    <svg class="w-4 h-4 {{ $card['text'] }} opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $card['icon'] }}"/>
                                    </svg>
                                </div>
                                <p class="text-2xl sm:text-3xl font-bold {{ $card['text'] }} mt-2">{{ $card['value'] }}</p>
                            </div>
                        @endforeach
                    </div>
                @endif

                <!-- Filter Section -->
                <div class="bg-white rounded-2xl shadow-sm p-4 sm:p-6 mb-6 sm:mb-8">
                    <div class="flex items-center gap-2 mb-4">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                        </svg>
                        <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wider">Filter Data</h3>
                    </div>

                    <form action="{{ route('hr.absensi.index') }}" method="GET" class="space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                            <!-- Karyawan -->
                            <div class="space-y-1.5">
                                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider">Karyawan</label>
                                <select name="karyawan_id"
                                    class="w-full px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-[#00a2e9] focus:border-transparent transition">
                                    <option value="">Semua Karyawan</option>
                                    @foreach ($karyawans as $k)
                                        <option value="{{ $k->id }}" {{ request('karyawan_id') == $k->id ? 'selected' : '' }}>
                                            {{ $k->nama_lengkap }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Status -->
                            <div class="space-y-1.5">
                                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</label>
                                <select name="status"
                                    class="w-full px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-[#00a2e9] focus:border-transparent transition">
                                    <option value="semua" {{ !request('status') || request('status') == 'semua' ? 'selected' : '' }}>Semua Status</option>
                                    <option value="Hadir" {{ request('status') == 'Hadir' ? 'selected' : '' }}>Hadir</option>
                                    <option value="Izin" {{ request('status') == 'Izin' ? 'selected' : '' }}>Izin</option>
                                    <option value="Sakit" {{ request('status') == 'Sakit' ? 'selected' : '' }}>Sakit</option>
                                    <option value="Alpha" {{ request('status') == 'Alpha' ? 'selected' : '' }}>Alpha</option>
                                    <option value="Perjalanan Dinas" {{ request('status') == 'Perjalanan Dinas' ? 'selected' : '' }}>Perjalanan Dinas</option>
                                </select>
                            </div>

                            <!-- Bulan -->
                            <div class="space-y-1.5">
                                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider">Bulan</label>
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

                            <!-- Tahun -->
                            <div class="space-y-1.5">
                                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider">Tahun</label>
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

                            <!-- Dari Tanggal -->
                            <div class="space-y-1.5">
                                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider">Dari Tanggal</label>
                                <input type="date" name="start_date" value="{{ request('start_date') }}"
                                    class="w-full px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-[#00a2e9] focus:border-transparent transition">
                            </div>

                            <!-- Sampai Tanggal -->
                            <div class="space-y-1.5">
                                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider">Sampai Tanggal</label>
                                <input type="date" name="end_date" value="{{ request('end_date') }}"
                                    class="w-full px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-[#00a2e9] focus:border-transparent transition">
                            </div>
                        </div>

                        <p class="text-xs text-gray-400 italic">
                            <svg class="w-3.5 h-3.5 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Jika "Dari Tanggal" & "Sampai Tanggal" diisi, filter Bulan/Tahun akan diabaikan.
                        </p>

                        <div class="flex flex-col sm:flex-row gap-3 pt-2">
                            <button type="submit"
                                class="flex-1 px-6 py-3 sm:py-2.5 bg-[#00a2e9] text-white rounded-xl hover:bg-[#0088c4] transition-all duration-200 text-sm font-medium shadow-sm hover:shadow-md flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                                Terapkan Filter
                            </button>
                            <a href="{{ route('hr.absensi.index') }}"
                                class="flex-1 px-6 py-3 sm:py-2.5 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 transition-all duration-200 text-sm font-medium text-center">
                                Reset Filter
                            </a>
                        </div>
                    </form>
                </div>

                <!-- Mobile Card View -->
                <div class="block lg:hidden space-y-4 mb-8">
                    @forelse($absensis as $item)
                        <div class="bg-white rounded-2xl shadow-sm hover:shadow-md transition-all duration-300 p-4 border border-gray-100 {{ $item->is_suspicious ? 'border-red-200 bg-red-50/30' : '' }}">
                            <!-- Header Card -->
                            <div class="flex items-start justify-between mb-3">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2">
                                        <div class="w-10 h-10 bg-gradient-to-br from-[#161758] to-[#00a2e9] rounded-full flex items-center justify-center text-white font-bold text-sm">
                                            {{ strtoupper(substr($item->karyawan->nama_lengkap ?? 'U', 0, 2)) }}
                                        </div>
                                        <div>
                                            <p class="font-semibold text-gray-900 text-sm">{{ $item->karyawan->nama_lengkap ?? '-' }}</p>
                                            <p class="text-xs text-gray-500">{{ $item->karyawan->kode_pegawai ?? '-' }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    @php
                                        $statusClass = match ($item->status) {
                                            'Hadir' => 'bg-[#2E7D3E] text-white',
                                            'Izin' => 'bg-[#FCC626] text-[#1B1B1B]',
                                            'Sakit' => 'bg-[#00a2e9] text-white',
                                            'Alpha' => 'bg-[#ec1d1d] text-white',
                                            'Perjalanan Dinas' => 'bg-purple-600 text-white',
                                            default => 'bg-gray-200 text-gray-800',
                                        };
                                    @endphp
                                    <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold {{ $statusClass }}">
                                        {{ $item->status }}
                                    </span>
                                </div>
                            </div>

                            <!-- Info Grid -->
                            <div class="grid grid-cols-2 gap-3 text-xs">
                                <div class="space-y-1">
                                    <p class="text-gray-400">Tanggal</p>
                                    <p class="font-medium text-gray-700">
                                        @if ($item->is_periode)
                                            {{ $item->tanggal_mulai_display->format('d/m/Y') }} - {{ $item->tanggal_selesai_display->format('d/m/Y') }}
                                            <span class="block text-purple-600 mt-0.5">📅 {{ $item->jumlah_hari }} hari</span>
                                        @else
                                            {{ $item->tanggal_mulai_display->format('d/m/Y') }}
                                            <span class="block text-gray-400 mt-0.5">{{ $item->hari }}</span>
                                        @endif
                                    </p>
                                </div>

                                <div class="space-y-1">
                                    <p class="text-gray-400">Jam Kerja</p>
                                    <p class="font-medium text-gray-700">
                                        {{ $item->check_in ? \Carbon\Carbon::parse($item->check_in)->format('H:i') : '-' }}
                                        /
                                        {{ $item->check_out ? \Carbon\Carbon::parse($item->check_out)->format('H:i') : '-' }}
                                    </p>
                                </div>

                                <div class="space-y-1">
                                    <p class="text-gray-400">Keterlambatan</p>
                                    <p class="font-medium {{ $item->is_terlambat ? 'text-red-600' : 'text-green-600' }}">
                                        @if (!$item->is_periode && $item->is_terlambat)
                                            ⏰ +{{ $item->terlambat_menit }} menit
                                        @elseif(!$item->is_periode && $item->check_in)
                                            ✅ Tepat waktu
                                        @else
                                            —
                                        @endif
                                    </p>
                                </div>

                                <div class="space-y-1">
                                    <p class="text-gray-400">Lokasi</p>
                                    <p class="font-medium {{ $item->is_valid_location ? 'text-green-600' : 'text-red-600' }}">
                                        @if ($item->status === 'Perjalanan Dinas')
                                            —
                                        @elseif($item->is_valid_location)
                                            ✅ Valid
                                        @else
                                            ❌ Invalid
                                        @endif
                                    </p>
                                </div>
                            </div>

                            @if ($item->is_suspicious)
                                <div class="mt-3 bg-red-50 rounded-lg p-2 border border-red-100">
                                    <p class="text-xs text-red-600 font-medium">⚠️ Mencurigakan: {{ $item->suspicious_reason }}</p>
                                </div>
                            @endif

                            <div class="mt-3 pt-3 border-t border-gray-100">
                                <a href="{{ route('hr.absensi.detail', $item->id) }}"
                                    class="inline-flex items-center justify-center w-full px-4 py-2 bg-blue-50 text-blue-600 rounded-xl hover:bg-blue-100 transition-colors text-sm font-medium gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    Lihat Detail
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-12 bg-white rounded-2xl">
                            <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <p class="text-gray-500 font-medium">Belum ada data absensi</p>
                            <p class="text-gray-400 text-sm mt-1">Silakan ubah filter atau coba kata kunci lain</p>
                        </div>
                    @endforelse
                </div>

                <!-- Desktop Table View -->
                <div class="hidden lg:block bg-white rounded-2xl shadow-sm overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-100">
                            <thead class="bg-gradient-to-r from-gray-50 to-white">
                                <tr>
                                    <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Karyawan</th>
                                    <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Tanggal / Periode</th>
                                    <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Check-in / out</th>
                                    <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Terlambat</th>
                                    <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                                    <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Lokasi</th>
                                    <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Jam Kerja</th>
                                    <th scope="col" class="px-6 py-4 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100">
                                @forelse($absensis as $item)
                                    <tr class="hover:bg-blue-50/30 transition-colors duration-200 {{ $item->is_suspicious ? 'bg-red-50/30' : '' }}">
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-3">
                                                <div class="w-9 h-9 bg-gradient-to-br from-[#161758] to-[#00a2e9] rounded-full flex items-center justify-center text-white font-bold text-xs">
                                                    {{ strtoupper(substr($item->karyawan->nama_lengkap ?? 'U', 0, 2)) }}
                                                </div>
                                                <div>
                                                    <p class="text-sm font-semibold text-gray-900">{{ $item->karyawan->nama_lengkap ?? '-' }}</p>
                                                    <p class="text-xs text-gray-500">{{ $item->karyawan->kode_pegawai ?? '-' }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            @if ($item->is_periode)
                                                <div>
                                                    <p class="text-sm font-medium text-gray-900">
                                                        {{ $item->tanggal_mulai_display->format('d/m/Y') }}
                                                        <span class="text-gray-400 mx-1">→</span>
                                                        {{ $item->tanggal_selesai_display->format('d/m/Y') }}
                                                    </p>
                                                    <span class="inline-flex items-center mt-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-purple-100 text-purple-700">
                                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                                                        </svg>
                                                        Periode • {{ $item->jumlah_hari }} hari
                                                    </span>
                                                </div>
                                            @else
                                                <div>
                                                    <p class="text-sm font-medium text-gray-900">{{ $item->tanggal_mulai_display->format('d/m/Y') }}</p>
                                                    <p class="text-xs text-gray-400 mt-0.5">{{ $item->hari }}</p>
                                                </div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="space-y-1">
                                                <div class="flex items-center gap-1.5">
                                                    <svg class="w-3.5 h-3.5 text-[#2E7D3E]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                                                    </svg>
                                                    <span class="text-sm text-gray-700">{{ $item->check_in ? \Carbon\Carbon::parse($item->check_in)->format('H:i') : '—' }}</span>
                                                </div>
                                                <div class="flex items-center gap-1.5">
                                                    <svg class="w-3.5 h-3.5 text-[#ec1d1d]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                                                    </svg>
                                                    <span class="text-sm text-gray-700">{{ $item->check_out ? \Carbon\Carbon::parse($item->check_out)->format('H:i') : '—' }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            @if (!$item->is_periode && $item->is_terlambat)
                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-700">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                    </svg>
                                                    +{{ $item->terlambat_menit }} menit
                                                </span>
                                            @elseif(!$item->is_periode && $item->check_in)
                                                <span class="inline-flex items-center gap-1 text-xs font-medium text-[#2E7D3E]">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                    </svg>
                                                    Tepat waktu
                                                </span>
                                            @else
                                                <span class="text-xs text-gray-400">—</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4">
                                            @php
                                                $statusClass = match ($item->status) {
                                                    'Hadir' => 'bg-[#2E7D3E] text-white',
                                                    'Izin' => 'bg-[#FCC626] text-[#1B1B1B]',
                                                    'Sakit' => 'bg-[#00a2e9] text-white',
                                                    'Alpha' => 'bg-[#ec1d1d] text-white',
                                                    'Perjalanan Dinas' => 'bg-purple-600 text-white',
                                                    default => 'bg-gray-200 text-gray-800',
                                                };
                                                $statusIcon = match ($item->status) {
                                                    'Hadir' => 'M5 13l4 4L19 7',
                                                    'Izin' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
                                                    'Sakit' => 'M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z',
                                                    'Alpha' => 'M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636',
                                                    'Perjalanan Dinas' => 'M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064',
                                                    default => '',
                                                };
                                            @endphp
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold {{ $statusClass }}">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $statusIcon }}"/>
                                                </svg>
                                                {{ $item->status }}
                                            </span>
                                            @if ($item->is_suspicious)
                                                <div class="mt-1.5 flex items-center gap-1 text-[11px] font-medium text-[#ec1d1d]" title="{{ $item->suspicious_reason }}">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                                                    </svg>
                                                    Mencurigakan
                                                </div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4">
                                            @if ($item->status === 'Perjalanan Dinas')
                                                <span class="text-xs text-gray-400">—</span>
                                            @elseif($item->is_valid_location)
                                                <span class="inline-flex items-center gap-1.5 text-xs font-medium text-[#2E7D3E]">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                    </svg>
                                                    Valid
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1.5 text-xs font-medium text-[#ec1d1d]">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                    </svg>
                                                    Invalid
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="text-sm font-semibold text-gray-700">{{ $item->total_jam_kerja }} jam</span>
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <a href="{{ route('hr.absensi.detail', $item->id) }}"
                                                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-blue-600 bg-blue-50 rounded-xl hover:bg-blue-100 transition-all duration-200">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                </svg>
                                                Detail
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="px-6 py-16 text-center">
                                            <div class="flex flex-col items-center justify-center text-gray-400">
                                                <svg class="w-16 h-16 mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                </svg>
                                                <p class="text-lg font-medium text-gray-500">Belum ada data absensi</p>
                                                <p class="text-sm text-gray-400 mt-1">Silakan ubah filter untuk melihat data</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if ($absensis->total() > 0)
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-t border-gray-100 px-6 py-4 bg-gray-50/50">
                            <div class="flex items-center gap-2 text-sm text-gray-600">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                                </svg>
                                <span>
                                    <span class="font-semibold text-gray-700">{{ $absensis->firstItem() }}</span> -
                                    <span class="font-semibold text-gray-700">{{ $absensis->lastItem() }}</span>
                                    dari
                                    <span class="font-semibold text-gray-700">{{ $absensis->total() }}</span> data
                                </span>
                            </div>

                            <div class="flex items-center gap-2">
                                @if ($absensis->onFirstPage())
                                    <span class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-sm font-medium text-gray-400 bg-gray-100 cursor-not-allowed">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                                        </svg>
                                        Sebelumnya
                                    </span>
                                @else
                                    <a href="{{ $absensis->appends(request()->query())->previousPageUrl() }}"
                                        class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-sm font-medium text-gray-700 bg-white border border-gray-200 hover:bg-gray-100 transition-all duration-200 shadow-sm">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                                        </svg>
                                        Sebelumnya
                                    </a>
                                @endif

                                <span class="px-3 py-2 text-sm font-medium text-gray-500">
                                    Halaman {{ $absensis->currentPage() }} dari {{ $absensis->lastPage() }}
                                </span>

                                @if ($absensis->hasMorePages())
                                    <a href="{{ $absensis->appends(request()->query())->nextPageUrl() }}"
                                        class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-sm font-medium text-white bg-[#00a2e9] hover:bg-[#0088c4] transition-all duration-200 shadow-sm">
                                        Selanjutnya
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                        </svg>
                                    </a>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-sm font-medium text-gray-400 bg-gray-100 cursor-not-allowed">
                                        Selanjutnya
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                        </svg>
                                    </span>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Footer Note -->
                <div class="mt-6 p-4 bg-blue-50 rounded-xl border border-blue-100">
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-blue-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div class="text-sm text-blue-700">
                            <p class="font-medium mb-1">Keterangan:</p>
                            <p class="text-blue-600/80">
                                * Baris berlabel <span class="font-semibold text-purple-600">Periode</span> merupakan gabungan tampilan dari beberapa hari Perjalanan Dinas yang berturut-turut untuk karyawan yang sama.
                                Data harian aslinya tetap tersimpan lengkap di database untuk keperluan rekap/audit.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
