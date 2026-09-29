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
                            $partRequest->status
                            !==
                            VehiclePartRequest::STATUS_ORDERED
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

                            'depot_id' =>
                                $validated['depot_id'],

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
]);

        return view(
            'supplier-orders.show',
            compact('supplierOrder')
        );
    }


    /*
    |--------------------------------------------------------------------------
    | APPROUVER LE BC
    |--------------------------------------------------------------------------
    */

    public function approve(
    Request $request,
    SupplierOrder $supplierOrder
): RedirectResponse {

    /*
    |--------------------------------------------------------------------------
    | AUTORISATION
    |--------------------------------------------------------------------------
    |
    | Seuls les administrateurs peuvent signer et approuver un BC.
    |
    */

    abort_unless(
        auth()->check()
        &&
        auth()->user()->role === 'admin',
        403
    );


    /*
    |--------------------------------------------------------------------------
    | VALIDATION DE LA SIGNATURE
    |--------------------------------------------------------------------------
    |
    | Le navigateur enverra la signature dessinée sous la forme :
    |
    | data:image/png;base64,xxxxxxxx...
    |
    | Nous limitons volontairement la taille reçue.
    |
    */

    $validated = $request->validate([

        'signature' => [
            'required',
            'string',
            'max:3000000',
            function (
                string $attribute,
                mixed $value,
                \Closure $fail
            ) {

                if (
                    !is_string($value)
                    ||
                    !str_starts_with(
                        $value,
                        'data:image/png;base64,'
                    )
                ) {
                    $fail(
                        'La signature électronique est invalide.'
                    );

                    return;
                }


                /*
                |--------------------------------------------------------------------------
                | EXTRAIRE LE BASE64
                |--------------------------------------------------------------------------
                */

                $base64 =
                    substr(
                        $value,
                        strlen(
                            'data:image/png;base64,'
                        )
                    );


                /*
                |--------------------------------------------------------------------------
                | DÉCODAGE STRICT
                |--------------------------------------------------------------------------
                */

                $binary =
                    base64_decode(
                        $base64,
                        true
                    );

                if (
                    $binary === false
                    ||
                    strlen($binary) < 100
                ) {

                    $fail(
                        'La signature électronique est vide ou invalide.'
                    );

                    return;
                }


                /*
                |--------------------------------------------------------------------------
                | VÉRIFIER QU'IL S'AGIT RÉELLEMENT D'UNE IMAGE PNG
                |--------------------------------------------------------------------------
                */

                $pngSignature =
                    "\x89PNG\r\n\x1a\n";

                if (
                    !str_starts_with(
                        $binary,
                        $pngSignature
                    )
                ) {

                    $fail(
                        'La signature doit être une image PNG valide.'
                    );
                }
            },
        ],
    ]);


    /*
    |--------------------------------------------------------------------------
    | PRÉPARER LE FICHIER
    |--------------------------------------------------------------------------
    */

    $base64 =
        substr(
            $validated['signature'],
            strlen(
                'data:image/png;base64,'
            )
        );

    $signatureBinary =
        base64_decode(
            $base64,
            true
        );

    if ($signatureBinary === false) {

        return back()
            ->withInput()
            ->with(
                'error',
                'Impossible de décoder la signature.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | CHEMIN PRIVÉ
    |--------------------------------------------------------------------------
    |
    | Le fichier n'est PAS enregistré dans public/storage.
    |
    | Exemple :
    |
    | storage/app/private/supplier-order-signatures/8/approved-....png
    |
    */

    $signatureDirectory =
        'supplier-order-signatures/'
        .
        $supplierOrder->id;

    $signatureFileName =
        'approved-'
        .
        now()->format('YmdHis')
        .
        '-'
        .
        bin2hex(
            random_bytes(8)
        )
        .
        '.png';

    $signaturePath =
        $signatureDirectory
        .
        '/'
        .
        $signatureFileName;


    /*
    |--------------------------------------------------------------------------
    | PERMET DE SUPPRIMER LE FICHIER EN CAS D'ÉCHEC SQL
    |--------------------------------------------------------------------------
    */

    $fileStored = false;


    try {

        DB::transaction(
            function () use (
                $request,
                $supplierOrder,
                $signatureBinary,
                $signaturePath,
                &$fileStored
            ) {

                /*
                |--------------------------------------------------------------------------
                | RECHARGER ET VERROUILLER LE BC
                |--------------------------------------------------------------------------
                |
                | Nous ne faisons pas confiance à l'état du modèle chargé avant
                | le début de la transaction.
                |
                */

                $lockedOrder =
                    SupplierOrder::query()
                        ->whereKey(
                            $supplierOrder->id
                        )
                        ->lockForUpdate()
                        ->firstOrFail();


                /*
                |--------------------------------------------------------------------------
                | LE BC DOIT TOUJOURS ÊTRE BROUILLON
                |--------------------------------------------------------------------------
                */

                if (
                    $lockedOrder->status
                    !==
                    SupplierOrder::STATUS_DRAFT
                ) {

                    throw ValidationException::withMessages([
                        'signature' =>
                            'Ce bon de commande n’est plus en brouillon et ne peut plus être approuvé.',
                    ]);
                }


                /*
                |--------------------------------------------------------------------------
                | EMPÊCHER UNE DEUXIÈME SIGNATURE D'APPROBATION
                |--------------------------------------------------------------------------
                */

                $alreadySigned =
                    SupplierOrderSignature::query()
                        ->where(
                            'supplier_order_id',
                            $lockedOrder->id
                        )
                        ->where(
                            'type',
                            SupplierOrderSignature::TYPE_APPROVED
                        )
                        ->exists();

                if ($alreadySigned) {

                    throw ValidationException::withMessages([
                        'signature' =>
                            'Ce bon de commande possède déjà une signature d’approbation.',
                    ]);
                }


                /*
                |--------------------------------------------------------------------------
                | CHARGER LES LIGNES DANS UN ORDRE DÉTERMINISTE
                |--------------------------------------------------------------------------
                |
                | L'ordre est important pour que le même document produise
                | toujours la même empreinte.
                |
                */

                $items =
                    $lockedOrder
                        ->items()
                        ->orderBy('id')
                        ->get();


                /*
                |--------------------------------------------------------------------------
                | CONSTRUIRE LE CONTENU À SIGNER
                |--------------------------------------------------------------------------
                |
                | Nous n'incluons volontairement pas :
                |
                | - updated_at ;
                | - approved_at ;
                | - approved_by ;
                | - status.
                |
                | Ces valeurs changent précisément pendant l'approbation.
                |
                */

                $documentData = [

                    'supplier_order' => [

                        'id' =>
                            (int) $lockedOrder->id,

                        'order_number' =>
                            (string) $lockedOrder->order_number,

                        'supplier_id' =>
                            (int) $lockedOrder->supplier_id,

                        'depot_id' =>
                            $lockedOrder->depot_id !== null
                                ? (int) $lockedOrder->depot_id
                                : null,

                        'created_by' =>
                            $lockedOrder->created_by !== null
                                ? (int) $lockedOrder->created_by
                                : null,

                        'order_date' =>
                            $lockedOrder->order_date
                                ?->format('Y-m-d'),

                        'expected_delivery_date' =>
                            $lockedOrder
                                ->expected_delivery_date
                                ?->format('Y-m-d'),

                        'currency' =>
                            (string) $lockedOrder->currency,

                        'subtotal' =>
                            number_format(
                                (float) $lockedOrder->subtotal,
                                2,
                                '.',
                                ''
                            ),

                        'discount_rate' =>
                            number_format(
                                (float) $lockedOrder->discount_rate,
                                2,
                                '.',
                                ''
                            ),

                        'discount' =>
                            number_format(
                                (float) $lockedOrder->discount,
                                2,
                                '.',
                                ''
                            ),

                        'shipping_cost' =>
                            number_format(
                                (float) $lockedOrder->shipping_cost,
                                2,
                                '.',
                                ''
                            ),

                        'tax_rate' =>
                            number_format(
                                (float) $lockedOrder->tax_rate,
                                2,
                                '.',
                                ''
                            ),

                        'tax_amount' =>
                            number_format(
                                (float) $lockedOrder->tax_amount,
                                2,
                                '.',
                                ''
                            ),

                        'total' =>
                            number_format(
                                (float) $lockedOrder->total,
                                2,
                                '.',
                                ''
                            ),

                        'payment_terms' =>
                            $lockedOrder->payment_terms,

                        'delivery_terms' =>
                            $lockedOrder->delivery_terms,

                        'notes' =>
                            $lockedOrder->notes,
                    ],


                    /*
                    |--------------------------------------------------------------------------
                    | LIGNES DU BC
                    |--------------------------------------------------------------------------
                    */

                    'items' =>
                        $items
                            ->map(
                                function (
                                    SupplierOrderItem $item
                                ) {

                                    return [

                                        'id' =>
                                            (int) $item->id,

                                        'vehicle_part_request_id' =>
                                            $item
                                                ->vehicle_part_request_id
                                                !== null
                                                ? (int) $item
                                                    ->vehicle_part_request_id
                                                : null,

                                        'product_id' =>
                                            $item->product_id !== null
                                                ? (int) $item->product_id
                                                : null,

                                        'reference' =>
                                            $item->reference,

                                        'description' =>
                                            $item->description,

                                        'unit' =>
                                            $item->unit,

                                        'quantity_ordered' =>
                                            number_format(
                                                (float) $item
                                                    ->quantity_ordered,
                                                2,
                                                '.',
                                                ''
                                            ),

                                        'unit_price' =>
                                            number_format(
                                                (float) $item
                                                    ->unit_price,
                                                4,
                                                '.',
                                                ''
                                            ),

                                        'line_total' =>
                                            number_format(
                                                (float) $item
                                                    ->line_total,
                                                2,
                                                '.',
                                                ''
                                            ),

                                        'notes' =>
                                            $item->notes,
                                    ];
                                }
                            )
                            ->values()
                            ->all(),
                ];


                /*
                |--------------------------------------------------------------------------
                | JSON CANONIQUE
                |--------------------------------------------------------------------------
                */

                $documentJson =
                    json_encode(
                        $documentData,
                        JSON_UNESCAPED_UNICODE
                        |
                        JSON_UNESCAPED_SLASHES
                        |
                        JSON_PRESERVE_ZERO_FRACTION
                        |
                        JSON_THROW_ON_ERROR
                    );


                /*
                |--------------------------------------------------------------------------
                | EMPREINTE SHA-256
                |--------------------------------------------------------------------------
                */

                $documentHash =
                    hash(
                        'sha256',
                        $documentJson
                    );


                /*
                |--------------------------------------------------------------------------
                | DATE UNIQUE DE SIGNATURE / APPROBATION
                |--------------------------------------------------------------------------
                */

                $signedAt = now();


                /*
                |--------------------------------------------------------------------------
                | ENREGISTRER LE PNG SUR LE DISQUE PRIVÉ
                |--------------------------------------------------------------------------
                */

                $stored =
                    Storage::disk('local')
                        ->put(
                            $signaturePath,
                            $signatureBinary
                        );

                if (!$stored) {

                    throw new \RuntimeException(
                        'Impossible d’enregistrer le fichier de signature.'
                    );
                }

                $fileStored = true;


                /*
                |--------------------------------------------------------------------------
                | ENREGISTRER LA SIGNATURE
                |--------------------------------------------------------------------------
                */

                SupplierOrderSignature::create([

                    'supplier_order_id' =>
                        $lockedOrder->id,

                    'user_id' =>
                        auth()->id(),

                    'type' =>
                        SupplierOrderSignature::TYPE_APPROVED,

                    'signature_path' =>
                        $signaturePath,

                    'signed_at' =>
                        $signedAt,

                    'ip_address' =>
                        $request->ip(),

                    'user_agent' =>
                        substr(
                            (string) $request->userAgent(),
                            0,
                            2000
                        ),

                    'document_hash' =>
                        $documentHash,
                ]);


                /*
                |--------------------------------------------------------------------------
                | APPROUVER LE BC
                |--------------------------------------------------------------------------
                |
                | La signature et l'approbation font partie de la même
                | transaction SQL.
                |
                */

                $lockedOrder->update([

                    'status' =>
                        SupplierOrder::STATUS_APPROVED,

                    'approved_by' =>
                        auth()->id(),

                    'approved_at' =>
                        $signedAt,
                ]);
            },
            3
        );


        /*
        |--------------------------------------------------------------------------
        | SUCCÈS
        |--------------------------------------------------------------------------
        */

        return back()->with(
            'success',
            'Bon de commande signé et approuvé avec succès.'
        );

    } catch (ValidationException $exception) {

        /*
        |--------------------------------------------------------------------------
        | NETTOYER LE PNG SI LA TRANSACTION A ÉCHOUÉ
        |--------------------------------------------------------------------------
        */

        if ($fileStored) {

            Storage::disk('local')
                ->delete(
                    $signaturePath
                );
        }

        throw $exception;

    } catch (Throwable $exception) {

        /*
        |--------------------------------------------------------------------------
        | NETTOYER LE PNG SI LA TRANSACTION A ÉCHOUÉ
        |--------------------------------------------------------------------------
        */

        if ($fileStored) {

            Storage::disk('local')
                ->delete(
                    $signaturePath
                );
        }

        report($exception);

        return back()
            ->withInput()
            ->with(
                'error',
                'Impossible de signer et approuver le bon de commande.'
            );
    }
}
    /*
    |--------------------------------------------------------------------------
    | MARQUER COMME ENVOYÉ AU FOURNISSEUR
    |--------------------------------------------------------------------------
    */

    public function markAsSent(
        SupplierOrder $supplierOrder
    ): RedirectResponse {

        /*
        |--------------------------------------------------------------------------
        | STATUTS AUTORISÉS
        |--------------------------------------------------------------------------
        */

        if (
            !in_array(
                $supplierOrder->status,
                [
                    SupplierOrder::STATUS_DRAFT,
                    SupplierOrder::STATUS_APPROVED,
                ],
                true
            )
        ) {
            return back()->with(
                'error',
                'Ce bon de commande ne peut pas être marqué comme envoyé.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | MARQUER COMME ENVOYÉ
        |--------------------------------------------------------------------------
        */

        $supplierOrder->update([
            'status' =>
                SupplierOrder::STATUS_SENT,

            'sent_at' =>
                now(),
        ]);

        return back()->with(
            'success',
            'Bon de commande marqué comme envoyé au fournisseur.'
        );
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

        DB::transaction(function () use ($supplierOrder) {

            $supplierOrder->load(
                'items.vehiclePartRequest'
            );

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

    /*
    |--------------------------------------------------------------------------
    | REJETER LE BON DE COMMANDE
    |--------------------------------------------------------------------------
    |
    | Le rejet est différent de l'annulation.
    |
    | Le rejet représente une décision administrative :
    | l'administrateur refuse le bon de commande et doit obligatoirement
    | enregistrer le motif de cette décision.
    |
    */

    public function reject(
        Request $request,
        SupplierOrder $supplierOrder
    ): RedirectResponse {

        /*
        |--------------------------------------------------------------------------
        | AUTORISATION
        |--------------------------------------------------------------------------
        |
        | Comme pour l'approbation, seuls les administrateurs peuvent
        | rejeter un bon de commande.
        |
        */

        abort_unless(
            auth()->check()
            &&
            auth()->user()->role === 'admin',
            403
        );


        /*
        |--------------------------------------------------------------------------
        | VALIDATION DU MOTIF
        |--------------------------------------------------------------------------
        */

        $validated = $request->validate(
            [
                'rejection_reason' => [
                    'required',
                    'string',
                    'min:3',
                    'max:2000',
                ],
            ],
            [
                'rejection_reason.required' =>
                    'Le motif du rejet est obligatoire.',

                'rejection_reason.min' =>
                    'Le motif du rejet doit contenir au moins 3 caractères.',

                'rejection_reason.max' =>
                    'Le motif du rejet ne peut pas dépasser 2000 caractères.',
            ]
        );


        /*
        |--------------------------------------------------------------------------
        | TRANSACTION SÉCURISÉE
        |--------------------------------------------------------------------------
        |
        | Le verrouillage empêche par exemple qu'un administrateur approuve
        | le BC pendant qu'un autre administrateur essaie de le rejeter.
        |
        */

        DB::transaction(
            function () use (
                $supplierOrder,
                $validated
            ) {

                /*
                |--------------------------------------------------------------------------
                | RECHARGER ET VERROUILLER LE BC
                |--------------------------------------------------------------------------
                */

                $lockedOrder =
                    SupplierOrder::query()
                        ->whereKey(
                            $supplierOrder->id
                        )
                        ->lockForUpdate()
                        ->firstOrFail();


                /*
                |--------------------------------------------------------------------------
                | SEUL UN BC BROUILLON PEUT ÊTRE REJETÉ
                |--------------------------------------------------------------------------
                */

                if (
                    $lockedOrder->status
                    !==
                    SupplierOrder::STATUS_DRAFT
                ) {

                    throw ValidationException::withMessages([
                        'rejection_reason' =>
                            'Ce bon de commande n’est plus en brouillon et ne peut plus être rejeté.',
                    ]);
                }


                /*
                |--------------------------------------------------------------------------
                | ENREGISTRER LE REJET
                |--------------------------------------------------------------------------
                */

                $lockedOrder->update([

                    'status' =>
                        SupplierOrder::STATUS_REJECTED,

                    'rejected_by' =>
                        auth()->id(),

                    'rejected_at' =>
                        now(),

                    'rejection_reason' =>
                        trim(
                            $validated[
                                'rejection_reason'
                            ]
                        ),
                ]);
            }
        );


        /*
        |--------------------------------------------------------------------------
        | RETOUR VERS LE BON DE COMMANDE
        |--------------------------------------------------------------------------
        */

        return redirect()
            ->route(
                'supplier-orders.show',
                $supplierOrder
            )
            ->with(
                'success',
                'Bon de commande rejeté avec succès.'
            );
    }


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
