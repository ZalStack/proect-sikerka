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
                    <span class="text-gray-700">Pengaturan Email</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-[#0F1245] font-['Poppins'] tracking-tight">
                    Pengaturan SMTP &amp; Email Keluar
                </h1>
                <p class="text-xs sm:text-sm text-gray-500 font-light mt-0.5 max-w-2xl">
                    Konfigurasi server email untuk laporan terjadwal, notifikasi, dan reset password. Pengaturan tersimpan di database dan langsung berlaku tanpa restart.
                </p>
            </div>

            <span class="px-4 py-2 rounded-2xl text-xs font-extrabold uppercase tracking-wide shadow-sm self-start
                {{ $configured ? 'bg-emerald-100 text-emerald-700 border border-emerald-200' : 'bg-amber-100 text-amber-700 border border-amber-200' }}">
                <i class="fa-solid {{ $configured ? 'fa-circle-check' : 'fa-triangle-exclamation' }} mr-1.5"></i>
                {{ $configured ? 'SMTP Terkonfigurasi' : 'Belum Dikonfigurasi' }}
            </span>
        </div>

        @include('superadmin.partials.flash')

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-4 sm:gap-5">

            <!-- FORM SMTP -->
            <div class="xl:col-span-2 bg-white rounded-3xl p-5 sm:p-7 shadow-sm border border-gray-100">
                <h2 class="text-base font-bold text-[#0F1245] flex items-center gap-2 mb-1">
                    <i class="fa-solid fa-server text-[#00a2e9]"></i>
                    Koneksi Server SMTP
                </h2>
                <p class="text-xs text-gray-500 mb-5">
                    Isi sesuai penyedia email Anda (Gmail, Outlook, host kantor, dll). Untuk uji cepat tanpa server, pilih <b>log</b> (email ditulis ke file log).
                </p>

                <form action="{{ route('superadmin.smtp.update') }}" method="POST" class="space-y-5">
                    @csrf

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label for="mail_mailer" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Mailer</label>
                            <select name="mail_mailer" id="mail_mailer" required
                                    class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-[#00a2e9] focus:ring-2 focus:ring-[#00a2e9]/20 text-sm">
                                <option value="smtp" {{ old('mail_mailer', $settings['mailer']) === 'smtp' ? 'selected' : '' }}>SMTP (kirim sungguhan)</option>
                                <option value="log" {{ old('mail_mailer', $settings['mailer']) === 'log' ? 'selected' : '' }}>Log (uji tanpa kirim)</option>
                            </select>
                            @error('mail_mailer')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label for="smtp_host" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Host SMTP</label>
                            <input type="text" name="smtp_host" id="smtp_host" value="{{ old('smtp_host', $settings['host']) }}"
                                   placeholder="smtp.gmail.com / mail.kantor.com"
                                   class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-[#00a2e9] focus:ring-2 focus:ring-[#00a2e9]/20 text-sm">
                            @error('smtp_host')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label for="smtp_port" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Port</label>
                            <input type="number" name="smtp_port" id="smtp_port" min="1" max="65535"
                                   value="{{ old('smtp_port', $settings['port']) }}" placeholder="587"
                                   class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-[#00a2e9] focus:ring-2 focus:ring-[#00a2e9]/20 text-sm">
                            @error('smtp_port')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label for="smtp_encryption" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Enkripsi</label>
                            <select name="smtp_encryption" id="smtp_encryption"
                                    class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-[#00a2e9] focus:ring-2 focus:ring-[#00a2e9]/20 text-sm">
                                <option value="tls" {{ old('smtp_encryption', $settings['encryption']) === 'tls' ? 'selected' : '' }}>TLS (587)</option>
                                <option value="ssl" {{ old('smtp_encryption', $settings['encryption']) === 'ssl' ? 'selected' : '' }}>SSL (465)</option>
                                <option value="none" {{ old('smtp_encryption', $settings['encryption']) === 'none' ? 'selected' : '' }}>Tanpa enkripsi</option>
                            </select>
                        </div>

                        <div>
                            <label for="smtp_username" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Username</label>
                            <input type="text" name="smtp_username" id="smtp_username" value="{{ old('smtp_username', $settings['username']) }}"
                                   autocomplete="off"
                                   class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-[#00a2e9] focus:ring-2 focus:ring-[#00a2e9]/20 text-sm">
                            @error('smtp_username')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label for="smtp_password" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Password / App Password</label>
                            <input type="password" name="smtp_password" id="smtp_password" value=""
                                   autocomplete="new-password" placeholder="{{ ($settings['password'] ?? '') !== '' ? 'Tersimpan — kosongkan untuk mempertahankan' : 'Password SMTP' }}"
                                   class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-[#00a2e9] focus:ring-2 focus:ring-[#00a2e9]/20 text-sm">
                            <p class="text-[11px] text-gray-400 mt-1">Gmail/Outlook umumnya memakai <b>App Password</b>, bukan password login biasa. Field kosong = password lama tetap dipakai.</p>
                            @error('smtp_password')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label for="smtp_from_name" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Nama Pengirim</label>
                            <input type="text" name="smtp_from_name" id="smtp_from_name" value="{{ old('smtp_from_name', $settings['from_name']) }}"
                                   placeholder="SIKEKAR"
                                   class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-[#00a2e9] focus:ring-2 focus:ring-[#00a2e9]/20 text-sm">
                            @error('smtp_from_name')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label for="smtp_from_address" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Alamat Email Pengirim</label>
                            <input type="email" name="smtp_from_address" id="smtp_from_address" value="{{ old('smtp_from_address', $settings['from_address']) }}"
                                   placeholder="no-reply@kantor.com"
                                   class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-[#00a2e9] focus:ring-2 focus:ring-[#00a2e9]/20 text-sm">
                            @error('smtp_from_address')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-4 border-t border-gray-100">
                        <p class="text-xs text-gray-400">
                            <i class="fa-solid fa-shield-halved text-[#00a2e9] mr-1"></i>
                            Password tidak pernah ditampilkan kembali setelah disimpan.
                        </p>
                        <button type="submit" class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-[#0F1245] hover:bg-[#161758] text-white text-xs font-bold transition-all shadow-md flex items-center justify-center gap-2">
                            <i class="fa-solid fa-floppy-disk"></i>
                            <span>Simpan Pengaturan SMTP</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- UJI KIRIM + INFO -->
            <div class="space-y-5">
                <div class="bg-white rounded-3xl p-5 sm:p-6 shadow-sm border border-gray-100">
                    <h2 class="text-base font-bold text-[#0F1245] flex items-center gap-2 mb-1">
                        <i class="fa-solid fa-paper-plane text-[#ea580c]"></i>
                        Uji Kirim Email
                    </h2>
                    <p class="text-xs text-gray-500 mb-4">Kirim email percobaan untuk memastikan koneksi SMTP berfungsi.</p>

                    <form action="{{ route('superadmin.smtp.test') }}" method="POST" class="space-y-3">
                        @csrf
                        <div>
                            <label for="test_email" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Email Tujuan</label>
                            <input type="email" name="test_email" id="test_email" required value="{{ old('test_email', auth()->user()?->email) }}"
                                   placeholder="nama@contoh.com"
                                   class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-[#ea580c] focus:ring-2 focus:ring-[#ea580c]/20 text-sm">
                            @error('test_email')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
                        </div>
                        <button type="submit" class="w-full px-5 py-2.5 rounded-xl bg-[#ea580c] hover:bg-[#c2410c] text-white text-xs font-bold transition-all shadow-md flex items-center justify-center gap-2">
                            <i class="fa-solid fa-paper-plane"></i>
                            <span>Kirim Email Uji Sekarang</span>
                        </button>
                    </form>
                </div>

                <div class="bg-[#0F1245] rounded-3xl p-5 sm:p-6 shadow-lg text-white space-y-3">
                    <h2 class="text-sm font-bold flex items-center gap-2">
                        <i class="fa-solid fa-lightbulb text-[#FCC626]"></i> Panduan Cepat
                    </h2>
                    <ul class="text-xs text-white/75 space-y-2.5 leading-relaxed">
                        <li><b class="text-[#FCC626]">Gmail:</b> host <code class="bg-white/10 px-1 rounded">smtp.gmail.com</code>, port 587, TLS, username email Gmail, password = App Password.</li>
                        <li><b class="text-[#FCC626]">Outlook:</b> host <code class="bg-white/10 px-1 rounded">smtp.office365.com</code>, port 587, TLS.</li>
                        <li><b class="text-[#FCC626]">Host kantor:</b> tanyakan tim IT nilai host/port/username yang dikeluarkan.</li>
                        <li><b class="text-[#FCC626]">Port 465</b> memakai SSL; <b>port 587</b> memakai TLS.</li>
                    </ul>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
