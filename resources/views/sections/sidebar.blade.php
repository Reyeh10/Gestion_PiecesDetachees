@php
    $user = auth()->user();

    /*
    |--------------------------------------------------------------------------
    | NOUVELLES COMMANDES REÇUES DU GARAGE
    |--------------------------------------------------------------------------
    |
    | Compteur des commandes App Atelier qui n'ont pas encore été ouvertes.
    | Visible pour :
    | - admin
    | - chef_magasinier
    | - vendeur
    |
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

<aside id="layout-menu"
       class="layout-menu menu-vertical menu bg-menu-theme">

    {{-- LOGO --}}
    <div class="app-brand demo py-3 px-3 border-bottom border-secondary">

        <a href="{{ route('dashboard') }}"
           class="app-brand-link d-flex align-items-center text-decoration-none w-100">

            {{-- LOGO --}}
            <div class="me-2">

                <img src="{{ asset('assets/img/logo/stcd.jpg') }}"
                     alt="STCD Motors"
                     width="42"
                     height="42"
                     class="rounded-circle bg-white p-1 shadow-sm">

            </div>

            {{-- TEXTE --}}
            <div class="d-flex flex-column">

                <span class="fw-bold"
                      style="
                        font-size:16px;
                        line-height:1.1;
                        color:#8b5cf6;
                      ">

                    STCD Motors

                </span>

                <small class="text-light opacity-75"
                       style="font-size:11px;">

                    Djibouti

                </small>

            </div>

        </a>

        {{-- MOBILE TOGGLE --}}
        <a href="javascript:void(0);"
           class="layout-menu-toggle menu-link text-large ms-auto d-block d-xl-none">

            <i class="bx bx-chevron-left bx-sm text-white"></i>

        </a>

    </div>

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

        {{-- CLIENTS --}}
        <li class="menu-item {{ request()->routeIs('customers.*') ? 'active' : '' }}">

            <a href="{{ route('customers.index') }}"
               class="menu-link">

                <i class="menu-icon tf-icons bx bx-user"></i>

                <div>Clients</div>

            </a>

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
        <!--@ if($user && in_array($user->role, [
            'admin',
            'chef_magasinier'
        ]))

        <li class="menu-item
            { {
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

                <li class="menu-item { { request()->routeIs('purchases.index') ? 'active' : '' }}">

                    <a href="{ { route('purchases.index') }}"
                       class="menu-link">

                        <div>Liste des achats</div>

                    </a>

                </li>

                <li class="menu-item { { request()->routeIs('purchases.create') ? 'active' : '' }}">

                    <a href="{ { route('purchases.create') }}"
                       class="menu-link">

                        <div>Générer un achat</div>

                    </a>

                </li>

            </ul>

        </li>

        @ endif-->





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

            document.querySelectorAll('.menu-toggle').forEach(function (toggle) {

                toggle.addEventListener('click', function (e) {

                    e.preventDefault();

                    let parent = this.closest('.menu-item');

                    if (parent) {
                        parent.classList.toggle('open');
                    }

                });

            });

        });
    </script>

</aside>
