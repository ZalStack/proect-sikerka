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

        <!-- Progress Bar dengan Animasi 4 Warna -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <div class="flex justify-between items-center mb-2">
                <h2 class="text-lg font-semibold text-[#161758]">🎯 Progress Poin Hari Ini</h2>
                <span id="poin-counter" class="text-2xl font-bold text-[#2E7D3E]">{{ $todayData->total_poin ?? 0 }}</span>
            </div>
            <div class="relative w-full h-10 bg-gray-200 rounded-full overflow-hidden shadow-inner">
                <!-- Progress Bar dengan 4 Warna -->
                <div id="progress-bar"
                     class="absolute top-0 left-0 h-full rounded-full transition-all duration-1000 ease-out"
                     style="width: {{ min(($todayData->total_poin ?? 0), 100) }}%;">
                    <!-- Gradient 4 Warna dengan animasi -->
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
                    <span id="progress-text" class="text-sm font-bold text-white drop-shadow-lg">
                        {{ min(($todayData->total_poin ?? 0), 100) }}%
                    </span>
                </div>
            </div>
            <div class="flex justify-between text-xs text-gray-500 mt-1 px-1">
                <span>0</span>
                <span>40</span>
                <span>75</span>
                <span>90</span>
                <span>100</span>
            </div>
            <!-- Milestone Points Indicator -->
            <div class="flex justify-between mt-2 px-1">
                @foreach([40, 75, 90, 100] as $point)
                    <div class="flex flex-col items-center">
                        <div id="milestone-dot-{{ $point }}"
                             class="w-3 h-3 rounded-full transition-all duration-500 {{ ($todayData->total_poin ?? 0) >= $point ? 'bg-[#2E7D3E] scale-125 shadow-lg' : 'bg-gray-300' }}">
                        </div>
                        <span class="text-[10px] text-gray-400 mt-1">{{ $point }}</span>
                    </div>
                @endforeach
            </div>

            <!-- Milestone Messages -->
            <div id="milestone-message" class="mt-4 text-center font-semibold text-[#161758] transition-all duration-500 opacity-0 transform scale-95">
                <!-- Akan diisi oleh JavaScript -->
            </div>
        </div>

        <!-- Statistik -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow-md p-4 text-center hover:shadow-lg transition-all duration-300 transform hover:scale-105">
                <p id="stat-total-hari" class="text-2xl font-bold text-[#161758]">{{ $statistik['total_hari'] }}</p>
                <p class="text-sm text-[#1B1B1B]">Total Hari</p>
            </div>
            <div class="bg-[#2E7D3E] text-white rounded-lg shadow-md p-4 text-center hover:shadow-lg transition-all duration-300 transform hover:scale-105">
                <p id="stat-total-poin" class="text-2xl font-bold">{{ $statistik['total_poin'] }}</p>
                <p class="text-sm">Total Poin</p>
            </div>
            <div class="bg-[#FCC626] text-[#1B1B1B] rounded-lg shadow-md p-4 text-center hover:shadow-lg transition-all duration-300 transform hover:scale-105">
                <p id="stat-rata-rata" class="text-2xl font-bold">{{ $statistik['rata_rata'] }}</p>
                <p class="text-sm">Rata-rata Poin</p>
            </div>
            <div class="bg-[#00a2e9] text-white rounded-lg shadow-md p-4 text-center hover:shadow-lg transition-all duration-300 transform hover:scale-105">
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
                           class="px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 {{ $selectedDate->isSameDay($today) ? 'bg-[#27438D] text-white shadow-md' : 'bg-[#F5F5F5] text-[#1B1B1B] hover:bg-gray-200 hover:shadow-md' }}">
                            Hari Ini
                        </a>
                        <a href="{{ route('karyawan.sunnah.dashboard', ['tanggal' => $yesterday->format('Y-m-d')]) }}"
                           class="px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 {{ $selectedDate->isSameDay($yesterday) ? 'bg-[#27438D] text-white shadow-md' : 'bg-[#F5F5F5] text-[#1B1B1B] hover:bg-gray-200 hover:shadow-md' }}">
                            Kemarin (H-1)
                        </a>
                    </div>
                    <p class="text-xs text-[#27438D] mt-2">Checklist hanya dapat diisi untuk hari ini atau H-1 (kemarin).</p>
                </div>
                <div>
                    <div id="today-status-empty" class="{{ $todayData ? 'hidden' : '' }}">
                        <span class="px-4 py-2 rounded-full text-sm font-medium bg-[#FCC626] text-[#1B1B1B] animate-pulse">
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
            <div class="bg-[#FCC626] text-[#1B1B1B] rounded-lg p-4 mb-6 text-sm font-medium animate-pulse">
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
                    <div class="flex items-center justify-between p-3 bg-[#F5F5F5] rounded-lg flex-wrap gap-2 transition-all duration-300 hover:bg-gray-100" id="row-{{ $key }}">
                        <div class="flex items-center space-x-3">
                            <span class="text-xl">{{ $config['icon'] }}</span>
                            <span class="text-sm font-medium text-[#1B1B1B]">{{ $config['label'] }}</span>
                        </div>
                        <div class="flex items-center space-x-4">
                            <label class="flex items-center space-x-2 text-sm text-[#1B1B1B] cursor-pointer">
                                <input type="checkbox"
                                       data-field="{{ $key }}"
                                       class="main-check w-5 h-5 rounded text-[#2E7D3E] focus:ring-2 focus:ring-[#2E7D3E] transition-all duration-200 cursor-pointer"
                                       {{ ($todayData && $todayData->$key) ? 'checked' : '' }}
                                       {{ $isLocked ? 'disabled' : '' }}>
                                <span>Dikerjakan</span>
                            </label>
                            <label class="flex items-center space-x-2 text-sm text-[#27438D] cursor-pointer">
                                <input type="checkbox"
                                       data-field="{{ $key }}_berjamaah"
                                       data-parent="{{ $key }}"
                                       class="jamaah-check w-5 h-5 rounded text-[#27438D] focus:ring-2 focus:ring-[#27438D] transition-all duration-200 cursor-pointer"
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
                           class="flex items-center justify-between bg-[#F5F5F5] rounded-lg p-4 cursor-pointer hover:bg-gray-100 transition-all duration-300 transform hover:scale-105 hover:shadow-md">
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
                               class="main-check simple-check w-5 h-5 rounded text-[#2E7D3E] focus:ring-2 focus:ring-[#2E7D3E] transition-all duration-200 cursor-pointer"
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
                                 class="mt-1 w-full rounded transition-all duration-500 {{ $day['poin'] > 0 ? 'bg-gradient-to-t from-[#FCC626] via-[#2E7D3E] to-[#00a2e9]' : 'bg-gray-200' }}"
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

