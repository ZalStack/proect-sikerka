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
                    <span class="text-gray-700">Manajemen Cuti</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-[#0F1245] font-['Poppins'] tracking-tight">
                    Manajemen Cuti
                </h1>
                <p class="text-xs sm:text-sm text-gray-500 font-light mt-0.5 max-w-2xl">
                    Kelola master jenis cuti yang tersedia untuk pengajuan karyawan serta verifikasi &amp; proses seluruh pengajuan cuti secara terpusat.
                </p>
            </div>

            <!-- Tab Switcher -->
            <div class="flex items-center bg-gray-100 p-1 rounded-2xl border border-gray-200">
                <a href="{{ route('superadmin.manajemen-cuti.index', ['tab' => 'pengaturan']) }}"
                   class="px-4 py-2 rounded-xl text-xs sm:text-sm font-bold flex items-center gap-2 transition-all {{ $tab === 'pengaturan' ? 'bg-white text-[#0F1245] shadow-sm' : 'text-gray-500 hover:text-gray-900' }}">
                    <i class="fa-solid fa-sliders text-[#00a2e9]"></i>
                    <span>Pengaturan Jenis Cuti</span>
                </a>
                <a href="{{ route('superadmin.manajemen-cuti.index', ['tab' => 'pengajuan']) }}"
                   class="px-4 py-2 rounded-xl text-xs sm:text-sm font-bold flex items-center gap-2 transition-all {{ $tab === 'pengajuan' ? 'bg-white text-[#0F1245] shadow-sm' : 'text-gray-500 hover:text-gray-900' }}">
                    <i class="fa-solid fa-list-check text-[#2E7D3E]"></i>
                    <span>Daftar Pengajuan</span>
                    @if($statistik['pending'] > 0)
                        <span class="ml-1 px-2 py-0.5 text-[10px] font-extrabold bg-amber-500 text-white rounded-full">
                            {{ $statistik['pending'] }}
                        </span>
                    @endif
                </a>
            </div>
        </div>

        @include('superadmin.partials.flash')

        <!-- TAB 1: PENGATURAN JENIS CUTI -->
        @if($tab === 'pengaturan')
            <!-- Statistik Jenis Cuti -->
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                <div class="bg-white rounded-2xl p-4 border border-gray-100 shadow-sm">
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Total Jenis Cuti</p>
                    <p class="text-2xl font-extrabold text-[#0F1245] mt-0.5">{{ $typeStats['total'] }}</p>
                </div>
                <div class="bg-white rounded-2xl p-4 border border-gray-100 shadow-sm">
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Jenis Aktif</p>
                    <p class="text-2xl font-extrabold text-emerald-600 mt-0.5">{{ $typeStats['active'] }}</p>
                </div>
                <div class="bg-white rounded-2xl p-4 border border-gray-100 shadow-sm col-span-2 sm:col-span-1">
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Nonaktif</p>
                    <p class="text-2xl font-extrabold text-gray-400 mt-0.5">{{ $typeStats['inactive'] }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 xl:grid-cols-3 gap-4 sm:gap-5">
                <!-- FORM TAMBAH JENIS CUTI -->
                <div class="xl:col-span-1 bg-white rounded-3xl p-5 sm:p-6 shadow-sm border border-gray-100 h-fit">
                    <h2 class="text-base font-bold text-[#0F1245] flex items-center gap-2 mb-1">
                        <i class="fa-solid fa-plus text-[#00a2e9]"></i> Tambah Jenis Cuti
                    </h2>
                    <p class="text-xs text-gray-500 mb-5">
                        Jenis cuti yang aktif akan langsung muncul pada pilihan form pengajuan cuti role Karyawan.
                    </p>

                    <form action="{{ route('superadmin.manajemen-cuti.type.store') }}" method="POST" class="space-y-4">
                        @csrf

                        <div>
                            <label for="nama" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                                Nama Jenis Cuti <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="nama" id="nama" value="{{ old('nama') }}" required maxlength="100"
                                   placeholder="Contoh: Cuti Menikah"
                                   class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-[#00a2e9] focus:ring-2 focus:ring-[#00a2e9]/20 text-sm">
                            @error('nama')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label for="kuota_hari" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                                    Kuota (Hari) <span class="text-rose-500">*</span>
                                </label>
                                <input type="number" name="kuota_hari" id="kuota_hari" value="{{ old('kuota_hari', 12) }}" min="0" max="365" required
                                       class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-[#00a2e9] focus:ring-2 focus:ring-[#00a2e9]/20 text-sm">
                                @error('kuota_hari')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="sort_order" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                                    Urutan
                                </label>
                                <input type="number" name="sort_order" id="sort_order" value="{{ old('sort_order', 0) }}" min="0"
                                       class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-[#00a2e9] focus:ring-2 focus:ring-[#00a2e9]/20 text-sm">
                                @error('sort_order')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
                            </div>
                        </div>

                        <div>
                            <label for="scope_golongan" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                                Golongan Khusus (Opsional)
                            </label>
                            <input type="text" name="scope_golongan" id="scope_golongan" value="{{ old('scope_golongan') }}" maxlength="100"
                                   placeholder="Kosongkan untuk semua golongan"
                                   class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-[#00a2e9] focus:ring-2 focus:ring-[#00a2e9]/20 text-sm">
                            <p class="text-[11px] text-gray-400 mt-1">Pisahkan dengan koma jika lebih dari satu (contoh: Golongan I, Golongan II).</p>
                        </div>

                        <div>
                            <label for="keterangan" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                                Keterangan
                            </label>
                            <textarea name="keterangan" id="keterangan" rows="2" maxlength="500"
                                      placeholder="Penjelasan hak atau syarat pengajuan jenis cuti ini..."
                                      class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-[#00a2e9] focus:ring-2 focus:ring-[#00a2e9]/20 text-sm">{{ old('keterangan') }}</textarea>
                        </div>

                        <button type="submit"
                                class="w-full py-2.5 px-4 rounded-xl bg-[#00a2e9] hover:bg-[#008ac7] text-white font-bold text-sm shadow-sm transition-colors flex items-center justify-center gap-2">
                            <i class="fa-solid fa-save"></i> Simpan Jenis Cuti
                        </button>
                    </form>
                </div>

                <!-- DAFTAR JENIS CUTI -->
                <div class="xl:col-span-2 bg-white rounded-3xl p-5 sm:p-6 shadow-sm border border-gray-100">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h2 class="text-base font-bold text-[#0F1245] flex items-center gap-2">
                                <i class="fa-solid fa-layer-group text-[#00a2e9]"></i> Master Jenis Cuti
                            </h2>
                            <p class="text-xs text-gray-500 mt-0.5">Daftar seluruh jenis cuti yang diatur oleh Super Admin.</p>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-gray-50 text-gray-400 font-bold uppercase tracking-wider border-b border-gray-100">
                                <tr>
                                    <th class="py-3 px-3">No</th>
                                    <th class="py-3 px-3">Nama Jenis</th>
                                    <th class="py-3 px-3">Kuota</th>
                                    <th class="py-3 px-3">Golongan</th>
                                    <th class="py-3 px-3">Urutan</th>
                                    <th class="py-3 px-3">Status</th>
                                    <th class="py-3 px-3 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse($cutiTypes as $item)
                                    <tr class="hover:bg-gray-50/70 transition-colors">
                                        <td class="py-3 px-3 font-semibold text-gray-400">{{ $loop->iteration }}</td>
                                        <td class="py-3 px-3">
                                            <p class="font-bold text-[#0F1245] text-sm">{{ $item->nama }}</p>
                                            @if($item->keterangan)
                                                <p class="text-[11px] text-gray-400 line-clamp-1">{{ $item->keterangan }}</p>
                                            @endif
                                        </td>
                                        <td class="py-3 px-3">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-xs font-bold bg-blue-50 text-[#00a2e9]">
                                                {{ $item->kuota_hari }} hari
                                            </span>
                                        </td>
                                        <td class="py-3 px-3 text-gray-600">
                                            {{ $item->scope_golongan ?: 'Semua' }}
                                        </td>
                                        <td class="py-3 px-3 font-mono text-gray-500">{{ $item->sort_order }}</td>
                                        <td class="py-3 px-3">
                                            @if($item->is_active)
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Aktif
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-gray-100 text-gray-500">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span> Nonaktif
                                                </span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-3 text-right">
                                            <div class="inline-flex items-center gap-1">
                                                <button type="button"
                                                        onclick="openEditModal({{ json_encode($item) }})"
                                                        class="p-1.5 rounded-lg text-gray-400 hover:text-[#00a2e9] hover:bg-blue-50 transition-colors"
                                                        title="Edit Jenis Cuti">
                                                    <i class="fa-solid fa-pen-to-square"></i>
                                                </button>

                                                <form action="{{ route('superadmin.manajemen-cuti.type.destroy', $item->id) }}" method="POST" class="inline"
                                                      onsubmit="return confirm('Yakin ingin menghapus jenis cuti \'{{ $item->nama }}\'? Jika sudah pernah dipakai pengajuan, penghapusan akan ditolak demi keamanan data.')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                            class="p-1.5 rounded-lg text-gray-400 hover:text-rose-600 hover:bg-rose-50 transition-colors"
                                                            title="Hapus Jenis Cuti">
                                                        <i class="fa-solid fa-trash-can"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="py-8 text-center text-gray-400">
                                            Belum ada data jenis cuti.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- MODAL EDIT JENIS CUTI -->
            <div id="editModal" class="fixed inset-0 z-50 hidden bg-black/40 backdrop-blur-sm flex items-center justify-center p-4">
                <div class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl border border-gray-100 animate-in fade-in zoom-in-95 duration-150">
                    <div class="flex items-center justify-between pb-3 border-b border-gray-100 mb-4">
                        <h3 class="text-base font-bold text-[#0F1245] flex items-center gap-2">
                            <i class="fa-solid fa-pen-to-square text-[#00a2e9]"></i> Edit Jenis Cuti
                        </h3>
                        <button type="button" onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600">
                            <i class="fa-solid fa-xmark text-lg"></i>
                        </button>
                    </div>

                    <form id="editForm" method="POST" class="space-y-4">
                        @csrf
                        @method('PUT')

                        <div>
                            <label for="edit_nama" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                                Nama Jenis Cuti <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="nama" id="edit_nama" required maxlength="100"
                                   class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-[#00a2e9] focus:ring-2 focus:ring-[#00a2e9]/20 text-sm">
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label for="edit_kuota" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                                    Kuota (Hari) <span class="text-rose-500">*</span>
                                </label>
                                <input type="number" name="kuota_hari" id="edit_kuota" min="0" max="365" required
                                       class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-[#00a2e9] focus:ring-2 focus:ring-[#00a2e9]/20 text-sm">
                            </div>
                            <div>
                                <label for="edit_sort" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                                    Urutan
                                </label>
                                <input type="number" name="sort_order" id="edit_sort" min="0"
                                       class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-[#00a2e9] focus:ring-2 focus:ring-[#00a2e9]/20 text-sm">
                            </div>
                        </div>

                        <div>
                            <label for="edit_scope" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                                Cakupan Golongan (Opsional)
                            </label>
                            <input type="text" name="scope_golongan" id="edit_scope" maxlength="100"
                                   placeholder="Kosongkan untuk semua golongan"
                                   class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-[#00a2e9] focus:ring-2 focus:ring-[#00a2e9]/20 text-sm">
                        </div>

                        <div>
                            <label for="edit_keterangan" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                                Keterangan
                            </label>
                            <textarea name="keterangan" id="edit_keterangan" rows="2" maxlength="500"
                                      class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-[#00a2e9] focus:ring-2 focus:ring-[#00a2e9]/20 text-sm"></textarea>
                        </div>

                        <div class="pt-2">
                            <label class="flex items-center gap-3 cursor-pointer select-none">
                                <input type="checkbox" name="is_active" id="edit_active" value="1" class="rounded border-gray-300 text-[#00a2e9] focus:ring-[#00a2e9]">
                                <span class="text-xs font-bold text-gray-700">Aktifkan jenis cuti ini untuk karyawan</span>
                            </label>
                        </div>

                        <div class="flex items-center justify-end gap-2 pt-3 border-t border-gray-100">
                            <button type="button" onclick="closeEditModal()"
                                    class="px-4 py-2 rounded-xl text-xs font-bold text-gray-500 hover:bg-gray-100 transition-colors">
                                Batal
                            </button>
                            <button type="submit"
                                    class="px-5 py-2 rounded-xl bg-[#00a2e9] hover:bg-[#008ac7] text-white text-xs font-bold shadow-sm transition-colors">
                                Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        <!-- TAB 2: DAFTAR PENGAJUAN CUTI KARYAWAN -->
        @elseif($tab === 'pengajuan')

            <!-- Statistik Pengajuan -->
            <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-3 mb-6">
                <div class="bg-gradient-to-r from-[#00a2e9]/5 to-white rounded-2xl shadow-sm border border-slate-100 p-4 text-center border-l-4 border-l-[#00a2e9]">
                    <p class="text-2xl font-bold text-[#161758]">{{ $statistik['total'] }}</p>
                    <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mt-1">Total Pengajuan</p>
                </div>
                <div class="bg-gradient-to-r from-[#FCC626]/5 to-white rounded-2xl shadow-sm border border-slate-100 p-4 text-center border-l-4 border-l-[#FCC626]">
                    <p class="text-2xl font-bold text-[#161758]">{{ $statistik['pending'] }}</p>
                    <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mt-1">Menunggu</p>
                </div>
                <div class="bg-gradient-to-r from-[#2E7D3E]/5 to-white rounded-2xl shadow-sm border border-slate-100 p-4 text-center border-l-4 border-l-[#2E7D3E]">
                    <p class="text-2xl font-bold text-[#161758]">{{ $statistik['approved'] }}</p>
                    <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mt-1">Disetujui</p>
                </div>
                <div class="bg-gradient-to-r from-[#ec1d1d]/5 to-white rounded-2xl shadow-sm border border-slate-100 p-4 text-center border-l-4 border-l-[#ec1d1d]">
                    <p class="text-2xl font-bold text-[#161758]">{{ $statistik['rejected'] }}</p>
                    <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mt-1">Ditolak</p>
                </div>
            </div>

            <!-- Filter Pengajuan -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-4 sm:p-5 mb-6">
                <form action="{{ route('superadmin.manajemen-cuti.index') }}" method="GET" class="flex flex-wrap gap-3 sm:gap-4 items-center">
                    <input type="hidden" name="tab" value="pengajuan">

                    <div class="flex-1 min-w-[140px]">
                        <select name="status" class="w-full px-3 py-2 text-xs sm:text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9] bg-white">
                            <option value="">Semua Status</option>
                            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Menunggu</option>
                            <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Disetujui</option>
                            <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Ditolak</option>
                        </select>
                    </div>

                    <div class="flex-1 min-w-[140px]">
                        <select name="karyawan_id" class="w-full px-3 py-2 text-xs sm:text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9] bg-white">
                            <option value="">Semua Karyawan</option>
                            @foreach($karyawans as $karyawan)
                                <option value="{{ $karyawan->id }}" {{ request('karyawan_id') == $karyawan->id ? 'selected' : '' }}>
                                    {{ $karyawan->nama_lengkap }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex-1 min-w-[140px]">
                        <select name="jenis_cuti" class="w-full px-3 py-2 text-xs sm:text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9] bg-white">
                            <option value="">Semua Jenis Cuti</option>
                            @foreach($cutiTypes as $t)
                                <option value="{{ $t->nama }}" {{ request('jenis_cuti') === $t->nama ? 'selected' : '' }}>
                                    {{ $t->nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <button type="submit" class="inline-flex items-center gap-1 bg-[#27438D] text-white px-4 py-2 rounded-lg hover:bg-[#161758] transition-colors text-xs sm:text-sm font-bold">
                        <i class="fa-solid fa-filter"></i> Filter
                    </button>

                    @if(request('status') || request('karyawan_id') || request('jenis_cuti'))
                        <a href="{{ route('superadmin.manajemen-cuti.index', ['tab' => 'pengajuan']) }}"
                           class="inline-flex items-center gap-1 bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600 transition-colors text-xs sm:text-sm font-bold">
                            <i class="fa-solid fa-rotate-left"></i> Reset
                        </a>
                    @endif
                </form>
            </div>

            <!-- Table & Bulk Actions -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                <div class="p-4 border-b border-gray-200 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2">
                    <h2 class="text-base font-bold text-[#161758]">Daftar Pengajuan Cuti Seluruh Karyawan</h2>
                    <span class="text-xs text-gray-500">Total: {{ $cuti->total() }} data</span>
                </div>

                <!-- Bulk Action -->
                <div class="p-3 sm:p-4 border-b border-gray-200 bg-[#F8F9FA] flex flex-wrap items-center gap-3">
                    <span class="text-xs sm:text-sm font-bold text-gray-700">Aksi Massal:</span>
                    <form id="bulkApproveForm" action="{{ route('superadmin.manajemen-cuti.bulk-approve') }}" method="POST" class="flex flex-wrap items-center gap-2">
                        @csrf
                        <input type="hidden" name="ids" id="bulkIds" value="">
                        <input type="hidden" name="target_status" id="bulkStatus" value="">
                        <button type="button" onclick="bulkAction('approved')"
                                class="inline-flex items-center bg-[#2E7D3E] text-white px-3 py-1.5 rounded-lg hover:bg-green-700 transition-colors text-xs font-bold disabled:opacity-50 disabled:cursor-not-allowed"
                                id="btnApproveAll" disabled>
                            <i class="fa-solid fa-check mr-1.5"></i> Setujui Semua
                        </button>
                        <button type="button" onclick="bulkAction('rejected')"
                                class="inline-flex items-center bg-[#ec1d1d] text-white px-3 py-1.5 rounded-lg hover:bg-red-700 transition-colors text-xs font-bold disabled:opacity-50 disabled:cursor-not-allowed"
                                id="btnRejectAll" disabled>
                            <i class="fa-solid fa-xmark mr-1.5"></i> Tolak Semua
                        </button>
                        <span class="text-xs text-gray-500 ml-2" id="selectedCount">Belum ada yang dipilih</span>
                    </form>
                </div>

                <!-- Desktop Table -->
                <div class="hidden sm:block overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-[#F5F5F5] text-xs font-bold text-[#1B1B1B]">
                            <tr>
                                <th class="px-4 py-3">
                                    <input type="checkbox" id="selectAll" onchange="toggleAllCheckbox(this)" class="rounded border-gray-300">
                                </th>
                                <th class="px-4 py-3">No</th>
                                <th class="px-4 py-3">Karyawan</th>
                                <th class="px-4 py-3">Jenis Cuti</th>
                                <th class="px-4 py-3">Tanggal Cuti</th>
                                <th class="px-4 py-3">Durasi</th>
                                <th class="px-4 py-3">Sisa Kuota</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 text-xs">
                            @forelse($cuti as $item)
                                <tr class="hover:bg-gray-50/70 transition-colors">
                                    <td class="px-4 py-3">
                                        @if($item->status === 'pending')
                                            <input type="checkbox" class="cuti-checkbox rounded border-gray-300" value="{{ $item->id }}" onchange="updateSelectedCount()">
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-gray-500">{{ $loop->iteration + ($cuti->currentPage() - 1) * $cuti->perPage() }}</td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-2">
                                            <div class="w-8 h-8 rounded-full bg-[#27438D] text-white flex items-center justify-center text-xs font-bold">
                                                {{ strtoupper(substr($item->karyawan->nama_lengkap, 0, 2)) }}
                                            </div>
                                            <div>
                                                <p class="font-bold text-[#1B1B1B]">{{ $item->karyawan->nama_lengkap }}</p>
                                                <p class="text-[11px] text-gray-400">{{ $item->karyawan->jabatan ?? '-' }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-xs font-bold bg-blue-50 text-[#00a2e9]">
                                            {{ $item->jenis_cuti }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <p class="font-medium text-gray-800">
                                            {{ $item->tanggal_mulai ? $item->tanggal_mulai->format('d/m/Y') : '-' }}
                                            <span class="text-gray-400">→</span>
                                            {{ $item->tanggal_selesai ? $item->tanggal_selesai->format('d/m/Y') : '-' }}
                                        </p>
                                        @if($item->keterangan)
                                            <p class="text-[11px] text-gray-400 max-w-xs truncate" title="{{ $item->keterangan }}">{{ $item->keterangan }}</p>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 font-bold text-[#27438D] whitespace-nowrap">{{ $item->durasi }} hari</td>
                                    <td class="px-4 py-3">
                                        <span class="font-bold {{ $item->sisa_cuti <= 3 ? 'text-[#ec1d1d]' : 'text-[#2E7D3E]' }}">
                                            {{ $item->sisa_cuti }} hari
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="px-2 py-1 rounded-full text-[10px] font-bold {{ $item->status_badge }}">
                                            {{ $item->status_label }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <div class="inline-flex items-center gap-1">
                                            @if($item->status === 'pending')
                                                <form action="{{ route('superadmin.manajemen-cuti.approve', $item->id) }}" method="POST" class="inline">
                                                    @csrf
                                                    <input type="hidden" name="status" value="approved">
                                                    <button type="submit" class="p-1.5 rounded-lg text-emerald-600 hover:bg-emerald-50 transition-colors" title="Setujui Pengajuan">
                                                        <i class="fa-solid fa-check"></i>
                                                    </button>
                                                </form>
                                                <form action="{{ route('superadmin.manajemen-cuti.approve', $item->id) }}" method="POST" class="inline">
                                                    @csrf
                                                    <input type="hidden" name="status" value="rejected">
                                                    <button type="submit" class="p-1.5 rounded-lg text-rose-600 hover:bg-rose-50 transition-colors" title="Tolak Pengajuan">
                                                        <i class="fa-solid fa-xmark"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="px-4 py-8 text-center text-gray-400">
                                        Belum ada pengajuan cuti yang sesuai filter.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Mobile Cards -->
                <div class="sm:hidden divide-y divide-gray-200">
                    @forelse($cuti as $item)
                        <div class="p-3 space-y-2">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    @if($item->status === 'pending')
                                        <input type="checkbox" class="cuti-checkbox-mobile rounded border-gray-300" value="{{ $item->id }}" onchange="updateSelectedCount()">
                                    @endif
                                    <span class="text-sm font-bold text-[#1B1B1B]">{{ $item->karyawan->nama_lengkap }}</span>
                                </div>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $item->status_badge }}">
                                    {{ $item->status_label }}
                                </span>
                            </div>
                            <div class="text-xs text-gray-600 space-y-1">
                                <p><span class="font-bold text-gray-500">Jenis:</span> {{ $item->jenis_cuti }}</p>
                                <p><span class="font-bold text-gray-500">Tanggal:</span> {{ $item->tanggal_mulai ? $item->tanggal_mulai->format('d/m/Y') : '-' }} → {{ $item->tanggal_selesai ? $item->tanggal_selesai->format('d/m/Y') : '-' }} ({{ $item->durasi }} hari)</p>
                                <p><span class="font-bold text-gray-500">Sisa Kuota:</span> {{ $item->sisa_cuti }} hari</p>
                            </div>
                            @if($item->status === 'pending')
                                <div class="flex items-center gap-2 pt-2 border-t border-gray-100">
                                    <form action="{{ route('superadmin.manajemen-cuti.approve', $item->id) }}" method="POST" class="inline">
                                        @csrf
                                        <input type="hidden" name="status" value="approved">
                                        <button type="submit" class="px-3 py-1 rounded-lg bg-emerald-600 text-white text-xs font-bold">
                                            Setujui
                                        </button>
                                    </form>
                                    <form action="{{ route('superadmin.manajemen-cuti.approve', $item->id) }}" method="POST" class="inline">
                                        @csrf
                                        <input type="hidden" name="status" value="rejected">
                                        <button type="submit" class="px-3 py-1 rounded-lg bg-rose-600 text-white text-xs font-bold">
                                            Tolak
                                        </button>
                                    </form>
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="p-6 text-center text-gray-400 text-xs">Belum ada pengajuan cuti.</div>
                    @endforelse
                </div>

                <!-- Pagination -->
                <div class="p-4 border-t border-gray-200">
                    {{ $cuti->links() }}
                </div>
            </div>
        @endif

    </div>
</div>

<script>
function openEditModal(item) {
    document.getElementById('editModal').classList.remove('hidden');
    document.getElementById('editForm').action = '/superadmin/manajemen-cuti/type/' + item.id;
    document.getElementById('edit_nama').value = item.nama;
    document.getElementById('edit_kuota').value = item.kuota_hari;
    document.getElementById('edit_sort').value = item.sort_order;
    document.getElementById('edit_scope').value = item.scope_golongan || '';
    document.getElementById('edit_keterangan').value = item.keterangan || '';
    document.getElementById('edit_active').checked = !!item.is_active;
}

function closeEditModal() {
    document.getElementById('editModal').classList.add('hidden');
}

function toggleAllCheckbox(selectAll) {
    const checkboxes = document.querySelectorAll('.cuti-checkbox, .cuti-checkbox-mobile');
    checkboxes.forEach(cb => {
        cb.checked = selectAll.checked;
    });
    updateSelectedCount();
}

function updateSelectedCount() {
    const checkboxes = document.querySelectorAll('.cuti-checkbox:checked, .cuti-checkbox-mobile:checked');
    const count = checkboxes.length;
    const selectedCount = document.getElementById('selectedCount');
    const btnApproveAll = document.getElementById('btnApproveAll');
    const btnRejectAll = document.getElementById('btnRejectAll');

    if (count > 0) {
        if (selectedCount) selectedCount.textContent = `${count} data dipilih`;
        if (btnApproveAll) btnApproveAll.disabled = false;
        if (btnRejectAll) btnRejectAll.disabled = false;
    } else {
        if (selectedCount) selectedCount.textContent = 'Belum ada yang dipilih';
        if (btnApproveAll) btnApproveAll.disabled = true;
        if (btnRejectAll) btnRejectAll.disabled = true;
    }
}

function bulkAction(status) {
    const checkboxes = document.querySelectorAll('.cuti-checkbox:checked, .cuti-checkbox-mobile:checked');
    const ids = Array.from(checkboxes).map(cb => cb.value);

    if (ids.length === 0) {
        alert('Pilih minimal satu data pengajuan cuti!');
        return;
    }

    const actionText = status === 'approved' ? 'menyetujui' : 'menolak';
    if (!confirm(`Yakin ingin ${actionText} ${ids.length} pengajuan cuti yang dipilih?`)) {
        return;
    }

    document.getElementById('bulkIds').value = JSON.stringify(ids);
    document.getElementById('bulkStatus').value = status;
    document.getElementById('bulkApproveForm').submit();
}

document.addEventListener('DOMContentLoaded', function() {
    updateSelectedCount();
});
</script>
@endsection
