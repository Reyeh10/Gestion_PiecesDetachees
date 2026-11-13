{{-- Lignes d'une demande de pièces (V7) : pièce du catalogue ou hors catalogue,
     quantité, fournisseur, prix indicatif. Le fournisseur suffit : le bon de
     commande se crée tout seul. Variables : $pieces, $suppliers. --}}
<div class="table-responsive">
    <table class="table align-middle no-cartes mb-2" id="lignes-demande">
        <thead>
            <tr>
                <th style="min-width:300px;">Pièce</th>
                <th style="width:110px;">Quantité</th>
                <th style="width:110px;">Unité</th>
                <th style="min-width:200px;">Fournisseur</th>
                <th style="width:140px;" class="text-end">Prix indicatif</th>
                <th style="width:40px;"></th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
</div>

<div class="d-flex flex-wrap gap-2 align-items-center">
    <button type="button" class="btn btn-outline-primary btn-sm" id="ajouter-ligne-demande"><i class="bx bx-plus"></i> Ajouter une pièce</button>
    <span class="text-muted small">Le prix est indicatif : le vrai prix se saisit à la réception (facture du fournisseur).</span>
</div>

<template id="modele-ligne-demande">
    <tr>
        <td>
            <div class="bloc-catalogue">
                <select class="form-select sel-piece" data-champ="product_id"><option value=""></option></select>
            </div>
            <div class="bloc-libre row g-1" style="display:none;">
                <div class="col-5"><input type="text" class="form-control form-control-sm" data-champ="reference" placeholder="Référence"></div>
                <div class="col-7"><input type="text" class="form-control form-control-sm" data-champ="part_name" placeholder="Désignation *"></div>
            </div>
            <div class="d-flex justify-content-between mt-1">
                <button type="button" class="btn btn-link btn-sm p-0 basculer-libre">Pièce hors catalogue</button>
            </div>
            <div class="alerte-stock small text-warning mt-1" style="display:none;"></div>
        </td>
        <td><input type="number" class="form-control" data-champ="quantity" min="0.01" step="0.01" value="1" required></td>
        <td><input type="text" class="form-control" data-champ="unit" placeholder="Pièce"></td>
        <td>
            <select class="form-select" data-champ="supplier_id">
                <option value="">— À chercher —</option>
                @foreach($suppliers as $s)
                    <option value="{{ $s->id }}">{{ $s->name }}</option>
                @endforeach
            </select>
        </td>
        <td><input type="number" class="form-control text-end" data-champ="prix" min="0" step="0.01" placeholder="—"></td>
        <td class="text-end"><button type="button" class="btn btn-sm btn-outline-danger retirer" title="Retirer">&times;</button></td>
    </tr>
</template>

@push('scripts')
<script src="{{ asset('assets/vendor/libs/select2/select2.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var PIECES = @json($pieces);
    var LIGNES = @json(array_values(old('lignes', [])));
    var parId = {};
    PIECES.forEach(function (p) { parId[p.id] = p; });

    var $tbody = $('#lignes-demande tbody');
    var modele = document.getElementById('modele-ligne-demande');
    var n = 0;

    function fournisseurParDefaut() { return $('#fournisseur-defaut').val() || ''; }

    function alerteStock($tr, p) {
        var $a = $tr.find('.alerte-stock');
        if (!p || !p.stocks || !p.stocks.length) { $a.hide().text(''); return; }
        $a.html('<i class="bx bx-error"></i> Déjà en stock : ' + p.stocks.map(function (s) {
            return $('<div>').text(s.depot).html() + ' (' + s.q + ')';
        }).join(', ') + ' — un transfert entre dépôts suffit peut-être.').show();
    }

    function modeLibre($tr, libre) {
        $tr.find('.bloc-catalogue').toggle(!libre);
        $tr.find('.bloc-libre').toggle(libre);
        $tr.find('.basculer-libre').text(libre ? 'Choisir dans le catalogue' : 'Pièce hors catalogue');
        if (libre) { $tr.find('.sel-piece').val('').trigger('change.select2'); alerteStock($tr, null); }
        else { $tr.find('[data-champ=reference],[data-champ=part_name]').val(''); }
        $tr.data('libre', libre);
    }

    function ajouterLigne(l) {
        var i = n++;
        var $tr = $(modele.content.firstElementChild.cloneNode(true));
        $tr.find('[data-champ]').each(function () { this.name = 'lignes[' + i + '][' + this.dataset.champ + ']'; });
        $tbody.append($tr);

        var $piece = $tr.find('.sel-piece');
        $piece.select2({
            width: '100%',
            placeholder: '— Rechercher dans le catalogue (référence ou désignation) —',
            allowClear: true,
            data: PIECES,
            matcher: function (params, data) {
                var t = $.trim(params.term || '').toUpperCase();
                if (t === '') return data;
                if (!data.id) return null;
                return ((data.ref || '').toUpperCase().indexOf(t) > -1 || (data.des || '').toUpperCase().indexOf(t) > -1) ? data : null;
            }
        });

        $piece.on('change', function () {
            var p = parId[$piece.val()];
            alerteStock($tr, p);
            if (p) {
                if (!$tr.find('[data-champ=unit]').val()) $tr.find('[data-champ=unit]').val(p.unite || '');
                if (!$tr.find('[data-champ=prix]').val() && p.prix) $tr.find('[data-champ=prix]').val(p.prix);
            }
        });
        $tr.find('.basculer-libre').on('click', function () { modeLibre($tr, !$tr.data('libre')); });
        $tr.find('.retirer').on('click', function () { if ($tbody.find('tr').length > 1) $tr.remove(); });

        l = l || {};
        $tr.find('[data-champ=supplier_id]').val(l.supplier_id || fournisseurParDefaut());
        ['quantity', 'unit', 'prix', 'reference', 'part_name'].forEach(function (c) {
            if (l[c] !== undefined && l[c] !== null) $tr.find('[data-champ=' + c + ']').val(l[c]);
        });
        if (l.product_id) { $piece.val(String(l.product_id)).trigger('change'); }
        else if (l.part_name || l.reference) { modeLibre($tr, true); }
    }

    // Fournisseur pour toutes les lignes : appliqué aux lignes sans fournisseur
    $('#fournisseur-defaut').on('change', function () {
        var v = this.value;
        $tbody.find('[data-champ=supplier_id]').each(function () { if (!this.value) this.value = v; });
    });

    (LIGNES.length ? LIGNES : [null]).forEach(ajouterLigne);
    $('#ajouter-ligne-demande').on('click', function () { ajouterLigne(null); });
});
</script>
@endpush
