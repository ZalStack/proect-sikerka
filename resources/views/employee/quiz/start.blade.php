@extends('layouts.app')

@section('title', 'Mulai Quiz - E-Learning')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-3xl">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8 text-center">
        <div class="w-24 h-24 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-6">
            <i class="fas fa-pencil-alt text-blue-600 text-4xl"></i>
        </div>

        <h1 class="text-3xl font-bold text-gray-800 mb-2">{{ $quiz->title }}</h1>
        <p class="text-gray-600 mb-6">{{ $quiz->description ?? 'Silakan kerjakan quiz dengan teliti.' }}</p>

        <div class="bg-gray-50 rounded-lg p-6 mb-8 text-left max-w-md mx-auto">
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <span class="text-gray-500">Jumlah Soal</span>
                    <p class="font-semibold text-gray-800">{{ $questions->count() }}</p>
                </div>
                <div>
                    <span class="text-gray-500">Durasi</span>
                    <p class="font-semibold text-gray-800">{{ $quiz->duration_minutes }} menit</p>
                </div>
                <div>
                    <span class="text-gray-500">Nilai Minimal</span>
                    <p class="font-semibold text-gray-800">{{ $quiz->passing_score }}%</p>
                </div>
                <div>
                    <span class="text-gray-500">Maks. Percobaan</span>
                    <p class="font-semibold text-gray-800">{{ $quiz->max_attempts }}x</p>
                </div>
            </div>
        </div>

        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-8 text-left">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-triangle text-yellow-400"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-yellow-700">
                        <strong>Perhatian:</strong> Pastikan Anda memiliki koneksi internet yang stabil.
                        Quiz akan berakhir otomatis setelah waktu habis.
                    </p>
                </div>
            </div>
        </div>

        <form id="startQuizForm" action="{{ route('employee.quiz.start', $quiz) }}" method="POST">
            @csrf
            <button type="button" onclick="startQuiz()"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-3 rounded-lg transition text-lg font-semibold">
                <i class="fas fa-play mr-2"></i> Mulai Quiz
            </button>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function startQuiz() {
        if (confirm('Yakin ingin memulai quiz? Waktu akan berjalan setelah Anda memulai.')) {
            const form = document.getElementById('startQuizForm');
            form.submit();
        }
    }
</script>
@endpush
@endsection