<!-- Audio Player (Hidden) -->
<audio id="milestone-audio" preload="auto"></audio>

<!-- Confetti Container -->
<div id="confetti-container" class="fixed top-0 left-0 w-full h-full pointer-events-none z-50"></div>

<style>
/* Animasi Gradient 4 Warna */
@keyframes gradientShift {
    0% {
        background-position: 0% 50%;
    }
    50% {
        background-position: 100% 50%;
    }
    100% {
        background-position: 0% 50%;
    }
}

.animate-gradient-shift {
    animation: gradientShift 3s ease-in-out infinite;
}

/* Animasi Confetti */
@keyframes confettiFall {
    0% {
        transform: translateY(0) rotate(0deg) scale(1);
        opacity: 1;
    }
    100% {
        transform: translateY(110vh) rotate(720deg) scale(0);
        opacity: 0;
    }
}

/* Animasi Pulsing untuk Milestone Dot */
@keyframes pulse-dot {
    0% {
        transform: scale(1);
        box-shadow: 0 0 0 0 rgba(46, 125, 62, 0.4);
    }
    70% {
        transform: scale(1.3);
        box-shadow: 0 0 0 10px rgba(46, 125, 62, 0);
    }
    100% {
        transform: scale(1);
        box-shadow: 0 0 0 0 rgba(46, 125, 62, 0);
    }
}

.milestone-active {
    animation: pulse-dot 1.5s ease-in-out 2;
}

/* Animasi Shimmer untuk Progress Bar */
@keyframes shimmer {
    0% {
        background-position: -200% 0;
    }
    100% {
        background-position: 200% 0;
    }
}

.progress-shimmer {
    background: linear-gradient(
        90deg,
        rgba(255, 255, 255, 0) 0%,
        rgba(255, 255, 255, 0.2) 50%,
        rgba(255, 255, 255, 0) 100%
    );
    background-size: 200% 100%;
    animation: shimmer 2s ease-in-out infinite;
}

/* Checkbox Custom Style */
.main-check:checked {
    background-color: #2E7D3E;
    border-color: #2E7D3E;
}

.main-check:checked + span {
    color: #2E7D3E;
}

.main-check:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.jamaah-check:checked {
    background-color: #27438D;
    border-color: #27438D;
}

.jamaah-check:disabled {
    opacity: 0.3;
    cursor: not-allowed;
}

