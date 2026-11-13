<nav class="layout-navbar navbar navbar-expand-xl navbar-detached align-items-center px-4 py-2">

    @php

        /*
        |--------------------------------------------------------------------------
        | UTILISATEUR CONNECTÉ
        |--------------------------------------------------------------------------
        */

        $user = auth()->user();


        /*
        |--------------------------------------------------------------------------
        | NOTIFICATIONS
        |--------------------------------------------------------------------------
        |
        | On récupère les dernières notifications de l'utilisateur.
        |
        | unreadNotifications :
        | notifications qui n'ont pas encore été lues.
        |
        */

        $headerNotifications = collect();

        $headerUnreadCount = 0;

        if ($user) {

            $headerUnreadCount =
                $user
                    ->unreadNotifications()
                    ->count();

            $headerNotifications =
                $user
                    ->notifications()
                    ->latest()
                    ->limit(10)
                    ->get();

        }

    @endphp


    {{-- ============================================================
        STYLE DES NOTIFICATIONS
    ============================================================ --}}

    <style>

        /*
        |--------------------------------------------------------------------------
        | BOUTON CLOCHE
        |--------------------------------------------------------------------------
        */

        .stcd-notification-dropdown {
            position: relative;
        }

        .stcd-notification-button {
            position: relative;
            width: 44px;
            height: 44px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            color: #566176;
            text-decoration: none;
            transition: all .2s ease;
        }

        .stcd-notification-button:hover {
            background: #f1f2ff;
            color: #696cff;
        }

        .stcd-notification-button i {
            font-size: 24px;
        }


        /*
        |--------------------------------------------------------------------------
        | BADGE NOMBRE
        |--------------------------------------------------------------------------
        */

        .stcd-notification-badge {
            position: absolute;
            top: 1px;
            right: 0;
            min-width: 19px;
            height: 19px;
            padding: 0 5px;
            border-radius: 20px;
            background: #ff3e1d;
            color: #ffffff;
            border: 2px solid #ffffff;
            font-size: 10px;
            font-weight: 800;
            line-height: 15px;
            text-align: center;
        }


        /*
        |--------------------------------------------------------------------------
        | DROPDOWN
        |--------------------------------------------------------------------------
        */

        .stcd-notification-menu {
            width: 390px;
            max-width: calc(100vw - 30px);
            padding: 0;
            overflow: hidden;
            border: 1px solid #e7e9ef !important;
            border-radius: 14px !important;
        }


        /*
        |--------------------------------------------------------------------------
        | ENTÊTE DROPDOWN
        |--------------------------------------------------------------------------
        */

        .stcd-notification-header {
            padding: 16px 18px;
            border-bottom: 1px solid #eceef4;
            background: #ffffff;
        }

        .stcd-notification-header-title {
            margin: 0;
            color: #32394d;
            font-size: 16px;
            font-weight: 800;
        }

        .stcd-notification-header-count {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-left: 7px;
            padding: 3px 8px;
            border-radius: 20px;
            background: #696cff;
            color: #ffffff;
            font-size: 10px;
            font-weight: 800;
        }


        /*
        |--------------------------------------------------------------------------
        | LISTE
        |--------------------------------------------------------------------------
        */

        .stcd-notification-list {
            max-height: 420px;
            overflow-y: auto;
            background: #ffffff;
        }

        .stcd-notification-item {
            display: flex;
            gap: 12px;
            padding: 14px 17px;
            color: inherit;
            text-decoration: none;
            border-bottom: 1px solid #f0f1f5;
            transition: background .2s ease;
        }

        .stcd-notification-item:hover {
            background: #f8f8ff;
            color: inherit;
        }

        .stcd-notification-item.unread {
            background: #f6f6ff;
        }

        .stcd-notification-item.unread:hover {
            background: #eeeeff;
        }


        /*
        |--------------------------------------------------------------------------
        | ICÔNE DE NOTIFICATION
        |--------------------------------------------------------------------------
        */

        .stcd-notification-icon {
            flex: 0 0 42px;
            width: 42px;
            height: 42px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #ececff;
            color: #696cff;
        }

        .stcd-notification-icon i {
            font-size: 21px;
        }


        /*
        |--------------------------------------------------------------------------
        | CONTENU
        |--------------------------------------------------------------------------
        */

        .stcd-notification-content {
            flex: 1;
            min-width: 0;
        }

        .stcd-notification-title {
            margin-bottom: 3px;
            color: #30364b;
            font-size: 13px;
            font-weight: 800;
        }

        .stcd-notification-message {
            margin: 0;
            color: #69738a;
            font-size: 12px;
            line-height: 1.45;
            white-space: normal;
        }

        .stcd-notification-time {
            display: block;
            margin-top: 5px;
            color: #a0a7b7;
            font-size: 10px;
        }


        /*
        |--------------------------------------------------------------------------
        | POINT NON LU
        |--------------------------------------------------------------------------
        */

        .stcd-notification-dot {
            flex: 0 0 8px;
            width: 8px;
            height: 8px;
            margin-top: 7px;
            border-radius: 50%;
            background: #696cff;
        }


        /*
        |--------------------------------------------------------------------------
        | AUCUNE NOTIFICATION
        |--------------------------------------------------------------------------
        */

        .stcd-notification-empty {
            padding: 35px 20px;
            text-align: center;
            color: #8c94a6;
        }

        .stcd-notification-empty i {
            display: block;
            margin-bottom: 10px;
            font-size: 35px;
            color: #c2c6d1;
        }


        /*
        |--------------------------------------------------------------------------
        | FOOTER
        |--------------------------------------------------------------------------
        */

        .stcd-notification-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            padding: 10px 12px;
            background: #fafbfc;
            border-top: 1px solid #eceef4;
        }

        .stcd-notification-footer form {
            margin: 0;
        }

        .stcd-notification-action {
            border: 0;
            background: transparent;
            padding: 6px 8px;
            color: #696cff;
            font-size: 11px;
            font-weight: 700;
            cursor: pointer;
        }

        .stcd-notification-action:hover {
            text-decoration: underline;
        }

        .stcd-notification-action-danger {
            color: #e85964;
        }


        /*
        |--------------------------------------------------------------------------
        | MOBILE
        |--------------------------------------------------------------------------
        */

        @media (max-width: 575.98px) {

            .stcd-notification-menu {
                width: 340px;
            }

        }

    </style>


    {{-- ============================================================
        TOGGLE SIDEBAR MOBILE
    ============================================================ --}}

    <div
        class="
            layout-menu-toggle
            navbar-nav
            align-items-xl-center
            me-3
            me-xl-0
            d-xl-none
        "
    >

        <a
            class="nav-item nav-link px-0 me-xl-4 stcd-ws-grille"
            href="javascript:void(0);"
            title="Afficher / masquer le menu"
        >

            <svg width="22" height="22" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><circle cx="5" cy="5" r="1.8"/><circle cx="12" cy="5" r="1.8"/><circle cx="19" cy="5" r="1.8"/><circle cx="5" cy="12" r="1.8"/><circle cx="12" cy="12" r="1.8"/><circle cx="19" cy="12" r="1.8"/><circle cx="5" cy="19" r="1.8"/><circle cx="12" cy="19" r="1.8"/><circle cx="19" cy="19" r="1.8"/></svg>

        </a>

    </div>


    {{-- ============================================================
        LEFT SECTION
    ============================================================ --}}

    {{-- Titre de la page dans la barre du haut, comme l'Atelier : pas de
         répétition. Le titre affiché en haut de la page est remonté ici
         (avec son sous-titre) puis masqué dans la page — voir le script.
         Sous-titre par défaut : « Bienvenue, Prénom — date ». --}}
    @php
        $prenomConnecte = auth()->check()
            ? (preg_split('/\s+/', trim((string) auth()->user()->name))[0] ?? '')
            : '';
    @endphp

    <div class="d-flex align-items-center min-w-0 me-3 stcd-ws-bandeau">

        {{-- Icône du titre (design Wholesale) --}}
        <span class="stcd-ws-titre-icone" aria-hidden="true">
            <svg width="34" height="34" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9h6m-6-4h6m-6 8h4"/></svg>
        </span>

        <div class="min-w-0">

            <h1 class="stcd-topbar-title" id="stcd-topbar-title">
                @hasSection('title')
                    @yield('title')
                @else
                    Tableau de bord
                @endif
            </h1>

            <div class="stcd-topbar-subtitle" id="stcd-topbar-subtitle">
                Bienvenue{{ $prenomConnecte ? ', ' . $prenomConnecte : '' }} — {{ now()->format('d/m/Y') }}
            </div>

        </div>

        {{-- Fil d'Ariane (design Wholesale) : construit depuis le menu --}}
        <nav class="stcd-ws-fil" id="stcd-ws-fil" aria-label="Fil d'Ariane">
            <a href="{{ route('dashboard') }}">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                Accueil
            </a>
        </nav>

    </div>

    <script>
        // Fil d'Ariane « Accueil / Rubrique / Page » d'après l'entrée active
        // du menu, et bouton grille : replie le menu sur ordinateur.
        document.addEventListener('DOMContentLoaded', function () {
            var fil = document.getElementById('stcd-ws-fil');
            var nettoyer = function (el) { return el ? (el.textContent || '').replace(/\s+/g, ' ').trim() : ''; };

            if (fil) {
                var actifs = document.querySelectorAll('#layout-menu .menu-item.active');
                var parties = [];
                actifs.forEach(function (item) {
                    var lien = item.querySelector(':scope > .menu-link');
                    var texte = nettoyer(lien && (lien.querySelector('div') || lien));
                    if (texte && texte !== 'Tableau de bord') parties.push(texte);
                });
                parties.forEach(function (texte, i) {
                    var sep = document.createElement('span');
                    sep.className = 'stcd-ws-fil-sep';
                    sep.textContent = '/';
                    var el = document.createElement('span');
                    el.textContent = texte;
                    if (i === parties.length - 1) el.className = 'stcd-ws-fil-actif';
                    fil.appendChild(sep);
                    fil.appendChild(el);
                });
                if (!parties.length) {
                    fil.querySelector('a').classList.add('stcd-ws-fil-actif');
                }
            }

            var grille = document.querySelector('.stcd-ws-grille');
            if (grille) {
                grille.addEventListener('click', function (e) {
                    if (window.innerWidth >= 1200 && (document.body.classList.contains('stcd-wholesale') || document.body.classList.contains('stcd-dreamspos'))) {
                        e.preventDefault();
                        e.stopImmediatePropagation();
                        document.body.classList.toggle('stcd-menu-ferme');
                        try { localStorage.setItem('stcd-menu-ferme', document.body.classList.contains('stcd-menu-ferme') ? '1' : '0'); } catch (err) {}
                    }
                }, true);
            }
        });
    </script>

    <script>
        // Appelée par layoutMaster juste après le contenu de la page (et, au
        // cas où, au chargement complet) : une seule exécution.
        window.stcdPlacerTitre = function () {
            if (window.stcdTitrePlace) {
                return;
            }
            window.stcdTitrePlace = true;

            var titre = document.getElementById('stcd-topbar-title');
            var sousTitre = document.getElementById('stcd-topbar-subtitle');
            var contenu = document.querySelector('.content-wrapper > .container-xxl');

            if (!titre || !contenu) {
                return;
            }

            var texte = function (el) {
                return (el.textContent || '').replace(/\s+/g, ' ').trim();
            };

            // Zones qui font partie du document (facture, BT, avoir imprimés…) :
            // on ne touche jamais à leurs titres.
            var exclu = function (el) {
                return !!el.closest('.modal, .alert, .card-body table, [class*="invoice"], .no-move-title');
            };

            // 1er titre de la page = titre d'en-tête de la page
            var candidats = contenu.querySelectorAll('h1, h2, h3, h4, .fc-title, .bt-show-title');
            var entete = null;

            for (var i = 0; i < candidats.length; i++) {
                if (!exclu(candidats[i]) && texte(candidats[i]) !== '') {
                    entete = candidats[i];
                    break;
                }
            }

            // Pas de titre dans la page : libellé de l'entrée active du menu
            if (!entete) {
                @unless(View::hasSection('title'))
                    var actifs = document.querySelectorAll('#layout-menu .menu-item.active > .menu-link:not(.menu-toggle) > div');
                    var dernier = actifs.length ? actifs[actifs.length - 1] : null;
                    if (dernier && texte(dernier)) {
                        titre.textContent = texte(dernier);
                    }
                @endunless
                return;
            }

            // Le titre de la page est un simple texte court : on le remonte
            // dans la barre et on le masque dans la page.
            if (entete.querySelector('a, button, form, input, select') || texte(entete).length > 90) {
                return;
            }

            titre.textContent = texte(entete);
            entete.style.display = 'none';

            // Sous-titre juste sous le titre (description courte) : remonté
            // aussi, sauf sur le tableau de bord qui garde « Bienvenue… ».
            var suivant = entete.nextElementSibling;
            var estSousTitre = suivant
                && /^(P|SMALL|DIV|SPAN)$/.test(suivant.tagName)
                && !suivant.querySelector('a, button, form, input, select, table, img, .badge')
                && texte(suivant) !== ''
                && texte(suivant).length <= 160;

            if (estSousTitre) {
                if (!document.body.dataset.routeDashboard && sousTitre) {
                    sousTitre.textContent = texte(suivant);
                }
                suivant.style.display = 'none';
            }

            // Bloc d'en-tête devenu vide (titre + sous-titre seuls, ou avec
            // une simple icône décorative) : masqué pour ne pas laisser un
            // bloc avec seulement l'icône.
            function decoratif(el) {
                return texte(el) === '' && !el.querySelector('a, button, form, input, select, textarea, img, canvas, table');
            }
            var parent = entete.parentElement;
            for (var niveau = 0; niveau < 4 && parent && parent !== contenu; niveau++) {
                var visibles = Array.prototype.filter.call(parent.children, function (enfant) {
                    return enfant.style.display !== 'none' && enfant.tagName !== 'SCRIPT' && enfant.tagName !== 'STYLE'
                        && !decoratif(enfant);
                });
                if (visibles.length) {
                    break;
                }
                parent.style.display = 'none';
                parent = parent.parentElement;
            }
        };

        document.addEventListener('DOMContentLoaded', window.stcdPlacerTitre);
    </script>


    {{-- ============================================================
        RIGHT SECTION
    ============================================================ --}}

    {{-- ============================================================
        RECHERCHE GLOBALE (design DreamsPOS) : pièces, clients, factures,
        BC garage, BC fournisseurs, BT — selon les permissions.
        Raccourci : Ctrl + K.
    ============================================================ --}}
    @auth
    <div class="dp-recherche" id="dp-recherche">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M11 18a7 7 0 100-14 7 7 0 000 14z"/></svg>
        <input type="search" id="dp-recherche-champ" placeholder="Rechercher une pièce, un client, une facture…" autocomplete="off" aria-label="Recherche globale">
        <kbd>Ctrl K</kbd>
        <div class="dp-recherche-resultats" id="dp-recherche-resultats" hidden></div>
    </div>
    @endauth

    <ul class="navbar-nav flex-row align-items-center ms-auto">

        @auth

            {{-- ====================================================
                + AJOUTER (design DreamsPOS) : raccourcis de création,
                seulement ceux que l'utilisateur a le droit d'ouvrir.
            ==================================================== --}}
            @php
                $dpUser = auth()->user();
                $dpPeut = fn (string $route): bool => \Illuminate\Support\Facades\Route::has($route)
                    && (($perm = \App\Services\PermissionService::forRoute($route)) === null || $dpUser->hasPermission($perm));
                $dpRaccourcis = array_filter([
                    ['sales.create', 'Vente', 'M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z'],
                    ['proformas.create', 'Proforma', 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
                    ['products.create', 'Pièce', 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4'],
                    ['customers.create', 'Client', 'M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z'],
                    ['suppliers.create', 'Fournisseur', 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4'],
                    ['vehicles.create', 'Véhicule', 'M8 17a2 2 0 11-4 0 2 2 0 014 0zM20 17a2 2 0 11-4 0 2 2 0 014 0zM13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0'],
                    ['vehicle-part-requests.create', 'Pièce spéciale', 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065zM15 12a3 3 0 11-6 0 3 3 0 016 0z'],
                    ['depot-transfers.create', 'Transfert dépôt', 'M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4'],
                    ['inventory-adjustments.create', 'Ajustement', 'M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4'],
                ], fn ($r) => $dpPeut($r[0]));
            @endphp

            @if($dpRaccourcis)
                <li class="nav-item dropdown dp-ajouter me-2">
                    <a class="dp-btn-ajouter dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown" aria-expanded="false">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                        <span>Ajouter</span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end dp-ajouter-menu">
                        @foreach($dpRaccourcis as [$dpRoute, $dpLibelle, $dpIcone])
                            <a class="dp-ajouter-item" href="{{ route($dpRoute) }}">
                                <span class="dp-ajouter-icone">
                                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $dpIcone }}"/></svg>
                                </span>
                                {{ $dpLibelle }}
                            </a>
                        @endforeach
                    </div>
                </li>
            @endif

            {{-- Plein écran --}}
            <li class="nav-item dp-plein-ecran me-2">
                <a class="dp-icone-btn" href="javascript:void(0);" onclick="dpPleinEcran()" title="Plein écran">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/></svg>
                </a>
            </li>


            {{-- ====================================================
                NOTIFICATIONS
            ==================================================== --}}

            <li
                class="
                    nav-item
                    dropdown
                    stcd-notification-dropdown
                    me-2
                "
            >

                <a
                    class="
                        nav-link
                        dropdown-toggle
                        hide-arrow
                        stcd-notification-button
                    "
                    href="javascript:void(0);"
                    data-bs-toggle="dropdown"
                    data-bs-auto-close="outside"
                    aria-expanded="false"
                    title="Notifications"
                >

                    <svg class="" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>


                    <span id="stcd-notif-badge" class="stcd-notification-badge {{ $headerUnreadCount > 0 ? '' : 'est-vide' }}">
                        {{ $headerUnreadCount > 9 ? '9+' : $headerUnreadCount }}
                    </span>

                </a>


                {{-- =================================================
                    DROPDOWN NOTIFICATIONS
                ================================================= --}}

                <div
                    class="
                        dropdown-menu
                        dropdown-menu-end
                        shadow-lg
                        stcd-notification-menu
                    "
                >


                    {{-- =============================================
                        ENTÊTE
                    ============================================= --}}

                    <div
                        class="
                            stcd-notification-header
                            d-flex
                            align-items-center
                            justify-content-between
                        "
                    >

                        <div class="d-flex align-items-center">

                            <h6 class="stcd-notification-header-title">
                                Notifications
                            </h6>


                            @if($headerUnreadCount > 0)

                                <span
                                    class="
                                        stcd-notification-header-count
                                    "
                                >

                                    {{ $headerUnreadCount }}

                                    non
                                    {{
                                        $headerUnreadCount > 1
                                            ? 'lues'
                                            : 'lue'
                                    }}

                                </span>

                            @endif

                        </div>


                        <i
                            class="bx bx-bell"
                            style="
                                color: #696cff;
                                font-size: 20px;
                            "
                        ></i>

                    </div>


                    {{-- =============================================
                        LISTE DES NOTIFICATIONS
                    ============================================= --}}

                    <div class="stcd-notification-list" id="stcd-notif-liste">

                        @forelse(
                            $headerNotifications
                            as $notification
                        )

                            @php

                                $notificationData =
                                    $notification->data ?? [];

                                $notificationTitle =
                                    $notificationData['title']
                                    ?? 'Notification';

                                $notificationMessage =
                                    $notificationData['message']
                                    ?? '';

                                $notificationOrderNumber =
                                    $notificationData['order_number']
                                    ?? null;

                            @endphp


                            <a
                                href="{{
                                    route(
                                        'notifications.open',
                                        $notification->id
                                    )
                                }}"
                                class="
                                    stcd-notification-item
                                    {{
                                        is_null(
                                            $notification->read_at
                                        )
                                            ? 'unread'
                                            : ''
                                    }}
                                "
                            >

                                {{-- ICÔNE --}}
                                <div class="stcd-notification-icon">

                                    <i class="bx bx-file"></i>

                                </div>


                                {{-- CONTENU --}}
                                <div class="stcd-notification-content">

                                    <div class="stcd-notification-title">

                                        {{ $notificationTitle }}

                                        @if($notificationOrderNumber)

                                            <span
                                                style="
                                                    color: #696cff;
                                                "
                                            >
                                                {{ $notificationOrderNumber }}
                                            </span>

                                        @endif

                                    </div>


                                    @if($notificationMessage)

                                        <p class="stcd-notification-message">

                                            {{ $notificationMessage }}

                                        </p>

                                    @endif


                                    <small class="stcd-notification-time">

                                        {{
                                            optional(
                                                $notification->created_at
                                            )->diffForHumans()
                                        }}

                                    </small>

                                </div>


                                {{-- POINT NON LU --}}
                                @if(is_null($notification->read_at))

                                    <span
                                        class="stcd-notification-dot"
                                    ></span>

                                @endif

                            </a>

                        @empty

                            <div class="stcd-notification-empty" id="stcd-notif-vide">

                                <i class="bx bx-bell-off"></i>

                                <div class="fw-semibold">
                                    Aucune notification
                                </div>

                                <small>
                                    Vous êtes à jour.
                                </small>

                            </div>

                        @endforelse

                    </div>


                    {{-- Réglages de cet appareil : son dans la page, notifications de l'appareil --}}
                    <div class="stcd-notif-reglages">
                        <p class="stcd-notif-reglages-titre">Sur cet appareil</p>
                        <button type="button" id="stcd-notif-son-btn" class="stcd-notif-reglage" onclick="stcdNotifBasculerSon()">🔊 Son dans la page : activé</button>
                        <button type="button" id="stcd-notif-appareil-btn" class="stcd-notif-reglage" onclick="stcdNotifBasculerAppareil()">🔔 Activer les notifications de cet appareil</button>
                    </div>

                    {{-- =============================================
                        ACTIONS
                    ============================================= --}}

                    @if($headerNotifications->isNotEmpty())

                        <div class="stcd-notification-footer">


                            {{-- TOUT MARQUER COMME LU --}}

                            @if($headerUnreadCount > 0)

                                <form
                                    method="POST"
                                    action="{{
                                        route(
                                            'notifications.read-all'
                                        )
                                    }}"
                                >

                                    @csrf

                                    @method('PATCH')


                                    <button
                                        type="submit"
                                        class="
                                            stcd-notification-action
                                        "
                                    >

                                        <i
                                            class="
                                                bx
                                                bx-check-double
                                                me-1
                                            "
                                        ></i>

                                        Tout marquer comme lu

                                    </button>

                                </form>

                            @else

                                <span></span>

                            @endif


                            {{-- EFFACER LES LUES --}}

                            <form
                                method="POST"
                                action="{{
                                    route(
                                        'notifications.clear-read'
                                    )
                                }}"
                            >

                                @csrf

                                @method('DELETE')


                                <button
                                    type="submit"
                                    class="
                                        stcd-notification-action
                                        stcd-notification-action-danger
                                    "
                                >

                                    <i
                                        class="
                                            bx
                                            bx-trash
                                            me-1
                                        "
                                    ></i>

                                    Effacer les lues

                                </button>

                            </form>

                        </div>

                    @endif

                </div>

            </li>


            {{-- Utilisateur, mode nuit et déconnexion : en bas du menu latéral (comme l'Atelier). --}}

            {{-- ====================================================
                PROFIL (design DreamsPOS) : avatar et son menu
            ==================================================== --}}
            @php
                $dpInitiales = collect(preg_split('/\s+/', trim((string) $dpUser->name)))
                    ->filter()->take(2)
                    ->map(fn ($mot) => mb_strtoupper(mb_substr($mot, 0, 1)))
                    ->implode('') ?: 'U';
            @endphp
            <li class="nav-item dropdown dp-profil ms-1">
                <a class="dropdown-toggle hide-arrow dp-profil-btn" href="javascript:void(0);" data-bs-toggle="dropdown" aria-expanded="false" title="{{ $dpUser->name }}">
                    <span class="dp-avatar">{{ $dpInitiales }}</span>
                    <span class="dp-profil-texte">
                        <strong>{{ $dpUser->name }}</strong>
                        <small>{{ $dpUser->getRoleLabel() }}</small>
                    </span>
                </a>
                <div class="dropdown-menu dropdown-menu-end dp-profil-menu">
                    <div class="dp-profil-entete">
                        <span class="dp-avatar dp-avatar-grand">{{ $dpInitiales }}</span>
                        <div>
                            <strong>{{ $dpUser->name }}</strong>
                            <small>{{ $dpUser->email }}</small>
                        </div>
                    </div>
                    <a class="dropdown-item" href="{{ route('profile.edit') }}">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        Mon profil
                    </a>
                    <a class="dropdown-item" href="javascript:void(0);" onclick="stcdBasculerTheme()">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/></svg>
                        Mode nuit / jour
                    </a>
                    <div class="dropdown-divider"></div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="dropdown-item dp-deconnexion">
                            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                            Déconnexion
                        </button>
                    </form>
                </div>
            </li>

        @endauth

    </ul>

{{-- ============================================================
        NOTIFICATIONS EN DIRECT, AVEC SON (comme l'app Atelier)
        Toutes les 20 s : nouvelles notifications → son « ding » + fenêtre
        en bas à droite + cloche à jour. Réglages par appareil dans la
        cloche : son activé / coupé, notifications de l'appareil.
    ============================================================ --}}
    @auth
    @php
        $dernierMsNotif = optional(auth()->user()->notifications()->latest()->value('created_at'), function ($date) {
            return \Illuminate\Support\Carbon::parse($date)->getTimestampMs();
        }) ?? 0;
    @endphp
    <script>
        (function () {
            var CFG = {
                nouvelles: @json(route('notifications.nouvelles')),
                dernier: {{ (int) $dernierMsNotif }},
                user: {{ (int) auth()->id() }}
            };
            var cleDernier = 'stcd-notif-dernier-' + CFG.user;

            function lire(k) { try { return localStorage.getItem(k); } catch (e) { return null; } }
            function ecrire(k, v) { try { localStorage.setItem(k, v); } catch (e) {} }
            function sonActive() { return lire('stcd-notif-son') !== 'off'; }
            function appareilActive() { return lire('stcd-notif-appareil') === 'on' && 'Notification' in window && Notification.permission === 'granted'; }

            // Son « ding » généré (aucun fichier) ; les navigateurs l'autorisent après un premier clic.
            var audio = null;
            function debloquerSon() {
                try {
                    audio = audio || new (window.AudioContext || window.webkitAudioContext)();
                    if (audio.state === 'suspended') audio.resume();
                } catch (e) {}
            }
            document.addEventListener('pointerdown', debloquerSon, { once: true });
            document.addEventListener('keydown', debloquerSon, { once: true });

            function jouerSon() {
                if (!audio) return;
                [[880, 0], [1320, 0.16]].forEach(function (note) {
                    var o = audio.createOscillator(), g = audio.createGain(), t = audio.currentTime + note[1];
                    o.type = 'sine';
                    o.frequency.value = note[0];
                    g.gain.setValueAtTime(0.0001, t);
                    g.gain.exponentialRampToValueAtTime(0.35, t + 0.02);
                    g.gain.exponentialRampToValueAtTime(0.0001, t + 0.45);
                    o.connect(g).connect(audio.destination);
                    o.start(t);
                    o.stop(t + 0.5);
                });
            }

            function echapper(s) {
                return String(s).replace(/[&<>"']/g, function (c) {
                    return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
                });
            }

            // Fenêtre en bas à droite, quelques secondes ; un clic ouvre la page concernée.
            function afficherFenetre(n) {
                var zone = document.getElementById('stcd-notif-fenetres');
                if (!zone) {
                    zone = document.createElement('div');
                    zone.id = 'stcd-notif-fenetres';
                    zone.style.cssText = 'position:fixed;right:16px;bottom:16px;z-index:3000;display:flex;flex-direction:column;gap:8px;max-width:340px;width:calc(100% - 32px)';
                    document.body.appendChild(zone);
                }
                var a = document.createElement('a');
                a.href = n.url;
                a.style.cssText = 'display:block;background:#fff;border:1px solid #e5dcff;border-left:4px solid #5B3FAF;border-radius:12px;padding:10px 14px;box-shadow:0 8px 24px rgba(0,0,0,.15);text-decoration:none;transition:opacity .3s';
                a.innerHTML = '<p style="font-size:13px;font-weight:700;color:#1e293b;margin:0">🔔 ' + echapper(n.titre) + '</p>'
                    + '<p style="font-size:12px;color:#64748b;margin:2px 0 0">' + echapper(n.corps) + '</p>';
                zone.appendChild(a);
                setTimeout(function () { a.style.opacity = '0'; setTimeout(function () { a.remove(); }, 300); }, 8000);
            }

            // Notification de l'appareil (barre Windows / téléphone) quand l'onglet n'est pas regardé.
            function notifierAppareil(n) {
                if (!appareilActive() || document.visibilityState === 'visible') return;
                try {
                    var notif = new Notification(n.titre, { body: n.corps, tag: n.id });
                    notif.onclick = function () { window.focus(); window.location.href = n.url; };
                } catch (e) {}
            }

            function ajouterALaListe(n) {
                var liste = document.getElementById('stcd-notif-liste');
                if (!liste) return;
                var vide = document.getElementById('stcd-notif-vide');
                if (vide) vide.remove();
                var a = document.createElement('a');
                a.href = n.url;
                a.className = 'stcd-notification-item unread';
                a.innerHTML = '<div class="stcd-notification-content">'
                    + '<div class="stcd-notification-title">' + echapper(n.titre) + '</div>'
                    + '<p class="stcd-notification-message">' + echapper(n.corps) + '</p>'
                    + '<small class="stcd-notification-time">à l\'instant</small></div>'
                    + '<span class="stcd-notification-dot"></span>';
                liste.prepend(a);
            }

            function majBadge(nb) {
                var b = document.getElementById('stcd-notif-badge');
                if (!b) return;
                b.textContent = nb > 9 ? '9+' : nb;
                b.classList.toggle('est-vide', nb <= 0);
            }

            function interroger() {
                // Plusieurs onglets ouverts : chaque notification ne sonne qu'une fois.
                var dejaVu = parseInt(lire(cleDernier) || '0', 10);
                var depuis = Math.max(dejaVu, CFG.dernier);

                fetch(CFG.nouvelles + '?depuis=' + depuis, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
                    .then(function (r) {
                        if (r.status === 401 && window.stcdSessionExpiree) { window.stcdSessionExpiree(); return null; }
                        return r.ok ? r.json() : null;
                    })
                    .then(function (d) {
                        if (!d) return;
                        majBadge(d.non_lues);
                        var vu = parseInt(lire(cleDernier) || '0', 10);
                        var neuves = d.nouvelles.filter(function (n) { return n.ms > vu; });
                        CFG.dernier = Math.max(CFG.dernier, d.dernier);
                        ecrire(cleDernier, Math.max(vu, d.dernier));
                        neuves.forEach(function (n) { afficherFenetre(n); ajouterALaListe(n); notifierAppareil(n); });
                        if (neuves.length && sonActive()) jouerSon();
                    })
                    .catch(function () {});
            }

            if (lire(cleDernier) === null) ecrire(cleDernier, CFG.dernier);
            setTimeout(interroger, 3000);
            setInterval(interroger, 20000);

            // ── Réglages de cet appareil (dans la cloche) ──
            function majBoutons() {
                var son = document.getElementById('stcd-notif-son-btn');
                if (son) son.textContent = sonActive() ? '🔊 Son dans la page : activé' : '🔇 Son dans la page : coupé';

                var app = document.getElementById('stcd-notif-appareil-btn');
                if (app) {
                    if (!('Notification' in window)) { app.hidden = true; return; }
                    app.textContent = appareilActive()
                        ? '🔕 Désactiver les notifications de cet appareil'
                        : '🔔 Activer les notifications de cet appareil';
                }
            }

            window.stcdNotifBasculerSon = function () {
                ecrire('stcd-notif-son', sonActive() ? 'off' : 'on');
                majBoutons();
                if (sonActive()) { debloquerSon(); jouerSon(); }
            };

            window.stcdNotifBasculerAppareil = function () {
                if (appareilActive()) {
                    ecrire('stcd-notif-appareil', 'off');
                    majBoutons();
                    return;
                }
                Notification.requestPermission().then(function (p) {
                    ecrire('stcd-notif-appareil', p === 'granted' ? 'on' : 'off');
                    majBoutons();
                });
            };

            document.addEventListener('DOMContentLoaded', majBoutons);
        })();
    </script>
    @endauth

</nav>

@auth
<script>
    // Plein écran (design DreamsPOS)
    window.dpPleinEcran = function () {
        if (!document.fullscreenElement) {
            document.documentElement.requestFullscreen && document.documentElement.requestFullscreen();
        } else {
            document.exitFullscreen && document.exitFullscreen();
        }
    };

    // Recherche globale : résultats groupés, clavier (↑ ↓ Entrée Échap, Ctrl + K)
    (function () {
        var champ = document.getElementById('dp-recherche-champ');
        var boite = document.getElementById('dp-recherche-resultats');
        if (!champ || !boite) return;

        var url = @json(route('recherche'));
        var minuteur = null, controleur = null, index = -1;

        function echapper(t) {
            return String(t || '').replace(/[&<>"']/g, function (c) {
                return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
            });
        }

        function liens() { return boite.querySelectorAll('a.dp-resultat'); }

        function surligner(i) {
            var l = liens();
            l.forEach(function (a) { a.classList.remove('actif'); });
            index = Math.max(-1, Math.min(i, l.length - 1));
            if (index >= 0) { l[index].classList.add('actif'); l[index].scrollIntoView({ block: 'nearest' }); }
        }

        function afficher(html) { boite.innerHTML = html; boite.hidden = false; index = -1; }
        function fermer() { boite.hidden = true; index = -1; }

        function chercher() {
            var q = champ.value.trim();
            if (q.length < 2) { fermer(); return; }
            if (controleur) controleur.abort();
            controleur = new AbortController();
            afficher('<div class="dp-recherche-info">Recherche…</div>');
            fetch(url + '?q=' + encodeURIComponent(q), { headers: { 'Accept': 'application/json' }, credentials: 'same-origin', signal: controleur.signal })
                .then(function (r) {
                    if (r.status === 401 && window.stcdSessionExpiree) { window.stcdSessionExpiree(); return null; }
                    return r.ok ? r.json() : null;
                })
                .then(function (d) {
                    if (!d) return;
                    if (!d.groupes.length) { afficher('<div class="dp-recherche-info">Aucun résultat pour « ' + echapper(q) + ' »</div>'); return; }
                    afficher(d.groupes.map(function (g) {
                        return '<div class="dp-recherche-groupe">' + echapper(g.titre) + '</div>'
                            + g.items.map(function (it) {
                                return '<a class="dp-resultat" href="' + echapper(it.url) + '"><strong>' + echapper(it.titre) + '</strong><small>' + echapper(it.detail) + '</small></a>';
                            }).join('');
                    }).join(''));
                })
                .catch(function () {});
        }

        champ.addEventListener('input', function () {
            clearTimeout(minuteur);
            minuteur = setTimeout(chercher, 250);
        });

        champ.addEventListener('focus', function () { if (boite.innerHTML && champ.value.trim().length >= 2) boite.hidden = false; });

        champ.addEventListener('keydown', function (e) {
            if (e.key === 'ArrowDown') { e.preventDefault(); surligner(index + 1); }
            else if (e.key === 'ArrowUp') { e.preventDefault(); surligner(index - 1); }
            else if (e.key === 'Enter') {
                var l = liens();
                var cible = index >= 0 ? l[index] : l[0];
                if (cible) { e.preventDefault(); window.location.href = cible.href; }
            } else if (e.key === 'Escape') { fermer(); champ.blur(); }
        });

        document.addEventListener('click', function (e) {
            if (!e.target.closest('#dp-recherche')) fermer();
        });

        document.addEventListener('keydown', function (e) {
            if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
                e.preventDefault();
                champ.focus();
                champ.select();
            }
        });
    })();
</script>
@endauth
