@extends('layouts.app')

@section('title', 'Dashboard Admin - E-Learning Karyawan')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Welcome Section -->
    <div class="bg-gradient-to-r from-blue-600 to-indigo-600 rounded-2xl p-8 mb-8 text-white">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
            <div>
                <h1 class="text-3xl font-bold mb-2">Selamat Datang, {{ auth()->user()->name }}!</h1>
                <p class="text-blue-100">Kelola sistem pembelajaran karyawan dengan mudah</p>
            </div>
            <div class="mt-4 md:mt-0 flex space-x-3">
                <a href="{{ route('admin.materials.create') }}"
                   class="bg-white text-blue-600 px-4 py-2 rounded-lg hover:bg-blue-50 transition flex items-center">
                    <i class="fas fa-plus mr-2"></i> Tambah Materi
                </a>
                <a href="{{ route('admin.quizzes.create') }}"
                   class="bg-white/20 backdrop-blur-sm text-white px-4 py-2 rounded-lg hover:bg-white/30 transition flex items-center">
                    <i class="fas fa-plus mr-2"></i> Buat Quiz
                </a>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 font-medium">Total Karyawan</p>
                    <p class="text-2xl font-bold text-gray-800 mt-1">{{ $stats['total_employees'] }}</p>
                    <p class="text-xs text-green-600 mt-1">
                        <i class="fas fa-arrow-up mr-1"></i> 12% dari bulan lalu
                    </p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-users text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 font-medium">Total Materi</p>
                    <p class="text-2xl font-bold text-gray-800 mt-1">{{ $stats['total_materials'] }}</p>
                    <p class="text-xs text-green-600 mt-1">
                        <i class="fas fa-arrow-up mr-1"></i> 5 materi baru
                    </p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-book text-green-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 font-medium">Total Quiz</p>
                    <p class="text-2xl font-bold text-gray-800 mt-1">{{ $stats['total_quizzes'] }}</p>
                    <p class="text-xs text-yellow-600 mt-1">
                        <i class="fas fa-clock mr-1"></i> 3 akan datang
                    </p>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-question-circle text-purple-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 font-medium">Pengerjaan Quiz</p>
                    <p class="text-2xl font-bold text-gray-800 mt-1">{{ $stats['total_attempts'] }}</p>
                    <p class="text-xs text-blue-600 mt-1">
                        <i class="fas fa-check-circle mr-1"></i> 78% selesai
                    </p>
                </div>
                <div class="w-12 h-12 bg-orange-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-pencil-alt text-orange-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Quiz Results Chart -->
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">
                <i class="fas fa-chart-line text-blue-600 mr-2"></i>
                Hasil Quiz (7 Hari Terakhir)
            </h3>
            <div class="h-64 flex items-center justify-center text-gray-500">
                <p class="text-sm">Grafik hasil quiz akan ditampilkan di sini</p>
            </div>
        </div>

        <!-- Material Progress -->
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">
                <i class="fas fa-progress text-green-600 mr-2"></i>
                Progress Materi
            </h3>
            <div class="space-y-4">
                @foreach($stats['material_stats'] as $material)
                <div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-700">{{ $material->title }}</span>
                        <span class="text-gray-500">{{ $material->tasks_count }} tugas</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2 mt-1">
                        <div class="bg-blue-600 h-2 rounded-full" style="width: 65%"></div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Recent Activities -->
    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">
            <i class="fas fa-clock text-gray-600 mr-2"></i>
            Aktivitas Terbaru
        </h3>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="text-left text-sm text-gray-500 border-b border-gray-200">
                        <th class="pb-2 font-medium">Nama</th>
                        <th class="pb-2 font-medium">Quiz</th>
                        <th class="pb-2 font-medium">Nilai</th>
                        <th class="pb-2 font-medium">Status</th>
                        <th class="pb-2 font-medium">Waktu</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($stats['recent_activities'] as $activity)
                    <tr class="border-b border-gray-100 hover:bg-gray-50 transition">
                        <td class="py-3 text-sm text-gray-700">{{ $activity->user->name ?? 'Unknown' }}</td>
                        <td class="py-3 text-sm text-gray-700">{{ $activity->quiz->title ?? 'Unknown' }}</td>
                        <td class="py-3 text-sm font-semibold
                            @if($activity->score >= 70) text-green-600
                            @elseif($activity->score >= 50) text-yellow-600
                            @else text-red-600 @endif">
                            {{ $activity->score ?? 0 }}
                        </td>
                        <td class="py-3">
                            <span class="px-2 py-1 text-xs rounded-full
                                @if($activity->is_passed) bg-green-100 text-green-600
                                @else bg-red-100 text-red-600 @endif">
                                {{ $activity->is_passed ? 'Lulus' : 'Tidak Lulus' }}
                            </span>
                        </td>
                        <td class="py-3 text-sm text-gray-500">
                            {{ $activity->created_at->diffForHumans() }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
