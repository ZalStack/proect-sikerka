{{-- views/hr/absensi/detail.blade.php --}}
@extends('layouts.app')

@section('content')
    <div class="flex min-h-screen">
        @include('layouts.sidebar')
        <div class="flex-1 transition-all duration-300 md:ml-64 pt-6 w-full">
            <div class="p-3 sm:p-6">
                <div class="mb-6">
                    <h1 class="text-xl sm:text-2xl font-bold font-['Montserrat'] text-[#161758]">Detail Absensi</h1>
                    <p class="text-sm sm:text-base text-[#27438D]">Informasi lengkap absensi karyawan</p>
                </div>

                <!-- Di dalam div header, tambahkan navigasi -->
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
                    <div class="flex items-center gap-2">
                        <a href="{{ route('hr.absensi.index') }}"
                            class="text-[#00a2e9] hover:text-[#0088c4] text-sm flex items-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                            <span>Kembali</span>
                        </a>
                        @if (isset($prevNext))
                            <span class="text-gray-300 mx-1">|</span>
                            @if ($prevNext['prev'])
                                <a href="{{ route('hr.absensi.detail', $prevNext['prev']) }}"
                                    class="text-[#00a2e9] hover:text-[#0088c4] text-sm flex items-center">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 19l-7-7 7-7" />
                                    </svg>
                                    <span>Prev</span>
                                </a>
                            @endif
                            @if ($prevNext['prev'] && $prevNext['next'])
                                <span class="text-gray-300 mx-1">|</span>
                            @endif
                            @if ($prevNext['next'])
                                <a href="{{ route('hr.absensi.detail', $prevNext['next']) }}"
                                    class="text-[#00a2e9] hover:text-[#0088c4] text-sm flex items-center">
                                    <span>Next</span>
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 5l7 7-7 7" />
                                    </svg>
                                </a>
                            @endif
                        @endif
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-md p-3 sm:p-6">
                    @if ($absensi->status === 'Perjalanan Dinas' && isset($perjalananDinas) && $perjalananDinas)
                        <div class="mb-6 bg-purple-50 border border-purple-300 rounded-lg p-3 sm:p-4">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="text-xs sm:text-sm font-semibold text-purple-800">✈️ Bagian dari Perjalanan
                                        Dinas</p>
                                    <p class="text-sm sm:text-base text-purple-900 font-medium break-words mt-1">
                                        {{ $perjalananDinas->judul }}</p>
                                    <p class="text-xs sm:text-sm text-purple-700 mt-1">
                                        Periode: {{ $perjalananDinas->tanggal_mulai->format('d/m/Y') }}
                                        s/d {{ $perjalananDinas->tanggal_selesai->format('d/m/Y') }}
                                        ({{ $perjalananDinas->tanggal_mulai->diffInDays($perjalananDinas->tanggal_selesai) + 1 }}
                                        hari)
                                    </p>
                                </div>
                                <a href="{{ route('hr.perjalanan-dinas.show', $perjalananDinas->id) }}"
                                    class="w-full sm:w-auto text-center flex-shrink-0 bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition-colors duration-200 text-xs sm:text-sm">
                                    Lihat Detail Pengajuan
                                </a>
                            </div>
                        </div>
                    @endif

                    @if ($absensi->status === 'Cuti' && isset($cutiInfo) && $cutiInfo)
                        <div class="mb-6 bg-blue-50 border border-[#27438D]/30 rounded-lg p-3 sm:p-4">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="text-xs sm:text-sm font-semibold text-[#161758]">🗓️ Bagian dari Pengajuan
                                        Cuti</p>
                                    <p class="text-sm sm:text-base text-[#27438D] font-medium break-words mt-1">
                                        {{ $cutiInfo->jenis_cuti }}
                                        <span
                                            class="ml-2 px-2 py-0.5 rounded-full text-[10px] font-medium {{ $cutiInfo->status_badge }}">
                                            {{ $cutiInfo->status_label }}
                                        </span>
                                    </p>
                                    <p class="text-xs sm:text-sm text-[#27438D] mt-1">
                                        Periode: {{ $cutiInfo->tanggal_mulai->format('d/m/Y') }}
                                        s/d {{ $cutiInfo->tanggal_selesai->format('d/m/Y') }}
                                        ({{ $cutiInfo->durasi }} hari)
                                    </p>
                                    @if ($cutiInfo->keterangan)
                                        <p class="text-xs sm:text-sm text-[#27438D] mt-1 break-words">
                                            Keterangan: {{ $cutiInfo->keterangan }}</p>
                                    @endif
                                </div>
                                <a href="{{ route('hr.cuti.show', $cutiInfo->id) }}"
                                    class="w-full sm:w-auto text-center flex-shrink-0 bg-[#27438D] text-white px-4 py-2 rounded-lg hover:bg-[#161758] transition-colors duration-200 text-xs sm:text-sm">
                                    Lihat Detail Pengajuan
                                </a>
                            </div>
                        </div>
                    @endif

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
                        <div>
                            <h3
                                class="text-base sm:text-lg font-semibold text-[#161758] border-b border-gray-200 pb-2 mb-4">
                                Informasi Karyawan</h3>
                            <div class="space-y-2">
                                <div>
                                    <label class="text-xs sm:text-sm text-[#1B1B1B] font-medium">Nama Lengkap</label>
                                    <p class="text-sm sm:text-base text-[#27438D] break-words">
                                        {{ $absensi->karyawan->nama_lengkap }}</p>
                                </div>
                                <div>
                                    <label class="text-xs sm:text-sm text-[#1B1B1B] font-medium">NIP</label>
                                    <p class="text-sm sm:text-base text-[#27438D] break-words">{{ $absensi->karyawan->nip }}
                                    </p>
                                </div>
                                <div>
                                    <label class="text-xs sm:text-sm text-[#1B1B1B] font-medium">Jabatan</label>
                                    <p class="text-sm sm:text-base text-[#27438D] break-words">
                                        {{ $absensi->karyawan->jabatan }}</p>
                                </div>
                                <div>
                                    <label class="text-xs sm:text-sm text-[#1B1B1B] font-medium">Divisi</label>
                                    <p class="text-sm sm:text-base text-[#27438D] break-words">
                                        {{ $absensi->karyawan->divisi ?? '-' }}</p>
                                </div>
                            </div>
                        </div>

                        <div>
                            <h3
                                class="text-base sm:text-lg font-semibold text-[#161758] border-b border-gray-200 pb-2 mb-4">
                                Detail Absensi</h3>
                            <div class="space-y-2">
                                <div>
                                    <label class="text-xs sm:text-sm text-[#1B1B1B] font-medium">Tanggal</label>
                                    <p class="text-sm sm:text-base text-[#27438D] break-words">
                                        {{ $absensi->tanggal->format('d-m-Y') }}</p>
                                </div>
                                <div>
                                    <label class="text-xs sm:text-sm text-[#1B1B1B] font-medium">Kantor Cabang</label>
                                    <p class="text-sm sm:text-base text-[#27438D] break-words">
                                        {{ $absensi->kantor_cabang }}</p>
                                </div>
                                <div>
                                    <label class="text-xs sm:text-sm text-[#1B1B1B] font-medium">Check-in</label>
                                    <p class="text-sm sm:text-base text-[#27438D] break-words">
                                        {{ $absensi->check_in ? Carbon\Carbon::parse($absensi->check_in)->format('H:i') : '-' }}
                                    </p>
                                </div>
                                <div>
                                    <label class="text-xs sm:text-sm text-[#1B1B1B] font-medium">Check-out</label>
                                    <p class="text-sm sm:text-base text-[#27438D] break-words">
                                        {{ $absensi->check_out ? Carbon\Carbon::parse($absensi->check_out)->format('H:i') : '-' }}
                                    </p>
                                </div>
                                <div>
                                    <label class="text-xs sm:text-sm text-[#1B1B1B] font-medium">Total Jam Kerja</label>
                                    <p class="text-sm sm:text-base text-[#27438D] break-words">
                                        {{ $absensi->total_jam_kerja }} jam</p>
                                </div>
                                <div>
                                    <label class="text-xs sm:text-sm text-[#1B1B1B] font-medium">Status</label>
                                    <p class="text-sm sm:text-base text-[#27438D]">
                                        @php
                                            $statusClass = match ($absensi->status) {
                                                'Hadir' => 'bg-[#2E7D3E] text-white',
                                                'Izin' => 'bg-[#FCC626] text-[#1B1B1B]',
                                                'Sakit' => 'bg-[#00a2e9] text-white',
                                                'Alpha' => 'bg-[#ec1d1d] text-white',
                                                'Perjalanan Dinas' => 'bg-purple-600 text-white',
                                                'Cuti' => 'bg-[#27438D] text-white',
                                                default => 'bg-gray-200 text-gray-800',
                                            };
                                        @endphp
                                        <span class="px-2 py-1 rounded-full text-xs font-medium {{ $statusClass }}">
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
                        <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                            <div class="bg-[#F5F5F5] rounded-lg p-3">
                                <p class="text-xs text-[#1B1B1B]">Latitude</p>
                                <p class="text-sm font-semibold text-[#161758] break-all">{{ $absensi->latitude ?? '-' }}
                                </p>
                            </div>
                            <div class="bg-[#F5F5F5] rounded-lg p-3">
                                <p class="text-xs text-[#1B1B1B]">Longitude</p>
                                <p class="text-sm font-semibold text-[#161758] break-all">{{ $absensi->longitude ?? '-' }}
                                </p>
                            </div>
                            <div class="bg-[#F5F5F5] rounded-lg p-3">
                                <p class="text-xs text-[#1B1B1B]">Akurasi</p>
                                <p class="text-sm font-semibold text-[#161758]">
                                    {{ $absensi->location_accuracy ? $absensi->location_accuracy . ' meter' : '-' }}</p>
                            </div>
                            <div class="bg-[#F5F5F5] rounded-lg p-3">
                                <p class="text-xs text-[#1B1B1B]">Valid Lokasi</p>
                                <p
                                    class="text-sm font-semibold {{ $absensi->is_valid_location ? 'text-[#2E7D3E]' : 'text-[#ec1d1d]' }}">
                                    {{ $absensi->is_valid_location ? '✅ Valid' : '❌ Invalid' }}
                                </p>
                            </div>
                        </div>

                        @if (isset($distances) && count($distances) > 0)
                            <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
                                @foreach ($distances as $name => $distance)
                                    <div class="bg-[#F5F5F5] rounded-lg p-2 text-xs">
                                        <span class="font-medium">{{ $name }}:</span>
                                        <span
                                            class="{{ $distance <= 50 ? 'text-[#2E7D3E] font-bold' : 'text-[#ec1d1d]' }}">
                                            {{ number_format($distance, 2) }} meter
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        @if ($absensi->is_suspicious)
                            <div
                                class="mt-3 bg-[#FCC626]/20 border border-[#FCC626] text-[#1B1B1B] rounded-lg p-3 text-xs sm:text-sm">
                                ⚠️ Data ini ditandai <strong>mencurigakan</strong> oleh
                                sistem{{ $absensi->suspicious_reason ? ': ' . $absensi->suspicious_reason : '' }}. Mohon
                                ditinjau.
                            </div>
                        @endif
                    </div>

                    <!-- Jejak Audit -->
                    <div class="mt-6 pt-6 border-t border-gray-200">
                        <h3 class="text-base sm:text-lg font-semibold text-[#161758] mb-4">🔒 Jejak Audit (Anti-Manipulasi)
                        </h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                            <div class="bg-[#F5F5F5] rounded-lg p-3">
                                <p class="text-xs text-[#1B1B1B]">Alamat IP</p>
                                <p class="text-sm font-semibold text-[#161758] break-all">{{ $absensi->ip_address ?? '-' }}
                                </p>
                            </div>
                            <div class="bg-[#F5F5F5] rounded-lg p-3 sm:col-span-2 lg:col-span-2">
                                <p class="text-xs text-[#1B1B1B]">Perangkat / Browser</p>
                                <p class="text-xs font-semibold text-[#161758] break-all">
                                    {{ $absensi->user_agent ?? '-' }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Verifikasi Absen Manual (HR) -->
                    <div class="mt-6 pt-6 border-t border-gray-200">
                        <h3 class="text-base sm:text-lg font-semibold text-[#161758] mb-2">✅ Verifikasi Absen Manual (HR)
                        </h3>
                        <p class="text-xs sm:text-sm text-gray-500 mb-3">
                            Dipakai kalau karyawan lupa/tidak bisa absen sendiri. Kolom Lokasi diisi otomatis dari lokasi
                            Anda (HR) saat menekan tombol ini -- tidak perlu diisi manual.
                        </p>
                        <div id="verifikasi-manual-alert" class="hidden mb-3 text-xs sm:text-sm rounded-lg p-3"></div>
                        <div class="flex flex-col sm:flex-row gap-3">
                            @if (!$absensi->check_in)
                                <button type="button" id="btn-verifikasi-checkin"
                                    data-url="{{ route('hr.absensi.verifikasi-checkin', $absensi->id) }}"
                                    class="verifikasi-manual-btn w-full sm:w-auto bg-[#2E7D3E] text-white px-4 sm:px-6 py-2 rounded-lg hover:bg-[#256b34] transition-colors duration-200 text-sm sm:text-base">
                                    Verifikasi Check-in Manual
                                </button>
                            @endif
                            @if ($absensi->check_in && !$absensi->check_out)
                                <button type="button" id="btn-verifikasi-checkout"
                                    data-url="{{ route('hr.absensi.verifikasi-checkout', $absensi->id) }}"
                                    class="verifikasi-manual-btn w-full sm:w-auto bg-[#00a2e9] text-white px-4 sm:px-6 py-2 rounded-lg hover:bg-[#0088c4] transition-colors duration-200 text-sm sm:text-base">
                                    Verifikasi Check-out Manual
                                </button>
                            @endif
                            @if ($absensi->check_in && $absensi->check_out)
                                <p class="text-xs sm:text-sm text-gray-400">Check-in dan check-out sudah lengkap.</p>
                            @endif
                        </div>
                    </div>

                    <script>
                        document.querySelectorAll('.verifikasi-manual-btn').forEach(function(btn) {
                            btn.addEventListener('click', function() {
                                var url = btn.dataset.url;
                                var alertBox = document.getElementById('verifikasi-manual-alert');
                                var originalText = btn.textContent;

                                if (!navigator.geolocation) {
                                    alertBox.textContent = 'Browser Anda tidak mendukung deteksi lokasi (geolocation).';
                                    alertBox.className = 'mb-3 text-xs sm:text-sm rounded-lg p-3 bg-[#ec1d1d]/10 text-[#ec1d1d]';
                                    alertBox.classList.remove('hidden');
                                    return;
                                }

                                btn.disabled = true;
                                btn.textContent = 'Mengambil lokasi...';

                                navigator.geolocation.getCurrentPosition(function(position) {
                                    btn.textContent = 'Memproses...';

                                    fetch(url, {
                                        method: 'POST',
                                        headers: {
                                            'Content-Type': 'application/json',
                                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                                .content,
                                            'Accept': 'application/json',
                                        },
                                        body: JSON.stringify({
                                            latitude: position.coords.latitude,
                                            longitude: position.coords.longitude,
                                        }),
                                    })
                                    .then(function(res) {
                                        return res.json();
                                    })
                                    .then(function(data) {
                                        alertBox.textContent = data.message;
                                        alertBox.className = 'mb-3 text-xs sm:text-sm rounded-lg p-3 ' + (data
                                            .success ? 'bg-[#2E7D3E]/10 text-[#2E7D3E]' :
                                            'bg-[#ec1d1d]/10 text-[#ec1d1d]');
                                        alertBox.classList.remove('hidden');

                                        if (data.success) {
                                            setTimeout(function() {
                                                window.location.reload();
                                            }, 900);
                                        } else {
                                            btn.disabled = false;
                                            btn.textContent = originalText;
                                        }
                                    })
                                    .catch(function() {
                                        alertBox.textContent = 'Terjadi kesalahan, coba lagi.';
                                        alertBox.className = 'mb-3 text-xs sm:text-sm rounded-lg p-3 bg-[#ec1d1d]/10 text-[#ec1d1d]';
                                        alertBox.classList.remove('hidden');
                                        btn.disabled = false;
                                        btn.textContent = originalText;
                                    });
                                }, function() {
                                    alertBox.textContent =
                                        'Gagal mengambil lokasi. Pastikan izin lokasi browser diaktifkan.';
                                    alertBox.className = 'mb-3 text-xs sm:text-sm rounded-lg p-3 bg-[#ec1d1d]/10 text-[#ec1d1d]';
                                    alertBox.classList.remove('hidden');
                                    btn.disabled = false;
                                    btn.textContent = originalText;
                                });
                            });
                        });
                    </script>

                    <!-- Form Update Status -->
                    <div class="mt-6 pt-6 border-t border-gray-200">
                        <h3 class="text-base sm:text-lg font-semibold text-[#161758] mb-4">Update Status</h3>
                        <form action="{{ route('hr.absensi.update-status', $absensi->id) }}" method="POST"
                            class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4">
                            @csrf
                            @method('PUT')
                            <div>
                                <label class="block text-xs sm:text-sm font-medium text-[#1B1B1B] mb-1">Status</label>
                                <select name="status" required
                                    class="w-full px-3 sm:px-4 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                                    <option value="Hadir" {{ $absensi->status == 'Hadir' ? 'selected' : '' }}>Hadir
                                    </option>
                                    <option value="Izin" {{ $absensi->status == 'Izin' ? 'selected' : '' }}>Izin
                                    </option>
                                    <option value="Sakit" {{ $absensi->status == 'Sakit' ? 'selected' : '' }}>Sakit
                                    </option>
                                    <option value="Alpha" {{ $absensi->status == 'Alpha' ? 'selected' : '' }}>Alpha
                                    </option>
                                    <option value="Perjalanan Dinas"
                                        {{ $absensi->status == 'Perjalanan Dinas' ? 'selected' : '' }}>Perjalanan Dinas
                                    </option>
                                    <option value="Cuti" {{ $absensi->status == 'Cuti' ? 'selected' : '' }}>Cuti
                                    </option>
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
