@extends('layouts.app')

@section('content')
<div class="flex min-h-screen">
    @include('layouts.sidebar')
    <div class="flex-1 transition-all duration-300 md:ml-64 pt-6">
        <div class="p-4 sm:p-6">
            <div class="mb-6">
                <a href="{{ route('karyawan.perjalanan-dinas.index') }}" class="text-[#00a2e9] hover:text-[#0088c4] flex items-center space-x-1 text-sm mb-3">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    <span>Kembali</span>
                </a>
                <h1 class="text-xl sm:text-2xl font-bold font-['Montserrat'] text-[#161758]">Ajukan Perjalanan Dinas</h1>
                <p class="text-[#27438D] text-sm sm:text-base">Isi form untuk mengajukan perjalanan dinas (otomatis disetujui)</p>
            </div>

            <div class="bg-white rounded-lg shadow-md p-4 sm:p-6">

                <form action="{{ route('karyawan.perjalanan-dinas.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <!-- Judul -->
                    <div class="mb-4">
                        <label for="judul" class="block text-sm font-medium text-gray-700 mb-1.5">Judul Perjalanan Dinas <span class="text-red-500">*</span></label>
                        <input type="text" name="judul" id="judul"
                               class="w-full px-3 sm:px-4 py-2 sm:py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00a2e9] focus:border-transparent @error('judul') border-red-500 @enderror"
                               value="{{ old('judul') }}" required placeholder="Contoh: Rapat Koordinasi Tahunan">
                        @error('judul')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Agenda -->
                    <div class="mb-4">
                        <label for="agenda" class="block text-sm font-medium text-gray-700 mb-1.5">Agenda <span class="text-red-500">*</span></label>
                        <textarea name="agenda" id="agenda" rows="4"
                                  class="w-full px-3 sm:px-4 py-2 sm:py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00a2e9] focus:border-transparent @error('agenda') border-red-500 @enderror"
                                  required placeholder="Jelaskan agenda perjalanan dinas secara detail...">{{ old('agenda') }}</textarea>
                        @error('agenda')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Tanggal Mulai & Selesai dengan Flatpickr -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label for="tanggal_mulai" class="block text-sm font-medium text-gray-700 mb-1.5">Tanggal Mulai <span class="text-red-500">*</span></label>
                            <input type="text" name="tanggal_mulai" id="tanggal_mulai"
                                   class="w-full px-3 sm:px-4 py-2 sm:py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00a2e9] focus:border-transparent @error('tanggal_mulai') border-red-500 @enderror"
                                   value="{{ old('tanggal_mulai') }}" required placeholder="dd/mm/yyyy" autocomplete="off">
                            @error('tanggal_mulai')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                            <p class="text-xs text-gray-400 mt-1">Minimal 7 hari dari hari ini</p>
                        </div>
                        <div>
                            <label for="tanggal_selesai" class="block text-sm font-medium text-gray-700 mb-1.5">Tanggal Selesai <span class="text-red-500">*</span></label>
                            <input type="text" name="tanggal_selesai" id="tanggal_selesai"
                                   class="w-full px-3 sm:px-4 py-2 sm:py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00a2e9] focus:border-transparent @error('tanggal_selesai') border-red-500 @enderror"
                                   value="{{ old('tanggal_selesai') }}" required placeholder="dd/mm/yyyy" autocomplete="off">
                            @error('tanggal_selesai')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Upload Surat Tugas (wajib) -->
                    <div class="mb-6">
                        <label for="surat_tugas" class="block text-sm font-medium text-gray-700 mb-1.5">Surat Tugas (PDF, maks. 2MB) <span class="text-red-500">*</span></label>
                        <div class="border-2 border-dashed border-gray-300 rounded-lg p-4 sm:p-6 text-center hover:border-[#00a2e9] transition @error('surat_tugas') border-red-500 @enderror">
                            <input type="file" name="surat_tugas" id="surat_tugas" accept=".pdf" class="hidden" required>
                            <label for="surat_tugas" class="cursor-pointer">
                                <svg class="w-10 h-10 mx-auto text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                </svg>
                                <p class="text-sm text-gray-500 mt-2">Klik untuk upload atau drag & drop file PDF</p>
                                <p class="text-xs text-gray-400 mt-1">Maksimal ukuran file 2 MB</p>
                            </label>
                            <div id="fileInfo" class="hidden mt-3">
                                <span id="fileName" class="text-sm text-gray-700"></span>
                                <button type="button" onclick="removeFile()" class="text-red-500 text-sm ml-2">Hapus</button>
                            </div>
                        </div>
                        @error('surat_tugas')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Submit -->
                    <div class="mt-6 flex flex-wrap gap-4">
                        <button type="submit" class="w-full sm:w-auto bg-[#27438D] text-white px-6 py-2 rounded-lg hover:bg-[#161758] transition font-medium text-sm sm:text-base">
                            Ajukan Perjalanan Dinas
                        </button>
                        <a href="{{ route('karyawan.perjalanan-dinas.index') }}"
                           class="w-full sm:w-auto text-center bg-gray-500 text-white px-6 py-2 rounded-lg hover:bg-gray-600 transition-colors">
                            Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Flatpickr CDN -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/id.js"></script>

