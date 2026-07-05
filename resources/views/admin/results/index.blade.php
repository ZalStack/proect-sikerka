@extends('layouts.app')

@section('title', 'Hasil Quiz - E-Learning')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800">Hasil Quiz</h1>
        <p class="text-gray-600 mt-1">Lihat hasil pengerjaan quiz seluruh karyawan</p>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200">
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-600">No</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-600">Karyawan</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-600">Quiz</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-600">Nilai</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-600">Status</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-600">Waktu</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-600">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($results as $index => $result)
                    <tr class="border-b border-gray-100 hover:bg-gray-50 transition">
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $results->firstItem() + $index }}</td>
                        <td class="px-6 py-4">
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center text-blue-600 font-semibold text-sm">
                                    {{ strtoupper(substr($result->user->name ?? 'U', 0, 1)) }}
                                </div>
                                <span class="text-sm font-medium text-gray-800">{{ $result->user->name ?? 'Unknown' }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $result->quiz->title ?? 'Unknown' }}</td>
                        <td class="px-6 py-4">
                            <span class="font-semibold text-sm
                                {{ ($result->score ?? 0) >= 70 ? 'text-green-600' :
                                   (($result->score ?? 0) >= 50 ? 'text-yellow-600' : 'text-red-600') }}">
                                {{ $result->score ?? '-' }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs rounded-full
                                {{ $result->is_passed ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600' }}">
                                {{ $result->is_passed ? 'Lulus' : 'Tidak Lulus' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            {{ $result->completed_at ? $result->completed_at->format('d M Y H:i') : '-' }}
                        </td>
                        <td class="px-6 py-4">
                            <a href="{{ route('admin.results.show', $result) }}"
                               class="text-blue-600 hover:text-blue-800 transition text-sm">
                                <i class="fas fa-eye mr-1"></i> Detail
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                            <i class="fas fa-chart-bar text-4xl text-gray-300 block mb-3"></i>
                            <p>Belum ada hasil quiz.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6">
        {{ $results->links() }}
    </div>
</div>
@endsection
