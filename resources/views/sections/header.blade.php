<nav class="layout-navbar navbar navbar-expand-xl navbar-detached align-items-center bg-white shadow-sm px-4 py-2">

    @php

        /*
        |--------------------------------------------------------------------------
        | UTILISATEUR CONNECTÉ
        |--------------------------------------------------------------------------
        */

        $user = auth()->user();

        $initials = 'U';

        $roleLabels = [
            'admin' => 'Administrateur',
            'chef_magasinier' => 'Chef magasinier',
            'magasinier' => 'Magasinier',
            'vendeur' => 'Vendeur',
            'caissier' => 'Caissier',
        ];


        /*
        |--------------------------------------------------------------------------
        | INITIALes
        |--------------------------------------------------------------------------
        */

        if ($user) {

            $names = preg_split(
                '/\s+/',
                trim($user->name)
            );

            $initials = '';

            foreach (
                array_slice(
                    $names,
                    0,
                    2
                ) as $name
            ) {

                if (!empty($name)) {

                    $initials .= strtoupper(
                        mb_substr(
                            $name,
                            0,
                            1
                        )
                    );

                }

            }

            if (empty($initials)) {
                $initials = 'U';
            }

        }


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
            class="nav-item nav-link px-0 me-xl-4"
            href="javascript:void(0);"
        >

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

                    <i class="bx bx-bell"></i>


                    @if($headerUnreadCount > 0)

                        <span class="stcd-notification-badge">

                            {{
                                $headerUnreadCount > 99
                                    ? '99+'
                                    : $headerUnreadCount
                            }}

                        </span>

                    @endif

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

                    <div class="stcd-notification-list">

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

                            <div class="stcd-notification-empty">

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


            {{-- ====================================================
                UTILISATEUR
            ==================================================== --}}

            <li
                class="
                    nav-item
                    navbar-dropdown
                    dropdown-user
                    dropdown
                "
            >

                <a
                    class="
                        nav-link
                        dropdown-toggle
                        hide-arrow
                        d-flex
                        align-items-center
                    "
                    href="javascript:void(0);"
                    data-bs-toggle="dropdown"
                    aria-expanded="false"
                >


                    {{-- AVATAR --}}

                    <div class="avatar avatar-online me-2">

                        <span
                            class="
                                avatar-initial
                                rounded-circle
                                bg-primary
                                text-white
                                fw-bold
                                shadow-sm
                                d-flex
                                align-items-center
                                justify-content-center
                            "
                            style="
                                width: 42px;
                                height: 42px;
                                font-size: 16px;
                                border: 2px solid #696cff;
                            "
                        >

                            {{ $initials }}

                        </span>

                    </div>


                    {{-- NOM + RÔLE --}}

                    <div class="d-none d-md-block">

                        <span
                            class="
                                fw-semibold
                                d-block
                                text-dark
                            "
                        >

                            {{ $user->name }}

                        </span>


                        <small class="text-muted">

                            {{
                                $roleLabels[$user->role]
                                ?? 'Utilisateur'
                            }}

                        </small>

                    </div>

                </a>


                {{-- =================================================
                    MENU UTILISATEUR
                ================================================= --}}

                <ul
                    class="
                        dropdown-menu
                        dropdown-menu-end
                        shadow
                        border-0
                        rounded-3
                    "
                >


                    {{-- INFORMATIONS UTILISATEUR --}}

                    <li>

                        <div class="dropdown-item-text py-3">

                            <div class="d-flex align-items-center">

                                <div class="avatar avatar-online me-3">

                                    <span
                                        class="
                                            avatar-initial
                                            rounded-circle
                                            bg-primary
                                            text-white
                                            fw-bold
                                            d-flex
                                            align-items-center
                                            justify-content-center
                                        "
                                        style="
                                            width: 45px;
                                            height: 45px;
                                            font-size: 18px;
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


                                    <br>


                                    <small class="text-muted">

                                        {{
                                            $roleLabels[$user->role]
                                            ?? 'Utilisateur'
                                        }}

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

                        <a
                            class="dropdown-item"
                            href="{{ route('profile.edit') }}"
                        >

                            <i class="bx bx-user me-2"></i>

                            Mon profil

                        </a>

                    </li>


                    <li>

                        <div class="dropdown-divider"></div>

                    </li>

                    --}}


                    {{-- =================================================
                        DÉCONNEXION
                    ================================================= --}}

                    <li>

                        <form
                            method="POST"
                            action="{{ route('logout') }}"
                            class="m-0 p-0"
                        >

                            @csrf


                            <button
                                type="submit"
                                class="
                                    dropdown-item
                                    text-danger
                                    w-100
                                    d-flex
                                    align-items-center
                                "
                            >

                                <i
                                    class="
                                        bx
                                        bx-power-off
                                        me-2
                                    "
                                ></i>

                                <span>
                                    Déconnexion
                                </span>

                            </button>

                        </form>

                    </li>

                </ul>

            </li>

        @endauth

    </ul>

</nav>
