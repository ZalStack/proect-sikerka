@extends('layouts.app')

@section('title', 'Hasil Quiz - E-Learning')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-3xl">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8">
        <!-- Header -->
        <div class="text-center mb-8">
            <div class="w-24 h-24 rounded-full flex items-center justify-center mx-auto mb-4
                {{ $attempt->is_passed ? 'bg-green-100' : 'bg-red-100' }}">
                <i class="fas {{ $attempt->is_passed ? 'fa-check-circle text-green-600' : 'fa-times-circle text-red-600' }} text-4xl"></i>
            </div>
            <h1 class="text-3xl font-bold text-gray-800 mb-2">{{ $attempt->quiz->title }}</h1>
            <p class="text-gray-600">Hasil Quiz Anda</p>
        </div>

        <!-- Score -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div class="bg-gray-50 rounded-lg p-6 text-center">
                <p class="text-sm text-gray-500">Nilai Anda</p>
                <p class="text-4xl font-bold
                    {{ $attempt->score >= 70 ? 'text-green-600' : ($attempt->score >= 50 ? 'text-yellow-600' : 'text-red-600') }}">
                    {{ $attempt->score }}%
                </p>
                <span class="px-3 py-1 text-sm rounded-full inline-block mt-2
                    {{ $attempt->is_passed ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600' }}">
                    {{ $attempt->is_passed ? '✅ Lulus' : '❌ Tidak Lulus' }}
                </span>
            </div>
            <div class="bg-gray-50 rounded-lg p-6 text-center">
                <p class="text-sm text-gray-500">Statistik</p>
                <div class="space-y-2 mt-2">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Total Pertanyaan</span>
                        <span class="font-semibold">{{ $totalQuestions }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-green-600">Jawaban Benar</span>
                        <span class="font-semibold text-green-600">{{ $correctAnswers }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-red-600">Jawaban Salah</span>
                        <span class="font-semibold text-red-600">{{ $totalQuestions - $correctAnswers }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Durasi</span>
                        <span class="font-semibold">
                            {{ number_format($attempt->started_at->diffInMinutes($attempt->completed_at ?? now())) }} menit
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Answer Review -->
        @if($attempt->quiz->show_correct_answers)
        <div class="border-t border-gray-200 pt-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Review Jawaban</h2>

            @foreach($attempt->answers as $index => $answer)
            <div class="bg-gray-50 rounded-lg p-4 mb-3">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <div class="flex items-center space-x-2 mb-2">
                            <span class="w-6 h-6 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center text-xs font-semibold">
                                {{ $index + 1 }}
                            </span>
                            <span class="font-medium text-gray-800">{{ $answer->question->question_text }}</span>
                            @if($answer->is_correct)
                                <span class="text-green-600 text-sm"><i class="fas fa-check"></i></span>
                            @else
                                <span class="text-red-600 text-sm"><i class="fas fa-times"></i></span>
                            @endif
                        </div>

                        <div class="ml-8 space-y-1 text-sm">
                            <div class="flex items-center space-x-2">
                                <span class="text-gray-500">Jawaban Anda:</span>
                                @if($answer->selected_option)
                                    <span class="{{ $answer->is_correct ? 'text-green-600 font-medium' : 'text-red-600 font-medium' }}">
                                        {{ $answer->selected_option->option_text }}
                                    </span>
                                @else
                                    <span class="text-gray-500">{{ $answer->answer_text ?? 'Tidak dijawab' }}</span>
                                @endif
                            </div>

                            @if(!$answer->is_correct && $answer->question->correctOption)
                            <div class="flex items-center space-x-2">
                                <span class="text-gray-500">Jawaban Benar:</span>
                                <span class="text-green-600 font-medium">
                                    {{ $answer->question->correctOption->option_text }}
                                </span>
                            </div>
                            @endif
                        </div>
                    </div>
                    <span class="text-xs text-gray-500">{{ $answer->question->points }} poin</span>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="text-center py-6 text-gray-500 border-t border-gray-200">
            <i class="fas fa-lock text-2xl block mb-2"></i>
            <p>Jawaban benar/salah tidak ditampilkan.</p>
        </div>
        @endif

        <!-- Actions -->
        <div class="flex flex-wrap gap-3 mt-8 pt-6 border-t border-gray-200">
            <a href="{{ route('employee.quiz.index') }}"
               class="flex-1 min-w-[150px] text-center bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition">
                <i class="fas fa-list mr-1"></i> Daftar Quiz
            </a>
            <a href="{{ route('employee.learning.show', $attempt->quiz->material) }}"
               class="flex-1 min-w-[150px] text-center bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-2 rounded-lg transition">
                <i class="fas fa-book mr-1"></i> Kembali ke Materi
            </a>
            @if($attempt->quiz->max_attempts > 1 && !$attempt->is_passed)
                <a href="{{ route('employee.quiz.show', $attempt->quiz) }}"
                   class="flex-1 min-w-[150px] text-center bg-yellow-600 hover:bg-yellow-700 text-white px-6 py-2 rounded-lg transition">
                    <i class="fas fa-redo mr-1"></i> Coba Lagi
                </a>
            @endif
        </div>
    </div>
</div>
@endsection
