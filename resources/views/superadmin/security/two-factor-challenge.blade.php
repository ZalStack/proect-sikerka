@extends('layouts.app')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-[#0F1245] via-[#161758] to-[#00a2e9]/40 p-4">
    <div class="w-full max-w-md">
        <div class="bg-white rounded-3xl shadow-2xl p-8 space-y-6">
            <div class="text-center space-y-2">
                <div class="w-16 h-16 mx-auto rounded-2xl bg-[#00a2e9]/10 text-[#00a2e9] flex items-center justify-center text-3xl">
                    <i class="fa-solid fa-shield-halved"></i>
                </div>
                <h1 class="text-xl font-extrabold text-[#0F1245] font-['Poppins']">Verifikasi Dua Langkah</h1>
                <p class="text-sm text-gray-500">
                    Masukkan kode 6 digit dari aplikasi authenticator Anda untuk melanjutkan aksi kritis ini.
                </p>
            </div>

            @include('superadmin.partials.flash')

            <form action="{{ route('superadmin.security.two-factor-challenge.store') }}" method="POST" class="space-y-4">
                @csrf
                @if(!empty($redirect))
                    <input type="hidden" name="redirect" value="{{ $redirect }}">
                @endif

                <input type="text" name="code" inputmode="numeric" autocomplete="one-time-code" maxlength="64" required autofocus
                       class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-[#00a2e9] focus:ring-2 focus:ring-[#00a2e9]/20 text-lg font-bold tracking-[0.4em] text-center"
                       placeholder="000000">

                <p class="text-[11px] text-gray-400 text-center">
                    Kehabisan akses? Masukkan salah satu <strong>recovery code</strong> Anda (sekali pakai).
                </p>

                <button type="submit" class="w-full px-6 py-3 rounded-xl bg-[#00a2e9] hover:bg-[#0088c7] text-white text-sm font-bold transition-all shadow-lg shadow-[#00a2e9]/25">
                    <i class="fa-solid fa-unlock-keyhole mr-1"></i> Verifikasi & Lanjutkan
                </button>
            </form>

            <a href="{{ route('superadmin.security.index') }}" class="block text-center text-xs text-gray-400 hover:text-[#00a2e9] transition-colors">
                <i class="fa-solid fa-arrow-left mr-1"></i> Kembali ke Pusat Keamanan
            </a>
        </div>
    </div>
</div>
@endsection
