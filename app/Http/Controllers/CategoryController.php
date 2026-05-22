<?php

namespace App\Http\Controllers;

use App\Models\FamilyModel;
use App\Models\Subfamily;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | INDEX
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        $query = FamilyModel::with('subfamilies');

        /*
        |--------------------------------------------------------------------------
        | SEARCH
        |--------------------------------------------------------------------------
        */

        if ($request->search) {

            $query->where(function ($q) use ($request) {

                $q->where(
                    'name',
                    'like',
                    '%' . $request->search . '%'
                )

                ->orWhereHas('subfamilies', function ($sub) use ($request) {

                    $sub->where(
                        'name',
                        'like',
                        '%' . $request->search . '%'
                    );
                });
            });
        }

        $families = $query
            ->latest()
            ->paginate(10);

        return view(
            'categories.index',
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
        return view('categories.create');
    }

    /*
    |--------------------------------------------------------------------------
    | STORE
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'subcategories.*' => 'nullable|string|max:255',
        ]);

        /*
        |--------------------------------------------------------------------------
        | CREATE FAMILY
        |--------------------------------------------------------------------------
        */

        $family = FamilyModel::create([
            'name' => $request->name,
        ]);

        /*
        |--------------------------------------------------------------------------
        | CREATE SUBFAMILIES
        |--------------------------------------------------------------------------
        */

        if ($request->subcategories) {

            foreach ($request->subcategories as $sub) {

                if (!empty($sub)) {

                    Subfamily::create([
                        'family_id' => $family->id,
                        'name' => $sub,
                    ]);
                }
            }
        }

        return redirect()
            ->route('categories.index')
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

    public function show($id)
    {
        $family = FamilyModel::with('subfamilies')
            ->findOrFail($id);

        return view(
            'categories.show',
            compact('family')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | EDIT
    |--------------------------------------------------------------------------
    */

    public function edit($id)
    {
        $category = FamilyModel::with('subfamilies')
            ->findOrFail($id);

        return view(
            'categories.edit',
            compact('category')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'subcategories.*' => 'nullable|string|max:255',
        ]);

        $family = FamilyModel::findOrFail($id);

        /*
        |--------------------------------------------------------------------------
        | UPDATE FAMILY
        |--------------------------------------------------------------------------
        */

        $family->update([
            'name' => $request->name,
        ]);

        /*
        |--------------------------------------------------------------------------
        | DELETE OLD SUBFAMILIES
        |--------------------------------------------------------------------------
        */

        Subfamily::where(
            'family_id',
            $family->id
        )->delete();

        /*
        |--------------------------------------------------------------------------
        | CREATE NEW SUBFAMILIES
        |--------------------------------------------------------------------------
        */

        if ($request->subcategories) {

            foreach ($request->subcategories as $sub) {

                if (!empty($sub)) {

                    Subfamily::create([
                        'family_id' => $family->id,
                        'name' => $sub,
                    ]);
                }
            }
        }

        return redirect()
            ->route('categories.index')
            ->with(
                'success',
                'Famille modifiée avec succès.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | DESTROY
    |--------------------------------------------------------------------------
    */

    public function destroy($id)
    {
        $family = FamilyModel::findOrFail($id);

        /*
        |--------------------------------------------------------------------------
        | DELETE SUBFAMILIES
        |--------------------------------------------------------------------------
        */

        Subfamily::where(
            'family_id',
            $family->id
        )->delete();

        /*
        |--------------------------------------------------------------------------
        | DELETE FAMILY
        |--------------------------------------------------------------------------
        */

        $family->delete();

        return redirect()
            ->route('categories.index')
            ->with(
                'success',
                'Famille supprimée avec succès.'
            );
    }
}
