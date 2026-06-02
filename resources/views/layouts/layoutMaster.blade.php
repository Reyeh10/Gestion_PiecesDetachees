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

    {{-- SELECT2 CSS --}}
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css"
        rel="stylesheet" />

    @stack('styles')

    <style>

        .select2-container {
            width: 100% !important;
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

{{-- JQUERY --}}
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

{{-- SELECT2 --}}
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

{{-- SWEETALERT --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@stack('scripts')



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
