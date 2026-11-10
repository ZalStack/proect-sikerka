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
                    <span class="text-gray-700">Broadcast</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-[#0F1245] font-['Poppins']">Broadcast Pengumuman</h1>
                <p class="text-xs sm:text-sm text-gray-500 font-light mt-0.5 max-w-2xl">
                    Kirim informasi ke seluruh {{ $totalAudience }} karyawan aktif atau ke karyawan tertentu. Pesan langsung muncul di daftar notifikasi mereka.
                </p>
            </div>

            <button type="button" onclick="document.getElementById('modal-broadcast').classList.remove('hidden')"
                    class="px-5 py-3 rounded-2xl bg-[#ea580c] hover:bg-[#c2410c] text-white text-sm font-bold transition-all shadow-lg shadow-[#ea580c]/25 flex items-center justify-center gap-2">
                <i class="fa-solid fa-bullhorn"></i> Kirim Broadcast
            </button>
        </div>

        @include('superadmin.partials.flash')

        <!-- DAFTAR BROADCAST -->
        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-5 sm:px-6 py-4 border-b border-gray-100 flex items-center justify-between gap-3">
                <div>
                    <h2 class="text-base font-bold text-[#0F1245] flex items-center gap-2">
                        <i class="fa-solid fa-list text-[#ea580c]"></i> Riwayat Broadcast
                    </h2>
                    <p class="text-xs text-gray-500 mt-0.5">50 pengumuman terbaru.</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50/70">
                        <tr class="text-left text-[11px] uppercase tracking-wider text-gray-400">
                            <th class="py-3 px-5 font-bold">Judul</th>
                            <th class="py-3 px-3 font-bold">Target</th>
                            <th class="py-3 px-3 font-bold">Pengirim</th>
                            <th class="py-3 px-3 font-bold">Waktu</th>
                            <th class="py-3 px-5 font-bold text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($broadcasts as $b)
                            <tr class="hover:bg-gray-50/60">
                                <td class="py-3 px-5">
                                    <div class="font-semibold text-gray-800">{{ $b->judul }}</div>
                                    <div class="text-[11px] text-gray-400 max-w-md truncate" title="{{ $b->isi }}">{{ \Illuminate\Support\Str::limit($b->isi, 90) }}</div>
                                </td>
                                <td class="py-3 px-3">
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase {{ $b->target === 'semua' ? 'bg-[#ea580c]/10 text-[#ea580c]' : 'bg-purple-100 text-purple-700' }}">
                                        {{ $b->target_label }}
                                    </span>
                                </td>
                                <td class="py-3 px-3 text-xs text-gray-600">{{ $b->creator?->nama_lengkap ?? '-' }}</td>
                                <td class="py-3 px-3 text-xs text-gray-500 whitespace-nowrap">{{ $b->created_at?->format('d M Y H:i') }}</td>
                                <td class="py-3 px-5 text-right">
                                    <form action="{{ route('superadmin.broadcast.destroy', $b->id) }}" method="POST" class="inline"
                                          onsubmit="return confirm('Hapus broadcast "{{ $b->judul }}"?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-2.5 py-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-600 text-[11px] font-bold border border-rose-100">
                                            <i class="fa-solid fa-trash mr-1"></i>Hapus
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-8 text-center text-sm text-gray-400">Belum ada broadcast dikirim.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- MODAL KIRIM BROADCAST -->
<div id="modal-broadcast" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-2xl p-6 sm:p-8 space-y-4 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between">
            <h3 class="text-lg font-extrabold text-[#0F1245]">Kirim Broadcast</h3>
            <button type="button" onclick="this.closest('.fixed').classList.add('hidden')" class="text-gray-400 hover:text-gray-700 text-xl">&times;</button>
        </div>

        <form action="{{ route('superadmin.broadcast.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf

            @if(count($templates) > 0)
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Gunakan Template</label>
                    <select id="template-picker" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-[#ea580c] focus:ring-2 focus:ring-[#ea580c]/20 text-sm">
                        <option value="">— Pilih template (opsional) —</option>
                        @foreach($templates as $t)
                            <option value="{{ $t->id }}" data-title="{{ $t->title }}" data-body="{{ $t->body }}">{{ $t->title }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Judul</label>
                <input type="text" name="judul" value="{{ old('judul') }}" required maxlength="200"
                       class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-[#ea580c] focus:ring-2 focus:ring-[#ea580c]/20 text-sm">
                @error('judul')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Isi Pesan</label>
                <textarea name="isi" rows="6" required
                          class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-[#ea580c] focus:ring-2 focus:ring-[#ea580c]/20 text-sm leading-relaxed">{{ old('isi') }}</textarea>
                @error('isi')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Target</label>
                    <select name="target" id="broadcast-target" required
                            class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-[#ea580c] focus:ring-2 focus:ring-[#ea580c]/20 text-sm">
                        <option value="semua" {{ old('target') === 'semua' ? 'selected' : '' }}>Semua Karyawan ({{ $totalAudience }})</option>
                        <option value="spesifik" {{ old('target') === 'spesifik' ? 'selected' : '' }}>Karyawan Spesifik</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Lampiran Gambar (opsional)</label>
                    <input type="file" name="gambar" accept="image/jpeg,image/png,image/gif"
                           class="w-full px-3 py-2 rounded-xl border border-gray-200 text-xs file:mr-3 file:px-3 file:py-1.5 file:rounded-lg file:border-0 file:bg-[#ea580c] file:text-white file:text-xs file:font-bold">
                    @error('gambar')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            <div id="target-list" class="{{ old('target') === 'spesifik' ? '' : 'hidden' }}">
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Pilih Penerima</label>
                <select name="target_karyawan[]" multiple size="8"
                        class="w-full px-3 py-2 rounded-xl border border-gray-200 text-sm focus:border-[#ea580c] focus:ring-2 focus:ring-[#ea580c]/20">
                    @foreach($recipients as $r)
                        <option value="{{ $r->id }}" {{ in_array($r->id, (array) old('target_karyawan', [])) ? 'selected' : '' }}>
                            {{ $r->nama_lengkap }} — {{ $r->divisi ?: '-' }} ({{ $r->posisi }})
                        </option>
                    @endforeach
                </select>
                @error('target_karyawan')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="this.closest('.fixed').classList.add('hidden')" class="px-5 py-2.5 rounded-xl border border-gray-200 text-gray-600 text-xs font-bold">Batal</button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-[#ea580c] hover:bg-[#c2410c] text-white text-xs font-bold shadow-md">Kirim Broadcast</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const target = document.getElementById('broadcast-target');
    const list = document.getElementById('target-list');
    if (target && list) {
        target.addEventListener('change', function () {
            list.classList.toggle('hidden', this.value !== 'spesifik');
        });
    }

    const picker = document.getElementById('template-picker');
    if (picker) {
        picker.addEventListener('change', function () {
            const opt = this.selectedOptions[0];
            if (!opt || !opt.dataset.title) return;
            const judul = document.querySelector('input[name="judul"]');
            const isi = document.querySelector('textarea[name="isi"]');
            if (judul) judul.value = opt.dataset.title;
            if (isi) isi.value = opt.dataset.body || '';
        });
    }
});
</script>
@endsection
