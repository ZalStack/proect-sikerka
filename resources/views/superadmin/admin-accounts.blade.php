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
                    <span class="text-gray-700">Akun Admin</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-[#0F1245] font-['Poppins']">Kelola Akun Admin</h1>
                <p class="text-xs sm:text-sm text-gray-500 font-light mt-0.5 max-w-2xl">
                    Buat, ubah, reset kata sandi, dan hapus akun Super Admin & HR. Sesi aktif akun yang dihapus ikut dikeluarkan.
                </p>
            </div>

            <button type="button" onclick="document.getElementById('modal-create').classList.remove('hidden')"
                    class="px-5 py-3 rounded-2xl bg-[#00a2e9] hover:bg-[#0088c7] text-white text-sm font-bold transition-all shadow-lg shadow-[#00a2e9]/25 flex items-center justify-center gap-2">
                <i class="fa-solid fa-user-plus"></i> Tambah Akun Admin
            </button>
        </div>

        @include('superadmin.partials.flash')

        <!-- DAFTAR AKUN -->
        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50/70">
                        <tr class="text-left text-[11px] uppercase tracking-wider text-gray-400">
                            <th class="py-3 px-5 font-bold">Nama</th>
                            <th class="py-3 px-3 font-bold">Email / Kode</th>
                            <th class="py-3 px-3 font-bold">Role</th>
                            <th class="py-3 px-3 font-bold">2FA</th>
                            <th class="py-3 px-3 font-bold">Status</th>
                            <th class="py-3 px-5 font-bold text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($admins as $admin)
                            <tr class="hover:bg-gray-50/60 {{ $admin->trashed() ? 'opacity-60' : '' }}">
                                <td class="py-3 px-5">
                                    <div class="font-semibold text-gray-800">{{ $admin->nama_lengkap }}</div>
                                    <div class="text-[11px] text-gray-400">{{ $admin->jabatan }}</div>
                                </td>
                                <td class="py-3 px-3">
                                    <div class="text-xs text-gray-700">{{ $admin->email }}</div>
                                    <div class="text-[11px] text-gray-400 font-mono">{{ $admin->kode_pegawai }}</div>
                                </td>
                                <td class="py-3 px-3">
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase {{ $admin->posisi === 'superadmin' ? 'bg-[#0F1245]/10 text-[#0F1245]' : 'bg-sky-100 text-sky-700' }}">
                                        {{ $admin->posisi }}
                                    </span>
                                </td>
                                <td class="py-3 px-3">
                                    @if(!empty($admin->two_factor_confirmed_at))
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-700 uppercase">Aktif</span>
                                    @else
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-gray-100 text-gray-500 uppercase">Off</span>
                                    @endif
                                </td>
                                <td class="py-3 px-3">
                                    @if($admin->trashed())
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-100 text-rose-700 uppercase">Dihapus</span>
                                    @elseif($admin->is_resigned)
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-100 text-amber-700 uppercase">Resign</span>
                                    @else
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-700 uppercase">Aktif</span>
                                    @endif
                                </td>
                                <td class="py-3 px-5">
                                    <div class="flex items-center justify-end gap-1.5 flex-wrap">
                                        <button type="button"
                                                class="px-2.5 py-1.5 rounded-lg bg-sky-50 hover:bg-sky-100 text-sky-700 text-[11px] font-bold border border-sky-100"
                                                data-id="{{ $admin->id }}"
                                                data-nama="{{ $admin->nama_lengkap }}"
                                                data-email="{{ $admin->email }}"
                                                data-posisi="{{ $admin->posisi }}"
                                                data-jabatan="{{ $admin->jabatan }}"
                                                data-divisi="{{ $admin->divisi }}"
                                                onclick="openEdit(this)">
                                            <i class="fa-solid fa-pen mr-1"></i>Ubah
                                        </button>

                                        <form action="{{ route('superadmin.admins.reset-password', $admin->id) }}" method="POST" class="inline"
                                              onsubmit="return confirm('Reset kata sandi akun {{ $admin->email }}?')">
                                            @csrf
                                            <button type="submit" class="px-2.5 py-1.5 rounded-lg bg-amber-50 hover:bg-amber-100 text-amber-700 text-[11px] font-bold border border-amber-100">
                                                <i class="fa-solid fa-key mr-1"></i>Reset Sandi
                                            </button>
                                        </form>

                                        @if($admin->id !== Auth::id())
                                            <form action="{{ route('superadmin.admins.destroy', $admin->id) }}" method="POST" class="inline"
                                                  onsubmit="return confirm('Hapus akun {{ $admin->email }}? Sesi aktifnya akan dikeluarkan.')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="px-2.5 py-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-600 text-[11px] font-bold border border-rose-100">
                                                    <i class="fa-solid fa-trash mr-1"></i>Hapus
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 text-center text-sm text-gray-400">Belum ada akun admin.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- MODAL TAMBAH -->
<div id="modal-create" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-lg p-6 sm:p-8 space-y-4 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between">
            <h3 class="text-lg font-extrabold text-[#0F1245]">Tambah Akun Admin</h3>
            <button type="button" onclick="this.closest('.fixed').classList.add('hidden')" class="text-gray-400 hover:text-gray-700 text-xl">&times;</button>
        </div>

        <form action="{{ route('superadmin.admins.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Nama Lengkap</label>
                <input type="text" name="nama_lengkap" value="{{ old('nama_lengkap') }}" required class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-[#00a2e9] focus:ring-2 focus:ring-[#00a2e9]/20 text-sm">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-[#00a2e9] focus:ring-2 focus:ring-[#00a2e9]/20 text-sm">
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Role</label>
                    <select name="posisi" required class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-[#00a2e9] focus:ring-2 focus:ring-[#00a2e9]/20 text-sm">
                        <option value="hr" {{ old('posisi') === 'hr' ? 'selected' : '' }}>HR</option>
                        <option value="superadmin" {{ old('posisi') === 'superadmin' ? 'selected' : '' }}>Super Admin</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Kata Sandi (min 10)</label>
                    <input type="text" name="kata_sandi" value="{{ old('kata_sandi') }}" minlength="10" required class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-[#00a2e9] focus:ring-2 focus:ring-[#00a2e9]/20 text-sm">
                </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Jabatan</label>
                    <input type="text" name="jabatan" value="{{ old('jabatan') }}" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-[#00a2e9] focus:ring-2 focus:ring-[#00a2e9]/20 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Divisi</label>
                    <input type="text" name="divisi" value="{{ old('divisi') }}" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-[#00a2e9] focus:ring-2 focus:ring-[#00a2e9]/20 text-sm">
                </div>
            </div>

            @if($errors->any())
                <ul class="text-xs text-rose-600 list-disc list-inside space-y-0.5">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            @endif

            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="this.closest('.fixed').classList.add('hidden')" class="px-5 py-2.5 rounded-xl border border-gray-200 text-gray-600 text-xs font-bold">Batal</button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-[#00a2e9] hover:bg-[#0088c7] text-white text-xs font-bold shadow-md">Simpan Akun</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL UBAH -->
<div id="modal-edit" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-lg p-6 sm:p-8 space-y-4 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between">
            <h3 class="text-lg font-extrabold text-[#0F1245]">Ubah Akun Admin</h3>
            <button type="button" onclick="this.closest('.fixed').classList.add('hidden')" class="text-gray-400 hover:text-gray-700 text-xl">&times;</button>
        </div>

        <form id="form-edit" method="POST" class="space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Nama Lengkap</label>
                <input type="text" name="nama_lengkap" id="edit-nama" required class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-[#00a2e9] focus:ring-2 focus:ring-[#00a2e9]/20 text-sm">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Email</label>
                <input type="email" name="email" id="edit-email" required class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-[#00a2e9] focus:ring-2 focus:ring-[#00a2e9]/20 text-sm">
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Role</label>
                    <select name="posisi" id="edit-posisi" required class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-[#00a2e9] focus:ring-2 focus:ring-[#00a2e9]/20 text-sm">
                        <option value="hr">HR</option>
                        <option value="superadmin">Super Admin</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Kata Sandi Baru (opsional, min 10)</label>
                    <input type="text" name="kata_sandi" minlength="10" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-[#00a2e9] focus:ring-2 focus:ring-[#00a2e9]/20 text-sm" placeholder="Biarkan kosong bila tidak diubah">
                </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Jabatan</label>
                    <input type="text" name="jabatan" id="edit-jabatan" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-[#00a2e9] focus:ring-2 focus:ring-[#00a2e9]/20 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Divisi</label>
                    <input type="text" name="divisi" id="edit-divisi" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-[#00a2e9] focus:ring-2 focus:ring-[#00a2e9]/20 text-sm">
                </div>
            </div>

            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="this.closest('.fixed').classList.add('hidden')" class="px-5 py-2.5 rounded-xl border border-gray-200 text-gray-600 text-xs font-bold">Batal</button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-[#00a2e9] hover:bg-[#0088c7] text-white text-xs font-bold shadow-md">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEdit(el) {
    document.getElementById('form-edit').action = '{{ url('superadmin/akun-admin') }}/' + el.dataset.id;
    document.getElementById('edit-nama').value = el.dataset.nama || '';
    document.getElementById('edit-email').value = el.dataset.email || '';
    document.getElementById('edit-posisi').value = el.dataset.posisi || 'hr';
    document.getElementById('edit-jabatan').value = el.dataset.jabatan || '';
    document.getElementById('edit-divisi').value = el.dataset.divisi || '';
    document.getElementById('modal-edit').classList.remove('hidden');
}
</script>
@endsection
