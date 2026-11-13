<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| CONTROLLERS
|--------------------------------------------------------------------------
*/

use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Auth;
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
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\DepotTransferController;
use App\Http\Controllers\VehiclePartRequestController;
use App\Http\Controllers\SupplierOrderController;
use App\Http\Controllers\VehicleHistoryController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\AvoirController;
use App\Http\Controllers\BonTransfertController;
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

/*
|--------------------------------------------------------------------------
| SESSION EXPIRÉE (10 min sans utilisation)
|--------------------------------------------------------------------------
|
| Appelée par la page quand le compte à rebours arrive à 0. Simple lien
| (GET) : pas de jeton CSRF, donc jamais d'erreur 419 « Page expirée »,
| même si la session a déjà été fermée par le serveur ou par un autre onglet.
|
*/
Route::get('/session-expiree', function (\Illuminate\Http\Request $request) {
    if (Auth::check()) {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }

    return redirect()->route('login')->with('timeout', "Votre session a expiré après 10 minutes d'inactivité. Veuillez vous reconnecter.");
})->name('session.expiree');

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

Route::middleware(['auth'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | NOTIFICATIONS
    |--------------------------------------------------------------------------
    */

    // « Je suis là » : la page signale l'activité (souris, clavier) au
    // serveur, au plus une fois par minute (déconnexion après 10 min).
    Route::post(
        '/session/ping',
        fn () => response()->noContent()
    )->name('session.ping');

    // Recherche globale de la barre du haut (design DreamsPOS)
    Route::get(
        '/recherche',
        [\App\Http\Controllers\RechercheController::class, 'index']
    )->name('recherche');

    // Nouvelles notifications (son + fenêtre), interrogée toutes les 20 s
    Route::get(
        '/notifications/nouvelles',
        [NotificationController::class, 'nouvelles']
    )->name('notifications.nouvelles');

    Route::patch(
        '/notifications/read-all',
        [
            NotificationController::class,
            'markAllAsRead',
        ]
    )->name('notifications.read-all');


    Route::delete(
        '/notifications/read',
        [
            NotificationController::class,
            'clearRead',
        ]
    )->name('notifications.clear-read');


    Route::get(
        '/notifications/{notification}/open',
        [
            NotificationController::class,
            'open',
        ]
    )
        ->whereUuid('notification')
        ->name('notifications.open');

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

    // « Mon profil » (menu de l'avatar) : nom, e-mail, mot de passe.
    // Avant : pointait vers UserController::profile, qui n'existait pas.
    Route::get(
        '/profile',
        [\App\Http\Controllers\ProfileController::class, 'edit']
    )->name('profile.edit');

    Route::put(
        '/profile',
        [\App\Http\Controllers\ProfileController::class, 'update']
    )->name('profile.update');

    Route::put(
        '/profile/mot-de-passe',
        [\App\Http\Controllers\ProfileController::class, 'updatePassword']
    )->name('profile.password');

});

