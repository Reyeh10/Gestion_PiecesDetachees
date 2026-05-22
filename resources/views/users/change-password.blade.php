@extends('layouts.guest')

@section('content')

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap"
      rel="stylesheet">

<link rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/boxicons/2.1.4/css/boxicons.min.css"/>

<style>

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
}

body{

    font-family:'Poppins', sans-serif;

    width:100%;
    height:100vh;

    overflow:hidden;
}

/*
|--------------------------------------------------------------------------
| PAGE
|--------------------------------------------------------------------------
*/

.password-page{

    width:100%;
    height:100vh;

    display:flex;
    justify-content:center;
    align-items:center;

    position:relative;

    overflow:hidden;

    padding:20px;
}

/*
|--------------------------------------------------------------------------
| BACKGROUND
|--------------------------------------------------------------------------
*/

.password-page::before{

    content:'';

    position:absolute;

    top:0;
    left:0;

    width:100%;
    height:100%;

    background:
        linear-gradient(
            rgba(15,23,42,.55),
            rgba(15,23,42,.55)
        ),
       url("{{ asset('assets/img/logo/djibouti-flag.jpg') }}");

    background-size:cover;
    background-position:center;
}

/*
|--------------------------------------------------------------------------
| CARD
|--------------------------------------------------------------------------
*/

.password-card{

    position:relative;

    z-index:2;

    width:100%;
    max-width:460px;

    background:rgba(255,255,255,.95);

    backdrop-filter:blur(12px);

    border-radius:30px;

    padding:40px;

    box-shadow:
        0 20px 60px rgba(0,0,0,.35);

    animation:fadeIn .7s ease;
}

/*
|--------------------------------------------------------------------------
| ANIMATION
|--------------------------------------------------------------------------
*/

@keyframes fadeIn{

    from{

        opacity:0;
        transform:translateY(25px);
    }

    to{

        opacity:1;
        transform:translateY(0);
    }
}

/*
|--------------------------------------------------------------------------
| LOGO
|--------------------------------------------------------------------------
*/

.logo{

    width:95px;

    display:block;

    margin:0 auto 20px;
}

/*
|--------------------------------------------------------------------------
| TITLE
|--------------------------------------------------------------------------
*/

.page-title{

    text-align:center;

    font-size:38px;

    font-weight:800;

    color:#1e1b4b;

    margin-bottom:12px;
}

/*
|--------------------------------------------------------------------------
| SUBTITLE
|--------------------------------------------------------------------------
*/

.page-subtitle{

    text-align:center;

    color:#64748b;

    font-size:15px;

    line-height:1.7;

    margin-bottom:35px;
}

/*
|--------------------------------------------------------------------------
| FORM GROUP
|--------------------------------------------------------------------------
*/

.form-group{

    margin-bottom:24px;
}

/*
|--------------------------------------------------------------------------
| LABEL
|--------------------------------------------------------------------------
*/

.form-label{

    display:block;

    margin-bottom:10px;

    font-weight:600;

    color:#0f172a;
}

/*
|--------------------------------------------------------------------------
| INPUT WRAPPER
|--------------------------------------------------------------------------
*/

.input-wrapper{

    position:relative;
}

/*
|--------------------------------------------------------------------------
| ICON
|--------------------------------------------------------------------------
*/

.input-wrapper i{

    position:absolute;

    left:18px;
    top:50%;

    transform:translateY(-50%);

    color:#64748b;

    font-size:22px;
}

/*
|--------------------------------------------------------------------------
| INPUT
|--------------------------------------------------------------------------
*/

.form-control{

    width:100%;

    height:58px;

    border:1px solid #dbeafe;

    border-radius:16px;

    background:#f8fafc;

    padding-left:58px;
    padding-right:60px;

    font-size:16px;

    transition:.3s;
}

.form-control:focus{

    outline:none;

    border:1px solid #2563eb;

    background:white;

    box-shadow:
        0 0 0 4px rgba(37,99,235,.12);
}

