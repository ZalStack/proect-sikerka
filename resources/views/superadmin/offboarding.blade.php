@extends('layouts.app')

@section('content')
<div class="flex">
    @include('layouts.sidebar')

    <div class="flex-1 min-w-0 md:ml-64 p-4 sm:p-6 lg:p-8 space-y-6">

        <!-- Header -->
        <div class="flex flex-col xl:flex-row xl:items-end justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">
                    <a href="{{ route('superadmin.dashboard') }}" class="hover:text-[#00a2e9] transition-colors">Super Admin</a>
                    <span>/</span>
                    <span class="text-gray-700">Offboarding</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-[#0F1245] font-['Poppins'] tracking-tight">
                    Offboarding Karyawan Terpusat
                </h1>
                <p class="text-xs sm:text-sm text-gray-500 font-light mt-0.5 max-w-2xl">
                    Tandai resign, arsipkan, atau pulihkan karyawan dalam satu halaman. Seluruh riwayat presensi, cuti, dan laporan tetap tersimpan.
                </p>
            </div>

            <!-- Pencarian -->
            <form method="GET" action="{{ route('superadmin.offboarding.index') }}" class="flex items-center gap-2">
                <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama / NIP / email..."
                       class="px-4 py-2.5 rounded-xl border border-gray-200 focus:border-[#00a2e9] focus:ring-2 focus:ring-[#00a2e9]/20 text-sm w-56">
                <button type="submit" class="px-4 py-2.5 rounded-xl bg-white border border-gray-200 text-gray-600 text-xs font-bold hover:border-[#00a2e9]/40 transition-all">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </button>
            </form>
        </div>

        @include('superadmin.partials.flash')

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-4 sm:gap-5">

            <!-- KARYAWAN AKTIF -->
            <div class="xl:col-span-2 bg-white rounded-3xl p-5 sm:p-6 shadow-sm border border-gray-100">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-base font-bold text-[#0F1245] flex items-center gap-2">
                        <i class="fa-solid fa-users text-[#00a2e9]"></i> Karyawan Aktif
                    </h2>
                    <span class="text-xs text-gray-400">{{ $active->total() }} orang</span>
                </div>

                @if($active->count() === 0)
                    <div class="py-10 text-center text-sm text-gray-400">Tidak ada karyawan cocok dengan pencarian.</div>
                @else
                    <div class="overflow-x-auto border border-gray-100 rounded-2xl">
                        <table class="w-full text-xs">
                            <thead class="bg-gray-50 text-gray-500 uppercase tracking-wider">
                                <tr>
                                    <th class="px-3 py-2.5 text-left font-bold">Karyawan</th>
                                    <th class="px-3 py-2.5 text-left font-bold">Divisi</th>
                                    <th class="px-3 py-2.5 text-left font-bold">Bergabung</th>
                                    <th class="px-3 py-2.5 text-right font-bold">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @foreach($active as $k)
                                    <tr class="hover:bg-gray-50/60">
                                        <td class="px-3 py-2.5">
                                            <p class="font-medium text-gray-800">{{ $k->nama_lengkap }}</p>
                                            <p class="text-[10px] text-gray-400">{{ $k->kode_pegawai }} · {{ $k->email }}</p>
                                        </td>
                                        <td class="px-3 py-2.5 text-gray-500">{{ $k->divisi ?: '—' }}</td>
                                        <td class="px-3 py-2.5 text-gray-500">{{ $k->tanggal_bergabung?->format('d/m/Y') ?: '—' }}</td>
                                        <td class="px-3 py-2.5 text-right whitespace-nowrap">
                                            <button type="button"
                                                    class="px-2.5 py-1.5 rounded-lg bg-amber-50 text-amber-600 text-[10px] font-bold hover:bg-amber-100 transition-all"
                                                    onclick="document.getElementById('off-{{ $k->id }}').classList.toggle('hidden')">
                                                <i class="fa-solid fa-door-open"></i> Resign
                                            </button>
                                            <form action="{{ route('superadmin.offboarding.archive', $k->id) }}" method="POST" class="inline"
                                                  onsubmit="return confirm('Arsipkan {{ $k->nama_lengkap }}? Data tidak hilang, hanya disembunyikan dari daftar aktif.')">
                                                @csrf
                                                <button type="submit" class="px-2.5 py-1.5 rounded-lg bg-gray-100 text-gray-600 text-[10px] font-bold hover:bg-gray-200 transition-all">
                                                    <i class="fa-solid fa-box-archive"></i> Arsip
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    <!-- Form resign inline -->
                                    <tr id="off-{{ $k->id }}" class="hidden">
                                        <td colspan="4" class="px-3 py-3 bg-amber-50/60">
                                            <form action="{{ route('superadmin.offboarding.offboard', $k->id) }}" method="POST" class="flex flex-wrap items-end gap-3">
                                                @csrf
                                                <div>
                                                    <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Tanggal Resign <span class="text-rose-500">*</span></label>
                                                    <input type="date" name="tanggal_resign" value="{{ old('tanggal_resign', now()->toDateString()) }}"
                                                           min="{{ $k->tanggal_bergabung?->toDateString() }}" required
                                                           class="px-3 py-2 rounded-lg border border-gray-200 focus:border-amber-500 text-xs">
                                                </div>
                                                <div class="flex-1 min-w-[180px]">
                                                    <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Keterangan</label>
                                                    <input type="text" name="keterangan" value="{{ old('keterangan') }}" maxlength="500"
                                                           placeholder="Alasan resign (opsional, tercatat di audit)"
                                                           class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:border-amber-500 text-xs">
                                                </div>
                                                <button type="submit"
                                                        onclick="return confirm('Tandai {{ $k->nama_lengkap }} resign? Akun langsung diblokir login.')"
                                                        class="px-4 py-2 rounded-lg bg-amber-600 text-white text-xs font-bold hover:bg-amber-700 transition-all">
                                                    Konfirmasi Resign
                                                </button>
                                                @error('tanggal_resign')<p class="w-full text-xs text-rose-600">{{ $message }}</p>@enderror
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $active->links() }}
                    </div>
                @endif
            </div>

            <!-- KANAN: RESIGN + ARSIP -->
            <div class="space-y-5">

                <!-- SUDAH RESIGN -->
                <div class="bg-white rounded-3xl p-5 shadow-sm border border-gray-100">
                    <h2 class="text-sm font-bold text-[#0F1245] flex items-center gap-2 mb-3">
                        <i class="fa-solid fa-right-from-bracket text-amber-500"></i> Sudah Resign ({{ $resigned->count() }})
                    </h2>

                    @if($resigned->isEmpty())
                        <p class="text-xs text-gray-400 py-3">Belum ada karyawan resign.</p>
                    @else
                        <ul class="space-y-2.5">
                            @foreach($resigned as $k)
                                <li class="flex items-center justify-between gap-2 p-2.5 rounded-xl bg-amber-50/70 border border-amber-100">
                                    <div class="min-w-0">
                                        <p class="text-xs font-semibold text-gray-800 truncate">{{ $k->nama_lengkap }}</p>
                                        <p class="text-[10px] text-gray-500">
                                            {{ $k->kode_pegawai }} · resign {{ $k->tanggal_resign?->format('d/m/Y') ?: '?' }}
                                        </p>
                                    </div>
                                    <form action="{{ route('superadmin.offboarding.reactivate', $k->id) }}" method="POST" class="flex-shrink-0">
                                        @csrf
                                        <button type="submit" class="px-2.5 py-1.5 rounded-lg bg-white border border-amber-200 text-amber-700 text-[10px] font-bold hover:bg-amber-100 transition-all"
                                                title="Batalkan resign">
                                            <i class="fa-solid fa-rotate-left"></i> Aktifkan
                                        </button>
                                    </form>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>

                <!-- ARSIP (SOFT DELETE) -->
                <div class="bg-white rounded-3xl p-5 shadow-sm border border-gray-100">
                    <h2 class="text-sm font-bold text-[#0F1245] flex items-center gap-2 mb-3">
                        <i class="fa-solid fa-box-archive text-gray-400"></i> Arsip ({{ $archived->count() }})
                    </h2>

                    @if($archived->isEmpty())
                        <p class="text-xs text-gray-400 py-3">Tidak ada karyawan di arsip.</p>
                    @else
                        <ul class="space-y-2.5">
                            @foreach($archived as $k)
                                <li class="flex items-center justify-between gap-2 p-2.5 rounded-xl bg-gray-50 border border-gray-100">
                                    <div class="min-w-0">
                                        <p class="text-xs font-semibold text-gray-700 truncate">{{ $k->nama_lengkap }}</p>
                                        <p class="text-[10px] text-gray-400">
                                            {{ $k->kode_pegawai }} · diarsipkan {{ $k->deleted_at?->format('d/m/Y') }}
                                        </p>
                                    </div>
                                    <form action="{{ route('superadmin.offboarding.restore', $k->id) }}" method="POST" class="flex-shrink-0">
                                        @csrf
                                        <button type="submit" class="px-2.5 py-1.5 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-700 text-[10px] font-bold hover:bg-emerald-100 transition-all"
                                                title="Pulihkan dari arsip">
                                            <i class="fa-solid fa-trash-arrow-up"></i> Pulihkan
                                        </button>
                                    </form>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>

                <!-- INFO -->
                <div class="bg-[#0F1245] rounded-3xl p-5 shadow-lg text-white space-y-2.5">
                    <h2 class="text-sm font-bold flex items-center gap-2">
                        <i class="fa-solid fa-lightbulb text-[#FCC626]"></i> Resign vs Arsip
                    </h2>
                    <ul class="text-xs text-white/75 space-y-2 leading-relaxed">
                        <li><b class="text-[#FCC626]">Resign:</b> karyawan ditandai keluar pada tanggal tertentu — login diblokir, tetapi data & riwayat tetap tampil di laporan.</li>
                        <li><b class="text-[#FCC626]">Arsip:</b> karyawan disembunyikan dari seluruh daftar (soft delete) — bisa dipulihkan kapan saja tanpa kehilangan data.</li>
                        <li><b class="text-[#FCC626]">Keduanya tercatat</b> di Log Aktivitas sebagai jejak audit siapa dan kapan.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
