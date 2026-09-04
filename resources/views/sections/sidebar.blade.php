@php

    $user = auth()->user();

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

        in_array($user->role, [

            'admin',

            'chef_magasinier',

            'vendeur'

        ])

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

                STCD<br>Motors

            </span>

            <span class="stcd-brand-city">

                Djibouti

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

                <i class="menu-icon tf-icons bx bx-home-circle"></i>

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

        @if($user && in_array($user->role, [

            'admin',

            'chef_magasinier',

            'magasinier',

            'vendeur',

            'caissier'

        ]))

            <li class="menu-item

                {{

                    request()->routeIs('products.*')

                        ? 'active open'

                        : ''

                }}"

            >

                <a href="javascript:void(0);"

                class="menu-link menu-toggle">

                    <i class="menu-icon tf-icons bx bx-package"></i>

                    <div>Produits</div>

                </a>

                <ul class="menu-sub">

                    {{-- GESTION DES PRODUITS --}}

                    <li class="menu-header small text-uppercase">

                        <span class="menu-header-text">

                            Gestion des produits

                        </span>

                    </li>

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

                    {{-- RÉAPPROVISIONNEMENT --}}

                    @if(in_array($user->role, [

                        'admin',

                        'chef_magasinier',

                        'magasinier',

                        'vendeur'

                    ]))

                        <li class="menu-header small text-uppercase">

                            <span class="menu-header-text">

                                Réapprovisionnement

                            </span>

                        </li>

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

                </ul>

            </li>

        @endif

        {{-- CATEGORIES --}}

        <li class="menu-item {{ request()->routeIs('categories.*') ? 'active' : '' }}">

            <a href="{{ route('categories.index') }}"

               class="menu-link">

                <i class="menu-icon tf-icons bx bx-grid-alt"></i>

                <div>Catégories</div>

            </a>

        </li>

        {{-- FOURNISSEURS --}}

        <li class="menu-item {{ request()->routeIs('suppliers.*') ? 'active' : '' }}">

            <a href="{{ route('suppliers.index') }}"

               class="menu-link">

                <i class="menu-icon tf-icons bx bx-building-house"></i>

                <div>Fournisseurs</div>

            </a>

        </li>

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

            <i class="menu-icon tf-icons bx bx-user"></i>

            <div>

                Clients

            </div>

        </a>

        {{-- SOUS-MENU --}}

        <ul class="menu-sub">

            {{-- =============================================== --}}

            {{-- LISTE DES CLIENTS --}}

            {{-- =============================================== --}}

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

            {{-- =============================================== --}}

            {{-- HISTORIQUE DES CLIENTS --}}

            {{-- =============================================== --}}

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

        </ul>

    </li>

        {{-- ===================================================== --}}

        {{-- VÉHICULES --}}

        {{-- ===================================================== --}}

        @if($user && in_array($user->role, [

            'admin',

            'chef_magasinier',

            'magasinier',

            'vendeur',

            'caissier'

            ]))

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

                    <i class="menu-icon tf-icons bx bx-car"></i>

                    <div>Véhicules</div>

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

                    {{-- NOUVEAU VÉHICULE --}}

                    <li class="menu-item

                        {{

                            request()->routeIs('vehicles.create')

                                ? 'active'

                                : ''

                        }}"

                    >

                        <a href="{{ route('vehicles.create') }}"

                        class="menu-link">

                            <div>

                                Nouveau véhicule

                            </div>

                        </a>

                    </li>

                    {{-- HISTORIQUE / TRAÇABILITÉ --}}

                    <li class="menu-item

                        {{

                            request()->routeIs('vehicles.history')

                                ? 'active'

                                : ''

                        }}"

                    >

                        <a href="{{ route('vehicles.history') }}"

                        class="menu-link">

                            <div>

                                Historique / Traçabilité

                            </div>

                        </a>

                    </li>

                    {{-- ================================================= --}}

                    {{-- 2. SUIVI DES PIÈCES --}}

                    {{-- ================================================= --}}

                    @if($user && in_array($user->role, [

                        'admin',

                        'chef_magasinier',

                        'magasinier'

                    ]))

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

                        {{-- PIÈCES COMMANDÉES --}}

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

                        {{-- PIÈCES REÇUES --}}

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

        {{-- GESTION DU STOCK --}}

        {{-- ===================================================== --}}

        <li class="menu-header small text-uppercase">

            <span class="menu-header-text">

                Gestion du stock

            </span>

        </li>

        {{-- DEPOTS --}}

        @if($user && in_array($user->role, [

            'admin',

            'chef_magasinier',

            'magasinier',

            'vendeur',

            'caissier'

        ]))

        <li class="menu-item

            {{

                request()->routeIs('depots.*')

                ? 'active open'

                : ''

            }}">

            <a href="{{ route('depots.index') }}"

               class="menu-link">

                <i class="menu-icon tf-icons bx bx-building-house"></i>

                <div>Dépôts</div>

            </a>

        </li>

        @endif

        {{-- ===================================================== --}}

        {{-- TRANSFERTS ENTRE DÉPÔTS --}}

        {{-- ===================================================== --}}

        @if($user && in_array($user->role, [

            'admin',

            'chef_magasinier',

            'magasinier',

            'vendeur',

            'caissier'

        ]))

            <li class="menu-item

                {{

                    request()->routeIs('depot-transfers.*')

                        ? 'active'

                        : ''

                }}

            ">

                <a href="{{ route('depot-transfers.index') }}"

                class="menu-link">

                    <i class="menu-icon tf-icons bx bx-transfer-alt"></i>

                    <div>

                        Transferts dépôts

                    </div>

                </a>

            </li>

        @endif

        {{-- AJUSTEMENTS INVENTAIRE --}}

        @if($user && in_array($user->role, [

            'admin',

            'chef_magasinier',

            'magasinier',

            'vendeur',

            'caissier'

        ]))

        <li class="menu-item

            {{

                request()->routeIs('inventory-adjustments.*')

                ? 'active'

                : ''

            }}">

            <a href="{{ route('inventory-adjustments.index') }}"

               class="menu-link">

                <i class="menu-icon tf-icons bx bx-wrench"></i>

                <div>Ajustements inventaire</div>

            </a>

        </li>

        @endif

        {{-- MOUVEMENTS STOCK --}}

        <li class="menu-item

            {{ request()->routeIs('stock-movements.*') ? 'active open' : '' }}">

            <a href="javascript:void(0);"

            class="menu-link menu-toggle">

                <i class="menu-icon tf-icons bx bx-transfer-alt"></i>

                <div>

                    Mouvements de stock

                </div>

            </a>

            <ul class="menu-sub">

                {{-- TOUS --}}

                <li class="menu-item

                    {{ request()->routeIs('stock-movements.index') ? 'active' : '' }}">

                    <a href="{{ route('stock-movements.index') }}"

                    class="menu-link">

                        <div>

                            Tous les mouvements

                        </div>

                    </a>

                </li>

                {{-- ENTREES --}}

                <li class="menu-item

                    {{ request()->routeIs('stock-movements.entries') ? 'active' : '' }}">

                    <a href="{{ route('stock-movements.entries') }}"

                    class="menu-link">

                        <div>

                            Entrées

                        </div>

                    </a>

                </li>

                {{-- SORTIES --}}

                <li class="menu-item

                    {{ request()->routeIs('stock-movements.exits') ? 'active' : '' }}">

                    <a href="{{ route('stock-movements.exits') }}"

                    class="menu-link">

                        <div>

                            Sorties

                        </div>

                    </a>

                </li>

            </ul>

        </li>

                {{-- ===================================================== --}}

                {{-- COMMANDES REÇUES DU GARAGE / APP ATELIER --}}

                {{-- ===================================================== --}}

                @if($user && in_array($user->role, [

                    'admin',

                    'chef_magasinier',

                    'vendeur'

                ]))

                    <li class="menu-item

                        {{

                            request()->routeIs('fournisseur-commandes.*')

                                ? 'active'

                                : ''

                        }}

                    ">

                        <a href="{{ route('fournisseur-commandes.index') }}"

                        class="menu-link">

                            <i class="menu-icon tf-icons bx bx-receipt"></i>

                            <div>

                                Commandes garage

                            </div>

                            {{-- NOMBRE DE NOUVELLES COMMANDES --}}

                            @if($nouvellesCommandesGarage > 0)

                                <div class="badge bg-danger rounded-pill ms-auto">

                                    {{ $nouvellesCommandesGarage }}

                                </div>

                            @endif

                        </a>

                    </li>

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

                <i class="menu-icon tf-icons bx bx-cart"></i>

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

        @if($user && in_array($user->role, [

            'admin',

            'chef_magasinier',

            'magasinier',

            'vendeur',

            'caissier'

        ]))

            <li class="menu-item

                {{

                    request()->routeIs('sales.*')

                    || request()->routeIs('proformas.*')

                        ? 'active open'

                        : ''

                }}"

            >

                {{-- MENU PRINCIPAL --}}

                <a href="javascript:void(0);"

                class="menu-link menu-toggle">

                    <i class="menu-icon tf-icons bx bx-store"></i>

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

                    {{-- LISTE DES VENTES --}}

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

                    {{-- LISTE DES PROFORMAS --}}

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

                </ul>

            </li>

        @endif

        {{-- ===================================================== --}}

        {{-- ADMINISTRATION --}}

        {{-- ===================================================== --}}

        @if($user && $user->role == 'admin')

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

                <i class="menu-icon tf-icons bx bx-user-circle"></i>

                <div>Utilisateurs</div>

            </a>

            <ul class="menu-sub">

                <li class="menu-item {{ request()->routeIs('users.index') ? 'active' : '' }}">

                    <a href="{{ route('users.index') }}"

                       class="menu-link">

                        <div>Liste utilisateurs</div>

                    </a>

                </li>

                <li class="menu-item {{ request()->routeIs('users.create') ? 'active' : '' }}">

                    <a href="{{ route('users.create') }}"

                       class="menu-link">

                        <div>Nouvel utilisateur</div>

                    </a>

                </li>

            </ul>

        </li>

        @endif

    </ul>

    <script>

        document.addEventListener('DOMContentLoaded', function () {

            const body = document.body;

            const html = document.documentElement;

            const sidebar = document.getElementById('layout-menu');

            // Sous-menus du sidebar

            document.querySelectorAll('#layout-menu .menu-link.menu-toggle').forEach(function (toggle) {

                toggle.addEventListener('click', function (event) {

                    event.preventDefault();

                    event.stopPropagation();

                    const parent = this.closest('.menu-item');

                    if (parent) {

                        parent.classList.toggle('open');

                    }

                });

            });

            // Fermeture du menu sur mobile après clic sur un vrai lien

            if (sidebar) {

                sidebar.querySelectorAll('a.menu-link:not(.menu-toggle)').forEach(function (link) {

                    link.addEventListener('click', function () {

                        if (window.innerWidth < 1200) {

                            body.classList.remove('layout-menu-expanded');

                            html.classList.remove('layout-menu-expanded');

                        }

                    });

                });

            }

            // En revenant sur ordinateur, le sidebar doit rester visible

            window.addEventListener('resize', function () {

                if (window.innerWidth >= 1200) {

                    body.classList.remove('layout-menu-expanded');

                    html.classList.remove('layout-menu-expanded');

                }

            });

        });

    </script>

</aside>
