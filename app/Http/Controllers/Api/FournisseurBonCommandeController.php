<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ExternalBonCommande;
use App\Models\Product;
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
                 * On utilise la position comme identifiant logique
                 * des lignes reçues depuis App Atelier.
                 */

                $lignesParPosition = $bc->lignes
                    ->keyBy('position');

                /*
                 * Les positions conservées permettront ensuite
                 * de supprimer les lignes retirées du devis.
                 */

                $positionsGardees = [];

                /*
                |--------------------------------------------------------------------------
                | TRAITEMENT DES PIÈCES
                |--------------------------------------------------------------------------
                */

                foreach (
                    array_values($data['pieces'])
                    as $position => $piece
                ) {

                    /*
                     * Nettoyage de la référence.
                     */

                    $reference = isset($piece['reference'])
                        ? trim((string) $piece['reference'])
                        : '';

                    $reference = $reference !== ''
                        ? $reference
                        : null;

                    /*
                     * Nettoyage de la désignation.
                     */

                    $designation = isset($piece['designation'])
                        ? trim((string) $piece['designation'])
                        : null;

                    if ($designation === '') {
                        $designation = null;
                    }

                    /*
                     * Quantité demandée.
                     */

                    $quantiteDemandee =
                        (float) $piece['quantite'];

                    /*
                     * Recherche d'une ligne existante
                     * à la même position.
                     */

                    $existante =
                        $lignesParPosition->get($position);

                    /*
                    |--------------------------------------------------------------------------
                    | CAS 1 :
                    | LA LIGNE EXISTE DÉJÀ ET POSSÈDE UN PRODUIT
                    |--------------------------------------------------------------------------
                    |
                    | Cela signifie que la pièce a déjà été identifiée :
                    |
                    | - automatiquement par référence ;
                    | - ou manuellement par un vendeur.
                    |
                    | On ne doit donc surtout pas supprimer product_id.
                    |
                    */

                    if (
                        $existante &&
                        $existante->product_id
                    ) {

                        /*
                         * On récupère le produit actuel.
                         *
                         * La relation peut éventuellement être null
                         * si le produit a été supprimé.
                         */

                        $product = $existante->product;

                        /*
                         * Si le produit existe encore,
                         * on recalcule le stock.
                         */

                        if ($product) {

                            $quantiteDisponible =
                                (float) ($product->quantity ?? 0);

                            $existante->update([
                                'designation' =>
                                    $designation
                                    ?? $existante->designation,

                                'quantite_demandee' =>
                                    $quantiteDemandee,

                                'quantite_disponible' =>
                                    $quantiteDisponible,

                                'disponible' =>
                                    $quantiteDisponible
                                    >= $quantiteDemandee,

                                'prix_unitaire' =>
                                    $product->sale_price,
                            ]);

                        } else {

                            /*
                             * Le product_id existe mais le produit
                             * correspondant n'existe plus.
                             */

                            $existante->update([
                                'product_id' =>
                                    null,

                                'designation' =>
                                    $designation
                                    ?? $existante->designation,

                                'quantite_demandee' =>
                                    $quantiteDemandee,

                                'quantite_disponible' =>
                                    0,

                                'disponible' =>
                                    false,

                                'prix_unitaire' =>
                                    null,
                            ]);
                        }

                        $positionsGardees[] =
                            $position;

                        continue;
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | CAS 2 :
                    | AUCUNE RÉFÉRENCE FOURNIE
                    |--------------------------------------------------------------------------
                    |
                    | Exemple :
                    |
                    | - main d'œuvre ;
                    | - peinture ;
                    | - pièce non référencée ;
                    | - article inconnu du garage.
                    |
                    | Le vendeur devra identifier la pièce manuellement.
                    |
                    */

                    if ($reference === null) {

                        $bc->lignes()->updateOrCreate(
                            [
                                'position' =>
                                    $position,
                            ],
                            [
                                'product_id' =>
                                    null,

                                'reference' =>
                                    null,

                                'designation' =>
                                    $designation,

                                'quantite_demandee' =>
                                    $quantiteDemandee,

                                'quantite_disponible' =>
                                    null,

                                'disponible' =>
                                    null,

                                'prix_unitaire' =>
                                    null,
                            ]
                        );

                        $positionsGardees[] =
                            $position;

                        continue;
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | CAS 3 :
                    | RECHERCHE AUTOMATIQUE PAR RÉFÉRENCE
                    |--------------------------------------------------------------------------
                    */

                    $product = Product::query()
                        ->where('reference', $reference)
                        ->first();

                    /*
                     * Quantité disponible.
                     */

                    $quantiteDisponible =
                        $product
                            ? (float) ($product->quantity ?? 0)
                            : 0;

                    /*
                     * Disponibilité.
                     */

                    $disponible =
                        $product !== null
                        &&
                        $quantiteDisponible
                        >= $quantiteDemandee;

                    /*
                    |--------------------------------------------------------------------------
                    | CRÉATION / MISE À JOUR DE LA LIGNE
                    |--------------------------------------------------------------------------
                    */

                    $bc->lignes()->updateOrCreate(
                        [
                            'position' =>
                                $position,
                        ],
                        [
                            'product_id' =>
                                $product?->id,

                            'reference' =>
                                $reference,

                            'designation' =>
                                $designation,

                            'quantite_demandee' =>
                                $quantiteDemandee,

                            'quantite_disponible' =>
                                $quantiteDisponible,

                            'disponible' =>
                                $disponible,

                            'prix_unitaire' =>
                                $product?->sale_price,
                        ]
                    );

                    $positionsGardees[] =
                        $position;
                }

                /*
                |--------------------------------------------------------------------------
                | SUPPRIMER LES LIGNES RETIRÉES DU DEVIS
                |--------------------------------------------------------------------------
                */

                if (count($positionsGardees) > 0) {

                    $bc->lignes()
                        ->whereNotIn(
                            'position',
                            $positionsGardees
                        )
                        ->delete();

                } else {

                    /*
                     * Normalement impossible grâce à min:1,
                     * mais sécurité supplémentaire.
                     */

                    $bc->lignes()->delete();
                }

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
}