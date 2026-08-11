{{-- views/hr/pengumuman/create.blade.php --}}
@extends('layouts.app')

@section('content')
    <div class="flex min-h-screen">
        @include('layouts.sidebar')
        <div class="flex-1 transition-all duration-300 md:ml-64 pt-6">
            <div class="p-4 sm:p-6">
                <div class="mb-6">
                    <h1 class="text-xl sm:text-2xl font-bold font-['Montserrat'] text-[#161758]">Buat Pengumuman</h1>
                    <p class="text-sm sm:text-base text-[#27438D]">Buat pengumuman baru untuk karyawan</p>
                </div>

                @if (session('error'))
                    <div class="bg-[#ec1d1d] text-white p-3 sm:p-4 rounded-lg mb-4 text-sm">
                        {{ session('error') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="bg-[#ec1d1d] text-white p-3 sm:p-4 rounded-lg mb-4 text-sm">
                        <ul class="list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('hr.pengumuman.store') }}" method="POST" enctype="multipart/form-data"
                    class="bg-white rounded-lg shadow-md p-4 sm:p-6">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Judul - Full Width -->
                        <div class="md:col-span-2">
                            <div class="mb-4">
                                <label class="block text-xs sm:text-sm font-medium text-[#1B1B1B] mb-1">
                                    Judul Pengumuman <span class="text-[#ec1d1d]">*</span>
                                </label>
                                <input type="text" name="judul" value="{{ old('judul') }}" required
                                    placeholder="Masukkan judul pengumuman"
                                    class="w-full px-3 sm:px-4 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                                @error('judul')
                                    <p class="mt-1 text-xs sm:text-sm text-[#ec1d1d]">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Isi Pengumuman - Full Width -->
                        <div class="md:col-span-2">
                            <div class="mb-4">
                                <label class="block text-xs sm:text-sm font-medium text-[#1B1B1B] mb-1">
                                    Isi Pengumuman <span class="text-[#ec1d1d]">*</span>
                                </label>
                                <textarea name="isi" rows="6" required placeholder="Tulis isi pengumuman di sini..."
                                    class="w-full px-3 sm:px-4 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">{{ old('isi') }}</textarea>
                                @error('isi')
                                    <p class="mt-1 text-xs sm:text-sm text-[#ec1d1d]">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Target Penerima -->
                        <div class="md:col-span-2">
                            <label class="block text-xs sm:text-sm font-medium text-[#1B1B1B] mb-1">
                                Target Penerima <span class="text-[#ec1d1d]">*</span>
                            </label>
                            <select name="target" id="targetSelect" required
                                class="w-full px-3 sm:px-4 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                                <option value="semua" {{ old('target') === 'semua' ? 'selected' : '' }}>📢 Semua Karyawan</option>
                                <option value="spesifik" {{ old('target') === 'spesifik' ? 'selected' : '' }}>🎯 Karyawan Spesifik</option>
                            </select>
                            @error('target')
                                <p class="mt-1 text-xs sm:text-sm text-[#ec1d1d]">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Pilih Karyawan Spesifik -->
                        <div class="md:col-span-2" id="karyawanSelectWrapper" style="display: {{ old('target') === 'spesifik' ? 'block' : 'none' }};">
                            <div class="mb-4">
                                <label class="block text-xs sm:text-sm font-medium text-[#1B1B1B] mb-1">
                                    Pilih Karyawan <span class="text-[#ec1d1d]">*</span>
                                </label>
                                <select name="target_karyawan[]" id="targetKaryawan" multiple
                                    class="w-full px-3 sm:px-4 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9] min-h-[150px]">
                                    @foreach($karyawan as $k)
                                        <option value="{{ $k->id }}"
                                            {{ in_array($k->id, old('target_karyawan', [])) ? 'selected' : '' }}>
                                            {{ $k->nama_lengkap }}
                                            @if($k->divisi)
                                                ({{ $k->divisi }})
                                            @endif
                                            @if($k->posisi)
                                                - {{ $k->posisi }}
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                                <div id="selectedKaryawanDisplay" class="mt-2 flex flex-wrap gap-1.5">
                                    <!-- Akan diisi oleh JavaScript -->
                                </div>
                                <p class="text-[10px] sm:text-xs text-gray-500 mt-1">
                                    💡 Tekan <kbd class="px-1 py-0.5 bg-gray-100 rounded">Ctrl</kbd> + klik untuk memilih lebih dari satu
                                </p>
                                @error('target_karyawan')
                                    <p class="mt-1 text-xs sm:text-sm text-[#ec1d1d]">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Upload Gambar -->
                        <div>
                            <label class="block text-xs sm:text-sm font-medium text-[#1B1B1B] mb-1">
                                Gambar (Opsional)
                            </label>
                            <input type="file" name="gambar" accept="image/*"
                                class="w-full px-3 sm:px-4 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                            <p class="text-[10px] sm:text-xs text-gray-500 mt-1">📷 Format: JPG, PNG, GIF | Maks: 2MB</p>
                            @error('gambar')
                                <p class="mt-1 text-xs sm:text-sm text-[#ec1d1d]">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Preview Pengumuman - Full Width -->
                        <div class="md:col-span-2">
                            <div class="border border-gray-200 rounded-lg p-3 sm:p-4 bg-[#FAFAFA]">
                                <h3 class="text-xs sm:text-sm font-semibold text-[#1B1B1B] mb-2">📋 Preview Pengumuman</h3>
                                <div class="bg-white rounded-lg p-3 sm:p-4 border border-gray-200">
                                    <div id="preview-container">
                                        <p class="text-xs sm:text-sm text-gray-400">Isi judul dan isi pengumuman untuk melihat preview</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 flex flex-wrap gap-3 sm:gap-4">
                        <button type="submit"
                            class="w-full sm:w-auto bg-[#27438D] text-white px-4 sm:px-6 py-2 rounded-lg hover:bg-[#161758] transition-colors duration-200 text-sm sm:text-base">
                            💾 Simpan
                        </button>
                        <a href="{{ route('hr.pengumuman.index') }}"
                            class="w-full sm:w-auto text-center bg-gray-500 text-white px-4 sm:px-6 py-2 rounded-lg hover:bg-gray-600 transition-colors duration-200 text-sm sm:text-base">
                            Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- JavaScript untuk Live Preview, Toggle & Auto Display -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const judulInput = document.querySelector('input[name="judul"]');
            const isiTextarea = document.querySelector('textarea[name="isi"]');
            const previewContainer = document.getElementById('preview-container');
            const targetSelect = document.getElementById('targetSelect');
            const karyawanWrapper = document.getElementById('karyawanSelectWrapper');
            const targetKaryawan = document.getElementById('targetKaryawan');
            const selectedDisplay = document.getElementById('selectedKaryawanDisplay');

            // Toggle karyawan select
            function toggleKaryawanSelect() {
                if (targetSelect.value === 'spesifik') {
                    karyawanWrapper.style.display = 'block';
                } else {
                    karyawanWrapper.style.display = 'none';
                }
            }

            targetSelect.addEventListener('change', toggleKaryawanSelect);
            toggleKaryawanSelect();

            // Update selected karyawan display
            function updateSelectedKaryawan() {
                const selectedOptions = targetKaryawan.selectedOptions;
                if (selectedOptions.length === 0) {
                    selectedDisplay.innerHTML = '<span class="text-xs text-gray-400">Belum ada karyawan dipilih</span>';
                    return;
                }

                let html = '';
                for (let option of selectedOptions) {
                    html += `<span class="text-xs bg-purple-100 text-purple-800 px-2 py-0.5 rounded-full">${option.text}</span>`;
                }
                selectedDisplay.innerHTML = html;
            }

            // Update when selection changes
            targetKaryawan.addEventListener('change', updateSelectedKaryawan);

            // Initial update
            updateSelectedKaryawan();

            // Update preview
            function updatePreview() {
                const judul = judulInput.value || 'Judul Pengumuman';
                const isi = isiTextarea.value || 'Isi pengumuman akan tampil di sini...';

                previewContainer.innerHTML = `
                    <div class="bg-[#F5F5F5] rounded-lg p-3 sm:p-4">
                        <h3 class="text-base sm:text-xl font-bold text-[#161758]">${judul}</h3>
                        <p class="text-xs sm:text-sm text-[#1B1B1B] mt-2 whitespace-pre-wrap">${isi}</p>
                        <div class="mt-3 text-[10px] sm:text-xs text-gray-500">
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