/*
|--------------------------------------------------------------------------
| USERS
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth'
])->group(function () {

    Route::resource(
        'users',
        UserController::class
    );

    // Permissions personnalisées d'un utilisateur (comme l'app Atelier)
    Route::get(
        '/users/{user}/permissions',
        [PermissionController::class, 'edit']
    )->name('users.permissions.edit');

    Route::put(
        '/users/{user}/permissions',
        [PermissionController::class, 'update']
    )->name('users.permissions.update');

});

/*
|--------------------------------------------------------------------------
| PRODUITS
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth'
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

    Route::get(
        '/products/to-order/export',
        [ProductController::class, 'exportToOrderExcel']
    )->name('products.to-order.export');


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
    | EXPORT EXCEL DES PRODUITS VENDUS
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/products/sold/export',
        [ProductController::class, 'exportSoldExcel']
    )->name('products.sold.export');



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
    | AJOUTER UNE FAMILLE
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/product-options/families',
        [
            ProductController::class,
            'storeFamilyOption',
        ]
    )->name(
        'product-options.families.store'
    );


    /*
    |--------------------------------------------------------------------------
    | AJOUTER UNE SOUS-FAMILLE
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/product-options/subfamilies',
        [
            ProductController::class,
            'storeSubfamilyOption',
        ]
    )->name(
        'product-options.subfamilies.store'
    );



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

    ->name('products.destroy');

});
/*
|--------------------------------------------------------------------------
| CATEGORIES
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth'
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
    )->name('categories.destroy');

});

/*
|--------------------------------------------------------------------------
| FAMILIES
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth'
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
    'auth'
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
    )->name('suppliers.destroy');

});

/*
|--------------------------------------------------------------------------
| CLIENTS
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth'
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
    'auth'
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
    'auth'
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
    'auth'
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
    'auth'
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
|
| Règles :
|
| - Import Excel : admin + chef_magasinier
| - Création manuelle : admin + chef_magasinier
| - Validation / refus : chef magasinier + admin (V6)
| - Plus de modification ni de suppression : contre-ajustement (V6)
| - Consultation index/show : tous les rôles autorisés
|
| IMPORTANT :
| Toutes les routes fixes (/import, /create, etc.) sont déclarées
| avant les routes dynamiques /inventory-adjustments/{inventoryAdjustment}.
|
*/

/*
|--------------------------------------------------------------------------
| IMPORT + CRÉATION
| ADMIN + CHEF MAGASINIER
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth'
])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | IMPORT EXCEL - FORMULAIRE
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/inventory-adjustments/import',
        [InventoryAdjustmentController::class, 'importForm']
    )->name('inventory-adjustments.import');

    /*
    |--------------------------------------------------------------------------
    | IMPORT EXCEL - TÉLÉCHARGER LE MODÈLE
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/inventory-adjustments/import/template',
        [InventoryAdjustmentController::class, 'importTemplate']
    )->name('inventory-adjustments.import.template');

    /*
    |--------------------------------------------------------------------------
    | IMPORT EXCEL - PRÉVISUALISATION
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/inventory-adjustments/import/preview',
        [InventoryAdjustmentController::class, 'importPreview']
    )->name('inventory-adjustments.import.preview');

    /*
    |--------------------------------------------------------------------------
    | IMPORT EXCEL - ENREGISTREMENT
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/inventory-adjustments/import/store',
        [InventoryAdjustmentController::class, 'importStore']
    )->name('inventory-adjustments.import.store');

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

});

/*
|--------------------------------------------------------------------------
| V6 : VALIDATION / REFUS / ANNULATION / CORRECTION
|--------------------------------------------------------------------------
| Plus de modification ni de suppression d'un ajustement : une erreur se
| corrige par un contre-ajustement (« Corriger »).
*/

Route::middleware(['auth'])->group(function () {
    Route::post('/inventory-adjustments/valider-selection', [InventoryAdjustmentController::class, 'validerLot'])
        ->name('inventory-adjustments.valider-lot');
    Route::patch('/inventory-adjustments/{inventoryAdjustment}/valider', [InventoryAdjustmentController::class, 'valider'])
        ->whereNumber('inventoryAdjustment')->name('inventory-adjustments.valider');
    Route::patch('/inventory-adjustments/{inventoryAdjustment}/refuser', [InventoryAdjustmentController::class, 'refuser'])
        ->whereNumber('inventoryAdjustment')->name('inventory-adjustments.refuser');
    Route::patch('/inventory-adjustments/{inventoryAdjustment}/annuler', [InventoryAdjustmentController::class, 'annuler'])
        ->whereNumber('inventoryAdjustment')->name('inventory-adjustments.annuler');
    Route::post('/inventory-adjustments/{inventoryAdjustment}/corriger', [InventoryAdjustmentController::class, 'corriger'])
        ->whereNumber('inventoryAdjustment')->name('inventory-adjustments.corriger');
});

