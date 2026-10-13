@php

    $user = auth()->user();

    // Une entrée du menu n'est affichée que si l'utilisateur a la permission
    // de la page (App\Services\PermissionService::$routes).
    $peut = fn (string $route): bool => $user !== null
        && (($permission = \App\Services\PermissionService::forRoute($route)) === null
            || $user->hasPermission($permission));

    /**

     * --------------------------------------------------------------------------

     * NOUVELLES COMMANDES REÇUES DU GARAGE

     * --------------------------------------------------------------------------

     * Compteur des commandes App Atelier qui n'ont pas encore été ouvertes.

     * Visible pour : admin, chef_magasinier, vendeur.

     */

    $nouvellesCommandesGarage = 0;

    if (

        $user &&

        $user->hasPermission('voir_commandes_garage')

    ) {

        $nouvellesCommandesGarage =

            \App\Models\ExternalBonCommande::whereNull('vu_at')->count();

    }

@endphp

{{-- ============================================================

     CORRECTION SIDEBAR STCD MOTORS

     - Visible en permanence sur ordinateur

     - Coulissant sur tablette/mobile

     - Le contenu reste entièrement visible à droite

============================================================ --}}

<style>

    :root {

        --stcd-sidebar-width: 280px;

    }

    #layout-menu {

        width: var(--stcd-sidebar-width) !important;

        min-width: var(--stcd-sidebar-width) !important;

        max-width: var(--stcd-sidebar-width) !important;

        overflow-x: hidden !important;

        overflow-y: auto !important;

        transition: transform .25s ease !important;

    }

    #layout-menu .menu-inner {

        width: 100% !important;

        margin: 0 !important;

        padding-left: 0 !important;

        padding-right: 0 !important;

    }

    #layout-menu .menu-item,

    #layout-menu .menu-link,

    #layout-menu .menu-sub {

        width: 100% !important;

    }

    #layout-menu .menu-link {

        display: flex !important;

        align-items: center !important;

        text-decoration: none !important;

    }

    /* Empêche le libellé d'un lien de repousser (ou de faire chevaucher)

       son badge de notification : le texte se tronque avec "…" au lieu de

       déborder derrière le badge. */

    #layout-menu .menu-link > .flex-grow-1 {

        min-width: 0 !important;

    }

    @media (min-width: 1200px) {

        #layout-menu {

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

            transform: translate3d(0, 0, 0) !important;

            visibility: visible !important;

            opacity: 1 !important;

            z-index: 2000 !important;

        }

        .layout-container {

            width: 100% !important;

            max-width: 100% !important;

            margin: 0 !important;

            padding: 0 !important;

        }

        .layout-page {

            width: calc(100% - var(--stcd-sidebar-width)) !important;

            max-width: calc(100% - var(--stcd-sidebar-width)) !important;

            min-width: 0 !important;

            margin-left: var(--stcd-sidebar-width) !important;

            padding-left: 0 !important;

        }

        .content-wrapper,

        .layout-navbar {

            width: 100% !important;

            max-width: 100% !important;

            min-width: 0 !important;

            margin-left: 0 !important;

        }

        .layout-overlay {

            display: none !important;

        }

    }

    @media (max-width: 1199.98px) {

        #layout-menu {

            position: fixed !important;

            top: 0 !important;

            left: 0 !important;

            bottom: 0 !important;

            width: var(--stcd-sidebar-width) !important;

            min-width: var(--stcd-sidebar-width) !important;

            max-width: var(--stcd-sidebar-width) !important;

            height: 100vh !important;

            transform: translateX(calc(-1 * var(--stcd-sidebar-width))) !important;

            visibility: visible !important;

            opacity: 1 !important;

            z-index: 2000 !important;

        }

        body.layout-menu-expanded #layout-menu,

        html.layout-menu-expanded #layout-menu {

            transform: translateX(0) !important;

        }

        .layout-page {

            width: 100% !important;

            max-width: 100% !important;

            margin-left: 0 !important;

        }

        .layout-overlay {

            position: fixed !important;

            inset: 0 !important;

            width: 100vw !important;

            height: 100vh !important;

            background: rgba(15, 23, 42, .45) !important;

            display: none !important;

            z-index: 1900 !important;

        }

        body.layout-menu-expanded .layout-overlay,

        html.layout-menu-expanded .layout-overlay {

            display: block !important;

        }

    }

    #layout-menu::-webkit-scrollbar {

        width: 6px;

    }

    #layout-menu::-webkit-scrollbar-track {

        background: transparent;

    }

    #layout-menu::-webkit-scrollbar-thumb {

        background: rgba(255, 255, 255, .18);

        border-radius: 10px;

    }

</style>

