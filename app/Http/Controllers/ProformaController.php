<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Proforma;
use App\Models\ProformaItem;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Vehicle;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

class ProformaController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | LISTE DES PROFORMAS
    |--------------------------------------------------------------------------
    */

    public function index(Request $request): View
    {
        /*
        |--------------------------------------------------------------------------
        | PARAMÈTRES DE RECHERCHE
        |--------------------------------------------------------------------------
        */

        $search = trim(
            (string) $request->input(
                'search',
                ''
            )
        );

        $status = trim(
            (string) $request->input(
                'status',
                ''
            )
        );


        /*
        |--------------------------------------------------------------------------
        | REQUÊTE DE BASE
        |--------------------------------------------------------------------------
        */

        $query = Proforma::query()
            ->with([
                'customer',
                'vehicle',
                'creator',
                'sale',
            ]);


        /*
        |--------------------------------------------------------------------------
        | RECHERCHE
        |--------------------------------------------------------------------------
        |
        | Accepte :
        | PROFORMA-000001
        | PROFORMA000001
        | 000001
        | 1
        |
        | Et fonctionne même si proforma_number est NULL pour un ancien
        | enregistrement.
        |
        */

        if ($search !== '') {

            $query->where(
                function ($q) use ($search) {

                    $proformaId = null;

                    if (
                        preg_match(
                            '/^PROFORMA[-\s]?0*(\d+)$/i',
                            $search,
                            $matches
                        )
                    ) {
                        $proformaId = (int) $matches[1];

                    } elseif (
                        ctype_digit($search)
                    ) {
                        $proformaId = (int) $search;
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | ID / NUMÉRO PROFORMA
                    |--------------------------------------------------------------------------
                    */

                    if (
                        $proformaId !== null
                        && $proformaId > 0
                    ) {

                        $q->where(
                            'id',
                            $proformaId
                        );

                        $q->orWhere(
                            'proforma_number',
                            'like',
                            '%' . $search . '%'
                        );

                    } else {

                        $q->where(
                            'proforma_number',
                            'like',
                            '%' . $search . '%'
                        );
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | CLIENT
                    |--------------------------------------------------------------------------
                    */

                    $q->orWhereHas(
                        'customer',
                        function ($customerQuery) use ($search) {

                            $customerQuery
                                ->where(
                                    'name',
                                    'like',
                                    '%' . $search . '%'
                                )
                                ->orWhere(
                                    'phone',
                                    'like',
                                    '%' . $search . '%'
                                )
                                ->orWhere(
                                    'email',
                                    'like',
                                    '%' . $search . '%'
                                );
                        }
                    );


                    /*
                    |--------------------------------------------------------------------------
                    | VÉHICULE
                    |--------------------------------------------------------------------------
                    */

                    $q->orWhereHas(
                        'vehicle',
                        function ($vehicleQuery) use ($search) {

                            $vehicleQuery
                                ->where(
                                    'plate_number',
                                    'like',
                                    '%' . $search . '%'
                                )
                                ->orWhere(
                                    'vin',
                                    'like',
                                    '%' . $search . '%'
                                )
                                ->orWhere(
                                    'brand',
                                    'like',
                                    '%' . $search . '%'
                                )
                                ->orWhere(
                                    'model',
                                    'like',
                                    '%' . $search . '%'
                                );
                        }
                    );
                }
            );
        }


        /*
        |--------------------------------------------------------------------------
        | FILTRE STATUT
        |--------------------------------------------------------------------------
        */

        if ($status !== '') {

            $query->where(
                'status',
                $status
            );
        }


        /*
        |--------------------------------------------------------------------------
        | LISTE
        |--------------------------------------------------------------------------
        */

        $proformas = $query
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();


        return view(
            'proformas.index',
            compact(
                'proformas',
                'search',
                'status'
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | FORMULAIRE DE CRÉATION
    |--------------------------------------------------------------------------
    */

    public function create(): View
    {
        $products = Product::query()
            ->with(['brand', 'model', 'depotStocks.depot'])
            ->where('quantity', '>', 0)
            ->where('status', '!=', 'vendu')
            ->orderBy('designation')
            ->get();

        $customers = Customer::query()
            ->orderBy('name')
            ->get();

        return view(
            'proformas.create',
            compact('products', 'customers')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | FORMULAIRE DE CRÉATION AVEC VÉHICULE
    |--------------------------------------------------------------------------
    */

    public function createWithVehicle(
        Vehicle $vehicle
    ): View|RedirectResponse {
        $products = Product::query()
            ->with(['brand', 'model', 'depotStocks.depot'])
            ->where('quantity', '>', 0)
            ->where('status', '!=', 'vendu')
            ->orderBy('designation')
            ->get();

        $customers = Customer::query()
            ->orderBy('name')
            ->get();

        return view(
            'proformas.create',
            [
                'products' => $products,
                'customers' => $customers,
                'selectedVehicle' => $vehicle,
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | VÉHICULES DU CLIENT
    |--------------------------------------------------------------------------
    */

    public function vehiclesByCustomer(
        Customer $customer
    ): JsonResponse {
        $vehicles = Vehicle::query()
            ->where('customer_id', $customer->id)
            ->orderBy('plate_number')
            ->get([
                'id',
                'customer_id',
                'plate_number',
                'brand',
                'model',
            ])
            ->map(function (Vehicle $vehicle): array {
                $description = trim(
                    implode(
                        ' ',
                        array_filter([
                            $vehicle->brand,
                            $vehicle->model,
                        ])
                    )
                );

                return [
                    'id' => $vehicle->id,
                    'customer_id' => $vehicle->customer_id,
                    'plate_number' => $vehicle->plate_number,
                    'brand' => $vehicle->brand,
                    'model' => $vehicle->model,
                    'label' => $description !== ''
                        ? $vehicle->plate_number . ' - ' . $description
                        : $vehicle->plate_number,
                ];
            })
            ->values();

        return response()->json([
            'success' => true,
            'vehicles' => $vehicles,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | ENREGISTRER LE PROFORMA
    |--------------------------------------------------------------------------
    */

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate(
            [
                'customer_id' => [
                    'required',
                    'integer',
                    'exists:customers,id',
                ],

                'vehicle_id' => [
                    'required',
                    'integer',
                    Rule::exists('vehicles', 'id')
                        ->where(
                            fn ($query) => $query->where(
                                'customer_id',
                                $request->input('customer_id')
                            )
                        ),
                ],

                'payment_type' => [
                    'required',
                    'in:Cash,Bon de commande,Echeance',
                ],

                'discount' => [
                    'nullable',
                    'numeric',
                    'min:0',
                    'max:100',
                ],

                'items' => [
                    'required',
                    'array',
                    'min:1',
                ],

                'items.*.product_id' => [
                    'required',
                    'integer',
                    'exists:products,id',
                ],

                'items.*.quantity' => [
                    'required',
                    'numeric',
                    'min:0.01',
                ],
            ],
            [
                'customer_id.required' =>
                    'Veuillez sélectionner un client.',

                'vehicle_id.required' =>
                    'Veuillez sélectionner le véhicule associé au client.',

                'vehicle_id.exists' =>
                    'Le véhicule sélectionné n’appartient pas au client choisi.',

                'payment_type.required' =>
                    'Veuillez sélectionner le mode de paiement.',

                'items.required' =>
                    'Vous devez ajouter au moins un produit.',

                'items.min' =>
                    'Vous devez ajouter au moins un produit.',

                'items.*.product_id.required' =>
                    'Veuillez sélectionner un produit.',

                'items.*.quantity.required' =>
                    'La quantité est obligatoire.',

                'items.*.quantity.min' =>
                    'La quantité doit être supérieure à zéro.',
            ]
        );

        DB::beginTransaction();

        try {
            $vehicle = Vehicle::query()
                ->whereKey($validated['vehicle_id'])
                ->where('customer_id', $validated['customer_id'])
                ->lockForUpdate()
                ->firstOrFail();

            $subtotal = 0.00;
            $validatedItems = [];

            foreach ($validated['items'] as $itemData) {
                $product = Product::query()
                    ->lockForUpdate()
                    ->findOrFail($itemData['product_id']);

                $quantity = round(
                    (float) $itemData['quantity'],
                    2
                );

                $availableQuantity = round(
                    (float) $product->quantity,
                    2
                );

                if ($quantity > $availableQuantity) {
                    DB::rollBack();

                    return back()
                        ->withInput()
                        ->with(
                            'error',
                            'Stock insuffisant pour : '
                            . $product->reference
                            . ' - '
                            . $product->designation
                            . ' | Disponible : '
                            . number_format(
                                $availableQuantity,
                                2,
                                ',',
                                ' '
                            )
                            . ' '
                            . ($product->unit_label ?? 'Pièce')
                        );
                }

                $price = round(
                    (float) $product->sale_price,
                    2
                );

                $lineTotal = round(
                    $quantity * $price,
                    2
                );

                $subtotal += $lineTotal;

                $validatedItems[] = [
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'price' => $price,
                    'total' => $lineTotal,
                ];
            }

            $subtotal = round($subtotal, 2);

            $discountPercent = round(
                (float) ($validated['discount'] ?? 0),
                2
            );

            $discountAmount = round(
                ($subtotal * $discountPercent) / 100,
                2
            );

            $taxable = max(
                0,
                round(
                    $subtotal - $discountAmount,
                    2
                )
            );

            $tva = round(
                $taxable * 0.10,
                2
            );

            $total = round(
                $taxable + $tva,
                2
            );

            /*
            |--------------------------------------------------------------------------
            | NUMÉRO DU PROFORMA
            |--------------------------------------------------------------------------
            */

            $nextId = ((int) Proforma::max('id')) + 1;

            $proformaNumber =
                'PROFORMA-'
                . str_pad(
                    (string) $nextId,
                    6,
                    '0',
                    STR_PAD_LEFT
                );

            /*
            |--------------------------------------------------------------------------
            | CRÉER LE PROFORMA
            |--------------------------------------------------------------------------
            */

            $proforma = Proforma::create([
                'proforma_number' => $proformaNumber,

                'customer_id' => $validated['customer_id'],
                'vehicle_id' => $vehicle->id,
                'created_by' => auth()->id(),

                'payment_type' => $validated['payment_type'],

                'subtotal' => $subtotal,
                'discount' => $discountPercent,
                'discount_amount' => $discountAmount,
                'tva' => $tva,
                'total' => $total,

                'status' => Proforma::STATUS_VALIDATED,
            ]);

            /*
            |--------------------------------------------------------------------------
            | ENREGISTRER LES PRODUITS
            |--------------------------------------------------------------------------
            |
            | IMPORTANT :
            | on ne diminue PAS le stock lors de la création du proforma.
            |--------------------------------------------------------------------------
            */

            foreach ($validatedItems as $item) {
                ProformaItem::create([
                    'proforma_id' => $proforma->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'total' => $item['total'],
                ]);
            }

            DB::commit();

            return redirect()
                ->route('proformas.show', $proforma)
                ->with(
                    'success',
                    'Le proforma a été créé avec succès.'
                );

        } catch (Throwable $e) {
            DB::rollBack();

            Log::error(
                'Création proforma impossible.',
                [
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'user_id' => auth()->id(),
                    'customer_id' => $validated['customer_id'] ?? null,
                    'vehicle_id' => $validated['vehicle_id'] ?? null,
                    'payment_type' => $validated['payment_type'] ?? null,
                ]
            );

            return back()
                ->withInput()
                ->with(
                    'error',
                    app()->environment('local')
                        ? 'ERREUR : ' . $e->getMessage()
                        : 'La création du proforma a échoué.'
                );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | AFFICHER LE PROFORMA
    |--------------------------------------------------------------------------
    */

    public function show(
        Proforma $proforma
    ): View {
        $proforma->load([
            'customer',
            'vehicle',
            'creator',
            'sale',
            'items.product',
        ]);

        return view(
            'proformas.show',
            compact('proforma')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | PDF
    |--------------------------------------------------------------------------
    */

    public function download(
        Proforma $proforma
    ) {
        $proforma->load([
            'customer',
            'vehicle',
            'creator',
            'items.product',
        ]);

        $safeNumber = preg_replace(
            '/[^A-Za-z0-9\-_]/',
            '-',
            $proforma->proforma_number
        );

        return Pdf::loadView(
            'proformas.pdf',
            [
                'proforma' => $proforma,
                'isPdf' => true,
            ]
        )
            ->setPaper('a4', 'portrait')
            ->download($safeNumber . '.pdf');
    }

   /*
    |--------------------------------------------------------------------------
    | CONVERTIR LE PROFORMA EN VENTE
    |--------------------------------------------------------------------------
    */
    public function convertToSale(
        Proforma $proforma
    ): RedirectResponse {

        try {

            $sale = DB::transaction(
                function () use ($proforma) {

                    /*
                    |--------------------------------------------------------------------------
                    | VERROUILLER LE PROFORMA
                    |--------------------------------------------------------------------------
                    */

                    $locked = Proforma::query()
                        ->with([
                            'items.product',
                            'vehicle',
                        ])
                        ->lockForUpdate()
                        ->findOrFail(
                            $proforma->id
                        );


                    /*
                    |--------------------------------------------------------------------------
                    | DÉJÀ CONVERTI
                    |--------------------------------------------------------------------------
                    |
                    | Aucun doublon de vente.
                    | On retourne simplement la vente existante.
                    |
                    */

                    if (
                        $locked->status
                        === Proforma::STATUS_CONVERTED
                    ) {

                        if (!$locked->sale_id) {

                            throw new \RuntimeException(
                                'Ce proforma est marqué comme converti, '
                                . 'mais aucune vente associée n’a été trouvée.'
                            );
                        }

                        return Sale::findOrFail(
                            $locked->sale_id
                        );
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | PROFORMA ANNULÉ
                    |--------------------------------------------------------------------------
                    */

                    if (
                        $locked->status
                        === Proforma::STATUS_CANCELLED
                    ) {

                        throw new \RuntimeException(
                            'Un proforma annulé ne peut pas être converti.'
                        );
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | VÉRIFIER LES ARTICLES
                    |--------------------------------------------------------------------------
                    */

                    if ($locked->items->isEmpty()) {

                        throw new \RuntimeException(
                            'Ce proforma ne contient aucun produit.'
                        );
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | VÉRIFIER LE STOCK
                    |--------------------------------------------------------------------------
                    */

                    foreach (
                        $locked->items as $item
                    ) {

                        $product =
                            Product::query()
                                ->lockForUpdate()
                                ->findOrFail(
                                    $item->product_id
                                );


                        $requestedQuantity =
                            round(
                                (float) $item->quantity,
                                2
                            );


                        $availableQuantity =
                            round(
                                (float) $product->quantity,
                                2
                            );


                        if (
                            $requestedQuantity
                            > $availableQuantity
                        ) {

                            throw new \RuntimeException(

                                'Stock insuffisant pour : '
                                . $product->reference
                                . ' - '
                                . $product->designation
                                . '. Disponible : '
                                . number_format(
                                    $availableQuantity,
                                    2,
                                    ',',
                                    ' '
                                )
                                . ' '
                                . (
                                    $product->unit_label
                                    ?? 'Pièce'
                                )

                            );
                        }
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | NUMÉRO FACTURE
                    |--------------------------------------------------------------------------
                    */

                    $nextSaleId =
                        ((int) Sale::max('id'))
                        + 1;


                    $invoiceNumber =
                        'FACT-'
                        . date('Y')
                        . '-'
                        . str_pad(
                            (string) $nextSaleId,
                            4,
                            '0',
                            STR_PAD_LEFT
                        );


                    /*
                    |--------------------------------------------------------------------------
                    | CRÉER LA VENTE
                    |--------------------------------------------------------------------------
                    */

                    $sale = Sale::create([

                        'customer_id' =>
                            $locked->customer_id,

                        'vehicle_id' =>
                            $locked->vehicle_id,

                        'payment_type' =>
                            $locked->payment_type,

                        'subtotal' =>
                            $locked->subtotal,

                        'discount' =>
                            $locked->discount,

                        'discount_amount' =>
                            $locked->discount_amount,

                        'tva' =>
                            $locked->tva,

                        'total' =>
                            $locked->total,

                        'status' =>
                            'vendu',

                        'document_type' =>
                            'sale',

                        'invoice_number' =>
                            $invoiceNumber,

                    ]);


                    /*
                    |--------------------------------------------------------------------------
                    | CRÉER LES LIGNES DE VENTE
                    |--------------------------------------------------------------------------
                    */

                    foreach (
                        $locked->items as $item
                    ) {

                        $product =
                            Product::query()
                                ->lockForUpdate()
                                ->findOrFail(
                                    $item->product_id
                                );


                        SaleItem::create([

                            'sale_id' =>
                                $sale->id,

                            'product_id' =>
                                $product->id,

                            'quantity' =>
                                $item->quantity,

                            'price' =>
                                $item->price,

                        ]);


                        /*
                        |--------------------------------------------------------------------------
                        | DIMINUER LE STOCK
                        |--------------------------------------------------------------------------
                        */

                        $product->quantity =
                            max(
                                0,
                                round(
                                    (float) $product->quantity
                                    -
                                    (float) $item->quantity,
                                    2
                                )
                            );


                        /*
                        |--------------------------------------------------------------------------
                        | STATUT PRODUIT
                        |--------------------------------------------------------------------------
                        */

                        $product->status =
                            $product->quantity <= 0
                                ? 'vendu'
                                : 'disponible';


                        $product->save();
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | MARQUER LE PROFORMA COMME CONVERTI
                    |--------------------------------------------------------------------------
                    */

                    $locked->update([

                        'status' =>
                            Proforma::STATUS_CONVERTED,

                        'sale_id' =>
                            $sale->id,

                        'converted_at' =>
                            now(),

                        'converted_by' =>
                            auth()->id(),

                    ]);


                    /*
                    |--------------------------------------------------------------------------
                    | RECHARGER LA VENTE
                    |--------------------------------------------------------------------------
                    */

                    $sale->refresh();


                    return $sale;

                }
            );


            /*
            |--------------------------------------------------------------------------
            | REDIRECTION VERS LA FACTURE HTML
            |--------------------------------------------------------------------------
            |
            | IMPORTANT :
            | cette route ne doit PAS télécharger le PDF.
            |
            */

            return redirect()
                ->route(
                    'sales.invoice',
                    $sale->id
                )
                ->with(
                    'success',
                    'Le proforma a été converti en vente avec succès.'
                );


        } catch (\RuntimeException $e) {

            return back()
                ->with(
                    'error',
                    $e->getMessage()
                );


        } catch (Throwable $e) {

            Log::error(
                'Conversion du proforma impossible.',
                [
                    'proforma_id' =>
                        $proforma->id,

                    'message' =>
                        $e->getMessage(),

                    'file' =>
                        $e->getFile(),

                    'line' =>
                        $e->getLine(),

                    'user_id' =>
                        auth()->id(),
                ]
            );


            return back()
                ->with(
                    'error',
                    app()->environment('local')
                        ? 'ERREUR : '
                            . $e->getMessage()
                        : 'Une erreur est survenue pendant la conversion.'
                );
        }
    }
    /*
    |--------------------------------------------------------------------------
    | ANNULER
    |--------------------------------------------------------------------------
    */

    public function cancel(
        Proforma $proforma
    ): RedirectResponse {
        if (
            $proforma->status === Proforma::STATUS_CONVERTED
        ) {
            return back()->with(
                'error',
                'Un proforma converti ne peut pas être annulé.'
            );
        }

        if (
            $proforma->status === Proforma::STATUS_CANCELLED
        ) {
            return back()->with(
                'info',
                'Ce proforma est déjà annulé.'
            );
        }

        $proforma->update([
            'status' => Proforma::STATUS_CANCELLED,
            'cancelled_at' => now(),
            'cancelled_by' => auth()->id(),
        ]);

        return redirect()
            ->route('proformas.index')
            ->with(
                'success',
                'Le proforma a été annulé.'
            );
    }
}