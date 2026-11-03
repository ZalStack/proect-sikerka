{{--
    views/layouts/superadmin-theme.blade.php
    ===========================================================================
    TEMA "DARK COMMAND CENTER" — KHUSUS ROLE SUPER ADMIN
    ---------------------------------------------------------------------------
    File ini hanya di-include oleh layouts/app.blade.php ketika role = superadmin.

    ATURAN MAINTENANCE (penting):
      1. SEMUA rule di bawah di-scope ke `body.sa-theme` / `.sa-theme ...`
         sehingga tampilan HRD & Karyawan TIDAK PERNAH ikut berubah.
      2. Markup halaman TIDAK disentuh sama sekali — form, name, id, route,
         JavaScript, dan seluruh fitur tetap persis seperti semula.
      3. Untuk mengganti warna, cukup ubah variabel di blok "0. TOKEN".
      4. Untuk menambah gaya baru, selalu awali selektor dengan `.sa-theme`.
      5. Override utility Tailwind memakai 2 class (contoh `.sa-theme .bg-white`)
         sehingga spesifitasnya (0,2,0) selalu mengalahkan utility aslinya (0,1,0).
         Variants dengan pseudo-class (hover/focus) perlu ditulis ulang di blok
         "9. HOVER & FOCUS" agar tidak kalah oleh aturan base di atasnya.
    ===========================================================================
--}}
<style>
/* =========================================================================
   0. TOKEN WARNA — ubah di sini untuk ganti nuansa seluruh halaman
   ========================================================================= */
body.sa-theme {
    color-scheme: dark; /* kontrol native (scrollbar, date picker, select) jadi gelap */

    --sa-ink:      #EAF0FF;   /* teks utama            */
    --sa-ink-2:    #C4CDE8;   /* teks sekunder         */
    --sa-ink-3:    #A6B0CC;   /* teks lemah            */
    --sa-ink-4:    #7C87A8;   /* teks paling lemah     */

    --sa-surface:  rgba(255,255,255,.055);  /* kartu / panel      */
    --sa-surface-2:rgba(255,255,255,.085);  /* kotak info         */
    --sa-surface-3:rgba(255,255,255,.130);  /* elemen menonjol    */
    --sa-line:     rgba(255,255,255,.10);   /* garis tipis        */
    --sa-line-2:   rgba(255,255,255,.16);   /* garis lebih tegas  */

    --sa-gold:     #FCC626;
    --sa-gold-2:   #FFD86B;
    --sa-cyan:     #00A2E9;
    --sa-violet:   #8B5CF6;

    color: var(--sa-ink);
    background-color: #05081A;
}

