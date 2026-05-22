{{-- BEGIN: Theme CSS --}}

<!-- Fonts -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<!-- Fonts CSS -->
<link rel="stylesheet" href="{{ asset('assets/vendor/fonts/boxicons.css') }}">
<link rel="stylesheet" href="{{ asset('assets/vendor/fonts/fontawesome.css') }}">
<link rel="stylesheet" href="{{ asset('assets/vendor/fonts/flag-icons.css') }}">

<!-- Core CSS -->
<link rel="stylesheet" href="{{ asset('assets/vendor/css/core.css') }}">
<link rel="stylesheet" href="{{ asset('assets/vendor/css/theme-default.css') }}">

<!-- Demo CSS -->
<link rel="stylesheet" href="{{ asset('assets/css/demo.css') }}">

<!-- Vendors CSS -->
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css') }}">
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/typeahead-js/typeahead.css') }}">

{{-- Vendor Styles --}}
@yield('vendor-style')

{{-- Page Styles --}}
@yield('page-style')

<!-- ================= CUSTOM STYLE ================= -->

<style>

    /* ===== SIDEBAR BLEU MARINE ===== */
    .layout-menu {
        background: linear-gradient(180deg, #0f172a, #1e293b);
        color: #cbd5f1;
        border-right: none;
    }

    /* Logo */
    .app-brand-text {
        color: #ffffff !important;
        font-size: 20px;
        letter-spacing: 1px;
    }

    .app-brand-logo i {
        color: #60a5fa;
    }

    /* Menu items */
    .menu-link {
        color: #cbd5f1 !important;
        border-radius: 8px;
        margin: 4px 10px;
        padding: 10px 15px;
        transition: all 0.3s ease;
    }

    /* Hover */
    .menu-link:hover {
        background-color: #1e40af;
        color: #ffffff !important;
        transform: translateX(3px);
    }

    /* Active */
    .menu-item.active .menu-link {
        background: linear-gradient(135deg, #2563eb, #3b82f6);
        color: #ffffff !important;
        box-shadow: 0 4px 12px rgba(0,0,0,0.3);
    }

    /* Icons */
    .menu-icon {
        color: #93c5fd !important;
        font-size: 18px;
        transition: 0.3s;
    }

    /* Active icon */
    .menu-item.active .menu-icon {
        color: #ffffff !important;
    }

    /* Section header */
    .menu-header-text {
        color: #94a3b8;
        font-size: 11px;
        letter-spacing: 1.2px;
        font-weight: 600;
    }

    /* Scrollbar (optionnel mais stylé) */
    .layout-menu::-webkit-scrollbar {
        width: 6px;
    }

    .layout-menu::-webkit-scrollbar-thumb {
        background: #334155;
        border-radius: 10px;
    }

    /* Smooth */
    .layout-menu {
        transition: all 0.3s ease;
    }

    /* FIX BUTTON ICON SIZE */
    .btn-icon-sm {
        width: 32px !important;
        height: 32px !important;
        padding: 0 !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        font-size: 16px !important;
    }
    /* ALIGNEMENT HEADER ACTIONS */
    .header-actions .form-control-sm {
        height: 32px !important;
        padding: 2px 8px !important;
    }

    .header-actions .btn {
        height: 32px !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    /* =========================================================
   SIDEBAR COMPACT STYLE
========================================================= */

/* ESPACE ENTRE ITEMS */
.layout-menu .menu-inner > .menu-item {
    margin-bottom: 4px !important;
}

/* TAILLE GENERALE DES MENUS */
.layout-menu .menu-link {
    padding-top: 10px !important;
    padding-bottom: 10px !important;
    min-height: 42px !important;
}

/* TEXTE MENU */
.layout-menu .menu-link div {
    font-size: 14px !important;
}

/* ICONE MENU */
.layout-menu .menu-icon {
    font-size: 18px !important;
}

/* =========================================================
   SIDEBAR ULTRA COMPACT
========================================================= */

/* LARGEUR SIDEBAR */
.layout-menu {
    width: 245px !important;
}

/* HEADER */
.menu-header {
    margin-top: 10px !important;
    margin-bottom: 4px !important;
    padding-top: 4px !important;
    padding-bottom: 2px !important;
}

/* TITRE HEADER */
.menu-header-text {
    font-size: 10px !important;
    letter-spacing: 1px;
}

/* ITEMS PRINCIPAUX */
.layout-menu .menu-link {

    min-height: 34px !important;

    padding-top: 6px !important;
    padding-bottom: 6px !important;

    padding-left: 14px !important;
    padding-right: 14px !important;
}

/* ESPACE ENTRE ITEMS */
.layout-menu .menu-item {
    margin-bottom: 1px !important;
}

/* TEXTE MENU */
.layout-menu .menu-link div {
    font-size: 14px !important;
}

/* ICONES */
.layout-menu .menu-icon {
    font-size: 17px !important;
}

/* =========================================================
   SOUS MENUS
========================================================= */

.layout-menu .menu-sub {

    margin-top: 0 !important;

    padding-top: 0 !important;
    padding-bottom: 0 !important;

    padding-left: 8px !important;
}

/* ITEMS SOUS MENU */
.layout-menu .menu-sub .menu-link {

    min-height: 28px !important;

    padding-top: 3px !important;
    padding-bottom: 3px !important;

    padding-left: 10px !important;

    border-radius: 6px !important;
}

/* TEXTE SOUS MENU */
.layout-menu .menu-sub .menu-link div {

    font-size: 13px !important;

    font-weight: 500;
}

/* POINTS SOUS MENU */
.layout-menu .menu-sub .menu-link::before {

    content: "";

    position: absolute !important;

    left: 16px !important;

    width: 5px !important;
    height: 5px !important;

    border-radius: 50%;

    background-color: #94a3b8;
}

/* TEXTE SOUS MENU */
.layout-menu .menu-sub .menu-link {

    position: relative !important;

    padding-left: 34px !important;
}



/* Sous-menu */
.menu-sub{
    display: none;
    padding-left: 12px;
    margin-top: 5px;
    background: transparent !important;
}

/* Quand ouvert */
.menu-item.open > .menu-sub{
    display: block;
}

/* Items sous-menu */
.menu-sub .menu-item{
    margin: 2px 0;
}

/* Liens */
.menu-sub .menu-link{
    background: transparent !important;
    color: #cfd3ec !important;

    padding:
        8px
        12px
        8px
        20px !important;

    border-radius: 8px;

    min-height: auto !important;

    font-size: 14px;

    transition: all .2s ease;
}

/* Hover */
.menu-sub .menu-link:hover{
    background: rgba(255,255,255,0.06) !important;
    color: #fff !important;
}

/* Actif */
.menu-sub .menu-item.active .menu-link{
    background: rgba(59,130,246,0.18) !important;
    color: #fff !important;
}

/* Petit point */
.menu-sub .menu-link div::before{
    display: none !important;
    content: none !important;
}

/* Supprime gros fond blanc */
.menu-item.open{
    background: transparent !important;
}

/* Parent menu */
.menu-toggle{
    cursor: pointer;
}



</style>

{{-- END: Theme CSS --}}
