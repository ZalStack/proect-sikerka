@extends('layouts.app')

@section('content')
    <div class="flex min-h">
        @include('layouts.sidebar')
        <div class="flex-1 transition-all duration-300 md:ml-64">
            <div class="p-4 sm:p-6 lg:p-8">
                <!-- Header -->
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
                    <div>
                        <h1 class="text-2xl sm:text-3xl font-bold text-[#161758] tracking-tight">Resume Absensi</h1>
                        <p class="text-sm text-gray-500 mt-1">Ringkasan performa karyawan berdasarkan data absensi</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                        <button type="button" id="btnSalinTeks"
                            class="inline-flex items-center justify-center px-4 py-2.5 bg-[#27438D] text-white rounded-xl hover:bg-[#161758] transition-all duration-200 text-sm font-medium shadow-sm hover:shadow-md gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3" />
                            </svg>
                            <span class="hidden sm:inline">Salin ke Teks</span>
                            <span class="sm:hidden">Salin</span>
                        </button>
                        <a href="{{ route('hr.absensi.index', request()->query()) }}"
                            class="inline-flex items-center justify-center px-4 py-2.5 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 transition-all duration-200 text-sm font-medium shadow-sm hover:shadow-md gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                            <span class="hidden sm:inline">Kembali ke Absensi</span>
                            <span class="sm:hidden">Kembali</span>
                        </a>
                    </div>
                </div>

                <!-- Filter -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 hover:shadow-lg transition-all duration-300 p-5 sm:p-6 mb-8">
                    <form action="{{ route('hr.absensi.resume') }}" method="GET"
                        class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Bulan</label>
                            <select name="month"
                                class="w-full px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-[#00a2e9] focus:border-transparent transition">
                                <option value="">Semua Bulan</option>
                                @php
                                    $bulanList = [
                                        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                                        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                                        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
                                    ];
                                    $activeMonth = request('month', $selectedMonth ?? null);
                                @endphp
                                @foreach ($bulanList as $num => $label)
                                    <option value="{{ $num }}" {{ (int) $activeMonth === $num ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Tahun</label>
                            @php
                                $activeYear = request('year', $selectedYear ?? null);
                                $currentYear = (int) \Carbon\Carbon::now('Asia/Jakarta')->year;
                            @endphp
                            <select name="year"
                                class="w-full px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-[#00a2e9] focus:border-transparent transition">
                                <option value="">Semua Tahun</option>
                                @for ($y = $currentYear; $y >= $currentYear - 4; $y--)
                                    <option value="{{ $y }}" {{ (int) $activeYear === $y ? 'selected' : '' }}>
                                        {{ $y }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Dari Tanggal</label>
                            <input type="date" name="start_date" value="{{ request('start_date') }}"
                                class="w-full px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-[#00a2e9] focus:border-transparent transition">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Sampai Tanggal</label>
                            <input type="date" name="end_date" value="{{ request('end_date') }}"
                                class="w-full px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-[#00a2e9] focus:border-transparent transition">
                        </div>
                        <div class="flex items-end gap-2">
                            <button type="submit"
                                class="flex-1 px-4 py-2.5 bg-[#00a2e9] text-white rounded-xl hover:bg-[#0088c4] transition-all duration-200 text-sm font-medium shadow-sm hover:shadow-md">
                                Filter
                            </button>
                            <a href="{{ route('hr.absensi.resume') }}"
                                class="flex-1 text-center px-4 py-2.5 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 transition-all duration-200 text-sm font-medium">
                                Reset
                            </a>
                        </div>
                    </form>
                    <p class="text-xs text-gray-400 mt-3">
                        * Kalau "Dari Tanggal" &amp; "Sampai Tanggal" diisi, filter Bulan/Tahun akan diabaikan.
                    </p>
                </div>

                <!-- Statistik Ringkas -->
                <div id="statistikRingkas" class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-3 sm:gap-4 mb-8">
                    @php
                        $stats = [
                            ['label' => 'Total Absensi', 'value' => $totalAbsensi, 'color' => 'border-[#161758]', 'text' => 'text-[#161758]'],
                            ['label' => 'Hadir', 'value' => $totalHadir, 'color' => 'border-[#2E7D3E]', 'text' => 'text-[#2E7D3E]'],
                            ['label' => 'Terlambat', 'value' => $totalHariTerlambat, 'color' => 'border-[#ec1d1d]', 'text' => 'text-[#ec1d1d]'],
                            ['label' => 'Izin', 'value' => $totalIzin, 'color' => 'border-[#FCC626]', 'text' => 'text-[#b58a00]'],
                            ['label' => 'Sakit', 'value' => $totalSakit, 'color' => 'border-[#00a2e9]', 'text' => 'text-[#00a2e9]'],
                            ['label' => 'Alpha', 'value' => $totalAlpha, 'color' => 'border-[#ec1d1d]', 'text' => 'text-[#ec1d1d]'],
                            ['label' => 'Rata-rata Jam Kerja', 'value' => $rataRataJamKerja . ' jam', 'color' => 'border-purple-600', 'text' => 'text-purple-600'],
                        ];
                    @endphp
                    @foreach ($stats as $stat)
                        <div class="stat-card bg-white rounded-2xl shadow-sm border border-slate-100 hover:shadow-lg transition-all duration-300 p-4 border-l-4 {{ $stat['color'] }}">
                            <p class="stat-label text-xs font-medium text-gray-400 uppercase tracking-wider">{{ $stat['label'] }}</p>
                            <p class="stat-value text-xl font-bold {{ $stat['text'] }} mt-1">{{ $stat['value'] }}</p>
                        </div>
                    @endforeach
                </div>

                <!-- Top 10 Jam Kerja -->
                <div id="topJamKerja" class="bg-white rounded-2xl shadow-sm border border-slate-100 hover:shadow-lg transition-all duration-300 overflow-hidden mb-8">
                    <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-[#2E7D3E]/10 flex items-center justify-center">
                            <svg class="w-5 h-5 text-[#2E7D3E]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-lg font-bold text-[#161758]">Top 10 — Paling Banyak Jam Kerja</h2>
                            <p class="text-xs text-gray-400">Karyawan dengan total jam kerja terbanyak pada periode ini</p>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-100">
                            <thead class="bg-gray-50/80">
                                <tr>
                                    <th class="px-6 py-3.5 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider w-16">Ranking</th>
                                    <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Karyawan</th>
                                    <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider hidden md:table-cell">Divisi</th>
                                    <th class="px-6 py-3.5 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Jam Kerja</th>
                                    <th class="px-6 py-3.5 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider hidden sm:table-cell">Hari Hadir</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100">
                                @forelse($topJamKerja as $index => $item)
                                    @php
                                        $rank = $index + 1;
                                        $badgeClass = match($rank) {
                                            1 => 'bg-yellow-400 text-yellow-900',
                                            2 => 'bg-gray-300 text-gray-700',
                                            3 => 'bg-amber-600 text-white',
                                            default => 'bg-gray-100 text-gray-600',
                                        };
                                    @endphp
                                    <tr class="hover:bg-gray-50/70 transition-colors">
                                        <td class="px-6 py-4 text-center">
                                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-full text-sm font-bold {{ $badgeClass }}">
                                                {{ $rank }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div>
                                                <p class="text-sm font-medium text-gray-900">{{ $item['karyawan']->nama_lengkap ?? '-' }}</p>
                                                <p class="text-xs text-gray-500">{{ $item['karyawan']->kode_pegawai ?? '-' }}</p>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 hidden md:table-cell">
                                            <span class="text-sm text-gray-700">{{ $item['karyawan']->divisi ?? '-' }}</span>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <span class="text-sm font-bold text-[#2E7D3E]">
                                                {{ $item['total_jam'] }} jam {{ $item['total_sisa_menit'] }} mnt
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-center hidden sm:table-cell">
                                            <span class="text-sm font-medium text-gray-700">{{ $item['jumlah_hari'] }} hari</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-12 text-center">
                                            <div class="flex flex-col items-center justify-center text-gray-400">
                                                <svg class="w-12 h-12 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                </svg>
                                                <p class="text-sm font-medium">Belum ada data hadir pada periode ini.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Bottom 5 Paling Sering Telat -->
                <div id="bottomTelat" class="bg-white rounded-2xl shadow-sm border border-slate-100 hover:shadow-lg transition-all duration-300 overflow-hidden mb-8">
                    <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-[#ec1d1d]/10 flex items-center justify-center">
                            <svg class="w-5 h-5 text-[#ec1d1d]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-lg font-bold text-[#161758]">Bottom 5 — Paling Sering Telat</h2>
                            <p class="text-xs text-gray-400">Karyawan dengan jumlah keterlambatan check-in terbanyak</p>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-100">
                            <thead class="bg-gray-50/80">
                                <tr>
                                    <th class="px-6 py-3.5 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider w-16">Ranking</th>
                                    <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Karyawan</th>
                                    <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider hidden md:table-cell">Divisi</th>
                                    <th class="px-6 py-3.5 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Hari Telat</th>
                                    <th class="px-6 py-3.5 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider hidden sm:table-cell">Total Telat</th>
                                    <th class="px-6 py-3.5 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider hidden lg:table-cell">Rata-rata</th>
                                    <th class="px-6 py-3.5 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider hidden lg:table-cell">Terparah</th>
                                    <th class="px-6 py-3.5 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Keterangan</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100">
                                @forelse($bottomTelat as $index => $item)
                                    @php
                                        $rank = $index + 1;
                                        $badgeClass = match($rank) {
                                            1 => 'bg-[#ec1d1d] text-white',
                                            2 => 'bg-orange-500 text-white',
                                            3 => 'bg-amber-500 text-white',
                                            default => 'bg-gray-100 text-gray-600',
                                        };
                                        $ketClass = match($item['keterangan']) {
                                            'Sangat Sering Telat' => 'bg-red-100 text-red-700',
                                            'Sering Telat' => 'bg-orange-100 text-orange-700',
                                            default => 'bg-yellow-100 text-yellow-700',
                                        };
                                    @endphp
                                    <tr class="hover:bg-gray-50/70 transition-colors">
                                        <td class="px-6 py-4 text-center">
                                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-full text-sm font-bold {{ $badgeClass }}">
                                                {{ $rank }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div>
                                                <p class="text-sm font-medium text-gray-900">{{ $item['karyawan']->nama_lengkap ?? '-' }}</p>
                                                <p class="text-xs text-gray-500">{{ $item['karyawan']->kode_pegawai ?? '-' }}</p>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 hidden md:table-cell">
                                            <span class="text-sm text-gray-700">{{ $item['karyawan']->divisi ?? '-' }}</span>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <span class="text-sm font-bold text-[#ec1d1d]">{{ $item['jumlah_hari_telat'] }} hari</span>
                                        </td>
                                        <td class="px-6 py-4 text-center hidden sm:table-cell">
                                            <span class="text-sm font-medium text-gray-700">{{ $item['total_menit_telat'] }} menit</span>
                                        </td>
                                        <td class="px-6 py-4 text-center hidden lg:table-cell">
                                            <span class="text-sm text-gray-700">{{ $item['rata_rata'] }} mnt/hari</span>
                                        </td>
                                        <td class="px-6 py-4 text-center hidden lg:table-cell">
                                            <span class="text-xs text-gray-500">{{ $item['terparah_checkin'] }}</span>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <span class="inline-block px-2.5 py-1 rounded-full text-xs font-semibold {{ $ketClass }}">
                                                {{ $item['keterangan'] }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="px-6 py-12 text-center">
                                            <div class="flex flex-col items-center justify-center text-gray-400">
                                                <svg class="w-12 h-12 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                </svg>
                                                <p class="text-sm font-medium">Tidak ada data keterlambatan pada periode ini.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Keterangan -->
                <p class="text-xs text-gray-400 mt-6 leading-relaxed">
                    * Total jam kerja dihitung dari selisih waktu check-in ke check-out (akurat per menit).
                    <br>
                    * Keterangan keterlambatan: <span class="font-semibold text-red-600">Sangat Sering Telat</span> (total &gt; 60 menit),
                    <span class="font-semibold text-orange-600">Sering Telat</span> (total 31-60 menit),
                    <span class="font-semibold text-yellow-600">Kadang Telat</span> (total 1-30 menit).
                    <br>
                    * Filter yang diterapkan di halaman ini sama dengan filter di halaman absensi utama.
                </p>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var btn = document.getElementById('btnSalinTeks');
            if (!btn) return;

            btn.addEventListener('click', function () {
                var lines = [];

                lines.push('RESUME ABSENSI KARYAWAN');
                lines.push('Dicetak pada ' + new Date().toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }) + ' ' + new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) + ' WIB');
                lines.push('');

                // Statistik Ringkas
                lines.push('=== STATISTIK RINGKAS ===');
                var statCards = document.querySelectorAll('#statistikRingkas .stat-card');
                statCards.forEach(function (card) {
                    var label = card.querySelector('.stat-label');
                    var value = card.querySelector('.stat-value');
                    if (label && value) {
                        lines.push(label.textContent.trim() + ': ' + value.textContent.trim());
                    }
                });
                lines.push('');

                // Top 10 Jam Kerja
                lines.push('=== TOP 10 — PALING BANYAK JAM KERJA ===');
                var topRows = document.querySelectorAll('#topJamKerja tbody tr');
                if (topRows.length === 0 || (topRows.length === 1 && topRows[0].querySelector('td[colspan]'))) {
                    lines.push('(Tidak ada data)');
                } else {
                    topRows.forEach(function (row) {
                        var cells = row.querySelectorAll('td');
                        if (cells.length >= 4) {
                            var ranking = cells[0].textContent.trim();
                            var nama = cells[1].querySelector('p:first-child') ? cells[1].querySelector('p:first-child').textContent.trim() : cells[1].textContent.trim();
                            var kode = cells[1].querySelector('p:last-child') ? cells[1].querySelector('p:last-child').textContent.trim() : '';
                            var divisi = cells[2] ? cells[2].textContent.trim() : '-';
                            var jamKerja = cells[3].textContent.trim();
                            var hari = cells[4] ? cells[4].textContent.trim() : '';
                            lines.push(ranking + '. ' + nama + ' (' + kode + ') — ' + divisi + ' — ' + jamKerja + ' — ' + hari);
                        }
                    });
                }
                lines.push('');

                // Bottom 5 Telat
                lines.push('=== BOTTOM 5 — PALING SERING TELAT ===');
                var bottomRows = document.querySelectorAll('#bottomTelat tbody tr');
                if (bottomRows.length === 0 || (bottomRows.length === 1 && bottomRows[0].querySelector('td[colspan]'))) {
                    lines.push('(Tidak ada data)');
                } else {
                    bottomRows.forEach(function (row) {
                        var cells = row.querySelectorAll('td');
                        if (cells.length >= 5) {
                            var ranking = cells[0].textContent.trim();
                            var nama = cells[1].querySelector('p:first-child') ? cells[1].querySelector('p:first-child').textContent.trim() : cells[1].textContent.trim();
                            var kode = cells[1].querySelector('p:last-child') ? cells[1].querySelector('p:last-child').textContent.trim() : '';
                            var divisi = cells[2] ? cells[2].textContent.trim() : '-';
                            var hariTelat = cells[3].textContent.trim();
                            var totalTelat = cells[4] ? cells[4].textContent.trim() : '';
                            var rata2 = cells[5] ? cells[5].textContent.trim() : '';
                            var terparah = cells[6] ? cells[6].textContent.trim() : '';
                            var keterangan = cells[7] ? cells[7].textContent.trim() : '';
                            lines.push(ranking + '. ' + nama + ' (' + kode + ') — ' + divisi + ' — ' + hariTelat + ' — ' + totalTelat + ' — ' + rata2 + ' — ' + terparah + ' — ' + keterangan);
                        }
                    });
                }

                var text = lines.join('\n');

                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(text).then(function () {
                        showCopyToast();
                    }).catch(function () {
                        fallbackCopy(text);
                    });
                } else {
                    fallbackCopy(text);
                }
            });

            function fallbackCopy(text) {
                var textarea = document.createElement('textarea');
                textarea.value = text;
                textarea.style.position = 'fixed';
                textarea.style.left = '-9999px';
                textarea.style.top = '-9999px';
                document.body.appendChild(textarea);
                textarea.focus();
                textarea.select();
                try {
                    document.execCommand('copy');
                    showCopyToast();
                } catch (e) {
                    alert('Gagal menyalin teks. Silakan salin manual.');
                }
                document.body.removeChild(textarea);
            }

            function showCopyToast() {
                var existing = document.getElementById('copyToast');
                if (existing) existing.remove();

                var toast = document.createElement('div');
                toast.id = 'copyToast';
                toast.className = 'fixed bottom-6 left-1/2 -translate-x-1/2 z-[9999] px-5 py-3 bg-[#2E7D3E] text-white text-sm font-medium rounded-xl shadow-lg transition-all duration-300 opacity-0 translate-y-2';
                toast.innerHTML = '<div class="flex items-center gap-2"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg><span>Berhasil disalin ke clipboard!</span></div>';
                document.body.appendChild(toast);

                requestAnimationFrame(function () {
                    toast.classList.remove('opacity-0', 'translate-y-2');
                    toast.classList.add('opacity-100', 'translate-y-0');
                });

                setTimeout(function () {
                    toast.classList.remove('opacity-100', 'translate-y-0');
                    toast.classList.add('opacity-0', 'translate-y-2');
                    setTimeout(function () { toast.remove(); }, 300);
                }, 2500);
            }
        });
    </script>
@endsection
