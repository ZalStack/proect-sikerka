@extends('layouts.app')

@section('title', 'Upload Tugas - E-Learning')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-3xl">
    <div class="mb-6">
        <a href="{{ route('employee.tasks.index') }}" class="text-blue-600 hover:text-blue-800 transition">
            <i class="fas fa-arrow-left mr-2"></i> Kembali
        </a>
        <h1 class="text-3xl font-bold text-gray-800 mt-2">Upload Tugas</h1>
        <p class="text-gray-600 mt-1">Materi: {{ $material->title }}</p>
    </div>

    @if($existingTask && $existingTask->status != 'rejected')
        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-triangle text-yellow-400"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-yellow-700">
                        Anda sudah mengirimkan tugas untuk materi ini.
                        Status: <strong>{{ ucfirst($existingTask->status) }}</strong>
                    </p>
                    @if($existingTask->status == 'submitted' || $existingTask->status == 'graded')
                        <p class="text-sm text-yellow-700 mt-1">
                            Anda tidak dapat mengupload ulang tugas yang sudah dikirim.
                        </p>
                    @endif
                </div>
            </div>
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        @if($existingTask && $existingTask->status == 'rejected')
            <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-times-circle text-red-400"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-red-700">
                            Tugas Anda ditolak. Silakan upload ulang dengan perbaikan.
                        </p>
                        @if($existingTask->comment)
                            <p class="text-sm text-red-700 mt-1">
                                Catatan: {{ $existingTask->comment }}
                            </p>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        <form action="{{ route('employee.tasks.store', $material) }}"
              method="POST"
              enctype="multipart/form-data">
            @csrf

            <div class="space-y-6">
                <!-- Video Upload -->
                <div>
                    <label for="video" class="block text-sm font-medium text-gray-700 mb-1">
                        Video Tugas *
                    </label>
                    <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg hover:border-blue-400 transition cursor-pointer"
                         id="dropzone">
                        <div class="space-y-1 text-center">
                            <i class="fas fa-cloud-upload-alt text-4xl text-gray-400"></i>
                            <div class="flex text-sm text-gray-600">
                                <label for="video" class="relative cursor-pointer rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                                    <span>Upload video</span>
                                    <input id="video" name="video" type="file" accept="video/*" class="sr-only" onchange="previewVideo(this)">
                                </label>
                                <p class="pl-1">atau drag and drop</p>
                            </div>
                            <p class="text-xs text-gray-500">MP4, AVI, MOV, WMV, FLV up to 2MB</p>
                            <div id="videoPreview" class="hidden mt-4">
                                <video controls class="max-h-64 rounded-lg shadow-sm">
                                    <source id="videoSource" src="">
                                    Browser Anda tidak mendukung video.
                                </video>
                                <p id="videoName" class="text-sm text-gray-600 mt-2"></p>
                            </div>
                        </div>
                    </div>
                    @error('video')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Comment -->
                <div>
                    <label for="comment" class="block text-sm font-medium text-gray-700 mb-1">
                        Catatan (Opsional)
                    </label>
                    <textarea id="comment"
                              name="comment"
                              rows="4"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                              placeholder="Tambahkan catatan untuk tugas Anda...">{{ old('comment', $existingTask->comment ?? '') }}</textarea>
                    @error('comment')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mt-8 flex justify-end space-x-3">
                <a href="{{ route('employee.tasks.index') }}"
                   class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                    Batal
                </a>
                <button type="submit"
                        class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                    <i class="fas fa-upload mr-2"></i> Kirim Tugas
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function previewVideo(input) {
        const preview = document.getElementById('videoPreview');
        const videoSource = document.getElementById('videoSource');
        const videoName = document.getElementById('videoName');

        if (input.files && input.files[0]) {
            const file = input.files[0];
            const reader = new FileReader();

            reader.onload = function(e) {
                videoSource.src = e.target.result;
                preview.classList.remove('hidden');
                videoName.textContent = file.name + ' (' + (file.size / 1024).toFixed(1) + ' KB)';
            }

            reader.readAsDataURL(file);
        }
    }
</script>
@endpush
@endsection
