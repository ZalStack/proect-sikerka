{{-- resources/views/profile/edit-superadmin.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="flex min-h-screen">
    @include('layouts.sidebar')
    <div class="flex-1 transition-all duration-300 md:ml-64 p-3 sm:p-6">
        <div class="flex flex-wrap justify-between items-start mb-6 gap-3">
            <div>
                <h1 class="text-2xl font-bold font-['Poppins'] text-[#161758]">Edit Profile</h1>
                <p class="text-[#27438D]">Perbarui nama dan email akun Super Admin Anda</p>
            </div>
            <a href="{{ route('profile.show') }}"
                class="w-full sm:w-auto bg-gray-500 text-white px-6 py-2 rounded-lg hover:bg-gray-600 transition-colors duration-200 text-center">
                <i class="fas fa-arrow-left mr-2"></i> Kembali ke Detail
            </a>
        </div>

        @if (session('success'))
            <div class="bg-[#2E7D3E] text-white p-4 rounded-lg mb-4">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
            <div class="bg-[#ec1d1d] text-white p-4 rounded-lg mb-4">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- ========== FORM EDIT PROFIL SUPER ADMIN (Nama & Email Saja) ========== -->
        <div class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden">
            <div class="p-6">
                <h2 class="text-xl font-bold text-[#161758] mb-2">Form Edit Profile</h2>
                <p class="text-sm text-[#27438D] mb-4">
                    <i class="fa-solid fa-shield-halved mr-1"></i>
                    Sebagai Super Admin, Anda hanya dapat mengubah <strong>Nama Lengkap</strong> dan
                    <strong>Email</strong>. Untuk mengubah password, gunakan menu
                    <a href="{{ route('profile.change-password') }}" class="text-[#00a2e9] font-semibold underline">Ubah Password</a>.
                </p>

                <form action="{{ route('profile.update') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-[#1B1B1B] mb-1">ID Karyawan</label>
                                <input type="text" value="{{ $user->kode_pegawai }}" readonly
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-100 focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                            </div>

                            <div class="mb-4">
                                <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Role</label>
                                <input type="text" value="Super Administrator" readonly
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-100 focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                            </div>
                        </div>

                        <div>
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Nama Lengkap <span class="text-[#ec1d1d]">*</span></label>
                                <input type="text" name="nama_lengkap"
                                    value="{{ old('nama_lengkap', $user->nama_lengkap) }}" required maxlength="100"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                                @error('nama_lengkap') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                            </div>

                            <div class="mb-4">
                                <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Email <span class="text-[#ec1d1d]">*</span></label>
                                <input type="email" name="email"
                                    value="{{ old('email', $user->email) }}" required maxlength="100"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                                @error('email') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>

                    <!-- TOMBOL AKSI -->
                    <div class="mt-6 flex flex-wrap gap-4">
                        <button type="submit"
                            class="w-full sm:w-auto bg-[#27438D] text-white px-6 py-2 rounded-lg hover:bg-[#161758] transition-colors duration-200">
                            <i class="fas fa-save mr-2"></i> Update Profile
                        </button>
                        <a href="{{ route('profile.show') }}"
                            class="w-full sm:w-auto bg-gray-500 text-white px-6 py-2 rounded-lg hover:bg-gray-600 transition-colors duration-200 text-center">
                            Batal
                        </a>
                        <a href="{{ route('profile.change-password') }}"
                            class="w-full sm:w-auto bg-[#FCC626] text-[#1B1B1B] px-6 py-2 rounded-lg hover:bg-[#e6b222] transition-colors duration-200 text-center">
                            <i class="fas fa-key mr-2"></i> Ubah Password
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
