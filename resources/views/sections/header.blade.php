<nav class="layout-navbar navbar navbar-expand-xl navbar-detached align-items-center bg-white shadow-sm px-4 py-2">

    @php

        $user = auth()->user();

        $names = explode(' ', $user->name);

        $initials = '';

        foreach ($names as $name) {

            $initials .= strtoupper(substr($name, 0, 1));
        }

        $roleLabels = [

            'admin' => 'Administrateur',
            'chef_magasinier' => 'Chef magasinier',
            'magasinier' => 'Magasinier',
            'vendeur' => 'Vendeur',
            'caissier' => 'Caissier',

        ];

    @endphp

    {{-- TOGGLE SIDEBAR --}}
    <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 d-xl-none">

        <a class="nav-item nav-link px-0 me-xl-4"
           href="javascript:void(0)">

            <i class="bx bx-menu bx-sm text-primary"></i>

        </a>

    </div>

    {{-- LEFT SECTION --}}
    <div class="d-flex align-items-center">

        {{-- PAGE TITLE --}}
        <div class="me-4">

            <h4 class="mb-0 fw-bold text-dark">
                STCD Motors
            </h4>

            <small class="text-muted">
                Gestion de stock & ventes
            </small>

        </div>

    </div>

    {{-- RIGHT SECTION --}}
    <ul class="navbar-nav flex-row align-items-center ms-auto">

        {{-- NOTIFICATIONS --}}
        <!--li class="nav-item dropdown-notifications navbar-dropdown dropdown me-3">

            <a class="nav-link dropdown-toggle hide-arrow position-relative"
               href="#"
               data-bs-toggle="dropdown">

                <i class="bx bx-bell fs-3 text-dark"></i>

                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">

                    5

                </span>

            </a>

            <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3">

                <li class="dropdown-header fw-bold">
                    Notifications
                </li>

                <li>
                    <a class="dropdown-item" href="#">
                        Nouveau produit ajouté
                    </a>
                </li>

                <li>
                    <a class="dropdown-item" href="#">
                        Stock faible détecté
                    </a>
                </li>

                <li>
                    <a class="dropdown-item" href="#">
                        Nouvelle vente enregistrée
                    </a>
                </li>

            </ul>

        </li-->

        {{-- USER --}}
        <li class="nav-item navbar-dropdown dropdown-user dropdown">

            <a class="nav-link dropdown-toggle hide-arrow d-flex align-items-center"
               href="#"
               data-bs-toggle="dropdown">

                {{-- AVATAR INITIALS --}}
                <div class="avatar avatar-online me-2">

                    <span
                        class="avatar-initial rounded-circle bg-primary text-white fw-bold shadow-sm d-flex align-items-center justify-content-center"
                        style="
                            width:42px;
                            height:42px;
                            font-size:16px;
                            border:2px solid #696cff;
                        "
                    >
                        {{ $initials }}
                    </span>

                </div>

                <div class="d-none d-md-block">

                    <span class="fw-semibold d-block text-dark">

                        {{ $user->name }}

                    </span>

                    <small class="text-muted">

                        {{ $roleLabels[$user->role] ?? 'Utilisateur' }}

                    </small>

                </div>

            </a>

            <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3">

                <li>

                    <a class="dropdown-item py-3"
                       href="#">

                        <div class="d-flex align-items-center">

                            {{-- GRAND AVATAR --}}
                            <div class="avatar avatar-online me-3">

                                <span
                                    class="avatar-initial rounded-circle bg-primary text-white fw-bold d-flex align-items-center justify-content-center"
                                    style="
                                        width:45px;
                                        height:45px;
                                        font-size:18px;
                                    "
                                >
                                    {{ $initials }}
                                </span>

                            </div>

                            <div>

                                <span class="fw-bold d-block">

                                    {{ $user->name }}

                                </span>

                                <small class="text-muted">

                                    {{ $user->email }}

                                </small>

                            </div>

                        </div>

                    </a>

                </li>

                <li>
                    <div class="dropdown-divider"></div>
                </li>

                {{-- PROFIL --}}
                <!--li>

                    <a class="dropdown-item"
                       href="{ { route('profile.edit') }}">

                        <i class="bx bx-user me-2"></i>
                        Mon profil

                    </a>

                </li-->

                {{-- LOGOUT --}}
                <li>

                    <form method="POST"
                          action="{{ route('logout') }}">

                        @csrf

                        <button type="submit"
                                class="dropdown-item text-danger">

                            <i class="bx bx-power-off me-2"></i>
                            Déconnexion

                        </button>

                    </form>

                </li>

            </ul>

        </li>

    </ul>

</nav>
