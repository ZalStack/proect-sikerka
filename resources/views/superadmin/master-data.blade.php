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
                    <span class="text-gray-700">Data Master</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-[#0F1245] font-['Poppins'] tracking-tight">
                    Data Master Organisasi
                </h1>
                <p class="text-xs sm:text-sm text-gray-500 font-light mt-0.5 max-w-2xl">
                    Kelola daftar divisi, jabatan, dan golongan. Data ini menjadi sumber dropdown pada form karyawan agar entri data konsisten.
                </p>
            </div>
        </div>

        @include('superadmin.partials.flash')

        <!-- Tab tipe -->
        <div class="flex items-center gap-2 flex-wrap">
            @foreach(\App\Models\MasterData::TYPES as $key => $label)
                <a href="{{ route('superadmin.master-data.index', ['type' => $key]) }}"
                   class="px-4 py-2 rounded-xl text-xs font-bold transition-all
                       {{ $type === $key
                          ? 'bg-[#0F1245] text-white shadow-md'
                          : 'bg-white text-gray-600 border border-gray-200 hover:border-[#00a2e9]/40' }}">
                    <i class="fa-solid {{ ['divisi' => 'fa-people-group', 'jabatan' => 'fa-id-badge', 'golongan' => 'fa-layer-group'][$key] }} mr-1.5"></i>
                    {{ $label }}
                    <span class="ml-1.5 px-1.5 py-0.5 rounded-full text-[10px] {{ $type === $key ? 'bg-white/20' : 'bg-gray-100' }}">{{ $counts[$key] ?? 0 }}</span>
                </a>
            @endforeach
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-4 sm:gap-5">

            <!-- FORM TAMBAH -->
            <div class="xl:col-span-1 bg-white rounded-3xl p-5 sm:p-6 shadow-sm border border-gray-100 h-fit">
                <h2 class="text-base font-bold text-[#0F1245] flex items-center gap-2 mb-1">
                    <i class="fa-solid fa-plus text-[#00a2e9]"></i> Tambah {{ \App\Models\MasterData::typeLabel($type) }}
                </h2>
                <p class="text-xs text-gray-500 mb-5">Nama bersifat unik per tipe (tidak membedakan huruf besar/kecil).</p>

                <form action="{{ route('superadmin.master-data.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <input type="hidden" name="type" value="{{ $type }}">

                    <div>
                        <label for="md_name" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Nama <span class="text-rose-500">*</span></label>
                        <input type="text" name="name" id="md_name" value="{{ old('name') }}" required maxlength="100"
                               placeholder="{{ ['divisi' => 'Contoh: Produksi', 'jabatan' => 'Contoh: Supervisor', 'golongan' => 'Contoh: Golongan III'][$type] }}"
                               class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-[#00a2e9] focus:ring-2 focus:ring-[#00a2e9]/20 text-sm">
                        @error('name')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label for="md_code" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Kode</label>
                            <input type="text" name="code" id="md_code" value="{{ old('code') }}" maxlength="30"
                                   placeholder="Opsional"
                                   class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-[#00a2e9] focus:ring-2 focus:ring-[#00a2e9]/20 text-sm">
                            @error('code')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="md_sort" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Urutan</label>
                            <input type="number" name="sort_order" id="md_sort" value="{{ old('sort_order', 0) }}" min="0"
                                   class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-[#00a2e9] focus:ring-2 focus:ring-[#00a2e9]/20 text-sm">
                            @error('sort_order')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div>
                        <label for="md_desc" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Keterangan</label>
                        <textarea name="description" id="md_desc" rows="2" maxlength="500"
                                  placeholder="Deskripsi singkat (opsional)"
                                  class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-[#00a2e9] focus:ring-2 focus:ring-[#00a2e9]/20 text-sm">{{ old('description') }}</textarea>
                        @error('description')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
                    </div>

                    <button type="submit" class="w-full px-5 py-2.5 rounded-xl bg-[#0F1245] hover:bg-[#161758] text-white text-xs font-bold transition-all shadow-md flex items-center justify-center gap-2">
                        <i class="fa-solid fa-floppy-disk"></i> Simpan Data Master
                    </button>
                </form>
            </div>

            <!-- DAFTAR -->
            <div class="xl:col-span-2 bg-white rounded-3xl p-5 sm:p-6 shadow-sm border border-gray-100">
                <h2 class="text-base font-bold text-[#0F1245] flex items-center gap-2 mb-4">
                    <i class="fa-solid fa-list text-[#00a2e9]"></i>
                    Daftar {{ \App\Models\MasterData::typeLabel($type) }}
                </h2>

                @if($items->count() === 0)
                    <div class="py-12 text-center">
                        <div class="w-14 h-14 mx-auto rounded-2xl bg-gray-100 flex items-center justify-center text-2xl text-gray-400 mb-3">
                            <i class="fa-solid fa-inbox"></i>
                        </div>
                        <p class="text-sm font-semibold text-gray-600">Belum ada data {{ \App\Models\MasterData::typeLabel($type) }}.</p>
                        <p class="text-xs text-gray-400 mt-1">Tambahkan lewat formulir di samping.</p>
                    </div>
                @else
                    <div class="overflow-x-auto border border-gray-100 rounded-2xl">
                        <table class="w-full text-xs">
                            <thead class="bg-gray-50 text-gray-500 uppercase tracking-wider">
                                <tr>
                                    <th class="px-3 py-2.5 text-left font-bold">Nama</th>
                                    <th class="px-3 py-2.5 text-left font-bold">Kode</th>
                                    <th class="px-3 py-2.5 text-left font-bold">Keterangan</th>
                                    <th class="px-3 py-2.5 text-center font-bold">Urutan</th>
                                    <th class="px-3 py-2.5 text-center font-bold">Status</th>
                                    <th class="px-3 py-2.5 text-right font-bold">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @foreach($items as $item)
                                    <tr class="hover:bg-gray-50/60">
                                        <td class="px-3 py-2.5 font-medium text-gray-800">{{ $item->name }}</td>
                                        <td class="px-3 py-2.5 text-gray-500">{{ $item->code ?: '—' }}</td>
                                        <td class="px-3 py-2.5 text-gray-500 max-w-[220px] truncate" title="{{ $item->description }}">{{ $item->description ?: '—' }}</td>
                                        <td class="px-3 py-2.5 text-center text-gray-500">{{ $item->sort_order }}</td>
                                        <td class="px-3 py-2.5 text-center">
                                            @if($item->is_active)
                                                <span class="px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700 text-[10px] font-extrabold">AKTIF</span>
                                            @else
                                                <span class="px-2 py-0.5 rounded-full bg-gray-100 text-gray-500 text-[10px] font-extrabold">NONAKTIF</span>
                                            @endif
                                        </td>
                                        <td class="px-3 py-2.5 text-right whitespace-nowrap">
                                            <button type="button"
                                                    class="px-2.5 py-1.5 rounded-lg bg-[#00a2e9]/10 text-[#00a2e9] text-[10px] font-bold hover:bg-[#00a2e9]/20 transition-all"
                                                    onclick="document.getElementById('edit-{{ $item->id }}').classList.toggle('hidden')">
                                                <i class="fa-solid fa-pen"></i> Edit
                                            </button>
                                            <form action="{{ route('superadmin.master-data.destroy', $item->id) }}" method="POST" class="inline"
                                                  onsubmit="return confirm('Hapus data master \"{{ $item->name }}\"? Data karyawan yang sudah terlanjur memakai nama ini TIDAK ikut berubah.')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="px-2.5 py-1.5 rounded-lg bg-rose-50 text-rose-600 text-[10px] font-bold hover:bg-rose-100 transition-all">
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    <!-- Form edit inline -->
                                    <tr id="edit-{{ $item->id }}" class="hidden">
                                        <td colspan="6" class="px-3 py-3 bg-[#00a2e9]/5">
                                            <form action="{{ route('superadmin.master-data.update', $item->id) }}" method="POST" class="flex flex-wrap items-end gap-3">
                                                @csrf
                                                @method('PUT')
                                                <div class="flex-1 min-w-[160px]">
                                                    <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Nama</label>
                                                    <input type="text" name="name" value="{{ old('name', $item->name) }}" required maxlength="100"
                                                           class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:border-[#00a2e9] text-xs">
                                                </div>
                                                <div class="w-28">
                                                    <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Kode</label>
                                                    <input type="text" name="code" value="{{ old('code', $item->code) }}" maxlength="30"
                                                           class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:border-[#00a2e9] text-xs">
                                                </div>
                                                <div class="w-24">
                                                    <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Urutan</label>
                                                    <input type="number" name="sort_order" value="{{ $item->sort_order }}" min="0"
                                                           class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:border-[#00a2e9] text-xs">
                                                </div>
                                                <div class="flex-1 min-w-[160px]">
                                                    <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Keterangan</label>
                                                    <input type="text" name="description" value="{{ old('description', $item->description) }}" maxlength="500"
                                                           class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:border-[#00a2e9] text-xs">
                                                </div>
                                                <label class="flex items-center gap-1.5 text-xs text-gray-600 pb-2 cursor-pointer">
                                                    <input type="hidden" name="is_active" value="0">
                                                    <input type="checkbox" name="is_active" value="1" {{ $item->is_active ? 'checked' : '' }}
                                                           class="w-4 h-4 rounded border-gray-300 text-[#00a2e9] focus:ring-[#00a2e9]">
                                                    Aktif
                                                </label>
                                                <button type="submit" class="px-4 py-2 rounded-lg bg-[#0F1245] text-white text-xs font-bold hover:bg-[#161758] transition-all">
                                                    Simpan
                                                </button>
                                                @error('name')<p class="w-full text-xs text-rose-600">{{ $message }}</p>@enderror
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $items->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
