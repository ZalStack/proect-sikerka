@extends('layouts.app')

@section('content')
<div class="flex">
    @include('layouts.sidebar')
    <div class="ml-64 flex-1 p-6">
        <div class="mb-6">
            <h1 class="text-2xl font-bold font-['Montserrat'] text-[#161758]">Absensi</h1>
            <p class="text-[#27438D]">Check-in dan Check-out hari ini</p>
        </div>

        <!-- Jam Realtime (tersinkron dengan jam server) -->
        <div class="bg-[#161758] rounded-lg shadow-md p-6 mb-4 text-center">
            <p class="text-sm text-white/70 mb-1">Jam Sekarang (Realtime)</p>
            <p id="liveClock" class="text-4xl font-bold text-white font-mono tracking-widest">--:--:--</p>
            <p id="liveDate" class="text-sm text-white/80 mt-1">Memuat tanggal...</p>
        </div>

        <!-- Status WiFi -->
        <div id="wifiStatusContainer" class="bg-white rounded-lg shadow-md p-4 mb-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <svg class="w-6 h-6 text-[#27438D]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.14 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"></path>
                    </svg>
                    <div>
                        <span id="wifiStatusText" class="font-semibold">🔴 Mengecek koneksi...</span>
                        <p id="wifiDetailText" class="text-xs text-[#1B1B1B] mt-1">IP: -</p>
                    </div>
                </div>
                <button onclick="checkWifiStatus()" class="bg-[#27438D] text-white px-4 py-2 rounded-lg hover:bg-[#161758] transition-colors duration-200 text-sm">
                    🔄 Cek Koneksi
                </button>
            </div>
        </div>

        <!-- Status Absensi Hari Ini -->
        <div id="statusContainer" class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-lg font-semibold text-[#161758] mb-4">Status Absensi Hari Ini</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-[#F5F5F5] rounded-lg p-4">
                    <p class="text-sm text-[#1B1B1B]">Status</p>
                    <p id="statusText" class="text-xl font-bold text-[#161758]">Loading...</p>
                </div>
                <div class="bg-[#F5F5F5] rounded-lg p-4">
                    <p class="text-sm text-[#1B1B1B]">Check-in</p>
                    <p id="checkInText" class="text-xl font-bold text-[#161758]">-</p>
                </div>
                <div class="bg-[#F5F5F5] rounded-lg p-4">
                    <p class="text-sm text-[#1B1B1B]">Check-out</p>
                    <p id="checkOutText" class="text-xl font-bold text-[#161758]">-</p>
                </div>
            </div>
            <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-[#1B1B1B] bg-[#F5F5F5] p-4 rounded-lg">
                <div>
                    <p>📅 Tanggal: <span id="tanggalText" class="font-semibold"></span></p>
                    <p>📆 Hari: <span id="hariText" class="font-semibold"></span></p>
                </div>
                <div>
                    <p>🏢 Kantor: <span id="kantorText" class="font-semibold">-</span></p>
                    <p>⏱️ Total Jam: <span id="totalJamText" class="font-semibold">0</span> jam</p>
                    <p id="wifiValidText" class="text-xs"></p>
                </div>
            </div>
        </div>

        <!-- Tombol Absensi -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-lg font-semibold text-[#161758] mb-4">Absensi Hari Ini</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Tombol Check-in -->
                <button id="btnCheckIn"
                        onclick="handleCheckIn()"
                        class="bg-[#2E7D3E] text-white px-6 py-4 rounded-lg hover:bg-[#009a4b] transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed text-lg font-semibold">
                    🟢 Check-in
                </button>

                <!-- Tombol Check-out -->
                <button id="btnCheckOut"
                        onclick="handleCheckOut()"
                        class="bg-[#ec1d1d] text-white px-6 py-4 rounded-lg hover:bg-red-700 transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed text-lg font-semibold" disabled>
                    🔴 Check-out
                </button>
            </div>

            <div class="mt-4 text-sm text-[#1B1B1B] bg-[#F5F5F5] p-3 rounded-lg">
                <p class="font-semibold">ℹ️ Informasi:</p>
                <ul class="list-disc list-inside mt-1 space-y-1">
                    <li>Absensi <strong>wajib</strong> dilakukan saat perangkat terhubung ke WiFi kantor <strong>"{{ 'KPM' }}"</strong>. Selain WiFi kantor, absensi otomatis ditolak.</li>
                    <li>Check-in hanya 1 kali per hari</li>
                    <li>Check-out hanya setelah check-in</li>
                    <li>Data absensi tidak dapat diubah oleh karyawan</li>
                    <li>Jam yang digunakan adalah jam server (realtime), bukan jam perangkat Anda</li>
                </ul>
            </div>
        </div>

        <!-- Riwayat 7 Hari Terakhir -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-lg font-semibold text-[#161758] mb-4">Riwayat 7 Hari Terakhir</h2>
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

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// ==========================================================
// Konfigurasi umum
// ==========================================================
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

// Status WiFi & absensi
let isWifiValid = false;
let wifiSSID = 'KPM';

// Selisih waktu antara jam server dan jam perangkat (dalam milidetik).
// Dipakai supaya jam yang ditampilkan SELALU mengikuti jam server (realtime),
// bukan jam perangkat karyawan yang bisa saja tidak akurat / sengaja diubah.
let serverTimeOffsetMs = 0;
let clockSynced = false;

// ==========================================================
// Jam Realtime (tersinkron dengan server)
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

// Ambil jam server terbaru untuk sinkronisasi (dipanggil saat load & berkala)
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

// Jam berjalan setiap 1 detik berdasarkan offset waktu server
setInterval(tickClock, 1000);
// Sinkronisasi ulang ke server setiap 30 detik agar tidak drift
setInterval(fetchServerTime, 30000);

// ==========================================================
// Cek Status WiFi via API
// ==========================================================
function checkWifiStatus() {
    const statusText = document.getElementById('wifiStatusText');
    const detailText = document.getElementById('wifiDetailText');
    const container = document.getElementById('wifiStatusContainer');

    const wasValid = isWifiValid;

    statusText.textContent = '⏳ Mengecek koneksi...';
    statusText.className = 'font-semibold text-[#FCC626]';

    fetch('{{ route("karyawan.absensi.check-wifi") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            ssid: wifiSSID,
            mac_address: ''
        })
    })
    .then(response => response.json())
    .then(data => {
        const clientIP = data.ip || '-';
        detailText.textContent = `IP: ${clientIP} | ${data.is_valid_ip ? '✅ Terdeteksi jaringan kantor' : '❌ Bukan jaringan kantor'}`;

        if (data.server_timestamp_ms) {
            syncServerTime(data.server_timestamp_ms);
        }

        if (data.success) {
            statusText.textContent = '🟢 Terhubung ke WiFi Kantor';
            statusText.className = 'font-semibold text-[#2E7D3E]';
            container.className = 'bg-white rounded-lg shadow-md p-4 mb-4 border-2 border-[#2E7D3E]';
            isWifiValid = true;
        } else {
            statusText.textContent = '🔴 Tidak terhubung ke WiFi Kantor';
            statusText.className = 'font-semibold text-[#ec1d1d]';
            container.className = 'bg-white rounded-lg shadow-md p-4 mb-4 border-2 border-[#ec1d1d]';
            isWifiValid = false;

            // Alert saat pertama kali diketahui belum/tidak terhubung ke WiFi kantor
            if (wasValid !== false) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Tidak Terhubung ke WiFi Kantor',
                    text: 'Absensi hanya dapat dilakukan saat perangkat Anda terhubung ke WiFi kantor "' + wifiSSID + '". Silakan periksa koneksi WiFi Anda.',
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#ec1d1d'
                });
            }
        }

        // Alert kecil (toast) jika koneksi kantor baru saja terputus setelah sebelumnya valid
        if (wasValid === true && !data.success) {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'error',
                title: 'Koneksi WiFi kantor terputus!',
                showConfirmButton: false,
                timer: 3500
            });
        }

        updateButtons();
    })
    .catch(error => {
        statusText.textContent = '❌ Error mengecek koneksi';
        statusText.className = 'font-semibold text-[#ec1d1d]';
        detailText.textContent = 'Terjadi kesalahan pada server';
        isWifiValid = false;
        updateButtons();
    });
}

