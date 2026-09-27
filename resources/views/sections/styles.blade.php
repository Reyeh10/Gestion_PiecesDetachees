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
       HEADER FIXE AU DÉFILEMENT
       Desktop : fixé à droite du sidebar
    ============================================================ */

    @media (min-width: 1200px) {

        .layout-page > .layout-navbar {

            position: fixed !important;

            top: 0 !important;
            left: var(--sidebar-width) !important;
            right: 0 !important;

            width: calc(100% - var(--sidebar-width)) !important;
            max-width: calc(100% - var(--sidebar-width)) !important;

            margin: 0 !important;

            z-index: 1050 !important;
        }

        /*
         * Le header fixed sort du flux.
         * On réserve donc sa hauteur avant le contenu.
         */
        .layout-page > .content-wrapper {

            padding-top: 72px !important;
        }
    }


    /* ============================================================
       TABLETTE / MOBILE
       Header sur toute la largeur
    ============================================================ */

    @media (max-width: 1199.98px) {

        .layout-page > .layout-navbar {

            position: fixed !important;

            top: 0 !important;
            left: 0 !important;
            right: 0 !important;

            width: 100% !important;
            max-width: 100% !important;

            margin: 0 !important;

            z-index: 1050 !important;
        }

        .layout-page > .content-wrapper {

            padding-top: 72px !important;
        }
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
/* ================================================================
   STCD MOTORS — SIDEBAR PREMIUM / HIÉRARCHIE DES SOUS-MENUS
   ================================================================ */

/* ---------- MENU PRINCIPAL ---------- */

#layout-menu .menu-inner {
    padding: 10px 10px 24px !important;
}

#layout-menu .menu-item {
    position: relative;
}

/* liens niveau principal */
#layout-menu > .menu-inner > .menu-item > .menu-link {
    min-height: 42px !important;
    margin: 3px 0 !important;
    padding: 9px 12px !important;

    border-radius: 9px !important;

    font-weight: 500 !important;
    color: #cbd5e1 !important;
}

#layout-menu > .menu-inner > .menu-item > .menu-link:hover {
    color: #ffffff !important;
    background: rgba(99, 102, 241, .12) !important;
}

/* icône niveau principal */
#layout-menu > .menu-inner > .menu-item > .menu-link .menu-icon {
    width: 22px !important;
    min-width: 22px !important;

    margin-right: 10px !important;

    color: #9db9ff !important;

    font-size: 18px !important;
}


/* ================================================================
   TITRES DE SECTIONS
   ================================================================ */

#layout-menu > .menu-inner > .menu-header {
    position: relative;

    margin: 18px 0 7px !important;
    padding: 0 10px !important;

    min-height: auto !important;
}

#layout-menu > .menu-inner > .menu-header .menu-header-text {
    color: #8892aa !important;

    font-size: 10px !important;
    font-weight: 700 !important;

    letter-spacing: 1.15px !important;
    text-transform: uppercase;
}


/* ================================================================
   PARENT QUI POSSÈDE UN SOUS-MENU
   ================================================================ */

#layout-menu .menu-item > .menu-link.menu-toggle {
    position: relative;
}

/* espace pour la flèche */
#layout-menu .menu-item > .menu-link.menu-toggle {
    padding-right: 38px !important;
}


/* ---------- FLÈCHE ---------- */

#layout-menu .menu-item > .menu-link.menu-toggle::after {
    content: "";

    position: absolute;

    top: 50%;
    right: 15px;

    width: 7px;
    height: 7px;

    border-right: 2px solid #8ea6d8;
    border-bottom: 2px solid #8ea6d8;

    transform:
        translateY(-65%)
        rotate(45deg);

    transition:
        transform .20s ease,
        border-color .20s ease;
}

/* parent ouvert */
#layout-menu .menu-item.open > .menu-link.menu-toggle::after {
    transform:
        translateY(-35%)
        rotate(225deg);

    border-color: #ffffff;
}


