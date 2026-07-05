@extends('layouts.app')

@section('title', 'Edit Materi - E-Learning')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-3xl">
    <div class="mb-6">
        <a href="{{ route('admin.materials.index') }}" class="text-blue-600 hover:text-blue-800 transition">
            <i class="fas fa-arrow-left mr-2"></i> Kembali
        </a>
        <h1 class="text-3xl font-bold text-gray-800 mt-2">Edit Materi</h1>
        <p class="text-gray-600 mt-1">Perbarui data materi pembelajaran</p>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <form action="{{ route('admin.materials.update', $material) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="space-y-6">
                <!-- Title -->
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Judul Materi *</label>
                    <input type="text"
                           id="title"
                           name="title"
                           value="{{ old('title', $material->title) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('title') border-red-500 @enderror"
                           required>
                    @error('title')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Description -->
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Deskripsi *</label>
                    <textarea id="description"
                              name="description"
                              rows="5"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('description') border-red-500 @enderror"
                              required>{{ old('description', $material->description) }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Current File -->
                @if($material->file_path)
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">File Saat Ini</label>
                    <div class="flex items-center space-x-3 p-3 bg-gray-50 rounded-lg border border-gray-200">
                        <i class="fas {{
                            str_contains($material->file_type, 'pdf') ? 'fa-file-pdf text-red-500' :
                            (str_contains($material->file_type, 'image') ? 'fa-file-image text-green-500' :
                            (str_contains($material->file_type, 'video') ? 'fa-file-video text-purple-500' : 'fa-file text-blue-500'))
                        }} text-2xl"></i>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-700">File saat ini</p>
                            <p class="text-xs text-gray-500">{{ number_format($material->file_size / 1024, 1) }} KB</p>
                        </div>
                        <a href="{{ asset('storage/' . $material->file_path) }}"
                           target="_blank"
                           class="text-blue-600 hover:text-blue-800 text-sm transition">
                            <i class="fas fa-eye mr-1"></i> Lihat
                        </a>
                    </div>
                    <p class="mt-1 text-xs text-gray-500">Upload file baru jika ingin mengganti</p>
                </div>
                @endif

                <!-- File Upload -->
                <div>
                    <label for="file" class="block text-sm font-medium text-gray-700 mb-1">File Baru (Opsional)</label>
                    <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg hover:border-blue-400 transition cursor-pointer"
                         id="dropzone">
                        <div class="space-y-1 text-center">
                            <i class="fas fa-cloud-upload-alt text-4xl text-gray-400"></i>
                            <div class="flex text-sm text-gray-600">
                                <label for="file" class="relative cursor-pointer rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                                    <span>Upload file</span>
                                    <input id="file" name="file" type="file" class="sr-only" onchange="previewFile(this)">
                                </label>
                                <p class="pl-1">atau drag and drop</p>
                            </div>
                            <p class="text-xs text-gray-500">PDF, DOC, DOCX, JPG, PNG, MP4, AVI up to 2MB</p>
                            <div id="filePreview" class="hidden mt-4">
                                <div class="flex items-center justify-center space-x-3 p-3 bg-gray-50 rounded-lg">
                                    <i id="fileIcon" class="fas fa-file text-blue-600 text-2xl"></i>
                                    <div class="text-left">
                                        <p id="fileName" class="text-sm font-medium text-gray-700"></p>
                                        <p id="fileSize" class="text-xs text-gray-500"></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @error('file')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Order Number -->
                <div>
                    <label for="order_number" class="block text-sm font-medium text-gray-700 mb-1">Urutan</label>
                    <input type="number"
                           id="order_number"
                           name="order_number"
                           value="{{ old('order_number', $material->order_number) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <p class="mt-1 text-xs text-gray-500">Urutan tampilan materi (semakin kecil semakin atas)</p>
                </div>

                <!-- Status -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <div class="flex items-center space-x-4">
                        <label class="flex items-center">
                            <input type="radio" name="is_active" value="1" {{ old('is_active', $material->is_active) == '1' ? 'checked' : '' }}>
                            <span class="ml-2 text-sm text-gray-700">Aktif</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" name="is_active" value="0" {{ old('is_active', $material->is_active) == '0' ? 'checked' : '' }}>
                            <span class="ml-2 text-sm text-gray-700">Nonaktif</span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="mt-8 flex justify-end space-x-3">
                <a href="{{ route('admin.materials.index') }}"
                   class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                    Batal
                </a>
                <button type="submit"
                        class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                    <i class="fas fa-save mr-2"></i> Update
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function previewFile(input) {
        const preview = document.getElementById('filePreview');
        const fileName = document.getElementById('fileName');
        const fileSize = document.getElementById('fileSize');
        const fileIcon = document.getElementById('fileIcon');

        if (input.files && input.files[0]) {
            const file = input.files[0];

            const iconMap = {
                'pdf': 'fa-file-pdf',
                'doc': 'fa-file-word',
                'docx': 'fa-file-word',
                'jpg': 'fa-file-image',
                'jpeg': 'fa-file-image',
                'png': 'fa-file-image',
                'mp4': 'fa-file-video',
                'avi': 'fa-file-video',
                'mov': 'fa-file-video'
            };

            const ext = file.name.split('.').pop().toLowerCase();
            fileIcon.className = 'fas ' + (iconMap[ext] || 'fa-file') + ' text-blue-600 text-2xl';

            fileName.textContent = file.name;
            fileSize.textContent = (file.size / 1024).toFixed(1) + ' KB';
            preview.classList.remove('hidden');
        }
    }
</script>
@endpush
@endsection
