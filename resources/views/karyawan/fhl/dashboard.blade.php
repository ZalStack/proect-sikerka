@extends('layouts.app')

@section('content')
<div class="flex">
    @include('layouts.sidebar')
    <div class="ml-64 flex-1 p-6">
        <div class="mb-6">
            <h1 class="text-2xl font-bold font-['Montserrat'] text-[#161758]">FHL - Friday Healthy Lifestyle</h1>
            <p class="text-[#27438D]">Absensi kegiatan FHL</p>
        </div>

        @if(session('success'))
            <div class="bg-[#2E7D3E] text-white p-4 rounded-lg mb-4">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-[#ec1d1d] text-white p-4 rounded-lg mb-4">
                {{ session('error') }}
            </div>
        @endif

        <!-- Status Hari Ini -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-[#161758]">
                        {{ $isFriday ? '📅 Hari ini adalah Jumat!' : '📅 Hari ini bukan Jumat' }}
                    </h2>
                    <p class="text-sm text-[#1B1B1B] mt-1">
                        {{ Carbon\Carbon::now()->locale('id')->isoFormat('dddd, D MMMM YYYY') }}
                    </p>
                </div>
                <div>
                    @if($todayAbsensi)
                        <span class="px-4 py-2 rounded-full text-sm font-medium bg-[#2E7D3E] text-white">
                            ✅ Sudah Absen
                        </span>
                    @elseif($isFriday)
                        <span class="px-4 py-2 rounded-full text-sm font-medium bg-[#FCC626] text-[#1B1B1B]">
                            ⏳ Belum Absen
                        </span>
                    @else
                        <span class="px-4 py-2 rounded-full text-sm font-medium bg-gray-300 text-gray-600">
                            ⛔ Tidak Ada Kegiatan
                        </span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Form Absen FHL -->
        @if($isFriday && !$todayAbsensi)
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-lg font-semibold text-[#161758] mb-4">📸 Absen FHL</h2>
            <form id="fhlForm" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-[#1B1B1B] mb-2">
                        Upload Bukti Foto Kegiatan FHL <span class="text-[#ec1d1d]">*</span>
                    </label>
                    <div class="flex items-center justify-center w-full">
                        <label class="flex flex-col items-center justify-center w-full h-48 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 hover:bg-gray-100 transition-colors duration-200">
                            <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                <svg class="w-10 h-10 text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                <p class="mb-2 text-sm text-gray-500"><span class="font-semibold">Klik untuk upload</span> atau drag and drop</p>
                                <p class="text-xs text-gray-500">PNG, JPG (MAX. 2MB)</p>
                            </div>
                            <input type="file" name="foto_bukti" id="foto_bukti" class="hidden" accept="image/*" required>
                        </label>
                    </div>
                    <div id="previewContainer" class="mt-3 hidden">
                        <img id="previewImage" src="#" alt="Preview" class="w-48 h-48 object-cover rounded-lg border border-gray-200">
                        <button type="button" onclick="removeImage()" class="mt-2 text-sm text-[#ec1d1d] hover:text-red-700">
                            Hapus Foto
                        </button>
                    </div>
                    <p id="fileError" class="mt-1 text-sm text-[#ec1d1d] hidden"></p>
                </div>
                <button type="submit" id="submitBtn"
                        class="bg-[#2E7D3E] text-white px-6 py-3 rounded-lg hover:bg-[#009a4b] transition-colors duration-200 font-semibold w-full md:w-auto">
                    📤 Kirim Absen FHL
                </button>
            </form>
        </div>
        @endif

        <!-- Statistik Bulan Ini -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-lg font-semibold text-[#161758] mb-4">Statistik Bulan Ini</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-[#F5F5F5] rounded-lg p-4 text-center">
                    <p class="text-2xl font-bold text-[#161758]">{{ $statistik['total_jumat'] }}</p>
                    <p class="text-sm text-[#1B1B1B]">Total Jumat</p>
                </div>
                <div class="bg-[#2E7D3E] text-white rounded-lg p-4 text-center">
                    <p class="text-2xl font-bold">{{ $statistik['hadir'] }}</p>
                    <p class="text-sm">Hadir</p>
                </div>
                <div class="bg-[#F5F5F5] rounded-lg p-4 text-center">
                    <p class="text-2xl font-bold text-[#161758]">{{ $statistik['total_jumat'] - $statistik['hadir'] }}</p>
                    <p class="text-sm text-[#1B1B1B]">Belum Hadir</p>
                </div>
            </div>
        </div>

        <!-- Daftar Hari Jumat dan Absensi -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-lg font-semibold text-[#161758] mb-4">📋 Daftar Absensi FHL Bulan Ini</h2>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-[#F5F5F5]">
                            <th class="px-4 py-2 text-left text-sm font-semibold text-[#1B1B1B]">Tanggal</th>
                            <th class="px-4 py-2 text-left text-sm font-semibold text-[#1B1B1B]">Hari</th>
                            <th class="px-4 py-2 text-left text-sm font-semibold text-[#1B1B1B]">Check-in</th>
                            <th class="px-4 py-2 text-left text-sm font-semibold text-[#1B1B1B]">Status</th>
                            <th class="px-4 py-2 text-left text-sm font-semibold text-[#1B1B1B]">Bukti</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($fridays as $friday)
                            @php
                                $absen = $absensi->firstWhere('tanggal', $friday->format('Y-m-d'));
                            @endphp
                            <tr class="border-b border-gray-200">
                                <td class="px-4 py-2 text-sm">{{ $friday->format('d-m-Y') }}</td>
                                <td class="px-4 py-2 text-sm">Jumat</td>
                                <td class="px-4 py-2 text-sm">
                                    {{ $absen && $absen->check_in ? Carbon\Carbon::parse($absen->check_in)->format('H:i') : '-' }}
                                </td>
                                <td class="px-4 py-2 text-sm">
                                    @if($absen)
                                        <span class="px-2 py-1 rounded-full text-xs font-medium bg-[#2E7D3E] text-white">
                                            ✅ Hadir
                                        </span>
                                    @else
                                        <span class="px-2 py-1 rounded-full text-xs font-medium bg-gray-300 text-gray-600">
                                            ⬜ Belum
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-2 text-sm">
                                    @if($absen && $absen->foto_bukti)
                                        <a href="{{ Storage::url($absen->foto_bukti) }}" target="_blank"
                                           class="text-[#00a2e9] hover:text-[#27438D]">
                                            📷 Lihat
                                        </a>
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-4 text-center text-sm text-[#1B1B1B]">
                                    Tidak ada hari Jumat di bulan ini
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

// Preview Image
document.getElementById('foto_bukti').addEventListener('change', function(e) {
    const file = e.target.files[0];
    const previewContainer = document.getElementById('previewContainer');
    const previewImage = document.getElementById('previewImage');
    const fileError = document.getElementById('fileError');

    if (file) {
        // Cek ukuran file (max 2MB = 2 * 1024 * 1024 bytes)
        if (file.size > 2 * 1024 * 1024) {
            fileError.textContent = 'Ukuran file maksimal 2MB!';
            fileError.classList.remove('hidden');
            this.value = '';
            previewContainer.classList.add('hidden');
            return;
        }

        fileError.classList.add('hidden');
        const reader = new FileReader();
        reader.onload = function(e) {
            previewImage.src = e.target.result;
            previewContainer.classList.remove('hidden');
        };
        reader.readAsDataURL(file);
    }
});

function removeImage() {
    document.getElementById('foto_bukti').value = '';
    document.getElementById('previewContainer').classList.add('hidden');
    document.getElementById('fileError').classList.add('hidden');
}

// Form Submit
document.getElementById('fhlForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    const btn = document.getElementById('submitBtn');
    btn.disabled = true;
    btn.textContent = '⏳ Memproses...';

    fetch('{{ route("karyawan.fhl.checkin") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: '✅ Absen FHL Berhasil!',
                html: `
                    <div class="text-left">
                        <p><strong>Waktu:</strong> ${data.data.waktu}</p>
                        <p><strong>Tanggal:</strong> ${data.data.tanggal}</p>
                        <p><strong>Status:</strong> Hadir</p>
                    </div>
                `,
                timer: 3000,
                showConfirmButton: true,
                confirmButtonColor: '#2E7D3E'
            }).then(() => {
                location.reload();
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Absen Gagal!',
                text: data.message,
                confirmButtonColor: '#ec1d1d'
            });
            btn.disabled = false;
            btn.textContent = '📤 Kirim Absen FHL';
        }
    })
    .catch(error => {
        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: 'Terjadi kesalahan pada server',
            confirmButtonColor: '#ec1d1d'
        });
        btn.disabled = false;
        btn.textContent = '📤 Kirim Absen FHL';
    });
});
</script>
@endsection
