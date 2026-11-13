{{-- Lignes d'un BT interne (sortie bus, BT direct « + BT »), saisies comme un devis.
     Variables : $pieces, $depotsParPiece, $depots, $lignes (SaisiePiecesCatalogue) ;
     $prixModifiable (facultatif) : prix proposé au catalogue mais modifiable. --}}
        <table class="table align-middle no-cartes" id="lignes-pieces">
            <thead>
                <tr>
                    <th style="width:{{ ($prixModifiable ?? false) ? '40' : '45' }}%;">Pièce</th>
                    <th style="width:22%;">Dépôt</th>
                    <th style="width:10%;">Quantité</th>
                    <th class="text-end" @if($prixModifiable ?? false) style="width:14%;" @endif>Prix HT</th>
                    <th class="text-end">Total</th>
                    <th></th>
                </tr>
            </thead>
            <tbody></tbody>
            <tfoot>
                <tr>
                    <td colspan="4" class="text-end fw-bold">Total HT</td>
                    <td class="text-end fw-bold" id="total-pieces">0</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>

        <button type="button" class="btn btn-outline-primary btn-sm" id="ajouter-ligne"><i class="bx bx-plus"></i> Ajouter une pièce</button>


@push('scripts')
<script src="{{ asset('assets/vendor/libs/select2/select2.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var PIECES = @json($pieces);
    var DEPOTS = @json($depotsParPiece);
    var TOUS_DEPOTS = @json($depots);
    var LIGNES = @json(array_values($lignes));
    var PRIX_MODIFIABLE = @json((bool) ($prixModifiable ?? false));
    var prixParPiece = {};
    PIECES.forEach(function (p) { prixParPiece[p.id] = p.prix; });

    var $tbody = $('#lignes-pieces tbody');
    var n = 0;

    function fdj(v) { return Math.round(v).toLocaleString('fr-FR'); }

    function remplirDepots($depot, productId, choisi) {
        var liste = (DEPOTS[productId] || []).slice();
        // dépôt déjà dans le BT mais vide maintenant (tout est parti) : on le garde
        if (choisi && !liste.some(function (d) { return String(d.depot_id) === String(choisi); })) {
            var d = TOUS_DEPOTS.find(function (x) { return String(x.id) === String(choisi); });
            if (d) liste.push({ depot_id: d.id, name: d.name, quantity: 0 });
        }
        var html = '<option value="">— Dépôt —</option>';
        liste.forEach(function (d) {
            html += '<option value="' + d.depot_id + '">' + $('<div>').text(d.name).html() + ' — stock : ' + d.quantity + '</option>';
        });
        $depot.html(html);
        if (choisi) $depot.val(String(choisi));
        else if (liste.length === 1) $depot.val(String(liste[0].depot_id));
    }

    function recalculer() {
        var total = 0;
        $tbody.find('tr').each(function () {
            var $tr = $(this);
            var prix = PRIX_MODIFIABLE
                ? (parseFloat($tr.find('.prix-saisi').val()) || 0)
                : (prixParPiece[$tr.find('.sel-piece').val()] || 0);
            var q = parseFloat($tr.find('.qte').val()) || 0;
            if (!PRIX_MODIFIABLE) $tr.find('.prix').text(prix ? fdj(prix) : '');
            $tr.find('.total').text(prix ? fdj(prix * q) : '');
            total += prix * q;
        });
        $('#total-pieces').text(fdj(total) + ' FDJ');
    }

    function ajouterLigne(l) {
        var i = n++;
        var $tr = $('<tr>' +
            '<td><select class="form-select sel-piece" name="lignes[' + i + '][product_id]" required><option value=""></option></select></td>' +
            '<td><select class="form-select sel-depot" name="lignes[' + i + '][depot_id]" required><option value="">— Dépôt —</option></select></td>' +
            '<td><input type="number" class="form-control qte" name="lignes[' + i + '][quantite]" min="0.01" step="0.01" required></td>' +
            (PRIX_MODIFIABLE
                ? '<td><input type="number" class="form-control text-end prix-saisi" name="lignes[' + i + '][prix]" min="0" step="0.01" required></td>'
                : '<td class="text-end prix"></td>') +
            '<td class="text-end total"></td>' +
            '<td class="text-end"><button type="button" class="btn btn-sm btn-outline-danger retirer" title="Retirer">&times;</button></td>' +
        '</tr>');
        $tbody.append($tr);

        var $piece = $tr.find('.sel-piece');
        $piece.select2({
            width: '100%',
            placeholder: '— Rechercher une pièce (référence ou désignation) —',
            data: PIECES,
            matcher: function (params, data) {
                var t = $.trim(params.term || '').toUpperCase();
                if (t === '' ) return data;
                if (!data.id) return null;
                return ((data.reference || '').toUpperCase().indexOf(t) > -1 || (data.designation || '').toUpperCase().indexOf(t) > -1) ? data : null;
            }
        });

        if (l && l.product_id) {
            $piece.val(String(l.product_id)).trigger('change.select2');
            remplirDepots($tr.find('.sel-depot'), l.product_id, l.depot_id);
        }
        $tr.find('.qte').val(l && l.quantite ? l.quantite : 1);
        if (l && l.product_id) {
            $tr.find('.prix-saisi').val(l.prix !== undefined && l.prix !== null && l.prix !== '' ? l.prix : (prixParPiece[l.product_id] || 0));
        }

        $piece.on('change', function () {
            remplirDepots($tr.find('.sel-depot'), $piece.val(), null);
            $tr.find('.prix-saisi').val(prixParPiece[$piece.val()] || 0); // prix catalogue proposé
            recalculer();
        });
        $tr.find('.qte, .prix-saisi').on('input', recalculer);
        $tr.find('.retirer').on('click', function () {
            if ($tbody.find('tr').length > 1) { $tr.remove(); recalculer(); }
        });
        recalculer();
    }

    LIGNES.forEach(ajouterLigne);
    $('#ajouter-ligne').on('click', function () { ajouterLigne(null); });
});
</script>
@endpush
