<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| CONTROLLERS
|--------------------------------------------------------------------------
*/

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductOptionController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DepotController;
use App\Http\Controllers\InventoryAdjustmentController;
use App\Http\Controllers\StockMovementController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\ProformaController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DepotTransferController;
use App\Http\Controllers\VehiclePartRequestController;
use App\Http\Controllers\VehicleHistoryController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\FournisseurCommandeController;


//use Illuminate\Http\Request;
//use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| AUTH
|--------------------------------------------------------------------------
*/

Route::get('/', function () {

    return view('auth.login');

});

/*Route::post('/login', function (Request $request) {

    $credentials = $request->validate([
        'email'    => ['required', 'email'],
        'password' => ['required'],
    ]);

    if (Auth::attempt($credentials)) {

        $request->session()->regenerate();

        return redirect()->route('dashboard');
    }

    return back()->withErrors([
        'email' => 'Email ou mot de passe incorrect.',
    ])->onlyInput('email');

})->name('login');*/

/*
|--------------------------------------------------------------------------
| change Password
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    Route::get(

        '/change-password',

        [UserController::class, 'changePasswordForm']

    )->name('password.change.form');

    Route::post(

        '/change-password',

        [UserController::class, 'changePassword']

    )->name('password.change');

});

/*
|--------------------------------------------------------------------------
| DASHBOARD
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {

    Route::get('/dashboard', function () {

        $role = auth()->user()->role;

        /*
        |--------------------------------------------------------------------------
        | ADMIN + CHEF MAGASINIER
        |--------------------------------------------------------------------------
        */

        if (in_array($role, [

            'admin',
            'chef_magasinier'

        ])) {

            return app(
                DashboardController::class
            )->index();
        }

        /*
        |--------------------------------------------------------------------------
        | EMPLOYES
        |--------------------------------------------------------------------------
        */

        if (in_array($role, [

            'magasinier',
            'vendeur',
            'caissier'

        ])) {

           return app(
                DashboardController::class
            )->index();
        }

        abort(403);

    })->name('dashboard');

});