<aside id="layout-menu"

       class="layout-menu menu-vertical menu bg-menu-theme">

    {{-- ============================================================ --}}

    {{-- EN-TÊTE / LOGO STCD MOTORS --}}

    {{-- ============================================================ --}}

    <style>

        /* ============================================================

        EN-TÊTE DU SIDEBAR

        ============================================================ */

        #layout-menu .stcd-sidebar-brand {

            position: relative;

            display: flex;

            align-items: center;

            width: 100%;

            min-height: 96px;

            padding: 14px 18px;

            border-bottom: 1px solid rgba(255, 255, 255, 0.16);

            overflow: hidden;

        }

        /*

        * Le lien occupe l'espace disponible mais laisse

        * suffisamment de place à la flèche.

        */

        #layout-menu .stcd-brand-link {

            display: flex;

            align-items: center;

            flex: 1 1 auto;

            min-width: 0;

            padding: 0;

            margin: 0;

            text-decoration: none;

        }

        /* ============================================================

        LOGO

        ============================================================ */

        #layout-menu .stcd-brand-logo {

            display: flex;

            align-items: center;

            justify-content: center;

            width: 48px;

            min-width: 48px;

            height: 48px;

            margin-right: 12px;

            background: #ffffff;

            border-radius: 50%;

            box-shadow:

                0 3px 8px rgba(0, 0, 0, 0.22),

                0 0 0 1px rgba(255, 255, 255, 0.15);

            overflow: hidden;

        }

        #layout-menu .stcd-brand-logo img {

            display: block;

            width: 100%;

            height: 100%;

            padding: 4px;

            object-fit: contain;

            border-radius: 50%;

        }

    /* ============================================================

       TEXTE

       ============================================================ */

    #layout-menu .stcd-brand-text {

        display: flex;

        flex-direction: column;

        justify-content: center;

        flex: 1 1 auto;

        min-width: 0;

        line-height: 1;

    }

    #layout-menu .stcd-brand-name {

        display: block;

        margin: 0;

        padding: 0;

        color: #8b5cf6;

        font-size: 22px;

        font-weight: 700;

        line-height: 1.05;

        letter-spacing: 0;

        white-space: normal;

    }

    #layout-menu .stcd-brand-city {

        display: block;

        margin-top: 4px;

        padding: 0;

        color: rgba(255, 255, 255, 0.72);

        font-size: 14px;

        font-weight: 400;

        line-height: 1;

        white-space: nowrap;

    }

    /* ============================================================

       FLÈCHE

       ============================================================ */

    #layout-menu .stcd-sidebar-toggle {

        display: flex !important;

        align-items: center;

        justify-content: center;

        flex: 0 0 40px;

        width: 40px;

        min-width: 40px;

        height: 40px;

        margin-left: 8px;

        padding: 0;

        color: #ffffff !important;

        text-decoration: none;

        border-radius: 50%;

        cursor: pointer;

        transition:

            background-color 0.2s ease,

            transform 0.2s ease;

    }

    #layout-menu .stcd-sidebar-toggle:hover {

        background-color: rgba(255, 255, 255, 0.08);

    }

    #layout-menu .stcd-sidebar-toggle i {

        display: flex;

        align-items: center;

        justify-content: center;

        margin: 0 !important;

        color: #ffffff !important;

        font-size: 28px !important;

        line-height: 1 !important;

    }

    /*

     * Important :

     * contrairement à l'ancienne version, la flèche

     * n'utilise plus d-xl-none.

     *

     * Elle reste donc visible également sur ordinateur.

     */

    @media (min-width: 1200px) {

        #layout-menu .stcd-sidebar-toggle {

            display: flex !important;

        }

    }

    /* ============================================================

       TABLETTE / MOBILE

       ============================================================ */

    @media (max-width: 1199.98px) {

        #layout-menu .stcd-sidebar-brand {

            min-height: 88px;

            padding: 12px 16px;

        }

        #layout-menu .stcd-brand-logo {

            width: 46px;

            min-width: 46px;

            height: 46px;

            margin-right: 11px;

        }

        #layout-menu .stcd-brand-name {

            font-size: 20px;

        }

        #layout-menu .stcd-brand-city {

            font-size: 13px;

        }

    }

</style>

{{-- ============================================================ --}}

{{-- EN-TÊTE --}}

{{-- ============================================================ --}}

