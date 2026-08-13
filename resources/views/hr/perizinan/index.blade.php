{{-- views/hr/perizinan/index.blade.php --}}
@extends('layouts.app')

@section('content')
    <div class="flex min-h-screen bg-gray-50">
        @include('layouts.sidebar')
        <div class="flex-1 transition-all duration-300 md:ml-64">
            <div class="p-4 sm:p-6 lg:p-8">
                <!-- Header -->
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
                    <div>
                        <h1 class="text-2xl sm:text-3xl font-bold text-[#161758] tracking-tight">Perizinan Karyawan</h1>
                        <p class="text-sm text-gray-500 mt-1">Review &amp; setujui pengajuan izin/sakit dari karyawan
                        </p>
                    </div>
                </div>

                {{-- Alert --}}
                @if (session('success'))
                    <div class="mb-6 flex items-start gap-3 rounded-xl border-l-4 border-[#2E7D3E] bg-green-50 p-4 text-sm text-[#1e5128] shadow-sm">
                        <span class="text-lg leading-none">✅</span>
                        <p>{{ session('success') }}</p>
                    </div>
                @endif
                @if (session('error'))
                    <div class="mb-6 flex items-start gap-3 rounded-xl border-l-4 border-[#ec1d1d] bg-red-50 p-4 text-sm text-[#8a1414] shadow-sm">
                        <span class="text-lg leading-none">❌</span>
                        <p>{{ session('error') }}</p>
                    </div>
                @endif

                <!-- Tabs / Stat Cards -->
                <div class="grid grid-cols-3 gap-3 sm:gap-4 mb-8">
                    @php
                        $tabs = [
                            'pending' => ['label' => 'Menunggu', 'icon' => '⏳', 'color' => 'border-[#FCC626]', 'text' => 'text-[#b58a00]'],
                            'approved' => ['label' => 'Disetujui', 'icon' => '✅', 'color' => 'border-[#2E7D3E]', 'text' => 'text-[#2E7D3E]'],
                            'rejected' => ['label' => 'Ditolak', 'icon' => '❌', 'color' => 'border-[#ec1d1d]', 'text' => 'text-[#ec1d1d]'],
                        ];
                    @endphp
                    @foreach ($tabs as $key => $tab)
                        <a href="{{ route('hr.perizinan.index', array_merge(request()->except(['status', 'page']), ['status' => $key])) }}"
                            class="bg-white rounded-2xl shadow-sm hover:shadow-md transition-shadow p-4 border-l-4 {{ $tab['color'] }} {{ $status === $key ? 'ring-2 ring-offset-1 ring-[#00a2e9]' : '' }}">
                            <p class="text-xs font-medium text-gray-400 uppercase tracking-wider">{{ $tab['icon'] }}
                                {{ $tab['label'] }}</p>
                            <p class="text-xl font-bold {{ $tab['text'] }} mt-1">{{ $counts[$key] }}</p>
                        </a>
                    @endforeach
                </div>

                <!-- Filter -->
                <div class="bg-white rounded-2xl shadow-sm p-5 sm:p-6 mb-6">
                    <form action="{{ route('hr.perizinan.index') }}" method="GET"
                        class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                        <input type="hidden" name="status" value="{{ $status }}">
                        <div>
                            <label
                                class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Karyawan</label>
                            <select name="karyawan_id"
                                class="w-full px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-[#00a2e9] focus:border-transparent transition">
                                <option value="">Semua Karyawan</option>
                                @foreach ($karyawans as $k)
                                    <option value="{{ $k->id }}"
                                        {{ request('karyawan_id') == $k->id ? 'selected' : '' }}>
                                        {{ $k->nama_lengkap }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label
                                class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Jenis</label>
                            <select name="jenis"
                                class="w-full px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-[#00a2e9] focus:border-transparent transition">
                                <option value="">Semua Jenis</option>
                                @foreach (\App\Models\Perizinan::JENIS as $jenis)
                                    <option value="{{ $jenis }}" {{ request('jenis') == $jenis ? 'selected' : '' }}>
                                        {{ $jenis }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Dari
                                Tanggal</label>
                            <input type="date" name="start_date" value="{{ request('start_date') }}"
                                class="w-full px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-[#00a2e9] focus:border-transparent transition">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Sampai
                                Tanggal</label>
                            <input type="date" name="end_date" value="{{ request('end_date') }}"
                                class="w-full px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-[#00a2e9] focus:border-transparent transition">
                        </div>
                        <div class="flex items-end gap-2">
                            <button type="submit"
                                class="flex-1 px-4 py-2.5 bg-[#00a2e9] text-white rounded-xl hover:bg-[#0088c4] transition-all duration-200 text-sm font-medium shadow-sm hover:shadow-md">
                                Filter
                            </button>
                            <a href="{{ route('hr.perizinan.index', ['status' => $status]) }}"
                                class="flex-1 text-center px-4 py-2.5 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 transition-all duration-200 text-sm font-medium">
                                Reset
                            </a>
                        </div>
                    </form>
                </div>

                <!-- Table -->
                <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-100">
                            <thead class="bg-gray-50/80">
                                <tr>
                                    <th scope="col"
                                        class="px-4 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                        Karyawan</th>
                                    <th scope="col"
                                        class="px-4 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                        Tanggal</th>
                                    <th scope="col"
                                        class="px-4 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                        Jenis</th>
                                    <th scope="col"
                                        class="px-4 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider hidden md:table-cell">
                                        Keterangan Karyawan</th>
                                    @if ($status !== 'pending')
                                        <th scope="col"
                                            class="px-4 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider hidden lg:table-cell">
                                            Catatan HRD</th>
                                    @endif
                                    <th scope="col"
                                        class="px-4 py-3.5 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                        Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100">
                                @forelse($perizinan as $item)
                                    <tr class="hover:bg-gray-50/70 transition-colors">
                                        <td class="px-4 py-3.5">
                                            <p class="text-sm font-medium text-gray-900">
                                                {{ $item->karyawan->nama_lengkap ?? '-' }}</p>
                                            <p class="text-xs text-gray-500">{{ $item->karyawan->kode_pegawai ?? '-' }}
                                            </p>
                                        </td>
                                        <td class="px-4 py-3.5">
                                            <p class="text-sm text-gray-700 whitespace-nowrap">
                                                {{ $item->tanggal_mulai->format('d/m/Y') }}
                                                @if (!$item->tanggal_mulai->isSameDay($item->tanggal_selesai))
                                                    <span class="text-gray-400">—</span>
                                                    {{ $item->tanggal_selesai->format('d/m/Y') }}
                                                @endif
                                            </p>
                                            <p class="text-xs text-gray-400">{{ $item->jumlah_hari }} hari &middot;
                                                diajukan {{ $item->created_at->format('d/m/Y H:i') }}</p>
                                            {{-- Mobile: tampilkan keterangan --}}
                                            <p class="text-xs text-gray-500 mt-1 md:hidden max-w-[180px]">
                                                {{ $item->keterangan }}</p>
                                        </td>
                                        <td class="px-4 py-3.5">
                                            @php
                                                $jenisColor = $item->jenis === 'Sakit' ? 'bg-[#00a2e9] text-white' : 'bg-[#FCC626] text-[#1B1B1B]';
                                            @endphp
                                            <span
                                                class="inline-block px-2.5 py-1 rounded-full text-xs font-semibold {{ $jenisColor }}">
                                                {{ $item->jenis }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3.5 hidden md:table-cell max-w-xs">
                                            <p class="text-sm text-gray-700">{{ $item->keterangan }}</p>
                                        </td>
                                        @if ($status !== 'pending')
                                            <td class="px-4 py-3.5 hidden lg:table-cell max-w-xs">
                                                <p class="text-sm text-gray-700">{{ $item->catatan_hr ?? '-' }}</p>
                                                @if ($item->approver)
                                                    <p class="text-xs text-gray-400 mt-0.5">oleh
                                                        {{ $item->approver->nama_lengkap }}</p>
                                                @endif
                                            </td>
                                        @endif
                                        <td class="px-4 py-3.5 text-right">
                                            @if ($item->status === 'pending')
                                                <div class="flex items-center justify-end gap-2">
                                                    <form method="POST"
                                                        action="{{ route('hr.perizinan.approve', $item->id) }}"
                                                        onsubmit="return confirm('Setujui pengajuan {{ $item->jenis }} atas nama {{ $item->karyawan->nama_lengkap ?? '' }}?')">
                                                        @csrf
                                                        <button type="submit"
                                                            class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-semibold bg-[#2E7D3E] text-white hover:bg-[#256b34] transition-colors">
                                                            ✅ Setujui
                                                        </button>
                                                    </form>
                                                    <button type="button"
                                                        onclick="openRejectModal({{ $item->id }}, '{{ $item->karyawan->nama_lengkap ?? '' }}')"
                                                        class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-semibold bg-[#ec1d1d] text-white hover:bg-red-700 transition-colors">
                                                        ❌ Tolak
                                                    </button>
                                                </div>
                                            @else
                                                <form method="POST" action="{{ route('hr.perizinan.reset', $item->id) }}"
                                                    onsubmit="return confirm('Kembalikan pengajuan ini ke status Menunggu Persetujuan?')">
                                                    @csrf
                                                    <button type="submit"
                                                        class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-medium bg-gray-100 text-gray-700 hover:bg-gray-200 transition-colors whitespace-nowrap">
                                                        ↩️ Reset
                                                    </button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-4 py-12 text-center">
                                            <div class="flex flex-col items-center justify-center text-gray-400">
                                                <svg class="w-12 h-12 mb-2" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="1.5"
                                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                </svg>
                                                <p class="text-sm font-medium">Tidak ada pengajuan untuk filter ini.
                                                </p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if ($perizinan->total() > 0)
                        <div class="border-t border-gray-100 px-4 py-4">
                            {{ $perizinan->onEachSide(1)->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- ============================== --}}
    {{-- MODAL TOLAK PENGAJUAN            --}}
    {{-- ============================== --}}
    <div id="rejectModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-5 sm:p-6">
            <h3 class="text-lg font-bold text-[#161758] mb-1">❌ Tolak Pengajuan</h3>
            <p class="text-sm text-gray-500 mb-4">
                Menolak pengajuan atas nama <span id="rejectKaryawanName" class="font-semibold text-gray-700"></span>.
                Mohon isi alasan penolakan supaya karyawan mengetahui penyebabnya.
            </p>
            <form id="rejectForm" method="POST" action="">
                @csrf
                <label class="block text-xs font-medium text-gray-600 mb-1">Alasan Penolakan <span
                        class="text-[#ec1d1d]">*</span></label>
                <textarea name="catatan_hr" rows="3" required minlength="3" maxlength="1000"
                    placeholder="Contoh: Tidak melampirkan surat dokter, silakan lengkapi terlebih dahulu."
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#ec1d1d] resize-none"></textarea>

                <div class="flex items-center justify-end gap-2 mt-4">
                    <button type="button" onclick="closeRejectModal()"
                        class="px-4 py-2 rounded-lg text-sm font-medium bg-gray-100 text-gray-700 hover:bg-gray-200 transition-colors">
                        Batal
                    </button>
                    <button type="submit"
                        class="px-4 py-2 rounded-lg text-sm font-semibold bg-[#ec1d1d] text-white hover:bg-red-700 transition-colors">
                        Tolak Pengajuan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openRejectModal(id, karyawanName) {
            const modal = document.getElementById('rejectModal');
            const form = document.getElementById('rejectForm');
            const nameEl = document.getElementById('rejectKaryawanName');

            form.action = `{{ url('/hr/perizinan') }}/${id}/reject`;
            nameEl.textContent = karyawanName || 'karyawan ini';

            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.style.overflow = 'hidden';
        }

        function closeRejectModal() {
            const modal = document.getElementById('rejectModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.style.overflow = '';
            document.getElementById('rejectForm').reset();
        }

        // Tutup modal kalau klik area gelap di luar box
        document.getElementById('rejectModal').addEventListener('click', function(e) {
            if (e.target === this) closeRejectModal();
        });
    </script>
@endsection
