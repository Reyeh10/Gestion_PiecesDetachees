<?php

namespace App\Http\Controllers;

use App\Models\Rayon;

use Illuminate\Http\Request;

class RayonController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | INDEX
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        $query = Rayon::with([

            'locations',
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

        $rayons = $query
            ->latest()
            ->paginate(10);

        return view(
            'rayons.index',
            compact('rayons')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE
    |--------------------------------------------------------------------------
    */

    public function create()
    {
        return view('rayons.create');
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
                'required|string|max:255|unique:rayons,name',
        ]);

        Rayon::create([

            'name' => $request->name,
        ]);

        return redirect()
            ->route('rayons.index')
            ->with(
                'success',
                'Rayon créé avec succès.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | SHOW
    |--------------------------------------------------------------------------
    */

    public function show(Rayon $rayon)
    {
        $rayon->load([

            'locations',
            'products.brand',
            'products.model',
        ]);

        return view(
            'rayons.show',
            compact('rayon')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | EDIT
    |--------------------------------------------------------------------------
    */

    public function edit(Rayon $rayon)
    {
        return view(
            'rayons.edit',
            compact('rayon')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */

    public function update(
        Request $request,
        Rayon $rayon
    ) {
        $request->validate([

            'name' =>
                'required|string|max:255|unique:rayons,name,' .
                $rayon->id,
        ]);

        $rayon->update([

            'name' => $request->name,
        ]);

        return redirect()
            ->route('rayons.index')
            ->with(
                'success',
                'Rayon modifié avec succès.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | DELETE
    |--------------------------------------------------------------------------
    */

    public function destroy(Rayon $rayon)
    {
        /*
        |--------------------------------------------------------------------------
        | VERIFICATION PRODUITS
        |--------------------------------------------------------------------------
        */

        if ($rayon->products()->count() > 0) {

            return redirect()
                ->route('rayons.index')
                ->with(
                    'error',
                    'Impossible de supprimer ce rayon car il contient des produits.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | VERIFICATION EMPLACEMENTS
        |--------------------------------------------------------------------------
        */

        if ($rayon->locations()->count() > 0) {

            return redirect()
                ->route('rayons.index')
                ->with(
                    'error',
                    'Impossible de supprimer ce rayon car il contient des emplacements.'
                );
        }

        $rayon->delete();

        return redirect()
            ->route('rayons.index')
            ->with(
                'success',
                'Rayon supprimé avec succès.'
            );
    }
}
