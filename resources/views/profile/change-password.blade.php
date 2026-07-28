{{-- resources/views/profile/change-password.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="flex min-h-screen">
    @include('layouts.sidebar')
    <div class="flex-1 transition-all duration-300 md:ml-64 p-3 sm:p-6">
        <div class="flex flex-wrap justify-between items-start mb-6 gap-3">
            <div>
                <h1 class="text-2xl font-bold font-['Montserrat'] text-[#161758]">Ubah Password</h1>
                <p class="text-[#27438D]">Perbarui password akun Anda</p>
            </div>
            <a href="{{ route('profile.show') }}"
                class="w-full sm:w-auto bg-gray-500 text-white px-6 py-2 rounded-lg hover:bg-gray-600 transition-colors duration-200 text-center">
                <i class="fas fa-arrow-left mr-2"></i> Kembali ke Profile
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

        <!-- ========== FORM UBAH PASSWORD ========== -->
        <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6">
            <h2 class="text-lg font-semibold text-[#161758] mb-4">Form Ubah Password</h2>
            <form action="{{ route('profile.update-password') }}" method="POST">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="mb-4 relative">
                        <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Password Saat Ini <span class="text-[#ec1d1d]">*</span></label>
                        <input type="password" name="current_password" id="current_password" required
                            class="w-full px-4 py-2 pr-10 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                        <button type="button" onclick="togglePassword('current_password')"
                            class="absolute right-2 top-8 text-gray-500 hover:text-gray-700">
                            <i class="fas fa-eye"></i>
                        </button>
                        @error('current_password')
                            <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-4 relative">
                        <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Password Baru <span class="text-[#ec1d1d]">*</span></label>
                        <input type="password" name="password" id="password" required
                            class="w-full px-4 py-2 pr-10 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                        <button type="button" onclick="togglePassword('password')"
                            class="absolute right-2 top-8 text-gray-500 hover:text-gray-700">
                            <i class="fas fa-eye"></i>
                        </button>
                        @error('password')
                            <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-4 relative md:col-span-2">
                        <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Konfirmasi Password Baru <span class="text-[#ec1d1d]">*</span></label>
                        <input type="password" name="password_confirmation" id="password_confirmation" required
                            class="w-full px-4 py-2 pr-10 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                        <button type="button" onclick="togglePassword('password_confirmation')"
                            class="absolute right-2 top-8 text-gray-500 hover:text-gray-700">
                            <i class="fas fa-eye"></i>
                        </button>
                        @error('password_confirmation')
                            <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="mt-6 flex flex-wrap gap-4">
                    <button type="submit"
                        class="w-full sm:w-auto bg-[#FCC626] text-[#1B1B1B] px-6 py-2 rounded-lg hover:bg-[#e6b222] transition-colors duration-200">
                        <i class="fas fa-save mr-2"></i> Simpan Password Baru
                    </button>
                    <a href="{{ route('profile.show') }}"
                        class="w-full sm:w-auto bg-gray-500 text-white px-6 py-2 rounded-lg hover:bg-gray-600 transition-colors duration-200 text-center">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function togglePassword(fieldId) {
        const input = document.getElementById(fieldId);
        input.type = (input.type === 'password') ? 'text' : 'password';
    }
</script>
@endsection