/*
|--------------------------------------------------------------------------
| CONSULTATION
| TOUS LES RÔLES AUTORISÉS
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth'
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
    )
    ->whereNumber('inventoryAdjustment')
    ->name('inventory-adjustments.show');

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
    'auth'
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
    'auth'
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

       /*
    |--------------------------------------------------------------------------
    | BONS DE COMMANDE FOURNISSEUR
    |--------------------------------------------------------------------------
    |
    | Ces routes sont placées AVANT les routes dynamiques afin d'éviter
    | tout conflit de résolution par Laravel.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | CRÉER UN BC DEPUIS UNE PIÈCE COMMANDÉE
    |--------------------------------------------------------------------------
    */
    Route::get(
        '/supplier-orders/create-from-part-request/{vehiclePartRequest}',
        [
            SupplierOrderController::class,
            'createFromPartRequest',
        ]
    )
        ->whereNumber('vehiclePartRequest')
        ->name('supplier-orders.create-from-part-request');


        /*
|--------------------------------------------------------------------------
| CRÉER UN BC DEPUIS PLUSIEURS PIÈCES COMMANDÉES
|--------------------------------------------------------------------------
|
| Cette route reçoit plusieurs vehicle_part_request_ids[].
|
| Elle ouvre le formulaire permettant de créer UN SEUL BC
| contenant plusieurs lignes.
|
|--------------------------------------------------------------------------
*/

