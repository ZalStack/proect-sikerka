@extends('layouts.app')

@section('title', 'Manajemen Quiz - E-Learning')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Manajemen Quiz</h1>
            <p class="text-gray-600 mt-1">Kelola quiz untuk setiap materi</p>
        </div>
        <a href="{{ route('admin.quizzes.create') }}"
           class="mt-4 md:mt-0 bg-purple-600 hover:bg-purple-700 text-white px-6 py-2 rounded-lg transition flex items-center">
            <i class="fas fa-plus mr-2"></i> Buat Quiz
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($quizzes as $quiz)
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 hover:shadow-md transition overflow-hidden">
            <div class="p-6">
                <div class="flex items-start justify-between mb-3">
                    <h3 class="font-semibold text-gray-800 text-lg">{{ $quiz->title }}</h3>
                    <span class="px-2 py-1 text-xs rounded-full
                        {{ $quiz->is_active ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600' }}">
                        {{ $quiz->is_active ? 'Aktif' : 'Nonaktif' }}
                    </span>
                </div>

                <p class="text-sm text-gray-600 mb-3 line-clamp-2">{{ $quiz->description ?? 'Tidak ada deskripsi' }}</p>

                <div class="text-sm text-gray-500 space-y-1">
                    <p><i class="fas fa-book mr-2"></i> {{ $quiz->material->title ?? 'Tidak ada materi' }}</p>
                    <p><i class="fas fa-question-circle mr-2"></i> {{ $quiz->questions->count() }} pertanyaan</p>
                    <p><i class="fas fa-clock mr-2"></i> {{ $quiz->duration_minutes }} menit</p>
                    <p><i class="fas fa-calendar mr-2"></i> {{ $quiz->start_date->format('d M Y') }} - {{ $quiz->end_date->format('d M Y') }}</p>
                </div>

                <div class="mt-4 pt-3 border-t border-gray-100 flex flex-wrap gap-2">
                    <a href="{{ route('admin.quizzes.edit', $quiz) }}"
                       class="flex-1 text-center bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded-lg transition text-sm">
                        <i class="fas fa-edit mr-1"></i> Edit
                    </a>
                    <a href="{{ route('admin.quizzes.questions', $quiz) }}"
                       class="flex-1 text-center bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded-lg transition text-sm">
                        <i class="fas fa-list mr-1"></i> Soal
                    </a>
                    <form action="{{ route('admin.quizzes.destroy', $quiz) }}"
                          method="POST"
                          class="flex-1"
                          onsubmit="return confirm('Yakin ingin menghapus quiz ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded-lg transition text-sm">
                            <i class="fas fa-trash mr-1"></i> Hapus
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-full text-center py-12">
            <i class="fas fa-clipboard-list text-6xl text-gray-300 block mb-4"></i>
            <h3 class="text-xl font-semibold text-gray-600">Belum Ada Quiz</h3>
            <p class="text-gray-400 mt-1">Buat quiz untuk materi pembelajaran</p>
            <a href="{{ route('admin.quizzes.create') }}" class="mt-4 inline-block bg-purple-600 hover:bg-purple-700 text-white px-6 py-2 rounded-lg transition">
                <i class="fas fa-plus mr-2"></i> Buat Quiz
            </a>
        </div>
        @endforelse
    </div>

    <div class="mt-8">
        {{ $quizzes->links() }}
    </div>
</div>
@endsection