/*
|--------------------------------------------------------------------------
| PROFILE
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {

    Route::get(
        '/profile',
        [UserController::class, 'profile']
    )->name('profile.edit');

    Route::put(
        '/profile',
        [UserController::class, 'updateProfile']
    )->name('profile.update');

});

/*
|--------------------------------------------------------------------------
| USERS
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth',
    'role:admin,chef_magasinier'
])->group(function () {

    Route::resource(
        'users',
        UserController::class
    );

});

/*
|--------------------------------------------------------------------------
| PRODUITS
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth',
    'role:admin,chef_magasinier,magasinier,vendeur,caissier'
])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | PIÈCES À COMMANDER
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/products/to-order',
        [ProductController::class, 'toOrder']
    )->name('products.to-order');


    /*
    |--------------------------------------------------------------------------
    | PRODUITS DISPONIBLES
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/products/available',
        [ProductController::class, 'available']
    )->name('products.available');


    /*
    |--------------------------------------------------------------------------
    | PRODUITS VENDUS
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/products/sold',
        [ProductController::class, 'sold']
    )->name('products.sold');


    /*
    |--------------------------------------------------------------------------
    | PRODUITS NON DISPONIBLES
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/products/unavailable',
        [ProductController::class, 'unavailable']
    )->name('products.unavailable');


    /*
    |--------------------------------------------------------------------------
    | IMPORT PRODUITS
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/products/import',
        [ProductController::class, 'import']
    )->name('products.import');


    Route::post(
        '/products/preview',
        [ProductController::class, 'preview']
    )->name('products.preview');


    Route::post(
        '/products/store-import',
        [ProductController::class, 'storeImport']
    )->name('products.import.store');


    /*
    |--------------------------------------------------------------------------
    | EXPORT EXCEL
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/products/export/excel',
        [ProductController::class, 'exportExcel']
    )->name('products.export.excel');


    /*
    |--------------------------------------------------------------------------
    | AJOUT RAPIDE DES OPTIONS PRODUIT
    |--------------------------------------------------------------------------
    |
    | Ces routes sont utilisées dans products/_form.blade.php.
    |
    | Elles permettent d'ajouter directement depuis le formulaire :
    |
    | - une marque
    | - un modèle
    | - un rayon
    | - un emplacement
    |
    | sans quitter la création/modification du produit.
    |
    */


    /*
    |--------------------------------------------------------------------------
    | AJOUTER UNE MARQUE
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/product-options/brands',
        [ProductOptionController::class, 'storeBrand']
    )->name('product-options.brands.store');


    /*
    |--------------------------------------------------------------------------
    | AJOUTER UN MODÈLE
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/product-options/models',
        [ProductOptionController::class, 'storeModel']
    )->name('product-options.models.store');


    /*
    |--------------------------------------------------------------------------
    | AJOUTER UN RAYON
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/product-options/rayons',
        [ProductOptionController::class, 'storeRayon']
    )->name('product-options.rayons.store');


    /*
    |--------------------------------------------------------------------------
    | AJOUTER UN EMPLACEMENT
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/product-options/locations',
        [ProductOptionController::class, 'storeLocation']
    )->name('product-options.locations.store');


    /*
    |--------------------------------------------------------------------------
    | CRUD PRODUITS
    |--------------------------------------------------------------------------
    |
    | IMPORTANT :
    |
    | Les routes particulières sont volontairement déclarées AVANT
    | Route::resource('products', ...).
    |
    */

    Route::resource(
        'products',
        ProductController::class
    )->except([
        'destroy'
    ]);


    /*
    |--------------------------------------------------------------------------
    | SUPPRESSION PRODUIT
    |--------------------------------------------------------------------------
    |
    | Seuls admin et chef_magasinier peuvent supprimer.
    |
    */

    Route::delete(
        '/products/{product}',
        [ProductController::class, 'destroy']
    )
    ->middleware(
        'role:admin,chef_magasinier'
    )
    ->name('products.destroy');

});
/*
|--------------------------------------------------------------------------
| CATEGORIES
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth',
    'role:admin,chef_magasinier,magasinier,vendeur,caissier'
])->group(function () {

    Route::resource(
        'categories',
        CategoryController::class
    )->except([
        'destroy'
    ]);

    Route::get(
    '/categories',
    [CategoryController::class, 'index']
    )->name('categories.index');

    Route::delete(
        '/categories/{category}',
        [CategoryController::class, 'destroy']
    )->middleware(
        'role:admin,chef_magasinier'
    )->name('categories.destroy');

});

/*
|--------------------------------------------------------------------------
| FAMILIES
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth',
    'role:admin,chef_magasinier,magasinier,vendeur,caissier'
])->group(function () {

     /*
    |--------------------------------------------------------------------------
    | CREATE
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/families/create',
        [CategoryController::class, 'create']
    )->name('families.create');

    /*
    |--------------------------------------------------------------------------
    | STORE
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/families',
        [CategoryController::class, 'store']
    )->name('families.store');

    /*
    |--------------------------------------------------------------------------
    | SHOW
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/families/{family}',
        [CategoryController::class, 'show']
    )->name('families.show');

    /*
    |--------------------------------------------------------------------------
    | EDIT
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/families/{family}/edit',
        [CategoryController::class, 'edit']
    )->name('families.edit');

    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */

    Route::put(
        '/families/{family}',
        [CategoryController::class, 'update']
    )->name('families.update');

    /*
    |--------------------------------------------------------------------------
    | DELETE
    |--------------------------------------------------------------------------
    */

    Route::delete(
        '/families/{family}',
        [CategoryController::class, 'destroy']
    )->name('families.destroy');

});

/*
|--------------------------------------------------------------------------
| FOURNISSEURS
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth',
    'role:admin,chef_magasinier,magasinier,vendeur,caissier'
])->group(function () {

    Route::resource(
        'suppliers',
        SupplierController::class
    )->except([
        'destroy'
    ]);

    Route::delete(
        '/suppliers/{supplier}',
        [SupplierController::class, 'destroy']
    )->middleware(
        'role:admin,chef_magasinier'
    )->name('suppliers.destroy');

});

/*
|--------------------------------------------------------------------------
| CLIENTS
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth',
    'role:admin,chef_magasinier,magasinier,vendeur,caissier'
])->group(function () {

    Route::resource(
        'customers',
        CustomerController::class
    )->except([
        'destroy'
    ]);

    Route::delete(
        '/customers/{customer}',
        [CustomerController::class, 'destroy']
    )->middleware(
        'role:admin,chef_magasinier'
    )->name('customers.destroy');

});

/*
|--------------------------------------------------------------------------
| DEPOTS
|--------------------------------------------------------------------------
*/

