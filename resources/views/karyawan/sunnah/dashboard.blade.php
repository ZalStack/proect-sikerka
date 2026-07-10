@extends('layouts.app')

@section('content')
<div class="flex">
    @include('layouts.sidebar')
    <div class="ml-64 flex-1 p-6">
        <div class="mb-6">
            <h1 class="text-2xl font-bold font-['Montserrat'] text-[#161758]">7SPS - 7 Sunnah Plus Suprasional</h1>
            <p class="text-[#27438D]">Kelola kegiatan sunnah harian Anda</p>
        </div>

        @if(session('success'))
            <div class="bg-[#2E7D3E] text-white p-4 rounded-lg mb-4">
                {{ session('success') }}
            </div>
        @endif

        <!-- Statistik -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow-md p-4 text-center">
                <p id="stat-total-hari" class="text-2xl font-bold text-[#161758]">{{ $statistik['total_hari'] }}</p>
                <p class="text-sm text-[#1B1B1B]">Total Hari</p>
            </div>
            <div class="bg-[#2E7D3E] text-white rounded-lg shadow-md p-4 text-center">
                <p id="stat-total-poin" class="text-2xl font-bold">{{ $statistik['total_poin'] }}</p>
                <p class="text-sm">Total Poin</p>
            </div>
            <div class="bg-[#FCC626] text-[#1B1B1B] rounded-lg shadow-md p-4 text-center">
                <p id="stat-rata-rata" class="text-2xl font-bold">{{ $statistik['rata_rata'] }}</p>
                <p class="text-sm">Rata-rata Poin</p>
            </div>
            <div class="bg-[#00a2e9] text-white rounded-lg shadow-md p-4 text-center">
                <p id="stat-tertinggi" class="text-2xl font-bold">{{ $statistik['tertinggi'] }}</p>
                <p class="text-sm">Poin Tertinggi</p>
            </div>
        </div>

        <!-- Status Hari Ini -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <div class="flex justify-between items-center">
                <div>
                    <h2 id="current-date-display" class="text-lg font-semibold text-[#161758]">
                        📅 {{ Carbon\Carbon::now()->locale('id')->isoFormat('dddd, D MMMM YYYY') }}
                    </h2>
                </div>
                <div>
                    <div id="today-status-empty" class="{{ $todayData ? 'hidden' : '' }}">
                        <span class="px-4 py-2 rounded-full text-sm font-medium bg-[#FCC626] text-[#1B1B1B]">
                            ⏳ Belum Mengisi
                        </span>
                    </div>
                    <div id="today-status-filled" class="{{ $todayData ? '' : 'hidden' }}">
                        <span id="today-poin-badge" class="px-4 py-2 rounded-full text-sm font-medium bg-[#2E7D3E] text-white">
                            ✅ Poin: {{ $todayData->total_poin ?? 0 }}
                        </span>
                        <span id="today-approval-badge" class="ml-2 px-3 py-1 rounded-full text-xs font-medium {{ $todayData->status_badge ?? '' }}">
                            {{ $todayData->status_label ?? '' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Grid 7SPS -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 mb-6">
            @foreach($poinConfig as $key => $config)
                <div class="bg-white rounded-lg shadow-md p-4 hover:shadow-lg transition-shadow duration-200 cursor-pointer"
                     onclick="openModal('{{ $key }}')">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <span class="text-2xl">{{ $config['icon'] }}</span>
                            <div>
                                <h3 class="font-semibold text-[#161758] text-sm">{{ $config['label'] }}</h3>
                                <p class="text-xs text-[#27438D]">
                                    @if(($config['has_jamaah'] ?? false))
                                        {{ $config['poin'] }} / {{ $config['poin_jamaah'] ?? $config['poin'] * 4 }} poin
                                        <span class="text-[10px]">(sendiri/jamaah)</span>
                                    @else
                                        {{ $config['poin'] }} poin
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="text-right" id="card-check-{{ $key }}">
                            @if($todayData && $todayData->$key)
                                <span class="text-[#2E7D3E]">✅</span>
                                @if(($config['has_jamaah'] ?? false) && $todayData->{$key . '_berjamaah'})
                                    <span class="text-xs text-[#27438D] block">🕌</span>
                                @endif
                            @else
                                <span class="text-gray-300">⬜</span>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Riwayat 30 Hari -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-lg font-semibold text-[#161758] mb-4">📊 Riwayat 30 Hari Terakhir</h2>
            <div class="overflow-x-auto">
                <div class="grid grid-cols-5 md:grid-cols-10 lg:grid-cols-15 gap-2">
                    @foreach($last30Days as $day)
                        <div class="text-center" id="day-{{ $day['iso'] }}">
                            <div class="text-xs text-gray-500">{{ $day['tanggal'] }}</div>
                            <div id="bar-{{ $day['iso'] }}"
                                 class="mt-1 w-full rounded {{ $day['poin'] > 0 ? 'bg-[#2E7D3E]' : 'bg-gray-200' }}"
                                 style="height: {{ $day['poin'] > 0 ? min($day['poin'] / 2, 32) : 8 }}px;">
                            </div>
                            <div class="text-xs font-semibold mt-1" id="poin-{{ $day['iso'] }}">{{ $day['poin'] }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal untuk Setiap Kegiatan -->
@foreach($poinConfig as $key => $config)
<div id="modal-{{ $key }}" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-md w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-bold text-[#161758]">
                    {{ $config['icon'] }} {{ $config['label'] }}
                </h3>
                <button onclick="closeModal('{{ $key }}')" class="text-gray-500 hover:text-[#ec1d1d] text-2xl">
                    &times;
                </button>
            </div>

            <div class="border-t border-gray-200 pt-4">
                <p class="text-sm text-[#1B1B1B] mb-4">
                    Centang kegiatan yang sudah Anda laksanakan hari ini:
                </p>

                <form id="form-{{ $key }}" onsubmit="saveChecklist(event, '{{ $key }}')">
                    @csrf
                    <input type="hidden" name="field_name" value="{{ $key }}">

                    <!-- Checklist Utama -->
                    <div class="flex items-center space-x-3 p-3 bg-[#F5F5F5] rounded-lg mb-3">
                        <input type="checkbox"
                               name="{{ $key }}"
                               id="check-{{ $key }}"
                               class="w-5 h-5 text-[#2E7D3E] rounded focus:ring-2 focus:ring-[#2E7D3E]"
                               {{ ($todayData && $todayData->$key) ? 'checked' : '' }}
                               {{ ($todayData && $todayData->status_approval === 'approved') ? 'disabled' : '' }}>
                        <label for="check-{{ $key }}" class="text-sm text-[#1B1B1B] font-medium">
                            {{ $config['label'] }}
                            @if(($config['has_jamaah'] ?? false))
                                <span class="text-xs text-[#27438D]">(dengan berjamaah: {{ $config['poin_jamaah'] ?? $config['poin'] * 4 }} poin)</span>
                            @else
                                <span class="text-xs text-[#27438D]">({{ $config['poin'] }} poin)</span>
                            @endif
                        </label>
                    </div>

                    <!-- Opsi Berjamaah untuk Sholat Wajib -->
                    @if($config['has_jamaah'] ?? false)
                        <div class="flex items-center space-x-3 p-3 bg-blue-50 rounded-lg mb-3 border border-blue-200">
                            <input type="checkbox"
                                   name="{{ $key }}_berjamaah"
                                   id="check-{{ $key }}-jamaah"
                                   class="w-5 h-5 text-[#27438D] rounded focus:ring-2 focus:ring-[#27438D]"
                                   {{ ($todayData && $todayData->{$key . '_berjamaah'}) ? 'checked' : '' }}
                                   {{ ($todayData && $todayData->status_approval === 'approved') ? 'disabled' : '' }}>
                            <label for="check-{{ $key }}-jamaah" class="text-sm text-[#1B1B1B]">
                                <span class="font-medium">🕌 Berjamaah</span>
                                <span class="text-xs text-[#27438D]">(+{{ ($config['poin_jamaah'] ?? $config['poin'] * 4) - $config['poin'] }} poin, total {{ $config['poin_jamaah'] ?? $config['poin'] * 4 }} poin)</span>
                            </label>
                        </div>
                    @endif

                    <!-- Informasi Tambahan -->
                    @if($key === 'sholat_tahajud')
                        <div class="text-xs text-gray-500 mb-3 p-2 bg-blue-50 rounded-lg">
                            📌 Dilaksanakan minimal 4 rakaat
                        </div>
                    @endif

                    @if($key === 'tilawah_quran')
                        <div class="text-xs text-gray-500 mb-3 p-2 bg-blue-50 rounded-lg">
                            📌 Minimal 1 Juz per pekan
                        </div>
                    @endif

                    @if($key === 'puasa_sunnah')
                        <div class="text-xs text-gray-500 mb-3 p-2 bg-blue-50 rounded-lg">
                            📌 Puasa Senin-Kamis, Ayyamul Bidh, dll
                        </div>
                    @endif

                    @if($key === 'infaq_sedekah')
                        <div class="text-xs text-gray-500 mb-3 p-2 bg-blue-50 rounded-lg">
                            📌 Minimal Rp 1.000 atau setara
                        </div>
                    @endif

                    @if($key === 'dzikir_sholawat')
                        <div class="text-xs text-gray-500 mb-3 p-2 bg-blue-50 rounded-lg">
                            📌 Minimal 10x dzikir/sholawat
                        </div>
                    @endif

                    @if($key === 'sholat_dhuha')
                        <div class="text-xs text-gray-500 mb-3 p-2 bg-blue-50 rounded-lg">
                            📌 Minimal 4 rakaat
                        </div>
                    @endif

                    <div class="flex items-center justify-between mt-4 pt-4 border-t border-gray-200">
                        <span class="text-sm text-[#1B1B1B]">
                            Poin item ini:
                            <strong id="poin-preview-{{ $key }}">
                                @php
                                    $poinValue = 0;
                                    if ($todayData && $todayData->$key) {
                                        if ($config['has_jamaah'] ?? false) {
                                            $jamaahKey = $key . '_berjamaah';
                                            $poinValue = ($todayData->$jamaahKey) ? ($config['poin_jamaah'] ?? $config['poin'] * 4) : $config['poin'];
                                        } else {
                                            $poinValue = $config['poin'];
                                        }
                                    }
                                @endphp
                                {{ $poinValue }}
                            </strong>
                        </span>
                        <button type="submit"
                                class="bg-[#27438D] text-white px-4 py-2 rounded-lg hover:bg-[#161758] transition-colors duration-200"
                                {{ ($todayData && $todayData->status_approval === 'approved') ? 'disabled' : '' }}>
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endforeach

<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
const poinConfig = @json($poinConfig);

// Tanggal server saat halaman dimuat
const serverTodayIso = "{{ $today->format('Y-m-d') }}";

// State untuk rekap statistik
let currentTodayPoin = {{ $todayData->total_poin ?? 0 }};
let hasTodayEntry = {{ $todayData ? 'true' : 'false' }};
let statTotalHari = {{ $statistik['total_hari'] }};
let statTotalPoin = {{ $statistik['total_poin'] }};
let statTertinggi = {{ $statistik['tertinggi'] }};

const statusBadgeClasses = {
    pending: 'bg-[#FCC626] text-[#1B1B1B]',
    approved: 'bg-[#2E7D3E] text-white',
    rejected: 'bg-[#ec1d1d] text-white',
};

// Open modal
function openModal(key) {
    document.getElementById(`modal-${key}`).classList.remove('hidden');
    document.getElementById(`modal-${key}`).classList.add('flex');
    document.body.style.overflow = 'hidden';
}

// Close modal
function closeModal(key) {
    document.getElementById(`modal-${key}`).classList.add('hidden');
    document.getElementById(`modal-${key}`).classList.remove('flex');
    document.body.style.overflow = 'auto';
}

// Close modal when clicking outside
document.addEventListener('click', function(event) {
    document.querySelectorAll('.fixed.inset-0.bg-black.bg-opacity-50').forEach(modal => {
        if (event.target === modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.style.overflow = 'auto';
        }
    });
});

// Update preview poin saat checkbox diubah
document.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
    checkbox.addEventListener('change', function() {
        const form = this.closest('form');
        if (!form) return;

        const key = form.querySelector('input[name="field_name"]').value;
        const config = poinConfig[key];
        const mainCheck = document.getElementById(`check-${key}`);
        const jamaahCheck = document.getElementById(`check-${key}-jamaah`);
        const preview = document.getElementById(`poin-preview-${key}`);

        if (!preview) return;

        let poin = 0;
        if (mainCheck && mainCheck.checked) {
            if (config.has_jamaah && jamaahCheck && jamaahCheck.checked) {
                poin = config.poin_jamaah || config.poin * 4;
            } else if (config.has_jamaah) {
                poin = config.poin;
            } else {
                poin = config.poin;
            }
        }
        preview.textContent = poin;
    });
});

// Simpan checklist
function saveChecklist(event, key) {
    event.preventDefault();

    const form = document.getElementById(`form-${key}`);
    const mainCheck = document.getElementById(`check-${key}`);
    const jamaahCheck = document.getElementById(`check-${key}-jamaah`);

    const formData = new FormData();
    formData.append('field_name', key);
    formData.append(key, mainCheck.checked ? '1' : '0');

    // Kirim data berjamaah jika ada
    if (jamaahCheck) {
        formData.append(key + '_berjamaah', jamaahCheck.checked ? '1' : '0');
    }

    const btn = form.querySelector('button[type="submit"]');
    const originalText = btn.textContent;
    btn.textContent = '⏳ Menyimpan...';
    btn.disabled = true;

    fetch('{{ route("karyawan.sunnah.save") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(response => response.json().then(data => ({ ok: response.ok, data })))
    .then(({ ok, data }) => {
        if (!ok || !data.success) {
            Swal.fire({
                icon: 'error',
                title: 'Tidak bisa disimpan',
                text: data.message || 'Terjadi kesalahan pada server',
            });
            mainCheck.checked = !mainCheck.checked;
            if (jamaahCheck) jamaahCheck.checked = !jamaahCheck.checked;
            return;
        }

        const newTotalPoin = data.data.total_poin;
        const config = poinConfig[key];
        const poinItem = mainCheck.checked ?
            (config.has_jamaah && jamaahCheck && jamaahCheck.checked ? (config.poin_jamaah || config.poin * 4) : config.poin) : 0;

        // Update poin preview
        const poinDisplay = document.querySelector(`#poin-preview-${key}`);
        if (poinDisplay) poinDisplay.textContent = poinItem;

        // Update card badge
        const cardBadge = document.getElementById(`card-check-${key}`);
        if (cardBadge) {
            let html = mainCheck.checked ? '<span class="text-[#2E7D3E]">✅</span>' : '<span class="text-gray-300">⬜</span>';
            if (mainCheck.checked && config.has_jamaah && jamaahCheck && jamaahCheck.checked) {
                html += '<span class="text-xs text-[#27438D] block">🕌</span>';
            }
            cardBadge.innerHTML = html;
        }

        // Update status hari ini
        document.getElementById('today-status-empty').classList.add('hidden');
        document.getElementById('today-status-filled').classList.remove('hidden');
        document.getElementById('today-poin-badge').textContent = `✅ Poin: ${newTotalPoin}`;

        const approvalBadge = document.getElementById('today-approval-badge');
        approvalBadge.textContent = data.data.status;
        approvalBadge.className = 'ml-2 px-3 py-1 rounded-full text-xs font-medium ' +
            (statusBadgeClasses[data.data.status_approval] || '');

        // Update riwayat 30 hari
        const barEl = document.getElementById(`bar-${serverTodayIso}`);
        const poinEl = document.getElementById(`poin-${serverTodayIso}`);
        if (barEl && poinEl) {
            poinEl.textContent = newTotalPoin;
            const height = newTotalPoin > 0 ? Math.min(newTotalPoin / 2, 32) : 8;
            barEl.style.height = `${height}px`;
            barEl.classList.toggle('bg-[#2E7D3E]', newTotalPoin > 0);
            barEl.classList.toggle('bg-gray-200', newTotalPoin <= 0);
        }

        // Update statistik
        if (!hasTodayEntry) {
            statTotalHari += 1;
            hasTodayEntry = true;
        }
        statTotalPoin = statTotalPoin - currentTodayPoin + newTotalPoin;
        statTertinggi = Math.max(statTertinggi, newTotalPoin);
        currentTodayPoin = newTotalPoin;

        document.getElementById('stat-total-hari').textContent = statTotalHari;
        document.getElementById('stat-total-poin').textContent = statTotalPoin;
        document.getElementById('stat-tertinggi').textContent = statTertinggi;
        document.getElementById('stat-rata-rata').textContent =
            statTotalHari > 0 ? (statTotalPoin / statTotalHari).toFixed(1) : 0;

        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: data.message,
            timer: 1500,
            showConfirmButton: false
        });

        closeModal(key);
    })
    .catch(error => {
        mainCheck.checked = !mainCheck.checked;
        if (jamaahCheck) jamaahCheck.checked = !jamaahCheck.checked;
        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: 'Terjadi kesalahan pada server',
        });
    })
    .finally(() => {
        btn.textContent = originalText;
        btn.disabled = false;
    });
}

// ===== Tanggal & jam realtime =====
const hariNama = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
const bulanNama = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli',
                    'Agustus', 'September', 'Oktober', 'November', 'Desember'];

function updateTanggalRealtime() {
    const now = new Date();
    const localIso = now.getFullYear() + '-' +
        String(now.getMonth() + 1).padStart(2, '0') + '-' +
        String(now.getDate()).padStart(2, '0');

    const display = document.getElementById('current-date-display');
    if (display) {
        display.textContent = `📅 ${hariNama[now.getDay()]}, ${now.getDate()} ${bulanNama[now.getMonth()]} ${now.getFullYear()}`;
    }

    if (localIso !== serverTodayIso) {
        location.reload();
    }
}

updateTanggalRealtime();
setInterval(updateTanggalRealtime, 30000);
</script>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endsection
