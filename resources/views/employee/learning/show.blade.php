@extends('layouts.app')

@section('title', $material->title . ' - E-Learning')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-4xl">
    <!-- Navigation -->
    <div class="mb-6">
        <a href="{{ route('employee.learning') }}" class="text-blue-600 hover:text-blue-800 transition">
            <i class="fas fa-arrow-left mr-2"></i> Kembali ke Materi
        </a>
    </div>

    <!-- Material Content -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <!-- Header -->
        <div class="bg-gradient-to-r from-blue-600 to-indigo-600 p-6 text-white">
            <h1 class="text-2xl font-bold">{{ $material->title }}</h1>
            <p class="text-blue-100 mt-1">Dibuat oleh: {{ $material->creator->name ?? 'Unknown' }}</p>
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
                <div class="mb-6 bg-gray-50 rounded-lg p-4 border border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-800 mb-3">Materi</h2>
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
                    @elseif(str_contains($material->file_type, 'pdf'))
                        <div class="flex items-center justify-between p-4 bg-white rounded-lg border border-gray-200">
                            <div class="flex items-center space-x-3">
                                <i class="fas fa-file-pdf text-red-500 text-3xl"></i>
                                <div>
                                    <p class="font-medium text-gray-800">File PDF</p>
                                    <p class="text-sm text-gray-500">{{ number_format($material->file_size / 1024, 1) }} KB</p>
                                </div>
                            </div>
                            <a href="{{ asset('storage/' . $material->file_path) }}"
                               target="_blank"
                               class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition text-sm">
                                <i class="fas fa-eye mr-1"></i> Lihat PDF
                            </a>
                        </div>
                    @else
                        <div class="flex items-center justify-between p-4 bg-white rounded-lg border border-gray-200">
                            <div class="flex items-center space-x-3">
                                <i class="fas fa-file-alt text-blue-500 text-3xl"></i>
                                <div>
                                    <p class="font-medium text-gray-800">File Materi</p>
                                    <p class="text-sm text-gray-500">{{ number_format($material->file_size / 1024, 1) }} KB</p>
                                </div>
                            </div>
                            <a href="{{ asset('storage/' . $material->file_path) }}"
                               download
                               class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition text-sm">
                                <i class="fas fa-download mr-1"></i> Download
                            </a>
                        </div>
                    @endif
                </div>
            @endif

            <!-- Progress -->
            <div class="mb-6">
                <div class="flex justify-between text-sm text-gray-600 mb-1">
                    <span>Progress Belajar</span>
                    <span>{{ $enrollment->progress ?? 0 }}%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-blue-600 h-2 rounded-full transition-all duration-500"
                         style="width: {{ $enrollment->progress ?? 0 }}%"></div>
                </div>
                <div class="mt-2 text-sm">
                    <span class="text-gray-500">Status: </span>
                    <span class="font-medium
                        @if(($enrollment->status ?? '') == 'completed') text-green-600
                        @else text-blue-600 @endif">
                        {{ ucfirst(str_replace('_', ' ', $enrollment->status ?? 'Belum dimulai')) }}
                    </span>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex flex-wrap gap-3">
                <!-- Task Submission -->
                @if($material->tasks)
                    <a href="{{ route('employee.tasks.create', $material) }}"
                       class="flex-1 min-w-[150px] text-center bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-lg transition text-sm font-medium">
                        <i class="fas fa-upload mr-1"></i> Upload Tugas
                    </a>
                @endif

                <!-- Quiz -->
                @if($material->quiz && $material->quiz->is_active)
                    @php
                        $canTakeQuiz = $material->quiz->start_date <= now() && $material->quiz->end_date >= now();
                        $hasAttempt = $material->quiz->attempts->where('user_id', auth()->id())->first();
                    @endphp
                    @if($canTakeQuiz && (!$hasAttempt || $hasAttempt->status == 'in_progress'))
                        <a href="{{ route('employee.quiz.show', $material->quiz) }}"
                           class="flex-1 min-w-[150px] text-center bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg transition text-sm font-medium">
                            <i class="fas fa-pencil-alt mr-1"></i> Kerjakan Quiz
                        </a>
                    @elseif($hasAttempt && $hasAttempt->status == 'completed')
                        <a href="{{ route('employee.quiz.result', $hasAttempt) }}"
                           class="flex-1 min-w-[150px] text-center bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition text-sm font-medium">
                            <i class="fas fa-chart-bar mr-1"></i> Lihat Hasil Quiz
                        </a>
                    @elseif(!$canTakeQuiz)
                        <span class="flex-1 min-w-[150px] text-center bg-gray-300 text-gray-600 px-4 py-2 rounded-lg text-sm font-medium cursor-not-allowed">
                            <i class="fas fa-clock mr-1"></i> Quiz Belum Tersedia
                        </span>
                    @endif
                @endif

                <!-- Mark as Complete -->
                @if(($enrollment->status ?? '') != 'completed')
                    <button onclick="markComplete()"
                            class="flex-1 min-w-[150px] text-center bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition text-sm font-medium">
                        <i class="fas fa-check mr-1"></i> Tandai Selesai
                    </button>
                @endif
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function markComplete() {
        if (confirm('Yakin ingin menandai materi ini sebagai selesai?')) {
            fetch('{{ route('employee.learning.progress', $material) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ progress: 100 })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                }
            });
        }
    }
</script>
@endpush
@endsection
