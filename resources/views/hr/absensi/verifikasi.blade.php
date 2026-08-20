{{-- views/hr/absensi/verifikasi.blade.php --}}
@extends('layouts.app')

@section('content')
    <div class="flex min-h-screen bg-gray-50">
        @include('layouts.sidebar')
        <div class="flex-1 transition-all duration-300 md:ml-64">
            <div class="p-4 sm:p-6 lg:p-8">
                <!-- Header -->
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
                    <div>
                        <h1 class="text-2xl sm:text-3xl font-bold text-[#161758] tracking-tight">Verifikasi Absen</h1>
                        <p class="text-sm text-gray-500 mt-1">Input manual check-in / check-out karyawan oleh HR</p>
                    </div>
                    <a href="{{ route('hr.absensi.index') }}"
                        class="inline-flex items-center justify-center px-5 py-2.5 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 transition-all duration-200 text-sm font-medium shadow-sm gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        <span>Kembali</span>
                    </a>
                </div>

                <!-- GPS Status Card -->
                <div id="gpsCard"
                    class="bg-white rounded-2xl shadow-sm p-5 sm:p-6 mb-6 border-2 border-gray-200">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="flex items-center gap-3">
                            <div id="gpsIcon"
                                class="w-10 h-10 sm:w-12 sm:h-12 rounded-full bg-yellow-100 flex items-center justify-center">
                                <svg class="w-5 h-5 sm:w-6 sm:h-6 text-yellow-500" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </div>
                            <div>
                                <p id="gpsStatusText" class="text-sm font-semibold text-yellow-600">Mendeteksi lokasi
                                    device HR...</p>
                                <p id="gpsDetailText" class="text-xs text-gray-500 mt-0.5">GPS diperlukan untuk verifikasi
                                    absen</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <button onclick="getHrLocation(true)" id="btnRefreshGps"
                                class="inline-flex items-center gap-1.5 px-4 py-2 bg-[#27438D] text-white text-sm rounded-xl hover:bg-[#161758] transition-all duration-200 font-medium">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                                Refresh GPS
                            </button>
                        </div>
                    </div>
                    <div class="mt-3 grid grid-cols-2 sm:grid-cols-4 gap-2 text-xs">
                        <div class="bg-gray-50 rounded-lg p-2">
                            <span class="text-gray-400">Latitude</span>
                            <p id="gpsLat" class="font-semibold text-gray-700">-</p>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-2">
                            <span class="text-gray-400">Longitude</span>
                            <p id="gpsLng" class="font-semibold text-gray-700">-</p>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-2">
                            <span class="text-gray-400">Akurasi</span>
                            <p id="gpsAccuracy" class="font-semibold text-gray-700">-</p>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-2">
                            <span class="text-gray-400">Status GPS</span>
                            <p id="gpsValidStatus" class="font-semibold text-yellow-600">Menunggu...</p>
                        </div>
                    </div>
                </div>

                <!-- Stats Cards -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4 mb-6">
                    <div class="bg-white rounded-2xl shadow-sm p-4 border-l-4 border-[#161758]">
                        <p class="text-xs font-medium text-gray-400 uppercase tracking-wider">Total Karyawan</p>
                        <p class="text-xl font-bold text-[#161758] mt-1">{{ $stats['total'] }}</p>
                    </div>
                    <div class="bg-white rounded-2xl shadow-sm p-4 border-l-4 border-[#2E7D3E]">
                        <p class="text-xs font-medium text-gray-400 uppercase tracking-wider">Sudah Check-in</p>
                        <p class="text-xl font-bold text-[#2E7D3E] mt-1">{{ $stats['sudah_checkin'] }}</p>
                    </div>
                    <div class="bg-white rounded-2xl shadow-sm p-4 border-l-4 border-[#00a2e9]">
                        <p class="text-xs font-medium text-gray-400 uppercase tracking-wider">Sudah Check-out</p>
                        <p class="text-xl font-bold text-[#00a2e9] mt-1">{{ $stats['sudah_checkout'] }}</p>
                    </div>
                    <div class="bg-white rounded-2xl shadow-sm p-4 border-l-4 border-[#ec1d1d]">
                        <p class="text-xs font-medium text-gray-400 uppercase tracking-wider">Belum Absen</p>
                        <p class="text-xl font-bold text-[#ec1d1d] mt-1">{{ $stats['belum_absen'] }}</p>
                    </div>
                </div>

                <!-- Filter & Tanggal -->
                <div class="bg-white rounded-2xl shadow-sm p-5 sm:p-6 mb-6">
                    <form action="{{ route('hr.absensi.verifikasi') }}" method="GET"
                        class="flex flex-col sm:flex-row gap-3 sm:items-end">
                        <div class="flex-1 sm:max-w-xs">
                            <label
                                class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Tanggal
                                Absensi</label>
                            <input type="date" name="tanggal"
                                value="{{ $selectedDate->format('Y-m-d') }}"
                                class="w-full px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-[#00a2e9] focus:border-transparent transition">
                        </div>
                        <div class="flex-1 sm:max-w-xs">
                            <label
                                class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Cari
                                Karyawan</label>
                            <input type="text" id="searchEmployee" placeholder="Nama / NIP / Jabatan..."
                                class="w-full px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-[#00a2e9] focus:border-transparent transition">
                        </div>
                        <div class="flex gap-2">
                            <button type="submit"
                                class="px-5 py-2.5 bg-[#00a2e9] text-white rounded-xl hover:bg-[#0088c4] transition-all duration-200 text-sm font-medium shadow-sm">
                                Filter
                            </button>
                            <a href="{{ route('hr.absensi.verifikasi') }}"
                                class="px-5 py-2.5 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 transition-all duration-200 text-sm font-medium">
                                Reset
                            </a>
                        </div>
                    </form>
                </div>

                <!-- Alert box -->
                <div id="alertBox" class="hidden mb-4 rounded-2xl p-4 text-sm font-medium"></div>

                <!-- Table -->
                <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-100" id="employeeTable">
                            <thead class="bg-gray-50/80">
                                <tr>
                                    <th scope="col"
                                        class="px-4 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                        Karyawan</th>
                                    <th scope="col"
                                        class="px-4 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider hidden sm:table-cell">
                                        Jabatan / Divisi</th>
                                    <th scope="col"
                                        class="px-4 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                        Status</th>
                                    <th scope="col"
                                        class="px-4 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider hidden md:table-cell">
                                        Check-in</th>
                                    <th scope="col"
                                        class="px-4 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider hidden md:table-cell">
                                        Check-out</th>
                                    <th scope="col"
                                        class="px-4 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider hidden lg:table-cell">
                                        Lokasi</th>
                                    <th scope="col"
                                        class="px-4 py-3.5 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                        Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100" id="employeeBody">
                                @forelse($employeesData as $emp)
                                    <tr class="hover:bg-gray-50/70 transition-colors employee-row"
                                        data-search="{{ strtolower($emp['nama'] . ' ' . $emp['kode_pegawai'] . ' ' . $emp['jabatan'] . ' ' . ($emp['divisi'] ?? '')) }}">
                                        <td class="px-4 py-3.5">
                                            <div>
                                                <p class="text-sm font-medium text-gray-900">{{ $emp['nama'] }}</p>
                                                <p class="text-xs text-gray-500">{{ $emp['kode_pegawai'] }}</p>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3.5 hidden sm:table-cell">
                                            <p class="text-sm text-gray-700">{{ $emp['jabatan'] }}</p>
                                            <p class="text-xs text-gray-500">{{ $emp['divisi'] ?? '-' }}</p>
                                        </td>
                                        <td class="px-4 py-3.5">
                                            @php
                                                $statusClass = match ($emp['status']) {
                                                    'Hadir' => 'bg-[#2E7D3E] text-white',
                                                    'Izin' => 'bg-[#FCC626] text-[#1B1B1B]',
                                                    'Sakit' => 'bg-[#00a2e9] text-white',
                                                    'Alpha' => 'bg-[#ec1d1d] text-white',
                                                    'Perjalanan Dinas' => 'bg-purple-600 text-white',
                                                    'Cuti' => 'bg-[#27438D] text-white',
                                                    default => 'bg-gray-200 text-gray-800',
                                                };
                                            @endphp
                                            <span
                                                class="inline-block px-2.5 py-1 rounded-full text-xs font-semibold {{ $statusClass }}">
                                                {{ $emp['status'] }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3.5 hidden md:table-cell">
                                            @if ($emp['check_in'])
                                                <span class="text-sm font-semibold text-[#2E7D3E]">{{ $emp['check_in'] }}</span>
                                            @else
                                                <span class="text-sm text-gray-400">-</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3.5 hidden md:table-cell">
                                            @if ($emp['check_out'])
                                                <span class="text-sm font-semibold text-[#00a2e9]">{{ $emp['check_out'] }}</span>
                                            @else
                                                <span class="text-sm text-gray-400">-</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3.5 hidden lg:table-cell">
                                            <span class="text-xs text-gray-600">{{ $emp['kantor_cabang'] }}</span>
                                        </td>
                                        <td class="px-4 py-3.5 text-center">
                                            <div class="flex items-center justify-center gap-1.5">
                                                @if (!$emp['check_in'])
                                                    <button type="button"
                                                        onclick="handleVerifikasi({{ $emp['id'] }}, 'checkin', '{{ addslashes($emp['nama']) }}')"
                                                        class="inline-flex items-center gap-1 px-3 py-1.5 bg-[#2E7D3E] text-white text-xs font-semibold rounded-lg hover:bg-[#256b34] transition-all duration-200 shadow-sm verify-btn">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                                        </svg>
                                                        Check-in
                                                    </button>
                                                @endif
                                                @if ($emp['check_in'] && !$emp['check_out'])
                                                    <button type="button"
                                                        onclick="handleVerifikasi({{ $emp['id'] }}, 'checkout', '{{ addslashes($emp['nama']) }}')"
                                                        class="inline-flex items-center gap-1 px-3 py-1.5 bg-[#00a2e9] text-white text-xs font-semibold rounded-lg hover:bg-[#0088c4] transition-all duration-200 shadow-sm verify-btn">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                                        </svg>
                                                        Check-out
                                                    </button>
                                                @endif
                                                @if ($emp['check_in'] && $emp['check_out'])
                                                    <span class="inline-flex items-center gap-1 px-3 py-1.5 bg-gray-100 text-gray-500 text-xs font-medium rounded-lg">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2" d="M5 13l4 4L19 7" />
                                                        </svg>
                                                        Selesai
                                                    </span>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-4 py-12 text-center">
                                            <div class="flex flex-col items-center justify-center text-gray-400">
                                                <svg class="w-12 h-12 mb-2" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="1.5"
                                                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                                                </svg>
                                                <p class="text-sm font-medium">Tidak ada data karyawan.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination info -->
                    <div class="px-4 py-3 border-t border-gray-100">
                        <p class="text-xs text-gray-400 text-center sm:text-left">
                            Menampilkan {{ $employeesData->count() }} karyawan
                            @if ($selectedDate->isToday())
                                &middot; Hari ini {{ $selectedDate->format('d/m/Y') }}
                            @else
                                &middot; Tanggal {{ $selectedDate->format('d/m/Y') }}
                            @endif
                        </p>
                    </div>
                </div>

                <!-- Keterangan -->
                <div class="mt-6 bg-white rounded-2xl shadow-sm p-5 sm:p-6">
                    <h3 class="text-sm font-semibold text-[#161758] mb-2">Keterangan</h3>
                    <ul class="text-xs text-gray-500 space-y-1">
                        <li>• GPS device HR akan digunakan sebagai lokasi absensi karyawan.</li>
                        <li>• Verifikasi check-in hanya bisa dilakukan jika karyawan belum memiliki data check-in.</li>
                        <li>• Verifikasi check-out hanya bisa dilakukan setelah karyawan di-check-in.</li>
                        <li>• Data verifikasi akan langsung muncul di halaman absensi karyawan dan HRD.</li>
                        <li>• Catatan verifikasi otomatis ditambahkan ke keterangan absensi.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        let hrLocation = null;

        // ==========================================================
        // GPS LOCATION - HR Device
        // ==========================================================
        function getHrLocation(force = false) {
            const statusText = document.getElementById('gpsStatusText');
            const detailText = document.getElementById('gpsDetailText');
            const latEl = document.getElementById('gpsLat');
            const lngEl = document.getElementById('gpsLng');
            const accEl = document.getElementById('gpsAccuracy');
            const validEl = document.getElementById('gpsValidStatus');
            const card = document.getElementById('gpsCard');
            const icon = document.getElementById('gpsIcon');

            statusText.textContent = 'Mendeteksi lokasi device HR...';
            statusText.className = 'text-sm font-semibold text-yellow-600';
            icon.className = 'w-10 h-10 sm:w-12 sm:h-12 rounded-full bg-yellow-100 flex items-center justify-center';

            if (!navigator.geolocation) {
                statusText.textContent = 'GPS tidak didukung oleh browser ini';
                statusText.className = 'text-sm font-semibold text-red-600';
                detailText.textContent = 'Aktifkan akses lokasi di browser';
                icon.className = 'w-10 h-10 sm:w-12 sm:h-12 rounded-full bg-red-100 flex items-center justify-center';
                validEl.textContent = 'Tidak tersedia';
                validEl.className = 'font-semibold text-red-600';
                card.className = 'bg-white rounded-2xl shadow-sm p-5 sm:p-6 mb-6 border-2 border-red-300';
                return;
            }

            navigator.geolocation.getCurrentPosition(
                function(position) {
                    hrLocation = {
                        latitude: position.coords.latitude,
                        longitude: position.coords.longitude,
                        accuracy: position.coords.accuracy
                    };

                    latEl.textContent = hrLocation.latitude.toFixed(6);
                    lngEl.textContent = hrLocation.longitude.toFixed(6);
                    accEl.textContent = '± ' + (hrLocation.accuracy || 0).toFixed(1) + ' m';
                    validEl.textContent = 'Siap digunakan';
                    validEl.className = 'font-semibold text-[#2E7D3E]';
                    statusText.textContent = 'GPS Aktif - Siap untuk verifikasi';
                    statusText.className = 'text-sm font-semibold text-[#2E7D3E]';
                    detailText.textContent = 'Lokasi device HR akan digunakan sebagai lokasi absensi karyawan';
                    icon.className = 'w-10 h-10 sm:w-12 sm:h-12 rounded-full bg-green-100 flex items-center justify-center';
                    card.className = 'bg-white rounded-2xl shadow-sm p-5 sm:p-6 mb-6 border-2 border-[#2E7D3E]';

                    enableAllButtons();
                },
                function(error) {
                    let msg = 'Gagal mendapatkan lokasi';
                    switch (error.code) {
                        case error.PERMISSION_DENIED:
                            msg = 'Izin lokasi ditolak. Aktifkan izin lokasi di browser.';
                            break;
                        case error.POSITION_UNAVAILABLE:
                            msg = 'Informasi lokasi tidak tersedia.';
                            break;
                        case error.TIMEOUT:
                            msg = 'Waktu pengambilan lokasi habis.';
                            break;
                    }
                    statusText.textContent = 'GPS Error';
                    statusText.className = 'text-sm font-semibold text-red-600';
                    detailText.textContent = msg;
                    icon.className = 'w-10 h-10 sm:w-12 sm:h-12 rounded-full bg-red-100 flex items-center justify-center';
                    validEl.textContent = 'Error';
                    validEl.className = 'font-semibold text-red-600';
                    card.className = 'bg-white rounded-2xl shadow-sm p-5 sm:p-6 mb-6 border-2 border-red-300';
                    disableAllButtons();
                }, {
                    enableHighAccuracy: true,
                    timeout: 15000,
                    maximumAge: 5000
                }
            );
        }

        function enableAllButtons() {
            document.querySelectorAll('.verify-btn').forEach(btn => {
                btn.disabled = false;
                btn.style.opacity = '1';
            });
        }

        function disableAllButtons() {
            document.querySelectorAll('.verify-btn').forEach(btn => {
                btn.disabled = true;
                btn.style.opacity = '0.5';
            });
        }

        // ==========================================================
        // VERIFIKASI ABSEN
        // ==========================================================
        function handleVerifikasi(karyawanId, type, nama) {
            if (!hrLocation) {
                Swal.fire({
                    icon: 'warning',
                    title: 'GPS Belum Aktif',
                    text: 'Pastikan GPS device HR sudah aktif. Tekan tombol "Refresh GPS" untuk mendeteksi lokasi.',
                    confirmButtonColor: '#FCC626'
                });
                return;
            }

            const typeLabel = type === 'checkin' ? 'Check-in' : 'Check-out';
            const confirmColor = type === 'checkin' ? '#2E7D3E' : '#00a2e9';

            Swal.fire({
                title: 'Verifikasi ' + typeLabel + '?',
                html: '<div class="text-left">' +
                    '<p>Apakah Anda yakin ingin melakukan verifikasi <strong>' + typeLabel + '</strong> untuk:</p>' +
                    '<p class="mt-2 text-base font-semibold text-[#161758]">' + nama + '</p>' +
                    '<p class="text-xs text-gray-500 mt-2">Lokasi: ' + hrLocation.latitude.toFixed(6) + ', ' + hrLocation.longitude.toFixed(6) + '</p>' +
                    '</div>',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: confirmColor,
                cancelButtonColor: '#6B7280',
                confirmButtonText: 'Ya, Verifikasi ' + typeLabel,
                cancelButtonText: 'Batal',
            }).then((result) => {
                if (result.isConfirmed) {
                    performVerifikasi(karyawanId, type, nama);
                }
            });
        }

        function performVerifikasi(karyawanId, type, nama) {
            const tanggal = '{{ $selectedDate->format("Y-m-d") }}';
            const typeLabel = type === 'checkin' ? 'Check-in' : 'Check-out';
            const confirmColor = type === 'checkin' ? '#2E7D3E' : '#00a2e9';

            // Show loading
            Swal.fire({
                title: 'Memproses...',
                html: 'Sedang memverifikasi ' + typeLabel + ' untuk ' + nama,
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            fetch('{{ route("hr.absensi.verifikasi.store") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        karyawan_id: karyawanId,
                        type: type,
                        latitude: hrLocation.latitude,
                        longitude: hrLocation.longitude,
                        tanggal: tanggal
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
                        Swal.fire({
                            icon: 'success',
                            title: typeLabel + ' Berhasil!',
                            text: data.message,
                            timer: 2500,
                            confirmButtonColor: confirmColor,
                        }).then(() => {
                            window.location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal!',
                            text: data.message,
                            confirmButtonColor: '#ec1d1d'
                        });
                    }
                })
                .catch(error => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Terjadi kesalahan pada server. Silakan coba lagi.',
                        confirmButtonColor: '#ec1d1d'
                    });
                });
        }

        // ==========================================================
        // SEARCH EMPLOYEE
        // ==========================================================
        document.getElementById('searchEmployee').addEventListener('input', function() {
            const query = this.value.toLowerCase();
            const rows = document.querySelectorAll('.employee-row');
            rows.forEach(row => {
                const search = row.getAttribute('data-search');
                if (search.includes(query)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });

        // ==========================================================
        // INIT
        // ==========================================================
        document.addEventListener('DOMContentLoaded', function() {
            getHrLocation();
        });
    </script>
@endsection