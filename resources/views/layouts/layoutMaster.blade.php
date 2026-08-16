<!DOCTYPE html>
<html lang="fr">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <meta name="csrf-token"
          content="{{ csrf_token() }}">

    <title>
        @yield('title', 'Gestion Pièces')
    </title>


    {{-- ========================================================= --}}
    {{-- STYLES PRINCIPAUX --}}
    {{-- ========================================================= --}}

    @include('sections.styles')


    {{-- ========================================================= --}}
    {{-- SELECT2 CSS --}}
    {{-- ========================================================= --}}

    <link
        href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css"
        rel="stylesheet"
    >


    @stack('styles')


    <style>

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
           STRUCTURE PRINCIPALE
        ========================================================= */

        .layout-wrapper {
            width: 100%;
            min-height: 100vh;
        }

        .layout-container {
            display: flex;
            width: 100%;
            min-height: 100vh;
            position: relative;
        }

        .layout-page {
            flex: 1 1 auto;
            min-width: 0;
            width: auto;
            max-width: 100%;
        }

        .content-wrapper {
            width: 100%;
            min-width: 0;
            max-width: 100%;
        }


        /* =========================================================
           CONTENEUR CENTRAL
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

            padding-left: 24px;
            padding-right: 24px;

            margin-left: auto;
            margin-right: auto;
        }


        /* =========================================================
           EMPÊCHER LE CONTENU DE SORTIR DE L'ÉCRAN
        ========================================================= */

        img,
        svg,
        canvas,
        video {
            max-width: 100%;
        }

        .row {
            max-width: 100%;
        }

        .card {
            max-width: 100%;
            min-width: 0;
        }

        .card-body {
            min-width: 0;
        }


        /* =========================================================
           TABLES
        ========================================================= */

        .table-responsive {
            width: 100%;
            max-width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        table {
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
           GRANDS ÉCRANS
        ========================================================= */

        @media (min-width: 1200px) {

            .layout-page {
                width: calc(100% - 260px);
                max-width: calc(100% - 260px);
            }

        }


        /* =========================================================
           ÉCRANS MOYENS
           Sidebar masquée / contenu 100 %
        ========================================================= */

        @media (max-width: 1199.98px) {

            .layout-container {
                width: 100%;
            }

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

            .content-wrapper > .container-xxl {
                width: 100% !important;
                max-width: 100% !important;

                padding-left: 20px;
                padding-right: 20px;
            }

        }


        /* =========================================================
           TABLETTES
        ========================================================= */

        @media (max-width: 991.98px) {

            .content-wrapper > .container-xxl {
                padding-left: 16px;
                padding-right: 16px;
            }

            h1 {
                font-size: 1.8rem;
            }

            h2 {
                font-size: 1.5rem;
            }

        }


        /* =========================================================
           MOBILES
        ========================================================= */

        @media (max-width: 767.98px) {

            .content-wrapper > .container-xxl {

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

        }


        /* =========================================================
           TRÈS PETITS ÉCRANS
        ========================================================= */

        @media (max-width: 575.98px) {

            .content-wrapper > .container-xxl {

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


        /* =========================================================
           CORRECTION DES TEXTES / GRANDS NOMBRES
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
           CORRECTION BOOTSTRAP ROW
        ========================================================= */

        .content-wrapper .row {

            --bs-gutter-x: 1.5rem;

            width: auto;
        }


        @media (max-width: 767.98px) {

            .content-wrapper .row {

                --bs-gutter-x: 1rem;

            }

        }

    </style>

</head>


<body>


    {{-- ========================================================= --}}
    {{-- LAYOUT --}}
    {{-- ========================================================= --}}

    <div class="layout-wrapper layout-content-navbar">

        <div class="layout-container">


            {{-- ================================================= --}}
            {{-- SIDEBAR --}}
            {{-- ================================================= --}}

            @include('sections.sidebar')


            {{-- ================================================= --}}
            {{-- PAGE --}}
            {{-- ================================================= --}}

            <div class="layout-page">


                {{-- ============================================= --}}
                {{-- HEADER --}}
                {{-- ============================================= --}}

                @include('sections.header')


                {{-- ============================================= --}}
                {{-- CONTENT --}}
                {{-- ============================================= --}}

                <div class="content-wrapper">


                    <div class="container-xxl flex-grow-1 container-p-y">

                        @yield('content')

                    </div>


                    {{-- ========================================= --}}
                    {{-- FOOTER --}}
                    {{-- ========================================= --}}

                    @include('sections.footer')


                    <div class="content-backdrop fade"></div>

                </div>

            </div>

        </div>


        {{-- Overlay utilisé lorsque le menu est ouvert sur mobile --}}
        <div class="layout-overlay layout-menu-toggle"></div>

    </div>



    {{-- ========================================================= --}}
    {{-- SCRIPTS --}}
    {{-- ========================================================= --}}

    @include('sections.scripts')


    @stack('scripts')


    {{-- ========================================================= --}}
    {{-- CORRECTION RESPONSIVE SIDEBAR --}}
    {{-- ========================================================= --}}

    <script>

        document.addEventListener('DOMContentLoaded', function () {

            const menu = document.getElementById('layout-menu');

            const toggles =
                document.querySelectorAll('.layout-menu-toggle');

            const body = document.body;


            toggles.forEach(function (toggle) {

                toggle.addEventListener('click', function () {

                    /*
                     * Sur les petits écrans,
                     * Sneat utilise généralement
                     * layout-menu-expanded.
                     */

                    if (window.innerWidth < 1200) {

                        body.classList.toggle('layout-menu-expanded');

                    }

                });

            });


            /*
             * Lorsque l'utilisateur agrandit l'écran,
             * on supprime l'état mobile.
             */

            window.addEventListener('resize', function () {

                if (window.innerWidth >= 1200) {

                    body.classList.remove('layout-menu-expanded');

                }

            });

        });

    </script>


</body>

</html>
