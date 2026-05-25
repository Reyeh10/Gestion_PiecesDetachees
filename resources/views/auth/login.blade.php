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

/* PAGE */
.login-page{

    width:100%;
    height:100vh;

    position:relative;

    display:flex;
    justify-content:center;
    align-items:center;

    overflow:hidden;

    padding:20px;
}

/* BACKGROUND IMAGE */
.login-page::before{

    content:'';

    position:absolute;

    top:0;
    left:0;

    width:100%;
    height:100%;

    background:
        linear-gradient(
            rgba(15,23,42,.45),
            rgba(15,23,42,.45)
        ),
        url("{{ asset('assets/img/login-bg.jpg') }}");

    background-size:cover;
    background-position:center;

    transform:scale(1.02);
}

/* CONTAINER */
.login-container{

    position:relative;

    z-index:2;

    width:100%;
    max-width:420px;
}

/* LOGIN CARD */
.login-card{

    width:100%;

    background:
        rgba(255,255,255,.95);

    backdrop-filter:blur(10px);

    border-radius:28px;

    padding:35px;

    box-shadow:
        0 20px 60px rgba(0,0,0,.35);

    text-align:center;

    animation:fadeIn .7s ease;

    max-height:90vh;

    overflow-y:auto;
}

/* ANIMATION */
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

/* LOGO */
.logo{

    width:90px;

    margin-bottom:18px;
}

/* TITLE */
.login-title{

    font-size:42px;

    font-weight:800;

    color:#1e1b4b;

    margin-bottom:10px;

    line-height:1.1;
}

/* SUBTITLE */
.login-subtitle{

    font-size:16px;

    color:#64748b;

    margin-bottom:35px;

    line-height:1.7;
}

/* FORM GROUP */
.form-group{

    margin-bottom:24px;

    text-align:left;
}

/* LABEL */
.form-label{

    display:block;

    margin-bottom:10px;

    font-size:16px;

    font-weight:600;

    color:#0f172a;
}

/* INPUT WRAPPER */
.input-wrapper{

    position:relative;
}

/* ICON */
.input-wrapper i{

    position:absolute;

    left:18px;
    top:50%;

    transform:translateY(-50%);

    color:#64748b;

    font-size:22px;
}

/* INPUT */
.form-control{

    width:100%;

    height:58px;

    border:1px solid #dbeafe;

    border-radius:16px;

    padding-left:58px;

    padding-right:55px;

    font-size:16px;

    background:#f8fafc;

    transition:.3s;

    color:#0f172a;
}

.form-control::placeholder{

    color:#94a3b8;
}

.form-control:focus{

    outline:none;

    border:1px solid #2563eb;

    background:white;

    box-shadow:
        0 0 0 4px rgba(37,99,235,.12);
}

/* OPTIONS */
.login-options{

    display:flex;
    justify-content:space-between;
    align-items:center;

    margin-top:5px;
    margin-bottom:28px;
}

/* REMEMBER */
.remember{

    display:flex;
    align-items:center;

    gap:8px;

    font-size:15px;

    color:#334155;
}

.remember input{

    width:16px;
    height:16px;
}

/* FORGOT */
.forgot-password{

    text-decoration:none;

    color:#2563eb;

    font-size:15px;

    font-weight:600;

    transition:.3s;
}

.forgot-password:hover{

    color:#1d4ed8;
}

/* BUTTON */
.login-btn{

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

.login-btn:hover{

    transform:translateY(-2px);

    box-shadow:
        0 16px 35px rgba(37,99,235,.40);
}

/* BUTTON ICON */
.login-btn i{

    margin-right:8px;
}

/* ERROR */
.alert-danger{

    background:#fee2e2;

    color:#b91c1c;

    padding:15px;

    border-radius:14px;

    margin-bottom:22px;

    font-size:14px;

    text-align:left;
}

/* FOOTER */
.login-footer{

    margin-top:28px;

    font-size:14px;

    color:#64748b;
}

 /* SHOW PASSWORD */
   .toggle-password{

    position:absolute;

    right:20px;

    width:24px;

    height:24px;

    display:flex;

    align-items:center;

    justify-content:center;
        top:50%;

        transform:translateY(-50%);

        cursor:pointer;

        color:#64748b;

        font-size:22px;

        transition:.3s;
    }

    .toggle-password:hover{

        color:#2563eb;
    }

/* MOBILE */
@media(max-width:768px){

    .login-page{

        padding:15px;
    }

    .login-card{

        padding:28px 22px;

        border-radius:22px;
    }

    .logo{

        width:75px;
    }

    .login-title{

        font-size:34px;
    }

    .login-subtitle{

        font-size:15px;
    }

    .form-control{

        height:54px;

        font-size:15px;
    }

    .login-btn{

        height:54px;

        font-size:17px;
    }

    .login-options{

        flex-direction:column;

        align-items:flex-start;

        gap:15px;
    }

}

</style>

<div class="login-page">

    <div class="login-container">

        <div class="login-card">

            {{-- LOGO --}}
            <img src="{{ asset('assets/img/logo/stcd.jpg') }}"
                 alt="STCD Motors"
                 class="logo">

            {{-- TITLE --}}
            <div class="login-title">

                Connexion

            </div>

            {{-- SUBTITLE --}}
            <div class="login-subtitle">

                Connectez-vous à votre espace sécurisé STCD Motors

            </div>

            {{-- ERRORS --}}
            @if ($errors->any())

                <div class="alert-danger">

                    {{ $errors->first() }}

                </div>

            @endif

            {{-- FORM --}}
            <form method="POST"
                  action="{{ route('login') }}">

                @csrf

                {{-- EMAIL --}}

                <div class="form-group">

                    <label class="form-label">

                        Adresse Email

                    </label>

                    <div class="input-wrapper">

                        <i class='bx bx-envelope'></i>

                        <input type="email"
                            name="email"
                            class="form-control"
                            placeholder="Entrer votre email"
                            required>

                    </div>

                </div>

                {{-- PASSWORD --}}

                <div class="form-group">

                    <label class="form-label">

                        Mot de passe

                    </label>

                    <div class="input-wrapper">

                        <i class='bx bx-lock-alt'></i>

                        <input type="password"
                            name="password"
                            id="password"
                            class="form-control"
                            placeholder="Entrer votre mot de passe"
                            required>

                        {{-- SHOW / HIDE PASSWORD --}}
                        <span class="toggle-password"
                            onclick="togglePassword()">

                            <i class='bx bx-show'
                            id="toggleIcon"></i>

                        </span>

                    </div>

                </div>
                {{-- OPTIONS --}}
                <div class="login-options">

                    <label class="remember">

                        <input type="checkbox"
                               name="remember">

                        Se souvenir de moi

                    </label>

                    <a href="#"
                       class="forgot-password">

                        Mot de passe oublié ?

                    </a>

                </div>

                {{-- BUTTON --}}
                <button type="submit"
                        class="login-btn">

                    <i class='bx bx-log-in-circle'></i>

                    LOGIN

                </button>

            </form>

            {{-- FOOTER --}}
            <div class="login-footer">

                © {{ date('Y') }} STCD Motors.
                Tous droits réservés.

            </div>

        </div>

    </div>

</div>
<script>

function togglePassword(){

    const password =
        document.getElementById('password');

    const icon =
        document.getElementById('toggleIcon');

    if(password.type === 'password'){

        password.type = 'text';

        icon.classList.remove('bx-show');

        icon.classList.add('bx-hide');

    }else{

        password.type = 'password';

        icon.classList.remove('bx-hide');

        icon.classList.add('bx-show');
    }
}

</script>
@endsection
