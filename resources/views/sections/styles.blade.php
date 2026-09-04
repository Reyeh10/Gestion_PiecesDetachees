{{-- ============================================================
     FONTS
============================================================ --}}

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

<link
    href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@300;400;500;600;700&display=swap"
    rel="stylesheet"
>


{{-- ============================================================
     FONT ICONS
============================================================ --}}

<link
    rel="stylesheet"
    href="{{ asset('assets/vendor/fonts/boxicons.css') }}"
>

<link
    rel="stylesheet"
    href="{{ asset('assets/vendor/fonts/fontawesome.css') }}"
>

<link
    rel="stylesheet"
    href="{{ asset('assets/vendor/fonts/flag-icons.css') }}"
>


{{-- ============================================================
     CORE CSS
============================================================ --}}

<link
    rel="stylesheet"
    href="{{ asset('assets/vendor/css/core.css') }}"
>

<link
    rel="stylesheet"
    href="{{ asset('assets/vendor/css/theme-default.css') }}"
>


{{-- ============================================================
     DEMO CSS
============================================================ --}}

<link
    rel="stylesheet"
    href="{{ asset('assets/css/demo.css') }}"
>


{{-- ============================================================
     VENDOR CSS
============================================================ --}}

<link
    rel="stylesheet"
    href="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css') }}"
>

<link
    rel="stylesheet"
    href="{{ asset('assets/vendor/libs/typeahead-js/typeahead.css') }}"
>


{{-- ============================================================
     VENDOR / PAGE STYLES
============================================================ --}}

@yield('vendor-style')

@yield('page-style')


{{-- ============================================================
     STCD MOTORS - GLOBAL DESIGN
============================================================ --}}

