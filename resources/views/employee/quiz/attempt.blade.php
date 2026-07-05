@extends('layouts.app')

@section('title', 'Mengerjakan Quiz - E-Learning')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-4xl">
    <!-- Timer & Info -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6 sticky top-0 z-10">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h2 class="font-semibold text-gray-800">{{ $attempt->quiz->title }}</h2>
                <p class="text-sm text-gray-500">{{ $questions->count() }} pertanyaan</p>
            </div>
            <div class="flex items-center space-x-6">
                <div class="text-center">
                    <p class="text-xs text-gray-500">Waktu Tersisa</p>
                    <p id="timer" class="text-2xl font-bold text-blue-600">00:00</p>
                </div>
                <button onclick="submitQuiz()"
                        class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg transition text-sm font-medium">
                    <i class="fas fa-check mr-1"></i> Selesai
                </button>
            </div>
        </div>
    </div>

    <!-- Questions -->
    <form id="quizForm" action="{{ route('employee.quiz.submit', $attempt) }}" method="POST">
        @csrf

        @foreach($questions as $index => $question)
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-4">
            <div class="flex items-start justify-between mb-4">
                <div class="flex items-start space-x-3">
                    <span class="flex-shrink-0 w-8 h-8 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center font-semibold text-sm">
                        {{ $index + 1 }}
                    </span>
                    <div>
                        <p class="font-medium text-gray-800">{{ $question->question_text }}</p>
                        <p class="text-xs text-gray-500 mt-1">Bobot: {{ $question->points }} poin</p>
                    </div>
                </div>
                <span class="text-xs px-2 py-1 bg-gray-100 text-gray-600 rounded-full">
                    {{ str_replace('_', ' ', ucfirst($question->type)) }}
                </span>
            </div>

            @php
                $selectedAnswer = $answers->get($question->id);
            @endphp

            @if($question->type == 'multiple_choice')
                <div class="space-y-2 ml-11">
                    @foreach($question->options as $option)
                    <label class="flex items-start space-x-3 p-3 rounded-lg border border-gray-200 hover:bg-gray-50 cursor-pointer transition">
                        <input type="radio"
                               name="answers[{{ $question->id }}]"
                               value="{{ $option->id }}"
                               {{ $selectedAnswer && $selectedAnswer->selected_option_id == $option->id ? 'checked' : '' }}
                               class="mt-1 text-blue-600 focus:ring-blue-500">
                        <span class="text-gray-700">{{ $option->option_text }}</span>
                    </label>
                    @endforeach
                </div>
            @elseif($question->type == 'true_false')
                <div class="space-y-2 ml-11">
                    <label class="flex items-start space-x-3 p-3 rounded-lg border border-gray-200 hover:bg-gray-50 cursor-pointer transition">
                        <input type="radio"
                               name="answers[{{ $question->id }}]"
                               value="true"
                               {{ $selectedAnswer && $selectedAnswer->answer_text == 'true' ? 'checked' : '' }}
                               class="mt-1 text-blue-600 focus:ring-blue-500">
                        <span class="text-gray-700">Benar</span>
                    </label>
                    <label class="flex items-start space-x-3 p-3 rounded-lg border border-gray-200 hover:bg-gray-50 cursor-pointer transition">
                        <input type="radio"
                               name="answers[{{ $question->id }}]"
                               value="false"
                               {{ $selectedAnswer && $selectedAnswer->answer_text == 'false' ? 'checked' : '' }}
                               class="mt-1 text-blue-600 focus:ring-blue-500">
                        <span class="text-gray-700">Salah</span>
                    </label>
                </div>
            @else
                <div class="ml-11">
                    <textarea name="answers[{{ $question->id }}]"
                              rows="4"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                              placeholder="Tulis jawaban Anda...">{{ $selectedAnswer->answer_text ?? '' }}</textarea>
                </div>
            @endif
        </div>
        @endforeach

        <div class="flex justify-end">
            <button type="button" onclick="submitQuiz()"
                    class="bg-green-600 hover:bg-green-700 text-white px-8 py-3 rounded-lg transition text-lg font-semibold">
                <i class="fas fa-check mr-2"></i> Selesaikan Quiz
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
    let timer;
    let timeLeft = {{ $attempt->quiz->duration_minutes * 60 }};
    const startTime = new Date('{{ $attempt->started_at }}').getTime();

    function updateTimer() {
        const now = new Date().getTime();
        const elapsed = Math.floor((now - startTime) / 1000);
        timeLeft = {{ $attempt->quiz->duration_minutes * 60 }} - elapsed;

        if (timeLeft <= 0) {
            clearInterval(timer);
            document.getElementById('timer').textContent = '00:00';
            alert('Waktu habis! Quiz akan disubmit secara otomatis.');
            document.getElementById('quizForm').submit();
            return;
        }

        const minutes = Math.floor(timeLeft / 60);
        const seconds = timeLeft % 60;
        document.getElementById('timer').textContent =
            `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;

        // Warning when time is running low
        if (timeLeft < 60) {
            document.getElementById('timer').classList.add('text-red-600');
        }
    }

    timer = setInterval(updateTimer, 1000);
    updateTimer();

    function submitQuiz() {
        if (confirm('Yakin ingin menyelesaikan quiz? Jawaban yang sudah diisi akan disimpan.')) {
            clearInterval(timer);
            document.getElementById('quizForm').submit();
        }
    }
</script>
@endpush
@endsection