/*
|--------------------------------------------------------------------------
| ADMIN + CHEF MAGASINIER
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth',
    'role:admin,chef_magasinier'
])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | CREATE
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/depots/create',
        [DepotController::class, 'create']
    )->name('depots.create');

    /*
    |--------------------------------------------------------------------------
    | STORE
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/depots',
        [DepotController::class, 'store']
    )->name('depots.store');

    /*
    |--------------------------------------------------------------------------
    | EDIT
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/depots/{depot}/edit',
        [DepotController::class, 'edit']
    )->name('depots.edit');

    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */

    Route::put(
        '/depots/{depot}',
        [DepotController::class, 'update']
    )->name('depots.update');

    /*
    |--------------------------------------------------------------------------
    | DELETE
    |--------------------------------------------------------------------------
    */

    Route::delete(
        '/depots/{depot}',
        [DepotController::class, 'destroy']
    )->name('depots.destroy');


});

/*
|--------------------------------------------------------------------------
| TOUS PEUVENT VOIR
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth',
    'role:admin,chef_magasinier,magasinier,vendeur,caissier'
])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | INDEX
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/depots',
        [DepotController::class, 'index']
    )->name('depots.index');

    /*
    |--------------------------------------------------------------------------
    | SHOW
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/depots/{depot}',
        [DepotController::class, 'show']
    )->name('depots.show');

});

/*
|--------------------------------------------------------------------------
| TRANSFERTS ENTRE DÉPÔTS
|--------------------------------------------------------------------------
|
| Règles :
|
| - Tous les utilisateurs autorisés peuvent consulter les transferts.
| - Admin et chef magasinier peuvent créer un transfert.
| - Le formulaire peut transférer plusieurs produits.
| - Une route AJAX permet de récupérer le stock disponible
|   d'un produit dans un dépôt source.
| - Admin et chef magasinier peuvent modifier/supprimer
|   un transfert si ces méthodes existent dans le contrôleur.
|
*/


/*
|--------------------------------------------------------------------------
| CONSULTATION DES TRANSFERTS
|--------------------------------------------------------------------------
|
| Accessible à :
| - admin
| - chef_magasinier
| - magasinier
| - vendeur
| - caissier
|
*/

Route::middleware([
    'auth',
    'role:admin,chef_magasinier,magasinier,vendeur,caissier'
])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | LISTE DES TRANSFERTS
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/depot-transfers',
        [DepotTransferController::class, 'index']
    )->name('depot-transfers.index');


    /*
    |--------------------------------------------------------------------------
    | STOCK DISPONIBLE D'UN PRODUIT DANS UN DÉPÔT
    |--------------------------------------------------------------------------
    |
    | IMPORTANT :
    |
    | Cette route doit impérativement être placée AVANT :
    |
    | /depot-transfers/{depotTransfer}
    |
    | Sinon Laravel peut considérer "stock" comme l'identifiant
    | d'un transfert.
    |
    | Exemple :
    |
    | /depot-transfers/stock/1/25
    |
    | 1  = ID du dépôt
    | 25 = ID du produit
    |
    */

    Route::get(
        '/depot-transfers/stock/{depot}/{product}',
        [DepotTransferController::class, 'getAvailableStock']
    )->name('depot-transfers.stock');


    /*
    |--------------------------------------------------------------------------
    | AFFICHER UN TRANSFERT
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/depot-transfers/{depotTransfer}',
        [DepotTransferController::class, 'show']
    )
    ->whereNumber('depotTransfer')
    ->name('depot-transfers.show');

});


/*
|--------------------------------------------------------------------------
| CRÉATION / MODIFICATION DES TRANSFERTS
|--------------------------------------------------------------------------
|
| Accessible uniquement à :
| - admin
| - chef_magasinier
|
*/

