@extends('layouts.app')

@section('content')
<div class="flex">
    @include('layouts.sidebar')

    <div class="flex-1 min-w-0 md:ml-64 p-4 sm:p-6 lg:p-8 space-y-6">
        
        <!-- Header & Breadcrumbs -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">
                    <a href="{{ route('superadmin.dashboard') }}" class="hover:text-[#00a2e9]">Super Admin</a>
                    <span>/</span>
                    <span class="text-gray-700">Kalender & Hari Libur</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-[#0F1245] font-['Poppins']">
                    Kelola Hari Libur & Tanggal Merah
                </h1>
                <p class="text-xs sm:text-sm text-gray-500 font-light mt-0.5">
                    Daftar tanggal merah nasional dan libur khusus kantor KPM. Absensi di tanggal ini otomatis <strong>menambah hari kerja</strong> dan <strong>tidak dihitung lembur</strong> (Lembur = 0).
                </p>
            </div>

            <!-- Filter Tahun & Sinkronisasi -->
            <div class="flex flex-wrap items-center gap-3">
                <form action="{{ route('superadmin.hari-libur.index') }}" method="GET" class="flex items-center gap-2">
                    <label for="tahun" class="text-xs font-bold text-gray-600">Tahun:</label>
                    <select name="tahun" id="tahun" onchange="this.form.submit()" class="px-3 py-2 rounded-xl border border-gray-200 bg-white text-xs font-bold text-[#0F1245] focus:ring-2 focus:ring-[#00a2e9]/20 focus:border-[#00a2e9]">
                        @foreach($availableYears as $y)
                            <option value="{{ $y }}" {{ $y == $selectedYear ? 'selected' : '' }}>{{ $y }}</option>
                        @endforeach
                    </select>
                </form>

                <form action="{{ route('superadmin.hari-libur.sync') }}" method="POST" onsubmit="return confirm('Tarik daftar hari libur nasional resmi untuk tahun {{ $selectedYear }}?')">
                    @csrf
                    <input type="hidden" name="tahun" value="{{ $selectedYear }}">
                    <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-[#00a2e9] hover:bg-[#008ac7] text-white text-xs font-bold shadow-sm transition-all hover:scale-105">
                        <i class="fa-solid fa-cloud-arrow-down"></i>
                        <span>Tarik Libur Nasional {{ $selectedYear }}</span>
                    </button>
                </form>
            </div>
        </div>

        <!-- Flash Alert -->
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

        <!-- Grid: Form Tambah Manual & Tabel Daftar Libur -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
            
            <!-- FORM TAMBAH LIBUR MANUAL -->
            <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100 space-y-4">
                <div class="flex items-center gap-3 pb-3 border-b border-gray-100">
                    <div class="w-10 h-10 rounded-xl bg-[#FCC626]/20 text-[#B8860B] flex items-center justify-center text-lg">
                        <i class="fa-solid fa-calendar-plus"></i>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-gray-900">Tambah Hari Libur</h2>
                        <p class="text-xs text-gray-500">Khusus KPM atau tanggal merah tambahan</p>
                    </div>
                </div>

                <form action="{{ route('superadmin.hari-libur.store') }}" method="POST" class="space-y-4">
                    @csrf
                    
                    <div>
                        <label for="tanggal" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                            Tanggal Libur <span class="text-rose-500">*</span>
                        </label>
                        <input type="date" name="tanggal" id="tanggal" value="{{ old('tanggal') }}" required class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-sm font-medium focus:ring-2 focus:ring-[#00a2e9]/20 focus:border-[#00a2e9]">
                        @error('tanggal')
                            <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="keterangan" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                            Keterangan Libur <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="keterangan" id="keterangan" value="{{ old('keterangan') }}" placeholder="Contoh: Milad KPM / Libur Idul Fitri" required class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-sm font-medium focus:ring-2 focus:ring-[#00a2e9]/20 focus:border-[#00a2e9]">
                        @error('keterangan')
                            <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="jenis" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                            Kategori Libur
                        </label>
                        <select name="jenis" id="jenis" class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-sm font-medium focus:ring-2 focus:ring-[#00a2e9]/20 focus:border-[#00a2e9]">
                            <option value="nasional">Hari Libur Nasional</option>
                            <option value="khusus_kpm" selected>Libur Khusus Kantor KPM</option>
                            <option value="cuti_bersama">Cuti Bersama Resmi</option>
                        </select>
                    </div>

                    <button type="submit" class="w-full py-2.5 px-4 rounded-xl bg-[#2E7D3E] hover:bg-[#256832] text-white text-xs font-bold shadow-sm transition-all flex items-center justify-center gap-2">
                        <i class="fa-solid fa-plus"></i>
                        <span>Simpan Hari Libur</span>
                    </button>
                </form>

                <div class="mt-4 p-3 bg-amber-50 rounded-2xl border border-amber-200 text-amber-900 text-xs leading-relaxed">
                    <p class="font-bold flex items-center gap-1.5 mb-1">
                        <i class="fa-solid fa-circle-info text-amber-600"></i> Catatan Aturan:
                    </p>
                    Hari <strong>Minggu</strong> dan <strong>Senin</strong> otomatis diakui libur oleh sistem. Halaman ini digunakan untuk tanggal merah tambahan / nasional di hari kerja (Selasa - Sabtu).
                </div>
            </div>

            <!-- TABEL DAFTAR HARI LIBUR -->
            <div class="lg:col-span-2 bg-white rounded-3xl p-6 shadow-sm border border-gray-100 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                    <div>
                        <h2 class="text-base font-bold text-gray-900">Daftar Hari Libur (Tahun {{ $selectedYear }})</h2>
                        <p class="text-xs text-gray-500">Total: {{ $hariLiburs->count() }} hari libur terdaftar</p>
                    </div>
                </div>

                @if($hariLiburs->isEmpty())
                    <div class="text-center py-12 px-4">
                        <div class="w-16 h-16 rounded-3xl bg-gray-100 text-gray-400 flex items-center justify-center text-2xl mx-auto mb-3">
                            <i class="fa-regular fa-calendar-xmark"></i>
                        </div>
                        <h3 class="text-sm font-bold text-gray-700">Belum Ada Tanggal Merah untuk Tahun {{ $selectedYear }}</h3>
                        <p class="text-xs text-gray-500 max-w-sm mx-auto mt-1 mb-4">
                            Klik tombol "Tarik Libur Nasional {{ $selectedYear }}" di atas atau gunakan form di sebelah kiri untuk menambah hari libur manual.
                        </p>
                    </div>
                @else
                    <div class="overflow-x-auto rounded-2xl border border-gray-100">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-gray-50/80 text-gray-600 uppercase font-bold text-[10px] tracking-wider border-b border-gray-100">
                                <tr>
                                    <th class="px-4 py-3 text-center w-12">No</th>
                                    <th class="px-4 py-3">Tanggal & Hari</th>
                                    <th class="px-4 py-3">Keterangan</th>
                                    <th class="px-4 py-3 text-center">Kategori</th>
                                    <th class="px-4 py-3 text-center w-20">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 font-medium">
                                @foreach($hariLiburs as $index => $item)
                                    @php
                                        $carbonDate = \Carbon\Carbon::parse($item->tanggal);
                                        $isWeekend = in_array($carbonDate->dayOfWeekIso, [1, 7]);
                                    @endphp
                                    <tr class="hover:bg-gray-50/60 transition-colors">
                                        <td class="px-4 py-3 text-center text-gray-400 font-bold">{{ $index + 1 }}</td>
                                        <td class="px-4 py-3">
                                            <div class="font-bold text-gray-900">{{ $carbonDate->isoFormat('D MMMM Y') }}</div>
                                            <div class="text-[11px] {{ $isWeekend ? 'text-rose-500 font-semibold' : 'text-gray-500' }}">
                                                {{ $carbonDate->isoFormat('dddd') }}
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 text-gray-800">
                                            <span class="font-semibold">{{ $item->keterangan }}</span>
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            @if($item->jenis === 'nasional')
                                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-100 text-rose-700">Libur Nasional</span>
                                            @elseif($item->jenis === 'cuti_bersama')
                                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-blue-100 text-blue-700">Cuti Bersama</span>
                                            @else
                                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-purple-100 text-purple-700">Khusus KPM</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <form action="{{ route('superadmin.hari-libur.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Hapus hari libur {{ $item->keterangan }}?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="w-8 h-8 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-600 transition-colors flex items-center justify-center mx-auto" title="Hapus">
                                                    <i class="fa-solid fa-trash-can text-xs"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

        </div>

    </div>
</div>
@endsection
