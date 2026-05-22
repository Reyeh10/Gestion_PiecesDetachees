<?php

namespace App\Http\Controllers;

use App\Models\Brand;

use Illuminate\Http\Request;

class BrandController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | INDEX
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        $brands = Brand::with('models')
            ->latest()
            ->paginate(10);

        return view(
            'brands.index',
            compact('brands')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE
    |--------------------------------------------------------------------------
    */

    public function create()
    {
        return view('brands.create');
    }

    /*
    |--------------------------------------------------------------------------
    | STORE
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        $request->validate([

            'name' => 'required|string|max:255|unique:brands,name',
        ]);

        Brand::create([

            'name' => $request->name,
        ]);

        return redirect()
            ->route('brands.index')
            ->with(
                'success',
                'Marque créée avec succès.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | SHOW
    |--------------------------------------------------------------------------
    */

    public function show(Brand $brand)
    {
        $brand->load([

            'models',

            'products',
        ]);

        return view(
            'brands.show',
            compact('brand')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | EDIT
    |--------------------------------------------------------------------------
    */

    public function edit(Brand $brand)
    {
        return view(
            'brands.edit',
            compact('brand')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */

    public function update(
        Request $request,
        Brand $brand
    ) {
        $request->validate([

            'name' =>
                'required|string|max:255|unique:brands,name,' .
                $brand->id,
        ]);

        $brand->update([

            'name' => $request->name,
        ]);

        return redirect()
            ->route('brands.index')
            ->with(
                'success',
                'Marque modifiée avec succès.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | DELETE
    |--------------------------------------------------------------------------
    */

    public function destroy(Brand $brand)
    {
        $brand->delete();

        return redirect()
            ->route('brands.index')
            ->with(
                'success',
                'Marque supprimée avec succès.'
            );
    }
}
