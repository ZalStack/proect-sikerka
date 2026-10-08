@extends('layouts.app')

@section('content')
<div class="flex">
    @include('layouts.sidebar')

    <div class="flex-1 min-w-0 md:ml-64 p-4 sm:p-6 lg:p-8 space-y-6">
        
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">
                    <a href="{{ route('superadmin.dashboard') }}" class="hover:text-[#00a2e9]">Super Admin</a>
                    <span>/</span>
                    <span class="text-gray-700">Presensi Karyawan</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-[#0F1245] font-['Montserrat']">
                    Kelola & Koreksi Jam Kerja Karyawan
                </h1>
                <p class="text-xs sm:text-sm text-gray-500 font-light mt-0.5">
                    Ubah jam masuk dan jam pulang presensi karyawan dengan kalkulasi otomatis total jam kerja langsung tersimpan ke database.
                </p>
            </div>

            <div class="flex items-center gap-2 flex-wrap">
                <button type="button" onclick="openAddModal()" class="px-4 py-2.5 rounded-xl bg-[#00a2e9] hover:bg-[#0088cc] text-white text-xs sm:text-sm font-semibold shadow-md shadow-[#00a2e9]/20 transition-all flex items-center gap-2">
                    <i class="fa-solid fa-plus"></i>
                    <span>Tambah Presensi Manual</span>
                </button>
            </div>
        </div>

        <!-- Flash Messages -->
        @if(session('success'))
            <div class="bg-emerald-500/10 border border-emerald-500/20 text-emerald-800 rounded-2xl p-4 flex items-center justify-between shadow-sm" role="alert">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-emerald-500 text-white flex items-center justify-center flex-shrink-0">
                        <i class="fa-solid fa-check"></i>
                    </div>
                    <span class="text-sm font-medium">{{ session('success') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-900 p-1">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
        @endif

        @if(session('error'))
            <div class="bg-rose-500/10 border border-rose-500/20 text-rose-800 rounded-2xl p-4 flex items-center justify-between shadow-sm" role="alert">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-rose-500 text-white flex items-center justify-center flex-shrink-0">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                    </div>
                    <span class="text-sm font-medium">{{ session('error') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-rose-600 hover:text-rose-900 p-1">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
        @endif

        <!-- Filter Bar -->
        <div class="bg-white rounded-3xl p-5 sm:p-6 shadow-sm border border-gray-100">
            <form method="GET" action="{{ route('superadmin.absensi.index') }}" class="space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                    
                    <!-- Karyawan Select -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Karyawan</label>
                        <select name="karyawan_id" class="w-full px-3 py-2 rounded-xl border border-gray-200 text-xs font-medium focus:ring-2 focus:ring-[#00a2e9]/20 focus:border-[#00a2e9]">
                            <option value="">-- Semua Karyawan --</option>
                            @foreach($karyawans as $k)
                                <option value="{{ $k->id }}" {{ $karyawanId == $k->id ? 'selected' : '' }}>
                                    {{ $k->nama_lengkap }} ({{ $k->kode_pegawai }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Divisi Select -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Divisi</label>
                        <select name="divisi" class="w-full px-3 py-2 rounded-xl border border-gray-200 text-xs font-medium focus:ring-2 focus:ring-[#00a2e9]/20 focus:border-[#00a2e9]">
                            <option value="">-- Semua Divisi --</option>
                            @foreach($divisis as $d)
                                <option value="{{ $d }}" {{ $divisi == $d ? 'selected' : '' }}>{{ $d }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Status Select -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Status Presensi</label>
                        <select name="status" class="w-full px-3 py-2 rounded-xl border border-gray-200 text-xs font-medium focus:ring-2 focus:ring-[#00a2e9]/20 focus:border-[#00a2e9]">
                            <option value="">-- Semua Status --</option>
                            @foreach(['Hadir', 'Izin', 'Sakit', 'Alpha', 'Perjalanan Dinas', 'Cuti'] as $st)
                                <option value="{{ $st }}" {{ $status == $st ? 'selected' : '' }}>{{ $st }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Start Date -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Dari Tanggal</label>
                        <input type="date" name="start_date" value="{{ $startDate }}" class="w-full px-3 py-2 rounded-xl border border-gray-200 text-xs font-medium focus:ring-2 focus:ring-[#00a2e9]/20 focus:border-[#00a2e9]">
                    </div>

                    <!-- End Date -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Sampai Tanggal</label>
                        <input type="date" name="end_date" value="{{ $endDate }}" class="w-full px-3 py-2 rounded-xl border border-gray-200 text-xs font-medium focus:ring-2 focus:ring-[#00a2e9]/20 focus:border-[#00a2e9]">
                    </div>
                </div>

                <!-- Search Input & Submit -->
                <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-2 border-t border-gray-100">
                    <div class="w-full sm:w-80">
                        <div class="relative">
                            <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                            <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama, NIP, keterangan..." class="w-full pl-9 pr-3 py-2 rounded-xl border border-gray-200 text-xs font-medium focus:ring-2 focus:ring-[#00a2e9]/20 focus:border-[#00a2e9]">
                        </div>
                    </div>

                    <div class="flex items-center gap-2 w-full sm:w-auto">
                        <a href="{{ route('superadmin.absensi.index') }}" class="px-4 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-600 text-xs font-semibold transition-all">
                            Reset
                        </a>
                        <button type="submit" class="px-5 py-2 rounded-xl bg-[#0F1245] hover:bg-[#161758] text-white text-xs font-bold transition-all shadow-sm flex items-center gap-1.5">
                            <i class="fa-solid fa-filter"></i>
                            <span>Terapkan Filter</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- KPI Metrics from Filter -->
        <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
            <div class="bg-white rounded-2xl p-4 border border-gray-100 shadow-sm">
                <span class="text-[11px] font-medium text-gray-400">Total Baris Presensi</span>
                <p class="text-xl font-extrabold text-[#0F1245] mt-1">{{ number_format($totalRecords) }}</p>
            </div>
            <div class="bg-white rounded-2xl p-4 border border-purple-100 shadow-sm">
                <span class="text-[11px] font-medium text-purple-600">Total Akumulasi Jam Kerja</span>
                <p class="text-xl font-extrabold text-purple-700 mt-1">{{ number_format($totalJamSemua) }} Jam</p>
            </div>
            <div class="bg-white rounded-2xl p-4 border border-blue-100 shadow-sm">
                <span class="text-[11px] font-medium text-blue-600">Rata-Rata Jam Kerja</span>
                <p class="text-xl font-extrabold text-blue-700 mt-1">{{ $avgJam }} Jam/Hari</p>
            </div>
            <div class="bg-white rounded-2xl p-4 border border-emerald-100 shadow-sm">
                <span class="text-[11px] font-medium text-emerald-600">Status Hadir</span>
                <p class="text-xl font-extrabold text-emerald-700 mt-1">{{ number_format($totalHadir) }}</p>
            </div>
            <div class="bg-white rounded-2xl p-4 border border-amber-100 shadow-sm">
                <span class="text-[11px] font-medium text-amber-600">Terlambat</span>
                <p class="text-xl font-extrabold text-amber-700 mt-1">{{ number_format($totalTerlambat) }}</p>
            </div>
        </div>

        <!-- Attendance Data Table -->
        <div class="bg-white rounded-3xl p-5 sm:p-6 shadow-sm border border-gray-100 space-y-4">
            
            <div class="flex items-center justify-between">
                <h2 class="text-base sm:text-lg font-bold text-gray-900 flex items-center gap-2">
                    <i class="fa-solid fa-list-check text-[#00a2e9]"></i>
                    <span>Daftar Presensi Karyawan</span>
                </h2>
                <span class="text-xs text-gray-500 font-medium">Menampilkan {{ $absensis->firstItem() ?? 0 }} - {{ $absensis->lastItem() ?? 0 }} dari {{ $absensis->total() }} data</span>
            </div>

            <div class="overflow-x-auto rounded-2xl border border-gray-100">
                <table class="w-full text-left text-xs sm:text-sm text-gray-600">
                    <thead class="bg-gray-50/80 text-gray-700 font-semibold text-xs border-b border-gray-100">
                        <tr>
                            <th class="px-4 py-3.5">Karyawan</th>
                            <th class="px-4 py-3.5">Tanggal</th>
                            <th class="px-4 py-3.5">Jam Masuk</th>
                            <th class="px-4 py-3.5">Jam Pulang</th>
                            <th class="px-4 py-3.5">Total Jam Kerja</th>
                            <th class="px-4 py-3.5">Status</th>
                            <th class="px-4 py-3.5">Kantor Cabang</th>
                            <th class="px-4 py-3.5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($absensis as $abs)
                            <tr class="hover:bg-blue-50/25 transition-colors" id="row-abs-{{ $abs->id }}">
                                <td class="px-4 py-3.5">
                                    <div class="font-bold text-gray-900">{{ $abs->karyawan->nama_lengkap ?? 'Karyawan Dihapus' }}</div>
                                    <div class="text-[11px] text-gray-400">NIP: {{ $abs->karyawan->kode_pegawai ?? '-' }} &bull; Div: {{ $abs->karyawan->divisi ?? '-' }}</div>
                                </td>
                                <td class="px-4 py-3.5 whitespace-nowrap font-medium text-gray-800">
                                    {{ $abs->tanggal ? $abs->tanggal->translatedFormat('d M Y') : '-' }}
                                    <span class="block text-[10px] text-gray-400 font-normal">{{ $abs->hari }}</span>
                                </td>
                                <td class="px-4 py-3.5 whitespace-nowrap">
                                    @if($abs->check_in)
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-mono font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <i class="fa-solid fa-arrow-right-to-bracket text-[10px]"></i>
                                            {{ $abs->check_in->format('H:i') }}
                                        </span>
                                        @if($abs->is_terlambat)
                                            <span class="block text-[10px] text-amber-600 font-medium mt-0.5">Telat {{ $abs->terlambat_menit }} mnt</span>
                                        @endif
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3.5 whitespace-nowrap">
                                    @if($abs->check_out)
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-mono font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                            <i class="fa-solid fa-arrow-right-from-bracket text-[10px]"></i>
                                            {{ $abs->check_out->format('H:i') }}
                                        </span>
                                        @if($abs->lembur_menit > 0)
                                            <span class="block text-[10px] text-purple-600 font-medium mt-0.5">Lembur {{ $abs->lembur_menit }} mnt</span>
                                        @endif
                                    @else
                                        <span class="text-gray-400 italic text-xs">Belum Pulang</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3.5 whitespace-nowrap">
                                    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-purple-50 text-purple-700 border border-purple-200">
                                        <i class="fa-solid fa-hourglass-half text-[10px]"></i>
                                        <span>{{ $abs->total_jam_kerja ?? 0 }} Jam</span>
                                    </div>
                                    <span class="block text-[10px] text-gray-400 font-normal mt-0.5">
                                        {{ \App\Models\Absensi::formatDurasiKerja($abs->check_in, $abs->check_out, $abs->tanggal) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3.5 whitespace-nowrap">
                                    <span class="px-2.5 py-1 rounded-full text-[11px] font-bold {{ $abs->status === 'Hadir' ? 'bg-emerald-100 text-emerald-800' : ($abs->status === 'Alpha' ? 'bg-rose-100 text-rose-800' : 'bg-amber-100 text-amber-800') }}">
                                        {{ $abs->status }}
                                    </span>
                                </td>
                                <td class="px-4 py-3.5 whitespace-nowrap text-xs text-gray-500">
                                    {{ $abs->kantor_cabang ?? 'KPM LALADON' }}
                                </td>
                                <td class="px-4 py-3.5 text-right whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <!-- Edit / Koreksi Button -->
                                        <button type="button" 
                                            onclick="openEditModal({
                                                id: {{ $abs->id }},
                                                karyawan_id: {{ $abs->karyawan_id }},
                                                karyawan_nama: '{{ addslashes($abs->karyawan->nama_lengkap ?? 'Karyawan') }}',
                                                tanggal: '{{ $abs->tanggal ? $abs->tanggal->format('Y-m-d') : '' }}',
                                                check_in: '{{ $abs->check_in ? $abs->check_in->format('H:i') : '' }}',
                                                check_out: '{{ $abs->check_out ? $abs->check_out->format('H:i') : '' }}',
                                                status: '{{ addslashes($abs->status) }}',
                                                kantor_cabang: '{{ addslashes($abs->kantor_cabang ?? 'KPM LALADON') }}',
                                                keterangan: '{{ addslashes(str_replace(["\r", "\n"], ' ', $abs->keterangan ?? '')) }}'
                                            })"
                                            class="px-3 py-1.5 rounded-lg bg-[#00a2e9]/10 hover:bg-[#00a2e9] text-[#00a2e9] hover:text-white text-xs font-semibold transition-all flex items-center gap-1">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                            <span>Koreksi</span>
                                        </button>

                                        <!-- Delete Button -->
                                        <form action="{{ route('superadmin.absensi.destroy', $abs->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data presensi ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 rounded-lg bg-gray-100 hover:bg-rose-100 text-gray-500 hover:text-rose-600 text-xs transition-all" title="Hapus Record">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-12 text-gray-400">
                                    <i class="fa-solid fa-inbox text-4xl mb-3 block opacity-40"></i>
                                    <p class="font-medium text-sm">Tidak ditemukan data presensi yang sesuai dengan filter.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="pt-4">
                {{ $absensis->links() }}
            </div>
        </div>

    </div>
</div>

<!-- ======================================================== -->
<!-- MODAL KOREKSI / EDIT PRESENSI (LIVE CALCULATOR) -->
<!-- ======================================================== -->
<div id="editModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-8 shadow-2xl border border-gray-100 space-y-5 transform transition-all">
        
        <div class="flex items-center justify-between pb-4 border-b border-gray-100">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-blue-50 text-[#00a2e9] flex items-center justify-center text-lg">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-gray-900">Koreksi Presensi & Jam Kerja</h3>
                    <p id="editKaryawanNama" class="text-xs text-gray-500 font-medium">Nama Karyawan</p>
                </div>
            </div>
            <button type="button" onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600 p-1.5 rounded-lg hover:bg-gray-100">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <form id="editForm" method="POST" action="" class="space-y-4">
            @csrf
            @method('PUT')

            <!-- Tanggal Presensi -->
            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Tanggal Presensi</label>
                <input type="date" id="editTanggal" name="tanggal" required class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm font-medium focus:ring-2 focus:ring-[#00a2e9]/20 focus:border-[#00a2e9]">
            </div>

            <!-- Jam Masuk & Jam Pulang Input -->
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                        <i class="fa-solid fa-arrow-right-to-bracket text-emerald-600 mr-1"></i>
                        Jam Masuk (H:i)
                    </label>
                    <input type="time" id="editCheckIn" name="check_in" oninput="calculateLiveHours()" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm font-mono font-bold focus:ring-2 focus:ring-[#00a2e9]/20 focus:border-[#00a2e9]">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                        <i class="fa-solid fa-arrow-right-from-bracket text-blue-600 mr-1"></i>
                        Jam Pulang (H:i)
                    </label>
                    <input type="time" id="editCheckOut" name="check_out" oninput="calculateLiveHours()" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm font-mono font-bold focus:ring-2 focus:ring-[#00a2e9]/20 focus:border-[#00a2e9]">
                </div>
            </div>

            <!-- LIVE CALCULATOR PREVIEW BOX -->
            <div class="bg-gradient-to-br from-purple-50 to-indigo-50 border border-purple-200/70 rounded-2xl p-4 text-purple-900 space-y-1">
                <div class="flex items-center justify-between text-xs font-bold uppercase tracking-wider text-purple-700">
                    <span class="flex items-center gap-1.5">
                        <i class="fa-solid fa-wand-magic-sparkles text-purple-600"></i>
                        Kalkulasi Otomatis Sistem
                    </span>
                    <span id="calcBadge" class="bg-purple-200/80 px-2 py-0.5 rounded-full text-[10px]">Real-Time</span>
                </div>
                <div class="flex items-baseline justify-between pt-1">
                    <span class="text-xs text-purple-700">Total Jam Kerja Terhitung:</span>
                    <span id="calcTotalJam" class="text-lg font-extrabold text-purple-900 font-mono">0 Jam</span>
                </div>
                <p id="calcDurasiDetail" class="text-[11px] text-purple-600 font-medium">Masukkan Jam Masuk & Jam Pulang untuk menghitung otomatis.</p>
            </div>

            <!-- Status Presensi & Kantor Cabang -->
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Status</label>
                    <select id="editStatus" name="status" required class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm font-medium focus:ring-2 focus:ring-[#00a2e9]/20 focus:border-[#00a2e9]">
                        @foreach(['Hadir', 'Izin', 'Sakit', 'Alpha', 'Perjalanan Dinas', 'Cuti'] as $st)
                            <option value="{{ $st }}">{{ $st }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Kantor Cabang</label>
                    <select id="editKantorCabang" name="kantor_cabang" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm font-medium focus:ring-2 focus:ring-[#00a2e9]/20 focus:border-[#00a2e9]">
                        @foreach($kantorLocations as $loc)
                            <option value="{{ $loc }}">{{ $loc }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Keterangan / Alasan Koreksi -->
            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Keterangan / Catatan Koreksi</label>
                <textarea id="editKeterangan" name="keterangan" rows="2" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm font-medium focus:ring-2 focus:ring-[#00a2e9]/20 focus:border-[#00a2e9]" placeholder="Contoh: Koreksi jam masuk disesuaikan dengan surat tugas..."></textarea>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-end gap-3 pt-3 border-t border-gray-100">
                <button type="button" onclick="closeEditModal()" class="px-5 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold transition-all">
                    Batal
                </button>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-[#00a2e9] hover:bg-[#0088cc] text-white text-xs font-bold transition-all shadow-md shadow-[#00a2e9]/20 flex items-center gap-2">
                    <i class="fa-solid fa-floppy-disk"></i>
                    <span>Simpan & Update Otomatis</span>
                </button>
            </div>
        </form>

    </div>
</div>

<!-- ======================================================== -->
<!-- MODAL TAMBAH PRESENSI MANUAL -->
<!-- ======================================================== -->
<div id="addModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-8 shadow-2xl border border-gray-100 space-y-5 transform transition-all">
        
        <div class="flex items-center justify-between pb-4 border-b border-gray-100">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-user-plus"></i>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-gray-900">Tambah Presensi Manual</h3>
                    <p class="text-xs text-gray-500">Input data presensi untuk karyawan tertentu</p>
                </div>
            </div>
            <button type="button" onclick="closeAddModal()" class="text-gray-400 hover:text-gray-600 p-1.5 rounded-lg hover:bg-gray-100">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <form method="POST" action="{{ route('superadmin.absensi.store') }}" class="space-y-4">
            @csrf

            <!-- Pilih Karyawan -->
            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Pilih Karyawan</label>
                <select name="karyawan_id" required class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm font-medium focus:ring-2 focus:ring-[#00a2e9]/20 focus:border-[#00a2e9]">
                    <option value="">-- Pilih Karyawan --</option>
                    @foreach($karyawans as $k)
                        <option value="{{ $k->id }}">{{ $k->nama_lengkap }} ({{ $k->kode_pegawai }})</option>
                    @endforeach
                </select>
            </div>

            <!-- Tanggal -->
            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Tanggal Presensi</label>
                <input type="date" name="tanggal" value="{{ date('Y-m-d') }}" required class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm font-medium focus:ring-2 focus:ring-[#00a2e9]/20 focus:border-[#00a2e9]">
            </div>

            <!-- Jam Masuk & Jam Pulang -->
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Jam Masuk (H:i)</label>
                    <input type="time" id="addCheckIn" name="check_in" value="07:30" oninput="calculateAddLiveHours()" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm font-mono font-bold focus:ring-2 focus:ring-[#00a2e9]/20 focus:border-[#00a2e9]">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Jam Pulang (H:i)</label>
                    <input type="time" id="addCheckOut" name="check_out" value="16:00" oninput="calculateAddLiveHours()" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm font-mono font-bold focus:ring-2 focus:ring-[#00a2e9]/20 focus:border-[#00a2e9]">
                </div>
            </div>

            <!-- Kalkulasi Live Add -->
            <div class="bg-gradient-to-br from-emerald-50 to-teal-50 border border-emerald-200/70 rounded-2xl p-4 text-emerald-900 space-y-1">
                <div class="flex items-baseline justify-between">
                    <span class="text-xs font-semibold text-emerald-700">Estimasi Total Jam Kerja:</span>
                    <span id="addCalcTotalJam" class="text-lg font-extrabold text-emerald-900 font-mono">9 Jam</span>
                </div>
                <p id="addCalcDurasiDetail" class="text-[11px] text-emerald-600 font-medium">8 Jam 30 Menit (Dibulatkan jadi 9 Jam kerja)</p>
            </div>

            <!-- Status & Kantor -->
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Status</label>
                    <select name="status" required class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm font-medium focus:ring-2 focus:ring-[#00a2e9]/20 focus:border-[#00a2e9]">
                        @foreach(['Hadir', 'Izin', 'Sakit', 'Alpha', 'Perjalanan Dinas', 'Cuti'] as $st)
                            <option value="{{ $st }}">{{ $st }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Kantor Cabang</label>
                    <select name="kantor_cabang" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm font-medium focus:ring-2 focus:ring-[#00a2e9]/20 focus:border-[#00a2e9]">
                        @foreach($kantorLocations as $loc)
                            <option value="{{ $loc }}">{{ $loc }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Keterangan -->
            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Keterangan</label>
                <textarea name="keterangan" rows="2" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm font-medium focus:ring-2 focus:ring-[#00a2e9]/20 focus:border-[#00a2e9]" placeholder="Contoh: Input presensi manual atas persetujuan pimpinan..."></textarea>
            </div>

            <!-- Submit -->
            <div class="flex items-center justify-end gap-3 pt-3 border-t border-gray-100">
                <button type="button" onclick="closeAddModal()" class="px-5 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold transition-all">
                    Batal
                </button>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition-all shadow-md shadow-emerald-600/20 flex items-center gap-2">
                    <i class="fa-solid fa-plus"></i>
                    <span>Simpan Presensi</span>
                </button>
            </div>
        </form>

    </div>
</div>

<script>
    function openEditModal(data) {
        document.getElementById('editKaryawanNama').innerText = (data.karyawan_nama || 'Karyawan') + ' (' + (data.tanggal || '') + ')';
        document.getElementById('editTanggal').value = data.tanggal || '';
        document.getElementById('editCheckIn').value = data.check_in || '';
        document.getElementById('editCheckOut').value = data.check_out || '';
        document.getElementById('editStatus').value = data.status || 'Hadir';
        document.getElementById('editKantorCabang').value = data.kantor_cabang || 'KPM LALADON';
        document.getElementById('editKeterangan').value = data.keterangan || '';

        // Form action
        document.getElementById('editForm').action = '/superadmin/absensi/' + data.id + '/update';

        calculateLiveHours();

        document.getElementById('editModal').classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    }

    function closeEditModal() {
        document.getElementById('editModal').classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }

    function openAddModal() {
        calculateAddLiveHours();
        document.getElementById('addModal').classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    }

    function closeAddModal() {
        document.getElementById('addModal').classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }

    // Hitung Live Total Jam Kerja Edit
    function calculateLiveHours() {
        const inVal = document.getElementById('editCheckIn').value;
        const outVal = document.getElementById('editCheckOut').value;
        const calcTotal = document.getElementById('calcTotalJam');
        const calcDetail = document.getElementById('calcDurasiDetail');

        if (!inVal || !outVal) {
            calcTotal.innerText = '0 Jam';
            calcDetail.innerText = 'Harap isi jam masuk dan jam pulang untuk melihat kalkulasi.';
            return;
        }

        const [inH, inM] = inVal.split(':').map(Number);
        const [outH, outM] = outVal.split(':').map(Number);

        let inMinutes = inH * 60 + inM;
        let outMinutes = outH * 60 + outM;

        if (outMinutes < inMinutes) {
            outMinutes += 24 * 60; // Lewat tengah malam
        }

        const diffMinutes = Math.max(0, outMinutes - inMinutes);
        const roundedHours = Math.round(diffMinutes / 60);
        const hoursPart = Math.floor(diffMinutes / 60);
        const minutesPart = diffMinutes % 60;

        calcTotal.innerText = roundedHours + ' Jam';
        calcDetail.innerText = `Durasi Aktual: ${hoursPart} Jam ${minutesPart} Menit (Dibulatkan otomatis menjadi ${roundedHours} Jam kerja)`;
    }

    // Hitung Live Total Jam Kerja Add
    function calculateAddLiveHours() {
        const inVal = document.getElementById('addCheckIn').value;
        const outVal = document.getElementById('addCheckOut').value;
        const calcTotal = document.getElementById('addCalcTotalJam');
        const calcDetail = document.getElementById('addCalcDurasiDetail');

        if (!inVal || !outVal) {
            calcTotal.innerText = '0 Jam';
            calcDetail.innerText = 'Harap isi jam masuk dan jam pulang.';
            return;
        }

        const [inH, inM] = inVal.split(':').map(Number);
        const [outH, outM] = outVal.split(':').map(Number);

        let inMinutes = inH * 60 + inM;
        let outMinutes = outH * 60 + outM;

        if (outMinutes < inMinutes) {
            outMinutes += 24 * 60;
        }

        const diffMinutes = Math.max(0, outMinutes - inMinutes);
        const roundedHours = Math.round(diffMinutes / 60);
        const hoursPart = Math.floor(diffMinutes / 60);
        const minutesPart = diffMinutes % 60;

        calcTotal.innerText = roundedHours + ' Jam';
        calcDetail.innerText = `Durasi Aktual: ${hoursPart} Jam ${minutesPart} Menit (Dibulatkan otomatis menjadi ${roundedHours} Jam kerja)`;
    }
</script>
@endsection
