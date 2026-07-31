{{-- views/hr/absensi/index.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="flex min-h-screen">
    @include('layouts.sidebar')
    <div class="flex-1 transition-all duration-300 md:ml-64">
        <div class="p-3 sm:p-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
                <div>
                    <h1 class="text-xl sm:text-2xl font-bold font-['Montserrat'] text-[#161758]">Manajemen Absensi</h1>
                    <p class="text-sm text-[#27438D] mt-1">Rekap kehadiran seluruh karyawan</p>
                </div>
                <a href="{{ route('hr.absensi.export', request()->query()) }}"
                   class="w-full sm:w-auto text-center px-4 py-2 bg-[#2E7D3E] text-white rounded-lg hover:bg-[#256b34] transition text-sm flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span>Export CSV</span>
                </a>
            </div>

            <!-- Stats Cards -->
            @if(isset($chartData))
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-2 sm:gap-3 mb-6">
                <div class="bg-white rounded-xl shadow-sm p-3 border-l-4 border-[#161758]">
                    <p class="text-[10px] sm:text-xs text-gray-500">Total</p>
                    <p class="text-lg sm:text-xl font-bold text-[#161758]">{{ $chartData['total'] }}</p>
                </div>
                <div class="bg-white rounded-xl shadow-sm p-3 border-l-4 border-[#2E7D3E]">
                    <p class="text-[10px] sm:text-xs text-gray-500">Hadir</p>
                    <p class="text-lg sm:text-xl font-bold text-[#2E7D3E]">{{ $chartData['hadir'] }}</p>
                </div>
                <div class="bg-white rounded-xl shadow-sm p-3 border-l-4 border-[#FCC626]">
                    <p class="text-[10px] sm:text-xs text-gray-500">Izin</p>
                    <p class="text-lg sm:text-xl font-bold text-[#b58a00]">{{ $chartData['izin'] }}</p>
                </div>
                <div class="bg-white rounded-xl shadow-sm p-3 border-l-4 border-[#00a2e9]">
                    <p class="text-[10px] sm:text-xs text-gray-500">Sakit</p>
                    <p class="text-lg sm:text-xl font-bold text-[#00a2e9]">{{ $chartData['sakit'] }}</p>
                </div>
                <div class="bg-white rounded-xl shadow-sm p-3 border-l-4 border-[#ec1d1d]">
                    <p class="text-[10px] sm:text-xs text-gray-500">Alpha</p>
                    <p class="text-lg sm:text-xl font-bold text-[#ec1d1d]">{{ $chartData['alpha'] }}</p>
                </div>
                <div class="bg-white rounded-xl shadow-sm p-3 border-l-4 border-purple-600">
                    <p class="text-[10px] sm:text-xs text-gray-500">Dinas</p>
                    <p class="text-lg sm:text-xl font-bold text-purple-600">{{ $chartData['perjalanan_dinas'] }}</p>
                </div>
                <div class="bg-white rounded-xl shadow-sm p-3 border-l-4 border-gray-400 col-span-2 sm:col-span-1">
                    <p class="text-[10px] sm:text-xs text-gray-500">Lokasi Invalid</p>
                    <p class="text-lg sm:text-xl font-bold text-gray-600">{{ $chartData['invalid_location'] }}</p>
                </div>
            </div>
            @endif

            <!-- Filter -->
            <div class="bg-white rounded-xl shadow-sm p-4 mb-6">
                <form action="{{ route('hr.absensi.index') }}" method="GET"
                      class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Karyawan</label>
                        <select name="karyawan_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-[#00a2e9] focus:border-transparent">
                            <option value="">Semua Karyawan</option>
                            @foreach($karyawans as $k)
                                <option value="{{ $k->id }}" {{ request('karyawan_id') == $k->id ? 'selected' : '' }}>
                                    {{ $k->nama_lengkap }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Status</label>
                        <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-[#00a2e9] focus:border-transparent bg-white">
                            <option value="semua" {{ !request('status') || request('status') == 'semua' ? 'selected' : '' }}>Semua Status</option>
                            <option value="Hadir" {{ request('status') == 'Hadir' ? 'selected' : '' }}>Hadir</option>
                            <option value="Izin" {{ request('status') == 'Izin' ? 'selected' : '' }}>Izin</option>
                            <option value="Sakit" {{ request('status') == 'Sakit' ? 'selected' : '' }}>Sakit</option>
                            <option value="Alpha" {{ request('status') == 'Alpha' ? 'selected' : '' }}>Alpha</option>
                            <option value="Perjalanan Dinas" {{ request('status') == 'Perjalanan Dinas' ? 'selected' : '' }}>Perjalanan Dinas</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Dari Tanggal</label>
                        <input type="date" name="start_date" value="{{ request('start_date') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-[#00a2e9] focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Sampai Tanggal</label>
                        <input type="date" name="end_date" value="{{ request('end_date') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-[#00a2e9] focus:border-transparent">
                    </div>
                    <div class="flex items-end gap-2 sm:col-span-2 lg:col-span-2">
                        <button type="submit" class="flex-1 px-4 py-2 bg-[#00a2e9] text-white rounded-lg hover:bg-[#0088c4] transition text-sm">
                            Filter
                        </button>
                        <a href="{{ route('hr.absensi.index') }}" class="flex-1 text-center px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition text-sm">
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
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal / Periode</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden sm:table-cell">Check-in / out</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden md:table-cell">Lokasi</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden lg:table-cell">Jam Kerja</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($absensis as $item)
                            <tr class="hover:bg-gray-50 transition {{ $item->is_suspicious ? 'bg-red-50/50' : '' }}">
                                <td class="px-4 py-3">
                                    <p class="text-sm font-medium text-gray-900">{{ $item->karyawan->nama_lengkap ?? '-' }}</p>
                                    <p class="text-xs text-gray-500">{{ $item->karyawan->kode_pegawai ?? '-' }}</p>
                                </td>
                                <td class="px-4 py-3">
                                    @if($item->is_periode)
                                        <p class="text-sm text-gray-900 font-medium">
                                            {{ $item->tanggal_mulai_display->format('d/m/Y') }}
                                            <span class="text-gray-400">s/d</span>
                                            {{ $item->tanggal_selesai_display->format('d/m/Y') }}
                                        </p>
                                        <span class="inline-block mt-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-purple-100 text-purple-700">
                                            Periode • {{ $item->jumlah_hari }} hari
                                        </span>
                                    @else
                                        <p class="text-sm text-gray-900">{{ $item->tanggal_mulai_display->format('d/m/Y') }}</p>
                                    @endif
                                    <p class="text-xs text-gray-400 sm:hidden mt-1">
                                        {{ $item->check_in ? \Carbon\Carbon::parse($item->check_in)->format('H:i') : '-' }}
                                        /
                                        {{ $item->check_out ? \Carbon\Carbon::parse($item->check_out)->format('H:i') : '-' }}
                                    </p>
                                </td>
                                <td class="px-4 py-3 hidden sm:table-cell">
                                    <p class="text-sm text-gray-900">
                                        {{ $item->check_in ? \Carbon\Carbon::parse($item->check_in)->format('H:i') : '-' }}
                                        <span class="text-gray-400">/</span>
                                        {{ $item->check_out ? \Carbon\Carbon::parse($item->check_out)->format('H:i') : '-' }}
                                    </p>
                                </td>
                                <td class="px-4 py-3">
                                    @php
                                        $statusClass = match($item->status) {
                                            'Hadir' => 'bg-[#2E7D3E] text-white',
                                            'Izin' => 'bg-[#FCC626] text-[#1B1B1B]',
                                            'Sakit' => 'bg-[#00a2e9] text-white',
                                            'Alpha' => 'bg-[#ec1d1d] text-white',
                                            'Perjalanan Dinas' => 'bg-purple-600 text-white',
                                            default => 'bg-gray-200 text-gray-800',
                                        };
                                    @endphp
                                    <span class="px-2 py-1 rounded-full text-[11px] font-medium {{ $statusClass }}">
                                        {{ $item->status }}
                                    </span>
                                    @if($item->is_suspicious)
                                        <span class="block mt-1 text-[10px] font-semibold text-[#ec1d1d]" title="{{ $item->suspicious_reason }}">
                                            ⚠️ Mencurigakan
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 hidden md:table-cell">
                                    @if($item->status === 'Perjalanan Dinas')
                                        <span class="text-xs text-gray-400">-</span>
                                    @elseif($item->is_valid_location)
                                        <span class="text-xs text-[#2E7D3E] font-medium">✅ Valid</span>
                                    @else
                                        <span class="text-xs text-[#ec1d1d] font-medium">❌ Invalid</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 hidden lg:table-cell">
                                    <span class="text-sm text-gray-900">{{ $item->total_jam_kerja }} jam</span>
                                </td>
                                <td class="px-4 py-3">
                                    <a href="{{ route('hr.absensi.detail', $item->id) }}"
                                       class="text-blue-600 hover:text-blue-800 text-sm whitespace-nowrap">
                                        Detail
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                                    <p>Belum ada data absensi untuk filter ini.</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                    </div>
                </div>

                <!-- Pagination -->
                <div class="px-4 py-3 bg-gray-50 border-t border-gray-200">
                    {{ $absensis->links() }}
                </div>
            </div>

            <p class="text-xs text-gray-400 mt-4">
                * Baris berlabel <span class="font-semibold text-purple-600">Periode</span> merupakan gabungan tampilan dari beberapa hari Perjalanan Dinas yang berturut-turut untuk karyawan yang sama. Data harian aslinya tetap tersimpan lengkap di database untuk keperluan rekap/audit.
            </p>
        </div>
    </div>
</div>
@endsection
