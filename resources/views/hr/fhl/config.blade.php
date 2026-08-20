{{-- resources/views/hr/fhl/config.blade.php --}}
@extends('layouts.app')

@section('content')
    <div class="flex min-h">
        @include('layouts.sidebar')
        <div class="flex-1 transition-all duration-300 md:ml-64 pt-6">
            <div class="p-3 sm:p-6">
                <div class="mb-6">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div>
                            <h1 class="text-xl sm:text-2xl font-bold font-['Montserrat'] text-[#161758]">⚙️ Pengaturan Jadwal FHL</h1>
                            <p class="text-sm sm:text-base text-[#27438D]">Atur hari dan jam terakhir absensi FHL</p>
                        </div>
                        <a href="{{ route('hr.fhl.index') }}"
                           class="inline-flex items-center px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors duration-200 text-sm sm:text-base">
                            ← Kembali
                        </a>
                    </div>
                </div>

                @if (session('success'))
                    <div class="bg-[#2E7D3E] text-white p-3 rounded-lg mb-4 text-sm">
                        {{ session('success') }}
                    </div>
                @endif
                @if (session('error'))
                    <div class="bg-[#ec1d1d] text-white p-3 rounded-lg mb-4 text-sm">
                        {{ session('error') }}
                    </div>
                @endif

                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 hover:shadow-lg transition-all duration-300 p-4 sm:p-6">
                    <form action="{{ route('hr.fhl.config.update') }}" method="POST" class="space-y-6">
                        @csrf

                        <div>
                            <label class="block text-sm font-medium text-[#1B1B1B] mb-2">
                                Hari Aktif FHL
                            </label>
                            <select name="active_day"
                                class="w-full max-w-xs px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9] text-sm">
                                @foreach ($days as $value => $label)
                                    <option value="{{ $value }}" {{ $config['active_day'] == $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            <p class="text-xs text-gray-500 mt-1">Pilih hari pelaksanaan kegiatan FHL.</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-[#1B1B1B] mb-2">
                                Batas Akhir Absensi
                            </label>
                            <div class="flex flex-wrap items-center gap-3">
                                <div>
                                    <label class="text-xs text-gray-500">Jam</label>
                                    <select name="end_hour"
                                        class="w-20 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9] text-sm">
                                        @for ($h = 0; $h <= 23; $h++)
                                            <option value="{{ $h }}" {{ $config['end_hour'] == $h ? 'selected' : '' }}>
                                                {{ sprintf('%02d', $h) }}
                                            </option>
                                        @endfor
                                    </select>
                                </div>
                                <div>
                                    <label class="text-xs text-gray-500">Menit</label>
                                    <select name="end_minute"
                                        class="w-20 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9] text-sm">
                                        @for ($m = 0; $m <= 59; $m += 5)
                                            <option value="{{ $m }}" {{ $config['end_minute'] == $m ? 'selected' : '' }}>
                                                {{ sprintf('%02d', $m) }}
                                            </option>
                                        @endfor
                                    </select>
                                </div>
                                <span class="text-sm text-[#1B1B1B]">WIB</span>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">Karyawan tidak dapat absen setelah jam ini.</p>
                        </div>

                        <div class="pt-4 border-t border-gray-200">
                            <button type="submit"
                                class="bg-[#27438D] text-white px-6 py-2 rounded-lg hover:bg-[#161758] transition-colors duration-200 text-sm sm:text-base">
                                💾 Simpan Pengaturan
                            </button>
                        </div>
                    </form>

                    <!-- Preview Konfigurasi -->
                    <div class="mt-6 p-4 bg-[#F5F5F5] rounded-lg">
                        <h3 class="text-sm font-semibold text-[#161758] mb-2">📋 Preview Konfigurasi</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-sm">
                            <div>
                                <span class="text-[#1B1B1B] font-medium">Hari Aktif:</span>
                                <span class="text-[#27438D] ml-2">{{ $days[$config['active_day']] }}</span>
                            </div>
                            <div>
                                <span class="text-[#1B1B1B] font-medium">Batas Akhir:</span>
                                <span class="text-[#27438D] ml-2">{{ sprintf('%02d:%02d', $config['end_hour'], $config['end_minute']) }} WIB</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection