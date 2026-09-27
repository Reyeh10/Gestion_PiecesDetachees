<?php

namespace App\Http\Controllers;

use App\Models\Depot;
use App\Models\Supplier;
use App\Models\SupplierOrder;
use App\Models\SupplierOrderItem;
use App\Models\User;
use App\Models\VehiclePartRequest;
use App\Notifications\NewSupplierOrderNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
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
        | VALIDATION DU FORMULAIRE
        |--------------------------------------------------------------------------
        */

        $validated = $request->validate([
            'vehicle_part_request_id' => [
                'required',
                'integer',
                'exists:vehicle_part_requests,id',
            ],

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
        ]);

        try {

            /*
            |--------------------------------------------------------------------------
            | TRANSACTION
            |--------------------------------------------------------------------------
            */

            $supplierOrder = DB::transaction(
                function () use ($validated) {

                    /*
                    |--------------------------------------------------------------------------
                    | VERROUILLER LA DEMANDE
                    |--------------------------------------------------------------------------
                    |
                    | Le verrou empêche deux utilisateurs de générer simultanément
                    | deux BC pour la même demande.
                    |
                    |--------------------------------------------------------------------------
                    */

                    $partRequest =
                        VehiclePartRequest::query()
                            ->with([
                                'product',
                                'supplier',
                            ])
                            ->lockForUpdate()
                            ->findOrFail(
                                $validated[
                                    'vehicle_part_request_id'
                                ]
                            );

                    /*
                    |--------------------------------------------------------------------------
                    | VÉRIFIER LE STATUT
                    |--------------------------------------------------------------------------
                    |
                    | SÉCURITÉ SERVEUR :
                    |
                    | Même si quelqu'un tente d'appeler directement la route POST,
                    | le BC ne sera créé que si la pièce est encore COMMANDÉE.
                    |
                    |--------------------------------------------------------------------------
                    */

                    if (
                        $partRequest->status
                        !==
                        VehiclePartRequest::STATUS_ORDERED
                    ) {
                        throw ValidationException::withMessages([
                            'vehicle_part_request_id' =>
                                'Cette pièce n’est plus au statut COMMANDÉE. Le bon de commande ne peut pas être généré.',
                        ]);
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | FOURNISSEUR
                    |--------------------------------------------------------------------------
                    |
                    | Le fournisseur envoyé par le formulaire doit être celui
                    | associé à la demande de pièce.
                    |
                    |--------------------------------------------------------------------------
                    */

                    if (!$partRequest->supplier_id) {
                        throw ValidationException::withMessages([
                            'supplier_id' =>
                                'Aucun fournisseur n’est associé à cette demande de pièce.',
                        ]);
                    }

                    if (
                        (int) $partRequest->supplier_id
                        !==
                        (int) $validated['supplier_id']
                    ) {
                        throw ValidationException::withMessages([
                            'supplier_id' =>
                                'Le fournisseur sélectionné ne correspond pas au fournisseur de la pièce.',
                        ]);
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | EMPÊCHER UN DEUXIÈME BC
                    |--------------------------------------------------------------------------
                    |
                    | Un autre BC actif ne doit pas déjà exister pour cette pièce.
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
                            'vehicle_part_request_id' =>
                                'Cette pièce appartient déjà à un bon de commande actif.',
                        ]);
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | QUANTITÉ COMMANDÉE
                    |--------------------------------------------------------------------------
                    */

                    $quantityOrdered =
                        round(
                            (float) $validated[
                                'quantity_ordered'
                            ],
                            2
                        );

                    /*
                    |--------------------------------------------------------------------------
                    | QUANTITÉ DÉJÀ REÇUE
                    |--------------------------------------------------------------------------
                    |
                    | Normalement, avec la nouvelle règle métier, une pièce
                    | COMMANDÉE ne devrait pas encore avoir de réception.
                    |
                    | Nous conservons néanmoins la valeur existante afin de ne
                    | pas altérer les données historiques éventuelles.
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
                            (float) $validated[
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
                    | REMISE EN POURCENTAGE
                    |--------------------------------------------------------------------------
                    */

                    $discountRate =
                        max(
                            0,
                            min(
                                100,
                                round(
                                    (float) (
                                        $validated['discount_rate']
                                        ?? 0
                                    ),
                                    2
                                )
                            )
                        );

                    $discountAmount =
                        round(
                            $lineTotal
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
                                    $validated['shipping_cost']
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
                                        $validated['tax_rate']
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
                            $lineTotal
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
                    | CRÉATION DU BC
                    |--------------------------------------------------------------------------
                    */

                    $supplierOrder =
                        SupplierOrder::create([
                            'order_number' =>
                                $orderNumber,

                            'supplier_id' =>
                                $partRequest->supplier_id,

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

                            'subtotal' =>
                                $lineTotal,

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
                    | SNAPSHOT DE LA LIGNE
                    |--------------------------------------------------------------------------
                    |
                    | Nous enregistrons les informations de la pièce telles
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
                            $quantityOrdered,

                        'quantity_received' =>
                            $alreadyReceived,

                        'unit_price' =>
                            $unitPrice,

                        'line_total' =>
                            $lineTotal,

                        'notes' =>
                            $partRequest->notes,
                    ]);

                    /*
                    |--------------------------------------------------------------------------
                    | RÉFÉRENCE DU BC DANS LA DEMANDE
                    |--------------------------------------------------------------------------
                    |
                    | La table vehicle_part_requests possède déjà
                    | order_reference.
                    |
                    | Nous synchronisons donc le numéro du BC.
                    |
                    |--------------------------------------------------------------------------
                    */

                    $partRequest->order_reference =
                        $orderNumber;

                    $partRequest->save();

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

            return redirect()
                ->route(
                    'supplier-orders.show',
                    $supplierOrder
                )
                ->with(
                    'success',
                    'Bon de commande '
                    . $supplierOrder->order_number
                    . ' créé avec succès.'
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
            'supplier',
            'depot',
            'creator',
            'approver',
            'items.product',
            'items.vehiclePartRequest.vehicle.customer',
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
        SupplierOrder $supplierOrder
    ): RedirectResponse {


        /*
    |--------------------------------------------------------------------------
    | AUTORISATION
    |--------------------------------------------------------------------------
    */

    abort_unless(
        auth()->check()
        &&
        auth()->user()->role === 'admin',
        403,
        'Seul un administrateur peut approuver un bon de commande.'
    );

        /*
        |--------------------------------------------------------------------------
        | SEUL UN BROUILLON PEUT ÊTRE APPROUVÉ
        |--------------------------------------------------------------------------
        */

        if (!$supplierOrder->isDraft()) {
            return back()->with(
                'error',
                'Seul un bon de commande en brouillon peut être approuvé.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | APPROBATION
        |--------------------------------------------------------------------------
        */

        $supplierOrder->update([
            'status' =>
                SupplierOrder::STATUS_APPROVED,

            'approved_by' =>
                auth()->id(),

            'approved_at' =>
                now(),
        ]);

        return back()->with(
            'success',
            'Bon de commande approuvé.'
        );
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
}
