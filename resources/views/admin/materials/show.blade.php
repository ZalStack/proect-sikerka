@extends('layouts.app')

@section('title', $material->title . ' - E-Learning')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-4xl">
    <div class="mb-6">
        <a href="{{ route('admin.materials.index') }}" class="text-blue-600 hover:text-blue-800 transition">
            <i class="fas fa-arrow-left mr-2"></i> Kembali
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <!-- Header -->
        <div class="bg-gradient-to-r from-blue-600 to-indigo-600 p-6 text-white">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
                <div>
                    <h1 class="text-2xl font-bold">{{ $material->title }}</h1>
                    <p class="text-blue-100 mt-1">
                        <i class="fas fa-user mr-1"></i>
                        {{ $material->creator->name ?? 'Unknown' }}
                        <span class="mx-2">•</span>
                        {{ $material->created_at->format('d M Y H:i') }}
                    </p>
                </div>
                <div class="mt-4 md:mt-0 flex items-center space-x-2">
                    <span class="px-3 py-1 text-sm rounded-full
                        {{ $material->is_active ? 'bg-green-500/20 text-green-100' : 'bg-red-500/20 text-red-100' }}">
                        {{ $material->is_active ? 'Aktif' : 'Nonaktif' }}
                    </span>
                    <a href="{{ route('admin.materials.edit', $material) }}"
                       class="bg-white/20 hover:bg-white/30 text-white px-4 py-2 rounded-lg transition text-sm">
                        <i class="fas fa-edit mr-1"></i> Edit
                    </a>
                </div>
            </div>
        </div>

        <!-- Body -->
        <div class="p-6">
            <!-- Description -->
            <div class="mb-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-2">Deskripsi</h2>
                <p class="text-gray-600">{{ $material->description }}</p>
            </div>

            <!-- File Preview -->
            @if($material->file_path)
            <div class="mb-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-3">File Materi</h2>
                <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                    @if(str_contains($material->file_type, 'image'))
                        <div class="flex justify-center">
                            <img src="{{ asset('storage/' . $material->file_path) }}"
                                 alt="{{ $material->title }}"
                                 class="max-h-96 rounded-lg shadow-sm">
                        </div>
                    @elseif(str_contains($material->file_type, 'video'))
                        <video controls class="w-full rounded-lg shadow-sm">
                            <source src="{{ asset('storage/' . $material->file_path) }}"
                                    type="{{ $material->file_type }}">
                            Browser Anda tidak mendukung video.
                        </video>
                    @else
                        <div class="flex items-center justify-between p-4 bg-white rounded-lg border border-gray-200">
                            <div class="flex items-center space-x-3">
                                <i class="fas {{
                                    str_contains($material->file_type, 'pdf') ? 'fa-file-pdf text-red-500' :
                                    'fa-file-alt text-blue-500'
                                }} text-3xl"></i>
                                <div>
                                    <p class="font-medium text-gray-800">File Materi</p>
                                    <p class="text-sm text-gray-500">{{ number_format($material->file_size / 1024, 1) }} KB</p>
                                </div>
                            </div>
                            <a href="{{ asset('storage/' . $material->file_path) }}"
                               target="_blank"
                               class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition text-sm">
                                <i class="fas fa-eye mr-1"></i> Lihat File
                            </a>
                        </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- Quiz Information -->
            @if($material->quiz)
            <div class="mb-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-3">Quiz</h2>
                <div class="bg-green-50 rounded-lg p-4 border border-green-200">
                    <div class="flex items-center justify-between flex-wrap gap-2">
                        <div>
                            <p class="font-medium text-green-800">{{ $material->quiz->title }}</p>
                            <p class="text-sm text-green-600">
                                <i class="fas fa-question-circle mr-1"></i>
                                {{ $material->quiz->questions->count() }} pertanyaan
                            </p>
                            <p class="text-sm text-green-600">
                                <i class="fas fa-clock mr-1"></i>
                                {{ $material->quiz->duration_minutes }} menit
                            </p>
                        </div>
                        <a href="{{ route('admin.quizzes.edit', $material->quiz) }}"
                           class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition text-sm">
                            <i class="fas fa-edit mr-1"></i> Kelola Quiz
                        </a>
                    </div>
                </div>
            </div>
            @else
            <div class="mb-6">
                <div class="bg-yellow-50 rounded-lg p-4 border border-yellow-200 flex items-center justify-between flex-wrap gap-2">
                    <div>
                        <p class="text-yellow-800"><i class="fas fa-info-circle mr-1"></i> Belum ada quiz untuk materi ini</p>
                    </div>
                    <a href="{{ route('admin.quizzes.create', ['material_id' => $material->id]) }}"
                       class="bg-yellow-600 text-white px-4 py-2 rounded-lg hover:bg-yellow-700 transition text-sm">
                        <i class="fas fa-plus mr-1"></i> Buat Quiz
                    </a>
                </div>
            </div>
            @endif

            <!-- Task Submissions -->
            <div>
                <h2 class="text-lg font-semibold text-gray-800 mb-3">Tugas yang Dikirim</h2>
                @if($material->tasks->count() > 0)
                    <div class="space-y-3">
                        @foreach($material->tasks as $task)
                        <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                            <div class="flex items-center justify-between flex-wrap gap-2">
                                <div>
                                    <p class="font-medium text-gray-800">{{ $task->user->name ?? 'Unknown' }}</p>
                                    <p class="text-sm text-gray-500">
                                        <i class="fas fa-calendar mr-1"></i>
                                        {{ $task->submitted_at ? $task->submitted_at->format('d M Y H:i') : '-' }}
                                    </p>
                                    <p class="text-sm text-gray-500">
                                        <i class="fas fa-file-video mr-1"></i>
                                        {{ number_format($task->video_size / 1024, 1) }} KB
                                    </p>
                                    @if($task->comment)
                                        <p class="text-sm text-gray-600 mt-1">{{ $task->comment }}</p>
                                    @endif
                                </div>
                                <div class="flex items-center space-x-3">
                                    <span class="px-2 py-1 text-xs rounded-full
                                        @if($task->status == 'approved') bg-green-100 text-green-600
                                        @elseif($task->status == 'rejected') bg-red-100 text-red-600
                                        @elseif($task->status == 'graded') bg-blue-100 text-blue-600
                                        @else bg-yellow-100 text-yellow-600 @endif">
                                        {{ ucfirst($task->status) }}
                                    </span>
                                    @if($task->score !== null)
                                        <span class="font-semibold text-sm
                                            @if($task->score >= 70) text-green-600
                                            @elseif($task->score >= 50) text-yellow-600
                                            @else text-red-600 @endif">
                                            {{ $task->score }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500 text-sm">Belum ada tugas yang dikirim.</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
