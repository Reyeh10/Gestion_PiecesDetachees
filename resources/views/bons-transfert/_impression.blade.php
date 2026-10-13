{{--
    Impression directe d'un bon de transfert : un bouton portant
    data-imprimer="{{ route('bons-transfert.imprimer', $bt) }}" charge le PDF
    du BT dans un cadre invisible puis ouvre la fenêtre d'impression du
    navigateur (même document que le PDF téléchargé). Si le navigateur bloque
    l'impression depuis le cadre, le PDF s'ouvre dans un nouvel onglet.
--}}
@push('scripts')
<script>
    document.addEventListener('click', function (event) {
        var bouton = event.target.closest('[data-imprimer]');

        if (!bouton) {
            return;
        }

        event.preventDefault();

        var url = bouton.getAttribute('data-imprimer');
        var ancien = document.getElementById('bt-cadre-impression');

        if (ancien) {
            ancien.remove();
        }

        var cadre = document.createElement('iframe');
        cadre.id = 'bt-cadre-impression';
        cadre.style.cssText = 'position:fixed;right:0;bottom:0;width:0;height:0;border:0;';

        cadre.onload = function () {
            try {
                cadre.contentWindow.focus();
                cadre.contentWindow.print();
            } catch (e) {
                window.open(url, '_blank');
            }
        };

        cadre.src = url;
        document.body.appendChild(cadre);
    });
</script>
@endpush
