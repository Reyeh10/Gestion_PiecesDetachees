<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVehiclePartRequestRequest;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Vehicle;
use App\Models\VehiclePartRequest;
use App\Models\VehiclePartRequestHistory;
use App\Models\StockMovement;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class VehiclePartRequestController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | INDEX
    |--------------------------------------------------------------------------
    |
    | Liste de toutes les demandes de pièces :
    | - en recherche
    | - trouvées
    | - commandées
    | - reçues
    | - non trouvées
    | - annulées
    |
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
        | RECHERCHE GÉNÉRALE
        |--------------------------------------------------------------------------
        */

        if ($request->filled('search')) {

            $search = trim(
                (string) $request->input('search')
            );

            $query->where(function ($subQuery) use ($search) {

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
        | FILTRE PAR VÉHICULE
        |--------------------------------------------------------------------------
        */

        if ($request->filled('vehicle_id')) {

            $query->where(
                'vehicle_id',
                $request->input('vehicle_id')
            );
        }

        /*
        |--------------------------------------------------------------------------
        | RÉSULTATS
        |--------------------------------------------------------------------------
        */

        $partRequests = $query
            ->latest('requested_at')
            ->latest('id')
            ->paginate(15);

        $partRequests->appends(
            $request->query()
        );

        /*
        |--------------------------------------------------------------------------
        | VÉHICULES
        |--------------------------------------------------------------------------
        */

        $vehicles = Vehicle::query()
            ->orderBy('brand')
            ->orderBy('model')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | STATUTS
        |--------------------------------------------------------------------------
        */

        $statuses = VehiclePartRequest::statuses();

        /*
        |--------------------------------------------------------------------------
        | VIEW
        |--------------------------------------------------------------------------
        */

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
    */

    public function ordered(Request $request): View
    {
        $query = VehiclePartRequest::query()
            ->with([
                'vehicle.customer',
                'product',
                'supplier',
                'creator',
            ])
            ->where(
                'status',
                VehiclePartRequest::STATUS_ORDERED
            );

        /*
        |--------------------------------------------------------------------------
        | RECHERCHE
        |--------------------------------------------------------------------------
        */

        if ($request->filled('search')) {

            $search = trim(
                (string) $request->input('search')
            );

            $query->where(function ($subQuery) use ($search) {

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
            });
        }

        /*
        |--------------------------------------------------------------------------
        | FILTRE VÉHICULE
        |--------------------------------------------------------------------------
        */

        if ($request->filled('vehicle_id')) {

            $query->where(
                'vehicle_id',
                $request->input('vehicle_id')
            );
        }

        /*
        |--------------------------------------------------------------------------
        | RÉSULTATS
        |--------------------------------------------------------------------------
        */

        $partRequests = $query
            ->latest('ordered_at')
            ->latest('id')
            ->paginate(15);

        $partRequests->appends(
            $request->query()
        );

        /*
        |--------------------------------------------------------------------------
        | VÉHICULES
        |--------------------------------------------------------------------------
        */

        $vehicles = Vehicle::query()
            ->orderBy('brand')
            ->orderBy('model')
            ->get();

        $statuses = VehiclePartRequest::statuses();

        /*
        |--------------------------------------------------------------------------
        | TITRE
        |--------------------------------------------------------------------------
        */

        $pageTitle = 'Pièces commandées';

        $pageDescription =
            'Liste des pièces déjà commandées auprès des fournisseurs.';

        $currentList = 'ordered';

        /*
        |--------------------------------------------------------------------------
        | VIEW
        |--------------------------------------------------------------------------
        */

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
    */

    public function received(Request $request): View
    {
        $query = VehiclePartRequest::query()
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

        /*
        |--------------------------------------------------------------------------
        | RECHERCHE
        |--------------------------------------------------------------------------
        */

        if ($request->filled('search')) {

            $search = trim(
                (string) $request->input('search')
            );

            $query->where(function ($subQuery) use ($search) {

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
            });
        }

        /*
        |--------------------------------------------------------------------------
        | FILTRE VÉHICULE
        |--------------------------------------------------------------------------
        */

        if ($request->filled('vehicle_id')) {

            $query->where(
                'vehicle_id',
                $request->input('vehicle_id')
            );
        }

        /*
        |--------------------------------------------------------------------------
        | RÉSULTATS
        |--------------------------------------------------------------------------
        */

        $partRequests = $query
            ->latest('received_at')
            ->latest('id')
            ->paginate(15);

        $partRequests->appends(
            $request->query()
        );

        /*
        |--------------------------------------------------------------------------
        | VÉHICULES
        |--------------------------------------------------------------------------
        */

        $vehicles = Vehicle::query()
            ->orderBy('brand')
            ->orderBy('model')
            ->get();

        $statuses = VehiclePartRequest::statuses();

        /*
        |--------------------------------------------------------------------------
        | TITRE
        |--------------------------------------------------------------------------
        */

        $pageTitle = 'Pièces reçues';

        $pageDescription =
            'Liste des pièces commandées qui ont été reçues.';

        $currentList = 'received';

        /*
        |--------------------------------------------------------------------------
        | VIEW
        |--------------------------------------------------------------------------
        */

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
    | PIÈCES NON TROUVÉES
    |--------------------------------------------------------------------------
    */

    public function notFound(Request $request): View
    {
        $query = VehiclePartRequest::query()
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

        /*
        |--------------------------------------------------------------------------
        | RECHERCHE
        |--------------------------------------------------------------------------
        */

        if ($request->filled('search')) {

            $search = trim(
                (string) $request->input('search')
            );

            $query->where(function ($subQuery) use ($search) {

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
            });
        }

        /*
        |--------------------------------------------------------------------------
        | FILTRE VÉHICULE
        |--------------------------------------------------------------------------
        */

        if ($request->filled('vehicle_id')) {

            $query->where(
                'vehicle_id',
                $request->input('vehicle_id')
            );
        }

        /*
        |--------------------------------------------------------------------------
        | RÉSULTATS
        |--------------------------------------------------------------------------
        */

        $partRequests = $query
            ->latest('not_found_at')
            ->latest('id')
            ->paginate(15);

        $partRequests->appends(
            $request->query()
        );

        /*
        |--------------------------------------------------------------------------
        | VÉHICULES
        |--------------------------------------------------------------------------
        */

        $vehicles = Vehicle::query()
            ->orderBy('brand')
            ->orderBy('model')
            ->get();

        $statuses = VehiclePartRequest::statuses();

        /*
        |--------------------------------------------------------------------------
        | TITRE
        |--------------------------------------------------------------------------
        */

        $pageTitle = 'Pièces non trouvées';

        $pageDescription =
            'Liste des pièces recherchées mais déclarées introuvables.';

        $currentList = 'not_found';

        /*
        |--------------------------------------------------------------------------
        | VIEW
        |--------------------------------------------------------------------------
        */

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
        /*
        |--------------------------------------------------------------------------
        | VÉHICULES
        |--------------------------------------------------------------------------
        */

        $vehicles = Vehicle::query()
            ->orderBy('brand')
            ->orderBy('model')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | PRODUITS
        |--------------------------------------------------------------------------
        */

        $products = Product::query()
            ->orderBy('designation')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | FOURNISSEURS
        |--------------------------------------------------------------------------
        */

        $suppliers = Supplier::query()
            ->orderBy('name')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | VÉHICULE PRÉ-SÉLECTIONNÉ
        |--------------------------------------------------------------------------
        */

        $selectedVehicleId =
            $request->input('vehicle_id');

        /*
        |--------------------------------------------------------------------------
        | VIEW
        |--------------------------------------------------------------------------
        */

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
    |
    | Création d'une nouvelle demande de pièce.
    |
    */

    public function store(
        StoreVehiclePartRequestRequest $request
    ): RedirectResponse {

        DB::transaction(function () use ($request) {

            /*
            |--------------------------------------------------------------------------
            | CRÉATION DEMANDE
            |--------------------------------------------------------------------------
            */

            $partRequest = VehiclePartRequest::create([

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

                'quantity' =>
                    $request->quantity,

                'unit' =>
                    $request->unit,

                /*
                |--------------------------------------------------------------------------
                | STATUT INITIAL
                |--------------------------------------------------------------------------
                */

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

                /*
                |--------------------------------------------------------------------------
                | DATES
                |--------------------------------------------------------------------------
                */

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

                'notes' =>
                    $request->notes,
            ]);

            /*
            |--------------------------------------------------------------------------
            | HISTORIQUE
            |--------------------------------------------------------------------------
            */

            VehiclePartRequestHistory::create([

                'vehicle_part_request_id' =>
                    $partRequest->id,

                'old_status' =>
                    null,

                'new_status' =>
                    VehiclePartRequest::STATUS_SEARCHING,

                'comment' =>
                    'Création de la demande et début de la recherche.',

                'changed_by' =>
                    Auth::id(),

                'changed_at' =>
                    now(),
            ]);
        });

        /*
        |--------------------------------------------------------------------------
        | REDIRECTION
        |--------------------------------------------------------------------------
        */

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
        | RELATIONS
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
        | STATUTS AUTORISÉS
        |--------------------------------------------------------------------------
        */

        $availableStatuses = collect(
            $vehiclePartRequest->availableNextStatuses()
        )->mapWithKeys(function ($status) {

            return [
                $status =>
                    VehiclePartRequest::statuses()[$status],
            ];
        });

        /*
        |--------------------------------------------------------------------------
        | FOURNISSEURS
        |--------------------------------------------------------------------------
        */

        $suppliers = Supplier::query()
            ->orderBy('name')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | VIEW
        |--------------------------------------------------------------------------
        */

        return view(
            'vehicle-part-requests.show',
            compact(
                'vehiclePartRequest',
                'availableStatuses',
                'suppliers'
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

        /*
        |--------------------------------------------------------------------------
        | VÉHICULES
        |--------------------------------------------------------------------------
        */

        $vehicles = Vehicle::query()
            ->orderBy('brand')
            ->orderBy('model')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | PRODUITS
        |--------------------------------------------------------------------------
        */

        $products = Product::query()
            ->orderBy('designation')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | FOURNISSEURS
        |--------------------------------------------------------------------------
        */

        $suppliers = Supplier::query()
            ->orderBy('name')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | VIEW
        |--------------------------------------------------------------------------
        */

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
    | UPDATE
    |--------------------------------------------------------------------------
    */

    public function update(
        StoreVehiclePartRequestRequest $request,
        VehiclePartRequest $vehiclePartRequest
    ): RedirectResponse {

        /*
        |--------------------------------------------------------------------------
        | MODIFICATION
        |--------------------------------------------------------------------------
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
                'Les informations de la pièce ont été modifiées.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | CHANGE STATUS
    |--------------------------------------------------------------------------
    |
    | IMPORTANT :
    |
    | Lorsque la demande devient REÇUE :
    |
    | 1. Le statut de la demande devient REÇUE.
    | 2. La date received_at est enregistrée.
    | 3. La quantité reçue du produit augmente.
    | 4. La quantité disponible du produit augmente.
    | 5. Le produit passe automatiquement à DISPONIBLE.
    | 6. Un mouvement de stock entrant est enregistré.
    |
    */

    public function changeStatus(
        Request $request,
        VehiclePartRequest $vehiclePartRequest
    ): RedirectResponse {

        /*
        |--------------------------------------------------------------------------
        | VALIDATION
        |--------------------------------------------------------------------------
        */

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

        /*
        |--------------------------------------------------------------------------
        | STATUTS
        |--------------------------------------------------------------------------
        */

        $newStatus =
            $request->input('status');

        $oldStatus =
            $vehiclePartRequest->status;

        /*
        |--------------------------------------------------------------------------
        | VÉRIFICATION DU CHANGEMENT DE STATUT
        |--------------------------------------------------------------------------
        */

        if (
            !$vehiclePartRequest->canChangeTo(
                $newStatus
            )
        ) {

            return back()
                ->with(
                    'error',
                    'Ce changement de statut n’est pas autorisé.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | TRANSACTION
        |--------------------------------------------------------------------------
        */

        DB::transaction(function () use (
            $request,
            $vehiclePartRequest,
            $oldStatus,
            $newStatus
        ) {

            /*
            |--------------------------------------------------------------------------
            | VERROUILLER LA DEMANDE
            |--------------------------------------------------------------------------
            |
            | Permet d'éviter que deux utilisateurs réceptionnent la même pièce
            | exactement au même moment.
            |
            */

            $lockedPartRequest =
                VehiclePartRequest::query()
                    ->lockForUpdate()
                    ->findOrFail(
                        $vehiclePartRequest->id
                    );

            /*
            |--------------------------------------------------------------------------
            | DONNÉES À METTRE À JOUR
            |--------------------------------------------------------------------------
            */

            $updateData = [

                'status' =>
                    $newStatus,
            ];

            /*
            |--------------------------------------------------------------------------
            | DATES AUTOMATIQUES
            |--------------------------------------------------------------------------
            */

            switch ($newStatus) {

                /*
                |--------------------------------------------------------------------------
                | EN RECHERCHE
                |--------------------------------------------------------------------------
                */

                case VehiclePartRequest::STATUS_SEARCHING:

                    $updateData['search_started_at'] =
                        $lockedPartRequest->search_started_at
                        ?? now();

                    $updateData['not_found_at'] =
                        null;

                    break;

                /*
                |--------------------------------------------------------------------------
                | COMMANDÉE
                |--------------------------------------------------------------------------
                */

                case VehiclePartRequest::STATUS_ORDERED:

                    $updateData['ordered_at'] =
                        now();

                    $updateData['not_found_at'] =
                        null;

                    /*
                    |--------------------------------------------------------------------------
                    | FOURNISSEUR
                    |--------------------------------------------------------------------------
                    */

                    if (
                        $request->filled(
                            'supplier_id'
                        )
                    ) {

                        $updateData['supplier_id'] =
                            $request->supplier_id;
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | RÉFÉRENCE DE COMMANDE
                    |--------------------------------------------------------------------------
                    */

                    if (
                        $request->filled(
                            'order_reference'
                        )
                    ) {

                        $updateData['order_reference'] =
                            $request->order_reference;
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | PRIX D'ACHAT
                    |--------------------------------------------------------------------------
                    */

                    if (
                        $request->filled(
                            'purchase_price'
                        )
                    ) {

                        $updateData['purchase_price'] =
                            $request->purchase_price;
                    }

                    break;

                /*
                |--------------------------------------------------------------------------
                | REÇUE
                |--------------------------------------------------------------------------
                */

                case VehiclePartRequest::STATUS_RECEIVED:

                    $updateData['received_at'] =
                        now();

                    break;

                /*
                |--------------------------------------------------------------------------
                | NON TROUVÉE
                |--------------------------------------------------------------------------
                */

                case VehiclePartRequest::STATUS_NOT_FOUND:

                    $updateData['not_found_at'] =
                        now();

                    break;

                /*
                |--------------------------------------------------------------------------
                | ANNULÉE
                |--------------------------------------------------------------------------
                */

                case VehiclePartRequest::STATUS_CANCELLED:

                    $updateData['cancelled_at'] =
                        now();

                    break;
            }

            /*
            |--------------------------------------------------------------------------
            | MISE À JOUR DE LA DEMANDE
            |--------------------------------------------------------------------------
            */

            $lockedPartRequest->update(
                $updateData
            );

            /*
            |--------------------------------------------------------------------------
            | TRAITEMENT DE LA RÉCEPTION
            |--------------------------------------------------------------------------
            |
            | IMPORTANT :
            |
            | Cette partie ne doit s'exécuter QUE lorsque la demande vient
            | réellement de passer au statut REÇUE.
            |
            | Elle ne doit jamais s'exécuter deux fois pour la même demande.
            |
            */

            if (
                $newStatus ===
                    VehiclePartRequest::STATUS_RECEIVED

                &&

                $oldStatus !==
                    VehiclePartRequest::STATUS_RECEIVED
            ) {

                /*
                |--------------------------------------------------------------------------
                | PRODUIT ASSOCIÉ
                |--------------------------------------------------------------------------
                */

                if (
                    !empty(
                        $lockedPartRequest->product_id
                    )
                ) {

                    /*
                    |--------------------------------------------------------------------------
                    | VERROUILLER LE PRODUIT
                    |--------------------------------------------------------------------------
                    */

                    $product = Product::query()
                        ->lockForUpdate()
                        ->find(
                            $lockedPartRequest->product_id
                        );

                    /*
                    |--------------------------------------------------------------------------
                    | SI LE PRODUIT EXISTE
                    |--------------------------------------------------------------------------
                    */

                    if ($product) {

                        /*
                        |--------------------------------------------------------------------------
                        | QUANTITÉ REÇUE
                        |--------------------------------------------------------------------------
                        |
                        | Dans votre fonctionnement actuel :
                        |
                        | quantité demandée = quantité commandée = quantité reçue
                        |
                        | Exemple :
                        |
                        | Demande = 5 pièces
                        | Passage à REÇUE
                        | => réception de 5 pièces.
                        |
                        */

                        $receivedQuantity =
                            (float) (
                                $lockedPartRequest->quantity
                                ?? 0
                            );

                        /*
                        |--------------------------------------------------------------------------
                        | VALIDATION DE SÉCURITÉ
                        |--------------------------------------------------------------------------
                        */

                        if (
                            $receivedQuantity <= 0
                        ) {

                            throw ValidationException::withMessages([

                                'quantity' =>
                                    'Impossible de réceptionner cette pièce : la quantité doit être supérieure à zéro.',
                            ]);
                        }

                        /*
                        |--------------------------------------------------------------------------
                        | ANCIENNES QUANTITÉS
                        |--------------------------------------------------------------------------
                        */

                        $currentReceivedQuantity =
                            (float) (
                                $product->received_quantity
                                ?? 0
                            );

                        $currentAvailableQuantity =
                            (float) (
                                $product->quantity
                                ?? 0
                            );

                        /*
                        |--------------------------------------------------------------------------
                        | QUANTITÉ INITIALE
                        |--------------------------------------------------------------------------
                        */

                        $initialQuantity =
                            (float) (
                                $product->initial_quantity
                                ?? 0
                            );

                        /*
                        |--------------------------------------------------------------------------
                        | QUANTITÉ RESTANTE À RECEVOIR
                        |--------------------------------------------------------------------------
                        |
                        | Exemple :
                        |
                        | initial_quantity  = 5
                        | received_quantity = 3
                        |
                        | restant = 2
                        |
                        */

                        $remainingQuantity =
                            max(
                                0,
                                $initialQuantity
                                - $currentReceivedQuantity
                            );

                        /*
                        |--------------------------------------------------------------------------
                        | QUANTITÉ À AJOUTER
                        |--------------------------------------------------------------------------
                        |
                        | Si initial_quantity existe, on ne dépasse jamais
                        | la quantité initialement prévue.
                        |
                        */

                        if (
                            $initialQuantity > 0
                        ) {

                            $quantityToReceive =
                                min(
                                    $receivedQuantity,
                                    $remainingQuantity
                                );

                        } else {

                            /*
                            |--------------------------------------------------------------------------
                            | ANCIEN PRODUIT SANS QUANTITÉ INITIALE
                            |--------------------------------------------------------------------------
                            |
                            | Pour assurer la compatibilité avec d'anciens produits.
                            |
                            */

                            $quantityToReceive =
                                $receivedQuantity;
                        }

                        /*
                        |--------------------------------------------------------------------------
                        | SI AUCUNE QUANTITÉ NE RESTE À RECEVOIR
                        |--------------------------------------------------------------------------
                        */

                        if (
                            $quantityToReceive <= 0
                        ) {

                            /*
                            |--------------------------------------------------------------------------
                            | LE PRODUIT PEUT TOUT DE MÊME ÊTRE DISPONIBLE
                            |--------------------------------------------------------------------------
                            */

                            if (
                                $currentAvailableQuantity > 0
                            ) {

                                $product->status =
                                    'disponible';

                                $product->save();
                            }

                        } else {

                            /*
                            |--------------------------------------------------------------------------
                            | NOUVELLE QUANTITÉ REÇUE
                            |--------------------------------------------------------------------------
                            */

                            $newReceivedQuantity =
                                $currentReceivedQuantity
                                + $quantityToReceive;

                            /*
                            |--------------------------------------------------------------------------
                            | NOUVELLE QUANTITÉ DISPONIBLE
                            |--------------------------------------------------------------------------
                            */

                            $newAvailableQuantity =
                                $currentAvailableQuantity
                                + $quantityToReceive;

                            /*
                            |--------------------------------------------------------------------------
                            | METTRE À JOUR LE PRODUIT
                            |--------------------------------------------------------------------------
                            |
                            | Exemple :
                            |
                            | initial_quantity  = 5
                            | received_quantity = 0
                            | quantity          = 0
                            |
                            | Réception de 5 :
                            |
                            | initial_quantity  = 5
                            | received_quantity = 5
                            | quantity          = 5
                            | status            = disponible
                            |
                            */

                            $product->received_quantity =
                                $newReceivedQuantity;

                            $product->quantity =
                                $newAvailableQuantity;

                            /*
                            |--------------------------------------------------------------------------
                            | IMPORTANT : STATUT PRODUIT
                            |--------------------------------------------------------------------------
                            |
                            | Dès qu'au moins une pièce physique est disponible,
                            | le produit devient DISPONIBLE.
                            |
                            */

                            if (
                                $newAvailableQuantity > 0
                            ) {

                                $product->status =
                                    'disponible';

                            } else {

                                $product->status =
                                    'non_disponible';
                            }

                            /*
                            |--------------------------------------------------------------------------
                            | SAUVEGARDE
                            |--------------------------------------------------------------------------
                            */

                            $product->save();

                            /*
                            |--------------------------------------------------------------------------
                            | MOUVEMENT DE STOCK
                            |--------------------------------------------------------------------------
                            |
                            | On conserve une trace de l'entrée de stock.
                            |
                            */

                            StockMovement::create([

                                'product_id' =>
                                    $product->id,

                                'type' =>
                                    'in',

                                'quantity' =>
                                    $quantityToReceive,

                                'source' =>
                                    'Réception pièce véhicule',

                                'reference' =>
                                    $product->reference,

                                'user_id' =>
                                    Auth::id(),
                            ]);
                        }
                    }
                }
            }

            /*
            |--------------------------------------------------------------------------
            | HISTORIQUE DU CHANGEMENT DE STATUT
            |--------------------------------------------------------------------------
            */

            VehiclePartRequestHistory::create([

                'vehicle_part_request_id' =>
                    $lockedPartRequest->id,

                'old_status' =>
                    $oldStatus,

                'new_status' =>
                    $newStatus,

                'comment' =>
                    $request->comment,

                'changed_by' =>
                    Auth::id(),

                'changed_at' =>
                    now(),
            ]);
        });

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
                $newStatus === VehiclePartRequest::STATUS_RECEIVED
                    ? 'La pièce a été reçue. Le produit et le stock disponible ont été mis à jour automatiquement.'
                    : 'Le statut de la pièce a été mis à jour.'
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

        /*
        |--------------------------------------------------------------------------
        | ADMIN UNIQUEMENT
        |--------------------------------------------------------------------------
        */

        if (
            auth()->user()->role !== 'admin'
        ) {

            abort(
                403,
                'Seul un administrateur peut supprimer cette demande.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | SUPPRESSION
        |--------------------------------------------------------------------------
        */

        $vehiclePartRequest->delete();

        /*
        |--------------------------------------------------------------------------
        | REDIRECTION
        |--------------------------------------------------------------------------
        */

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