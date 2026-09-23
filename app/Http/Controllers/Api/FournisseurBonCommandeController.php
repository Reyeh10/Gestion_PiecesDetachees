<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ExternalBonCommande;
use App\Models\Product;
use App\Models\ProductDepotStock;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Throwable;

/**
 * Contrôleur API des bons de commande fournisseur.
 *
 * Cette API reçoit les bons de commande envoyés par App Atelier.
 *
 * Fonctionnement :
 *
 * 1. App Atelier envoie un bon de commande.
 * 2. Le système crée ou met à jour le bon de commande.
 * 3. Chaque pièce est recherchée automatiquement par sa référence.
 * 4. Le système calcule :
 *      - le produit correspondant ;
 *      - la quantité disponible ;
 *      - la disponibilité ;
 *      - le prix unitaire.
 * 5. Les lignes sans référence restent en attente d'identification manuelle.
 * 6. Si le même bon est renvoyé, les lignes déjà identifiées manuellement
 *    ne sont pas écrasées.
 * 7. Les lignes supprimées du devis côté Atelier sont supprimées ici.
 */
class FournisseurBonCommandeController extends Controller
{
    /**
     * Reçoit ou met à jour un bon de commande provenant d'App Atelier.
     */
    public function store(Request $request): JsonResponse
    {
        try {

            /*
            |--------------------------------------------------------------------------
            | VALIDATION
            |--------------------------------------------------------------------------
            */

            $data = $request->validate(
                [
                    'numero' => [
                        'required',
                        'string',
                        'max:100',
                    ],

                    'vehicule' => [
                        'nullable',
                        'array',
                    ],

                    'vehicule.marque' => [
                        'nullable',
                        'string',
                        'max:255',
                    ],

                    'vehicule.modele' => [
                        'nullable',
                        'string',
                        'max:255',
                    ],

                    'vehicule.immatriculation' => [
                        'nullable',
                        'string',
                        'max:100',
                    ],

                    'vehicule.vin' => [
                        'nullable',
                        'string',
                        'max:255',
                    ],

                    'client' => [
                        'nullable',
                        'array',
                    ],

                    'client.nom' => [
                        'nullable',
                        'string',
                        'max:255',
                    ],

                    'client.telephone' => [
                        'nullable',
                        'string',
                        'max:100',
                    ],

                    'pieces' => [
                        'required',
                        'array',
                        'min:1',
                    ],

                    'pieces.*.reference' => [
                        'nullable',
                        'string',
                        'max:255',
                    ],

                    'pieces.*.designation' => [
                        'nullable',
                        'string',
                        'max:255',
                    ],

                    'pieces.*.quantite' => [
                        'required',
                        'numeric',
                        'min:0.01',
                    ],
                ],
                [
                    'numero.required' =>
                        'Le numéro du bon de commande est obligatoire.',

                    'pieces.required' =>
                        'La liste des pièces est obligatoire.',

                    'pieces.array' =>
                        'Les pièces doivent être envoyées sous forme de tableau.',

                    'pieces.min' =>
                        'Le bon de commande doit contenir au moins une pièce.',

                    'pieces.*.quantite.required' =>
                        'La quantité de chaque pièce est obligatoire.',

                    'pieces.*.quantite.numeric' =>
                        'La quantité de chaque pièce doit être numérique.',

                    'pieces.*.quantite.min' =>
                        'La quantité de chaque pièce doit être supérieure à zéro.',
                ]
            );

            /*
            |--------------------------------------------------------------------------
            | TRANSACTION
            |--------------------------------------------------------------------------
            */

            $bc = DB::transaction(function () use ($data) {

                /*
                |--------------------------------------------------------------------------
                | CRÉER OU METTRE À JOUR LE BON DE COMMANDE
                |--------------------------------------------------------------------------
                */

                $bc = ExternalBonCommande::updateOrCreate(
                    [
                        'numero' => trim($data['numero']),
                    ],
                    [
                        'source_system' =>
                            'app-atelier',

                        'vehicule_marque' =>
                            $data['vehicule']['marque'] ?? null,

                        'vehicule_modele' =>
                            $data['vehicule']['modele'] ?? null,

                        'vehicule_immatriculation' =>
                            $data['vehicule']['immatriculation'] ?? null,

                        'vehicule_vin' =>
                            $data['vehicule']['vin'] ?? null,

                        'client_nom' =>
                            $data['client']['nom'] ?? null,

                        'client_telephone' =>
                            $data['client']['telephone'] ?? null,

                        'statut' =>
                            'recu',
                    ]
                );

                /*
                |--------------------------------------------------------------------------
                | CHARGER LES LIGNES EXISTANTES
                |--------------------------------------------------------------------------
                */

                $bc->load([
                    'lignes.product',
                ]);

                /*
                 * Les lignes reçues sont rapprochées des lignes existantes
                 * par IDENTITÉ (référence, ou désignation pour les lignes
                 * sans référence) et NON par position : si le garage
                 * supprime une ligne, les positions se décalent et un
                 * rapprochement par position mélangerait références, prix
                 * et identifications manuelles entre les lignes.
                 */

                $lignesConservees = [];

                foreach (
                    array_values($data['pieces'])
                    as $position => $piece
                ) {

                    $reference = isset($piece['reference'])
                        ? trim((string) $piece['reference'])
                        : '';

                    $reference = $reference !== ''
                        ? $reference
                        : null;

                    $designation = isset($piece['designation'])
                        ? trim((string) $piece['designation'])
                        : null;

                    if ($designation === '') {
                        $designation = null;
                    }

                    $quantiteDemandee =
                        (float) $piece['quantite'];

                    $existante = $this->trouverLigneExistante(
                        $bc->lignes,
                        $lignesConservees,
                        $reference,
                        $designation,
                        $position
                    );

                    /*
                    |--------------------------------------------------------------------------
                    | LIGNE SANS RÉFÉRENCE
                    |--------------------------------------------------------------------------
                    | Le vendeur l'identifie manuellement : on ne touche pas à
                    | cette identification si la ligne est retrouvée.
                    */

                    if ($reference === null) {

                        if ($existante && $existante->product_id) {

                            $product = $existante->product;

                            if ($product) {
                                [$depotId, $quantiteDisponible, $disponible] =
                                    $this->resoudreDepot(
                                        $product->id,
                                        $quantiteDemandee,
                                        $existante->depot_id
                                    );

                                $existante->update([
                                    'position' => $position,
                                    'designation' => $designation ?? $existante->designation,
                                    'depot_id' => $depotId,
                                    'quantite_demandee' => $quantiteDemandee,
                                    'quantite_disponible' => $quantiteDisponible,
                                    'disponible' => $disponible,
                                    'prix_unitaire' => $product->sale_price,
                                ]);
                            } else {
                                $existante->update([
                                    'position' => $position,
                                    'product_id' => null,
                                    'depot_id' => null,
                                    'designation' => $designation ?? $existante->designation,
                                    'quantite_demandee' => $quantiteDemandee,
                                    'quantite_disponible' => 0,
                                    'disponible' => false,
                                    'prix_unitaire' => null,
                                ]);
                            }

                        } elseif ($existante) {

                            $existante->update([
                                'position' => $position,
                                'designation' => $designation ?? $existante->designation,
                                'quantite_demandee' => $quantiteDemandee,
                            ]);

                        } else {

                            $existante = $bc->lignes()->create([
                                'position' => $position,
                                'product_id' => null,
                                'reference' => null,
                                'designation' => $designation,
                                'quantite_demandee' => $quantiteDemandee,
                                'quantite_disponible' => null,
                                'disponible' => null,
                                'prix_unitaire' => null,
                            ]);
                        }

                        $lignesConservees[] = $existante->id;

                        continue;
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | LIGNE AVEC RÉFÉRENCE : recherche automatique du produit
                    |--------------------------------------------------------------------------
                    */

                    $product = Product::query()
                        ->where('reference', $reference)
                        ->first();

                    if ($product) {
                        [$depotId, $quantiteDisponible, $disponible] =
                            $this->resoudreDepot(
                                $product->id,
                                $quantiteDemandee,
                                $existante?->depot_id
                            );
                    } else {
                        $depotId = null;
                        $quantiteDisponible = 0;
                        $disponible = false;
                    }

                    $attributs = [
                        'position' => $position,
                        'product_id' => $product?->id,
                        'depot_id' => $depotId,
                        'reference' => $reference,
                        'designation' => $designation,
                        'quantite_demandee' => $quantiteDemandee,
                        'quantite_disponible' => $quantiteDisponible,
                        'disponible' => $disponible,
                        'prix_unitaire' => $product?->sale_price,
                    ];

                    if ($existante) {
                        $existante->update($attributs);
                    } else {
                        $existante = $bc->lignes()->create($attributs);
                    }

                    $lignesConservees[] = $existante->id;
                }

                /*
                |--------------------------------------------------------------------------
                | SUPPRIMER LES LIGNES RETIRÉES DU DEVIS
                |--------------------------------------------------------------------------
                */

                $bc->lignes()
                    ->whereNotIn('id', $lignesConservees)
                    ->delete();

                /*
                 * Retour du bon de commande
                 * hors transaction.
                 */

                return $bc;
            });

            /*
            |--------------------------------------------------------------------------
            | RECHARGER LES LIGNES APRÈS TRANSACTION
            |--------------------------------------------------------------------------
            */

            $bc->load([
                'lignes' => function ($query) {
                    $query->orderBy('position');
                },
            ]);

            /*
            |--------------------------------------------------------------------------
            | LOG
            |--------------------------------------------------------------------------
            */

            Log::info(
                'Bon de commande reçu depuis App Atelier.',
                [
                    'external_bon_commande_id' =>
                        $bc->id,

                    'numero' =>
                        $bc->numero,

                    'nombre_lignes' =>
                        $bc->lignes->count(),
                ]
            );

            /*
            |--------------------------------------------------------------------------
            | RÉPONSE JSON
            |--------------------------------------------------------------------------
            */

            return response()->json([
                'success' =>
                    true,

                'message' =>
                    'Bon de commande reçu avec succès.',

                'numero' =>
                    $bc->numero,

                'statut' =>
                    $bc->statut,

                'pieces' =>
                    $bc->lignes
                        ->sortBy('position')
                        ->values()
                        ->map(
                            function ($ligne) {

                                return [
                                    'index' =>
                                        (int) $ligne->position,

                                    'reference' =>
                                        $ligne->reference,

                                    'designation' =>
                                        $ligne->designation,

                                    'product_id' =>
                                        $ligne->product_id,

                                    'quantite_demandee' =>
                                        $ligne->quantite_demandee !== null
                                            ? (float) $ligne->quantite_demandee
                                            : null,

                                    'disponible' =>
                                        $ligne->disponible !== null
                                            ? (bool) $ligne->disponible
                                            : null,

                                    'quantite_disponible' =>
                                        $ligne->quantite_disponible !== null
                                            ? (float) $ligne->quantite_disponible
                                            : null,

                                    'prix_unitaire' =>
                                        $ligne->prix_unitaire !== null
                                            ? (float) $ligne->prix_unitaire
                                            : null,

                                    'note' =>
                                        $ligne->note,
                                ];
                            }
                        )
                        ->all(),
            ], 200);

        } catch (ValidationException $e) {

            /*
            |--------------------------------------------------------------------------
            | ERREUR DE VALIDATION
            |--------------------------------------------------------------------------
            */

            return response()->json([
                'success' =>
                    false,

                'message' =>
                    'Les données envoyées sont invalides.',

                'errors' =>
                    $e->errors(),
            ], 422);

        } catch (Throwable $e) {

            /*
            |--------------------------------------------------------------------------
            | ERREUR INTERNE
            |--------------------------------------------------------------------------
            */

            Log::error(
                'Erreur lors de la réception du bon de commande App Atelier.',
                [
                    'numero' =>
                        $request->input('numero'),

                    'message' =>
                        $e->getMessage(),

                    'file' =>
                        $e->getFile(),

                    'line' =>
                        $e->getLine(),
                ]
            );

            return response()->json([
                'success' =>
                    false,

                'message' =>
                    'Une erreur est survenue lors du traitement du bon de commande.',
            ], 500);
        }
    }

    /**
     * Retrouve la ligne existante correspondant à une pièce reçue :
     * même référence, ou (sans référence) même désignation. Les lignes déjà
     * rapprochées sont exclues ; en cas de doublons, la plus proche en
     * position est préférée.
     */
    private function trouverLigneExistante($lignes, array $dejaRapprochees, ?string $reference, ?string $designation, int $position)
    {
        $norm = fn ($v) => mb_strtolower(trim((string) $v));

        $candidates = $lignes
            ->reject(fn ($l) => in_array($l->id, $dejaRapprochees, true))
            ->filter(fn ($l) => $reference !== null
                ? $norm($l->reference) === $norm($reference)
                : ($l->reference === null && $norm($l->designation) === $norm($designation)));

        return $candidates->firstWhere('position', $position) ?? $candidates->first();
    }

    /**
     * Détermine le dépôt, la quantité disponible et la disponibilité d'une
     * pièce identifiée, en tenant compte du stock par dépôt.
     *
     * - Aucun dépôt en stock                  -> [null, 0, false]
     * - Un seul dépôt en stock                -> ce dépôt, sa quantité, dispo si suffisant
     * - Plusieurs dépôts en stock             -> le dépôt déjà choisi s'il est encore
     *   valable, sinon [null, quantité max, false] : le vendeur doit choisir.
     *
     * @return array{0:?int,1:float,2:bool} [depot_id, quantite_disponible, disponible]
     */
    private function resoudreDepot(int $productId, float $quantiteDemandee, ?int $depotPrefere = null): array
    {
        $stocks = ProductDepotStock::query()
            ->where('product_id', $productId)
            ->where('quantity', '>', 0)
            ->orderByDesc('quantity')
            ->get();

        if ($stocks->isEmpty()) {
            return [null, 0.0, false];
        }

        if ($stocks->count() === 1) {
            $stock = $stocks->first();
            $qte = (float) $stock->quantity;

            return [(int) $stock->depot_id, $qte, $qte >= $quantiteDemandee];
        }

        // Plusieurs dépôts : on conserve le dépôt déjà retenu s'il tient encore.
        if ($depotPrefere) {
            $choisi = $stocks->firstWhere('depot_id', $depotPrefere);

            if ($choisi) {
                $qte = (float) $choisi->quantity;

                return [(int) $choisi->depot_id, $qte, $qte >= $quantiteDemandee];
            }
        }

        // Le vendeur devra choisir le dépôt sur la page du bon de commande.
        return [null, (float) $stocks->first()->quantity, false];
    }
}