Route::middleware([
    'auth',
    'role:admin,chef_magasinier'
])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | FORMULAIRE DE CRÉATION
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/depot-transfers/create',
        [DepotTransferController::class, 'create']
    )->name('depot-transfers.create');


    /*
    |--------------------------------------------------------------------------
    | ENREGISTRER UN TRANSFERT
    |--------------------------------------------------------------------------
    |
    | Le contrôleur recevra :
    |
    | source_depot_id
    | destination_depot_id
    | note
    |
    | items[0][product_id]
    | items[0][quantity]
    |
    | items[1][product_id]
    | items[1][quantity]
    |
    | etc.
    |
    */

    Route::post(
        '/depot-transfers',
        [DepotTransferController::class, 'store']
    )->name('depot-transfers.store');


    /*
    |--------------------------------------------------------------------------
    | MODIFIER UN TRANSFERT
    |--------------------------------------------------------------------------
    |
    | Gardez cette route uniquement si votre
    | DepotTransferController possède une méthode edit().
    |
    */

    Route::get(
        '/depot-transfers/{depotTransfer}/edit',
        [DepotTransferController::class, 'edit']
    )
    ->whereNumber('depotTransfer')
    ->name('depot-transfers.edit');


    /*
    |--------------------------------------------------------------------------
    | METTRE À JOUR UN TRANSFERT
    |--------------------------------------------------------------------------
    |
    | Gardez cette route uniquement si votre
    | DepotTransferController possède une méthode update().
    |
    */

    Route::put(
        '/depot-transfers/{depotTransfer}',
        [DepotTransferController::class, 'update']
    )
    ->whereNumber('depotTransfer')
    ->name('depot-transfers.update');


    /*
    |--------------------------------------------------------------------------
    | SUPPRIMER UN TRANSFERT
    |--------------------------------------------------------------------------
    |
    | Gardez cette route uniquement si votre
    | DepotTransferController possède une méthode destroy().
    |
    */

    Route::delete(
        '/depot-transfers/{depotTransfer}',
        [DepotTransferController::class, 'destroy']
    )
    ->whereNumber('depotTransfer')
    ->name('depot-transfers.destroy');

});
/*
|--------------------------------------------------------------------------
| AJUSTEMENTS INVENTAIRE
|--------------------------------------------------------------------------
*/

/*
|--------------------------------------------------------------------------
| ADMIN + CHEF MAGASINIER
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth',
    'role:admin,chef_magasinier'
])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | CREATE
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/inventory-adjustments/create',
        [InventoryAdjustmentController::class, 'create']
    )->name('inventory-adjustments.create');

    /*
    |--------------------------------------------------------------------------
    | STORE
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/inventory-adjustments',
        [InventoryAdjustmentController::class, 'store']
    )->name('inventory-adjustments.store');

    /*
    |--------------------------------------------------------------------------
    | EDIT
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/inventory-adjustments/{inventoryAdjustment}/edit',
        [InventoryAdjustmentController::class, 'edit']
    )->name('inventory-adjustments.edit');

    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */

    Route::put(
        '/inventory-adjustments/{inventoryAdjustment}',
        [InventoryAdjustmentController::class, 'update']
    )->name('inventory-adjustments.update');

    /*
    |--------------------------------------------------------------------------
    | DELETE
    |--------------------------------------------------------------------------
    */

    Route::delete(
        '/inventory-adjustments/{inventoryAdjustment}',
        [InventoryAdjustmentController::class, 'destroy']
    )->name('inventory-adjustments.destroy');

});

/*
|--------------------------------------------------------------------------
| TOUS PEUVENT VOIR
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth',
    'role:admin,chef_magasinier,magasinier,vendeur,caissier'
])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | INDEX
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/inventory-adjustments',
        [InventoryAdjustmentController::class, 'index']
    )->name('inventory-adjustments.index');

    /*
    |--------------------------------------------------------------------------
    | SHOW
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/inventory-adjustments/{inventoryAdjustment}',
        [InventoryAdjustmentController::class, 'show']
    )->name('inventory-adjustments.show');

});

/*
|--------------------------------------------------------------------------
| STOCK MOVEMENTS
|--------------------------------------------------------------------------
*/