<style>

    /* ============================================================
       VARIABLES
    ============================================================ */

    :root {
        --stcd-sidebar-width: 260px;
        --stcd-sidebar-bg-1: #0f172a;
        --stcd-sidebar-bg-2: #1e293b;
        --stcd-content-bg: #f5f6f8;
    }


    /* ============================================================
       RESET
    ============================================================ */

    *,
    *::before,
    *::after {
        box-sizing: border-box;
    }

    html,
    body {
        width: 100%;
        min-height: 100%;
        margin: 0;
        padding: 0;
    }

    html {
        overflow-x: hidden;
    }

    body {
        min-height: 100vh;
        overflow-x: hidden;
        background: var(--stcd-content-bg);
        font-family: 'IBM Plex Sans', sans-serif;
    }


    /* ============================================================
       LAYOUT GLOBAL
    ============================================================ */

    .layout-wrapper {
        position: relative !important;

        width: 100% !important;
        max-width: 100% !important;

        min-height: 100vh !important;

        overflow: visible !important;
    }

    .layout-container {
        position: relative !important;

        width: 100% !important;
        max-width: 100% !important;

        min-height: 100vh !important;

        overflow: visible !important;
    }


    /* ============================================================
       SIDEBAR BASE
    ============================================================ */

    html body #layout-menu {

        width: var(--stcd-sidebar-width) !important;
        min-width: var(--stcd-sidebar-width) !important;
        max-width: var(--stcd-sidebar-width) !important;

        background:
            linear-gradient(
                180deg,
                var(--stcd-sidebar-bg-1),
                var(--stcd-sidebar-bg-2)
            ) !important;

        color: #cbd5e1 !important;

        border-right: 0 !important;

        overflow-x: hidden !important;
        overflow-y: auto !important;

        opacity: 1 !important;
        visibility: visible !important;

        transition:
            transform .25s ease !important;
    }


    /* ============================================================
       ORDINATEUR / LAPTOP
       >= 992px
       SIDEBAR TOUJOURS VISIBLE
    ============================================================ */

    @media (min-width: 992px) {

        html body #layout-menu {

            position: fixed !important;

            top: 0 !important;
            left: 0 !important;
            right: auto !important;
            bottom: 0 !important;

            display: flex !important;
            flex-direction: column !important;

            width: var(--stcd-sidebar-width) !important;
            min-width: var(--stcd-sidebar-width) !important;
            max-width: var(--stcd-sidebar-width) !important;

            height: 100vh !important;
            min-height: 100vh !important;

            margin: 0 !important;
            padding: 0 !important;

            transform: none !important;
            translate: none !important;

            opacity: 1 !important;
            visibility: visible !important;

            pointer-events: auto !important;

            z-index: 99999 !important;
        }


        /*
         * Le contenu commence après le sidebar.
         */

        html body .layout-page {

            position: relative !important;

            width:
                calc(
                    100% - var(--stcd-sidebar-width)
                ) !important;

            max-width:
                calc(
                    100% - var(--stcd-sidebar-width)
                ) !important;

            min-width: 0 !important;

            margin-left:
                var(--stcd-sidebar-width) !important;

            margin-right: 0 !important;

            padding-left: 0 !important;

            transform: none !important;
        }


        /*
         * Empêcher Sneat d'ajouter un décalage.
         */

        html body .layout-container,
        html body .layout-wrapper {

            padding-left: 0 !important;
            margin-left: 0 !important;
        }


        /*
         * Overlay inutile sur ordinateur.
         */

        html body .layout-overlay {

            display: none !important;
        }


        /*
         * Le bouton hamburger du header
         * n'est pas nécessaire sur >= 992px.
         */

        html body .layout-navbar .layout-menu-toggle {

            display: none !important;
        }
    }


    /* ============================================================
       TABLETTE / MOBILE
       < 992px
    ============================================================ */

    @media (max-width: 991.98px) {

        html body #layout-menu {

            position: fixed !important;

            top: 0 !important;
            left: 0 !important;
            bottom: 0 !important;

            width: var(--stcd-sidebar-width) !important;
            min-width: var(--stcd-sidebar-width) !important;
            max-width: var(--stcd-sidebar-width) !important;

            height: 100vh !important;

            transform:
                translateX(
                    calc(
                        -1 * var(--stcd-sidebar-width)
                    )
                ) !important;

            opacity: 1 !important;
            visibility: visible !important;

            z-index: 99999 !important;
        }


        /*
         * Sidebar ouvert.
         */

        html.layout-menu-expanded
        body #layout-menu,

        body.layout-menu-expanded
        #layout-menu {

            transform: translateX(0) !important;
        }


        /*
         * Contenu plein écran.
         */

        html body .layout-page {

            width: 100% !important;
            max-width: 100% !important;

            margin-left: 0 !important;
            padding-left: 0 !important;
        }


        /*
         * Overlay.
         */

        html body .layout-overlay {

            position: fixed !important;

            inset: 0 !important;

            width: 100vw !important;
            height: 100vh !important;

            display: none !important;

            background:
                rgba(
                    15,
                    23,
                    42,
                    .45
                ) !important;

            z-index: 99990 !important;
        }


        html.layout-menu-expanded
        body .layout-overlay,

        body.layout-menu-expanded
        .layout-overlay {

            display: block !important;
        }
    }


    /* ============================================================
       PAGE
    ============================================================ */

    .layout-page {
        min-width: 0 !important;
    }

    .content-wrapper {

        width: 100% !important;
        max-width: 100% !important;

        min-width: 0 !important;
    }


    /* ============================================================
       NAVBAR
    ============================================================ */

    .layout-navbar {

        width: 100% !important;
        max-width: 100% !important;

        margin-left: 0 !important;
        margin-right: 0 !important;
    }


    /* ============================================================
       CONTENEURS BOOTSTRAP
    ============================================================ */

    .content-wrapper > .container-xxl,
    .content-wrapper > .container-xl,
    .content-wrapper > .container-lg,
    .content-wrapper > .container-md,
    .content-wrapper > .container-sm,
    .content-wrapper > .container,
    .content-wrapper > .container-fluid {

        width: 100% !important;
        max-width: 100% !important;

        margin-left: 0 !important;
        margin-right: 0 !important;
    }


    /* ============================================================
       SIDEBAR - LOGO
    ============================================================ */

    #layout-menu .app-brand {

        flex-shrink: 0;

        width: 100% !important;

        background:
            rgba(
                15,
                23,
                42,
                .95
            ) !important;
    }


    #layout-menu .app-brand-text {

        color: #ffffff !important;

        font-size: 20px;

        letter-spacing: 1px;
    }


    #layout-menu .app-brand-logo i {

        color: #60a5fa !important;
    }


    /* ============================================================
       MENU INTERNE
    ============================================================ */

    #layout-menu .menu-inner {

        width: 100% !important;

        margin: 0 !important;

        padding-top: 8px !important;
        padding-left: 0 !important;
        padding-right: 0 !important;
    }


    #layout-menu .menu-item {

        width: 100% !important;

        margin-bottom: 1px !important;
    }


    /* ============================================================
       MENU LINK
    ============================================================ */

    #layout-menu .menu-link {

        position: relative;

        display: flex !important;
        align-items: center !important;

        width:
            calc(
                100% - 20px
            ) !important;

        min-height: 36px !important;

        margin:
            2px
            10px !important;

        padding:
            7px
            14px !important;

        color: #cbd5e1 !important;

        border-radius: 8px !important;

        text-decoration: none !important;

        transition:
            background-color .2s ease,
            color .2s ease,
            transform .2s ease !important;
    }


    #layout-menu .menu-link > div {

        min-width: 0;

        font-size: 14px !important;
    }


    /* ============================================================
       HOVER
    ============================================================ */

    #layout-menu .menu-link:hover {

        color: #ffffff !important;

        background:
            rgba(
                59,
                130,
                246,
                .16
            ) !important;

        transform:
            translateX(2px);
    }


    /* ============================================================
       ACTIVE
    ============================================================ */

    #layout-menu
    .menu-item.active
    > .menu-link {

        color: #ffffff !important;

        background:
            linear-gradient(
                135deg,
                #2563eb,
                #3b82f6
            ) !important;

        box-shadow:
            0
            4px
            12px
            rgba(
                0,
                0,
                0,
                .20
            ) !important;
    }


    /* ============================================================
       ICONES
    ============================================================ */

    #layout-menu .menu-icon {

        flex-shrink: 0;

        color: #93c5fd !important;

        font-size: 17px !important;
    }


    #layout-menu
    .menu-item.active
    > .menu-link
    .menu-icon {

        color: #ffffff !important;
    }


    /* ============================================================
       HEADERS MENU
    ============================================================ */

    #layout-menu .menu-header {

        width: 100% !important;

        margin-top: 10px !important;
        margin-bottom: 3px !important;

        padding:
            4px
            20px
            2px !important;
    }


    #layout-menu .menu-header-text {

        color: #94a3b8 !important;

        font-size: 10px !important;

        font-weight: 600 !important;

        letter-spacing: 1px;
    }


    /* ============================================================
       SOUS-MENUS
    ============================================================ */

    #layout-menu .menu-sub {

        display: none;

        width: 100% !important;

        margin: 0 !important;

        padding:
            0
            0
            0
            8px !important;

        background:
            transparent !important;
    }


    #layout-menu
    .menu-item.open
    > .menu-sub {

        display: block !important;
    }


    #layout-menu
    .menu-sub
    .menu-item {

        margin:
            1px
            0 !important;
    }


    #layout-menu
    .menu-sub
    .menu-link {

        width:
            calc(
                100% - 20px
            ) !important;

        min-height: 30px !important;

        margin:
            1px
            10px !important;

        padding:
            5px
            12px
            5px
            34px !important;

        color: #cbd5e1 !important;

        background:
            transparent !important;

        border-radius:
            6px !important;
    }


    #layout-menu
    .menu-sub
    .menu-link
    > div {

        font-size: 13px !important;

        font-weight: 500;
    }


    /* ============================================================
       POINT SOUS-MENU
    ============================================================ */

    #layout-menu
    .menu-sub
    .menu-link::before {

        content: "";

        position: absolute !important;

        top: 50% !important;
        left: 18px !important;

        width: 5px !important;
        height: 5px !important;

        transform:
            translateY(-50%);

        border-radius:
            50%;

        background:
            #94a3b8;
    }


    #layout-menu
    .menu-sub
    .menu-item.active
    > .menu-link {

        color:
            #ffffff !important;

        background:
            rgba(
                59,
                130,
                246,
                .18
            ) !important;
    }


    #layout-menu
    .menu-sub
    .menu-item.active
    > .menu-link::before {

        background:
            #60a5fa !important;
    }


    /* ============================================================
       PARENT OUVERT
    ============================================================ */

    #layout-menu .menu-item.open {

        background:
            transparent !important;
    }


    #layout-menu .menu-toggle {

        cursor: pointer;
    }


    /* ============================================================
       SCROLLBAR
    ============================================================ */

    #layout-menu::-webkit-scrollbar {

        width: 6px;
    }


    #layout-menu::-webkit-scrollbar-track {

        background:
            transparent;
    }


    #layout-menu::-webkit-scrollbar-thumb {

        background:
            #334155;

        border-radius:
            10px;
    }


    /* ============================================================
       CARDS
    ============================================================ */

    .card,
    .card-body {

        min-width: 0;

        max-width: 100%;
    }


    /* ============================================================
       TABLES
    ============================================================ */

    .table-responsive {

        display: block;

        width: 100% !important;
        max-width: 100% !important;

        overflow-x: auto !important;

        -webkit-overflow-scrolling:
            touch;
    }


    /* ============================================================
       ACTION BUTTONS
    ============================================================ */

    .btn-icon-sm {

        display: inline-flex !important;

        align-items: center !important;
        justify-content: center !important;

        width: 32px !important;
        height: 32px !important;

        padding: 0 !important;

        font-size: 16px !important;
    }


    .header-actions .form-control-sm {

        height: 32px !important;

        padding:
            2px
            8px !important;
    }


    .header-actions .btn {

        display: inline-flex;

        align-items: center;
        justify-content: center;

        height: 32px !important;
    }


    /* ============================================================
       TABLETTE
    ============================================================ */

    @media (max-width: 991.98px) {

        .content-wrapper
        > .container-xxl {

            padding-left:
                16px !important;

            padding-right:
                16px !important;
        }
    }


    /* ============================================================
       MOBILE
    ============================================================ */

    @media (max-width: 767.98px) {

        .content-wrapper
        > .container-xxl {

            padding-left:
                12px !important;

            padding-right:
                12px !important;
        }
    }
    /* ============================================================
   BARRE DE RECHERCHE GLOBALE STCD
   Recherche + Rechercher + Réinitialiser
============================================================ */