/* Latar dekoratif (di-render khusus oleh layouts/app.blade.php) */
.sa-bg-glow { position: absolute; border-radius: 9999px; filter: blur(90px); pointer-events: none; }
.sa-bg-glow-1 { top: -18rem; left: -14rem; width: 44rem; height: 44rem; background: rgba(139,92,246,.22); }
.sa-bg-glow-2 { top: -12rem; right: -16rem; width: 40rem; height: 40rem; background: rgba(252,198,38,.14); }
.sa-bg-glow-3 { bottom: -20rem; left: 30%;  width: 46rem; height: 46rem; background: rgba(0,162,233,.16); }
.sa-bg-grid {
    position: absolute; inset: 0;
    background-image:
        linear-gradient(rgba(255,255,255,.045) 1px, transparent 1px),
        linear-gradient(90deg, rgba(255,255,255,.045) 1px, transparent 1px);
    background-size: 46px 46px;
    mask-image: radial-gradient(ellipse 90% 70% at 50% 20%, #000 30%, transparent 85%);
    -webkit-mask-image: radial-gradient(ellipse 90% 70% at 50% 20%, #000 30%, transparent 85%);
}
.sa-bg-vignette {
    position: absolute; inset: 0;
    background: radial-gradient(ellipse at 50% 0%, transparent 40%, rgba(3,5,16,.75) 100%);
}

/* =========================================================================
   1. TIPOGRAFI
   ========================================================================= */
.sa-theme .text-white { color: #FFFFFF; }
.sa-theme .text-gray-900  { color: #F3F6FF; }
.sa-theme .text-gray-800  { color: #E7ECFB; }
.sa-theme .text-gray-700  { color: #CBD4EC; }
.sa-theme .text-gray-600  { color: #AAB5D2; }
.sa-theme .text-gray-500  { color: #8D99B9; }
.sa-theme .text-gray-400  { color: #7C87A8; }
.sa-theme .text-gray-300  { color: #66718F; }
.sa-theme .text-slate-800 { color: #EAF0FF; }
.sa-theme .text-slate-700 { color: #CBD5E1; }
.sa-theme .text-slate-400 { color: #94A3B8; }
.sa-theme .text-blue-500   { color: #79A9FF; }
.sa-theme .text-indigo-500 { color: #A5B4FC; }

.sa-theme .text-\[\#0F1245\]   { color: #F3F6FF; }
.sa-theme .text-\[\#161758\]   { color: #E7ECFB; }
/* Catatan: `text-[#1B1B1B]` TIDAK di-override - warnanya hanya dipakai di atas
   latar emas (tombol & badge) sehingga harus tetap gelap. */
.sa-theme .text-\[\#27438D\]   { color: #9EC8FF; }
.sa-theme .text-\[\#0077b6\]   { color: #6FD3FF; }
.sa-theme .text-\[\#d49b06\]   { color: #FFD34E; }
.sa-theme .text-\[\#B8860B\]   { color: #FFCF45; }
.sa-theme .text-\[\#2E7D3E\]   { color: #4ADE80; }
.sa-theme .text-\[\#ec1d1d\]   { color: #FF7B7B; }

/* Warna teks "gelap" yang dipasangkan dengan badge berlatar terang */
.sa-theme .text-emerald-900 { color: #B7F7DF; }
.sa-theme .text-emerald-800 { color: #8CF0CB; }
.sa-theme .text-emerald-700 { color: #5CE6B0; }
.sa-theme .text-emerald-600 { color: #34DDA2; }
.sa-theme .text-rose-900    { color: #FFD3D8; }
.sa-theme .text-rose-800    { color: #FFB0B8; }
.sa-theme .text-rose-700    { color: #FF8D99; }
.sa-theme .text-rose-600    { color: #FF6A78; }
.sa-theme .text-amber-900   { color: #FFEDC2; }
.sa-theme .text-amber-800   { color: #FFDF9A; }
.sa-theme .text-amber-700   { color: #FFCB63; }
.sa-theme .text-amber-600   { color: #FFB63B; }
.sa-theme .text-blue-800    { color: #C3DAFF; }
.sa-theme .text-blue-700    { color: #9FC4FF; }
.sa-theme .text-blue-600    { color: #79A9FF; }
.sa-theme .text-purple-900  { color: #EFD9FF; }
.sa-theme .text-purple-700  { color: #CFA6FF; }
.sa-theme .text-purple-600  { color: #C58BFF; }
.sa-theme .text-indigo-700  { color: #A9B7FF; }
.sa-theme .text-indigo-600  { color: #9AA9FF; }
.sa-theme .text-yellow-800  { color: #FFE9A8; }
.sa-theme .text-cyan-700    { color: #7BE0FF; }
.sa-theme .text-sky-700     { color: #8FDBFF; }
.sa-theme .text-teal-700    { color: #7FE3D4; }
.sa-theme .text-violet-700  { color: #C9A2FF; }

/* =========================================================================
   2. LATAR PERMUKAAN (kartu, panel, kotak info, tabel)
   ========================================================================= */
.sa-theme .bg-white   { background-color: var(--sa-surface); }
.sa-theme .bg-gray-50,
.sa-theme .bg-gray-50\/50,
.sa-theme .bg-gray-50\/60,
.sa-theme .bg-gray-50\/70,
.sa-theme .bg-gray-50\/80 { background-color: rgba(255,255,255,.045); }
/* Item notifikasi & kartu info berlatar biru sangat terang */
.sa-theme .bg-\[\#F0F9FF\] { background-color: rgba(0,162,233,.10); }
.sa-theme .hover\:bg-\[\#E4F4FE\]:hover { background-color: rgba(0,162,233,.16); }
.sa-theme .bg-gray-100 { background-color: var(--sa-surface-2); }
.sa-theme .bg-gray-200 { background-color: var(--sa-surface-3); }
.sa-theme .bg-gray-300 { background-color: rgba(255,255,255,.18); }
.sa-theme .bg-\[\#161758\]  { background-color: rgba(255,255,255,.12); }

.sa-theme .bg-\[\#F5F5F5\] { background-color: rgba(255,255,255,.07); }
.sa-theme .bg-\[\#f8faff\] { background-color: rgba(255,255,255,.05); }

/* Badge berlatar pastel → translasi gelap agar kontras tetap terjaga */
.sa-theme .bg-emerald-50  { background-color: rgba(16,185,129,.13); }
.sa-theme .bg-emerald-50\/60 { background-color: rgba(16,185,129,.12); }
.sa-theme .bg-emerald-100 { background-color: rgba(16,185,129,.19); }
.sa-theme .bg-rose-50     { background-color: rgba(244,63,94,.13); }
.sa-theme .bg-rose-50\/20,
.sa-theme .bg-rose-50\/40 { background-color: rgba(244,63,94,.12); }
.sa-theme .bg-rose-100    { background-color: rgba(244,63,94,.19); }
.sa-theme .bg-amber-50    { background-color: rgba(245,158,11,.13); }
.sa-theme .bg-amber-100   { background-color: rgba(245,158,11,.19); }
.sa-theme .bg-amber-200   { background-color: rgba(251,191,36,.22); }
.sa-theme .bg-blue-50,
.sa-theme .bg-blue-50\/25,
.sa-theme .bg-blue-50\/30 { background-color: rgba(59,130,246,.15); }
.sa-theme .bg-blue-100    { background-color: rgba(59,130,246,.21); }
.sa-theme .bg-purple-50   { background-color: rgba(168,85,247,.15); }
.sa-theme .bg-purple-100  { background-color: rgba(168,85,247,.21); }
.sa-theme .bg-purple-200\/80 { background-color: rgba(168,85,247,.27); }
.sa-theme .bg-indigo-50   { background-color: rgba(99,102,241,.15); }
.sa-theme .bg-yellow-200  { background-color: rgba(250,204,21,.22); }

/* Tint navy/hijau merah bawaan → menyesuaikan permukaan gelap */
.sa-theme .bg-\[\#0F1245\]\/5,
.sa-theme .bg-\[\#0F1245\]\/10,
.sa-theme .bg-\[\#0F1245\]\/15 { background-color: rgba(255,255,255,.06); }
.sa-theme .bg-\[\#27438D\]\/10 { background-color: rgba(80,110,220,.22); }
.sa-theme .bg-\[\#2E7D3E\]\/10 { background-color: rgba(46,125,62,.24); }
.sa-theme .bg-\[\#ec1d1d\]\/10 { background-color: rgba(236,29,29,.22); }

/* Panel terminal / blok kode tetap gelap pekat (warna teksnya dari markup) */
.sa-theme pre { background-color: #0A0F30; }

/* =========================================================================
   3. GARIS, BORDER & PEMBATAS
   ========================================================================= */
.sa-theme .border-gray-100,
.sa-theme .border-slate-100,
.sa-theme .border-gray-200,
.sa-theme .border-gray-200\/80 { border-color: var(--sa-line); }
.sa-theme .border-gray-300     { border-color: var(--sa-line-2); }
.sa-theme .border-gray-50      { border-color: var(--sa-line); }
.sa-theme .divide-gray-100 > :not([hidden]) ~ :not([hidden]) { border-color: var(--sa-line); }

.sa-theme .border-emerald-100,
.sa-theme .border-emerald-200,
.sa-theme .border-emerald-200\/70 { border-color: rgba(16,185,129,.34); }
.sa-theme .border-rose-100,
.sa-theme .border-rose-200        { border-color: rgba(244,63,94,.34); }
.sa-theme .border-amber-100       { border-color: rgba(245,158,11,.34); }
.sa-theme .border-amber-200       { border-color: rgba(245,158,11,.42); }
.sa-theme .border-amber-300       { border-color: rgba(245,158,11,.50); }
.sa-theme .border-blue-100,
.sa-theme .border-blue-200        { border-color: rgba(59,130,246,.34); }
.sa-theme .border-purple-100,
.sa-theme .border-purple-200,
.sa-theme .border-purple-200\/70  { border-color: rgba(168,85,247,.34); }
.sa-theme .border-indigo-100      { border-color: rgba(99,102,241,.34); }

.sa-theme .border-\[\#0F1245\]\/15,
.sa-theme .border-\[\#161758\]    { border-color: var(--sa-line); }

/* =========================================================================
   4. TABEL
   ========================================================================= */
.sa-theme thead.bg-gray-50\/80,
.sa-theme thead.bg-gray-50       { background-color: rgba(255,255,255,.05); }
.sa-theme tbody tr:hover         { background-color: rgba(0,162,233,.07); }

/* =========================================================================
   5. FORMULIR (select, input, textarea, checkbox)
   ========================================================================= */
.sa-theme :where(input:not([type="checkbox"]):not([type="radio"]):not([type="hidden"]):not([type="range"]):not([type="file"]), select, textarea) {
    background-color: rgba(255,255,255,.06);
    border-color: var(--sa-line-2);
    color: var(--sa-ink);
}
/* `focus:bg-white` dari utility Tailwind tidak boleh mengubah field jadi putih */
.sa-theme .focus\:bg-white:focus { background-color: rgba(255,255,255,.11); }
.sa-theme :where(input:not([type="checkbox"]):not([type="radio"]):not([type="hidden"])::placeholder,
                 textarea::placeholder) { color: #6D7898; }
.sa-theme :where(input[type="date"], input[type="time"], input[type="datetime-local"]) { color-scheme: dark; }

.sa-theme :where(input[type="checkbox"], input[type="radio"]) {
    accent-color: var(--sa-cyan);
    background-color: rgba(255,255,255,.08);
    border-color: rgba(255,255,255,.35);
}
.sa-theme :where(input[type="checkbox"], input[type="radio"]):checked {
    background-color: var(--sa-cyan);
    border-color: var(--sa-cyan);
}

/* =========================================================================
   6. GRADASI (info card yang memakai from-white / to-terang)
   ========================================================================= */
.sa-theme .from-white,
.sa-theme .from-slate-50,
.sa-theme .from-\[\#f8faff\] { --tw-gradient-from: rgba(255,255,255,.10); }
.sa-theme .to-white          { --tw-gradient-to: rgba(255,255,255,.03); }
.sa-theme .to-amber-50\/40   { --tw-gradient-to: rgba(252,198,38,.14); }
.sa-theme .to-rose-50\/40,
.sa-theme .to-rose-50\/30    { --tw-gradient-to: rgba(244,63,94,.14); }
.sa-theme .to-emerald-50\/40 { --tw-gradient-to: rgba(16,185,129,.14); }
.sa-theme .to-gray-50        { --tw-gradient-to: rgba(255,255,255,.03); }
.sa-theme .to-teal-50        { --tw-gradient-to: rgba(20,184,166,.14); }
.sa-theme .to-indigo-50      { --tw-gradient-to: rgba(99,102,241,.16); }
.sa-theme .to-yellow-100     { --tw-gradient-to: rgba(250,204,21,.16); }

/* Header navy → gradasi command-center (navy → violet) */
.sa-theme .from-\[\#0F1245\] { --tw-gradient-from: #141A4E; }
.sa-theme .to-\[\#27438D\]   { --tw-gradient-to: #4B39A8; }
.sa-theme .via-\[\#161758\]  { --tw-gradient-stops: var(--tw-gradient-from), #2C2270, var(--tw-gradient-to); }

/* =========================================================================
   7. TOMBOL & ELEMEN AKSI
   ========================================================================= */
/* Tombol gelap (`bg-[#0F1245] text-white`) jadi emas agar menonjol di tema gelap */
.sa-theme .bg-\[\#0F1245\].text-white {
    background-color: var(--sa-gold);
    color: #0B1030;
}
.sa-theme .bg-\[\#0F1245\].text-white:hover { background-color: var(--sa-gold-2); }

/* Elemen berlatar EMAS harus tetap memakai teks gelap (jangan ikut jadi terang) */
.sa-theme .bg-\[\#FCC626\].text-\[\#0F1245\],
.sa-theme .bg-\[\#FCC626\].text-\[\#1B1B1B\],
.sa-theme .bg-\[\#FCC626\].text-\[\#161758\],
.sa-theme .from-\[\#FCC626\].to-\[\#f0a500\].text-\[\#0F1245\],
.sa-theme .bg-gradient-to-br.from-\[\#FCC626\].text-\[\#0F1245\],
.sa-theme .bg-\[\#FCC626\] .text-\[\#0F1245\],
.sa-theme .bg-\[\#FCC626\] .text-\[\#1B1B1B\],
.sa-theme .from-\[\#FCC626\] .text-\[\#0F1245\],
.sa-theme .from-\[\#FCC626\] .text-\[\#1B1B1B\] { color: #0B1030; }

/* Tombol sekunder bertema navy di halaman profil tetap kontras */
.sa-theme .bg-\[\#27438D\] { background-color: #2F4BD8; }
.sa-theme .bg-\[\#27438D\]:hover { background-color: #2440BC; }
.sa-theme .hover\:bg-\[\#27438D\]:hover { background-color: #3E5BE5; }

/* =========================================================================
   8. FOCUS & SCROLLBAR
   ========================================================================= */
.sa-theme ::-webkit-scrollbar-track { background: rgba(255,255,255,.03); }
.sa-theme ::-webkit-scrollbar-thumb {
    background: linear-gradient(180deg, rgba(252,198,38,.55), rgba(139,92,246,.45));
    border: 2px solid transparent;
    background-clip: padding-box;
}
.sa-theme ::-webkit-scrollbar-thumb:hover {
    background: linear-gradient(180deg, #FCC626, #8B5CF6);
    background-clip: padding-box;
}
.sa-theme :focus-visible {
    box-shadow: 0 0 0 3px rgba(252,198,38,.40);
}
.sa-theme ::selection { background: rgba(252,198,38,.30); }
.sa-theme .focus\:ring-\[\#27438D\]:focus { --tw-ring-color: rgba(0,162,233,.35); }

/* =========================================================================
   9. HOVER & FOCUS — ditulis ulang agar tidak kalah oleh aturan base di atas
   ========================================================================= */
.sa-theme .hover\:text-white:hover          { color: #FFFFFF; }
.sa-theme .hover\:text-gray-800:hover       { color: #E7ECFB; }
.sa-theme .hover\:text-gray-700:hover       { color: #CBD4EC; }
.sa-theme .hover\:text-gray-600:hover       { color: #AAB5D2; }
.sa-theme .hover\:text-amber-900:hover      { color: #FFEDC2; }
.sa-theme .hover\:text-emerald-900:hover    { color: #B7F7DF; }
.sa-theme .hover\:text-rose-900:hover       { color: #FFD3D8; }
.sa-theme .hover\:text-rose-700:hover       { color: #FF8D99; }
.sa-theme .hover\:text-rose-600:hover       { color: #FF6A78; }
.sa-theme .hover\:text-\[\#27438D\]:hover   { color: #9EC8FF; }
.sa-theme .group:hover .group-hover\:text-\[\#27438D\] { color: #9EC8FF; }

.sa-theme .hover\:bg-gray-50:hover,
.sa-theme .hover\:bg-gray-50\/50:hover,
.sa-theme .hover\:bg-gray-50\/60:hover,
.sa-theme .hover\:bg-gray-50\/80:hover      { background-color: rgba(255,255,255,.06); }
.sa-theme .hover\:bg-gray-100:hover         { background-color: var(--sa-surface-2); }
.sa-theme .hover\:bg-gray-200:hover         { background-color: var(--sa-surface-3); }
.sa-theme .hover\:bg-gray-300:hover         { background-color: rgba(255,255,255,.20); }
.sa-theme .hover\:bg-gray-600:hover         { background-color: #4B5570; }
.sa-theme .hover\:bg-blue-50:hover,
.sa-theme .hover\:bg-blue-50\/25:hover,
.sa-theme .hover\:bg-blue-50\/30:hover      { background-color: rgba(59,130,246,.20); }
.sa-theme .hover\:bg-rose-50:hover          { background-color: rgba(244,63,94,.16); }
.sa-theme .hover\:bg-rose-100:hover         { background-color: rgba(244,63,94,.24); }
.sa-theme .hover\:bg-emerald-100:hover      { background-color: rgba(16,185,129,.24); }
.sa-theme .hover\:bg-amber-50:hover         { background-color: rgba(245,158,11,.16); }
.sa-theme .hover\:bg-\[\#F5F5F5\]:hover     { background-color: rgba(255,255,255,.09); }
.sa-theme .hover\:bg-\[\#f8faff\]:hover     { background-color: rgba(255,255,255,.08); }
/* tombol abu-abu yang berubah navy saat hover (log detail) */
.sa-theme .hover\:bg-\[\#0F1245\]:hover     { background-color: #111742; }

/* =========================================================================
   10. FOOTER — dipisah dari warna sidebar agar tetap terbaca
   ========================================================================= */
.sa-theme footer {
    background-color: #040615;
    border-top: 1px solid rgba(252,198,38,.18);
}
/* Footer juga memakai `bg-[#0F1245] text-white` — jangan ikut berubah jadi emas
   (spesifiksinya harus mengalahkan aturan tombol emas di blok 7) */
.sa-theme footer.bg-\[\#0F1245\].text-white {
    background-color: #040615;
    color: #FFFFFF;
}

/* =========================================================================
   11. KOMPONEN KUSTOM SUPER ADMIN (sidebar, topbar, kartu user)
   —— kelas `sa-*` hanya dipakai pada markup khusus role Super Admin
   ========================================================================= */

/* --- Sidebar --- */
.sa-sidebar {
    background:
        linear-gradient(rgba(255,255,255,.035) 1px, transparent 1px) 0 0 / 100% 34px,
        radial-gradient(130% 55% at 0% 0%, rgba(252,198,38,.12), transparent 55%),
        radial-gradient(120% 60% at 100% 100%, rgba(139,92,246,.16), transparent 60%),
        linear-gradient(180deg, #070B22 0%, #0A1030 55%, #05081A 100%);
    border-right-color: rgba(252,198,38,.20);
    box-shadow: 14px 0 44px -26px rgba(0,0,0,.9);
}
.sa-sidebar .sidebar-scroll::-webkit-scrollbar { width: 6px; }
.sa-sidebar .sidebar-scroll::-webkit-scrollbar-track { background: transparent; }
.sa-sidebar .sidebar-scroll::-webkit-scrollbar-thumb { background: rgba(252,198,38,.35); border-radius: 9999px; }
.sa-sidebar .sidebar-scroll::-webkit-scrollbar-thumb:hover { background: rgba(252,198,38,.65); }

/* Kartu profil user di sidebar */
.sa-user-card {
    position: relative;
    overflow: hidden;
    border-radius: 1rem;
    padding: .8rem;
    margin-bottom: 1.1rem;
    background: linear-gradient(140deg, rgba(252,198,38,.16), rgba(255,255,255,.05) 48%, rgba(139,92,246,.16));
    border: 1px solid rgba(252,198,38,.30);
    box-shadow: inset 0 1px 0 rgba(255,255,255,.12), 0 10px 24px -18px rgba(0,0,0,.9);
}
.sa-user-card::after {
    content: "";
    position: absolute;
    top: -2.5rem; right: -2.5rem;
    width: 7rem; height: 7rem;
    border-radius: 9999px;
    background: rgba(252,198,38,.28);
    filter: blur(26px);
    pointer-events: none;
}
.sa-avatar {
    width: 2.75rem; height: 2.75rem;
    border-radius: 9999px;
    display: flex; align-items: center; justify-content: center;
    font-weight: 800;
    background: linear-gradient(140deg, #FCC626, #F0A500);
    color: #0B1030;
    box-shadow: 0 8px 20px -10px rgba(252,198,38,.9), 0 0 0 2px rgba(255,255,255,.22);
    position: relative;
    overflow: hidden;
}
.sa-avatar img { width: 100%; height: 100%; object-fit: cover; }
.sa-avatar-dot {
    position: absolute; bottom: 0; right: 0;
    width: .72rem; height: .72rem;
    border-radius: 9999px;
    background: #34D399;
    border: 2px solid #080C24;
}
.sa-role-chip {
    display: inline-flex; align-items: center; gap: .3rem;
    padding: .12rem .5rem;
    border-radius: 9999px;
    font-size: 9.5px;
    font-weight: 800;
    letter-spacing: .12em;
    text-transform: uppercase;
    background: linear-gradient(135deg, rgba(252,198,38,.95), rgba(240,165,0,.95));
    color: #0B1030;
    box-shadow: 0 6px 16px -10px rgba(252,198,38,.9);
}

/* Label seksi menu */
.sa-side-label {
    display: flex;
    align-items: center;
    gap: .45rem;
    padding: .55rem .7rem .35rem;
    font-size: 9.5px;
    font-weight: 800;
    letter-spacing: .16em;
    text-transform: uppercase;
    color: var(--sa-gold);
}
.sa-side-label::after {
    content: "";
    flex: 1;
    height: 1px;
    background: linear-gradient(90deg, rgba(252,198,38,.55), transparent);
}
.sa-side-sublabel {
    display: flex;
    align-items: center;
    gap: .45rem;
    padding: .7rem .7rem .3rem;
    margin-top: .55rem;
    border-top: 1px solid rgba(255,255,255,.09);
    font-size: 9.5px;
    font-weight: 800;
    letter-spacing: .16em;
    text-transform: uppercase;
    color: rgba(255,255,255,.45);
}

/* Item menu */
.sa-side-link {
    position: relative;
    display: flex;
    align-items: center;
    gap: .75rem;
    padding: .6rem .7rem;
    border-radius: .8rem;
    font-size: 13px;
    font-weight: 500;
    color: rgba(255,255,255,.74);
    transition: background-color .2s ease, color .2s ease, transform .2s ease;
}
.sa-side-link:hover {
    background-color: rgba(255,255,255,.08);
    color: #FFFFFF;
    transform: translateX(2px);
}
.sa-side-ico {
    flex-shrink: 0;
    width: 2rem; height: 2rem;
    border-radius: .6rem;
    display: flex; align-items: center; justify-content: center;
    background: rgba(255,255,255,.08);
    color: rgba(255,255,255,.82);
    transition: background-color .2s ease, color .2s ease;
}
.sa-side-link:hover .sa-side-ico { background: rgba(255,255,255,.16); color: #FFFFFF; }
.sa-side-link.is-active {
    background: linear-gradient(135deg, #FCC626 0%, #F0A500 100%);
    color: #0B1030;
    font-weight: 700;
    box-shadow: 0 12px 26px -14px rgba(252,198,38,.95);
}
.sa-side-link.is-active::before {
    content: "";
    position: absolute;
    left: -.7rem; top: 22%;
    height: 56%; width: 3px;
    border-radius: 9999px;
    background: #0B1030;
    opacity: .85;
}
.sa-side-link.is-active .sa-side-ico {
    background: rgba(11,16,48,.16);
    color: #0B1030;
}
.sa-side-link.is-active:hover { transform: translateX(2px); }

/* Divider bawah sidebar */
.sa-side-divider {
    margin-top: .8rem;
    padding-top: .7rem;
    border-top: 1px solid rgba(255,255,255,.09);
}

/* --- Topbar --- */
.sa-topbar {
    background: rgba(5,8,26,.92);
    border-bottom-color: rgba(252,198,38,.22);
    box-shadow: 0 14px 40px -24px rgba(0,0,0,1);
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);
}
.sa-topbar-accent {
    position: absolute;
    inset-inline: 0;
    top: 0;
    height: 3px;
    background: linear-gradient(90deg, #FCC626 0%, #8B5CF6 45%, #00A2E9 100%);
    box-shadow: 0 0 18px rgba(252,198,38,.55);
}
.sa-topbar-chip {
    display: none;
    align-items: center;
    gap: .4rem;
    padding: .3rem .7rem;
    border-radius: 9999px;
    font-size: 10px;
    font-weight: 800;
    letter-spacing: .14em;
    text-transform: uppercase;
    color: var(--sa-gold);
    background: rgba(252,198,38,.10);
    border: 1px solid rgba(252,198,38,.35);
    white-space: nowrap;
}
@media (min-width: 1024px) { .sa-topbar-chip { display: inline-flex; } }

.sa-topbar-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: .5rem;
    border-radius: .75rem;
    color: rgba(255,255,255,.82);
    transition: background-color .2s ease, color .2s ease, transform .15s ease;
}
.sa-topbar-btn:hover { background-color: rgba(252,198,38,.14); color: var(--sa-gold); }
.sa-topbar-btn:active { transform: scale(.95); }

/* Tombol profil user di topbar */
.sa-topbar-user { border-radius: .75rem; }
.sa-topbar-user:hover { background-color: rgba(255,255,255,.10); }

.sa-avatar-ring {
    position: absolute;
    inset: -.125rem;
    border-radius: 9999px;
    background: linear-gradient(140deg, #FCC626, #8B5CF6);
    opacity: .8;
    transition: opacity .2s ease;
}
.sa-topbar-user:hover .sa-avatar-ring { opacity: 1; }
.sa-avatar-inner {
    position: relative;
    width: 100%; height: 100%;
    border-radius: 9999px;
    display: flex; align-items: center; justify-content: center;
    font-weight: 700;
    font-size: 11px;
    color: #FFFFFF;
    background: linear-gradient(140deg, #1B2160, #0B1030);
    overflow: hidden;
    box-shadow: 0 8px 18px -12px rgba(0,0,0,1);
}
.sa-avatar-inner img { width: 100%; height: 100%; object-fit: cover; }

/* =========================================================================
   12. UTILITAS PENUNJANG TAMPILAN
   ========================================================================= */
/* Judul halaman: beri aksen emas tipis di bawah teks */
.sa-theme h1 { text-shadow: 0 2px 24px rgba(252,198,38,.12); }

@media (prefers-reduced-motion: reduce) {
    .sa-side-link, .sa-topbar-btn { transition: none !important; transform: none !important; }
}
</style>