/*
|--------------------------------------------------------------------------
| TOUS PEUVENT VOIR
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth',
    'role:admin,chef_magasinier,magasinier,vendeur,caissier'
])->group(function () {

    Route::get(
        '/stock-movements',
        [StockMovementController::class, 'index']
    )->name('stock-movements.index');

    Route::get(
        '/stock-movements/entries',
        [StockMovementController::class, 'entries']
    )->name('stock-movements.entries');

    Route::get(
        '/stock-movements/exits',
        [StockMovementController::class, 'exits']
    )->name('stock-movements.exits');

    Route::get(
        '/stock-movements/{stockMovement}',
        [StockMovementController::class, 'show']
    )->name('stock-movements.show');

});

/*
|--------------------------------------------------------------------------
| ADMIN + CHEF MAGASINIER
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth',
    'role:admin,chef_magasinier'
])->group(function () {

    Route::get(
        '/stock-movements/{stockMovement}/edit',
        [StockMovementController::class, 'edit']
    )->name('stock-movements.edit');

    Route::put(
        '/stock-movements/{stockMovement}',
        [StockMovementController::class, 'update']
    )->name('stock-movements.update');

    Route::delete(
        '/stock-movements/{stockMovement}',
        [StockMovementController::class, 'destroy']
    )->name('stock-movements.destroy');

});

Route::middleware(['auth'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Suivi des pièces liées aux véhicules
    |--------------------------------------------------------------------------
    */

    // Toutes les pièces à rechercher, trouvées, commandées, reçues, etc.
    Route::get(
        '/vehicle-part-requests',
        [VehiclePartRequestController::class, 'index']
    )->name('vehicle-part-requests.index');

    // Seulement les pièces commandées
    Route::get(
        '/vehicle-part-requests-ordered',
        [VehiclePartRequestController::class, 'ordered']
    )->name('vehicle-part-requests.ordered');

    // Seulement les pièces reçues
    Route::get(
        '/vehicle-part-requests-received',
        [VehiclePartRequestController::class, 'received']
    )->name('vehicle-part-requests.received');

    // Seulement les pièces non trouvées
    Route::get(
        '/vehicle-part-requests-not-found',
        [VehiclePartRequestController::class, 'notFound']
    )->name('vehicle-part-requests.not-found');

    // Modification du statut d'une pièce
    Route::patch(
        '/vehicle-part-requests/{vehiclePartRequest}/status',
        [VehiclePartRequestController::class, 'changeStatus']
    )->name('vehicle-part-requests.change-status');


    /*
    |--------------------------------------------------------------------------
    | MODIFIER LA QUANTITÉ REÇUE
    |--------------------------------------------------------------------------
    |
    | Cette route permet d'enregistrer une réception partielle ou complète
    | d'une pièce commandée.
    |
    | Exemple :
    |
    | Quantité commandée : 10
    | Quantité reçue      : 5
    | Reste à recevoir    : 5
    |
    */

    Route::patch(
        '/vehicle-part-requests/{vehiclePartRequest}/received-quantity',
        [
            VehiclePartRequestController::class,
            'updateReceivedQuantity'
        ]
    )->name(
        'vehicle-part-requests.update-received-quantity'
    );


    /*
    |--------------------------------------------------------------------------
    | Autres routes CRUD
    |--------------------------------------------------------------------------
    |
    | On exclut index parce que nous l'avons déclaré manuellement plus haut.
    |
    */

    Route::resource(
        'vehicle-part-requests',
        VehiclePartRequestController::class
    )->except(['index']);

});
/*
|--------------------------------------------------------------------------
| VÉHICULES
|--------------------------------------------------------------------------
*/

