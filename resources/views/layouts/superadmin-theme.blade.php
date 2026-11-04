{{--
    views/layouts/superadmin-theme.blade.php
    ===========================================================================
    TEMA EXECUTIVE COMMAND CENTER — KHUSUS ROLE SUPER ADMIN
    ---------------------------------------------------------------------------
    File ini memberikan sentuhan visual premium & prestisius bagi Super Admin:
    - Sidebar Midnight Navy dengan active indicator Emas Royal
    - Topbar dengan aksen gold line & Command Center chip
    - Card, Modal, Tabel, dan Form yang SOLID, TAJAM, BERSIH & BERKONTRAK TINGGI
    - 100% Responsive di seluruh ukuran layar (Mobile, Tablet, Desktop)
    ===========================================================================
--}}
<style>
/* =========================================================================
   0. TOKEN WARNA SUPER ADMIN
   ========================================================================= */
:root {
    --sa-navy:       #0F1245;
    --sa-navy-dark:  #080C26;
    --sa-navy-light: #161758;
    --sa-blue:       #27438D;
    --sa-cyan:       #00A2E9;
    --sa-gold:       #FCC626;
    --sa-gold-dark:  #F59E0B;
}

/* =========================================================================
   1. SIDEBAR KHUSUS SUPER ADMIN
   ========================================================================= */
