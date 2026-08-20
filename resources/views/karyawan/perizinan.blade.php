{{-- views/karyawan/perizinan.blade.php --}}
@extends('layouts.app')

@section('content')
    <div class="flex min-h">
        @include('layouts.sidebar')
        <div class="flex-1 transition-all duration-300 md:ml-64">
            <div class="p-3 sm:p-4 lg:p-6">
                <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div>
                        <h1 class="text-xl sm:text-2xl font-bold font-['Montserrat'] text-[#161758]">Perizinan (Izin /
                            Sakit)</h1>
                        <p class="text-sm sm:text-base text-[#27438D]">Ajukan izin/sakit dan pantau status
                            persetujuannya dari HRD</p>
                    </div>
                    <a href="{{ route('karyawan.absensi') }}"
                        class="inline-flex items-center justify-center gap-2 bg-[#27438D] text-white px-4 py-2 rounded-lg hover:bg-[#161758] transition-colors duration-200 text-sm font-medium">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Kembali ke Absensi
                    </a>
                </div>

                {{-- Alert --}}
                @if (session('success'))
                    <div class="mb-4 flex items-start gap-3 rounded-lg border-l-4 border-[#2E7D3E] bg-green-50 p-4 text-sm text-[#1e5128]">
                        <span class="text-lg leading-none">✅</span>
                        <p>{{ session('success') }}</p>
                    </div>
                @endif
                @if (session('error'))
                    <div class="mb-4 flex items-start gap-3 rounded-lg border-l-4 border-[#ec1d1d] bg-red-50 p-4 text-sm text-[#8a1414]">
                        <span class="text-lg leading-none">❌</span>
                        <p>{{ session('error') }}</p>
                    </div>
                @endif
                @if ($errors->any())
                    <div class="mb-4 rounded-lg border-l-4 border-[#ec1d1d] bg-red-50 p-4 text-sm text-[#8a1414]">
                        <p class="font-semibold mb-1">⚠️ Mohon periksa kembali form Anda:</p>
                        <ul class="list-disc list-inside space-y-0.5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- ============================== --}}
                {{-- FORM PENGAJUAN IZIN / SAKIT     --}}
                {{-- ============================== --}}
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 hover:shadow-lg transition-all duration-300 p-4 sm:p-6 mb-6">
                    <h2 class="text-lg font-semibold text-[#161758] mb-1">📝 Ajukan Izin / Sakit</h2>
                    <p class="text-xs sm:text-sm text-gray-500 mb-4">
                        Pengajuan akan dikirim ke HRD untuk disetujui. Setelah disetujui, tanggal yang diajukan
                        otomatis tercatat sebagai Izin/Sakit di rekap absensi Anda.
                    </p>

                    <form method="POST" action="{{ route('karyawan.absensi.perizinan.store') }}"
                        class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
                        @csrf
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Jenis <span
                                    class="text-[#ec1d1d]">*</span></label>
                            <select name="jenis" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                                <option value="" disabled selected>Pilih jenis</option>
                                @foreach (\App\Models\Perizinan::JENIS as $jenis)
                                    <option value="{{ $jenis }}" {{ old('jenis') === $jenis ? 'selected' : '' }}>
                                        {{ $jenis }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Tanggal Mulai <span
                                    class="text-[#ec1d1d]">*</span></label>
                            <input type="date" name="tanggal_mulai" value="{{ old('tanggal_mulai') }}" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Tanggal Selesai <span
                                    class="text-[#ec1d1d]">*</span></label>
                            <input type="date" name="tanggal_selesai" value="{{ old('tanggal_selesai') }}" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                            <p class="text-[10px] text-gray-400 mt-1">Isi sama dengan Tanggal Mulai kalau cuma 1 hari.
                            </p>
                        </div>
                        <div class="flex items-end">
                            <button type="submit"
                                class="w-full inline-flex items-center justify-center gap-2 bg-[#00a2e9] text-white px-4 py-2 rounded-lg hover:bg-[#0088c4] transition-colors duration-200 text-sm font-semibold">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4v16m8-8H4" />
                                </svg>
                                Ajukan Sekarang
                            </button>
                        </div>
                        <div class="sm:col-span-2 lg:col-span-4">
                            <label class="block text-xs font-medium text-gray-600 mb-1">Keterangan / Alasan <span
                                    class="text-[#ec1d1d]">*</span></label>
                            <textarea name="keterangan" rows="2" required minlength="5" maxlength="1000"
                                placeholder="Jelaskan alasan izin/sakit Anda secara singkat..."
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#00a2e9] resize-none">{{ old('keterangan') }}</textarea>
                        </div>
                    </form>
                </div>

                {{-- ============================== --}}
                {{-- RIWAYAT PENGAJUAN                --}}
                {{-- ============================== --}}
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 hover:shadow-lg transition-all duration-300 p-4 sm:p-6">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
                        <h2 class="text-lg font-semibold text-[#161758]">Riwayat Pengajuan Saya</h2>
                    </div>

                    <!-- Filter -->
                    <form method="GET" action="{{ route('karyawan.absensi.perizinan') }}"
                        class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 mb-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Dari Tanggal</label>
                            <input type="date" name="start_date" value="{{ $startDate }}"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Sampai Tanggal</label>
                            <input type="date" name="end_date" value="{{ $endDate }}"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Jenis</label>
                            <select name="jenis"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                                <option value="semua" {{ $selectedJenis == 'semua' ? 'selected' : '' }}>Semua Jenis
                                </option>
                                @foreach (\App\Models\Perizinan::JENIS as $jenis)
                                    <option value="{{ $jenis }}" {{ $selectedJenis == $jenis ? 'selected' : '' }}>
                                        {{ $jenis }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Status</label>
                            <select name="status"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                                <option value="semua" {{ $selectedStatus == 'semua' ? 'selected' : '' }}>Semua Status
                                </option>
                                <option value="pending" {{ $selectedStatus == 'pending' ? 'selected' : '' }}>Menunggu
                                </option>
                                <option value="approved" {{ $selectedStatus == 'approved' ? 'selected' : '' }}>
                                    Disetujui</option>
                                <option value="rejected" {{ $selectedStatus == 'rejected' ? 'selected' : '' }}>
                                    Ditolak</option>
                            </select>
                        </div>
                        <div class="flex items-end gap-2">
                            <button type="submit"
                                class="w-full px-4 py-2 bg-[#00a2e9] text-white rounded-lg hover:bg-[#0088c4] transition-colors text-sm">
                                Filter
                            </button>
                            <a href="{{ route('karyawan.absensi.perizinan') }}"
                                class="w-full text-center px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors text-sm">
                                Reset
                            </a>
                        </div>
                    </form>

                    <!-- Tabel Perizinan -->
                    <div class="overflow-x-auto -mx-4 sm:mx-0">
                        <div class="inline-block min-w-full align-middle">
                            <table class="min-w-full">
                                <thead>
                                    <tr class="bg-[#F5F5F5]">
                                        <th class="px-3 sm:px-4 py-2 text-left text-xs font-semibold text-[#1B1B1B]">
                                            Tanggal</th>
                                        <th class="px-3 sm:px-4 py-2 text-left text-xs font-semibold text-[#1B1B1B]">
                                            Jenis</th>
                                        <th
                                            class="px-3 sm:px-4 py-2 text-left text-xs font-semibold text-[#1B1B1B] hidden md:table-cell">
                                            Keterangan</th>
                                        <th class="px-3 sm:px-4 py-2 text-left text-xs font-semibold text-[#1B1B1B]">
                                            Status</th>
                                        <th
                                            class="px-3 sm:px-4 py-2 text-left text-xs font-semibold text-[#1B1B1B] hidden lg:table-cell">
                                            Catatan HRD</th>
                                        <th class="px-3 sm:px-4 py-2 text-right text-xs font-semibold text-[#1B1B1B]">
                                            Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($perizinan as $item)
                                        <tr class="border-b border-gray-200 hover:bg-gray-50/50 transition-colors align-top">
                                            <td class="px-3 sm:px-4 py-2.5 text-xs font-medium whitespace-nowrap">
                                                {{ $item->tanggal_mulai->format('d/m/Y') }}
                                                @if (!$item->tanggal_mulai->isSameDay($item->tanggal_selesai))
                                                    <span class="text-gray-400">—</span><br class="sm:hidden">
                                                    {{ $item->tanggal_selesai->format('d/m/Y') }}
                                                    <span
                                                        class="block text-[10px] text-gray-400 mt-0.5">{{ $item->jumlah_hari }}
                                                        hari</span>
                                                @endif
                                                {{-- Mobile: tampilkan keterangan singkat --}}
                                                <p class="text-[10px] text-gray-400 mt-1 md:hidden max-w-[140px] truncate">
                                                    {{ $item->keterangan }}</p>
                                            </td>
                                            <td class="px-3 sm:px-4 py-2.5 text-xs">
                                                @php
                                                    $jenisColor = $item->jenis === 'Sakit' ? 'bg-[#00a2e9] text-white' : 'bg-[#FCC626] text-[#1B1B1B]';
                                                @endphp
                                                <span class="px-2 py-1 rounded-full text-[10px] font-medium {{ $jenisColor }}">
                                                    {{ $item->jenis }}
                                                </span>
                                            </td>
                                            <td class="px-3 sm:px-4 py-2.5 text-xs text-[#1B1B1B] hidden md:table-cell max-w-xs">
                                                {{ $item->keterangan }}
                                            </td>
                                            <td class="px-3 sm:px-4 py-2.5 text-xs">
                                                @php
                                                    $statusColors = [
                                                        'pending' => 'bg-gray-200 text-gray-700',
                                                        'approved' => 'bg-[#2E7D3E] text-white',
                                                        'rejected' => 'bg-[#ec1d1d] text-white',
                                                    ];
                                                    $statusIcon = ['pending' => '⏳', 'approved' => '✅', 'rejected' => '❌'];
                                                @endphp
                                                <span
                                                    class="px-2 py-1 rounded-full text-[10px] font-medium whitespace-nowrap {{ $statusColors[$item->status] }}">
                                                    {{ $statusIcon[$item->status] }} {{ $item->status_label }}
                                                </span>
                                            </td>
                                            <td class="px-3 sm:px-4 py-2.5 text-xs text-[#1B1B1B] hidden lg:table-cell max-w-xs">
                                                {{ $item->catatan_hr ?? '-' }}
                                            </td>
                                            <td class="px-3 sm:px-4 py-2.5 text-right">
                                                @if ($item->status === 'pending')
                                                    <form method="POST"
                                                        action="{{ route('karyawan.absensi.perizinan.cancel', $item->id) }}"
                                                        onsubmit="return confirm('Batalkan pengajuan ini?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit"
                                                            class="text-[#ec1d1d] hover:text-red-700 text-xs font-medium whitespace-nowrap">
                                                            Batalkan
                                                        </button>
                                                    </form>
                                                @else
                                                    <span class="text-gray-300 text-xs">—</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                                                Belum ada pengajuan izin/sakit.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Pagination -->
                    @if ($perizinan->total() > 0)
                        <div class="mt-3">
                            {{ $perizinan->onEachSide(1)->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