/*
|--------------------------------------------------------------------------
| VOIR, AJOUTER ET MODIFIER
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth',
    'role:admin,chef_magasinier,magasinier,vendeur,caissier'
])->group(function () {

    Route::get(
        '/vehicles',
        [VehicleController::class, 'index']
    )->name('vehicles.index');

    Route::get(
        '/vehicles/create',
        [VehicleController::class, 'create']
    )->name('vehicles.create');

    Route::post(
        '/vehicles',
        [VehicleController::class, 'store']
    )->name('vehicles.store');

    Route::get(
        '/vehicles/{vehicle}',
        [VehicleController::class, 'show']
    )->name('vehicles.show');

    Route::get(
        '/vehicles/{vehicle}/edit',
        [VehicleController::class, 'edit']
    )->name('vehicles.edit');

    Route::put(
        '/vehicles/{vehicle}',
        [VehicleController::class, 'update']
    )->name('vehicles.update');

    /*
    |--------------------------------------------------------------------------
    | HISTORIQUE PAR IMMATRICULATION
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/vehicle-history',
        [VehicleHistoryController::class, 'index']
    )->name('vehicles.history');

});

/*
|--------------------------------------------------------------------------
| SUPPRESSION : ADMIN UNIQUEMENT
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth',
    'role:admin'
])->group(function () {

    Route::delete(
        '/vehicles/{vehicle}',
        [VehicleController::class, 'destroy']
    )->name('vehicles.destroy');

});
/*
|--------------------------------------------------------------------------
| VENTES
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth',
    'role:admin,chef_magasinier,magasinier,vendeur,caissier'
])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | VÉHICULES ASSOCIÉS À UN CLIENT
    |--------------------------------------------------------------------------
    |
    | IMPORTANT :
    | cette route doit rester avant Route::resource('sales', ...)
    |
    */

    Route::get(
        '/sales/customers/{customer}/vehicles',
        [SaleController::class, 'vehiclesByCustomer']
    )->name('sales.customers.vehicles');


    /*
    |--------------------------------------------------------------------------
    | AFFICHER LA FACTURE
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/sales/{sale}/invoice',
        [SaleController::class, 'invoice']
    )
    ->whereNumber('sale')
    ->name('sales.invoice');


    /*
    |--------------------------------------------------------------------------
    | TÉLÉCHARGER LA FACTURE PDF
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/sales/{sale}/invoice/download',
        [SaleController::class, 'downloadInvoice']
    )
    ->whereNumber('sale')
    ->name('sales.invoice.download');

    /*
    |--------------------------------------------------------------------------
    | AJOUT D'UN PAIEMENT
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/sales/{sale}/payment',
        [SaleController::class, 'addPayment']
    )
    ->whereNumber('sale')
    ->name('sales.payment');


    /*
    |--------------------------------------------------------------------------
    | ANNULATION D'UNE VENTE
    |--------------------------------------------------------------------------
    */

    Route::put(
        '/sales/{sale}/cancel',
        [SaleController::class, 'cancel']
    )
    ->whereNumber('sale')
    ->name('sales.cancel');


    /*
    |--------------------------------------------------------------------------
    | CRUD DES VENTES
    |--------------------------------------------------------------------------
    |
    | IMPORTANT :
    | Les routes spécifiques ci-dessus sont placées avant la resource.
    |
    */

    Route::resource(
        'sales',
        SaleController::class
    )->except([
        'destroy'
    ]);


    /*
    |--------------------------------------------------------------------------
    | SUPPRESSION D'UNE VENTE
    |--------------------------------------------------------------------------
    |
    | Admin + chef magasinier uniquement.
    |
    */

    Route::delete(
        '/sales/{sale}',
        [SaleController::class, 'destroy']
    )
    ->whereNumber('sale')
    ->middleware(
        'role:admin,chef_magasinier'
    )
    ->name('sales.destroy');

});

