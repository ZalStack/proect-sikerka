@extends('layouts.app')

@section('content')
<div class="flex">
    @include('layouts.sidebar')

    <div class="flex-1 min-w-0 md:ml-64 p-4 sm:p-6 lg:p-8 space-y-8">
        
        <!-- Header & Breadcrumbs -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">
                    <a href="{{ route('superadmin.dashboard') }}" class="hover:text-[#00a2e9]">Super Admin</a>
                    <span>/</span>
                    <span class="text-gray-700">Kontrol Fitur & Maintenance</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-[#0F1245] font-['Montserrat']">
                    Pusat Kontrol Sistem & Fitur
                </h1>
                <p class="text-xs sm:text-sm text-gray-500 font-light mt-0.5">
                    Kelola mode pemeliharaan darurat dan aktifkan atau nonaktifkan fitur-fitur SIKEKAR secara menyeluruh maupun sebagian.
                </p>
            </div>

            <a href="{{ route('superadmin.dashboard') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold transition-all">
                <i class="fa-solid fa-arrow-left"></i>
                <span>Kembali ke Dashboard</span>
            </a>
        </div>

        <!-- Flash Alert -->
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

        <!-- SECTION 1: MAINTENANCE MODE CONTROLLER -->
        <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-sm border {{ $maintenanceInfo['is_active'] ? 'border-rose-200 ring-2 ring-rose-400/20' : 'border-gray-100' }} space-y-6">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-6 border-b border-gray-100">
                <div class="flex items-start gap-4">
                    <div class="w-14 h-14 rounded-2xl flex items-center justify-center text-2xl flex-shrink-0 {{ $maintenanceInfo['is_active'] ? 'bg-rose-500 text-white shadow-lg shadow-rose-500/30' : 'bg-gray-100 text-gray-600' }}">
                        <i class="fa-solid fa-power-off"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2.5 flex-wrap">
                            <h2 class="text-xl font-bold text-gray-900">Mode Pemeliharaan Sistem (Maintenance Mode)</h2>
                            <span class="px-3 py-0.5 rounded-full text-xs font-extrabold tracking-wide uppercase {{ $maintenanceInfo['is_active'] ? 'bg-rose-100 text-rose-700 animate-pulse' : 'bg-emerald-100 text-emerald-700' }}">
                                {{ $maintenanceInfo['is_active'] ? 'Sistem Non-Aktif (Offline)' : 'Sistem Normal (Online)' }}
                            </span>
                        </div>
                        <p class="text-xs sm:text-sm text-gray-500 mt-1 max-w-2xl">
                            Bila diaktifkan, seluruh pengguna non-superadmin (Karyawan & HR) yang mengakses sistem akan langsung diarahkan ke Halaman Pemeliharaan Baru. Super Admin tetap memiliki akses penuh tanpa terputus.
                        </p>
                    </div>
                </div>

                <!-- Quick Big Switch Button -->
                <form action="{{ route('superadmin.maintenance.toggle') }}" method="POST" class="flex-shrink-0">
                    @csrf
                    <button type="submit" class="w-full md:w-auto px-6 py-3 rounded-2xl font-bold text-sm transition-all flex items-center justify-center gap-2 shadow-lg {{ $maintenanceInfo['is_active'] ? 'bg-emerald-600 hover:bg-emerald-700 text-white shadow-emerald-600/20 hover:scale-105' : 'bg-rose-600 hover:bg-rose-700 text-white shadow-rose-600/20 hover:scale-105' }}">
                        <i class="fa-solid {{ $maintenanceInfo['is_active'] ? 'fa-play' : 'fa-pause' }}"></i>
                        <span>{{ $maintenanceInfo['is_active'] ? 'Matikan Maintenance (Kembali Online)' : 'Aktifkan Maintenance Sekarang' }}</span>
                    </button>
                </form>
            </div>

            <!-- Maintenance Settings Form -->
            <form action="{{ route('superadmin.maintenance.update') }}" method="POST" class="space-y-5">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label for="title" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">
                            Judul Pesan Pemeliharaan
                        </label>
                        <input type="text" name="title" id="title" value="{{ old('title', $maintenanceInfo['title']) }}" required class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-[#00a2e9] focus:ring-2 focus:ring-[#00a2e9]/20 text-sm font-medium transition-all" placeholder="Contoh: Sistem Sedang Dalam Pemeliharaan">
                    </div>

                    <div>
                        <label for="end_time" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">
                            Estimasi Waktu Selesai (Teks / Waktu)
                        </label>
                        <input type="text" name="end_time" id="end_time" value="{{ old('end_time', $maintenanceInfo['end_time']) }}" required class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-[#00a2e9] focus:ring-2 focus:ring-[#00a2e9]/20 text-sm font-medium transition-all" placeholder="Contoh: 14:00 WIB / 30 Menit Lagi / Segera">
                    </div>

                    <div class="md:col-span-2">
                        <label for="message" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">
                            Isi Pesan Penjelasan ke Karyawan
                        </label>
                        <textarea name="message" id="message" rows="3" required class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-[#00a2e9] focus:ring-2 focus:ring-[#00a2e9]/20 text-sm font-medium transition-all leading-relaxed" placeholder="Tuliskan keterangan perbaikan sistem...">{{ old('message', $maintenanceInfo['message']) }}</textarea>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-4 border-t border-gray-100">
                    <p class="text-xs text-gray-400">
                        <i class="fa-solid fa-circle-info text-[#00a2e9] mr-1"></i>
                        Pesan di atas akan langsung tampil di halaman maintenance publik secara real-time.
                    </p>

                    <button type="submit" class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-[#0F1245] hover:bg-[#161758] text-white text-xs font-bold transition-all shadow-md flex items-center justify-center gap-2">
                        <i class="fa-solid fa-floppy-disk"></i>
                        <span>Simpan Pengaturan Maintenance</span>
                    </button>
                </div>
            </form>
        </div>


        <!-- SECTION 2: GRANULAR FEATURE TOGGLES -->
        <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-gray-100 space-y-6">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-6 border-b border-gray-100">
                <div>
                    <h2 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                        <i class="fa-solid fa-sliders text-[#00a2e9]"></i>
                        <span>Kontrol Fitur Sistem (Feature Flags)</span>
                    </h2>
                    <p class="text-xs sm:text-sm text-gray-500 mt-1">
                        Aktifkan atau matikan semua fitur sekaligus, atau sesuaikan fitur spesifik yang ingin dibuka/ditutup untuk operasional.
                    </p>
                </div>

                <!-- Master Bulk Toggles -->
                <div class="flex items-center gap-2 flex-wrap">
                    <form action="{{ route('superadmin.features.toggle-all') }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin MEMATIKAN SEMUA FITUR sistem? Pengguna biasa tidak akan bisa mengakses modul terkait.')">
                        @csrf
                        <input type="hidden" name="action" value="disable_all">
                        <button type="submit" class="px-4 py-2.5 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 text-xs font-bold transition-all flex items-center gap-1.5 shadow-sm">
                            <i class="fa-solid fa-toggle-off"></i>
                            <span>Matikan SEMUA Fitur</span>
                        </button>
                    </form>

                    <form action="{{ route('superadmin.features.toggle-all') }}" method="POST">
                        @csrf
                        <input type="hidden" name="action" value="enable_all">
                        <button type="submit" class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition-all flex items-center gap-1.5 shadow-md shadow-emerald-600/20">
                            <i class="fa-solid fa-toggle-on"></i>
                            <span>Aktifkan SEMUA Fitur</span>
                        </button>
                    </form>
                </div>
            </div>

            <!-- Features Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-4 sm:gap-6">
                @foreach($features as $key => $feat)
                    <div class="rounded-2xl p-5 border transition-all duration-200 {{ $feat['is_enabled'] ? 'bg-white border-gray-200/80 shadow-sm hover:shadow-md' : 'bg-gray-50/70 border-gray-200 opacity-75' }}">
                        <div class="flex items-start justify-between gap-4">
                            
                            <div class="flex items-start gap-3.5">
                                <div class="w-12 h-12 rounded-2xl flex items-center justify-center text-xl flex-shrink-0 shadow-sm" style="background-color: {{ $feat['color'] }}15; color: {{ $feat['color'] }}; border: 1px solid {{ $feat['color'] }}30;">
                                    <i class="fa-solid {{ $feat['icon'] }}"></i>
                                </div>
                                <div class="space-y-1">
                                    <div class="flex items-center gap-2">
                                        <h3 class="text-sm sm:text-base font-bold text-gray-900">{{ $feat['name'] }}</h3>
                                    </div>
                                    <span class="inline-block text-[10px] font-semibold px-2 py-0.5 rounded-md bg-gray-100 text-gray-600">
                                        {{ $feat['category'] }}
                                    </span>
                                    <p class="text-xs text-gray-500 font-light leading-relaxed pt-1">
                                        {{ $feat['description'] }}
                                    </p>
                                </div>
                            </div>

                            <!-- Toggle Switch Button -->
                            <form action="{{ route('superadmin.features.toggle', $key) }}" method="POST" class="flex-shrink-0">
                                @csrf
                                <button type="submit" class="relative inline-flex h-7 w-14 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none {{ $feat['is_enabled'] ? 'bg-emerald-500' : 'bg-gray-300' }}" role="switch" aria-checked="{{ $feat['is_enabled'] ? 'true' : 'false' }}">
                                    <span class="sr-only">Toggle {{ $feat['name'] }}</span>
                                    <span aria-hidden="true" class="pointer-events-none inline-block h-6 w-6 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $feat['is_enabled'] ? 'translate-x-7' : 'translate-x-0' }}"></span>
                                </button>
                                <span class="block text-center text-[10px] font-bold mt-1 {{ $feat['is_enabled'] ? 'text-emerald-600' : 'text-gray-400' }}">
                                    {{ $feat['is_enabled'] ? 'AKTIF' : 'NON-AKTIF' }}
                                </span>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

    </div>
</div>
@endsection
