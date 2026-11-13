<?php

namespace App\Http\Controllers;

use App\Models\Depot;
use App\Models\Supplier;
use App\Models\SupplierOrder;
use App\Models\SupplierOrderItem;
use App\Models\User;
use App\Models\SupplierOrderSignature;
use App\Models\VehiclePartRequest;
use App\Notifications\NewSupplierOrderNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class SupplierOrderController extends Controller
{
        /**
     * Récupérer l'utilisateur connecté avec son type User.
     *
     * Permet à Intelephense de reconnaître les méthodes
     * personnalisées : hasPermission(), isAdmin(), etc.
     */
    private function utilisateurConnecte(): User
    {
        $user = auth()->user();

        abort_unless($user instanceof User, 401);

        return $user;
    }
    /*
    |--------------------------------------------------------------------------
    | LISTE DES BONS DE COMMANDE
    |--------------------------------------------------------------------------
    */

    public function index(Request $request): View
    {
        $query = SupplierOrder::query()
            ->with([
                'supplier',
                'depot',
                'creator',
                'approver',
            ])
            ->withCount('items');

        /*
        |--------------------------------------------------------------------------
        | RECHERCHE
        |--------------------------------------------------------------------------
        */

        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));

            $query->where(function ($subQuery) use ($search) {
                $subQuery
                    ->where(
                        'order_number',
                        'like',
                        "%{$search}%"
                    )
                    ->orWhereHas(
                        'supplier',
                        function ($supplierQuery) use ($search) {
                            $supplierQuery
                                ->where(
                                    'name',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhere(
                                    'code',
                                    'like',
                                    "%{$search}%"
                                );
                        }
                    );
            });
        }

        /*
        |--------------------------------------------------------------------------
        | FILTRE PAR STATUT
        |--------------------------------------------------------------------------
        */

        if ($request->filled('status')) {
            $query->where(
                'status',
                $request->input('status')
            );
        }

        /*
        |--------------------------------------------------------------------------
        | FILTRE PAR FOURNISSEUR
        |--------------------------------------------------------------------------
        */

        if ($request->filled('supplier_id')) {
            $query->where(
                'supplier_id',
                $request->input('supplier_id')
            );
        }

        // V7 : « À signer » (ma signature attendue) et « En retard »
        if ($request->query('vue') === 'a-signer' && auth()->user()) {
            $query->whereIn('id', self::requeteASigner(auth()->user())->select('id'));
        }

        if ($request->query('vue') === 'en-retard') {
            $query->whereIn('status', SupplierOrder::STATUTS_RECEPTION)
                ->whereNotNull('expected_delivery_date')
                ->whereDate('expected_delivery_date', '<', today());
        }

        /*
        |--------------------------------------------------------------------------
        | PAGINATION
        |--------------------------------------------------------------------------
        */

        $supplierOrders = $query
            ->latest('order_date')
            ->latest('id')
            ->paginate(15);

        $supplierOrders->appends(
            $request->query()
        );

        /*
        |--------------------------------------------------------------------------
        | FOURNISSEURS
        |--------------------------------------------------------------------------
        */

        $suppliers = Supplier::query()
            ->orderBy('name')
            ->get();

        return view(
            'supplier-orders.index',
            compact(
                'supplierOrders',
                'suppliers'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | FORMULAIRE DE CRÉATION DEPUIS UNE DEMANDE DE PIÈCE
    |--------------------------------------------------------------------------
    */

    public function createFromPartRequest(
        VehiclePartRequest $vehiclePartRequest
    ): View|RedirectResponse {

        $vehiclePartRequest->load([
            'vehicle.customer',

            /*
            |--------------------------------------------------------------------------
            | DESTINATION DÉPÔT
            |--------------------------------------------------------------------------
            |
            | Une demande de pièce peut maintenant être destinée soit
            | à un véhicule, soit directement à un dépôt.
            |
            */

            'depot',

            'product',
            'supplier',
            'latestSupplierOrderItem.supplierOrder',
        ]);

        /*
        |--------------------------------------------------------------------------
        | STATUT AUTORISÉ
        |--------------------------------------------------------------------------
        |
        | RÈGLE MÉTIER :
        |
        | Un nouveau bon de commande fournisseur peut être généré
        | UNIQUEMENT lorsque la demande de pièce est au statut COMMANDÉE.
        |
        | Aucun BC ne doit être généré pour :
        |
        | - EN RECHERCHE
        | - RÉCEPTION PARTIELLE
        | - REÇUE
        | - NON TROUVÉE
        | - ANNULÉE
        |
        |--------------------------------------------------------------------------
        */

        if (
            $vehiclePartRequest->status
            !==
            VehiclePartRequest::STATUS_ORDERED
        ) {
            return redirect()
                ->route('vehicle-part-requests.ordered')
                ->with(
                    'error',
                    'Un bon de commande ne peut être généré que lorsque la pièce est au statut COMMANDÉE.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | BC DÉJÀ EXISTANT
        |--------------------------------------------------------------------------
        */

        if (
            $vehiclePartRequest->latestSupplierOrderItem
            &&
            $vehiclePartRequest
                ->latestSupplierOrderItem
                ->supplierOrder
        ) {
            return redirect()
                ->route(
                    'supplier-orders.show',
                    $vehiclePartRequest
                        ->latestSupplierOrderItem
                        ->supplierOrder
                )
                ->with(
                    'info',
                    'Cette pièce appartient déjà à un bon de commande.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | FOURNISSEUR OBLIGATOIRE
        |--------------------------------------------------------------------------
        */

        if (!$vehiclePartRequest->supplier_id) {
            return redirect()
                ->route(
                    'vehicle-part-requests.edit',
                    $vehiclePartRequest
                )
                ->with(
                    'error',
                    'Veuillez sélectionner un fournisseur avant de générer le bon de commande.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | LISTE DES FOURNISSEURS
        |--------------------------------------------------------------------------
        */

        $suppliers = Supplier::query()
            ->orderBy('name')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | LISTE DES DÉPÔTS ACTIFS
        |--------------------------------------------------------------------------
        */

        $depots = Depot::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view(
            'supplier-orders.create',
            compact(
                'vehiclePartRequest',
                'suppliers',
                'depots'
            )
        );
    }

    /*
|--------------------------------------------------------------------------
| FORMULAIRE DE CRÉATION DEPUIS PLUSIEURS DEMANDES DE PIÈCES
|--------------------------------------------------------------------------
|
| Cette méthode permet de créer UN SEUL bon de commande contenant
| plusieurs pièces.
|
| RÈGLES :
|
| - au moins une pièce doit être sélectionnée ;
| - toutes les pièces doivent être au statut COMMANDÉE ;
| - toutes les pièces doivent avoir un fournisseur ;
| - toutes les pièces doivent appartenir au MÊME fournisseur ;
| - aucune pièce ne doit déjà appartenir à un BC actif.
|
|--------------------------------------------------------------------------
*/

public function createFromPartRequests(Request $request): View|RedirectResponse
{
    /*
    |--------------------------------------------------------------------------
    | RÉCUPÉRER LES IDENTIFIANTS
    |--------------------------------------------------------------------------
    */

    $validated = $request->validate([
        'vehicle_part_request_ids' => [
            'required',
            'array',
            'min:1',
        ],

        'vehicle_part_request_ids.*' => [
            'required',
            'integer',
            'distinct',
            'exists:vehicle_part_requests,id',
        ],
    ]);

    $ids = array_values(
        array_unique(
            array_map(
                'intval',
                $validated['vehicle_part_request_ids']
            )
        )
    );


    /*
    |--------------------------------------------------------------------------
    | CHARGER LES DEMANDES
    |--------------------------------------------------------------------------
    */

    $vehiclePartRequests =
        VehiclePartRequest::query()
            ->with([
                'vehicle.customer',
                'product',
                'supplier',
                'latestSupplierOrderItem.supplierOrder',
            ])
            ->whereIn('id', $ids)
            ->get();


    /*
    |--------------------------------------------------------------------------
    | VÉRIFIER QUE TOUTES LES PIÈCES EXISTENT
    |--------------------------------------------------------------------------
    */

    if ($vehiclePartRequests->count() !== count($ids)) {

        return redirect()
            ->route('vehicle-part-requests.ordered')
            ->with(
                'error',
                'Une ou plusieurs pièces sélectionnées sont introuvables.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | STATUT COMMANDÉE OBLIGATOIRE
    |--------------------------------------------------------------------------
    */

    $invalidStatus =
        $vehiclePartRequests->first(
            function ($partRequest) {
                return
                    $partRequest->status
                    !==
                    VehiclePartRequest::STATUS_ORDERED;
            }
        );

    if ($invalidStatus) {

        return redirect()
            ->route('vehicle-part-requests.ordered')
            ->with(
                'error',
                'Toutes les pièces sélectionnées doivent être au statut COMMANDÉE.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | FOURNISSEUR OBLIGATOIRE
    |--------------------------------------------------------------------------
    */

    $withoutSupplier =
        $vehiclePartRequests->first(
            function ($partRequest) {
                return !$partRequest->supplier_id;
            }
        );

    if ($withoutSupplier) {

        return redirect()
            ->route(
                'vehicle-part-requests.edit',
                $withoutSupplier
            )
            ->with(
                'error',
                'Toutes les pièces doivent avoir un fournisseur avant la création du bon de commande.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | UN SEUL FOURNISSEUR PAR BC
    |--------------------------------------------------------------------------
    */

    $supplierIds =
        $vehiclePartRequests
            ->pluck('supplier_id')
            ->map(
                fn ($supplierId) =>
                    (int) $supplierId
            )
            ->unique()
            ->values();

    if ($supplierIds->count() !== 1) {

        return redirect()
            ->route('vehicle-part-requests.ordered')
            ->with(
                'error',
                'Les pièces sélectionnées appartiennent à plusieurs fournisseurs. Un bon de commande ne peut concerner qu’un seul fournisseur.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | EMPÊCHER UNE PIÈCE DÉJÀ DANS UN BC ACTIF
    |--------------------------------------------------------------------------
    */

    foreach ($vehiclePartRequests as $partRequest) {

        $existingItem =
            SupplierOrderItem::query()
                ->where(
                    'vehicle_part_request_id',
                    $partRequest->id
                )
                ->whereHas(
                    'supplierOrder',
                    function ($query) {
                        $query->where(
                            'status',
                            '!=',
                            SupplierOrder::STATUS_CANCELLED
                        );
                    }
                )
                ->first();

        if ($existingItem) {

            return redirect()
                ->route('vehicle-part-requests.ordered')
                ->with(
                    'error',
                    'La pièce « '
                    . (
                        $partRequest->part_name
                        ?? $partRequest->reference
                        ?? ('#' . $partRequest->id)
                    )
                    . ' » appartient déjà à un bon de commande actif.'
                );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | FOURNISSEUR DU BC
    |--------------------------------------------------------------------------
    */

    $supplier =
        $vehiclePartRequests
            ->first()
            ->supplier;


    /*
    |--------------------------------------------------------------------------
    | LISTE DES FOURNISSEURS
    |--------------------------------------------------------------------------
    */

    $suppliers =
        Supplier::query()
            ->orderBy('name')
            ->get();


    /*
    |--------------------------------------------------------------------------
    | LISTE DES DÉPÔTS ACTIFS
    |--------------------------------------------------------------------------
    */

    $depots =
        Depot::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();


    /*
    |--------------------------------------------------------------------------
    | AFFICHER LE FORMULAIRE MULTI-PIÈCES
    |--------------------------------------------------------------------------
    */

    return view(
        'supplier-orders.create-multiple',
        compact(
            'vehiclePartRequests',
            'supplier',
            'suppliers',
            'depots'
        )
    );
}


    /*
    |--------------------------------------------------------------------------
    | ENREGISTRER LE BON DE COMMANDE
    |--------------------------------------------------------------------------
    |
    | IMPORTANT :
    |
    | Cette méthode ne touche JAMAIS :
    |
    | - products.quantity
    | - product_depot_stocks
    | - stock_movements
    | - CUMP
    |
    | Elle crée uniquement le document commercial.
    |
    | Le stock sera modifié uniquement lors de la réception effective
    | de la marchandise.
    |
    |--------------------------------------------------------------------------
    */

   public function store(Request $request): RedirectResponse
{
    /*
    |--------------------------------------------------------------------------
    | DÉTERMINER LE MODE DE CRÉATION
    |--------------------------------------------------------------------------
    |
    | Le système accepte maintenant deux formats :
    |
    | 1. ANCIEN MODE / UNE SEULE PIÈCE
    |
    |    vehicle_part_request_id
    |    quantity_ordered
    |    unit_price
    |
    | 2. NOUVEAU MODE / PLUSIEURS PIÈCES
    |
    |    items[0][vehicle_part_request_id]
    |    items[0][quantity_ordered]
    |    items[0][unit_price]
    |
    |    items[1][vehicle_part_request_id]
    |    items[1][quantity_ordered]
    |    items[1][unit_price]
    |
    | Cela permet de conserver totalement le fonctionnement historique
    | tout en permettant de créer un seul BC avec plusieurs lignes.
    |
    |--------------------------------------------------------------------------
    */

    $isMultiple =
        $request->has('items')
        &&
        is_array($request->input('items'));


    /*
    |--------------------------------------------------------------------------
    | VALIDATION COMMUNE
    |--------------------------------------------------------------------------
    */

    $commonRules = [

        'supplier_id' => [
            'required',
            'integer',
            'exists:suppliers,id',
        ],

        'depot_id' => [
            'required',
            'integer',
            'exists:depots,id',
        ],

        'order_date' => [
            'required',
            'date',
        ],

        'expected_delivery_date' => [
            'nullable',
            'date',
            'after_or_equal:order_date',
        ],

        'currency' => [
            'required',
            'string',
            'max:10',
        ],

        'discount_rate' => [
            'nullable',
            'numeric',
            'min:0',
            'max:100',
        ],

        'shipping_cost' => [
            'nullable',
            'numeric',
            'min:0',
        ],

        'tax_rate' => [
            'nullable',
            'numeric',
            'min:0',
            'max:100',
        ],

        'payment_terms' => [
            'nullable',
            'string',
            'max:255',
        ],

        'delivery_terms' => [
            'nullable',
            'string',
            'max:255',
        ],

        'notes' => [
            'nullable',
            'string',
        ],
    ];


    /*
    |--------------------------------------------------------------------------
    | VALIDATION DES LIGNES
    |--------------------------------------------------------------------------
    */

    if ($isMultiple) {

        /*
        |--------------------------------------------------------------------------
        | PLUSIEURS PIÈCES
        |--------------------------------------------------------------------------
        */

        $itemRules = [

            'items' => [
                'required',
                'array',
                'min:1',
            ],

            'items.*.vehicle_part_request_id' => [
                'required',
                'integer',
                'distinct',
                'exists:vehicle_part_requests,id',
            ],

            'items.*.quantity_ordered' => [
                'required',
                'numeric',
                'gt:0',
            ],

            'items.*.unit_price' => [
                'required',
                'numeric',
                'min:0',
            ],
        ];

    } else {

        /*
        |--------------------------------------------------------------------------
        | UNE SEULE PIÈCE
        |--------------------------------------------------------------------------
        |
        | Nous conservons exactement les noms des anciens champs.
        |
        */

        $itemRules = [

            'vehicle_part_request_id' => [
                'required',
                'integer',
                'exists:vehicle_part_requests,id',
            ],

            'quantity_ordered' => [
                'required',
                'numeric',
                'gt:0',
            ],

            'unit_price' => [
                'required',
                'numeric',
                'min:0',
            ],
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | VALIDATION DU FORMULAIRE
    |--------------------------------------------------------------------------
    */

    $validated =
        $request->validate(
            array_merge(
                $commonRules,
                $itemRules
            )
        );


    /*
    |--------------------------------------------------------------------------
    | NORMALISER LES LIGNES
    |--------------------------------------------------------------------------
    |
    | À partir d'ici, le reste du code travaille toujours avec :
    |
    | $items = [
    |     [
    |         vehicle_part_request_id,
    |         quantity_ordered,
    |         unit_price,
    |     ],
    |     ...
    | ];
    |
    |--------------------------------------------------------------------------
    */

    if ($isMultiple) {

        $items =
            array_values(
                $validated['items']
            );

    } else {

        $items = [
            [
                'vehicle_part_request_id' =>
                    $validated[
                        'vehicle_part_request_id'
                    ],

                'quantity_ordered' =>
                    $validated[
                        'quantity_ordered'
                    ],

                'unit_price' =>
                    $validated[
                        'unit_price'
                    ],
            ],
        ];
    }


    try {

        /*
        |--------------------------------------------------------------------------
        | TRANSACTION
        |--------------------------------------------------------------------------
        */

        $supplierOrder =
            DB::transaction(
                function () use (
                    $validated,
                    $items
                ) {

                    /*
                    |--------------------------------------------------------------------------
                    | IDENTIFIANTS DES DEMANDES
                    |--------------------------------------------------------------------------
                    */

                    $partRequestIds =
                        collect($items)
                            ->pluck(
                                'vehicle_part_request_id'
                            )
                            ->map(
                                fn ($id) => (int) $id
                            )
                            ->unique()
                            ->values()
                            ->all();


                    /*
                    |--------------------------------------------------------------------------
                    | VERROUILLER TOUTES LES DEMANDES
                    |--------------------------------------------------------------------------
                    |
                    | Toutes les pièces sont verrouillées dans la même transaction.
                    |
                    | Cela empêche deux utilisateurs de générer simultanément
                    | deux BC pour une même pièce.
                    |
                    |--------------------------------------------------------------------------
                    */

                    $partRequests =
                        VehiclePartRequest::query()
                            ->with([
                                'product',
                                'supplier',
                            ])
                            ->whereIn(
                                'id',
                                $partRequestIds
                            )
                            ->orderBy('id')
                            ->lockForUpdate()
                            ->get()
                            ->keyBy('id');


                    /*
                    |--------------------------------------------------------------------------
                    | VÉRIFIER QUE TOUTES LES DEMANDES EXISTENT
                    |--------------------------------------------------------------------------
                    */

                    if (
                        $partRequests->count()
                        !==
                        count($partRequestIds)
                    ) {

                        throw ValidationException::withMessages([
                            'items' =>
                                'Une ou plusieurs pièces sélectionnées sont introuvables.',
                        ]);
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | VÉRIFIER CHAQUE PIÈCE
                    |--------------------------------------------------------------------------
                    */

                    foreach ($items as $index => $item) {

                        $partRequestId =
                            (int) $item[
                                'vehicle_part_request_id'
                            ];

                        $partRequest =
                            $partRequests->get(
                                $partRequestId
                            );


                        /*
                        |--------------------------------------------------------------------------
                        | STATUT COMMANDÉE OBLIGATOIRE
                        |--------------------------------------------------------------------------
                        |
                        | SÉCURITÉ SERVEUR :
                        |
                        | Même si quelqu'un modifie manuellement le formulaire,
                        | une pièce qui n'est plus COMMANDÉE ne peut pas entrer
                        | dans le bon de commande.
                        |
                        |--------------------------------------------------------------------------
                        */

                        if (
                            ! in_array(
                                $partRequest->status,
                                [VehiclePartRequest::STATUS_ORDERED, VehiclePartRequest::STATUS_TO_ORDER],
                                true
                            )
                        ) {

                            throw ValidationException::withMessages([
                                "items.$index.vehicle_part_request_id" =>
                                    'La pièce « '
                                    . (
                                        $partRequest->part_name
                                        ?? $partRequest->reference
                                        ?? ('#' . $partRequest->id)
                                    )
                                    . ' » n’est plus au statut COMMANDÉE.',
                            ]);
                        }


                        /*
                        |--------------------------------------------------------------------------
                        | FOURNISSEUR OBLIGATOIRE
                        |--------------------------------------------------------------------------
                        */

                        if (!$partRequest->supplier_id) {

                            throw ValidationException::withMessages([
                                'supplier_id' =>
                                    'La pièce « '
                                    . (
                                        $partRequest->part_name
                                        ?? $partRequest->reference
                                        ?? ('#' . $partRequest->id)
                                    )
                                    . ' » n’a aucun fournisseur associé.',
                            ]);
                        }


                        /*
                        |--------------------------------------------------------------------------
                        | UN SEUL FOURNISSEUR POUR TOUT LE BC
                        |--------------------------------------------------------------------------
                        |
                        | Chaque pièce doit appartenir au fournisseur sélectionné.
                        |
                        |--------------------------------------------------------------------------
                        */

                        if (
                            (int) $partRequest->supplier_id
                            !==
                            (int) $validated['supplier_id']
                        ) {

                            throw ValidationException::withMessages([
                                'supplier_id' =>
                                    'Toutes les pièces du bon de commande doivent appartenir au même fournisseur.',
                            ]);
                        }


                        /*
                        |--------------------------------------------------------------------------
                        | EMPÊCHER UN DEUXIÈME BC ACTIF
                        |--------------------------------------------------------------------------
                        |
                        | Un BC annulé n'empêche pas la création d'un nouveau BC.
                        |
                        |--------------------------------------------------------------------------
                        */

                        $existingItem =
                            SupplierOrderItem::query()
                                ->where(
                                    'vehicle_part_request_id',
                                    $partRequest->id
                                )
                                ->whereHas(
                                    'supplierOrder',
                                    function ($query) {

                                        $query->where(
                                            'status',
                                            '!=',
                                            SupplierOrder::STATUS_CANCELLED
                                        );
                                    }
                                )
                                ->first();

                        if ($existingItem) {

                            throw ValidationException::withMessages([
                                "items.$index.vehicle_part_request_id" =>
                                    'La pièce « '
                                    . (
                                        $partRequest->part_name
                                        ?? $partRequest->reference
                                        ?? ('#' . $partRequest->id)
                                    )
                                    . ' » appartient déjà à un bon de commande actif.',
                            ]);
                        }
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | SÉCURISER LE DÉPÔT DE DESTINATION
                    |--------------------------------------------------------------------------
                    |
                    | Une demande de pièce peut être destinée :
                    |
                    | 1. directement à un DÉPÔT ;
                    | 2. à un VÉHICULE.
                    |
                    | Pour une demande destinée directement à un dépôt,
                    | le dépôt enregistré dans vehicle_part_requests.depot_id
                    | est prioritaire et ne peut jamais être remplacé par
                    | une valeur envoyée manuellement depuis le formulaire.
                    |
                    | Pour un BC contenant plusieurs pièces destinées à des dépôts,
                    | toutes les demandes doivent appartenir au même dépôt.
                    |
                    |--------------------------------------------------------------------------
                    */

                    $forcedDepotIds =
                        $partRequests
                            ->pluck('depot_id')
                            ->filter(
                                fn ($depotId) =>
                                    !is_null($depotId)
                            )
                            ->map(
                                fn ($depotId) =>
                                    (int) $depotId
                            )
                            ->unique()
                            ->values();


                    /*
                    |--------------------------------------------------------------------------
                    | PLUSIEURS DÉPÔTS DIFFÉRENTS = BC INTERDIT
                    |--------------------------------------------------------------------------
                    */

                    if ($forcedDepotIds->count() > 1) {

                        throw ValidationException::withMessages([
                            'depot_id' =>
                                'Les pièces sélectionnées sont destinées à plusieurs dépôts différents. '
                                . 'Elles ne peuvent pas être regroupées dans le même bon de commande.',
                        ]);
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | DÉTERMINER LE DÉPÔT FINAL DU BC
                    |--------------------------------------------------------------------------
                    */

                    if ($forcedDepotIds->count() === 1) {

                        /*
                        |--------------------------------------------------------------------------
                        | DESTINATION IMPOSÉE PAR LA DEMANDE
                        |--------------------------------------------------------------------------
                        |
                        | Au moins une demande possède depot_id.
                        | Le serveur impose donc ce dépôt.
                        |
                        */

                        $supplierOrderDepotId =
                            (int) $forcedDepotIds->first();


                        /*
                        |--------------------------------------------------------------------------
                        | EMPÊCHER LE MÉLANGE DÉPÔT / VÉHICULE
                        |--------------------------------------------------------------------------
                        |
                        | Si le BC contient plusieurs lignes et qu'une demande est
                        | destinée à un dépôt alors que l'autre est destinée à un
                        | véhicule, nous refusons le regroupement.
                        |
                        */

                        $containsVehicleDestination =
                            $partRequests->contains(
                                function ($partRequest) {
                                    return
                                        is_null($partRequest->depot_id)
                                        &&
                                        !is_null($partRequest->vehicle_id);
                                }
                            );

                        if ($containsVehicleDestination) {

                            throw ValidationException::withMessages([
                                'depot_id' =>
                                    'Un même bon de commande ne peut pas mélanger '
                                    . 'des pièces destinées directement à un dépôt '
                                    . 'et des pièces destinées à des véhicules.',
                            ]);
                        }

                    } else {

                        /*
                        |--------------------------------------------------------------------------
                        | DEMANDES DESTINÉES AUX VÉHICULES
                        |--------------------------------------------------------------------------
                        |
                        | Aucun dépôt n'est imposé par les demandes.
                        | Nous conservons donc le dépôt de réception sélectionné
                        | dans le formulaire.
                        |
                        */

                        $supplierOrderDepotId =
                            (int) $validated['depot_id'];
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | PRÉPARER LES LIGNES DU BC
                    |--------------------------------------------------------------------------
                    */

                    $preparedItems = [];

                    $subtotal = 0;


                    foreach ($items as $item) {

                        $partRequestId =
                            (int) $item[
                                'vehicle_part_request_id'
                            ];

                        $partRequest =
                            $partRequests->get(
                                $partRequestId
                            );


                        /*
                        |--------------------------------------------------------------------------
                        | QUANTITÉ COMMANDÉE
                        |--------------------------------------------------------------------------
                        */

                        $quantityOrdered =
                            round(
                                (float) $item[
                                    'quantity_ordered'
                                ],
                                2
                            );


                        /*
                        |--------------------------------------------------------------------------
                        | QUANTITÉ DÉJÀ REÇUE
                        |--------------------------------------------------------------------------
                        |
                        | Normalement une pièce COMMANDÉE ne devrait pas encore
                        | avoir de réception.
                        |
                        | Nous conservons néanmoins la valeur existante afin de
                        | préserver les éventuelles données historiques.
                        |
                        |--------------------------------------------------------------------------
                        */

                        $alreadyReceived =
                            min(
                                $quantityOrdered,
                                max(
                                    0,
                                    (float) (
                                        $partRequest
                                            ->received_quantity
                                        ?? 0
                                    )
                                )
                            );


                        /*
                        |--------------------------------------------------------------------------
                        | PRIX UNITAIRE
                        |--------------------------------------------------------------------------
                        */

                        $unitPrice =
                            round(
                                (float) $item[
                                    'unit_price'
                                ],
                                4
                            );


                        /*
                        |--------------------------------------------------------------------------
                        | TOTAL DE LA LIGNE
                        |--------------------------------------------------------------------------
                        */

                        $lineTotal =
                            round(
                                $quantityOrdered
                                *
                                $unitPrice,
                                2
                            );


                        /*
                        |--------------------------------------------------------------------------
                        | AJOUT AU SOUS-TOTAL GLOBAL
                        |--------------------------------------------------------------------------
                        */

                        $subtotal =
                            round(
                                $subtotal
                                + $lineTotal,
                                2
                            );


                        /*
                        |--------------------------------------------------------------------------
                        | PRÉPARER LE SNAPSHOT
                        |--------------------------------------------------------------------------
                        */

                        $preparedItems[] = [

                            'part_request' =>
                                $partRequest,

                            'quantity_ordered' =>
                                $quantityOrdered,

                            'quantity_received' =>
                                $alreadyReceived,

                            'unit_price' =>
                                $unitPrice,

                            'line_total' =>
                                $lineTotal,
                        ];
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | REMISE EN POURCENTAGE
                    |--------------------------------------------------------------------------
                    |
                    | La remise est maintenant calculée sur le sous-total de
                    | TOUTES les pièces du bon de commande.
                    |
                    |--------------------------------------------------------------------------
                    */

                    $discountRate =
                        max(
                            0,
                            min(
                                100,
                                round(
                                    (float) (
                                        $validated[
                                            'discount_rate'
                                        ]
                                        ?? 0
                                    ),
                                    2
                                )
                            )
                        );

                    $discountAmount =
                        round(
                            $subtotal
                            * $discountRate
                            / 100,
                            2
                        );


                    /*
                    |--------------------------------------------------------------------------
                    | FRAIS DE TRANSPORT
                    |--------------------------------------------------------------------------
                    */

                    $shippingCost =
                        round(
                            max(
                                0,
                                (float) (
                                    $validated[
                                        'shipping_cost'
                                    ]
                                    ?? 0
                                )
                            ),
                            2
                        );


                    /*
                    |--------------------------------------------------------------------------
                    | TAXE / TVA EN POURCENTAGE
                    |--------------------------------------------------------------------------
                    */

                    $taxRate =
                        max(
                            0,
                            min(
                                100,
                                round(
                                    (float) (
                                        $validated[
                                            'tax_rate'
                                        ]
                                        ?? 0
                                    ),
                                    2
                                )
                            )
                        );


                    /*
                    |--------------------------------------------------------------------------
                    | BASE APRÈS REMISE
                    |--------------------------------------------------------------------------
                    */

                    $afterDiscount =
                        max(
                            0,
                            $subtotal
                            - $discountAmount
                        );


                    /*
                    |--------------------------------------------------------------------------
                    | BASE TAXABLE
                    |--------------------------------------------------------------------------
                    */

                    $taxableAmount =
                        $afterDiscount
                        + $shippingCost;


                    /*
                    |--------------------------------------------------------------------------
                    | MONTANT DE LA TAXE
                    |--------------------------------------------------------------------------
                    */

                    $taxAmount =
                        round(
                            $taxableAmount
                            * $taxRate
                            / 100,
                            2
                        );


                    /*
                    |--------------------------------------------------------------------------
                    | TOTAL DU BC
                    |--------------------------------------------------------------------------
                    */

                    $total =
                        max(
                            0,
                            round(
                                $taxableAmount
                                + $taxAmount,
                                2
                            )
                        );


                    /*
                    |--------------------------------------------------------------------------
                    | NUMÉRO DU BC
                    |--------------------------------------------------------------------------
                    */

                    $orderNumber =
                        SupplierOrder::generateOrderNumber();


                    /*
                    |--------------------------------------------------------------------------
                    | CRÉATION D'UN SEUL BC
                    |--------------------------------------------------------------------------
                    */

                    $supplierOrder =
                        SupplierOrder::create([

                            'order_number' =>
                                $orderNumber,

                            'supplier_id' =>
                                $validated['supplier_id'],

                            /*
                            |--------------------------------------------------------------------------
                            | DÉPÔT SÉCURISÉ
                            |--------------------------------------------------------------------------
                            |
                            | Ne jamais utiliser directement depot_id provenant
                            | du formulaire lorsqu'une demande impose déjà
                            | son propre dépôt.
                            |
                            */

                            'depot_id' =>
                                $supplierOrderDepotId,

                            'created_by' =>
                                auth()->id(),

                            'approved_by' =>
                                null,

                            'order_date' =>
                                $validated['order_date'],

                            'expected_delivery_date' =>
                                $validated[
                                    'expected_delivery_date'
                                ]
                                ?? null,

                            'approved_at' =>
                                null,

                            'sent_at' =>
                                null,

                            'status' =>
                                SupplierOrder::STATUS_DRAFT,

                            'currency' =>
                                strtoupper(
                                    trim(
                                        $validated['currency']
                                    )
                                ),

                            /*
                            |--------------------------------------------------------------------------
                            | TOTAL DE TOUTES LES LIGNES
                            |--------------------------------------------------------------------------
                            */

                            'subtotal' =>
                                $subtotal,

                            'discount_rate' =>
                                $discountRate,

                            'discount' =>
                                $discountAmount,

                            'shipping_cost' =>
                                $shippingCost,

                            'tax_rate' =>
                                $taxRate,

                            'tax_amount' =>
                                $taxAmount,

                            'total' =>
                                $total,

                            'payment_terms' =>
                                $validated[
                                    'payment_terms'
                                ]
                                ?? null,

                            'delivery_terms' =>
                                $validated[
                                    'delivery_terms'
                                ]
                                ?? null,

                            'notes' =>
                                $validated['notes']
                                ?? null,
                        ]);


                    /*
                    |--------------------------------------------------------------------------
                    | CRÉER TOUTES LES LIGNES DU BC
                    |--------------------------------------------------------------------------
                    */

                    foreach (
                        $preparedItems
                        as $preparedItem
                    ) {

                        $partRequest =
                            $preparedItem[
                                'part_request'
                            ];


                        /*
                        |--------------------------------------------------------------------------
                        | SNAPSHOT DE LA PIÈCE
                        |--------------------------------------------------------------------------
                        |
                        | Les informations de la pièce sont enregistrées telles
                        | qu'elles existent au moment de la création du BC.
                        |
                        |--------------------------------------------------------------------------
                        */

                        SupplierOrderItem::create([

                            'supplier_order_id' =>
                                $supplierOrder->id,

                            'vehicle_part_request_id' =>
                                $partRequest->id,

                            'product_id' =>
                                $partRequest->product_id,

                            'reference' =>
                                $partRequest->reference
                                ??
                                $partRequest
                                    ->product
                                    ?->reference,

                            'description' =>
                                $partRequest->part_name
                                ??
                                $partRequest
                                    ->product
                                    ?->designation
                                ??
                                'Pièce',

                            'unit' =>
                                $partRequest->unit
                                ??
                                $partRequest
                                    ->product
                                    ?->unit_label,

                            'quantity_ordered' =>
                                $preparedItem[
                                    'quantity_ordered'
                                ],

                            'quantity_received' =>
                                $preparedItem[
                                    'quantity_received'
                                ],

                            'unit_price' =>
                                $preparedItem[
                                    'unit_price'
                                ],

                            'line_total' =>
                                $preparedItem[
                                    'line_total'
                                ],

                            'notes' =>
                                $partRequest->notes,
                        ]);


                        /*
                        |--------------------------------------------------------------------------
                        | RÉFÉRENCE DU BC DANS CHAQUE DEMANDE
                        |--------------------------------------------------------------------------
                        |
                        | Toutes les pièces de ce BC reçoivent le même numéro
                        | de bon de commande.
                        |
                        |--------------------------------------------------------------------------
                        */

                        $partRequest->order_reference =
                            $orderNumber;

                        $partRequest->save();
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | IMPORTANT : AUCUNE MODIFICATION DU STOCK
                    |--------------------------------------------------------------------------
                    |
                    | La création du BC ne modifie jamais :
                    |
                    | - products.quantity ;
                    | - product_depot_stocks ;
                    | - stock_movements ;
                    | - le CUMP ;
                    | - le prix de revient.
                    |
                    | Le stock sera modifié uniquement pendant la réception.
                    |
                    |--------------------------------------------------------------------------
                    */

                    return $supplierOrder;
                },
                3
            );


        /*
        |--------------------------------------------------------------------------
        | NOTIFICATION DES RESPONSABLES
        |--------------------------------------------------------------------------
        |
        | Lorsqu'un nouveau bon de commande est créé :
        |
        | - les administrateurs actifs reçoivent une notification ;
        | - les chefs magasiniers actifs reçoivent une notification ;
        | - le créateur reçoit également la notification ;
        | - seuls les administrateurs pourront approuver le BC.
        |
        */

        try {

            $responsables =
                User::query()
                    ->whereIn(
                        'role',
                        [
                            'admin',
                            'chef_magasinier',
                        ]
                    )
                    ->where(
                        'is_active',
                        true
                    )
                    ->get();

            if ($responsables->isNotEmpty()) {

                Notification::send(
                    $responsables,
                    new NewSupplierOrderNotification(
                        $supplierOrder
                    )
                );
            }

        } catch (Throwable $notificationException) {

            /*
            |--------------------------------------------------------------------------
            | Une erreur de notification ne doit pas annuler le BC
            |--------------------------------------------------------------------------
            */

            report(
                $notificationException
            );
        }


        /*
        |--------------------------------------------------------------------------
        | REDIRECTION APRÈS CRÉATION
        |--------------------------------------------------------------------------
        */

        $itemsCount =
            $supplierOrder
                ->items()
                ->count();

        return redirect()
            ->route(
                'supplier-orders.show',
                $supplierOrder
            )
            ->with(
                'success',
                'Bon de commande '
                . $supplierOrder->order_number
                . ' créé avec succès avec '
                . $itemsCount
                . ' pièce'
                . ($itemsCount > 1 ? 's' : '')
                . '.'
            );

    } catch (ValidationException $exception) {

        throw $exception;

    } catch (Throwable $exception) {

        report($exception);

        return back()
            ->withInput()
            ->with(
                'error',
                'Impossible de créer le bon de commande : '
                . $exception->getMessage()
            );
    }
}
    /*
    |--------------------------------------------------------------------------
    | MODIFIER UN BC (TANT QU'IL N'EST PAS APPROUVÉ)
    |--------------------------------------------------------------------------
    |
    | Un BC brouillon (ou rejeté) peut être modifié : fournisseur, dépôt,
    | dates, devise, conditions, montants, et les pièces (ajouter, retirer,
    | quantité, prix). Une fois approuvé par l'administrateur, il est figé.
    | Un BC rejeté puis modifié repasse en brouillon pour être revu.
    |
    */

    private const STATUTS_MODIFIABLES = [
        SupplierOrder::STATUS_DRAFT,
        SupplierOrder::STATUS_REJECTED,
    ];

    public static function estModifiable(SupplierOrder $supplierOrder): bool
    {
        return in_array($supplierOrder->status, self::STATUTS_MODIFIABLES, true);
    }

    /**
     * Pièces COMMANDÉES qui ne sont dans aucun BC actif (hors ce BC) :
     * elles peuvent être ajoutées au BC.
     */
    private function piecesAjoutables(SupplierOrder $supplierOrder)
    {
        return VehiclePartRequest::query()
            ->with(['vehicle.customer', 'product', 'supplier'])
            ->whereIn('status', [VehiclePartRequest::STATUS_ORDERED, VehiclePartRequest::STATUS_TO_ORDER])
            ->whereDoesntHave('supplierOrderItems', function ($q) use ($supplierOrder) {
                $q->where('supplier_order_id', '!=', $supplierOrder->id)
                    ->whereHas('supplierOrder', fn ($o) => $o->whereNotIn('status', SupplierOrder::STATUTS_INACTIFS));
            })
            ->whereDoesntHave('supplierOrderItems', fn ($q) => $q->where('supplier_order_id', $supplierOrder->id))
            ->orderByDesc('id')
            ->get();
    }

    public function edit(SupplierOrder $supplierOrder): View|RedirectResponse
    {
        if (! self::estModifiable($supplierOrder)) {
            return redirect()
                ->route('supplier-orders.show', $supplierOrder)
                ->with('error', 'Ce bon de commande est déjà approuvé : il ne peut plus être modifié.');
        }

        $supplierOrder->load([
            'supplier',
            'items.vehiclePartRequest.vehicle.customer',
            'items.vehiclePartRequest.product',
            'items.vehiclePartRequest.supplier',
        ]);

        /*
         * Lignes affichées : celles du BC, ou celles envoyées juste avant
         * si le formulaire est revenu avec une erreur (rien n'est perdu).
         */
        $anciennes = old('items');

        if (is_array($anciennes)) {
            $parts = VehiclePartRequest::query()
                ->with(['vehicle.customer', 'product', 'supplier'])
                ->whereIn('id', collect($anciennes)->pluck('vehicle_part_request_id')->filter())
                ->get()
                ->keyBy('id');

            $lignes = collect($anciennes)
                ->map(fn ($l) => [
                    'part'     => $parts->get((int) ($l['vehicle_part_request_id'] ?? 0)),
                    'quantity' => $l['quantity_ordered'] ?? 1,
                    'price'    => $l['unit_price'] ?? 0,
                ])
                ->filter(fn ($l) => $l['part'] !== null)
                ->values();
        } else {
            $lignes = $supplierOrder->items
                ->filter(fn ($item) => $item->vehiclePartRequest !== null)
                ->map(fn ($item) => [
                    'part'     => $item->vehiclePartRequest,
                    'quantity' => (float) $item->quantity_ordered,
                    'price'    => (float) $item->unit_price,
                ])
                ->values();
        }

        $piecesAjoutables = $this->piecesAjoutables($supplierOrder)
            ->reject(fn ($p) => $lignes->contains(fn ($l) => $l['part']->id === $p->id))
            ->values();

        $vehiclePartRequests = $lignes->pluck('part');
        $supplier = $supplierOrder->supplier;

        $suppliers = Supplier::query()->orderBy('name')->get();

        $depots = Depot::query()
            ->where(fn ($q) => $q->where('is_active', true)->orWhere('id', $supplierOrder->depot_id))
            ->orderBy('name')
            ->get();

        return view(
            'supplier-orders.create-multiple',
            compact(
                'supplierOrder',
                'lignes',
                'piecesAjoutables',
                'vehiclePartRequests',
                'supplier',
                'suppliers',
                'depots'
            )
        );
    }

    public function update(Request $request, SupplierOrder $supplierOrder): RedirectResponse
    {
        $validated = $request->validate([
            'supplier_id'            => ['required', 'integer', 'exists:suppliers,id'],
            'depot_id'               => ['required', 'integer', 'exists:depots,id'],
            'order_date'             => ['required', 'date'],
            'expected_delivery_date' => ['nullable', 'date', 'after_or_equal:order_date'],
            'currency'               => ['required', 'string', 'max:10'],
            'discount_rate'          => ['nullable', 'numeric', 'min:0', 'max:100'],
            'shipping_cost'          => ['nullable', 'numeric', 'min:0'],
            'tax_rate'               => ['nullable', 'numeric', 'min:0', 'max:100'],
            'payment_terms'          => ['nullable', 'string', 'max:255'],
            'delivery_terms'         => ['nullable', 'string', 'max:255'],
            'notes'                  => ['nullable', 'string'],
            'items'                                => ['required', 'array', 'min:1'],
            'items.*.vehicle_part_request_id'      => ['required', 'integer', 'distinct', 'exists:vehicle_part_requests,id'],
            'items.*.quantity_ordered'             => ['required', 'numeric', 'gt:0'],
            'items.*.unit_price'                   => ['required', 'numeric', 'min:0'],
        ], [
            'items.required' => 'Le bon de commande doit contenir au moins une pièce.',
            'items.min'      => 'Le bon de commande doit contenir au moins une pièce.',
        ]);

        $items = array_values($validated['items']);

        try {
            DB::transaction(function () use ($supplierOrder, $validated, $items) {
                $bc = SupplierOrder::query()->lockForUpdate()->findOrFail($supplierOrder->id);

                if (! self::estModifiable($bc)) {
                    throw ValidationException::withMessages([
                        'items' => 'Ce bon de commande vient d\'être approuvé : il ne peut plus être modifié.',
                    ]);
                }

                $ids = collect($items)->pluck('vehicle_part_request_id')->map(fn ($id) => (int) $id)->all();

                $parts = VehiclePartRequest::query()
                    ->with('product')
                    ->whereIn('id', $ids)
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

                $lignesActuelles = $bc->items()->get()->keyBy('vehicle_part_request_id');

                foreach ($items as $index => $item) {
                    $part = $parts->get((int) $item['vehicle_part_request_id']);
                    $nom = $part->part_name ?? $part->reference ?? ('#' . $part->id);

                    // Pièce ajoutée : doit être COMMANDÉE et libre
                    if (! $lignesActuelles->has($part->id)) {
                        if (! in_array($part->status, [VehiclePartRequest::STATUS_ORDERED, VehiclePartRequest::STATUS_TO_ORDER], true)) {
                            throw ValidationException::withMessages([
                                "items.$index.vehicle_part_request_id" => 'La pièce « ' . $nom . ' » n’est pas au statut COMMANDÉE.',
                            ]);
                        }

                        $ailleurs = SupplierOrderItem::query()
                            ->where('vehicle_part_request_id', $part->id)
                            ->where('supplier_order_id', '!=', $bc->id)
                            ->whereHas('supplierOrder', fn ($q) => $q->where('status', '!=', SupplierOrder::STATUS_CANCELLED))
                            ->exists();

                        if ($ailleurs) {
                            throw ValidationException::withMessages([
                                "items.$index.vehicle_part_request_id" => 'La pièce « ' . $nom . ' » appartient déjà à un autre bon de commande actif.',
                            ]);
                        }
                    }
                }

                // Dépôt : même règle qu'à la création
                $depotsImposes = $parts->pluck('depot_id')->filter()->map(fn ($d) => (int) $d)->unique()->values();

                if ($depotsImposes->count() > 1) {
                    throw ValidationException::withMessages([
                        'depot_id' => 'Les pièces sont destinées à plusieurs dépôts différents : elles ne peuvent pas être dans le même bon de commande.',
                    ]);
                }

                if ($depotsImposes->count() === 1) {
                    if ($parts->contains(fn ($p) => is_null($p->depot_id) && ! is_null($p->vehicle_id))) {
                        throw ValidationException::withMessages([
                            'depot_id' => 'Un même bon de commande ne peut pas mélanger des pièces destinées directement à un dépôt et des pièces destinées à des véhicules.',
                        ]);
                    }
                    $depotId = (int) $depotsImposes->first();
                } else {
                    $depotId = (int) $validated['depot_id'];
                }

                // Montants (mêmes calculs qu'à la création)
                $subtotal = 0;
                $preparees = [];

                foreach ($items as $item) {
                    $part = $parts->get((int) $item['vehicle_part_request_id']);
                    $qte = round((float) $item['quantity_ordered'], 2);
                    $prix = round((float) $item['unit_price'], 4);
                    $total = round($qte * $prix, 2);
                    $subtotal = round($subtotal + $total, 2);
                    $preparees[] = compact('part', 'qte', 'prix', 'total');
                }

                $tauxRemise = max(0, min(100, round((float) ($validated['discount_rate'] ?? 0), 2)));
                $remise = round($subtotal * $tauxRemise / 100, 2);
                $transport = round(max(0, (float) ($validated['shipping_cost'] ?? 0)), 2);
                $tauxTaxe = max(0, min(100, round((float) ($validated['tax_rate'] ?? 0), 2)));
                $base = max(0, $subtotal - $remise) + $transport;
                $taxe = round($base * $tauxTaxe / 100, 2);

                $bc->update([
                    'supplier_id'            => $validated['supplier_id'],
                    'depot_id'               => $depotId,
                    'order_date'             => $validated['order_date'],
                    'expected_delivery_date' => $validated['expected_delivery_date'] ?? null,
                    'currency'               => strtoupper(trim($validated['currency'])),
                    'subtotal'               => $subtotal,
                    'discount_rate'          => $tauxRemise,
                    'discount'               => $remise,
                    'shipping_cost'          => $transport,
                    'tax_rate'               => $tauxTaxe,
                    'tax_amount'             => $taxe,
                    'total'                  => max(0, round($base + $taxe, 2)),
                    'payment_terms'          => $validated['payment_terms'] ?? null,
                    'delivery_terms'         => $validated['delivery_terms'] ?? null,
                    'notes'                  => $validated['notes'] ?? null,
                    // Rejeté puis corrigé : repart en brouillon pour être revu
                    'status'                 => SupplierOrder::STATUS_DRAFT,
                    'rejected_by'            => null,
                    'rejected_at'            => null,
                    'rejection_reason'       => null,
                ]);

                // Pièces retirées : ligne supprimée, la pièce redevient libre
                foreach ($lignesActuelles as $partId => $ligne) {
                    if (! in_array((int) $partId, $ids, true)) {
                        $ligne->delete();
                        VehiclePartRequest::query()
                            ->whereKey($partId)
                            ->where('order_reference', $bc->order_number)
                            ->update(['order_reference' => null]);
                    }
                }

                // Pièces gardées / ajoutées
                foreach ($preparees as $p) {
                    $part = $p['part'];

                    SupplierOrderItem::updateOrCreate(
                        ['supplier_order_id' => $bc->id, 'vehicle_part_request_id' => $part->id],
                        [
                            'product_id'        => $part->product_id,
                            'reference'         => $part->reference ?? $part->product?->reference,
                            'description'       => $part->part_name ?? $part->product?->designation ?? 'Pièce',
                            'unit'              => $part->unit ?? $part->product?->unit_label,
                            'quantity_ordered'  => $p['qte'],
                            'quantity_received' => min($p['qte'], max(0, (float) ($part->received_quantity ?? 0))),
                            'unit_price'        => $p['prix'],
                            'line_total'        => $p['total'],
                            'notes'             => $part->notes,
                        ]
                    );

                    // La pièce suit le fournisseur et le numéro du BC
                    $part->supplier_id = (int) $validated['supplier_id'];
                    $part->order_reference = $bc->order_number;
                    $part->save();
                }
            }, 3);
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            report($exception);

            return back()
                ->withInput()
                ->with('error', 'Impossible de modifier le bon de commande : ' . $exception->getMessage());
        }

        return redirect()
            ->route('supplier-orders.show', $supplierOrder)
            ->with('success', 'Bon de commande ' . $supplierOrder->order_number . ' modifié.');
    }

    /*
    |--------------------------------------------------------------------------
    | AFFICHER UN BC
    |--------------------------------------------------------------------------
    */

    public function show(
        SupplierOrder $supplierOrder
    ): View {

      $supplierOrder->load([
    /*
    |--------------------------------------------------------------------------
    | INFORMATIONS PRINCIPALES DU BON DE COMMANDE
    |--------------------------------------------------------------------------
    */

    'supplier',
    'depot',
    'creator',
    'approver',

    /*
    |--------------------------------------------------------------------------
    | LIGNES DU BON DE COMMANDE
    |--------------------------------------------------------------------------
    */

    'items.product',
    'items.vehiclePartRequest.vehicle.customer',

    /*
    |--------------------------------------------------------------------------
    | SIGNATURES ÉLECTRONIQUES
    |--------------------------------------------------------------------------
    |
    | preparedSignature :
    | signature de la personne qui a préparé le BC.
    |
    | approvedSignature :
    | signature électronique de l'approbateur.
    |
    */

    'preparedSignature.user',
    'approvedSignature.user',

    // V7 : chef magasinier, demande, réception
    'validator',
    'validatedSignature.user',
    'demande',
    'closer',
]);

        $etapeSignature = self::etapeSignature($supplierOrder, auth()->user());
        $peutRejeter = self::peutRejeter($supplierOrder, auth()->user());
        $depots = Depot::query()->orderBy('name')->get(['id', 'name']);

        return view(
            'supplier-orders.show',
            compact('supplierOrder', 'etapeSignature', 'peutRejeter', 'depots')
        );
    }


    /*
    |--------------------------------------------------------------------------
    | V7 — SIGNATURES : CHEF MAGASINIER (VALIDER) PUIS ADMIN (APPROUVER)
    |--------------------------------------------------------------------------
    |
    | Le vendeur / magasinier enregistre (sans signer) → BC « À valider ».
    | Le chef magasinier signe → « À approuver ». L'admin signe → « Approuvé ».
    | Chacun saute les étapes inférieures à la sienne : un BC créé par l'admin
    | est approuvé directement par sa signature. Une même personne ne signe
    | pas deux fois le même BC.
    |
    */

    /** Étape de signature que l'utilisateur peut faire maintenant : 'valider', 'approuver' ou null. */
    public static function etapeSignature(SupplierOrder $bc, ?User $user): ?string
    {
        if (! $user) {
            return null;
        }

        if ($user->isAdmin()) {
            if ($bc->status === SupplierOrder::STATUS_VALIDATED) {
                return 'approuver';
            }

            return $bc->status === SupplierOrder::STATUS_DRAFT && $bc->creator?->isAdmin() ? 'approuver' : null;
        }

        if ($bc->status === SupplierOrder::STATUS_DRAFT && $user->hasPermission('valider_commandes_fournisseurs')) {
            return 'valider';
        }

        if (
            $bc->status === SupplierOrder::STATUS_VALIDATED
            && $user->hasPermission('approuver_commandes_fournisseurs')
            && (int) $bc->validated_by !== (int) $user->id
        ) {
            return 'approuver';
        }

        return null;
    }

    /** Refus : le chef sur un BC à valider, l'admin sur un BC à valider ou à approuver. */
    public static function peutRejeter(SupplierOrder $bc, ?User $user): bool
    {
        if (! $user) {
            return false;
        }

        if ($bc->status === SupplierOrder::STATUS_VALIDATED) {
            return $user->hasPermission('approuver_commandes_fournisseurs') && (int) $bc->validated_by !== (int) $user->id;
        }

        return $bc->status === SupplierOrder::STATUS_DRAFT
            && ($user->hasPermission('valider_commandes_fournisseurs') || $user->hasPermission('approuver_commandes_fournisseurs'));
    }

    /** Nombre de BC qui attendent la signature de l'utilisateur (compteur du menu). */
    public static function nombreASigner(?User $user): int
    {
        if (! $user) {
            return 0;
        }

        return self::requeteASigner($user)->count();
    }

    private static function requeteASigner(User $user)
    {
        $q = SupplierOrder::query();

        if ($user->isAdmin()) {
            return $q->where(fn ($w) => $w
                ->where('status', SupplierOrder::STATUS_VALIDATED)
                ->orWhere(fn ($d) => $d->where('status', SupplierOrder::STATUS_DRAFT)->whereHas('creator', fn ($u) => $u->where('role', 'admin'))));
        }

        if ($user->hasPermission('valider_commandes_fournisseurs')) {
            return $q->where('status', SupplierOrder::STATUS_DRAFT);
        }

        if ($user->hasPermission('approuver_commandes_fournisseurs')) {
            return $q->where('status', SupplierOrder::STATUS_VALIDATED)->where('validated_by', '!=', $user->id);
        }

        return $q->whereRaw('1 = 0');
    }

    /** Chef magasinier : signe et valide (le BC part chez l'admin). */
    public function validateOrder(Request $request, SupplierOrder $supplierOrder): RedirectResponse
    {
        return $this->signerEtape($request, $supplierOrder, 'valider');
    }

    /** Admin : signe et approuve (le BC peut être envoyé au fournisseur). */
    public function approve(Request $request, SupplierOrder $supplierOrder): RedirectResponse
    {
        return $this->signerEtape($request, $supplierOrder, 'approuver');
    }

    private function signerEtape(Request $request, SupplierOrder $supplierOrder, string $etape): RedirectResponse
    {
        abort_unless(self::etapeSignature($supplierOrder, $this->utilisateurConnecte()) === $etape, 403);

        $validated = $request->validate([
            'signature' => \App\Services\SignatureBonCommandeService::regle(),
        ]);

        $service = app(\App\Services\SignatureBonCommandeService::class);

        try {
            $bc = DB::transaction(function () use ($request, $supplierOrder, $validated, $etape, $service) {
                $bc = SupplierOrder::query()->lockForUpdate()->findOrFail($supplierOrder->id);

                if (self::etapeSignature($bc, auth()->user()) !== $etape) {
                    throw ValidationException::withMessages([
                        'signature' => 'Ce bon de commande vient de changer de statut (' . $bc->libelleStatut() . ') : rechargez la page.',
                    ]);
                }

                if (! $bc->items()->exists()) {
                    throw ValidationException::withMessages(['signature' => 'Ce bon de commande n\'a aucune ligne.']);
                }

                if ($etape === 'valider') {
                    $service->signer($bc, SupplierOrderSignature::TYPE_VALIDATED, $validated['signature'], $request);
                    $bc->update([
                        'status'       => SupplierOrder::STATUS_VALIDATED,
                        'validated_by' => auth()->id(),
                        'validated_at' => now(),
                    ]);
                } else {
                    $service->signer($bc, SupplierOrderSignature::TYPE_APPROVED, $validated['signature'], $request);
                    $bc->update([
                        'status'      => SupplierOrder::STATUS_APPROVED,
                        'approved_by' => auth()->id(),
                        'approved_at' => now(),
                    ]);
                }

                return $bc;
            }, 3);
        } catch (ValidationException $exception) {
            $this->effacerFichier($service);
            throw $exception;
        } catch (Throwable $exception) {
            $this->effacerFichier($service);
            report($exception);

            return back()->withInput()->with('error', 'Impossible de signer le bon de commande : ' . $exception->getMessage());
        }

        if ($etape === 'valider') {
            app(\App\Services\CircuitAchatService::class)->notifier(
                $bc,
                ['admin'],
                'Bon de commande à approuver',
                'Le bon de commande ' . $bc->order_number . ' a été validé par ' . auth()->user()->name . ' : il attend votre approbation.'
            );

            return back()->with('success', 'Bon de commande signé et validé : il attend maintenant l\'approbation de l\'administrateur.');
        }

        return back()->with('success', 'Bon de commande signé et approuvé : il peut être envoyé au fournisseur.');
    }

    private function effacerFichier(\App\Services\SignatureBonCommandeService $service): void
    {
        if ($service->dernierFichier) {
            Storage::disk('local')->delete($service->dernierFichier);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | V7 — ENVOI AU FOURNISSEUR
    |--------------------------------------------------------------------------
    |
    | BC approuvé seulement. Ses pièces passent « Commandée ».
    |
    */

    public function markAsSent(
        SupplierOrder $supplierOrder
    ): RedirectResponse {
        try {
            app(\App\Services\CircuitAchatService::class)->envoyer($supplierOrder);
        } catch (ValidationException $e) {
            return back()->with('error', collect($e->errors())->flatten()->first());
        }

        return back()->with(
            'success',
            'Bon de commande marqué comme envoyé au fournisseur : ses pièces sont maintenant « Commandées ».'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | V7 — RÉCEPTION DEPUIS LE BON DE COMMANDE
    |--------------------------------------------------------------------------
    |
    | Ligne par ligne : quantité reçue et prix réel (facture fournisseur).
    | Stock, CUMP, pièce, ligne du BC, statut du BC et Atelier en un
    | enregistrement. Plus que commandé : administrateur seulement.
    |
    */

    public function receive(Request $request, SupplierOrder $supplierOrder): RedirectResponse
    {
        $validated = $request->validate([
            'depot_id'           => ['required', 'integer', 'exists:depots,id'],
            'lignes'             => ['required', 'array'],
            'lignes.*.quantite'  => ['nullable', 'numeric', 'min:0'],
            'lignes.*.prix'      => ['nullable', 'numeric', 'min:0'],
        ], [
            'depot_id.required' => 'Le dépôt de réception est obligatoire.',
        ]);

        try {
            $n = app(\App\Services\CircuitAchatService::class)->receptionner(
                $supplierOrder,
                $validated['lignes'],
                (int) $validated['depot_id'],
                (bool) auth()->user()?->isAdmin()
            );
        } catch (ValidationException $e) {
            return back()->withInput()->with('error', collect($e->errors())->flatten()->first());
        }

        $bc = $supplierOrder->fresh();

        return back()->with(
            'success',
            $n . ' ligne' . ($n > 1 ? 's' : '') . ' réceptionnée' . ($n > 1 ? 's' : '') . ' : stock mis à jour. '
            . ($bc->status === SupplierOrder::STATUS_RECEIVED
                ? 'Tout est arrivé : le bon de commande est reçu.'
                : 'Le bon de commande est en réception partielle.')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | V7 — SOLDER (le fournisseur ne livrera pas le reste)
    |--------------------------------------------------------------------------
    |
    | Pour chaque ligne pas entièrement reçue : « recommander » (le reste repart
    | en recherche dans la même demande, pour un autre fournisseur) ou
    | « annuler ». Le BC passe « Soldé ».
    |
    */

    public function close(Request $request, SupplierOrder $supplierOrder): RedirectResponse
    {
        $validated = $request->validate([
            'choix'   => ['nullable', 'array'],
            'choix.*' => ['in:recommander,annuler'],
        ]);

        try {
            app(\App\Services\CircuitAchatService::class)->solder($supplierOrder, $validated['choix'] ?? []);
        } catch (ValidationException $e) {
            return back()->with('error', collect($e->errors())->flatten()->first());
        }

        return back()->with('success', 'Bon de commande soldé. Les pièces « à re-commander » sont de nouveau « En recherche » dans leur demande.');
    }

    /*
    |--------------------------------------------------------------------------
    | ANNULER LE BC
    |--------------------------------------------------------------------------
    */

    public function cancel(
        SupplierOrder $supplierOrder
    ): RedirectResponse {

        /*
        |--------------------------------------------------------------------------
        | INTERDIRE L'ANNULATION APRÈS RÉCEPTION
        |--------------------------------------------------------------------------
        |
        | Ici STATUS_PARTIAL_RECEIVED doit rester.
        |
        | Il ne sert PAS à autoriser la création d'un BC.
        | Il empêche simplement d'annuler un BC ayant déjà reçu
        | de la marchandise.
        |
        |--------------------------------------------------------------------------
        */

        if (
            in_array(
                $supplierOrder->status,
                [
                    SupplierOrder::STATUS_PARTIAL_RECEIVED,
                    SupplierOrder::STATUS_RECEIVED,
                ],
                true
            )
        ) {
            return back()->with(
                'error',
                'Impossible d’annuler un bon de commande ayant déjà une réception.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | ANNULATION
        |--------------------------------------------------------------------------
        */

        if (
            $supplierOrder->status === SupplierOrder::STATUS_SENT
            && ! auth()->user()?->isAdmin()
        ) {
            return back()->with(
                'error',
                'Ce bon de commande est déjà envoyé au fournisseur : seul l’administrateur peut l’annuler.'
            );
        }

        if (in_array($supplierOrder->status, SupplierOrder::STATUTS_INACTIFS, true)) {
            return back()->with('error', 'Ce bon de commande est déjà ' . mb_strtolower($supplierOrder->libelleStatut()) . '.');
        }

        DB::transaction(function () use ($supplierOrder) {
            $supplierOrder->load(
                'items.vehiclePartRequest'
            );

            // V7 : les pièces pas encore commandées repartent en recherche
            // (sans fournisseur, pour en choisir un autre)
            $circuit = app(\App\Services\CircuitAchatService::class);
            foreach ($supplierOrder->items as $item) {
                $piece = $item->vehiclePartRequest;
                if ($piece && in_array($piece->status, [VehiclePartRequest::STATUS_TO_ORDER, VehiclePartRequest::STATUS_ORDERED], true)) {
                    $piece->supplier_id = null;
                    $circuit->changerStatut($piece, VehiclePartRequest::STATUS_SEARCHING, 'Bon de commande ' . $supplierOrder->order_number . ' annulé.');
                    $piece->save();
                }
            }

            foreach ($supplierOrder->items as $item) {

                $partRequest =
                    $item->vehiclePartRequest;

                /*
                |--------------------------------------------------------------------------
                | RETIRER LA RÉFÉRENCE DU BC DE LA DEMANDE
                |--------------------------------------------------------------------------
                */

                if (
                    $partRequest
                    &&
                    $partRequest->order_reference
                    ===
                    $supplierOrder->order_number
                ) {
                    $partRequest->order_reference = null;
                    $partRequest->save();
                }
            }

            /*
            |--------------------------------------------------------------------------
            | PASSER LE BC À ANNULÉ
            |--------------------------------------------------------------------------
            */

            $supplierOrder->update([
                'status' =>
                    SupplierOrder::STATUS_CANCELLED,
            ]);
        });

        return back()->with(
            'success',
            'Bon de commande annulé.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | V7 — REFUS (chef magasinier ou admin)
    |--------------------------------------------------------------------------
    |
    | Le BC revient à son créateur avec le motif ; il le corrige (« Modifier »)
    | et il repart « À valider ». Les signatures repartent de zéro.
    |
    */

    public function reject(
        Request $request,
        SupplierOrder $supplierOrder
    ): RedirectResponse {
        abort_unless(self::peutRejeter($supplierOrder, $this->utilisateurConnecte()), 403);

        $validated = $request->validate(
            [
                'rejection_reason' => ['required', 'string', 'min:3', 'max:2000'],
            ],
            [
                'rejection_reason.required' => 'Le motif du rejet est obligatoire.',
                'rejection_reason.min'      => 'Le motif du rejet doit contenir au moins 3 caractères.',
                'rejection_reason.max'      => 'Le motif du rejet ne peut pas dépasser 2000 caractères.',
            ]
        );

        DB::transaction(function () use ($supplierOrder, $validated) {
            $bc = SupplierOrder::query()->lockForUpdate()->findOrFail($supplierOrder->id);

            if (! self::peutRejeter($bc, auth()->user())) {
                throw ValidationException::withMessages([
                    'rejection_reason' => 'Ce bon de commande vient de changer de statut (' . $bc->libelleStatut() . ') : il ne peut plus être rejeté.',
                ]);
            }

            app(\App\Services\SignatureBonCommandeService::class)->effacer($bc, SupplierOrderSignature::TYPE_VALIDATED);

            $bc->update([
                'status'           => SupplierOrder::STATUS_REJECTED,
                'rejected_by'      => auth()->id(),
                'rejected_at'      => now(),
                'rejection_reason' => trim($validated['rejection_reason']),
                'validated_by'     => null,
                'validated_at'     => null,
            ]);
        });

        $bc = $supplierOrder->fresh('creator');

        if ($bc->creator && $bc->creator->id !== auth()->id()) {
            rescue(fn () => $bc->creator->notify(new NewSupplierOrderNotification(
                $bc,
                'Bon de commande rejeté',
                'Le bon de commande ' . $bc->order_number . ' a été rejeté par ' . auth()->user()->name . ' : ' . $bc->rejection_reason
            )), null, true);
        }

        return redirect()
            ->route('supplier-orders.show', $supplierOrder)
            ->with('success', 'Bon de commande rejeté : il revient à son créateur pour correction.');
    }

    /*
|--------------------------------------------------------------------------
| AFFICHER UNE SIGNATURE ÉLECTRONIQUE
|--------------------------------------------------------------------------
|
| Les signatures sont stockées sur le disque privé "local".
|
| Cette méthode permet uniquement à un utilisateur authentifié
| d'afficher une signature appartenant réellement au bon de commande.
|
|--------------------------------------------------------------------------
*/

public function showSignature(
    SupplierOrder $supplierOrder,
    string $type
)
{
    /*
    |--------------------------------------------------------------------------
    | TYPES AUTORISÉS
    |--------------------------------------------------------------------------
    */

    if (
        !in_array(
            $type,
            [
                SupplierOrderSignature::TYPE_PREPARED,
                SupplierOrderSignature::TYPE_VALIDATED,
                SupplierOrderSignature::TYPE_APPROVED,
            ],
            true
        )
    ) {
        abort(404);
    }


    /*
    |--------------------------------------------------------------------------
    | RECHERCHER LA SIGNATURE
    |--------------------------------------------------------------------------
    */

    $signature = SupplierOrderSignature::query()
        ->where(
            'supplier_order_id',
            $supplierOrder->id
        )
        ->where(
            'type',
            $type
        )
        ->latest('id')
        ->first();


    /*
    |--------------------------------------------------------------------------
    | SIGNATURE INTROUVABLE
    |--------------------------------------------------------------------------
    */

    if (!$signature) {
        abort(404);
    }


    /*
    |--------------------------------------------------------------------------
    | VÉRIFIER LE CHEMIN
    |--------------------------------------------------------------------------
    */

    $path = $signature->signature_path;

    if (
        !$path
        ||
        !Storage::disk('local')->exists($path)
    ) {
        abort(404);
    }


    /*
    |--------------------------------------------------------------------------
    | RETOURNER LE PNG PRIVÉ
    |--------------------------------------------------------------------------
    */

    return response(
        Storage::disk('local')->get($path),
        200,
        [
            'Content-Type' => 'image/png',

            /*
            | La signature est une donnée sensible.
            | On évite donc sa mise en cache persistante.
            */
            'Cache-Control' =>
                'private, no-store, no-cache, must-revalidate',

            'Pragma' => 'no-cache',

            'X-Content-Type-Options' => 'nosniff',
        ]
    );
}
}
