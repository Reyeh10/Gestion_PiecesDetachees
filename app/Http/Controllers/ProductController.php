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

            'file' =>
                'required|mimes:xlsx,xls,csv',

           'supplier_id' =>
                'required|exists:suppliers,id',

            'depot_id' =>
                'required|exists:depots,id',

        ]);

        /*
        |--------------------------------------------------------------------------
        | RECUPERATION FOURNISSEUR
        |--------------------------------------------------------------------------
        */

        $supplier = Supplier::findOrFail(
            $request->supplier_id
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

        foreach ($rows as $row) {

            /*
            |--------------------------------------------------------------------------
            | IGNORER LIGNES VIDES
            |--------------------------------------------------------------------------
            */

            if (

                empty($row[0]) &&
                empty($row[1]) &&
                empty($row[2]) &&
                empty($row[3])

            ) {

                continue;
            }

            $errors = [];

            /*
            |--------------------------------------------------------------------------
            | VALIDATIONS
            |--------------------------------------------------------------------------
            */

            if (empty($row[0])) {

                $errors[] = 'Référence manquante';
            }

            if (empty($row[1])) {

                $errors[] = 'Désignation manquante';
            }

            /*
            |--------------------------------------------------------------------------
            | CALCULS
            |--------------------------------------------------------------------------
            */

            $purchasePrice =
                (float) ($row[11] ?? 0);

            $coefPurchase =
                (float) ($row[12] ?? 1);

            $coefSale =
                (float) ($row[13] ?? 1);

            $costPrice =
                $purchasePrice * $coefPurchase;

            $salePrice =
                $costPrice * $coefSale;

            /*
            |--------------------------------------------------------------------------
            | DATA
            |--------------------------------------------------------------------------
            */

            $data[] = [

                'reference' =>
                    $row[0] ?? '',

                'designation' =>
                    $row[1] ?? '',

                'brand_name' =>
                    $row[2] ?? '',

                'model_name' =>
                    $row[3] ?? '',

                'family_name' =>
                    $row[4] ?? '',

                'subfamily_name' =>
                    $row[5] ?? '',

                'rayon_name' =>
                    $row[6] ?? '',

                'location_name' =>
                    $row[7] ?? '',

                /*
                |--------------------------------------------------------------------------
                | STOCK
                |--------------------------------------------------------------------------
                */

                'quantity' =>
                    (int) ($row[8] ?? 0),

                'min_stock' =>
                    (int) ($row[9] ?? 0),

                'max_stock' =>
                    (int) ($row[10] ?? 0),

                /*
                |--------------------------------------------------------------------------
                | PRIX
                |--------------------------------------------------------------------------
                */

                'purchase_price' =>
                    (float) ($row[11] ?? 0),

                'coef_purchase' =>
                    (float) ($row[12] ?? 1),

                'cost_price' =>
                    $costPrice,

                'coef_sale' =>
                    (float) ($row[13] ?? 1),

                'sale_price' =>
                    $salePrice,

                /*
                |--------------------------------------------------------------------------
                | UNITES
                |--------------------------------------------------------------------------
                */

                'unit_type' =>
                    strtolower($row[14] ?? 'piece'),

                'unit_label' =>
                    strtolower($row[14] ?? 'piece') == 'litre'
                        ? 'L'
                        : 'Pièce',
                /*
                |--------------------------------------------------------------------------
                | STATUS
                |--------------------------------------------------------------------------
                */

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

            $depot = Depot::findOrFail(
            $request->depot_id
        );

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
        | RECUPERATION DONNEES
        |--------------------------------------------------------------------------
        */
        //dd($request->all());

        //dd($request->products);
        $products = $request->products;



        /*
        |--------------------------------------------------------------------------
        | FOURNISSEUR SELECTIONNE
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
        | VALIDATION
        |--------------------------------------------------------------------------
        */

        if (!$products || count($products) == 0) {

            return redirect()
                ->back()
                ->with(
                    'error',
                    'Aucun produit à importer.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | VERIFICATION CHAMPS OBLIGATOIRES
        |--------------------------------------------------------------------------
        */

        foreach ($products as $index => $product) {

            $requiredFields = [
                'reference',
                'designation',
                'brand_name',
                'model_name',
                'family_name',
                'subfamily_name',
                'rayon_name',
                'location_name',
            ];

           /*8 foreach ($requiredFields as $field) {

                if (!array_key_exists($field, $product)) {

                    dd(
                        'Champ manquant',
                        $field,
                        'Produit index',
                        $index,
                        $product
                    );
                }
            }*/

            if (
                empty($product['reference']) ||
                empty($product['designation'])
            ) {

                return redirect()
                    ->route('products.index')
                    ->with(
                        'error',
                        'Impossible d’importer. Certains produits ont des champs obligatoires manquants.'
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
            | CREATION RELATIONS
            |--------------------------------------------------------------------------
            */

            $brand = Brand::firstOrCreate([

                'name' =>
                    $product['brand_name']

            ]);

            $model = CarModel::firstOrCreate([

                'name' =>
                    $product['model_name'],

                'brand_id' =>
                    $brand->id,

            ]);

            $family = FamilyModel::firstOrCreate([

                'name' =>
                    $product['family_name']

            ]);

            $subfamily = Subfamily::firstOrCreate([

                'name' =>
                    $product['subfamily_name'],

                'family_id' =>
                    $family->id,

            ]);


           /* $rayon = Rayon::firstOrCreate([

                'name' =>
                    $product['rayon_name']

            ]);*/

            $rayonName = $product['rayon_name'] ?? 'Non défini';

                $rayon = Rayon::firstOrCreate([
                    'name' => $rayonName,
                ]);

           /* $location = Location::firstOrCreate([

                'name' =>
                    $product['location_name']

            ]);*/

            $locationName = $product['location_name'] ?? 'Non défini';

                $location = Location::firstOrCreate([
                    'name' => $locationName ?: 'Non défini',
                    'rayon_id' => $rayon->id,
                ]);

            /*
            |--------------------------------------------------------------------------
            | CALCULS
            |--------------------------------------------------------------------------
            */

            $purchasePrice =
                (float) ($product['purchase_price'] ?? 0);

            $coefPurchase =
                (float) ($product['coef_purchase'] ?? 1);

            $coefSale =
                (float) ($product['coef_sale'] ?? 1);

            $costPrice =
                $purchasePrice * $coefPurchase;

            $salePrice =
                $costPrice * $coefSale;

            /*
            |--------------------------------------------------------------------------
            | CREATE PRODUCT
            |--------------------------------------------------------------------------
            */

            $createdProduct = Product::create([

                /*
                |--------------------------------------------------------------------------
                | INFORMATIONS
                |--------------------------------------------------------------------------
                */

                'reference' =>
                    $product['reference'] ?? '',

                'designation' =>
                    $product['designation'] ?? '',

                /*
                |--------------------------------------------------------------------------
                | UNITES
                |--------------------------------------------------------------------------
                */

                'unit_type' =>
                    $product['unit_type'] ?? 'piece',

                'unit_label' =>
                    $product['unit_label'] ?? 'Pièce',

                /*
                |--------------------------------------------------------------------------
                | RELATIONS
                |--------------------------------------------------------------------------
                */

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
                    (float) ($product['quantity'] ?? 0),

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
                */

                'status' =>
                    'disponible',
            ]);

            /*
            |--------------------------------------------------------------------------
            | STOCK DEPOT
            |--------------------------------------------------------------------------
            */

            ProductDepotStock::create([

                'product_id' =>
                    $createdProduct->id,

                'depot_id' =>
                    $depot->id,

                'quantity' =>
                    (float) ($product['quantity'] ?? 0),

            ]);

            /*
            |--------------------------------------------------------------------------
            | STOCK MOVEMENT
            |--------------------------------------------------------------------------
            */

            StockMovement::create([

                'product_id' =>
                    $createdProduct->id,

                'type' =>
                    'in',

                'quantity' =>
                    (int) ($product['quantity'] ?? 0),

                'source' =>
                    'Import Excel',

                'reference' =>
                    $createdProduct->reference,

                'user_id' =>
                    auth()->id(),
            ]);

            /*
            |--------------------------------------------------------------------------
            | LINK PRODUCT -> SUPPLIER
            |--------------------------------------------------------------------------
            */

            $createdProduct->suppliers()
                ->syncWithoutDetaching([

                    $supplier->id => [

                        /*
                        |--------------------------------------------------------------------------
                        | INFORMATIONS FOURNISSEUR
                        |--------------------------------------------------------------------------
                        */

                        'supplier_reference' =>
                            $createdProduct->reference,

                        'purchase_price' =>
                            $purchasePrice,

                        'delivery_delay' =>
                            3,

                        /*
                        |--------------------------------------------------------------------------
                        | STATUS
                        |--------------------------------------------------------------------------
                        */

                        'is_primary' =>
                            true,

                        'active' =>
                            true,
                    ]
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
        | PRODUITS DISPONIBLES
        |--------------------------------------------------------------------------
        */

        $products = $query

            ->where('status', '!=', 'vendu')

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
                'Produits disponibles',

            'hideButtons' =>
                false,

        ]);
    }

    /*
|--------------------------------------------------------------------------
| PRODUITS VENDUS
|--------------------------------------------------------------------------
*/

/*
|--------------------------------------------------------------------------
| PRODUITS VENDUS
|--------------------------------------------------------------------------
*/

    public function sold(Request $request)
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

        ])->withSum([
            'saleItems as sold_quantity' => function ($query) {
                $query->whereHas('sale', function ($q) {
                    $q->whereNotIn(
                        'status',
                        ['cancelled']
                    );
                });
            }
        ], 'quantity') ->having('sold_quantity', '>', 0);

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
        | DATA
        |--------------------------------------------------------------------------
        */

        $products = $query
            ->latest()
            ->paginate(10);

        $suppliers = Supplier::orderBy('name')->get();

        $depots = Depot::where('is_active', true)
            ->orderBy('name')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | VIEW
        |--------------------------------------------------------------------------
        */

        return view('products.index', [

            'products' => $products,

            'suppliers' => $suppliers,

            'depots' => $depots,

            'pageTitle' => 'Produits vendus',

            'hideButtons' => true,

        ]);
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

    ]);

    /*
    |--------------------------------------------------------------------------
    | SOLD QUANTITY
    |--------------------------------------------------------------------------
    */

    $soldQuantity = $product->stockMovements()
        ->where('type', 'out')
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

    return view(
        'products.create',
        compact(
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
| Store
|--------------------------------------------------------------------------
*/
public function store(Request $request)
{
    $request->validate([
        'reference' => 'required|string|max:255|unique:products,reference',
        'designation' => 'required|string|max:255',

        'brand_id' => 'nullable|exists:brands,id',
        'model_id' => 'nullable|exists:models,id',

        'family_id' => 'nullable|exists:families,id',
        'subfamily_id' => 'nullable|exists:subfamilies,id',

        'rayon_id' => 'nullable|exists:rayons,id',
        'location_id' => 'nullable|exists:locations,id',

        'quantity' => 'required|numeric|min:0',

        'min_stock' => 'nullable|numeric|min:0',
        'max_stock' => 'nullable|numeric|min:0',

        'purchase_price' => 'required|numeric|min:0',
        'coef_purchase' => 'nullable|numeric|min:0',

        'cost_price' => 'nullable|numeric|min:0',

        'coef_sale' => 'nullable|numeric|min:0',
        'sale_price' => 'required|numeric|min:0',

        'status' => 'nullable|string',

        'unit_type' => 'nullable|string',
        'unit_label' => 'nullable|string|max:50',
    ]);

    Product::create([

        'reference' => $request->reference,
        'designation' => $request->designation,

        'brand_id' => $request->brand_id,
        'model_id' => $request->model_id,

        'family_id' => $request->family_id,
        'subfamily_id' => $request->subfamily_id,

        'rayon_id' => $request->rayon_id,
        'location_id' => $request->location_id,

        'quantity' => $request->quantity,

        'min_stock' => $request->min_stock ?? 0,
        'max_stock' => $request->max_stock ?? 0,

        'purchase_price' => $request->purchase_price,
        'coef_purchase' => $request->coef_purchase ?? 1,

        'cost_price' => $request->cost_price,

        'coef_sale' => $request->coef_sale ?? 1,
        'sale_price' => $request->sale_price,

        'status' => $request->status ?? 'disponible',

        'unit_type' => $request->unit_type ?? 'piece',
        'unit_label' => $request->unit_label ?? 'Pièce',
    ]);

    return redirect()
        ->route('products.index')
        ->with(
            'success',
            'Produit créé avec succès.'
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
                'required|string|max:255',

            'designation' =>
                'required|string|max:255',

            'brand_id' =>
                'nullable|exists:brands,id',

            'model_id' =>
                'nullable|exists:models,id',

            'family_id' =>
                'nullable|exists:families,id',

            'subfamily_id' =>
                'nullable|exists:subfamilies,id',

            'rayon_id' =>
                'nullable|exists:rayons,id',

            'location_id' =>
                'nullable|exists:locations,id',

            'quantity' =>
                'nullable|numeric|min:0',

            'min_stock' =>
                'nullable|numeric|min:0',

            'max_stock' =>
                'nullable|numeric|min:0',

            'purchase_price' =>
                'nullable|numeric|min:0',

            'coef_purchase' =>
                'nullable|numeric|min:0',

            'coef_sale' =>
                'nullable|numeric|min:0',
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

            'quantity' =>
                $request->quantity,

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
}
