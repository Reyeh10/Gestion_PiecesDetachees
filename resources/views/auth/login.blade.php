{{--
    PAGE DE CONNEXION — même présentation que celle de l'Atelier
    (sys-atelier/resources/views/auth/login.blade.php) : panneau de marque
    ardoise à gauche, formulaire blanc à droite, accents orange #f97316.
    Différence : à la place des engrenages du garage, des PIÈCES qui tournent
    en fond (disque de frein, roulement, écrou) et les textes du magasin.

    Page autonome (CSS intégré, valeurs Tailwind de l'Atelier) : ne dépend pas
    de Vite / npm run build. Champs inchangés : email, password, remember.
--}}
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Connexion — STCD Motors Magasin</title>

    <style>
        *, *::before, *::after { box-sizing: border-box; }

        html, body { margin: 0; min-height: 100%; }

        body {
            font-family: 'Instrument Sans', ui-sans-serif, system-ui, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol', 'Noto Color Emoji';
            background: #f9fafb;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        .page { min-height: 100vh; display: flex; }

        /* ===== PANNEAU GAUCHE : MARQUE ===== */
        .marque {
            display: none;
            position: relative;
            width: 58.333333%;
            overflow-x: hidden;
            overflow-y: auto;
            flex-direction: column;
            align-items: center;
            padding: 48px 0;
            background: linear-gradient(145deg, #0f172a 0%, #1e293b 60%, #0c1525 100%);
        }

        @media (min-width: 1024px) { .marque { display: flex; } }

        /* Pièces en fond (même place, taille et vitesse que les engrenages de l'Atelier) */
        .pieces { position: absolute; inset: 0; overflow: hidden; pointer-events: none; }
        .pieces svg { position: absolute; }
        .pc-a { bottom: -100px; right: -100px; width: 360px; height: 360px; opacity: .12; animation: tourne-h 24s linear infinite; }
        .pc-b { top: -70px; left: -70px; width: 260px; height: 260px; opacity: .10; animation: tourne-ah 15s linear infinite; }
        .pc-c { top: 50%; left: 65%; width: 130px; height: 130px; opacity: .10; animation: tourne-h 9s linear infinite; }

        @keyframes tourne-h  { to { transform: rotate(360deg); } }
        @keyframes tourne-ah { to { transform: rotate(-360deg); } }

        @media (prefers-reduced-motion: reduce) { .pieces svg { animation: none; } }

        .marque-contenu {
            position: relative;
            z-index: 10;
            display: flex;
            flex-direction: column;
            align-items: center;
            max-width: 42rem;
            margin: auto 0;
            padding: 0 64px;
            text-align: center;
        }

        .marque-logo {
            margin-bottom: 2rem;
            padding: 12px;
            background: rgba(255, 255, 255, .06);
            border: 1px solid rgba(249, 115, 22, .25);
            border-radius: 24px;
        }

        .marque-logo img { display: block; width: 7rem; height: 7rem; object-fit: contain; border-radius: 1rem; }

        .marque-nom { margin: 0 0 .4rem; color: #fff; font-size: 2.5rem; font-weight: 800; letter-spacing: .02em; line-height: 1.2; }
        .marque-slogan { margin: 0 0 2rem; color: #fb923c; font-size: 1.1rem; font-style: italic; }

        .separateur {
            width: 80px;
            height: 3px;
            margin-bottom: 2rem;
            border-radius: 9px;
            background: linear-gradient(90deg, transparent, #f97316, transparent);
        }

        .accroche-1, .accroche-2 { margin: 0; font-size: 3rem; font-weight: 800; line-height: 1.15; }
        .accroche-1 { margin-bottom: .5rem; color: #fff; text-shadow: 0 2px 20px rgba(0, 0, 0, .5); }
        .accroche-2 { margin-bottom: 2rem; color: #f97316; text-shadow: 0 2px 20px rgba(249, 115, 22, .3); }

        .marque-texte { margin: 0 0 2.5rem; color: #94a3b8; font-size: 1.05rem; line-height: 1.7; }

        .atouts { width: 100%; display: flex; flex-direction: column; gap: 1rem; }
        .atout { display: flex; align-items: center; gap: 1rem; text-align: left; }

        .atout-icone {
            flex-shrink: 0;
            width: 46px;
            height: 46px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            background: rgba(249, 115, 22, .22);
            border: 1px solid rgba(249, 115, 22, .45);
        }

        .atout-icone svg { width: 22px; height: 22px; stroke: #f97316; }
        .atout span { color: #e2e8f0; font-size: 1rem; font-weight: 500; }

        .marque-pied { position: relative; z-index: 10; flex-shrink: 0; margin: 2rem 0 0; color: #64748b; font-size: .875rem; text-align: center; }

        /* ===== PANNEAU DROIT : FORMULAIRE ===== */
        .formulaire {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 48px 40px;
            background: #fff;
        }

        @media (min-width: 1024px) { .formulaire { width: 41.666667%; } }

        .boite { width: 100%; max-width: 24rem; }

        .logo-mobile { display: flex; flex-direction: column; align-items: center; gap: .5rem; margin-bottom: 2.5rem; }
        .logo-mobile img { width: 4rem; height: 4rem; object-fit: contain; border-radius: .75rem; box-shadow: 0 1px 3px rgba(0, 0, 0, .1), 0 1px 2px -1px rgba(0, 0, 0, .1); }
        .logo-mobile strong { margin: 0; color: #0f172a; font-size: 1.125rem; font-weight: 700; }
        .logo-mobile small { margin: 0; color: #94a3b8; font-size: .75rem; font-style: italic; }

        @media (min-width: 1024px) { .logo-mobile { display: none; } }

        .entete { margin-bottom: 2rem; }
        .titre { margin: 0 0 .25rem; color: #0f172a; font-size: 1.875rem; font-weight: 700; line-height: 2.25rem; }
        .sous-titre { margin: 0; color: #64748b; font-size: .875rem; }

        .alerte {
            display: flex;
            align-items: flex-start;
            gap: .75rem;
            margin-bottom: 1.25rem;
            padding: .75rem 1rem;
            border-radius: .75rem;
            font-size: .875rem;
        }

        .alerte svg { flex-shrink: 0; width: 1.25rem; height: 1.25rem; margin-top: .125rem; }
        .alerte-erreur { color: #b91c1c; background: #fef2f2; border: 1px solid #fecaca; }
        .alerte-erreur svg { stroke: #ef4444; }
        .alerte-info { color: #15803d; background: #f0fdf4; border: 1px solid #bbf7d0; }
        .alerte-info svg { stroke: #22c55e; }
        .alerte-attention { color: #92400e; background: #fffbeb; border: 1px solid #fde68a; }
        .alerte-attention svg { stroke: #f59e0b; }

        form > * + * { margin-top: 1.25rem; }

        .champ label { display: block; margin-bottom: .375rem; color: #334155; font-size: .875rem; font-weight: 500; }

        .saisie {
            width: 100%;
            padding: .75rem 1rem;
            color: #0f172a;
            background: #fff;
            border: 1px solid #d1d5db;
            border-radius: .75rem;
            font: inherit;
            font-size: 1rem;
            outline: none;
            transition: border-color .15s ease, box-shadow .15s ease;
        }

        .saisie::placeholder { color: #94a3b8; }
        .saisie:focus { border-color: transparent; box-shadow: 0 0 0 2px #f97316; }
        .saisie.en-erreur { border-color: #f87171; background: #fef2f2; }

        .mot-de-passe { position: relative; }

        .oeil {
            position: absolute;
            top: 50%;
            right: .75rem;
            transform: translateY(-50%);
            padding: 0;
            color: #94a3b8;
            background: none;
            border: 0;
            cursor: pointer;
        }

        .oeil:hover { color: #475569; }
        .oeil svg { display: block; width: 1.25rem; height: 1.25rem; }

        .options { display: flex; align-items: center; justify-content: space-between; gap: .75rem; }
        .souvenir { display: flex; align-items: center; color: #475569; font-size: .875rem; cursor: pointer; }
        .souvenir input { width: 1rem; height: 1rem; margin: 0 .5rem 0 0; accent-color: #f97316; }
        .oubli { color: #f97316; font-size: .875rem; font-weight: 500; text-decoration: none; }
        .oubli:hover { color: #ea580c; }

        .bouton {
            width: 100%;
            padding: .75rem 1.5rem;
            color: #fff;
            background: #f97316;
            border: 0;
            border-radius: .75rem;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, .05);
            font: inherit;
            font-weight: 600;
            cursor: pointer;
            transition: background-color .15s ease;
        }

        .bouton:hover { background: #ea580c; }
        .bouton:focus { outline: none; }

        .aide { margin: 2rem 0 0; color: #94a3b8; font-size: .75rem; text-align: center; }
    </style>
</head>
<body>

@php
    // Pièces dessinées en fond (viewBox 512 × 512, remplissage pair-impair pour les trous)
    $cercle = fn ($cx, $cy, $r) => sprintf('M%s %sa%s %s 0 1 0 %s 0a%s %s 0 1 0 %s 0z', $cx - $r, $cy, $r, $r, 2 * $r, $r, $r, -2 * $r);
    $autour = function (int $n, float $rayon, float $r, float $decalage = 0) use ($cercle) {
        $d = '';
        for ($i = 0; $i < $n; $i++) {
            $a = deg2rad($decalage + 360 * $i / $n);
            $d .= $cercle(round(256 + $rayon * cos($a), 1), round(256 + $rayon * sin($a), 1), $r);
        }
        return $d;
    };

    // Disque de frein ventilé : disque, moyeu évidé, 5 trous de roue, 2 couronnes de perçages
    $disque = $cercle(256, 256, 248) . $cercle(256, 256, 70)
        . $autour(5, 105, 16, -90) . $autour(16, 172, 9) . $autour(16, 208, 9, 11.25);

    // Roulement à billes : bague extérieure, 9 billes, bague intérieure
    $roulement = $cercle(256, 256, 248) . $cercle(256, 256, 200)
        . $autour(9, 165, 30) . $cercle(256, 256, 130) . $cercle(256, 256, 88);

    // Écrou hexagonal avec son trou taraudé
    $ecrou = 'M256 8 470.8 132v248L256 504 41.2 380V132z' . $cercle(256, 256, 104);

    $atouts = [
        ['icone' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4', 'texte' => 'Stock par dépôt, rayon et emplacement'],
        ['icone' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4', 'texte' => 'Ventes, proformas et factures'],
        ['icone' => 'M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4', 'texte' => 'Commandes du garage et bons de transfert'],
    ];
@endphp

<div class="page">

    {{-- ════════ PANNEAU GAUCHE — MARQUE ════════ --}}
    <div class="marque">

        {{-- Pièces en fond --}}
        <div class="pieces" aria-hidden="true">
            <svg class="pc-a" viewBox="0 0 512 512"><path fill="#f97316" fill-rule="evenodd" d="{{ $disque }}"/></svg>
            <svg class="pc-b" viewBox="0 0 512 512"><path fill="#f97316" fill-rule="evenodd" d="{{ $roulement }}"/></svg>
            <svg class="pc-c" viewBox="0 0 512 512"><path fill="#f97316" fill-rule="evenodd" d="{{ $ecrou }}"/></svg>
        </div>

        <div class="marque-contenu">

            <div class="marque-logo">
                <img src="{{ asset('assets/img/logo/stcd.jpg') }}" alt="STCD Motors">
            </div>

            <h1 class="marque-nom">STCD Motors</h1>
            <p class="marque-slogan">Your Dream Car, One Visit Away</p>

            <div class="separateur"></div>

            <h2 class="accroche-1">Gérez votre magasin</h2>
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

            <div class="entete">
                <h1 class="titre">Connexion</h1>
                <p class="sous-titre">Bienvenue, veuillez entrer vos identifiants.</p>
            </div>

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
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="oubli">Mot de passe oublié ?</a>
                    @endif
                </div>

                <button type="submit" class="bouton">Se connecter</button>
            </form>

            <p class="aide">Vous n'avez pas de compte ? Contactez votre administrateur.</p>

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
            + '<span>Votre session a expiré après 10 minutes d\'inactivité. Veuillez vous reconnecter.</span>';
        var form = document.querySelector('form');
        form && form.parentNode.insertBefore(div, form);
    })();
</script>

</body>
</html>