/* ================================================================
   PARENT OUVERT
   ================================================================ */

#layout-menu .menu-item.open > .menu-link.menu-toggle {
    color: #ffffff !important;

    background:
        linear-gradient(
            90deg,
            rgba(78, 91, 213, .24),
            rgba(78, 91, 213, .08)
        ) !important;
}

#layout-menu .menu-item.open > .menu-link.menu-toggle .menu-icon {
    color: #8da9ff !important;
}


/* ================================================================
   CONTENEUR DU SOUS-MENU
   ================================================================ */

#layout-menu .menu-sub {
    position: relative;

    display: none;

    width: calc(100% - 14px) !important;

    margin:
        3px
        0
        8px
        14px !important;

    padding:
        6px
        6px
        7px
        17px !important;

    background:
        rgba(8, 15, 35, .25) !important;

    border-radius: 8px !important;

    overflow: visible !important;
}


/* afficher le sous-menu */
#layout-menu .menu-item.open > .menu-sub {
    display: block !important;
}


/* ================================================================
   LIGNE VERTICALE DU SOUS-MENU
   ================================================================ */

#layout-menu .menu-sub::before {
    content: "";

    position: absolute;

    top: 8px;
    bottom: 8px;
    left: 12px;

    width: 1px;

    background:
        linear-gradient(
            180deg,
            rgba(113, 139, 255, .60),
            rgba(113, 139, 255, .12)
        );

    border-radius: 10px;
}


/* ================================================================
   ÉLÉMENTS DU SOUS-MENU
   ================================================================ */

#layout-menu .menu-sub > .menu-item {
    position: relative;

    width: 100% !important;

    margin: 2px 0 !important;
}


/* lien */
#layout-menu .menu-sub > .menu-item > .menu-link {
    position: relative;

    width: 100% !important;

    min-height: 34px !important;

    margin: 0 !important;

    padding:
        7px
        10px
        7px
        27px !important;

    color: #aeb9cf !important;

    background: transparent !important;

    border-radius: 7px !important;

    font-size: 13px !important;

    transition:
        color .18s ease,
        background-color .18s ease,
        transform .18s ease !important;
}


/* texte sous-menu */
#layout-menu .menu-sub > .menu-item > .menu-link > div {
    font-size: 13px !important;
    font-weight: 500 !important;

    line-height: 1.3 !important;
}


/* ================================================================
   CONNECTEUR / POINT DES SOUS-MENUS
   ================================================================ */

/* ================================================================
   SOUS-MENUS — PETITS POINTS RONDS
   ================================================================ */

#layout-menu
.menu-sub
> .menu-item
> .menu-link::before {

    content: "" !important;

    position: absolute !important;

    top: 50% !important;
    left: 8px !important;

    width: 7px !important;
    height: 7px !important;

    display: block !important;

    margin: 0 !important;

    transform:
        translateY(-50%) !important;

    background:
        #8396df !important;

    border:
        2px solid
        rgba(165, 178, 255, .45) !important;

    border-radius:
        50% !important;

    box-shadow:
        none !important;

    z-index: 2;
}


/* petite branche horizontale */
#layout-menu .menu-sub > .menu-item > .menu-link::after {
    display: none !important;
}


/* ================================================================
   HOVER DU SOUS-MENU
   ================================================================ */

#layout-menu .menu-sub > .menu-item > .menu-link:hover {
    color: #ffffff !important;

    background:
        rgba(91, 108, 255, .12) !important;

    transform: translateX(2px);
}

#layout-menu
.menu-sub
> .menu-item
> .menu-link:hover::before {

    background:
        #aab7ff !important;

    border-color:
        #d5dcff !important;

    transform:
        translateY(-50%)
        scale(1.15) !important;
}

/* ================================================================
   SOUS-MENU ACTIF
   ================================================================ */

