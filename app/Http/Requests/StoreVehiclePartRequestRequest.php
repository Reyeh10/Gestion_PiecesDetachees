<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreVehiclePartRequestRequest extends FormRequest
{
    /**
     * Tous les utilisateurs authentifiés peuvent utiliser ce formulaire.
     * Les permissions pourront être renforcées plus tard.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'vehicle_id' => [
                'required',
                'exists:vehicles,id',
            ],

            'product_id' => [
                'nullable',
                'exists:products,id',
            ],

            'supplier_id' => [
                'nullable',
                'exists:suppliers,id',
            ],

            'reference' => [
                'nullable',
                'string',
                'max:255',
            ],

            'part_name' => [
                'required',
                'string',
                'max:255',
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'quantity' => [
                'required',
                'numeric',
                'min:0.01',
            ],

            'unit' => [
                'required',
                'string',
                'max:50',
            ],

            'supplier_reference' => [
                'nullable',
                'string',
                'max:255',
            ],

            'order_reference' => [
                'nullable',
                'string',
                'max:255',
            ],

            'estimated_price' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'purchase_price' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'notes' => [
                'nullable',
                'string',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'vehicle_id.required' => 'Veuillez sélectionner un véhicule.',
            'vehicle_id.exists' => 'Le véhicule sélectionné est invalide.',

            'product_id.exists' => 'La pièce sélectionnée est invalide.',

            'supplier_id.exists' => 'Le fournisseur sélectionné est invalide.',

            'part_name.required' => 'Le nom de la pièce est obligatoire.',

            'quantity.required' => 'La quantité est obligatoire.',
            'quantity.numeric' => 'La quantité doit être un nombre.',
            'quantity.min' => 'La quantité doit être supérieure à zéro.',

            'unit.required' => 'L’unité est obligatoire.',

            'estimated_price.numeric' => 'Le prix estimé doit être un nombre.',
            'purchase_price.numeric' => 'Le prix d’achat doit être un nombre.',
        ];
    }
}
