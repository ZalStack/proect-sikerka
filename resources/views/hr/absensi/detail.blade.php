{{-- views/hr/absensi/detail.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="flex min-h-screen">
    @include('layouts.sidebar')
    <div class="flex-1 transition-all duration-300 md:ml-64 pt-6">
        <div class="p-4 sm:p-6">
            <div class="mb-6">
                <h1 class="text-xl sm:text-2xl font-bold font-['Montserrat'] text-[#161758]">Detail Absensi</h1>
                <p class="text-sm sm:text-base text-[#27438D]">Informasi lengkap absensi karyawan</p>
            </div>

            <div class="bg-white rounded-lg shadow-md p-4 sm:p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
                    <div>
                        <h3 class="text-base sm:text-lg font-semibold text-[#161758] border-b border-gray-200 pb-2 mb-4">Informasi Karyawan</h3>
                        <div class="space-y-2">
                            <div>
                                <label class="text-xs sm:text-sm text-[#1B1B1B] font-medium">Nama Lengkap</label>
                                <p class="text-sm sm:text-base text-[#27438D] break-words">{{ $absensi->karyawan->nama_lengkap }}</p>
                            </div>
                            <div>
                                <label class="text-xs sm:text-sm text-[#1B1B1B] font-medium">NIP</label>
                                <p class="text-sm sm:text-base text-[#27438D] break-words">{{ $absensi->karyawan->nip }}</p>
                            </div>
                            <div>
                                <label class="text-xs sm:text-sm text-[#1B1B1B] font-medium">Jabatan</label>
                                <p class="text-sm sm:text-base text-[#27438D] break-words">{{ $absensi->karyawan->jabatan }}</p>
                            </div>
                            <div>
                                <label class="text-xs sm:text-sm text-[#1B1B1B] font-medium">Divisi</label>
                                <p class="text-sm sm:text-base text-[#27438D] break-words">{{ $absensi->karyawan->divisi ?? '-' }}</p>
                            </div>
                        </div>
                    </div>

                    <div>
                        <h3 class="text-base sm:text-lg font-semibold text-[#161758] border-b border-gray-200 pb-2 mb-4">Detail Absensi</h3>
                        <div class="space-y-2">
                            <div>
                                <label class="text-xs sm:text-sm text-[#1B1B1B] font-medium">Tanggal</label>
                                <p class="text-sm sm:text-base text-[#27438D] break-words">{{ $absensi->tanggal->format('d-m-Y') }}</p>
                            </div>
                            <div>
                                <label class="text-xs sm:text-sm text-[#1B1B1B] font-medium">Kantor Cabang</label>
                                <p class="text-sm sm:text-base text-[#27438D] break-words">{{ $absensi->kantor_cabang }}</p>
                            </div>
                            <div>
                                <label class="text-xs sm:text-sm text-[#1B1B1B] font-medium">Check-in</label>
                                <p class="text-sm sm:text-base text-[#27438D] break-words">{{ $absensi->check_in ? Carbon\Carbon::parse($absensi->check_in)->format('H:i') : '-' }}</p>
                            </div>
                            <div>
                                <label class="text-xs sm:text-sm text-[#1B1B1B] font-medium">Check-out</label>
                                <p class="text-sm sm:text-base text-[#27438D] break-words">{{ $absensi->check_out ? Carbon\Carbon::parse($absensi->check_out)->format('H:i') : '-' }}</p>
                            </div>
                            <div>
                                <label class="text-xs sm:text-sm text-[#1B1B1B] font-medium">Total Jam Kerja</label>
                                <p class="text-sm sm:text-base text-[#27438D] break-words">{{ $absensi->total_jam_kerja }} jam</p>
                            </div>
                            <div>
                                <label class="text-xs sm:text-sm text-[#1B1B1B] font-medium">Status</label>
                                <p class="text-sm sm:text-base text-[#27438D]">
                                    <span class="px-2 py-1 rounded-full text-xs font-medium
                                        {{ $absensi->status == 'Hadir' ? 'bg-[#2E7D3E] text-white' :
                                           ($absensi->status == 'Izin' ? 'bg-[#FCC626] text-[#1B1B1B]' :
                                           ($absensi->status == 'Sakit' ? 'bg-[#00a2e9] text-white' : 'bg-[#ec1d1d] text-white')) }}">
                                        {{ $absensi->status }}
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Lokasi Absensi -->
                <div class="mt-6 pt-6 border-t border-gray-200">
                    <h3 class="text-base sm:text-lg font-semibold text-[#161758] mb-4">📍 Lokasi Absensi</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                        <div class="bg-[#F5F5F5] rounded-lg p-3">
                            <p class="text-xs text-[#1B1B1B]">Latitude</p>
                            <p class="text-sm font-semibold text-[#161758]">{{ $absensi->latitude ?? '-' }}</p>
                        </div>
                        <div class="bg-[#F5F5F5] rounded-lg p-3">
                            <p class="text-xs text-[#1B1B1B]">Longitude</p>
                            <p class="text-sm font-semibold text-[#161758]">{{ $absensi->longitude ?? '-' }}</p>
                        </div>
                        <div class="bg-[#F5F5F5] rounded-lg p-3">
                            <p class="text-xs text-[#1B1B1B]">Akurasi</p>
                            <p class="text-sm font-semibold text-[#161758]">{{ $absensi->location_accuracy ? $absensi->location_accuracy . ' meter' : '-' }}</p>
                        </div>
                        <div class="bg-[#F5F5F5] rounded-lg p-3">
                            <p class="text-xs text-[#1B1B1B]">Valid Lokasi</p>
                            <p class="text-sm font-semibold {{ $absensi->is_valid_location ? 'text-[#2E7D3E]' : 'text-[#ec1d1d]' }}">
                                {{ $absensi->is_valid_location ? '✅ Valid' : '❌ Invalid' }}
                            </p>
                        </div>
                    </div>

                    @if(isset($distances) && count($distances) > 0)
                    <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
                        @foreach($distances as $name => $distance)
                        <div class="bg-[#F5F5F5] rounded-lg p-2 text-xs">
                            <span class="font-medium">{{ $name }}:</span>
                            <span class="{{ $distance <= 50 ? 'text-[#2E7D3E] font-bold' : 'text-[#ec1d1d]' }}">
                                {{ number_format($distance, 2) }} meter
                            </span>
                        </div>
                        @endforeach
                    </div>
                    @endif

                    @if($absensi->is_suspicious)
                    <div class="mt-3 bg-[#FCC626]/20 border border-[#FCC626] text-[#1B1B1B] rounded-lg p-3 text-xs sm:text-sm">
                        ⚠️ Data ini ditandai <strong>mencurigakan</strong> oleh sistem{{ $absensi->suspicious_reason ? ': ' . $absensi->suspicious_reason : '' }}. Mohon ditinjau.
                    </div>
                    @endif
                </div>

                <!-- Jejak Audit -->
                <div class="mt-6 pt-6 border-t border-gray-200">
                    <h3 class="text-base sm:text-lg font-semibold text-[#161758] mb-4">🔒 Jejak Audit (Anti-Manipulasi)</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                        <div class="bg-[#F5F5F5] rounded-lg p-3">
                            <p class="text-xs text-[#1B1B1B]">Alamat IP</p>
                            <p class="text-sm font-semibold text-[#161758] break-words">{{ $absensi->ip_address ?? '-' }}</p>
                        </div>
                        <div class="bg-[#F5F5F5] rounded-lg p-3 sm:col-span-2 lg:col-span-2">
                            <p class="text-xs text-[#1B1B1B]">Perangkat / Browser</p>
                            <p class="text-xs font-semibold text-[#161758] break-words">{{ $absensi->user_agent ?? '-' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Form Update Status -->
                <div class="mt-6 pt-6 border-t border-gray-200">
                    <h3 class="text-base sm:text-lg font-semibold text-[#161758] mb-4">Update Status</h3>
                    <form action="{{ route('hr.absensi.update-status', $absensi->id) }}" method="POST" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4">
                        @csrf
                        @method('PUT')
                        <div>
                            <label class="block text-xs sm:text-sm font-medium text-[#1B1B1B] mb-1">Status</label>
                            <select name="status" required
                                    class="w-full px-3 sm:px-4 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                                <option value="Hadir" {{ $absensi->status == 'Hadir' ? 'selected' : '' }}>Hadir</option>
                                <option value="Izin" {{ $absensi->status == 'Izin' ? 'selected' : '' }}>Izin</option>
                                <option value="Sakit" {{ $absensi->status == 'Sakit' ? 'selected' : '' }}>Sakit</option>
                                <option value="Alpha" {{ $absensi->status == 'Alpha' ? 'selected' : '' }}>Alpha</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs sm:text-sm font-medium text-[#1B1B1B] mb-1">Keterangan</label>
                            <input type="text" name="keterangan" value="{{ $absensi->keterangan }}"
                                   class="w-full px-3 sm:px-4 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                        </div>
                        <div class="flex items-end">
                            <button type="submit"
                                    class="w-full sm:w-auto bg-[#27438D] text-white px-4 sm:px-6 py-2 rounded-lg hover:bg-[#161758] transition-colors duration-200 text-sm sm:text-base">
                                Update
                            </button>
                        </div>
                    </form>
                </div>

                <div class="mt-6">
                    <a href="{{ route('hr.absensi.index') }}"
                       class="inline-block w-full sm:w-auto text-center bg-gray-500 text-white px-4 sm:px-6 py-2 rounded-lg hover:bg-gray-600 transition-colors duration-200 text-sm sm:text-base">
                        Kembali
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
