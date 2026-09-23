<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVehiclePartRequestRequest;
use App\Imports\VehiclePartRequestsImport;

use App\Models\Depot;
use App\Models\Product;
use App\Models\ProductDepotStock;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\Vehicle;
use App\Models\VehiclePartRequest;
use App\Models\VehiclePartRequestHistory;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class VehiclePartRequestController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | INDEX
    |--------------------------------------------------------------------------
    */

    public function index(Request $request): View
    {
        $query = VehiclePartRequest::query()
            ->with([
                'vehicle.customer',
                'product',
                'supplier',
                'creator',
            ]);


        /*
        |--------------------------------------------------------------------------
        | RECHERCHE
        |--------------------------------------------------------------------------
        */

        if ($request->filled('search')) {

            $search =
                trim((string) $request->search);

            $query->where(
                function ($subQuery) use ($search) {

                    $subQuery
                        ->where(
                            'part_name',
                            'like',
                            "%{$search}%"
                        )
                        ->orWhere(
                            'reference',
                            'like',
                            "%{$search}%"
                        )
                        ->orWhere(
                            'supplier_reference',
                            'like',
                            "%{$search}%"
                        )
                        ->orWhere(
                            'order_reference',
                            'like',
                            "%{$search}%"
                        )
                        ->orWhereHas(
                            'vehicle',
                            function ($vehicleQuery) use ($search) {

                                $vehicleQuery
                                    ->where(
                                        'plate_number',
                                        'like',
                                        "%{$search}%"
                                    )
                                    ->orWhere(
                                        'vin',
                                        'like',
                                        "%{$search}%"
                                    )
                                    ->orWhere(
                                        'brand',
                                        'like',
                                        "%{$search}%"
                                    )
                                    ->orWhere(
                                        'model',
                                        'like',
                                        "%{$search}%"
                                    );
                            }
                        );
                }
            );
        }


        /*
        |--------------------------------------------------------------------------
        | STATUT
        |--------------------------------------------------------------------------
        */

        if ($request->filled('status')) {

            $query->where(
                'status',
                $request->status
            );
        }


        /*
        |--------------------------------------------------------------------------
        | VÉHICULE
        |--------------------------------------------------------------------------
        */

        if ($request->filled('vehicle_id')) {

            $query->where(
                'vehicle_id',
                $request->vehicle_id
            );
        }


        /*
        |--------------------------------------------------------------------------
        | PAGINATION
        |--------------------------------------------------------------------------
        */

        $partRequests =
            $query
                ->latest('requested_at')
                ->latest('id')
                ->paginate(15);

        $partRequests->appends(
            $request->query()
        );


        $vehicles =
            Vehicle::query()
                ->orderBy('brand')
                ->orderBy('model')
                ->get();


        $statuses =
            VehiclePartRequest::statuses();


        return view(
            'vehicle-part-requests.index',
            compact(
                'partRequests',
                'vehicles',
                'statuses'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | PIÈCES COMMANDÉES
    |--------------------------------------------------------------------------
    |
    | IMPORTANT :
    |
    | Une réception partielle reste une commande ouverte.
    |
    */

    public function ordered(Request $request): View
    {
        $query =
            VehiclePartRequest::query()
                ->with([
                    'vehicle.customer',
                    'product',
                    'supplier',
                    'creator',
                ])
                ->whereIn(
                    'status',
                    [
                        VehiclePartRequest::STATUS_ORDERED,
                        VehiclePartRequest::STATUS_PARTIAL_RECEIVED,
                    ]
                );


        if ($request->filled('search')) {

            $search =
                trim((string) $request->input('search'));

            $query->where(
                function ($subQuery) use ($search) {

                    $subQuery
                        ->where(
                            'part_name',
                            'like',
                            "%{$search}%"
                        )
                        ->orWhere(
                            'reference',
                            'like',
                            "%{$search}%"
                        )
                        ->orWhereHas(
                            'vehicle',
                            function ($vehicleQuery) use ($search) {

                                $vehicleQuery
                                    ->where(
                                        'plate_number',
                                        'like',
                                        "%{$search}%"
                                    )
                                    ->orWhere(
                                        'vin',
                                        'like',
                                        "%{$search}%"
                                    )
                                    ->orWhere(
                                        'brand',
                                        'like',
                                        "%{$search}%"
                                    )
                                    ->orWhere(
                                        'model',
                                        'like',
                                        "%{$search}%"
                                    );
                            }
                        );
                }
            );
        }


        if ($request->filled('vehicle_id')) {

            $query->where(
                'vehicle_id',
                $request->input('vehicle_id')
            );
        }


        $partRequests =
            $query
                ->latest('ordered_at')
                ->latest('id')
                ->paginate(15);


        $partRequests->appends(
            $request->query()
        );


        $vehicles =
            Vehicle::query()
                ->orderBy('brand')
                ->orderBy('model')
                ->get();


        $statuses =
            VehiclePartRequest::statuses();


        $pageTitle =
            'Pièces commandées';


        $pageDescription =
            'Liste des pièces commandées, y compris les réceptions partielles.';


        $currentList =
            'ordered';


        return view(
            'vehicle-part-requests.index',
            compact(
                'partRequests',
                'vehicles',
                'statuses',
                'pageTitle',
                'pageDescription',
                'currentList'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | PIÈCES REÇUES
    |--------------------------------------------------------------------------
    |
    | Seulement réception complète.
    |
    */

    public function received(Request $request): View
    {
        $query =
            VehiclePartRequest::query()
                ->with([
                    'vehicle.customer',
                    'product',
                    'supplier',
                    'creator',
                ])
                ->where(
                    'status',
                    VehiclePartRequest::STATUS_RECEIVED
                );


        if ($request->filled('search')) {

            $search =
                trim((string) $request->input('search'));

            $query->where(
                function ($subQuery) use ($search) {

                    $subQuery
                        ->where(
                            'part_name',
                            'like',
                            "%{$search}%"
                        )
                        ->orWhere(
                            'reference',
                            'like',
                            "%{$search}%"
                        )
                        ->orWhereHas(
                            'vehicle',
                            function ($vehicleQuery) use ($search) {

                                $vehicleQuery
                                    ->where(
                                        'plate_number',
                                        'like',
                                        "%{$search}%"
                                    )
                                    ->orWhere(
                                        'vin',
                                        'like',
                                        "%{$search}%"
                                    )
                                    ->orWhere(
                                        'brand',
                                        'like',
                                        "%{$search}%"
                                    )
                                    ->orWhere(
                                        'model',
                                        'like',
                                        "%{$search}%"
                                    );
                            }
                        );
                }
            );
        }


        if ($request->filled('vehicle_id')) {

            $query->where(
                'vehicle_id',
                $request->input('vehicle_id')
            );
        }


        $partRequests =
            $query
                ->latest('received_at')
                ->latest('id')
                ->paginate(15);


        $partRequests->appends(
            $request->query()
        );


        $vehicles =
            Vehicle::query()
                ->orderBy('brand')
                ->orderBy('model')
                ->get();


        $statuses =
            VehiclePartRequest::statuses();


        $pageTitle =
            'Pièces reçues';


        $pageDescription =
            'Liste des commandes entièrement reçues.';


        $currentList =
            'received';


        return view(
            'vehicle-part-requests.index',
            compact(
                'partRequests',
                'vehicles',
                'statuses',
                'pageTitle',
                'pageDescription',
                'currentList'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | NON TROUVÉES
    |--------------------------------------------------------------------------
    */

    public function notFound(Request $request): View
    {
        $query =
            VehiclePartRequest::query()
                ->with([
                    'vehicle.customer',
                    'product',
                    'supplier',
                    'creator',
                ])
                ->where(
                    'status',
                    VehiclePartRequest::STATUS_NOT_FOUND
                );


        if ($request->filled('search')) {

            $search =
                trim((string) $request->input('search'));

            $query->where(
                function ($subQuery) use ($search) {

                    $subQuery
                        ->where(
                            'part_name',
                            'like',
                            "%{$search}%"
                        )
                        ->orWhere(
                            'reference',
                            'like',
                            "%{$search}%"
                        )
                        ->orWhereHas(
                            'vehicle',
                            function ($vehicleQuery) use ($search) {

                                $vehicleQuery
                                    ->where(
                                        'plate_number',
                                        'like',
                                        "%{$search}%"
                                    )
                                    ->orWhere(
                                        'vin',
                                        'like',
                                        "%{$search}%"
                                    )
                                    ->orWhere(
                                        'brand',
                                        'like',
                                        "%{$search}%"
                                    )
                                    ->orWhere(
                                        'model',
                                        'like',
                                        "%{$search}%"
                                    );
                            }
                        );
                }
            );
        }


        if ($request->filled('vehicle_id')) {

            $query->where(
                'vehicle_id',
                $request->input('vehicle_id')
            );
        }


        $partRequests =
            $query
                ->latest('not_found_at')
                ->latest('id')
                ->paginate(15);


        $partRequests->appends(
            $request->query()
        );


        $vehicles =
            Vehicle::query()
                ->orderBy('brand')
                ->orderBy('model')
                ->get();


        $statuses =
            VehiclePartRequest::statuses();


        $pageTitle =
            'Pièces non trouvées';


        $pageDescription =
            'Liste des pièces recherchées mais déclarées introuvables.';


        $currentList =
            'not_found';


        return view(
            'vehicle-part-requests.index',
            compact(
                'partRequests',
                'vehicles',
                'statuses',
                'pageTitle',
                'pageDescription',
                'currentList'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | CREATE
    |--------------------------------------------------------------------------
    */

    public function create(Request $request): View
    {
        $vehicles =
            Vehicle::query()
                ->orderBy('brand')
                ->orderBy('model')
                ->get();


        $products =
            Product::query()
                ->orderBy('designation')
                ->get();


        $suppliers =
            Supplier::query()
                ->orderBy('name')
                ->get();


        $selectedVehicleId =
            $request->vehicle_id;


        return view(
            'vehicle-part-requests.create',
            compact(
                'vehicles',
                'products',
                'suppliers',
                'selectedVehicleId'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | STORE
    |--------------------------------------------------------------------------
    */

    public function store(
        StoreVehiclePartRequestRequest $request
    ): RedirectResponse {

        DB::transaction(
            function () use ($request) {

                $partRequest =
                    VehiclePartRequest::create([

                        'vehicle_id' =>
                            $request->vehicle_id,

                        'product_id' =>
                            $request->product_id,

                        'supplier_id' =>
                            $request->supplier_id,

                        'created_by' =>
                            Auth::id(),

                        'reference' =>
                            $request->reference,

                        'part_name' =>
                            $request->part_name,

                        'description' =>
                            $request->description,

                        /*
                        |--------------------------------------------------------------------------
                        | QUANTITÉ DEMANDÉE
                        |--------------------------------------------------------------------------
                        */

                        'quantity' =>
                            $request->quantity,

                        /*
                        |--------------------------------------------------------------------------
                        | RIEN REÇU À LA CRÉATION
                        |--------------------------------------------------------------------------
                        */

                        'received_quantity' =>
                            0,

                        'unit' =>
                            $request->unit,

                        'status' =>
                            VehiclePartRequest::STATUS_SEARCHING,

                        'supplier_reference' =>
                            $request->supplier_reference,

                        'order_reference' =>
                            null,

                        'estimated_price' =>
                            $request->estimated_price,

                        'purchase_price' =>
                            null,

                        'requested_at' =>
                            now(),

                        'search_started_at' =>
                            now(),

                        'ordered_at' =>
                            null,

                        'received_at' =>
                            null,

                        'not_found_at' =>
                            null,

                        'cancelled_at' =>
                            null,

                        'notes' =>
                            $request->notes,
                    ]);


                VehiclePartRequestHistory::create([

                    'vehicle_part_request_id' =>
                        $partRequest->id,

                    'old_status' =>
                        null,

                    'new_status' =>
                        VehiclePartRequest::STATUS_SEARCHING,

                    'old_received_quantity' =>
                        null,

                    'new_received_quantity' =>
                        0,

                    'comment' =>
                        'Création de la demande et début de la recherche.',

                    'changed_by' =>
                        Auth::id(),

                    'changed_at' =>
                        now(),
                ]);
            }
        );


        return redirect()
            ->route(
                'vehicle-part-requests.index'
            )
            ->with(
                'success',
                'La demande de pièce a été créée avec le statut En recherche.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | SHOW
    |--------------------------------------------------------------------------
    */

   public function show(
    VehiclePartRequest $vehiclePartRequest
): View {

    /*
    |--------------------------------------------------------------------------
    | CHARGER LES RELATIONS
    |--------------------------------------------------------------------------
    */

    $vehiclePartRequest->load([
        'vehicle.customer',
        'product',
        'supplier',
        'creator',
        'histories.user',
    ]);


    /*
    |--------------------------------------------------------------------------
    | STATUTS DISPONIBLES
    |--------------------------------------------------------------------------
    */

    $availableStatuses =
        collect(
            $vehiclePartRequest
                ->availableNextStatuses()
        )
        ->mapWithKeys(
            function ($status) {

                return [
                    $status =>
                        VehiclePartRequest::statuses()[$status],
                ];
            }
        );


    /*
    |--------------------------------------------------------------------------
    | FOURNISSEURS
    |--------------------------------------------------------------------------
    */

    $suppliers =
        Supplier::query()
            ->orderBy('name')
            ->get();


    /*
    |--------------------------------------------------------------------------
    | DÉPÔTS
    |--------------------------------------------------------------------------
    |
    | Les dépôts sont nécessaires dans le formulaire de réception.
    |
    | Lorsqu'une pièce commandée est reçue, l'utilisateur doit sélectionner
    | le dépôt dans lequel la quantité entre physiquement en stock.
    |
    */

    $depots =
        Depot::query()
            ->orderBy('name')
            ->get();


    /*
    |--------------------------------------------------------------------------
    | AFFICHAGE
    |--------------------------------------------------------------------------
    */

    return view(
        'vehicle-part-requests.show',
        compact(
            'vehiclePartRequest',
            'availableStatuses',
            'suppliers',
            'depots'
        )
    );
}


    /*
    |--------------------------------------------------------------------------
    | EDIT
    |--------------------------------------------------------------------------
    */

    public function edit(
        VehiclePartRequest $vehiclePartRequest
    ): View {

        $vehicles =
            Vehicle::query()
                ->orderBy('brand')
                ->orderBy('model')
                ->get();


        $products =
            Product::query()
                ->orderBy('designation')
                ->get();


        $suppliers =
            Supplier::query()
                ->orderBy('name')
                ->get();


        return view(
            'vehicle-part-requests.edit',
            compact(
                'vehiclePartRequest',
                'vehicles',
                'products',
                'suppliers'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | UPDATE INFORMATIONS GÉNÉRALES
    |--------------------------------------------------------------------------
    */

    public function update(
        StoreVehiclePartRequestRequest $request,
        VehiclePartRequest $vehiclePartRequest
    ): RedirectResponse {

        /*
        |--------------------------------------------------------------------------
        | IMPORTANT
        |--------------------------------------------------------------------------
        |
        | received_quantity n'est PAS modifiée ici.
        |
        | Elle est modifiée uniquement par updateReceivedQuantity().
        |
        */

        $vehiclePartRequest->update([

            'vehicle_id' =>
                $request->vehicle_id,

            'product_id' =>
                $request->product_id,

            'supplier_id' =>
                $request->supplier_id,

            'reference' =>
                $request->reference,

            'part_name' =>
                $request->part_name,

            'description' =>
                $request->description,

            'quantity' =>
                $request->quantity,

            'unit' =>
                $request->unit,

            'supplier_reference' =>
                $request->supplier_reference,

            'order_reference' =>
                $request->order_reference,

            'estimated_price' =>
                $request->estimated_price,

            'purchase_price' =>
                $request->purchase_price,

            'notes' =>
                $request->notes,
        ]);


        return redirect()
            ->route(
                'vehicle-part-requests.show',
                $vehiclePartRequest
            )
            ->with(
                'success',
                'Les informations de la pièce ont été modifiées.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | CHANGE STATUS
    |--------------------------------------------------------------------------
    */

    public function changeStatus(
        Request $request,
        VehiclePartRequest $vehiclePartRequest
    ): RedirectResponse {

        $request->validate([

            'status' => [
                'required',
                Rule::in(
                    array_keys(
                        VehiclePartRequest::statuses()
                    )
                ),
            ],

            'comment' => [
                'nullable',
                'string',
                'max:2000',
            ],

            'supplier_id' => [
                'nullable',
                'exists:suppliers,id',
            ],

            'order_reference' => [
                'nullable',
                'string',
                'max:255',
            ],

            'purchase_price' => [
                'nullable',
                'numeric',
                'min:0',
            ],
        ]);


        $newStatus =
            $request->status;

        $oldStatus =
            $vehiclePartRequest->status;


        /*
        |--------------------------------------------------------------------------
        | ON NE CHANGE PAS MANUELLEMENT EN RÉCEPTION PARTIELLE / REÇUE
        |--------------------------------------------------------------------------
        |
        | Ces statuts sont pilotés automatiquement par la quantité reçue.
        |
        */

        if (
            in_array(
                $newStatus,
                [
                    VehiclePartRequest::STATUS_PARTIAL_RECEIVED,
                    VehiclePartRequest::STATUS_RECEIVED,
                ],
                true
            )
        ) {

            return back()->with(
                'error',
                'Pour enregistrer une réception, utilisez le formulaire Quantité reçue.'
            );
        }


        if (
            !$vehiclePartRequest
                ->canChangeTo($newStatus)
        ) {

            return back()->with(
                'error',
                'Ce changement de statut n’est pas autorisé.'
            );
        }


        DB::transaction(
            function () use (
                $request,
                $vehiclePartRequest,
                $oldStatus,
                $newStatus
            ) {

                $updateData = [

                    'status' =>
                        $newStatus,
                ];


                switch ($newStatus) {

                    case VehiclePartRequest::STATUS_SEARCHING:

                        $updateData['search_started_at'] =
                            $vehiclePartRequest
                                ->search_started_at
                            ?? now();

                        $updateData['not_found_at'] =
                            null;

                        break;


                    case VehiclePartRequest::STATUS_ORDERED:

                        $updateData['ordered_at'] =
                            $vehiclePartRequest->ordered_at
                            ?? now();

                        $updateData['not_found_at'] =
                            null;


                        if (
                            $request->filled(
                                'supplier_id'
                            )
                        ) {

                            $updateData['supplier_id'] =
                                $request->supplier_id;
                        }


                        if (
                            $request->filled(
                                'order_reference'
                            )
                        ) {

                            $updateData['order_reference'] =
                                $request->order_reference;
                        }


                        if (
                            $request->filled(
                                'purchase_price'
                            )
                        ) {

                            $updateData['purchase_price'] =
                                $request->purchase_price;
                        }

                        break;


                    case VehiclePartRequest::STATUS_NOT_FOUND:

                        $updateData['not_found_at'] =
                            now();

                        break;


                    case VehiclePartRequest::STATUS_CANCELLED:

                        $updateData['cancelled_at'] =
                            now();

                        break;
                }


                $vehiclePartRequest
                    ->update($updateData);


                VehiclePartRequestHistory::create([

                    'vehicle_part_request_id' =>
                        $vehiclePartRequest->id,

                    'old_status' =>
                        $oldStatus,

                    'new_status' =>
                        $newStatus,

                    'old_received_quantity' =>
                        $vehiclePartRequest
                            ->received_quantity,

                    'new_received_quantity' =>
                        $vehiclePartRequest
                            ->received_quantity,

                    'comment' =>
                        $request->comment,

                    'changed_by' =>
                        Auth::id(),

                    'changed_at' =>
                        now(),
                ]);
            }
        );


        return redirect()
            ->route(
                'vehicle-part-requests.show',
                $vehiclePartRequest
            )
            ->with(
                'success',
                'Le statut de la pièce a été mis à jour.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | UPDATE QUANTITÉ REÇUE
    |--------------------------------------------------------------------------
    |
    | Exemple :
    |
    | commandé = 10
    | ancien reçu = 5
    | nouveau reçu = 8
    |
    | Différence à ajouter au stock = 3
    |
    */

    public function updateReceivedQuantity(
        Request $request,
        VehiclePartRequest $vehiclePartRequest
    ): RedirectResponse {

        /*
        |--------------------------------------------------------------------------
        | VALIDATION
        |--------------------------------------------------------------------------
        |
        | received_now = quantité reçue dans CETTE livraison uniquement.
        |
        | Exemple :
        |
        | commandée        = 30
        | déjà reçue       = 20
        | reçue maintenant = 8
        |
        | total reçu après = 28
        | reste après      = 2
        |
        */

        $request->validate(
            [
                'received_now' => [
                    'required',
                    'numeric',
                    'gt:0',
                ],

                /*
                |--------------------------------------------------------------------------
                | DÉPÔT
                |--------------------------------------------------------------------------
                |
                | Facultatif.
                |
                | S'il est envoyé, le stock du dépôt reçoit seulement la quantité
                | de cette nouvelle livraison.
                |
                */

               'depot_id' => [
                    'required',
                    'integer',
                    'exists:depots,id',
                ],

                'comment' => [
                    'nullable',
                    'string',
                    'max:2000',
                ],
            ],
            [
                'received_now.required' =>
                    'La quantité reçue maintenant est obligatoire.',

                'received_now.numeric' =>
                    'La quantité reçue maintenant doit être un nombre.',

                'received_now.gt' =>
                    'La quantité reçue maintenant doit être supérieure à zéro.',

                'depot_id.exists' =>
                    'Le dépôt sélectionné est invalide.',

                'depot_id.required' =>
                    'Le dépôt de réception est obligatoire.',

                'depot_id.integer' =>
                    'Le dépôt sélectionné est invalide.',
            ]
        );


        /*
        |--------------------------------------------------------------------------
        | AUTORISER UNIQUEMENT UNE COMMANDE OU UNE RÉCEPTION PARTIELLE
        |--------------------------------------------------------------------------
        */

        if (
            !in_array(
                $vehiclePartRequest->status,
                [
                    VehiclePartRequest::STATUS_ORDERED,
                    VehiclePartRequest::STATUS_PARTIAL_RECEIVED,
                ],
                true
            )
        ) {
            return back()->with(
                'error',
                'Cette pièce ne peut pas recevoir de quantité dans son statut actuel.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | QUANTITÉS DE LA COMMANDE
        |--------------------------------------------------------------------------
        |
        | quantity          = quantité commandée
        | received_quantity = quantité cumulée déjà reçue
        | received_now      = quantité reçue dans cette livraison
        |
        */

        $orderedQuantity =
            (float) $vehiclePartRequest->quantity;

        $oldReceivedQuantity =
            (float) ($vehiclePartRequest->received_quantity ?? 0);

        $receivedNow =
            (float) $request->received_now;

        $remainingBeforeReception =
            max(
                0,
                $orderedQuantity - $oldReceivedQuantity
            );


        /*
        |--------------------------------------------------------------------------
        | INTERDIRE DE RECEVOIR PLUS QUE LE RESTE
        |--------------------------------------------------------------------------
        |
        | Exemple :
        |
        | commandée   = 30
        | déjà reçue  = 20
        | reste       = 10
        |
        | received_now ne peut pas dépasser 10.
        |
        */

        if ($receivedNow > $remainingBeforeReception) {
            return back()
                ->withInput()
                ->with(
                    'error',
                    'Vous ne pouvez recevoir que '
                    . number_format(
                        $remainingBeforeReception,
                        2,
                        ',',
                        ' '
                    )
                    . ' '
                    . $vehiclePartRequest->unit
                    . ' au maximum.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | NOUVEAU TOTAL REÇU
        |--------------------------------------------------------------------------
        */

        $newReceivedQuantity =
            $oldReceivedQuantity + $receivedNow;

        $remainingAfterReception =
            max(
                0,
                $orderedQuantity - $newReceivedQuantity
            );


        /*
        |--------------------------------------------------------------------------
        | DIFFÉRENCE À APPLIQUER AU STOCK
        |--------------------------------------------------------------------------
        |
        | Ici la différence est simplement la nouvelle livraison.
        |
        | Exemple :
        |
        | déjà reçue       = 20
        | reçue maintenant = 8
        |
        | stock à ajouter  = +8
        |
        */

        $quantityDifference =
            $receivedNow;


        /*
        |--------------------------------------------------------------------------
        | STATUT AUTOMATIQUE
        |--------------------------------------------------------------------------
        */

        $oldStatus =
            $vehiclePartRequest->status;

        if ($newReceivedQuantity >= $orderedQuantity) {

            $newStatus =
                VehiclePartRequest::STATUS_RECEIVED;

        } else {

            $newStatus =
                VehiclePartRequest::STATUS_PARTIAL_RECEIVED;
        }


        /*
        |--------------------------------------------------------------------------
        | TRANSACTION
        |--------------------------------------------------------------------------
        */

        DB::transaction(
            function () use (
                $request,
                $vehiclePartRequest,
                $orderedQuantity,
                $oldReceivedQuantity,
                $receivedNow,
                $newReceivedQuantity,
                $quantityDifference,
                $oldStatus,
                $newStatus
            ) {

               /*
                |--------------------------------------------------------------------------
                | RETROUVER LE PRODUIT
                |--------------------------------------------------------------------------
                |
                | Priorité :
                |
                | 1. product_id déjà lié à la demande
                | 2. référence de la pièce
                | 3. création automatique si le produit n'existe pas
                |
                */

                $product = null;

                if ($vehiclePartRequest->product_id) {
                    $product = Product::query()
                        ->lockForUpdate()
                        ->find($vehiclePartRequest->product_id);
                }

                if (
                    !$product
                    &&
                    !empty($vehiclePartRequest->reference)
                ) {
                    $product = Product::query()
                        ->where(
                            'reference',
                            $vehiclePartRequest->reference
                        )
                        ->lockForUpdate()
                        ->first();
                }


                /*
                |--------------------------------------------------------------------------
                | CRÉATION AUTOMATIQUE DU PRODUIT
                |--------------------------------------------------------------------------
                |
                | Si la pièce commandée n'existe pas encore dans le catalogue,
                | elle est créée lors de sa première réception.
                |
                | IMPORTANT :
                |
                | La quantité est initialisée à zéro ici.
                | L'entrée physique reçue sera ajoutée plus bas UNE SEULE FOIS.
                |
                */

                if (!$product) {

                    if (empty($vehiclePartRequest->reference)) {
                        throw new \RuntimeException(
                            'Impossible de créer automatiquement la pièce : '
                            . 'la référence est obligatoire.'
                        );
                    }

                    $product = new Product();

                    $product->reference =
                        trim($vehiclePartRequest->reference);

                    $product->designation =
                        $vehiclePartRequest->part_name
                        ?: $vehiclePartRequest->reference;

                    /*
                    |--------------------------------------------------------------------------
                    | QUANTITÉS INITIALES
                    |--------------------------------------------------------------------------
                    |
                    | On démarre à zéro pour éviter de compter deux fois
                    | la réception courante.
                    |
                    */

                    $product->quantity = 0;

                    $product->initial_quantity = 0;

                    $product->received_quantity = 0;

                    /*
                    |--------------------------------------------------------------------------
                    | UNITÉ
                    |--------------------------------------------------------------------------
                    |
                    | On reprend l'unité de la demande lorsqu'elle existe.
                    |
                    | Si votre colonne unit_type utilise des valeurs spécifiques,
                    | le modèle / la base conserve ici la valeur par défaut.
                    |
                    */

                    if (!empty($vehiclePartRequest->unit)) {
                        $product->unit_label =
                            $vehiclePartRequest->unit;
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | PRIX D'ACHAT INITIAL
                    |--------------------------------------------------------------------------
                    |
                    | Pour un nouveau produit :
                    |
                    | stock avant = 0
                    |
                    | donc le CUMP initial est simplement le prix
                    | de cette première réception.
                    |
                    */

                    $incomingPrice =
                        (float) ($vehiclePartRequest->purchase_price ?? 0);

                    $product->purchase_price =
                        round($incomingPrice, 4);

                    /*
                    |--------------------------------------------------------------------------
                    | COEFFICIENTS
                    |--------------------------------------------------------------------------
                    |
                    | Si le modèle / la base possède des valeurs par défaut,
                    | elles seront utilisées.
                    |
                    */

                    $coefPurchase =
                        (float) ($product->coef_purchase ?? 0);

                    $coefSale =
                        (float) ($product->coef_sale ?? 0);

                    $product->cost_price =
                        round(
                            $product->purchase_price * $coefPurchase,
                            4
                        );

                    $product->sale_price =
                        round(
                            $product->cost_price * $coefSale,
                            2
                        );

                    $product->status =
                        'disponible';

                    $product->save();
                }


                /*
                |--------------------------------------------------------------------------
                | LIER LE PRODUIT À LA DEMANDE
                |--------------------------------------------------------------------------
                */

                if (
                    !$vehiclePartRequest->product_id
                    ||
                    (int) $vehiclePartRequest->product_id
                        !== (int) $product->id
                ) {
                    $vehiclePartRequest->product_id =
                        $product->id;
                }


                /*
                |--------------------------------------------------------------------------
                | STOCK AVANT RÉCEPTION
                |--------------------------------------------------------------------------
                |
                | IMPORTANT :
                |
                | quantity représente le stock actuellement disponible.
                |
                | C'est cette quantité qui doit être utilisée pour le CUMP.
                |
                | Le CUMP doit impérativement être calculé AVANT
                | d'ajouter received_now au stock.
                |
                */

                $oldQuantity =
                    (float) ($product->quantity ?? 0);

                $oldAveragePurchasePrice =
                    (float) ($product->purchase_price ?? 0);

                $currentProductReceivedQuantity =
                    (float) ($product->received_quantity ?? 0);

                $currentProductInitialQuantity =
                    (float) ($product->initial_quantity ?? 0);


                /*
                |--------------------------------------------------------------------------
                | PRIX DE LA NOUVELLE RÉCEPTION
                |--------------------------------------------------------------------------
                */

                $newPurchasePrice =
                    (float) ($vehiclePartRequest->purchase_price ?? 0);


                /*
                |--------------------------------------------------------------------------
                | QUANTITÉ APRÈS RÉCEPTION
                |--------------------------------------------------------------------------
                */

                $newProductAvailableQuantity =
                    $oldQuantity + $quantityDifference;


                /*
                |--------------------------------------------------------------------------
                | CUMP - PRIX D'ACHAT MOYEN PONDÉRÉ
                |--------------------------------------------------------------------------
                |
                | Même règle que ProductController :
                |
                | (stock actuel × ancien CUMP)
                | +
                | (quantité reçue × nouveau prix)
                | ------------------------------------------------
                | stock actuel + quantité reçue
                |
                */

                if ($quantityDifference > 0) {

                    if ($oldQuantity > 0) {

                        $weightedPurchasePrice = (
                            ($oldQuantity * $oldAveragePurchasePrice)
                            +
                            ($quantityDifference * $newPurchasePrice)
                        ) / $newProductAvailableQuantity;

                    } else {

                        /*
                        |--------------------------------------------------------------------------
                        | STOCK AVANT = 0
                        |--------------------------------------------------------------------------
                        |
                        | L'ancien prix ne participe pas au calcul.
                        |
                        */

                        $weightedPurchasePrice =
                            $newPurchasePrice;
                    }

                } else {

                    $weightedPurchasePrice =
                        $oldAveragePurchasePrice;
                }

                $weightedPurchasePrice =
                    round(
                        $weightedPurchasePrice,
                        4
                    );


                /*
                |--------------------------------------------------------------------------
                | QUANTITÉS HISTORIQUES DU PRODUIT
                |--------------------------------------------------------------------------
                |
                | Cette réception représente une nouvelle entrée physique.
                |
                */

                $newProductInitialQuantity =
                    $currentProductInitialQuantity
                    +
                    $quantityDifference;

                $newProductReceivedQuantity =
                    $currentProductReceivedQuantity
                    +
                    $quantityDifference;


                /*
                |--------------------------------------------------------------------------
                | PRIX DE REVIENT PONDÉRÉ
                |--------------------------------------------------------------------------
                |
                | Même règle que ProductController :
                |
                | prix de revient
                | =
                | CUMP × dernier coefficient d'achat
                |
                */

                $coefPurchase =
                    (float) ($product->coef_purchase ?? 0);

                $weightedCostPrice =
                    round(
                        $weightedPurchasePrice * $coefPurchase,
                        4
                    );


                /*
                |--------------------------------------------------------------------------
                | PRIX DE VENTE
                |--------------------------------------------------------------------------
                */

                $coefSale =
                    (float) ($product->coef_sale ?? 0);

                $salePrice =
                    round(
                        $weightedCostPrice * $coefSale,
                        2
                    );


                /*
                |--------------------------------------------------------------------------
                | MISE À JOUR DU PRODUIT
                |--------------------------------------------------------------------------
                */

                $product->initial_quantity =
                    $newProductInitialQuantity;

                $product->received_quantity =
                    $newProductReceivedQuantity;

                $product->quantity =
                    $newProductAvailableQuantity;

                $product->purchase_price =
                    $weightedPurchasePrice;

                $product->cost_price =
                    $weightedCostPrice;

                $product->sale_price =
                    $salePrice;

                $product->status =
                    'disponible';

                $product->save();


                /*
                |--------------------------------------------------------------------------
                | STOCK DU DÉPÔT
                |--------------------------------------------------------------------------
                |
                | depot_id est obligatoire dans le formulaire.
                |
                */

                $depotStock =
                    ProductDepotStock::query()
                        ->where(
                            'product_id',
                            $product->id
                        )
                        ->where(
                            'depot_id',
                            $request->depot_id
                        )
                        ->lockForUpdate()
                        ->first();

                if (!$depotStock) {

                    $depotStock =
                        new ProductDepotStock();

                    $depotStock->product_id =
                        $product->id;

                    $depotStock->depot_id =
                        $request->depot_id;

                    $depotStock->quantity =
                        0;
                }

                $depotStock->quantity =
                    (float) ($depotStock->quantity ?? 0)
                    +
                    $quantityDifference;

                $depotStock->save();


                /*
                |--------------------------------------------------------------------------
                | MOUVEMENT DE STOCK
                |--------------------------------------------------------------------------
                */

                $stockMovement =
                    new StockMovement();

                $stockMovement->product_id =
                    $product->id;

                $stockMovement->type =
                    'in';

                $stockMovement->quantity =
                    $quantityDifference;

                $stockMovement->source =
                    'Réception commande véhicule';

                $stockMovement->reference =
                    $vehiclePartRequest->order_reference
                    ??
                    $vehiclePartRequest->reference;

                $stockMovement->user_id =
                    Auth::id();

                $stockMovement->save();

                /*
                |--------------------------------------------------------------------------
                | MISE À JOUR DE LA DEMANDE
                |--------------------------------------------------------------------------
                */

                $vehiclePartRequest->received_quantity =
                    $newReceivedQuantity;

                $vehiclePartRequest->status =
                    $newStatus;


                /*
                |--------------------------------------------------------------------------
                | DATE DE RÉCEPTION
                |--------------------------------------------------------------------------
                |
                | On conserve la date de la première réception.
                |
                */

                if (!$vehiclePartRequest->received_at) {
                    $vehiclePartRequest->received_at =
                        now();
                }

                $vehiclePartRequest->save();


                /*
                |--------------------------------------------------------------------------
                | HISTORIQUE
                |--------------------------------------------------------------------------
                */

                $defaultComment =
                    'Nouvelle réception : '
                    . number_format(
                        $receivedNow,
                        2,
                        ',',
                        ' '
                    )
                    . ' '
                    . $vehiclePartRequest->unit
                    . '. Total reçu : '
                    . number_format(
                        $newReceivedQuantity,
                        2,
                        ',',
                        ' '
                    )
                    . ' / '
                    . number_format(
                        $orderedQuantity,
                        2,
                        ',',
                        ' '
                    )
                    . '.';

                VehiclePartRequestHistory::create([
                    'vehicle_part_request_id' =>
                        $vehiclePartRequest->id,

                    'old_status' =>
                        $oldStatus,

                    'new_status' =>
                        $newStatus,

                    'old_received_quantity' =>
                        $oldReceivedQuantity,

                    'new_received_quantity' =>
                        $newReceivedQuantity,

                    'comment' =>
                        $request->filled('comment')
                            ? $request->comment
                            : $defaultComment,

                    'changed_by' =>
                        Auth::id(),

                    'changed_at' =>
                        now(),
                ]);
            }
        );


        /*
        |--------------------------------------------------------------------------
        | MESSAGE DE SUCCÈS
        |--------------------------------------------------------------------------
        */

        if (
            $newStatus
            ===
            VehiclePartRequest::STATUS_RECEIVED
        ) {

            $message =
                'Réception complète enregistrée avec succès.';

        } else {

            $message =
                'Réception partielle enregistrée. Reste à recevoir : '
                . number_format(
                    $remainingAfterReception,
                    2,
                    ',',
                    ' '
                )
                . ' '
                . $vehiclePartRequest->unit
                . '.';
        }


        /*
        |--------------------------------------------------------------------------
        | REDIRECTION
        |--------------------------------------------------------------------------
        */

        return redirect()
            ->route(
                'vehicle-part-requests.show',
                $vehiclePartRequest
            )
            ->with(
                'success',
                $message
            );
    }

        /*
    |--------------------------------------------------------------------------
    | IMPORT EXCEL
    |--------------------------------------------------------------------------
    */

   public function importExcel(
    Request $request
): RedirectResponse {

    /*
    |--------------------------------------------------------------------------
    | VALIDATION DU FORMULAIRE
    |--------------------------------------------------------------------------
    */

    $validated = $request->validate(
        [
            'vehicle_id' => [
                'required',
                'integer',
                'exists:vehicles,id',
            ],

            'excel_file' => [
                'required',
                'file',
                'mimes:xlsx,xls,csv',
                'max:10240',
            ],
        ],
        [
            'vehicle_id.required' =>
                'Veuillez sélectionner un véhicule.',

            'vehicle_id.integer' =>
                'Le véhicule sélectionné est invalide.',

            'vehicle_id.exists' =>
                'Le véhicule sélectionné est invalide.',

            'excel_file.required' =>
                'Veuillez sélectionner un fichier Excel.',

            'excel_file.file' =>
                'Le fichier sélectionné est invalide.',

            'excel_file.mimes' =>
                'Le fichier doit être au format XLSX, XLS ou CSV.',

            'excel_file.max' =>
                'Le fichier ne doit pas dépasser 10 Mo.',
        ]
    );


    /*
    |--------------------------------------------------------------------------
    | PRÉPARER L'IMPORT
    |--------------------------------------------------------------------------
    */

    $import =
        new VehiclePartRequestsImport(
            (int) $validated['vehicle_id']
        );


    /*
    |--------------------------------------------------------------------------
    | IMPORT EXCEL
    |--------------------------------------------------------------------------
    */

    try {

        Excel::import(
            $import,
            $request->file('excel_file')
        );


        /*
        |--------------------------------------------------------------------------
        | RÉSULTAT
        |--------------------------------------------------------------------------
        */

        $imported =
            $import->getImportedCount();

        $matched =
            $import->getMatchedProductsCount();

        $unmatched =
            $import->getUnmatchedProductsCount();

        $errors =
            $import->getErrors();


        /*
        |--------------------------------------------------------------------------
        | SÉCURITÉ SUPPLÉMENTAIRE
        |--------------------------------------------------------------------------
        |
        | Normalement la classe d'import lance déjà une exception si elle trouve
        | une erreur. Cette vérification empêche malgré tout de considérer
        | l'import comme réussi si des erreurs sont présentes.
        |
        */

        if (!empty($errors)) {

            return redirect()
                ->route(
                    'vehicle-part-requests.create'
                )
                ->withInput()
                ->with(
                    'error',
                    'Import annulé. '
                    . count($errors)
                    . ' erreur(s) détectée(s). '
                    . 'Aucune pièce n’a été importée.'
                )
                ->with(
                    'import_errors',
                    $errors
                );
        }


        /*
        |--------------------------------------------------------------------------
        | AUCUNE LIGNE IMPORTÉE
        |--------------------------------------------------------------------------
        */

        if ($imported === 0) {

            return redirect()
                ->route(
                    'vehicle-part-requests.create'
                )
                ->withInput()
                ->with(
                    'error',
                    'Aucune pièce n’a été importée. '
                    . 'Vérifiez le contenu et les en-têtes du fichier Excel.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | IMPORT RÉUSSI
        |--------------------------------------------------------------------------
        */

        $message =
            $imported
            . ' pièce(s) importée(s) avec succès. '
            . $matched
            . ' référence(s) trouvée(s) dans le catalogue et '
            . $unmatched
            . ' référence(s) non trouvée(s).';


        return redirect()
            ->route(
                'vehicle-part-requests.index'
            )
            ->with(
                'success',
                $message
            );

    } catch (\Throwable $e) {

        /*
        |--------------------------------------------------------------------------
        | RÉCUPÉRER LES ERREURS MÉTIER DE L'IMPORT
        |--------------------------------------------------------------------------
        */

        $errors =
            $import->getErrors();


        /*
        |--------------------------------------------------------------------------
        | ERREUR DE VALIDATION DU CONTENU EXCEL
        |--------------------------------------------------------------------------
        */

        if (!empty($errors)) {

            return redirect()
                ->route(
                    'vehicle-part-requests.create'
                )
                ->withInput()
                ->with(
                    'error',
                    'Import annulé. '
                    . count($errors)
                    . ' erreur(s) détectée(s) dans le fichier Excel. '
                    . 'Aucune pièce n’a été importée.'
                )
                ->with(
                    'import_errors',
                    $errors
                );
        }


        /*
        |--------------------------------------------------------------------------
        | AUTRE ERREUR TECHNIQUE
        |--------------------------------------------------------------------------
        */

        report($e);


        return redirect()
            ->route(
                'vehicle-part-requests.create'
            )
            ->withInput()
            ->with(
                'error',
                'L’import Excel a échoué : '
                . $e->getMessage()
            );
    }
}


    /*
    |--------------------------------------------------------------------------
    | TÉLÉCHARGER LE MODÈLE EXCEL
    |--------------------------------------------------------------------------
    */

    public function downloadImportTemplate(): BinaryFileResponse
    {
        /*
        |--------------------------------------------------------------------------
        | CRÉER LE CLASSEUR
        |--------------------------------------------------------------------------
        */

        $spreadsheet =
            new Spreadsheet();

        $sheet =
            $spreadsheet->getActiveSheet();

        $sheet->setTitle(
            'Pieces'
        );


        /*
        |--------------------------------------------------------------------------
        | EN-TÊTES
        |--------------------------------------------------------------------------
        */

        $headers = [
            'REFERENCE',
            'DESIGNATION',
            'QUANTITE',
            'UNITE',
            'FOURNISSEUR',
            'REFERENCE_FOURNISSEUR',
            'PRIX_ESTIME',
            'DESCRIPTION',
            'NOTES',
        ];

        foreach (
            $headers as $columnIndex => $header
        ) {

            $sheet->setCellValue(
                [
                    $columnIndex + 1,
                    1,
                ],
                $header
            );
        }


        /*
        |--------------------------------------------------------------------------
        | EXEMPLES
        |--------------------------------------------------------------------------
        */

        $examples = [
            [
                '17801-30040',
                'Filtre à air',
                2,
                'Pièce',
                '',
                '',
                '',
                'Filtre moteur',
                'Urgent',
            ],

            [
                '23390-0L100',
                'Filtre gasoil',
                1,
                'Pièce',
                '',
                '',
                '',
                '',
                '',
            ],

            [
                'ABC-001',
                'Plaquette de frein avant',
                4,
                'Pièce',
                '',
                '',
                '',
                'Pièce absente du catalogue',
                '',
            ],
        ];

        $rowNumber = 2;

        foreach ($examples as $example) {

            foreach (
                $example as $columnIndex => $value
            ) {

                $sheet->setCellValue(
                    [
                        $columnIndex + 1,
                        $rowNumber,
                    ],
                    $value
                );
            }

            $rowNumber++;
        }


        /*
        |--------------------------------------------------------------------------
        | STYLE DES EN-TÊTES
        |--------------------------------------------------------------------------
        */

        $sheet
            ->getStyle('A1:I1')
            ->getFont()
            ->setBold(true);

        $sheet
            ->getStyle('A1:I1')
            ->getAlignment()
            ->setHorizontal(
                \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
            );

        $sheet
            ->getStyle('A1:I1')
            ->getAlignment()
            ->setVertical(
                \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
            );

        $sheet
            ->getStyle('A1:I1')
            ->getFill()
            ->setFillType(
                \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID
            )
            ->getStartColor()
            ->setARGB('FFD9EAF7');


        /*
        |--------------------------------------------------------------------------
        | LARGEUR DES COLONNES
        |--------------------------------------------------------------------------
        */

        $widths = [
            'A' => 24,
            'B' => 35,
            'C' => 14,
            'D' => 16,
            'E' => 28,
            'F' => 25,
            'G' => 18,
            'H' => 40,
            'I' => 35,
        ];

        foreach ($widths as $column => $width) {

            $sheet
                ->getColumnDimension($column)
                ->setWidth($width);
        }


        /*
        |--------------------------------------------------------------------------
        | FILTRE + FIGER LES EN-TÊTES
        |--------------------------------------------------------------------------
        */

        $sheet->setAutoFilter(
            'A1:I1'
        );

        $sheet->freezePane(
            'A2'
        );


        /*
        |--------------------------------------------------------------------------
        | SAUVEGARDE TEMPORAIRE
        |--------------------------------------------------------------------------
        */

        $directory =
            storage_path(
                'app/maintenance'
            );

        if (!is_dir($directory)) {

            mkdir(
                $directory,
                0755,
                true
            );
        }

        $fileName =
            'modele_import_commandes_pieces.xlsx';

        $filePath =
            $directory
            . DIRECTORY_SEPARATOR
            . $fileName;

        $writer =
            new Xlsx(
                $spreadsheet
            );

        $writer->save(
            $filePath
        );

        $spreadsheet
            ->disconnectWorksheets();


        /*
        |--------------------------------------------------------------------------
        | TÉLÉCHARGEMENT
        |--------------------------------------------------------------------------
        */

        return response()
            ->download(
                $filePath,
                $fileName
            )
            ->deleteFileAfterSend(
                true
            );
    }

    /*
    |--------------------------------------------------------------------------
    | DESTROY
    |--------------------------------------------------------------------------
    */

    public function destroy(
        VehiclePartRequest $vehiclePartRequest
    ): RedirectResponse {

        if (
            auth()->user()->role
            !==
            'admin'
        ) {

            abort(
                403,
                'Seul un administrateur peut supprimer cette demande.'
            );
        }


        $vehiclePartRequest->delete();


        return redirect()
            ->route(
                'vehicle-part-requests.index'
            )
            ->with(
                'success',
                'La demande a été supprimée.'
            );
    }
}
