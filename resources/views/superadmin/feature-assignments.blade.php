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
                    <a href="{{ route('superadmin.features.index') }}" class="hover:text-[#00a2e9] transition-colors">Fitur &amp; Maintenance</a>
                    <span>/</span>
                    <span class="text-gray-700">Penugasan Fitur</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-[#0F1245] font-['Poppins'] tracking-tight">
                    Penugasan Fitur per Pengguna
                </h1>
                <p class="text-xs sm:text-sm text-gray-500 font-light mt-0.5 max-w-2xl">
                    Buka atau tutup satu fitur hanya untuk role, divisi, atau karyawan tertentu — di atas sakelar global.
                </p>
            </div>

            <a href="{{ route('superadmin.features.index') }}"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold transition-all self-start">
                <i class="fa-solid fa-sliders"></i> <span>Kembali ke Kontrol Fitur</span>
            </a>
        </div>

        @include('superadmin.partials.flash')

        <!-- Aturan main -->
        <div class="bg-[#0F1245] rounded-3xl p-5 shadow-lg text-white space-y-2.5">
            <h2 class="text-sm font-bold flex items-center gap-2">
                <i class="fa-solid fa-layer-group text-[#FCC626]"></i> Cara Kerja Penugasan
            </h2>
            <ul class="text-xs text-white/75 space-y-2 leading-relaxed">
                <li><b class="text-[#FCC626]">1. Sakelar global MATI</b> mengalahkan semua penugasan — fitur tetap tertutup untuk seluruh non-superadmin.</li>
                <li><b class="text-[#FCC626]">2. Sakelar global AKTIF</b> → penugasan berlaku: <code class="bg-white/10 px-1 rounded">enabled=false</code> memblokir cakupan tersebut.</li>
                <li><b class="text-[#FCC626]">3. Prioritas bila lebih dari satu cocok:</b> karyawan individual &gt; divisi &gt; role.</li>
                <li><b class="text-[#FCC626]">4. Tanpa penugasan yang cocok</b> → pengguna mengikuti sakelar global (terbuka).</li>
            </ul>
        </div>

        <!-- Statistik -->
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
            <div class="bg-white rounded-2xl p-4 border border-gray-100 shadow-sm">
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Total Penugasan</p>
                <p class="text-2xl font-extrabold text-[#0F1245] mt-0.5">{{ $stats['total'] }}</p>
            </div>
            <div class="bg-white rounded-2xl p-4 border border-gray-100 shadow-sm">
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Sedang Diaktifkan</p>
                <p class="text-2xl font-extrabold text-emerald-600 mt-0.5">{{ $stats['aktif'] }}</p>
            </div>
            <div class="bg-white rounded-2xl p-4 border border-gray-100 shadow-sm col-span-2 sm:col-span-1">
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Fitur Terpengaruh</p>
                <p class="text-2xl font-extrabold text-[#00a2e9] mt-0.5">{{ $stats['fitur'] }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-4 sm:gap-5">

            <!-- FORM -->
            <div class="xl:col-span-1 bg-white rounded-3xl p-5 sm:p-6 shadow-sm border border-gray-100 h-fit space-y-4">
                <h2 class="text-base font-bold text-[#0F1245] flex items-center gap-2">
                    <i class="fa-solid fa-plus text-[#00a2e9]"></i> Tambah / Ubah Penugasan
                </h2>
                <p class="text-xs text-gray-500">Kombinasi fitur + cakupan bersifat unik — mengisi ulang akan memperbarui penugasan lama.</p>

                <form action="{{ route('superadmin.features.assignments.store') }}" method="POST" class="space-y-4">
                    @csrf

                    <div>
                        <label for="fa_feature" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Fitur <span class="text-rose-500">*</span></label>
                        <select name="feature_key" id="fa_feature" required
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-[#00a2e9] focus:ring-2 focus:ring-[#00a2e9]/20 text-sm">
                            @foreach($features as $key => $meta)
                                <option value="{{ $key }}" {{ old('feature_key', $featureKey) === $key ? 'selected' : '' }}>
                                    {{ $meta['name'] }} ({{ $counts[$key] ?? 0 }})
                                </option>
                            @endforeach
                        </select>
                        @error('feature_key')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="fa_scope" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Cakupan <span class="text-rose-500">*</span></label>
                        <select name="scope_type" id="fa_scope" required
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-[#00a2e9] focus:ring-2 focus:ring-[#00a2e9]/20 text-sm">
                            <option value="role" {{ old('scope_type') === 'role' ? 'selected' : '' }}>Role (posisi pengguna)</option>
                            <option value="divisi" {{ old('scope_type') === 'divisi' ? 'selected' : '' }}>Divisi</option>
                            <option value="karyawan" {{ old('scope_type') === 'karyawan' ? 'selected' : '' }}>Karyawan Individual</option>
                        </select>
                        @error('scope_type')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div id="wrap-role">
                        <label for="fa_role" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Role</label>
                        <select name="scope_value_role" id="fa_role"
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-[#00a2e9] focus:ring-2 focus:ring-[#00a2e9]/20 text-sm">
                            @foreach($roles as $r)
                                <option value="{{ $r }}" {{ old('scope_value_role') === $r ? 'selected' : '' }}>{{ ucfirst($r) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div id="wrap-divisi" class="hidden">
                        <label for="fa_divisi" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Divisi</label>
                        <input type="text" name="scope_value_divisi" id="fa_divisi" list="fa-divisi-list" maxlength="100"
                               value="{{ old('scope_value_divisi') }}" placeholder="Pilih atau ketik nama divisi"
                               class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-[#00a2e9] focus:ring-2 focus:ring-[#00a2e9]/20 text-sm">
                        <datalist id="fa-divisi-list">
                            @foreach($divisiOptions as $d)
                                <option value="{{ $d }}">
                            @endforeach
                        </datalist>
                    </div>

                    <div id="wrap-karyawan" class="hidden">
                        <label for="fa_karyawan" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Karyawan</label>
                        <select name="scope_value_karyawan" id="fa_karyawan"
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-[#00a2e9] focus:ring-2 focus:ring-[#00a2e9]/20 text-sm">
                            @foreach($karyawanOptions as $k)
                                <option value="{{ $k->id }}" {{ (string) old('scope_value_karyawan') === (string) $k->id ? 'selected' : '' }}>
                                    {{ $k->nama_lengkap }} — {{ $k->divisi ?: '-' }} ({{ $k->posisi }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- input tersembunyi yang benar-benar dikirim -->
                    <input type="hidden" name="scope_value" id="fa_scope_value" value="{{ old('scope_value') }}">
                    @error('scope_value')<p class="text-xs text-rose-600">{{ $message }}</p>@enderror

                    <div>
                        <label class="flex items-center gap-3 text-sm text-gray-700 cursor-pointer">
                            <input type="hidden" name="enabled" value="0">
                            <input type="checkbox" name="enabled" value="1" {{ old('enabled', '1') === '1' ? 'checked' : '' }}
                                   class="w-5 h-5 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                            <span class="font-semibold">Aktifkan fitur untuk cakupan ini</span>
                        </label>
                        <p class="text-[11px] text-gray-400 mt-1">Centang = buka fitur; lepas centang = blokir fitur untuk cakupan ini.</p>
                    </div>

                    <div>
                        <label for="fa_notes" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Catatan</label>
                        <textarea name="notes" id="fa_notes" rows="2" maxlength="500"
                                  placeholder="Alasan / dasar penugasan (opsional)"
                                  class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-[#00a2e9] focus:ring-2 focus:ring-[#00a2e9]/20 text-sm">{{ old('notes') }}</textarea>
                        @error('notes')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
                    </div>

                    <button type="submit" class="w-full px-5 py-2.5 rounded-xl bg-[#0F1245] hover:bg-[#161758] text-white text-xs font-bold transition-all shadow-md flex items-center justify-center gap-2">
                        <i class="fa-solid fa-floppy-disk"></i> Simpan Penugasan
                    </button>
                </form>
            </div>

            <!-- DAFTAR -->
            <div class="xl:col-span-2 bg-white rounded-3xl p-5 sm:p-6 shadow-sm border border-gray-100">
                <h2 class="text-base font-bold text-[#0F1245] flex items-center gap-2 mb-4">
                    <i class="fa-solid fa-list-check text-[#00a2e9]"></i> Daftar Penugasan ({{ $stats['total'] }})
                </h2>

                @if($assignments->isEmpty())
                    <div class="py-12 text-center">
                        <div class="w-14 h-14 mx-auto rounded-2xl bg-gray-100 flex items-center justify-center text-2xl text-gray-400 mb-3">
                            <i class="fa-solid fa-inbox"></i>
                        </div>
                        <p class="text-sm font-semibold text-gray-600">Belum ada penugasan fitur.</p>
                        <p class="text-xs text-gray-400 mt-1">Semua pengguna mengikuti sakelar global. Tambahkan penugasan lewat formulir di samping.</p>
                    </div>
                @else
                    <div class="overflow-x-auto border border-gray-100 rounded-2xl">
                        <table class="w-full text-xs">
                            <thead class="bg-gray-50 text-gray-500 uppercase tracking-wider">
                                <tr>
                                    <th class="px-3 py-2.5 text-left font-bold">Fitur</th>
                                    <th class="px-3 py-2.5 text-left font-bold">Cakupan</th>
                                    <th class="px-3 py-2.5 text-center font-bold">Status</th>
                                    <th class="px-3 py-2.5 text-left font-bold">Catatan</th>
                                    <th class="px-3 py-2.5 text-left font-bold">Oleh</th>
                                    <th class="px-3 py-2.5 text-right font-bold">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @foreach($assignments as $a)
                                    @php
                                        $meta = $features[$a->feature_key] ?? ['name' => $a->feature_key, 'color' => '#00a2e9', 'icon' => 'fa-puzzle-piece'];
                                        $scopeBadge = match ($a->scope_type) {
                                            'role' => 'bg-[#00a2e9]/10 text-[#00a2e9]',
                                            'divisi' => 'bg-amber-100 text-amber-700',
                                            'karyawan' => 'bg-violet-100 text-violet-700',
                                            default => 'bg-gray-100 text-gray-600',
                                        };
                                    @endphp
                                    <tr class="hover:bg-gray-50/60">
                                        <td class="px-3 py-2.5">
                                            <span class="inline-flex items-center gap-1.5 font-medium text-gray-800">
                                                <i class="fa-solid {{ $meta['icon'] }} text-[{{ $meta['color'] }}]"></i>
                                                {{ $meta['name'] }}
                                            </span>
                                        </td>
                                        <td class="px-3 py-2.5">
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold {{ $scopeBadge }}">{{ $a->scopeTypeLabel() }}</span>
                                        </td>
                                        <td class="px-3 py-2.5 text-center">
                                            @if($a->enabled)
                                                <span class="px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700 text-[10px] font-extrabold">DIBUKA</span>
                                            @else
                                                <span class="px-2 py-0.5 rounded-full bg-rose-100 text-rose-700 text-[10px] font-extrabold">DIBLOKIR</span>
                                            @endif
                                        </td>
                                        <td class="px-3 py-2.5 text-gray-500 max-w-[180px] truncate" title="{{ $a->notes }}">{{ $a->notes ?: '—' }}</td>
                                        <td class="px-3 py-2.5 text-gray-400">{{ $a->creator?->nama_lengkap ?? '—' }}</td>
                                        <td class="px-3 py-2.5 text-right">
                                            <form action="{{ route('superadmin.features.assignments.destroy', $a->id) }}" method="POST" class="inline"
                                                  onsubmit="return confirm('Hapus penugasan ini? Pengguna terkait kembali mengikuti sakelar global.')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="px-2.5 py-1.5 rounded-lg bg-rose-50 text-rose-600 text-[10px] font-bold hover:bg-rose-100 transition-all">
                                                    <i class="fa-solid fa-trash"></i> Hapus
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4 p-4 rounded-2xl bg-[#00a2e9]/5 border border-[#00a2e9]/10">
                        <p class="text-xs text-gray-600 leading-relaxed">
                            <i class="fa-solid fa-circle-info text-[#00a2e9] mr-1"></i>
                            Penugasan disimpan pada tabel <code class="bg-white px-1 rounded">feature_assignments</code> dan dibaca oleh middleware
                            <code class="bg-white px-1 rounded">feature:{nama}</code> pada setiap request. Perubahan maksimal terlambat ±1 menit terpakai (cache).
                        </p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const scope = document.getElementById('fa_scope');
    const wraps = {
        role: document.getElementById('wrap-role'),
        divisi: document.getElementById('wrap-divisi'),
        karyawan: document.getElementById('wrap-karyawan'),
    };
    const hidden = document.getElementById('fa_scope_value');
    const fields = {
        role: document.getElementById('fa_role'),
        divisi: document.getElementById('fa_divisi'),
        karyawan: document.getElementById('fa_karyawan'),
    };

    function sync() {
        const type = scope.value;
        Object.keys(wraps).forEach(function (key) {
            wraps[key].classList.toggle('hidden', key !== type);
        });
        // Salin nilai field aktif ke input tersembunyi yang dikirim.
        hidden.value = fields[type] ? fields[type].value : '';
        // Satu name saja agar validasi server menerima satu nilai.
        Object.keys(fields).forEach(function (key) {
            fields[key].name = key === type ? '' : 'scope_value_' + key;
        });
    }

    scope.addEventListener('change', sync);
    Object.keys(fields).forEach(function (key) {
        fields[key].addEventListener('change', sync);
        fields[key].addEventListener('input', sync);
    });
    sync();
});
</script>
@endsection
