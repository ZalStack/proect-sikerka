@extends('layouts.app')

@section('title', 'Quiz - E-Learning')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800">Quiz</h1>
        <p class="text-gray-600 mt-1">Kerjakan quiz untuk menguji pemahaman Anda</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($quizzes as $quiz)
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 hover:shadow-md transition overflow-hidden">
            <div class="p-6">
                <div class="flex items-start justify-between mb-3">
                    <h3 class="font-semibold text-gray-800 text-lg">{{ $quiz->title }}</h3>
                    <span class="px-2 py-1 text-xs rounded-full
                        @if($quiz->attempt && $quiz->attempt->status == 'completed') bg-green-100 text-green-600
                        @elseif($quiz->attempt && $quiz->attempt->status == 'in_progress') bg-yellow-100 text-yellow-600
                        @else bg-blue-100 text-blue-600 @endif">
                        @if($quiz->attempt && $quiz->attempt->status == 'completed') Selesai
                        @elseif($quiz->attempt && $quiz->attempt->status == 'in_progress') Sedang Dikerjakan
                        @else Belum Dikerjakan @endif
                    </span>
                </div>

                <p class="text-sm text-gray-600 mb-3">{{ $quiz->description ?? 'Tidak ada deskripsi' }}</p>

                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Soal</span>
                        <span class="font-medium">{{ $quiz->questions->count() }} pertanyaan</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Durasi</span>
                        <span class="font-medium">{{ $quiz->duration_minutes }} menit</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Nilai Minimal</span>
                        <span class="font-medium">{{ $quiz->passing_score }}%</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Periode</span>
                        <span class="font-medium text-xs">
                            {{ $quiz->start_date->format('d M Y H:i') }} -
                            {{ $quiz->end_date->format('d M Y H:i') }}
                        </span>
                    </div>
                </div>

                <div class="mt-4 pt-3 border-t border-gray-100">
                    @if($quiz->attempt && $quiz->attempt->status == 'completed')
                        <a href="{{ route('employee.quiz.result', $quiz->attempt) }}"
                           class="w-full block text-center bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition text-sm font-medium">
                            <i class="fas fa-chart-bar mr-1"></i> Lihat Hasil
                        </a>
                    @elseif($quiz->attempt && $quiz->attempt->status == 'in_progress')
                        <a href="{{ route('employee.quiz.continue', $quiz->attempt) }}"
                           class="w-full block text-center bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-lg transition text-sm font-medium">
                            <i class="fas fa-play mr-1"></i> Lanjutkan
                        </a>
                    @else
                        @if($quiz->start_date <= now() && $quiz->end_date >= now())
                            <a href="{{ route('employee.quiz.show', $quiz) }}"
                               class="w-full block text-center bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition text-sm font-medium">
                                <i class="fas fa-play mr-1"></i> Mulai Quiz
                            </a>
                        @elseif($quiz->start_date > now())
                            <span class="w-full block text-center bg-gray-300 text-gray-600 px-4 py-2 rounded-lg text-sm font-medium cursor-not-allowed">
                                <i class="fas fa-clock mr-1"></i> Belum Tersedia
                            </span>
                        @else
                            <span class="w-full block text-center bg-red-100 text-red-600 px-4 py-2 rounded-lg text-sm font-medium cursor-not-allowed">
                                <i class="fas fa-times mr-1"></i> Telah Berakhir
                            </span>
                        @endif
                    @endif
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-full text-center py-12">
            <i class="fas fa-clipboard-list text-6xl text-gray-300 block mb-4"></i>
            <h3 class="text-xl font-semibold text-gray-600">Belum Ada Quiz</h3>
            <p class="text-gray-400 mt-1">Quiz akan segera tersedia</p>
        </div>
        @endforelse
    </div>

    <div class="mt-8">
        {{ $quizzes->links() }}
    </div>
</div>
@endsection