/*
|--------------------------------------------------------------------------
| PROFORMAS
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth',
    'role:admin,chef_magasinier,magasinier,vendeur,caissier'
])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | VÉHICULES ASSOCIÉS À UN CLIENT
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/proformas/customers/{customer}/vehicles',
        [ProformaController::class, 'vehiclesByCustomer']
    )->name('proformas.customers.vehicles');


    /*
    |--------------------------------------------------------------------------
    | HISTORIQUE ASSOCIÉS À UN CLIENT
    |--------------------------------------------------------------------------
    */

    Route::get(
    '/customers-history',
    [CustomerController::class, 'history']
        )->name('customers.history');

        Route::resource('customers', CustomerController::class);

    /*
    |--------------------------------------------------------------------------
    | CRÉER UN PROFORMA À PARTIR D'UN VÉHICULE
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/proformas/create/vehicle/{vehicle}',
        [ProformaController::class, 'createWithVehicle']
    )
    ->whereNumber('vehicle')
    ->name('proformas.create.vehicle');


    /*
    |--------------------------------------------------------------------------
    | TÉLÉCHARGER LE PDF DU PROFORMA
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/proformas/{proforma}/pdf',
        [ProformaController::class, 'download']
    )
    ->whereNumber('proforma')
    ->name('proformas.pdf');


    /*
    |--------------------------------------------------------------------------
    | CONVERTIR LE PROFORMA EN VENTE
    |--------------------------------------------------------------------------
    |
    | Cette route crée la vente puis ProformaController doit rediriger vers :
    |
    | sales.invoice
    |
    | et NON sales.invoice.download.
    |
    */

    Route::post(
        '/proformas/{proforma}/convert-sale',
        [ProformaController::class, 'convertToSale']
    )
    ->whereNumber('proforma')
    ->name('proformas.convert-sale');


    /*
    |--------------------------------------------------------------------------
    | ANNULER LE PROFORMA
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/proformas/{proforma}/cancel',
        [ProformaController::class, 'cancel']
    )
    ->whereNumber('proforma')
    ->name('proformas.cancel');


    /*
    |--------------------------------------------------------------------------
    | CRUD PROFORMAS
    |--------------------------------------------------------------------------
    */

    Route::resource(
        'proformas',
        ProformaController::class
    )->only([
        'index',
        'create',
        'store',
        'show',
    ]);


    /*
    |--------------------------------------------------------------------------
    | SUPPRESSION
    |--------------------------------------------------------------------------
    */

    Route::delete(
        '/proformas/{proforma}',
        [ProformaController::class, 'destroy']
    )
    ->whereNumber('proforma')
    ->middleware(
        'role:admin,chef_magasinier'
    )
    ->name('proformas.destroy');

});


/*
|--------------------------------------------------------------------------
| COMMANDES REÇUES DEPUIS APP ATELIER
|--------------------------------------------------------------------------
|
| Ces routes affichent et traitent les bons de commande reçus depuis
| l'application App Atelier.
|
| - index        : liste des bons reçus
| - show         : détail d'un bon
| - updateLigne  : identification / disponibilité d'une ligne
| - creerVente   : conversion du bon en vente
|
*/

Route::middleware([
    'auth',
    'role:admin,chef_magasinier,magasinier,vendeur,caissier'
])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | LISTE DES BONS DE COMMANDE
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/fournisseur-commandes',
        [FournisseurCommandeController::class, 'index']
    )->name('fournisseur-commandes.index');


    /*
    |--------------------------------------------------------------------------
    | DÉTAIL D'UN BON DE COMMANDE
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/fournisseur-commandes/{fournisseurCommande}',
        [FournisseurCommandeController::class, 'show']
    )
    ->whereNumber('fournisseurCommande')
    ->name('fournisseur-commandes.show');


    /*
    |--------------------------------------------------------------------------
    | IDENTIFIER / METTRE À JOUR UNE LIGNE
    |--------------------------------------------------------------------------
    */

    Route::put(
        '/fournisseur-commandes/{fournisseurCommande}/lignes/{ligne}',
        [FournisseurCommandeController::class, 'updateLigne']
    )
    ->whereNumber('fournisseurCommande')
    ->whereNumber('ligne')
    ->name('fournisseur-commandes.lignes.update');


    /*
    |--------------------------------------------------------------------------
    | CONVERTIR LE BON DE COMMANDE EN VENTE
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/fournisseur-commandes/{fournisseurCommande}/creer-vente',
        [FournisseurCommandeController::class, 'creerVente']
    )
    ->whereNumber('fournisseurCommande')
    ->name('fournisseur-commandes.creer-vente');

});


/*
|--------------------------------------------------------------------------
| AUTHENTIFICATION
|--------------------------------------------------------------------------
|
| Les routes Login / Logout / Password Reset sont définies
| dans routes/auth.php.
|
| IMPORTANT :
| Ne pas redéclarer la route logout dans ce fichier.
|
*/

require __DIR__.'/auth.php';
