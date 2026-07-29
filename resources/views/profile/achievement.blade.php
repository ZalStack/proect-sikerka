{{-- resources/views/profile/achievement.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="flex min-h-screen bg-[#f8faff]">
    @include('layouts.sidebar')
    <div class="flex-1 transition-all duration-300 md:ml-64 p-3 sm:p-6">
        <div class="flex flex-wrap justify-between items-start mb-6 gap-3">
            <div>
                <h1 class="text-2xl font-bold font-['Montserrat'] text-[#161758]">🏅 Achievement</h1>
                <p class="text-[#27438D]">Rekap capaian 7SPS, FHL, dan Khataman</p>
            </div>
            <a href="{{ route('profile.show') }}"
                class="w-full sm:w-auto bg-gray-500 text-white px-6 py-2 rounded-lg hover:bg-gray-600 transition-colors duration-200 text-center">
                <i class="fas fa-arrow-left mr-2"></i> Kembali
            </a>
        </div>

        <!-- Filter Bulan/Tahun -->
        <div class="bg-white rounded-xl shadow-lg p-4 mb-6">
            <form method="GET" class="flex flex-wrap items-end gap-4">
                <div>
                    <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Bulan</label>
                    <select name="month" class="px-4 py-2 border rounded-lg focus:ring-2 focus:ring-[#00a2e9]">
                        @for ($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" {{ $m == $month ? 'selected' : '' }}>
                                {{ Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                            </option>
                        @endfor
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Tahun</label>
                    <select name="year" class="px-4 py-2 border rounded-lg focus:ring-2 focus:ring-[#00a2e9]">
                        @for ($y = date('Y') - 2; $y <= date('Y'); $y++)
                            <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
                <button type="submit"
                    class="bg-[#27438D] text-white px-6 py-2 rounded-lg hover:bg-[#161758]">Tampilkan</button>
            </form>
        </div>

        @if (Auth::user()->isHr())
            <!-- ========== TAMPILAN HR: TABLE ========== -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-100">
                <div class="p-6">
                    <h2 class="text-xl font-bold text-[#161758] mb-4">
                        🏆 Peringkat Achievement Bulan
                        {{ Carbon\Carbon::create()->month((int) $month)->translatedFormat('F') }} {{ $year }}
                    </h2>

                    @if (!empty($englishSummary['total_quizzes']))
                        <div class="bg-[#f8faff] border border-gray-100 rounded-lg p-4 mb-4 flex flex-wrap gap-4 justify-between items-center">
                            <div>
                                <p class="text-sm text-gray-500">🇬🇧 English Today &mdash; ringkasan seluruh quiz</p>
                            </div>
                            <div class="flex gap-6 text-sm">
                                <div class="text-center">
                                    <div class="font-bold text-[#161758]">{{ $englishSummary['total_quizzes'] }}</div>
                                    <div class="text-gray-500">Quiz</div>
                                </div>
                                <div class="text-center">
                                    <div class="font-bold text-[#161758]">{{ $englishSummary['total_participants'] }}</div>
                                    <div class="text-gray-500">Peserta</div>
                                </div>
                                <div class="text-center">
                                    <div class="font-bold text-[#161758]">{{ number_format($englishSummary['average_score'], 1) }}</div>
                                    <div class="text-gray-500">Rata-rata</div>
                                </div>
                            </div>
                        </div>
                    @endif
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left border-collapse">
                            <thead class="bg-[#f8faff] text-[#27438D] uppercase text-xs">
                                <tr>
                                    <th class="px-4 py-3 border-b">#</th>
                                    <th class="px-4 py-3 border-b">Nama</th>
                                    <th class="px-4 py-3 border-b">Divisi</th>
                                    <th class="px-4 py-3 border-b text-center">Sunnah (Poin)</th>
                                    <th class="px-4 py-3 border-b text-center">FHL (%)</th>
                                    <th class="px-4 py-3 border-b text-center">Khataman (%)</th>
                                    <th class="px-4 py-3 border-b text-center">English Today</th>
                                    <th class="px-4 py-3 border-b text-center">Total Score</th>
                                    <th class="px-4 py-3 border-b text-center">Badge</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($paginatedData as $index => $item)
                                    @php
                                        $globalRank = (($paginatedData->currentPage() - 1) * $paginatedData->perPage()) + $index + 1;
                                        $isTop3 = $globalRank <= 3;
                                        $rowClass = $isTop3 ? 'bg-[#FFFDE7]' : '';
                                    @endphp
                                    <tr class="border-b hover:bg-gray-50 {{ $rowClass }}">
                                        <td class="px-4 py-3 font-bold text-center">
                                            @if ($globalRank == 1)
                                                🥇
                                            @elseif ($globalRank == 2)
                                                🥈
                                            @elseif ($globalRank == 3)
                                                🥉
                                            @else
                                                {{ $globalRank }}
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 font-semibold">{{ $item['karyawan']->nama_lengkap }}</td>
                                        <td class="px-4 py-3">{{ $item['karyawan']->divisi ?? '-' }}</td>
                                        <td class="px-4 py-3 text-center">{{ $item['sunnah']['total_poin'] }}</td>
                                        <td class="px-4 py-3 text-center">{{ $item['fhl']['percentage'] }}%</td>
                                        <td class="px-4 py-3 text-center">{{ $item['khataman']['percentage'] }}%</td>
                                        <td class="px-4 py-3 text-center">
                                            @if ($item['english_today'])
                                                <span class="font-semibold text-[#161758]">{{ $item['english_today']['average_score'] }}</span>
                                                <span class="block text-xs text-gray-400">{{ $item['english_today']['total_quizzes_taken'] }} quiz</span>
                                                @if ($item['english_today']['badge'])
                                                    <span class="block text-xs mt-0.5">{{ $item['english_today']['badge']['icon'] }} {{ $item['english_today']['badge']['name'] }}</span>
                                                @endif
                                            @else
                                                <span class="text-gray-400">Belum ikut</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-center font-bold text-[#161758]">
                                            {{ number_format($item['total_score'], 1) }}
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="flex flex-wrap gap-1 justify-center">
                                                @forelse ($item['badges'] as $badge)
                                                    <span class="inline-block px-2 py-1 rounded-full text-xs font-medium
                                                        @if ($badge['level'] == 'gold') bg-yellow-200 text-yellow-800
                                                        @elseif ($badge['level'] == 'silver') bg-gray-200 text-gray-800
                                                        @else bg-amber-200 text-amber-800 @endif">
                                                        {{ $badge['icon'] }} {{ $badge['name'] }}
                                                    </span>
                                                @empty
                                                    <span class="text-gray-400">-</span>
                                                @endforelse
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="px-4 py-6 text-center text-gray-400">Belum ada data untuk bulan ini</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>

                        <!-- Pagination -->
                        <div class="mt-4 flex flex-col sm:flex-row flex-wrap justify-between items-center gap-3 border-t border-gray-100 pt-4">
                            <div class="text-xs sm:text-sm text-gray-600 text-center sm:text-left order-2 sm:order-1">
                                Menampilkan
                                <span class="font-semibold">{{ $paginatedData->firstItem() ?? 0 }}</span> -
                                <span class="font-semibold">{{ $paginatedData->lastItem() ?? 0 }}</span>
                                dari <span class="font-semibold">{{ $paginatedData->total() }}</span> data
                            </div>

                            @if ($paginatedData->hasPages())
                                @php
                                    $paginator = $paginatedData->appends(['month' => $month, 'year' => $year]);
                                    $current = $paginator->currentPage();
                                    $last = $paginator->lastPage();
                                    $start = max(1, $current - 1);
                                    $end = min($last, $current + 1);
                                @endphp
                                <nav class="flex items-center flex-wrap justify-center gap-1 order-1 sm:order-2" aria-label="Pagination">
                                    {{-- Previous --}}
                                    @if ($paginator->onFirstPage())
                                        <span class="flex items-center px-2.5 sm:px-3 py-2 rounded-lg border border-gray-200 text-gray-300 cursor-not-allowed select-none text-sm">
                                            <i class="fas fa-chevron-left"></i>
                                            <span class="hidden sm:inline ml-1">Sebelumnya</span>
                                        </span>
                                    @else
                                        <a href="{{ $paginator->previousPageUrl() }}"
                                            class="flex items-center px-2.5 sm:px-3 py-2 rounded-lg border border-gray-200 text-[#27438D] hover:bg-[#27438D] hover:text-white transition-colors duration-200 text-sm">
                                            <i class="fas fa-chevron-left"></i>
                                            <span class="hidden sm:inline ml-1">Sebelumnya</span>
                                        </a>
                                    @endif

                                    {{-- Page numbers (desktop/tablet) --}}
                                    <div class="hidden xs:flex sm:flex items-center gap-1">
                                        @if ($start > 1)
                                            <a href="{{ $paginator->url(1) }}"
                                                class="w-9 h-9 inline-flex items-center justify-center rounded-lg border border-gray-200 text-[#27438D] hover:bg-[#27438D] hover:text-white transition-colors duration-200 text-sm">1</a>
                                            @if ($start > 2)
                                                <span class="w-9 h-9 inline-flex items-center justify-center text-gray-400 text-sm">&hellip;</span>
                                            @endif
                                        @endif

                                        @for ($page = $start; $page <= $end; $page++)
                                            @if ($page == $current)
                                                <span class="w-9 h-9 inline-flex items-center justify-center rounded-lg bg-[#27438D] text-white font-semibold text-sm">{{ $page }}</span>
                                            @else
                                                <a href="{{ $paginator->url($page) }}"
                                                    class="w-9 h-9 inline-flex items-center justify-center rounded-lg border border-gray-200 text-[#27438D] hover:bg-[#27438D] hover:text-white transition-colors duration-200 text-sm">{{ $page }}</a>
                                            @endif
                                        @endfor

                                        @if ($end < $last)
                                            @if ($end < $last - 1)
                                                <span class="w-9 h-9 inline-flex items-center justify-center text-gray-400 text-sm">&hellip;</span>
                                            @endif
                                            <a href="{{ $paginator->url($last) }}"
                                                class="w-9 h-9 inline-flex items-center justify-center rounded-lg border border-gray-200 text-[#27438D] hover:bg-[#27438D] hover:text-white transition-colors duration-200 text-sm">{{ $last }}</a>
                                        @endif
                                    </div>

                                    {{-- Compact page indicator (mobile only) --}}
                                    <span class="sm:hidden px-2 py-2 text-sm font-medium text-[#161758] whitespace-nowrap">
                                        Hal {{ $current }} / {{ $last }}
                                    </span>

                                    {{-- Next --}}
                                    @if ($paginator->hasMorePages())
                                        <a href="{{ $paginator->nextPageUrl() }}"
                                            class="flex items-center px-2.5 sm:px-3 py-2 rounded-lg border border-gray-200 text-[#27438D] hover:bg-[#27438D] hover:text-white transition-colors duration-200 text-sm">
                                            <span class="hidden sm:inline mr-1">Selanjutnya</span>
                                            <i class="fas fa-chevron-right"></i>
                                        </a>
                                    @else
                                        <span class="flex items-center px-2.5 sm:px-3 py-2 rounded-lg border border-gray-200 text-gray-300 cursor-not-allowed select-none text-sm">
                                            <span class="hidden sm:inline mr-1">Selanjutnya</span>
                                            <i class="fas fa-chevron-right"></i>
                                        </span>
                                    @endif
                                </nav>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @else
            <!-- ========== TAMPILAN KARYAWAN: KARTU ========== -->
            @php $item = $data->first(); @endphp
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Kartu Sunnah -->
                <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-[#161758]">🌙 7SPS</h3>
                        <span class="text-2xl font-bold text-[#27438D]">{{ $item['sunnah']['total_poin'] }} poin</span>
                    </div>
                    <div class="mb-2 flex justify-between text-sm">
                        <span>Rata-rata harian</span>
                        <span class="font-bold">{{ $item['sunnah']['avg'] }}</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2.5">
                        <div class="bg-[#27438D] h-2.5 rounded-full"
                            style="width: {{ min(($item['sunnah']['avg'] / 100) * 100, 100) }}%"></div>
                    </div>
                    <p class="text-xs text-gray-500 mt-2">{{ $item['sunnah']['count'] }} hari tercatat</p>
                    <div class="mt-3 flex flex-wrap gap-1">
                        @foreach ($item['badges'] as $badge)
                            @if (str_contains($badge['name'], 'Sunnah'))
                                <span class="px-3 py-1 rounded-full text-xs font-medium
                                    @if ($badge['level'] == 'gold') bg-yellow-200 text-yellow-800
                                    @elseif ($badge['level'] == 'silver') bg-gray-200 text-gray-800
                                    @else bg-amber-200 text-amber-800 @endif">
                                    {{ $badge['icon'] }} {{ $badge['name'] }}
                                </span>
                            @endif
                        @endforeach
                    </div>
                </div>

                <!-- Kartu FHL -->
                <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-[#161758]">🕌 FHL (Jumat)</h3>
                        <span class="text-2xl font-bold text-[#2E7D3E]">{{ $item['fhl']['percentage'] }}%</span>
                    </div>
                    <div class="mb-2 flex justify-between text-sm">
                        <span>Hadir {{ $item['fhl']['hadir'] }} dari {{ $item['fhl']['total'] }} Jumat</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2.5">
                        <div class="bg-[#2E7D3E] h-2.5 rounded-full" style="width: {{ $item['fhl']['percentage'] }}%"></div>
                    </div>
                    <div class="mt-3 flex flex-wrap gap-1">
                        @foreach ($item['badges'] as $badge)
                            @if (str_contains($badge['name'], 'FHL'))
                                <span class="px-3 py-1 rounded-full text-xs font-medium
                                    @if ($badge['level'] == 'gold') bg-yellow-200 text-yellow-800
                                    @elseif ($badge['level'] == 'silver') bg-gray-200 text-gray-800
                                    @else bg-amber-200 text-amber-800 @endif">
                                    {{ $badge['icon'] }} {{ $badge['name'] }}
                                </span>
                            @endif
                        @endforeach
                    </div>
                </div>

                <!-- Kartu Khataman -->
                <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-[#161758]">📖 Khataman (Kamis)</h3>
                        <span class="text-2xl font-bold text-[#FCC626]">{{ $item['khataman']['percentage'] }}%</span>
                    </div>
                    <div class="mb-2 flex justify-between text-sm">
                        <span>Hadir {{ $item['khataman']['hadir'] }} dari {{ $item['khataman']['total'] }} Kamis</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2.5">
                        <div class="bg-[#FCC626] h-2.5 rounded-full" style="width: {{ $item['khataman']['percentage'] }}%"></div>
                    </div>
                    <div class="mt-3 flex flex-wrap gap-1">
                        @foreach ($item['badges'] as $badge)
                            @if (str_contains($badge['name'], 'Khataman'))
                                <span class="px-3 py-1 rounded-full text-xs font-medium
                                    @if ($badge['level'] == 'gold') bg-yellow-200 text-yellow-800
                                    @elseif ($badge['level'] == 'silver') bg-gray-200 text-gray-800
                                    @else bg-amber-200 text-amber-800 @endif">
                                    {{ $badge['icon'] }} {{ $badge['name'] }}
                                </span>
                            @endif
                        @endforeach
                    </div>
                </div>

                <!-- Kartu English Today -->
                <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-[#161758]">🇬🇧 English Today</h3>
                        @if ($item['english_today'])
                            <span class="text-2xl font-bold text-[#00a2e9]">{{ $item['english_today']['average_score'] }}</span>
                        @endif
                    </div>

                    @if ($item['english_today'])
                        <div class="mb-2 flex justify-between text-sm">
                            <span>Rata-rata dari {{ $item['english_today']['total_quizzes_taken'] }} quiz</span>
                            <span class="font-bold">Terbaik: {{ $item['english_today']['best_score'] }}</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2.5 mb-3">
                            <div class="bg-[#00a2e9] h-2.5 rounded-full" style="width: {{ min($item['english_today']['average_score'], 100) }}%"></div>
                        </div>

                        <div class="mt-3 flex flex-wrap gap-1 mb-3">
                            @if ($item['english_today']['badge'])
                                @php $badge = $item['english_today']['badge']; @endphp
                                <span class="px-3 py-1 rounded-full text-xs font-medium
                                    @if ($badge['level'] == 'gold') bg-yellow-200 text-yellow-800
                                    @elseif ($badge['level'] == 'silver') bg-gray-200 text-gray-800
                                    @else bg-amber-200 text-amber-800 @endif">
                                    {{ $badge['icon'] }} {{ $badge['name'] }}
                                </span>
                            @endif
                        </div>

                        <!-- Rincian per quiz -->
                        <div class="border-t border-gray-100 pt-3 space-y-2 max-h-40 overflow-y-auto">
                            @foreach ($item['english_today']['attempts'] as $attempt)
                                <div class="flex justify-between items-center text-xs">
                                    <span class="text-gray-600 truncate pr-2">{{ $attempt['quiz_title'] }}</span>
                                    <span class="font-semibold text-[#161758] whitespace-nowrap">{{ $attempt['score'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-gray-400">Belum mengikuti quiz English Today.</p>
                    @endif
                </div>
            </div>

            <!-- Daftar semua badge yang diperoleh -->
            @php
                $allBadges = $item['badges'];
                if (!empty($item['english_today']['badge'])) {
                    $allBadges[] = $item['english_today']['badge'];
                }
            @endphp
            @if (count($allBadges) > 0)
                <div class="bg-white rounded-xl shadow-lg p-6 mt-6 border border-gray-100">
                    <h3 class="text-lg font-semibold text-[#161758] mb-3">🏅 Semua Lencana</h3>
                    <div class="flex flex-wrap gap-3">
                        @foreach ($allBadges as $badge)
                            <div class="flex items-center space-x-2 bg-[#f8faff] px-4 py-2 rounded-full border border-gray-200">
                                <span class="text-xl">{{ $badge['icon'] }}</span>
                                <span class="font-medium">{{ $badge['name'] }}</span>
                                <span class="text-xs uppercase px-2 py-0.5 rounded-full
                                    @if ($badge['level'] == 'gold') bg-yellow-200 text-yellow-800
                                    @elseif ($badge['level'] == 'silver') bg-gray-200 text-gray-800
                                    @else bg-amber-200 text-amber-800 @endif">
                                    {{ $badge['level'] }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        @endif
    </div>
</div>
@endsection
