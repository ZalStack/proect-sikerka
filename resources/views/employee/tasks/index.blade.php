@extends('layouts.app')

@section('title', 'Tugas Saya - E-Learning')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-4xl">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Tugas Saya</h1>
        <p class="text-gray-600 mt-1">Kelola tugas yang telah Anda kirim</p>
    </div>

    <div class="space-y-4">
        @forelse($tasks as $task)
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex-1">
                    <div class="flex items-start space-x-3">
                        <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-video text-orange-600 text-xl"></i>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-800">{{ $task->material->title ?? 'Materi' }}</h3>
                            <p class="text-sm text-gray-500">
                                <i class="fas fa-calendar mr-1"></i>
                                Dikirim: {{ $task->submitted_at ? $task->submitted_at->format('d M Y H:i') : '-' }}
                            </p>
                            <p class="text-sm text-gray-500">
                                <i class="fas fa-file-video mr-1"></i>
                                {{ number_format($task->video_size / 1024, 1) }} KB
                            </p>
                            @if($task->comment)
                                <p class="text-sm text-gray-600 mt-1">{{ $task->comment }}</p>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="flex items-center space-x-4">
                    <!-- Status -->
                    <span class="px-3 py-1 text-xs rounded-full font-medium
                        @if($task->status == 'approved') bg-green-100 text-green-600
                        @elseif($task->status == 'rejected') bg-red-100 text-red-600
                        @elseif($task->status == 'graded') bg-blue-100 text-blue-600
                        @else bg-yellow-100 text-yellow-600 @endif">
                        {{ ucfirst($task->status) }}
                    </span>

                    @if($task->status == 'rejected' || $task->status == 'pending')
                        <a href="{{ route('employee.tasks.edit', $task) }}"
                           class="text-blue-600 hover:text-blue-800 text-sm transition">
                            <i class="fas fa-edit mr-1"></i> Edit
                        </a>
                    @endif
                </div>
            </div>

            @if($task->score !== null)
            <div class="mt-3 pt-3 border-t border-gray-100">
                <div class="flex items-center space-x-4 text-sm">
                    <span class="text-gray-500">Nilai:</span>
                    <span class="font-semibold
                        @if($task->score >= 70) text-green-600
                        @elseif($task->score >= 50) text-yellow-600
                        @else text-red-600 @endif">
                        {{ $task->score }}
                    </span>
                    @if($task->comment)
                        <span class="text-gray-500">|</span>
                        <span class="text-gray-600">{{ $task->comment }}</span>
                    @endif
                </div>
            </div>
            @endif
        </div>
        @empty
        <div class="text-center py-12">
            <i class="fas fa-tasks text-6xl text-gray-300 block mb-4"></i>
            <h3 class="text-xl font-semibold text-gray-600">Belum Ada Tugas</h3>
            <p class="text-gray-400 mt-1">Anda belum mengirimkan tugas apapun</p>
        </div>
        @endforelse
    </div>

    <div class="mt-6">
        {{ $tasks->links() }}
    </div>
</div>
@endsection
