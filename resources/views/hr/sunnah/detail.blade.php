@extends('layouts.app')

@section('content')
<div class="flex">
    @include('layouts.sidebar')
    <div class="ml-64 flex-1 p-6">
        <div class="mb-6">
            <h1 class="text-2xl font-bold font-['Montserrat'] text-[#161758]">Detail 7SPS</h1>
            <p class="text-[#27438D]">Detail kegiatan 7 Sunnah Plus Suprasional</p>
        </div>

        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h3 class="text-lg font-semibold text-[#161758] border-b border-gray-200 pb-2 mb-4">Informasi Karyawan</h3>
                        <div class="space-y-2">
                            <div>
                                <label class="text-sm text-[#1B1B1B] font-medium">Nama Lengkap</label>
                                <p class="text-[#27438D]">{{ $sunnah->karyawan->nama_lengkap }}</p>
                            </div>
                            <div>
                                <label class="text-sm text-[#1B1B1B] font-medium">Kode Pegawai</label>
                                <p class="text-[#27438D]">{{ $sunnah->karyawan->kode_pegawai }}</p>
                            </div>
                            <div>
                                <label class="text-sm text-[#1B1B1B] font-medium">Divisi</label>
                                <p class="text-[#27438D]">{{ $sunnah->karyawan->divisi ?? '-' }}</p>
                            </div>
                        </div>
                    </div>

                    <div>
                        <h3 class="text-lg font-semibold text-[#161758] border-b border-gray-200 pb-2 mb-4">Detail Kegiatan</h3>
                        <div class="space-y-2">
                            <div>
                                <label class="text-sm text-[#1B1B1B] font-medium">Tanggal</label>
                                <p class="text-[#27438D]">{{ $sunnah->tanggal->format('d-m-Y') }}</p>
                            </div>
                            <div>
                                <label class="text-sm text-[#1B1B1B] font-medium">Total Poin</label>
                                <p class="text-2xl font-bold text-[#161758]">{{ $sunnah->total_poin }}</p>
                            </div>
                            <div>
                                <label class="text-sm text-[#1B1B1B] font-medium">Status</label>
                                <p>
                                    <span class="px-2 py-1 rounded-full text-xs font-medium {{ $sunnah->status_badge }}">
                                        {{ $sunnah->status_label }}
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Kelompok Sholat Wajib -->
                <div class="mt-6 pt-6 border-t border-gray-200">
                    <h3 class="text-lg font-semibold text-[#161758] mb-2">🕌 Sholat Wajib &amp; Berjamaah</h3>
                    <p class="text-xs text-[#27438D] mb-4">
                        <strong>Ketentuan poin:</strong><br>
                        • 5/5 berjamaah (lengkap) = <strong>20 poin</strong> (5 × 4)<br>
                        • 1-4/5 berjamaah = <strong>jumlah berjamaah × 1 poin</strong><br>
                        • 0/5 berjamaah = <strong>0 poin</strong>
                    </p>

                    <div class="flex items-center gap-4 mb-4">
                        <span class="px-4 py-2 rounded-lg bg-[#F5F5F5] text-sm font-medium text-[#1B1B1B]">
                            Berjamaah: {{ $sunnah->jumlah_sholat_berjamaah }}/5
                        </span>
                        <span class="px-4 py-2 rounded-lg bg-[#161758] text-white text-sm font-bold">
                            Poin Kelompok: {{ $sunnah->poin_sholat_wajib }}
                        </span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        @foreach($sholatWajibKeys as $key)
                            @php $config = $poinConfig[$key]; @endphp
                            <div class="flex items-center space-x-3 p-3 bg-[#F5F5F5] rounded-lg">
                                <span class="text-xl">{{ $config['icon'] }}</span>
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-[#1B1B1B]">{{ $config['label'] }}</p>
                                    <p class="text-xs text-[#27438D]">
                                        @if($sunnah->$key)
                                            @if($sunnah->{$key . '_berjamaah'})
                                                Dikerjakan &middot; Berjamaah 🕌
                                            @else
                                                Dikerjakan &middot; Sendiri
                                            @endif
                                        @else
                                            Belum dikerjakan
                                        @endif
                                    </p>
                                </div>
                                <div class="text-right">
                                    @if($sunnah->$key)
                                        <span class="text-[#2E7D3E] text-xl">✅</span>
                                        @if($sunnah->{$key . '_berjamaah'})
                                            <span class="text-xs text-[#27438D] block">🕌</span>
                                        @endif
                                    @else
                                        <span class="text-gray-300 text-xl">⬜</span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Kegiatan Sunnah Lainnya -->
                <div class="mt-6 pt-6 border-t border-gray-200">
                    <h3 class="text-lg font-semibold text-[#161758] mb-4">📋 Kegiatan Sunnah Lainnya</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        @foreach($poinConfig as $key => $config)
                            @continue(in_array($key, $sholatWajibKeys, true))
                            <div class="flex items-center space-x-3 p-3 bg-[#F5F5F5] rounded-lg">
                                <span class="text-xl">{{ $config['icon'] }}</span>
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-[#1B1B1B]">{{ $config['label'] }}</p>
                                    <p class="text-xs text-[#27438D]">{{ $config['poin'] }} poin</p>
                                </div>
                                <div class="text-right">
                                    @if($sunnah->$key)
                                        <span class="text-[#2E7D3E] text-xl">✅</span>
                                    @else
                                        <span class="text-gray-300 text-xl">⬜</span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Approval Form -->
                <div class="mt-6 pt-6 border-t border-gray-200">
                    <h3 class="text-lg font-semibold text-[#161758] mb-4">✏️ Approval / Assessment</h3>
                    <form action="{{ route('hr.sunnah.approve', $sunnah->id) }}" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Status</label>
                            <select name="status" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">
                                <option value="pending" {{ $sunnah->status_approval === 'pending' ? 'selected' : '' }}>⏳ Menunggu</option>
                                <option value="approved" {{ $sunnah->status_approval === 'approved' ? 'selected' : '' }}>✅ Disetujui</option>
                                <option value="rejected" {{ $sunnah->status_approval === 'rejected' ? 'selected' : '' }}>❌ Ditolak</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-[#1B1B1B] mb-1">Catatan</label>
                            <textarea name="catatan_hr" rows="3"
                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00a2e9]">{{ old('catatan_hr', $sunnah->catatan_hr) }}</textarea>
                        </div>
                        <button type="submit"
                                class="bg-[#27438D] text-white px-6 py-2 rounded-lg hover:bg-[#161758] transition-colors duration-200">
                            Simpan Approval
                        </button>
                    </form>
                </div>

                <div class="mt-6">
                    <a href="{{ route('hr.sunnah.index') }}"
                       class="bg-gray-500 text-white px-6 py-2 rounded-lg hover:bg-gray-600 transition-colors duration-200">
                        Kembali
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
