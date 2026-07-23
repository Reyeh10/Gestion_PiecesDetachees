<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| CONTROLLERS
|--------------------------------------------------------------------------
*/

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
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
    | IMPORT
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
    | EXPORT PDF
    |--------------------------------------------------------------------------
    */

   /* Route::get(
        '/products/export/pdf',
        [ProductController::class, 'exportPdf']
    )->name('products.export.pdf');*/

    /*
    |--------------------------------------------------------------------------
    | CRUD
    |--------------------------------------------------------------------------
    */

    Route::resource(
        'products',
        ProductController::class
    )->except([
        'destroy'
    ]);

    /*
    |--------------------------------------------------------------------------
    | DELETE
    |--------------------------------------------------------------------------
    */

    Route::delete(
        '/products/{product}',
        [ProductController::class, 'destroy']
    )->middleware(
        'role:admin,chef_magasinier'
    )->name('products.destroy');

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
| ADMIN + CHEF MAGASINIER
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth',
    'role:admin,chef_magasinier'
])->group(function () {

    Route::get(
        '/depots/create',
        [DepotController::class, 'create']
    )->name('depots.create');

    Route::post(
        '/depots',
        [DepotController::class, 'store']
    )->name('depots.store');

    Route::get(
        '/depots/{depot}/edit',
        [DepotController::class, 'edit']
    )->name('depots.edit');

    Route::put(
        '/depots/{depot}',
        [DepotController::class, 'update']
    )->name('depots.update');

    Route::delete(
        '/depots/{depot}',
        [DepotController::class, 'destroy']
    )->name('depots.destroy');

});
/*
|--------------------------------------------------------------------------
| TRANSFERTS DEPOTS
|--------------------------------------------------------------------------
*/
/*


/*
|--------------------------------------------------------------------------
| EDIT
|--------------------------------------------------------------------------
*/

Route::get(
    '/depot-transfers/{transfer}/edit',
    [DepotTransferController::class, 'edit']
)->name('depot-transfers.edit');

/*
|--------------------------------------------------------------------------
| UPDATE
|--------------------------------------------------------------------------
*/

Route::put(
    '/depot-transfers/{transfer}',
    [DepotTransferController::class, 'update']
)->name('depot-transfers.update');

/*
|--------------------------------------------------------------------------
| DELETE
|--------------------------------------------------------------------------
*/

Route::delete(
    '/depot-transfers/{transfer}',
    [DepotTransferController::class, 'destroy']
)->name('depot-transfers.destroy');
/*
|--------------------------------------------------------------------------
| TOUS PEUVENT VOIR
|--------------------------------------------------------------------------
*/

/*Route::middleware([
    'auth',
    'role:admin,chef_magasinier,magasinier,vendeur,caissier'
])->group(function () {



    Route::get(
        '/transfers',
        [DepotTransferController::class, 'index']
    )->name('transfers.index');



    Route::get(
        '/depot-transfers',
        [DepotTransferController::class, 'index']
    )->name('depot-transfers.index');



    Route::get(
        '/depot-transfers/{transfer}',
        [DepotTransferController::class, 'show']
    )->name('depot-transfers.show');

});*/


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
        '/transfers',
        [DepotTransferController::class, 'index']
    )->name('transfers.index');

    /*
    |--------------------------------------------------------------------------
    | ALIAS INDEX
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/depot-transfers',
        [DepotTransferController::class, 'index']
    )->name('depot-transfers.index');

});

/*
|--------------------------------------------------------------------------
| ADMIN + CHEF MAGASINIER SEULEMENT
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
        '/transfers/create',
        [DepotTransferController::class, 'create']
    )->name('transfers.create');

    /*
    |--------------------------------------------------------------------------
    | ALIAS CREATE
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/depot-transfers/create',
        [DepotTransferController::class, 'create']
    )->name('depot-transfers.create');


    /*
    |--------------------------------------------------------------------------
    | STORE
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/transfers',
        [DepotTransferController::class, 'store']
    )->name('transfers.store');

    /*
    |--------------------------------------------------------------------------
    | ALIAS STORE
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/depot-transfers',
        [DepotTransferController::class, 'store']
    )->name('depot-transfers.store');


});

    /*
|--------------------------------------------------------------------------
| SHOW
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth',
    'role:admin,chef_magasinier,magasinier,vendeur,caissier'
])->group(function () {

    Route::get(
        '/depot-transfers/{transfer}',
        [DepotTransferController::class, 'show']
    )->name('depot-transfers.show');

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
/*
|--------------------------------------------------------------------------
| VENTES
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth',
    'role:admin,chef_magasinier,magasinier,vendeur,caissier'
])->group(function () {

    Route::resource(
        'sales',
        SaleController::class
    )->except([
        'destroy'
    ]);

Route::get(
    '/sales/{sale}/invoice',
    [SaleController::class, 'invoice']
)->name('sales.invoice');

/*
|--------------------------------------------------------------------------
| FACTURE PDF
|--------------------------------------------------------------------------
*/

Route::get(
    '/sales/{sale}/invoice',
    [SaleController::class, 'invoice']
)->name('sales.invoice');

/*
|--------------------------------------------------------------------------
| AJOUT PAIEMENT
|--------------------------------------------------------------------------
*/

    Route::post(
        '/sales/{sale}/payment',
        [SaleController::class, 'addPayment']
    )->name('sales.payment');

    Route::put(
        '/sales/{sale}/cancel',
        [SaleController::class, 'cancel']
    )->name('sales.cancel');

    Route::delete(
        '/sales/{sale}',
        [SaleController::class, 'destroy']
    )->middleware(
        'role:admin,chef_magasinier'
    )->name('sales.destroy');

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
    | PDF PROFORMA
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/proformas/{proforma}/pdf',
        [ProformaController::class, 'pdf']
    )->name('proformas.pdf');

    /*
    |--------------------------------------------------------------------------
    | CONVERTIR PROFORMA EN VENTE
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/proformas/{id}/convert-sale',
        [ProformaController::class, 'convertToSale']
    )->name('proformas.convert-sale');

    /*
    |--------------------------------------------------------------------------
    | CRUD PROFORMAS
    |--------------------------------------------------------------------------
    */

    Route::resource(
        'proformas',
        ProformaController::class
    )->except([
        'destroy'
    ]);

    /*
    |--------------------------------------------------------------------------
    | DELETE
    |--------------------------------------------------------------------------
    */

    Route::delete(
        '/proformas/{proforma}',
        [ProformaController::class, 'destroy']
    )->middleware(
        'role:admin,chef_magasinier'
    )->name('proformas.destroy');

});
/*
|--------------------------------------------------------------------------
| Redirect
|--------------------------------------------------------------------------
*/
require __DIR__.'/auth.php';

/*
|--------------------------------------------------------------------------
| LOGOUT
|--------------------------------------------------------------------------
*/

Route::post('/logout', function () {

    auth()->logout();

    request()->session()->invalidate();

    request()->session()->regenerateToken();

    return redirect('/');

})->name('logout');