// ==========================================================
// Update tombol berdasarkan status WiFi dan absensi
// ==========================================================
function updateButtons() {
    const btnCheckIn = document.getElementById('btnCheckIn');
    const btnCheckOut = document.getElementById('btnCheckOut');
    const checkInText = document.getElementById('checkInText').textContent;
    const checkOutText = document.getElementById('checkOutText').textContent;

    if (!isWifiValid) {
        btnCheckIn.disabled = true;
        btnCheckOut.disabled = true;
        btnCheckIn.title = 'Harus terhubung ke WiFi kantor "' + wifiSSID + '"';
        btnCheckOut.title = 'Harus terhubung ke WiFi kantor "' + wifiSSID + '"';
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

            const wifiValid = document.getElementById('wifiValidText');
            if (d.is_valid_wifi) {
                wifiValid.textContent = '✅ Terverifikasi WiFi Kantor (IP: ' + (d.ip_address || '-') + ')';
                wifiValid.className = 'text-xs text-[#2E7D3E] font-semibold';
            } else {
                wifiValid.textContent = '⚠️ Belum terverifikasi WiFi';
                wifiValid.className = 'text-xs text-[#ec1d1d] font-semibold';
            }

            updateButtons();
        }
    })
    .catch(error => {
        console.error('Error loading status:', error);
    });
}

