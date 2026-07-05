@extends('layouts.app')

@section('title', 'Manajemen Soal - E-Learning')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-4xl">
    <div class="mb-6">
        <a href="{{ route('admin.quizzes.index') }}" class="text-blue-600 hover:text-blue-800 transition">
            <i class="fas fa-arrow-left mr-2"></i> Kembali
        </a>
        <h1 class="text-3xl font-bold text-gray-800 mt-2">Manajemen Soal</h1>
        <p class="text-gray-600 mt-1">Quiz: {{ $quiz->title }}</p>
    </div>

    <!-- Add Question Form -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Tambah Soal</h2>
        <form action="{{ route('admin.quizzes.questions.store', $quiz) }}" method="POST">
            @csrf

            <div class="space-y-4">
                <!-- Question Type -->
                <div>
                    <label for="type" class="block text-sm font-medium text-gray-700 mb-1">Jenis Soal *</label>
                    <select name="type"
                            id="type"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            onchange="toggleQuestionType()"
                            required>
                        <option value="multiple_choice">Pilihan Ganda</option>
                        <option value="true_false">Benar/Salah</option>
                        <option value="short_answer">Jawaban Singkat</option>
                        <option value="essay">Essay</option>
                    </select>
                </div>

                <!-- Question Text -->
                <div>
                    <label for="question_text" class="block text-sm font-medium text-gray-700 mb-1">Pertanyaan *</label>
                    <textarea name="question_text"
                              id="question_text"
                              rows="3"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                              required></textarea>
                </div>

                <!-- Points -->
                <div>
                    <label for="points" class="block text-sm font-medium text-gray-700 mb-1">Bobot Nilai *</label>
                    <input type="number"
                           name="points"
                           id="points"
                           value="1"
                           min="1"
                           class="w-full md:w-32 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                           required>
                </div>

                <!-- Multiple Choice Options -->
                <div id="multipleChoiceOptions">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Pilihan Jawaban *</label>
                    <div class="space-y-2">
                        @for($i = 0; $i < 4; $i++)
                        <div class="flex items-center space-x-3">
                            <span class="text-sm font-medium text-gray-500 w-8">{{ chr(65 + $i) }}.</span>
                            <input type="text"
                                   name="options[]"
                                   placeholder="Pilihan {{ chr(65 + $i) }}"
                                   class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <label class="flex items-center text-sm">
                                <input type="radio"
                                       name="correct_option"
                                       value="{{ $i }}"
                                       {{ $i == 0 ? 'checked' : '' }}
                                       class="mr-1 text-blue-600 focus:ring-blue-500">
                                Benar
                            </label>
                        </div>
                        @endfor
                    </div>
                </div>

                <!-- True/False Options -->
                <div id="trueFalseOptions" class="hidden">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Jawaban Benar *</label>
                    <div class="flex space-x-4">
                        <label class="flex items-center">
                            <input type="radio" name="correct_answer" value="true" checked>
                            <span class="ml-2 text-sm text-gray-700">Benar</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" name="correct_answer" value="false">
                            <span class="ml-2 text-sm text-gray-700">Salah</span>
                        </label>
                    </div>
                </div>

                <!-- Short Answer & Essay -->
                <div id="shortAnswerOptions" class="hidden">
                    <label for="correct_answer" class="block text-sm font-medium text-gray-700 mb-1">Jawaban Benar *</label>
                    <input type="text"
                           name="correct_answer"
                           id="correct_answer"
                           placeholder="Masukkan jawaban benar"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <!-- Order Number -->
                <div>
                    <label for="order_number" class="block text-sm font-medium text-gray-700 mb-1">Urutan</label>
                    <input type="number"
                           name="order_number"
                           id="order_number"
                           value="{{ $questions->count() + 1 }}"
                           class="w-full md:w-32 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            <div class="mt-4 flex justify-end">
                <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition">
                    <i class="fas fa-plus mr-2"></i> Tambahkan Soal
                </button>
            </div>
        </form>
    </div>

    <!-- Questions List -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-800">Daftar Soal</h2>
        </div>

        @if($questions->count() > 0)
            <div class="divide-y divide-gray-200">
                @foreach($questions as $index => $question)
                <div class="p-6">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center space-x-2 mb-2">
                                <span class="w-6 h-6 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center text-xs font-semibold">
                                    {{ $index + 1 }}
                                </span>
                                <span class="text-sm text-gray-500">{{ str_replace('_', ' ', ucfirst($question->type)) }}</span>
                                <span class="text-xs text-gray-400">|</span>
                                <span class="text-sm text-gray-500">{{ $question->points }} poin</span>
                            </div>
                            <p class="text-gray-800">{{ $question->question_text }}</p>

                            @if($question->type == 'multiple_choice' && $question->options->count() > 0)
                                <div class="mt-2 space-y-1">
                                    @foreach($question->options as $option)
                                    <div class="flex items-center space-x-2 text-sm">
                                        <span class="text-gray-500">{{ chr(65 + $loop->index) }}.</span>
                                        <span class="{{ $option->is_correct ? 'text-green-600 font-medium' : 'text-gray-600' }}">
                                            {{ $option->option_text }}
                                            @if($option->is_correct)
                                                <i class="fas fa-check text-green-600 ml-1"></i>
                                            @endif
                                        </span>
                                    </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                        <div class="flex space-x-2 ml-4">
                            <form action="{{ route('admin.questions.destroy', $question) }}"
                                  method="POST"
                                  onsubmit="return confirm('Yakin ingin menghapus soal ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-800 transition">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        @else
            <div class="p-6 text-center text-gray-500">
                <i class="fas fa-question-circle text-4xl text-gray-300 block mb-3"></i>
                <p>Belum ada soal untuk quiz ini.</p>
                <p class="text-sm">Tambahkan soal menggunakan form di atas.</p>
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
    function toggleQuestionType() {
        const type = document.getElementById('type').value;
        const multipleChoice = document.getElementById('multipleChoiceOptions');
        const trueFalse = document.getElementById('trueFalseOptions');
        const shortAnswer = document.getElementById('shortAnswerOptions');

        multipleChoice.classList.add('hidden');
        trueFalse.classList.add('hidden');
        shortAnswer.classList.add('hidden');

        if (type === 'multiple_choice') {
            multipleChoice.classList.remove('hidden');
        } else if (type === 'true_false') {
            trueFalse.classList.remove('hidden');
        } else {
            shortAnswer.classList.remove('hidden');
        }
    }

    // Initialize on load
    document.addEventListener('DOMContentLoaded', function() {
        toggleQuestionType();
    });
</script>
@endpush
@endsection