Route::get(
    '/supplier-orders/create-from-part-requests',
    [
        SupplierOrderController::class,
        'createFromPartRequests',
    ]
)->name('supplier-orders.create-from-part-requests');

    /*
    |--------------------------------------------------------------------------
    | LISTE DES BONS DE COMMANDE
    |--------------------------------------------------------------------------
    */
    Route::get(
        '/supplier-orders',
        [
            SupplierOrderController::class,
            'index',
        ]
    )->name('supplier-orders.index');


    /*
    |--------------------------------------------------------------------------
    | ENREGISTRER UN BON DE COMMANDE
    |--------------------------------------------------------------------------
    */
    Route::post(
        '/supplier-orders',
        [
            SupplierOrderController::class,
            'store',
        ]
    )->name('supplier-orders.store');

    /*
    |--------------------------------------------------------------------------
    | MODIFIER UN BON DE COMMANDE (tant qu'il n'est pas approuvé)
    |--------------------------------------------------------------------------
    */
    Route::get(
        '/supplier-orders/{supplierOrder}/edit',
        [SupplierOrderController::class, 'edit']
    )->name('supplier-orders.edit');

    Route::put(
        '/supplier-orders/{supplierOrder}',
        [SupplierOrderController::class, 'update']
    )->name('supplier-orders.update');


    /*
    |--------------------------------------------------------------------------
    | APPROUVER UN BON DE COMMANDE
    |--------------------------------------------------------------------------
    */
    Route::patch(
        '/supplier-orders/{supplierOrder}/approve',
        [
            SupplierOrderController::class,
            'approve',
        ]
    )
        ->whereNumber('supplierOrder')
        ->name('supplier-orders.approve');


    /*
    |--------------------------------------------------------------------------
    | REJETER UN BON DE COMMANDE
    |--------------------------------------------------------------------------
    |
    | Le rejet est une décision administrative différente de l'annulation.
    | Le motif du rejet sera obligatoirement enregistré.
    |
    */

    Route::patch(
        '/supplier-orders/{supplierOrder}/reject',
        [
            SupplierOrderController::class,
            'reject',
        ]
    )
        ->whereNumber('supplierOrder')
        ->name('supplier-orders.reject');


    /*
    |--------------------------------------------------------------------------
    | MARQUER LE BC COMME ENVOYÉ AU FOURNISSEUR
    |--------------------------------------------------------------------------
    */
    Route::patch(
        '/supplier-orders/{supplierOrder}/mark-as-sent',
        [
            SupplierOrderController::class,
            'markAsSent',
        ]
    )
        ->whereNumber('supplierOrder')
        ->name('supplier-orders.mark-as-sent');


    /*
    |--------------------------------------------------------------------------
    | ANNULER UN BON DE COMMANDE
    |--------------------------------------------------------------------------
    */
    Route::patch(
        '/supplier-orders/{supplierOrder}/cancel',
        [
            SupplierOrderController::class,
            'cancel',
        ]
    )
        ->whereNumber('supplierOrder')
        ->name('supplier-orders.cancel');


            /*
    |--------------------------------------------------------------------------
    | AFFICHER UNE SIGNATURE ÉLECTRONIQUE DU BON DE COMMANDE
    |--------------------------------------------------------------------------
    |
    | Cette route permet d'afficher de manière sécurisée la signature
    | électronique enregistrée dans le stockage privé.
    |
    | IMPORTANT :
    | Cette route doit rester AVANT :
    |
    | /supplier-orders/{supplierOrder}
    |
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/supplier-orders/{supplierOrder}/signatures/{type}',
        [
            SupplierOrderController::class,
            'showSignature',
        ]
    )
        ->whereNumber('supplierOrder')
        ->whereIn(
            'type',
            [
                'prepared',
                'validated', // V7 : signature du chef magasinier
                'approved',
            ]
        )
        ->name('supplier-orders.signature');
    /*
    |--------------------------------------------------------------------------
    | AFFICHER UN BON DE COMMANDE
    |--------------------------------------------------------------------------
    |
    | IMPORTANT :
    | Cette route dynamique reste APRÈS toutes les routes spécifiques.
    |
    */
    Route::get(
        '/supplier-orders/{supplierOrder}',
        [
            SupplierOrderController::class,
            'show',
        ]
    )
        ->whereNumber('supplierOrder')
        ->name('supplier-orders.show');
    /*
    |--------------------------------------------------------------------------
    | IMPORT EXCEL DES DEMANDES DE PIÈCES
    |--------------------------------------------------------------------------
    |
    | Importe une liste de pièces depuis un fichier Excel.
    | Cette route doit rester AVANT Route::resource().
    |
    */

    Route::post(
        '/vehicle-part-requests/import-excel',
        [
            VehiclePartRequestController::class,
            'importExcel'
        ]
    )->name(
        'vehicle-part-requests.import-excel'
    );


    /*
    |--------------------------------------------------------------------------
    | TÉLÉCHARGER LE MODÈLE EXCEL
    |--------------------------------------------------------------------------
    |
    | Permet de télécharger le fichier modèle utilisé pour l'import
    | des demandes de pièces.
    |
    */

    Route::get(
        '/vehicle-part-requests/import-template',
        [
            VehiclePartRequestController::class,
            'downloadImportTemplate'
        ]
    )->name(
        'vehicle-part-requests.import-template'
    );


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
    'auth'
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
    'auth'
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
    'auth'
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

    // V6 : fiche de suivi de règlement (Excel) — récolte de la TVA
    Route::get(
        '/sales/export',
        [SaleController::class, 'exporter']
    )->name('sales.export');

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
    | AVOIRS (annulation des factures, comme l'app Atelier)
    |--------------------------------------------------------------------------
    */

    // V6 : bons de livraison (facultatifs, après la facture ; BL partiels possibles)
    Route::get('/bons-livraison', [\App\Http\Controllers\BonLivraisonController::class, 'index'])->name('bons-livraison.index');
    Route::get('/sales/{sale}/bons-livraison/create', [\App\Http\Controllers\BonLivraisonController::class, 'create'])
        ->whereNumber('sale')->name('bons-livraison.create');
    Route::post('/sales/{sale}/bons-livraison', [\App\Http\Controllers\BonLivraisonController::class, 'store'])
        ->whereNumber('sale')->name('bons-livraison.store');
    Route::get('/bons-livraison/{bonLivraison}', [\App\Http\Controllers\BonLivraisonController::class, 'show'])
        ->whereNumber('bonLivraison')->name('bons-livraison.show');
    Route::get('/bons-livraison/{bonLivraison}/pdf', [\App\Http\Controllers\BonLivraisonController::class, 'pdf'])
        ->whereNumber('bonLivraison')->name('bons-livraison.pdf');
    Route::get('/bons-livraison/{bonLivraison}/imprimer', [\App\Http\Controllers\BonLivraisonController::class, 'imprimer'])
        ->whereNumber('bonLivraison')->name('bons-livraison.imprimer');

    // V6 : bus leasing / bus du transport public — sorties de pièces faites au Magasin
    Route::get('/bus', [\App\Http\Controllers\BusController::class, 'index'])->name('bus.index');
    Route::post('/bus/synchroniser', [\App\Http\Controllers\BusController::class, 'synchroniser'])->name('bus.synchroniser');
    Route::get('/bus/sorties/create', [\App\Http\Controllers\BusController::class, 'create'])->name('bus.create');
    Route::post('/bus/sorties', [\App\Http\Controllers\BusController::class, 'store'])->name('bus.store');
    Route::get('/bus/sorties/{bonTransfert}/edit', [\App\Http\Controllers\BusController::class, 'edit'])
        ->whereNumber('bonTransfert')->name('bus.edit');
    Route::put('/bus/sorties/{bonTransfert}', [\App\Http\Controllers\BusController::class, 'update'])
        ->whereNumber('bonTransfert')->name('bus.update');
    Route::delete('/bus/sorties/{bonTransfert}', [\App\Http\Controllers\BusController::class, 'destroy'])
        ->whereNumber('bonTransfert')->name('bus.destroy');
    Route::get('/bus/import/modele', [\App\Http\Controllers\BusController::class, 'modeleExcel'])->name('bus.import.modele');
    Route::post('/bus/import/apercu', [\App\Http\Controllers\BusController::class, 'apercuImport'])->name('bus.import.apercu');
    Route::post('/bus/import', [\App\Http\Controllers\BusController::class, 'importer'])->name('bus.import.store');

    // V6 : « + BT » — BT direct pour tout usage interne (consommation atelier, intersociété, équipe commerciale…)
    Route::get('/bons-transfert/direct/create', [\App\Http\Controllers\BtDirectController::class, 'create'])->name('bt-direct.create');
    Route::post('/bons-transfert/direct', [\App\Http\Controllers\BtDirectController::class, 'store'])->name('bt-direct.store');
    Route::post('/bons-transfert/direct/equipements', [\App\Http\Controllers\BtDirectController::class, 'synchroniserEquipements'])->name('bt-direct.equipements');
    Route::get('/bons-transfert/direct/{bonTransfert}/edit', [\App\Http\Controllers\BtDirectController::class, 'edit'])
        ->whereNumber('bonTransfert')->name('bt-direct.edit');
    Route::put('/bons-transfert/direct/{bonTransfert}', [\App\Http\Controllers\BtDirectController::class, 'update'])
        ->whereNumber('bonTransfert')->name('bt-direct.update');
    Route::delete('/bons-transfert/direct/{bonTransfert}', [\App\Http\Controllers\BtDirectController::class, 'destroy'])
        ->whereNumber('bonTransfert')->name('bt-direct.destroy');

    // V7 : demandes de pièces (plusieurs pièces ; le BC de chaque fournisseur se crée tout seul)
    Route::get('/demandes', [\App\Http\Controllers\DemandeController::class, 'index'])->name('demandes.index');
    Route::get('/demandes/create', [\App\Http\Controllers\DemandeController::class, 'create'])->name('demandes.create');
    Route::post('/demandes', [\App\Http\Controllers\DemandeController::class, 'store'])->name('demandes.store');
    Route::get('/demandes/{demande}', [\App\Http\Controllers\DemandeController::class, 'show'])
        ->whereNumber('demande')->name('demandes.show');
    Route::post('/demandes/{demande}/pieces', [\App\Http\Controllers\DemandeController::class, 'ajouterPieces'])
        ->whereNumber('demande')->name('demandes.pieces.store');
    Route::patch('/demandes/{demande}/depot', [\App\Http\Controllers\DemandeController::class, 'depot'])
        ->whereNumber('demande')->name('demandes.depot');
    Route::patch('/demandes/pieces/{piece}/fournisseur', [\App\Http\Controllers\DemandeController::class, 'fournisseur'])
        ->whereNumber('piece')->name('demandes.pieces.fournisseur');

    // V7 : bon de commande — validation du chef magasinier, réception, solde
    Route::patch('/supplier-orders/{supplierOrder}/validate', [SupplierOrderController::class, 'validateOrder'])
        ->whereNumber('supplierOrder')->name('supplier-orders.validate');
    Route::post('/supplier-orders/{supplierOrder}/receive', [SupplierOrderController::class, 'receive'])
        ->whereNumber('supplierOrder')->name('supplier-orders.receive');
    Route::post('/supplier-orders/{supplierOrder}/close', [SupplierOrderController::class, 'close'])
        ->whereNumber('supplierOrder')->name('supplier-orders.close');

    // V6 : fiches d'inventaire (imprimer un rayon, compter, saisir / importer, valider les écarts)
    Route::get('/inventaires', [\App\Http\Controllers\InventaireController::class, 'index'])->name('inventaires.index');
    Route::get('/inventaires/create', [\App\Http\Controllers\InventaireController::class, 'create'])->name('inventaires.create');
    Route::post('/inventaires', [\App\Http\Controllers\InventaireController::class, 'store'])->name('inventaires.store');
    Route::get('/inventaires/{inventaire}', [\App\Http\Controllers\InventaireController::class, 'show'])
        ->whereNumber('inventaire')->name('inventaires.show');
    Route::get('/inventaires/{inventaire}/pdf', [\App\Http\Controllers\InventaireController::class, 'pdf'])
        ->whereNumber('inventaire')->name('inventaires.pdf');
    Route::get('/inventaires/{inventaire}/imprimer', [\App\Http\Controllers\InventaireController::class, 'imprimer'])
        ->whereNumber('inventaire')->name('inventaires.imprimer');
    Route::get('/inventaires/{inventaire}/export', [\App\Http\Controllers\InventaireController::class, 'exportExcel'])
        ->whereNumber('inventaire')->name('inventaires.export');
    Route::post('/inventaires/{inventaire}/saisir', [\App\Http\Controllers\InventaireController::class, 'saisir'])
        ->whereNumber('inventaire')->name('inventaires.saisir');
    Route::post('/inventaires/{inventaire}/ajouter', [\App\Http\Controllers\InventaireController::class, 'ajouterLigne'])
        ->whereNumber('inventaire')->name('inventaires.ajouter');
    Route::post('/inventaires/{inventaire}/import', [\App\Http\Controllers\InventaireController::class, 'importExcel'])
        ->whereNumber('inventaire')->name('inventaires.import');
    Route::post('/inventaires/{inventaire}/valider', [\App\Http\Controllers\InventaireController::class, 'valider'])
        ->whereNumber('inventaire')->name('inventaires.valider');

    Route::get('/avoirs', [AvoirController::class, 'index'])->name('avoirs.index');

    Route::get('/avoirs/{avoir}', [AvoirController::class, 'show'])
        ->whereNumber('avoir')
        ->name('avoirs.show');

    Route::get('/avoirs/{avoir}/pdf', [AvoirController::class, 'pdf'])
        ->whereNumber('avoir')
        ->name('avoirs.pdf');

    Route::get('/avoirs/{avoir}/imprimer', [AvoirController::class, 'imprimer'])
        ->whereNumber('avoir')
        ->name('avoirs.imprimer');

    Route::patch('/avoirs/{avoir}/rembourser', [AvoirController::class, 'rembourser'])
        ->whereNumber('avoir')
        ->name('avoirs.rembourser');


    // Une facture ne se supprime plus : elle s'annule par un avoir
    // (sales.cancel → AvoirController).

});

