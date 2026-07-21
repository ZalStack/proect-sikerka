{{-- views/karyawan/absensi.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="flex min-h-screen bg-gray-50">
    @include('layouts.sidebar')
    <div class="flex-1 transition-all duration-300 md:ml-64">
        <div class="p-3 sm:p-4 lg:p-6">
            <div class="mb-6">
                <h1 class="text-xl sm:text-2xl font-bold font-['Montserrat'] text-[#161758]">Absensi QR Code</h1>
                <p class="text-sm sm:text-base text-[#27438D]">Scan QR Code untuk check-in / check-out</p>
            </div>

            <!-- Jam Realtime -->
            <div class="bg-[#161758] rounded-lg shadow-md p-4 sm:p-6 mb-4 text-center">
                <p class="text-sm text-white/70 mb-1">Jam Sekarang (Realtime)</p>
                <p id="liveClock" class="text-2xl sm:text-4xl font-bold text-white font-mono tracking-widest">--:--:--</p>
                <p id="liveDate" class="text-sm text-white/80 mt-1">Memuat tanggal...</p>
            </div>

            <!-- Lokasi & Status -->
            <div id="locationStatus" class="bg-white rounded-lg shadow-md p-4 mb-4">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                    <div class="flex items-center space-x-3">
                        <svg class="w-6 h-6 text-[#27438D]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <div>
                            <span id="locationStatusText" class="font-semibold">🔴 Mengecek lokasi...</span>
                            <p id="locationDetailText" class="text-xs text-[#1B1B1B] mt-1">Mengambil lokasi...</p>
                        </div>
                    </div>
                    <button onclick="getLocation()" class="bg-[#27438D] text-white px-4 py-2 rounded-lg hover:bg-[#161758] transition-colors duration-200 text-sm">
                        📍 Ambil Lokasi
                    </button>
                </div>
            </div>

            <!-- QR Code Scanner -->
            <div class="bg-white rounded-lg shadow-md p-4 sm:p-6 mb-6">
                <h2 class="text-lg font-semibold text-[#161758] mb-4">Scan QR Code</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- QR Code Display -->
                    <div class="flex flex-col items-center justify-center bg-[#F5F5F5] rounded-lg p-4">
                        <div id="qrCodeContainer" class="bg-white p-4 rounded-lg shadow-sm">
                            <div id="qrPlaceholder" class="w-48 h-48 flex items-center justify-center text-gray-400 text-sm">
                                <span>🔍 QR Code akan muncul di sini</span>
                            </div>
                            <img id="qrCodeImage" class="w-48 h-48 hidden" alt="QR Code" />
                        </div>
                        <p class="text-xs text-gray-500 mt-2">
                            <span id="qrExpiryText">QR Code akan kadaluarsa dalam 5 menit</span>
                        </p>
                        <button onclick="refreshQrCode()" class="mt-2 bg-[#27438D] text-white px-4 py-2 rounded-lg hover:bg-[#161758] transition-colors duration-200 text-sm">
                            🔄 Refresh QR Code
                        </button>
                    </div>

                    <!-- Scanner Area -->
                    <div>
                        <div class="bg-[#F5F5F5] rounded-lg p-4">
                            <div id="scannerContainer" class="relative bg-black rounded-lg overflow-hidden" style="height: 250px;">
                                <div id="scannerPlaceholder" class="absolute inset-0 flex items-center justify-center text-white/50 text-sm">
                                    <div class="text-center">
                                        <svg class="w-16 h-16 mx-auto mb-2 text-white/30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 9V5a2 2 0 012-2h4M3 15v4a2 2 0 002 2h4M21 9V5a2 2 0 00-2-2h-4M21 15v4a2 2 0 01-2 2h-4"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v8M8 12h8"/>
                                        </svg>
                                        <span>Klik tombol di bawah untuk scan</span>
                                    </div>
                                </div>
                                <video id="scannerVideo" class="absolute inset-0 w-full h-full object-cover hidden"></video>
                                <canvas id="scannerCanvas" class="absolute inset-0 w-full h-full hidden"></canvas>
                                <div id="scannerOverlay" class="absolute inset-0 border-2 border-[#2E7D3E] pointer-events-none hidden"></div>
                            </div>
                            <div class="flex flex-wrap gap-2 mt-3">
                                <button onclick="startScanner()" id="scanBtn" class="flex-1 bg-[#27438D] text-white px-4 py-2 rounded-lg hover:bg-[#161758] transition-colors duration-200 text-sm">
                                    📷 Buka Kamera
                                </button>
                                <button onclick="stopScanner()" id="stopScanBtn" class="flex-1 bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600 transition-colors duration-200 text-sm hidden">
                                    ⏹ Stop
                                </button>
                                <button onclick="scanFromFile()" class="flex-1 bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600 transition-colors duration-200 text-sm">
                                    📁 Upload Gambar
                                </button>
                            </div>
                            <input type="file" id="fileInput" accept="image/*" class="hidden" onchange="handleFileScan(event)">
                            <div id="scanResult" class="mt-3 text-sm hidden"></div>
                        </div>
                    </div>
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
                <div class="mt-3 grid grid-cols-1 md:grid-cols-2 gap-2 text-sm text-[#1B1B1B] bg-[#F5F5F5] p-3 rounded-lg">
                    <div>
                        <p>📅 Tanggal: <span id="tanggalText" class="font-semibold"></span></p>
                        <p>📆 Hari: <span id="hariText" class="font-semibold"></span></p>
                    </div>
                    <div>
                        <p>🏢 Kantor: <span id="kantorText" class="font-semibold">-</span></p>
                        <p>⏱️ Total Jam: <span id="totalJamText" class="font-semibold">0</span> jam</p>
                        <p id="distanceText" class="text-xs text-gray-500"></p>
                    </div>
                </div>
            </div>

            <!-- Tombol Absensi -->
            <div class="bg-white rounded-lg shadow-md p-4 sm:p-6 mb-6">
                <h2 class="text-lg font-semibold text-[#161758] mb-4">Absensi Hari Ini</h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <button id="btnCheckIn"
                            onclick="handleCheckIn()"
                            class="bg-[#2E7D3E] text-white px-6 py-4 rounded-lg hover:bg-[#009a4b] transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed text-lg font-semibold">
                        🟢 Check-in
                    </button>
                    <button id="btnCheckOut"
                            onclick="handleCheckOut()"
                            class="bg-[#ec1d1d] text-white px-6 py-4 rounded-lg hover:bg-red-700 transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed text-lg font-semibold" disabled>
                        🔴 Check-out
                    </button>
                </div>

                <div class="mt-4 text-sm text-[#1B1B1B] bg-[#F5F5F5] p-3 rounded-lg">
                    <p class="font-semibold">ℹ️ Informasi:</p>
                    <ul class="list-disc list-inside mt-1 space-y-1 text-xs sm:text-sm">
                        <li>Absensi dilakukan dengan <strong>Scan QR Code</strong> yang tertera di layar</li>
                        <li>Lokasi Anda harus berada dalam <strong>radius 50 meter</strong> dari kantor KPM</li>
                        <li>QR Code akan <strong>kadaluarsa dalam 5 menit</strong> untuk keamanan</li>
                        <li>Check-in hanya 1 kali per hari, Check-out setelah Check-in</li>
                        <li>Jam yang digunakan adalah <strong>jam server</strong> (realtime)</li>
                    </ul>
                </div>
            </div>

            <!-- Lokasi Kantor -->
            <div class="bg-white rounded-lg shadow-md p-4 sm:p-6 mb-6">
                <h2 class="text-lg font-semibold text-[#161758] mb-4">📍 Lokasi Kantor KPM</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2 text-sm">
                    @foreach($officeLocations ?? [] as $name => $coords)
                    <div class="bg-[#F5F5F5] rounded-lg p-3">
                        <p class="font-semibold text-[#161758]">{{ $name }}</p>
                        <p class="text-xs text-gray-500">{{ number_format($coords['latitude'], 6) }}, {{ number_format($coords['longitude'], 6) }}</p>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Riwayat 7 Hari Terakhir -->
            <div class="bg-white rounded-lg shadow-md p-4 sm:p-6">
                <h2 class="text-lg font-semibold text-[#161758] mb-4">Riwayat 7 Hari Terakhir</h2>
                <div class="overflow-x-auto -mx-4 sm:mx-0">
                    <div class="inline-block min-w-full align-middle">
                        <table class="min-w-full">
                            <thead>
                                <tr class="bg-[#F5F5F5]">
                                    <th class="px-3 sm:px-4 py-2 text-left text-xs font-semibold text-[#1B1B1B]">Tanggal</th>
                                    <th class="px-3 sm:px-4 py-2 text-left text-xs font-semibold text-[#1B1B1B]">Check-in</th>
                                    <th class="px-3 sm:px-4 py-2 text-left text-xs font-semibold text-[#1B1B1B]">Check-out</th>
                                    <th class="px-3 sm:px-4 py-2 text-left text-xs font-semibold text-[#1B1B1B]">Status</th>
                                    <th class="px-3 sm:px-4 py-2 text-left text-xs font-semibold text-[#1B1B1B]">Lokasi</th>
                                    <th class="px-3 sm:px-4 py-2 text-left text-xs font-semibold text-[#1B1B1B]">Total Jam</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($last7Days as $day)
                                <tr class="border-b border-gray-200">
                                    <td class="px-3 sm:px-4 py-2 text-xs">{{ $day['tanggal'] }}</td>
                                    <td class="px-3 sm:px-4 py-2 text-xs">{{ $day['check_in'] }}</td>
                                    <td class="px-3 sm:px-4 py-2 text-xs">{{ $day['check_out'] }}</td>
                                    <td class="px-3 sm:px-4 py-2 text-xs">
                                        @if($day['status'] == 'Hadir')
                                            <span class="px-2 py-1 rounded-full text-[10px] font-medium bg-[#2E7D3E] text-white">Hadir</span>
                                        @elseif($day['status'] == 'Izin')
                                            <span class="px-2 py-1 rounded-full text-[10px] font-medium bg-[#FCC626] text-[#1B1B1B]">Izin</span>
                                        @elseif($day['status'] == 'Sakit')
                                            <span class="px-2 py-1 rounded-full text-[10px] font-medium bg-[#00a2e9] text-white">Sakit</span>
                                        @else
                                            <span class="px-2 py-1 rounded-full text-[10px] font-medium bg-[#ec1d1d] text-white">Alpha</span>
                                        @endif
                                    </td>
                                    <td class="px-3 sm:px-4 py-2 text-xs">
                                        @if($day['is_valid'])
                                            <span class="text-[#2E7D3E]">✅ Valid</span>
                                            @if($day['distance'])
                                            <span class="text-[10px] text-gray-500 block">{{ round($day['distance'], 1) }}m</span>
                                            @endif
                                        @else
                                            <span class="text-[#ec1d1d]">❌ Invalid</span>
                                        @endif
                                    </td>
                                    <td class="px-3 sm:px-4 py-2 text-xs">{{ $day['total_jam'] }} jam</td>
                                </tr>
                                @endforeach
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

<!-- QR Code Scanner Library -->
<script src="https://unpkg.com/jsqr@1.4.0/dist/jsQR.js"></script>

<script>
// ==========================================================
// Konfigurasi
// ==========================================================
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
const MAX_RADIUS = 50; // meter
let currentLocation = null;
let isLocationValid = false;
let scanning = false;
let stream = null;
let qrToken = null;

// Selisih waktu server
let serverTimeOffsetMs = 0;
let clockSynced = false;

// ==========================================================
// Jam Realtime
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
    const bulanList = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    dateEl.textContent = `${hariList[now.getDay()]}, ${now.getDate()} ${bulanList[now.getMonth()]} ${now.getFullYear()}`;
}

function fetchServerTime() {
    fetch('{{ route("karyawan.absensi.server-time") }}', {
        headers: { 'Accept': 'application/json' }
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
// Geolokasi
// ==========================================================
function getLocation() {
    const statusText = document.getElementById('locationStatusText');
    const detailText = document.getElementById('locationDetailText');

    statusText.textContent = '⏳ Mendapatkan lokasi...';
    statusText.className = 'font-semibold text-[#FCC626]';
    detailText.textContent = 'Menggunakan GPS...';

    if (!navigator.geolocation) {
        statusText.textContent = '❌ GPS tidak didukung';
        statusText.className = 'font-semibold text-[#ec1d1d]';
        detailText.textContent = 'Browser Anda tidak mendukung GPS';
        return;
    }

    navigator.geolocation.getCurrentPosition(
        function(position) {
            currentLocation = {
                latitude: position.coords.latitude,
                longitude: position.coords.longitude,
                accuracy: position.coords.accuracy
            };

            // Validasi lokasi ke server
            validateLocationWithServer(currentLocation);
        },
        function(error) {
            let msg = 'Gagal mendapatkan lokasi';
            switch(error.code) {
                case error.PERMISSION_DENIED:
                    msg = 'Izin lokasi ditolak. Izinkan akses lokasi di browser Anda.';
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
            isLocationValid = false;
            updateButtons();
        },
        {
            enableHighAccuracy: true,
            timeout: 15000,
            maximumAge: 5000
        }
    );
}

function validateLocationWithServer(location) {
    const statusText = document.getElementById('locationStatusText');
    const detailText = document.getElementById('locationDetailText');

    statusText.textContent = '⏳ Memvalidasi lokasi...';
    statusText.className = 'font-semibold text-[#FCC626]';

    // Validasi lokal dulu (cepat)
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
        statusText.textContent = '✅ Lokasi valid - ' + nearest + ' (' + nearestDist.toFixed(1) + 'm)';
        statusText.className = 'font-semibold text-[#2E7D3E]';
        detailText.textContent = `📌 ${location.latitude.toFixed(6)}, ${location.longitude.toFixed(6)} | Akurasi: ${(location.accuracy || 0).toFixed(1)}m`;
        isLocationValid = true;
    } else {
        statusText.textContent = '❌ Di luar radius kantor (terdekat: ' + nearestDist.toFixed(1) + 'm)';
        statusText.className = 'font-semibold text-[#ec1d1d]';
        detailText.textContent = `📌 ${location.latitude.toFixed(6)}, ${location.longitude.toFixed(6)} | Jarak terdekat: ${nearestDist.toFixed(1)}m dari ${nearest}`;
        isLocationValid = false;
    }

    // Update tampilan jarak di status
    const distanceText = document.getElementById('distanceText');
    if (distanceText) {
        distanceText.textContent = `📏 Jarak terdekat: ${nearestDist.toFixed(1)} meter dari ${nearest}`;
        distanceText.className = `text-xs ${isValid ? 'text-[#2E7D3E]' : 'text-[#ec1d1d]'}`;
    }

    updateButtons();
}

function haversineDistance(lat1, lon1, lat2, lon2) {
    const R = 6371000;
    const dLat = (lat2 - lat1) * Math.PI / 180;
    const dLon = (lon2 - lon1) * Math.PI / 180;
    const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
              Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
              Math.sin(dLon/2) * Math.sin(dLon/2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
    return R * c;
}

// ==========================================================
// QR Code
// ==========================================================
function refreshQrCode() {
    const qrPlaceholder = document.getElementById('qrPlaceholder');
    const qrImage = document.getElementById('qrCodeImage');
    const qrExpiryText = document.getElementById('qrExpiryText');

    qrPlaceholder.classList.remove('hidden');
    qrImage.classList.add('hidden');
    qrExpiryText.textContent = '⏳ Memuat QR Code...';

    fetch('{{ route("karyawan.absensi.generate-qr") }}', {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            qrPlaceholder.classList.add('hidden');
            qrImage.classList.remove('hidden');
            qrImage.src = data.qr_code;
            qrToken = data.token;

            const expires = new Date(data.expires_at);
            qrExpiryText.textContent = `QR Code berlaku hingga ${expires.toLocaleTimeString('id-ID')}`;
            qrExpiryText.className = 'text-xs text-[#2E7D3E]';

            // Auto refresh 30 detik sebelum expired
            const expiresIn = (expires.getTime() - Date.now()) - 30000;
            if (expiresIn > 0) {
                setTimeout(refreshQrCode, expiresIn);
            }
        } else {
            qrExpiryText.textContent = '❌ Gagal generate QR Code';
            qrExpiryText.className = 'text-xs text-[#ec1d1d]';
        }
    })
    .catch(() => {
        qrExpiryText.textContent = '❌ Error server';
        qrExpiryText.className = 'text-xs text-[#ec1d1d]';
    });
}

// ==========================================================
// QR Code Scanner
// ==========================================================
function startScanner() {
    const video = document.getElementById('scannerVideo');
    const canvas = document.getElementById('scannerCanvas');
    const placeholder = document.getElementById('scannerPlaceholder');
    const overlay = document.getElementById('scannerOverlay');
    const scanBtn = document.getElementById('scanBtn');
    const stopBtn = document.getElementById('stopScanBtn');

    if (scanning) return;

    navigator.mediaDevices.getUserMedia({
        video: { facingMode: 'environment' }
    })
    .then(function(mediaStream) {
        stream = mediaStream;
        video.srcObject = stream;
        video.classList.remove('hidden');
        video.play();
        placeholder.classList.add('hidden');
        overlay.classList.remove('hidden');
        scanBtn.classList.add('hidden');
        stopBtn.classList.remove('hidden');
        scanning = true;

        // Start scanning frame
        scanFrame();
    })
    .catch(function(err) {
        Swal.fire({
            icon: 'error',
            title: 'Gagal Buka Kamera',
            text: 'Pastikan Anda memberikan izin akses kamera. Error: ' + err.message,
            confirmButtonColor: '#ec1d1d'
        });
    });
}

function stopScanner() {
    if (stream) {
        stream.getTracks().forEach(track => track.stop());
        stream = null;
    }
    const video = document.getElementById('scannerVideo');
    const placeholder = document.getElementById('scannerPlaceholder');
    const overlay = document.getElementById('scannerOverlay');
    const scanBtn = document.getElementById('scanBtn');
    const stopBtn = document.getElementById('stopScanBtn');

    video.classList.add('hidden');
    placeholder.classList.remove('hidden');
    overlay.classList.add('hidden');
    scanBtn.classList.remove('hidden');
    stopBtn.classList.add('hidden');
    scanning = false;
}

function scanFrame() {
    if (!scanning) return;

    const video = document.getElementById('scannerVideo');
    const canvas = document.getElementById('scannerCanvas');
    const ctx = canvas.getContext('2d');

    if (video.readyState === video.HAVE_ENOUGH_DATA) {
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

        const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
        const code = jsQR(imageData.data, imageData.width, imageData.height, {
            inversionAttempts: "dontInvert",
        });

        if (code && code.data) {
            try {
                const data = JSON.parse(code.data);
                if (data.token) {
                    // QR Code detected
                    stopScanner();
                    processQrCode(data.token);
                    return;
                }
            } catch (e) {
                // Not valid QR data
            }
        }
    }

    requestAnimationFrame(scanFrame);
}

function scanFromFile() {
    document.getElementById('fileInput').click();
}

function handleFileScan(event) {
    const file = event.target.files[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = function(e) {
        const img = new Image();
        img.onload = function() {
            const canvas = document.createElement('canvas');
            canvas.width = img.width;
            canvas.height = img.height;
            const ctx = canvas.getContext('2d');
            ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
            const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
            const code = jsQR(imageData.data, imageData.width, imageData.height, {
                inversionAttempts: "dontInvert",
            });
            if (code && code.data) {
                try {
                    const data = JSON.parse(code.data);
                    if (data.token) {
                        processQrCode(data.token);
                        return;
                    }
                } catch (e) {}
            }
            Swal.fire({
                icon: 'warning',
                title: 'QR Code Tidak Valid',
                text: 'File yang diupload bukan QR Code yang valid.',
                confirmButtonColor: '#FCC626'
            });
        };
        img.src = e.target.result;
    };
    reader.readAsDataURL(file);
}

function processQrCode(token) {
    const resultDiv = document.getElementById('scanResult');
    resultDiv.classList.remove('hidden');
    resultDiv.innerHTML = '⏳ Memproses QR Code...';
    resultDiv.className = 'mt-3 text-sm text-[#FCC626]';

    // Kirim ke server dengan data lokasi
    const action = document.getElementById('btnCheckIn').disabled ? 'checkout' : 'checkin';
    performAbsensi(token, action);
}

// ==========================================================
// Absensi
// ==========================================================
function performAbsensi(token, action) {
    if (!currentLocation) {
        Swal.fire({
            icon: 'warning',
            title: 'Lokasi Tidak Diketahui',
            text: 'Silakan ambil lokasi Anda terlebih dahulu.',
            confirmButtonColor: '#FCC626'
        });
        return;
    }

    const resultDiv = document.getElementById('scanResult');
    resultDiv.innerHTML = '⏳ Memproses absensi...';

    fetch('{{ route("karyawan.absensi.scan-qr") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            qr_token: token,
            latitude: currentLocation.latitude,
            longitude: currentLocation.longitude,
            accuracy: currentLocation.accuracy || 0,
            action: action
        })
    })
    .then(response => response.json().then(data => ({ status: response.status, data })))
    .then(({ status, data }) => {
        if (data.success) {
            if (data.data && data.data.server_timestamp_ms) {
                syncServerTime(data.data.server_timestamp_ms);
            }

            const icon = action === 'checkin' ? '✅' : '✅';
            const title = action === 'checkin' ? 'Check-in Berhasil!' : 'Check-out Berhasil!';

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
                        <p><strong>Lokasi:</strong> ${data.data.location.latitude.toFixed(6)}, ${data.data.location.longitude.toFixed(6)}</p>
                    </div>
                `,
                timer: 5000,
                confirmButtonColor: '#2E7D3E'
            });

            resultDiv.innerHTML = '✅ ' + (action === 'checkin' ? 'Check-in' : 'Check-out') + ' berhasil!';
            resultDiv.className = 'mt-3 text-sm text-[#2E7D3E] font-semibold';

            loadStatus();
            updateButtons();
        } else {
            const isLocationError = status === 403;
            Swal.fire({
                icon: isLocationError ? 'error' : 'warning',
                title: isLocationError ? '🚫 Absensi Ditolak' : 'Absensi Gagal!',
                text: data.message,
                confirmButtonColor: '#ec1d1d'
            });

            resultDiv.innerHTML = '❌ ' + data.message;
            resultDiv.className = 'mt-3 text-sm text-[#ec1d1d]';

            if (isLocationError) {
                getLocation();
            }
        }
    })
    .catch(error => {
        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: 'Terjadi kesalahan pada server',
            confirmButtonColor: '#ec1d1d'
        });
        resultDiv.innerHTML = '❌ Error server';
        resultDiv.className = 'mt-3 text-sm text-[#ec1d1d]';
    });
}

// ==========================================================
// Handle Check-in / Check-out (via button)
// ==========================================================
function handleCheckIn() {
    if (!isLocationValid) {
        Swal.fire({
            icon: 'warning',
            title: 'Lokasi Tidak Valid',
            text: 'Anda harus berada dalam radius 50 meter dari kantor KPM.',
            confirmButtonColor: '#FCC626'
        });
        getLocation();
        return;
    }

    if (!qrToken) {
        Swal.fire({
            icon: 'warning',
            title: 'QR Code Tidak Tersedia',
            text: 'Silakan refresh QR Code terlebih dahulu.',
            confirmButtonColor: '#FCC626'
        });
        refreshQrCode();
        return;
    }

    performAbsensi(qrToken, 'checkin');
}

function handleCheckOut() {
    if (!isLocationValid) {
        Swal.fire({
            icon: 'warning',
            title: 'Lokasi Tidak Valid',
            text: 'Anda harus berada dalam radius 50 meter dari kantor KPM.',
            confirmButtonColor: '#FCC626'
        });
        getLocation();
        return;
    }

    if (!qrToken) {
        Swal.fire({
            icon: 'warning',
            title: 'QR Code Tidak Tersedia',
            text: 'Silakan refresh QR Code terlebih dahulu.',
            confirmButtonColor: '#FCC626'
        });
        refreshQrCode();
        return;
    }

    performAbsensi(qrToken, 'checkout');
}

// ==========================================================
// Update Buttons
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

    if (checkInText !== '-' && checkOutText !== '-') {
        btnCheckIn.disabled = true;
        btnCheckOut.disabled = true;
        btnCheckIn.textContent = '✅ Selesai';
        btnCheckOut.textContent = '✅ Selesai';
    } else if (checkInText !== '-' && checkOutText === '-') {
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
// Load Status Absensi
// ==========================================================
function loadStatus() {
    fetch('{{ route("karyawan.absensi.status") }}', {
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

            // Update QR Code if available
            if (d.qr_code) {
                const qrPlaceholder = document.getElementById('qrPlaceholder');
                const qrImage = document.getElementById('qrCodeImage');
                qrPlaceholder.classList.add('hidden');
                qrImage.classList.remove('hidden');
                qrImage.src = d.qr_code;
                qrToken = d.qr_token;

                const qrExpiryText = document.getElementById('qrExpiryText');
                if (d.qr_expires_at) {
                    const expires = new Date(d.qr_expires_at);
                    qrExpiryText.textContent = `QR Code berlaku hingga ${expires.toLocaleTimeString('id-ID')}`;
                    qrExpiryText.className = 'text-xs text-[#2E7D3E]';
                }
            }

            // Update office locations
            if (d.office_locations) {
                // Already rendered from server
            }

            updateButtons();
        }
    })
    .catch(error => {
        console.error('Error loading status:', error);
    });
}

// ==========================================================
// Initialization
// ==========================================================
document.addEventListener('DOMContentLoaded', function() {
    fetchServerTime();
    getLocation();
    loadStatus();
    refreshQrCode();
});

// Refresh QR Code setiap 5 menit (keamanan)
setInterval(refreshQrCode, 300000);

// Refresh status setiap 30 detik
setInterval(loadStatus, 30000);

// Cek lokasi ulang setiap 60 detik
setInterval(getLocation, 60000);
</script>
@endsection
