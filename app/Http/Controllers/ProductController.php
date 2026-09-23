<?php

namespace App\Http\Controllers;

use App\Models\Product;
//use App\Models\Category;
use App\Models\Brand;
use App\Models\CarModel;
use App\Models\FamilyModel;
use App\Models\Subfamily;
use App\Models\Rayon;
use App\Models\Location;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\Depot;
use App\Models\ProductDepotStock;

use App\Exports\ProductsExport;
use App\Exports\SoldProductsExport;
// use Barryvdh\DomPDF\Facade\Pdf;


use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ProductController extends Controller
{
    /*
|--------------------------------------------------------------------------
| INDEX
|--------------------------------------------------------------------------
*/

   public function index(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | QUERY
        |--------------------------------------------------------------------------
        */

       $query = Product::with([

    'brand',
    'model',
    'family',
    'subfamily',
    'rayon',
    'location',
    'stockMovements',

]);



        /*
        |--------------------------------------------------------------------------
        | SEARCH
        |--------------------------------------------------------------------------
        */

        if ($request->search) {

            $query->where(function ($q) use ($request) {

                $q->where(
                    'designation',
                    'like',
                    '%' . $request->search . '%'
                )

                ->orWhere(
                    'reference',
                    'like',
                    '%' . $request->search . '%'
                );

            });

        }

        /*
        |--------------------------------------------------------------------------
        | PRODUITS
        |--------------------------------------------------------------------------
        */

        $products = $query
            ->latest()
            ->paginate(10);

        /*
        |--------------------------------------------------------------------------
        | FOURNISSEURS
        |--------------------------------------------------------------------------
        */

        $suppliers = Supplier::orderBy('name')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | DEPOTS
        |--------------------------------------------------------------------------
        */

        $depots = Depot::where('is_active', true)
            ->orderBy('name')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | RETURN VIEW
        |--------------------------------------------------------------------------
        */

        return view('products.index', [

            'products' =>
                $products,

            'suppliers' =>
                $suppliers,

            'depots' =>
                $depots,

            'pageTitle' =>
                'Liste de tous les produits',

            'hideButtons' =>
                false,

        ]);
    }



    /*
    |--------------------------------------------------------------------------
    | IMPORT PAGE
    |--------------------------------------------------------------------------
    */

    public function import()
    {
        /*
        |--------------------------------------------------------------------------
        | FOURNISSEURS
        |--------------------------------------------------------------------------
        */

        $suppliers = Supplier::orderBy('name')->get();

        /*
        |--------------------------------------------------------------------------
        | DEPOTS
        |--------------------------------------------------------------------------
        */

        $depots = Depot::where('is_active', true)
            ->orderBy('name')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | RETURN VIEW
        |--------------------------------------------------------------------------
        */

        return view(
            'products.import',
            compact(
                'suppliers',
                'depots'
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | PREVIEW IMPORT
    |--------------------------------------------------------------------------
    */

   public function preview(Request $request)
{
    /*
    |--------------------------------------------------------------------------
    | VALIDATION
    |--------------------------------------------------------------------------
    */

    $request->validate([
        'file' => 'required|mimes:xlsx,xls,csv',
        'supplier_id' => 'required|exists:suppliers,id',
        'depot_id' => 'required|exists:depots,id',
    ]);

    /*
    |--------------------------------------------------------------------------
    | FOURNISSEUR / DEPOT
    |--------------------------------------------------------------------------
    */

    $supplier = Supplier::findOrFail(
        $request->supplier_id
    );

    $depot = Depot::findOrFail(
        $request->depot_id
    );

    /*
    |--------------------------------------------------------------------------
    | LECTURE EXCEL
    |--------------------------------------------------------------------------
    */

    $rows = Excel::toCollection(
        collect([]),
        $request->file('file')
    )->first();

    /*
    |--------------------------------------------------------------------------
    | IGNORER HEADER
    |--------------------------------------------------------------------------
    */

    $rows = $rows->skip(1);

    $data = [];

    /*
    |--------------------------------------------------------------------------
    | LOOP
    |--------------------------------------------------------------------------
    */

    foreach ($rows as $index => $row) {

        /*
        |--------------------------------------------------------------------------
        | NUMERO REEL DE LA LIGNE EXCEL
        |--------------------------------------------------------------------------
        */

        $excelLine = $index + 1;

        /*
        |--------------------------------------------------------------------------
        | IGNORER LES LIGNES COMPLETEMENT VIDES
        |--------------------------------------------------------------------------
        */

        $allEmpty = true;

        for ($i = 0; $i <= 14; $i++) {

            if (
                trim((string) ($row[$i] ?? '')) !== ''
            ) {
                $allEmpty = false;
                break;
            }
        }

        if ($allEmpty) {
            continue;
        }

        /*
        |--------------------------------------------------------------------------
        | CHAMPS OBLIGATOIRES
        |--------------------------------------------------------------------------
        */

        $errors = [];

        if (
            trim((string) ($row[0] ?? '')) === ''
        ) {
            $errors[] = 'REFERENCE';
        }

        if (
            trim((string) ($row[1] ?? '')) === ''
        ) {
            $errors[] = 'DESIGNATION';
        }

        if (
            trim((string) ($row[2] ?? '')) === ''
        ) {
            $errors[] = 'MARQUE';
        }

        if (
            trim((string) ($row[3] ?? '')) === ''
        ) {
            $errors[] = 'MODELE';
        }

        /*
        |--------------------------------------------------------------------------
        | QUANTITE
        |--------------------------------------------------------------------------
        */

        if (
            !isset($row[8]) ||
            trim((string) $row[8]) === '' ||
            !is_numeric($row[8]) ||
            (float) $row[8] < 0
        ) {
            $errors[] = 'QUANTITE';
        }

        /*
        |--------------------------------------------------------------------------
        | PRIX ACHAT
        |--------------------------------------------------------------------------
        */

        if (
            !isset($row[11]) ||
            trim((string) $row[11]) === '' ||
            !is_numeric($row[11]) ||
            (float) $row[11] < 0
        ) {
            $errors[] = 'PRIX_ACHAT';
        }

        /*
        |--------------------------------------------------------------------------
        | COEFFICIENT ACHAT
        |--------------------------------------------------------------------------
        */

        if (
            !isset($row[12]) ||
            trim((string) $row[12]) === '' ||
            !is_numeric($row[12]) ||
            (float) $row[12] < 0
        ) {
            $errors[] = 'COEF_ACHAT';
        }

        /*
        |--------------------------------------------------------------------------
        | COEFFICIENT VENTE
        |--------------------------------------------------------------------------
        */

        if (
            !isset($row[13]) ||
            trim((string) $row[13]) === '' ||
            !is_numeric($row[13]) ||
            (float) $row[13] < 0
        ) {
            $errors[] = 'COEF_VENTE';
        }

        /*
        |--------------------------------------------------------------------------
        | DONNEES DE BASE
        |--------------------------------------------------------------------------
        */

        $reference =
            trim((string) ($row[0] ?? ''));

        $newQuantity =
            isset($row[8]) &&
            is_numeric($row[8])
                ? (float) $row[8]
                : 0;

        $newPurchasePrice =
            isset($row[11]) &&
            is_numeric($row[11])
                ? (float) $row[11]
                : 0;

        $latestCoefPurchase =
            isset($row[12]) &&
            is_numeric($row[12])
                ? (float) $row[12]
                : 0;

        $coefSale =
            isset($row[13]) &&
            is_numeric($row[13])
                ? (float) $row[13]
                : 0;

        /*
        |--------------------------------------------------------------------------
        | RECHERCHER LE PRODUIT EXISTANT
        |--------------------------------------------------------------------------
        |
        | La référence est utilisée pour déterminer s'il s'agit :
        |
        | - d'un nouveau produit
        | - ou d'un nouvel arrivage d'un produit déjà existant
        |
        */

        $existingProduct = null;

        if ($reference !== '') {

            $existingProduct = Product::where(
                'reference',
                $reference
            )->first();
        }

        /*
        |--------------------------------------------------------------------------
        | VARIABLES POUR LA PREVISUALISATION
        |--------------------------------------------------------------------------
        */

        $oldQuantity = 0;

        $oldAveragePurchasePrice = 0;

        $weightedPurchasePrice =
            $newPurchasePrice;

        $futureQuantity =
            $newQuantity;

        /*
        |--------------------------------------------------------------------------
        | PRODUIT EXISTANT
        |--------------------------------------------------------------------------
        */

        if ($existingProduct) {

            /*
            |--------------------------------------------------------------------------
            | STOCK ACTUEL
            |--------------------------------------------------------------------------
            */

            $oldQuantity =
                (float) $existingProduct->quantity;

            /*
            |--------------------------------------------------------------------------
            | ANCIEN PRIX D'ACHAT MOYEN PONDERE
            |--------------------------------------------------------------------------
            |
            | purchase_price contient maintenant le prix d'achat moyen pondéré.
            |
            */

            $oldAveragePurchasePrice =
                (float) $existingProduct->purchase_price;

            /*
            |--------------------------------------------------------------------------
            | STOCK APRES IMPORT
            |--------------------------------------------------------------------------
            */

            $futureQuantity =
                $oldQuantity + $newQuantity;

            /*
            |--------------------------------------------------------------------------
            | PRIX D'ACHAT MOYEN PONDERE
            |--------------------------------------------------------------------------
            |
            | FORMULE :
            |
            | (stock actuel × ancien prix achat moyen)
            | +
            | (nouvelle quantité × nouveau prix achat)
            |
            | ------------------------------------------------
            | stock actuel + nouvelle quantité
            |
            */

            if ($newQuantity > 0) {

                if ($oldQuantity > 0) {

                    $weightedPurchasePrice = (
                        ($oldQuantity * $oldAveragePurchasePrice)
                        +
                        ($newQuantity * $newPurchasePrice)
                    ) / $futureQuantity;

                } else {

                    /*
                    |--------------------------------------------------------------------------
                    | STOCK ACTUEL = 0
                    |--------------------------------------------------------------------------
                    |
                    | L'ancien prix n'entre plus dans le calcul.
                    |
                    */

                    $weightedPurchasePrice =
                        $newPurchasePrice;
                }

            } else {

                /*
                |--------------------------------------------------------------------------
                | AUCUN NOUVEL ARRIVAGE
                |--------------------------------------------------------------------------
                */

                $weightedPurchasePrice =
                    $oldAveragePurchasePrice;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | ARRONDI PRIX D'ACHAT MOYEN
        |--------------------------------------------------------------------------
        */

        $weightedPurchasePrice =
            round(
                $weightedPurchasePrice,
                4
            );

        /*
        |--------------------------------------------------------------------------
        | PRIX DE REVIENT PONDERE
        |--------------------------------------------------------------------------
        |
        | REGLE METIER :
        |
        | Prix de revient pondéré
        | =
        | Prix d'achat moyen pondéré
        | ×
        | DERNIER coefficient d'achat
        |
        */

        $weightedCostPrice =
            round(
                $weightedPurchasePrice *
                $latestCoefPurchase,
                4
            );

        /*
        |--------------------------------------------------------------------------
        | PRIX DE VENTE
        |--------------------------------------------------------------------------
        */

        $salePrice =
            round(
                $weightedCostPrice *
                $coefSale,
                2
            );

        /*
        |--------------------------------------------------------------------------
        | UNITES
        |--------------------------------------------------------------------------
        */

        $unitType = strtolower(
            trim(
                (string) ($row[14] ?? 'piece')
            )
        );

        if ($unitType === '') {
            $unitType = 'piece';
        }

        $unitLabel =
            in_array(
                $unitType,
                ['litre', 'liter', 'l']
            )
                ? 'L'
                : (
                    in_array(
                        $unitType,
                        [
                            'kg',
                            'kilogramme',
                            'kilogram'
                        ]
                    )
                        ? 'Kg'
                        : (
                            $unitType === 'carton'
                                ? 'Carton'
                                : 'Pièce'
                        )
                );

        /*
        |--------------------------------------------------------------------------
        | DATA
        |--------------------------------------------------------------------------
        |
        | IMPORTANT :
        |
        | purchase_price envoyé à storeImport doit rester le prix du
        | NOUVEL ARRIVAGE.
        |
        | Il ne faut surtout pas envoyer weightedPurchasePrice comme
        | purchase_price, sinon storeImport ferait une deuxième pondération.
        |
        */

        $data[] = [

            'excel_line' =>
                $excelLine,

            'reference' =>
                $reference,

            'designation' =>
                trim((string) ($row[1] ?? '')),

            'brand_name' =>
                trim((string) ($row[2] ?? '')),

            'model_name' =>
                trim((string) ($row[3] ?? '')),

            'family_name' =>
                trim((string) ($row[4] ?? '')),

            'subfamily_name' =>
                trim((string) ($row[5] ?? '')),

            'rayon_name' =>
                trim((string) ($row[6] ?? '')),

            'location_name' =>
                trim((string) ($row[7] ?? '')),

            /*
            |--------------------------------------------------------------------------
            | QUANTITE DU NOUVEL ARRIVAGE
            |--------------------------------------------------------------------------
            */

            'quantity' =>
                $newQuantity,

            'min_stock' =>
                is_numeric($row[9] ?? null)
                    ? (float) $row[9]
                    : 0,

            'max_stock' =>
                is_numeric($row[10] ?? null)
                    ? (float) $row[10]
                    : 0,

            /*
            |--------------------------------------------------------------------------
            | PRIX D'ACHAT REEL DU NOUVEL ARRIVAGE
            |--------------------------------------------------------------------------
            |
            | Ce champ est envoyé à storeImport().
            |
            */

            'purchase_price' =>
                $newPurchasePrice,

            /*
            |--------------------------------------------------------------------------
            | DERNIER COEFFICIENT D'ACHAT
            |--------------------------------------------------------------------------
            */

            'coef_purchase' =>
                $latestCoefPurchase,

            /*
            |--------------------------------------------------------------------------
            | PRIX DE REVIENT QUI SERA ENREGISTRE
            |--------------------------------------------------------------------------
            */

            'cost_price' =>
                $weightedCostPrice,

            /*
            |--------------------------------------------------------------------------
            | COEFFICIENT DE VENTE
            |--------------------------------------------------------------------------
            */

            'coef_sale' =>
                $coefSale,

            /*
            |--------------------------------------------------------------------------
            | PRIX DE VENTE QUI SERA ENREGISTRE
            |--------------------------------------------------------------------------
            */

            'sale_price' =>
                $salePrice,

            /*
            |--------------------------------------------------------------------------
            | INFORMATIONS DE PREVISUALISATION
            |--------------------------------------------------------------------------
            |
            | Ces champs permettent à la vue d'afficher le détail du calcul.
            |
            */

            'existing_product' =>
                $existingProduct !== null,

            'old_quantity' =>
                $oldQuantity,

            'old_purchase_price' =>
                round(
                    $oldAveragePurchasePrice,
                    4
                ),

            'weighted_purchase_price' =>
                $weightedPurchasePrice,

            'future_quantity' =>
                $futureQuantity,

            /*
            |--------------------------------------------------------------------------
            | UNITES
            |--------------------------------------------------------------------------
            */

            'unit_type' =>
                $unitType,

            'unit_label' =>
                $unitLabel,

            'status' =>
                'disponible',

            'errors' =>
                $errors,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | RETURN VIEW
    |--------------------------------------------------------------------------
    */

    return view(
        'products.import_preview',
        compact(
            'data',
            'supplier',
            'depot'
        )
    );
}

    /*
    |--------------------------------------------------------------------------
    | STORE IMPORT
    |--------------------------------------------------------------------------
    */


   public function storeImport(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | VALIDATION GENERALE
        |--------------------------------------------------------------------------
        */

        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'depot_id' => 'required|exists:depots,id',
            'products' => 'required|array|min:1',
        ]);

        $products = $request->products;

        /*
        |--------------------------------------------------------------------------
        | FOURNISSEUR / DEPOT
        |--------------------------------------------------------------------------
        */

        $supplier = Supplier::findOrFail(
            $request->supplier_id
        );

        $depot = Depot::findOrFail(
            $request->depot_id
        );

        /*
        |--------------------------------------------------------------------------
        | VERIFICATION CHAMPS OBLIGATOIRES
        |--------------------------------------------------------------------------
        */

        foreach ($products as $index => $product) {

            $excelLine = (int) (
                $product['excel_line']
                ?? ($index + 2)
            );

            $requiredFields = [
                'reference' => 'REFERENCE',
                'designation' => 'DESIGNATION',
                'brand_name' => 'MARQUE',
                'model_name' => 'MODELE',
                'purchase_price' => 'PRIX_ACHAT',
                'coef_purchase' => 'COEF_ACHAT',
                'coef_sale' => 'COEF_VENTE',
            ];

            foreach ($requiredFields as $field => $label) {

                if (
                    !array_key_exists($field, $product) ||
                    trim((string) ($product[$field] ?? '')) === ''
                ) {
                    return redirect()
                        ->back()
                        ->withInput()
                        ->with(
                            'error',
                            'Ligne ' .
                            $excelLine .
                            ' : ' .
                            $label
                        );
                }
            }

            /*
            |--------------------------------------------------------------------------
            | VERIFICATION DES CHAMPS NUMERIQUES OBLIGATOIRES
            |--------------------------------------------------------------------------
            */

            $numericRequiredFields = [
                'purchase_price' => 'PRIX_ACHAT',
                'coef_purchase' => 'COEF_ACHAT',
                'coef_sale' => 'COEF_VENTE',
            ];

            foreach ($numericRequiredFields as $field => $label) {

                if (
                    !is_numeric($product[$field]) ||
                    (float) $product[$field] < 0
                ) {
                    return redirect()
                        ->back()
                        ->withInput()
                        ->with(
                            'error',
                            'Ligne ' .
                            $excelLine .
                            ' : ' .
                            $label
                        );
                }
            }

            /*
            |--------------------------------------------------------------------------
            | VERIFICATION QUANTITE
            |--------------------------------------------------------------------------
            */

            $quantity = $product['quantity'] ?? 0;

            if (
                !is_numeric($quantity) ||
                (float) $quantity < 0
            ) {
                return redirect()
                    ->back()
                    ->withInput()
                    ->with(
                        'error',
                        'Ligne ' .
                        $excelLine .
                        ' : QUANTITE invalide.'
                    );
            }
        }

        /*
        |--------------------------------------------------------------------------
        | LOOP PRODUITS
        |--------------------------------------------------------------------------
        */

        foreach ($products as $product) {

            /*
            |--------------------------------------------------------------------------
            | RELATIONS
            |--------------------------------------------------------------------------
            */

            $brandName =
                trim((string) $product['brand_name']);

            $modelName =
                trim((string) $product['model_name']);

            $familyName =
                trim((string) ($product['family_name'] ?? ''));

            $subfamilyName =
                trim((string) ($product['subfamily_name'] ?? ''));

            $rayonName =
                trim((string) ($product['rayon_name'] ?? ''));

            $locationName =
                trim((string) ($product['location_name'] ?? ''));

            $familyName =
                $familyName !== ''
                    ? $familyName
                    : 'Non défini';

            $subfamilyName =
                $subfamilyName !== ''
                    ? $subfamilyName
                    : 'Non défini';

            $rayonName =
                $rayonName !== ''
                    ? $rayonName
                    : 'Non défini';

            $locationName =
                $locationName !== ''
                    ? $locationName
                    : 'Non défini';

            /*
            |--------------------------------------------------------------------------
            | CREATION / RECUPERATION DES RELATIONS
            |--------------------------------------------------------------------------
            */

            $brand = Brand::firstOrCreate([
                'name' => $brandName,
            ]);

            $model = CarModel::firstOrCreate([
                'name' => $modelName,
                'brand_id' => $brand->id,
            ]);

            $family = FamilyModel::firstOrCreate([
                'name' => $familyName,
            ]);

            $subfamily = Subfamily::firstOrCreate([
                'name' => $subfamilyName,
                'family_id' => $family->id,
            ]);

            $rayon = Rayon::firstOrCreate([
                'name' => $rayonName,
            ]);

            $location = Location::firstOrCreate([
                'name' => $locationName,
                'rayon_id' => $rayon->id,
            ]);

            /*
            |--------------------------------------------------------------------------
            | DONNEES DU NOUVEL ARRIVAGE
            |--------------------------------------------------------------------------
            */

            $newQuantity =
                (float) ($product['quantity'] ?? 0);

            $newPurchasePrice =
                (float) $product['purchase_price'];

            /*
            |--------------------------------------------------------------------------
            | DERNIER COEFFICIENT D'ACHAT
            |--------------------------------------------------------------------------
            |
            | C'est le coefficient fourni par le nouvel arrivage.
            | Il sera utilisé sur le nouveau prix d'achat moyen pondéré.
            |
            */

            $latestCoefPurchase =
                (float) $product['coef_purchase'];

            /*
            |--------------------------------------------------------------------------
            | COEFFICIENT DE VENTE
            |--------------------------------------------------------------------------
            */

            $coefSale =
                (float) $product['coef_sale'];

            /*
            |--------------------------------------------------------------------------
            | PRODUIT EXISTANT / CREATION
            |--------------------------------------------------------------------------
            */

            $existingProduct = Product::where(
                'reference',
                $product['reference']
            )->first();

            /*
            |--------------------------------------------------------------------------
            | PRODUIT EXISTANT
            |--------------------------------------------------------------------------
            */

            if ($existingProduct) {

                /*
                |--------------------------------------------------------------------------
                | STOCK ACTUEL
                |--------------------------------------------------------------------------
                |
                | IMPORTANT :
                |
                | quantity représente le stock actuellement disponible.
                | C'est cette quantité qu'il faut utiliser pour la pondération.
                |
                | On ne doit PAS utiliser initial_quantity ou received_quantity.
                |
                */

                $oldQuantity =
                    (float) $existingProduct->quantity;

                /*
                |--------------------------------------------------------------------------
                | ANCIEN PRIX D'ACHAT MOYEN
                |--------------------------------------------------------------------------
                |
                | purchase_price représente maintenant le prix d'achat moyen pondéré
                | du stock restant.
                |
                */

                $oldAveragePurchasePrice =
                    (float) $existingProduct->purchase_price;

                /*
                |--------------------------------------------------------------------------
                | NOUVELLE QUANTITE TOTALE
                |--------------------------------------------------------------------------
                */

                $totalQuantity =
                    $oldQuantity + $newQuantity;

                /*
                |--------------------------------------------------------------------------
                | PRIX D'ACHAT MOYEN PONDERE
                |--------------------------------------------------------------------------
                |
                | FORMULE :
                |
                | (stock actuel × ancien prix d'achat moyen)
                | +
                | (nouvelle quantité × nouveau prix d'achat)
                |
                | ------------------------------------------------
                | stock actuel + nouvelle quantité
                |
                |
                | Exemple :
                |
                | stock actuel             = 40
                | ancien prix achat moyen  = 100
                | nouvel arrivage          = 100
                | nouveau prix achat       = 120
                |
                | ((40 × 100) + (100 × 120)) / 140
                |
                | = 114.2857
                |
                */

                if ($newQuantity > 0) {

                    if ($oldQuantity > 0) {

                        $weightedPurchasePrice = (
                            ($oldQuantity * $oldAveragePurchasePrice)
                            +
                            ($newQuantity * $newPurchasePrice)
                        ) / $totalQuantity;

                    } else {

                        /*
                        |--------------------------------------------------------------------------
                        | STOCK ACTUEL = 0
                        |--------------------------------------------------------------------------
                        |
                        | Lorsqu'il n'existe plus de stock, l'ancien prix ne doit
                        | pas participer au nouveau calcul.
                        |
                        */

                        $weightedPurchasePrice =
                            $newPurchasePrice;
                    }

                } else {

                    /*
                    |--------------------------------------------------------------------------
                    | PAS DE NOUVELLE QUANTITE
                    |--------------------------------------------------------------------------
                    |
                    | Aucun arrivage réel :
                    | on conserve le prix moyen existant.
                    |
                    */

                    $weightedPurchasePrice =
                        $oldAveragePurchasePrice;
                }

                /*
                |--------------------------------------------------------------------------
                | PRECISION DU PRIX D'ACHAT MOYEN
                |--------------------------------------------------------------------------
                */

                $weightedPurchasePrice =
                    round(
                        $weightedPurchasePrice,
                        4
                    );

                /*
                |--------------------------------------------------------------------------
                | PRIX DE REVIENT PONDERE
                |--------------------------------------------------------------------------
                |
                | REGLE METIER VALIDEE :
                |
                | Prix de revient pondéré
                | =
                | Prix d'achat moyen pondéré
                | ×
                | DERNIER coefficient d'achat
                |
                */

                $weightedCostPrice =
                    round(
                        $weightedPurchasePrice *
                        $latestCoefPurchase,
                        4
                    );

                /*
                |--------------------------------------------------------------------------
                | PRIX DE VENTE
                |--------------------------------------------------------------------------
                */

                $salePrice =
                    round(
                        $weightedCostPrice *
                        $coefSale,
                        2
                    );

                /*
                |--------------------------------------------------------------------------
                | MISE A JOUR PRODUIT
                |--------------------------------------------------------------------------
                */

                $existingProduct->update([

                    'designation' =>
                        $product['designation'],

                    'brand_id' =>
                        $brand->id,

                    'model_id' =>
                        $model->id,

                    'family_id' =>
                        $family->id,

                    'subfamily_id' =>
                        $subfamily->id,

                    'rayon_id' =>
                        $rayon->id,

                    'location_id' =>
                        $location->id,

                    /*
                    |--------------------------------------------------------------------------
                    | STOCK DISPONIBLE
                    |--------------------------------------------------------------------------
                    */

                    'quantity' =>
                        $totalQuantity,

                    /*
                    |--------------------------------------------------------------------------
                    | QUANTITE INITIALE CUMULEE
                    |--------------------------------------------------------------------------
                    */

                    'initial_quantity' =>
                        (float) $existingProduct->initial_quantity +
                        $newQuantity,

                    /*
                    |--------------------------------------------------------------------------
                    | QUANTITE RECUE CUMULEE
                    |--------------------------------------------------------------------------
                    */

                    'received_quantity' =>
                        (float) ($existingProduct->received_quantity ?? 0) +
                        $newQuantity,

                    'min_stock' =>
                        (float) ($product['min_stock'] ?? 0),

                    'max_stock' =>
                        (float) ($product['max_stock'] ?? 0),

                    /*
                    |--------------------------------------------------------------------------
                    | PRIX D'ACHAT MOYEN PONDERE
                    |--------------------------------------------------------------------------
                    */

                    'purchase_price' =>
                        $weightedPurchasePrice,

                    /*
                    |--------------------------------------------------------------------------
                    | DERNIER COEFFICIENT D'ACHAT
                    |--------------------------------------------------------------------------
                    */

                    'coef_purchase' =>
                        $latestCoefPurchase,

                    /*
                    |--------------------------------------------------------------------------
                    | PRIX DE REVIENT MOYEN PONDERE
                    |--------------------------------------------------------------------------
                    */

                    'cost_price' =>
                        $weightedCostPrice,

                    /*
                    |--------------------------------------------------------------------------
                    | COEFFICIENT DE VENTE
                    |--------------------------------------------------------------------------
                    */

                    'coef_sale' =>
                        $coefSale,

                    /*
                    |--------------------------------------------------------------------------
                    | PRIX DE VENTE
                    |--------------------------------------------------------------------------
                    */

                    'sale_price' =>
                        $salePrice,

                    'unit_type' =>
                        $product['unit_type'] ?? 'piece',

                    'unit_label' =>
                        $product['unit_label'] ?? 'Pièce',

                    'status' =>
                        'disponible',

                    'supply_status' =>
                        null,
                ]);

                $createdProduct =
                    $existingProduct;

            } else {

                /*
                |--------------------------------------------------------------------------
                | NOUVEAU PRODUIT
                |--------------------------------------------------------------------------
                |
                | Pour le premier arrivage :
                |
                | Prix achat moyen = prix achat du premier arrivage.
                |
                */

                $weightedPurchasePrice =
                    round(
                        $newPurchasePrice,
                        4
                    );

                /*
                |--------------------------------------------------------------------------
                | PRIX DE REVIENT
                |--------------------------------------------------------------------------
                */

                $weightedCostPrice =
                    round(
                        $weightedPurchasePrice *
                        $latestCoefPurchase,
                        4
                    );

                /*
                |--------------------------------------------------------------------------
                | PRIX DE VENTE
                |--------------------------------------------------------------------------
                */

                $salePrice =
                    round(
                        $weightedCostPrice *
                        $coefSale,
                        2
                    );

                /*
                |--------------------------------------------------------------------------
                | CREATION PRODUIT
                |--------------------------------------------------------------------------
                */

                $createdProduct = Product::create([

                    'reference' =>
                        $product['reference'],

                    'designation' =>
                        $product['designation'],

                    'unit_type' =>
                        $product['unit_type'] ?? 'piece',

                    'unit_label' =>
                        $product['unit_label'] ?? 'Pièce',

                    'brand_id' =>
                        $brand->id,

                    'model_id' =>
                        $model->id,

                    'family_id' =>
                        $family->id,

                    'subfamily_id' =>
                        $subfamily->id,

                    'rayon_id' =>
                        $rayon->id,

                    'location_id' =>
                        $location->id,

                    /*
                    |--------------------------------------------------------------------------
                    | STOCK
                    |--------------------------------------------------------------------------
                    */

                    'quantity' =>
                        $newQuantity,

                    'initial_quantity' =>
                        $newQuantity,

                    'received_quantity' =>
                        $newQuantity,

                    'min_stock' =>
                        (float) ($product['min_stock'] ?? 0),

                    'max_stock' =>
                        (float) ($product['max_stock'] ?? 0),

                    /*
                    |--------------------------------------------------------------------------
                    | PRIX
                    |--------------------------------------------------------------------------
                    */

                    'purchase_price' =>
                        $weightedPurchasePrice,

                    'coef_purchase' =>
                        $latestCoefPurchase,

                    'cost_price' =>
                        $weightedCostPrice,

                    'coef_sale' =>
                        $coefSale,

                    'sale_price' =>
                        $salePrice,

                    'status' =>
                        'disponible',

                    'supply_status' =>
                        null,
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | STOCK DEPOT
            |--------------------------------------------------------------------------
            */

            $depotStock = ProductDepotStock::firstOrCreate(
                [
                    'product_id' =>
                        $createdProduct->id,

                    'depot_id' =>
                        $depot->id,
                ],
                [
                    'quantity' => 0,
                ]
            );

            $depotStock->quantity =
                (float) $depotStock->quantity +
                $newQuantity;

            /*
            |--------------------------------------------------------------------------
            | RAYON / EMPLACEMENT PROPRES AU DEPOT
            |--------------------------------------------------------------------------
            */

            $depotStock->rayon_id =
                $rayon->id;

            $depotStock->location_id =
                $location->id;

            $depotStock->save();

            /*
            |--------------------------------------------------------------------------
            | MOUVEMENT DE STOCK
            |--------------------------------------------------------------------------
            */

            StockMovement::create([

                'product_id' =>
                    $createdProduct->id,

                'type' =>
                    'in',

                'quantity' =>
                    $newQuantity,

                'source' =>
                    'Import Excel',

                'reference' =>
                    $createdProduct->reference,

                'user_id' =>
                    auth()->id(),
            ]);

            /*
            |--------------------------------------------------------------------------
            | FOURNISSEUR
            |--------------------------------------------------------------------------
            |
            | IMPORTANT :
            |
            | Ici nous gardons le VRAI prix d'achat du nouvel arrivage.
            |
            | Il ne faut PAS enregistrer weightedPurchasePrice ici.
            |
            */

            $createdProduct
                ->suppliers()
                ->syncWithoutDetaching([

                    $supplier->id => [

                        'supplier_reference' =>
                            $createdProduct->reference,

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
        }

        /*
        |--------------------------------------------------------------------------
        | REDIRECT
        |--------------------------------------------------------------------------
        */

        return redirect()
            ->route('products.index')
            ->with(
                'success',
                'Importation effectuée avec succès.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | PRODUITS DISPONIBLES
    |--------------------------------------------------------------------------
    */

    public function available(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | QUERY
        |--------------------------------------------------------------------------
        */

        $query = Product::with([
            'brand',
            'model',
            'family',
            'subfamily',
            'rayon',
            'location',
            'stockMovements',
        ]);

        /*
        |--------------------------------------------------------------------------
        | RECHERCHE
        |--------------------------------------------------------------------------
        */

        if ($request->filled('search')) {

            $search = trim(
                (string) $request->input('search')
            );

            $query->where(function ($q) use ($search) {

                $q->where(
                    'designation',
                    'like',
                    '%' . $search . '%'
                )

                ->orWhere(
                    'reference',
                    'like',
                    '%' . $search . '%'
                )

                ->orWhereHas(
                    'brand',
                    function ($brandQuery) use ($search) {

                        $brandQuery->where(
                            'name',
                            'like',
                            '%' . $search . '%'
                        );
                    }
                )

                ->orWhereHas(
                    'model',
                    function ($modelQuery) use ($search) {

                        $modelQuery->where(
                            'name',
                            'like',
                            '%' . $search . '%'
                        );
                    }
                );
            });
        }

        /*
        |--------------------------------------------------------------------------
        | UNIQUEMENT LES PRODUITS RÉELLEMENT DISPONIBLES
        |--------------------------------------------------------------------------
        |
        | Un produit apparaît ici seulement lorsque :
        |
        | 1. son statut = disponible
        | 2. sa quantité physique disponible > 0
        |
        | Exemple :
        |
        | initial_quantity  = 5
        | received_quantity = 3
        | quantity          = 3
        | status            = disponible
        |
        | => le produit apparaît dans cette liste.
        |
        */

       $products = $query
            ->where(
                'quantity',
                '>',
                0
            )
            ->latest()
            ->paginate(10);

        /*
        |--------------------------------------------------------------------------
        | CONSERVATION DE LA RECHERCHE DANS LA PAGINATION
        |--------------------------------------------------------------------------
        |
        | On utilise appends() au lieu de withQueryString().
        | Cela évite également l'avertissement Intelephense.
        |
        */

        $products->appends(
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

        /*
        |--------------------------------------------------------------------------
        | DÉPÔTS
        |--------------------------------------------------------------------------
        */

        $depots = Depot::query()
            ->where(
                'is_active',
                true
            )
            ->orderBy('name')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | RETOUR VIEW
        |--------------------------------------------------------------------------
        */

        return view(
            'products.index',
            [
                'products' =>
                    $products,

                'suppliers' =>
                    $suppliers,

                'depots' =>
                    $depots,

                'pageTitle' =>
                    'Produits disponibles',

                'hideButtons' =>
                    false,
            ]
        );
    }

   /*
    |--------------------------------------------------------------------------
    | PRODUITS NON DISPONIBLES
    |--------------------------------------------------------------------------
    |
    | Un produit est considéré comme non totalement disponible lorsque :
    |
    | received_quantity < initial_quantity
    |
    | Exemple :
    |
    | initial_quantity  = 5
    | received_quantity = 3
    |
    | quantité non disponible = 2
    |
    */

    public function unavailable(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | QUERY
        |--------------------------------------------------------------------------
        */

        $query = Product::query()
            ->with([
                'brand',
                'model',
                'family',
                'subfamily',
                'rayon',
                'location',
                'stockMovements',
            ])
            ->whereColumn(
                'received_quantity',
                '<',
                'initial_quantity'
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

            $query->where(
                function ($q) use ($search) {

                    $q->where(
                        'reference',
                        'like',
                        '%' . $search . '%'
                    )

                    ->orWhere(
                        'designation',
                        'like',
                        '%' . $search . '%'
                    )

                    ->orWhereHas(
                        'brand',
                        function ($brandQuery) use ($search) {

                            $brandQuery->where(
                                'name',
                                'like',
                                '%' . $search . '%'
                            );
                        }
                    )

                    ->orWhereHas(
                        'model',
                        function ($modelQuery) use ($search) {

                            $modelQuery->where(
                                'name',
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
        | PAGINATION
        |--------------------------------------------------------------------------
        */

        $products = $query
            ->latest()
            ->paginate(10);

        $products->appends(
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

        /*
        |--------------------------------------------------------------------------
        | DÉPÔTS
        |--------------------------------------------------------------------------
        */

        $depots = Depot::query()
            ->where(
                'is_active',
                true
            )
            ->orderBy('name')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | RETOUR
        |--------------------------------------------------------------------------
        |
        | IMPORTANT :
        | On réutilise products.index.
        | Il ne faut PAS retourner products.unavailable.
        |
        */

        return view(
            'products.index',
            [
                'products' =>
                    $products,

                'suppliers' =>
                    $suppliers,

                'depots' =>
                    $depots,

                'pageTitle' =>
                    'Pièces non disponibles',

                'hideButtons' =>
                    true,

                'isUnavailablePage' =>
                    true,
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | PRODUITS VENDUS
    |--------------------------------------------------------------------------
    */

   public function sold(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | FILTRES
        |--------------------------------------------------------------------------
        */

        $search = trim(
            (string) $request->input('search', '')
        );

        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        /*
        |--------------------------------------------------------------------------
        | QUERY
        |--------------------------------------------------------------------------
        |
        | sold_quantity est calculée uniquement à partir :
        | - des ventes non annulées
        | - de la période sélectionnée
        |
        */

        $query = Product::with([
            'brand',
            'model',
            'family',
            'subfamily',
            'rayon',
            'location',
        ])
        ->withSum([
            'saleItems as sold_quantity' => function ($query) use (
                $dateFrom,
                $dateTo
            ) {
                $query->whereHas(
                    'sale',
                    function ($q) use ($dateFrom, $dateTo) {

                        /*
                        |--------------------------------------------------------------------------
                        | EXCLURE LES VENTES ANNULÉES
                        |--------------------------------------------------------------------------
                        */

                        $q->whereNotIn(
                            'status',
                            ['cancelled']
                        );

                        /*
                        |--------------------------------------------------------------------------
                        | DATE DÉBUT
                        |--------------------------------------------------------------------------
                        */

                        if ($dateFrom) {
                            $q->whereDate(
                                'created_at',
                                '>=',
                                $dateFrom
                            );
                        }

                        /*
                        |--------------------------------------------------------------------------
                        | DATE FIN
                        |--------------------------------------------------------------------------
                        */

                        if ($dateTo) {
                            $q->whereDate(
                                'created_at',
                                '<=',
                                $dateTo
                            );
                        }
                    }
                );
            },
        ], 'quantity')
        ->having(
            'sold_quantity',
            '>',
            0
        );

        /*
        |--------------------------------------------------------------------------
        | RECHERCHE
        |--------------------------------------------------------------------------
        */

        if ($search !== '') {
            $query->where(
                function ($q) use ($search) {
                    $q->where(
                        'designation',
                        'like',
                        '%' . $search . '%'
                    )
                    ->orWhere(
                        'reference',
                        'like',
                        '%' . $search . '%'
                    );
                }
            );
        }

        /*
        |--------------------------------------------------------------------------
        | DATA
        |--------------------------------------------------------------------------
        */

        $products = $query
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $suppliers = Supplier::orderBy('name')
            ->get();

        $depots = Depot::where(
            'is_active',
            true
        )
        ->orderBy('name')
        ->get();

        /*
        |--------------------------------------------------------------------------
        | VIEW
        |--------------------------------------------------------------------------
        */

        return view(
            'products.index',
            [
                'products' => $products,

                'suppliers' => $suppliers,

                'depots' => $depots,

                'pageTitle' => 'Produits vendus',

                'hideButtons' => true,

                'dateFrom' => $dateFrom,

                'dateTo' => $dateTo,
            ]
        );
    }
    /*
|--------------------------------------------------------------------------
| EXPORT EXCEL - PRODUITS VENDUS
|--------------------------------------------------------------------------
*/

   public function exportSoldExcel(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | FILTRES
        |--------------------------------------------------------------------------
        */

        $search = trim(
            (string) $request->input(
                'search',
                ''
            )
        );

        $dateFrom = $request->input(
            'date_from'
        );

        $dateTo = $request->input(
            'date_to'
        );

        /*
        |--------------------------------------------------------------------------
        | NOM DU FICHIER
        |--------------------------------------------------------------------------
        */

        $fileName =
            'produits_vendus_'
            . now()->format('Y-m-d_H-i-s')
            . '.xlsx';

        /*
        |--------------------------------------------------------------------------
        | EXPORT
        |--------------------------------------------------------------------------
        */

        return Excel::download(
            new SoldProductsExport(
                $search !== ''
                    ? $search
                    : null,

                $dateFrom ?: null,

                $dateTo ?: null
            ),
            $fileName
        );
    }
    /**
     * Liste des pièces à commander.
     *
     * Une pièce doit être commandée lorsque :
     *
     * quantité disponible <= stock minimum
     *
     * Cela inclut :
     * - les produits en rupture
     * - les produits avec stock faible
     */
    public function toOrder(Request $request)
    {
        $search = trim((string) $request->input('search'));

        $products = Product::query()

            /*
            |--------------------------------------------------------------------------
            | Relations nécessaires pour afficher Marque et Modèle
            |--------------------------------------------------------------------------
            */

            ->with([
                'brand',
                'model',
            ])

            /*
            |--------------------------------------------------------------------------
            | Seulement les produits à réapprovisionner
            |--------------------------------------------------------------------------
            |
            | quantity = quantité actuellement disponible
            | min_stock = seuil minimum défini pour le produit
            |
            */

           ->where(function ($query) {

                $query
                    ->whereColumn(
                        'quantity',
                        '<=',
                        'min_stock'
                    )

                    ->orWhereIn(
                        'supply_status',
                        [
                            'rupture',
                            'en_recherche',
                            'en_commande',
                            'partiellement_recu',
                        ]
                    )

                    ->orWhereColumn(
                        'received_quantity',
                        '<',
                        'initial_quantity'
                    );
            })

            /*
            |--------------------------------------------------------------------------
            | Recherche
            |--------------------------------------------------------------------------
            */

            ->when($search !== '', function ($query) use ($search) {

                $query->where(function ($q) use ($search) {

                    /*
                    |--------------------------------------------------------------
                    | Référence
                    |--------------------------------------------------------------
                    */

                    $q->where(
                        'reference',
                        'like',
                        '%' . $search . '%'
                    )

                    /*
                    |--------------------------------------------------------------
                    | Désignation
                    |--------------------------------------------------------------
                    */

                    ->orWhere(
                        'designation',
                        'like',
                        '%' . $search . '%'
                    )

                    /*
                    |--------------------------------------------------------------
                    | Marque
                    |--------------------------------------------------------------
                    */

                    ->orWhereHas(
                        'brand',
                        function ($brandQuery) use ($search) {

                            $brandQuery->where(
                                'name',
                                'like',
                                '%' . $search . '%'
                            );
                        }
                    )

                    /*
                    |--------------------------------------------------------------
                    | Modèle
                    |--------------------------------------------------------------
                    */

                    ->orWhereHas(
                        'model',
                        function ($modelQuery) use ($search) {

                            $modelQuery->where(
                                'name',
                                'like',
                                '%' . $search . '%'
                            );
                        }
                    );

                });

            })

            /*
            |--------------------------------------------------------------------------
            | Priorité
            |--------------------------------------------------------------------------
            |
            | Les quantités les plus faibles apparaissent en premier.
            |
            */

            ->orderBy('quantity', 'asc')
            ->orderBy('designation', 'asc')

            /*
            |--------------------------------------------------------------------------
            | Pagination
            |--------------------------------------------------------------------------
            */

            ->paginate(20);
            $products->appends(
                $request->query()
            );



        return view(
            'products.to-order',
            compact('products')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | MODIFIER LE STATUT DE RÉAPPROVISIONNEMENT
    |--------------------------------------------------------------------------
    */

    public function updateSupplyStatus(
        Request $request,
        Product $product
    ) {
        $request->validate([
            'supply_status' => [
                'required',
                'in:rupture,en_recherche,en_commande,partiellement_recu,recu',
            ],
        ]);

        $product->update([
            'supply_status' =>
                $request->supply_status,
        ]);

        return redirect()
            ->back()
            ->with(
                'success',
                'Statut de réapprovisionnement mis à jour avec succès.'
            );
    }

   /*
|--------------------------------------------------------------------------
| SHOW
|--------------------------------------------------------------------------
*/

public function show(Product $product)
{
    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    $product->load([

        'brand',
        'model',
        'family',
        'subfamily',
        'rayon',
        'location',

        'suppliers',

        'stockMovements',

        'saleItems.sale',

        'depotStocks.depot',
        'depotStocks.rayon',
        'depotStocks.location',

    ]);

    /*
    |--------------------------------------------------------------------------
    | SOLD QUANTITY
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | QUANTITÉ VENDUE RÉELLE
    |--------------------------------------------------------------------------
    |
    | Ne pas utiliser tous les mouvements "out", car un ajustement inventaire
    | négatif crée lui aussi un mouvement "out" sans être une vente.
    |
    */
    $soldQuantity = $product->saleItems()
        ->whereHas('sale', function ($query) {
            $query->whereNotIn(
                'status',
                ['cancelled', 'annulé', 'annule']
            );
        })
        ->sum('quantity');

    /*
    |--------------------------------------------------------------------------
    | AVAILABLE QUANTITY
    |--------------------------------------------------------------------------
    */

    $availableQuantity = $product->quantity;

    /*
    |--------------------------------------------------------------------------
    | RETURN VIEW
    |--------------------------------------------------------------------------
    */

    return view(
        'products.show',
        compact(
            'product',
            'soldQuantity',
            'availableQuantity'
        )
    );
}
 /*
|--------------------------------------------------------------------------
| Create
|--------------------------------------------------------------------------
*/

public function create()
{
    $brands = Brand::orderBy('name')->get();
    $models = CarModel::orderBy('name')->get();
    $families = FamilyModel::orderBy('name')->get();
    $subfamilies = Subfamily::orderBy('name')->get();
    $rayons = Rayon::orderBy('name')->get();
    $locations = Location::orderBy('name')->get();
    $depots = Depot::where('is_active', true)
            ->orderBy('name')
            ->get();

    return view(
        'products.create',
        compact(
            'brands',
            'models',
            'families',
            'subfamilies',
            'rayons',
            'locations',
            'depots'

        )
    );
}

/*
|--------------------------------------------------------------------------
| STORE
|--------------------------------------------------------------------------
|
| Création manuelle d'un produit.
|
| IMPORTANT :
|
| La colonne products.status accepte uniquement :
|
| - disponible
| - vendu
| - retourne
|
| On ne stocke donc JAMAIS "non_disponible" dans status.
|
| La disponibilité physique est déterminée par :
|
| quantity
| received_quantity
| initial_quantity
|
*/

public function store(Request $request)
{
    /*
    |--------------------------------------------------------------------------
    | VALIDATION
    |--------------------------------------------------------------------------
    */

    $request->validate([

        'reference' => [
            'required',
            'string',
            'max:255',
            'unique:products,reference',
        ],

        'designation' => [
            'required',
            'string',
            'max:255',
        ],

        'brand_id' => [
            'required',
            'exists:brands,id',
        ],

        'model_id' => [
            'required',
            'exists:models,id',
        ],

        'family_id' => [
            'nullable',
            'exists:families,id',
        ],

        'subfamily_id' => [
            'nullable',
            'exists:subfamilies,id',
        ],

        'rayon_id' => [
            'nullable',
            'exists:rayons,id',
        ],

        'location_id' => [
            'nullable',
            'exists:locations,id',
        ],

        'depot_id' => [
                'required',
                'exists:depots,id',
            ],

        /*
        |--------------------------------------------------------------------------
        | QUANTITÉ INITIALE
        |--------------------------------------------------------------------------
        */

        'quantity' => [
            'required',
            'numeric',
            'min:0',
        ],

        /*
        |--------------------------------------------------------------------------
        | ÉTAT DE STOCK
        |--------------------------------------------------------------------------
        |
        | Ce champ vient du formulaire.
        |
        | Il ne correspond PAS directement à products.status.
        |
        */

        'stock_state' => [
            'required',
            'in:available,unavailable',
        ],

        /*
        |--------------------------------------------------------------------------
        | SEUILS
        |--------------------------------------------------------------------------
        */

        'min_stock' => [
            'nullable',
            'numeric',
            'min:0',
        ],

        'max_stock' => [
            'nullable',
            'numeric',
            'min:0',
        ],

        /*
        |--------------------------------------------------------------------------
        | PRIX
        |--------------------------------------------------------------------------
        */

        'purchase_price' => [
            'required',
            'numeric',
            'min:0',
        ],

        'coef_purchase' => [
            'required',
            'numeric',
            'min:0',
        ],

        'cost_price' => [
            'nullable',
            'numeric',
            'min:0',
        ],

        'coef_sale' => [
            'required',
            'numeric',
            'min:0',
        ],

        'sale_price' => [
            'required',
            'numeric',
            'min:0',
        ],

        /*
        |--------------------------------------------------------------------------
        | UNITÉ
        |--------------------------------------------------------------------------
        */

        'unit_type' => [
            'nullable',
            'string',
        ],

        'unit_label' => [
            'nullable',
            'string',
            'max:50',
        ],
    ]);

    /*
    |--------------------------------------------------------------------------
    | QUANTITÉ SAISIE
    |--------------------------------------------------------------------------
    |
    | Cette quantité représente toujours la quantité initiale.
    |
    */

    $initialQuantity =
        (float) $request->quantity;

    /*
    |--------------------------------------------------------------------------
    | ÉTAT DU STOCK
    |--------------------------------------------------------------------------
    */

    $isUnavailable =
        $request->input('stock_state')
        === 'unavailable';

    /*
    |--------------------------------------------------------------------------
    | QUANTITÉ DISPONIBLE
    |--------------------------------------------------------------------------
    |
    | Produit disponible :
    |
    | quantity = quantité saisie
    |
    | Produit non disponible :
    |
    | quantity = 0
    |
    */

    $availableQuantity =
        $isUnavailable
            ? 0
            : $initialQuantity;

    /*
    |--------------------------------------------------------------------------
    | QUANTITÉ REÇUE
    |--------------------------------------------------------------------------
    |
    | Produit disponible :
    |
    | received_quantity = quantité saisie
    |
    | Produit non disponible :
    |
    | received_quantity = 0
    |
    */

    $receivedQuantity =
        $isUnavailable
            ? 0
            : $initialQuantity;

   /*
    |--------------------------------------------------------------------------
    | CALCUL PRIX DE REVIENT
    |--------------------------------------------------------------------------
    */

    $purchasePrice =
        (float) $request->purchase_price;

    $coefPurchase =
        (float) $request->coef_purchase;

    $costPrice =
        $purchasePrice * $coefPurchase;

    /*
    |--------------------------------------------------------------------------
    | CALCUL PRIX DE VENTE
    |--------------------------------------------------------------------------
    */

    $coefSale =
        (float) $request->coef_sale;

    $salePrice =
        $costPrice * $coefSale;

    /*
    |--------------------------------------------------------------------------
    | UTILISER LE PRIX SAISI
    |--------------------------------------------------------------------------
    |
    | On conserve votre fonctionnement actuel.
    |
    */

    $salePrice =
        (float) $request->sale_price;

    /*
    |--------------------------------------------------------------------------
    | CRÉATION PRODUIT
    |--------------------------------------------------------------------------
    */

  $product = Product::create([

        /*
        |--------------------------------------------------------------------------
        | INFORMATIONS
        |--------------------------------------------------------------------------
        */

        'reference' =>
            $request->reference,

        'designation' =>
            $request->designation,

        /*
        |--------------------------------------------------------------------------
        | RELATIONS
        |--------------------------------------------------------------------------
        */

        'brand_id' =>
            $request->brand_id,

        'model_id' =>
            $request->model_id,

        'family_id' =>
            $request->family_id,

        'subfamily_id' =>
            $request->subfamily_id,

        'rayon_id' =>
            $request->rayon_id,

        'location_id' =>
            $request->location_id,

        /*
        |--------------------------------------------------------------------------
        | STOCK
        |--------------------------------------------------------------------------
        */

        'quantity' =>
            $availableQuantity,

        'initial_quantity' =>
            $initialQuantity,

        'received_quantity' =>
            $receivedQuantity,

        'min_stock' =>
            $request->min_stock ?? 0,

        'max_stock' =>
            $request->max_stock ?? 0,

        /*
        |--------------------------------------------------------------------------
        | PRIX
        |--------------------------------------------------------------------------
        */

        'purchase_price' =>
            $purchasePrice,

        'coef_purchase' =>
            $coefPurchase,

        'cost_price' =>
            $costPrice,

        'coef_sale' =>
            $coefSale,

        'sale_price' =>
            $salePrice,

        /*
        |--------------------------------------------------------------------------
        | STATUS
        |--------------------------------------------------------------------------
        |
        | IMPORTANT :
        |
        | On reste volontairement sur "disponible".
        |
        | Ce champ appartient au cycle de vie historique du produit.
        |
        | Une pièce physiquement absente sera reconnue grâce à quantity = 0.
        |
        | On ne met jamais :
        |
        | non_disponible
        |
        | car cette valeur n'existe pas dans votre ENUM MySQL.
        |
        */

        'status' =>
            'disponible',
           /*
            |--------------------------------------------------------------------------
            | STATUT DE RÉAPPROVISIONNEMENT
            |--------------------------------------------------------------------------
            |
            | Une nouvelle pièce créée sans stock commence en "rupture".
            |
            */

            'supply_status' =>
                $isUnavailable
                    ? 'rupture'
                    : null,

        /*
        |--------------------------------------------------------------------------
        | UNITÉ
        |--------------------------------------------------------------------------
        */

        'unit_type' =>
            $request->unit_type ?? 'piece',

        'unit_label' =>
            $request->unit_label ?? 'Pièce',
    ]);

        ProductDepotStock::updateOrCreate(
        [
            'product_id' => $product->id,
            'depot_id' => $request->depot_id,
        ],
        [
            'quantity' => $availableQuantity,
            'rayon_id' => $request->rayon_id,
            'location_id' => $request->location_id,
        ]
    );

    /*
    |--------------------------------------------------------------------------
    | REDIRECTION
    |--------------------------------------------------------------------------
    */

    return redirect()
        ->route('products.index')
        ->with(
            'success',
            $isUnavailable
                ? 'Produit créé avec succès. La pièce est enregistrée comme non disponible et reste à recevoir.'
                : 'Produit créé avec succès et disponible en stock.'
        );
}

/*
|--------------------------------------------------------------------------
| EXPORT EXCEL
|--------------------------------------------------------------------------
*/

public function exportExcel()
{
    return Excel::download(
        new ProductsExport,
        'stock.xlsx'
    );
}

/*
|--------------------------------------------------------------------------
| EXPORT PDF
|--------------------------------------------------------------------------
*/

/*public function exportPdf()
{
    $products = Product::with('depot')->get();

    $pdf = Pdf::loadView(
        'products.export_pdf',
        compact('products')
    );

    return $pdf->download('stock.pdf');
}*/

    /*
|--------------------------------------------------------------------------
| EDIT
|--------------------------------------------------------------------------
*/

    public function edit(Product $product)
    {

        /*
    |--------------------------------------------------------------------------
    | SECURITY
    |--------------------------------------------------------------------------
    */

    if(
        !in_array(auth()->user()->role, [
            'admin',
            'chef_magasinier'
        ])
    ){
        abort(403);
    }
        /*
        |--------------------------------------------------------------------------
        | RELATIONS
        |--------------------------------------------------------------------------
        */

        $brands = Brand::orderBy('name')->get();

        $models = CarModel::orderBy('name')->get();

        $families = FamilyModel::orderBy('name')->get();

        $subfamilies = Subfamily::orderBy('name')->get();

        $rayons = Rayon::orderBy('name')->get();

        $locations = Location::orderBy('name')->get();

        /*
        |--------------------------------------------------------------------------
        | RETURN VIEW
        |--------------------------------------------------------------------------
        */

        return view(
            'products.edit',
            compact(
                'product',
                'brands',
                'models',
                'families',
                'subfamilies',
                'rayons',
                'locations'
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */

    public function update(
        Request $request,
        Product $product
    ) {

        /*
        |--------------------------------------------------------------------------
        | VALIDATION
        |--------------------------------------------------------------------------
        */

        $request->validate([

            'reference' =>
                'required|string|max:255|unique:products,reference,' . $product->id,

            'designation' =>
                'required|string|max:255',


            'brand_id' =>
                'required|exists:brands,id',

            'model_id' =>
                'required|exists:models,id',

            'family_id' =>
                'nullable|exists:families,id',

            'subfamily_id' =>
                'nullable|exists:subfamilies,id',

            'rayon_id' =>
                'nullable|exists:rayons,id',

            'location_id' =>
                'nullable|exists:locations,id',

            /*
            |--------------------------------------------------------------------------
            | QUANTITÉ
            |--------------------------------------------------------------------------
            |
            | Le champ peut encore exister dans edit.blade.php pour compatibilité,
            | mais sa valeur est volontairement ignorée dans l'UPDATE.
            |
            */
            'quantity' =>
                'nullable|numeric|min:0',

            'min_stock' =>
                'nullable|numeric|min:0',

            'max_stock' =>
                'nullable|numeric|min:0',

            'purchase_price' =>
                'required|numeric|min:0',

            'coef_purchase' =>
                'required|numeric|min:0',

            'coef_sale' =>
                'required|numeric|min:0',

                'unit_type' => [
                    'required',
                    'string',
                    'in:piece,litre',
                ],

                'unit_label' => [
                    'nullable',
                    'string',
                    'max:50',
                ],
        ]);



        /*
        |--------------------------------------------------------------------------
        | CALCULS
        |--------------------------------------------------------------------------
        */

        $purchasePrice =
            (float) $request->purchase_price;

        $coefPurchase =
            (float) $request->coef_purchase;

        $coefSale =
            (float) $request->coef_sale;

        $costPrice =
            $purchasePrice * $coefPurchase;

        $salePrice =
            $costPrice * $coefSale;

        /*
        |--------------------------------------------------------------------------
        | UPDATE
        |--------------------------------------------------------------------------
        */

        $product->update([

            'reference' =>
                $request->reference,

            'designation' =>
                $request->designation,

            'unit_type' =>
                $request->unit_type,

            'unit_label' =>
                $request->unit_type === 'litre'
                    ? 'L'
                    : 'Pièce',

            'brand_id' =>
                $request->brand_id,

            'model_id' =>
                $request->model_id,

            'family_id' =>
                $request->family_id,

            'subfamily_id' =>
                $request->subfamily_id,

            'rayon_id' =>
                $request->rayon_id,

            'location_id' =>
                $request->location_id,

            /*
            |--------------------------------------------------------------------------
            | NE PAS MODIFIER LE STOCK ICI
            |--------------------------------------------------------------------------
            |
            | IMPORTANT :
            | - quantity = quantité disponible
            | - initial_quantity = quantité initiale historique
            |
            | La modification de la fiche produit ne doit modifier AUCUNE
            | de ces deux valeurs.
            |
            | Pour corriger le stock disponible, utiliser :
            | Ajustements inventaire.
            |
            | Pour ajouter un nouvel arrivage, utiliser :
            | Import / entrée de stock.
            |
            */

            'min_stock' =>
                $request->min_stock,

            'max_stock' =>
                $request->max_stock,

            'purchase_price' =>
                $purchasePrice,

            'coef_purchase' =>
                $coefPurchase,

            'cost_price' =>
                $costPrice,

            'coef_sale' =>
                $coefSale,

            'sale_price' =>
                $salePrice,
        ]);

        /*
        |--------------------------------------------------------------------------
        | REDIRECT
        |--------------------------------------------------------------------------
        */

        return redirect()
            ->route('products.index')
            ->with(
                'success',
                'Produit modifié avec succès.'
            );
    }
    /*
    |--------------------------------------------------------------------------
    | DELETE
    |--------------------------------------------------------------------------
    */

    public function destroy(Product $product)
    {
        $product->delete();

        return redirect()
            ->route('products.index')
            ->with(
                'success',
                'Produit supprimé avec succès.'
            );
    }

    /*
|--------------------------------------------------------------------------
| AJAX - AJOUT FAMILLE
|--------------------------------------------------------------------------
*/
public function storeFamilyOption(Request $request)
{
    $validated = $request->validate([
        'name' => [
            'required',
            'string',
            'max:255',
        ],
    ]);

    $name = trim(
        (string) $validated['name']
    );

    /*
    |--------------------------------------------------------------------------
    | VÉRIFIER SI LA FAMILLE EXISTE DÉJÀ
    |--------------------------------------------------------------------------
    */
    $family = FamilyModel::query()
        ->whereRaw(
            'LOWER(name) = ?',
            [
                mb_strtolower($name),
            ]
        )
        ->first();

    /*
    |--------------------------------------------------------------------------
    | CRÉATION SI ELLE N'EXISTE PAS
    |--------------------------------------------------------------------------
    */
    if (!$family) {

        $family = FamilyModel::create([
            'name' => $name,
        ]);
    }

    return response()->json([
        'success' => true,

        'message' =>
            'Famille enregistrée avec succès.',

        'item' => [
            'id' =>
                $family->id,

            'name' =>
                $family->name,
        ],
    ]);
}


/*
|--------------------------------------------------------------------------
| AJAX - AJOUT SOUS-FAMILLE
|--------------------------------------------------------------------------
*/
public function storeSubfamilyOption(Request $request)
{
    $validated = $request->validate([
        'family_id' => [
            'required',
            'exists:families,id',
        ],

        'name' => [
            'required',
            'string',
            'max:255',
        ],
    ]);

    $familyId =
        (int) $validated['family_id'];

    $name =
        trim(
            (string) $validated['name']
        );

    /*
    |--------------------------------------------------------------------------
    | VÉRIFIER SI LA SOUS-FAMILLE EXISTE DÉJÀ POUR CETTE FAMILLE
    |--------------------------------------------------------------------------
    */
    $subfamily = Subfamily::query()
        ->where(
            'family_id',
            $familyId
        )
        ->whereRaw(
            'LOWER(name) = ?',
            [
                mb_strtolower($name),
            ]
        )
        ->first();

    /*
    |--------------------------------------------------------------------------
    | CRÉATION
    |--------------------------------------------------------------------------
    */
    if (!$subfamily) {

        $subfamily = Subfamily::create([
            'family_id' =>
                $familyId,

            'name' =>
                $name,
        ]);
    }

    return response()->json([
        'success' => true,

        'message' =>
            'Sous-famille enregistrée avec succès.',

        'item' => [
            'id' =>
                $subfamily->id,

            'name' =>
                $subfamily->name,

            'family_id' =>
                $subfamily->family_id,
        ],
    ]);
}
}
