{{-- views/karyawan/absensi.blade.php --}}
@extends('layouts.app')

@section('content')
    <div class="flex min-h-screen bg-gray-50">
        @include('layouts.sidebar')
        <div class="flex-1 transition-all duration-300 md:ml-64">
            <div class="p-3 sm:p-4 lg:p-6">
                <div class="mb-6">
                    <h1 class="text-xl sm:text-2xl font-bold font-['Montserrat'] text-[#161758]">Absensi Karyawan</h1>
                    <p class="text-sm sm:text-base text-[#27438D]">Check-in / Check-out dengan lokasi GPS</p>
                    <p class="text-xs text-gray-500 mt-1">📍 Radius absensi: 50 meter dari kantor KPM</p>
                </div>

                <!-- Jam Realtime -->
                <div class="bg-[#161758] rounded-lg shadow-md p-4 sm:p-6 mb-4 text-center">
                    <p class="text-sm text-white/70 mb-1">Jam Sekarang (Realtime Server)</p>
                    <p id="liveClock" class="text-2xl sm:text-4xl font-bold text-white font-mono tracking-widest">--:--:--
                    </p>
                    <p id="liveDate" class="text-sm text-white/80 mt-1">Memuat tanggal...</p>
                </div>

                <!-- Status Lokasi - AUTO DETECT -->
                <div id="locationStatus"
                    class="bg-white rounded-lg shadow-md p-4 mb-4 border-2 {{ session('location_valid', false) ? 'border-[#2E7D3E]' : 'border-gray-200' }}">
                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                        <div class="flex items-center space-x-3">
                            <svg class="w-6 h-6 {{ session('location_valid', false) ? 'text-[#2E7D3E]' : 'text-[#27438D]' }}"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <div>
                                <span id="locationStatusText" class="font-semibold text-[#FCC626]">⏳ Mendeteksi lokasi
                                    otomatis...</span>
                                <p id="locationDetailText" class="text-xs text-[#1B1B1B] mt-1">Mengambil lokasi GPS...</p>
                                <p id="locationDistanceText" class="text-xs mt-1"></p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <span id="locationDot" class="w-3 h-3 rounded-full bg-yellow-400 animate-pulse"></span>
                            <button onclick="getLocation(true)"
                                class="bg-[#27438D] text-white px-3 py-1.5 rounded-lg hover:bg-[#161758] transition-colors duration-200 text-xs">
                                🔄 Refresh Lokasi
                            </button>
                        </div>
                    </div>
                    <div class="mt-3 w-full bg-gray-200 rounded-full h-1.5">
                        <div id="locationProgress" class="bg-[#27438D] h-1.5 rounded-full transition-all duration-500"
                            style="width: 0%"></div>
                    </div>
                </div>

                <!-- Status Absensi Hari Ini -->
                <div id="statusContainer" class="bg-white rounded-lg shadow-md p-4 sm:p-6 mb-6">
                    <h2 class="text-lg font-semibold text-[#161758] mb-4">Status Absensi Hari Ini</h2>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                        <div class="bg-[#F5F5F5] rounded-lg p-3">
                            <p class="text-xs text-[#1B1B1B]">Status</p>
                            <p id="statusText" class="text-lg font-bold text-[#161758]">Loading...</p>
                        </div>
                        <div class="bg-[#F5F5F5] rounded-lg p-3">
                            <p class="text-xs text-[#1B1B1B]">Check-in</p>
                            <p id="checkInText" class="text-lg font-bold text-[#161758]">-</p>
                        </div>
                        <div class="bg-[#F5F5F5] rounded-lg p-3">
                            <p class="text-xs text-[#1B1B1B]">Check-out</p>
                            <p id="checkOutText" class="text-lg font-bold text-[#161758]">-</p>
                        </div>
                        <div class="bg-[#F5F5F5] rounded-lg p-3">
                            <p class="text-xs text-[#1B1B1B]">Lokasi</p>
                            <p id="locationValidText" class="text-lg font-bold text-[#161758]">-</p>
                        </div>
                    </div>
                    <div
                        class="mt-3 grid grid-cols-1 md:grid-cols-2 gap-2 text-sm text-[#1B1B1B] bg-[#F5F5F5] p-3 rounded-lg">
                        <div>
                            <p>📅 Tanggal: <span id="tanggalText" class="font-semibold"></span></p>
                            <p>📆 Hari: <span id="hariText" class="font-semibold"></span></p>
                        </div>
                        <div>
                            <p>🏢 Kantor: <span id="kantorText" class="font-semibold">-</span></p>
                            <p>⏱️ Total Jam: <span id="totalJamText" class="font-semibold">0</span> jam</p>
                        </div>
                    </div>
                </div>

                <!-- Tombol Absensi -->
                <div class="bg-white rounded-lg shadow-md p-4 sm:p-6 mb-6">
                    <h2 class="text-lg font-semibold text-[#161758] mb-4">Absensi Hari Ini</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <button id="btnCheckIn" onclick="handleCheckIn()"
                            class="bg-[#2E7D3E] text-white px-6 py-4 rounded-lg hover:bg-[#009a4b] transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed text-lg font-semibold">🟢
                            Check-in</button>
                        <button id="btnCheckOut" onclick="handleCheckOut()"
                            class="bg-[#ec1d1d] text-white px-6 py-4 rounded-lg hover:bg-red-700 transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed text-lg font-semibold"
                            disabled>🔴 Check-out</button>
                    </div>
                    <div class="mt-4 text-sm text-[#1B1B1B] bg-[#F5F5F5] p-3 rounded-lg">
                        <p class="font-semibold">ℹ️ Informasi:</p>
                        <ul class="list-disc list-inside mt-1 space-y-1 text-xs sm:text-sm">
                            <li>Absensi dilakukan dengan <strong>deteksi lokasi GPS</strong></li>
                            <li>Lokasi Anda harus berada dalam <strong>radius 50 meter</strong> dari kantor KPM</li>
                            <li>Sinyal GPS harus cukup akurat (akurasi lebih baik dari 75 meter). Jika di dalam
                                ruangan/gedung, coba dekat jendela atau area terbuka</li>
                            <li>Check-in hanya 1 kali per hari, Check-out setelah Check-in</li>
                            <li>Jam yang digunakan adalah <strong>jam server</strong> (realtime), bukan jam HP Anda</li>
                        </ul>
                    </div>
                </div>

                <!-- Lokasi Kantor -->
                @if (!empty($officeLocations))
                    <div class="bg-white rounded-lg shadow-md p-4 sm:p-6 mb-6">
                        <h2 class="text-lg font-semibold text-[#161758] mb-4">📍 Lokasi Kantor KPM</h2>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2 text-sm">
                            @foreach ($officeLocations as $name => $coords)
                                <div class="bg-[#F5F5F5] rounded-lg p-3">
                                    <p class="font-semibold text-[#161758]">{{ $name }}</p>
                                    <p class="text-xs text-gray-500">{{ number_format($coords['latitude'], 6) }},
                                        {{ number_format($coords['longitude'], 6) }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Riwayat 7 Hari Terakhir -->
                <div class="bg-white rounded-lg shadow-md p-4 sm:p-6">
                    <h2 class="text-lg font-semibold text-[#161758] mb-4">Riwayat 7 Hari Terakhir</h2>
                    <div class="overflow-x-auto -mx-4 sm:mx-0">
                        <div class="inline-block min-w-full align-middle">
                            <table class="min-w-full">
                                <thead>
                                    <tr class="bg-[#F5F5F5]">
                                        <th class="px-3 sm:px-4 py-2 text-left text-xs font-semibold text-[#1B1B1B]">Tanggal
                                        </th>
                                        <th class="px-3 sm:px-4 py-2 text-left text-xs font-semibold text-[#1B1B1B]">
                                            Check-in</th>
                                        <th class="px-3 sm:px-4 py-2 text-left text-xs font-semibold text-[#1B1B1B]">
                                            Check-out</th>
                                        <th class="px-3 sm:px-4 py-2 text-left text-xs font-semibold text-[#1B1B1B]">Status
                                        </th>
                                        <th class="px-3 sm:px-4 py-2 text-left text-xs font-semibold text-[#1B1B1B]">Lokasi
                                        </th>
                                        <th class="px-3 sm:px-4 py-2 text-left text-xs font-semibold text-[#1B1B1B]">Total
                                            Jam</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($last7Days as $day)
                                        <tr class="border-b border-gray-200">
                                            <td class="px-3 sm:px-4 py-2 text-xs">{{ $day['tanggal'] }}</td>
                                            <td class="px-3 sm:px-4 py-2 text-xs">{{ $day['check_in'] }}</td>
                                            <td class="px-3 sm:px-4 py-2 text-xs">{{ $day['check_out'] }}</td>
                                            <td class="px-3 sm:px-4 py-2 text-xs">
                                                @if ($day['status'] == 'Hadir')
                                                    <span
                                                        class="px-2 py-1 rounded-full text-[10px] font-medium bg-[#2E7D3E] text-white">Hadir</span>
                                                @elseif($day['status'] == 'Izin')
                                                    <span
                                                        class="px-2 py-1 rounded-full text-[10px] font-medium bg-[#FCC626] text-[#1B1B1B]">Izin</span>
                                                @elseif($day['status'] == 'Sakit')
                                                    <span
                                                        class="px-2 py-1 rounded-full text-[10px] font-medium bg-[#00a2e9] text-white">Sakit</span>
                                                @elseif($day['status'] == 'Perjalanan Dinas')
                                                    <span
                                                        class="px-2 py-1 rounded-full text-[10px] font-medium bg-purple-600 text-white">Perjalanan
                                                        Dinas</span>
                                                @else
                                                    <span
                                                        class="px-2 py-1 rounded-full text-[10px] font-medium bg-[#ec1d1d] text-white">Alpha</span>
                                                @endif
                                            </td>
                                            <td class="px-3 sm:px-4 py-2 text-xs">
                                                @if ($day['is_valid'])
                                                    <span class="text-[#2E7D3E]">✅ Valid</span>
                                                    @if ($day['distance'])
                                                        <span
                                                            class="text-[10px] text-gray-500 block">{{ $day['distance'] }}m</span>
                                                    @endif
                                                @else
                                                    <span class="text-[#ec1d1d]">❌ Invalid</span>
                                                @endif
                                            </td>
                                            <td class="px-3 sm:px-4 py-2 text-xs">{{ $day['total_jam'] }} jam</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="px-4 py-8 text-center text-gray-500">Belum ada data
                                                absensi 7 hari terakhir.</td>
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

    <!-- SweetAlert2 & Script -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // ==========================================================
        // KONFIGURASI
        // ==========================================================
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        const MAX_RADIUS = 50; // meter
        const MAX_GPS_ACCURACY = 75; // meter, harus sinkron dengan Absensi::MAX_GPS_ACCURACY di backend

        // Selama proses mencari sinyal GPS, cek lebih sering (lebih responsif)
        const LOCATION_SEARCH_INTERVAL = 3000; // 3 detik
        // Setelah lokasi valid & akurat didapat, kunci hasilnya selama 1 menit
        // supaya UI tidak balik ke "mencari lokasi" terus-menerus
        const LOCATION_LOCK_DURATION = 60000; // 1 menit
        // Jika error (izin ditolak / GPS mati / timeout), coba lagi lebih cepat
        const LOCATION_RETRY_INTERVAL = 3000; // 3 detik

        let currentLocation = null;
        let isLocationValid = false;
        let isLocationChecking = false;
        let locationLockedUntil = 0; // timestamp (ms) sampai kapan lokasi masih dianggap terkunci/valid

        // Server time offset
        let serverTimeOffsetMs = 0;
        let clockSynced = false;

        // ==========================================================
        // JAM REALTIME
        // ==========================================================
        function syncServerTime(timestampMs) {
            serverTimeOffsetMs = timestampMs - Date.now();
            clockSynced = true;
        }

        function tickClock() {
            const el = document.getElementById('liveClock');
            const dateEl = document.getElementById('liveDate');

            if (!clockSynced) {
                el.textContent = '--:--:--';
                return;
            }

            const now = new Date(Date.now() + serverTimeOffsetMs);
            const hh = String(now.getHours()).padStart(2, '0');
            const mm = String(now.getMinutes()).padStart(2, '0');
            const ss = String(now.getSeconds()).padStart(2, '0');
            el.textContent = `${hh}:${mm}:${ss}`;

            const hariList = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
            const bulanList = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September',
                'Oktober', 'November', 'Desember'
            ];
            dateEl.textContent =
                `${hariList[now.getDay()]}, ${now.getDate()} ${bulanList[now.getMonth()]} ${now.getFullYear()}`;
        }

        function fetchServerTime() {
            fetch('{{ route('karyawan.absensi.server-time') }}', {
                    headers: {
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        syncServerTime(data.timestamp_ms);
                    }
                })
                .catch(() => {});
        }

        setInterval(tickClock, 1000);
        setInterval(fetchServerTime, 30000);

        // ==========================================================
        // GEOLOKASI - AUTO DETECT
        // ==========================================================
        function getLocation(force = false) {
            // Kalau lokasi masih "terkunci" (baru saja dapat sinyal akurat) dan
            // bukan permintaan paksa (refresh manual / retry setelah gagal absen),
            // jangan cari ulang dulu supaya UI tidak flicker balik ke "mencari lokasi".
            if (!force && Date.now() < locationLockedUntil) {
                return;
            }

            if (isLocationChecking) return;
            isLocationChecking = true;

            const statusText = document.getElementById('locationStatusText');
            const detailText = document.getElementById('locationDetailText');
            const distanceText = document.getElementById('locationDistanceText');
            const progress = document.getElementById('locationProgress');
            const dot = document.getElementById('locationDot');

            statusText.textContent = '⏳ Mendeteksi lokasi otomatis...';
            statusText.className = 'font-semibold text-[#FCC626]';
            detailText.textContent = 'Mengambil sinyal GPS...';
            distanceText.textContent = '';
            dot.className = 'w-3 h-3 rounded-full bg-yellow-400 animate-pulse';
            progress.style.width = '30%';

            if (!navigator.geolocation) {
                statusText.textContent = '❌ GPS tidak didukung';
                statusText.className = 'font-semibold text-[#ec1d1d]';
                detailText.textContent = 'Browser Anda tidak mendukung GPS';
                dot.className = 'w-3 h-3 rounded-full bg-red-500';
                isLocationChecking = false;
                return;
            }

            navigator.geolocation.getCurrentPosition(
                function(position) {
                    progress.style.width = '70%';

                    currentLocation = {
                        latitude: position.coords.latitude,
                        longitude: position.coords.longitude,
                        accuracy: position.coords.accuracy
                    };

                    detailText.textContent =
                        `📍 ${currentLocation.latitude.toFixed(6)}, ${currentLocation.longitude.toFixed(6)} | Akurasi: ${(currentLocation.accuracy || 0).toFixed(1)}m`;
                    progress.style.width = '100%';

                    // Validasi lokasi
                    validateLocation(currentLocation);

                    // Kalau akurasi GPS sudah cukup baik, kunci hasil ini selama 1 menit
                    // supaya tidak mencari-cari sinyal lagi padahal sudah dapat.
                    if (currentLocation.accuracy && currentLocation.accuracy <= MAX_GPS_ACCURACY) {
                        locationLockedUntil = Date.now() + LOCATION_LOCK_DURATION;
                    } else {
                        // Akurasi masih kurang baik, jangan dikunci, biar dicoba lagi lebih cepat
                        locationLockedUntil = 0;
                    }

                    setTimeout(() => {
                        progress.style.width = '0%';
                    }, 1000);

                    isLocationChecking = false;
                },
                function(error) {
                    let msg = 'Gagal mendapatkan lokasi';
                    switch (error.code) {
                        case error.PERMISSION_DENIED:
                            msg = 'Izin lokasi ditolak. Izinkan akses lokasi di browser.';
                            break;
                        case error.POSITION_UNAVAILABLE:
                            msg = 'Informasi lokasi tidak tersedia. Pastikan GPS aktif.';
                            break;
                        case error.TIMEOUT:
                            msg = 'Waktu pengambilan lokasi habis. Coba lagi.';
                            break;
                    }
                    statusText.textContent = '❌ ' + msg;
                    statusText.className = 'font-semibold text-[#ec1d1d]';
                    detailText.textContent = 'Error: ' + error.message;
                    dot.className = 'w-3 h-3 rounded-full bg-red-500';
                    progress.style.width = '100%';
                    isLocationValid = false;
                    isLocationChecking = false;
                    locationLockedUntil = 0; // jangan dikunci kalau gagal
                    updateButtons();

                    setTimeout(() => {
                        progress.style.width = '0%';
                    }, 1000);

                    // Coba lagi lebih cepat (sebelumnya 5 detik)
                    setTimeout(() => getLocation(true), LOCATION_RETRY_INTERVAL);
                }, {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 5000
                }
            );
        }

        function validateLocation(location) {
            const statusText = document.getElementById('locationStatusText');
            const detailText = document.getElementById('locationDetailText');
            const distanceText = document.getElementById('locationDistanceText');
            const dot = document.getElementById('locationDot');
            const container = document.getElementById('locationStatus');

            const officeLocations = {!! json_encode($officeLocations ?? []) !!};
            let nearest = null;
            let nearestDist = Infinity;

            for (const [name, coords] of Object.entries(officeLocations)) {
                const dist = haversineDistance(
                    location.latitude,
                    location.longitude,
                    coords.latitude,
                    coords.longitude
                );
                if (dist < nearestDist) {
                    nearestDist = dist;
                    nearest = name;
                }
            }

            const isValid = nearestDist <= MAX_RADIUS;

            if (isValid) {
                statusText.textContent = '✅ Lokasi VALID - ' + nearest + ' (' + nearestDist.toFixed(1) + 'm)';
                statusText.className = 'font-semibold text-[#2E7D3E]';
                dot.className = 'w-3 h-3 rounded-full bg-[#2E7D3E]';
                container.className = 'bg-white rounded-lg shadow-md p-4 mb-4 border-2 border-[#2E7D3E]';
                distanceText.textContent = `✅ Dalam radius ${MAX_RADIUS}m dari ${nearest} (${nearestDist.toFixed(1)}m)`;
                distanceText.className = 'text-xs text-[#2E7D3E] font-semibold';
                isLocationValid = true;
            } else {
                statusText.textContent = '❌ Di LUAR radius kantor (terdekat: ' + nearestDist.toFixed(1) + 'm)';
                statusText.className = 'font-semibold text-[#ec1d1d]';
                dot.className = 'w-3 h-3 rounded-full bg-[#ec1d1d]';
                container.className = 'bg-white rounded-lg shadow-md p-4 mb-4 border-2 border-[#ec1d1d]';
                distanceText.textContent =
                    `❌ Jarak terdekat ${nearestDist.toFixed(1)}m dari ${nearest} (maks ${MAX_RADIUS}m)`;
                distanceText.className = 'text-xs text-[#ec1d1d] font-semibold';
                isLocationValid = false;
            }

            detailText.textContent =
                `📍 ${location.latitude.toFixed(6)}, ${location.longitude.toFixed(6)} | Akurasi: ${(location.accuracy || 0).toFixed(1)}m | ${nearest}: ${nearestDist.toFixed(1)}m`;

            updateButtons();
        }

        function haversineDistance(lat1, lon1, lat2, lon2) {
            const R = 6371000;
            const dLat = (lat2 - lat1) * Math.PI / 180;
            const dLon = (lon2 - lon1) * Math.PI / 180;
            const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                Math.sin(dLon / 2) * Math.sin(dLon / 2);
            const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
            return R * c;
        }

        // ==========================================================
        // ABSENSI
        // ==========================================================
        function performAbsensi(action) {
            if (!currentLocation) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Lokasi Tidak Diketahui',
                    text: 'Silakan tunggu deteksi lokasi otomatis atau tekan Refresh Lokasi.',
                    confirmButtonColor: '#FCC626'
                });
                return;
            }

            if (!isLocationValid) {
                Swal.fire({
                    icon: 'error',
                    title: 'Lokasi Tidak Valid',
                    text: 'Anda harus berada dalam radius 50 meter dari kantor KPM.',
                    confirmButtonColor: '#ec1d1d'
                });
                return;
            }

            // Disable buttons while processing
            document.getElementById('btnCheckIn').disabled = true;
            document.getElementById('btnCheckOut').disabled = true;

            const url = action === 'checkin' ?
                '{{ route('karyawan.absensi.checkin') }}' :
                '{{ route('karyawan.absensi.checkout') }}';

            fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        latitude: currentLocation.latitude,
                        longitude: currentLocation.longitude,
                        accuracy: currentLocation.accuracy || 0
                    })
                })
                .then(response => response.json().then(data => ({
                    status: response.status,
                    data
                })))
                .then(({
                    status,
                    data
                }) => {
                    if (data.success) {
                        if (data.data && data.data.server_timestamp_ms) {
                            syncServerTime(data.data.server_timestamp_ms);
                        }

                        const title = action === 'checkin' ? '✅ Check-in Berhasil!' : '✅ Check-out Berhasil!';

                        Swal.fire({
                            icon: 'success',
                            title: title,
                            html: `
                    <div class="text-left">
                        <p><strong>Waktu:</strong> ${data.data.waktu}</p>
                        <p><strong>Tanggal:</strong> ${data.data.tanggal}</p>
                        <p><strong>Kantor:</strong> ${data.data.kantor || 'KPM'}</p>
                        <p><strong>Jarak:</strong> ${data.data.distance || 0} meter</p>
                        ${data.data.total_jam ? `<p><strong>Total Jam:</strong> ${data.data.total_jam} jam</p>` : ''}
                    </div>
                `,
                            timer: 4000,
                            confirmButtonColor: '#2E7D3E'
                        });

                        loadStatus();
                        updateButtons();
                    } else {
                        let errorMsg = data.message;
                        if (data.code === 'INVALID_LOCATION') {
                            errorMsg = 'Lokasi tidak valid! Jarak terdekat: ' + (data.distance || 0) + 'm dari ' + (data
                                .nearest_location || 'kantor');
                            getLocation(true);
                        } else if (data.code === 'POOR_GPS_ACCURACY') {
                            errorMsg = data.message || 'Sinyal GPS kurang akurat. Coba pindah ke area terbuka.';
                            getLocation(true);
                        }

                        Swal.fire({
                            icon: 'error',
                            title: 'Absensi Gagal!',
                            text: errorMsg,
                            confirmButtonColor: '#ec1d1d'
                        });

                        // Re-enable buttons
                        updateButtons();
                    }
                })
                .catch(error => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Terjadi kesalahan pada server',
                        confirmButtonColor: '#ec1d1d'
                    });
                    // Re-enable buttons
                    updateButtons();
                });
        }

        // ==========================================================
        // HANDLE CHECK-IN / CHECK-OUT
        // ==========================================================
        function handleCheckIn() {
            performAbsensi('checkin');
        }

        function handleCheckOut() {
            performAbsensi('checkout');
        }

        // ==========================================================
        // UPDATE BUTTONS
        // ==========================================================
        function updateButtons() {
            const btnCheckIn = document.getElementById('btnCheckIn');
            const btnCheckOut = document.getElementById('btnCheckOut');
            const checkInText = document.getElementById('checkInText').textContent;
            const checkOutText = document.getElementById('checkOutText').textContent;

            if (!isLocationValid) {
                btnCheckIn.disabled = true;
                btnCheckOut.disabled = true;
                btnCheckIn.title = 'Harus berada dalam radius 50 meter dari kantor';
                btnCheckOut.title = 'Harus berada dalam radius 50 meter dari kantor';
                return;
            }

            if (checkInText !== '-' && checkInText !== 'null' && checkOutText !== '-' && checkOutText !== 'null') {
                btnCheckIn.disabled = true;
                btnCheckOut.disabled = true;
                btnCheckIn.textContent = '✅ Selesai';
                btnCheckOut.textContent = '✅ Selesai';
            } else if (checkInText !== '-' && checkInText !== 'null' && (checkOutText === '-' || checkOutText === 'null')) {
                btnCheckIn.disabled = true;
                btnCheckOut.disabled = false;
                btnCheckIn.textContent = '✅ Check-in';
                btnCheckOut.textContent = '🔴 Check-out';
            } else {
                btnCheckIn.disabled = false;
                btnCheckOut.disabled = true;
                btnCheckIn.textContent = '🟢 Check-in';
                btnCheckOut.textContent = '🔴 Check-out';
            }
        }

        // ==========================================================
        // LOAD STATUS
        // ==========================================================
        function loadStatus() {
            fetch('{{ route('karyawan.absensi.status') }}', {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const d = data.data;
                        document.getElementById('statusText').textContent = d.status || '-';
                        document.getElementById('checkInText').textContent = d.check_in || '-';
                        document.getElementById('checkOutText').textContent = d.check_out || '-';
                        document.getElementById('kantorText').textContent = d.kantor || '-';
                        document.getElementById('tanggalText').textContent = d.tanggal || '-';
                        document.getElementById('hariText').textContent = d.hari || '-';
                        document.getElementById('totalJamText').textContent = d.total_jam || '0';

                        if (d.server_timestamp_ms) {
                            syncServerTime(d.server_timestamp_ms);
                        }

                        const locationValid = document.getElementById('locationValidText');
                        if (d.is_valid_location) {
                            locationValid.textContent = '✅ Valid';
                            locationValid.className = 'text-lg font-bold text-[#2E7D3E]';
                        } else {
                            locationValid.textContent = d.latitude ? '❌ Invalid' : '⏳ Belum absen';
                            locationValid.className = 'text-lg font-bold text-[#ec1d1d]';
                        }

                        updateButtons();
                    }
                })
                .catch(error => {
                    console.error('Error loading status:', error);
                });
        }

        // ==========================================================
        // INITIALIZATION
        // ==========================================================
        document.addEventListener('DOMContentLoaded', function() {
            fetchServerTime();
            loadStatus();

            // Auto detect location immediately
            setTimeout(getLocation, 500);
        });

        // Cek lokasi berkala setiap 3 detik (dipercepat dari 10 detik).
        // Kalau lokasi sudah terkunci (valid & akurat, dalam 1 menit terakhir),
        // getLocation() akan langsung return tanpa melakukan pencarian ulang,
        // jadi UI tidak flicker balik ke "mencari lokasi".
        setInterval(() => getLocation(), LOCATION_SEARCH_INTERVAL);

        // Refresh status every 15 seconds
        setInterval(loadStatus, 15000);
    </script>
@endsection
