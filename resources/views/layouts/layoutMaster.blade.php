<!DOCTYPE html>

<html lang="fr" @class(['dark' => request()->cookie('stcd-theme') === 'dark'])>

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

    {{-- THÈME STCD « ATELIER » : chargé en dernier pour passer devant les styles ci-dessus --}}
    <link rel="stylesheet" href="{{ asset('assets/css/stcd-theme.css') }}?v={{ filemtime(public_path('assets/css/stcd-theme.css')) }}">

    {{-- DESIGN ACTIF (V6) : une seule valeur à changer.
           'dreamspos' : style DreamsPOS (menu blanc, orange #FE9F43)
           'wholesale' : style Wholesale (menu anthracite, vert)
           'atelier'   : design de l'app Atelier (stcd-theme.css seul)
         Chaque design est un fichier à part, chargé par-dessus stcd-theme.css. --}}
    @php
        $stcdDesign = 'dreamspos';
        $stcdPolices = ['dreamspos' => 'Nunito:wght@400;500;600;700;800', 'wholesale' => 'Open+Sans:wght@400;500;600;700'];
    @endphp
    @if(isset($stcdPolices[$stcdDesign]))
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family={{ $stcdPolices[$stcdDesign] }}&display=swap">
        <link rel="stylesheet" href="{{ asset('assets/css/stcd-theme-' . $stcdDesign . '.css') }}?v={{ filemtime(public_path('assets/css/stcd-theme-' . $stcdDesign . '.css')) }}">
    @endif

</head>

<body class="stcd-theme stcd-{{ $stcdDesign }}" @if(request()->routeIs('dashboard')) data-route-dashboard="1" @endif>
<script>
    try { if (localStorage.getItem('stcd-menu-ferme') === '1' && window.innerWidth >= 1200) document.body.classList.add('stcd-menu-ferme'); } catch (e) {}
</script>

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

                {{-- Titre de la page remonté dans la barre du haut dès que le
                     contenu est lu (sans attendre les scripts) : pas de flash. --}}
                <script>
                    try {
                        window.stcdPlacerTitre && window.stcdPlacerTitre();
                        // Design DreamsPOS : le bloc du titre (titre, sous-titre, fil
                        // d'Ariane) passe en tête du contenu ; la barre du haut garde
                        // la recherche.
                        if (document.body.classList.contains('stcd-dreamspos')) {
                            var dpBloc = document.querySelector('.layout-navbar .stcd-ws-bandeau');
                            var dpContenu = document.querySelector('.content-wrapper > .container-xxl');
                            if (dpBloc && dpContenu) {
                                dpBloc.classList.remove('me-3');
                                dpBloc.classList.add('dp-entete-page');
                                dpContenu.insertBefore(dpBloc, dpContenu.firstChild);
                            }
                        }
                        window.stcdAssombrir && window.stcdAssombrir(document.querySelector('.layout-page'));
                        // Tableaux adaptatifs (sans défilement horizontal) : chaque
                        // cellule reçoit le nom de sa colonne (data-label). Sur
                        // tablette en portrait / téléphone, chaque ligne devient une
                        // carte « colonne : valeur » (CSS dans stcd-theme.css).
                        document.querySelectorAll('.content-wrapper table').forEach(function (t) {
                            if (t.closest('.no-cartes, [class*="invoice"], .modal') || t.classList.contains('no-cartes')) return;
                            var entetes = [];
                            var ligneEntete = t.tHead && t.tHead.rows[t.tHead.rows.length - 1];
                            if (!ligneEntete) return;
                            Array.prototype.forEach.call(ligneEntete.cells, function (th) {
                                var texte = (th.textContent || '').replace(/\s+/g, ' ').trim();
                                for (var k = 0; k < (th.colSpan || 1); k++) entetes.push(texte);
                            });
                            t.classList.add('stcd-tableau-adaptatif');
                            Array.prototype.forEach.call(t.tBodies, function (corps) {
                                Array.prototype.forEach.call(corps.rows, function (tr) {
                                    var col = 0;
                                    Array.prototype.forEach.call(tr.cells, function (td) {
                                        if ((td.colSpan || 1) >= entetes.length) { td.classList.add('stcd-cellule-pleine'); }
                                        else if (!td.hasAttribute('data-label')) { td.setAttribute('data-label', entetes[col] || ''); }
                                        col += td.colSpan || 1;
                                    });
                                });
                            });

                            // Grille de petites cartes (tablette portrait / téléphone) :
                            // seulement pour les listes qui ont une page « voir ».
                            // Carte = titre (1re colonne), sous-titre (2e), 2-3 infos
                            // clés (statut, disponibilité…), actions et « Voir plus ».
                            var lignes = Array.prototype.filter.call(t.tBodies.length ? t.tBodies[0].rows : [], function (tr) {
                                return !tr.querySelector('.stcd-cellule-pleine');
                            });
                            var lienVoir = function (tr) {
                                var derniere = tr.cells[tr.cells.length - 1];
                                var liens = derniere ? derniere.querySelectorAll('a[href]') : [];
                                for (var i = 0; i < liens.length; i++) {
                                    var h = liens[i].getAttribute('href') || '';
                                    if (h && h.charAt(0) !== '#' && h.indexOf('javascript') !== 0 && !/\/edit(\/|$|\?)|pdf|imprimer|download|print/i.test(h)) return h;
                                }
                                return null;
                            };
                            var avecVoir = lignes.filter(lienVoir);
                            if (lignes.length && avecVoir.length >= lignes.length / 2) {
                                t.classList.add('stcd-grille');
                                // infos clés par ordre de priorité
                                var priorites = [/statut|status/i, /r[oô]le/i, /^actif|activ/i, /disponib/i, /prix vente|total|montant/i, /date|reçu le/i, /^type/i, /client|fournisseur/i, /t[ée]l[ée]phone/i];
                                // 1re colonne = simple numéro (#, N°, ID) : le titre prend la suivante
                                var debut = /^(#|n°|no|id|)$/i.test((entetes[0] || '').trim()) ? 1 : 0;
                                lignes.forEach(function (tr) {
                                    var cells = tr.cells;
                                    if (!cells.length) return;
                                    if (cells.length <= debut + 1) return;
                                    cells[debut].classList.add('stcd-c-titre');
                                    if (cells.length > debut + 2) cells[debut + 1].classList.add('stcd-c-sous');
                                    cells[cells.length - 1].classList.add('stcd-c-actions');
                                    var n = 0;
                                    priorites.forEach(function (motif) {
                                        for (var i = debut + 2; i < cells.length - 1 && n < 3; i++) {
                                            var c = cells[i];
                                            if (!c.classList.contains('stcd-c-cle') && motif.test(c.getAttribute('data-label') || '')) {
                                                c.classList.add('stcd-c-cle');
                                                n++;
                                                break;
                                            }
                                        }
                                    });
                                    // contenu des cellules regroupé dans un seul bloc : les
                                    // éléments s'empilent au lieu de se serrer côte à côte
                                    Array.prototype.forEach.call(cells, function (c) {
                                        if (c.classList.contains('stcd-c-actions') || c.querySelector(':scope > .stcd-valeur')) return;
                                        if (!(c.classList.contains('stcd-c-titre') || c.classList.contains('stcd-c-sous') || c.classList.contains('stcd-c-cle'))) return;
                                        var bloc = document.createElement('span');
                                        bloc.className = 'stcd-valeur';
                                        while (c.firstChild) bloc.appendChild(c.firstChild);
                                        c.appendChild(bloc);
                                    });
                                    var url = lienVoir(tr);
                                    if (url && !tr.querySelector('.stcd-voir-plus')) {
                                        var td = document.createElement('td');
                                        td.className = 'stcd-voir-plus';
                                        var a = document.createElement('a');
                                        a.href = url;
                                        a.innerHTML = 'Voir plus <span aria-hidden="true">→</span>';
                                        td.appendChild(a);
                                        tr.appendChild(td);
                                    }
                                });
                            }
                        });
                    }
                    finally { document.documentElement.classList.remove('stcd-prep'); }
                </script>

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

@include('sections.session-timeout')

@include('sections.animations')

{{-- Bouton rond « haut de page » (design Wholesale) --}}
<button type="button" class="stcd-ws-haut" id="stcd-ws-haut" title="Haut de page" onclick="window.scrollTo({ top: 0, behavior: 'smooth' })">
    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 19V5m-7 7l7-7 7 7"/></svg>
</button>
<script>
    (function () {
        var b = document.getElementById('stcd-ws-haut');
        var maj = function () { b.classList.toggle('visible', window.scrollY > 300); };
        window.addEventListener('scroll', maj, { passive: true });
        maj();
    })();
</script>

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

{{-- Popup global : disponible depuis le menu, quelle que soit la page. --}}
@include('vehicles._history-modal')

</body>

</html>
