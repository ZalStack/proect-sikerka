@extends('layouts.app')

@section('title', 'Detail Hasil Quiz - E-Learning')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-4xl">
    <div class="mb-6">
        <a href="{{ route('admin.results.index') }}" class="text-blue-600 hover:text-blue-800 transition">
            <i class="fas fa-arrow-left mr-2"></i> Kembali
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <!-- Header -->
        <div class="bg-gradient-to-r from-blue-600 to-indigo-600 p-6 text-white">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
                <div>
                    <h1 class="text-2xl font-bold">{{ $attempt->quiz->title }}</h1>
                    <p class="text-blue-100 mt-1">
                        <i class="fas fa-user mr-1"></i> {{ $attempt->user->name ?? 'Unknown' }}
                        <span class="mx-2">•</span>
                        <i class="fas fa-calendar mr-1"></i> {{ $attempt->completed_at->format('d M Y H:i') }}
                    </p>
                </div>
                <div class="mt-4 md:mt-0 flex items-center space-x-4">
                    <div class="text-center">
                        <p class="text-sm text-blue-100">Nilai</p>
                        <p class="text-2xl font-bold
                            {{ ($attempt->score ?? 0) >= 70 ? 'text-green-300' :
                               (($attempt->score ?? 0) >= 50 ? 'text-yellow-300' : 'text-red-300') }}">
                            {{ $attempt->score ?? '-' }}%
                        </p>
                    </div>
                    <div class="text-center">
                        <p class="text-sm text-blue-100">Status</p>
                        <span class="px-3 py-1 text-sm rounded-full
                            {{ $attempt->is_passed ? 'bg-green-500/20 text-green-100' : 'bg-red-500/20 text-red-100' }}">
                            {{ $attempt->is_passed ? '✅ Lulus' : '❌ Tidak Lulus' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics -->
        <div class="p-6 border-b border-gray-200">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="text-center">
                    <p class="text-sm text-gray-500">Total Soal</p>
                    <p class="text-xl font-bold text-gray-800">{{ $totalQuestions }}</p>
                </div>
                <div class="text-center">
                    <p class="text-sm text-gray-500">Benar</p>
                    <p class="text-xl font-bold text-green-600">{{ $correctAnswers }}</p>
                </div>
                <div class="text-center">
                    <p class="text-sm text-gray-500">Salah</p>
                    <p class="text-xl font-bold text-red-600">{{ $wrongAnswers }}</p>
                </div>
                <div class="text-center">
                    <p class="text-sm text-gray-500">Durasi</p>
                    <p class="text-xl font-bold text-gray-800">
                        {{ $attempt->started_at->diffInMinutes($attempt->completed_at) }} m
                    </p>
                </div>
            </div>
        </div>

        <!-- Answers Review -->
        <div class="p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Review Jawaban</h2>

            <div class="space-y-4">
                @foreach($attempt->answers as $index => $answer)
                <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center space-x-2 mb-2">
                                <span class="w-6 h-6 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center text-xs font-semibold">
                                    {{ $index + 1 }}
                                </span>
                                <span class="font-medium text-gray-800">{{ $answer->question->question_text }}</span>
                                @if($answer->is_correct)
                                    <span class="text-green-600"><i class="fas fa-check-circle"></i></span>
                                @else
                                    <span class="text-red-600"><i class="fas fa-times-circle"></i></span>
                                @endif
                            </div>

                            <div class="ml-8 space-y-1 text-sm">
                                <div class="flex items-center space-x-2">
                                    <span class="text-gray-500">Jawaban:</span>
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
        </div>

        <!-- Actions -->
        <div class="p-6 border-t border-gray-200 flex flex-wrap gap-3">
            <a href="{{ route('admin.results.index') }}"
               class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-2 rounded-lg transition">
                <i class="fas fa-list mr-1"></i> Kembali
            </a>
            @if($attempt->score !== null)
                <form action="{{ route('admin.results.hide', $attempt) }}" method="POST">
                    @csrf
                    <button type="submit" class="bg-yellow-600 hover:bg-yellow-700 text-white px-6 py-2 rounded-lg transition">
                        <i class="fas fa-eye-slash mr-1"></i> Sembunyikan Nilai
                    </button>
                </form>
            @else
                <form action="{{ route('admin.results.show', $attempt) }}" method="POST">
                    @csrf
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg transition">
                        <i class="fas fa-eye mr-1"></i> Tampilkan Nilai
                    </button>
                </form>
            @endif
        </div>
    </div>
</div>
@endsection
