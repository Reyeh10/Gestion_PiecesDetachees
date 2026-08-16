<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\CarModel;
use App\Models\Location;
use App\Models\Rayon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductOptionController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | AJOUTER UNE MARQUE
    |--------------------------------------------------------------------------
    */
    public function storeBrand(Request $request): JsonResponse
    {
        $validated = $request->validate(
            [
                'name' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique((new Brand())->getTable(), 'name'),
                ],
            ],
            [
                'name.required' => 'Le nom de la marque est obligatoire.',
                'name.max' => 'Le nom de la marque ne doit pas dépasser 255 caractères.',
                'name.unique' => 'Cette marque existe déjà.',
            ]
        );

        $brand = new Brand();

        $brand->forceFill([
            'name' => trim($validated['name']),
        ]);

        $brand->save();

        return response()->json([
            'success' => true,
            'message' => 'Marque ajoutée avec succès.',
            'item' => [
                'id' => $brand->id,
                'name' => $brand->name,
            ],
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | AJOUTER UN MODÈLE
    |--------------------------------------------------------------------------
    */
    public function storeModel(Request $request): JsonResponse
    {
        $validated = $request->validate(
            [
                'brand_id' => [
                    'required',
                    'integer',
                    Rule::exists((new Brand())->getTable(), 'id'),
                ],

                'name' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique((new CarModel())->getTable(), 'name')
                        ->where(
                            fn ($query) =>
                                $query->where(
                                    'brand_id',
                                    $request->input('brand_id')
                                )
                        ),
                ],
            ],
            [
                'brand_id.required' => 'Veuillez sélectionner une marque.',
                'brand_id.exists' => 'La marque sélectionnée est invalide.',
                'name.required' => 'Le nom du modèle est obligatoire.',
                'name.max' => 'Le nom du modèle ne doit pas dépasser 255 caractères.',
                'name.unique' => 'Ce modèle existe déjà pour cette marque.',
            ]
        );

        $model = new CarModel();

        $model->forceFill([
            'brand_id' => $validated['brand_id'],
            'name' => trim($validated['name']),
        ]);

        $model->save();

        return response()->json([
            'success' => true,
            'message' => 'Modèle ajouté avec succès.',
            'item' => [
                'id' => $model->id,
                'name' => $model->name,
                'brand_id' => $model->brand_id,
            ],
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | AJOUTER UN RAYON
    |--------------------------------------------------------------------------
    */
    public function storeRayon(Request $request): JsonResponse
    {
        $validated = $request->validate(
            [
                'name' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique((new Rayon())->getTable(), 'name'),
                ],
            ],
            [
                'name.required' => 'Le nom du rayon est obligatoire.',
                'name.max' => 'Le nom du rayon ne doit pas dépasser 255 caractères.',
                'name.unique' => 'Ce rayon existe déjà.',
            ]
        );

        $rayon = new Rayon();

        $rayon->forceFill([
            'name' => trim($validated['name']),
        ]);

        $rayon->save();

        return response()->json([
            'success' => true,
            'message' => 'Rayon ajouté avec succès.',
            'item' => [
                'id' => $rayon->id,
                'name' => $rayon->name,
            ],
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | AJOUTER UN EMPLACEMENT
    |--------------------------------------------------------------------------
    */
    public function storeLocation(Request $request): JsonResponse
    {
        $validated = $request->validate(
            [
                'rayon_id' => [
                    'required',
                    'integer',
                    Rule::exists((new Rayon())->getTable(), 'id'),
                ],

                'name' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique((new Location())->getTable(), 'name')
                        ->where(
                            fn ($query) =>
                                $query->where(
                                    'rayon_id',
                                    $request->input('rayon_id')
                                )
                        ),
                ],
            ],
            [
                'rayon_id.required' => 'Veuillez sélectionner un rayon.',
                'rayon_id.exists' => 'Le rayon sélectionné est invalide.',
                'name.required' => 'Le nom de l’emplacement est obligatoire.',
                'name.max' => 'Le nom de l’emplacement ne doit pas dépasser 255 caractères.',
                'name.unique' => 'Cet emplacement existe déjà dans ce rayon.',
            ]
        );

        $location = new Location();

        $location->forceFill([
            'rayon_id' => $validated['rayon_id'],
            'name' => trim($validated['name']),
        ]);

        $location->save();

        return response()->json([
            'success' => true,
            'message' => 'Emplacement ajouté avec succès.',
            'item' => [
                'id' => $location->id,
                'name' => $location->name,
                'rayon_id' => $location->rayon_id,
            ],
        ]);
    }
}