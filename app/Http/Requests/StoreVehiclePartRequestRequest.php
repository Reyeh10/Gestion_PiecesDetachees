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

    /*
    |--------------------------------------------------------------------------
    | RÈGLES DE VALIDATION
    |--------------------------------------------------------------------------
    |
    | Une demande de pièce doit avoir UNE SEULE destination :
    |
    | 1. soit un véhicule ;
    | 2. soit un dépôt.
    |
    | Il est interdit :
    |
    | - de laisser les deux destinations vides ;
    | - de sélectionner un véhicule ET un dépôt simultanément.
    |
    */

    public function rules(): array
    {
        return [

            /*
            |--------------------------------------------------------------------------
            | DESTINATION : VÉHICULE
            |--------------------------------------------------------------------------
            |
            | vehicle_id est obligatoire uniquement lorsque depot_id est vide.
            |
            */

            'vehicle_id' => [
                'nullable',
                'required_without:depot_id',
                'prohibited_unless:depot_id,null',
                'exists:vehicles,id',
            ],

            /*
            |--------------------------------------------------------------------------
            | DESTINATION : DÉPÔT
            |--------------------------------------------------------------------------
            |
            | depot_id est obligatoire uniquement lorsque vehicle_id est vide.
            |
            */

            'depot_id' => [
                'nullable',
                'required_without:vehicle_id',
                'prohibited_unless:vehicle_id,null',
                'exists:depots,id',
            ],

            /*
            |--------------------------------------------------------------------------
            | PRODUIT
            |--------------------------------------------------------------------------
            */

            'product_id' => [
                'nullable',
                'exists:products,id',
            ],

            /*
            |--------------------------------------------------------------------------
            | FOURNISSEUR
            |--------------------------------------------------------------------------
            */

            'supplier_id' => [
                'nullable',
                'exists:suppliers,id',
            ],

            /*
            |--------------------------------------------------------------------------
            | RÉFÉRENCE
            |--------------------------------------------------------------------------
            */

            'reference' => [
                'nullable',
                'string',
                'max:255',
            ],

            /*
            |--------------------------------------------------------------------------
            | NOM DE LA PIÈCE
            |--------------------------------------------------------------------------
            */

            'part_name' => [
                'required',
                'string',
                'max:255',
            ],

            /*
            |--------------------------------------------------------------------------
            | DESCRIPTION
            |--------------------------------------------------------------------------
            */

            'description' => [
                'nullable',
                'string',
            ],

            /*
            |--------------------------------------------------------------------------
            | QUANTITÉ
            |--------------------------------------------------------------------------
            */

            'quantity' => [
                'required',
                'numeric',
                'min:0.01',
            ],

            /*
            |--------------------------------------------------------------------------
            | UNITÉ
            |--------------------------------------------------------------------------
            */

            'unit' => [
                'required',
                'string',
                'max:50',
            ],

            /*
            |--------------------------------------------------------------------------
            | RÉFÉRENCE FOURNISSEUR
            |--------------------------------------------------------------------------
            */

            'supplier_reference' => [
                'nullable',
                'string',
                'max:255',
            ],

            /*
            |--------------------------------------------------------------------------
            | RÉFÉRENCE COMMANDE
            |--------------------------------------------------------------------------
            */

            'order_reference' => [
                'nullable',
                'string',
                'max:255',
            ],

            /*
            |--------------------------------------------------------------------------
            | PRIX ESTIMÉ
            |--------------------------------------------------------------------------
            */

            'estimated_price' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            /*
            |--------------------------------------------------------------------------
            | PRIX D'ACHAT
            |--------------------------------------------------------------------------
            */

            'purchase_price' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            /*
            |--------------------------------------------------------------------------
            | NOTES
            |--------------------------------------------------------------------------
            */

            'notes' => [
                'nullable',
                'string',
            ],
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | MESSAGES DE VALIDATION
    |--------------------------------------------------------------------------
    */

    public function messages(): array
    {
        return [

            /*
            |--------------------------------------------------------------------------
            | DESTINATION
            |--------------------------------------------------------------------------
            */

            'vehicle_id.required_without' =>
                'Veuillez sélectionner un véhicule ou un dépôt.',

            'vehicle_id.prohibited_unless' =>
                'Vous ne pouvez pas sélectionner un véhicule et un dépôt en même temps.',

            'vehicle_id.exists' =>
                'Le véhicule sélectionné est invalide.',


            'depot_id.required_without' =>
                'Veuillez sélectionner un véhicule ou un dépôt.',

            'depot_id.prohibited_unless' =>
                'Vous ne pouvez pas sélectionner un véhicule et un dépôt en même temps.',

            'depot_id.exists' =>
                'Le dépôt sélectionné est invalide.',

            /*
            |--------------------------------------------------------------------------
            | PRODUIT
            |--------------------------------------------------------------------------
            */

            'product_id.exists' =>
                'La pièce sélectionnée est invalide.',

            /*
            |--------------------------------------------------------------------------
            | FOURNISSEUR
            |--------------------------------------------------------------------------
            */

            'supplier_id.exists' =>
                'Le fournisseur sélectionné est invalide.',

            /*
            |--------------------------------------------------------------------------
            | PIÈCE
            |--------------------------------------------------------------------------
            */

            'part_name.required' =>
                'Le nom de la pièce est obligatoire.',

            /*
            |--------------------------------------------------------------------------
            | QUANTITÉ
            |--------------------------------------------------------------------------
            */

            'quantity.required' =>
                'La quantité est obligatoire.',

            'quantity.numeric' =>
                'La quantité doit être un nombre.',

            'quantity.min' =>
                'La quantité doit être supérieure à zéro.',

            /*
            |--------------------------------------------------------------------------
            | UNITÉ
            |--------------------------------------------------------------------------
            */

            'unit.required' =>
                'L’unité est obligatoire.',

            /*
            |--------------------------------------------------------------------------
            | PRIX
            |--------------------------------------------------------------------------
            */

            'estimated_price.numeric' =>
                'Le prix estimé doit être un nombre.',

            'purchase_price.numeric' =>
                'Le prix d’achat doit être un nombre.',
        ];
    }
}
