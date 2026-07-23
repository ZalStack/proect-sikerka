{{-- resources/views/karyawan/khataman/dashboard.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="flex min-h-screen">
    @include('layouts.sidebar')
    <div class="flex-1 transition-all duration-300 md:ml-64 pt-6">
        <div class="p-4 sm:p-6">
            <div class="mb-6">
                <h1 class="text-xl sm:text-2xl font-bold font-['Montserrat'] text-[#161758]">Khataman</h1>
                <p class="text-sm sm:text-base text-[#27438D]">Absensi kegiatan Khataman (Kamis)</p>
            </div>

            @if(session('success'))
                <div class="bg-[#2E7D3E] text-white p-3 sm:p-4 rounded-lg mb-4 text-sm">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="bg-[#ec1d1d] text-white p-3 sm:p-4 rounded-lg mb-4 text-sm">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Jam Real-Time -->
            <div class="bg-white rounded-lg shadow-md p-4 sm:p-6 mb-6">
                <div class="flex flex-col sm:flex-row items-center justify-between gap-3">
                    <div>
                        <p class="text-xs sm:text-sm text-[#1B1B1B] font-medium">⏰ Waktu Server (Real-Time)</p>
                        <div id="serverClock" class="text-2xl sm:text-4xl font-mono font-bold text-[#161758] tracking-wider">
                            {{ Carbon\Carbon::now()->format('H:i:s') }}
                        </div>
                        <p id="serverDate" class="text-xs sm:text-sm text-[#27438D] mt-1">
                            {{ Carbon\Carbon::now()->locale('id')->isoFormat('dddd, D MMMM YYYY') }}
                        </p>
                    </div>
                    <div class="text-center sm:text-right">
                        @if($todayAbsensi)
                            <span class="px-3 sm:px-4 py-2 rounded-full text-xs sm:text-sm font-medium bg-[#2E7D3E] text-white">
                                ✅ Sudah Absen
                            </span>
                        @elseif($isActiveDay)
                            <span class="px-3 sm:px-4 py-2 rounded-full text-xs sm:text-sm font-medium bg-[#FCC626] text-[#1B1B1B]">
                                ⏳ Belum Absen
                            </span>
                        @else
                            <span class="px-3 sm:px-4 py-2 rounded-full text-xs sm:text-sm font-medium bg-gray-300 text-gray-600">
                                ⛔ Tidak Ada Kegiatan
                            </span>
                        @endif
                        <p class="text-xs text-gray-500 mt-1">
                            {{ $isActiveDay ? '📅 Hari ini Kamis (aktif)' : '📅 Hari ini bukan Kamis' }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Form Absen (hanya jika hari Kamis dan belum absen) -->
            @if($isActiveDay && !$todayAbsensi)
            <div class="bg-white rounded-lg shadow-md p-4 sm:p-6 mb-6">
                <h2 class="text-base sm:text-lg font-semibold text-[#161758] mb-4">📝 Absen Khataman</h2>
                <form id="khatamanForm" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs sm:text-sm font-medium text-[#1B1B1B] mb-2">
                            Kode Kegiatan <span class="text-[#ec1d1d]">*</span>
                        </label>
                        <input type="text" name="kode_absensi" id="kode_absensi"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9] text-sm"
                               placeholder="Masukkan kode yang diumumkan" required>
                    </div>

                    <button type="submit" id="submitBtn"
                            class="w-full sm:w-auto bg-[#2E7D3E] text-white px-4 sm:px-6 py-2 sm:py-3 rounded-lg hover:bg-[#009a4b] transition-colors duration-200 font-semibold text-sm sm:text-base">
                        📤 Kirim Absen Khataman
                    </button>
                </form>
            </div>
            @endif

            <!-- Statistik Bulan Ini -->
            <div class="bg-white rounded-lg shadow-md p-4 sm:p-6 mb-6">
                <h2 class="text-base sm:text-lg font-semibold text-[#161758] mb-4">Statistik Bulan Ini</h2>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4">
                    <div class="bg-[#F5F5F5] rounded-lg p-3 sm:p-4 text-center">
                        <p class="text-xl sm:text-2xl font-bold text-[#161758]">{{ $statistik['total_hari_aktif'] }}</p>
                        <p class="text-xs sm:text-sm text-[#1B1B1B]">Total Hari Kamis</p>
                    </div>
                    <div class="bg-[#2E7D3E] text-white rounded-lg p-3 sm:p-4 text-center">
                        <p class="text-xl sm:text-2xl font-bold">{{ $statistik['hadir'] }}</p>
                        <p class="text-xs sm:text-sm">Hadir</p>
                    </div>
                    <div class="bg-[#F5F5F5] rounded-lg p-3 sm:p-4 text-center">
                        <p class="text-xl sm:text-2xl font-bold text-[#161758]">{{ $statistik['total_hari_aktif'] - $statistik['hadir'] }}</p>
                        <p class="text-xs sm:text-sm text-[#1B1B1B]">Belum Hadir</p>
                    </div>
                </div>
            </div>

            <!-- Daftar Hari Kamis dan Absensi -->
            <div class="bg-white rounded-lg shadow-md p-4 sm:p-6">
                <h2 class="text-base sm:text-lg font-semibold text-[#161758] mb-4">📋 Daftar Absensi Khataman Bulan Ini (Kamis)</h2>
                <div class="overflow-x-auto -mx-4 sm:mx-0">
                    <div class="inline-block min-w-full align-middle">
                        <table class="min-w-full">
                            <thead>
                                <tr class="bg-[#F5F5F5]">
                                    <th class="px-3 sm:px-4 py-2 text-left text-xs sm:text-sm font-semibold text-[#1B1B1B]">Tanggal</th>
                                    <th class="px-3 sm:px-4 py-2 text-left text-xs sm:text-sm font-semibold text-[#1B1B1B]">Hari</th>
                                    <th class="px-3 sm:px-4 py-2 text-left text-xs sm:text-sm font-semibold text-[#1B1B1B]">Check-in</th>
                                    <th class="px-3 sm:px-4 py-2 text-left text-xs sm:text-sm font-semibold text-[#1B1B1B]">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($activeDays as $day)
                                    @php
                                        $absen = $absensi->get($day->format('Y-m-d'));
                                    @endphp
                                    <tr class="border-b border-gray-200">
                                        <td class="px-3 sm:px-4 py-2 text-xs sm:text-sm">{{ $day->format('d-m-Y') }}</td>
                                        <td class="px-3 sm:px-4 py-2 text-xs sm:text-sm">{{ $day->format('l') }}</td>
                                        <td class="px-3 sm:px-4 py-2 text-xs sm:text-sm">
                                            {{ $absen && $absen->check_in ? Carbon\Carbon::parse($absen->check_in)->format('H:i:s') : '-' }}
                                        </td>
                                        <td class="px-3 sm:px-4 py-2 text-xs sm:text-sm">
                                            @if($absen)
                                                <span class="px-2 py-1 rounded-full text-[10px] sm:text-xs font-medium bg-[#2E7D3E] text-white">
                                                    ✅ Hadir
                                                </span>
                                            @else
                                                <span class="px-2 py-1 rounded-full text-[10px] sm:text-xs font-medium bg-gray-300 text-gray-600">
                                                    ⬜ Belum
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-4 py-4 text-center text-xs sm:text-sm text-[#1B1B1B]">
                                            Tidak ada hari Kamis di bulan ini
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

    // --- Sinkronisasi jam ke SERVER (bukan jam device karyawan) ---
    // serverOffsetMs = selisih antara waktu server dan waktu device saat sinkronisasi.
    // Jam yang ditampilkan = waktu device + offset, sehingga tetap "real-time"
    // per detik tanpa perlu request tiap detik, tapi tetap akurat sesuai server.
    let serverOffsetMs = 0;
    let lastSyncFailed = false;

    async function syncServerTime() {
        try {
            const res = await fetch('{{ route("karyawan.khataman.server-time") }}', {
                headers: { 'Accept': 'application/json' }
            });
            const data = await res.json();
            if (data.success) {
                serverOffsetMs = data.timestamp_ms - Date.now();
                lastSyncFailed = false;
            }
        } catch (error) {
            // Kalau gagal sync, tetap pakai offset terakhir yang diketahui
            lastSyncFailed = true;
            console.error('Gagal sinkronisasi jam server:', error);
        }
    }

    // Fungsi update jam real-time (setiap 1 detik), berbasis waktu server
    function updateClock() {
        const now = new Date(Date.now() + serverOffsetMs);

        // Format jam HH:MM:SS
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const seconds = String(now.getSeconds()).padStart(2, '0');
        const timeString = hours + ':' + minutes + ':' + seconds;

        // Update elemen jam
        const clockElem = document.getElementById('serverClock');
        if (clockElem) {
            clockElem.textContent = timeString;
        }

        // Update tanggal
        const dateElem = document.getElementById('serverDate');
        if (dateElem) {
            const options = {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            };
            dateElem.textContent = now.toLocaleDateString('id-ID', options);
        }
    }

    // Sinkronisasi awal saat halaman dimuat, lalu jalankan jam
    syncServerTime().then(updateClock);
    // Perbarui tampilan jam setiap 1 detik (berbasis offset server)
    setInterval(updateClock, 1000);
    // Sinkronisasi ulang ke server tiap 60 detik untuk menghindari drift
    setInterval(syncServerTime, 60000);

    // Submit form absen
    document.getElementById('khatamanForm')?.addEventListener('submit', function(e) {
        e.preventDefault();

        const kode = document.getElementById('kode_absensi').value.trim();
        if (!kode) {
            Swal.fire({
                icon: 'warning',
                title: 'Kode wajib diisi!',
                text: 'Silakan masukkan kode kegiatan yang diumumkan.',
                confirmButtonColor: '#FCC626'
            });
            return;
        }

        const formData = new FormData(this);
        const btn = document.getElementById('submitBtn');
        btn.disabled = true;
        btn.textContent = '⏳ Memproses...';

        fetch('{{ route("karyawan.khataman.checkin") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: '✅ Absen Khataman Berhasil!',
                    html: `
                        <div class="text-left">
                            <p><strong>Waktu Check-in:</strong> ${data.data.waktu}</p>
                            <p><strong>Tanggal:</strong> ${data.data.tanggal}</p>
                            <p><strong>Status:</strong> Hadir</p>
                        </div>
                    `,
                    timer: 3000,
                    showConfirmButton: true,
                    confirmButtonColor: '#2E7D3E'
                }).then(() => {
                    location.reload();
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Absen Gagal!',
                    text: data.message,
                    confirmButtonColor: '#ec1d1d'
                });
                btn.disabled = false;
                btn.textContent = '📤 Kirim Absen Khataman';
            }
        })
        .catch(error => {
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'Terjadi kesalahan pada server',
                confirmButtonColor: '#ec1d1d'
            });
            btn.disabled = false;
            btn.textContent = '📤 Kirim Absen Khataman';
        });
    });
</script>
@endsection
