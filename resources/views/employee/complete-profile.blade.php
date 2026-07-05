@extends('layouts.app')

@section('title', 'Lengkapi Profil - E-Learning')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-50 to-indigo-100 py-12 px-4">
    <div class="max-w-md w-full">
        <div class="text-center mb-8">
            <div class="w-20 h-20 bg-blue-600 rounded-2xl flex items-center justify-center mx-auto shadow-lg">
                <i class="fas fa-user-plus text-white text-3xl"></i>
            </div>
            <h2 class="mt-4 text-2xl font-bold text-gray-800">Lengkapi Profil</h2>
            <p class="mt-1 text-sm text-gray-600">Silakan lengkapi data diri Anda</p>
        </div>

        <div class="bg-white rounded-2xl shadow-xl p-8">
            <form action="{{ route('employee.complete-profile') }}" method="POST">
                @csrf

                <div class="space-y-4">
                    <!-- Name -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap *</label>
                        <input type="text"
                               id="name"
                               name="name"
                               value="{{ old('name', auth()->user()->name) }}"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('name') border-red-500 @enderror"
                               required>
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Division -->
                    <div>
                        <label for="division_id" class="block text-sm font-medium text-gray-700 mb-1">Divisi *</label>
                        <select name="division_id"
                                id="division_id"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('division_id') border-red-500 @enderror"
                                required>
                            <option value="">Pilih Divisi</option>
                            @foreach(\App\Models\Division::where('is_active', true)->get() as $division)
                                <option value="{{ $division->id }}" {{ old('division_id', auth()->user()->division_id) == $division->id ? 'selected' : '' }}>
                                    {{ $division->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('division_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Phone -->
                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Telepon</label>
                        <input type="text"
                               id="phone"
                               name="phone"
                               value="{{ old('phone', auth()->user()->employee->phone ?? '') }}"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <!-- Address -->
                    <div>
                        <label for="address" class="block text-sm font-medium text-gray-700 mb-1">Alamat</label>
                        <textarea id="address"
                                  name="address"
                                  rows="3"
                                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('address', auth()->user()->employee->address ?? '') }}</textarea>
                    </div>

                    <button type="submit"
                            class="w-full py-3 px-4 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition duration-200">
                        <i class="fas fa-save mr-2"></i> Simpan Profil
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
