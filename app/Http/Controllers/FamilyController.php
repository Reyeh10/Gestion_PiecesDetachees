<?php

namespace App\Http\Controllers;
use App\Models\FamilyModel;
use Illuminate\Http\Request;

class FamilyController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | INDEX
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        $query = FamilyModel::with([

            'subfamilies',
            'products',
        ]);

        /*
        |--------------------------------------------------------------------------
        | SEARCH
        |--------------------------------------------------------------------------
        */

        if ($request->search) {

            $query->where(
                'name',
                'like',
                '%' . $request->search . '%'
            );
        }

        $families = $query
            ->latest()
            ->paginate(10);

        return view(
            'families.index',
            compact('families')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE
    |--------------------------------------------------------------------------
    */

    public function create()
    {
        return view('families.create');
    }

    /*
    |--------------------------------------------------------------------------
    | STORE
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        $request->validate([

            'name' =>
                'required|string|max:255|unique:families,name',
        ]);

        FamilyModel::create([

            'name' => $request->name,
        ]);

        return redirect()
            ->route('families.index')
            ->with(
                'success',
                'Famille créée avec succès.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | SHOW
    |--------------------------------------------------------------------------
    */

    public function show(FamilyModel $family)
    {
        $family->load([

            'subfamilies',
            'products.brand',
            'products.model',
        ]);

        return view(
            'families.show',
            compact('family')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | EDIT
    |--------------------------------------------------------------------------
    */

    public function edit(FamilyModel $family)
    {
        return view(
            'families.edit',
            compact('family')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */

    public function update(
        Request $request,
        FamilyModel $family
    ) {
        $request->validate([

            'name' =>
                'required|string|max:255|unique:families,name,' .
                $family->id,
        ]);

        $family->update([

            'name' => $request->name,
        ]);

        return redirect()
            ->route('families.index')
            ->with(
                'success',
                'Famille modifiée avec succès.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | DELETE
    |--------------------------------------------------------------------------
    */

    public function destroy(FamilyModel $family)
    {
        /*
        |--------------------------------------------------------------------------
        | VERIFICATION PRODUITS
        |--------------------------------------------------------------------------
        */

        if ($family->products()->count() > 0) {

            return redirect()
                ->route('families.index')
                ->with(
                    'error',
                    'Impossible de supprimer cette famille car elle contient des produits.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | VERIFICATION SOUS-FAMILLES
        |--------------------------------------------------------------------------
        */

        if ($family->subfamilies()->count() > 0) {

            return redirect()
                ->route('families.index')
                ->with(
                    'error',
                    'Impossible de supprimer cette famille car elle contient des sous-familles.'
                );
        }

        $family->delete();

        return redirect()
            ->route('families.index')
            ->with(
                'success',
                'Famille supprimée avec succès.'
            );
    }
}
