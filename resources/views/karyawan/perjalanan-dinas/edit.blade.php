@extends('layouts.app')

@section('content')
<div class="flex min-h-screen">
    @include('layouts.sidebar')
    <div class="flex-1 transition-all duration-300 md:ml-64">
        <div class="p-4 sm:p-6 max-w-3xl mx-auto">
            <div class="mb-6">
                <a href="{{ route('karyawan.perjalanan-dinas.index') }}" class="text-[#00a2e9] hover:text-[#0088c4] flex items-center space-x-1 text-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    <span>Kembali</span>
                </a>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6">
                <h1 class="text-xl font-bold text-[#161758] mb-2">Edit Pengajuan Perjalanan Dinas</h1>
                <p class="text-sm text-gray-500 mb-6">Perbarui data pengajuan perjalanan dinas Anda</p>

                <form action="{{ route('karyawan.perjalanan-dinas.update', $perjalananDinas->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <!-- Judul -->
                    <div class="mb-4">
                        <label for="judul" class="block text-sm font-medium text-gray-700 mb-1.5">Judul Perjalanan Dinas <span class="text-red-500">*</span></label>
                        <input type="text" name="judul" id="judul"
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00a2e9] focus:border-transparent @error('judul') border-red-500 @enderror"
                               value="{{ old('judul', $perjalananDinas->judul) }}" required>
                        @error('judul')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Agenda -->
                    <div class="mb-4">
                        <label for="agenda" class="block text-sm font-medium text-gray-700 mb-1.5">Agenda <span class="text-red-500">*</span></label>
                        <textarea name="agenda" id="agenda" rows="4"
                                  class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00a2e9] focus:border-transparent @error('agenda') border-red-500 @enderror"
                                  required>{{ old('agenda', $perjalananDinas->agenda) }}</textarea>
                        @error('agenda')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Tanggal Mulai & Selesai -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label for="tanggal_mulai" class="block text-sm font-medium text-gray-700 mb-1.5">Tanggal Mulai <span class="text-red-500">*</span></label>
                            <input type="date" name="tanggal_mulai" id="tanggal_mulai"
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00a2e9] focus:border-transparent @error('tanggal_mulai') border-red-500 @enderror"
                                   value="{{ old('tanggal_mulai', $perjalananDinas->tanggal_mulai->format('Y-m-d')) }}" required>
                            @error('tanggal_mulai')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="tanggal_selesai" class="block text-sm font-medium text-gray-700 mb-1.5">Tanggal Selesai <span class="text-red-500">*</span></label>
                            <input type="date" name="tanggal_selesai" id="tanggal_selesai"
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00a2e9] focus:border-transparent @error('tanggal_selesai') border-red-500 @enderror"
                                   value="{{ old('tanggal_selesai', $perjalananDinas->tanggal_selesai->format('Y-m-d')) }}" required>
                            @error('tanggal_selesai')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Upload Surat Tugas -->
                    <div class="mb-6">
                        <label for="surat_tugas" class="block text-sm font-medium text-gray-700 mb-1.5">Surat Tugas (PDF, maks. 2MB)</label>
                        @if($perjalananDinas->surat_tugas)
                            <div class="mb-3 flex items-center space-x-2 text-sm">
                                <span class="text-gray-600">File saat ini:</span>
                                <a href="{{ route('karyawan.perjalanan-dinas.download', $perjalananDinas->id) }}"
                                   class="text-[#00a2e9] hover:underline flex items-center space-x-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    <span>Download</span>
                                </a>
                                <span class="text-xs text-gray-400">(Upload file baru untuk mengganti)</span>
                            </div>
                        @endif
                        <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-[#00a2e9] transition @error('surat_tugas') border-red-500 @enderror">
                            <input type="file" name="surat_tugas" id="surat_tugas" accept=".pdf" class="hidden">
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
                    <button type="submit" class="w-full px-6 py-3 bg-[#00a2e9] text-white rounded-lg hover:bg-[#0088c4] transition font-medium">
                        Update Pengajuan
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
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

document.getElementById('tanggal_mulai').addEventListener('change', function() {
    const selesai = document.getElementById('tanggal_selesai');
    if (selesai.value && selesai.value < this.value) {
        selesai.value = this.value;
    }
    selesai.min = this.value;
});
</script>
@endsection
