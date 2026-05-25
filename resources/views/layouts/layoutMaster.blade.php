<!DOCTYPE html>
<html lang="fr">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>
        Gestion Pièces
    </title>

    {{-- STYLES --}}
    @include('sections.styles')

    @stack('styles')

    {{-- SWEETALERT STYLE --}}
    <style>

        .swal2-popup{

            border-radius:20px !important;

            padding:30px !important;
        }

        .swal2-title{

            font-size:28px !important;

            font-weight:700 !important;

            color:#566a7f !important;
        }

        .swal2-html-container{

            font-size:15px !important;

            color:#6c757d !important;

            line-height:1.8 !important;
        }

        .swal2-confirm{

            border-radius:12px !important;

            font-size:16px !important;

            font-weight:600 !important;

            padding:10px 25px !important;
        }

    </style>

</head>

<body>

<div class="layout-wrapper layout-content-navbar">

    <div class="layout-container">

        {{-- SIDEBAR --}}
        @include('sections.sidebar')

        <div class="layout-page">

            {{-- HEADER --}}
            @include('sections.header')

            {{-- CONTENT --}}
            <div class="content-wrapper">

                <div class="container-xxl flex-grow-1 container-p-y">

                    @yield('content')

                </div>

                {{-- FOOTER --}}
                @include('sections.footer')

            </div>

        </div>

    </div>

</div>

{{-- SCRIPTS --}}
@include('sections.scripts')

@stack('scripts')

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

{{-- FORM AUTO LOGOUT --}}
<!--form id="auto-logout-form"
      action="{ { route('logout') }}"
      method="POST"
      style="display:none;">

    @ csrf

</form-->

<script>

    /*
    |--------------------------------------------------------------------------
    | AUTO LOGOUT APRES INACTIVITE
    |--------------------------------------------------------------------------
    */

    let inactivityTime = function () {

        let timer;

        function resetTimer() {

            clearTimeout(timer);

            timer = setTimeout(

                logout,

                10 * 60 * 1000

            );
        }

       function logout() {

            Swal.fire({

                icon: 'info',

                title: 'Session expirée',

                html: `

                    <div>

                        <b>
                            Vous avez été déconnecté automatiquement
                        </b>

                        <br><br>

                        Pour des raisons de sécurité,
                        votre session a expiré après
                        <b>10 minutes d’inactivité</b>.

                    </div>

                `,

                confirmButtonText: 'Se reconnecter',

                confirmButtonColor: '#696cff',

                background: '#ffffff',

                color: '#566a7f',

                allowOutsideClick: false,

                allowEscapeKey: false

            }).then(() => {

                window.location.href =
                    "{{ route('login') }}";

            });
        }

        /*
        |--------------------------------------------------------------------------
        | EVENEMENTS ACTIVITE UTILISATEUR
        |--------------------------------------------------------------------------
        */

        window.onload = resetTimer;

        document.onmousemove = resetTimer;

        document.onkeypress = resetTimer;

        document.onclick = resetTimer;

        document.onscroll = resetTimer;

        document.onmousedown = resetTimer;

        document.ontouchstart = resetTimer;
    };

    inactivityTime();

</script>

</body>
</html>
