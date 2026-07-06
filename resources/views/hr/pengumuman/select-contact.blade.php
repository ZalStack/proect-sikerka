@extends('layouts.app')

@section('content')
<div class="flex">
    @include('layouts.sidebar')
    <div class="ml-64 flex-1 p-6">
        <div class="mb-6">
            <h1 class="text-2xl font-bold font-['Montserrat'] text-[#161758]">Kirim Pengumuman ke WhatsApp</h1>
            <p class="text-[#27438D]">Pilih kontak atau grup WhatsApp untuk mengirim pengumuman</p>
        </div>

        <!-- Preview Pengumuman -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-lg font-semibold text-[#161758] mb-4">📋 Preview Pengumuman</h2>
            <div class="bg-[#F5F5F5] rounded-lg p-4">
                <h3 class="text-xl font-bold text-[#161758]">{{ $pengumuman->judul }}</h3>
                <p class="text-[#1B1B1B] mt-2 whitespace-pre-wrap">{{ $pengumuman->isi }}</p>
                @if($pengumuman->gambar)
                    <div class="mt-3">
                        <img src="{{ Storage::url($pengumuman->gambar) }}" alt="{{ $pengumuman->judul }}"
                             class="w-full max-h-48 object-cover rounded-lg">
                    </div>
                @endif
                <div class="mt-3 text-sm text-gray-500">
                    <p>📅 {{ $pengumuman->created_at->format('d-m-Y H:i') }}</p>
                    <p>👤 {{ $pengumuman->creator ? $pengumuman->creator->nama_lengkap : 'HR' }}</p>
                    <p>📌 Target: {{ $pengumuman->target_label }}</p>
                </div>
            </div>
        </div>

        <!-- Kirim ke WhatsApp - Tanpa Pilih Kontak -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6 border-2 border-[#25D366]">
            <h2 class="text-lg font-semibold text-[#161758] mb-4">📱 Kirim ke WhatsApp</h2>
            <p class="text-sm text-[#1B1B1B] mb-4">
                Klik tombol di bawah untuk membuka WhatsApp dan memilih kontak/grup secara manual.
                <br>Seperti saat berbagi link di TikTok/Instagram, Anda bisa memilih kontak atau grup yang diinginkan.
            </p>
            <a href="{{ route('hr.pengumuman.send-whatsapp', $pengumuman->id) }}"
               target="_blank"
               class="inline-flex items-center bg-[#25D366] text-white px-6 py-3 rounded-lg hover:bg-green-700 transition-colors duration-200 text-lg font-semibold">
                <svg class="w-7 h-7 mr-3" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                </svg>
                Kirim ke WhatsApp
                <span class="ml-2 text-sm font-normal">(Pilih Kontak/Grup)</span>
            </a>
            <p class="text-xs text-gray-500 mt-3">
                💡 Akan membuka WhatsApp Web/App, Anda bisa memilih kontak atau grup yang tersimpan di HP
            </p>
        </div>

        <!-- Daftar Kontak Tersimpan -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-lg font-semibold text-[#161758] mb-4">👥 Kontak WhatsApp Tersimpan</h2>
            <p class="text-sm text-[#1B1B1B] mb-4">
                Kirim langsung ke salah satu kontak di bawah ini:
            </p>

            @if(count($contacts) > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($contacts as $contact)
                        <a href="{{ route('hr.pengumuman.send-whatsapp-number', ['id' => $pengumuman->id, 'phone' => $contact['phone']]) }}"
                           target="_blank"
                           class="flex items-center p-4 bg-[#F5F5F5] rounded-lg hover:bg-[#e8f5e9] transition-colors duration-200 border border-transparent hover:border-[#25D366] group">
                            <div class="w-12 h-12 rounded-full bg-[#25D366] flex items-center justify-center text-white font-bold text-xl">
                                {{ strtoupper(substr($contact['nama'], 0, 1)) }}
                            </div>
                            <div class="ml-3 flex-1">
                                <p class="font-semibold text-[#1B1B1B]">{{ $contact['nama'] }}</p>
                                <p class="text-sm text-gray-500">{{ $contact['phone'] }}</p>
                            </div>
                            <svg class="w-5 h-5 text-[#25D366] opacity-0 group-hover:opacity-100 transition-opacity duration-200" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                            </svg>
                        </a>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8">
                    <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                    <p class="text-gray-500">Belum ada kontak WhatsApp tersimpan</p>
                    <p class="text-sm text-gray-400 mt-1">Tambahkan nomor WhatsApp pada data karyawan</p>
                </div>
            @endif
        </div>

        <div class="mt-6 flex space-x-4">
            <a href="{{ route('hr.pengumuman.index') }}"
               class="bg-gray-500 text-white px-6 py-2 rounded-lg hover:bg-gray-600 transition-colors duration-200">
                Kembali
            </a>
            <a href="{{ route('hr.pengumuman.show', $pengumuman->id) }}"
               class="bg-[#00a2e9] text-white px-6 py-2 rounded-lg hover:bg-[#27438D] transition-colors duration-200">
                Lihat Detail
            </a>
        </div>
    </div>
</div>
@endsection
