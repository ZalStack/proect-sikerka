@extends('layouts.app')

@section('content')
<div class="flex">
    @include('layouts.sidebar')
    <div class="ml-64 flex-1 p-6">
        <div class="mb-6">
            <h1 class="text-2xl font-bold font-['Montserrat'] text-[#161758]">Absensi</h1>
            <p class="text-[#27438D]">Check-in dan Check-out hari ini</p>
        </div>

        @if(session('success'))
            <div class="bg-[#2E7D3E] text-white p-4 rounded-lg mb-4">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-[#ec1d1d] text-white p-4 rounded-lg mb-4">
                {{ session('error') }}
            </div>
        @endif

        <!-- Status Absensi Hari Ini -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-lg font-semibold text-[#161758] mb-4">Status Absensi Hari Ini</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-[#F5F5F5] rounded-lg p-4">
                    <p class="text-sm text-[#1B1B1B]">Status</p>
                    <p class="text-xl font-bold text-[#161758]">
                        @if($todayAbsensi && $todayAbsensi->check_in && $todayAbsensi->check_out)
                            Selesai
                        @elseif($todayAbsensi && $todayAbsensi->check_in)
                            Sedang Bekerja
                        @else
                            Belum Absen
                        @endif
                    </p>
                </div>
                <div class="bg-[#F5F5F5] rounded-lg p-4">
                    <p class="text-sm text-[#1B1B1B]">Check-in</p>
                    <p class="text-xl font-bold text-[#161758]">
                        {{ $todayAbsensi && $todayAbsensi->check_in ? Carbon\Carbon::parse($todayAbsensi->check_in)->format('H:i') : '-' }}
                        @if($todayAbsensi && $todayAbsensi->is_manual_checkin)
                            <span class="text-xs text-[#00a2e9]">(Manual)</span>
                        @endif
                    </p>
                </div>
                <div class="bg-[#F5F5F5] rounded-lg p-4">
                    <p class="text-sm text-[#1B1B1B]">Check-out</p>
                    <p class="text-xl font-bold text-[#161758]">
                        {{ $todayAbsensi && $todayAbsensi->check_out ? Carbon\Carbon::parse($todayAbsensi->check_out)->format('H:i') : '-' }}
                        @if($todayAbsensi && $todayAbsensi->is_manual_checkout)
                            <span class="text-xs text-[#00a2e9]">(Manual)</span>
                        @endif
                    </p>
                </div>
            </div>
        </div>

        <!-- Form Check-in -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-lg font-semibold text-[#161758] mb-4">Check-in</h2>

            @if(!$todayAbsensi || !$todayAbsensi->check_in)
                <form action="{{ route('karyawan.absensi.checkin') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Pilih Kantor Cabang</label>
                        <select name="kantor_cabang" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                            <option value="">Pilih Kantor Cabang</option>
                            @foreach($kantorCabang as $cabang)
                                <option value="{{ $cabang }}">{{ $cabang }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Jam Check-in (Manual)</label>
                        <input type="time" name="check_in_manual" step="60"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                        <p class="text-xs text-gray-500 mt-1">Kosongkan untuk menggunakan waktu sekarang</p>
                    </div>
                    <button type="submit"
                            class="bg-[#2E7D3E] text-white px-6 py-2 rounded-lg hover:bg-[#009a4b] transition-colors duration-200">
                        Check-in
                    </button>
                </form>
            @else
                <div class="bg-[#F5F5F5] p-4 rounded-lg">
                    <p class="text-[#1B1B1B]">✅ Anda sudah check-in pada {{ Carbon\Carbon::parse($todayAbsensi->check_in)->format('H:i') }}</p>
                    <p class="text-sm text-[#ec1d1d] mt-1">⚠️ Data check-in tidak dapat diubah atau dihapus!</p>
                </div>
            @endif
        </div>

        <!-- Form Check-out -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-lg font-semibold text-[#161758] mb-4">Check-out</h2>

            @if($todayAbsensi && $todayAbsensi->check_in && !$todayAbsensi->check_out)
                <form action="{{ route('karyawan.absensi.checkout') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Jam Check-out (Manual)</label>
                        <input type="time" name="check_out_manual" step="60"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                        <p class="text-xs text-gray-500 mt-1">Kosongkan untuk menggunakan waktu sekarang</p>
                    </div>
                    <button type="submit"
                            class="bg-[#ec1d1d] text-white px-6 py-2 rounded-lg hover:bg-red-700 transition-colors duration-200">
                        Check-out
                    </button>
                </form>
            @elseif($todayAbsensi && $todayAbsensi->check_out)
                <div class="bg-[#F5F5F5] p-4 rounded-lg">
                    <p class="text-[#1B1B1B]">✅ Anda sudah check-out pada {{ Carbon\Carbon::parse($todayAbsensi->check_out)->format('H:i') }}</p>
                    <p class="text-sm text-[#ec1d1d] mt-1">⚠️ Data check-out tidak dapat diubah atau dihapus!</p>
                </div>
            @else
                <div class="bg-[#F5F5F5] p-4 rounded-lg">
                    <p class="text-[#1B1B1B]">Silahkan check-in terlebih dahulu</p>
                </div>
            @endif
        </div>

        <!-- Statistik Bulan Ini -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-lg font-semibold text-[#161758] mb-4">Statistik Bulan Ini</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-[#F5F5F5] rounded-lg p-4 text-center">
                    <p class="text-2xl font-bold text-[#2E7D3E]">{{ $statistik->total_hadir ?? 0 }}</p>
                    <p class="text-sm text-[#1B1B1B]">Total Hadir</p>
                </div>
                <div class="bg-[#F5F5F5] rounded-lg p-4 text-center">
                    <p class="text-2xl font-bold text-[#FCC626]">{{ $statistik->total_telat ?? 0 }}</p>
                    <p class="text-sm text-[#1B1B1B]">Total Telat</p>
                </div>
                <div class="bg-[#F5F5F5] rounded-lg p-4 text-center">
                    <p class="text-2xl font-bold text-[#00a2e9]">{{ $statistik->total_lembur ?? 0 }}</p>
                    <p class="text-sm text-[#1B1B1B]">Total Lembur</p>
                </div>
                <div class="bg-[#F5F5F5] rounded-lg p-4 text-center">
                    <p class="text-2xl font-bold text-[#161758]">{{ $statistik->total_jam_kerja ?? 0 }}</p>
                    <p class="text-sm text-[#1B1B1B]">Total Jam Kerja</p>
                </div>
            </div>
        </div>

        <!-- Grafik 7 Hari Terakhir -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-lg font-semibold text-[#161758] mb-4">Aktivitas 7 Hari Terakhir</h2>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-[#F5F5F5]">
                            <th class="px-4 py-2 text-left text-sm font-semibold text-[#1B1B1B]">Tanggal</th>
                            <th class="px-4 py-2 text-left text-sm font-semibold text-[#1B1B1B]">Check-in</th>
                            <th class="px-4 py-2 text-left text-sm font-semibold text-[#1B1B1B]">Check-out</th>
                            <th class="px-4 py-2 text-left text-sm font-semibold text-[#1B1B1B]">Status</th>
                            <th class="px-4 py-2 text-left text-sm font-semibold text-[#1B1B1B]">Total Jam</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($last7Days as $day)
                        <tr class="border-b border-gray-200">
                            <td class="px-4 py-2 text-sm">{{ $day['tanggal'] }}</td>
                            <td class="px-4 py-2 text-sm">{{ $day['check_in'] }}</td>
                            <td class="px-4 py-2 text-sm">{{ $day['check_out'] }}</td>
                            <td class="px-4 py-2 text-sm">
                                @if($day['status'] == 'Hadir')
                                    <span class="px-2 py-1 rounded-full text-xs font-medium bg-[#2E7D3E] text-white">Hadir</span>
                                @elseif($day['status'] == 'Izin')
                                    <span class="px-2 py-1 rounded-full text-xs font-medium bg-[#FCC626] text-[#1B1B1B]">Izin</span>
                                @elseif($day['status'] == 'Sakit')
                                    <span class="px-2 py-1 rounded-full text-xs font-medium bg-[#00a2e9] text-white">Sakit</span>
                                @else
                                    <span class="px-2 py-1 rounded-full text-xs font-medium bg-[#ec1d1d] text-white">Alpha</span>
                                @endif
                            </td>
                            <td class="px-4 py-2 text-sm">{{ $day['total_jam'] }} jam</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
