@extends('layouts.layoutMaster')

@section('content')

@php
    // Liste des pièces pour la recherche (Select2), envoyée UNE fois pour
    // toutes les lignes (avant : 784 options recopiées dans chaque ligne).
    $piecesSelect = $products->map(fn ($product) => [
        'id'          => $product->id,
        'text'        => trim($product->reference . ' — ' . $product->designation
            . ' (' . trim(($product->brand->name ?? '') . ' ' . ($product->model->name ?? '')) . ')'
            . ' — stock: ' . (float) $product->quantity
            . ((float) $product->quantity <= 0 ? ' — INDISPONIBLE' : '')),
        'reference'   => $product->reference,
        'designation' => $product->designation,
        'stock'       => (float) $product->quantity,
    ])->values();

    // Stock par dépôt de chaque produit, pour permettre au vendeur de choisir
    // le dépôt de prélèvement (comme dans la vente normale) sans recharger la page.
    $depotsByProduct = $products->mapWithKeys(function ($product) {
        return [
            $product->id => $product->depotStocks->map(fn ($stock) => [
                'depot_id' => $stock->depot_id,
                'name'     => $stock->depot->name ?? ('Dépôt #' . $stock->depot_id),
                'quantity' => (float) $stock->quantity,
            ])->values(),
        ];
    });
@endphp

