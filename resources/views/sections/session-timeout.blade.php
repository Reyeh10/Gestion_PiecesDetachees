{{-- ============================================================
    EXPIRATION DE SESSION (comme l'app Atelier)
    Après 10 minutes sans utilisation : déconnexion. Une fenêtre prévient
    60 s avant avec un bouton « Rester connecté ».
    La dernière activité est partagée entre les onglets : un onglet oublié
    ne déconnecte pas celui qu'on utilise.
    Sécurité côté serveur : App\Http\Middleware\SessionTimeout.
============================================================ --}}
@auth
<div id="stcd-idle-warning" class="stcd-idle" hidden>
    <div class="stcd-idle-fond"></div>
    <div class="stcd-idle-boite" role="alertdialog" aria-labelledby="stcd-idle-titre">
        <div class="stcd-idle-icone">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <h2 id="stcd-idle-titre">Session bientôt expirée</h2>
        <p>
            Vous serez déconnecté dans
            <span id="stcd-idle-countdown">60</span>
            secondes en raison d'inactivité.
        </p>
        <button type="button" onclick="stcdIdleReset()">Rester connecté</button>
    </div>
</div>

<form id="stcd-idle-logout-form" method="POST" action="{{ route('logout') }}" hidden>
    @csrf
</form>

<script>
(function () {
    var TIMEOUT = {{ \App\Http\Middleware\SessionTimeout::TIMEOUT }}; // 10 minutes
    var WARNING = 60;                                                     // avertir 60 s avant
    var CLE = 'stcd-derniere-activite';
    var derniere = Date.now();
    var averti = false;
    var parti = false;
    var dernierPing = Date.now();

    // Le serveur ne voit que les requêtes : on lui signale l'activité dans
    // la page (formulaire long, lecture…) au plus une fois par minute.
    function ping() {
        dernierPing = Date.now();
        fetch(@json(route('session.ping')), {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': @json(csrf_token())
            }
        }).then(function (r) {
            if (r.status === 401) window.stcdSessionExpiree();
        }).catch(function () {});
    }

    function lire() {
        try { return parseInt(localStorage.getItem(CLE) || '0', 10) || 0; } catch (e) { return 0; }
    }

    function noter() {
        derniere = Date.now();
        try { localStorage.setItem(CLE, String(derniere)); } catch (e) {}
        if (derniere - dernierPing > 60000) ping();
    }

    function fermerAvertissement() {
        if (averti) {
            document.getElementById('stcd-idle-warning').hidden = true;
            averti = false;
        }
    }

    // Activité : notée au plus une fois toutes les 5 s (pas à chaque mouvement)
    function activite() {
        if (averti) return; // pendant l'avertissement, seul le bouton compte
        if (Date.now() - derniere > 5000) noter();
    }

    window.stcdIdleReset = function () {
        noter();
        fermerAvertissement();
        ping(); // le serveur repart aussi à zéro
    };

    function deconnecter() {
        if (parti) return;
        parti = true;
        try { sessionStorage.setItem('session_expired', '1'); } catch (e) {}
        document.getElementById('stcd-idle-logout-form').submit();
    }
    window.stcdSessionExpiree = function () {
        if (parti) return;
        parti = true;
        try { sessionStorage.setItem('session_expired', '1'); } catch (e) {}
        window.location.href = @json(route('login'));
    };

    ['mousemove', 'mousedown', 'keydown', 'scroll', 'touchstart', 'click'].forEach(function (e) {
        document.addEventListener(e, activite, { passive: true, capture: true });
    });

    noter();

    // Calcul sur l'heure réelle : reste juste même si l'ordinateur était en veille
    setInterval(function () {
        derniere = Math.max(derniere, lire());
        var inactif = Math.floor((Date.now() - derniere) / 1000);
        var reste = TIMEOUT - inactif;

        if (reste <= 0) {
            deconnecter();
            return;
        }

        if (reste <= WARNING) {
            document.getElementById('stcd-idle-countdown').textContent = reste;
            if (!averti) {
                document.getElementById('stcd-idle-warning').hidden = false;
                averti = true;
            }
        } else {
            fermerAvertissement(); // activité dans un autre onglet
        }
    }, 1000);
})();
</script>
@endauth
