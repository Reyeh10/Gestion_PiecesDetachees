<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Bon de commande reçu depuis un système externe
 * (par exemple : l'application Atelier).
 *
 * Le bon de commande contient plusieurs lignes de pièces.
 * Lorsque toutes les pièces sont identifiées, disponibles et rattachées
 * à leurs dépôts respectifs, un bon de transfert peut être généré.
 */
class ExternalBonCommande extends Model
{
    /**
     * Table associée au modèle.
     */
    protected $table = 'external_bons_commande';

    /**
     * Champs pouvant être renseignés en masse.
     */
    /** V6 : BC interne d'une sortie de pièces pour un bus leasing / transport public */
    public const SOURCE_BUS = 'magasin-bus';

    /** V6 : BC interne d'un BT direct « consommation atelier » (équipement du garage) */
    public const SOURCE_CONSO = 'magasin-conso';

    /** V6 : BC interne d'un BT direct pour un autre usage interne (intersociété, équipe commerciale…) */
    public const SOURCE_INTERNE = 'magasin-interne';

    /** Motifs d'un BT direct (« + BT ») ; seul le premier part à l'Atelier */
    public const MOTIF_CONSO = 'consommation_atelier';

    public const MOTIFS = [
        self::MOTIF_CONSO    => 'Consommation atelier',
        'intersociete'       => 'Vente intersociété',
        'equipe_commerciale' => 'Équipe commerciale',
        'autre'              => 'Autre usage interne',
    ];

    protected $fillable = [
        'numero',
        'source_system',
        'motif',
        'beneficiaire',
        'bus_atelier_id',
        'groupe_bus',
        'equipement_atelier_id',
        'equipement',
        'demande_par',

        'vehicule_marque',
        'vehicule_modele',
        'vehicule_immatriculation',
        'vehicule_vin',

        'client_nom',
        'client_telephone',

        'statut',

        // Ancienne relation vers une vente.
        // Conservée pour les anciens bons déjà convertis.
        'vente_id',

        // Nouveau bon de transfert généré pour ce BC.
        'bon_transfert_id',

        'vu_at',
    ];

    /**
     * Conversions automatiques.
     */
    protected $casts = [
        'vu_at' => 'datetime',
    ];

    /** BC reçus du garage (sans les sorties bus ni les BT directs faits au Magasin). */
    public function scopeGarage($query)
    {
        return $query->whereNotIn('source_system', [self::SOURCE_BUS, self::SOURCE_CONSO, self::SOURCE_INTERNE]);
    }

    /** Consommation atelier : BT directs du Magasin + BC « consommation » créés par l'Atelier */
    public function scopeConsommations($query)
    {
        return $query->where(fn ($q) => $q->where('source_system', self::SOURCE_CONSO)->orWhereNotNull('equipement'));
    }

    public function scopeSortiesBus($query)
    {
        return $query->where('source_system', self::SOURCE_BUS);
    }

    public function estBus(): bool
    {
        return $this->source_system === self::SOURCE_BUS;
    }

    /** BT direct « consommation atelier » saisi au Magasin */
    public function estConsommationDirecte(): bool
    {
        return $this->source_system === self::SOURCE_CONSO;
    }

    /** BT direct « usage interne » (intersociété, équipe commerciale…) : rien n'est envoyé à l'Atelier */
    public function estInterne(): bool
    {
        return $this->source_system === self::SOURCE_INTERNE;
    }

    /** BT direct saisi au Magasin (« + BT »), quel que soit le motif */
    public function estBtDirect(): bool
    {
        return $this->estConsommationDirecte() || $this->estInterne();
    }

    public function libelleMotif(): ?string
    {
        return $this->estConsommationDirecte() ? self::MOTIFS[self::MOTIF_CONSO] : (self::MOTIFS[$this->motif] ?? null);
    }

    /** Pièces pour un équipement du garage (BC de l'Atelier ou BT direct) */
    public function estConsommation(): bool
    {
        return $this->estConsommationDirecte() || filled($this->equipement);
    }

    public function equipementAtelier(): BelongsTo
    {
        return $this->belongsTo(EquipementAtelier::class, 'equipement_atelier_id');
    }

    public function bus(): BelongsTo
    {
        return $this->belongsTo(BusAtelier::class, 'bus_atelier_id');
    }

    /**
     * Lignes du bon de commande.
     */
    public function lignes(): HasMany
    {
        return $this->hasMany(ExternalBonCommandeLigne::class);
    }

    /**
     * Ancienne vente éventuellement générée à partir du BC.
     *
     * Cette relation est conservée pour assurer la compatibilité
     * avec les anciens bons déjà transformés en vente/facture.
     */
    public function vente(): BelongsTo
    {
        return $this->belongsTo(Sale::class, 'vente_id');
    }

    /**
     * Bon de transfert créé à partir de ce bon de commande.
     */
    public function bonTransfert(): BelongsTo
    {
        return $this->belongsTo(
            BonTransfert::class,
            'bon_transfert_id'
        );
    }

    /**
     * Tous les BT du BC : le premier, puis les BT du reste (R1, R2…). V6.
     */
    public function bonsTransfert(): HasMany
    {
        return $this->hasMany(BonTransfert::class, 'external_bon_commande_id')
            ->orderBy('reste');
    }

    /** Pièces pas encore transférées (aucun BT). */
    public function lignesEnAttente()
    {
        return $this->lignes->filter(fn (ExternalBonCommandeLigne $l) => ! $l->bon_transfert_id);
    }

    /** Pièces en attente qui sont prêtes à partir (pour le BT ou le BT du reste). */
    public function lignesPretesAEnvoyer()
    {
        return $this->lignesEnAttente()->filter(fn (ExternalBonCommandeLigne $l) => $l->estPrete());
    }

    /**
     * Indique si cet ancien bon a déjà été transformé en vente/facture.
     *
     * Les anciens BC convertis avant la mise en place des bons
     * de transfert peuvent encore posséder un vente_id.
     */
    public function estFacture(): bool
    {
        return $this->vente_id !== null;
    }

    /**
     * Vérifie si le bon de transfert correspond encore exactement
     * aux lignes actuelles du bon de commande.
     *
     * Si une pièce, un dépôt ou une quantité du BC est modifié après
     * la création du BT, cette méthode retourne false.
     */
    public function bonTransfertAJour(): bool
    {
        if (! $this->bon_transfert_id) {
            return true;
        }

        $signature = fn ($btId, $position, $productId, $depotId, $quantite) =>
            ((int) $btId)
            .'|'.((int) $position)
            .'|'.((int) $productId)
            .'|'.((int) $depotId)
            .'|'.number_format((float) $quantite, 2, '.', '');

        // V6 : un BC peut avoir plusieurs BT (le premier + les BT du reste).
        // Seules les pièces déjà transférées (rattachées à un BT) comptent ;
        // les pièces en attente partiront dans un BT du reste.
        $bc = $this->lignes
            ->filter(fn ($ligne) => $ligne->bon_transfert_id)
            ->map(fn ($ligne) => $signature($ligne->bon_transfert_id, $ligne->position, $ligne->product_id, $ligne->depot_id, $ligne->quantite_demandee))
            ->sort()->values()->all();

        $bt = $this->bonsTransfert
            ->flatMap(fn ($bonTransfert) => $bonTransfert->lignes->map(
                fn ($ligne) => $signature($bonTransfert->id, $ligne->position, $ligne->product_id, $ligne->depot_id, $ligne->quantite)
            ))
            ->sort()->values()->all();

        return $bc === $bt;
    }

    /**
     * Toutes les pièces sont-elles prêtes pour générer
     * le bon de transfert ?
     *
     * Chaque ligne doit :
     * - être disponible ;
     * - avoir une pièce identifiée ;
     * - être rattachée à un dépôt.
     */
    public function toutesPiecesDisponibles(): bool
    {
        return $this->lignes->isNotEmpty()
            && $this->lignes->every(
                fn (ExternalBonCommandeLigne $ligne) =>
                    $ligne->disponible === true
                    && $ligne->product_id !== null
                    && $ligne->depot_id !== null
            );
    }
}
