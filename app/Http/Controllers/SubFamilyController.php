<?php

namespace App\Http\Controllers;

use App\Models\Subfamily;
use App\Models\FamilyModel;

use Illuminate\Http\Request;

class SubFamilyController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | INDEX
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        $subfamilies = Subfamily::with('family')
            ->latest()
            ->paginate(10);

        return view(
            'subfamilies.index',
            compact('subfamilies')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE
    |--------------------------------------------------------------------------
    */

    public function create()
    {
        $families = FamilyModel::orderBy('name')->get();

        return view(
            'subfamilies.create',
            compact('families')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | STORE
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        $request->validate([

            'family_id' => 'required|exists:families,id',

            'name' => 'required|string|max:255',
        ]);

        Subfamily::create([

            'family_id' => $request->family_id,

            'name' => $request->name,
        ]);

        return redirect()
            ->route('subfamilies.index')
            ->with(
                'success',
                'Sous-famille créée avec succès.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | SHOW
    |--------------------------------------------------------------------------
    */

    public function show(Subfamily $subfamily)
    {
        $subfamily->load('family');

        return view(
            'subfamilies.show',
            compact('subfamily')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | EDIT
    |--------------------------------------------------------------------------
    */

    public function edit(Subfamily $subfamily)
    {
        $families = FamilyModel::orderBy('name')->get();

        return view(
            'subfamilies.edit',
            compact(
                'subfamily',
                'families'
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
        Subfamily $subfamily
    ) {
        $request->validate([

            'family_id' => 'required|exists:families,id',

            'name' => 'required|string|max:255',
        ]);

        $subfamily->update([

            'family_id' => $request->family_id,

            'name' => $request->name,
        ]);

        return redirect()
            ->route('subfamilies.index')
            ->with(
                'success',
                'Sous-famille modifiée avec succès.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | DELETE
    |--------------------------------------------------------------------------
    */

    public function destroy(Subfamily $subfamily)
    {
        $subfamily->delete();

        return redirect()
            ->route('subfamilies.index')
            ->with(
                'success',
                'Sous-famille supprimée avec succès.'
            );
    }
}
