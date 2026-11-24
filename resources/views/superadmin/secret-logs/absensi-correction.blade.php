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

            <div class="flex items-center gap-2">
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
                            <th class="py-3 px-4">Waktu & IP</th>
                            <th class="py-3 px-4">Operator Pengubah</th>
                            <th class="py-3 px-4">Target Karyawan</th>
                            <th class="py-3 px-4">Tgl Presensi</th>
                            <th class="py-3 px-4">Tindakan</th>
                            <th class="py-3 px-4 min-w-[280px]">Rincian Perubahan (Sebelum ➔ Sesudah)</th>
                            <th class="py-3 px-4">Alasan / Ket</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($logs as $log)
                            <tr class="hover:bg-slate-50/80 transition-colors">
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
                                    @if($log->action === 'hapus')
                                        <div class="text-rose-600 font-medium">Data presensi dihapus dari sistem.</div>
                                        <div class="text-[11px] text-gray-500 mt-1">
                                            Status sebelumnya: <span class="font-bold">{{ $log->old_data['status'] ?? '-' }}</span> |
                                            Masuk: {{ $log->old_data['check_in'] ?? '-' }} |
                                            Pulang: {{ $log->old_data['check_out'] ?? '-' }}
                                        </div>
                                    @else
                                        <div class="space-y-1">
                                            {{-- Status --}}
                                            @if(($log->old_data['status'] ?? '') !== ($log->new_data['status'] ?? ''))
                                                <div class="flex items-center gap-1.5">
                                                    <span class="text-gray-400 font-semibold text-[10px] w-12">Status:</span>
                                                    <span class="px-1.5 py-0.5 rounded bg-gray-100 text-gray-600 font-medium line-through">{{ $log->old_data['status'] ?? '-' }}</span>
                                                    <i class="fa-solid fa-arrow-right text-[9px] text-gray-400"></i>
                                                    <span class="px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-800 font-bold">{{ $log->new_data['status'] ?? '-' }}</span>
                                                </div>
                                            @else
                                                <div class="text-[11px] text-gray-500">Status: <span class="font-medium text-gray-700">{{ $log->new_data['status'] ?? '-' }}</span></div>
                                            @endif

                                            {{-- Jam Masuk --}}
                                            @php
                                                $oldIn = isset($log->old_data['check_in']) ? substr($log->old_data['check_in'], 0, 5) : '-';
                                                $newIn = isset($log->new_data['check_in']) ? substr($log->new_data['check_in'], 0, 5) : '-';
                                            @endphp
                                            @if($oldIn !== $newIn)
                                                <div class="flex items-center gap-1.5">
                                                    <span class="text-gray-400 font-semibold text-[10px] w-12">Masuk:</span>
                                                    <span class="px-1.5 py-0.5 rounded bg-gray-100 text-gray-600 font-mono line-through">{{ $oldIn }}</span>
                                                    <i class="fa-solid fa-arrow-right text-[9px] text-gray-400"></i>
                                                    <span class="px-1.5 py-0.5 rounded bg-blue-100 text-blue-800 font-mono font-bold">{{ $newIn }}</span>
                                                </div>
                                            @endif

                                            {{-- Jam Pulang --}}
                                            @php
                                                $oldOut = isset($log->old_data['check_out']) ? substr($log->old_data['check_out'], 0, 5) : '-';
                                                $newOut = isset($log->new_data['check_out']) ? substr($log->new_data['check_out'], 0, 5) : '-';
                                            @endphp
                                            @if($oldOut !== $newOut)
                                                <div class="flex items-center gap-1.5">
                                                    <span class="text-gray-400 font-semibold text-[10px] w-12">Pulang:</span>
                                                    <span class="px-1.5 py-0.5 rounded bg-gray-100 text-gray-600 font-mono line-through">{{ $oldOut }}</span>
                                                    <i class="fa-solid fa-arrow-right text-[9px] text-gray-400"></i>
                                                    <span class="px-1.5 py-0.5 rounded bg-blue-100 text-blue-800 font-mono font-bold">{{ $newOut }}</span>
                                                </div>
                                            @endif

                                            {{-- Total Jam Kerja --}}
                                            @php
                                                $oldJam = $log->old_data['total_jam_kerja'] ?? 0;
                                                $newJam = $log->new_data['total_jam_kerja'] ?? 0;
                                            @endphp
                                            @if($oldJam != $newJam)
                                                <div class="flex items-center gap-1.5 text-[10px]">
                                                    <span class="text-gray-400 font-semibold w-12">Total:</span>
                                                    <span class="text-gray-500">{{ $oldJam }} Jam</span>
                                                    <i class="fa-solid fa-arrow-right text-[9px] text-gray-400"></i>
                                                    <span class="font-bold text-emerald-700">{{ $newJam }} Jam</span>
                                                </div>
                                            @endif
                                        </div>
                                    @endif
                                </td>

                                <td class="py-3.5 px-4 text-gray-600 max-w-[200px] truncate" title="{{ $log->keterangan }}">
                                    {{ $log->keterangan ?: '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-12 text-center">
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

    </div>
</div>
@endsection
