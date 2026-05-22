<?php

namespace App\Http\Controllers;

use App\Models\Location;
use App\Models\Rayon;

use Illuminate\Http\Request;

class LocationController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | INDEX
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        $query = Location::with([
            'rayon',
            'products',
        ]);

        /*
        |--------------------------------------------------------------------------
        | SEARCH
        |--------------------------------------------------------------------------
        */

        if ($request->search) {

            $query->where('name', 'like', '%' . $request->search . '%')

                ->orWhereHas('rayon', function ($q) use ($request) {

                    $q->where(
                        'name',
                        'like',
                        '%' . $request->search . '%'
                    );
                });
        }

        $locations = $query
            ->latest()
            ->paginate(10);

        return view(
            'locations.index',
            compact('locations')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE
    |--------------------------------------------------------------------------
    */

    public function create()
    {
        $rayons = Rayon::orderBy('name')->get();

        return view(
            'locations.create',
            compact('rayons')
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

            'rayon_id' => 'required|exists:rayons,id',
            'name' =>
                'required|string|max:255',
        ]);

        Location::create([

            'rayon_id' => $request->rayon_id,
            'name' => $request->name,
        ]);

        return redirect()
            ->route('locations.index')
            ->with(
                'success',
                'Emplacement créé avec succès.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | SHOW
    |--------------------------------------------------------------------------
    */

    public function show(Location $location)
    {
        $location->load([

            'rayon',
            'products.brand',
            'products.model',
        ]);

        return view(
            'locations.show',
            compact('location')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | EDIT
    |--------------------------------------------------------------------------
    */

    public function edit(Location $location)
    {
        $rayons = Rayon::orderBy('name')->get();

        return view(
            'locations.edit',
            compact(
                'location',
                'rayons'
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
        Location $location
    ) {
        $request->validate([

            'rayon_id' => 'required|exists:rayons,id',
            'name' =>
                'required|string|max:255',
        ]);

        $location->update([

            'rayon_id' => $request->rayon_id,

            'name' => $request->name,
        ]);

        return redirect()
            ->route('locations.index')
            ->with(
                'success',
                'Emplacement modifié avec succès.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | DELETE
    |--------------------------------------------------------------------------
    */

    public function destroy(Location $location)
    {
        /*
        |--------------------------------------------------------------------------
        | VERIFICATION PRODUITS
        |--------------------------------------------------------------------------
        */

        if ($location->products()->count() > 0) {

            return redirect()
                ->route('locations.index')
                ->with(
                    'error',
                    'Impossible de supprimer cet emplacement car il contient des produits.'
                );
        }

        $location->delete();

        return redirect()
            ->route('locations.index')
            ->with(
                'success',
                'Emplacement supprimé avec succès.'
            );
    }
}
