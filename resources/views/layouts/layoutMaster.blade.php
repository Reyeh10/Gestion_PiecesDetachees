<!DOCTYPE html>
<html lang="fr">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <meta
        name="csrf-token"
        content="{{ csrf_token() }}"
    >

    <title>
        @yield('title', 'Gestion Pièces')
    </title>


    {{-- ========================================================= --}}
    {{-- STYLES PRINCIPAUX --}}
    {{-- ========================================================= --}}

    @include('sections.styles')


    {{-- ========================================================= --}}
    {{-- SELECT2 --}}
    {{-- ========================================================= --}}

    <link
        href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css"
        rel="stylesheet"
    >


    @stack('styles')


    <style>

        /* =========================================================
           VARIABLES
        ========================================================= */

        :root {
            --sidebar-width: 260px;
        }


        /* =========================================================
           RESET GLOBAL
        ========================================================= */

        html,
        body {
            width: 100%;
            min-height: 100%;
            margin: 0;
            padding: 0;
        }

        * {
            box-sizing: border-box;
        }

        html {
            overflow-x: hidden;
        }

        body {
            width: 100%;
            min-height: 100vh;
            overflow-x: hidden;
            background-color: #f5f6f8;
        }


        /* =========================================================
           SELECT2
        ========================================================= */

        .select2-container {
            width: 100% !important;
            max-width: 100% !important;
        }

        .select2-container .select2-selection--single {
            height: 38px !important;
            border: 1px solid #d9dee3 !important;
            border-radius: 0.375rem !important;
        }

        .select2-selection__rendered {
            line-height: 38px !important;
        }

        .select2-selection__arrow {
            height: 38px !important;
        }


        /* =========================================================
           WRAPPER PRINCIPAL
        ========================================================= */

        .layout-wrapper {
            position: relative;
            width: 100%;
            min-height: 100vh;
        }

        .layout-container {
            position: relative;
            display: block !important;
            width: 100% !important;
            min-height: 100vh;
        }


        /* =========================================================
           SIDEBAR
        ========================================================= */

        #layout-menu {
            width: var(--sidebar-width) !important;
            min-width: var(--sidebar-width) !important;
            max-width: var(--sidebar-width) !important;
        }


        /* =========================================================
           PAGE PRINCIPALE
        ========================================================= */

        .layout-page {
            position: relative;

            min-width: 0 !important;

            transition:
                margin-left .25s ease,
                width .25s ease;
        }


        /* =========================================================
           CONTENT WRAPPER
        ========================================================= */

        .content-wrapper {
            display: flex;
            flex-direction: column;

            width: 100% !important;
            min-width: 0 !important;
            max-width: 100% !important;

            min-height: calc(100vh - 80px);
        }


        /* =========================================================
           CONTENEUR DE PAGE
        ========================================================= */

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

            padding-left: 28px !important;
            padding-right: 28px !important;
        }


        /* =========================================================
           HEADER
        ========================================================= */

        .layout-navbar {
            width: 100% !important;
            max-width: 100% !important;
        }


        /* =========================================================
           CARDS
        ========================================================= */

        .card {
            width: 100%;
            max-width: 100%;
            min-width: 0;
        }

        .card-body {
            min-width: 0;
        }


        /* =========================================================
           ROW BOOTSTRAP
        ========================================================= */

        .content-wrapper .row {
            --bs-gutter-x: 1.5rem;

            width: auto;
            max-width: none;
        }


        /* =========================================================
           IMAGES / SVG / CANVAS
        ========================================================= */

        img,
        svg,
        canvas,
        video {
            max-width: 100%;
        }


        /* =========================================================
           TABLEAUX
        ========================================================= */

        .table-responsive {
            display: block;

            width: 100% !important;
            max-width: 100% !important;

            overflow-x: auto !important;
            overflow-y: hidden;

            -webkit-overflow-scrolling: touch;
        }

        .table-responsive table {
            margin-bottom: 0;
        }


        /*
         * Important :
         * le tableau peut être plus large que l'écran.
         * C'est UNIQUEMENT la zone du tableau qui défile.
         */

        table {
            max-width: none;
        }


        /* =========================================================
           FORMULAIRES
        ========================================================= */

        input,
        select,
        textarea,
        .form-control,
        .form-select,
        .input-group {
            max-width: 100%;
        }


        /* =========================================================
           GRAPHIQUES
        ========================================================= */

        .chart-container,
        .chart-wrapper {
            position: relative;

            width: 100%;
            max-width: 100%;

            min-width: 0;
        }

        .chart-container canvas,
        .chart-wrapper canvas {
            max-width: 100% !important;
        }


        /* =========================================================
           TEXTE
        ========================================================= */

        .card h1,
        .card h2,
        .card h3,
        .card h4,
        .card h5,
        .card h6,
        .card .fw-bold {
            overflow-wrap: anywhere;
            word-break: normal;
        }


        /* =========================================================
           ORDINATEUR
           Sidebar visible en permanence
        ========================================================= */

        @media (min-width: 1200px) {

            /*
             * Sidebar permanent à gauche
             */

            #layout-menu {
                position: fixed !important;

                top: 0 !important;
                left: 0 !important;
                bottom: 0 !important;

                display: flex !important;

                width: var(--sidebar-width) !important;
                min-width: var(--sidebar-width) !important;
                max-width: var(--sidebar-width) !important;

                height: 100vh !important;

                transform: translate3d(0, 0, 0) !important;

                visibility: visible !important;
                opacity: 1 !important;

                overflow-y: auto;
                overflow-x: hidden;

                z-index: 1080;
            }


            /*
             * La page commence APRÈS le sidebar.
             */

            .layout-page {
                width: calc(100% - var(--sidebar-width)) !important;
                max-width: calc(100% - var(--sidebar-width)) !important;

                margin-left: var(--sidebar-width) !important;

                padding-left: 0 !important;
            }


            /*
             * Le contenu prend toute la largeur disponible.
             */

            .content-wrapper {
                width: 100% !important;
                max-width: 100% !important;
            }


            /*
             * L'overlay mobile doit être invisible.
             */

            .layout-overlay {
                display: none !important;
            }

        }


        /* =========================================================
           TABLETTE + MOBILE
           Sidebar caché par défaut
        ========================================================= */

        @media (max-width: 1199.98px) {

            .layout-container {
                width: 100% !important;
            }


            /*
             * Sidebar hors écran
             */

            #layout-menu {
                position: fixed !important;

                top: 0 !important;
                left: 0 !important;
                bottom: 0 !important;

                width: var(--sidebar-width) !important;
                min-width: var(--sidebar-width) !important;
                max-width: var(--sidebar-width) !important;

                height: 100vh !important;

                transform: translate3d(
                    calc(-1 * var(--sidebar-width)),
                    0,
                    0
                ) !important;

                transition: transform .25s ease !important;

                z-index: 1100;

                overflow-y: auto;
                overflow-x: hidden;
            }


            /*
             * Ouverture du sidebar
             */

            body.layout-menu-expanded #layout-menu,
            html.layout-menu-expanded #layout-menu {
                transform: translate3d(0, 0, 0) !important;
            }


            /*
             * Page 100 %
             */

            .layout-page {
                width: 100% !important;
                max-width: 100% !important;

                margin-left: 0 !important;
                padding-left: 0 !important;
            }

            .content-wrapper {
                width: 100% !important;
                max-width: 100% !important;
            }


            /*
             * Overlay
             */

            .layout-overlay {
                position: fixed;

                top: 0;
                left: 0;

                width: 100vw;
                height: 100vh;

                background: rgba(0, 0, 0, .35);

                z-index: 1090;

                display: none;
            }

            body.layout-menu-expanded .layout-overlay,
            html.layout-menu-expanded .layout-overlay {
                display: block;
            }


            /*
             * Contenu
             */

            .content-wrapper > .container-xxl,
            .content-wrapper > .container-xl,
            .content-wrapper > .container-lg,
            .content-wrapper > .container-md,
            .content-wrapper > .container-sm,
            .content-wrapper > .container,
            .content-wrapper > .container-fluid {

                padding-left: 20px !important;
                padding-right: 20px !important;
            }

        }


        /* =========================================================
           TABLETTE
        ========================================================= */

        @media (max-width: 991.98px) {

            .content-wrapper > .container-xxl,
            .content-wrapper > .container-xl,
            .content-wrapper > .container-lg,
            .content-wrapper > .container-md,
            .content-wrapper > .container-sm,
            .content-wrapper > .container,
            .content-wrapper > .container-fluid {

                padding-left: 16px !important;
                padding-right: 16px !important;
            }

            h1 {
                font-size: 1.8rem;
            }

            h2 {
                font-size: 1.5rem;
            }

        }


        /* =========================================================
           MOBILE
        ========================================================= */

        @media (max-width: 767.98px) {

            .content-wrapper > .container-xxl,
            .content-wrapper > .container-xl,
            .content-wrapper > .container-lg,
            .content-wrapper > .container-md,
            .content-wrapper > .container-sm,
            .content-wrapper > .container,
            .content-wrapper > .container-fluid {

                padding-left: 12px !important;
                padding-right: 12px !important;
            }


            .container-p-y {
                padding-top: 1rem !important;
                padding-bottom: 1rem !important;
            }


            .card {
                width: 100%;
            }


            .card-body {
                padding-left: 15px;
                padding-right: 15px;
            }


            .btn {
                max-width: 100%;
            }


            .content-wrapper .row {
                --bs-gutter-x: 1rem;
            }

        }


        /* =========================================================
           PETITS MOBILES
        ========================================================= */

        @media (max-width: 575.98px) {

            .content-wrapper > .container-xxl,
            .content-wrapper > .container-xl,
            .content-wrapper > .container-lg,
            .content-wrapper > .container-md,
            .content-wrapper > .container-sm,
            .content-wrapper > .container,
            .content-wrapper > .container-fluid {

                padding-left: 10px !important;
                padding-right: 10px !important;
            }


            h1 {
                font-size: 1.55rem;
            }

            h2 {
                font-size: 1.3rem;
            }

            h3 {
                font-size: 1.15rem;
            }

        }

    </style>

