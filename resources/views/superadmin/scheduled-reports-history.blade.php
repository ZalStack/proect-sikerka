@extends('layouts.app')

@section('content')
<div class="flex">
    @include('layouts.sidebar')

    <div class="flex-1 min-w-0 md:ml-64 p-4 sm:p-6 lg:p-8 space-y-6">

        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">
                    <a href="{{ route('superadmin.dashboard') }}" class="hover:text-[#00a2e9]">Super Admin</a>
                    <span>/</span>
                    <a href="{{ route('superadmin.reports.index') }}" class="hover:text-[#00a2e9]">Laporan Terjadwal</a>
                    <span>/</span>
                    <span class="text-gray-700">Riwayat</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-[#0F1245] font-['Poppins']">Riwayat Laporan</h1>
                <p class="text-xs sm:text-sm text-gray-500 font-light mt-0.5 max-w-2xl">
                    Seluruh file laporan yang pernah dibuat oleh scheduler maupun dijalankan manual.
                </p>
            </div>

            <a href="{{ route('superadmin.reports.index') }}"
               class="px-4 py-3 rounded-2xl bg-white border border-gray-200 text-gray-700 text-sm font-bold hover:bg-gray-50 transition-all flex items-center gap-2 self-start">
                <i class="fa-solid fa-arrow-left"></i> Kembali
            </a>
        </div>

        @include('superadmin.partials.flash')

        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm min-w-[760px]">
                    <thead class="bg-gray-50/70">
                        <tr class="text-left text-[11px] uppercase tracking-wider text-gray-400">
                            <th class="py-3 px-5 font-bold">Waktu</th>
                            <th class="py-3 px-3 font-bold">Jadwal</th>
                            <th class="py-3 px-3 font-bold">File</th>
                            <th class="py-3 px-3 font-bold">Ukuran</th>
                            <th class="py-3 px-3 font-bold">Status</th>
                            <th class="py-3 px-3 font-bold">Keterangan</th>
                            <th class="py-3 px-5 font-bold text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($reports as $r)
                            <tr class="hover:bg-gray-50/60">
                                <td class="py-3 px-5 text-xs text-gray-600 whitespace-nowrap">{{ $r->created_at?->format('d M Y H:i:s') }}</td>
                                <td class="py-3 px-3">
                                    <span class="text-xs font-semibold text-gray-800">{{ $r->scheduledReport?->name ?? 'Manual' }}</span>
                                    <br><span class="text-[10px] text-gray-400 uppercase">{{ $r->type }}</span>
                                </td>
                                <td class="py-3 px-3 text-[11px] font-mono text-gray-500 max-w-[240px] truncate" title="{{ $r->filename }}">{{ $r->filename }}</td>
                                <td class="py-3 px-3 text-xs text-gray-600">{{ $r->sizeHuman() }}</td>
                                <td class="py-3 px-3">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $r->status === 'success' ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">
                                        {{ $r->statusLabel() }}
                                    </span>
                                </td>
                                <td class="py-3 px-3 text-[11px] text-gray-500 max-w-[300px]">{{ $r->message }}</td>
                                <td class="py-3 px-5 text-right whitespace-nowrap">
                                    @if($r->status === 'success' && $r->filename !== '-')
                                        <a href="{{ route('superadmin.reports.download', $r->id) }}"
                                           class="px-3 py-1.5 rounded-lg bg-[#00a2e9]/10 hover:bg-[#00a2e9] text-[#0077b6] hover:text-white text-[11px] font-bold transition-all inline-flex items-center gap-1">
                                            <i class="fa-solid fa-download"></i> Unduh
                                        </a>
                                    @else
                                        <span class="text-[11px] text-gray-400">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-10 text-center text-sm text-gray-400">Belum ada laporan dibuat.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="flex justify-center">
            {{ $reports->links() }}
        </div>

    </div>
</div>
@endsection
