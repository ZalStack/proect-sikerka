@extends('layouts.app')

@section('content')
<div class="flex">
    @include('layouts.sidebar')

    <div class="flex-1 min-w-0 md:ml-64 p-4 sm:p-6 lg:p-8 space-y-6">

        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">
                    <a href="{{ route('superadmin.dashboard') }}" class="hover:text-[#00a2e9]">Super Admin</a>
                    <span>/</span>
                    <span class="text-gray-700">Laporan Terjadwal</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-[#0F1245] font-['Poppins']">Laporan Terjadwal</h1>
                <p class="text-xs sm:text-sm text-gray-500 font-light mt-0.5 max-w-2xl">
                    Rekap absensi, cuti, 7SPS, pelanggaran, dan kehadiran per divisi dibuat otomatis oleh scheduler dan bisa diunduh kapan saja.
                </p>
            </div>

            <div class="flex flex-wrap gap-2">
                <a href="{{ route('superadmin.reports.history') }}"
                   class="px-4 py-3 rounded-2xl bg-white border border-gray-200 text-gray-700 text-sm font-bold hover:bg-gray-50 transition-all flex items-center gap-2">
                    <i class="fa-solid fa-clock-rotate-left"></i> Riwayat
                </a>
                <button type="button" onclick="openCreateSchedule()"
                        class="px-5 py-3 rounded-2xl bg-[#00a2e9] hover:bg-[#0088c7] text-white text-sm font-bold transition-all shadow-lg shadow-[#00a2e9]/25 flex items-center justify-center gap-2">
                    <i class="fa-solid fa-plus"></i> Jadwal Baru
                </button>
            </div>
        </div>

        @include('superadmin.partials.flash')

        <!-- DAFTAR JADWAL -->
        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm min-w-[860px]">
                    <thead class="bg-gray-50/70">
                        <tr class="text-left text-[11px] uppercase tracking-wider text-gray-400">
                            <th class="py-3 px-5 font-bold">Nama</th>
                            <th class="py-3 px-3 font-bold">Jenis</th>
                            <th class="py-3 px-3 font-bold">Frekuensi</th>
                            <th class="py-3 px-3 font-bold">Format</th>
                            <th class="py-3 px-3 font-bold">Terakhir</th>
                            <th class="py-3 px-3 font-bold">Berikutnya</th>
                            <th class="py-3 px-5 font-bold text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($schedules as $s)
                            <tr class="hover:bg-gray-50/60 {{ $s->is_active ? '' : 'opacity-60' }}">
                                <td class="py-3 px-5">
                                    <div class="font-semibold text-gray-800 flex items-center gap-2">
                                        {{ $s->name }}
                                        @if(!$s->is_active)
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-gray-100 text-gray-500 uppercase">Nonaktif</span>
                                        @endif
                                    </div>
                                    <div class="text-[11px] text-gray-400">
                                        {{ $s->generated_reports_count }} laporan dibuat
                                        @if($s->recipients)
                                            &middot; {{ count($s->recipients) }} penerima email
                                        @endif
                                    </div>
                                </td>
                                <td class="py-3 px-3">
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-sky-100 text-sky-700 uppercase">{{ $s->typeLabel() }}</span>
                                </td>
                                <td class="py-3 px-3 text-xs text-gray-600">{{ $s->frequencyLabel() }}<br><span class="text-[11px] text-gray-400">pukul {{ $s->run_time }}</span></td>
                                <td class="py-3 px-3 text-xs text-gray-600 uppercase">{{ $s->format }}</td>
                                <td class="py-3 px-3 text-xs">
                                    @if($s->last_run_at)
                                        <span class="{{ $s->last_status === 'success' ? 'text-emerald-600' : 'text-rose-600' }} font-bold">{{ $s->statusLabel() }}</span>
                                        <br><span class="text-[11px] text-gray-400">{{ $s->last_run_at->format('d M H:i') }}</span>
                                    @else
                                        <span class="text-[11px] text-gray-400">Belum pernah</span>
                                    @endif
                                </td>
                                <td class="py-3 px-3 text-xs text-gray-600 whitespace-nowrap">
                                    {{ $s->next_run_at ? \Carbon\Carbon::parse($s->next_run_at)->format('d M Y H:i') : '-' }}
                                </td>
                                <td class="py-3 px-5">
                                    <div class="flex items-center justify-end gap-1.5 flex-wrap">
                                        <form action="{{ route('superadmin.reports.run', $s->id) }}" method="POST" class="inline"
                                              onsubmit="return confirm('Jalankan laporan "{{ $s->name }}" sekarang?')">
                                            @csrf
                                            <button type="submit" class="px-2.5 py-1.5 rounded-lg bg-emerald-50 hover:bg-emerald-500 text-emerald-700 hover:text-white text-[11px] font-bold border border-emerald-100">
                                                <i class="fa-solid fa-play mr-1"></i>Jalankan
                                            </button>
                                        </form>

                                        <button type="button"
                                                data-id="{{ $s->id }}"
                                                data-name="{{ $s->name }}"
                                                data-type="{{ $s->type }}"
                                                data-frequency="{{ $s->frequency }}"
                                                data-day_of_week="{{ $s->day_of_week }}"
                                                data-day_of_month="{{ $s->day_of_month }}"
                                                data-run_time="{{ $s->run_time }}"
                                                data-format="{{ $s->format }}"
                                                data-recipients="{{ implode(', ', (array) $s->recipients) }}"
                                                data-is_active="{{ $s->is_active ? 1 : 0 }}"
                                                onclick="openEditSchedule(this)"
                                                class="px-2.5 py-1.5 rounded-lg bg-sky-50 hover:bg-sky-100 text-sky-700 text-[11px] font-bold border border-sky-100">
                                            <i class="fa-solid fa-pen mr-1"></i>Ubah
                                        </button>

                                        <form action="{{ route('superadmin.reports.destroy', $s->id) }}" method="POST" class="inline"
                                              onsubmit="return confirm('Hapus jadwal laporan "{{ $s->name }}"?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="px-2.5 py-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-600 text-[11px] font-bold border border-rose-100">
                                                <i class="fa-solid fa-trash mr-1"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-10 text-center text-sm text-gray-400">
                                    Belum ada jadwal laporan. Klik "Jadwal Baru" untuk membuatnya.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Laporan terbaru -->
        <div class="bg-white rounded-3xl p-5 sm:p-6 shadow-sm border border-gray-100 space-y-4">
            <div>
                <h2 class="text-base font-bold text-[#0F1245] flex items-center gap-2">
                    <i class="fa-solid fa-file-lines text-[#00a2e9]"></i> 15 Laporan Terbaru
                </h2>
                <p class="text-xs text-gray-500 mt-0.5">Klik Unduh untuk mengambil file.</p>
            </div>

            <div class="overflow-x-auto rounded-2xl border border-gray-100">
                <table class="w-full text-xs sm:text-sm text-gray-600 min-w-[640px]">
                    <thead class="bg-gray-50/80 font-semibold text-xs border-b border-gray-100">
                        <tr class="text-left">
                            <th class="px-4 py-3">Waktu</th>
                            <th class="px-4 py-3">Laporan</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Keterangan</th>
                            <th class="px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($recentReports as $r)
                            <tr class="hover:bg-blue-50/25">
                                <td class="px-4 py-3 whitespace-nowrap">{{ $r->created_at?->format('d M Y H:i') }}</td>
                                <td class="px-4 py-3">
                                    <span class="font-semibold text-gray-800">{{ $r->scheduledReport?->name ?? $r->type }}</span>
                                    <br><span class="text-[11px] text-gray-400 font-mono">{{ $r->filename }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $r->status === 'success' ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">
                                        {{ $r->statusLabel() }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-[11px] text-gray-500 max-w-[320px]">{{ $r->message }}</td>
                                <td class="px-4 py-3 text-right whitespace-nowrap">
                                    @if($r->status === 'success' && $r->filename !== '-')
                                        <a href="{{ route('superadmin.reports.download', $r->id) }}"
                                           class="px-3 py-1.5 rounded-lg bg-[#00a2e9]/10 hover:bg-[#00a2e9] text-[#0077b6] hover:text-white text-[11px] font-bold transition-all inline-flex items-center gap-1">
                                            <i class="fa-solid fa-download"></i> Unduh
                                        </a>
                                        <span class="ml-2 text-[10px] text-gray-400">{{ $r->sizeHuman() }}</span>
                                    @else
                                        <span class="text-[11px] text-gray-400">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-4 py-8 text-center text-xs text-gray-400">Belum ada laporan dibuat.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<!-- MODAL TAMBAH / UBAH -->
<div id="modal-schedule" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-xl p-6 sm:p-8 space-y-4 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between">
            <h3 class="text-lg font-extrabold text-[#0F1245]" id="modal-schedule-title">Jadwal Laporan Baru</h3>
            <button type="button" onclick="this.closest('.fixed').classList.add('hidden')" class="text-gray-400 hover:text-gray-700 text-xl">&times;</button>
        </div>

        <form id="form-schedule" method="POST" class="space-y-4">
            @csrf
            <input type="hidden" name="_method" value="POST" id="schedule-method">

            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Nama Laporan</label>
                <input type="text" name="name" id="f-name" required maxlength="120"
                       class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-[#00a2e9] focus:ring-2 focus:ring-[#00a2e9]/20 text-sm">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Jenis</label>
                    <select name="type" id="f-type" required class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-[#00a2e9] focus:ring-2 focus:ring-[#00a2e9]/20 text-sm">
                        @foreach($types as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Format</label>
                    <select name="format" id="f-format" required class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-[#00a2e9] focus:ring-2 focus:ring-[#00a2e9]/20 text-sm">
                        @foreach($formats as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Frekuensi</label>
                    <select name="frequency" id="f-frequency" required class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-[#00a2e9] focus:ring-2 focus:ring-[#00a2e9]/20 text-sm">
                        @foreach($frequencies as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div id="wrap-day-of-week">
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Hari</label>
                    <select name="day_of_week" id="f-day_of_week" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-[#00a2e9] focus:ring-2 focus:ring-[#00a2e9]/20 text-sm">
                        @foreach($days as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div id="wrap-day-of-month" class="hidden">
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Tanggal (1-28)</label>
                    <input type="number" name="day_of_month" id="f-day_of_month" min="1" max="28"
                           class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-[#00a2e9] focus:ring-2 focus:ring-[#00a2e9]/20 text-sm">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Jam Eksekusi</label>
                    <input type="time" name="run_time" id="f-run_time" value="07:00" required
                           class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-[#00a2e9] focus:ring-2 focus:ring-[#00a2e9]/20 text-sm">
                </div>
                <div class="flex items-end pb-1">
                    <label class="flex items-center gap-2 text-sm text-gray-700">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" id="f-is_active" checked
                               class="w-4 h-4 rounded border-gray-300 text-[#00a2e9] focus:ring-[#00a2e9]">
                        <span class="font-semibold">Jadwal aktif</span>
                    </label>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Penerima Email (opsional, pisahkan dengan koma)</label>
                <input type="text" name="recipients" id="f-recipients"
                       placeholder="hr@example.com, manager@example.com"
                       class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-[#00a2e9] focus:ring-2 focus:ring-[#00a2e9]/20 text-sm">
                <p class="text-[11px] text-gray-400 mt-1">File laporan akan di-attach ke email penerima setelah dibuat.</p>
            </div>

            @if($errors->any())
                <ul class="text-xs text-rose-600 list-disc list-inside space-y-0.5">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            @endif

            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="this.closest('.fixed').classList.add('hidden')" class="px-5 py-2.5 rounded-xl border border-gray-200 text-gray-600 text-xs font-bold">Batal</button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-[#00a2e9] hover:bg-[#0088c7] text-white text-xs font-bold shadow-md">Simpan Jadwal</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const freq = document.getElementById('f-frequency');
    const wrapDow = document.getElementById('wrap-day-of-week');
    const wrapDom = document.getElementById('wrap-day-of-month');

    function syncFrequency() {
        if (!freq || !wrapDow || !wrapDom) return;
        wrapDow.classList.toggle('hidden', freq.value !== 'weekly');
        wrapDom.classList.toggle('hidden', freq.value !== 'monthly');
    }

    if (freq) {
        freq.addEventListener('change', syncFrequency);
        syncFrequency();
    }

    window.openEditSchedule = function (el) {
        document.getElementById('form-schedule').action = '{{ url('superadmin/laporan-terjadwal') }}/' + el.dataset.id;
        document.getElementById('schedule-method').value = 'PUT';
        document.getElementById('modal-schedule-title').textContent = 'Ubah Jadwal Laporan';
        document.getElementById('f-name').value = el.dataset.name || '';
        document.getElementById('f-type').value = el.dataset.type || 'absensi';
        document.getElementById('f-format').value = el.dataset.format || 'xlsx';
        document.getElementById('f-frequency').value = el.dataset.frequency || 'monthly';
        document.getElementById('f-day_of_week').value = el.dataset.day_of_week || '1';
        document.getElementById('f-day_of_month').value = el.dataset.day_of_month || '1';
        document.getElementById('f-run_time').value = el.dataset.run_time || '07:00';
        document.getElementById('f-recipients').value = el.dataset.recipients || '';
        document.getElementById('f-is_active').checked = el.dataset.is_active === '1';
        syncFrequency();
        document.getElementById('modal-schedule').classList.remove('hidden');
    };

    window.openCreateSchedule = function () {
        document.getElementById('form-schedule').action = '{{ route('superadmin.reports.store') }}';
        document.getElementById('schedule-method').value = 'POST';
        document.getElementById('modal-schedule-title').textContent = 'Jadwal Laporan Baru';
        document.getElementById('form-schedule').reset();
        syncFrequency();
        document.getElementById('modal-schedule').classList.remove('hidden');
    };
});
</script>
@endsection