</head>


<body>


{{-- ============================================================= --}}
{{-- APPLICATION --}}
{{-- ============================================================= --}}

<div class="layout-wrapper layout-content-navbar">

    <div class="layout-container">


        {{-- ===================================================== --}}
        {{-- SIDEBAR --}}
        {{-- ===================================================== --}}

        @include('sections.sidebar')


        {{-- ===================================================== --}}
        {{-- PAGE --}}
        {{-- ===================================================== --}}

        <div class="layout-page">


            {{-- ================================================= --}}
            {{-- HEADER --}}
            {{-- ================================================= --}}

            @include('sections.header')


            {{-- ================================================= --}}
            {{-- CONTENT --}}
            {{-- ================================================= --}}

            <div class="content-wrapper">


                {{-- ============================================= --}}
                {{-- CONTENU DE LA PAGE --}}
                {{-- ============================================= --}}

                <div
                    class="container-xxl flex-grow-1 container-p-y"
                >

                    @yield('content')

                </div>


                {{-- ============================================= --}}
                {{-- FOOTER --}}
                {{-- ============================================= --}}

                @include('sections.footer')


                <div class="content-backdrop fade"></div>

            </div>

        </div>

    </div>


    {{-- ========================================================= --}}
    {{-- OVERLAY MOBILE --}}
    {{-- ========================================================= --}}

    <div
        class="layout-overlay layout-menu-toggle"
    ></div>

