@extends('layouts.app')

@section('content')
<div class="flex">
    @include('layouts.sidebar')

    <div class="flex-1 min-w-0 md:ml-64 p-4 sm:p-6 lg:p-8 space-y-6">

        <!-- Header -->
        <div class="flex flex-col xl:flex-row xl:items-end justify-between gap-4">
            <div>
                <div class="flex flex-wrap items-center gap-2 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">
                    <a href="{{ route('superadmin.dashboard') }}" class="hover:text-amber-500 transition-colors">Super Admin</a>
                    <span>/</span>
                    <span class="text-amber-600 font-bold flex items-center gap-1">
                        <i class="fa-solid fa-user-secret"></i> Root Eksklusif
                    </span>
                    <span>/</span>
                    <span class="text-gray-700">Log Koreksi Presensi</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-[#0F1245] font-['Poppins'] tracking-tight flex items-center gap-3">
                    <span>Audit Log Koreksi Presensi</span>
                    <span class="text-xs bg-amber-500/10 text-amber-700 border border-amber-500/30 font-bold px-2.5 py-1 rounded-full uppercase tracking-wider">
                        Eksklusif Root
                    </span>
                </h1>
                <p class="text-xs sm:text-sm text-gray-500 font-light mt-0.5 max-w-2xl">
                    Rekaman terisolasi khusus Root Administrator untuk melacak seluruh pengubahan data presensi: siapa yang mengubah, karyawan mana yang diubah, dan rincian perubahan jam/status.
                </p>
            </div>

            <div class="flex items-center gap-2 flex-wrap">
                <!-- Tombol Hapus Terpilih (Muncul saat ada checkbox terpilih) -->
                <button type="button" id="btnBulkDelete" onclick="confirmBulkDelete()" class="hidden items-center gap-2 px-3.5 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold transition-all shadow-sm">
                    <i class="fa-solid fa-trash-can"></i>
                    <span>Hapus Terpilih (<span id="selectedCount">0</span>)</span>
                </button>

                <!-- Tombol Kosongkan Semua -->
                @if($stats['total'] > 0)
                    <button type="button" onclick="confirmClearAll()" class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl bg-rose-50 border border-rose-200 hover:bg-rose-100 text-rose-700 text-xs font-bold transition-all shadow-sm">
                        <i class="fa-solid fa-broom"></i>
                        <span>Kosongkan Semua</span>
                    </button>
                @endif

                <a href="{{ route('superadmin.absensi.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 text-xs font-semibold transition-all shadow-sm">
                    <i class="fa-solid fa-clock-rotate-left text-[#00a2e9]"></i>
                    <span>Menu Koreksi Presensi</span>
                </a>
            </div>
        </div>

        @include('superadmin.partials.flash')

        <!-- KPI Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
            <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm hover:shadow-md transition-all duration-300 border-l-4 border-l-amber-500">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Total Riwayat</span>
                    <span class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-sm shadow-sm">
                        <i class="fa-solid fa-database"></i>
                    </span>
                </div>
                <div class="text-2xl sm:text-3xl font-extrabold text-[#0F1245]">{{ number_format($stats['total']) }}</div>
                <p class="text-xs text-gray-400 mt-1">Total seluruh perubahan presensi</p>
            </div>

            <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm hover:shadow-md transition-all duration-300 border-l-4 border-l-emerald-500">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Koreksi Hari Ini</span>
                    <span class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-sm shadow-sm">
                        <i class="fa-solid fa-calendar-check"></i>
                    </span>
                </div>
                <div class="text-2xl sm:text-3xl font-extrabold text-[#0F1245]">{{ number_format($stats['today']) }}</div>
                <p class="text-xs text-gray-400 mt-1">Tindakan koreksi hari ini</p>
            </div>

            <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm hover:shadow-md transition-all duration-300 border-l-4 border-l-blue-500">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Karyawan Terdampak</span>
                    <span class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-sm shadow-sm">
                        <i class="fa-solid fa-users"></i>
                    </span>
                </div>
                <div class="text-2xl sm:text-3xl font-extrabold text-[#0F1245]">{{ number_format($stats['unique_karyawan']) }}</div>
                <p class="text-xs text-gray-400 mt-1">Karyawan yang pernah diubah</p>
            </div>

            <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm hover:shadow-md transition-all duration-300 border-l-4 border-l-purple-500">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Operator Terdeteksi</span>
                    <span class="w-9 h-9 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-sm shadow-sm">
                        <i class="fa-solid fa-user-shield"></i>
                    </span>
                </div>
                <div class="text-2xl sm:text-3xl font-extrabold text-[#0F1245]">{{ number_format($stats['operators_count']) }}</div>
                <p class="text-xs text-gray-400 mt-1">Admin/HR pelaku perubahan</p>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-100 shadow-sm">
            <form method="GET" action="{{ route('superadmin.secret.absensi-logs') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                <div class="lg:col-span-2">
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Cari Karyawan / Operator / Alasan</label>
                    <div class="relative">
                        <input type="text" name="search" value="{{ $search }}" placeholder="Ketik nama, kode pegawai, operator..."
                            class="w-full pl-9 pr-3 py-2 text-xs rounded-xl border border-gray-200 focus:outline-none focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20">
                        <i class="fa-solid fa-magnifying-glass absolute left-3 top-2.5 text-gray-400 text-xs"></i>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Operator Pengubah</label>
                    <select name="operator" class="w-full py-2 px-3 text-xs rounded-xl border border-gray-200 focus:outline-none focus:border-amber-500">
                        <option value="">Semua Operator</option>
                        @foreach($operatorList as $op)
                            <option value="{{ $op }}" {{ $operator === $op ? 'selected' : '' }}>{{ $op }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Jenis Tindakan</label>
                    <select name="action" class="w-full py-2 px-3 text-xs rounded-xl border border-gray-200 focus:outline-none focus:border-amber-500">
                        <option value="">Semua Tindakan</option>
                        <option value="koreksi" {{ $action === 'koreksi' ? 'selected' : '' }}>Koreksi Presensi</option>
                        <option value="hapus" {{ $action === 'hapus' ? 'selected' : '' }}>Hapus Presensi</option>
                    </select>
                </div>

                <div class="flex items-end gap-2">
                    <button type="submit" class="flex-1 py-2 px-4 rounded-xl bg-slate-800 hover:bg-slate-900 text-white text-xs font-bold transition-all shadow-sm">
                        <i class="fa-solid fa-filter mr-1"></i> Saring
                    </button>
                    @if($search || $action || $operator || $from || $to)
                        <a href="{{ route('superadmin.secret.absensi-logs') }}" class="py-2 px-3 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-600 text-xs font-bold transition-all" title="Reset filter">
                            <i class="fa-solid fa-rotate-left"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Table Card -->
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="p-4 sm:p-5 border-b border-gray-100 flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold text-[#0F1245]">Catatan Koreksi Presensi</h3>
                    <p class="text-xs text-gray-400 mt-0.5">Menampilkan {{ $logs->firstItem() ?? 0 }} - {{ $logs->lastItem() ?? 0 }} dari {{ $logs->total() }} catatan</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50 border-b border-gray-100 text-gray-400 uppercase text-[10px] tracking-wider font-bold">
                            <th class="py-3 px-3 w-10 text-center">
                                <input type="checkbox" id="selectAllCheckbox" class="rounded border-gray-300 text-rose-600 focus:ring-rose-500 cursor-pointer" title="Pilih Semua">
                            </th>
                            <th class="py-3 px-4">Waktu & IP</th>
                            <th class="py-3 px-4">Operator Pengubah</th>
                            <th class="py-3 px-4">Target Karyawan</th>
                            <th class="py-3 px-4">Tgl Presensi</th>
                            <th class="py-3 px-4">Tindakan</th>
                            <th class="py-3 px-4 min-w-[340px]">Rincian Perubahan (Nilai Awal ➔ Nilai Terakhir)</th>
                            <th class="py-3 px-4">Alasan / Ket</th>
                            <th class="py-3 px-4 text-center w-16">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @php
                            $formatTime = function ($val) {
                                if (empty($val) || $val === '-') return '-';
                                if ($val instanceof \Carbon\CarbonInterface) {
                                    return $val->copy()->setTimezone(config('app.timezone', 'Asia/Jakarta'))->format('H:i');
                                }
                                $str = (string) $val;
                                if (str_contains($str, 'T') || str_contains($str, 'Z')) {
                                    try {
                                        return \Carbon\Carbon::parse($str)->setTimezone(config('app.timezone', 'Asia/Jakarta'))->format('H:i');
                                    } catch (\Throwable $e) {}
                                }
                                if (str_contains($str, ' ')) {
                                    try {
                                        return \Carbon\Carbon::parse($str)->format('H:i');
                                    } catch (\Throwable $e) {}
                                }
                                if (preg_match('/^(\d{2}:\d{2})/', $str, $m)) {
                                    return $m[1];
                                }
                                return substr($str, 0, 5);
                            };
                        @endphp
                        @forelse($logs as $log)
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="py-3.5 px-3 text-center">
                                    <input type="checkbox" value="{{ $log->id }}" class="log-item-checkbox rounded border-gray-300 text-rose-600 focus:ring-rose-500 cursor-pointer">
                                </td>

                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    <div class="font-bold text-gray-800">{{ $log->created_at ? $log->created_at->format('d/m/Y H:i:s') : '-' }}</div>
                                    <div class="text-[10px] text-gray-400 font-mono">{{ $log->ip_address ?: '127.0.0.1' }}</div>
                                </td>

                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    <div class="flex items-center gap-2">
                                        <div class="w-7 h-7 rounded-lg bg-slate-800 text-white flex items-center justify-center font-bold text-xs uppercase shadow-xs">
                                            {{ substr($log->operator_name, 0, 1) }}
                                        </div>
                                        <div>
                                            <div class="font-bold text-gray-900">{{ $log->operator_name }}</div>
                                            <span class="inline-block text-[9px] px-1.5 py-0.2 rounded font-semibold uppercase {{ $log->operator_role === 'superadmin' ? 'bg-amber-100 text-amber-800' : 'bg-blue-100 text-blue-800' }}">
                                                {{ $log->operator_role }}
                                            </span>
                                        </div>
                                    </div>
                                </td>

                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-gray-900">{{ $log->karyawan_nama }}</div>
                                    @if($log->karyawan_kode)
                                        <div class="text-[10px] text-gray-400 font-mono">{{ $log->karyawan_kode }}</div>
                                    @endif
                                </td>

                                <td class="py-3.5 px-4 whitespace-nowrap font-medium text-gray-700">
                                    {{ $log->tanggal_presensi ? $log->tanggal_presensi->translatedFormat('d M Y') : '-' }}
                                </td>

                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    @if($log->action === 'hapus')
                                        <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                            <i class="fa-solid fa-trash-can"></i> Hapus
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                            <i class="fa-solid fa-pen-to-square"></i> Koreksi
                                        </span>
                                    @endif
                                </td>

                                <td class="py-3.5 px-4">
                                    @php
                                        $oldStatus = $log->old_data['status'] ?? '-';
                                        $newStatus = $log->new_data['status'] ?? '-';
                                        $statusChanged = $oldStatus !== $newStatus;

                                        $oldIn = $formatTime($log->old_data['check_in'] ?? null);
                                        $newIn = $formatTime($log->new_data['check_in'] ?? null);
                                        $inChanged = $oldIn !== $newIn;

                                        $oldOut = $formatTime($log->old_data['check_out'] ?? null);
                                        $newOut = $formatTime($log->new_data['check_out'] ?? null);
                                        $outChanged = $oldOut !== $newOut;

                                        $oldJam = isset($log->old_data['total_jam_kerja']) ? (int)$log->old_data['total_jam_kerja'] : 0;
                                        $newJam = isset($log->new_data['total_jam_kerja']) ? (int)$log->new_data['total_jam_kerja'] : 0;
                                        $jamChanged = $oldJam !== $newJam;

                                        $oldCabang = $log->old_data['kantor_cabang'] ?? null;
                                        $newCabang = $log->new_data['kantor_cabang'] ?? null;
                                        $cabangChanged = $oldCabang && $newCabang && ($oldCabang !== $newCabang);
                                    @endphp

                                    @if($log->action === 'hapus')
                                        <div class="p-2.5 rounded-xl bg-rose-50 border border-rose-200 text-xs">
                                            <div class="flex items-center gap-1.5 text-rose-700 font-bold mb-1.5">
                                                <i class="fa-solid fa-trash-can text-rose-600"></i>
                                                <span>Data Presensi Dihapus Permanen</span>
                                            </div>
                                            <div class="text-[11px] bg-white/90 rounded-lg p-2 border border-rose-100">
                                                <div class="font-bold text-gray-500 text-[10px] uppercase tracking-wider pb-1 mb-1 border-b border-gray-100 flex items-center gap-1">
                                                    <i class="fa-solid fa-clock-rotate-left text-gray-400"></i> Nilai Awal Sebelum Dihapus:
                                                </div>
                                                <div class="grid grid-cols-2 gap-x-3 gap-y-1 text-slate-700">
                                                    <div class="flex justify-between"><span class="text-gray-400">Status:</span> <span class="font-bold text-rose-700">{{ $oldStatus }}</span></div>
                                                    <div class="flex justify-between"><span class="text-gray-400">Total:</span> <span class="font-bold text-gray-800">{{ $oldJam }} Jam</span></div>
                                                    <div class="flex justify-between"><span class="text-gray-400">Masuk:</span> <span class="font-mono text-gray-800">{{ $oldIn }}</span></div>
                                                    <div class="flex justify-between"><span class="text-gray-400">Pulang:</span> <span class="font-mono text-gray-800">{{ $oldOut }}</span></div>
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <div class="min-w-[320px] max-w-[420px]">
                                            <div class="grid grid-cols-2 gap-2">
                                                <!-- Kolom Nilai Awal (Sebelum) -->
                                                <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-200">
                                                    <div class="flex items-center justify-between pb-1.5 mb-1.5 border-b border-slate-200">
                                                        <span class="text-[10px] font-bold text-slate-600 uppercase tracking-wider flex items-center gap-1">
                                                            <i class="fa-solid fa-clock-rotate-left text-slate-400 text-[9px]"></i> Nilai Awal
                                                        </span>
                                                        <span class="text-[9px] font-semibold text-slate-600 bg-slate-200/80 px-1.5 py-0.5 rounded">Sebelum</span>
                                                    </div>
                                                    <div class="space-y-1 text-[11px]">
                                                        <div class="flex justify-between items-center">
                                                            <span class="text-gray-400">Status:</span>
                                                            <span class="font-medium {{ $statusChanged ? 'line-through text-rose-500 font-bold' : 'text-gray-700' }}">{{ $oldStatus }}</span>
                                                        </div>
                                                        <div class="flex justify-between items-center">
                                                            <span class="text-gray-400">Masuk:</span>
                                                            <span class="font-mono {{ $inChanged ? 'line-through text-rose-500 font-bold' : 'text-gray-700' }}">{{ $oldIn }}</span>
                                                        </div>
                                                        <div class="flex justify-between items-center">
                                                            <span class="text-gray-400">Pulang:</span>
                                                            <span class="font-mono {{ $outChanged ? 'line-through text-rose-500 font-bold' : 'text-gray-700' }}">{{ $oldOut }}</span>
                                                        </div>
                                                        <div class="flex justify-between items-center pt-1 border-t border-slate-200 font-semibold">
                                                            <span class="text-gray-400">Total:</span>
                                                            <span class="{{ $jamChanged ? 'line-through text-rose-500 font-bold' : 'text-gray-700' }}">{{ $oldJam }} Jam</span>
                                                        </div>
                                                        @if($cabangChanged)
                                                            <div class="flex justify-between items-center pt-1 border-t border-slate-200 text-[10px]">
                                                                <span class="text-gray-400">Cabang:</span>
                                                                <span class="line-through text-rose-500 truncate max-w-[90px]" title="{{ $oldCabang }}">{{ $oldCabang }}</span>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>

                                                <!-- Kolom Nilai Terakhir (Sesudah) -->
                                                <div class="p-2.5 rounded-xl bg-emerald-50/60 border border-emerald-200">
                                                    <div class="flex items-center justify-between pb-1.5 mb-1.5 border-b border-emerald-200">
                                                        <span class="text-[10px] font-bold text-emerald-800 uppercase tracking-wider flex items-center gap-1">
                                                            <i class="fa-solid fa-circle-check text-emerald-600 text-[9px]"></i> Nilai Terakhir
                                                        </span>
                                                        <span class="text-[9px] font-bold text-emerald-700 bg-emerald-100 px-1.5 py-0.5 rounded">Sesudah</span>
                                                    </div>
                                                    <div class="space-y-1 text-[11px]">
                                                        <div class="flex justify-between items-center">
                                                            <span class="text-gray-400">Status:</span>
                                                            <span class="font-medium {{ $statusChanged ? 'text-emerald-700 font-bold bg-emerald-100/90 px-1.5 py-0.5 rounded' : 'text-gray-700' }}">{{ $newStatus }}</span>
                                                        </div>
                                                        <div class="flex justify-between items-center">
                                                            <span class="text-gray-400">Masuk:</span>
                                                            <span class="font-mono {{ $inChanged ? 'text-emerald-700 font-bold bg-emerald-100/90 px-1.5 py-0.5 rounded' : 'text-gray-700' }}">{{ $newIn }}</span>
                                                        </div>
                                                        <div class="flex justify-between items-center">
                                                            <span class="text-gray-400">Pulang:</span>
                                                            <span class="font-mono {{ $outChanged ? 'text-emerald-700 font-bold bg-emerald-100/90 px-1.5 py-0.5 rounded' : 'text-gray-700' }}">{{ $newOut }}</span>
                                                        </div>
                                                        <div class="flex justify-between items-center pt-1 border-t border-emerald-200 font-semibold">
                                                            <span class="text-gray-400">Total:</span>
                                                            <span class="{{ $jamChanged ? 'text-emerald-700 font-bold bg-emerald-100/90 px-1.5 py-0.5 rounded' : 'text-gray-700' }}">{{ $newJam }} Jam</span>
                                                        </div>
                                                        @if($cabangChanged)
                                                            <div class="flex justify-between items-center pt-1 border-t border-emerald-200 text-[10px]">
                                                                <span class="text-gray-400">Cabang:</span>
                                                                <span class="text-emerald-700 font-bold bg-emerald-100/90 px-1.5 py-0.5 rounded truncate max-w-[90px]" title="{{ $newCabang }}">{{ $newCabang }}</span>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </td>

                                <td class="py-3.5 px-4 text-gray-600 max-w-[200px] truncate" title="{{ $log->keterangan }}">
                                    {{ $log->keterangan ?: '-' }}
                                </td>

                                <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                    <form action="{{ route('superadmin.secret.absensi-logs.destroy', $log->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus catatan log ini?');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="w-8 h-8 rounded-lg bg-gray-50 hover:bg-rose-50 text-gray-400 hover:text-rose-600 border border-gray-200 hover:border-rose-200 transition-colors inline-flex items-center justify-center text-xs" title="Hapus catatan ini">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="py-12 text-center">
                                    <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3 text-lg">
                                        <i class="fa-solid fa-clock-rotate-left"></i>
                                    </div>
                                    <p class="text-sm font-bold text-gray-700">Belum Ada Riwayat Koreksi Presensi</p>
                                    <p class="text-xs text-gray-400 mt-1">Setiap ada aksi koreksi atau penghapusan presensi dari menu Superadmin/HR akan tercatat otomatis di sini.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($logs->hasPages())
                <div class="p-4 border-t border-gray-100">
                    {{ $logs->links() }}
                </div>
            @endif
        </div>

        <!-- Hidden Forms untuk Hapus Massal & Kosongkan Semua -->
        <form id="clearAllForm" action="{{ route('superadmin.secret.absensi-logs.clear-all') }}" method="POST" class="hidden">
            @csrf
        </form>

        <form id="bulkDeleteForm" action="{{ route('superadmin.secret.absensi-logs.bulk-destroy') }}" method="POST" class="hidden">
            @csrf
            <div id="bulkDeleteIdsContainer"></div>
        </form>

    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const selectAll = document.getElementById('selectAllCheckbox');
    const checkboxes = document.querySelectorAll('.log-item-checkbox');
    const btnBulk = document.getElementById('btnBulkDelete');
    const countSpan = document.getElementById('selectedCount');

    function updateBulkButton() {
        const checked = document.querySelectorAll('.log-item-checkbox:checked');
        const count = checked.length;
        if (countSpan) countSpan.textContent = count;
        if (btnBulk) {
            if (count > 0) {
                btnBulk.classList.remove('hidden');
                btnBulk.classList.add('inline-flex');
            } else {
                btnBulk.classList.add('hidden');
                btnBulk.classList.remove('inline-flex');
            }
        }
        if (selectAll && checkboxes.length > 0) {
            selectAll.checked = checked.length === checkboxes.length;
        }
    }

    if (selectAll) {
        selectAll.addEventListener('change', function () {
            checkboxes.forEach(cb => cb.checked = selectAll.checked);
            updateBulkButton();
        });
    }

    checkboxes.forEach(cb => {
        cb.addEventListener('change', updateBulkButton);
    });

    window.confirmBulkDelete = function () {
        const checked = document.querySelectorAll('.log-item-checkbox:checked');
        if (checked.length === 0) {
            alert('Pilih setidaknya satu catatan untuk dihapus.');
            return;
        }

        if (confirm('Apakah Anda yakin ingin menghapus ' + checked.length + ' catatan riwayat yang dipilih?')) {
            const form = document.getElementById('bulkDeleteForm');
            const container = document.getElementById('bulkDeleteIdsContainer');
            container.innerHTML = '';
            checked.forEach(cb => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'ids[]';
                input.value = cb.value;
                container.appendChild(input);
            });
            form.submit();
        }
    };

    window.confirmClearAll = function () {
        if (confirm('PERINGATAN: Apakah Anda yakin ingin MENGOSONGKAN SELURUH riwayat log koreksi presensi? Tindakan ini permanen dan tidak dapat dibatalkan.')) {
            document.getElementById('clearAllForm').submit();
        }
    };
});
</script>
@endpush
@endsection
