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
                    <span class="text-gray-700">Template Pesan</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-[#0F1245] font-['Poppins'] tracking-tight">
                    Template Pesan Siap Pakai
                </h1>
                <p class="text-xs sm:text-sm text-gray-500 font-light mt-0.5 max-w-2xl">
                    Kelola pesan berulang untuk blokir presensi, pelanggaran, dan broadcast. Template langsung muncul di halaman terkait.
                </p>
            </div>
        </div>

        @include('superadmin.partials.flash')

        <!-- Tab tipe -->
        <div class="flex items-center gap-2 flex-wrap">
            @foreach(\App\Models\MessageTemplate::TYPES as $key => $label)
                <a href="{{ route('superadmin.message-templates.index', ['type' => $key]) }}"
                   class="px-4 py-2 rounded-xl text-xs font-bold transition-all
                       {{ $type === $key
                          ? 'bg-[#0F1245] text-white shadow-md'
                          : 'bg-white text-gray-600 border border-gray-200 hover:border-[#00a2e9]/40' }}">
                    <i class="fa-solid {{ ['ban' => 'fa-ban', 'violation' => 'fa-gavel', 'broadcast' => 'fa-bullhorn'][$key] }} mr-1.5"></i>
                    {{ $label }}
                    <span class="ml-1.5 px-1.5 py-0.5 rounded-full text-[10px] {{ $type === $key ? 'bg-white/20' : 'bg-gray-100' }}">{{ $counts[$key] ?? 0 }}</span>
                </a>
            @endforeach
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-4 sm:gap-5">

            <!-- FORM -->
            <div class="xl:col-span-1 bg-white rounded-3xl p-5 sm:p-6 shadow-sm border border-gray-100 h-fit space-y-4">
                <h2 class="text-base font-bold text-[#0F1245] flex items-center gap-2">
                    <i class="fa-solid fa-plus text-[#00a2e9]"></i> Tambah Template
                </h2>
                <p class="text-xs text-gray-500">
                    Tipe mengikuti tab yang sedang aktif (<b>{{ \App\Models\MessageTemplate::TYPES[$type] }}</b>).
                    Judul unik per tipe. Placeholder <code class="bg-gray-100 px-1 rounded">{nama}</code> bisa dipakai di isi pesan.
                </p>

                <form action="{{ route('superadmin.message-templates.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <input type="hidden" name="type" value="{{ $type }}">

                    <div>
                        <label for="mt_title" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Judul <span class="text-rose-500">*</span></label>
                        <input type="text" name="title" id="mt_title" value="{{ old('title') }}" required maxlength="150"
                               placeholder="Contoh: Peringatan Kedua"
                               class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-[#00a2e9] focus:ring-2 focus:ring-[#00a2e9]/20 text-sm">
                        @error('title')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="mt_body" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Isi Pesan <span class="text-rose-500">*</span></label>
                        <textarea name="body" id="mt_body" rows="6" required maxlength="5000"
                                  placeholder="Tulis isi pesan yang akan terisi otomatis saat template dipilih..."
                                  class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-[#00a2e9] focus:ring-2 focus:ring-[#00a2e9]/20 text-sm leading-relaxed">{{ old('body') }}</textarea>
                        @error('body')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="flex flex-wrap gap-5">
                        <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer">
                            <input type="hidden" name="is_default" value="0">
                            <input type="checkbox" name="is_default" value="1" {{ old('is_default') ? 'checked' : '' }}
                                   class="w-4 h-4 rounded border-gray-300 text-[#00a2e9] focus:ring-[#00a2e9]">
                            Jadikan default
                        </label>
                        <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', '1') === '1' ? 'checked' : '' }}
                                   class="w-4 h-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                            Aktif
                        </label>
                    </div>

                    <button type="submit" class="w-full px-5 py-2.5 rounded-xl bg-[#0F1245] hover:bg-[#161758] text-white text-xs font-bold transition-all shadow-md flex items-center justify-center gap-2">
                        <i class="fa-solid fa-floppy-disk"></i> Simpan Template
                    </button>
                </form>

                <div class="border-t border-gray-100 pt-4 p-4 rounded-2xl bg-[#00a2e9]/5 border-[#00a2e9]/10">
                    <p class="text-xs text-gray-600 leading-relaxed">
                        <i class="fa-solid fa-circle-info text-[#00a2e9] mr-1"></i>
                        @if($type === 'broadcast')
                            Template tipe <b>Broadcast</b> dipilih pada form Kirim Broadcast — judul &amp; isi otomatis terisi.
                        @elseif($type === 'violation')
                            Template tipe <b>Pelanggaran</b> muncul di dropdown "Template" pada form Konfirmasi Pelanggaran.
                        @else
                            Template tipe <b>Pesan Ban</b> muncul di dropdown "Template" pada form Pesan Alert blokir presensi.
                        @endif
                    </p>
                </div>
            </div>

            <!-- DAFTAR -->
            <div class="xl:col-span-2 bg-white rounded-3xl p-5 sm:p-6 shadow-sm border border-gray-100">
                <h2 class="text-base font-bold text-[#0F1245] flex items-center gap-2 mb-4">
                    <i class="fa-solid fa-note-sticky text-[#00a2e9]"></i>
                    Daftar {{ \App\Models\MessageTemplate::TYPES[$type] }}
                </h2>

                @if($templates->count() === 0)
                    <div class="py-12 text-center">
                        <div class="w-14 h-14 mx-auto rounded-2xl bg-gray-100 flex items-center justify-center text-2xl text-gray-400 mb-3">
                            <i class="fa-solid fa-inbox"></i>
                        </div>
                        <p class="text-sm font-semibold text-gray-600">Belum ada template {{ \App\Models\MessageTemplate::TYPES[$type] }}.</p>
                        <p class="text-xs text-gray-400 mt-1">Tambahkan lewat formulir di samping.</p>
                    </div>
                @else
                    <div class="space-y-4">
                        @foreach($templates as $t)
                            <div class="border border-gray-100 rounded-2xl p-4 hover:shadow-sm transition-shadow">
                                <div class="flex flex-wrap items-start justify-between gap-3">
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <h3 class="text-sm font-bold text-gray-800">{{ $t->title }}</h3>
                                            @if($t->is_default)
                                                <span class="px-2 py-0.5 rounded-full bg-[#00a2e9]/10 text-[#00a2e9] text-[10px] font-extrabold">DEFAULT</span>
                                            @endif
                                            @if($t->is_active)
                                                <span class="px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700 text-[10px] font-extrabold">AKTIF</span>
                                            @else
                                                <span class="px-2 py-0.5 rounded-full bg-gray-100 text-gray-500 text-[10px] font-extrabold">NONAKTIF</span>
                                            @endif
                                        </div>
                                        <p class="text-xs text-gray-600 mt-1.5 whitespace-pre-wrap leading-relaxed bg-gray-50 rounded-xl p-3 border border-gray-50">{{ $t->body }}</p>
                                        <p class="text-[10px] text-gray-400 mt-1.5">Dibuat {{ $t->created_at?->format('d/m/Y H:i') }} · Diperbarui {{ $t->updated_at?->format('d/m/Y H:i') }}</p>
                                    </div>

                                    <div class="flex items-center gap-2 flex-shrink-0">
                                        <button type="button"
                                                class="px-2.5 py-1.5 rounded-lg bg-[#00a2e9]/10 text-[#00a2e9] text-[10px] font-bold hover:bg-[#00a2e9]/20 transition-all"
                                                onclick="document.getElementById('mt-edit-{{ $t->id }}').classList.toggle('hidden')">
                                            <i class="fa-solid fa-pen"></i> Edit
                                        </button>
                                        <form action="{{ route('superadmin.message-templates.destroy', $t->id) }}" method="POST" class="inline"
                                              onsubmit="return confirm('Hapus template \\"{{ $t->title }}\\"?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="px-2.5 py-1.5 rounded-lg bg-rose-50 text-rose-600 text-[10px] font-bold hover:bg-rose-100 transition-all">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>

                                <!-- Form edit inline -->
                                <div id="mt-edit-{{ $t->id }}" class="hidden mt-3 pt-3 border-t border-gray-100">
                                    <form action="{{ route('superadmin.message-templates.update', $t->id) }}" method="POST" class="space-y-3">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="type" value="{{ $t->type }}">
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                            <div>
                                                <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Judul</label>
                                                <input type="text" name="title" value="{{ old('title', $t->title) }}" required maxlength="150"
                                                       class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:border-[#00a2e9] text-xs">
                                            </div>
                                            <div class="flex items-end gap-4 pb-1">
                                                <label class="flex items-center gap-1.5 text-xs text-gray-600 cursor-pointer">
                                                    <input type="hidden" name="is_default" value="0">
                                                    <input type="checkbox" name="is_default" value="1" {{ $t->is_default ? 'checked' : '' }}
                                                           class="w-4 h-4 rounded border-gray-300 text-[#00a2e9] focus:ring-[#00a2e9]">
                                                    Default
                                                </label>
                                                <label class="flex items-center gap-1.5 text-xs text-gray-600 cursor-pointer">
                                                    <input type="hidden" name="is_active" value="0">
                                                    <input type="checkbox" name="is_active" value="1" {{ $t->is_active ? 'checked' : '' }}
                                                           class="w-4 h-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                                                    Aktif
                                                </label>
                                            </div>
                                        </div>
                                        <div>
                                            <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Isi Pesan</label>
                                            <textarea name="body" rows="4" required maxlength="5000"
                                                      class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:border-[#00a2e9] text-xs">{{ old('body', $t->body) }}</textarea>
                                        </div>
                                        <div class="flex justify-end gap-2">
                                            <button type="button"
                                                    class="px-4 py-2 rounded-lg border border-gray-200 text-gray-600 text-xs font-bold hover:bg-gray-50"
                                                    onclick="document.getElementById('mt-edit-{{ $t->id }}').classList.add('hidden')">Batal</button>
                                            <button type="submit" class="px-4 py-2 rounded-lg bg-[#0F1245] text-white text-xs font-bold hover:bg-[#161758] transition-all">Simpan</button>
                                        </div>
                                        @error('title')<p class="text-xs text-rose-600">{{ $message }}</p>@enderror
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-4">
                        {{ $templates->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
