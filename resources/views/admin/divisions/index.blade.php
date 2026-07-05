@extends('layouts.app')

@section('title', 'Manajemen Divisi - E-Learning')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Manajemen Divisi</h1>
            <p class="text-gray-600 mt-1">Kelola divisi perusahaan</p>
        </div>
        <a href="{{ route('admin.divisions.create') }}"
           class="mt-4 md:mt-0 bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition flex items-center">
            <i class="fas fa-plus mr-2"></i> Tambah Divisi
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200">
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-600">No</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-600">Nama Divisi</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-600">Deskripsi</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-600">Karyawan</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-600">Status</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-600">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($divisions as $index => $division)
                    <tr class="border-b border-gray-100 hover:bg-gray-50 transition">
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $divisions->firstItem() + $index }}</td>
                        <td class="px-6 py-4 font-medium text-gray-800">{{ $division->name }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $division->description ?? '-' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $division->employees_count }}</td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs rounded-full
                                {{ $division->is_active ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600' }}">
                                {{ $division->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex space-x-2">
                                <a href="{{ route('admin.divisions.edit', $division) }}"
                                   class="text-blue-600 hover:text-blue-800 transition">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.divisions.destroy', $division) }}"
                                      method="POST"
                                      onsubmit="return confirm('Yakin ingin menghapus divisi ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-800 transition">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                            <i class="fas fa-building text-4xl text-gray-300 block mb-3"></i>
                            <p>Tidak ada data divisi.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6">
        {{ $divisions->links() }}
    </div>
</div>
@endsection
