<nav class="layout-navbar navbar navbar-expand-xl navbar-detached align-items-center bg-white shadow-sm px-4 py-2">

    @php

        $user = auth()->user();

        $initials = 'U';

        $roleLabels = [
            'admin' => 'Administrateur',
            'chef_magasinier' => 'Chef magasinier',
            'magasinier' => 'Magasinier',
            'vendeur' => 'Vendeur',
            'caissier' => 'Caissier',
        ];

        if ($user) {

            $names = preg_split('/\s+/', trim($user->name));

            $initials = '';

            foreach (array_slice($names, 0, 2) as $name) {

                if (!empty($name)) {
                    $initials .= strtoupper(
                        mb_substr($name, 0, 1)
                    );
                }
            }

            if (empty($initials)) {
                $initials = 'U';
            }
        }

    @endphp


    {{-- ============================================================
        TOGGLE SIDEBAR MOBILE
    ============================================================ --}}
    <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 d-xl-none">

        <a class="nav-item nav-link px-0 me-xl-4"
           href="javascript:void(0);">

            <i class="bx bx-menu bx-sm text-primary"></i>

        </a>

    </div>


    {{-- ============================================================
        LEFT SECTION
    ============================================================ --}}
    <div class="d-flex align-items-center">

        <div class="me-4">

            <h4 class="mb-0 fw-bold text-dark">
                STCD Motors
            </h4>

            <small class="text-muted">
                Gestion de stock & ventes
            </small>

        </div>

    </div>


    {{-- ============================================================
        RIGHT SECTION
    ============================================================ --}}
    <ul class="navbar-nav flex-row align-items-center ms-auto">

        @auth

            {{-- ====================================================
                UTILISATEUR
            ==================================================== --}}
            <li class="nav-item navbar-dropdown dropdown-user dropdown">

                <a class="nav-link dropdown-toggle hide-arrow d-flex align-items-center"
                   href="javascript:void(0);"
                   data-bs-toggle="dropdown"
                   aria-expanded="false">

                    {{-- AVATAR --}}
                    <div class="avatar avatar-online me-2">

                        <span
                            class="avatar-initial rounded-circle bg-primary text-white fw-bold shadow-sm d-flex align-items-center justify-content-center"
                            style="
                                width: 42px;
                                height: 42px;
                                font-size: 16px;
                                border: 2px solid #696cff;
                            ">

                            {{ $initials }}

                        </span>

                    </div>


                    {{-- NOM + RÔLE --}}
                    <div class="d-none d-md-block">

                        <span class="fw-semibold d-block text-dark">

                            {{ $user->name }}

                        </span>

                        <small class="text-muted">

                            {{ $roleLabels[$user->role] ?? 'Utilisateur' }}

                        </small>

                    </div>

                </a>


                {{-- =================================================
                    MENU UTILISATEUR
                ================================================= --}}
                <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3">

                    {{-- INFORMATIONS UTILISATEUR --}}
                    <li>

                        <div class="dropdown-item-text py-3">

                            <div class="d-flex align-items-center">

                                <div class="avatar avatar-online me-3">

                                    <span
                                        class="avatar-initial rounded-circle bg-primary text-white fw-bold d-flex align-items-center justify-content-center"
                                        style="
                                            width: 45px;
                                            height: 45px;
                                            font-size: 18px;
                                        ">

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

                                    <br>

                                    <small class="text-muted">

                                        {{ $roleLabels[$user->role] ?? 'Utilisateur' }}

                                    </small>

                                </div>

                            </div>

                        </div>

                    </li>


                    <li>

                        <div class="dropdown-divider"></div>

                    </li>


                    {{-- =================================================
                        PROFIL
                    ================================================= --}}
                    {{--
                    <li>

                        <a class="dropdown-item"
                           href="{{ route('profile.edit') }}">

                            <i class="bx bx-user me-2"></i>

                            Mon profil

                        </a>

                    </li>

                    <li>

                        <div class="dropdown-divider"></div>

                    </li>
                    --}}


                   {{-- ============================================================
                        DÉCONNEXION
                    ============================================================ --}}
                    <li>

                       <form
                            method="POST"
                            action="{{ route('logout') }}"
                            class="m-0 p-0"
                        >
                            @csrf

                            <button
                                type="submit"
                                class="dropdown-item text-danger w-100 d-flex align-items-center"
                            >
                                <i class="bx bx-power-off me-2"></i>
                                <span>Déconnexion</span>
                            </button>
                        </form>

                    </li>

                </ul>

            </li>

        @endauth

    </ul>

</nav>
