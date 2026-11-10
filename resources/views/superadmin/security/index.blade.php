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
                    <span class="text-gray-700">Pusat Keamanan</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-[#0F1245] font-['Poppins']">Pusat Keamanan</h1>
                <p class="text-xs sm:text-sm text-gray-500 font-light mt-0.5 max-w-2xl">
                    Two-Factor Authentication, whitelist IP panel Super Admin, dan manajemen sesi aktif akun Anda.
                </p>
            </div>
        </div>

        @include('superadmin.partials.flash')

        <!-- Recovery codes (sekali tampil setelah aktivasi 2FA) -->
        @if(session('recovery_codes'))
            <div class="bg-amber-50 border border-amber-200 rounded-3xl p-6 space-y-3">
                <div class="flex items-center gap-2 text-amber-800 font-bold text-sm">
                    <i class="fa-solid fa-key"></i>
                    <span>Simpan Recovery Code Sekarang (Hanya Ditampilkan Sekali)</span>
                </div>
                <p class="text-xs text-amber-700">
                    Gunakan salah satu kode ini bila Anda kehilangan akses ke aplikasi authenticator. Setiap kode hanya berlaku satu kali.
                </p>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                    @foreach(session('recovery_codes') as $code)
                        <code class="bg-white border border-amber-200 rounded-lg px-3 py-2 text-center text-sm font-bold text-gray-800 tracking-wider">{{ $code }}</code>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- SECTION 1: TWO-FACTOR AUTHENTICATION -->
        <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-gray-100 space-y-5">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-5 border-b border-gray-100">
                <div class="flex items-start gap-4">
                    <div class="w-14 h-14 rounded-2xl flex items-center justify-center text-2xl flex-shrink-0 {{ $twoFactorEnabled ? 'bg-emerald-500 text-white shadow-lg shadow-emerald-500/30' : 'bg-gray-100 text-gray-500' }}">
                        <i class="fa-solid fa-mobile-screen-button"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2.5 flex-wrap">
                            <h2 class="text-xl font-bold text-gray-900">Two-Factor Authentication (2FA)</h2>
                            <span class="px-3 py-0.5 rounded-full text-xs font-extrabold uppercase {{ $twoFactorEnabled ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-600' }}">
                                {{ $twoFactorEnabled ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </div>
                        <p class="text-xs sm:text-sm text-gray-500 mt-1 max-w-2xl">
                            Verifikasi kode 6 digit dari aplikasi authenticator (Google Authenticator / Authy) sebelum menjalankan aksi kritis seperti hapus backup, ubah role, dan hapus akun admin.
                        </p>
                        @if($twoFactorEnabled && !empty($user->two_factor_confirmed_at))
                            <p class="text-[11px] text-gray-400 mt-1">Aktif sejak {{ \Carbon\Carbon::parse($user->two_factor_confirmed_at)->translatedFormat('d M Y, H:i') }}</p>
                        @endif
                    </div>
                </div>

                @if(!$twoFactorEnabled)
                    <a href="{{ route('superadmin.security.two-factor') }}" class="px-6 py-3 rounded-2xl bg-[#00a2e9] hover:bg-[#0088c7] text-white text-sm font-bold transition-all shadow-lg shadow-[#00a2e9]/25 flex items-center justify-center gap-2">
                        <i class="fa-solid fa-shield-halved"></i>
                        <span>Aktifkan 2FA</span>
                    </a>
                @endif
            </div>

            @if($twoFactorEnabled)
                <form action="{{ route('superadmin.security.two-factor.disable') }}" method="POST" class="flex flex-col sm:flex-row items-end gap-3" onsubmit="return confirm('Nonaktifkan 2FA? Keamanan akun Anda akan menurun.')">
                    @csrf
                    <div class="flex-1 w-full">
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Masukkan Kode 6 Digit untuk Menonaktifkan</label>
                        <input type="text" name="code" inputmode="numeric" pattern="\d{6}" maxlength="6" required
                               class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-[#00a2e9] focus:ring-2 focus:ring-[#00a2e9]/20 text-sm font-medium tracking-[0.3em]" placeholder="000000">
                    </div>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 text-xs font-bold transition-all">
                        <i class="fa-solid fa-xmark mr-1"></i> Nonaktifkan 2FA
                    </button>
                </form>
            @endif
        </div>

        <!-- SECTION 2: WHITELIST IP & KEWAJIBAN 2FA -->
        <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-gray-100 space-y-5">
            <div class="pb-5 border-b border-gray-100">
                <h2 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                    <i class="fa-solid fa-network-wired text-[#00a2e9]"></i>
                    <span>Whitelist IP & Kebijakan Keamanan</span>
                </h2>
                <p class="text-xs sm:text-sm text-gray-500 mt-1">
                    Batasi akses panel Super Admin hanya dari IP tertentu. Kosongkan agar semua IP diizinkan. IP Anda saat ini: <code class="bg-gray-100 px-2 py-0.5 rounded">{{ $currentIp }}</code>
                </p>
            </div>

            <form action="{{ route('superadmin.security.whitelist.update') }}" method="POST" class="space-y-5">
                @csrf
                <div>
                    <label for="whitelist" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Daftar IP yang Diizinkan</label>
                    <textarea name="whitelist" id="whitelist" rows="3" placeholder="192.168.1.10, 10.0.0.0/24, 172.16.1.*"
                        class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-[#00a2e9] focus:ring-2 focus:ring-[#00a2e9]/20 text-sm font-medium leading-relaxed">{{ old('whitelist', $whitelist) }}</textarea>
                    <p class="text-[11px] text-gray-400 mt-1">Pisahkan dengan koma atau baris baru. Mendukung CIDR (<code>192.168.1.0/24</code>) dan wildcard (<code>10.0.0.*</code>).</p>
                </div>

                <label class="flex items-start gap-3 bg-gray-50 border border-gray-200 rounded-2xl p-4 cursor-pointer">
                    <input type="checkbox" name="require_2fa" value="1" {{ $twoFactorRequired ? 'checked' : '' }}
                           class="mt-0.5 w-4 h-4 rounded border-gray-300 text-[#00a2e9] focus:ring-[#00a2e9]">
                    <span class="text-sm text-gray-700">
                        <span class="font-bold">Wajibkan 2FA untuk seluruh Super Admin</span>
                        <span class="block text-xs text-gray-500 mt-0.5">Super Admin yang belum mengaktifkan 2FA akan dipaksa menuju halaman aktivasi sebelum bisa mengakses panel.</span>
                    </span>
                </label>

                <div class="flex justify-end">
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-[#0F1245] hover:bg-[#161758] text-white text-xs font-bold transition-all shadow-md flex items-center gap-2">
                        <i class="fa-solid fa-floppy-disk"></i> Simpan Pengaturan Keamanan
                    </button>
                </div>
            </form>
        </div>

        <!-- SECTION 3: SESI AKTIF -->
        <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-gray-100 space-y-5">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-5 border-b border-gray-100">
                <div>
                    <h2 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                        <i class="fa-solid fa-laptop text-[#00a2e9]"></i>
                        <span>Sesi Aktif</span>
                    </h2>
                    <p class="text-xs sm:text-sm text-gray-500 mt-1">Perangkat yang sedang login ke akun Anda. Keluarkan sesi yang tidak dikenali.</p>
                </div>

                <form action="{{ route('superadmin.security.sessions.revoke-others') }}" method="POST" onsubmit="return confirm('Keluarkan semua sesi lain? Semua perangkat lain akan logout.')">
                    @csrf
                    <button type="submit" class="px-4 py-2.5 rounded-xl bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200 text-xs font-bold transition-all">
                        <i class="fa-solid fa-right-from-bracket mr-1"></i> Keluarkan Semua Sesi Lain
                    </button>
                </form>
            </div>

            @if(count($sessions) === 0)
                <p class="text-sm text-gray-400 text-center py-6">
                    Tidak ada sesi tercatat (driver sesi aktif tidak menyimpan daftar sesi).
                </p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left text-[11px] uppercase tracking-wider text-gray-400 border-b border-gray-100">
                                <th class="py-2 pr-4 font-bold">IP</th>
                                <th class="py-2 pr-4 font-bold">Perangkat</th>
                                <th class="py-2 pr-4 font-bold">Terakhir Aktif</th>
                                <th class="py-2 pr-4 font-bold">Status</th>
                                <th class="py-2 font-bold text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach($sessions as $sesi)
                                <tr class="hover:bg-gray-50/60">
                                    <td class="py-3 pr-4 font-mono text-xs">{{ $sesi->ip_address }}</td>
                                    <td class="py-3 pr-4 text-xs text-gray-600 max-w-xs truncate" title="{{ $sesi->user_agent }}">{{ \Illuminate\Support\Str::limit((string) $sesi->user_agent, 60) }}</td>
                                    <td class="py-3 pr-4 text-xs text-gray-500">{{ \Carbon\Carbon::createFromTimestamp($sesi->last_activity)->diffForHumans() }}</td>
                                    <td class="py-3 pr-4">
                                        @if($sesi->is_current)
                                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-700 uppercase">Sesi Ini</span>
                                        @else
                                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-gray-100 text-gray-600 uppercase">Lain</span>
                                        @endif
                                    </td>
                                    <td class="py-3 text-right">
                                        @unless($sesi->is_current)
                                            <form action="{{ route('superadmin.security.sessions.destroy', $sesi->id) }}" method="POST" class="inline"
                                                  onsubmit="return confirm('Keluarkan sesi ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="px-3 py-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-600 text-[11px] font-bold border border-rose-100">
                                                    Keluarkan
                                                </button>
                                            </form>
                                        @endunless
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
@endsection