/*
|--------------------------------------------------------------------------
| PROFORMAS
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth'
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
| - creerBonTransfert : création du bon de transfert (BT)
|
*/

Route::middleware([
    'auth'
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

    Route::put(
        '/fournisseur-commandes/{fournisseurCommande}/lignes',
        [FournisseurCommandeController::class, 'updateLignes']
    )
    ->whereNumber('fournisseurCommande')
    ->name('fournisseur-commandes.lignes.update-all');


    /*
    |--------------------------------------------------------------------------
    | CRÉER LE BON DE TRANSFERT (BT) DU BON DE COMMANDE
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/fournisseur-commandes/{fournisseurCommande}/creer-bon-transfert',
        [FournisseurCommandeController::class, 'creerBonTransfert']
    )
    ->whereNumber('fournisseurCommande')
    ->name('fournisseur-commandes.creer-bon-transfert');

    // V6 : « BT du reste » (pièces en attente devenues disponibles)
    Route::post(
        '/fournisseur-commandes/{fournisseurCommande}/creer-bon-transfert-reste',
        [FournisseurCommandeController::class, 'creerBonTransfertReste']
    )->name('fournisseur-commandes.creer-bon-transfert-reste');

    // V6 : commander au fournisseur les pièces non disponibles d'un BC garage
    Route::post(
        '/fournisseur-commandes/{fournisseurCommande}/commander-fournisseur',
        [FournisseurCommandeController::class, 'commanderFournisseur']
    )->name('fournisseur-commandes.commander-fournisseur');

    // Suppression manuelle (administrateur uniquement), ex. devis supprimé côté garage
    Route::delete(
        '/fournisseur-commandes/{fournisseurCommande}',
        [FournisseurCommandeController::class, 'destroy']
    )
    ->whereNumber('fournisseurCommande')
    ->name('fournisseur-commandes.destroy');

    Route::post(
        '/fournisseur-commandes/{fournisseurCommande}/mettre-a-jour-bon-transfert',
        [FournisseurCommandeController::class, 'mettreAJourBonTransfert']
    )
    ->whereNumber('fournisseurCommande')
    ->name('fournisseur-commandes.mettre-a-jour-bon-transfert');


    /*
    |--------------------------------------------------------------------------
    | BONS DE TRANSFERT (BT)
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/bons-transfert',
        [BonTransfertController::class, 'index']
    )->name('bons-transfert.index');

    Route::get(
        '/bons-transfert/{bonTransfert}',
        [BonTransfertController::class, 'show']
    )
    ->whereNumber('bonTransfert')
    ->name('bons-transfert.show');

    Route::get(
        '/bons-transfert/{bonTransfert}/pdf',
        [BonTransfertController::class, 'pdf']
    )
    ->whereNumber('bonTransfert')
    ->name('bons-transfert.pdf');

    Route::get(
        '/bons-transfert/{bonTransfert}/imprimer',
        [BonTransfertController::class, 'imprimer']
    )
    ->whereNumber('bonTransfert')
    ->name('bons-transfert.imprimer');

    Route::post(
        '/bons-transfert/{bonTransfert}/envoyer',
        [BonTransfertController::class, 'envoyer']
    )
    ->whereNumber('bonTransfert')
    ->name('bons-transfert.envoyer');

    // Suppression manuelle d'un BT (administrateur uniquement)
    Route::delete(
        '/bons-transfert/{bonTransfert}',
        [BonTransfertController::class, 'destroy']
    )
    ->whereNumber('bonTransfert')
    ->name('bons-transfert.destroy');

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
