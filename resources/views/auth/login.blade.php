{{--
    PAGE DE CONNEXION — style « DreamsPOS » : formulaire blanc à gauche,
    panneau de marque bleu marine (#092C4C) à droite avec logo, slogan,
    engrenages animés et atouts ; accents orange #FE9F43 ; police Nunito.

    Page autonome (CSS intégré) : ne dépend pas de Vite / npm run build.
    Champs inchangés : email, password, remember → route('login').
--}}
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Connexion — STCD Motors Magasin</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        *, *::before, *::after { box-sizing: border-box; }

        :root {
            --violet: #FE9F43;          /* focus / case à cocher : orange aussi */
            --orange: #FE9F43;
            --orange-hover: #e88a2f;
            --orange-clair: #FFC48A;
            --marine: #092C4C;
        }

        html, body { margin: 0; min-height: 100%; }

        body {
            font-family: 'Nunito', ui-sans-serif, system-ui, -apple-system, 'Segoe UI', sans-serif;
            background: #f9fafb;
            color: #212B36;
            -webkit-font-smoothing: antialiased;
        }

        .page { min-height: 100vh; display: flex; flex-direction: row-reverse; }

        /* ===== PANNEAU GAUCHE : MARQUE ===== */
        .marque {
            display: none;
            position: relative;
            width: 58.333%;
            overflow-x: hidden;
            overflow-y: auto;
            flex-direction: column;
            align-items: center;
            padding: 48px 0;
            background: linear-gradient(145deg, #092C4C 0%, #0d3a63 55%, #061f36 100%);
        }

        @media (min-width: 1024px) { .marque { display: flex; } }

        .engrenages { position: absolute; inset: 0; overflow: hidden; pointer-events: none; }
        .engrenages svg { position: absolute; }
        .eng-a { bottom: -100px; right: -100px; width: 360px; height: 360px; opacity: .08; animation: tourne-h 24s linear infinite; }
        .eng-b { top: -70px; left: -70px; width: 260px; height: 260px; opacity: .07; animation: tourne-ah 15s linear infinite; }
        .eng-c { top: 50%; left: 65%; width: 130px; height: 130px; opacity: .06; animation: tourne-h 9s linear infinite; }

        @keyframes tourne-h  { to { transform: rotate(360deg); } }
        @keyframes tourne-ah { to { transform: rotate(-360deg); } }

        .marque-contenu {
            position: relative;
            z-index: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            max-width: 640px;
            margin: auto 0;
            padding: 0 64px;
            text-align: center;
        }

        .marque-logo {
            margin-bottom: 32px;
            padding: 12px;
            background: rgba(255, 255, 255, .06);
            border: 1px solid rgba(254, 159, 67, .35);
            border-radius: 24px;
        }

        .marque-logo img {
            display: block;
            width: 112px;
            height: 112px;
            object-fit: contain;
            background: #fff;
            border-radius: 16px;
        }

        .marque-nom { margin: 0 0 6px; color: #fff; font-size: 2.5rem; font-weight: 800; letter-spacing: .02em; }
        .marque-slogan { margin: 0 0 28px; color: var(--orange-clair); font-size: 1.1rem; font-style: italic; }

        .separateur {
            width: 80px;
            height: 3px;
            margin-bottom: 28px;
            border-radius: 9px;
            background: linear-gradient(90deg, transparent, var(--orange), transparent);
        }

        .accroche-1, .accroche-2 { margin: 0; font-size: 3rem; font-weight: 800; line-height: 1.15; }
        .accroche-1 { color: #fff; text-shadow: 0 2px 20px rgba(0, 0, 0, .5); }
        .accroche-2 { margin-bottom: 28px; color: var(--orange); text-shadow: 0 2px 20px rgba(254, 159, 67, .3); }

        .marque-texte { margin: 0 0 36px; color: #b6c4d3; font-size: 1.05rem; line-height: 1.7; }

        .atouts { width: 100%; display: flex; flex-direction: column; gap: 16px; }
        .atout { display: flex; align-items: center; gap: 16px; text-align: left; }

        .atout-icone {
            flex-shrink: 0;
            width: 46px;
            height: 46px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            background: rgba(254, 159, 67, .18);
            border: 1px solid rgba(254, 159, 67, .45);
        }

        .atout-icone svg { width: 22px; height: 22px; stroke: var(--orange); }
        .atout span { color: #e2e8f0; font-size: 1rem; font-weight: 500; }

        .marque-pied { position: relative; z-index: 1; flex-shrink: 0; margin: 32px 0 0; color: #8aa0b6; font-size: .875rem; }

        /* ===== PANNEAU DROIT : FORMULAIRE ===== */
        .formulaire {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 48px 40px;
            background: #fff;
        }

        @media (min-width: 1024px) { .formulaire { width: 41.667%; } }

        .boite { width: 100%; max-width: 384px; }

        .logo-mobile { display: flex; flex-direction: column; align-items: center; gap: 6px; margin-bottom: 40px; }
        .logo-mobile img { width: 64px; height: 64px; object-fit: contain; border-radius: 12px; box-shadow: 0 2px 8px rgba(0, 0, 0, .12); }
        .logo-mobile strong { color: var(--marine); font-size: 1.1rem; }
        .logo-mobile small { color: #94a3b8; font-size: .75rem; font-style: italic; }

        @media (min-width: 1024px) { .logo-mobile { display: none; } }

        .titre { margin: 0 0 4px; color: var(--marine); font-size: 1.75rem; font-weight: 800; }
        .sous-titre { margin: 0 0 32px; color: #646B72; font-size: .875rem; }

        .alerte {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            margin-bottom: 20px;
            padding: 12px 16px;
            border-radius: 12px;
            font-size: .875rem;
        }

        .alerte svg { flex-shrink: 0; width: 20px; height: 20px; margin-top: 1px; }
        .alerte-erreur { color: #b91c1c; background: #fef2f2; border: 1px solid #fecaca; }
        .alerte-erreur svg { stroke: #ef4444; }
        .alerte-info { color: #15803d; background: #f0fdf4; border: 1px solid #bbf7d0; }
        .alerte-info svg { stroke: #22c55e; }
        .alerte-attention { color: #92400e; background: #fffbeb; border: 1px solid #fde68a; }
        .alerte-attention svg { stroke: #f59e0b; }

        .champ { margin-bottom: 20px; }
        .champ label { display: block; margin-bottom: 6px; color: #212B36; font-size: .875rem; font-weight: 700; }

        .saisie {
            width: 100%;
            padding: 12px 16px;
            color: #212B36;
            background: #fff;
            border: 1px solid #E6EAED;
            border-radius: 6px;
            font: inherit;
            font-size: .95rem;
            outline: none;
            transition: border-color .15s ease, box-shadow .15s ease;
        }

        .saisie::placeholder { color: #94a3b8; }
        .saisie:focus { border-color: var(--orange); box-shadow: none; }
        .saisie.en-erreur { border-color: #f87171; background: #fef2f2; }

        .mot-de-passe { position: relative; }
        .mot-de-passe .saisie { padding-right: 46px; }

        .oeil {
            position: absolute;
            top: 50%;
            right: 12px;
            transform: translateY(-50%);
            padding: 0;
            color: #94a3b8;
            background: none;
            border: 0;
            cursor: pointer;
        }

        .oeil:hover { color: #475569; }
        .oeil svg { display: block; width: 20px; height: 20px; }

        .options { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 20px; }
        .souvenir { display: flex; align-items: center; gap: 8px; color: #475569; font-size: .875rem; cursor: pointer; }
        .souvenir input { width: 16px; height: 16px; accent-color: var(--violet); }

        .bouton {
            width: 100%;
            padding: 12px 24px;
            color: #fff;
            background: var(--orange);
            border: 0;
            border-radius: 6px;
            box-shadow: 0 1px 2px rgba(0, 0, 0, .08);
            font: inherit;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            transition: background-color .15s ease;
        }

        .bouton:hover { background: var(--orange-hover); }
        .bouton:focus-visible { outline: 2px solid var(--violet); outline-offset: 2px; }

        .aide { margin: 32px 0 0; color: #94a3b8; font-size: .75rem; text-align: center; line-height: 1.6; }
    </style>
</head>
<body>

@php
    $engrenage = 'M495.9 166.6c3.2 8.7.5 18.4-6.4 24.6l-43.3 39.4c1.1 8.3 1.7 16.8 1.7 25.4s-.6 17.1-1.7 25.4l43.3 39.4c6.9 6.2 9.6 15.9 6.4 24.6c-4.4 11.9-9.7 23.3-15.8 34.3l-4.7 8.1c-6.6 11-14 21.4-22.1 31.2c-5.9 7.2-15.7 9.6-24.5 6.8l-55.7-17.7c-13.4 10.3-28.2 18.9-44 25.4l-12.5 57.1c-2 9.1-9 16.3-18.2 17.8c-13.8 2.3-28 3.5-42.5 3.5s-28.7-1.2-42.5-3.5c-9.2-1.5-16.2-8.7-18.2-17.8l-12.5-57.1c-15.8-6.5-30.6-15.1-44-25.4L83.1 425.9c-8.8 2.8-18.6.3-24.5-6.8c-8.1-9.8-15.5-20.2-22.1-31.2l-4.7-8.1c-6.1-11-11.4-22.4-15.8-34.3c-3.2-8.7-.5-18.4 6.4-24.6l43.3-39.4C64.6 273.1 64 264.6 64 256s.6-17.1 1.7-25.4L22.4 191.2c-6.9-6.2-9.6-15.9-6.4-24.6c4.4-11.9 9.7-23.3 15.8-34.3l4.7-8.1c6.6-11 14-21.4 22.1-31.2c5.9-7.2 15.7-9.6 24.5-6.8l55.7 17.7c13.4-10.3 28.2-18.9 44-25.4l12.5-57.1c2-9.1 9-16.3 18.2-17.8C227.3 1.2 241.5 0 256 0s28.7 1.2 42.5 3.5c9.2 1.5 16.2 8.7 18.2 17.8l12.5 57.1c15.8 6.5 30.6 15.1 44 25.4l55.7-17.7c8.8-2.8 18.6-.3 24.5 6.8c8.1 9.8 15.5 20.2 22.1 31.2l4.7 8.1c6.1 11 11.4 22.4 15.8 34.3zM256 336a80 80 0 1 0 0-160 80 80 0 1 0 0 160z';

    $atouts = [
        ['icone' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4', 'texte' => 'Stock par dépôt, rayon et emplacement'],
        ['icone' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4', 'texte' => 'Ventes, proformas et factures'],
        ['icone' => 'M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4', 'texte' => 'Commandes du garage et bons de transfert'],
    ];
@endphp

<div class="page">

    {{-- ════════ PANNEAU GAUCHE — MARQUE ════════ --}}
    <div class="marque">

        <div class="engrenages" aria-hidden="true">
            <svg class="eng-a" viewBox="0 0 512 512"><path fill="#FE9F43" d="{{ $engrenage }}"/></svg>
            <svg class="eng-b" viewBox="0 0 512 512"><path fill="#FE9F43" d="{{ $engrenage }}"/></svg>
            <svg class="eng-c" viewBox="0 0 512 512"><path fill="#FE9F43" d="{{ $engrenage }}"/></svg>
        </div>

        <div class="marque-contenu">

            <div class="marque-logo">
                <img src="{{ asset('assets/img/logo/stcd.jpg') }}" alt="STCD Motors">
            </div>

            <h1 class="marque-nom">STCD Motors</h1>
            <p class="marque-slogan">Your Dream Car, One Visit Away</p>

            <div class="separateur"></div>

            <p class="accroche-1">Gérez votre magasin</p>
            <p class="accroche-2">en toute simplicité</p>

            <p class="marque-texte">
                Pièces détachées, stock par dépôt, ventes et factures —<br>
                tout le magasin dans un seul outil, relié à l'atelier.
            </p>

            <div class="atouts">
                @foreach($atouts as $atout)
                    <div class="atout">
                        <div class="atout-icone">
                            <svg fill="none" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $atout['icone'] }}"/>
                            </svg>
                        </div>
                        <span>{{ $atout['texte'] }}</span>
                    </div>
                @endforeach
            </div>

        </div>

        <p class="marque-pied">© {{ date('Y') }} STCD Motors — Tous droits réservés</p>

    </div>

    {{-- ════════ PANNEAU DROIT — FORMULAIRE ════════ --}}
    <div class="formulaire">
        <div class="boite">

            {{-- Logo (petits écrans) --}}
            <div class="logo-mobile">
                <img src="{{ asset('assets/img/logo/stcd.jpg') }}" alt="STCD Motors">
                <strong>STCD Motors</strong>
                <small>Your Dream Car, One Visit Away</small>
            </div>

            <h2 class="titre">Connexion</h2>
            <p class="sous-titre">Bienvenue sur le magasin, veuillez entrer vos identifiants.</p>

            @if (session('status'))
                <div class="alerte alerte-info">
                    <svg fill="none" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            {{-- Session expirée (10 min sans utilisation) --}}
            @if (session('timeout'))
                <div class="alerte alerte-attention" id="alerte-timeout">
                    <svg fill="none" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>{{ session('timeout') }}</span>
                </div>
            @endif

            @if ($errors->any())
                <div class="alerte alerte-erreur">
                    <svg fill="none" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="champ">
                    <label for="email">Adresse email</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}"
                           class="saisie @error('email') en-erreur @enderror"
                           placeholder="exemple@stcd.dj" autocomplete="email" autofocus required>
                </div>

                <div class="champ">
                    <label for="password">Mot de passe</label>
                    <div class="mot-de-passe">
                        <input type="password" id="password" name="password"
                               class="saisie @error('password') en-erreur @enderror"
                               placeholder="••••••••" autocomplete="current-password" required>
                        <button type="button" class="oeil" onclick="basculerMotDePasse()" aria-label="Afficher le mot de passe">
                            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="options">
                    <label class="souvenir">
                        <input type="checkbox" name="remember" @checked(old('remember'))>
                        Se souvenir de moi
                    </label>
                </div>

                <button type="submit" class="bouton">Se connecter</button>
            </form>

            <p class="aide">
                Mot de passe oublié ou pas encore de compte ?<br>
                Contactez votre administrateur.
            </p>

        </div>
    </div>

</div>

<script>
    function basculerMotDePasse() {
        var champ = document.getElementById('password');
        champ.type = champ.type === 'password' ? 'text' : 'password';
    }

    (function () {
        var expire = false;
        try { expire = sessionStorage.getItem('session_expired'); sessionStorage.removeItem('session_expired'); } catch (e) {}
        if (!expire || document.getElementById('alerte-timeout')) return;
        var div = document.createElement('div');
        div.className = 'alerte alerte-attention';
        div.innerHTML = '<svg fill="none" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'
            + '<span>Votre session a expiré après 10 minutes d'inactivité. Veuillez vous reconnecter.</span>';
        var form = document.querySelector('form');
        form && form.parentNode.insertBefore(div, form);
    })();
</script>

</body>
</html>
