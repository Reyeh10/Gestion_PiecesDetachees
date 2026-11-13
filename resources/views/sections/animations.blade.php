{{-- ============================================================
    ANIMATIONS DU DESIGN (V6)
    - chiffres du tableau de bord qui défilent jusqu'à leur valeur ;
    - onde au clic sur les boutons.
    Rien ne se lance si l'ordinateur demande moins d'animations.
============================================================ --}}
<script>
(function () {
    if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

    // Chiffres : « 23 352 », « 46 204 677 788,81 »… défilent depuis 0
    function compter(el) {
        var texte = (el.textContent || '').trim();
        var m = texte.match(/^([^\d-]*)(-?[\d\s  ]+(?:,\d+)?)(.*)$/);
        if (!m) return;
        var brut = m[2].replace(/[\s  ]/g, '');
        var decimales = (brut.split(',')[1] || '').length;
        var cible = parseFloat(brut.replace(',', '.'));
        if (!isFinite(cible) || cible === 0) return;

        var format = new Intl.NumberFormat('fr-FR', { minimumFractionDigits: decimales, maximumFractionDigits: decimales });
        var debut = null;
        var duree = 900;

        function pas(t) {
            if (debut === null) debut = t;
            var p = Math.min(1, (t - debut) / duree);
            var v = cible * (1 - Math.pow(1 - p, 3));
            el.textContent = m[1] + format.format(p < 1 ? v : cible).replace(/[  ]/g, ' ') + m[3];
            if (p < 1) requestAnimationFrame(pas);
        }

        requestAnimationFrame(pas);
    }

    document.addEventListener('DOMContentLoaded', function () {
        if (!document.body.classList.contains('stcd-dreamspos')) return;
        document.querySelectorAll('.dashboard-value').forEach(compter);
    });

    // Onde au clic sur les boutons
    document.addEventListener('pointerdown', function (e) {
        var btn = e.target.closest && e.target.closest('.btn');
        if (!btn || btn.disabled || !document.body.classList.contains('stcd-dreamspos')) return;
        var r = btn.getBoundingClientRect();
        var onde = document.createElement('span');
        onde.className = 'dp-onde';
        onde.style.left = (e.clientX - r.left) + 'px';
        onde.style.top = (e.clientY - r.top) + 'px';
        btn.appendChild(onde);
        setTimeout(function () { onde.remove(); }, 600);
    });
})();
</script>