<style>

    .fc-card {
        border: none;
        border-radius: 18px;
        overflow: hidden;
        background: #fff;
        box-shadow: 0 4px 18px rgba(0,0,0,.04);
    }

    .fc-header {
        padding: 25px 30px;
        border-bottom: 1px solid #f1f1f1;
        background: #fff;
    }

    .fc-title {
        font-size: 28px;
        font-weight: 700;
        color: #2c3e50;
        margin-bottom: 4px;
    }

    .fc-subtitle {
        color: #94a3b8;
        font-size: 14px;
    }

    .fc-btn-back {
        background: #f1f5f9;
        border: none;
        color: #334155;
        padding: 10px 18px;
        border-radius: 12px;
        font-weight: 600;
        transition: .2s;
    }

    .fc-btn-back:hover {
        background: #e2e8f0;
        color: #334155;
    }

    .fc-info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 16px;
        margin-bottom: 28px;
    }

    .fc-info-box {
        background: #f8fafc;
        border-radius: 14px;
        padding: 18px 20px;
    }

    .fc-info-label {
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: #94a3b8;
        font-weight: 700;
        margin-bottom: 6px;
    }

    .fc-info-value {
        font-size: 15px;
        font-weight: 700;
        color: #334155;
    }

    .fc-info-sub {
        font-size: 13px;
        color: #64748b;
        margin-top: 2px;
    }

    .fc-table {
        margin: 0;
        width: 100%;
        border-collapse: separate;
        border-spacing: 0 12px;
    }

    /* Colonnes à largeur fixe : évite que le tableau se redimensionne
       (et que tout l'écran "bouge") à chaque sélection de pièce/dépôt. */
    .fc-table {
        table-layout: fixed;
        min-width: 1150px;
    }

    .fc-table .col-ref    { width: 11%; }
    .fc-table .col-desig  { width: 17%; }
    .fc-table .col-qte    { width: 8%; }
    .fc-table .col-stock  { width: 8%; }
    .fc-table .col-prix   { width: 9%; }
    .fc-table .col-ident  { width: 47%; }

    .fc-table tbody td { word-break: break-word; }

    .ligne-identification-form,
    .ligne-depot-form {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        width: 100%;
    }

    .ligne-identification-form .select2-container {
        flex: 0 0 100%;
        width: 100% !important;
        min-width: 0;
    }

    .ligne-identification-form .select2-selection__rendered {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .ligne-identification-form .ligne-product-select,
    .ligne-identification-form .ligne-depot-select,
    .ligne-identification-form .ligne-note-input,
    .ligne-depot-form select {
        flex: 1 1 150px;
        min-width: 0;
        width: auto !important;
    }

    /* Note masquée SANS libérer sa place : la ligne ne change pas de hauteur. */
    .ligne-identification-form .ligne-note-input.note-masquee {
        visibility: hidden;
    }

    /* Hauteur fixe des champs : choisir une pièce ne fait plus « sauter » la ligne. */
    .ligne-identification-form .select2-container .select2-selection--single,
    .ligne-identification-form .form-select,
    .ligne-identification-form .form-control {
        height: 38px;
    }

    /* Messages flottants en bas à droite : ils ne poussent plus le contenu. */
    .bc-toasts {
        position: fixed;
        right: 24px;
        bottom: 24px;
        z-index: 2000;
        display: flex;
        flex-direction: column;
        gap: 10px;
        max-width: min(440px, calc(100vw - 48px));
    }

    .bc-toast {
        margin: 0;
        box-shadow: 0 10px 25px rgba(15, 23, 42, .15);
        transition: opacity .4s ease, transform .4s ease;
    }

    .bc-toast-out {
        opacity: 0;
        transform: translateY(10px);
        pointer-events: none;
    }

    .fc-table thead th {
        border: none !important;
        background: transparent;
        color: #64748b;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 1px;
        font-weight: 700;
        padding: 0 20px 10px;
    }

    .fc-table tbody tr {
        background: #ffffff;
        box-shadow: 0 2px 10px rgba(0,0,0,.03);
        border-radius: 14px;
    }

    .fc-table tbody td {
        padding: 18px 20px;
        border: none !important;
        vertical-align: middle;
        background: white;
    }

    .fc-table tbody tr td:first-child {
        border-top-left-radius: 14px;
        border-bottom-left-radius: 14px;
    }

    .fc-table tbody tr td:last-child {
        border-top-right-radius: 14px;
        border-bottom-right-radius: 14px;
    }

    .badge-bt,
    .badge-en-attente {
        display: inline-block;
        margin-bottom: 4px;
        padding: 3px 10px;
        border-radius: 12px;
        font-size: 11.5px;
        font-weight: 800;
    }

    .badge-bt { color: #155EEF; background: #e0eaff; }

    .badge-commandee {
        display: inline-block;
        margin-bottom: 4px;
        padding: 3px 10px;
        color: #6d28d9;
        background: #ede9fe;
        border-radius: 12px;
        font-size: 11.5px;
        font-weight: 800;
    }

    .badge-commandee a { color: inherit; text-decoration: underline; }
    .badge-en-attente { color: #b45309; background: #fef3c7; }

    .badge-dispo {
        background: rgba(34,197,94,.12);
        color: #16a34a;
        padding: 8px 14px;
        border-radius: 30px;
        font-size: 12px;
        font-weight: 700;
    }

    .badge-indispo {
        background: rgba(239,68,68,.12);
        color: #dc2626;
        padding: 8px 14px;
        border-radius: 30px;
        font-size: 12px;
        font-weight: 700;
    }

    .badge-attente {
        background: rgba(148,163,184,.15);
        color: #64748b;
        padding: 8px 14px;
        border-radius: 30px;
        font-size: 12px;
        font-weight: 700;
    }

</style>

<div class="card fc-card">

    {{-- HEADER --}}
    <div class="fc-header d-flex justify-content-between align-items-center flex-wrap gap-3">

        <div>
            <div class="fc-title">{{ $commande->numero }}</div>
            <div class="fc-subtitle">Reçu le {{ $commande->created_at->format('d/m/Y à H:i') }} depuis {{ $commande->source_system }}</div>
        </div>

        <div class="d-flex gap-2">

            {{-- Anciennes commandes déjà converties en vente --}}
            @if($commande->vente_id)

                <a href="{{ route('sales.show', $commande->vente_id) }}" class="btn btn-success">
                    <i class="bx bx-receipt"></i> Voir la facture
                </a>

            {{-- V6 : un ou plusieurs BT (le premier + les BT du reste -R1, -R2…) --}}
            @elseif($commande->bon_transfert_id)

                @foreach($commande->bonsTransfert as $btBc)
                    <a href="{{ route('bons-transfert.show', $btBc) }}" class="btn btn-success">
                        <i class="bx bx-transfer"></i> {{ $btBc->numero }}
                    </a>
                @endforeach

                @if(!$commande->bonTransfertAJour())
                    <form
                        method="POST"
                        action="{{ route('fournisseur-commandes.mettre-a-jour-bon-transfert', $commande) }}"
                        class="m-0"
                    >
                        @csrf

                        <button
                            type="submit"
                            class="btn btn-warning"
                            onclick="return confirm('Mettre à jour les bons de transfert et les renvoyer au garage ?')"
                        >
                            <i class="bx bx-refresh"></i>
                            Mettre à jour le BT
                        </button>
                    </form>
                @endif

                @if($commande->lignesPretesAEnvoyer()->isNotEmpty())
                    <form
                        method="POST"
                        action="{{ route('fournisseur-commandes.creer-bon-transfert-reste', $commande) }}"
                        class="m-0"
                    >
                        @csrf

                        <button
                            type="submit"
                            class="btn btn-primary"
                            onclick="return confirm('Créer le bon de transfert du reste ({{ $commande->lignesPretesAEnvoyer()->count() }} pièce(s) prête(s)) et l\'envoyer au garage ?')"
                        >
                            <i class="bx bx-transfer-alt"></i>
                            Créer le BT du reste
                        </button>
                    </form>
                @endif

            {{-- Création du premier BT : possible dès qu'une pièce est prête (BT partiel) --}}
            @elseif($commande->lignesPretesAEnvoyer()->isNotEmpty())

                @php
                    $nbPretes = $commande->lignesPretesAEnvoyer()->count();
                    $nbAttente = $commande->lignes->count() - $nbPretes;
                @endphp

                <form
                    method="POST"
                    action="{{ route('fournisseur-commandes.creer-bon-transfert', $commande) }}"
                    class="m-0"
                >
                    @csrf

                    <button
                        type="submit"
                        class="btn btn-success"
                        onclick="return confirm('{{ $nbAttente > 0
                            ? "Créer un bon de transfert PARTIEL avec les {$nbPretes} pièce(s) prête(s) ? Les {$nbAttente} autre(s) resteront en attente (BT du reste)."
                            : "Voulez-vous créer le bon de transfert et envoyer les pièces au garage ?" }}')"
                    >
                        <i class="bx bx-transfer"></i>
                        {{ $nbAttente > 0 ? "Créer le BT partiel ({$nbPretes}/" . $commande->lignes->count() . ')' : 'Créer le bon de transfert' }}
                    </button>
                </form>

            @endif


            @if($peutCommander && $aCommander->isNotEmpty())
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#commanderFournisseurModal">
                    <i class="bx bx-cart-add"></i> Commander au fournisseur ({{ $aCommander->count() }})
                </button>
            @endif

            @if(auth()->user()?->isAdmin() && ! $commande->estFacture())
                <button type="button" class="btn btn-outline-danger"
                        data-bs-toggle="modal" data-bs-target="#supprimerBcModal">
                    <i class="bx bx-trash"></i> Supprimer
                </button>
            @endif


            <a href="{{ route('fournisseur-commandes.index') }}" class="fc-btn-back">
                <i class="bx bx-arrow-back"></i>
                Retour à la liste
            </a>
        </div>

    </div>

    <div class="card-body p-4">

        @if(session('success') || session('error'))
            <div class="bc-toasts">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible bc-toast">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible bc-toast">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
            </div>
        @endif

        @if($commande->bon_transfert_id && ! $commande->bonTransfertAJour())
            <div class="alert alert-warning">
                <i class="bx bx-error"></i>
                Ce bon de commande a été modifié après le bon de transfert
                <strong>{{ $commande->bonsTransfert->pluck('numero')->implode(', ') }}</strong> :
                cliquez sur « Mettre à jour le BT ». Les pièces ajoutées au devis restent en attente
                et partiront dans un BT du reste.
            </div>
        @endif

        {{-- V6 : pièces en attente (BT partiel) --}}
        @if(! $commande->vente_id && $commande->bon_transfert_id && $commande->lignesEnAttente()->isNotEmpty())
            <div class="alert alert-info">
                <i class="bx bx-time-five"></i>
                <strong>{{ $commande->lignesEnAttente()->count() }} pièce(s) en attente</strong>
                (pas encore dans un bon de transfert).
                @if($commande->lignesPretesAEnvoyer()->isNotEmpty())
                    {{ $commande->lignesPretesAEnvoyer()->count() }} sont prêtes : cliquez sur « Créer le BT du reste ».
                @else
                    Elles partiront dans un BT du reste dès qu'elles seront disponibles (réapprovisionnement).
                @endif
            </div>
        @endif

        {{-- INFOS VÉHICULE / CLIENT --}}
        <div class="fc-info-grid">

            @if($commande->equipement)
            <div class="fc-info-box">
                <div class="fc-info-label">Équipement (consommation atelier)</div>
                <div class="fc-info-value">{{ $commande->equipement }}</div>
                <div class="fc-info-sub">Pièces pour le garage, pas pour un véhicule</div>
            </div>
            @else
            <div class="fc-info-box">
                <div class="fc-info-label">Véhicule</div>
                <div class="fc-info-value">{{ $commande->vehicule_marque }} {{ $commande->vehicule_modele }}</div>
                <div class="fc-info-sub">{{ $commande->vehicule_immatriculation }}</div>
                @if($commande->vehicule_vin)
                <div class="fc-info-sub">VIN : {{ $commande->vehicule_vin }}</div>
                @endif
            </div>
            @endif

            <div class="fc-info-box">
                <div class="fc-info-label">Client</div>
                <div class="fc-info-value">{{ $commande->client_nom }}</div>
                <div class="fc-info-sub">{{ $commande->client_telephone }}</div>
            </div>

            <div class="fc-info-box">
                <div class="fc-info-label">Statut</div>
                <div class="fc-info-value">{{ ucfirst($commande->statut) }}</div>
            </div>

        </div>

        {{-- LIGNES DE PIÈCES --}}
        <form method="POST" action="{{ route('fournisseur-commandes.lignes.update-all', $commande) }}">
            @csrf
            @method('PUT')

        <div class="table-responsive">

            <table class="table fc-table align-middle">

                <thead>
                    <tr>
                        <th class="col-ref">Référence</th>
                        <th class="col-desig">Désignation (garage)</th>
                        <th class="col-qte">Quantité demandée</th>
                        <th class="col-stock">Stock chez nous</th>
                        <th class="col-prix">Prix de vente</th>
                        <th class="col-ident">Identification / Disponibilité</th>
                    </tr>
                </thead>

                <tbody>

                    @foreach($commande->lignes as $ligne)

                        <tr>

                            <td class="fw-bold">{{ $ligne->reference ?: '—' }}</td>

                            <td>{{ $ligne->designation }}</td>

                            <td>{{ (float) $ligne->quantite_demandee }}</td>

                            <td>{{ $ligne->product ? (float) $ligne->quantite_disponible : '—' }}</td>

                            <td>{{ $ligne->prix_unitaire !== null ? number_format($ligne->prix_unitaire, 2) : '—' }}</td>

                            <td>
                                @if(! $commande->estFacture())

                                    {{-- Même formulaire sur TOUTES les lignes (avec ou sans référence
                                         garage) : le vendeur peut toujours remplacer la pièce, choisir
                                         le dépôt, ou noter pourquoi rien ne correspond. La liste des
                                         pièces est chargée une seule fois (STCD_PIECES, plus bas). --}}
                                    <div class="ligne-identification-form">

                                        <select name="lignes[{{ $ligne->id }}][product_id]"
                                                class="form-select ligne-product-select"
                                                data-selection="{{ $ligne->product_id }}">
                                            <option value="">— Rechercher une pièce —</option>
                                            @if($ligne->product)
                                                <option value="{{ $ligne->product_id }}" selected>
                                                    {{ $ligne->product->reference }} — {{ $ligne->product->designation }}
                                                </option>
                                            @endif
                                        </select>

                                        <select name="lignes[{{ $ligne->id }}][depot_id]" class="form-select form-select-sm ligne-depot-select"
                                                @unless($ligne->product) disabled @endunless>
                                            <option value="">— Choisir le dépôt —</option>
                                            @if($ligne->product)
                                                @foreach($ligne->product->depotStocks as $stock)
                                                    <option value="{{ $stock->depot_id }}" @selected($ligne->depot_id === $stock->depot_id)>
                                                        {{ $stock->depot->name ?? ('Dépôt #' . $stock->depot_id) }}
                                                        — stock: {{ (float) $stock->quantity }}
                                                    </option>
                                                @endforeach
                                            @endif
                                        </select>

                                        {{-- Toujours présente (place réservée) : masquée sans décaler la ligne --}}
                                        <input type="text" name="lignes[{{ $ligne->id }}][note]" value="{{ $ligne->note }}"
                                               placeholder="Note (optionnel — ex: raison d'indisponibilité)"
                                               class="form-control form-control-sm ligne-note-input @if($ligne->product_id) note-masquee @endif">

                                    </div>

                                @endif

                                <div class="mt-2 ligne-statut">
                                    {{-- V6 : pièce déjà partie dans un BT, ou en attente --}}
                                    @if($ligne->bon_transfert_id && $ligne->bonTransfert)
                                        <span class="badge-bt">Transférée — {{ $ligne->bonTransfert->numero }}</span>
                                    @elseif($commande->bon_transfert_id)
                                        <span class="badge-en-attente">En attente — BT du reste</span>
                                    @endif

                                    {{-- V6 : pièce commandée au fournisseur depuis ce BC --}}
                                    @php $bcf = $ligne->demandePiece?->latestSupplierOrderItem?->supplierOrder; @endphp
                                    @if($bcf && $bcf->status !== \App\Models\SupplierOrder::STATUS_CANCELLED && ! $ligne->bon_transfert_id)
                                        <span class="badge-commandee">
                                            Commandée chez {{ $ligne->demandePiece->supplier?->name ?? 'fournisseur' }} —
                                            <a href="{{ route('supplier-orders.show', $bcf) }}">{{ $bcf->order_number }}</a>
                                        </span>
                                    @endif

                                    @if($ligne->product_id)
                                        @if($ligne->disponible)
                                            <span class="badge-dispo">Disponible</span>
                                        @elseif($ligne->disponible === false && ! $ligne->depot_id)
                                            {{-- Choisie au catalogue mais sans stock : prix transmis au garage --}}
                                            <span class="badge-indispo">En rupture — prix transmis au garage</span>
                                            <span class="text-muted small d-block mt-1">À l'arrivée de la pièce : choisissez le dépôt puis « Tout valider ».</span>
                                        @elseif($ligne->depot_id)
                                            <span class="badge-indispo">Indisponible dans ce dépôt</span>
                                        @else
                                            <span class="badge-attente">Pièce trouvée — choisissez le dépôt</span>
                                        @endif
                                    @elseif(! is_null($ligne->disponible))
                                        <span class="badge-indispo">{{ $ligne->reference ? 'Référence inconnue au magasin' : 'Marquée indisponible' }}</span>
                                    @else
                                        <span class="badge-attente">Sans référence — à vérifier</span>
                                    @endif
                                    @if($ligne->note)
                                        <span class="text-muted small d-block mt-1">{{ $ligne->note }}</span>
                                    @endif
                                </div>
                            </td>

                        </tr>

                    @endforeach

                </tbody>

            </table>

        </div>

            @unless($commande->estFacture())
                <div class="d-flex justify-content-end mt-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bx bx-check-double"></i> Tout valider
                    </button>
                </div>
            @endunless

        </form>

    </div>

</div>

<script>
    // Liste des pièces, chargée UNE seule fois pour toutes les lignes :
    // { id, text, reference, designation, stock } — et le stock par dépôt.
    window.STCD_PIECES = @json($piecesSelect);
    window.STCD_PRODUCT_DEPOTS = @json($depotsByProduct);
</script>

<script>
    document.addEventListener('DOMContentLoaded', function () {

        /* ---------------------------------------------------------------
           Retour à la même hauteur de page après « Tout valider »
           (avant : la page repartait tout en haut à chaque enregistrement).
           --------------------------------------------------------------- */
        var cleScroll = 'stcd-bc-scroll-' + window.location.pathname;

        try {
            var hauteur = sessionStorage.getItem(cleScroll);
            if (hauteur !== null) {
                sessionStorage.removeItem(cleScroll);
                window.scrollTo(0, parseInt(hauteur, 10) || 0);
            }
        } catch (e) {}

        document.querySelectorAll('form').forEach(function (form) {
            form.addEventListener('submit', function () {
                try { sessionStorage.setItem(cleScroll, String(window.scrollY)); } catch (e) {}
            });
        });

        /* ---------------------------------------------------------------
           Recherche de pièce (Select2) : référence ou désignation.
           --------------------------------------------------------------- */
        function matchReferenceDesignation(params, data) {
            var terme = $.trim(params.term || '').toUpperCase();
            if (terme === '') return data;
            if (!data.id) return null;

            var reference = (data.reference || '').toString().toUpperCase();
            var designation = (data.designation || '').toString().toUpperCase();

            return (reference.indexOf(terme) > -1 || designation.indexOf(terme) > -1) ? data : null;
        }

        function styleOption(data) {
            if (!data.id) return data.text;
            var $result = $('<span></span>').text(data.text);
            if (data.stock !== undefined && parseFloat(data.stock) <= 0) {
                $result.css('color', '#dc2626').css('font-weight', '600');
            }
            return $result;
        }

        function escapeHtml(str) {
            return $('<div>').text(str == null ? '' : str).html();
        }

        function populateDepotSelect($depot, depots) {
            var html = '<option value="">— Choisir le dépôt —</option>';

            (depots || []).forEach(function (d) {
                html += '<option value="' + d.depot_id + '">'
                    + escapeHtml(d.name) + ' — stock: ' + d.quantity
                    + '</option>';
            });

            $depot.html(html);
            $depot.prop('disabled', !depots || depots.length === 0);

            // Un seul dépôt possible : on le choisit directement.
            if (depots && depots.length === 1) {
                $depot.val(String(depots[0].depot_id));
            }
        }

        $('.ligne-identification-form').each(function () {
            var $form = $(this);
            var $select = $form.find('.ligne-product-select');
            var $note = $form.find('.ligne-note-input');
            var $depot = $form.find('.ligne-depot-select');
            var selection = String($select.data('selection') || '');

            // L'option affichée sans JavaScript est remplacée par la liste commune.
            $select.find('option[value!=""]').remove();

            $select.select2({
                width: '100%',
                placeholder: '— Rechercher une pièce —',
                allowClear: true,
                data: window.STCD_PIECES,
                matcher: matchReferenceDesignation,
                templateResult: styleOption,
            });

            if (selection) {
                $select.val(selection).trigger('change.select2');
            }

            // La note sert à expliquer « aucune pièce ne correspond » : elle
            // garde sa place (pas de décalage) mais n'est visible que sans pièce.
            function toggleNote() {
                $note.toggleClass('note-masquee', !!$select.val());
            }

            $select.on('change', function () {
                toggleNote();
                var productId = $select.val();
                var depots = productId ? (window.STCD_PRODUCT_DEPOTS[productId] || []) : [];
                populateDepotSelect($depot, depots);
            });

            toggleNote();
        });

        /* Messages : disparaissent seuls après quelques secondes */
        setTimeout(function () {
            document.querySelectorAll('.bc-toast').forEach(function (t) { t.classList.add('bc-toast-out'); });
        }, 6000);
    });
</script>

{{-- ================================================================
     V6 — COMMANDER AU FOURNISSEUR les pièces non disponibles
     (crée les demandes de pièces spéciales + le BC fournisseur en
     brouillon, approuvé ensuite par l'administrateur)
================================================================ --}}
@if($peutCommander && $aCommander->isNotEmpty())
    <div class="modal fade" id="commanderFournisseurModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <form method="POST" action="{{ route('fournisseur-commandes.commander-fournisseur', $commande) }}" class="modal-content">
                @csrf

                <div class="modal-header">
                    <h5 class="modal-title">Commander au fournisseur — {{ $commande->numero }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>

                <div class="modal-body">

                    <p class="text-muted mb-3">
                        @if($commande->equipement)
                        Consommation atelier : <strong>{{ $commande->equipement }}</strong>.
                        @else
                        Véhicule : <strong>{{ trim($commande->vehicule_marque . ' ' . $commande->vehicule_modele) ?: '—' }}</strong>
                        {{ $commande->vehicule_immatriculation }} — client {{ $commande->client_nom ?: '—' }}.
                        @endif
                        Ces informations sont reprises automatiquement dans la commande.
                    </p>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Fournisseur <span class="text-danger">*</span></label>
                            <select name="supplier_id" class="form-select" required>
                                <option value="">Choisir le fournisseur…</option>
                                @foreach($suppliers as $s)
                                    <option value="{{ $s->id }}" @selected(old('supplier_id') == $s->id)>{{ $s->name }}{{ $s->currency ? ' (' . $s->currency . ')' : '' }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Dépôt de réception <span class="text-danger">*</span></label>
                            <select name="depot_id" class="form-select" required>
                                @foreach($depots as $d)
                                    <option value="{{ $d->id }}" @selected(old('depot_id', $depotParDefaut) == $d->id)>{{ $d->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <table class="table align-middle no-cartes cmd-table">
                        <thead>
                            <tr>
                                <th style="width:40px;"></th>
                                <th>Pièce du garage</th>
                                <th style="width:190px;">Référence magasin <span class="text-danger">*</span></th>
                                <th>Désignation</th>
                                <th style="width:100px;">Quantité</th>
                                <th style="width:140px;">Prix d'achat</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($aCommander as $l)
                                @php $ref = $l->product?->reference ?? $l->reference; @endphp
                                <tr>
                                    <td>
                                        <input type="checkbox" class="form-check-input" name="lignes[{{ $l->id }}][commander]" value="1" checked>
                                    </td>
                                    <td>
                                        <strong>{{ $l->reference ?: '—' }}</strong>
                                        <div class="text-muted small">{{ $l->designation }}</div>
                                    </td>
                                    <td>
                                        <input type="text" name="lignes[{{ $l->id }}][reference]" class="form-control form-control-sm"
                                               value="{{ old("lignes.{$l->id}.reference", $ref) }}" maxlength="100" placeholder="Référence au magasin">
                                        @if(! $l->product_id)
                                            <div class="small text-muted mt-1">Absente du catalogue : fiche créée à la réception.</div>
                                        @endif
                                    </td>
                                    <td>
                                        <input type="text" name="lignes[{{ $l->id }}][designation]" class="form-control form-control-sm"
                                               value="{{ old("lignes.{$l->id}.designation", $l->product?->designation ?? $l->designation) }}" maxlength="255">
                                    </td>
                                    <td>
                                        <input type="number" name="lignes[{{ $l->id }}][quantite]" class="form-control form-control-sm"
                                               value="{{ old("lignes.{$l->id}.quantite", (float) $l->quantite_demandee) }}" min="0.01" step="0.01">
                                    </td>
                                    <td>
                                        <input type="number" name="lignes[{{ $l->id }}][prix]" class="form-control form-control-sm"
                                               value="{{ old("lignes.{$l->id}.prix", $l->product?->purchase_price) }}" min="0" step="0.0001" placeholder="Si connu">
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <p class="small text-muted mb-0">
                        Le bon de commande est créé en <strong>brouillon</strong> : il doit être approuvé par l'administrateur.
                        À la réception, les pièces entrent en stock et redeviennent disponibles sur ce BC : il suffira de créer le BT du reste.
                    </p>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bx bx-cart-add"></i> Créer le bon de commande
                    </button>
                </div>
            </form>
        </div>
    </div>
@endif

{{-- ================================================================
     SUPPRESSION MANUELLE DU BC (administrateur uniquement)
================================================================ --}}
@if(auth()->user()->isAdmin() && ! $commande->estFacture())
    <div class="modal fade" id="supprimerBcModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form method="POST" action="{{ route('fournisseur-commandes.destroy', $commande) }}">
                    @csrf
                    @method('DELETE')

                    <div class="modal-header">
                        <h5 class="modal-title">Supprimer le bon de commande {{ $commande->numero }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <div class="alert alert-danger small mb-0">
                            Le bon de commande et ses lignes seront <strong>définitivement supprimés</strong> du magasin.
                            Si le garage renvoie ce BC plus tard, il réapparaîtra comme un nouveau BC.
                        </div>

                        @if($commande->bonTransfert)
                            <div class="alert alert-warning small mt-3">
                                Son bon de transfert <strong>{{ $commande->bonTransfert->numero }}</strong> sera supprimé aussi.
                            </div>

                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="remettre_en_stock" value="1" id="remettreStockBc" checked>
                                <label class="form-check-label" for="remettreStockBc">
                                    Les pièces sont revenues au magasin : les <strong>remettre en stock</strong> dans leur dépôt
                                </label>
                            </div>
                            <div class="text-muted small mt-1">
                                Décochez si les pièces sont restées au garage (le stock ne bouge pas).
                            </div>
                        @endif
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                        <button type="submit" class="btn btn-danger">
                            <i class="bx bx-trash me-1"></i> Supprimer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif

@push('scripts')
    <script src="{{ asset('assets/vendor/libs/select2/select2.js') }}"></script>
@endpush
@endsection
