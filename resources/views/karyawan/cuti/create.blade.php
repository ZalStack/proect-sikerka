@extends('layouts.app')

@section('content')
<div class="flex min-h-screen">
    @include('layouts.sidebar')
    <!-- Main Content -->
    <div class="main-content flex-1 transition-all duration-300 md:ml-64 pt-6">
        <div class="p-4 sm:p-6">
            <div class="mb-6">
                <h1 class="text-xl sm:text-2xl font-bold font-['Montserrat'] text-[#161758]">Ajukan Cuti</h1>
                <p class="text-[#27438D] text-sm sm:text-base">Isi form untuk mengajukan cuti tahunan</p>
            </div>

            @if(session('error'))
                <div class="bg-[#ec1d1d] text-white p-4 rounded-lg mb-4">
                    {{ session('error') }}
                </div>
            @endif

            @if($errors->any())
                <div class="bg-[#ec1d1d] text-white p-4 rounded-lg mb-4">
                    <ul class="list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white rounded-lg shadow-md p-4 sm:p-6">
                <form action="{{ route('karyawan.cuti.store') }}" method="POST">
                    @csrf

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Jenis Cuti</label>
                            <input type="text" value="Cuti Tahunan (12 hari)" disabled
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-100">
                            <input type="hidden" name="jenis_cuti" value="Cuti Tahunan">
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Tanggal Mulai <span class="text-[#ec1d1d]">*</span></label>
                            <input type="date" name="tanggal_mulai" value="{{ old('tanggal_mulai') }}" required
                                   min="{{ date('Y-m-d') }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                            @error('tanggal_mulai') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Tanggal Selesai <span class="text-[#ec1d1d]">*</span></label>
                            <input type="date" name="tanggal_selesai" value="{{ old('tanggal_selesai') }}" required
                                   min="{{ date('Y-m-d') }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                            @error('tanggal_selesai') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                        </div>

                        <div class="mb-4 sm:col-span-2">
                            <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Keterangan <span class="text-[#ec1d1d]">*</span></label>
                            <textarea name="keterangan" rows="4" required
                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">{{ old('keterangan') }}</textarea>
                            @error('keterangan') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="mt-6 flex flex-wrap gap-4">
                        <button type="submit"
                                class="bg-[#27438D] text-white px-6 py-2 rounded-lg hover:bg-[#161758] transition-colors">
                            Ajukan Cuti
                        </button>
                        <a href="{{ route('karyawan.cuti.dashboard') }}"
                           class="bg-gray-500 text-white px-6 py-2 rounded-lg hover:bg-gray-600 transition-colors">
                            Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
