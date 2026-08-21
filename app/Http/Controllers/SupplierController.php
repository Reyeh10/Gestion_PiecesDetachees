<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index()
    {
        $suppliers = Supplier::latest()->get();
        return view('suppliers.index', compact('suppliers'));
    }

   public function create()
    {
        /*
        |--------------------------------------------------------------------------
        | GÉNÉRER LE PROCHAIN CODE FOURNISSEUR
        |--------------------------------------------------------------------------
        |
        | Exemples :
        | aucun fournisseur => FR001
        | dernier code FR001 => FR002
        | dernier code FR025 => FR026
        |
        */

        $lastSupplier = Supplier::query()
            ->where('code', 'like', 'FR%')
            ->orderByRaw('CAST(SUBSTRING(code, 3) AS UNSIGNED) DESC')
            ->first();

        if ($lastSupplier && preg_match('/^FR(\d+)$/', $lastSupplier->code, $matches)) {

            $nextNumber = ((int) $matches[1]) + 1;

        } else {

            $nextNumber = 1;
        }

        $nextCode =
            'FR' . str_pad(
                $nextNumber,
                3,
                '0',
                STR_PAD_LEFT
            );

        return view(
            'suppliers.create',
            compact('nextCode')
        );
    }

   public function store(Request $request)
    {
        $request->validate([

            'name' =>
                'required|string|max:255',

            'phone' =>
                'nullable|string|max:50',

            'email' =>
                'nullable|email|max:255',

            'address' =>
                'nullable|string',

            'currency' =>
                'nullable|string|max:10',
        ]);

        /*
        |--------------------------------------------------------------------------
        | GÉNÉRATION AUTOMATIQUE DU CODE
        |--------------------------------------------------------------------------
        */

        $lastSupplier = Supplier::query()
            ->where('code', 'like', 'FR%')
            ->orderByRaw(
                'CAST(SUBSTRING(code, 3) AS UNSIGNED) DESC'
            )
            ->first();

        if (
            $lastSupplier
            &&
            preg_match(
                '/^FR(\d+)$/',
                $lastSupplier->code,
                $matches
            )
        ) {

            $nextNumber =
                ((int) $matches[1]) + 1;

        } else {

            $nextNumber = 1;
        }

        $code =
            'FR'
            .
            str_pad(
                $nextNumber,
                3,
                '0',
                STR_PAD_LEFT
            );

        /*
        |--------------------------------------------------------------------------
        | CRÉATION
        |--------------------------------------------------------------------------
        */

        Supplier::create([

            'code' =>
                $code,

            'name' =>
                $request->name,

            'phone' =>
                $request->phone,

            'email' =>
                $request->email,

            'address' =>
                $request->address,

            'currency' =>
                $request->currency ?? 'FDJ',
        ]);

        return redirect()
            ->route('suppliers.index')
            ->with(
                'success',
                'Fournisseur créé avec succès.'
            );
    }

    public function show(Supplier $supplier)
    {
        $supplier->load([

            'products.brand',
            'products.model',

            'purchases.items.product',

        ]);

        return view(
            'suppliers.show',
            compact('supplier')
        );
    }

    public function edit(Supplier $supplier)
    {
        return view('suppliers.edit', compact('supplier'));
    }

    public function update(
    Request $request,
    Supplier $supplier
    ) {
        $request->validate([

            'name' =>
                'required|string|max:255',

            'phone' =>
                'nullable|string|max:50',

            'email' =>
                'nullable|email|max:255',

            'address' =>
                'nullable|string',

            'currency' =>
                'nullable|string|max:10',
        ]);

        $supplier->update([

            'name' =>
                $request->name,

            'phone' =>
                $request->phone,

            'email' =>
                $request->email,

            'address' =>
                $request->address,

            'currency' =>
                $request->currency ?? 'FDJ',
        ]);

        return redirect()
            ->route('suppliers.index')
            ->with(
                'success',
                'Fournisseur modifié avec succès.'
            );
    }

    public function destroy(Supplier $supplier)
    {
        $supplier->delete();

        return redirect()->route('suppliers.index')
            ->with('success', 'Fournisseur supprimé avec succès.');
    }
}
