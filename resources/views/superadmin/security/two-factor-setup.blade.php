@extends('layouts.app')

@section('content')
<div class="flex">
    @include('layouts.sidebar')

    <div class="flex-1 min-w-0 md:ml-64 p-4 sm:p-6 lg:p-8 space-y-6">
        <div class="max-w-3xl">
            <div class="flex items-center gap-2 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">
                <a href="{{ route('superadmin.dashboard') }}" class="hover:text-[#00a2e9]">Super Admin</a>
                <span>/</span>
                <a href="{{ route('superadmin.security.index') }}" class="hover:text-[#00a2e9]">Pusat Keamanan</a>
                <span>/</span>
                <span class="text-gray-700">Aktivasi 2FA</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-[#0F1245] font-['Poppins']">Aktifkan Two-Factor Authentication</h1>
            <p class="text-xs sm:text-sm text-gray-500 font-light mt-0.5 max-w-2xl">
                Tiga langkah: pindai QR (atau masukkan kunci manual), masukkan kode 6 digit, lalu simpan recovery code.
            </p>
        </div>

        @include('superadmin.partials.flash')

        <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-gray-100 space-y-6 max-w-3xl">

            <!-- Langkah 1: QR -->
            <div class="space-y-3">
                <span class="inline-flex items-center gap-2 text-xs font-bold text-[#00a2e9] uppercase tracking-wider">
                    <span class="w-6 h-6 rounded-full bg-[#00a2e9] text-white flex items-center justify-center text-[11px]">1</span>
                    Pindai Kode QR
                </span>

                <div class="flex flex-col sm:flex-row gap-6 items-start">
                    <div class="bg-white border border-gray-200 rounded-2xl p-4 flex-shrink-0">
                        <img src="{{ $qrUrl }}" alt="QR Code aktivasi 2FA" width="220" height="220"
                             class="w-[220px] h-[220px]" onerror="this.style.display='none';document.getElementById('secret-fallback').style.display='block';">
                        <div id="secret-fallback" style="display:none" class="text-[11px] text-gray-500 text-center mt-2 max-w-[220px] break-all">
                            QR tidak dapat dimuat. Gunakan kunci manual di bawah.
                        </div>
                    </div>

                    <div class="space-y-3 text-sm text-gray-600">
                        <p class="leading-relaxed">Buka aplikasi <strong>Google Authenticator</strong>, <strong>Authy</strong>, atau aplikasi authenticator lain, lalu pindai kode di samping.</p>
                        <div>
                            <span class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Kunci Manual (bila tidak bisa memindai)</span>
                            <code class="block bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm font-bold tracking-[0.15em] break-all select-all">{{ $secret }}</code>
                        </div>
                        <div>
                            <span class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">URI Provisioning</span>
                            <code class="block bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-[11px] break-all select-all">{{ $uri }}</code>
                        </div>
                    </div>
                </div>
            </div>

            <hr class="border-gray-100">

            <!-- Langkah 2: konfirmasi kode -->
            <form action="{{ route('superadmin.security.two-factor.enable') }}" method="POST" class="space-y-4">
                @csrf
                <span class="inline-flex items-center gap-2 text-xs font-bold text-[#00a2e9] uppercase tracking-wider">
                    <span class="w-6 h-6 rounded-full bg-[#00a2e9] text-white flex items-center justify-center text-[11px]">2</span>
                    Masukkan Kode 6 Digit
                </span>

                <div class="flex flex-col sm:flex-row gap-3 items-end">
                    <div class="flex-1 w-full">
                        <input type="text" name="code" inputmode="numeric" pattern="\d{6}" maxlength="6" required autocomplete="one-time-code"
                               value="{{ old('code') }}"
                               class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-[#00a2e9] focus:ring-2 focus:ring-[#00a2e9]/20 text-lg font-bold tracking-[0.5em] text-center"
                               placeholder="000000">
                        @error('code')
                            <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <button type="submit" class="px-6 py-3 rounded-xl bg-[#00a2e9] hover:bg-[#0088c7] text-white text-sm font-bold transition-all shadow-lg shadow-[#00a2e9]/25">
                        <i class="fa-solid fa-check mr-1"></i> Aktifkan 2FA
                    </button>
                </div>
                <p class="text-[11px] text-gray-400">
                    <i class="fa-solid fa-circle-info mr-1"></i>
                    Pastikan waktu di perangkat Anda tersinkron otomatis agar kode sesuai.
                </p>
            </form>
        </div>
    </div>
</div>
@endsection