.stcd-search-row {
    display: grid !important;

    grid-template-columns:
        minmax(300px, 1fr)
        minmax(220px, 280px)
        minmax(220px, 280px);

    gap: 16px;

    width: 100%;

    align-items: end;
}


/* Champ de recherche */

.stcd-search-field {
    width: 100%;
    min-width: 0;
}


/* Zone des boutons */

.stcd-search-action {
    width: 100%;
    min-width: 0;
}


/* Même hauteur pour tout */

.stcd-search-row .form-control,
.stcd-search-row .form-select,
.stcd-search-row .btn {

    width: 100% !important;

    min-height: 46px !important;
}


/* Boutons centrés */

.stcd-search-row .btn {

    display: flex !important;

    align-items: center !important;
    justify-content: center !important;

    gap: 8px;

    white-space: nowrap;
}


/* ============================================================
   ÉCRANS MOYENS
============================================================ */

@media (max-width: 1199.98px) {

    .stcd-search-row {

        grid-template-columns:
            minmax(250px, 1fr)
            minmax(190px, 240px)
            minmax(190px, 240px);

        gap: 12px;
    }
}


/* ============================================================
   TABLETTE
============================================================ */

@media (max-width: 991.98px) {

    .stcd-search-row {

        grid-template-columns:
            1fr
            1fr;

    }


    /*
     * Le champ occupe toute la première ligne
     */

    .stcd-search-field {

        grid-column:
            1 / -1;

    }
}


/* ============================================================
   MOBILE
============================================================ */

@media (max-width: 575.98px) {

    .stcd-search-row {

        grid-template-columns:
            1fr;

    }


    .stcd-search-field {

        grid-column:
            auto;

    }
}

</style>

{{-- END: Theme CSS --}}