<div class="app-brand demo stcd-sidebar-brand">

    {{-- ======================================================== --}}

    {{-- LOGO + NOM --}}

    {{-- ======================================================== --}}

    <a href="{{ route('dashboard') }}"

       class="app-brand-link stcd-brand-link">

        {{-- LOGO --}}

        <span class="app-brand-logo demo stcd-brand-logo">

            <img

                src="{{ asset('assets/img/logo/stcd.jpg') }}"

                alt="STCD Motors"

            >

        </span>

        {{-- NOM DE L'APPLICATION --}}

        <span class="stcd-brand-text">

            <span class="stcd-brand-name">

                STCD Motors

            </span>

            <span class="stcd-brand-city">

                Your Dream Car,<br>One Visit Away

            </span>

        </span>

    </a>

</div>

{{-- OMBRE SOUS L'EN-TÊTE --}}

<div class="menu-inner-shadow"></div>

    <ul class="menu-inner py-2">

        {{-- DASHBOARD --}}

        <li class="menu-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">

            <a href="{{ route('dashboard') }}"

               class="menu-link">

                <svg class="menu-icon" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>

                <div>Tableau de bord</div>

            </a>

        </li>

        {{-- ===================================================== --}}

        {{-- REFERENTIELS --}}

        {{-- ===================================================== --}}

        <li class="menu-header small text-uppercase">

            <span class="menu-header-text">

                Référentiels

            </span>

        </li>

        {{-- ===================================================== --}}

        {{-- PRODUITS --}}

        {{-- ===================================================== --}}

        @if($peut('products.index') || $peut('products.available') || $peut('products.sold') || $peut('products.to-order'))

            <li class="menu-item

                {{

                    request()->routeIs('products.*')

                        ? 'active open'

                        : ''

                }}"

            >

                <a href="javascript:void(0);"

                class="menu-link menu-toggle">

                    <svg class="menu-icon" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>

                    <div>Produits</div>

                </a>

                <ul class="menu-sub">

                    {{-- GESTION DES PRODUITS --}}

                    <li class="menu-header small text-uppercase">

                        <span class="menu-header-text">

                            Gestion des produits

                        </span>

                    </li>

                    @if($peut('products.index'))
                    <li class="menu-item

                        {{

                            request()->routeIs(

                                'products.index',

                                'products.show',

                                'products.create',

                                'products.edit'

                            )

                                ? 'active'

                                : ''

                        }}"

                    >

                        <a href="{{ route('products.index') }}"

                        class="menu-link">

                            <div>Tous les produits</div>

                        </a>

                    </li>
                    @endif

                    @if($peut('products.available'))
                    <li class="menu-item

                        {{

                            request()->routeIs('products.available')

                                ? 'active'

                                : ''

                        }}"

                    >

                        <a href="{{ route('products.available') }}"

                        class="menu-link">

                            <div>Produits disponibles</div>

                        </a>

                    </li>
                    @endif

                    {{-- ===================================================== --}}

                    {{-- PIÈCES NON DISPONIBLES --}}

                    {{-- ===================================================== --}}

                    {{--

                    <li class="menu-item

                        {{

                            request()->routeIs('products.unavailable')

                                ? 'active'

                                : ''

                        }}"

                        >

                        <a

                            href="{{ route('products.unavailable') }}"

                            class="menu-link"

                        >

                            <div>

                                Pièces non disponibles

                            </div>

                        </a>

                    </li>

                    --}}

                    @if($peut('products.sold'))
                    <li class="menu-item

                        {{

                            request()->routeIs('products.sold')

                                ? 'active'

                                : ''

                        }}"

                    >

                        <a href="{{ route('products.sold') }}"

                        class="menu-link">

                            <div>Produits vendus</div>

                        </a>

                    </li>
                    @endif

                    {{-- RÉAPPROVISIONNEMENT --}}

                    @if($peut('products.to-order'))

                        <li class="menu-header small text-uppercase">

                            <span class="menu-header-text">

                                Réapprovisionnement

                            </span>

                        </li>

                        @if($peut('products.to-order'))
                        <li class="menu-item

                            {{

                                request()->routeIs('products.to-order')

                                    ? 'active'

                                    : ''

                            }}"

                        >

                            <a href="{{ route('products.to-order') }}"

                            class="menu-link">

                                <div>Pièces à commander</div>

                            </a>

                        </li>
                        @endif

                    @endif

                </ul>

            </li>

        @endif

        {{-- CATEGORIES --}}

        @if($peut('categories.index'))
        <li class="menu-item {{ request()->routeIs('categories.*') ? 'active' : '' }}">

            <a href="{{ route('categories.index') }}"

               class="menu-link">

                <svg class="menu-icon" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>

                <div>Catégories</div>

            </a>

        </li>
        @endif

        {{-- FOURNISSEURS --}}

        @if($peut('suppliers.index'))
        <li class="menu-item {{ request()->routeIs('suppliers.*') ? 'active' : '' }}">

            <a href="{{ route('suppliers.index') }}"

               class="menu-link">

                <svg class="menu-icon" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>

                <div>Fournisseurs</div>

            </a>

        </li>
        @endif

       {{-- ===================================================== --}}

    {{-- CLIENTS --}}

    {{-- ===================================================== --}}

    <li class="menu-item

        {{

            request()->routeIs('customers.*')

            ? 'active open'

            : ''

        }}"

    >

        {{-- MENU PRINCIPAL --}}

        <a

            href="javascript:void(0);"

            class="menu-link menu-toggle"

        >

            <svg class="menu-icon" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>

            <div>

                Clients

            </div>

        </a>

        {{-- SOUS-MENU --}}

        <ul class="menu-sub">

            {{-- =============================================== --}}

            {{-- LISTE DES CLIENTS --}}

            {{-- =============================================== --}}

            @if($peut('customers.index'))
            <li class="menu-item

                {{

                    request()->routeIs(

                        'customers.index',

                        'customers.create',

                        'customers.edit'

                    )

                        ? 'active'

                        : ''

                }}"

            >

                <a

                    href="{{ route('customers.index') }}"

                    class="menu-link"

                >

                    <div>

                        Liste des clients

                    </div>

                </a>

            </li>
            @endif

            {{-- =============================================== --}}

            {{-- HISTORIQUE DES CLIENTS --}}

            {{-- =============================================== --}}

            @if($peut('customers.history'))
            <li class="menu-item

                {{

                    request()->routeIs(

                        'customers.history',

                        'customers.show'

                    )

                        ? 'active'

                        : ''

                }}"

            >

                <a

                    href="{{ route('customers.history') }}"

                    class="menu-link"

                >

                    <div>

                        Historique des clients

                    </div>

                </a>

            </li>
            @endif

        </ul>

    </li>

        {{-- ===================================================== --}}

        {{-- VÉHICULES --}}

        {{-- ===================================================== --}}

        @if($peut('vehicles.index') || $peut('vehicles.create') || $peut('vehicles.history') || $peut('vehicle-part-requests.index') || $peut('vehicle-part-requests.ordered') || $peut('vehicle-part-requests.received'))

            <li class="menu-item

                {{

                    request()->routeIs('vehicles.*')

                    || request()->routeIs('vehicle-part-requests.*')

                        ? 'active open'

                        : ''

                }}"

            >

                {{-- MENU PRINCIPAL --}}

                <a href="javascript:void(0);"

                class="menu-link menu-toggle">

                    <svg class="menu-icon" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10l2 1m0-7h8m0 0V6a1 1 0 00-1-1H9M5 16H3"/></svg>

                    <div
                        style="
                            display: flex;
                            flex-direction: column;
                            justify-content: center;
                            line-height: 1.15;
                        "
                    >
                        <span>
                            Véhicules/Dépot
                        </span>

                        <span
                            style="
                                margin-top: 3px;
                                font-size: 9px;
                                font-weight: 700;
                                letter-spacing: .45px;
                                text-transform: uppercase;
                                opacity: .65;
                            "
                        >
                            Pièces spéciales
                        </span>
                    </div>

                </a>

                <ul class="menu-sub">

                    {{-- ================================================= --}}

                    {{-- 1. TRAÇABILITÉ DES VÉHICULES --}}

                    {{-- ================================================= --}}

                    <li class="menu-header small text-uppercase"

                        style="

                            padding-left: 1rem;

                            margin-top: 8px;

                            margin-bottom: 4px;

                        ">

                        <span class="menu-header-text"

                            style="

                                font-size: 10px;

                                font-weight: 700;

                                letter-spacing: .5px;

                                opacity: .65;

                            ">

                            Traçabilité des véhicules

                        </span>

                    </li>

                    {{-- LISTE DES VÉHICULES --}}

                    @if($peut('vehicles.index'))
                    <li class="menu-item

                        {{

                            request()->routeIs(

                                'vehicles.index',

                                'vehicles.show',

                                'vehicles.edit'

                            )

                                ? 'active'

                                : ''

                        }}"

                    >

                        <a href="{{ route('vehicles.index') }}"

                        class="menu-link">

                            <div>

                                Liste des véhicules

                            </div>

                        </a>

                    </li>
                    @endif

                    {{-- NOUVEAU VÉHICULE --}}

                    <!--li class="menu-item

                        { {

                            request()->routeIs('vehicles.create')

                                ? 'active'

                                : ''

                        }}"

                     >

                        <a href="{ { route('vehicles.create') }}"

                        class="menu-link">

                            <div>

                                Nouveau véhicule

                            </div>

                        </a>

                    </li-->

                    {{-- HISTORIQUE / TRAÇABILITÉ --}}

                    @if($peut('vehicles.history'))
                    <li class="menu-item

                        {{

                            request()->routeIs('vehicles.history')

                                ? 'active'

                                : ''

                        }}"

                    >

                        <a href="{{ route('vehicles.history') }}"
                            data-vehicle-history-popup="true"
                            aria-haspopup="dialog"
                            aria-controls="vehicleHistoryModal"

                        class="menu-link">

                            <div>

                                Historique / Traçabilité

                            </div>

                        </a>

                    </li>
                    @endif

                    {{-- ================================================= --}}

                    {{-- 2. SUIVI DES PIÈCES --}}

                    {{-- ================================================= --}}

                    @if($peut('vehicle-part-requests.index') || $peut('vehicle-part-requests.ordered') || $peut('vehicle-part-requests.received'))

                        <li class="menu-header small text-uppercase"

                            style="

                                padding-left: 1rem;

                                margin-top: 18px;

                                margin-bottom: 4px;

                            ">

                            <span class="menu-header-text"

                                style="

                                    font-size: 10px;

                                    font-weight: 700;

                                    letter-spacing: .5px;

                                    opacity: .65;

                                ">

                                Suivi des pièces

                            </span>

                        </li>

                        {{-- TOUTES LES PIÈCES --}}

                        @if($peut('vehicle-part-requests.index'))
                        <li class="menu-item

                            {{

                                request()->routeIs(

                                    'vehicle-part-requests.index',

                                    'vehicle-part-requests.create',

                                    'vehicle-part-requests.store',

                                    'vehicle-part-requests.show',

                                    'vehicle-part-requests.edit',

                                    'vehicle-part-requests.update',

                                    'vehicle-part-requests.change-status'

                                )

                                    ? 'active'

                                    : ''

                            }}"

                        >

                            <a href="{{ route('vehicle-part-requests.index') }}"

                            class="menu-link">

                                <div>

                                    Toutes les pièces

                                </div>

                            </a>

                        </li>
                        @endif

                        {{-- PIÈCES COMMANDÉES --}}

                        @if($peut('vehicle-part-requests.ordered'))
                        <li class="menu-item

                            {{

                                request()->routeIs(

                                    'vehicle-part-requests.ordered'

                                )

                                    ? 'active'

                                    : ''

                            }}"

                        >

                            <a href="{{ route('vehicle-part-requests.ordered') }}"

                            class="menu-link">

                                <div>

                                    Pièces commandées

                                </div>

                            </a>

                        </li>
                        @endif

                        {{-- PIÈCES REÇUES --}}

                        @if($peut('vehicle-part-requests.received'))
                        <li class="menu-item

                            {{

                                request()->routeIs(

                                    'vehicle-part-requests.received'

                                )

                                    ? 'active'

                                    : ''

                            }}"

                        >

                            <a href="{{ route('vehicle-part-requests.received') }}"

                            class="menu-link">

                                <div>

                                    Pièces reçues

                                </div>

                            </a>

                        </li>
                        @endif

                        {{-- PIÈCES NON TROUVÉES --}}

                        <li class="menu-item

                            {{

                                request()->routeIs(

                                    'vehicle-part-requests.not-found'

                                )

                                    ? 'active'

                                    : ''

                            }}"

                        >

                            <a href="{{ route(

                                'vehicle-part-requests.not-found'

                            ) }}"

                            class="menu-link">

                                <div>

                                    Pièces non trouvées

                                </div>

                            </a>

                        </li>

                    @endif

                </ul>

            </li>

        @endif

         {{-- ===================================================== --}}

        {{-- BONS DE COMMANDE FOURNISSEURS --}}

        {{-- ===================================================== --}}

        @if($peut('supplier-orders.index'))

            @if($peut('supplier-orders.index'))
            <li class="menu-item

                {{

                    request()->routeIs('supplier-orders.*')

                        ? 'active'

                        : ''

                }}

            ">

                <a

                    href="{{ route('supplier-orders.index') }}"

                    class="menu-link"

                >

                    <svg class="menu-icon" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>

                    <div class="flex-grow-1 text-truncate">

                        Bons de commande

                    </div>

                </a>

            </li>
            @endif

        @endif

        {{-- ===================================================== --}}

        {{-- GESTION DU STOCK --}}

        {{-- ===================================================== --}}

        <li class="menu-header small text-uppercase">

            <span class="menu-header-text">

                Gestion du stock

            </span>

        </li>

        {{-- DEPOTS --}}

        @if($peut('depots.index'))

        @if($peut('depots.index'))
        <li class="menu-item

            {{

                request()->routeIs('depots.*')

                ? 'active open'

                : ''

            }}">

            <a href="{{ route('depots.index') }}"

               class="menu-link">

                <svg class="menu-icon" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>

                <div>Dépôts</div>

            </a>

        </li>
        @endif

        @endif



        {{-- ===================================================== --}}

        {{-- TRANSFERTS ENTRE DÉPÔTS --}}

        {{-- ===================================================== --}}

        @if($peut('depot-transfers.index'))

            @if($peut('depot-transfers.index'))
            <li class="menu-item

                {{

                    request()->routeIs('depot-transfers.*')

                        ? 'active'

                        : ''

                }}

            ">

                <a href="{{ route('depot-transfers.index') }}"

                class="menu-link">

                    <svg class="menu-icon" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>

                    <div>

                        Transferts dépôts

                    </div>

                </a>

            </li>
            @endif

        @endif

        {{-- AJUSTEMENTS INVENTAIRE --}}

        @if($peut('inventory-adjustments.index'))

        @if($peut('inventory-adjustments.index'))
        <li class="menu-item

            {{

                request()->routeIs('inventory-adjustments.*')

                ? 'active'

                : ''

            }}">

            <a href="{{ route('inventory-adjustments.index') }}"

               class="menu-link">

                <svg class="menu-icon" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>

                <div>Ajustements inventaire</div>

            </a>

        </li>
        @endif

        @endif

        {{-- MOUVEMENTS STOCK --}}

        <li class="menu-item

            {{ request()->routeIs('stock-movements.*') ? 'active open' : '' }}">

            <a href="javascript:void(0);"

            class="menu-link menu-toggle">

                <svg class="menu-icon" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>

                <div>

                    Mouvements de stock

                </div>

            </a>

            <ul class="menu-sub">

                {{-- TOUS --}}

                @if($peut('stock-movements.index'))
                <li class="menu-item

                    {{ request()->routeIs('stock-movements.index') ? 'active' : '' }}">

                    <a href="{{ route('stock-movements.index') }}"

                    class="menu-link">

                        <div>

                            Tous les mouvements

                        </div>

                    </a>

                </li>
                @endif

                {{-- ENTREES --}}

                @if($peut('stock-movements.entries'))
                <li class="menu-item

                    {{ request()->routeIs('stock-movements.entries') ? 'active' : '' }}">

                    <a href="{{ route('stock-movements.entries') }}"

                    class="menu-link">

                        <div>

                            Entrées

                        </div>

                    </a>

                </li>
                @endif

                {{-- SORTIES --}}

                @if($peut('stock-movements.exits'))
                <li class="menu-item

                    {{ request()->routeIs('stock-movements.exits') ? 'active' : '' }}">

                    <a href="{{ route('stock-movements.exits') }}"

                    class="menu-link">

                        <div>

                            Sorties

                        </div>

                    </a>

                </li>
                @endif

            </ul>

        </li>

                {{-- ===================================================== --}}

                {{-- COMMANDES REÇUES DU GARAGE / APP ATELIER --}}

                {{-- ===================================================== --}}

                @if($peut('fournisseur-commandes.index') || $peut('bons-transfert.index'))

                    @if($peut('fournisseur-commandes.index'))
                    <li class="menu-item

                        {{

                            request()->routeIs('fournisseur-commandes.*')

                                ? 'active'

                                : ''

                        }}

                    ">

                        <a href="{{ route('fournisseur-commandes.index') }}"

                        class="menu-link">

                            <svg class="menu-icon" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>

                            <div class="flex-grow-1 text-truncate">

                                Commandes garage

                            </div>

                            {{-- NOMBRE DE NOUVELLES COMMANDES --}}

                            @if($nouvellesCommandesGarage > 0)

                                <div class="badge bg-danger rounded-pill flex-shrink-0 ms-2">

                                    {{ $nouvellesCommandesGarage }}

                                </div>

                            @endif

                        </a>

                    </li>
                    @endif


                    {{-- ===================================================== --}}
                    {{-- BONS DE TRANSFERT --}}
                    {{-- ===================================================== --}}
                    @if($peut('bons-transfert.index'))
                    <li class="menu-item {{ request()->routeIs('bons-transfert.*') ? 'active' : '' }}">
                        <a href="{{ route('bons-transfert.index') }}" class="menu-link">
                            <svg class="menu-icon" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13 9l3 3m0 0l-3 3m3-3H8m13 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <div class="flex-grow-1 text-truncate">
                                Bons de transfert
                            </div>
                        </a>
                    </li>
                    @endif

                               @endif



        {{-- ===================================================== --}}

        {{-- TRANSACTIONS --}}

        {{-- ===================================================== --}}

        <li class="menu-header small text-uppercase">

            <span class="menu-header-text">

                Transactions

            </span>

        </li>

        {{-- ACHATS --}}

        {{--

        @if($user && in_array($user->role, [

            'admin',

            'chef_magasinier'

        ]))

        <li class="menu-item

            {{

                request()->routeIs('purchases.*')

                ? 'active open'

                : ''

            }}">

            <a href="javascript:void(0);"

               class="menu-link menu-toggle">

                <svg class="menu-icon" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>

                <div>Achats</div>

            </a>

            <ul class="menu-sub">

                <li class="menu-item {{ request()->routeIs('purchases.index') ? 'active' : '' }}">

                    <a href="{{ route('purchases.index') }}"

                       class="menu-link">

                        <div>Liste des achats</div>

                    </a>

                </li>

                <li class="menu-item {{ request()->routeIs('purchases.create') ? 'active' : '' }}">

                    <a href="{{ route('purchases.create') }}"

                       class="menu-link">

                        <div>Générer un achat</div>

                    </a>

                </li>

            </ul>

        </li>

        @endif

        --}}

       {{-- ===================================================== --}}

        {{-- VENTES --}}

        {{-- ===================================================== --}}

        @if($peut('sales.create') || $peut('sales.index') || $peut('proformas.create') || $peut('proformas.index') || $peut('avoirs.index'))

            <li class="menu-item

                {{

                    request()->routeIs('sales.*')

                    || request()->routeIs('proformas.*')

                    || request()->routeIs('avoirs.*')

                        ? 'active open'

                        : ''

                }}"

            >

                {{-- MENU PRINCIPAL --}}

                <a href="javascript:void(0);"

                class="menu-link menu-toggle">

                    <svg class="menu-icon" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>

                    <div>Ventes</div>

                </a>

                <ul class="menu-sub">

                    {{-- ================================================= --}}

                    {{-- 1. GESTION DES VENTES --}}

                    {{-- ================================================= --}}

                    <li class="menu-header small text-uppercase"

                        style="

                            padding-left: 1rem;

                            margin-top: 8px;

                            margin-bottom: 4px;

                        ">

                        <span class="menu-header-text"

                            style="

                                font-size: 10px;

                                font-weight: 700;

                                letter-spacing: .5px;

                                opacity: .65;

                            ">

                            Gestion des ventes

                        </span>

                    </li>

                    {{-- GÉNÉRER UNE VENTE --}}

                    @if($peut('sales.create'))
                    <li class="menu-item

                        {{

                            request()->routeIs('sales.create')

                                ? 'active'

                                : ''

                        }}"

                    >

                        <a href="{{ route('sales.create') }}"

                        class="menu-link">

                            <div>

                                Générer une vente

                            </div>

                        </a>

                    </li>
                    @endif

                    {{-- LISTE DES VENTES --}}

                    @if($peut('sales.index'))
                    <li class="menu-item

                        {{

                            request()->routeIs(

                                'sales.index',

                                'sales.show'

                            )

                                ? 'active'

                                : ''

                        }}"

                    >

                        <a href="{{ route('sales.index') }}"

                        class="menu-link">

                            <div>

                                Liste des ventes

                            </div>

                        </a>

                    </li>
                    @endif

                    {{-- AVOIRS : factures annulées (une facture ne se supprime pas) --}}
                    @if($peut('avoirs.index'))
                    <li class="menu-item {{ request()->routeIs('avoirs.*') ? 'active' : '' }}">

                        <a href="{{ route('avoirs.index') }}" class="menu-link">

                            <div>Avoirs</div>

                        </a>

                    </li>
                    @endif

                    {{-- ================================================= --}}

                    {{-- 2. GESTION DES PROFORMAS --}}

                    {{-- ================================================= --}}

                    <li class="menu-header small text-uppercase"

                        style="

                            padding-left: 1rem;

                            margin-top: 18px;

                            margin-bottom: 4px;

                        ">

                        <span class="menu-header-text"

                            style="

                                font-size: 10px;

                                font-weight: 700;

                                letter-spacing: .5px;

                                opacity: .65;

                            ">

                            Gestion des proformas

                        </span>

                    </li>

                    {{-- GÉNÉRER UN PROFORMA --}}

                    @if($peut('proformas.create'))
                    <li class="menu-item

                        {{

                            request()->routeIs('proformas.create')

                                ? 'active'

                                : ''

                        }}"

                    >

                        <a href="{{ route('proformas.create') }}"

                        class="menu-link">

                            <div>

                                Générer un proforma

                            </div>

                        </a>

                    </li>
                    @endif

                    {{-- LISTE DES PROFORMAS --}}

                    @if($peut('proformas.index'))
                    <li class="menu-item

                        {{

                            request()->routeIs(

                                'proformas.index',

                                'proformas.show'

                            )

                                ? 'active'

                                : ''

                        }}"

                    >

                        <a href="{{ route('proformas.index') }}"

                        class="menu-link">

                            <div>

                                Liste des proformas

                            </div>

                        </a>

                    </li>
                    @endif

                </ul>

            </li>

        @endif

        {{-- ===================================================== --}}

        {{-- ADMINISTRATION --}}

        {{-- ===================================================== --}}

        @if($peut('users.index') || $peut('users.create'))

        <li class="menu-header small text-uppercase">

            <span class="menu-header-text">

                Administration

            </span>

        </li>

        <li class="menu-item

            {{

                request()->routeIs('users.*')

                ? 'active open'

                : ''

            }}">

            <a href="javascript:void(0);"

               class="menu-link menu-toggle">

                <svg class="menu-icon" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>

                <div>Utilisateurs</div>

            </a>

            <ul class="menu-sub">

                @if($peut('users.index'))
                <li class="menu-item {{ request()->routeIs('users.index') ? 'active' : '' }}">

                    <a href="{{ route('users.index') }}"

                       class="menu-link">

                        <div>Liste utilisateurs</div>

                    </a>

                </li>
                @endif

                @if($peut('users.create'))
                <li class="menu-item {{ request()->routeIs('users.create') ? 'active' : '' }}">

                    <a href="{{ route('users.create') }}"

                       class="menu-link">

                        <div>Nouvel utilisateur</div>

                    </a>

                </li>
                @endif

            </ul>

        </li>

        @endif

    </ul>

    {{-- ============================================================ --}}
    {{-- PIED DU MENU : UTILISATEUR, MODE NUIT, DÉCONNEXION (comme l'Atelier) --}}
    {{-- ============================================================ --}}

    @if($user)
        @php
            $roleLabels = [
                'admin'           => 'Administrateur',
                'chef_magasinier' => 'Chef magasinier',
                'magasinier'      => 'Magasinier',
                'vendeur'         => 'Vendeur',
                'caissier'        => 'Caissier',
            ];

            $initiales = collect(preg_split('/\s+/', trim((string) $user->name)))
                ->filter()
                ->take(2)
                ->map(fn ($mot) => mb_strtoupper(mb_substr($mot, 0, 1)))
                ->implode('') ?: 'U';
        @endphp

        <div class="stcd-sidebar-footer">

            <div class="stcd-sidebar-user" title="{{ $user->email }}">
                <div class="stcd-sidebar-avatar">{{ $initiales }}</div>
                <div class="min-w-0">
                    <div class="stcd-sidebar-user-name">{{ $user->name }}</div>
                    <div class="stcd-sidebar-user-role">{{ $roleLabels[$user->role] ?? 'Utilisateur' }}</div>
                </div>
            </div>

            <button type="button" class="stcd-sidebar-icon-btn" onclick="stcdBasculerTheme()" title="Basculer mode nuit / mode clair">
                <svg class="stcd-icon-moon" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/></svg>
                <svg class="stcd-icon-sun" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707M17.657 17.657l-.707-.707M6.343 6.343l-.707-.707M12 7a5 5 0 100 10A5 5 0 0012 7z"/></svg>
            </button>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="stcd-sidebar-icon-btn" title="Déconnexion">
                    <svg class="" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                </button>
            </form>

            <p class="stcd-sidebar-version">Magasin — Version 4.0</p>

        </div>


    @endif

  <script>

document.addEventListener('DOMContentLoaded', function () {

    const body = document.body;

    const html = document.documentElement;

    const sidebar = document.getElementById('layout-menu');

    if (!sidebar) {

        return;

    }

    /* ============================================================

       STCD MOTORS

       GESTION DES SOUS-MENUS

       ============================================================ */

    const toggles = sidebar.querySelectorAll(

        '.menu-link.menu-toggle'

    );

    /*

     * Les éléments contenant une page active doivent rester ouverts.

     *

     * Cela permet par exemple :

     *

     * Produits

     *   └── Tous les produits   <- actif

     *

     * de rester développé après le chargement de la page.

     */

    sidebar

        .querySelectorAll('.menu-item.active')

        .forEach(function (activeItem) {

            let parent = activeItem.parentElement;

            while (parent && parent !== sidebar) {

                if (

                    parent.classList &&

                    parent.classList.contains('menu-item')

                ) {

                    parent.classList.add('open');

                }

                parent = parent.parentElement;

            }

        });

    /* ============================================================

       CLIC SUR UN MENU PARENT

       ============================================================ */

    toggles.forEach(function (toggle) {

        /*

         * capture = true

         *

         * Notre événement passe avant le gestionnaire JavaScript

         * du template.

         */

        toggle.addEventListener(

            'click',

            function (event) {

                event.preventDefault();

                event.stopPropagation();

                event.stopImmediatePropagation();

                const parent =

                    this.closest('.menu-item');

                if (!parent) {

                    return;

                }

                const willOpen =

                    !parent.classList.contains('open');

                /* =================================================

                   ACCORDÉON

                   On ferme les autres menus du même niveau.

                   ================================================= */

                const parentList =

                    parent.parentElement;

                if (parentList) {

                    Array.from(parentList.children)

                        .forEach(function (sibling) {

                            if (

                                sibling !== parent &&

                                sibling.classList &&

                                sibling.classList.contains(

                                    'menu-item'

                                )

                            ) {

                                sibling.classList.remove(

                                    'open'

                                );

                            }

                        });

                }

                /* =================================================

                   OUVERTURE / FERMETURE

                   ================================================= */

                if (willOpen) {

                    parent.classList.add('open');

                } else {

                    parent.classList.remove('open');

                }

            },

            true

        );

    });

    /* ============================================================

       FERMETURE MOBILE APRÈS CLIC SUR UN VRAI LIEN

       ============================================================ */

    sidebar

        .querySelectorAll(

            'a.menu-link:not(.menu-toggle)'

        )

        .forEach(function (link) {

            link.addEventListener(

                'click',

                function () {

                    if (window.innerWidth < 1200) {

                        body.classList.remove(

                            'layout-menu-expanded'

                        );

                        html.classList.remove(

                            'layout-menu-expanded'

                        );

                    }

                }

            );

        });

    /* ============================================================

       RETOUR SUR ORDINATEUR

       ============================================================ */

    window.addEventListener(

        'resize',

        function () {

            if (window.innerWidth >= 1200) {

                body.classList.remove(

                    'layout-menu-expanded'

                );

                html.classList.remove(

                    'layout-menu-expanded'

                );

            }

        }

    );

});

</script>

</aside>
