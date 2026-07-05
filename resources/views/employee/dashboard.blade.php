@extends('layouts.app')

@section('title', 'Dashboard Karyawan - E-Learning')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Welcome Section -->
    <div class="bg-gradient-to-r from-green-600 to-teal-600 rounded-2xl p-8 mb-8 text-white">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
            <div>
                <h1 class="text-3xl font-bold mb-2">Selamat Datang, {{ auth()->user()->name }}!</h1>
                <p class="text-green-100">Terus tingkatkan pengetahuan dan keterampilan Anda</p>
            </div>
            <div class="mt-4 md:mt-0 flex space-x-3">
                <a href="{{ route('employee.learning') }}"
                   class="bg-white text-green-600 px-6 py-2 rounded-lg hover:bg-green-50 transition flex items-center">
                    <i class="fas fa-book mr-2"></i> Mulai Belajar
                </a>
            </div>
        </div>
    </div>

    <!-- Statistics -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-6 mb-8">
        <div class="bg-white rounded-xl shadow-sm p-4 md:p-6 border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs md:text-sm text-gray-500">Total Materi</p>
                    <p class="text-xl md:text-2xl font-bold text-gray-800">{{ $stats['total_materials'] }}</p>
                </div>
                <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-book text-blue-600"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-4 md:p-6 border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs md:text-sm text-gray-500">Selesai</p>
                    <p class="text-xl md:text-2xl font-bold text-gray-800">{{ $stats['completed_materials'] }}</p>
                </div>
                <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-check-circle text-green-600"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-4 md:p-6 border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs md:text-sm text-gray-500">Total Quiz</p>
                    <p class="text-xl md:text-2xl font-bold text-gray-800">{{ $stats['total_quizzes'] }}</p>
                </div>
                <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-pencil-alt text-purple-600"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-4 md:p-6 border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs md:text-sm text-gray-500">Rata-rata Nilai</p>
                    <p class="text-xl md:text-2xl font-bold text-gray-800">{{ number_format($stats['average_score'], 0) }}</p>
                </div>
                <div class="w-10 h-10 bg-yellow-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-star text-yellow-600"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Materials Grid -->
    <div class="mb-8">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-bold text-gray-800">Materi Terbaru</h2>
            <a href="{{ route('employee.learning') }}" class="text-blue-600 hover:text-blue-800 transition text-sm">
                Lihat Semua <i class="fas fa-arrow-right ml-1"></i>
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($materials as $material)
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 hover:shadow-md transition overflow-hidden">
                <div class="h-40 bg-gradient-to-br from-blue-400 to-indigo-500 relative flex items-center justify-center">
                    <i class="fas fa-file-alt text-white text-5xl opacity-50"></i>
                    <span class="absolute bottom-2 right-2 bg-black/50 text-white text-xs px-2 py-1 rounded">
                        {{ $material->file_type ? strtoupper(explode('/', $material->file_type)[1] ?? 'FILE') : 'FILE' }}
                    </span>
                </div>
                <div class="p-4">
                    <h3 class="font-semibold text-gray-800 mb-1 line-clamp-1">{{ $material->title }}</h3>
                    <p class="text-sm text-gray-600 line-clamp-2 mb-3">{{ $material->description }}</p>
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-gray-500">
                            <i class="fas fa-user mr-1"></i> {{ $material->creator->name ?? 'Unknown' }}
                        </span>
                        @php
                            $enrolled = $enrolledMaterials->where('material_id', $material->id)->first();
                        @endphp
                        @if($enrolled)
                            <span class="text-xs px-2 py-1 bg-green-100 text-green-600 rounded-full">
                                @if($enrolled->status == 'completed')
                                    <i class="fas fa-check mr-1"></i> Selesai
                                @else
                                    <i class="fas fa-spinner mr-1"></i> {{ $enrolled->progress }}%
                                @endif
                            </span>
                        @else
                            <a href="{{ route('employee.learning.show', $material) }}"
                               class="text-xs text-blue-600 hover:text-blue-800 transition">
                                Mulai Belajar <i class="fas fa-arrow-right ml-1"></i>
                            </a>
                        @endif
                    </div>
                </div>
            </div>
            @empty
            <div class="col-span-full text-center py-8 text-gray-500">
                <i class="fas fa-book text-4xl text-gray-300 block mb-3"></i>
                <p>Belum ada materi pembelajaran.</p>
            </div>
            @endforelse
        </div>
    </div>

    <!-- Recent Quiz Attempts -->
    <div>
        <h2 class="text-xl font-bold text-gray-800 mb-4">Riwayat Quiz</h2>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-200">
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Quiz</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Nilai</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Waktu</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($quizAttempts as $attempt)
                        <tr class="border-b border-gray-100">
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $attempt->quiz->title ?? 'Unknown' }}</td>
                            <td class="px-4 py-3 text-sm font-semibold
                                @if($attempt->score >= 70) text-green-600
                                @elseif($attempt->score >= 50) text-yellow-600
                                @else text-red-600 @endif">
                                {{ $attempt->score ?? '-' }}
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-1 text-xs rounded-full
                                    {{ $attempt->is_passed ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600' }}">
                                    {{ $attempt->is_passed ? 'Lulus' : 'Tidak Lulus' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-500">
                                {{ $attempt->created_at->format('d M Y H:i') }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-4 py-6 text-center text-gray-500">
                                <i class="fas fa-clipboard-list text-3xl text-gray-300 block mb-2"></i>
                                <p>Belum ada riwayat quiz.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
