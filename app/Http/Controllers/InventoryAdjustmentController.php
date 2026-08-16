<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\InventoryAdjustment;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryAdjustmentController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | INDEX
    |--------------------------------------------------------------------------
    */
    public function index(Request $request)
    {
        $query = InventoryAdjustment::with([
            'product.brand',
            'product.model',
            'approver',
        ]);

        /*
        |--------------------------------------------------------------------------
        | RECHERCHE
        |--------------------------------------------------------------------------
        */
        if ($request->filled('search')) {

            $search = trim((string) $request->search);

            $query->whereHas('product', function ($q) use ($search) {

                $q->where('designation', 'like', '%' . $search . '%')
                    ->orWhere('reference', 'like', '%' . $search . '%');
            });
        }

        $adjustments = $query
            ->latest()
            ->paginate(15);

        $adjustments->appends(
            $request->only('search')
        );

        return view(
            'inventory_adjustments.index',
            compact('adjustments')
        );
    }


    /*
    |--------------------------------------------------------------------------
    | CREATE
    |--------------------------------------------------------------------------
    */
    public function create()
    {
        /*
        |--------------------------------------------------------------------------
        | PRODUITS
        |--------------------------------------------------------------------------
        |
        | IMPORTANT :
        | product.quantity représente ici la QUANTITÉ DISPONIBLE.
        |
        | L'ajustement inventaire travaille uniquement sur cette quantité.
        | Il ne doit jamais modifier la quantité initiale.
        |
        */

        $products = Product::with([
            'brand',
            'model',
            'family',
            'subfamily',
        ])
            ->orderBy('designation')
            ->get();

        return view(
            'inventory_adjustments.create',
            compact('products')
        );
    }


    /*
    |--------------------------------------------------------------------------
    | STORE
    |--------------------------------------------------------------------------
    */
    public function store(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | VALIDATION
        |--------------------------------------------------------------------------
        |
        | numeric au lieu de integer permet également les produits en :
        | - litre
        | - kg
        | - quantité décimale
        |
        */

        $request->validate([
            'product_id' => [
                'required',
                'exists:products,id',
            ],

            'new_qty' => [
                'required',
                'numeric',
                'min:0',
            ],

            'reason' => [
                'required',
                'string',
                'max:1000',
            ],
        ], [
            'product_id.required' =>
                'Veuillez sélectionner un produit.',

            'product_id.exists' =>
                'Le produit sélectionné est invalide.',

            'new_qty.required' =>
                'Veuillez saisir la nouvelle quantité disponible.',

            'new_qty.numeric' =>
                'La quantité doit être un nombre valide.',

            'new_qty.min' =>
                'La quantité ne peut pas être négative.',

            'reason.required' =>
                'Veuillez préciser la raison de l’ajustement.',
        ]);


        try {

            DB::transaction(function () use ($request) {

                /*
                |--------------------------------------------------------------------------
                | VERROUILLER LE PRODUIT
                |--------------------------------------------------------------------------
                |
                | lockForUpdate empêche deux utilisateurs de modifier le même
                | stock simultanément.
                |
                */

                $product = Product::where(
                    'id',
                    $request->product_id
                )
                    ->lockForUpdate()
                    ->firstOrFail();


                /*
                |--------------------------------------------------------------------------
                | QUANTITÉ DISPONIBLE AVANT AJUSTEMENT
                |--------------------------------------------------------------------------
                |
                | IMPORTANT :
                |
                | $product->quantity = QUANTITÉ DISPONIBLE
                |
                | Ce n'est PAS la quantité initiale.
                |
                */

                $oldQty = round(
                    (float) $product->quantity,
                    2
                );


                /*
                |--------------------------------------------------------------------------
                | QUANTITÉ DISPONIBLE PHYSIQUEMENT COMPTÉE
                |--------------------------------------------------------------------------
                */

                $newQty = round(
                    (float) $request->new_qty,
                    2
                );


                /*
                |--------------------------------------------------------------------------
                | DIFFÉRENCE
                |--------------------------------------------------------------------------
                |
                | Exemples :
                |
                | disponible système : 40
                | quantité physique  : 45
                | différence         : +5
                |
                | disponible système : 40
                | quantité physique  : 35
                | différence         : -5
                |
                */

                $signedDifference = round(
                    $newQty - $oldQty,
                    2
                );


                /*
                |--------------------------------------------------------------------------
                | ENREGISTRER L'AJUSTEMENT
                |--------------------------------------------------------------------------
                |
                | old_qty et new_qty représentent :
                |
                | - quantité disponible AVANT
                | - quantité disponible APRÈS
                |
                | Ils ne représentent jamais la quantité initiale.
                |
                */

                $adjustment = InventoryAdjustment::create([
                    'product_id'  => $product->id,
                    'old_qty'     => $oldQty,
                    'new_qty'     => $newQty,
                    'reason'      => trim((string) $request->reason),
                    'approved_by' => auth()->id(),
                ]);


                /*
                |--------------------------------------------------------------------------
                | MODIFIER UNIQUEMENT LA QUANTITÉ DISPONIBLE
                |--------------------------------------------------------------------------
                |
                | IMPORTANT :
                |
                | On utilise DB::table() volontairement ici pour modifier
                | UNIQUEMENT la colonne "quantity".
                |
                | Cela évite qu'un mutator / observer Eloquent éventuel
                | modifie indirectement une autre colonne comme :
                |
                | - initial_quantity
                | - quantity_initial
                | - initial_qty
                |
                | La quantité initiale reste donc inchangée.
                |
                */

                DB::table('products')
                    ->where('id', $product->id)
                    ->update([
                        'quantity'   => $newQty,
                        'updated_at' => now(),
                    ]);


                /*
                |--------------------------------------------------------------------------
                | MOUVEMENT DE STOCK
                |--------------------------------------------------------------------------
                */

                if (abs($signedDifference) > 0.00001) {

                    StockMovement::create([
                        'product_id' => $product->id,

                        'type' => $signedDifference > 0
                            ? 'in'
                            : 'out',

                        'quantity' => abs($signedDifference),

                        'source' => 'Ajustement inventaire',

                        'reference' => 'ADJ-' . $adjustment->id,

                        'user_id' => auth()->id(),
                    ]);
                }
            });


            return redirect()
                ->route('inventory-adjustments.index')
                ->with(
                    'success',
                    'Ajustement inventaire enregistré avec succès. Seule la quantité disponible a été modifiée.'
                );

        } catch (\Throwable $e) {

            report($e);

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Une erreur est survenue pendant l’ajustement : '
                    . $e->getMessage()
                );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | SHOW
    |--------------------------------------------------------------------------
    */
    public function show(
        InventoryAdjustment $inventoryAdjustment
    ) {
        /*
        |--------------------------------------------------------------------------
        | CHARGER LES RELATIONS
        |--------------------------------------------------------------------------
        */

        $inventoryAdjustment->load([
            'product.brand',
            'product.model',
            'product.family',
            'product.subfamily',
            'approver',
        ]);


        /*
        |--------------------------------------------------------------------------
        | CHARGER LES PRODUITS
        |--------------------------------------------------------------------------
        |
        | Nécessaire si show.blade.php réutilise form.blade.php.
        |
        */

        $products = Product::with([
            'brand',
            'model',
            'family',
            'subfamily',
        ])
            ->orderBy('designation')
            ->get();


        return view(
            'inventory_adjustments.show',
            compact(
                'inventoryAdjustment',
                'products'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    |
    | Un ajustement déjà enregistré ne doit pas être modifié.
    | On crée un nouvel ajustement correctif si nécessaire.
    |
    */
    public function update(
        Request $request,
        InventoryAdjustment $inventoryAdjustment
    ) {
        return redirect()
            ->route('inventory-adjustments.index')
            ->with(
                'error',
                'Un ajustement enregistré ne peut pas être modifié. Créez un nouvel ajustement correctif.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | DELETE
    |--------------------------------------------------------------------------
    |
    | IMPORTANT :
    |
    | La suppression est interdite pour préserver la traçabilité.
    |
    | Supprimer uniquement l'historique sans annuler le mouvement réel
    | rendrait les stocks incohérents.
    |
    */
    public function destroy(
        InventoryAdjustment $inventoryAdjustment
    ) {
        return redirect()
            ->route('inventory-adjustments.index')
            ->with(
                'error',
                'La suppression d’un ajustement inventaire est interdite afin de préserver la traçabilité du stock.'
            );
    }
}