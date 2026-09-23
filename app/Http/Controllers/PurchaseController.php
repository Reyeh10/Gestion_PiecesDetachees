<?php

namespace App\Http\Controllers;

use App\Models\Depot;
use App\Models\Product;
use App\Models\ProductDepotStock;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\StockMovement;
use App\Models\Supplier;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | LISTE DES ACHATS
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        $purchases = Purchase::with([
                'supplier',
                'depot',
                'items.product',
                'user',
            ])
            ->latest()
            ->paginate(20);

        return view(
            'purchases.index',
            compact('purchases')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | FORMULAIRE CRÉER ACHAT
    |--------------------------------------------------------------------------
    */

    public function create()
    {
        $suppliers = Supplier::orderBy('name')
            ->get();

        $products = Product::with([
                'brand',
                'model',
                'suppliers',
            ])
            ->orderBy('designation')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | DÉPÔTS ACTIFS
        |--------------------------------------------------------------------------
        */

        $depots = Depot::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view(
            'purchases.create',
            compact(
                'suppliers',
                'products',
                'depots'
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | AJAX PRODUITS FOURNISSEUR
    |--------------------------------------------------------------------------
    */

    public function getSupplierProducts(
        Supplier $supplier
    ) {
        $supplier->load([
            'products.brand',
            'products.model',
        ]);

        $products = $supplier
            ->products
            ->map(function ($product) {

                /*
                |--------------------------------------------------------------------------
                | STOCK TOTAL RÉEL
                |--------------------------------------------------------------------------
                |
                | Le stock affiché provient de la somme des dépôts.
                |
                */

                $stock = (float) ProductDepotStock::query()
                    ->where(
                        'product_id',
                        $product->id
                    )
                    ->sum('quantity');

                return [
                    'id' =>
                        $product->id,

                    'reference' =>
                        $product->reference,

                    'designation' =>
                        $product->designation,

                    'brand' =>
                        $product->brand->name ?? '',

                    'model' =>
                        $product->model->name ?? '',

                    'stock' =>
                        round($stock, 2),

                    /*
                    |--------------------------------------------------------------------------
                    | PRIX DU FOURNISSEUR
                    |--------------------------------------------------------------------------
                    |
                    | Le prix du pivot représente le dernier prix réel connu
                    | pour ce fournisseur.
                    |
                    */

                    'purchase_price' =>
                        $product->pivot->purchase_price
                        ??
                        $product->purchase_price
                        ??
                        0,
                ];
            })
            ->values();

        return response()->json(
            $products
        );
    }

    /*
    |--------------------------------------------------------------------------
    | ENREGISTRER ACHAT
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | VALIDATION
        |--------------------------------------------------------------------------
        */

        $validated = $request->validate([
            'supplier_id' =>
                'required|exists:suppliers,id',

            'depot_id' =>
                'required|exists:depots,id',

            'items' =>
                'required|array|min:1',

            'items.*.product_id' =>
                'required|exists:products,id',

            'items.*.quantity' =>
                'required|numeric|min:0.01',

            'items.*.price' =>
                'required|numeric|min:0',
        ]);

        /*
        |--------------------------------------------------------------------------
        | DÉPÔT ACTIF
        |--------------------------------------------------------------------------
        */

        $depot = Depot::query()
            ->whereKey(
                $validated['depot_id']
            )
            ->where(
                'is_active',
                true
            )
            ->firstOrFail();

        DB::beginTransaction();

        try {

            /*
            |--------------------------------------------------------------------------
            | CALCUL SOUS-TOTAL
            |--------------------------------------------------------------------------
            */

            $subtotal = 0;

            foreach ($validated['items'] as $item) {

                $quantity =
                    round(
                        (float) $item['quantity'],
                        2
                    );

                $price =
                    round(
                        (float) $item['price'],
                        2
                    );

                $subtotal +=
                    $quantity * $price;
            }

            $subtotal =
                round(
                    $subtotal,
                    2
                );

            /*
            |--------------------------------------------------------------------------
            | TVA AUTOMATIQUE 10 %
            |--------------------------------------------------------------------------
            */

            $vat =
                round(
                    $subtotal * 0.10,
                    2
                );

            /*
            |--------------------------------------------------------------------------
            | TOTAL TTC
            |--------------------------------------------------------------------------
            */

            $grandTotal =
                round(
                    $subtotal + $vat,
                    2
                );

            /*
            |--------------------------------------------------------------------------
            | RÉFÉRENCE ACHAT
            |--------------------------------------------------------------------------
            */

            $nextId =
                ((int) Purchase::max('id')) + 1;

            $purchaseReference =
                'PUR-'
                .
                date('Y')
                .
                '-'
                .
                str_pad(
                    $nextId,
                    4,
                    '0',
                    STR_PAD_LEFT
                );

            /*
            |--------------------------------------------------------------------------
            | CRÉER ACHAT
            |--------------------------------------------------------------------------
            */

            $purchase = Purchase::create([
                'reference' =>
                    $purchaseReference,

                'supplier_id' =>
                    $validated['supplier_id'],

                'depot_id' =>
                    $depot->id,

                'user_id' =>
                    auth()->id(),

                'subtotal' =>
                    $subtotal,

                'vat' =>
                    $vat,

                'total' =>
                    $grandTotal,

                'status' =>
                    'completed',
            ]);

            /*
            |--------------------------------------------------------------------------
            | ARTICLES
            |--------------------------------------------------------------------------
            */

            foreach ($validated['items'] as $item) {

                /*
                |--------------------------------------------------------------------------
                | VERROUILLER LE PRODUIT
                |--------------------------------------------------------------------------
                */

                $product = Product::query()
                    ->lockForUpdate()
                    ->findOrFail(
                        $item['product_id']
                    );

                $quantity =
                    round(
                        (float) $item['quantity'],
                        2
                    );

                $newPurchasePrice =
                    round(
                        (float) $item['price'],
                        2
                    );

                $lineTotal =
                    round(
                        $quantity * $newPurchasePrice,
                        2
                    );

                /*
                |--------------------------------------------------------------------------
                | STOCK GLOBAL AVANT ACHAT
                |--------------------------------------------------------------------------
                |
                | IMPORTANT :
                |
                | Pour le CUMP, nous utilisons le stock physique réellement
                | affecté aux dépôts.
                |
                */

                $oldQuantity =
                    round(
                        (float) ProductDepotStock::query()
                            ->where(
                                'product_id',
                                $product->id
                            )
                            ->sum('quantity'),
                        2
                    );
                /*
                |--------------------------------------------------------------------------
                | PROTECTION DES ÉCARTS HISTORIQUES
                |--------------------------------------------------------------------------
                |
                | products.quantity doit être identique à la somme des dépôts avant
                | d'autoriser un nouvel achat.
                |
                | Cela empêche qu'un ancien stock non encore affecté à un dépôt soit
                | supprimé silencieusement lors du recalcul du stock global.
                |
                */

                $globalQuantity =
                    round(
                        (float) $product->quantity,
                        2
                    );

                if (
                    abs(
                        $globalQuantity - $oldQuantity
                    ) > 0.01
                ) {
                    throw new \RuntimeException(
                        'Impossible d’enregistrer cet achat pour le produit '
                        .
                        $product->reference
                        .
                        ' : le stock global ('
                        .
                        number_format(
                            $globalQuantity,
                            2,
                            '.',
                            ''
                        )
                        .
                        ') est différent du stock affecté aux dépôts ('
                        .
                        number_format(
                            $oldQuantity,
                            2,
                            '.',
                            ''
                        )
                        .
                        '). Ce produit doit d’abord être régularisé par dépôt.'
                    );
                }
                /*
                |--------------------------------------------------------------------------
                | ANCIENNES VALEURS
                |--------------------------------------------------------------------------
                */

                $oldAveragePurchasePrice =
                    round(
                        (float) $product->purchase_price,
                        4
                    );

                $oldCostPrice =
                    round(
                        (float) $product->cost_price,
                        4
                    );

                $oldSalePrice =
                    round(
                        (float) $product->sale_price,
                        2
                    );

                $coefPurchase =
                    (float) $product->coef_purchase;

                if ($coefPurchase <= 0) {
                    $coefPurchase = 1;
                }

                $coefSale =
                    (float) $product->coef_sale;

                if ($coefSale <= 0) {
                    $coefSale = 1;
                }

                /*
                |--------------------------------------------------------------------------
                | STOCK APRÈS ACHAT
                |--------------------------------------------------------------------------
                */

                $futureQuantity =
                    round(
                        $oldQuantity + $quantity,
                        2
                    );

                /*
                |--------------------------------------------------------------------------
                | CALCUL DU CUMP
                |--------------------------------------------------------------------------
                |
                | FORMULE :
                |
                | (ancien stock × ancien CUMP)
                | +
                | (nouvelle quantité × nouveau prix)
                |
                | ------------------------------------------------
                | ancien stock + nouvelle quantité
                |
                */

                if ($oldQuantity > 0) {

                    $weightedPurchasePrice = (
                        ($oldQuantity * $oldAveragePurchasePrice)
                        +
                        ($quantity * $newPurchasePrice)
                    ) / $futureQuantity;

                } else {

                    /*
                    |--------------------------------------------------------------------------
                    | STOCK PRÉCÉDENT = 0
                    |--------------------------------------------------------------------------
                    |
                    | L'ancien prix ne participe pas au nouveau CUMP.
                    |
                    */

                    $weightedPurchasePrice =
                        $newPurchasePrice;
                }

                $weightedPurchasePrice =
                    round(
                        $weightedPurchasePrice,
                        4
                    );

                /*
                |--------------------------------------------------------------------------
                | PRIX DE REVIENT
                |--------------------------------------------------------------------------
                */

                $weightedCostPrice =
                    round(
                        $weightedPurchasePrice
                        *
                        $coefPurchase,
                        4
                    );

                /*
                |--------------------------------------------------------------------------
                | PRIX DE VENTE
                |--------------------------------------------------------------------------
                */

                $newSalePrice =
                    round(
                        $weightedCostPrice
                        *
                        $coefSale,
                        2
                    );

                /*
                |--------------------------------------------------------------------------
                | CRÉER LA LIGNE D'ACHAT
                |--------------------------------------------------------------------------
                |
                | price = vrai prix du nouvel arrivage.
                |
                | previous_* = photographie de la situation avant achat.
                |
                */

                PurchaseItem::create([
                    'purchase_id' =>
                        $purchase->id,

                    'product_id' =>
                        $product->id,

                    'quantity' =>
                        $quantity,

                    'price' =>
                        $newPurchasePrice,

                    'total' =>
                        $lineTotal,

                    'previous_quantity' =>
                        $oldQuantity,

                    'previous_purchase_price' =>
                        $oldAveragePurchasePrice,

                    'previous_cost_price' =>
                        $oldCostPrice,

                    'previous_sale_price' =>
                        $oldSalePrice,

                    'previous_coef_purchase' =>
                        $coefPurchase,

                    'previous_coef_sale' =>
                        $coefSale,

                    'new_weighted_purchase_price' =>
                        $weightedPurchasePrice,
                ]);

                /*
                |--------------------------------------------------------------------------
                | STOCK DU DÉPÔT
                |--------------------------------------------------------------------------
                */

                $depotStock = ProductDepotStock::query()
                    ->where(
                        'product_id',
                        $product->id
                    )
                    ->where(
                        'depot_id',
                        $depot->id
                    )
                    ->lockForUpdate()
                    ->first();

                if (!$depotStock) {

                    $depotStock =
                        new ProductDepotStock();

                    $depotStock->product_id =
                        $product->id;

                    $depotStock->depot_id =
                        $depot->id;

                    /*
                    |--------------------------------------------------------------------------
                    | RAYON / EMPLACEMENT
                    |--------------------------------------------------------------------------
                    |
                    | Pour une nouvelle ligne produit/dépôt, nous reprenons
                    | les valeurs actuellement enregistrées sur le produit.
                    |
                    */

                    $depotStock->rayon_id =
                        $product->rayon_id;

                    $depotStock->location_id =
                        $product->location_id;

                    $depotStock->quantity =
                        0;
                }

                $depotStock->quantity =
                    round(
                        (float) $depotStock->quantity
                        +
                        $quantity,
                        2
                    );

                $depotStock->save();

                /*
                |--------------------------------------------------------------------------
                | RECALCUL DU STOCK GLOBAL
                |--------------------------------------------------------------------------
                */

                $totalQuantity =
                    round(
                        (float) ProductDepotStock::query()
                            ->where(
                                'product_id',
                                $product->id
                            )
                            ->sum('quantity'),
                        2
                    );

                /*
                |--------------------------------------------------------------------------
                | METTRE À JOUR LE PRODUIT
                |--------------------------------------------------------------------------
                */

                $product->quantity =
                    $totalQuantity;


               /*
                |--------------------------------------------------------------------------
                | QUANTITÉ REÇUE
                |--------------------------------------------------------------------------
                |
                | initial_quantity représente la quantité initiale historique.
                | Elle ne doit JAMAIS être modifiée lors d'un achat.
                |
                | received_quantity représente la quantité cumulée reçue.
                |
                */

                $product->received_quantity =
                    round(
                        (float) ($product->received_quantity ?? 0)
                        +
                        $quantity,
                        2
                    );

                /*
                |--------------------------------------------------------------------------
                | CUMP
                |--------------------------------------------------------------------------
                */

                $product->purchase_price =
                    $weightedPurchasePrice;

                /*
                |--------------------------------------------------------------------------
                | PRIX DE REVIENT
                |--------------------------------------------------------------------------
                */

                $product->cost_price =
                    $weightedCostPrice;

                /*
                |--------------------------------------------------------------------------
                | PRIX DE VENTE
                |--------------------------------------------------------------------------
                */

                $product->sale_price =
                    $newSalePrice;

                /*
                |--------------------------------------------------------------------------
                | STATUS
                |--------------------------------------------------------------------------
                |
                | products.status accepte :
                |
                | disponible
                | vendu
                | retourne
                |
                | Un nouvel arrivage rend le produit disponible.
                |
                */

                $product->status =
                    'disponible';

                $product->save();

                /*
                |--------------------------------------------------------------------------
                | PRIX FOURNISSEUR
                |--------------------------------------------------------------------------
                |
                | Le pivot conserve le VRAI prix du dernier achat auprès
                | de ce fournisseur et non le CUMP.
                |
                */

                $product
                    ->suppliers()
                    ->syncWithoutDetaching([
                        $validated['supplier_id'] => [
                            'supplier_reference' =>
                                $product->reference,

                            'purchase_price' =>
                                $newPurchasePrice,

                            'delivery_delay' =>
                                3,

                            'is_primary' =>
                                true,

                            'active' =>
                                true,
                        ],
                    ]);

                /*
                |--------------------------------------------------------------------------
                | MOUVEMENT DE STOCK
                |--------------------------------------------------------------------------
                */

                StockMovement::create([
                    'product_id' =>
                        $product->id,

                    'user_id' =>
                        auth()->id(),

                    'type' =>
                        'in',

                    'quantity' =>
                        $quantity,

                    'source' =>
                        'Achat fournisseur - '
                        .
                        $depot->name,

                    'reference' =>
                        $purchaseReference,
                ]);
            }

            DB::commit();

            return redirect()
                ->route(
                    'purchases.show',
                    $purchase->id
                )
                ->with(
                    'success',
                    'Achat enregistré avec succès dans le dépôt '
                    .
                    $depot->name
                    .
                    '. Le stock et le prix moyen pondéré ont été recalculés.'
                );

        } catch (\Throwable $e) {

            DB::rollBack();

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
    | DÉTAILS ACHAT
    |--------------------------------------------------------------------------
    */

    public function show(
        Purchase $purchase
    ) {
        $purchase->load([
            'supplier',
            'depot',
            'items.product.brand',
            'items.product.model',
            'user',
        ]);

        return view(
            'purchases.show',
            compact('purchase')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | SUPPRIMER / ANNULER ACHAT
    |--------------------------------------------------------------------------
    */

    public function destroy(
        Purchase $purchase
    ) {
        /*
        |--------------------------------------------------------------------------
        | PROTECTION DES ANCIENS ACHATS
        |--------------------------------------------------------------------------
        */

        if (!$purchase->depot_id) {

            return redirect()
                ->back()
                ->with(
                    'error',
                    'Impossible d’annuler automatiquement cet ancien achat : '
                    .
                    'aucun dépôt de réception n’est enregistré.'
                );
        }

        DB::beginTransaction();

        try {

            /*
            |--------------------------------------------------------------------------
            | VERROUILLER L'ACHAT
            |--------------------------------------------------------------------------
            */

            $purchase = Purchase::query()
                ->lockForUpdate()
                ->findOrFail(
                    $purchase->id
                );

            $depot = Depot::query()
                ->findOrFail(
                    $purchase->depot_id
                );

            $purchase->load([
                'items.product',
            ]);

            foreach ($purchase->items as $item) {

                /*
                |--------------------------------------------------------------------------
                | PRODUIT
                |--------------------------------------------------------------------------
                */

                $product = Product::query()
                    ->lockForUpdate()
                    ->findOrFail(
                        $item->product_id
                    );

                $quantity =
                    round(
                        (float) $item->quantity,
                        2
                    );

                $purchaseUnitPrice =
                    round(
                        (float) $item->price,
                        2
                    );

                /*
                |--------------------------------------------------------------------------
                | STOCK DU DÉPÔT
                |--------------------------------------------------------------------------
                */

                $depotStock = ProductDepotStock::query()
                    ->where(
                        'product_id',
                        $product->id
                    )
                    ->where(
                        'depot_id',
                        $purchase->depot_id
                    )
                    ->lockForUpdate()
                    ->first();

                if (!$depotStock) {

                    throw new \RuntimeException(
                        'Impossible d’annuler l’achat '
                        .
                        $purchase->reference
                        .
                        ' : aucun stock correspondant n’existe dans le dépôt '
                        .
                        $depot->name
                        .
                        ' pour le produit '
                        .
                        $product->reference
                        .
                        '.'
                    );
                }

                /*
                |--------------------------------------------------------------------------
                | PROTECTION CONTRE STOCK NÉGATIF
                |--------------------------------------------------------------------------
                */

                if (
                    (float) $depotStock->quantity
                    <
                    $quantity
                ) {

                    throw new \RuntimeException(
                        'Impossible d’annuler l’achat '
                        .
                        $purchase->reference
                        .
                        ' : le dépôt '
                        .
                        $depot->name
                        .
                        ' ne possède plus suffisamment de stock pour '
                        .
                        $product->reference
                        .
                        '. Stock actuel : '
                        .
                        $depotStock->quantity
                        .
                        ', quantité à retirer : '
                        .
                        $quantity
                        .
                        '.'
                    );
                }

                /*
                |--------------------------------------------------------------------------
                | STOCK GLOBAL AVANT ANNULATION
                |--------------------------------------------------------------------------
                */

                $currentTotalQuantity =
                    round(
                        (float) ProductDepotStock::query()
                            ->where(
                                'product_id',
                                $product->id
                            )
                            ->sum('quantity'),
                        2
                    );

                $remainingQuantity =
                    round(
                        $currentTotalQuantity
                        -
                        $quantity,
                        2
                    );

                /*
                |--------------------------------------------------------------------------
                | RECALCUL DU CUMP APRÈS RETRAIT
                |--------------------------------------------------------------------------
                |
                | Valeur actuelle du stock :
                |
                | stock actuel × CUMP actuel
                |
                | Valeur de l'achat retiré :
                |
                | quantité achat × prix réel achat
                |
                */

                $currentAveragePurchasePrice =
                    round(
                        (float) $product->purchase_price,
                        4
                    );

                $currentInventoryValue =
                    $currentTotalQuantity
                    *
                    $currentAveragePurchasePrice;

                $purchaseValueToRemove =
                    $quantity
                    *
                    $purchaseUnitPrice;

                $remainingInventoryValue =
                    $currentInventoryValue
                    -
                    $purchaseValueToRemove;

                /*
                |--------------------------------------------------------------------------
                | PROTECTION DE LA VALORISATION
                |--------------------------------------------------------------------------
                */

                if (
                    $remainingQuantity > 0
                    &&
                    $remainingInventoryValue < -0.01
                ) {

                    throw new \RuntimeException(
                        'Impossible d’annuler automatiquement l’achat '
                        .
                        $purchase->reference
                        .
                        ' pour le produit '
                        .
                        $product->reference
                        .
                        ' : la valorisation du stock ne permet pas un retrait '
                        .
                        'cohérent de cet achat.'
                    );
                }

                /*
                |--------------------------------------------------------------------------
                | RETIRER DU DÉPÔT
                |--------------------------------------------------------------------------
                */

                $depotStock->quantity =
                    round(
                        (float) $depotStock->quantity
                        -
                        $quantity,
                        2
                    );

                $depotStock->save();

                /*
                |--------------------------------------------------------------------------
                | RECALCULER LE STOCK GLOBAL
                |--------------------------------------------------------------------------
                */

                $totalQuantity =
                    round(
                        (float) ProductDepotStock::query()
                            ->where(
                                'product_id',
                                $product->id
                            )
                            ->sum('quantity'),
                        2
                    );

                /*
                |--------------------------------------------------------------------------
                | NOUVEAU CUMP
                |--------------------------------------------------------------------------
                */

                if ($totalQuantity > 0) {

                    $newWeightedPurchasePrice =
                        round(
                            max(
                                0,
                                $remainingInventoryValue
                                /
                                $totalQuantity
                            ),
                            4
                        );

                } else {

                    /*
                    |--------------------------------------------------------------------------
                    | STOCK = 0
                    |--------------------------------------------------------------------------
                    |
                    | Si l'achat annulé correspond exactement à la dernière
                    | entrée enregistrée, nous pouvons restaurer l'ancien prix.
                    |
                    */

                    $newWeightedPurchasePrice =
                        $item->previous_purchase_price !== null
                            ? round(
                                (float) $item->previous_purchase_price,
                                4
                            )
                            : 0;
                }

                /*
                |--------------------------------------------------------------------------
                | COEFFICIENTS ACTUELS
                |--------------------------------------------------------------------------
                */

                $coefPurchase =
                    (float) $product->coef_purchase;

                if ($coefPurchase <= 0) {
                    $coefPurchase = 1;
                }

                $coefSale =
                    (float) $product->coef_sale;

                if ($coefSale <= 0) {
                    $coefSale = 1;
                }

                /*
                |--------------------------------------------------------------------------
                | METTRE À JOUR PRODUIT
                |--------------------------------------------------------------------------
                */

                $product->quantity =
                    $totalQuantity;

                $product->purchase_price =
                    $newWeightedPurchasePrice;

                $product->cost_price =
                    round(
                        $newWeightedPurchasePrice
                        *
                        $coefPurchase,
                        4
                    );

                $product->sale_price =
                    round(
                        (float) $product->cost_price
                        *
                        $coefSale,
                        2
                    );

                /*
                |--------------------------------------------------------------------------
                | QUANTITÉS CUMULÉES
                |--------------------------------------------------------------------------
                |
                | On annule également la réception comptabilisée par cet achat.
                |
                */

               /*
            |--------------------------------------------------------------------------
            | ANNULER LA QUANTITÉ REÇUE
            |--------------------------------------------------------------------------
            |
            | initial_quantity reste intacte.
            |
            */

            $product->received_quantity =
                round(
                    max(
                        0,
                        (float) ($product->received_quantity ?? 0)
                        -
                        $quantity
                    ),
                    2
                );

                /*
                |--------------------------------------------------------------------------
                | STATUS
                |--------------------------------------------------------------------------
                |
                | Ne jamais utiliser rupture / stock_faible ici.
                |
                */

                if ($totalQuantity > 0) {

                    $product->status =
                        'disponible';
                }

                $product->save();

                /*
                |--------------------------------------------------------------------------
                | MOUVEMENT SORTANT
                |--------------------------------------------------------------------------
                */

                StockMovement::create([
                    'product_id' =>
                        $product->id,

                    'user_id' =>
                        auth()->id(),

                    'type' =>
                        'out',

                    'quantity' =>
                        $quantity,

                    'source' =>
                        'Annulation achat - '
                        .
                        $depot->name,

                    'reference' =>
                        $purchase->reference,
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | SUPPRIMER LES LIGNES
            |--------------------------------------------------------------------------
            */

            $purchase->items()
                ->delete();

            /*
            |--------------------------------------------------------------------------
            | SUPPRIMER L'ACHAT
            |--------------------------------------------------------------------------
            */

            $purchase->delete();

            DB::commit();

            return redirect()
                ->route(
                    'purchases.index'
                )
                ->with(
                    'success',
                    'Achat annulé avec succès. '
                    .
                    'Le stock et la valorisation ont été recalculés.'
                );

        } catch (\Throwable $e) {

            DB::rollBack();

            return redirect()
                ->back()
                ->with(
                    'error',
                    $e->getMessage()
                );
        }
    }
}
