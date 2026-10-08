@extends('layouts.app')

@section('content')
<div class="flex">
    @include('layouts.sidebar')

    <div class="flex-1 min-w-0 md:ml-64 p-4 sm:p-6 lg:p-8 space-y-6">
        
        <!-- Alerts / Flash Messages -->
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

        <!-- Super Admin Welcome Banner -->
        <div class="relative overflow-hidden bg-gradient-to-br from-[#0F1245] via-[#161758] to-[#27438D] rounded-3xl p-6 sm:p-8 text-white shadow-xl shadow-[#161758]/15 border border-white/10">
            <!-- Decorative blur balls -->
            <div class="absolute -right-16 -top-16 w-64 h-64 bg-[#00a2e9] rounded-full blur-3xl opacity-20 pointer-events-none"></div>
            <div class="absolute -left-16 -bottom-16 w-64 h-64 bg-[#FCC626] rounded-full blur-3xl opacity-15 pointer-events-none"></div>

            <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div class="space-y-2">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 border border-white/20 text-xs font-semibold text-[#FCC626] backdrop-blur-sm">
                        <i class="fa-solid fa-crown text-xs"></i>
                        <span>Super Administrator Control Center</span>
                    </div>
                    <h1 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold font-['Montserrat'] tracking-tight">
                        Selamat Datang, {{ auth()->user()->nama_lengkap }}
                    </h1>
                    <p class="text-white/75 text-xs sm:text-sm max-w-2xl font-light">
                        Kendali penuh sistem SIKEKAR: kelola mode maintenance sistem, aktifkan/matikan fitur secara fleksibel, dan lakukan koreksi presensi jam masuk/pulang karyawan secara otomatis.
                    </p>
                </div>

                <!-- Quick Action Buttons -->
                <div class="flex flex-wrap items-center gap-3">
                    <a href="{{ route('superadmin.features.index') }}" class="px-4 py-2.5 rounded-xl bg-white/10 hover:bg-white/20 border border-white/20 text-white font-medium text-xs sm:text-sm backdrop-blur-sm transition-all flex items-center gap-2 shadow-sm">
                        <i class="fa-solid fa-sliders text-[#00a2e9]"></i>
                        <span>Kontrol Fitur & Maintenance</span>
                    </a>
                    <a href="{{ route('superadmin.absensi.index') }}" class="px-4 py-2.5 rounded-xl bg-[#FCC626] hover:bg-[#e5a000] text-[#0F1245] font-semibold text-xs sm:text-sm transition-all flex items-center gap-2 shadow-lg shadow-[#FCC626]/20">
                        <i class="fa-solid fa-clock-rotate-left"></i>
                        <span>Kelola Presensi Karyawan</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- System Controls Widget: Maintenance Mode & Feature Master Switch -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            
            <!-- 1. Maintenance Status Card -->
            <div class="bg-white rounded-3xl p-5 sm:p-6 shadow-sm border border-gray-100 relative overflow-hidden flex flex-col justify-between">
                <div class="flex items-start justify-between gap-4 mb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-2xl flex items-center justify-center text-xl {{ $maintenanceInfo['is_active'] ? 'bg-rose-50 text-rose-600 border border-rose-200' : 'bg-emerald-50 text-emerald-600 border border-emerald-200' }}">
                            <i class="fa-solid {{ $maintenanceInfo['is_active'] ? 'fa-triangle-exclamation' : 'fa-shield-halved' }}"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-gray-900">Mode Pemeliharaan (Maintenance)</h3>
                            <p class="text-xs text-gray-500">Matikan sistem untuk karyawan & HR saat perbaikan</p>
                        </div>
                    </div>
                    
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold {{ $maintenanceInfo['is_active'] ? 'bg-rose-100 text-rose-700 animate-pulse' : 'bg-emerald-100 text-emerald-700' }}">
                        <span class="w-2 h-2 rounded-full {{ $maintenanceInfo['is_active'] ? 'bg-rose-600' : 'bg-emerald-600' }}"></span>
                        {{ $maintenanceInfo['is_active'] ? 'AKTIF (Sistem Offline)' : 'NON-AKTIF (Sistem Online)' }}
                    </span>
                </div>

                <div class="bg-gray-50 rounded-2xl p-4 border border-gray-100 mb-5 space-y-1.5 text-xs text-gray-600">
                    <div class="flex justify-between">
                        <span class="text-gray-400">Judul Tampilan:</span>
                        <span class="font-medium text-gray-800 truncate max-w-[220px]">{{ $maintenanceInfo['title'] }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400">Estimasi Selesai:</span>
                        <span class="font-medium text-gray-800">{{ $maintenanceInfo['end_time'] }}</span>
                    </div>
                </div>

                <div class="flex items-center justify-between gap-3 pt-2 border-t border-gray-100">
                    <a href="{{ route('superadmin.features.index') }}" class="text-xs font-semibold text-[#00a2e9] hover:underline flex items-center gap-1">
                        <span>Konfigurasi Pesan</span>
                        <i class="fa-solid fa-chevron-right text-[10px]"></i>
                    </a>

                    <form action="{{ route('superadmin.maintenance.toggle') }}" method="POST">
                        @csrf
                        <button type="submit" class="px-4 py-2 rounded-xl text-xs font-bold transition-all shadow-sm flex items-center gap-2 {{ $maintenanceInfo['is_active'] ? 'bg-emerald-600 hover:bg-emerald-700 text-white shadow-emerald-500/20' : 'bg-rose-600 hover:bg-rose-700 text-white shadow-rose-500/20' }}">
                            <i class="fa-solid {{ $maintenanceInfo['is_active'] ? 'fa-power-off' : 'fa-ban' }}"></i>
                            <span>{{ $maintenanceInfo['is_active'] ? 'Matikan Maintenance (Online)' : 'Aktifkan Maintenance (Offline)' }}</span>
                        </button>
                    </form>
                </div>
            </div>

            <!-- 2. Feature Master Status Card -->
            <div class="bg-white rounded-3xl p-5 sm:p-6 shadow-sm border border-gray-100 relative overflow-hidden flex flex-col justify-between">
                <div class="flex items-start justify-between gap-4 mb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-2xl bg-blue-50 border border-blue-200 text-[#00a2e9] flex items-center justify-center text-xl">
                            <i class="fa-solid fa-toggle-on"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-gray-900">Manajemen Fitur Sistem</h3>
                            <p class="text-xs text-gray-500">Status ketersediaan {{ $totalFeaturesCount }} modul aplikasi</p>
                        </div>
                    </div>

                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-700">
                        {{ $activeFeaturesCount }} / {{ $totalFeaturesCount }} Aktif
                    </span>
                </div>

                <!-- Feature Mini Grid -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 mb-5">
                    @foreach(array_slice($features, 0, 8) as $key => $feat)
                        <div class="p-2 rounded-xl border text-center transition-all {{ $feat['is_enabled'] ? 'bg-emerald-50/50 border-emerald-200 text-emerald-900' : 'bg-gray-50 border-gray-200 text-gray-400 opacity-60' }}">
                            <i class="fa-solid {{ $feat['icon'] }} text-xs mb-1 block" style="color: {{ $feat['is_enabled'] ? $feat['color'] : '#9ca3af' }}"></i>
                            <span class="text-[10px] font-medium truncate block">{{ $feat['name'] }}</span>
                            <span class="text-[9px] font-bold block mt-0.5 {{ $feat['is_enabled'] ? 'text-emerald-600' : 'text-rose-500' }}">
                                {{ $feat['is_enabled'] ? 'ON' : 'OFF' }}
                            </span>
                        </div>
                    @endforeach
                </div>

                <div class="flex items-center justify-between gap-3 pt-2 border-t border-gray-100">
                    <a href="{{ route('superadmin.features.index') }}" class="text-xs font-semibold text-[#00a2e9] hover:underline flex items-center gap-1">
                        <span>Kelola Masing-Masing Fitur</span>
                        <i class="fa-solid fa-chevron-right text-[10px]"></i>
                    </a>

                    <div class="flex items-center gap-2">
                        <form action="{{ route('superadmin.features.toggle-all') }}" method="POST" class="inline">
                            @csrf
                            <input type="hidden" name="action" value="disable_all">
                            <button type="submit" class="px-2.5 py-1.5 rounded-lg bg-gray-100 hover:bg-rose-50 hover:text-rose-600 text-gray-600 text-xs font-semibold transition-all">
                                Matikan Semua
                            </button>
                        </form>
                        <form action="{{ route('superadmin.features.toggle-all') }}" method="POST" class="inline">
                            @csrf
                            <input type="hidden" name="action" value="enable_all">
                            <button type="submit" class="px-2.5 py-1.5 rounded-lg bg-[#00a2e9] hover:bg-[#0088cc] text-white text-xs font-semibold shadow-sm transition-all">
                                Aktifkan Semua
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- KPI Summary Cards: Presensi Hari Ini -->
        <div class="space-y-3">
            <div class="flex items-center justify-between">
                <h2 class="text-base sm:text-lg font-bold text-gray-900 flex items-center gap-2">
                    <i class="fa-solid fa-chart-pie text-[#00a2e9]"></i>
                    <span>Ringkasan Kehadiran Hari Ini ({{ \Carbon\Carbon::today()->translatedFormat('d F Y') }})</span>
                </h2>
                <a href="{{ route('superadmin.absensi.index') }}" class="text-xs font-semibold text-[#00a2e9] hover:underline">
                    Lihat Semua Data &rarr;
                </a>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 sm:gap-4">
                <!-- Total Karyawan -->
                <div class="bg-white rounded-2xl p-4 border border-gray-100 shadow-sm flex flex-col justify-between">
                    <span class="text-xs font-medium text-gray-500">Total Karyawan</span>
                    <div class="mt-2 flex items-baseline justify-between">
                        <span class="text-2xl font-extrabold text-[#0F1245]">{{ $totalKaryawan }}</span>
                        <div class="w-7 h-7 rounded-lg bg-indigo-50 text-[#161758] flex items-center justify-center text-xs">
                            <i class="fa-solid fa-users"></i>
                        </div>
                    </div>
                </div>

                <!-- Hadir -->
                <div class="bg-white rounded-2xl p-4 border border-emerald-100 shadow-sm flex flex-col justify-between">
                    <span class="text-xs font-medium text-emerald-700">Hadir</span>
                    <div class="mt-2 flex items-baseline justify-between">
                        <span class="text-2xl font-extrabold text-emerald-600">{{ $hadirHariIni }}</span>
                        <div class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-xs">
                            <i class="fa-solid fa-user-check"></i>
                        </div>
                    </div>
                </div>

                <!-- Izin / Sakit -->
                <div class="bg-white rounded-2xl p-4 border border-purple-100 shadow-sm flex flex-col justify-between">
                    <span class="text-xs font-medium text-purple-700">Izin / Sakit</span>
                    <div class="mt-2 flex items-baseline justify-between">
                        <span class="text-2xl font-extrabold text-purple-600">{{ $izinHariIni + $sakitHariIni }}</span>
                        <div class="w-7 h-7 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center text-xs">
                            <i class="fa-solid fa-notes-medical"></i>
                        </div>
                    </div>
                </div>

                <!-- Cuti / Dinas -->
                <div class="bg-white rounded-2xl p-4 border border-blue-100 shadow-sm flex flex-col justify-between">
                    <span class="text-xs font-medium text-blue-700">Cuti / Dinas</span>
                    <div class="mt-2 flex items-baseline justify-between">
                        <span class="text-2xl font-extrabold text-blue-600">{{ $cutiHariIni + $dinasHariIni }}</span>
                        <div class="w-7 h-7 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-xs">
                            <i class="fa-solid fa-plane-departure"></i>
                        </div>
                    </div>
                </div>

                <!-- Terlambat -->
                <div class="bg-white rounded-2xl p-4 border border-amber-100 shadow-sm flex flex-col justify-between">
                    <span class="text-xs font-medium text-amber-700">Terlambat</span>
                    <div class="mt-2 flex items-baseline justify-between">
                        <span class="text-2xl font-extrabold text-amber-600">{{ $terlambatHariIni }}</span>
                        <div class="w-7 h-7 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center text-xs">
                            <i class="fa-solid fa-clock"></i>
                        </div>
                    </div>
                </div>

                <!-- Belum Hadir / Alpha -->
                <div class="bg-white rounded-2xl p-4 border border-rose-100 shadow-sm flex flex-col justify-between">
                    <span class="text-xs font-medium text-rose-700">Belum Presensi</span>
                    <div class="mt-2 flex items-baseline justify-between">
                        <span class="text-2xl font-extrabold text-rose-600">{{ $alphaHariIni }}</span>
                        <div class="w-7 h-7 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center text-xs">
                            <i class="fa-solid fa-user-xmark"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Attendance Management Section with Quick Edit -->
        <div class="bg-white rounded-3xl p-5 sm:p-6 shadow-sm border border-gray-100 space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <h2 class="text-base sm:text-lg font-bold text-gray-900 flex items-center gap-2">
                        <i class="fa-solid fa-pen-to-square text-[#00a2e9]"></i>
                        <span>Presensi Terbaru & Koreksi Cepat Jam Kerja</span>
                    </h2>
                    <p class="text-xs text-gray-500">Ubah jam masuk atau jam pulang karyawan, data total jam kerja akan otomatis terhitung dan masuk ke sistem.</p>
                </div>
                <a href="{{ route('superadmin.absensi.index') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-[#00a2e9] hover:bg-[#0088cc] text-white text-xs font-semibold shadow-sm transition-all">
                    <i class="fa-solid fa-list-check"></i>
                    <span>Buka Konsol Presensi Lengkap</span>
                </a>
            </div>

            <!-- Table Responsive -->
            <div class="overflow-x-auto rounded-2xl border border-gray-100">
                <table class="w-full text-left text-xs sm:text-sm text-gray-600">
                    <thead class="bg-gray-50/80 text-gray-700 font-semibold text-xs border-b border-gray-100">
                        <tr>
                            <th class="px-4 py-3">Karyawan</th>
                            <th class="px-4 py-3">Tanggal</th>
                            <th class="px-4 py-3">Jam Masuk</th>
                            <th class="px-4 py-3">Jam Pulang</th>
                            <th class="px-4 py-3">Total Jam Kerja</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($recentAbsensi as $abs)
                            <tr class="hover:bg-blue-50/30 transition-colors">
                                <td class="px-4 py-3">
                                    <div class="font-semibold text-gray-900">{{ $abs->karyawan->nama_lengkap ?? 'Karyawan Dihapus' }}</div>
                                    <div class="text-[11px] text-gray-400">{{ $abs->karyawan->kode_pegawai ?? '-' }} &bull; {{ $abs->karyawan->divisi ?? 'Umum' }}</div>
                                </td>
                                <td class="px-4 py-3 font-medium text-gray-700 whitespace-nowrap">
                                    {{ $abs->tanggal ? $abs->tanggal->translatedFormat('d M Y') : '-' }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap font-mono text-xs">
                                    @if($abs->check_in)
                                        <span class="inline-flex items-center gap-1 text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md font-semibold">
                                            <i class="fa-solid fa-arrow-right-to-bracket text-[10px]"></i>
                                            {{ $abs->check_in->format('H:i') }}
                                        </span>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap font-mono text-xs">
                                    @if($abs->check_out)
                                        <span class="inline-flex items-center gap-1 text-blue-700 bg-blue-50 px-2 py-0.5 rounded-md font-semibold">
                                            <i class="fa-solid fa-arrow-right-from-bracket text-[10px]"></i>
                                            {{ $abs->check_out->format('H:i') }}
                                        </span>
                                    @else
                                        <span class="text-gray-400 italic">Belum Pulang</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap font-semibold">
                                    <span class="inline-flex items-center gap-1 text-purple-700 bg-purple-50 px-2.5 py-0.5 rounded-full text-xs">
                                        <i class="fa-solid fa-hourglass-half text-[10px]"></i>
                                        {{ $abs->total_jam_kerja ?? 0 }} Jam ({{ \App\Models\Absensi::formatDurasiKerja($abs->check_in, $abs->check_out, $abs->tanggal) }})
                                    </span>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span class="px-2.5 py-1 rounded-full text-[11px] font-bold {{ $abs->status === 'Hadir' ? 'bg-emerald-100 text-emerald-800' : ($abs->status === 'Alpha' ? 'bg-rose-100 text-rose-800' : 'bg-amber-100 text-amber-800') }}">
                                        {{ $abs->status }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right whitespace-nowrap">
                                    <a href="{{ route('superadmin.absensi.index', ['karyawan_id' => $abs->karyawan_id, 'start_date' => $abs->tanggal->toDateString(), 'end_date' => $abs->tanggal->toDateString()]) }}" class="px-3 py-1.5 rounded-lg bg-gray-100 hover:bg-[#00a2e9] hover:text-white text-gray-700 text-xs font-semibold transition-all inline-flex items-center gap-1.5">
                                        <i class="fa-solid fa-pen-to-square text-xs"></i>
                                        <span>Koreksi Jam</span>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-8 text-gray-400">
                                    <i class="fa-solid fa-inbox text-3xl mb-2 block"></i>
                                    <span>Belum ada riwayat presensi yang tercatat.</span>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>
@endsection
