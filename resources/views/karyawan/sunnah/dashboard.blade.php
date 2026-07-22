{{-- views/karyawan/sunnah/dashboard.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="flex min-h-screen">
    @include('layouts.sidebar')
    <div class="flex-1 transition-all duration-300 md:ml-64 pt-6">
        <div class="p-3 sm:p-6">
            <div class="mb-6">
                <h1 class="text-xl sm:text-2xl font-bold font-['Montserrat'] text-[#161758]">7SPS - 7 Sunnah Plus Suprasional</h1>
                <p class="text-sm sm:text-base text-[#27438D]">Kelola kegiatan sunnah harian Anda. Langsung centang, tidak perlu konfirmasi tambahan.</p>
            </div>

            @if(session('success'))
                <div class="bg-[#2E7D3E] text-white p-3 sm:p-4 rounded-lg mb-4 text-sm">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Statistik -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-4 mb-6">
                <div class="bg-white rounded-lg shadow-md p-3 sm:p-4 text-center hover:shadow-lg transition-all duration-300 transform hover:scale-105">
                    <p id="stat-total-hari" class="text-lg sm:text-2xl font-bold text-[#161758]">{{ $statistik['total_hari'] }}</p>
                    <p class="text-[10px] sm:text-sm text-[#1B1B1B]">Total Hari</p>
                </div>
                <div class="bg-[#2E7D3E] text-white rounded-lg shadow-md p-3 sm:p-4 text-center hover:shadow-lg transition-all duration-300 transform hover:scale-105">
                    <p id="stat-total-poin" class="text-lg sm:text-2xl font-bold">{{ $statistik['total_poin'] }}</p>
                    <p class="text-[10px] sm:text-sm">Total Poin</p>
                </div>
                <div class="bg-[#FCC626] text-[#1B1B1B] rounded-lg shadow-md p-3 sm:p-4 text-center hover:shadow-lg transition-all duration-300 transform hover:scale-105">
                    <p id="stat-rata-rata" class="text-lg sm:text-2xl font-bold">{{ $statistik['rata_rata'] }}</p>
                    <p class="text-[10px] sm:text-sm">Rata-rata Poin</p>
                </div>
                <div class="bg-[#00a2e9] text-white rounded-lg shadow-md p-3 sm:p-4 text-center hover:shadow-lg transition-all duration-300 transform hover:scale-105">
                    <p id="stat-tertinggi" class="text-lg sm:text-2xl font-bold">{{ $statistik['tertinggi'] }}</p>
                    <p class="text-[10px] sm:text-sm">Poin Tertinggi</p>
                </div>
            </div>

            <!-- Pemilih Tanggal: Hari Ini / Kemarin (H-1) -->
            <div class="bg-white rounded-lg shadow-md p-4 sm:p-6 mb-6">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 sm:gap-4">
                    <div>
                        <h2 class="text-base sm:text-lg font-semibold text-[#161758] mb-2">
                            📅 {{ $selectedDate->locale('id')->isoFormat('dddd, D MMMM YYYY') }}
                            @if($selectedDate->isSameDay($yesterday))
                                <span class="text-[10px] sm:text-xs font-normal text-[#27438D]">(Kemarin)</span>
                            @endif
                        </h2>
                        <div class="flex flex-wrap gap-2">
                            <a href="{{ route('karyawan.sunnah.dashboard') }}"
                               class="px-3 sm:px-4 py-1.5 sm:py-2 rounded-lg text-xs sm:text-sm font-medium transition-all duration-200 {{ $selectedDate->isSameDay($today) ? 'bg-[#27438D] text-white shadow-md' : 'bg-[#F5F5F5] text-[#1B1B1B] hover:bg-gray-200 hover:shadow-md' }}">
                                Hari Ini
                            </a>
                            <a href="{{ route('karyawan.sunnah.dashboard', ['tanggal' => $yesterday->format('Y-m-d')]) }}"
                               class="px-3 sm:px-4 py-1.5 sm:py-2 rounded-lg text-xs sm:text-sm font-medium transition-all duration-200 {{ $selectedDate->isSameDay($yesterday) ? 'bg-[#27438D] text-white shadow-md' : 'bg-[#F5F5F5] text-[#1B1B1B] hover:bg-gray-200 hover:shadow-md' }}">
                                Kemarin
                            </a>
                        </div>
                        <p class="text-[10px] sm:text-xs text-[#27438D] mt-2">Checklist hanya dapat diisi untuk hari ini atau (kemarin).</p>
                    </div>
                    <div>
                        <div id="today-status-empty" class="{{ $todayData ? 'hidden' : '' }}">
                            <span class="px-3 sm:px-4 py-2 rounded-full text-xs sm:text-sm font-medium bg-[#FCC626] text-[#1B1B1B] animate-pulse">
                                ⏳ Belum Mengisi
                            </span>
                        </div>
                        <div id="today-status-filled" class="{{ $todayData ? '' : 'hidden' }}">
                            <span id="today-poin-badge" class="px-3 sm:px-4 py-2 rounded-full text-xs sm:text-sm font-medium bg-[#2E7D3E] text-white">
                                ✅ Poin: {{ $todayData->total_poin ?? 0 }}
                            </span>
                            <span id="today-approval-badge" class="ml-2 px-2 sm:px-3 py-1 rounded-full text-[10px] sm:text-xs font-medium {{ $todayData->status_badge ?? '' }}">
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
                <div class="bg-[#FCC626] text-[#1B1B1B] rounded-lg p-3 sm:p-4 mb-6 text-xs sm:text-sm font-medium animate-pulse">
                    🔒 Data tanggal ini sudah disetujui HR dan tidak dapat diubah lagi.
                </div>
            @endif

            <!-- Progress Bar dengan Animasi 4 Warna -->
            <div class="bg-white rounded-lg shadow-md p-4 sm:p-6 mb-6">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-2 gap-2">
                    <h2 class="text-base sm:text-lg font-semibold text-[#161758]">🎯 Progress Poin Hari Ini</h2>
                    <span id="poin-counter" class="text-xl sm:text-2xl font-bold text-[#2E7D3E]">{{ $todayData->total_poin ?? 0 }}</span>
                </div>
                <div class="relative w-full h-8 sm:h-10 bg-gray-200 rounded-full overflow-hidden shadow-inner">
                    <div id="progress-bar"
                         class="absolute top-0 left-0 h-full rounded-full transition-all duration-1000 ease-out"
                         style="width: {{ min(($todayData->total_poin ?? 0), 100) }}%;">
                        <div class="w-full h-full rounded-full animate-gradient-shift"
                             style="background: linear-gradient(90deg,
                                    #FCC626 0%,
                                    #FF6B35 25%,
                                    #2E7D3E 50%,
                                    #00a2e9 75%,
                                    #FCC626 100%);
                                    background-size: 300% 100%;">
                        </div>
                    </div>
                    <div class="absolute inset-0 flex items-center justify-center">
                        <span id="progress-text" class="text-xs sm:text-sm font-bold text-white drop-shadow-lg">
                            {{ min(($todayData->total_poin ?? 0), 100) }}%
                        </span>
                    </div>
                </div>
            </div>

            <!-- Sholat Berjamaah & Kegiatan Sunnah Lainnya (digabung dalam satu grid) -->
            <div class="bg-white rounded-lg shadow-md p-4 sm:p-6 mb-6">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-4 gap-2">
                    <h2 class="text-base sm:text-lg font-semibold text-[#161758]">🕌 Kegiatan Sunnah </h2>
                    <div class="text-xs sm:text-sm text-[#1B1B1B]">
                        Berjamaah: <strong id="jumlah-berjamaah">{{ $todayData->jumlah_sholat_berjamaah ?? 0 }}</strong>/5
                        &middot; Poin kelompok:
                        <strong id="poin-wajib-badge" class="text-[#161758]">{{ $todayData->poin_sholat_wajib ?? 0 }}</strong>
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3 sm:gap-4">
                    {{-- Sholat Wajib: hanya ceklis Berjamaah --}}
                    @foreach($sholatWajibKeys as $key)
                        @php $config = $poinConfig[$key]; @endphp
                        <label for="check-{{ $key }}_berjamaah"
                               class="flex items-center justify-between bg-[#F5F5F5] rounded-lg p-3 sm:p-4 cursor-pointer hover:bg-gray-100 transition-all duration-300 transform hover:scale-105 hover:shadow-md"
                               id="row-{{ $key }}">
                            <div class="flex items-center space-x-2 sm:space-x-3">
                                <span class="text-xl sm:text-2xl">{{ $config['icon'] }}</span>
                                <div>
                                    <h3 class="font-semibold text-[#161758] text-xs sm:text-sm">{{ $config['label'] }}</h3>
                                    <p class="text-[10px] sm:text-xs text-[#27438D]">🕌 Berjamaah</p>
                                </div>
                            </div>
                            <input type="checkbox"
                                   id="check-{{ $key }}_berjamaah"
                                   data-field="{{ $key }}"
                                   class="jamaah-only-check w-4 h-4 sm:w-5 sm:h-5 rounded text-[#27438D] focus:ring-2 focus:ring-[#27438D] transition-all duration-200 cursor-pointer"
                                   {{ ($todayData && $todayData->{$key . '_berjamaah'}) ? 'checked' : '' }}
                                   {{ $isLocked ? 'disabled' : '' }}>
                        </label>
                    @endforeach

                    {{-- Kegiatan Sunnah Lainnya --}}
                    @foreach($poinConfig as $key => $config)
                        @continue(in_array($key, $sholatWajibKeys, true))
                        <label for="check-{{ $key }}"
                               class="flex items-center justify-between bg-[#F5F5F5] rounded-lg p-3 sm:p-4 cursor-pointer hover:bg-gray-100 transition-all duration-300 transform hover:scale-105 hover:shadow-md">
                            <div class="flex items-center space-x-2 sm:space-x-3">
                                <span class="text-xl sm:text-2xl">{{ $config['icon'] }}</span>
                                <div>
                                    <h3 class="font-semibold text-[#161758] text-xs sm:text-sm">{{ $config['label'] }}</h3>
                                    <p class="text-[10px] sm:text-xs text-[#27438D]" id="poin-preview-{{ $key }}">{{ $config['poin'] }} poin</p>
                                </div>
                            </div>
                            <input type="checkbox"
                                   id="check-{{ $key }}"
                                   data-field="{{ $key }}"
                                   class="main-check simple-check w-4 h-4 sm:w-5 sm:h-5 rounded text-[#2E7D3E] focus:ring-2 focus:ring-[#2E7D3E] transition-all duration-200 cursor-pointer"
                                   {{ ($todayData && $todayData->$key) ? 'checked' : '' }}
                                   {{ $isLocked ? 'disabled' : '' }}>
                        </label>
                    @endforeach
                </div>
            </div>

            <!-- Riwayat 30 Hari -->
            <div class="bg-white rounded-lg shadow-md p-4 sm:p-6">
                <h2 class="text-base sm:text-lg font-semibold text-[#161758] mb-4">📊 Riwayat 30 Hari Terakhir</h2>
                <div class="overflow-x-auto -mx-4 sm:mx-0">
                    <div class="grid grid-cols-5 sm:grid-cols-10 lg:grid-cols-15 gap-1 sm:gap-2 min-w-[300px]">
                        @foreach($last30Days as $day)
                            <div class="text-center" id="day-{{ $day['iso'] }}">
                                <div class="text-[8px] sm:text-xs text-gray-500">{{ $day['tanggal'] }}</div>
                                <div id="bar-{{ $day['iso'] }}"
                                     class="mt-1 w-full rounded transition-all duration-500 {{ $day['poin'] > 0 ? 'bg-gradient-to-t from-[#FCC626] via-[#2E7D3E] to-[#00a2e9]' : 'bg-gray-200' }}"
                                     style="height: {{ $day['poin'] > 0 ? min($day['poin'] / 2, 32) : 8 }}px;">
                                </div>
                                <div class="text-[8px] sm:text-xs font-semibold mt-1" id="poin-{{ $day['iso'] }}">{{ $day['poin'] }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Audio Player (Hidden) -->
<audio id="milestone-audio" preload="auto"></audio>

<!-- Confetti Container -->
<div id="confetti-container" class="fixed top-0 left-0 w-full h-full pointer-events-none z-50"></div>

<style>
@keyframes gradientShift {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}
.animate-gradient-shift {
    animation: gradientShift 3s ease-in-out infinite;
}

@keyframes confettiFall {
    0% { transform: translateY(0) rotate(0deg) scale(1); opacity: 1; }
    100% { transform: translateY(110vh) rotate(720deg) scale(0); opacity: 0; }
}

@keyframes pulse-dot {
    0% { transform: scale(1); box-shadow: 0 0 0 0 rgba(46, 125, 62, 0.4); }
    70% { transform: scale(1.3); box-shadow: 0 0 0 10px rgba(46, 125, 62, 0); }
    100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(46, 125, 62, 0); }
}
.milestone-active {
    animation: pulse-dot 1.5s ease-in-out 2;
}

.main-check:checked {
    background-color: #2E7D3E;
    border-color: #2E7D3E;
}
.main-check:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}
.jamaah-only-check:checked {
    background-color: #27438D;
    border-color: #27438D;
}
.jamaah-only-check:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    const isLocked = @json($isLocked);
    const selectedDateIso = "{{ $selectedDate->format('Y-m-d') }}";
    const serverTodayIso = "{{ $today->format('Y-m-d') }}";
    // Konfigurasi suara milestone (file & durasi asli file, dalam detik)
    const milestoneSounds = {
        40: { file: 'point-40.mp3', duration: 5 },
        75: { file: 'point-75.mp3', duration: 3 },
        90: { file: 'point-90.mp3', duration: 4 },
        100: { file: 'point-100.mp3', duration: 7 },
    };
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

    // Kunci milestone terurut (40, 75, 90, 100)
    const milestoneKeys = Object.keys(milestoneSounds).map(Number).sort((a, b) => a - b);

    // Set milestone yang sedang tercapai berdasarkan poin saat ini
    let reachedMilestones = new Set();

    // Antrian audio, supaya jika beberapa milestone terlewati sekaligus,
    // suara diputar satu per satu (tidak bertumpuk)
    let audioQueue = [];
    let isPlayingAudio = false;

    function enqueueMilestoneAudio(milestoneKey, direction) {
        audioQueue.push({ milestoneKey, direction });
        processAudioQueue();
    }

    function processAudioQueue() {
        if (isPlayingAudio || audioQueue.length === 0) return;
        isPlayingAudio = true;

        const { milestoneKey, direction } = audioQueue.shift();
        const milestoneData = milestoneSounds[milestoneKey];
        const audio = document.getElementById('milestone-audio');

        const dot = document.getElementById(`milestone-dot-${milestoneKey}`);
        if (dot && direction === 'up') {
            dot.classList.add('milestone-active');
            setTimeout(() => dot.classList.remove('milestone-active'), 3000);
        }

        audio.src = `/storage/sounds/${milestoneData.file}`;
        audio.currentTime = 0;
        const playPromise = audio.play();

        if (direction === 'up' && milestoneKey === 100) {
            triggerConfetti();
            document.getElementById('progress-bar').classList.add('animate-pulse');
            setTimeout(() => {
                document.getElementById('progress-bar').classList.remove('animate-pulse');
            }, 3000);
        }

        // Durasi sesuai panjang file mp3 aslinya (40=5s, 75=3s, 90=4s, 100=7s)
        const duration = milestoneData.duration * 1000 + 300;

        const finishPlayback = () => {
            isPlayingAudio = false;
            processAudioQueue();
        };

        if (playPromise && typeof playPromise.then === 'function') {
            playPromise
                .then(() => setTimeout(finishPlayback, duration))
                .catch((e) => {
                    console.log('Audio play failed:', e);
                    setTimeout(finishPlayback, duration);
                });
        } else {
            setTimeout(finishPlayback, duration);
        }
    }

    // Deteksi milestone yang baru tercapai (naik) atau baru lepas (turun)
    // dibandingkan dengan state poin sebelumnya, lalu putar suara untuk tiap milestone yang terlewati
    function checkMilestoneCrossing(newPoin) {
        const newReached = new Set(milestoneKeys.filter(k => newPoin >= k));

        milestoneKeys.forEach(key => {
            const wasReached = reachedMilestones.has(key);
            const isReached = newReached.has(key);

            if (isReached && !wasReached) {
                enqueueMilestoneAudio(key, 'up');
            } else if (!isReached && wasReached) {
                enqueueMilestoneAudio(key, 'down');
            }
        });

        reachedMilestones = newReached;
    }

    // Pengumuman awal saat halaman dimuat: putar suara milestone tertinggi yang sudah tercapai (jika ada)
    function announceInitialMilestone(poin) {
        reachedMilestones = new Set(milestoneKeys.filter(k => poin >= k));
        if (reachedMilestones.size > 0) {
            const highest = Math.max(...reachedMilestones);
            setTimeout(() => enqueueMilestoneAudio(highest, 'up'), 500);
        }
    }

    function triggerConfetti() {
        const container = document.getElementById('confetti-container');
        const colors = ['#FCC626', '#2E7D3E', '#00a2e9', '#FF6B35', '#ff6b6b', '#ffd93d', '#6bcb77', '#4d96ff', '#a29bfe', '#fd79a8'];

        for (let i = 0; i < 200; i++) {
            const confetti = document.createElement('div');
            const size = Math.random() * 12 + 4;
            const leftPos = Math.random() * 100;
            const delay = Math.random() * 2;
            const duration = Math.random() * 2.5 + 2;
            const isCircle = Math.random() > 0.5;
            const rotation = Math.random() * 720;

            confetti.style.cssText = `
                position: absolute;
                width: ${size}px;
                height: ${isCircle ? size : size * 0.6}px;
                background: ${colors[Math.floor(Math.random() * colors.length)]};
                left: ${leftPos}%;
                top: -20px;
                border-radius: ${isCircle ? '50%' : '2px'};
                opacity: 1;
                transform: rotate(${rotation}deg);
                animation: confettiFall ${duration}s ease-in forwards;
                animation-delay: ${delay}s;
            `;
            container.appendChild(confetti);
            setTimeout(() => confetti.remove(), (duration + delay) * 1000 + 100);
        }
    }

    function updateBarChart(dateIso, newPoin) {
        const barEl = document.getElementById(`bar-${dateIso}`);
        const poinEl = document.getElementById(`poin-${dateIso}`);

        if (barEl && poinEl) {
            poinEl.textContent = newPoin;
            let height;
            if (newPoin === 0) {
                height = 8;
            } else {
                height = Math.min(8 + (newPoin / 64) * 24, 32);
                height = Math.max(height, 10);
            }
            barEl.style.height = `${height}px`;

            if (newPoin > 0) {
                barEl.className = 'mt-1 w-full rounded transition-all duration-500 bg-gradient-to-t from-[#FCC626] via-[#2E7D3E] to-[#00a2e9]';
            } else {
                barEl.className = 'mt-1 w-full rounded transition-all duration-500 bg-gray-200';
            }
            barEl.title = `${newPoin} poin`;
        }
    }

    function updateProgressBar(poin) {
        const progressBar = document.getElementById('progress-bar');
        const progressText = document.getElementById('progress-text');
        const poinCounter = document.getElementById('poin-counter');

        const percentage = Math.min(poin, 100);
        progressBar.style.width = percentage + '%';
        progressText.textContent = percentage + '%';
        poinCounter.textContent = poin;

        [40, 75, 90, 100].forEach(point => {
            const dot = document.getElementById(`milestone-dot-${point}`);
            if (dot) {
                if (poin >= point) {
                    dot.classList.remove('bg-gray-300');
                    dot.classList.add('bg-[#2E7D3E]', 'scale-125', 'shadow-lg');
                } else {
                    dot.classList.add('bg-gray-300');
                    dot.classList.remove('bg-[#2E7D3E]', 'scale-125', 'shadow-lg');
                }
            }
        });

        checkMilestoneCrossing(poin);
    }

    function simpanChecklist(fieldName, checkbox) {
        if (isLocked) {
            checkbox.checked = !checkbox.checked;
            return;
        }

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
            updateProgressBar(d.total_poin);

            const poinWajibBadge = document.getElementById('poin-wajib-badge');
            const jumlahBerjamaah = document.getElementById('jumlah-berjamaah');
            if (poinWajibBadge) poinWajibBadge.textContent = d.poin_sholat_wajib;
            if (jumlahBerjamaah) jumlahBerjamaah.textContent = d.jumlah_sholat_berjamaah;

            document.getElementById('today-status-empty').classList.add('hidden');
            document.getElementById('today-status-filled').classList.remove('hidden');
            document.getElementById('today-poin-badge').textContent = `✅ Poin: ${d.total_poin}`;
            const approvalBadge = document.getElementById('today-approval-badge');
            approvalBadge.textContent = d.status;
            approvalBadge.className = 'ml-2 px-2 sm:px-3 py-1 rounded-full text-[10px] sm:text-xs font-medium ' +
                (statusBadgeClasses[d.status_approval] || '');

            if (jamaahCheckbox) {
                jamaahCheckbox.disabled = !(mainCheckbox && mainCheckbox.checked) || isLocked;
                if (!(mainCheckbox && mainCheckbox.checked)) {
                    jamaahCheckbox.checked = false;
                }
            }

            updateBarChart(d.tanggal, d.total_poin);

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
        .catch((error) => {
            setSaving(checkbox, false);
            checkbox.checked = !checkbox.checked;
            console.error('Error:', error);
            alert('Terjadi kesalahan pada server');
        });
    }

    function simpanChecklistBerjamaah(fieldName, checkbox) {
        if (isLocked) {
            checkbox.checked = !checkbox.checked;
            return;
        }

        const formData = new FormData();
        formData.append('field_name', fieldName);
        formData.append('tanggal', selectedDateIso);
        formData.append(fieldName, checkbox.checked ? '1' : '0');
        formData.append(fieldName + '_berjamaah', checkbox.checked ? '1' : '0');

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
            updateProgressBar(d.total_poin);

            const poinWajibBadge = document.getElementById('poin-wajib-badge');
            const jumlahBerjamaah = document.getElementById('jumlah-berjamaah');
            if (poinWajibBadge) poinWajibBadge.textContent = d.poin_sholat_wajib;
            if (jumlahBerjamaah) jumlahBerjamaah.textContent = d.jumlah_sholat_berjamaah;

            document.getElementById('today-status-empty').classList.add('hidden');
            document.getElementById('today-status-filled').classList.remove('hidden');
            document.getElementById('today-poin-badge').textContent = `✅ Poin: ${d.total_poin}`;
            const approvalBadge = document.getElementById('today-approval-badge');
            approvalBadge.textContent = d.status;
            approvalBadge.className = 'ml-2 px-2 sm:px-3 py-1 rounded-full text-[10px] sm:text-xs font-medium ' +
                (statusBadgeClasses[d.status_approval] || '');

            updateBarChart(d.tanggal, d.total_poin);

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
        .catch((error) => {
            setSaving(checkbox, false);
            checkbox.checked = !checkbox.checked;
            console.error('Error:', error);
            alert('Terjadi kesalahan pada server');
        });
    }

    document.querySelectorAll('.main-check[data-field]').forEach(checkbox => {
        checkbox.addEventListener('change', function () {
            const field = this.dataset.field;
            const jamaahCheckbox = document.querySelector(`.jamaah-check[data-parent="${field}"]`);
            if (jamaahCheckbox) {
                jamaahCheckbox.disabled = !this.checked || isLocked;
                if (!this.checked) jamaahCheckbox.checked = false;
            }
            simpanChecklist(field, this);
        });
    });

    document.querySelectorAll('.jamaah-only-check[data-field]').forEach(checkbox => {
        checkbox.addEventListener('change', function () {
            simpanChecklistBerjamaah(this.dataset.field, this);
        });
    });

    const initialPoin = {{ $todayData->total_poin ?? 0 }};

    // Set tampilan progress bar dulu tanpa memicu deteksi crossing (karena reachedMilestones
    // masih kosong di titik ini), lalu baru umumkan milestone yang sudah tercapai sejak awal.
    (function initProgressBarOnly(poin) {
        const progressBar = document.getElementById('progress-bar');
        const progressText = document.getElementById('progress-text');
        const poinCounter = document.getElementById('poin-counter');
        const percentage = Math.min(poin, 100);
        progressBar.style.width = percentage + '%';
        progressText.textContent = percentage + '%';
        poinCounter.textContent = poin;

        [40, 75, 90, 100].forEach(point => {
            const dot = document.getElementById(`milestone-dot-${point}`);
            if (dot) {
                if (poin >= point) {
                    dot.classList.remove('bg-gray-300');
                    dot.classList.add('bg-[#2E7D3E]', 'scale-125', 'shadow-lg');
                } else {
                    dot.classList.add('bg-gray-300');
                    dot.classList.remove('bg-[#2E7D3E]', 'scale-125', 'shadow-lg');
                }
            }
        });
    })(initialPoin);

    announceInitialMilestone(initialPoin);
});
</script>
@endsection