#layout-menu
.menu-sub
> .menu-item.active
> .menu-link {

    color: #ffffff !important;

    background:
        linear-gradient(
            90deg,
            rgba(80, 96, 230, .45),
            rgba(80, 96, 230, .16)
        ) !important;

    box-shadow:
        inset 3px 0 0 #7084ff !important;

    font-weight: 600 !important;
}


#layout-menu
.menu-sub
> .menu-item.active
> .menu-link::before {

    content: "" !important;

    width: 8px !important;
    height: 8px !important;

    background:
        #ffffff !important;

    border:
        2px solid
        #8fa1ff !important;

    border-radius:
        50% !important;

    transform:
        translateY(-50%) !important;

    box-shadow:
        0 0 0 3px
        rgba(113, 132, 255, .20) !important;
}


/* ================================================================
   PETITS TITRES À L'INTÉRIEUR DES SOUS-MENUS
   Exemple :
   GESTION DES PRODUITS
   RÉAPPROVISIONNEMENT
   ================================================================ */

#layout-menu .menu-sub > .menu-header {
    position: relative;

    margin:
        10px
        0
        4px !important;

    padding:
        2px
        8px
        2px
        27px !important;

    min-height: auto !important;
}

#layout-menu
.menu-sub
> .menu-header
.menu-header-text {

    color: #7583a3 !important;

    font-size: 9px !important;
    font-weight: 700 !important;

    letter-spacing: .8px !important;

    text-transform: uppercase;
}


/* ================================================================
   TABLEAU DE BORD ACTIF
   ================================================================ */

#layout-menu
> .menu-inner
> .menu-item.active
> .menu-link:not(.menu-toggle) {

    color: #ffffff !important;

    background:
        linear-gradient(
            135deg,
            #5368e9,
            #6574e8
        ) !important;

    box-shadow:
        0 5px 15px
        rgba(45, 62, 180, .25) !important;
}


/* ================================================================
   SCROLLBAR
   ================================================================ */

#layout-menu::-webkit-scrollbar {
    width: 5px;
}

#layout-menu::-webkit-scrollbar-track {
    background: transparent;
}

#layout-menu::-webkit-scrollbar-thumb {
    background:
        rgba(135, 151, 190, .30);

    border-radius: 20px;
}

#layout-menu::-webkit-scrollbar-thumb:hover {
    background:
        rgba(135, 151, 190, .50);
}

/* ================================================================
   STCD MOTORS — FLÈCHE DEVANT CHAQUE MODULE PRINCIPAL
   ================================================================ */

/*
 * Tous les modules principaux :
 * Produits, Catégories, Fournisseurs, Clients, Véhicules,
 * Dépôts, Transferts, Ventes, Utilisateurs, etc.
 */
#layout-menu
> .menu-inner
> .menu-item
> .menu-link::before {

    content: "›";

    position: relative !important;

    top: auto !important;
    left: auto !important;

    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;

    flex: 0 0 14px !important;

    width: 14px !important;
    height: 20px !important;

    margin-right: 5px !important;

    color: #7389d8 !important;

    background: transparent !important;
    border: 0 !important;
    border-radius: 0 !important;

    font-size: 18px !important;
    font-weight: 700 !important;
    line-height: 1 !important;

    transform: none !important;

    transition:
        color .18s ease,
        transform .18s ease !important;
}


/* Survol */
#layout-menu
> .menu-inner
> .menu-item
> .menu-link:hover::before {

    color: #ffffff !important;

    transform: translateX(2px) !important;
}


/* Module actif */
#layout-menu
> .menu-inner
> .menu-item.active
> .menu-link::before {

    color: #ffffff !important;
}


/* Module ouvert */
#layout-menu
> .menu-inner
> .menu-item.open
> .menu-link.menu-toggle::before {

    color: #a9b8ff !important;

    transform: rotate(90deg) !important;
}
</style>

{{-- END: Theme CSS --}}
