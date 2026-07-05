@extends('layouts.app')

@section('content')
<div class="flex">
    @include('layouts.sidebar')
    <div class="ml-64 flex-1 p-6">
        <div class="mb-6">
            <h1 class="text-2xl font-bold font-['Montserrat'] text-[#161758]">Edit Pengumuman</h1>
            <p class="text-[#27438D]">Update pengumuman</p>
        </div>

        @if($errors->any())
            <div class="bg-[#ec1d1d] text-white p-4 rounded-lg mb-4">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('hr.pengumuman.update', $pengumuman->id) }}" method="POST" enctype="multipart/form-data" class="bg-white rounded-lg shadow-md p-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Judul Pengumuman <span class="text-[#ec1d1d]">*</span></label>
                        <input type="text" name="judul" value="{{ old('judul', $pengumuman->judul) }}" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                        @error('judul') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Isi Pengumuman <span class="text-[#ec1d1d]">*</span></label>
                        <textarea name="isi" rows="6" required
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">{{ old('isi', $pengumuman->isi) }}</textarea>
                        @error('isi') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Target Penerima <span class="text-[#ec1d1d]">*</span></label>
                    <select name="target" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                        <option value="semua" {{ old('target', $pengumuman->target) === 'semua' ? 'selected' : '' }}>Semua Karyawan</option>
                        <option value="hr" {{ old('target', $pengumuman->target) === 'hr' ? 'selected' : '' }}>HR</option>
                        <option value="karyawan" {{ old('target', $pengumuman->target) === 'karyawan' ? 'selected' : '' }}>Karyawan</option>
                    </select>
                    @error('target') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Gambar</label>
                    <input type="file" name="gambar" accept="image/*"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                    @if($pengumuman->gambar)
                        <div class="mt-2">
                            <img src="{{ Storage::url($pengumuman->gambar) }}" alt="Gambar" class="w-32 h-32 object-cover rounded-lg">
                        </div>
                    @endif
                    @error('gambar') <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="mt-6 flex space-x-4">
                <button type="submit"
                        class="bg-[#27438D] text-white px-6 py-2 rounded-lg hover:bg-[#161758] transition-colors duration-200">
                    Update
                </button>
                <a href="{{ route('hr.pengumuman.index') }}"
                   class="bg-gray-500 text-white px-6 py-2 rounded-lg hover:bg-gray-600 transition-colors duration-200">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
