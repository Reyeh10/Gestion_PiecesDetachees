<?php

namespace App\Http\Controllers;

use App\Models\Depot;
use App\Models\DepotTransfer;
use App\Models\Product;
use App\Models\ProductDepotStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class DepotTransferController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | INDEX
    |--------------------------------------------------------------------------
    */
    public function index(Request $request)
    {
        $query = DepotTransfer::with([
            'product',
            'sourceDepot',
            'destinationDepot',
            'user',
        ])->latest();

        /*
        |--------------------------------------------------------------------------
        | RECHERCHE
        |--------------------------------------------------------------------------
        */
        if ($request->filled('search')) {

            $search = trim($request->search);

            $query->where(function ($q) use ($search) {

                $q->whereHas('product', function ($productQuery) use ($search) {

                    $productQuery
                        ->where('reference', 'like', '%' . $search . '%')
                        ->orWhere('designation', 'like', '%' . $search . '%');

                })
                ->orWhereHas('sourceDepot', function ($depotQuery) use ($search) {

                    $depotQuery->where(
                        'name',
                        'like',
                        '%' . $search . '%'
                    );

                })
                ->orWhereHas('destinationDepot', function ($depotQuery) use ($search) {

                    $depotQuery->where(
                        'name',
                        'like',
                        '%' . $search . '%'
                    );

                });

            });
        }

        /*
        |--------------------------------------------------------------------------
        | FILTRE DÉPÔT SOURCE
        |--------------------------------------------------------------------------
        */
        if ($request->filled('source_depot_id')) {

            $query->where(
                'source_depot_id',
                $request->source_depot_id
            );
        }

        /*
        |--------------------------------------------------------------------------
        | FILTRE DÉPÔT DESTINATION
        |--------------------------------------------------------------------------
        */
        if ($request->filled('destination_depot_id')) {

            $query->where(
                'destination_depot_id',
                $request->destination_depot_id
            );
        }

       $transfers = $query
            ->paginate(20);

        $transfers->appends(
            $request->query()
        );

        $depots = Depot::orderBy('name')->get();

        return view(
            'depot_transfers.index',
            compact(
                'transfers',
                'depots'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | CREATE
    |--------------------------------------------------------------------------
    */
    public function create()
    {
        $products = Product::orderBy('designation')
            ->get();

        $depots = Depot::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view(
            'depot_transfers.create',
            compact(
                'products',
                'depots'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | STOCK DISPONIBLE
    |--------------------------------------------------------------------------
    |
    | Cette méthode est utilisée en AJAX dans create.blade.php.
    |
    | Exemple :
    |
    | /depot-transfers/stock/1/25
    |
    | 1  = dépôt
    | 25 = produit
    |
    */
    public function getAvailableStock(
        Depot $depot,
        Product $product
    ) {
        $stock = ProductDepotStock::where(
            'depot_id',
            $depot->id
        )
        ->where(
            'product_id',
            $product->id
        )
        ->first();

        $quantity = $stock
            ? (float) $stock->quantity
            : 0;

        return response()->json([
            'success' => true,

            'depot_id' => $depot->id,

            'product_id' => $product->id,

            'quantity' => $quantity,
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | STORE
    |--------------------------------------------------------------------------
    |
    | Permet maintenant de transférer plusieurs produits.
    |
    */
    public function store(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | VALIDATION
        |--------------------------------------------------------------------------
        */
        $validator = Validator::make(
            $request->all(),
            [
                'source_depot_id' => [
                    'required',
                    'integer',
                    'exists:depots,id',
                ],

                'destination_depot_id' => [
                    'required',
                    'integer',
                    'exists:depots,id',
                    'different:source_depot_id',
                ],

                'note' => [
                    'nullable',
                    'string',
                    'max:1000',
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
                    'gt:0',
                ],
            ],
            [
                'source_depot_id.required' =>
                    'Le dépôt source est obligatoire.',

                'source_depot_id.exists' =>
                    'Le dépôt source sélectionné est invalide.',

                'destination_depot_id.required' =>
                    'Le dépôt destination est obligatoire.',

                'destination_depot_id.exists' =>
                    'Le dépôt destination sélectionné est invalide.',

                'destination_depot_id.different' =>
                    'Le dépôt destination doit être différent du dépôt source.',

                'items.required' =>
                    'Vous devez ajouter au moins un produit.',

                'items.array' =>
                    'La liste des produits est invalide.',

                'items.min' =>
                    'Vous devez ajouter au moins un produit.',

                'items.*.product_id.required' =>
                    'Veuillez sélectionner un produit.',

                'items.*.product_id.exists' =>
                    'Un des produits sélectionnés est invalide.',

                'items.*.quantity.required' =>
                    'Veuillez saisir la quantité à transférer.',

                'items.*.quantity.numeric' =>
                    'La quantité doit être numérique.',

                'items.*.quantity.gt' =>
                    'La quantité doit être supérieure à zéro.',
            ]
        );


        if ($validator->fails()) {

            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput();
        }


        /*
        |--------------------------------------------------------------------------
        | EMPÊCHER LE MÊME PRODUIT PLUSIEURS FOIS
        |--------------------------------------------------------------------------
        */
        $productIds = collect($request->items)
            ->pluck('product_id')
            ->filter()
            ->map(function ($id) {
                return (int) $id;
            })
            ->values();


        if (
            $productIds->count()
            !==
            $productIds->unique()->count()
        ) {

            return redirect()
                ->back()
                ->withInput()
                ->with(
                    'error',
                    'Le même produit ne peut pas être ajouté plusieurs fois dans le même transfert.'
                );
        }


        try {

            /*
            |--------------------------------------------------------------------------
            | TRANSACTION SQL
            |--------------------------------------------------------------------------
            |
            | Si une seule ligne échoue, aucun transfert n'est enregistré.
            |
            */
            DB::transaction(function () use ($request) {

                $sourceDepotId =
                    (int) $request->source_depot_id;

                $destinationDepotId =
                    (int) $request->destination_depot_id;


                /*
                |--------------------------------------------------------------------------
                | CHAQUE PRODUIT
                |--------------------------------------------------------------------------
                */
                foreach ($request->items as $item) {

                    $productId =
                        (int) $item['product_id'];

                    $quantity =
                        (float) $item['quantity'];


                    /*
                    |--------------------------------------------------------------------------
                    | PRODUIT
                    |--------------------------------------------------------------------------
                    */
                    $product = Product::findOrFail(
                        $productId
                    );


                    /*
                    |--------------------------------------------------------------------------
                    | STOCK SOURCE
                    |--------------------------------------------------------------------------
                    |
                    | lockForUpdate() évite que deux utilisateurs transfèrent
                    | en même temps le même stock.
                    |
                    */
                    $sourceStock = ProductDepotStock::where(
                        'product_id',
                        $productId
                    )
                    ->where(
                        'depot_id',
                        $sourceDepotId
                    )
                    ->lockForUpdate()
                    ->first();


                    /*
                    |--------------------------------------------------------------------------
                    | PRODUIT ABSENT DU DÉPÔT
                    |--------------------------------------------------------------------------
                    */
                    if (!$sourceStock) {

                        $productLabel =
                            $this->getProductLabel(
                                $product
                            );

                        throw new \Exception(
                            "Le produit {$productLabel} n'existe pas dans le dépôt source."
                        );
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | STOCK DISPONIBLE
                    |--------------------------------------------------------------------------
                    */
                    $availableQuantity =
                        (float) $sourceStock->quantity;


                    /*
                    |--------------------------------------------------------------------------
                    | STOCK NUL
                    |--------------------------------------------------------------------------
                    */
                    if ($availableQuantity <= 0) {

                        $productLabel =
                            $this->getProductLabel(
                                $product
                            );

                        throw new \Exception(
                            "Le stock du produit {$productLabel} est nul dans le dépôt source."
                        );
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | STOCK INSUFFISANT
                    |--------------------------------------------------------------------------
                    */
                    if (
                        $quantity >
                        $availableQuantity
                    ) {

                        $productLabel =
                            $this->getProductLabel(
                                $product
                            );

                        throw new \Exception(
                            'Stock insuffisant pour le produit '
                            . $productLabel
                            . '. Disponible : '
                            . $this->formatQuantity($availableQuantity)
                            . '. Quantité demandée : '
                            . $this->formatQuantity($quantity)
                            . '.'
                        );
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | STOCK DESTINATION
                    |--------------------------------------------------------------------------
                    */
                    $destinationStock =
                        ProductDepotStock::where(
                            'product_id',
                            $productId
                        )
                        ->where(
                            'depot_id',
                            $destinationDepotId
                        )
                        ->lockForUpdate()
                        ->first();


                    /*
                    |--------------------------------------------------------------------------
                    | DIMINUER LE STOCK SOURCE
                    |--------------------------------------------------------------------------
                    */
                    $newSourceQuantity =
                        $availableQuantity - $quantity;


                    if ($newSourceQuantity <= 0) {

                        /*
                        |--------------------------------------------------------------------------
                        | Si le stock arrive à 0, suppression de la ligne.
                        |--------------------------------------------------------------------------
                        |
                        | Si vous préférez garder les stocks à zéro dans votre table,
                        | remplacez delete() par :
                        |
                        | $sourceStock->quantity = 0;
                        | $sourceStock->save();
                        |
                        */
                        $sourceStock->delete();

                    } else {

                        $sourceStock->quantity =
                            $newSourceQuantity;

                        $sourceStock->save();
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | AUGMENTER LE STOCK DESTINATION
                    |--------------------------------------------------------------------------
                    */
                    if ($destinationStock) {

                        $destinationStock->quantity =
                            (float) $destinationStock->quantity
                            + $quantity;

                        $destinationStock->save();

                    } else {

                        ProductDepotStock::create([
                            'product_id' =>
                                $productId,

                            'depot_id' =>
                                $destinationDepotId,

                            'quantity' =>
                                $quantity,
                        ]);
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | HISTORIQUE DU TRANSFERT
                    |--------------------------------------------------------------------------
                    |
                    | Une ligne est enregistrée pour chaque produit.
                    |
                    */
                    DepotTransfer::create([
                        'product_id' =>
                            $productId,

                        'source_depot_id' =>
                            $sourceDepotId,

                        'destination_depot_id' =>
                            $destinationDepotId,

                        'quantity' =>
                            $quantity,

                        'note' =>
                            $request->note,

                        'user_id' =>
                            auth()->id(),
                    ]);
                }
            });


            return redirect()
                ->route('depot-transfers.index')
                ->with(
                    'success',
                    'Le transfert de '
                    . count($request->items)
                    . ' produit(s) a été effectué avec succès.'
                );

        } catch (\Throwable $e) {

            return redirect()
                ->back()
                ->withInput()
                ->with(
                    'error',
                    $e->getMessage()
                );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | SHOW
    |--------------------------------------------------------------------------
    */
    public function show(DepotTransfer $depotTransfer)
    {
        $depotTransfer->load([
            'product',
            'sourceDepot',
            'destinationDepot',
            'user',
        ]);

        return view(
            'depot_transfers.show',
            [
                'transfer' => $depotTransfer,
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | EDIT
    |--------------------------------------------------------------------------
    |
    | Pour des raisons de traçabilité, on ne permet pas de modifier
    | le produit, les dépôts ou la quantité après le transfert.
    |
    | Seule la note est modifiable.
    |
    */
    public function edit(DepotTransfer $depotTransfer)
    {
        $depotTransfer->load([
            'product',
            'sourceDepot',
            'destinationDepot',
        ]);

        return view(
            'depot_transfers.edit',
            [
                'transfer' => $depotTransfer,
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    |
    | Seule la note est modifiable.
    |
    | Il ne faut pas modifier directement la quantité ou les dépôts
    | d'un transfert déjà exécuté car cela désynchroniserait le stock.
    |
    */
    public function update(
        Request $request,
        DepotTransfer $depotTransfer
    ) {
        $validated = $request->validate(
            [
                'note' => [
                    'nullable',
                    'string',
                    'max:1000',
                ],
            ],
            [
                'note.string' =>
                    'La note doit être un texte.',

                'note.max' =>
                    'La note ne peut pas dépasser 1000 caractères.',
            ]
        );


        $depotTransfer->update([
            'note' =>
                $validated['note'] ?? null,
        ]);


        return redirect()
            ->route(
                'depot-transfers.show',
                $depotTransfer
            )
            ->with(
                'success',
                'La note du transfert a été modifiée avec succès.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | DESTROY
    |--------------------------------------------------------------------------
    |
    | IMPORTANT :
    |
    | Supprimer simplement DepotTransfer::delete() serait dangereux.
    |
    | Le stock a déjà été déplacé.
    |
    | Si on supprime l'historique sans rétablir le stock :
    |
    | - le dépôt source reste diminué
    | - le dépôt destination reste augmenté
    | - l'historique disparaît
    |
    | Ici, la suppression ANNULE donc le transfert :
    |
    | destination -> source
    |
    */
    public function destroy(DepotTransfer $depotTransfer)
    {
        try {

            DB::transaction(function () use ($depotTransfer) {

                /*
                |--------------------------------------------------------------------------
                | VERROUILLER LE TRANSFERT
                |--------------------------------------------------------------------------
                */
                $transfer = DepotTransfer::where(
                    'id',
                    $depotTransfer->id
                )
                ->lockForUpdate()
                ->firstOrFail();


                $productId =
                    (int) $transfer->product_id;

                $quantity =
                    (float) $transfer->quantity;

                $sourceDepotId =
                    (int) $transfer->source_depot_id;

                $destinationDepotId =
                    (int) $transfer->destination_depot_id;


                /*
                |--------------------------------------------------------------------------
                | STOCK DESTINATION ACTUEL
                |--------------------------------------------------------------------------
                */
                $destinationStock =
                    ProductDepotStock::where(
                        'product_id',
                        $productId
                    )
                    ->where(
                        'depot_id',
                        $destinationDepotId
                    )
                    ->lockForUpdate()
                    ->first();


                /*
                |--------------------------------------------------------------------------
                | IMPOSSIBLE D'ANNULER SI LE STOCK N'EST PLUS DISPONIBLE
                |--------------------------------------------------------------------------
                */
                if (
                    !$destinationStock ||
                    (float) $destinationStock->quantity
                    < $quantity
                ) {

                    throw new \Exception(
                        'Impossible de supprimer ce transfert, car le dépôt destination ne possède plus une quantité suffisante pour annuler le mouvement.'
                    );
                }


                /*
                |--------------------------------------------------------------------------
                | STOCK SOURCE
                |--------------------------------------------------------------------------
                */
                $sourceStock =
                    ProductDepotStock::where(
                        'product_id',
                        $productId
                    )
                    ->where(
                        'depot_id',
                        $sourceDepotId
                    )
                    ->lockForUpdate()
                    ->first();


                /*
                |--------------------------------------------------------------------------
                | RETIRER DU DÉPÔT DESTINATION
                |--------------------------------------------------------------------------
                */
                $newDestinationQuantity =
                    (float) $destinationStock->quantity
                    - $quantity;


                if ($newDestinationQuantity <= 0) {

                    $destinationStock->delete();

                } else {

                    $destinationStock->quantity =
                        $newDestinationQuantity;

                    $destinationStock->save();
                }


                /*
                |--------------------------------------------------------------------------
                | REMETTRE DANS LE DÉPÔT SOURCE
                |--------------------------------------------------------------------------
                */
                if ($sourceStock) {

                    $sourceStock->quantity =
                        (float) $sourceStock->quantity
                        + $quantity;

                    $sourceStock->save();

                } else {

                    ProductDepotStock::create([
                        'product_id' =>
                            $productId,

                        'depot_id' =>
                            $sourceDepotId,

                        'quantity' =>
                            $quantity,
                    ]);
                }


                /*
                |--------------------------------------------------------------------------
                | SUPPRIMER L'HISTORIQUE
                |--------------------------------------------------------------------------
                */
                $transfer->delete();
            });


            return redirect()
                ->route('depot-transfers.index')
                ->with(
                    'success',
                    'Le transfert a été annulé et le stock a été rétabli avec succès.'
                );

        } catch (\Throwable $e) {

            return redirect()
                ->back()
                ->with(
                    'error',
                    $e->getMessage()
                );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | LIBELLÉ PRODUIT
    |--------------------------------------------------------------------------
    */
    private function getProductLabel(
        Product $product
    ): string {

        $reference =
            trim(
                (string) (
                    $product->reference ?? ''
                )
            );

        $designation =
            trim(
                (string) (
                    $product->designation
                    ?? $product->name
                    ?? ''
                )
            );


        if (
            $reference !== ''
            &&
            $designation !== ''
        ) {

            return $reference
                . ' - '
                . $designation;
        }


        if ($reference !== '') {

            return $reference;
        }


        if ($designation !== '') {

            return $designation;
        }


        return 'Produit #' . $product->id;
    }


    /*
    |--------------------------------------------------------------------------
    | FORMAT QUANTITÉ
    |--------------------------------------------------------------------------
    */
    private function formatQuantity(
        float $quantity
    ): string {

        if (
            floor($quantity)
            ===
            $quantity
        ) {

            return (string) (int) $quantity;
        }


        return rtrim(
            rtrim(
                number_format(
                    $quantity,
                    2,
                    '.',
                    ''
                ),
                '0'
            ),
            '.'
        );
    }
}