<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Demande de pièces : le dossier qui regroupe les pièces spéciales demandées
 * ensemble (une saisie, un import Excel ou une demande de l'Atelier).
 *
 * Dès qu'une pièce a son fournisseur, le bon de commande de ce fournisseur se
 * crée tout seul (App\Services\CircuitAchatService). Une demande a donc un ou
 * plusieurs bons de commande : un par fournisseur, plus les compléments
 * (pièce ajoutée après l'envoi). Elle est terminée quand toutes ses pièces
 * sont reçues, soldées ou annulées.
 */
class Demande extends Model
{
    public const SOURCE_SAISIE  = 'saisie';
    public const SOURCE_IMPORT  = 'import';
    public const SOURCE_ATELIER = 'atelier';

    public const SOURCES = [
        self::SOURCE_SAISIE  => 'Saisie',
        self::SOURCE_IMPORT  => 'Import Excel',
        self::SOURCE_ATELIER => 'Demande de l\'Atelier',
    ];

    protected $fillable = [
        'numero',
        'source',
        'vehicle_id',
        'depot_id',
        'external_bon_commande_id',
        'created_by',
        'notes',
    ];

    public function pieces(): HasMany
    {
        return $this->hasMany(VehiclePartRequest::class);
    }

    public function bonsCommande(): HasMany
    {
        return $this->hasMany(SupplierOrder::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function depot(): BelongsTo
    {
        return $this->belongsTo(Depot::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function bonCommandeGarage(): BelongsTo
    {
        return $this->belongsTo(ExternalBonCommande::class, 'external_bon_commande_id');
    }

    /** DEM-AAAA-NNNN, à appeler dans une transaction. */
    public static function prochainNumero(): string
    {
        $prefixe = 'DEM-' . now()->format('Y') . '-';

        $dernier = static::query()
            ->where('numero', 'like', $prefixe . '%')
            ->lockForUpdate()
            ->orderByDesc('id')
            ->value('numero');

        $suivant = $dernier ? ((int) substr($dernier, strlen($prefixe))) + 1 : 1;

        return $prefixe . str_pad((string) $suivant, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Étapes d'une demande (onglets de la liste), de la moins avancée à la
     * plus avancée. Une demande est à l'étape de sa pièce la MOINS avancée :
     * 1 pièce en recherche + 2 commandées → « En recherche ».
     *   [clé => [libellé, couleur, statuts qui la font être à cette étape]]
     */
    public const ETAPES = [
        'en-recherche' => ['En recherche', 'warning', [VehiclePartRequest::STATUS_SEARCHING, VehiclePartRequest::STATUS_FOUND]],
        'a-commander'  => ['À commander', 'info', [VehiclePartRequest::STATUS_TO_ORDER]],
        'commandees'   => ['Commandée', 'primary', [VehiclePartRequest::STATUS_ORDERED, VehiclePartRequest::STATUS_PARTIAL_RECEIVED]],
        'non-trouvees' => ['Non trouvée / annulée', 'danger', [VehiclePartRequest::STATUS_NOT_FOUND]],
        'recues'       => ['Reçue', 'success', [VehiclePartRequest::STATUS_RECEIVED]],
    ];

    /**
     * Filtre « demandes à l'étape $etape » (onglets exclusifs : chaque demande
     * est dans un seul onglet).
     */
    public function scopeEtape($query, string $etape)
    {
        $cles = array_keys(self::ETAPES);
        $rang = array_search($etape, $cles, true);

        if ($rang === false) {
            return $query;
        }

        // aucune pièce d'une étape moins avancée
        $avant = collect(array_slice(self::ETAPES, 0, $rang))->flatMap(fn ($e) => $e[2])->all();

        if ($avant) {
            $query->whereDoesntHave('pieces', fn ($p) => $p->whereIn('status', $avant));
        }

        if ($etape === 'non-trouvees') {
            // une pièce non trouvée, ou toutes annulées (rien de reçu)
            return $query->has('pieces')->where(fn ($q) => $q
                ->whereHas('pieces', fn ($p) => $p->where('status', VehiclePartRequest::STATUS_NOT_FOUND))
                ->orWhereDoesntHave('pieces', fn ($p) => $p->where('status', '!=', VehiclePartRequest::STATUS_CANCELLED)));
        }

        if ($etape === 'recues') {
            return $query->whereHas('pieces', fn ($p) => $p->where('status', VehiclePartRequest::STATUS_RECEIVED))
                ->whereDoesntHave('pieces', fn ($p) => $p->whereNotIn('status', [VehiclePartRequest::STATUS_RECEIVED, VehiclePartRequest::STATUS_CANCELLED]));
        }

        return $query->whereHas('pieces', fn ($p) => $p->whereIn('status', self::ETAPES[$etape][2]));
    }

    /** Clé de l'étape (voir ETAPES), null si la demande n'a pas de pièce. */
    public function etape(): ?string
    {
        $statuts = ($this->relationLoaded('pieces') ? $this->pieces : $this->pieces()->get(['id', 'demande_id', 'status']))
            ->pluck('status')->unique();

        if ($statuts->isEmpty()) {
            return null;
        }

        foreach (['en-recherche', 'a-commander', 'commandees'] as $cle) {
            if ($statuts->intersect(self::ETAPES[$cle][2])->isNotEmpty()) {
                return $cle;
            }
        }

        return $statuts->contains(VehiclePartRequest::STATUS_RECEIVED) && ! $statuts->contains(VehiclePartRequest::STATUS_NOT_FOUND)
            ? 'recues'
            : 'non-trouvees';
    }

    public function libelleEtape(): string
    {
        return self::ETAPES[$this->etape()][0] ?? 'Vide';
    }

    public function couleurEtape(): string
    {
        return self::ETAPES[$this->etape()][1] ?? 'secondary';
    }

    /** « 2/5 reçues » : pièces reçues (non annulées) sur le total. */
    public function avancement(): array
    {
        $pieces = $this->relationLoaded('pieces') ? $this->pieces : $this->pieces()->get(['id', 'demande_id', 'status']);
        $utiles = $pieces->where('status', '!=', VehiclePartRequest::STATUS_CANCELLED);

        return [$utiles->where('status', VehiclePartRequest::STATUS_RECEIVED)->count(), $utiles->count()];
    }

    /** Terminée : plus aucune pièce en cours (toutes reçues ou annulées). */
    public function estTerminee(): bool
    {
        $pieces = $this->relationLoaded('pieces') ? $this->pieces : $this->pieces()->get();

        return $pieces->isNotEmpty() && $pieces->every(fn (VehiclePartRequest $p) => in_array(
            $p->status,
            [VehiclePartRequest::STATUS_RECEIVED, VehiclePartRequest::STATUS_CANCELLED],
            true
        ));
    }

    public function libelleStatut(): string
    {
        return $this->estTerminee() ? 'Terminée' : 'En cours';
    }

    /** Véhicule ou dépôt de destination, pour l'affichage. */
    public function libelleDestination(): string
    {
        if ($this->vehicle) {
            return trim(($this->vehicle->plate_number ?? '') . ' ' . ($this->vehicle->brand ?? '') . ' ' . ($this->vehicle->model ?? ''));
        }

        return $this->depot?->name ?? '—';
    }
}