</div>


{{-- ============================================================= --}}
{{-- SCRIPTS PRINCIPAUX --}}
{{-- ============================================================= --}}

@include('sections.scripts')


{{-- ============================================================= --}}
{{-- SCRIPTS PROPRES AUX PAGES --}}
{{-- ============================================================= --}}

@stack('scripts')


{{-- ============================================================= --}}
{{-- GESTION DU SIDEBAR --}}
{{-- ============================================================= --}}

<script>

document.addEventListener('DOMContentLoaded', function () {

    const body = document.body;

    const html = document.documentElement;

    const menu = document.getElementById('layout-menu');

    const toggles =
        document.querySelectorAll('.layout-menu-toggle');


    /*
    |--------------------------------------------------------------------------
    | Clic sur bouton hamburger / overlay
    |--------------------------------------------------------------------------
    */

    toggles.forEach(function (toggle) {

        toggle.addEventListener('click', function (event) {

            /*
             * Sur ordinateur le menu reste permanent.
             *
             * Le bouton hamburger n'a donc pas besoin
             * de masquer le sidebar.
             */
            if (window.innerWidth >= 1200) {

                body.classList.remove(
                    'layout-menu-expanded'
                );

                html.classList.remove(
                    'layout-menu-expanded'
                );

                return;
            }


            /*
             * Mobile / tablette
             */

            event.preventDefault();


            const isExpanded =
                body.classList.contains(
                    'layout-menu-expanded'
                );


            if (isExpanded) {

                body.classList.remove(
                    'layout-menu-expanded'
                );

                html.classList.remove(
                    'layout-menu-expanded'
                );

            } else {

                body.classList.add(
                    'layout-menu-expanded'
                );

                html.classList.add(
                    'layout-menu-expanded'
                );

            }

        });

    });


    /*
    |--------------------------------------------------------------------------
    | Fermer le menu mobile lorsqu'on clique sur un lien
    |--------------------------------------------------------------------------
    */

    if (menu) {

        const menuLinks =
            menu.querySelectorAll('a');


        menuLinks.forEach(function (link) {

            link.addEventListener('click', function () {

                if (window.innerWidth < 1200) {

                    body.classList.remove(
                        'layout-menu-expanded'
                    );

                    html.classList.remove(
                        'layout-menu-expanded'
                    );

                }

            });

        });

    }


    /*
    |--------------------------------------------------------------------------
    | Redimensionnement
    |--------------------------------------------------------------------------
    */

    window.addEventListener('resize', function () {

        if (window.innerWidth >= 1200) {

            body.classList.remove(
                'layout-menu-expanded'
            );

            html.classList.remove(
                'layout-menu-expanded'
            );

        }

    });

});

</script>


</body>

</html>