/*
|--------------------------------------------------------------------------
| SHOW PASSWORD
|--------------------------------------------------------------------------
*/

.toggle-password{

    position:absolute;

    right:18px;
    top:50%;

    transform:translateY(-50%);

    cursor:pointer;

    color:#64748b;

    font-size:22px;
}

.toggle-password:hover{

    color:#2563eb;
}

/*
|--------------------------------------------------------------------------
| BUTTON
|--------------------------------------------------------------------------
*/

.password-btn{

    width:100%;

    height:58px;

    border:none;

    border-radius:16px;

    background:
        linear-gradient(
            135deg,
            #2563eb,
            #3b82f6
        );

    color:white;

    font-size:18px;

    font-weight:700;

    cursor:pointer;

    transition:.3s;

    box-shadow:
        0 12px 28px rgba(37,99,235,.30);
}

.password-btn:hover{

    transform:translateY(-2px);

    box-shadow:
        0 16px 35px rgba(37,99,235,.40);
}

/*
|--------------------------------------------------------------------------
| FOOTER
|--------------------------------------------------------------------------
*/

.footer{

    text-align:center;

    margin-top:28px;

    color:#64748b;

    font-size:14px;
}

/*
|--------------------------------------------------------------------------
| MOBILE
|--------------------------------------------------------------------------
*/

@media(max-width:768px){

    .password-card{

        padding:28px 22px;

        border-radius:22px;
    }

    .page-title{

        font-size:32px;
    }

    .form-control{

        height:54px;
    }

    .password-btn{

        height:54px;
    }
}

</style>

<div class="password-page">

    <div class="password-card">

       {{-- LOGO --}}
        <img src="{{ asset('assets/img/logo/stcd.jpg') }}"
            class="logo">

        {{-- TITLE --}}
        <div class="page-title">

            Nouveau mot de passe

        </div>

        {{-- SUBTITLE --}}
        <div class="page-subtitle">

            Pour sécuriser votre compte,
            veuillez créer votre propre mot de passe.

        </div>

        {{-- FORM --}}
        <form method="POST"
              action="{{ route('password.change') }}">

            @csrf

            {{-- PASSWORD --}}
            <div class="form-group">

                <label class="form-label">

                    Nouveau mot de passe

                </label>

                <div class="input-wrapper">

                    <i class='bx bx-lock-alt'></i>

                    <input type="password"
                           id="password"
                           name="password"
                           class="form-control"
                           placeholder="Entrer votre nouveau mot de passe"
                           required>

                    <span class="toggle-password"
                          onclick="togglePassword('password','icon1')">

                        <i class='bx bx-show'
                           id="icon1"></i>

                    </span>

                </div>

            </div>

            {{-- CONFIRM PASSWORD --}}
            <div class="form-group">

                <label class="form-label">

                    Confirmation mot de passe

                </label>

                <div class="input-wrapper">

                    <i class='bx bx-lock-alt'></i>

                    <input type="password"
                           id="password_confirmation"
                           name="password_confirmation"
                           class="form-control"
                           placeholder="Confirmer votre mot de passe"
                           required>

                    <span class="toggle-password"
                          onclick="togglePassword('password_confirmation','icon2')">

                        <i class='bx bx-show'
                           id="icon2"></i>

                    </span>

                </div>

            </div>

            {{-- BUTTON --}}
            <button type="submit"
                    class="password-btn">

                <i class='bx bx-check-circle'></i>

                Modifier mot de passe

            </button>

        </form>

        {{-- FOOTER --}}
        <div class="footer">

            © {{ date('Y') }} STCD Motors.
            Tous droits réservés.

        </div>

    </div>

</div>

<script>

function togglePassword(fieldId, iconId){

    const field =
        document.getElementById(fieldId);

    const icon =
        document.getElementById(iconId);

    if(field.type === 'password'){

        field.type = 'text';

        icon.classList.remove('bx-show');

        icon.classList.add('bx-hide');

    }else{

        field.type = 'password';

        icon.classList.remove('bx-hide');

        icon.classList.add('bx-show');
    }
}

</script>

@endsection
