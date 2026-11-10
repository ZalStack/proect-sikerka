@extends('layouts.app')

@section('content')
<div class="flex">
    @include('layouts.sidebar')

    <div class="flex-1 min-w-0 md:ml-64 p-4 sm:p-6 lg:p-8 space-y-6">

        <!-- Header -->
        <div class="flex flex-col xl:flex-row xl:items-end justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">
                    <a href="{{ route('superadmin.dashboard') }}" class="hover:text-[#00a2e9] transition-colors">Super Admin</a>
                    <span>/</span>
                    <span class="text-gray-700">Import Karyawan</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-[#0F1245] font-['Poppins'] tracking-tight">
                    Import Massal Karyawan
                </h1>
                <p class="text-xs sm:text-sm text-gray-500 font-light mt-0.5 max-w-2xl">
                    Unggah file CSV/XLSX berisi daftar karyawan baru. Sistem menampilkan <b>preview</b> dan hasil validasi dulu — tidak ada data yang tersimpan sebelum Anda menekan tombol konfirmasi.
                </p>
            </div>
        </div>

        @include('superadmin.partials.flash')

        @if(session('import_errors') && count(session('import_errors')))
            <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4 sm:p-5">
                <p class="text-sm font-bold text-amber-800 mb-2">
                    <i class="fa-solid fa-triangle-exclamation mr-1.5"></i>Baris yang dilewati
                </p>
                <ul class="text-xs text-amber-700 space-y-1 list-disc list-inside max-h-40 overflow-y-auto">
                    @foreach(session('import_errors') as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-4 sm:gap-5">

            <!-- UPLOAD / FORM -->
            <div class="xl:col-span-1 bg-white rounded-3xl p-5 sm:p-6 shadow-sm border border-gray-100 h-fit space-y-5">
                <h2 class="text-base font-bold text-[#0F1245] flex items-center gap-2">
                    <i class="fa-solid fa-upload text-[#00a2e9]"></i> Unggah File
                </h2>

                <form action="{{ route('superadmin.karyawan-import.preview') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <div>
                        <label for="import_file" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">File (.csv / .xlsx)</label>
                        <input type="file" name="import_file" id="import_file" accept=".csv,.xlsx" required
                               class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-sm file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:bg-[#00a2e9]/10 file:text-[#00a2e9] file:text-xs file:font-bold hover:file:bg-[#00a2e9]/20">
                        @error('import_file')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
                    </div>

                    <button type="submit" class="w-full px-5 py-2.5 rounded-xl bg-[#00a2e9] hover:bg-[#008cc4] text-white text-xs font-bold transition-all shadow-md flex items-center justify-center gap-2">
                        <i class="fa-solid fa-magnifying-glass"></i> Preview & Validasi
                    </button>
                </form>

                <div class="border-t border-gray-100 pt-4 space-y-3">
                    <p class="text-xs font-bold text-gray-700 uppercase tracking-wider">Kolom wajib</p>
                    <div class="flex flex-wrap gap-1.5">
                        @foreach($columns as $col)
                            <span class="px-2 py-0.5 rounded-md bg-[#0F1245]/5 text-[10px] font-bold text-[#0F1245]">{{ $col }}</span>
                        @endforeach
                    </div>

                    <p class="text-xs font-bold text-gray-700 uppercase tracking-wider pt-1">Kolom opsional</p>
                    <div class="flex flex-wrap gap-1.5">
                        @foreach(['nik', 'golongan', 'nomor_telepon', 'jenis_kelamin', 'alamat', 'tanggal_lahir', 'tempat_lahir', 'npwp'] as $col)
                            <span class="px-2 py-0.5 rounded-md bg-gray-100 text-[10px] font-bold text-gray-500">{{ $col }}</span>
                        @endforeach
                    </div>

                    <ul class="text-[11px] text-gray-500 space-y-1.5 leading-relaxed pt-2">
                        <li>• Header boleh memakai spasi atau underscore ("Kode Pegawai" = "kode_pegawai").</li>
                        <li>• Tanggal format <b>YYYY-MM-DD</b>; status <b>Karyawan Tetap / Contract / Internship</b> (alias: Tetap, Kontrak, Magang).</li>
                        <li>• Maksimal {{ $maxRows }} baris per file, ukuran file maks 5 MB.</li>
                        <li>• Akun baru dibuat dengan <b>password acak</b> — karyawan masuk lewat fitur lupa password.</li>
                        <li>• Baris valid tetap dipisahkan dari baris error; duplikat email/kode dilewati otomatis.</li>
                    </ul>
                </div>
            </div>

            <!-- PREVIEW -->
            <div class="xl:col-span-2 bg-white rounded-3xl p-5 sm:p-6 shadow-sm border border-gray-100">
                @if(!empty($preview))
                    @php $stats = $stats ?? ['valid' => 0, 'invalid' => 0, 'total' => 0]; @endphp

                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-gray-100">
                        <div>
                            <h2 class="text-base font-bold text-[#0F1245] flex items-center gap-2">
                                <i class="fa-solid fa-table-list text-[#00a2e9]"></i>
                                Preview: {{ $filename ?? 'file' }}
                            </h2>
                            <p class="text-xs text-gray-500 mt-0.5">Periksa hasil validasi di bawah sebelum konfirmasi.</p>
                        </div>

                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="px-3 py-1 rounded-full text-xs font-extrabold bg-emerald-100 text-emerald-700">{{ $stats['valid'] }} valid</span>
                            <span class="px-3 py-1 rounded-full text-xs font-extrabold {{ $stats['invalid'] > 0 ? 'bg-rose-100 text-rose-700' : 'bg-gray-100 text-gray-500' }}">{{ $stats['invalid'] }} error</span>
                            @if(!empty($truncated))
                                <span class="px-3 py-1 rounded-full text-xs font-extrabold bg-amber-100 text-amber-700">dipotong maks {{ $maxRows }} baris</span>
                            @endif
                        </div>
                    </div>

                    @if($stats['valid'] > 0)
                        <form action="{{ route('superadmin.karyawan-import.commit') }}" method="POST" class="pt-4">
                            @csrf
                            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 p-4 rounded-2xl bg-emerald-50 border border-emerald-200">
                                <p class="text-xs sm:text-sm text-emerald-800">
                                    <i class="fa-solid fa-circle-check mr-1.5"></i>
                                    <b>{{ $stats['valid'] }} baris siap disimpan.</b>
                                    @if($stats['invalid'] > 0) {{ $stats['invalid'] }} baris error akan dilewati.@endif
                                </p>
                                <button type="submit"
                                        onclick="return confirm('Simpan {{ $stats['valid'] }} karyawan ke database? Tindakan ini tidak bisa dibatalkan.')"
                                        class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition-all shadow-md flex items-center justify-center gap-2">
                                    <i class="fa-solid fa-check"></i> Konfirmasi & Simpan
                                </button>
                            </div>
                        </form>
                    @else
                        <div class="mt-4 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-xs text-rose-700">
                            Tidak ada baris valid. Perbaiki file lalu unggah kembali.
                        </div>
                    @endif

                    <div class="mt-4 overflow-x-auto border border-gray-100 rounded-2xl">
                        <table class="w-full text-xs">
                            <thead class="bg-gray-50 text-gray-500 uppercase tracking-wider">
                                <tr>
                                    <th class="px-3 py-2.5 text-left font-bold">Baris</th>
                                    <th class="px-3 py-2.5 text-left font-bold">Status</th>
                                    <th class="px-3 py-2.5 text-left font-bold">Kode</th>
                                    <th class="px-3 py-2.5 text-left font-bold">Nama</th>
                                    <th class="px-3 py-2.5 text-left font-bold">Email</th>
                                    <th class="px-3 py-2.5 text-left font-bold">Divisi</th>
                                    <th class="px-3 py-2.5 text-left font-bold">Jabatan</th>
                                    <th class="px-3 py-2.5 text-left font-bold">Bergabung</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @foreach($preview as $row)
                                    <tr class="{{ $row['valid'] ? '' : 'bg-rose-50/60' }}">
                                        <td class="px-3 py-2 text-gray-400">{{ $row['row'] }}</td>
                                        <td class="px-3 py-2">
                                            @if($row['valid'])
                                                <span class="px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700 text-[10px] font-extrabold">VALID</span>
                                            @else
                                                <span class="px-2 py-0.5 rounded-full bg-rose-100 text-rose-700 text-[10px] font-extrabold" title="{{ implode("\n", $row['errors']) }}">ERROR</span>
                                            @endif
                                        </td>
                                        <td class="px-3 py-2 font-medium text-gray-800">{{ $row['data']['kode_pegawai'] ?: '—' }}</td>
                                        <td class="px-3 py-2 text-gray-800">{{ $row['data']['nama_lengkap'] ?: '—' }}</td>
                                        <td class="px-3 py-2 text-gray-500">{{ $row['data']['email'] ?: '—' }}</td>
                                        <td class="px-3 py-2 text-gray-500">{{ $row['data']['divisi'] ?: '—' }}</td>
                                        <td class="px-3 py-2 text-gray-500">{{ $row['data']['jabatan'] ?: '—' }}</td>
                                        <td class="px-3 py-2 text-gray-500">{{ $row['data']['tanggal_bergabung'] ?: '—' }}</td>
                                    </tr>
                                    @if(!$row['valid'])
                                        <tr class="bg-rose-50/60">
                                            <td></td>
                                            <td colspan="7" class="px-3 pb-2 text-[11px] text-rose-600">
                                                @foreach($row['errors'] as $e)
                                                    <span class="inline-block mr-3">• {{ $e }}</span>
                                                @endforeach
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <!-- EMPTY STATE -->
                    <div class="py-14 text-center">
                        <div class="w-16 h-16 mx-auto rounded-3xl bg-[#00a2e9]/10 flex items-center justify-center text-3xl text-[#00a2e9] mb-4">
                            <i class="fa-solid fa-file-csv"></i>
                        </div>
                        <h2 class="text-lg font-bold text-gray-800">Belum ada file diunggah</h2>
                        <p class="text-xs sm:text-sm text-gray-500 mt-1 max-w-md mx-auto">
                            Siapkan file CSV atau XLSX dengan header kolom sesuai daftar di samping, lalu unggah untuk melihat preview validasi sebelum data masuk ke database.
                        </p>
                        <div class="mt-5 inline-block text-left text-[11px] text-gray-500 bg-gray-50 rounded-xl p-4 font-mono">
                            <p class="font-sans font-bold text-gray-600 mb-1.5">Contoh baris:</p>
                            kode_pegawai,nama_lengkap,email,jabatan,divisi,status,tanggal_bergabung<br>
                            NIP-001,Budi Santoso,budi@kantor.com,Staff,Operasional,Karyawan Tetap,2026-01-15
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
