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
    protected $fillable = [
        'numero',
        'source_system',

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
        if (! $this->bonTransfert) {
            return true;
        }

        $signature = fn (
            $position,
            $productId,
            $depotId,
            $quantite
        ) =>
            ((int) $position)
            .'|'.((int) $productId)
            .'|'.((int) $depotId)
            .'|'.number_format(
                (float) $quantite,
                2,
                '.',
                ''
            );

        $bc = $this->lignes
            ->map(
                fn ($ligne) =>
                    $signature(
                        $ligne->position,
                        $ligne->product_id,
                        $ligne->depot_id,
                        $ligne->quantite_demandee
                    )
            )
            ->sort()
            ->values()
            ->all();

        $bt = $this->bonTransfert
            ->lignes
            ->map(
                fn ($ligne) =>
                    $signature(
                        $ligne->position,
                        $ligne->product_id,
                        $ligne->depot_id,
                        $ligne->quantite
                    )
            )
            ->sort()
            ->values()
            ->all();

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
