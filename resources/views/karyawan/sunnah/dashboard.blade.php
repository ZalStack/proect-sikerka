@extends('layouts.app')

@section('content')
<div class="flex">
    @include('layouts.sidebar')
    <div class="ml-64 flex-1 p-6">
        <div class="mb-6">
            <h1 class="text-2xl font-bold font-['Montserrat'] text-[#161758]">7SPS - 7 Sunnah Plus Suprasional</h1>
            <p class="text-[#27438D]">Kelola kegiatan sunnah harian Anda. Langsung centang, tidak perlu konfirmasi tambahan.</p>
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

        <!-- Pemilih Tanggal: Hari Ini / Kemarin (H-1) -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <div class="flex justify-between items-center flex-wrap gap-4">
                <div>
                    <h2 class="text-lg font-semibold text-[#161758] mb-2">
                        📅 {{ $selectedDate->locale('id')->isoFormat('dddd, D MMMM YYYY') }}
                        @if($selectedDate->isSameDay($yesterday))
                            <span class="text-xs font-normal text-[#27438D]">(Kemarin / H-1)</span>
                        @endif
                    </h2>
                    <div class="flex gap-2">
                        <a href="{{ route('karyawan.sunnah.dashboard') }}"
                           class="px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-200 {{ $selectedDate->isSameDay($today) ? 'bg-[#27438D] text-white' : 'bg-[#F5F5F5] text-[#1B1B1B] hover:bg-gray-200' }}">
                            Hari Ini
                        </a>
                        <a href="{{ route('karyawan.sunnah.dashboard', ['tanggal' => $yesterday->format('Y-m-d')]) }}"
                           class="px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-200 {{ $selectedDate->isSameDay($yesterday) ? 'bg-[#27438D] text-white' : 'bg-[#F5F5F5] text-[#1B1B1B] hover:bg-gray-200' }}">
                            Kemarin (H-1)
                        </a>
                    </div>
                    <p class="text-xs text-[#27438D] mt-2">Checklist hanya dapat diisi untuk hari ini atau H-1 (kemarin).</p>
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

        @php
            $isLocked = $todayData && $todayData->status_approval === 'approved';
        @endphp

        @if($isLocked)
            <div class="bg-[#FCC626] text-[#1B1B1B] rounded-lg p-4 mb-6 text-sm font-medium">
                🔒 Data tanggal ini sudah disetujui HR dan tidak dapat diubah lagi.
            </div>
        @endif

        <!-- Kelompok Sholat Wajib (Berjamaah) -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <div class="flex items-center justify-between mb-4 flex-wrap gap-2">
                <h2 class="text-lg font-semibold text-[#161758]">🕌 Sholat Wajib &amp; Berjamaah</h2>
                <div class="text-sm text-[#1B1B1B]">
                    Berjamaah: <strong id="jumlah-berjamaah">{{ $todayData->jumlah_sholat_berjamaah ?? 0 }}</strong>/5
                    &middot; Poin kelompok:
                    <strong id="poin-wajib-badge" class="text-[#161758]">{{ $todayData->poin_sholat_wajib ?? 0 }}</strong>
                </div>
            </div>
            <p class="text-xs text-[#27438D] mb-4">
                <strong>Ketentuan poin:</strong><br>
                • 5/5 berjamaah (lengkap) = <strong>20 poin</strong> (5 × 4)<br>
                • 1-4/5 berjamaah = <strong>jumlah berjamaah × 1 poin</strong><br>
                • 0/5 berjamaah = <strong>0 poin</strong><br>
                <span class="text-xs text-gray-500">Sholat yang dikerjakan sendiri (tidak berjamaah) tidak menyumbang poin.</span>
            </p>
            <div class="space-y-2" id="wajib-list">
                @foreach($sholatWajibKeys as $key)
                    @php $config = $poinConfig[$key]; @endphp
                    <div class="flex items-center justify-between p-3 bg-[#F5F5F5] rounded-lg flex-wrap gap-2" id="row-{{ $key }}">
                        <div class="flex items-center space-x-3">
                            <span class="text-xl">{{ $config['icon'] }}</span>
                            <span class="text-sm font-medium text-[#1B1B1B]">{{ $config['label'] }}</span>
                        </div>
                        <div class="flex items-center space-x-4">
                            <label class="flex items-center space-x-2 text-sm text-[#1B1B1B]">
                                <input type="checkbox"
                                       data-field="{{ $key }}"
                                       class="main-check w-5 h-5 rounded text-[#2E7D3E] focus:ring-2 focus:ring-[#2E7D3E]"
                                       {{ ($todayData && $todayData->$key) ? 'checked' : '' }}
                                       {{ $isLocked ? 'disabled' : '' }}>
                                <span>Dikerjakan</span>
                            </label>
                            <label class="flex items-center space-x-2 text-sm text-[#27438D]">
                                <input type="checkbox"
                                       data-field="{{ $key }}_berjamaah"
                                       data-parent="{{ $key }}"
                                       class="jamaah-check w-5 h-5 rounded text-[#27438D] focus:ring-2 focus:ring-[#27438D]"
                                       {{ ($todayData && $todayData->{$key . '_berjamaah'}) ? 'checked' : '' }}
                                       {{ (!($todayData && $todayData->$key)) ? 'disabled' : '' }}
                                       {{ $isLocked ? 'disabled' : '' }}>
                                <span>🕌 Berjamaah</span>
                            </label>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Kegiatan Sunnah Lainnya -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-lg font-semibold text-[#161758] mb-4">📋 Kegiatan Sunnah Lainnya</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                @foreach($poinConfig as $key => $config)
                    @continue(in_array($key, $sholatWajibKeys, true))
                    <label for="check-{{ $key }}"
                           class="flex items-center justify-between bg-[#F5F5F5] rounded-lg p-4 cursor-pointer hover:bg-gray-100 transition-colors duration-200">
                        <div class="flex items-center space-x-3">
                            <span class="text-2xl">{{ $config['icon'] }}</span>
                            <div>
                                <h3 class="font-semibold text-[#161758] text-sm">{{ $config['label'] }}</h3>
                                <p class="text-xs text-[#27438D]" id="poin-preview-{{ $key }}">{{ $config['poin'] }} poin</p>
                            </div>
                        </div>
                        <input type="checkbox"
                               id="check-{{ $key }}"
                               data-field="{{ $key }}"
                               class="main-check simple-check w-5 h-5 rounded text-[#2E7D3E] focus:ring-2 focus:ring-[#2E7D3E]"
                               {{ ($todayData && $todayData->$key) ? 'checked' : '' }}
                               {{ $isLocked ? 'disabled' : '' }}>
                    </label>
                @endforeach
            </div>
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

<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
const isLocked = @json($isLocked);

// Tanggal yang sedang dipilih (bisa hari ini atau kemarin/H-1)
const selectedDateIso = "{{ $selectedDate->format('Y-m-d') }}";
const serverTodayIso = "{{ $today->format('Y-m-d') }}";

// State untuk rekap statistik bulanan
let currentEntryPoin = {{ $todayData->total_poin ?? 0 }};
let hasEntry = {{ $todayData ? 'true' : 'false' }};
let statTotalHari = {{ $statistik['total_hari'] }};
let statTotalPoin = {{ $statistik['total_poin'] }};
let statTertinggi = {{ $statistik['tertinggi'] }};

const statusBadgeClasses = {
    pending: 'bg-[#FCC626] text-[#1B1B1B]',
    approved: 'bg-[#2E7D3E] text-white',
    rejected: 'bg-[#ec1d1d] text-white',
};

function setSaving(el, saving) {
    el.disabled = saving || isLocked;
}

function simpanChecklist(fieldName, checkbox) {
    if (isLocked) {
        checkbox.checked = !checkbox.checked;
        return;
    }

    // Field induk (mis. "sholat_subuh"), baik dipicu dari checkbox utama maupun checkbox berjamaah
    const parentField = checkbox.dataset.parent || fieldName;
    const mainCheckbox = document.querySelector(`.main-check[data-field="${parentField}"]`);
    const jamaahCheckbox = document.querySelector(`.jamaah-check[data-parent="${parentField}"]`);

    const formData = new FormData();
    formData.append('field_name', parentField);
    formData.append('tanggal', selectedDateIso);
    formData.append(parentField, mainCheckbox && mainCheckbox.checked ? '1' : '0');

    if (jamaahCheckbox) {
        formData.append(parentField + '_berjamaah', jamaahCheckbox.checked ? '1' : '0');
    }

    setSaving(checkbox, true);

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
        setSaving(checkbox, false);

        if (!ok || !data.success) {
            alert(data.message || 'Terjadi kesalahan pada server');
            checkbox.checked = !checkbox.checked;
            return;
        }

        const d = data.data;

        // Update badge poin kelompok sholat wajib
        const poinWajibBadge = document.getElementById('poin-wajib-badge');
        const jumlahBerjamaah = document.getElementById('jumlah-berjamaah');
        if (poinWajibBadge) poinWajibBadge.textContent = d.poin_sholat_wajib;
        if (jumlahBerjamaah) jumlahBerjamaah.textContent = d.jumlah_sholat_berjamaah;

        // Update status badge
        document.getElementById('today-status-empty').classList.add('hidden');
        document.getElementById('today-status-filled').classList.remove('hidden');
        document.getElementById('today-poin-badge').textContent = `✅ Poin: ${d.total_poin}`;
        const approvalBadge = document.getElementById('today-approval-badge');
        approvalBadge.textContent = d.status;
        approvalBadge.className = 'ml-2 px-3 py-1 rounded-full text-xs font-medium ' +
            (statusBadgeClasses[d.status_approval] || '');

        // Aktifkan/nonaktifkan checkbox berjamaah sesuai status checklist utama
        if (jamaahCheckbox) {
            jamaahCheckbox.disabled = !(mainCheckbox && mainCheckbox.checked) || isLocked;
            if (!(mainCheckbox && mainCheckbox.checked)) {
                jamaahCheckbox.checked = false;
            }
        }

        // Update riwayat 30 hari untuk tanggal yang sedang diisi (hari ini atau kemarin)
        const barEl = document.getElementById(`bar-${d.tanggal}`);
        const poinEl = document.getElementById(`poin-${d.tanggal}`);
        if (barEl && poinEl) {
            poinEl.textContent = d.total_poin;
            const height = d.total_poin > 0 ? Math.min(d.total_poin / 2, 32) : 8;
            barEl.style.height = `${height}px`;
            barEl.classList.toggle('bg-[#2E7D3E]', d.total_poin > 0);
            barEl.classList.toggle('bg-gray-200', d.total_poin <= 0);
        }

        // Update statistik bulanan
        if (!hasEntry) {
            statTotalHari += 1;
            hasEntry = true;
        }
        statTotalPoin = statTotalPoin - currentEntryPoin + d.total_poin;
        statTertinggi = Math.max(statTertinggi, d.total_poin);
        currentEntryPoin = d.total_poin;

        document.getElementById('stat-total-hari').textContent = statTotalHari;
        document.getElementById('stat-total-poin').textContent = statTotalPoin;
        document.getElementById('stat-tertinggi').textContent = statTertinggi;
        document.getElementById('stat-rata-rata').textContent =
            statTotalHari > 0 ? (statTotalPoin / statTotalHari).toFixed(1) : 0;
    })
    .catch(() => {
        setSaving(checkbox, false);
        checkbox.checked = !checkbox.checked;
        alert('Terjadi kesalahan pada server');
    });
}

// Checklist utama sholat wajib
document.querySelectorAll('.main-check[data-field]').forEach(checkbox => {
    checkbox.addEventListener('change', function () {
        const field = this.dataset.field;
        const jamaahCheckbox = document.querySelector(`.jamaah-check[data-parent="${field}"]`);
        if (jamaahCheckbox) {
            // Aktif/nonaktifkan opsi berjamaah secara langsung (tanpa modal/popup)
            jamaahCheckbox.disabled = !this.checked || isLocked;
            if (!this.checked) jamaahCheckbox.checked = false;
        }
        simpanChecklist(field, this);
    });
});

// Checklist berjamaah (langsung, tanpa modal)
document.querySelectorAll('.jamaah-check').forEach(checkbox => {
    checkbox.addEventListener('change', function () {
        simpanChecklist(this.dataset.field, this);
    });
});
</script>
@endsection
