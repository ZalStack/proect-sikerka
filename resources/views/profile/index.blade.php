@extends('layouts.app')

@section('title', 'Profil Saya - E-Learning')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-3xl">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Profil Saya</h1>
        <p class="text-gray-600 mt-1">Informasi akun Anda</p>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6">
            <div class="flex items-center space-x-4 mb-6">
                <div class="w-20 h-20 bg-blue-600 rounded-full flex items-center justify-center text-white text-3xl font-bold">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-800">{{ auth()->user()->name }}</h2>
                    <p class="text-gray-600">{{ auth()->user()->email ?? 'Tidak ada email' }}</p>
                    <p class="text-sm text-gray-500">
                        <span class="px-2 py-1 bg-blue-100 text-blue-600 rounded-full text-xs">
                            {{ ucfirst(auth()->user()->role) }}
                        </span>
                        <span class="ml-2">{{ auth()->user()->enroll_number }}</span>
                    </p>
                </div>
            </div>

            <div class="border-t border-gray-200 pt-6">
                <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Nama Lengkap</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ auth()->user()->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Nomor Enroll</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ auth()->user()->enroll_number }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Divisi</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ auth()->user()->division->name ?? 'Tidak ada' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Email</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ auth()->user()->email ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Telepon</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ auth()->user()->employee->phone ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Bergabung Sejak</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ auth()->user()->created_at->format('d M Y') }}</dd>
                    </div>
                </dl>
            </div>

            <div class="mt-6 border-t border-gray-200 pt-6 flex space-x-3">
                <a href="{{ route('profile.settings') }}"
                   class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition">
                    <i class="fas fa-cog mr-2"></i> Pengaturan
                </a>
                <a href="/"
                   class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-2 rounded-lg transition">
                    <i class="fas fa-arrow-left mr-2"></i> Kembali
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
