{{-- views/hr/pengumuman/edit.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="flex min-h">
    @include('layouts.sidebar')
    <div class="flex-1 transition-all duration-300 md:ml-64 pt-6">
        <div class="p-4 sm:p-6">
            <div class="mb-6">
                <h1 class="text-xl sm:text-2xl font-bold font-['Montserrat'] text-[#161758]">Edit Pengumuman</h1>
                <p class="text-sm sm:text-base text-[#27438D]">Update pengumuman</p>
            </div>

            @if($errors->any())
                <div class="bg-[#ec1d1d] text-white p-3 sm:p-4 rounded-lg mb-4 text-sm">
                    <ul class="list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('hr.pengumuman.update', $pengumuman->id) }}" method="POST" enctype="multipart/form-data" class="bg-white rounded-2xl shadow-sm border border-slate-100 hover:shadow-lg transition-all duration-300 p-4 sm:p-6">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <div class="mb-4">
                            <label class="block text-xs sm:text-sm font-medium text-[#1B1B1B] mb-1">Judul Pengumuman <span class="text-[#ec1d1d]">*</span></label>
                            <input type="text" name="judul" value="{{ old('judul', $pengumuman->judul) }}" required
                                   class="w-full px-3 sm:px-4 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                            @error('judul') <p class="mt-1 text-xs sm:text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                        </div>

                        <div class="mb-4">
                            <label class="block text-xs sm:text-sm font-medium text-[#1B1B1B] mb-1">Isi Pengumuman <span class="text-[#ec1d1d]">*</span></label>
                            <textarea name="isi" rows="6" required
                                      class="w-full px-3 sm:px-4 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">{{ old('isi', $pengumuman->isi) }}</textarea>
                            @error('isi') <p class="mt-1 text-xs sm:text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <!-- Target Penerima -->
                    <div class="md:col-span-2">
                        <label class="block text-xs sm:text-sm font-medium text-[#1B1B1B] mb-1">Target Penerima <span class="text-[#ec1d1d]">*</span></label>
                        <select name="target" id="targetSelect" required
                                class="w-full px-3 sm:px-4 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                            <option value="semua" {{ old('target', $pengumuman->target) === 'semua' ? 'selected' : '' }}>📢 Semua Karyawan</option>
                            <option value="spesifik" {{ old('target', $pengumuman->target) === 'spesifik' ? 'selected' : '' }}>🎯 Karyawan Spesifik</option>
                        </select>
                        @error('target') <p class="mt-1 text-xs sm:text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                    </div>

                    <!-- Pilih Karyawan Spesifik -->
                    <div class="md:col-span-2" id="karyawanSelectWrapper" style="display: {{ old('target', $pengumuman->target) === 'spesifik' ? 'block' : 'none' }};">
                        <div class="mb-4">
                            @php
                                $selectedKaryawanIds = old('target_karyawan', is_array($pengumuman->target_karyawan_ids) ? $pengumuman->target_karyawan_ids : []);
                            @endphp
                            <div class="flex items-center justify-between mb-1.5 flex-wrap gap-2">
                                <label class="block text-xs sm:text-sm font-medium text-[#1B1B1B]">
                                    Pilih Karyawan <span class="text-[#ec1d1d]">*</span>
                                </label>
                                <div class="flex items-center gap-2">
                                    <button type="button" id="selectAllKaryawan"
                                        class="text-[10px] sm:text-xs font-medium text-[#00a2e9] hover:underline">Pilih Semua</button>
                                    <span class="text-gray-300 text-[10px]">|</span>
                                    <button type="button" id="clearAllKaryawan"
                                        class="text-[10px] sm:text-xs font-medium text-gray-500 hover:underline">Hapus Semua</button>
                                </div>
                            </div>

                            <input type="text" id="karyawanSearch" placeholder="🔍 Cari nama karyawan..."
                                class="w-full px-3 py-2 mb-2 text-xs sm:text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">

                            <div id="karyawanCheckboxList"
                                class="border border-gray-300 rounded-lg max-h-[220px] overflow-y-auto p-2 space-y-0.5 bg-white">
                                @forelse($karyawan as $k)
                                    <label class="karyawan-item flex items-center gap-2 px-2 py-1.5 rounded-md hover:bg-[#f0f9ff] cursor-pointer text-xs sm:text-sm"
                                        data-name="{{ strtolower($k->nama_lengkap) }}">
                                        <input type="checkbox" name="target_karyawan[]" value="{{ $k->id }}"
                                            class="karyawan-checkbox w-4 h-4 rounded border-gray-300 text-[#00a2e9] focus:ring-[#00a2e9] flex-shrink-0"
                                            {{ in_array($k->id, $selectedKaryawanIds) ? 'checked' : '' }}>
                                        <span class="flex-1 text-[#1B1B1B]">
                                            {{ $k->nama_lengkap }}
                                            @if($k->divisi)
                                                <span class="text-gray-400">({{ $k->divisi }})</span>
                                            @endif
                                            @if($k->posisi)
                                                <span class="text-gray-400">- {{ $k->posisi }}</span>
                                            @endif
                                        </span>
                                    </label>
                                @empty
                                    <p class="text-xs text-gray-400 px-2 py-1.5">Tidak ada data karyawan aktif</p>
                                @endforelse
                            </div>
                            <p id="karyawanNoResult" class="text-xs text-gray-400 px-1 py-1.5 hidden">Tidak ada karyawan yang cocok dengan pencarian</p>

                            <div id="selectedKaryawanDisplay" class="mt-2 flex flex-wrap gap-1.5">
                                <!-- Akan diisi oleh JavaScript -->
                            </div>
                            <p class="text-[10px] sm:text-xs text-gray-500 mt-1">
                                ✅ <span id="selectedCount">0</span> karyawan dipilih
                            </p>
                            @error('target_karyawan')
                                <p class="mt-1 text-xs sm:text-sm text-[#ec1d1d]">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs sm:text-sm font-medium text-[#1B1B1B] mb-1">Gambar</label>
                        <input type="file" name="gambar" accept="image/*"
                               class="w-full px-3 sm:px-4 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                        @if($pengumuman->gambar)
                            <div class="mt-2">
                                <img src="{{ Storage::url($pengumuman->gambar) }}" alt="Gambar" class="w-24 h-24 sm:w-32 sm:h-32 object-cover rounded-lg">
                            </div>
                        @endif
                        @error('gambar') <p class="mt-1 text-xs sm:text-sm text-[#ec1d1d]">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="mt-6 flex flex-wrap gap-3 sm:gap-4">
                    <button type="submit"
                            class="w-full sm:w-auto bg-[#27438D] text-white px-4 sm:px-6 py-2 rounded-lg hover:bg-[#161758] transition-colors duration-200 text-sm sm:text-base">
                        Update
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

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const targetSelect = document.getElementById('targetSelect');
        const karyawanWrapper = document.getElementById('karyawanSelectWrapper');
        const selectedDisplay = document.getElementById('selectedKaryawanDisplay');
        const selectedCount = document.getElementById('selectedCount');
        const selectAllBtn = document.getElementById('selectAllKaryawan');
        const clearAllBtn = document.getElementById('clearAllKaryawan');
        const karyawanSearch = document.getElementById('karyawanSearch');
        const karyawanNoResult = document.getElementById('karyawanNoResult');

        function toggleKaryawanSelect() {
            if (targetSelect && targetSelect.value === 'spesifik') {
                karyawanWrapper.style.display = 'block';
            } else if (karyawanWrapper) {
                karyawanWrapper.style.display = 'none';
            }
        }

        if (targetSelect && karyawanWrapper) {
            targetSelect.addEventListener('change', toggleKaryawanSelect);
            toggleKaryawanSelect();
        }

        // Update selected karyawan display + counter (safe if list is empty)
        function updateSelectedKaryawan() {
            if (!selectedDisplay || !selectedCount) return;
            const checked = document.querySelectorAll('.karyawan-checkbox:checked');
            selectedCount.textContent = checked.length;

            if (checked.length === 0) {
                selectedDisplay.innerHTML = '<span class="text-xs text-gray-400">Belum ada karyawan dipilih</span>';
                return;
            }

            let html = '';
            checked.forEach(function (cb) {
                const labelEl = cb.closest('.karyawan-item');
                const name = labelEl ? labelEl.querySelector('span').textContent.trim() : '';
                html += `<span class="text-xs bg-purple-100 text-purple-800 px-2 py-0.5 rounded-full">${name}</span>`;
            });
            selectedDisplay.innerHTML = html;
        }

        // Bind change listener to every checkbox
        document.querySelectorAll('.karyawan-checkbox').forEach(function (cb) {
            cb.addEventListener('change', updateSelectedKaryawan);
        });

        // Pilih Semua / Hapus Semua (hanya yang sedang tampil saat dicari)
        if (selectAllBtn) {
            selectAllBtn.addEventListener('click', function () {
                document.querySelectorAll('.karyawan-item').forEach(function (item) {
                    if (item.style.display !== 'none') {
                        const cb = item.querySelector('.karyawan-checkbox');
                        if (cb) cb.checked = true;
                    }
                });
                updateSelectedKaryawan();
            });
        }

        if (clearAllBtn) {
            clearAllBtn.addEventListener('click', function () {
                document.querySelectorAll('.karyawan-checkbox').forEach(function (cb) {
                    cb.checked = false;
                });
                updateSelectedKaryawan();
            });
        }

        // Pencarian nama karyawan
        if (karyawanSearch) {
            karyawanSearch.addEventListener('input', function () {
                const term = this.value.trim().toLowerCase();
                let visibleCount = 0;
                document.querySelectorAll('.karyawan-item').forEach(function (item) {
                    const match = item.dataset.name && item.dataset.name.includes(term);
                    item.style.display = match ? 'flex' : 'none';
                    if (match) visibleCount++;
                });
                if (karyawanNoResult) {
                    karyawanNoResult.classList.toggle('hidden', visibleCount !== 0);
                }
            });
        }

        // Initial update
        updateSelectedKaryawan();
    });
</script>
@endsection
