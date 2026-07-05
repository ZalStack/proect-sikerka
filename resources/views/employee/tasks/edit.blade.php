@extends('layouts.app')

@section('title', 'Edit Tugas - E-Learning')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-3xl">
    <div class="mb-6">
        <a href="{{ route('employee.tasks.index') }}" class="text-blue-600 hover:text-blue-800 transition">
            <i class="fas fa-arrow-left mr-2"></i> Kembali
        </a>
        <h1 class="text-3xl font-bold text-gray-800 mt-2">Edit Tugas</h1>
        <p class="text-gray-600 mt-1">Perbarui tugas yang telah dikirim</p>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <form action="{{ route('employee.tasks.update', $task) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="space-y-6">
                <!-- Current Video -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Video Saat Ini</label>
                    <div class="flex items-center space-x-3 p-3 bg-gray-50 rounded-lg border border-gray-200">
                        <i class="fas fa-video text-orange-500 text-2xl"></i>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-700">Video saat ini</p>
                            <p class="text-xs text-gray-500">{{ number_format($task->video_size / 1024, 1) }} KB</p>
                        </div>
                        <a href="{{ asset('storage/' . $task->video_path) }}"
                           target="_blank"
                           class="text-blue-600 hover:text-blue-800 text-sm transition">
                            <i class="fas fa-eye mr-1"></i> Lihat
                        </a>
                    </div>
                    <p class="mt-1 text-xs text-gray-500">Upload video baru jika ingin mengganti</p>
                </div>

                <!-- Video Upload -->
                <div>
                    <label for="video" class="block text-sm font-medium text-gray-700 mb-1">Video Baru (Opsional)</label>
                    <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg hover:border-blue-400 transition cursor-pointer">
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
                    <label for="comment" class="block text-sm font-medium text-gray-700 mb-1">Catatan</label>
                    <textarea id="comment"
                              name="comment"
                              rows="4"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('comment', $task->comment) }}</textarea>
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
                    <i class="fas fa-save mr-2"></i> Update
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