/* Card Hover Effect */
.hover-lift {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.hover-lift:hover {
    transform: translateY(-4px) scale(1.02);
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    const isLocked = @json($isLocked);

    // Tanggal yang sedang dipilih
    const selectedDateIso = "{{ $selectedDate->format('Y-m-d') }}";
    const serverTodayIso = "{{ $today->format('Y-m-d') }}";

    // Milestone configuration
    const milestones = @json($milestones);
    let lastMilestoneReached = 0;
    let audioPlaying = false;

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

    // Function to play milestone sound
    function playMilestoneSound(poin) {
        const audio = document.getElementById('milestone-audio');
        const milestoneMessage = document.getElementById('milestone-message');

        let milestoneKey = null;
        let milestoneData = null;

        // Find the highest milestone reached
        const sortedKeys = Object.keys(milestones).map(Number).sort((a, b) => a - b);
        for (const key of sortedKeys) {
            if (poin >= key) {
                milestoneKey = key;
                milestoneData = milestones[key];
            }
        }

        // Only trigger if milestone is new and higher than last
        if (milestoneKey && milestoneKey > lastMilestoneReached && milestoneData) {
            lastMilestoneReached = milestoneKey;

            console.log('Milestone reached:', milestoneKey, milestoneData.message);

            // Show message with animation
            milestoneMessage.textContent = milestoneData.message;
            milestoneMessage.className = 'mt-4 text-center font-semibold text-[#161758] transition-all duration-500 opacity-100 transform scale-100';

            // Highlight milestone dot
            const dot = document.getElementById(`milestone-dot-${milestoneKey}`);
            if (dot) {
                dot.classList.add('milestone-active');
                setTimeout(() => dot.classList.remove('milestone-active'), 3000);
            }

            // Play sound
            const audioPath = `/storage/sounds/${milestoneData.sound}`;
            audio.src = audioPath;
            audio.currentTime = 0;

            audio.play().then(() => {
                console.log('Audio playing:', audioPath);
            }).catch(e => {
                console.log('Audio play failed:', e);
                // Try alternative path
                audio.src = `/sounds/${milestoneData.sound}`;
                audio.play().catch(err => console.log('Audio play failed with alt path:', err));
            });

            // Show celebration animation for 100 points
            if (milestoneKey === 100) {
                triggerConfetti();
                // Add extra celebration effect
                document.getElementById('progress-bar').classList.add('animate-pulse');
                setTimeout(() => {
                    document.getElementById('progress-bar').classList.remove('animate-pulse');
                }, 3000);
            }

            // Auto hide message after duration
            const duration = (milestoneData.duration || 2) * 1000 + 1000;
            setTimeout(() => {
                milestoneMessage.className = 'mt-4 text-center font-semibold text-[#161758] transition-all duration-500 opacity-0 transform scale-95';
            }, duration);
        }
    }

    // Confetti animation for 100 points
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

    // Update progress bar with animation
    function updateProgressBar(poin) {
        const progressBar = document.getElementById('progress-bar');
        const progressText = document.getElementById('progress-text');
        const poinCounter = document.getElementById('poin-counter');

        const percentage = Math.min(poin, 100);
        progressBar.style.width = percentage + '%';
        progressText.textContent = percentage + '%';
        poinCounter.textContent = poin;

        // Update milestone dots
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

        console.log('Progress updated:', poin, 'percentage:', percentage);

        // Play milestone sound if applicable
        playMilestoneSound(poin);
    }

    function simpanChecklist(fieldName, checkbox) {
        if (isLocked) {
            checkbox.checked = !checkbox.checked;
            return;
        }

        // Field induk
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
            console.log('Response data:', d);

            // Update progress bar dengan poin baru
            updateProgressBar(d.total_poin);

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

            // Aktifkan/nonaktifkan checkbox berjamaah
            if (jamaahCheckbox) {
                jamaahCheckbox.disabled = !(mainCheckbox && mainCheckbox.checked) || isLocked;
                if (!(mainCheckbox && mainCheckbox.checked)) {
                    jamaahCheckbox.checked = false;
                }
            }

            // Update riwayat 30 hari
            const barEl = document.getElementById(`bar-${d.tanggal}`);
            const poinEl = document.getElementById(`poin-${d.tanggal}`);
            if (barEl && poinEl) {
                poinEl.textContent = d.total_poin;
                const height = d.total_poin > 0 ? Math.min(d.total_poin / 2, 32) : 8;
                barEl.style.height = `${height}px`;
                if (d.total_poin > 0) {
                    barEl.className = 'mt-1 w-full rounded transition-all duration-500 bg-gradient-to-t from-[#FCC626] via-[#2E7D3E] to-[#00a2e9]';
                } else {
                    barEl.className = 'mt-1 w-full rounded transition-all duration-500 bg-gray-200';
                }
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
        .catch((error) => {
            setSaving(checkbox, false);
            checkbox.checked = !checkbox.checked;
            console.error('Error:', error);
            alert('Terjadi kesalahan pada server');
        });
    }

    // Checklist utama sholat wajib
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

    // Checklist berjamaah
    document.querySelectorAll('.jamaah-check').forEach(checkbox => {
        checkbox.addEventListener('change', function () {
            simpanChecklist(this.dataset.field, this);
        });
    });

    // Initialize progress bar on load
    const initialPoin = {{ $todayData->total_poin ?? 0 }};
    updateProgressBar(initialPoin);

    // Trigger initial milestone if any
    if (initialPoin > 0) {
        setTimeout(() => playMilestoneSound(initialPoin), 500);
    }
});
</script>
@endsection