.sa-sidebar {
    background: linear-gradient(180deg, #090C28 0%, #0F1245 45%, #0B0E2E 100%) !important;
    border-right: 1px solid rgba(252, 198, 38, 0.20) !important;
    box-shadow: 4px 0 25px -5px rgba(15, 18, 69, 0.4) !important;
}

.sa-sidebar .sidebar-scroll::-webkit-scrollbar {
    width: 5px;
}
.sa-sidebar .sidebar-scroll::-webkit-scrollbar-track {
    background: transparent;
}
.sa-sidebar .sidebar-scroll::-webkit-scrollbar-thumb {
    background: rgba(252, 198, 38, 0.3);
    border-radius: 9999px;
}
.sa-sidebar .sidebar-scroll::-webkit-scrollbar-thumb:hover {
    background: rgba(252, 198, 38, 0.6);
}

/* Kartu profil user di sidebar */
.sa-user-card {
    position: relative;
    overflow: hidden;
    border-radius: 1rem;
    padding: 0.85rem;
    margin-bottom: 1.25rem;
    background: linear-gradient(135deg, rgba(252, 198, 38, 0.12) 0%, rgba(255, 255, 255, 0.05) 50%, rgba(0, 162, 233, 0.10) 100%);
    border: 1px solid rgba(252, 198, 38, 0.25);
    box-shadow: 0 4px 15px -3px rgba(0, 0, 0, 0.25);
}

.sa-avatar {
    width: 2.75rem;
    height: 2.75rem;
    border-radius: 9999px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 800;
    background: linear-gradient(135deg, #FCC626 0%, #F59E0B 100%);
    color: #0F1245;
    box-shadow: 0 4px 12px rgba(252, 198, 38, 0.4), 0 0 0 2px rgba(255, 255, 255, 0.25);
    position: relative;
    overflow: hidden;
}
.sa-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.sa-avatar-dot {
    position: absolute;
    bottom: 0;
    right: 0;
    width: 0.75rem;
    height: 0.75rem;
    border-radius: 9999px;
    background: #10B981;
    border: 2px solid #0F1245;
}

.sa-role-chip {
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    padding: 0.15rem 0.55rem;
    border-radius: 9999px;
    font-size: 9px;
    font-weight: 800;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    background: linear-gradient(135deg, #FCC626 0%, #F59E0B 100%);
    color: #0F1245;
    box-shadow: 0 2px 8px rgba(252, 198, 38, 0.4);
}

/* Label seksi menu */
.sa-side-label {
    display: flex;
    align-items: center;
    gap: 0.45rem;
    padding: 0.6rem 0.75rem 0.35rem;
    font-size: 9.5px;
    font-weight: 800;
    letter-spacing: 0.14em;
    text-transform: uppercase;
    color: #FCC626;
}
.sa-side-label::after {
    content: "";
    flex: 1;
    height: 1px;
    background: linear-gradient(90deg, rgba(252, 198, 38, 0.5), transparent);
}

.sa-side-sublabel {
    display: flex;
    align-items: center;
    gap: 0.45rem;
    padding: 0.75rem 0.75rem 0.35rem;
    margin-top: 0.6rem;
    border-top: 1px solid rgba(255, 255, 255, 0.08);
    font-size: 9.5px;
    font-weight: 800;
    letter-spacing: 0.14em;
    text-transform: uppercase;
    color: rgba(255, 255, 255, 0.45);
}

/* Item navigasi sidebar */
.sa-side-link {
    position: relative;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.6rem 0.75rem;
    border-radius: 0.75rem;
    font-size: 13px;
    font-weight: 500;
    color: rgba(255, 255, 255, 0.75);
    transition: all 0.2s ease;
}
.sa-side-link:hover {
    background-color: rgba(255, 255, 255, 0.08);
    color: #FFFFFF;
    transform: translateX(2px);
}
.sa-side-ico {
    flex-shrink: 0;
    width: 2rem;
    height: 2rem;
    border-radius: 0.6rem;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(255, 255, 255, 0.08);
    color: rgba(255, 255, 255, 0.85);
    transition: all 0.2s ease;
}
.sa-side-link:hover .sa-side-ico {
    background: rgba(255, 255, 255, 0.16);
    color: #FCC626;
}

/* Active sidebar item */
.sa-side-link.is-active {
    background: linear-gradient(135deg, #FCC626 0%, #F59E0B 100%) !important;
    color: #0F1245 !important;
    font-weight: 700 !important;
    box-shadow: 0 4px 15px -2px rgba(252, 198, 38, 0.45) !important;
}
.sa-side-link.is-active .sa-side-ico {
    background: rgba(15, 18, 69, 0.15) !important;
    color: #0F1245 !important;
}

.sa-side-divider {
    margin-top: 0.8rem;
    padding-top: 0.75rem;
    border-top: 1px solid rgba(255, 255, 255, 0.08);
}

/* =========================================================================
   2. TOPBAR KHUSUS SUPER ADMIN
   ========================================================================= */
.sa-topbar {
    background: rgba(15, 18, 69, 0.92) !important;
    border-bottom: 1px solid rgba(252, 198, 38, 0.25) !important;
    box-shadow: 0 4px 20px -2px rgba(15, 18, 69, 0.3) !important;
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);
}
.sa-topbar-accent {
    position: absolute;
    inset-inline: 0;
    top: 0;
    height: 3px;
    background: linear-gradient(90deg, #FCC626 0%, #00A2E9 50%, #27438D 100%);
    box-shadow: 0 0 12px rgba(252, 198, 38, 0.4);
}
.sa-topbar-chip {
    display: none;
    align-items: center;
    gap: 0.35rem;
    padding: 0.22rem 0.65rem;
    border-radius: 9999px;
    font-size: 10px;
    font-weight: 800;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    color: #FCC626;
    background: rgba(252, 198, 38, 0.12);
    border: 1px solid rgba(252, 198, 38, 0.3);
    white-space: nowrap;
}
@media (min-width: 640px) {
    .sa-topbar-chip { display: inline-flex; }
}

.sa-topbar-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0.5rem;
    border-radius: 0.75rem;
    color: rgba(255, 255, 255, 0.85);
    transition: all 0.2s ease;
}
.sa-topbar-btn:hover {
    background-color: rgba(252, 198, 38, 0.15);
    color: #FCC626;
}

.sa-avatar-ring {
    position: absolute;
    inset: -2px;
    border-radius: 9999px;
    background: linear-gradient(135deg, #FCC626, #00A2E9);
    opacity: 0.85;
}
.sa-avatar-inner {
    position: relative;
    width: 100%;
    height: 100%;
    border-radius: 9999px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 11px;
    color: #FFFFFF;
    background: #161758;
    overflow: hidden;
}
.sa-avatar-inner img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

/* =========================================================================
   3. SOLID & ELEGAN MODAL DIALOGUES
   ========================================================================= */
.sa-theme #editModal > div,
.sa-theme #addModal > div,
.sa-theme #resetLinkModal > div,
.sa-theme #suspendModal > div,
.sa-theme #menuModal > div,
.sa-theme #banModal > div,
.sa-theme #confirmModal > div,
.sa-theme .sa-modal-card {
    background-color: #FFFFFF !important;
    color: #1E293B !important;
    border: 1px solid rgba(226, 232, 240, 0.9) !important;
    box-shadow: 0 25px 50px -12px rgba(15, 18, 69, 0.35) !important;
}

/* =========================================================================
   4. FORM CONTROLS & INPUTS (High Contrast & Clean Focus)
   ========================================================================= */
.sa-theme input:not([type="checkbox"]):not([type="radio"]):not([type="hidden"]),
.sa-theme select,
.sa-theme textarea {
    background-color: #FFFFFF !important;
    color: #0F1245 !important;
    border-color: #CBD5E1 !important;
}
.sa-theme input:focus,
.sa-theme select:focus,
.sa-theme textarea:focus {
    background-color: #FFFFFF !important;
    border-color: #00A2E9 !important;
    box-shadow: 0 0 0 3px rgba(0, 162, 233, 0.18) !important;
}
.sa-theme select option {
    background-color: #FFFFFF !important;
    color: #0F1245 !important;
}

/* =========================================================================
   5. TABEL SUPER ADMIN (Tebal, Rapih, Bersih)
   ========================================================================= */
.sa-theme thead th {
    background-color: #F8FAFC !important;
    color: #475569 !important;
    font-weight: 700 !important;
    letter-spacing: 0.05em !important;
    font-size: 11px !important;
    border-bottom: 1px solid #E2E8F0 !important;
}
.sa-theme tbody tr {
    transition: background-color 0.15s ease;
}
.sa-theme tbody tr:hover {
    background-color: #F1F5F9 !important;
}

/* =========================================================================
   6. RESPONSIVE UTILITIES & SLIM SCROLLBARS
   ========================================================================= */
.table-responsive-wrapper {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: thin;
}
.table-responsive-wrapper::-webkit-scrollbar {
    height: 6px;
    width: 6px;
}
.table-responsive-wrapper::-webkit-scrollbar-track {
    background: #F1F5F9;
    border-radius: 9999px;
}
.table-responsive-wrapper::-webkit-scrollbar-thumb {
    background: #CBD5E1;
    border-radius: 9999px;
}
.table-responsive-wrapper::-webkit-scrollbar-thumb:hover {
    background: #94A3B8;
}

/* Transisi halus saat resize */
@media (max-width: 640px) {
    .sa-theme h1 {
        font-size: 1.35rem !important;
        line-height: 1.25 !important;
    }
}
</style>
