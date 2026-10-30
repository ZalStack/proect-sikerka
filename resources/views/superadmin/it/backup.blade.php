@extends('layouts.app')

@section('content')
<div class="flex">
    @include('layouts.sidebar')

    <div class="flex-1 min-w-0 md:ml-64 p-4 sm:p-6 lg:p-8 space-y-6">

        <!-- Header -->
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">
                    <a href="{{ route('superadmin.dashboard') }}" class="hover:text-[#00a2e9] transition-colors">Super Admin</a>
                    <span>/</span>
                    <span class="text-gray-700">Pemeliharaan IT</span>
                    <span>/</span>
                    <span class="text-gray-700">Backup Database</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-[#0F1245] font-['Poppins'] tracking-tight">
                    Backup Database
                </h1>
                <p class="text-xs sm:text-sm text-gray-500 font-light mt-0.5 max-w-2xl">
                    Buat, unduh, dan kelola cadangan database SIKEKAR. File backup disimpan di penyimpanan privat dan hanya dapat diakses oleh Super Admin.
                </p>
            </div>

            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
                <a href="{{ route('superadmin.dashboard') }}" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold transition-all">
                    <i class="fa-solid fa-arrow-left"></i>
                    <span>Dashboard</span>
                </a>

                <form action="{{ route('superadmin.backup.store') }}" method="POST" class="w-full sm:w-auto" onsubmit="const btn=this.querySelector('button[type=submit]'); btn.disabled=true; btn.classList.add('opacity-70','cursor-wait'); btn.innerHTML='<i class=&quot;fa-solid fa-circle-notch fa-spin&quot;></i><span>Membuat Backup...</span>';">
                    @csrf
                    <input type="hidden" name="label" value="manual">
                    <button type="submit" class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-gradient-to-r from-[#00a2e9] to-[#0077b6] hover:from-[#0095d6] hover:to-[#006aa1] text-white text-xs font-bold transition-all shadow-lg shadow-[#00a2e9]/25 flex items-center justify-center gap-2">
                        <i class="fa-solid fa-cloud-arrow-up"></i>
                        <span>Buat Backup Sekarang</span>
                    </button>
                </form>
            </div>
        </div>

        @include('superadmin.partials.flash')

        <!-- KPI Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
            <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm hover:shadow-md transition-all duration-300 border-l-4 border-l-[#FCC626]">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Backup Terakhir</p>
                        <p class="text-2xl font-extrabold text-[#0F1245] mt-1">
                            {{ $stats['last'] ? $stats['last']['age'] : 'Belum Ada' }}
                        </p>
                        <p class="text-[11px] text-slate-400 mt-1 truncate max-w-[210px]" title="{{ $stats['last']['filename'] ?? 'Belum ada backup' }}">
                            {{ $stats['last']['filename'] ?? 'Buat backup pertama Anda' }}
                        </p>
                    </div>
                    <div class="w-11 h-11 rounded-2xl bg-[#FCC626]/15 text-[#d49b06] flex items-center justify-center text-lg shrink-0">
                        <i class="fa-solid fa-clock-rotate-left"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm hover:shadow-md transition-all duration-300 border-l-4 border-l-[#00a2e9]">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Backup</p>
                        <p class="text-2xl font-extrabold text-[#0F1245] mt-1">{{ $stats['count'] }} file</p>
                        <p class="text-[11px] text-slate-400 mt-1">Retensi {{ $stats['retention'] }} backup terbaru</p>
                    </div>
                    <div class="w-11 h-11 rounded-2xl bg-[#00a2e9]/10 text-[#00a2e9] flex items-center justify-center text-lg shrink-0">
                        <i class="fa-solid fa-database"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm hover:shadow-md transition-all duration-300 border-l-4 border-l-[#2E7D3E]">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Ukuran</p>
                        <p class="text-2xl font-extrabold text-[#0F1245] mt-1">{{ $stats['total_size_human'] }}</p>
                        <p class="text-[11px] text-slate-400 mt-1">Terpakai di folder backup</p>
                    </div>
                    <div class="w-11 h-11 rounded-2xl bg-[#2E7D3E]/10 text-[#2E7D3E] flex items-center justify-center text-lg shrink-0">
                        <i class="fa-solid fa-hard-drive"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm hover:shadow-md transition-all duration-300 border-l-4 {{ $stats['writable'] ? 'border-l-[#2E7D3E]' : 'border-l-[#ec1d1d]' }}">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Status Folder</p>
                        <p class="text-2xl font-extrabold {{ $stats['writable'] ? 'text-[#2E7D3E]' : 'text-[#ec1d1d]' }} mt-1">
                            {{ $stats['writable'] ? 'Writable' : 'Read-only' }}
                        </p>
                        <p class="text-[11px] text-slate-400 mt-1 truncate max-w-[210px]" title="{{ $stats['path'] }}">
                            storage/app/backups
                        </p>
                    </div>
                    <div class="w-11 h-11 rounded-2xl {{ $stats['writable'] ? 'bg-[#2E7D3E]/10 text-[#2E7D3E]' : 'bg-[#ec1d1d]/10 text-[#ec1d1d]' }} flex items-center justify-center text-lg shrink-0">
                        <i class="fa-solid {{ $stats['writable'] ? 'fa-folder-open' : 'fa-folder-minus' }}"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Info banner -->
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-[#0F1245] via-[#161758] to-[#27438D] p-5 sm:p-6 text-white shadow-lg shadow-[#161758]/15">
            <div class="absolute -right-10 -top-10 w-40 h-40 rounded-full bg-[#00a2e9]/20 blur-3xl"></div>
            <div class="absolute -left-10 -bottom-10 w-40 h-40 rounded-full bg-[#FCC626]/15 blur-3xl"></div>
            <div class="relative flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 rounded-xl bg-white/10 border border-white/15 flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-shield-halved text-[#FCC626]"></i>
                    </div>
                    <div>
                        <p class="text-sm font-bold">Backup tersimpan aman di penyimpanan privat</p>
                        <p class="text-xs text-white/70 mt-1 max-w-2xl">
                            File tidak dapat diakses langsung melalui URL publik. Backup otomatis berjalan setiap hari pukul
                            <span class="font-semibold text-white">{{ config('backup.schedule', '01:00') }}</span>
                            dan hanya menyimpan {{ config('backup.retention', 14) }} file terbaru.
                        </p>
                    </div>
                </div>
                <a href="{{ route('superadmin.health.index') }}" class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-xl bg-white/10 hover:bg-white/20 border border-white/20 text-xs font-semibold transition-all shrink-0">
                    <i class="fa-solid fa-heart-pulse text-[#FCC626]"></i>
                    <span>Kesehatan Sistem</span>
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
            <!-- Backup list -->
            <div class="xl:col-span-2 bg-white rounded-3xl p-5 sm:p-6 shadow-sm border border-gray-100 space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                        <h2 class="text-lg font-bold text-[#0F1245] flex items-center gap-2">
                            <i class="fa-solid fa-clock-rotate-left text-[#00a2e9]"></i>
                            Riwayat Backup
                        </h2>
                        <p class="text-xs text-gray-500 mt-0.5">Daftar file backup yang tersedia untuk diunduh.</p>
                    </div>
                    <span class="inline-flex items-center gap-1.5 self-start sm:self-auto px-3 py-1 rounded-full bg-gray-100 text-gray-600 text-[11px] font-bold">
                        <i class="fa-solid fa-file-zipper text-[#00a2e9]"></i>
                        {{ $stats['count'] }} file
                    </span>
                </div>

                <div class="overflow-x-auto rounded-2xl border border-gray-100">
                    <table class="w-full text-left text-xs sm:text-sm text-gray-600 min-w-[620px]">
                        <thead class="bg-gray-50/80 text-gray-700 font-semibold text-xs border-b border-gray-100">
                            <tr>
                                <th class="px-4 py-3.5">File Backup</th>
                                <th class="px-4 py-3.5">Dibuat</th>
                                <th class="px-4 py-3.5">Ukuran</th>
                                <th class="px-4 py-3.5 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($backups as $backup)
                                <tr class="hover:bg-blue-50/25 transition-colors">
                                    <td class="px-4 py-3.5">
                                        <div class="flex items-center gap-3">
                                            <div class="w-9 h-9 rounded-xl bg-[#00a2e9]/10 text-[#00a2e9] flex items-center justify-center shrink-0">
                                                <i class="fa-solid fa-file-zipper"></i>
                                            </div>
                                            <div class="min-w-0">
                                                <p class="font-bold text-gray-900 truncate max-w-[260px]" title="{{ $backup['filename'] }}">
                                                    {{ $backup['filename'] }}
                                                </p>
                                                <p class="text-[11px] text-gray-400">{{ $backup['age'] }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3.5 whitespace-nowrap">
                                        <div class="font-semibold text-gray-800">{{ $backup['created_at']->translatedFormat('d M Y') }}</div>
                                        <div class="text-[11px] text-gray-400">{{ $backup['created_at']->format('H:i:s') }} WIB</div>
                                    </td>
                                    <td class="px-4 py-3.5 whitespace-nowrap">
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-gray-100 text-gray-700 text-[11px] font-bold">
                                            {{ $backup['size_human'] }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3.5">
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="{{ route('superadmin.backup.download', $backup['filename']) }}"
                                                class="px-3 py-1.5 rounded-lg bg-[#00a2e9]/10 hover:bg-[#00a2e9] text-[#0077b6] hover:text-white text-xs font-bold transition-all flex items-center gap-1.5">
                                                <i class="fa-solid fa-download"></i>
                                                <span>Unduh</span>
                                            </a>

                                            <form action="{{ route('superadmin.backup.destroy', $backup['filename']) }}" method="POST"
                                                onsubmit="return confirm('Hapus file backup {{ $backup['filename'] }}? Tindakan ini tidak dapat dibatalkan.')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="px-3 py-1.5 rounded-lg bg-rose-50 hover:bg-rose-600 text-rose-600 hover:text-white text-xs font-bold transition-all flex items-center gap-1.5">
                                                    <i class="fa-solid fa-trash-can"></i>
                                                    <span>Hapus</span>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-12 text-center">
                                        <div class="w-14 h-14 rounded-2xl bg-gray-50 text-gray-300 flex items-center justify-center mx-auto mb-3 text-2xl">
                                            <i class="fa-solid fa-database"></i>
                                        </div>
                                        <p class="text-sm font-bold text-gray-700">Belum ada backup</p>
                                        <p class="text-xs text-gray-400 mt-1">Klik tombol “Buat Backup Sekarang” untuk membuat cadangan pertama.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Side panel -->
            <div class="space-y-6">
                <div class="bg-white rounded-3xl p-5 sm:p-6 shadow-sm border border-gray-100">
                    <h3 class="text-base font-bold text-[#0F1245] flex items-center gap-2">
                        <i class="fa-solid fa-calendar-check text-[#00a2e9]"></i>
                        Jadwal & Retensi
                    </h3>
                    <div class="mt-4 space-y-3">
                        <div class="flex items-center justify-between gap-3 p-3 rounded-xl bg-gray-50 border border-gray-100">
                            <span class="text-xs text-gray-500">Backup otomatis</span>
                            <span class="text-xs font-bold text-gray-800">Setiap hari {{ config('backup.schedule', '01:00') }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-3 p-3 rounded-xl bg-gray-50 border border-gray-100">
                            <span class="text-xs text-gray-500">Retensi file</span>
                            <span class="text-xs font-bold text-gray-800">{{ config('backup.retention', 14) }} backup terbaru</span>
                        </div>
                        <div class="flex items-center justify-between gap-3 p-3 rounded-xl bg-gray-50 border border-gray-100">
                            <span class="text-xs text-gray-500">Lokasi penyimpanan</span>
                            <span class="text-xs font-bold text-gray-800">storage/app/backups</span>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-3xl p-5 sm:p-6 shadow-sm border border-gray-100">
                    <h3 class="text-base font-bold text-[#0F1245] flex items-center gap-2">
                        <i class="fa-solid fa-rotate-left text-[#d49b06]"></i>
                        Panduan Restore
                    </h3>
                    <p class="text-xs text-gray-500 mt-2 leading-relaxed">
                        Restore dari halaman ini sengaja tidak disediakan untuk mencegah kesalahan operasional. Lakukan restore melalui CLI server dengan file backup yang sudah diunduh.
                    </p>

                    <div class="mt-4 space-y-2">
                        <p class="text-[11px] font-bold uppercase tracking-wider text-gray-400">Backup .sql.gz</p>
                        <pre class="bg-[#0F1245] text-emerald-300 text-[11px] rounded-xl p-3 overflow-x-auto leading-relaxed"><code>gunzip -c sikekar-YYYYmmdd-HHMMSS-manual.sql.gz | mysql -u root -p sikekar</code></pre>
                    </div>

                    <div class="mt-3 p-3 rounded-xl bg-amber-50 border border-amber-100">
                        <p class="text-[11px] text-amber-800 leading-relaxed">
                            <i class="fa-solid fa-triangle-exclamation mr-1"></i>
                            Selalu buat backup baru sebelum melakukan restore. Pastikan aplikasi dalam mode maintenance agar tidak ada perubahan data saat proses restore.
                        </p>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