<script>
    // Inisialisasi flatpickr dengan format Y-m-d untuk value, tapi tampilan d/m/Y
    flatpickr.localize(flatpickr.l10ns.id);

    const minDate = new Date();
    minDate.setDate(minDate.getDate() + 7); // H-7

    // Mulai
    const startPicker = flatpickr("#tanggal_mulai", {
        dateFormat: "Y-m-d",           // format value yang dikirim
        altInput: true,
        altFormat: "d/m/Y",            // format tampilan
        minDate: minDate,
        disableMobile: true,
        onChange: function(selectedDates, dateStr, instance) {
            if (selectedDates.length > 0) {
                endPicker.set('minDate', selectedDates[0]);
            }
        }
    });

    // Selesai
    const endPicker = flatpickr("#tanggal_selesai", {
        dateFormat: "Y-m-d",
        altInput: true,
        altFormat: "d/m/Y",
        minDate: minDate,
        disableMobile: true,
    });

    // Validasi form sebelum submit
    document.querySelector('form').addEventListener('submit', function(e) {
        const startVal = document.getElementById('tanggal_mulai').value;
        const endVal = document.getElementById('tanggal_selesai').value;
        if (!startVal || !endVal) {
            alert('Tanggal mulai dan selesai harus diisi.');
            e.preventDefault();
            return;
        }
        // Karena format Y-m-d, bisa langsung bandingkan string
        if (startVal > endVal) {
            alert('Tanggal selesai harus setelah atau sama dengan tanggal mulai.');
            e.preventDefault();
            return;
        }
        // Cek minimal H-7 (sudah diatur di minDate, tapi amankan)
        const today = new Date();
        today.setHours(0,0,0,0);
        const minAllowed = new Date(today);
        minAllowed.setDate(minAllowed.getDate() + 7);
        const startDate = new Date(startVal);
        if (startDate < minAllowed) {
            alert('Tanggal mulai minimal 7 hari dari hari ini.');
            e.preventDefault();
            return;
        }
    });

    // File upload handler
    document.getElementById('surat_tugas').addEventListener('change', function(e) {
        const file = this.files[0];
        const fileInfo = document.getElementById('fileInfo');
        const fileName = document.getElementById('fileName');

        if (file) {
            if (file.size > 2 * 1024 * 1024) {
                alert('Ukuran file terlalu besar. Maksimal 2 MB.');
                this.value = '';
                fileInfo.classList.add('hidden');
                return;
            }
            if (file.type !== 'application/pdf') {
                alert('Hanya file PDF yang diperbolehkan.');
                this.value = '';
                fileInfo.classList.add('hidden');
                return;
            }
            fileName.textContent = file.name + ' (' + (file.size / 1024).toFixed(1) + ' KB)';
            fileInfo.classList.remove('hidden');
        } else {
            fileInfo.classList.add('hidden');
        }
    });

    function removeFile() {
        document.getElementById('surat_tugas').value = '';
        document.getElementById('fileInfo').classList.add('hidden');
    }
</script>
@endsection
