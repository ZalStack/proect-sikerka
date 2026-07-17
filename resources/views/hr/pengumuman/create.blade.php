@extends('layouts.app')

@section('content')
    <div class="flex">
        @include('layouts.sidebar')
        <div class="ml-64 flex-1 p-6">
            <div class="mb-6">
                <h1 class="text-2xl font-bold font-['Montserrat'] text-[#161758]">Buat Pengumuman</h1>
                <p class="text-[#27438D]">Buat pengumuman baru untuk karyawan</p>
            </div>

            @if (session('error'))
                <div class="bg-[#ec1d1d] text-white p-4 rounded-lg mb-4">
                    {{ session('error') }}
                </div>
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

            <form action="{{ route('hr.pengumuman.store') }}" method="POST" enctype="multipart/form-data"
                class="bg-white rounded-lg shadow-md p-6">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Judul - Full Width -->
                    <div class="md:col-span-2">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-[#1B1B1B] mb-1">
                                Judul Pengumuman <span class="text-[#ec1d1d]">*</span>
                            </label>
                            <input type="text" name="judul" value="{{ old('judul') }}" required
                                placeholder="Masukkan judul pengumuman"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                            @error('judul')
                                <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Isi Pengumuman - Full Width -->
                    <div class="md:col-span-2">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-[#1B1B1B] mb-1">
                                Isi Pengumuman <span class="text-[#ec1d1d]">*</span>
                            </label>
                            <textarea name="isi" rows="6" required placeholder="Tulis isi pengumuman di sini..."
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">{{ old('isi') }}</textarea>
                            @error('isi')
                                <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Target Penerima -->
                    <div>
                        <label class="block text-sm font-medium text-[#1B1B1B] mb-1">
                            Target Penerima <span class="text-[#ec1d1d]">*</span>
                        </label>
                        <select name="target" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                            <option value="semua" {{ old('target') === 'semua' ? 'selected' : '' }}>📢 Semua Karyawan
                            </option>
                            <option value="hr" {{ old('target') === 'hr' ? 'selected' : '' }}>👔 HR</option>
                            <option value="karyawan" {{ old('target') === 'karyawan' ? 'selected' : '' }}>👤 Karyawan
                            </option>
                        </select>
                        @error('target')
                            <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Upload Gambar -->
                    <div>
                        <label class="block text-sm font-medium text-[#1B1B1B] mb-1">
                            Gambar (Opsional)
                        </label>
                        <input type="file" name="gambar" accept="image/*"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                        <p class="text-xs text-gray-500 mt-1">📷 Format: JPG, PNG, GIF | Maks: 2MB</p>
                        @error('gambar')
                            <p class="mt-1 text-sm text-[#ec1d1d]">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Kirim WhatsApp - Full Width -->
                    <div class="md:col-span-2">
                        <div class="bg-[#F5F5F5] rounded-lg p-4 border border-gray-200">
                            <div class="flex items-center justify-between">
                                <div>
                                    <label class="block text-sm font-medium text-[#1B1B1B]">
                                        Kirim ke WhatsApp
                                    </label>
                                    <p class="text-xs text-gray-500 mt-1">
                                        Pengumuman akan dikirim melalui WhatsApp ke kontak yang dipilih
                                    </p>
                                </div>
                                <div class="flex items-center space-x-3">
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" name="send_whatsapp" class="sr-only peer"
                                            {{ old('send_whatsapp') ? 'checked' : '' }}>
                                        <div
                                            class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-[#25D366]/30 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#25D366]">
                                        </div>
                                        <span class="ml-3 text-sm font-semibold text-[#25D366]">
                                            <svg class="w-5 h-5 inline" fill="currentColor" viewBox="0 0 24 24">
                                                <path
                                                    d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z" />
                                            </svg>
                                            WhatsApp
                                        </span>
                                    </label>
                                </div>
                            </div>

                            <div class="mt-3 p-3 bg-white rounded-lg border border-gray-200">
                                <div class="flex items-center space-x-2">
                                    <svg class="w-5 h-5 text-[#25D366]" fill="currentColor" viewBox="0 0 24 24">
                                        <path
                                            d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z" />
                                    </svg>
                                    <span class="text-sm font-medium text-[#1B1B1B]">Nomor WhatsApp:</span>
                                    <span class="text-sm text-[#27438D] font-mono font-bold">+62 811-1912-340</span>
                                </div>
                                <p class="text-xs text-gray-500 mt-2">
                                    💡 Pengumuman akan disimpan terlebih dahulu, kemudian Anda akan diarahkan ke halaman
                                    pilih kontak/grup WhatsApp sebelum pesan dikirim
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Preview Pengumuman - Full Width -->
                    <div class="md:col-span-2">
                        <div class="border border-gray-200 rounded-lg p-4 bg-[#FAFAFA]">
                            <h3 class="text-sm font-semibold text-[#1B1B1B] mb-2">📋 Preview Pengumuman</h3>
                            <div class="bg-white rounded-lg p-4 border border-gray-200">
                                <div id="preview-container">
                                    <p class="text-gray-400 text-sm">Isi judul dan isi pengumuman untuk melihat preview</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex space-x-4">
                    <button type="submit"
                        class="bg-[#27438D] text-white px-6 py-2 rounded-lg hover:bg-[#161758] transition-colors duration-200">
                        💾 Simpan
                    </button>
                    <a href="{{ route('hr.pengumuman.index') }}"
                        class="bg-gray-500 text-white px-6 py-2 rounded-lg hover:bg-gray-600 transition-colors duration-200">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- JavaScript untuk Live Preview -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const judulInput = document.querySelector('input[name="judul"]');
            const isiTextarea = document.querySelector('textarea[name="isi"]');
            const previewContainer = document.getElementById('preview-container');

            function updatePreview() {
                const judul = judulInput.value || 'Judul Pengumuman';
                const isi = isiTextarea.value || 'Isi pengumuman akan tampil di sini...';

                previewContainer.innerHTML = `
            <div class="bg-[#F5F5F5] rounded-lg p-4">
                <h3 class="text-xl font-bold text-[#161758]">${judul}</h3>
                <p class="text-[#1B1B1B] mt-2 whitespace-pre-wrap">${isi}</p>
                <div class="mt-3 text-xs text-gray-500">
                    <p>📅 ${new Date().toLocaleDateString('id-ID')} ${new Date().toLocaleTimeString('id-ID', {hour:'2-digit', minute:'2-digit'})}</p>
                    <p>👤 HR Admin</p>
                </div>
            </div>
        `;
            }

            judulInput.addEventListener('input', updatePreview);
            isiTextarea.addEventListener('input', updatePreview);

            // Initial preview
            updatePreview();
        });
    </script>
@endsection
