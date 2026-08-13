{{-- views/karyawan/perizinan.blade.php --}}
@extends('layouts.app')

@section('content')
    <div class="flex min-h-screen bg-gray-50">
        @include('layouts.sidebar')
        <div class="flex-1 transition-all duration-300 md:ml-64">
            <div class="p-3 sm:p-4 lg:p-6">
                <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div>
                        <h1 class="text-xl sm:text-2xl font-bold font-['Montserrat'] text-[#161758]">Perizinan (Izin /
                            Sakit)</h1>
                        <p class="text-sm sm:text-base text-[#27438D]">Riwayat izin &amp; sakit Anda beserta keterangan
                            dan tanggalnya</p>
                    </div>
                    <a href="{{ route('karyawan.absensi') }}"
                        class="inline-flex items-center justify-center gap-2 bg-[#27438D] text-white px-4 py-2 rounded-lg hover:bg-[#161758] transition-colors duration-200 text-sm font-medium">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Kembali ke Absensi
                    </a>
                </div>

                <div class="bg-white rounded-lg shadow-md p-4 sm:p-6">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
                        <h2 class="text-lg font-semibold text-[#161758]">Riwayat Izin &amp; Sakit</h2>
                    </div>

                    <!-- Filter -->
                    <form method="GET" action="{{ route('karyawan.absensi.perizinan') }}"
                        class="grid grid-cols-1 sm:grid-cols-4 gap-3 mb-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Dari Tanggal</label>
                            <input type="date" name="start_date" value="{{ $startDate }}"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Sampai Tanggal</label>
                            <input type="date" name="end_date" value="{{ $endDate }}"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Status</label>
                            <select name="status"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                                <option value="semua" {{ $selectedStatus == 'semua' ? 'selected' : '' }}>Semua Status
                                </option>
                                @foreach (['Izin', 'Sakit'] as $status)
                                    <option value="{{ $status }}" {{ $selectedStatus == $status ? 'selected' : '' }}>
                                        {{ $status }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex items-end gap-2">
                            <button type="submit"
                                class="w-full px-4 py-2 bg-[#00a2e9] text-white rounded-lg hover:bg-[#0088c4] transition-colors text-sm">
                                Filter
                            </button>
                            <a href="{{ route('karyawan.absensi.perizinan') }}"
                                class="w-full text-center px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors text-sm">
                                Reset
                            </a>
                        </div>
                    </form>

                    <!-- Tabel Perizinan -->
                    <div class="overflow-x-auto -mx-4 sm:mx-0">
                        <div class="inline-block min-w-full align-middle">
                            <table class="min-w-full">
                                <thead>
                                    <tr class="bg-[#F5F5F5]">
                                        <th class="px-3 sm:px-4 py-2 text-left text-xs font-semibold text-[#1B1B1B]">
                                            Tanggal</th>
                                        <th
                                            class="px-3 sm:px-4 py-2 text-left text-xs font-semibold text-[#1B1B1B] hidden sm:table-cell">
                                            Hari</th>
                                        <th class="px-3 sm:px-4 py-2 text-left text-xs font-semibold text-[#1B1B1B]">
                                            Status</th>
                                        <th class="px-3 sm:px-4 py-2 text-left text-xs font-semibold text-[#1B1B1B]">
                                            Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($perizinan as $item)
                                        <tr class="border-b border-gray-200 hover:bg-gray-50/50 transition-colors">
                                            <td class="px-3 sm:px-4 py-2 text-xs font-medium">
                                                {{ $item->tanggal->format('d/m/Y') }}
                                            </td>
                                            <td class="px-3 sm:px-4 py-2 text-xs hidden sm:table-cell">
                                                {{ $item->tanggal->locale('id')->isoFormat('dddd') }}
                                            </td>
                                            <td class="px-3 sm:px-4 py-2 text-xs">
                                                @php
                                                    $statusColors = [
                                                        'Izin' => 'bg-[#FCC626] text-[#1B1B1B]',
                                                        'Sakit' => 'bg-[#00a2e9] text-white',
                                                    ];
                                                    $color = $statusColors[$item->status] ?? 'bg-gray-200 text-gray-800';
                                                @endphp
                                                <span
                                                    class="px-2 py-1 rounded-full text-[10px] font-medium {{ $color }}">
                                                    {{ $item->status }}
                                                </span>
                                            </td>
                                            <td class="px-3 sm:px-4 py-2 text-xs text-[#1B1B1B]">
                                                {{ $item->keterangan ?? '-' }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="px-4 py-8 text-center text-gray-500">
                                                Belum ada data izin/sakit.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Pagination -->
                    @if ($perizinan->total() > 0)
                        <div class="mt-3">
                            {{ $perizinan->onEachSide(1)->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
