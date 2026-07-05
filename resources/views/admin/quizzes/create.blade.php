@extends('layouts.app')

@section('title', 'Buat Quiz - E-Learning')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-3xl">
    <div class="mb-6">
        <a href="{{ route('admin.quizzes.index') }}" class="text-blue-600 hover:text-blue-800 transition">
            <i class="fas fa-arrow-left mr-2"></i> Kembali
        </a>
        <h1 class="text-3xl font-bold text-gray-800 mt-2">Buat Quiz</h1>
        <p class="text-gray-600 mt-1">Buat quiz untuk materi pembelajaran</p>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <form action="{{ route('admin.quizzes.store') }}" method="POST">
            @csrf

            <div class="space-y-6">
                <!-- Material -->
                <div>
                    <label for="material_id" class="block text-sm font-medium text-gray-700 mb-1">Materi *</label>
                    <select name="material_id"
                            id="material_id"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('material_id') border-red-500 @enderror"
                            required>
                        <option value="">Pilih Materi</option>
                        @foreach($materials as $material)
                            <option value="{{ $material->id }}" {{ old('material_id', $selectedMaterial ?? '') == $material->id ? 'selected' : '' }}>
                                {{ $material->title }}
                            </option>
                        @endforeach
                    </select>
                    @error('material_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Title -->
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Judul Quiz *</label>
                    <input type="text"
                           id="title"
                           name="title"
                           value="{{ old('title') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('title') border-red-500 @enderror"
                           required>
                    @error('title')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Description -->
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                    <textarea id="description"
                              name="description"
                              rows="3"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('description') }}</textarea>
                </div>

                <!-- Date & Time -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">Tanggal Mulai *</label>
                        <input type="datetime-local"
                               id="start_date"
                               name="start_date"
                               value="{{ old('start_date') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('start_date') border-red-500 @enderror"
                               required>
                        @error('start_date')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1">Tanggal Berakhir *</label>
                        <input type="datetime-local"
                               id="end_date"
                               name="end_date"
                               value="{{ old('end_date') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('end_date') border-red-500 @enderror"
                               required>
                        @error('end_date')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Settings -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="duration_minutes" class="block text-sm font-medium text-gray-700 mb-1">Durasi (menit) *</label>
                        <input type="number"
                               id="duration_minutes"
                               name="duration_minutes"
                               value="{{ old('duration_minutes', 60) }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('duration_minutes') border-red-500 @enderror"
                               required>
                        @error('duration_minutes')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="passing_score" class="block text-sm font-medium text-gray-700 mb-1">Nilai Minimal (%) *</label>
                        <input type="number"
                               id="passing_score"
                               name="passing_score"
                               value="{{ old('passing_score', 70) }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('passing_score') border-red-500 @enderror"
                               required>
                        @error('passing_score')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="max_attempts" class="block text-sm font-medium text-gray-700 mb-1">Maks. Percobaan *</label>
                        <input type="number"
                               id="max_attempts"
                               name="max_attempts"
                               value="{{ old('max_attempts', 1) }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('max_attempts') border-red-500 @enderror"
                               required>
                        @error('max_attempts')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Options -->
                <div class="space-y-3">
                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" name="is_random_questions" value="1" {{ old('is_random_questions') ? 'checked' : '' }}>
                            <span class="ml-2 text-sm text-gray-700">Acak urutan soal</span>
                        </label>
                    </div>
                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" name="show_score" value="1" {{ old('show_score', true) ? 'checked' : '' }}>
                            <span class="ml-2 text-sm text-gray-700">Tampilkan nilai</span>
                        </label>
                    </div>
                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" name="show_correct_answers" value="1" {{ old('show_correct_answers') ? 'checked' : '' }}>
                            <span class="ml-2 text-sm text-gray-700">Tampilkan jawaban benar/salah</span>
                        </label>
                    </div>
                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                            <span class="ml-2 text-sm text-gray-700">Aktif</span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="mt-8 flex justify-end space-x-3">
                <a href="{{ route('admin.quizzes.index') }}"
                   class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                    Batal
                </a>
                <button type="submit"
                        class="px-6 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition">
                    <i class="fas fa-save mr-2"></i> Simpan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