// ==========================================================
// Handle Check-in
// ==========================================================
function handleCheckIn() {
    if (!isWifiValid) {
        Swal.fire({
            icon: 'warning',
            title: 'Tidak Terhubung ke WiFi Kantor',
            text: 'Absensi hanya dapat dilakukan saat perangkat Anda terhubung ke WiFi kantor "' + wifiSSID + '".',
            confirmButtonText: 'OK',
            confirmButtonColor: '#ec1d1d'
        });
        return;
    }

    const btn = document.getElementById('btnCheckIn');
    btn.disabled = true;
    btn.textContent = '⏳ Memproses...';

    fetch('{{ route("karyawan.absensi.checkin") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            ssid: wifiSSID,
            bssid: ''
        })
    })
    .then(response => response.json().then(data => ({ status: response.status, data })))
    .then(({ status, data }) => {
        if (data.success) {
            if (data.data && data.data.server_timestamp_ms) {
                syncServerTime(data.data.server_timestamp_ms);
            }
            Swal.fire({
                icon: 'success',
                title: '✅ Check-in Berhasil!',
                html: `
                    <div class="text-left">
                        <p><strong>Waktu:</strong> ${data.data.waktu}</p>
                        <p><strong>Tanggal:</strong> ${data.data.tanggal}</p>
                        <p><strong>Kantor:</strong> ${data.data.kantor}</p>
                        <p><strong>IP:</strong> ${data.data.ip}</p>
                        <p><strong>Status:</strong> ${data.data.status}</p>
                    </div>
                `,
                timer: 4000,
                showConfirmButton: true,
                confirmButtonColor: '#2E7D3E'
            });
            loadStatus();
        } else {
            const isWifiRejected = status === 403;
            Swal.fire({
                icon: isWifiRejected ? 'error' : 'warning',
                title: isWifiRejected ? '🚫 Absensi Ditolak' : 'Check-in Gagal!',
                text: data.message,
                confirmButtonColor: '#ec1d1d'
            });
            if (isWifiRejected) {
                // Refresh status koneksi karena ternyata sudah tidak valid di server
                isWifiValid = false;
                checkWifiStatus();
            }
            btn.disabled = false;
            btn.textContent = '🟢 Check-in';
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
        btn.textContent = '🟢 Check-in';
    });
}

// ==========================================================
// Handle Check-out
// ==========================================================
function handleCheckOut() {
    if (!isWifiValid) {
        Swal.fire({
            icon: 'warning',
            title: 'Tidak Terhubung ke WiFi Kantor',
            text: 'Absensi hanya dapat dilakukan saat perangkat Anda terhubung ke WiFi kantor "' + wifiSSID + '".',
            confirmButtonText: 'OK',
            confirmButtonColor: '#ec1d1d'
        });
        return;
    }

    const btn = document.getElementById('btnCheckOut');
    btn.disabled = true;
    btn.textContent = '⏳ Memproses...';

    fetch('{{ route("karyawan.absensi.checkout") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            ssid: wifiSSID,
            bssid: ''
        })
    })
    .then(response => response.json().then(data => ({ status: response.status, data })))
    .then(({ status, data }) => {
        if (data.success) {
            if (data.data && data.data.server_timestamp_ms) {
                syncServerTime(data.data.server_timestamp_ms);
            }
            Swal.fire({
                icon: 'success',
                title: '✅ Check-out Berhasil!',
                html: `
                    <div class="text-left">
                        <p><strong>Waktu:</strong> ${data.data.waktu}</p>
                        <p><strong>Tanggal:</strong> ${data.data.tanggal}</p>
                        <p><strong>Kantor:</strong> ${data.data.kantor}</p>
                        <p><strong>Total Jam:</strong> ${data.data.total_jam} jam</p>
                        <p><strong>IP:</strong> ${data.data.ip}</p>
                    </div>
                `,
                timer: 4000,
                showConfirmButton: true,
                confirmButtonColor: '#ec1d1d'
            });
            loadStatus();
        } else {
            const isWifiRejected = status === 403;
            Swal.fire({
                icon: isWifiRejected ? 'error' : 'warning',
                title: isWifiRejected ? '🚫 Absensi Ditolak' : 'Check-out Gagal!',
                text: data.message,
                confirmButtonColor: '#ec1d1d'
            });
            if (isWifiRejected) {
                isWifiValid = false;
                checkWifiStatus();
            }
            btn.disabled = false;
            btn.textContent = '🔴 Check-out';
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
        btn.textContent = '🔴 Check-out';
    });
}

// ==========================================================
// Interval pengecekan berkala
// ==========================================================
// Cek WiFi setiap 10 detik (mendeteksi jika karyawan pindah/putus dari WiFi kantor)
setInterval(checkWifiStatus, 10000);
// Load status absensi setiap 30 detik
setInterval(loadStatus, 30000);

// Inisialisasi saat halaman dibuka
document.addEventListener('DOMContentLoaded', function () {
    fetchServerTime();
    checkWifiStatus();
    loadStatus();
});
</script>
@endsection
