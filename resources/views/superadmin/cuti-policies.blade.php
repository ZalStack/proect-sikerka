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
                    <span class="text-gray-700">Kebijakan Cuti</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-[#0F1245] font-['Poppins'] tracking-tight">
                    Kebijakan &amp; Kuota Cuti
                </h1>
                <p class="text-xs sm:text-sm text-gray-500 font-light mt-0.5 max-w-2xl">
                    Atur jatah cuti per tahun &amp; per jenis, dengan cakupan golongan opsional. Tanpa kebijakan, sistem memakai default 12 hari.
                </p>
            </div>

            <!-- Pilih tahun -->
            <form method="GET" action="{{ route('superadmin.cuti-policies.index') }}" class="flex items-center gap-2">
                <label for="tahun" class="text-xs font-bold text-gray-500 uppercase tracking-wider">Tahun</label>
                <select name="tahun" id="tahun" onchange="this.form.submit()"
                        class="px-4 py-2.5 rounded-xl border border-gray-200 focus:border-[#00a2e9] focus:ring-2 focus:ring-[#00a2e9]/20 text-sm font-bold">
                    @foreach($tahunList as $t)
                        <option value="{{ $t }}" {{ $t === $tahun ? 'selected' : '' }}>{{ $t }}</option>
                    @endforeach
                </select>
            </form>
        </div>

        @include('superadmin.partials.flash')

        <!-- Statistik -->
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
            <div class="bg-white rounded-2xl p-4 border border-gray-100 shadow-sm">
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Kebijakan {{ $tahun }}</p>
                <p class="text-2xl font-extrabold text-[#0F1245] mt-0.5">{{ $stats['total'] }}</p>
            </div>
            <div class="bg-white rounded-2xl p-4 border border-gray-100 shadow-sm">
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Aktif</p>
                <p class="text-2xl font-extrabold text-emerald-600 mt-0.5">{{ $stats['aktif'] }}</p>
            </div>
            <div class="bg-white rounded-2xl p-4 border border-gray-100 shadow-sm col-span-2 sm:col-span-1">
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Tanpa Kebijakan</p>
                <p class="text-xs text-gray-500 mt-1.5 leading-relaxed">Jenis cuti tanpa baris di tahun ini otomatis memakai kuota <b>12 hari</b>.</p>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-4 sm:gap-5">

            <!-- FORM -->
            <div class="xl:col-span-1 bg-white rounded-3xl p-5 sm:p-6 shadow-sm border border-gray-100 h-fit">
                <h2 class="text-base font-bold text-[#0F1245] flex items-center gap-2 mb-1">
                    <i class="fa-solid fa-plus text-[#00a2e9]"></i> Tambah Kebijakan
                </h2>
                <p class="text-xs text-gray-500 mb-5">Kombinasi tahun + jenis + golongan bersifat unik.</p>

                <form action="{{ route('superadmin.cuti-policies.store') }}" method="POST" class="space-y-4">
                    @csrf

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label for="cp_tahun" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Tahun <span class="text-rose-500">*</span></label>
                            <input type="number" name="tahun" id="cp_tahun" value="{{ old('tahun', $tahun) }}" min="2020" max="2100" required
                                   class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-[#00a2e9] focus:ring-2 focus:ring-[#00a2e9]/20 text-sm">
                            @error('tahun')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="cp_kuota" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Kuota (hari) <span class="text-rose-500">*</span></label>
                            <input type="number" name="kuota_hari" id="cp_kuota" value="{{ old('kuota_hari', 12) }}" min="0" max="365" required
                                   class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-[#00a2e9] focus:ring-2 focus:ring-[#00a2e9]/20 text-sm">
                            @error('kuota_hari')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div>
                        <label for="cp_jenis" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Jenis Cuti <span class="text-rose-500">*</span></label>
                        <input type="text" name="jenis_cuti" id="cp_jenis" value="{{ old('jenis_cuti', 'Cuti Tahunan') }}" required maxlength="50"
                               list="jenis-cuti-list"
                               class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-[#00a2e9] focus:ring-2 focus:ring-[#00a2e9]/20 text-sm">
                        <datalist id="jenis-cuti-list">
                            <option value="Cuti Tahunan">
                            <option value="Cuti Besar">
                            <option value="Cuti Melahirkan">
                            <option value="Cuti Penting">
                        </datalist>
                        @error('jenis_cuti')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="cp_scope" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Golongan (opsional)</label>
                        <input type="text" name="scope_golongan" id="cp_scope" value="{{ old('scope_golongan') }}" maxlength="100"
                               placeholder="Kosongkan = semua golongan"
                               list="golongan-list"
                               class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-[#00a2e9] focus:ring-2 focus:ring-[#00a2e9]/20 text-sm">
                        <datalist id="golongan-list">
                            @foreach(\App\Models\MasterData::options('golongan') as $g)
                                <option value="{{ $g }}">
                            @endforeach
                        </datalist>
                        <p class="text-[11px] text-gray-400 mt-1">Beberapa golongan dipisah koma (mis. <code class="bg-gray-100 px-1 rounded">Tetap,Non-Tetap</code>).</p>
                        @error('scope_golongan')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="cp_ket" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Keterangan</label>
                        <textarea name="keterangan" id="cp_ket" rows="2" maxlength="500"
                                  placeholder="Dasar kebijakan / catatan (opsional)"
                                  class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-[#00a2e9] focus:ring-2 focus:ring-[#00a2e9]/20 text-sm">{{ old('keterangan') }}</textarea>
                        @error('keterangan')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
                    </div>

                    <button type="submit" class="w-full px-5 py-2.5 rounded-xl bg-[#0F1245] hover:bg-[#161758] text-white text-xs font-bold transition-all shadow-md flex items-center justify-center gap-2">
                        <i class="fa-solid fa-floppy-disk"></i> Simpan Kebijakan
                    </button>
                </form>
            </div>

            <!-- DAFTAR -->
            <div class="xl:col-span-2 bg-white rounded-3xl p-5 sm:p-6 shadow-sm border border-gray-100">
                <h2 class="text-base font-bold text-[#0F1245] flex items-center gap-2 mb-4">
                    <i class="fa-solid fa-umbrella-beach text-[#00a2e9]"></i>
                    Kebijakan Tahun {{ $tahun }}
                </h2>

                @if($policies->isEmpty())
                    <div class="py-12 text-center">
                        <div class="w-14 h-14 mx-auto rounded-2xl bg-gray-100 flex items-center justify-center text-2xl text-gray-400 mb-3">
                            <i class="fa-solid fa-inbox"></i>
                        </div>
                        <p class="text-sm font-semibold text-gray-600">Belum ada kebijakan cuti untuk tahun {{ $tahun }}.</p>
                        <p class="text-xs text-gray-400 mt-1">Semua jenis cuti masih memakai default 12 hari.</p>
                    </div>
                @else
                    <div class="overflow-x-auto border border-gray-100 rounded-2xl">
                        <table class="w-full text-xs">
                            <thead class="bg-gray-50 text-gray-500 uppercase tracking-wider">
                                <tr>
                                    <th class="px-3 py-2.5 text-left font-bold">Jenis Cuti</th>
                                    <th class="px-3 py-2.5 text-center font-bold">Kuota</th>
                                    <th class="px-3 py-2.5 text-left font-bold">Golongan</th>
                                    <th class="px-3 py-2.5 text-left font-bold">Keterangan</th>
                                    <th class="px-3 py-2.5 text-center font-bold">Status</th>
                                    <th class="px-3 py-2.5 text-right font-bold">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @foreach($policies as $p)
                                    <tr class="hover:bg-gray-50/60">
                                        <td class="px-3 py-2.5 font-medium text-gray-800">{{ $p->jenis_cuti }}</td>
                                        <td class="px-3 py-2.5 text-center">
                                            <span class="px-2 py-0.5 rounded-lg bg-[#00a2e9]/10 text-[#00a2e9] font-extrabold">{{ $p->kuota_hari }} hari</span>
                                        </td>
                                        <td class="px-3 py-2.5 text-gray-500">{{ $p->scope_golongan ?: 'Semua' }}</td>
                                        <td class="px-3 py-2.5 text-gray-500 max-w-[200px] truncate" title="{{ $p->keterangan }}">{{ $p->keterangan ?: '—' }}</td>
                                        <td class="px-3 py-2.5 text-center">
                                            @if($p->is_active)
                                                <span class="px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700 text-[10px] font-extrabold">AKTIF</span>
                                            @else
                                                <span class="px-2 py-0.5 rounded-full bg-gray-100 text-gray-500 text-[10px] font-extrabold">NONAKTIF</span>
                                            @endif
                                        </td>
                                        <td class="px-3 py-2.5 text-right whitespace-nowrap">
                                            <button type="button"
                                                    class="px-2.5 py-1.5 rounded-lg bg-[#00a2e9]/10 text-[#00a2e9] text-[10px] font-bold hover:bg-[#00a2e9]/20 transition-all"
                                                    onclick="document.getElementById('cp-edit-{{ $p->id }}').classList.toggle('hidden')">
                                                <i class="fa-solid fa-pen"></i> Edit
                                            </button>
                                            <form action="{{ route('superadmin.cuti-policies.destroy', $p->id) }}" method="POST" class="inline"
                                                  onsubmit="return confirm('Hapus kebijakan {{ $p->jenis_cuti }} {{ $p->tahun }}? Kuota karyawan yang sudah dibuat TIDAK berubah otomatis.')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="px-2.5 py-1.5 rounded-lg bg-rose-50 text-rose-600 text-[10px] font-bold hover:bg-rose-100 transition-all">
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    <tr id="cp-edit-{{ $p->id }}" class="hidden">
                                        <td colspan="6" class="px-3 py-3 bg-[#00a2e9]/5">
                                            <form action="{{ route('superadmin.cuti-policies.update', $p->id) }}" method="POST" class="flex flex-wrap items-end gap-3">
                                                @csrf
                                                @method('PUT')
                                                <div class="flex-1 min-w-[140px]">
                                                    <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Jenis Cuti</label>
                                                    <input type="text" name="jenis_cuti" value="{{ old('jenis_cuti', $p->jenis_cuti) }}" required maxlength="50"
                                                           class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:border-[#00a2e9] text-xs">
                                                </div>
                                                <div class="w-24">
                                                    <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Kuota</label>
                                                    <input type="number" name="kuota_hari" value="{{ $p->kuota_hari }}" min="0" max="365" required
                                                           class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:border-[#00a2e9] text-xs">
                                                </div>
                                                <div class="flex-1 min-w-[140px]">
                                                    <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Golongan</label>
                                                    <input type="text" name="scope_golongan" value="{{ $p->scope_golongan }}" maxlength="100"
                                                           placeholder="Semua" list="golongan-list"
                                                           class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:border-[#00a2e9] text-xs">
                                                </div>
                                                <div class="flex-1 min-w-[140px]">
                                                    <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Keterangan</label>
                                                    <input type="text" name="keterangan" value="{{ $p->keterangan }}" maxlength="500"
                                                           class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:border-[#00a2e9] text-xs">
                                                </div>
                                                <label class="flex items-center gap-1.5 text-xs text-gray-600 pb-2 cursor-pointer">
                                                    <input type="hidden" name="is_active" value="0">
                                                    <input type="checkbox" name="is_active" value="1" {{ $p->is_active ? 'checked' : '' }}
                                                           class="w-4 h-4 rounded border-gray-300 text-[#00a2e9] focus:ring-[#00a2e9]">
                                                    Aktif
                                                </label>
                                                <button type="submit" class="px-4 py-2 rounded-lg bg-[#0F1245] text-white text-xs font-bold hover:bg-[#161758] transition-all">
                                                    Simpan
                                                </button>
                                                @error('jenis_cuti')<p class="w-full text-xs text-rose-600">{{ $message }}</p>@enderror
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4 p-4 rounded-2xl bg-[#0F1245]/5 border border-[#0F1245]/10">
                        <p class="text-xs text-gray-600 leading-relaxed">
                            <i class="fa-solid fa-circle-info text-[#00a2e9] mr-1"></i>
                            <b>Cara kerja:</b> kebijakan dipakai saat baris kuota cuti karyawan <i>baru</i> dibuat dan saat mengecek sisa kuota pengajuan.
                            Perubahan kebijakan tidak menghitung ulang kuota karyawan yang sudah ada — hubungi HR bila perlu penyesuaian massal.
                        </p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
