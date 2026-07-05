@extends('layouts.app')

@section('title', 'Manajemen Materi - E-Learning Karyawan')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Manajemen Materi</h1>
            <p class="text-gray-600 mt-1">Kelola seluruh materi pembelajaran karyawan</p>
        </div>
        <a href="{{ route('admin.materials.create') }}"
           class="mt-4 md:mt-0 bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg transition flex items-center">
            <i class="fas fa-plus mr-2"></i> Tambah Materi
        </a>
    </div>

    <!-- Search & Filter -->
    <div class="bg-white rounded-xl shadow-sm p-4 mb-6 border border-gray-100">
        <div class="flex flex-col md:flex-row gap-4">
            <div class="flex-1">
                <div class="relative">
                    <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    <input type="text"
                           id="searchMaterial"
                           placeholder="Cari materi..."
                           class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
            <div class="flex gap-2">
                <select class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option>Semua Status</option>
                    <option>Aktif</option>
                    <option>Nonaktif</option>
                </select>
                <select class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option>Urutkan</option>
                    <option>Terbaru</option>
                    <option>Terlama</option>
                    <option>Judul A-Z</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Materials Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($materials as $material)
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 hover:shadow-md transition overflow-hidden">
            <!-- Thumbnail -->
            <div class="h-48 bg-gradient-to-br from-blue-400 to-indigo-500 relative">
                <div class="absolute inset-0 flex items-center justify-center">
                    @if($material->file_type && str_contains($material->file_type, 'image'))
                        <img src="{{ asset('storage/' . $material->file_path) }}"
                             alt="{{ $material->title }}"
                             class="w-full h-full object-cover">
                    @elseif($material->file_type && str_contains($material->file_type, 'video'))
                        <i class="fas fa-video text-white text-6xl"></i>
                    @else
                        <i class="fas fa-file-alt text-white text-6xl"></i>
                    @endif
                </div>
                <div class="absolute top-2 right-2">
                    <span class="px-2 py-1 text-xs rounded-full
                        @if($material->is_active) bg-green-500 text-white
                        @else bg-gray-500 text-white @endif">
                        {{ $material->is_active ? 'Aktif' : 'Nonaktif' }}
                    </span>
                </div>
            </div>

            <!-- Content -->
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-2 line-clamp-1">
                    {{ $material->title }}
                </h3>
                <p class="text-sm text-gray-600 line-clamp-2 mb-3">
                    {{ $material->description }}
                </p>
                <div class="flex items-center text-sm text-gray-500 mb-3">
                    <i class="fas fa-user mr-2"></i>
                    <span>{{ $material->creator->name ?? 'Unknown' }}</span>
                    <span class="mx-2">•</span>
                    <i class="fas fa-calendar mr-2"></i>
                    <span>{{ $material->created_at->format('d M Y') }}</span>
                </div>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-500">
                        <i class="fas fa-file mr-1"></i>
                        {{ $material->file_size ? number_format($material->file_size / 1024, 1) . ' KB' : '0 KB' }}
                    </span>
                    <span class="text-gray-500">
                        <i class="fas fa-question-circle mr-1"></i>
                        {{ $material->quiz ? 'Ada Quiz' : 'Tidak Ada Quiz' }}
                    </span>
                </div>
                <div class="mt-4 pt-4 border-t border-gray-100 flex justify-between">
                    <div class="flex space-x-2">
                        <a href="{{ route('admin.materials.show', $material) }}"
                           class="text-blue-600 hover:text-blue-800 text-sm font-medium transition">
                            <i class="fas fa-eye mr-1"></i> Lihat
                        </a>
                        <a href="{{ route('admin.materials.edit', $material) }}"
                           class="text-green-600 hover:text-green-800 text-sm font-medium transition">
                            <i class="fas fa-edit mr-1"></i> Edit
                        </a>
                    </div>
                    <form action="{{ route('admin.materials.destroy', $material) }}"
                          method="POST"
                          onsubmit="return confirm('Yakin ingin menghapus materi ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 hover:text-red-800 text-sm font-medium transition">
                            <i class="fas fa-trash mr-1"></i> Hapus
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Pagination -->
    <div class="mt-8">
        {{ $materials->links() }}
    </div>
</div>
@endsection
