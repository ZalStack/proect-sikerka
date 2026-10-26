@extends('layouts.app')

@section('content')
<div class="flex">
    @include('layouts.sidebar')

    <div class="flex-1 min-w-0 md:ml-64 p-4 sm:p-6 lg:p-8 space-y-6">
        
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">
                    <a href="{{ route('superadmin.dashboard') }}" class="hover:text-[#00a2e9]">Super Admin</a>
                    <span>/</span>
                    <span class="text-gray-700">Manajemen Role Pengguna</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-[#0F1245] font-['Poppins']">
                    Kelola Role & Hak Akses Pengguna
                </h1>
                <p class="text-xs sm:text-sm text-gray-500 font-light mt-0.5">
                    Atur hak akses pengguna menjadi Super Administrator, HR Administrator, atau Karyawan.
                </p>
            </div>

            <a href="{{ route('superadmin.dashboard') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold transition-all">
                <i class="fa-solid fa-arrow-left"></i>
                <span>Kembali ke Dashboard</span>
            </a>
        </div>

        <!-- Flash Messages -->
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

        <!-- Role KPI Cards -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <div class="bg-white rounded-2xl p-4 border border-gray-100 shadow-sm">
                <span class="text-xs font-medium text-gray-400">Total Akun Pengguna</span>
                <p class="text-2xl font-extrabold text-[#0F1245] mt-1">{{ $stats['total'] }}</p>
            </div>
            <div class="bg-white rounded-2xl p-4 border border-amber-200 shadow-sm bg-gradient-to-br from-white to-amber-50/40">
                <span class="text-xs font-bold text-amber-700">Super Administrator</span>
                <div class="flex items-baseline justify-between mt-1">
                    <span class="text-2xl font-extrabold text-amber-600">{{ $stats['superadmin'] }}</span>
                    <i class="fa-solid fa-crown text-amber-500 text-sm"></i>
                </div>
            </div>
            <div class="bg-white rounded-2xl p-4 border border-blue-100 shadow-sm">
                <span class="text-xs font-medium text-blue-600">HR Administrator</span>
                <p class="text-2xl font-extrabold text-blue-700 mt-1">{{ $stats['hr'] }}</p>
            </div>
            <div class="bg-white rounded-2xl p-4 border border-emerald-100 shadow-sm">
                <span class="text-xs font-medium text-emerald-600">Karyawan</span>
                <p class="text-2xl font-extrabold text-emerald-700 mt-1">{{ $stats['karyawan'] }}</p>
            </div>
        </div>

        <!-- Filter & Search -->
        <div class="bg-white rounded-3xl p-5 shadow-sm border border-gray-100">
            <form method="GET" action="{{ route('superadmin.karyawan.index') }}" class="flex flex-col sm:flex-row items-center justify-between gap-3">
                <div class="flex items-center gap-3 w-full sm:w-auto flex-1">
                    <div class="relative flex-1 max-w-md">
                        <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                        <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama, email, NIP, atau jabatan..." class="w-full pl-9 pr-3 py-2 rounded-xl border border-gray-200 text-xs font-medium focus:ring-2 focus:ring-[#00a2e9]/20 focus:border-[#00a2e9]">
                    </div>

                    <select name="posisi" class="px-3 py-2 rounded-xl border border-gray-200 text-xs font-medium focus:ring-2 focus:ring-[#00a2e9]/20 focus:border-[#00a2e9]">
                        <option value="">-- Semua Role --</option>
                        <option value="superadmin" {{ $posisi === 'superadmin' ? 'selected' : '' }}>Super Administrator</option>
                        <option value="hr" {{ $posisi === 'hr' ? 'selected' : '' }}>HR Administrator</option>
                        <option value="karyawan" {{ $posisi === 'karyawan' ? 'selected' : '' }}>Karyawan</option>
                    </select>
                </div>

                <div class="flex items-center gap-2 w-full sm:w-auto justify-end">
                    <a href="{{ route('superadmin.karyawan.index') }}" class="px-4 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-600 text-xs font-semibold transition-all">
                        Reset
                    </a>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-[#0F1245] hover:bg-[#161758] text-white text-xs font-bold transition-all shadow-sm flex items-center gap-1.5">
                        <i class="fa-solid fa-filter"></i>
                        <span>Filter</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- Users Table -->
        <div class="bg-white rounded-3xl p-5 sm:p-6 shadow-sm border border-gray-100 space-y-4">
            <div class="overflow-x-auto rounded-2xl border border-gray-100">
                <table class="w-full text-left text-xs sm:text-sm text-gray-600">
                    <thead class="bg-gray-50/80 text-gray-700 font-semibold text-xs border-b border-gray-100">
                        <tr>
                            <th class="px-4 py-3.5">Nama & NIP</th>
                            <th class="px-4 py-3.5">Email</th>
                            <th class="px-4 py-3.5">Divisi & Jabatan</th>
                            <th class="px-4 py-3.5">Role Saat Ini</th>
                            <th class="px-4 py-3.5 text-right">Ubah Hak Akses</th>
                            <th class="px-4 py-3.5 text-right">Reset Password</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($karyawans as $k)
                            <tr class="hover:bg-blue-50/25 transition-colors">
                                <td class="px-4 py-3.5">
                                    <div class="font-bold text-gray-900">{{ $k->nama_lengkap }}</div>
                                    <div class="text-[11px] text-gray-400">NIP: {{ $k->kode_pegawai }}</div>
                                </td>
                                <td class="px-4 py-3.5 font-['Poppins'] text-xs text-gray-700">
                                    {{ $k->email }}
                                </td>
                                <td class="px-4 py-3.5 text-xs">
                                    <span class="font-semibold text-gray-800">{{ $k->jabatan ?? '-' }}</span>
                                    <span class="block text-gray-400">{{ $k->divisi ?? '-' }}</span>
                                </td>
                                <td class="px-4 py-3.5 whitespace-nowrap">
                                    @if($k->posisi === 'superadmin')
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-extrabold bg-gradient-to-r from-amber-100 to-yellow-100 text-amber-800 border border-amber-300 shadow-sm">
                                            <i class="fa-solid fa-crown text-amber-600"></i>
                                            SUPERADMIN
                                        </span>
                                    @elseif($k->posisi === 'hr')
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-800 border border-blue-200">
                                            <i class="fa-solid fa-user-shield text-blue-600"></i>
                                            HR ADMIN
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-700">
                                            Karyawan
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3.5 text-right whitespace-nowrap">
                                    <form action="{{ route('superadmin.karyawan.update-role', $k->id) }}" method="POST" class="inline-flex items-center gap-2">
                                        @csrf
                                        @method('PUT')
                                        <select name="posisi" class="px-2.5 py-1.5 rounded-lg border border-gray-200 text-xs font-semibold focus:ring-2 focus:ring-[#00a2e9]/20 focus:border-[#00a2e9]">
                                            <option value="karyawan" {{ $k->posisi === 'karyawan' ? 'selected' : '' }}>Karyawan</option>
                                            <option value="hr" {{ $k->posisi === 'hr' ? 'selected' : '' }}>HR Admin</option>
                                            <option value="superadmin" {{ $k->posisi === 'superadmin' ? 'selected' : '' }}>Super Admin</option>
                                        </select>
                                        <button type="submit" class="px-3 py-1.5 rounded-lg bg-[#0F1245] hover:bg-[#161758] text-white text-xs font-semibold shadow-sm transition-all">
                                            Update
                                        </button>
                                    </form>
                                </td>
                                <td class="px-4 py-3.5 text-right whitespace-nowrap">
                                    <form action="{{ route('superadmin.karyawan.generate-reset-link', $k->id) }}" method="POST" class="inline-block"
                                        onsubmit="return confirm({{ json_encode('Buat link reset password baru untuk ' . $k->nama_lengkap . '? Link lama (jika ada) otomatis dicabut dan link baru berlaku 60 menit.') }});">
                                        @csrf
                                        <button type="submit" title="Generate link reset password"
                                            class="px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow-sm transition-all inline-flex items-center gap-1.5">
                                            <i class="fa-solid fa-key"></i>
                                            <span>Reset</span>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-10 text-gray-400">
                                    Tidak ada data pengguna.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="pt-4">
                {{ $karyawans->links() }}
            </div>
        </div>

    </div>
</div>

<!-- ==================== MODAL LINK RESET PASSWORD ==================== -->
@if(session('reset_link'))
<div id="resetLinkModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
    <div class="bg-white rounded-3xl shadow-2xl max-w-lg w-full p-6 sm:p-7 border border-white/20" style="animation: scale-in 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards;">
        <div class="flex justify-center mb-4">
            <div class="w-16 h-16 rounded-2xl bg-emerald-50 border border-emerald-100 flex items-center justify-center">
                <i class="fa-solid fa-key text-emerald-600 text-2xl"></i>
            </div>
        </div>

        <h3 class="text-xl font-['Poppins'] font-bold text-slate-800 text-center mb-1">Link Reset Password</h3>
        <p class="text-gray-500 text-center text-sm mb-5">
            Untuk <span class="font-semibold text-slate-700">{{ session('reset_link_name') }}</span>
            <span class="block text-xs text-gray-400">{{ session('reset_link_email') }}</span>
        </p>

        <label for="reset-link-input" class="block text-xs font-semibold text-gray-500 mb-1.5">Link Reset (klik "Salin" lalu kirimkan ke pengguna)</label>
        <div class="flex items-stretch gap-2">
            <input type="text" id="reset-link-input" readonly value="{{ session('reset_link') }}"
                class="flex-1 min-w-0 px-3.5 py-3 rounded-xl border border-gray-200 bg-gray-50 text-xs text-gray-700 font-medium focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
            <button type="button" id="copy-reset-link"
                class="px-4 py-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-sm transition-all inline-flex items-center gap-1.5 whitespace-nowrap">
                <i class="fa-regular fa-copy"></i>
                <span id="copy-reset-label">Salin</span>
            </button>
        </div>

        <div class="flex items-start gap-2 mt-3 text-xs text-gray-500">
            <i class="fa-regular fa-clock mt-0.5 text-amber-500"></i>
            <span>Berlaku hingga <span class="font-bold text-gray-700">{{ session('reset_link_expires') }}</span> dan hanya dapat dipakai sekali.</span>
        </div>

        <div class="flex flex-col sm:flex-row gap-2.5 mt-5">
            @if(session('reset_link_wa'))
            <a href="https://wa.me/{{ session('reset_link_wa') }}?text={{ rawurlencode('Halo, berikut link reset password SIKEKAR untuk Anda. Berlaku 60 menit, jangan dibagikan ke siapa pun: ' . session('reset_link')) }}"
                target="_blank" rel="noopener noreferrer"
                class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-3 rounded-xl bg-gradient-to-r from-emerald-600 to-green-500 hover:shadow-lg hover:shadow-emerald-500/30 text-white text-xs font-bold transition-all">
                <i class="fa-brands fa-whatsapp text-sm"></i>
                Kirim via WhatsApp
            </a>
            @endif
            <button type="button" onclick="closeResetLinkModal()"
                class="flex-1 px-4 py-3 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold transition-all">
                Tutup
            </button>
        </div>
    </div>
</div>

<style>@keyframes scale-in { from { opacity: 0; transform: scale(0.95); } to { opacity: 1; transform: scale(1); } }</style>

<script>
    (function () {
        const modal = document.getElementById('resetLinkModal');
        if (!modal) return;
        document.body.style.overflow = 'hidden';

        const copyBtn = document.getElementById('copy-reset-link');
        const copyLabel = document.getElementById('copy-reset-label');
        const input = document.getElementById('reset-link-input');

        copyBtn.addEventListener('click', async function () {
            try {
                if (navigator.clipboard && window.isSecureContext) {
                    await navigator.clipboard.writeText(input.value);
                } else {
                    input.removeAttribute('readonly');
                    input.select();
                    input.setSelectionRange(0, 99999);
                    document.execCommand('copy');
                    input.setAttribute('readonly', 'readonly');
                }
                copyLabel.textContent = 'Tersalin!';
                copyBtn.classList.add('bg-emerald-700');
                setTimeout(() => {
                    copyLabel.textContent = 'Salin';
                    copyBtn.classList.remove('bg-emerald-700');
                }, 2000);
            } catch (e) {
                input.focus();
                input.select();
            }
        });

        window.closeResetLinkModal = function () {
            modal.style.opacity = '0';
            modal.style.transition = 'opacity 0.25s ease';
            setTimeout(() => {
                modal.style.display = 'none';
                document.body.style.overflow = '';
            }, 250);
        };

        document.addEventListener('click', (e) => { if (e.target.id === 'resetLinkModal') closeResetLinkModal(); });
        document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeResetLinkModal(); });
    })();
</script>
@endif
@endsection
