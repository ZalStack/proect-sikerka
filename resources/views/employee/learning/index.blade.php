@extends('layouts.app')

@section('title', 'Materi Pembelajaran - E-Learning')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800">Materi Pembelajaran</h1>
        <p class="text-gray-600 mt-1">Pelajari materi yang tersedia dan tingkatkan pengetahuan Anda</p>
    </div>

    <!-- Search -->
    <div class="mb-6">
        <div class="relative max-w-md">
            <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
            <input type="text"
                   id="searchMaterial"
                   placeholder="Cari materi..."
                   class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
    </div>

    <!-- Materials Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($materials as $material)
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 hover:shadow-md transition overflow-hidden">
            <!-- Thumbnail -->
            <div class="h-48 bg-gradient-to-br from-blue-400 to-indigo-500 relative flex items-center justify-center">
                @if($material->file_type && str_contains($material->file_type, 'image'))
                    <img src="{{ asset('storage/' . $material->file_path) }}"
                         alt="{{ $material->title }}"
                         class="w-full h-full object-cover">
                @elseif($material->file_type && str_contains($material->file_type, 'video'))
                    <i class="fas fa-video text-white text-6xl opacity-50"></i>
                @else
                    <i class="fas fa-file-alt text-white text-6xl opacity-50"></i>
                @endif
                <span class="absolute bottom-2 right-2 bg-black/50 text-white text-xs px-2 py-1 rounded">
                    {{ $material->file_type ? strtoupper(explode('/', $material->file_type)[1] ?? 'FILE') : 'FILE' }}
                </span>
            </div>

            <!-- Content -->
            <div class="p-4">
                <h3 class="font-semibold text-gray-800 mb-1 line-clamp-1">{{ $material->title }}</h3>
                <p class="text-sm text-gray-600 line-clamp-2 mb-3">{{ $material->description }}</p>

                <div class="flex items-center text-xs text-gray-500 mb-3">
                    <i class="fas fa-user mr-1"></i>
                    <span>{{ $material->creator->name ?? 'Unknown' }}</span>
                    <span class="mx-2">•</span>
                    <i class="fas fa-calendar mr-1"></i>
                    <span>{{ $material->created_at->format('d M Y') }}</span>
                </div>

                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        @if($material->quiz)
                            <span class="text-xs px-2 py-1 bg-purple-100 text-purple-600 rounded-full">
                                <i class="fas fa-question-circle mr-1"></i> Ada Quiz
                            </span>
                        @endif
                        @if($material->tasks->count() > 0)
                            <span class="text-xs px-2 py-1 bg-orange-100 text-orange-600 rounded-full">
                                <i class="fas fa-tasks mr-1"></i> Tugas
                            </span>
                        @endif
                    </div>

                    @if($material->enrolled)
                        @if($material->enrolled->status == 'completed')
                            <span class="text-xs px-3 py-1 bg-green-100 text-green-600 rounded-full font-medium">
                                <i class="fas fa-check mr-1"></i> Selesai
                            </span>
                        @else
                            <span class="text-xs px-3 py-1 bg-blue-100 text-blue-600 rounded-full font-medium">
                                {{ $material->enrolled->progress }}% Selesai
                            </span>
                        @endif
                    @endif
                </div>

                <div class="mt-4 pt-3 border-t border-gray-100">
                    <a href="{{ route('employee.learning.show', $material) }}"
                       class="w-full block text-center bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition text-sm font-medium">
                        @if($material->enrolled)
                            Lanjutkan Belajar
                        @else
                            Mulai Belajar
                        @endif
                        <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-full text-center py-12">
            <i class="fas fa-book-open text-6xl text-gray-300 block mb-4"></i>
            <h3 class="text-xl font-semibold text-gray-600">Belum Ada Materi</h3>
            <p class="text-gray-400 mt-1">Materi pembelajaran akan segera tersedia</p>
        </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="mt-8">
        {{ $materials->links() }}
    </div>
</div>
@endsection
